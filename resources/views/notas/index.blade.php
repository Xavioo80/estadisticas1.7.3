@extends('layouts.app')

@section('title', 'Notas Rápidas - Estadísticas 1.7')

@push('styles')
<link rel="stylesheet" href="{{ asset('assets/css/sing-sticky-notes.css') }}">
@endpush

@php
function getStickyColorClass($color) {
    $color = strtolower(trim($color ?? ''));
    if (str_contains($color, '22c55e') || str_contains($color, '10b981') || str_contains($color, 'green')) return 'green';
    if (str_contains($color, 'ec4899') || str_contains($color, 'ef4444') || str_contains($color, 'pink')) return 'pink';
    if (str_contains($color, 'a855f7') || str_contains($color, '8b5cf6') || str_contains($color, 'purple')) return 'purple';
    if (str_contains($color, '3b82f6') || str_contains($color, '06b6d4') || str_contains($color, 'blue')) return 'blue';
    if (str_contains($color, '64748b') || str_contains($color, '6c757d') || str_contains($color, 'charcoal') || str_contains($color, 'gray')) return 'charcoal';
    return 'yellow'; // default classic Windows yellow
}
@endphp

@section('content')
<div class="sticky-dashboard-container">

    <!-- Header Principal Estilo Windows Notas Rápidas Hub -->
    <div class="sticky-hub-header">
        <div class="sticky-hub-title">
            <div class="sticky-hub-icon">
                <i class="bi bi-stickies-fill"></i>
            </div>
            <div class="sticky-hub-text">
                <h4>Notas Rápidas</h4>
                <p>Tablero interactivo de notas adhesivas, tareas y recordatorios del sistema</p>
            </div>
        </div>

        <div class="sticky-hub-controls">
            <!-- Buscador en tiempo real -->
            <div class="sticky-search-box">
                <i class="bi bi-search"></i>
                <input type="text" id="sticky-search" class="sticky-search-input" placeholder="Buscar en notas... (Ctrl+F)" oninput="filtrarNotasLive()">
            </div>

            <!-- Selector de Vista (Sticky Notes vs Tareas) -->
            <div class="sticky-view-tabs">
                <button type="button" class="sticky-tab-btn active" id="btn-tab-stickies" onclick="switchMainTab('stickies')">
                    <i class="bi bi-stickies"></i>
                    <span>Notas Rápidas</span>
                    <span class="badge rounded-pill bg-warning text-dark font-weight-bold ml-1" id="badge-total-notas" style="font-size: 0.68rem;">{{ $notas->count() }}</span>
                </button>
                <button type="button" class="sticky-tab-btn" id="btn-tab-tareas" onclick="switchMainTab('tareas')">
                    <i class="bi bi-list-check"></i>
                    <span>Gestor de Tareas</span>
                    <span class="badge rounded-pill bg-primary text-white font-weight-bold ml-1" style="font-size: 0.68rem;">{{ $tareas->count() }}</span>
                </button>
            </div>

            <!-- Selector Desplegable Nueva Nota por Color/Tipo -->
            <div class="dropdown">
                <button type="button" class="btn-new-sticky dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false" style="display:flex;align-items:center;gap:6px;">
                    <i class="bi bi-plus-lg"></i>
                    <span>Nueva Nota</span>
                </button>
                <div class="dropdown-menu dropdown-menu-right p-2 shadow-lg" style="min-width: 250px; border-radius: 10px; background: var(--bg-surface); border: 1px solid var(--border-color); z-index: 1050;">
                    <div class="text-muted font-weight-bold px-2 py-1" style="font-size: 0.70rem; text-transform: uppercase;">Crear por Tipo / Color:</div>
                    <a class="dropdown-item d-flex align-items-center gap-2.5 py-1.5 px-2 rounded" href="javascript:void(0)" onclick="crearNuevaNotaRapida('#eab308')">
                        <span style="width:18px;height:18px;border-radius:50%;background:#eab308;display:inline-block;flex-shrink:0;box-shadow:0 1px 3px rgba(0,0,0,0.2);margin-right:8px;"></span>
                        <div>
                            <div style="font-weight:700;font-size:0.83rem;color:var(--text-primary);">Nota de Texto</div>
                            <small class="text-muted d-block" style="font-size:0.71rem;">Amarillo — Anotación rápida libre</small>
                        </div>
                    </a>
                    <a class="dropdown-item d-flex align-items-center gap-2.5 py-1.5 px-2 rounded" href="javascript:void(0)" onclick="crearNuevaNotaRapida('#22c55e')">
                        <span style="width:18px;height:18px;border-radius:50%;background:#22c55e;display:inline-block;flex-shrink:0;box-shadow:0 1px 3px rgba(0,0,0,0.2);margin-right:8px;"></span>
                        <div>
                            <div style="font-weight:700;font-size:0.83rem;color:var(--text-primary);">Checklist / Tareas ✅</div>
                            <small class="text-muted d-block" style="font-size:0.71rem;">Verde — Casillas con verificación</small>
                        </div>
                    </a>
                    <a class="dropdown-item d-flex align-items-center gap-2.5 py-1.5 px-2 rounded" href="javascript:void(0)" onclick="crearNuevaNotaRapida('#ec4899')">
                        <span style="width:18px;height:18px;border-radius:50%;background:#ec4899;display:inline-block;flex-shrink:0;box-shadow:0 1px 3px rgba(0,0,0,0.2);margin-right:8px;"></span>
                        <div>
                            <div style="font-weight:700;font-size:0.83rem;color:var(--text-primary);">Lista Numerada 🔢</div>
                            <small class="text-muted d-block" style="font-size:0.71rem;">Rosa — Numeración automática (1, 2, 3...)</small>
                        </div>
                    </a>
                    <a class="dropdown-item d-flex align-items-center gap-2.5 py-1.5 px-2 rounded" href="javascript:void(0)" onclick="crearNuevaNotaRapida('#a855f7')">
                        <span style="width:18px;height:18px;border-radius:50%;background:#a855f7;display:inline-block;flex-shrink:0;box-shadow:0 1px 3px rgba(0,0,0,0.2);margin-right:8px;"></span>
                        <div>
                            <div style="font-weight:700;font-size:0.83rem;color:var(--text-primary);">Mensaje / Alerta 📢</div>
                            <small class="text-muted d-block" style="font-size:0.71rem;">Púrpura — Flotante enfrente de pantalla</small>
                        </div>
                    </a>
                    <div class="dropdown-divider my-1"></div>
                    <div class="d-flex align-items-center justify-content-between px-2 py-1">
                        <span class="text-muted" style="font-size:0.72rem;">Otros colores:</span>
                        <div class="d-flex gap-2">
                            <span onclick="crearNuevaNotaRapida('#3b82f6')" title="Azul (Texto)" style="width:18px;height:18px;border-radius:50%;background:#3b82f6;cursor:pointer;display:inline-block;margin-right:4px;"></span>
                            <span onclick="crearNuevaNotaRapida('#64748b')" title="Carbón (Texto)" style="width:18px;height:18px;border-radius:50%;background:#64748b;cursor:pointer;display:inline-block;"></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- SECCIÓN 1: TABLERO DE STICKY NOTES -->
    <div id="seccion-stickies" class="d-flex flex-column gap-3">
        
        <!-- Barra de Filtros Rápida (Paleta Circular + Etiquetas) -->
        <div class="sticky-filter-bar">
            <!-- Filtro por Color -->
            <div class="d-flex align-items-center gap-2">
                <span class="text-muted font-weight-semibold" style="font-size: 0.76rem;">Color:</span>
                <div class="sticky-color-pills">
                    <div class="color-filter-dot bg-sticky-yellow active" data-color-filter="all" onclick="filtrarPorColor('all', this)" title="Todos los colores" style="background: linear-gradient(135deg, #eab308 50%, #22c55e 50%);"></div>
                    <div class="color-filter-dot bg-sticky-yellow" data-color-filter="yellow" onclick="filtrarPorColor('yellow', this)" title="Amarillo"></div>
                    <div class="color-filter-dot bg-sticky-green" data-color-filter="green" onclick="filtrarPorColor('green', this)" title="Verde"></div>
                    <div class="color-filter-dot bg-sticky-pink" data-color-filter="pink" onclick="filtrarPorColor('pink', this)" title="Rosa"></div>
                    <div class="color-filter-dot bg-sticky-purple" data-color-filter="purple" onclick="filtrarPorColor('purple', this)" title="Púrpura"></div>
                    <div class="color-filter-dot bg-sticky-blue" data-color-filter="blue" onclick="filtrarPorColor('blue', this)" title="Azul"></div>
                    <div class="color-filter-dot bg-sticky-charcoal" data-color-filter="charcoal" onclick="filtrarPorColor('charcoal', this)" title="Carbón"></div>
                </div>
            </div>

            <!-- Filtro por Etiquetas -->
            <div class="sticky-tags-list">
                <span class="text-muted font-weight-semibold mr-1" style="font-size: 0.76rem;">Etiqueta:</span>
                <span class="sticky-tag-pill active" data-tag="all" onclick="filtrarPorEtiqueta('all', this)">Todas</span>
                @foreach($etiquetas as $etq)
                    <span class="sticky-tag-pill" data-tag="{{ strtolower($etq) }}" onclick="filtrarPorEtiqueta('{{ strtolower($etq) }}', this)">{{ $etq }}</span>
                @endforeach
            </div>

            <!-- Crear rápida con 1 clic por color/tipo -->
            <div class="d-none d-lg-flex align-items-center gap-1.5 ml-auto">
                <span class="text-muted font-weight-semibold mr-1" style="font-size: 0.72rem;">Crear:</span>
                <button type="button" class="btn btn-sm btn-subtle d-inline-flex align-items-center gap-1 py-0.5 px-2" onclick="crearNuevaNotaRapida('#eab308')" title="Crear Nota Amarilla de Texto Libre" style="font-size: 0.72rem; border-radius: 6px;">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #eab308; display: inline-block;"></span>
                    <span>Texto</span>
                </button>
                <button type="button" class="btn btn-sm btn-subtle d-inline-flex align-items-center gap-1 py-0.5 px-2" onclick="crearNuevaNotaRapida('#22c55e')" title="Crear Checklist Verde de Tareas" style="font-size: 0.72rem; border-radius: 6px;">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #22c55e; display: inline-block;"></span>
                    <span>Checklist</span>
                </button>
                <button type="button" class="btn btn-sm btn-subtle d-inline-flex align-items-center gap-1 py-0.5 px-2" onclick="crearNuevaNotaRapida('#ec4899')" title="Crear Lista Numerada Rosa" style="font-size: 0.72rem; border-radius: 6px;">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #ec4899; display: inline-block;"></span>
                    <span>Numerada</span>
                </button>
                <button type="button" class="btn btn-sm btn-subtle d-inline-flex align-items-center gap-1 py-0.5 px-2" onclick="crearNuevaNotaRapida('#a855f7')" title="Crear Mensaje / Alerta Púrpura" style="font-size: 0.72rem; border-radius: 6px;">
                    <span style="width: 8px; height: 8px; border-radius: 50%; background: #a855f7; display: inline-block;"></span>
                    <span>Alerta</span>
                </button>
            </div>
        </div>

        <!-- Tablero Grid de Sticky Notes -->
        <div class="sticky-board-grid" id="sticky-board-grid">
            @forelse($notas as $nota)
                @php
                    $colorClass = getStickyColorClass($nota->color);
                @endphp
                <div class="sticky-note-card sticky-{{ $colorClass }} {{ $nota->pinned ? 'is-pinned' : '' }} {{ $nota->tipo === 'alerta' ? 'is-alerta' : '' }}"
                    id="sticky-note-{{ $nota->id }}"
                    data-id="{{ $nota->id }}"
                    data-color-class="{{ $colorClass }}"
                    data-color="{{ $nota->color ?: '#eab308' }}"
                    data-tag="{{ strtolower($nota->etiqueta ?: 'general') }}"
                    data-tipo="{{ $nota->tipo }}">

                    <!-- Top Header Bar (Windows Titlebar) -->
                    <div class="sticky-header">
                        <button type="button" class="sticky-btn-add" onclick="crearNuevaNotaRapida('{{ $nota->color ?: '#eab308' }}')" title="Nueva nota del mismo tipo (+)">
                            <i class="bi bi-plus-lg"></i>
                        </button>

                        <div class="sticky-title-container">
                            <input type="text" class="sticky-title-input" value="{{ $nota->titulo }}" placeholder="Título de nota..." onchange="actualizarTituloNota({{ $nota->id }}, this.value)">
                        </div>

                        <div class="sticky-header-actions">
                            <!-- Botón Chincheta Directo (Fijar enfrente de pantalla) -->
                            <button type="button" class="sticky-btn-pin {{ $nota->pinned ? 'is-pinned' : '' }}" id="pin-btn-{{ $nota->id }}" onclick="togglePinNota({{ $nota->id }})" title="{{ $nota->pinned ? 'Desfijar de la pantalla' : 'Fijar enfrente de la pantalla' }}">
                                <i class="bi {{ $nota->pinned ? 'bi-pin-angle-fill' : 'bi-pin-angle' }}" id="pin-icon-{{ $nota->id }}"></i>
                            </button>

                            <!-- Menú 3 puntos (Paleta circular y opciones) -->
                            <div class="dropdown">
                                <button type="button" class="sticky-btn-menu" data-toggle="dropdown" data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false" title="Color y Opciones">
                                    <i class="bi bi-three-dots"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right sticky-color-menu shadow-lg">
                                    <div class="text-muted font-weight-bold mb-1" style="font-size: 0.70rem; text-transform: uppercase;">Color / Tipo:</div>
                                    <div class="sticky-palette-grid">
                                        <span class="color-circle bg-sticky-yellow {{ $colorClass === 'yellow' ? 'active' : '' }}" onclick="cambiarColorNota({{ $nota->id }}, '#eab308', 'yellow')" title="Amarillo — Texto libre"></span>
                                        <span class="color-circle bg-sticky-green {{ $colorClass === 'green' ? 'active' : '' }}" onclick="cambiarColorNota({{ $nota->id }}, '#22c55e', 'green')" title="Verde — Checklist tareas"></span>
                                        <span class="color-circle bg-sticky-pink {{ $colorClass === 'pink' ? 'active' : '' }}" onclick="cambiarColorNota({{ $nota->id }}, '#ec4899', 'pink')" title="Rosa — Lista numerada"></span>
                                        <span class="color-circle bg-sticky-purple {{ $colorClass === 'purple' ? 'active' : '' }}" onclick="cambiarColorNota({{ $nota->id }}, '#a855f7', 'purple')" title="Púrpura — Mensaje/Alerta enfrente"></span>
                                        <span class="color-circle bg-sticky-blue {{ $colorClass === 'blue' ? 'active' : '' }}" onclick="cambiarColorNota({{ $nota->id }}, '#3b82f6', 'blue')" title="Azul — Nota libre"></span>
                                        <span class="color-circle bg-sticky-charcoal {{ $colorClass === 'charcoal' ? 'active' : '' }}" onclick="cambiarColorNota({{ $nota->id }}, '#64748b', 'charcoal')" title="Carbón — Nota libre"></span>
                                    </div>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item py-1 px-2 font-weight-semibold" href="javascript:void(0)" onclick="togglePinNota({{ $nota->id }})" style="font-size: 0.78rem;">
                                        <i class="bi bi-pin-angle mr-1.5 text-warning"></i>
                                        <span id="pin-text-{{ $nota->id }}">{{ $nota->pinned ? 'Desfijar de arriba' : 'Fijar arriba' }}</span>
                                    </a>
                                    <a class="dropdown-item py-1 px-2 font-weight-semibold" href="javascript:void(0)" onclick="cambiarEtiquetaRapida({{ $nota->id }}, '{{ $nota->etiqueta }}')" style="font-size: 0.78rem;">
                                        <i class="bi bi-tag mr-1.5 text-primary"></i> Cambiar Etiqueta
                                    </a>
                                    @if($nota->tipo === 'alerta')
                                        <a class="dropdown-item py-1 px-2 font-weight-semibold" href="javascript:void(0)" onclick="mostrarAlertaOverlay({id: {{ $nota->id }}})" style="font-size: 0.78rem;">
                                            <i class="bi bi-bell mr-1.5 text-purple"></i> Ver como Alerta
                                        </a>
                                    @endif
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item py-1 px-2 font-weight-semibold text-danger" href="javascript:void(0)" onclick="eliminarNotaRapida({{ $nota->id }})" style="font-size: 0.78rem;">
                                        <i class="bi bi-trash3 mr-1.5"></i> Eliminar nota
                                    </a>
                                </div>
                            </div>

                            <!-- Botón X Cerrar / Eliminar -->
                            <button type="button" class="sticky-btn-close" onclick="eliminarNotaRapida({{ $nota->id }})" title="Eliminar nota">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Note Body -->
                    <div class="sticky-body">
                        @if($nota->tipo === 'checklist')
                            <div class="sticky-checklist" id="sticky-checklist-{{ $nota->id }}">
                                @if(is_array($nota->checklist_items) && count($nota->checklist_items) > 0)
                                    @foreach($nota->checklist_items as $cIdx => $item)
                                        <div class="sticky-checklist-row" id="checklist-item-{{ $nota->id }}-{{ $cIdx }}">
                                            <input type="checkbox" class="sticky-check-input" {{ !empty($item['done']) ? 'checked' : '' }} onchange="toggleChecklistItemRapido({{ $nota->id }}, {{ $cIdx }})">
                                            <span class="sticky-check-text {{ !empty($item['done']) ? 'is-done' : '' }}">{{ $item['text'] }}</span>
                                            <button type="button" class="sticky-check-del" onclick="eliminarChecklistItem({{ $nota->id }}, {{ $cIdx }})" title="Quitar ítem"><i class="bi bi-x"></i></button>
                                        </div>
                                    @endforeach
                                @endif
                                <div class="sticky-add-item-row">
                                    <input type="text" class="sticky-add-item-input" placeholder="+ Agregar tarea y presiona Enter..." onkeydown="if(event.key==='Enter'){event.preventDefault(); agregarChecklistItemDirecto({{ $nota->id }}, this);}">
                                </div>
                            </div>
                        @elseif($nota->tipo === 'lista_numerada')
                            <div class="sticky-numbered-list" id="sticky-numbered-{{ $nota->id }}">
                                @if(is_array($nota->checklist_items) && count($nota->checklist_items) > 0)
                                    @foreach($nota->checklist_items as $cIdx => $item)
                                        <div class="sticky-numbered-row" id="numbered-item-{{ $nota->id }}-{{ $cIdx }}">
                                            <span class="sticky-num-index">{{ $cIdx + 1 }}.</span>
                                            <span class="sticky-num-text">{{ $item['text'] }}</span>
                                            <button type="button" class="sticky-check-del" onclick="eliminarItemNumerado({{ $nota->id }}, {{ $cIdx }})" title="Quitar ítem"><i class="bi bi-x"></i></button>
                                        </div>
                                    @endforeach
                                @endif
                                <div class="sticky-add-item-row">
                                    <input type="text" class="sticky-add-item-input" placeholder="+ Escribir elemento numerado y presiona Enter..." onkeydown="if(event.key==='Enter'){event.preventDefault(); agregarItemNumerado({{ $nota->id }}, this);}">
                                </div>
                            </div>
                        @else
                            <div class="sticky-editable-content"
                                contenteditable="true"
                                id="sticky-content-{{ $nota->id }}"
                                data-id="{{ $nota->id }}"
                                oninput="onNotaInput({{ $nota->id }})"
                                placeholder="{{ $nota->tipo === 'alerta' ? 'Escribe tu mensaje importante aquí...' : 'Escribe una nota rápida...' }}">{!! $nota->contenido !!}</div>
                        @endif

                        <!-- Captura Adjunta si existe -->
                        @if($nota->captura_url)
                            <div class="sticky-image-attachment" id="attachment-{{ $nota->id }}">
                                <img src="{{ $nota->captura_url }}" alt="Captura" onclick="verImagenCompleta('{{ $nota->captura_url }}', '{{ addslashes($nota->titulo) }}')">
                                <button type="button" class="sticky-del-img" onclick="quitarCapturaNota({{ $nota->id }})" title="Quitar imagen"><i class="bi bi-x"></i></button>
                            </div>
                        @endif
                    </div>

                    <!-- Bottom Formatting Toolbar (Windows Style) -->
                    <div class="sticky-toolbar">
                        <div class="sticky-format-tools">
                            <button type="button" class="format-btn" onclick="formatDoc({{ $nota->id }}, 'bold')" title="Negrita (Ctrl+B)"><b>B</b></button>
                            <button type="button" class="format-btn" onclick="formatDoc({{ $nota->id }}, 'italic')" title="Cursiva (Ctrl+I)"><i>I</i></button>
                            <button type="button" class="format-btn" onclick="formatDoc({{ $nota->id }}, 'underline')" title="Subrayado (Ctrl+U)"><u>U</u></button>
                            <button type="button" class="format-btn" onclick="formatDoc({{ $nota->id }}, 'strikeThrough')" title="Tachado"><s>ab</s></button>
                            <button type="button" class="format-btn" onclick="formatDoc({{ $nota->id }}, 'insertUnorderedList')" title="Lista con viñetas"><i class="bi bi-list-ul"></i></button>
                            <button type="button" class="format-btn" onclick="toggleTipoNota({{ $nota->id }})" title="{{ $nota->tipo === 'checklist' ? 'Cambiar a texto' : 'Cambiar a checklist' }}"><i class="bi bi-check2-square"></i></button>
                            <button type="button" class="format-btn" onclick="abrirSubidaImagenNota({{ $nota->id }})" title="Adjuntar imagen / captura"><i class="bi bi-image"></i></button>
                        </div>
                        <div class="sticky-meta">
                            <span class="save-status" id="save-status-{{ $nota->id }}"></span>
                            <span class="sticky-time">{{ $nota->updated_at ? $nota->updated_at->format('H:i') : $nota->created_at->format('H:i') }}</span>
                        </div>
                    </div>

                    <!-- Pliegue Dog-ear en la esquina -->
                    <div class="sticky-dogear"></div>
                </div>
            @empty
                <div class="sticky-empty-state" id="sticky-empty-state">
                    <div class="sticky-empty-icon">
                        <i class="bi bi-stickies"></i>
                    </div>
                    <h5 class="font-weight-bold mb-1" style="color: var(--text-primary);">Aún no tienes notas rápidas</h5>
                    <p class="text-muted mb-3" style="font-size: 0.85rem; max-width: 420px;">
                        Crea tu primera nota adhesiva estilo Windows para tomar anotaciones rápidas, listas de tareas o recordatorios en el sistema.
                    </p>
                    <button type="button" class="btn-new-sticky" onclick="crearNuevaNotaRapida('#eab308')">
                        <i class="bi bi-plus-lg"></i>
                        <span>Crear Primera Nota</span>
                    </button>
                </div>
            @endforelse
        </div>

    </div>

    <!-- SECCIÓN 2: GESTOR DE TAREAS (TABLA) -->
    <div id="seccion-tareas" class="flex-column gap-3" style="display: none !important;">
        <!-- Métricas de Tareas -->
        <div class="row g-2">
            <div class="col-6 col-md-3">
                <div class="p-3 rounded d-flex align-items-center justify-content-between" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 10px;">
                    <div>
                        <div class="text-muted" style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700;">Total Tareas</div>
                        <div class="font-weight-bold" style="font-size: 1.35rem; color: var(--text-primary);">{{ $tareas->count() }}</div>
                    </div>
                    <i class="bi bi-list-task text-primary" style="font-size: 1.6rem; opacity: 0.7;"></i>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-3 rounded d-flex align-items-center justify-content-between" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 10px;">
                    <div>
                        <div class="text-muted" style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700;">Pendientes</div>
                        <div class="font-weight-bold text-warning" style="font-size: 1.35rem;">{{ $tareas->where('estado', 'pendiente')->count() }}</div>
                    </div>
                    <i class="bi bi-clock-history text-warning" style="font-size: 1.6rem; opacity: 0.7;"></i>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-3 rounded d-flex align-items-center justify-content-between" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 10px;">
                    <div>
                        <div class="text-muted" style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700;">En Progreso</div>
                        <div class="font-weight-bold text-info" style="font-size: 1.35rem;">{{ $tareas->where('estado', 'en_progreso')->count() }}</div>
                    </div>
                    <i class="bi bi-arrow-repeat text-info" style="font-size: 1.6rem; opacity: 0.7;"></i>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="p-3 rounded d-flex align-items-center justify-content-between" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 10px;">
                    <div>
                        <div class="text-muted" style="font-size: 0.72rem; text-transform: uppercase; font-weight: 700;">Completadas</div>
                        <div class="font-weight-bold text-success" style="font-size: 1.35rem;">{{ $tareas->where('estado', 'completada')->count() }}</div>
                    </div>
                    <i class="bi bi-check-circle-fill text-success" style="font-size: 1.6rem; opacity: 0.7;"></i>
                </div>
            </div>
        </div>

        <!-- Tabla de Tareas -->
        <div class="card border-0 shadow-sm overflow-hidden" style="background: var(--bg-surface); border: 1px solid var(--border-color) !important; border-radius: 10px;">
            <div class="p-3 d-flex align-items-center justify-content-between border-bottom" style="border-color: var(--border-color) !important;">
                <h6 class="mb-0 font-weight-bold" style="color: var(--text-primary);">Lista de Tareas Registradas</h6>
                <button type="button" class="btn btn-sm btn-primary d-inline-flex align-items-center gap-1.5" onclick="openModalNuevaTarea()">
                    <i class="bi bi-plus-lg"></i>
                    <span>Nueva Tarea</span>
                </button>
            </div>
            <div class="table-responsive custom-scrollbar" style="max-height: calc(100vh - 350px); overflow-y: auto;">
                <table class="table table-hover table-sing mb-0 text-nowrap" style="font-size: 0.78rem;">
                    <thead style="position: sticky; top: 0; z-index: 10; background-color: var(--bg-subtle); border-bottom: 2px solid var(--border-color);">
                        <tr>
                            <th class="py-2.5 px-3 text-center" style="width: 40px;">N°</th>
                            <th class="py-2.5 px-3 text-center" style="width: 120px;">Estado</th>
                            <th class="py-2.5 px-3">Tarea / Actividad</th>
                            <th class="py-2.5 px-3">Descripción</th>
                            <th class="py-2.5 px-3 text-center" style="width: 110px;">Fecha Límite</th>
                            <th class="py-2.5 px-3" style="width: 150px;">Asignado a</th>
                            <th class="py-2.5 px-3 text-center" style="width: 90px;">Prioridad</th>
                            <th class="py-2.5 px-3 text-center" style="width: 80px;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($tareas as $idx => $t)
                            <tr id="tarea-row-{{ $t->id }}">
                                <td class="py-2 px-3 text-center text-muted font-monospace">{{ $idx + 1 }}</td>
                                <td class="py-2 px-3 text-center">
                                    @if($t->estado === 'completada')
                                        <span class="badge badge-subtle-success px-2 py-1 font-weight-bold" role="button" onclick="cambiarEstadoTareaRapido({{ $t->id }}, 'pendiente')" style="cursor: pointer;">
                                            <i class="bi bi-check2-circle mr-1"></i> Completada
                                        </span>
                                    @elseif($t->estado === 'en_progreso')
                                        <span class="badge badge-subtle-info px-2 py-1 font-weight-bold" role="button" onclick="cambiarEstadoTareaRapido({{ $t->id }}, 'completada')" style="cursor: pointer;">
                                            <i class="bi bi-arrow-repeat mr-1"></i> En Progreso
                                        </span>
                                    @else
                                        <span class="badge badge-subtle-warning px-2 py-1 font-weight-bold" role="button" onclick="cambiarEstadoTareaRapido({{ $t->id }}, 'en_progreso')" style="cursor: pointer;">
                                            <i class="bi bi-hourglass-split mr-1"></i> Pendiente
                                        </span>
                                    @endif
                                </td>
                                <td class="py-2 px-3 font-weight-bold" style="color: {{ $t->estado === 'completada' ? 'var(--text-muted)' : 'var(--text-primary)' }}; {{ $t->estado === 'completada' ? 'text-decoration: line-through;' : '' }}">
                                    {{ $t->titulo }}
                                </td>
                                <td class="py-2 px-3 text-secondary text-truncate" style="max-width: 280px;" title="{{ $t->descripcion }}">
                                    {{ $t->descripcion ?: '-' }}
                                </td>
                                <td class="py-2 px-3 text-center text-muted">
                                    {{ $t->fecha_limite ? \Carbon\Carbon::parse($t->fecha_limite)->format('d/m/Y') : '-' }}
                                </td>
                                <td class="py-2 px-3">
                                    @if($t->assignedTo)
                                        <i class="bi bi-person-fill text-primary mr-1"></i> {{ $t->assignedTo->name }}
                                    @else
                                        <span class="text-muted">-</span>
                                    @endif
                                </td>
                                <td class="py-2 px-3 text-center">
                                    @if($t->prioridad === 'urgente' || $t->prioridad === 'alta')
                                        <span class="badge badge-subtle-danger px-2 py-0.5 font-weight-bold">Alta</span>
                                    @elseif($t->prioridad === 'baja')
                                        <span class="badge badge-subtle-secondary px-2 py-0.5">Baja</span>
                                    @else
                                        <span class="badge badge-subtle-primary px-2 py-0.5">Normal</span>
                                    @endif
                                </td>
                                <td class="py-2 px-3 text-center">
                                    <button type="button" class="btn btn-icon btn-sm btn-subtle-danger" onclick="eliminarTarea({{ $t->id }})" title="Eliminar tarea">
                                        <i class="bi bi-trash3"></i>
                                    </button>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="text-center py-4 text-muted">
                                    No hay tareas registradas en el gestor.
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<!-- Input oculto para adjuntar imagen a nota específica -->
<input type="file" id="input-archivo-imagen-nota" accept="image/*" style="display: none !important;" onchange="procesarArchivoImagenNota(this)">

