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

    public function getExisteFisicamenteAttribute()
    {
        $paths = [
            storage_path('app/public/' . $this->ruta),
            storage_path('app/' . $this->ruta),
            storage_path('app/public/documentacion/' . $this->nombre_archivo),
            storage_path('app/documentacion/' . $this->nombre_archivo),
            public_path('storage/' . $this->ruta),
            public_path($this->ruta),
        ];

        foreach ($paths as $path) {
            if (file_exists($path) && is_file($path)) {
                return true;
            }
        }

        return false;
    }
}


