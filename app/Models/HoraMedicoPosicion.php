<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HoraMedicoPosicion extends Model
{
    protected $table = 'hora_medico_posiciones';

    protected $fillable = [
        'ano',
        'mes',
        'jornada',
        'medico_id',
        'posicion'
    ];

    public function medico()
    {
        return $this->belongsTo(Medico::class, 'medico_id');
    }
}
