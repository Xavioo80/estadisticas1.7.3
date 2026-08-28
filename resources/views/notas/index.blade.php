@extends('layouts.app')

@section('title', 'Bloc de Notas & Gestor de Tareas - Estadísticas 1.7')

@section('content')
<div class="notas-page-container d-flex flex-column h-100" style="padding: 1rem 1.25rem; gap: 1rem; overflow-y: auto; max-height: calc(100vh - 75px);">

    <!-- Header Principal -->
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 p-3 rounded"
        style="background: var(--bg-surface); border: 1px solid var(--border-color); box-shadow: var(--shadow-sm); border-radius: var(--radius-md, 10px);">
        <div class="d-flex align-items-center gap-2.5">
            <div class="d-flex align-items-center justify-content-center"
                style="width: 40px; height: 40px; border-radius: var(--radius-md, 8px); background: rgba(79, 70, 229, 0.12); color: #6366f1;">
                <i class="bi bi-journal-check" style="font-size: 1.35rem;"></i>
            </div>
            <div>
                <h4 class="mb-0 font-weight-bold" style="color: var(--text-primary); font-size: 1.15rem;">
                    Bloc de Notas, Mensajes & Gestor de Tareas
                </h4>
                <p class="mb-0 text-muted" style="font-size: 0.76rem;">
                    Organización de anotaciones, listas de verificación con capturas y control de actividades
                </p>
            </div>
        </div>

        <!-- Botones de Acción Rápida -->
        <div class="d-flex align-items-center gap-2 flex-wrap">
            <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1.5" onclick="openModalNuevaNota()" style="border-radius: var(--radius-sm, 6px); font-size: 0.8rem; padding: 0.4rem 0.85rem;">
                <i class="bi bi-plus-circle-fill"></i>
                <span>Nueva Nota / Mensaje</span>
            </button>
            <button type="button" class="btn btn-sm btn-subtle-primary d-inline-flex align-items-center gap-1.5" onclick="openModalNuevaTarea()" style="border-radius: var(--radius-sm, 6px); font-size: 0.8rem; padding: 0.4rem 0.85rem;">
                <i class="bi bi-check2-square"></i>
                <span>Nueva Tarea</span>
            </button>
        </div>
    </div>

    <!-- Pestañas de Navegación (Tabs) -->
    <ul class="nav nav-tabs border-0" id="notasTareasTab" role="tablist" style="gap: 6px;">
        <li class="nav-item" role="presentation">
            <button class="nav-link active font-weight-bold d-inline-flex align-items-center gap-2 px-3 py-2" id="tab-notas-btn" data-toggle="tab" data-target="#tab-notas" type="button" role="tab"
                style="border-radius: var(--radius-sm, 6px); font-size: 0.84rem; background: var(--bg-surface); border: 1px solid var(--border-color); color: var(--text-primary);">
                <i class="bi bi-stickies-fill text-warning"></i>
                <span>Bloc de Notas & Mensajes</span>
                <span class="badge badge-subtle-primary ml-1" style="font-size: 0.72rem;">{{ $notas->count() }}</span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link font-weight-bold d-inline-flex align-items-center gap-2 px-3 py-2" id="tab-tareas-btn" data-toggle="tab" data-target="#tab-tareas" type="button" role="tab"
                style="border-radius: var(--radius-sm, 6px); font-size: 0.84rem; background: var(--bg-surface); border: 1px solid var(--border-color); color: var(--text-primary);">
                <i class="bi bi-list-task text-primary"></i>
                <span>Gestor de Tareas (Tabla)</span>
                <span class="badge badge-subtle-info ml-1" style="font-size: 0.72rem;">{{ $tareas->count() }}</span>
            </button>
        </li>
    </ul>

    <!-- Contenido de las Pestañas -->
    <div class="tab-content flex-grow-1" id="notasTareasTabContent">

        <!-- ==================================================================== -->
        <!-- PESTAÑA 1: BLOC DE NOTAS & MENSAJES                                  -->
        <!-- ==================================================================== -->
        <div class="tab-pane fade show active" id="tab-notas" role="tabpanel">
            <div class="d-flex flex-column gap-3">

                <!-- Barra de Filtros de Notas -->
                <div class="d-flex flex-wrap align-items-center justify-content-between gap-2 p-2.5 rounded"
                    style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md, 8px);">
                    
                    <!-- Filtros por Etiqueta -->
                    <div class="d-flex align-items-center gap-1.5 flex-wrap">
                        <span class="text-muted font-weight-semibold mr-1" style="font-size: 0.76rem;">Etiqueta:</span>
                        <a href="{{ route('notas.index', array_merge(request()->except('tag'), ['tag' => 'all'])) }}"
                            class="badge px-2 py-1.5 {{ (!$tag || $tag === 'all') ? 'badge-primary' : 'badge-subtle' }}"
                            style="cursor: pointer; text-decoration: none; font-size: 0.75rem;">
                            Todas
                        </a>
                        @foreach($etiquetas as $etq)
                            <a href="{{ route('notas.index', array_merge(request()->except('tag'), ['tag' => $etq])) }}"
                                class="badge px-2 py-1.5 {{ $tag === $etq ? 'badge-primary' : 'badge-subtle' }}"
                                style="cursor: pointer; text-decoration: none; font-size: 0.75rem;">
                                {{ $etq }}
                            </a>
                        @endforeach
                    </div>

                    <!-- Buscador y Filtro por Tipo -->
                    <div class="d-flex align-items-center gap-2">
                        <select id="filtro-tipo-nota" class="form-control form-control-sm" style="width: 140px; height: 32px; font-size: 0.78rem; background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color);"
                            onchange="filtrarPorTipo(this.value)">
                            <option value="all" {{ (!$type || $type === 'all') ? 'selected' : '' }}>Todos los Tipos</option>
                            <option value="nota" {{ $type === 'nota' ? 'selected' : '' }}>Notas de Texto</option>
                            <option value="checklist" {{ $type === 'checklist' ? 'selected' : '' }}>Checklists</option>
                            <option value="mensaje" {{ $type === 'mensaje' ? 'selected' : '' }}>Mensajes / Capturas</option>
                        </select>

                        <form method="GET" action="{{ route('notas.index') }}" class="position-relative mb-0" style="width: 220px;">
                            <input type="hidden" name="tag" value="{{ $tag }}">
                            <input type="hidden" name="type" value="{{ $type }}">
                            <i class="bi bi-search position-absolute" style="left: 9px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.75rem;"></i>
                            <input type="text" name="search" value="{{ $search }}" placeholder="Buscar en notas..."
                                class="form-control form-control-sm"
                                style="padding-left: 1.8rem; height: 32px; font-size: 0.78rem; background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); border-radius: var(--radius-sm, 6px);">
                            @if($search)
                                <a href="{{ route('notas.index', request()->except('search')) }}" class="position-absolute" style="right: 8px; top: 50%; transform: translateY(-50%); color: var(--text-muted);">
                                    <i class="bi bi-x-circle-fill" style="font-size: 0.8rem;"></i>
                                </a>
                            @endif
                        </form>
                    </div>
                </div>

                <!-- Grid de Notas / Tarjetas -->
                <div class="row row-cols-1 row-cols-md-2 row-cols-lg-3 row-cols-xl-4 g-3" id="notas-grid">
                    @forelse($notas as $nota)
                        <div class="col">
                            <div class="card h-100 position-relative shadow-sm transition-all"
                                style="background: var(--bg-surface); border: 1px solid var(--border-color); border-top: 4px solid {{ $nota->color ?: '#4f46e5' }}; border-radius: var(--radius-md, 8px); overflow: hidden;">
                                
                                <!-- Card Header -->
                                <div class="p-3 pb-2 d-flex align-items-start justify-content-between gap-2">
                                    <div class="d-flex flex-column" style="min-width: 0;">
                                        <div class="d-flex align-items-center gap-1.5 mb-1">
                                            @if($nota->pinned)
                                                <span class="badge badge-warning px-1.5 py-0.5 font-weight-bold" style="font-size: 0.68rem;" title="Nota fijada al tope">
                                                    <i class="bi bi-pin-angle-fill"></i>
                                                </span>
                                            @endif
                                            <span class="badge badge-subtle px-2 py-0.5" style="font-size: 0.68rem; font-weight: 600; color: {{ $nota->color ?: '#4f46e5' }}; border: 1px solid rgba(120,120,120,0.2);">
                                                {{ $nota->etiqueta ?: 'General' }}
                                            </span>
                                            @if($nota->tipo === 'checklist')
                                                <span class="badge badge-subtle-info px-1.5 py-0.5" style="font-size: 0.68rem;">
                                                    <i class="bi bi-check2-square"></i> Checklist
                                                </span>
                                            @elseif($nota->tipo === 'mensaje')
                                                <span class="badge badge-subtle-success px-1.5 py-0.5" style="font-size: 0.68rem;">
                                                    <i class="bi bi-chat-left-text"></i> Mensaje
                                                </span>
                                            @endif
                                        </div>
                                        <h6 class="font-weight-bold mb-0 text-truncate" style="color: var(--text-primary); font-size: 0.88rem;" title="{{ $nota->titulo }}">
                                            {{ $nota->titulo ?: 'Sin título' }}
                                        </h6>
                                    </div>

                                    <!-- Menú de Acciones de Nota -->
                                    <div class="d-flex align-items-center gap-1">
                                        <button type="button" class="btn btn-icon btn-sm btn-subtle" onclick="editarNota({{ json_encode($nota) }})" title="Editar nota" style="width: 26px; height: 26px; padding: 0;">
                                            <i class="bi bi-pencil" style="font-size: 0.75rem;"></i>
                                        </button>
                                        <button type="button" class="btn btn-icon btn-sm btn-subtle-danger" onclick="eliminarNota({{ $nota->id }})" title="Eliminar nota" style="width: 26px; height: 26px; padding: 0;">
                                            <i class="bi bi-trash3" style="font-size: 0.75rem;"></i>
                                        </button>
                                    </div>
                                </div>

                                <!-- Card Body -->
                                <div class="p-3 pt-1 flex-grow-1 d-flex flex-column justify-content-between">
                                    
                                    <!-- Contenido / Checklist / Captura -->
                                    <div>
                                        @if($nota->tipo === 'checklist' && is_array($nota->checklist_items) && count($nota->checklist_items) > 0)
                                            <div class="checklist-container d-flex flex-column gap-1.5 my-2">
                                                @foreach($nota->checklist_items as $cIdx => $item)
                                                    <label class="d-flex align-items-center gap-2 mb-0 p-1 rounded" style="cursor: pointer; background: var(--bg-subtle); font-size: 0.78rem;">
                                                        <input type="checkbox" class="form-check-input mt-0 checklist-check"
                                                            data-nota-id="{{ $nota->id }}" data-index="{{ $cIdx }}"
                                                            {{ !empty($item['done']) ? 'checked' : '' }}
                                                            onchange="toggleChecklistItem({{ $nota->id }}, {{ $cIdx }})">
                                                        <span class="checklist-text {{ !empty($item['done']) ? 'text-muted text-decoration-line-through' : '' }}" style="color: var(--text-primary); word-break: break-word;">
                                                            {{ $item['text'] ?? '' }}
                                                        </span>
                                                    </label>
                                                @endforeach
                                            </div>
                                        @elseif($nota->contenido)
                                            <p class="mb-2" style="font-size: 0.8rem; color: var(--text-secondary); white-space: pre-wrap; word-break: break-word;">{{ $nota->contenido }}</p>
                                        @endif

                                        <!-- Captura de Pantalla Adjunta -->
                                        @if($nota->captura_url)
                                            <div class="mt-2 position-relative rounded overflow-hidden" style="border: 1px solid var(--border-color); background: var(--bg-subtle);">
                                                <img src="{{ $nota->captura_url }}" alt="Captura" class="img-fluid w-100" style="max-height: 180px; object-fit: cover; cursor: pointer;"
                                                    onclick="verImagenCompleta('{{ $nota->captura_url }}', '{{ addslashes($nota->titulo) }}')">
                                                <div class="position-absolute bottom-0 right-0 p-1" style="background: rgba(0,0,0,0.6); border-top-left-radius: 4px;">
                                                    <i class="bi bi-zoom-in text-white" style="font-size: 0.75rem;"></i>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Footer de la Tarjeta (Usuario asignado + Fecha) -->
                                    <div class="d-flex align-items-center justify-content-between pt-2 mt-2" style="border-top: 1px dashed var(--border-color); font-size: 0.72rem; color: var(--text-muted);">
                                        <div class="d-flex align-items-center gap-1 text-truncate" style="max-width: 60%;">
                                            @if($nota->assigned_user_name)
                                                <i class="bi bi-person-fill text-primary"></i>
                                                <span class="font-weight-semibold text-truncate" title="Asignado a: {{ $nota->assigned_user_name }}">
                                                    {{ $nota->assigned_user_name }}
                                                </span>
                                            @else
                                                <i class="bi bi-person text-muted"></i>
                                                <span>General</span>
                                            @endif
                                        </div>
                                        <span>{{ $nota->created_at->format('d/m H:i') }}</span>
                                    </div>

                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12 py-5 text-center" style="color: var(--text-muted);">
                            <div class="d-flex flex-column align-items-center justify-content-center gap-2">
                                <i class="bi bi-journal-x" style="font-size: 2.5rem; opacity: 0.4;"></i>
                                <span class="font-weight-bold" style="font-size: 0.95rem;">No hay notas o mensajes registrados</span>
                                <button type="button" class="btn btn-sm btn-primary mt-2" onclick="openModalNuevaNota()">
                                    <i class="bi bi-plus-lg mr-1"></i> Crear Primera Nota
                                </button>
                            </div>
                        </div>
                    @endforelse
                </div>

            </div>
        </div>

        <!-- ==================================================================== -->
        <!-- PESTAÑA 2: GESTOR DE TAREAS (SISTEMA DE TABLA)                        -->
        <!-- ==================================================================== -->
        <div class="tab-pane fade" id="tab-tareas" role="tabpanel">
            <div class="d-flex flex-column gap-3">
                
                <!-- Resumen de Métricas de Tareas -->
                <div class="row g-2">
                    <div class="col-6 col-md-3">
                        <div class="p-2.5 rounded d-flex align-items-center justify-content-between" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md, 8px);">
                            <div>
                                <div class="text-muted" style="font-size: 0.72rem; text-transform: uppercase;">Total Tareas</div>
                                <div class="font-weight-bold" style="font-size: 1.25rem; color: var(--text-primary);">{{ $tareas->count() }}</div>
                            </div>
                            <i class="bi bi-list-task text-primary" style="font-size: 1.5rem; opacity: 0.6;"></i>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2.5 rounded d-flex align-items-center justify-content-between" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md, 8px);">
                            <div>
                                <div class="text-muted" style="font-size: 0.72rem; text-transform: uppercase;">Pendientes</div>
                                <div class="font-weight-bold text-warning" style="font-size: 1.25rem;">{{ $tareas->where('estado', 'pendiente')->count() }}</div>
                            </div>
                            <i class="bi bi-clock-history text-warning" style="font-size: 1.5rem; opacity: 0.6;"></i>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2.5 rounded d-flex align-items-center justify-content-between" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md, 8px);">
                            <div>
                                <div class="text-muted" style="font-size: 0.72rem; text-transform: uppercase;">En Progreso</div>
                                <div class="font-weight-bold text-info" style="font-size: 1.25rem;">{{ $tareas->where('estado', 'en_progreso')->count() }}</div>
                            </div>
                            <i class="bi bi-arrow-repeat text-info" style="font-size: 1.5rem; opacity: 0.6;"></i>
                        </div>
                    </div>
                    <div class="col-6 col-md-3">
                        <div class="p-2.5 rounded d-flex align-items-center justify-content-between" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md, 8px);">
                            <div>
                                <div class="text-muted" style="font-size: 0.72rem; text-transform: uppercase;">Completadas</div>
                                <div class="font-weight-bold text-success" style="font-size: 1.25rem;">{{ $tareas->where('estado', 'completada')->count() }}</div>
                            </div>
                            <i class="bi bi-check-circle-fill text-success" style="font-size: 1.5rem; opacity: 0.6;"></i>
                        </div>
                    </div>
                </div>

                <!-- Tabla de Tareas Compacta -->
                <div class="card border-0 shadow-sm overflow-hidden" style="background: var(--bg-surface); border: 1px solid var(--border-color) !important; border-radius: var(--radius-md, 8px);">
                    <div class="table-responsive custom-scrollbar" style="max-height: calc(100vh - 290px); overflow-y: auto;">
                        <table class="table table-hover table-sing mb-0 text-nowrap" style="font-size: 0.75rem; border-collapse: separate; border-spacing: 0;">
                            <thead style="position: sticky; top: 0; z-index: 10; background-color: var(--bg-subtle); border-bottom: 2px solid var(--border-color);">
                                <tr>
                                    <th class="py-2 px-2.5 text-center" style="width: 40px; color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">N°</th>
                                    <th class="py-2 px-2.5 text-center" style="width: 110px; color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">Estado</th>
                                    <th class="py-2 px-3" style="color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">Tarea / Actividad</th>
                                    <th class="py-2 px-3" style="color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">Descripción</th>
                                    <th class="py-2 px-3 text-center" style="width: 100px; color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">Fecha Asig.</th>
                                    <th class="py-2 px-3 text-center" style="width: 100px; color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">Fecha Límite</th>
                                    <th class="py-2 px-3" style="width: 150px; color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">Asignado a</th>
                                    <th class="py-2 px-2.5 text-center" style="width: 90px; color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">Prioridad</th>
                                    <th class="py-2 px-2.5 text-center" style="width: 80px; color: var(--text-muted); font-weight: 700;">Acciones</th>
                                </tr>
                            </thead>
                            <tbody style="color: var(--text-primary);">
                                @forelse($tareas as $idx => $t)
                                    <tr id="tarea-row-{{ $t->id }}" style="border-bottom: 1px solid var(--border-color);">
                                        <td class="py-1.5 px-2 text-center font-monospace text-muted" style="border-right: 1px solid var(--border-color); font-size: 0.73rem;">
                                            {{ $idx + 1 }}
                                        </td>
                                        <!-- Selector rápido de estado -->
                                        <td class="py-1.5 px-2 text-center" style="border-right: 1px solid var(--border-color);">
                                            @if($t->estado === 'completada')
                                                <span class="badge badge-subtle-success px-2 py-1 font-weight-bold" role="button" onclick="cambiarEstadoTareaRapido({{ $t->id }}, 'pendiente')" title="Clic para marcar como pendiente" style="font-size: 0.72rem; cursor: pointer;">
                                                    <i class="bi bi-check2-circle mr-1"></i> Completada
                                                </span>
                                            @elseif($t->estado === 'en_progreso')
                                                <span class="badge badge-subtle-info px-2 py-1 font-weight-bold" role="button" onclick="cambiarEstadoTareaRapido({{ $t->id }}, 'completada')" title="Clic para marcar como completada" style="font-size: 0.72rem; cursor: pointer;">
                                                    <i class="bi bi-arrow-repeat mr-1"></i> En Progreso
                                                </span>
                                            @else
                                                <span class="badge badge-subtle-warning px-2 py-1 font-weight-bold" role="button" onclick="cambiarEstadoTareaRapido({{ $t->id }}, 'en_progreso')" title="Clic para poner en progreso" style="font-size: 0.72rem; cursor: pointer;">
                                                    <i class="bi bi-hourglass-split mr-1"></i> Pendiente
                                                </span>
                                            @endif
                                        </td>
                                        <td class="py-1.5 px-3 font-weight-bold" style="border-right: 1px solid var(--border-color); font-size: 0.76rem; color: {{ $t->estado === 'completada' ? 'var(--text-muted)' : 'var(--text-primary)' }}; {{ $t->estado === 'completada' ? 'text-decoration: line-through;' : '' }}">
                                            {{ $t->titulo }}
                                        </td>
                                        <td class="py-1.5 px-3 text-secondary text-truncate" style="max-width: 250px; border-right: 1px solid var(--border-color); font-size: 0.74rem;" title="{{ $t->descripcion }}">
                                            {{ $t->descripcion ?: '-' }}
                                        </td>
                                        <td class="py-1.5 px-3 text-center text-muted font-monospace" style="border-right: 1px solid var(--border-color); font-size: 0.73rem;">
                                            {{ $t->fecha_asignacion ? $t->fecha_asignacion->format('d/m/Y') : '-' }}
                                        </td>
                                        <td class="py-1.5 px-3 text-center font-monospace" style="border-right: 1px solid var(--border-color); font-size: 0.73rem;">
                                            @if($t->fecha_limite)
                                                @php
                                                    $esVencida = $t->estado !== 'completada' && $t->fecha_limite->isPast();
                                                    $esHoy = $t->estado !== 'completada' && $t->fecha_limite->isToday();
                                                @endphp
                                                <span class="{{ $esVencida ? 'text-danger font-weight-bold' : ($esHoy ? 'text-warning font-weight-bold' : 'text-secondary') }}">
                                                    {{ $t->fecha_limite->format('d/m/Y') }}
                                                    @if($esVencida) <i class="bi bi-exclamation-circle-fill ml-0.5" title="Vencida"></i> @endif
                                                </span>
                                            @else
                                                <span class="text-muted">-</span>
                                            @endif
                                        </td>
                                        <td class="py-1.5 px-3 font-weight-semibold" style="border-right: 1px solid var(--border-color); font-size: 0.74rem; color: var(--text-primary);">
                                            <i class="bi bi-person text-muted mr-1"></i> {{ $t->assigned_to ?: 'Sin asignar' }}
                                        </td>
                                        <td class="py-1.5 px-2 text-center" style="border-right: 1px solid var(--border-color);">
                                            @if($t->prioridad === 'alta')
                                                <span class="badge badge-subtle-danger px-2 py-0.5" style="font-size: 0.7rem;">Alta</span>
                                            @elseif($t->prioridad === 'media')
                                                <span class="badge badge-subtle-warning px-2 py-0.5" style="font-size: 0.7rem;">Media</span>
                                            @else
                                                <span class="badge badge-subtle-success px-2 py-0.5" style="font-size: 0.7rem;">Baja</span>
                                            @endif
                                        </td>
                                        <!-- Acciones -->
                                        <td class="py-1.5 px-2 text-center">
                                            <div class="d-inline-flex align-items-center gap-1">
                                                <button type="button" class="btn btn-icon btn-sm btn-subtle" onclick="editarTarea({{ json_encode($t) }})" title="Editar tarea" style="width: 24px; height: 24px; padding: 0;">
                                                    <i class="bi bi-pencil" style="font-size: 0.75rem;"></i>
                                                </button>
                                                <button type="button" class="btn btn-icon btn-sm btn-subtle-danger" onclick="eliminarTarea({{ $t->id }})" title="Eliminar tarea" style="width: 24px; height: 24px; padding: 0;">
                                                    <i class="bi bi-trash3" style="font-size: 0.75rem;"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="9" class="py-5 text-center text-muted">
                                            <div class="d-flex flex-column align-items-center justify-content-center gap-2">
                                                <i class="bi bi-clipboard-check" style="font-size: 2.5rem; opacity: 0.4;"></i>
                                                <span class="font-weight-bold" style="font-size: 0.95rem;">No hay tareas registradas</span>
                                                <button type="button" class="btn btn-sm btn-primary mt-2" onclick="openModalNuevaTarea()">
                                                    <i class="bi bi-plus-lg mr-1"></i> Crear Primera Tarea
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

            </div>
        </div>

    </div>

