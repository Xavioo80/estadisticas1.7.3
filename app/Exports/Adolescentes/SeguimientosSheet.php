<?php

namespace App\Exports\Adolescentes;

use App\Models\AdolescenteControl;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class SeguimientosSheet implements FromQuery, WithTitle, WithHeadings, WithMapping, ShouldAutoSize
{
    public function query()
    {
        return AdolescenteControl::query()->orderBy('fecha_consulta', 'desc');
    }

    public function title(): string
    {
        return 'Seguimientos';
    }

    public function headings(): array
    {
        return [
            'No. Expediente',
            'Nombre Completo',
            'DNI',
            'Fecha Consulta',
            'Diagnóstico Seguimiento',
            'Médico Atención',
            'Usuario Registro'
        ];
    }

    public function map($seguimiento): array
    {
        return [
            $seguimiento->no_expediente,
            $seguimiento->nombre_completo,
            $seguimiento->numero_identidad,
            $seguimiento->fecha_consulta ? \Carbon\Carbon::parse($seguimiento->fecha_consulta)->format('d/m/Y') : '',
            $seguimiento->diagnostico_seguimiento,
            $seguimiento->medico_atencion,
            $seguimiento->usuario_registro,
        ];
    }
}
