<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Colonia extends Model
{
    use HasFactory;

    protected $fillable = [
        'COD_COL',
        'COLONIA'
    ];

    protected $table = 'colonias';
}
