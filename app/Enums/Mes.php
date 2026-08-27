<?php

namespace App\Enums;

enum Mes: string
{
    case ENERO = 'ENERO';
    case FEBRERO = 'FEBRERO';
    case MARZO = 'MARZO';
    case ABRIL = 'ABRIL';
    case MAYO = 'MAYO';
    case JUNIO = 'JUNIO';
    case JULIO = 'JULIO';
    case AGOSTO = 'AGOSTO';
    case SEPTIEMBRE = 'SEPTIEMBRE';
    case OCTUBRE = 'OCTUBRE';
    case NOVIEMBRE = 'NOVIEMBRE';
    case DICIEMBRE = 'DICIEMBRE';

    public function numero(): int
    {
        return match($this) {
            self::ENERO => 1,
            self::FEBRERO => 2,
            self::MARZO => 3,
            self::ABRIL => 4,
            self::MAYO => 5,
            self::JUNIO => 6,
            self::JULIO => 7,
            self::AGOSTO => 8,
            self::SEPTIEMBRE => 9,
            self::OCTUBRE => 10,
            self::NOVIEMBRE => 11,
            self::DICIEMBRE => 12,
        };
    }
}
