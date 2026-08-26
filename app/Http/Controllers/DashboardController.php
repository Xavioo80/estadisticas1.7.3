<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
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

        // Totales de registros
        $totales = DB::table('registros_globales')
            ->where('ano', $anioAct)
            ->selectRaw("COUNT(*) as total_anio")
            ->selectRaw("SUM(CASE WHEN UPPER(mes) = ? THEN 1 ELSE 0 END) as total_mes", [strtoupper($mesTexto)])
            ->selectRaw("SUM(CASE WHEN se = ? THEN 1 ELSE 0 END) as total_semana", [$seActual])
            ->first();

        $totalAnio = (int) ($totales->total_anio ?? 0);
        $totalMes = (int) ($totales->total_mes ?? 0);
        $totalSemana = (int) ($totales->total_semana ?? 0);
        $totalBD = DB::table('registros_globales')->count();

        // Nuevos / Subsecuentes este mes
        $condicionesMes = DB::table('registros_globales')
            ->where('ano', $anioAct)
            ->whereRaw('UPPER(mes) = ?', [strtoupper($mesTexto)])
            ->selectRaw("SUM(CASE WHEN cond = 'N' THEN 1 ELSE 0 END) as nuevos")
            ->selectRaw("SUM(CASE WHEN cond = 'S' THEN 1 ELSE 0 END) as subsec")
            ->first();

        $nuevos = (int) ($condicionesMes->nuevos ?? 0);
        $subsec = (int) ($condicionesMes->subsec ?? 0);

        // Últimos registros agrupados por médico y fecha
        $registrosRecientes = DB::table('registros_globales')
            ->select(
                'medico',
                'se',
                'ano',
                'fecha',
                DB::raw('COUNT(*) as total_dia'),
                DB::raw('MAX(id) as max_id')
            )
            ->whereNotNull('medico')
            ->where('medico', '!=', '')
            ->whereNotNull('fecha')
            ->where('fecha', '!=', '')
            ->groupBy('medico', 'se', 'ano', 'fecha')
            ->orderByRaw("COALESCE(STR_TO_DATE(fecha, '%Y-%m-%d'), STR_TO_DATE(fecha, '%d/%m/%Y')) DESC")
            ->orderByDesc('max_id')
            ->orderBy('medico')
            ->limit(50)
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

        // Total por médico este mes
        $totalMedicoMes = DB::table('registros_globales')
            ->select('medico', DB::raw('COUNT(*) as total_mes'))
            ->where('ano', $anioAct)
            ->whereRaw('UPPER(mes) = ?', [strtoupper($mesTexto)])
            ->whereNotNull('medico')
            ->where('medico', '!=', '')
            ->groupBy('medico')
            ->pluck('total_mes', 'medico');

        $medicosActivosCount = count($totalMedicoMes);

        // Nombres de usuarios para los registros recientes si existe columna user_id
        $hasUserId = Schema::hasColumn('registros_globales', 'user_id');
        $usuariosData = [];
        if ($hasUserId && $registrosRecientes->isNotEmpty() && Schema::hasTable('users')) {
            $usuariosData = DB::table('registros_globales')
                ->select(
                    'registros_globales.medico',
                    'registros_globales.fecha',
                    DB::raw('GROUP_CONCAT(DISTINCT users.name ORDER BY users.name SEPARATOR ", ") as usuarios')
                )
                ->leftJoin('users', 'registros_globales.user_id', '=', 'users.id')
                ->where(function ($query) use ($registrosRecientes) {
                    foreach ($registrosRecientes as $reg) {
                        $query->orWhere(function ($q) use ($reg) {
                            $q->where('registros_globales.medico', $reg->medico)
                              ->where('registros_globales.fecha', $reg->fecha);
                        });
                    }
                })
                ->groupBy('registros_globales.medico', 'registros_globales.fecha')
                ->get()
                ->keyBy(function ($item) {
                    return $item->medico . '|' . $item->fecha;
                });
        }

        // ── Datos Interactivos para Gráficos: DÍAS, SEMANAS, MESES, AÑOS ─────
        
        // 1. DÍAS (Hasta 60 días activos con scroll horizontal)
        $diasDataRaw = DB::table('registros_globales')
            ->select('fecha', DB::raw('COUNT(*) as total'))
            ->whereNotNull('fecha')
            ->where('fecha', '!=', '')
            ->groupBy('fecha')
            ->orderByRaw("COALESCE(STR_TO_DATE(fecha, '%Y-%m-%d'), STR_TO_DATE(fecha, '%d/%m/%Y')) DESC")
            ->limit(60)
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
                'title' => 'Evolución por Días (Últimos 20 días)',
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

        return view('dashboard.index', compact(
            'anioAct',
            'mesAct',
            'mesTexto',
            'seActual',
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
        ));
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

