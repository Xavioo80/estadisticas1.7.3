<?php

namespace App\Services;

use App\Models\Informe;
use App\Models\RegistroGlobal;
use App\Traits\InformesHelperTrait;
use Illuminate\Support\Facades\Cache;

class ComparacionInformesService
{
    use InformesHelperTrait;

    /**
     * Realiza la comparación cruzada de enfermedades compatibles
     * entre AT2-R(N), Morbilidad, TRANS-2 y Registros Globales con desglose de condiciones (Nuevos / Subsiguientes).
     */
    public function comparar(int|string $ano, string $mes, string $jornada = 'TODAS'): array
    {
        $mes = strtoupper(trim($mes));
        $cacheKey = "comp_informes_v5_{$ano}_{$mes}_{$jornada}";

        return Cache::remember($cacheKey, 60, function() use ($ano, $mes, $jornada) {
            return $this->ejecutarComparacion($ano, $mes, $jornada);
        });
    }

    private function ejecutarComparacion(int|string $ano, string $mes, string $jornada): array
    {
        // 1. Obtener registros de RegistroGlobal (Fuente única para AT2-R N, Morbilidad, TRANS-2 y Raw)
        $rgQuery = RegistroGlobal::query()->where('ano', $ano)->where('mes', $mes);
        if ($jornada !== 'TODAS') {
            $rgQuery->where('jornada', $jornada);
        }
        $registrosGlobales = $rgQuery->get([
            'id', 'ano', 'mes', 'se', 'jornada', 'edad', 'sexo', 'tipo', 'cond', 'medico', 'prof',
            'diagnostico_1', 'cod_1', 'cond_1',
            'diagnostico_2', 'cod_2', 'cond_2',
            'diagnostico_3', 'cod_3', 'cond_3',
            'diagnostico_4', 'cod_4', 'cond_4',
            'diagnostico_5', 'cod_5', 'cond_5',
            'diagnostico_6', 'cod_6', 'cond_6',
            'diagnostico_7', 'cod_7', 'cond_7',
            'nombre_paciente', 'identidad', 'fecha'
        ]);

        // 2. Desplegar los 7 diagnósticos en memoria para Morbilidad y TRANS-2
        $unrolledInformes = [];
        foreach ($registrosGlobales as $rg) {
            for ($i = 1; $i <= 7; $i++) {
                $diag = trim($rg->{"diagnostico_$i"} ?? '');
                if ($diag === '') continue;

                $cond = strtoupper(trim($rg->{"cond_$i"} ?? ($rg->cond ?? '')));
                if ($cond !== 'N' && $cond !== 'S') $cond = 'N';

                $unrolledInformes[] = (object)[
                    'id' => "RG-{$rg->id}-{$i}",
                    'registro_id' => $rg->id,
                    'ano' => $rg->ano,
                    'mes' => $rg->mes,
                    'se' => $rg->se,
                    'jornada' => $rg->jornada,
                    'edad' => $rg->edad,
                    'tipo' => $rg->tipo,
                    'sexo' => $rg->sexo,
                    'diagnostico' => $diag,
                    'cod' => trim($rg->{"cod_$i"} ?? ''),
                    'cond_diagnostico' => $cond,
                    'nombre_paciente' => $rg->nombre_paciente,
                    'identidad' => $rg->identidad,
                    'fecha' => $rg->fecha,
                    'medico' => $rg->medico,
                ];
            }
        }
        $informes = collect($unrolledInformes);

        // 3. Definición exhaustiva de comparaciones con desglose de condición
        $comparaciones = [
            // ──────────────── DIARREAS EN < 5 AÑOS ────────────────
            [
                'id' => 'diarrea_u5_n',
                'categoria' => 'Enfermedades Infecciosas (< 5 Años)',
                'label' => 'Diarreas < 5 Años - Casos Nuevos (N)',
                'condicion' => 'N',
                'codigo_cie' => 'A09.X',
                'descripcion' => 'Rehidratación oral / Diarreas nuevas en niños menores de 5 años',
                'at2rn' => $this->countAt2rNDiarreaU5PorCondicion($registrosGlobales, 'N'),
                'morbilidad' => $this->countMorbilidadDiarreaU5PorCondicion($informes, 'N'),
                'trans2' => $this->countTrans2DiarreaU5($informes), // TRANS-2 solo reporta Nuevas
                'globales' => $this->countGlobalesDiarreaU5PorCondicion($registrosGlobales, 'N'),
                'has_trans2' => true,
                'has_at2rn' => true,
            ],
            [
                'id' => 'diarrea_u5_s',
                'categoria' => 'Enfermedades Infecciosas (< 5 Años)',
                'label' => 'Diarreas < 5 Años - Subsiguientes (S)',
                'condicion' => 'S',
                'codigo_cie' => 'A09.X',
                'descripcion' => 'Citas de seguimiento por diarrea en niños menores de 5 años',
                'at2rn' => $this->countAt2rNDiarreaU5PorCondicion($registrosGlobales, 'S'),
                'morbilidad' => $this->countMorbilidadDiarreaU5PorCondicion($informes, 'S'),
                'trans2' => null, // TRANS-2 no lleva subsiguientes
                'globales' => $this->countGlobalesDiarreaU5PorCondicion($registrosGlobales, 'S'),
                'has_trans2' => false,
                'has_at2rn' => true,
            ],
            [
                'id' => 'diarrea_u5_total',
                'categoria' => 'Enfermedades Infecciosas (< 5 Años)',
                'label' => 'Diarreas < 5 Años - Total (N + S)',
                'condicion' => 'TOTAL',
                'codigo_cie' => 'A09.X',
                'descripcion' => 'Total de atenciones (Nuevas + Subsiguientes) por diarreas en < 5 años',
                'at2rn' => $this->countAt2rNDiarreaU5($registrosGlobales),
                'morbilidad' => $this->countMorbilidadDiarreaU5($informes),
                'trans2' => null, // TRANS-2 solo reporta Nuevas
                'globales' => $this->countGlobalesDiarreaU5($registrosGlobales),
                'has_trans2' => false,
                'has_at2rn' => true,
            ],

            // ──────────────── NEUMONÍAS EN < 5 AÑOS ────────────────
            [
                'id' => 'neumonia_u5_n',
                'categoria' => 'Enfermedades Respiratorias (< 5 Años)',
                'label' => 'Neumonías < 5 Años - Casos Nuevos (N)',
                'condicion' => 'N',
                'codigo_cie' => 'J18.9 / J18.0',
                'descripcion' => 'Casos de Neumonía / Bronconeumonía nuevos en niños < 5 años',
                'at2rn' => $this->countAt2rNNeumoniaU5PorCondicion($registrosGlobales, 'N'),
                'morbilidad' => $this->countMorbilidadNeumoniaU5PorCondicion($informes, 'N'),
                'trans2' => $this->countTrans2NeumoniaU5($informes), // TRANS-2 solo Nuevas
                'globales' => $this->countGlobalesNeumoniaU5PorCondicion($registrosGlobales, 'N'),
                'has_trans2' => true,
                'has_at2rn' => true,
            ],
            [
                'id' => 'neumonia_u5_s',
                'categoria' => 'Enfermedades Respiratorias (< 5 Años)',
                'label' => 'Neumonías < 5 Años - Subsiguientes (S)',
                'condicion' => 'S',
                'codigo_cie' => 'J18.9 / J18.0',
                'descripcion' => 'Citas de seguimiento por neumonía en niños < 5 años',
                'at2rn' => $this->countAt2rNNeumoniaU5PorCondicion($registrosGlobales, 'S'),
                'morbilidad' => $this->countMorbilidadNeumoniaU5PorCondicion($informes, 'S'),
                'trans2' => null,
                'globales' => $this->countGlobalesNeumoniaU5PorCondicion($registrosGlobales, 'S'),
                'has_trans2' => false,
                'has_at2rn' => true,
            ],
            [
                'id' => 'neumonia_u5_total',
                'categoria' => 'Enfermedades Respiratorias (< 5 Años)',
                'label' => 'Neumonías < 5 Años - Total (N + S)',
                'condicion' => 'TOTAL',
                'codigo_cie' => 'J18.9 / J18.0',
                'descripcion' => 'Total de casos de Neumonía y Bronconeumonía en < 5 años',
                'at2rn' => $this->countAt2rNNeumoniaU5($registrosGlobales),
                'morbilidad' => $this->countMorbilidadNeumoniaU5($informes),
                'trans2' => null, // TRANS-2 solo reporta Nuevas
                'globales' => $this->countGlobalesNeumoniaU5($registrosGlobales),
                'has_trans2' => false,
                'has_at2rn' => true,
            ],

            // ──────────────── ANEMIAS EN < 5 AÑOS ────────────────
            [
                'id' => 'anemia_u5_n',
                'categoria' => 'Nutricionales / Hematológicas (< 5 Años)',
                'label' => 'Anemias < 5 Años - Casos Nuevos (N)',
                'condicion' => 'N',
                'codigo_cie' => 'D50-D64',
                'descripcion' => 'Síndrome anémico nuevo diagnosticado en niños < 5 años',
                'at2rn' => $this->countAt2rNAnemiaU5PorCondicion($registrosGlobales, 'N'),
                'morbilidad' => $this->countMorbilidadAnemiaU5PorCondicion($informes, 'N'),
                'trans2' => $this->countTrans2AnemiaU5($informes),
                'globales' => $this->countGlobalesAnemiaU5PorCondicion($registrosGlobales, 'N'),
                'has_trans2' => true,
                'has_at2rn' => true,
            ],
            [
                'id' => 'anemia_u5_s',
                'categoria' => 'Nutricionales / Hematológicas (< 5 Años)',
                'label' => 'Anemias < 5 Años - Subsiguientes (S)',
                'condicion' => 'S',
                'codigo_cie' => 'D50-D64',
                'descripcion' => 'Seguimiento de Anemia en niños < 5 años',
                'at2rn' => $this->countAt2rNAnemiaU5PorCondicion($registrosGlobales, 'S'),
                'morbilidad' => $this->countMorbilidadAnemiaU5PorCondicion($informes, 'S'),
                'trans2' => null,
                'globales' => $this->countGlobalesAnemiaU5PorCondicion($registrosGlobales, 'S'),
                'has_trans2' => false,
                'has_at2rn' => true,
            ],

            // ──────────────── CRÓNICAS: DIABETES MELLITUS ────────────────
            [
                'id' => 'diabetes_nuevas',
                'categoria' => 'Enfermedades Crónicas No Transmisibles',
                'label' => 'Diabetes Mellitus - Casos Nuevos (N)',
                'condicion' => 'N',
                'codigo_cie' => 'E14.9',
                'descripcion' => 'Atenciones brindadas Nuevas de Diabetes Mellitus',
                'at2rn' => $this->countAt2rNDiabetesNuevas($registrosGlobales),
                'morbilidad' => $this->countMorbilidadDiabetesNuevas($informes),
                'trans2' => $this->countTrans2DiabetesNuevas($informes),
                'globales' => $this->countGlobalesDiabetesNuevas($registrosGlobales),
                'has_trans2' => true,
                'has_at2rn' => true,
            ],
            [
                'id' => 'diabetes_subs',
                'categoria' => 'Enfermedades Crónicas No Transmisibles',
                'label' => 'Diabetes Mellitus - Subsiguientes (S)',
                'condicion' => 'S',
                'codigo_cie' => 'E14.9',
                'descripcion' => 'Atenciones brindadas Subsiguientes de Diabetes Mellitus',
                'at2rn' => $this->countAt2rNDiabetesSubs($registrosGlobales),
                'morbilidad' => $this->countMorbilidadDiabetesSubs($informes),
                'trans2' => null,
                'globales' => $this->countGlobalesDiabetesSubs($registrosGlobales),
                'has_trans2' => false,
                'has_at2rn' => true,
            ],
            [
                'id' => 'diabetes_total',
                'categoria' => 'Enfermedades Crónicas No Transmisibles',
                'label' => 'Diabetes Mellitus - Total Atenciones (N + S)',
                'condicion' => 'TOTAL',
                'codigo_cie' => 'E14.9',
                'descripcion' => 'Total de atenciones de Diabetes Mellitus',
                'at2rn' => $this->countAt2rNDiabetesTotal($registrosGlobales),
                'morbilidad' => $this->countMorbilidadDiabetesTotal($informes),
                'trans2' => null,
                'globales' => $this->countGlobalesDiabetesTotal($registrosGlobales),
                'has_trans2' => false,
                'has_at2rn' => true,
            ],

            // ──────────────── CRÓNICAS: HIPERTENSIÓN ARTERIAL ────────────────
            [
                'id' => 'hta_nuevas',
                'categoria' => 'Enfermedades Crónicas No Transmisibles',
                'label' => 'Hipertensión Arterial - Casos Nuevos (N)',
                'condicion' => 'N',
                'codigo_cie' => 'I10.X',
                'descripcion' => 'Atenciones brindadas Nuevas de Hipertensión Arterial',
                'at2rn' => $this->countAt2rNHtaNuevas($registrosGlobales),
                'morbilidad' => $this->countMorbilidadHtaNuevas($informes),
                'trans2' => $this->countTrans2HtaNuevas($informes),
                'globales' => $this->countGlobalesHtaNuevas($registrosGlobales),
                'has_trans2' => true,
                'has_at2rn' => true,
            ],
            [
                'id' => 'hta_subs',
                'categoria' => 'Enfermedades Crónicas No Transmisibles',
                'label' => 'Hipertensión Arterial - Subsiguientes (S)',
                'condicion' => 'S',
                'codigo_cie' => 'I10.X',
                'descripcion' => 'Atenciones brindadas Subsiguientes de Hipertensión Arterial',
                'at2rn' => $this->countAt2rNHtaSubs($registrosGlobales),
                'morbilidad' => $this->countMorbilidadHtaSubs($informes),
                'trans2' => null,
                'globales' => $this->countGlobalesHtaSubs($registrosGlobales),
                'has_trans2' => false,
                'has_at2rn' => true,
            ],
            [
                'id' => 'hta_total',
                'categoria' => 'Enfermedades Crónicas No Transmisibles',
                'label' => 'Hipertensión Arterial - Total Atenciones (N + S)',
                'condicion' => 'TOTAL',
                'codigo_cie' => 'I10.X',
                'descripcion' => 'Total de atenciones de Hipertensión Arterial',
                'at2rn' => $this->countAt2rNHtaTotal($registrosGlobales),
                'morbilidad' => $this->countMorbilidadHtaTotal($informes),
                'trans2' => null,
                'globales' => $this->countGlobalesHtaTotal($registrosGlobales),
                'has_trans2' => false,
                'has_at2rn' => true,
            ],

            // ──────────────── CRÓNICAS: ENFERMEDAD RENAL CRÓNICA ────────────────
            [
                'id' => 'erc_nuevas',
                'categoria' => 'Enfermedades Crónicas No Transmisibles',
                'label' => 'Enfermedad Renal Crónica - Casos Nuevos (N)',
                'condicion' => 'N',
                'codigo_cie' => 'N18.9',
                'descripcion' => 'Atenciones brindadas Nuevas de ERC',
                'at2rn' => $this->countAt2rNErcNuevas($registrosGlobales),
                'morbilidad' => $this->countMorbilidadErcNuevas($informes),
                'trans2' => $this->countTrans2ErcNuevas($informes),
                'globales' => $this->countGlobalesErcNuevas($registrosGlobales),
                'has_trans2' => true,
                'has_at2rn' => true,
            ],
            [
                'id' => 'erc_subs',
                'categoria' => 'Enfermedades Crónicas No Transmisibles',
                'label' => 'Enfermedad Renal Crónica - Subsiguientes (S)',
                'condicion' => 'S',
                'codigo_cie' => 'N18.9',
                'descripcion' => 'Atenciones brindadas Subsiguientes de ERC',
                'at2rn' => $this->countAt2rNErcSubs($registrosGlobales),
                'morbilidad' => $this->countMorbilidadErcSubs($informes),
                'trans2' => null,
                'globales' => $this->countGlobalesErcSubs($registrosGlobales),
                'has_trans2' => false,
                'has_at2rn' => true,
            ],

            // ──────────────── DENGUES (TRANS-2 / MORBILIDAD / RG) ────────────────
            [
                'id' => 'dengue_sin_signos',
                'categoria' => 'Enfermedades Vectoriales y Febriles',
                'label' => 'Sospecha Dengue Sin Signos de Alarma (DSSA)',
                'condicion' => 'N',
                'codigo_cie' => 'A90.X',
                'descripcion' => 'Casos de dengue sin signos de alarma (Nuevos)',
                'at2rn' => null,
                'morbilidad' => $this->countMorbilidadDengueSinSignos($informes),
                'trans2' => $this->countTrans2DengueSinSignos($informes),
                'globales' => $this->countGlobalesDengueSinSignos($registrosGlobales),
                'has_trans2' => true,
                'has_at2rn' => false,
            ],
            [
                'id' => 'dengue_con_signos',
                'categoria' => 'Enfermedades Vectoriales y Febriles',
                'label' => 'Sospecha Dengue Con Signos de Alarma (DCSA)',
                'condicion' => 'N',
                'codigo_cie' => 'A90.X / A97.1',
                'descripcion' => 'Casos de dengue con signos de alarma (Nuevos)',
                'at2rn' => null,
                'morbilidad' => $this->countMorbilidadDengueConSignos($informes),
                'trans2' => $this->countTrans2DengueConSignos($informes),
                'globales' => $this->countGlobalesDengueConSignos($registrosGlobales),
                'has_trans2' => true,
                'has_at2rn' => false,
            ],
            [
                'id' => 'dengue_grave',
                'categoria' => 'Enfermedades Vectoriales y Febriles',
                'label' => 'Dengue Grave',
                'condicion' => 'N',
                'codigo_cie' => 'A91.X',
                'descripcion' => 'Casos de dengue grave / hemorrágico (Nuevos)',
                'at2rn' => null,
                'morbilidad' => $this->countMorbilidadDengueGrave($informes),
                'trans2' => $this->countTrans2DengueGrave($informes),
                'globales' => $this->countGlobalesDengueGrave($registrosGlobales),
                'has_trans2' => true,
                'has_at2rn' => false,
            ],

            // ──────────────── ASMA Y OTRAS RESPIRATORIAS ────────────────
            [
                'id' => 'asma_bronquitis_n',
                'categoria' => 'Otras Patologías Respiratorias',
                'label' => 'Asma Bronquial / Bronquitis - Nuevos (N)',
                'condicion' => 'N',
                'codigo_cie' => 'J45 / J20',
                'descripcion' => 'Casos nuevos de Asma Bronquial y Bronquitis',
                'at2rn' => null,
                'morbilidad' => $this->countMorbilidadAsmaBronquitisPorCondicion($informes, 'N'),
                'trans2' => $this->countTrans2AsmaBronquitis($informes),
                'globales' => $this->countGlobalesAsmaBronquitisPorCondicion($registrosGlobales, 'N'),
                'has_trans2' => true,
                'has_at2rn' => false,
            ],
            [
                'id' => 'iras_resfrio_n',
                'categoria' => 'Otras Patologías Respiratorias',
                'label' => 'IRAS Altas / Resfrío Común - Nuevos (N)',
                'condicion' => 'N',
                'codigo_cie' => 'J00 - J06',
                'descripcion' => 'Infecciones respiratorias agudas altas (excepto estreptocócicas)',
                'at2rn' => null,
                'morbilidad' => $this->countMorbilidadIrasAltasPorCondicion($informes, 'N'),
                'trans2' => $this->countTrans2IrasAltas($informes),
                'globales' => $this->countGlobalesIrasAltasPorCondicion($registrosGlobales, 'N'),
                'has_trans2' => true,
                'has_at2rn' => false,
            ],

            // ──────────────── TUBERCULOSIS ────────────────
            [
                'id' => 'tuberculosis',
                'categoria' => 'Otras Enfermedades Transmisibles',
                'label' => 'Tuberculosis (Presuntiva / Confirmada)',
                'condicion' => 'TOTAL',
                'codigo_cie' => 'A15 - A19 / J16.4',
                'descripcion' => 'Casos y detecciones de Tuberculosis',
                'at2rn' => $this->countAt2rNTuberculosis($registrosGlobales),
                'morbilidad' => $this->countMorbilidadTuberculosis($informes),
                'trans2' => null, // TRANS-2 solo reporta casos nuevos individuales
                'globales' => $this->countGlobalesTuberculosis($registrosGlobales),
                'has_trans2' => false,
                'has_at2rn' => true,
            ],

            // ──────────────── TOTALES DE MORBILIDAD ────────────────
            [
                'id' => 'total_morbilidad_nuevas',
                'categoria' => 'Resumen Global de Morbilidad',
                'label' => 'Total de Morbilidad - Casos Nuevos (N)',
                'condicion' => 'N',
                'codigo_cie' => 'TODOS',
                'descripcion' => 'Suma de todos los diagnósticos nuevos de morbilidad (Cuadre con TRANS-2)',
                'at2rn' => $this->countAt2rNTotalMorbilidadPorCondicion($registrosGlobales, 'N'),
                'morbilidad' => $this->countMorbilidadTotalPorCondicion($informes, 'N'),
                'trans2' => $this->countTrans2TotalNuevas($informes),
                'globales' => $this->countGlobalesTotalMorbilidadPorCondicion($registrosGlobales, 'N'),
                'has_trans2' => true,
                'has_at2rn' => true,
            ],
            [
                'id' => 'total_morbilidad_general',
                'categoria' => 'Resumen Global de Morbilidad',
                'label' => 'Total General de Morbilidad (N + S)',
                'condicion' => 'TOTAL',
                'codigo_cie' => 'TODOS',
                'descripcion' => 'Suma total de atenciones / diagnósticos de morbilidad registrados',
                'at2rn' => $this->countAt2rNTotalMorbilidad($registrosGlobales),
                'morbilidad' => $this->countMorbilidadTotalGeneral($informes),
                'trans2' => null,
                'globales' => $this->countGlobalesTotalMorbilidad($registrosGlobales),
                'has_trans2' => false,
                'has_at2rn' => true,
            ],
        ];

        // 4. Evaluar consistencia por cada fila
        $totalComparables = 0;
        $totalCoincidencias = 0;
        $totalDiscrepancias = 0;

        foreach ($comparaciones as &$item) {
            $valoresValidos = [];
            if ($item['at2rn'] !== null) $valoresValidos['at2rn'] = $item['at2rn'];
            if ($item['morbilidad'] !== null) $valoresValidos['morbilidad'] = $item['morbilidad'];
            if ($item['trans2'] !== null) $valoresValidos['trans2'] = $item['trans2'];
            if ($item['globales'] !== null) $valoresValidos['globales'] = $item['globales'];

            $item['valores_presentes'] = count($valoresValidos);
            $item['es_consistente'] = false;
            $item['diferencia_max'] = 0;

            if (count($valoresValidos) >= 2) {
                $totalComparables++;
                $minVal = min($valoresValidos);
                $maxVal = max($valoresValidos);
                $item['diferencia_max'] = $maxVal - $minVal;

                if ($minVal === $maxVal) {
                    $item['es_consistente'] = true;
                    $item['estado'] = 'match';
                    $totalCoincidencias++;
                } else {
                    $item['es_consistente'] = false;
                    $item['estado'] = 'discrepancia';
                    $totalDiscrepancias++;
                }
            } else {
                $item['estado'] = 'info';
            }
        }
        unset($item);

        $porcentajeConsistencia = $totalComparables > 0
            ? round(($totalCoincidencias / $totalComparables) * 100, 1)
            : 100;

        return [
            'ano' => $ano,
            'mes' => $mes,
            'jornada' => $jornada,
            'comparaciones' => $comparaciones,
            'resumen' => [
                'total_comparables' => $totalComparables,
                'coincidencias' => $totalCoincidencias,
                'discrepancias' => $totalDiscrepancias,
                'porcentaje_consistencia' => $porcentajeConsistencia,
                'total_informes_raw' => $informes->count(),
                'total_registros_globales' => $registrosGlobales->count(),
            ]
        ];
    }

