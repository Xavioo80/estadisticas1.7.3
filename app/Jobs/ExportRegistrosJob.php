<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RegistrosAt1Export;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class ExportRegistrosJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * The number of times the job may be attempted.
     */
    public int $tries = 3;

    /**
     * The number of seconds to wait before retrying the job.
     */
    public int $backoff = 60;

    /**
     * Export parameters
     */
    protected $years;
    protected $months;
    protected $filters;
    protected $userId;
    protected $jobUuid;

    /**
     * Create a new job instance.
     */
    public function __construct(array $years, array $months, array $filters, ?int $userId = null)
    {
        $this->years = $years;
        $this->months = $months;
        $this->filters = $filters;
        $this->userId = $userId;
        $this->jobUuid = uniqid('export_', true);
        $this->onQueue('exports');
    }

    /**
     * Get the unique ID for this job
     */
    public function getJobUuid(): string
    {
        return $this->jobUuid;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        try {
            // Set unlimited memory and time for large exports
            ini_set('memory_limit', '-1');
            set_time_limit(0);

            // Generate unique filename
            $filename = 'registros_export_' . $this->jobUuid . '_' . date('Y-m-d_His') . '.xlsx';
            $filepath = 'exports/' . $filename;

            // Create and store the export
            $export = new RegistrosAt1Export($this->years, $this->months, $this->filters);

            Excel::store($export, $filepath, 'local');

            // Store metadata for later retrieval
            $metadata = [
                'filename' => $filename,
                'filepath' => $filepath,
                'status' => 'completed',
                'user_id' => $this->userId,
                'created_at' => now()->toIso8601String(),
                'completed_at' => now()->toIso8601String(),
                'years' => $this->years,
                'months' => $this->months,
                'records_count' => $this->getEstimatedRecordCount(),
            ];

            // Save metadata to cache for quick access
            cache()->put('export_metadata_' . $this->jobUuid, $metadata, now()->addHours(24));

            Log::info("Export completed: {$filename}", [
                'job_uuid' => $this->jobUuid,
                'user_id' => $this->userId,
            ]);

        }
        catch (\Exception $e) {
            Log::error('Export job failed: ' . $e->getMessage(), [
                'job_uuid' => $this->jobUuid ?? 'unknown',
                'user_id' => $this->userId,
                'trace' => $e->getTraceAsString(),
            ]);

            // Store failure metadata
            $metadata = [
                'status' => 'failed',
                'error' => $e->getMessage(),
                'user_id' => $this->userId,
                'created_at' => now()->toIso8601String(),
            ];
            cache()->put('export_metadata_' . ($this->jobUuid ?? 'failed'), $metadata, now()->addHours(24));

            throw $e;
        }
    }

    /**
     * Get estimated record count for metadata
     */
    protected function getEstimatedRecordCount(): int
    {
        try {
            $query = \App\Models\RegistroGlobal::query();

            if (!empty($this->years)) {
                $query->whereIn('ano', $this->years);
            }

            if (!empty($this->months)) {
                $query->whereIn('mes', $this->months);
            }

            if (!empty($this->filters['global_search'])) {
                $search = $this->filters['global_search'];
                $query->where(function ($q) use ($search) {
                    $q->where('medico', 'LIKE', "%{$search}%")
                        ->orWhere('diagnostico_1', 'LIKE', "%{$search}%")
                        ->orWhere('colonia', 'LIKE', "%{$search}%")
                        ->orWhere('exp', 'LIKE', "%{$search}%");
                });
            }

            // Apply additional column filters
            $filters = $this->filters;
            unset($filters['global_search']);
            if (!empty($filters)) {
                foreach ($filters as $column => $value) {
                    if (!empty($value)) {
                        if (is_array($value)) {
                            $query->whereIn($column, $value);
                        }
                        else {
                            $query->where($column, 'LIKE', "%{$value}%");
                        }
                    }
                }
            }

            return $query->count();
        }
        catch (\Exception $e) {
            return 0;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(\Throwable $exception): void
    {
        Log::error('Export job permanently failed: ' . $exception->getMessage(), [
            'job_uuid' => $this->jobUuid ?? 'unknown',
            'user_id' => $this->userId,
        ]);
    }
}
