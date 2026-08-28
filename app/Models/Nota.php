<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Nota extends Model
{
    use HasFactory;

    protected $table = 'notas';

    protected $fillable = [
        'user_id',
        'assigned_user_id',
        'assigned_user_name',
        'titulo',
        'contenido',
        'tipo',
        'checklist_items',
        'etiqueta',
        'color',
        'captura_url',
        'pinned',
    ];

    protected $casts = [
        'checklist_items' => 'array',
        'pinned' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function assignedUser()
    {
        return $this->belongsTo(User::class, 'assigned_user_id');
    }
}
