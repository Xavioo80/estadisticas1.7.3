<!-- Modal Selector de Médicos -->
<div class="modal fade" id="modalSelectorMedicos" tabindex="-1" role="dialog" aria-labelledby="modalSelectorMedicosLabel" aria-hidden="true" style="z-index: 9999 !important;">
    <div class="modal-dialog modal-lg" role="document" style="z-index: 10000 !important;">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="modalSelectorMedicosLabel">
                    <i class="fas fa-user-md"></i> Seleccionar Médico
                    <span class="badge badge-secondary ml-2" style="font-size: 0.7rem;">Alt+M</span>
                </h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <label for="busquedaSelectorMedico">Buscar por código, nombre, especialidad o jornada:</label>
                    <div class="input-group">
                        <input type="text" 
                               id="busquedaSelectorMedico" 
                               class="form-control form-control-sm" 
                               placeholder="Buscar médico... (filtrado en tiempo real)"
                               autocomplete="off">
                        <div class="input-group-append">
                            <button class="btn btn-outline-secondary" type="button" id="btnLimpiarBusquedaMedicoSelector" title="Limpiar búsqueda">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                    <small class="text-muted">
                        <span id="contadorResultadosMedicoSelector">Cargando médicos...</span>
                    </small>
                </div>

                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm table-hover table-bordered" id="tablaSelectorMedicos">
                        <thead class="thead-light sticky-top">
                            <tr>
                                <th width="15%">Código</th>
                                <th width="35%">Nombre</th>
                                <th width="25%">Especialidad</th>
                                <th width="15%">Jornada</th>
                                <th width="10%">Acción</th>
                            </tr>
                        </thead>
                        <tbody id="resultadosSelectorMedicos">
                            <!-- Los resultados se cargarán aquí dinámicamente -->
                        </tbody>
                    </table>
                </div>

                <div id="loadingSelectorMedicos" class="text-center" style="display: none;">
                    <i class="fas fa-spinner fa-spin"></i> Buscando médicos...
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
            </div>
        </div>
    </div>
</div>
