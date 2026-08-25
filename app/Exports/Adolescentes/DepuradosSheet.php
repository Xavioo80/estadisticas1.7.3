<?php

namespace App\Exports\Adolescentes;

use App\Models\Adolescente;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Carbon\Carbon;

class DepuradosSheet implements FromQuery, WithTitle, WithHeadings, WithMapping, ShouldAutoSize
{
    public function query()
    {
        $hoy = Carbon::now();
        return Adolescente::query()
            ->whereRaw("TIMESTAMPDIFF(YEAR, fecha_nacimiento, ?) NOT BETWEEN 10 AND 19", [$hoy->format('Y-m-d')])
            ->orderBy('fecha_nacimiento', 'desc');
    }

    public function title(): string
    {
        return 'Depurados (Fuera de Rango)';
    }

    public function headings(): array
    {
        return [
            'No. Expediente',
            'Nombre Completo',
            'Sexo',
            'Fecha Nacimiento',
            'Edad Actual',
            'DNI',
            'Colonia',
            'Teléfono'
        ];
    }

    public function map($adolescente): array
    {
        $edadActual = $adolescente->fecha_nacimiento ? Carbon::parse($adolescente->fecha_nacimiento)->age : 'N/A';
        
        return [
            $adolescente->no_expediente,
            $adolescente->nombre_completo,
            $adolescente->sexo,
            $adolescente->fecha_nacimiento ? $adolescente->fecha_nacimiento->format('d/m/Y') : '',
            $edadActual,
            $adolescente->numero_identidad,
            $adolescente->colonia,
            $adolescente->numero_telefono,
        ];
    }
}
