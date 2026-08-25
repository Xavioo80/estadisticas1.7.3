<?php

namespace App\Enums;

enum Sexo: string
{
    case MASCULINO = 'M';
    case FEMENINO = 'F';
    case HOMBRE = 'H';

    public function label(): string
    {
        return match($this) {
            self::MASCULINO => 'Masculino',
            self::FEMENINO => 'Femenino',
            self::HOMBRE => 'Hombre',
        };
    }
}
