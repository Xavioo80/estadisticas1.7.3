@extends('layouts.app')

@section('title', 'Directorio de Médicos - Estadísticas 1.7')

@push('styles')
<style>
    .medicos-toolbar {
        background-color: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 0.85rem 1.1rem;
        box-shadow: var(--shadow-sm);
        margin-bottom: 0.85rem;
    }
    .modal-backdrop-custom {
        position: fixed;
        inset: 0;
        z-index: 1050;
        background-color: rgba(11, 17, 32, 0.75);
        backdrop-filter: blur(4px);
        display: none !important;
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }
    .modal-backdrop-custom.active {
        display: flex !important;
    }
    .modal-dialog-custom {
        background-color: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-lg);
        width: 100%;
        max-width: 700px;
        max-height: 90vh;
        box-shadow: var(--shadow-xl);
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
</style>
@endpush

@section('content')
<div class="informe-page-wrapper">
    <!-- Header -->
    <div class="informe-header">
        <div class="d-flex align-items-center gap-3">
            <div class="d-inline-flex align-items-center justify-content-center" style="width: 42px; height: 42px; border-radius: var(--radius-md); background: rgba(77, 124, 254, 0.12); color: var(--color-primary);">
                <i class="bi bi-person-badge-fill" style="font-size: 1.25rem;"></i>
            </div>
            <div>
                <h2 class="mb-1 d-flex align-items-center gap-2">
                    Directorio del Personal Médico
                    <span id="medicoCountBadge" class="badge badge-subtle-primary" style="font-size: 0.8rem; font-weight: 700;">
                        <span id="totalMedicos">...</span> Médicos
                    </span>
                </h2>
                <p>Gestión de facultativos, códigos de consulta, especialidades y jornadas</p>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <a href="{{ route('medicos.create') }}" class="btn btn-primary btn-sm" style="font-weight: 600;">
                <i class="bi bi-person-plus mr-1"></i> Nuevo Médico
            </a>
        </div>
    </div>

    <!-- Barra de Herramientas & Filtros -->
    <div class="medicos-toolbar d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex flex-wrap align-items-center gap-3 flex-grow-1">
            <!-- Buscar médico -->
            <div class="position-relative flex-grow-1" style="min-width: 260px; max-width: 400px;">
                <i class="bi bi-search position-absolute" style="left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.85rem;"></i>
                <input type="search" id="buscadorMedico" class="form-control form-control-sm"
                    style="padding-left: 2.2rem; height: 34px; background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color);"
                    placeholder="Buscar por nombre, código o especialidad...">
            </div>

            <!-- Filtro de estado -->
            <div style="min-width: 180px; max-width: 220px;">
                <select id="filtroEstado" class="form-control form-control-sm font-weight-semibold"
                    style="height: 34px; background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color);"
                    onchange="applyFilters()">
                    <option value="">TODOS LOS ESTADOS</option>
                    <option value="activo">Solo Activos</option>
                    <option value="inactivo">Solo Inactivos</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Contenedor Principal de la Tabla -->
    <div class="table-responsive flex-grow-1" style="border-radius: var(--radius-md); border: 1px solid var(--border-color); background-color: var(--bg-surface); overflow-y: auto; max-height: calc(100vh - 250px);">
        <table id="medicos-table" class="table table-hover table-sing mb-0" style="font-size: 0.83rem; border-collapse: separate; border-spacing: 0;">
            <thead style="position: sticky; top: 0; z-index: 10; background-color: var(--bg-subtle); border-bottom: 2px solid var(--border-color);">
                <tr>
                    <th class="py-2 px-3 text-center" style="width: 50px; color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">N°</th>
                    <th class="py-2 px-3" style="min-width: 220px; color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">Médico</th>
                    <th class="py-2 px-3" style="width: 130px; color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">Jornada</th>
                    <th class="py-2 px-3" style="width: 160px; color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">Especialidad</th>
                    <th class="py-2 px-3" style="width: 130px; color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">Modalidad</th>
                    <th class="py-2 px-3" style="min-width: 180px; color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">Observaciones</th>
                    <th class="py-2 px-3 text-center" style="width: 100px; color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">Estado</th>
                    <th class="py-2 px-3 text-center" style="width: 100px; color: var(--text-muted); font-weight: 700;">Acciones</th>
                </tr>
            </thead>
            <tbody id="medicosTableBody" style="color: var(--text-primary);">
                <tr>
                    <td colspan="8" class="text-center py-5 text-muted">
                        <div class="spinner-border spinner-border-sm text-primary mr-2" role="status"></div>
                        Cargando personal médico...
                    </td>
                </tr>
            </tbody>
        </table>
    </div>

    <!-- Pie de tabla / Paginación -->
    <div class="mt-3 d-flex flex-column flex-md-row align-items-center justify-content-between p-2.5 rounded" style="background-color: var(--bg-surface); border: 1px solid var(--border-color); font-size: 0.82rem;">
        <span id="tableInfo" style="color: var(--text-secondary); font-weight: 500;">Cargando...</span>
        <div class="d-flex align-items-center gap-2">
            <button id="prevPage" onclick="changePage(-1)" class="btn btn-subtle btn-sm px-2 py-1" style="font-weight: 600;">
                <i class="bi bi-chevron-left"></i> Anterior
            </button>
            <span id="pageIndicator" class="badge badge-subtle-primary font-weight-bold px-2 py-1">1</span>
            <button id="nextPage" onclick="changePage(1)" class="btn btn-subtle btn-sm px-2 py-1" style="font-weight: 600;">
                Siguiente <i class="bi bi-chevron-right"></i>
            </button>
        </div>
    </div>
