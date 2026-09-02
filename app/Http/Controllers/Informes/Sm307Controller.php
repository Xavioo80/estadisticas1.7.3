<?php

namespace App\Http\Controllers\Informes;

use App\Http\Controllers\Controller;
use App\Models\Informe;
use App\Models\RegistroGlobal;
use App\Traits\InformesHelperTrait;
use Illuminate\Http\Request;

class Sm307Controller extends Controller
{
    use InformesHelperTrait;

    public function __construct(private \App\Services\RegistroGlobalService $service)
    {
    }

    public function index(Request $request)
    {
        $ano = $request->input('ano', date('Y'));
        $mes = $request->input('mes', '');

        // Resolver mes por defecto basado en los últimos datos ingresados
        if (empty($mes)) {
            $mes = $this->resolverMesPorDefecto($ano, true);
        }

        $jornada = $request->input('jornada', 'TODAS') ?: 'TODAS';
        $lado = $request->input('lado', 'obverso');

        $anos = $this->getAnosDisponibles();
        $meses = $this->getMesesDisponibles($ano);
        $jornadas = $this->getJornadasDisponibles();

        $diagCatalog = $this->getDiagCatalog($lado);

        $rowsDef = [];
        $textLookup = [];

        foreach ($diagCatalog as $row) {
            $rowsDef[] = [
                'id_diag' => $row['id'],
                'display' => $row['code'] ?? '',
                'label' => $row['label'],
                'seccion' => $row['seccion'] ?? ''
            ];

            // Construir mapa de búsqueda textual
            foreach ((array) $row['diag'] as $term) {
                $norm = $this->normalizeForMatch($term);
                if (!empty($norm)) {
                    $textLookup[$norm] = $row['id'];
                }
            }
        }

        // =====================================================================
        // 2. CONSULTA A REGISTROS GLOBALES
        // =====================================================================
        $query = RegistroGlobal::query()->where('ano', $ano)->where('mes', $mes);
        if ($jornada != 'TODAS') {
            $query->where('jornada', $jornada);
        }

        $rawData = $query->select(
            'fecha',
            'sexo',
            'edad',
            'tipo',
            'cond',
            'prof',
            'cod_1',
            'cond_1',
            'diagnostico_1',
            'cod_2',
            'cond_2',
            'diagnostico_2',
            'cod_3',
            'cond_3',
            'diagnostico_3',
            'cod_4',
            'cond_4',
            'diagnostico_4',
            'cod_5',
            'cond_5',
            'diagnostico_5',
            'cod_6',
            'cond_6',
            'diagnostico_6',
            'cod_7',
            'cond_7',
            'diagnostico_7'
        )->get();

        $resultsByCode = [];

        $mesMap = [
            'ENERO' => 1, 'FEBRERO' => 2, 'MARZO' => 3, 'ABRIL' => 4,
            'MAYO' => 5, 'JUNIO' => 6, 'JULIO' => 7, 'AGOSTO' => 8,
            'SEPTIEMBRE' => 9, 'OCTUBRE' => 10, 'NOVIEMBRE' => 11, 'DICIEMBRE' => 12
        ];
        $monthNum = $mesMap[strtoupper($mes)] ?? 0;
        $anoInt = (int) $ano;

        foreach ($rawData as $r) {
            // --- FILTRADO POR FECHA ROBUSTO ---
            if (str_contains($r->fecha, '/')) {
                $parts = explode('/', $r->fecha);
                if (count($parts) === 3) {
                    $day = (int) $parts[0];
                    $month = (int) $parts[1];
                    $year = (int) $parts[2];
                    if ($month !== $monthNum || $year !== $anoInt) {
                        continue;
                    }
                }
            }

            $sexo = strtoupper(trim($r->sexo ?? '')) == 'H' ? 'H' : 'M';
            $t = strtoupper(trim($r->tipo ?? 'A'));
            $e = (int) ($r->edad ?? 0);

            $ageIdx = 0;
            if ($t == 'D' || $t == 'M' || ($t == 'A' && $e < 1)) {
                $ageIdx = 1;
            } elseif ($t == 'A') {
                if ($e >= 1 && $e <= 4)
                    $ageIdx = 2;
                elseif ($e >= 5 && $e <= 9)
                    $ageIdx = 3;
                elseif ($e >= 10 && $e <= 14)
                    $ageIdx = 4;
                elseif ($e >= 15 && $e <= 19)
                    $ageIdx = 5;
                elseif ($e >= 20 && $e <= 24)
                    $ageIdx = 6;
                elseif ($e >= 25 && $e <= 39)
                    $ageIdx = 7;
                elseif ($e >= 40 && $e <= 59)
                    $ageIdx = 8;
                elseif ($e >= 60)
                    $ageIdx = 9;
            }

            if ($ageIdx == 0)
                continue;

            // =====================================================================
            // BÚSQUEDA DEL DIAGNÓSTICO POR CÓDIGO Y POR CONCEPTO TEXTUAL
            // =====================================================================
            for ($i = 1; $i <= 7; $i++) {
                $diagField = "diagnostico_$i";
                $codField = "cod_$i";
                $rawDiag = trim($r->$diagField ?? '');
                $rawCod = trim($r->$codField ?? '');

                if (empty($rawDiag) && empty($rawCod))
                    continue;

                $finalCod = '';
                $normDiag = $this->normalizeForMatch($rawDiag);
                $normCod = $this->normalizeForMatch($rawCod);

                // 1. Prioridad: Match exacto por diagnóstico
                if (!empty($normDiag) && isset($textLookup[$normDiag])) {
                    $finalCod = $textLookup[$normDiag];
                }
                // 2. Prioridad: Coincidencia parcial (Contenido en el concepto)
                elseif (!empty($normDiag)) {
                    foreach ($textLookup as $term => $code) {
                        if (strlen($term) > 4 && str_contains($normDiag, $term)) {
                            $finalCod = $code;
                            break;
                        }
                    }
                }

                // 3. Prioridad: Match por código si el diagnóstico vino vacío
                if (empty($finalCod) && empty($normDiag) && !empty($normCod) && isset($textLookup[$normCod])) {
                    $finalCod = $textLookup[$normCod];
                }

                if (empty($finalCod))
                    continue;

                // OBSERVACION: Atenciones en menores de 5 años (ageIdx 1 o 2) 
                // solo de Psiquiatría o Psicología, EXCEPTO REFERENCIAS (151, 152) y códigos de maltrato/violencia
                $p = strtoupper(trim($r->prof ?? ''));
                if (
                    ($ageIdx == 1 || $ageIdx == 2) &&
                    !in_array($p, ['PSIQUIATRA', 'PSICOLOGIA', 'PSICOLOGO', 'PSIQUIATRIA']) &&
                    !in_array($finalCod, ['151', '152', '402', '413', '422', '423', '424', '425', '426', '427', '428', '429', '430', '431', '432', '433', '434'])
                ) {
                    continue;
                }

                $condField = "cond_$i";
                $cond = strtoupper(trim($r->$condField ?? ''));
                if (empty($cond))
                    $cond = strtoupper(trim($r->cond ?? ''));

                $isN = ($cond == 'N');
                $isS = ($cond == 'S');
                if (!$isN && !$isS)
                    continue;

                $subCol = ($sexo == 'H') ? ($isN ? 1 : 3) : ($isN ? 2 : 4);
                $targetCol = (($ageIdx - 1) * 4) + $subCol;

                if (!isset($resultsByCode[$finalCod])) {
                    $resultsByCode[$finalCod] = array_fill(1, 40, 0);
                }
                $resultsByCode[$finalCod][$targetCol]++;
            }
        }

        $totalGeneral = array_fill(1, 40, 0);
        foreach ($resultsByCode as $c => $counts) {
            for ($i = 1; $i <= 36; $i++) {
                if ($counts[$i] == 0)
                    continue;
                $subOffset = ($i - 1) % 4; // 0=N-H, 1=N-M, 2=S-H, 3=S-M
                $targetTotalCol = 36 + ($subOffset + 1);
                $resultsByCode[$c][$targetTotalCol] += $counts[$i];
                $totalGeneral[$i] += $counts[$i];
                $totalGeneral[$targetTotalCol] += $counts[$i];
            }
        }

        $finalData = [];
        foreach ($rowsDef as $row) {
            $c = $row['id_diag'];
            $counts = $resultsByCode[$c] ?? array_fill(1, 40, 0);

            $finalData[] = [
                'id' => $row['id_diag'],
                'code' => $row['display'],
                'label' => $row['label'],
                'cols' => $counts,
                'seccion' => $row['seccion'],
            ];
        }

        $data = compact('finalData', 'totalGeneral', 'ano', 'mes', 'jornada', 'anos', 'meses', 'jornadas', 'lado');

        if ($request->ajax()) {
            return view('informes.sm307_content', $data);
        }

        return view('informes.sm307', $data);
    }

