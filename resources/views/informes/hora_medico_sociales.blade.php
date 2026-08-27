<x-app-layout>
    @section('title', 'Productividad Médica - Servicio Social')

    {{-- ===== Page Header Bar (inside main slot) ===== --}}
    <div class="flex flex-wrap items-center justify-between gap-2 py-1.5 px-2 mb-1 bg-white dark:bg-gray-900 border border-gray-200 dark:border-gray-800 rounded-xl shadow-sm">
        <div class="flex items-center space-x-3">
            <div>
                <h2 class="font-bold text-base text-gray-900 dark:text-white leading-none mb-0.5">Hora Médico - Servicio Social</h2>
                <p class="text-[9px] font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-[0.2em] m-0">Informe de Médicos en Servicio Social (Todas las Jornadas)</p>
            </div>
            {{-- Navigation Tabs --}}
            <div class="flex items-center bg-gray-200/70 dark:bg-gray-800/90 p-1 rounded-xl border border-gray-300/80 dark:border-gray-700/80">
                <a href="{{ route('informes.hora-medico') }}"
                   class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all {{ request()->routeIs('informes.hora-medico') && !request()->routeIs('informes.hora-medico.servicio-social') && !request()->routeIs('informes.hora-medico.consolidado') ? 'bg-blue-600 text-white dark:bg-blue-600 dark:text-white shadow-xs' : 'bg-slate-600/90 dark:bg-slate-700/90 text-white dark:text-white border border-slate-500/50 dark:border-slate-600/50 hover:bg-slate-700 dark:hover:bg-slate-600' }}" style="text-decoration: none;">
                    <i class="fas fa-user-md mr-1.5"></i> Generales y Especialistas
                </a>
                <a href="{{ route('informes.hora-medico.servicio-social') }}"
                   class="px-3 py-1.5 text-xs font-bold rounded-lg transition-all {{ request()->routeIs('informes.hora-medico.servicio-social') ? 'bg-blue-600 text-white dark:bg-blue-600 dark:text-white shadow-xs' : 'bg-slate-600/90 dark:bg-slate-700/90 text-white dark:text-white border border-slate-500/50 dark:border-slate-600/50 hover:bg-slate-700 dark:hover:bg-slate-600' }}" style="text-decoration: none;">
                    <i class="fas fa-graduation-cap mr-1.5"></i> Servicio Social
                </a>
            </div>
        </div>
        <div class="flex items-center space-x-2">
            <!-- Botón Observaciones -->
            <a href="{{ route('informes.hora-medico.consolidado', ['ano' => $ano, 'mes' => $mesNombre, 'jornada' => 'SERVICIO SOCIAL']) }}" 
               class="btn btn-sm btn-info text-white font-bold flex items-center gap-1.5 shadow-sm"
               style="background: linear-gradient(135deg, #6366f1, #4f46e5); border: none; padding: 5px 12px; border-radius: 8px; text-decoration: none; font-size: 11px; white-space: nowrap;"
               title="Ver Informe Oficial de Observaciones (Servicio Social)">
                <i class="bi bi-journal-text text-sm"></i> Observaciones
            </a>

            <button
                class="p-2 rounded-lg bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-500 text-white shadow-xs transition-all border-0 flex items-center justify-center"
                data-toggle="modal" data-target="#addMedicoModal" title="Incluir Médico Social">
                <i class="fas fa-user-plus text-xs"></i>
            </button>
            <div class="h-4 w-[1px] bg-gray-300 dark:bg-gray-700 mx-1"></div>
            <button type="button"
                class="font-semibold flex items-center px-3 rounded-lg h-8 text-[10px] bg-white dark:bg-gray-800 text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-700 hover:bg-gray-100 dark:hover:bg-gray-700 transition-all uppercase shadow-xs"
                onclick="window.location.reload()">
                <svg class="w-3 h-3 mr-1.5" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round"
                        d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"></path>
                </svg>
                Actualizar
            </button>
        </div>
    </div>

    {{-- CSS --}}
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
    <style>
        .select2-container--default .select2-selection--multiple {
            border: 1px solid #ced4da;
            border-radius: .25rem;
        }

        .select2-container {
            width: 100% !important;
        }
        #mainTable td {
            padding: 2px 4px !important;
            vertical-align: middle !important;
            height: auto !important;
        }
        #mainTable th.sticky-col-2,
        #mainTable td.sticky-col-2,
        #mainTable .sticky-col-2 {
            text-align: left !important;
            padding-left: 8px !important;
            padding-right: 4px !important;
        }

        .btn-hsc-modal {
            padding: 0px 4px !important;
            font-size: 0.7rem !important;
            line-height: 1 !important;
            height: 18px !important;
            width: 22px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            border-radius: 2px !important;
        }

        .btn-hsc-modal i {
            font-size: 0.65rem !important;
        }

        .obs-column {
            white-space: normal !important;
            word-wrap: break-word !important;
            min-height: 20px !important;
        }

        .sticky-thead th {
            position: sticky;
            top: 0;
            z-index: 10;
            background-color: #f8fafc;
            border-bottom: 2px solid #cbd5e1;
        }

        html.dark .sticky-thead th,
        .dark .sticky-thead th {
            background-color: #1e293b !important;
            color: #f8fafc !important;
            border-bottom: 2px solid #334155 !important;
        }

        .modal:not(.show) {
            display: none !important;
        }

        .modal-backdrop.show {
            opacity: 0.5;
            z-index: 1040;
        }

        .modal.show {
            z-index: 1050;
        }

    </style>

    <div class="report-container fade-in">
        <!-- Header Oficial (Solo para Impresión) -->
        <div class="official-header-print hidden print:block p-1 mb-1"
            style="background-color: transparent; color: #000; border: none;">
            <div class="row align-items-center no-gutters">
                <div class="col-2 text-left px-3">
                    <img src="{{ asset('img/logos/logo_izquierdo.png') }}" alt="Logo Izquierdo" id="img_logo_izquierdo_print"
                         style="width: <?php echo $settings['logo_izquierdo_width'] ?? 'auto'; ?>; height: <?php echo $settings['logo_izquierdo_height'] ?? '70px'; ?>; object-fit: contain;">
                </div>
                <div class="col-8 text-center p-0">
                    <h6 class="mb-0 font-weight-bold" style="font-size: 1rem; line-height: 1.1;">REGION SANITARIA METROPOLITANA DISTRITO CENTRAL</h6>
                    <h6 class="mb-0 font-weight-bold" style="font-size: 1rem; line-height: 1.1;">AREA DE GESTION A LA INFORMACION</h6>
                    <h6 class="mb-0 font-weight-bold" style="font-size: 1rem; line-height: 1.1;">INFORME DE CONSULTAS BRINDADAS POR MEDICO - SERVICIO SOCIAL</h6>
                </div>
                <div class="col-2 text-right px-3">
                    <img src="{{ asset('img/logos/logo_derecho.png') }}" alt="Logo Derecho" id="img_logo_derecho_print"
                         style="width: <?php echo $settings['logo_derecho_width'] ?? 'auto'; ?>; height: <?php echo $settings['logo_derecho_height'] ?? '70px'; ?>; object-fit: contain;">
                </div>
            </div>

            <div class="row align-items-end mt-1 px-2 no-gutters">
                <div class="col-4">
                    <div class="font-weight-bold" style="font-size: 0.85rem;">JORNADA: TODAS LAS JORNADAS (SERVICIO SOCIAL)</div>
                    <div class="font-weight-bold" style="font-size: 0.85rem; line-height: 1;">CENTRO INTEGRAL DE SALUD SAN MIGUEL</div>
                </div>
                <div class="col-2 text-center">
                    <span class="font-weight-bold" style="font-size: 0.85rem;">MES: {{ $mesNombre }}</span>
                </div>
                <div class="col-3 text-center">
                    <span class="font-weight-bold" style="font-size: 0.85rem;">AÑO: {{ $ano }}</span>
                </div>
                <div class="col-3 text-right">
                    <h6 class="font-weight-bold mb-0" style="font-size: 1.1rem;">Hora Medico - Servicio Social</h6>
                </div>
            </div>
        </div>

        <!-- Barra de Filtros (Solo para Pantalla) -->
        <div class="filter-container flex flex-wrap items-center justify-between gap-3 p-3 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 shadow-theme-xs mb-3 no-print">
            <div class="flex items-center gap-2">
                <i class="fas fa-filter text-brand-500"></i>
                <span class="text-xs font-bold uppercase text-gray-900 dark:text-white">Filtros</span>
            </div>
            <div class="flex flex-wrap items-center gap-3">
                <div class="flex items-center gap-1.5">
                    <span class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">JORNADA:</span>
                    <select name="jornada" class="filter-select h-8 text-xs rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-2" disabled>
                        <option value="TOTAL JORNADAS" selected>TODAS LAS JORNADAS (SERVICIO SOCIAL)</option>
                    </select>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">MES:</span>
                    <select name="mes" class="filter-select h-8 text-xs rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-2"
                        onchange="updateFilters()">
                        @foreach($meses as $m)
                            <option value="{{ $m }}" {{ $mesNombre == $m ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex items-center gap-1.5">
                    <span class="text-xs font-semibold uppercase text-gray-500 dark:text-gray-400">AÑO:</span>
                    <select name="ano" class="filter-select h-8 text-xs rounded-xl border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-2"
                        onchange="updateFilters()">
                        @foreach($anos as $a)
                            <option value="{{ $a }}" {{ $ano == $a ? 'selected' : '' }}>{{ $a }}</option>
                        @endforeach
                    </select>
                </div>
            </div>
        </div>

        <!-- Tabla Principal -->
        <div class="table-container-wrapper table-responsive rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-theme-xs p-2 overflow-x-auto">
            <table class="table table-bordered text-xs text-center mb-0 w-full" id="mainTable">
                <thead class="sticky-thead">
                    <tr class="header-row" style="height: 25px;">
                        <th rowspan="2" class="align-middle border-black sticky-header" style="width: 30px; top: 0; left: 0; z-index: 90;">N°</th>
                        <th rowspan="2" class="align-middle border-black sticky-header text-center" style="width: 200px; top: 0; left: 30px; z-index: 90;">NOMBRE</th>
                        <th rowspan="2" class="vertical-text border-black sticky-header" style="width: 30px; top: 0; z-index: 80;">ACUERDO</th>
                        <th rowspan="2" class="vertical-text border-black sticky-header" style="width: 30px; top: 0; z-index: 80;">CONTRATO</th>
                        <th rowspan="2" class="vertical-text border-black sticky-header" style="width: 30px; top: 0; z-index: 80;">M. GENERAL</th>
                        <th rowspan="2" class="vertical-text border-black sticky-header" style="width: 30px; top: 0; z-index: 80;">M. ESPECIALISTA</th>
                        <th rowspan="2" class="vertical-text border-black sticky-header" style="width: 35px; top: 0; z-index: 80;">HRS CONTRATADAS POR DIA</th>
                        <th rowspan="2" class="vertical-text border-black sticky-header" style="width: 35px; top: 0; z-index: 80;">DIAS CONTRATADOS AL MES</th>
                        <th rowspan="2" class="vertical-text border-black sticky-header" style="width: 35px; top: 0; z-index: 80;">DIAS CUMPLIDOS AL MES</th>
                        <th rowspan="2" class="vertical-text border-black sticky-header" style="width: 35px; top: 0; z-index: 80;">HORAS CONTRATADAS EN EL MES</th>
                        <th rowspan="2" class="vertical-text border-black sticky-header" style="width: 35px; top: 0; z-index: 80;">HORAS CUMPLIDAS EN EL MES</th>
                        <th rowspan="2" class="vertical-text border-black sticky-header" style="width: 45px; top: 0; z-index: 80;">PACIENTES PROGRAMADOS</th>
                        <th rowspan="2" class="vertical-text border-black sticky-header" style="width: 45px; top: 0; z-index: 80;">PACIENTES REPROGRAMADOS</th>
                        <th rowspan="2" class="vertical-text border-black sticky-header" style="width: 45px; top: 0; z-index: 80;">PACIENTES ATENDIDOS</th>
                        <th rowspan="2" class="vertical-text border-black sticky-header" style="width: 40px; top: 0; z-index: 80;">RENDIMIENTO MEDICO %</th>
                        <th colspan="3" class="border-black sticky-header" style="top: 0; z-index: 80;">HORAS PERDIDAS POR CAUSA JUSTIFICADA</th>
                        <th rowspan="2" class="align-middle border-black sticky-header" style="width: 250px; top: 0; z-index: 80;">OBSERVACIONES</th>
                    </tr>
                    <tr class="header-row" style="height: 25px;">
                        <th class="vertical-text border-black sticky-header" style="width: 30px; top: 25px; z-index: 80;">OFICIALES</th>
                        <th class="vertical-text border-black sticky-header" style="width: 30px; top: 25px; z-index: 80;">VACACIONES</th>
                        <th class="vertical-text border-black sticky-header" style="width: 30px; top: 25px; z-index: 80;">PERSONALES</th>
                    </tr>
                </thead>
                <tbody id="tableContent">
                    @include('informes.hora_medico_table')
                </tbody>
            </table>
        </div>
    </div>

    <!-- Modales -->
    @include('informes.hora_medico_modales')

    <!-- Sticky Action Footer Bar -->
    <div class="report-footer-screen fixed bottom-0 left-0 right-0 z-40 bg-white/95 dark:bg-gray-900/95 backdrop-blur-md border-t border-gray-200 dark:border-gray-800 shadow-lg py-2 px-4 no-print">
        <div class="max-w-7xl mx-auto flex flex-wrap items-center justify-between gap-2">
            <div class="flex items-center gap-2">
                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-blue-100 text-blue-800 dark:bg-blue-950 dark:text-blue-300">
                    <i class="fas fa-graduation-cap mr-1"></i> Servicio Social
                </span>
            </div>
            <div class="flex items-center gap-2">
                <button type="button" onclick="imprimirReporte()" 
                    class="inline-flex items-center px-3 py-1.5 rounded-lg text-xs font-bold bg-blue-600 text-white hover:bg-blue-700 transition-colors shadow-xs">
                    <i class="fas fa-print mr-1.5"></i> Imprimir Informe Social
                </button>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        function updateFilters() {
            let ano = $('[name="ano"]').val();
            let mes = $('[name="mes"]').val();
            let jornada = 'TOTAL JORNADAS';

            $('#tableContent').css('opacity', '0.5');

            $.get("{{ route('informes.hora-medico.servicio-social') }}", {
                ano: ano,
                mes: mes,
                jornada: jornada
            }, function (html) {
                $('#mainTable thead').nextAll().remove();
                $('#mainTable').append(html);
                $('#tableContent').css('opacity', '1');

                const url = new URL(window.location);
                url.searchParams.set('ano', ano);
                url.searchParams.set('mes', mes);
                window.history.pushState({}, '', url);
            });
        }

        function imprimirReporte() {
            let ano = $('[name="ano"]').val();
            let mes = $('[name="mes"]').val();
            let jornada = 'TOTAL JORNADAS';

            const url = new URL("{{ route('informes.hora-medico.imprimir') }}");
            url.searchParams.set('ano', ano);
            url.searchParams.set('mes', mes);
            url.searchParams.set('jornada', jornada);

            window.open(url.toString(), '_blank');
        }

        $(document).ready(function () {
            $(document).on('click', '.btn-hsc-modal', function () {
                const d = $(this).data();
                openHSCModal(d.id, d.name, d.atenciones, d.prog, d.pxh, d.diasmes, d.diascont, d.hrsdia, d.rend, d.observaciones);
            });
        });

        function openHSCModal(medicoId, name, atenciones, prog, pxh, diasMes, diasCont, hrsDia, rendActual, initialObs = '') {
            let currentAno = $('[name="ano"]').val();
            let currentMes = $('[name="mes"]').val();

            $('#hsc_medico_id').val(medicoId);
            $('#hscForm [name="ano"]').val(currentAno);
            $('#hscForm [name="mes"]').val(currentMes);
            $('#hsc_medico_name').text(name);

            $('#m_stat_atend').text(atenciones);
            $('#m_stat_dias_mes').text(diasMes);
            $('#m_stat_hrs_dia').text(hrsDia);
            $('#m_input_dias_cont').val(diasCont);
            $('#m_stat_hrs_cont').text(Math.round(hrsDia * diasCont));
            let initialRend = Math.round(rendActual) + '%';
            $('#m_stat_rend_actual').text(initialRend);
            $('#res_rendimiento_modal').text(initialRend);

            $('#hscForm .hsc-td-input').val(0);
            $('#hscForm textarea').val('');
            $('#m_input_observaciones').val(initialObs || '');

            $.get("{{ route('informes.hora-medico.get-hsc') }}", {
                medico_id: medicoId,
                ano: currentAno,
                mes: currentMes
            }, function (data) {
                if (data.id) {
                    Object.keys(data).forEach(key => {
                        let val = data[key];
                        if ($.isNumeric(val) && !['id', 'medico_id', 'ano', 'created_at', 'updated_at'].includes(key)) {
                            val = parseFloat(val);
                        }
                        $(`[name="${key}"]`).val(val);
                    });
                    if (data.observaciones !== undefined) {
                        $('#m_input_observaciones').val(data.observaciones || '');
                    }
                }
                $('.hsc-td-input').first().trigger('input');
            });
            $('#hscModal').modal('show');
        }

        function saveHSC() {
            let formData = $('#hscForm').serialize();
            Swal.fire({ title: 'Guardando...', didOpen: () => { Swal.showLoading(); } });

            $.post("{{ route('informes.hora-medico.save-hsc') }}", formData + "&_token={{ csrf_token() }}", function (res) {
                Swal.close();
                if (res.success) {
                    Swal.fire({ icon: 'success', title: 'Guardado', timer: 1000, showConfirmButton: false })
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
                Swal.fire({ icon: 'error', title: 'Error 500', text: errorMsg });
            });
        }
    </script>
</x-app-layout>
