<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ImportacionColoniaAlias extends Model
{
    protected $table = 'importacion_colonias_alias';

    protected $fillable = [
        'texto_original',
        'texto_normalizado',
        'cod_col',
        'colonia',
        'colonia_id',
        'veces_usado',
    ];
}
