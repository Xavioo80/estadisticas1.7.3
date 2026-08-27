@extends('layouts.app')

@section('title', 'Informe TRANS-2 - Estadísticas 1.7')

@push('styles')
<style>
    .cell-clickable:hover {
        background-color: var(--color-primary-light) !important;
    }
    @media print {
        .no-print { display: none !important; }
        .informe-table-container { border: none !important; box-shadow: none !important; max-height: none !important; overflow: visible !important; }
        .trans2-side-container { display: block !important; }
        .table-trans2 th, .table-trans2 td { border: 1px solid #000 !important; color: #000 !important; }
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
@endsection

@push('scripts')
<script>
    function fetchTrans2Details(rowId, range, se) {
        const ano = $('select[name="ano"]').val() || '{{ $anoDefault }}';

        $('#modalTrans2Title').text('Cargando detalles...');
        $('#modalTrans2SemanaBadge').text(`Semana ${se}`);
        $('#modalTrans2TableBody').html('<tr><td colspan="6" class="text-center py-4 text-muted"><div class="spinner-border spinner-border-sm mr-1"></div> Cargando pacientes...</td></tr>');
        $('#modalTrans2Detalles').modal('show');

        const url = `{{ route('informes.trans2.details') }}?ano=${ano}&se=${se}&row_id=${rowId}&range=${range}`;

        $.getJSON(url, function(data) {
            $('#modalTrans2Title').text(data.label || 'Detalles');
            
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
            $('#modalTrans2TableBody').html(rowsHtml);
        }).fail(function() {
            $('#modalTrans2TableBody').html('<tr><td colspan="6" class="text-center text-danger py-3">Error al cargar los datos.</td></tr>');
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
            $('.btn-toggle-side').removeClass('btn-primary active').addClass('btn-outline-secondary');
            $(this).removeClass('btn-outline-secondary').addClass('btn-primary active');

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
