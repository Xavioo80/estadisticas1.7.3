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

class HoraMedicoController extends Controller
{
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
        $ano = $request->input('ano', date('Y'));
        $mesNombre = $request->input('mes', mb_strtoupper(Carbon::now()->locale('es')->monthName));
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

            $anos = RegistroGlobal::distinct()->orderBy('ano', 'desc')->pluck('ano');
            if ($anos->isEmpty())
                $anos = [date('Y')];
            $meses = array_keys($this->mesMap);
            return view($request->ajax() ? 'informes.hora_medico_table' : 'informes.hora_medico', compact('dataByJornada', 'ano', 'mesNombre', 'jornada', 'meses', 'anos', 'diasLaborables', 'diasFinSemana', 'semanasResumen', 'totalDias', 'nombreBusqueda', 'todosLosMedicos', 'settings', 'currentDirectorId'));
        }

        $data = $this->getJornadaData($ano, $mesNombre, $jornada, $nombreBusqueda, $diasLaborables, $diasFinSemana);
        $anos = RegistroGlobal::distinct()->orderBy('ano', 'desc')->pluck('ano');
        if ($anos->isEmpty())
            $anos = [date('Y')];
        $meses = array_keys($this->mesMap);
        return view($request->ajax() ? 'informes.hora_medico_table' : 'informes.hora_medico', compact('data', 'ano', 'mesNombre', 'jornada', 'meses', 'anos', 'diasLaborables', 'diasFinSemana', 'semanasResumen', 'totalDias', 'nombreBusqueda', 'todosLosMedicos', 'settings', 'currentDirectorId'));
    }

    public function imprimir(Request $request)
    {
        $ano = $request->input('ano', date('Y'));
        $mesNombre = $request->input('mes', mb_strtoupper(Carbon::now()->locale('es')->monthName));
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

        if (($monthlyDirectorId && (int)$medico->id === (int)$monthlyDirectorId) || !empty($medico->es_director)) {
            return 1;
        }

        $nomina       = strtoupper($medico->NOMINA ?? '');
        $modalidad    = strtoupper($medico->MODALIDAD ?? '');
        $especialidad = trim(strtoupper($medico->ESPECIALIDAD ?? ''));
        $nombre       = strtoupper($medico->NOM_MED ?? '');
        $obs          = strtoupper($medico->observaciones ?? '');

        $isONG = (!empty($medico->es_ong)
            || str_contains($modalidad, 'ONG')
            || str_contains($nomina, 'ONG')
            || str_contains($modalidad, 'TEMPORAL')
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

        $isSS = (str_contains($nomina, 'SOCIAL') || str_contains($modalidad, 'SOCIAL') || str_contains($especialidad, 'SOCIAL'));
        if ($isSS) {
            return 10;
        }

        $isEspecialista = ($especialidad !== '' && $especialidad !== 'MEDICO GENERAL' && $especialidad !== 'MÉDICO GENERAL');
        if ($isEspecialista) {
            return 2;
        }

        $isAcuerdo = (str_contains($nomina, 'ACUERDO') || str_contains($modalidad, 'ACUERDO') || str_contains($nomina, 'PERMANENTE') || str_contains($modalidad, 'PERMANENTE'));
        if ($isAcuerdo) {
            return 3;
        }

        $isContrato = (str_contains($nomina, 'CONTRATO') || str_contains($modalidad, 'CONTRATO'));
        if ($isContrato) {
            return 4;
        }

        return 5;
    }

    private function getJornadaData($ano, $mesNombre, $jornada, $nombreBusqueda, $diasLaborables, $diasFinSemana, $onlySS = false)
    {
        $medicosConRegistros = RegistroGlobal::where('ano', $ano)
            ->whereRaw('UPPER(mes) = ?', [strtoupper($mesNombre)])
            ->distinct()
            ->pluck('medico')
            ->map(fn($n) => trim($n))
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        $medicosConHSC = HoraSinConsulta::where('ano', $ano)
            ->whereRaw('UPPER(mes) = ?', [strtoupper($mesNombre)])
            ->pluck('medico_id')
            ->toArray();

        $query = Medico::where('estado', 'activo')
            ->where(function ($q) use ($medicosConRegistros, $medicosConHSC) {
                $q->whereIn('id', $medicosConHSC)
                  ->orWhereIn('NOM_MED', $medicosConRegistros)
                  ->orWhere(function($sub) use ($medicosConRegistros) {
                      foreach ($medicosConRegistros as $rgName) {
                          $sub->orWhereRaw('TRIM(NOM_MED) = ?', [$rgName]);
                      }
                  });
            });

        if ($jornada !== 'TOTAL JORNADAS' && $jornada !== 'TODAS LAS JORNADAS' && $jornada !== 'TODAS') {
            $query->where('JORNADA', $jornada);
        }

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
            $hsc = HoraSinConsulta::where('medico_id', $medico->id)->where('ano', $ano)->whereRaw('UPPER(mes) = ?', [strtoupper($mesNombre)])->first();
            $atenciones = RegistroGlobal::where('ano', $ano)
                ->whereRaw('UPPER(mes) = ?', [strtoupper($mesNombre)])
                ->where(function($q) use ($medico) {
                    $q->where('medico', $medico->NOM_MED)
                      ->orWhereRaw('TRIM(medico) = ?', [trim($medico->NOM_MED)]);
                })
                ->count();

            // Detectar si es ONG
            $nomina    = strtoupper($medico->NOMINA ?? '');
            $modalidad = strtoupper($medico->MODALIDAD ?? '');
            $nombre    = strtoupper($medico->NOM_MED ?? '');
            $obs       = strtoupper($medico->observaciones ?? '');
            $isONG = (!empty($medico->es_ong)
                || str_contains($modalidad, 'ONG')
                || str_contains($nomina, 'ONG')
                || str_contains($nombre, 'MEDICOS SIN FRONTERAS')
                || str_contains($nombre, 'ONG')
                || str_contains($obs, 'MEDICOS SIN FRONTERAS'));

            // 1. Horas por Día (Manejo de Jornada Semanal vs Diaria)
            $rawHrs = $medico->HORAS_CONTRATADAS ?: 0;
            $horasPorDia = ($rawHrs > 12) ? ($rawHrs / 5) : $rawHrs;

            $diasContratados = ($jornada === 'FIN DE SEMANA') ? $diasFinSemana : $diasLaborables;
            if ($hsc && $hsc->dias_contratados > 0) {
                $diasContratados = $hsc->dias_contratados;
            }
            $horasContratadasMes = $horasPorDia * $diasContratados;

            $totalOfic = $hsc ? $hsc->total_horas_oficiales : 0;
            $totalVac = $hsc ? $hsc->total_vacaciones : 0;
            $totalPers = $hsc ? $hsc->total_horas_personales : 0;

            // 2. Pacientes por Hora (Factor de Productividad)
            $pacientesPorHour = $medico->consultas_por_hora ?: 0;

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

        if (empty($posicionesExistentes)) {
            // No existe orden histórico guardado para este mes/jornada aún:
            // Ordenar primero con el algoritmo de prioridad (Director Mensual -> Especialistas -> Gen Acuerdo -> Gen Contrato -> OTROS -> ONG)
            usort($data, function ($a, $b) use ($ano, $mesNombre) {
                $pA = $this->getDoctorPriority($a['medico'], $ano, $mesNombre);
                $pB = $this->getDoctorPriority($b['medico'], $ano, $mesNombre);

                if ($pA === $pB) {
                    return strcmp($a['medico']->NOM_MED, $b['medico']->NOM_MED);
                }
                return $pA <=> $pB;
            });

            // Guardar el snapshot histórico inicial en la BD
            $pos = 1;
            foreach ($data as &$item) {
                $item['posicion'] = $pos;
                HoraMedicoPosicion::updateOrCreate(
                    [
                        'ano' => $ano,
                        'mes' => $mesNombre,
                        'jornada' => $jornada,
                        'medico_id' => $item['medico']->id
                    ],
                    ['posicion' => $pos]
                );
                $pos++;
            }
            unset($item);
        } else {
            // Ya existe un snapshot histórico guardado para este mes/jornada:
            $maxPos = !empty($posicionesExistentes) ? max($posicionesExistentes) : 0;

            foreach ($data as &$item) {
                $mId = $item['medico']->id;
                if (isset($posicionesExistentes[$mId])) {
                    $item['posicion'] = (int)$posicionesExistentes[$mId];
                } else {
                    $maxPos++;
                    $item['posicion'] = $maxPos;
                    HoraMedicoPosicion::create([
                        'ano' => $ano,
                        'mes' => $mesNombre,
                        'jornada' => $jornada,
                        'medico_id' => $mId,
                        'posicion' => $maxPos
                    ]);
                }
            }
            unset($item);

            usort($data, function ($a, $b) {
                return $a['posicion'] <=> $b['posicion'];
            });
        }

        return $data;
    }

    public function servicioSocial(Request $request)
    {
        $ano = $request->input('ano', date('Y'));
        $mesNombre = $request->input('mes', mb_strtoupper(Carbon::now()->locale('es')->monthName));
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
            $hsc = HoraSinConsulta::firstOrCreate(['medico_id' => $request->medico_id, 'ano' => $request->ano, 'mes' => $request->mes], ['total_horas' => 0]);
            return response()->json(['success' => true]);
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
        $ano = $request->input('ano', date('Y'));
        $mes = $request->input('mes', mb_strtoupper(Carbon::now()->locale('es')->monthName));
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
        $data = $this->getJornadaData($ano, $mes, $jornada, null, $diasLaborables, $diasFinSemana, $onlySS);
        $settings = Setting::pluck('value', 'key');
        $currentDirectorId = Setting::where('key', "director_medico_id_{$ano}_{$mes}")->value('value') ?: Setting::where('key', 'director_medico_id')->value('value');

        return view('informes.vista_impresion_HSC', compact('data', 'ano', 'mes', 'jornada', 'settings', 'currentDirectorId'));
    }

    public function consolidado(Request $request)
    {
        $ano = $request->input('ano', date('Y'));
        $mes = $request->input('mes', mb_strtoupper(Carbon::now()->locale('es')->monthName));
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
        $data = $this->getJornadaData($ano, $mes, $jornada, null, $diasLaborables, $diasFinSemana, $onlySS);

        $anos = RegistroGlobal::distinct()->orderBy('ano', 'desc')->pluck('ano')->toArray();
        if (empty($anos)) {
            $anos = [date('Y'), date('Y') - 1];
        }
        $meses = array_keys($this->mesMap);
        $settings = Setting::pluck('value', 'key');
        $currentDirectorId = Setting::where('key', "director_medico_id_{$ano}_{$mes}")->value('value') ?: Setting::where('key', 'director_medico_id')->value('value');
        $todosLosMedicos = $this->applyExclusionMedicos(Medico::where('estado', 'activo'))->orderBy('NOM_MED')->get();
        $mesNombre = $mes;

        return view($request->ajax() ? 'informes.hora_medico_consolidado_table' : 'informes.hora_medico_consolidado', compact('data', 'ano', 'mes', 'mesNombre', 'jornada', 'meses', 'anos', 'settings', 'currentDirectorId', 'todosLosMedicos'));
    }
}
