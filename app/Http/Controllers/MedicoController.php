<?php

namespace App\Http\Controllers;

use App\Models\Medico;
use App\Models\HoraMedicoPosicion;
use Illuminate\Http\Request;
use Yajra\DataTables\Facades\DataTables;

class MedicoController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        if ($request->ajax()) {
            try {
                $medicos = Medico::select([
                    'id', 'COD_MED', 'NOM_MED', 'JORNADA', 'NOMINA',
                    'ESPECIALIDAD', 'MODALIDAD', 'estado', 'FECHA_INGRESO',
                    'observaciones', 'es_director', 'es_ong'
                ])->orderBy('NOM_MED')->get();

                return response()->json(['data' => $medicos]);
            }
            catch (\Exception $e) {
                \Log::error('Error en MedicoController index: ' . $e->getMessage());
                return response()->json(['error' => 'Error al cargar datos: ' . $e->getMessage()], 500);
            }
        }
        return view('medicos.index');
    }

    /**
     * Alternar el estado de Director del CIS
     */
    public function toggleDirector(Medico $medico, Request $request)
    {
        $esDirector = $request->boolean('es_director');
        if ($esDirector) {
            // Desactivar el rol de director de los demás médicos
            Medico::where('id', '!=', $medico->id)->update(['es_director' => false]);
            HoraMedicoPosicion::truncate();
        }
        $medico->es_director = $esDirector;
        $medico->save();

        return response()->json([
            'success' => true,
            'message' => $esDirector ? 'Médico asignado como Director del CIS' : 'Médico desmarcado como Director del CIS',
            'es_director' => $medico->es_director
        ]);
    }

    /**
     * Buscar médico por código para autocompletado
     */
    public function buscarPorCodigo(Request $request)
    {
        $codigo = $request->input('codigo');

        if (!$codigo) {
            return response()->json(['error' => 'Código requerido'], 400);
        }

        $medico = Medico::where('COD_MED', $codigo)->first();

        if ($medico) {
            return response()->json([
                'success' => true,
                'medico' => [
                    'codigo' => $medico->COD_MED,
                    'nombre' => $medico->NOM_MED,
                    'especialidad' => $medico->ESPECIALIDAD ?? '',
                    'jornada' => $medico->JORNADA ?? ''
                ]
            ]);
        }

        return response()->json([
            'success' => false,
            'message' => 'Médico no encontrado'
        ], 404);
    }

    /**
     * Obtener todos los médicos para autocompletado
     */
    public function obtenerTodos()
    {
        $medicos = Medico::select('COD_MED', 'NOM_MED', 'ESPECIALIDAD', 'JORNADA')->get();

        return response()->json([
            'medicos' => $medicos->map(function ($medico) {
            return [
                    'codigo' => $medico->COD_MED,
                    'nombre' => $medico->NOM_MED,
                    'especialidad' => $medico->ESPECIALIDAD ?? '',
                    'jornada' => $medico->JORNADA ?? ''
                ];
        })
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('medicos.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            // DEBUG: Log información de la petición
            \Log::info('MedicoController::store - Información de petición:', [
                'is_ajax' => $request->ajax(),
                'wants_json' => $request->wantsJson(),
                'content_type' => $request->header('Content-Type'),
                'accept' => $request->header('Accept'),
                'x_requested_with' => $request->header('X-Requested-With'),
                'method' => $request->method(),
                'url' => $request->url()
            ]);

            // Check if the code already exists before validation
            $existingMedico = Medico::where('COD_MED', $request->COD_MED)->first();
            if ($existingMedico) {
                \Log::info('Código duplicado detectado, tipo de respuesta:', [
                    'is_ajax' => $request->ajax(),
                    'wants_json' => $request->wantsJson()
                ]);

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'success' => false,
                        'message' => 'El código de médico ya existe. Por favor, use un código diferente.',
                        'error_type' => 'duplicate_code'
                    ], 422);
                }

                return redirect()->back()->with('error', 'El código de médico ya existe. Por favor, use un código diferente.')->withInput();
            }

            $validated = $request->validate([
                'COD_MED' => 'required',
                'NOM_MED' => 'required',
                'ESPECIALIDAD' => 'nullable',
                'NOMINA' => 'nullable',
                'JORNADA' => 'required',
                'MODALIDAD' => 'nullable',
                'FECHA_INGRESO' => 'nullable|date',
                'HORAS_CONTRATADAS' => 'nullable|numeric|min:0',
                'consultas_por_hora' => 'nullable|numeric|min:0',
                'consultas_dia' => 'nullable|numeric|min:0',
                'TELEFONO' => 'nullable|max:15',
                'CORREO' => 'nullable|email|max:100',
                'TIPO_CONSULTA' => 'nullable|max:100',
                'estado' => 'required|in:activo,inactivo',
                'observaciones' => 'nullable',
                'es_director' => 'nullable|boolean'
            ]);

            $validated['es_director'] = $request->has('es_director');
            if ($validated['es_director']) {
                Medico::where('id', '>', 0)->update(['es_director' => false]);
                HoraMedicoPosicion::truncate();
            }

            // Use DB transaction to ensure atomicity
            $medico = \DB::transaction(function () use ($validated) {
                // Double-check for duplicates within transaction
                $exists = Medico::where('COD_MED', $validated['COD_MED'])->exists();
                if ($exists) {
                    throw new \Exception('El código de médico ya existe');
                }

                $data = [
                    'COD_MED' => $validated['COD_MED'],
                    'NOM_MED' => $validated['NOM_MED'],
                    'ESPECIALIDAD' => $validated['ESPECIALIDAD'] ?? 'MEDICO GENERAL',
                    'JORNADA' => $validated['JORNADA'],
                    'NOMINA' => $validated['NOMINA'] ?? 'MEDICO ASISTENCIAL',
                    'MODALIDAD' => $validated['MODALIDAD'] ?? 'PERMANENTE',
                    'FECHA_INGRESO' => $validated['FECHA_INGRESO'] ?? now()->toDateString(),
                    'CORREO' => $validated['CORREO'] ?? '',
                    'TELEFONO' => $validated['TELEFONO'] ?? '',
                    'HORAS_CONTRATADAS' => $validated['HORAS_CONTRATADAS'] ?? 6,
                    'CONSULTAS' => $validated['TIPO_CONSULTA'] ?? '',
                    'consultas_por_hora' => $validated['consultas_por_hora'] ?? 6,
                    'consultas_dia' => $validated['consultas_dia'] ?? 0,
                    'estado' => $validated['estado'],
                    'observaciones' => $validated['observaciones'] ?? '',
                    'es_director' => $validated['es_director']
                ];

                if (str_starts_with(strtoupper(trim($data['NOM_MED'])), 'MSS.')) {
                    $data['MODALIDAD'] = 'SERVICIO SOCIAL';
                    $data['NOMINA'] = 'SERVICIO SOCIAL';
                    $data['ESPECIALIDAD'] = 'SERVICIO SOCIAL';
                }

                return Medico::create($data);
            });

            // Si es una petición AJAX (desde modal), devolver JSON
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Médico agregado correctamente',
                    'medico' => $medico
                ]);
            }

            // Si es una petición normal (desde vista create), redirigir al index
            \Log::info('Devolviendo redirección al index');
            return redirect()->route('medicos.index')->with('success', 'Médico agregado correctamente');

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
                    'message' => 'Error al guardar el médico: ' . $e->getMessage(),
                    'error_type' => 'general_error'
                ], 500);
            }
            return redirect()->back()->with('error', 'Error al guardar el médico: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Medico $medico)
    {
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'medico' => $medico]);
        }
        return view('medicos.show', compact('medico'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Medico $medico)
    {
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'medico' => $medico]);
        }
        return view('medicos.edit', compact('medico'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Medico $medico)
    {
        $validated = $request->validate([
            'COD_MED'           => 'required|unique:medicos,COD_MED,' . $medico->id,
            'NOM_MED'           => 'required',
            'JORNADA'           => 'required',
            'NOMINA'            => 'nullable',
            'ESPECIALIDAD'      => 'nullable',
            'MODALIDAD'         => 'nullable',
            'FECHA_INGRESO'     => 'nullable|date',
            'CORREO'            => 'nullable|email',
            'TELEFONO'          => 'nullable',
            'HORAS_CONTRATADAS' => 'nullable|numeric',
            'CONSULTAS'         => 'nullable',
            'consultas_por_hora'=> 'nullable|numeric',
            'consultas_dia'     => 'nullable|numeric',
            'estado'            => 'required|in:activo,inactivo',
            'observaciones'     => 'nullable',
            'es_director'       => 'nullable|boolean'
        ]);

        // Keep existing values for fields not submitted (modal edit may omit some)
        if (empty($validated['NOMINA']))      $validated['NOMINA']      = $medico->NOMINA;
        if (empty($validated['ESPECIALIDAD'])) $validated['ESPECIALIDAD'] = $medico->ESPECIALIDAD;
        if (empty($validated['MODALIDAD']))   $validated['MODALIDAD']   = $medico->MODALIDAD;
        if (empty($validated['FECHA_INGRESO'])) $validated['FECHA_INGRESO'] = $medico->FECHA_INGRESO;

        if (str_starts_with(strtoupper(trim($validated['NOM_MED'])), 'MSS.')) {
            $validated['MODALIDAD'] = 'SERVICIO SOCIAL';
            $validated['NOMINA'] = 'SERVICIO SOCIAL';
            $validated['ESPECIALIDAD'] = 'SERVICIO SOCIAL';
        }

        $validated['es_director'] = $request->has('es_director');
        if ($validated['es_director']) {
            Medico::where('id', '!=', $medico->id)->update(['es_director' => false]);
            HoraMedicoPosicion::truncate();
        }

        $medico->update($validated);

        if ($request->ajax() || $request->wantsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Médico actualizado correctamente',
                'medico' => $medico
            ]);
        }

        return redirect()->route('medicos.index')->with('success', 'Médico actualizado');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Medico $medico)
    {
        $medico->delete();
        if (request()->ajax() || request()->wantsJson()) {
            return response()->json(['success' => true, 'message' => 'Médico eliminado']);
        }
        return back()->with('success', 'Médico eliminado');
    }

    // Método para generar planilla médica
    public function planilla()
    {
        $medicos = Medico::orderBy('NOM_MED')->get();

        return view('medicos.planilla', [
            'medicos' => $medicos,
            'fecha' => now()->format('d/m/Y')
        ]);
    }

    /**
     * Obtener el siguiente código disponible para médico
     */
    public function obtenerSiguienteCodigo()
    {
        try {
            // Obtener el último código numérico
            $ultimoMedico = Medico::whereRaw('COD_MED REGEXP "^[0-9]+$"')
                ->orderByRaw('CAST(COD_MED AS UNSIGNED) DESC')
                ->first();

            $siguienteCodigo = 1;

            if ($ultimoMedico) {
                $siguienteCodigo = intval($ultimoMedico->COD_MED) + 1;
            }

            // Formatear con ceros a la izquierda si es necesario (ej: 001, 002, etc.)
            $codigoFormateado = str_pad($siguienteCodigo, 3, '0', STR_PAD_LEFT);

            return response()->json([
                'success' => true,
                'siguiente_codigo' => $codigoFormateado,
                'ultimo_codigo' => $ultimoMedico ? $ultimoMedico->COD_MED : null
            ]);

        }
        catch (\Exception $e) {
            \Log::error('Error al obtener siguiente código de médico: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener el siguiente código'
            ], 500);
        }
    }
}