<!-- MODAL: VISOR DE IMAGEN COMPLETA -->
<div class="modal fade" id="modalVisorImagen" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1070;">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 12px; overflow: hidden;">
            <div class="modal-header py-2.5 px-3 d-flex align-items-center justify-content-between" style="background: var(--bg-subtle); border-bottom: 1px solid var(--border-color);">
                <h6 class="modal-title font-weight-bold mb-0" id="visorImagenTitle" style="color: var(--text-primary);">Captura adjunta</h6>
                <button type="button" class="btn-close text-muted" data-dismiss="modal" data-bs-dismiss="modal" style="background: none; border: none; font-size: 1.2rem;">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="modal-body p-2 text-center" style="background: #090d16;">
                <img id="visorImagenImg" src="" class="img-fluid rounded" style="max-height: 80vh; object-fit: contain;">
            </div>
        </div>
    </div>
</div>

<!-- MODAL: CREAR / EDITAR TAREA -->
<div class="modal fade" id="modalTarea" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 550px;">
        <div class="modal-content" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 12px; box-shadow: var(--shadow-xl);">
            <div class="modal-header py-2.5 px-3 d-flex align-items-center justify-content-between" style="background: var(--bg-subtle); border-bottom: 1px solid var(--border-color);">
                <h6 class="modal-title font-weight-bold mb-0 d-flex align-items-center gap-2" id="modalTareaTitle" style="color: var(--text-primary);">
                    <i class="bi bi-list-check text-primary"></i> <span>Nueva Tarea</span>
                </h6>
                <button type="button" class="btn-close text-muted" data-dismiss="modal" data-bs-dismiss="modal" style="background: none; border: none; font-size: 1.1rem;">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <form id="formTarea">
                @csrf
                <input type="hidden" id="tarea_id" name="id">
                <div class="modal-body p-3 d-flex flex-column gap-2.5">
                    <div>
                        <label class="form-label font-weight-semibold mb-1" style="font-size: 0.78rem;">Título de la Tarea:</label>
                        <input type="text" id="tarea_titulo" name="titulo" class="form-control form-control-sm" placeholder="Ej: Entregar informe mensual AT2-R..." required style="background: var(--input-bg); color: var(--text-primary); border-color: var(--border-color);">
                    </div>
                    <div>
                        <label class="form-label font-weight-semibold mb-1" style="font-size: 0.78rem;">Descripción (Opcional):</label>
                        <textarea id="tarea_descripcion" name="descripcion" rows="2" class="form-control form-control-sm" placeholder="Detalles de la tarea..." style="background: var(--input-bg); color: var(--text-primary); border-color: var(--border-color);"></textarea>
                    </div>
                    <div class="row g-2">
                        <div class="col-6">
                            <label class="form-label font-weight-semibold mb-1" style="font-size: 0.78rem;">Prioridad:</label>
                            <select id="tarea_prioridad" name="prioridad" class="form-control form-control-sm" style="background: var(--input-bg); color: var(--text-primary); border-color: var(--border-color);">
                                <option value="baja">Baja</option>
                                <option value="normal" selected>Normal</option>
                                <option value="alta">Alta</option>
                                <option value="urgente">Urgente</option>
                            </select>
                        </div>
                        <div class="col-6">
                            <label class="form-label font-weight-semibold mb-1" style="font-size: 0.78rem;">Fecha Límite:</label>
                            <input type="date" id="tarea_fecha_limite" name="fecha_limite" class="form-control form-control-sm" style="background: var(--input-bg); color: var(--text-primary); border-color: var(--border-color);">
                        </div>
                    </div>
                    <div>
                        <label class="form-label font-weight-semibold mb-1" style="font-size: 0.78rem;">Asignar a:</label>
                        <select id="tarea_assigned_to" name="assigned_to" class="form-control form-control-sm" style="background: var(--input-bg); color: var(--text-primary); border-color: var(--border-color);">
                            <option value="">Sin Asignar</option>
                            @foreach($usuarios as $u)
                                <option value="{{ $u->id }}">{{ $u->name }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer py-2 px-3 d-flex justify-content-end gap-2" style="background: var(--bg-subtle); border-top: 1px solid var(--border-color);">
                    <button type="button" class="btn btn-sm btn-subtle" data-dismiss="modal" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-sm btn-primary font-weight-bold px-3">Guardar Tarea</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
let saveTimeouts = {};
let targetNotaIdForImage = null;
let filtroColorActivo = 'all';
let filtroEtiquetaActiva = 'all';

// Alternar entre pestañas principales
function switchMainTab(tab) {
    if (tab === 'stickies') {
        $('#btn-tab-stickies').addClass('active');
        $('#btn-tab-tareas').removeClass('active');
        $('#seccion-stickies').attr('style', 'display: flex !important;');
        $('#seccion-tareas').attr('style', 'display: none !important;');
    } else {
        $('#btn-tab-tareas').addClass('active');
        $('#btn-tab-stickies').removeClass('active');
        $('#seccion-tareas').attr('style', 'display: flex !important;');
        $('#seccion-stickies').attr('style', 'display: none !important;');
    }
}

// ── CREAR NUEVA NOTA RÁPIDA ESTILO WINDOWS ───────────────────────────────
// Mapeo Color → Tipo automático
// 🟡 Amarillo/Azul/Carbón = 'nota'  (texto libre)
// 🟢 Verde                = 'checklist'
// 🩷 Rosa                 = 'lista_numerada'
// 🟣 Púrpura              = 'alerta'  (mensaje superpuesto)
function colorToTipo(color) {
    if (!color) return 'nota';
    const c = color.toLowerCase();
    if (c.includes('22c55e') || c.includes('10b981') || c.includes('green'))  return 'checklist';
    if (c.includes('ec4899') || c.includes('ef4444') || c.includes('pink'))   return 'lista_numerada';
    if (c.includes('a855f7') || c.includes('8b5cf6') || c.includes('purple')) return 'alerta';
    return 'nota';
}

// Etiqueta amigable por tipo
function tipoLabel(tipo) {
    const labels = { nota: 'Nota de texto', checklist: 'Lista de tareas ✅', lista_numerada: 'Lista numerada 🔢', alerta: 'Mensaje / Alerta 📢' };
    return labels[tipo] || 'Nota';
}

function crearNuevaNotaRapida(color) {
    color = color || '#eab308';
    const tipo = colorToTipo(color);

    // Título por defecto según tipo
    const titulos = { nota: 'Nota rápida', checklist: 'Lista de tareas', lista_numerada: 'Lista numerada', alerta: 'Mensaje importante' };
    const titulo = titulos[tipo] || 'Nota rápida';

    $.ajax({
        url: '{{ route("notas.store") }}',
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            titulo: titulo,
            contenido: '',
            tipo: tipo,
            color: color,
            etiqueta: 'General',
            pinned: 1
        },
        success: function(response) {
            if (!response || !response.nota) return;
            const n = response.nota;
            const colorClass = getStickyColorClassJS(n.color);
            const tipoFinal = n.tipo || tipo;

            // ── Todas las notas tienen la misma función y quedan estáticas en pantalla ──
            if (typeof window.abrirNotaFlotante === 'function') {
                window.abrirNotaFlotante(n.id);
            }
            if (tipoFinal === 'alerta') {
                mostrarAlertaOverlay(n);
            }

            // ── Construir body según tipo ──────────────────────────────
            let bodyHtml = '';
            if (tipoFinal === 'checklist') {
                bodyHtml = `
                    <div class="sticky-checklist" id="sticky-checklist-${n.id}">
                        <div class="sticky-add-item-row">
                            <input type="text" class="sticky-add-item-input" placeholder="+ Agregar tarea y presiona Enter..."
                                onkeydown="if(event.key==='Enter'){event.preventDefault(); agregarChecklistItemDirecto(${n.id}, this);}">
                        </div>
                    </div>`;
            } else if (tipoFinal === 'lista_numerada') {
                bodyHtml = `
                    <div class="sticky-numbered-list" id="sticky-numbered-${n.id}">
                        <div class="sticky-add-item-row">
                            <input type="text" class="sticky-add-item-input" placeholder="+ Escribir elemento y presiona Enter..."
                                onkeydown="if(event.key==='Enter'){event.preventDefault(); agregarItemNumerado(${n.id}, this);}">
                        </div>
                    </div>`;
            } else {
                // nota o alerta: editor de texto
                bodyHtml = `
                    <div class="sticky-editable-content"
                        contenteditable="true"
                        id="sticky-content-${n.id}"
                        data-id="${n.id}"
                        oninput="onNotaInput(${n.id})"
                        placeholder="${tipoFinal === 'alerta' ? 'Escribe tu mensaje aquí...' : 'Escribe una nota rápida...'}"></div>`;
            }

            // ── Icono de tipo en la toolbar ────────────────────────────
            const tipoIco = { nota: 'bi-sticky', checklist: 'bi-check2-square', lista_numerada: 'bi-list-ol', alerta: 'bi-bell-fill' };
            const ico = tipoIco[tipoFinal] || 'bi-sticky';

            const html = `
                <div class="sticky-note-card sticky-${colorClass} ${tipoFinal === 'alerta' ? 'is-alerta' : ''}"
                    id="sticky-note-${n.id}"
                    data-id="${n.id}"
                    data-color-class="${colorClass}"
                    data-color="${n.color || '#eab308'}"
                    data-tag="general"
                    data-tipo="${tipoFinal}"
                    style="opacity: 0; transform: scale(0.95);">

                    <!-- Header -->
                    <div class="sticky-header">
                        <button type="button" class="sticky-btn-add" onclick="crearNuevaNotaRapida('${n.color || '#eab308'}')" title="Nueva nota del mismo tipo">
                            <i class="bi bi-plus-lg"></i>
                        </button>
                        <div class="sticky-title-container">
                            <i class="bi ${ico}" style="font-size:0.8rem; opacity:0.7; flex-shrink:0;"></i>
                            <input type="text" class="sticky-title-input" value="${n.titulo}" placeholder="Título..." onchange="actualizarTituloNota(${n.id}, this.value)">
                            <i class="bi bi-pin-angle-fill sticky-pin-icon d-none" id="pin-icon-${n.id}" title="Nota fijada"></i>
                        </div>
                        <div class="sticky-header-actions">
                            <!-- Botón Chincheta Directo -->
                            <button type="button" class="sticky-btn-pin ${n.pinned ? 'is-pinned' : ''}" id="pin-btn-${n.id}" onclick="togglePinNota(${n.id})" title="${n.pinned ? 'Desfijar de la pantalla' : 'Fijar enfrente de la pantalla'}">
                                <i class="bi ${n.pinned ? 'bi-pin-angle-fill' : 'bi-pin-angle'}" id="pin-icon-${n.id}"></i>
                            </button>

                            <div class="dropdown">
                                <button type="button" class="sticky-btn-menu" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                                    <i class="bi bi-three-dots"></i>
                                </button>
                                <div class="dropdown-menu dropdown-menu-right sticky-color-menu shadow-lg">
                                    <div class="text-muted font-weight-bold mb-1" style="font-size:0.70rem;text-transform:uppercase;">Color / Tipo:</div>
                                    <div class="sticky-palette-grid">
                                        <span class="color-circle bg-sticky-yellow" onclick="cambiarColorNota(${n.id},'#eab308','yellow')" title="Amarillo — Nota libre"></span>
                                        <span class="color-circle bg-sticky-green"  onclick="cambiarColorNota(${n.id},'#22c55e','green')"  title="Verde — Checklist"></span>
                                        <span class="color-circle bg-sticky-pink"   onclick="cambiarColorNota(${n.id},'#ec4899','pink')"   title="Rosa — Lista numerada"></span>
                                        <span class="color-circle bg-sticky-purple" onclick="cambiarColorNota(${n.id},'#a855f7','purple')" title="Púrpura — Mensaje/Alerta"></span>
                                        <span class="color-circle bg-sticky-blue"   onclick="cambiarColorNota(${n.id},'#3b82f6','blue')"   title="Azul — Nota libre"></span>
                                        <span class="color-circle bg-sticky-charcoal" onclick="cambiarColorNota(${n.id},'#64748b','charcoal')" title="Carbón — Nota libre"></span>
                                    </div>
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item py-1 px-2 font-weight-semibold" href="javascript:void(0)" onclick="togglePinNota(${n.id})" style="font-size:0.78rem;">
                                        <i class="bi bi-pin-angle mr-1 text-warning"></i>
                                        <span id="pin-text-${n.id}">Fijar arriba</span>
                                    </a>
                                    <a class="dropdown-item py-1 px-2 font-weight-semibold" href="javascript:void(0)" onclick="cambiarEtiquetaRapida(${n.id},'General')" style="font-size:0.78rem;">
                                        <i class="bi bi-tag mr-1 text-primary"></i> Cambiar Etiqueta
                                    </a>
                                    ${tipoFinal === 'alerta' ? `<a class="dropdown-item py-1 px-2 font-weight-semibold" href="javascript:void(0)" onclick="mostrarAlertaOverlay(${JSON.stringify(n)})" style="font-size:0.78rem;"><i class="bi bi-bell mr-1 text-purple"></i> Ver como Alerta</a>` : ''}
                                    <div class="dropdown-divider"></div>
                                    <a class="dropdown-item py-1 px-2 font-weight-semibold text-danger" href="javascript:void(0)" onclick="eliminarNotaRapida(${n.id})" style="font-size:0.78rem;">
                                        <i class="bi bi-trash3 mr-1"></i> Eliminar nota
                                    </a>
                                </div>
                            </div>
                            <button type="button" class="sticky-btn-close" onclick="eliminarNotaRapida(${n.id})" title="Eliminar nota">
                                <i class="bi bi-x-lg"></i>
                            </button>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="sticky-body">${bodyHtml}</div>

                    <!-- Toolbar -->
                    <div class="sticky-toolbar">
                        <div class="sticky-format-tools">
                            ${tipoFinal === 'nota' || tipoFinal === 'alerta' ? `
                            <button type="button" class="format-btn" onclick="formatDoc(${n.id},'bold')" title="Negrita"><b>B</b></button>
                            <button type="button" class="format-btn" onclick="formatDoc(${n.id},'italic')" title="Cursiva"><i>I</i></button>
                            <button type="button" class="format-btn" onclick="formatDoc(${n.id},'underline')" title="Subrayado"><u>U</u></button>
                            <button type="button" class="format-btn" onclick="formatDoc(${n.id},'strikeThrough')" title="Tachado"><s>ab</s></button>
                            ` : ''}
                            ${tipoFinal === 'alerta' ? `
                            <button type="button" class="format-btn" onclick="mostrarAlertaOverlay({id:${n.id}})" title="Ver alerta ahora" style="color:#a855f7;">
                                <i class="bi bi-bell-fill"></i>
                            </button>` : ''}
                        </div>
                        <div class="sticky-meta">
                            <span class="save-status" id="save-status-${n.id}"></span>
                            <span class="sticky-tipo-badge">${tipoLabel(tipoFinal)}</span>
                        </div>
                    </div>
                    <div class="sticky-dogear"></div>
                </div>
            `;

            $('#sticky-empty-state').remove();
            $('#sticky-board-grid').prepend(html);

            const $card = $(`#sticky-note-${n.id}`);
            $card.animate({ opacity: 1 }, 200, function() {
                $card.css('transform', 'none');
                // Foco en el primer input de texto disponible
                const focusEl = $card.find('.sticky-editable-content, .sticky-add-item-input').first();
                if (focusEl.length) focusEl.focus();
            });

            // Actualizar contador
            const cnt = parseInt($('#badge-total-notas').text() || '0') + 1;
            $('#badge-total-notas').text(cnt);
        },
        error: function() {
            Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo crear la nota.' });
        }
    });
}

