<?php

namespace App\Services;

use App\Models\ExportJob;
use App\Models\RegistroGlobal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\OptimizedChunkedExport;
use App\Jobs\ProcessLargeExportJob;

class ExportService
{
    /**
     * Optimal chunk size for large exports
     * Balance between memory usage and query performance
     */
    const CHUNK_SIZE = 1000;
    const QUERY_BATCH_SIZE = 5000;

    /**
     * Maximum records before forcing async export
     */
    const SYNC_EXPORT_LIMIT = 10000;

    /**
     * Create a new export job (async for large datasets)
     */
    public function createExport(array $filters, int $userId = null): ExportJob
    {
        // Calculate estimated record count
        $totalRecords = $this->getRecordCount($filters);

        // Create export job record
        $exportJob = ExportJob::create([
            'id' => \Illuminate\Support\Str::uuid()->toString(),
            'user_id' => $userId,
            'type' => ExportJob::TYPE_REGISTROS,
            'filters' => $filters,
            'status' => ExportJob::STATUS_PENDING,
            'total_records' => $totalRecords,
            'expires_at' => now()->addHours(24),
        ]);

        // For large exports, always use async processing
        if ($totalRecords > self::SYNC_EXPORT_LIMIT) {
            $this->dispatchAsyncExport($exportJob);
        }
        else {
            // For smaller exports, also use async for better UX
            $this->dispatchAsyncExport($exportJob);
        }

        return $exportJob;
    }

    /**
     * Get record count for the given filters
     */
    public function getRecordCount(array $filters): int
    {
        try {
            $query = $this->buildQuery($filters);
            return $query->count();
        }
        catch (\Exception $e) {
            Log::warning('Failed to get exact record count, using estimate', [
                'error' => $e->getMessage(),
            ]);
            // Return a reasonable estimate if count fails
            return 50000;
        }
    }

    /**
     * Build query with filters applied
     */
    public function buildQuery(array $filters): Builder
    {
        $query = RegistroGlobal::query();

        // Apply year filter
        if (!empty($filters['years'])) {
            $query->whereIn('ano', (array)$filters['years']);
        }

        // Apply month filter
        if (!empty($filters['months'])) {
            $query->whereIn('mes', (array)$filters['months']);
        }

        // Apply global search
        if (!empty($filters['global_search'])) {
            $search = $filters['global_search'];
            $query->where(function ($q) use ($search) {
                $q->where('medico', 'LIKE', "%{$search}%")
                    ->orWhere('diagnostico_1', 'LIKE', "%{$search}%")
                    ->orWhere('colonia', 'LIKE', "%{$search}%")
                    ->orWhere('exp', 'LIKE', "%{$search}%");
            });
        }

        // Apply additional column filters
        if (!empty($filters['columnFilters']) && is_array($filters['columnFilters'])) {
            $allowedColumns = [
                'id', 'ano', 'mes', 'numero', 'cm', 'medico', 'prof', 'fecha', 'se', 'exp',
                'sexo', 'edad', 'tipo', 'rango', 'rango_2', 'rango_3', 'rango_4', 'rango_5', 'cond',
                'cod_col', 'colonia', 'cod_1', 'diagnostico_1', 'cond_1', 'sg', 'cod_2', 'diagnostico_2',
                'cond_2', 'cod_3', 'diagnostico_3', 'cond_3', 'cod_4', 'diagnostico_4', 'cond_4',
                'cod_5', 'diagnostico_5', 'cond_5', 'cod_6', 'diagnostico_6', 'cond_6', 'cod_7',
                'diagnostico_7', 'cond_7', 'referido_a', 'referido_de', 'pg_emb', 'jornada', 'sm', 'sg2'
            ];

            foreach ($filters['columnFilters'] as $column => $value) {
                if (!empty($value) && in_array($column, $allowedColumns)) {
                    if (is_array($value)) {
                        $query->whereIn($column, $value);
                    }
                    else {
                        $query->where($column, 'LIKE', "%{$value}%");
                    }
                }
            }
        }

        return $query->orderBy('fecha', 'desc')->orderBy('numero', 'asc');
    }

    /**
     * Get paginated data for preview
     */
    public function getPreviewData(array $filters, int $perPage = 100): LengthAwarePaginator
    {
        $query = $this->buildQuery($filters);
        return $query->paginate($perPage);
    }

