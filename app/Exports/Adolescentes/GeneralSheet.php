<?php

namespace App\Exports\Adolescentes;

use App\Models\Adolescente;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;

class GeneralSheet implements FromQuery, WithTitle, WithHeadings, WithMapping, ShouldAutoSize
{
    public function query()
    {
        return Adolescente::query()->orderBy('fecha_ingreso', 'desc');
    }

    public function title(): string
    {
        return 'Base General';
    }

    public function headings(): array
    {
        return [
            'No. Expediente',
            'Nombre Completo',
            'Sexo',
            'Fecha Nacimiento',
            'Fecha Ingreso',
            'Edad',
            'DNI',
            'Colonia',
            'Tutor',
            'Teléfono',
            'Estado Civil',
            'Escolaridad',
            'Años Cursados',
            'Ocupación',
            'Médico Atención',
            'Usuario Registro'
        ];
    }

    public function map($adolescente): array
    {
        return [
            $adolescente->no_expediente,
            $adolescente->nombre_completo,
            $adolescente->sexo,
            $adolescente->fecha_nacimiento ? $adolescente->fecha_nacimiento->format('d/m/Y') : '',
            $adolescente->fecha_ingreso ? $adolescente->fecha_ingreso->format('d/m/Y') : '',
            $adolescente->edad,
            $adolescente->numero_identidad,
            $adolescente->colonia,
            $adolescente->nombre_tutor,
            $adolescente->numero_telefono,
            $adolescente->estado_civil,
            $adolescente->escolaridad,
            $adolescente->anios_cursados,
            $adolescente->ocupacion,
            $adolescente->medico_atencion,
            $adolescente->usuario_registro,
        ];
    }
}
