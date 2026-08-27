<?php

namespace App\Http\Controllers;

use App\Models\Medico;
use App\Models\Setting;
use App\Models\HoraSinConsulta;
use App\Models\HoraMedicoPosicion;
use App\Models\RegistroGlobal;
use App\Exports\HoraMedicoExport;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

use App\Traits\InformesHelperTrait;

class HoraMedicoController extends Controller
{
    use InformesHelperTrait;

    private $mesMap = [
        'ENERO' => 1,
        'FEBRERO' => 2,
        'MARZO' => 3,
        'ABRIL' => 4,
        'MAYO' => 5,
        'JUNIO' => 6,
        'JULIO' => 7,
        'AGOSTO' => 8,
        'SEPTIEMBRE' => 9,
        'OCTUBRE' => 10,
        'NOVIEMBRE' => 11,
        'DICIEMBRE' => 12
    ];

    public function index(Request $request)
    {
        $latestAno = RegistroGlobal::whereNotNull('ano')->where('ano', '>', 1900)->orderBy('ano', 'desc')->value('ano');
        $ano = $request->input('ano', $latestAno ?: date('Y'));
        $mesNombre = $request->input('mes', '');
        if (empty($mesNombre)) {
            $mesNombre = $this->resolverMesPorDefecto((string)$ano, true);
        }
        $jornada = $request->input('jornada', 'MATUTINA');
        $nombreBusqueda = $request->input('nombre');

        $mesNum = $this->mesMap[$mesNombre] ?? date('n');

        $fechaStart = Carbon::create($ano, $mesNum, 1);
        $totalDias = $fechaStart->daysInMonth;

        $diasLaborables = 0;
        $diasFinSemana = 0;
        $conteoDiasSemana = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 0 => 0];

        for ($d = 1; $d <= $totalDias; $d++) {
            $cw = Carbon::create($ano, $mesNum, $d)->dayOfWeek;
            $conteoDiasSemana[$cw]++;
            if ($cw >= 1 && $cw <= 5)
                $diasLaborables++;
            else
                $diasFinSemana++;
        }

        $semanasResumen = [];
        $diasNombres = [1 => 'L', 2 => 'M', 3 => 'Mi', 4 => 'J', 5 => 'V', 6 => 'S', 0 => 'D'];
        foreach ($conteoDiasSemana as $dw => $count)
            $semanasResumen[$diasNombres[$dw]] = $count;

        $todosLosMedicos = $this->applyExclusionMedicos(Medico::where('estado', 'activo'))->orderBy('NOM_MED')->get();
        $settings = Setting::pluck('value', 'key');
        $currentDirectorId = Setting::where('key', "director_medico_id_{$ano}_{$mesNombre}")->value('value') ?: Setting::where('key', 'director_medico_id')->value('value');

        if ($jornada === 'TOTAL JORNADAS') {
            $jornadasList = ['MATUTINA', 'VESPERTINA', 'FIN DE SEMANA'];
            $dataByJornada = [];
            foreach ($jornadasList as $j)
                $dataByJornada[$j] = $this->getJornadaData($ano, $mesNombre, $j, $nombreBusqueda, $diasLaborables, $diasFinSemana);

            $anos = RegistroGlobal::distinct()->whereNotNull('ano')->where('ano', '>', 1900)->orderBy('ano', 'desc')->pluck('ano');
            if ($anos->isEmpty())
                $anos = collect([date('Y')]);
            $meses = array_keys($this->mesMap);
            return view($request->ajax() ? 'informes.hora_medico_table' : 'informes.hora_medico', compact('dataByJornada', 'ano', 'mesNombre', 'jornada', 'meses', 'anos', 'diasLaborables', 'diasFinSemana', 'semanasResumen', 'totalDias', 'nombreBusqueda', 'todosLosMedicos', 'settings', 'currentDirectorId'));
        }

