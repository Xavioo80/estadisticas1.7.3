<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Medico extends Model
{
    use HasFactory;

    protected $fillable = [
        'COD_MED', 'NOM_MED', 'JORNADA', 'NOMINA', 
        'ESPECIALIDAD', 'MODALIDAD', 'FECHA_INGRESO',
        'CORREO', 'TELEFONO', 'HORAS_CONTRATADAS', 'CONSULTAS', 
        'consultas_por_hora', 'consultas_dia', 'estado', 'observaciones',
        'es_ong', 'es_director'
    ];

    protected $casts = [
        'FECHA_INGRESO' => 'date',
        'HORAS_CONTRATADAS' => 'decimal:2',
        'es_ong' => 'boolean',
        'es_director' => 'boolean'
    ];

    public static $rules = [
        'COD_MED' => 'required|unique:medicos',
        'NOM_MED' => 'required|string|max:100',
        'JORNADA' => 'required|in:MATUTINA,VESPERTINA,FIN DE SEMANA',
        'estado' => 'required|in:activo,inactivo'
    ];
}
