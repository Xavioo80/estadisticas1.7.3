<?php

namespace App\Http\Controllers;

use App\Models\Importacion;
use App\Models\ImportacionRegistro;
use App\Services\ExcelImportService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ImportacionExcelController extends Controller
{
    protected ExcelImportService $importService;

    public function __construct(ExcelImportService $importService)
    {
        $this->importService = $importService;
    }

    /**
     * Vista Principal: Listado y Monitoreo de Registros Importados desde Excel (Registro Universal Dinámico)
     */
    public function index(Request $request)
    {
        $search = $request->input('search');
        $estado = $request->input('estado');
        $fecha = $request->input('fecha');
        $medico = $request->input('medico');
        $importacionId = $request->input('importacion_id');
        $perPage = (int)$request->input('per_page', 35);

        $query = ImportacionRegistro::with('importacion')->latest('id');

        if (!empty($search)) {
            $query->where(function ($q) use ($search) {
                $q->where('nombre_paciente', 'LIKE', "%{$search}%")
                  ->orWhere('numero_identidad', 'LIKE', "%{$search}%")
                  ->orWhere('expediente', 'LIKE', "%{$search}%")
                  ->orWhere('colonia_normalizada', 'LIKE', "%{$search}%")
                  ->orWhere('direccion_original', 'LIKE', "%{$search}%");
            });
        }

        if (!empty($estado)) {
            $query->where('estado', $estado);
        }

        if (!empty($fecha)) {
            $query->whereDate('fecha_atencion', $fecha);
        }

        if (!empty($medico)) {
            $query->where('medico', $medico);
        }

        if (!empty($importacionId)) {
            $query->where('importacion_id', $importacionId);
        }

        $registros = $query->paginate($perPage)->withQueryString();

        // Enriquecer con cotejo dinámico día-médico y paciente en BD
        $this->enriquecerRegistrosConCotejo($registros->getCollection());

        // Respuesta JSON para Infinite Scroll dinámico
        if ($request->ajax() || $request->wantsJson() || $request->input('ajax') === '1') {
            $filasHtml = [];
            foreach ($registros as $reg) {
                $dxList = is_array($reg->diagnosticos_json) ? $reg->diagnosticos_json : [];
                $dx1 = $dxList[0] ?? null;
                $dxCodigo = $dx1['codigo'] ?? '';
                $dxNombre = $dx1['diagnostico'] ?? ($dx1['original'] ?? 'Sin Diagnóstico');

                $filasHtml[] = [
                    'id' => $reg->id,
                    'fila_excel' => $reg->fila_excel ?: $reg->id,
                    'fecha_formato' => $reg->fecha_atencion ? $reg->fecha_atencion->format('d/m/Y') : '-',
                    'medico' => $reg->medico ?: 'SIN MÉDICO',
                    'prof' => $reg->prof ?: 'MÉDICO GENERAL',
                    'identidad' => $reg->numero_identidad ?: 'SIN DNI',
                    'expediente' => $reg->expediente,
                    'nombre_paciente' => $reg->nombre_paciente ?: 'SIN NOMBRE',
                    'sexo_edad' => ($reg->sexo == 'M' || $reg->sexo == 'H' ? 'Hombre' : ($reg->sexo == 'F' ? 'Mujer' : $reg->sexo)) . ($reg->edad ? ' • ' . $reg->edad . ' años' : ''),
                    'colonia' => ($reg->cod_col ? '[' . $reg->cod_col . '] ' : '') . ($reg->colonia_normalizada ?: ($reg->direccion_original ?: 'No asignada')),
                    'diagnostico' => ($dxCodigo ? '[' . $dxCodigo . '] ' : '') . $dxNombre,
                    'estado' => $reg->estado,
                    'motivo_estado' => $reg->motivo_estado,
                    'archivo_lote' => $reg->importacion ? $reg->importacion->nombre_archivo : 'Lote #' . $reg->importacion_id,
                    'created_at_formato' => $reg->created_at ? $reg->created_at->format('d/m/Y H:i') : '',
                    'cotejo_label' => $reg->cotejo_label,
                    'cotejo_class' => $reg->cotejo_class,
                    'cotejo_tooltip' => $reg->cotejo_tooltip,
                    'bd_dia_medico_total' => $reg->bd_dia_medico_total,
                    'bd_paciente_en_dia' => $reg->bd_paciente_en_dia,
                    'bd_paciente_historial_total' => $reg->bd_paciente_historial_total,
                ];
            }

            return response()->json([
                'success' => true,
                'registros' => $filasHtml,
                'current_page' => $registros->currentPage(),
                'last_page' => $registros->lastPage(),
                'has_more' => $registros->hasMorePages(),
                'total' => $registros->total(),
                'first_item' => $registros->firstItem() ?? 0,
                'last_item' => $registros->lastItem() ?? 0,
            ]);
        }

        // Estadísticas de importación
        $stats = [
            'total_registros' => ImportacionRegistro::count(),
            'importados' => ImportacionRegistro::where('estado', 'IMPORTADO')->count(),
            'nuevos' => ImportacionRegistro::where('estado', 'NUEVO')->count(),
            'pendientes' => ImportacionRegistro::where('estado', 'PENDIENTE_REVISION')->count(),
            'ya_existentes' => ImportacionRegistro::where('estado', 'YA_EXISTE')->count(),
            'lotes' => Importacion::count(),
        ];

        // Catálogos para filtros
        $medicos = ImportacionRegistro::whereNotNull('medico')
            ->where('medico', '!=', '')
            ->distinct()
            ->orderBy('medico')
            ->pluck('medico');

        $importaciones = Importacion::latest('id')->take(20)->get();

        return view('ingresos.importaciones', compact(
            'registros',
            'stats',
            'medicos',
            'importaciones',
            'search',
            'estado',
            'fecha',
            'medico',
            'importacionId'
        ));
    }

    /**
     * Cotejo Dinámico de atenciones con registros_globales (por 4 Ejes: Día, Médico, Total Atenciones en BD e Historia Clínica/DNI)
     */
    protected function enriquecerRegistrosConCotejo($registros): void
    {
        if ($registros->isEmpty()) {
            return;
        }

        $fechas = [];
        $medicos = [];
        $identidades = [];
        $expedientes = [];

        foreach ($registros as $r) {
            if ($r->fecha_atencion) {
                $fStr = $r->fecha_atencion->format('Y-m-d');
                $fechas[$fStr] = true;
            }
            if (!empty($r->medico)) {
                $medicos[$r->medico] = true;
            }
            if (!empty($r->numero_identidad)) {
                $identidades[$r->numero_identidad] = true;
                $limpio = preg_replace('/\D/', '', $r->numero_identidad);
                if (!empty($limpio)) {
                    $identidades[$limpio] = true;
                }
            }
            if (!empty($r->expediente)) {
                $expedientes[trim((string)$r->expediente)] = true;
            }
        }

        // 1. Conteos de atenciones por día y médico en registros_globales
        $conteosDiaMedico = DB::table('registros_globales')
            ->select('fecha', 'medico', DB::raw('COUNT(*) as total'))
            ->whereIn('fecha', array_keys($fechas))
            ->whereIn('medico', array_keys($medicos))
            ->groupBy('fecha', 'medico')
            ->get()
            ->keyBy(function ($row) {
                return $row->fecha . '|' . mb_strtoupper(trim($row->medico));
            });

        // 2. Coincidencias exactas de paciente en el mismo día y médico (por Identidad / DNI)
        $pacientesEnDiaPorDni = !empty($identidades) ? DB::table('registros_globales')
            ->select('fecha', 'medico', 'identidad', 'exp', 'id')
            ->whereIn('fecha', array_keys($fechas))
            ->whereIn('medico', array_keys($medicos))
            ->whereIn('identidad', array_keys($identidades))
            ->get()
            ->groupBy(function ($row) {
                return $row->fecha . '|' . mb_strtoupper(trim($row->medico)) . '|' . trim($row->identidad);
            }) : collect();

        // 3. Coincidencias de paciente en el mismo día por Historia Clínica / Expediente
        $pacientesEnDiaPorExp = !empty($expedientes) ? DB::table('registros_globales')
            ->select('fecha', 'medico', 'exp', 'id')
            ->whereIn('fecha', array_keys($fechas))
            ->whereIn('medico', array_keys($medicos))
            ->whereIn('exp', array_keys($expedientes))
            ->get()
            ->groupBy(function ($row) {
                return $row->fecha . '|' . mb_strtoupper(trim($row->medico)) . '|' . trim($row->exp);
            }) : collect();

        // 4. Historial total de atenciones por DNI y por Historia Clínica
        $historialPacientesDni = !empty($identidades) ? DB::table('registros_globales')
            ->select('identidad', DB::raw('COUNT(*) as total'))
            ->whereIn('identidad', array_keys($identidades))
            ->groupBy('identidad')
            ->pluck('total', 'identidad') : collect();

        $historialPacientesExp = !empty($expedientes) ? DB::table('registros_globales')
            ->select('exp', DB::raw('COUNT(*) as total'))
            ->whereIn('exp', array_keys($expedientes))
            ->groupBy('exp')
            ->pluck('total', 'exp') : collect();

        // Asignación de badges y estado de cotejo a cada registro
        foreach ($registros as $r) {
            $fStr = $r->fecha_atencion ? $r->fecha_atencion->format('Y-m-d') : '';
            $mKey = mb_strtoupper(trim($r->medico ?? ''));
            $expKey = trim((string)($r->expediente ?? ''));
            $dniKey = trim((string)($r->numero_identidad ?? ''));

            $dmKey = $fStr . '|' . $mKey;
            $dmpKeyDni = $fStr . '|' . $mKey . '|' . $dniKey;
            $dmpKeyExp = $fStr . '|' . $mKey . '|' . $expKey;

            $totalDiaMedico = isset($conteosDiaMedico[$dmKey]) ? (int)$conteosDiaMedico[$dmKey]->total : 0;
            
            $estaEnBdEseDia = (!empty($dniKey) && isset($pacientesEnDiaPorDni[$dmpKeyDni]) && $pacientesEnDiaPorDni[$dmpKeyDni]->isNotEmpty())
                || (!empty($expKey) && isset($pacientesEnDiaPorExp[$dmpKeyExp]) && $pacientesEnDiaPorExp[$dmpKeyExp]->isNotEmpty());

            $totalHistorialDni = !empty($dniKey) && isset($historialPacientesDni[$dniKey]) ? (int)$historialPacientesDni[$dniKey] : 0;
            $totalHistorialExp = !empty($expKey) && isset($historialPacientesExp[$expKey]) ? (int)$historialPacientesExp[$expKey] : 0;
            $totalHistorial = max($totalHistorialDni, $totalHistorialExp);

            $r->bd_dia_medico_total = $totalDiaMedico;
            $r->bd_paciente_en_dia = $estaEnBdEseDia;
            $r->bd_paciente_historial_total = $totalHistorial;

            if ($r->estado === 'IMPORTADO' || $estaEnBdEseDia) {
                $r->cotejo_badge = 'YA_EN_BD';
                $r->cotejo_label = 'En BD (' . ($totalHistorial > 0 ? $totalHistorial . ' atenc.' : 'Guardado') . ')';
                $r->cotejo_class = 'badge-success';
                $r->cotejo_tooltip = "Atención ya registrada en BD oficial para este día y médico ({$totalDiaMedico} en el día)";
            } elseif ($r->estado === 'DUPLICADO') {
                $r->cotejo_badge = 'DUPLICADO';
                $r->cotejo_label = 'Duplicado Excel';
                $r->cotejo_class = 'badge-purple';
                $r->cotejo_tooltip = 'Historia clínica o DNI repetido en el mismo archivo Excel';
            } elseif ($totalDiaMedico > 0) {
                $r->cotejo_badge = 'COINCIDENCIA_DIA';
                $r->cotejo_label = "Día c/{$totalDiaMedico} en BD";
                $r->cotejo_class = 'badge-info';
                $r->cotejo_tooltip = "El Dr/Dra ya tiene {$totalDiaMedico} atenciones en BD en esta fecha. El registro se anexará.";
            } else {
                $r->cotejo_badge = 'UNICO';
                $r->cotejo_label = 'Día Nuevo (0 en BD)';
                $r->cotejo_class = 'badge-primary';
                $r->cotejo_tooltip = 'No hay registros previos en BD para esta fecha y médico. Listo para ingresar.';
            }
        }
    }

    /**
     * Paso 1: Cargar y analizar archivo Excel
     */
    public function analizar(Request $request): JsonResponse
    {
        $request->validate([
            'archivo' => 'required|file|mimes:xlsx,xls,csv|max:51200', // max 50MB
        ]);

        try {
            $file = $request->file('archivo');
            $originalName = $file->getClientOriginalName();
            $extension = $file->getClientOriginalExtension();

            // Guardar en storage/app/private/imports
            $tempFilename = 'import_' . time() . '_' . uniqid() . '.' . $extension;
            $path = $file->storeAs('imports', $tempFilename);
            $fullPath = Storage::path($path);

            $userId = Auth::id();

            $resultado = $this->importService->analizarArchivo($fullPath, $originalName, $userId);

            // Guardar ruta del archivo temporal en sesión o asociarla a la importación
            session(['import_file_' . $resultado['importacion_id'] => $fullPath]);

            return response()->json($resultado);

        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al leer el archivo Excel: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Paso 2 & 3: Filtrar filas por fechas y médicos, normalizar y comparar con histórico
     */
    public function filtrar(Request $request): JsonResponse
    {
        $importacionId = (int)$request->input('importacion_id');
        if (!$importacionId || !Importacion::where('id', $importacionId)->exists()) {
            $latest = Importacion::latest()->first();
            if ($latest) {
                $importacionId = $latest->id;
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'No se encontró una sesión de importación activa. Por favor, suba el archivo nuevamente.',
                ], 422);
            }
        }

        $fechas = (array)$request->input('fechas', []);
        $medicos = (array)$request->input('medicos', []);

        $filePath = session('import_file_' . $importacionId);

        if (!$filePath || !file_exists($filePath)) {
            $files = array_merge(
                glob(storage_path('app/private/imports/import_*')),
                glob(storage_path('app/imports/import_*'))
            );
            if (!empty($files)) {
                $filePath = end($files);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'El archivo temporal de la importación ha expirado. Por favor, cárguelo de nuevo.',
                ], 400);
            }
        }

        try {
            $resultado = $this->importService->filtrarYClasificar($importacionId, $fechas, $medicos, $filePath);
            return response()->json($resultado);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar y clasificar los registros: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Paso 4: Previsualizar registros clasificados
     */
    public function previsualizar(Request $request): JsonResponse
    {
        $request->validate([
            'importacion_id' => 'required|integer|exists:importaciones,id',
            'estado' => 'nullable|string',
            'page' => 'nullable|integer|min:1',
            'per_page' => 'nullable|integer|min:10|max:200',
        ]);

        $importacionId = (int)$request->input('importacion_id');
        $estado = $request->input('estado', 'TODOS');
        $page = (int)$request->input('page', 1);
        $perPage = (int)$request->input('per_page', 50);

        try {
            $resultado = $this->importService->obtenerPrevisualizacion($importacionId, $estado, $page, $perPage);
            return response()->json($resultado);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener la previsualización: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Catálogos completos de diagnósticos y colonias para búsqueda en el modal
     */
    public function catalogos(): JsonResponse
    {
        try {
            $diagnosticos = DB::table('diagnosticos')
                ->select('id', 'codigo', 'patologia', 'categoria')
                ->orderBy('patologia', 'asc')
                ->get();

            $colonias = DB::table('colonias')
                ->select('id', 'COD_COL as cod_col', 'COLONIA as colonia')
                ->orderByRaw('CAST(COD_COL AS UNSIGNED) ASC, COD_COL ASC')
                ->get();

            return response()->json([
                'success' => true,
                'diagnosticos' => $diagnosticos,
                'colonias' => $colonias,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar catálogos: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Paso 5: Corregir / Reasignar un registro individual
     */
    public function corregir(Request $request): JsonResponse
    {
        $request->validate([
            'registro_id' => 'required|integer|exists:importacion_registros,id',
            'colonia_normalizada' => 'nullable|string',
            'cod_col' => 'nullable|string',
            'colonia_id' => 'nullable|integer',
            'codigo' => 'nullable|string',
            'diagnostico' => 'nullable|string',
            'diagnostico_id' => 'nullable|integer',
            'diagnosticos_json' => 'nullable|array',
        ]);

        $registroId = (int)$request->input('registro_id');
        $datos = $request->only(['colonia_normalizada', 'cod_col', 'colonia_id', 'codigo', 'diagnostico', 'diagnostico_id', 'diagnosticos_json']);

        try {
            $resultado = $this->importService->corregirRegistro($registroId, $datos);
            return response()->json($resultado);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al corregir el registro: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Paso 6: Confirmar importación transaccional
     */
    public function confirmar(Request $request): JsonResponse
    {
        $importacionId = (int)$request->input('importacion_id');
        if (!$importacionId || !Importacion::where('id', $importacionId)->exists()) {
            $latest = Importacion::latest()->first();
            $importacionId = $latest ? $latest->id : 0;
        }

        if (!$importacionId) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontró la importación a confirmar.',
            ], 422);
        }

        $opciones = [
            'solo_nuevos' => filter_var($request->input('solo_nuevos', true), FILTER_VALIDATE_BOOLEAN),
            'modo' => $request->input('modo', 'anexar'),
            'ids' => (array)$request->input('ids', []),
        ];

        try {
            $resultado = $this->importService->confirmarImportacion($importacionId, $opciones);

            // Limpieza de archivo temporal si fue exitoso
            $filePath = session('import_file_' . $importacionId);
            if ($filePath && file_exists($filePath)) {
                @unlink($filePath);
            }
            session()->forget('import_file_' . $importacionId);

            return response()->json($resultado);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error crítico durante la importación transaccional: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Seguimiento Clínico e Historial Completo de Atenciones del Paciente (indexado por Identidad/DNI y/o Historia Clínica)
     */
    public function historialPaciente(Request $request): JsonResponse
    {
        $dni = trim((string)$request->input('dni', ''));
        $expediente = trim((string)$request->input('expediente', ''));
        $registroId = (int)$request->input('registro_id', 0);

        if ($registroId > 0) {
            $reg = ImportacionRegistro::find($registroId);
            if ($reg) {
                if (empty($dni)) $dni = $reg->numero_identidad ?? '';
                if (empty($expediente)) $expediente = $reg->expediente ?? '';
            }
        }

        if (empty($dni) && empty($expediente)) {
            return response()->json([
                'success' => false,
                'message' => 'Número de identidad o Historia Clínica no proporcionado.',
            ], 422);
        }

        $dniLimpio = preg_replace('/\D/', '', $dni);

        // 1. Información base del Paciente
        $paciente = null;
        if (!empty($dniLimpio)) {
            $paciente = DB::table('pacientes')
                ->where('dni_limpio', $dniLimpio)
                ->orWhere('dni', $dni)
                ->first();
        }
        if (!$paciente && !empty($expediente)) {
            $paciente = DB::table('pacientes')
                ->where('expediente', $expediente)
                ->first();
        }

        // 2. Historial en registros_globales (Histórico Oficial AT-1)
        $atencionesGlobales = DB::table('registros_globales')
            ->where(function ($q) use ($dni, $dniLimpio, $expediente) {
                $conds = false;
                if (!empty($dni)) {
                    $q->where('identidad', $dni);
                    $conds = true;
                    if (!empty($dniLimpio)) {
                        $q->orWhereRaw("REPLACE(REPLACE(identidad, '-', ''), ' ', '') = ?", [$dniLimpio]);
                    }
                }
                if (!empty($expediente)) {
                    if ($conds) {
                        $q->orWhere('exp', $expediente);
                    } else {
                        $q->where('exp', $expediente);
                    }
                }
            })
            ->orderBy('fecha', 'desc')
            ->get();

        // 3. Historial en importacion_registros (Procesados en Excel)
        $atencionesExcel = ImportacionRegistro::with('importacion')
            ->where(function ($q) use ($dni, $dniLimpio, $expediente) {
                $conds = false;
                if (!empty($dni)) {
                    $q->where('numero_identidad', $dni);
                    $conds = true;
                    if (!empty($dniLimpio)) {
                        $q->orWhereRaw("REPLACE(REPLACE(numero_identidad, '-', ''), ' ', '') = ?", [$dniLimpio]);
                    }
                }
                if (!empty($expediente)) {
                    if ($conds) {
                        $q->orWhere('expediente', $expediente);
                    } else {
                        $q->where('expediente', $expediente);
                    }
                }
            })
            ->orderBy('fecha_atencion', 'desc')
            ->get();

        // Si no hay paciente en tabla pacientes, armar paciente con los datos de las atenciones
        if (!$paciente) {
            $primeraAtencion = $atencionesGlobales->first() ?: $atencionesExcel->first();
            if ($primeraAtencion) {
                $paciente = (object)[
                    'nombre_completo' => $primeraAtencion->nombre_paciente ?? ($primeraAtencion->nombre ?? 'Paciente sin nombre'),
                    'dni' => $dni ?: ($primeraAtencion->identidad ?? ($primeraAtencion->numero_identidad ?? 'S/DNI')),
                    'dni_limpio' => $dniLimpio,
                    'expediente' => $expediente ?: ($primeraAtencion->exp ?? ($primeraAtencion->expediente ?? 'S/N')),
                    'fecha_nacimiento' => $primeraAtencion->fecha_nacimiento ?? null,
                    'edad' => $primeraAtencion->edad ?? null,
                    'sexo' => $primeraAtencion->sexo ?? null,
                    'colonia' => $primeraAtencion->colonia ?? ($primeraAtencion->colonia_normalizada ?? ''),
                    'telefono' => $primeraAtencion->telefono ?? '',
                ];
            }
        }

        // Construir listado unificado de atenciones
        $timeline = [];

        foreach ($atencionesGlobales as $ag) {
            $dxs = [];
            for ($k = 1; $k <= 7; $k++) {
                $cod = $ag->{"cod_{$k}"} ?? null;
                $dx = $ag->{"diagnostico_{$k}"} ?? null;
                $cond = $ag->{"cond_{$k}"} ?? 'N';
                if (!empty($cod) || !empty($dx)) {
                    $dxs[] = [
                        'posicion' => $k,
                        'codigo' => $cod,
                        'diagnostico' => $dx,
                        'condicion' => $cond,
                    ];
                }
            }

            $timeline[] = [
                'origen' => 'BASE_DATOS_HISTORICA',
                'id' => $ag->id,
                'fecha' => $ag->fecha,
                'fecha_formato' => $ag->fecha ? date('d/m/Y', strtotime($ag->fecha)) : '-',
                'medico' => $ag->medico,
                'prof' => $ag->prof ?: 'MÉDICO GENERAL',
                'cm' => $ag->cm,
                'colonia' => $ag->colonia,
                'cod_col' => $ag->cod_col,
                'edad' => $ag->edad,
                'sexo' => $ag->sexo,
                'diagnosticos' => $dxs,
                'referido_a' => $ag->referido_a,
                'referido_de' => $ag->referido_de,
                'jornada' => $ag->jornada,
                'sm' => $ag->sm,
                'estado' => 'HISTORICO_OFICIAL',
            ];
        }

        foreach ($atencionesExcel as $ae) {
            // Evitar duplicar en timeline si ya está en globales
            if ($ae->registro_global_id && $atencionesGlobales->contains('id', $ae->registro_global_id)) {
                continue;
            }

            $timeline[] = [
                'origen' => 'IMPORTACION_EXCEL',
                'id' => $ae->id,
                'fecha' => $ae->fecha_atencion ? $ae->fecha_atencion->format('Y-m-d') : null,
                'fecha_formato' => $ae->fecha_atencion ? $ae->fecha_atencion->format('d/m/Y') : '-',
                'medico' => $ae->medico,
                'prof' => $ae->prof ?: 'MÉDICO GENERAL',
                'cm' => $ae->cm,
                'colonia' => $ae->colonia_normalizada,
                'cod_col' => $ae->cod_col,
                'edad' => $ae->edad,
                'sexo' => $ae->sexo,
                'diagnosticos' => is_array($ae->diagnosticos_json) ? $ae->diagnosticos_json : [],
                'archivo_excel' => $ae->importacion ? $ae->importacion->nombre_archivo : 'Lote #' . $ae->importacion_id,
                'estado' => $ae->estado,
                'motivo_estado' => $ae->motivo_estado,
            ];
        }

        // Ordenar cronológicamente descendente
        usort($timeline, function ($a, $b) {
            return strcmp($b['fecha'] ?? '', $a['fecha'] ?? '');
        });

        return response()->json([
            'success' => true,
            'paciente' => $paciente,
            'total_atenciones' => count($timeline),
            'timeline' => $timeline,
        ]);
    }

    /**
     * Ver un registro individual de importación
     */
    public function showRegistro(int $id): JsonResponse
    {
        $registro = ImportacionRegistro::with('importacion')->findOrFail($id);

        return response()->json([
            'success' => true,
            'registro' => $registro,
        ]);
    }

    /**
     * Actualizar / Editar un registro de importación
     */
    public function updateRegistro(Request $request, int $id): JsonResponse
    {
        $datos = $request->only([
            'colonia_normalizada',
            'cod_col',
            'colonia_id',
            'codigo',
            'diagnostico',
            'diagnostico_id',
            'nombre_paciente',
            'numero_identidad',
            'edad',
            'sexo',
            'medico',
            'fecha_atencion',
        ]);

        $registro = ImportacionRegistro::findOrFail($id);

        if ($request->filled('nombre_paciente')) $registro->nombre_paciente = $request->input('nombre_paciente');
        if ($request->filled('numero_identidad')) $registro->numero_identidad = $request->input('numero_identidad');
        if ($request->filled('edad')) $registro->edad = $request->input('edad');
        if ($request->filled('sexo')) $registro->sexo = $request->input('sexo');
        if ($request->filled('medico')) $registro->medico = $request->input('medico');
        if ($request->filled('fecha_atencion')) $registro->fecha_atencion = $request->input('fecha_atencion');
        $registro->save();

        $res = $this->importService->corregirRegistro($id, $datos);

        return response()->json($res);
    }

    /**
     * Eliminar un registro individual de importación
     */
    public function destroyRegistro(int $id): JsonResponse
    {
        try {
            $registro = ImportacionRegistro::findOrFail($id);
            $importacionId = $registro->importacion_id;
            $registro->delete();

            // Recalcular estadísticas del lote
            $stats = [
                'total_seleccionados' => ImportacionRegistro::where('importacion_id', $importacionId)->count(),
                'nuevos' => ImportacionRegistro::where('importacion_id', $importacionId)->where('estado', 'NUEVO')->count(),
                'ya_existentes' => ImportacionRegistro::where('importacion_id', $importacionId)->where('estado', 'YA_EXISTE')->count(),
                'pendientes' => ImportacionRegistro::where('importacion_id', $importacionId)->where('estado', 'PENDIENTE_REVISION')->count(),
                'errores' => ImportacionRegistro::where('importacion_id', $importacionId)->where('estado', 'ERROR')->count(),
            ];

            return response()->json([
                'success' => true,
                'message' => 'Registro eliminado exitosamente.',
                'stats' => $stats,
            ]);
        } catch (Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar registro: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Sincronizar todos los pacientes de Importaciones y Registros Globales a la tabla `pacientes` (Pacientes BD)
     */
    public function sincronizarPacientesBd(Request $request): JsonResponse
    {
        try {
            \App\Models\Paciente::ensureTableExists();

            $importados = ImportacionRegistro::all();
            $registrosGlobales = DB::table('registros_globales')->get();

            $nuevos = 0;
            $actualizados = 0;
            $pacientesMap = [];

            // 1. Procesar Registros Globales
            foreach ($registrosGlobales as $rg) {
                $dniOriginal = trim($rg->identidad ?? '');
                $dniLimpio = preg_replace('/\D/', '', $dniOriginal);
                $nombre = trim($rg->nombre_paciente ?? '');

                if (empty($nombre) && empty($dniLimpio)) {
                    continue;
                }

                $paciente = null;
                if (!empty($dniLimpio) && strlen($dniLimpio) >= 8) {
                    if (isset($pacientesMap[$dniLimpio])) {
                        $paciente = $pacientesMap[$dniLimpio];
                    } else {
                        $paciente = \App\Models\Paciente::where('dni_limpio', $dniLimpio)
                            ->orWhere('dni', $dniOriginal)
                            ->first();
                    }
                } elseif (!empty($nombre)) {
                    $paciente = \App\Models\Paciente::where('nombre_completo', $nombre)->first();
                }

                $colonia = $rg->colonia ?: null;
                $fecNac = $rg->fecha_nacimiento ?: null;
                $edad = is_numeric($rg->edad) ? (int)$rg->edad : null;
                $sexo = $rg->sexo ?: null;
                $exp = $rg->exp ?: null;
                $tel = $rg->telefono ?: null;

                if (!$paciente) {
                    $paciente = \App\Models\Paciente::create([
                        'nombre_completo' => $nombre ?: 'PACIENTE S/N',
                        'dni' => $dniOriginal ?: null,
                        'dni_limpio' => $dniLimpio ?: null,
                        'expediente' => $exp,
                        'fecha_nacimiento' => $fecNac,
                        'colonia' => $colonia,
                        'telefono' => $tel,
                        'sexo' => $sexo,
                        'edad' => $edad,
                        'departamento' => 'FRANCISCO MORAZAN',
                        'municipio' => 'DISTRITO CENTRAL',
                        'cod_municipio' => '0801',
                    ]);
                    $nuevos++;
                } else {
                    $mod = false;
                    if (empty($paciente->nombre_completo) || $paciente->nombre_completo === 'PACIENTE S/N') {
                        if (!empty($nombre)) { $paciente->nombre_completo = $nombre; $mod = true; }
                    }
                    if (empty($paciente->dni) && !empty($dniOriginal)) { $paciente->dni = $dniOriginal; $mod = true; }
                    if (empty($paciente->dni_limpio) && !empty($dniLimpio)) { $paciente->dni_limpio = $dniLimpio; $mod = true; }
                    if (empty($paciente->expediente) && !empty($exp)) { $paciente->expediente = $exp; $mod = true; }
                    if (empty($paciente->fecha_nacimiento) && !empty($fecNac)) { $paciente->fecha_nacimiento = $fecNac; $mod = true; }
                    if (empty($paciente->colonia) && !empty($colonia)) { $paciente->colonia = $colonia; $mod = true; }
                    if (empty($paciente->telefono) && !empty($tel)) { $paciente->telefono = $tel; $mod = true; }
                    if (empty($paciente->sexo) && !empty($sexo)) { $paciente->sexo = $sexo; $mod = true; }
                    if (empty($paciente->edad) && !empty($edad)) { $paciente->edad = $edad; $mod = true; }

                    if ($mod) {
                        $paciente->save();
                        $actualizados++;
                    }
                }

                if ($dniLimpio) {
                    $pacientesMap[$dniLimpio] = $paciente;
                }
            }

            // 2. Procesar Importación Registros
            foreach ($importados as $reg) {
                $dniOriginal = trim($reg->numero_identidad ?? '');
                $dniLimpio = preg_replace('/\D/', '', $dniOriginal);
                $nombre = trim($reg->nombre_paciente ?? '');

                if (empty($nombre) && empty($dniLimpio)) {
                    continue;
                }

                $paciente = null;
                if (!empty($dniLimpio) && strlen($dniLimpio) >= 8) {
                    if (isset($pacientesMap[$dniLimpio])) {
                        $paciente = $pacientesMap[$dniLimpio];
                    } else {
                        $paciente = \App\Models\Paciente::where('dni_limpio', $dniLimpio)
                            ->orWhere('dni', $dniOriginal)
                            ->first();
                    }
                } elseif (!empty($nombre)) {
                    $paciente = \App\Models\Paciente::where('nombre_completo', $nombre)->first();
                }

                $colonia = $reg->colonia_normalizada ?: ($reg->direccion_original ?: null);
                $fecNac = $reg->fecha_nacimiento ? $reg->fecha_nacimiento->format('Y-m-d') : null;
                $edad = is_numeric($reg->edad) ? (int)$reg->edad : null;
                $sexo = $reg->sexo ?: null;
                $exp = $reg->expediente ?: null;
                $tel = $reg->telefono ?: null;

                if (!$paciente) {
                    $paciente = \App\Models\Paciente::create([
                        'nombre_completo' => $nombre ?: 'PACIENTE S/N',
                        'dni' => $dniOriginal ?: null,
                        'dni_limpio' => $dniLimpio ?: null,
                        'expediente' => $exp,
                        'fecha_nacimiento' => $fecNac,
                        'colonia' => $colonia,
                        'telefono' => $tel,
                        'sexo' => $sexo,
                        'edad' => $edad,
                        'departamento' => 'FRANCISCO MORAZAN',
                        'municipio' => 'DISTRITO CENTRAL',
                        'cod_municipio' => '0801',
                    ]);
                    $nuevos++;
                } else {
                    $mod = false;
                    if ((empty($paciente->nombre_completo) || $paciente->nombre_completo === 'PACIENTE S/N') && !empty($nombre)) {
                        $paciente->nombre_completo = $nombre;
                        $mod = true;
                    }
                    if (empty($paciente->dni) && !empty($dniOriginal)) { $paciente->dni = $dniOriginal; $mod = true; }
                    if (empty($paciente->dni_limpio) && !empty($dniLimpio)) { $paciente->dni_limpio = $dniLimpio; $mod = true; }
                    if (empty($paciente->expediente) && !empty($exp)) { $paciente->expediente = $exp; $mod = true; }
                    if (empty($paciente->fecha_nacimiento) && !empty($fecNac)) { $paciente->fecha_nacimiento = $fecNac; $mod = true; }
                    if (empty($paciente->colonia) && !empty($colonia)) { $paciente->colonia = $colonia; $mod = true; }
                    if (empty($paciente->telefono) && !empty($tel)) { $paciente->telefono = $tel; $mod = true; }
                    if (empty($paciente->sexo) && !empty($sexo)) { $paciente->sexo = $sexo; $mod = true; }
                    if (empty($paciente->edad) && !empty($edad)) { $paciente->edad = $edad; $mod = true; }

                    if ($mod) {
                        $paciente->save();
                        $actualizados++;
                    }
                }

                if ($dniLimpio) {
                    $pacientesMap[$dniLimpio] = $paciente;
                }

                if ($paciente && $reg->paciente_id !== $paciente->id) {
                    $reg->paciente_id = $paciente->id;
                    $reg->save();
                }
            }

            $totalPacientes = \App\Models\Paciente::count();

            return response()->json([
                'success' => true,
                'message' => "Sincronización exitosa: {$nuevos} pacientes nuevos incorporados a Pacientes BD, {$actualizados} actualizados.",
                'nuevos' => $nuevos,
                'actualizados' => $actualizados,
                'total_pacientes' => $totalPacientes,
            ]);

        } catch (\Throwable $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al sincronizar pacientes: ' . $e->getMessage(),
            ], 500);
        }
    }
}
