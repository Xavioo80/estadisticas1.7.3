<?php

namespace App\Http\Controllers\Informes;

use App\Http\Controllers\Controller;
use App\Models\Informe;
use App\Models\Diagnostico;
use App\Models\Setting;
use App\Traits\InformesHelperTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class At2Controller extends Controller
{
    use InformesHelperTrait;

    public function __construct(private \App\Services\RegistroGlobalService $service)
    {
    }

    public function index(Request $request)
    {
        $dateParams = $this->resolveDateParams($request);
        $ano = $dateParams['ano'];
        $mesStr = $dateParams['mesStr'];
        $mesNum = $dateParams['mesNum'];
        $daysInMonth = $dateParams['daysInMonth'];

        $filters = $this->getFilterData($ano, $mesStr);
        $anos = $filters['anos'];
        $meses = $filters['meses'];
        $medicos = $filters['medicos'];

        $medico = $request->input('medico') ?: ($medicos[0] ?? null);

        $allRecords = $this->fetchAndNormalizeRecords($medico, $ano, $mesStr);

        // Agrupar datos por día
        $groupedRecords = $allRecords->groupBy('d_idx');
        $groupedAtenciones = $allRecords->whereIn('n_cond', ['N', 'S'])->unique('registro_id')->groupBy('d_idx');

        // Definiciones de filas
        $ageRows = $this->buildAt2rAgeRows();
        $progRows = $this->buildAt2rProgRows();
        $allDefs = array_merge($ageRows, $progRows);

        $results = $this->calculateReportResults($daysInMonth, $groupedRecords, $groupedAtenciones, $ageRows, $progRows, $allDefs, $ano, $mesStr, $medico);

        $finalData = $this->formatFinalData($allDefs, $results);
        $dayMeta = $this->prepareDayMeta($ano, $mesNum, $daysInMonth);

        $viewData = compact('anos', 'meses', 'medicos', 'ano', 'mesStr', 'medico', 'daysInMonth', 'finalData', 'dayMeta');

        return $request->ajax() 
            ? view('informes.at2_content', $viewData) 
            : view('informes.at2', $viewData);
    }

    public function saveManual(Request $request)
    {
        $ano = $request->input('ano', date('Y'));
        $mes = $request->input('mes', '');
        $medico = $request->input('medico', 'TODOS');
        $manualKey = $request->input('manual_key', 'inadecuado');
        $day = $request->input('day');
        $value = $request->input('value', 0);

        // Validar que manualKey contiene solo caracteres alfanuméricos, guiones y guiones bajos
        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $manualKey)) {
            return response()->json(['success' => false, 'message' => 'manual_key contiene caracteres inválidos'], 400);
        }

        // Limitar la longitud de la clave
        if (strlen($manualKey) > 100) {
            return response()->json(['success' => false, 'message' => 'manual_key es demasiado largo'], 400);
        }

        $settingKey = "at2_manual_{$manualKey}_{$ano}_{$mes}_{$medico}";
        $setting = Setting::where('key', $settingKey)->first();

        $data = $setting ? json_decode($setting->value, true) : [];
        $data[$day] = (int) $value;

        Setting::updateOrCreate(
            ['key' => $settingKey],
            ['value' => json_encode($data)]
        );

        return response()->json(['success' => true]);
    }

    /**
     * Resuelve los parámetros de fecha (año, mes, días)
     */
    private function resolveDateParams(Request $request): array
    {
        $ano = (int) $request->input('ano', date('Y'));
        $mesStr = $request->input('mes', '');
        if (empty($mesStr)) {
            $mesStr = $this->resolverMesPorDefecto($ano);
        }

        $mesesNumericos = [
            'ENERO' => 1, 'FEBRERO' => 2, 'MARZO' => 3, 'ABRIL' => 4,
            'MAYO' => 5, 'JUNIO' => 6, 'JULIO' => 7, 'AGOSTO' => 8,
            'SEPTIEMBRE' => 9, 'OCTUBRE' => 10, 'NOVIEMBRE' => 11, 'DICIEMBRE' => 12
        ];
        $mesNum = $mesesNumericos[strtoupper($mesStr)] ?? 1;
        $daysInMonth = Carbon::create($ano, $mesNum, 1)->daysInMonth;

        return compact('ano', 'mesStr', 'mesNum', 'daysInMonth');
    }

    /**
     * Obtiene los datos de filtros comunes (años, meses, médicos)
     */
    private function getFilterData(int $ano, string $mesStr): array
    {
        $data = $this->getAnosMesesDisponiblesInformes();
        $anos = $data['anos'];
        $meses = $data['meses'];

        $medicos = Informe::distinct()
            ->where('ano', $ano)
            ->where('mes', $mesStr)
            ->whereNotNull('medico')
            ->where('medico', '!=', '')
            ->orderBy('medico')
            ->pluck('medico');

        return compact('anos', 'meses', 'medicos');
    }

    /**
     * Obtiene y normaliza los registros del médico
     */
    private function fetchAndNormalizeRecords($medico, int $ano, string $mesStr)
    {
        if (!$medico) return collect();

        return Informe::query()
            ->where('medico', $medico)
            ->where('ano', $ano)
            ->where('mes', $mesStr)
            ->get()
            ->map(function ($r) {
                // Normalizar campos para evitar procesado repetitivo en bucles
                $r->d_idx = (int) Carbon::parse($r->fecha)->day;
                $r->n_diag = strtoupper(trim($r->diagnostico ?? ''));
                $r->n_cond_diag = strtoupper(trim($r->cond_diagnostico ?? ''));
                $r->n_cod = trim($r->cod ?? '');
                $r->n_sexo = strtoupper(trim($r->sexo ?? ''));
                $r->n_cond = strtoupper(trim($r->cond ?? ''));
                return $r;
            });
    }

    /**
     * Realiza el cálculo completo del informe
     */
    private function calculateReportResults(int $daysInMonth, $groupedRecords, $groupedAtenciones, array $ageRows, array $progRows, array $allDefs, int $ano, string $mesStr, ?string $medico): array
    {
        $results = [];
        foreach ($allDefs as $idx => $def) {
            $results[$idx] = array_fill(1, $daysInMonth, 0);
        }

        $diagRows = Diagnostico::where('categoria', 'AT2-R')->orderBy('id')->select('codigo')->get();
        $extraCodigos = ['30', '55', '130', '47', '31', '240', '301', '4', '128', '223', '165', '129', '110', '111', '107', '108', '114', '115', '127', '99999', '99998', '99997', '103', '104', '105', '106', '109', '112', '113', '116', '117', '118', '119', '120', '121', '122', '123', '124', '125', '126', 'A15', 'A16'];
        $at2rCodigos = array_unique(array_merge($diagRows->pluck('codigo')->map(fn($v) => trim($v))->toArray(), $extraCodigos));

        $this->processDailyRecords($daysInMonth, $groupedRecords, $groupedAtenciones, $at2rCodigos, $ageRows, $progRows, $results);
        $this->processManualFields($results, $progRows, count($ageRows), $daysInMonth, $ano, $mesStr, $medico);

        // Post-procesamiento: Sumas y porcentajes
        foreach ($allDefs as $idx => $def) {
            if (isset($def['sum'])) {
                for ($d = 1; $d <= $daysInMonth; $d++) {
                    foreach ($def['sum'] as $srcIdx) $results[$idx][$d] += $results[$srcIdx][$d];
                }
            } elseif (isset($def['sum_rows'])) {
                for ($d = 1; $d <= $daysInMonth; $d++) {
                    foreach ($def['sum_rows'] as $srcIdx) $results[$idx][$d] += ($results[$srcIdx][$d] ?? 0);
                }
            } elseif (isset($def['diff'])) {
                $baseIdx = $def['diff'][0];
                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $val = $results[$baseIdx][$d] ?? 0;
                    for ($i = 1; $i < count($def['diff']); $i++) {
                        $val -= ($results[$def['diff'][$i]][$d] ?? 0);
                    }
                    $results[$idx][$d] = max(0, $val);
                }
            } elseif (isset($def['pct'])) {
                $numIdx = $def['pct'][0];
                $denIdx = $def['pct'][1];
                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $den = $results[$denIdx][$d] ?? 0;
                    $results[$idx][$d] = $den > 0 ? round(($results[$numIdx][$d] / $den) * 100, 2) : 0;
                }
            }
        }

        return $results;
    }

    /**
     * Formatea los datos finales para la vista
     */
    private function formatFinalData(array $allDefs, array $results): array
    {
        $finalData = [];
        foreach ($allDefs as $idx => $def) {
            if ($def['hidden'] ?? false) continue;
            $finalData[] = [
                'label' => $def['label'],
                'days' => $results[$idx],
                'total' => array_sum($results[$idx]),
                'color' => $def['color'] ?? '',
                'isPct' => isset($def['pct']),
                'header' => $def['header'] ?? false,
                'is_manual' => $def['is_manual'] ?? false,
                'manual_key' => $def['manual_key'] ?? null
            ];
        }
        return $finalData;
    }

    /**
     * Prepara la metadata de los días (fines de semana)
     */
    private function prepareDayMeta(int $ano, int $mesNum, int $daysInMonth): array
    {
        $dayMeta = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dt = Carbon::create($ano, $mesNum, $d);
            $dayMeta[$d] = [
                'day' => $d,
                'isWeekend' => $dt->isWeekend()
            ];
        }
        return $dayMeta;
    }

    private function processDailyRecords(int $daysInMonth, $groupedRecords, $groupedAtenciones, array $at2rCodigos, array $ageRows, array $progRows, array &$results): void
    {
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $dayRecords = $groupedRecords->get($d, collect());
            if ($dayRecords->isEmpty())
                continue;

            $dayAtenciones = $groupedAtenciones->get($d, collect());
            $dayDiagnosticos = $dayRecords->filter(fn($r) => in_array($r->n_cod, $at2rCodigos));

            // Calcular Age Rows
            foreach ($ageRows as $idx => $def) {
                if (isset($def['match'])) {
                    foreach ($dayAtenciones as $r) {
                        if ($def['match']($r))
                            $results[$idx][$d]++;
                    }
                }
            }

            // Calcular Prog Rows (Lógica de Diagnósticos)
            $startIdx = count($ageRows);
            foreach ($progRows as $pIdx => $def) {
                if ($def['is_manual'] ?? false) continue;
                $absIdx = $startIdx + $pIdx;
                if (isset($def['match'])) {
                    foreach ($dayRecords as $r) {
                        if ($def['match']($r))
                            $results[$absIdx][$d]++;
                    }
                } elseif (isset($def['match_atencion'])) {
                    foreach ($dayAtenciones as $r) {
                        if ($def['match_atencion']($r))
                            $results[$absIdx][$d]++;
                    }
                } elseif (isset($def['code']) || isset($def['diag'])) {
                    foreach ($dayDiagnosticos as $r) {
                        $match = false;
                        if (isset($def['diag'])) {
                            $diags = is_array($def['diag']) ? $def['diag'] : [$def['diag']];
                            foreach ($diags as $diag) {
                                if (strtoupper(trim($diag)) === $r->n_diag) {
                                    $match = true;
                                    break;
                                }
                            }
                        } elseif (isset($def['code'])) {
                            $codes = is_array($def['code']) ? $def['code'] : [$def['code']];
                            if (in_array($r->n_cod, $codes))
                                $match = true;
                        }

                        if ($match) {
                            if (isset($def['cond']) && $r->n_cond_diag != strtoupper(trim($def['cond'])))
                                $match = false;
                            if (isset($def['age_max']) && strtoupper(trim($r->tipo)) == 'A' && $r->edad > $def['age_max'])
                                $match = false;
                            if ($match)
                                $results[$absIdx][$d]++;
                        }
                    }
                } elseif (isset($def['sum_diags'])) {
                    foreach ($dayDiagnosticos as $r) {
                        foreach ($def['sum_diags'] as $pair) {
                            $pCond = strtoupper(trim($pair[1]));
                            $search = strtoupper(trim($pair[0]));
                            if ((str_contains($r->n_diag, $search) || $r->n_diag === $search) && ($pCond == '*' || $r->n_cond_diag == $pCond)) {
                                $results[$absIdx][$d]++;
                            }
                        }
                    }
                }
            }
        }
    }

    private function buildAt2rAgeRows(): array
    {
        return [
            ['label' => 'Menores de 1 mes de 1a Vez', 'match' => fn($r) => $r->tipo == 'D' && $r->edad <= 28 && $r->cond == 'N'],
            ['label' => 'Menores de 1 mes subsiguiente', 'match' => fn($r) => $r->tipo == 'D' && $r->edad <= 28 && $r->cond == 'S'],
            ['label' => 'Total menores de 1 mes', 'sum' => [0, 1], 'color' => 'bg-warning-soft'],
            ['label' => '1 mes a 2 meses de 1a Vez', 'match' => fn($r) => $r->tipo == 'M' && $r->edad == 1 && $r->cond == 'N'],
            ['label' => '1 mes a 2 meses subsiguiente', 'match' => fn($r) => $r->tipo == 'M' && $r->edad == 1 && $r->cond == 'S'],
            ['label' => 'Total 1 a 2 meses', 'sum' => [3, 4], 'color' => 'bg-warning-soft'],
            ['label' => '2 meses a 1 año de 1a Vez', 'match' => fn($r) => ($r->tipo == 'M' && $r->edad >= 2 && $r->edad < 12) && $r->cond == 'N'],
            ['label' => '2 meses a 1 año subsiguiente', 'match' => fn($r) => ($r->tipo == 'M' && $r->edad >= 2 && $r->edad < 12) && $r->cond == 'S'],
            ['label' => 'Total 2 meses a 1 año', 'sum' => [6, 7]],
            ['label' => '1 año a < 5 años 1a Vez', 'match' => fn($r) => ($r->tipo == 'A' && $r->edad >= 1 && $r->edad < 5) && $r->cond == 'N'],
            ['label' => '1 año a < 5 años subsiguiente', 'match' => fn($r) => ($r->tipo == 'A' && $r->edad >= 1 && $r->edad < 5) && $r->cond == 'S'],
            ['label' => 'Total 1 a 4 años', 'sum' => [9, 10]],
            ['label' => 'Total Niños/as menores de 5 años Atendidos 1a vez', 'sum' => [0, 3, 6, 9], 'color' => 'bg-info-soft'],
            ['label' => 'Total Niños/as menores de 5 años Atendidos Subs.', 'sum' => [1, 4, 7, 10], 'color' => 'bg-info-soft'],
            ['label' => 'Total Niños/as menores de 5 años Atendidos', 'sum' => [12, 13], 'color' => 'bg-info-soft'],
            ['label' => '5 a 9 años 1a Vez', 'match' => fn($r) => $r->tipo == 'A' && $r->edad >= 5 && $r->edad <= 9 && $r->cond == 'N'],
            ['label' => '5 a 9 años subsiguiente', 'match' => fn($r) => $r->tipo == 'A' && $r->edad >= 5 && $r->edad <= 9 && $r->cond == 'S'],
            ['label' => 'Total 5 - 9 años', 'sum' => [15, 16]],
            ['label' => 'Total Pacientes', 'sum' => [14, 17], 'color' => 'bg-primary-soft'],
            ['label' => 'No. Atenciones de mujeres', 'match' => fn($r) => $r->n_sexo === 'M'],
            ['label' => 'No. Atenciones de hombres', 'match' => fn($r) => $r->n_sexo === 'H'],
            ['label' => 'Total atenciones a mujeres y hombres', 'sum' => [19, 20], 'color' => 'bg-info-soft'],
            ['label' => 'No. Consultas Espontáneas', 'match' => fn($r) => empty($r->referido_de)],
            ['label' => 'No. Consultas Referidas', 'match' => fn($r) => !empty($r->referido_de)],
            ['label' => 'Total de Consultas', 'sum' => [22, 23], 'color' => 'bg-info-soft'],
            ['label' => 'H: N < 5', 'sum' => [0, 3, 6, 9], 'hidden' => true],
            ['label' => 'H: S < 5', 'sum' => [1, 4, 7, 10], 'hidden' => true],
            ['label' => 'H: All < 5', 'sum' => [25, 26], 'hidden' => true],
        ];
    }

    private function buildAt2rProgRows(): array
    {
        return [
            ['label' => 'Número de Atenciones del Recién Nacido para Control Temprano antes de los 5 días', 'match_atencion' => fn($r) => $r->tipo == 'D' && $r->edad < 5, 'color' => 'bg-green-soft'],
            ['label' => 'No. De niños/as menores de 5 años con diarrea nuevo', 'diag' => ['DIARREAS SIN SANGRE', 'DIARREAS CON DESHIDRATACION', 'DIARREAS'], 'cond' => 'N', 'age_max' => 4],
            ['label' => 'No. De niños/as menores de 5 años con diarrea que acuden a cita de seguimiento', 'diag' => ['DIARREAS SIN SANGRE', 'DIARREAS CON DESHIDRATACION', 'DIARREAS'], 'cond' => 'S', 'age_max' => 4],
            ['label' => 'No. De Niños/as menores de 5 años con deshidratación rehidratados en la US', 'diag' => 'REHIDRATACION ORAL', 'age_max' => 4, 'color' => 'bg-green-soft', 'is_manual' => true, 'manual_key' => 'rehidratados'],
            ['label' => 'No. De Niños/as menores de 5 años con casos de Neumonía nuevos en el año', 'diag' => 'NEUMONIAS', 'cond' => 'N', 'age_max' => 4],
            ['label' => 'No. De niños/as menores de 5 años con Neumonía que acuden a su cita de Seguimiento', 'diag' => 'NEUMONIAS', 'cond' => 'S', 'age_max' => 4],
            ['label' => 'No. de niños/as menores de 5 años con algun grado de Síndrome anémico diagnosticado', 'diag' => 'REUNIÓN DE TRABAJO COMUNITARIO', 'age_max' => 4],
            ['label' => 'Número de menores de 5 años con crecimiento adecuado (Gráficas peso/talla y talla/edad)', 'diff' => [27, 37, 39, 40, 41], 'color' => 'bg-green-soft'],
            ['label' => 'Número de menores de 5 años sin desnutrición crónica (Gráfica talla/edad)', 'diff' => [27, 37, 39, 40, 41], 'color' => 'bg-green-soft'],
            ['label' => 'Número de menores de 5 años con baja talla y baja talla severa (Gráfica talla/edad)', 'match' => fn($r) => in_array($r->n_diag, ['BAJA TALLA', 'BAJA TALLA SEVERA', 'TALLA BAJA', 'TALLA BAJA SEVERA']) && ($r->tipo == 'D' || $r->tipo == 'M' || ($r->tipo == 'A' && $r->edad < 5)), 'color' => 'bg-green-soft'],
            ['label' => 'Número de menores de 5 años sin desnutrición aguda ni sobrepeso/obesidad (Gráfica peso/talla)', 'diff' => [27, 37, 39, 40, 41], 'color' => 'bg-green-soft'],
            ['label' => 'Número de menores de 5 años emaciados y severamente emaciados (Gráfica peso/talla)', 'match' => fn($r) => in_array($r->n_diag, ['DESNUTRICION AGUDA', 'DESNUTRICION AGUDA SEVERA', 'EMACIADO', 'EMACIADO SEVERO']) && ($r->tipo == 'D' || $r->tipo == 'M' || ($r->tipo == 'A' && $r->edad < 5)), 'color' => 'bg-green-soft'],
            ['label' => 'Número de menores de 5 años con sobrepeso y obesidad (Gráfica peso para la Longitud/Talla mayor o igual a +2 DE)', 'diag' => 'OBESIDAD', 'age_max' => 4, 'color' => 'bg-green-soft'],
            ['label' => 'Número de menores de 5 años con crecimiento inadecuado persistente (en 2 controles sucesivos) en el mes.', 'is_manual' => true, 'manual_key' => 'inadecuado', 'color' => 'bg-green-soft'],
            ['label' => 'Número de menores de 5 años con Discapacidad Nuevos', 'diag' => ['DISCAPACIDAD', 'DISCAPACITADO', 'DISCAPACIDAD NUEVO'], 'cond' => 'N', 'age_max' => 4, 'color' => 'bg-green-soft'],
            ['label' => 'Número de menores de 5 años con Probable Alteración del Desarrollo', 'diag' => ['PROBABLE ALTERACION DEL DESARROLLO', 'ALTERACION DEL DESARROLLO'], 'age_max' => 4, 'color' => 'bg-green-soft'],
            ['label' => 'Número de mujeres atendidas que se les entregó Anticonceptivo Oral Combinado', 'diag' => ['ANTICONCEPTIVO ORAL COMBINADO', 'ANTICONCEPTIVO ORAL 1 CICLO', 'ANTICONCEPTIVO ORAL 3 CICLO', 'ANTICONCEPTIVO ORAL 6 CICLO'], 'code' => ['1001', '116', '117', '118'], 'color' => 'bg-purple-lite'],
            ['label' => 'Número de mujeres atendidas que se les entregó Anticonceptivos Orales con Progestina sola', 'diag' => 'ANTICONCEPTIVOS ORALES CON PROGESTINA SOLA', 'code' => '1002', 'color' => 'bg-purple-lite'],
            ['label' => 'Número de mujeres que se les aplicó inyectables trimestral', 'diag' => ['MUJERES QUE SE LES APLICÓ INYECTABLES TRIMESTRAL', 'DEPO PROVERAS APLICADAS'], 'code' => '1003', 'color' => 'bg-purple-lite'],
            ['label' => 'Número de mujeres que se les aplicó autoinyectables trimestral', 'diag' => 'MUJERES QUE SE LES APLICÓ AUTOINYECTABLES TRIMESTRAL', 'code' => '1004', 'color' => 'bg-purple-lite'],
            ['label' => 'Número de DIU con cobre insertados', 'diag' => ['DIU CON COBRE INSERTADOS', 'DIU INSERTADOS', 'DIU CON COBRE'], 'code' => ['1005', '122'], 'color' => 'bg-purple-lite'],
            ['label' => 'Número de DIU con levonorgestrel insertados', 'diag' => ['DIU CON LEVONORGESTREL INSERTADOS', 'DIU CON LEVONORGESTREL', 'DIU LNG', 'MIRENA'], 'code' => '1006', 'color' => 'bg-purple-lite'],
            ['label' => 'Número de mujeres que se les insertó Implante con levonorgestrel 5 años (JADELLE)', 'diag' => 'INSERCIÓN DE IMPLANTE CON LEVONORGESTREL 5 AÑOS (JADELLE)', 'color' => 'bg-purple-lite'],
            ['label' => 'Número de mujeres que se les insertó Implante con Etonogestrel 3 años (NXT)', 'diag' => ['INSERCIÓN DE IMPLANTE CON ETONOGESTREL 3 AÑOS (NXT)', 'IMPLANTE SUB DERMICO', 'IMPLANTE CON ETONOGESTREL', 'NXT'], 'color' => 'bg-purple-lite'],
            ['label' => 'Número de mujeres que se les retiró implante', 'diag' => ['RETIRO DE IMPLANTE', 'RETIRO IMPLANON'], 'color' => 'bg-purple-lite'],
            ['label' => 'Número de mujeres que se les retiró DIU', 'diag' => 'RETIRO DE DIU', 'color' => 'bg-purple-lite'],
            [
                'label' => 'Detección de Cáncer Cérvico Uterino',
                'match' => fn($r) => (
                    in_array($r->n_cod, ['107', '108', '229', '245', '246', '247', '248', '249', '250', '251', '252', '253', '254', '255', '256', '257', '258', '259', '260', '261', '262', '263', '265', '271', '275', '277', '278']) ||
                    str_contains($r->n_diag, 'IVAA') ||
                    str_contains($r->n_diag, 'VPH') ||
                    str_contains($r->n_diag, 'CITOLOGIA') ||
                    str_contains($r->n_diag, 'CRIOTERAPIA')
                ),
                'color' => 'bg-purple-lite'
            ],
            ['label' => 'Número de consejerías de planificación familiar brindadas', 'diag' => ['CONSEJERÍAS DE PLANIFICACIÓN FAMILIAR BRINDADAS', 'CONSEJERIA PF/EMB/ZIKA'], 'color' => 'bg-purple-lite'],
            ['label' => 'Número de mujeres que se les realizó AQV Ambulatoria', 'diag' => ['AQV AMBULATORIA MUJERES', 'AQV REALIZADOS', 'AQV'], 'color' => 'bg-purple-lite'],
            ['label' => 'Número de hombres que se les realizó AQV Ambulatoria', 'diag' => ['AQV AMBULATORIA HOMBRES', 'VASECTORMÍAS REALIZADAS'], 'color' => 'bg-purple-lite'],
            ['label' => 'Número de mujeres que se les brindó PAE', 'diag' => 'PAE', 'color' => 'bg-purple-lite'],
            ['label' => 'Número de personas atendidas que se les entregó condones', 'diag' => ['CONDONES ENTREGADOS', 'CONDONES 10 UNIDADES', 'CONDONES 30 UNIDADES'], 'color' => 'bg-purple-lite'],
            ['label' => 'Número de mujeres atendidas por aborto ambulatorio', 'diag' => ['ABORTO AMBULATORIO', 'AMEU REALIZADAS'], 'color' => 'bg-purple-lite'],
            [
                'label' => 'Atención Prenatal NUEVA en las edades de 10 a 19 años (Adolescentes)',
                'match' => fn($r) => (
                    in_array($r->n_cod, ['104', '105', '106']) ||
                    str_contains($r->n_diag, 'ATENCION PRENATAL')
                ) && $r->n_cond_diag == 'N' && $r->tipo == 'A' && $r->edad >= 10 && $r->edad <= 19,
                'color' => 'bg-fuchsia-soft'
            ],
            [
                'label' => 'Atención Prenatal NUEVA en las primeras 12 Semanas de Gestación',
                'match' => fn($r) => (
                    $r->n_cod == '104' ||
                    (str_contains($r->n_diag, 'ATENCION PRENATAL') && str_contains($r->n_diag, 'ANTES'))
                ) && $r->n_cond_diag == 'N',
                'color' => 'bg-fuchsia-soft'
            ],
            [
                'label' => 'Atención prenatal NUEVA después de las 12 semanas de gestación',
                'match' => fn($r) => (
                    $r->n_cod == '105' ||
                    (str_contains($r->n_diag, 'ATENCION PRENATAL') && str_contains($r->n_diag, 'DESPUES'))
                ) && $r->n_cond_diag == 'N',
                'color' => 'bg-fuchsia-soft'
            ],
            [
                'label' => 'Total de atenciones prenatales subsiguientes',
                'match' => fn($r) => (
                    in_array($r->n_cod, ['104', '105', '106']) ||
                    str_contains($r->n_diag, 'ATENCION PRENATAL')
                ) && $r->n_cond_diag == 'S',
                'color' => 'bg-fuchsia-soft'
            ],
            [
                'label' => 'Número de atenciones puerperales entre los 3 a 7 días',
                'match' => fn($r) => (
                    in_array($r->n_cod, ['120', '114']) ||
                    (str_contains($r->n_diag, 'ATENCIÓN PUERPERAL') && str_contains($r->n_diag, 'PRIMEROS 7'))
                ) && $r->n_cond_diag == 'N',
                'color' => 'bg-orange-soft'
            ],
            [
                'label' => 'Número de Atenciones puerperales después de los 7 días',
                'match' => fn($r) => (
                    in_array($r->n_cod, ['121', '115']) ||
                    (str_contains($r->n_diag, 'ATENCIÓN PUERPERAL') && str_contains($r->n_diag, 'DESPUES DE LOS 7'))
                ) && $r->n_cond_diag == 'N',
                'color' => 'bg-orange-soft'
            ],
            [
                'label' => 'Controles Puerperales',
                'match' => fn($r) => (
                    in_array($r->n_cod, ['120', '121']) ||
                    str_contains($r->n_diag, 'PUERPERAL')
                ) && $r->n_cond_diag == 'S',
                'color' => 'bg-orange-soft'
            ],
            ['label' => 'Número de atenciones por Violencia Sexual', 'diag' => ['VIOLENCIA SEXUAL', 'ABUSO SEXUAL'], 'color' => 'bg-info-soft'],
            ['label' => 'Número de atencion de adolescentes de 10 a 19 años mujeres', 'match_atencion' => fn($r) => $r->tipo == 'A' && $r->edad >= 10 && $r->edad <= 19 && $r->n_sexo == 'M', 'color' => 'bg-info-soft'],
            ['label' => 'Número de atencion de adolescentes de 10 a 19 años varones', 'match_atencion' => fn($r) => $r->tipo == 'A' && $r->edad >= 10 && $r->edad <= 19 && $r->n_sexo == 'H', 'color' => 'bg-info-soft'],
            ['label' => 'Detección de Casos presuntivos de Tuberculosis', 'diag' => ['DETECCIÓN DE CASOS PRESUNTIVOS DE TUBERCULOSIS', 'TUBERCULOSIS', 'SINTOMATICO RESPIRATORIO'], 'cond' => 'N', 'color' => 'bg-info-soft'],
            ['label' => 'Número de atenciones brindadas Nuevas de Diabetes Mellitus', 'match' => fn($r) => str_contains($r->n_diag, 'DIABETES') && $r->n_cond_diag == 'N', 'color' => 'bg-green-soft'],
            ['label' => 'Número de atenciones brindadas Subsiguientes de Diabetes Mellitus', 'match' => fn($r) => str_contains($r->n_diag, 'DIABETES') && $r->n_cond_diag == 'S', 'color' => 'bg-green-soft'],
            ['label' => 'Número de atenciones brindadas Nuevas de Hipertensión Arterial', 'diag' => ['ATENCIONES BRINDADAS NUEVAS DE HIPERTENSIÓN ARTERIAL', 'HIPERTENSION ARTERIAL'], 'cond' => 'N', 'color' => 'bg-green-soft'],
            ['label' => 'Número de atenciones brindadas Subsiguientes de Hipertensión Arterial', 'diag' => ['ATENCIONES BRINDADAS SUBSIGUIENTES DE HIPERTENSIÓN ARTERIAL', 'HIPERTENSION ARTERIAL'], 'cond' => 'S', 'color' => 'bg-green-soft'],
            ['label' => 'Número de atenciones brindadas Nuevas de Enfermedad Renal Crónica', 'match' => fn($r) => (in_array($r->n_diag, ['ENFERMEDAD RENAL CRONICA', 'ENFERMEDAD RENAL CRÓNICA']) || $r->n_cod == '2015') && $r->n_cond_diag == 'N', 'color' => 'bg-orange-soft'],
            ['label' => 'Número de atenciones brindadas Subsiguientes de Enfermedad Renal Crónica', 'match' => fn($r) => (in_array($r->n_diag, ['ENFERMEDAD RENAL CRONICA', 'ENFERMEDAD RENAL CRÓNICA']) || $r->n_cod == '2015') && $r->n_cond_diag == 'S', 'color' => 'bg-orange-soft'],
            ['label' => 'Número de atenciones brindadas Nuevas de Cáncer Cérvico Uterino', 'match' => fn($r) => (in_array($r->n_cod, ['107', '108', '229', '245', '246', '247', '248', '249', '250', '251', '252', '253', '254', '255', '256', '257', '258', '259', '260', '261', '262', '263', '265', '271', '275', '277', '278']) || str_contains($r->n_diag, 'IVAA') || str_contains($r->n_diag, 'VPH') || str_contains($r->n_diag, 'CITOLOGIA') || str_contains($r->n_diag, 'CRIOTERAPIA')) && $r->n_cond_diag == 'N', 'color' => 'bg-orange-soft'],
            ['label' => 'Número de atenciones brindadas Subsiguientes de Cáncer Cérvico Uterino', 'match' => fn($r) => (in_array($r->n_cod, ['107', '108', '229', '245', '246', '247', '248', '249', '250', '251', '252', '253', '254', '255', '256', '257', '258', '259', '260', '261', '262', '263', '265', '271', '275', '277', '278']) || str_contains($r->n_diag, 'IVAA') || str_contains($r->n_diag, 'VPH') || str_contains($r->n_diag, 'CITOLOGIA') || str_contains($r->n_diag, 'CRIOTERAPIA')) && $r->n_cond_diag == 'S', 'color' => 'bg-orange-soft'],
            ['label' => 'Número de atenciones brindadas Nuevas de Cáncer Priorizados', 'match' => fn($r) => (str_contains($r->n_diag, 'CANCER PRIORIZADO') || str_contains($r->n_diag, 'CÁNCER PRIORIZADO') || $r->n_cod == '2016') && $r->n_cond_diag == 'N', 'color' => 'bg-orange-soft'],
            ['label' => 'Número de atenciones brindadas Subsiguientes de Cáncer Priorizados', 'match' => fn($r) => (str_contains($r->n_diag, 'CANCER PRIORIZADO') || str_contains($r->n_diag, 'CÁNCER PRIORIZADO') || $r->n_cod == '2016') && $r->n_cond_diag == 'S', 'color' => 'bg-orange-soft'],
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
            ['label' => 'Otro', 'diag' => 'OTRA ETNIA'],
            ['label' => 'No Sabe/ Ninguno', 'diag' => 'NO SABE/ NINGUNO ( ETNIA)'],
        ];
    }

    private function processManualFields(array &$results, array $progRows, int $startIdx, int $daysInMonth, int $ano, string $mes, ?string $medico): void
    {
        foreach ($progRows as $pIdx => $def) {
            if (!empty($def['is_manual'])) {
                $absIdx = $startIdx + $pIdx;
                $manualKey = $def['manual_key'];
                $settingKey = "at2_manual_{$manualKey}_{$ano}_{$mes}_" . ($medico ?: 'TODOS');
                
                $setting = Setting::where('key', $settingKey)->first();
                if ($setting) {
                    $manualValues = json_decode($setting->value, true);
                    for ($d = 1; $d <= $daysInMonth; $d++) {
                        $results[$absIdx][$d] = $manualValues[$d] ?? 0;
                    }
                }
            }
        }
    }
}
