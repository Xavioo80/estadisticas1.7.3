@extends('layouts.app')

@section('title', 'Informe de Clasificación TB (TB9) - Estadísticas 1.7')

@section('content')
<div class="informe-page-wrapper" id="report-wrapper">
    <!-- Header -->
    <div class="informe-header">
        <div>
            <h2><i class="bi bi-clipboard2-pulse text-primary mr-1"></i> Informe de Clasificación TB (TB9)</h2>
            <p>Seguimiento de Sintomáticos Respiratorios</p>
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

        @include('informes.tb9_content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>

<script>
    document.addEventListener('DOMContentLoaded', function() {
            
            // --- INYECCIÓN MANUAL DEL SIDEBAR DERECHO ---
            var sidebarHtml = `
            <div class="p-3">
                <h5 class="mb-2 text-dark font-weight-bold"><i class="fas fa-sliders-h mr-2"></i>Vista</h5>
                <hr class="mb-3">
                <div class="mb-3">
                    <label class="mb-1 text-sm font-weight-600">Tamaño del Texto</label>
                    <select id="configFontSize" class="form-control form-control-sm border-0 bg-light shadow-sm">
                        <option value="13.5px" selected>Normal (13.5px)</option>
                        <option value="14px">Estándar (14px)</option>
                        <option value="16px">Grande (16px)</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="mb-1 text-sm font-weight-600">Altura Filas</label>
                    <input type="range" class="custom-range" id="configRowHeight" min="0" max="10" step="1" value="2">
                </div>
                <button class="btn btn-sm btn-outline-danger btn-block" onclick="resetConfigTabla()">
                    <i class="fas fa-undo mr-1"></i> Restaurar
                </button>
            </div>`;
            
            var $target = $('.control-sidebar-content');
            if($target.length === 0) $target = $('.control-sidebar');
            
            if($target.length > 0) {
                $target.html(sidebarHtml);
                $target.css({ 'height': '100vh', 'min-height': '100%', 'overflow-y': 'auto', 'padding-bottom': '80px' });
                $('.control-sidebar').css({ 'bottom': '0', 'top': '57px', 'height': 'calc(100vh - 57px)', 'position': 'fixed' });
                setTimeout(initTableConfig, 100);
            }

            // --- LÓGICA AJAX PARA FILTROS ---
            function refreshReport() {
                const $form = $('#filter-form');
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
                        initSelect2(); // Re-inicializar Select2

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

            // Delegar cambio en filtros ajax
            $(document).on('change', '.ajax-filter', function() {
                updateExportUrl();
                refreshReport();
            });

            $(document).on('click', '#btn-refresh-report', function() {
                refreshReport();
            });

            function updateExportUrl() {
                const $form = $('#filter-form');
                const params = $form.serialize();
                const baseUrl = "{{ route('informes.tb9.export') }}";
                $('#btn-export-tb9').attr('href', `${baseUrl}?${params}`);
            }


            // Delegar submit del form
            $(document).on('submit', '#filter-form', function(e) {
                e.preventDefault();
                refreshReport();
            });

            function initTableFeatures() {
                // RESALTADOR DE POSICIÓN (CROSSHAIR)
                var table = document.getElementById('tb9Table');
                if(table) {
                    $(table).off('mouseenter mouseleave click', 'td'); // Limpiar previos
                    
                    $(table).on('mouseenter', 'td', function() {
                        var $this = $(this);
                        var $parent = $this.parent();
                        if($this.hasClass('sticky-col-first') || $parent.hasClass('total-row')) return;

                        var idx = $this.index();
                        $this.addClass('hover-cell');
                        $parent.addClass('hover-row');
                        
                        // Resaltar la columna (excluyendo celdas especiales)
                        $(table).find('tbody tr').not('.total-row').each(function() {
                            $(this).find('td').eq(idx).addClass('hover-col');
                        });
                    }).on('mouseleave', 'td', function() {
                        var $this = $(this);
                        var idx = $this.index();
                        $this.removeClass('hover-cell');
                        $this.parent().removeClass('hover-row');
                        $(table).find('tbody tr').not('.total-row').each(function() {
                            $(this).find('td').eq(idx).removeClass('hover-col');
                        });
                    });

                    // FUNCIÓN PARA SELECCIONAR / MARCAR DATOS (CLIC ÚNICO)
                    $(table).on('click', 'td', function() {
                        var $this = $(this);
                        if($this.hasClass('sticky-col-first') || $this.closest('tr').hasClass('total-row')) return;
                        
                        var isSelected = $this.hasClass('selected-cell');
                        
                        // Limpiar cualquier otra selección previa (modo único)
                        $(table).find('.selected-cell').removeClass('selected-cell');
                        
                        if (!isSelected) {
                            $this.addClass('selected-cell');
                        }
                    });
                }

                // Ajustar altura de tabla dinámicamente
                $('.table-responsive').css('height', 'calc(100vh - 170px)');

                // Aplicar configuraciones de vista (fuente, alto filas)
                if (typeof updateTableStyles === 'function') updateTableStyles();
            }

            function initSelect2() {
                if ($.fn.select2) {
                    $('.select2-multiple').select2({
                        closeOnSelect: false,
                        language: {
                            noResults: function() { return "No hay resultados"; }
                        }
                    });
                }
            }

            initSelect2();

            // --- LÓGICA PANTALLA COMPLETA ---
            var wrapper = document.getElementById('report-wrapper');
            
            $(document).on('click', '#toggle-fullscreen', function() {
                if (!document.fullscreenElement) {
                    enterFS();
                } else {
                    exitFS();
                }
            });

            function enterFS() {
                wrapper.requestFullscreen().catch(err => console.error(err));
                localStorage.setItem('report_tb9_fullscreen', 'true');
            }

            function exitFS() {
                if (document.fullscreenElement) document.exitFullscreen();
                localStorage.removeItem('report_tb9_fullscreen');
            }

            document.addEventListener('fullscreenchange', function() {
                if (!document.fullscreenElement) {
                    $(wrapper).removeClass('p-3 fullscreen-mode');
                    $('#fullScreenIcon').removeClass('fa-compress').addClass('fa-expand');
                    $('.table-responsive').css('height', 'calc(100vh - 170px)');
                    $('.report-title').show();
                    localStorage.removeItem('report_tb9_fullscreen');
                } else {
                    $(wrapper).addClass('p-3 fullscreen-mode');
                    $('#fullScreenIcon').removeClass('fa-expand').addClass('fa-compress');
                    $('.report-title').hide();
                }
            });

            // Re-entrada automática si venía de recarga
            if(localStorage.getItem('report_tb9_fullscreen') === 'true') {
                setTimeout(enterFS, 500);
            }

            initTableFeatures();
        });

        var updateTableStyles; // Global reference

        function initTableConfig() {
            var confFontSize = document.getElementById('configFontSize');
            if(!confFontSize) return;
            var confRowHeight = document.getElementById('configRowHeight');
            var styleTag = document.getElementById('dynamic-table-styles-tb9');
            if(!styleTag) {
                styleTag = document.createElement('style');
                styleTag.id = 'dynamic-table-styles-tb9';
                document.head.appendChild(styleTag);
            }

            updateTableStyles = function() {
                var fs = confFontSize.value;
                var pv = confRowHeight.value + 'px';
                var css = `
                    #tb9Table tbody td { font-size: ${fs} !important; border-top-width: 0; padding-top: ${pv} !important; padding-bottom: ${pv} !important; }
                    #tb9Table tbody tr.total-row td { font-size: ${fs} !important; }
                    #tb9Table thead th.sticky-col-first div { font-size: 0.7rem !important; }
                `;
                styleTag.innerHTML = css;
                localStorage.setItem('tablaConfigTb9', JSON.stringify({ fontSize: fs, rowHeight: confRowHeight.value }));
            };

            var saved = localStorage.getItem('tablaConfigTb9');
            if(saved) {
                try {
                    var c = JSON.parse(saved);
                    if(c.fontSize) confFontSize.value = c.fontSize;
                    if(c.rowHeight) confRowHeight.value = c.rowHeight;
                } catch(e) {}
            }

            confFontSize.addEventListener('change', updateTableStyles);
            confRowHeight.addEventListener('input', updateTableStyles);
            updateTableStyles();

            window.resetConfigTabla = function() {
                confFontSize.value = '13.5px';
                confRowHeight.value = '2';
                updateTableStyles();
            };
        }
    </script>


@endsection
