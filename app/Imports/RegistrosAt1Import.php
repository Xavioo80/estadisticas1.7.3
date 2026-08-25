<?php

namespace App\Imports;

use App\Models\RegistroGlobal;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Maatwebsite\Excel\Concerns\WithValidation;
use Maatwebsite\Excel\Concerns\SkipsOnError;
use Maatwebsite\Excel\Concerns\SkipsErrors;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RegistrosAt1Import implements ToModel, WithHeadingRow, WithValidation, SkipsOnError
{
    use SkipsErrors;
    
    // Cache de registros existentes para evitar consultas repetidas
    private $existingRecords = [];
    
    /**
     * Verificar si el registro ya existe (basado en año, mes, numero y medico)
     */
    private function recordExists(array $row, ?string $fecha = null): bool
    {
        $key = ($row['ano'] ?? '') . '_' . strtoupper($row['mes'] ?? '') . '_' . ($fecha ?? '') . '_' . ($row['numero'] ?? '') . '_' . ($row['medico'] ?? '');
        
        if (isset($this->existingRecords[$key])) {
            return true;
        }
        
        $query = RegistroGlobal::where('ano', $row['ano'] ?? null)
            ->where('mes', strtoupper($row['mes'] ?? ''))
            ->where('numero', $row['numero'] ?? null)
            ->where('medico', $row['medico'] ?? null);

        if ($fecha) {
            $query->where('fecha', $fecha);
        }

        $exists = $query->exists();
            
        if ($exists) {
            $this->existingRecords[$key] = true;
        }
        
        return $exists;
    }

    public function model(array $row)
    {
        // Procesar fecha primero para incluirla en la verificación de duplicados
        $fecha = null;
        if (!empty($row['fecha'])) {
            try {
                if (is_numeric($row['fecha'])) {
                    // Fecha de Excel (número de días desde 1900-01-01)
                    $fechaObj = \PhpOffice\PhpSpreadsheet\Shared\Date::excelToDateTimeObject($row['fecha']);
                    $fecha = $fechaObj->format('Y-m-d');
                } else {
                    $strValue = trim((string)$row['fecha']);
                    // Detectar formato con barras
                    if (strpos($strValue, '/') !== false) {
                        $parts = explode('/', $strValue);
                        if (count($parts) === 3) {
                            $p1 = (int)$parts[0];
                            $p2 = (int)$parts[1];
                            if ($p1 > 12) {
                                try { $fecha = Carbon::createFromFormat('d/m/Y', $strValue)->format('Y-m-d'); } catch (\Exception $e) {}
                            } elseif ($p2 > 12) {
                                try { $fecha = Carbon::createFromFormat('m/d/Y', $strValue)->format('Y-m-d'); } catch (\Exception $e) {}
                            } else {
                                try { $fecha = Carbon::createFromFormat('d/m/Y', $strValue)->format('Y-m-d'); } catch (\Exception $e) {
                                    try { $fecha = Carbon::createFromFormat('m/d/Y', $strValue)->format('Y-m-d'); } catch (\Exception $e2) {}
                                }
                            }
                        }
                    }
                    
                    if (!$fecha) {
                        $fecha = Carbon::parse($strValue)->format('Y-m-d');
                    }
                }
            } catch (\Exception $e) {
                $fecha = null;
            }
        }

        // Verificar duplicado antes de crear (tomando en cuenta la fecha)
        if ($this->recordExists($row, $fecha)) {
            Log::info('Registro duplicado ignorado:', $row);
            return null;
        }

        return new RegistroGlobal([
            'ano' => $row['ano'] ?? null,
            'mes' => strtoupper($row['mes'] ?? ''),
            'numero' => $row['numero'] ?? null,
            'cm' => $row['cm'] ?? null,
            'medico' => $row['medico'] ?? null,
            'prof' => $row['profesion'] ?? null,
            'fecha' => $fecha,
            'se' => $row['se'] ?? null,
            'exp' => $row['expediente'] ?? null,
            'sexo' => $row['sexo'] ?? null,
            'edad' => $row['edad'] ?? null,
            'tipo' => $row['tipo'] ?? null,
            'rango' => $row['rango'] ?? null,
            'rango_2' => $row['rango_2'] ?? null,
            'rango_3' => $row['rango_3'] ?? null,
            'rango_4' => $row['rango_4'] ?? null,
            'rango_5' => $row['rango_5'] ?? null,
            'cond' => $row['condicion'] ?? null,
            'cod_col' => $row['cod_colonia'] ?? null,
            'colonia' => $row['colonia'] ?? null,
            'cod_1' => $row['codigo_1'] ?? null,
            'diagnostico_1' => $row['diagnostico_1'] ?? null,
            'cond_1' => $row['cond_1'] ?? null,
            'sg' => $row['sg'] ?? null,
            'cod_2' => $row['codigo_2'] ?? null,
            'diagnostico_2' => $row['diagnostico_2'] ?? null,
            'cond_2' => $row['cond_2'] ?? null,
            'cod_3' => $row['codigo_3'] ?? null,
            'diagnostico_3' => $row['diagnostico_3'] ?? null,
            'cond_3' => $row['cond_3'] ?? null,
            'cod_4' => $row['codigo_4'] ?? null,
            'diagnostico_4' => $row['diagnostico_4'] ?? null,
            'cond_4' => $row['cond_4'] ?? null,
            'cod_5' => $row['codigo_5'] ?? null,
            'diagnostico_5' => $row['diagnostico_5'] ?? null,
            'cond_5' => $row['cond_5'] ?? null,
            'cod_6' => $row['codigo_6'] ?? null,
            'diagnostico_6' => $row['diagnostico_6'] ?? null,
            'cond_6' => $row['cond_6'] ?? null,
            'cod_7' => $row['codigo_7'] ?? null,
            'diagnostico_7' => $row['diagnostico_7'] ?? null,
            'cond_7' => $row['cond_7'] ?? null,
            'referido_a' => $row['referido_a'] ?? null,
            'referido_de' => $row['referido_de'] ?? null,
            'pg_emb' => $row['pg_emb'] ?? null,
            'jornada' => $row['jornada'] ?? null,
            'sm' => $row['sm'] ?? null,
        ]);
    }

    public function rules(): array
    {
        return [
            'ano' => 'nullable|integer',
            'mes' => 'nullable|string',
            'edad' => 'nullable|integer',
        ];
    }
}
