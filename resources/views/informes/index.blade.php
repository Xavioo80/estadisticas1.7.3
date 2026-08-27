<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between py-2" class="px-2 sm:px-3 lg:px-4">
            <div class="flex-shrink-0">
                <h2 class="font-bold text-lg text-slate-900 leading-none mb-0.5">
                    {{ __('Informes por Diagnóstico') }}
                </h2>
                <p class="text-[9px] font-semibold text-slate-400 uppercase tracking-[0.2em] m-0">Análisis y Estadísticas Globales del Centro</p>
            </div>
        </div>
    </x-slot>

    <div class="filter-container flex flex-wrap items-center gap-2 p-2.5 sm:p-3 bg-white dark:bg-gray-900 dark:border-gray-800 dark:bg-slate-900 border border-slate-200 dark:border-slate-800 rounded-2xl mb-3 shadow-sm transition-all shrink-0 no-print">
        <div class="flex flex-1 items-center gap-2">
            <div class="flex items-center gap-1.5 p-1 bg-slate-50 dark:bg-slate-800 border border-slate-200 dark:border-slate-700 rounded-xl shadow-xs">
                <div id="year-wrapper" class="w-32 md:w-36 min-w-[130px]">
                    <select class="form-control select2 select-anos js-example-basic-multiple" id="ano" name="anos[]" multiple="multiple" style="width: 100%;">
                        @foreach($anos as $ano)
                            <option value="{{ $ano }}" {{ $ano == $anoDefault ? 'selected' : '' }}>{{ $ano }}</option>
                        @endforeach
                    </select>
                </div>
                <div id="month-wrapper" class="w-44 md:w-52 min-w-[180px]">
                    <select class="form-control select2 select-meses js-example-basic-multiple" id="mes" name="meses[]" multiple="multiple" style="width: 100%;">
                        @foreach($meses as $mesOption)
                            <option value="{{ $mesOption }}" {{ $mesOption == $mesDefault ? 'selected' : '' }}>{{ $mesOption }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="h-6 w-[1px] bg-slate-200 mx-1"></div>

            <div class="flex flex-wrap gap-1 px-1 flex-1 overflow-x-auto no-scrollbar">
                <a href="{{ route('informes.at1') }}" class="font-semibold flex items-center px-2.5 rounded h-7 text-[9px] bg-white dark:bg-gray-800 text-slate-600 dark:text-gray-200 border border-slate-200 dark:border-gray-700 hover:bg-slate-50 dark:hover:bg-gray-700 transition-all uppercase shadow-sm whitespace-nowrap">AT1</a>
                <a href="{{ route('informes.atenciones') }}" class="font-semibold flex items-center px-2.5 rounded h-7 text-[9px] bg-white dark:bg-gray-800 text-slate-600 dark:text-gray-200 border border-slate-200 dark:border-gray-700 hover:bg-slate-50 dark:hover:bg-gray-700 transition-all uppercase shadow-sm whitespace-nowrap">Atenciones</a>
                <a href="{{ route('informes.tb9') }}" class="font-semibold flex items-center px-2.5 rounded h-7 text-[9px] bg-white dark:bg-gray-800 text-slate-600 dark:text-gray-200 border border-slate-200 dark:border-gray-700 hover:bg-slate-50 dark:hover:bg-gray-700 transition-all uppercase shadow-sm whitespace-nowrap">TB9</a>
                <a href="{{ route('informes.implantes') }}" class="font-semibold flex items-center px-2.5 rounded h-7 text-[9px] bg-white dark:bg-gray-800 text-slate-600 dark:text-gray-200 border border-slate-200 dark:border-gray-700 hover:bg-slate-50 dark:hover:bg-gray-700 transition-all uppercase shadow-sm whitespace-nowrap">Implantes</a>
                <a href="{{ route('informes.at2r') }}" class="font-semibold flex items-center px-2.5 rounded h-7 text-[9px] bg-white dark:bg-gray-800 text-slate-600 dark:text-gray-200 border border-slate-200 dark:border-gray-700 hover:bg-slate-50 dark:hover:bg-gray-700 transition-all uppercase shadow-sm whitespace-nowrap">AT2-r</a>
                <a href="{{ route('informes.at2r-n') }}" class="font-semibold flex items-center px-2.5 rounded h-7 text-[9px] bg-white dark:bg-gray-800 text-slate-600 dark:text-gray-200 border border-slate-200 dark:border-gray-700 hover:bg-slate-50 dark:hover:bg-gray-700 transition-all uppercase shadow-sm whitespace-nowrap">AT2-r N</a>
                <a href="{{ route('informes.at2r-rsm') }}" class="font-semibold flex items-center px-2.5 rounded h-7 text-[9px] bg-white dark:bg-gray-800 text-slate-600 dark:text-gray-200 border border-slate-200 dark:border-gray-700 hover:bg-slate-50 dark:hover:bg-gray-700 transition-all uppercase shadow-sm whitespace-nowrap">AT2-R RSM</a>
                <a href="{{ route('informes.morbilidad') }}" class="font-semibold flex items-center px-2.5 rounded h-7 text-[9px] bg-white dark:bg-gray-800 text-slate-600 dark:text-gray-200 border border-slate-200 dark:border-gray-700 hover:bg-slate-50 dark:hover:bg-gray-700 transition-all uppercase shadow-sm whitespace-nowrap">Morbilidad</a>
                <a href="{{ route('informes.its') }}" class="font-semibold flex items-center px-2.5 rounded h-7 text-[9px] bg-white dark:bg-gray-800 text-slate-600 dark:text-gray-200 border border-slate-200 dark:border-gray-700 hover:bg-slate-50 dark:hover:bg-gray-700 transition-all uppercase shadow-sm whitespace-nowrap">ITS</a>
                <a href="{{ route('informes.alerta-semanal') }}" class="font-semibold flex items-center px-2.5 rounded h-7 text-[9px] bg-white dark:bg-gray-800 text-slate-600 dark:text-gray-200 border border-slate-200 dark:border-gray-700 hover:bg-slate-50 dark:hover:bg-gray-700 transition-all uppercase shadow-sm whitespace-nowrap">Alerta Semanal</a>
                <a href="{{ route('informes.sm107') }}" class="font-semibold flex items-center px-2.5 rounded h-7 text-[9px] bg-white dark:bg-gray-800 text-slate-600 dark:text-gray-200 border border-slate-200 dark:border-gray-700 hover:bg-slate-50 dark:hover:bg-gray-700 transition-all uppercase shadow-sm whitespace-nowrap">SM1-07</a>
                <a href="{{ route('informes.hora-medico') }}" class="font-semibold flex items-center px-2.5 rounded h-7 text-[9px] bg-blue-600 text-white hover:bg-blue-700 transition-all uppercase shadow-sm whitespace-nowrap">Hora Médico</a>
            </div>

            <div class="h-6 w-[1px] bg-slate-200 mx-1"></div>

            <div class="flex items-center gap-1.5 ml-auto">
                <button type="button" onclick="location.reload()" class="font-medium flex items-center justify-center rounded h-7 w-7 text-[10px] bg-slate-100 text-slate-600 border border-slate-200 hover:bg-slate-200 transition-all shadow-sm" title="Actualizar">
                    <i class="fas fa-sync-alt"></i>
                </button>
                <div class="h-6 w-[1px] bg-slate-200 mx-1"></div>
                <button type="button" id="btn-export-at1" class="font-medium flex items-center justify-center rounded h-7 w-7 text-[10px] bg-emerald-600 text-white hover:bg-emerald-700 transition-all shadow-sm" title="Exportar Excel">
                    <i class="fas fa-file-excel"></i>
                </button>
            </div>
        </div>
    </div>

    <div class="" style="height: calc(100vh - 120px);">
        <div id="report-wrapper" class="bg-white dark:bg-gray-900 dark:border-gray-800 overflow-hidden shadow-sm border border-slate-200 rounded-xl flex flex-col h-full mb-2 w-full" style="transition: all 0.3s ease-in-out; position: relative;">
            <div class="card shadow-none mb-0 h-full" style="border: none;">
                <div class="card-body p-0 flex flex-col h-full">
                    <div class="flex-1 overflow-hidden relative">
                        <table id="tabla-informes" class="table table-hover table-striped table-bordered nowrap mb-0" style="width:100%;">
                            <thead>
                                <tr>
                                    <th class="text-center" style="width: 20px; padding: 0 !important;"></th>
                                    @foreach($columns as $column)
                                        <th>
                                            @if($column == 'numero')
                                                N°
                                            @else
                                                {{ strtoupper(str_replace('_', ' ', $column)) }}
                                            @endif
                                        </th>
                                    @endforeach
                                </tr>
                            </thead>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Excel-like Footer Status Bar (Reconteo) -->
    <div id="reconteo-footer" class="reconteo-footer">
        <div class="footer-item">
            <span class="footer-label"><i class="fas fa-list mr-1"></i>TOTAL:</span>
            <span class="footer-value" id="footer-total">0</span>
        </div>
        <div class="footer-item border-left pl-3 ml-3">
            <span class="footer-label"><i class="fas fa-stethoscope mr-1"></i>DIAGNÓSTICOS (N|S):</span>
            <span class="footer-value text-success font-semibold" id="footer-diags">0 | 0</span>
        </div>
        <div class="footer-item border-left pl-3 ml-3">
            <span class="footer-label"><i class="fas fa-user-md mr-1"></i>MÉDICOS:</span>
            <span class="footer-value text-info font-semibold" id="footer-medicos">0</span>
        </div>

        <div class="footer-item border-left pl-3 ml-3">
             <button id="btn-sync-manual" class="btn btn-warning btn-sm py-0 shadow-sm" title="Sincronizar Informes con ATA" style="font-size: 0.8rem;">
                <i class="fas fa-sync-alt"></i> Sync
             </button>
        </div>

        <!-- Banner Rotativo de Enfermedades -->
        <div class="stats-marquee-container">
            <div class="stats-marquee" id="footer-marquee">
                <div class="marquee-item"><span class="marquee-label">Diarreas:</span> <span class="marquee-value text-primary" id="m-diarrea">0</span></div>
                <div class="marquee-item"><span class="marquee-label">Neumonías:</span> <span class="marquee-value text-danger" id="m-neumonia">0</span></div>
                <div class="marquee-item"><span class="marquee-label">Hipertensión:</span> <span class="marquee-value text-warning" id="m-hiper">0</span></div>
                <div class="marquee-item"><span class="marquee-label">Diabetes:</span> <span class="marquee-value text-info" id="m-diabetes">0</span></div>
                <div class="marquee-item"><span class="marquee-label">Dengue S.S:</span> <span class="marquee-value text-success" id="m-d-ss">0</span></div>
                <div class="marquee-item"><span class="marquee-label">Dengue C.S:</span> <span class="marquee-value text-orange" id="m-d-cs">0</span></div>
                <div class="marquee-item"><span class="marquee-label">Dengue G:</span> <span class="marquee-value text-dark" id="m-d-g">0</span></div>
            </div>
        </div>

        <div class="footer-item border-left pl-3 ml-3 d-none d-md-block">
            <span class="footer-label"><i class="fas fa-info-circle mr-1"></i>ESTADO:</span>
            <span class="footer-value" id="footer-status">Listo</span>
        </div>
    </div>

    <!-- Botones Flotantes (Editar / Eliminar) -->
    <div id="floating-actions-informes" class="floating-actions">
        <button class="floating-btn btn-float-edit" title="Editar seleccionados">
            <i class="fas fa-pencil-alt"></i>
        </button>
        <button class="floating-btn btn-float-delete" title="Eliminar seleccionados">
            <i class="fas fa-trash-alt"></i>
        </button>
    </div>

    <!-- El modal ha sido eliminado por solicitud del usuario -->

    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/2.2.9/css/responsive.bootstrap4.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/scroller/2.0.5/css/scroller.bootstrap4.min.css">
    <style>
        /* ── SELECT2 ESTÁNDAR 1.5 DESIGN — COMPACT (SIN ESPACIO DEBAJO) ── */
        #year-wrapper, #ano-wrapper {
            min-width: 130px !important;
        }
        #month-wrapper, #mes-wrapper {
            min-width: 180px !important;
        }

        .select2-container--default .select2-selection--multiple {
            background-color: #ffffff;
            border: 1px solid #cbd5e1;
            border-radius: 0.375rem !important;
            height: 28px !important;
            min-height: 28px !important;
            max-height: 28px !important;
            padding: 0 4px !important;
            transition: border-color 0.2s ease-in-out;
            display: flex !important;
            align-items: center !important;
            overflow: hidden !important;
        }

        html.dark .select2-container--default .select2-selection--multiple,
        .dark .select2-container--default .select2-selection--multiple {
            background-color: #0f172a !important;
            border-color: #334155 !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__rendered {
            display: flex !important;
            flex-wrap: nowrap !important;
            align-items: center !important;
            gap: 3px !important;
            padding: 0 2px !important;
            margin: 0 !important;
            width: 100% !important;
            height: 100% !important;
            overflow-x: hidden !important;
            overflow-y: hidden !important;
            scrollbar-width: none !important;
            -ms-overflow-style: none !important;
        }
        .select2-container--default .select2-selection--multiple .select2-selection__rendered::-webkit-scrollbar {
            display: none !important;
        }

        /* Base Tags Estándar de 1.5 */
        .select2-container--default .select2-selection--multiple .select2-selection__choice {
            background-color: #2563eb !important;
            color: #ffffff !important;
            border-radius: 6px !important;
            font-size: 11px !important;
            font-weight: 600 !important;
            padding: 2px 6px 2px 22px !important;
            margin: 0 1px !important;
            position: relative !important;
            border: none !important;
            display: inline-flex !important;
            align-items: center !important;
            flex-shrink: 0 !important;
            white-space: nowrap !important;
            height: 20px !important;
            line-height: 1 !important;
        }

        /* Tags AÑO — Verde */
        #year-wrapper .select2-selection__choice,
        #ano-wrapper .select2-selection__choice,
        #filter-year + .select2-container .select2-selection__choice,
        #filter-year + .select2-container--default .select2-selection__choice,
        #ano + .select2-container .select2-selection__choice,
        #ano + .select2-container--default .select2-selection__choice,
        .select-anos + .select2-container .select2-selection__choice {
            background-color: #16a34a !important;
            color: #ffffff !important;
        }

        /* Tags MES — Azul */
        #month-wrapper .select2-selection__choice,
        #mes-wrapper .select2-selection__choice,
        #filter-month + .select2-container .select2-selection__choice,
        #filter-month + .select2-container--default .select2-selection__choice,
        #mes + .select2-container .select2-selection__choice,
        #mes + .select2-container--default .select2-selection__choice,
        .select-meses + .select2-container .select2-selection__choice {
            background-color: #2563eb !important;
            color: #ffffff !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
            color: #ffffff !important;
            font-size: 15px !important;
            font-weight: bold !important;
            position: absolute !important;
            left: 5px !important;
            top: 50% !important;
            transform: translateY(-50%) !important;
            border: none !important;
            padding: 0 !important;
            margin: 0 !important;
            line-height: 1 !important;
            background: transparent !important;
            cursor: pointer !important;
        }

        .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
            background-color: rgba(255, 255, 255, 0.2) !important;
            color: #ffffff !important;
        }

        .select2-container--default .select2-selection--multiple .select2-search--inline,
        .select2-container--open .select2-selection--multiple .select2-search--inline {
            position: absolute !important;
            left: 0 !important;
            top: 0 !important;
            width: 0 !important;
            height: 0 !important;
            opacity: 0 !important;
            overflow: hidden !important;
            pointer-events: none !important;
            margin: 0 !important;
            padding: 0 !important;
        }

        .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field,
        .select2-container--open .select2-selection--multiple .select2-search--inline .select2-search__field {
            width: 0 !important;
            height: 0 !important;
            padding: 0 !important;
            margin: 0 !important;
            border: none !important;
            outline: none !important;
            opacity: 0 !important;
        }

        html.dark .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field,
        .dark .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field {
            color: #f8fafc !important;
            caret-color: #38bdf8 !important;
        }

        .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field::placeholder {
            color: #64748b !important;
            opacity: 1 !important;
        }

        html.dark .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field::placeholder,
        .dark .select2-container--default .select2-selection--multiple .select2-search--inline .select2-search__field::placeholder {
            color: #94a3b8 !important;
            opacity: 1 !important;
        }
        /* SELECT2 DROPDOWN DARK THEME ADAPTATION */
        html.dark .select2-dropdown,
        .dark .select2-dropdown,
        body.dark .select2-dropdown {
            background-color: #0f172a !important;
            border: 1px solid #334155 !important;
            color: #f3f4f6 !important;
            box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.5) !important;
        }

        html.dark .select2-results__option,
        .dark .select2-results__option,
        body.dark .select2-results__option {
            background-color: #0f172a !important;
            color: #e2e8f0 !important;
            font-size: 11px !important;
        }

        html.dark .select2-results__option--highlighted[aria-selected],
        .dark .select2-results__option--highlighted[aria-selected],
        body.dark .select2-results__option--highlighted[aria-selected] {
            background-color: #2563eb !important;
            color: #ffffff !important;
        }

        html.dark .select2-results__option[aria-selected="true"],
        .dark .select2-results__option[aria-selected="true"],
        body.dark .select2-results__option[aria-selected="true"] {
            background-color: #1e293b !important;
            color: #ffffff !important;
        }

        html.dark .select2-search--dropdown .select2-search__field,
        .dark .select2-search--dropdown .select2-search__field {
            background-color: #1e293b !important;
            border: 1px solid #334155 !important;
            color: #ffffff !important;
        }

        /* X de cerrar también negra */
        .select2-selection__choice__remove {
            color: #000 !important;
            margin-right: 5px !important;
        }
        /* --- VARIABLES PARA CONFIGURACIÓN DINÁMICA --- */
        :root {
            --indicator-color: #007bff;
            --indicator-color-faint: rgba(0, 123, 255, 0.15);
            --table-font-size: 0.82rem;
            --table-padding-v: 2px;
            --table-padding-h-right: 8px;
            --table-padding-h-left: 4px;
        }

        /* Estilos para DataTables */
        table.dataTable th {
            padding: px 4px !important;
            vertical-align: middle !important;
            font-size: 0.85rem;
            font-weight: bold;
        }
        table.dataTable td {
            padding: var(--table-padding-v) var(--table-padding-h-right) var(--table-padding-v) var(--table-padding-h-left) !important;
            vertical-align: middle !important;
            font-size: var(--table-font-size) !important;
            line-height: 1.1 !important;
            white-space: nowrap;
            position: relative;
        }

        /* --- EFECTO CROSSHAIR (FILAS Y COLUMNAS) --- */
        /* Fila resaltada */
        table.dataTable tbody tr:hover td {
            background-color: var(--indicator-color) !important;
            color: #ffffff !important;
        }

        /* Columna resaltada */
        table.dataTable td.column-highlight {
            background-color: var(--indicator-color-faint) !important;
        }

        table.dataTable {
            margin-top: 0 !important;
            margin-bottom: 0 !important;
        }
        .dataTables_wrapper .dataTables_paginate .paginate_button {
            padding: 0;
        }
        /* Scroll container */
        .dataTables_wrapper .dataTables_scroll {
            width: 100%;
        }
        .dataTables_wrapper .dataTables_scrollBody {
            overflow-y: auto;
            overflow-x: auto;
        }
        /* Ocultar flechas de ordenamiento */
        table.dataTable thead .sorting:before,
        table.dataTable thead .sorting:after,
        table.dataTable thead .sorting_asc:before,
        table.dataTable thead .sorting_asc:after,
        table.dataTable thead .sorting_desc:before,
        table.dataTable thead .sorting_desc:after {
            display: none !important;
        }
        /* Ocultar flechas (botones) de las barras de scroll */
        .dataTables_scrollBody::-webkit-scrollbar-button {
            display: none !important;
            width: 0;
            height: 0;
        }
        .dataTables_scrollBody::-webkit-scrollbar {
            width: 8px;
            height: 8px;
        }
        .dataTables_scrollBody::-webkit-scrollbar-thumb {
            background: #888;
            border-radius: 4px;
        }
        .dataTables_scrollBody::-webkit-scrollbar-thumb:hover {
            background: #555;
        }
        /* Footer siempre visible */
        .card-footer {
            position: sticky;
            bottom: 0;
            background: #f8f9fa !important;
            z-index: 1000;
            border-top: 1px solid #dee2e6;
        }

        /* --- FORMATO IDENTICO A INDEXCOPIAS --- */
        /* --- FORMATO IDENTICO A INDEXCOPIAS --- */
        /* Estilos globales para ambas cabeceras (asegura alineación) */
        table.dataTable thead th {
            position: relative !important;
            padding: 8px 48px 8px 8px !important; 
            background-color: #343a40 !important; 
            color: #ffffff !important;
            text-transform: uppercase;
            font-size: 0.8rem;
            white-space: nowrap;
            vertical-align: middle !important;
            border: 1px solid #454d55 !important;
        }

        /* Ocultar la cabecera duplicada de forma que NO descuadre */
        .dataTables_scrollBody table.dataTable thead {
            visibility: hidden;
        }
        .dataTables_scrollBody table.dataTable thead th {
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            border-top: none !important;
            border-bottom: none !important;
            height: 0 !important;
        }

        .dt-column-menu-btn, .dt-sort-btn {
            position: absolute;
            top: 50% !important;
            transform: translateY(-50%) !important;
            width: 22px;
            height: 22px;
            background: #fff;
            border: 1px solid #ced4da;
            border-radius: 3px;
            color: #495057;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            font-size: 0.7rem;
            opacity: 0; 
            transition: all 0.2s;
            z-index: 10;
        }

        /* Mostrar solo en la cabecera visible (superior) */
        .dataTables_scrollHead th:hover .dt-column-menu-btn, 
        .dataTables_scrollHead th:hover .dt-sort-btn {
            opacity: 1;
        }

        .dt-column-menu-btn { right: 26px !important; }
        .dt-sort-btn { right: 4px !important; }

        .dt-column-menu-btn.has-filter {
            background: #007bff !important;
            color: #fff !important;
            opacity: 1 !important;
            border-color: #0056b3 !important;
        }

        .dt-sort-btn.active {
            background: #28a745 !important;
            color: #fff !important;
            opacity: 1 !important;
        }

        .dt-menu-popover {
            position: absolute;
            background: white;
            border: 1px solid rgba(0,0,0,.15);
            box-shadow: 0 0.5rem 1rem rgba(0,0,0,.175);
            border-radius: 0.3rem;
            z-index: 1060;
            width: 250px;
            display: none;
        }
        .dt-menu-popover .popover-body {
            padding: 8px;
        }
        .dt-menu-section {
            margin-bottom: 8px;
            border-bottom: 1px solid #eee;
            padding-bottom: 8px;
        }
        .dt-menu-section:last-child {
            border-bottom: none;
        }
        .dt-menu-title {
            font-weight: bold;
            margin-bottom: 5px;
            color: #444;
        }
        .dt-filter-select {
            width: 100%;
            margin-bottom: 5px;
            padding: 2px;
            font-size: 0.8rem;
        }
        .dt-filter-input {
            width: 100%;
            padding: 3px 6px;
            font-size: 0.8rem;
            border: 1px solid #ced4da;
            border-radius: 2px;
        }
        .dt-list-container {
            max-height: 150px;
            overflow-y: auto;
            border: 1px solid #eee;
            margin-top: 5px;
            background: #fdfdfd;
        }
        .dt-list-item {
            padding: 2px 5px;
            display: flex;
            align-items: center;
            cursor: pointer;
        }
        .dt-list-item:hover {
            background: #f1f1f1;
        }
        .dt-list-item input {
            margin-right: 5px;
        }
        .dt-list-label {
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .dt-menu-footer {
            display: flex;
            justify-content: space-between;
            margin-top: 8px;
        }
        /* --- ESTILOS PARA EL PIE DE PÁGINA DE RECONTEO --- */
        body {
            padding-bottom: 35px !important; /* Espacio para el footer */
        }
        .reconteo-footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            height: 35px;
            background: #f8f9fa;
            border-top: 1px solid #dee2e6;
            z-index: 3000;
            display: flex;
            align-items: center;
            padding: 0 15px;
            font-size: 0.85rem;
            color: #495057;
            box-shadow: 0 -2px 5px rgba(0,0,0,0.05);
        }
        .footer-item {
            display: flex;
            align-items: center;
            white-space: nowrap;
        }
        /* Scroller Optimization: Fixed height for rows */
        #tabla-informes tbody tr {
            height: 25px !important;
        }
        .footer-label {
            font-weight: 600;
            margin-right: 5px;
            color: #6c757d;
        }
        .footer-value {
            font-family: 'Courier New', Courier, monospace;
        }
        
        /* Estilos del Banner Rotativo */
        .stats-marquee-container {
            flex-grow: 1;
            margin: 0 20px;
            overflow: hidden;
            position: relative;
            background: rgba(0,0,0,0.03);
            height: 24px;
            border-radius: 12px;
            display: flex;
            align-items: center;
        }
        .stats-marquee {
            display: flex;
            white-space: nowrap;
            animation: marquee 30s linear infinite;
            padding-left: 100%;
        }
        .marquee-item {
            display: inline-flex;
            align-items: center;
            margin-right: 40px;
            font-weight: bold;
        }
        .marquee-label {
            color: #555;
            margin-right: 6px;
            font-size: 0.75rem;
            text-transform: uppercase;
        }
        .marquee-value {
            font-size: 0.9rem;
            padding: 1px 8px;
            border-radius: 4px;
        }
        
        @keyframes marquee {
            0% { transform: translateX(0); }
            100% { transform: translateX(-100%); }
        }
        
        .stats-marquee:hover {
            animation-play-state: paused;
        }
        /* Botones Flotantes */
        .floating-actions {
            position: fixed;
            bottom: 60px; /* Un poco arriba del footer de reconteo */
            right: 20px;
            z-index: 4000;
            display: none;
            display: flex; /* Para alinearlos si hay varios, pero oculto por JS/jQuery toggle */
        }
        /* Inicialmente oculto via style inline o clase d-none manejada por JS, 
           pero usaremos jQuery fadeIn/Out que maneja display */
        #floating-actions-informes {
            display: none;
        }

        .floating-btn {
            width: 56px;
            height: 56px;
            border-radius: 50%;
            box-shadow: 0 4px 10px rgba(0,0,0,0.3);
            border: none;
            color: white;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
            margin-left: 15px;
            display: flex;
            align-items: center;
            justify-content: center;
            outline: none !important;
        }
        .floating-btn:hover {
            transform: scale(1.1) translateY(-2px);
            box-shadow: 0 6px 15px rgba(0,0,0,0.35);
        }
        .floating-btn:active {
            transform: scale(0.95);
        }
        .btn-float-edit { 
            background: linear-gradient(135deg, #ffc107, #ffdb72); 
            color: #343a40; 
        }
        .btn-float-delete { 
            background: linear-gradient(135deg, #dc3545, #ff6b6b); 
            color: white;
        }

        /* Checkbox style custom */
        .row-checkbox {
            cursor: pointer;
            width: 16px;
            height: 16px;
        }

    </style>

    <div class="hidden p-3" id="right_sidebar_config">
        <h5><i class="fas fa-cog mr-2"></i>Configuración</h5>
        <hr class="mb-3">
        
        <div class="mb-4">
            <label class="d-block mb-1">Color de Indicador</label>
            <input type="color" id="config-indicator-color" class="form-control form-control-sm" value="#007bff">
        </div>

        <div class="mb-4">
            <label class="d-block mb-1">Tamaño de Fuente</label>
            <select id="config-font-size" class="form-control form-control-sm">
                <option value="0.7rem">Pequeño</option>
                <option value="0.82rem" selected>Mediano</option>
                <option value="0.95rem">Grande</option>
                <option value="1.1rem">Extra Grande</option>
            </select>
        </div>

        <div class="mb-4">
            <label class="d-block mb-1">Altura de Filas (Padding V)</label>
            <input type="range" id="config-padding-v" class="custom-range" min="0" max="15" value="2">
            <small class="text-muted">Valor: <span id="val-padding-v">2</span>px</small>
        </div>

        <div class="mb-4">
            <label class="d-block mb-1">Espacio al Final (Padding H)</label>
            <input type="range" id="config-padding-h" class="custom-range" min="2" max="25" value="8">
            <small class="text-muted">Valor: <span id="val-padding-h">8</span>px</small>
        </div>

        <hr>

        <div class="mb-3">
            <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="config-fix-navbar">
                <label class="custom-control-label" for="config-fix-navbar">Fijar Navbar</label>
            </div>
        </div>

        <div class="mb-3">
            <div class="custom-control custom-switch">
                <input type="checkbox" class="custom-control-input" id="config-fix-footer">
                <label class="custom-control-label" for="config-fix-footer">Fijar Footer</label>
            </div>
        </div>

        <div class="mt-4">
            <button id="config-reset" class="btn btn-sm btn-outline-danger btn-block">
                <i class="fas fa-undo mr-1"></i> Restablecer
            </button>
        </div>
    </div>

@push('scripts')
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/2.2.9/js/responsive.bootstrap4.min.js"></script>
    <script src="https://cdn.datatables.net/scroller/2.0.5/js/dataTables.scroller.min.js"></script>
    <script>
        $(document).ready(function() {
            // Lógica de Configuración
            const savedConfig = JSON.parse(localStorage.getItem('tableConfig_informes') || '{}');
            
            function applyConfig(config) {
                if (config.color) {
                    document.documentElement.style.setProperty('--indicator-color', config.color);
                    const r = parseInt(config.color.slice(1,3), 16), g = parseInt(config.color.slice(3,5), 16), b = parseInt(config.color.slice(5,7), 16);
                    document.documentElement.style.setProperty('--indicator-color-faint', `rgba(${r}, ${g}, ${b}, 0.15)`);
                    $('#config-indicator-color').val(config.color);
                }
                if (config.fontSize) {
                    document.documentElement.style.setProperty('--table-font-size', config.fontSize);
                    $('#config-font-size').val(config.fontSize);
                }
                if (config.paddingV !== undefined) {
                    document.documentElement.style.setProperty('--table-padding-v', config.paddingV + 'px');
                    $('#config-padding-v').val(config.paddingV);
                    $('#val-padding-v').text(config.paddingV);
                }
                if (config.paddingH !== undefined) {
                    document.documentElement.style.setProperty('--table-padding-h-right', config.paddingH + 'px');
                    $('#config-padding-h').val(config.paddingH);
                    $('#val-padding-h').text(config.paddingH);
                }
                if (config.fixNavbar !== undefined) {
                    $('body').toggleClass('layout-navbar-fixed', config.fixNavbar);
                    $('#config-fix-navbar').prop('checked', config.fixNavbar);
                }
                if (config.fixFooter !== undefined) {
                    $('body').toggleClass('layout-footer-fixed', config.fixFooter);
                    $('#config-fix-footer').prop('checked', config.fixFooter);
                }
            }

            applyConfig(savedConfig);

            $('#config-indicator-color').on('input', function() {
                const val = $(this).val();
                document.documentElement.style.setProperty('--indicator-color', val);
                const r = parseInt(val.slice(1,3), 16), g = parseInt(val.slice(3,5), 16), b = parseInt(val.slice(5,7), 16);
                document.documentElement.style.setProperty('--indicator-color-faint', `rgba(${r}, ${g}, ${b}, 0.15)`);
                saveCurrentConfig();
            });

            $('#config-font-size').on('change', function() {
                document.documentElement.style.setProperty('--table-font-size', $(this).val());
                if (window.tableInformes) {
                    // Forzar recalculo completo de columnas
                    window.tableInformes.columns.adjust();
                    // Redibujar la tabla
                    setTimeout(() => { 
                        window.tableInformes.draw(false); 
                        // Ajustar scroll headers
                        $(window).trigger('resize');
                    }, 150);
                }
                saveCurrentConfig();
            });

            $('#config-padding-v').on('input', function() {
                const val = $(this).val();
                document.documentElement.style.setProperty('--table-padding-v', val + 'px');
                $('#val-padding-v').text(val);
                if (window.tableInformes) {
                    window.tableInformes.columns.adjust();
                }
                saveCurrentConfig();
            });

            $('#config-padding-h').on('input', function() {
                const val = $(this).val();
                document.documentElement.style.setProperty('--table-padding-h-right', val + 'px');
                $('#val-padding-h').text(val);
                if (window.tableInformes) {
                    window.tableInformes.columns.adjust();
                }
                saveCurrentConfig();
            });

            $('#config-fix-navbar').on('change', function() {
                $('body').toggleClass('layout-navbar-fixed', $(this).is(':checked'));
                saveCurrentConfig();
            });

            $('#config-fix-footer').on('change', function() {
                $('body').toggleClass('layout-footer-fixed', $(this).is(':checked'));
                saveCurrentConfig();
            });

            $('#config-reset').on('click', function() {
                const defaultConfig = { color: '#007bff', fontSize: '0.82rem', paddingV: 2, paddingH: 8, fixNavbar: false, fixFooter: false };
                applyConfig(defaultConfig);
                saveCurrentConfig();
            });

            function saveCurrentConfig() {
                const config = {
                    color: $('#config-indicator-color').val(), fontSize: $('#config-font-size').val(),
                    paddingV: parseInt($('#config-padding-v').val()), paddingH: parseInt($('#config-padding-h').val()),
                    fixNavbar: $('#config-fix-navbar').is(':checked'), fixFooter: $('#config-fix-footer').is(':checked')
                };
                localStorage.setItem('tableConfig_informes', JSON.stringify(config));
            }
            // Inicializar Select2
            $('.js-example-basic-multiple').select2({
                closeOnSelect: false
            });

            // Columnas para DataTable
            const columns = [
                @foreach($columns as $column)
                    { data: '{{ $column }}', name: '{{ $column }}', defaultContent: '-' },
                @endforeach
            ];

            const table = $('#tabla-informes').DataTable({
                processing: true,
                serverSide: true,
                deferRender: true,
                responsive: false,
                autoWidth: false,
                searching: true, // Requerido para filtros de columna
                ajax: {
                    url: "{{ route('informes.data') }}",
                    data: function(d) {
                        d.anos = $('#ano').val();
                        d.meses = $('#mes').val();
                    }
                },
                columns: [
                    { 
                        data: null, 
                        orderable: false, 
                        searchable: false, 
                        className: 'text-center',
                        width: '20px',
                        render: function(data, type, row) {
                            // Usamos row.id si existe, sino un fallback
                            const val = row.id || '';
                            const regId = row.registro_id || '';
                            const diagIdx = row.diag_index || '';
                            return `<input type="checkbox" class="row-checkbox" value="${val}" data-registro-id="${regId}" data-diag-index="${diagIdx}">`;
                        }
                    },
                    @foreach($columns as $column)
                        { data: '{{ $column }}', name: '{{ $column }}' },
                    @endforeach
                ],
                pageLength: 25,
                language: {
                    url: "https://cdn.datatables.net/plug-ins/1.10.25/i18n/Spanish.json"
                },
                lengthChange: false,
                dom: 'rt', 
                scrollX: true,
                scrollY: 'calc(100vh - 230px)', 
                scrollCollapse: false, 
                scroller: {
                    loadingIndicator: false,
                    displayBuffer: 200, 
                    boundaryScale: 0.5
                },
                stateSave: false,
                order: [[6, 'desc']],
                initComplete: function() {
                    const api = this.api();

                    // Lógica de Crosshair
                    $('#tabla-informes tbody').on('mouseenter', 'td', function () {
                        const colIdx = api.cell(this).index().column;
                        $(api.cells().nodes()).removeClass('column-highlight');
                        $(api.column(colIdx).nodes()).addClass('column-highlight');
                    });
                    
                    $('#tabla-informes tbody').on('mouseleave', function () {
                        $(api.cells().nodes()).removeClass('column-highlight');
                    });

                    // Lógica de Checkboxes y Botones Flotantes
                    $('#tabla-informes tbody').on('change', '.row-checkbox', function() {
                        toggleFloatingActions();
                    });

                    function toggleFloatingActions() {
                        const checkedCount = $('#tabla-informes tbody .row-checkbox:checked').length;
                        if (checkedCount > 0) {
                            $('#floating-actions-informes').stop(true, true).fadeIn(200).css('display', 'flex');
                            
                            if (checkedCount > 1) {
                                $('.btn-float-edit').hide();
                            } else {
                                $('.btn-float-edit').show();
                                // Resetear estado visual del botón
                                $('.btn-float-edit').removeClass('bg-success btn-save-mode').attr('title', 'Editar Registro').find('i').removeClass('fa-save').addClass('fa-pencil-alt');
                            }
                        } else {
                            $('#floating-actions-informes').stop(true, true).fadeOut(200);
                        }
                    }

                    // Botón Editar Flotante (Edición Fila Completa)
                    $('.btn-float-edit').off('click').on('click', function() {
                        const btn = $(this);
                        const selectedCb = $('#tabla-informes tbody .row-checkbox:checked');
                        
                        if (selectedCb.length !== 1) return;

                        const tr = selectedCb.closest('tr');
                        const row = api.row(tr);
                        const rowData = row.data();

                        // Si ya está en modo guardar
                        if (btn.hasClass('btn-save-mode')) {
                             // Guardar
                             const payload = {
                                _token: "{{ csrf_token() }}",
                                _method: 'PUT'
                             };
                             
                             let hasChanges = false;
                             
                             tr.find('input.row-edit-input, select.row-edit-input').each(function() {
                                 const colName = $(this).data('col');
                                 const val = $(this).val();
                                 if (val != rowData[colName]) {
                                     payload[colName] = val;
                                     hasChanges = true;
                                 }
                             });

                             if (!hasChanges) {
                                 // Cancelar/Salir
                                 api.ajax.reload(null, false); 
                                 return;
                             }

                             // AJAX
                             const originalIcon = btn.find('i').attr('class');
                             btn.prop('disabled', true).find('i').attr('class', 'fas fa-spinner fa-spin');

                             $.ajax({
                                url: "{{ url('informes') }}/" + rowData.id,
                                method: "POST",
                                data: payload,
                                success: function(res) {
                                    if(res.success) {
                                        api.ajax.reload(null, false);
                                        if(typeof loadStats === 'function') loadStats();
                                        $('#floating-actions-informes').fadeOut();
                                    } else {
                                        alert('Error: ' + res.message);
                                        btn.prop('disabled', false).find('i').attr('class', originalIcon);
                                    }
                                },
                                error: function() { 
                                    alert('Error de conexión');
                                    btn.prop('disabled', false).find('i').attr('class', originalIcon);
                                }
                             });

                        } else {
                            // Activar Edición
                            btn.addClass('bg-success btn-save-mode').attr('title', 'Guardar Cambios');
                            btn.find('i').removeClass('fa-pencil-alt').addClass('fa-save');
                            
                            // Iterar celdas
                            api.settings()[0].aoColumns.forEach((col, idx) => {
                                const colName = col.name;
                                // Ignorar columnas protegidas
                                if (!colName || ['numero', 'ano', 'mes', 'cm'].includes(colName)) return;

                                const cell = api.cell(tr, idx);
                                const val = cell.data();
                                const node = $(cell.node());

                                // Detectar si deberíamos usar select (ej. Sexo, Tipo Edad)
                                let input;
                                if (colName === 'sexo') {
                                    input = $('<select class="form-control form-control-sm row-edit-input p-0" data-col="sexo"><option value="M">M</option><option value="F">F</option></select>').val(val);
                                } else if (colName === 'tipo') {
                                    input = $('<select class="form-control form-control-sm row-edit-input p-0" data-col="tipo"><option value="A">A</option><option value="M">M</option><option value="D">D</option></select>').val(val);
                                } else if (colName === 'fecha') {
                                    input = $('<input type="date" class="form-control form-control-sm row-edit-input p-0" data-col="fecha">').val(val);
                                } else {
                                    input = $('<input>', {
                                        type: 'text',
                                        class: 'form-control form-control-sm row-edit-input p-1',
                                        value: val,
                                        'data-col': colName
                                    });
                                }
                                
                                input.css({ width: '100%', minWidth: '50px', fontSize: '0.85rem' });
                                
                                // Manejo de enter en inputs
                                input.on('keydown', function(e){
                                    if(e.key === 'Enter') btn.click();
                                });

                                node.html(input);
                            });
                        }
                    });

                    // Eliminar seleccionados
                    $('.btn-float-delete').on('click', function() {
                        const selectedCbs = $('#tabla-informes tbody .row-checkbox:checked');
                        if (selectedCbs.length === 0) return;

                        if (!confirm(`¿Está seguro de eliminar ${selectedCbs.length} registros de la vista informes?\nEsta acción eliminará el diagnóstico asociado del registro padre.`)) {
                            return;
                        }

                        // Recopilar datos
                        const items = [];
                        selectedCbs.each(function() {
                            items.push({
                                id: $(this).val(), // ID Fila Informes
                                registro_id: $(this).data('registro-id'),
                                diag_index: $(this).data('diag-index')
                            });
                        });

                        // Enviar AJAX
                        $.ajax({
                            url: "{{ route('informes.bulk-delete') }}",
                            method: "POST",
                            data: {
                                _token: "{{ csrf_token() }}",
                                items: items
                            },
                            success: function(res) {
                                if (res.success) {
                                    // Recargar tabla
                                    window.tableInformes.ajax.reload(null, false);
                                    // Opcional: Actualizar stats
                                    if(typeof loadStats === 'function') loadStats();
                                    
                                    // Ocultar botones flotantes
                                    $('#floating-actions-informes').fadeOut();
                                } else {
                                    alert('Error: ' + (res.message || 'Ocurrió un algo inesperado'));
                                }
                            },
                            error: function(err) {
                                console.error(err);
                                alert('Ocurrió un error al procesar la solicitud.');
                            }
                        });
                    });

                    // Edición Inline al Doble Clic
                    $('#tabla-informes tbody').on('dblclick', 'td', function() {
                        const cell = api.cell(this);
                        
                        // Verificar validez (ej. si es la columna de checkbox o detalles)
                        if (cell.index().column === 0) return;

                        const colIdx = cell.index().column;
                        const data = cell.data(); // Valor actual
                        const rowData = api.row($(this).closest('tr')).data();
                        
                        // Obtener nombre columna
                        const colName = api.settings()[0].aoColumns[colIdx].name;

                        // Columnas protegidas
                        if (!colName || colName === 'numero' || colName === 'ano' || colName === 'mes' || colName === 'cm') return;
                        
                        // Si ya es input, no hacer nada
                        if ($(this).find('input').length > 0) return;

                        // Renderizar input
                        const input = $('<input type="text" class="form-control form-control-sm p-0 m-0 border-0 bg-light" style="width: 100%; height: 100%;">')
                            .val(data)
                            .on('blur', function() {
                                saveEdit(cell, $(this).val(), colName, rowData.id);
                            })
                            .on('keydown', function(e) {
                                if (e.key === 'Enter') {
                                    $(this).blur();
                                } else if (e.key === 'Escape') {
                                    cell.data(data).draw(false); // Cancelar
                                }
                            });
                        
                        $(this).html(input);
                        input.focus();
                    });

                    function saveEdit(cell, newValue, colName, id) {
                        const oldValue = cell.data();
                        
                        // Si no hubo cambios
                        if (newValue == oldValue) {
                            cell.data(oldValue).draw(false);
                            return;
                        }

                        // Actualizar UI optimistamente
                        cell.data(newValue).draw(false);

                        // Payload
                        const payload = {
                            _token: "{{ csrf_token() }}",
                            _method: 'PUT'
                        };
                        payload[colName] = newValue;

                        $.ajax({
                            url: "{{ url('informes') }}/" + id,
                            method: "POST",
                            data: payload,
                            success: function(res) {
                                if (res.success) {
                                    // Éxito
                                    if(typeof loadStats === 'function') loadStats();
                                } else {
                                    alert('Error al guardar: ' + (res.message || 'Desconocido'));
                                    cell.data(oldValue).draw(false); // Revertir
                                }
                            },
                            error: function(err) {
                                console.error(err);
                                alert('Error de conexión');
                                cell.data(oldValue).draw(false); // Revertir
                            }
                        });
                    }

                    // Cerrar popovers al hacer clic fuera
                    $(document).on('click', function(e) {
                         if (!$(e.target).closest('.dt-menu-popover').length && !$(e.target).closest('.dt-column-menu-btn').length) {
                             $('.dt-menu-popover').hide();
                         }
                    });

                    api.columns().every(function() {
                        const column = this;
                        const header = $(column.header());
                        const colIdx = column.index();
                        const colName = column.dataSrc();

                        // Encontrar AMBOS encabezados (scrollHead y scrollBody) para asegurar que el ancho coincida exactamente
                        const $allHeaders = $(api.table().header()).closest('.dataTables_scroll').find('thead th:nth-child('+(colIdx+1)+')');

                        const $menuBtn = $('<div class="dt-column-menu-btn" title="Filtros"><i class="fas fa-bars"></i></div>');
                        const $sortBtn = $('<div class="dt-sort-btn" title="Ordenar"><i class="fas fa-sort"></i></div>');

                        // Añadir a ambos para que DataTables calcule el ancho basado en el contenido completo
                        $allHeaders.append($menuBtn).append($sortBtn);

                        const popoverId = 'popover-inf-' + colIdx;
                        const popoverHtml = `
                            <div id="${popoverId}" class="dt-menu-popover">
                                <div class="popover-body" style="padding: 10px;">
                                    <div class="dt-menu-section">
                                        <div class="dt-menu-title">Búsqueda rápida</div>
                                        <select class="dt-filter-select">
                                            <option value="contains" selected>Contiene</option>
                                            <option value="equal">Es igual a</option>
                                            <option value="starts">Empieza por</option>
                                            <option value="ends">Termina con</option>
                                        </select>
                                        <input type="text" class="dt-filter-input" placeholder="Escribe para buscar...">
                                    </div>
                                    <div class="dt-menu-section" style="margin-top:10px;">
                                        <div class="dt-menu-title">Seleccionar de la lista</div>
                                        <input type="text" class="dt-filter-input dt-list-search" placeholder="Filtrar lista..." style="margin-bottom:5px;">
                                        <div class="dt-list-container" style="border: 1px solid #eee; max-height: 180px; overflow-y: auto;">
                                            <div class="p-2 text-center text-muted">Cargando...</div>
                                        </div>
                                    </div>
                                    <div class="dt-menu-footer">
                                        <button class="btn btn-xs btn-default btn-clear">Limpiar</button>
                                        <button class="btn btn-xs btn-primary btn-apply">Aplicar</button>
                                    </div>
                                </div>
                            </div>
                        `;
                        const $popover = $(popoverHtml).appendTo('body');
                        const $listContainer = $popover.find('.dt-list-container');
                        const $listSearch = $popover.find('.dt-list-search');
                        const $textInput = $popover.find('.dt-filter-input').first();
                        const $textSelect = $popover.find('.dt-filter-select');

                        // El menú solo interactúa con el botón del scrollHead (el visible)
                        const $visibleMenuBtn = header.find('.dt-column-menu-btn');

                        $visibleMenuBtn.on('click', function(e) {
                            e.stopPropagation();
                            $('.dt-menu-popover').not($popover).hide();
                            if ($popover.is(':visible')) {
                                $popover.hide();
                            } else {
                                const offset = $(this).offset();
                                $popover.css({
                                    top: offset.top + 28,
                                    left: Math.max(10, Math.min($(window).width() - 260, offset.left - 200))
                                }).show();
                                loadValues();
                            }
                        });

                        // Lógica de Ordenamiento manual
                        let sortState = 'none';
                        const $visibleSortBtn = header.find('.dt-sort-btn');

                        $visibleSortBtn.on('click', function(e) {
                            e.stopPropagation();
                            $('.dt-menu-popover').hide();
                            $('.dt-sort-btn').not(this).removeClass('active').html('<i class="fas fa-sort"></i>');
                            
                            if (sortState === 'none') {
                                sortState = 'asc'; column.order('asc').draw(); $(this).html('<i class="fas fa-sort-up"></i>').addClass('active');
                            } else if (sortState === 'asc') {
                                sortState = 'desc'; column.order('desc').draw(); $(this).html('<i class="fas fa-sort-down"></i>');
                            } else {
                                sortState = 'none'; column.order('').draw(); $(this).html('<i class="fas fa-sort"></i>').removeClass('active');
                            }
                        });

                        function loadValues() {
                            $listContainer.html('<div class="p-2 text-center text-muted">Cargando...</div>');
                            
                            const otherFilters = {};
                            api.columns().every(function(idx) {
                                if (idx === colIdx) return;
                                const search = this.search();
                                if (search) otherFilters[this.dataSrc()] = search;
                            });

                            $.ajax({
                                url: "{{ route('informes.column-values') }}",
                                data: {
                                    column: colName,
                                    global_anos: $('#ano').val(),
                                    global_meses: $('#mes').val(),
                                    filters: otherFilters
                                },
                                success: function(resp) {
                                    $listContainer.empty();
                                    if (resp.values && resp.values.length > 0) {
                                        $listContainer.append(`<div class="dt-list-item"><input type="checkbox" class="check-all"> <span><b>(Todo)</b></span></div>`);
                                        resp.values.forEach(val => {
                                            if (val === null || val === '') return;
                                            const isChecked = column.search().includes(val.toString().replace(/[.*+?^${}()|[\]\\]/g, '\\$&'));
                                            $listContainer.append(`
                                                <div class="dt-list-item">
                                                    <input type="checkbox" value="${val}" ${isChecked ? 'checked' : ''}>
                                                    <span class="dt-list-label" title="${val}">${val}</span>
                                                </div>
                                            `);
                                        });
                                    } else {
                                        $listContainer.html('<div class="p-2 text-center text-muted">Sin datos</div>');
                                    }
                                }
                            });
                        }

                        $listSearch.on('keyup', function() {
                            const term = $(this).val().toLowerCase();
                            $listContainer.find('.dt-list-item').each(function() {
                                const text = $(this).text().toLowerCase();
                                $(this).toggle(text.indexOf(term) > -1);
                            });
                        });

                        $listContainer.on('change', '.check-all', function() {
                            $listContainer.find('input').not('.check-all').prop('checked', $(this).is(':checked'));
                        });

                        $popover.find('.btn-apply').on('click', function() {
                            let searchStr = '';
                            const selected = [];
                            $listContainer.find('input:checked').not('.check-all').each(function() {
                                selected.push($.fn.dataTable.util.escapeRegex($(this).val()));
                            });

                        const textVal = $textInput.val();
                            if (textVal) {
                                const type = $textSelect.val();
                                switch(type) {
                                    case 'contains': searchStr = textVal; break;
                                    case 'equal': searchStr = '^' + $.fn.dataTable.util.escapeRegex(textVal) + '$'; break;
                                    case 'starts': searchStr = '^' + $.fn.dataTable.util.escapeRegex(textVal); break;
                                    case 'ends': searchStr = $.fn.dataTable.util.escapeRegex(textVal) + '$'; break;
                                }
                                column.search(searchStr, true, false);
                            } else if (selected.length > 0) {
                                searchStr = '^(' + selected.join('|') + ')$';
                                column.search(searchStr, true, false);
                            } else {
                                column.search('');
                            }

                            if (column.search()) {
                                header.find('.dt-column-menu-btn').addClass('has-filter');
                            } else {
                                header.find('.dt-column-menu-btn').removeClass('has-filter');
                            }
 
                            $popover.hide();
                            table.draw();
                            loadStats(); // Recargar estadísticas al aplicar filtro
                        });
 
                        $popover.find('.btn-clear').on('click', function() {
                            $textInput.val('');
                            $listContainer.find('input').prop('checked', false);
                            column.search('').draw();
                            header.find('.dt-column-menu-btn').removeClass('has-filter');
                            $popover.hide();
                            loadStats(); // Recargar estadísticas al limpiar
                        });
                    });

                    // Forzar ajuste de columnas después de un breve delay
                    setTimeout(() => { api.columns.adjust(); }, 600);
                },
                drawCallback: function(settings) {
                    const api = this.api();
                    const info = api.page.info();
                    const json = settings.json;

                    // Actualizar información básica de DataTables solamente (sin stats)
                    $('#footer-info-informes').html(
                        `Mostrando ${info.recordsDisplay.toLocaleString()} registros de un total de ${info.recordsTotal.toLocaleString()}`
                    );
                }
            });

            // Lógica Sincronización Manual
            $('#btn-sync-manual').on('click', function() {
                if(!confirm('¿Desea sincronizar manualmente la tabla de informes desde los registros globales?\nEsta operación regenerará la vista de informes y puede tardar unos segundos.')) return;
                
                const $btn = $(this);
                const originalHtml = $btn.html();
                $btn.prop('disabled', true).html('<i class="fas fa-spinner fa-spin mr-1"></i> Sincronizando...');
                
                $.ajax({
                    url: "{{ route('informes.sync-manual') }}",
                    method: 'POST',
                    data: { _token: "{{ csrf_token() }}" },
                    success: function(res) {
                        alert(res.message);
                        table.ajax.reload();
                        loadStats();
                    },
                    error: function(err) {
                        alert('Error al sincronizar: ' + (err.responseJSON ? err.responseJSON.message : err.statusText));
                        console.error(err);
                    },
                    complete: function() {
                        $btn.prop('disabled', false).html(originalHtml);
                    }
                });
            });

            window.tableInformes = table;

            // Función independiente para cargar estadísticas (totales, marquee, etc.)
            let statsAbortController = null;
            function loadStats() {
                if (statsAbortController) statsAbortController.abort();
                statsAbortController = new AbortController();

                // Recopilar filtros actuales de la tabla
                const params = table.ajax.params();
                // Nota: params puede ser null si no se ha hecho request aun o si es client-side, pero aquí es serverSide.
                // Sin embargo, `table.ajax.params()` devuelve los del último request.
                // A veces es mejor reconstruirlos o usar los valores de los inputs directamente + columnas.
                
                // Construir data manual para stats
                const data = {
                    anos: $('#ano').val(),
                    meses: $('#mes').val(),
                    columns: []
                };

                // Recorrer columnas para extraer búsquedas actuales
                table.columns().every(function(index) {
                     const col = this;
                     const searchVal = col.search();
                     if(searchVal) {
                         data.columns.push({
                             data: col.dataSrc(),
                             search: { value: searchVal } 
                         });
                     }
                });

                $('#footer-status').text('Calculando estadísticas...');
                
                $.ajax({
                    url: "{{ route('informes.stats') }}",
                    data: data,
                    signal: statsAbortController.signal,
                    success: function(stats) {
                         $('#footer-total').text((stats.total || 0).toLocaleString());
                         $('#footer-diags').text(`${(stats.diag_n || 0).toLocaleString()} | ${(stats.diag_s || 0).toLocaleString()}`);
                         $('#footer-medicos').text((stats.medicos_count || 0).toLocaleString());
                         
                         if (stats.disease_stats) {
                             const ds = stats.disease_stats;
                             $('#m-diarrea').text(ds.diarrea.toLocaleString());
                             $('#m-neumonia').text(ds.neumonia.toLocaleString());
                             $('#m-hiper').text(ds.hipertension.toLocaleString());
                             $('#m-diabetes').text(ds.diabetes.toLocaleString());
                             $('#m-d-ss').text(ds.dengue_ss.toLocaleString());
                             $('#m-d-cs').text(ds.dengue_cs.toLocaleString());
                             $('#m-d-g').text(ds.dengue_grave.toLocaleString());
                         }
                         $('#footer-status').text('Actualizado');
                    },
                    error: function(err) {
                        if (err.statusText !== 'abort') {
                             console.error('Error loading stats', err);
                        }
                    }
                });
            }

            // Asegurar que al hacer click/focus/open las etiquetas seleccionadas nunca se desplacen fuera de vista
            $(document).on('focus click scroll select2:open', '.select2-search__field, .select2-selection--multiple, .select2-selection__rendered', function() {
                const rendered = $(this).closest('.select2-container').find('.select2-selection__rendered');
                if (rendered.length && rendered[0].scrollLeft !== 0) {
                    rendered[0].scrollLeft = 0;
                }
            });

            // Llamar stats inicialmente
            setTimeout(loadStats, 500);

            // Lógica para actualizar meses disponibles al cambiar año
            $('#ano').on('change', function() {
                const selectedAnos = $(this).val();
                
                // Recargar tabla inmediatamente con la selección actual
                table.ajax.reload();
                loadStats();

                // Actualizar opciones del selector de meses
                $.ajax({
                    url: "{{ route('informes.meses') }}",
                    data: { anos: selectedAnos },
                    success: function(resp) {
                        if (resp.success && resp.meses) {
                            const $mesSelect = $('#mes');
                            const currentMeses = $mesSelect.val() || [];
                            
                            $mesSelect.empty();
                            resp.meses.forEach(mes => {
                                const isSelected = currentMeses.includes(mes);
                                const option = new Option(mes, mes, false, isSelected);
                                $mesSelect.append(option);
                            });
                            
                            // Actualizar Select2 si está en uso (trigger change para actualizar UI, pero sin burbujear para evitar recarga doble infinita si no es necesario)
                            // $mesSelect.trigger('change.select2'); 
                        }
                    }
                });
            });

            $('#mes').on('change', function() {
                table.ajax.reload(); 
                loadStats();
            });

            $('#btn-export-at1').on('click', function() {
                const anos = $('#ano').val();
                const meses = $('#mes').val();
                let url = "{{ route('informes.at1.export') }}?";
                
                if (anos) anos.forEach(a => url += `anos[]=${a}&`);
                if (meses) meses.forEach(m => url += `meses[]=${m}&`);
                
                // Filtros de columnas
                table.columns().every(function() {
                    const col = this;
                    const val = col.search();
                    if (val) {
                        url += `filters[${col.dataSrc()}]=${encodeURIComponent(val)}&`;
                    }
                });
                
                window.location.href = url;
            });

        });
    </script>
@endpush
</x-app-layout>