    /**
     * Obtener detalles de los médicos y fechas de atención para una casilla específica
     */
    public function cellDetails(Request $request)
    {
        $ano = $request->input('ano', date('Y'));
        $mes = $request->input('mes', '');
        $jornada = $request->input('jornada', 'TODAS');
        $lado = $request->input('lado', 'obverso');
        $diagId = $request->input('diag_id', '');
        $col = (int) $request->input('col', 1);

        $diagCatalog = $this->getDiagCatalog($lado);

        $targetRow = null;
        if ($diagId === 'TOTAL_ROW') {
            $targetRow = [
                'id' => 'TOTAL_ROW',
                'code' => 'TOTAL',
                'label' => 'TOTAL GENERAL DE ATENCIONES',
                'diag' => []
            ];
        } else {
            foreach ($diagCatalog as $row) {
                if ($row['id'] == $diagId || $row['code'] == $diagId) {
                    $targetRow = $row;
                    break;
                }
            }
        }

        if (!$targetRow) {
            return response()->json([
                'concepto' => 'Diagnóstico No Encontrado',
                'columna_nombre' => 'Columna ' . $col,
                'total_registros' => 0,
                'medicos' => []
            ]);
        }

        $textLookup = [];
        if ($diagId === 'TOTAL_ROW') {
            foreach ($diagCatalog as $r) {
                foreach ((array) $r['diag'] as $term) {
                    $norm = $this->normalizeForMatch($term);
                    if (!empty($norm)) {
                        $textLookup[$norm] = $r['id'];
                    }
                }
            }
        } else {
            foreach ((array) $targetRow['diag'] as $term) {
                $norm = $this->normalizeForMatch($term);
                if (!empty($norm)) {
                    $textLookup[$norm] = $targetRow['id'];
                }
            }
        }

        $colInfo = $this->getColInfo($col);

        $query = RegistroGlobal::query()->where('ano', $ano)->where('mes', $mes);
        if ($jornada != 'TODAS') {
            $query->where('jornada', $jornada);
        }

        $mesMap = [
            'ENERO' => 1, 'FEBRERO' => 2, 'MARZO' => 3, 'ABRIL' => 4,
            'MAYO' => 5, 'JUNIO' => 6, 'JULIO' => 7, 'AGOSTO' => 8,
            'SEPTIEMBRE' => 9, 'OCTUBRE' => 10, 'NOVIEMBRE' => 11, 'DICIEMBRE' => 12
        ];
        $monthNum = $mesMap[strtoupper($mes)] ?? 0;
        $anoInt = (int) $ano;

        $rawData = $query->select(
            'id', 'fecha', 'numero', 'exp', 'sexo', 'edad', 'tipo', 'cond', 'prof', 'medico',
            'cod_1', 'cond_1', 'diagnostico_1',
            'cod_2', 'cond_2', 'diagnostico_2',
            'cod_3', 'cond_3', 'diagnostico_3',
            'cod_4', 'cond_4', 'diagnostico_4',
            'cod_5', 'cond_5', 'diagnostico_5',
            'cod_6', 'cond_6', 'diagnostico_6',
            'cod_7', 'cond_7', 'diagnostico_7'
        )->get();

        $matchingAtenciones = [];

        foreach ($rawData as $r) {
            if (str_contains($r->fecha, '/')) {
                $parts = explode('/', $r->fecha);
                if (count($parts) === 3) {
                    $month = (int) $parts[1];
                    $year = (int) $parts[2];
                    if ($month !== $monthNum || $year !== $anoInt) {
                        continue;
                    }
                }
            }

            $sexo = strtoupper(trim($r->sexo ?? '')) == 'H' ? 'H' : 'M';
            $t = strtoupper(trim($r->tipo ?? 'A'));
            $e = (int) ($r->edad ?? 0);

            $ageIdx = 0;
            if ($t == 'D' || $t == 'M' || ($t == 'A' && $e < 1)) {
                $ageIdx = 1;
            } elseif ($t == 'A') {
                if ($e >= 1 && $e <= 4) $ageIdx = 2;
                elseif ($e >= 5 && $e <= 9) $ageIdx = 3;
                elseif ($e >= 10 && $e <= 14) $ageIdx = 4;
                elseif ($e >= 15 && $e <= 19) $ageIdx = 5;
                elseif ($e >= 20 && $e <= 24) $ageIdx = 6;
                elseif ($e >= 25 && $e <= 39) $ageIdx = 7;
                elseif ($e >= 40 && $e <= 59) $ageIdx = 8;
                elseif ($e >= 60) $ageIdx = 9;
            }

            if ($ageIdx == 0) continue;

            for ($i = 1; $i <= 7; $i++) {
                $diagField = "diagnostico_$i";
                $codField = "cod_$i";
                $rawDiag = trim($r->$diagField ?? '');
                $rawCod = trim($r->$codField ?? '');
                if (empty($rawDiag) && empty($rawCod)) continue;

                $finalCod = '';
                $normDiag = $this->normalizeForMatch($rawDiag);
                $normCod = $this->normalizeForMatch($rawCod);

                // 1. Prioridad: Match exacto por diagnóstico
                if (!empty($normDiag) && isset($textLookup[$normDiag])) {
                    $finalCod = $textLookup[$normDiag];
                }
                // 2. Prioridad: Coincidencia parcial (Contenido en el concepto)
                elseif (!empty($normDiag)) {
                    foreach ($textLookup as $term => $code) {
                        if (strlen($term) > 4 && str_contains($normDiag, $term)) {
                            $finalCod = $code;
                            break;
                        }
                    }
                }

                // 3. Prioridad: Match por código si el diagnóstico vino vacío
                if (empty($finalCod) && empty($normDiag) && !empty($normCod) && isset($textLookup[$normCod])) {
                    $finalCod = $textLookup[$normCod];
                }

                if (empty($finalCod)) continue;

                $p = strtoupper(trim($r->prof ?? ''));
                if (
                    ($ageIdx == 1 || $ageIdx == 2) &&
                    !in_array($p, ['PSIQUIATRA', 'PSICOLOGIA', 'PSICOLOGO', 'PSIQUIATRIA']) &&
                    !in_array($finalCod, ['151', '152', '402', '422', '423', '424', '425', '426', '427', '428', '429', '430', '431', '432', '433', '434'])
                ) {
                    continue;
                }

                $condField = "cond_$i";
                $cond = strtoupper(trim($r->$condField ?? ''));
                if (empty($cond)) $cond = strtoupper(trim($r->cond ?? ''));

                $isN = ($cond == 'N');
                $isS = ($cond == 'S');
                if (!$isN && !$isS) continue;

                $subCol = ($sexo == 'H') ? ($isN ? 1 : 3) : ($isN ? 2 : 4);
                $targetCol = (($ageIdx - 1) * 4) + $subCol;

                $matchesCol = ($targetCol == $col);
                if (!$matchesCol && $col >= 37 && $col <= 40) {
                    $subOffset = ($targetCol - 1) % 4;
                    if (36 + ($subOffset + 1) == $col) {
                        $matchesCol = true;
                    }
                }

                if ($matchesCol) {
                    $medicoNombre = trim($r->medico ?? '');
                    if (empty($medicoNombre)) $medicoNombre = 'MÉDICO NO ESPECIFICADO';

                    $fechaFormatted = $r->fecha;
                    if (str_contains($fechaFormatted, ' ')) {
                        $fechaFormatted = explode(' ', $fechaFormatted)[0];
                    }

                    $matchingAtenciones[] = [
                        'id' => $r->id,
                        'medico' => $medicoNombre,
                        'profesion' => !empty($r->prof) ? trim($r->prof) : 'MEDICO ASISTENCIAL',
                        'fecha' => $fechaFormatted,
                        'expediente' => $r->exp ?: ($r->numero ?: 'S/N'),
                        'sexo' => $sexo,
                        'edad' => $e . ($t == 'D' ? ' días' : ($t == 'M' ? ' meses' : ' años')),
                        'condicion' => $isN ? 'Nueva (1ra vez)' : 'Subsiguiente',
                        'diagnostico' => $rawDiag,
                    ];
                }
            }
        }

        // Agrupar por médico
        $medicosGrouped = [];
        foreach ($matchingAtenciones as $at) {
            $mName = $at['medico'];
            if (!isset($medicosGrouped[$mName])) {
                $medicosGrouped[$mName] = [
                    'medico' => $mName,
                    'profesion' => $at['profesion'],
                    'total' => 0,
                    'fechasCount' => [],
                    'atenciones' => []
                ];
            }
            $medicosGrouped[$mName]['total']++;
            $fDate = $at['fecha'];
            if (!isset($medicosGrouped[$mName]['fechasCount'][$fDate])) {
                $medicosGrouped[$mName]['fechasCount'][$fDate] = 0;
            }
            $medicosGrouped[$mName]['fechasCount'][$fDate]++;
            $medicosGrouped[$mName]['atenciones'][] = $at;
        }

        $medicosFormatted = [];
        foreach ($medicosGrouped as $m) {
            $fechasArr = [];
            foreach ($m['fechasCount'] as $fDate => $cnt) {
                $fechasArr[] = [
                    'fecha' => $fDate,
                    'count' => $cnt
                ];
            }
            usort($fechasArr, fn($a, $b) => strcmp($a['fecha'], $b['fecha']));
            $m['fechas'] = $fechasArr;
            unset($m['fechasCount']);
            $medicosFormatted[] = $m;
        }

        usort($medicosFormatted, fn($a, $b) => $b['total'] <=> $a['total']);

        return response()->json([
            'concepto' => ($targetRow['code'] ? '[' . $targetRow['code'] . '] ' : '') . $targetRow['label'],
            'columna_nombre' => $colInfo['nombre'],
            'total_registros' => count($matchingAtenciones),
            'medicos' => $medicosFormatted
        ]);
    }

