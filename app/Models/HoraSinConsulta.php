<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HoraSinConsulta extends Model
{
    use HasFactory;

    protected $fillable = [
        'medico_id', 'ano', 'mes', 'dias_contratados',
        'administrativas_evaluacion', 'reuniones_trabajo', 'cita_ihss', 'taller',
        'capacitaciones', 'incapacidad', 'compensatorio', 'duelo',
        'congresos_medicos', 'charlas_ambiente', 'trabajo_campo', 'promocion',
        'esfam', 'convocatoria_general', 'permiso_personal', 'vacaciones_ordinarias',
        'descanso_profilactico', 'total_vacaciones', 'total_horas_personales',
        'total_horas_oficiales', 'total_horas', 'observaciones'
    ];

    public function medico()
    {
        return $this->belongsTo(Medico::class);
    }
}