</div>

<!-- ==================================================================== -->
<!-- MODAL: CREAR / EDITAR NOTA O MENSAJE                                 -->
<!-- ==================================================================== -->
<div class="modal fade" id="modalNota" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="background: var(--bg-surface); border: 1px solid var(--border-color); color: var(--text-primary); border-radius: var(--radius-lg, 12px); box-shadow: var(--shadow-xl);">
            <div class="modal-header py-2.5 px-3 d-flex align-items-center justify-content-between" style="background: var(--bg-subtle); border-bottom: 1px solid var(--border-color);">
                <h5 class="modal-title font-weight-bold mb-0 d-flex align-items-center gap-2" id="modalNotaTitle" style="font-size: 0.98rem; color: var(--text-primary);">
                    <i class="bi bi-journal-plus text-primary"></i> <span>Nueva Nota o Mensaje</span>
                </h5>
                <button type="button" class="close text-muted" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="background: none; border: none; font-size: 1.25rem; color: var(--text-primary);">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <form id="formNota" enctype="multipart/form-data">
                @csrf
                <input type="hidden" id="nota_id" name="id" value="">
                <input type="hidden" id="nota_captura_base64" name="captura_base64" value="">

                <div class="modal-body p-3 d-flex flex-column gap-3">
                    
                    <!-- Fila 1: Título + Tipo + Etiqueta -->
                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label font-weight-semibold mb-1" style="font-size: 0.78rem;">Título de la Nota / Mensaje:</label>
                            <input type="text" id="nota_titulo" name="titulo" class="form-control form-control-sm" placeholder="Ej: Revisar pacientes de Sala 2..." required
                                style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); font-size: 0.8rem; height: 34px;">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label font-weight-semibold mb-1" style="font-size: 0.78rem;">Tipo:</label>
                            <select id="nota_tipo" name="tipo" class="form-control form-control-sm" onchange="cambiarTipoNota(this.value)"
                                style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); font-size: 0.8rem; height: 34px;">
                                <option value="nota">Nota de Texto</option>
                                <option value="checklist">Checklist interactivo</option>
                                <option value="mensaje">Mensaje / Comunicación</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label font-weight-semibold mb-1" style="font-size: 0.78rem;">Etiqueta:</label>
                            <input type="text" id="nota_etiqueta" name="etiqueta" list="lista-etiquetas" class="form-control form-control-sm" placeholder="General, Urgente..."
                                style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); font-size: 0.8rem; height: 34px;">
                            <datalist id="lista-etiquetas">
                                @foreach($etiquetas as $etq)
                                    <option value="{{ $etq }}">
                                @endforeach
                            </datalist>
                        </div>
                    </div>

                    <!-- Fila 2: Asignar a Usuario + Color + Fijar -->
                    <div class="row g-2 align-items-center">
                        <div class="col-md-6">
                            <label class="form-label font-weight-semibold mb-1" style="font-size: 0.78rem;">Asignar a Usuario:</label>
                            <select id="nota_assigned_user_id" name="assigned_user_id" class="form-control form-control-sm"
                                style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); font-size: 0.8rem; height: 34px;">
                                <option value="">-- General / Para todos --</option>
                                @foreach($usuarios as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->email }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label font-weight-semibold mb-1" style="font-size: 0.78rem;">Color de Identificación:</label>
                            <div class="d-flex align-items-center gap-1.5" id="color-palette">
                                @foreach(['#4f46e5', '#f59e0b', '#10b981', '#ef4444', '#8b5cf6', '#06b6d4', '#64748b'] as $c)
                                    <button type="button" class="color-dot btn p-0 rounded-circle" data-color="{{ $c }}" onclick="seleccionarColor('{{ $c }}')"
                                        style="width: 22px; height: 22px; background: {{ $c }}; border: 2px solid transparent;"></button>
                                @endforeach
                                <input type="hidden" id="nota_color" name="color" value="#4f46e5">
                            </div>
                        </div>
                        <div class="col-md-2 d-flex align-items-center mt-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="nota_pinned" name="pinned" value="1">
                                <label class="form-check-label font-weight-semibold" for="nota_pinned" style="font-size: 0.78rem; cursor: pointer;">
                                    <i class="bi bi-pin-angle text-warning mr-1"></i> Fijar al tope
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Fila 3: Contenido de Texto (Para Notas normales y Mensajes) -->
                    <div id="seccion-texto-nota">
                        <label class="form-label font-weight-semibold mb-1" style="font-size: 0.78rem;">Contenido / Mensaje:</label>
                        <textarea id="nota_contenido" name="contenido" rows="4" class="form-control form-control-sm" placeholder="Escribe el texto de la nota o mensaje aquí..."
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); font-size: 0.8rem;"></textarea>
                    </div>

                    <!-- Fila 4: Builder de Checklist (Para Tipo Checklist) -->
                    <div id="seccion-checklist-nota" class="d-none">
                        <label class="form-label font-weight-semibold mb-1 d-flex justify-content-between align-items-center" style="font-size: 0.78rem;">
                            <span>Elementos del Checklist:</span>
                            <span class="text-muted font-weight-normal" style="font-size: 0.72rem;">Presiona Enter para agregar nuevo ítem</span>
                        </label>
                        <div id="checklist-builder-list" class="d-flex flex-column gap-1 mb-2">
                            <!-- Items dinámicos -->
                        </div>
                        <div class="input-group input-group-sm">
                            <input type="text" id="nuevo_item_checklist" class="form-control form-control-sm" placeholder="Escribe una tarea y pulsa Enter o Agregar..."
                                style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); font-size: 0.8rem;"
                                onkeydown="if(event.key==='Enter'){event.preventDefault(); agregarItemChecklist();}">
                            <div class="input-group-append">
                                <button type="button" class="btn btn-subtle-primary" onclick="agregarItemChecklist()">
                                    <i class="bi bi-plus-lg"></i> Agregar Ítem
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Fila 5: Pegar Captura de Pantalla (Ctrl+V) o Subir Archivo -->
                    <div>
                        <label class="form-label font-weight-semibold mb-1 d-flex justify-content-between" style="font-size: 0.78rem;">
                            <span>Captura de Pantalla / Imagen Adjunta:</span>
                            <span class="text-primary font-weight-bold" style="font-size: 0.72rem;"><i class="bi bi-clipboard"></i> Puedes pegar con Ctrl + V directamente</span>
                        </label>
                        
                        <div id="paste-dropzone" class="p-3 text-center rounded border-dashed d-flex flex-column align-items-center justify-content-center gap-1"
                            style="background: var(--bg-subtle); border: 2px dashed var(--border-color); border-radius: var(--radius-md, 8px); cursor: pointer; min-height: 85px;"
                            onclick="document.getElementById('nota_captura_file').click()">
                            
                            <i class="bi bi-image text-muted" style="font-size: 1.5rem;"></i>
                            <div class="text-muted font-weight-semibold" style="font-size: 0.78rem;">
                                Haz clic para subir o presiona <kbd style="background: var(--bg-surface); color: var(--text-primary); border: 1px solid var(--border-color);">Ctrl + V</kbd> para pegar captura
                            </div>
                        </div>
                        <input type="file" id="nota_captura_file" name="captura_file" accept="image/*" class="d-none" onchange="handleFileSelect(this)">

                        <!-- Preview de Captura -->
                        <div id="captura-preview-container" class="mt-2 position-relative d-none rounded overflow-hidden" style="max-width: 280px; border: 1px solid var(--border-color);">
                            <img id="captura-preview-img" src="" class="img-fluid w-100" style="max-height: 140px; object-fit: cover;">
                            <button type="button" class="btn btn-sm btn-danger position-absolute top-0 right-0 m-1 p-0.5 rounded-circle" style="width: 22px; height: 22px; line-height: 1;" onclick="quitarCaptura()" title="Quitar captura">
                                <i class="bi bi-x"></i>
                            </button>
                        </div>
                    </div>

                </div>
                <div class="modal-footer py-2.5 px-3 d-flex justify-content-end gap-2" style="background: var(--bg-subtle); border-top: 1px solid var(--border-color);">
                    <button type="button" class="btn btn-sm btn-subtle" data-dismiss="modal" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary font-weight-bold px-3" id="btnGuardarNota">
                        <i class="bi bi-check2 mr-1"></i> Guardar Nota
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================================================================== -->
<!-- MODAL: CREAR / EDITAR TAREA                                          -->
<!-- ==================================================================== -->
<div class="modal fade" id="modalTarea" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 600px;">
        <div class="modal-content" style="background: var(--bg-surface); border: 1px solid var(--border-color); color: var(--text-primary); border-radius: var(--radius-lg, 12px); box-shadow: var(--shadow-xl);">
            <div class="modal-header py-2.5 px-3 d-flex align-items-center justify-content-between" style="background: var(--bg-subtle); border-bottom: 1px solid var(--border-color);">
                <h5 class="modal-title font-weight-bold mb-0 d-flex align-items-center gap-2" id="modalTareaTitle" style="font-size: 0.98rem; color: var(--text-primary);">
                    <i class="bi bi-list-check text-primary"></i> <span>Nueva Tarea</span>
                </h5>
                <button type="button" class="close text-muted" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="background: none; border: none; font-size: 1.25rem; color: var(--text-primary);">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <form id="formTarea">
                @csrf
                <input type="hidden" id="tarea_id" name="id" value="">

                <div class="modal-body p-3 d-flex flex-column gap-2.5">
                    <div>
                        <label class="form-label font-weight-semibold mb-1" style="font-size: 0.78rem;">Título de la Tarea / Actividad:</label>
                        <input type="text" id="tarea_titulo" name="titulo" class="form-control form-control-sm" placeholder="Ej: Generar informe epidemiológico SE 34..." required
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); font-size: 0.8rem; height: 34px;">
                    </div>

                    <div>
                        <label class="form-label font-weight-semibold mb-1" style="font-size: 0.78rem;">Descripción Detallada:</label>
                        <textarea id="tarea_descripcion" name="descripcion" rows="3" class="form-control form-control-sm" placeholder="Detalles de la tarea o notas adicionales..."
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); font-size: 0.8rem;"></textarea>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label font-weight-semibold mb-1" style="font-size: 0.78rem;">Asignado a:</label>
                            <input type="text" id="tarea_assigned_to" name="assigned_to" list="lista-usuarios-tareas" class="form-control form-control-sm" placeholder="Nombre de usuario..."
                                style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); font-size: 0.8rem; height: 34px;">
                            <datalist id="lista-usuarios-tareas">
                                @foreach($usuarios as $u)
                                    <option value="{{ $u->name }}">
                                @endforeach
                            </datalist>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label font-weight-semibold mb-1" style="font-size: 0.78rem;">Prioridad:</label>
                            <select id="tarea_prioridad" name="prioridad" class="form-control form-control-sm"
                                style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); font-size: 0.8rem; height: 34px;">
                                <option value="alta">Alta</option>
                                <option value="media" selected>Media</option>
                                <option value="baja">Baja</option>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label class="form-label font-weight-semibold mb-1" style="font-size: 0.78rem;">Estado:</label>
                            <select id="tarea_estado" name="estado" class="form-control form-control-sm"
                                style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); font-size: 0.8rem; height: 34px;">
                                <option value="pendiente" selected>Pendiente</option>
                                <option value="en_progreso">En Progreso</option>
                                <option value="completada">Completada</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-2">
                        <div class="col-md-6">
                            <label class="form-label font-weight-semibold mb-1" style="font-size: 0.78rem;">Fecha de Asignación:</label>
                            <input type="date" id="tarea_fecha_asignacion" name="fecha_asignacion" value="{{ date('Y-m-d') }}" class="form-control form-control-sm"
                                style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); font-size: 0.8rem; height: 34px;">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label font-weight-semibold mb-1" style="font-size: 0.78rem;">Fecha Límite (Entrega):</label>
                            <input type="date" id="tarea_fecha_limite" name="fecha_limite" class="form-control form-control-sm"
                                style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); font-size: 0.8rem; height: 34px;">
                        </div>
                    </div>
                </div>

                <div class="modal-footer py-2.5 px-3 d-flex justify-content-end gap-2" style="background: var(--bg-subtle); border-top: 1px solid var(--border-color);">
                    <button type="button" class="btn btn-sm btn-subtle" data-dismiss="modal" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary font-weight-bold px-3" id="btnGuardarTarea">
                        <i class="bi bi-check2 mr-1"></i> Guardar Tarea
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ==================================================================== -->
<!-- MODAL: VISOR DE IMÁGENES / CAPTURAS                                  -->
<!-- ==================================================================== -->
<div class="modal fade" id="modalVisorImagen" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1070;">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
        <div class="modal-content" style="background: rgba(15, 23, 42, 0.95); border: 1px solid rgba(255,255,255,0.1); border-radius: 12px;">
            <div class="modal-header py-2 px-3 border-0 d-flex justify-content-between align-items-center">
                <h6 class="modal-title text-white font-weight-bold mb-0" id="visorImagenTitle">Captura Adjunta</h6>
                <button type="button" class="close text-white" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="background: none; border: none; font-size: 1.5rem;">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="modal-body p-2 text-center">
                <img id="visorImagenImg" src="" class="img-fluid rounded" style="max-height: 80vh; object-fit: contain;">
            </div>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

