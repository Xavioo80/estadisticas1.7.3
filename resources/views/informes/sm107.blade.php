@extends('layouts.app')

@section('title', 'Informe SM1-07 - Estadísticas 1.7')

@section('content')
<div class="informe-page-wrapper" style="height: calc(100vh - 75px) !important; max-height: calc(100vh - 75px) !important; overflow: hidden !important; display: flex !important; flex-direction: column !important;">
    <!-- Header -->
    <div class="informe-header" style="flex-shrink: 0 !important;">
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
    <div id="dynamic-content" style="flex: 1 1 0% !important; min-height: 0 !important; display: flex !important; flex-direction: column !important; overflow: hidden !important; position: relative !important;">
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

<!-- Modal para Detalles de Diagnósticos -->
<div class="modal fade" id="detailsModal" tabindex="-1" role="dialog" aria-labelledby="detailsModalLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document" style="max-width: 1050px;">
        <div class="modal-content" style="background: var(--bg-surface); border: 1px solid var(--border-color); color: var(--text-primary); border-radius: var(--radius-lg); box-shadow: var(--shadow-xl);">
            <div class="modal-header d-flex align-items-center justify-content-between py-2.5 px-3" style="background: var(--bg-subtle); border-bottom: 1px solid var(--border-color);">
                <div class="d-flex align-items-center gap-2">
                    <div class="p-2 rounded" style="background: rgba(77, 124, 254, 0.15); color: var(--color-primary);">
                        <i class="bi bi-list-check" style="font-size: 1.15rem;"></i>
                    </div>
                    <h5 class="modal-title font-weight-bold mb-0" id="detailsModalLabel" style="font-size: 1rem; color: var(--text-primary);">
                        Detalles de Registros Contabilizados
                    </h5>
                </div>
                <button type="button" class="close text-muted" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.8; font-size: 1.25rem; background: none; border: none; cursor: pointer; color: var(--text-primary);">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>
            <div class="modal-body p-0 custom-scrollbar" style="max-height: 480px; overflow-y: auto;">
                <div id="details-loader" class="text-center py-5" style="display: none;">
                    <div class="spinner-border text-primary mb-2" role="status" style="width: 2.5rem; height: 2.5rem;"></div>
                    <p class="mb-0 text-muted font-weight-bold" style="font-size: 0.85rem;">Obteniendo información de la base de datos...</p>
                </div>
                <div id="details-content" class="table-responsive">
                    <!-- Aquí se cargará la tabla de detalles -->
                </div>
            </div>
            <div class="modal-footer py-2 px-3 d-flex justify-content-end" style="background: var(--bg-subtle); border-top: 1px solid var(--border-color);">
                <button type="button" class="btn btn-sm btn-subtle" data-dismiss="modal" data-bs-dismiss="modal">
                    <i class="bi bi-x-lg mr-1"></i> Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    document.addEventListener('DOMContentLoaded', function() {
        function refreshReport() {
            const $form = $('#filter-form');
            if (!$form.length) return;
            
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
                    
                    const $newScrollContainer = $('.table-responsive');
                    $newScrollContainer.scrollLeft(scrollLeft);
                    $newScrollContainer.scrollTop(scrollTop);

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
            $('.btn-toggle-view').removeClass('btn-primary').addClass('btn-subtle');
            $(this).removeClass('btn-subtle').addClass('btn-primary');
            $('#view-input').val($(this).data('view'));
            refreshReport();
        });

        window.showDetails = function(rowId, fecha) {
            const ano = $('select[name="ano"]').val();
            const mes = $('select[name="mes"]').val();
            const jornada = $('select[name="jornada"]').val();
            const view = $('#view-input').val();

            const $modal = $('#detailsModal');
            $modal.appendTo('body');
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
                    fecha: fecha,
                    _t: new Date().getTime()
                },
                success: function(response) {
                    let html = `
                        <table class="table table-sm table-hover mb-0 align-middle" style="font-size: 0.8rem; background: var(--bg-surface);">
                            <thead class="sticky-top" style="background: var(--bg-subtle); z-index: 10; border-bottom: 2px solid var(--border-color); color: var(--text-muted); font-size: 0.72rem; text-transform: uppercase;">
                                <tr>
                                    <th class="py-2 px-3 text-center" style="width: 85px;">Fecha</th>
                                    <th class="py-2 px-2 text-center" style="width: 65px;">N° Reg</th>
                                    <th class="py-2 px-2 text-center" style="width: 85px;">Expediente</th>
                                    <th class="py-2 px-2 text-center" style="width: 95px;">Edad / Sexo</th>
                                    <th class="py-2 px-3">Médico</th>
                                    <th class="py-2 px-3">Diagnóstico Principal</th>
                                    <th class="py-2 px-3">Otros Diagnósticos</th>
                                    <th class="py-2 px-2 text-center" style="width: 50px;">SM</th>
                                </tr>
                            </thead>
                            <tbody>
                    `;

                    if (!response.records || response.records.length === 0) {
                        html += '<tr><td colspan="8" class="text-center py-4 text-muted font-weight-bold">No se encontraron registros detallados.</td></tr>';
                    } else {
                        response.records.forEach(r => {
                            let formattedDate = '-';
                            if (r.fecha && r.fecha !== '0000-00-00') {
                                try {
                                    const [y, m, d] = r.fecha.split('-');
                                    formattedDate = `${d}/${m}/${y}`;
                                } catch (e) {
                                    formattedDate = r.fecha;
                                }
                            }

                            const isSmDiag1 = response.smSearchStrings && response.smSearchStrings.includes(r.diagnostico_1 ? r.diagnostico_1.trim() : '');
                            const isSmDiag2 = response.smSearchStrings && response.smSearchStrings.includes(r.diagnostico_2 ? r.diagnostico_2.trim() : '');
                            const isSmDiag3 = response.smSearchStrings && response.smSearchStrings.includes(r.diagnostico_3 ? r.diagnostico_3.trim() : '');
                            
                            html += `
                                <tr style="border-bottom: 1px solid var(--border-color);">
                                    <td class="py-2 px-3 text-center font-weight-bold" style="color: var(--text-primary); font-size: 0.8rem;">${formattedDate}</td>
                                    <td class="py-2 px-2 text-center text-muted" style="font-size: 0.76rem;">${r.numero || '-'}</td>
                                    <td class="py-2 px-2 text-center font-monospace" style="font-size: 0.78rem; color: var(--color-primary); font-weight: 700;">${r.exp || '-'}</td>
                                    <td class="py-2 px-2 text-center" style="font-size: 0.76rem;">
                                        <span class="badge badge-subtle-secondary">${r.edad}${r.tipo}</span>
                                        <span class="badge badge-subtle-info ml-1">${r.sexo}</span>
                                    </td>
                                    <td class="py-2 px-3" style="font-size: 0.8rem; color: var(--text-primary); font-weight: 600;">${r.medico || '-'}</td>
                                    <td class="py-2 px-3">
                                        <div class="font-weight-bold" style="font-size: 0.8rem; color: ${isSmDiag1 ? 'var(--color-primary)' : 'var(--text-primary)'};">${r.cod_1 ? `[${r.cod_1}] ` : ''}${r.diagnostico_1 || ''}</div>
                                    </td>
                                    <td class="py-2 px-3 small text-muted" style="font-size: 0.75rem;">
                                        ${r.diagnostico_2 ? `<div class="mb-0.5 ${isSmDiag2 ? 'text-primary font-weight-bold' : ''}">${r.cod_2 ? `[${r.cod_2}] ` : ''}${r.diagnostico_2}</div>` : ''}
                                        ${r.diagnostico_3 ? `<div class="${isSmDiag3 ? 'text-primary font-weight-bold' : ''}">${r.cod_3 ? `[${r.cod_3}] ` : ''}${r.diagnostico_3}</div>` : ''}
                                    </td>
                                    <td class="py-2 px-2 text-center">
                                        ${(isSmDiag1 || isSmDiag2 || isSmDiag3) ? '<span class="badge badge-subtle-success font-weight-bold">SÍ</span>' : '<span class="badge badge-subtle-secondary">NO</span>'}
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
                    $('#details-content').html('<div class="alert alert-danger m-3 text-center"><i class="bi bi-exclamation-octagon-fill mr-1"></i> Error al cargar los detalles.</div>').show();
                    $('#details-loader').hide();
                }
            });
        };

        window.toggleFullScreen = function() {
            var wrapper = document.querySelector('.informe-page-wrapper') || document.body;
            if (!document.fullscreenElement) {
                wrapper.requestFullscreen().catch(err => {
                    console.error(`Error al activar pantalla completa: ${err.message}`);
                });
                $('#fullScreenIcon').removeClass('bi-arrows-fullscreen').addClass('bi-fullscreen-exit');
            } else {
                document.exitFullscreen();
                $('#fullScreenIcon').removeClass('bi-fullscreen-exit').addClass('bi-arrows-fullscreen');
            }
        };

        document.addEventListener('fullscreenchange', function() {
            if (!document.fullscreenElement) {
                $('#fullScreenIcon').removeClass('bi-fullscreen-exit').addClass('bi-arrows-fullscreen');
            }
        });

        function initTableFeatures() {
            // Interacciones de hover y estilos limpios
        }

        initTableFeatures();
    });
</script>
@endsection
