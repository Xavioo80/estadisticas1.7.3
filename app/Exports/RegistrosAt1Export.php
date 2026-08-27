<?php

namespace App\Exports;

use App\Models\RegistroGlobal;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\Exportable;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class RegistrosAt1Export implements FromQuery, WithHeadings, WithMapping, WithChunkReading
{
    protected $anos;
    protected $meses;
    protected $filters;

    public function __construct($anos = null, $meses = null, $filters = [])
    {
        $this->anos = $anos;
        $this->meses = $meses;
        $this->filters = $filters;
    }

    public function query()
    {
        $query = RegistroGlobal::query();

        if (!empty($this->anos)) {
            $query->whereIn('ano', (array)$this->anos);
        }

        if (!empty($this->meses)) {
            $query->whereIn('mes', (array)$this->meses);
        }

        // Búsqueda global - buscar en TODOS los campos
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
            unset($this->filters['global_search']);
        }

        // Aplicar filtros adicionales por columna
        if (!empty($this->filters)) {
            $allowedColumns = [
                'id', 'ano', 'mes', 'numero', 'cm', 'medico', 'prof', 'fecha', 'se', 'exp',
                'sexo', 'edad', 'tipo', 'rango', 'rango_2', 'rango_3', 'rango_4', 'rango_5', 'cond',
                'cod_col', 'colonia', 'cod_1', 'diagnostico_1', 'cond_1', 'sg', 'cod_2', 'diagnostico_2',
                'cond_2', 'cod_3', 'diagnostico_3', 'cond_3', 'cod_4', 'diagnostico_4', 'cond_4',
                'cod_5', 'diagnostico_5', 'cond_5', 'cod_6', 'diagnostico_6', 'cond_6', 'cod_7',
                'diagnostico_7', 'cond_7', 'referido_a', 'referido_de', 'pg_emb', 'jornada', 'sm', 'sg2'
            ];

            foreach ($this->filters as $column => $value) {
                if (!empty($value) && in_array($column, $allowedColumns)) {
                    if (is_array($value)) {
                        $query->whereIn($column, (array)$value);
                    }
                    else {
                        $query->where($column, 'LIKE', "%{$value}%");
                    }
                }
            }
        }

        return $query->orderBy('fecha', 'desc')->orderBy('medico', 'asc')->orderByRaw('CAST(numero AS UNSIGNED) ASC');
    }

    public function headings(): array
    {
        return [
            'ID',
            'AÑO',
            'MES',
            'NÚMERO',
            'CM',
            'MÉDICO',
            'PROFESIÓN',
            'FECHA',
            'SE',
            'EXPEDIENTE',
            'SEXO',
            'EDAD',
            'TIPO',
            'RANGO',
            'RANGO 2',
            'RANGO 3',
            'RANGO 4',
            'RANGO 5',
            'CONDICIÓN',
            'CÓD. COLONIA',
            'COLONIA',
            'CÓDIGO 1',
            'DIAGNÓSTICO 1',
            'COND. 1',
            'SG',
            'CÓDIGO 2',
            'DIAGNÓSTICO 2',
            'COND. 2',
            'CÓDIGO 3',
            'DIAGNÓSTICO 3',
            'COND. 3',
            'CÓDIGO 4',
            'DIAGNÓSTICO 4',
            'COND. 4',
            'CÓDIGO 5',
            'DIAGNÓSTICO 5',
            'COND. 5',
            'CÓDIGO 6',
            'DIAGNÓSTICO 6',
            'COND. 6',
            'CÓDIGO 7',
            'DIAGNÓSTICO 7',
            'COND. 7',
            'REFERIDO A',
            'REFERIDO DE',
            'PG EMB',
            'JORNADA',
            'SM',
        ];
    }

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
            $registro->fecha ? (\Carbon\Carbon::parse($registro->fecha)->format('Y-m-d')) : '',
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
     * Chunk size for processing large datasets
     */
    public function chunkSize(): int
    {
        return 1000;
    }

}
