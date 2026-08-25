<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Diagnostico extends Model
{
    use HasFactory;

    protected $fillable = [
        'codigo', 
        'patologia', 
        'auxiliar',
        'secundario', 
        'categoria',
        // Columnas de validación
        'edad_minima',
        'edad_maxima',
        'tipo_edad',
        'sexo_permitido',
        'requiere_embarazo',
        'es_pediatrico',
        'es_adulto',
        'notas_validacion',
        'validaciones_actualizadas_en',
        'validaciones_actualizadas_por'
    ];

    protected $casts = [
        'requiere_embarazo' => 'boolean',
        'es_pediatrico' => 'boolean',
        'es_adulto' => 'boolean',
        'validaciones_actualizadas_en' => 'datetime'
    ];

    protected $table = 'diagnosticos';
}
