<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Models\RegistroGlobal;
use App\Models\Medico;
use App\Models\Colonia;
use App\Models\Diagnostico;
use App\Models\Referencia;
use App\Models\Paciente;
use App\Models\NotificacionSvs;
use Carbon\Carbon;
use Yajra\DataTables\Facades\DataTables;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Services\RegistroGlobalValidationService;

class IngresoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            // ── Filtros de entrada ────────────────────────────────────────────
            $jornada = $request->input('jornada');
            $medico  = $request->input('medico');

            // Mes/año por defecto: registro más reciente (sólo 3 columnas)
            $latestRegistro = RegistroGlobal::select('ano', 'mes', 'fecha')
                ->whereNotNull('fecha')->whereNotNull('mes')->where('mes', '!=', '')
                ->orderBy('fecha', 'desc')
                ->first();

            $defaultAno = $latestRegistro ? ($latestRegistro->ano ?: date('Y')) : date('Y');
            $defaultMes = $latestRegistro ? ($latestRegistro->mes ?: 'Agosto') : 'Agosto';

            $mesesNombres = [
                1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo',    4 => 'Abril',
                5 => 'Mayo',  6 => 'Junio',   7 => 'Julio',    8 => 'Agosto',
                9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
            ];

            $ano = $request->filled('ano') ? $request->input('ano') : $defaultAno;

            if ($request->filled('mes')) {
                $mesInput = $request->input('mes');
                $mes = is_numeric($mesInput) ? ($mesesNombres[(int)$mesInput] ?? $mesInput)
                                             : ucfirst(strtolower(trim($mesInput)));
            } else {
                $mes = is_numeric($defaultMes) ? ($mesesNombres[(int)$defaultMes] ?? $defaultMes)
                                               : ucfirst(strtolower(trim($defaultMes)));
            }

            $fechaCalendario = $request->input('fecha_calendario', '');

            if (!empty($fechaCalendario)) {
                $fechaFiltro = Carbon::parse($fechaCalendario)->format('Y-m-d');
                $tipoFiltro  = 'fecha_calendario';
            } else {
                $mesNumero  = $this->getMonthNumber($mes);
                $fechaFiltro = $ano . '-' . str_pad($mesNumero, 2, '0', STR_PAD_LEFT);
                $tipoFiltro  = 'ano_mes';
            }

            // ── UNA SOLA QUERY PRINCIPAL ─────────────────────────────────────
            // Traemos todos los registros del período con sólo las columnas necesarias.
            // Eliminamos el N+1 (antes: 1 query por fecha × N fechas en el mes).
            $camposNecesarios = [
                'rg.id', 'rg.fecha', 'rg.medico', 'rg.jornada',
                'rg.user_id', 'rg.edad', 'rg.created_at',
                'rg.cod_1', 'rg.cod_2', 'rg.cod_3', 'rg.cod_4',
                'rg.cod_5', 'rg.cod_6', 'rg.cod_7',
                'u.name as user_name',
            ];

            $bulkQuery = DB::table('registros_globales as rg')
                ->leftJoin('users as u', 'rg.user_id', '=', 'u.id')
                ->select($camposNecesarios)
                ->whereNotNull('rg.fecha')
                ->whereNotNull('rg.medico')
                ->where('rg.medico', '!=', '');

            if ($tipoFiltro === 'fecha_calendario') {
                $bulkQuery->where('rg.fecha', $fechaFiltro);
            } else {
                $bulkQuery->where('rg.ano', $ano)
                          ->whereRaw('UPPER(TRIM(rg.mes)) = ?', [strtoupper(trim($mes))]);
            }

            if (!empty($jornada)) { $bulkQuery->where('rg.jornada', $jornada); }
            if (!empty($medico))  { $bulkQuery->where('rg.medico', $medico);   }

            // Ordenar por fecha desc, luego por id asc (para mantener orden de inserción por lote)
            $todosLosRegistros = $bulkQuery->orderBy('rg.fecha', 'desc')->orderBy('rg.id', 'asc')->get();

            // ── AGRUPACIÓN EN PHP (0 queries adicionales) ─────────────────────
            // Agrupar por fecha → médico → lote (user+jornada+minuto)
            $porFecha = $todosLosRegistros->groupBy('fecha');

            $fechasConMedicos = $porFecha->map(function ($registrosFecha, $fecha) {
                $porMedico = $registrosFecha->groupBy('medico');

                $medicos = $porMedico->map(function ($itemsMedico, $nomMed) {
                    // Sub-lotes: agrupados por user_id + jornada + minuto de created_at
                    $subGruposRaw = $itemsMedico->groupBy(function ($item) {
                        $u = $item->user_id ?? '0';
                        $j = $item->jornada ?? 'SIN_JORNADA';
                        $c = $item->created_at ? Carbon::parse($item->created_at)->format('Y-m-d H:i') : 'batch_0';
                        return "{$u}_{$j}_{$c}";
                    });

                    $subRegistros = $subGruposRaw->map(function ($subItems, $key) {
                        $first = $subItems->first();

                        // Contar diagnósticos válidos en SQL sería mejor, pero aquí ya tenemos los datos
                        $totalDiag = $subItems->sum(function ($r) {
                            $count = 0;
                            foreach (['cod_1','cod_2','cod_3','cod_4','cod_5','cod_6','cod_7'] as $col) {
                                $v = $r->$col ?? null;
                                if ($v !== null && $v !== '' && $v !== 'N') $count++;
                            }
                            return $count;
                        });

                        $totalMenores5 = $subItems->filter(fn($r) =>
                            is_numeric($r->edad) && (int)$r->edad < 5
                        )->count();

                        return (object)[
                            'key'                 => $key,
                            'jornada'             => $first->jornada ?: 'Sin jornada',
                            'user_id'             => $first->user_id,
                            'user_name'           => $first->user_name ?: 'S/U',
                            'created_at'          => $first->created_at,
                            'created_at_formatted'=> $first->created_at
                                                        ? Carbon::parse($first->created_at)->format('h:i A')
                                                        : null,
                            'total_registros'     => $subItems->count(),
                            'total_diagnosticos'  => $totalDiag,
                            'total_menores_5'     => $totalMenores5,
                            'record_ids'          => $subItems->pluck('id')->toArray(),
                        ];
                    })->values();

                    $jornadasUnicas    = $subRegistros->pluck('jornada')->unique();
                    $jornadaPrincipal  = $jornadasUnicas->count() === 1 ? $jornadasUnicas->first() : 'VARIAS';
                    $usuariosUnicos    = $subRegistros->pluck('user_name')->unique();
                    $usuarioPrincipal  = $usuariosUnicos->count() === 1 ? $usuariosUnicos->first() : 'VARIOS USUARIOS';

                    return (object)[
                        'nom_med'           => $nomMed,
                        'cod_med'           => '',
                        'jornada'           => $jornadaPrincipal,
                        'user_name'         => $usuarioPrincipal,
                        'total_registros'   => $subRegistros->sum('total_registros'),
                        'total_diagnosticos'=> $subRegistros->sum('total_diagnosticos'),
                        'total_menores_5'   => $subRegistros->sum('total_menores_5'),
                        'total_subregistros'=> $subRegistros->count(),
                        'sub_registros'     => $subRegistros,
                    ];
                })->values();

                return (object)[
                    'fecha'                  => $fecha,
                    'total_atenciones_fecha' => $registrosFecha->count(),
                    'medicos'                => $medicos,
                ];
            });

            // ── ESTADÍSTICAS: derivadas de la colección ya cargada ────────────
            $totalRegistrosFiltrados = $todosLosRegistros->count();
            $totalMedicosFiltrados   = $todosLosRegistros->pluck('medico')->unique()->count();

            // Registros de HOY: query liviana sobre sólo la fecha de hoy
            $registrosHoy = DB::table('registros_globales')
                ->whereDate('fecha', Carbon::today()->format('Y-m-d'))
                ->count();

            $estadisticas = [
                'total_registros' => $totalRegistrosFiltrados,
                'total_medicos'   => $totalMedicosFiltrados,
                'registros_hoy'   => $registrosHoy,
            ];

            // ── FILTROS PARA SELECTORES (jornadas / médicos / años) ───────────
            // Derivados de la colección: 0 queries adicionales
            $jornadas     = $todosLosRegistros->pluck('jornada')->filter()->unique()->sort()->values();
            $medicosUnicos = $todosLosRegistros->pluck('medico')->filter()->unique()->sort()->values();

            // Años disponibles: query liviana sólo sobre columna `ano`
            $anosDisponibles = DB::table('registros_globales')
                ->select('ano')->distinct()
                ->whereNotNull('ano')->where('ano', '!=', '')
                ->orderBy('ano', 'desc')->pluck('ano')->toArray();

            if (!in_array(date('Y'), $anosDisponibles)) $anosDisponibles[] = date('Y');
            if (!in_array($ano, $anosDisponibles)) $anosDisponibles[] = $ano;
            rsort($anosDisponibles);
            $anos = array_unique($anosDisponibles);

            return view('ingresos.index', compact(
                'fechasConMedicos', 'jornadas', 'medicosUnicos', 'estadisticas',
                'jornada', 'medico', 'ano', 'mes', 'fechaCalendario', 'anos'
            ));

        } catch (\Exception $e) {
            Log::error('Error en IngresoController@index', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return view('ingresos.index', [
                'fechasConMedicos' => collect(),
                'jornadas'         => collect(),
                'medicosUnicos'    => collect(),
                'estadisticas'     => ['total_registros' => 0, 'total_medicos' => 0, 'registros_hoy' => 0],
                'jornada'          => null,
                'medico'           => null,
                'ano'              => date('Y'),
                'mes'              => $this->getCurrentMonthName(),
                'fechaCalendario'  => null,
                'anos'             => [date('Y')],
            ]);
        }
    }


    /**
     * Obtener profesiones filtradas por fecha (AJAX)
     */

    public function profesionesPorFecha(Request $request)
    {
        try {
            $ano = $request->input('ano');
            $mes = $request->input('mes');
            $fechaEspecifica = $request->input('fecha_calendario');
            $jornada = $request->input('jornada');

            Log::info('profesionesPorFecha llamado', [
                'ano' => $ano,
                'mes' => $mes,
                'fecha_especifica' => $fechaEspecifica,
                'jornada' => $jornada
            ]);

            $query = RegistroGlobal::distinct('prof')
                ->whereNotNull('prof')
                ->where('prof', '!=', '')
                ->orderBy('prof');

            // Filtrar por jornada si se especifica
            if (!empty($jornada)) {
                $query->where('jornada', $jornada);
            }

            // Si hay fecha específica, usarla (tiene prioridad sobre año/mes)
            if (!empty($fechaEspecifica)) {
                $query->whereDate('fecha', $fechaEspecifica);
            }
            else {
                // Solo usar año/mes si no hay fecha específica
                if (!empty($ano)) {
                    $query->whereYear('fecha', $ano);
                }

                if (!empty($mes)) {
                    $query->whereMonth('fecha', $mes);
                }
            }

            $profesiones = $query->pluck('prof');

            Log::info('Profesiones encontradas', [
                'total' => $profesiones->count(),
                'profesiones' => $profesiones->toArray()
            ]);

            if (ob_get_length()) ob_clean();
            return response()->json([
                'success' => true,
                'profesiones' => $profesiones
            ]);

        }
        catch (\Throwable $e) {
            Log::error('Error en profesionesPorFecha', [
                'error' => $e->getMessage()
            ]);

            if (ob_get_length()) ob_clean();
            return response()->json([
                'success' => false,
                'error' => 'Error al cargar profesiones'
            ], 500);
        }
    }

    /**
     * Obtener médicos filtrados por profesión (AJAX)
     */
    public function medicosPorProfesion(Request $request)
    {
        try {
            $profesion = $request->input('profesion');
            $ano = $request->input('ano');
            $mes = $request->input('mes');
            $fechaEspecifica = $request->input('fecha_calendario');
            $jornada = $request->input('jornada');

            Log::info('medicosPorProfesion llamado', [
                'profesion' => $profesion,
                'ano' => $ano,
                'mes' => $mes,
                'fecha_especifica' => $fechaEspecifica,
                'jornada' => $jornada
            ]);

            $query = RegistroGlobal::distinct('medico')
                ->whereNotNull('medico')
                ->where('medico', '!=', '')
                ->orderBy('medico');

            // Filtrar por profesión si se especifica
            if (!empty($profesion)) {
                $query->where('prof', $profesion);
            }

            // Filtrar por jornada si se especifica
            if (!empty($jornada)) {
                $query->where('jornada', $jornada);
            }

            // Si hay fecha específica, usarla (tiene prioridad sobre año/mes)
            if (!empty($fechaEspecifica)) {
                $query->whereDate('fecha', $fechaEspecifica);
            }
            else {
                // Solo usar año/mes si no hay fecha específica
                if (!empty($ano)) {
                    $query->whereYear('fecha', $ano);
                }

                if (!empty($mes)) {
                    $query->whereMonth('fecha', $mes);
                }
            }

            $medicos = $query->pluck('medico');

            Log::info('Médicos encontrados', [
                'total' => $medicos->count(),
                'medicos' => $medicos->toArray()
            ]);

            if (ob_get_length()) ob_clean();
            return response()->json([
                'success' => true,
                'medicos' => $medicos
            ]);

        }
        catch (\Throwable $e) {
            Log::error('Error en medicosPorProfesion', [
                'error' => $e->getMessage()
            ]);

            if (ob_get_length()) ob_clean();
            return response()->json([
                'success' => false,
                'error' => 'Error al cargar médicos'
            ], 500);
        }
    }

    /**
     * Mostrar registros de un médico específico en una fecha
     */
    public function detallesMedico($fecha, $medico)
    {
        try {
            // Decodificar parámetros URL
            $fecha = urldecode($fecha);
            $medico = urldecode($medico);

            Log::info('detallesMedico called', ['fecha' => $fecha, 'medico' => $medico]);

            // Obtener registros de esa fecha
            // Normalizar fecha si viene con hora
            $fechaNormalized = $fecha;
            if (str_contains($fecha, ' ')) {
                $fechaNormalized = explode(' ', $fecha)[0];
            }

            $query = RegistroGlobal::where('fecha', $fechaNormalized);

            // Si se especifica médico, filtrar por él
            if (!empty($medico)) {
                $query->where('medico', $medico);
            }

            // Si se especifican IDs (de un sub-registro en particular)
            if (request()->has('ids') && !empty(request()->input('ids'))) {
                $idsArray = is_array(request()->input('ids')) ? request()->input('ids') : explode(',', request()->input('ids'));
                $query->whereIn('id', $idsArray);
            }

            $registros = $query->orderByRaw('CAST(numero AS UNSIGNED) ASC')->get();

            // Si no hay registros, mostrar mensaje
            if ($registros->count() === 0) {
                return redirect()->route('ingresos.index')
                    ->with('info', 'No se encontraron registros para la fecha especificada.');
            }

            // Obtener el nombre del médico de los registros
            $medicoNombre = $registros->first()->medico ?? 'Médico no especificado';

            Log::info('Registros found', ['count' => $registros->count(), 'medico' => $medicoNombre]);

            // Obtener datos de referencia
            $referencias = Referencia::where('estado', true)->get();
            $medicos = Medico::where('estado', 'activo')->get();
            $colonias = Colonia::all();
            $diagnosticos = Diagnostico::all();

            // Estadísticas básicas
            $estadisticas = [
                'total_registros' => $registros->count(),
                'total_medicos' => $registros->pluck('medico')->unique()->count(),
                'por_jornada' => $registros->groupBy('jornada')->map->count()
            ];

            Log::info('About to return detalles-medico view');

            return view('ingresos.detalles-medico', compact(
                'registros',
                'fecha',
                'medico',
                'medicoNombre',
                'referencias',
                'estadisticas',
                'medicos',
                'colonias',
                'diagnosticos'
            ));

        }
        catch (\Exception $e) {
            Log::error('Error en IngresoController@detallesMedico', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
                'fecha' => $fecha,
                'medico' => $medico
            ]);

            return redirect()->route('ingresos.index')
                ->with('error', 'Error al cargar los detalles: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar grupo de registros por fecha y médico
     */
    public function eliminarGrupo(Request $request)
    {
        $fecha = $request->input('fecha');
        $codMed = $request->input('cod_med');
        $ids = $request->input('ids');

        try {
            $eliminados = DB::transaction(function () use ($ids, $fecha, $codMed) {
                if (!empty($ids)) {
                    $idsArray = is_array($ids) ? $ids : explode(',', $ids);
                    \App\Models\Informe::whereIn('registro_id', $idsArray)->delete();
                    return RegistroGlobal::whereIn('id', $idsArray)->delete();
                } else {
                    $targetIds = RegistroGlobal::where('fecha', $fecha)
                        ->where('medico', $codMed)
                        ->pluck('id');
                    \App\Models\Informe::whereIn('registro_id', $targetIds)->delete();
                    return RegistroGlobal::whereIn('id', $targetIds)->delete();
                }
            });

            return response()->json([
                'success' => true,
                'message' => "Se eliminaron {$eliminados} registros correctamente"
            ]);
        }
        catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar los registros: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener detalles de registros por fecha y médico
     */
    public function detalles(Request $request)
    {
        $fecha = $request->input('fecha');
        $codMed = $request->input('cod_med');

        $registros = RegistroGlobal::where('fecha', $fecha)
            ->where('cod_med', $codMed)
            ->orderBy('numero')
            ->get();

        return view('ingresos.detalles', compact('registros', 'fecha', 'codMed'));
    }

    /**
     * Vista completa de detalles por fecha (todos los médicos)
     */
    public function detallesFecha($fecha)
    {
        // Normalizar fecha si viene con hora
        if (str_contains($fecha, ' ')) {
            $fecha = explode(' ', $fecha)[0];
        }

        $registros = RegistroGlobal::where('fecha', $fecha)
            ->orderBy('medico')
            ->orderBy('numero')
            ->get();

        // Obtener datos de referencia para los formularios
        $referencias = Referencia::where('estado', true)->get();
        $medicos = Medico::where('estado', 'activo')->get();
        $colonias = Colonia::all();
        $diagnosticos = Diagnostico::all();

        $estadisticas = [
            'total_registros' => $registros->count(),
            'total_medicos' => $registros->pluck('medico')->unique()->count(),
            'por_jornada' => $registros->groupBy('medico')->map->count()
        ];

        return view('ingresos.detalles-fecha', compact('registros', 'fecha', 'estadisticas', 'referencias', 'medicos', 'colonias', 'diagnosticos'));
    }

    /**
     * Vista completa de detalles mensuales con gestión de datos
     */
    public function detallesMensual(Request $request)
    {
        $fecha = $request->input('fecha');
        $codMed = $request->input('cod_med');
        $nomMed = $request->input('nom_med');

        $registros = RegistroGlobal::where('fecha', $fecha)
            ->where('cod_med', $codMed)
            ->orderBy('numero')
            ->get();

        // Obtener datos para formularios
        $medicosActivos = Medico::where('estado', 'activo')->get();
        $colonias = Colonia::all();
        $referencias = Referencia::where('estado', true)->get();

        return view('ingresos.detalles-mensual', compact(
            'registros', 'fecha', 'codMed', 'nomMed',
            'medicosActivos', 'colonias', 'referencias'
        ));
    }

    /**
     * Display a listing of the resource for datatable.
     */
    public function datatable()
    {
        $ingresos = RegistroGlobal::query();

        return DataTables::of($ingresos)
            ->addColumn('action', function ($ingreso) {
            return '<a href="' . route('ingresos.edit', $ingreso->id) . '" class="btn btn-sm btn-primary">Editar</a>';
        })
            ->make(true);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        $columns = [
            'ano', 'mes', 'numero', 'cm', 'medico', 'prof', 'fecha', 'se', 'exp',
            'identidad', 'nombre_paciente', 'fecha_nacimiento',
            'sexo', 'edad', 'tipo',
            'rango', 'rango_2', 'rango_3', 'rango_4', 'rango_5', 'cond', 'cod_col', 'colonia',
            'cod_1', 'diagnostico_1', 'cond_1', 'sg',
            'cod_2', 'diagnostico_2', 'cond_2',
            'cod_3', 'diagnostico_3', 'cond_3',
            'cod_4', 'diagnostico_4', 'cond_4',
            'cod_5', 'diagnostico_5', 'cond_5',
            'cod_6', 'diagnostico_6', 'cond_6',
            'cod_7', 'diagnostico_7', 'cond_7',
            'referido_a', 'referido_de', 'pg_emb', 'jornada', 'sm'
        ];

        $medicos = Medico::select(['COD_MED', 'NOM_MED', 'ESPECIALIDAD', 'JORNADA'])->where('estado', 'activo')->get();
        // Nota: En 'dos' traía todos, aquí mantengo filtro 'activo' de create original pero con campos específicos

        $colonias = Colonia::select(['COD_COL', 'COLONIA'])->get();
        $diagnosticos = Diagnostico::select(['codigo', 'auxiliar', 'patologia', 'categoria', 'requiere_embarazo'])->get();
        $referencias = Referencia::where('estado', true)->select(['nombre'])->orderBy('nombre')->get();

        // Cargar validaciones de diagnósticos
        $validacionesDiagnosticos = Diagnostico::where(function ($query) {
            $query->whereNotNull('edad_minima')
                ->orWhereNotNull('edad_maxima')
                ->orWhere('sexo_permitido', '!=', 'ambos')
                ->orWhere('requiere_embarazo', true)
                ->orWhere('es_pediatrico', true)
                ->orWhere('es_adulto', true);
        })->get(['codigo', 'patologia', 'edad_minima', 'edad_maxima', 'tipo_edad',
            'sexo_permitido', 'requiere_embarazo', 'es_pediatrico', 'es_adulto', 'notas_validacion']);

        return view('ingresos.create', compact('columns', 'medicos', 'colonias', 'diagnosticos', 'referencias', 'validacionesDiagnosticos'));
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $request->validate([
            'medico' => 'required|string|max:255',
            'fecha' => 'required|date',
            // Agregar validaciones para los demás campos
        ]);

        $data = $request->all();
        $this->normalizarMedicoYProf($data);

        if (!empty($data['edad']) && !empty($data['tipo'])) {
            $rangosCalculados = \App\Services\ExcelImportService::calcularRangosEpidemiologicos($data['edad'], $data['tipo']);
            $data['rango'] = $rangosCalculados['rango'];
            $data['rango_2'] = $rangosCalculados['rango_2'];
            $data['rango_3'] = $rangosCalculados['rango_3'];
            $data['rango_4'] = $rangosCalculados['rango_4'];
            $data['rango_5'] = $rangosCalculados['rango_5'];
        }

        RegistroGlobal::create($data);

        return redirect()->route('ingresos.index', [
            'mes' => Carbon::parse($request->fecha)->format('Y-m'),
            'jornada' => $request->jornada
        ])->with('success', 'RegistroGlobal creado exitosamente.');
    }

    /**
     * Display the specified resource.
     */
    public function show(RegistroGlobal $ingreso)
    {
    //
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(RegistroGlobal $ingreso)
    {
    //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RegistroGlobal $ingreso)
    {
        try {
            Log::info('=== UPDATE INGRESO ===', [
                'id' => $ingreso->id,
                'method' => $request->method(),
                'has_field' => $request->has('field'),
                'request_keys' => array_keys($request->all())
            ]);

            // Si es actualización de campo individual (desde detalles-fecha)
            if ($request->has('field')) {
                $field = $request->field;
                $value = $request->value;

                // Lista de campos permitidos
                $allowedFields = [
                    'numero', 'mes', 'cod_med', 'nom_med', 'prof', 'fecha', 'sem_epi', 'ano_epi',
                    'nombre_paciente', 'identidad', 'telefono', 'fecha_nacimiento', 'etnia',
                    'sexo', 'edad', 'tipo', 'rango1', 'rango2', 'rango3', 'rango4', 'rango5', 'condp',
                    'cod_col', 'colonia', 'cod1', 'diagnostico1', 'cond1', 'sg_num', 'cod2', 'diagnostico2',
                    'cond2', 'cod3', 'diagnostico3', 'cond3', 'cod4', 'diagnostico4', 'cond4', 'cod5',
                    'diagnostico5', 'cond5', 'cod6', 'diagnostico6', 'cond6', 'cod7', 'diagnostico7',
                    'cond7', 'referido_a', 'referido_de', 'Informe', 'jornada', 'pg_emb'
                ];

                if (!in_array($field, $allowedFields)) {
                    return response()->json(['success' => false, 'message' => "Campo '$field' no válido"], 422);
                }

                $ingreso->$field = $value;
                $ingreso->save();
                $this->syncPacienteFromRow($ingreso->toArray());

                return response()->json([
                    'success' => true,
                    'message' => "Campo '$field' actualizado correctamente"
                ]);
            }
            else {
                // Actualización completa del registro (desde modal)
                $allowedFields = [
                    'numero', 'mes', 'cod_med', 'nom_med', 'prof', 'fecha', 'sem_epi', 'ano_epi',
                    'nombre_paciente', 'identidad', 'telefono', 'fecha_nacimiento', 'etnia',
                    'sexo', 'edad', 'tipo', 'rango1', 'rango2', 'rango3', 'rango4', 'rango5', 'condp',
                    'cod_col', 'colonia', 'sg_num', 'referido_a', 'referido_de', 'Informe', 'jornada', 'pg_emb',
                    'cod1', 'diagnostico1', 'cond1', 'cod2', 'diagnostico2', 'cond2',
                    'cod3', 'diagnostico3', 'cond3', 'cod4', 'diagnostico4', 'cond4',
                    'cod5', 'diagnostico5', 'cond5', 'cod6', 'diagnostico6', 'cond6',
                    'cod7', 'diagnostico7', 'cond7'
                ];

                // Actualizar solo campos permitidos
                foreach ($allowedFields as $field) {
                    if ($request->has($field)) {
                        $value = $request->input($field);
                        $ingreso->$field = ($value === '') ? null : $value;
                    }
                }

                $ingreso->save();
                $this->syncPacienteFromRow($ingreso->toArray());

                return response()->json([
                    'success' => true,
                    'message' => 'Registro actualizado exitosamente.',
                    'registro' => $ingreso->fresh()
                ]);
            }

        }
        catch (\Exception $e) {
            Log::error('Error actualizando ingreso', [
                'id' => $ingreso->id ?? 'unknown',
                'error' => $e->getMessage(),
                'line' => $e->getLine(),
                'file' => $e->getFile()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Request $request, RegistroGlobal $ingreso)
    {
        try {
            Log::info('Destroy method called', [
                'id' => $ingreso->id,
                'is_ajax' => $request->ajax(),
                'expects_json' => $request->expectsJson(),
                'headers' => $request->headers->all()
            ]);

            $ingreso->delete();

            // Forzar respuesta JSON para todas las peticiones DELETE
            return response()->json([
                'success' => true,
                'message' => 'Registro eliminado exitosamente.'
            ]);

        }
        catch (\Exception $e) {
            Log::error('Error eliminando registro', [
                'error' => $e->getMessage(),
                'id' => $ingreso->id
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar el registro: ' . $e->getMessage()
            ], 500);
        }
    }

    public function storeMassive(Request $request)
    {
        try {
            if (!$request->has('rows') || empty($request->input('rows'))) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se recibieron datos de filas'
                ], 422);
            }

            $savedCount = 0;
            $rows = $request->input('rows'); // Obtener filas correctamente ya sea de JSON o FormData

            // Process and save each row
            foreach ($request->rows as $index => $rowData) {
                try {
                    Log::info("Processing row {$index}", ['data' => $rowData]);

                    // Verificar que al menos tenga algunos campos básicos llenos
                    // Cambiado: Solo requerir fecha O médico (nombre o código) para considerar válida la fila
                    $hasBasicData = !empty($rowData['fecha']) || !empty($rowData['medico']) || !empty($rowData['cm']);

                    if ($hasBasicData) {
                        // Limpiar campos completamente vacíos y normalizar fecha
                        $cleanData = array_filter($rowData, function ($value) {
                            return $value !== '' && $value !== null;
                        });

                        // Normalizar fecha y calcular automáticamente Año, Mes y Semana Epidemiológica
                        if (!empty($cleanData['fecha'])) {
                            try {
                                $carbonFecha = Carbon::parse($cleanData['fecha']);
                                $cleanData['fecha'] = $carbonFecha->format('Y-m-d');
                                $cleanData['ano'] = (string)$carbonFecha->year;
                                $monthNames = ["ENERO", "FEBRERO", "MARZO", "ABRIL", "MAYO", "JUNIO", "JULIO", "AGOSTO", "SEPTIEMBRE", "OCTUBRE", "NOVIEMBRE", "DICIEMBRE"];
                                $cleanData['mes'] = $monthNames[$carbonFecha->month - 1];
                                $cleanData['se'] = (string)\App\Services\ExcelImportService::calcularSemanaEpidemiologica($carbonFecha);
                            } catch (\Exception $e) {
                                Log::warning("Error normalizando fecha en storeMassive: " . $cleanData['fecha']);
                            }
                        }

                        // Normalizar y calcular automáticamente Rangos 1 a 5 si hay edad y tipo
                        if (!empty($cleanData['edad']) && !empty($cleanData['tipo'])) {
                            $rangosCalculados = \App\Services\ExcelImportService::calcularRangosEpidemiologicos($cleanData['edad'], $cleanData['tipo']);
                            $cleanData['rango'] = $rangosCalculados['rango'];
                            $cleanData['rango_2'] = $rangosCalculados['rango_2'];
                            $cleanData['rango_3'] = $rangosCalculados['rango_3'];
                            $cleanData['rango_4'] = $rangosCalculados['rango_4'];
                            $cleanData['rango_5'] = $rangosCalculados['rango_5'];
                        }

                        // Normalizar médico con catálogo de BD
                        $this->normalizarMedicoYProf($cleanData);

                        $created = RegistroGlobal::create($cleanData);
                        $this->syncPacienteFromRow($cleanData);
                        Log::info("Row {$index} saved successfully", ['id' => $created->id, 'clean_data' => $cleanData]);

                        // Sincronizar automáticamente con tabla ATA
                        // $this->syncToATA($cleanData); // Comentado - clase ATA no existe

                        $savedCount++;
                    }
                    else {
                        \Log::warning("Row {$index} has no basic data (fecha or medico), skipping", ['data' => $rowData]);
                    }
                }
                catch (\Exception $e) {
                    \Log::error("Error saving row {$index}", [
                        'error' => $e->getMessage(),
                        'data' => $rowData,
                        'sql_state' => $e->getCode(),
                        'trace' => $e->getTraceAsString()
                    ]);

                    // Retornar error específico al frontend
                    return response()->json([
                        'success' => false,
                        'message' => "Error en fila {$index}: " . $e->getMessage(),
                        'row_data' => $rowData
                    ], 422);
                }
            }

            Log::info("Successfully saved {$savedCount} records");

            if (ob_get_length()) ob_clean();
            return response()->json([
                'success' => true,
                'message' => "Se guardaron {$savedCount} registros correctamente"
            ]);

        }
        catch (\Throwable $e) {
            \Log::error('Error en storeMassive', ['error' => $e->getMessage()]);
            if (ob_get_length()) ob_clean();
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Actualización masiva de registros
     */
    public function batchUpdate(Request $request)
    {
        try {
            // Usar 'rows' para coincidir con el frontend
            $registros = $request->input('rows', []);

            if (empty($registros)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se recibieron registros para actualizar'
                ], 400);
            }

            Log::info('Iniciando actualización masiva', [
                'total_registros' => count($registros)
            ]);

            $updatedCount = 0;
            $errors = [];

            foreach ($registros as $index => $registro) {
                try {
                    $id = $registro['id'] ?? null;

                    if (!$id) {
                        $errors[] = "Registro {$index}: ID faltante";
                        continue;
                    }

                    $ingreso = RegistroGlobal::find($id);

                    if (!$ingreso) {
                        $errors[] = "Registro {$index}: No se encontró el ingreso con ID {$id}";
                        continue;
                    }

                    // Campos permitidos para actualización (coinciden con registros_globales y RegistroGlobal::$fillable)
                    $camposPermitidos = [
                        'ano', 'mes', 'numero', 'cm', 'medico', 'prof', 'fecha', 'se', 'exp',
                        'identidad', 'nombre_paciente', 'telefono', 'fecha_nacimiento', 'etnia',
                        'sexo', 'edad', 'tipo', 'rango', 'rango_2', 'rango_3', 'rango_4', 'rango_5',
                        'cond', 'cod_col', 'colonia', 'cod_1', 'diagnostico_1', 'cond_1', 'sg',
                        'cod_2', 'diagnostico_2', 'cond_2', 'cod_3', 'diagnostico_3', 'cond_3',
                        'cod_4', 'diagnostico_4', 'cond_4', 'cod_5', 'diagnostico_5', 'cond_5',
                        'cod_6', 'diagnostico_6', 'cond_6', 'cod_7', 'diagnostico_7', 'cond_7',
                        'referido_a', 'referido_de', 'pg_emb', 'jornada', 'sm', 'sg2'
                    ];

                    $datosActualizacion = [];

                    foreach ($camposPermitidos as $campo) {
                        if (array_key_exists($campo, $registro)) {
                            $valor = $registro[$campo];

                            // Conversiones específicas
                            if ($campo === 'fecha' && !empty($valor)) {
                                $datosActualizacion[$campo] = Carbon::parse($valor)->format('Y-m-d');
                            }
                            elseif (in_array($campo, ['edad', 'se', 'ano']) && $valor !== '') {
                                $datosActualizacion[$campo] = (int)$valor;
                            }
                            else {
                                $datosActualizacion[$campo] = $valor === '' ? null : $valor;
                            }
                        }
                    }

                    if (!empty($datosActualizacion)) {
                        $ingreso->update($datosActualizacion);
                        $this->syncPacienteFromRow($ingreso->toArray());
                        $updatedCount++;
                    }

                }
                catch (\Exception $e) {
                    $error = "Registro {$index} (ID: {$id}): " . $e->getMessage();
                    $errors[] = $error;

                    \Log::error('Error actualizando registro individual', [
                        'id' => $id,
                        'index' => $index,
                        'error' => $e->getMessage()
                    ]);
                }
            }

            if (!empty($errors)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Se completó con errores',
                    'updated_count' => $updatedCount,
                    'errors' => $errors
                ], 207);
            }

            if (ob_get_length()) ob_clean();
            return response()->json([
                'success' => true,
                'message' => "Se actualizaron {$updatedCount} registros correctamente",
                'updated_count' => $updatedCount
            ]);

        }
        catch (\Throwable $e) {
            \Log::error('Error en batchUpdate', [
                'error' => $e->getMessage()
            ]);

            if (ob_get_length()) ob_clean();
            return response()->json([
                'success' => false,
                'message' => 'Error interno del servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener el nombre del mes actual
     */
    private function getCurrentMonthName(): string
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];

        return $meses[(int)date('n')];
    }

    /**
     * Obtener médicos filtrados SOLO por fecha/mes (AJAX)
     */
    public function medicosPorFecha(Request $request)
    {
        try {
            $ano = $request->input('ano');
            $mes = $request->input('mes');
            $fechaEspecifica = $request->input('fecha_calendario');

            Log::info('medicosPorFecha llamado', [
                'ano' => $ano,
                'mes' => $mes,
                'fecha_especifica' => $fechaEspecifica
            ]);

            $query = RegistroGlobal::distinct('medico')
                ->whereNotNull('medico')
                ->where('medico', '!=', '')
                ->orderBy('medico');

            // Si hay fecha específica, usarla (tiene prioridad sobre año/mes)
            if (!empty($fechaEspecifica)) {
                $query->whereDate('fecha', $fechaEspecifica);
            }
            else {
                // Solo usar año/mes si no hay fecha específica
                if (!empty($ano)) {
                    $query->whereYear('fecha', $ano);
                }

                if (!empty($mes)) {
                    $query->whereMonth('fecha', $mes);
                }
            }

            // Si hay jornada, filtrar también
            if (!empty($request->input('jornada'))) {
                $query->where('jornada', $request->input('jornada'));
            }

            $medicos = $query->pluck('medico');

            Log::info('Médicos encontrados', [
                'total' => $medicos->count(),
                'medicos' => $medicos->toArray()
            ]);

            if (ob_get_length()) ob_clean();
            return response()->json([
                'success' => true,
                'medicos' => $medicos
            ]);

        }
        catch (\Throwable $e) {
            \Log::error('Error en medicosPorFecha', [
                'error' => $e->getMessage()
            ]);

            if (ob_get_length()) ob_clean();
            return response()->json([
                'success' => false,
                'error' => 'Error al cargar médicos'
            ], 500);
        }
    }

    /**
     * Obtener jornadas filtradas por médico y fecha (AJAX)
     */
    public function jornadasPorMedico(Request $request)
    {
        try {
            $medico = $request->input('medico');
            $ano = $request->input('ano');
            $mes = $request->input('mes');
            $fechaEspecifica = $request->input('fecha_calendario');

            $query = RegistroGlobal::distinct('jornada')
                ->whereNotNull('jornada')
                ->where('jornada', '!=', '');

            if (!empty($medico)) {
                $query->where('medico', $medico);
            }

            if (!empty($fechaEspecifica)) {
                $query->whereDate('fecha', $fechaEspecifica);
            }
            else {
                if (!empty($ano))
                    $query->whereYear('fecha', $ano);
                if (!empty($mes))
                    $query->whereMonth('fecha', $mes);
            }

            $jornadas = $query->orderBy('jornada')->pluck('jornada');

            if (ob_get_length()) ob_clean();
            return response()->json([
                'success' => true,
                'jornadas' => $jornadas
            ]);
        }
        catch (\Throwable $e) {
            if (ob_get_length()) ob_clean();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Convertir nombre de mes a número
     */
    private function getMonthNumber(string $monthName): int
    {
        // Si ya es un número, devolverlo
        if (is_numeric($monthName)) {
            return (int)$monthName;
        }

        $meses = [
            'Enero' => 1, 'Febrero' => 2, 'Marzo' => 3, 'Abril' => 4,
            'Mayo' => 5, 'Junio' => 6, 'Julio' => 7, 'Agosto' => 8,
            'Septiembre' => 9, 'Octubre' => 10, 'Noviembre' => 11, 'Diciembre' => 12
        ];

        // Normalizar: capitalizar primera letra, resto en minúsculas
        $normalized = ucfirst(strtolower(trim($monthName)));

        return $meses[$normalized] ?? (int)date('n');
    }

    /**
     * Muestra la vista de Ingresos Dos (Tabla editable AngularJS)
     */
    public function dos()
    {
        return redirect()->route('ingresos.create');
    }

    /**
     * Busca un paciente por número de identidad (DNI).
     * Prioridad: tabla pacientes → notificaciones_svs → registros_globales
     * Si no existe, lo crea en la tabla pacientes con los datos disponibles.
     * Calcula edad en días / meses / años y devuelve el tipo correspondiente.
     */
    public function buscarIdentidad(Request $request)
    {
        try {
            $identidadRaw = trim($request->input('identidad', ''));
            if (empty($identidadRaw)) {
                return response()->json(['success' => false, 'message' => 'Identidad vacía'], 400);
            }

            // Limpiar DNI (solo dígitos) y formatear como xxxx-xxxx-xxxxx
            $cleanDni = preg_replace('/\D/', '', $identidadRaw);
            $formattedDni = $identidadRaw;
            if (strlen($cleanDni) === 13) {
                $formattedDni = substr($cleanDni, 0, 4) . '-'
                              . substr($cleanDni, 4, 4) . '-'
                              . substr($cleanDni, 8, 5);
            }

            // ── 1. Buscar PRIMERO en la base de pacientes local ─────────────
            $paciente = Paciente::where(function ($q) use ($identidadRaw, $cleanDni, $formattedDni) {
                $q->where('dni', $identidadRaw)
                  ->orWhere('dni', $formattedDni)
                  ->orWhere('dni_limpio', $cleanDni);
                if (!empty($cleanDni)) {
                    $q->orWhereRaw("REPLACE(REPLACE(dni, '-', ''), ' ', '') = ?", [$cleanDni]);
                }
            })->first();

            if ($paciente && !empty($paciente->nombre_completo)
                && !str_starts_with(strtoupper($paciente->nombre_completo), 'PACIENTE DE')) {
                
                $telefono = $paciente->telefono;
                if (empty($telefono) || $telefono === '-') {
                    $svsTel = NotificacionSvs::where('no_documento', $cleanDni)->orWhere('no_documento', $formattedDni)->whereNotNull('telefono')->value('telefono');
                    if (!empty($svsTel) && $svsTel !== '-') {
                        $telefono = $svsTel;
                    } else {
                        $rgTel = DB::table('registros_globales')->where('identidad', $cleanDni)->orWhere('identidad', $formattedDni)->whereNotNull('telefono')->value('telefono');
                        if (!empty($rgTel) && $rgTel !== '-') {
                            $telefono = $rgTel;
                        }
                    }
                    if (!empty($telefono)) {
                        $paciente->telefono = $telefono;
                        $paciente->save();
                    }
                }

                $resultado = $this->buildIdentidadResult(
                    $paciente->nombre_completo,
                    $paciente->fecha_nacimiento,
                    $telefono,
                    $paciente->edad,
                    null,
                    $paciente->sexo ?? null,
                    $paciente->colonia ?? null
                );
                return response()->json(array_merge(['success' => true, 'origen' => 'pacientes'], $resultado));
            }

            // ── 2. Buscar en notificaciones_svs ──────────────────────────────
            $notif = NotificacionSvs::where(function ($q) use ($identidadRaw, $cleanDni, $formattedDni) {
                    $q->where('no_documento', $identidadRaw)
                      ->orWhere('no_documento', $formattedDni)
                      ->orWhere('no_documento', $cleanDni);
                    if (!empty($cleanDni)) {
                        $q->orWhereRaw("REPLACE(REPLACE(no_documento, '-', ''), ' ', '') = ?", [$cleanDni]);
                    }
                })
                ->whereNotNull('nombres')
                ->where('nombres', '!=', '')
                ->where('nombres', 'NOT LIKE', 'PACIENTE%')
                ->orderBy('id', 'desc')
                ->first();

            if ($notif) {
                $nombreFull = trim($notif->nombres . ' ' . ($notif->apellidos ?? ''));
                $resultado = $this->buildIdentidadResult(
                    $nombreFull,
                    $notif->fecha_nacimiento,
                    $notif->telefono ?? null,
                    $notif->edad,
                    null,
                    $notif->sexo ?? null,
                    $notif->colonia ?? null
                );

                $this->guardarEnPacientes($cleanDni, $formattedDni, $nombreFull, $notif->fecha_nacimiento, $notif->telefono ?? null, $notif->colonia ?? null, $notif->departamento ?? null, $notif->municipio ?? null, $notif->sexo ?? null);

                return response()->json(array_merge(['success' => true, 'origen' => 'notificaciones_svs'], $resultado));
            }

            // ── 3. Buscar en registros_globales ──────────────────────────────
            $regGlobal = DB::table('registros_globales')
                ->where(function ($q) use ($identidadRaw, $cleanDni, $formattedDni) {
                    $q->where('identidad', $identidadRaw)
                      ->orWhere('identidad', $formattedDni)
                      ->orWhere('identidad', $cleanDni);
                    if (!empty($cleanDni)) {
                        $q->orWhereRaw("REPLACE(REPLACE(identidad, '-', ''), ' ', '') = ?", [$cleanDni]);
                    }
                })
                ->whereNotNull('nombre_paciente')
                ->where('nombre_paciente', '!=', '')
                ->orderBy('id', 'desc')
                ->first();

            if ($regGlobal && !empty($regGlobal->nombre_paciente)) {
                $resultado = $this->buildIdentidadResult(
                    $regGlobal->nombre_paciente,
                    $regGlobal->fecha_nacimiento ?? null,
                    $regGlobal->telefono ?? null,
                    $regGlobal->edad ?? null,
                    $regGlobal->tipo ?? null,
                    $regGlobal->sexo ?? null,
                    $regGlobal->colonia ?? null
                );

                $this->guardarEnPacientes($cleanDni, $formattedDni, $regGlobal->nombre_paciente, $regGlobal->fecha_nacimiento ?? null, $regGlobal->telefono ?? null, $regGlobal->colonia ?? null, null, null, $regGlobal->sexo ?? null);

                return response()->json(array_merge(['success' => true, 'origen' => 'registros_globales'], $resultado));
            }

            // ── 4. SI NO ESTÁ EN BD LOCAL: Consultar a SNVS / SESAL externo ──
            try {
                $reqSesal = new Request(['identidad' => $cleanDni ?: $identidadRaw]);
                $pruebaCtrl = app(PruebaConsultaController::class);
                $res = $pruebaCtrl->buscar($reqSesal);

                if ($res && method_exists($res, 'getData')) {
                    $json = $res->getData(true);
                    $dataObj = isset($json['data']) && is_array($json['data']) ? $json['data'] : $json;

                    $nombreExterno = $dataObj['nombre_completo']
                        ?? trim(($dataObj['nombres'] ?? '') . ' ' . ($dataObj['apellidos'] ?? ''))
                        ?: null;

                    if (!empty($nombreExterno) && !str_starts_with(strtoupper($nombreExterno), 'PACIENTE DE')) {
                        $fnac = $dataObj['fecha_nacimiento'] ?? null;
                        $tel  = $dataObj['telefono'] ?? null;
                        $col  = $dataObj['colonia'] ?? ($dataObj['direccion'] ?? null);
                        $dep  = $dataObj['departamento'] ?? null;
                        $mun  = $dataObj['municipio'] ?? null;
                        $edadS = $dataObj['edad'] ?? null;
                        $sexoS = $dataObj['genero'] ?? ($dataObj['sexo'] ?? null);

                        $resultado = $this->buildIdentidadResult($nombreExterno, $fnac, $tel, $edadS, null, $sexoS, $col);
                        // Guardar en tabla local pacientes para consultas inmediatas futuras
                        $this->guardarEnPacientes($cleanDni, $formattedDni, $nombreExterno, $fnac, $tel, $col, $dep, $mun, $sexoS);
                        return response()->json(array_merge(['success' => true, 'origen' => 'snvs'], $resultado));
                    }
                }
            } catch (\Throwable $e) {
                Log::warning("buscarIdentidad SNVS/SESAL error: " . $e->getMessage());
            }

            // ── 5. No encontrado ─────────────────────────────────────────────
            return response()->json([
                'success'          => false,
                'origen'           => 'no_encontrado',
                'nombre_paciente'  => '',
                'fecha_nacimiento' => '',
                'telefono'         => '',
                'edad'             => '',
                'tipo'             => '',
                'sexo'             => '',
                'colonia'          => '',
                'message'          => 'Paciente no encontrado en BD ni SNVS. Puede ingresar los datos manualmente.',
            ]);

        } catch (\Throwable $e) {
            Log::error('Error en buscarIdentidad', ['error' => $e->getMessage()]);
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Calcula edad desde fecha de nacimiento y devuelve edad + tipo (D/M/A).
     */
    private function buildIdentidadResult(string $nombre, $fechaNacimiento, $telefono, $edadRaw = null, $tipoRaw = null, $sexoRaw = null, $coloniaRaw = null, $codColRaw = null): array
    {
        $edad = '';
        $tipo = '';
        $fechaNacStr = '';

        if (!empty($fechaNacimiento)) {
            try {
                // Soportar dd/mm/yyyy y yyyy-mm-dd
                $carbon = null;
                if (preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $fechaNacimiento)) {
                    $carbon = Carbon::createFromFormat('d/m/Y', $fechaNacimiento);
                } else {
                    $carbon = Carbon::parse($fechaNacimiento);
                }

                if ($carbon && $carbon->year > 1900) {
                    $now = Carbon::now();
                    $fechaNacStr = $carbon->format('Y-m-d'); // formato para input[type=date]

                    $diffDias  = (int)$carbon->diffInDays($now);
                    $diffMeses = (int)$carbon->diffInMonths($now);
                    $diffAnios = (int)$carbon->diffInYears($now);

                    if ($diffDias < 30) {
                        $edad = $diffDias;
                        $tipo = 'D';
                    } elseif ($diffMeses < 12) {
                        $edad = $diffMeses;
                        $tipo = 'M';
                    } else {
                        $edad = $diffAnios;
                        $tipo = 'A';
                    }
                }
            } catch (\Throwable $e) {
                // fecha inválida, se deja vacío
            }
        }

        // Si no se pudo calcular edad de fecha_nacimiento, usar valores consignados si existen
        if ($edad === '' && $edadRaw !== null && $edadRaw !== '') {
            $edad = (string)$edadRaw;
            $tipo = !empty($tipoRaw) ? strtoupper($tipoRaw) : 'A';
        }

        $tel = (string)($telefono ?? '');
        if ($tel === '-' || $tel === '') $tel = '';

        $sexo = '';
        if (!empty($sexoRaw)) {
            $s = strtoupper(trim((string)$sexoRaw));
            if ($s === 'MASCULINO' || $s === 'H' || ($s === 'M' && str_contains($s, 'MASC'))) {
                $sexo = 'H';
            } elseif ($s === 'FEMENINO' || $s === 'F' || ($s === 'M' && !str_contains($s, 'MASC'))) {
                $sexo = 'M';
            } elseif ($s === 'H' || $s === 'M') {
                $sexo = $s;
            }
        }

        return [
            'nombre_paciente'  => strtoupper(trim($nombre)),
            'fecha_nacimiento' => $fechaNacStr,
            'telefono'         => $tel,
            'edad'             => (string)$edad,
            'tipo'             => $tipo,
            'sexo'             => $sexo,
            'colonia'          => $coloniaRaw ? strtoupper(trim($coloniaRaw)) : '',
            'cod_col'          => $codColRaw ? strtoupper(trim($codColRaw)) : '',
        ];
    }

    /**
     * Guarda o actualiza el paciente en la tabla local pacientes.
     */
    private function guardarEnPacientes(string $cleanDni, string $formattedDni, string $nombre, $fechaNac, $telefono, $colonia = null, $depto = null, $muni = null, $sexo = null): void
    {
        try {
            if (empty($cleanDni)) return;

            $tel = ($telefono === '-' || $telefono === '') ? null : $telefono;
            $edad = null;
            if (!empty($fechaNac)) {
                try {
                    $carbon = preg_match('/^\d{2}\/\d{2}\/\d{4}$/', $fechaNac)
                        ? Carbon::createFromFormat('d/m/Y', $fechaNac)
                        : Carbon::parse($fechaNac);
                    if ($carbon && $carbon->year > 1900) $edad = $carbon->diffInYears(Carbon::now());
                } catch (\Throwable $e) {}
            }

            $sexoNorm = null;
            if (!empty($sexo)) {
                $s = strtoupper(trim((string)$sexo));
                if (in_array($s, ['H', 'MASCULINO']) || ($s === 'M' && str_contains($s, 'MASC'))) {
                    $sexoNorm = 'H';
                } else if (in_array($s, ['M', 'FEMENINO', 'F'])) {
                    $sexoNorm = 'M';
                }
            }

            $data = [
                'nombre_completo' => strtoupper(trim($nombre)),
                'dni'             => $formattedDni ?: $cleanDni,
                'dni_limpio'      => $cleanDni,
            ];
            if (!empty($fechaNac)) $data['fecha_nacimiento'] = $fechaNac;
            if (!empty($tel))      $data['telefono'] = $tel;
            if ($edad !== null)    $data['edad'] = $edad;
            if (!empty($colonia))  $data['colonia'] = $colonia;
            if (!empty($depto))    $data['departamento'] = $depto;
            if (!empty($muni))     $data['municipio'] = $muni;
            if (!empty($sexoNorm)) $data['sexo'] = $sexoNorm;

            Paciente::updateOrCreate(
                ['dni_limpio' => $cleanDni],
                $data
            );
        } catch (\Throwable $e) {
            Log::warning('guardarEnPacientes error: ' . $e->getMessage());
        }
    }

    /**
     * Sincroniza los datos del paciente desde una fila de atenciones o ingresos.
     */
    private function syncPacienteFromRow(array $data): void
    {
        try {
            $identidad = $data['identidad'] ?? '';
            $cleanDni = preg_replace('/\D/', '', $identidad);
            if (empty($cleanDni) || strlen($cleanDni) < 5) return;

            $nombre = $data['nombre_paciente'] ?? '';
            if (empty($nombre) || str_starts_with(strtoupper(trim($nombre)), 'PACIENTE DE')) return;

            $formattedDni = (strlen($cleanDni) === 13)
                ? substr($cleanDni, 0, 4) . '-' . substr($cleanDni, 4, 4) . '-' . substr($cleanDni, 8)
                : ($identidad ?: $cleanDni);

            $fechaNac = $data['fecha_nacimiento'] ?? null;
            $tel      = $data['telefono'] ?? null;
            $colonia  = $data['colonia'] ?? null;

            $this->guardarEnPacientes($cleanDni, $formattedDni, $nombre, $fechaNac, $tel, $colonia);
        } catch (\Throwable $e) {
            Log::warning('syncPacienteFromRow error: ' . $e->getMessage());
        }
    }

    /**
     * Normaliza el nombre y código del médico contra la base de datos oficial
     */
    protected function normalizarMedicoYProf(array &$data): void
    {
        $medico = $data['medico'] ?? null;
        $cm = $data['cm'] ?? null;

        if (empty($medico) && empty($cm)) {
            return;
        }

        $medicos = Medico::all();
        $encontrado = null;

        // 1. Buscar por código si está presente
        if (!empty($cm)) {
            $encontrado = $medicos->firstWhere('COD_MED', trim((string)$cm));
        }

        // 2. Buscar por nombre exacto
        if (!$encontrado && !empty($medico)) {
            $medicoUpper = mb_strtoupper(trim((string)$medico), 'UTF-8');
            $encontrado = $medicos->firstWhere('NOM_MED', $medicoUpper);
        }

        // 3. Buscar usando normalización de clave y alias
        if (!$encontrado && !empty($medico)) {
            $aliasMap = [
                'ANDREA MEJIA' => 'MSS. ANDREA MICHELLE MEJIA MORAZAN',
                'DRA. MAGALY COELLO' => 'DRA. MAGALY ROCIO COELLO GARCIA',
                'MAGALY COELLO' => 'DRA. MAGALY ROCIO COELLO GARCIA',
                'ISSIS NOHEMY RIVAS ARTILES' => 'DRA. ISSIS NOHEMY RIVAS ARTILES',
                'DRA. ISSIS RIVAS' => 'DRA. ISSIS NOHEMY RIVAS ARTILES',
                'DRA.ISSIS RIVAS' => 'DRA. ISSIS NOHEMY RIVAS ARTILES',
                'KATHERINE ATENA FERNANDEZ PEREZ' => 'MSS.KATHERINE ATENA FERNANDEZ PEREZ',
                'MARCELA DE JESÚS CRUZ COLINDRES' => 'MSS. MARCELA DE JESUS CRUZ COLINDRES',
                'MARCELA DE JESUS CRUZ COLINDRES' => 'MSS. MARCELA DE JESUS CRUZ COLINDRES',
                'DRA. YUSEN NUÑEZ' => 'DRA. YUSEN NIESVANOVA NUÑEZ',
                'DR. EDWIN JOSUE ESPINAL MARTINEZ' => 'DR. EDWIN JOSE ESPINAL MARTINEZ',
            ];

            $medicoUpper = mb_strtoupper(trim((string)$medico), 'UTF-8');
            if (isset($aliasMap[$medicoUpper])) {
                $target = $aliasMap[$medicoUpper];
                $encontrado = $medicos->firstWhere('NOM_MED', $target);
            }

            if (!$encontrado) {
                $cleanInput = \App\Services\ExcelImportService::normalizarClaveMedico($medico);
                if (!empty($cleanInput)) {
                    $encontrado = $medicos->first(function($m) use ($cleanInput) {
                        return \App\Services\ExcelImportService::normalizarClaveMedico($m->NOM_MED) === $cleanInput;
                    });

                    if (!$encontrado) {
                        $palabras = array_filter(explode(' ', $cleanInput), fn($p) => strlen($p) > 2);
                        if (!empty($palabras)) {
                            $cands = $medicos->filter(function($m) use ($palabras) {
                                $cleanM = \App\Services\ExcelImportService::normalizarClaveMedico($m->NOM_MED);
                                foreach ($palabras as $p) {
                                    if (!str_contains($cleanM, $p)) return false;
                                }
                                return true;
                            });
                            if ($cands->count() === 1) {
                                $encontrado = $cands->first();
                            }
                        }
                    }
                }
            }
        }

        if ($encontrado) {
            $data['medico'] = $encontrado->NOM_MED;
            $data['cm'] = $encontrado->COD_MED;
            if (empty($data['prof']) || $data['prof'] === 'MÉDICO GENERAL' || $data['prof'] === 'MEDICO GENERAL') {
                $data['prof'] = $encontrado->ESPECIALIDAD ?: 'MEDICO GENERAL';
            }
            if (empty($data['jornada']) || in_array($data['jornada'], ['M', 'V', 'F', 'FS'])) {
                $data['jornada'] = !empty($encontrado->JORNADA) ? $encontrado->JORNADA : ($data['jornada'] === 'V' ? 'VESPERTINA' : ($data['jornada'] === 'FS' || $data['jornada'] === 'F' ? 'FIN DE SEMANA' : 'MATUTINA'));
            }
        }
    }
}

