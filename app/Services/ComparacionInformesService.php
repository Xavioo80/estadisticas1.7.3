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
     * Realiza la comparación cruzada integral de consistencia epidemiológica
     * entre AT2-R(N), Morbilidad, TRANS-2, ITS y Registros Globales (AT-1).
     */
    public function comparar(int|string $ano, string $mes, string $jornada = 'TODAS'): array
    {
        $mes = strtoupper(trim($mes));
        $cacheKey = "comp_informes_v7_{$ano}_{$mes}_{$jornada}";

        return Cache::remember($cacheKey, 60, function() use ($ano, $mes, $jornada) {
            return $this->ejecutarComparacion($ano, $mes, $jornada);
        });
    }

    private function cleanDiag(?string $d): string
    {
        $d = mb_strtoupper(trim((string)$d), 'UTF-8');
        return str_replace(['Á', 'É', 'Í', 'Ó', 'Ú', 'Ü', 'Ñ'], ['A', 'E', 'I', 'O', 'U', 'U', 'N'], $d);
    }

    private function ejecutarComparacion(int|string $ano, string $mes, string $jornada): array
    {
        // 1. Obtener registros de RegistroGlobal (Fuente única para AT2-R N, Morbilidad, TRANS-2, ITS y Raw)
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

        // 2. Desplegar los 7 diagnósticos en memoria para Morbilidad, TRANS-2 e ITS
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

        // 3. Definición exhaustiva de comparaciones
        $comparaciones = [
            // ──────────────── DIARREAS E INFECCIONES INTESTINALES ────────────────
            [
                'id' => 'diarrea_u5_n',
                'categoria' => 'Enfermedades Infecciosas (< 5 Años)',
                'label' => 'Diarreas < 5 Años - Casos Nuevos (N)',
                'condicion' => 'N',
                'codigo_cie' => 'A09.X',
                'descripcion' => 'Rehidratación oral / Diarreas nuevas en niños menores de 5 años',
                'at2rn' => $this->countAt2rNDiarreaU5PorCondicion($registrosGlobales, 'N'),
                'morbilidad' => $this->countMorbilidadDiarreaU5PorCondicion($informes, 'N'),
                'trans2' => $this->countTrans2DiarreaU5($informes),
                'its' => null,
                'globales' => $this->countGlobalesDiarreaU5PorCondicion($registrosGlobales, 'N'),
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
                'trans2' => null,
                'its' => null,
                'globales' => $this->countGlobalesDiarreaU5PorCondicion($registrosGlobales, 'S'),
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
                'trans2' => null,
                'its' => null,
                'globales' => $this->countGlobalesDiarreaU5($registrosGlobales),
            ],
            [
                'id' => 'diarrea_total_n',
                'categoria' => 'Enfermedades Infecciosas e Intestinales',
                'label' => 'Diarreas Totales (Todas las Edades) - Nuevos (N)',
                'condicion' => 'N',
                'codigo_cie' => 'A09.X',
                'descripcion' => 'Total de casos nuevos de diarreas en todas las edades',
                'at2rn' => null,
                'morbilidad' => $this->countMorbilidadDiarreaTotalNuevas($informes),
                'trans2' => $this->countTrans2DiarreaTotalNuevas($informes),
                'its' => null,
                'globales' => $this->countGlobalesDiarreaTotalNuevas($registrosGlobales),
            ],

            // ──────────────── ENFERMEDADES INMUNOPREVENIBLES ────────────────
            [
                'id' => 'polio_n',
                'categoria' => 'Enfermedades Inmunoprevenibles',
                'label' => 'Poliomielitis Aguda - Casos Nuevos (N)',
                'condicion' => 'N',
                'codigo_cie' => 'A80.9',
                'descripcion' => 'Parálisis flácida aguda / Poliomielitis',
                'at2rn' => null,
                'morbilidad' => $this->countMorbilidadPolioNuevas($informes),
                'trans2' => $this->countTrans2PolioNuevas($informes),
                'its' => null,
                'globales' => $this->countGlobalesPolioNuevas($registrosGlobales),
            ],
            [
                'id' => 'sarampion_n',
                'categoria' => 'Enfermedades Inmunoprevenibles',
                'label' => 'Sarampión - Casos Nuevos (N)',
                'condicion' => 'N',
                'codigo_cie' => 'B05.9',
                'descripcion' => 'Enfermedad febril exantemática / Sarampión',
                'at2rn' => null,
                'morbilidad' => $this->countMorbilidadSarampionNuevas($informes),
                'trans2' => $this->countTrans2SarampionNuevas($informes),
                'its' => null,
                'globales' => $this->countGlobalesSarampionNuevas($registrosGlobales),
            ],
            [
                'id' => 'tosferina_n',
                'categoria' => 'Enfermedades Inmunoprevenibles',
                'label' => 'Tosferina - Casos Nuevos (N)',
                'condicion' => 'N',
                'codigo_cie' => 'A37.9',
                'descripcion' => 'Tosferina / Síndrome Coqueluchoide',
                'at2rn' => null,
                'morbilidad' => $this->countMorbilidadTosferinaNuevas($informes),
                'trans2' => $this->countTrans2TosferinaNuevas($informes),
                'its' => null,
                'globales' => $this->countGlobalesTosferinaNuevas($registrosGlobales),
            ],
            [
                'id' => 'difteria_n',
                'categoria' => 'Enfermedades Inmunoprevenibles',
                'label' => 'Difteria - Casos Nuevos (N)',
                'condicion' => 'N',
                'codigo_cie' => 'A36.9',
                'descripcion' => 'Infección aguda por Corynebacterium diphtheriae',
                'at2rn' => null,
                'morbilidad' => $this->countMorbilidadDifteriaNuevas($informes),
                'trans2' => $this->countTrans2DifteriaNuevas($informes),
                'its' => null,
                'globales' => $this->countGlobalesDifteriaNuevas($registrosGlobales),
            ],
            [
                'id' => 'tetanos_neo_n',
                'categoria' => 'Enfermedades Inmunoprevenibles',
                'label' => 'Tétanos Neonatorum - Casos Nuevos (N)',
                'condicion' => 'N',
                'codigo_cie' => 'A33.X',
                'descripcion' => 'Tétanos en recién nacidos',
                'at2rn' => null,
                'morbilidad' => $this->countMorbilidadTetanosNeoNuevas($informes),
                'trans2' => $this->countTrans2TetanosNeoNuevas($informes),
                'its' => null,
                'globales' => $this->countGlobalesTetanosNeoNuevas($registrosGlobales),
            ],
            [
                'id' => 'parotiditis_n',
                'categoria' => 'Enfermedades Inmunoprevenibles',
                'label' => 'Parotiditis Infecciosa - Casos Nuevos (N)',
                'condicion' => 'N',
                'codigo_cie' => 'K11.2 / B26',
                'descripcion' => 'Parotiditis / Paperas',
                'at2rn' => null,
                'morbilidad' => $this->countMorbilidadParotiditisNuevas($informes),
                'trans2' => $this->countTrans2ParotiditisNuevas($informes),
                'its' => null,
                'globales' => $this->countGlobalesParotiditisNuevas($registrosGlobales),
            ],
            [
                'id' => 'rubeola_n',
                'categoria' => 'Enfermedades Inmunoprevenibles',
                'label' => 'Rubéola - Casos Nuevos (N)',
                'condicion' => 'N',
                'codigo_cie' => 'B06.9',
                'descripcion' => 'Rubéola aguda',
                'at2rn' => null,
                'morbilidad' => $this->countMorbilidadRubeolaNuevas($informes),
                'trans2' => $this->countTrans2RubeolaNuevas($informes),
                'its' => null,
                'globales' => $this->countGlobalesRubeolaNuevas($registrosGlobales),
            ],
            [
                'id' => 'rubeola_cong_n',
                'categoria' => 'Enfermedades Inmunoprevenibles',
                'label' => 'Síndrome de Rubéola Congénita - Casos Nuevos (N)',
                'condicion' => 'N',
                'codigo_cie' => 'P35.0',
                'descripcion' => 'Rubéola congénita',
                'at2rn' => null,
                'morbilidad' => $this->countMorbilidadRubeolaCongNuevas($informes),
                'trans2' => $this->countTrans2RubeolaCongNuevas($informes),
                'its' => null,
                'globales' => $this->countGlobalesRubeolaCongNuevas($registrosGlobales),
            ],
            [
                'id' => 'varicela_n',
                'categoria' => 'Enfermedades Inmunoprevenibles',
                'label' => 'Varicela - Casos Nuevos (N)',
                'condicion' => 'N',
                'codigo_cie' => 'B01.9',
                'descripcion' => 'Varicela sin complicaciones',
                'at2rn' => null,
                'morbilidad' => $this->countMorbilidadVaricelaNuevas($informes),
                'trans2' => $this->countTrans2VaricelaNuevas($informes),
                'its' => null,
                'globales' => $this->countGlobalesVaricelaNuevas($registrosGlobales),
            ],

            // ──────────────── ENFERMEDADES RESPIRATORIAS ────────────────
            [
                'id' => 'neumonia_u5_n',
                'categoria' => 'Enfermedades Respiratorias (< 5 Años)',
                'label' => 'Neumonías < 5 Años - Casos Nuevos (N)',
                'condicion' => 'N',
                'codigo_cie' => 'J18.9 / J18.0',
                'descripcion' => 'Casos de Neumonía / Bronconeumonía nuevos en niños < 5 años',
                'at2rn' => $this->countAt2rNNeumoniaU5PorCondicion($registrosGlobales, 'N'),
                'morbilidad' => $this->countMorbilidadNeumoniaU5PorCondicion($informes, 'N'),
                'trans2' => $this->countTrans2NeumoniaU5($informes),
                'its' => null,
                'globales' => $this->countGlobalesNeumoniaU5PorCondicion($registrosGlobales, 'N'),
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
                'its' => null,
                'globales' => $this->countGlobalesNeumoniaU5PorCondicion($registrosGlobales, 'S'),
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
                'trans2' => null,
                'its' => null,
                'globales' => $this->countGlobalesNeumoniaU5($registrosGlobales),
            ],
            [
                'id' => 'neumonia_total_n',
                'categoria' => 'Otras Patologías Respiratorias',
                'label' => 'Neumonías / Bronconeumonías (Todas las Edades) - Nuevos (N)',
                'condicion' => 'N',
                'codigo_cie' => 'J18.9 - J18.0',
                'descripcion' => 'Total de casos nuevos de Neumonías en todas las edades',
                'at2rn' => null,
                'morbilidad' => $this->countMorbilidadNeumoniaTotalNuevas($informes),
                'trans2' => $this->countTrans2NeumoniaTotalNuevas($informes),
                'its' => null,
                'globales' => $this->countGlobalesNeumoniaTotalNuevas($registrosGlobales),
            ],
            [
                'id' => 'asma_bronquitis_n',
                'categoria' => 'Otras Patologías Respiratorias',
                'label' => 'Asma Bronquial / Bronquitis - Nuevos (N)',
                'condicion' => 'N',
                'codigo_cie' => 'J45 / J20 - J21',
                'descripcion' => 'Casos nuevos: Morbilidad agrupa Asma/Bronquitis, TRANS-2 suma Asma + Bronquitis',
                'at2rn' => null,
                'morbilidad' => $this->countMorbilidadAsmaBronquitisPorCondicion($informes, 'N'),
                'trans2' => $this->countTrans2AsmaBronquitisSuma($informes),
                'its' => null,
                'globales' => $this->countGlobalesAsmaBronquitisPorCondicion($registrosGlobales, 'N'),
            ],
            [
                'id' => 'faringoamigdalitis_n',
                'categoria' => 'Otras Patologías Respiratorias',
                'label' => 'Faringoamigdalitis (Viral + Estreptocócica + Faringitis) - Nuevos (N)',
                'condicion' => 'N',
                'codigo_cie' => 'J02.0 - J03.0',
                'descripcion' => 'Casos nuevos de Faringoamigdalitis en Morbilidad vs TRANS-2',
                'at2rn' => null,
                'morbilidad' => $this->countMorbilidadFaringoamigdalitisSuma($informes),
                'trans2' => $this->countTrans2Faringoamigdalitis($informes),
                'its' => null,
                'globales' => $this->countGlobalesFaringoamigdalitisSuma($registrosGlobales),
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
                'its' => null,
                'globales' => $this->countGlobalesIrasAltasPorCondicion($registrosGlobales, 'N'),
            ],

            // ──────────────── ENFERMEDADES VECTORIALES Y FEBRILES ────────────────
            [
                'id' => 'tuberculosis',
                'categoria' => 'Enfermedades Vectoriales y Transmisibles',
                'label' => 'Tuberculosis (Presuntiva / Confirmada)',
                'condicion' => 'TOTAL',
                'codigo_cie' => 'A15 - A19 / J16.4',
                'descripcion' => 'Casos y detecciones de Tuberculosis',
                'at2rn' => $this->countAt2rNTuberculosis($registrosGlobales),
                'morbilidad' => $this->countMorbilidadTuberculosis($informes),
                'trans2' => $this->countTrans2Tuberculosis($informes),
                'its' => null,
                'globales' => $this->countGlobalesTuberculosis($registrosGlobales),
            ],
            [
                'id' => 'malaria_n',
                'categoria' => 'Enfermedades Vectoriales y Transmisibles',
                'label' => 'Malaria (Casos Confirmados) - Nuevos (N)',
                'condicion' => 'N',
                'codigo_cie' => 'B54.X',
                'descripcion' => 'Casos de Malaria / Paludismo confirmados',
                'at2rn' => null,
                'morbilidad' => $this->countMorbilidadMalariaNuevas($informes),
                'trans2' => $this->countTrans2MalariaNuevas($informes),
                'its' => null,
                'globales' => $this->countGlobalesMalariaNuevas($registrosGlobales),
            ],
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
                'its' => null,
                'globales' => $this->countGlobalesDengueSinSignos($registrosGlobales),
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
                'its' => null,
                'globales' => $this->countGlobalesDengueConSignos($registrosGlobales),
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
                'its' => null,
                'globales' => $this->countGlobalesDengueGrave($registrosGlobales),
            ],
            [
                'id' => 'chikungunya_n',
                'categoria' => 'Enfermedades Vectoriales y Transmisibles',
                'label' => 'Chikungunya - Casos Nuevos (N)',
                'condicion' => 'N',
                'codigo_cie' => 'A92.0',
                'descripcion' => 'Casos nuevos de Fiebre de Chikungunya',
                'at2rn' => null,
                'morbilidad' => $this->countMorbilidadChikungunyaNuevas($informes),
                'trans2' => $this->countTrans2ChikungunyaNuevas($informes),
                'its' => null,
                'globales' => $this->countGlobalesChikungunyaNuevas($registrosGlobales),
            ],

            // ──────────────── INFECCIONES DE TRANSMISIÓN SEXUAL (ITS) ────────────────
            [
                'id' => 'sifilis_n',
                'categoria' => 'Infecciones de Transmisión Sexual (ITS)',
                'label' => 'Sífilis - Casos Nuevos (N)',
                'condicion' => 'N',
                'codigo_cie' => 'A53.9',
                'descripcion' => 'Casos nuevos de Sífilis en Morbilidad, TRANS-2 e ITS',
                'at2rn' => null,
                'morbilidad' => $this->countMorbilidadSifilisNuevas($informes),
                'trans2' => $this->countTrans2SifilisNuevas($informes),
                'its' => $this->countItsSifilisNuevas($informes),
                'globales' => $this->countGlobalesSifilisNuevas($registrosGlobales),
            ],
            [
                'id' => 'gonorrea_n',
                'categoria' => 'Infecciones de Transmisión Sexual (ITS)',
                'label' => 'Gonorrea - Casos Nuevos (N)',
                'condicion' => 'N',
                'codigo_cie' => 'A54.9',
                'descripcion' => 'Casos nuevos de Infección Gonocócica en Morbilidad, TRANS-2 e ITS',
                'at2rn' => null,
                'morbilidad' => $this->countMorbilidadGonorreaNuevas($informes),
                'trans2' => $this->countTrans2GonorreaNuevas($informes),
                'its' => $this->countItsGonorreaNuevas($informes),
                'globales' => $this->countGlobalesGonorreaNuevas($registrosGlobales),
            ],
            [
                'id' => 'vih_sida_n',
                'categoria' => 'Infecciones de Transmisión Sexual (ITS)',
                'label' => 'VIH / SIDA (Asintomáticos + Avanzados) - Nuevos (N)',
                'condicion' => 'N',
                'codigo_cie' => 'B24.9',
                'descripcion' => 'Casos nuevos de VIH/SIDA sumados en Morbilidad, TRANS-2 e ITS',
                'at2rn' => null,
                'morbilidad' => $this->countMorbilidadVihSidaNuevas($informes),
                'trans2' => $this->countTrans2VihSidaNuevas($informes),
                'its' => $this->countItsVihSidaNuevas($informes),
                'globales' => $this->countGlobalesVihSidaNuevas($registrosGlobales),
            ],
            [
                'id' => 'condiloma_n',
                'categoria' => 'Infecciones de Transmisión Sexual (ITS)',
                'label' => 'Condiloma Acuminado - Casos Nuevos (N)',
                'condicion' => 'N',
                'codigo_cie' => 'A63.0',
                'descripcion' => 'Verrugas anogenitales / Condilomas en TRANS-2 e ITS',
                'at2rn' => null,
                'morbilidad' => null,
                'trans2' => $this->countTrans2CondilomaNuevas($informes),
                'its' => $this->countItsCondilomaNuevas($informes),
                'globales' => $this->countGlobalesCondilomaNuevas($registrosGlobales),
            ],
            [
                'id' => 'herpes_genital_n',
                'categoria' => 'Infecciones de Transmisión Sexual (ITS)',
                'label' => 'Herpes Genital - Casos Nuevos (N)',
                'condicion' => 'N',
                'codigo_cie' => 'A60.0',
                'descripcion' => 'Infección anogenital por virus del herpes en Morbilidad, TRANS-2 e ITS',
                'at2rn' => null,
                'morbilidad' => $this->countMorbilidadHerpesGenitalNuevas($informes),
                'trans2' => $this->countTrans2HerpesGenitalNuevas($informes),
                'its' => $this->countItsHerpesGenitalNuevas($informes),
                'globales' => $this->countGlobalesHerpesGenitalNuevas($registrosGlobales),
            ],
            [
                'id' => 'hepatitis_b_n',
                'categoria' => 'Infecciones de Transmisión Sexual (ITS)',
                'label' => 'Hepatitis B - Casos Nuevos (N)',
                'condicion' => 'N',
                'codigo_cie' => 'B16.9',
                'descripcion' => 'Infección por Hepatitis B en Morbilidad, TRANS-2 e ITS',
                'at2rn' => null,
                'morbilidad' => $this->countMorbilidadHepatitisBNuevas($informes),
                'trans2' => $this->countTrans2HepatitisBNuevas($informes),
                'its' => $this->countItsHepatitisBNuevas($informes),
                'globales' => $this->countGlobalesHepatitisBNuevas($registrosGlobales),
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
                'its' => null,
                'globales' => $this->countGlobalesHtaNuevas($registrosGlobales),
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
                'its' => null,
                'globales' => $this->countGlobalesHtaSubs($registrosGlobales),
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
                'its' => null,
                'globales' => $this->countGlobalesHtaTotal($registrosGlobales),
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
                'its' => null,
                'globales' => $this->countGlobalesDiabetesNuevas($registrosGlobales),
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
                'its' => null,
                'globales' => $this->countGlobalesDiabetesSubs($registrosGlobales),
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
                'its' => null,
                'globales' => $this->countGlobalesDiabetesTotal($registrosGlobales),
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
                'its' => null,
                'globales' => $this->countGlobalesErcNuevas($registrosGlobales),
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
                'its' => null,
                'globales' => $this->countGlobalesErcSubs($registrosGlobales),
            ],
            [
                'id' => 'erc_total',
                'categoria' => 'Enfermedades Crónicas No Transmisibles',
                'label' => 'Enfermedad Renal Crónica - Total Atenciones (N + S)',
                'condicion' => 'TOTAL',
                'codigo_cie' => 'N18.9',
                'descripcion' => 'Total de atenciones de ERC',
                'at2rn' => $this->countAt2rNErcTotal($registrosGlobales),
                'morbilidad' => $this->countMorbilidadErcTotal($informes),
                'trans2' => null,
                'its' => null,
                'globales' => $this->countGlobalesErcTotal($registrosGlobales),
            ],

            // ──────────────── NUTRICIONALES / HEMATOLÓGICAS (< 5 AÑOS) ────────────────
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
                'its' => null,
                'globales' => $this->countGlobalesAnemiaU5PorCondicion($registrosGlobales, 'N'),
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
                'its' => null,
                'globales' => $this->countGlobalesAnemiaU5PorCondicion($registrosGlobales, 'S'),
            ],

            // ──────────────── RESUMEN GLOBAL DE MORBILIDAD ────────────────
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
                'its' => null,
                'globales' => $this->countGlobalesTotalMorbilidadPorCondicion($registrosGlobales, 'N'),
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
                'its' => null,
                'globales' => $this->countGlobalesTotalMorbilidad($registrosGlobales),
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
            if ($item['its'] !== null) $valoresValidos['its'] = $item['its'];
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

    // ──────────────── MÉTODOS DE CONTEO DETALLADOS ────────────────

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

    public function countAt2rNDiarreaU5($registros): int
    {
        return $this->countAt2rNDiarreaU5PorCondicion($registros, 'N') + $this->countAt2rNDiarreaU5PorCondicion($registros, 'S');
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

    public function countMorbilidadDiarreaU5($informes): int
    {
        return $this->countMorbilidadDiarreaU5PorCondicion($informes, 'N') + $this->countMorbilidadDiarreaU5PorCondicion($informes, 'S');
    }

    public function countTrans2DiarreaU5($informes): int
    {
        return $this->countMorbilidadDiarreaU5PorCondicion($informes, 'N');
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
                }
            }
        }
        return $count;
    }

    public function countGlobalesDiarreaU5($registros): int
    {
        return $this->countGlobalesDiarreaU5PorCondicion($registros, 'N') + $this->countGlobalesDiarreaU5PorCondicion($registros, 'S');
    }

    public function countMorbilidadDiarreaTotalNuevas($informes): int
    {
        return $informes->filter(function ($inf) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            $cod = strtoupper(trim($inf->cod ?? ''));
            return (str_contains($d, 'DIARREA') || $cod === 'A09.X') && $c === 'N';
        })->count();
    }

    public function countTrans2DiarreaTotalNuevas($informes): int
    {
        return $this->countMorbilidadDiarreaTotalNuevas($informes);
    }

    public function countGlobalesDiarreaTotalNuevas($registros): int
    {
        return $this->countGlobalesGenerico($registros, ['DIARREA'], ['A09.X'], 'N');
    }

    // ──────────────── INMUNOPREVENIBLES ────────────────

    public function countMorbilidadPolioNuevas($informes): int
    {
        return $informes->filter(function ($inf) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            $cod = strtoupper(trim($inf->cod ?? ''));
            return (str_contains($d, 'POLIOMIELITIS') || $cod === 'A80.9') && $c === 'N';
        })->count();
    }

    public function countTrans2PolioNuevas($informes): int { return $this->countMorbilidadPolioNuevas($informes); }
    public function countGlobalesPolioNuevas($registros): int {
        return $this->countGlobalesGenerico($registros, ['POLIOMIELITIS'], ['A80.9'], 'N');
    }

    public function countMorbilidadSarampionNuevas($informes): int
    {
        return $informes->filter(function ($inf) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            $cod = strtoupper(trim($inf->cod ?? ''));
            return (str_contains($d, 'SARAMPION') || $cod === 'B05.9') && $c === 'N';
        })->count();
    }

    public function countTrans2SarampionNuevas($informes): int { return $this->countMorbilidadSarampionNuevas($informes); }
    public function countGlobalesSarampionNuevas($registros): int {
        return $this->countGlobalesGenerico($registros, ['SARAMPION'], ['B05.9'], 'N');
    }

    public function countMorbilidadTosferinaNuevas($informes): int
    {
        return $informes->filter(function ($inf) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            $cod = strtoupper(trim($inf->cod ?? ''));
            return (str_contains($d, 'TOSFERINA') || str_contains($d, 'TOS FERINA') || $cod === 'A37.9') && $c === 'N';
        })->count();
    }

    public function countTrans2TosferinaNuevas($informes): int { return $this->countMorbilidadTosferinaNuevas($informes); }
    public function countGlobalesTosferinaNuevas($registros): int {
        return $this->countGlobalesGenerico($registros, ['TOSFERINA', 'TOS FERINA'], ['A37.9'], 'N');
    }

    public function countMorbilidadDifteriaNuevas($informes): int
    {
        return $informes->filter(function ($inf) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            $cod = strtoupper(trim($inf->cod ?? ''));
            return (str_contains($d, 'DIFTERIA') || $cod === 'A36.9') && $c === 'N';
        })->count();
    }

    public function countTrans2DifteriaNuevas($informes): int { return $this->countMorbilidadDifteriaNuevas($informes); }
    public function countGlobalesDifteriaNuevas($registros): int {
        return $this->countGlobalesGenerico($registros, ['DIFTERIA'], ['A36.9'], 'N');
    }

    public function countMorbilidadTetanosNeoNuevas($informes): int
    {
        return $informes->filter(function ($inf) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            $cod = strtoupper(trim($inf->cod ?? ''));
            return (str_contains($d, 'TETANO NEONATORUM') || str_contains($d, 'TETANOS NEONATORUM') || $cod === 'A33.X') && $c === 'N';
        })->count();
    }

    public function countTrans2TetanosNeoNuevas($informes): int { return $this->countMorbilidadTetanosNeoNuevas($informes); }
    public function countGlobalesTetanosNeoNuevas($registros): int {
        return $this->countGlobalesGenerico($registros, ['TETANO NEONATORUM', 'TETANOS NEONATORUM'], ['A33.X'], 'N');
    }

    public function countMorbilidadParotiditisNuevas($informes): int
    {
        return $informes->filter(function ($inf) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            $cod = strtoupper(trim($inf->cod ?? ''));
            return (str_contains($d, 'PAROTIDITIS') || $cod === 'K11.2' || $cod === 'B26') && $c === 'N';
        })->count();
    }

    public function countTrans2ParotiditisNuevas($informes): int { return $this->countMorbilidadParotiditisNuevas($informes); }
    public function countGlobalesParotiditisNuevas($registros): int {
        return $this->countGlobalesGenerico($registros, ['PAROTIDITIS'], ['K11.2', 'B26'], 'N');
    }

    public function countMorbilidadRubeolaNuevas($informes): int
    {
        return $informes->filter(function ($inf) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            $cod = strtoupper(trim($inf->cod ?? ''));
            return (str_contains($d, 'RUBEOLA') && !str_contains($d, 'CONGENITA') || $cod === 'B06.9') && $c === 'N';
        })->count();
    }

    public function countTrans2RubeolaNuevas($informes): int { return $this->countMorbilidadRubeolaNuevas($informes); }
    public function countGlobalesRubeolaNuevas($registros): int {
        return $this->countGlobalesGenerico($registros, ['RUBEOLA'], ['B06.9'], 'N', ['CONGENITA']);
    }

    public function countMorbilidadRubeolaCongNuevas($informes): int
    {
        return $informes->filter(function ($inf) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            $cod = strtoupper(trim($inf->cod ?? ''));
            return (str_contains($d, 'RUBEOLA CONGENITA') || str_contains($d, 'SINDROME DE RUBEOLA CONGENITA') || $cod === 'P35.0') && $c === 'N';
        })->count();
    }

    public function countTrans2RubeolaCongNuevas($informes): int { return $this->countMorbilidadRubeolaCongNuevas($informes); }
    public function countGlobalesRubeolaCongNuevas($registros): int {
        return $this->countGlobalesGenerico($registros, ['RUBEOLA CONGENITA', 'SINDROME DE RUBEOLA CONGENITA'], ['P35.0'], 'N');
    }

    public function countMorbilidadVaricelaNuevas($informes): int
    {
        return $informes->filter(function ($inf) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            $cod = strtoupper(trim($inf->cod ?? ''));
            return (str_contains($d, 'VARICELA') || $cod === 'B01.9') && $c === 'N';
        })->count();
    }

    public function countTrans2VaricelaNuevas($informes): int { return $this->countMorbilidadVaricelaNuevas($informes); }
    public function countGlobalesVaricelaNuevas($registros): int {
        return $this->countGlobalesGenerico($registros, ['VARICELA'], ['B01.9'], 'N');
    }

    // ──────────────── RESPIRATORIAS ────────────────

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

    public function countAt2rNNeumoniaU5($registros): int
    {
        return $this->countAt2rNNeumoniaU5PorCondicion($registros, 'N') + $this->countAt2rNNeumoniaU5PorCondicion($registros, 'S');
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

    public function countMorbilidadNeumoniaU5($informes): int
    {
        return $this->countMorbilidadNeumoniaU5PorCondicion($informes, 'N') + $this->countMorbilidadNeumoniaU5PorCondicion($informes, 'S');
    }

    public function countTrans2NeumoniaU5($informes): int
    {
        return $this->countMorbilidadNeumoniaU5PorCondicion($informes, 'N');
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
                }
            }
        }
        return $count;
    }

    public function countGlobalesNeumoniaU5($registros): int
    {
        return $this->countGlobalesNeumoniaU5PorCondicion($registros, 'N') + $this->countGlobalesNeumoniaU5PorCondicion($registros, 'S');
    }

    public function countMorbilidadNeumoniaTotalNuevas($informes): int
    {
        return $informes->filter(function ($inf) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            return (str_contains($d, 'NEUMONIA') || str_contains($d, 'BRONCONEUMONIA')) && $c === 'N';
        })->count();
    }

    public function countTrans2NeumoniaTotalNuevas($informes): int { return $this->countMorbilidadNeumoniaTotalNuevas($informes); }
    public function countGlobalesNeumoniaTotalNuevas($registros): int {
        return $this->countGlobalesGenerico($registros, ['NEUMONIA', 'BRONCONEUMONIA'], ['J18.9', 'J18.0'], 'N');
    }

    public function countMorbilidadAsmaBronquitisPorCondicion($informes, string $cond): int
    {
        return $informes->filter(function ($inf) use ($cond) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            return (str_contains($d, 'ASMA') || str_contains($d, 'BRONQUITIS')) && $c === $cond;
        })->count();
    }

    public function countTrans2AsmaBronquitisSuma($informes): int
    {
        // En TRANS-2 Asma y Bronquitis van en filas separadas; aquí sumamos ambas para cuadrar exactamente con Morbilidad
        return $this->countMorbilidadAsmaBronquitisPorCondicion($informes, 'N');
    }

    public function countGlobalesAsmaBronquitisPorCondicion($registros, string $cond): int
    {
        return $this->countGlobalesGenerico($registros, ['ASMA', 'BRONQUITIS'], ['J45', 'J20', 'J21'], $cond);
    }

    public function countMorbilidadFaringoamigdalitisSuma($informes): int
    {
        return $informes->filter(function ($inf) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            return (str_contains($d, 'FARINGOAMIGDALITIS') || str_contains($d, 'FARINGITIS') || str_contains($d, 'AMIGDALITIS')) && $c === 'N';
        })->count();
    }

    public function countTrans2Faringoamigdalitis($informes): int
    {
        return $this->countMorbilidadFaringoamigdalitisSuma($informes);
    }

    public function countGlobalesFaringoamigdalitisSuma($registros): int
    {
        return $this->countGlobalesGenerico($registros, ['FARINGOAMIGDALITIS', 'FARINGITIS', 'AMIGDALITIS'], ['J02.0', 'J03.0'], 'N');
    }

    public function countMorbilidadIrasAltasPorCondicion($informes, string $cond): int
    {
        return $informes->filter(function ($inf) use ($cond) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            return (str_contains($d, 'RESFRIO') || str_contains($d, 'RINITIS') || str_contains($d, 'LARINGITIS') || str_contains($d, 'CRUP') || str_contains($d, 'OTRAS IRAS') || str_contains($d, 'SBO'))
                && !str_contains($d, 'ESTREPTO')
                && $c === $cond;
        })->count();
    }

    public function countTrans2IrasAltas($informes): int
    {
        return $this->countMorbilidadIrasAltasPorCondicion($informes, 'N');
    }

    public function countGlobalesIrasAltasPorCondicion($registros, string $cond): int
    {
        return $this->countGlobalesGenerico($registros, ['RESFRIO', 'RINITIS', 'LARINGITIS', 'CRUP', 'OTRAS IRAS', 'SBO'], [], $cond, ['ESTREPTO']);
    }

    // ──────────────── VECTORIALES Y TRANSMISIBLES ────────────────

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

    public function countTrans2Tuberculosis($informes): int { return $this->countMorbilidadTuberculosis($informes); }
    public function countGlobalesTuberculosis($registros): int {
        return $this->countGlobalesGenerico($registros, ['TUBERCULOSIS', 'TB', 'BK'], ['A15', 'J16.4']);
    }

    public function countMorbilidadMalariaNuevas($informes): int
    {
        return $informes->filter(function ($inf) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            $cod = strtoupper(trim($inf->cod ?? ''));
            return (str_contains($d, 'MALARIA') || $cod === 'B54.X') && $c === 'N';
        })->count();
    }

    public function countTrans2MalariaNuevas($informes): int { return $this->countMorbilidadMalariaNuevas($informes); }
    public function countGlobalesMalariaNuevas($registros): int {
        return $this->countGlobalesGenerico($registros, ['MALARIA'], ['B54.X'], 'N');
    }

    public function countMorbilidadDengueSinSignos($informes): int
    {
        return $informes->filter(function ($inf) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            $cod = strtoupper(trim($inf->cod ?? ''));
            return (str_contains($d, 'DENGUE') && (str_contains($d, 'SIN') || str_contains($d, 'DSSA') || str_contains($d, 'S.S.A')) || $cod === 'A90.X')
                && !str_contains($d, 'CON') && !str_contains($d, 'GRAVE') && $c === 'N';
        })->count();
    }

    public function countTrans2DengueSinSignos($informes): int { return $this->countMorbilidadDengueSinSignos($informes); }
    public function countGlobalesDengueSinSignos($registros): int {
        return $this->countGlobalesGenerico($registros, ['DENGUE SIN', 'DSSA', 'DENGUE S.S.A'], ['A90.X'], 'N', ['CON', 'GRAVE']);
    }

    public function countMorbilidadDengueConSignos($informes): int
    {
        return $informes->filter(function ($inf) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            return str_contains($d, 'DENGUE') && (str_contains($d, 'CON') || str_contains($d, 'DCSA') || str_contains($d, 'C.S.A')) && $c === 'N';
        })->count();
    }

    public function countTrans2DengueConSignos($informes): int { return $this->countMorbilidadDengueConSignos($informes); }
    public function countGlobalesDengueConSignos($registros): int {
        return $this->countGlobalesGenerico($registros, ['DENGUE CON', 'DCSA', 'DENGUE C.S.A'], ['A97.1'], 'N');
    }

    public function countMorbilidadDengueGrave($informes): int
    {
        return $informes->filter(function ($inf) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            $cod = strtoupper(trim($inf->cod ?? ''));
            return (str_contains($d, 'DENGUE') && (str_contains($d, 'GRAVE') || str_contains($d, 'HEMORRAGICO')) || $cod === 'A91.X') && $c === 'N';
        })->count();
    }

    public function countTrans2DengueGrave($informes): int { return $this->countMorbilidadDengueGrave($informes); }
    public function countGlobalesDengueGrave($registros): int {
        return $this->countGlobalesGenerico($registros, ['DENGUE GRAVE', 'DENGUE HEMORRAGICO'], ['A91.X'], 'N');
    }

    public function countMorbilidadChikungunyaNuevas($informes): int
    {
        return $informes->filter(function ($inf) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            $cod = strtoupper(trim($inf->cod ?? ''));
            return (str_contains($d, 'CHIKUNGUNYA') || $cod === 'A92.0') && $c === 'N';
        })->count();
    }

    public function countTrans2ChikungunyaNuevas($informes): int { return $this->countMorbilidadChikungunyaNuevas($informes); }
    public function countGlobalesChikungunyaNuevas($registros): int {
        return $this->countGlobalesGenerico($registros, ['CHIKUNGUNYA'], ['A92.0'], 'N');
    }

    // ──────────────── INFECCIONES DE TRANSMISIÓN SEXUAL (ITS) ────────────────

    public function countMorbilidadSifilisNuevas($informes): int
    {
        return $informes->filter(function ($inf) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            $cod = strtoupper(trim($inf->cod ?? ''));
            return (str_contains($d, 'SIFILIS') || $cod === 'A53.9' || $cod === '200') && $c === 'N';
        })->count();
    }

    public function countTrans2SifilisNuevas($informes): int { return $this->countMorbilidadSifilisNuevas($informes); }
    public function countItsSifilisNuevas($informes): int { return $this->countMorbilidadSifilisNuevas($informes); }
    public function countGlobalesSifilisNuevas($registros): int {
        return $this->countGlobalesGenerico($registros, ['SIFILIS'], ['A53.9', '200'], 'N');
    }

    public function countMorbilidadGonorreaNuevas($informes): int
    {
        return $informes->filter(function ($inf) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            $cod = strtoupper(trim($inf->cod ?? ''));
            return (str_contains($d, 'GONORREA') || $cod === 'A54.9' || $cod === '190') && $c === 'N';
        })->count();
    }

    public function countTrans2GonorreaNuevas($informes): int { return $this->countMorbilidadGonorreaNuevas($informes); }
    public function countItsGonorreaNuevas($informes): int { return $this->countMorbilidadGonorreaNuevas($informes); }
    public function countGlobalesGonorreaNuevas($registros): int {
        return $this->countGlobalesGenerico($registros, ['GONORREA'], ['A54.9', '190'], 'N');
    }

    public function countMorbilidadVihSidaNuevas($informes): int
    {
        return $informes->filter(function ($inf) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            $cod = strtoupper(trim($inf->cod ?? ''));
            return (str_contains($d, 'VIH') || str_contains($d, 'SIDA') || $cod === 'B24.9') && $c === 'N';
        })->count();
    }

    public function countTrans2VihSidaNuevas($informes): int { return $this->countMorbilidadVihSidaNuevas($informes); }
    public function countItsVihSidaNuevas($informes): int { return $this->countMorbilidadVihSidaNuevas($informes); }
    public function countGlobalesVihSidaNuevas($registros): int {
        return $this->countGlobalesGenerico($registros, ['VIH', 'SIDA'], ['B24.9'], 'N');
    }

    public function countTrans2CondilomaNuevas($informes): int
    {
        return $informes->filter(function ($inf) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            return str_contains($d, 'CONDILOMA') && $c === 'N';
        })->count();
    }

    public function countItsCondilomaNuevas($informes): int { return $this->countTrans2CondilomaNuevas($informes); }
    public function countGlobalesCondilomaNuevas($registros): int {
        return $this->countGlobalesGenerico($registros, ['CONDILOMA'], ['A63.0'], 'N');
    }

    public function countMorbilidadHerpesGenitalNuevas($informes): int
    {
        return $informes->filter(function ($inf) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            $cod = strtoupper(trim($inf->cod ?? ''));
            return (str_contains($d, 'HERPES GENITAL') || $cod === 'A60.0' || $cod === '206') && $c === 'N';
        })->count();
    }

    public function countTrans2HerpesGenitalNuevas($informes): int { return $this->countMorbilidadHerpesGenitalNuevas($informes); }
    public function countItsHerpesGenitalNuevas($informes): int { return $this->countMorbilidadHerpesGenitalNuevas($informes); }
    public function countGlobalesHerpesGenitalNuevas($registros): int {
        return $this->countGlobalesGenerico($registros, ['HERPES GENITAL'], ['A60.0', '206'], 'N');
    }

    public function countMorbilidadHepatitisBNuevas($informes): int
    {
        return $informes->filter(function ($inf) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            $cod = strtoupper(trim($inf->cod ?? ''));
            return (str_contains($d, 'HEPATITIS B') || $cod === 'B16.9') && $c === 'N';
        })->count();
    }

    public function countTrans2HepatitisBNuevas($informes): int { return $this->countMorbilidadHepatitisBNuevas($informes); }
    public function countItsHepatitisBNuevas($informes): int { return $this->countMorbilidadHepatitisBNuevas($informes); }
    public function countGlobalesHepatitisBNuevas($registros): int {
        return $this->countGlobalesGenerico($registros, ['HEPATITIS B'], ['B16.9'], 'N');
    }

    // ──────────────── CRÓNICAS ────────────────

    public function countAt2rNHtaPorCondicion($registros, string $cond): int
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

    public function countAt2rNHtaNuevas($registros): int { return $this->countAt2rNHtaPorCondicion($registros, 'N'); }
    public function countAt2rNHtaSubs($registros): int { return $this->countAt2rNHtaPorCondicion($registros, 'S'); }
    public function countAt2rNHtaTotal($registros): int { return $this->countAt2rNHtaNuevas($registros) + $this->countAt2rNHtaSubs($registros); }

    public function countMorbilidadHtaPorCondicion($informes, string $cond): int
    {
        return $informes->filter(function ($inf) use ($cond) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            $cod = strtoupper(trim($inf->cod ?? ''));
            return (str_contains($d, 'HIPERTENSION') || $d === 'HTA' || $cod === 'I10.X') && $c === $cond;
        })->count();
    }

    public function countMorbilidadHtaNuevas($informes): int { return $this->countMorbilidadHtaPorCondicion($informes, 'N'); }
    public function countMorbilidadHtaSubs($informes): int { return $this->countMorbilidadHtaPorCondicion($informes, 'S'); }
    public function countMorbilidadHtaTotal($informes): int { return $this->countMorbilidadHtaNuevas($informes) + $this->countMorbilidadHtaSubs($informes); }

    public function countTrans2HtaNuevas($informes): int { return $this->countMorbilidadHtaNuevas($informes); }

    public function countGlobalesHtaPorCondicion($registros, string $cond): int
    {
        return $this->countGlobalesGenerico($registros, ['HIPERTENSION', 'HTA'], ['I10.X'], $cond);
    }

    public function countGlobalesHtaNuevas($registros): int { return $this->countGlobalesHtaPorCondicion($registros, 'N'); }
    public function countGlobalesHtaSubs($registros): int { return $this->countGlobalesHtaPorCondicion($registros, 'S'); }
    public function countGlobalesHtaTotal($registros): int { return $this->countGlobalesHtaNuevas($registros) + $this->countGlobalesHtaSubs($registros); }

    public function countAt2rNDiabetesPorCondicion($registros, string $cond): int
    {
        $count = 0;
        foreach ($registros as $r) {
            if (!$this->resolveColumnaProfesion($r->prof, $r->medico ?? '', false)) continue;
            for ($i = 1; $i <= 7; $i++) {
                $d = $this->cleanDiag($r->{"diagnostico_$i"} ?? '');
                $c = strtoupper(trim($r->{"cond_$i"} ?? ($r->cond ?? '')));
                $cod = strtoupper(trim($r->{"cod_$i"} ?? ''));
                if ((str_contains($d, 'DIABETES') || $d === 'DM2' || $cod === 'E14.9') && $c === $cond) {
                    $count++;
                    break;
                }
            }
        }
        return $count;
    }

    public function countAt2rNDiabetesNuevas($registros): int { return $this->countAt2rNDiabetesPorCondicion($registros, 'N'); }
    public function countAt2rNDiabetesSubs($registros): int { return $this->countAt2rNDiabetesPorCondicion($registros, 'S'); }
    public function countAt2rNDiabetesTotal($registros): int { return $this->countAt2rNDiabetesNuevas($registros) + $this->countAt2rNDiabetesSubs($registros); }

    public function countMorbilidadDiabetesPorCondicion($informes, string $cond): int
    {
        return $informes->filter(function ($inf) use ($cond) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            $cod = strtoupper(trim($inf->cod ?? ''));
            return (str_contains($d, 'DIABETES') || $d === 'DM2' || $cod === 'E14.9') && $c === $cond;
        })->count();
    }

    public function countMorbilidadDiabetesNuevas($informes): int { return $this->countMorbilidadDiabetesPorCondicion($informes, 'N'); }
    public function countMorbilidadDiabetesSubs($informes): int { return $this->countMorbilidadDiabetesPorCondicion($informes, 'S'); }
    public function countMorbilidadDiabetesTotal($informes): int { return $this->countMorbilidadDiabetesNuevas($informes) + $this->countMorbilidadDiabetesSubs($informes); }

    public function countTrans2DiabetesNuevas($informes): int { return $this->countMorbilidadDiabetesNuevas($informes); }

    public function countGlobalesDiabetesPorCondicion($registros, string $cond): int
    {
        return $this->countGlobalesGenerico($registros, ['DIABETES', 'DM2'], ['E14.9'], $cond);
    }

    public function countGlobalesDiabetesNuevas($registros): int { return $this->countGlobalesDiabetesPorCondicion($registros, 'N'); }
    public function countGlobalesDiabetesSubs($registros): int { return $this->countGlobalesDiabetesPorCondicion($registros, 'S'); }
    public function countGlobalesDiabetesTotal($registros): int { return $this->countGlobalesDiabetesNuevas($registros) + $this->countGlobalesDiabetesSubs($registros); }

    public function countAt2rNErcPorCondicion($registros, string $cond): int
    {
        $count = 0;
        foreach ($registros as $r) {
            if (!$this->resolveColumnaProfesion($r->prof, $r->medico ?? '', false)) continue;
            for ($i = 1; $i <= 7; $i++) {
                $d = $this->cleanDiag($r->{"diagnostico_$i"} ?? '');
                $c = strtoupper(trim($r->{"cond_$i"} ?? ($r->cond ?? '')));
                $cod = strtoupper(trim($r->{"cod_$i"} ?? ''));
                if ((str_contains($d, 'RENAL CRONICA') || str_contains($d, 'INSUFICIENCIA RENAL') || preg_match('/\bERC\b/', $d) || $cod === 'N18.9') && $c === $cond) {
                    $count++;
                    break;
                }
            }
        }
        return $count;
    }

    public function countAt2rNErcNuevas($registros): int { return $this->countAt2rNErcPorCondicion($registros, 'N'); }
    public function countAt2rNErcSubs($registros): int { return $this->countAt2rNErcPorCondicion($registros, 'S'); }
    public function countAt2rNErcTotal($registros): int { return $this->countAt2rNErcNuevas($registros) + $this->countAt2rNErcSubs($registros); }

    public function countMorbilidadErcPorCondicion($informes, string $cond): int
    {
        return $informes->filter(function ($inf) use ($cond) {
            $d = $this->cleanDiag($inf->diagnostico ?? '');
            $c = strtoupper(trim($inf->cond_diagnostico ?? ''));
            $cod = strtoupper(trim($inf->cod ?? ''));
            return (str_contains($d, 'RENAL CRONICA') || str_contains($d, 'INSUFICIENCIA RENAL') || preg_match('/\bERC\b/', $d) || $cod === 'N18.9') && $c === $cond;
        })->count();
    }

    public function countMorbilidadErcNuevas($informes): int { return $this->countMorbilidadErcPorCondicion($informes, 'N'); }
    public function countMorbilidadErcSubs($informes): int { return $this->countMorbilidadErcPorCondicion($informes, 'S'); }
    public function countMorbilidadErcTotal($informes): int { return $this->countMorbilidadErcNuevas($informes) + $this->countMorbilidadErcSubs($informes); }

    public function countTrans2ErcNuevas($informes): int { return $this->countMorbilidadErcNuevas($informes); }

    public function countGlobalesErcPorCondicion($registros, string $cond): int
    {
        return $this->countGlobalesGenerico($registros, ['RENAL CRONICA', 'INSUFICIENCIA RENAL', 'ERC'], ['N18.9'], $cond);
    }

    public function countGlobalesErcNuevas($registros): int { return $this->countGlobalesErcPorCondicion($registros, 'N'); }
    public function countGlobalesErcSubs($registros): int { return $this->countGlobalesErcPorCondicion($registros, 'S'); }
    public function countGlobalesErcTotal($registros): int { return $this->countGlobalesErcNuevas($registros) + $this->countGlobalesErcSubs($registros); }

    // ──────────────── ANEMIAS < 5 AÑOS ────────────────

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

    public function countTrans2AnemiaU5($informes): int
    {
        return $this->countMorbilidadAnemiaU5PorCondicion($informes, 'N');
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
                }
            }
        }
        return $count;
    }

    // ──────────────── TOTALES GLOBALES ────────────────

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
        return $this->countMorbilidadTotalPorCondicion($informes, 'N');
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
                }
            }
        }
        return $count;
    }

    /**
     * Helper genérico para conteo directo sobre Registros Globales
     */
    private function countGlobalesGenerico($registros, array $terms, array $codes = [], ?string $cond = null, array $excludeTerms = []): int
    {
        $count = 0;
        foreach ($registros as $r) {
            for ($i = 1; $i <= 7; $i++) {
                $d = $this->cleanDiag($r->{"diagnostico_$i"} ?? '');
                if ($d === '') continue;

                $c = strtoupper(trim($r->{"cond_$i"} ?? ($r->cond ?? '')));
                if ($cond !== null && $c !== $cond) continue;

                $cod = strtoupper(trim($r->{"cod_$i"} ?? ''));

                $excluded = false;
                foreach ($excludeTerms as $ex) {
                    if (str_contains($d, $this->cleanDiag($ex))) {
                        $excluded = true;
                        break;
                    }
                }
                if ($excluded) continue;

                $matched = false;
                foreach ($terms as $term) {
                    $termClean = $this->cleanDiag($term);
                    if ($termClean === 'ERC' || $termClean === 'DM2' || $termClean === 'HTA') {
                        if (preg_match('/\b' . preg_quote($termClean, '/') . '\b/', $d)) {
                            $matched = true;
                            break;
                        }
                    } elseif (str_contains($d, $termClean)) {
                        $matched = true;
                        break;
                    }
                }
                if (!$matched && !empty($codes)) {
                    foreach ($codes as $code) {
                        if ($cod === strtoupper(trim($code))) {
                            $matched = true;
                            break;
                        }
                    }
                }

                if ($matched) {
                    $count++;
                }
            }
        }
        return $count;
    }
}