// ── AGREGAR ÍTEM A LISTA NUMERADA ─────────────────────────────────────────
function agregarItemNumerado(id, inputEl) {
    const texto = $(inputEl).val().trim();
    if (!texto) return;

    $(inputEl).val('');

    // Construir lista de items actuales
    let items = [];
    $(`#sticky-numbered-${id} .sticky-numbered-row`).each(function() {
        items.push({
            text: $(this).find('.sticky-num-text').text().trim(),
            done: false
        });
    });
    items.push({ text: texto, done: false });

    $.ajax({
        url: `/notas/${id}`,
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            _method: 'PUT',
            checklist_items: items
        },
        success: function() {
            location.reload();
        }
    });
}

function eliminarItemNumerado(id, index) {
    let items = [];
    $(`#sticky-numbered-${id} .sticky-numbered-row`).each(function(i) {
        if (i !== index) {
            items.push({
                text: $(this).find('.sticky-num-text').text().trim(),
                done: false
            });
        }
    });

    $.ajax({
        url: `/notas/${id}`,
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            _method: 'PUT',
            checklist_items: items
        },
        success: function() {
            location.reload();
        }
    });
}

// ── MOSTRAR ALERTA PÚRPURA COMO OVERLAY ─────────────────────────────────
function mostrarAlertaOverlay(nota) {
    // nota puede ser objeto completo o {id: X}
    const titulo = nota.titulo || 'Mensaje importante';
    const contenido = nota.contenido || '';
    const notaId = nota.id;

    // Si es objeto mínimo {id}, cargar desde el grid
    if (!nota.titulo && notaId) {
        const $card = $(`#sticky-note-${notaId}`);
        nota = {
            id: notaId,
            titulo: $card.find('.sticky-title-input').val() || 'Mensaje',
            contenido: $card.find('.sticky-editable-content').html() || ''
        };
    }

    Swal.fire({
        title: `<i class="bi bi-bell-fill" style="color:#a855f7; margin-right:6px;"></i>${nota.titulo || 'Mensaje'}`,
        html: nota.contenido
            ? `<div style="text-align:left; font-size:0.95rem; line-height:1.6; color:var(--text-primary);">${nota.contenido}</div>`
            : `<p style="color:var(--text-muted);">Sin contenido aún. Escribe el mensaje en la nota.</p>`,
        icon: undefined,
        showConfirmButton: true,
        confirmButtonText: 'Entendido',
        confirmButtonColor: '#a855f7',
        showCloseButton: true,
        customClass: {
            popup: 'swal2-popup',
            title: 'swal2-title'
        },
        backdrop: `rgba(88,28,135,0.4)`
    });
}

