@extends('layouts.app')

@section('title', 'Alerta Semanal - Estadísticas 1.7')

@push('styles')
<style>
    .app-footer {
        display: none !important;
    }
    .app-content {
        padding: 0.6rem 0.85rem !important;
        height: calc(100vh - var(--navbar-height)) !important;
        max-height: calc(100vh - var(--navbar-height)) !important;
        display: flex !important;
        flex-direction: column !important;
        overflow: hidden !important;
        width: 100% !important;
        min-width: 0 !important;
        max-width: 100% !important;
        align-items: center !important;
    }
    .cell-clickable:hover {
        background-color: var(--color-primary-light, rgba(99, 102, 241, 0.15)) !important;
    }
    /* Estilos Modernos para la tabla del Modal de Pacientes */
    .modal-patient-table {
        border-collapse: collapse !important;
    }
    .modal-patient-table th,
    .modal-patient-table td {
        border: none !important;
        border-bottom: 1px solid var(--border-color) !important;
        vertical-align: middle !important;
    }
    .modal-patient-table tbody tr {
        transition: background 0.15s ease-in-out !important;
    }
    .modal-patient-table tbody tr:hover td {
        background-color: var(--color-primary-light, rgba(99, 102, 241, 0.08)) !important;
    }
    .modal-patient-table tbody tr:last-child td {
        border-bottom: none !important;
    }
    @media print {
        .no-print { display: none !important; }
        .informe-table-container { border: none !important; box-shadow: none !important; max-height: none !important; width: 100% !important; max-width: 100% !important; }
        .table-alerta th, .table-alerta td { border: 1px solid #000 !important; color: #000 !important; }
    }
</style>
@endpush

@section('content')
<div class="informe-page-wrapper" style="flex: 1 1 0%; min-height: 0; display: flex; flex-direction: column; overflow: hidden; width: 100%; max-width: 980px; margin: 0 auto; background: transparent; border: none; box-shadow: none;">
    <!-- Header -->
    <div class="informe-header no-print" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md, 10px); padding: 0.65rem 1rem; margin-bottom: 8px; display: flex; align-items: center; justify-content: space-between; gap: 1rem; flex-shrink: 0; width: 100%;">
        <div>
            <h2 style="font-size: 1.05rem; font-weight: 800; color: var(--text-primary); margin: 0; display: flex; align-items: center; gap: 0.4rem;">
                <i class="bi bi-exclamation-triangle text-primary"></i> Alerta Semanal
            </h2>
            <p style="font-size: 0.72rem; color: var(--text-muted); margin: 0.15rem 0 0 0; text-transform: uppercase; letter-spacing: 0.05em; font-weight: 600;">
                Vigilancia de Alertas Epidemiológicas Semanales (Telegrama Semanal)
            </p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" id="btn-refresh-report" class="btn btn-subtle btn-sm" style="font-weight: 600; font-size: 0.78rem; padding: 0.35rem 0.75rem; border-radius: 6px; border: 1px solid var(--border-color); background: var(--bg-surface); color: var(--text-primary);">
                <i class="bi bi-arrow-clockwise mr-1"></i> Actualizar
            </button>
        </div>
    </div>

    <!-- Dynamic Content Area -->
    <div id="dynamic-content" style="flex: 1 1 0%; min-height: 0; display: flex; flex-direction: column; overflow: hidden; position: relative; width: 100%;">
        <!-- Loading Overlay -->
        <div id="table-loader" style="display: none; position: absolute; top: 0; left: 0; width: 100%; height: 100%; background: var(--bg-surface); opacity: 0.85; z-index: 1000; align-items: center; justify-content: center; backdrop-filter: blur(4px); border-radius: var(--radius-md, 10px);">
            <div class="spinner-border text-primary" role="status">
                <span class="sr-only">Cargando...</span>
            </div>
        </div>

        @include('informes.alerta_semanal_content')
    </div>
</div>
@endsection