    // ──────────────── MÉTODOS DE CONTEO POR CONDICIÓN ────────────────

    public function countAt2rNDiarreaU5PorCondicion($registros, string $cond): int
    {
        $count = 0;
        foreach ($registros as $r) {
            $t = strtoupper(trim($r->tipo ?? ''));
            $e = (int)($r->edad ?? 0);
            if (!($t === 'D' || $t === 'M' || ($t === 'A' && $e <= 4))) continue;
            if (!$this->resolveColumnaProfesion($r->prof, $r->medico ?? '', false)) continue;

            for ($i = 1; $i <= 7; $i++) {
                $d = $this->cleanDiag($r->{"diagnostico_$i"} ?? '');
                $c = strtoupper(trim($r->{"cond_$i"} ?? ($r->cond ?? '')));
                $cod = strtoupper(trim($r->{"cod_$i"} ?? ''));
                if ((str_contains($d, 'DIARREA') || $cod === 'A09.X') && $c === $cond) {
                    $count++;
                    break;
                }
            }
        }
        return $count;
    }

    public function countMorbilidadDiarreaU5PorCondicion($informes, string $cond): int
    {
        return $informes->filter(function ($inf) use ($cond) {
            $t = strtoupper(trim($inf->tipo ?? ''));
            $e = (int)($inf->edad ?? 0);
            if (!($t === 'D' || $t === 'M' || ($t === 'A' && $e <= 4))) return false;
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            $cod = strtoupper(trim($inf->cod ?? ''));
            return (str_contains($d, 'DIARREA') || $cod === 'A09.X') && $c === $cond;
        })->count();
    }

