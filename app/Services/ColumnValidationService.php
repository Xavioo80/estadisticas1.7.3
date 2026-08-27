<?php

namespace App\Services;

use Illuminate\Support\Facades\Schema;

class ColumnValidationService
{
    // Lista blanca de columnas permitidas para RegistroGlobal
    private static array $allowedColumns = [
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

    /**
     * Valida si una columna está en la lista blanca
     */
    public static function isValidColumn(string $column): bool
    {
        return in_array($column, self::$allowedColumns, true);
    }

    /**
     * Valida si una columna es un campo de diagnóstico (cod_*, diagnostico_*, cond_*)
     */
    public static function isDiagnosticField(string $column): bool
    {
        return preg_match('/^(cod|diagnostico|cond)_([1-7])$/', $column) === 1;
    }

    /**
     * Obtiene la lista completa de columnas permitidas
     */
    public static function getAllowedColumns(): array
    {
        return self::$allowedColumns;
    }

    /**
     * Valida y sanitiza un nombre de columna. Retorna null si no es válido.
     */
    public static function validateColumn(?string $column): ?string
    {
        if (null === $column || empty($column)) {
            return null;
        }

        if (self::isValidColumn($column)) {
            return $column;
        }

        return null;
    }

    /**
     * Valida múltiples columnas (para filtros)
     */
    public static function validateColumns(array $columns): array
    {
        return array_filter($columns, fn($col) => self::isValidColumn($col));
    }
}
