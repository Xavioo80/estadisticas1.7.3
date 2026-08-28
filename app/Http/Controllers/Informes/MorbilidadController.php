<?php

namespace App\Http\Controllers\Informes;

use App\Http\Controllers\Controller;
use App\Models\RegistroGlobal;
use App\Traits\InformesHelperTrait;
use Illuminate\Http\Request;

class MorbilidadController extends Controller
{
    use InformesHelperTrait;

    public function __construct(private \App\Services\RegistroGlobalService $service)
    {
    }

    public function index(Request $request)
    {
        if (!$request->ajax() && $request->getQueryString()) {
            return redirect()->route('informes.morbilidad');
        }

        $helperData = $this->getAnosMesesDisponiblesInformes();
        $anos = $helperData['anos'];
        $meses = $helperData['meses'];

        $ano = $request->input('ano', $helperData['anoDefault']);
        $mes = $request->input('mes', '');
        if (empty($mes))
            $mes = $this->resolverMesPorDefecto($ano);

        $jornada = $request->input('jornada', 'TODAS') ?: 'TODAS';
        $jornadas = RegistroGlobal::distinct()->whereNotNull('jornada')->where('jornada', '!=', '')->pluck('jornada');

        // =====================================================================
        // DEFINICIÓN DE FILAS DEL INFORME DE MORBILIDAD
        // Cada entrada puede tener 'diag' como string o array de variantes.
        // La función normalizeForMatch() garantiza comparación sin tildes/mayúsculas.
        // =====================================================================
        $rowsDef = [
            ['label' => 'FIEBRE TIFOIDEA Y PARATIFOIDEA', 'code' => 'A.01.0', 'diag' => 'FIEBRE TIFOIDEA Y PARATIFOIDEA'],
            ['label' => 'FIEBRE DE ORIGEN DESCONOCIDO', 'diag' => 'FIEBRE DE ORIGEN DESCONOCIDO'],
            ['label' => 'DISENTERIA', 'diag' => ['DISENTERIA', 'DISENTERIA BACILAR', 'DISENTERIA AMEBIANA']],
            ['label' => 'DIARREAS', 'code' => 'A09.X', 'diag' => ['DIARREAS', 'DIARREAS CON DESHIDRATACION', 'DIARREAS SIN DESHIDRATACION']],
            ['label' => 'TUBERCULOSIS', 'code' => 'J16.4', 'diag' => 'TUBERCULOSIS'],
            ['label' => 'HEPATITIS INFECCIOSA', 'code' => 'B15.9', 'diag' => 'HEPATITIS INFECCIOSA'],
            ['label' => 'DIFTERIA', 'code' => 'A36.9', 'diag' => 'DIFTERIA'],
            ['label' => 'TOSFERINA', 'code' => 'A37.9', 'diag' => 'TOSFERINA'],
            ['label' => 'INFECCION MENINGOCOCICA', 'diag' => 'INFECCION MENINGOCOCICA'],
            ['label' => 'TETANO NEONATORUM', 'code' => 'A33.X', 'diag' => 'TETANO NEONATORUM'],
            ['label' => 'POLIOMIELITIS AGUDA', 'code' => 'A80.9', 'diag' => 'POLIOMIELITIS AGUDA'],
            ['label' => 'SARAMPION', 'code' => 'B05.9', 'diag' => 'SARAMPION'],
            ['label' => 'RUBEOLA', 'code' => 'B06.9', 'diag' => 'RUBEOLA'],
            ['label' => 'SOSP. DENGUE SIN SIGNOS DE ALARMA', 'code' => 'A90.X', 'diag' => ['SOSP. DENGUE SIN SIGNOS DE ALARMA', 'DENGUE SIN SIGNOS DE ALARMA', 'DSSA', 'D.S.S.A', 'SOSPECHA DENGUE SIN SIGNOS DE ALARMA', 'DENGUE S.S.A']],
            ['label' => 'SOSP. DENGUE CON SIGNOS DE ALARMA', 'diag' => ['SOSP. DENGUE CON SIGNOS DE ALARMA', 'DENGUE CON SIGNOS DE ALARMA', 'DCSA', 'D.C.S.A', 'SOSPECHA DENGUE CON SIGNOS DE ALARMA', 'DENGUE C.S.A']],
            ['label' => 'DENGUE GRAVE', 'diag' => 'DENGUE GRAVE'],
            ['label' => 'ZIKA', 'diag' => 'ZIKA'],
            ['label' => 'CHIKUNGUNYA', 'diag' => 'CHIKUNGUNYA'],
            ['label' => 'LUMBALGIA', 'diag' => 'LUMBALGIA'],
            ['label' => 'HEMORROIDES', 'diag' => 'HEMORROIDES'],
            ['label' => 'MALARIA', 'code' => 'B54.X', 'diag' => 'MALARIA'],
            ['label' => 'LEISHMANIASIS CUTANEA', 'code' => 'B55.1', 'diag' => 'LEISHMANIASIS CUTANEA'],
            ['label' => 'SIFILIS', 'code' => 'A53.9', 'diag' => 'SIFILIS'],
            ['label' => 'GONORREA', 'code' => 'A54.9', 'diag' => 'GONORREA'],
            ['label' => 'MICOSIS', 'diag' => 'MICOSIS'],
            ['label' => 'VIH', 'diag' => ['VIH', 'VIH-SIDA', 'VIH POSITIVO']],
            ['label' => 'PARASITOSIS INTESTINAL', 'diag' => 'PARASITOSIS INTESTINAL'],
            ['label' => 'TUMORES MALIGNOS', 'diag' => 'TUMORES MALIGNOS'],
            ['label' => 'TUMORES BENIGNOS', 'diag' => 'TUMORES BENIGNOS'],
            ['label' => 'CANCER IN SITU', 'diag' => 'CANCER IN SITU'],
            ['label' => 'BOCIO', 'diag' => 'BOCIO'],
            ['label' => 'DIABETES', 'code' => 'E14.9', 'diag' => 'DIABETES'],
            ['label' => 'ANEMIAS', 'diag' => 'ANEMIAS'],
            ['label' => 'VIOLENCIA DOMESTICA EN TODAS SUS FORMAS', 'code' => 'AA', 'diag' => 'VIOLENCIA DOMESTICA EN TODAS SUS FORMAS'],
            ['label' => 'ANSIEDAD Y ESTRÉS', 'code' => 'F40-F49', 'diag' => 'TRASTORNOS NEURÓTICOS, TRASTORNOS RELACIONADOS CON EL ESTRÉS Y TRASTORNOS SOMATOMORFOS'],
            ['label' => 'TRASTORNOS MENTALES Y DEL COMPORTAMIENTO DEBIDO AL ALCOHOL', 'code' => 'F10', 'diag' => ['TRASTORNOS MENTALES Y DEL COMPORTAMIENTO DEBIDO AL ALCOHOL', 'SINDROME DEPENDENCIA DEL ALCOHOL', 'PROBLEMAS RELACIONADOS CON EL USO DEL ALCOHOL']],
            ['label' => 'TRASTORNO DEBIDO A CONSUMO DE OTRAS DROGAS', 'code' => 'F11-F19', 'diag' => ['TRASTORNO DEBIDO A CONSUMO DE OTRAS DROGAS', 'TRASTORNOS MENTALES Y DEL COMPORTAMIENTO DEBIDOS AL CONSUMO DE SUSTANCIAS PSICOTROPAS', 'CONSUMO DE SUSTANCIAS', 'DROGAS', 'ALCOHOLISMO', 'TABAQUISMO', 'PSICOACTIVAS']],
            ['label' => '', 'space' => true],
            ['label' => 'MALTRATO INFANTIL', 'diag' => ['MALTRATO INFANTIL','NEGLIGENCIA O ABANDONO (NIÑOS Y NIÑAS)','ABUSO SEXUAL','ABUSO PSICOLÓGICO',]],
            ['label' => 'TRAST. DEL HUMOR EPISODIO DEPRESIVO', 'diag' => ['TRASTORNOS DEL HUMOR EPISODIO DEPRESIVO', 'DEPRESION', 'EPISODIO DEPRESIVO']],
            ['label' => 'SINDROME HIPERCINETICO DE LA NIÑEZ (HIPERACTIVIDAD, TDAH)', 'diag' => ['SINDROME HIPERCINETICO DE LA NIÑEZ', 'TRASTORNO EMOCIONALES Y DEL COMPORTAMIENTO QUE APARECEN HABITUALMENTE EN LA NIÑEZ Y LA ADOLESCENCIA.', 'TDAH']],
            ['label' => 'EPILEPSIA', 'diag' => 'EPILEPSIA'],
            ['label' => 'FARINGOAMIGDALITIS VIRAL', 'diag' => 'FARINGOAMIGDALITIS VIRAL'],
            ['label' => 'RESFRIO COMUN', 'diag' => ['RESFRIO COMUN', 'RESFRIADO COMUN']],
            ['label' => 'FARINGITIS', 'diag' => 'FARINGITIS'],
            ['label' => 'OTRAS IRAS (LARINGITIS, CRUP )', 'diag' => ['OTRAS IRAS', 'LARINGITIS', 'CRUP', 'SINOSITIS', 'SBO', 'SINDROME BRONQUIAL OBSTRUCTIVO', 'RINITIS ALERGICA', 'CRUP (Laringotraqueobronquitis Aguda)', 'LARINGITIS AGUDA', 'LARINGOTRAQUEITIS AGUDA']],
            ['label' => 'FARING.AMIG. ESTREPTOCOCICAS', 'diag' => 'FARINGOAMIGDALITIS ESTREPTOCOCICAS'],
            ['label' => 'NEUMONIAS/BRONCONEUMONIAS', 'diag' => ['NEUMONIA', 'NEUMONIAS', 'BRONCONEUMONIA', 'BRONCONEUMONIAS', 'NEUMONIAS/BRONCONEUMONIAS', 'NEUMONIAS/BORNCONEUMONIAS']],
            ['label' => 'COVID-19', 'diag' => ['ATENCION CLINICA POR COVID-19', 'COVID-19']],
            ['label' => 'ASMA BRONQUIAL/BRONQUITIS', 'diag' => ['ASMA BRONQUIAL', 'BRONQUITIS', 'ASMA BRONQUIAL Y BRONQUITIS', 'ASMA BRONQUIAL/BRONQUITIS']],
            ['label' => 'INFECCION TRACTO URINARIO (ITU)', 'diag' => ['ITU', 'ENF. APARATO GENITOURINARIO', 'INFECCION EN TRACTO URINARIO', 'INFECCION TRACTO URINARIO']],
            ['label' => 'ENF. DE VIAS URINARIAS (PIELONEFRITIS,CISTITIS)', 'diag' => ['ENF. DE VIAS URINARIAS', 'PIELONEFRITIS', 'CISTITIS', 'URETRITIS', 'PROSTATITIS', 'ENF. DE VIAS URINARIAS (PIELONEFRITIS,CISTITIS)']],
            ['label' => 'FIEBRE REUMATICA', 'diag' => 'FIEBRE REUMATICA'],
            ['label' => 'HIPERTENSION ARTERIAL', 'diag' => 'HIPERTENSION ARTERIAL'],
            ['label' => 'CARDIOPATIAS', 'diag' => ['CARDIOPATIAS', 'CARDIOPATIA']],
            ['label' => 'ENFERMEDAD CEREBRO VASCULAR', 'diag' => 'ENFERMEDAD CEREBRO VASCULAR'],
            ['label' => 'HERNIAS', 'diag' => 'HERNIAS'],
            ['label' => 'DISLIPIDEMIA:[elevación anormal de concentrac. de grasas en la sangre (colest., triglicér., colesterol HDL y LDL).', 'diag' => 'DISLIPIDEMIA'],
            ['label' => 'CERVICITIS', 'diag' => 'CERVICITIS'],
            ['label' => 'PROLAPSO GENITAL', 'diag' => 'PROLAPSO GENITAL'],
            ['label' => 'COMPLICACIONES DEL EMBARAZO', 'diag' => 'COMPLICACIONES DEL EMBARAZO'],
            ['label' => 'DERMATITIS', 'diag' => 'DERMATITIS'],
            ['label' => 'FRACTURAS Y CONTUSIONES', 'diag' => 'FRACTURAS Y CONTUSIONES'],
            ['label' => 'ALERGIAS', 'diag' => 'ALERGIAS'],
            ['label' => 'MORDEDURAS ANIMALES TRANSM. DE RABIA', 'diag' => 'MORDEDURAS ANIMALES TRASM DE RABIA'],
            ['label' => 'DESNUTRICION', 'diag' => ['DESNUTRICION', 'DESNUTRICION SEVERA']],
            ['label' => 'OBESIDAD', 'diag' => ['OBESIDAD', 'SOBREPESO', 'OBESIDAD Y SOBREPESO', 'OBESIDAD O SOBREPESO ']],
            ['label' => 'CONJUNTIVITIS', 'diag' => 'CONJUNTIVITIS'],
            ['label' => 'OTITIS MEDIA', 'diag' => 'OTITIS MEDIA'],
            ['label' => 'VARICELA', 'diag' => 'VARICELA'],
            ['label' => 'GASTRITIS', 'diag' => 'GASTRITIS'],
            ['label' => 'HERIDAS Y TRAUMATISMOS', 'diag' => ['HERIDAS Y TRAUMATISMOS', 'HERIDAS', 'TRAUMATISMO']],
            ['label' => 'CHAGAS', 'diag' => 'CHAGAS'],
            ['label' => 'OTRAS PATOLOGIAS', 'diag' => 'OTRAS PATOLOGIAS'],
            ['label' => 'ATENCIÓN PRENATAL ANTES DE LAS 12 SEMANAS DE GESTIÓN', 'diag' => 'ATENCION PRENATAL ANTES DE LAS 12 SG'],
            ['label' => 'ATENCIÓN PRENATAL DESPUÉS DE LAS 12 SEMANAS DE GESTACIÓN', 'diag' => 'ATENCION PRENATAL DESPUES DE LAS 12 SG'],
        ];

        // Consulta base directamente desde RegistroGlobal
        $query = RegistroGlobal::query()->where('ano', $ano)->where('mes', $mes);
        if ($jornada != 'TODAS')
            $query->where('jornada', $jornada);

        $rgRecords = $query->get([
            'edad', 'tipo', 'sexo', 'cond',
            'diagnostico_1', 'cod_1', 'cond_1',
            'diagnostico_2', 'cod_2', 'cond_2',
            'diagnostico_3', 'cod_3', 'cond_3',
            'diagnostico_4', 'cod_4', 'cond_4',
            'diagnostico_5', 'cod_5', 'cond_5',
            'diagnostico_6', 'cod_6', 'cond_6',
            'diagnostico_7', 'cod_7', 'cond_7',
        ]);

        $unrolled = [];
        foreach ($rgRecords as $rg) {
            for ($i = 1; $i <= 7; $i++) {
                $diag = trim($rg->{"diagnostico_$i"} ?? '');
                if ($diag === '') continue;

                $cond = strtoupper(trim($rg->{"cond_$i"} ?? ($rg->cond ?? '')));
                if ($cond !== 'N' && $cond !== 'S') $cond = 'N';

                $unrolled[] = (object)[
                    'cod' => trim($rg->{"cod_$i"} ?? ''),
                    'diagnostico' => $diag,
                    'cond_diagnostico' => $cond,
                    'sexo' => $rg->sexo,
                    'edad' => $rg->edad,
                    'tipo' => $rg->tipo,
                ];
            }
        }

        $rawData = collect($unrolled);

        // Función de columna (32 columnas: 7 rangos × 4 sub + 4 totales)
        $getCol = function ($r) {
            $cond = strtoupper(trim($r->cond_diagnostico));
            $isN = ($cond == 'N');
            $isS = ($cond == 'S');
            if (!$isN && !$isS)
                return null;

            $t = strtoupper(trim($r->tipo));
            $e = (int) $r->edad;
            $ageIdx = 0;

            if ($t == 'D' || $t == 'M' || ($t == 'A' && $e == 0)) {
                $ageIdx = 1;
            } elseif ($t == 'A') {
                if ($e >= 1 && $e <= 4)
                    $ageIdx = 2;
                elseif ($e >= 5 && $e <= 14)
                    $ageIdx = 3;
                elseif ($e >= 15 && $e <= 19)
                    $ageIdx = 4;
                elseif ($e >= 20 && $e <= 49)
                    $ageIdx = 5;
                elseif ($e >= 50 && $e <= 59)
                    $ageIdx = 6;
                elseif ($e >= 60)
                    $ageIdx = 7;
            }
            if ($ageIdx == 0)
                return null;

            $sexo = strtoupper(trim($r->sexo)) == 'H' ? 'H' : 'M';
            $subCol = 0;
            if ($sexo == 'H')
                $subCol = $isN ? 1 : 2;
            else
                $subCol = $isN ? 3 : 4;

            return (($ageIdx - 1) * 4) + $subCol;
        };

        $resultsByCode = [];
        $resultsByDiag = [];
        $totalGeneral = array_fill(1, 32, 0);

        foreach ($rawData as $r) {
            $cod = trim($r->cod);
            $diagNorm = $this->normalizeForMatch($r->diagnostico ?? '');
            $col = $getCol($r);
            if ($col) {
                if (!isset($resultsByCode[$cod]))
                    $resultsByCode[$cod] = array_fill(1, 32, 0);
                $resultsByCode[$cod][$col]++;
                $targetTotalCol = 28 + (($col - 1) % 4 + 1);
                $resultsByCode[$cod][$targetTotalCol]++;

                if (!empty($diagNorm)) {
                    if (!isset($resultsByDiag[$diagNorm]))
                        $resultsByDiag[$diagNorm] = array_fill(1, 32, 0);
                    $resultsByDiag[$diagNorm][$col]++;
                    $resultsByDiag[$diagNorm][$targetTotalCol]++;
                }

                $totalGeneral[$col]++;
                $totalGeneral[$targetTotalCol]++;
            }
        }

        $finalData = [];
        foreach ($rowsDef as $row) {
            if (isset($row['space'])) {
                $finalData[] = ['label' => $row['label'], 'is_extra' => true, 'cols' => array_fill(1, 32, 0), 'total' => 0];
                continue;
            }

            $rowCounts = array_fill(1, 32, 0);

            if (isset($row['diag'])) {
                $diags = is_array($row['diag']) ? $row['diag'] : [$row['diag']];
                foreach ($diags as $d) {
                    $dNorm = $this->normalizeForMatch($d);
                    if (isset($resultsByDiag[$dNorm])) {
                        foreach ($resultsByDiag[$dNorm] as $col => $val) {
                            $rowCounts[$col] += $val;
                        }
                    }
                }
            }

            if (isset($row['code'])) {
                $codes = is_array($row['code']) ? $row['code'] : [$row['code']];
                foreach ($codes as $c) {
                    if (isset($resultsByCode[$c])) {
                        foreach ($resultsByCode[$c] as $col => $val) {
                            $rowCounts[$col] += $val;
                        }
                    }
                }
            }

            $finalData[] = [
                'label' => $row['label'],
                'cols' => $rowCounts,
                'total' => array_sum(array_slice($rowCounts, 0, 28)),
                'color' => $row['color'] ?? '',
            ];
        }

        if ($request->ajax()) {
            return view('informes.morbilidad_content', compact('anos', 'meses', 'jornadas', 'ano', 'mes', 'jornada', 'finalData', 'totalGeneral'));
        }
        return view('informes.morbilidad', compact('anos', 'meses', 'jornadas', 'ano', 'mes', 'jornada', 'finalData', 'totalGeneral'));
    }
}
