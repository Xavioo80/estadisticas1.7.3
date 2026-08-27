<?php

namespace App\Exports;

use App\Models\RegistroGlobal;
use Illuminate\Database\Eloquent\Builder;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithProperties;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

/**
 * Optimized Chunked Export for Large Datasets
 * 
 * This export class uses Laravel Excel's chunked reading to process
 * large datasets in batches, minimizing memory usage and preventing timeouts.
 * 
 * Key optimizations:
 * - Chunk reading: Process 1000 records at a time
 * - Lazy evaluation: Only load what's needed
 * - Memory efficient: Don't load entire dataset into memory
 * - Configurable: Can be used with FromQuery or FromCollection
 */
class OptimizedChunkedExport implements
    FromQuery,
    WithHeadings,
    WithMapping,
    WithChunkReading,
    WithTitle,
    WithProperties
{
    use Exportable;

    /**
     * Filters to apply to the export
     */
    protected array $filters;

    /**
     * Callback for progress updates
     */
    protected $progressCallback;

    /**
     * Chunk size for processing
     * Smaller chunks = less memory usage but more queries
     * Larger chunks = more memory but faster processing
     */
    protected int $chunkSize = 1000;

    /**
     * Heading columns
     */
    protected array $headings = [
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

    /**
     * Create a new export instance
     */
    public function __construct(array $filters = [], ?callable $progressCallback = null)
    {
        $this->filters = $filters;
        $this->progressCallback = $progressCallback;
    }

    /**
     * Build the query with filters
     * This is the core of the chunked export - the query is built once
     * and then executed in chunks
     */
    public function query(): Builder
    {
        $query = RegistroGlobal::query();

        // Apply year filter
        if (!empty($this->filters['years'])) {
            $query->whereIn('ano', (array)$this->filters['years']);
        }

        // Apply month filter
        if (!empty($this->filters['months'])) {
            $query->whereIn('mes', (array)$this->filters['months']);
        }

        // Apply global search - search ALL columns
        if (!empty($this->filters['global_search'])) {
            $search = $this->filters['global_search'];
            $searchableColumns = [
                'id', 'ano', 'mes', 'numero', 'cm', 'medico', 'prof', 'fecha', 'se', 'exp',
                'sexo', 'edad', 'tipo', 'rango', 'rango_2', 'rango_3', 'rango_4', 'rango_5', 'cond',
                'cod_col', 'colonia',
                'cod_1', 'diagnostico_1', 'cond_1', 'sg',
                'cod_2', 'diagnostico_2', 'cond_2',
                'cod_3', 'diagnostico_3', 'cond_3',
                'cod_4', 'diagnostico_4', 'cond_4',
                'cod_5', 'diagnostico_5', 'cond_5',
                'cod_6', 'diagnostico_6', 'cond_6',
                'cod_7', 'diagnostico_7', 'cond_7',
                'referido_a', 'referido_de', 'pg_emb', 'jornada', 'sm', 'sg2'
            ];

            $query->where(function ($q) use ($search, $searchableColumns) {
                foreach ($searchableColumns as $column) {
                    $q->orWhere($column, 'LIKE', "%{$search}%");
                }
            });
        }

        // Apply additional column filters
        if (!empty($this->filters['columnFilters']) && is_array($this->filters['columnFilters'])) {
            foreach ($this->filters['columnFilters'] as $column => $value) {
                if (!empty($value) && in_array($column, $this->getFilterableColumns())) {
                    if (is_array($value)) {
                        $query->whereIn($column, $value);
                    }
                    else {
                        $query->where($column, 'LIKE', "%{$value}%");
                    }
                }
            }
        }

        // Order by date and number for consistent results
        return $query->orderBy('fecha', 'desc')->orderBy('numero', 'asc');
    }

    /**
     * Get list of filterable columns
     */
    protected function getFilterableColumns(): array
    {
        return [
            'medico', 'prof', 'sexo', 'tipo', 'rango', 'cond',
            'colonia', 'cod_1', 'diagnostico_1', 'cond_1',
            'cod_2', 'diagnostico_2', 'cond_2', 'cod_3', 'diagnostico_3', 'cond_3',
            'cod_4', 'diagnostico_4', 'cond_4', 'cod_5', 'diagnostico_5', 'cond_5',
            'cod_6', 'diagnostico_6', 'cond_6', 'cod_7', 'diagnostico_7', 'cond_7',
            'referido_a', 'referido_de', 'jornada', 'sm', 'sg', 'se', 'exp'
        ];
    }

    /**
     * Map each record to an array
     * This method is called for each record in the chunk
     */
    public function map($registro): array
    {
        return [
            $registro->id,
            $registro->ano,
            $registro->mes,
            $registro->numero,
            $registro->cm,
            $registro->medico,
            $registro->prof,
            $registro->fecha ? $registro->fecha->format('Y-m-d') : '',
            $registro->se,
            $registro->exp,
            $registro->sexo,
            $registro->edad,
            $registro->tipo,
            $registro->rango,
            $registro->rango_2,
            $registro->rango_3,
            $registro->rango_4,
            $registro->rango_5,
            $registro->cond,
            $registro->cod_col,
            $registro->colonia,
            $registro->cod_1,
            $registro->diagnostico_1,
            $registro->cond_1,
            $registro->sg,
            $registro->cod_2,
            $registro->diagnostico_2,
            $registro->cond_2,
            $registro->cod_3,
            $registro->diagnostico_3,
            $registro->cond_3,
            $registro->cod_4,
            $registro->diagnostico_4,
            $registro->cond_4,
            $registro->cod_5,
            $registro->diagnostico_5,
            $registro->cond_5,
            $registro->cod_6,
            $registro->diagnostico_6,
            $registro->cond_6,
            $registro->cod_7,
            $registro->diagnostico_7,
            $registro->cond_7,
            $registro->referido_a,
            $registro->referido_de,
            $registro->pg_emb,
            $registro->jornada,
            $registro->sm,
        ];
    }

    /**
     * Define the headings for the export
     */
    public function headings(): array
    {
        return $this->headings;
    }

    /**
     * Chunk size for reading
     * This is crucial for memory management with large datasets
     */
    public function chunkSize(): int
    {
        return $this->chunkSize;
    }

    /**
     * Set custom chunk size
     */
    public function setChunkSize(int $size): self
    {
        $this->chunkSize = $size;
        return $this;
    }

    /**
     * Set the worksheet title
     */
    public function title(): string
    {
        return 'Registros_AT1';
    }

    /**
     * Set document properties
     */
    public function properties(): array
    {
        return [
            'creator' => 'Sistema de Estadísticas',
            'title' => 'Exportación de Registros AT1',
            'description' => 'Exportación de registros globales filtrados',
            'subject' => 'Registros de Atención',
            'keywords' => 'registros,atención,exportación',
            'category' => 'Reportes',
        ];
    }

    /**
     * Register hooks for additional processing
     */
    public function registerHooks(): void
    {
    // This can be used for additional processing before/after export
    }
}