</div>

<!-- Modal de Edición de Médico -->
<div id="editMedicoModal" class="modal-backdrop-custom">
    <div class="modal-dialog-custom">
        <!-- Header -->
        <div class="d-flex align-items-center justify-content-between p-3" style="background-color: var(--color-primary); color: #fff;">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-person-badge-fill" style="font-size: 1.2rem;"></i>
                <div>
                    <h5 class="mb-0 font-weight-bold" style="font-size: 0.95rem;">Editar Información del Médico</h5>
                    <small id="editModalSubhead" style="opacity: 0.85; font-size: 0.75rem;">Modificar datos del profesional</small>
                </div>
            </div>
            <button type="button" onclick="closeEditModal()" class="btn btn-icon btn-sm" style="color: #fff; background: rgba(255,255,255,0.15);" title="Cerrar">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Form Body -->
        <form id="editMedicoForm" onsubmit="saveEditMedico(event)">
            @csrf
            <input type="hidden" id="edit_medico_id" name="id">

            <div class="p-3 overflow-auto" style="max-height: calc(85vh - 120px);">
                <div class="row">
                    <div class="col-md-4 mb-3">
                        <label class="form-label font-weight-bold" style="color: var(--text-primary); font-size: 0.82rem;">Código Médico <span class="text-danger">*</span></label>
                        <input type="text" id="edit_COD_MED" name="COD_MED" required
                            class="form-control form-control-sm font-monospace font-weight-bold"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); height: 34px;">
                    </div>

                    <div class="col-md-8 mb-3">
                        <label class="form-label font-weight-bold" style="color: var(--text-primary); font-size: 0.82rem;">Nombre Completo <span class="text-danger">*</span></label>
                        <input type="text" id="edit_NOM_MED" name="NOM_MED" required oninput="checkMssAutoDetect(this.value)"
                            class="form-control form-control-sm text-uppercase font-weight-semibold"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); height: 34px;">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="color: var(--text-primary); font-size: 0.82rem;">Jornada</label>
                        <select id="edit_JORNADA" name="JORNADA" class="form-control form-control-sm text-uppercase"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); height: 34px;">
                            <option value="MATUTINA">MATUTINA</option>
                            <option value="VESPERTINA">VESPERTINA</option>
                            <option value="NOCTURNA">NOCTURNA</option>
                            <option value="JORNADA COMPLETA">JORNADA COMPLETA</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="color: var(--text-primary); font-size: 0.82rem;">Especialidad</label>
                        <input type="text" id="edit_ESPECIALIDAD" name="ESPECIALIDAD" class="form-control form-control-sm text-uppercase"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); height: 34px;">
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="color: var(--text-primary); font-size: 0.82rem;">Modalidad</label>
                        <select id="edit_MODALIDAD" name="MODALIDAD" class="form-control form-control-sm text-uppercase"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); height: 34px;">
                            <option value="PERMANENTE">PERMANENTE</option>
                            <option value="CONTRATO">CONTRATO</option>
                            <option value="SERVICIO SOCIAL">SERVICIO SOCIAL</option>
                            <option value="INTERINATO">INTERINATO</option>
                        </select>
                    </div>

                    <div class="col-md-6 mb-3">
                        <label class="form-label font-weight-bold" style="color: var(--text-primary); font-size: 0.82rem;">Estado</label>
                        <select id="edit_estado" name="estado" class="form-control form-control-sm text-uppercase font-weight-bold"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); height: 34px;">
                            <option value="activo">ACTIVO</option>
                            <option value="inactivo">INACTIVO</option>
                        </select>
                    </div>

                    <div class="col-md-12 mb-3">
                        <label class="form-label font-weight-bold" style="color: var(--text-primary); font-size: 0.82rem;">Observaciones</label>
                        <textarea id="edit_observaciones" name="observaciones" rows="2" class="form-control form-control-sm"
                            style="background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color);"></textarea>
                    </div>

                    <div class="col-md-12">
                        <div class="p-2 rounded" style="background-color: var(--bg-subtle); border: 1px solid var(--border-color);">
                            <div class="form-check">
                                <input type="checkbox" id="edit_es_director" name="es_director" value="1" class="form-check-input">
                                <label class="form-check-label font-weight-bold" for="edit_es_director" style="color: var(--text-primary); font-size: 0.82rem;">
                                    Asignar como Director / Firma Principal del Mes
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <div class="d-flex align-items-center justify-content-end gap-2 p-3 border-top" style="background-color: var(--bg-subtle); border-color: var(--border-color) !important;">
                <button type="button" onclick="closeEditModal()" class="btn btn-secondary btn-sm" style="font-weight: 600;">
                    Cancelar
                </button>
                <button type="submit" id="btnSaveEditMedico" class="btn btn-primary btn-sm" style="font-weight: 600;">
                    <i class="bi bi-save mr-1"></i> Guardar Cambios
                </button>
            </div>
        </form>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
