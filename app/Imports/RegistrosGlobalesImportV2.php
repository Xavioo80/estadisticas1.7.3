<?php

namespace App\Imports;

use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

/**
 * Importador optimizado para grandes archivos de Registros AT1
 * Usa chunking para procesar grandes cantidades de datos sin agotar memoria
 */
class RegistrosGlobalesImportV2 implements WithChunkReading, WithHeadingRow, WithCustomCsvSettings
{
    private $totalProcessed = 0;
    private $totalInserted = 0;
    private $totalFailed = 0;
    private $errors = [];
    private $chunkSize = 500;

    // Mapeo de columnas del Excel a la base de datos
    private $columnMapping = [
        'ano' => ['ano', 'año', 'year'],
        'mes' => ['mes', 'month'],
        'numero' => ['numero', 'n°', 'n', 'num', 'numero'],
        'cm' => ['cm'],
        'medico' => ['medico', 'médico', 'doctor', 'dr'],
        'prof' => ['prof', 'profesion', 'profesión', 'profession'],
        'fecha' => ['fecha', 'date'],
        'se' => ['se', 'semana', 'week'],
        'exp' => ['exp', 'expediente', 'expedient'],
        'sexo' => ['sexo', 'sex'],
        'edad' => ['edad', 'age'],
        'tipo' => ['tipo', 'type'],
        'rango' => ['rango', 'range'],
        'rango_2' => ['rango_2', 'range2', 'rango2'],
        'rango_3' => ['rango_3', 'range3', 'rango3'],
        'rango_4' => ['rango_4', 'range4', 'rango4'],
        'rango_5' => ['rango_5', 'range5', 'rango5'],
        'cond' => ['cond', 'condicion', 'condición', 'condition'],
        'cod_col' => ['cod_col', 'codigo_col', 'codcol', 'cod_colonia'],
        'colonia' => ['colonia', 'col', 'community'],
        'cod_1' => ['cod_1', 'codigo_1', 'cod1', 'code1'],
        'diagnostico_1' => ['diagnostico_1', 'diagnóstico_1', 'diagnosis_1', 'diag1'],
        'cond_1' => ['cond_1', 'condicion_1', 'condition1'],
        'sg' => ['sg'],
        'cod_2' => ['cod_2', 'codigo_2', 'cod2', 'code2'],
        'diagnostico_2' => ['diagnostico_2', 'diagnóstico_2', 'diagnosis_2', 'diag2'],
        'cond_2' => ['cond_2', 'condicion_2', 'condition2'],
        'cod_3' => ['cod_3', 'codigo_3', 'cod3', 'code3'],
        'diagnostico_3' => ['diagnostico_3', 'diagnóstico_3', 'diagnosis_3', 'diag3'],
        'cond_3' => ['cond_3', 'condicion_3', 'condition3'],
        'cod_4' => ['cod_4', 'codigo_4', 'cod4', 'code4'],
        'diagnostico_4' => ['diagnostico_4', 'diagnóstico_4', 'diagnosis_4', 'diag4'],
        'cond_4' => ['cond_4', 'condicion_4', 'condition4'],
        'cod_5' => ['cod_5', 'codigo_5', 'cod5', 'code5'],
        'diagnostico_5' => ['diagnostico_5', 'diagnóstico_5', 'diagnosis_5', 'diag5'],
        'cond_5' => ['cond_5', 'condicion_5', 'condition5'],
        'cod_6' => ['cod_6', 'codigo_6', 'cod6', 'code6'],
        'diagnostico_6' => ['diagnostico_6', 'diagnóstico_6', 'diagnosis_6', 'diag6'],
        'cond_6' => ['cond_6', 'condicion_6', 'condition6'],
        'cod_7' => ['cod_7', 'codigo_7', 'cod7', 'code7'],
        'diagnostico_7' => ['diagnostico_7', 'diagnóstico_7', 'diagnosis_7', 'diag7'],
        'cond_7' => ['cond_7', 'condicion_7', 'condition7'],
        'referido_a' => ['referido_a', 'referido a', 'referred_to'],
        'referido_de' => ['referido_de', 'referido de', 'referred_from'],
        'pg_emb' => ['pg_emb', 'pg/emb', 'pgemb', 'poblacion', 'population'],
        'jornada' => ['jornada', 'shift', 'turno'],
        'sm' => ['sm'],
        'sg2' => ['sg2'],
    ];

    // Campos que no deben convertirse a mayúsculas
    private $noUpperFields = ['ano', 'fecha', 'edad', 'se', 'numero', 'exp',
        'cod_1', 'cod_2', 'cod_3', 'cod_4', 'cod_5', 'cod_6', 'cod_7', 'cod_col'];

