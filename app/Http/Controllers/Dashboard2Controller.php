<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\RegistroGlobal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class Dashboard2Controller extends Controller
{
    public function index(Request $request)
    {
        $selectedAno = $request->input('ano', [(string) date('Y')]); // Modificado para soportar array
        if (!is_array($selectedAno)) {
            $selectedAno = [$selectedAno];
        }

        $reqMes = $request->input('mes');
        $selectedMes = ($reqMes === null || $reqMes === '') ? 'all' : $reqMes;
        $viewType = $request->input('view_type', 'weeks');
        $selectedCond = $request->input('cond_filtro', ''); // Nuevo filtro
        $selectedColonia = $request->input('colonia_especifica', ''); // Nueva variable

        // 2. Catálogos para filtros
        $aniosDisponibles = RegistroGlobal::select('ano')->distinct()->orderBy('ano', 'desc')->pluck('ano');

        $mesesTraduccion = [
            'enero' => 'Enero',
            'febrero' => 'Febrero',
            'marzo' => 'Marzo',
            'abril' => 'Abril',
            'mayo' => 'Mayo',
            'junio' => 'Junio',
            'julio' => 'Julio',
            'agosto' => 'Agosto',
            'septiembre' => 'Septiembre',
            'octubre' => 'Octubre',
            'noviembre' => 'Noviembre',
            'diciembre' => 'Diciembre',
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre'
        ];

        $mesesNumericos = [
            'enero' => 1,
            'febrero' => 2,
            'marzo' => 3,
            'abril' => 4,
            'mayo' => 5,
            'junio' => 6,
            'julio' => 7,
            'agosto' => 8,
            'septiembre' => 9,
            'octubre' => 10,
            'noviembre' => 11,
            'diciembre' => 12,
            '1' => 1,
            '2' => 2,
            '3' => 3,
            '4' => 4,
            '5' => 5,
            '6' => 6,
            '7' => 7,
            '8' => 8,
            '9' => 9,
            '10' => 10,
            '11' => 11,
            '12' => 12
        ];

        $mesesDisponibles = RegistroGlobal::select('mes')->distinct()->get()
            ->sortBy(function ($item) use ($mesesNumericos) {
                $m = strtolower($item->mes);
                return $mesesNumericos[$m] ?? 99;
            })
            ->map(function ($item) use ($mesesTraduccion) {
                $val = is_numeric($item->mes) ? (int) $item->mes : strtolower($item->mes);
                return (object) ['id' => $item->mes, 'nombre' => $mesesTraduccion[$val] ?? ucfirst($item->mes)];
            });

        // Obtener diagnósticos optimizados usando SQL UNION
        $diagnosticos = $this->getDiagnosticosDinamicos($selectedAno, $selectedMes, $selectedCond);

        $selectedCode = $request->input('codigo');
        if (!$selectedCode || !in_array($selectedCode, $diagnosticos)) {
            $selectedCode = count($diagnosticos) > 0 ? $diagnosticos[0] : '';
        }
        $patologiaName = $selectedCode ?: 'Sin datos';

        $data = $this->generateChartData($selectedAno, $selectedMes, $viewType, $selectedCode, $mesesNumericos, $patologiaName, $selectedCond, $selectedColonia);

        return view('dashboard2', array_merge($data, compact(
            'selectedAno',
            'selectedMes',
            'viewType',
            'diagnosticos',
            'selectedCode',
            'patologiaName',
            'aniosDisponibles',
            'mesesDisponibles',
            'selectedCond',
            'selectedColonia'
        )));
    }

    public function getData(Request $request)
    {
        $selectedAno = $request->input('ano', ['all']);
        if (!is_array($selectedAno)) {
            $selectedAno = explode(',', $selectedAno);
        }

        $reqMes = $request->input('mes');
        $selectedMes = ($reqMes === null || $reqMes === '') ? 'all' : $reqMes;
        $viewType = $request->input('view_type', 'weeks');
        $selectedCond = $request->input('cond_filtro', ''); // Nuevo filtro
        $selectedColonia = $request->input('colonia_especifica', '');

        $diagnosticos = $this->getDiagnosticosDinamicos($selectedAno, $selectedMes, $selectedCond);

        $selectedCode = $request->input('codigo');
        if (!$selectedCode || !in_array($selectedCode, $diagnosticos)) {
            $selectedCode = count($diagnosticos) > 0 ? $diagnosticos[0] : '';
        }
        $patologiaName = $selectedCode ?: 'Sin datos';

        $mesesNumericos = [
            'enero' => 1,
            'febrero' => 2,
            'marzo' => 3,
            'abril' => 4,
            'mayo' => 5,
            'junio' => 6,
            'julio' => 7,
            'agosto' => 8,
            'septiembre' => 9,
            'octubre' => 10,
            'noviembre' => 11,
            'diciembre' => 12,
            '1' => 1,
            '2' => 2,
            '3' => 3,
            '4' => 4,
            '5' => 5,
            '6' => 6,
            '7' => 7,
            '8' => 8,
            '9' => 9,
            '10' => 10,
            '11' => 11,
            '12' => 12
        ];

        $data = $this->generateChartData($selectedAno, $selectedMes, $viewType, $selectedCode, $mesesNumericos, $patologiaName, $selectedCond, $selectedColonia);

        return response()->json(array_merge($data, [
            'diagnosticos' => $diagnosticos,
            'selectedCode' => $selectedCode,
            'patologiaName' => $patologiaName,
        ]));
    }

    private function getDiagnosticosDinamicos($selectedAnoArr, $selectedMes, $selectedCond = '')
    {
        $cacheKey = 'diagnosticos_dinamicos_' . md5(json_encode($selectedAnoArr) . '_' . $selectedMes . '_' . $selectedCond);

        return Cache::remember($cacheKey, 3600, function () use ($selectedAnoArr, $selectedMes, $selectedCond) {
            $queries = [];
            for ($i = 1; $i <= 7; $i++) {
                $q = DB::table('registros_globales')
                    ->select("diagnostico_{$i} as d")
                    ->whereNotNull("diagnostico_{$i}")
                    ->where("diagnostico_{$i}", '!=', '');

                if (!in_array('all', $selectedAnoArr)) {
                    $q->whereIn('ano', $selectedAnoArr);
                }
                if ($selectedMes !== 'all') {
                    $q->where('mes', $selectedMes);
                }
                if (!empty($selectedCond)) {
                    $q->where('cond_' . $i, $selectedCond);
                }
                $queries[] = $q;
            }

            $firstQuery = array_shift($queries);
            foreach ($queries as $q) {
                $firstQuery->union($q);
            }

            $diagnosticos = $firstQuery->pluck('d')->toArray();
            $diagnosticos = array_unique($diagnosticos);
            sort($diagnosticos);
            return $diagnosticos;
        });
    }

    private function generateChartData($selectedAnoArr, $selectedMes, $viewType, $selectedCode, $mesesNumericos, $patologiaName, $selectedCond = '', $selectedColonia = '')
    {
        $baseQuery = $this->buildBaseQuery($selectedAnoArr, $selectedMes, $selectedCode, $selectedCond);
        $totalFilteredRecords = (clone $baseQuery)->count();

        $mesesAbrev = $this->getMesesAbrev();
        $isComparison = count($selectedAnoArr) > 1 && !in_array('all', $selectedAnoArr) && $viewType !== 'years';
        $colors = $this->getChartColors();

        $mainChart = $this->prepareMainChartData($baseQuery, $selectedAnoArr, $viewType, $patologiaName, $isComparison, $mesesNumericos, $mesesAbrev, $colors);

        $secondaryCharts = $this->prepareSecondaryChartsData($baseQuery, $selectedAnoArr, $isComparison, $colors);

        $topColonia = $selectedColonia ?: (($secondaryCharts['coloniaData']['labels'][0] ?? ''));
        $coloniaTrend = $this->prepareColoniaTrendData($baseQuery, $topColonia, $viewType, $mesesNumericos, $mesesAbrev);

        return [
            'labels'               => $mainChart['labels'],
            'datasets'             => $mainChart['datasets'],
            'totalPoints'          => $mainChart['totalPoints'],
            'totalFilteredRecords' => $totalFilteredRecords,
            'coloniaLabels'        => $secondaryCharts['coloniaData']['labels'],
            'coloniaDatasets'      => $secondaryCharts['coloniaData']['datasets'],
            'sexoLabels'           => $secondaryCharts['sexoData']['labels'],
            'sexoDatasets'         => $secondaryCharts['sexoData']['datasets'],
            'rangoLabels'          => $secondaryCharts['rangoData']['labels'],
            'rangoDatasets'        => $secondaryCharts['rangoData']['datasets'],
            'jornadaLabels'        => $secondaryCharts['jornadaData']['labels'],
            'jornadaDatasets'      => $secondaryCharts['jornadaData']['datasets'],
            'condLabels'           => $secondaryCharts['condData']['labels'],
            'condDatasets'         => $secondaryCharts['condData']['datasets'],
            'coloniaTrendLabels'   => $coloniaTrend['labels'],
            'coloniaTrendDatasets' => $coloniaTrend['datasets'],
            'topColonia'           => $topColonia
        ];
    }

    private function buildBaseQuery($selectedAnoArr, $selectedMes, $selectedCode, $selectedCond)
    {
        $query = RegistroGlobal::query();

        if ($selectedCode !== 'all' && $selectedCode !== '') {
            $query->where(function ($q) use ($selectedCode, $selectedCond) {
                for ($i = 1; $i <= 7; $i++) {
                    $q->orWhere(function ($sub) use ($i, $selectedCode, $selectedCond) {
                        $sub->where('diagnostico_' . $i, $selectedCode);
                        if (!empty($selectedCond)) {
                            $sub->where('cond_' . $i, $selectedCond);
                        }
                    });
                }
            });
        } elseif (!empty($selectedCond)) {
            $query->where(function ($q) use ($selectedCond) {
                for ($i = 1; $i <= 7; $i++) {
                    $q->orWhere('cond_' . $i, $selectedCond);
                }
            });
        }

        if (!in_array('all', $selectedAnoArr)) {
            $query->whereIn('ano', $selectedAnoArr);
        }
        if ($selectedMes !== '' && $selectedMes !== 'all') {
            $query->where('mes', $selectedMes);
        }

        return $query;
    }

    private function prepareMainChartData($baseQuery, $selectedAnoArr, $viewType, $patologiaName, $isComparison, $mesesNumericos, $mesesAbrev, $colors)
    {
        $datasets = [];
        $labels = collect();
        $totalPoints = 0;

        $chartDataQuery = clone $baseQuery;

        if ($isComparison) {
            if ($viewType === 'days') {
                $chartData = $chartDataQuery->select('ano', DB::raw('DAYOFYEAR(fecha) as period'), DB::raw('count(*) as total'))
                    ->whereNotNull('fecha')->groupBy('ano', DB::raw('DAYOFYEAR(fecha)'))->get();
                $labels = collect(range(1, 366))->map(fn($d) => "Día $d");
            } elseif ($viewType === 'months') {
                $chartData = $chartDataQuery->select('ano', 'mes as period', DB::raw('count(*) as total'))->groupBy('ano', 'mes')->get();
                $labels = collect(range(1, 12))->map(fn($m) => $mesesAbrev[$m] ?? "Mes $m");
            } else {
                $chartData = $chartDataQuery->select('ano', 'se as period', DB::raw('count(*) as total'))->whereNotNull('se')->groupBy('ano', 'se')->get();
                $maxSe = $chartData->max('period') ?: 52;
                $labels = collect(range(1, $maxSe))->map(fn($w) => "SE $w");
            }

            foreach ($selectedAnoArr as $idx => $ano) {
                $color = $colors[$idx % count($colors)];
                $yearData = [];
                if ($viewType === 'days') {
                    $yearRows = $chartData->where('ano', $ano)->keyBy('period');
                    for ($i = 1; $i <= 366; $i++) {
                        $yearData[] = isset($yearRows[$i]) ? $yearRows[$i]->total : 0;
                    }
                } elseif ($viewType === 'months') {
                    $yearRows = $chartData->where('ano', $ano)->mapWithKeys(fn($item) => [$mesesNumericos[strtolower($item->period)] ?? 99 => $item->total]);
                    for ($i = 1; $i <= 12; $i++) {
                        $yearData[] = isset($yearRows[$i]) ? $yearRows[$i] : 0;
                    }
                } else {
                    $yearRows = $chartData->where('ano', $ano)->keyBy('period');
                    foreach (range(1, count($labels)) as $w) {
                        $yearData[] = isset($yearRows[$w]) ? $yearRows[$w]->total : 0;
                    }
                }
                $datasets[] = ['label' => "$patologiaName ($ano)", 'data' => $yearData, 'lineTension' => 0.3, 'backgroundColor' => $color['bg'], 'borderColor' => $color['border'], 'borderWidth' => 2, 'fill' => true, 'pointRadius' => count($labels) > 50 ? 0 : 3];
                $totalPoints += array_sum($yearData);
            }
        } else {
            if ($viewType === 'days') {
                $chartItems = $chartDataQuery->select('fecha', DB::raw('count(*) as total'))->whereNotNull('fecha')->groupBy('fecha')->orderBy('fecha', 'asc')->get();
                $labels = $chartItems->map(fn($i) => Carbon::parse($i->fecha)->format('d/m/Y'));
            } elseif ($viewType === 'months') {
                $chartItems = $chartDataQuery->select('ano', 'mes', DB::raw('count(*) as total'))->groupBy('ano', 'mes')->get();
                $chartItems = $chartItems->sortBy(fn($i) => $i->ano * 100 + ($mesesNumericos[strtolower($i->mes)] ?? 99))->values();
                $labels = $chartItems->map(fn($i) => ($mesesAbrev[is_numeric($i->mes) ? (int) $i->mes : strtolower($i->mes)] ?? $i->mes) . ' ' . $i->ano);
            } elseif ($viewType === 'years') {
                $chartItems = $chartDataQuery->select('ano', DB::raw('count(*) as total'))->groupBy('ano')->orderBy('ano', 'asc')->get();
                $labels = $chartItems->pluck('ano');
            } else {
                $chartItems = $chartDataQuery->select('ano', 'se', DB::raw('count(*) as total'))->whereNotNull('se')->groupBy('ano', 'se')->get();
                $chartItems = $chartItems->sortBy(fn($i) => $i->ano * 100 + (int) $i->se)->values();
                $labels = $chartItems->map(fn($i) => in_array('all', $selectedAnoArr) ? $i->ano . "-SE" . $i->se : "SE " . $i->se);
            }
            $datasets[] = ['label' => $patologiaName, 'data' => $chartItems->pluck('total')->toArray(), 'lineTension' => 0.3, 'backgroundColor' => $colors[0]['bg'], 'borderColor' => $colors[0]['border'], 'borderWidth' => 2, 'fill' => true, 'pointRadius' => count($labels) > 50 ? 0 : 3];
            $totalPoints = $chartItems->sum('total');
        }

        return compact('labels', 'datasets', 'totalPoints');
    }

    private function prepareSecondaryChartsData($baseQuery, $selectedAnoArr, $isComparison, $colors)
    {
        $buildSecondaryData = function ($field, $chartType, $labelsMap = null, $sortFn = null) use ($baseQuery, $selectedAnoArr, $isComparison, $colors) {
            $q = clone $baseQuery;
            $overall = $q->select($field, DB::raw('count(*) as total'))->whereNotNull($field)->where($field, '!=', '')->where($field, '!=', '0')->groupBy($field)->get();
            $overall = $sortFn ? $overall->sortBy($sortFn)->values() : $overall->sortByDesc('total')->values();
            $rawLabels = $overall->pluck($field);
            $lbls = $labelsMap ? $rawLabels->map($labelsMap) : $rawLabels;
            $ds = [];
            if ($isComparison) {
                $byYear = (clone $baseQuery)->select('ano', $field, DB::raw('count(*) as total'))->whereNotNull($field)->where($field, '!=', '')->groupBy('ano', $field)->get();
                foreach ($selectedAnoArr as $idx => $ano) {
                    $yData = [];
                    $yRows = $byYear->where('ano', $ano)->keyBy($field);
                    foreach ($rawLabels as $l) {
                        $yData[] = isset($yRows[$l]) ? $yRows[$l]->total : 0;
                    }
                    $ds[] = ['label' => (string) $ano, 'backgroundColor' => $colors[$idx % count($colors)]['border'], 'data' => $yData];
                }
            } else {
                $yData = $overall->pluck('total')->toArray();
                $bg = match ($chartType) {
                    'doughnut_sexo'    => ['rgba(255, 99, 132, 0.7)', 'rgba(54, 162, 235, 0.7)', 'rgba(201, 203, 207, 0.7)'],
                    'doughnut_jornada' => ['#f6c23e', '#e74a3b', '#858796', '#1cc88a'],
                    'doughnut_cond'    => ['#1cc88a', '#36b9cc', '#f6c23e', '#e74a3b'],
                    default            => 'rgba(54, 162, 235, 0.7)'
                };
                $ds[] = ['label' => 'Casos', 'backgroundColor' => is_array($bg) ? (count($yData) > count($bg) ? array_pad($bg, count($yData), end($bg)) : $bg) : $bg, 'data' => $yData];
            }
            return ['labels' => $lbls->toArray(), 'datasets' => $ds];
        };

        $coloniaData = $buildSecondaryData('colonia', 'bar_colonia');
        $sexoData = $buildSecondaryData('sexo', 'doughnut_sexo', fn($s) => strtoupper(trim($s)) === 'M' ? 'Masculino' : (strtoupper(trim($s)) === 'F' ? 'Femenino' : $s));
        $rangoData = $buildSecondaryData('rango', 'bar_rango', null, fn($i) => (preg_match('/^\d+/', $i->rango, $m)) ? (int) $m[0] : 999);
        $jornadaData = $buildSecondaryData('jornada', 'doughnut_jornada');
        $condData = $buildSecondaryData('cond', 'doughnut_cond', fn($c) => match (strtoupper(trim($c))) { 'N' => 'Nuevo (N)', 'S' => 'Subsecuente (S)', 'A' => 'Alta (A)', default => $c});

        return compact('coloniaData', 'sexoData', 'rangoData', 'jornadaData', 'condData');
    }

    private function prepareColoniaTrendData($baseQuery, $topColonia, $viewType, $mesesNumericos, $mesesAbrev)
    {
        $labels = [];
        $datasets = [];

        if ($topColonia) {
            $colQuery = (clone $baseQuery)->where('colonia', $topColonia);
            if ($viewType === 'days') {
                $colItems = $colQuery->select('fecha', DB::raw('count(*) as total'))->whereNotNull('fecha')->groupBy('fecha')->orderBy('fecha', 'asc')->get();
                $labels = $colItems->map(fn($i) => Carbon::parse($i->fecha)->format('d/m/Y'))->toArray();
                $datasets[] = ['label' => "Casos en $topColonia", 'data' => $colItems->pluck('total')->toArray(), 'borderColor' => '#e74a3b', 'backgroundColor' => 'rgba(231, 74, 59, 0.1)', 'fill' => true, 'lineTension' => 0.4];
            } elseif ($viewType === 'weeks') {
                $colItems = $colQuery->select('ano', 'se', DB::raw('count(*) as total'))->whereNotNull('se')->groupBy('ano', 'se')->get();
                $colItems = $colItems->sortBy(fn($i) => $i->ano * 100 + (int) $i->se)->values();
                $labels = $colItems->map(fn($i) => $i->ano . "-SE" . $i->se)->toArray();
                $datasets[] = ['label' => "Casos en $topColonia", 'data' => $colItems->pluck('total')->toArray(), 'borderColor' => '#e74a3b', 'backgroundColor' => 'rgba(231, 74, 59, 0.1)', 'fill' => true, 'lineTension' => 0.4];
            } else {
                $colItems = $colQuery->select('ano', 'mes', DB::raw('count(*) as total'))->groupBy('ano', 'mes')->get();
                $colItems = $colItems->sortBy(fn($i) => $i->ano * 100 + ($mesesNumericos[strtolower($i->mes)] ?? 99))->values();
                $labels = $colItems->map(fn($i) => ($mesesAbrev[is_numeric($i->mes) ? (int) $i->mes : strtolower($i->mes)] ?? $i->mes) . ' ' . $i->ano)->toArray();
                $datasets[] = ['label' => "Casos en $topColonia", 'data' => $colItems->pluck('total')->toArray(), 'borderColor' => '#e74a3b', 'backgroundColor' => 'rgba(231, 74, 59, 0.1)', 'fill' => true, 'lineTension' => 0.4];
            }
        }

        return compact('labels', 'datasets');
    }

    private function getMesesAbrev()
    {
        return [
            'enero' => 'Ene', 'febrero' => 'Feb', 'marzo' => 'Mar', 'abril' => 'Abr', 'mayo' => 'May', 'junio' => 'Jun',
            'julio' => 'Jul', 'agosto' => 'Ago', 'septiembre' => 'Sep', 'octubre' => 'Oct', 'noviembre' => 'Nov', 'diciembre' => 'Dic',
            1 => 'Ene', 2 => 'Feb', 3 => 'Mar', 4 => 'Abr', 5 => 'May', 6 => 'Jun',
            7 => 'Jul', 8 => 'Ago', 9 => 'Sep', 10 => 'Oct', 11 => 'Nov', 12 => 'Dic'
        ];
    }

    private function getChartColors()
    {
        return [
            ['bg' => 'rgba(78, 115, 223, 0.1)', 'border' => 'rgba(78, 115, 223, 1)'],
            ['bg' => 'rgba(28, 200, 138, 0.1)', 'border' => 'rgba(28, 200, 138, 1)'],
            ['bg' => 'rgba(246, 194, 62, 0.1)', 'border' => 'rgba(246, 194, 62, 1)'],
            ['bg' => 'rgba(231, 74, 59, 0.1)', 'border' => 'rgba(231, 74, 59, 1)'],
            ['bg' => 'rgba(54, 185, 204, 0.1)', 'border' => 'rgba(54, 185, 204, 1)'],
        ];
    }
}
