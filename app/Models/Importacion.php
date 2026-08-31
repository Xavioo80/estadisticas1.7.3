<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Importacion extends Model
{
    use HasFactory;

    protected $table = 'importaciones';

    protected $fillable = [
        'nombre_archivo',
        'hash_archivo',
        'tamano_archivo',
        'total_filas',
        'filas_analizadas',
        'filas_importadas',
        'fechas_disponibles',
        'medicos_disponibles',
        'resumen_estadistico',
        'usuario_id',
        'estado',
    ];

    protected $casts = [
        'fechas_disponibles' => 'array',
        'medicos_disponibles' => 'array',
        'resumen_estadistico' => 'array',
    ];

    public function registros()
    {
        return $this->hasMany(ImportacionRegistro::class, 'importacion_id');
    }

    public function usuario()
    {
        return $this->belongsTo(User::class, 'usuario_id');
    }
}
