@extends('layouts.app')

@section('title', 'Alerta Semanal - Estadísticas 1.7')

@section('content')
<div class="informe-page-wrapper">
    <!-- Header -->
    <div class="informe-header">
        <div>
            <h2><i class="bi bi-exclamation-triangle text-primary mr-1"></i> Alerta Semanal</h2>
            <p>Vigilancia de Alertas Epidemiológicas Semanales</p>
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
        function alertaReport() {
            return {
                showModal: false,
                modalData: { label: '', range_label: '', count: 0, details: [], summaryByDay: {}, summaryByRange: {} },
                loading: false,
                coldChainStatus: 'green',
                toggleColdChain() {
                    const states = ['green', 'yellow', 'red'];
                    const currentIndex = states.indexOf(this.coldChainStatus);
                    this.coldChainStatus = states[(currentIndex + 1) % states.length];
                },
                getColdChainClass() {
                    const classes = {
                        'green': 'bg-green-600 text-white',
                        'yellow': 'bg-yellow-400 text-slate-900',
                        'red': 'bg-red-600 text-white'
                    };
                    return classes[this.coldChainStatus] + ' font-bold text-[12px]';
                },
                getColdChainLabel() {
                    const labels = {
                        'green': 'VERDE',
                        'yellow': 'AMARILLO',
                        'red': 'ROJO'
                    };
                    return labels[this.coldChainStatus];
                },
                fetchDetails(idx, range) {
                    this.loading = true;
                    this.showModal = true;
                    this.modalData = { label: 'Cargando...', range_label: 'Procesando...', count: 0, details: [], summaryByDay: {}, summaryByRange: {} };

                    let url = `{{ route('informes.alerta-semanal.details') }}?ano={{ $anoDefault }}&se={{ $seDefault }}&idx=${idx}&range=${range}`;

                    fetch(url)
                        .then(res => res.json())
                        .then(data => {
                            this.modalData = data;
                            this.loading = false;
                        })
                        .catch(err => {
                            console.error(err);
                            this.loading = false;
                            alert('Error al cargar los detalles');
                        });
                },
                formatRange(range) {
                    const labels = {
                        'less_1': '<1',
                        '1_4': '1-4',
                        '5_14': '5-14',
                        '15_plus': '+15'
                    };
                    return labels[range] || range;
                },
                getDayName(dateStr) {
                    const days = ['DOM', 'LUN', 'MAR', 'MIÉ', 'JUE', 'VIE', 'SÁB'];
                    const date = new Date(dateStr + 'T12:00:00');
                    return days[date.getDay()];
                }
            };
        }
    </script>
    <script>
        function copyToExcel() {
            const table = document.querySelector('.table-alerta');
            if (!table) return;

            let text = "";
            // Seleccionamos solo las filas del cuerpo (tbody) para evitar encabezados
            const rows = table.querySelectorAll('tbody tr');

            rows.forEach(row => {
                const cols = row.querySelectorAll('td');
                let rowData = [];
                // Empezamos desde el índice 1 para saltar la columna de nombres (Diagnóstico)
                for (let i = 1; i < cols.length; i++) {
                    // Limpiar espacios y saltos de línea
                    let val = cols[i].innerText.trim().replace(/\s+/g, " ");
                    rowData.push(val);

                    // Si la celda tiene colspan (como en Cadena de Frío), 
                    // agregamos espacios vacíos para mantener la alineación de columnas en Excel
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

            // Intentar usar el API de portapapeles moderno
            if (navigator.clipboard && window.isSecureContext) {
                navigator.clipboard.writeText(text).then(() => {
                    notifyCopy();
                });
            } else {
                // Fallback para navegadores antiguos o contextos no seguros
                const textArea = document.createElement("textarea");
                textArea.value = text;
                document.body.appendChild(textArea);
                textArea.select();
                try {
                    document.execCommand('copy');
                    notifyCopy();
                } catch (err) {
                    Swal.fire('Error', 'No se pudo copiar automáticamente. Intenta seleccionar la tabla manualmente.', 'error');
                }
                document.body.removeChild(textArea);
            }
        }

        function notifyCopy() {
            Swal.fire({
                title: '¡Copiado!',
                html: 'Los datos están en tu portapapeles.<br><br><b>Pasos:</b><br>1. Ve a tu Excel en OneDrive.<br>2. Selecciona la celda de inicio.<br>3. Presiona <b>Ctrl + V</b>.',
                icon: 'success',
                confirmButtonText: 'ENTENDIDO',
                confirmButtonColor: '#4f46e5'
            });
        }
    </script>

@endsection
