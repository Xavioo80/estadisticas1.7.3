<?php

namespace App\Imports;

use App\Models\RegistroGlobal;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithChunkReading;
use Maatwebsite\Excel\Concerns\WithCustomCsvSettings;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RegistrosGlobalesImport implements ToCollection, WithHeadingRow, WithChunkReading, WithCustomCsvSettings
{
    private $columnMapping = [];
    private $headerRow = null;
    
    // Estadísticas de importación
    public $totalProcessed = 0;
    public $totalInserted = 0;
    public $totalFailed = 0;
    public $errors = [];
    public $warnings = [];
    
    /**
     * Mapeo inteligente de columnas - detecta automáticamente las columnas
     */
    public function __construct()
    {
        // Definir posibles nombres de columnas (normalizados)
        $this->columnMapping = [
            'ano' => ['año', 'ano', 'year', 'aÑo'],
            'mes' => ['mes', 'month'],
            'numero' => ['n°', 'n', 'numero', 'número', 'num', 'no'],
            'cm' => ['cm'],
            'medico' => ['medico', 'médico', 'doctor', 'med'],
            'prof' => ['prof', 'profesion', 'profesión'],
            'fecha' => ['fecha', 'date'],
            'se' => ['se'],
            'exp' => ['exp'],
            'sexo' => ['sexo', 'gender'],
            'edad' => ['edad', 'age'],
            'tipo' => ['tipo', 'type'],
            'rango' => ['rango'],
            'rango_2' => ['rango_2', 'rango 2'],
            'rango_3' => ['rango_3', 'rango 3'],
            'rango_4' => ['rango_4', 'rango 4'],
            'rango_5' => ['rango_5', 'rango 5'],
            'cond' => ['cond', 'condicion', 'condición'],
            'cod_col' => ['cod_col', 'codigo_col', 'código_col', 'cod col'],
            'colonia' => ['colonia'],
            'cod_1' => ['cod_1', 'cod 1', 'codigo_1', 'código_1'],
            'diagnostico_1' => ['diagnostico_1', 'diagnóstico_1', 'diagnostico 1', 'diagnóstico 1'],
            'cond_1' => ['cond_1', 'cond 1', 'condicion_1', 'condición_1'],
            'sg' => ['sg'],
            'cod_2' => ['cod_2', 'cod 2', 'codigo_2', 'código_2'],
            'diagnostico_2' => ['diagnostico_2', 'diagnóstico_2', 'diagnostico 2', 'diagnóstico 2'],
            'cond_2' => ['cond_2', 'cond 2', 'condicion_2', 'condición_2'],
            'cod_3' => ['cod_3', 'cod 3', 'codigo_3', 'código_3'],
            'diagnostico_3' => ['diagnostico_3', 'diagnóstico_3', 'diagnostico 3', 'diagnóstico 3'],
            'cond_3' => ['cond_3', 'cond 3', 'condicion_3', 'condición_3'],
            'cod_4' => ['cod_4', 'cod 4', 'codigo_4', 'código_4'],
            'diagnostico_4' => ['diagnostico_4', 'diagnóstico_4', 'diagnostico 4', 'diagnóstico 4'],
            'cond_4' => ['cond_4', 'cond 4', 'condicion_4', 'condición_4'],
            'cod_5' => ['cod_5', 'cod 5', 'codigo_5', 'código_5'],
            'diagnostico_5' => ['diagnostico_5', 'diagnóstico_5', 'diagnostico 5', 'diagnóstico 5'],
            'cond_5' => ['cond_5', 'cond 5', 'condicion_5', 'condición_5'],
            'cod_6' => ['cod_6', 'cod 6', 'codigo_6', 'código_6'],
            'diagnostico_6' => ['diagnostico_6', 'diagnóstico_6', 'diagnostico 6', 'diagnóstico 6'],
            'cond_6' => ['cond_6', 'cond 6', 'condicion_6', 'condición_6'],
            'cod_7' => ['cod_7', 'cod 7', 'codigo_7', 'código_7'],
            'diagnostico_7' => ['diagnostico_7', 'diagnóstico_7', 'diagnostico 7', 'diagnóstico 7'],
            'cond_7' => ['cond_7', 'cond 7', 'condicion_7', 'condición_7'],
            'referido_a' => ['referido_a', 'referido a', 'referidoa'],
            'referido_de' => ['referido_de', 'referido de', 'referidode'],
            'pg_emb' => ['pg/emb', 'pg_emb', 'pg emb', 'pgemb'],
            'jornada' => ['jornada'],
            'sm' => ['sm'],
            'sg2' => ['sg2', 'sg 2'],
        ];
    }
    
    /**
     * Procesar colección de datos
     */
    public function collection(Collection $rows)
    {
        $dataToInsert = [];
        $now = now();
        $rowNumber = 0;
        
        Log::info("Iniciando procesamiento de chunk con " . count($rows) . " filas");
        
        // Log de las columnas detectadas en la primera fila
        if (count($rows) > 0) {
            $firstRow = $rows->first();
            if ($firstRow) {
                $columns = array_keys($firstRow->toArray());
                Log::info("Columnas detectadas en Excel: " . implode(', ', $columns));
            }
        }
        
        foreach ($rows as $row) {
            $rowNumber++;
            $this->totalProcessed++;
            
            try {
                // Convertir row a array y normalizar claves
                /** @var Collection $row */
                $rowArray = $row->toArray();
                $normalizedRow = $this->normalizeRow($rowArray);
                
                // Mapear datos
                $mappedData = $this->mapRowData($normalizedRow);
                
                // Validar datos antes de insertar
                $validation = $this->validateRowData($mappedData, $rowNumber);
                
                if ($validation['valid']) {
                    if (!empty($mappedData)) {
                        $mappedData['created_at'] = $now;
                        $mappedData['updated_at'] = $now;
                        $dataToInsert[] = $mappedData;
                    }
                } else {
                    $this->totalFailed++;
                    $this->errors[] = [
                        'row' => $rowNumber,
                        'errors' => $validation['errors'],
                        'data' => $mappedData
                    ];
                    Log::warning("Fila {$rowNumber} no pasó validación: " . implode(', ', $validation['errors']));
                }
                
                // Registrar advertencias si existen
                if (!empty($validation['warnings'])) {
                    $this->warnings[] = [
                        'row' => $rowNumber,
                        'warnings' => $validation['warnings']
                    ];
                }
                
            } catch (\Exception $e) {
                $this->totalFailed++;
                $this->errors[] = [
                    'row' => $rowNumber,
                    'errors' => ['Error al procesar fila: ' . $e->getMessage()],
                    'data' => $rowArray ?? []
                ];
                Log::error("Error procesando fila {$rowNumber}: " . $e->getMessage());
            }
        }
        
        Log::info("Total de registros preparados para inserción: " . count($dataToInsert));
        
        // Inserción masiva para mejor rendimiento (en lotes de 500 para evitar timeouts)
        if (!empty($dataToInsert)) {
            // Normalizar todas las filas para que tengan las mismas columnas
            $normalizedData = $this->normalizeDataForInsert($dataToInsert);
            
            $chunks = array_chunk($normalizedData, 500);
            $totalChunks = count($chunks);
            $inserted = 0;
            
            Log::info("Iniciando inserción de " . count($dataToInsert) . " registros válidos en {$totalChunks} chunks");
            
            foreach ($chunks as $index => $chunk) {
                try {
                    // Reconectar periódicamente para evitar "gone away"
                    if ($index % 10 == 0) {
                        DB::reconnect();
                        Log::info("Reconexión a DB realizada en chunk {$index}");
                    }
                    
                    $chunkSize = count($chunk);
                    DB::table('registros_globales')->insert($chunk);
                    $inserted += $chunkSize;
                    $this->totalInserted += $chunkSize;
                    
                    // Log de progreso cada 10 chunks para monitoreo detallado
                    if (($index + 1) % 10 == 0 || ($index + 1) == $totalChunks) {
                        $percentage = round((($index + 1) / $totalChunks) * 100, 2);
                        Log::info("Progreso: " . ($index + 1) . "/{$totalChunks} chunks ({$percentage}%) - {$inserted} registros insertados");
                    }
                } catch (\Exception $e) {
                    $this->totalFailed += count($chunk);
                    
                    // Si es error de conexión, reconectar y reintentar una vez
                    if (strpos($e->getMessage(), 'gone away') !== false || 
                        strpos($e->getMessage(), 'Lost connection') !== false) {
                        try {
                            Log::warning("Conexión perdida en chunk {$index}, intentando reconectar...");
                            DB::reconnect();
                            DB::table('registros_globales')->insert($chunk);
                            $inserted += count($chunk);
                            $this->totalInserted += count($chunk);
                            Log::info("✓ Chunk {$index} reinsertado exitosamente después de reconexión");
                        } catch (\Exception $retryException) {
                            Log::error("✗ Error al insertar chunk {$index} después de reintento: " . $retryException->getMessage());
                            $this->errors[] = [
                                'chunk' => $index,
                                'error' => $retryException->getMessage(),
                                'records_affected' => count($chunk)
                            ];
                            continue;
                        }
                    } else {
                        // Continuar con el siguiente chunk si hay otro tipo de error
                        Log::error("✗ Error al insertar chunk {$index}: " . $e->getMessage());
                        $this->errors[] = [
                            'chunk' => $index,
                            'error' => $e->getMessage(),
                            'records_affected' => count($chunk)
                        ];
                        continue;
                    }
                }
            }
            
            Log::info("=".str_repeat("=", 50));
            Log::info("RESUMEN DE IMPORTACIÓN:");
            Log::info("{$inserted} registros insertados exitosamente de " . count($dataToInsert) . " procesados");
            Log::info("Total procesado: {$this->totalProcessed} | Insertados: {$this->totalInserted} | Fallidos: {$this->totalFailed}");
            Log::info("=".str_repeat("=", 50));
        } else {
            Log::warning("No hay registros válidos para insertar después de la validación");
        }
    }
    
    /**
     * Normalizar fila - convertir todas las claves a minúsculas sin espacios
     */
    private function normalizeRow(array $row): array
    {
        $normalized = [];
        foreach ($row as $key => $value) {
            // Normalizar clave: minúsculas, sin espacios, sin acentos básicos
            $normalizedKey = $this->normalizeKey($key);
            $normalized[$normalizedKey] = $value;
        }
        return $normalized;
    }
    
    /**
     * Normalizar clave de columna
     */
    private function normalizeKey($key): string
    {
        if (is_null($key)) {
            return '';
        }
        
        // Convertir a string y limpiar
        $key = trim((string)$key);
        $key = mb_strtolower($key, 'UTF-8');
        
        // Remover caracteres especiales al inicio y final
        $key = trim($key, " \t\n\r\0\x0B°");
        
        // Reemplazar caracteres especiales
        $key = str_replace(['/', '-', ' '], ['_', '_', '_'], $key);
        
        // Remover acentos básicos
        $key = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ', 'Á', 'É', 'Í', 'Ó', 'Ú', 'Ñ'],
            ['a', 'e', 'i', 'o', 'u', 'n', 'a', 'e', 'i', 'o', 'u', 'n'],
            $key
        );
        
        return $key;
    }
    
    /**
     * Validar datos de una fila antes de insertar
     */
    private function validateRowData(array $data, int $rowNumber): array
    {
        $errors = [];
        $warnings = [];
        
        // Validaciones obligatorias
        // Al menos debe tener año o fecha para ser válido
        if (empty($data['ano']) && empty($data['fecha'])) {
            $errors[] = 'Falta año y fecha - al menos uno es requerido';
        }
        
        // Validar año si existe
        if (!empty($data['ano'])) {
            $year = (int)$data['ano'];
            if ($year < 1900 || $year > 2100) {
                $errors[] = "Año inválido: {$year}";
            }
        }
        
        // Validar mes si existe
        if (!empty($data['mes'])) {
            $month = (int)$data['mes'];
            if ($month < 1 || $month > 12) {
                $errors[] = "Mes inválido: {$month}";
            }
        }
        
        // Validar edad si existe
        if (!empty($data['edad'])) {
            $age = (int)$data['edad'];
            if ($age < 0 || $age > 150) {
                $errors[] = "Edad inválida: {$age}";
            }
        }
        
        // Validar sexo si existe
        if (!empty($data['sexo'])) {
            $sexo = strtoupper(trim($data['sexo']));
            if (!in_array($sexo, ['M', 'F', 'MASCULINO', 'FEMENINO', 'H', 'HOMBRE', 'MUJER'])) {
                $warnings[] = "Sexo con formato no estándar: {$sexo}";
            }
        }
        
        // Validar que no esté completamente vacío
        $nonEmptyFields = array_filter($data, function($value) {
            return !is_null($value) && $value !== '';
        });
        
        if (count($nonEmptyFields) < 3) {
            $errors[] = 'Registro con muy pocos datos (menos de 3 campos con información)';
        }
        
        // Advertencias para campos importantes vacíos
        if (empty($data['medico'])) {
            $warnings[] = 'Campo médico vacío';
        }
        
        if (empty($data['sexo'])) {
            $warnings[] = 'Campo sexo vacío';
        }
        
        if (empty($data['edad'])) {
            $warnings[] = 'Campo edad vacío';
        }
        
        return [
            'valid' => empty($errors),
            'errors' => $errors,
            'warnings' => $warnings
        ];
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
            'errors_count' => count($this->errors),
            'warnings_count' => count($this->warnings),
            'errors' => $this->errors,
            'warnings' => $this->warnings
        ];
    }
    
    /**
     * Mapear datos de fila a campos de base de datos
     */
    private function mapRowData(array $normalizedRow): array
    {
        $mapped = [];
        
        foreach ($this->columnMapping as $dbField => $possibleKeys) {
            $value = null;
            
            // Buscar en las claves posibles - SOLO coincidencia exacta
            foreach ($possibleKeys as $key) {
                $normalizedKey = $this->normalizeKey($key);
                
                // Buscar coincidencia exacta SOLAMENTE
                if (isset($normalizedRow[$normalizedKey])) {
                    $value = $normalizedRow[$normalizedKey];
                    break;
                }
            }
            
            // Procesar valor según el campo
            $processedValue = $this->processFieldValue($dbField, $value);
            if ($processedValue !== null) {
                $mapped[$dbField] = $processedValue;
            }
        }
        
        return $mapped;
    }
    
    /**
     * Procesar valor según el tipo de campo
     */
    private function processFieldValue(string $field, $value)
    {
        if ($value === null || $value === '') {
            return null;
        }
        
        // Convertir a string y limpiar
        $value = trim((string)$value);
        
        // Campos que NO deben convertirse a mayúsculas
        $excludedFields = ['fecha', 'ano', 'edad', 'numero', 'se', 'ano_epi'];
        
        // Procesar según el campo
        switch ($field) {
            case 'mes':
                return $this->convertMonthToName($value);
                
            case 'ano':
            case 'edad':
                return $this->getNumericValue($value);
                
            case 'fecha':
                return $this->parseDate($value);
                
            case 'cod_1':
            case 'cod_2':
            case 'cod_3':
            case 'cod_4':
            case 'cod_5':
            case 'cod_6':
            case 'cod_7':
            case 'cod_col':
                return $this->getNumericValue($value);
                
            default:
                // Convertir a mayúsculas si es un campo de texto y no está excluido
                if (!in_array($field, $excludedFields)) {
                    return mb_strtoupper($value, 'UTF-8');
                }
                return $value;
        }
    }
    
    /**
     * Convertir mes de texto o número a nombre de mes
     */
    private function convertMonthToName($value): ?string
    {
        if ($value === null || $value === '') {
            return null;
        }
        
        // Si ya es numérico, convertir a nombre de mes
        if (is_numeric($value)) {
            $num = (int)$value;
            if ($num >= 1 && $num <= 12) {
                $nombresNumeros = [
                    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
                    5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
                    9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                ];
                return $nombresNumeros[$num];
            }
            return null;
        }
        
        $value = trim((string)$value);
        
        $meses = [
            'enero' => 'Enero', 'febrero' => 'Febrero', 'marzo' => 'Marzo', 'abril' => 'Abril',
            'mayo' => 'Mayo', 'junio' => 'Junio', 'julio' => 'Julio', 'agosto' => 'Agosto',
            'septiembre' => 'Septiembre', 'octubre' => 'Octubre', 'noviembre' => 'Noviembre', 'diciembre' => 'Diciembre',
            'ene' => 'Enero', 'feb' => 'Febrero', 'mar' => 'Marzo', 'abr' => 'Abril',
            'jun' => 'Junio', 'jul' => 'Julio', 'ago' => 'Agosto',
            'sep' => 'Septiembre', 'oct' => 'Octubre', 'nov' => 'Noviembre', 'dic' => 'Diciembre',
            'january' => 'Enero', 'february' => 'Febrero', 'march' => 'Marzo', 'april' => 'Abril',
            'june' => 'Junio', 'july' => 'Julio', 'august' => 'Agosto',
            'september' => 'Septiembre', 'october' => 'Octubre', 'november' => 'Noviembre', 'december' => 'Diciembre',
        ];
        
        $valueLower = mb_strtolower($value, 'UTF-8');
        
        // Remover acentos
        $valueLower = str_replace(
            ['á', 'é', 'í', 'ó', 'ú', 'ñ'],
            ['a', 'e', 'i', 'o', 'u', 'n'],
            $valueLower
        );
        
        // Buscar coincidencia exacta
        if (isset($meses[$valueLower])) {
            return $meses[$valueLower];
        }
        
        // Buscar coincidencia parcial (por si tiene espacios o caracteres extra)
        foreach ($meses as $mes => $numero) {
            if (strpos($valueLower, $mes) !== false || strpos($mes, $valueLower) !== false) {
                return $numero;
            }
        }
        
        return null;
    }
    
    /**
     * Convertir a valor numérico
     */
    private function getNumericValue($value): ?int
    {
        if ($value === null || $value === '') {
            return null;
        }
        
        // Si ya es numérico
        if (is_numeric($value)) {
            return (int)$value;
        }
        
        // Intentar extraer números del string
        if (preg_match('/\d+/', (string)$value, $matches)) {
            return (int)$matches[0];
        }
        
        return null;
    }
    
    /**
     * Parsear fecha desde diferentes formatos.
     * Siempre devuelve Y-m-d para consistencia en la BD.
     */
    private function parseDate($value): ?string
    {
        if (empty($value)) {
            return null;
        }

        try {
            // Si es un número de Excel (días desde 1900-01-01)
            if (is_numeric($value)) {
                $date = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($value);
                return $date->format('Y-m-d');
            }

            $strValue = trim((string) $value);

            // Detectar formato con barras
            if (strpos($strValue, '/') !== false) {
                $parts = explode('/', $strValue);
                if (count($parts) === 3) {
                    $p1 = (int)$parts[0];
                    $p2 = (int)$parts[1];
                    
                    // Si el primer número > 12, es definitivamente d/m/Y
                    if ($p1 > 12) {
                        try { return Carbon::createFromFormat('d/m/Y', $strValue)->format('Y-m-d'); } catch (\Exception $e) {}
                    }
                    // Si el segundo número > 12, es definitivamente m/d/Y
                    elseif ($p2 > 12) {
                        try { return Carbon::createFromFormat('m/d/Y', $strValue)->format('Y-m-d'); } catch (\Exception $e) {}
                    }
                    // Si es ambiguo (ambos <= 12), intentamos d/m/Y por defecto (estándar local)
                    // pero con fallback a m/d/Y si falla
                    else {
                        try { return Carbon::createFromFormat('d/m/Y', $strValue)->format('Y-m-d'); } catch (\Exception $e) {
                            try { return Carbon::createFromFormat('m/d/Y', $strValue)->format('Y-m-d'); } catch (\Exception $e2) {}
                        }
                    }
                }
            }

            // Detectar formato con guiones (d-m-Y)
            if (preg_match('/^\d{1,2}-\d{1,2}-\d{4}$/', $strValue)) {
                try {
                    return Carbon::createFromFormat('d-m-Y', $strValue)->format('Y-m-d');
                } catch (\Exception $e) {}
            }

            // Último recurso: parseo genérico
            return Carbon::parse($strValue)->format('Y-m-d');
        } catch (\Exception $e) {
            return null;
        }
    }
    
    /**
     * Tamaño de chunk para lectura
     */
    public function chunkSize(): int
    {
        return 2000; // Reducido para evitar timeouts en archivos grandes
    }
    
    /**
     * Normalizar datos para inserción - asegurar que todas las filas tengan las mismas columnas
     */
    private function normalizeDataForInsert(array $dataToInsert): array
    {
        if (empty($dataToInsert)) {
            return [];
        }
        
        // Obtener todas las columnas únicas de todos los registros
        $allColumns = [];
        foreach ($dataToInsert as $row) {
            $allColumns = array_merge($allColumns, array_keys($row));
        }
        $allColumns = array_unique($allColumns);
        sort($allColumns); // Ordenar para consistencia
        
        // Normalizar cada fila para que tenga todas las columnas
        $normalizedData = [];
        foreach ($dataToInsert as $row) {
            $normalizedRow = [];
            foreach ($allColumns as $column) {
                $normalizedRow[$column] = $row[$column] ?? null;
            }
            $normalizedData[] = $normalizedRow;
        }
        
        return $normalizedData;
    }
    
    /**
     * Configuración para archivos CSV
     */
    public function getCsvSettings(): array
    {
        return [
            'delimiter' => ',',        // Delimitador: coma
            'enclosure' => '"',        // Enclosure: comillas dobles
            'escape_character' => '\\', // Carácter de escape
            'contiguous' => false,      // No tratar líneas vacías como contiguas
            'input_encoding' => 'UTF-8', // Codificación de entrada
        ];
    }
}
