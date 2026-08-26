@extends('layouts.app')

@section('title', 'Atenciones por Médico - Reporte de Productividad')

@push('styles')
<style>
    .app-footer {
        display: none !important;
    }
    .app-content {
        padding: 0.6rem 0.85rem !important;
        height: calc(100vh - var(--navbar-height)) !important;
        max-height: calc(100vh - var(--navbar-height)) !important;
        display: flex !important;
        flex-direction: column !important;
        overflow: hidden !important;
        width: 100% !important;
        min-width: 0 !important;
        max-width: 100% !important;
    }
</style>
@endpush

@section('content')
<div class="atenciones-page-wrapper" id="report-wrapper" style="flex: 1 1 0%; min-height: 0; display: flex; flex-direction: column; overflow: hidden; width: 100%;">
    <!-- Top Header Card -->
    <div class="informe-header no-print" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md, 10px); padding: 0.65rem 1rem; margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-shrink: 0;">
        <div>
            <h2 style="font-size: 1.05rem; font-weight: 800; color: var(--text-primary); margin: 0; display: flex; align-items: center; gap: 0.4rem;">
                <i class="bi bi-person-check text-primary"></i> Atenciones por Médico
            </h2>
            <p style="font-size: 0.72rem; color: var(--text-muted); margin: 0.15rem 0 0 0; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">
                Reporte de Productividad Mensual
            </p>
        </div>
        <div style="display: flex; align-items: center; gap: 0.5rem;">
            <button type="button" id="btn-refresh-report" class="btn btn-subtle btn-sm" style="font-weight: 600; font-size: 0.78rem; padding: 0.35rem 0.75rem; border-radius: 6px; border: 1px solid var(--border-color); background: var(--bg-surface); color: var(--text-primary);">
                <i class="bi bi-arrow-clockwise mr-1"></i> Actualizar
            </button>
        </div>
    </div>

    <!-- Full Width Table Container -->
    <div style="flex: 1 1 0%; min-height: 0; display: flex; flex-direction: column; overflow: hidden; width: 100%; background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md, 10px);">
        <!-- Dynamic Content Area -->
        <div id="dynamic-content" style="flex: 1 1 0%; min-height: 0; display: flex; flex-direction: column; overflow: hidden; position: relative; width: 100%;">
            <!-- Loading Overlay -->
            <div id="table-loader" style="display: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: var(--bg-surface); opacity: 0.85; z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
                <div class="spinner-border text-primary" role="status">
                    <span class="sr-only">Cargando...</span>
                </div>
            </div>

            @include('informes.atenciones_content')
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function () {
        const wrapper = document.getElementById('report-wrapper');

        function refreshReport() {
            const $form = $('#filter-form');
            if (!$form.length) return;
            const url = $form.attr('action');
            const data = $form.serialize();

            // Guardar posición del scroll
            const $scroller = $('.table-responsive');
            const scrollTop = $scroller.scrollTop();
            const scrollLeft = $scroller.scrollLeft();

            $('#table-loader').css('display', 'flex').fadeIn(150);

            $.ajax({
                url: url,
                type: 'GET',
                data: data,
                success: function (response) {
                    $('#dynamic-content').html(response);
                    initTableFeatures();

                    // Restaurar posición del scroll
                    const $newScroller = $('.table-responsive');
                    $newScroller.scrollTop(scrollTop);
                    $newScroller.scrollLeft(scrollLeft);

                    $('#table-loader').fadeOut(150);
                },
                error: function () {
                    alert('Error al actualizar los datos');
                    $('#table-loader').fadeOut(150);
                }
            });
        }

        function initTableFeatures() {
            var table = document.getElementById('atencionesTable');
            if (table) {
                $(table).off('mouseenter mouseleave', 'tbody tr.doctor-row td');
                $(table).on('mouseenter', 'tbody tr.doctor-row td', function () {
                    var $this = $(this);
                    var $parent = $this.parent();
                    if ($this.hasClass('sticky-col-first')) return;

                    var idx = $this.index();
                    $this.addClass('hover-cell');
                    $parent.addClass('hover-row');

                    $(table).find('tbody tr.doctor-row').each(function () {
                        $(this).find('td').eq(idx).addClass('hover-col');
                    });
                }).on('mouseleave', 'tbody tr.doctor-row td', function () {
                    var $this = $(this);
                    var idx = $this.index();
                    $this.removeClass('hover-cell');
                    $this.parent().removeClass('hover-row');
                    $(table).find('tbody tr.doctor-row').each(function () {
                        $(this).find('td').eq(idx).removeClass('hover-col');
                    });
                });
            }
        }

        // TOOLBAR & FILTERS
        $(document).on('click', '#btn-refresh-report', function() {
            refreshReport();
        });

        $(document).on('change', '.ajax-filter', function () {
            refreshReport();
        });

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
            if (document.fullscreenElement) {
                $('#fullScreenIcon').removeClass('bi-arrows-fullscreen').addClass('bi-fullscreen-exit');
            } else {
                $('#fullScreenIcon').removeClass('bi-fullscreen-exit').addClass('bi-arrows-fullscreen');
            }
        });

        initTableFeatures();
    });
</script>
@endsection