    /**
     * Export using chunked query (memory efficient)
     * This is the optimal method for large exports
     */
    public function exportInChunks(ExportJob $exportJob): string
    {
        $startTime = microtime(true);

        Log::info("Starting chunked export", [
            'export_id' => $exportJob->id,
            'total_records' => $exportJob->total_records,
        ]);

        // Generate filename
        $filename = $this->generateFilename($exportJob->type);
        $filepath = "exports/{$filename}";

        // Mark as processing
        $exportJob->markAsProcessing();

        try {
            // Create the export instance with chunked reading
            $export = new OptimizedChunkedExport(
                $exportJob->filters,
                function ($processed) use ($exportJob) {
                // Update progress periodically (every 1000 records)
                if ($processed % 1000 === 0) {
                    $exportJob->updateProgress($processed);
                }
            }
                );

            // Store the file
            Excel::store($export, $filepath, 'local');

            // Mark as completed
            $exportJob->markAsCompleted($filename, $filepath);

            $duration = round(microtime(true) - $startTime, 2);
            Log::info("Export completed successfully", [
                'export_id' => $exportJob->id,
                'filename' => $filename,
                'duration_seconds' => $duration,
            ]);

            return $filename;
        }
        catch (\Exception $e) {
            $exportJob->markAsFailed($e->getMessage());
            Log::error("Export failed", [
                'export_id' => $exportJob->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }

    /**
     * Alternative method: Export using generator (for very large datasets)
     * This uses streaming to minimize memory usage
     */
    public function exportWithGenerator(ExportJob $exportJob): string
    {
        $filename = $this->generateFilename($exportJob->type);
        $filepath = "exports/{$filename}";

        $exportJob->markAsProcessing();

        try {
            // Use a generator-based export for maximum memory efficiency
            $export = new class($exportJob->filters, function ($processed) use ($exportJob) {
                $exportJob->updateProgress($processed);
            }) implements \Maatwebsite\Excel\Concerns\FromIterator, \Maatwebsite\Excel\Concerns\WithHeadings {
                private $filters;
                private $progressCallback;
                private $headings = [
                'ID', 'AÑO', 'MES', 'NÚMERO', 'CM', 'MÉDICO', 'PROFESIÓN', 'FECHA',
                'SE', 'EXPEDIENTE', 'SEXO', 'EDAD', 'TIPO', 'RANGO', 'RANGO 2',
                'RANGO 3', 'RANGO 4', 'RANGO 5', 'CONDICIÓN', 'CÓD. COLONIA',
                'COLONIA', 'CÓDIGO 1', 'DIAGNÓSTICO 1', 'COND. 1', 'SG',
                'CÓDIGO 2', 'DIAGNÓSTICO 2', 'COND. 2', 'CÓDIGO 3', 'DIAGNÓSTICO 3',
                'COND. 3', 'CÓDIGO 4', 'DIAGNÓSTICO 4', 'COND. 4', 'CÓDIGO 5',
                'DIAGNÓSTICO 5', 'COND. 5', 'CÓDIGO 6', 'DIAGNÓSTICO 6', 'COND. 6',
                'CÓDIGO 7', 'DIAGNÓSTICO 7', 'COND. 7', 'REFERIDO A', 'REFERIDO DE',
                'PG EMB', 'JORNADA', 'SM'
                ];

                public function __construct($filters, $progressCallback)
                {
                    $this->filters = $filters;
                    $this->progressCallback = $progressCallback;
                }

                public function headings(): array
                {
                    return $this->headings;
                }

                public function iterator(): \Generator
                {
                    $service = app(\App\Services\ExportService::class);
                    $query = $service->buildQuery($this->filters);

                    $processed = 0;
                    $lastProgressUpdate = 0;

                    // Use chunked query with cursor for memory efficiency
                    $query->chunk(1000, function ($records) use (&$processed, &$lastProgressUpdate) {
                        foreach ($records as $record) {
                            yield [
                                $record->id, $record->ano, $record->mes, $record->numero,
                                $record->cm, $record->medico, $record->prof,
                                $record->fecha ? $record->fecha->format('Y-m-d') : '',
                                $record->se, $record->exp, $record->sexo, $record->edad,
                                $record->tipo, $record->rango, $record->rango_2,
                                $record->rango_3, $record->rango_4, $record->rango_5,
                                $record->cond, $record->cod_col, $record->colonia,
                                $record->cod_1, $record->diagnostico_1, $record->cond_1,
                                $record->sg, $record->cod_2, $record->diagnostico_2,
                                $record->cond_2, $record->cod_3, $record->diagnostico_3,
                                $record->cond_3, $record->cod_4, $record->diagnostico_4,
                                $record->cond_4, $record->cod_5, $record->diagnostico_5,
                                $record->cond_5, $record->cod_6, $record->diagnostico_6,
                                $record->cond_6, $record->cod_7, $record->diagnostico_7,
                                $record->cond_7, $record->referido_a, $record->referido_de,
                                $record->pg_emb, $record->jornada, $record->sm,
                            ];

                            $processed++;

                            // Update progress every 100 records
                            if ($processed - $lastProgressUpdate >= 100) {
                                $lastProgressUpdate = $processed;
                                if ($this->progressCallback) {
                                    ($this->progressCallback)($processed);
                                }
                            }
                        }
                    });
                }
            };

            Excel::store($export, $filepath, 'local');
            $exportJob->markAsCompleted($filename, $filepath);

            return $filename;
        }
        catch (\Exception $e) {
            $exportJob->markAsFailed($e->getMessage());
            throw $e;
        }
    }

    /**
     * Dispatch async export job
     */
    protected function dispatchAsyncExport(ExportJob $exportJob): void
    {
        ProcessLargeExportJob::dispatch($exportJob)
            ->onQueue('exports')
            ->onConnection('database');
    }

    /**
     * Generate unique filename
     */
    protected function generateFilename(string $type): string
    {
        $timestamp = now()->format('Y-m-d_His');
        return "{$type}_export_{$timestamp}_{$this->generateRandomString(8)}.xlsx";
    }

    /**
     * Generate random string for filename
     */
    protected function generateRandomString(int $length = 8): string
    {
        return substr(str_shuffle('0123456789abcdefghijklmnopqrstuvwxyz'), 0, $length);
    }

    /**
     * Get export by UUID
     */
    public function getExportByUuid(string $uuid): ?ExportJob
    {
        return ExportJob::where('id', $uuid)->first();
    }

    /**
     * Get user's export history
     */
    public function getUserExports(int $userId, int $limit = 10)
    {
        return ExportJob::forUser($userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Clean up old exports
     */
    public function cleanupOldExports(): int
    {
        return ExportJob::where('expires_at', '<', now())
            ->where('status', '!=', ExportJob::STATUS_PROCESSING)
            ->delete();
    }
}
