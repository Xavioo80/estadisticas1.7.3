<x-app-layout>
    @section('title', 'Seguimientos')
    <x-slot name="header">
        <div class="d-flex align-items-center justify-content-between py-2">
            <div>
                <h2 class="font-bold text-xl text-slate-900 leading-none mb-1">
                    {{ __('Seguimientos y Consultas') }}
                </h2>
                <p class="text-[10px] font-semibold text-slate-400 uppercase tracking-[0.2em] m-0">Historial Global de Atenciones</p>
            </div>
            <div class="d-flex align-items-center gap-2">
                {{-- Filtro de Fechas --}}
                <div class="d-flex align-items-center bg-white border rounded shadow-sm px-2 gap-2" style="height: 35px;">
                    <span class="text-[10px] font-bold text-muted uppercase">Desde:</span>
                    <input type="date" id="fecha-desde" class="border-0 p-0 text-sm" style="width: 110px; outline: none; font-size: 0.8rem;">
                    <span class="text-[10px] font-bold text-muted uppercase">Hasta:</span>
                    <input type="date" id="fecha-hasta" class="border-0 p-0 text-sm" style="width: 110px; outline: none; font-size: 0.8rem;">
                </div>

                {{-- Buscador --}}
                <div class="input-group shadow-sm" style="width: 250px;">
                    <div class="input-group-prepend">
                        <span class="input-group-text bg-white border-right-0" style="height: 35px;"><i class="fas fa-search text-muted"></i></span>
                    </div>
                    <input type="text" id="search-input" value="{{ request('search') }}" 
                        class="form-control border-left-0 border-right-0" 
                        style="font-size: 0.9rem; height: 35px; border-radius: 0;" placeholder="Buscar...">
                    <div class="input-group-append">
                        <button id="btn-clear-search" class="btn btn-light border-left-0 px-2" type="button" style="height: 35px;"><i class="fas fa-times text-muted"></i></button>
                    </div>
                </div>
            </div>
        </div>
    </x-slot>

    @push('css')
    <style>
        /* ── Estilo ATA (Spreadsheet) ──────────────────────────────── */
        .ata-table-container {
            background-color: #f8fafc;
            padding: 10px;
            height: calc(100vh - 120px);
            overflow: hidden;
            display: flex;
            flex-direction: column;
        }

        .ata-card {
            background: white;
            border: 1px solid #cbd5e1;
            border-radius: 4px;
            flex: 1;
            display: flex;
            flex-direction: column;
            overflow: hidden;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
        }

        .ata-table-wrapper {
            flex: 1;
            overflow: auto;
        }

        .table-ata {
            font-family: 'Aptos Narrow', 'Arial Narrow', sans-serif !important;
            font-size: 14px !important;
            border-collapse: separate;
            border-spacing: 0;
            width: 100%;
        }

        .table-ata thead th {
            background: linear-gradient(to bottom, #f2f2f2, #e8e8e8) !important;
            color: #000 !important;
            font-weight: 400 !important;
            border-bottom: 2px solid #9e9e9e !important;
            border-right: 1px solid #b8b8b8 !important;
            text-align: center !important;
            height: 28px !important;
            line-height: 28px !important;
            padding: 0 8px !important;
            position: sticky;
            top: 0;
            z-index: 10;
            white-space: nowrap;
        }

        .table-ata tbody td {
            height: 24px !important;
            line-height: 24px !important;
            padding: 0 4px !important;
            border-right: 1px solid #d0d0d0 !important;
            border-bottom: 1px solid #d0d0d0 !important;
            color: #000 !important;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .table-ata tbody tr:nth-child(odd) td {
            background-color: #ffffff !important;
        }

        .table-ata tbody tr:nth-child(even) td {
            background-color: #f5f7fa !important;
        }

        .table-ata tbody tr:hover td {
            background-color: #fefce8 !important;
        }

        /* Footer Estilo ATA */
        .ata-footer {
            background: white;
            border-top: 1px solid #cbd5e1;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            padding: 0 15px;
            font-size: 10px;
            font-weight: 800;
            color: #475569;
            text-transform: uppercase;
            letter-spacing: 1px;
        }

        .badge-ata {
            background: #fee2e2;
            color: #b91c1c;
            padding: 2px 8px;
            border-radius: 4px;
            margin-left: 5px;
        }

        /* Loading Spinner */
        #infinite-scroll-loader {
            display: none;
            text-align: center;
            padding: 10px;
            background: white;
            font-weight: bold;
            font-size: 11px;
            color: #64748b;
        }
    </style>
    @endpush

    <div class="ata-table-container">
        @if(session('success'))
            <div class="alert alert-success py-1 px-3 mb-2 small fw-bold shadow-sm" style="font-size: 0.7rem;">
                {{ session('success') }}
            </div>
        @endif
        
        <div class="ata-card">
            <div class="ata-table-wrapper" id="table-wrapper">
                <table class="table-ata">
                    <thead>
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th style="width: 100px;">EXPEDIENTE</th>
                            <th style="width: 100px;">FECHA</th>
                            <th>NOMBRE COMPLETO</th>
                            <th style="width: 50px;">SEXO</th>
                            <th style="width: 50px;">EDAD</th>
                            <th>DIAGNÓSTICO / MOTIVO DE CONSULTA</th>
                            <th>TUTOR</th>
                            <th style="width: 100px;">TELÉFONO</th>
                            <th style="width: 100px;">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody id="table-body">
                        @include('adolescentes.partials.table_rows_seguimientos')
                    </tbody>
                </table>
                <div id="infinite-scroll-loader">
                    <i class="fas fa-spinner fa-spin mr-2"></i> CARGANDO MÁS REGISTROS...
                </div>
            </div>

            <div class="ata-footer">
                <div class="d-flex align-items-center">
                    <span>SEGUIMIENTOS: <span class="badge-ata" id="total-count">{{ $registros->total() }}</span></span>
                    <span class="mx-3">|</span>
                    <span>VISTA: <span class="badge-ata bg-light text-dark border" id="loaded-count">{{ $registros->count() }}</span></span>
                </div>
                <div id="pagination-container" style="display: none;">
                    {{ $registros->links() }}
                </div>
            </div>
        </div>
    </div>

    {{-- MODAL EDITAR SEGUIMIENTO --}}
    <div class="modal fade" id="modalEditarSeguimiento" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form id="formEditarSeguimiento" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="modal-header bg-info text-white">
                        <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Editar Seguimiento</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4">
                        <div id="loadingSeg" class="text-center py-3 d-none">
                            <div class="spinner-border text-info" role="status"></div>
                        </div>
                        <div id="camposSeg">
                            <div class="form-row">
                                <div class="form-group col-md-6">
                                    <label class="fw-bold small text-muted text-uppercase">Fecha de Consulta</label>
                                    <input type="date" name="fecha_consulta" id="edit_seg_fecha" class="form-control" required>
                                </div>
                                <div class="form-group col-md-6">
                                    <label class="fw-bold small text-muted text-uppercase">Edad</label>
                                    <input type="number" name="edad" id="edit_seg_edad" class="form-control" required>
                                </div>
                            </div>
                            <div class="form-group mb-0">
                                <label class="fw-bold small text-muted text-uppercase">Diagnóstico / Motivo</label>
                                <textarea name="diagnostico_seguimiento" id="edit_seg_diag" class="form-control" rows="4" required></textarea>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-info px-4 fw-bold text-white shadow-sm">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tableWrapper = document.getElementById('table-wrapper');
            const tableBody = document.getElementById('table-body');
            const searchInput = document.getElementById('search-input');
            const fechaDesde = document.getElementById('fecha-desde');
            const fechaHasta = document.getElementById('fecha-hasta');
            const loader = document.getElementById('infinite-scroll-loader');
            const loadedCountBadge = document.getElementById('loaded-count');
            const btnClearSearch = document.getElementById('btn-clear-search');

            let currentPage = 1;
            let lastPage = {{ $registros->lastPage() }};
            let isLoading = false;
            let searchTimeout = null;

            function loadData(reset = false) {
                if (isLoading) return;
                if (reset) {
                    currentPage = 1;
                } else {
                    if (currentPage >= lastPage) return;
                    currentPage++;
                }

                isLoading = true;
                if (!reset) loader.style.display = 'block';
                if (reset) tableBody.style.opacity = '0.5';

                const params = new URLSearchParams({
                    page: currentPage,
                    search: searchInput.value,
                    fecha_desde: fechaDesde.value || '',
                    fecha_hasta: fechaHasta.value || ''
                });

                fetch(`{{ route('adolescentes.seguimientos') }}?${params.toString()}`, {
                    headers: { 'X-Requested-With': 'XMLHttpRequest' }
                })
                .then(response => response.text())
                .then(html => {
                    if (reset) {
                        tableBody.innerHTML = html;
                        tableBody.style.opacity = '1';
                        tableWrapper.scrollTop = 0;
                    } else {
                        tableBody.insertAdjacentHTML('beforeend', html);
                    }
                    isLoading = false;
                    loader.style.display = 'none';
                    loadedCountBadge.innerText = tableBody.querySelectorAll('tr').length;
                })
                .catch(error => {
                    console.error('Error loadData:', error);
                    isLoading = false;
                    loader.style.display = 'none';
                    tableBody.style.opacity = '1';
                });
            }

            tableWrapper.addEventListener('scroll', function() {
                if (tableWrapper.scrollTop + tableWrapper.clientHeight >= tableWrapper.scrollHeight - 50) {
                    loadData();
                }
            });

            const triggerSearch = () => {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => loadData(true), 300);
            };

            searchInput.addEventListener('input', triggerSearch);
            fechaDesde.addEventListener('change', triggerSearch);
            fechaHasta.addEventListener('change', triggerSearch);
            btnClearSearch.addEventListener('click', () => {
                searchInput.value = '';
                triggerSearch();
            });

            // DELEGACIÓN PARA BOTÓN EDITAR SEGUIMIENTO
            document.addEventListener('click', function(e) {
                const btnEdit = e.target.closest('.btn-editar-seguimiento');
                if (btnEdit) {
                    const id = btnEdit.dataset.id;
                    const form = document.getElementById('formEditarSeguimiento');
                    form.action = `/adolescentes/seguimiento/${id}`;
                    
                    document.getElementById('loadingSeg').classList.remove('d-none');
                    document.getElementById('camposSeg').classList.add('opacity-50');
                    $('#modalEditarSeguimiento').modal('show');

                    fetch(`/adolescentes/seguimiento/${id}/edit`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(r => {
                        if(!r.ok) throw new Error('Status ' + r.status);
                        return r.json();
                    })
                    .then(data => {
                        document.getElementById('edit_seg_fecha').value = data.fecha_consulta;
                        document.getElementById('edit_seg_edad').value = data.edad;
                        document.getElementById('edit_seg_diag').value = data.diagnostico_seguimiento;
                        
                        document.getElementById('loadingSeg').classList.add('d-none');
                        document.getElementById('camposSeg').classList.remove('opacity-50');
                    })
                    .catch(err => {
                        console.error('Error fetch edit seg:', err);
                        alert('Error al cargar datos del seguimiento.');
                        $('#modalEditarSeguimiento').modal('hide');
                    });
                }
            });

            // Gestionar aria-hidden para modal
            $('#modalEditarSeguimiento').on('show.bs.modal', function() {
                $(this).removeAttr('aria-hidden');
            }).on('hidden.bs.modal', function() {
                $(this).attr('aria-hidden', 'true');
            });
        });
    </script>

    <style>
        .table-ata td { vertical-align: middle !important; }
        .modal-header .btn-close { margin: 0; }
    </style>
</x-app-layout>
