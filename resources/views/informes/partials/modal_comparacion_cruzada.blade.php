<!-- Modal de Comparación Cruzada y Auditoría Epidemiológica -->
<div class="modal fade" id="modalComparacionCruzada" tabindex="-1" role="dialog" aria-labelledby="modalComparacionCruzadaLabel" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document" style="max-width: min(1140px, 98vw); margin: 8px auto;">
        <div class="modal-content" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-lg); box-shadow: var(--shadow-xl); max-height: 96vh; display: flex; flex-direction: column;">
            
            <!-- Modal Header -->
            <div class="modal-header d-flex align-items-center justify-content-between px-3"
                 style="background: var(--bg-subtle); border-bottom: 1px solid var(--border-color); padding-top: 10px; padding-bottom: 10px; flex-shrink: 0;">
                <div class="d-flex align-items-center" style="gap: 10px; overflow: hidden; flex: 1; min-width: 0;">
                    <i class="bi bi-diagram-3-fill" style="font-size: 1.75rem; color: var(--color-primary); flex-shrink: 0;"></i>
                    <span id="modalComparacionCruzadaLabel"
                          style="font-size: 0.93rem; font-weight: 700; color: var(--text-primary); white-space: nowrap; flex-shrink: 0;">
                        Auditoría de Consistencia Epidemiológica Cruzada
                    </span>
                    <span style="color: var(--border-color); flex-shrink: 0; font-size: 0.85rem;">|</span>
                    <span class="text-muted"
                          style="font-size: 0.71rem; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; min-width: 0;">
                        Comparativa en tiempo real entre <strong>AT2-R (N)</strong>, <strong>Morbilidad</strong>, <strong>TRANS-2</strong>, <strong>ITS</strong> y <strong>Registros Globales (AT1)</strong>
                    </span>
                </div>
                <button type="button" class="close text-muted" data-dismiss="modal" data-bs-dismiss="modal" aria-label="Close"
                        style="opacity: 0.8; font-size: 1.25rem; background: none; border: none; cursor: pointer; color: var(--text-primary); flex-shrink: 0; margin-left: 10px;">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-2" id="modalComparacionCruzadaBody" style="min-height: 180px; position: relative; overflow-y: auto; flex: 1 1 auto;">
                <div class="d-flex flex-column align-items-center justify-content-center py-5">
                    <div class="spinner-border text-primary mb-2" role="status" style="width: 2rem; height: 2rem;"></div>
                    <span class="text-muted font-weight-bold" style="font-size: 0.85rem;">Analizando registros cruzados...</span>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
    function abrirModalComparacion(ano, mes, jornada) {
        if (!ano) {
            ano = $('select[name="ano"]').val() 
               || $('#filter-form select[name="ano"]').val() 
               || $('#filtroAno').val() 
               || '';
        }
        if (!mes) {
            mes = $('select[name="mes"]').val() 
               || $('#filter-form select[name="mes"]').val() 
               || $('#filtroMes').val() 
               || '';
        }
        if (!jornada) {
            jornada = $('select[name="jornada"]').val() 
               || $('#filter-form select[name="jornada"]').val() 
               || $('#filtroJornada').val() 
               || 'TODAS';
        }

        $('#modalComparacionCruzada').modal('show');
        
        cargarDatosComparacionCruzada(ano, mes, jornada);
    }

    function cargarDatosComparacionCruzada(ano, mes, jornada) {
        const body = $('#modalComparacionCruzadaBody');
        body.html(`
            <div class="d-flex flex-column align-items-center justify-content-center py-5">
                <div class="spinner-border text-primary mb-2" role="status" style="width: 2rem; height: 2rem;"></div>
                <span class="text-muted font-weight-bold" style="font-size: 0.85rem;">Analizando consistencia en 5 fuentes...</span>
            </div>
        `);

        const requestData = {};
        if (ano) requestData.ano = ano;
        if (mes) requestData.mes = mes;
        if (jornada) requestData.jornada = jornada;

        $.ajax({
            url: "{{ route('informes.comparacion-cruzada.ajax') }}",
            type: "GET",
            data: requestData,
            success: function(response) {
                body.html(response);
            },
            error: function(xhr) {
                console.error("Error al cargar la comparación cruzada:", xhr);
                body.html(`
                    <div class="alert alert-danger m-3 d-flex align-items-center" role="alert">
                        <i class="bi bi-exclamation-triangle-fill mr-2" style="font-size: 1.2rem;"></i>
                        <div>
                            <strong>Error al cargar la auditoría:</strong> No se pudo procesar la comparación de datos.
                            <div class="mt-1 small text-muted">${xhr.responseText ? xhr.responseText.substring(0, 100) : ''}</div>
                        </div>
                    </div>
                `);
            }
        });
    }

    function recargarModalComparacion() {
        const ano = $('#modal-cmp-ano').val();
        const mes = $('#modal-cmp-mes').val();
        const jornada = $('#modal-cmp-jornada').val();
        cargarDatosComparacionCruzada(ano, mes, jornada);
    }
</script>
