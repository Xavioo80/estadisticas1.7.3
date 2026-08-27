<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class NotificacionSvs extends Model
{
    use HasFactory;

    protected $table = 'notificaciones_svs';

    protected $fillable = [
        'informe_id',
        'registro_id',
        'ano',
        'mes',
        'se',
        'fecha_consulta',
        'fecha_inicio_sintomas',
        'expediente',
        'tipo_documento',
        'no_documento',
        'nombres',
        'apellidos',
        'fecha_nacimiento',
        'edad',
        'tipo_edad',
        'sexo',
        'telefono',
        'departamento',
        'municipio',
        'direccion',
        'colonia',
        'medico',
        'diagnostico_consignado',
        'enfermedad_svs',
        'observaciones',
        'estado_notificacion',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
