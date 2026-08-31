<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportacionDiagnosticoAlias extends Model
{
    protected $table = 'importacion_diagnosticos_alias';

    protected $fillable = [
        'texto_original',
        'texto_normalizado',
        'codigo',
        'patologia',
        'diagnostico_id',
        'veces_usado',
    ];
}
