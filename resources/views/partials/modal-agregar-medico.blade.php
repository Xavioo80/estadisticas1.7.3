<!-- Modal para Agregar Médico -->
<div class="modal fade" id="modalAgregarMedico" tabindex="-1" role="dialog" aria-labelledby="modalAgregarMedicoLabel" aria-hidden="true" style="z-index: 9999 !important;">
    <div class="modal-dialog modal-lg" role="document" style="max-width: 800px; margin: 1rem auto; z-index: 10000 !important;">
        <div class="modal-content shadow-lg border-0" style="border-radius: 15px;">
            <!-- Header con gradiente -->
            <div class="modal-header text-white position-relative" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 15px 15px 0 0; padding: 1.5rem;">
                <div class="d-flex align-items-center">
                    <div class="bg-white rounded-circle p-2 mr-3" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center;">
                        <i class="fas fa-user-md text-primary" style="font-size: 1.5rem;"></i>
                    </div>
                    <div>
                        <h4 class="modal-title mb-0 font-weight-bold" id="modalAgregarMedicoLabel">Agregar Nuevo Médico</h4>
                        <small class="opacity-75">Complete la información del profesional médico</small>
                    </div>
                </div>
                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Cerrar" style="font-size: 1.5rem; opacity: 0.8;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <!-- Body con diseño mejorado -->
            <div class="modal-body p-4" style="background: #f8f9fa;">
                <form id="form-agregar-medico">
                    @csrf
                    
                    <!-- Card: Información Personal -->
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                        <div class="card-header bg-white border-0 py-3" style="border-radius: 12px 12px 0 0;">
                            <h6 class="mb-0 text-primary font-weight-bold">
                                <i class="fas fa-user mr-2"></i>Información Personal
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group mb-3">
                                        <label for="medico_codigo" class="font-weight-semibold text-dark" style="font-size: 15px;">Código <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="medico_codigo" name="COD_MED" required maxlength="5" 
                                               style="font-weight: 600; background: #e3f2fd; font-size: 16px;">
                                    </div>
                                </div>
                                <div class="col-md-9">
                                    <div class="form-group mb-3">
                                        <label for="medico_nombre" class="font-weight-semibold text-dark" style="font-size: 15px;">Nombre Completo <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control" id="medico_nombre" name="NOM_MED" required maxlength="100" style="font-size: 16px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Información Laboral -->
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                        <div class="card-header bg-white border-0 py-3" style="border-radius: 12px 12px 0 0;">
                            <h6 class="mb-0 text-success font-weight-bold">
                                <i class="fas fa-briefcase mr-2"></i>Información Laboral
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="medico_especialidad" class="font-weight-semibold text-dark" style="font-size: 15px;">Especialidad <span class="text-danger">*</span></label>
                                        <select class="form-control" id="medico_especialidad" name="ESPECIALIDAD" required style="font-size: 16px;">
                                            <option value="">Seleccionar especialidad</option>
                                            <option value="GINECOLOGIA">GINECOLOGIA</option>
                                            <option value="LICENCIADAS EN ENFERMERIA">LICENCIADAS EN ENFERMERIA</option>
                                            <option value="ENFERMERAS AUXILIARES">ENFERMERAS AUXILIARES</option>
                                            <option value="PEDIATRA">PEDIATRA</option>
                                            <option value="CONSEJERIA">CONSEJERIA</option>
                                            <option value="MEDICO GENERAL">MEDICO GENERAL</option>
                                            <option value="PSICOLOGIA">PSICOLOGIA</option>
                                            <option value="PSIQUIATRA">PSIQUIATRA</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="medico_jornada" class="font-weight-semibold text-dark" style="font-size: 15px;">Jornada <span class="text-danger">*</span></label>
                                        <select class="form-control" id="medico_jornada" name="JORNADA" required style="font-size: 16px;">
                                            <option value="">Seleccionar jornada</option>
                                            <option value="MATUTINA">MATUTINA</option>
                                            <option value="VESPERTINA">VESPERTINA</option>
                                            <option value="FIN DE SEMANA">FIN DE SEMANA</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="medico_nomina" class="font-weight-semibold text-dark" style="font-size: 15px;">Nómina <span class="text-danger">*</span></label>
                                        <select class="form-control" id="medico_nomina" name="NOMINA" required style="font-size: 16px;">
                                            <option value="">Seleccionar nómina</option>
                                            <option value="MEDICO ASISTENCIAL">MEDICO ASISTENCIAL</option>
                                            <option value="ESPECIALISTA">ESPECIALISTA</option>
                                            <option value="LICENCIADA EN ENFERMERIA">LICENCIADA EN ENFERMERIA</option>
                                            <option value="ENFERMERA AUXILIAR">ENFERMERA AUXILIAR</option>
                                            <option value="OTROS">OTROS</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="medico_modalidad" class="font-weight-semibold text-dark" style="font-size: 15px;">Modalidad <span class="text-danger">*</span></label>
                                        <select class="form-control" id="medico_modalidad" name="MODALIDAD" required style="font-size: 16px;">
                                            <option value="">Seleccionar</option>
                                            <option value="PERMANENTE">PERMANENTE</option>
                                            <option value="TEMPORAL">TEMPORAL</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Detalles Contractuales -->
                    <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px;">
                        <div class="card-header bg-white border-0 py-3" style="border-radius: 12px 12px 0 0;">
                            <h6 class="mb-0 text-info font-weight-bold">
                                <i class="fas fa-file-contract mr-2"></i>Detalles Contractuales
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="medico_fecha_ingreso" class="font-weight-semibold text-dark" style="font-size: 15px;">Fecha Ingreso <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="medico_fecha_ingreso" name="FECHA_INGRESO" required style="font-size: 16px;">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="medico_horas" class="font-weight-semibold text-dark" style="font-size: 15px;">Horas <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="medico_horas" name="HORAS_CONTRATADAS" min="0" step="0.5" required style="font-size: 16px;">
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="medico_consultas" class="font-weight-semibold text-dark" style="font-size: 15px;">Consultas</label>
                                        <input type="number" class="form-control" id="medico_consultas" name="NUMERO_CONSULTAS" min="0" step="1" value="0" style="font-size: 16px;">
                                    </div>
                                </div>
                            </div>
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="form-group mb-3">
                                        <label for="medico_estado" class="font-weight-semibold text-dark" style="font-size: 15px;">Estado <span class="text-danger">*</span></label>
                                        <select class="form-control" id="medico_estado" name="estado" required style="font-size: 16px;">
                                            <option value="activo" selected>Activo</option>
                                            <option value="inactivo">Inactivo</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Información de Contacto -->
                    <div class="card border-0 shadow-sm" style="border-radius: 12px;">
                        <div class="card-header bg-white border-0 py-3" style="border-radius: 12px 12px 0 0;">
                            <h6 class="mb-0 text-warning font-weight-bold">
                                <i class="fas fa-address-book mr-2"></i>Información de Contacto
                            </h6>
                        </div>
                        <div class="card-body p-4">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="medico_telefono" class="font-weight-semibold text-dark" style="font-size: 15px;">Teléfono</label>
                                        <input type="tel" class="form-control" id="medico_telefono" name="TELEFONO" maxlength="15" style="font-size: 16px;">
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group mb-3">
                                        <label for="medico_correo" class="font-weight-semibold text-dark" style="font-size: 15px;">Email</label>
                                        <input type="email" class="form-control" id="medico_correo" name="CORREO" maxlength="100" style="font-size: 16px;">
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Footer con botones mejorados -->
            <div class="modal-footer border-0 p-4" style="background: #f8f9fa; border-radius: 0 0 15px 15px;">
                <button type="button" class="btn btn-light border-2 px-4 py-2 mr-2" data-dismiss="modal" 
                        style="border-radius: 25px; font-weight: 600;">
                    <i class="fas fa-times mr-2"></i>Cancelar
                </button>
                <button type="button" class="btn btn-primary px-4 py-2" id="btn-guardar-medico"
                        style="border-radius: 25px; font-weight: 600; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border: none;">
                    <i class="fas fa-save mr-2"></i>Guardar Médico
                </button>
            </div>
        </div>
    </div>
</div>