function getStickyColorClassJS(color) {
    if (!color) return 'yellow';
    color = color.toLowerCase();
    if (color.includes('22c55e') || color.includes('10b981') || color.includes('green')) return 'green';
    if (color.includes('ec4899') || color.includes('ef4444') || color.includes('pink')) return 'pink';
    if (color.includes('a855f7') || color.includes('8b5cf6') || color.includes('purple')) return 'purple';
    if (color.includes('3b82f6') || color.includes('06b6d4') || color.includes('blue')) return 'blue';
    if (color.includes('64748b') || color.includes('6c757d') || color.includes('charcoal')) return 'charcoal';
    return 'yellow';
}

// ── AUTO-GUARDADO DE CONTENIDO (DEBOUNCE 800ms) ───────────────────────────
function onNotaInput(id) {
    const $status = $(`#save-status-${id}`);
    $status.text('Escribiendo...').css('color', '#eab308');

    clearTimeout(saveTimeouts[id]);
    saveTimeouts[id] = setTimeout(() => {
        const contenido = $(`#sticky-content-${id}`).html();
        $status.text('Guardando...');

        $.ajax({
            url: `/notas/${id}`,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                _method: 'PUT',
                contenido: contenido
            },
            success: function() {
                $status.text('Guardado ✓').css('color', '#10b981');
                setTimeout(() => { $status.text(''); }, 2000);
            },
            error: function() {
                $status.text('Error al guardar').css('color', '#ef4444');
            }
        });
    }, 800);
}