    public function chunkSize(): int
    {
        return $this->chunkSize;
    }

    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ',',
            'enclosure' => '"',
            'input_encoding' => 'UTF-8',
        ];
    }

    public function model(array $row)
    {
        $this->totalProcessed++;

        $data = $this->mapRowToColumns($row);

        // Validar que tenga al menos un dato válido
        if (!$this->isValidRow($data)) {
            $this->totalFailed++;
            return null;
        }

        // Agregar timestamps
        $now = now();
        $data['created_at'] = $now;
        $data['updated_at'] = $now;

        try {
            DB::table('registros_globales')->insert($data);
            $this->totalInserted++;
        }
        catch (\Exception $e) {
            $this->totalFailed++;
            $this->errors[] = "Error en fila {$this->totalProcessed}: " . $e->getMessage();
            Log::error("Error inserting row: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Map row data to database columns
     */
    private function mapRowToColumns(array $row): array
    {
        $mapped = [];
        $normalizedRow = [];

        // Normalizar keys del row
        foreach ($row as $key => $value) {
            $normalizedRow[$this->normalize($key)] = $value;
        }

        // Mapear cada campo
        foreach ($this->columnMapping as $dbField => $excelVariants) {
            foreach ($excelVariants as $variant) {
                $normalizedVariant = $this->normalize($variant);
                if (isset($normalizedRow[$normalizedVariant]) &&
                $normalizedRow[$normalizedVariant] !== null &&
                $normalizedRow[$normalizedVariant] !== '') {
                    $mapped[$dbField] = $this->transformValue($dbField, $normalizedRow[$normalizedVariant]);
                    break;
                }
            }
        }

        return $mapped;
    }

    /**
     * Normalizar nombre de columna
     */
    private function normalize(string $str): string
    {
        if (is_null($str))
            return '';
        // Convertir a minúsculas, reemplazar caracteres especiales
        $str = mb_strtolower(trim((string)$str), 'UTF-8');
        // Reemplazar caracteres especiales comunes
        $str = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ', 'ü', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ', 'Ü'],
        ['a', 'e', 'i', 'o', 'u', 'n', 'u', 'A', 'E', 'I', 'O', 'U', 'N', 'U'], $str);
        // Reemplazar caracteres no alfanuméricos con underscore
        $str = preg_replace('/[^a-z0-9]/', '_', $str);
        // Eliminar underscores重复
        $str = preg_replace('/_+/', '_', $str);
        return trim($str, '_');
    }

    /**
     * Transformar valor según el tipo de campo
     */
    private function transformValue(string $field, $value)
    {
        // Manejar fechas
        if ($field === 'fecha') {
            try {
                if (is_numeric($value)) {
                    // Excel date serial
                    return \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value)->format('Y-m-d');
                }
                // Intentar parsear como fecha
                $parsed = Carbon::parse($value);
                return $parsed->format('Y-m-d');
            }
            catch (\Exception $e) {
                return null;
            }
        }

        // Campos numéricos
        $numericFields = ['ano', 'edad', 'se', 'numero', 'exp', 'cod_1', 'cod_2', 'cod_3',
            'cod_4', 'cod_5', 'cod_6', 'cod_7', 'cod_col', 'cm'];

        if (in_array($field, $numericFields)) {
            if (is_numeric($value)) {
                return (int)$value;
            }
            return null;
        }

        // Campos que no deben ser mayúsculas
        if (in_array($field, $this->noUpperFields)) {
            return trim($value);
        }

        // Convertir a mayúsculas para el resto
        return mb_strtoupper(trim((string)$value), 'UTF-8');
    }

    /**
     * Validar que la fila tenga datos útiles
     */
    private function isValidRow(array $data): bool
    {
        return !empty($data['fecha']) ||
            !empty($data['medico']) ||
            !empty($data['diagnostico_1']) ||
            !empty($data['exp']) ||
            !empty($data['ano']);
    }

    /**
     * Obtener estadísticas de la importación
     */
    public function getStats(): array
    {
        return [
            'total_processed' => $this->totalProcessed,
            'total_inserted' => $this->totalInserted,
            'total_failed' => $this->totalFailed,
            'success_rate' => $this->totalProcessed > 0
            ? round(($this->totalInserted / $this->totalProcessed) * 100, 2)
            : 0,
            'errors' => array_slice($this->errors, 0, 50), // Limitar a 50 errores
            'errors_count' => count($this->errors),
        ];
    }
}
