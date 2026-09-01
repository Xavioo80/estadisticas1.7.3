@extends('layouts.app')

@section('title', 'Informe TRANS-2 - Estadísticas 1.7')

@push('styles')
<style>
    :root {
        --trans2-border-internal: #cbd5e1;
        --trans2-semana-border: #2563eb; /* Azul rey de alto contraste en Modo Claro */
        --trans2-semana-hdr-bg: rgba(37, 99, 235, 0.12);
        --trans2-semana-odd-bg: #ffffff;
        --trans2-semana-odd-hover: rgba(0, 0, 0, 0.02);
        --trans2-semana-even-bg: #f0f7ff;
        --trans2-semana-even-hover: #e0effe;
    }
    
    [data-theme="dark"] {
        --trans2-border-internal: rgba(255, 255, 255, 0.12);
        --trans2-semana-border: #38bdf8; /* Cyan luminoso de alto contraste en Modo Oscuro */
        --trans2-semana-hdr-bg: rgba(56, 189, 248, 0.18);
        --trans2-semana-odd-bg: transparent;
        --trans2-semana-odd-hover: rgba(255, 255, 255, 0.03);
        --trans2-semana-even-bg: rgba(56, 189, 248, 0.06);
        --trans2-semana-even-hover: rgba(56, 189, 248, 0.11);
    }

    .cell-clickable:hover {
        background-color: var(--color-primary-light) !important;
    }
    
    /* Encabezados y columnas fijas (Sticky) para TRANS-2 */
    .table-trans2 {
        border-collapse: separate !important;
        border-spacing: 0 !important;
    }
    
    /* Cuadrícula interna fina */
    .table-trans2 th,
    .table-trans2 td {
        border: 1px solid var(--trans2-border-internal) !important;
        background-clip: padding-box !important;
        box-sizing: border-box;
    }
    
    .trans2-side-banner {
        position: sticky;
        top: 0;
        z-index: 60;
        height: 36px;
        min-height: 36px;
        background: var(--bg-subtle);
        border-bottom: 2px solid var(--trans2-semana-border);
        box-sizing: border-box;
    }
    
    .table-trans2 thead {
        position: relative;
    }
    
    /* Fila 1: Semanas y Títulos de columna */
    .table-trans2 thead tr.tr-semanas th {
        position: sticky;
        top: 36px;
        z-index: 50;
        height: 38px;
        line-height: 1.2;
        padding: 5px 4px !important;
        background: var(--bg-subtle);
        box-sizing: border-box;
    }
    
    /* Marco perimetral superior del conjunto de semana (1.5px) */
    .table-trans2 thead tr.tr-semanas th.th-semana {
        border: 1.5px solid var(--trans2-semana-border) !important;
        box-shadow: inset 0 0 0 0.5px var(--trans2-semana-border) !important;
        background: var(--trans2-semana-hdr-bg) !important;
        color: var(--trans2-semana-border) !important;
        font-weight: 800 !important;
        font-size: 0.82rem !important;
        letter-spacing: 0.5px;
    }

    .semana-pill {
        display: inline-block;
        padding: 2px 10px;
        border-radius: 20px;
        font-size: 0.76rem;
        font-weight: 800;
        letter-spacing: 0.5px;
        background: var(--trans2-semana-hdr-bg);
        color: var(--trans2-semana-border);
        border: 1.5px solid var(--trans2-semana-border);
        box-shadow: 0 1px 3px rgba(0,0,0,0.1);
    }
    
    .table-trans2 thead tr.tr-semanas th.th-sticky-codigo {
        position: sticky;
        top: 36px;
        left: 0;
        z-index: 55;
        height: 68px;
        background: var(--bg-subtle);
        box-shadow: 2px 0 4px rgba(0,0,0,0.06);
    }
    
    .table-trans2 thead tr.tr-semanas th.th-sticky-enfermedad {
        position: sticky;
        top: 36px;
        left: 85px;
        z-index: 55;
        height: 68px;
        background: var(--bg-subtle);
        border-right: 1.5px solid var(--trans2-semana-border) !important;
        box-shadow: inset -1.5px 0 0 var(--trans2-semana-border), 2px 0 4px rgba(0,0,0,0.08) !important;
    }
    
    /* Fila 2: Rangos de Edad (<1, 1-4, 5-14, 15+) */
    .table-trans2 thead tr.tr-rangos th {
        position: sticky;
        top: 74px;
        z-index: 49;
        height: 30px;
        line-height: 1.2;
        padding: 5px 2px !important;
        background: var(--bg-surface);
        border-bottom: 1.5px solid var(--trans2-semana-border) !important;
        box-shadow: inset 0 -1.5px 0 var(--trans2-semana-border) !important;
        box-sizing: border-box;
    }

    /* Borde izquierdo del conjunto de semana (columna <1) */
    .table-trans2 thead tr.tr-rangos th.th-semana-start,
    .table-trans2 tbody td.td-semana-start {
        border-left: 1.5px solid var(--trans2-semana-border) !important;
        box-shadow: inset 1.5px 0 0 var(--trans2-semana-border) !important;
    }

    /* Borde derecho del conjunto de semana (columna 15+) */
    .table-trans2 thead tr.tr-rangos th.th-semana-end,
    .table-trans2 tbody td.td-semana-end {
        border-right: 1.5px solid var(--trans2-semana-border) !important;
        box-shadow: inset -1.5px 0 0 var(--trans2-semana-border) !important;
    }

    /* Borde superior del bloque de datos */
    .table-trans2 tbody tr.row-semana-top td.td-semana-cell {
        border-top: 1.5px solid var(--trans2-semana-border) !important;
        box-shadow: inset 0 1.5px 0 var(--trans2-semana-border) !important;
    }

    /* Borde inferior del bloque de datos */
    .table-trans2 tbody tr.row-semana-bottom td.td-semana-cell {
        border-bottom: 1.5px solid var(--trans2-semana-border) !important;
        box-shadow: inset 0 -1.5px 0 var(--trans2-semana-border) !important;
    }

    /* Esquinas compuestas */
    .table-trans2 tbody tr.row-semana-top td.td-semana-start {
        box-shadow: inset 1.5px 1.5px 0 var(--trans2-semana-border) !important;
    }
    .table-trans2 tbody tr.row-semana-top td.td-semana-end {
        box-shadow: inset -1.5px 1.5px 0 var(--trans2-semana-border) !important;
    }
    .table-trans2 tbody tr.row-semana-bottom td.td-semana-start {
        box-shadow: inset 1.5px -1.5px 0 var(--trans2-semana-border) !important;
    }
    .table-trans2 tbody tr.row-semana-bottom td.td-semana-end {
        box-shadow: inset -1.5px -1.5px 0 var(--trans2-semana-border) !important;
    }

    /* Coloreado de Recuadros de Semana (Impar vs Par) */
    .table-trans2 th.col-semana-even,
    .table-trans2 td.col-semana-even {
        background-color: var(--trans2-semana-even-bg) !important;
    }
    .table-trans2 tr:hover td.col-semana-even {
        background-color: var(--trans2-semana-even-hover) !important;
    }

    .table-trans2 th.col-semana-odd,
    .table-trans2 td.col-semana-odd {
        background-color: var(--trans2-semana-odd-bg) !important;
    }
    .table-trans2 tr:hover td.col-semana-odd {
        background-color: var(--trans2-semana-odd-hover) !important;
    }
    
    /* Columnas fijas en el cuerpo */
    .table-trans2 tbody td.td-sticky-codigo {
        position: sticky;
        left: 0;
        z-index: 30;
        background: var(--bg-surface);
        box-shadow: 2px 0 4px rgba(0,0,0,0.06);
    }
    
    .table-trans2 tbody td.td-sticky-enfermedad {
        position: sticky;
        left: 85px;
        z-index: 30;
        background: var(--bg-surface);
        border-right: 3.5px solid var(--trans2-semana-border) !important;
        box-shadow: 3px 0 6px rgba(0,0,0,0.15);
    }
    
    .table-trans2 tbody tr:hover td.td-sticky-codigo,
    .table-trans2 tbody tr:hover td.td-sticky-enfermedad {
        background-color: var(--bg-surface-hover, rgba(255,255,255,0.04)) !important;
    }

    @media print {
        .no-print { display: none !important; }
        .informe-table-container { border: none !important; box-shadow: none !important; max-height: none !important; overflow: visible !important; }
        .trans2-side-container { display: block !important; }
        .table-trans2 th, .table-trans2 td { border: 1px solid #000 !important; color: #000 !important; }
        .table-trans2 thead tr th, .table-trans2 tbody td { position: static !important; }
        .trans2-side-banner { position: static !important; }
    }
</style>
@endpush

@section('content')
<div class="informe-page-wrapper">
    <!-- Header -->
    <div class="informe-header">
        <div>
            <h2><i class="bi bi-activity text-primary mr-1"></i> Informe TRANS-2</h2>
            <p>Formulario de Notificación Semanal de Alerta (Consolidado Mensual)</p>
        </div>
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-subtle-primary btn-sm" onclick="abrirModalComparacion()" style="font-weight: 600;">
                <i class="bi bi-diagram-3-fill mr-1"></i> Comparar con otros informes
            </button>
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

        @include('informes.trans2_content')
    </div>
</div>

@include('informes.partials.modal_comparacion_cruzada')
@endsection

@push('scripts')
<script>
    function fetchTrans2Details(rowId, range, se) {
        const ano = $('select[name="ano"]').val() || '{{ $anoDefault }}';
        const mes = $('select[name="mes"]').val() || '{{ $mesDefault }}';

        $('#modalTrans2Title').text('Cargando detalles...');
        $('#modalTrans2SemanaBadge').text(`Semana ${se}`);
        $('#modalTrans2TableBody').html('<tr><td colspan="6" class="text-center py-4 text-muted font-weight-bold"><div class="spinner-border spinner-border-sm text-primary mr-1"></div> Cargando registros de pacientes...</td></tr>');
        $('#modalTrans2Detalles').modal('show');

        const url = `{{ route('informes.trans2.details') }}?ano=${ano}&mes=${mes}&se=${se}&row_id=${rowId}&range=${range}&_t=${new Date().getTime()}`;

        $.getJSON(url, function(data) {
            $('#modalTrans2Title').text(data.label || 'Detalles');
            
            let rowsHtml = '';
            if (data.details && data.details.length > 0) {
                $.each(data.details, function(i, item) {
                    rowsHtml += `
                        <tr style="border-bottom: 1px solid var(--border-color);">
                            <td class="font-weight-600" style="color: var(--text-primary); font-size: 0.8rem;">${item.fecha}</td>
                            <td style="font-family: monospace; font-size: 0.8rem; color: var(--color-primary); font-weight: 700;">${item.exp || '-'}</td>
                            <td class="text-center" style="font-size: 0.8rem;">${item.sexo || '-'}</td>
                            <td style="font-size: 0.8rem;"><span class="badge badge-subtle-secondary">${item.edad || '-'}</span></td>
                            <td class="font-weight-bold" style="color: var(--text-primary); font-size: 0.82rem;">${item.diagnostico || '-'}</td>
                            <td class="small" style="color: var(--text-secondary); font-size: 0.78rem;">${item.medico || '-'}</td>
                        </tr>
                    `;
                });
            } else {
                rowsHtml = '<tr><td colspan="6" class="text-center py-4 text-muted font-weight-bold">No se encontraron pacientes para este criterio en la Semana ' + se + '.</td></tr>';
            }
            $('#modalTrans2TableBody').html(rowsHtml);
        }).fail(function(xhr) {
            console.error('Error fetching TRANS-2 details:', xhr);
            $('#modalTrans2TableBody').html('<tr><td colspan="6" class="text-center text-danger py-4 font-weight-bold"><i class="bi bi-exclamation-octagon-fill mr-1"></i> Error al cargar los datos. Intente de nuevo.</td></tr>');
        });
    }

    function copyToExcel() {
        const activeContainer = $('.trans2-side-container:not(.d-none)');
        const table = activeContainer.find('.table-trans2')[0];
        if (!table) return;

        let text = "";
        const rows = table.querySelectorAll('tbody tr');
        rows.forEach(row => {
            const cols = row.querySelectorAll('td');
            if (cols.length === 1) return; // Saltear headers de sección
            let rowData = [];
            for (let i = 1; i < cols.length; i++) {
                rowData.push(cols[i].innerText.trim().replace(/\s+/g, " "));
            }
            if (rowData.length > 0) text += rowData.join("\t") + "\n";
        });

        if (navigator.clipboard && window.isSecureContext) {
            navigator.clipboard.writeText(text).then(() => {
                Swal.fire({ title: '¡Copiado!', text: 'Datos de la tabla actual copiados al portapapeles.', icon: 'success', confirmButtonColor: '#4d7cfe' });
            });
        } else {
            const textArea = document.createElement("textarea");
            textArea.value = text;
            document.body.appendChild(textArea);
            textArea.select();
            document.execCommand('copy');
            document.body.removeChild(textArea);
            Swal.fire({ title: '¡Copiado!', text: 'Datos de la tabla actual copiados al portapapeles.', icon: 'success', confirmButtonColor: '#4d7cfe' });
        }
    }

    document.addEventListener('DOMContentLoaded', function() {
        // Alternador Anverso / Reverso
        $(document).on('click', '.btn-toggle-side', function() {
            const side = $(this).data('side');
            $('.btn-toggle-side').removeClass('btn-primary').addClass('btn-subtle');
            $(this).removeClass('btn-subtle').addClass('btn-primary');

            if (side === 'obverso') {
                $('#side-obverso').removeClass('d-none');
                $('#side-reverso').addClass('d-none');
            } else {
                $('#side-reverso').removeClass('d-none');
                $('#side-obverso').addClass('d-none');
            }
        });

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
