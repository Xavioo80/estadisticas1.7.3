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

    <script>
        function switchConsolidadoTab(tab) {
            const jornadaSelect = document.getElementById('jornadaSelect');
            const jornadaHidden = document.getElementById('jornadaHidden');
            const tabGenerales = document.getElementById('tabGenerales');
            const tabSociales = document.getElementById('tabSociales');

            const activeClasses = 'bg-blue-600 text-white dark:bg-blue-600 dark:text-white shadow-xs border-transparent';
            const inactiveClasses = 'bg-slate-600/90 dark:bg-slate-700/90 text-white dark:text-white border-slate-500/50 dark:border-slate-600/50 hover:bg-slate-700 dark:hover:bg-slate-600';

            if (tab === 'generales') {
                // Show the jornada select, disable hidden SS input
                jornadaSelect.classList.remove('hidden');
                jornadaHidden.disabled = true;
                // Update tab styles
                tabGenerales.className = tabGenerales.className.replace(inactiveClasses, activeClasses);
                tabSociales.className = tabSociales.className.replace(activeClasses, inactiveClasses);
                tabGenerales.classList.remove(...inactiveClasses.split(' '));
                tabGenerales.classList.add(...activeClasses.split(' '));
                tabSociales.classList.remove(...activeClasses.split(' '));
                tabSociales.classList.add(...inactiveClasses.split(' '));
            } else {
                // Hide the jornada select, enable hidden SS input
                jornadaSelect.classList.add('hidden');
                jornadaHidden.disabled = false;
                // Update tab styles
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

                // Build params: include jornada from select OR from hidden input
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

        function guardarObservacionDirecta(medicoId, observacionesText) {
            guardarObservacionConsolidado(medicoId, observacionesText, '');
        }

        function guardarObservacionConsolidado(medicoId, fullText, staticPrefix) {
            const ano = $('[name="ano"]').val() || "{{ $ano }}";
            const mes = $('[name="mes"]').val() || "{{ $mes }}";

            // Quitamos el prefijo estático del texto completo para guardar solo la parte dinámica
            let dinamica = fullText.trim();
            if (staticPrefix && dinamica.startsWith(staticPrefix)) {
                dinamica = dinamica.slice(staticPrefix.length).replace(/^\s*\|\s*/, '').trim();
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
                    // Guardado silencioso
                },
                error: function (err) {
                    console.error('Error al guardar la observación:', err);
                }
            });
        }
    </script>
</x-app-layout>