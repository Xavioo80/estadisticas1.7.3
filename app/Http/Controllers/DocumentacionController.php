<?php

namespace App\Http\Controllers;

use App\Models\Documentacion;
use App\Models\CategoriaDocumentacion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class DocumentacionController extends Controller
{
    public function index(Request $request)
    {
        // Solo carpetas raíz (sin padre)
        $categorias = CategoriaDocumentacion::with(['documentos', 'subcarpetas'])
            ->whereNull('parent_id')
            ->get();

        $documentosSinCategoria = Documentacion::whereNull('categoria_id')->latest()->get();

        $categoriaActual = null;
        $breadcrumb = [];

        if ($request->has('categoria')) {
            $categoriaActual = CategoriaDocumentacion::with(['documentos', 'subcarpetas', 'parent.parent'])
                ->findOrFail($request->categoria);

            $breadcrumb = $this->buildBreadcrumb($categoriaActual);
        }

        return view('documentacion.index', compact('categorias', 'documentosSinCategoria', 'categoriaActual', 'breadcrumb'));
    }

    private function buildBreadcrumb($categoria)
    {
        $trail = [];
        $current = $categoria;
        while ($current) {
            array_unshift($trail, $current);
            $current = $current->parent;
        }
        return $trail;
    }

    public function store(Request $request)
    {
        $request->validate([
            'archivos'        => 'required|array',
            'archivos.*'      => 'file|max:10240',
            'descripcion'     => 'nullable|string|max:255',
            'categoria_id'    => 'nullable|exists:categorias_documentacion,id',
            'nueva_categoria' => 'nullable|string|max:255',
        ]);

        $categoriaId = $request->categoria_id;

        if ($request->filled('nueva_categoria')) {
            $nuevaCat = CategoriaDocumentacion::firstOrCreate(['nombre' => $request->nueva_categoria]);
            $categoriaId = $nuevaCat->id;
        }

        if ($request->hasFile('archivos')) {
            foreach ($request->file('archivos') as $file) {
                $originalName = $file->getClientOriginalName();
                $extension    = $file->getClientOriginalExtension();
                $size         = $file->getSize();
                $fileName     = Str::uuid() . '.' . $extension;
                $path         = $file->storeAs('documentacion', $fileName, 'public');

                Documentacion::create([
                    'nombre_original' => $originalName,
                    'nombre_archivo'  => $fileName,
                    'ruta'            => $path,
                    'extension'       => $extension,
                    'tamano'          => $size,
                    'descripcion'     => $request->descripcion,
                    'categoria_id'    => $categoriaId,
                ]);
            }

            return redirect()->back()->with('success', 'Archivos subidos correctamente.');
        }

        return redirect()->back()->with('error', 'Error al subir los archivos.');
    }

    public function storeFolder(Request $request)
    {
        $request->validate([
            'nombre'    => 'required|string|max:255',
            'color'     => 'nullable|string|max:255',
            'parent_id' => 'nullable|exists:categorias_documentacion,id',
        ]);

        CategoriaDocumentacion::create([
            'nombre'    => $request->nombre,
            'color'     => $request->color ?? 'primary',
            'parent_id' => $request->parent_id ?: null,
        ]);

        return redirect()->back()->with('success', 'Carpeta creada correctamente.');
    }

    public function updateFolder(Request $request, $id)
    {
        $request->validate([
            'nombre' => 'required|string|max:255',
            'color'  => 'nullable|string|max:255',
        ]);

        $categoria = CategoriaDocumentacion::findOrFail($id);
        $categoria->update([
            'nombre' => $request->nombre,
            'color'  => $request->color ?? $categoria->color,
        ]);

        return redirect()->back()->with('success', 'Carpeta actualizada correctamente.');
    }

    public function destroyFolder($id)
    {
        $categoria = CategoriaDocumentacion::withCount('documentos')->findOrFail($id);

        if ($categoria->documentos_count > 0) {
            return redirect()->back()->with('error', 'No se puede eliminar una carpeta que contiene archivos.');
        }

        $parentId = $categoria->parent_id;
        $categoria->delete();

        if ($parentId) {
            return redirect()->route('documentacion.index', ['categoria' => $parentId])
                ->with('success', 'Subcarpeta eliminada correctamente.');
        }

        return redirect()->route('documentacion.index')->with('success', 'Carpeta eliminada correctamente.');
    }

    public function view($id)
    {
        $documento = Documentacion::findOrFail($id);
        $path = storage_path('app/public/' . $documento->ruta);

        if (!file_exists($path)) {
            return redirect()->back()->with('error', 'El archivo no existe físicamente.');
        }

        return response()->file($path, [
            'Content-Disposition' => 'inline; filename="' . $documento->nombre_original . '"'
        ]);
    }

    public function download($id)
    {
        $documento = Documentacion::findOrFail($id);
        $path = storage_path('app/public/' . $documento->ruta);

        if (!file_exists($path)) {
            return redirect()->back()->with('error', 'El archivo no existe físicamente.');
        }

        return response()->download($path, $documento->nombre_original);
    }

    public function destroy($id)
    {
        $documento = Documentacion::findOrFail($id);

        if (Storage::disk('public')->exists($documento->ruta)) {
            Storage::disk('public')->delete($documento->ruta);
        }

        $documento->delete();

        return redirect()->back()->with('success', 'Archivo eliminado correctamente.');
    }
}
