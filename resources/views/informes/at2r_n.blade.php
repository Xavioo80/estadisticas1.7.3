@extends('layouts.app')

@section('title', 'Informe Mensual AT2-r N - Estadísticas 1.7')

@section('content')
<div style="flex: 1 1 0%; min-height: 0; display: flex; flex-direction: column; overflow: hidden;">
    <!-- Top Header con Filtros integrados -->
    <div class="informe-header no-print" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md, 10px); gap: 1rem; flex-wrap: nowrap; margin-bottom: 12px;">
        <!-- Título -->
        <div style="flex: 1 1 0%; min-width: 0;">
            <h2><i class="bi bi-card-checklist text-primary mr-1"></i> Informe Mensual AT2-r N</h2>
            <p>INFORME MENSUAL DE ACTIVIDADES E INTERVENCIONES (NUEVO)</p>
        </div>

        <!-- Filtros (alineados a la derecha) -->
        <form id="filter-form" action="{{ route('informes.at2r-n') }}" method="GET" style="display: flex; align-items: center; gap: 0.5rem; flex-shrink: 0; margin: 0; margin-left: auto;">
            <div style="display: flex; align-items: center; gap: 0.4rem; flex-shrink: 0;">
                <div style="width: 78px;">
                    <select name="ano" class="filter-select w-full ajax-filter">
                        @foreach($anos as $a)
                            <option value="{{ $a }}" {{ $a == $ano ? 'selected' : '' }}>{{ $a }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="width: 112px;">
                    <select name="mes" class="filter-select w-full ajax-filter">
                        @foreach($meses as $m)
                            <option value="{{ $m }}" {{ $m == $mes ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div style="height: 20px; width: 1px; background: var(--border-color); flex-shrink: 0;"></div>

            <div style="display: flex; align-items: center; gap: 0.4rem; flex: 1 1 0%; min-width: 0; overflow: hidden;">
                <div style="width: 130px; flex-shrink: 0;">
                    <select name="prof" class="filter-select w-full ajax-filter">
                        <option value="TODAS">PROFESIÓN</option>
                        @foreach($profesiones as $p)
                            <option value="{{ $p }}" {{ $p == $profFilter ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="width: 150px; flex-shrink: 0;">
                    <select name="medico" class="filter-select w-full ajax-filter">
                        <option value="TODOS">MÉDICO</option>
                        @foreach($nombresMedicos as $nm)
                            <option value="{{ $nm }}" {{ $nm == $medicoFilter ? 'selected' : '' }}>{{ $nm }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="width: 88px; flex-shrink: 0;">
                    <select name="sexo" class="filter-select w-full ajax-filter">
                        <option value="AMBOS">SEXO</option>
                        <option value="M" {{ $sexoFilter == 'M' ? 'selected' : '' }}>MUJER</option>
                        <option value="H" {{ $sexoFilter == 'H' ? 'selected' : '' }}>HOMBRE</option>
                    </select>
                </div>
                <div style="width: 105px; flex-shrink: 0;">
                    <select name="jornada" class="filter-select w-full ajax-filter">
                        <option value="TODAS">JORNADA</option>
                        @foreach($jornadas as $j)
                            <option value="{{ $j }}" {{ $j == $jornada ? 'selected' : '' }}>{{ $j }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Botones de acción -->
            <div style="display: flex; align-items: center; gap: 0.375rem; flex-shrink: 0; margin-left: auto;">
                <button type="button" id="btn-refresh-report" class="btn-action-fullscreen" title="Actualizar datos">
                    <i class="bi bi-arrow-clockwise"></i>
                </button>
                <button type="button" id="toggle-fullscreen" class="btn-action-fullscreen" title="Pantalla Completa">
                    <i class="bi bi-arrows-fullscreen" id="fullScreenIcon"></i>
                </button>
                <button type="button" onclick="window.print()" class="btn-action-print" title="Imprimir">
                    <i class="bi bi-printer"></i>
                </button>
                <a href="{{ route('informes.at2r-n.audit', request()->all()) }}" target="_blank" class="btn-action-audit" title="Auditar Discrepancias">
                    <i class="bi bi-search-heart"></i>
                </a>
                <a href="{{ route('informes.at2r-n.export', request()->all()) }}" class="btn-action-excel" title="Exportar Excel">
                    <i class="bi bi-file-earmark-excel"></i>
                </a>
            </div>
        </form>
    </div>

    <!-- Centered Card Container -->
    <div class="informe-container-centered" style="flex: 1 1 0%; min-height: 0;">
        <div class="informe-card-centered">
            <!-- Dynamic Content Area -->
            <div id="dynamic-content" style="flex: 1 1 0%; min-height: 0; display: flex; flex-direction: column; overflow: hidden; position: relative;">
                <!-- Loading Overlay -->
                <div id="table-loader" style="display: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: var(--bg-surface); opacity: 0.8; z-index: 1000; align-items: center; justify-content: center;">
                    <div class="spinner-border text-primary" role="status">
                        <span class="sr-only">Cargando...</span>
                    </div>
                </div>

                @include('informes.at2r_n_content')
            </div>
        </div>
    </div>
</div>

<script>
        document.addEventListener('DOMContentLoaded', function () {

            function refreshReport(silent = false) {
                const $form = $('#filter-form');
                if (!$form.length) return;

                const url = $form.attr('action');
                const data = $form.serialize();

                // Guardar posición del scroll
                const $scroller = $('.table-responsive');
                const scrollTop = $scroller.scrollTop();
                const scrollLeft = $scroller.scrollLeft();

                if (!silent) {
                    $('#table-loader').css('display', 'flex').fadeIn(200);
                }

                $.ajax({
                    url: url + (url.indexOf('?') >= 0 ? '&' : '?') + '_t=' + new Date().getTime(),
                    type: 'GET',
                    cache: false,
                    data: data,
                    headers: {
                        'Cache-Control': 'no-cache, no-store, must-revalidate',
                        'Pragma': 'no-cache'
                    },
                    success: function (response) {
                        $('#dynamic-content').html(response);
                        initTableFeatures();

                        // Restaurar posición del scroll
                        const $newScroller = $('.table-responsive');
                        $newScroller.scrollTop(scrollTop);
                        $newScroller.scrollLeft(scrollLeft);

                        if (!silent) {
                            $('#table-loader').fadeOut(200);
                        }
                    },
                    error: function () {
                        if (!silent) alert('Error al actualizar los datos');
                        $('#table-loader').fadeOut(200);
                    }
                });
            }

            $(document).on('change', '.ajax-filter', function () {
                refreshReport();
            });

            $(document).on('click', '#btn-refresh-report', function () {
                refreshReport();
            });

            $(document).on('submit', '#filter-form', function (e) {
                e.preventDefault();
                refreshReport();
            });

            function initTableFeatures() {
                var table = document.getElementById('at2rTable');
                if (table) {
                    $(table).off('mouseenter mouseleave', 'td');
                    $(table).on('mouseenter', 'td', function () {
                        var $this = $(this);
                        var $parent = $this.parent();
                        if ($this.hasClass('sticky-col-first')) return;

                        var idx = $this.index();
                        $this.addClass('hover-cell');
                        $parent.addClass('hover-row');

                        $(table).find('tbody tr').each(function () {
                            $(this).find('td').eq(idx).addClass('hover-col');
                        });
                    }).on('mouseleave', 'td', function () {
                        var $this = $(this);
                        var idx = $this.index();
                        $this.removeClass('hover-cell');
                        $this.parent().removeClass('hover-row');
                        $(table).find('tbody tr').each(function () {
                            $(this).find('td').eq(idx).removeClass('hover-col');
                        });
                    });
                }

            }

            var wrapper = document.querySelector('.informe-card-centered') || document.getElementById('dynamic-content');

            $(document).on('click', '#toggle-fullscreen', function () {
                if (!document.fullscreenElement) {
                    if (wrapper.requestFullscreen) {
                        wrapper.requestFullscreen().catch(err => console.error(err));
                    }
                    $('#fullScreenIcon').removeClass('bi-arrows-fullscreen').addClass('bi-fullscreen-exit');
                } else {
                    document.exitFullscreen();
                }
            });

            document.addEventListener('fullscreenchange', function () {
                const modal = document.getElementById('cellDetailModal');
                const morbModal = document.getElementById('morbilidadAuditModal');
                if (document.fullscreenElement) {
                    // Mover modales dentro del elemento fullscreen para que sean visibles
                    if (modal) document.fullscreenElement.appendChild(modal);
                    if (morbModal) document.fullscreenElement.appendChild(morbModal);
                    $('#fullScreenIcon').removeClass('bi-arrows-fullscreen').addClass('bi-fullscreen-exit');
                } else {
                    // Devolver modales al body al salir de fullscreen
                    if (modal) document.body.appendChild(modal);
                    if (morbModal) document.body.appendChild(morbModal);
                    $('#fullScreenIcon').removeClass('bi-fullscreen-exit').addClass('bi-arrows-fullscreen');
                }
            });

            $(document).on('change', '.manual-input', function() {
                var $input = $(this);
                var $row = $input.closest('tr');
                var manualKey = $input.data('key') || 'rehidratados';
                var col1 = $row.find('input[data-col="1"]').val() || 0;
                var col2 = $row.find('input[data-col="2"]').val() || 0;
                var col3 = $row.find('input[data-col="3"]').val() || 0;
                var col4 = $row.find('input[data-col="4"]').val() || 0;
                
                var total = parseInt(col1) + parseInt(col2) + parseInt(col3) + parseInt(col4);
                $row.find('.manual-total').text(total);

                $input.addClass('bg-yellow-100');

                var $form = $('#filter-form');
                var data = {
                    _token: '{{ csrf_token() }}',
                    ano: $form.find('select[name="ano"]').val(),
                    mes: $form.find('select[name="mes"]').val(),
                    jornada: $form.find('select[name="jornada"]').val(),
                    prof: $form.find('select[name="prof"]').val(),
                    medico: $form.find('select[name="medico"]').val(),
                    sexo: $form.find('select[name="sexo"]').val(),
                    manual_key: manualKey,
                    col1: col1,
                    col2: col2,
                    col3: col3,
                    col4: col4
                };

                $.ajax({
                    url: '{{ route("informes.at2r-n.save-manual") }}',
                    type: 'POST',
                    data: data,
                    success: function(response) {
                        $input.removeClass('bg-yellow-100').addClass('bg-green-100');
                        setTimeout(() => $input.removeClass('bg-green-100'), 1000);
                        // Refrescar silenciosamente para actualizar restas (Adecuado)
                        refreshReport(true);
                    },
                    error: function() {
                        $input.removeClass('bg-yellow-100').addClass('bg-red-100');
                        alert('Error al guardar el dato manual');
                    }
                });
            });

            $(document).on('keydown', '.manual-input', function(e) {
                if (e.which === 13) {
                    $(this).blur();
                }
            });

            initTableFeatures();
        });
    </script>

@endsection
