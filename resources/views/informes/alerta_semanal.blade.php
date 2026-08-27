@extends('layouts.app')

@section('title', 'Alerta Semanal - Estadísticas 1.7')

@push('styles')
<style>
    .cell-clickable:hover {
        background-color: var(--color-primary-light) !important;
    }
    @media print {
        .no-print { display: none !important; }
        .informe-table-container { border: none !important; box-shadow: none !important; max-height: none !important; }
        .table-alerta th, .table-alerta td { border: 1px solid #000 !important; color: #000 !important; }
    }
</style>
@endpush

@section('content')
<div class="informe-page-wrapper">
    <!-- Header -->
    <div class="informe-header">
        <div>
            <h2><i class="bi bi-exclamation-triangle text-primary mr-1"></i> Alerta Semanal</h2>
            <p>Vigilancia de Alertas Epidemiológicas Semanales (Telegrama Semanal)</p>
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

        $('#modalAlertaTitle').text('Cargando detalles...');
        $('#modalAlertaRangeLabel').text('...');
        $('#modalSummaryTotal').text('...');
        $('#modalSummaryDays').empty();
        $('#modalSummaryRanges').empty();
        $('#modalAlertaTableBody').html('<tr><td colspan="6" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm mr-1"></div> Cargando pacientes...</td></tr>');
        $('#modalAlertaDetalles').modal('show');

        const url = `{{ route('informes.alerta-semanal.details') }}?ano=${ano}&se=${se}&idx=${idx}&range=${range}`;

        $.getJSON(url, function(data) {
            $('#modalAlertaTitle').text(data.label);
            $('#modalAlertaRangeLabel').text(data.range_label);
            $('#modalSummaryTotal').text(data.count);

            // Resumen por día
            let daysHtml = '';
            if (data.summaryByDay) {
                $.each(data.summaryByDay, function(date, count) {
                    const dayNum = date.split('-')[2] || date;
                    daysHtml += `<div class="px-2 py-1 text-center"><span class="small text-muted d-block">${dayNum}</span><strong class="text-primary">${count}</strong></div>`;
                });
            }
            $('#modalSummaryDays').html(daysHtml || '<span class="text-muted small">Sin datos</span>');

            // Resumen por rango
            let rangesHtml = '';
            const rangeLabels = { 'less_1': '<1', '1_4': '1-4', '5_14': '5-14', '15_plus': '+15' };
            if (data.summaryByRange) {
                $.each(data.summaryByRange, function(rng, count) {
                    const label = rangeLabels[rng] || rng;
                    rangesHtml += `<div class="px-2 py-1 text-center"><span class="small text-muted d-block">${label}</span><strong class="text-primary">${count}</strong></div>`;
                });
            }
            $('#modalSummaryRanges').html(rangesHtml || '<span class="text-muted small">Sin datos</span>');

            // Tabla de pacientes
            let rowsHtml = '';
            if (data.details && data.details.length > 0) {
                $.each(data.details, function(i, item) {
                    rowsHtml += `
                        <tr>
                            <td class="font-weight-600">${item.fecha}</td>
                            <td>${item.exp || '-'}</td>
                            <td>${item.sexo || '-'}</td>
                            <td>${item.edad || '-'}</td>
                            <td class="font-weight-600 text-dark">${item.diagnostico || '-'}</td>
                            <td class="small text-muted">${item.medico || '-'}</td>
                        </tr>
                    `;
                });
            } else {
                rowsHtml = '<tr><td colspan="6" class="text-center py-3 text-muted">No se encontraron pacientes para este criterio.</td></tr>';
            }
            $('#modalAlertaTableBody').html(rowsHtml);
        }).fail(function() {
            $('#modalAlertaTableBody').html('<tr><td colspan="6" class="text-center text-danger py-3">Error al cargar los datos.</td></tr>');
        });
    }

    function copyToExcel() {
        const table = document.querySelector('.table-alerta');
        if (!table) return;

        let text = "";
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const cols = row.querySelectorAll('td');
            let rowData = [];
            for (let i = 1; i < cols.length; i++) {
                let val = cols[i].innerText.trim().replace(/\s+/g, " ");
                rowData.push(val);
                let colspan = cols[i].getAttribute('colspan');
                if (colspan) {
                    for (let c = 1; c < parseInt(colspan); c++) {
                        rowData.push("");
                    }
                }
            }
            if (rowData.length > 0) {
                text += rowData.join("\t") + "\n";
            }
        });

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(() => {
                Swal.fire({ title: '¡Copiado!', text: 'Datos copiados al portapapeles listos para pegar en Excel.', icon: 'success', confirmButtonColor: '#4d7cfe' });
            });
        } else {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            Swal.fire({ title: '¡Copiado!', text: 'Datos copiados al portapapeles.', icon: 'success', confirmButtonColor: '#4d7cfe' });
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
