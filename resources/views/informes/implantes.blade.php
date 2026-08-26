@extends('layouts.app')

@section('title', 'Informe de Implantes - Estadísticas 1.7')

@section('content')
<div class="informe-page-wrapper">
    <!-- Header -->
    <div class="informe-header">
        <div>
            <h2><i class="bi bi-bandaid text-primary mr-1"></i> Informe de Implantes</h2>
            <p>Registro y Procedimientos de Implantes</p>
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

        @include('informes.implantes_content')
    </div>
</div>

<script>
        document.addEventListener('DOMContentLoaded', function() {
            
            var sidebarHtml = `
            <div class="p-3">
                <h5 class="mb-2 text-dark font-weight-bold"><i class="fas fa-sliders-h mr-2"></i>Vista</h5>
                <hr class="mb-3">
                <div class="mb-3">
                    <label class="mb-1 text-sm font-weight-600">Tamaño del Texto</label>
                    <select id="configFontSize" class="form-control form-control-sm border-0 bg-light shadow-sm">
                        <option value="14px">Normal (14px)</option>
                        <option value="16px">Grande (16px)</option>
                        <option value="18px" selected>Extra Grande (18px)</option>
                        <option value="20px">Enorme (20px)</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="mb-1 text-sm font-weight-600">Altura Filas</label>
                    <input type="range" class="custom-range" id="configRowHeight" min="0" max="15" step="1" value="6">
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

            function refreshReport() {
                const $form = $('#filter-form');
                const url = $form.attr('action');
                const data = $form.serialize();

                $('#table-loader').css('display', 'flex').fadeIn(200);

                $.ajax({
                    url: url,
                    type: 'GET',
                    data: data,
                    success: function(response) {
                        $('#dynamic-content').html(response);
                        initTableFeatures();
                        $('#table-loader').fadeOut(200);
                    },
                    error: function() {
                        alert('Error al actualizar los datos');
                        $('#table-loader').fadeOut(200);
                    }
                });
            }

            $(document).on('change', '.ajax-filter', function() {
                updateExportUrl();
                refreshReport();
            });

            function updateExportUrl() {
                const $form = $('#filter-form');
                const params = $form.serialize();
                const baseUrl = "{{ route('informes.implantes.export') }}";
                $('#btn-export-implantes').attr('href', `${baseUrl}?${params}`);
            }


            $(document).on('submit', '#filter-form', function(e) {
                e.preventDefault();
                refreshReport();
            });

            function initTableFeatures() {
                var table = document.getElementById('implantesTable');
                if(table) {
                    $('[data-toggle="tooltip"]').tooltip();
                    $(table).off('mouseenter mouseleave', 'td');
                    $(table).on('mouseenter', 'td', function() {
                        var $this = $(this);
                        var $parent = $this.parent();
                        if($this.hasClass('sticky-col-first') || $parent.hasClass('total-row')) return;

                        var idx = $this.index();
                        $this.addClass('hover-cell');
                        $parent.addClass('hover-row');
                        
                        if($this.hasClass('border-thick-vertical')) return;

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
                }

                if (document.fullscreenElement) {
                    $('.table-responsive').css('height', 'calc(100vh - 80px)');
                    $('.report-title').hide();
                } else {
                    $('.table-responsive').css('height', 'calc(100vh - 155px)');
                }

                if (typeof updateTableStyles === 'function') updateTableStyles();
            }

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
                $(wrapper).addClass('p-3 fullscreen-mode');
                $('#toggle-fullscreen').html('<i class="fas fa-compress"></i>');
                $('.table-responsive').css('height', 'calc(100vh - 80px)');
                localStorage.setItem('report_implantes_fullscreen', 'true');
            }

            function exitFS() {
                if (document.fullscreenElement) document.exitFullscreen();
                $(wrapper).removeClass('p-3 fullscreen-mode');
                $('#toggle-fullscreen').html('<i class="fas fa-expand"></i>');
                $('.table-responsive').css('height', 'calc(100vh - 155px)');
                localStorage.removeItem('report_implantes_fullscreen');
            }

            document.addEventListener('fullscreenchange', function() {
                if (!document.fullscreenElement) {
                    $(wrapper).removeClass('p-3 fullscreen-mode');
                    $('#toggle-fullscreen').html('<i class="fas fa-expand"></i>');
                    $('.table-responsive').css('height', 'calc(100vh - 155px)');
                    $('.report-title').show();
                    localStorage.removeItem('report_implantes_fullscreen');
                } else {
                    $('.report-title').hide();
                }
            });

            if(localStorage.getItem('report_implantes_fullscreen') === 'true') {
                setTimeout(enterFS, 500);
            }

            initTableFeatures();
        });

        var updateTableStyles;

        function initTableConfig() {
            var confFontSize = document.getElementById('configFontSize');
            if(!confFontSize) return;
            var confRowHeight = document.getElementById('configRowHeight');
            var styleTag = document.getElementById('dynamic-table-styles-implantes');
            if(!styleTag) {
                styleTag = document.createElement('style');
                styleTag.id = 'dynamic-table-styles-implantes';
                document.head.appendChild(styleTag);
            }

            updateTableStyles = function() {
                var fs = confFontSize.value;
                var pv = confRowHeight.value + 'px';
                var css = `
                    #implantesTable tbody td { font-size: ${fs} !important; border-top-width: 0; padding-top: ${pv} !important; padding-bottom: ${pv} !important; }
                    #implantesTable tbody tr.total-row td { font-size: ${fs} !important; }
                `;
                styleTag.innerHTML = css;
                localStorage.setItem('tablaConfigImplantes', JSON.stringify({ fontSize: fs, rowHeight: confRowHeight.value }));
            };

            var saved = localStorage.getItem('tablaConfigImplantes');
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
                confFontSize.value = '18px';
                confRowHeight.value = '6';
                updateTableStyles();
            };
        }
    </script>


@endsection
