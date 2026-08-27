{{-- ===== MODALES COMPARTIDOS - HORA MEDICO ===== --}}
{{-- Incluido desde: hora_medico.blade.php y hora_medico_sociales.blade.php --}}

<style>
    /* Rules for Modal Hiding and Flex Display */
    .modal:not(.show) {
        display: none !important;
    }

    .modal.show {
        display: flex !important;
        position: fixed;
        top: 0;
        left: 0;
        right: 0;
        bottom: 0;
        z-index: 1050;
        background-color: rgba(15, 23, 42, 0.75);
        backdrop-filter: blur(4px);
        align-items: center;
        justify-content: center;
        overflow-x: hidden;
        overflow-y: auto;
        padding: 1rem;
    }

    /* Complete High-Contrast Dark Mode Styles for Hora Médico Modals */
    html.dark .modal-content,
    .dark .modal-content,
    body.dark .modal-content {
        background-color: #18181b !important; /* Crisp dark gray surface (#18181b) */
        border-color: #3f3f46 !important;
        color: #f4f4f5 !important;
        box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.7) !important;
    }

    html.dark .modal-header,
    .dark .modal-header,
    body.dark .modal-header {
        background-color: #27272a !important; /* Lighter header gray (#27272a) */
        border-color: #3f3f46 !important;
        color: #ffffff !important;
    }

    html.dark .modal-footer,
    .dark .modal-footer,
    body.dark .modal-footer {
        background-color: #18181b !important;
        border-color: #3f3f46 !important;
    }

    html.dark .modal-body,
    .dark .modal-body,
    body.dark .modal-body {
        background-color: #18181b !important;
        color: #f4f4f5 !important;
    }

    html.dark .modal-body label,
    .dark .modal-body label,
    body.dark .modal-body label {
        color: #e4e4e7 !important;
    }

    /* Form Inputs and Native Selects in Dark Mode */
    html.dark .modal-body input:not([type="checkbox"]):not([type="radio"]),
    html.dark .modal-body select,
    .dark .modal-body input:not([type="checkbox"]):not([type="radio"]),
    .dark .modal-body select,
    body.dark .modal-body input:not([type="checkbox"]):not([type="radio"]),
    body.dark .modal-body select {
        background-color: #09090b !important; /* Very dark input surface for contrast against #27272a card */
        border-color: #3f3f46 !important;
        color: #ffffff !important;
    }

    /* FIX: Native Option Tags adaptive light/dark background */
    html.dark select option,
    .dark select option,
    body.dark select option {
        background-color: #1f2937 !important;
        color: #ffffff !important;
        padding: 8px 12px !important;
    }
    select option {
        background-color: #ffffff;
        color: #1e293b;
        padding: 8px 12px !important;
    }

    /* ─── HSC Modal Table Styles with Vertical Headers ─── */
    .hsc-table {
        table-layout: fixed;
        width: 100%;
        margin-bottom: 0 !important;
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }
    .hsc-table th, .hsc-table td {
        vertical-align: middle !important;
        text-align: center !important;
        padding: 0 !important;
    }
    .hsc-header-row-main th {
        height: 28px !important;
        font-size: 0.72rem !important;
        font-weight: 800 !important;
        letter-spacing: 0.4px;
        padding: 4px 6px !important;
        vertical-align: middle !important;
    }
    .hsc-header-row-sub {
        height: 130px !important;
    }
    .hsc-header-row-sub th {
        height: 130px !important;
        max-height: 130px !important;
        vertical-align: middle !important;
        padding: 4px 1px !important;
        overflow: hidden !important;
    }
    .hsc-v-header {
        writing-mode: vertical-rl !important;
        transform: rotate(180deg) !important;
        white-space: normal !important;
        word-break: normal !important;
        font-size: 0.68rem !important;
        font-weight: 800 !important;
        text-align: center !important;
        vertical-align: middle !important;
        line-height: 1.15 !important;
        letter-spacing: 0.25px !important;
        display: inline-block !important;
    }
    /* Hide spin-button arrows on number inputs so digits aren't squished */
    .hsc-td-input::-webkit-outer-spin-button,
    .hsc-td-input::-webkit-inner-spin-button,
    #m_input_dias_cont::-webkit-outer-spin-button,
    #m_input_dias_cont::-webkit-inner-spin-button {
        -webkit-appearance: none !important;
        margin: 0 !important;
    }
    .hsc-td-input,
    #m_input_dias_cont {
        -moz-appearance: textfield !important;
        appearance: textfield !important;
    }

    .hsc-td-input {
        width: 100% !important;
        height: 38px !important;
        text-align: center !important;
        font-size: 1.05rem !important;
        font-weight: 800 !important;
        border: none !important;
        background: transparent !important;
        color: #0f172a !important;
        transition: all 0.15s ease !important;
        padding: 0 !important;
    }
    .hsc-td-input:focus {
        background: #ffffff !important;
        box-shadow: inset 0 0 0 2px #4f46e5 !important;
        outline: none !important;
    }
    html.dark .hsc-td-input,
    [data-theme="dark"] .hsc-td-input {
        color: #f8fafc !important;
    }
    html.dark .hsc-td-input:focus,
    [data-theme="dark"] .hsc-td-input:focus {
        background: #09090b !important;
        box-shadow: inset 0 0 0 2px #6366f1 !important;
        color: #ffffff !important;
    }

    /* 1. Oficiales (Ámbar / Naranja) */
    .bg-oficial-main {
        background: linear-gradient(135deg, #f59e0b, #d97706) !important;
        color: #ffffff !important;
        border: 1px solid #d97706 !important;
    }
    .bg-oficial-sub {
        background-color: #fef3c7 !important;
        color: #92400e !important;
        border: 1px solid #fde68a !important;
    }
    .td-oficial {
        background-color: #fffbeb !important;
        border: 1px solid #fde68a !important;
    }
    html.dark .bg-oficial-main, [data-theme="dark"] .bg-oficial-main {
        background: linear-gradient(135deg, #92400e, #78350f) !important;
        color: #fef3c7 !important;
        border-color: #78350f !important;
    }
    html.dark .bg-oficial-sub, [data-theme="dark"] .bg-oficial-sub {
        background-color: #2e1d0d !important;
        color: #fcd34d !important;
        border-color: #452a12 !important;
    }
    html.dark .td-oficial, [data-theme="dark"] .td-oficial {
        background-color: #1c1309 !important;
        border-color: #452a12 !important;
    }

    /* 2. Vacaciones (Azul / Cyan) */
    .bg-vacaciones-main {
        background: linear-gradient(135deg, #0284c7, #0369a1) !important;
        color: #ffffff !important;
        border: 1px solid #0369a1 !important;
    }
    .bg-vacaciones-sub {
        background-color: #e0f2fe !important;
        color: #075985 !important;
        border: 1px solid #bae6fd !important;
    }
    .td-vacaciones {
        background-color: #f0f9ff !important;
        border: 1px solid #bae6fd !important;
    }
    html.dark .bg-vacaciones-main, [data-theme="dark"] .bg-vacaciones-main {
        background: linear-gradient(135deg, #075985, #0c4a6e) !important;
        color: #e0f2fe !important;
        border-color: #0c4a6e !important;
    }
    html.dark .bg-vacaciones-sub, [data-theme="dark"] .bg-vacaciones-sub {
        background-color: #08283b !important;
        color: #7dd3fc !important;
        border-color: #0c4a6e !important;
    }
    html.dark .td-vacaciones, [data-theme="dark"] .td-vacaciones {
        background-color: #051a26 !important;
        border-color: #0c4a6e !important;
    }

    /* 3. Permisos Personales (Violeta / Púrpura) */
    .bg-personal-main {
        background: linear-gradient(135deg, #7c3aed, #6d28d9) !important;
        color: #ffffff !important;
        border: 1px solid #6d28d9 !important;
    }
    .bg-personal-sub {
        background-color: #ede9fe !important;
        color: #5b21b6 !important;
        border: 1px solid #ddd6fe !important;
    }
    .td-personal {
        background-color: #f5f3ff !important;
        border: 1px solid #ddd6fe !important;
    }
    html.dark .bg-personal-main, [data-theme="dark"] .bg-personal-main {
        background: linear-gradient(135deg, #5b21b6, #4c1d95) !important;
        color: #ede9fe !important;
        border-color: #4c1d95 !important;
    }
    html.dark .bg-personal-sub, [data-theme="dark"] .bg-personal-sub {
        background-color: #241442 !important;
        color: #c4b5fd !important;
        border-color: #3b1d6e !important;
    }
    html.dark .td-personal, [data-theme="dark"] .td-personal {
        background-color: #160c29 !important;
        border-color: #3b1d6e !important;
    }

    /* Info Box Alert in Modals - High Contrast */
    .info-box-alert {
        background-color: #eff6ff !important;
        border-color: #93c5fd !important;
    }
    .info-box-alert p {
        color: #1e3a8a !important;
    }
    .info-box-alert p strong {
        color: #172554 !important;
    }

    html.dark .info-box-alert,
    .dark .info-box-alert,
    body.dark .info-box-alert {
        background-color: rgba(30, 58, 138, 0.45) !important;
        border-color: #3b82f6 !important;
    }
    html.dark .info-box-alert p,
    .dark .info-box-alert p,
    body.dark .info-box-alert p {
        color: #dbeafe !important;
    }
    html.dark .info-box-alert p strong,
    .dark .info-box-alert p strong,
    body.dark .info-box-alert p strong {
        color: #ffffff !important;
    }

    /* Inner Card Layer Contrast (#27272a over #18181b) */
    html.dark .modal-body .card,
    .dark .modal-body .card,
    body.dark .modal-body .card {
        background-color: #27272a !important;
        border-color: #3f3f46 !important;
        color: #f4f4f5 !important;
    }

    /* Select2 Single & Multiple Controls in Dark Mode */
    html.dark .select2-container--default .select2-selection--single,
    .dark .select2-container--default .select2-selection--single,
    body.dark .select2-container--default .select2-selection--single {
        background-color: #09090b !important;
        border-color: #3f3f46 !important;
        color: #ffffff !important;
        height: 38px !important;
        border-radius: 0.5rem !important;
        display: flex !important;
        align-items: center !important;
    }

    html.dark .select2-container--default .select2-selection--single .select2-selection__rendered,
    .dark .select2-container--default .select2-selection--single .select2-selection__rendered,
    body.dark .select2-container--default .select2-selection--single .select2-selection__rendered {
        color: #f4f4f5 !important;
        line-height: 36px !important;
        padding-left: 12px !important;
        font-size: 0.75rem !important;
        font-weight: 500 !important;
    }

    html.dark .select2-container--default .select2-selection--single .select2-selection__placeholder,
    .dark .select2-container--default .select2-selection--single .select2-selection__placeholder,
    body.dark .select2-container--default .select2-selection--single .select2-selection__placeholder {
        color: #a1a1aa !important;
    }

    html.dark .select2-container--default .select2-selection--single .select2-selection__arrow,
    .dark .select2-container--default .select2-selection--single .select2-selection__arrow,
    body.dark .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 36px !important;
    }

    html.dark .select2-container--default .select2-selection--single .select2-selection__arrow b,
    .dark .select2-container--default .select2-selection--single .select2-selection__arrow b,
    body.dark .select2-container--default .select2-selection--single .select2-selection__arrow b {
        border-color: #a1a1aa transparent transparent transparent !important;
    }

    html.dark .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b,
    .dark .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b,
    body.dark .select2-container--default.select2-container--open .select2-selection--single .select2-selection__arrow b {
        border-color: transparent transparent #a1a1aa transparent !important;
    }

    html.dark .select2-container--default .select2-selection--multiple,
    .dark .select2-container--default .select2-selection--multiple,
    body.dark .select2-container--default .select2-selection--multiple {
        background-color: #09090b !important;
        border-color: #3f3f46 !important;
        color: #ffffff !important;
        border-radius: 0.5rem !important;
        min-height: 38px !important;
    }

    html.dark .select2-container--default .select2-selection--multiple .select2-selection__choice,
    .dark .select2-container--default .select2-selection--multiple .select2-selection__choice,
    body.dark .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #27272a !important;
        border-color: #3f3f46 !important;
        color: #60a5fa !important;
        border-radius: 0.375rem !important;
        font-size: 0.75rem !important;
        padding: 2px 8px !important;
    }

    html.dark .select2-container--default .select2-selection--multiple .select2-selection__choice__remove,
    .dark .select2-container--default .select2-selection--multiple .select2-selection__choice__remove,
    body.dark .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #a1a1aa !important;
        margin-right: 4px !important;
    }

    html.dark .select2-dropdown,
    .dark .select2-dropdown,
    body.dark .select2-dropdown {
        background-color: #18181b !important;
        border-color: #3f3f46 !important;
        color: #ffffff !important;
        border-radius: 0.5rem !important;
        box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.7) !important;
        z-index: 1060 !important;
    }

    html.dark .select2-search--dropdown .select2-search__field,
    .dark .select2-search--dropdown .select2-search__field,
    body.dark .select2-search--dropdown .select2-search__field {
        background-color: #09090b !important;
        border-color: #3f3f46 !important;
        color: #ffffff !important;
        border-radius: 0.375rem !important;
        font-size: 0.75rem !important;
        padding: 6px 10px !important;
    }

    html.dark .select2-results__option,
    .dark .select2-results__option,
    body.dark .select2-results__option {
        background-color: #18181b !important;
        color: #f4f4f5 !important;
        font-size: 0.75rem !important;
        padding: 8px 12px !important;
    }

    html.dark .select2-results__option--highlighted[aria-selected],
    .dark .select2-results__option--highlighted[aria-selected],
    body.dark .select2-results__option--highlighted[aria-selected] {
        background-color: #2563eb !important;
        color: #ffffff !important;
        font-weight: 600 !important;
    }

    html.dark .select2-results__option[aria-selected="true"],
    .dark .select2-results__option[aria-selected="true"],
    body.dark .select2-results__option[aria-selected="true"] {
        background-color: #27272a !important;
        color: #60a5fa !important;
    }
</style>

<!-- Modal Agregar Medico (Sin Consultas) -->
<div class="modal fade" id="addMedicoModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border border-gray-200 dark:border-gray-700 shadow-2xl rounded-2xl overflow-hidden bg-white dark:bg-gray-900">
            <div class="modal-header text-white px-4 py-3.5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gradient-to-r from-gray-800 to-gray-900 dark:from-gray-800 dark:to-gray-900">
                <h5 class="modal-title font-bold m-0 flex items-center gap-2.5 text-white text-base">
                    <div class="rounded-xl flex items-center justify-center w-8 h-8 bg-blue-500/20 text-blue-400">
                        <i class="fas fa-user-plus text-sm"></i>
                    </div>
                    <span>Incluir Médico sin Atenciones en el Reporte</span>
                </h5>
                <button type="button" class="close text-gray-400 hover:text-white outline-none border-0 bg-transparent text-2xl leading-none transition-colors" data-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <div class="modal-body p-4 bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200">
                <div class="info-box-alert p-3.5 mb-4 rounded-xl flex items-start gap-3 border border-l-4 border-l-blue-600 dark:border-l-blue-500 shadow-xs">
                    <i class="fas fa-info-circle text-lg text-blue-600 dark:text-blue-400 shrink-0 mt-0.5"></i>
                    <p class="m-0 text-xs leading-relaxed font-medium">
                        Use este panel para incluir médicos registrados en el sistema que <strong class="font-bold">no tienen atenciones computadas en este mes</strong>.
                    </p>
                </div>
                <div class="card border border-gray-200 dark:border-gray-700 bg-gray-100/80 dark:bg-gray-800 p-4 rounded-xl shadow-xs">
                    <div class="row">
                        <div class="col-md-5 mb-3 mb-md-0">
                            <label for="filter_add_jornada" class="form-label font-bold uppercase text-[10px] tracking-wider mb-2 flex items-center gap-1.5 text-gray-700 dark:text-gray-200">
                                <i class="fas fa-clock text-blue-600 dark:text-blue-400"></i>
                                <span>Filtrar por Jornada:</span>
                            </label>
                            <select id="filter_add_jornada" class="form-control custom-select font-medium text-xs rounded-lg h-9 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500">
                                <option value="TODAS">TODAS LAS JORNADAS</option>
                                <option value="MATUTINA">MATUTINA</option>
                                <option value="VESPERTINA">VESPERTINA</option>
                                <option value="FIN DE SEMANA">FIN DE SEMANA</option>
                            </select>
                        </div>
                        <div class="col-md-7">
                            <label for="select_add_medico" class="form-label font-bold uppercase text-[10px] tracking-wider mb-2 flex items-center gap-1.5 text-gray-700 dark:text-gray-200">
                                <i class="fas fa-user-md text-blue-600 dark:text-blue-400"></i>
                                <span>Seleccionar Médico:</span>
                            </label>
                            <select id="select_add_medico" class="form-control select2 w-100">
                                <option value="">Seleccione médico a incluir...</option>
                                @foreach($todosLosMedicos as $m)
                                    <option value="{{ $m->id }}" data-jornada="{{ $m->JORNADA }}">{{ $m->NOM_MED }} ({{ $m->JORNADA }})</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-end bg-gray-50 dark:bg-gray-900 gap-2">
                <button type="button" class="btn font-semibold text-uppercase px-4 py-2 text-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all shadow-xs" data-dismiss="modal">
                    <i class="fas fa-times mr-1.5"></i> Cancelar
                </button>
                <button type="button" onclick="agregarMedicoTable()" class="btn btn-primary font-bold text-uppercase px-4 py-2 text-xs rounded-lg shadow-sm bg-blue-600 hover:bg-blue-500 text-white border-0 transition-all flex items-center gap-1.5">
                    <i class="fas fa-plus-circle"></i>
                    <span>Agregar al Cuadro</span>
                </button>
            </div>
        </div>
    </div>
</div>

<!-- Modal Exportar Excel -->
<div class="modal fade" id="exportExcelModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <div class="modal-content border border-gray-200 dark:border-gray-700 shadow-2xl rounded-2xl overflow-hidden bg-white dark:bg-gray-900">
            <div class="modal-header bg-gradient-to-r from-emerald-700 to-teal-800 dark:from-gray-800 dark:to-gray-900 text-white px-4 py-3.5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h5 class="modal-title font-bold text-white text-base flex items-center gap-2 m-0">
                    <div class="rounded-xl flex items-center justify-center w-8 h-8 bg-emerald-500/20 text-emerald-400">
                        <i class="fas fa-file-excel text-sm"></i>
                    </div>
                    <span>Exportar Reporte a Excel</span>
                </h5>
                <button type="button" class="close text-gray-400 hover:text-white outline-none border-0 bg-transparent text-2xl leading-none transition-colors" data-dismiss="modal">&times;</button>
            </div>
            <form action="{{ route('informes.hora-medico.export-excel') }}" method="GET">
                <div class="modal-body p-4 bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200">
                    <div class="row">
                        <div class="col-md-6">
                            <label class="font-bold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-wider mb-1.5 block">Desde:</label>
                            <div class="row">
                                <div class="col-7">
                                    <select name="mes_inicio" class="form-control form-control-sm rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                        @foreach($meses as $m) <option value="{{$m}}" {{$mesNombre == $m ? 'selected' : ''}}>{{$m}}</option> @endforeach
                                    </select>
                                </div>
                                <div class="col-5">
                                    <select name="ano_inicio" class="form-control form-control-sm rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                        @foreach($anos as $a) <option value="{{$a}}" {{$ano == $a ? 'selected' : ''}}>{{$a}}</option> @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6 mt-3 mt-md-0">
                            <label class="font-bold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-wider mb-1.5 block">Hasta:</label>
                            <div class="row">
                                <div class="col-7">
                                    <select name="mes_fin" class="form-control form-control-sm rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                        @foreach($meses as $m) <option value="{{$m}}" {{$mesNombre == $m ? 'selected' : ''}}>{{$m}}</option> @endforeach
                                    </select>
                                </div>
                                <div class="col-5">
                                    <select name="ano_fin" class="form-control form-control-sm rounded-lg border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-900 dark:text-white">
                                        @foreach($anos as $a) <option value="{{$a}}" {{$ano == $a ? 'selected' : ''}}>{{$a}}</option> @endforeach
                                    </select>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label class="font-bold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-wider mb-1.5 block">Jornadas (Multiselección):</label>
                            <select name="jornadas[]" class="form-control select2" multiple="multiple">
                                <option value="MATUTINA" selected>MATUTINA</option>
                                <option value="VESPERTINA" selected>VESPERTINA</option>
                                <option value="FIN DE SEMANA" selected>FIN DE SEMANA</option>
                            </select>
                        </div>
                    </div>
                    <div class="row mt-3">
                        <div class="col-md-12">
                            <label class="font-bold text-xs text-gray-700 dark:text-gray-200 uppercase tracking-wider mb-1.5 block">Médicos (Vacío = Todos):</label>
                            <select name="medicos[]" class="form-control select2" multiple="multiple">
                                @if(isset($todosLosMedicos))
                                    @foreach($todosLosMedicos as $m)
                                        <option value="{{ $m->id }}">{{ $m->NOM_MED }} ({{ $m->JORNADA }})</option>
                                    @endforeach
                                @endif
                            </select>
                        </div>
                    </div>
                </div>
                <div class="modal-footer px-4 py-3 border-t border-gray-200 dark:border-gray-700 flex items-center justify-end bg-gray-50 dark:bg-gray-900 gap-2">
                    <button type="button" class="btn font-semibold text-uppercase px-4 py-2 text-xs rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all shadow-xs" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-success text-xs rounded-lg font-bold uppercase px-4 py-2 shadow-sm bg-emerald-600 hover:bg-emerald-500 text-white border-0 transition-all flex items-center gap-1.5">
                        <i class="fas fa-download"></i>
                        <span>Generar Excel</span>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Calculos HSC -->
<div class="modal fade" id="hscModal" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document" style="max-width: 1080px; width: 92%;">
        <div class="modal-content shadow-2xl border border-gray-200 dark:border-gray-700 rounded-2xl overflow-hidden bg-white dark:bg-gray-900">
            <!-- Header -->
            <div class="modal-header bg-gray-100 dark:bg-gray-800 text-gray-900 dark:text-white py-3.5 px-4 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between">
                <h6 class="modal-title font-bold text-sm text-gray-900 dark:text-white flex items-center gap-2 m-0">
                    <div class="w-7 h-7 rounded-xl bg-blue-500/20 flex items-center justify-center text-blue-600 dark:text-blue-400">
                        <i class="fas fa-calculator text-xs"></i>
                    </div>
                    <span>OBSERVACIONES / CÁLCULO DE ACTIVIDADES</span>
                </h6>
                <button type="button" class="close text-gray-400 hover:text-gray-700 dark:hover:text-white outline-none border-0 bg-transparent text-2xl leading-none transition-colors" data-dismiss="modal">&times;</button>
            </div>

            <!-- Body -->
            <div class="modal-body p-0 bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200">
                <form id="hscForm">
                    <input type="hidden" name="medico_id" id="hsc_medico_id">
                    <input type="hidden" name="ano" value="{{ $ano }}">
                    <input type="hidden" name="mes" value="{{ $mesNombre }}">

                    <!-- Top Summary Stats Bar -->
                    <div class="bg-gray-100 dark:bg-gray-800 px-5 py-2.5 border-b border-gray-200 dark:border-gray-700 flex flex-wrap justify-between items-center text-xs gap-3">
                        <div class="flex items-center gap-2.5">
                            <span class="font-black text-gray-700 dark:text-gray-300 uppercase tracking-wider text-xs flex items-center gap-1">
                                <i class="fas fa-user-md text-emerald-600 dark:text-emerald-400"></i> Médico:
                            </span>
                            <span id="hsc_medico_name" class="uppercase font-black text-sm px-3.5 py-1 rounded-xl bg-emerald-600 text-white shadow-sm tracking-wide inline-flex items-center"></span>
                        </div>
                        <div id="hsc_stats_summary" class="flex flex-wrap items-center gap-1.5 text-[11px]">
                            <div class="px-2 border-r border-gray-300 dark:border-gray-700 text-center">
                                <span class="text-gray-600 dark:text-gray-300 block text-[10px] uppercase font-bold"><i class="fas fa-users mr-1" style="color: #60a5fa !important;"></i>Atendidos</span>
                                <strong id="m_stat_atend" class="text-gray-900 dark:text-white text-xs">0</strong>
                            </div>
                            <div class="px-2 border-r border-gray-300 dark:border-gray-700 text-center">
                                <span class="text-gray-600 dark:text-gray-300 block text-[10px] uppercase font-bold"><i class="fas fa-calendar-alt mr-1" style="color: #22d3ee !important;"></i>Días Mes</span>
                                <strong id="m_stat_dias_mes" class="text-gray-900 dark:text-white text-xs">0</strong>
                            </div>
                            <div class="px-2 border-r border-gray-300 dark:border-gray-700 text-center">
                                <span class="text-gray-600 dark:text-gray-300 block text-[10px] uppercase font-bold"><i class="fas fa-clock mr-1" style="color: #fbbf24 !important;"></i>Hrs/Día</span>
                                <strong id="m_stat_hrs_dia" class="text-gray-900 dark:text-white text-xs">0</strong>
                            </div>
                            <div class="px-2 border-r border-gray-300 dark:border-gray-700 text-center flex items-center gap-1.5">
                                <div>
                                    <span class="text-gray-600 dark:text-gray-300 block text-[10px] uppercase font-bold"><i class="fas fa-briefcase mr-1" style="color: #818cf8 !important;"></i>Días Cont</span>
                                    <input type="number" name="dias_contratados" id="m_input_dias_cont" class="form-control form-control-sm text-center font-black px-1 py-0 rounded-lg border border-gray-300 dark:border-gray-600 bg-white dark:bg-zinc-950 text-gray-900 dark:text-white focus:ring-2 focus:ring-blue-500" style="width: 62px; height: 26px; font-size: 0.85rem; display: inline-block;" step="any">
                                </div>
                            </div>
                            <div class="px-2 border-r border-gray-300 dark:border-gray-700 text-center">
                                <span class="text-gray-600 dark:text-gray-300 block text-[10px] uppercase font-bold"><i class="fas fa-history mr-1" style="color: #c084fc !important;"></i>Hrs Cont</span>
                                <strong id="m_stat_hrs_cont" class="text-gray-900 dark:text-white text-xs">0</strong>
                            </div>
                            <div class="px-2 border-r border-gray-300 dark:border-gray-700 text-center">
                                <span class="text-gray-600 dark:text-gray-300 block text-[10px] uppercase font-bold"><i class="fas fa-check-circle mr-1" style="color: #34d399 !important;"></i>Días Cump</span>
                                <strong id="m_stat_dias_cump" class="text-gray-900 dark:text-white text-xs">0</strong>
                            </div>
                            <div class="px-2 border-r border-gray-300 dark:border-gray-700 text-center">
                                <span class="text-gray-600 dark:text-gray-300 block text-[10px] uppercase font-bold"><i class="fas fa-hourglass-half mr-1" style="color: #2dd4bf !important;"></i>Horas Cump</span>
                                <strong id="m_stat_hrs_cump" class="text-gray-900 dark:text-white text-xs">0</strong>
                            </div>
                            <div class="pl-2 text-center">
                                <span class="text-gray-600 dark:text-gray-300 block text-[10px] uppercase font-bold"><i class="fas fa-chart-line mr-1" style="color: #34d399 !important;"></i>Rendimiento</span>
                                <strong id="m_stat_rend_actual" class="text-xs font-black text-blue-600 dark:text-cyan-400">0%</strong>
                            </div>
                        </div>
                    </div>

                    <!-- Main HSC Table -->
                    <div class="table-responsive p-3 bg-slate-50/50 dark:bg-zinc-900/50">
                        <table class="table table-bordered table-sm text-center mb-0 hsc-table">
                            <thead>
                                {{-- Fila 1: Grupos Principales --}}
                                <tr class="hsc-header-row-main">
                                    <th colspan="7" class="bg-oficial-main text-center align-middle font-extrabold uppercase">TOTAL DE HORAS OFICIALES</th>
                                    <th colspan="2" class="bg-vacaciones-main text-center align-middle font-extrabold uppercase">VACACIONES</th>
                                    <th rowspan="2" class="bg-personal-main align-middle font-extrabold uppercase" style="width: 46px;">
                                        <span class="hsc-v-header text-white">PERMISOS PERSONALES.</span>
                                    </th>
                                </tr>
                                {{-- Fila 2: Sub-columnas con texto vertical --}}
                                <tr class="hsc-header-row-sub">
                                    <th class="bg-oficial-sub" style="width: 44px;"><span class="hsc-v-header">FERIADOS /<br>COMPENSATORIOS</span></th>
                                    <th class="bg-oficial-sub" style="width: 44px;"><span class="hsc-v-header">ESFAM.</span></th>
                                    <th class="bg-oficial-sub" style="width: 44px;"><span class="hsc-v-header">ACTIVIDADES DE<br>PROMOCION</span></th>
                                    <th class="bg-oficial-sub" style="width: 44px;"><span class="hsc-v-header">CONGRESOS /<br>TALLERES.</span></th>
                                    <th class="bg-oficial-sub" style="width: 44px;"><span class="hsc-v-header">INVESTIGACION<br>DE CAMPO.</span></th>
                                    <th class="bg-oficial-sub" style="width: 44px;"><span class="hsc-v-header">ASAMBLEAS<br>COLEGIO MEDICO.</span></th>
                                    <th class="bg-oficial-sub" style="width: 44px;"><span class="hsc-v-header">CITAS, INCAPACIDADES<br>IHSS / PRIVADA.</span></th>
                                    <th class="bg-vacaciones-sub" style="width: 44px;"><span class="hsc-v-header">ORDINARIAS.</span></th>
                                    <th class="bg-vacaciones-sub" style="width: 44px;"><span class="hsc-v-header">PROFILACTICAS.</span></th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    {{-- OFICIALES (7) --}}
                                    <td class="td-oficial"><input type="number" name="compensatorio"         class="hsc-td-input" value="0" step="any"></td>
                                    <td class="td-oficial"><input type="number" name="esfam"                 class="hsc-td-input" value="0" step="any"></td>
                                    <td class="td-oficial"><input type="number" name="promocion"             class="hsc-td-input" value="0" step="any"></td>
                                    <td class="td-oficial"><input type="number" name="congresos_medicos"     class="hsc-td-input" value="0" step="any"></td>
                                    <td class="td-oficial"><input type="number" name="trabajo_campo"         class="hsc-td-input" value="0" step="any"></td>
                                    <td class="td-oficial"><input type="number" name="convocatoria_general"  class="hsc-td-input" value="0" step="any"></td>
                                    <td class="td-oficial"><input type="number" name="incapacidad"           class="hsc-td-input" value="0" step="any"></td>
                                    {{-- VACACIONES (2) --}}
                                    <td class="td-vacaciones"><input type="number" name="vacaciones_ordinarias" class="hsc-td-input" value="0" step="any"></td>
                                    <td class="td-vacaciones"><input type="number" name="descanso_profilactico" class="hsc-td-input" value="0" step="any"></td>
                                    {{-- PERMISOS PERSONALES (1) --}}
                                    <td class="td-personal"><input type="number" name="permiso_personal"      class="hsc-td-input" value="0" step="any"></td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Campo de Observaciones del Médico -->
                    <div class="px-6 py-4 mb-3 bg-purple-50/40 dark:bg-purple-950/20 border-t border-b border-gray-200 dark:border-gray-700 flex items-center gap-3.5">
                        <label for="m_input_observaciones" class="form-label font-black uppercase text-xs tracking-wider mb-0 text-purple-950 dark:text-purple-200 shrink-0 flex items-center gap-1.5">
                            <i class="fas fa-comment-alt text-purple-600 dark:text-purple-400"></i>
                            <span>Observaciones:</span>
                        </label>
                        <input type="text" name="observaciones" id="m_input_observaciones" 
                               class="form-control text-xs font-bold rounded-xl border-gray-300 dark:border-gray-700 bg-white dark:bg-zinc-950 text-gray-900 dark:text-white px-3.5 py-2 flex-1 focus:ring-2 focus:ring-purple-500 shadow-xs"
                               style="height: 42px;"
                               placeholder="Ingrese cualquier observación para este médico en el informe de Observaciones...">
                    </div>

                </form>

                <!-- Unified Footer (Metrics + Actions) with Generous Separation and Bold Labels -->
                <div class="px-6 py-4 bg-slate-100 dark:bg-zinc-950 border-t border-gray-300 dark:border-zinc-800 flex flex-nowrap items-center justify-between gap-4 overflow-x-auto text-xs text-gray-900 dark:text-white">
                    <!-- Left: Metrics with Extra Bold Typography -->
                    <div class="flex items-center gap-4 shrink-0">
                        <div class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-amber-500/15 border border-amber-500/30">
                            <span class="text-amber-900 dark:text-amber-200 font-black uppercase text-xs tracking-wider">OFICIALES:</span>
                            <span id="res_oficiales" class="text-amber-700 dark:text-amber-400 font-black text-sm">0</span>
                        </div>
                        <div class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-blue-500/15 border border-blue-500/30">
                            <span class="text-blue-900 dark:text-blue-200 font-black uppercase text-xs tracking-wider">VACACIONES:</span>
                            <span id="res_vacaciones" class="text-blue-700 dark:text-blue-400 font-black text-sm">0</span>
                        </div>
                        <div class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-purple-500/15 border border-purple-500/30">
                            <span class="text-purple-900 dark:text-purple-200 font-black uppercase text-xs tracking-wider">PERSONALES:</span>
                            <span id="res_personales" class="text-purple-700 dark:text-purple-400 font-black text-sm">0</span>
                        </div>
                    </div>

                    <!-- Center: Performance & Total HSC with Extra Bold Typography -->
                    <div class="flex items-center gap-4 shrink-0">
                        <div class="flex items-center gap-2 px-3.5 py-2 rounded-xl bg-cyan-500/15 border border-cyan-500/30">
                            <span class="text-cyan-900 dark:text-cyan-200 font-black uppercase text-xs tracking-wider">RENDIMIENTO:</span>
                            <span id="res_rendimiento_modal" class="text-cyan-700 dark:text-cyan-400 font-black text-base">0%</span>
                        </div>
                        <div class="flex items-center gap-2 px-4 py-2 rounded-xl bg-emerald-500/15 border border-emerald-500/30">
                            <span class="text-emerald-900 dark:text-emerald-200 font-black uppercase text-xs tracking-wider">TOTAL HSC:</span>
                            <span id="res_total_general" class="text-emerald-700 dark:text-emerald-400 font-black text-lg">0</span>
                        </div>
                    </div>

                    <!-- Right: Action Buttons with Clear Margins -->
                    <div class="flex items-center gap-3 shrink-0 ml-auto">
                        <button type="button" class="btn text-xs rounded-xl font-black uppercase px-4 py-2.5 border border-gray-400 dark:border-gray-600 bg-white dark:bg-zinc-800 text-gray-800 dark:text-gray-100 hover:bg-gray-200 dark:hover:bg-zinc-700 transition-all shadow-xs" data-dismiss="modal">
                            CANCELAR
                        </button>
                        <button type="button" class="btn text-xs rounded-xl font-black uppercase px-6 py-2.5 bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white border-0 shadow-md hover:shadow-lg transition-all flex items-center gap-2" onclick="saveHSC()">
                            <i class="fas fa-save text-xs"></i>
                            <span>GUARDAR DATOS</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Asignar Director del Mes -->
<div class="modal fade" id="directorMensualModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content border border-gray-200 dark:border-gray-700 shadow-2xl rounded-2xl overflow-hidden bg-white dark:bg-gray-900">
            <div class="modal-header text-white px-4 py-3.5 border-b border-gray-200 dark:border-gray-700 flex items-center justify-between bg-gradient-to-r from-gray-800 to-gray-900 dark:from-gray-800 dark:to-gray-900">
                <h5 class="modal-title font-bold m-0 flex items-center gap-2.5 text-white text-base">
                    <div class="rounded-xl flex items-center justify-center w-8 h-8 bg-amber-500/20 text-amber-400">
                        <i class="fas fa-user-tie text-sm"></i>
                    </div>
                    <span>Director del Mes ({{ $mesNombre }} {{ $ano }})</span>
                </h5>
                <button type="button" class="close text-gray-400 hover:text-white outline-none border-0 bg-transparent text-2xl leading-none transition-colors" data-dismiss="modal" aria-label="Close">&times;</button>
            </div>
            <form id="formDirectorMensual" onsubmit="guardarDirectorMensual(event)">
                @csrf
                <input type="hidden" name="ano" value="{{ $ano }}">
                <input type="hidden" name="mes" value="{{ $mesNombre }}">
                <input type="hidden" name="jornada" value="{{ $jornada }}">

                <div class="modal-body p-4 bg-white dark:bg-gray-900 text-gray-800 dark:text-gray-200">
                    <div class="info-box-alert p-3.5 mb-4 rounded-xl flex items-start gap-3 border border-l-4 border-l-amber-600 dark:border-l-amber-500 shadow-xs">
                        <i class="fas fa-info-circle text-lg text-amber-600 dark:text-amber-400 shrink-0 mt-0.5"></i>
                        <p class="m-0 text-xs leading-relaxed font-medium">
                            Seleccione el médico que ejercerá como <strong class="font-bold">Director del Establecimiento</strong> durante el mes de <strong class="font-bold">{{ $mesNombre }} {{ $ano }}</strong>. Este médico encabezará la casilla #1 en el reporte.
                        </p>
                    </div>
                    <div class="form-group mb-3">
                        <label for="select_director_mensual" class="form-label font-bold uppercase text-[10px] tracking-wider mb-2 flex items-center gap-1.5 text-gray-700 dark:text-gray-200">
                            <i class="fas fa-user-md text-amber-600 dark:text-amber-400"></i>
                            <span>Seleccionar Director:</span>
                        </label>
                        <select id="select_director_mensual" name="medico_id" class="form-control select2 font-medium text-xs rounded-lg h-9 border-gray-300 dark:border-gray-600 bg-white dark:bg-gray-900 text-gray-900 dark:text-white" style="width: 100%;" required>
                            <option value="">-- Seleccione el Médico Director --</option>
                            @foreach($todosLosMedicos as $m)
                                <option value="{{ $m->id }}" {{ (isset($currentDirectorId) && $currentDirectorId == $m->id) ? 'selected' : '' }}>
                                    {{ $m->NOM_MED }} {{ !empty($m->ESPECIALIDAD) ? '('.$m->ESPECIALIDAD.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="modal-footer px-4 py-3 bg-gray-50 dark:bg-gray-800/80 border-t border-gray-200 dark:border-gray-700 flex justify-end gap-2">
                    <button type="button" class="btn btn-secondary text-xs rounded-lg px-3 py-1.5 font-bold" data-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-amber text-xs rounded-lg px-3 py-1.5 font-bold text-white bg-amber-600 hover:bg-amber-700">Guardar Director</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function guardarDirectorMensual(e) {
    e.preventDefault();
    const formData = new FormData(document.getElementById('formDirectorMensual'));
    $.ajax({
        url: "{{ route('informes.hora-medico.save-director-mensual') }}",
        method: "POST",
        data: formData,
        processData: false,
        contentType: false,
        success: function(response) {
            if (response.success) {
                $('#directorMensualModal').modal('hide');
                Swal.fire({
                    icon: 'success',
                    title: 'Director Guardado',
                    text: 'El Director del Mes ha sido actualizado y fijado en la casilla #1.',
                    timer: 1500,
                    showConfirmButton: false
                }).then(() => {
                    window.location.reload();
                });
            }
        },
        error: function(err) {
            Swal.fire('Error', 'No se pudo guardar la configuración del Director.', 'error');
        }
    });
}

$(document).on('click', '[data-toggle="modal"]', function(e) {
    e.preventDefault();
    const target = $(this).attr('data-target');
    if (target) {
        $(target).addClass('show').css('display', 'flex');
    }
});

$(document).on('click', '[data-dismiss="modal"]', function(e) {
    e.preventDefault();
    $(this).closest('.modal').removeClass('show').css('display', 'none');
});

$(document).on('click', '.modal', function(e) {
    if ($(e.target).hasClass('modal')) {
        $(this).removeClass('show').css('display', 'none');
    }
});
</script>
