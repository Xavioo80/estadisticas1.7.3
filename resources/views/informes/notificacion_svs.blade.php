@extends('layouts.app')

@section('title', 'Consulta y Registro SNVS - Estadísticas 1.7')

@push('styles')
<style>
  .app-content {
    padding: 0.65rem 1.25rem 0.4rem !important;
    height: calc(100vh - var(--navbar-height) - var(--footer-height)) !important;
    max-height: calc(100vh - var(--navbar-height) - var(--footer-height)) !important;
    display: flex !important;
    flex-direction: column !important;
    overflow: hidden !important;
  }

  /* Encabezado y Acciones */
  .svs-header-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md, 6px);
    padding: 0.45rem 0.75rem;
    margin-bottom: 0.45rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: var(--shadow-sm);
    flex-shrink: 0;
  }

  .svs-header-title {
    display: flex;
    align-items: center;
    gap: 0.65rem;
  }

  .svs-header-icon {
    width: 32px;
    height: 32px;
    border-radius: 6px;
    background: linear-gradient(135deg, #0ea5e9 0%, #0284c7 100%);
    color: #ffffff;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.95rem;
    flex-shrink: 0;
    box-shadow: 0 2px 4px rgba(14, 165, 233, 0.25);
  }

  /* Métricas Resumen en 1 fila horizontal */
  .svs-stat-grid {
    display: grid;
    grid-template-columns: repeat(5, minmax(130px, 1fr));
    gap: 0.45rem;
    margin-bottom: 0.45rem;
    flex-shrink: 0;
  }

  .svs-stat-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md, 6px);
    padding: 0.35rem 0.65rem;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-shadow: var(--shadow-sm);
  }

  .svs-stat-icon {
    width: 28px;
    height: 28px;
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 0.85rem;
    color: #ffffff;
    flex-shrink: 0;
  }

  .icon-grad-blue    { background: linear-gradient(135deg, #3b82f6 0%, #2563eb 100%); }
  .icon-grad-emerald { background: linear-gradient(135deg, #10b981 0%, #059669 100%); }
  .icon-grad-amber   { background: linear-gradient(135deg, #f59e0b 0%, #d97706 100%); }
  .icon-grad-rose    { background: linear-gradient(135deg, #f43f5e 0%, #e11d48 100%); }
  .icon-grad-purple  { background: linear-gradient(135deg, #8b5cf6 0%, #7c3aed 100%); }

  /* Filtros en 1 sola línea horizontal */
  .svs-filter-card {
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md, 6px);
    padding: 0.4rem 0.65rem;
    margin-bottom: 0.45rem;
    box-shadow: var(--shadow-sm);
    flex-shrink: 0;
  }

  .svs-filter-form {
    display: flex !important;
    flex-direction: row !important;
    align-items: center !important;
    gap: 0.5rem !important;
    width: 100% !important;
    flex-wrap: nowrap !important;
    margin-bottom: 0 !important;
    height: 32px !important;
  }

  .svs-filter-input,
  .svs-filter-select {
    width: 100%;
    height: 32px !important;
    min-height: 32px !important;
    max-height: 32px !important;
    padding: 0 0.65rem;
    font-size: 0.8rem;
    font-weight: 600;
    line-height: 30px;
    border-radius: 4px;
    background-color: var(--bg-body, #1e293b);
    color: var(--text-primary, #f8fafc);
    border: 1px solid var(--border-color, #334155);
    outline: none;
    box-sizing: border-box;
    display: block;
    margin: 0 !important;
    transition: border-color 0.15s ease;
  }

  .svs-filter-select {
    padding: 0 1.75rem 0 0.65rem;
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 0.6rem center;
    background-size: 11px 9px;
    cursor: pointer;
  }

  .svs-filter-input:focus,
  .svs-filter-select:focus {
    border-color: var(--color-primary, #3b82f6);
    box-shadow: 0 0 0 2px rgba(59, 130, 246, 0.2);
  }

  /* Tabla Contenedora: Scroll Fijo y Siempre Visible */
  .svs-table-container {
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md, 6px);
    box-shadow: var(--shadow-sm);
    flex: 1 1 auto;
    min-height: 350px;
    display: flex;
    flex-direction: column;
    overflow: hidden;
  }

  .svs-table-scroll {
    flex: 1 1 auto;
    overflow-x: scroll !important;
    overflow-y: auto !important;
    min-height: 0;
    width: 100%;
    scrollbar-color: #475569 var(--bg-body, #0f172a);
    scrollbar-width: auto;
  }

  /* Scrollbars estilizados, siempre visibles y fijos al pie */
  .svs-table-scroll::-webkit-scrollbar {
    width: 10px;
    height: 10px;
  }

  .svs-table-scroll::-webkit-scrollbar-track {
    background: var(--bg-body, #0f172a);
    border-radius: 4px;
  }

  .svs-table-scroll::-webkit-scrollbar-thumb {
    background: #475569;
    border-radius: 5px;
    border: 2px solid var(--bg-body, #0f172a);
  }

  .svs-table-scroll::-webkit-scrollbar-thumb:hover {
    background: #3b82f6;
  }

  /* Tabla de Texto Plano */
  .svs-table {
    width: max-content;
    min-width: 2200px;
    margin-bottom: 0;
    border-collapse: separate;
    border-spacing: 0;
    font-size: 0.8rem;
  }

  .svs-table thead th {
    position: sticky;
    top: 0;
    z-index: 10;
    background: var(--bg-subtle, #f8fafc);
    color: var(--text-muted, #64748b);
    font-weight: 700;
    text-transform: uppercase;
    font-size: 0.7rem;
    letter-spacing: 0.5px;
    padding: 0.45rem 0.6rem;
    border-bottom: 1px solid var(--border-color);
    white-space: nowrap;
    text-align: left;
  }

  .svs-table tbody tr {
    transition: background 0.15s ease;
  }

  .svs-table tbody tr:hover {
    background: var(--bg-hover, rgba(0, 0, 0, 0.02));
  }

  .svs-table tbody td {
    padding: 0.35rem 0.6rem;
    vertical-align: middle;
    border-bottom: 1px solid var(--border-color);
    color: var(--text-primary);
    white-space: nowrap;
    font-weight: 500;
    font-size: 0.78rem;
  }

  /* Pie Informativo de Scroll */
  .svs-pagination-wrapper {
    padding: 0.35rem 0.75rem;
    background: var(--bg-subtle, #f8fafc);
    border-top: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    justify-content: space-between;
    font-size: 0.75rem;
    color: var(--text-muted);
    flex-shrink: 0;
  }

  /* Inputs planos y limpios dentro de celdas */
  .table-input-plain {
    height: 24px;
    padding: 0 0.35rem;
    font-size: 0.76rem;
    border-radius: 3px;
    border: 1px solid var(--border-color);
    background: var(--bg-body);
    color: var(--text-primary);
    outline: none;
    transition: all 0.15s ease;
  }

  .table-input-plain:focus {
    border-color: var(--color-primary, #3b82f6);
    box-shadow: 0 0 0 1.5px rgba(59, 130, 246, 0.2);
  }

  .table-select-plain {
    height: 24px;
    padding: 0 1.1rem 0 0.35rem;
    font-size: 0.74rem;
    border-radius: 3px;
    border: 1px solid var(--border-color);
    background-color: var(--bg-body);
    color: var(--text-primary);
    outline: none;
    appearance: none;
    -webkit-appearance: none;
    background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' viewBox='0 0 16 16'%3e%3cpath fill='none' stroke='%2394a3b8' stroke-linecap='round' stroke-linejoin='round' stroke-width='2' d='M2 5l6 6 6-6'/%3e%3c/svg%3e");
    background-repeat: no-repeat;
    background-position: right 0.35rem center;
    background-size: 8px 6px;
    cursor: pointer;
  }

  /* Botón Check Tipo Notificación */
  .btn-notificar-toggle {
    height: 25px;
    padding: 0 0.55rem;
    font-size: 0.72rem;
    border-radius: 4px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    gap: 0.25rem;
    transition: all 0.2s ease;
    cursor: pointer;
    white-space: nowrap;
    text-decoration: none;
  }

  .btn-notificar-toggle.is-notificado {
    background: #10b981 !important;
    color: #ffffff !important;
    border: 1px solid #059669 !important;
    box-shadow: 0 2px 5px rgba(16, 185, 129, 0.35);
  }

  .btn-notificar-toggle.is-notificado:hover {
    background: #059669 !important;
  }

  .btn-notificar-toggle.is-pendiente {
    background: var(--bg-body, #1e293b) !important;
    color: var(--text-muted, #94a3b8) !important;
    border: 1px solid var(--border-color, #475569) !important;
  }

  .btn-notificar-toggle.is-pendiente:hover {
    background: rgba(59, 130, 246, 0.15) !important;
    color: #3b82f6 !important;
    border-color: #3b82f6 !important;
  }

  @media print {
    .no-print, .svs-header-card, .svs-stat-grid, .svs-filter-card, .svs-pagination-wrapper {
      display: none !important;
    }
    .svs-table-container {
      border: none !important;
      box-shadow: none !important;
    }
    .svs-table-scroll {
      overflow: visible !important;
    }
  }
</style>
@endpush

@section('content')
<div id="notificacion-svs-container" style="height: 100%; display: flex; flex-direction: column; flex: 1 1 auto; min-height: 0; overflow: hidden;">
    @include('informes.notificacion_svs_content')
</div>
@endsection

@push('scripts')
<script>
    $(document).ready(function () {
        // 1. Manejador AJAX para Filtros
        $(document).on('change', '.ajax-filter-svs', function () {
            ejecutarFiltroAjax();
        });

        var searchTimer = null;
        $(document).on('input', 'input[name="search"]', function () {
            clearTimeout(searchTimer);
            searchTimer = setTimeout(function () {
                ejecutarFiltroAjax();
            }, 350);
        });

        function ejecutarFiltroAjax() {
            let formData = $('#filter-form-svs').serialize();
            let url = "{{ route('informes.notificacion_svs') }}?" + formData;

            $('#notificacion-svs-container').css('opacity', '0.5');

            $.ajax({
                url: url,
                type: 'GET',
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function (html) {
                    $('#notificacion-svs-container').html(html).css('opacity', '1');
                },
                error: function () {
                    $('#notificacion-svs-container').css('opacity', '1');
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de Carga',
                        text: 'No se pudo actualizar la tabla de notificaciones SNVS.'
                    });
                }
            });
        }

        // 2. Cambio de enfermedad SVS
        $(document).on('change', '.svs-disease-select', function () {
            let select = $(this);
            $.post("{{ route('informes.notificacion_svs.update_disease') }}", {
                _token: "{{ csrf_token() }}",
                informe_id: select.data('informe-id'),
                enfermedad_svs: select.val()
            });
        });

        // 3. Toggle Notificado / Enviar
        window.toggleEstadoNotificacion = function (btnEl, informeId) {
            let btn = $(btnEl);
            let currentStatus = btn.hasClass('is-notificado');
            let newStatus = !currentStatus;

            // Feedback visual instantáneo
            if (newStatus) {
                btn.removeClass('is-pendiente').addClass('is-notificado').html('<i class="bi bi-check-circle-fill"></i> Enviado');
            } else {
                btn.removeClass('is-notificado').addClass('is-pendiente').html('<i class="bi bi-send"></i> Enviar');
            }

            $.post("{{ route('informes.notificacion_svs.toggle_notificado') }}", {
                _token: "{{ csrf_token() }}",
                informe_id: informeId,
                notificado: newStatus
            }).fail(function () {
                // Revertir si hay error
                if (currentStatus) {
                    btn.removeClass('is-pendiente').addClass('is-notificado').html('<i class="bi bi-check-circle-fill"></i> Enviado');
                } else {
                    btn.removeClass('is-notificado').addClass('is-pendiente').html('<i class="bi bi-send"></i> Enviar');
                }
            });
        };

        // 4. Edición inline de teléfono
        $(document).on('click', '.inline-edit-tel-wrap .inline-edit-tel-disp', function (e) {
            e.stopPropagation();
            let wrap = $(this).closest('.inline-edit-tel-wrap');
            let disp = wrap.find('.inline-edit-tel-disp');
            let input = wrap.find('.inline-edit-tel-input');

            disp.addClass('d-none');
            input.removeClass('d-none').focus().select();
        });

        $(document).on('blur', '.inline-edit-tel-input', function () {
            guardarTelefonoInline($(this));
        });

        $(document).on('keydown', '.inline-edit-tel-input', function (e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                $(this).blur();
            }
            if (e.key === 'Escape') {
                let wrap = $(this).closest('.inline-edit-tel-wrap');
                $(this).val(wrap.data('value')).addClass('d-none');
                wrap.find('.inline-edit-tel-disp').removeClass('d-none');
            }
        });

        function guardarTelefonoInline(inputEl) {
            let wrap = inputEl.closest('.inline-edit-tel-wrap');
            let disp = wrap.find('.inline-edit-tel-disp');
            let informeId = wrap.data('informe-id');
            let nuevoTel = inputEl.val().trim();

            if (nuevoTel === wrap.data('value')) {
                inputEl.addClass('d-none');
                disp.removeClass('d-none');
                return;
            }

            $.post("{{ route('informes.notificacion_svs.update_telefono') }}", {
                _token: "{{ csrf_token() }}",
                informe_id: informeId,
                telefono: nuevoTel
            }).done(function (res) {
                wrap.data('value', nuevoTel);
                disp.text(nuevoTel || '-').removeClass('d-none');
                inputEl.addClass('d-none');
            }).fail(function () {
                inputEl.val(wrap.data('value')).addClass('d-none');
                disp.removeClass('d-none');
            });
        }
    });

    // Formato de DNI en tiempo real
    function formatearDniFila(inputEl) {
        let value = inputEl.value.replace(/\D/g, '');
        let formatted = '';

        if (value.length > 0) formatted = value.substring(0, 4);
        if (value.length >= 5) formatted += '-' + value.substring(4, 8);
        if (value.length >= 9) formatted += '-' + value.substring(8, 13);

        inputEl.value = formatted;

        if (value.length === 13 && inputEl.dataset.lastSearched !== formatted) {
            inputEl.dataset.lastSearched = formatted;
            buscarPacienteEnFila(inputEl, $(inputEl).data('informe-id'));
        }
    }

    // Búsqueda AJAX ultra-rápida y guardado
    function buscarPacienteEnFila(inputOrBtn, informeId) {
        let row = $(inputOrBtn).closest('tr');
        let inputDni = row.find('.input-dni-row');
        let formatted = inputDni.val().trim();

        if (!formatted) return;

        let statusIcon = row.find('.btn-guardar-row i');
        statusIcon.removeClass('fa-save fa-check fa-exclamation-triangle text-success text-danger').addClass('fa-spinner fa-spin');

        $.ajax({
            url: "{{ route('informes.notificacion_svs.buscar_paciente') }}",
            type: 'POST',
            data: {
                _token: "{{ csrf_token() }}",
                identidad: formatted,
                informe_id: informeId,
                fecha_inicio_sintomas: row.find('.input-fecha-sintomas-row').val(),
                enfermedad_svs: row.find('.svs-disease-select').val()
            },
            success: function (res) {
                statusIcon.removeClass('fa-spinner fa-spin').addClass('fa-check text-success');
                if (res && (res.success || res.data)) {
                    let d = res.data || res;
                    if (d.nombre_completo) row.find('.cell-paciente').text(d.nombre_completo);
                    if (d.fecha_nacimiento) row.find('.cell-fecha-nac').text(d.fecha_nacimiento);
                    if (d.edad !== undefined && d.edad !== null) row.find('.cell-edad').text(d.edad);
                    if (d.sexo) row.find('.cell-sexo').text(d.sexo);
                    if (d.telefono) {
                        row.find('.cell-telefono .inline-edit-tel-disp').text(d.telefono);
                        row.find('.cell-telefono .inline-edit-tel-wrap').data('value', d.telefono);
                        row.find('.cell-telefono .inline-edit-tel-input').val(d.telefono);
                    }
                    if (d.colonia || d.direccion) row.find('.cell-direccion').text(d.colonia || d.direccion);
                }
                setTimeout(() => statusIcon.removeClass('fa-check text-success').addClass('fa-save'), 1500);
            },
            error: function () {
                statusIcon.removeClass('fa-spinner fa-spin').addClass('fa-exclamation-triangle text-danger');
                setTimeout(() => statusIcon.removeClass('fa-exclamation-triangle text-danger').addClass('fa-save'), 2000);
            }
        });
    }

    function guardarRegistroFila(btnEl, informeId) {
        buscarPacienteEnFila(btnEl, informeId);
    }

    function guardarFechaSintomasFila(inputEl, informeId) {
        let val = $(inputEl).val();
        $.post("{{ route('informes.notificacion_svs.update_disease') }}", {
            _token: "{{ csrf_token() }}",
            informe_id: informeId,
            fecha_inicio_sintomas: val
        });
    }
</script>
@endpush
