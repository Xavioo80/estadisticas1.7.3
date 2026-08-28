<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Cache;
use Carbon\Carbon;

class DashboardController extends Controller
{
    /**
     * Display the main analytics dashboard.
     */
    public function index()
    {
        $now = Carbon::now();
        $anioAct = (int) $now->format('Y');
        $mesAct = (int) $now->format('n');

        // Cálculo de Semana Epidemiológica (Estándar CDC / Honduras)
        $seActual = $this->getSeDeDate($now->format('Y-m-d'));

        // Mapa de meses estándar
        $mesesMap = [
            1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL',
            5 => 'MAYO', 6 => 'JUNIO', 7 => 'JULIO', 8 => 'AGOSTO',
            9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE'
        ];
        $mesTexto = $mesesMap[$mesAct] ?? 'AGOSTO';
        $mesUpper = strtoupper($mesTexto);

        // Cachear datos calculados del Dashboard por 45 segundos para carga instantánea
        $cacheKey = "dashboard_data_v3_{$anioAct}_{$mesAct}_{$seActual}";
        $data = Cache::remember($cacheKey, 45, function() use ($anioAct, $mesAct, $mesTexto, $mesUpper, $seActual, $mesesMap) {
            
            // 1. Totales y Condiciones en una sola consulta optimizada
            $totales = DB::table('registros_globales')
                ->where('ano', $anioAct)
                ->selectRaw("COUNT(*) as total_anio")
                ->selectRaw("SUM(CASE WHEN UPPER(mes) = ? THEN 1 ELSE 0 END) as total_mes", [$mesUpper])
                ->selectRaw("SUM(CASE WHEN se = ? THEN 1 ELSE 0 END) as total_semana", [$seActual])
                ->selectRaw("SUM(CASE WHEN UPPER(mes) = ? AND cond = 'N' THEN 1 ELSE 0 END) as nuevos", [$mesUpper])
                ->selectRaw("SUM(CASE WHEN UPPER(mes) = ? AND cond = 'S' THEN 1 ELSE 0 END) as subsec", [$mesUpper])
                ->first();

            $totalAnio = (int) ($totales->total_anio ?? 0);
            $totalMes = (int) ($totales->total_mes ?? 0);
            $totalSemana = (int) ($totales->total_semana ?? 0);
            $nuevos = (int) ($totales->nuevos ?? 0);
            $subsec = (int) ($totales->subsec ?? 0);
            $totalBD = DB::table('registros_globales')->count();

            // 2. Últimos registros agrupados por médico y fecha (Optimizado con filtro de año reciente y max_id)
            $registrosRecientes = DB::table('registros_globales')
                ->select(
                    'medico',
                    'se',
                    'ano',
                    'fecha',
                    DB::raw('COUNT(*) as total_dia'),
                    DB::raw('MAX(id) as max_id')
                )
                ->where('ano', '>=', $anioAct - 1)
                ->whereNotNull('medico')
                ->where('medico', '!=', '')
                ->whereNotNull('fecha')
                ->where('fecha', '!=', '')
                ->groupBy('medico', 'se', 'ano', 'fecha')
                ->orderByDesc('max_id')
                ->limit(40)
                ->get()
                ->map(function($reg) {
                    if ($reg->fecha) {
                        try {
                            $reg->fecha_formateada = Carbon::parse($reg->fecha)->format('d/m/Y');
                        } catch (\Exception $e) {
                            $reg->fecha_formateada = $reg->fecha;
                        }
                    }
                    return $reg;
                });

            // 3. Total por médico este mes
            $totalMedicoMes = DB::table('registros_globales')
                ->select('medico', DB::raw('COUNT(*) as total_mes'))
                ->where('ano', $anioAct)
                ->whereRaw('UPPER(mes) = ?', [$mesUpper])
                ->whereNotNull('medico')
                ->where('medico', '!=', '')
                ->groupBy('medico')
                ->pluck('total_mes', 'medico');

            $medicosActivosCount = count($totalMedicoMes);

            // 4. Nombres de usuarios para los registros recientes
            $hasUserId = Schema::hasColumn('registros_globales', 'user_id');
            $usuariosData = collect();
            if ($hasUserId && $registrosRecientes->isNotEmpty() && Schema::hasTable('users')) {
                $medicosList = $registrosRecientes->pluck('medico')->unique()->filter()->values()->all();
                $fechasList = $registrosRecientes->pluck('fecha')->unique()->filter()->values()->all();

                $usuariosData = DB::table('registros_globales')
                    ->select(
                        'registros_globales.medico',
                        'registros_globales.fecha',
                        DB::raw('GROUP_CONCAT(DISTINCT users.name ORDER BY users.name SEPARATOR ", ") as usuarios')
                    )
                    ->leftJoin('users', 'registros_globales.user_id', '=', 'users.id')
                    ->whereIn('registros_globales.medico', $medicosList)
                    ->whereIn('registros_globales.fecha', $fechasList)
                    ->groupBy('registros_globales.medico', 'registros_globales.fecha')
                    ->get()
                    ->keyBy(function ($item) {
                        return $item->medico . '|' . $item->fecha;
                    });
            }

            // ── Datos para Gráficos: DÍAS, SEMANAS, MESES, AÑOS ─────
            
            // 1. DÍAS (Últimos 30 días activos ordenados por id reciente)
            $diasDataRaw = DB::table('registros_globales')
                ->select('fecha', DB::raw('COUNT(*) as total'), DB::raw('MAX(id) as max_id'))
                ->where('ano', '>=', $anioAct - 1)
                ->whereNotNull('fecha')
                ->where('fecha', '!=', '')
                ->groupBy('fecha')
                ->orderByDesc('max_id')
                ->limit(30)
                ->get()
                ->reverse();

            $diasCategories = [];
            $diasValues = [];
            foreach ($diasDataRaw as $item) {
                try {
                    $diasCategories[] = Carbon::parse($item->fecha)->format('d/M');
                } catch (\Exception $e) {
                    $diasCategories[] = $item->fecha;
                }
                $diasValues[] = (int) $item->total;
            }

            // 2. SEMANAS (Semanas epidemiológicas del año actual)
            $semanasDataRaw = DB::table('registros_globales')
                ->where('ano', $anioAct)
                ->whereNotNull('se')
                ->where('se', '!=', '')
                ->where('se', '!=', 0)
                ->select('se', DB::raw('COUNT(*) as total'))
                ->groupBy('se')
                ->orderByRaw("CAST(se AS UNSIGNED) ASC")
                ->get();

            $semanasCategories = [];
            $semanasValues = [];
            foreach ($semanasDataRaw as $item) {
                $semanasCategories[] = 'SE ' . $item->se;
                $semanasValues[] = (int) $item->total;
            }

            // 3. MESES (12 meses del año actual)
            $mesesEvolution = DB::table('registros_globales')
                ->where('ano', $anioAct)
                ->select(DB::raw('UPPER(mes) as mes_upper'), DB::raw('COUNT(*) as total'))
                ->groupBy(DB::raw('UPPER(mes)'))
                ->get()
                ->keyBy('mes_upper');

            $mesesCategories = ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'];
            $mesesValues = [];
            foreach ($mesesMap as $mNum => $mName) {
                $mesesValues[] = (int) ($mesesEvolution[$mName]->total ?? 0);
            }

            // 4. AÑOS (Histórico anual general)
            $aniosDataRaw = DB::table('registros_globales')
                ->whereNotNull('ano')
                ->where('ano', '!=', '')
                ->where('ano', '>', 2000)
                ->select('ano', DB::raw('COUNT(*) as total'))
                ->groupBy('ano')
                ->orderBy('ano', 'asc')
                ->get();

            $aniosCategories = [];
            $aniosValues = [];
            foreach ($aniosDataRaw as $item) {
                $aniosCategories[] = (string) $item->ano;
                $aniosValues[] = (int) $item->total;
            }

            $chartDatasets = [
                'dias' => [
                    'categories' => array_values($diasCategories),
                    'data' => array_values($diasValues),
                    'title' => 'Evolución por Días (Últimos 30 días)',
                    'badge' => 'Días'
                ],
                'semanas' => [
                    'categories' => array_values($semanasCategories),
                    'data' => array_values($semanasValues),
                    'title' => 'Evolución por Semanas Epidemiológicas (' . $anioAct . ')',
                    'badge' => 'SE ' . $anioAct
                ],
                'meses' => [
                    'categories' => array_values($mesesCategories),
                    'data' => array_values($mesesValues),
                    'title' => 'Evolución Mensual de Atenciones (' . $anioAct . ')',
                    'badge' => 'Año ' . $anioAct
                ],
                'anios' => [
                    'categories' => array_values($aniosCategories),
                    'data' => array_values($aniosValues),
                    'title' => 'Histórico Anual de Consultas Registradas',
                    'badge' => 'Histórico'
                ]
            ];

            return compact(
                'totalAnio',
                'totalMes',
                'totalSemana',
                'totalBD',
                'nuevos',
                'subsec',
                'medicosActivosCount',
                'registrosRecientes',
                'totalMedicoMes',
                'usuariosData',
                'chartDatasets'
            );
        });

        return view('dashboard.index', array_merge([
            'anioAct' => $anioAct,
            'mesAct' => $mesAct,
            'mesTexto' => $mesTexto,
            'seActual' => $seActual,
        ], $data));
    }

    /**
     * Display the epidemiologic surveillance dashboard (Visitas).
     */
    public function visits()
    {
        return view('dashboard.visits');
    }

    /**
     * Display diagnostic statistics & KPIs (Gráficos).
     */
    public function charts()
    {
        return view('dashboard.charts');
    }

    /**
     * Calcula la Semana Epidemiológica para una fecha dada (Estándar CDC / Honduras).
     */
    private function getSeDeDate(string $fecha): int
    {
        $d = Carbon::parse($fecha);
        $anio = $d->year;

        $d4Enero = Carbon::create($anio, 1, 4);
        $dow4Enero = $d4Enero->dayOfWeek; // 0=Dom
        $primeroSE1 = $d4Enero->copy()->subDays($dow4Enero);

        if ($d->lt($primeroSE1)) {
            return $this->getSeDeDate(Carbon::create($anio - 1, 12, 31)->format('Y-m-d'));
        }

        $diffDias = $primeroSE1->diffInDays($d);
        return (int) floor($diffDias / 7) + 1;
    }
}
