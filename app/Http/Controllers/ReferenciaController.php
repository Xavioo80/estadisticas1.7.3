<?php

namespace App\Http\Controllers;

use App\Models\Referencia;
use Illuminate\Http\Request;

class ReferenciaController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $referencias = Referencia::orderBy('nombre')->paginate(15);
        return view('referencias.index', compact('referencias'));
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('referencias.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255|unique:referencias,nombre',
                'tipo' => 'required|string|max:100',
                'direccion' => 'nullable|string|max:500',
                'telefono' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'contacto' => 'nullable|string|max:255'
            ]);

            // Manejar el checkbox de estado correctamente
            $validated['estado'] = $request->has('estado') ? 1 : 0;

            $referencia = Referencia::create($validated);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Referencia agregada correctamente',
                    'referencia' => $referencia
                ]);
            }

            return redirect()->route('referencias.index')->with('success', 'Referencia agregada correctamente');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación: ' . implode(', ', $e->validator->errors()->all()),
                    'errors' => $e->validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al guardar la referencia: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Error al guardar la referencia: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Display the specified resource.
     */
    public function show(Referencia $referencia)
    {
        return view('referencias.show', compact('referencia'));
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Referencia $referencia)
    {
        return view('referencias.edit', compact('referencia'));
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, Referencia $referencia)
    {
        try {
            $validated = $request->validate([
                'nombre' => 'required|string|max:255|unique:referencias,nombre,' . $referencia->id,
                'tipo' => 'required|string|max:100',
                'direccion' => 'nullable|string|max:500',
                'telefono' => 'nullable|string|max:20',
                'email' => 'nullable|email|max:255',
                'contacto' => 'nullable|string|max:255'
            ]);

            // Manejar el checkbox de estado correctamente
            $validated['estado'] = $request->has('estado') ? 1 : 0;

            $referencia->update($validated);

            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Referencia actualizada correctamente',
                    'referencia' => $referencia
                ]);
            }

            return redirect()->route('referencias.index')->with('success', 'Referencia actualizada correctamente');
        } catch (\Illuminate\Validation\ValidationException $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error de validación: ' . implode(', ', $e->validator->errors()->all()),
                    'errors' => $e->validator->errors()
                ], 422);
            }
            return redirect()->back()->withErrors($e->validator)->withInput();
        } catch (\Exception $e) {
            if ($request->ajax() || $request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al actualizar la referencia: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Error al actualizar la referencia: ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Referencia $referencia)
    {
        try {
            $referencia->delete();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => true,
                    'message' => 'Referencia eliminada correctamente'
                ]);
            }

            return redirect()->route('referencias.index')->with('success', 'Referencia eliminada correctamente');
        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Error al eliminar la referencia: ' . $e->getMessage()
                ], 500);
            }
            return redirect()->back()->with('error', 'Error al eliminar la referencia: ' . $e->getMessage());
        }
    }

    /**
     * Get all active references for API/AJAX calls
     */
    public function getActivas()
    {
        $referencias = Referencia::activos()->orderBy('nombre')->get();
        return response()->json($referencias);
    }
}
