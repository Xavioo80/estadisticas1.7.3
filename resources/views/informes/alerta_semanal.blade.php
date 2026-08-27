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
                $('#modalAlertaSubtitle').text('Grupo: ' + rangeText);
                $('#modalAlertaTotalBadge').text(totalCount + (totalCount === 1 ? ' Caso' : ' Casos'));
                $('#modalSummaryTotal').text(totalCount);

                let daysHtml = '';
                const daysObj = res.summary_days || res.summaryByDay;
                if (daysObj && Object.keys(daysObj).length > 0) {
                    for (const [day, count] of Object.entries(daysObj)) {
                        daysHtml += `<span class="badge badge-subtle px-2 py-1 mr-1 mb-1" style="font-size: 0.75rem; border: 1px solid var(--border-color); color: var(--text-primary); background: var(--bg-surface);"><i class="bi bi-calendar-event text-primary mr-1"></i> ${day}: <strong>${count}</strong></span>`;
                    }
                }
                $('#modalSummaryDays').html(daysHtml || '<span class="text-muted small">Sin registros por fecha</span>');

                let rows = '';
                const patientsList = res.patients || res.details || [];
                if (patientsList.length > 0) {
                    patientsList.forEach((p, i) => {
                        rows += `
                            <tr style="border-color: var(--border-color);">
                                <td class="font-weight-bold text-muted">${i + 1}</td>
                                <td class="font-weight-bold text-primary">${p.fecha}</td>
                                <td class="font-weight-bold" style="color: var(--text-primary);">${p.expediente || p.exp || '-'}</td>
                                <td>${p.sexo || '-'}</td>
                                <td>${p.edad || '-'}</td>
                                <td style="color: var(--text-primary);">${p.diagnostico || '-'}</td>
                                <td class="text-muted">${p.medico || '-'}</td>
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