let allMedicos = [];
let filtered = [];
let page = 1;
const perPage = 50;

function loadMedicos() {
    $.ajax({
        url: '{{ route('medicos.index') }}',
        type: 'GET',
        dataType: 'json',
        headers: { 'X-Requested-With': 'XMLHttpRequest' },
        success: function(json) {
            allMedicos = json.data || [];
            applyFilters();
        },
        error: function(xhr, status, error) {
            $('#medicosTableBody').html(`<tr><td colspan="8" class="text-center py-5 text-danger">Error al cargar datos: ${error || 'Error de conexión'}</td></tr>`);
        }
    });
}

function applyFilters() {
    const q = ($('#buscadorMedico').val() || '').toLowerCase();
    const est = $('#filtroEstado').val();

    filtered = allMedicos.filter(m => {
        const matchQ = !q || (m.NOM_MED||'').toLowerCase().includes(q) || (m.COD_MED||'').toLowerCase().includes(q) || (m.ESPECIALIDAD||'').toLowerCase().includes(q) || (m.observaciones||'').toLowerCase().includes(q);
        const matchEst = !est || m.estado === est;
        return matchQ && matchEst;
    });

    page = 1;
    render();
}

function render() {
    const tbody = document.getElementById('medicosTableBody');
    if (!tbody) return;

    const start = (page - 1) * perPage;
    const pageData = filtered.slice(start, start + perPage);

    if (pageData.length === 0) {
        tbody.innerHTML = `<tr><td colspan="8" class="text-center py-5 text-muted">No se encontraron médicos</td></tr>`;
    } else {
        tbody.innerHTML = pageData.map((m, i) => {
            const rowNum = start + i + 1;
            const obs = m.observaciones || '—';
            const estadoBadge = m.estado === 'activo'
                ? '<span class="badge badge-subtle-success font-weight-bold">ACTIVO</span>'
                : '<span class="badge badge-subtle-danger font-weight-bold">INACTIVO</span>';

            const directorBadge = m.es_director
                ? '<span class="badge badge-subtle-warning ml-1 font-weight-bold"><i class="bi bi-star-fill mr-1"></i>DIRECTOR</span>'
                : '';

            return `<tr style="border-bottom: 1px solid var(--border-color);">
                <td class="py-2 px-3 text-center font-monospace" style="color: var(--text-muted); border-right: 1px solid var(--border-color);">${rowNum}</td>
                <td class="py-2 px-3 font-weight-semibold" style="border-right: 1px solid var(--border-color);">
                    <div class="d-flex align-items-center justify-content-between">
                        <span style="color: var(--text-primary); font-weight: 600;">${m.NOM_MED || ''}</span>
                        ${directorBadge}
                    </div>
                    <small class="font-monospace text-primary font-weight-bold">CÓD: ${m.COD_MED || ''}</small>
                </td>
                <td class="py-2 px-3 text-uppercase font-weight-medium" style="color: var(--text-secondary); border-right: 1px solid var(--border-color);">${m.JORNADA || '—'}</td>
                <td class="py-2 px-3 text-uppercase font-weight-medium" style="color: var(--text-secondary); border-right: 1px solid var(--border-color);">${m.ESPECIALIDAD || '—'}</td>
                <td class="py-2 px-3 text-uppercase font-weight-medium" style="color: var(--text-secondary); border-right: 1px solid var(--border-color);">${m.MODALIDAD || '—'}</td>
                <td class="py-2 px-3" style="color: var(--text-muted); font-size: 0.78rem; border-right: 1px solid var(--border-color);">${obs}</td>
                <td class="py-2 px-3 text-center" style="border-right: 1px solid var(--border-color);">${estadoBadge}</td>
                <td class="py-2 px-2 text-center">
                    <div class="d-inline-flex align-items-center justify-content-center gap-1">
                        <button onclick="openEditModalByCod('${m.COD_MED}')" class="btn btn-icon btn-sm btn-subtle-warning" title="Editar" style="width: 28px; height: 28px; border-radius: var(--radius-sm);">
                            <i class="bi bi-pencil-square" style="font-size: 0.85rem;"></i>
                        </button>
                        <button onclick="confirmDelete('${m.COD_MED}', '${(m.NOM_MED||'').replace(/'/g,"\\\'")}')" class="btn btn-icon btn-sm btn-subtle-danger" title="Eliminar" style="width: 28px; height: 28px; border-radius: var(--radius-sm);">
                            <i class="bi bi-trash" style="font-size: 0.85rem;"></i>
                        </button>
                    </div>
                </td>
            </tr>`;
        }).join('');
    }

    const totalEl = document.getElementById('totalMedicos');
    if (totalEl) totalEl.textContent = filtered.length;

    const totalPages = Math.ceil(filtered.length / perPage) || 1;
    const infoEl = document.getElementById('tableInfo');
    if (infoEl) infoEl.textContent = `Mostrando ${Math.min(start + 1, filtered.length)}–${Math.min(start + perPage, filtered.length)} de ${filtered.length} médicos`;

    const pageEl = document.getElementById('pageIndicator');
    if (pageEl) pageEl.textContent = `${page} / ${totalPages}`;
}