// Actualizar título de nota
function actualizarTituloNota(id, titulo) {
    const $status = $(`#save-status-${id}`);
    $status.text('Guardando...');

    $.ajax({
        url: `/notas/${id}`,
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            _method: 'PUT',
            titulo: titulo || 'Nota rápida'
        },
        success: function() {
            $status.text('Guardado ✓').css('color', '#10b981');
            setTimeout(() => { $status.text(''); }, 2000);
        }
    });
}

// ── FORMATO DE TEXTO (BOLD, ITALIC, LIST, ETC.) ───────────────────────────
function formatDoc(id, cmd) {
    const el = document.getElementById(`sticky-content-${id}`);
    if (el) {
        el.focus();
        document.execCommand(cmd, false, null);
        onNotaInput(id);
    }
}

// ── CAMBIAR COLOR DE NOTA (también actualiza tipo en la card) ─────────────
function cambiarColorNota(id, hexColor, colorClass) {
    const $card = $(`#sticky-note-${id}`);

    $card.removeClass('sticky-yellow sticky-green sticky-pink sticky-purple sticky-blue sticky-charcoal')
         .addClass(`sticky-${colorClass}`)
         .data('color-class', colorClass)
         .data('color', hexColor);

    // Actualizar tipo según nuevo color (sólo visual del badge, el tipo real requiere reload)
    const nuevoTipo = colorToTipo(hexColor);

    $.ajax({
        url: `/notas/${id}`,
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            _method: 'PUT',
            color: hexColor,
            tipo: nuevoTipo
        },
        success: function() {
            // Recargar card para reflejar el cambio de tipo correctamente
            setTimeout(() => location.reload(), 400);
        }
    });
}

