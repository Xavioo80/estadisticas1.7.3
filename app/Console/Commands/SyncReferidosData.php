<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class SyncReferidosData extends Command
{
    protected $signature = 'sync:referidos {--dry-run : Simular sin hacer cambios} {--from-rg : Sincronizar solo referred desde registros_globales} {--full : Sincronización completa (reconstruir informes desde registros_globales)}';
    protected $description = 'Sincroniza referido_a, referido_de y pg_emb desde registros_globales hacia informes';

    public function handle()
    {
        $dryRun = $this->option('dry-run');
        $fromRg = $this->option('from-rg');
        $full = $this->option('full');
        
        $this->info('===========================================');
        $this->info('  SINCRONIZACIÓN DE DATOS DE REFERIDO');
        $this->info('===========================================');
        
        if ($full) {
            return $this->fullSync($dryRun);
        }
        
        return $this->syncFromRegistrosGlobales($dryRun);
    }
    
    private function fullSync($dryRun)
    {
        $this->info('Ejecutando sincronización completa...');
        
        // Contar registros actuales
        $totalRg = DB::table('registros_globales')->count();
        $this->info("Total registros_globales: {$totalRg}");
        
        if ($dryRun) {
            $this->warn('DRY RUN: No se realizarán cambios');
            return 0;
        }
        
        try {
            // 1. Limpiar tabla informes
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('informes')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            
            $this->info('Tabla informes truncada');
            
            // 2. Re-poblar desde registros_globales
            $fields = "ano, mes, numero, cm, medico, prof, fecha, se, exp, sexo, edad, tipo, rango, rango_2, rango_3, rango_4, rango_5, cond, cod_col, colonia, referido_a, referido_de, pg_emb, jornada, sm, sg2, sg";
            
            $batchSize = 1000;
            for ($i = 1; $i <= 7; $i++) {
                DB::statement("
                    INSERT INTO informes (id, registro_id, diag_index, $fields, cod, diagnostico, cond_diagnostico, created_at, updated_at)
                    SELECT CONCAT(id, '_', $i), id, $i, $fields, cod_$i, diagnostico_$i, cond_$i, NOW(), NOW() 
                    FROM registros_globales 
                    WHERE (cod_$i <> '' AND cod_$i IS NOT NULL AND cod_$i <> '0') 
                       OR (diagnostico_$i <> '' AND diagnostico_$i IS NOT NULL AND diagnostico_$i <> '0')
                ");
                $this->info("Insertados diagnósticos $i");
            }
            
            $totalInformes = DB::table('informes')->count();
            $this->info("Total registros en informes: {$totalInformes}");
            
            $this->info('Sincronización completa terminada exitosamente!');
            
        } catch (\Exception $e) {
            $this->error('Error en sincronización: ' . $e->getMessage());
            return 1;
        }
        
        return 0;
    }
    
    private function syncFromRegistrosGlobales($dryRun)
    {
        // 1. Contar registros actuales
        $totalRg = DB::table('registros_globales')->count();
        $totalInformes = DB::table('informes')->count();
        $this->info("Total registros_globales: {$totalRg}");
        $this->info("Total informes: {$totalInformes}");
        
        // 2. Verificar cuántos tienen datos de referido en registros_globales
        $rgWithReferidos = DB::table('registros_globales')
            ->where(function($q) {
                $q->whereNotNull('referido_a')->where('referido_a', '!=', '')
                  ->orWhereNotNull('referido_de')->where('referido_de', '!=', '');
            })
            ->count();
        $this->info("Registros en registros_globales con referido: {$rgWithReferidos}");
        
        if ($dryRun) {
            // Contar cuántos registros necesitan actualización
            $countToUpdate = DB::table('informes')
                ->select('informes.id')
                ->leftJoin('registros_globales', 'informes.registro_id', '=', 'registros_globales.id')
                ->where(function($q) {
                    $q->whereNull('registros_globales.referido_a')
                      ->orWhere('registros_globales.referido_a', '')
                      ->orWhereNull('registros_globales.referido_de')
                      ->orWhere('registros_globales.referido_de', '')
                      ->orWhereNull('registros_globales.pg_emb')
                      ->orWhere('registros_globales.pg_emb', '');
                })
                ->count();
            
            $this->warn("DRY RUN: Se actualizarían {$countToUpdate} registros");
            return 0;
        }
        
        // 3. Actualizar informes con los datos de referido desde registros_globales
        $updateSql = "
            UPDATE informes i
            INNER JOIN (
                SELECT id, referido_a, referido_de, pg_emb 
                FROM registros_globales
            ) rg ON i.registro_id = rg.id
            SET 
                i.referido_a = COALESCE(NULLIF(rg.referido_a, ''), i.referido_a),
                i.referido_de = COALESCE(NULLIF(rg.referido_de, ''), i.referido_de),
                i.pg_emb = COALESCE(NULLIF(rg.pg_emb, ''), i.pg_emb)
            WHERE (i.referido_a IS NULL OR i.referido_a = '' OR i.referido_a = '0')
               OR (i.referido_de IS NULL OR i.referido_de = '' OR i.referido_de = '0')
               OR (i.pg_emb IS NULL OR i.pg_emb = '' OR i.pg_emb = '0')
        ";
        
        $affected = DB::unprepared($updateSql);
        $this->info("Registros actualizados: {$affected}");
        
        // 4. Verificar valores después de la sincronización
        $withReferidos = DB::table('informes')
            ->where(function($q) {
                $q->whereNotNull('referido_a')->where('referido_a', '!=', '')
                  ->orWhereNotNull('referido_de')->where('referido_de', '!=', '');
            })
            ->count();
            
        $this->info("Total informes con referido_a o referido_de: {$withReferidos}");
        
        // 5. Verificar pg_emb
        $withPgEmb = DB::table('informes')
            ->whereNotNull('pg_emb')
            ->where('pg_emb', '!=', '')
            ->count();
            
        $this->info("Total informes con pg_emb: {$withPgEmb}");
        
        $this->info('===========================================');
        $this->info('Sincronización completada exitosamente!');
        $this->info('===========================================');
        
        return 0;
    }
}
