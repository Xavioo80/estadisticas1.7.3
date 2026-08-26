@extends('layouts.app')

@section('title', 'Informe SM3-07 - Estadísticas 1.7')

@section('content')
<div class="informe-page-wrapper">
    <!-- Header -->
    <div class="informe-header">
        <div>
            <h2><i class="bi bi-file-medical text-primary mr-1"></i> Informe SM3-07</h2>
            <p>Informe Consolidado SM3-07</p>
        </div>
        <div class="d-flex align-items-center gap-2">
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

        @include('informes.sm307_content')
    </div>
</div>

<script>
        // Definir funciones globales fuera de $(document).ready() para disponibilidad inmediata
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
                success: function(html) {
                    $('#dynamic-content').html(html).css('opacity', '1');
                    $('#table-loader').fadeOut(200);
                },
                error: function() {
                    alert('Error al actualizar el informe');
                    $('#table-loader').fadeOut(200);
                    $('#dynamic-content').css('opacity', '1');
                }
            });
        };

        window.toggleFullScreen = function() {
            const elem = document.getElementById('report-wrapper');
            const icon = document.getElementById('fullScreenIcon');

            if (!document.fullscreenElement) {
                elem.requestFullscreen().then(() => {
                    elem.classList.add('fullscreen-mode');
                    icon.classList.remove('fa-expand');
                    icon.classList.add('fa-compress');
                }).catch(err => {
                    alert(`Error al intentar modo pantalla plena: ${err.message}`);
                });
            } else {
                document.exitFullscreen();
            }
        };

        $(document).ready(function() {
            // Manejo de filtros AJAX
            $(document).on('change', '.ajax-filter', function() {
                updateReport();
            });

            document.addEventListener('fullscreenchange', () => {
                const icon = document.getElementById('fullScreenIcon');
                const wrapper = document.getElementById('report-wrapper');
                if (!document.fullscreenElement) {
                    icon.classList.remove('fa-compress');
                    icon.classList.add('fa-expand');
                    wrapper.style.height = 'auto';
                } else {
                    icon.classList.remove('fa-expand');
                    icon.classList.add('fa-compress');
                }
            });
        });
    </script>
    @endpush


@endsection