    public function countTrans2AnemiaU5($informes): int
    {
        return $informes->filter(function ($inf) {
            $t = strtoupper(trim($inf->tipo ?? ''));
            $e = (int)($inf->edad ?? 0);
            if (!($t === 'D' || $t === 'M' || ($t === 'A' && $e <= 4))) return false;
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            $cod = strtoupper(trim($inf->cod ?? ''));
            return (str_contains($d, 'ANEMIA') || $cod === 'D64.9') && $c === 'N';
        })->count();
    }

    public function countTrans2DiabetesNuevas($informes): int
    {
        return $informes->filter(function ($inf) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            $cod = strtoupper(trim($inf->cod ?? ''));
            return (str_contains($d, 'DIABETES') || $d === 'DM2' || $cod === 'E14.9') && $c === 'N';
        })->count();
    }

    public function countTrans2HtaNuevas($informes): int
    {
        return $informes->filter(function ($inf) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            $cod = strtoupper(trim($inf->cod ?? ''));
            return (str_contains($d, 'HIPERTENSION') || $d === 'HTA' || $cod === 'I10.X') && $c === 'N';
        })->count();
    }

    public function countTrans2ErcNuevas($informes): int
    {
        return $informes->filter(function ($inf) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            $cod = strtoupper(trim($inf->cod ?? ''));
            return (str_contains($d, 'RENAL CRONICA') || str_contains($d, 'ENFERMEDAD RENAL') || $d === 'ERC') && $c === 'N';
        })->count();
    }

    public function countGlobalesDiarreaU5PorCondicion($registros, string $cond): int
    {
        $count = 0;
        foreach ($registros as $r) {
            $t = strtoupper(trim($r->tipo ?? ''));
            $e = (int)($r->edad ?? 0);
            if (!($t === 'D' || $t === 'M' || ($t === 'A' && $e <= 4))) continue;

            for ($i = 1; $i <= 7; $i++) {
                $d = $this->cleanDiag($r->{"diagnostico_$i"} ?? '');
                $c = strtoupper(trim($r->{"cond_$i"} ?? ($r->cond ?? '')));
                $cod = strtoupper(trim($r->{"cod_$i"} ?? ''));
                if ((str_contains($d, 'DIARREA') || $cod === 'A09.X') && $c === $cond) {
                    $count++;
                    break;
                }
            }
        }
        return $count;
    }

    public function countAt2rNNeumoniaU5PorCondicion($registros, string $cond): int
    {
        $count = 0;
        foreach ($registros as $r) {
            $t = strtoupper(trim($r->tipo ?? ''));
            $e = (int)($r->edad ?? 0);
            if (!($t === 'D' || $t === 'M' || ($t === 'A' && $e <= 4))) continue;
            if (!$this->resolveColumnaProfesion($r->prof, $r->medico ?? '', false)) continue;

            for ($i = 1; $i <= 7; $i++) {
                $d = $this->cleanDiag($r->{"diagnostico_$i"} ?? '');
                $c = strtoupper(trim($r->{"cond_$i"} ?? ($r->cond ?? '')));
                if ((str_contains($d, 'NEUMONIA') || str_contains($d, 'BRONCONEUMONIA')) && $c === $cond) {
                    $count++;
                    break;
                }
            }
        }
        return $count;
    }

    public function countMorbilidadNeumoniaU5PorCondicion($informes, string $cond): int
    {
        return $informes->filter(function ($inf) use ($cond) {
            $t = strtoupper(trim($inf->tipo ?? ''));
            $e = (int)($inf->edad ?? 0);
            if (!($t === 'D' || $t === 'M' || ($t === 'A' && $e <= 4))) return false;
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            return (str_contains($d, 'NEUMONIA') || str_contains($d, 'BRONCONEUMONIA')) && $c === $cond;
        })->count();
    }

    public function countGlobalesNeumoniaU5PorCondicion($registros, string $cond): int
    {
        $count = 0;
        foreach ($registros as $r) {
            $t = strtoupper(trim($r->tipo ?? ''));
            $e = (int)($r->edad ?? 0);
            if (!($t === 'D' || $t === 'M' || ($t === 'A' && $e <= 4))) continue;

            for ($i = 1; $i <= 7; $i++) {
                $d = $this->cleanDiag($r->{"diagnostico_$i"} ?? '');
                $c = strtoupper(trim($r->{"cond_$i"} ?? ($r->cond ?? '')));
                if ((str_contains($d, 'NEUMONIA') || str_contains($d, 'BRONCONEUMONIA')) && $c === $cond) {
                    $count++;
                    break;
                }
            }
        }
        return $count;
    }

    public function countAt2rNAnemiaU5PorCondicion($registros, string $cond): int
    {
        $count = 0;
        foreach ($registros as $r) {
            $t = strtoupper(trim($r->tipo ?? ''));
            $e = (int)($r->edad ?? 0);
            if (!($t === 'D' || $t === 'M' || ($t === 'A' && $e <= 4))) continue;
            if (!$this->resolveColumnaProfesion($r->prof, $r->medico ?? '', false)) continue;

            for ($i = 1; $i <= 7; $i++) {
                $d = $this->cleanDiag($r->{"diagnostico_$i"} ?? '');
                $c = strtoupper(trim($r->{"cond_$i"} ?? ($r->cond ?? '')));
                if (str_contains($d, 'ANEMIA') && $c === $cond) {
                    $count++;
                    break;
                }
            }
        }
        return $count;
    }

    public function countMorbilidadAnemiaU5PorCondicion($informes, string $cond): int
    {
        return $informes->filter(function ($inf) use ($cond) {
            $t = strtoupper(trim($inf->tipo ?? ''));
            $e = (int)($inf->edad ?? 0);
            if (!($t === 'D' || $t === 'M' || ($t === 'A' && $e <= 4))) return false;
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            return str_contains($d, 'ANEMIA') && $c === $cond;
        })->count();
    }

    public function countGlobalesAnemiaU5PorCondicion($registros, string $cond): int
    {
        $count = 0;
        foreach ($registros as $r) {
            $t = strtoupper(trim($r->tipo ?? ''));
            $e = (int)($r->edad ?? 0);
            if (!($t === 'D' || $t === 'M' || ($t === 'A' && $e <= 4))) continue;

            for ($i = 1; $i <= 7; $i++) {
                $d = $this->cleanDiag($r->{"diagnostico_$i"} ?? '');
                $c = strtoupper(trim($r->{"cond_$i"} ?? ($r->cond ?? '')));
                if (str_contains($d, 'ANEMIA') && $c === $cond) {
                    $count++;
                    break;
                }
            }
        }
        return $count;
    }

    public function countAt2rNDiabetesSubs($registros): int
    {
        return $this->countAt2rNDiabetesPorCondicion($registros, 'S');
    }

    public function countMorbilidadDiabetesSubs($informes): int
    {
        return $this->countMorbilidadDiabetesPorCondicion($informes, 'S');
    }

    public function countGlobalesDiabetesSubs($registros): int
    {
        return $this->countGlobalesDiabetesPorCondicion($registros, 'S');
    }

    public function countAt2rNHtaSubs($registros): int
    {
        return $this->countAt2rNHtaPorCondicion($registros, 'S');
    }

    public function countMorbilidadHtaSubs($informes): int
    {
        return $this->countMorbilidadHtaPorCondicion($informes, 'S');
    }

    public function countGlobalesHtaSubs($registros): int
    {
        return $this->countGlobalesHtaPorCondicion($registros, 'S');
    }

    public function countAt2rNErcNuevas($registros): int
    {
        return $this->countAt2rNErcPorCondicion($registros, 'N');
    }

    public function countAt2rNErcSubs($registros): int
    {
        return $this->countAt2rNErcPorCondicion($registros, 'S');
    }

    public function countMorbilidadErcNuevas($informes): int
    {
        return $this->countMorbilidadErcPorCondicion($informes, 'N');
    }

    public function countMorbilidadErcSubs($informes): int
    {
        return $this->countMorbilidadErcPorCondicion($informes, 'S');
    }

    public function countGlobalesErcNuevas($registros): int
    {
        return $this->countGlobalesErcPorCondicion($registros, 'N');
    }

    public function countGlobalesErcSubs($registros): int
    {
        return $this->countGlobalesErcPorCondicion($registros, 'S');
    }

    public function countMorbilidadAsmaBronquitisPorCondicion($informes, string $cond): int
    {
        return $informes->filter(function ($inf) use ($cond) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            return (str_contains($d, 'ASMA') || str_contains($d, 'BRONQUITIS')) && $c === $cond;
        })->count();
    }

    public function countGlobalesAsmaBronquitisPorCondicion($registros, string $cond): int
    {
        $count = 0;
        foreach ($registros as $r) {
            for ($i = 1; $i <= 7; $i++) {
                $d = $this->cleanDiag($r->{"diagnostico_$i"} ?? '');
                $c = strtoupper(trim($r->{"cond_$i"} ?? ($r->cond ?? '')));
                if ((str_contains($d, 'ASMA') || str_contains($d, 'BRONQUITIS')) && $c === $cond) {
                    $count++;
                    break;
                }
            }
        }
        return $count;
    }

    public function countMorbilidadIrasAltasPorCondicion($informes, string $cond): int
    {
        return $informes->filter(function ($inf) use ($cond) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            return (str_contains($d, 'RESFRIO') || str_contains($d, 'RINITIS') || str_contains($d, 'FARINGITIS') || str_contains($d, 'IRA'))
                && !str_contains($d, 'ESTREPTO')
                && $c === $cond;
        })->count();
    }

    public function countGlobalesIrasAltasPorCondicion($registros, string $cond): int
    {
        $count = 0;
        foreach ($registros as $r) {
            for ($i = 1; $i <= 7; $i++) {
                $d = $this->cleanDiag($r->{"diagnostico_$i"} ?? '');
                $c = strtoupper(trim($r->{"cond_$i"} ?? ($r->cond ?? '')));
                if ((str_contains($d, 'RESFRIO') || str_contains($d, 'RINITIS') || str_contains($d, 'FARINGITIS') || str_contains($d, 'IRA'))
                    && !str_contains($d, 'ESTREPTO')
                    && $c === $cond) {
                    $count++;
                    break;
                }
            }
        }
        return $count;
    }

    public function countAt2rNTotalMorbilidadPorCondicion($registros, string $cond): int
    {
        $count = 0;
        foreach ($registros as $r) {
            if (!$this->resolveColumnaProfesion($r->prof, $r->medico ?? '', false)) continue;
            for ($i = 1; $i <= 7; $i++) {
                $d = trim($r->{"diagnostico_$i"} ?? '');
                $c = strtoupper(trim($r->{"cond_$i"} ?? ($r->cond ?? '')));
                if (!empty($d) && $c === $cond) {
                    $count++;
                    break;
                }
            }
        }
        return $count;
    }

    public function countMorbilidadTotalPorCondicion($informes, string $cond): int
    {
        return $informes->filter(function ($inf) use ($cond) {
            return strtoupper(trim($inf->cond_diagnostico ?? '')) === $cond;
        })->count();
    }

    public function countTrans2TotalNuevas($informes): int
    {
        return $informes->filter(function ($inf) {
            return strtoupper(trim($inf->cond_diagnostico ?? '')) === 'N';
        })->count();
    }

    public function countGlobalesTotalMorbilidadPorCondicion($registros, string $cond): int
    {
        $count = 0;
        foreach ($registros as $r) {
            for ($i = 1; $i <= 7; $i++) {
                $d = trim($r->{"diagnostico_$i"} ?? '');
                $c = strtoupper(trim($r->{"cond_$i"} ?? ($r->cond ?? '')));
                if (!empty($d) && $c === $cond) {
                    $count++;
                    break;
                }
            }
        }
        return $count;
    }

    // ──────────────── MÉTODOS DE APOYO PREEXISTENTES ────────────────

    public function countAt2rNDiarreaU5($registros): int
    {
        return $this->countAt2rNDiarreaU5PorCondicion($registros, 'N') + $this->countAt2rNDiarreaU5PorCondicion($registros, 'S');
    }

    public function countMorbilidadDiarreaU5($informes): int
    {
        return $this->countMorbilidadDiarreaU5PorCondicion($informes, 'N') + $this->countMorbilidadDiarreaU5PorCondicion($informes, 'S');
    }

    public function countTrans2DiarreaU5($informes): int
    {
        return $informes->filter(function ($inf) {
            $t = strtoupper(trim($inf->tipo ?? ''));
            $e = (int)($inf->edad ?? 0);
            if (!($t === 'D' || $t === 'M' || ($t === 'A' && $e <= 4))) return false;
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $cod = strtoupper(trim($inf->cod ?? ''));
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            return (str_contains($d, 'DIARREA') || $cod === 'A09.X') && $c === 'N';
        })->count();
    }

    public function countGlobalesDiarreaU5($registros): int
    {
        return $this->countGlobalesDiarreaU5PorCondicion($registros, 'N') + $this->countGlobalesDiarreaU5PorCondicion($registros, 'S');
    }

    public function countAt2rNDiarreaTotal($registros): int
    {
        $count = 0;
        foreach ($registros as $r) {
            if (!$this->resolveColumnaProfesion($r->prof, $r->medico ?? '', false)) continue;
            for ($i = 1; $i <= 7; $i++) {
                $d = $this->cleanDiag($r->{"diagnostico_$i"} ?? '');
                $cod = strtoupper(trim($r->{"cod_$i"} ?? ''));
                if (str_contains($d, 'DIARREA') || $cod === 'A09.X') {
                    $count++;
                    break;
                }
            }
        }
        return $count;
    }

    public function countMorbilidadDiarreaTotal($informes): int
    {
        return $informes->filter(function ($inf) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $cod = strtoupper(trim($inf->cod ?? ''));
            return str_contains($d, 'DIARREA') || $cod === 'A09.X';
        })->count();
    }

    public function countTrans2DiarreaTotal($informes): int
    {
        return $informes->filter(function ($inf) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $cod = strtoupper(trim($inf->cod ?? ''));
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            return (str_contains($d, 'DIARREA') || $cod === 'A09.X') && $c === 'N';
        })->count();
    }

    public function countGlobalesDiarreaTotal($registros): int
    {
        $count = 0;
        foreach ($registros as $r) {
            for ($i = 1; $i <= 7; $i++) {
                $d = $this->cleanDiag($r->{"diagnostico_$i"} ?? '');
                $cod = strtoupper(trim($r->{"cod_$i"} ?? ''));
                if (str_contains($d, 'DIARREA') || $cod === 'A09.X') {
                    $count++;
                    break;
                }
            }
        }
        return $count;
    }

    public function countAt2rNNeumoniaU5($registros): int
    {
        return $this->countAt2rNNeumoniaU5PorCondicion($registros, 'N') + $this->countAt2rNNeumoniaU5PorCondicion($registros, 'S');
    }

    public function countMorbilidadNeumoniaU5($informes): int
    {
        return $this->countMorbilidadNeumoniaU5PorCondicion($informes, 'N') + $this->countMorbilidadNeumoniaU5PorCondicion($informes, 'S');
    }

    public function countTrans2NeumoniaU5($informes): int
    {
        return $informes->filter(function ($inf) {
            $t = strtoupper(trim($inf->tipo ?? ''));
            $e = (int)($inf->edad ?? 0);
            if (!($t === 'D' || $t === 'M' || ($t === 'A' && $e <= 4))) return false;
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            return (str_contains($d, 'NEUMONIA') || str_contains($d, 'BRONCONEUMONIA')) && $c === 'N';
        })->count();
    }

    public function countGlobalesNeumoniaU5($registros): int
    {
        return $this->countGlobalesNeumoniaU5PorCondicion($registros, 'N') + $this->countGlobalesNeumoniaU5PorCondicion($registros, 'S');
    }

    public function countAt2rNAnemiaU5($registros): int
    {
        return $this->countAt2rNAnemiaU5PorCondicion($registros, 'N') + $this->countAt2rNAnemiaU5PorCondicion($registros, 'S');
    }

    public function countMorbilidadAnemiaU5($informes): int
    {
        return $this->countMorbilidadAnemiaU5PorCondicion($informes, 'N') + $this->countMorbilidadAnemiaU5PorCondicion($informes, 'S');
    }

    public function countGlobalesAnemiaU5($registros): int
    {
        return $this->countGlobalesAnemiaU5PorCondicion($registros, 'N') + $this->countGlobalesAnemiaU5PorCondicion($registros, 'S');
    }

    public function countAt2rNDiabetesNuevas($registros): int
    {
        return $this->countAt2rNDiabetesPorCondicion($registros, 'N');
    }

    public function countAt2rNDiabetesTotal($registros): int
    {
        return $this->countAt2rNDiabetesPorCondicion($registros, 'N') + $this->countAt2rNDiabetesPorCondicion($registros, 'S');
    }

    public function countMorbilidadDiabetesNuevas($informes): int
    {
        return $this->countMorbilidadDiabetesPorCondicion($informes, 'N');
    }

    public function countMorbilidadDiabetesTotal($informes): int
    {
        return $this->countMorbilidadDiabetesPorCondicion($informes, 'N') + $this->countMorbilidadDiabetesPorCondicion($informes, 'S');
    }

    public function countGlobalesDiabetesNuevas($registros): int
    {
        return $this->countGlobalesDiabetesPorCondicion($registros, 'N');
    }

    public function countGlobalesDiabetesTotal($registros): int
    {
        return $this->countGlobalesDiabetesPorCondicion($registros, 'N') + $this->countGlobalesDiabetesPorCondicion($registros, 'S');
    }

    private function countAt2rNDiabetesPorCondicion($registros, string $cond): int
    {
        $count = 0;
        foreach ($registros as $r) {
            if (!$this->resolveColumnaProfesion($r->prof, $r->medico ?? '', false)) continue;
            for ($i = 1; $i <= 7; $i++) {
                $d = $this->cleanDiag($r->{"diagnostico_$i"} ?? '');
                $c = strtoupper(trim($r->{"cond_$i"} ?? ($r->cond ?? '')));
                $cod = strtoupper(trim($r->{"cod_$i"} ?? ''));
                if ((str_contains($d, 'DIABETES') || $cod === 'E14.9' || $d === 'DM2') && $c === $cond) {
                    $count++;
                    break;
                }
            }
        }
        return $count;
    }

    private function countMorbilidadDiabetesPorCondicion($informes, string $cond): int
    {
        return $informes->filter(function ($inf) use ($cond) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            $cod = strtoupper(trim($inf->cod ?? ''));
            return (str_contains($d, 'DIABETES') || $cod === 'E14.9' || $d === 'DM2') && $c === $cond;
        })->count();
    }

    private function countGlobalesDiabetesPorCondicion($registros, string $cond): int
    {
        $count = 0;
        foreach ($registros as $r) {
            for ($i = 1; $i <= 7; $i++) {
                $d = $this->cleanDiag($r->{"diagnostico_$i"} ?? '');
                $c = strtoupper(trim($r->{"cond_$i"} ?? ($r->cond ?? '')));
                $cod = strtoupper(trim($r->{"cod_$i"} ?? ''));
                if ((str_contains($d, 'DIABETES') || $cod === 'E14.9' || $d === 'DM2') && $c === $cond) {
                    $count++;
                    break;
                }
            }
        }
        return $count;
    }

    public function countAt2rNHtaNuevas($registros): int
    {
        return $this->countAt2rNHtaPorCondicion($registros, 'N');
    }

    public function countAt2rNHtaTotal($registros): int
    {
        return $this->countAt2rNHtaPorCondicion($registros, 'N') + $this->countAt2rNHtaPorCondicion($registros, 'S');
    }

    public function countMorbilidadHtaNuevas($informes): int
    {
        return $this->countMorbilidadHtaPorCondicion($informes, 'N');
    }

    public function countMorbilidadHtaTotal($informes): int
    {
        return $this->countMorbilidadHtaPorCondicion($informes, 'N') + $this->countMorbilidadHtaPorCondicion($informes, 'S');
    }

    public function countGlobalesHtaNuevas($registros): int
    {
        return $this->countGlobalesHtaPorCondicion($registros, 'N');
    }

    public function countGlobalesHtaTotal($registros): int
    {
        return $this->countGlobalesHtaPorCondicion($registros, 'N') + $this->countGlobalesHtaPorCondicion($registros, 'S');
    }

    private function countAt2rNHtaPorCondicion($registros, string $cond): int
    {
        $count = 0;
        foreach ($registros as $r) {
            if (!$this->resolveColumnaProfesion($r->prof, $r->medico ?? '', false)) continue;
            for ($i = 1; $i <= 7; $i++) {
                $d = $this->cleanDiag($r->{"diagnostico_$i"} ?? '');
                $c = strtoupper(trim($r->{"cond_$i"} ?? ($r->cond ?? '')));
                $cod = strtoupper(trim($r->{"cod_$i"} ?? ''));
                if ((str_contains($d, 'HIPERTENSION') || $d === 'HTA' || $cod === 'I10.X') && $c === $cond) {
                    $count++;
                    break;
                }
            }
        }
        return $count;
    }

    private function countMorbilidadHtaPorCondicion($informes, string $cond): int
    {
        return $informes->filter(function ($inf) use ($cond) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            $cod = strtoupper(trim($inf->cod ?? ''));
            return (str_contains($d, 'HIPERTENSION') || $d === 'HTA' || $cod === 'I10.X') && $c === $cond;
        })->count();
    }

    private function countGlobalesHtaPorCondicion($registros, string $cond): int
    {
        $count = 0;
        foreach ($registros as $r) {
            for ($i = 1; $i <= 7; $i++) {
                $d = $this->cleanDiag($r->{"diagnostico_$i"} ?? '');
                $c = strtoupper(trim($r->{"cond_$i"} ?? ($r->cond ?? '')));
                $cod = strtoupper(trim($r->{"cod_$i"} ?? ''));
                if ((str_contains($d, 'HIPERTENSION') || $d === 'HTA' || $cod === 'I10.X') && $c === $cond) {
                    $count++;
                    break;
                }
            }
        }
        return $count;
    }

    private function countAt2rNErcPorCondicion($registros, string $cond): int
    {
        $count = 0;
        foreach ($registros as $r) {
            if (!$this->resolveColumnaProfesion($r->prof, $r->medico ?? '', false)) continue;
            for ($i = 1; $i <= 7; $i++) {
                $d = $this->cleanDiag($r->{"diagnostico_$i"} ?? '');
                $c = strtoupper(trim($r->{"cond_$i"} ?? ($r->cond ?? '')));
                if ((str_contains($d, 'RENAL CRONICA') || str_contains($d, 'ENFERMEDAD RENAL') || $d === 'ERC') && $c === $cond) {
                    $count++;
                    break;
                }
            }
        }
        return $count;
    }

    private function countMorbilidadErcPorCondicion($informes, string $cond): int
    {
        return $informes->filter(function ($inf) use ($cond) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            return (str_contains($d, 'RENAL CRONICA') || str_contains($d, 'ENFERMEDAD RENAL') || $d === 'ERC') && $c === $cond;
        })->count();
    }

    private function countGlobalesErcPorCondicion($registros, string $cond): int
    {
        $count = 0;
        foreach ($registros as $r) {
            for ($i = 1; $i <= 7; $i++) {
                $d = $this->cleanDiag($r->{"diagnostico_$i"} ?? '');
                $c = strtoupper(trim($r->{"cond_$i"} ?? ($r->cond ?? '')));
                if ((str_contains($d, 'RENAL CRONICA') || str_contains($d, 'ENFERMEDAD RENAL') || $d === 'ERC') && $c === $cond) {
                    $count++;
                    break;
                }
            }
        }
        return $count;
    }

    public function countMorbilidadDengueSinSignos($informes): int
    {
        return $informes->filter(function ($inf) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $cod = strtoupper(trim($inf->cod ?? ''));
            return (str_contains($d, 'DENGUE') && !str_contains($d, 'GRAVE') && !str_contains($d, 'ALARMA')) || $cod === 'A90.X';
        })->count();
    }

    public function countTrans2DengueSinSignos($informes): int
    {
        return $this->countMorbilidadDengueSinSignos($informes);
    }

    public function countGlobalesDengueSinSignos($registros): int
    {
        $count = 0;
        foreach ($registros as $r) {
            for ($i = 1; $i <= 7; $i++) {
                $d = $this->cleanDiag($r->{"diagnostico_$i"} ?? '');
                $cod = strtoupper(trim($r->{"cod_$i"} ?? ''));
                if ((str_contains($d, 'DENGUE') && !str_contains($d, 'GRAVE') && !str_contains($d, 'ALARMA')) || $cod === 'A90.X') {
                    $count++;
                    break;
                }
            }
        }
        return $count;
    }

    public function countMorbilidadDengueConSignos($informes): int
    {
        return $informes->filter(function ($inf) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            return str_contains($d, 'DENGUE') && (str_contains($d, 'ALARMA') || str_contains($d, 'DCSA') || str_contains($d, 'SIGNOS'));
        })->count();
    }

    public function countTrans2DengueConSignos($informes): int
    {
        return $this->countMorbilidadDengueConSignos($informes);
    }

    public function countGlobalesDengueConSignos($registros): int
    {
        $count = 0;
        foreach ($registros as $r) {
            for ($i = 1; $i <= 7; $i++) {
                $d = $this->cleanDiag($r->{"diagnostico_$i"} ?? '');
                if (str_contains($d, 'DENGUE') && (str_contains($d, 'ALARMA') || str_contains($d, 'DCSA') || str_contains($d, 'SIGNOS'))) {
                    $count++;
                    break;
                }
            }
        }
        return $count;
    }

    public function countMorbilidadDengueGrave($informes): int
    {
        return $informes->filter(function ($inf) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $cod = strtoupper(trim($inf->cod ?? ''));
            return (str_contains($d, 'DENGUE') && (str_contains($d, 'GRAVE') || str_contains($d, 'HEMORRAGICO'))) || $cod === 'A91.X';
        })->count();
    }

    public function countTrans2DengueGrave($informes): int
    {
        return $this->countMorbilidadDengueGrave($informes);
    }

    public function countGlobalesDengueGrave($registros): int
    {
        $count = 0;
        foreach ($registros as $r) {
            for ($i = 1; $i <= 7; $i++) {
                $d = $this->cleanDiag($r->{"diagnostico_$i"} ?? '');
                $cod = strtoupper(trim($r->{"cod_$i"} ?? ''));
                if ((str_contains($d, 'DENGUE') && (str_contains($d, 'GRAVE') || str_contains($d, 'HEMORRAGICO'))) || $cod === 'A91.X') {
                    $count++;
                    break;
                }
            }
        }
        return $count;
    }

    public function countTrans2AsmaBronquitis($informes): int
    {
        return $informes->filter(function ($inf) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            return str_contains($d, 'ASMA') || str_contains($d, 'BRONQUITIS');
        })->count();
    }

    public function countTrans2IrasAltas($informes): int
    {
        return $informes->filter(function ($inf) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            return (str_contains($d, 'RESFRIO') || str_contains($d, 'RINITIS') || str_contains($d, 'FARINGITIS') || str_contains($d, 'IRA'))
                && !str_contains($d, 'ESTREPTO');
        })->count();
    }

    public function countAt2rNTuberculosis($registros): int
    {
        $count = 0;
        foreach ($registros as $r) {
            if (!$this->resolveColumnaProfesion($r->prof, $r->medico ?? '', false)) continue;
            for ($i = 1; $i <= 7; $i++) {
                $d = $this->cleanDiag($r->{"diagnostico_$i"} ?? '');
                $cod = strtoupper(trim($r->{"cod_$i"} ?? ''));
                if (str_contains($d, 'TUBERCULOSIS') || str_contains($d, 'TB') || str_contains($d, 'BK') || str_contains($cod, 'A15')) {
                    $count++;
                    break;
                }
            }
        }
        return $count;
    }

    public function countMorbilidadTuberculosis($informes): int
    {
        return $informes->filter(function ($inf) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $cod = strtoupper(trim($inf->cod ?? ''));
            return str_contains($d, 'TUBERCULOSIS') || str_contains($d, 'TB') || str_contains($d, 'BK') || str_contains($cod, 'A15');
        })->count();
    }

    public function countTrans2Tuberculosis($informes): int
    {
        return $this->countMorbilidadTuberculosis($informes);
    }

    public function countGlobalesTuberculosis($registros): int
    {
        $count = 0;
        foreach ($registros as $r) {
            for ($i = 1; $i <= 7; $i++) {
                $d = $this->cleanDiag($r->{"diagnostico_$i"} ?? '');
                $cod = strtoupper(trim($r->{"cod_$i"} ?? ''));
                if (str_contains($d, 'TUBERCULOSIS') || str_contains($d, 'TB') || str_contains($d, 'BK') || str_contains($cod, 'A15')) {
                    $count++;
                    break;
                }
            }
        }
        return $count;
    }

    public function countAt2rNTotalMorbilidad($registros): int
    {
        $count = 0;
        foreach ($registros as $r) {
            if (!$this->resolveColumnaProfesion($r->prof, $r->medico ?? '', false)) continue;
            for ($i = 1; $i <= 7; $i++) {
                $d = trim($r->{"diagnostico_$i"} ?? '');
                if (!empty($d)) {
                    $count++;
                    break;
                }
            }
        }
        return $count;
    }

    public function countMorbilidadTotalGeneral($informes): int
    {
        return $informes->count();
    }

    public function countGlobalesTotalMorbilidad($registros): int
    {
        $count = 0;
        foreach ($registros as $r) {
            for ($i = 1; $i <= 7; $i++) {
                $d = trim($r->{"diagnostico_$i"} ?? '');
                if (!empty($d)) {
                    $count++;
                    break;
                }
            }
        }
        return $count;
    }
}