// ── FIJAR / DESFIJAR NOTA ─────────────────────────────────────────────────
function togglePinNota(id) {
    const $card = $(`#sticky-note-${id}`);
    const isPinned = $card.hasClass('is-pinned');
    const newPinned = !isPinned;

    if (newPinned) {
        $card.addClass('is-pinned');
        $(`#pin-btn-${id}`).addClass('is-pinned').attr('title', 'Desfijar de la pantalla');
        $(`#pin-icon-${id}`).removeClass('bi-pin-angle d-none').addClass('bi-pin-angle-fill');
        $(`#pin-text-${id}`).text('Desfijar de arriba');
        $('#sticky-board-grid').prepend($card);
    } else {
        $card.removeClass('is-pinned');
        $(`#pin-btn-${id}`).removeClass('is-pinned').attr('title', 'Fijar enfrente de la pantalla');
        $(`#pin-icon-${id}`).removeClass('bi-pin-angle-fill').addClass('bi-pin-angle');
        $(`#pin-text-${id}`).text('Fijar arriba');
        // Quitar nota del overlay flotante global si existe
        const fsn = document.getElementById(`fsn-${id}`);
        if (fsn) {
            fsn.style.transition = 'opacity 0.18s ease';
            fsn.style.opacity = '0';
            setTimeout(() => { if (fsn.parentNode) fsn.parentNode.removeChild(fsn); }, 200);
        }
    }

    $.ajax({
        url: `/notas/${id}`,
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            _method: 'PUT',
            pinned: newPinned ? 1 : 0
        },
        success: function() {
            if (newPinned) {
                // Abrir la nota flotante enfrente inmediatamente
                if (typeof window.abrirNotaFlotante === 'function') {
                    window.abrirNotaFlotante(id);
                } else if (typeof window.mostrarNotasFlotantes === 'function') {
                    window.mostrarNotasFlotantes();
                }
                Swal.fire({
                    toast: true, position: 'top-end', icon: 'success',
                    title: '📌 Nota fijada enfrente de la pantalla',
                    showConfirmButton: false, timer: 2200
                });
            }
        }
    });
}

