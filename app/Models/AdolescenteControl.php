<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class AdolescenteControl extends Model
{
    use HasFactory;

    protected $table = 'adolescentes_control';

    protected $fillable = [
        'no_expediente',
        'nombre_completo',
        'sexo',
        'fecha_nacimiento',
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
        'ocupacion',
        'fecha_consulta',
        'diagnostico_seguimiento'
    ];
}
