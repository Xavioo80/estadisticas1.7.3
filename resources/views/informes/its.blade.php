@extends('layouts.app')

@section('title', 'Informe de ITS - Estadísticas 1.7')

@section('content')
<div class="informe-page-wrapper">
    <!-- Header -->
    <div class="informe-header">
        <div>
            <h2><i class="bi bi-shield-plus text-primary mr-1"></i> Informe de ITS</h2>
            <p>Vigilancia Epidemiológica de Infecciones de Transmisión Sexual</p>
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

        @include('informes.its_content')
    </div>
</div>

<script>
        document.addEventListener('DOMContentLoaded', function() {
            
            function refreshReport() {
                const $form = $('#filter-form');
                if (!$form.length) return;
                
                const url = $form.attr('action');
                const data = $form.serialize();

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
                var table = document.getElementById('itsTable');
                if(table) {
                    $(table).off('mouseenter mouseleave', 'td');
                    $(table).on('mouseenter', 'td', function() {
                        var $this = $(this);
                        var idxStr = $this.attr('data-col-idx');
                        if (idxStr === undefined) return;
                        
                        var idx = parseInt(idxStr);
                        $this.addClass('hover-cell');
                        $this.parent().addClass('hover-row');
                        
                        $(table).find('tbody tr, thead tr:nth-child(3), tfoot tr').each(function() {
                            $(this).find('th, td').filter('[data-col-idx="' + idx + '"]').addClass('hover-col');
                        });
                    }).on('mouseleave', 'td', function() {
                        var $this = $(this);
                        $this.removeClass('hover-cell');
                        $this.parent().removeClass('hover-row');
                        $(table).find('.hover-col').removeClass('hover-col');
                    });
                }
                $('.table-responsive').css('height', 'calc(100vh - 160px)');
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
                    $('.table-responsive').css('height', 'calc(100vh - 160px)');
                }
            });

            initTableFeatures();

            // Asegurar que el modal esté en el body al cargar la página
            const modalEl = document.getElementById('itsDetailsModal');
            if (modalEl && modalEl.parentElement !== document.body) {
                document.body.appendChild(modalEl);
            }
        });

        window.openItsDetailsModal = function(label, colIdx) {
            const $modal = $('#itsDetailsModal');
            const $loader = $('#itsModalLoader');
            const $body = $('#itsModalBody');

            $modal.modal('show');
            $loader.show();
            $body.hide();

            const ano = $('select[name="ano"]').val() || '{{ $ano }}';
            const mes = $('select[name="mes"]').val() || '{{ $mes }}';
            const jornada = $('select[name="jornada"]').val() || '{{ $jornada }}';

            const params = new URLSearchParams({
                ano: ano,
                mes: mes,
                jornada: jornada,
                label: label,
                col: colIdx,
                _t: new Date().getTime()
            });

            fetch(`{{ route('informes.its.details') }}?${params.toString()}`)
                .then(r => r.json())
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.message || 'Error al cargar los datos');
                    }

                    $('#itsModalLabelTitle').text(data.label);
                    $('#itsModalColTitle').text(data.columna);
                    $('#itsModalBadgeTotal').text(`${data.total} Atenciones`);

                    const tbody = document.getElementById('itsModalTableBody');
                    tbody.innerHTML = '';

                    if (!data.records || data.records.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="6" class="py-4 text-center text-muted font-weight-bold">No se encontraron registros detallados para esta celda.</td></tr>`;
                    } else {
                        data.records.forEach((r, idx) => {
                            const tr = document.createElement('tr');
                            tr.style.borderBottom = '1px solid var(--border-color)';
                            tr.innerHTML = `
                                <td class="text-center font-weight-bold text-muted py-2" style="font-size: 0.78rem;">${idx + 1}</td>
                                <td class="text-center font-weight-bold py-2" style="font-size: 0.8rem; color: var(--text-primary);">${r.fecha}</td>
                                <td class="text-center py-2" style="font-size: 0.78rem; font-family: monospace; color: var(--color-primary); font-weight: 700;">${r.exp || '-'}</td>
                                <td class="py-2 text-left">
                                    <div class="font-weight-bold" style="font-size: 0.82rem; color: var(--text-primary);">${r.medico}</div>
                                    ${r.prof ? `<span class="badge badge-subtle-primary text-uppercase" style="font-size: 0.68rem;">${r.prof}</span>` : ''}
                                </td>
                                <td class="text-center py-2" style="font-size: 0.78rem;">
                                    <span class="badge badge-subtle-secondary">${r.edad}</span>
                                    <span class="badge badge-subtle-info ml-1">${r.sexo}</span>
                                </td>
                                <td class="py-2 text-left">
                                    <div class="font-weight-bold" style="font-size: 0.8rem; color: var(--text-primary);">${r.diagnostico}</div>
                                    ${r.cod && r.cod !== '-' ? `<span class="badge badge-secondary" style="font-size: 0.68rem; background: var(--bg-subtle); color: var(--text-muted); border: 1px solid var(--border-color);">${r.cod}</span>` : ''}
                                    <span class="badge ${r.cond.includes('N') ? 'badge-subtle-success' : 'badge-subtle-warning'} ml-1" style="font-size: 0.68rem;">${r.cond}</span>
                                </td>
                            `;
                            tbody.appendChild(tr);
                        });
                    }

                    $loader.hide();
                    $body.show();
                })
                .catch(err => {
                    console.error('Error:', err);
                    $loader.html(`<div class="p-4 text-center text-danger font-weight-bold"><i class="bi bi-exclamation-octagon-fill mr-1"></i> Error al cargar la información: ${err.message}</div>`).show();
                    $body.hide();
                });
        };
    </script>

    <!-- Modal de Detalles de Atenciones ITS -->
    <div class="modal fade" id="itsDetailsModal" tabindex="-1" role="dialog" aria-labelledby="itsModalLabelTitle" aria-hidden="true" style="z-index: 1060;">
        <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document" style="max-width: 1000px;">
            <div class="modal-content" style="background: var(--bg-surface); border: 1px solid var(--border-color); color: var(--text-primary); border-radius: var(--radius-lg); box-shadow: var(--shadow-xl);">
                <!-- Header -->
                <div class="modal-header d-flex align-items-center justify-content-between py-2.5 px-3" style="background: var(--bg-subtle); border-bottom: 1px solid var(--border-color);">
                    <div class="d-flex align-items-center gap-2">
                        <div class="p-2 rounded" style="background: rgba(77, 124, 254, 0.15); color: var(--color-primary);">
                            <i class="bi bi-shield-plus" style="font-size: 1.15rem;"></i>
                        </div>
                        <div>
                            <h5 class="modal-title font-weight-bold mb-0" id="itsModalLabelTitle" style="font-size: 1rem; color: var(--text-primary);">Patología</h5>
                            <p class="text-muted mb-0" style="font-size: 0.72rem;" id="itsModalColTitle">Detalle de atenciones</p>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <span id="itsModalBadgeTotal" class="badge badge-subtle-primary px-2.5 py-1 font-weight-bold" style="font-size: 0.75rem; border: 1px solid var(--border-color);">
                            0 Atenciones
                        </span>
                        <button type="button" class="close text-muted" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close" style="opacity: 0.8; font-size: 1.25rem; background: none; border: none; cursor: pointer; color: var(--text-primary);">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>

                <!-- Loader -->
                <div id="itsModalLoader" class="py-5 text-center" style="display: none;">
                    <div class="spinner-border text-primary mb-2" role="status" style="width: 2rem; height: 2rem;"></div>
                    <p class="mb-0 text-muted font-weight-bold" style="font-size: 0.85rem;">Cargando registros de atenciones...</p>
                </div>

                <!-- Body (Con scroll interno y cabecera pegajosa) -->
                <div id="itsModalBody" class="modal-body p-0 custom-scrollbar" style="max-height: 480px; overflow-y: auto;">
                    <table class="table table-sm table-hover mb-0 align-middle" style="font-size: 0.8rem; background: var(--bg-surface);">
                        <thead class="sticky-top" style="background: var(--bg-subtle); z-index: 10; border-bottom: 2px solid var(--border-color);">
                            <tr style="color: var(--text-muted); font-size: 0.72rem; text-transform: uppercase;">
                                <th class="text-center py-2 px-2" style="width: 40px;">#</th>
                                <th class="text-center py-2 px-2" style="width: 90px;">Fecha</th>
                                <th class="text-center py-2 px-2" style="width: 95px;">Expediente</th>
                                <th class="py-2 px-3">Quién Atendió (Médico / Profesional)</th>
                                <th class="text-center py-2 px-2" style="width: 140px;">Edad / Sexo</th>
                                <th class="py-2 px-3" style="width: 240px;">Diagnóstico & Condición</th>
                            </tr>
                        </thead>
                        <tbody id="itsModalTableBody">
                        </tbody>
                    </table>
                </div>

                <!-- Footer -->
                <div class="modal-footer py-2 px-3 d-flex justify-content-end" style="background: var(--bg-subtle); border-top: 1px solid var(--border-color);">
                    <button type="button" class="btn btn-sm btn-subtle" data-dismiss="modal" data-bs-dismiss="modal">
                        <i class="bi bi-x-lg mr-1"></i> Cerrar
                    </button>
                </div>
            </div>
        </div>
    </div>


@endsection
