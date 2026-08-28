<?php

namespace App\Http\Controllers;

use App\Models\Nota;
use App\Models\Tarea;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class NotaController extends Controller
{
    /**
     * Muestra la vista principal de Bloc de Notas & Gestor de Tareas.
     */
    public function index(Request $request)
    {
        // ── API: Devolver todas las notas para notificaciones y persistencia estática ──
        if ($request->boolean('pinned_only') || ($request->ajax() && $request->has('pinned_only')) || $request->has('all_notes')) {
            $notas = Nota::orderBy('pinned', 'desc')
                ->orderBy('created_at', 'desc')
                ->get(['id', 'titulo', 'contenido', 'color', 'tipo', 'checklist_items', 'pinned', 'updated_at']);
            return response()->json(['notas_pinned' => $notas]);
        }

        $tag = $request->input('tag');
        $type = $request->input('type');
        $search = $request->input('search');

        $query = Nota::with('user', 'assignedUser')->orderBy('pinned', 'desc')->orderBy('created_at', 'desc');

        if ($tag && $tag !== 'all') {
            $query->where('etiqueta', $tag);
        }

        if ($type && $type !== 'all') {
            $query->where('tipo', $type);
        }

        if ($search) {
            $query->where(function ($q) use ($search) {
                $q->where('titulo', 'like', "%{$search}%")
                  ->orWhere('contenido', 'like', "%{$search}%")
                  ->orWhere('assigned_user_name', 'like', "%{$search}%");
            });
        }

        $notas = $query->get();

        // Tareas
        $tareas = Tarea::orderBy('estado', 'asc')
            ->orderByRaw("FIELD(prioridad, 'alta', 'media', 'baja')")
            ->orderBy('fecha_limite', 'asc')
            ->get();

        // Lista de usuarios para asignación
        $usuarios = User::select('id', 'name', 'email')->get();

        // Lista de etiquetas fijas o dinámicas
        $etiquetas = ['General', 'Urgente', 'Recordatorio', 'SESAL', 'Epidemiología', 'Estadísticas'];

        return view('notas.index', compact('notas', 'tareas', 'usuarios', 'etiquetas', 'tag', 'type', 'search'));
    }

    /**
     * Guarda una nueva nota o mensaje.
     */
    public function store(Request $request)
    {
        $request->validate([
            'titulo'    => 'nullable|string|max:255',
            'contenido' => 'nullable|string',
            'tipo'      => 'required|in:nota,checklist,mensaje,lista_numerada,alerta',
            'etiqueta'  => 'nullable|string|max:50',
            'color'     => 'nullable|string|max:20',
        ]);

        $capturaUrl = null;

        // Captura subida como archivo o base64 (pegar con Ctrl+V)
        if ($request->hasFile('captura_file')) {
            $path = $request->file('captura_file')->store('capturas', 'public');
            $capturaUrl = Storage::url($path);
        } elseif ($request->filled('captura_base64')) {
            $base64 = $request->input('captura_base64');
            if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
                $base64Data = substr($base64, strpos($base64, ',') + 1);
                $type = strtolower($type[1]);
                $base64Data = base64_decode($base64Data);

                if ($base64Data !== false) {
                    $fileName = 'capturas/cap_' . time() . '_' . Str::random(8) . '.' . $type;
                    Storage::disk('public')->put($fileName, $base64Data);
                    $capturaUrl = Storage::url($fileName);
                }
            }
        }

        // Checklist items
        $checklistItems = null;
        if ($request->tipo === 'checklist' && $request->has('checklist_items')) {
            $rawItems = $request->input('checklist_items');
            if (is_array($rawItems)) {
                $checklistItems = $rawItems;
            } elseif (is_string($rawItems)) {
                $checklistItems = json_decode($rawItems, true);
            }
        }

        $assignedUserName = null;
        if ($request->filled('assigned_user_id')) {
            $u = User::find($request->assigned_user_id);
            if ($u) {
                $assignedUserName = $u->name;
            }
        } elseif ($request->filled('assigned_user_name')) {
            $assignedUserName = $request->assigned_user_name;
        }

        $nota = Nota::create([
            'user_id' => Auth::id() ?? 1,
            'assigned_user_id' => $request->assigned_user_id ?: null,
            'assigned_user_name' => $assignedUserName,
            'titulo' => $request->titulo ?: 'Sin título',
            'contenido' => $request->contenido,
            'tipo' => $request->tipo,
            'checklist_items' => $checklistItems,
            'etiqueta' => $request->etiqueta ?: 'General',
            'color' => $request->color ?: '#eab308',
            'captura_url' => $capturaUrl,
            'pinned' => true,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'nota' => $nota]);
        }

        return redirect()->route('notas.index')->with('success', 'Nota guardada exitosamente.');
    }

    /**
     * Actualiza una nota.
     */
    public function update(Request $request, $id)
    {
        $nota = Nota::findOrFail($id);

        $capturaUrl = $nota->captura_url;
        if ($request->hasFile('captura_file')) {
            $path = $request->file('captura_file')->store('capturas', 'public');
            $capturaUrl = Storage::url($path);
        } elseif ($request->filled('captura_base64')) {
            $base64 = $request->input('captura_base64');
            if (preg_match('/^data:image\/(\w+);base64,/', $base64, $type)) {
                $base64Data = substr($base64, strpos($base64, ',') + 1);
                $type = strtolower($type[1]);
                $base64Data = base64_decode($base64Data);

                if ($base64Data !== false) {
                    $fileName = 'capturas/cap_' . time() . '_' . Str::random(8) . '.' . $type;
                    Storage::disk('public')->put($fileName, $base64Data);
                    $capturaUrl = Storage::url($fileName);
                }
            }
        } elseif ($request->boolean('delete_captura')) {
            $capturaUrl = null;
        }

        // Checklist items
        $checklistItems = $nota->checklist_items;
        if ($request->has('checklist_items')) {
            $rawItems = $request->input('checklist_items');
            $checklistItems = is_array($rawItems) ? $rawItems : json_decode($rawItems, true);
        }

        $assignedUserName = $nota->assigned_user_name;
        if ($request->filled('assigned_user_id')) {
            $u = User::find($request->assigned_user_id);
            if ($u) $assignedUserName = $u->name;
        }

        $nota->update([
            'titulo' => $request->titulo ?? $nota->titulo,
            'contenido' => $request->has('contenido') ? $request->contenido : $nota->contenido,
            'tipo' => $request->tipo ?? $nota->tipo,
            'checklist_items' => $checklistItems,
            'etiqueta' => $request->etiqueta ?? $nota->etiqueta,
            'color' => $request->color ?? $nota->color,
            'assigned_user_id' => $request->assigned_user_id ?? $nota->assigned_user_id,
            'assigned_user_name' => $assignedUserName,
            'captura_url' => $capturaUrl,
            'pinned' => $request->has('pinned') ? $request->boolean('pinned') : $nota->pinned,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'nota' => $nota]);
        }

        return redirect()->route('notas.index')->with('success', 'Nota actualizada.');
    }

    /**
     * Alternar estado de un ítem de checklist.
     */
    public function toggleChecklist(Request $request, $id)
    {
        $nota = Nota::findOrFail($id);
        $itemIndex = (int) $request->input('index');

        $items = $nota->checklist_items ?? [];
        if (isset($items[$itemIndex])) {
            $items[$itemIndex]['done'] = !$items[$itemIndex]['done'];
            $nota->checklist_items = $items;
            $nota->save();
        }

        return response()->json(['success' => true, 'checklist_items' => $nota->checklist_items]);
    }

    /**
     * Elimina una nota.
     */
    public function destroy($id)
    {
        $nota = Nota::findOrFail($id);
        $nota->delete();

        return response()->json(['success' => true, 'message' => 'Nota eliminada.']);
    }

    /**
     * Agrega un ítem a una nota de lista numerada.
     */
    public function agregarItem(Request $request, $id)
    {
        $nota = Nota::findOrFail($id);
        $texto = $request->input('texto', '');

        $items = $nota->checklist_items ?? [];
        $items[] = ['text' => $texto, 'done' => false];
        $nota->checklist_items = $items;
        $nota->save();

        return response()->json(['success' => true, 'index' => count($items) - 1]);
    }

    // ==========================================
    // SECCIÓN DE TAREAS (SISTEMA TIPO TABLA)
    // ==========================================

    /**
     * Crea una nueva tarea.
     */
    public function storeTarea(Request $request)
    {
        $request->validate([
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'assigned_to' => 'nullable|string|max:150',
            'fecha_asignacion' => 'nullable|date',
            'fecha_limite' => 'nullable|date',
            'prioridad' => 'required|in:alta,media,baja',
            'estado' => 'required|in:pendiente,en_progreso,completada',
        ]);

        $tarea = Tarea::create([
            'user_id' => Auth::id() ?? 1,
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'assigned_to' => $request->assigned_to ?: (Auth::user() ? Auth::user()->name : 'Sin asignar'),
            'fecha_asignacion' => $request->fecha_asignacion ?: now()->format('Y-m-d'),
            'fecha_limite' => $request->fecha_limite,
            'prioridad' => $request->prioridad,
            'estado' => $request->estado,
        ]);

        return response()->json(['success' => true, 'tarea' => $tarea]);
    }

    /**
     * Actualiza una tarea.
     */
    public function updateTarea(Request $request, $id)
    {
        $tarea = Tarea::findOrFail($id);

        $tarea->update($request->only([
            'titulo',
            'descripcion',
            'assigned_to',
            'fecha_asignacion',
            'fecha_limite',
            'prioridad',
            'estado',
        ]));

        return response()->json(['success' => true, 'tarea' => $tarea]);
    }

    /**
     * Elimina una tarea.
     */
    public function destroyTarea($id)
    {
        $tarea = Tarea::findOrFail($id);
        $tarea->delete();

        return response()->json(['success' => true, 'message' => 'Tarea eliminada.']);
    }
}
