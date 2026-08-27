<?php

namespace App\Exports;

use App\Models\Informe;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class InformesAt1Export implements FromQuery, WithHeadings, WithMapping, WithChunkReading
{
    protected $anos;
    protected $meses;
    protected $filters;
    protected $selectedColumns;

    public function __construct($anos = null, $meses = null, $filters = [], $selectedColumns = [])
    {
        $this->anos = $anos;
        $this->meses = $meses;
        $this->filters = $filters;
        $this->selectedColumns = $selectedColumns;
    }

    public function query()
    {
        $query = Informe::query();

        if (!empty($this->anos)) {
            $query->whereIn('ano', (array)$this->anos);
        }
        if (!empty($this->meses)) {
            $query->whereIn('mes', (array)$this->meses);
        }

        $filters = $this->filters;

        // Diagnósticos múltiples: OR LIKE por cada keyword
        if (!empty($filters['diagnosticos'])) {
            $keywords = (array)$filters['diagnosticos'];
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $kw) {
                    $q->orWhere('diagnostico', 'LIKE', "%{$kw}%");
                }
            });
            unset($filters['diagnosticos']);
        }

        // Búsqueda global
        if (!empty($filters['global_search'])) {
            $search = $filters['global_search'];
            $query->where(function ($q) use ($search) {
                $q->where('medico', 'LIKE', "%{$search}%")
                    ->orWhere('diagnostico', 'LIKE', "%{$search}%")
                    ->orWhere('colonia', 'LIKE', "%{$search}%")
                    ->orWhere('exp', 'LIKE', "%{$search}%");
            });
            unset($filters['global_search']);
        }

        // Filtros de columna (whereIn para arrays, LIKE para texto)
        $allowedColumns = [
            'id','numero','medico','cm','ano','mes','prof','fecha','se','exp','sexo','edad','tipo',
            'rango','rango_2','rango_3','rango_4','rango_5','cond','colonia',
            'cod','diagnostico','cond_diagnostico','sg','referido_a','referido_de','pg_emb','jornada','sm'
        ];
        foreach ($filters as $column => $value) {
            if (!empty($value) && in_array($column, $allowedColumns)) {
                $query->whereIn($column, (array)$value);
            }
        }

        return $query->orderBy('fecha', 'desc')->orderBy('numero', 'asc');
    }

    protected function getColumnMap()
    {
        return [
            'numero' => 'NÚMERO',
            'medico' => 'MÉDICO',
            'id' => 'ID',
            'ano' => 'AÑO',
            'mes' => 'MES',
            'cm' => 'CM',
            'prof' => 'PROFESIÓN',
            'fecha' => 'FECHA',
            'se' => 'SE',
            'exp' => 'EXPEDIENTE',
            'sexo' => 'SEXO',
            'edad' => 'EDAD',
            'tipo' => 'TIPO',
            'rango' => 'RANGO',
            'rango_2' => 'RANGO 2',
            'rango_3' => 'RANGO 3',
            'rango_4' => 'RANGO 4',
            'rango_5' => 'RANGO 5',
            'cond' => 'CONDICIÓN',
            'colonia' => 'COLONIA',
            'cod' => 'CÓDIGO',
            'diagnostico' => 'DIAGNÓSTICO',
            'cond_diagnostico' => 'CND',
            'sg' => 'SG',
            'referido_a' => 'REFERIDO A',
            'referido_de' => 'REFERIDO DE',
            'pg_emb' => 'PG EMB',
            'jornada' => 'JORNADA',
            'sm' => 'SM',
        ];
    }

    public function headings(): array
    {
        $map = $this->getColumnMap();
        if (empty($this->selectedColumns)) {
            return array_values($map);
        }

        return array_map(fn($col) => $map[$col] ?? strtoupper($col), $this->selectedColumns);
    }

    public function map($informe): array
    {
        $map = $this->getColumnMap();
        $cols = empty($this->selectedColumns) ? array_keys($map) : $this->selectedColumns;

        $row = [];
        foreach ($cols as $col) {
            $val = $informe->{$col};
            if ($col === 'fecha' && $val) {
                if (is_string($val) && strlen($val) >= 10 && $val[4] === '-' && $val[7] === '-') {
                    $val = substr($val, 0, 10);
                } else {
                    try {
                        $val = date('Y-m-d', strtotime($val));
                    } catch (\Throwable $e) {
                    }
                }
            }
            $row[] = $val;
        }

        return $row;
    }

    public function chunkSize(): int
    {
        return 1000;
    }
}
