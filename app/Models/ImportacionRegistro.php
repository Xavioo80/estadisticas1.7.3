<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ImportacionRegistro extends Model
{
    use HasFactory;

    protected $table = 'importacion_registros';

    protected $fillable = [
        'importacion_id',
        'fila_excel',
        'hash_registro',
        'fecha_atencion',
        'medico',
        'cm',
        'prof',
        'numero_identidad',
        'identidad_original',
        'nombre_paciente',
        'fecha_nacimiento',
        'edad',
        'tipo',
        'sexo',
        'expediente',
        'telefono',
        'direccion_original',
        'colonia_normalizada',
        'cod_col',
        'colonia_id',
        'diagnosticos_json',
        'datos_originales_json',
        'datos_normalizados_json',
        'paciente_id',
        'registro_global_id',
        'estado',
        'motivo_estado',
        'requiere_revision',
    ];

    protected $casts = [
        'fecha_atencion' => 'date',
        'fecha_nacimiento' => 'date',
        'diagnosticos_json' => 'array',
        'datos_originales_json' => 'array',
        'datos_normalizados_json' => 'array',
        'requiere_revision' => 'boolean',
    ];

    public function importacion()
    {
        return $this->belongsTo(Importacion::class, 'importacion_id');
    }

    public function paciente()
    {
        return $this->belongsTo(Paciente::class, 'paciente_id');
    }

    public function registroGlobal()
    {
        return $this->belongsTo(RegistroGlobal::class, 'registro_global_id');
    }

    public function colonia()
    {
        return $this->belongsTo(Colonia::class, 'colonia_id');
    }
}
