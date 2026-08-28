@extends('layouts.app')

@section('title', 'Base de Datos de Pacientes - Estadísticas 1.7')

@push('styles')
<style>
    .pacientes-toolbar {
        background-color: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        padding: 0.85rem 1.1rem;
        box-shadow: var(--shadow-sm);
        margin-bottom: 0.85rem;
    }
    .pacientes-search-input {
        background-color: var(--input-bg);
        color: var(--text-primary);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md);
        font-size: 0.85rem;
        padding-left: 2.25rem;
        transition: all var(--transition-fast);
    }
    .pacientes-search-input:focus {
        border-color: var(--color-primary);
        box-shadow: 0 0 0 3px rgba(77, 124, 254, 0.18);
        background-color: var(--bg-surface);
    }
    .pacientes-action-btn {
        font-weight: 600;
        font-size: 0.8rem;
        display: inline-flex;
        align-items: center;
        gap: 0.35rem;
        padding: 0.35rem 0.75rem;
        border-radius: var(--radius-sm);
        transition: all var(--transition-fast);
    }
    .modal-backdrop-custom {
        position: fixed;
        inset: 0;
        z-index: 1050;
        background-color: var(--modal-backdrop-bg, rgba(3, 7, 18, 0.8));
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
        background-color: var(--modal-bg, var(--bg-surface-elevated, #1e293b));
        border: 1px solid var(--modal-border, rgba(255, 255, 255, 0.22));
        border-radius: var(--radius-lg);
        width: 100%;
        max-width: 650px;
        max-height: 90vh;
        box-shadow: var(--modal-shadow, 0 30px 60px -12px rgba(0, 0, 0, 0.85));
        overflow: hidden;
        display: flex;
        flex-direction: column;
    }
</style>
@endpush

@section('content')
<div class="informe-page-wrapper">
    <!-- Header -->
    <div class="informe-header d-flex flex-wrap align-items-center justify-content-between gap-3 py-2 px-3 mb-2" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md, 10px);">
        <div class="d-flex align-items-center gap-3">
            <div class="d-inline-flex align-items-center justify-content-center" style="width: 38px; height: 38px; border-radius: var(--radius-md); background: rgba(77, 124, 254, 0.12); color: var(--color-primary); flex-shrink: 0;">
                <i class="bi bi-person-lines-fill" style="font-size: 1.2rem;"></i>
            </div>
            <div>
                <h2 class="mb-0 d-flex align-items-center gap-2" style="font-size: 1.05rem; font-weight: 800; color: var(--text-primary);">
                    Base de Datos de Pacientes
                    <span id="badge-total-pacientes" class="badge badge-subtle-primary" style="font-size: 0.75rem; font-weight: 700;">
                        {{ number_format($totalPacientes) }} Pacientes
                    </span>
                </h2>
                <p class="mb-0" style="font-size: 0.72rem; color: var(--text-muted);">Registro local sincronizado automáticamente desde SNVS/SESAL y consultas médicas</p>
            </div>
        </div>
    </div>

    <!-- Barra de Herramientas en Una Sola Fila Horizontal Estricta -->
    <div class="pacientes-toolbar no-print mb-2"
        style="background: var(--bg-surface) !important; border: 1px solid var(--border-color) !important; border-radius: var(--radius-md, 10px) !important; padding: 0.45rem 0.85rem !important; display: flex !important; flex-direction: row !important; align-items: center !important; justify-content: space-between !important; flex-wrap: nowrap !important; gap: 8px !important; box-shadow: var(--shadow-sm) !important; width: 100% !important; overflow-x: auto !important;">
        
        <!-- Izquierda: Buscador General + Consultar DNI a la par -->
        <div style="display: flex !important; flex-direction: row !important; align-items: center !important; flex-wrap: nowrap !important; gap: 8px !important; flex-shrink: 0 !important;">
            
            <!-- Buscador General -->
            <div style="width: 250px; min-width: 190px;">
                <form id="search-pacientes-form" action="{{ route('pacientes.index') }}" method="GET" class="position-relative mb-0">
                    <i class="bi bi-search position-absolute" style="left: 10px; top: 50%; transform: translateY(-50%); color: var(--text-muted); font-size: 0.8rem;"></i>
                    <input type="text" id="input-search-pacientes" name="search" value="{{ $search }}"
                        placeholder="Buscar por DNI, Nombre o Colonia..."
                        class="form-control form-control-sm pacientes-search-input" style="height: 34px; font-size: 0.8rem; padding-left: 1.9rem; background-color: var(--input-bg); border-color: var(--border-color); color: var(--text-primary); border-radius: var(--radius-sm, 6px);">
                    @if($search)
                        <a href="{{ route('pacientes.index') }}"
                            class="position-absolute" style="right: 10px; top: 50%; transform: translateY(-50%); color: var(--text-muted);"
                            title="Limpiar búsqueda">
                            <i class="bi bi-x-circle-fill"></i>
                        </a>
                    @endif
                </form>
            </div>

            <!-- Separador -->
            <div style="height: 22px; width: 1px; background: var(--border-color); flex-shrink: 0;"></div>

            <!-- Input DNI + Botón Consultar a la par -->
            <div style="display: flex !important; flex-direction: row !important; align-items: center !important; flex-wrap: nowrap !important; gap: 6px !important; width: 340px !important; min-width: 320px !important;">
                <div class="position-relative flex-grow-1" style="min-width: 0;">
                    <i class="bi bi-person-badge position-absolute text-warning" style="left: 9px; top: 50%; transform: translateY(-50%); font-size: 0.85rem;"></i>
                    <input type="text" id="input-nuevo-dni" maxlength="19"
                        placeholder="DNI (0801-1990-00000)..."
                        class="form-control form-control-sm font-monospace"
                        style="padding-left: 1.8rem; height: 34px; background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color); font-size: 0.78rem; border-radius: var(--radius-sm, 6px); width: 100%;">
                </div>
                <button type="button" id="btn-buscar-nuevo" class="btn btn-warning btn-sm font-weight-bold" style="height: 34px; padding: 0 0.85rem; font-size: 0.76rem; display: inline-flex; align-items: center; gap: 0.3rem; white-space: nowrap; border-radius: var(--radius-sm, 6px); flex-shrink: 0;" title="Buscar este DNI en SESAL/SNVS y agregarlo">
                    <i class="bi bi-cloud-arrow-down-fill"></i>
                    <span>Consultar</span>
                </button>
            </div>

            <!-- Separador -->
            <div style="height: 22px; width: 1px; background: var(--border-color); flex-shrink: 0;"></div>

            <!-- Mensaje de estado -->
            <span id="status-message" class="badge badge-subtle-info px-2 py-1 d-none" style="font-size: 0.75rem; white-space: nowrap;"></span>
        </div>

        <!-- Derecha: Acciones Masivas en la misma fila -->
        <div style="display: flex !important; flex-direction: row !important; align-items: center !important; flex-wrap: nowrap !important; gap: 6px !important; flex-shrink: 0 !important; margin-left: auto !important;">
            <button type="button" id="btn-recargar-tabla" class="btn btn-subtle btn-sm pacientes-action-btn" style="height: 34px; padding: 0 0.65rem; font-size: 0.76rem; border-radius: var(--radius-sm, 6px);" title="Recargar la tabla con los datos locales actuales">
                <i class="bi bi-arrow-clockwise"></i>
                <span>Recargar</span>
            </button>

            <button type="button" id="btn-recalcular-edades" class="btn btn-subtle-info btn-sm pacientes-action-btn" style="height: 34px; padding: 0 0.65rem; font-size: 0.76rem; border-radius: var(--radius-sm, 6px);" title="Recalcular todas las edades a partir de la fecha de nacimiento">
                <i class="bi bi-calculator"></i>
                <span>Recalc. Edades</span>
            </button>

            <button type="button" id="btn-resync-pagina" class="btn btn-subtle-primary btn-sm pacientes-action-btn" style="height: 34px; padding: 0 0.65rem; font-size: 0.76rem; border-radius: var(--radius-sm, 6px);" title="Volver a consultar los 50 pacientes de la página actual en SESAL/SNVS">
                <i class="bi bi-arrow-repeat"></i>
                <span>Resync Página</span>
            </button>

            <button type="button" id="btn-resync-todos" class="btn btn-subtle-danger btn-sm pacientes-action-btn" style="height: 34px; padding: 0 0.65rem; font-size: 0.76rem; border-radius: var(--radius-sm, 6px);" title="ATENCIÓN: Volver a consultar TODOS los pacientes registrados en SESAL/SNVS">
                <i class="bi bi-cloud-arrow-down-fill"></i>
                <span>Resync Todos</span>
            </button>
        </div>
    </div>

    <!-- Contenedor principal de la tabla adaptativo -->
    <div id="pacientes-table-container" class="flex-grow-1 position-relative d-flex flex-column overflow-hidden" style="min-height: 400px;">
        @include('pacientes.partials.table')
    </div>
</div>

<!-- Modal Preview / Confirmación para "Consultar / Sincronizar paciente por DNI" -->
<div id="modal-preview-paciente" class="modal-backdrop-custom">
    <div class="modal-dialog-custom">
        <!-- Header -->
        <div id="modal-preview-header" class="d-flex align-items-center justify-content-between p-3" style="background-color: var(--color-primary); color: #fff;">
            <div class="d-flex align-items-center gap-2">
                <i id="modal-preview-icon" class="bi bi-person-plus-fill" style="font-size: 1.25rem;"></i>
                <div>
                    <h5 id="modal-preview-title" class="mb-0 font-weight-bold" style="font-size: 0.95rem;">Confirmar acción</h5>
                    <small id="modal-preview-subtitle" style="opacity: 0.85; font-size: 0.75rem;">Revisa los datos y selecciona los campos a actualizar</small>
                </div>
            </div>
            <button type="button" id="modal-preview-close" class="btn btn-icon btn-sm" style="color: #fff; background: rgba(255,255,255,0.15);" title="Cerrar">
                <i class="bi bi-x-lg"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="p-3 overflow-auto flex-grow-1" style="max-height: calc(90vh - 120px);">
            <!-- Badge de Estado + Fuentes -->
            <div class="d-flex align-items-center justify-content-between mb-2">
                <div id="modal-preview-badge"></div>
                <small id="modal-preview-fuentes-info" style="color: var(--text-muted); font-size: 0.75rem;"></small>
            </div>
            <p id="modal-preview-message" class="mb-3 font-weight-medium" style="font-size: 0.82rem; color: var(--text-primary);"></p>

            <!-- Barra de selección de todos los checkboxes -->
            <div class="d-flex align-items-center justify-content-between p-2 rounded mb-2" style="background-color: var(--bg-subtle); border: 1px solid var(--border-color); font-size: 0.78rem;">
                <div class="d-flex align-items-center gap-1 font-weight-bold" style="color: var(--text-secondary);">
                    <i class="bi bi-check2-square text-primary"></i>
                    <span>Selecciona los campos a sobreescribir/guardar:</span>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <button type="button" id="btn-modal-select-all" class="btn btn-link p-0 text-primary font-weight-bold" style="font-size: 0.75rem; text-decoration: none;">
                        Seleccionar todos
                    </button>
                    <span style="color: var(--text-muted);">|</span>
                    <button type="button" id="btn-modal-deselect-all" class="btn btn-link p-0 text-muted font-weight-bold" style="font-size: 0.75rem; text-decoration: none;">
                        Deseleccionar todos
                    </button>
                </div>
            </div>

            <!-- Tabla de Campos con Checkboxes -->
            <div class="table-responsive rounded border" style="border-color: var(--border-color) !important; background-color: var(--bg-surface);">
                <table class="table table-sm table-sing mb-0" style="font-size: 0.8rem;">
                    <thead style="background-color: var(--bg-subtle);">
                        <tr>
                            <th class="py-2 px-2 text-center" style="width: 38px;">
                                <i class="bi bi-check-all"></i>
                            </th>
                            <th class="py-2 px-2" style="width: 140px; color: var(--text-muted);">Campo</th>
                            <th class="py-2 px-2" style="color: var(--text-muted);">Valor Actual (BD Local)</th>
                            <th class="py-2 px-2" style="color: var(--text-muted);">Valor Nuevo (SESAL)</th>
                            <th class="py-2 px-2 text-right" style="color: var(--text-muted);">Estado</th>
                        </tr>
                    </thead>
                    <tbody id="modal-preview-fields-tbody">
                    </tbody>
                </table>
            </div>

            <!-- Mensaje de sin cambios -->
            <div id="modal-preview-nochg" class="alert alert-success mt-2 py-2 px-3 d-none mb-0" style="font-size: 0.8rem;">
                <i class="bi bi-check-circle-fill mr-1"></i>
                <span id="modal-preview-nochg-msg">Los datos locales ya están actualizados con SESAL.</span>
            </div>
        </div>

        <!-- Footer -->
        <div class="d-flex align-items-center justify-content-end gap-2 p-3 border-top" style="background-color: var(--bg-subtle); border-color: var(--border-color) !important;">
            <button type="button" id="modal-preview-btn-cancel" class="btn btn-secondary btn-sm" style="font-weight: 600;">
                Cancelar
            </button>
            <button type="button" id="modal-preview-btn-confirm" class="btn btn-primary btn-sm" style="font-weight: 600;">
                <i id="modal-preview-btn-icon" class="bi bi-save mr-1"></i>
                <span id="modal-preview-btn-text">Guardar</span>
            </button>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const PACIENTES_UPDATE_URL = "{{ route('pacientes.update_field', ['id' => '__ID__']) }}";
    const PACIENTES_BUSCAR_NUEVO_URL = "{{ route('pacientes.buscar_nuevo') }}";
    const PACIENTES_RESYNC_MASIVO_URL = "{{ route('pacientes.resync_masivo') }}";
    const PACIENTES_RECALC_EDADES_URL = "{{ route('pacientes.recalcular_edades') }}";
    const PACIENTES_INDEX_URL = "{{ route('pacientes.index') }}";
    const CSRF_TOKEN = "{{ csrf_token() }}";

    function mostrarStatus(mensaje, tipo = 'info') {
        const $el = $('#status-message');
        $el.removeClass('d-none badge-subtle-primary badge-subtle-success badge-subtle-warning badge-subtle-danger badge-subtle-info');
        const badgeClasses = {
            info: 'badge-subtle-primary',
            success: 'badge-subtle-success',
            warn: 'badge-subtle-warning',
            error: 'badge-subtle-danger',
            loading: 'badge-subtle-info',
            calc: 'badge-subtle-info'
        };
        $el.addClass(badgeClasses[tipo] || 'badge-subtle-primary').text(mensaje).removeClass('d-none');
    }
    function ocultarStatus() { $('#status-message').addClass('d-none'); }

    function recargarTabla(appendSearch = true) {
        const $container = $('#pacientes-table-container');
        $container.css('opacity', '0.5').css('pointer-events', 'none');
        const data = {};
        if (appendSearch) {
            const s = $('#input-search-pacientes').val();
            if (s) data.search = s;
        }
        $.ajax({
            url: PACIENTES_INDEX_URL,
            type: 'GET',
            data: data,
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function (html) {
                $container.html(html).css('opacity', '1').css('pointer-events', 'auto');
                bindInlineEdit();
                bindResyncRowButtons();
                bindRecalcEdadButtons();
                bindGuardarFilaButtons();
            },
            error: function () {
                $container.css('opacity', '1').css('pointer-events', 'auto');
                mostrarStatus('Error al recargar la tabla.', 'error');
                setTimeout(ocultarStatus, 4000);
            }
        });
    }

    function formatearDniInput(el) {
        let value = el.value.replace(/\D/g, '');
        let formatted = '';
        if (value.length > 0) formatted = value.substring(0, 4);
        if (value.length >= 5) formatted += '-' + value.substring(4, 8);
        if (value.length >= 9) formatted += '-' + value.substring(8, 13);
        el.value = formatted;
    }

    $(document).ready(function () {
        // ── Búsqueda en tiempo real ─────────────────────────────────────
        let searchTimeout = null;
        $('#input-search-pacientes').on('input', function () {
            clearTimeout(searchTimeout);
            let val = $(this).val();
            searchTimeout = setTimeout(function () {
                $('#pacientes-table-container').css('opacity', '0.5').css('pointer-events', 'none');
                $.ajax({
                    url: PACIENTES_INDEX_URL,
                    type: 'GET',
                    data: { search: val },
                    headers: { 'X-Requested-With': 'XMLHttpRequest' },
                    success: function (html) {
                        $('#pacientes-table-container').html(html).css('opacity', '1').css('pointer-events', 'auto');
                        bindInlineEdit();
                        bindResyncRowButtons();
                        bindRecalcEdadButtons();
                        bindGuardarFilaButtons();
                    },
                    error: function () {
                        $('#pacientes-table-container').css('opacity', '1').css('pointer-events', 'auto');
                    }
                });
            }, 300);
        });

        // ── Edición inline y botones ───────────────────────────────────
        bindInlineEdit();
        bindResyncRowButtons();
        bindRecalcEdadButtons();
        bindGuardarFilaButtons();

        // ── Formateo de DNI en input nuevo ──────────────────────────────
        $('#input-nuevo-dni').on('input', function () { formatearDniInput(this); });
        $('#input-nuevo-dni').on('keydown', function (e) { if (e.key === 'Enter') { e.preventDefault(); $('#btn-buscar-nuevo').click(); } });

        // ── Consultar / Agregar paciente por DNI ─────
        let ultimoDniConsultado = '';
        let ultimoIdPacienteResync = null;

        const CAMPOS_CONFIG = [
            { key: 'nombre_completo', label: 'Nombre Completo', icon: 'bi-person' },
            { key: 'fecha_nacimiento', label: 'Fecha Nacimiento', icon: 'bi-calendar-event' },
            { key: 'edad', label: 'Edad', icon: 'bi-hourglass-split' },
            { key: 'telefono', label: 'Teléfono', icon: 'bi-telephone' },
            { key: 'colonia', label: 'Colonia', icon: 'bi-geo-alt' },
            { key: 'departamento', label: 'Departamento', icon: 'bi-map' },
            { key: 'municipio', label: 'Municipio', icon: 'bi-building' },
            { key: 'cod_municipio', label: 'Cód. Municipio', icon: 'bi-upc-scan' }
        ];

        $('#btn-buscar-nuevo').on('click', function () {
            const dni = $('#input-nuevo-dni').val().trim();
            if (!dni || dni.replace(/\D/g, '').length < 5) {
                mostrarStatus('Ingrese un DNI válido (mín. 5 dígitos).', 'warn');
                setTimeout(ocultarStatus, 3500);
                return;
            }
            const $btn = $(this);
            $btn.prop('disabled', true);
            $btn.find('i').removeClass('bi-search').addClass('spinner-border spinner-border-sm');
            mostrarStatus('Consultando SESAL/SNVS...', 'loading');

            $.ajax({
                url: PACIENTES_BUSCAR_NUEVO_URL,
                type: 'POST',
                data: { _token: CSRF_TOKEN, dni: dni, modo: 'preview' },
                success: function (res) {
                    if (res && res.success) {
                        if (res.modo === 'preview') {
                            ultimoDniConsultado = dni;
                            ultimoIdPacienteResync = null;
                            abrirModalPreview(res);
                        } else {
                            postGuardarManejar(res, dni);
                        }
                    } else {
                        mostrarStatus(res?.message || 'No se encontraron datos.', 'warn');
                    }
                },
                error: function (xhr) {
                    const msg = xhr.responseJSON?.message || 'Error al consultar.';
                    mostrarStatus(msg, 'error');
                },
                complete: function () {
                    $btn.prop('disabled', false);
                    $btn.find('i').removeClass('spinner-border spinner-border-sm').addClass('bi-search');
                    setTimeout(ocultarStatus, 5500);
                }
            });
        });

        // ── Modal Preview / Confirmar con Checkboxes por campo ───────────
        function abrirModalPreview(res) {
            const $modal = $('#modal-preview-paciente');
            const $badge = $('#modal-preview-badge');
            const $msg = $('#modal-preview-message');
            const $nochg = $('#modal-preview-nochg');
            const $tbody = $('#modal-preview-fields-tbody');
            const $btnConfirm = $('#modal-preview-btn-confirm');
            const $btnIcon = $('#modal-preview-btn-icon');
            const $btnText = $('#modal-preview-btn-text');
            const $header = $('#modal-preview-header');
            const $iconTitle = $('#modal-preview-icon');
            const $title = $('#modal-preview-title');
            const $subtitle = $('#modal-preview-subtitle');
            const $fuentes = $('#modal-preview-fuentes-info');

            $nochg.addClass('d-none');

            if (res.paciente_id) {
                ultimoIdPacienteResync = res.paciente_id;
            } else if (res.existente && res.existente.id) {
                ultimoIdPacienteResync = res.existente.id;
            }

            if (res.fallback_fuentes && res.fallback_fuentes.length > 0) {
                $fuentes.text('Fuentes locales: ' + res.fallback_fuentes.join(', '));
            } else {
                $fuentes.text('');
            }

            if (res.es_nuevo) {
                $badge.html('<span class="badge badge-subtle-primary px-2 py-1"><i class="bi bi-person-plus mr-1"></i> PACIENTE NUEVO</span>');
                $title.text('Confirmar nuevo paciente');
                $subtitle.text('Selecciona los datos a registrar en la base de datos local');
                $iconTitle.attr('class', 'bi bi-person-plus-fill');
                $btnIcon.attr('class', 'bi bi-person-plus mr-1');
                $btnText.text('Agregar Paciente');
            } else {
                $badge.html('<span class="badge badge-subtle-success px-2 py-1"><i class="bi bi-check-circle mr-1"></i> PACIENTE REGISTRADO</span>');
                $title.text('Confirmar actualización de paciente');
                $subtitle.text('Combina los datos locales con los datos nuevos de SESAL');
                $iconTitle.attr('class', 'bi bi-arrow-repeat');
                $btnIcon.attr('class', 'bi bi-arrow-repeat mr-1');
                $btnText.text('Actualizar Paciente');
            }

            $msg.text(res.message || '');

            let htmlRows = '';
            let hayDiferencias = false;

            CAMPOS_CONFIG.forEach(item => {
                const k = item.key;
                const valLocRaw = res.existente ? res.existente[k] : null;
                const valNueRaw = res.preview_data ? res.preview_data[k] : null;

                const valLocStr = (valLocRaw !== null && valLocRaw !== undefined) ? String(valLocRaw).trim() : '';
                const valNueStr = (valNueRaw !== null && valNueRaw !== undefined) ? String(valNueRaw).trim() : '';

                const tieneLoc = valLocStr !== '' && valLocStr !== '-' && valLocStr.toUpperCase() !== 'NULL';
                const tieneNue = valNueStr !== '' && valNueStr !== '-' && valNueStr.toUpperCase() !== 'NULL';

                let dispLoc = tieneLoc ? valLocStr : '<span style="color: var(--text-muted); font-style: italic;">-- Vacío --</span>';
                let dispNue = tieneNue ? valNueStr : '<span style="color: var(--text-muted); font-style: italic;">-- Vacío --</span>';

                if (k === 'edad') {
                    if (tieneLoc) dispLoc = valLocStr + ' Años';
                    if (tieneNue) dispNue = valNueStr + ' Años';
                }

                let isChecked = false;
                let badgeHtml = '';

                if (res.es_nuevo) {
                    isChecked = tieneNue;
                    if (tieneNue) {
                        badgeHtml = '<span class="badge badge-subtle-primary">Dato nuevo</span>';
                    } else {
                        badgeHtml = '<span class="badge badge-subtle-secondary">Sin dato</span>';
                    }
                } else {
                    if (tieneNue && !tieneLoc) {
                        isChecked = true;
                        hayDiferencias = true;
                        badgeHtml = '<span class="badge badge-subtle-success">Completar</span>';
                    } else if (tieneNue && tieneLoc && valLocStr.toUpperCase() !== valNueStr.toUpperCase()) {
                        isChecked = true;
                        hayDiferencias = true;
                        badgeHtml = '<span class="badge badge-subtle-warning">Actualizar</span>';
                    } else if (!tieneNue && tieneLoc) {
                        isChecked = false;
                        badgeHtml = '<span class="badge badge-subtle-info">Mantener local</span>';
                    } else if (tieneNue && tieneLoc && valLocStr.toUpperCase() === valNueStr.toUpperCase()) {
                        isChecked = true;
                        badgeHtml = '<span class="badge badge-subtle-secondary">Sin cambios</span>';
                    } else {
                        isChecked = false;
                        badgeHtml = '<span class="badge badge-subtle-secondary">Sin dato</span>';
                    }
                }

                htmlRows += `
                    <tr>
                        <td class="py-2 px-2 text-center">
                            <input type="checkbox"
                                   class="chk-campo-preview"
                                   value="${k}"
                                   ${isChecked ? 'checked' : ''} style="cursor: pointer;">
                        </td>
                        <td class="py-2 px-2 font-weight-bold" style="color: var(--text-primary);">
                            <i class="bi ${item.icon} mr-1 text-muted"></i>
                            <span>${item.label}</span>
                        </td>
                        <td class="py-2 px-2" style="color: var(--text-secondary);">${dispLoc}</td>
                        <td class="py-2 px-2 font-weight-bold" style="color: var(--color-primary);">${dispNue}</td>
                        <td class="py-2 px-2 text-right">${badgeHtml}</td>
                    </tr>
                `;
            });

            $tbody.html(htmlRows);

            if (!res.es_nuevo && !hayDiferencias) {
                $nochg.removeClass('d-none');
            }

            $modal.addClass('active');
        }

        // Seleccionar / Deseleccionar todos
        $(document).on('click', '#btn-modal-select-all', function () {
            $('.chk-campo-preview').prop('checked', true);
        });
        $(document).on('click', '#btn-modal-deselect-all', function () {
            $('.chk-campo-preview').prop('checked', false);
        });

        function cerrarModalPreview() {
            $('#modal-preview-paciente').removeClass('active');
        }

        $(document).off('click.previewPac').on('click.previewPac', '#modal-preview-close, #modal-preview-btn-cancel', function () {
            cerrarModalPreview();
        });
        $(document).off('keydown.previewPac').on('keydown.previewPac', function (e) {
            if (e.key === 'Escape' && $('#modal-preview-paciente').hasClass('active')) cerrarModalPreview();
        });

        // Botón Confirmar Modal
        $(document).off('click.previewConfirm').on('click.previewConfirm', '#modal-preview-btn-confirm', function () {
            const $btn = $(this);
            if ($btn.prop('disabled')) return;

            const camposSeleccionados = [];
            $('.chk-campo-preview:checked').each(function () {
                camposSeleccionados.push($(this).val());
            });

            $btn.prop('disabled', true);
            const prevHtml = $btn.html();
            $btn.html('<i class="spinner-border spinner-border-sm mr-1"></i> Procesando...');

            if (ultimoIdPacienteResync) {
                const resyncUrl = "{{ route('pacientes.resync', ['id' => '__ID__']) }}".replace('__ID__', ultimoIdPacienteResync);
                $.ajax({
                    url: resyncUrl,
                    type: 'POST',
                    data: {
                        _token: CSRF_TOKEN,
                        modo: 'confirmar',
                        campos: camposSeleccionados
                    },
                    success: function (res) {
                        if (res && res.success) {
                            postGuardarManejar(res, res.paciente?.dni || ultimoDniConsultado);
                            cerrarModalPreview();
                        } else {
                            mostrarStatus(res?.message || 'Error al actualizar.', 'error');
                            alert(res?.message || 'Error al actualizar.');
                        }
                    },
                    error: function (xhr) {
                        const msg = xhr.responseJSON?.message || 'Error al actualizar.';
                        mostrarStatus(msg, 'error');
                        alert(msg);
                    },
                    complete: function () {
                        $btn.prop('disabled', false);
                        $btn.html(prevHtml);
                    }
                });
            } else {
                $.ajax({
                    url: PACIENTES_BUSCAR_NUEVO_URL,
                    type: 'POST',
                    data: {
                        _token: CSRF_TOKEN,
                        dni: ultimoDniConsultado,
                        modo: 'confirmar',
                        campos: camposSeleccionados
                    },
                    success: function (res) {
                        if (res && res.success) {
                            postGuardarManejar(res, ultimoDniConsultado);
                            cerrarModalPreview();
                        } else {
                            mostrarStatus(res?.message || 'Error al guardar.', 'error');
                            alert(res?.message || 'Error al guardar.');
                        }
                    },
                    error: function (xhr) {
                        const msg = xhr.responseJSON?.message || 'Error al guardar.';
                        mostrarStatus(msg, 'error');
                        alert(msg);
                    },
                    complete: function () {
                        $btn.prop('disabled', false);
                        $btn.html(prevHtml);
                    }
                });
            }
        });

        function postGuardarManejar(res, dniUsado) {
            const nuevoTxt = (res.nuevo || res.modo === 'confirmar' && res.nuevo) ? 'Paciente NUEVO agregado. ' : (res.nuevo === false ? 'Paciente actualizado. ' : '');
            mostrarStatus(nuevoTxt + (res.message || 'OK'), 'success');
            const dniLimpio = $('#input-nuevo-dni').val().replace(/\D/g, '');
            const ultLimpio = (dniUsado || '').replace(/\D/g, '');
            if (dniLimpio === ultLimpio) {
                $('#input-nuevo-dni').val('');
            }
            recargarTabla(false);
            $('#input-search-pacientes').val('');
            ultimoDniConsultado = '';
            ultimoIdPacienteResync = null;
        }

        // ── Recargar tabla ──────────────────────────────
        $('#btn-recargar-tabla').on('click', function () {
            mostrarStatus('Recargando tabla...', 'info');
            recargarTabla(true);
            setTimeout(function () {
                mostrarStatus('Tabla recargada con datos locales.', 'success');
                setTimeout(ocultarStatus, 2500);
            }, 600);
        });

        // ── Recalcular edades ───────────────────────────────────────────
        $('#btn-recalcular-edades').on('click', function () {
            const $btn = $(this);
            if ($btn.prop('disabled')) return;
            $btn.prop('disabled', true);
            const oldHtml = $btn.html();
            $btn.html('<i class="spinner-border spinner-border-sm mr-1"></i> Calculando...');
            mostrarStatus('Recalculando edades...', 'calc');

            $.ajax({
                url: PACIENTES_RECALC_EDADES_URL,
                type: 'POST',
                data: { _token: CSRF_TOKEN },
                success: function (res) {
                    if (res && res.success) {
                        mostrarStatus(res.message || 'Edades recalculadas.', 'success');
                        recargarTabla(true);
                    } else {
                        mostrarStatus(res?.message || 'No se pudo recalcular.', 'error');
                    }
                },
                error: function () {
                    mostrarStatus('Error al recalcular edades.', 'error');
                },
                complete: function () {
                    $btn.prop('disabled', false);
                    $btn.html(oldHtml);
                    setTimeout(ocultarStatus, 5000);
                }
            });
        });

        // ── Resync Página ──────────────
        $('#btn-resync-pagina').on('click', function () {
            if (!confirm('¿Volver a consultar en SESAL/SNVS los 50 pacientes de la página actual?')) return;
            ejecutarResyncMasivo({ todos: false, limite: 50 }, 'Página actual');
        });

        // ── Resync TODOS ──────────────────────────────────
        $('#btn-resync-todos').on('click', function () {
            if (!confirm('ATENCIÓN: Esto volverá a consultar TODOS los pacientes en SESAL/SNVS y puede tardar varios minutos.\n\n¿Desea continuar?')) return;
            ejecutarResyncMasivo({ todos: true, limite: 0 }, 'TODOS los pacientes');
        });

        function ejecutarResyncMasivo(payload, label) {
            const $btn = payload.todos ? $('#btn-resync-todos') : $('#btn-resync-pagina');
            if ($btn.prop('disabled')) return;
            $btn.prop('disabled', true);
            const oldHtml = $btn.html();
            $btn.html('<i class="spinner-border spinner-border-sm mr-1"></i> Procesando...');
            mostrarStatus('Resync (' + label + '): consultando SESAL...', 'loading');

            const reqPayload = { _token: CSRF_TOKEN };
            if (payload.todos) reqPayload.todos = 1;
            if (payload.limite > 0) reqPayload.limite = payload.limite;

            $.ajax({
                url: PACIENTES_RESYNC_MASIVO_URL,
                type: 'POST',
                data: reqPayload,
                timeout: 1000 * 60 * 10,
                success: function (res) {
                    if (res && res.success) {
                        mostrarStatus(res.message, 'success');
                        recargarTabla(true);
                    } else {
                        mostrarStatus(res?.message || 'Error en resync masivo.', 'error');
                    }
                },
                error: function (xhr, status) {
                    const msg = status === 'timeout'
                        ? 'Tiempo de espera agotado. Algunos pacientes sí se actualizaron. Recargue la tabla.'
                        : 'Error en resync: ' + (xhr.responseJSON?.message || 'ver LOG.');
                    mostrarStatus(msg, 'error');
                },
                complete: function () {
                    $btn.prop('disabled', false);
                    $btn.html(oldHtml);
                    setTimeout(ocultarStatus, 7000);
                }
            });
        }
    });

    // ──────────────────────────────────────────────────────────────────
    // Bindeo de botones de resync individual
    // ──────────────────────────────────────────────────────────────────
    function bindResyncRowButtons() {
        const CSRF = document.querySelector('meta[name="csrf-token"]')
            ? document.querySelector('meta[name="csrf-token"]').content
            : CSRF_TOKEN;

        document.querySelectorAll('.btn-resync').forEach(function (btn) {
            if (btn.dataset.resyncBound === '1') return;
            btn.dataset.resyncBound = '1';

            btn.addEventListener('click', function (e) {
                const id = btn.dataset.id;
                const url = btn.dataset.url;
                const icono = btn.querySelector('i');
                const resyncDirecto = e.shiftKey;

                btn.disabled = true;
                if (icono) {
                    icono.className = 'spinner-border spinner-border-sm';
                }

                const payload = resyncDirecto ? { modo: 'confirmar' } : { modo: 'preview' };

                fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify(payload)
                })
                    .then(r => r.json())
                    .then(function (res) {
                        btn.disabled = false;
                        if (icono) {
                            icono.className = 'bi bi-arrow-repeat';
                        }

                        if (res.success) {
                            if (res.modo === 'preview') {
                                ultimoIdPacienteResync = id;
                                ultimoDniConsultado = res.existente?.dni || res.preview_data?.dni || '';
                                abrirModalPreview(res);
                            } else {
                                const row = btn.closest('tr');
                                if (res.paciente) actualizarFilaPaciente(row, res.paciente);
                                mostrarStatus(res.message || 'Paciente actualizado.', 'success');
                                setTimeout(ocultarStatus, 3000);
                            }
                        } else {
                            mostrarStatus(res.message || 'No se pudo sincronizar.', 'warn');
                        }
                    })
                    .catch(function () {
                        btn.disabled = false;
                        if (icono) {
                            icono.className = 'bi bi-arrow-repeat';
                        }
                        mostrarStatus('No se pudo conectar con SESAL.', 'warn');
                        setTimeout(ocultarStatus, 6000);
                    });
            });
        });
    }

    // ──────────────────────────────────────────────────────────────────
    // Bindeo de botones de recalcular edad por fila
    // ──────────────────────────────────────────────────────────────────
    function bindRecalcEdadButtons() {
        const CSRF = document.querySelector('meta[name="csrf-token"]')
            ? document.querySelector('meta[name="csrf-token"]').content
            : CSRF_TOKEN;

        document.querySelectorAll('.btn-recalc-edad').forEach(function (btn) {
            if (btn.dataset.recalcBound === '1') return;
            btn.dataset.recalcBound = '1';

            btn.addEventListener('click', function () {
                const url = btn.dataset.url;
                const row = btn.closest('tr');
                const cells = row ? row.querySelectorAll('td') : [];
                const icono = btn.querySelector('i');

                btn.disabled = true;
                if (icono) {
                    icono.className = 'spinner-border spinner-border-sm';
                }

                fetch(url, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': CSRF },
                    body: JSON.stringify({})
                })
                    .then(r => r.json())
                    .then(function (res) {
                        btn.disabled = false;
                        if (icono) {
                            icono.className = 'bi bi-calculator';
                        }

                        if (res.success) {
                            if (res.edad !== undefined && res.edad !== null && cells[5]) {
                                cells[5].innerHTML =
                                    '<span class="badge badge-subtle-success px-2 py-1" style="font-size: 0.78rem; font-weight: 600;">' +
                                    res.edad + ' Años</span>';
                            }
                        }
                    })
                    .catch(function () {
                        btn.disabled = false;
                        if (icono) {
                            icono.className = 'bi bi-calculator';
                        }
                        alert('Error al recalcular edad.');
                    });
            });
        });
    }

    // ──────────────────────────────────────────────────────────────────
    // Bindeo de botones de Guardar edición
    // ──────────────────────────────────────────────────────────────────
    function bindGuardarFilaButtons() {
        document.querySelectorAll('.btn-guardar-fila').forEach(function (btn) {
            if (btn.dataset.guardarBound === '1') return;
            btn.dataset.guardarBound = '1';

            btn.addEventListener('click', function () {
                const id = btn.dataset.id;
                const row = btn.closest('tr');
                if (!row) return;
                const icono = btn.querySelector('i');

                const wrap = row.querySelector('.inline-edit-wrap');
                if (!wrap) return;

                const $wrap = $(wrap);
                const field = $wrap.data('field') || 'telefono';
                let value;

                const $input = $wrap.find('.inline-edit-input');
                const $disp = $wrap.find('.inline-edit-display');

                if ($input && !$input.hasClass('d-none')) {
                    value = $input.val().trim();
                } else {
                    value = ($wrap.data('value') || '').toString().trim();
                }

                btn.disabled = true;
                if (icono) {
                    icono.className = 'spinner-border spinner-border-sm';
                }

                const url = PACIENTES_UPDATE_URL.replace('__ID__', id);

                $.ajax({
                    url: url,
                    type: 'POST',
                    data: {
                        _method: 'PATCH',
                        _token: CSRF_TOKEN,
                        field: field,
                        value: value
                    },
                    success: function (res) {
                        btn.disabled = false;
                        if (icono) {
                            icono.className = 'bi bi-save';
                        }

                        if (res && res.success) {
                            const nuevoValor = res.value || '';
                            $wrap.data('value', nuevoValor);

                            if (field === 'telefono') {
                                if (nuevoValor) {
                                    $disp.html(
                                        '<span style="color: var(--text-primary); font-weight: 600;">' + nuevoValor + '</span>' +
                                        '<i class="bi bi-pencil-square ml-1 text-muted" style="font-size: 0.75rem;"></i>'
                                    );
                                } else {
                                    $disp.html('<span style="color: var(--text-muted); font-style: italic; font-size: 0.75rem;">-- teléfono --</span><i class="bi bi-pencil-square ml-1 text-muted" style="font-size: 0.75rem;"></i>');
                                }
                            }
                            $input.addClass('d-none');
                            $disp.removeClass('d-none');
                        } else {
                            alert(res?.message || 'Error al guardar.');
                        }
                    },
                    error: function () {
                        btn.disabled = false;
                        if (icono) {
                            icono.className = 'bi bi-save';
                        }
                        alert('Error de conexión al guardar.');
                    }
                });
            });
        });
    }

    function actualizarFilaPaciente(row, p) {
        if (!row || !p) return;
        const cells = row.querySelectorAll('td');

        if (p.nombre_completo && cells[3]) {
            cells[3].textContent = p.nombre_completo.toUpperCase ? p.nombre_completo.toUpperCase() : p.nombre_completo;
        }
        if (p.fecha_nacimiento && p.fecha_nacimiento !== '-' && cells[4]) {
            cells[4].textContent = p.fecha_nacimiento;
        }
        if (cells[5]) {
            if (p.edad !== undefined && p.edad !== null && p.edad !== '') {
                cells[5].innerHTML = '<span class="badge badge-subtle-success px-2 py-1" style="font-size: 0.78rem; font-weight: 600;">' + p.edad + ' Años</span>';
            }
        }
        if (cells[6]) {
            const telWrap = row.querySelector('.inline-edit-wrap');
            if (telWrap && p.telefono) {
                telWrap.dataset.value = p.telefono;
                const disp = telWrap.querySelector('.inline-edit-display');
                if (disp) {
                    disp.innerHTML = '<span style="color: var(--text-primary); font-weight: 600;">' + p.telefono + '</span><i class="bi bi-pencil-square ml-1 text-muted" style="font-size: 0.75rem;"></i>';
                }
            }
        }
        if (p.colonia && p.colonia !== '-' && cells[7]) {
            cells[7].textContent = (p.colonia || '').toUpperCase ? (p.colonia || '').toUpperCase() : p.colonia;
        }
    }

    function bindInlineEdit() {
        $(document).off('dblclick.inlineEdit').on('dblclick.inlineEdit', '.inline-edit-wrap .inline-edit-display', function () {
            const $wrap = $(this).closest('.inline-edit-wrap');
            const $disp = $wrap.find('.inline-edit-display');
            const $input = $wrap.find('.inline-edit-input');
            const currentVal = $wrap.data('value') || '';

            $disp.addClass('d-none');
            $input.val(currentVal).removeClass('d-none').focus().select();
        });

        $(document).off('keydown.inlineEdit blur.inlineEdit', '.inline-edit-input')
            .on('keydown.inlineEdit', '.inline-edit-input', function (e) {
                if (e.key === 'Enter') { e.preventDefault(); saveInlineField($(this)); }
                if (e.key === 'Escape') { cancelInlineEdit($(this)); }
            })
            .on('blur.inlineEdit', '.inline-edit-input', function () {
                saveInlineField($(this));
            });
    }

    function saveInlineField($input) {
        const $wrap = $input.closest('.inline-edit-wrap');
        if ($wrap.data('saving')) return;

        const id = $wrap.data('id');
        const field = $wrap.data('field');
        const valor = $input.val().trim();
        const $disp = $wrap.find('.inline-edit-display');
        const $spin = $wrap.find('.inline-edit-saving');

        $wrap.data('saving', true);
        $input.addClass('d-none');
        $spin.removeClass('d-none');

        const url = PACIENTES_UPDATE_URL.replace('__ID__', id);

        $.ajax({
            url: url,
            type: 'POST',
            data: { _method: 'PATCH', _token: CSRF_TOKEN, field: field, value: valor },
            success: function (res) {
                $wrap.data('saving', false);
                $spin.addClass('d-none');

                const nuevoValor = res.value || '';
                $wrap.data('value', nuevoValor);

                if (field === 'telefono') {
                    if (nuevoValor) {
                        $disp.html('<span style="color: var(--text-primary); font-weight: 600;">' + nuevoValor + '</span><i class="bi bi-pencil-square ml-1 text-muted" style="font-size: 0.75rem;"></i>');
                    } else {
                        $disp.html('<span style="color: var(--text-muted); font-style: italic; font-size: 0.75rem;">-- teléfono --</span><i class="bi bi-pencil-square ml-1 text-muted" style="font-size: 0.75rem;"></i>');
                    }
                }
                $disp.removeClass('d-none');
            },
            error: function () {
                $wrap.data('saving', false);
                $spin.addClass('d-none');
                $disp.removeClass('d-none');
                alert('Error al guardar.');
            }
        });
    }

    function cancelInlineEdit($input) {
        const $wrap = $input.closest('.inline-edit-wrap');
        $input.addClass('d-none');
        $wrap.find('.inline-edit-display').removeClass('d-none');
    }
</script>
@endpush