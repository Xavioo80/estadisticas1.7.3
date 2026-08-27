<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class HoraMedicoExport implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function collection()
    {
        return collect($this->data);
    }

    public function headings(): array
    {
        return [
            'AÑO',
            'MES',
            'JORNADA',
            'CÓDIGO',
            'NOMBRE MÉDICO',
            'ESPECIALIDAD',
            'ATENCIONES',
            'HRS DIARIAS',
            'DÍAS CUMP.',
            'HRS CUMP.',
            'PROG.',
            'REPR.',
            'RENDIMIENTO %',
            'OFICIALES',
            'VACACIONES',
            'PERSONALES',
            'OBSERVACIONES'
        ];
    }

    public function map($row): array
    {
        return [
            $row['ano'],
            $row['mes'],
            $row['jornada'],
            $row['medico']->COD_MED,
            $row['medico']->NOM_MED,
            $row['medico']->ESPECIALIDAD,
            $row['atenciones'],
            $row['horasPorDia'],
            $row['diasCumplidos'],
            $row['horasCumplidas'],
            $row['prog'],
            $row['repr'],
            round($row['rendimiento'], 0) . '%',
            $row['totalOfic'],
            $row['totalVac'],
            $row['totalPers'],
            trim(($row['medico']->observaciones ?? '') . (isset($row['hsc']->observaciones) && !empty($row['hsc']->observaciones) ? ', ' . $row['hsc']->observaciones : ''))
        ];
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => ['font' => ['bold' => true, 'color' => ['rgb' => 'FFFFFF']], 'fill' => ['fillType' => 'solid', 'startColor' => ['rgb' => '333333']]],
        ];
    }
}
