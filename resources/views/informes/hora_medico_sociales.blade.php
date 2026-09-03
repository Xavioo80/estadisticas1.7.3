@extends('layouts.app')

@section('title', 'Rendimiento Médico (Servicio Social) - Estadísticas 1.7')

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

        .v-header {
            writing-mode: vertical-rl !important;
            transform: rotate(180deg) !important;
            white-space: normal !important;
            word-break: normal !important;
            font-size: 0.54rem !important;
            font-weight: 800 !important;
            text-align: center !important;
            margin: 0 auto !important;
            display: inline-block !important;
            letter-spacing: 0px !important;
            line-height: 1.08 !important;
            padding: 1px 0 !important;
        }

        #mainTable thead tr.header-row-main th {
            height: 22px !important;
            font-size: 0.70rem !important;
            font-weight: 800 !important;
            padding: 2px 2px !important;
            vertical-align: middle !important;
            text-align: center !important;
        }

        #mainTable thead tr.header-row-mid th {
            height: 20px !important;
            font-size: 0.66rem !important;
            font-weight: 800 !important;
            padding: 2px 2px !important;
            vertical-align: middle !important;
            text-align: center !important;
        }

        #mainTable thead tr.header-row-sub th {
            height: 96px !important;
            font-size: 0.66rem !important;
            max-height: 96px !important;
            vertical-align: middle !important;
            text-align: center !important;
            padding: 2px 1px !important;
        }

        #mainTable {
            border: 1px solid #94a3b8 !important;
            border-collapse: collapse !important;
        }

        html.dark #mainTable,
        [data-theme="dark"] #mainTable {
            border: 1px solid #475569 !important;
        }

        #mainTable thead th {
            border: 1px solid #94a3b8 !important;
            vertical-align: middle !important;
            background-color: var(--bg-surface-alt, #e2e8f0) !important;
            color: var(--text-primary, #0f172a) !important;
        }

        html.dark #mainTable thead th,
        [data-theme="dark"] #mainTable thead th {
            background-color: #1e293b !important;
            color: #f8fafc !important;
            border: 1px solid #475569 !important;
        }

        #mainTable tbody tr {
            height: 25px !important;
        }

        #mainTable tbody td {
            vertical-align: middle !important;
            font-size: 0.78rem !important;
            font-weight: 600 !important;
            padding: 1px 3px !important;
            border: 1px solid #94a3b8 !important;
            color: var(--text-primary) !important;
        }

        html.dark #mainTable tbody td,
        [data-theme="dark"] #mainTable tbody td {
            border: 1px solid #475569 !important;
        }

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

        #mainTable thead tr.header-row-main th,
        #mainTable thead tr.header-row-mid th,
        #mainTable thead tr.header-row-sub th {
            vertical-align: middle !important;
            text-align: center !important;
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
            font-size: 0.88rem !important;
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
    </style>

    <div class="informe-page-wrapper" id="report-wrapper">
        <!-- Header -->
        <div class="informe-header no-print">
            <div class="flex items-center gap-3 shrink-0">
                <h2
                    style="margin: 0; font-size: 1rem; font-weight: 800; display: inline-flex; align-items: center; gap: 6px; white-space: nowrap; color: var(--text-primary);">
                    <i class="bi bi-mortarboard text-primary"></i> Hora Médico - Servicio Social
                </h2>
                {{-- Navigation Tabs --}}
                <div class="nav-tab-group">
                    <a href="{{ route('informes.hora-medico', ['ano' => $ano, 'mes' => $mesNombre]) }}"
                        class="nav-tab-btn {{ !request()->routeIs('informes.hora-medico.servicio-social') ? 'active' : '' }}">
                        <i class="bi bi-people-fill"></i> Generales y Especialistas
                    </a>
                    <a href="{{ route('informes.hora-medico.servicio-social', ['ano' => $ano, 'mes' => $mesNombre]) }}"
                        class="nav-tab-btn {{ request()->routeIs('informes.hora-medico.servicio-social') ? 'active' : '' }}">
                        <i class="bi bi-mortarboard-fill"></i> Servicio Social
                    </a>
                </div>
            </div>

            <div class="header-actions-row">
                <!-- Botón Observaciones -->
                <button type="button" onclick="abrirObservacionesSociales()"
                    class="btn-header-purple" title="Ver Informe Oficial de Observaciones (Servicio Social)">
                    <i class="bi bi-journal-text"></i> Observaciones
                </button>

                <!-- Botón Incluir Médico Social -->
                <button type="button" class="btn btn-primary btn-sm flex items-center gap-1 shadow-sm"
                    style="white-space: nowrap;" data-toggle="modal" data-target="#addMedicoModal"
                    title="Incluir Médico Social">
                    <i class="bi bi-person-plus text-xs"></i> Incluir Médico
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
                    <div style="width: 190px;">
                        <select name="jornada" class="filter-select w-full" onchange="updateFilters()">
                            <option value="TOTAL JORNADAS" {{ ($jornada == 'TOTAL JORNADAS' || $jornada == 'TODAS LAS JORNADAS' || $jornada == 'SERVICIO SOCIAL') ? 'selected' : '' }}>TOTAL JORNADAS</option>
                            <option value="CONGLOMERADO SOCIALES" {{ $jornada == 'CONGLOMERADO SOCIALES' ? 'selected' : '' }}>CONGLOMERADO SOCIALES</option>
                            <option value="MATUTINA" {{ $jornada == 'MATUTINA' ? 'selected' : '' }}>MATUTINA</option>
                            <option value="VESPERTINA" {{ $jornada == 'VESPERTINA' ? 'selected' : '' }}>VESPERTINA</option>
                            <option value="FIN DE SEMANA" {{ $jornada == 'FIN DE SEMANA' ? 'selected' : '' }}>FIN DE SEMANA</option>
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
                <table class="table table-bordered table-sm text-center mb-0 w-full" id="mainTable"
                    style="min-width: 1350px;">
                    <thead class="thead-premium sticky-top">
                        {{-- Fila 1: Grupos Principales y Super-Header HORAS SIN CONSULTA --}}
                        <tr class="header-row-main">
                            <th rowspan="3" class="align-middle sticky-col-1"
                                style="width: 36px; min-width: 36px; z-index: 100;">#</th>
                            <th rowspan="3" class="align-middle sticky-col-2 text-left"
                                style="width: 230px; min-width: 230px; z-index: 100; padding-left: 10px !important;">NOMBRE
                                COMPLETO DEL MEDICO</th>
                            <th colspan="2" rowspan="2" class="align-middle">MODALIDAD</th>
                            <th colspan="2" rowspan="2" class="align-middle">CATEGORIA</th>
                            <th rowspan="3" class="align-middle" style="width: 32px;"><div class="v-header">HORAS / DÍA</div></th>
                            <th colspan="2" rowspan="2" class="align-middle">DIAS MES</th>
                            <th colspan="2" rowspan="2" class="align-middle">HORAS MES</th>
                            <th colspan="3" rowspan="2" class="align-middle">ATENCIONES</th>
                            <th rowspan="3" class="align-middle font-bold" style="width: 32px;"><div class="v-header">% RENDIMIENTO</div></th>
                            <th colspan="10" class="align-middle bg-oficial-main">HORAS SIN CONSULTA</th>
                            <th rowspan="3" class="align-middle" style="width: 36px; min-width: 36px;">ACCION</th>
                        </tr>
                        {{-- Fila 2: Sub-grupos bajo Horas Sin Consulta --}}
                        <tr class="header-row-mid">
                            <th colspan="7" class="align-middle bg-oficial-sub">TOTAL DE HORAS OFICIALES</th>
                            <th colspan="2" class="align-middle th-hatched" id="th_vacaciones_group">VACACIONES</th>
                            <th rowspan="2" class="align-middle" style="width: 30px;"><div class="v-header">PERMISOS PERSONALES.</div></th>
                        </tr>
                        {{-- Fila 3: Subcolumnas Verticales Detalladas --}}
                        <tr class="header-row-sub">
                            {{-- Modalidad: Acuerdo rayado, Contrato activo --}}
                            <th class="align-middle th-hatched" style="width: 30px;"><div class="v-header">ACUERDO.</div></th>
                            <th class="align-middle" style="width: 30px;"><div class="v-header">CONTRATO.</div></th>
                            {{-- Categoria: Medico General activo, Especialista rayado --}}
                            <th class="align-middle" style="width: 30px;"><div class="v-header">MÉDICO GENERAL.</div></th>
                            <th class="align-middle th-hatched" style="width: 30px;"><div class="v-header">MÉDICO ESPECIALISTA.</div></th>
                            {{-- Días Mes --}}
                            <th class="align-middle" style="width: 30px;"><div class="v-header">CONTRATADOS.</div></th>
                            <th class="align-middle" style="width: 30px;"><div class="v-header">CUMPLIDOS.</div></th>
                            {{-- Horas Mes --}}
                            <th class="align-middle" style="width: 32px;"><div class="v-header">CONTRATADAS.</div></th>
                            <th class="align-middle" style="width: 32px;"><div class="v-header">CUMPLIDAS.</div></th>
                            {{-- Atenciones --}}
                            <th class="align-middle" style="width: 32px;"><div class="v-header">PROGRAMADAS.</div></th>
                            <th class="align-middle" style="width: 32px;"><div class="v-header">REPROGRAMADAS.</div></th>
                            <th class="align-middle font-bold" style="width: 34px;"><div class="v-header">ATENDIDAS.</div></th>
                            {{-- Horas Sin Consulta: Oficiales (7) con **** OTRAS ACTIVIDADES --}}
                            <th class="align-middle" style="width: 30px;"><div class="v-header">FERIADOS / COMPENSATORIOS</div></th>
                            <th class="align-middle" style="width: 30px;"><div class="v-header">ESFAM.</div></th>
                            <th class="align-middle" style="width: 30px;"><div class="v-header">**** OTRAS ACTIVIDADES</div></th>
                            <th class="align-middle" style="width: 30px;"><div class="v-header">CONGRESOS / TALLERES.</div></th>
                            <th class="align-middle" style="width: 30px;"><div class="v-header">INVESTIGACION DE CAMPO.</div></th>
                            <th class="align-middle" style="width: 30px;"><div class="v-header">ACTIVIDADES UNIVERSIDAD.</div></th>
                            <th class="align-middle" style="width: 30px;"><div class="v-header">CITAS, INCAPACIDADES IHSS / PRIVADA.</div></th>
                            {{-- Horas Sin Consulta: Vacaciones rayadas --}}
                            <th class="align-middle th-hatched" style="width: 30px;"><div class="v-header">ORDINARIAS.</div></th>
                            <th class="align-middle th-hatched" style="width: 30px;"><div class="v-header">PROFILACTICAS.</div></th>
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
            // El thead completo tiene position: sticky sin desfasar las filas
        }

        function updateFilters() {
            let ano = $('[name="ano"]').val();
            let mes = $('[name="mes"]').val();
            let jornada = $('[name="jornada"]').val();

            $('#table-loader').css('display', 'flex').fadeIn(150);

            $.get("{{ route('informes.hora-medico.servicio-social') }}", {
                ano: ano,
                mes: mes,
                jornada: jornada
            }, function (html) {
                $('#mainTable > tbody, #mainTable > tfoot').remove();
                $('#mainTable').append(html);
                syncStickyHeaderOffsets();
                $('#table-loader').fadeOut(150);

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

            const url = new URL("{{ route('informes.hora-medico.imprimir') }}");
            url.searchParams.set('ano', ano);
            url.searchParams.set('mes', mes);
            url.searchParams.set('jornada', jornada);
            url.searchParams.set('only_ss', '1');

            window.open(url.toString(), '_blank');
        }

        function abrirObservacionesSociales() {
            let ano = $('[name="ano"]').val();
            let mes = $('[name="mes"]').val();
            let jornada = $('[name="jornada"]').val();

            const url = new URL("{{ route('informes.hora-medico.consolidado') }}");
            url.searchParams.set('ano', ano);
            url.searchParams.set('mes', mes);
            url.searchParams.set('jornada', jornada);
            url.searchParams.set('only_ss', '1');

            window.location.href = url.toString();
        }

        // Modal HSC / Observaciones Calculations
        function openHscModal(el) {
            let btn = $(el);
            let medicoId = btn.data('medico-id') || btn.data('id');
            let medName = btn.data('medico-nombre') || btn.data('name');
            let atend = btn.data('atendidos') || btn.data('atenciones') || 0;
            let diasMes = btn.data('total-dias') || btn.data('diasmes') || 0;
            let hrsDia = btn.data('horas-dia') || btn.data('hrsdia') || 0;
            let diasCont = btn.data('dias-cont') || btn.data('diascont') || 0;
            let isSS = true; // Vista Servicio Social
            let pxh = parseFloat(btn.data('pxh')) || 3;

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

            $('.hsc-td-input').val(0);

            $('#modal_th_asambleas').html('ACTIVIDADES<br>UNIVERSIDAD.');
            $('#modal_th_vacaciones_group, #modal_th_vac_ord, #modal_th_vac_prof').addClass('th-hatched');
            $('[name="vacaciones_ordinarias"], [name="descanso_profilactico"]').prop('disabled', true).addClass('td-hatched').val(0);

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
                        if (key === 'vacaciones_ordinarias' || key === 'descanso_profilactico') {
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

            let pxh = parseFloat($('#hsc_medico_id').data('pxh')) || 3;
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
    </script>
@endsection