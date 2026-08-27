<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\Medico;
use App\Models\Colonia;
use App\Models\Diagnostico;
use App\Models\Referencia;

class MigrarAEstructuraNormalizadaSeeder extends Seeder
{
    private $medicoCache = [];
    private $coloniaCache = [];
    private $diagnosticoCache = [];
    private $referenciaCache = [];
    
    public function run(): void
    {
        $this->command->info('Iniciando migración a estructura normalizada...');
        
        DB::statement('SET FOREIGN_KEY_CHECKS=0');
        
        $this->migrarCatalogos();
        $this->migrarRegistros();
        
        DB::statement('SET FOREIGN_KEY_CHECKS=1');
        
        $this->command->info('Migración completada!');
    }
    
    private function migrarCatalogos(): void
    {
        $this->command->info('Migrando catálogos...');
        
        // Médicos
        $medicos = DB::table('registros_globales')
            ->select('medico', 'prof')
            ->distinct()
            ->whereNotNull('medico')
            ->where('medico', '!=', '')
            ->get();
        
        foreach ($medicos as $m) {
            $medico = Medico::firstOrCreate(
                ['nombre' => trim($m->medico)],
                ['profesion' => trim($m->prof ?? ''), 'activo' => true]
            );
            $this->medicoCache[trim($m->medico)] = $medico->id;
        }
        
        // Colonias
        $colonias = DB::table('registros_globales')
            ->select('cod_col', 'colonia')
            ->distinct()
            ->whereNotNull('colonia')
            ->where('colonia', '!=', '')
            ->get();
        
        foreach ($colonias as $c) {
            $colonia = Colonia::firstOrCreate(
                ['nombre' => trim($c->colonia)],
                ['codigo' => trim($c->cod_col ?? ''), 'activo' => true]
            );
            $this->coloniaCache[trim($c->colonia)] = $colonia->id;
        }
        
        // Referencias
        $refs = DB::table('registros_globales')
            ->selectRaw('referido_a as nombre')
            ->whereNotNull('referido_a')
            ->where('referido_a', '!=', '')
            ->union(
                DB::table('registros_globales')
                    ->selectRaw('referido_de as nombre')
                    ->whereNotNull('referido_de')
                    ->where('referido_de', '!=', '')
            )
            ->distinct()
            ->get();
        
        foreach ($refs as $r) {
            $ref = Referencia::firstOrCreate(
                ['nombre' => trim($r->nombre)],
                ['tipo' => 'EXTERNA', 'activo' => true]
            );
            $this->referenciaCache[trim($r->nombre)] = $ref->id;
        }
        
        // Diagnósticos (ya existen)
        $diagnosticos = Diagnostico::all();
        foreach ($diagnosticos as $d) {
            $this->diagnosticoCache[$d->codigo] = $d->id;
        }
    }
    
    private function migrarRegistros(): void
    {
        $this->command->info('Migrando registros...');
        
        $total = DB::table('registros_globales')->count();
        $bar = $this->command->getOutput()->createProgressBar($total);
        
        DB::table('registros_globales')
            ->orderBy('id')
            ->chunk(500, function ($registros) use ($bar) {
                foreach ($registros as $old) {
                    $this->migrarRegistro($old);
                    $bar->advance();
                }
            });
        
        $bar->finish();
        $this->command->newLine();
    }
    
    private function migrarRegistro($old): void
    {
        $nuevoId = DB::table('registros_medicos')->insertGetId([
            'ano' => $old->ano,
            'mes' => $old->mes,
            'numero' => $old->numero,
            'cm' => $old->cm,
            'fecha' => $old->fecha,
            'se' => $old->se,
            'exp' => $old->exp,
            'medico_id' => $this->getMedicoId($old->medico),
            'colonia_id' => $this->getColoniaId($old->colonia),
            'referido_a_id' => $this->getReferenciaId($old->referido_a),
            'referido_de_id' => $this->getReferenciaId($old->referido_de),
            'paciente_sexo' => $old->sexo,
            'paciente_edad' => $old->edad,
            'tipo' => $old->tipo,
            'rango' => $old->rango,
            'rango_2' => $old->rango_2,
            'rango_3' => $old->rango_3,
            'rango_4' => $old->rango_4,
            'rango_5' => $old->rango_5,
            'cond' => $old->cond,
            'pg_emb' => $old->pg_emb,
            'jornada' => $old->jornada,
            'sm' => $old->sm,
            'sg' => $old->sg,
            'sg2' => $old->sg2,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        
        // Migrar diagnósticos
        for ($i = 1; $i <= 7; $i++) {
            $cod = $old->{"cod_$i"};
            $diag = $old->{"diagnostico_$i"};
            $cond = $old->{"cond_$i"};
            
            if (!empty($cod) || !empty($diag)) {
                DB::table('diagnosticos_registro')->insert([
                    'registro_medico_id' => $nuevoId,
                    'diagnostico_id' => $this->getDiagnosticoId($cod),
                    'orden' => $i,
                    'condicionamiento' => $cond,
                    'es_principal' => ($i === 1),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }
    }
    
    private function getMedicoId($nombre): ?int
    {
        return empty($nombre) ? null : ($this->medicoCache[trim($nombre)] ?? null);
    }
    
    private function getColoniaId($nombre): ?int
    {
        return empty($nombre) ? null : ($this->coloniaCache[trim($nombre)] ?? null);
    }
    
    private function getReferenciaId($nombre): ?int
    {
        return empty($nombre) ? null : ($this->referenciaCache[trim($nombre)] ?? null);
    }
    
    private function getDiagnosticoId($codigo): ?int
    {
        return empty($codigo) ? null : ($this->diagnosticoCache[$codigo] ?? null);
    }
}
