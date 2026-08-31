@extends('layouts.app')

@section('title', 'Rendimiento Médico (Hora Médico) - Estadísticas 1.7')

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

        #mainTable th.vertical-text,
        #mainTable .vertical-text,
        .vertical-text {
            writing-mode: vertical-rl !important;
            transform: rotate(180deg) !important;
            white-space: normal !important;
            word-break: normal !important;
            font-size: 0.54rem !important;
            font-weight: 700 !important;
            text-align: center !important;
            vertical-align: middle !important;
            padding: 2px 1px !important;
            letter-spacing: 0px !important;
            line-height: 1.08 !important;
        }

        #mainTable thead tr.header-row-main th {
            height: 26px !important;
            font-size: 0.74rem !important;
            font-weight: 800 !important;
            padding: 3px 4px !important;
            vertical-align: middle !important;
        }

        #mainTable thead tr.header-row-mid th {
            height: 22px !important;
            font-size: 0.70rem !important;
            font-weight: 800 !important;
            padding: 2px 4px !important;
            vertical-align: middle !important;
        }

        #mainTable thead tr.header-row-sub th {
            height: 100px !important;
            font-size: 0.70rem !important;
            max-height: 100px !important;
            vertical-align: middle !important;
            padding: 2px 1px !important;
        }

        #mainTable {
            border: 1px solid #94a3b8 !important;
            border-collapse: separate !important;
            border-spacing: 0 !important;
        }

        html.dark #mainTable,
        [data-theme="dark"] #mainTable {
            border: 1px solid #475569 !important;
        }

        #mainTable thead th {
            border: 1px solid #94a3b8 !important;
            vertical-align: middle !important;
        }

        html.dark #mainTable thead th,
        [data-theme="dark"] #mainTable thead th {
            border: 1px solid #475569 !important;
        }

        #mainTable tbody td {
            vertical-align: middle !important;
            font-size: 0.86rem !important;
            font-weight: 600 !important;
            padding: 2px 3px !important;
            border: 1px solid #94a3b8 !important;
            color: var(--text-primary) !important;
        }

        html.dark #mainTable tbody td,
        [data-theme="dark"] #mainTable tbody td {
            border: 1px solid #475569 !important;
        }

        #mainTable th.sticky-col-2,
        #mainTable td.sticky-col-2,
        #mainTable .sticky-col-2 {
            text-align: left !important;
            padding-left: 10px !important;
            padding-right: 6px !important;
        }

        #mainTable tbody td.sticky-col-2 {
            font-size: 0.80rem !important;
            font-weight: 700 !important;
            text-align: left !important;
            padding-left: 10px !important;
            padding-right: 6px !important;
        }

        /* ─── Botón de Acción en Fila (Diseño y Color Premium) ─── */
        .btn-hsc-modal {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 26px !important;
            height: 22px !important;
            padding: 0 !important;
            border-radius: 6px !important;
            background: linear-gradient(135deg, #4f46e5, #6366f1) !important;
            color: #ffffff !important;
            border: 1px solid rgba(255, 255, 255, 0.2) !important;
            box-shadow: 0 1px 3px rgba(79, 70, 229, 0.35) !important;
            transition: all 0.18s ease-in-out !important;
            cursor: pointer !important;
        }

        .btn-hsc-modal:hover {
            background: linear-gradient(135deg, #3730a3, #4f46e5) !important;
            transform: translateY(-1px) scale(1.08) !important;
            box-shadow: 0 4px 8px rgba(79, 70, 229, 0.45) !important;
            color: #ffffff !important;
        }

        .btn-hsc-modal:active {
            transform: translateY(0) scale(0.96) !important;
        }

        .btn-hsc-modal i {
            font-size: 0.72rem !important;

            /* ─── Columnas Fijas (Sticky Columns) ─── */
            .sticky-col-1,
            #mainTable th.sticky-col-1,
            #mainTable td.sticky-col-1 {
                position: sticky !important;
                left: 0 !important;
                width: 36px !important;
                min-width: 36px !important;
                max-width: 36px !important;
                background-color: var(--bg-surface, #ffffff) !important;
                z-index: 30 !important;
                text-align: center !important;
                font-size: 0.80rem !important;
                box-sizing: border-box !important;
            }

            .sticky-col-2,
            #mainTable th.sticky-col-2,
            #mainTable td.sticky-col-2,
            #mainTable .sticky-col-2 {
                position: sticky !important;
                left: 36px !important;
                width: 230px !important;
                min-width: 230px !important;
                max-width: 230px !important;
                background-color: var(--bg-surface, #ffffff) !important;
                z-index: 30 !important;
                text-align: left !important;
                padding-left: 10px !important;
                padding-right: 6px !important;
                font-size: 0.82rem !important;
                white-space: nowrap !important;
                overflow: hidden !important;
                text-overflow: ellipsis !important;
                box-sizing: border-box !important;
            }

            html.dark .sticky-col-1,
            html.dark #mainTable th.sticky-col-1,
            html.dark #mainTable td.sticky-col-1,
            [data-theme="dark"] .sticky-col-1,
            [data-theme="dark"] #mainTable th.sticky-col-1,
            [data-theme="dark"] #mainTable td.sticky-col-1,
            html.dark .sticky-col-2,
            html.dark #mainTable th.sticky-col-2,
            html.dark #mainTable td.sticky-col-2,
            [data-theme="dark"] .sticky-col-2,
            [data-theme="dark"] #mainTable th.sticky-col-2,
            [data-theme="dark"] #mainTable td.sticky-col-2 {
                background-color: #0f172a !important;
                color: var(--text-primary) !important;
            }

            /* En thead, las celdas sticky de la esquina tienen z-index 100 */
            #mainTable thead th.sticky-col-1 {
                position: sticky !important;
                top: 0 !important;
                left: 0 !important;
                z-index: 100 !important;
                background-color: var(--bg-surface-alt, #e2e8f0) !important;
            }

            #mainTable thead th.sticky-col-2 {
                position: sticky !important;
                top: 0 !important;
                left: 36px !important;
                z-index: 100 !important;
                background-color: var(--bg-surface-alt, #e2e8f0) !important;
            }

            /* ─── Encabezados Fijos de 3 Filas (Sticky Multi-row Thead) ─── */
            #mainTable thead,
            #mainTable thead.thead-premium {
                position: sticky !important;
                top: 0 !important;
                z-index: 60 !important;
            }

            #mainTable thead th {
                background-color: var(--bg-surface-alt, #e2e8f0) !important;
                color: var(--text-primary, #0f172a) !important;
                border: 1px solid #94a3b8 !important;
                box-sizing: border-box !important;
            }

            #mainTable thead tr.header-row-main th {
                position: sticky !important;
                top: 0 !important;
                z-index: 60 !important;
            }

            #mainTable thead tr.header-row-mid th {
                position: sticky !important;
                top: 28px !important;
                z-index: 55 !important;
            }

            #mainTable thead tr.header-row-sub th {
                position: sticky !important;
                top: 54px !important;
                z-index: 50 !important;
            }

            html.dark #mainTable thead th,
            [data-theme="dark"] #mainTable thead th {
                background-color: #1e293b !important;
                color: #f8fafc !important;
                border: 1px solid #475569 !important;
            }

            html.dark #mainTable thead th.sticky-col-1,
            html.dark #mainTable thead th.sticky-col-2,
            [data-theme="dark"] #mainTable thead th.sticky-col-1,
            [data-theme="dark"] #mainTable thead th.sticky-col-2 {
                background-color: #1e293b !important;
            }

            /* Hover de filas: mantener opacidad sólida en celdas sticky */
            #mainTable tbody tr:hover td.sticky-col-1,
            #mainTable tbody tr:hover td.sticky-col-2 {
                background-color: var(--bg-surface-alt, #e2e8f0) !important;
            }

            html.dark #mainTable tbody tr:hover td.sticky-col-1,
            html.dark #mainTable tbody tr:hover td.sticky-col-2,
            [data-theme="dark"] #mainTable tbody tr:hover td.sticky-col-1,
            [data-theme="dark"] #mainTable tbody tr:hover td.sticky-col-2 {
                background-color: #1e293b !important;
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
            }

            #tableContainer::-webkit-scrollbar {
                width: 8px;
                height: 8px;
            }

            #tableContainer::-webkit-scrollbar-track {
                background: var(--bg-surface, #f8fafc);
            }

            #tableContainer::-webkit-scrollbar-thumb {
                background: #94a3b8;
                border-radius: 4px;
            }

            #tableContainer::-webkit-scrollbar-thumb:hover {
                background: #64748b;
            }

            /* ─── Fila de Totales Fija con Cuadrícula Completa (Sticky Tfoot) ─── */
            #mainTable tfoot {
                position: sticky !important;
                bottom: 0 !important;
                z-index: 45 !important;
            }

            #mainTable tfoot tr {
                background-color: var(--bg-surface-alt, #e2e8f0) !important;
            }

            #mainTable tfoot td {
                position: sticky !important;
                bottom: 0 !important;
                background-color: var(--bg-surface-alt, #e2e8f0) !important;
                color: var(--text-primary, #0f172a) !important;
                font-weight: 800 !important;
                font-size: 100.0rem !important;
                border: 1px solid #94a3b8 !important;
                vertical-align: middle !important;
                padding: 4px 4px !important;
                z-index: 45 !important;
            }

            html.dark #mainTable tfoot tr,
            html.dark #mainTable tfoot td,
            [data-theme="dark"] #mainTable tfoot tr,
            [data-theme="dark"] #mainTable tfoot td {
                background-color: #1e293b !important;
                color: #f8fafc !important;
                border: 1px solid #475569 !important;
            }

            #mainTable tfoot td.sticky-col-footer {
                position: sticky !important;
                left: 0 !important;
                bottom: 0 !important;
                width: 266px !important;
                min-width: 266px !important;
                max-width: 266px !important;
                z-index: 80 !important;
                background-color: var(--bg-surface-alt, #e2e8f0) !important;
                border: 1px solid #94a3b8 !important;
                box-shadow: 2px 0 4px rgba(0, 0, 0, 0.1) !important;
                font-size: 0.82rem !important;
                box-sizing: border-box !important;
            }

            html.dark #mainTable tfoot td.sticky-col-footer,
            [data-theme="dark"] #mainTable tfoot td.sticky-col-footer {
                background-color: #1e293b !important;
                color: #f8fafc !important;
                border: 1px solid #475569 !important;
            }

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
    </style>
    <div class="informe-page-wrapper" id="report-wrapper">
        <!-- Header -->
        <div class="informe-header no-print">
            <div class="flex items-center gap-3 shrink-0">
                <h2
                    style="margin: 0; font-size: 1rem; font-weight: 800; display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; color: var(--text-primary);">
                    <i class="bi bi-clock-history text-primary"></i> Rendimiento Médico (Hora Médico)
                </h2>
                {{-- Navigation Tabs --}}
                <div class="nav-tab-group">
                    <a href="{{ route('informes.hora-medico', ['ano' => $ano, 'mes' => $mesNombre, 'jornada' => $jornada]) }}"
                        class="nav-tab-btn {{ request()->routeIs('informes.hora-medico') && !request()->routeIs('informes.hora-medico.servicio-social') && !request()->routeIs('informes.hora-medico.consolidado') ? 'active' : '' }}">
                        <i class="bi bi-people-fill"></i> Generales y Especialistas
                    </a>
                    <a href="{{ route('informes.hora-medico.servicio-social', ['ano' => $ano, 'mes' => $mesNombre]) }}"
                        class="nav-tab-btn {{ request()->routeIs('informes.hora-medico.servicio-social') ? 'active' : '' }}">
                        <i class="bi bi-mortarboard-fill"></i> Servicio Social
                    </a>
                </div>
            </div>

            <div class="header-actions-row">
                <!-- Botón Observaciones (Acceso a Horas Sin Consulta / Observaciones) -->
                <a href="{{ route('informes.hora-medico.consolidado', ['ano' => $ano, 'mes' => $mesNombre, 'jornada' => $jornada]) }}"
                    class="btn-header-purple" title="Ver Informe Oficial de Observaciones">
                    <i class="bi bi-journal-text"></i> Observaciones
                </a>

                <!-- Botón Incluir Médico -->
                <button type="button" class="btn btn-primary btn-sm flex items-center gap-1 shadow-sm"
                    style="white-space: nowrap;" data-toggle="modal" data-target="#addMedicoModal"
                    title="Incluir Médico al Informe">
                    <i class="fas fa-user-plus text-xs"></i> Incluir Médico
                </button>

                <!-- Botón Director del Mes -->
                <button type="button"
                    class="btn btn-warning btn-sm text-dark font-weight-bold flex items-center gap-1 shadow-sm"
                    style="white-space: nowrap;" data-toggle="modal" data-target="#directorMensualModal"
                    title="Asignar Director del Mes ({{ $mesNombre }} {{ $ano }})">
                    <i class="fas fa-user-tie text-xs"></i> Director del Mes
                </button>

                <!-- Botón Exportar Excel -->
                <button type="button" class="btn btn-success btn-sm flex items-center gap-1 shadow-sm"
                    style="white-space: nowrap;" data-toggle="modal" data-target="#exportExcelModal"
                    title="Exportar a Excel">
                    <i class="bi bi-file-earmark-excel"></i> Excel
                </button>

                <button type="button" onclick="imprimirReporte()" class="btn-action-print" title="Imprimir Reporte">
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
                <div class="flex items-center gap-1.5">
                    <span class="text-[11px] font-bold uppercase text-slate-500 dark:text-slate-400">Jornada:</span>
                    <div style="width: 155px;">
                        <select name="jornada" class="filter-select w-full" onchange="updateFilters()">
                            <option value="MATUTINA" {{ $jornada == 'MATUTINA' ? 'selected' : '' }}>MATUTINA</option>
                            <option value="VESPERTINA" {{ $jornada == 'VESPERTINA' ? 'selected' : '' }}>VESPERTINA</option>
                            <option value="FIN DE SEMANA" {{ $jornada == 'FIN DE SEMANA' ? 'selected' : '' }}>FIN DE SEMANA
                            </option>
                            <option value="TOTAL JORNADAS" {{ $jornada == 'TOTAL JORNADAS' ? 'selected' : '' }}>TOTAL JORNADAS
                            </option>
                        </select>
                    </div>
                </div>

                <div class="flex items-center gap-1.5">
                    <span class="text-[11px] font-bold uppercase text-slate-500 dark:text-slate-400">Mes:</span>
                    <div style="width: 125px;">
                        <select name="mes" class="filter-select w-full" onchange="updateFilters()">
                            @foreach($meses as $m)
                                <option value="{{ $m }}" {{ $mesNombre == $m ? 'selected' : '' }}>{{ $m }}</option>
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
                    <div style="width: 220px;">
                        <input type="text" id="nombre_medico" class="filter-select w-full"
                            placeholder="Escriba para filtrar..." oninput="updateFilters()">
                    </div>
                </div>
            </div>
        </div>

        <!-- Dynamic Content Area (Table Container) -->
        <div id="dynamic-content"
            style="flex: 1 1 0%; min-height: 0; min-width: 0; display: flex; flex-direction: column; overflow: hidden; position: relative;">
            <!-- Loading Overlay -->
            <div id="table-loader"
                style="display: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: var(--bg-surface); opacity: 0.8; z-index: 1000; align-items: center; justify-content: center;">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Cargando...</span>
                </div>
            </div>

            <div class="table-responsive" id="tableContainer">
                <table class="table table-bordered table-sm text-center mb-0 w-full" id="mainTable"
                    style="min-width: 1350px;">
                    <thead class="thead-premium sticky-top">
                        {{-- Fila 1: Grupos Principales y Super-Header HORAS SIN CONSULTA --}}
                        <tr class="header-row-main">
                            <th rowspan="3" class="align-middle sticky-col-1"
                                style="width: 28px; min-width: 28px; z-index: 30;">#</th>
                            <th rowspan="3" class="align-middle sticky-col-2 text-left"
                                style="width: 210px; min-width: 210px; z-index: 30; padding-left: 8px !important;">NOMBRE
                                COMPLETO DEL MEDICO</th>
                            <th colspan="2" rowspan="2" class="align-middle">MODALIDAD</th>
                            <th colspan="2" rowspan="2" class="align-middle">CATEGORIA</th>
                            <th rowspan="3" class="vertical-text align-middle" style="width: 32px; min-width: 32px;">HORAS
                                CONTRATADAS X DIA</th>
                            <th colspan="2" rowspan="2" class="align-middle">DIAS MES</th>
                            <th colspan="2" rowspan="2" class="align-middle">HORAS MES</th>
                            <th colspan="3" rowspan="2" class="align-middle">ATENCIONES</th>
                            <th rowspan="3" class="vertical-text align-middle font-bold"
                                style="width: 36px; min-width: 36px;"><span class="text-danger">%</span> DE RENDIMIENTO</th>
                            <th colspan="10" class="align-middle bg-primary-soft font-bold" style="letter-spacing: 0.5px;">
                                HORAS SIN CONSULTA</th>
                            <th rowspan="3" class="align-middle no-print" style="width: 40px; min-width: 40px;">ACCION</th>
                        </tr>
                        {{-- Fila 2: Sub-grupos bajo HORAS SIN CONSULTA --}}
                        <tr class="header-row-mid">
                            <th colspan="7" class="align-middle font-bold">TOTAL DE HORAS OFICIALES</th>
                            <th colspan="2" class="align-middle font-bold">VACACIONES</th>
                            <th rowspan="2" class="vertical-text align-middle" style="width: 32px; min-width: 32px;">
                                PERMISOS PERSONALES.</th>
                        </tr>
                        {{-- Fila 3: Subcolumnas Detalladas (Verticales y compactas) --}}
                        <tr class="header-row-sub">
                            {{-- Modalidad --}}
                            <th class="vertical-text align-middle" style="width: 30px;">ACUERDO.</th>
                            <th class="vertical-text align-middle" style="width: 30px;">CONTRATO.</th>
                            {{-- Categoria --}}
                            <th class="vertical-text align-middle" style="width: 30px;">MÉDICO GENERAL.</th>
                            <th class="vertical-text align-middle" style="width: 30px;">MÉDICO ESPECIALISTA.</th>
                            {{-- Días Mes --}}
                            <th class="vertical-text align-middle" style="width: 30px;">CONTRATADOS.</th>
                            <th class="vertical-text align-middle" style="width: 30px;">CUMPLIDOS.</th>
                            {{-- Horas Mes --}}
                            <th class="vertical-text align-middle" style="width: 32px;">CONTRATADAS.</th>
                            <th class="vertical-text align-middle" style="width: 32px;">CUMPLIDAS.</th>
                            {{-- Atenciones --}}
                            <th class="vertical-text align-middle" style="width: 32px;">PROGRAMADAS.</th>
                            <th class="vertical-text align-middle" style="width: 32px;">REPROGRAMADAS.</th>
                            <th class="vertical-text align-middle font-bold" style="width: 34px;">ATENDIDAS.</th>
                            {{-- Horas Sin Consulta: Oficiales (7) --}}
                            <th class="vertical-text align-middle" style="width: 30px;">FERIADOS / COMPENSATORIOS</th>
                            <th class="vertical-text align-middle" style="width: 30px;">ESFAM.</th>
                            <th class="vertical-text align-middle" style="width: 30px;">ACTIVIDADES DE PROMOCION</th>
                            <th class="vertical-text align-middle" style="width: 30px;">CONGRESOS / TALLERES.</th>
                            <th class="vertical-text align-middle" style="width: 30px;">INVESTIGACION DE CAMPO.</th>
                            <th class="vertical-text align-middle" style="width: 30px;">ASAMBLEAS COLEGIO MEDICO.</th>
                            <th class="vertical-text align-middle" style="width: 30px;">CITAS, INCAPACIDADES IHSS / PRIVADA.
                            </th>
                            {{-- Horas Sin Consulta: Vacaciones (2) --}}
                            <th class="vertical-text align-middle" style="width: 30px;">ORDINARIAS.</th>
                            <th class="vertical-text align-middle" style="width: 30px;">PROFILACTICAS.</th>
                        </tr>
                    </thead>
                    @include('informes.hora_medico_table')
                </table>
            </div>
        </div>
    </div>

    <!-- Modales -->
    @include('informes.hora_medico_modales')

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        function syncStickyHeaderOffsets() {
            const row1 = document.querySelector('#mainTable thead tr.header-row-main');
            const row2 = document.querySelector('#mainTable thead tr.header-row-mid');
            const row3 = document.querySelector('#mainTable thead tr.header-row-sub');
            if (!row1) return;
            const h1 = row1.offsetHeight;
            if (row2) {
                const h2 = row2.offsetHeight;
                row2.querySelectorAll('th').forEach(th => th.style.setProperty('top', `${h1}px`, 'important'));
                if (row3) {
                    row3.querySelectorAll('th').forEach(th => th.style.setProperty('top', `${h1 + h2}px`, 'important'));
                }
            }
        }

        $(document).ready(function () {
            syncStickyHeaderOffsets();
            window.addEventListener('resize', syncStickyHeaderOffsets);
        });

        function updateFilters() {
            let ano = $('[name="ano"]').val();
            let mes = $('[name="mes"]').val();
            let jornada = $('[name="jornada"]').val();
            let nombre = $('#nombre_medico').val();

            $('#table-loader').css('display', 'flex').fadeIn(150);

            $.get("{{ route('informes.hora-medico') }}", {
                ano: ano,
                mes: mes,
                jornada: jornada,
                nombre: nombre
            }, function (html) {
                $('#mainTable > tbody, #mainTable > tfoot').remove();
                $('#mainTable').append(html);
                syncStickyHeaderOffsets();
                $('#table-loader').fadeOut(150);

                // Actualizar URL sin recargar
                const url = new URL(window.location);
                url.searchParams.set('ano', ano);
                url.searchParams.set('mes', mes);
                url.searchParams.set('jornada', jornada);
                window.history.pushState({}, '', url);
            }).fail(function () {
                $('#table-loader').fadeOut(150);
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

        function imprimirReporte() {
            let ano = $('[name="ano"]').val();
            let mes = $('[name="mes"]').val();
            let jornada = $('[name="jornada"]').val();
            let nombre = $('#nombre_medico').val();

            const url = new URL("{{ route('informes.hora-medico.imprimir') }}");
            url.searchParams.set('ano', ano);
            url.searchParams.set('mes', mes);
            url.searchParams.set('jornada', jornada);
            if (nombre) url.searchParams.set('nombre', nombre);

            window.open(url.toString(), '_blank');
        }

        function openHscModal(el) {
            let btn = $(el);
            let medicoId = btn.data('medico-id') || btn.data('id');
            let medName = btn.data('medico-nombre') || btn.data('name');
            let atend = btn.data('atendidos') || btn.data('atenciones') || 0;
            let diasMes = btn.data('total-dias') || btn.data('diasmes') || 0;
            let hrsDia = btn.data('horas-dia') || btn.data('hrsdia') || 0;
            let diasCont = btn.data('dias-cont') || btn.data('diascont') || 0;
            let isSS = parseInt(btn.data('is-ss')) === 1;
            let pxh = parseFloat(btn.data('pxh')) || (isSS ? 3 : 6);

            $('#hsc_medico_id').val(medicoId).data('pxh', pxh);
            $('#hsc_medico_name').text(medName);
            $('#m_stat_atend').text(atend);
            $('#m_stat_dias_mes').text(diasMes);
            $('#m_stat_hrs_dia').text(hrsDia);
            $('#m_input_dias_cont').val(diasCont);
            $('#m_input_observaciones').val('');

            let currentAno = $('[name="ano"]').val();
            let currentMes = $('[name="mes"]').val();
            $('#hscForm [name="ano"]').val(currentAno);
            $('#hscForm [name="mes"]').val(currentMes);

            // Reset inputs
            $('.hsc-td-input').val(0);

            if (isSS) {
                $('#modal_th_asambleas').html('ACTIVIDADES<br>UNIVERSIDAD.');
                $('#modal_th_vacaciones_group, #modal_th_vac_ord, #modal_th_vac_prof').addClass('th-hatched');
                $('[name="vacaciones_ordinarias"], [name="descanso_profilactico"]').prop('disabled', true).addClass('td-hatched').val(0);
            } else {
                $('#modal_th_asambleas').html('ASAMBLEAS<br>COLEGIO MEDICO.');
                $('#modal_th_vacaciones_group, #modal_th_vac_ord, #modal_th_vac_prof').removeClass('th-hatched');
                $('[name="vacaciones_ordinarias"], [name="descanso_profilactico"]').prop('disabled', false).removeClass('td-hatched');
            }

            $.get("{{ route('informes.hora-medico.get-hsc') }}", {
                medico_id: medicoId,
                ano: currentAno,
                mes: currentMes
            }, function (data) {
                if (data && data.id) {
                    Object.keys(data).forEach(key => {
                        let val = data[key];
                        if ($.isNumeric(val) && !['id', 'medico_id', 'ano', 'created_at', 'updated_at'].includes(key)) {
                            val = parseFloat(val);
                        }
                        if (isSS && (key === 'vacaciones_ordinarias' || key === 'descanso_profilactico')) {
                            $(`[name="${key}"]`).val(0);
                        } else {
                            $(`[name="${key}"]`).val(val);
                        }
                    });
                    if (data.observaciones !== undefined) {
                        $('#m_input_observaciones').val(data.observaciones || '');
                    }
                }
                recalcHSC();
            });

            $('#hscModal').modal('show');
        }

        $(document).on('click', '.btn-hsc-modal', function (e) {
            e.preventDefault();
            openHscModal(this);
        });

        $(document).on('input', '.hsc-td-input, #m_input_dias_cont', function () {
            recalcHSC();
        });

        function recalcHSC() {
            let comp = parseFloat($('[name="compensatorio"]').val()) || 0;
            let esfam = parseFloat($('[name="esfam"]').val()) || 0;
            let prom = parseFloat($('[name="promocion"]').val()) || 0;
            let cong = parseFloat($('[name="congresos_medicos"]').val()) || 0;
            let campo = parseFloat($('[name="trabajo_campo"]').val()) || 0;
            let asam = parseFloat($('[name="convocatoria_general"]').val()) || 0;
            let citas = parseFloat($('[name="incapacidad"]').val()) || 0;

            let vacOrd = parseFloat($('[name="vacaciones_ordinarias"]').val()) || 0;
            let vacProf = parseFloat($('[name="descanso_profilactico"]').val()) || 0;
            let pers = parseFloat($('[name="permiso_personal"]').val()) || 0;

            let totalOfic = comp + esfam + prom + cong + campo + asam + citas;
            let totalVac = vacOrd + vacProf;
            let totalPers = pers;
            let totalHsc = totalOfic + totalVac + totalPers;

            $('#res_oficiales').text(totalOfic);
            $('#res_vacaciones').text(totalVac);
            $('#res_personales').text(totalPers);
            $('#res_total_general').text(totalHsc);

            // Calculate hours and performance preview
            let diasCont = parseFloat($('#m_input_dias_cont').val()) || 0;
            let hrsDia = parseFloat($('#m_stat_hrs_dia').text()) || 0;
            let atend = parseFloat($('#m_stat_atend').text()) || 0;

            let hrsCont = diasCont * hrsDia;
            let hrsDescontadas = totalOfic + totalVac;
            let hrsCump = hrsCont - hrsDescontadas;
            let diasCump = hrsDia > 0 ? (hrsCump / hrsDia) : 0;

            $('#m_stat_hrs_cont').text(Math.round(hrsCont));
            $('#m_stat_dias_cump').text(Math.round(diasCump));
            $('#m_stat_hrs_cump').text(Math.round(hrsCump));

            let pxh = parseFloat($('#hsc_medico_id').data('pxh')) || 6;
            let prog = diasCont * (hrsDia * pxh);
            let repr = prog - (hrsDescontadas * pxh);
            let rend = 0;
            if (repr <= 0) {
                rend = (hrsCont > 0 && hrsDescontadas >= hrsCont) ? 100 : 0;
            } else {
                rend = (atend / repr) * 100;
            }
            $('#m_stat_rend_actual').text(Math.round(rend) + '%');
            $('#res_rendimiento_modal').text(Math.round(rend) + '%');
        }

        function saveHSC() {
            let formData = $('#hscForm').serialize();
            Swal.fire({ title: 'Guardando...', didOpen: () => { Swal.showLoading(); } });

            $.post("{{ route('informes.hora-medico.save-hsc') }}", formData + "&_token={{ csrf_token() }}", function (res) {
                Swal.close();
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Guardado correctamente', timer: 1000, showConfirmButton: false })
                        .then(() => {
                            $('#hscModal').modal('hide');
                            updateFilters();
                        });
                } else {
                    Swal.fire({ icon: 'error', title: 'Error al guardar', text: res.error });
                }
            }).fail(function (xhr) {
                Swal.close();
                let errorMsg = xhr.responseJSON ? xhr.responseJSON.error : 'Ocurrió un error inesperado';
                Swal.fire({ icon: 'error', title: 'Error', text: errorMsg });
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

        document.addEventListener('DOMContentLoaded', function () {
            if ($.fn.select2) {
                $('#exportExcelModal .select2').select2({
                    dropdownParent: $('#exportExcelModal')
                });
            }
        });
    </script>
@endsection