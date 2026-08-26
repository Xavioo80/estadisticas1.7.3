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
            const modal = document.getElementById('itsDetailsModal');
            const loader = document.getElementById('itsModalLoader');
            const body = document.getElementById('itsModalBody');

            if (!modal) return;

            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }

            modal.classList.remove('hidden');
            loader.classList.remove('hidden');
            body.classList.add('hidden');

            const ano = document.querySelector('select[name="ano"]')?.value || '{{ $ano }}';
            const mes = document.querySelector('select[name="mes"]')?.value || '{{ $mes }}';
            const jornada = document.querySelector('select[name="jornada"]')?.value || '{{ $jornada }}';

            const params = new URLSearchParams({
                ano: ano,
                mes: mes,
                jornada: jornada,
                label: label,
                col: colIdx
            });

            fetch(`{{ route('informes.its.details') }}?${params.toString()}`)
                .then(r => r.json())
                .then(data => {
                    if (!data.success) {
                        throw new Error(data.message || 'Error al cargar los datos');
                    }

                    document.getElementById('itsModalLabelTitle').innerText = data.label;
                    document.getElementById('itsModalColTitle').innerText = data.columna;
                    document.getElementById('itsModalBadgeTotal').innerText = `${data.total} Atenciones`;

                    const tbody = document.getElementById('itsModalTableBody');
                    tbody.innerHTML = '';

                    if (!data.records || data.records.length === 0) {
                        tbody.innerHTML = `<tr><td colspan="3" class="py-6 text-center text-slate-500 font-medium">No se encontraron registros detallados para esta celda.</td></tr>`;
                    } else {
                        data.records.forEach((r, idx) => {
                            const tr = document.createElement('tr');
                            tr.className = 'hover:bg-slate-50 transition-colors border-b border-slate-100';
                            tr.innerHTML = `
                                <td class="py-3 px-4 text-center font-semibold text-slate-400 w-12">${idx + 1}</td>
                                <td class="py-3 px-4">
                                    <div class="font-bold text-slate-900 text-sm">${r.medico}</div>
                                    ${r.prof ? `<div class="text-[11px] font-semibold text-blue-600 uppercase">${r.prof}</div>` : ''}
                                </td>
                                <td class="py-3 px-4 text-center font-bold text-slate-700 text-sm whitespace-nowrap w-36">${r.fecha}</td>
                            `;
                            tbody.appendChild(tr);
                        });
                    }

                    loader.classList.add('hidden');
                    body.classList.remove('hidden');
                })
                .catch(err => {
                    console.error('Error:', err);
                    loader.innerHTML = `<div class="p-6 text-center text-red-600 font-bold">Error al cargar la información: ${err.message}</div>`;
                });
        };

        window.closeItsDetailsModal = function() {
            const modal = document.getElementById('itsDetailsModal');
            if (modal) modal.classList.add('hidden');
        };
    </script>

    <!-- Modal de Detalles de Atenciones ITS -->
    <div id="itsDetailsModal" class="fixed inset-0 hidden items-center justify-center p-4 sm:p-6" style="z-index: 999999 !important;" aria-labelledby="modal-title" role="dialog" aria-modal="true">
        <div class="fixed inset-0 bg-slate-900/70 backdrop-blur-sm transition-opacity" style="z-index: 999998 !important;" onclick="closeItsDetailsModal()"></div>
        
        <div id="itsDetailsModalCard" class="relative flex flex-col w-full max-w-xl max-h-[80vh] transform overflow-hidden rounded-2xl bg-white text-left shadow-2xl transition-all border border-slate-200" style="fff !important; z-index: 999999 !important;">
            <!-- Header -->
            <div class="bg-slate-900 px-6 py-4 flex-shrink-0 flex items-center justify-between border-b border-slate-800">
                <div class="flex items-center space-x-3">
                    <div class="w-9 h-9 rounded-xl bg-blue-600/20 border border-blue-500/30 flex items-center justify-center text-blue-400">
                        <i class="fas fa-stethoscope text-base"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-bold text-white leading-tight" id="itsModalLabelTitle">Patología</h3>
                        <p class="text-xs text-blue-400 font-semibold" id="itsModalColTitle">Detalle de atenciones</p>
                    </div>
                </div>
                <div class="flex items-center space-x-3">
                    <span id="itsModalBadgeTotal" class="inline-flex items-center px-3 py-1 rounded-full text-xs font-bold bg-blue-500/20 text-blue-300 border border-blue-400/30">
                        0 Atenciones
                    </span>
                    <button type="button" onclick="closeItsDetailsModal()" class="text-slate-400 hover:text-white transition-colors p-1.5 rounded-lg hover:bg-slate-800">
                        <i class="fas fa-times text-base"></i>
                    </button>
                </div>
            </div>

            <!-- Loader -->
            <div id="itsModalLoader" class="p-12 text-center bg-white flex-1">
                <div class="inline-block animate-spin rounded-full h-8 w-8 border-4 border-blue-600 border-t-transparent"></div>
                <p class="mt-3 text-sm font-semibold text-slate-600">Cargando registros de atenciones...</p>
            </div>

            <!-- Body (Con scroll interno y cabecera pegajosa) -->
            <div id="itsModalBody" class="hidden flex-1 overflow-y-auto bg-white p-0">
                <table class="w-full text-left text-xs border-collapse">
                    <thead class="sticky top-0 bg-slate-100 shadow-sm z-10">
                        <tr class="bg-slate-100 text-slate-700 font-bold uppercase text-[11px] tracking-wider border-b border-slate-200">
                            <th class="py-3 px-4 w-12 text-center">#</th>
                            <th class="py-3 px-4">Quién Atendió (Médico)</th>
                            <th class="py-3 px-4 text-center w-36">Fecha</th>
                        </tr>
                    </thead>
                    <tbody id="itsModalTableBody" class="divide-y divide-slate-100">
                    </tbody>
                </table>
            </div>

            <!-- Footer -->
            <div class="bg-slate-50 px-6 py-3 border-t border-slate-200 flex-shrink-0 flex justify-end">
                <button type="button" onclick="closeItsDetailsModal()" class="px-4 py-2 bg-slate-800 text-white text-xs font-bold rounded-lg hover:bg-slate-900 transition-all shadow-sm">
                    Cerrar
                </button>
            </div>
        </div>
    </div>


@endsection
