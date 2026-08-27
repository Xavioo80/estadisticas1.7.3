<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class CategoriaDocumentacion extends Model
{
    use HasFactory;

    protected $table = 'categorias_documentacion';

    protected $fillable = [
        'nombre',
        'color',
        'icono',
        'parent_id',
    ];

    // Documentos directamente en esta carpeta
    public function documentos()
    {
        return $this->hasMany(Documentacion::class, 'categoria_id');
    }

    // Subcarpetas de esta carpeta
    public function subcarpetas()
    {
        return $this->hasMany(CategoriaDocumentacion::class, 'parent_id')->with('subcarpetas', 'documentos');
    }

    // Carpeta padre
    public function parent()
    {
        return $this->belongsTo(CategoriaDocumentacion::class, 'parent_id');
    }
}