@push('scripts')
<script>
    let coldChainStatus = 'green';
    function toggleColdChain() {
        const states = ['green', 'yellow', 'red'];
        const labels = { 'green': 'VERDE', 'yellow': 'AMARILLO', 'red': 'ROJO' };
        const colors = { 'green': '#22c55e', 'yellow': '#f59e0b', 'red': '#ef4444' };
        const textColors = { 'green': '#fff', 'yellow': '#000', 'red': '#fff' };

        const currentIndex = states.indexOf(coldChainStatus);
        coldChainStatus = states[(currentIndex + 1) % states.length];

        const cell = $('#coldChainCell');
        const lbl = $('#coldChainLabel');
        if (cell.length) {
            cell.css({ 'background-color': colors[coldChainStatus], 'color': textColors[coldChainStatus] });
            lbl.text(labels[coldChainStatus]);
        }
    }

    function fetchDetails(idx, range) {
        const ano = $('select[name="ano"]').val() || '{{ $anoDefault }}';
        const se = $('select[name="se"]').val() || '{{ $seDefault }}';

        const modal = $('#modalAlertaDetalles');
        const loader = $('#modalAlertaLoader');
        const bodyContent = $('#modalAlertaBodyContent');

        loader.show();
        bodyContent.hide();
        modal.modal('show');

        $.ajax({
            url: "{{ route('informes.alerta-semanal.details') }}",
            type: 'GET',
            data: { ano: ano, se: se, idx: idx, range: range },
            headers: { 'X-Requested-With': 'XMLHttpRequest' },
            success: function(res) {
                const titleText = res.title || res.label || 'Detalle de Pacientes';
                const rangeText = res.range_label || range || '';
                const totalCount = res.total !== undefined ? res.total : (res.count !== undefined ? res.count : 0);

                $('#modalAlertaTitle').text(titleText);
                $('#modalAlertaSubtitleBadge').text('Grupo: ' + rangeText);
                $('#modalAlertaTotalBadge').text(totalCount + (totalCount === 1 ? ' Caso' : ' Casos'));
                $('#modalSummaryTotal').text(totalCount);

                let daysHtml = '';
                const daysObj = res.summary_days || res.summaryByDay;
                if (daysObj && Object.keys(daysObj).length > 0) {
                    for (const [day, count] of Object.entries(daysObj)) {
                        daysHtml += `
                            <div class="d-inline-flex align-items-center px-2.5 py-1 rounded-pill mr-1 mb-1" style="background: var(--bg-surface); border: 1px solid var(--border-color); font-size: 0.75rem; gap: 6px; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
                                <span class="text-muted"><i class="bi bi-calendar3 mr-1 text-primary"></i> ${day}</span>
                                <span class="badge badge-primary font-weight-bold" style="border-radius: 9999px; font-size: 0.70rem; padding: 2px 6px;">${count}</span>
                            </div>
                        `;
                    }
                }
                $('#modalSummaryDays').html(daysHtml || '<span class="text-muted small">Sin registros por fecha</span>');

                let rows = '';
                const patientsList = res.patients || res.details || [];
                if (patientsList.length > 0) {
                    patientsList.forEach((p, i) => {
                        const sexoUpper = (p.sexo || '').trim().toUpperCase();
                        let sexoBadge = '-';
                        if (sexoUpper === 'M' || sexoUpper === 'MASCULINO') {
                            sexoBadge = `<span class="badge font-weight-bold" style="background: rgba(59, 130, 246, 0.12); color: #3b82f6; font-size: 0.72rem; border-radius: 6px; padding: 2px 7px;">M</span>`;
                        } else if (sexoUpper === 'F' || sexoUpper === 'FEMENINO') {
                            sexoBadge = `<span class="badge font-weight-bold" style="background: rgba(236, 72, 153, 0.12); color: #ec4899; font-size: 0.72rem; border-radius: 6px; padding: 2px 7px;">F</span>`;
                        } else if (p.sexo) {
                            sexoBadge = `<span class="badge badge-subtle" style="font-size: 0.72rem; border-radius: 6px; padding: 2px 7px;">${p.sexo}</span>`;
                        }

                        rows += `
                            <tr>
                                <td class="text-center font-weight-bold text-muted" style="padding: 9px 12px; font-size: 0.75rem;">${i + 1}</td>
                                <td style="padding: 9px 12px; white-space: nowrap; font-weight: 700; color: var(--color-primary); font-size: 0.82rem;">${p.fecha}</td>
                                <td style="padding: 9px 12px;">
                                    <span class="badge" style="background: var(--bg-subtle); color: var(--text-primary); border: 1px solid var(--border-color); font-family: monospace; font-size: 0.78rem; font-weight: 700; border-radius: 6px; padding: 3px 8px;">
                                        ${p.expediente || p.exp || '-'}
                                    </span>
                                </td>
                                <td class="text-center" style="padding: 9px 12px;">${sexoBadge}</td>
                                <td style="padding: 9px 12px; font-weight: 600; color: var(--text-primary); font-size: 0.80rem;">${p.edad || '-'}</td>
                                <td style="padding: 9px 12px; font-weight: 700; color: var(--text-primary); font-size: 0.82rem;">${p.diagnostico || '-'}</td>
                                <td style="padding: 9px 12px; color: var(--text-muted); font-size: 0.80rem; font-weight: 500;">
                                    <i class="bi bi-person-fill text-primary mr-1" style="font-size: 0.75rem;"></i>${p.medico || '-'}
                                </td>
                            </tr>
                        `;
                    });
                } else {
                    rows = '<tr><td colspan="7" class="text-center py-4 text-muted"><i class="bi bi-info-circle mr-1"></i> No se encontraron registros de pacientes para este rango.</td></tr>';
                }
                $('#modalAlertaTableBody').html(rows);

                loader.hide();
                bodyContent.show();
            },
            error: function() {
                loader.hide();
                bodyContent.show();
                $('#modalAlertaTableBody').html('<tr><td colspan="7" class="text-center py-4 text-danger font-weight-bold"><i class="bi bi-exclamation-triangle mr-1"></i> Error al cargar los datos</td></tr>');
            }
        });
    }

    function copyToExcel() {
        let table = document.querySelector('.table-alerta');
        if (!table) return;

        let text = "";
        let rows = table.querySelectorAll("tr");
        rows.forEach(row => {
            let cols = row.querySelectorAll("th, td");
            let rowData = [];
            cols.forEach(col => {
                let cellText = col.innerText.trim().replace(/\n/g, " ");
                rowData.push(cellText);
            });
            if (rowData.length > 0) {
                text += rowData.join("\t") + "\n";
            }
        });

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(() => {
                Swal.fire({ title: '¡Copiado!', text: 'Datos copiados al portapapeles listos para pegar en Excel.', icon: 'success', timer: 1500, showConfirmButton: false });
            });
        } else {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            Swal.fire({ title: '¡Copiado!', text: 'Datos copiados al portapapeles.', icon: 'success', timer: 1500, showConfirmButton: false });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        function refreshReport() {
            const $form = $('#filter-form');
            if (!$form.length) return;
            
            const url = $form.attr('action');
            const data = $form.serialize();

            $('#table-loader').css('display', 'flex').fadeIn(200);

            $.ajax({
                url: url,
                type: 'GET',
                data: data,
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
                success: function(response) {
                    $('#dynamic-content').html(response);
                    $('#table-loader').fadeOut(200);
                },
                error: function() {
                    Swal.fire('Error', 'Error al actualizar los datos', 'error');
                    $('#table-loader').fadeOut(200);
                }
            });
        }

        $(document).on('change', '.ajax-filter', function() {
            refreshReport();
        });

        $(document).on('click', '#btn-refresh-report', function() {
            refreshReport();
        });
    });
</script>
@endpush