function changePage(delta) {
    const totalPages = Math.ceil(filtered.length / perPage) || 1;
    const newPage = page + delta;
    if (newPage >= 1 && newPage <= totalPages) {
        page = newPage;
        render();
    }
}

function checkMssAutoDetect(val) {
    if ((val || '').trim().toUpperCase().startsWith('MSS.')) {
        $('#edit_MODALIDAD').val('SERVICIO SOCIAL');
        $('#edit_ESPECIALIDAD').val('SERVICIO SOCIAL');
    }
}

function openEditModalByCod(cod) {
    const m = allMedicos.find(item => item.COD_MED == cod);
    if (!m) return;

    $('#edit_medico_id').val(m.id);
    $('#edit_COD_MED').val(m.COD_MED);
    $('#edit_NOM_MED').val(m.NOM_MED);
    $('#edit_JORNADA').val(m.JORNADA || 'MATUTINA');
    $('#edit_ESPECIALIDAD').val(m.ESPECIALIDAD || 'MEDICO GENERAL');
    $('#edit_MODALIDAD').val(m.MODALIDAD || 'PERMANENTE');
    $('#edit_estado').val(m.estado || 'activo');
    $('#edit_observaciones').val(m.observaciones || '');
    $('#edit_es_director').prop('checked', m.es_director == 1 || m.es_director === true);

    $('#editModalSubhead').text(m.NOM_MED);
    $('#editMedicoModal').addClass('active');
}

function closeEditModal() {
    $('#editMedicoModal').removeClass('active');
}

function saveEditMedico(e) {
    e.preventDefault();
    const id = $('#edit_medico_id').val();
    const formData = $('#editMedicoForm').serialize();

    $('#btnSaveEditMedico').prop('disabled', true);

    $.ajax({
        url: '/medicos/' + id,
        type: 'POST',
        data: formData + '&_method=PUT',
        success: function(res) {
            closeEditModal();
            $('#btnSaveEditMedico').prop('disabled', false);
            Swal.fire({
                icon: 'success',
                title: '¡Guardado!',
                text: 'Información médica actualizada con éxito',
                timer: 1500,
                showConfirmButton: false
            });
            loadMedicos();
        },
        error: function(xhr) {
            $('#btnSaveEditMedico').prop('disabled', false);
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: xhr.responseJSON?.message || 'Error al guardar los cambios'
            });
        }
    });
}

function confirmDelete(cod, nom) {
    Swal.fire({
        title: '¿Eliminar Médico?',
        text: `¿Estás seguro de eliminar a ${nom} (${cod})?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#ef4444',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            const m = allMedicos.find(item => item.COD_MED == cod);
            if (!m) return;

            $.ajax({
                url: '/medicos/' + m.id,
                type: 'POST',
                data: {
                    _token: '{{ csrf_token() }}',
                    _method: 'DELETE'
                },
                success: function() {
                    Swal.fire({
                        icon: 'success',
                        title: 'Eliminado',
                        text: 'Médico eliminado del directorio.',
                        timer: 1500,
                        showConfirmButton: false
                    });
                    loadMedicos();
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo eliminar el médico.'
                    });
                }
            });
        }
    });
}

$(document).ready(function() {
    loadMedicos();

    let searchTimer;
    $('#buscadorMedico').on('keyup', function() {
        clearTimeout(searchTimer);
        searchTimer = setTimeout(applyFilters, 250);
    });
});
</script>
@endpush
