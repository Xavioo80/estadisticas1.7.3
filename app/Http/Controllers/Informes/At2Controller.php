<?php

namespace App\Http\Controllers\Informes;

use App\Http\Controllers\Controller;
use App\Models\RegistroGlobal;
use App\Models\Setting;
use App\Traits\InformesHelperTrait;
use Illuminate\Http\Request;
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

        $registrosRaw = $this->fetchRecords($medico, $ano, $mesStr);

        $ageRows = $this->getAgeRows();
        $progRows = $this->getProgRows();
        $allDefs = array_merge($ageRows, $progRows);

        $results = $this->calculateReportResults($daysInMonth, $registrosRaw, $ageRows, $progRows, $allDefs, $ano, $mesStr, $medico);

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
        $manualKey = $request->input('manual_key', 'rehidratados');
        $day = $request->input('day');
        $value = $request->input('value', 0);

        if (!preg_match('/^[a-zA-Z0-9_-]+$/', $manualKey)) {
            return response()->json(['success' => false, 'message' => 'manual_key contiene caracteres inválidos'], 400);
        }

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

    private function resolveDateParams(Request $request): array
    {
        $ano = (int) $request->input('ano', date('Y'));
        $mesStr = $request->input('mes', '');
        if (empty($mesStr)) {
            $mesStr = $this->resolverMesPorDefecto($ano, true);
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

    private function getFilterData(int $ano, string $mesStr): array
    {
        $data = $this->getAnosMesesDisponiblesInformes();
        $anos = $data['anos'];
        $meses = $data['meses'];

        $medicos = RegistroGlobal::distinct()
            ->where('ano', $ano)
            ->where('mes', $mesStr)
            ->whereNotNull('medico')
            ->where('medico', '!=', '')
            ->orderBy('medico')
            ->pluck('medico');

        return compact('anos', 'meses', 'medicos');
    }

    private function fetchRecords(?string $medico, int $ano, string $mesStr)
    {
        if (!$medico) return collect();

        return RegistroGlobal::query()
            ->where('medico', $medico)
            ->where('ano', $ano)
            ->where('mes', $mesStr)
            ->get();
    }

    private function calculateReportResults(int $daysInMonth, $registrosRaw, array $ageRows, array $progRows, array $allDefs, int $ano, string $mesStr, ?string $medico): array
    {
        $results = [];
        foreach ($allDefs as $idx => $def) {
            $results[$idx] = array_fill(1, $daysInMonth, 0);
        }

        $countedInRow = [];

        foreach ($registrosRaw as $r) {
            $d = (int) Carbon::parse($r->fecha)->day;
            if ($d < 1 || $d > $daysInMonth) continue;

            // 1. Procesar ageRows
            foreach ($ageRows as $idx => $def) {
                if (isset($def['match']) && $def['match']($r)) {
                    $results[$idx][$d]++;
                }
            }

            // 2. Procesar progRows
            $startIdx = count($ageRows);
            foreach ($progRows as $pIdx => $def) {
                $absIdx = $startIdx + $pIdx;

                if (isset($def['match_atencion'])) {
                    if (!isset($countedInRow[$absIdx][$r->id]) && $def['match_atencion']($r)) {
                        $results[$absIdx][$d]++;
                        $countedInRow[$absIdx][$r->id] = true;
                    }
                } elseif (isset($def['match']) || isset($def['diag']) || isset($def['code'])) {
                    if (isset($countedInRow[$absIdx][$r->id])) continue;

                    for ($i = 1; $i <= 7; $i++) {
                        $cod = trim((string)($r->{"cod_{$i}"} ?? ''));
                        $diag = trim((string)($r->{"diagnostico_{$i}"} ?? ''));
                        $cond = trim((string)($r->{"cond_{$i}"} ?? ''));
                        if ($cod === '' && $diag === '') continue;

                        $diagObj = (object)[
                            'reg_id' => $r->id,
                            'cod' => $cod,
                            'diagnostico' => $diag,
                            'cond_diag' => $cond,
                            'prof' => $r->prof,
                            'medico' => $r->medico,
                            'fecha' => $r->fecha,
                            'edad' => $r->edad,
                            'tipo' => $r->tipo,
                            'sexo' => $r->sexo,
                            'cond' => $r->cond,
                        ];

                        $match = false;
                        if (isset($def['match'])) {
                            if ($def['match']($diagObj)) $match = true;
                        } elseif (isset($def['diag'])) {
                            $diags = is_array($def['diag']) ? $def['diag'] : [$def['diag']];
                            $cleanDiags = array_map(fn($x) => $this->cleanDiag($x), $diags);
                            if (in_array($this->cleanDiag($diag), $cleanDiags)) $match = true;
                        } elseif (isset($def['code'])) {
                            $codes = is_array($def['code']) ? $def['code'] : [$def['code']];
                            if (in_array($cod, $codes)) $match = true;
                        }

                        if ($match) {
                            if (isset($def['cond'])) {
                                $rCond = strtoupper(trim($cond !== '' ? $cond : ($r->cond ?? '')));
                                if ($rCond !== strtoupper($def['cond'])) $match = false;
                            }
                            if ($match && isset($def['age_max'])) {
                                if (strtoupper(trim($r->tipo)) == 'A' && $r->edad > $def['age_max']) $match = false;
                            }
                            if ($match) {
                                $results[$absIdx][$d]++;
                                $countedInRow[$absIdx][$r->id] = true;
                                break;
                            }
                        }
                    }
                }
            }
        }

        // Cargar campos manuales si existen
        $startIdx = count($ageRows);
        foreach ($progRows as $pIdx => $def) {
            $absIdx = $startIdx + $pIdx;
            if (!empty($def['is_manual'])) {
                $manualKey = $def['manual_key'] ?? 'rehidratados';
                $settingKey = "at2_manual_{$manualKey}_{$ano}_{$mesStr}_{$medico}";
                $setting = Setting::where('key', $settingKey)->first();
                if ($setting) {
                    $manualData = json_decode($setting->value, true) ?: [];
                    for ($d = 1; $d <= $daysInMonth; $d++) {
                        $results[$absIdx][$d] = (int)($manualData[$d] ?? 0);
                    }
                }
            }
        }

        // Post-procesamiento: sum, sum_rows, diff
        foreach ($allDefs as $idx => $def) {
            if (isset($def['sum'])) {
                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $results[$idx][$d] = 0;
                    foreach ($def['sum'] as $srcIdx) {
                        $results[$idx][$d] += ($results[$srcIdx][$d] ?? 0);
                    }
                }
            } elseif (isset($def['sum_rows'])) {
                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $results[$idx][$d] = 0;
                    foreach ($def['sum_rows'] as $srcIdx) {
                        $results[$idx][$d] += ($results[$srcIdx][$d] ?? 0);
                    }
                }
            } elseif (isset($def['diff'])) {
                $baseIdx = $def['diff'][0];
                for ($d = 1; $d <= $daysInMonth; $d++) {
                    $val = $results[$baseIdx][$d] ?? 0;
                    for ($i = 1; $i < count($def['diff']); $i++) {
                        $srcIdx = $def['diff'][$i];
                        $val -= ($results[$srcIdx][$d] ?? 0);
                    }
                    $results[$idx][$d] = max(0, $val);
                }
            }
        }

        return $results;
    }

    private function formatFinalData(array $allDefs, array $results): array
    {
        $finalData = [];
        foreach ($allDefs as $idx => $def) {
            if ($def['hidden'] ?? false) continue;
            $finalData[] = [
                'label' => $def['label'],
                'days' => $results[$idx] ?? array_fill(1, 31, 0),
                'total' => array_sum($results[$idx] ?? []),
                'color' => $def['color'] ?? '',
                'header' => $def['header'] ?? false,
                'is_manual' => $def['is_manual'] ?? false,
                'manual_key' => $def['manual_key'] ?? null
            ];
        }
        return $finalData;
    }

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

    private function isPrenatalNuevaRecord($r): bool
    {
        for ($i = 1; $i <= 7; $i++) {
            $diagProp = "diagnostico_{$i}";
            $condProp = "cond_{$i}";
            $diag = strtoupper(trim($r->{$diagProp} ?? ''));
            $cond = strtoupper(trim($r->{$condProp} ?? ''));
            $diagClean = $this->cleanDiag($diag);
            if ((str_contains($diagClean, 'PRENATAL') || str_contains($diagClean, 'EMBARAZ')) && $cond === 'N') {
                return true;
            }
        }
        return false;
    }

    private function getAgeRows(): array
    {
        return [
            ['label' => 'menores de 1 mes primera vez', 'match' => fn($r) => (strtoupper(trim($r->tipo ?? '')) === 'D' || (strtoupper(trim($r->tipo ?? '')) === 'M' && (int)$r->edad === 0)) && $this->getCondRecord($r) === 'N'],
            ['label' => 'menores de 1 mes subsiguiente', 'match' => fn($r) => (strtoupper(trim($r->tipo ?? '')) === 'D' || (strtoupper(trim($r->tipo ?? '')) === 'M' && (int)$r->edad === 0)) && $this->getCondRecord($r) === 'S'],
            ['label' => '1 mes a un año primera vez', 'match' => fn($r) => strtoupper(trim($r->tipo ?? '')) === 'M' && (int)$r->edad >= 1 && $this->getCondRecord($r) === 'N'],
            ['label' => '1 mes a un año subsiguiente', 'match' => fn($r) => strtoupper(trim($r->tipo ?? '')) === 'M' && (int)$r->edad >= 1 && $this->getCondRecord($r) === 'S'],
            ['label' => '1-4 años primera vez', 'match' => fn($r) => strtoupper(trim($r->tipo ?? '')) === 'A' && (int)$r->edad <= 4 && $this->getCondRecord($r) === 'N'],
            ['label' => '1-4 años subsiguiente', 'match' => fn($r) => strtoupper(trim($r->tipo ?? '')) === 'A' && (int)$r->edad <= 4 && $this->getCondRecord($r) === 'S'],
            ['label' => '5-9 años primera vez', 'match' => fn($r) => strtoupper(trim($r->tipo ?? '')) === 'A' && (int)$r->edad >= 5 && (int)$r->edad <= 9 && $this->getCondRecord($r) === 'N'],
            ['label' => '5-9 años subsiguiente', 'match' => fn($r) => strtoupper(trim($r->tipo ?? '')) === 'A' && (int)$r->edad >= 5 && (int)$r->edad <= 9 && $this->getCondRecord($r) === 'S'],
            ['label' => '10-14 años primera vez', 'match' => fn($r) => strtoupper(trim($r->tipo ?? '')) === 'A' && (int)$r->edad >= 10 && (int)$r->edad <= 14 && $this->getCondRecord($r) === 'N'],
            ['label' => '10-14 años subsiguiente', 'match' => fn($r) => strtoupper(trim($r->tipo ?? '')) === 'A' && (int)$r->edad >= 10 && (int)$r->edad <= 14 && $this->getCondRecord($r) === 'S'],
            ['label' => '15-19 años primera vez', 'match' => fn($r) => strtoupper(trim($r->tipo ?? '')) === 'A' && (int)$r->edad >= 15 && (int)$r->edad <= 19 && $this->getCondRecord($r) === 'N'],
            ['label' => '15-19 años sub siguiente', 'match' => fn($r) => strtoupper(trim($r->tipo ?? '')) === 'A' && (int)$r->edad >= 15 && (int)$r->edad <= 19 && $this->getCondRecord($r) === 'S'],
            ['label' => '20-49 años primera vez', 'match' => fn($r) => strtoupper(trim($r->tipo ?? '')) === 'A' && (int)$r->edad >= 20 && (int)$r->edad <= 49 && $this->getCondRecord($r) === 'N'],
            ['label' => '20-49 años subsiguiente', 'match' => fn($r) => strtoupper(trim($r->tipo ?? '')) === 'A' && (int)$r->edad >= 20 && (int)$r->edad <= 49 && $this->getCondRecord($r) === 'S'],
            ['label' => '50-59 años primera vez', 'match' => fn($r) => strtoupper(trim($r->tipo ?? '')) === 'A' && (int)$r->edad >= 50 && (int)$r->edad <= 59 && $this->getCondRecord($r) === 'N'],
            ['label' => '50-59 años subsiguiente', 'match' => fn($r) => strtoupper(trim($r->tipo ?? '')) === 'A' && (int)$r->edad >= 50 && (int)$r->edad <= 59 && $this->getCondRecord($r) === 'S'],
            ['label' => '60...- años primera vez', 'match' => fn($r) => strtoupper(trim($r->tipo ?? '')) === 'A' && (int)$r->edad >= 60 && $this->getCondRecord($r) === 'N'],
            ['label' => '60...- años subsiguiente', 'match' => fn($r) => strtoupper(trim($r->tipo ?? '')) === 'A' && (int)$r->edad >= 60 && $this->getCondRecord($r) === 'S'],
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

    private function getProgRows(): array
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
            ['label' => 'No. De niños/as menores de 5 años con diarrea nuevo', 'diag' => ['DIARREAS', 'DIARREAS CON DESHIDRATACION', 'DIARREAS SIN SANGRE'], 'cond' => 'N', 'age_max' => 4, 'color' => 'bg-green-soft'],
            ['label' => 'No. De niños/as menores de 5 años con diarrea que acuden a cita de seguimiento', 'diag' => ['DIARREAS', 'DIARREAS CON DESHIDRATACION', 'DIARREAS SIN SANGRE'], 'cond' => 'S', 'age_max' => 4, 'color' => 'bg-green-soft'],
            ['label' => 'No. De Niños/as menores de 5 años con deshidratación rehidratados en la US', 'diag' => 'REHIDRATACION ORAL', 'age_max' => 4, 'color' => 'bg-green-soft', 'is_manual' => true, 'manual_key' => 'rehidratados'],
            ['label' => 'No. De Niños/as menores de 5 años con casos de Neumonía nuevos en el año', 'diag' => ['NEUMONIAS', 'NEUMONIAS/BRONCONEUMONIAS'], 'cond' => 'N', 'age_max' => 4, 'color' => 'bg-green-soft'],
            ['label' => 'No. De Niños/as menores de 5 años con Neumonía que acuden a su cita de Seguimiento', 'diag' => ['NEUMONIAS', 'NEUMONIAS/BRONCONEUMONIAS'], 'cond' => 'S', 'age_max' => 4, 'color' => 'bg-green-soft'],
            ['label' => 'No. de niños/as menores de 5 años con algun grado de Síndrome anémico diagnosticado', 'diag' => 'ANEMIAS', 'cond' => 'N', 'age_max' => 4, 'color' => 'bg-green-soft'],
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
            ['label' => 'Número de mujeres atendidas que se les entregó Anticonceptivos Orales con Progestina sola', 'diag' => 'ANTICONCEPTIVOS ORALES CON PROGESTINA SOLA', 'color' => 'bg-purple-lite'],
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
                    $diagClean = $this->cleanDiag($r->diagnostico ?? '');
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