    private function getColInfo($col)
    {
        $ageRanges = [
            1 => 'MENOR 1 AÑO', 2 => '1-4 AÑOS', 3 => '5-9 AÑOS', 4 => '10-14 AÑOS', 5 => '15-19 AÑOS',
            6 => '20-24 AÑOS', 7 => '25-39 AÑOS', 8 => '40-59 AÑOS', 9 => '60 Y MÁS'
        ];

        if ($col >= 1 && $col <= 36) {
            $ageIdx = (int) ceil($col / 4);
            $subCol = ($col - 1) % 4; // 0=N-H, 1=N-M, 2=S-H, 3=S-M
            $ageName = $ageRanges[$ageIdx] ?? '';
            $subName = '';
            switch ($subCol) {
                case 0: $subName = '1RA. VEZ — Hombre (H)'; break;
                case 1: $subName = '1RA. VEZ — Mujer (M)'; break;
                case 2: $subName = 'SUBSIGUIENTE — Hombre (H)'; break;
                case 3: $subName = 'SUBSIGUIENTE — Mujer (M)'; break;
            }
            return ['nombre' => "$ageName | $subName"];
        } elseif ($col >= 37 && $col <= 40) {
            $subCol = ($col - 37) % 4;
            $subName = '';
            switch ($subCol) {
                case 0: $subName = 'TOTAL 1RA. VEZ — Hombre (H)'; break;
                case 1: $subName = 'TOTAL 1RA. VEZ — Mujer (M)'; break;
                case 2: $subName = 'TOTAL SUBSIGUIENTE — Hombre (H)'; break;
                case 3: $subName = 'TOTAL SUBSIGUIENTE — Mujer (M)'; break;
            }
            return ['nombre' => $subName];
        }

        return ['nombre' => "Columna $col"];
    }

