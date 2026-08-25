<?php

namespace App\Models;

use App\Enums\Sexo;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RegistroGlobal extends Model
{
    protected $table = 'registros_globales';
    public $timestamps = false;

    protected $fillable = [
        'ano',
        'mes',
        'numero',
        'cm',
        'medico',
        'user_id',
        'prof',
        'fecha',
        'se',
        'exp',
        'sexo',
        'edad',
        'tipo',
        'rango',
        'rango_2',
        'rango_3',
        'rango_4',
        'rango_5',
        'cond',
        'cod_col',
        'colonia',
        'cod_1',
        'diagnostico_1',
        'cond_1',
        'sg',
        'cod_2',
        'diagnostico_2',
        'cond_2',
        'cod_3',
        'diagnostico_3',
        'cond_3',
        'cod_4',
        'diagnostico_4',
        'cond_4',
        'cod_5',
        'diagnostico_5',
        'cond_5',
        'cod_6',
        'diagnostico_6',
        'cond_6',
        'cod_7',
        'diagnostico_7',
        'cond_7',
        'referido_a',
        'referido_de',
        'pg_emb',
        'jornada',
        'sm',
        'sg2'
    ];

    protected $casts = [
        'ano' => 'integer',
        'edad' => 'integer',
        'se' => 'integer',
    ];

    /**
     * Serializar fecha a formato d-m-Y para JSON
     */
    protected function serializeDate(\DateTimeInterface $date)
    {
        return $date->format('d-m-Y');
    }

    public function setAttribute($key, $value)
    {
        $excludedFields = ['fecha', 'created_at', 'updated_at', 'id', 'ano', 'edad', 'numero', 'se'];

        if (is_string($value) && !in_array($key, $excludedFields) && $value !== null) {
            $value = mb_strtoupper($value, 'UTF-8');
        }

        return parent::setAttribute($key, $value);
    }

    /**
     * Get the user that created this record.
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopePorPeriodo($query, int $ano, ?string $mes = null)
    {
        $query->where('ano', $ano);
        if ($mes)
            $query->where('mes', $mes);
        return $query;
    }

    public function scopeConDiagnosticos($query)
    {
        return $query->where(function ($q) {
            for ($i = 1; $i <= 7; $i++) {
                $q->orWhereNotNull("cod_{$i}")
                    ->orWhereNotNull("diagnostico_{$i}");
            }
        });
    }

    public function scopeRecientes($query, int $limit = 100)
    {
        return $query->orderBy('fecha', 'desc')->limit($limit);
    }
}
