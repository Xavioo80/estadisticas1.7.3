<!-- Modal para Agregar Diagnóstico -->
<div class="modal fade" id="modalAgregarDiagnostico" tabindex="-1" role="dialog" aria-labelledby="modalAgregarDiagnosticoLabel" aria-hidden="true" style="z-index: 99999 !important;">
    <div class="modal-dialog modal-lg" role="document" style="z-index: 100000 !important;">
        <div class="modal-content">
            <div class="modal-header bg-success text-white">
                <h5 class="modal-title" id="modalAgregarDiagnosticoLabel">
                    <i class="fas fa-stethoscope"></i> Agregar Nuevo Diagnóstico
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="form-agregar-diagnostico">
                    @csrf
                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="diagnostico_codigo">Código <span class="text-danger">*</span></label>
                                <input type="text" class="form-control form-control-sm" id="diagnostico_codigo" name="codigo" required maxlength="6">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label for="diagnostico_patologia">Patología <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="diagnostico_patologia" name="patologia" required>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="diagnostico_secundario">Secundario</label>
                                <input type="text" class="form-control" id="diagnostico_secundario" name="secundario">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label for="diagnostico_categoria">Categoría <span class="text-danger">*</span></label>
                                <select class="form-control" id="diagnostico_categoria" name="categoria" required>
                                    <option value="">Seleccione...</option>
                                    <option value="AT2-R">AT2-R</option>
                                    <option value="MORBILIDAD">MORBILIDAD</option>
                                    <option value="SM1-03">SALUD MENTAL SM1-03</option>
                                    <option value="SM1-07">SALUD MENTAL SM1-07</option>
                                    <option value="ITS">ITS</option>
                                    <option value="IRAS">IRAS</option>
                                    <option value="ARBOVIROSIS">ARBOVIROSIS</option>
                                    <option value="OTRAS PATOLOGIAS">OTRAS PATOLOGIAS</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" class="btn btn-success" id="btn-guardar-diagnostico">
                    <i class="fas fa-save"></i> Guardar Diagnóstico
                </button>
            </div>
        </div>
    </div>
</div>
