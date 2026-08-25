<?php
 
namespace App\Models;
 
use Illuminate\Database\Eloquent\Model;
 
class InformePrueba extends Model
{
    protected $table = 'informes_prueba';
    public $timestamps = false;
    public $incrementing = false;
    protected $keyType = 'string';
 
    protected $casts = [
        'ano' => 'integer',
        'se' => 'integer',
        'edad' => 'integer',
        'diag_index' => 'integer'
    ];
    
    /**
     * Serializar fecha a formato d-m-Y para JSON
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('d-m-Y');
    }
}
