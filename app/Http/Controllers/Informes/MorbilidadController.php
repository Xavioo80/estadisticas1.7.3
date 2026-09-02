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

        $rowsDef = $this->getRowsDefinition();

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
                $cod = trim($rg->{"cod_$i"} ?? '');
                if ($diag === '' && $cod === '') continue;

                $cond = strtoupper(trim($rg->{"cond_$i"} ?? ($rg->cond ?? '')));
                if ($cond !== 'N' && $cond !== 'S') $cond = 'N';

                $unrolled[] = (object)[
                    'cod' => $cod,
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

        // Preparar mapa de definiciones normalizadas
        $preparedRows = [];
        foreach ($rowsDef as $idx => $row) {
            if (isset($row['space'])) {
                $preparedRows[$idx] = null;
                continue;
            }
            $diags = isset($row['diag']) ? (is_array($row['diag']) ? $row['diag'] : [$row['diag']]) : [];
            $codes = isset($row['code']) ? (is_array($row['code']) ? $row['code'] : [$row['code']]) : [];
            $preparedRows[$idx] = [
                'diagsNorm' => array_flip(array_map([$this, 'normalizeForMatch'], $diags)),
                'rawDiagsNorm' => array_map([$this, 'normalizeForMatch'], $diags),
                'codes' => array_flip($codes),
            ];
        }

        $rowCountsByRowIdx = [];
        foreach ($rowsDef as $idx => $row) {
            $rowCountsByRowIdx[$idx] = array_fill(1, 32, 0);
        }
        $totalGeneral = array_fill(1, 32, 0);

        foreach ($rawData as $r) {
            $col = $getCol($r);
            if (!$col) continue;

            $cod = trim($r->cod);
            $diagNorm = $this->normalizeForMatch($r->diagnostico ?? '');
            $targetTotalCol = 28 + (($col - 1) % 4 + 1);

            $matchedAny = false;
            foreach ($preparedRows as $idx => $prep) {
                if (!$prep) continue;
                $matches = false;
                if (!empty($diagNorm)) {
                    if (isset($prep['diagsNorm'][$diagNorm])) {
                        $matches = true;
                    } else {
                        foreach ($prep['rawDiagsNorm'] as $term) {
                            if (strlen($term) > 4 && str_contains($diagNorm, $term)) {
                                $matches = true;
                                break;
                            }
                        }
                    }
                }

                if (!$matches && empty($diagNorm) && !empty($cod) && isset($prep['codes'][$cod])) {
                    $matches = true;
                }

                if ($matches) {
                    $rowCountsByRowIdx[$idx][$col]++;
                    $rowCountsByRowIdx[$idx][$targetTotalCol]++;
                    $matchedAny = true;
                }
            }

            if ($matchedAny) {
                $totalGeneral[$col]++;
                $totalGeneral[$targetTotalCol]++;
            }
        }

        $finalData = [];
        foreach ($rowsDef as $idx => $row) {
            if (isset($row['space'])) {
                $finalData[] = ['label' => $row['label'], 'is_extra' => true, 'cols' => array_fill(1, 32, 0), 'total' => 0];
                continue;
            }

            $counts = $rowCountsByRowIdx[$idx];
            $finalData[] = [
                'id' => $row['id'] ?? '',
                'label' => $row['label'],
                'cols' => $counts,
                'total' => array_sum(array_slice($counts, 0, 28)),
                'color' => $row['color'] ?? '',
            ];
        }

        if ($request->ajax()) {
            return view('informes.morbilidad_content', compact('anos', 'meses', 'jornadas', 'ano', 'mes', 'jornada', 'finalData', 'totalGeneral'));
        }
        return view('informes.morbilidad', compact('anos', 'meses', 'jornadas', 'ano', 'mes', 'jornada', 'finalData', 'totalGeneral'));
    }

    /**
     * Retorna el detalle de pacientes para una celda seleccionada en Morbilidad.
     */
    public function getDetails(Request $request): \Illuminate\Http\JsonResponse
    {
        $ano = (int)$request->input('ano', (int)date('Y'));
        $mes = (string)$request->input('mes', '');
        $jornada = (string)$request->input('jornada', 'TODAS');
        $rowId = (string)$request->input('row_id', '');
        $col = (string)$request->input('col', 'all');

        $rowsDef = $this->getRowsDefinition();
        $targetRow = null;
        foreach ($rowsDef as $r) {
            if (isset($r['id']) && $r['id'] === $rowId) {
                $targetRow = $r;
                break;
            }
        }

        if (!$targetRow) {
            return response()->json(['error' => 'Diagnóstico no encontrado'], 404);
        }

        $diags = isset($targetRow['diag']) ? (is_array($targetRow['diag']) ? $targetRow['diag'] : [$targetRow['diag']]) : [];
        $codes = isset($targetRow['code']) ? (is_array($targetRow['code']) ? $targetRow['code'] : [$targetRow['code']]) : [];
        $diagsNorm = array_map([$this, 'normalizeForMatch'], $diags);

        $getCol = function ($r) {
            $cond = strtoupper(trim($r->cond_diagnostico));
            $isN = ($cond == 'N');
            $isS = ($cond == 'S');
            if (!$isN && !$isS) return null;

            $t = strtoupper(trim($r->tipo));
            $e = (int) $r->edad;
            $ageIdx = 0;

            if ($t == 'D' || $t == 'M' || ($t == 'A' && $e == 0)) {
                $ageIdx = 1;
            } elseif ($t == 'A') {
                if ($e >= 1 && $e <= 4) $ageIdx = 2;
                elseif ($e >= 5 && $e <= 14) $ageIdx = 3;
                elseif ($e >= 15 && $e <= 19) $ageIdx = 4;
                elseif ($e >= 20 && $e <= 49) $ageIdx = 5;
                elseif ($e >= 50 && $e <= 59) $ageIdx = 6;
                elseif ($e >= 60) $ageIdx = 7;
            }
            if ($ageIdx == 0) return null;

            $sexo = strtoupper(trim($r->sexo)) == 'H' ? 'H' : 'M';
            $subCol = 0;
            if ($sexo == 'H') $subCol = $isN ? 1 : 2;
            else $subCol = $isN ? 3 : 4;

            return (($ageIdx - 1) * 4) + $subCol;
        };

        $query = RegistroGlobal::query()->where('ano', $ano);
        if (!empty($mes)) {
            $query->where('mes', $mes);
        }
        if ($jornada !== 'TODAS') {
            $query->where('jornada', $jornada);
        }

        $rgRecords = $query->get([
            'id', 'numero', 'fecha', 'se', 'edad', 'tipo', 'sexo', 'cond', 'exp', 'identidad', 'nombre_paciente', 'medico', 'prof', 'jornada',
            'diagnostico_1', 'cod_1', 'cond_1',
            'diagnostico_2', 'cod_2', 'cond_2',
            'diagnostico_3', 'cod_3', 'cond_3',
            'diagnostico_4', 'cod_4', 'cond_4',
            'diagnostico_5', 'cod_5', 'cond_5',
            'diagnostico_6', 'cod_6', 'cond_6',
            'diagnostico_7', 'cod_7', 'cond_7',
        ]);

        $details = [];
        $colInt = is_numeric($col) ? (int)$col : null;

        $ageRangeNames = [
            1 => '< 1 AÑO',
            2 => '1 A 4 A.',
            3 => '5 A 14 A.',
            4 => '15 A 19',
            5 => '20 A 49',
            6 => '50 A 59',
            7 => '60 AÑOS Y MÁS'
        ];

        $colDescription = 'Todos los casos del mes';
        if ($colInt !== null && $colInt >= 1 && $colInt <= 28) {
            $ageIdx = intdiv($colInt - 1, 4) + 1;
            $subCol = ($colInt - 1) % 4 + 1;
            $sexoStr = in_array($subCol, [1, 2]) ? 'HOM' : 'MUJ';
            $condStr = in_array($subCol, [1, 3]) ? 'NUEVO (N)' : 'SUBSECUENTE (S)';
            $colDescription = "{$ageRangeNames[$ageIdx]} • {$sexoStr} {$condStr}";
        } elseif ($colInt !== null && $colInt >= 29 && $colInt <= 32) {
            $totalSub = $colInt - 28;
            $sexoStr = in_array($totalSub, [1, 2]) ? 'HOM' : 'MUJ';
            $condStr = in_array($totalSub, [1, 3]) ? 'NUEVO (N)' : 'SUBSECUENTE (S)';
            $colDescription = "TOTAL GENERAL • {$sexoStr} {$condStr}";
        } elseif ($col === 'suma') {
            $colDescription = "SUMA TOTAL DEL MES";
        }

        foreach ($rgRecords as $rg) {
            for ($i = 1; $i <= 7; $i++) {
                $diag = trim($rg->{"diagnostico_$i"} ?? '');
                $cod = trim($rg->{"cod_$i"} ?? '');
                if ($diag === '' && $cod === '') continue;

                $cond = strtoupper(trim($rg->{"cond_$i"} ?? ($rg->cond ?? '')));
                if ($cond !== 'N' && $cond !== 'S') $cond = 'N';

                $diagNorm = $this->normalizeForMatch($diag);

                $matches = false;
                if (!empty($diagNorm)) {
                    if (in_array($diagNorm, $diagsNorm, true)) {
                        $matches = true;
                    } else {
                        foreach ($diagsNorm as $term) {
                            if (strlen($term) > 4 && str_contains($diagNorm, $term)) {
                                $matches = true;
                                break;
                            }
                        }
                    }
                }

                if (!$matches && empty($diagNorm) && !empty($cod) && in_array($cod, $codes, true)) {
                    $matches = true;
                }

                if (!$matches) continue;

                $itemObj = (object)[
                    'edad' => $rg->edad,
                    'tipo' => $rg->tipo,
                    'sexo' => $rg->sexo,
                    'cond_diagnostico' => $cond,
                ];
                $cellCol = $getCol($itemObj);
                if (!$cellCol) continue;

                $include = false;
                if ($col === 'all' || $col === 'suma') {
                    $include = true;
                } elseif ($colInt !== null) {
                    if ($colInt >= 1 && $colInt <= 28) {
                        $include = ($cellCol === $colInt);
                    } elseif ($colInt >= 29 && $colInt <= 32) {
                        $targetTotalCol = 28 + (($cellCol - 1) % 4 + 1);
                        $include = ($targetTotalCol === $colInt);
                    }
                }

                if ($include) {
                    $fechaFmt = $rg->fecha ? \Carbon\Carbon::parse($rg->fecha)->format('d/m/Y') : '-';
                    $medicoStr = (string)($rg->medico ?: 'No asignado');
                    if ($rg->prof) {
                        $medicoStr .= " - {$rg->prof}";
                    }

                    $edadStr = trim($rg->edad . ' ' . strtoupper(trim((string)$rg->tipo)));

                    $details[] = [
                        'id' => $rg->id,
                        'numero' => $rg->numero ?: $rg->id,
                        'fecha' => $fechaFmt,
                        'exp' => $rg->exp ?: '-',
                        'paciente' => $rg->nombre_paciente ?: 'Sin nombre registrado',
                        'identidad' => $rg->identidad ?: '-',
                        'sexo' => strtoupper(trim((string)$rg->sexo)) === 'H' ? 'H' : 'M',
                        'edad' => $edadStr,
                        'diagnostico' => $diag . ($cod !== '' ? " ({$cod})" : ''),
                        'cond' => $cond,
                        'medico' => $medicoStr,
                        'jornada' => $rg->jornada ?: '-',
                    ];
                }
            }
        }

        return response()->json([
            'label' => $targetRow['label'],
            'filtro' => $colDescription,
            'total' => count($details),
            'details' => $details,
        ]);
    }

    /**
     * Definición de filas del Informe de Morbilidad
     */
    private function getRowsDefinition(): array
    {
        return [
            ['id' => 'tifoidea', 'label' => 'FIEBRE TIFOIDEA Y PARATIFOIDEA', 'code' => 'A.01.0', 'diag' => 'FIEBRE TIFOIDEA Y PARATIFOIDEA'],
            ['id' => 'fiebre_origen', 'label' => 'FIEBRE DE ORIGEN DESCONOCIDO', 'diag' => 'FIEBRE DE ORIGEN DESCONOCIDO'],
            ['id' => 'disenteria', 'label' => 'DISENTERIA', 'diag' => ['DISENTERIA', 'DISENTERIA BACILAR', 'DISENTERIA AMEBIANA']],
            ['id' => 'diarreas', 'label' => 'DIARREAS', 'code' => 'A09.X', 'diag' => ['DIARREAS', 'DIARREAS CON DESHIDRATACION', 'DIARREAS SIN DESHIDRATACION']],
            ['id' => 'tuberculosis', 'label' => 'TUBERCULOSIS', 'code' => 'J16.4', 'diag' => 'TUBERCULOSIS'],
            ['id' => 'hepatitis_inf', 'label' => 'HEPATITIS INFECCIOSA', 'code' => 'B15.9', 'diag' => 'HEPATITIS INFECCIOSA'],
            ['id' => 'difteria', 'label' => 'DIFTERIA', 'code' => 'A36.9', 'diag' => 'DIFTERIA'],
            ['id' => 'tosferina', 'label' => 'TOSFERINA', 'code' => 'A37.9', 'diag' => 'TOSFERINA'],
            ['id' => 'meningococica', 'label' => 'INFECCION MENINGOCOCICA', 'diag' => 'INFECCION MENINGOCOCICA'],
            ['id' => 'tetano_neo', 'label' => 'TETANO NEONATORUM', 'code' => 'A33.X', 'diag' => 'TETANO NEONATORUM'],
            ['id' => 'polio', 'label' => 'POLIOMIELITIS AGUDA', 'code' => 'A80.9', 'diag' => 'POLIOMIELITIS AGUDA'],
            ['id' => 'sarampion', 'label' => 'SARAMPION', 'code' => 'B05.9', 'diag' => 'SARAMPION'],
            ['id' => 'rubeola', 'label' => 'RUBEOLA', 'code' => 'B06.9', 'diag' => 'RUBEOLA'],
            ['id' => 'dengue_ssa', 'label' => 'SOSP. DENGUE SIN SIGNOS DE ALARMA', 'code' => 'A90.X', 'diag' => ['SOSP. DENGUE SIN SIGNOS DE ALARMA', 'DENGUE SIN SIGNOS DE ALARMA', 'DSSA', 'D.S.S.A', 'SOSPECHA DENGUE SIN SIGNOS DE ALARMA', 'DENGUE S.S.A']],
            ['id' => 'dengue_csa', 'label' => 'SOSP. DENGUE CON SIGNOS DE ALARMA', 'diag' => ['SOSP. DENGUE CON SIGNOS DE ALARMA', 'DENGUE CON SIGNOS DE ALARMA', 'DCSA', 'D.C.S.A', 'SOSPECHA DENGUE CON SIGNOS DE ALARMA', 'DENGUE C.S.A']],
            ['id' => 'dengue_grave', 'label' => 'DENGUE GRAVE', 'diag' => 'DENGUE GRAVE'],
            ['id' => 'zika', 'label' => 'ZIKA', 'diag' => 'ZIKA'],
            ['id' => 'chikungunya', 'label' => 'CHIKUNGUNYA', 'diag' => 'CHIKUNGUNYA'],
            ['id' => 'lumbalgia', 'label' => 'LUMBALGIA', 'diag' => 'LUMBALGIA'],
            ['id' => 'hemorroides', 'label' => 'HEMORROIDES', 'diag' => 'HEMORROIDES'],
            ['id' => 'malaria', 'label' => 'MALARIA', 'code' => 'B54.X', 'diag' => 'MALARIA'],
            ['id' => 'leishmaniasis', 'label' => 'LEISHMANIASIS CUTANEA', 'code' => 'B55.1', 'diag' => 'LEISHMANIASIS CUTANEA'],
            ['id' => 'sifilis', 'label' => 'SIFILIS', 'code' => 'A53.9', 'diag' => ['SIFILIS', 'SIFILIS PRIMARIA', 'SIFILIS SECUNDARIA', 'SIFILIS CONGENITA', 'SIFILIS LATENTE', 'SIFILIS PRECOZ', 'SIFILIS TARDIA', 'SIFILIS GESTACIONAL']],
            ['id' => 'gonorrea', 'label' => 'GONORREA', 'code' => 'A54.9', 'diag' => ['GONORREA', 'INFECCION GONOCOCICA', 'GONOCOCO', 'BLENORRAGIA']],
            ['id' => 'micosis', 'label' => 'MICOSIS', 'diag' => 'MICOSIS'],
            ['id' => 'vih', 'label' => 'VIH', 'diag' => ['VIH', 'VIH-SIDA', 'VIH POSITIVO', 'CASOS ASINTOMATICOS POR VIH', 'INFECCION AVANZADA POR VIH', 'SIDA', 'SINDROME DE INMUNODEFICIENCIA ADQUIRIDA', 'ENFERMEDAD POR VIH', 'VIH ASINTOMATICO', 'INFECCION ASINTOMATICA POR VIH']],
            ['id' => 'parasitosis', 'label' => 'PARASITOSIS INTESTINAL', 'diag' => 'PARASITOSIS INTESTINAL'],
            ['id' => 'tumores_mal', 'label' => 'TUMORES MALIGNOS', 'diag' => 'TUMORES MALIGNOS'],
            ['id' => 'tumores_ben', 'label' => 'TUMORES BENIGNOS', 'diag' => 'TUMORES BENIGNOS'],
            ['id' => 'cancer_situ', 'label' => 'CANCER IN SITU', 'diag' => 'CANCER IN SITU'],
            ['id' => 'bocio', 'label' => 'BOCIO', 'diag' => 'BOCIO'],
            ['id' => 'diabetes', 'label' => 'DIABETES', 'code' => 'E14.9', 'diag' => ['DIABETES', 'DIABETES MELLITUS', 'DIABETES MELLITUS TIPO 2', 'DIABETES MELLITUS TIPO 1', 'DM', 'DM2', 'DMT2', 'DIABETES MELLITUS NO INSULINODEPENDIENTE', 'DIABETES MELLITUS INSULINODEPENDIENTE']],
            ['id' => 'anemias', 'label' => 'ANEMIAS', 'diag' => 'ANEMIAS'],
            ['id' => 'violencia', 'label' => 'VIOLENCIA DOMESTICA EN TODAS SUS FORMAS', 'code' => 'AA', 'diag' => 'VIOLENCIA DOMESTICA EN TODAS SUS FORMAS'],
            ['id' => 'ansiedad', 'label' => 'ANSIEDAD Y ESTRÉS', 'code' => 'F40-F49', 'diag' => 'TRASTORNOS NEURÓTICOS, TRASTORNOS RELACIONADOS CON EL ESTRÉS Y TRASTORNOS SOMATOMORFOS'],
            ['id' => 'trast_alcohol', 'label' => 'TRASTORNOS MENTALES Y DEL COMPORTAMIENTO DEBIDO AL ALCOHOL', 'code' => ['F10', '37', '76'], 'diag' => [
                'TRASTORNOS MENTALES Y DEL COMPORTAMIENTO DEBIDO AL ALCOHOL',
                'TRASTORNO MENTAL Y DEL COMPORTAMIENTO DEBIDO AL ALCOHOL',
                'TRAST. MENTALES Y DEL COMPORTAMIENTO DEBIDO AL ALCOHOL',
                'TRAST. MENTALES Y DEL COMPORTAMIENTO DEBIDOS AL USO DE ALCOHOL',
                'SINDROME DEPENDENCIA DEL ALCOHOL',
                'SINDROME DE DEPENDENCIA DEL ALCOHOL',
                'PROBLEMAS RELACIONADOS CON EL USO DEL ALCOHOL',
                'ALCOHOLISMO',
                'DEPENDENCIA DEL ALCOHOL'
            ]],
            ['id' => 'trast_drogas', 'label' => 'TRASTORNO DEBIDO A CONSUMO DE OTRAS DROGAS', 'code' => ['F11', 'F12', 'F13', 'F14', 'F15', 'F16', 'F17', 'F18', 'F19', 'F11-F19', '38', '77', '156', '402'], 'diag' => [
                'TRASTORNO DEBIDO A CONSUMO DE OTRAS DROGAS',
                'TRASTORNOS DEBIDO A CONSUMO DE OTRAS DROGAS',
                'TRASTORNO DEBIDO AL CONSUMO DE OTRAS DROGAS',
                'TRASTORNOS DEBIDOS AL CONSUMO DE OTRAS DROGAS',
                'TRASTORNOS MENTALES Y DEL COMPORTAMIENTO DEBIDOS AL CONSUMO DE SUSTANCIAS PSICOTROPAS',
                'TRAST. MENTALES Y DEL COMPORTAMIENTO DEBIDOS AL CONSUMO DE SUSTANCIAS PSICOTROPAS',
                'TRAST. MENTALES Y DEL COMPORTAMIENTO DEBIDOS AL CONSUMO DE SUSTANCIAS PSICOTROPAS.',
                'TRASTORNOS MENTALES Y DEL COMPORTAMIENTO DEBIDOS AL USO DE OTRAS DROGAS',
                'SINDROME DE DEPENDENCIA CON CONSUMO ACTUAL DE LA SUSTANCIA',
                'PROBLEMAS RELACIONADOS CON EL USO DE LAS DROGAS',
                'CONSUMO DE SUSTANCIAS',
                'CONSUMO DE SUSTANCIAS PSICOTROPAS',
                'CONSUMO DE DROGAS',
                'DROGAS',
                'TABAQUISMO',
                'PSICOACTIVAS',
                'SUSTANCIAS PSICOTROPAS'
            ]],
            ['label' => '', 'space' => true],
            ['id' => 'maltrato', 'label' => 'MALTRATO INFANTIL', 'diag' => ['MALTRATO INFANTIL','NEGLIGENCIA O ABANDONO (NIÑOS Y NIÑAS)','ABUSO SEXUAL','ABUSO PSICOLÓGICO',]],
            ['id' => 'depresion', 'label' => 'TRAST. DEL HUMOR EPISODIO DEPRESIVO', 'diag' => ['TRASTORNOS DEL HUMOR EPISODIO DEPRESIVO', 'DEPRESION', 'EPISODIO DEPRESIVO']],
            ['id' => 'tdah', 'label' => 'SINDROME HIPERCINETICO DE LA NIÑEZ (HIPERACTIVIDAD, TDAH)', 'code' => ['F90', 'F90.0', 'F90.1', 'F90.8', 'F90.9', 'F90-F98', '43', '413'], 'diag' => [
                'SINDROME HIPERCINETICO DE LA NIÑEZ',
                'SINDROME HIPERSINETICO DE LA NIÑEZ',
                'SINDROME HIPERCINETICO',
                'SINDROME HIPERSINETICO',
                'TRASTORNO EMOCIONALES Y DEL COMPORTAMIENTO QUE APARECEN HABITUALMENTE EN LA NIÑEZ Y LA ADOLESCENCIA.',
                'TRASTORNOS EMOCIONALES Y DEL COMPORTAMIENTO QUE APARECEN HABITUALMENTE EN LA NIÑEZ Y LA ADOLESCENCIA',
                'TRASTORNO EMOCIONALES Y DEL COMPORTAMIENTO',
                'TRASTORNOS EMOCIONALES Y DEL COMPORTAMIENTO',
                'TDAH',
                'HIPERACTIVIDAD',
                'DEFICIT DE ATENCION',
                'TRASTORNO POR DEFICIT DE ATENCION'
            ]],
            ['id' => 'epilepsia', 'label' => 'EPILEPSIA', 'diag' => 'EPILEPSIA'],
            ['id' => 'faringo_viral', 'label' => 'FARINGOAMIGDALITIS VIRAL', 'diag' => 'FARINGOAMIGDALITIS VIRAL'],
            ['id' => 'resfrio', 'label' => 'RESFRIO COMUN', 'diag' => ['RESFRIO COMUN', 'RESFRIADO COMUN']],
            ['id' => 'faringitis', 'label' => 'FARINGITIS', 'diag' => 'FARINGITIS'],
            ['id' => 'otras_iras', 'label' => 'OTRAS IRAS (LARINGITIS, CRUP )', 'diag' => ['OTRAS IRAS', 'LARINGITIS', 'CRUP', 'SINOSITIS', 'SBO', 'SINDROME BRONQUIAL OBSTRUCTIVO', 'RINITIS ALERGICA', 'CRUP (Laringotraqueobronquitis Aguda)', 'LARINGITIS AGUDA', 'LARINGOTRAQUEITIS AGUDA']],
            ['id' => 'faringo_estrepto', 'label' => 'FARING.AMIG. ESTREPTOCOCICAS', 'diag' => 'FARINGOAMIGDALITIS ESTREPTOCOCICAS'],
            ['id' => 'neumonias', 'label' => 'NEUMONIAS/BRONCONEUMONIAS', 'diag' => ['NEUMONIA', 'NEUMONIAS', 'BRONCONEUMONIA', 'BRONCONEUMONIAS', 'NEUMONIAS/BRONCONEUMONIAS', 'NEUMONIAS/BORNCONEUMONIAS']],
            ['id' => 'covid', 'label' => 'COVID-19', 'diag' => ['ATENCION CLINICA POR COVID-19', 'COVID-19']],
            ['id' => 'asma', 'label' => 'ASMA BRONQUIAL/BRONQUITIS', 'diag' => ['ASMA BRONQUIAL', 'BRONQUITIS', 'ASMA BRONQUIAL Y BRONQUITIS', 'ASMA BRONQUIAL/BRONQUITIS']],
            ['id' => 'itu', 'label' => 'INFECCION TRACTO URINARIO (ITU)', 'diag' => ['ITU', 'ENF. APARATO GENITOURINARIO', 'INFECCION EN TRACTO URINARIO', 'INFECCION TRACTO URINARIO']],
            ['id' => 'vias_urinarias', 'label' => 'ENF. DE VIAS URINARIAS (PIELONEFRITIS,CISTITIS)', 'diag' => ['ENF. DE VIAS URINARIAS', 'PIELONEFRITIS', 'CISTITIS', 'URETRITIS', 'PROSTATITIS', 'ENF. DE VIAS URINARIAS (PIELONEFRITIS,CISTITIS)']],
            ['id' => 'fiebre_reumatica', 'label' => 'FIEBRE REUMATICA', 'diag' => 'FIEBRE REUMATICA'],
            ['id' => 'hta', 'label' => 'HIPERTENSION ARTERIAL', 'diag' => ['HIPERTENSION ARTERIAL', 'HIPERTENSION', 'HTA', 'HIPERTENSION PRIMARIA', 'HIPERTENSION ESENCIAL', 'HIPERTENSION ARTERIAL PRIMARIA', 'HIPERTENSION ARTERIAL ESENCIAL', 'HIPERTENSION ARTERIAL SISTEMICA', 'HAS']],
            ['id' => 'cardiopatias', 'label' => 'CARDIOPATIAS', 'diag' => ['CARDIOPATIAS', 'CARDIOPATIA']],
            ['id' => 'ecv', 'label' => 'ENFERMEDAD CEREBRO VASCULAR', 'diag' => 'ENFERMEDAD CEREBRO VASCULAR'],
            ['id' => 'hernias', 'label' => 'HERNIAS', 'diag' => 'HERNIAS'],
            ['id' => 'dislipidemia', 'label' => 'DISLIPIDEMIA:[elevación anormal de concentrac. de grasas en la sangre (colest., triglicér., colesterol HDL y LDL).', 'diag' => 'DISLIPIDEMIA'],
            ['id' => 'cervicitis', 'label' => 'CERVICITIS', 'diag' => 'CERVICITIS'],
            ['id' => 'prolapso', 'label' => 'PROLAPSO GENITAL', 'diag' => 'PROLAPSO GENITAL'],
            ['id' => 'comp_embarazo', 'label' => 'COMPLICACIONES DEL EMBARAZO', 'diag' => 'COMPLICACIONES DEL EMBARAZO'],
            ['id' => 'dermatitis', 'label' => 'DERMATITIS', 'diag' => 'DERMATITIS'],
            ['id' => 'fracturas', 'label' => 'FRACTURAS Y CONTUSIONES', 'diag' => 'FRACTURAS Y CONTUSIONES'],
            ['id' => 'alergias', 'label' => 'ALERGIAS', 'diag' => 'ALERGIAS'],
            ['id' => 'mordeduras_rabia', 'label' => 'MORDEDURAS ANIMALES TRANSM. DE RABIA', 'diag' => 'MORDEDURAS ANIMALES TRASM DE RABIA'],
            ['id' => 'desnutricion', 'label' => 'DESNUTRICION', 'diag' => ['DESNUTRICION', 'DESNUTRICION SEVERA']],
            ['id' => 'obesidad', 'label' => 'OBESIDAD', 'diag' => ['OBESIDAD', 'SOBREPESO', 'OBESIDAD Y SOBREPESO', 'OBESIDAD O SOBREPESO ']],
            ['id' => 'conjuntivitis', 'label' => 'CONJUNTIVITIS', 'diag' => 'CONJUNTIVITIS'],
            ['id' => 'otitis', 'label' => 'OTITIS MEDIA', 'diag' => 'OTITIS MEDIA'],
            ['id' => 'varicela', 'label' => 'VARICELA', 'diag' => 'VARICELA'],
            ['id' => 'gastritis', 'label' => 'GASTRITIS', 'diag' => 'GASTRITIS'],
            ['id' => 'heridas', 'label' => 'HERIDAS Y TRAUMATISMOS', 'diag' => ['HERIDAS Y TRAUMATISMOS', 'HERIDAS', 'TRAUMATISMO']],
            ['id' => 'chagas', 'label' => 'CHAGAS', 'diag' => 'CHAGAS'],
            ['id' => 'otras_pat', 'label' => 'OTRAS PATOLOGIAS', 'diag' => 'OTRAS PATOLOGIAS'],
            ['id' => 'prenatal_antes12', 'label' => 'ATENCIÓN PRENATAL ANTES DE LAS 12 SEMANAS DE GESTIÓN', 'diag' => 'ATENCION PRENATAL ANTES DE LAS 12 SG'],
            ['id' => 'prenatal_desp12', 'label' => 'ATENCIÓN PRENATAL DESPUÉS DE LAS 12 SEMANAS DE GESTACIÓN', 'diag' => 'ATENCION PRENATAL DESPUES DE LAS 12 SG'],
        ];
    }
}
