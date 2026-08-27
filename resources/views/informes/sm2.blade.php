@extends('layouts.app')

@section('title', 'Informe SM2 - Estadísticas 1.7')

@section('content')
<div class="informe-page-wrapper">
    <!-- Header -->
    <div class="informe-header">
        <div>
            <h2><i class="bi bi-hospital text-primary mr-1"></i> Informe SM2</h2>
            <p>Informe Complementario de Salud Materna SM2</p>
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

        @include('informes.sm2_content')
    </div>
</div>
@endsection

@push('scripts')
<script>
        $(document).on('change', '.ajax-filter', function() {
            const container = $('#sm2-container');
            const data = {
                ano: $('select[name="ano"]').val(),
                mes: $('select[name="mes"]').val(),
                jornada: $('select[name="jornada"]').val()
            };

            container.addClass('opacity-50 pointer-events-none');
            
            $.get("{{ route('informes.sm2') }}", data, function(html) {
                container.html(html);
                container.removeClass('opacity-50 pointer-events-none');
            });
        });

        function toggleFullScreen() {
            const elem = document.getElementById('sm2-full-wrapper');
            const icon = $('#fullScreenIcon');
            
            if (!document.fullscreenElement) {
                if (elem.requestFullscreen) elem.requestFullscreen();
                else if (elem.webkitRequestFullscreen) elem.webkitRequestFullscreen();
                else if (elem.msRequestFullscreen) elem.msRequestFullscreen();
                
                icon.removeClass('fa-expand').addClass('fa-compress');
            } else {
                if (document.exitFullscreen) document.exitFullscreen();
                icon.removeClass('fa-compress').addClass('fa-expand');
            }
        }
    </script>
@endpush
