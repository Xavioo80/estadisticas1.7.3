<!-- Modal de Ingreso Masivo -->
<div class="modal fade" id="modalIngresoMasivo" tabindex="-1" role="dialog" aria-labelledby="modalIngresoMasivoLabel" aria-hidden="true" style="z-index: 9999 !important;">
    <div class="modal-dialog" role="document" style="z-index: 10000 !important;">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title" id="modalIngresoMasivoLabel">
                    <i class="fas fa-layer-group"></i> Ingreso Masivo de Datos AT-1
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <form id="formularioIngresoMasivo">
                    
                    <div class="card mb-2">
                        <div class="card-header py-1">
                            <h6 class="mb-0"><i class="fas fa-edit"></i> Datos para Ingreso Masivo</h6>
                        </div>
                        <div class="card-body py-2">
                            <!-- Fila 1: Configuración -->
                            <div class="form-row mb-2">
                                <div class="col-2">
                                    <label for="masivo_px" class="small font-weight-bold text-primary mb-1">
                                        <i class="fas fa-hashtag"></i> Cantidad (px)
                                    </label>
                                    <input type="number" id="masivo_px" class="form-control text-center" 
                                           style="max-width:12ch;" min="1" max="50" value="1" required>
                                </div>
                                <div class="col-3">
                                    <label for="masivo_fecha" class="small mb-1">
                                        <i class="fas fa-calendar"></i> Fecha
                                    </label>
                                    <input type="date" id="masivo_fecha" class="form-control">
                                </div>
                                <div class="col-2">
                                    <label for="masivo_cod_med" class="small mb-1">
                                        <i class="fas fa-hashtag"></i> Código Médico
                                    </label>
                                    <input type="text" id="masivo_cod_med" class="form-control codigo-medico-modal" 
                                           placeholder="Código médico" style="max-width:12ch;">
                                </div>
                                <div class="col-5">
                                    <label for="masivo_nom_med" class="small mb-1">
                                        <i class="fas fa-user-md"></i> Nombre Médico
                                    </label>
                                    <input type="text" id="masivo_nom_med" class="form-control nombre-medico-modal" 
                                           placeholder="Nombre del médico">
                                </div>
                            </div>
                            
                            <!-- Fila 2: Paciente -->
                            <div class="form-row mb-2">
                                <div class="col-2">
                                    <label for="masivo_sexo" class="small mb-1">
                                        <i class="fas fa-venus-mars"></i> Sexo
                                    </label>
                                    <input type="text" id="masivo_sexo" class="form-control text-center" 
                                           style="max-width:12ch;" maxlength="1" placeholder="H/M">
                                </div>
                                <div class="col-2">
                                    <label for="masivo_edad" class="small mb-1">
                                        <i class="fas fa-birthday-cake"></i> Edad
                                    </label>
                                    <input type="number" id="masivo_edad" class="form-control" 
                                           style="max-width:12ch;" min="0" max="120" placeholder="Edad">
                                </div>
                                <div class="col-2">
                                    <label for="masivo_tipo" class="small mb-1">
                                        <i class="fas fa-tag"></i> Tipo
                                    </label>
                                    <input type="text" id="masivo_tipo" class="form-control text-center" 
                                           style="max-width:12ch;" maxlength="1" placeholder="Tipo">
                                </div>
                                <div class="col-2">
                                    <label for="masivo_condp" class="small mb-1">
                                        <i class="fas fa-clipboard-check"></i> COND-P
                                    </label>
                                    <input type="text" id="masivo_condp" class="form-control text-center" 
                                           style="max-width:12ch;" maxlength="1" placeholder="COND-P">
                                </div>
                                <div class="col-2">
                                    <label for="masivo_cod_col" class="small mb-1">
                                        <i class="fas fa-hashtag"></i> Colonia
                                    </label>
                                    <input type="text" id="masivo_cod_col" class="form-control" 
                                           placeholder="Código colonia" style="max-width:12ch;">
                                </div>
                                <div class="col-2">
                                    <label for="masivo_colonia" class="small mb-1">
                                        <i class="fas fa-map-marker-alt"></i> Colonia
                                    </label>
                                    <input type="text" id="masivo_colonia" class="form-control" 
                                           placeholder="Nombre de la colonia">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Diagnósticos -->
                    <div class="card mb-2">
                        <div class="card-header py-1">
                            <h6 class="mb-0"><i class="fas fa-stethoscope"></i> Diagnósticos</h6>
                        </div>
                        <div class="card-body py-2">
                            <!-- Diagnóstico 1 -->
                            <div class="form-row mb-1">
                                <div class="col-2">
                                    <label for="masivo_cod1_primary" class="small mb-1">
                                        <i class="fas fa-hashtag"></i> COD1
                                    </label>
                                    <input type="text" id="masivo_cod1_primary" class="form-control" 
                                           placeholder="Código" style="max-width:12ch;">
                                </div>
                                <div class="col-8">
                                    <label for="masivo_diagnostico1_primary" class="small mb-1">
                                        <i class="fas fa-notes-medical"></i> DIAGNOSTICO1
                                    </label>
                                    <div class="input-group">
                                        <input type="text" id="masivo_diagnostico1_primary" class="form-control" 
                                               readonly style="background-color: #f8f9fa;" placeholder="Descripción del diagnóstico">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-sm btn-outline-primary btn-buscar-diagnostico-masivo" 
                                                    title="Buscar diagnóstico" data-numero="1">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-2">
                                    <label for="masivo_cond1_primary" class="small mb-1">
                                        <i class="fas fa-clipboard-check"></i> COND1
                                    </label>
                                    <input type="text" id="masivo_cond1_primary" class="form-control text-center" 
                                           style="max-width:12ch;" maxlength="1" placeholder="COND">
                                </div>
                            </div>
                            
                            <!-- Diagnóstico 2 -->
                            <div class="form-row mb-1">
                                <div class="col-2">
                                    <label for="masivo_cod2_primary" class="small mb-1">
                                        <i class="fas fa-hashtag"></i> COD2
                                    </label>
                                    <input type="text" id="masivo_cod2_primary" class="form-control" 
                                           placeholder="Código" style="max-width:12ch;">
                                </div>
                                <div class="col-8">
                                    <label for="masivo_diagnostico2_primary" class="small mb-1">
                                        <i class="fas fa-notes-medical"></i> DIAGNOSTICO2
                                    </label>
                                    <div class="input-group">
                                        <input type="text" id="masivo_diagnostico2_primary" class="form-control" 
                                               readonly style="background-color: #f8f9fa;" placeholder="Descripción del diagnóstico">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-sm btn-outline-primary btn-buscar-diagnostico-masivo" 
                                                    title="Buscar diagnóstico" data-numero="2">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-2">
                                    <label for="masivo_cond2_primary" class="small mb-1">
                                        <i class="fas fa-clipboard-check"></i> COND2
                                    </label>
                                    <input type="text" id="masivo_cond2_primary" class="form-control text-center" 
                                           style="max-width:12ch;" maxlength="1" placeholder="COND">
                                </div>
                            </div>
                            
                            <!-- Diagnóstico 3 -->
                            <div class="form-row mb-1">
                                <div class="col-2">
                                    <label for="masivo_cod3_primary" class="small mb-1">
                                        <i class="fas fa-hashtag"></i> COD3
                                    </label>
                                    <input type="text" id="masivo_cod3_primary" class="form-control" 
                                           placeholder="Código" style="max-width:12ch;">
                                </div>
                                <div class="col-8">
                                    <label for="masivo_diagnostico3_primary" class="small mb-1">
                                        <i class="fas fa-notes-medical"></i> DIAGNOSTICO3
                                    </label>
                                    <div class="input-group">
                                        <input type="text" id="masivo_diagnostico3_primary" class="form-control" 
                                               readonly style="background-color: #f8f9fa;" placeholder="Descripción del diagnóstico">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-sm btn-outline-primary btn-buscar-diagnostico-masivo" 
                                                    title="Buscar diagnóstico" data-numero="3">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-2">
                                    <label for="masivo_cond3_primary" class="small mb-1">
                                        <i class="fas fa-clipboard-check"></i> COND3
                                    </label>
                                    <input type="text" id="masivo_cond3_primary" class="form-control text-center" 
                                           style="max-width:12ch;" maxlength="1" placeholder="COND">
                                </div>
                            </div>
                            
                            <!-- Diagnóstico 4 -->
                            <div class="form-row mb-1">
                                <div class="col-2">
                                    <label for="masivo_cod4_primary" class="small mb-1">
                                        <i class="fas fa-hashtag"></i> COD4
                                    </label>
                                    <input type="text" id="masivo_cod4_primary" class="form-control" 
                                           placeholder="Código" style="max-width:12ch;">
                                </div>
                                <div class="col-8">
                                    <label for="masivo_diagnostico4_primary" class="small mb-1">
                                        <i class="fas fa-notes-medical"></i> DIAGNOSTICO4
                                    </label>
                                    <div class="input-group">
                                        <input type="text" id="masivo_diagnostico4_primary" class="form-control" 
                                               readonly style="background-color: #f8f9fa;" placeholder="Descripción del diagnóstico">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-sm btn-outline-primary btn-buscar-diagnostico-masivo" 
                                                    title="Buscar diagnóstico" data-numero="4">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-2">
                                    <label for="masivo_cond4_primary" class="small mb-1">
                                        <i class="fas fa-clipboard-check"></i> COND4
                                    </label>
                                    <input type="text" id="masivo_cond4_primary" class="form-control text-center" 
                                           style="max-width:12ch;" maxlength="1" placeholder="COND">
                                </div>
                            </div>
                            
                            <!-- Diagnóstico 5 -->
                            <div class="form-row mb-1">
                                <div class="col-2">
                                    <label for="masivo_cod5_primary" class="small mb-1">
                                        <i class="fas fa-hashtag"></i> COD5
                                    </label>
                                    <input type="text" id="masivo_cod5_primary" class="form-control" 
                                           placeholder="Código" style="max-width:12ch;">
                                </div>
                                <div class="col-8">
                                    <label for="masivo_diagnostico5_primary" class="small mb-1">
                                        <i class="fas fa-notes-medical"></i> DIAGNOSTICO5
                                    </label>
                                    <div class="input-group">
                                        <input type="text" id="masivo_diagnostico5_primary" class="form-control" 
                                               readonly style="background-color: #f8f9fa;" placeholder="Descripción del diagnóstico">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-sm btn-outline-primary btn-buscar-diagnostico-masivo" 
                                                    title="Buscar diagnóstico" data-numero="5">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-2">
                                    <label for="masivo_cond5_primary" class="small mb-1">
                                        <i class="fas fa-clipboard-check"></i> COND5
                                    </label>
                                    <input type="text" id="masivo_cond5_primary" class="form-control text-center" 
                                           style="max-width:12ch;" maxlength="1" placeholder="COND">
                                </div>
                            </div>
                            
                            <!-- Diagnóstico 6 -->
                            <div class="form-row mb-1">
                                <div class="col-2">
                                    <label for="masivo_cod6_primary" class="small mb-1">
                                        <i class="fas fa-hashtag"></i> COD6
                                    </label>
                                    <input type="text" id="masivo_cod6_primary" class="form-control" 
                                           placeholder="Código" style="max-width:12ch;">
                                </div>
                                <div class="col-8">
                                    <label for="masivo_diagnostico6_primary" class="small mb-1">
                                        <i class="fas fa-notes-medical"></i> DIAGNOSTICO6
                                    </label>
                                    <div class="input-group">
                                        <input type="text" id="masivo_diagnostico6_primary" class="form-control" 
                                               readonly style="background-color: #f8f9fa;" placeholder="Descripción del diagnóstico">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-sm btn-outline-primary btn-buscar-diagnostico-masivo" 
                                                    title="Buscar diagnóstico" data-numero="6">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-2">
                                    <label for="masivo_cond6_primary" class="small mb-1">
                                        <i class="fas fa-clipboard-check"></i> COND6
                                    </label>
                                    <input type="text" id="masivo_cond6_primary" class="form-control text-center" 
                                           style="max-width:12ch;" maxlength="1" placeholder="COND">
                                </div>
                            </div>
                            
                            <!-- Diagnóstico 7 -->
                            <div class="form-row mb-1">
                                <div class="col-2">
                                    <label for="masivo_cod7_primary" class="small mb-1">
                                        <i class="fas fa-hashtag"></i> COD7
                                    </label>
                                    <input type="text" id="masivo_cod7_primary" class="form-control" 
                                           placeholder="Código" style="max-width:12ch;">
                                </div>
                                <div class="col-8">
                                    <label for="masivo_diagnostico7_primary" class="small mb-1">
                                        <i class="fas fa-notes-medical"></i> DIAGNOSTICO7
                                    </label>
                                    <div class="input-group">
                                        <input type="text" id="masivo_diagnostico7_primary" class="form-control" 
                                               readonly style="background-color: #f8f9fa;" placeholder="Descripción del diagnóstico">
                                        <div class="input-group-append">
                                            <button type="button" class="btn btn-sm btn-outline-primary btn-buscar-diagnostico-masivo" 
                                                    title="Buscar diagnóstico" data-numero="7">
                                                <i class="fas fa-search"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-2">
                                    <label for="masivo_cond7_primary" class="small mb-1">
                                        <i class="fas fa-clipboard-check"></i> COND7
                                    </label>
                                    <input type="text" id="masivo_cond7_primary" class="form-control text-center" 
                                           style="max-width:12ch;" maxlength="1" placeholder="COND">
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-dismiss="modal">
                    <i class="fas fa-times"></i> Cancelar
                </button>
                <button type="button" id="btn-limpiar-masivo" class="btn btn-warning">
                    <i class="fas fa-eraser"></i> Limpiar Campos
                </button>
                <button type="button" id="btn-procesar-masivo" class="btn btn-success">
                    <i class="fas fa-check"></i> Ingresar Registros
                </button>
            </div>
        </div>
    </div>
</div>
