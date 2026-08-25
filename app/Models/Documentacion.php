<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Documentacion extends Model
{
    use HasFactory;

    protected $table = 'documentacions';

    protected $fillable = [
        'nombre_original',
        'nombre_archivo',
        'ruta',
        'extension',
        'tamano',
        'descripcion',
        'categoria_id',
    ];

    public function categoria()
    {
        return $this->belongsTo(CategoriaDocumentacion::class, 'categoria_id');
    }
}

