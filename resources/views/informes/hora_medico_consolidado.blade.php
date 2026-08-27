@extends('layouts.app')

@section('title', 'Informe Oficial de Observaciones - Hora Médico')

@section('content')
    <style>
        .header-actions-row {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            flex-wrap: nowrap !important;
            gap: 6px !important;
            white-space: nowrap !important;
        }

        /* ─── Navigation Tabs ─── */
        .nav-tab-group {
            display: inline-flex !important;
            align-items: center !important;
            background: var(--bg-surface-alt, #e2e8f0) !important;
            padding: 3px !important;
            border-radius: 10px !important;
            border: 1px solid var(--border-color, #cbd5e1) !important;
            gap: 3px !important;
        }

        html.dark .nav-tab-group,
        [data-theme="dark"] .nav-tab-group {
            background: #1e293b !important;
            border-color: #334155 !important;
        }

        .nav-tab-btn {
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            padding: 5px 12px !important;
            font-size: 0.75rem !important;
            font-weight: 700 !important;
            border-radius: 7px !important;
            text-decoration: none !important;
            transition: all 0.18s ease-in-out !important;
            white-space: nowrap !important;
        }

        .nav-tab-btn.active {
            background: #2563eb !important;
            color: #ffffff !important;
            box-shadow: 0 1px 3px rgba(37, 99, 235, 0.35) !important;
            border: 1px solid #1d4ed8 !important;
        }

        .nav-tab-btn.active i {
            color: #ffffff !important;
        }

        .nav-tab-btn:not(.active) {
            background: var(--bg-surface, #ffffff) !important;
            color: var(--text-primary, #334155) !important;
            border: 1px solid var(--border-color, #cbd5e1) !important;
        }

        .nav-tab-btn:not(.active):hover {
            background: var(--bg-surface-alt, #f1f5f9) !important;
            color: var(--text-primary, #0f172a) !important;
            transform: translateY(-1px) !important;
        }

        html.dark .nav-tab-btn:not(.active),
        [data-theme="dark"] .nav-tab-btn:not(.active) {
            background: #334155 !important;
            color: #f8fafc !important;
            border-color: #475569 !important;
        }

        html.dark .nav-tab-btn:not(.active):hover,
        [data-theme="dark"] .nav-tab-btn:not(.active):hover {
            background: #475569 !important;
            color: #ffffff !important;
        }

        .btn-header-purple {
            background: linear-gradient(135deg, #6366f1, #4f46e5) !important;
            color: #ffffff !important;
            border: none !important;
            padding: 6px 12px !important;
            border-radius: 8px !important;
            text-decoration: none !important;
            white-space: nowrap !important;
            display: inline-flex !important;
            align-items: center !important;
            gap: 6px !important;
            font-size: 0.78rem !important;
            font-weight: 700 !important;
            box-shadow: 0 1px 3px rgba(79, 70, 229, 0.35) !important;
            transition: all 0.18s ease-in-out !important;
        }

        .btn-header-purple:hover {
            background: linear-gradient(135deg, #4f46e5, #4338ca) !important;
            color: #ffffff !important;
            transform: translateY(-1px) !important;
            box-shadow: 0 4px 6px rgba(79, 70, 229, 0.4) !important;
        }

        .btn-header-purple i {
            color: #ffffff !important;
        }

        #tableContainer {
            flex: 1 1 0% !important;
            min-height: 0 !important;
            min-width: 0 !important;
            width: 100% !important;
            overflow: auto !important;
            position: relative !important;
            scrollbar-width: thin;
            scrollbar-color: #94a3b8 var(--bg-surface, #f8fafc);
            background: var(--bg-surface);
            border: 1px solid var(--border-color);
            border-radius: var(--radius-md, 10px);
            padding: 0;
        }

        #consolidadoTable {
            border: 1px solid #94a3b8 !important;
            border-collapse: separate !important;
            border-spacing: 0 !important;
            width: 100% !important;
            margin-bottom: 0 !important;
        }

        html.dark #consolidadoTable,
        [data-theme="dark"] #consolidadoTable {
            border: 1px solid #475569 !important;
        }

        #consolidadoTable thead th {
            border: 1px solid #94a3b8 !important;
            vertical-align: middle !important;
            background-color: var(--bg-surface-alt, #e2e8f0) !important;
            color: var(--text-primary, #0f172a) !important;
            font-weight: 800 !important;
            font-size: 0.78rem !important;
            padding: 6px 8px !important;
            text-transform: uppercase;
            position: sticky !important;
            top: 0 !important;
            z-index: 20 !important;
        }

        #consolidadoTable thead th.sticky-col-1 {
            position: sticky !important;
            left: 0 !important;
            top: 0 !important;
            z-index: 80 !important;
            width: 44px !important;
            min-width: 44px !important;
            max-width: 44px !important;
            background-color: var(--bg-surface-alt, #e2e8f0) !important;
        }

        #consolidadoTable thead th.sticky-col-2,
        #consolidadoTable thead th.col-medico-name {
            position: sticky !important;
            left: 44px !important;
            top: 0 !important;
            z-index: 80 !important;
            width: 320px !important;
            min-width: 320px !important;
            max-width: 320px !important;
            background-color: var(--bg-surface-alt, #e2e8f0) !important;
        }

        html.dark #consolidadoTable thead th,
        [data-theme="dark"] #consolidadoTable thead th {
            background-color: #1e293b !important;
            color: #f8fafc !important;
            border: 1px solid #475569 !important;
        }

        html.dark #consolidadoTable thead th.sticky-col-1,
        html.dark #consolidadoTable thead th.sticky-col-2,
        [data-theme="dark"] #consolidadoTable thead th.sticky-col-1,
        [data-theme="dark"] #consolidadoTable thead th.sticky-col-2 {
            background-color: #1e293b !important;
        }

        #consolidadoTable tbody td {
            vertical-align: middle !important;
            font-size: 0.84rem !important;
            font-weight: 600 !important;
            padding: 3px 6px !important;
            border: 1px solid #94a3b8 !important;
            color: var(--text-primary) !important;
            background-color: var(--bg-surface) !important;
        }

        #consolidadoTable tbody td.sticky-col-1 {
            position: sticky !important;
            left: 0 !important;
            z-index: 25 !important;
            width: 44px !important;
            min-width: 44px !important;
            max-width: 44px !important;
            background-color: var(--bg-surface) !important;
            box-sizing: border-box !important;
        }

        #consolidadoTable tbody td.sticky-col-2,
        #consolidadoTable tbody td.col-medico-name {
            position: sticky !important;
            left: 44px !important;
            z-index: 25 !important;
            width: 320px !important;
            min-width: 320px !important;
            max-width: 320px !important;
            background-color: var(--bg-surface) !important;
            text-align: left !important;
            padding-left: 14px !important;
            box-sizing: border-box !important;
            white-space: nowrap !important;
            overflow: hidden !important;
            text-overflow: ellipsis !important;
        }

        html.dark #consolidadoTable tbody td,
        [data-theme="dark"] #consolidadoTable tbody td {
            border: 1px solid #475569 !important;
            background-color: var(--bg-surface) !important;
        }

        html.dark #consolidadoTable tbody td.sticky-col-1,
        html.dark #consolidadoTable tbody td.sticky-col-2,
        [data-theme="dark"] #consolidadoTable tbody td.sticky-col-1,
        [data-theme="dark"] #consolidadoTable tbody td.sticky-col-2 {
            background-color: #0f172a !important;
        }

        #consolidadoTable tbody tr:hover td {
            background-color: rgba(99, 102, 241, 0.04) !important;
        }

        #consolidadoTable tbody tr:hover td.sticky-col-1,
        #consolidadoTable tbody tr:hover td.sticky-col-2 {
            background-color: var(--bg-surface-alt, #e2e8f0) !important;
        }

        html.dark #consolidadoTable tbody tr:hover td,
        [data-theme="dark"] #consolidadoTable tbody tr:hover td {
            background-color: rgba(99, 102, 241, 0.08) !important;
        }

        html.dark #consolidadoTable tbody tr:hover td.sticky-col-1,
        html.dark #consolidadoTable tbody tr:hover td.sticky-col-2,
        [data-theme="dark"] #consolidadoTable tbody tr:hover td.sticky-col-1,
        [data-theme="dark"] #consolidadoTable tbody tr:hover td.sticky-col-2 {
            background-color: #1e293b !important;
        }

        /* ─── Observación como Texto Plano Editable en Tabla ─── */
        .obs-plain-input {
            width: 100% !important;
            background: transparent !important;
            border: none !important;
            box-shadow: none !important;
            outline: none !important;
            padding: 4px 6px !important;
            font-size: 0.83rem !important;
            font-weight: 600 !important;
            color: var(--text-primary) !important;
            font-family: inherit !important;
            text-transform: uppercase !important;
            letter-spacing: 0.2px !important;
            border-radius: 4px !important;
            transition: background-color 0.18s ease, box-shadow 0.18s ease !important;
        }

        .obs-plain-input:focus {
            background-color: var(--input-bg, rgba(255, 255, 255, 0.08)) !important;
            box-shadow: inset 0 0 0 1px #6366f1 !important;
        }

        .obs-plain-input::placeholder {
            color: var(--text-muted, #94a3b8) !important;
            font-weight: 400 !important;
            font-style: italic !important;
            text-transform: none !important;
            opacity: 0.65 !important;
        }

        /* ─── Botón de Editar Observación (100% visible, nítido y estilizado) ─── */
        .btn-obs-edit {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 28px !important;
            height: 24px !important;
            padding: 0 !important;
            border: 1px solid rgba(99, 102, 241, 0.3) !important;
            background: rgba(99, 102, 241, 0.12) !important;
            color: #4f46e5 !important;
            opacity: 1 !important;
            border-radius: 6px !important;
            cursor: pointer !important;
            transition: all 0.18s ease-in-out !important;
            flex-shrink: 0 !important;
        }

        html.dark .btn-obs-edit,
        [data-theme="dark"] .btn-obs-edit {
            background: rgba(99, 102, 241, 0.25) !important;
            border-color: rgba(129, 140, 248, 0.45) !important;
            color: #a5b4fc !important;
        }

        .btn-obs-edit:hover {
            transform: translateY(-1px) scale(1.08) !important;
            color: #ffffff !important;
            background: linear-gradient(135deg, #4f46e5, #6366f1) !important;
            border-color: #6366f1 !important;
            box-shadow: 0 2px 6px rgba(79, 70, 229, 0.45) !important;
        }

        .btn-obs-edit:hover i {
            color: #ffffff !important;
        }

        .btn-obs-edit i {
            font-size: 0.85rem !important;
            color: inherit !important;
        }

        /* Modal Hiding & Display */
        .modal:not(.show) {
            display: none !important;
        }
    </style>

    <div class="informe-page-wrapper" id="report-wrapper">
        <!-- Header -->
        <div class="informe-header no-print">
            <div class="flex items-center gap-3 shrink-0">
                <h2 style="margin: 0; font-size: 1rem; font-weight: 800; display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; color: var(--text-primary);">
                    <i class="bi bi-journal-text text-primary"></i> Observaciones (Rendimiento Médico)
                </h2>

                {{-- Navigation Tabs --}}
                <div class="nav-tab-group">
                    <a href="{{ route('informes.hora-medico.consolidado', ['ano' => $ano, 'mes' => $mes, 'jornada' => ($jornada === 'SERVICIO SOCIAL' ? 'MATUTINA' : $jornada)]) }}"
                        class="nav-tab-btn {{ $jornada !== 'SERVICIO SOCIAL' ? 'active' : '' }}">
                        <i class="bi bi-people-fill"></i> Generales y Especialistas
                    </a>
                    <a href="{{ route('informes.hora-medico.consolidado', ['ano' => $ano, 'mes' => $mes, 'jornada' => 'SERVICIO SOCIAL']) }}"
                        class="nav-tab-btn {{ $jornada === 'SERVICIO SOCIAL' ? 'active' : '' }}">
                        <i class="bi bi-mortarboard-fill"></i> Servicio Social
                    </a>
                </div>
            </div>

            <div class="header-actions-row">
                <!-- Botón Volver a Hora Médico -->
                <a href="{{ route('informes.hora-medico', ['ano' => $ano, 'mes' => $mes, 'jornada' => ($jornada === 'SERVICIO SOCIAL' ? 'MATUTINA' : $jornada)]) }}"
                    class="btn-header-purple" title="Regresar a Rendimiento Médico (Hora Médico)">
                    <i class="bi bi-clock-history"></i> Hora Médico
                </a>

                <!-- Botón Nueva Observación -->
                <button type="button" class="btn btn-primary btn-sm flex items-center gap-1 font-bold shadow-sm"
                    onclick="abrirModalNuevaObservacion()" style="white-space: nowrap;"
                    title="Agregar o editar observación">
                    <i class="bi bi-plus-circle text-xs"></i> Nueva Observación
                </button>

                <!-- Botón Incluir Médico -->
                <button type="button" class="btn btn-primary btn-sm flex items-center gap-1 shadow-sm"
                    style="white-space: nowrap;" data-toggle="modal" data-target="#addMedicoModal"
                    title="Incluir Médico al Informe">
                    <i class="bi bi-person-plus text-xs"></i> Incluir Médico
                </button>

                <!-- Botón Director del Mes -->
                <button type="button"
                    class="btn btn-warning btn-sm text-dark font-weight-bold flex items-center gap-1 shadow-sm"
                    style="white-space: nowrap;" data-toggle="modal" data-target="#directorMensualModal"
                    title="Asignar Director del Mes ({{ $mes }} {{ $ano }})">
                    <i class="bi bi-person-badge text-xs"></i> Director del Mes
                </button>

                <!-- Botón Exportar Excel -->
                <button type="button" class="btn btn-success btn-sm flex items-center gap-1 shadow-sm"
                    style="white-space: nowrap;" data-toggle="modal" data-target="#exportExcelModal"
                    title="Exportar a Excel">
                    <i class="bi bi-file-earmark-excel"></i> Excel
                </button>

                <button type="button" onclick="imprimirReporteConsolidado()" class="btn-action-print"
                    title="Imprimir Reporte">
                    <i class="bi bi-printer"></i>
                </button>

                <button type="button" onclick="toggleFullScreen()" class="btn-action-fullscreen" title="Pantalla Completa">
                    <i class="bi bi-arrows-fullscreen" id="fullScreenIcon"></i>
                </button>

                <button type="button" id="btn-refresh-report" class="btn btn-subtle btn-sm font-semibold"
                    onclick="updateFilters()">
                    <i class="bi bi-arrow-clockwise mr-1"></i> Actualizar
                </button>
            </div>
        </div>

        <!-- Barra de Filtros -->
        <div class="filter-container no-print">
            <div class="flex flex-1 items-center gap-2 mb-0 flex-wrap">
                @if($jornada !== 'SERVICIO SOCIAL')
                    <div class="flex items-center gap-1.5">
                        <span class="text-[11px] font-bold uppercase text-slate-500 dark:text-slate-400">Jornada:</span>
                        <div style="width: 155px;">
                            <select name="jornada" class="filter-select w-full" onchange="updateFilters()">
                                <option value="MATUTINA" {{ $jornada == 'MATUTINA' ? 'selected' : '' }}>MATUTINA</option>
                                <option value="VESPERTINA" {{ $jornada == 'VESPERTINA' ? 'selected' : '' }}>VESPERTINA</option>
                                <option value="FIN DE SEMANA" {{ $jornada == 'FIN DE SEMANA' ? 'selected' : '' }}>FIN DE SEMANA
                                </option>
                                <option value="TOTAL JORNADAS" {{ ($jornada == 'TOTAL JORNADAS' || $jornada == 'TODAS LAS JORNADAS') ? 'selected' : '' }}>TOTAL JORNADAS</option>
                            </select>
                        </div>
                    </div>
                @else
                    <input type="hidden" name="jornada" value="SERVICIO SOCIAL">
                    <div class="flex items-center gap-1.5">
                        <span class="text-[11px] font-bold uppercase text-slate-500 dark:text-slate-400">Jornada:</span>
                        <span class="badge badge-info font-bold px-2.5 py-1 text-xs"
                            style="background: rgba(59,130,246,0.15); color: #3b82f6; border: 1px solid rgba(59,130,246,0.3); border-radius: 6px;">
                            <i class="bi bi-mortarboard-fill mr-1"></i> SERVICIO SOCIAL
                        </span>
                    </div>
                @endif

                <div class="flex items-center gap-1.5">
                    <span class="text-[11px] font-bold uppercase text-slate-500 dark:text-slate-400">Mes:</span>
                    <div style="width: 125px;">
                        <select name="mes" class="filter-select w-full" onchange="updateFilters()">
                            @foreach($meses as $m)
                                <option value="{{ $m }}" {{ $mes == $m ? 'selected' : '' }}>{{ $m }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-1.5">
                    <span class="text-[11px] font-bold uppercase text-slate-500 dark:text-slate-400">Año:</span>
                    <div style="width: 88px;">
                        <select name="ano" class="filter-select w-full" onchange="updateFilters()">
                            @foreach($anos as $a)
                                <option value="{{ $a }}" {{ $ano == $a ? 'selected' : '' }}>{{ $a }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-1.5 ml-auto">
                    <span class="text-[11px] font-bold uppercase text-slate-500 dark:text-slate-400">Buscar Médico:</span>
                    <div style="width: 240px;">
                        <input type="text" id="nombre_medico" class="filter-select w-full"
                            placeholder="Escriba para filtrar..." oninput="filterDoctorsClient(this.value)">
                    </div>
                </div>
            </div>
        </div>

        <!-- Dynamic Content Area -->
        <div id="dynamic-content"
            style="flex: 1 1 0%; min-height: 0; min-width: 0; display: flex; flex-direction: column; overflow: hidden; position: relative;">
            <!-- Loading Overlay -->
            <div id="table-loader"
                style="display: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: var(--bg-surface); opacity: 0.85; z-index: 1000; align-items: center; justify-content: center;">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Cargando...</span>
                </div>
            </div>

            <div class="table-responsive" id="tableContainer">
                <div id="consolidadoContent" class="w-full">
                    @include('informes.hora_medico_consolidado_table')
                </div>
            </div>
        </div>
    </div>

    <!-- Modales Globales de Hora Médico -->
    @include('informes.hora_medico_modales')

    <!-- Modal para Agregar / Editar Observación Detallada -->
    <div class="modal fade" id="modalNuevaObservacion" tabindex="-1" role="dialog"
        aria-labelledby="modalNuevaObservacionLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content rounded-2xl border shadow-2xl overflow-hidden"
                style="background: var(--bg-surface); color: var(--text-primary); border-color: var(--border-color);">
                <div class="modal-header py-3 px-4 flex items-center justify-between"
                    style="background: var(--bg-surface-alt, #e2e8f0); border-bottom: 1px solid var(--border-color);">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-sm shadow-xs"
                            style="background: linear-gradient(135deg, #6366f1, #4f46e5); color: #fff;">
                            <i class="bi bi-chat-left-text-fill"></i>
                        </div>
                        <div>
                            <h5 class="modal-title font-bold text-sm mb-0" id="modalNuevaObservacionLabel"
                                style="color: var(--text-primary);">
                                Observación de Rendimiento Médico
                            </h5>
                            <p class="text-[10px] font-semibold mb-0 uppercase tracking-wider"
                                style="color: var(--text-muted);">
                                Período: <span class="text-primary font-bold" id="modal_periodo_text">{{ $mes }}
                                    {{ $ano }}</span> - Jornada: <span class="text-primary font-bold"
                                    id="modal_jornada_text">{{ $jornada }}</span>
                            </p>
                        </div>
                    </div>
                    <button type="button" class="close text-xl p-1 outline-none border-none bg-transparent"
                        data-dismiss="modal" aria-label="Close" style="color: var(--text-primary); opacity: 0.7;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 space-y-3 text-xs" style="background: var(--bg-surface);">
                    <!-- Selector de Médico -->
                    <div class="mb-3">
                        <label for="obs_modal_medico_id" class="block font-bold uppercase tracking-wider text-[11px] mb-1"
                            style="color: var(--text-primary);">
                            Seleccionar Médico:
                        </label>
                        <select id="obs_modal_medico_id"
                            class="form-control text-xs rounded-xl border font-semibold py-1.5 px-2.5 w-full"
                            style="background: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); height: 38px;"
                            onchange="onModalMedicoChange()">
                            <option value="">-- Seleccione un médico --</option>
                            @foreach($todosLosMedicos as $m)
                                <option value="{{ $m->id }}" data-static="{{ $m->observaciones ?? '' }}">
                                    {{ $m->NOM_MED }} {{ !empty($m->ESPECIALIDAD) ? '(' . $m->ESPECIALIDAD . ')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Observación Fija / Perfil del Médico -->
                    <div id="obs_static_wrap" class="p-2.5 rounded-xl border mb-3 hidden"
                        style="background: var(--bg-surface-alt); border-color: var(--border-color);">
                        <span class="text-[10px] font-bold uppercase tracking-wider block mb-0.5"
                            style="color: var(--text-muted);">
                            <i class="bi bi-person-vcard text-primary mr-1"></i> Observación Fija en Perfil del Médico:
                        </span>
                        <span id="obs_static_text" class="text-xs font-semibold uppercase"
                            style="color: var(--text-primary);"></span>
                    </div>

                    <!-- Texto de la Observación Mensual -->
                    <div class="mb-3">
                        <label for="obs_modal_texto" class="block font-bold uppercase tracking-wider text-[11px] mb-1"
                            style="color: var(--text-primary);">
                            Observación del Mes:
                        </label>
                        <textarea id="obs_modal_texto" rows="3"
                            class="form-control text-xs rounded-xl border font-medium p-2.5 w-full"
                            style="background: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); text-transform: uppercase;"
                            placeholder="Escriba la observación para este médico en este mes (ej. VACACIONES DEL 01 AL 15, INCAPACIDAD, etc.)..."></textarea>
                    </div>

                    <!-- Plantillas / Sugerencias Rápidas -->
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider block mb-1.5"
                            style="color: var(--text-muted);">Sugerencias Rápidas:</span>
                        <div class="d-flex flex-wrap" style="gap: 5px;">
                            <button type="button" class="btn btn-subtle btn-xs font-semibold py-1 px-2"
                                style="font-size: 0.72rem; border-radius: 6px;" onclick="appendTag('VACACIONES')">+
                                Vacaciones</button>
                            <button type="button" class="btn btn-subtle btn-xs font-semibold py-1 px-2"
                                style="font-size: 0.72rem; border-radius: 6px;" onclick="appendTag('INCAPACIDAD')">+
                                Incapacidad</button>
                            <button type="button" class="btn btn-subtle btn-xs font-semibold py-1 px-2"
                                style="font-size: 0.72rem; border-radius: 6px;" onclick="appendTag('PERMISO PERSONAL')">+
                                Permiso Personal</button>
                            <button type="button" class="btn btn-subtle btn-xs font-semibold py-1 px-2"
                                style="font-size: 0.72rem; border-radius: 6px;" onclick="appendTag('CONGRESO MEDICO')">+
                                Congreso Médico</button>
                            <button type="button" class="btn btn-subtle btn-xs font-semibold py-1 px-2"
                                style="font-size: 0.72rem; border-radius: 6px;" onclick="appendTag('DIRECTOR DE CIS')">+
                                Director CIS</button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-t py-2.5 px-4 flex items-center justify-between"
                    style="background: var(--bg-surface-alt); border-color: var(--border-color);">
                    <button type="button" class="btn btn-subtle btn-sm font-semibold px-3 py-1.5" data-dismiss="modal"
                        style="border-radius: 8px;">
                        Cancelar
                    </button>
                    <button type="button"
                        class="btn btn-primary btn-sm font-bold px-4 py-1.5 flex items-center gap-1 shadow-sm"
                        style="border-radius: 8px;" onclick="guardarDesdeModalObservacion()">
                        <i class="bi bi-check-circle-fill mr-1"></i> Guardar Observación
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function updateFilters() {
            let ano = $('[name="ano"]').val();
            let mes = $('[name="mes"]').val();
            let jornada = $('[name="jornada"]').val();
            let nombre = $('#nombre_medico').val();

            $('#table-loader').css('display', 'flex').fadeIn(150);

            $.get("{{ route('informes.hora-medico.consolidado') }}", {
                ano: ano,
                mes: mes,
                jornada: jornada,
                nombre: nombre
            }, function (html) {
                $('#consolidadoContent').html(html);
                $('#table-loader').fadeOut(150);

                // Actualizar etiquetas en modales
                $('#modal_periodo_text').text(mes + ' ' + ano);
                $('#modal_jornada_text').text(jornada);

                // Actualizar URL sin recargar
                const url = new URL(window.location);
                url.searchParams.set('ano', ano);
                url.searchParams.set('mes', mes);
                url.searchParams.set('jornada', jornada);
                window.history.pushState({}, '', url);
            }).fail(function () {
                $('#table-loader').fadeOut(150);
                Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudieron actualizar los datos.' });
            });
        }

        function filterDoctorsClient(query) {
            let q = query.trim().toUpperCase();
            $('.medico-obs-row').each(function () {
                let name = $(this).data('name') || '';
                let obs = $(this).find('.obs-plain-input').val() || '';
                if (name.includes(q) || obs.toUpperCase().includes(q) || q === '') {
                    $(this).show();
                } else {
                    $(this).hide();
                }
            });
        }

        function toggleFullScreen() {
            const elem = document.getElementById('report-wrapper');
            const icon = document.getElementById('fullScreenIcon');

            if (!document.fullscreenElement) {
                elem.requestFullscreen().catch(err => {
                    alert(`Error al intentar pantalla completa: ${err.message}`);
                });
                if (icon) {
                    icon.classList.remove('bi-arrows-fullscreen');
                    icon.classList.add('bi-fullscreen-exit');
                }
            } else {
                document.exitFullscreen();
                if (icon) {
                    icon.classList.remove('bi-fullscreen-exit');
                    icon.classList.add('bi-arrows-fullscreen');
                }
            }
        }

        document.addEventListener('fullscreenchange', () => {
            const icon = document.getElementById('fullScreenIcon');
            if (!document.fullscreenElement && icon) {
                icon.classList.remove('bi-fullscreen-exit');
                icon.classList.add('bi-arrows-fullscreen');
            }
        });

        function imprimirReporteConsolidado() {
            let ano = $('[name="ano"]').val();
            let mes = $('[name="mes"]').val();
            let jornada = $('[name="jornada"]').val();

            const url = new URL("{{ route('informes.hora-medico.consolidado.imprimir') }}");
            url.searchParams.set('ano', ano);
            url.searchParams.set('mes', mes);
            url.searchParams.set('jornada', jornada);

            window.open(url.toString(), '_blank');
        }

        // Modal de Observaciones
        function abrirModalNuevaObservacion() {
            $('#obs_modal_medico_id').val('').trigger('change');
            $('#obs_static_wrap').addClass('hidden');
            $('#obs_static_text').text('');
            $('#obs_modal_texto').val('');
            $('#modalNuevaObservacion').modal('show');
        }

        function abrirModalObservacion(medicoId, medicoName, staticObs, dinamicObs) {
            $('#obs_modal_medico_id').val(medicoId);
            if (staticObs && staticObs.trim()) {
                $('#obs_static_text').text(staticObs.trim());
                $('#obs_static_wrap').removeClass('hidden');
            } else {
                $('#obs_static_wrap').addClass('hidden');
                $('#obs_static_text').text('');
            }
            $('#obs_modal_texto').val(dinamicObs || '');
            $('#modalNuevaObservacion').modal('show');
        }

        function onModalMedicoChange() {
            const select = $('#obs_modal_medico_id');
            const medicoId = select.val();
            const selectedOpt = select.find('option:selected');
            const staticObs = selectedOpt.data('static') || '';

            if (staticObs && String(staticObs).trim()) {
                $('#obs_static_text').text(String(staticObs).trim());
                $('#obs_static_wrap').removeClass('hidden');
            } else {
                $('#obs_static_wrap').addClass('hidden');
                $('#obs_static_text').text('');
            }

            if (!medicoId) {
                $('#obs_modal_texto').val('');
                return;
            }

            const ano = $('[name="ano"]').val();
            const mes = $('[name="mes"]').val();

            $.get("{{ route('informes.hora-medico.get-hsc') }}", {
                medico_id: medicoId,
                ano: ano,
                mes: mes
            }, function (data) {
                if (data && data.observaciones !== undefined) {
                    $('#obs_modal_texto').val(data.observaciones || '');
                } else {
                    $('#obs_modal_texto').val('');
                }
            });
        }

        function appendTag(tag) {
            const txt = $('#obs_modal_texto');
            let current = txt.val().trim();
            if (current) {
                if (!current.toUpperCase().includes(tag.toUpperCase())) {
                    txt.val(current + ', ' + tag);
                }
            } else {
                txt.val(tag);
            }
            txt.focus();
        }

        function guardarDesdeModalObservacion() {
            const medicoId = $('#obs_modal_medico_id').val();
            if (!medicoId) {
                Swal.fire({ icon: 'warning', title: 'Atención', text: 'Por favor seleccione un médico.' });
                return;
            }

            const dinamica = $('#obs_modal_texto').val().trim();
            const ano = $('[name="ano"]').val();
            const mes = $('[name="mes"]').val();

            Swal.fire({ title: 'Guardando...', didOpen: () => { Swal.showLoading(); } });

            $.ajax({
                url: "{{ route('informes.hora-medico.save-observacion') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    medico_id: medicoId,
                    ano: ano,
                    mes: mes,
                    observaciones: dinamica
                },
                success: function (response) {
                    Swal.close();
                    $('#modalNuevaObservacion').modal('hide');
                    Swal.fire({ icon: 'success', title: 'Observación Guardada', timer: 1000, showConfirmButton: false });
                    updateFilters();
                },
                error: function (err) {
                    Swal.close();
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo guardar la observación.' });
                }
            });
        }

        function guardarObservacionConsolidado(medicoId, fullText, staticPrefix, inputElem) {
            const ano = $('[name="ano"]').val();
            const mes = $('[name="mes"]').val();

            let dinamica = fullText.trim();
            if (staticPrefix && dinamica.toUpperCase().startsWith(staticPrefix.toUpperCase())) {
                dinamica = dinamica.slice(staticPrefix.length).replace(/^\s*\|\s*/, '').replace(/^\s*,\s*/, '').trim();
            }

            if (inputElem) {
                $(inputElem).css('background-color', 'rgba(59, 130, 246, 0.15)');
            }

            $.ajax({
                url: "{{ route('informes.hora-medico.save-observacion') }}",
                method: "POST",
                data: {
                    _token: "{{ csrf_token() }}",
                    medico_id: medicoId,
                    ano: ano,
                    mes: mes,
                    observaciones: dinamica
                },
                success: function (response) {
                    if (inputElem) {
                        $(inputElem).css('background-color', 'rgba(16, 185, 129, 0.18)');
                        setTimeout(() => {
                            $(inputElem).css('background-color', 'transparent');
                        }, 800);
                    }
                },
                error: function (err) {
                    console.error('Error al guardar la observación:', err);
                    if (inputElem) {
                        $(inputElem).css('background-color', 'rgba(239, 68, 68, 0.2)');
                    }
                    Swal.fire({ icon: 'error', title: 'Error', text: 'No se pudo guardar la observación.' });
                }
            });
        }

        function agregarMedicoTable() {
            let medicoId = $('#select_add_medico').val();
            let currentAno = $('[name="ano"]').val();
            let currentMes = $('[name="mes"]').val();

            if (!medicoId) {
                Swal.fire({ icon: 'warning', title: 'Atención', text: 'Debe seleccionar un médico' });
                return;
            }

            Swal.fire({ title: 'Procesando...', didOpen: () => { Swal.showLoading(); } });

            $.post("{{ route('informes.hora-medico.add-medico-hsc') }}", {
                _token: "{{ csrf_token() }}",
                medico_id: medicoId,
                ano: currentAno,
                mes: currentMes
            }, function (res) {
                Swal.close();
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Médico Agregado', timer: 1200, showConfirmButton: false })
                        .then(() => {
                            $('#addMedicoModal').modal('hide');
                            updateFilters();
                        });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.error });
                }
            });
        }

        function guardarDirectorMensual(e) {
            e.preventDefault();
            let formData = $('#formDirectorMensual').serialize();

            Swal.fire({ title: 'Guardando...', didOpen: () => { Swal.showLoading(); } });

            $.post("{{ route('informes.hora-medico.save-director-mensual') }}", formData, function (res) {
                Swal.close();
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Director Asignado', timer: 1200, showConfirmButton: false })
                        .then(() => {
                            $('#directorMensualModal').modal('hide');
                            updateFilters();
                        });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.error });
                }
            });
        }
    </script>
@endsection