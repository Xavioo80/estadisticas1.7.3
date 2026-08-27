@extends('layouts.app')

@section('title', 'Informe AT2-r - Estadísticas 1.7')

@section('content')
<div class="informe-page-wrapper">
    <!-- Header -->
    <div class="informe-header">
        <div>
            <h2><i class="bi bi-file-earmark-bar-graph text-primary mr-1"></i> Informe AT2-r</h2>
            <p>Reporte Estadístico AT2-r</p>
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

        @include('informes.at2r_content')
    </div>
</div>

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
                var table = document.getElementById('at2rTable');
                if(table) {
                    $(table).off('mouseenter mouseleave', 'td');
                    $(table).on('mouseenter', 'td', function() {
                        var $this = $(this);
                        var $parent = $this.parent();
                        if($this.hasClass('sticky-col-first')) return;

                        var idx = $this.index();
                        $this.addClass('hover-cell');
                        $parent.addClass('hover-row');
                        
                        $(table).find('tbody tr').each(function() {
                            $(this).find('td').eq(idx).addClass('hover-col');
                        });
                    }).on('mouseleave', 'td', function() {
                        var $this = $(this);
                        var idx = $this.index();
                        $this.removeClass('hover-cell');
                        $this.parent().removeClass('hover-row');
                        $(table).find('tbody tr').each(function() {
                            $(this).find('td').eq(idx).removeClass('hover-col');
                        });
                    });
                }
                
                $('.table-responsive').css('height', 'calc(100vh - 155px)');
            }

            var wrapper = document.getElementById('report-wrapper');
            
            $(document).on('click', '#toggle-fullscreen', function() {
                if (!document.fullscreenElement) {
                    wrapper.requestFullscreen().catch(err => console.error(err));
                    $(wrapper).addClass('p-3 fullscreen-mode');
                    $('#toggle-fullscreen').html('<i class="fas fa-compress"></i>');
                    $('.table-responsive').css('height', 'calc(100vh - 80px)');
                } else {
                    document.exitFullscreen();
                }
            });

            document.addEventListener('fullscreenchange', function() {
                if (!document.fullscreenElement) {
                    $(wrapper).removeClass('p-3 fullscreen-mode');
                    $('#toggle-fullscreen').html('<i class="fas fa-expand"></i>');
                    $('.table-responsive').css('height', 'calc(100vh - 155px)');
                }
            });

            initTableFeatures();
        });
    </script>


@endsection
