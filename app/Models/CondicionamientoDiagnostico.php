<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CondicionamientoDiagnostico extends Model
{
    protected $table = 'condicionamientos_diagnosticos';
    
    protected $fillable = [
        'codigo_diagnostico',
        'nombre_diagnostico',
        'embarazo',
        'pediatrico',
        'adulto',
        'edad_min',
        'edad_max',
        'sg_min',
        'sg_max',
        'notas_validacion'
    ];
    
    protected $casts = [
        'embarazo' => 'boolean',
        'pediatrico' => 'boolean',
        'adulto' => 'boolean',
        'edad_min' => 'integer',
        'edad_max' => 'integer',
        'sg_min' => 'integer',
        'sg_max' => 'integer',
    ];
}
