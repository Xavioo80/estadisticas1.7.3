<x-app-layout>
    <nav aria-label="breadcrumb">
        <ol class="breadcrumb mb-2" style="font-size: 0.9rem;">
            <li class="breadcrumb-item"><a href="{{ route('adolescentes.index') }}" class="text-decoration-none">Registros</a></li>
            <li class="breadcrumb-item active" aria-current="page">Historial</li>
        </ol>
    </nav>

    <div class="row g-2 mb-3">
    <!-- Card 1: Perfil (Dark Blue) -->
    <div class="col-md-3">
        <div class="card shadow rounded-3 border-0 h-100" style="background: linear-gradient(135deg, #0d47a1, #1565c0); color: white;">
            <div class="card-body p-3 d-flex align-items-center">
                <div class="bg-white bg-opacity-25 rounded-circle d-flex align-items-center justify-content-center me-3 shadow-sm" style="width: 45px; height: 45px;">
                    <i class="fas fa-user-circle" style="font-size: 1.5rem;"></i>
                </div>
                <div>
                    <h5 class="mb-0 fw-bold" style="font-size: 1.2rem; line-height: 1.1;">{{ $adolescente->nombre_completo }}</h5>
                    <span class="badge bg-white text-primary mt-1 shadow-sm px-3" style="font-size: 0.8rem; font-weight: bold;">
                        <i class="fas fa-file-medical me-1"></i>EXP: {{ $adolescente->no_expediente }}
                    </span>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 2: Identidad (Dark Indigo) -->
    <div class="col-md-2">
        <div class="card shadow rounded-3 border-0 h-100" style="background: linear-gradient(135deg, #4527a0, #673ab7); color: white;">
            <div class="card-body p-3">
                <span class="d-block fw-bold opacity-75 mb-1" style="font-size: 0.7rem; letter-spacing: 0.8px;">IDENTIDAD</span>
                <div class="d-flex align-items-center">
                    <i class="fas fa-fingerprint opacity-50 me-2" style="font-size: 1.1rem;"></i>
                    <span class="fw-bold" style="font-size: 0.95rem;">{{ $adolescente->numero_identidad }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 3: Género (Dark Blue/Ruby) -->
    @php 
        $genderGradient = $adolescente->sexo == 'M' ? 'linear-gradient(135deg, #1565c0, #1e88e5)' : 'linear-gradient(135deg, #ad1457, #d81b60)';
    @endphp
    <div class="col-md-2">
        <div class="card shadow rounded-3 border-0 h-100" style="background: {{ $genderGradient }}; color: white;">
            <div class="card-body p-3 text-center">
                <span class="d-block fw-bold opacity-75 mb-1" style="font-size: 0.7rem; letter-spacing: 0.8px;">SEXO / GÉNERO</span>
                <div class="mt-1">
                    @if($adolescente->sexo == 'M')
                        <span style="font-size: 0.95rem; fw-bold"><i class="fas fa-mars me-1"></i>MASCULINO</span>
                    @else
                        <span style="font-size: 0.95rem; fw-bold"><i class="fas fa-venus me-1"></i>FEMENINO</span>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <!-- Card 4: Edad (Burnt Orange) -->
    <div class="col-md-2">
        <div class="card shadow rounded-3 border-0 h-100" style="background: linear-gradient(135deg, #e65100, #fb8c00); color: white;">
            <div class="card-body p-3 text-center">
                <span class="d-block fw-bold opacity-75 mb-1" style="font-size: 0.7rem; letter-spacing: 0.8px;">INGRESO AL SISTEMA</span>
                <div class="mt-1">
                    <i class="fas fa-calendar-check opacity-75 me-1" style="font-size: 1.1rem;"></i>
                    <span class="fw-bold" style="font-size: 0.95rem;">{{ $adolescente->fecha_ingreso ? \Carbon\Carbon::parse($adolescente->fecha_ingreso)->format('d/m/Y') : '-' }}</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Card 5: Consultas (Teal/Dark Green) -->
    <div class="col-md-3">
        <div class="card shadow rounded-3 border-0 h-100" style="background: linear-gradient(135deg, #004d40, #00796b); color: white;">
            <div class="card-body p-3 d-flex justify-content-between align-items-center">
                <div>
                    <span class="d-block fw-bold opacity-75 mb-1" style="font-size: 0.7rem; letter-spacing: 0.8px;">HISTORIAL CLÍNICO</span>
                    <h4 class="mb-0 fw-bold" style="font-size: 1.1rem;">{{ $seguimientos->count() }} ATENCIONES</h4>
                </div>
                <div class="col-auto">
                    <button type="button" class="btn btn-sm btn-success fw-bold px-3 shadow-sm" data-toggle="modal" data-target="#modalNuevoSeguimiento">
                        <i class="fas fa-plus"></i> Nuevo Seguimiento
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>

    @if(session('success'))
        <div class="alert alert-success py-1 px-3 small fw-bold mb-3 shadow-sm border-0" style="font-size: 0.75rem;">
            <i class="fas fa-check-circle me-1"></i> {{ session('success') }}
        </div>
    @endif

    @push('css')
    <style>
        /* ── Estilo ATA (Spreadsheet) ──────────────────────────────── */
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
        }

        .table-ata tbody td {
            height: 24px !important;
            line-height: 24px !important;
            padding: 0 4px !important;
            border-right: 1px solid #d0d0d0 !important;
            border-bottom: 1px solid #d0d0d0 !important;
            color: #000 !important;
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
    </style>
    @endpush

    <div class="card shadow-sm border-0 overflow-hidden">
        <div class="card-header bg-dark text-white py-1 px-3 d-flex align-items-center justify-content-between">
            <h6 class="mb-0 fw-bold text-uppercase" style="font-size: 0.7rem;">
                <i class="fas fa-history me-2 text-info"></i>Historial Clínico de Atenciones
            </h6>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table-ata">
                    <thead>
                        <tr>
                            <th style="width: 40px;">#</th>
                            <th style="width: 100px;">FECHA</th>
                            <th style="width: 60px;">EDAD</th>
                            <th>DIAGNÓSTICO / MOTIVO DE CONSULTA</th>
                            <th style="width: 150px;">TUTOR</th>
                            <th style="width: 100px;">TELÉFONO</th>
                            <th style="width: 80px;">ACCIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($seguimientos as $index => $seg)
                            <tr>
                                <td class="text-center text-muted">{{ $index + 1 }}</td>
                                <td class="text-center text-primary font-weight-bold">
                                    {{ \Carbon\Carbon::parse($seg->fecha_consulta)->format('d/m/Y') }}
                                </td>
                                <td class="text-center">{{ $seg->edad }}</td>
                                <td class="text-wrap" style="white-space: normal; line-height: 1.2;">{{ $seg->diagnostico_seguimiento }}</td>
                                <td class="text-muted">{{ $seg->nombre_tutor }}</td>
                                <td class="text-center">{{ $seg->numero_telefono }}</td>
                                <td class="text-center">
                                    <div class="d-flex justify-content-center align-items-center h-100 gap-1 px-1">
                                        <button type="button" 
                                            class="btn btn-info btn-sm p-0 d-flex align-items-center justify-content-center btn-editar-seguimiento" 
                                            data-id="{{ $seg->id }}"
                                            style="width: 20px; height: 18px; border-radius: 2px; border: none;"
                                            title="Editar">
                                            <i class="fas fa-edit text-white" style="font-size: 9px;"></i>
                                        </button>
                                        <form action="{{ route('adolescentes.seguimiento.destroy', $seg->id) }}" method="POST" onsubmit="return confirm('¿Eliminar seguimiento?');" class="m-0">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-danger btn-sm p-0 d-flex align-items-center justify-content-center"
                                                style="width: 20px; height: 18px; border-radius: 2px;"
                                                title="Eliminar">
                                                <i class="fas fa-trash-alt" style="font-size: 9px;"></i>
                                            </button>
                                        </form>
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">No se encontraron registros de atenciones.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    {{-- MODAL NUEVO SEGUIMIENTO --}}
    <div class="modal fade" id="modalNuevoSeguimiento" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form action="{{ route('adolescentes.seguimiento.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="no_expediente" value="{{ $adolescente->no_expediente }}">
                    <input type="hidden" name="nombre_completo" value="{{ $adolescente->nombre_completo }}">
                    <input type="hidden" name="sexo" value="{{ $adolescente->sexo }}">
                    <input type="hidden" name="fecha_nacimiento" value="{{ $adolescente->fecha_nacimiento }}">
                    <input type="hidden" name="numero_identidad" value="{{ $adolescente->numero_identidad }}">
                    <input type="hidden" name="nombre_tutor" value="{{ $adolescente->nombre_tutor }}">
                    <input type="hidden" name="direccion_completa" value="{{ $adolescente->direccion_completa }}">
                    <input type="hidden" name="numero_telefono" value="{{ $adolescente->numero_telefono }}">
                    <input type="hidden" name="estado_civil" value="{{ $adolescente->estado_civil }}">
                    <input type="hidden" name="escolaridad" value="{{ $adolescente->escolaridad }}">
                    <input type="hidden" name="ocupacion" value="{{ $adolescente->ocupacion }}">

                    <div class="modal-header bg-success text-white">
                        <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Nuevo Seguimiento</h5>
                        <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                    <div class="modal-body p-4">
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="fw-bold small text-muted text-uppercase">Fecha de Consulta</label>
                                <input type="date" name="fecha_consulta" class="form-control" value="{{ date('Y-m-d') }}" required>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="fw-bold small text-muted text-uppercase">Edad</label>
                                <input type="number" name="edad" class="form-control" value="{{ $adolescente->edad }}" required>
                            </div>
                        </div>
                        <div class="form-group mb-0">
                            <label class="fw-bold small text-muted text-uppercase">Diagnóstico / Motivo</label>
                            <textarea name="diagnostico_seguimiento" class="form-control" rows="4" required></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Cancelar</button>
                        <button type="submit" class="btn btn-success px-4 fw-bold">Guardar</button>
                    </div>
                </form>
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
                        <button type="submit" class="btn btn-info px-4 fw-bold text-white">Actualizar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const modalEditSeg = new bootstrap.Modal(document.getElementById('modalEditarSeguimiento'));

            document.querySelectorAll('.btn-editar-seguimiento').forEach(btn => {
                btn.addEventListener('click', function() {
                    const id = this.dataset.id;
                    const form = document.getElementById('formEditarSeguimiento');
                    form.action = `/adolescentes/seguimiento/${id}`;
                    
                    document.getElementById('loadingSeg').classList.remove('d-none');
                    document.getElementById('camposSeg').classList.add('opacity-50');
                    $('#modalEditarSeguimiento').modal('show');

                    fetch(`/adolescentes/seguimiento/${id}/edit`, {
                        headers: { 'X-Requested-With': 'XMLHttpRequest' }
                    })
                    .then(r => r.json())
                    .then(data => {
                        document.getElementById('edit_seg_fecha').value = data.fecha_consulta;
                        document.getElementById('edit_seg_edad').value = data.edad;
                        document.getElementById('edit_seg_diag').value = data.diagnostico_seguimiento;
                        
                        document.getElementById('loadingSeg').classList.add('d-none');
                        document.getElementById('camposSeg').classList.remove('opacity-50');
                    });
                });
            });

            // Gestionar aria-hidden para modales
            $('#modalEditarSeguimiento').on('show.bs.modal', function() {
                $(this).removeAttr('aria-hidden');
            }).on('hidden.bs.modal', function() {
                $(this).attr('aria-hidden', 'true');
            });

            $('#modalNuevoSeguimiento').on('show.bs.modal', function() {
                $(this).removeAttr('aria-hidden');
            }).on('hidden.bs.modal', function() {
                $(this).attr('aria-hidden', 'true');
            });
        });
    </script>

    <style>
        .table th,
        .table td {
            border-color: #000000 !important;
            padding-top: 0.25rem !important;
            padding-bottom: 0.25rem !important;
        }

        .bg-primary-subtle {
            background-color: #e7f1ff !important;
        }

        .border-primary-shared {
            border-color: #b6d4fe !important;
        }
        .modal-header .btn-close { margin: 0; }
    </style>
</x-app-layout>
