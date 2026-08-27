<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between py-1.5 no-print px-2 sm:px-3 lg:px-4">
            <div class="flex-shrink-0">
                <h2 class="font-bold text-base text-slate-900 dark:text-white leading-none mb-0.5">Informe de
                    Observaciones y Rendimiento Médico</h2>
                <p class="text-[9px] font-semibold text-slate-400 uppercase tracking-[0.2em] m-0">Formato Oficial de
                    Observaciones de Rendimiento Médico</p>
            </div>
            <div class="flex items-center space-x-2">
                <span
                    class="badge badge-info py-1.5 px-3 text-xs font-bold rounded-lg bg-blue-100 dark:bg-blue-900/60 text-blue-800 dark:text-blue-200 border border-blue-200 dark:border-blue-800">
                    <i class="fas fa-user-md mr-1 text-blue-600 dark:text-blue-400"></i> <span
                        id="medicoCount">{{ count($data) }}</span> Médicos en Orden Histórico
                </span>
            </div>
        </div>
    </x-slot>

    <div class="report-container fade-in">
        <!-- Barra Consolidada de Acciones y Filtros (MISMO DISEÑO EXACTO DE HORA MEDICO) -->
        <div
            class="filter-container flex items-center justify-between gap-2 p-1.5 rounded-2xl border border-gray-200 bg-white dark:border-gray-800 dark:bg-gray-900 shadow-theme-xs mb-2 no-print overflow-x-auto no-scrollbar">
            <!-- Izquierda: Pestañas de Navegación y Botones de Acción -->
            <div class="flex items-center gap-1.5 flex-nowrap shrink-0">
                <!-- Pestañas de Navegación -->
                <div
                    class="flex items-center bg-gray-200/70 dark:bg-gray-800/90 p-0.5 rounded-xl border border-gray-300/80 dark:border-gray-700/80 shrink-0">
                    {{-- Tab Generales y Especialistas: active when jornada != SERVICIO SOCIAL --}}
                    <button type="button" id="tabGenerales" onclick="switchConsolidadoTab('generales')"
                        class="px-2.5 py-1 text-xs font-bold rounded-lg transition-all flex items-center gap-1 border {{ $jornada !== 'SERVICIO SOCIAL' ? 'bg-blue-600 text-white dark:bg-blue-600 dark:text-white shadow-xs border-transparent' : 'bg-slate-600/90 dark:bg-slate-700/90 text-white dark:text-white border-slate-500/50 dark:border-slate-600/50 hover:bg-slate-700 dark:hover:bg-slate-600' }}">
                        <i class="fas fa-user-md text-xs"></i>
                        <span>Generales y Especialistas</span>
                    </button>
                    {{-- Tab Servicio Social: active when jornada == SERVICIO SOCIAL --}}
                    <button type="button" id="tabSociales" onclick="switchConsolidadoTab('sociales')"
                        class="px-2.5 py-1 text-xs font-bold rounded-lg transition-all flex items-center gap-1 border {{ $jornada === 'SERVICIO SOCIAL' ? 'bg-blue-600 text-white dark:bg-blue-600 dark:text-white shadow-xs border-transparent' : 'bg-slate-600/90 dark:bg-slate-700/90 text-white dark:text-white border-slate-500/50 dark:border-slate-600/50 hover:bg-slate-700 dark:hover:bg-slate-600' }}">
                        <i class="fas fa-graduation-cap text-xs"></i>
                        <span>Servicio Social</span>
                    </button>
                </div>

                <!-- Botón Hora Médico (Para regresar a Hora Médico) -->
                <a href="{{ route('informes.hora-medico', ['ano' => $ano, 'mes' => $mes, 'jornada' => $jornada]) }}"
                    class="font-bold flex items-center px-2.5 py-1 text-xs rounded-lg bg-purple-600 hover:bg-purple-700 text-white shadow-xs uppercase border-0 shrink-0 gap-1 transition-all"
                    style="text-decoration: none;" title="Regresar a Hora Médico">
                    <i class="fas fa-clock text-xs"></i>
                    <span>Hora Medico</span>
                </a>

                <!-- Botón Agregar / Nueva Observación -->
                <button type="button"
                    class="font-bold flex items-center px-2.5 py-1 text-xs rounded-lg bg-indigo-600 hover:bg-indigo-700 text-white shadow-xs uppercase border-0 shrink-0 gap-1.5 transition-all"
                    onclick="abrirModalNuevaObservacion()" title="Agregar o editar observación para un médico">
                    <i class="fas fa-plus-circle text-xs"></i>
                    <span>Nueva Observación</span>
                </button>

                <!-- Botón Incluir Médico (Solo Icono 28x28px) -->
                <button type="button"
                    class="w-7 h-7 rounded-lg bg-blue-600 hover:bg-blue-700 dark:bg-blue-600 dark:hover:bg-blue-500 text-white shadow-xs transition-all border-0 flex items-center justify-center shrink-0"
                    data-toggle="modal" data-target="#addMedicoModal" title="Incluir Médico">
                    <i class="fas fa-user-plus text-xs"></i>
                </button>

                <!-- Botón Director del Mes (Solo Icono 28x28px) -->
                <button type="button"
                    class="w-7 h-7 rounded-lg bg-amber-600 hover:bg-amber-700 dark:bg-amber-600 dark:hover:bg-amber-500 text-white shadow-xs transition-all border-0 flex items-center justify-center shrink-0"
                    data-toggle="modal" data-target="#directorMensualModal"
                    title="Asignar Director del Mes ({{ $mes }} {{ $ano }})">
                    <i class="fas fa-user-tie text-xs"></i>
                </button>

                <!-- Botón Exportar Excel (Solo Icono 28x28px) -->
                <button type="button"
                    class="w-7 h-7 rounded-lg bg-emerald-600 hover:bg-emerald-700 dark:bg-emerald-600 dark:hover:bg-emerald-500 text-white shadow-xs transition-all border-0 flex items-center justify-center shrink-0"
                    data-toggle="modal" data-target="#exportExcelModal" title="Exportar Excel">
                    <i class="fas fa-file-excel text-xs"></i>
                </button>

                <!-- Botón Actualizar (Solo Icono 28x28px) -->
                <button type="button"
                    class="w-7 h-7 rounded-lg bg-white hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-700 transition-all shadow-xs flex items-center justify-center shrink-0"
                    onclick="window.location.reload()" title="Actualizar">
                    <i class="fas fa-rotate text-xs text-gray-500 dark:text-gray-400"></i>
                </button>

                <!-- Pantalla Completa & Imprimir (Solo Iconos 28x28px) -->
                <button type="button" onclick="toggleFullScreen()"
                    class="w-7 h-7 rounded-lg bg-white hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-700 transition-all shadow-xs flex items-center justify-center shrink-0"
                    title="Pantalla Completa">
                    <i class="fas fa-expand text-xs"></i>
                </button>
                <button type="button" id="btnImprimirConsolidado"
                    class="w-7 h-7 rounded-lg bg-white hover:bg-gray-100 dark:bg-gray-800 dark:hover:bg-gray-700 text-gray-700 dark:text-gray-200 border border-gray-300 dark:border-gray-700 transition-all shadow-xs flex items-center justify-center shrink-0"
                    title="Imprimir Reporte">
                    <i class="fas fa-print text-xs"></i>
                </button>
            </div>

            <!-- Derecha: Selectores de Filtro (MISMO DISEÑO EXACTO DE HORA MEDICO) -->
            <form action="{{ route('informes.hora-medico.consolidado') }}" method="GET" id="filterForm"
                class="flex items-center gap-2 flex-nowrap shrink-0 ml-auto m-0 p-0">
                <div class="flex items-center gap-1" id="jornadaFilterWrap">
                    <span
                        class="text-[10px] font-bold uppercase text-gray-500 dark:text-gray-400 whitespace-nowrap">JORNADA:</span>
                    <select name="jornada" id="jornadaSelect"
                        class="filter-select h-7 text-xs rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-2 font-semibold {{ $jornada === 'SERVICIO SOCIAL' ? 'hidden' : '' }}">
                        <option value="MATUTINA" {{ ($jornada == 'MATUTINA' || empty($jornada)) ? 'selected' : '' }}>
                            MATUTINA</option>
                        <option value="VESPERTINA" {{ $jornada == 'VESPERTINA' ? 'selected' : '' }}>VESPERTINA</option>
                        <option value="FIN DE SEMANA" {{ $jornada == 'FIN DE SEMANA' ? 'selected' : '' }}>FIN DE SEMANA
                        </option>
                        <option value="TODAS LAS JORNADAS" {{ ($jornada == 'TODAS LAS JORNADAS' || $jornada == 'TOTAL JORNADAS') ? 'selected' : '' }}>TODAS LAS JORNADAS</option>
                    </select>
                    {{-- Hidden input used when Servicio Social tab is active --}}
                    <input type="hidden" name="jornada" id="jornadaHidden" value="SERVICIO SOCIAL" {{ $jornada !== 'SERVICIO SOCIAL' ? 'disabled' : '' }}>
                    {{-- Show text label when SS is active --}}
                    @if($jornada === 'SERVICIO SOCIAL')
                        <span class="text-xs font-bold text-blue-600 dark:text-blue-400 uppercase">SERVICIO SOCIAL</span>
                    @endif
                </div>

                <div class="flex items-center gap-1">
                    <span
                        class="text-[10px] font-bold uppercase text-gray-500 dark:text-gray-400 whitespace-nowrap">MES:</span>
                    <select name="mes"
                        class="filter-select h-7 text-xs rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-2 font-semibold">
                        @foreach($meses as $m)
                            <option value="{{ $m }}" {{ $mes == $m ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="flex items-center gap-1">
                    <span
                        class="text-[10px] font-bold uppercase text-gray-500 dark:text-gray-400 whitespace-nowrap">AÑO:</span>
                    <select name="ano"
                        class="filter-select h-7 text-xs rounded-lg border border-gray-300 dark:border-gray-700 bg-gray-50 dark:bg-gray-800 text-gray-900 dark:text-white px-2 font-semibold">
                        @foreach($anos as $a)
                            <option value="{{ $a }}" {{ $ano == $a ? 'selected' : '' }}>{{ $a }}</option>
                        @endforeach
                    </select>
                </div>
            </form>
        </div>

        <!-- Contenedor Principal de la Tabla (MISMO DISEÑO Y MEDIDAS EXACTAS DE HORA MEDICO) -->
        <div class="table-container-wrapper table-responsive rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-theme-xs p-3 overflow-x-auto overflow-y-scroll max-h-[calc(100vh-140px)]"
            id="consolidadoContent">
            @include('informes.hora_medico_consolidado_table')
        </div>
    </div>

    @include('informes.hora_medico_modales')

    <!-- Modal para Agregar / Editar Observación -->
    <div class="modal fade" id="modalNuevaObservacion" tabindex="-1" role="dialog" aria-labelledby="modalNuevaObservacionLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered" role="document">
            <div class="modal-content rounded-2xl border border-gray-200 dark:border-gray-800 bg-white dark:bg-gray-900 shadow-2xl overflow-hidden" style="background: var(--bg-surface); color: var(--text-primary); border-color: var(--border-color);">
                <div class="modal-header bg-slate-50 dark:bg-gray-800/60 border-b border-gray-200 dark:border-gray-700 py-3 px-4 flex items-center justify-between" style="background: var(--bg-subtle); border-color: var(--border-color);">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-lg bg-indigo-600 text-white flex items-center justify-center font-bold text-sm shadow-xs" style="width: 32px; height: 32px; border-radius: 8px; background: linear-gradient(135deg, #6366f1, #4f46e5); display: flex; align-items: center; justify-content: center; color: #fff;">
                            <i class="fas fa-comment-medical"></i>
                        </div>
                        <div>
                            <h5 class="modal-title font-bold text-sm mb-0" id="modalNuevaObservacionLabel" style="color: var(--text-primary); font-size: 0.95rem;">
                                Observación de Rendimiento Médico
                            </h5>
                            <p class="text-[10px] text-muted font-semibold mb-0 uppercase tracking-wider" style="font-size: 0.70rem; color: var(--text-muted);">
                                Período: <span class="text-primary font-bold">{{ $mes }} {{ $ano }}</span> - Jornada: <span class="text-primary font-bold">{{ $jornada }}</span>
                            </p>
                        </div>
                    </div>
                    <button type="button" class="close text-muted text-xl p-1 outline-none border-none bg-transparent" data-dismiss="modal" aria-label="Close" style="color: var(--text-primary); opacity: 0.6;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-4 space-y-3.5 text-xs" style="background: var(--bg-surface);">
                    <!-- Selector de Médico -->
                    <div class="mb-3">
                        <label for="obs_modal_medico_id" class="block font-bold uppercase tracking-wider text-[11px] mb-1" style="color: var(--text-primary); font-size: 0.75rem;">
                            Seleccionar Médico:
                        </label>
                        <select id="obs_modal_medico_id" class="form-control text-xs rounded-xl border font-semibold py-1.5 px-2.5 w-full" style="background: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); height: 38px;" onchange="onModalMedicoChange()">
                            <option value="">-- Seleccione un médico --</option>
                            @foreach($todosLosMedicos as $m)
                                <option value="{{ $m->id }}" data-static="{{ $m->observaciones ?? '' }}">
                                    {{ $m->NOM_MED }} {{ !empty($m->ESPECIALIDAD) ? '('.$m->ESPECIALIDAD.')' : '' }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <!-- Observación Fija / Perfil del Médico -->
                    <div id="obs_static_wrap" class="p-2.5 rounded-xl border mb-3 hidden" style="background: var(--bg-subtle); border-color: var(--border-color);">
                        <span class="text-[10px] font-bold uppercase tracking-wider block mb-0.5" style="color: var(--text-muted); font-size: 0.68rem;">
                            <i class="fas fa-id-badge text-primary mr-1"></i> Observación Fija de la Tabla de Médicos:
                        </span>
                        <span id="obs_static_text" class="text-xs font-semibold" style="color: var(--text-primary); font-size: 0.78rem;"></span>
                    </div>

                    <!-- Texto de la Observación Mensual -->
                    <div class="mb-3">
                        <label for="obs_modal_texto" class="block font-bold uppercase tracking-wider text-[11px] mb-1" style="color: var(--text-primary); font-size: 0.75rem;">
                            Observación del Mes ({{ $mes }} {{ $ano }}):
                        </label>
                        <textarea id="obs_modal_texto" rows="3" class="form-control text-xs rounded-xl border font-medium p-2.5 w-full" style="background: var(--input-bg); color: var(--text-primary); border-color: var(--border-color);" placeholder="Escriba la observación para este médico en este mes (ej. VACACIONES DEL 01 AL 15, INCAPACIDAD, etc.)..."></textarea>
                    </div>

                    <!-- Plantillas / Sugerencias Rápidas -->
                    <div>
                        <span class="text-[10px] font-bold uppercase tracking-wider block mb-1.5" style="color: var(--text-muted); font-size: 0.68rem;">Sugerencias Rápidas:</span>
                        <div class="d-flex flex-wrap" style="gap: 5px;">
                            <button type="button" class="btn btn-subtle btn-xs font-semibold py-1 px-2" style="font-size: 0.68rem; border-radius: 6px;" onclick="appendTag('VACACIONES')">+ Vacaciones</button>
                            <button type="button" class="btn btn-subtle btn-xs font-semibold py-1 px-2" style="font-size: 0.68rem; border-radius: 6px;" onclick="appendTag('INCAPACIDAD')">+ Incapacidad</button>
                            <button type="button" class="btn btn-subtle btn-xs font-semibold py-1 px-2" style="font-size: 0.68rem; border-radius: 6px;" onclick="appendTag('PERMISO PERSONAL')">+ Permiso</button>
                            <button type="button" class="btn btn-subtle btn-xs font-semibold py-1 px-2" style="font-size: 0.68rem; border-radius: 6px;" onclick="appendTag('CONGRESO MEDICO')">+ Congreso</button>
                            <button type="button" class="btn btn-subtle btn-xs font-semibold py-1 px-2" style="font-size: 0.68rem; border-radius: 6px;" onclick="appendTag('DIRECTOR DEL ESTABLECIMIENTO')">+ Director</button>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-t py-2.5 px-4 flex items-center justify-between" style="background: var(--bg-subtle); border-color: var(--border-color);">
                    <button type="button" class="btn btn-subtle btn-sm font-semibold px-3 py-1.5 rounded-lg" data-dismiss="modal" style="border-radius: 8px;">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-primary btn-sm font-bold px-4 py-1.5 rounded-lg shadow-sm flex items-center gap-1" style="border-radius: 8px;" onclick="guardarDesdeModalObservacion()">
                        <i class="fas fa-save mr-1"></i> Guardar Observación
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function switchConsolidadoTab(tab) {
            const jornadaSelect = document.getElementById('jornadaSelect');
            const jornadaHidden = document.getElementById('jornadaHidden');
            const tabGenerales = document.getElementById('tabGenerales');
            const tabSociales = document.getElementById('tabSociales');

            const activeClasses = 'bg-blue-600 text-white dark:bg-blue-600 dark:text-white shadow-xs border-transparent';
            const inactiveClasses = 'bg-slate-600/90 dark:bg-slate-700/90 text-white dark:text-white border-slate-500/50 dark:border-slate-600/50 hover:bg-slate-700 dark:hover:bg-slate-600';

            if (tab === 'generales') {
                jornadaSelect.classList.remove('hidden');
                jornadaHidden.disabled = true;
                tabGenerales.className = tabGenerales.className.replace(inactiveClasses, activeClasses);
                tabSociales.className = tabSociales.className.replace(activeClasses, inactiveClasses);
                tabGenerales.classList.remove(...inactiveClasses.split(' '));
                tabGenerales.classList.add(...activeClasses.split(' '));
                tabSociales.classList.remove(...activeClasses.split(' '));
                tabSociales.classList.add(...inactiveClasses.split(' '));
            } else {
                jornadaSelect.classList.add('hidden');
                jornadaHidden.disabled = false;
                tabSociales.classList.remove(...inactiveClasses.split(' '));
                tabSociales.classList.add(...activeClasses.split(' '));
                tabGenerales.classList.remove(...activeClasses.split(' '));
                tabGenerales.classList.add(...inactiveClasses.split(' '));
            }

            updateConsolidado();
        }

        $(document).ready(function () {
            $('#filterForm select').on('change', function (e) {
                e.preventDefault();
                updateConsolidado();
            });

            function updateConsolidado() {
                const form = $('#filterForm');
                const content = $('#consolidadoContent');
                const url = form.attr('action');

                let params = {};
                form.find('select, input[type="text"], input[type="hidden"]:not(:disabled)').each(function () {
                    if (!$(this).prop('disabled') && $(this).attr('name')) {
                        params[$(this).attr('name')] = $(this).val();
                    }
                });

                content.css('opacity', '0.5');

                $.ajax({
                    url: url,
                    type: 'GET',
                    data: params,
                    success: function (response) {
                        content.html(response);
                        content.css('opacity', '1');
                        const count = content.find('tbody tr:not(.empty-row)').filter(function () {
                            return $(this).find('td:first').text().trim() !== '';
                        }).length;
                        $('#medicoCount').text(count);
                    },
                    error: function () {
                        alert('Error al cargar los datos');
                        content.css('opacity', '1');
                    }
                });
            }

            window.updateConsolidado = updateConsolidado;

            $('#btnImprimirConsolidado').on('click', function () {
                let params = {};
                $('#filterForm').find('select, input[type="text"], input[type="hidden"]:not(:disabled)').each(function () {
                    if (!$(this).prop('disabled') && $(this).attr('name')) {
                        params[$(this).attr('name')] = $(this).val();
                    }
                });
                const url = "{{ route('informes.hora-medico.consolidado.imprimir') }}?" + $.param(params);
                window.open(url, '_blank');
            });
        });

        function toggleFullScreen() {
            if (!document.fullscreenElement) {
                document.documentElement.requestFullscreen().catch(err => {
                    alert(`Error intentando activar pantalla completa: ${err.message}`);
                });
            } else {
                if (document.exitFullscreen) {
                    document.exitFullscreen();
                }
            }
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

            const ano = $('[name="ano"]').val() || "{{ $ano }}";
            const mes = $('[name="mes"]').val() || "{{ $mes }}";

            // Buscar HSC existente para precargar la observación mensual
            $.get("{{ route('informes.hora-medico.get-hsc') }}", {
                medico_id: medicoId,
                ano: ano,
                mes: mes
            }, function(data) {
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
                if (!current.includes(tag)) {
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
                alert('Por favor seleccione un médico.');
                return;
            }

            const dinamica = $('#obs_modal_texto').val().trim();
            const ano = $('[name="ano"]').val() || "{{ $ano }}";
            const mes = $('[name="mes"]').val() || "{{ $mes }}";

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
                    $('#modalNuevaObservacion').modal('hide');
                    if (window.updateConsolidado) {
                        window.updateConsolidado();
                    } else {
                        window.location.reload();
                    }
                },
                error: function (err) {
                    alert('Error al guardar la observación.');
                }
            });
        }

        function guardarObservacionConsolidado(medicoId, fullText, staticPrefix, inputElem) {
            const ano = $('[name="ano"]').val() || "{{ $ano }}";
            const mes = $('[name="mes"]').val() || "{{ $mes }}";

            let dinamica = fullText.trim();
            if (staticPrefix && dinamica.startsWith(staticPrefix)) {
                dinamica = dinamica.slice(staticPrefix.length).replace(/^\s*\|\s*/, '').replace(/^\s*,\s*/, '').trim();
            }

            if (inputElem) {
                $(inputElem).css('background-color', 'rgba(59, 130, 246, 0.1)');
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
                        $(inputElem).css('background-color', 'transparent');
                    }
                },
                error: function (err) {
                    console.error('Error al guardar la observación:', err);
                    if (inputElem) {
                        $(inputElem).css('background-color', 'rgba(239, 68, 68, 0.1)');
                    }
                }
            });
        }
    </script>
</x-app-layout>