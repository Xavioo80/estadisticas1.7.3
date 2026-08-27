<?php

namespace App\Http\Controllers;

use App\Models\Diagnostico;
use App\Models\CondicionamientoDiagnostico;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DiagnosticoController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');
        $categoriaFiltrar = $request->input('categoria');

        $query = Diagnostico::query();

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('patologia', 'LIKE', "%{$search}%")
                    ->orWhere('codigo', 'LIKE', "%{$search}%")
                    ->orWhere('auxiliar', 'LIKE', "%{$search}%");
            });
        }

        if ($categoriaFiltrar && $categoriaFiltrar != 'TODAS') {
            $query->where('categoria', $categoriaFiltrar);
        }

        // Si es una petición AJAX para la búsqueda en tiempo real
        if ($request->ajax() || $request->wantsJson()) {
            $total = $query->count();
            $diagnosticos = $query->orderBy('codigo')->take(100)->get();

            return response()->json([
                'diagnosticos' => $diagnosticos,
                'total' => $total
            ]);
        }

        $diagnosticos = $query->orderBy('codigo')->take(100)->get();
        $categorias = Diagnostico::select('categoria')->distinct()->orderBy('categoria')->pluck('categoria');

        return view('diagnosticos.index', compact('diagnosticos', 'categorias', 'search', 'categoriaFiltrar'));
    }

    public function create()
    {
        return view('diagnosticos.create');
    }

    public function store(Request $request)
    {
        try {
            \Log::info('DiagnosticoController::store - Información de petición:', [
                'is_ajax' => $request->ajax(),
                'wants_json' => $request->wantsJson(),
                'content_type' => $request->header('Content-Type'),
                'accept' => $request->header('Accept'),
                'x_requested_with' => $request->header('X-Requested-With'),
                'method' => $request->method(),
                'url' => $request->url()
            ]);

            $validated = $request->validate([
                'codigo' => 'required|string|max:20|unique:diagnosticos,codigo',
                'patologia' => 'required|string|max:255',
                'auxiliar' => 'nullable|string|max:50',
                'categoria' => 'required|string|max:50',
                'secundario' => 'nullable|string|max:100',
                'tipo' => 'nullable|string|max:50',
                'observaciones' => 'nullable|string|max:255'
            ]);

            $diagnostico = Diagnostico::create($validated);

            \Log::info('DiagnosticoController::store - Decidiendo tipo de respuesta:', [
                'is_ajax' => $request->ajax(),
                'wants_json' => $request->wantsJson(),
                'content_type' => $request->header('Content-Type'),
                'accept' => $request->header('Accept'),
                'x_requested_with' => $request->header('X-Requested-With')
            ]);

            if ($request->ajax() || $request->wantsJson()) {
                \Log::info('Devolviendo respuesta JSON');
                return response()->json([
                    'success' => true,
                    'message' => 'Diagnóstico agregado correctamente',
                    'diagnostico' => $diagnostico
                ]);
            }

            \Log::info('Devolviendo redirección al index');
            return redirect()->route('diagnosticos.index')->with('success', 'Diagnóstico agregado correctamente');

        }
        catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación: ' . implode(', ', $e->validator->errors()->all()),
                    'errors' => $e->validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($e->validator)->withInput();
        }
        catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al guardar el diagnóstico: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Error al guardar el diagnóstico: ' . $e->getMessage())->withInput();
        }
    }

    public function show(Diagnostico $diagnostico)
    {
        // Retornar JSON si es una petición AJAX
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json($diagnostico);
        }

        return view('diagnosticos.show', compact('diagnostico'));
    }

    public function edit(Diagnostico $diagnostico)
    {
        return view('diagnosticos.edit', compact('diagnostico'));
    }

    public function update(Request $request, Diagnostico $diagnostico)
    {
        $request->validate([
            'codigo' => 'required|string|max:20',
            'patologia' => 'required|string|max:255',
            'categoria' => 'required|string|max:50'
        ]);

        $diagnostico->update($request->all());
        return redirect()->route('diagnosticos.index')->with('success', 'Diagnóstico actualizado correctamente');
    }

    public function destroy(Diagnostico $diagnostico)
    {
        $diagnostico->delete();
        return redirect()->route('diagnosticos.index')->with('success', 'Diagnóstico eliminado correctamente');
    }

    public function buscar(Request $request)
    {
        $query = $request->input('q', '');

        if (empty($query)) {
            // Si no hay query, devolver todos los diagnósticos
            $diagnosticos = Diagnostico::orderBy('codigo', 'asc')->get();
        }
        else {
            // Si hay query, buscar con filtros
            $diagnosticos = Diagnostico::where('codigo', 'LIKE', "%{$query}%")
                ->orWhere('patologia', 'LIKE', "%{$query}%")
                ->orderBy('codigo', 'asc')
                ->get();
        }

        return response()->json($diagnosticos);
    }

    // Nueva función para obtener diagnósticos de salud mental (categorías SM03 y SM07)
    public function obtenerSaludMental()
    {
        $diagnosticosSM = Diagnostico::whereIn('categoria', ['SM03', 'SM07', 'SM1'])->get(['codigo', 'patologia', 'categoria']);
        return response()->json($diagnosticosSM);
    }

    /**
     * Obtener el siguiente código disponible para diagnóstico
     */
    public function obtenerSiguienteCodigo()
    {
        try {
            // Obtener el código numérico más alto de la tabla
            $maxCodigoNumerico = Diagnostico::whereRaw('codigo REGEXP "^[0-9]+$"')
                ->orderByRaw('CAST(codigo AS UNSIGNED) DESC')
                ->first();

            $siguienteCodigo = '001'; // Código por defecto si no hay registros numéricos

            if ($maxCodigoNumerico) {
                $ultimoNumero = intval($maxCodigoNumerico->codigo);
                $siguienteCodigo = str_pad($ultimoNumero + 1, 3, '0', STR_PAD_LEFT);
            }
            else {
                // Si no hay códigos numéricos, buscar el total de registros + 1
                $count = Diagnostico::count();
                $siguienteCodigo = str_pad($count + 1, 3, '0', STR_PAD_LEFT);
            }

            \Log::info('Diagnóstico - Código máximo encontrado: ' . ($maxCodigoNumerico ? $maxCodigoNumerico->codigo : 'ninguno'));
            \Log::info('Diagnóstico - Siguiente código generado: ' . $siguienteCodigo);

            return response()->json([
                'success' => true,
                'siguiente_codigo' => $siguienteCodigo,
                'ultimo_codigo' => $maxCodigoNumerico ? $maxCodigoNumerico->codigo : null
            ]);

        }
        catch (\Exception $e) {
            \Log::error('Error al obtener siguiente código de diagnóstico: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el siguiente código'
            ], 500);
        }
    }

    /**
     * Mostrar vista de condicionamientos
     */
    public function condicionamientos()
    {
        $diagnosticos = Diagnostico::orderBy('codigo')->get();
        $categorias = Diagnostico::select('categoria')->distinct()->pluck('categoria');

        // Cargar condicionamientos existentes
        $condicionamientos = CondicionamientoDiagnostico::all()->keyBy('codigo_diagnostico');

        return view('diagnosticos.condicionamientos', compact('diagnosticos', 'categorias', 'condicionamientos'));
    }

    /**
     * Actualizar condicionamiento individual
     */
    public function actualizarCondicionamiento(Request $request, Diagnostico $diagnostico)
    {
        try {
            \Log::info('Actualizando condicionamiento (' . $request->method() . ') para diagnóstico: ' . $diagnostico->codigo);

            // 1. Actualizar el modelo Diagnostico (Catálogo Maestro)
            $diagnostico->update([
                'edad_minima' => $request->filled('edad_minima') ? (int)$request->input('edad_minima') : null,
                'edad_maxima' => $request->filled('edad_maxima') ? (int)$request->input('edad_maxima') : null,
                'tipo_edad' => $request->input('tipo_edad', 'A'),
                'sexo_permitido' => $request->input('sexo_permitido', 'ambos'),
                'requiere_embarazo' => $request->boolean('requiere_embarazo'),
                'es_pediatrico' => $request->boolean('es_pediatrico'),
                'es_adulto' => $request->boolean('es_adulto'),
                'notas_validacion' => $request->input('notas_validacion'),
            ]);

            // 2. Mantener sincronizada la tabla secundaria CondicionamientoDiagnostico (opcional pero bueno para compatibilidad)
            $condicionamiento = CondicionamientoDiagnostico::updateOrCreate(
            ['codigo_diagnostico' => $diagnostico->codigo],
            [
                'codigo_diagnostico' => $diagnostico->codigo,
                'nombre_diagnostico' => $diagnostico->patologia,
                'embarazo' => $request->boolean('requiere_embarazo'),
                'pediatrico' => $request->boolean('es_pediatrico'),
                'adulto' => $request->boolean('es_adulto'),
                'edad_min' => $request->filled('edad_minima') ? (int)$request->input('edad_minima') : null,
                'edad_max' => $request->filled('edad_maxima') ? (int)$request->input('edad_maxima') : null,
                'notas_validacion' => $request->input('notas_validacion'),
            ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Condicionamiento actualizado correctamente en catálogo y tabla de reglas',
                'data' => $diagnostico
            ]);

        }
        catch (\Illuminate\Database\QueryException $e) {
            \Log::error('Error SQL al actualizar condicionamiento: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error de base de datos. Ver logs.'
            ], 500);
        }
        catch (\Exception $e) {
            \Log::error('Error general al actualizar condicionamiento: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error del servidor: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Actualizar condicionamientos en lote
     */
    public function actualizarCondicionamientosBatch(Request $request)
    {
        try {
            \Log::info('=== INICIO actualizarCondicionamientosBatch ===');
            $condicionamientos = $request->input('condicionamientos', []);
            \Log::info('Total de condicionamientos recibidos: ' . count($condicionamientos));

            if (empty($condicionamientos)) {
                return response()->json([
                    'success' => false,
                    'message' => 'No se recibieron condicionamientos para actualizar'
                ], 400);
            }

            $guardados = 0;
            $errores = [];

            foreach ($condicionamientos as $index => $cond) {
                try {
                    // Buscar el diagnóstico para obtener el código y nombre
                    $diagnostico = Diagnostico::find($cond['id']);
                    if (!$diagnostico) {
                        \Log::warning("Diagnóstico con ID {$cond['id']} no encontrado");
                        continue;
                    }

                    // 1. Actualizar modelo Diagnostico
                    $diagnostico->update([
                        'edad_minima' => isset($cond['edad_minima']) && $cond['edad_minima'] !== '' ? (int)$cond['edad_minima'] : null,
                        'edad_maxima' => isset($cond['edad_maxima']) && $cond['edad_maxima'] !== '' ? (int)$cond['edad_maxima'] : null,
                        'tipo_edad' => $cond['tipo_edad'] ?? 'A',
                        'sexo_permitido' => $cond['sexo_permitido'] ?? 'ambos',
                        'requiere_embarazo' => filter_var($cond['requiere_embarazo'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'es_pediatrico' => filter_var($cond['es_pediatrico'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'es_adulto' => filter_var($cond['es_adulto'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'notas_validacion' => $cond['notas_validacion'] ?? null,
                    ]);

                    // 2. Actualizar tabla secundaria de condicionamiento
                    CondicionamientoDiagnostico::updateOrCreate(
                    ['codigo_diagnostico' => $diagnostico->codigo],
                    [
                        'codigo_diagnostico' => $diagnostico->codigo,
                        'nombre_diagnostico' => $diagnostico->patologia,
                        'embarazo' => filter_var($cond['requiere_embarazo'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'pediatrico' => filter_var($cond['es_pediatrico'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'adulto' => filter_var($cond['es_adulto'] ?? false, FILTER_VALIDATE_BOOLEAN),
                        'edad_min' => isset($cond['edad_minima']) && $cond['edad_minima'] !== '' ? (int)$cond['edad_minima'] : null,
                        'edad_max' => isset($cond['edad_maxima']) && $cond['edad_maxima'] !== '' ? (int)$cond['edad_maxima'] : null,
                        'notas_validacion' => isset($cond['notas_validacion']) && $cond['notas_validacion'] !== '' ? $cond['notas_validacion'] : null,
                    ]
                    );

                    $guardados++;

                }
                catch (\Exception $e) {
                    $error = "Error en diagnóstico ID {$cond['id']}: " . $e->getMessage();
                    \Log::error($error);
                    $errores[] = $error;
                }
            }

            \Log::info("Condicionamientos guardados: $guardados");
            \Log::info('=== FIN actualizarCondicionamientosBatch ===');

            $response = [
                'success' => true,
                'guardados' => $guardados,
                'message' => "Se guardaron $guardados condicionamientos correctamente"
            ];

            if (!empty($errores)) {
                $response['errores'] = $errores;
                $response['message'] .= ' (con algunos errores)';
            }

            return response()->json($response);

        }
        catch (\Exception $e) {
            \Log::error('Error al guardar condicionamientos: ' . $e->getMessage());
            \Log::error('Trace: ' . $e->getTraceAsString());
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener validaciones en formato JSON para usar en ingresos
     */
    public function obtenerValidacionesJson()
    {
        try {
            $diagnosticos = Diagnostico::whereNotNull('edad_minima')
                ->orWhereNotNull('edad_maxima')
                ->orWhere('sexo_permitido', '!=', 'ambos')
                ->orWhere('requiere_embarazo', true)
                ->orWhere('es_pediatrico', true)
                ->orWhere('es_adulto', true)
                ->get(['codigo', 'patologia', 'edad_minima', 'edad_maxima', 'tipo_edad',
                'sexo_permitido', 'requiere_embarazo', 'es_pediatrico', 'es_adulto']);

            return response()->json([
                'success' => true,
                'validaciones' => $diagnosticos
            ]);
        }
        catch (\Exception $e) {
            \Log::error('Error al obtener validaciones JSON: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener las validaciones'
            ], 500);
        }
    }

    public function normalizar()
    {
        try {
            \Log::info('Iniciando normalización de diagnósticos...');

            // 1. Obtener catálogo maestro para búsqueda rápida
            $catalogo = Diagnostico::all();
            $mapaPorNombreUpper = [];
            foreach ($catalogo as $diag) {
                $nombre = strtoupper(trim($diag->patologia));
                if (!empty($nombre)) {
                    $mapaPorNombreUpper[$nombre] = $diag->codigo;
                }
            }

            $codigosValidos = $catalogo->pluck('codigo')->toArray();

            $totalActualizados = 0;
            $detalles = [];

            // 2. Procesar registros globales en bloques para no saturar memoria
            DB::table('registros_globales')->orderBy('id')->chunk(500, function ($registros) use (&$totalActualizados, &$detalles, $mapaPorNombreUpper, $codigosValidos) {
                foreach ($registros as $r) {
                    $updates = [];
                    $filaCambio = false;

                    // A. Normalizar campos de diagnóstico (1 a 7)
                    for ($i = 1; $i <= 7; $i++) {
                        $codKey = "cod_{$i}";
                        $diagKey = "diagnostico_{$i}";

                        $codActual = isset($r->$codKey) ? (string)$r->$codKey : '';
                        $nombreRaw = isset($r->$diagKey) ? $r->$diagKey : '';
                        // Limpieza profunda de nombre (espacios extra, saltos de línea, etc.)
                        $nombreActual = strtoupper(trim(preg_replace('/\s+/', ' ', $nombreRaw)));

                        if (empty($nombreActual))
                            continue;

                        // Intentar buscar por nombre
                        if (isset($mapaPorNombreUpper[$nombreActual])) {
                            $nuevoCod = (string)$mapaPorNombreUpper[$nombreActual];

                            // Si el código NO coincide o es del rango SM07, forzar actualización para normalizar
                            // (Especialmente importante para salud mental 19-43)
                            $esSM07 = (intval($nuevoCod) >= 19 && intval($nuevoCod) <= 43);

                            if ($nuevoCod != $codActual || $esSM07) {
                                if ($nuevoCod != $codActual) {
                                    $updates[$codKey] = $nuevoCod;
                                    $filaCambio = true;
                                    $detalles[] = "ID {$r->id}: {$nombreActual} (COD_{$i}: '{$codActual}' -> '{$nuevoCod}')";
                                }
                            }
                        }
                    }

                    // B. DETECCIÓN INTELIGENTE DE PARTICIPANTES EN SG (ESPECIAL PARA REVERSA SM1-07)
                    // Si diagnostico_1 es una actividad grupal, y SG parece un conteo (ej: 12)
                    $diag1 = isset($r->diagnostico_1) ? strtoupper(trim(preg_replace('/\s+/', ' ', $r->diagnostico_1))) : '';
                    if (isset($mapaPorNombreUpper[$diag1])) {
                        $codOficial = (string)$mapaPorNombreUpper[$diag1];
                        $valCod = intval($codOficial);

                        // Si es una actividad de las que tienen "Número de Participantes" debajo
                        $esGrupal = in_array($valCod, [24, 26, 28, 31, 33, 36]);

                        if ($esGrupal) {
                            $sgActual = isset($r->sg) ? (string)$r->sg : '';
                            $numActual = (int)($r->numero ?: 0);

                            // Si SG es numérico y diferente al código de actividad, probablemente son los participantes
                            if (!empty($sgActual) && is_numeric($sgActual) && $sgActual != $codOficial) {
                                // Mover SG a numero (si numero es 1 o menor)
                                if ($numActual <= 1) {
                                    $updates['numero'] = (int)$sgActual;
                                    $updates['sg'] = $codOficial; // Unificar SG con el código de la actividad
                                    $filaCambio = true;
                                    $detalles[] = "ID {$r->id}: Detectados participantes en SG ('{$sgActual}' -> NUMERO, SG: '{$codOficial}')";
                                }
                            }
                        }
                        else if ($valCod >= 19 && $valCod <= 43) {
                            // Para cualquier otra actividad SM07, asegurar que SG tenga el código correcto
                            if (isset($r->sg) && (string)$r->sg != $codOficial) {
                                $updates['sg'] = $codOficial;
                                $filaCambio = true;
                            }
                        }
                    }

                    if ($filaCambio) {
                        DB::table('registros_globales')->where('id', $r->id)->update($updates);
                        $totalActualizados++;
                    }
                }
            });

            return response()->json([
                'success' => true,
                'message' => "Normalización completada. Se actualizaron {$totalActualizados} registros.",
                'total' => $totalActualizados,
                'detalles' => array_slice($detalles, 0, 50)
            ]);

        }
        catch (\Exception $e) {
            \Log::error('Error en normalización: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error durante el proceso: ' . $e->getMessage()
            ], 500);
        }
    }
}
