@extends('layouts.app')

@section('title', 'Informe de Morbilidad - Estadísticas 1.7')

@section('content')
<div class="informe-page-wrapper">
    <!-- Header -->
    <div class="informe-header">
        <div>
            <h2><i class="bi bi-virus text-primary mr-1"></i> Informe de Morbilidad</h2>
            <p>Consolidado General de Morbilidad</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-subtle-primary btn-sm" onclick="abrirModalComparacion()" style="font-weight: 600;">
                <i class="bi bi-diagram-3-fill mr-1"></i> Comparar con otros informes
            </button>
            <button type="button" id="btn-refresh-report" class="btn btn-subtle btn-sm" style="font-weight: 600;">
                <i class="bi bi-arrow-clockwise mr-1"></i> Actualizar
            </button>
        </div>
    </div>

    <!-- Dynamic Content Area -->
    <div id="dynamic-content" style="flex: 1 1 0%; display: flex; flex-direction: column; overflow: hidden; position: relative;">
        <!-- Loading Overlay -->
        <div id="table-loader" style="display: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: var(--bg-surface); opacity: 0.8; z-index: 1000; align-items: center; justify-content: center;">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Cargando...</span>
            </div>
        </div>

        @include('informes.morbilidad_content')
    </div>
</div>

@include('informes.partials.modal_comparacion_cruzada')

<script>
        document.addEventListener('DOMContentLoaded', function() {
            
            function refreshReport() {
                const $form = $('#filter-form');
                if (!$form.length) return;
                
                const url = $form.attr('action');
                const data = $form.serialize();

                // Guardar posición del scroll
                const $scroller = $('.table-responsive');
                const scrollTop = $scroller.scrollTop();
                const scrollLeft = $scroller.scrollLeft();

                $('#table-loader').css('display', 'flex').fadeIn(200);

                $.ajax({
                    url: url,
                    type: 'GET',
                    data: data,
                    success: function(response) {
                        $('#dynamic-content').html(response);
                        initTableFeatures();

                        // Restaurar posición del scroll
                        const $newScroller = $('.table-responsive');
                        $newScroller.scrollTop(scrollTop);
                        $newScroller.scrollLeft(scrollLeft);

                        $('#table-loader').fadeOut(200);
                    },
                    error: function() {
                        alert('Error al actualizar los datos');
                        $('#table-loader').fadeOut(200);
                    }
                });
            }

            $(document).on('change', '.ajax-filter', function() {
                refreshReport();
            });

            $(document).on('submit', '#filter-form', function(e) {
                e.preventDefault();
                refreshReport();
            });

            function initTableFeatures() {
                var table = document.getElementById('morbilidadTable');
                if (!table) return;

                // ─── Asignar data-col a cada celda física del tbody/tfoot ───
                // Las celdas de datos van de col 0 (diagnóstico) a col 33 (suma)
                $(table).find('tbody tr, tfoot tr').each(function() {
                    $(this).find('td').each(function(i) {
                        $(this).attr('data-col', i);
                    });
                });

                // ─── Asignar data-col a los th de la fila N/S (fila 3 del thead) ───
                // Esta fila es 1:1 con las columnas de datos (sin colspan)
                $(table).find('thead tr:nth-child(3) th').each(function(i) {
                    $(this).attr('data-col', i + 1); // +1 porque col 0 es DIAGNOSTICO
                });

                // ─── Asignar data-col a los th de HOMBRE/MUJER (fila 2, colspan=2) ───
                var col2 = 1;
                $(table).find('thead tr:nth-child(2) th').each(function() {
                    var span = parseInt($(this).attr('colspan') || 1);
                    $(this).attr('data-col-start', col2).attr('data-col-end', col2 + span - 1);
                    col2 += span;
                });

                // ─── Asignar data-col a los th de rangos de edad (fila 1, colspan=4) ───
                var col1 = 1;
                $(table).find('thead tr:nth-child(1) th[colspan]').each(function() {
                    var span = parseInt($(this).attr('colspan') || 1);
                    $(this).attr('data-col-start', col1).attr('data-col-end', col1 + span - 1);
                    col1 += span;
                });

                // ─── Eventos hover ───
                $(table).off('mouseenter mouseleave', 'td');

                $(table).on('mouseenter', 'td', function() {
                    var $td   = $(this);
                    var col   = parseInt($td.attr('data-col'));
                    var $row  = $td.closest('tr');

                    // 1) Resaltar toda la fila (incluye td diagnóstico sticky)
                    $row.addClass('hover-row');

                    // 2) Marcar la celda exacta como celda activa
                    $td.addClass('hover-cell');

                    if (isNaN(col) || col === 0) return; // columna diagnóstico: solo fila

                    // 3) Resaltar columna entera en tbody + tfoot
                    $(table).find('tbody td[data-col="' + col + '"], tfoot td[data-col="' + col + '"]')
                            .addClass('hover-col');

                    // 4) Resaltar th de fila 3 (N/S) que coincide 1:1
                    $(table).find('thead tr:nth-child(3) th[data-col="' + col + '"]')
                            .addClass('hover-col');

                    // 5) Resaltar th de fila 2 (HOMBRE/MUJER) si el col cae en su rango
                    $(table).find('thead tr:nth-child(2) th').each(function() {
                        var s = parseInt($(this).attr('data-col-start'));
                        var e = parseInt($(this).attr('data-col-end'));
                        if (col >= s && col <= e) $(this).addClass('hover-col');
                    });

                    // 6) Resaltar th de fila 1 (rangos de edad) si el col cae en su rango
                    $(table).find('thead tr:nth-child(1) th[data-col-start]').each(function() {
                        var s = parseInt($(this).attr('data-col-start'));
                        var e = parseInt($(this).attr('data-col-end'));
                        if (col >= s && col <= e) $(this).addClass('hover-col');
                    });

                }).on('mouseleave', 'td', function() {
                    $(table).find('.hover-row').removeClass('hover-row');
                    $(table).find('.hover-cell').removeClass('hover-cell');
                    $(table).find('.hover-col').removeClass('hover-col');
                });

                $('.table-responsive').css('height', 'calc(100vh - 160px)');
            }

            // ── Función global de pantalla completa ──
            window.toggleFullScreen = function() {
                var wrapper = document.getElementById('report-wrapper');
                if (!document.fullscreenElement) {
                    wrapper.requestFullscreen().catch(function(err) {
                        console.error('Error al entrar en pantalla completa:', err);
                    });
                } else {
                    document.exitFullscreen();
                }
            };

            document.addEventListener('fullscreenchange', function() {
                var icon = document.getElementById('fullScreenIcon');
                if (document.fullscreenElement) {
                    // Entrando en pantalla completa
                    if (icon) { icon.classList.remove('fa-expand'); icon.classList.add('fa-compress'); }
                    $('.table-responsive').css('height', 'calc(100vh - 80px)');
                } else {
                    // Saliendo de pantalla completa
                    if (icon) { icon.classList.remove('fa-compress'); icon.classList.add('fa-expand'); }
                    $('.table-responsive').css('height', 'calc(100vh - 160px)');
                }
            });

            initTableFeatures();
        });
    </script>


@endsection