    private function getDiagCatalog($lado)
    {
        if ($lado == 'obverso') {
            return [
                ['id' => '401', 'code' => 'F00-F09', 'label' => 'TRAST. MENTALES ORGÁNICOS INCLUIDOS LOS TRASTORNOS SINTOMÁTICOS (DEMENCIAS)', 'diag' => ['TRAST. MENTALES ORGANICOS', 'DEMENCIA', 'ALZHEIMER', 'F00', 'F01', 'F02', 'F03', 'F04', 'F05', 'F06', 'F07', 'F08', 'F09']],
                ['id' => '402', 'code' => 'F10-F19', 'label' => 'TRASTORNOS MENTALES Y DEL COMPORTAMIENTO DEBIDOS AL CONSUMO DE SUSTANCIAS PSICOTROPAS', 'diag' => [
                    'TRASTORNOS MENTALES Y DEL COMPORTAMIENTO DEBIDOS AL CONSUMO DE SUSTANCIAS PSICOTROPAS',
                    'TRAST. MENTALES Y DEL COMPORTAMIENTO DEBIDOS AL CONSUMO DE SUSTANCIAS PSICOTROPAS',
                    'TRAST. MENTALES Y DEL COMPORTAMIENTO DEBIDOS AL CONSUMO DE SUSTANCIAS PSICOTROPAS.',
                    'TRASTORNOS MENTALES Y DEL COMPORTAMIENTO DEBIDO AL ALCOHOL',
                    'TRASTORNO MENTAL Y DEL COMPORTAMIENTO DEBIDO AL ALCOHOL',
                    'TRAST. MENTALES Y DEL COMPORTAMIENTO DEBIDO AL ALCOHOL',
                    'TRAST. MENTALES Y DEL COMPORTAMIENTO DEBIDOS AL USO DE ALCOHOL',
                    'TRASTORNO DEBIDO A CONSUMO DE OTRAS DROGAS',
                    'TRASTORNOS DEBIDO A CONSUMO DE OTRAS DROGAS',
                    'TRASTORNO DEBIDO AL CONSUMO DE OTRAS DROGAS',
                    'TRASTORNOS DEBIDOS AL CONSUMO DE OTRAS DROGAS',
                    'TRASTORNOS MENTALES Y DEL COMPORTAMIENTO DEBIDOS AL USO DE OTRAS DROGAS',
                    'SINDROME DEPENDENCIA DEL ALCOHOL',
                    'SINDROME DE DEPENDENCIA DEL ALCOHOL',
                    'SINDROME DE DEPENDENCIA CON CONSUMO ACTUAL DE LA SUSTANCIA',
                    'PROBLEMAS RELACIONADOS CON EL USO DEL ALCOHOL',
                    'PROBLEMAS RELACIONADOS CON EL USO DE LAS DROGAS',
                    'CONSUMO DE SUSTANCIAS',
                    'CONSUMO DE SUSTANCIAS PSICOTROPAS',
                    'CONSUMO DE DROGAS',
                    'DROGAS',
                    'ALCOHOLISMO',
                    'TABAQUISMO',
                    'PSICOACTIVAS',
                    'SUSTANCIAS PSICOTROPAS',
                    'DEPENDENCIA DEL ALCOHOL',
                    '37', '38', '76', '77', '156', '402',
                    'F10', 'F11', 'F12', 'F13', 'F14', 'F15', 'F16', 'F17', 'F18', 'F19'
                ]],
                ['id' => '403', 'code' => 'F20-F29', 'label' => 'ESQUIZOFRENIA, TRASTORNO ESQUIZOTIPICO Y TRASTORNO DE IDEAS DELIRANTES', 'diag' => ['ESQUIZOFRENIA', 'TRASTORNO ESQUIZOTIPICO', 'TRASTORNO DE IDEAS DELIRANTES', 'F20', 'F21', 'F22', 'F23', 'F24', 'F25', 'F26', 'F27', 'F28', 'F29']],
                ['id' => '404', 'code' => 'F30', 'label' => 'TRANSTORNOS DEL HUMOR (EPISODIO MANIACO)', 'diag' => ['TRANSTORNOS DEL HUMOR (EPISODIO MANIACO)', 'MANIACO', 'F30']],
                ['id' => '405', 'code' => 'F31', 'label' => 'TASTORNOS DEL HUMOR AFECTIVO ( BIPOLAR)', 'diag' => ['TASTORNOS DEL HUMOR AFECTIVO ( BIPOLAR)', 'BIPOLAR', 'TRASTORNO BIPOLAR', 'F31']],
                ['id' => '406', 'code' => 'F32', 'label' => 'TRASTORNOS DEL HUMOR, EPISODIO DEPRESIVO', 'diag' => ['TRASTORNOS DEL HUMOR, EPISODIO DEPRESIVO']],
                ['id' => '407', 'code' => 'F33-F39', 'label' => 'TRASTORNO DEPRESIVO RECURRENTE, TRASTORNO SEL HUMOR (AFECTIVOS RECURREBTES), OTROS TRASTORNOS DE HUMOR AFECTIVOS OR ESPECIFICADOS.', 'diag' => ['TRASTORNO DEPRESIVO RECURRENTE', 'HUMOR AFECTIVO', 'F33', 'F34', 'F38', 'F39']],
                ['id' => '408', 'code' => 'F40-F48', 'label' => 'TRASTORNOS NEURÓTICOS, TRASTORNOS RELACIONADOS CON EL ESTRÉS Y TRASTORNOS SOMATOMORFOS', 'diag' => ['TRASTORNOS NEUROTICOS', 'TRASTORNOS NEURÓTICOS, TRASTORNOS RELACIONADOS CON EL ESTRÉS Y TRASTORNOS SOMATOMORFOS']],
                ['id' => '409', 'code' => 'F50-F59', 'label' => 'SINDROME DEL COMPORTAMIENTO ASOCIADOS CON ALTERACIONES FISIOLOGICAS Y FACTORES FISICOS', 'diag' => ['SINDROME DEL COMPORTAMIENTO ASOCIADOS CON ALTERACIONES FISIOLOGICAS', 'TRASTORNO ALIMENTARIO', 'ANOREXIA', 'BULIMIA', 'F50', 'F51', 'F52', 'F53', 'F54', 'F55', 'F59']],
                ['id' => '410', 'code' => 'F60-F69', 'label' => 'TRASTORNOS DE LA PERSONALIDAD Y DEL COMPORTAMIENTO DEL ADULTO', 'diag' => ['TRASTORNOS DE LA PERSONALIDAD', 'COMPORTAMIENTO DEL ADULTO', 'F60', 'F61', 'F62', 'F63', 'F64', 'F65', 'F66', 'F67', 'F68', 'F69']],
                ['id' => '411', 'code' => 'F70-F79', 'label' => 'RETRASO MENTAL', 'diag' => ['RETRASO MENTAL', 'DISCAPACIDAD INTELECTUAL', 'F70', 'F71', 'F72', 'F73', 'F74', 'F75', 'F76', 'F77', 'F78', 'F79']],
                ['id' => '412', 'code' => 'F80-F89', 'label' => 'TRASTORNOS DEL DESARROLLO PSICOLÓGICO', 'diag' => ['TRASTORNOS DEL DESARROLLO PSICOLOGICO', 'F80', 'F81', 'F82', 'F83', 'F84', 'F88', 'F89']],
                ['id' => '413', 'code' => 'F90-F98', 'label' => 'TRASTORNO EMOCIONALES Y DEL COMPORTAMIENTO QUE APARECEN HABITUALMENTE EN LA NIÑEZ Y LA ADOLESCENCIA.', 'diag' => [
                    'TRASTORNO EMOCIONALES Y DEL COMPORTAMIENTO QUE APARECEN HABITUALMENTE EN LA NIÑEZ Y LA ADOLESCENCIA.',
                    'TRASTORNOS EMOCIONALES Y DEL COMPORTAMIENTO QUE APARECEN HABITUALMENTE EN LA NIÑEZ Y LA ADOLESCENCIA',
                    'TRASTORNO EMOCIONALES Y DEL COMPORTAMIENTO',
                    'TRASTORNOS EMOCIONALES Y DEL COMPORTAMIENTO',
                    'SINDROME HIPERCINETICO DE LA NIÑEZ',
                    'SINDROME HIPERSINETICO DE LA NIÑEZ',
                    'SINDROME HIPERCINETICO',
                    'SINDROME HIPERSINETICO',
                    'TDAH',
                    'HIPERACTIVIDAD',
                    'DEFICIT DE ATENCION',
                    'TRASTORNO POR DEFICIT DE ATENCION',
                    'TRASTORNO DE LA CONDUCTA',
                    'TRASTORNO DISOCIAL',
                    'TRASTORNO DE ANSIEDAD POR SEPARACION DE LA NIÑEZ',
                    'FOBIAS EN LA NIÑEZ',
                    'MUTISMO SELECTIVO',
                    'TRASTORNO DE TICS',
                    'SINDROME DE TOURETTE',
                    'TOURETTE',
                    'ENURESIS',
                    'ENCOPRESIS',
                    'TARTAMUDEZ',
                    'ESPASMOFEMIA',
                    '43', '413',
                    'F90', 'F90.0', 'F90.1', 'F90.8', 'F90.9',
                    'F91', 'F91.0', 'F91.1', 'F91.2', 'F91.3', 'F91.8', 'F91.9',
                    'F92', 'F93', 'F94', 'F95', 'F98'
                ]],
                ['id' => '414', 'code' => 'F99', 'label' => 'TRASTORNO MENTAL SIN ESPECIFICACIÓN', 'diag' => ['TRASTORNO MENTAL SIN ESPECIFICACION', 'F99']],
                ['id' => '415', 'code' => 'G40', 'label' => 'EPILEPSIA', 'diag' => ['EPILEPSIA', 'G40']],
                ['id' => '416', 'code' => 'G41', 'label' => 'ESTADO DE MAL EPILÉPTICO (STATUS) GRAN MAL, Y PEQUEÑO MAL, PARCIAL, COMPLEJO, OTROS, DE TIPO NO ESPECIFICADO, Y SE AGREGA ENTRE PARENTESIS ESTATUS.', 'diag' => ['ESTADO DE MAL EPILEPTICO', 'STATUS', 'G41']],
                ['id' => '417', 'code' => 'X40-X49', 'label' => 'OTROS SINTOMAS QUE INVOLUCRAN EL ESTADO EMOCIONAL IDEACION SUICIDA/TENDENCIA. ENVENENAMIENTO ACCIDENTAL POR, Y EXPOSICIÓN A SUSTANCIAS NOCIVAS (X40-X49)', 'diag' => ['IDEACION SUICIDA', 'TENDENCIA SUICIDA', 'ENVENENAMIENTO ACCIDENTAL', 'X40', 'X41', 'X42', 'X43', 'X44', 'X45', 'X46', 'X47', 'X48', 'X49']],
            ];
        } else {
            return [
                ['id' => '418', 'code' => 'X40-X49', 'label' => 'ENVENENAMIENTO O LESIÓN INTENSIONAL-ENVENENAMIENTO ACCIDENTAL POR, Y EXPOSICIÓN A SUSTANCIAS NOCIVAS.', 'diag' => ['ENVENENAMIENTO O LESION INTENSIONAL', 'ENVENENAMIENTO ACCIDENTAL', 'X40', 'X41', 'X42', 'X43', 'X44', 'X45', 'X46', 'X47', 'X48', 'X49'], 'seccion' => 'CAUSAS EXTERNAS'],
                ['id' => '419', 'code' => 'X60-X69', 'label' => 'ENVENENAMIENTO O LESIÓN INTERNACIONALMENTE AUTOINFLINGIDA, INTENTO DE SUICIDIO POR DIFERENTES MEDIOS.', 'diag' => ['INTENTO DE SUICIDIO', 'AUTOINFLINGIDA', 'X60', 'X61', 'X62', 'X63', 'X64', 'X65', 'X66', 'X67', 'X68', 'X69'], 'seccion' => 'CAUSAS EXTERNAS'],
                ['id' => '420', 'code' => 'X70-X84', 'label' => 'SUICIDIO Y LESIÓN INTERNACIONALMENTE AUTOINFLINGIDA POR DIFERENTES MEDIOS.', 'diag' => ['SUICIDIO', 'AUTOINFLINGIDA', 'X70', 'X71', 'X72', 'X73', 'X74', 'X75', 'X76', 'X77', 'X78', 'X79', 'X80', 'X81', 'X82', 'X83', 'X84'], 'seccion' => 'CAUSAS EXTERNAS'],
                ['id' => '421', 'code' => 'X85-Y09', 'label' => 'INCLUYE: HOMICIDIO. LESIONES OCASIONADAS POR OTRA PERSONA CON INTENTO DE LESIONAR O MATAR (INCLUYE HOMICIDIO)', 'diag' => ['HOMICIDIO', 'AGRESION', 'LESIONES OCASIONADAS POR OTRA PERSONA', 'X85', 'X86', 'X87', 'X88', 'X89', 'X90', 'X91', 'X92', 'X93', 'X94', 'X95', 'X96', 'X97', 'X98', 'X99', 'Y00', 'Y01', 'Y02', 'Y03', 'Y04', 'Y05', 'Y06', 'Y07', 'Y08', 'Y09'], 'seccion' => 'CAUSAS EXTERNAS'],
                ['id' => '422', 'code' => 'Y07', 'label' => 'SINDROME DE MALTRATO POR ESPOSO O PAREJA (QUIEN EJERCE ABUSO FISICO, SEXUAL, CRUELDAD MENTAL Y TORTURA)', 'diag' => ['SINDROME DE MALTRATO POR ESPOSO O PAREJA', 'MALTRATO POR PAREJA', 'ABUSO POR PAREJA', 'ABUSO FISICO PAREJA'], 'seccion' => 'MALTRATO'],
                ['id' => '423', 'code' => 'T74.0', 'label' => 'NEGLIGENCIA O ABANDONO (NIÑOS Y NIÑAS)', 'diag' => ['NEGLIGENCIA', 'ABANDONO', 'T74.0'], 'seccion' => 'MALTRATO'],
                ['id' => '424', 'code' => 'T74.1', 'label' => 'ABUSO FÍSICO', 'diag' => ['ABUSO FISICO', 'T74.1'], 'seccion' => 'MALTRATO'],
                ['id' => '425', 'code' => 'T74.2', 'label' => 'ABUSO SEXUAL', 'diag' => ['ABUSO SEXUAL', 'T74.2'], 'seccion' => 'MALTRATO'],
                ['id' => '426', 'code' => 'T74.3', 'label' => 'ABUSO PSICOLÓGICO', 'diag' => ['ABUSO PSICOLOGICO', 'T74.3'], 'seccion' => 'MALTRATO'],
                ['id' => '427', 'code' => '', 'label' => 'VIOLENCIA FÍSICA', 'diag' => ['VIOLENCIA FISICA'], 'seccion' => 'VIOLENCIA INTRAFAMILIAR'],
                ['id' => '428', 'code' => '', 'label' => 'VIOLENCIA SEXUAL', 'diag' => ['VIOLENCIA SEXUAL'], 'seccion' => 'VIOLENCIA INTRAFAMILIAR'],
                ['id' => '429', 'code' => '', 'label' => 'VIOLENCIA PSICOLÓGICA', 'diag' => ['VIOLENCIA PSICOLOGICA'], 'seccion' => 'VIOLENCIA INTRAFAMILIAR'],
                ['id' => '430', 'code' => '', 'label' => 'VIOLENCIA PATRIMONIAL / ECONÓMICA.', 'diag' => ['VIOLENCIA PATRIMONIAL', 'VIOLENCIA ECONOMICA'], 'seccion' => 'VIOLENCIA INTRAFAMILIAR'],
                ['id' => '431', 'code' => 'Z20-Z22', 'label' => 'CONTACTO CON Y EXPOSICIÓN A ENFERMEDADES TRANSMISIBLES (RELACIONADO A VIH, TB Y OTRAS TRANSMISIBLES)', 'diag' => ['ENFERMEDADES TRANSMISIBLES', 'VIH', 'TB', 'TUBERCULOSIS', 'CONTACTO CON Y EXPOSICION', 'Z20', 'Z21', 'Z22'], 'seccion' => 'PROBLEMAS SOCIALES Y OTROS'],
                ['id' => '432', 'code' => 'Z55-Z65', 'label' => 'PERSONAS CON RIESGO POTENCIAL PARA SU SALUD RELACIONADO CON CIRCUNSTANCIAS SOCIOECONOMICAS Y PSICOSOCIALES', 'diag' => ['RIESGO POTENCIAL PARA SU SALUD', 'CIRCUNSTANCIAS SOCIOECONOMICAS', 'Z55', 'Z56', 'Z57', 'Z59', 'Z60', 'Z61', 'Z62', 'Z63', 'Z64', 'Z65'], 'seccion' => 'PROBLEMAS SOCIALES Y OTROS'],
                ['id' => '433', 'code' => 'Z63', 'label' => 'OTROS PROBLEMAS RELACIONADOS CON EL GRUPO PRIMARIO DE APOYO', 'diag' => ['OTROS PROBLEMAS RELACIONADOS CON EL GRUPO PRIMARIO DE APOYO', 'Z63'], 'seccion' => 'PROBLEMAS SOCIALES Y OTROS'],
                ['id' => '434', 'code' => 'Z70', 'label' => 'CONSULTA RELACIONADA CON ACTITUD, CONDUCTA U ORIENTACION SEXUAL', 'diag' => ['ACTITUD, CONDUCTA U ORIENTACION SEXUAL', 'ORIENTACION SEXUAL', 'Z70'], 'seccion' => 'PROBLEMAS SOCIALES Y OTROS'],
            ];
        }
    }

    private function normalizeCod($cod)
    {
        return strtoupper(trim($cod ?? ''));
    }





    private function quitarAcentos($cadena)
    {
        $buscar = array('Á', 'É', 'Í', 'Ó', 'Ú', 'Ü', 'Ñ', 'á', 'é', 'í', 'ó', 'ú', 'ü', 'ñ');
        $reemplazar = array('A', 'E', 'I', 'O', 'U', 'U', 'N', 'A', 'E', 'I', 'O', 'U', 'U', 'N');
        return str_replace($buscar, $reemplazar, $cadena);
    }

    public function export(Request $request)
    {
        // Implementación futura de exportación
        return back()->with('error', 'Exportación no implementada aún');
    }
}
