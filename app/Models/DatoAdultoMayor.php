<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DatoAdultoMayor extends Model
{
    protected $table = 'datos_adulto_mayor';

    protected $fillable = [
        'expediente',
        'nombre_completo',
        'dni',
        'edad',
        'direccion',
        'colonia',
        'telefono',
    ];
}
