<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Adolescente;
use App\Models\AdolescenteControl;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\AdolescentesExport;

class AdolescenteController extends Controller
{
    /**
     * Muestra la lista de adolescentes registrados.
     */
    public function index(Request $request)
    {
        $query = Adolescente::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('no_expediente', 'like', "%{$search}%")
                    ->orWhere('nombre_completo', 'like', "%{$search}%")
                    ->orWhere('numero_identidad', 'like', "%{$search}%");
            });
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_ingreso', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_ingreso', '<=', $request->fecha_hasta);
        }

        $registros = $query->orderByRaw("LENGTH(REPLACE(no_expediente, '-', '')) ASC")
                           ->orderByRaw("REPLACE(no_expediente, '-', '') ASC")
                           ->paginate(500);
        $colonias = DB::table('colonias')->orderBy('COLONIA', 'asc')->get();

        if ($request->ajax()) {
            return view('adolescentes.partials.table_rows', compact('registros', 'colonias'))->render();
        }

        return view('adolescentes.index', compact('registros', 'colonias'));
    }

    /**
     * Formulario para crear un nuevo adolescente.
     */
    public function create()
    {
        return view('adolescentes.create');
    }

    /**
     * Almacena un nuevo adolescente en la base de datos.
     */
    public function store(Request $request)
    {
        $request->validate([
            'no_expediente' => 'required|unique:adolescentes,no_expediente',
            'nombre_completo' => 'required',
            'sexo' => 'required|in:F,M',
            'fecha_nacimiento' => 'required|date',
            'fecha_ingreso' => 'required|date',
            'edad' => 'required|integer',
        ]);

        Adolescente::create($request->all());

        return redirect()->route('adolescentes.index')->with('success', 'Paciente registrado correctamente.');
    }

    /**
     * Formulario para editar un adolescente.
     */
    public function edit($id)
    {
        try {
            $adolescente = Adolescente::findOrFail($id);
            if (request()->ajax()) {
                $response = [
                    'id' => $adolescente->id,
                    'no_expediente' => $adolescente->no_expediente,
                    'nombre_completo' => $adolescente->nombre_completo,
                    'sexo' => $adolescente->sexo,
                    'fecha_nacimiento' => $adolescente->fecha_nacimiento ? Carbon::parse($adolescente->fecha_nacimiento)->format('Y-m-d') : null,
                    'fecha_ingreso' => $adolescente->fecha_ingreso ? Carbon::parse($adolescente->fecha_ingreso)->format('Y-m-d') : null,
                    'edad' => $adolescente->edad,
                    'numero_identidad' => $adolescente->numero_identidad,
                    'nombre_tutor' => $adolescente->nombre_tutor,
                    'direccion_completa' => $adolescente->direccion_completa,
                    'numero_telefono' => $adolescente->numero_telefono,
                    'estado_civil' => $adolescente->estado_civil,
                    'escolaridad' => $adolescente->escolaridad,
                    'anios_cursados' => $adolescente->anios_cursados,
                    'colonia' => $adolescente->colonia,
                    'ocupacion' => $adolescente->ocupacion
                ];
                return response()->json($response);
            }
            return view('adolescentes.edit', compact('adolescente'));
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json(['error' => $e->getMessage()], 500);
            }
            return redirect()->back()->with('error', $e->getMessage());
        }
    }

    /**
     * Actualiza los datos de un adolescente.
     */
    public function update(Request $request, $id)
    {
        $request->validate([
            'no_expediente' => 'required|unique:adolescentes,no_expediente,' . $id,
            'nombre_completo' => 'required',
            'sexo' => 'required|in:F,M',
            'fecha_nacimiento' => 'required|date',
            'fecha_ingreso' => 'required|date',
            'edad' => 'required|integer',
        ]);

        $adolescente = Adolescente::findOrFail($id);
        $adolescente->update($request->all());

        return redirect()->route('adolescentes.index')->with('success', 'Registro actualizado correctamente.');
    }

    /**
     * Actualización por AJAX para campos individuales.
     */
    public function ajaxUpdate(Request $request, $id)
    {
        $adolescente = Adolescente::findOrFail($id);
        $field = $request->input('field');
        $value = $request->input('value');

        if (!$field || !array_key_exists($field, $adolescente->getAttributes()) && !in_array($field, $adolescente->getFillable())) {
            return response()->json(['success' => false, 'message' => 'Campo inválido'], 400);
        }

        $adolescente->update([$field => $value]);

        return response()->json(['success' => true]);
    }

    /**
     * Elimina un adolescente.
     */
    public function destroy($id)
    {
        $adolescente = Adolescente::findOrFail($id);
        $adolescente->delete();

        return redirect()->route('adolescentes.index')->with('success', 'Registro eliminado correctamente.');
    }

    /**
     * Muestra la lista de todos los controles/seguimientos.
     */
    public function seguimientos(Request $request)
    {
        $query = AdolescenteControl::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('no_expediente', 'like', "%{$search}%")
                    ->orWhere('nombre_completo', 'like', "%{$search}%")
                    ->orWhere('numero_identidad', 'like', "%{$search}%");
            });
        }

        if ($request->filled('fecha_desde')) {
            $query->whereDate('fecha_consulta', '>=', $request->fecha_desde);
        }

        if ($request->filled('fecha_hasta')) {
            $query->whereDate('fecha_consulta', '<=', $request->fecha_hasta);
        }

        $registros = $query->orderBy('fecha_consulta', 'desc')->paginate(500);

        if ($request->ajax()) {
            return view('adolescentes.partials.table_rows_seguimientos', compact('registros'))->render();
        }

        return view('adolescentes.seguimientos', compact('registros'));
    }

    /**
     * Formulario para crear un seguimiento para un adolescente específico.
     */
    public function seguimientoCreate($no_expediente)
    {
        $adolescente = Adolescente::where('no_expediente', $no_expediente)->firstOrFail();
        return view('seguimientos.create', compact('adolescente'));
    }

    /**
     * Almacena un nuevo seguimiento.
     */
    public function seguimientoStore(Request $request)
    {
        $request->validate([
            'no_expediente' => 'required',
            'fecha_consulta' => 'required|date',
            'diagnostico_seguimiento' => 'required',
        ]);

        AdolescenteControl::create($request->all());

        return redirect()->route('adolescentes.historial', $request->no_expediente)
            ->with('success', 'Seguimiento agregado correctamente.');
    }

    /**
     * Formulario para editar un seguimiento.
     */
    public function seguimientoEdit($id)
    {
        $seguimiento = AdolescenteControl::findOrFail($id);
        if (request()->ajax()) {
            return response()->json($seguimiento);
        }
        $adolescente = Adolescente::where('no_expediente', $seguimiento->no_expediente)->first();
        return view('seguimientos.edit', compact('seguimiento', 'adolescente'));
    }

    /**
     * Actualiza un seguimiento.
     */
    public function seguimientoUpdate(Request $request, $id)
    {
        $request->validate([
            'fecha_consulta' => 'required|date',
            'diagnostico_seguimiento' => 'required',
        ]);

        $seguimiento = AdolescenteControl::findOrFail($id);
        $seguimiento->update($request->all());

        return redirect()->route('adolescentes.historial', $seguimiento->no_expediente)
            ->with('success', 'Seguimiento actualizado correctamente.');
    }

    /**
     * Elimina un seguimiento.
     */
    public function seguimientoDestroy($id)
    {
        $seguimiento = AdolescenteControl::findOrFail($id);
        $no_exp = $seguimiento->no_expediente;
        $seguimiento->delete();

        return redirect()->route('adolescentes.historial', $no_exp)
            ->with('success', 'Seguimiento eliminado correctamente.');
    }

    /**
     * Muestra el historial de seguimientos de un adolescente.
     */
    public function historial($no_expediente)
    {
        $adolescente = Adolescente::where('no_expediente', $no_expediente)->firstOrFail();
        $seguimientos = AdolescenteControl::where('no_expediente', $no_expediente)
            ->orderBy('fecha_consulta', 'desc')
            ->get();

        return view('adolescentes.historial', compact('adolescente', 'seguimientos'));
    }

    /**
     * Busca un adolescente por número de identidad o expediente (Usado por el módulo de ingresos).
     */
    public function checkIdentity(Request $request)
    {
        $id = $request->query('numero_identidad');
        $exp = $request->query('no_expediente');

        if (!$id && !$exp) {
            return response()->json(null);
        }

        try {
            $registro = null;

            if ($id) {
                $registro = DB::table('adolescentes')
                    ->where('numero_identidad', $id)
                    ->first();
                if ($registro) {
                    $registro->procedencia = 'MAESTRO';
                }

                if (!$registro) {
                    $registro = DB::table('adolescentes_control')
                        ->where('numero_identidad', $id)
                        ->orderBy('created_at', 'desc')
                        ->first();
                    if ($registro) {
                        $registro->procedencia = 'SEGUIMIENTO';
                    }
                }
            }

            if (!$registro && $exp) {
                $registro = DB::table('adolescentes')
                    ->where('no_expediente', $exp)
                    ->first();
                if ($registro) {
                    $registro->procedencia = 'MAESTRO';
                }

                if (!$registro) {
                    $registro = DB::table('adolescentes_control')
                        ->where('no_expediente', $exp)
                        ->orderBy('created_at', 'desc')
                        ->first();
                    if ($registro) {
                        $registro->procedencia = 'SEGUIMIENTO';
                    }
                }
            }

            if ($registro) {
                if (isset($registro->sexo)) {
                    if ($registro->sexo === 'M')
                        $registro->sexo = 'H';
                    elseif ($registro->sexo === 'F')
                        $registro->sexo = 'M';
                }
            }

            return response()->json($registro);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    /**
     * Verifica si un DNI ya existe (Usado para validación en tiempo real).
     */
    public function checkDni(Request $request)
    {
        $dni = $request->query('dni');
        if (!$dni) return response()->json(['exists' => false]);

        $exists = DB::table('adolescentes')->where('numero_identidad', $dni)->exists();
        return response()->json(['exists' => $exists]);
    }

    /**
     * Verifica si un Expediente ya existe (Usado para validación en tiempo real).
     */
    public function checkExpediente(Request $request)
    {
        $exp = $request->query('exp');
        if (!$exp) return response()->json(['exists' => false]);

        $exists = DB::table('adolescentes')->where('no_expediente', $exp)->exists();
        return response()->json(['exists' => $exists]);
    }

    /**
     * Muestra la lista de pacientes depurados (fuera del rango 10-19 años).
     */
    public function depurados(Request $request)
    {
        $query = Adolescente::query();

        // Lógica de depuración: Pacientes que hoy tienen < 10 o > 19 años
        $hoy = Carbon::now();
        $query->where(function($q) use ($hoy) {
            $q->whereRaw("TIMESTAMPDIFF(YEAR, fecha_nacimiento, ?) NOT BETWEEN 10 AND 19", [$hoy->format('Y-m-d')]);
        });

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('no_expediente', 'like', "%{$search}%")
                    ->orWhere('nombre_completo', 'like', "%{$search}%")
                    ->orWhere('numero_identidad', 'like', "%{$search}%");
            });
        }

        $registros = $query->orderBy('fecha_nacimiento', 'desc')->paginate(500);
        $colonias = DB::table('colonias')->orderBy('COLONIA', 'asc')->get();

        return view('adolescentes.depurados', compact('registros', 'colonias'));
    }

    /**
     * Guarda múltiples adolescentes en las tablas correspondientes (Usado por el módulo de ingresos).
     */
    public function storeBatch(Request $request)
    {
        $rows = $request->input('adolescentes', []);

        try {
            DB::beginTransaction();

            foreach ($rows as $row) {
                $data = [
                    'no_expediente' => $row['no_expediente'] ?? null,
                    'nombre_completo' => (!empty($row['nombre_completo']) && $row['nombre_completo'] !== 'null') ? $row['nombre_completo'] : 'PACIENTE SIN NOMBRE',
                    'sexo' => 'F',
                    'fecha_nacimiento' => null,
                    'edad' => $row['edad'] ?? null,
                    'numero_identidad' => $row['numero_identidad'] ?? null,
                    'nombre_tutor' => $row['nombre_tutor'] ?? null,
                    'direccion_completa' => $row['direccion_completa'] ?? null,
                    'numero_telefono' => $row['numero_telefono'] ?? null,
                    'estado_civil' => $row['estado_civil'] ?? null,
                    'colonia' => $row['colonia'] ?? null,
                    'medico_atencion' => $row['medico_atencion'] ?? null,
                    'usuario_registro' => $row['usuario_registro'] ?? null,
                    'escolaridad' => $row['escolaridad'] ?? null,
                    'anios_cursados' => $row['anios_cursados'] ?? null,
                    'ocupacion' => $row['ocupacion'] ?? null,
                    'fecha_ingreso' => null, // Se asignará abajo
                    'updated_at' => now(),
                ];

                // Asignar fecha_ingreso desde la fecha de consulta
                if (!empty($row['fecha'])) {
                    try {
                        if (strpos($row['fecha'], '/') !== false) {
                            $data['fecha_ingreso'] = Carbon::createFromFormat('d/m/Y', $row['fecha'])->format('Y-m-d');
                        } else {
                            $data['fecha_ingreso'] = Carbon::parse($row['fecha'])->format('Y-m-d');
                        }
                    } catch (\Exception $e) {
                        $data['fecha_ingreso'] = now()->format('Y-m-d');
                    }
                } else {
                    $data['fecha_ingreso'] = now()->format('Y-m-d');
                }

                $sexoApp = strtoupper($row['sexo'] ?? '');
                if ($sexoApp === 'H' || $sexoApp === 'M' || $sexoApp === 'SEXO') {
                    $data['sexo'] = 'M';
                } elseif ($sexoApp === 'M' || $sexoApp === 'F') {
                    $data['sexo'] = 'F';
                }

                if (!empty($row['fecha_nacimiento']) && $row['fecha_nacimiento'] !== 'null') {
                    try {
                        if (strpos($row['fecha_nacimiento'], '/') !== false) {
                            $data['fecha_nacimiento'] = Carbon::createFromFormat('d/m/Y', $row['fecha_nacimiento'])->format('Y-m-d');
                        } else {
                            $data['fecha_nacimiento'] = Carbon::parse($row['fecha_nacimiento'])->format('Y-m-d');
                        }
                    } catch (\Exception $e) {
                        $data['fecha_nacimiento'] = '1900-01-01';
                    }
                } else {
                    $data['fecha_nacimiento'] = '1900-01-01';
                }

                foreach ($data as $key => $value) {
                    if ($value === '' || $value === 'null')
                        $data[$key] = null;
                }

                $condIngreso = strtoupper($row['cond'] ?? 'N');

                $existingByDni = null;
                if (!empty($data['numero_identidad'])) {
                    $existingByDni = DB::table('adolescentes')
                        ->where('numero_identidad', $data['numero_identidad'])
                        ->first();
                }

                $existingByExp = null;
                if (!$existingByDni && !empty($data['no_expediente'])) {
                    $existingByExp = DB::table('adolescentes')
                        ->where('no_expediente', $data['no_expediente'])
                        ->first();
                }

                $targetRecord = $existingByDni ?: $existingByExp;

                if ($targetRecord) {
                    // Solo actualizar fecha_ingreso si el registro existente no tiene una
                    if (!empty($targetRecord->fecha_ingreso)) {
                        unset($data['fecha_ingreso']);
                    }

                    DB::table('adolescentes')
                        ->where('id', $targetRecord->id)
                        ->update($data);
                } else {
                    $data['created_at'] = now();
                    DB::table('adolescentes')
                        ->insert($data);
                }

                if ($condIngreso === 'S') {
                    if (!empty($row['fecha'])) {
                        try {
                            if (strpos($row['fecha'], '/') !== false) {
                                $data['fecha_consulta'] = Carbon::createFromFormat('d/m/Y', $row['fecha'])->format('Y-m-d');
                            } else {
                                $data['fecha_consulta'] = Carbon::parse($row['fecha'])->format('Y-m-d');
                            }
                        } catch (\Exception $e) {
                            $data['fecha_consulta'] = date('Y-m-d');
                        }
                    } else {
                        $data['fecha_consulta'] = date('Y-m-d');
                    }

                    $diagnosticos = [];
                    for ($i = 1; $i <= 7; $i++) {
                        if (!empty($row['diagnostico_' . $i]))
                            $diagnosticos[] = $row['diagnostico_' . $i];
                    }
                    $data['diagnostico_seguimiento'] = implode(', ', $diagnosticos);
                    $data['created_at'] = now();

                    // IMPORTANTE: Quitar fecha_ingreso ya que no existe en adolescentes_control
                    $dataControl = $data;
                    unset($dataControl['fecha_ingreso']);

                    DB::table('adolescentes_control')
                        ->insert($dataControl);
                }
            }

            DB::commit();
            return response()->json(['success' => true]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['success' => false, 'error' => $e->getMessage()], 500);
        }
    }

    /**
     * Exporta los datos de adolescentes a Excel con múltiples hojas.
     */
    public function exportExcel()
    {
        return Excel::download(new AdolescentesExport, 'Reporte_Adolescentes_' . date('d-m-Y') . '.xlsx');
    }
}
