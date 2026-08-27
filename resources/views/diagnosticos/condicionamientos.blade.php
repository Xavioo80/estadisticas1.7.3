@extends('layouts.app')

@section('title', 'Condicionamientos de Diagnósticos - Estadísticas 1.7')

@push('styles')
<style>
    .cond-toolbar {
        background-color: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 0.85rem 1.1rem;
        box-shadow: var(--shadow-sm);
        margin-bottom: 0.85rem;
    }
    .input-compact-num {
        width: 52px !important;
        text-align: center !important;
        padding: 2px 4px !important;
        font-size: 0.82rem !important;
        background-color: var(--input-bg) !important;
        color: var(--text-primary) !important;
        border-color: var(--border-color) !important;
    }
    .input-compact-type {
        width: 80px !important;
        font-size: 0.8rem !important;
        padding: 2px 4px !important;
        background-color: var(--input-bg) !important;
        color: var(--text-primary) !important;
        border-color: var(--border-color) !important;
    }
    .select-compact-sexo {
        font-size: 0.8rem !important;
        background-color: var(--input-bg) !important;
        color: var(--text-primary) !important;
        border-color: var(--border-color) !important;
    }
</style>
@endpush

@section('content')
<div class="informe-page-wrapper">
    <!-- Header -->
    <div class="informe-header">
        <div class="d-flex align-items-center gap-3">
            <div class="d-inline-flex align-items-center justify-content-center" style="width: 42px; height: 42px; border-radius: var(--radius-md); background: rgba(77, 124, 254, 0.12); color: var(--color-primary);">
                <i class="bi bi-shield-check" style="font-size: 1.25rem;"></i>
            </div>
            <div>
                <h2 class="mb-1 d-flex align-items-center gap-2">
                    Condicionamientos y Reglas de Diagnósticos
                    <span class="badge badge-subtle-primary" id="contador-visibles" style="font-size: 0.8rem; font-weight: 700;">
                        {{ count($diagnosticos) }} Diagnósticos
                    </span>
                </h2>
                <p>Reglas epidemiológicas: límites de edad mínima/máxima, sexo permitido y condiciones especiales</p>
            </div>
        </div>

        <div class="d-flex align-items-center gap-2">
            <button type="button" id="btn-guardar-todos" class="btn btn-success btn-sm" style="font-weight: 600;">
                <i class="bi bi-save mr-1"></i> Guardar Todos los Cambios
            </button>
            <a href="{{ route('diagnosticos.index') }}" class="btn btn-subtle btn-sm" style="font-weight: 600;">
                <i class="bi bi-arrow-left mr-1"></i> Volver a Diagnósticos
            </a>
        </div>
    </div>

    <!-- Barra de Filtros -->
    <div class="cond-toolbar d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex flex-wrap align-items-center gap-3 flex-grow-1">
            <!-- Buscar texto -->
            <div class="position-relative flex-grow-1" style="min-width: 240px; max-width: 380px;">
                <i class="bi bi-search position-absolute" style="left: 12px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.85rem;"></i>
                <input type="text" id="buscar-diagnostico" class="form-control form-control-sm"
                    style="padding-left: 2.2rem; height: 34px; background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color);"
                    placeholder="Buscar por código CIE o nombre..." autocomplete="off">
            </div>

            <!-- Categoría -->
            <div style="min-width: 200px; max-width: 260px;">
                <select id="filtro-categoria" class="form-control form-control-sm text-uppercase font-weight-semibold"
                    style="height: 34px; background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color);">
                    <option value="">TODAS LAS CATEGORÍAS</option>
                    @foreach($categorias as $cat)
                        <option value="{{ $cat }}">{{ $cat }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Filtro de Validaciones -->
            <div style="min-width: 190px; max-width: 230px;">
                <select id="filtro-validaciones" class="form-control form-control-sm text-uppercase font-weight-semibold"
                    style="height: 34px; background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color);">
                    <option value="">TODAS LAS REGLAS</option>
                    <option value="con">CON VALIDACIONES</option>
                    <option value="sin">SIN VALIDACIONES</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Contenedor Principal de la Tabla -->
    <div class="table-responsive flex-grow-1" style="border-radius: var(--radius-md); border: 1px solid var(--border-color); background-color: var(--bg-surface); overflow-y: auto; max-height: calc(100vh - 250px);">
        <table class="table table-hover table-sing mb-0" id="tabla-condicionamientos" style="font-size: 0.82rem; border-collapse: separate; border-spacing: 0;">
            <thead style="position: sticky; top: 0; z-index: 10; background-color: var(--bg-subtle); border-bottom: 2px solid var(--border-color);">
                <tr>
                    <th class="py-2 px-3" style="width: 85px; color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">Código</th>
                    <th class="py-2 px-3" style="color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">Diagnóstico</th>
                    <th class="py-2 px-3 text-center" style="width: 140px; color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">Categoría</th>
                    <th class="py-2 px-3 text-center" style="width: 170px; color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">Edad Mínima</th>
                    <th class="py-2 px-3 text-center" style="width: 170px; color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">Edad Máxima</th>
                    <th class="py-2 px-3 text-center" style="width: 140px; color: var(--text-muted); font-weight: 700; border-right: 1px solid var(--border-color);">Sexo</th>
                    <th class="py-2 px-3 text-center" style="width: 140px; color: var(--text-muted); font-weight: 700;">Acciones</th>
                </tr>
            </thead>
            <tbody id="tbody-condicionamientos" style="color: var(--text-primary);">
                @foreach($diagnosticos as $diagnostico)
                    <tr data-id="{{ $diagnostico->id }}" data-codigo="{{ $diagnostico->codigo }}" style="border-bottom: 1px solid var(--border-color);">
                        <td class="py-2 px-3 font-monospace" style="border-right: 1px solid var(--border-color);">
                            <span class="badge badge-subtle-primary font-monospace font-weight-bold" style="font-size: 0.8rem;">
                                {{ $diagnostico->codigo }}
                            </span>
                        </td>
                        <td class="py-2 px-3 font-weight-semibold text-uppercase" style="color: var(--text-primary); border-right: 1px solid var(--border-color);">
                            {{ $diagnostico->patologia }}
                        </td>
                        <td class="py-2 px-3 text-center" style="border-right: 1px solid var(--border-color);">
                            <span class="badge badge-subtle-secondary px-2 py-1" style="font-size: 0.75rem; font-weight: 700;">
                                {{ $diagnostico->categoria ?: 'GENERAL' }}
                            </span>
                        </td>

                        <!-- Edad Mínima -->
                        <td class="py-2 px-2 text-center" style="border-right: 1px solid var(--border-color);">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <input type="number" class="form-control form-control-sm input-compact-num edad-minima"
                                    value="{{ $diagnostico->edad_minima ?? 0 }}" min="0" max="150">
                                <select class="form-control form-control-sm input-compact-type tipo-edad-min">
                                    <option value="A" {{ $diagnostico->tipo_edad == 'A' || !$diagnostico->tipo_edad ? 'selected' : '' }}>Años</option>
                                    <option value="M" {{ $diagnostico->tipo_edad == 'M' ? 'selected' : '' }}>Meses</option>
                                    <option value="D" {{ $diagnostico->tipo_edad == 'D' ? 'selected' : '' }}>Días</option>
                                </select>
                            </div>
                        </td>

                        <!-- Edad Máxima -->
                        <td class="py-2 px-2 text-center" style="border-right: 1px solid var(--border-color);">
                            <div class="d-flex align-items-center justify-content-center gap-1">
                                <input type="number" class="form-control form-control-sm input-compact-num edad-maxima"
                                    value="{{ $diagnostico->edad_maxima ?? 150 }}" min="0" max="150">
                                <select class="form-control form-control-sm input-compact-type tipo-edad-max">
                                    <option value="A" {{ $diagnostico->tipo_edad == 'A' || !$diagnostico->tipo_edad ? 'selected' : '' }}>Años</option>
                                    <option value="M" {{ $diagnostico->tipo_edad == 'M' ? 'selected' : '' }}>Meses</option>
                                    <option value="D" {{ $diagnostico->tipo_edad == 'D' ? 'selected' : '' }}>Días</option>
                                </select>
                            </div>
                        </td>

                        <!-- Sexo -->
                        <td class="py-2 px-2 text-center" style="border-right: 1px solid var(--border-color);">
                            <select class="form-control form-control-sm select-compact-sexo sexo-permitido text-uppercase">
                                <option value="ambos" {{ $diagnostico->sexo_permitido == 'ambos' ? 'selected' : '' }}>Ambos</option>
                                <option value="H" {{ $diagnostico->sexo_permitido == 'H' ? 'selected' : '' }}>Masculino</option>
                                <option value="M" {{ $diagnostico->sexo_permitido == 'M' ? 'selected' : '' }}>Femenino</option>
                            </select>
                        </td>

                        <!-- Acciones -->
                        <td class="py-2 px-2 text-center">
                            <div class="d-inline-flex align-items-center justify-content-center gap-1">
                                <button type="button" class="btn btn-icon btn-sm btn-subtle-info btn-condiciones"
                                    data-id="{{ $diagnostico->id }}"
                                    data-codigo="{{ $diagnostico->codigo }}"
                                    data-embarazo="{{ $diagnostico->requiere_embarazo ? '1' : '0' }}"
                                    data-pediatrico="{{ $diagnostico->es_pediatrico ? '1' : '0' }}"
                                    data-adulto="{{ $diagnostico->es_adulto ? '1' : '0' }}"
                                    title="Condiciones Especiales" style="width: 28px; height: 28px; border-radius: var(--radius-sm);">
                                    <i class="bi bi-sliders" style="font-size: 0.85rem;"></i>
                                </button>
                                <button type="button" class="btn btn-icon btn-sm btn-subtle-warning btn-notas"
                                    data-id="{{ $diagnostico->id }}"
                                    data-codigo="{{ $diagnostico->codigo }}"
                                    data-notas="{{ $diagnostico->notas_validacion }}"
                                    title="Notas" style="width: 28px; height: 28px; border-radius: var(--radius-sm);">
                                    <i class="bi bi-sticky" style="font-size: 0.85rem;"></i>
                                </button>
                                <button type="button" class="btn btn-icon btn-sm btn-subtle-success btn-guardar-fila"
                                    title="Guardar Fila" style="width: 28px; height: 28px; border-radius: var(--radius-sm);">
                                    <i class="bi bi-save" style="font-size: 0.85rem;"></i>
                                </button>
                                <button type="button" class="btn btn-icon btn-sm btn-subtle-danger btn-limpiar-fila"
                                    title="Restablecer Fila" style="width: 28px; height: 28px; border-radius: var(--radius-sm);">
                                    <i class="bi bi-arrow-counterclockwise" style="font-size: 0.85rem;"></i>
                                </button>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
$(document).ready(function () {
    $.ajaxSetup({
        headers: { 'X-CSRF-TOKEN': $('meta[name="csrf-token"]').attr('content') }
    });

    // --- FILTROS ---
    function aplicarFiltros() {
        const busqueda = $('#buscar-diagnostico').val().toLowerCase();
        const categoria = $('#filtro-categoria').val();
        const validaciones = $('#filtro-validaciones').val();
        let visibles = 0;

        $('#tbody-condicionamientos tr').each(function () {
            const fila = $(this);
            const codigo = fila.find('td:eq(0)').text().toLowerCase();
            const diagnostico = fila.find('td:eq(1)').text().toLowerCase();
            const catFila = fila.find('td:eq(2)').text().trim();

            let mostrar = true;

            // Búsqueda de texto
            if (busqueda && !codigo.includes(busqueda) && !diagnostico.includes(busqueda)) {
                mostrar = false;
            }

            // Categoría
            if (mostrar && categoria && catFila !== categoria) {
                mostrar = false;
            }

            // Validaciones
            if (mostrar && validaciones) {
                const eMin = fila.find('.edad-minima').val();
                const eMax = fila.find('.edad-maxima').val();
                const sexo = fila.find('.sexo-permitido').val();
                const btnCond = fila.find('.btn-condiciones');
                const embarazo = btnCond.data('embarazo') == '1';
                const pediatrico = btnCond.data('pediatrico') == '1';
                const adulto = btnCond.data('adulto') == '1';

                const tieneVal = (eMin != 0 || eMax != 150 || sexo !== 'ambos' || embarazo || pediatrico || adulto);

                if (validaciones === 'con' && !tieneVal) mostrar = false;
                if (validaciones === 'sin' && tieneVal) mostrar = false;
            }

            fila.toggle(mostrar);
            if (mostrar) visibles++;
        });
        $('#contador-visibles').text(`${visibles} Diagnósticos`);
    }

    $('#buscar-diagnostico').on('input', aplicarFiltros);
    $('#filtro-categoria, #filtro-validaciones').on('change', aplicarFiltros);

    // --- SINCRONIZAR TIPOS DE EDAD ---
    $(document).on('change', '.tipo-edad-min, .tipo-edad-max', function () {
        const fila = $(this).closest('tr');
        const val = $(this).val();
        fila.find('.tipo-edad-min, .tipo-edad-max').val(val);
    });

    // --- MODALES (SWAL) ---
    $(document).on('click', '.btn-condiciones', function () {
        const btn = $(this);
        const id = btn.data('id');
        const codigo = btn.data('codigo');
        const embarazo = btn.data('embarazo') == '1';
        const pediatrico = btn.data('pediatrico') == '1';
        const adulto = btn.data('adulto') == '1';

        Swal.fire({
            title: `Condiciones Especiales - ${codigo}`,
            html: `
                <div style="text-align: left; padding: 10px;">
                    <div class="p-2 mb-2 rounded" style="background: var(--bg-subtle); border: 1px solid var(--border-color);">
                        <div class="form-check">
                            <input type="checkbox" id="modal-embarazo" class="form-check-input" ${embarazo ? 'checked' : ''}>
                            <label for="modal-embarazo" class="form-check-label font-weight-semibold" style="color: var(--text-primary);">Requiere Embarazo</label>
                        </div>
                    </div>
                    <div class="p-2 mb-2 rounded" style="background: var(--bg-subtle); border: 1px solid var(--border-color);">
                        <div class="form-check">
                            <input type="checkbox" id="modal-pediatrico" class="form-check-input" ${pediatrico ? 'checked' : ''}>
                            <label for="modal-pediatrico" class="form-check-label font-weight-semibold" style="color: var(--text-primary);">Es Pediátrico</label>
                        </div>
                    </div>
                    <div class="p-2 mb-2 rounded" style="background: var(--bg-subtle); border: 1px solid var(--border-color);">
                        <div class="form-check">
                            <input type="checkbox" id="modal-adulto" class="form-check-input" ${adulto ? 'checked' : ''}>
                            <label for="modal-adulto" class="form-check-label font-weight-semibold" style="color: var(--text-primary);">Es Adulto Mayor</label>
                        </div>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#4d7cfe',
            preConfirm: () => ({
                requiere_embarazo: $('#modal-embarazo').is(':checked'),
                es_pediatrico: $('#modal-pediatrico').is(':checked'),
                es_adulto: $('#modal-adulto').is(':checked')
            })
        }).then((result) => {
            if (result.isConfirmed) {
                btn.data('embarazo', result.value.requiere_embarazo ? '1' : '0');
                btn.data('pediatrico', result.value.es_pediatrico ? '1' : '0');
                btn.data('adulto', result.value.es_adulto ? '1' : '0');
                guardarFila(btn.closest('tr'));
            }
        });
    });

    $(document).on('click', '.btn-notas', function () {
        const btn = $(this);
        const id = btn.data('id');
        const codigo = btn.data('codigo');
        const notas = btn.data('notas') || '';

        Swal.fire({
            title: `Notas de Validación - ${codigo}`,
            input: 'textarea',
            inputValue: notas,
            inputPlaceholder: 'Escriba notas o excepciones...',
            showCancelButton: true,
            confirmButtonText: 'Guardar',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#4d7cfe',
        }).then((result) => {
            if (result.isConfirmed) {
                btn.data('notas', result.value);
                guardarFila(btn.closest('tr'));
            }
        });
    });

    // --- GUARDADO ---
    function obtenerDatosFila(fila) {
        const btnCond = fila.find('.btn-condiciones');
        const btnNotas = fila.find('.btn-notas');
        return {
            id: fila.data('id'),
            edad_minima: fila.find('.edad-minima').val(),
            edad_maxima: fila.find('.edad-maxima').val(),
            tipo_edad: fila.find('.tipo-edad-min').val(),
            sexo_permitido: fila.find('.sexo-permitido').val(),
            requiere_embarazo: btnCond.data('embarazo') == '1',
            es_pediatrico: btnCond.data('pediatrico') == '1',
            es_adulto: btnCond.data('adulto') == '1',
            notas_validacion: btnNotas.data('notas') || ''
        };
    }

    function guardarFila(fila) {
        const datos = obtenerDatosFila(fila);
        const id = datos.id;

        $.ajax({
            url: '{{ url("diagnosticos") }}/' + id + '/condicionamiento',
            method: 'PUT',
            data: datos,
            success: function () {
                fila.css('background-color', 'rgba(34, 197, 94, 0.15)');
                setTimeout(() => fila.css('background-color', ''), 1200);
            }
        });
    }

    $(document).on('click', '.btn-guardar-fila', function () {
        guardarFila($(this).closest('tr'));
    });

    $(document).on('click', '.btn-limpiar-fila', function () {
        const fila = $(this).closest('tr');
        Swal.fire({
            title: '¿Restablecer Fila?',
            text: "Se resetearán los valores a los predeterminados.",
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            confirmButtonText: 'Sí, restablecer',
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                fila.find('.edad-minima').val(0);
                fila.find('.edad-maxima').val(150);
                fila.find('.tipo-edad-min').val('A');
                fila.find('.tipo-edad-max').val('A');
                fila.find('.sexo-permitido').val('ambos');
                fila.find('.btn-condiciones').data('embarazo', '0').data('pediatrico', '0').data('adulto', '0');
                fila.find('.btn-notas').data('notas', '');
                guardarFila(fila);
            }
        });
    });

    $('#btn-guardar-todos').click(function () {
        const cambios = [];
        $('#tbody-condicionamientos tr').each(function () {
            cambios.push(obtenerDatosFila($(this)));
        });

        Swal.fire({
            title: 'Guardando todas las reglas...',
            didOpen: () => Swal.showLoading(),
            allowOutsideClick: false
        });

        $.ajax({
            url: '{{ route("diagnosticos.condicionamientos.batch") }}',
            method: 'POST',
            contentType: 'application/json',
            data: JSON.stringify({ condicionamientos: cambios }),
            success: function (response) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Guardado con Éxito!',
                    text: `${response.guardados} diagnósticos actualizados.`,
                    timer: 2000,
                    showConfirmButton: false
                });
            },
            error: function (xhr) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error al Guardar',
                    text: xhr.responseJSON?.message || 'Error al guardar los cambios masivos.',
                    confirmButtonColor: '#4d7cfe'
                });
            }
        });
    });

    aplicarFiltros();
});
</script>
@endpush