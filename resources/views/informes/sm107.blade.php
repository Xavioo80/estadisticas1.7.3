@extends('layouts.app')

@section('title', 'Informe SM1-07 - Estadísticas 1.7')

@section('content')
<div class="informe-page-wrapper">
    <!-- Header -->
    <div class="informe-header">
        <div>
            <h2><i class="bi bi-heart-pulse text-primary mr-1"></i> Informe SM1-07</h2>
            <p>Salud Materno Infantil SM1-07</p>
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

        @include('informes.sm107_content')
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <!-- Modal para Detalles de Diagnósticos - Movido fuera de contenedores relativos -->
    <div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content shadow-lg border-0" style="border-radius: 15px; overflow: hidden;">
                <div class="modal-header bg-slate-800 text-white" style="border-radius: 0; padding: 1.2rem 1.5rem;">
                    <h5 class="modal-title font-weight-bold" id="detailsModalLabel">
                        <i class="fas fa-list-ul mr-2 text-blue-300"></i>Detalles de Registros Contabilizados
                    </h5>
                    <button type="button" class="close text-white opacity-100" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="outline: none;">
                        <span aria-hidden="true" style="font-size: 1.5rem;">&times;</span>
                    </button>
                </div>
                <div class="modal-body p-0" >
                    <div id="details-loader" class="text-center py-5" style="display: none;">
                        <div class="spinner-border text-primary" role="status" style="width: 3rem; height: 3rem;">
                            <span class="sr-only">Cargando...</span>
                        </div>
                        <p class="mt-3 text-slate-500 font-medium">Obteniendo información de la base de datos...</p>
                    </div>
                    <div id="details-content" class="table-responsive" style="max-height: 70vh; min-height: 200px;">
                        <!-- Aquí se cargará la tabla de detalles -->
                    </div>
                </div>
                <div class="modal-footer bg-white border-top px-4 py-3">
                    <button type="button" class="btn btn-secondary font-weight-bold px-4 rounded-lg" data-dismiss="modal" data-bs-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        // Función global vacía para evitar errores de referencia iniciales
        window.updateTableStyles = function() {};

        document.addEventListener('DOMContentLoaded', function() {
            // --- INYECCIÓN SIDEBAR CONFIG ---
            var sidebarHtml = `
            <div class="p-3">
                <h5 class="mb-3 text-dark font-weight-bold"><i class="fas fa-sliders-h mr-2"></i>Ajustes de Vista</h5>
                <hr>
                <div class="mb-3">
                    <label class="small font-weight-bold">Tamaño Fuente</label>
                    <select id="configFontSize" class="form-control form-control-sm filter-select">
                        <option value="12px">Pequeño (12px)</option>
                        <option value="14px" selected>Normal (14px)</option>
                        <option value="16px">Grande (16px)</option>
                        <option value="18px">Extra Grande (18px)</option>
                    </select>
                </div>
                <div class="mb-4">
                    <label class="small font-weight-bold">Altura Filas</label>
                    <input type="range" class="custom-range" id="configRowHeight" min="0" max="10" value="2">
                </div>
                <button class="btn btn-sm btn-outline-danger btn-block" onclick="resetConfig()">
                    <i class="fas fa-undo mr-1"></i> Restaurar
                </button>
            </div>`;
            
            var $sidebar = $('.control-sidebar-content');
            if($sidebar.length) {
                $sidebar.html(sidebarHtml);
                initTableConfig();
            }

            function refreshReport() {
                const $form = $('#filter-form');
                if (!$form.length) return;
                
                // GUARDAR POSICIONES DE SCROLL
                const $scrollContainer = $('.table-responsive');
                const scrollLeft = $scrollContainer.scrollLeft();
                const scrollTop = $scrollContainer.scrollTop();

                const data = $form.serialize();
                $('#table-loader').css('display', 'flex').fadeIn(100);

                $.ajax({
                    url: "{{ route('informes.sm107') }}",
                    type: 'GET',
                    data: data,
                    success: function(response) {
                        $('#dynamic-content').html(response);
                        initTableFeatures();
                        
                        // RESTAURAR POSICIONES DE SCROLL
                        const $newScrollContainer = $('.table-responsive');
                        $newScrollContainer.scrollLeft(scrollLeft);
                        $newScrollContainer.scrollTop(scrollTop);

                        if (typeof window.updateTableStyles === 'function') {
                            window.updateTableStyles();
                        }
                        $('#table-loader').fadeOut(100);
                    },
                    error: function() {
                        alert('Error al actualizar los datos');
                        $('#table-loader').fadeOut(100);
                    }
                });
            }

            $(document).on('change', '.ajax-filter', refreshReport);
            $(document).on('click', '.btn-toggle-view', function() {
                $('.btn-toggle-view').removeClass('active');
                $(this).addClass('active');
                $('#view-input').val($(this).data('view'));
                refreshReport();
            });

            window.showDetails = function(rowId, fecha) {
                const ano = $('select[name="ano"]').val();
                const mes = $('select[name="mes"]').val();
                const jornada = $('select[name="jornada"]').val();
                const view = $('#view-input').val();

                const $modal = $('#detailsModal');
                $modal.appendTo('body'); // MOVER AL BODY PARA EVITAR STACKING CONTEXT
                $modal.modal('show');
                $('#details-loader').show();
                $('#details-content').hide();

                $.ajax({
                    url: "{{ route('informes.sm107.details') }}",
                    type: 'GET',
                    data: {
                        ano: ano,
                        mes: mes,
                        jornada: jornada,
                        view: view,
                        rowId: rowId,
                        fecha: fecha
                    },
                    success: function(response) {
                        let html = `
                            <table class="table table-hover mb-0" style="font-size: 13px;">
                                <thead class="bg-slate-100 text-slate-700 sticky-top">
                                    <tr>
                                        <th class="py-3 px-4">Fecha</th>
                                        <th class="py-3 px-4">N° Reg</th>
                                        <th class="py-3 px-4">Expediente</th>
                                        <th class="py-3 px-4">Edad/Sexo</th>
                                        <th class="py-3 px-4">Médico</th>
                                        <th class="py-3 px-4">Diagnóstico Principal</th>
                                        <th class="py-3 px-4">Otros Diagnósticos</th>
                                        <th class="py-3 px-4 text-center">SM</th>
                                    </tr>
                                </thead>
                                <tbody>
                        `;

                        if (response.records.length === 0) {
                            html += '<tr><td colspan="8" class="text-center py-5 text-slate-400 font-medium">No se encontraron registros detallados.</td></tr>';
                        } else {
                            response.records.forEach(r => {
                                // Formatear fecha de forma segura
                                let formattedDate = 'Invalid Date';
                                if (r.fecha && r.fecha !== '0000-00-00') {
                                    try {
                                        // r.fecha viene como YYYY-MM-DD
                                        const [y, m, d] = r.fecha.split('-');
                                        formattedDate = `${d}/${m}/${y}`;
                                    } catch (e) {
                                        formattedDate = r.fecha;
                                    }
                                } else {
                                    formattedDate = '<span class="text-muted italic">Sin fecha</span>';
                                }

                                const isSmDiag1 = response.smSearchStrings.includes(r.diagnostico_1 ? r.diagnostico_1.trim() : '');
                                const isSmDiag2 = response.smSearchStrings.includes(r.diagnostico_2 ? r.diagnostico_2.trim() : '');
                                const isSmDiag3 = response.smSearchStrings.includes(r.diagnostico_3 ? r.diagnostico_3.trim() : '');
                                
                                html += `
                                    <tr class="hover:bg-slate-50 transition-colors">
                                        <td class="py-3 px-4 font-medium text-slate-600">${formattedDate}</td>
                                        <td class="py-3 px-4 text-slate-500">${r.numero || '-'}</td>
                                        <td class="py-3 px-4 text-slate-500">${r.exp || '-'}</td>
                                        <td class="py-3 px-4"><span class="badge badge-outline-secondary">${r.edad}${r.tipo}</span> / <span class="font-bold">${r.sexo}</span></td>
                                        <td class="py-3 px-4 small text-slate-600">${r.medico || '-'}</td>
                                        <td class="py-3 px-4">
                                            <div class="font-weight-bold ${isSmDiag1 ? 'text-blue-600' : 'text-slate-700'}">${r.cod_1} - ${r.diagnostico_1 || ''}</div>
                                        </td>
                                        <td class="py-3 px-4 small text-slate-400">
                                            ${r.diagnostico_2 ? `<div class="mb-1 ${isSmDiag2 ? 'text-blue-500 font-medium' : ''}">${r.cod_2 || ''} ${r.diagnostico_2}</div>` : ''}
                                            ${r.diagnostico_3 ? `<div class="${isSmDiag3 ? 'text-blue-500 font-medium' : ''}">${r.cod_3 || ''} ${r.diagnostico_3}</div>` : ''}
                                        </td>
                                        <td class="py-3 px-4 text-center">
                                            ${(isSmDiag1 || isSmDiag2 || isSmDiag3) ? '<span class="badge badge-pill badge-success px-3">SÍ</span>' : '<span class="badge badge-pill badge-light text-slate-300 px-3 border">NO</span>'}
                                        </td>
                                    </tr>
                                `;
                            });
                        }

                        html += '</tbody></table>';
                        $('#details-content').html(html).show();
                        $('#details-loader').hide();
                    },
                    error: function() {
                        $('#details-content').html('<div class="alert alert-danger m-4">Error al cargar los detalles.</div>').show();
                        $('#details-loader').hide();
                    }
                });
            };

            // FULLSCREEN TOGGLE - usando API nativa del navegador
            var wrapper = document.getElementById('report-wrapper');
            
            window.toggleFullScreen = function() {
                if (!document.fullscreenElement) {
                    wrapper.requestFullscreen().catch(err => {
                        console.error(`Error al intentar activar pantalla completa: ${err.message}`);
                    });
                    $(wrapper).addClass('fullscreen-mode');
                    $('#fullScreenIcon').removeClass('fa-expand').addClass('fa-compress');
                    $('#btn-fullscreen').attr('title', 'Salir de Pantalla Completa');
                    // En fullscreen, el contenedor de la tabla debe llenar el espacio
                    $('.table-responsive').css('height', '100%');
                } else {
                    document.exitFullscreen();
                }
            };

            // Listener para cuando se sale de pantalla completa (con ESC u otro método)
            document.addEventListener('fullscreenchange', function() {
                if (!document.fullscreenElement) {
                    $(wrapper).removeClass('p-3 fullscreen-mode');
                    $('#fullScreenIcon').removeClass('fa-compress').addClass('fa-expand');
                    $('#btn-fullscreen').attr('title', 'Pantalla Completa');
                    $('.table-responsive').css('height', 'auto');
                    // Asegurar que los estilos de la tabla se mantengan
                    if (typeof window.updateTableStyles === 'function') {
                        window.updateTableStyles();
                    }
                }
            });

            function initTableFeatures() {
                // Eliminar eventos previos para evitar duplicados
                $('.table-premium td').off('mouseenter mouseleave');
                
                $('.table-premium td').on('mouseenter', function() {
                    var $td = $(this);
                    var idx = $td.index();
                    var $table = $td.closest('table');
                    
                    // No resaltar si es columna fija o header
                    if ($td.closest('thead').length) return;

                    $td.addClass('hover-cell');
                    $td.closest('tr').addClass('hover-row');
                    
                    $table.find('tbody tr').each(function() {
                        $(this).find('td').eq(idx).addClass('hover-col');
                    });
                    
                    // Resaltar también el header de la columna
                    $table.find('thead tr:last-child th').eq(idx).addClass('bg-blue-100');
                }).on('mouseleave', function() {
                    var $td = $(this);
                    var idx = $td.index();
                    var $table = $td.closest('table');

                    $td.removeClass('hover-cell');
                    $td.closest('tr').removeClass('hover-row');
                    
                    $table.find('tbody tr').each(function() {
                        $(this).find('td').eq(idx).removeClass('hover-col');
                    });
                    
                    $table.find('thead th').removeClass('bg-blue-100');
                });
            }

            function initTableConfig() {
                var fontSize = $('#configFontSize');
                var rowHeight = $('#configRowHeight');
                
                window.updateTableStyles = function() {
                    var fs = fontSize.val();
                    var pad = rowHeight.val() + 'px';
                    var fsInt = parseInt(fs);
                    var firstColFs = (fsInt + 3) + 'px'; // Primera columna +3px que el resto

                    var styles = `
                        .table-premium td { font-size: ${fs} !important; border: 1px solid #000 !important; padding-top: ${pad} !important; padding-bottom: ${pad} !important; text-align: center; }
                        .table-premium th { font-size: ${fs} !important; border: 1px solid #000 !important; }
                        .table-premium .sticky-col-first { font-size: ${firstColFs} !important; text-align: left !important; font-weight: 600 !important; }
                        .table-premium tr:hover td { background-color: #dbeafe !important; }
                    `;
                    $('#dynamic-styles').remove();
                    $('<style id="dynamic-styles">').text(styles).appendTo('head');
                };

                fontSize.on('change', updateTableStyles);
                rowHeight.on('input', updateTableStyles);
                window.resetConfig = function() { fontSize.val('14px'); rowHeight.val(10); updateTableStyles(); };
                updateTableStyles();
            }

            initTableFeatures();
        });
    </script>


@endsection
