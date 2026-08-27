<?php

namespace App\Observers;

use App\Models\RegistroGlobal;
use App\Models\Informe;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class RegistroGlobalObserver
{
    /**
     * Handle the RegistroGlobal "creating" event.
     */
    public function creating(RegistroGlobal $registroGlobal): void
    {
        // Set the user_id to the currently authenticated user
        if (auth()->check()) {
            $registroGlobal->user_id = auth()->id();
        }
    }

    /**
     * Handle the RegistroGlobal "created" event.
     */
    public function created(RegistroGlobal $registroGlobal): void
    {
        $this->syncInformes($registroGlobal);
        $this->clearCache($registroGlobal);
    }

    /**
     * Handle the RegistroGlobal "updated" event.
     */
    public function updated(RegistroGlobal $registroGlobal): void
    {
        $this->syncInformes($registroGlobal);
        $this->clearCache($registroGlobal);
    }

    /**
     * Handle the RegistroGlobal "deleted" event.
     */
    public function deleted(RegistroGlobal $registroGlobal): void
    {
        // Al eliminar el registro global, se eliminan sus informes hijos
        Informe::where('registro_id', $registroGlobal->id)->delete();
    }

    /**
     * Sincronizar los informes basados en el estado actual del RegistroGlobal.
     */
    private function syncInformes(RegistroGlobal $registro)
    {
        try {
            DB::transaction(function () use ($registro) {
                // 1. Eliminar informes existentes para este registro
                Informe::where('registro_id', $registro->id)->delete();

                // 2. Generar nuevos informes
                $nuevosInformes = [];

                $baseData = [
                    'registro_id' => $registro->id,
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
                    'rango_2' => $registro->rango_2,
                    'rango_3' => $registro->rango_3,
                    'rango_4' => $registro->rango_4,
                    'rango_5' => $registro->rango_5,
                    'cond' => $registro->cond,
                    'cod_col' => $registro->cod_col,
                    'colonia' => $registro->colonia,
                    'referido_a' => $registro->referido_a,
                    'referido_de' => $registro->referido_de,
                    'pg_emb' => $registro->pg_emb,
                    'jornada' => $registro->jornada,
                    'sm' => $registro->sm,
                    'sg2' => $registro->sg2,
                ];

                for ($i = 1; $i <= 7; $i++) {
                    $diag = $registro->{"diagnostico_$i"};
                    $cod = $registro->{"cod_$i"};
                    $cond = $registro->{"cond_$i"};

                    // Criterio para considerar válido un diagnóstico
                    if ((!empty($diag) && $diag !== '0') || (!empty($cod) && $cod !== '0')) {
                        $informeData = $baseData;
                        $informeData['id'] = $registro->id . '_' . $i;
                        $informeData['diag_index'] = $i;
                        $informeData['cod'] = $cod;
                        $informeData['diagnostico'] = $diag;
                        $informeData['cond_diagnostico'] = $cond;
                        $informeData['sg'] = $registro->sg;

                        $nuevosInformes[] = $informeData;
                    }
                }

                if (!empty($nuevosInformes)) {
                    Informe::insert($nuevosInformes);
                }
            });

        } catch (\Throwable $e) {
            Log::error("Error sincronizando Informes para RegistroGlobal ID {$registro->id}: " . $e->getMessage() . " en línea " . $e->getLine());
        }
    }
    /**
     * Limpiar caché relacionada con el registro para que los cambios se vean en tiempo real.
     */
    private function clearCache(RegistroGlobal $registro)
    {
        if ($registro->ano && $registro->mes) {
            $mesUpper = mb_strtoupper($registro->mes, 'UTF-8');
            Cache::forget("registros.{$registro->ano}.{$registro->mes}");
            Cache::forget("registros.{$registro->ano}.{$mesUpper}");
            Cache::forget("registros.years");
        }
    }
}