// ── CAMBIAR ETIQUETA RÁPIDA ───────────────────────────────────────────────

function cambiarEtiquetaRapida(id, currentTag) {
    const etiquetas = ['General', 'Urgente', 'Recordatorio', 'SESAL', 'Epidemiología', 'Estadísticas'];
    let options = {};
    etiquetas.forEach(e => { options[e] = e; });

    Swal.fire({
        title: 'Asignar Etiqueta',
        input: 'select',
        inputOptions: options,
        inputValue: currentTag || 'General',
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            const nuevaEtq = result.value;
            $(`#sticky-note-${id}`).data('tag', nuevaEtq.toLowerCase());

            $.ajax({
                url: `/notas/${id}`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'PUT',
                    etiqueta: nuevaEtq
                },
                success: function() {
                    const $status = $(`#save-status-${id}`);
                    $status.text('Etiqueta cambiada ✓').css('color', '#10b981');
                    setTimeout(() => { $status.text(''); }, 2000);
                }
            });
        }
    });
}

// ── CAMBIAR ENTRE NOTA DE TEXTO Y CHECKLIST ───────────────────────────────
function toggleTipoNota(id) {
    const $card = $(`#sticky-note-${id}`);
    const tipoActual = $card.data('tipo');
    const nuevoTipo = (tipoActual === 'checklist') ? 'nota' : 'checklist';

    Swal.fire({
        title: nuevoTipo === 'checklist' ? '¿Convertir a Checklist?' : '¿Convertir a Nota de Texto?',
        text: 'Se adaptará el formato de la nota.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, cambiar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/notas/${id}`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'PUT',
                    tipo: nuevoTipo
                },
                success: function() {
                    location.reload();
                }
            });
        }
    });
}

