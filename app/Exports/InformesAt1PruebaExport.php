<?php

namespace App\Exports;

use App\Models\RegistroGlobalPrueba;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Carbon\Carbon;

class InformesAt1PruebaExport implements FromQuery, WithHeadings, WithMapping, WithChunkReading
{
    protected $anos;
    protected $meses;
    protected $search;
    protected $filters;
    protected $selectedColumns;

    public function __construct($anos = [], $meses = [], $search = null, $filters = [], $selectedColumns = [])
    {
        $this->anos = (array) $anos;
        $this->meses = (array) $meses;
        $this->search = $search;
        $this->filters = (array) $filters;
        $this->selectedColumns = (array) $selectedColumns;
    }

    public function query()
    {
        $query = RegistroGlobalPrueba::query();

        if (!empty($this->anos)) {
            $query->whereIn('ano', $this->anos);
        }
        if (!empty($this->meses)) {
            $query->whereIn('mes', $this->meses);
        }

        $filters = $this->filters;

        // Diagnósticos múltiples: buscar en diagnostico_1 y diagnostico_2
        if (!empty($filters['diagnosticos'])) {
            $keywords = (array)$filters['diagnosticos'];
            $query->where(function ($q) use ($keywords) {
                foreach ($keywords as $kw) {
                    $q->orWhere('diagnostico_1', 'LIKE', "%{$kw}%")
                      ->orWhere('diagnostico_2', 'LIKE', "%{$kw}%");
                }
            });
            unset($filters['diagnosticos']);
        }

        if ($this->search) {
            $search = $this->search;
            $query->where(function ($q) use ($search) {
                $q->where('medico', 'LIKE', "%$search%")
                    ->orWhere('colonia', 'LIKE', "%$search%")
                    ->orWhere('numero', 'LIKE', "%$search%")
                    ->orWhere('exp', 'LIKE', "%$search%")
                    ->orWhere('diagnostico_1', 'LIKE', "%$search%")
                    ->orWhere('diagnostico_2', 'LIKE', "%$search%");
            });
        }

        // Otros filtros (whereIn para arrays)
        $allowedColumns = [
            'numero','medico','cm','prof','fecha','se','exp','sexo','edad','tipo',
            'rango','cond','colonia','cod_1','diagnostico_1','cond_1',
            'cod_2','diagnostico_2','cond_2','referido_a','referido_de',
            'pg_emb','jornada','sm'
        ];

        foreach ($filters as $column => $value) {
            if (!empty($value) && in_array($column, $allowedColumns)) {
                $query->whereIn($column, (array)$value);
            }
        }

        return $query->orderBy('fecha', 'desc')
                    ->orderBy('medico', 'asc')
                    ->orderByRaw('CAST(numero AS UNSIGNED) ASC');
    }

    protected function getColumnMap()
    {
        return [
            'numero' => 'NÚMERO',
            'medico' => 'MÉDICO',
            'cm' => 'CM',
            'prof' => 'PROFESIÓN',
            'fecha' => 'FECHA',
            'se' => 'SE',
            'exp' => 'EXPEDIENTE',
            'sexo' => 'SEXO',
            'edad' => 'EDAD',
            'tipo' => 'TIPO',
            'rango' => 'RANGO',
            'cond' => 'CONDICIÓN',
            'colonia' => 'COLONIA',
            'cod_1' => 'CÓD. CIE 1',
            'diagnostico_1' => 'DIAG. 1',
            'cond_1' => 'COND. 1',
            'cod_2' => 'CÓD. CIE 2',
            'diagnostico_2' => 'DIAG. 2',
            'cond_2' => 'COND. 2',
            'referido_a' => 'REFERIDO A',
            'referido_de' => 'REFERIDO DE',
            'pg_emb' => 'PG EMB',
            'jornada' => 'JORNADA',
            'sm' => 'SM'
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

    public function map($registro): array
    {
        $map = $this->getColumnMap();
        $cols = empty($this->selectedColumns) ? array_keys($map) : $this->selectedColumns;

        $row = [];
        foreach ($cols as $col) {
            $val = $registro->{$col};
            if ($col === 'fecha' && $val) {
                try {
                    $val = Carbon::parse($val)->format('Y-m-d');
                } catch (\Throwable $e) {}
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
