<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Adolescente extends Model
{
    use HasFactory;

    protected $table = 'adolescentes';

    protected $fillable = [
        'no_expediente',
        'nombre_completo',
        'sexo',
        'fecha_nacimiento',
        'fecha_ingreso',
        'edad',
        'numero_identidad',
        'colonia',
        'medico_atencion',
        'usuario_registro',
        'nombre_tutor',
        'direccion_completa',
        'numero_telefono',
        'estado_civil',
        'escolaridad',
        'anios_cursados',
        'ocupacion'
    ];

    protected $casts = [
        'fecha_nacimiento' => 'date:Y-m-d',
        'fecha_ingreso' => 'date:Y-m-d',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];
}
