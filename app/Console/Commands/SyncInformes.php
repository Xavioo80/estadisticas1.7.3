<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\RegistroGlobal;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SyncInformes extends Command
{
    protected $signature = 'informes:sync';
    protected $description = 'Sincroniza la tabla informes desde registros_globales';

    public function handle()
    {
        $this->info('Iniciando sincronización...');
        
        // Truncar la tabla informes
        DB::table('informes')->truncate();
        $this->info('Tabla informes limpia.');

        // Procesar en lotes desde registros_globales
        RegistroGlobal::chunk(200, function ($registros) {
            $dataToInsert = [];
            $now = Carbon::now();

            foreach ($registros as $registro) {
                // Iterar diagnósticos 1 a 7
                for ($i = 1; $i <= 7; $i++) {
                    $diagVal = $registro->{"diagnostico_$i"};
                    $codVal = $registro->{"cod_$i"};
                    
                    // Condición para considerar válido un diagnóstico: no vacío y no '0'
                    if (!empty($diagVal) && $diagVal !== '0') {
                        $condVal = $registro->{"cond_$i"};
                        
                        $dataToInsert[] = [
                            'registro_id' => $registro->id,
                            'diag_index' => $i,
                            'ano' => $registro->ano,
                            'mes' => $registro->mes,
                            'numero' => $registro->numero,
                            'cm' => $registro->cm,
                            'medico' => $registro->medico,
                            'prof' => $registro->prof,
                            'fecha' => $registro->fecha,
                            'se' => $registro->se,
                            'exp' => $registro->exp,
                            'sexo' => $registro->sexo,
                            'edad' => $registro->edad,
                            'tipo' => $registro->tipo,
                            'rango' => $registro->rango,
                            'rango_5' => $registro->rango_5,
                            'cond' => $registro->cond, // Condición del paciente (N/S)
                            'cod_col' => $registro->cod_col,
                            'colonia' => $registro->colonia,
                            // Datos específicos del diagnóstico
                            'cod' => $codVal,
                            'diagnostico' => $diagVal,
                            'cond_diagnostico' => $condVal,
                            'sg' => $registro->sg,
                            'referido_a' => $registro->referido_a,
                            'referido_de' => $registro->referido_de,
                            'pg_emb' => $registro->pg_emb,
                            'jornada' => $registro->jornada,
                            'created_at' => $now,
                            'updated_at' => $now,
                        ];
                    }
                }
            }

            if (!empty($dataToInsert)) {
                DB::table('informes')->insert($dataToInsert);
            }
            $this->output->write('.');
        });

        $this->info(PHP_EOL . 'Sincronización completada exitosamente.');
    }
}
