@extends('layouts.app')

@section('title', 'Catálogo de Diagnósticos CIE-10 - Estadísticas 1.7')

@push('styles')
<style>
    .diagnosticos-toolbar {
        background-color: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 0.85rem 1.1rem;
        box-shadow: var(--shadow-sm);
        margin-bottom: 0.85rem;
    }
    .code-pill {
        font-family: var(--font-monospace, monospace);
        font-weight: 700;
        font-size: 0.82rem;
        background: rgba(77, 124, 254, 0.12);
        color: var(--color-primary);
        padding: 0.2rem 0.55rem;
        border-radius: var(--radius-sm);
        display: inline-block;
    }
</style>
@endpush

@section('content')
<div class="informe-page-wrapper">
    <!-- Header -->
    <div class="informe-header">
        <div class="d-flex align-items-center gap-3">
            <div class="d-inline-flex align-items-center justify-content-center" style="width: 42px; height: 42px; border-radius: var(--radius-md); background: rgba(77, 124, 254, 0.12); color: var(--color-primary);">
                <i class="bi bi-journal-medical" style="font-size: 1.25rem;"></i>
            </div>
            <div>
                <h2 class="mb-1 d-flex align-items-center gap-2">
                    Catálogo de Diagnósticos CIE-10
                    <span id="results-counter" class="badge badge-subtle-primary" style="font-size: 0.8rem; font-weight: 700;">
                        {{ number_format(count($diagnosticos)) }} ENCONTRADOS
                    </span>
                </h2>
                <p>Codificación CIE-10, categorización y reglas de validación epidemiológica</p>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-subtle btn-sm" onclick="ejecutarNormalizacion()" style="font-weight: 600;">
                <i class="bi bi-arrow-repeat mr-1"></i> Normalizar
            </button>
            <a href="{{ route('diagnosticos.condicionamientos') }}" class="btn btn-subtle-info btn-sm" style="font-weight: 600;">
                <i class="bi bi-shield-check mr-1"></i> Reglas Globales
            </a>
            <a href="{{ route('diagnosticos.create') }}" class="btn btn-primary btn-sm" style="font-weight: 600;">
                <i class="bi bi-plus-lg mr-1"></i> Nuevo CIE-10
            </a>
        </div>
    </div>

    <!-- Barra de Filtros -->
    <div class="diagnosticos-toolbar d-flex flex-wrap align-items-center justify-content-between gap-3">
        <form action="{{ route('diagnosticos.index') }}" method="GET" id="search-filter-form" class="d-flex flex-wrap align-items-center gap-3 flex-grow-1 mb-0">
            <!-- Buscar texto -->
            <div class="position-relative flex-grow-1" style="min-width: 260px; max-width: 420px;">
                <i class="bi bi-search position-absolute" style="left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.85rem;"></i>
                <input type="text" name="search" id="search-input" class="form-control form-control-sm"
                    style="padding-left: 2.2rem; height: 34px; background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color);"
                    placeholder="Buscar por nombre o código CIE-10..." value="{{ $search }}" autocomplete="off">
            </div>

            <!-- Categoría -->
            <div style="min-width: 220px; max-width: 280px;">
                <select name="categoria" id="categoria-select" class="form-control form-control-sm text-uppercase font-weight-semibold"
                    style="height: 34px; background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color);"
                    onchange="ejecutarBusqueda()">
                    <option value="TODAS">TODAS LAS CATEGORÍAS</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat }}" {{ $categoriaFiltrar == $cat ? 'selected' : '' }}>{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            @if($search || ($categoriaFiltrar && $categoriaFiltrar != 'TODAS'))
                <a href="{{ route('diagnosticos.index') }}" class="btn btn-subtle-danger btn-sm" title="Limpiar Filtros" style="font-weight: 600;">
                    <i class="bi bi-x-circle mr-1"></i> Limpiar
                </a>
            @endif
        </form>
    </div>

    <!-- Contenedor Principal de la Tabla -->
    <div class="table-responsive flex-grow-1" style="border-radius: var(--radius-md); border: 1px solid var(--border-color); background-color: var(--bg-surface); overflow-y: auto; max-height: calc(100vh - 250px);">
        <table class="table table-hover table-sing mb-0" style="font-size: 0.84rem; border-collapse: separate; border-spacing: 0;">
            <thead style="position: sticky; top: 0; z-index: 10; background-color: var(--bg-subtle); border-bottom: 2px solid var(--border-color);">
                <tr>
                    <th class="py-2 px-3" style="width: 100px; color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">Código</th>
                    <th class="py-2 px-3 text-center" style="width: 80px; color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">Aux</th>
                    <th class="py-2 px-3" style="color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">Patología / Diagnóstico</th>
                    <th class="py-2 px-3 text-center" style="width: 160px; color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">Categoría</th>
                    <th class="py-2 px-3 text-center" style="width: 130px; color: var(--text-muted); font-weight: 700;">Acciones</th>
                </tr>
            </thead>
            <tbody id="diagnosticos-tbody" style="color: var(--text-primary);">
                @forelse($diagnosticos as $diagnostico)
                    <tr style="border-bottom: 1px solid var(--border-color);">
                        <td class="py-2 px-3 font-monospace" style="border-right: 1px solid var(--border-color);">
                            <span class="code-pill">{{ $diagnostico->codigo }}</span>
                        </td>
                        <td class="py-2 px-3 text-center font-monospace font-weight-bold" style="color: var(--text-muted); font-size: 0.78rem; border-right: 1px solid var(--border-color);">
                            {{ $diagnostico->auxiliar ?: '-' }}
                        </td>
                        <td class="py-2 px-3 font-weight-semibold text-uppercase" style="color: var(--text-primary); border-right: 1px solid var(--border-color);">
                            {{ $diagnostico->patologia }}
                        </td>
                        <td class="py-2 px-3 text-center" style="border-right: 1px solid var(--border-color);">
                            <span class="badge badge-subtle-secondary px-2 py-1" style="font-size: 0.75rem; font-weight: 700;">
                                {{ $diagnostico->categoria ?: 'GENERAL' }}
                            </span>
                        </td>
                        <td class="py-2 px-2 text-center">
                            <div class="d-inline-flex align-items-center justify-content-center gap-1">
                                <a href="{{ route('diagnosticos.edit', $diagnostico) }}" class="btn btn-icon btn-sm btn-subtle-warning" title="Editar" style="width: 28px; height: 28px; border-radius: var(--radius-sm);">
                                    <i class="bi bi-pencil-square" style="font-size: 0.85rem;"></i>
                                </a>
                                <button type="button" class="btn btn-icon btn-sm btn-subtle-success btn-condicionamiento"
                                        data-id="{{ $diagnostico->id }}"
                                        data-codigo="{{ $diagnostico->codigo }}"
                                        data-patologia="{{ $diagnostico->patologia }}"
                                        title="Configurar Reglas" style="width: 28px; height: 28px; border-radius: var(--radius-sm);">
                                    <i class="bi bi-shield-check" style="font-size: 0.85rem;"></i>
                                </button>
                                <form action="{{ route('diagnosticos.destroy', $diagnostico) }}" method="POST" class="d-inline mb-0">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="btn btn-icon btn-sm btn-subtle-danger" onclick="return confirm('¿Eliminar este diagnóstico?')" title="Eliminar" style="width: 28px; height: 28px; border-radius: var(--radius-sm);">
                                        <i class="bi bi-trash" style="font-size: 0.85rem;"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="5" class="py-5 text-center" style="color: var(--text-muted);">
                            <div class="d-flex flex-column align-items-center justify-content-center gap-2">
                                <i class="bi bi-search" style="font-size: 2rem; opacity: 0.4;"></i>
                                <span style="font-size: 0.9rem; font-weight: 500;">No se encontraron diagnósticos que coincidan con la búsqueda</span>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function() {
    // Abrir modal de condicionamiento individual
    $('.btn-condicionamiento').click(function() {
        const id = $(this).data('id');
        const codigo = $(this).data('codigo');
        const patologia = $(this).data('patologia');

        $.ajax({
            url: `/diagnosticos/${id}`,
            method: 'GET',
            success: function(diagnostico) {
                mostrarModalCondicionamiento(diagnostico);
            },
            error: function() {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'No se pudo cargar el diagnóstico',
                    customClass: { popup: 'sing-swal-modal' }
                });
            }
        });
    });

    function mostrarModalCondicionamiento(diagnostico) {
        Swal.fire({
            title: `Reglas de Validación - CIE: ${diagnostico.codigo}`,
            html: `
                <div style="text-align: left; font-size: 0.88rem;">
                    <div class="p-2 mb-3 rounded" style="background: var(--bg-subtle); border: 1px solid var(--border-color);">
                        <strong style="color: var(--text-primary);">${diagnostico.patologia}</strong>
                    </div>

                    <div class="form-group mb-3">
                        <label style="font-weight: 600; color: var(--text-primary);">Rango de Edad:</label>
                        <div class="row">
                            <div class="col-6">
                                <label class="small text-muted mb-1">Edad Mínima</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" id="edad_minima" class="form-control"
                                           value="${diagnostico.edad_minima || ''}"
                                           min="0" max="120" placeholder="0" style="background: var(--input-bg); color: var(--text-primary); border-color: var(--border-color);">
                                    <div class="input-group-append">
                                        <select id="tipo_edad_min" class="form-control" style="background: var(--input-bg); color: var(--text-primary); border-color: var(--border-color);">
                                            <option value="A" ${diagnostico.tipo_edad === 'A' ? 'selected' : ''}>Años</option>
                                            <option value="M" ${diagnostico.tipo_edad === 'M' ? 'selected' : ''}>Meses</option>
                                            <option value="D" ${diagnostico.tipo_edad === 'D' ? 'selected' : ''}>Días</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="col-6">
                                <label class="small text-muted mb-1">Edad Máxima</label>
                                <div class="input-group input-group-sm">
                                    <input type="number" id="edad_maxima" class="form-control"
                                           value="${diagnostico.edad_maxima || ''}"
                                           min="0" max="120" placeholder="120" style="background: var(--input-bg); color: var(--text-primary); border-color: var(--border-color);">
                                    <div class="input-group-append">
                                        <select id="tipo_edad_max" class="form-control" style="background: var(--input-bg); color: var(--text-primary); border-color: var(--border-color);">
                                            <option value="A" ${diagnostico.tipo_edad === 'A' ? 'selected' : ''}>Años</option>
                                            <option value="M" ${diagnostico.tipo_edad === 'M' ? 'selected' : ''}>Meses</option>
                                            <option value="D" ${diagnostico.tipo_edad === 'D' ? 'selected' : ''}>Días</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group mb-3">
                        <label style="font-weight: 600; color: var(--text-primary);">Sexo Permitido:</label>
                        <select id="sexo_permitido" class="form-control form-control-sm" style="background: var(--input-bg); color: var(--text-primary); border-color: var(--border-color);">
                            <option value="ambos" ${diagnostico.sexo_permitido === 'ambos' || !diagnostico.sexo_permitido ? 'selected' : ''}>Ambos Sexos</option>
                            <option value="H" ${diagnostico.sexo_permitido === 'H' ? 'selected' : ''}>Solo Hombres</option>
                            <option value="M" ${diagnostico.sexo_permitido === 'M' ? 'selected' : ''}>Solo Mujeres</option>
                        </select>
                    </div>

                    <div class="form-group mb-3">
                        <label style="font-weight: 600; color: var(--text-primary);">Condiciones Especiales:</label>
                        <div class="d-flex flex-column gap-2 p-2 rounded" style="background: var(--bg-subtle); border: 1px solid var(--border-color);">
                            <div class="form-check">
                                <input type="checkbox" id="requiere_embarazo" class="form-check-input" ${diagnostico.requiere_embarazo ? 'checked' : ''}>
                                <label class="form-check-label small" for="requiere_embarazo" style="color: var(--text-primary);">Requiere Embarazo</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="es_pediatrico" class="form-check-input" ${diagnostico.es_pediatrico ? 'checked' : ''}>
                                <label class="form-check-label small" for="es_pediatrico" style="color: var(--text-primary);">Es Pediátrico</label>
                            </div>
                            <div class="form-check">
                                <input type="checkbox" id="es_adulto" class="form-check-input" ${diagnostico.es_adulto ? 'checked' : ''}>
                                <label class="form-check-label small" for="es_adulto" style="color: var(--text-primary);">Es Adulto Mayor</label>
                            </div>
                        </div>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="bi bi-save mr-1"></i> Guardar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#4d7cfe',
            preConfirm: () => {
                return {
                    edad_minima: document.getElementById('edad_minima').value || null,
                    edad_maxima: document.getElementById('edad_maxima').value || null,
                    tipo_edad: document.getElementById('tipo_edad_min').value,
                    sexo_permitido: document.getElementById('sexo_permitido').value,
                    requiere_embarazo: document.getElementById('requiere_embarazo').checked,
                    es_pediatrico: document.getElementById('es_pediatrico').checked,
                    es_adulto: document.getElementById('es_adulto').checked
                };
            }
        }).then((result) => {
            if (result.isConfirmed) {
                guardarCondicionamiento(diagnostico.id, result.value);
            }
        });
    }

    function guardarCondicionamiento(id, data) {
        $.ajax({
            url: `/diagnosticos/${id}/condicionamiento`,
            method: 'PUT',
            headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
            data: data,
            success: function() {
                Swal.fire({
                    icon: 'success',
                    title: '¡Guardado!',
                    text: 'Condicionamientos actualizados correctamente',
                    timer: 1500,
                    showConfirmButton: false
                });
            },
            error: function(xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: xhr.responseJSON?.message || 'Error al guardar los cambios'
                });
            }
        });
    }

    // Búsqueda en vivo
    let typingTimer;
    $('#search-input').on('keyup', function() {
        clearTimeout(typingTimer);
        typingTimer = setTimeout(ejecutarBusqueda, 300);
    });
});

function ejecutarBusqueda() {
    $('#search-filter-form').submit();
}

function ejecutarNormalizacion() {
    Swal.fire({
        title: '¿Normalizar Códigos y Categorías?',
        text: 'Se actualizarán las descripciones CIE-10 y categorías según la norma epidemiológica oficial.',
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sí, normalizar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#4d7cfe'
    }).then((result) => {
        if (result.isConfirmed) {
            Swal.fire({
                title: 'Normalizando catálogo...',
                didOpen: () => Swal.showLoading(),
                allowOutsideClick: false
            });
            $.ajax({
                url: "{{ route('diagnosticos.normalizar') }}",
                method: 'POST',
                headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') },
                success: function(res) {
                    Swal.fire({
                        icon: 'success',
                        title: 'Normalización completada',
                        text: res.message || 'Catálogo actualizado.',
                        confirmButtonColor: '#4d7cfe'
                    }).then(() => location.reload());
                },
                error: function() {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'No se pudo completar la normalización.'
                    });
                }
            });
        }
    });
}
</script>
@endpush
