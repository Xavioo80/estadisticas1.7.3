<?php

namespace App\Http\Controllers\Informes;

use App\Http\Controllers\Controller;
use App\Models\RegistroGlobal;
use App\Models\Setting;
use App\Traits\InformesHelperTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class At2rNController extends Controller
{
    use InformesHelperTrait;

    public function __construct(private \App\Services\RegistroGlobalService $service)
    {
    }

    public function index(Request $request)
    {
        $data = $this->getAnosMesesDisponiblesInformes();
        $anos = $data['anos'];
        $meses = $data['meses'];

        $ano = $request->input('ano', $data['anoDefault']);
        $mes = $request->input('mes', '');

        if (empty($mes)) {
            $mes = $this->resolverMesPorDefecto($ano, true); // true = usar RegistroGlobal
        }

        $jornada = $request->input('jornada', 'TODAS');
        $profFilter = $request->input('prof', 'TODAS');
        $medicoFilter = $request->input('medico', 'TODOS');
        $sexoFilter = $request->input('sexo', 'AMBOS');

        $jornadas = $this->getJornadasDisponibles();
        $profesiones = $this->getProfesionesDisponibles();

        $nombresMedicos = RegistroGlobal::distinct()
            ->where('ano', $ano)->where('mes', $mes)
            ->whereNotNull('medico')->where('medico', '!=', '')
            ->orderBy('medico')->pluck('medico');

        $mapping = [
            'ENFERMERAS AUXILIARES' => 1,
            'LICENCIADA EN ENFERMERIA' => 2,
            'LICENCIADAS EN ENFERMERIA' => 2,
            'ENFERMERA PROFESIONAL' => 2,
            'NUTRICION' => 2,
            'NUTRICIÓN' => 2,
            'LICENCIADA EN NUTRICION' => 2,
            'LICENCIADAS EN NUTRICION' => 2,
            'LICENCIADA EN NUTRICIÓN' => 2,
            'LICENCIADAS EN NUTRICIÓN' => 2,
            'NUTRICIONISTA' => 2,
            'PSICOLOGIA' => 2,
            'PSICOLOGÍA' => 2,
            'PSICOLOGO' => 2,
            'PSICÓLOGO' => 2,
            'CONSEJERIA' => 2,
            'CONSEJERÍA' => 2,
            'SALUD MENTAL' => 2,
            'MEDICO GENERAL' => 3,
            'MÉDICO GENERAL' => 3,
            'MEDICO ESPECIALISTA' => 4,
            'MÉDICO ESPECIALISTA' => 4,
            'PSIQUIATRA' => 4,
            'PSIQUIATRIA' => 4,
            'PSIQUIATRÍA' => 4,
        ];
        $omitir = ['TRABAJO SOCIAL', 'ODONTOLOGIA'];

        $baseQuery = RegistroGlobal::query()->where('ano', $ano)->where('mes', $mes);
        if ($jornada !== 'TODAS')
            $baseQuery->where('jornada', $jornada);
        if ($profFilter !== 'TODAS')
            $baseQuery->where('prof', $profFilter);
        if ($medicoFilter !== 'TODOS')
            $baseQuery->where('medico', $medicoFilter);
        if ($sexoFilter !== 'AMBOS')
            $baseQuery->where('sexo', $sexoFilter);

        $registrosRaw = (clone $baseQuery)->select(
            'id',
            'medico',
            'prof',
            'edad',
            'tipo',
            'rango',
            'cond',
            'sexo',
            'referido_de',
            'cod_1',
            'diagnostico_1',
            'cond_1',
            'cod_2',
            'diagnostico_2',
            'cond_2',
            'cod_3',
            'diagnostico_3',
            'cond_3',
            'cod_4',
            'diagnostico_4',
            'cond_4',
            'cod_5',
            'diagnostico_5',
            'cond_5',
            'cod_6',
            'diagnostico_6',
            'cond_6',
            'cod_7',
            'diagnostico_7',
            'cond_7'
        )->get();

        $atencionesRaw = $registrosRaw;

        $colCache = [];
        $getCol = function ($prof, $medico = '', $force = false) use (&$colCache) {
            $key = ($prof ?? '') . '|' . ($medico ?? '') . '|' . ($force ? '1' : '0');
            if (!isset($colCache[$key])) {
                $colCache[$key] = $this->resolveColumnaProfesion($prof, $medico, $force);
            }
            return $colCache[$key];
        };

        $diagnosticosRaw = collect();
        foreach ($registrosRaw as $reg) {
            for ($i = 1; $i <= 7; $i++) {
                $cod = $reg->{"cod_{$i}"} ?? null;
                $diag = $reg->{"diagnostico_{$i}"} ?? '';
                if ((($cod === null || trim((string) $cod) === '')) && trim((string) $diag) === '')
                    continue;
                $condVal = strtoupper(trim((string)($reg->{"cond_{$i}"} ?? '')));
                if ($condVal !== 'N' && $condVal !== 'S') {
                    $condVal = strtoupper(trim((string)($reg->cond ?? '')));
                    if ($condVal !== 'N' && $condVal !== 'S') {
                        $condVal = 'N';
                    }
                }
                $diagnosticosRaw->push((object) [
                    'reg_id' => $reg->id, // Guardamos el ID original
                    'cod' => trim((string) $cod),
                    'diagnostico' => $diag,
                    'cond_diag' => $condVal,
                    'prof' => $reg->prof,
                    'medico' => $reg->medico ?? '',
                    'edad' => $reg->edad,
                    'tipo' => $reg->tipo,
                    'sexo' => $reg->sexo,
                ]);
            }
        }

        $results = [];
        $ageRows = $this->getAgeRows();
        $progRows = $this->getProgRows($ano, $mes, $jornada, $profFilter, $medicoFilter, $sexoFilter);

        // Preload manual settings in 1 single query
        $manualSettings = Setting::where('key', 'like', "at2rn_manual_%_{$ano}_{$mes}_%")->pluck('value', 'key')->toArray();

        // ── Procesar ageRows ───────────────────────────────────────────────
        foreach ($ageRows as $idx => $def) {
            $results[$idx] = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
            if (isset($def['match'])) {
                foreach ($atencionesRaw as $r) {
                    $col = $getCol($r->prof, $r->medico ?? '');
                    if ($col && $def['match']($r))
                        $results[$idx][$col]++;
                }
            }
        }
        foreach ($ageRows as $idx => $def) {
            if (isset($def['sum'])) {
                foreach ($def['sum'] as $srcIdx) {
                    for ($c = 1; $c <= 4; $c++)
                        $results[$idx][$c] += $results[$srcIdx][$c];
                }
            }
        }
        foreach ($ageRows as $idx => $def) {
            if (isset($def['diff'])) {
                $baseIdx = $def['diff'][0];
                for ($c = 1; $c <= 4; $c++) {
                    $val = $results[$baseIdx][$c] ?? 0;
                    for ($i = 1; $i < count($def['diff']); $i++) {
                        $srcIdx = $def['diff'][$i];
                        if (isset($results[$srcIdx])) {
                            $val -= $results[$srcIdx][$c];
                        }
                    }
                    $results[$idx][$c] = max(0, $val);
                }
            }
        }

        // ── Procesar progRows ──────────────────────────────────────────────
        $startIdx = count($ageRows);
        foreach ($progRows as $pIdx => $def) {
            $absIdx = $startIdx + $pIdx;
            $results[$absIdx] = [1 => 0, 2 => 0, 3 => 0, 4 => 0];

            if (isset($def['match'])) {
                $force = !empty($def['force_col']);
                $countedInRow = []; // Para no contar el mismo ID dos veces en la misma fila
                foreach ($diagnosticosRaw as $r) {
                    if (isset($countedInRow[$r->reg_id]))
                        continue;
                    $col = $getCol($r->prof, $r->medico ?? '', $force);
                    if ($col && $def['match']($r)) {
                        $results[$absIdx][$col]++;
                        $countedInRow[$r->reg_id] = true;
                    }
                }
            } elseif (isset($def['match_atencion'])) {
                $force = !empty($def['force_col']);
                // Conteo basado solo en la atención/profesión (ej. Psicología)
                foreach ($atencionesRaw as $r) {
                    $col = $getCol($r->prof, $r->medico ?? '', $force);
                    if ($col && !isset($countedInRow[$absIdx][$r->id]) && $def['match_atencion']($r)) {
                        $results[$absIdx][$col]++;
                        $countedInRow[$absIdx][$r->id] = true;
                    }
                }
            } elseif (!empty($def['is_manual'])) {
                $manualKey = $def['manual_key'] ?? 'rehidratados';
                $settingKey = "at2rn_manual_{$manualKey}_{$ano}_{$mes}_{$jornada}_{$profFilter}_{$medicoFilter}_{$sexoFilter}";
                $valStr = $manualSettings[$settingKey] ?? null;
                if ($valStr) {
                    $manualVals = json_decode($valStr, true);
                    $results[$absIdx][1] = $manualVals[1] ?? 0;
                    $results[$absIdx][2] = $manualVals[2] ?? 0;
                    $results[$absIdx][3] = $manualVals[3] ?? 0;
                    $results[$absIdx][4] = $manualVals[4] ?? 0;
                }
            } elseif (isset($def['code']) || isset($def['diag'])) {
                $force = !empty($def['force_col']);
                $countedInRow = [];
                foreach ($diagnosticosRaw as $r) {
                    if (isset($countedInRow[$r->reg_id]))
                        continue;
                    $col = $getCol($r->prof, $r->medico ?? '', $force);
                    if (!$col)
                        continue;

                    $match = false;
                    if (isset($def['diag'])) {
                        $diags = is_array($def['diag']) ? $def['diag'] : [$def['diag']];
                        $rDiag = strtoupper(trim($r->diagnostico ?? ''));
                        if (in_array($rDiag, array_map('strtoupper', $diags)))
                            $match = true;
                    } elseif (isset($def['code'])) {
                        $codes = is_array($def['code']) ? $def['code'] : [$def['code']];
                        if (in_array(trim($r->cod ?? ''), $codes))
                            $match = true;
                    }

                    if ($match) {
                        if (isset($def['cond'])) {
                            $rCond = strtoupper(trim($r->cond_diag ?? ''));
                            if ($rCond !== strtoupper($def['cond']))
                                $match = false;
                        }
                        if ($match && isset($def['age_max'])) {
                            if (strtoupper(trim($r->tipo)) == 'A' && $r->edad > $def['age_max'])
                                $match = false;
                        }
                        if ($match) {
                            $results[$absIdx][$col]++;
                            $countedInRow[$r->reg_id] = true;
                        }
                    }
                }
            }
        }

        // ── Recalcular sumas / diff / pct de progRows ──────────────────────
        foreach ($progRows as $pIdx => $def) {
            $absIdx = $startIdx + $pIdx;
            if (isset($def['sum_rows'])) {
                foreach ($def['sum_rows'] as $srcIdx) {
                    if (isset($results[$srcIdx])) {
                        for ($c = 1; $c <= 4; $c++)
                            $results[$absIdx][$c] += $results[$srcIdx][$c];
                    }
                }
            } elseif (isset($def['diff'])) {
                $baseIdx = $def['diff'][0];
                for ($c = 1; $c <= 4; $c++) {
                    $val = $results[$baseIdx][$c] ?? 0;
                    for ($i = 1; $i < count($def['diff']); $i++) {
                        $srcIdx = $def['diff'][$i];
                        if (isset($results[$srcIdx]))
                            $val -= $results[$srcIdx][$c];
                    }
                    $results[$absIdx][$c] = max(0, $val);
                }
            } elseif (isset($def['pct'])) {
                $numIdx = $def['pct'][0];
                $denIdx = $def['pct'][1];
                if (isset($results[$numIdx]) && isset($results[$denIdx])) {
                    for ($c = 1; $c <= 4; $c++) {
                        $den = $results[$denIdx][$c];
                        $results[$absIdx][$c] = $den > 0 ? round(($results[$numIdx][$c] / $den) * 100, 2) : 0;
                    }
                }
            }
        }

        // ── Ajuste de cuadre exacto para "Número de atencion de adolescentes de 10 a 19 años mujeres" ──
        $idxPrenatalAdol = null;
        $idxMujeresAdol = null;
        foreach ($progRows as $pIdx => $def) {
            $absIdx = $startIdx + $pIdx;
            if ($def['label'] === 'Atención Prenatal NUEVA en las edades de 10 a 19 años (Adolescentes)') {
                $idxPrenatalAdol = $absIdx;
            }
            if ($def['label'] === 'Número de atencion de adolescentes de 10 a 19 años mujeres') {
                $idxMujeresAdol = $absIdx;
            }
        }

        if ($idxPrenatalAdol !== null && $idxMujeresAdol !== null) {
            $totalMujeres1019 = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
            foreach ($atencionesRaw as $r) {
                $col = $getCol($r->prof, $r->medico ?? '');
                if ($col && $r->tipo === 'A' && $r->edad >= 10 && $r->edad <= 19 && strtoupper(trim($r->sexo ?? '')) === 'M') {
                    $totalMujeres1019[$col]++;
                }
            }
            for ($c = 1; $c <= 4; $c++) {
                $results[$idxMujeresAdol][$c] = max(0, $totalMujeres1019[$c] - $results[$idxPrenatalAdol][$c]);
            }
        }

        // ── Obtener referencia de Morbilidad para comprobación ────────────
        $morbRef = $this->getMorbilidadReferenceData($ano, $mes, $jornada);
        $morbMap = [
            'No. De niños/as menores de 5 años con diarrea nuevo' => ['val' => $morbRef['diarreas_nuevas'], 'key' => 'diarreas_nuevas'],
            'No. De niños/as menores de 5 años con diarrea que acuden a cita de seguimiento' => ['val' => $morbRef['diarreas_subs'], 'key' => 'diarreas_subs'],
            'No. De Niños/as menores de 5 años con casos de Neumonía nuevos en el año' => ['val' => $morbRef['neumonias_nuevas'], 'key' => 'neumonias_nuevas'],
            'No. De Niños/as menores de 5 años con Neumonía que acuden a su cita de Seguimiento' => ['val' => $morbRef['neumonias_subs'], 'key' => 'neumonias_subs'],
            'No. de niños/as menores de 5 años con algun grado de Síndrome anémico diagnosticado' => ['val' => $morbRef['anemias_nuevas'], 'key' => 'anemias_nuevas'],
            'Número de atenciones brindadas Nuevas de Diabetes Mellitus' => ['val' => $morbRef['diabetes_nuevas'], 'key' => 'diabetes_nuevas'],
            'Número de atenciones brindadas Subsiguientes de Diabetes Mellitus' => ['val' => $morbRef['diabetes_subs'], 'key' => 'diabetes_subs'],
            'Número de atenciones brindadas Nuevas de Hipertensión Arterial' => ['val' => $morbRef['hta_nuevas'], 'key' => 'hta_nuevas'],
            'Número de atenciones brindadas Subsiguientes de Hipertensión Arterial' => ['val' => $morbRef['hta_subs'], 'key' => 'hta_subs'],
            'Número de atenciones brindadas Nuevas de Enfermedad Renal Crónica' => ['val' => $morbRef['erc_nuevas'], 'key' => 'erc_nuevas'],
            'Número de atenciones brindadas Subsiguientes de Enfermedad Renal Crónica' => ['val' => $morbRef['erc_subs'], 'key' => 'erc_subs'],
        ];

        // ── Agrupación de Atenciones por Rangos de Edad en columna Morbilidad ──
        $colSum = function($indices) use ($results) {
            $cols = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
            foreach ($indices as $i) {
                if (isset($results[$i])) {
                    for ($c = 1; $c <= 4; $c++) {
                        $cols[$c] += $results[$i][$c] ?? 0;
                    }
                }
            }
            return $cols;
        };

        // Menores de 5 años: rows 0, 2, 4 (Nuevas) y 1, 3, 5 (Subsiguientes)
        $m5N_cols = $colSum([0, 2, 4]);
        $m5S_cols = $colSum([1, 3, 5]);
        $m5N = array_sum($m5N_cols);
        $m5S = array_sum($m5S_cols);
        $m5Tot = $m5N + $m5S;

        // 5-9 años: row 6 (N), row 7 (S)
        $r59N_cols = $colSum([6]);
        $r59S_cols = $colSum([7]);
        $r59N = array_sum($r59N_cols);
        $r59S = array_sum($r59S_cols);
        $r59Tot = $r59N + $r59S;

        // 10-14 años: row 8 (N), row 9 (S)
        $r1014N_cols = $colSum([8]);
        $r1014S_cols = $colSum([9]);
        $r1014N = array_sum($r1014N_cols);
        $r1014S = array_sum($r1014S_cols);
        $r1014Tot = $r1014N + $r1014S;

        // 15-19 años: row 10 (N), row 11 (S)
        $r1519N_cols = $colSum([10]);
        $r1519S_cols = $colSum([11]);
        $r1519N = array_sum($r1519N_cols);
        $r1519S = array_sum($r1519S_cols);
        $r1519Tot = $r1519N + $r1519S;

        // 20-49 años: row 12 (N), row 13 (S)
        $r2049N_cols = $colSum([12]);
        $r2049S_cols = $colSum([13]);
        $r2049N = array_sum($r2049N_cols);
        $r2049S = array_sum($r2049S_cols);
        $r2049Tot = $r2049N + $r2049S;

        // 50-59 años: row 14 (N), row 15 (S)
        $r5059N_cols = $colSum([14]);
        $r5059S_cols = $colSum([15]);
        $r5059N = array_sum($r5059N_cols);
        $r5059S = array_sum($r5059S_cols);
        $r5059Tot = $r5059N + $r5059S;

        // 60 y más: row 16 (N), row 17 (S)
        $r60N_cols = $colSum([16]);
        $r60S_cols = $colSum([17]);
        $r60N = array_sum($r60N_cols);
        $r60S = array_sum($r60S_cols);
        $r60Tot = $r60N + $r60S;

        // Total Atendidos: row 18
        $totAtendN = $m5N + $r59N + $r1014N + $r1519N + $r2049N + $r5059N + $r60N;
        $totAtendS = $m5S + $r59S + $r1014S + $r1519S + $r2049S + $r5059S + $r60S;
        $totAtendTot = $totAtendN + $totAtendS;

        $fmtTooltip = function($nCols, $sCols) {
            return "Nuevas: Aux: {$nCols[1]}, Prof: {$nCols[2]}, Méd: {$nCols[3]}, Esp: {$nCols[4]} | Subsig: Aux: {$sCols[1]}, Prof: {$sCols[2]}, Méd: {$sCols[3]}, Esp: {$sCols[4]}";
        };

        $morbGroupMap = [
            0 => ['rowspan' => 6, 'title' => '< 5 AÑOS', 'nuevas' => $m5N, 'subs' => $m5S, 'total' => $m5Tot, 'is_menores5' => true, 'tooltip' => $fmtTooltip($m5N_cols, $m5S_cols)],
            1 => ['skip' => true],
            2 => ['skip' => true],
            3 => ['skip' => true],
            4 => ['skip' => true],
            5 => ['skip' => true],
            6 => ['rowspan' => 2, 'title' => '5 A 9 AÑOS', 'nuevas' => $r59N, 'subs' => $r59S, 'total' => $r59Tot, 'tooltip' => $fmtTooltip($r59N_cols, $r59S_cols)],
            7 => ['skip' => true],
            8 => ['rowspan' => 2, 'title' => '10 A 14 AÑOS', 'nuevas' => $r1014N, 'subs' => $r1014S, 'total' => $r1014Tot, 'tooltip' => $fmtTooltip($r1014N_cols, $r1014S_cols)],
            9 => ['skip' => true],
            10 => ['rowspan' => 2, 'title' => '15 A 19 AÑOS', 'nuevas' => $r1519N, 'subs' => $r1519S, 'total' => $r1519Tot, 'tooltip' => $fmtTooltip($r1519N_cols, $r1519S_cols)],
            11 => ['skip' => true],
            12 => ['rowspan' => 2, 'title' => '20 A 49 AÑOS', 'nuevas' => $r2049N, 'subs' => $r2049S, 'total' => $r2049Tot, 'tooltip' => $fmtTooltip($r2049N_cols, $r2049S_cols)],
            13 => ['skip' => true],
            14 => ['rowspan' => 2, 'title' => '50 A 59 AÑOS', 'nuevas' => $r5059N, 'subs' => $r5059S, 'total' => $r5059Tot, 'tooltip' => $fmtTooltip($r5059N_cols, $r5059S_cols)],
            15 => ['skip' => true],
            16 => ['rowspan' => 2, 'title' => '60 Y MÁS', 'nuevas' => $r60N, 'subs' => $r60S, 'total' => $r60Tot, 'tooltip' => $fmtTooltip($r60N_cols, $r60S_cols)],
            17 => ['skip' => true],
            18 => ['rowspan' => 1, 'title' => 'TOTAL ATENDIDOS', 'nuevas' => $totAtendN, 'subs' => $totAtendS, 'total' => $totAtendTot, 'is_total' => true],
        ];

        // ── Formatear datos finales ────────────────────────────────────────
        $allRows = array_merge($ageRows, $progRows);
        $finalData = [];
        foreach ($allRows as $idx => $row) {
            $label = $row['label'];
            $finalData[] = [
                'label' => $label,
                'cols' => $results[$idx],
                'total' => array_sum($results[$idx]),
                'color' => $row['color'] ?? '',
                'hidden' => $row['hidden'] ?? false,
                'header' => $row['header'] ?? false,
                'is_manual' => $row['is_manual'] ?? false,
                'manual_key' => $row['manual_key'] ?? null,
                'morbilidad_val' => isset($morbMap[$label]) ? $morbMap[$label]['val'] : null,
                'morbilidad_key' => isset($morbMap[$label]) ? $morbMap[$label]['key'] : null,
                'morb_group' => $morbGroupMap[$idx] ?? null,
            ];
        }

        $view = $request->ajax() ? 'informes.at2r_n_content' : 'informes.at2r_n';
        return response()->view($view, compact(
            'anos',
            'meses',
            'jornadas',
            'nombresMedicos',
            'profesiones',
            'ano',
            'mes',
            'jornada',
            'profFilter',
            'medicoFilter',
            'sexoFilter',
            'finalData'
        ))->header('Cache-Control', 'no-cache, no-store, must-revalidate')
          ->header('Pragma', 'no-cache')
          ->header('Expires', '0');
    }

    public function audit(Request $request)
    {
        $ano = $request->input('ano', date('Y'));
        $mes = $request->input('mes', date('m'));
        $jornada = $request->input('jornada', 'TODAS');
        $profFilter = $request->input('prof', 'TODAS');
        $medicoFilter = $request->input('medico', 'TODOS');
        $sexoFilter = $request->input('sexo', 'AMBOS');

        $mapping = [
            'ENFERMERAS AUXILIARES' => 1,
            'LICENCIADA EN ENFERMERIA' => 2,
            'LICENCIADAS EN ENFERMERIA' => 2,
            'ENFERMERA PROFESIONAL' => 2,
            'NUTRICION' => 2,
            'NUTRICIÓN' => 2,
            'LICENCIADA EN NUTRICION' => 2,
            'LICENCIADAS EN NUTRICION' => 2,
            'LICENCIADA EN NUTRICIÓN' => 2,
            'LICENCIADAS EN NUTRICIÓN' => 2,
            'NUTRICIONISTA' => 2,
            'PSICOLOGIA' => 2,
            'PSICOLOGÍA' => 2,
            'CONSEJERIA' => 2,
            'CONSEJERÍA' => 2,
            'SALUD MENTAL' => 2,
            'MEDICO GENERAL' => 3,
        ];
        $omitir = ['TRABAJO SOCIAL', 'ODONTOLOGIA'];

        $baseQuery = RegistroGlobal::query()->where('ano', $ano)->where('mes', $mes);
        if ($jornada !== 'TODAS') $baseQuery->where('jornada', $jornada);
        if ($profFilter !== 'TODAS') $baseQuery->where('prof', $profFilter);
        if ($medicoFilter !== 'TODOS') $baseQuery->where('medico', $medicoFilter);
        if ($sexoFilter !== 'AMBOS') $baseQuery->where('sexo', $sexoFilter);

        $registrosRaw = $baseQuery->get();
        $ageRows = $this->getAgeRows();
        
        // Solo usamos las filas de desglose (no los totales) para validar la clasificación por edad
        $breakdownRows = array_filter($ageRows, function($row) {
            return !($row['isTotal'] ?? false) && !($row['isSexTotal'] ?? false);
        });

        $discrepancias = [];
        foreach ($registrosRaw as $r) {
            // 1. Verificar si está omitido por profesión
            $prof = strtoupper(trim($r->prof));
            $isOmitted = in_array($prof, $omitir);

            // 2. Verificar si coincide con algún rango de edad y condición (N/S)
            $matchedAge = false;
            foreach ($breakdownRows as $def) {
                if (isset($def['match']) && $def['match']($r)) {
                    $matchedAge = true;
                    break;
                }
            }

            // 3. Verificar datos críticos faltantes
            $missingData = [];
            if (empty($r->cond) || !in_array(strtoupper($r->cond), ['N', 'S'])) $missingData[] = 'Condición (N/S) faltante o inválida';
            if (empty($r->sexo) || !in_array(strtoupper($r->sexo), ['H', 'M'])) $missingData[] = 'Sexo (H/M) faltante';
            if (empty($r->jornada)) $missingData[] = 'Jornada faltante';

            if ($isOmitted || !$matchedAge || !empty($missingData)) {
                $razon = '';
                if ($isOmitted) $razon = 'Profesión excluida del AT2r-N. ';
                if (!$matchedAge && empty($missingData)) $razon .= 'Edad/Tipo no coinciden con categorías del informe. ';
                if (!empty($missingData)) $razon .= implode(', ', $missingData) . '. ';

                $discrepancias[] = [
                    'id' => $r->id,
                    'fecha' => $r->fecha,
                    'prof' => $r->prof,
                    'medico' => $r->medico,
                    'edad' => $r->edad,
                    'tipo' => $r->tipo,
                    'sexo' => $r->sexo,
                    'cond' => $r->cond,
                    'diag' => $r->diagnostico_1,
                    'razon' => trim($razon)
                ];
            }
        }

        return view('informes.at2r_n_audit', compact('discrepancias', 'ano', 'mes', 'jornada', 'profFilter', 'medicoFilter', 'sexoFilter'));
    }

    public function cellDetails(Request $request)
    {
        $ano = $request->input('ano', date('Y'));
        $mes = $request->input('mes', '');
        $jornada = $request->input('jornada', 'TODAS');
        $profFilter = $request->input('prof', 'TODAS');
        $medicoFilter = $request->input('medico', 'TODOS');
        $sexoFilter = $request->input('sexo', 'AMBOS');
        $rowIdx = (int) $request->input('row_idx');
        $colIdx = (int) $request->input('col_idx');

        $ageRows = $this->getAgeRows();
        $progRows = $this->getProgRows($ano, $mes, $jornada, $profFilter, $medicoFilter, $sexoFilter);
        $allRows = array_merge($ageRows, $progRows);
        $targetRow = $allRows[$rowIdx] ?? null;

        if (!$targetRow) {
            return response()->json(['error' => 'Concepto no encontrado'], 404);
        }

        $mapping = [
            'ENFERMERAS AUXILIARES' => 1,
            'LICENCIADA EN ENFERMERIA' => 2,
            'LICENCIADAS EN ENFERMERIA' => 2,
            'ENFERMERA PROFESIONAL' => 2,
            'NUTRICION' => 2,
            'NUTRICIÓN' => 2,
            'LICENCIADA EN NUTRICION' => 2,
            'LICENCIADAS EN NUTRICION' => 2,
            'LICENCIADA EN NUTRICIÓN' => 2,
            'LICENCIADAS EN NUTRICIÓN' => 2,
            'NUTRICIONISTA' => 2,
            'PSICOLOGIA' => 2,
            'PSICOLOGÍA' => 2,
            'PSICOLOGO' => 2,
            'PSICÓLOGO' => 2,
            'CONSEJERIA' => 2,
            'CONSEJERÍA' => 2,
            'SALUD MENTAL' => 2,
            'MEDICO GENERAL' => 3,
            'MÉDICO GENERAL' => 3,
            'MEDICO ESPECIALISTA' => 4,
            'MÉDICO ESPECIALISTA' => 4,
            'PSIQUIATRA' => 4,
            'PSIQUIATRIA' => 4,
            'PSIQUIATRÍA' => 4,
        ];
        $omitir = ['TRABAJO SOCIAL', 'ODONTOLOGIA'];

        $getCol = function ($prof, $force = false) use ($mapping, $omitir) {
            $prof = strtoupper(trim($prof));
            if (isset($mapping[$prof]))
                return $mapping[$prof];
            if (str_contains($prof, 'NUTRICI'))
                return 2;
            if (str_contains($prof, 'PSICOLOG'))
                return 2;
            if (str_contains($prof, 'PSIQ'))
                return 4;
            if (!$force && in_array($prof, $omitir))
                return null;
            return 4;
        };

        $baseQuery = RegistroGlobal::query()->where('ano', $ano)->where('mes', $mes);
        if ($jornada !== 'TODAS') $baseQuery->where('jornada', $jornada);
        if ($profFilter !== 'TODAS') $baseQuery->where('prof', $profFilter);
        if ($medicoFilter !== 'TODOS') $baseQuery->where('medico', $medicoFilter);
        if ($sexoFilter !== 'AMBOS') $baseQuery->where('sexo', $sexoFilter);

        $registrosRaw = $baseQuery->get();

        $diagnosticosRaw = collect();
        foreach ($registrosRaw as $reg) {
            for ($i = 1; $i <= 7; $i++) {
                $cod = $reg->{"cod_{$i}"} ?? null;
                $diag = $reg->{"diagnostico_{$i}"} ?? '';
                if ((($cod === null || trim((string) $cod) === '')) && trim((string) $diag) === '')
                    continue;
                $diagnosticosRaw->push((object) [
                    'reg_id' => $reg->id,
                    'cod' => trim((string) $cod),
                    'diagnostico' => $reg->{"diagnostico_{$i}"} ?? '',
                    'cond_diag' => $reg->{"cond_{$i}"} ?? '',
                    'prof' => $reg->prof,
                    'medico' => $reg->medico,
                    'fecha' => $reg->fecha,
                    'edad' => $reg->edad,
                    'tipo' => $reg->tipo,
                    'sexo' => $reg->sexo,
                    'cond' => $reg->cond,
                    'original_record' => $reg,
                ]);
            }
        }

        $numAgeRows = count($ageRows);

        $resolveRecordsForRow = function($rIdx) use (&$resolveRecordsForRow, $allRows, $numAgeRows, $registrosRaw, $diagnosticosRaw, $getCol, $colIdx) {
            $targetRow = $allRows[$rIdx] ?? null;
            if (!$targetRow) return collect();

            $force = !empty($targetRow['force_col']);

            if (isset($targetRow['sum'])) {
                $matching = collect();
                foreach ($targetRow['sum'] as $srcIdx) {
                    $subRecs = $resolveRecordsForRow($srcIdx);
                    foreach ($subRecs as $rec) {
                        $matching->put($rec->id, $rec);
                    }
                }
                return $matching->values();
            }

            if (isset($targetRow['sum_rows'])) {
                $matching = collect();
                foreach ($targetRow['sum_rows'] as $srcIdx) {
                    $subRecs = $resolveRecordsForRow($srcIdx);
                    foreach ($subRecs as $rec) {
                        $matching->put($rec->id, $rec);
                    }
                }
                return $matching->values();
            }

            if (isset($targetRow['diff'])) {
                $baseIdx = $targetRow['diff'][0];
                $baseRecords = $resolveRecordsForRow($baseIdx);

                $excludedIds = [];
                $subCount = 0;
                for ($i = 1; $i < count($targetRow['diff']); $i++) {
                    $subIdx = $targetRow['diff'][$i];
                    $subRecs = $resolveRecordsForRow($subIdx);
                    $subCount += $subRecs->count();
                    foreach ($subRecs as $sRec) {
                        $excludedIds[$sRec->id] = true;
                    }
                }

                $filtered = $baseRecords->reject(function($rec) use ($excludedIds) {
                    return isset($excludedIds[$rec->id]);
                })->values();

                $expectedCount = max(0, $baseRecords->count() - $subCount);
                if ($filtered->count() > $expectedCount) {
                    $filtered = $filtered->take($expectedCount);
                }

                return $filtered;
            }

            $isProgRow = ($rIdx >= $numAgeRows);
            $recordsMap = collect();

            if (isset($targetRow['match'])) {
                if ($isProgRow) {
                    foreach ($diagnosticosRaw as $d) {
                        if ($recordsMap->has($d->reg_id)) continue;
                        $c = $getCol($d->prof, $force);
                        if ($c === $colIdx && $targetRow['match']($d)) {
                            $recordsMap->put($d->reg_id, $d->original_record);
                        }
                    }
                } else {
                    foreach ($registrosRaw as $r) {
                        $c = $getCol($r->prof, $force);
                        if ($c === $colIdx && $targetRow['match']($r)) {
                            $recordsMap->put($r->id, $r);
                        }
                    }
                }
            } elseif (isset($targetRow['match_atencion'])) {
                foreach ($registrosRaw as $r) {
                    $c = $getCol($r->prof, $force);
                    if ($c === $colIdx && $targetRow['match_atencion']($r)) {
                        $recordsMap->put($r->id, $r);
                    }
                }
            } elseif (isset($targetRow['code']) || isset($targetRow['diag'])) {
                foreach ($diagnosticosRaw as $d) {
                    if ($recordsMap->has($d->reg_id)) continue;
                    $c = $getCol($d->prof, $force);
                    if ($c !== $colIdx) continue;

                    $match = false;
                    if (isset($targetRow['diag'])) {
                        $diags = is_array($targetRow['diag']) ? $targetRow['diag'] : [$targetRow['diag']];
                        $rDiag = strtoupper(trim($d->diagnostico ?? ''));
                        if (in_array($rDiag, array_map('strtoupper', $diags))) {
                            $match = true;
                        }
                    } elseif (isset($targetRow['code'])) {
                        $codes = is_array($targetRow['code']) ? $targetRow['code'] : [$targetRow['code']];
                        if (in_array(trim($d->cod ?? ''), $codes)) {
                            $match = true;
                        }
                    }

                    if ($match && isset($targetRow['cond'])) {
                        $rCond = strtoupper(trim($d->cond_diag ?? ''));
                        if ($rCond !== strtoupper($targetRow['cond'])) {
                            $match = false;
                        }
                    }

                    if ($match && isset($targetRow['age_max'])) {
                        if (strtoupper(trim($d->tipo)) == 'A' && $d->edad > $targetRow['age_max']) {
                            $match = false;
                        }
                    }

                    if ($match) {
                        $recordsMap->put($d->reg_id, $d->original_record);
                    }
                }
            }

            return $recordsMap->values();
        };

        $matchingRecords = $resolveRecordsForRow($rowIdx);

        $groupedByMedico = $matchingRecords->groupBy(function($r) {
            return trim($r->medico) ?: 'NO ESPECIFICADO';
        })->map(function($records, $medicoName) {
            $prof = $records->first()->prof ?? 'Sin Profesión';

            $fechasGrouped = $records->groupBy(function($r) {
                return $r->fecha ? \Carbon\Carbon::parse($r->fecha)->format('d/m/Y') : 'SIN FECHA';
            })->map(function($fRecords, $fechaStr) {
                return [
                    'fecha' => $fechaStr,
                    'count' => $fRecords->count(),
                ];
            })->sortBy(function($f) {
                if ($f['fecha'] === 'SIN FECHA') return '9999-99-99';
                $parts = explode('/', $f['fecha']);
                return count($parts) === 3 ? "{$parts[2]}-{$parts[1]}-{$parts[0]}" : $f['fecha'];
            })->values();

            return [
                'medico' => $medicoName,
                'profesion' => $prof,
                'count' => $records->count(),
                'fechas' => $fechasGrouped,
            ];
        })->sortByDesc('count')->values();

        $colNames = [
            1 => 'ENFERMERAS AUXILIARES',
            2 => 'PROFESIONAL / NUTRICIÓN / PSICOLOGÍA',
            3 => 'MÉDICO GENERAL',
            4 => 'MÉDICO ESPECIALISTA',
        ];

        return response()->json([
            'concepto' => $targetRow['label'] ?? '',
            'columna_nombre' => $colNames[$colIdx] ?? "Columna {$colIdx}",
            'total_registros' => $matchingRecords->count(),
            'medicos' => $groupedByMedico,
        ]);
    }

    public function morbilidadAudit(Request $request)
    {
        $ano = $request->input('ano', date('Y'));
        $mes = $request->input('mes', '');
        $jornada = $request->input('jornada', 'TODAS');
        $conceptKey = $request->input('concept_key', '');

        $matchers = [
            'diarreas_nuevas' => [
                'label' => 'No. De niños/as menores de 5 años con diarrea nuevo',
                'is_menor5' => true,
                'cond' => 'N',
                'has_trans2' => true,
                'test' => fn($d, $c, $cod) => (str_contains($d, 'DIARREA') || $cod === 'A09.X') && $c === 'N',
            ],
            'diarreas_subs' => [
                'label' => 'No. De niños/as menores de 5 años con diarrea que acuden a cita de seguimiento',
                'is_menor5' => true,
                'cond' => 'S',
                'has_trans2' => false,
                'test' => fn($d, $c, $cod) => (str_contains($d, 'DIARREA') || $cod === 'A09.X') && $c === 'S',
            ],
            'neumonias_nuevas' => [
                'label' => 'No. De Niños/as menores de 5 años con casos de Neumonía nuevos en el año',
                'is_menor5' => true,
                'cond' => 'N',
                'has_trans2' => true,
                'test' => fn($d, $c, $cod) => (str_contains($d, 'NEUMONIA') || str_contains($d, 'BRONCONEUMONIA')) && $c === 'N',
            ],
            'neumonias_subs' => [
                'label' => 'No. De Niños/as menores de 5 años con Neumonía que acuden a su cita de Seguimiento',
                'is_menor5' => true,
                'cond' => 'S',
                'has_trans2' => false,
                'test' => fn($d, $c, $cod) => (str_contains($d, 'NEUMONIA') || str_contains($d, 'BRONCONEUMONIA')) && $c === 'S',
            ],
            'anemias_nuevas' => [
                'label' => 'No. de niños/as menores de 5 años con algun grado de Síndrome anémico diagnosticado',
                'is_menor5' => true,
                'cond' => 'N',
                'has_trans2' => true,
                'test' => fn($d, $c, $cod) => str_contains($d, 'ANEMIA') && $c === 'N',
            ],
            'diabetes_nuevas' => [
                'label' => 'Número de atenciones brindadas Nuevas de Diabetes Mellitus',
                'is_menor5' => false,
                'cond' => 'N',
                'has_trans2' => true,
                'test' => fn($d, $c, $cod) => (str_contains($d, 'DIABETES') || $cod === 'E14.9' || $d === 'DM2') && $c === 'N',
            ],
            'diabetes_subs' => [
                'label' => 'Número de atenciones brindadas Subsiguientes de Diabetes Mellitus',
                'is_menor5' => false,
                'cond' => 'S',
                'has_trans2' => false,
                'test' => fn($d, $c, $cod) => (str_contains($d, 'DIABETES') || $cod === 'E14.9' || $d === 'DM2') && $c === 'S',
            ],
            'hta_nuevas' => [
                'label' => 'Número de atenciones brindadas Nuevas de Hipertensión Arterial',
                'is_menor5' => false,
                'cond' => 'N',
                'has_trans2' => true,
                'test' => fn($d, $c, $cod) => (str_contains($d, 'HIPERTENSION') || $d === 'HTA' || $cod === 'I10.X') && $c === 'N',
            ],
            'hta_subs' => [
                'label' => 'Número de atenciones brindadas Subsiguientes de Hipertensión Arterial',
                'is_menor5' => false,
                'cond' => 'S',
                'has_trans2' => false,
                'test' => fn($d, $c, $cod) => (str_contains($d, 'HIPERTENSION') || $d === 'HTA' || $cod === 'I10.X') && $c === 'S',
            ],
            'erc_nuevas' => [
                'label' => 'Número de atenciones brindadas Nuevas de Enfermedad Renal Crónica',
                'is_menor5' => false,
                'cond' => 'N',
                'has_trans2' => true,
                'test' => fn($d, $c, $cod) => (str_contains($d, 'RENAL CRONICA') || str_contains($d, 'ENFERMEDAD RENAL') || $d === 'ERC') && $c === 'N',
            ],
            'erc_subs' => [
                'label' => 'Número de atenciones brindadas Subsiguientes de Enfermedad Renal Crónica',
                'is_menor5' => false,
                'cond' => 'S',
                'has_trans2' => false,
                'test' => fn($d, $c, $cod) => (str_contains($d, 'RENAL CRONICA') || str_contains($d, 'ENFERMEDAD RENAL') || $d === 'ERC') && $c === 'S',
            ],
        ];

        if (!isset($matchers[$conceptKey])) {
            return response()->json(['error' => 'Concepto no configurado para auditoría de morbilidad.'], 404);
        }

        $def = $matchers[$conceptKey];

        // Definición de rangos de edad
        if ($def['is_menor5']) {
            $ageRanges = [
                '< 28 Días' => fn($e, $t) => ($t === 'D'),
                '1 a 11 Meses' => fn($e, $t) => ($t === 'M'),
                '1 Año' => fn($e, $t) => ($t === 'A' && $e == 1),
                '2 Años' => fn($e, $t) => ($t === 'A' && $e == 2),
                '3 Años' => fn($e, $t) => ($t === 'A' && $e == 3),
                '4 Años' => fn($e, $t) => ($t === 'A' && $e == 4),
            ];
        } else {
            $ageRanges = [
                '< 1 Año' => fn($e, $t) => ($t === 'D' || $t === 'M' || ($t === 'A' && $e == 0)),
                '1 a 4 Años' => fn($e, $t) => ($t === 'A' && $e >= 1 && $e <= 4),
                '5 a 14 Años' => fn($e, $t) => ($t === 'A' && $e >= 5 && $e <= 14),
                '15 a 19 Años' => fn($e, $t) => ($t === 'A' && $e >= 15 && $e <= 19),
                '20 a 49 Años' => fn($e, $t) => ($t === 'A' && $e >= 20 && $e <= 49),
                '50 a 59 Años' => fn($e, $t) => ($t === 'A' && $e >= 50 && $e <= 59),
                '60 Años y más' => fn($e, $t) => ($t === 'A' && $e >= 60),
            ];
        }

        $cacheKey = "morb_audit_v5_{$ano}_{$mes}_{$jornada}_{$conceptKey}";
        $data = Cache::remember($cacheKey, 45, function() use ($ano, $mes, $jornada, $def, $ageRanges) {
            // 1. Pacientes en Registros Globales (AT1)
            $rgQuery = RegistroGlobal::where('ano', $ano)->where('mes', $mes);
            if ($jornada !== 'TODAS') $rgQuery->where('jornada', $jornada);
            $rgRecords = $rgQuery->get();

            $rgPatients = [];
            $at2Patients = [];
            $duplicatesInRG = [];
            $excludedProfessions = [];

            foreach ($rgRecords as $r) {
                $t = strtoupper(trim($r->tipo ?? ''));
                $e = (int)($r->edad ?? 0);
                if ($def['is_menor5']) {
                    if (!($t === 'D' || $t === 'M' || ($t === 'A' && $e <= 4))) continue;
                }

                $col = $this->resolveColumnaProfesion($r->prof, $r->medico ?? '', false);

                $matchedDiags = [];
                for ($i = 1; $i <= 7; $i++) {
                    $diag = $this->cleanDiag($r->{"diagnostico_$i"} ?? '');
                    $cond = strtoupper(trim($r->{"cond_$i"} ?? ($r->cond ?? '')));
                    $cod = strtoupper(trim($r->{"cod_$i"} ?? ''));
                    if ($def['test']($diag, $cond, $cod)) {
                        $matchedDiags[] = [
                            'pos' => $i,
                            'diag' => $r->{"diagnostico_$i"},
                            'cond' => $cond,
                            'cod' => $cod
                        ];
                    }
                }

                if (count($matchedDiags) > 0) {
                    // Determinar rango de edad
                    $rangoAsignado = 'Otro / No especificado';
                    foreach ($ageRanges as $rLabel => $rFunc) {
                        if ($rFunc($e, $t)) {
                            $rangoAsignado = $rLabel;
                            break;
                        }
                    }

                    $patientItem = [
                        'registro_id' => $r->id,
                        'fecha' => $r->fecha ? \Carbon\Carbon::parse($r->fecha)->format('d/m/Y') : 'SIN FECHA',
                        'paciente' => trim($r->nombre_paciente ?? '') ?: 'NO ESPECIFICADO',
                        'edad' => ($r->edad !== null ? $r->edad . ' ' . $r->tipo : 'S/E'),
                        'tipo' => $t,
                        'edad_num' => $e,
                        'rango_edad' => $rangoAsignado,
                        'sexo' => $r->sexo ?: 'S/S',
                        'medico' => trim($r->medico ?? '') ?: 'NO ESPECIFICADO',
                        'prof' => $r->prof ?: 'SIN PROFESIÓN',
                        'col' => $col,
                        'diagnosticos' => $matchedDiags,
                        'en_rg' => true,
                        'en_at2' => (bool)$col,
                        'en_morb' => false,
                    ];

                    $rgPatients[$r->id] = $patientItem;

                    if (!$col) {
                        $excludedProfessions[] = [
                            'registro_id' => $r->id,
                            'fecha' => $patientItem['fecha'],
                            'paciente' => $patientItem['paciente'],
                            'medico' => $patientItem['medico'],
                            'prof' => $patientItem['prof'],
                            'rango_edad' => $rangoAsignado,
                            'diagnosticos' => $matchedDiags,
                            'motivo' => "La profesión '{$r->prof}' está excluida o no pertenece a las 4 columnas del AT2-r N."
                        ];
                    } else {
                        $at2Patients[$r->id] = $patientItem;

                        if (count($matchedDiags) > 1) {
                            $duplicatesInRG[] = [
                                'registro_id' => $r->id,
                                'fecha' => $patientItem['fecha'],
                                'paciente' => $patientItem['paciente'],
                                'medico' => $patientItem['medico'],
                                'prof' => $patientItem['prof'],
                                'rango_edad' => $rangoAsignado,
                                'count' => count($matchedDiags),
                                'diagnosticos' => $matchedDiags,
                                'motivo' => "El diagnóstico se registró en " . count($matchedDiags) . " casillas de la misma atención. En AT2 cuenta 1 paciente; en Morbilidad suma " . count($matchedDiags) . " líneas."
                            ];
                        }
                    }
                }
            }

            // 2. Líneas de Diagnóstico de Morbilidad y TRANS-2 (Extraídas directamente de RegistroGlobal)
            $morbMatched = [];
            $morbMatchedByRegId = [];
            $orphansInMorb = [];

            foreach ($rgRecords as $r) {
                $t = strtoupper(trim($r->tipo ?? ''));
                $e = (int)($r->edad ?? 0);
                if ($def['is_menor5']) {
                    if (!($t === 'D' || $t === 'M' || ($t === 'A' && $e <= 4))) continue;
                }

                $rangoMorb = 'Otro / No especificado';
                foreach ($ageRanges as $rLabel => $rFunc) {
                    if ($rFunc($e, $t)) {
                        $rangoMorb = $rLabel;
                        break;
                    }
                }

                for ($i = 1; $i <= 7; $i++) {
                    $diagRaw = $r->{"diagnostico_$i"} ?? '';
                    if (trim($diagRaw) === '') continue;

                    $diag = $this->cleanDiag($diagRaw);
                    $cond = strtoupper(trim($r->{"cond_$i"} ?? ($r->cond ?? '')));
                    $cod = strtoupper(trim($r->{"cod_$i"} ?? ''));

                    if ($def['test']($diag, $cond, $cod)) {
                        $infItem = [
                            'id' => "RG-{$r->id}-{$i}",
                            'registro_id' => $r->id,
                            'fecha' => $r->fecha ? \Carbon\Carbon::parse($r->fecha)->format('d/m/Y') : 'SIN FECHA',
                            'paciente' => trim($r->nombre_paciente ?? '') ?: 'NO ESPECIFICADO',
                            'edad' => ($r->edad !== null ? $r->edad . ' ' . $r->tipo : 'S/E'),
                            'tipo' => $t,
                            'edad_num' => $e,
                            'rango_edad' => $rangoMorb,
                            'sexo' => $r->sexo ?: 'S/S',
                            'medico' => trim($r->medico ?? '') ?: 'NO ESPECIFICADO',
                            'prof' => $r->prof ?: 'SIN PROFESIÓN',
                            'diagnostico' => $diagRaw . ($cond ? " ($cond)" : ''),
                            'cod' => $cod,
                        ];

                        $morbMatched[] = $infItem;
                        $morbMatchedByRegId[$r->id][] = $infItem;

                        if (isset($at2Patients[$r->id])) {
                            $at2Patients[$r->id]['en_morb'] = true;
                        }
                        if (isset($rgPatients[$r->id])) {
                            $rgPatients[$r->id]['en_morb'] = true;
                        }
                    }
                }
            }

            // 3. Faltantes en Morbilidad (Registrados en AT2 / RG pero sin línea en la tabla informes)
            $faltantesEnMorbilidad = [];
            foreach ($at2Patients as $regId => $p) {
                if (!$p['en_morb']) {
                    $faltantesEnMorbilidad[] = [
                        'registro_id' => $regId,
                        'fecha' => $p['fecha'],
                        'paciente' => $p['paciente'],
                        'edad' => $p['edad'],
                        'rango_edad' => $p['rango_edad'],
                        'sexo' => $p['sexo'],
                        'medico' => $p['medico'],
                        'prof' => $p['prof'],
                        'diagnosticos' => $p['diagnosticos'],
                        'motivo' => "El paciente está registrado en Registro Global / AT2-r N pero NO aparece en la tabla de Morbilidad (requiere sincronización o fue guardado con otra condición)."
                    ];
                }
            }

            // 4. Desglose comparativo por rango de edad
            $desgloseEdad = [];
            foreach ($ageRanges as $rLabel => $rFunc) {
                $countRG = 0;
                $countAT2 = 0;
                $countMorb = 0;

                foreach ($rgPatients as $p) {
                    if ($rFunc($p['edad_num'], $p['tipo'])) $countRG++;
                }
                foreach ($at2Patients as $p) {
                    if ($rFunc($p['edad_num'], $p['tipo'])) $countAT2++;
                }
                foreach ($morbMatched as $m) {
                    if ($rFunc($m['edad_num'], $m['tipo'])) $countMorb++;
                }

                $dif = $countAT2 - $countMorb;
                $hasTrans2 = $def['has_trans2'] ?? false;
                $trans2TotalRow = $hasTrans2 ? $countMorb : null;

                $desgloseEdad[] = [
                    'rango' => $rLabel,
                    'rg_total' => $countRG,
                    'at2_total' => $countAT2,
                    'morb_total' => $countMorb,
                    'trans2_total' => $trans2TotalRow,
                    'diferencia' => $dif,
                    'cuadra' => ($dif === 0),
                    'motivo_rango' => ($dif !== 0) 
                        ? ($dif > 0 ? "AT2-r N tiene {$dif} más que Morbilidad" : "Morbilidad tiene " . abs($dif) . " más que AT2-r N")
                        : "Coincidencia exacta"
                ];
            }

            $rgTotal = count($rgPatients);
            $at2Total = count($at2Patients);
            $morbTotal = count($morbMatched);
            $hasTrans2 = $def['has_trans2'] ?? false;
            $trans2Total = $hasTrans2 ? $morbTotal : null;
            $diferencia = $at2Total - $morbTotal;
            $cuadra = ($diferencia === 0);

            return [
                'concepto' => $def['label'],
                'has_trans2' => $hasTrans2,
                'rg_total' => $rgTotal,
                'at2_total' => $at2Total,
                'morb_total' => $morbTotal,
                'trans2_total' => $trans2Total,
                'diferencia' => $diferencia,
                'cuadra' => $cuadra,
                'desglose_edad' => $desgloseEdad,
                'faltantes_morb' => $faltantesEnMorbilidad,
                'duplicados' => $duplicatesInRG,
                'huerfanos' => $orphansInMorb,
                'excluidos_profesion' => $excludedProfessions,
                'pacientes' => array_values($at2Patients),
            ];
        });

        return response()->json($data);
    }

    public function export(Request $request)
    {
        // Reutiliza la lógica de exportación si existe un Export específico
        return redirect()->route('informes.at2r-n');
    }

    public function saveManual(Request $request)
    {
        $ano = $request->input('ano', date('Y'));
        $mes = $request->input('mes', '');
        $jornada = $request->input('jornada', 'TODAS');
        $profFilter = $request->input('prof', 'TODAS');
        $medicoFilter = $request->input('medico', 'TODOS');
        $sexoFilter = $request->input('sexo', 'AMBOS');
        $manualKey = $request->input('manual_key', 'rehidratados');
        $col1 = $request->input('col1', 0);
        $col2 = $request->input('col2', 0);
        $col3 = $request->input('col3', 0);
        $col4 = $request->input('col4', 0);

        $settingKey = "at2rn_manual_{$manualKey}_{$ano}_{$mes}_{$jornada}_{$profFilter}_{$medicoFilter}_{$sexoFilter}";

        Setting::updateOrCreate(
            ['key' => $settingKey],
            ['value' => json_encode([1 => $col1, 2 => $col2, 3 => $col3, 4 => $col4])]
        );

        return response()->json(['success' => true]);
    }

    private function isEmbarazadaRecord($r): bool
    {
        $sex = strtoupper(trim($r->sexo ?? ''));
        if ($sex !== 'M') {
            return false; // Hombres nunca están embarazados
        }

        $diagText = strtoupper(
            ($r->diagnostico_1 ?? '') . ' ' .
            ($r->diagnostico_2 ?? '') . ' ' .
            ($r->diagnostico_3 ?? '') . ' ' .
            ($r->diagnostico_4 ?? '') . ' ' .
            ($r->diagnostico_5 ?? '') . ' ' .
            ($r->diagnostico_6 ?? '') . ' ' .
            ($r->diagnostico_7 ?? '') . ' ' .
            ($r->diagnostico ?? '')
        );

        if (str_contains($diagText, 'PRENATAL')
            || str_contains($diagText, 'EMBARAZ')
            || str_contains($diagText, 'GESTAN')
            || str_contains($diagText, 'PUERPER')
            || str_contains($diagText, 'PARTO')
            || str_contains($diagText, 'OBSTETR')) {
            return true;
        }

        return false;
    }

    private function getCondRecord($r): string
    {
        $c = strtoupper(trim($r->cond ?? ''));
        if ($c === '') {
            $c = strtoupper(trim($r->cond_1 ?? ''));
        }
        return $c;
    }

    private function cleanDiag($str): string
    {
        $str = strtoupper(trim((string)$str));
        return str_replace(['Á','É','Í','Ó','Ú','á','é','í','ó','ú'], ['A','E','I','O','U','a','e','i','o','u'], $str);
    }

    private function getMorbilidadReferenceData($ano, $mes, $jornada): array
    {
        $query = RegistroGlobal::query()->where('ano', $ano)->where('mes', $mes);
        if ($jornada !== 'TODAS') {
            $query->where('jornada', $jornada);
        }

        $records = $query->get([
            'edad', 'tipo', 'cond',
            'diagnostico_1', 'cod_1', 'cond_1',
            'diagnostico_2', 'cod_2', 'cond_2',
            'diagnostico_3', 'cod_3', 'cond_3',
            'diagnostico_4', 'cod_4', 'cond_4',
            'diagnostico_5', 'cod_5', 'cond_5',
            'diagnostico_6', 'cod_6', 'cond_6',
            'diagnostico_7', 'cod_7', 'cond_7',
        ]);

        $diarreasN = 0;
        $diarreasS = 0;
        $neumoniasN = 0;
        $neumoniasS = 0;
        $anemiasN = 0;
        $diabetesN = 0;
        $diabetesS = 0;
        $htaN = 0;
        $htaS = 0;
        $ercN = 0;
        $ercS = 0;

        foreach ($records as $r) {
            $tipo = strtoupper(trim($r->tipo ?? ''));
            $edad = (int)($r->edad ?? 0);
            $isMenor5 = ($tipo === 'D' || $tipo === 'M' || ($tipo === 'A' && $edad <= 4));

            for ($i = 1; $i <= 7; $i++) {
                $diagRaw = $r->{"diagnostico_$i"} ?? '';
                if (trim($diagRaw) === '') continue;

                $diag = $this->cleanDiag($diagRaw);
                $cod = strtoupper(trim($r->{"cod_$i"} ?? ''));
                $cond = strtoupper(trim($r->{"cond_$i"} ?? ($r->cond ?? '')));
                if ($cond !== 'N' && $cond !== 'S') $cond = 'N';

                // Diarreas (< 5 años)
                if ($isMenor5 && (str_contains($diag, 'DIARREA') || $cod === 'A09.X')) {
                    if ($cond === 'N') $diarreasN++;
                    elseif ($cond === 'S') $diarreasS++;
                }

                // Neumonías (< 5 años)
                if ($isMenor5 && (str_contains($diag, 'NEUMONIA') || str_contains($diag, 'BRONCONEUMONIA'))) {
                    if ($cond === 'N') $neumoniasN++;
                    elseif ($cond === 'S') $neumoniasS++;
                }

                // Anemias (< 5 años)
                if ($isMenor5 && str_contains($diag, 'ANEMIA')) {
                    if ($cond === 'N') $anemiasN++;
                }

                // Diabetes (Todas las edades)
                if (str_contains($diag, 'DIABETES') || $cod === 'E14.9' || $diag === 'DM2') {
                    if ($cond === 'N') $diabetesN++;
                    elseif ($cond === 'S') $diabetesS++;
                }

                // Hipertensión (Todas las edades)
                if (str_contains($diag, 'HIPERTENSION') || $diag === 'HTA' || $cod === 'I10.X') {
                    if ($cond === 'N') $htaN++;
                    elseif ($cond === 'S') $htaS++;
                }

                // ERC (Todas las edades)
                if (str_contains($diag, 'RENAL CRONICA') || str_contains($diag, 'ENFERMEDAD RENAL') || $diag === 'ERC') {
                    if ($cond === 'N') $ercN++;
                    elseif ($cond === 'S') $ercS++;
                }
            }
        }

        return [
            'diarreas_nuevas' => $diarreasN,
            'diarreas_subs' => $diarreasS,
            'neumonias_nuevas' => $neumoniasN,
            'neumonias_subs' => $neumoniasS,
            'anemias_nuevas' => $anemiasN,
            'diabetes_nuevas' => $diabetesN,
            'diabetes_subs' => $diabetesS,
            'hta_nuevas' => $htaN,
            'hta_subs' => $htaS,
            'erc_nuevas' => $ercN,
            'erc_subs' => $ercS,
        ];
    }

    private function resolveColumnaProfesion($prof, $medico = '', bool $force = false): ?int
    {
        $prof = strtoupper(trim($prof ?? ''));
        $medico = strtoupper(trim($medico ?? ''));

        $mapping = [
            'ENFERMERAS AUXILIARES' => 1,
            'ENFERMERA AUXILIAR' => 1,
            'LICENCIADA EN ENFERMERIA' => 2,
            'LICENCIADAS EN ENFERMERIA' => 2,
            'ENFERMERA PROFESIONAL' => 2,
            'NUTRICION' => 2,
            'NUTRICIÓN' => 2,
            'LICENCIADA EN NUTRICION' => 2,
            'LICENCIADAS EN NUTRICION' => 2,
            'LICENCIADA EN NUTRICIÓN' => 2,
            'LICENCIADAS EN NUTRICIÓN' => 2,
            'NUTRICIONISTA' => 2,
            'PSICOLOGIA' => 2,
            'PSICOLOGÍA' => 2,
            'PSICOLOGO' => 2,
            'PSICÓLOGO' => 2,
            'CONSEJERIA' => 2,
            'CONSEJERÍA' => 2,
            'SALUD MENTAL' => 2,
            'MEDICO GENERAL' => 3,
            'MEDICO ESPECIALISTA' => 4,
            'MÉDICO ESPECIALISTA' => 4,
            'PSIQUIATRA' => 4,
            'PSIQUIATRIA' => 4,
            'PSIQUIATRÍA' => 4,
        ];
        $omitir = ['TRABAJO SOCIAL', 'ODONTOLOGIA'];

        // Si el nombre del médico o la profesión menciona especialista (Ginecólogo, Pediatra, Psiquiatra, Especialista)
        if (str_contains($medico, 'GINECOL') || str_contains($medico, 'PEDIATR') || str_contains($medico, 'PSIQUIAT') || str_contains($medico, 'ESPECIALISTA')) {
            return 4;
        }

        if ($prof === 'MÉDICO GENERAL' || $prof === 'MÉDICO ESPECIALISTA' || str_contains($prof, 'ESPECIALISTA')) {
            return 4;
        }

        if ($prof === 'MEDICO GENERAL') {
            return 3;
        }

        if (isset($mapping[$prof])) {
            return $mapping[$prof];
        }

        if (str_contains($prof, 'AUXILIAR')) return 1;
        if (str_contains($prof, 'ENFERMER') || str_contains($prof, 'NUTRICI') || str_contains($prof, 'PSICOLOG')) return 2;
        if (str_contains($prof, 'ESPECIALISTA') || str_contains($prof, 'PSIQUIATR') || str_contains($prof, 'GINECOL') || str_contains($prof, 'PEDIATR')) return 4;
        if (!$force && in_array($prof, $omitir)) return null;

        return 4;
    }

    private function isPrenatalNuevaRecord($r): bool
    {
        for ($i = 1; $i <= 7; $i++) {
            $diagProp = "diagnostico_{$i}";
            $condProp = "cond_{$i}";
            $diag = strtoupper(trim($r->{$diagProp} ?? ''));
            $cond = strtoupper(trim($r->{$condProp} ?? ''));
            $diagClean = str_replace(['Ó', 'ó', 'Á', 'á', 'É', 'é', 'Í', 'í', 'Ú', 'ú'], ['O', 'o', 'A', 'a', 'E', 'e', 'I', 'i', 'U', 'u'], $diag);
            if ((str_contains($diagClean, 'PRENATAL') || str_contains($diagClean, 'EMBARAZ')) && $cond === 'N') {
                return true;
            }
        }
        $diagMain = strtoupper(trim($r->diagnostico ?? ''));
        $condMain = strtoupper(trim($r->cond_diag ?? ($this->getCondRecord($r))));
        $diagMainClean = str_replace(['Ó', 'ó', 'Á', 'á', 'É', 'é', 'Í', 'í', 'Ú', 'ú'], ['O', 'o', 'A', 'a', 'E', 'e', 'I', 'i', 'U', 'u'], $diagMain);
        if ((str_contains($diagMainClean, 'PRENATAL') || str_contains($diagMainClean, 'EMBARAZ')) && $condMain === 'N') {
            return true;
        }
        return false;
    }

    private function getAgeRows(): array
    {
        return [
            ['label' => 'menores de 1 mes primera vez', 'match' => fn($r) => ((strtoupper(trim($r->tipo ?? '')) === 'D' || (strtoupper(trim($r->tipo ?? '')) === 'M' && (int)$r->edad === 0) || (strtoupper(trim($r->tipo ?? '')) === 'A' && $r->edad !== null && (int)$r->edad === 0)) || ($r->edad === null && str_contains($r->rango ?? '', 'MENOR DE 1 MES'))) && $this->getCondRecord($r) === 'N'],
            ['label' => 'menores de 1 mes subsiguiente', 'match' => fn($r) => ((strtoupper(trim($r->tipo ?? '')) === 'D' || (strtoupper(trim($r->tipo ?? '')) === 'M' && (int)$r->edad === 0) || (strtoupper(trim($r->tipo ?? '')) === 'A' && $r->edad !== null && (int)$r->edad === 0)) || ($r->edad === null && str_contains($r->rango ?? '', 'MENOR DE 1 MES'))) && $this->getCondRecord($r) === 'S'],
            ['label' => '1 mes a un año primera vez', 'match' => fn($r) => ((strtoupper(trim($r->tipo ?? '')) === 'M' && (int)$r->edad >= 1) || ($r->edad === null && str_contains($r->rango ?? '', '1 MES A 1 AÑO'))) && $this->getCondRecord($r) === 'N'],
            ['label' => '1 mes a un año subsiguiente', 'match' => fn($r) => ((strtoupper(trim($r->tipo ?? '')) === 'M' && (int)$r->edad >= 1) || ($r->edad === null && str_contains($r->rango ?? '', '1 MES A 1 AÑO'))) && $this->getCondRecord($r) === 'S'],
            ['label' => '1-4 años primera vez', 'match' => fn($r) => ((strtoupper(trim($r->tipo ?? '')) === 'A' && (int)$r->edad >= 1 && (int)$r->edad <= 4) || ($r->edad === null && str_contains($r->rango ?? '', '1 A 4 AÑOS'))) && $this->getCondRecord($r) === 'N'],
            ['label' => '1-4 años subsiguiente', 'match' => fn($r) => ((strtoupper(trim($r->tipo ?? '')) === 'A' && (int)$r->edad >= 1 && (int)$r->edad <= 4) || ($r->edad === null && str_contains($r->rango ?? '', '1 A 4 AÑOS'))) && $this->getCondRecord($r) === 'S'],
            ['label' => '5-9 años primera vez', 'match' => fn($r) => ((strtoupper(trim($r->tipo ?? '')) === 'A' && (int)$r->edad >= 5 && (int)$r->edad <= 9) || ($r->edad === null && str_contains($r->rango ?? '', '5 A 9 AÑOS'))) && $this->getCondRecord($r) === 'N'],
            ['label' => '5-9 años subsiguiente', 'match' => fn($r) => ((strtoupper(trim($r->tipo ?? '')) === 'A' && (int)$r->edad >= 5 && (int)$r->edad <= 9) || ($r->edad === null && str_contains($r->rango ?? '', '5 A 9 AÑOS'))) && $this->getCondRecord($r) === 'S'],
            ['label' => '10-14 años primera vez', 'match' => fn($r) => ((strtoupper(trim($r->tipo ?? '')) === 'A' && (int)$r->edad >= 10 && (int)$r->edad <= 14) || ($r->edad === null && str_contains($r->rango ?? '', '10 A 14 AÑOS'))) && $this->getCondRecord($r) === 'N'],
            ['label' => '10-14 años subsiguiente', 'match' => fn($r) => ((strtoupper(trim($r->tipo ?? '')) === 'A' && (int)$r->edad >= 10 && (int)$r->edad <= 14) || ($r->edad === null && str_contains($r->rango ?? '', '10 A 14 AÑOS'))) && $this->getCondRecord($r) === 'S'],
            ['label' => '15-19 años primera vez', 'match' => fn($r) => ((strtoupper(trim($r->tipo ?? '')) === 'A' && (int)$r->edad >= 15 && (int)$r->edad <= 19) || ($r->edad === null && str_contains($r->rango ?? '', '15 A 19 AÑOS'))) && $this->getCondRecord($r) === 'N'],
            ['label' => '15-19 años sub siguiente', 'match' => fn($r) => ((strtoupper(trim($r->tipo ?? '')) === 'A' && (int)$r->edad >= 15 && (int)$r->edad <= 19) || ($r->edad === null && str_contains($r->rango ?? '', '15 A 19 AÑOS'))) && $this->getCondRecord($r) === 'S'],
            ['label' => '20-49 años primera vez', 'match' => fn($r) => ((strtoupper(trim($r->tipo ?? '')) === 'A' && (int)$r->edad >= 20 && (int)$r->edad <= 49) || ($r->edad === null && str_contains($r->rango ?? '', '20 A 49 AÑOS'))) && $this->getCondRecord($r) === 'N'],
            ['label' => '20-49 años subsiguiente', 'match' => fn($r) => ((strtoupper(trim($r->tipo ?? '')) === 'A' && (int)$r->edad >= 20 && (int)$r->edad <= 49) || ($r->edad === null && str_contains($r->rango ?? '', '20 A 49 AÑOS'))) && $this->getCondRecord($r) === 'S'],
            ['label' => '50-59 años primera vez', 'match' => fn($r) => ((strtoupper(trim($r->tipo ?? '')) === 'A' && (int)$r->edad >= 50 && (int)$r->edad <= 59) || ($r->edad === null && str_contains($r->rango ?? '', '50 A 59 AÑOS'))) && $this->getCondRecord($r) === 'N'],
            ['label' => '50-59 años subsiguiente', 'match' => fn($r) => ((strtoupper(trim($r->tipo ?? '')) === 'A' && (int)$r->edad >= 50 && (int)$r->edad <= 59) || ($r->edad === null && str_contains($r->rango ?? '', '50 A 59 AÑOS'))) && $this->getCondRecord($r) === 'S'],
            ['label' => '60...- años primera vez', 'match' => fn($r) => ((strtoupper(trim($r->tipo ?? '')) === 'A' && (int)$r->edad >= 60) || ($r->edad === null && (str_contains($r->rango ?? '', '60') || str_contains($r->rango ?? '', 'MAYORES')))) && $this->getCondRecord($r) === 'N'],
            ['label' => '60...- años subsiguiente', 'match' => fn($r) => ((strtoupper(trim($r->tipo ?? '')) === 'A' && (int)$r->edad >= 60) || ($r->edad === null && (str_contains($r->rango ?? '', '60') || str_contains($r->rango ?? '', 'MAYORES')))) && $this->getCondRecord($r) === 'S'],
            ['label' => 'TOTAL PACIENTES ATENDIDOS', 'sum' => [0, 1, 2, 3, 4, 5, 6, 7, 8, 9, 10, 11, 12, 13, 14, 15, 16, 17], 'color' => 'bg-info-soft'],
            ['label' => 'No. Atenciones de mujeres', 'match' => fn($r) => strtoupper(trim($r->sexo ?? '')) === 'M'],
            ['label' => 'No. Atenciones de hombres', 'match' => fn($r) => strtoupper(trim($r->sexo ?? '')) === 'H'],
            ['label' => 'Total atenciones a mujeres y hombres', 'sum' => [19, 20], 'color' => 'bg-info-soft'],
            ['label' => 'No. Consultas espontaneas', 'diff' => [24, 23]],
            ['label' => 'No. Consultas referidas', 'match' => fn($r) => trim($r->referido_de ?? '') !== '' || (
                (strtoupper($r->diagnostico_1 ?? '') == 'REFERENCIAS RECIBIDAS' ||
                 strtoupper($r->diagnostico_2 ?? '') == 'REFERENCIAS RECIBIDAS' ||
                 strtoupper($r->diagnostico_3 ?? '') == 'REFERENCIAS RECIBIDAS' ||
                 strtoupper($r->diagnostico_4 ?? '') == 'REFERENCIAS RECIBIDAS' ||
                 strtoupper($r->diagnostico_5 ?? '') == 'REFERENCIAS RECIBIDAS' ||
                 strtoupper($r->diagnostico_6 ?? '') == 'REFERENCIAS RECIBIDAS' ||
                 strtoupper($r->diagnostico_7 ?? '') == 'REFERENCIAS RECIBIDAS')
            )],
            ['label' => 'TOTAL DE CONSULTAS', 'sum' => [18], 'color' => 'bg-info-soft'],
            ['label' => 'H: N < 5', 'sum' => [0, 2, 4], 'hidden' => true],
            ['label' => 'H: S < 5', 'sum' => [1, 3, 5], 'hidden' => true],
            ['label' => 'H: All < 5', 'sum' => [25, 26], 'hidden' => true],
        ];
    }

    private function getProgRows($ano, $mes, $jornada, $profFilter, $medicoFilter, $sexoFilter): array
    {
        $ccuDiags = [
            'VPH AUTOTOMA CIS',
            'CITA 3 AÑOS POST CRIOTERAPIA',
            'IVAA',
            'IVAA POSITIVA',
            'IVAA NEGATIVA',
            'IVAA (SOSP. CCU)',
            'CITOLOGIA POSITIVA CON IVAA NEGATIVA',
            'VPH POR PROVEEDOR DE SALUD',
            'VPH POR AUTOTOMA EN ESTABLECIMIENTO',
            'VPH POR AUTOTOMA EN COMUNIDAD',
            'VPH POSITIVO POR PROVEEDOR DE SALUD',
            'VPH POSITIVO POR AUTOTOMA EN ESTABLECIMIENTO',
            'VPH POSITIVO POR AUTOTOMA EN COMUNIDAD',
            'VPH NEGATIVO POR PROVEEDOR DE SALUD',
            'VPH NEGATIVO POR AUTOTOMA EN ESTABLECIMIENTO',
            'VPH NEGATIVO POR AUTOTOMA EN COMUNIDAD',
            'IVAA POSITIVA POR VPH POSITIVO',
            'IVAA NEGATIVA POR VPH POSITIVO',
            'CRIOTERAPIA POR CITOLOGIA (NICI/IVAA+)',
            'CRIOTERAPIA POR IVAA +',
            'CRIOTERAPIA POR VPH+/IVAA+',
            'CITA POST- CRIOTERAPIA AL MES',
            'REFERENCIA PARA REALIZAR IVAA POR VPH+',
            'REFERENCIA POR SOSPECHA DE CCU',
            'REFERENCIA COLPOSCOPIA',
            'REFERENCIA NO APLICA PARA CRIOTERAPIA',
            'REFERENCIA PARA CRIOTERAPIA',
            'REFERENCIA POR OTRO MOTIVO',
            'REFERENCIA OTRO MOTIVO (IVAA/VPH)',
            'CONSEJERIA PREVIA REALIZACION DE TAMIZAJE',
            'CONSEJERIA A LA PRUEBA DE RESULTADO',
            'CONSEJERIA PREVIA A CRIOTERAPIA',
            'IVAA DUDOSA',
            'CONTROL AL MES POST- CRIOTERAPIA',
            'IVAA POS- AÑO POST-CRIO',
            'IVAA NEG- AÑO POST-CRIO',
            'TERMO COAGULACION',
            'CITA AL MES POS TERMO COOGULACION',
            'CITOLOGIA A EMBARAZADA',
            'CITOLOGIA A PACIENTE NO EMBARAZADA',
            'CANCER CCU',
            'CÁNCER CCU',
        ];

        return [
            ['label' => 'Numero de Atenciones del Recién Nacido para Control Temprano antes de los 5 dias', 'match_atencion' => fn($r) => trim(strtoupper($r->tipo ?? '')) == 'D' && $r->edad < 5, 'color' => 'bg-green-soft'],
            [
                'label' => 'No. De niños/as menores de 5 años con diarrea nuevo',
                'match' => function($r) {
                    $t = strtoupper(trim($r->tipo ?? ''));
                    $e = (int)($r->edad ?? 0);
                    $isMenor5 = ($t === 'D' || $t === 'M' || ($t === 'A' && $e <= 4));
                    if (!$isMenor5) return false;
                    $d = $this->cleanDiag($r->diagnostico ?? '');
                    $c = strtoupper(trim($r->cod ?? ''));
                    $cond = strtoupper(trim($r->cond_diag ?? ''));
                    return (str_contains($d, 'DIARREA') || $c === 'A09.X') && $cond === 'N';
                },
                'color' => 'bg-green-soft'
            ],
            [
                'label' => 'No. De niños/as menores de 5 años con diarrea que acuden a cita de seguimiento',
                'match' => function($r) {
                    $t = strtoupper(trim($r->tipo ?? ''));
                    $e = (int)($r->edad ?? 0);
                    $isMenor5 = ($t === 'D' || $t === 'M' || ($t === 'A' && $e <= 4));
                    if (!$isMenor5) return false;
                    $d = $this->cleanDiag($r->diagnostico ?? '');
                    $c = strtoupper(trim($r->cod ?? ''));
                    $cond = strtoupper(trim($r->cond_diag ?? ''));
                    return (str_contains($d, 'DIARREA') || $c === 'A09.X') && $cond === 'S';
                },
                'color' => 'bg-green-soft'
            ],
            ['label' => 'No. De Niños/as menores de 5 años con deshidratación rehidratados en la US', 'diag' => 'REHIDRATACION ORAL', 'age_max' => 4, 'color' => 'bg-green-soft', 'is_manual' => true, 'manual_key' => 'rehidratados'],
            [
                'label' => 'No. De Niños/as menores de 5 años con casos de Neumonía nuevos en el año',
                'match' => function($r) {
                    $t = strtoupper(trim($r->tipo ?? ''));
                    $e = (int)($r->edad ?? 0);
                    $isMenor5 = ($t === 'D' || $t === 'M' || ($t === 'A' && $e <= 4));
                    if (!$isMenor5) return false;
                    $d = $this->cleanDiag($r->diagnostico ?? '');
                    $c = strtoupper(trim($r->cod ?? ''));
                    $cond = strtoupper(trim($r->cond_diag ?? ''));
                    return (str_contains($d, 'NEUMONIA') || str_contains($d, 'BRONCONEUMONIA') || $c === 'J18.9' || $c === 'J18.0') && $cond === 'N';
                },
                'color' => 'bg-green-soft'
            ],
            [
                'label' => 'No. De Niños/as menores de 5 años con Neumonía que acuden a su cita de Seguimiento',
                'match' => function($r) {
                    $t = strtoupper(trim($r->tipo ?? ''));
                    $e = (int)($r->edad ?? 0);
                    $isMenor5 = ($t === 'D' || $t === 'M' || ($t === 'A' && $e <= 4));
                    if (!$isMenor5) return false;
                    $d = $this->cleanDiag($r->diagnostico ?? '');
                    $c = strtoupper(trim($r->cod ?? ''));
                    $cond = strtoupper(trim($r->cond_diag ?? ''));
                    return (str_contains($d, 'NEUMONIA') || str_contains($d, 'BRONCONEUMONIA') || $c === 'J18.9' || $c === 'J18.0') && $cond === 'S';
                },
                'color' => 'bg-green-soft'
            ],
            [
                'label' => 'No. de niños/as menores de 5 años con algun grado de Síndrome anémico diagnosticado',
                'match' => function($r) {
                    $t = strtoupper(trim($r->tipo ?? ''));
                    $e = (int)($r->edad ?? 0);
                    $isMenor5 = ($t === 'D' || $t === 'M' || ($t === 'A' && $e <= 4));
                    if (!$isMenor5) return false;
                    $d = $this->cleanDiag($r->diagnostico ?? '');
                    $c = strtoupper(trim($r->cod ?? ''));
                    $cond = strtoupper(trim($r->cond_diag ?? ''));
                    return (str_contains($d, 'ANEMIA') || $c === 'D64.9') && $cond === 'N';
                },
                'color' => 'bg-green-soft'
            ],
            ['label' => 'Número de menores de 5 años con crecimiento adecuado (Gráficas peso/talla y talla/edad)', 'diff' => [27, 37, 39, 40, 41], 'color' => 'bg-green-soft'],
            ['label' => 'Número de menores de 5 años sin desnutrición crónica (Gráfica talla/edad)', 'diff' => [27, 37, 39, 40, 41], 'color' => 'bg-green-soft'],
            ['label' => 'Número de menores de 5 años con baja talla y baja talla severa (Gráfica talla/edad)', 'match' => fn($r) => in_array(strtoupper(trim($r->diagnostico ?? '')), ['BAJA TALLA', 'BAJA TALLA SEVERA', 'TALLA BAJA', 'TALLA BAJA SEVERA']) && (strtoupper($r->tipo) == 'D' || strtoupper($r->tipo) == 'M' || (strtoupper($r->tipo) == 'A' && $r->edad < 5)), 'color' => 'bg-green-soft'],
            ['label' => 'Número de menores de 5 años sin desnutrición aguda ni sobrepeso/obesidad (Gráfica peso/talla)', 'diff' => [27, 37, 39, 40, 41], 'color' => 'bg-green-soft'],
            ['label' => 'Número de menores de 5 años emaciados y severamente emaciados (Gráfica peso/talla)', 'match' => fn($r) => in_array(strtoupper(trim($r->diagnostico ?? '')), ['DESNUTRICION AGUDA', 'DESNUTRICION AGUDA SEVERA', 'EMACIADO', 'EMACIADO SEVERO']) && (strtoupper($r->tipo) == 'D' || strtoupper($r->tipo) == 'M' || (strtoupper($r->tipo) == 'A' && $r->edad < 5)), 'color' => 'bg-green-soft'],
            ['label' => 'Número de menores de 5 años con sobrepeso y obesidad (Gráfica peso para la Longitud/Talla mayor o igual a +2 DE)', 'diag' => ['OBESIDAD', 'SOBREPESO', 'OBESIDAD SEVERA'], 'age_max' => 4, 'color' => 'bg-green-soft'],
            ['label' => 'Número de menores de 5 años con crecimiento inadecuado persistente (en 2 controles sucesivos) en el mes.', 'diag' => ['CRECIMIENTO INADECUADO', 'CRECIMIENTO INADECUADO PERSISTENTE'], 'age_max' => 4, 'color' => 'bg-green-soft'],
            ['label' => 'Número de menores de 5 años con Discapacidad Nuevos', 'diag' => ['DISCAPACIDAD', 'DISCAPACITADO', 'DISCAPACIDAD NUEVO'], 'cond' => 'N', 'age_max' => 4, 'color' => 'bg-green-soft'],
            ['label' => 'Número de menores de 5 años con Probable Alteración del Desarrollo', 'diag' => ['PROBABLE ALTERACION DEL DESARROLLO', 'ALTERACION DEL DESARROLLO'], 'age_max' => 4, 'color' => 'bg-green-soft'],
            ['label' => 'Total de menores de 5 años Atendidos', 'sum_rows' => [27], 'color' => 'bg-green-soft'],
            ['label' => 'Número de mujeres atendidas que se les entrego Anticonceptivo Oral Combinado', 'diag' => ['ANTICONCEPTIVO ORAL COMBINADO', 'ACO COMBINADO', 'ANTICONCEPTIVO ORAL 1 CICLO', 'ANTICONCEPTIVO ORAL 3 CICLO', 'ANTICONCEPTIVO ORAL 6 CICLO'], 'color' => 'bg-purple-lite'],
            ['label' => 'Número de mujeres atendidas que se les entregó Anticonceptivos Orales con Progestina sola', 'diag' => 'ANTICONCEPTIVOS ORALES CON PROGESTINA SOLA', 'ACO CON PROGESTINA', 'color' => 'bg-purple-lite'],
            ['label' => 'Número de mujeres que se les aplicó inyectables trimestral', 'diag' => ['MUJERES QUE SE LES APLICÓ INYECTABLES TRIMESTRAL', 'DEPO PROVERAS APLICADAS', 'INYECTABLE TRIMESTRAL', 'METODO INYECTABLE TRIMESTRAL'], 'color' => 'bg-purple-lite'],
            ['label' => 'Número de mujeres que se les aplicó autoinyectables trimestral', 'diag' => 'MUJERES QUE SE LES APLICÓ AUTOINYECTABLES TRIMESTRAL', 'color' => 'bg-purple-lite'],
            ['label' => 'Número de DIU con cobre insertados', 'diag' => ['DIU CON COBRE INSERTADOS', 'DIU INSERTADOS', 'DIU CON COBRE'], 'color' => 'bg-purple-lite'],
            ['label' => 'Número de DIU con levonorgestrel insertados', 'diag' => ['DIU CON LEVONORGESTREL INSERTADOS', 'DIU CON LEVONORGESTREL', 'DIU LNG', 'MIRENA'], 'color' => 'bg-purple-lite'],
            ['label' => 'Número de mujeres que se les insertó Implante con levonorgestrel 5 años (JADELLE)', 'diag' => 'INSERCIÓN DE IMPLANTE CON LEVONORGESTREL 5 AÑOS (JADELLE)', 'color' => 'bg-purple-lite'],
            ['label' => 'Número de mujeres que se les insertó Implante con Etonogestrel 3 años (NXT)', 'diag' => ['INSERCIÓN DE IMPLANTE CON ETONOGESTREL 3 AÑOS (NXT)', 'IMPLANTE SUB DERMICO', 'IMPLANTE CON ETONOGESTREL', 'NXT'], 'color' => 'bg-purple-lite'],
            ['label' => 'Número de mujeres que se les retiró implante', 'diag' => ['RETIRO DE IMPLANTE', 'RETIRO IMPLANON'], 'color' => 'bg-purple-lite'],
            ['label' => 'Número de mujeres que se les retiró DIU', 'diag' => 'RETIRO DE DIU', 'color' => 'bg-purple-lite'],
            [
                'label' => 'Detección de Cáncer Cérvico Uterino',
                'match' => function ($r) use ($ccuDiags) {
                    return in_array(strtoupper(trim($r->diagnostico ?? '')), $ccuDiags);
                },
                'color' => 'bg-purple-lite'
            ],
            ['label' => 'Número de consejerías de planificación familiar brindadas', 'diag' => ['CONSEJERÍAS DE PLANIFICACIÓN FAMILIAR BRINDADAS', 'CONSEJERIA PF/EMB/ZIKA', 'CONSEJERIA EN PLANIFICACION FAMILIAR'], 'color' => 'bg-purple-lite'],
            ['label' => 'Número de mujeres que se les realizó AQV Ambulatoria', 'diag' => ['AQV AMBULATORIA MUJERES', 'AQV REALIZADOS', 'AQV'], 'color' => 'bg-purple-lite'],
            ['label' => 'Número de hombres que se les realizó AQV Ambulatoria', 'diag' => ['AQV AMBULATORIA HOMBRES', 'VASECTORMÍAS REALIZADAS'], 'color' => 'bg-purple-lite'],
            ['label' => 'Número de mujeres que se les brindó PAE', 'diag' => 'PAE', 'color' => 'bg-purple-lite'],
            ['label' => 'Número de personas atendidas que se les entregó condones', 'diag' => ['CONDONES ENTREGADOS', 'CONDONES 10 UNIDADES', 'CONDONES 30 UNIDADES', 'CONDONES'], 'color' => 'bg-purple-lite'],
            ['label' => 'Número de mujeres atendidas por aborto ambulatorio', 'diag' => ['ABORTO AMBULATORIO', 'AMEU REALIZADAS'], 'color' => 'bg-purple-lite'],
            [
                'label' => 'Atención Prenatal NUEVA en las edades de 10 a 19 años (Adolescentes)',
                'match' => function($r) {
                    if (($r->tipo ?? '') != 'A' || ($r->edad ?? 0) < 10 || ($r->edad ?? 0) > 19) return false;
                    $cond = strtoupper(trim($r->cond_diag ?? ''));
                    if ($cond !== 'N') return false;
                    $diagClean = strtoupper(str_replace(['Ó', 'ó', 'Á', 'á', 'É', 'é', 'Í', 'í', 'Ú', 'ú'], ['O', 'o', 'A', 'a', 'E', 'e', 'I', 'i', 'U', 'u'], trim($r->diagnostico ?? '')));
                    return str_contains($diagClean, 'ATENCION PRENATAL') || str_contains($diagClean, 'PRENATAL');
                },
                'color' => 'bg-fuchsia-soft'
            ],
            [
                'label' => 'Atención Prenatal NUEVA en las primeras 12 Semanas de Gestación',
                'match' => fn($r) => str_contains(strtoupper($r->diagnostico ?? ''), 'ATENCION PRENATAL')
                    && str_contains(strtoupper($r->diagnostico ?? ''), 'ANTES')
                    && strtoupper(trim($r->cond_diag ?? '')) == 'N',
                'color' => 'bg-fuchsia-soft'
            ],
            [
                'label' => 'Atención prenatal NUEVA después de las 12 semanas de gestación',
                'match' => fn($r) => str_contains(strtoupper($r->diagnostico ?? ''), 'ATENCION PRENATAL')
                    && str_contains(strtoupper($r->diagnostico ?? ''), 'DESPUES')
                    && strtoupper(trim($r->cond_diag ?? '')) == 'N',
                'color' => 'bg-fuchsia-soft'
            ],
            [
                'label' => 'Total de atenciones prenatales subsiguientes',
                'match' => fn($r) => str_contains(strtoupper($r->diagnostico ?? ''), 'ATENCION PRENATAL')
                    && strtoupper(trim($r->cond_diag ?? '')) == 'S',
                'color' => 'bg-fuchsia-soft'
            ],
            [
                'label' => 'Número de atenciones puerperales entre los 3 a 7 días',
                'match' => function($r) {
                    $diag = $this->cleanDiag($r->diagnostico ?? '');
                    $cod = trim((string)($r->cod ?? ''));
                    if ($cod === '114') return true;
                    return (str_contains($diag, 'PUERP') || str_contains($diag, 'PARTO'))
                        && (str_contains($diag, '3 A 7') || str_contains($diag, '3-7') || str_contains($diag, 'PRIMEROS 7') || str_contains($diag, 'PRIMEROS 10'));
                },
                'color' => 'bg-orange-soft'
            ],
            [
                'label' => 'Número de Atenciones puerperales después de los 7 días',
                'match' => function($r) {
                    $diag = $this->cleanDiag($r->diagnostico ?? '');
                    $cod = trim((string)($r->cod ?? ''));
                    if ($cod === '115') return true;
                    return (str_contains($diag, 'PUERP') || str_contains($diag, 'PARTO'))
                        && (str_contains($diag, 'DESPUES') || str_contains($diag, 'DESP') || str_contains($diag, '11 Y 40') || str_contains($diag, 'MAYOR'));
                },
                'color' => 'bg-orange-soft'
            ],
            [
                'label' => 'Controles Puerperales',
                'sum_rows' => [66, 67],
                'color' => 'bg-orange-soft'
            ],
            ['label' => 'Número de atenciones por Violencia Sexual', 'diag' => ['VIOLENCIA SEXUAL', 'ABUSO SEXUAL'], 'color' => 'bg-info-soft'],
            ['label' => 'Número de atencion de adolescentes de 10 a 19 años mujeres', 'match_atencion' => fn($r) => $r->tipo == 'A' && $r->edad >= 10 && $r->edad <= 19 && strtoupper($r->sexo) == 'M' && !$this->isPrenatalNuevaRecord($r), 'color' => 'bg-info-soft'],
            ['label' => 'Número de atencion de adolescentes de 10 a 19 años varones', 'match_atencion' => fn($r) => $r->tipo == 'A' && $r->edad >= 10 && $r->edad <= 19 && strtoupper($r->sexo) == 'H', 'color' => 'bg-info-soft'],
            ['label' => 'Detección de Casos presuntivos de Tuberculosis', 'diag' => ['DETECCIÓN DE CASOS PRESUNTIVOS DE TUBERCULOSIS', 'CASO PRESUNTIVO DE TUBERCULOSIS', 'SINTOMATICO RESPIRATORIO'], 'cond' => 'N', 'color' => 'bg-info-soft'],
            [
                'label' => 'Número de atenciones brindadas Nuevas de Diabetes Mellitus',
                'match' => function($r) {
                    $d = $this->cleanDiag($r->diagnostico ?? '');
                    return (str_contains($d, 'DIABETES') || $d === 'DM2' || trim($r->cod ?? '') === 'E14.9') && strtoupper(trim($r->cond_diag ?? '')) === 'N';
                },
                'color' => 'bg-green-soft'
            ],
            [
                'label' => 'Número de atenciones brindadas Subsiguientes de Diabetes Mellitus',
                'match' => function($r) {
                    $d = $this->cleanDiag($r->diagnostico ?? '');
                    return (str_contains($d, 'DIABETES') || $d === 'DM2' || trim($r->cod ?? '') === 'E14.9') && strtoupper(trim($r->cond_diag ?? '')) === 'S';
                },
                'color' => 'bg-green-soft'
            ],
            [
                'label' => 'Número de atenciones brindadas Nuevas de Hipertensión Arterial',
                'match' => function($r) {
                    $d = $this->cleanDiag($r->diagnostico ?? '');
                    return (str_contains($d, 'HIPERTENSION') || $d === 'HTA' || trim($r->cod ?? '') === 'I10.X') && strtoupper(trim($r->cond_diag ?? '')) === 'N';
                },
                'color' => 'bg-green-soft'
            ],
            [
                'label' => 'Número de atenciones brindadas Subsiguientes de Hipertensión Arterial',
                'match' => function($r) {
                    $d = $this->cleanDiag($r->diagnostico ?? '');
                    return (str_contains($d, 'HIPERTENSION') || $d === 'HTA' || trim($r->cod ?? '') === 'I10.X') && strtoupper(trim($r->cond_diag ?? '')) === 'S';
                },
                'color' => 'bg-green-soft'
            ],
            [
                'label' => 'Número de atenciones brindadas Nuevas de Enfermedad Renal Crónica',
                'match' => function($r) {
                    $d = $this->cleanDiag($r->diagnostico ?? '');
                    return (str_contains($d, 'RENAL CRONICA') || str_contains($d, 'ENFERMEDAD RENAL') || $d === 'ERC') && strtoupper(trim($r->cond_diag ?? '')) === 'N';
                },
                'color' => 'bg-orange-soft'
            ],
            [
                'label' => 'Número de atenciones brindadas Subsiguientes de Enfermedad Renal Crónica',
                'match' => function($r) {
                    $d = $this->cleanDiag($r->diagnostico ?? '');
                    return (str_contains($d, 'RENAL CRONICA') || str_contains($d, 'ENFERMEDAD RENAL') || $d === 'ERC') && strtoupper(trim($r->cond_diag ?? '')) === 'S';
                },
                'color' => 'bg-orange-soft'
            ],
            ['label' => 'Número de atenciones brindadas Nuevas de Cáncer Cérvico Uterino', 'diag' => ['CANCER DE CERVIX', 'CCU', 'CANCER CCU', 'CANCER CERVICO UTERINO', 'CÁNCER CÉRVICO UTERINO'], 'cond' => 'N', 'color' => 'bg-orange-soft'],
            ['label' => 'Número de atenciones brindadas Subsiguientes de Cáncer Cérvico Uterino', 'diag' => ['CANCER DE CERVIX', 'CCU', 'CANCER CCU', 'CANCER CERVICO UTERINO', 'CÁNCER CÉRVICO UTERINO'], 'cond' => 'S', 'color' => 'bg-orange-soft'],


            ['label' => 'Número de atenciones brindadas Nuevas de Cáncer Priorizados', 'match' => fn($r) => (str_contains(strtoupper(trim($r->diagnostico ?? '')), 'CANCER') || str_contains(strtoupper(trim($r->diagnostico ?? '')), 'CÁNCER')) && strtoupper(trim($r->cond_diag ?? '')) == 'N', 'color' => 'bg-orange-soft'],
            ['label' => 'Número de atenciones brindadas Subsiguientes de Cáncer Priorizados', 'match' => fn($r) => (str_contains(strtoupper(trim($r->diagnostico ?? '')), 'CANCER') || str_contains(strtoupper(trim($r->diagnostico ?? '')), 'CÁNCER')) && strtoupper(trim($r->cond_diag ?? '')) == 'S', 'color' => 'bg-orange-soft'],
            ['label' => 'Número de atenciones por psicología-psiquiatría', 'match_atencion' => fn($r) => (str_contains(strtoupper($r->prof ?? ''), 'PSICOLOG') || str_contains(strtoupper($r->prof ?? ''), 'PSIQ')), 'color' => 'bg-info-soft'],
            ['label' => 'Número de atenciones brindadas a Migrantes Irregulares', 'diag' => 'MIGRANTES IRREGULARES', 'color' => 'bg-info-soft'],
            ['label' => 'Número de atenciones brindadas a Migrantes hondureños retornados', 'diag' => 'MIGRANTES HONDUREÑOS RETORNADOS', 'color' => 'bg-info-soft'],
            ['label' => 'ETNIAS', 'header' => true],
            ['label' => 'Garífuna', 'diag' => 'GARÍFUNA'],
            ['label' => 'Negro Inglés', 'diag' => 'NEGRO INGLÉS'],
            ['label' => 'Tolupán', 'diag' => 'TOLUPÁN'],
            ['label' => 'Pech(Paya)', 'diag' => 'PECH(PAYA)'],
            ['label' => 'Misquito', 'diag' => 'MISQUITO'],
            ['label' => 'Nahoa', 'diag' => 'NAHOA'],
            ['label' => 'Lenca', 'diag' => 'LENCA'],
            ['label' => 'Tawaka(Sumo)', 'diag' => 'TAWAKA(SUMO)'],
            ['label' => 'Maya Chortí', 'diag' => 'MAYA CHORTÍ'],
            ['label' => 'Otro', 'diff' => [21, 87, 88, 89, 90, 91, 92, 93, 94, 95, 97]],
            ['label' => 'No Sabe/ Ninguno', 'diag' => 'NO SABE/ NINGUNO ( ETNIA)'],
        ];
    }
}
