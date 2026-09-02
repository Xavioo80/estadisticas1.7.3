<?php

namespace App\Http\Controllers;

use App\Models\RegistroGlobal;
use App\Imports\RegistrosGlobalesImport;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class RegistroGlobalController extends Controller
{
    /**
     * Mostrar lista de registros con filtros dinámicos y tabla Excel
     */
    public function index(Request $request)
    {
        // Obtener todos los años disponibles en la BD (excluyendo 0 y nulos)
        $anos = Cache::remember('registros.anos', 3600, function() {
            return RegistroGlobal::select('ano')
                ->distinct()
                ->whereNotNull('ano')
                ->where('ano', '!=', 0)
                ->orderBy('ano', 'desc')
                ->pluck('ano')
                ->map(fn($val) => (string)$val)
                ->values()
                ->toArray();
        });

        if (empty($anos)) {
            $anos = [(string)date('Y')];
        }

        // Determinar años seleccionados (array o single)
        $selectedYears = $request->input('years', []);
        if (empty($selectedYears) && $request->has('ano')) {
            $selectedYears = [(string)$request->input('ano')];
        }
        if (empty($selectedYears)) {
            $selectedYears = [$anos[0]]; // Por defecto el año más reciente
        }

        // Obtener meses disponibles para los años seleccionados
        $mesesDisponibles = RegistroGlobal::whereIn('ano', $selectedYears)
            ->select('mes')
            ->distinct()
            ->whereNotNull('mes')
            ->where('mes', '!=', '')
            ->pluck('mes')
            ->toArray();

        // Orden de meses estándar en español para ordenar los tabs/opciones
        $ordenMeses = ['ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'];
        usort($mesesDisponibles, function($a, $b) use ($ordenMeses) {
            $idxA = array_search(strtoupper($a), $ordenMeses);
            $idxB = array_search(strtoupper($b), $ordenMeses);
            $idxA = $idxA === false ? 99 : $idxA;
            $idxB = $idxB === false ? 99 : $idxB;
            return $idxA - $idxB;
        });

        // Determinar meses seleccionados (array o single)
        $selectedMonths = $request->input('months', []);
        if (empty($selectedMonths) && $request->has('mes')) {
            $selectedMonths = [(string)$request->input('mes')];
        }
        if (empty($selectedMonths) && !empty($mesesDisponibles)) {
            // Por defecto el último mes disponible con datos (ej. AGOSTO)
            $ultimoMes = end($mesesDisponibles);
            $selectedMonths = [$ultimoMes];
        }

        $query = RegistroGlobal::query();

        if (!empty($selectedYears)) {
            $query->whereIn('ano', $selectedYears);
        }
        if (!empty($selectedMonths)) {
            $query->whereIn('mes', $selectedMonths);
        }

        // Ordenar fecha ascendente y número de menor a mayor (1, 2, 3...)
        $registros = $query->orderBy('fecha', 'asc')->orderBy('numero', 'asc')->orderBy('id', 'asc')->limit(10000)->get();

        $stats = [
            'total' => $registros->count(),
            'total_bd' => RegistroGlobal::count()
        ];

        $anoActual = implode(',', $selectedYears);
        $mesSeleccionado = implode(',', $selectedMonths);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $registros,
                'total' => $registros->count()
            ]);
        }

        return view('reports.registrosat1', compact(
            'registros',
            'stats',
            'anos',
            'selectedYears',
            'mesesDisponibles',
            'selectedMonths',
            'anoActual',
            'mesSeleccionado'
        ));
    }

    /**
     * Mostrar vista Informes AT1 segmentada individualmente por Diagnóstico
     */
    public function informesAt1(Request $request)
    {
        // 1. Obtener todos los años disponibles en la BD
        $anos = Cache::remember('registros.anos', 3600, function() {
            return RegistroGlobal::select('ano')
                ->distinct()
                ->whereNotNull('ano')
                ->where('ano', '!=', 0)
                ->orderBy('ano', 'desc')
                ->pluck('ano')
                ->map(fn($val) => (string)$val)
                ->values()
                ->toArray();
        });

        if (empty($anos)) {
            $anos = [(string)date('Y')];
        }

        $selectedYears = $request->input('years', []);
        if (empty($selectedYears) && $request->has('ano')) {
            $selectedYears = [(string)$request->input('ano')];
        }
        if (empty($selectedYears)) {
            $selectedYears = [$anos[0]];
        }

        // 2. Obtener meses disponibles
        $mesesDisponibles = RegistroGlobal::whereIn('ano', $selectedYears)
            ->select('mes')
            ->distinct()
            ->whereNotNull('mes')
            ->where('mes', '!=', '')
            ->pluck('mes')
            ->toArray();

        $ordenMeses = ['ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'];
        usort($mesesDisponibles, function($a, $b) use ($ordenMeses) {
            $idxA = array_search(strtoupper($a), $ordenMeses);
            $idxB = array_search(strtoupper($b), $ordenMeses);
            return ($idxA === false ? 99 : $idxA) - ($idxB === false ? 99 : $idxB);
        });

        $selectedMonths = $request->input('months', []);
        if (empty($selectedMonths) && $request->has('mes')) {
            $selectedMonths = [(string)$request->input('mes')];
        }
        if (empty($selectedMonths) && !empty($mesesDisponibles)) {
            $ultimoMes = end($mesesDisponibles);
            $selectedMonths = [$ultimoMes];
        }

        $query = RegistroGlobal::query();
        if (!empty($selectedYears)) {
            $query->whereIn('ano', $selectedYears);
        }
        if (!empty($selectedMonths)) {
            $query->whereIn('mes', $selectedMonths);
        }

        // Ordenar fecha ascendente y número de menor a mayor (1, 2, 3...)
        $registrosRaw = $query->orderBy('fecha', 'asc')->orderBy('numero', 'asc')->orderBy('id', 'asc')->limit(10000)->get();

        // 3. Segmentación / Unpivot de Registros por Diagnóstico (1 a 7)
        $segmentedRows = [];
        $totalDiagnosticos = 0;

        foreach ($registrosRaw as $r) {
            $diags = [
                1 => ['cod' => $r->cod_1, 'diag' => $r->diagnostico_1, 'cond' => $r->cond_1 ?: $r->cond],
                2 => ['cod' => $r->cod_2, 'diag' => $r->diagnostico_2, 'cond' => $r->cond_2],
                3 => ['cod' => $r->cod_3, 'diag' => $r->diagnostico_3, 'cond' => $r->cond_3],
                4 => ['cod' => $r->cod_4, 'diag' => $r->diagnostico_4, 'cond' => $r->cond_4],
                5 => ['cod' => $r->cod_5, 'diag' => $r->diagnostico_5, 'cond' => $r->cond_5],
                6 => ['cod' => $r->cod_6, 'diag' => $r->diagnostico_6, 'cond' => $r->cond_6],
                7 => ['cod' => $r->cod_7, 'diag' => $r->diagnostico_7, 'cond' => $r->cond_7],
            ];

            $emittedCount = 0;
            foreach ($diags as $diagNum => $d) {
                $diagText = trim($d['diag'] ?? '');
                $codText = trim($d['cod'] ?? '');

                if ($diagText !== '' || $codText !== '') {
                    $segmentedRows[] = [
                        $r->numero ?? '',
                        $r->cm ?? '',
                        $r->medico ?? '',
                        $r->prof ?? '',
                        $r->fecha ? Carbon::parse($r->fecha)->format('Y-m-d') : '',
                        $r->se ?? '',
                        $r->exp ?? '',
                        $r->nombre_paciente ?? '',
                        $r->identidad ?? '',
                        $r->telefono ?? '',
                        $r->fecha_nacimiento ? Carbon::parse($r->fecha_nacimiento)->format('Y-m-d') : '',
                        $r->etnia ?? '',
                        $r->sexo ?? '',
                        $r->edad ?? '',
                        $r->tipo ?? '',
                        $r->rango ?? '',
                        $r->colonia ?? '',
                        'D' . $diagNum,               // N° Diagnóstico (D1, D2, D3...)
                        $codText,                     // Código CIE-10
                        $diagText,                    // Diagnóstico
                        $d['cond'] ?? '',             // Condición
                        $r->sg ?? '',                 // Semanas Gestacionales
                        $r->referido_a ?? '',         // Referencia Enviada
                        $r->referido_de ?? '',        // Referencia Recibida
                        $r->pg_emb ?? '',             // Población General / Embarazada
                        $r->jornada ?? '',            // Jornada
                        $r->ano ?? '',                // Año
                        $r->mes ?? '',                // Mes
                        $r->id ?? ''                  // ID BD
                    ];
                    $emittedCount++;
                    $totalDiagnosticos++;
                }
            }

            // Si el registro no tenía diagnósticos explícitos, emitir al menos una fila con los datos de consulta
            if ($emittedCount === 0) {
                $segmentedRows[] = [
                    $r->numero ?? '',
                    $r->cm ?? '',
                    $r->medico ?? '',
                    $r->prof ?? '',
                    $r->fecha ? Carbon::parse($r->fecha)->format('Y-m-d') : '',
                    $r->se ?? '',
                    $r->exp ?? '',
                    $r->nombre_paciente ?? '',
                    $r->identidad ?? '',
                    $r->telefono ?? '',
                    $r->fecha_nacimiento ? Carbon::parse($r->fecha_nacimiento)->format('Y-m-d') : '',
                    $r->etnia ?? '',
                    $r->sexo ?? '',
                    $r->edad ?? '',
                    $r->tipo ?? '',
                    $r->rango ?? '',
                    $r->colonia ?? '',
                    'D1',
                    '',
                    '(Sin Diagnóstico)',
                    $r->cond ?? '',
                    $r->sg ?? '',
                    $r->referido_a ?? '',
                    $r->referido_de ?? '',
                    $r->pg_emb ?? '',
                    $r->jornada ?? '',
                    $r->ano ?? '',
                    $r->mes ?? '',
                    $r->id ?? ''
                ];
                $totalDiagnosticos++;
            }
        }

        $stats = [
            'total_consultas' => $registrosRaw->count(),
            'total_diagnosticos' => $totalDiagnosticos,
            'total_bd' => RegistroGlobal::count()
        ];

        $anoActual = implode(',', $selectedYears);
        $mesSeleccionado = implode(',', $selectedMonths);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'data' => $segmentedRows,
                'total' => count($segmentedRows)
            ]);
        }

        return view('reports.informesat1', compact(
            'segmentedRows',
            'stats',
            'anos',
            'selectedYears',
            'mesesDisponibles',
            'selectedMonths',
            'anoActual',
            'mesSeleccionado'
        ));
    }
    
    /**
     * Obtener nombre del mes
     */
    private function getMesNombre($mes)
    {
        $meses = [
            1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril',
            5 => 'Mayo', 6 => 'Junio', 7 => 'Julio', 8 => 'Agosto',
            9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
        ];
        return $meses[$mes] ?? '';
    }
    
    /**
     * Obtener valores únicos de una columna (para filtros)
     */
    public function getColumnValues(Request $request)
    {
        $column = $request->input('column');
        $ano = $request->input('ano', date('Y'));
        $mes = $request->input('mes');
        
        $query = RegistroGlobal::where('ano', $ano)
            ->where('mes', $mes)
            ->select($column)
            ->distinct()
            ->whereNotNull($column)
            ->where($column, '!=', '')
            ->where($column, '!=', '0')
            ->orderBy($column);
        
        // Para fechas, agrupar por mes (las fechas ya están en formato Y-m-d)
        if ($column === 'fecha') {
            $fechas = RegistroGlobal::where('ano', $ano)
                ->where('mes', $mes)
                ->select('fecha')
                ->whereNotNull('fecha')
                ->where('fecha', '!=', '')
                ->orderBy('fecha')
                ->get()
                ->groupBy(function($item) {
                    // Agrupamos por Y-m directamente del string Y-m-d
                    return substr($item->fecha, 0, 7);
                });
            
            $groupedDates = [];
            foreach ($fechas as $yearMonth => $items) {
                $groupedDates[$yearMonth] = $items->pluck('fecha')->unique()->values()->toArray();
            }
            
            return response()->json([
                'success' => true,
                'values' => [],
                'grouped' => $groupedDates,
                'isDate' => true
            ]);
        }
        
        $values = $query->pluck($column)->toArray();
        
        return response()->json([
            'success' => true,
            'values' => $values,
            'isDate' => false
        ]);
    }
    
    /**
     * Mostrar formulario de carga de Excel
     */
    public function upload()
    {
        $totalRegistros = RegistroGlobal::count() ?? 0;
        return view('registros.upload', compact('totalRegistros'));
    }
    
    /**
     * Mostrar un registro individual
     */
    public function show($id)
    {
        $registro = RegistroGlobal::findOrFail($id);
        return view('registros.show', compact('registro'));
    }
    
    /**
     * Procesar y cargar archivo Excel
     */
    public function import(Request $request)
    {
        // Aumentar límites de tiempo y memoria para archivos grandes
        set_time_limit(3600); // 1 hora
        ini_set('memory_limit', '512M');
        
        $request->validate([
            'archivo' => 'required|mimes:xlsx,xls,csv,txt|max:204800', // Máximo 200MB - acepta Excel y CSV
        ]);
        
        $archivo = $request->file('archivo');
        $startTime = microtime(true);
        
        try {
            // Configurar MySQL para archivos grandes
            try {
                DB::statement('SET SESSION wait_timeout = 28800'); // 8 horas
                DB::statement('SET SESSION interactive_timeout = 28800');
                DB::statement('SET SESSION net_read_timeout = 600');
                DB::statement('SET SESSION net_write_timeout = 600');
                // max_allowed_packet debe configurarse globalmente en my.ini
            } catch (\Exception $configException) {
                Log::warning('No se pudieron configurar algunos parámetros de MySQL: ' . $configException->getMessage());
            }
            
            // Log inicial detallado
            Log::info("=".str_repeat("=", 60));
            Log::info("INICIO DE IMPORTACIÓN DE DATOS");
            Log::info("Archivo: " . $archivo->getClientOriginalName());
            Log::info("Tamaño: " . round($archivo->getSize() / (1024 * 1024), 2) . " MB");
            Log::info("Tipo: " . $archivo->getClientOriginalExtension());
            Log::info("Usuario: " . ($request->user()->name ?? 'Anónimo'));
            Log::info("Fecha/Hora: " . now()->format('Y-m-d H:i:s'));
            Log::info("=".str_repeat("=", 60));
            
            // Detectar tipo de archivo y procesar
            $extension = strtolower($archivo->getClientOriginalExtension());
            
            // Determinar el tipo de lectura según la extensión
            $readerType = null;
            if (in_array($extension, ['csv', 'txt'])) {
                $readerType = \Maatwebsite\Excel\Excel::CSV;
            } elseif ($extension === 'xls') {
                $readerType = \Maatwebsite\Excel\Excel::XLS;
            } else {
                $readerType = \Maatwebsite\Excel\Excel::XLSX;
            }
            
            // Crear instancia del importador
            $importer = new RegistrosGlobalesImport();
            
            // Procesar archivo (Excel o CSV) con carga rápida
            Excel::import($importer, $archivo, null, $readerType);
            
            // Obtener estadísticas de la importación
            $stats = $importer->getStats();
            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);
            
            $totalRegistros = RegistroGlobal::count();
            
            // Log final con estadísticas completas
            Log::info("=".str_repeat("=", 60));
            Log::info("IMPORTACIÓN FINALIZADA");
            Log::info("✓ Registros procesados: " . number_format($stats['total_processed']));
            Log::info("✓ Registros insertados: " . number_format($stats['total_inserted']));
            Log::info("✗ Registros fallidos: " . number_format($stats['total_failed']));
            Log::info("✓ Tasa de éxito: " . $stats['success_rate'] . "%");
            Log::info("Errores encontrados: " . $stats['errors_count']);
            Log::info("Advertencias: " . $stats['warnings_count']);
            Log::info("Duración: {$duration} segundos");
            Log::info("Total en BD: " . number_format($totalRegistros));
            Log::info("=".str_repeat("=", 60));
            
            // Preparar mensaje de respuesta
            $successMessage = "Archivo cargado exitosamente.\n";
            $successMessage .= "Procesados: " . number_format($stats['total_processed']) . " | ";
            $successMessage .= "Insertados: " . number_format($stats['total_inserted']) . " | ";
            $successMessage .= "Fallidos: " . number_format($stats['total_failed']) . "\n";
            $successMessage .= "Tasa de éxito: " . $stats['success_rate'] . "%\n";
            $successMessage .= "Total de registros en BD: " . number_format($totalRegistros) . "\n";
            $successMessage .= "Tiempo de procesamiento: {$duration} segundos";
            
            // Agregar información de errores si existen
            if ($stats['errors_count'] > 0) {
                $successMessage .= "\n\nSe encontraron " . $stats['errors_count'] . " errores. Revisa los logs para más detalles.";
            }
            
            // Agregar advertencias si existen
            if ($stats['warnings_count'] > 0) {
                $successMessage .= "\n" . $stats['warnings_count'] . " advertencias registradas.";
            }
            
            return redirect()->route('registros.index')
                ->with('success', $successMessage)
                ->with('import_stats', $stats);
        } catch (\Exception $e) {
            $endTime = microtime(true);
            $duration = round($endTime - $startTime, 2);
            
            // Reconectar si se perdió la conexión
            try {
                DB::reconnect();
            } catch (\Exception $reconnectException) {
                // Ignorar error de reconexión
            }
            
            // Log detallado del error
            Log::error("=".str_repeat("=", 60));
            Log::error("ERROR EN IMPORTACIÓN");
            Log::error('Error: ' . $e->getMessage());
            Log::error('Archivo: ' . ($archivo->getClientOriginalName() ?? 'desconocido'));
            Log::error('Tamaño: ' . round(($archivo->getSize() ?? 0) / (1024 * 1024), 2) . ' MB');
            Log::error('Tipo: ' . ($archivo->getClientOriginalExtension() ?? 'desconocido'));
            Log::error('Línea: ' . $e->getLine());
            Log::error('Archivo: ' . $e->getFile());
            Log::error('Duración antes del error: ' . $duration . ' segundos');
            Log::error('Stack trace: ' . $e->getTraceAsString());
            Log::error("=".str_repeat("=", 60));
            
            $errorMessage = 'Error al cargar el archivo: ' . $e->getMessage();
            
            if (strpos($e->getMessage(), 'gone away') !== false) {
                $errorMessage .= ' (La conexión a MySQL se perdió. Intenta con un archivo más pequeño o verifica la configuración de MySQL)';
            }
            
            return redirect()->route('registros.upload')
                ->with('error', $errorMessage);
        }
    }
    
    /**
     * Eliminar un registro individual
     */
    public function destroy(Request $request, $id)
    {
        try {
            $registro = RegistroGlobal::findOrFail($id);
            $registro->delete(); // Dispara Observer: elimina informes hijos y limpia caché
            
            if ($request->wantsJson() || $request->ajax() || $request->isJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Registro eliminado exitosamente.'
                ]);
            }
            
            return redirect()->route('registros.index')
                ->with('success', 'Registro eliminado exitosamente.');
        } catch (\Exception $e) {
            if ($request->wantsJson() || $request->ajax() || $request->isJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar el registro: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->route('registros.index')
                ->with('error', 'Error al eliminar el registro: ' . $e->getMessage());
        }
    }

    /**
     * Eliminar múltiples registros (borrado masivo vía AJAX)
     */
    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);

        if (!is_array($ids) || empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No se recibieron registros para eliminar.',
            ], 422);
        }

        try {
            RegistroGlobal::whereIn('id', $ids)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Registros eliminados correctamente.',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar los registros: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Actualizar un registro específico
     */
    public function update(Request $request, $id)
    {
        try {
            $registro = RegistroGlobal::findOrFail($id);
            
            $data = $request->only([
                'fecha', 'medico', 'prof', 'se', 'exp', 'sexo', 'edad', 'tipo', 
                'rango', 'rango_2', 'rango_3', 'rango_4', 'rango_5', 'cond',
                'cod_col', 'colonia', 'paciente',
                'cod_1', 'diagnostico_1', 'cond_1', 'sg',
                'cod_2', 'diagnostico_2', 'cond_2', 
                'cod_3', 'diagnostico_3', 'cond_3',
                'cod_4', 'diagnostico_4', 'cond_4', 
                'cod_5', 'diagnostico_5', 'cond_5',
                'cod_6', 'diagnostico_6', 'cond_6', 
                'cod_7', 'diagnostico_7', 'cond_7',
                'referido_a', 'referido_de', 'pg_emb', 'jornada', 'sm', 'sg2'
            ]);
            
            foreach ($data as $key => $value) {
                if ($value === '' || $value === 'null') {
                    $data[$key] = null;
                } elseif ($key === 'fecha' && $value) {
                    try {
                        if (strpos($value, '/') !== false) {
                            $data[$key] = Carbon::createFromFormat('d/m/Y', $value)->format('Y-m-d');
                        } else {
                            $data[$key] = Carbon::parse($value)->format('Y-m-d');
                        }
                    } catch (\Exception $e) {
                        // Mantener valor original o error
                    }
                }
            }
            
            $updated = $registro->update($data);
            
            // Limpiar caché
            Cache::forget("registros.{$registro->ano}.{$registro->mes}");
            
            return response()->json([
                'success' => true,
                'message' => 'Registro actualizado correctamente.',
                'data' => $registro->fresh()
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el registro: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Búsqueda en catálogo de diagnósticos para autocompletado en Informes AT1
     */
    public function buscarDiagnosticos(Request $request)
    {
        $q = trim($request->input('q', ''));
        if (strlen($q) < 1) {
            $diags = \App\Models\Diagnostico::select('codigo', 'patologia')
                ->whereNotNull('patologia')
                ->where('patologia', '!=', '')
                ->orderBy('codigo')
                ->get();
        } else {
            $diags = \App\Models\Diagnostico::select('codigo', 'patologia')
                ->where('patologia', 'LIKE', "%{$q}%")
                ->orWhere('codigo', 'LIKE', "%{$q}%")
                ->orderBy('codigo')
                ->limit(50)
                ->get();
        }
        return response()->json($diags);
    }

    /**
     * Actualizar Diagnóstico individual desde Informes AT1 (Sincroniza Registros Globales e Informes)
     */
    public function updateAt1Diagnostico(Request $request)
    {
        try {
            $request->validate([
                'registro_id' => 'required|integer',
                'diag_index' => 'required|integer|min:1|max:7',
                'diagnostico' => 'nullable|string',
                'cod' => 'nullable|string',
                'cond' => 'nullable|string',
            ]);

            $registro = RegistroGlobal::findOrFail($request->registro_id);
            $idx = (int)$request->diag_index;
            $cod = $request->cod ? trim($request->cod) : null;
            $diag = $request->diagnostico ? trim($request->diagnostico) : null;
            $cond = $request->cond ? strtoupper(trim($request->cond)) : null;

            $registro->{"cod_{$idx}"} = $cod;
            $registro->{"diagnostico_{$idx}"} = $diag;
            $registro->{"cond_{$idx}"} = $cond;

            if ($idx === 1 && !empty($cond)) {
                $registro->cond = $cond;
            }

            $registro->save(); // El observer sincroniza la tabla 'informes' y limpia caché

            return response()->json([
                'success' => true,
                'message' => 'Diagnóstico actualizado correctamente en Registros Globales e Informes AT1.',
                'registro_id' => $registro->id,
                'diag_index' => $idx,
                'cod' => $cod ?: '',
                'diagnostico' => $diag ?: '',
                'cond' => $cond ?: ''
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el diagnóstico: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Eliminar Diagnóstico individual desde Informes AT1
     */
    public function deleteAt1Diagnostico(Request $request)
    {
        try {
            $request->validate([
                'registro_id' => 'required|integer',
                'diag_index' => 'required|integer|min:1|max:7'
            ]);

            $registro = RegistroGlobal::findOrFail($request->registro_id);
            $idx = (int)$request->diag_index;

            // Contar diagnósticos activos en este registro
            $activeCount = 0;
            for ($i = 1; $i <= 7; $i++) {
                $d = trim((string)($registro->{"diagnostico_{$i}"} ?? ''));
                $c = trim((string)($registro->{"cod_{$i}"} ?? ''));
                if ($d !== '' || $c !== '') {
                    $activeCount++;
                }
            }

            if ($request->boolean('check_only')) {
                return response()->json([
                    'success' => true,
                    'total_diagnosticos' => $activeCount,
                    'is_only_diagnosis' => ($activeCount <= 1),
                    'paciente' => $registro->nombre_paciente,
                    'exp' => $registro->exp,
                    'medico' => $registro->medico,
                    'fecha' => $registro->fecha,
                    'diagnostico' => $registro->{"diagnostico_{$idx}"},
                    'cod' => $registro->{"cod_{$idx}"}
                ]);
            }

            if ($request->boolean('eliminar_registro_completo')) {
                $registro->delete(); // El observer elimina todas las filas hijas en informes
                return response()->json([
                    'success' => true,
                    'action' => 'deleted_record',
                    'message' => 'Se ha eliminado el registro completo del paciente con éxito.'
                ]);
            }

            // Eliminar solo este diagnóstico específico
            $registro->{"cod_{$idx}"} = null;
            $registro->{"diagnostico_{$idx}"} = null;
            $registro->{"cond_{$idx}"} = null;

            // Si se eliminó el diagnóstico 1 y no quedan más diagnósticos, limpiar cond principal
            if ($idx === 1 && $activeCount <= 1) {
                $registro->cond = null;
            }

            $registro->save(); // El observer sincroniza la tabla 'informes' y limpia caché

            return response()->json([
                'success' => true,
                'action' => 'deleted_diagnosis',
                'message' => 'Se ha eliminado el diagnóstico correctamente (el registro del paciente permanece guardado).'
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener un registro global específico por ID para visualización y edición
     */
    public function getRegistro($id)
    {
        try {
            $registro = RegistroGlobal::findOrFail($id);
            return response()->json([
                'success' => true,
                'data' => $registro
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Registro no encontrado: ' . $e->getMessage()
            ], 404);
        }
    }

    /**
     * Actualización Integral de RegistroGlobal y Paciente desde Registros AT1
     */
    public function updateFull(Request $request)
    {
        try {
            $request->validate([
                'id' => 'required|integer|exists:registros_globales,id',
            ]);

            $registro = RegistroGlobal::findOrFail($request->id);

            $data = $request->except(['_token', 'id']);

            // Parsear y normalizar fecha
            if (!empty($data['fecha'])) {
                try {
                    $dt = Carbon::parse($data['fecha']);
                    $data['fecha'] = $dt->format('Y-m-d');
                    $data['ano'] = (int)$dt->format('Y');
                    
                    $mesesEs = [
                        1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL',
                        5 => 'MAYO', 6 => 'JUNIO', 7 => 'JULIO', 8 => 'AGOSTO',
                        9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE'
                    ];
                    $data['mes'] = $mesesEs[(int)$dt->format('n')] ?? strtoupper($dt->format('F'));
                } catch (\Exception $e) {
                    // Mantener fecha original si falla el parseo
                }
            }

            // Normalizar y calcular rangos epidemiológicos de edad
            $edad = isset($data['edad']) ? (int)$data['edad'] : $registro->edad;
            $tipo = isset($data['tipo']) ? strtoupper(trim($data['tipo'])) : $registro->tipo;

            if ($edad !== null && !empty($tipo)) {
                $rangos = \App\Services\ExcelImportService::calcularRangosEpidemiologicos($edad, $tipo);
                $data['rango'] = $rangos['rango'] ?? $registro->rango;
                $data['rango_2'] = $rangos['rango_2'] ?? $registro->rango_2;
                $data['rango_3'] = $rangos['rango_3'] ?? $registro->rango_3;
                $data['rango_4'] = $rangos['rango_4'] ?? $registro->rango_4;
                $data['rango_5'] = $rangos['rango_5'] ?? $registro->rango_5;
            }

            // Normalizar condición principal con cond_1
            if (!empty($data['cond_1'])) {
                $data['cond'] = strtoupper(trim($data['cond_1']));
            } elseif (!empty($data['cond'])) {
                $data['cond_1'] = strtoupper(trim($data['cond']));
            }

            // Asignar atributos al modelo
            $registro->fill($data);
            $registro->save(); // El observer sincroniza la tabla 'informes' y limpia caché automáticamente

            return response()->json([
                'success' => true,
                'message' => 'Registro y paciente actualizados exitosamente. Los cambios se han propagado a todos los informes.',
                'data' => $registro->fresh()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar el registro: ' . $e->getMessage()
            ], 500);
        }
    }


    /**
     * Eliminar múltiples registros seleccionados en lote
     */
    public function deleteMultiple(Request $request)
    {
        try {
            $ids = $request->input('ids', []);
            if (!is_array($ids) || empty($ids)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se proporcionaron registros para eliminar.'
                ], 400);
            }

            /** @var \Illuminate\Database\Eloquent\Collection<int, \App\Models\RegistroGlobal> $registros */
            $registros = RegistroGlobal::whereIn('id', $ids)->get();
            $count = $registros->count();

            foreach ($registros as $rg) {
                /** @var \App\Models\RegistroGlobal $rg */
                $rg->delete(); // Dispara Observer individual para cada registro
            }

            return response()->json([
                'success' => true,
                'count' => $count,
                'message' => "Se eliminaron {$count} registros exitosamente."
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar los registros: ' . $e->getMessage()
            ], 500);
        }
    }
}