<script>
    let checklistItemsActuales = [];

    // ==========================================
    // NOTAS: MODAL & GESTIÓN
    // ==========================================
    function openModalNuevaNota() {
        $('#formNota')[0].reset();
        $('#nota_id').val('');
        $('#nota_captura_base64').val('');
        $('#modalNotaTitle span').text('Nueva Nota o Mensaje');
        $('#btnGuardarNota span').text('Guardar Nota');
        checklistItemsActuales = [];
        renderChecklistBuilder();
        cambiarTipoNota('nota');
        seleccionarColor('#4f46e5');
        quitarCaptura();
        $('#modalNota').modal('show');
    }

    function editarNota(nota) {
        $('#formNota')[0].reset();
        $('#nota_id').val(nota.id);
        $('#nota_titulo').val(nota.titulo || '');
        $('#nota_tipo').val(nota.tipo || 'nota');
        $('#nota_etiqueta').val(nota.etiqueta || 'General');
        $('#nota_assigned_user_id').val(nota.assigned_user_id || '');
        $('#nota_contenido').val(nota.contenido || '');
        $('#nota_pinned').prop('checked', !!nota.pinned);
        
        seleccionarColor(nota.color || '#4f46e5');
        cambiarTipoNota(nota.tipo || 'nota');

        // Checklist items
        checklistItemsActuales = Array.isArray(nota.checklist_items) ? [...nota.checklist_items] : [];
        renderChecklistBuilder();

        // Captura
        if (nota.captura_url) {
            $('#captura-preview-img').attr('src', nota.captura_url);
            $('#captura-preview-container').removeClass('d-none');
        } else {
            quitarCaptura();
        }

        $('#modalNotaTitle span').text('Editar Nota o Mensaje');
        $('#btnGuardarNota span').text('Actualizar Nota');
        $('#modalNota').modal('show');
    }

    function cambiarTipoNota(tipo) {
        if (tipo === 'checklist') {
            $('#seccion-texto-nota').addClass('d-none');
            $('#seccion-checklist-nota').removeClass('d-none');
        } else {
            $('#seccion-texto-nota').removeClass('d-none');
            $('#seccion-checklist-nota').addClass('d-none');
        }
    }

    function seleccionarColor(color) {
        $('#nota_color').val(color);
        $('.color-dot').each(function() {
            if ($(this).data('color') === color) {
                $(this).css('border', '2px solid #ffffff').css('box-shadow', '0 0 5px ' + color);
            } else {
                $(this).css('border', '2px solid transparent').css('box-shadow', 'none');
            }
        });
    }

    // Builder de Checklist
    function agregarItemChecklist() {
        const text = $('#nuevo_item_checklist').val().trim();
        if (!text) return;
        checklistItemsActuales.push({ text: text, done: false });
        $('#nuevo_item_checklist').val('').focus();
        renderChecklistBuilder();
    }

    function eliminarItemChecklist(index) {
        checklistItemsActuales.splice(index, 1);
        renderChecklistBuilder();
    }

    function renderChecklistBuilder() {
        const $list = $('#checklist-builder-list');
        $list.empty();
        checklistItemsActuales.forEach((item, idx) => {
            $list.append(`
                <div class="d-flex align-items-center justify-content-between p-1.5 rounded" style="background: var(--bg-surface); border: 1px solid var(--border-color); font-size: 0.78rem;">
                    <div class="d-flex align-items-center gap-2">
                        <i class="bi bi-check2 text-muted"></i>
                        <span>${item.text}</span>
                    </div>
                    <button type="button" class="btn btn-icon btn-sm btn-subtle-danger p-0" style="width: 20px; height: 20px;" onclick="eliminarItemChecklist(${idx})">
                        <i class="bi bi-x"></i>
                    </button>
                </div>
            `);
        });
    }

    // Captura de Pantalla: Soporte Ctrl + V
    document.addEventListener('paste', function(e) {
        if (!$('#modalNota').hasClass('show')) return;
        const items = (e.clipboardData || e.originalEvent.clipboardData).items;
        for (let index in items) {
            const item = items[index];
            if (item.kind === 'file' && item.type.includes('image/')) {
                const blob = item.getAsFile();
                const reader = new FileReader();
                reader.onload = function(event) {
                    $('#nota_captura_base64').val(event.target.result);
                    $('#captura-preview-img').attr('src', event.target.result);
                    $('#captura-preview-container').removeClass('d-none');
                };
                reader.readAsDataURL(blob);
            }
        }
    });

    function handleFileSelect(input) {
        if (input.files && input.files[0]) {
            const reader = new FileReader();
            reader.onload = function(e) {
                $('#captura-preview-img').attr('src', e.target.result);
                $('#captura-preview-container').removeClass('d-none');
                $('#nota_captura_base64').val('');
            };
            reader.readAsDataURL(input.files[0]);
        }
    }

    function quitarCaptura() {
        $('#nota_captura_file').val('');
        $('#nota_captura_base64').val('');
        $('#captura-preview-img').attr('src', '');
        $('#captura-preview-container').addClass('d-none');
    }

    function verImagenCompleta(url, titulo) {
        $('#visorImagenTitle').text(titulo || 'Captura de pantalla');
        $('#visorImagenImg').attr('src', url);
        $('#modalVisorImagen').modal('show');
    }

    // Submit Formulario Nota
    $('#formNota').on('submit', function(e) {
        e.preventDefault();
        const id = $('#nota_id').val();
        const url = id ? `/notas/${id}` : '/notas';
        const method = id ? 'PUT' : 'POST';

        const formData = new FormData(this);
        if (id) formData.append('_method', 'PUT');

        if ($('#nota_tipo').val() === 'checklist') {
            formData.append('checklist_items', JSON.stringify(checklistItemsActuales));
        }

        $('#btnGuardarNota').prop('disabled', true).html('<span class="spinner-border spinner-border-sm mr-1"></span> Guardando...');

        $.ajax({
            url: url,
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function() {
                $('#modalNota').modal('hide');
                location.reload();
            },
            error: function(xhr) {
                $('#btnGuardarNota').prop('disabled', false).html('<i class="bi bi-check2 mr-1"></i> Guardar Nota');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Error al guardar la nota.'
                });
            }
        });
    });

    // Alternar ítem de Checklist
    function toggleChecklistItem(notaId, index) {
        $.ajax({
            url: `/notas/${notaId}/toggle-checklist`,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                index: index
            },
            success: function(resp) {
                // UI feedback
            }
        });
    }

    // Eliminar Nota
    function eliminarNota(id) {
        Swal.fire({
            title: '¿Eliminar nota?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/notas/${id}`,
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        _method: 'DELETE'
                    },
                    success: function() {
                        location.reload();
                    }
                });
            }
        });
    }

    function filtrarPorTipo(tipo) {
        const url = new URL(window.location.href);
        if (tipo === 'all') {
            url.searchParams.delete('type');
        } else {
            url.searchParams.set('type', tipo);
        }
        window.location.href = url.toString();
    }

    // ==========================================
    // TAREAS: MODAL & GESTIÓN
    // ==========================================
    function openModalNuevaTarea() {
        $('#formTarea')[0].reset();
        $('#tarea_id').val('');
        $('#tarea_fecha_asignacion').val('{{ date("Y-m-d") }}');
        $('#modalTareaTitle span').text('Nueva Tarea');
        $('#btnGuardarTarea span').text('Guardar Tarea');
        $('#modalTarea').modal('show');
    }

    function editarTarea(tarea) {
        $('#formTarea')[0].reset();
        $('#tarea_id').val(tarea.id);
        $('#tarea_titulo').val(tarea.titulo || '');
        $('#tarea_descripcion').val(tarea.descripcion || '');
        $('#tarea_assigned_to').val(tarea.assigned_to || '');
        $('#tarea_prioridad').val(tarea.prioridad || 'media');
        $('#tarea_estado').val(tarea.estado || 'pendiente');
        $('#tarea_fecha_asignacion').val(tarea.fecha_asignacion ? tarea.fecha_asignacion.substring(0, 10) : '');
        $('#tarea_fecha_limite').val(tarea.fecha_limite ? tarea.fecha_limite.substring(0, 10) : '');
        
        $('#modalTareaTitle span').text('Editar Tarea');
        $('#btnGuardarTarea span').text('Actualizar Tarea');
        $('#modalTarea').modal('show');
    }

    // Submit Formulario Tarea
    $('#formTarea').on('submit', function(e) {
        e.preventDefault();
        const id = $('#tarea_id').val();
        const url = id ? `/notas/tareas/${id}` : '/notas/tareas';
        const method = id ? 'PUT' : 'POST';

        const data = $(this).serialize();

        $('#btnGuardarTarea').prop('disabled', true).html('<span class="spinner-border spinner-border-sm mr-1"></span> Guardando...');

        $.ajax({
            url: url,
            type: method,
            data: data,
            success: function() {
                $('#modalTarea').modal('hide');
                location.reload();
            },
            error: function(xhr) {
                $('#btnGuardarTarea').prop('disabled', false).html('<i class="bi bi-check2 mr-1"></i> Guardar Tarea');
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Error al guardar la tarea.'
                });
            }
        });
    });

    function cambiarEstadoTareaRapido(id, nuevoEstado) {
        $.ajax({
            url: `/notas/tareas/${id}`,
            type: 'PUT',
            data: {
                _token: '{{ csrf_token() }}',
                estado: nuevoEstado
            },
            success: function() {
                location.reload();
            }
        });
    }

    function eliminarTarea(id) {
        Swal.fire({
            title: '¿Eliminar tarea?',
            text: 'Esta acción no se puede deshacer.',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#64748b',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                $.ajax({
                    url: `/notas/tareas/${id}`,
                    type: 'DELETE',
                    data: {
                        _token: '{{ csrf_token() }}'
                    },
                    success: function() {
                        location.reload();
                    }
                });
            }
        });
    }
</script>
@endsection
