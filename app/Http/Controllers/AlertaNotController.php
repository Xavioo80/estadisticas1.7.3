<?php

namespace App\Http\Controllers;

use App\Models\AlertaNotEnlace;
use Illuminate\Http\Request;

class AlertaNotController extends Controller
{
    /**
     * Mostrar la vista con el visor embebido y las pestañas configuradas.
     */
    public function index(Request $request)
    {
        $isAdmin = auth()->check() && (
            (method_exists(auth()->user(), 'isAdmin') && auth()->user()->isAdmin()) ||
            (auth()->user()->role ?? '') === 'administrador'
        );

        // Si es admin, ve todos; si no, solo los activos
        $enlaces = $isAdmin 
            ? AlertaNotEnlace::orderBy('orden', 'asc')->orderBy('id', 'asc')->get()
            : AlertaNotEnlace::where('is_active', true)->orderBy('orden', 'asc')->orderBy('id', 'asc')->get();

        $selectedId = $request->input('tab');
        $selectedEnlace = null;

        if ($selectedId) {
            $selectedEnlace = $enlaces->firstWhere('id', (int)$selectedId);
        }

        if (!$selectedEnlace && $enlaces->isNotEmpty()) {
            $selectedEnlace = $enlaces->first();
        }

        return view('alertas.index', compact('enlaces', 'selectedEnlace', 'isAdmin'));
    }

    /**
     * Guardar un nuevo enlace (Solo Administradores)
     */
    public function store(Request $request)
    {
        if (!$this->verificarAdmin($request)) {
            return response()->json(['success' => false, 'message' => 'Clave de seguridad incorrecta o no autorizado.'], 403);
        }

        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'url' => 'required|url|max:2000',
            'icono' => 'nullable|string|max:100',
            'descripcion' => 'nullable|string|max:500',
            'orden' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['icono'] = $validated['icono'] ?: 'bi bi-globe';
        $validated['orden'] = $validated['orden'] ?? ((AlertaNotEnlace::max('orden') ?? 0) + 1);
        $validated['is_active'] = $request->has('is_active') ? filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN) : true;

        $enlace = AlertaNotEnlace::create($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pestaña/Enlace agregado exitosamente',
                'enlace' => $enlace
            ]);
        }

        return redirect()->route('alertas.index', ['tab' => $enlace->id])
            ->with('success', 'Pestaña agregada exitosamente.');
    }

    /**
     * Actualizar un enlace existente (Solo Administradores o con Clave)
     */
    public function update(Request $request, $id)
    {
        if (!$this->verificarAdmin($request)) {
            return response()->json(['success' => false, 'message' => 'Clave de seguridad incorrecta o no autorizado.'], 403);
        }

        $enlace = AlertaNotEnlace::findOrFail($id);

        $validated = $request->validate([
            'titulo' => 'required|string|max:255',
            'url' => 'required|url|max:2000',
            'icono' => 'nullable|string|max:100',
            'descripcion' => 'nullable|string|max:500',
            'orden' => 'nullable|integer',
            'is_active' => 'nullable|boolean',
        ]);

        $validated['icono'] = $validated['icono'] ?: 'bi bi-globe';
        if ($request->has('is_active')) {
            $validated['is_active'] = filter_var($request->input('is_active'), FILTER_VALIDATE_BOOLEAN);
        }

        $enlace->update($validated);

        if ($request->ajax()) {
            return response()->json([
                'success' => true,
                'message' => 'Pestaña/Enlace actualizado exitosamente',
                'enlace' => $enlace
            ]);
        }

        return redirect()->route('alertas.index', ['tab' => $enlace->id])
            ->with('success', 'Pestaña actualizada exitosamente.');
    }

    /**
     * Eliminar un enlace (Solo Administradores o con Clave)
     */
    public function destroy(Request $request, $id)
    {
        if (!$this->verificarAdmin($request)) {
            return response()->json(['success' => false, 'message' => 'Clave de seguridad incorrecta o no autorizado.'], 403);
        }

        $enlace = AlertaNotEnlace::findOrFail($id);
        $enlace->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pestaña eliminada exitosamente'
        ]);
    }

    /**
     * Helper para verificar rol de administrador o clave de seguridad Distica2026
     */
    private function verificarAdmin(Request $request = null): bool
    {
        if ($request && $request->input('clave') === 'Distica2026') {
            session(['alerta_not_unlocked' => true]);
            return true;
        }

        if (session('alerta_not_unlocked')) {
            return true;
        }

        return auth()->check() && (
            (method_exists(auth()->user(), 'isAdmin') && auth()->user()->isAdmin()) ||
            (auth()->user()->role ?? '') === 'administrador'
        );
    }
}