// ── MANEJO DE CHECKLIST DIRECTO EN LA NOTA ────────────────────────────────
function toggleChecklistItemRapido(notaId, index) {
    $.ajax({
        url: `/notas/${notaId}/toggle-checklist`,
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            index: index
        },
        success: function(resp) {
            if (resp && resp.checklist_items) {
                const item = resp.checklist_items[index];
                const $row = $(`#checklist-item-${notaId}-${index}`);
                if (item && item.done) {
                    $row.find('.sticky-check-text').addClass('is-done');
                } else {
                    $row.find('.sticky-check-text').removeClass('is-done');
                }
            }
        }
    });
}

function agregarChecklistItemDirecto(notaId, inputEl) {
    const text = $(inputEl).val().trim();
    if (!text) return;

    $(inputEl).val('');
    
    // Obtener items actuales del DOM o vía API
    let items = [];
    $(`#sticky-checklist-${notaId} .sticky-checklist-row`).each(function() {
        items.push({
            text: $(this).find('.sticky-check-text').text().trim(),
            done: $(this).find('.sticky-check-input').is(':checked')
        });
    });
    items.push({ text: text, done: false });

    $.ajax({
        url: `/notas/${notaId}`,
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            _method: 'PUT',
            checklist_items: items
        },
        success: function() {
            location.reload();
        }
    });
}

function eliminarChecklistItem(notaId, index) {
    let items = [];
    $(`#sticky-checklist-${notaId} .sticky-checklist-row`).each(function(i) {
        if (i !== index) {
            items.push({
                text: $(this).find('.sticky-check-text').text().trim(),
                done: $(this).find('.sticky-check-input').is(':checked')
            });
        }
    });

    $.ajax({
        url: `/notas/${notaId}`,
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            _method: 'PUT',
            checklist_items: items
        },
        success: function() {
            location.reload();
        }
    });
}

// ── ADJUNTAR IMÁGENES / CAPTURAS ──────────────────────────────────────────
function abrirSubidaImagenNota(id) {
    targetNotaIdForImage = id;
    $('#input-archivo-imagen-nota').click();
}

function procesarArchivoImagenNota(input) {
    if (!targetNotaIdForImage || !input.files || !input.files[0]) return;

    const file = input.files[0];
    const reader = new FileReader();
    reader.onload = function(e) {
        const base64 = e.target.result;
        const id = targetNotaIdForImage;

        $.ajax({
            url: `/notas/${id}`,
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                _method: 'PUT',
                captura_base64: base64
            },
            success: function(resp) {
                if (resp && resp.nota && resp.nota.captura_url) {
                    location.reload();
                }
            }
        });
    };
    reader.readAsDataURL(file);
    $(input).val('');
}

function quitarCapturaNota(id) {
    Swal.fire({
        title: '¿Quitar imagen?',
        text: 'Se eliminará la captura adjunta de esta nota.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sí, quitar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            $.ajax({
                url: `/notas/${id}`,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'PUT',
                    delete_captura: 1
                },
                success: function() {
                    $(`#attachment-${id}`).fadeOut(200, function() { $(this).remove(); });
                }
            });
        }
    });
}

// Soporte para pegar capturas con Ctrl+V directamente en cualquier nota
document.addEventListener('paste', function(e) {
    const activeEl = document.activeElement;
    if (activeEl && activeEl.classList.contains('sticky-editable-content')) {
        const notaId = $(activeEl).data('id');
        const items = (e.clipboardData || e.originalEvent.clipboardData).items;
        for (let i in items) {
            const item = items[i];
            if (item.kind === 'file' && item.type.includes('image/')) {
                e.preventDefault();
                const blob = item.getAsFile();
                const reader = new FileReader();
                reader.onload = function(ev) {
                    $.ajax({
                        url: `/notas/${notaId}`,
                        type: 'POST',
                        data: {
                            _token: '{{ csrf_token() }}',
                            _method: 'PUT',
                            captura_base64: ev.target.result
                        },
                        success: function() {
                            location.reload();
                        }
                    });
                };
                reader.readAsDataURL(blob);
                break;
            }
        }
    }
});

// ── ELIMINAR NOTA RÁPIDA ──────────────────────────────────────────────────
function eliminarNotaRapida(id) {
    Swal.fire({
        title: '¿Eliminar nota rápida?',
        text: 'La nota será eliminada del tablero.',
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
                    $(`#sticky-note-${id}`).fadeOut(250, function() {
                        $(this).remove();
                        const newCount = Math.max(0, parseInt($('#badge-total-notas').text() || '1') - 1);
                        $('#badge-total-notas').text(newCount);

                        if ($('#sticky-board-grid .sticky-note-card').length === 0) {
                            location.reload();
                        }
                    });
                }
            });
        }
    });
}

// ── FILTRADO EN VIVO (BÚSQUEDA, COLORES, ETIQUETAS) ────────────────────────
function filtrarPorColor(color, el) {
    filtroColorActivo = color;
    $('.color-filter-dot').removeClass('active');
    $(el).addClass('active');
    filtrarNotasLive();
}

function filtrarPorEtiqueta(etq, el) {
    filtroEtiquetaActiva = etq;
    $('.sticky-tag-pill').removeClass('active');
    $(el).addClass('active');
    filtrarNotasLive();
}

function filtrarNotasLive() {
    const query = $('#sticky-search').val().toLowerCase().trim();

    $('#sticky-board-grid .sticky-note-card').each(function() {
        const $card = $(this);
        const cardColorClass = $card.data('color-class');
        const cardTag = ($card.data('tag') || '').toString().toLowerCase();
        const cardText = ($card.find('.sticky-title-input').val() + ' ' + $card.find('.sticky-body').text()).toLowerCase();

        // Evaluar condiciones
        const matchSearch = (!query || cardText.includes(query));
        const matchColor = (filtroColorActivo === 'all' || cardColorClass === filtroColorActivo);
        const matchTag = (filtroEtiquetaActiva === 'all' || cardTag === filtroEtiquetaActiva);

        if (matchSearch && matchColor && matchTag) {
            $card.fadeIn(150);
        } else {
            $card.fadeOut(150);
        }
    });
}

// ── VISOR DE IMÁGENES LIGHTBOX ───────────────────────────────────────────
function verImagenCompleta(url, titulo) {
    $('#visorImagenTitle').text(titulo || 'Captura de nota adhesiva');
    $('#visorImagenImg').attr('src', url);
    $('#modalVisorImagen').modal('show');
}

// ── GESTOR DE TAREAS ACTIONS ──────────────────────────────────────────────
function openModalNuevaTarea() {
    $('#formTarea')[0].reset();
    $('#tarea_id').val('');
    $('#modalTareaTitle').html('<i class="bi bi-list-check text-primary"></i> <span>Nueva Tarea</span>');
    $('#modalTarea').modal('show');
}

$('#formTarea').on('submit', function(e) {
    e.preventDefault();
    const id = $('#tarea_id').val();
    const url = id ? `/notas/tareas/${id}` : '/notas/tareas';

    const data = $(this).serializeArray();
    if (id) data.push({ name: '_method', value: 'PUT' });

    $.ajax({
        url: url,
        type: 'POST',
        data: data,
        success: function() {
            $('#modalTarea').modal('hide');
            location.reload();
        },
        error: function(xhr) {
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
        type: 'POST',
        data: {
            _token: '{{ csrf_token() }}',
            _method: 'PUT',
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
        text: 'La tarea será removida del gestor.',
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
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'DELETE'
                },
                success: function() {
                    $(`#tarea-row-${id}`).fadeOut(200, function() { $(this).remove(); });
                }
            });
        }
    });
}
</script>
@endpush
@endsection
