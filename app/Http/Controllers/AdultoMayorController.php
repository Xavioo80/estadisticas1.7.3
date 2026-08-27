<?php

namespace App\Http\Controllers;

use App\Models\DatoAdultoMayor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AdultoMayorController extends Controller
{
    public function index(Request $request)
    {
        $search = $request->input('search');

        $registros = DatoAdultoMayor::query()
            ->when($search, function ($q) use ($search) {
                $q->where('nombre_completo', 'LIKE', "%{$search}%")
                  ->orWhere('dni', 'LIKE', "%{$search}%")
                  ->orWhere('expediente', 'LIKE', "%{$search}%")
                  ->orWhere('direccion', 'LIKE', "%{$search}%");
            })
            ->orderBy('nombre_completo')
            ->paginate(50)
            ->withQueryString();

        $total = DatoAdultoMayor::count();

        // Estadísticas adicionales
        $nuevosEsteMes = DatoAdultoMayor::whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count();

        $promedioEdad = DatoAdultoMayor::whereNotNull('edad')
            ->where('edad', '>', 0)
            ->avg('edad') ?? 0;

        // Obtener colonias excluyendo KINDER y ESCUELA, ordenadas por código
        $colonias = DB::table('colonias')
            ->where('COLONIA', 'NOT LIKE', '%KINDER%')
            ->where('COLONIA', 'NOT LIKE', '%ESCUELA%')
            ->orderByRaw('CAST(COD_COL AS UNSIGNED)')
            ->get();

        // Si es petición AJAX, retornar solo las filas
        if ($request->ajax()) {
            return view('adulto-mayor.partials.table_rows', compact('registros'))->render();
        }

        return view('adulto-mayor.index-modal', compact('registros', 'search', 'total', 'colonias', 'nuevosEsteMes', 'promedioEdad'));
    }

    public function create()
    {
        // Obtener colonias excluyendo KINDER y ESCUELA, ordenadas por código
        $colonias = DB::table('colonias')
            ->where('COLONIA', 'NOT LIKE', '%KINDER%')
            ->where('COLONIA', 'NOT LIKE', '%ESCUELA%')
            ->orderByRaw('CAST(COD_COL AS UNSIGNED)')
            ->get();

        return view('adulto-mayor.create', compact('colonias'));
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'expediente'     => 'nullable|string|max:50',
            'nombre_completo'=> 'required|string|max:150',
            'dni'            => 'nullable|string|max:20',
            'edad'           => 'nullable|integer|min:0|max:150',
            'direccion'      => 'nullable|string|max:150',
            'telefono'       => 'nullable|string|max:20',
        ]);

        // Verificar si el DNI ya existe antes de intentar guardar
        if (!empty($data['dni'])) {
            $existe = DatoAdultoMayor::where('dni', $data['dni'])->first();
            if ($existe) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "El DNI {$data['dni']} ya está registrado con el nombre: {$existe->nombre_completo} (Expediente: {$existe->expediente})");
            }
        }

        try {
            DatoAdultoMayor::create($data);
            return redirect()->route('adulto-mayor.index')
                ->with('success', 'Registro creado correctamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            // Capturar error de duplicado por si acaso
            if ($e->getCode() == 23000) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'No se puede guardar el registro: El DNI ya existe en la base de datos.');
            }
            throw $e;
        }
    }

    public function edit(Request $request, DatoAdultoMayor $adultoMayor)
    {
        // Si es petición AJAX, retornar JSON
        if ($request->ajax()) {
            return response()->json($adultoMayor);
        }

        // Obtener colonias excluyendo KINDER y ESCUELA, ordenadas por código
        $colonias = DB::table('colonias')
            ->where('COLONIA', 'NOT LIKE', '%KINDER%')
            ->where('COLONIA', 'NOT LIKE', '%ESCUELA%')
            ->orderByRaw('CAST(COD_COL AS UNSIGNED)')
            ->get();

        return view('adulto-mayor.edit', ['registro' => $adultoMayor, 'colonias' => $colonias]);
    }

    public function update(Request $request, DatoAdultoMayor $adultoMayor)
    {
        $data = $request->validate([
            'expediente'     => 'nullable|string|max:50',
            'nombre_completo'=> 'required|string|max:150',
            'dni'            => 'nullable|string|max:20',
            'edad'           => 'nullable|integer|min:0|max:150',
            'direccion'      => 'nullable|string|max:150',
            'telefono'       => 'nullable|string|max:20',
        ]);

        // Verificar si el DNI ya existe en otro registro
        if (!empty($data['dni']) && $data['dni'] !== $adultoMayor->dni) {
            $existe = DatoAdultoMayor::where('dni', $data['dni'])
                ->where('id', '!=', $adultoMayor->id)
                ->first();
            
            if ($existe) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', "El DNI {$data['dni']} ya está registrado con el nombre: {$existe->nombre_completo} (Expediente: {$existe->expediente})");
            }
        }

        try {
            $adultoMayor->update($data);
            return redirect()->route('adulto-mayor.index')
                ->with('success', 'Registro actualizado correctamente.');
        } catch (\Illuminate\Database\QueryException $e) {
            if ($e->getCode() == 23000) {
                return redirect()->back()
                    ->withInput()
                    ->with('error', 'No se puede actualizar el registro: El DNI ya existe en la base de datos.');
            }
            throw $e;
        }
    }

    public function destroy(DatoAdultoMayor $adultoMayor)
    {
        $adultoMayor->delete();

        return redirect()->route('adulto-mayor.index')
            ->with('success', 'Registro eliminado.');
    }

    /**
     * Actualizar un campo específico mediante AJAX
     */
    public function ajaxUpdate(Request $request, DatoAdultoMayor $adultoMayor)
    {
        $field = $request->input('field');
        $value = $request->input('value');

        // Validar que el campo es permitido
        $allowedFields = ['expediente', 'nombre_completo', 'dni', 'edad', 'direccion', 'telefono'];
        
        if (!in_array($field, $allowedFields)) {
            return response()->json(['success' => false, 'message' => 'Campo no permitido'], 400);
        }

        try {
            $adultoMayor->update([$field => $value]);
            return response()->json(['success' => true, 'message' => 'Campo actualizado']);
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Verifica si un DNI ya existe en la base de datos (validación en tiempo real)
     */
    public function checkDni(Request $request)
    {
        $dni = $request->query('dni');
        if (!$dni) {
            return response()->json(['exists' => false]);
        }

        $registro = DatoAdultoMayor::where('dni', $dni)->first();
        
        if ($registro) {
            return response()->json([
                'exists' => true,
                'nombre' => $registro->nombre_completo,
                'expediente' => $registro->expediente
            ]);
        }

        return response()->json(['exists' => false]);
    }
}
