<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Informe extends Model
{
    protected $table = 'informes';
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id', 'registro_id', 'diag_index',
        'ano', 'mes', 'numero', 'cm', 'medico', 'prof',
        'fecha', 'se', 'exp',
        'identidad', 'nombre_paciente', 'telefono', 'fecha_nacimiento', 'etnia',
        'sexo', 'edad', 'tipo',
        'rango', 'rango_2', 'rango_3', 'rango_4', 'rango_5', 'cond',
        'cod_col', 'colonia',
        'cod', 'diagnostico', 'cond_diagnostico',
        'sg', 'referido_a', 'referido_de', 'pg_emb', 'jornada', 'sm', 'sg2',
    ];

    protected $casts = [
        'ano' => 'integer',
        'se' => 'integer',
        'edad' => 'integer',
        'diag_index' => 'integer',
        'fecha_nacimiento' => 'date',
    ];
    
    /**
     * Serializar fecha a formato d-m-Y para JSON
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('d-m-Y');
    }
}
