@extends('layouts.app')

@section('title', 'Informe TRANS-2 - Estadísticas 1.7')

@section('content')
<div class="informe-page-wrapper">
    <!-- Header -->
    <div class="informe-header">
        <div>
            <h2><i class="bi bi-activity text-primary mr-1"></i> Informe TRANS-2</h2>
            <p>Reporte Consolidado de Transferencias TRANS-2</p>
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

    </div>
</div>

<script>
        function trans2Report() {
            return {
                showModal: false,
                activeSide: 'obverso',
                modalData: { label: '', details: [], semana: '' },
                loading: false,
                fetchDetails(rowId, range, se) {
                    this.loading = true;
                    this.showModal = true;
                    this.modalData = { label: 'Cargando...', details: [], semana: se };
                    let url = `{{ route('informes.trans2.details') }}?ano={{ $anoDefault }}&se=${se}&row_id=${rowId}&range=${range}`;
                    fetch(url).then(res => res.json()).then(data => {
                        this.modalData = { ...data, semana: se };
                        this.loading = false;
                    });
                }
            };
        }

        function copyToExcel() {
            const tables = document.querySelectorAll('.table-trans2');
            let text = "";
            tables.forEach(table => {
                const rows = table.querySelectorAll('tbody tr');
                rows.forEach(row => {
                    const cols = row.querySelectorAll('td');
                    if (cols.length === 1) return;
                    let rowData = [];
                    for (let i = 1; i < cols.length; i++) {
                        rowData.push(cols[i].innerText.trim().replace(/\s+/g, " "));
                    }
                    if (rowData.length > 0) text += rowData.join("\t") + "\n";
                });
            });
            navigator.clipboard.writeText(text).then(() => {
                Swal.fire({ title: '¡Copiado!', text: 'Los datos han sido copiados.', icon: 'success', confirmButtonColor: '#4f46e5' });
            });
        }
    </script>

@endsection
