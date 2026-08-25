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
     * Mostrar lista de registros con paginación
     */
    public function index(Request $request)
    {
        $anoActual = $request->input('ano', date('Y'));
        $ultimoMes = RegistroGlobal::where('ano', $anoActual)->max('mes') ?? date('n');
        $mesSeleccionado = $request->input('mes', $ultimoMes);
        
        $cacheKey = "registros.{$anoActual}.{$mesSeleccionado}";
        
        if ($request->ajax()) {
            $registros = Cache::remember($cacheKey, 1800, function () use ($anoActual, $mesSeleccionado) {
                return RegistroGlobal::where('ano', $anoActual)
                    ->where('mes', $mesSeleccionado)
                    ->orderBy('fecha', 'desc')
                    ->get()
                    ->map(function($registro) {
                        if ($registro->fecha) {
                            try {
                                $registro->fecha = Carbon::parse($registro->fecha)->format('d-m-Y');
                            } catch (\Exception $e) {}
                        }
                        return $registro;
                    });
            });
            
            return response()->json([
                'success' => true,
                'data' => $registros,
                'total' => $registros->count()
            ]);
        }
        
        $registros = Cache::remember($cacheKey, 1800, function () use ($anoActual, $mesSeleccionado) {
            return RegistroGlobal::where('ano', $anoActual)
                ->where('mes', $mesSeleccionado)
                ->orderBy('fecha', 'desc')
                ->get()
                ->map(function($registro) {
                    if ($registro->fecha) {
                        try {
                            $registro->fecha = Carbon::parse($registro->fecha)->format('d-m-Y');
                        } catch (\Exception $e) {}
                    }
                    return $registro;
                });
        });
        
        $anos = RegistroGlobal::select('ano')->distinct()->whereNotNull('ano')->orderBy('ano', 'desc')->pluck('ano');
        $mesesDisponibles = RegistroGlobal::where('ano', $anoActual)->select('mes')->distinct()->whereNotNull('mes')->orderBy('mes')->pluck('mes');
        $stats = ['total' => $registros->count()];
        
        return view('registros.index', compact('registros', 'stats', 'anos', 'anoActual', 'mesSeleccionado', 'mesesDisponibles'));
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
     * Eliminar un registro
     */
    public function destroy($id)
    {
        try {
            RegistroGlobal::findOrFail($id)->delete();
            
            // Limpiar caché
            Cache::flush();
            
            return redirect()->route('registros.index')
                ->with('success', 'Registro eliminado exitosamente.');
        } catch (\Exception $e) {
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
}
