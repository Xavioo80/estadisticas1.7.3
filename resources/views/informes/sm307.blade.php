@extends('layouts.app')

@section('title', 'Informe SM3-07: Salud Mental - Estadísticas 1.7')

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
<div class="informe-page-wrapper" id="report-wrapper" style="flex: 1 1 0%; min-height: 0; display: flex; flex-direction: column; overflow: hidden; width: 100%;">
    <!-- Header -->
    <div class="informe-header no-print" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md, 10px); padding: 0.65rem 1rem; margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-shrink: 0;">
        <div>
            <h2 style="font-size: 1.05rem; font-weight: 800; color: var(--text-primary); margin: 0; display: flex; align-items: center; gap: 0.4rem;">
                <i class="bi bi-file-medical text-primary"></i> Informe SM3-07: Salud Mental
            </h2>
            <p style="font-size: 0.72rem; color: var(--text-muted); margin: 0.15rem 0 0 0; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">
                Informe Consolidado Mensual de Salud Mental
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" id="btn-refresh-report" class="btn btn-subtle btn-sm" style="font-weight: 600; font-size: 0.78rem; padding: 0.35rem 0.75rem; border-radius: 6px; border: 1px solid var(--border-color); background: var(--bg-surface); color: var(--text-primary);">
                <i class="bi bi-arrow-clockwise mr-1"></i> Actualizar
            </button>
        </div>
    </div>

    <!-- Dynamic Content Area -->
    <div id="dynamic-content" style="flex: 1 1 0%; min-height: 0; display: flex; flex-direction: column; overflow: hidden; position: relative; width: 100%;">
        <!-- Loading Overlay -->
        <div id="table-loader" style="display: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: var(--bg-surface); opacity: 0.85; z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px);">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Cargando...</span>
            </div>
        </div>

        @include('informes.sm307_content')
    </div>
</div>
@endsection

@push('scripts')
<script>
    window.updateReport = function() {
        const form = $('#filter-form');
        if (!form.length) return;
        const url = form.attr('action');
        const data = form.serialize();

        $('#table-loader').css('display', 'flex').fadeIn(100);
        $('#dynamic-content').css('opacity', '0.5');

        $.ajax({
            url: url,
            data: data,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(html) {
                $('#dynamic-content').html(html).css('opacity', '1');
                $('#table-loader').fadeOut(200);
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Ocurrió un error al actualizar el informe.'
                });
                $('#table-loader').fadeOut(200);
                $('#dynamic-content').css('opacity', '1');
            }
        });
    };

    window.toggleFullScreen = function() {
        const elem = document.getElementById('report-wrapper') || document.getElementById('sm307-wrapper');
        const icon = document.getElementById('fullScreenIcon');

        if (!document.fullscreenElement) {
            elem.requestFullscreen().then(() => {
                if (icon) {
                    icon.classList.remove('bi-arrows-fullscreen');
                    icon.classList.add('bi-fullscreen-exit');
                }
            }).catch(err => {
                console.error(err);
            });
        } else {
            document.exitFullscreen();
        }
    };

    $(document).ready(function() {
        $(document).on('change', '.ajax-filter', function() {
            updateReport();
        });

        $(document).on('click', '#btn-refresh-report', function() {
            updateReport();
        });

        document.addEventListener('fullscreenchange', () => {
            const icon = document.getElementById('fullScreenIcon');
            if (!document.fullscreenElement) {
                if (icon) {
                    icon.classList.remove('bi-fullscreen-exit');
                    icon.classList.add('bi-arrows-fullscreen');
                }
            } else {
                if (icon) {
                    icon.classList.remove('bi-arrows-fullscreen');
                    icon.classList.add('bi-fullscreen-exit');
                }
            }
        });
    });
</script>
@endpush