        $data = $this->getJornadaData($ano, $mesNombre, $jornada, $nombreBusqueda, $diasLaborables, $diasFinSemana);
        $anos = RegistroGlobal::distinct()->whereNotNull('ano')->where('ano', '>', 1900)->orderBy('ano', 'desc')->pluck('ano');
        if ($anos->isEmpty())
            $anos = collect([date('Y')]);
        $meses = array_keys($this->mesMap);
        return view($request->ajax() ? 'informes.hora_medico_table' : 'informes.hora_medico', compact('data', 'ano', 'mesNombre', 'jornada', 'meses', 'anos', 'diasLaborables', 'diasFinSemana', 'semanasResumen', 'totalDias', 'nombreBusqueda', 'todosLosMedicos', 'settings', 'currentDirectorId'));
    }

    public function imprimir(Request $request)
    {
        $latestAno = RegistroGlobal::whereNotNull('ano')->where('ano', '>', 1900)->orderBy('ano', 'desc')->value('ano');
        $ano = $request->input('ano', $latestAno ?: date('Y'));
        $mesNombre = $request->input('mes', '');
        if (empty($mesNombre)) {
            $mesNombre = $this->resolverMesPorDefecto((string)$ano, true);
        }
        $jornada = $request->input('jornada', 'MATUTINA');
        $nombreBusqueda = $request->input('nombre');

        $mesNum = $this->mesMap[$mesNombre] ?? date('n');

        $fechaStart = Carbon::create($ano, $mesNum, 1);
        $totalDias = $fechaStart->daysInMonth;

        $diasLaborables = 0;
        $diasFinSemana = 0;
        for ($d = 1; $d <= $totalDias; $d++) {
            $cw = Carbon::create($ano, $mesNum, $d)->dayOfWeek;
            if ($cw >= 1 && $cw <= 5)
                $diasLaborables++;
            else
                $diasFinSemana++;
        }

        $settings = Setting::pluck('value', 'key');
        $currentDirectorId = Setting::where('key', "director_medico_id_{$ano}_{$mesNombre}")->value('value') ?: Setting::where('key', 'director_medico_id')->value('value');

        if ($jornada === 'TOTAL JORNADAS') {
            $jornadasList = ['MATUTINA', 'VESPERTINA', 'FIN DE SEMANA'];
            $dataByJornada = [];
            foreach ($jornadasList as $j)
                $dataByJornada[$j] = $this->getJornadaData($ano, $mesNombre, $j, $nombreBusqueda, $diasLaborables, $diasFinSemana);
            return view('informes.vista_impresion_hora_medico', compact('dataByJornada', 'ano', 'mesNombre', 'jornada', 'totalDias', 'settings', 'currentDirectorId'));
        }

        $data = $this->getJornadaData($ano, $mesNombre, $jornada, $nombreBusqueda, $diasLaborables, $diasFinSemana);
        return view('informes.vista_impresion_hora_medico', compact('data', 'ano', 'mesNombre', 'jornada', 'totalDias', 'settings', 'currentDirectorId'));
    }

    public function saveDirectorMensual(Request $request)
    {
        $request->validate([
            'ano' => 'required',
            'mes' => 'required',
            'medico_id' => 'required'
        ]);

        $ano = $request->input('ano');
        $mes = $request->input('mes');
        $medicoId = $request->input('medico_id');

        Setting::updateOrCreate(
            ['key' => "director_medico_id_{$ano}_{$mes}"],
            ['value' => $medicoId]
        );

        // Resetear posiciones históricas de este mes para que el nuevo director encabece la posición #1
        HoraMedicoPosicion::where('ano', $ano)
            ->where('mes', $mes)
            ->delete();

        return response()->json(['success' => true]);
    }

    public function uploadLogo(Request $request)
    {
        if ($request->hasFile('image')) {
            $image = $request->file('image');
            $name = $request->input('name', 'logo_personalizado_' . time());

            // Forzar extensión .png para que coincida con la vista y sea consistente
            $filename = $name . '.png';

            // Asegurar que la carpeta exista
            $path = public_path('img/logos');
            if (!file_exists($path)) {
                mkdir($path, 0777, true);
            }

            $image->move($path, $filename);

            return response()->json([
                'success' => true,
                'url' => asset('img/logos/' . $filename)
            ]);
        }
        return response()->json(['success' => false], 400);
    }

    public function saveSetting(Request $request)
    {
        $request->validate([
            'key' => 'required|string',
            'value' => 'required'
        ]);

        Setting::updateOrCreate(
            ['key' => $request->key],
            ['value' => $request->value]
        );

        return response()->json(['success' => true]);
    }

    private function applyExclusionMedicos($query)
    {
        return $query->where(function ($q) {
            $q->where('ESPECIALIDAD', 'NOT LIKE', '%NUTRI%')
                ->where('ESPECIALIDAD', 'NOT LIKE', '%PSICOL%')
                ->where('ESPECIALIDAD', 'NOT LIKE', '%ENFERM%')
                ->where('ESPECIALIDAD', 'NOT LIKE', '%AUXILIAR%')
                ->where('ESPECIALIDAD', 'NOT LIKE', '%CONSEJ%')
                ->where('NOMINA', 'NOT LIKE', '%NUTRI%')
                ->where('NOMINA', 'NOT LIKE', '%PSICOL%')
                ->where('NOMINA', 'NOT LIKE', '%ENFERM%')
                ->where('NOMINA', 'NOT LIKE', '%AUXILIAR%')
                ->where('NOMINA', 'NOT LIKE', '%CONSEJ%')
                ->where('NOM_MED', 'NOT LIKE', '%NUTRI%')
                ->where('NOM_MED', 'NOT LIKE', '%PSICOL%')
                ->where('NOM_MED', 'NOT LIKE', '%ENFERM%')
                ->where('NOM_MED', 'NOT LIKE', '%AUXILIAR%')
                ->where('NOM_MED', 'NOT LIKE', '%CONSEJ%');
        });
    }

    private function getDoctorPriority($medico, $ano = null, $mesNombre = null)
    {
        $monthlyDirectorId = null;
        if ($ano && $mesNombre) {
            $monthlyDirectorId = Setting::where('key', "director_medico_id_{$ano}_{$mesNombre}")->value('value');
        }
        if (!$monthlyDirectorId) {
            $monthlyDirectorId = Setting::where('key', 'director_medico_id')->value('value');
        }

        // 1. Director siempre primero
        if (!empty($medico->es_director) || ($monthlyDirectorId && (int)$medico->id === (int)$monthlyDirectorId)) {
            return 1;
        }

        $nomina       = strtoupper($medico->NOMINA ?? '');
        $modalidad    = strtoupper($medico->MODALIDAD ?? '');
        $especialidad = trim(strtoupper($medico->ESPECIALIDAD ?? ''));
        $nombre       = strtoupper($medico->NOM_MED ?? '');
        $obs          = strtoupper($medico->observaciones ?? '');

        // 4. Temporales y ONGs (UNITEC, Médicos Sin Fronteras, etc.) siempre al final
        $isONG = (!empty($medico->es_ong)
            || str_contains($modalidad, 'ONG')
            || str_contains($nomina, 'ONG')
            || str_contains($modalidad, 'TEMPORAL')
            || str_contains($nomina, 'TEMPORAL')
            || str_contains($nombre, 'MEDICOS SIN FRONTERAS')
            || str_contains($nombre, 'UNITEC')
            || str_contains($nombre, 'TEMPORAL')
            || str_contains($nombre, 'ONG')
            || str_contains($obs, 'MEDICOS SIN FRONTERAS')
            || str_contains($obs, 'UNITEC')
            || str_contains($obs, 'TEMPORAL'));

        if ($isONG) {
            return 99;
        }

        $isSS = (str_contains($nomina, 'SOCIAL') || str_contains($modalidad, 'SOCIAL') || str_contains($especialidad, 'SOCIAL') || str_starts_with($nombre, 'MSS.'));
        if ($isSS) {
            return 10;
        }

        // 2. Especialistas van después del director
        $isEspecialista = ($nomina === 'ESPECIALISTA' || ($especialidad !== '' && $especialidad !== 'MEDICO GENERAL' && $especialidad !== 'MÉDICO GENERAL'));
        if ($isEspecialista) {
            return 2;
        }

        // 3. Médicos Generales
        return 3;
    }

    private function getJornadaData($ano, $mesNombre, $jornada, $nombreBusqueda, $diasLaborables, $diasFinSemana, $onlySS = false)
    {
        $mesVariants = array_unique(array_filter([
            strtoupper($mesNombre),
            ucfirst(strtolower($mesNombre)),
            strtolower($mesNombre)
        ]));

        // 🚀 Optimización de Rendimiento Extrema: Uso del índice compuesto (ano, mes) de MySQL
        $allHSC = HoraSinConsulta::where('ano', $ano)
            ->whereIn('mes', $mesVariants)
            ->get()
            ->keyBy('medico_id');

        // Base RG query for this month (and jornada if applicable)
        $rgBaseQuery = RegistroGlobal::where('ano', $ano)
            ->whereIn('mes', $mesVariants)
            ->whereNotNull('medico')
            ->where('medico', '!=', '');

        // For specific jornada (not totals), also filter RG by jornada
        $rgJornadaQuery = (clone $rgBaseQuery);
        if ($jornada !== 'TOTAL JORNADAS' && $jornada !== 'TODAS LAS JORNADAS' && $jornada !== 'TODAS' && $jornada !== 'SERVICIO SOCIAL' && !$onlySS) {
            $rgJornadaQuery->where('jornada', $jornada);
        }

        // Get atenciones counts (by medico name) for this jornada
        $atencionesCounts = (clone $rgJornadaQuery)
            ->select('medico', DB::raw('count(*) as total'))
            ->groupBy('medico')
            ->pluck('total', 'medico')
            ->toArray();

        // Get atenciones counts by cm (código médico) for this jornada
        $atencionesByCm = (clone $rgJornadaQuery)
            ->whereNotNull('cm')
            ->where('cm', '!=', '')
            ->select('cm', DB::raw('count(*) as total'))
            ->groupBy('cm')
            ->pluck('total', 'cm')
            ->toArray();

        $medicosConRegistrosNombres = array_keys($atencionesCounts);
        $medicosConRegistrosCm     = array_keys($atencionesByCm);
        $medicosConHSC             = $allHSC->keys()->toArray();

        // Build the main medico query: include anyone who has records in RG (by name OR by cm code) OR is in HSC
        $query = Medico::where('estado', 'activo')
            ->where(function ($q) use ($medicosConRegistrosNombres, $medicosConRegistrosCm, $medicosConHSC) {
                $q->whereIn('id', $medicosConHSC)
                  ->orWhereIn('COD_MED', $medicosConRegistrosCm)
                  ->orWhereIn('NOM_MED', $medicosConRegistrosNombres);
            });

        if ($onlySS) {
            $query->where(function($q) {
                $q->where('NOMINA', 'LIKE', '%SOCIAL%')
                  ->orWhere('MODALIDAD', 'LIKE', '%SOCIAL%')
                  ->orWhere('ESPECIALIDAD', 'LIKE', '%SOCIAL%')
                  ->orWhere('NOM_MED', 'LIKE', 'MSS.%');
            });
        } else {
            $query->where(function($q) {
                $q->where(function($sub) {
                    $sub->where('NOMINA', 'NOT LIKE', '%SOCIAL%')
                        ->orWhereNull('NOMINA');
                })->where(function($sub) {
                    $sub->where('MODALIDAD', 'NOT LIKE', '%SOCIAL%')
                        ->orWhereNull('MODALIDAD');
                })->where(function($sub) {
                    $sub->where('ESPECIALIDAD', 'NOT LIKE', '%SOCIAL%')
                        ->orWhereNull('ESPECIALIDAD');
                })->where('NOM_MED', 'NOT LIKE', 'MSS.%');
            });
        }

        $query = $this->applyExclusionMedicos($query);

        if (!empty($nombreBusqueda))
            $query->where('NOM_MED', 'like', '%' . $nombreBusqueda . '%');

        $medicos = $query->get();


        $data = [];

        foreach ($medicos as $medico) {
            $hsc = $allHSC->get($medico->id);
            $trimmedName = trim($medico->NOM_MED);
            // Lookup atenciones: first by cm code, then by name (with/without trim)
            $atenciones = $atencionesByCm[$medico->COD_MED]
                ?? $atencionesByCm[(string)$medico->id]
                ?? $atencionesCounts[$trimmedName]
                ?? $atencionesCounts[$medico->NOM_MED]
                ?? 0;

            // Detectar si es ONG
            $nomina    = strtoupper($medico->NOMINA ?? '');
            $modalidad = strtoupper($medico->MODALIDAD ?? '');
            $nombre    = strtoupper($medico->NOM_MED ?? '');
            $obs       = strtoupper($medico->observaciones ?? '');
            $isONG = (!empty($medico->es_ong)
                || str_contains($modalidad, 'ONG')
                || str_contains($nomina, 'ONG')
                || str_contains($modalidad, 'TEMPORAL')
                || str_contains($nomina, 'TEMPORAL')
                || str_contains($nombre, 'MEDICOS SIN FRONTERAS')
                || str_contains($nombre, 'UNITEC')
                || str_contains($nombre, 'TEMPORAL')
                || str_contains($nombre, 'ONG')
                || str_contains($obs, 'MEDICOS SIN FRONTERAS')
                || str_contains($obs, 'UNITEC')
                || str_contains($obs, 'TEMPORAL'));

            // 1. Horas por Día (Manejo de Jornada Semanal vs Diaria)
            $rawHrs = $medico->HORAS_CONTRATADAS ?: 0;
            $horasPorDia = ($rawHrs > 12) ? ($rawHrs / 5) : $rawHrs;

            $diasContratados = ($jornada === 'FIN DE SEMANA') ? $diasFinSemana : $diasLaborables;
            if ($hsc && $hsc->dias_contratados > 0) {
                $diasContratados = $hsc->dias_contratados;
            }
            $horasContratadasMes = $horasPorDia * $diasContratados;

            // 2. Horas Sin Consulta Totales
            $totalOfic = 0;
            $totalVac  = 0;
            $totalPers = 0;
            if ($hsc) {
                $totalOfic = $hsc->total_horas_oficiales ?? 0;
                $totalVac  = $hsc->total_vacaciones ?? 0;
                $totalPers = $hsc->total_horas_personales ?? 0;
            }

            // 3. Pacientes por Hora
            $pacientesPorHour = (float)($medico->PACIENTES_POR_HORA ?? 0);

            // Si no tiene nada configurado en la ficha, usamos la lógica por defecto del manual
            if ($pacientesPorHour <= 0) {
                $especialidad = trim(strtoupper($medico->ESPECIALIDAD ?? ''));
                $isSS = (str_contains($nomina, 'SOCIAL') || str_contains($modalidad, 'SOCIAL') || str_contains($especialidad, 'SOCIAL'));
                $isEspecialista = ($especialidad !== '' && $especialidad !== 'MEDICO GENERAL' && $especialidad !== 'MÉDICO GENERAL' && !$isSS);
                $pacientesPorHour = $isSS ? 3 : ($isEspecialista ? 4 : 6);
            }

            $prog = $diasContratados * ($horasPorDia * $pacientesPorHour);

            // Manual Punto 9: Horas Cumplidas = Contratadas - (Oficiales + Vacaciones)
            $horasDescontadasMeta = $totalOfic + $totalVac;
            $horasCumplidas = $horasContratadasMes - $horasDescontadasMeta;

            // Manual Punto 8: Días cumplidos
            $diasCumplidos = $horasPorDia > 0 ? ($horasCumplidas / $horasPorDia) : 0;

            // Manual Punto 10: Reprogramadas
            $repr = $prog - ($horasDescontadasMeta * $pacientesPorHour);
            if ($repr <= 0) {
                $rendimiento = ($horasContratadasMes > 0 && $horasDescontadasMeta >= $horasContratadasMes) ? 100 : 0;
            } else {
                $rendimiento = ($atenciones / $repr) * 100;
            }

            $data[] = [
                'ano' => $ano,
                'mes' => $mesNombre,
                'jornada' => $jornada,
                'medico' => $medico,
                'hsc' => $hsc,
                'is_ong' => $isONG,
                'atenciones' => $atenciones,
                'horasPorDia' => $isONG ? 0 : $horasPorDia,
                'diasContratados' => $isONG ? 0 : $diasContratados,
                'diasCumplidos' => $isONG ? 0 : $diasCumplidos,
                'horasContratadasMes' => $isONG ? 0 : $horasContratadasMes,
                'horasCumplidas' => $isONG ? 0 : $horasCumplidas,
                'prog' => $isONG ? 0 : $prog,
                'repr' => $isONG ? 0 : $repr,
                'pacientesPorHour' => $isONG ? 0 : $pacientesPorHour,
                'rendimiento' => $isONG ? 0 : $rendimiento,
                'totalOfic' => $totalOfic,
                'totalVac' => $totalVac,
                'totalPers' => $totalPers
            ];
        }

        // Cargar o registrar el orden histórico persistido por mes y jornada
        $posicionesExistentes = HoraMedicoPosicion::where('ano', $ano)
            ->where('mes', $mesNombre)
            ->where('jornada', $jornada)
            ->pluck('posicion', 'medico_id')
            ->toArray();

        usort($data, function ($a, $b) use ($ano, $mesNombre, $posicionesExistentes) {
            $pA = $this->getDoctorPriority($a['medico'], $ano, $mesNombre);
            $pB = $this->getDoctorPriority($b['medico'], $ano, $mesNombre);

            if ($pA !== $pB) {
                return $pA <=> $pB;
            }

            // Dentro del mismo grupo de prioridad, si hay posiciones históricas guardadas, respetarlas
            $posA = $posicionesExistentes[$a['medico']->id] ?? null;
            $posB = $posicionesExistentes[$b['medico']->id] ?? null;

            if ($posA !== null && $posB !== null && $posA !== $posB) {
                return $posA <=> $posB;
            }
            if ($posA !== null && $posB === null) return -1;
            if ($posA === null && $posB !== null) return 1;

            return strcmp($a['medico']->NOM_MED, $b['medico']->NOM_MED);
        });

        // Actualizar la secuencia de posiciones numeradas
        $pos = 1;
        foreach ($data as &$item) {
            $item['posicion'] = $pos++;
        }
        unset($item);

        return $data;
    }

    public function servicioSocial(Request $request)
    {
        $latestAno = RegistroGlobal::whereNotNull('ano')->where('ano', '>', 1900)->orderBy('ano', 'desc')->value('ano');
        $ano = $request->input('ano', $latestAno ?: date('Y'));
        $mesNombre = $request->input('mes', '');
        if (empty($mesNombre)) {
            $mesNombre = $this->resolverMesPorDefecto((string)$ano, true);
        }
        $jornada = 'TOTAL JORNADAS';
        $nombreBusqueda = $request->input('search', $request->input('nombre', ''));

        $mesNum = $this->mesMap[$mesNombre] ?? date('n');

        $fechaStart = Carbon::create($ano, $mesNum, 1);
        $totalDias = $fechaStart->daysInMonth;

        $diasLaborables = 0;
        $diasFinSemana = 0;
        $conteoDiasSemana = [1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0, 0 => 0];

        for ($d = 1; $d <= $totalDias; $d++) {
            $cw = Carbon::create($ano, $mesNum, $d)->dayOfWeek;
            $conteoDiasSemana[$cw]++;
            if ($cw >= 1 && $cw <= 5)
                $diasLaborables++;
            else
                $diasFinSemana++;
        }

        $semanasResumen = [];
        $diasNombres = [1 => 'L', 2 => 'M', 3 => 'Mi', 4 => 'J', 5 => 'V', 6 => 'S', 0 => 'D'];
        foreach ($conteoDiasSemana as $dw => $count)
            $semanasResumen[$diasNombres[$dw]] = $count;

        $todosLosMedicos = $this->applyExclusionMedicos(
            Medico::where('estado', 'activo')->where(function($q) {
                $q->where('NOMINA', 'LIKE', '%SOCIAL%')
                  ->orWhere('MODALIDAD', 'LIKE', '%SOCIAL%')
                  ->orWhere('ESPECIALIDAD', 'LIKE', '%SOCIAL%');
            })
        )->orderBy('NOM_MED')->get();

        $settings = Setting::pluck('value', 'key');

        $data = $this->getJornadaData($ano, $mesNombre, $jornada, $nombreBusqueda, $diasLaborables, $diasFinSemana, true);
        $anos = RegistroGlobal::distinct()->orderBy('ano', 'desc')->pluck('ano');
        if ($anos->isEmpty())
            $anos = [date('Y')];
        $meses = array_keys($this->mesMap);

        return view($request->ajax() ? 'informes.hora_medico_table' : 'informes.hora_medico_sociales', compact(
            'data', 'ano', 'mesNombre', 'jornada', 'meses', 'anos', 'diasLaborables', 'diasFinSemana',
            'semanasResumen', 'totalDias', 'nombreBusqueda', 'todosLosMedicos', 'settings'
        ));
    }

    public function agregarMedicoHSC(Request $request)
    {
        try {
            $request->validate([
                'medico_id' => 'required',
                'ano' => 'required',
                'mes' => 'required'
            ]);

            $medicoId = $request->input('medico_id');
            $ano = $request->input('ano');
            $mes = strtoupper($request->input('mes'));

            $medico = Medico::find($medicoId);
            if (!$medico) {
                return response()->json(['success' => false, 'error' => 'Médico no encontrado'], 404);
            }

            $hsc = HoraSinConsulta::firstOrCreate(
                [
                    'medico_id' => $medicoId,
                    'ano' => $ano,
                    'mes' => $mes
                ],
                [
                    'dias_contratados' => 0,
                    'total_horas_oficiales' => 0,
                    'total_vacaciones' => 0,
                    'total_horas_personales' => 0,
                    'total_horas' => 0
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Médico incluido exitosamente en el reporte',
                'hsc' => $hsc
            ]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function exportExcel(Request $request)
    {
        $medicoIds = $request->input('medicos', []);
        $jornadas = $request->input('jornadas', []);
        $anoInicio = $request->input('ano_inicio');
        $mesInicio = $request->input('mes_inicio');
        $anoFin = $request->input('ano_fin');
        $mesFin = $request->input('mes_fin');
        $start = Carbon::create($anoInicio, $this->mesMap[$mesInicio] ?? 1, 1);
        $end = Carbon::create($anoFin, $this->mesMap[$mesFin] ?? 12, 1)->endOfMonth();
        $allExportData = [];
        $mesReverseMap = array_flip($this->mesMap);
        $current = $start->copy();
        while ($current <= $end) {
            $cAno = $current->year;
            $cMesNum = $current->month;
            $cMesNombre = $mesReverseMap[$cMesNum];
            $diasLaborables = 0;
            $diasFinSemana = 0;
            $daysInMonth = $current->daysInMonth;
            for ($d = 1; $d <= $daysInMonth; $d++) {
                $cw = Carbon::create($cAno, $cMesNum, $d)->dayOfWeek;
                if ($cw >= 1 && $cw <= 5)
                    $diasLaborables++;
                else
                    $diasFinSemana++;
            }
            foreach ($jornadas as $jor) {
                $jData = $this->getJornadaData($cAno, $cMesNombre, $jor, null, $diasLaborables, $diasFinSemana);
                foreach ($jData as $row)
                    if (empty($medicoIds) || in_array($row['medico']->id, $medicoIds))
                        $allExportData[] = $row;
            }
            $current->addMonth();
        }
        return Excel::download(new HoraMedicoExport($allExportData), 'Reporte_HoraMedico_' . date('Ymd_His') . '.xlsx');
    }

    public function getHSC(Request $request)
    {
        $hsc = HoraSinConsulta::with('medico')->where('medico_id', $request->medico_id)->where('ano', $request->ano)->where('mes', $request->mes)->first();
        return response()->json($hsc ?: new HoraSinConsulta());
    }

    public function saveHSC(Request $request)
    {
        try {
            $data = $request->all();
            $numericFields = [
                'administrativas_evaluacion',
                'reuniones_trabajo',
                'cita_ihss',
                'taller',
                'capacitaciones',
                'incapacidad',
                'compensatorio',
                'duelo',
                'congresos_medicos',
                'charlas_ambiente',
                'trabajo_campo',
                'promocion',
                'esfam',
                'convocatoria_general',
                'permiso_personal',
                'vacaciones_ordinarias',
                'descanso_profilactico',
                'dias_contratados'
            ];
            foreach ($numericFields as $field) {
                $data[$field] = isset($data[$field]) ? ($data[$field] ?: 0) : 0;
            }
            $vacaciones = (float) $data['vacaciones_ordinarias'] + (float) $data['descanso_profilactico'];
            $personales = (float) $data['permiso_personal'];
            $oficialesFields = ['administrativas_evaluacion', 'reuniones_trabajo', 'cita_ihss', 'taller', 'capacitaciones', 'incapacidad', 'compensatorio', 'duelo', 'congresos_medicos', 'charlas_ambiente', 'trabajo_campo', 'promocion', 'esfam', 'convocatoria_general'];
            $totalOficiales = 0;
            foreach ($oficialesFields as $f)
                $totalOficiales += (float) $data[$f];
            $data['total_vacaciones'] = $vacaciones;
            $data['total_horas_personales'] = $personales;
            $data['total_horas_oficiales'] = $totalOficiales;
            $data['total_horas'] = $vacaciones + $personales + $totalOficiales;
            $hsc = HoraSinConsulta::updateOrCreate(['medico_id' => $data['medico_id'], 'ano' => $data['ano'], 'mes' => $data['mes']], $data);
            return response()->json(['success' => true, 'hsc' => $hsc]);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    public function saveObservacion(Request $request)
    {
        $request->validate([
            'medico_id' => 'required',
            'ano' => 'required',
            'mes' => 'required',
            'observaciones' => 'nullable|string'
        ]);

        $hsc = HoraSinConsulta::updateOrCreate(
            [
                'medico_id' => $request->input('medico_id'),
                'ano' => $request->input('ano'),
                'mes' => $request->input('mes')
            ],
            [
                'observaciones' => $request->input('observaciones')
            ]
        );

        return response()->json(['success' => true, 'hsc' => $hsc]);
    }

    public function imprimirConsolidado(Request $request)
    {
        $latestAno = RegistroGlobal::whereNotNull('ano')->where('ano', '>', 1900)->orderBy('ano', 'desc')->value('ano');
        $ano = $request->input('ano', $latestAno ?: date('Y'));
        $mes = $request->input('mes', '');
        if (empty($mes)) {
            $mes = $this->resolverMesPorDefecto((string)$ano, true);
        }
        $jornada = $request->input('jornada', 'MATUTINA');

        $mesNum = $this->mesMap[$mes] ?? date('n');
        $fechaStart = Carbon::create($ano, $mesNum, 1);
        $totalDias = $fechaStart->daysInMonth;

        $diasLaborables = 0;
        $diasFinSemana = 0;
        for ($d = 1; $d <= $totalDias; $d++) {
            $cw = Carbon::create($ano, $mesNum, $d)->dayOfWeek;
            if ($cw >= 1 && $cw <= 5)
                $diasLaborables++;
            else
                $diasFinSemana++;
        }

        $onlySS = ($jornada === 'SERVICIO SOCIAL' || request()->routeIs('informes.hora-medico.servicio-social'));
        $nombreBusqueda = $request->input('nombre', $request->input('search', null));
        $data = $this->getJornadaData($ano, $mes, $jornada, $nombreBusqueda, $diasLaborables, $diasFinSemana, $onlySS);
        $settings = Setting::pluck('value', 'key');
        $currentDirectorId = Setting::where('key', "director_medico_id_{$ano}_{$mes}")->value('value') ?: Setting::where('key', 'director_medico_id')->value('value');

        return view('informes.vista_impresion_HSC', compact('data', 'ano', 'mes', 'jornada', 'settings', 'currentDirectorId'));
    }

    public function consolidado(Request $request)
    {
        $latestAno = RegistroGlobal::whereNotNull('ano')->where('ano', '>', 1900)->orderBy('ano', 'desc')->value('ano');
        $ano = $request->input('ano', $latestAno ?: date('Y'));
        $mes = $request->input('mes', '');
        if (empty($mes)) {
            $mes = $this->resolverMesPorDefecto((string)$ano, true);
        }
        $jornada = $request->input('jornada', 'MATUTINA');

        $mesNum = $this->mesMap[$mes] ?? date('n');
        $fechaStart = Carbon::create($ano, $mesNum, 1);
        $totalDias = $fechaStart->daysInMonth;

        $diasLaborables = 0;
        $diasFinSemana = 0;
        for ($d = 1; $d <= $totalDias; $d++) {
            $cw = Carbon::create($ano, $mesNum, $d)->dayOfWeek;
            if ($cw >= 1 && $cw <= 5)
                $diasLaborables++;
            else
                $diasFinSemana++;
        }

        $onlySS = ($jornada === 'SERVICIO SOCIAL' || request()->routeIs('informes.hora-medico.servicio-social'));
        $nombreBusqueda = $request->input('nombre', $request->input('search', null));
        $data = $this->getJornadaData($ano, $mes, $jornada, $nombreBusqueda, $diasLaborables, $diasFinSemana, $onlySS);

        $anos = RegistroGlobal::distinct()->orderBy('ano', 'desc')->pluck('ano')->toArray();
        if (empty($anos)) {
            $anos = [date('Y'), date('Y') - 1];
        }
        $meses = array_keys($this->mesMap);
        $settings = Setting::pluck('value', 'key');
        $currentDirectorId = Setting::where('key', "director_medico_id_{$ano}_{$mes}")->value('value') ?: Setting::where('key', 'director_medico_id')->value('value');
        if ($onlySS) {
            $todosLosMedicos = $this->applyExclusionMedicos(
                Medico::where('estado', 'activo')->where(function($q) {
                    $q->where('NOMINA', 'LIKE', '%SOCIAL%')
                      ->orWhere('MODALIDAD', 'LIKE', '%SOCIAL%')
                      ->orWhere('ESPECIALIDAD', 'LIKE', '%SOCIAL%')
                      ->orWhere('NOM_MED', 'LIKE', 'MSS.%');
                })
            )->orderBy('NOM_MED')->get();
        } else {
            $todosLosMedicos = $this->applyExclusionMedicos(Medico::where('estado', 'activo'))->orderBy('NOM_MED')->get();
        }
        $mesNombre = $mes;

        return view($request->ajax() ? 'informes.hora_medico_consolidado_table' : 'informes.hora_medico_consolidado', compact('data', 'ano', 'mes', 'mesNombre', 'jornada', 'meses', 'anos', 'settings', 'currentDirectorId', 'todosLosMedicos'));
    }
}
