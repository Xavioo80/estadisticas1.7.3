<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class NormalizeAgeRanges extends Command
{
    protected $signature = 'normalize:ranges {--dry-run : Solo mostrar cambios sin aplicar}';
    protected $description = 'Normaliza los rangos de edad en la base de datos (Prefijo solo en Rango 1)';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $this->info($dryRun ? '--- MODO SIMULACIÓN (No se aplicarán cambios) ---' : 'Iniciando normalización de rangos...');

        // Definiciones estándar según create.blade.php
        $r1Map = [
            'MENOR DE 1 MES' => '1. MENOR DE 1 MES',
            'DE 1 MES A 1 AÑO' => '2. DE 1 MES A 1 AÑO',
            'DE 1 A 4 AÑOS' => '3. DE 1 A 4 AÑOS',
            'DE 5 A 9 AÑOS' => '4. DE 5 A 9 AÑOS',
            'DE 10 A 14 AÑOS' => '5. DE 10 A 14 AÑOS',
            'DE 15 A 19 AÑOS' => '6. DE 15 A 19 AÑOS',
            'DE 20 A 49 AÑOS' => '7. DE 20 A 49 AÑOS',
            'DE 50 A 59 AÑOS' => '8. DE 50 A 59 AÑOS',
            'MAYORES DE 60 AÑOS' => '9. MAYORES DE 60 AÑOS',
            '60 Y MAS' => '9. MAYORES DE 60 AÑOS',
        ];

        $columns = ['rango', 'rango_2', 'rango_3', 'rango_4', 'rango_5'];

        foreach ($columns as $col) {
            $this->info("Procesando columna: $col...");
            
            $distinctValues = DB::table('registros_globales')
                ->whereNotNull($col)
                ->where($col, '!=', '')
                ->distinct()
                ->pluck($col);

            foreach ($distinctValues as $val) {
                $cleanVal = $this->cleanValue($val, $col);
                $finalVal = $cleanVal;

                if ($col === 'rango') {
                    // Asegurar prefijo en Rango 1
                    $finalVal = $r1Map[$cleanVal] ?? $this->ensureR1Prefix($cleanVal, $r1Map);
                } else {
                    // Quitar prefijo en los demás
                    $finalVal = $cleanVal;
                }

                if ($val !== $finalVal) {
                    $this->line("  Cambio: [$val] -> [$finalVal]");
                    if (!$dryRun) {
                        DB::table('registros_globales')
                            ->where($col, $val)
                            ->update([$col => $finalVal]);
                    }
                }
            }
        }

        $this->info('Normalización completada.');
        return 0;
    }

    private function cleanValue($val, $col)
    {
        // Quitar prefijos numéricos existentes (ej: "7. ", "10. ")
        $clean = preg_replace('/^\d+\.\s+/', '', $val);
        $clean = trim($clean);
        
        // Unificar variaciones según columna específica para coincidir con capturas
        if ($col === 'rango_3') {
            if ($clean === 'MAYORES DE 60 AÑOS' || $clean === '60 Y +' || $clean === '60 y +') return '60 Y MAS';
        }
        if ($col === 'rango_4') {
            if ($clean === 'MAYORES DE 60 AÑOS' || $clean === '60 Y MAS' || $clean === '50 y +') return '50 Y +';
        }
        
        // Unificación general
        if ($clean === '60 Y MAS' || $clean === '60 y +' || $clean === '60 Y +') return 'MAYORES DE 60 AÑOS';
        
        return $clean;
    }

    private function ensureR1Prefix($val, $map)
    {
        // Si ya tiene el prefijo correcto, dejarlo
        if (preg_match('/^\d+\.\s+/', $val)) return $val;
        
        // Si está en el mapa, devolver el mapeado
        return $map[$val] ?? $val;
    }
}
