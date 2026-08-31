<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class AlertaNotEnlace extends Model
{
    use HasFactory;

    protected $table = 'alerta_not_enlaces';

    protected $fillable = [
        'titulo',
        'url',
        'icono',
        'descripcion',
        'orden',
        'is_active',
    ];

    protected $casts = [
        'orden' => 'integer',
        'is_active' => 'boolean',
    ];
}
