<?php

namespace App\Exports;

use App\Exports\Adolescentes\GeneralSheet;
use App\Exports\Adolescentes\DepuradosSheet;
use App\Exports\Adolescentes\SeguimientosSheet;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;

class AdolescentesExport implements WithMultipleSheets
{
    public function sheets(): array
    {
        $sheets = [];

        $sheets[] = new GeneralSheet();
        $sheets[] = new DepuradosSheet();
        $sheets[] = new SeguimientosSheet();

        return $sheets;
    }
}
