<style>
.import-selection-card {
    cursor: pointer;
    transition: all 0.18s cubic-bezier(0.4, 0, 0.2, 1);
    user-select: none;
    border-radius: 8px;
    padding: 10px 14px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    box-sizing: border-box;
}
.import-selection-card.is-selected {
    background: rgba(16, 185, 129, 0.2) !important;
    border: 2px solid #10b981 !important;
    box-shadow: 0 0 14px rgba(16, 185, 129, 0.35) !important;
    color: var(--text-primary, #ffffff) !important;
}
.import-selection-card.is-selected .item-checkbox-icon {
    color: #10b981 !important;
    transform: scale(1.15);
}
.import-selection-card.is-selected .item-badge {
    background-color: #10b981 !important;
    color: #ffffff !important;
    border: 1px solid #059669 !important;
    box-shadow: 0 2px 4px rgba(0,0,0,0.15);
}
.import-selection-card.is-unselected {
    background: var(--bg-surface, #1e293b) !important;
    border: 1px solid var(--border-color, #334155) !important;
    opacity: 0.55;
}
.import-selection-card.is-unselected:hover {
    opacity: 0.9;
    background: var(--bg-subtle, #0f172a) !important;
    border-color: rgba(16, 185, 129, 0.5) !important;
}
.import-selection-card.is-unselected .item-checkbox-icon {
    color: var(--text-muted, #94a3b8) !important;
}
.import-selection-card.is-unselected .item-badge {
    background-color: var(--bg-subtle, rgba(255,255,255,0.05)) !important;
    color: var(--text-muted, #94a3b8) !important;
    border: 1px solid var(--border-color, #334155) !important;
}
</style>

<!-- Modal Importación Profesional de Excel e Histórico Clínico -->
<div class="modal fade" id="modalImportarExcel" tabindex="-1" role="dialog" aria-hidden="true" data-backdrop="static" style="z-index: 2200;">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document" style="max-width: 95%;">
        <div class="modal-content shadow-2xl rounded-xl border-0 overflow-hidden" style="background-color: var(--bg-surface, #ffffff); color: var(--text-primary, #1e293b);">
            
            <!-- Header Modal con Estilo Sing Theme -->
            <div class="modal-header d-flex align-items-center justify-content-between px-4 py-3 border-0"
                 style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff;">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 42px; height: 42px; background: rgba(255,255,255,0.2); border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem;">
                        <i class="fas fa-file-excel"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-weight-bold mb-0 text-white" style="font-size: 1.15rem; letter-spacing: -0.5px;">
                            IMPORTACIÓN DE EXCEL E HISTÓRICO CLÍNICO
                        </h5>
                        <small class="text-white-50" style="font-size: 0.78rem;">
                            Lectura incremental, normalización de colonias/CIE-10 y deduplicación contra histórico
                        </small>
                    </div>
                </div>

                <div class="d-flex align-items-center gap-3">
                    <!-- Wizard Steps Indicator -->
                    <div class="d-none d-md-flex align-items-center gap-2 mr-3" style="font-size: 0.75rem;">
                        <span class="badge badge-pill" id="stepBadge1" style="background: #ffffff; color: #059669; font-weight: 700; padding: 5px 10px;">1. Archivo</span>
                        <i class="fas fa-chevron-right text-white-50" style="font-size: 0.6rem;"></i>
                        <span class="badge badge-pill" id="stepBadge2" style="background: rgba(255,255,255,0.25); color: #ffffff; font-weight: 600; padding: 5px 10px;">2. Fechas</span>
                        <i class="fas fa-chevron-right text-white-50" style="font-size: 0.6rem;"></i>
                        <span class="badge badge-pill" id="stepBadge3" style="background: rgba(255,255,255,0.25); color: #ffffff; font-weight: 600; padding: 5px 10px;">3. Médicos</span>
                        <i class="fas fa-chevron-right text-white-50" style="font-size: 0.6rem;"></i>
                        <span class="badge badge-pill" id="stepBadge4" style="background: rgba(255,255,255,0.25); color: #ffffff; font-weight: 600; padding: 5px 10px;">4. Análisis & Preview</span>
                        <i class="fas fa-chevron-right text-white-50" style="font-size: 0.6rem;"></i>
                        <span class="badge badge-pill" id="stepBadge5" style="background: rgba(255,255,255,0.25); color: #ffffff; font-weight: 600; padding: 5px 10px;">5. Importar</span>
                    </div>

                    <button type="button" class="close text-white opacity-80 hover:opacity-100" data-dismiss="modal" aria-label="Close" style="font-size: 1.5rem; outline: none;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>

            <!-- Body Modal -->
            <div class="modal-body p-4" style="background-color: var(--bg-surface, #ffffff); max-height: 78vh; overflow-y: auto;">

                <!-- ========================================================================= -->
                <!-- PASO 1: SELECCIÓN Y SUBIDA DE ARCHIVO EXCEL -->
                <!-- ========================================================================= -->
                <div id="importWizardStep1" class="wizard-step">
                    <div class="text-center py-4 px-3">
                        <div class="dropzone-excel p-5 rounded-xl border-2 border-dashed mb-4" id="excelDropzone"
                             style="border-color: var(--border-color, #cbd5e1); background: var(--bg-subtle, #f8f9fa); cursor: pointer; transition: all 0.2s;">
                            <i class="fas fa-cloud-upload-alt text-success mb-3" style="font-size: 3rem;"></i>
                            <h5 class="font-weight-bold" style="color: var(--text-primary);">Arrastra tu archivo Excel aquí o haz clic para seleccionarlo</h5>
                            <p class="text-muted mb-3" style="font-size: 0.85rem;">Soporta formatos .xlsx, .xls y .csv de fuentes acumuladas o reportes mensuales</p>
                            <input type="file" id="excelFileInput" accept=".xlsx, .xls, .csv" style="display: none;">
                            <button type="button" class="btn btn-sm btn-outline-success font-weight-bold px-4 py-2" onclick="document.getElementById('excelFileInput').click();">
                                <i class="fas fa-folder-open mr-2"></i> Explorar Archivo
                            </button>
                        </div>

                        <!-- Spinner de lectura inicial -->
                        <div id="uploadAnalyzingSpinner" class="d-none py-4">
                            <div class="spinner-border text-success" role="status" style="width: 3rem; height: 3rem;">
                                <span class="sr-only">Analizando...</span>
                            </div>
                            <h6 class="font-weight-bold mt-3" style="color: var(--text-primary);">Analizando estructura, fechas y médicos del Excel...</h6>
                            <p class="text-muted" style="font-size: 0.8rem;">Esto no insertará ningún registro en la base de datos todavía.</p>
                        </div>
                    </div>
                </div>

                <!-- ========================================================================= -->
                <!-- PASO 2: SELECCIÓN DE FECHAS -->
                <!-- ========================================================================= -->
                <div id="importWizardStep2" class="wizard-step d-none">
                    <!-- Banner de Archivo Cargado -->
                    <div class="p-3 mb-3 rounded-lg d-flex align-items-center justify-content-between"
                         style="background: var(--bg-subtle, #f8f9fa); border: 1px solid var(--border-color, #e2e8f0);">
                        <div class="d-flex align-items-center gap-3">
                            <i class="fas fa-file-alt text-success" style="font-size: 1.8rem;"></i>
                            <div>
                                <span class="font-weight-bold text-truncate d-block" id="loadedFileName" style="max-width: 400px; color: var(--text-primary);">-</span>
                                <small class="text-muted" id="loadedFileMeta">0 KB | 0 Filas encontradas</small>
                            </div>
                        </div>
                        <div id="prevImportAlert" class="d-none badge badge-warning px-3 py-2" style="font-size: 0.78rem;">
                            <i class="fas fa-info-circle mr-1"></i> Este archivo ya fue subido anteriormente. Se verificarán solo registros nuevos.
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div>
                            <h6 class="font-weight-bold mb-0" style="color: var(--text-primary);"><i class="fas fa-calendar-check text-success mr-2"></i>Seleccione las Fechas a Importar</h6>
                            <small class="text-muted">Marque una o varias fechas encontradas en el Excel acumulado.</small>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary" onclick="seleccionarTodasFechas(true)">Seleccionar Todas</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="seleccionarTodasFechas(false)">Deseleccionar</button>
                        </div>
                    </div>

                    <div class="row" id="datesCheckboxContainer" style="max-height: 40vh; overflow-y: auto;">
                        <!-- Checkboxes generados dinámicamente -->
                    </div>
                </div>

                <!-- ========================================================================= -->
                <!-- PASO 3: SELECCIÓN DE MÉDICOS -->
                <!-- ========================================================================= -->
                <div id="importWizardStep3" class="wizard-step d-none">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <div>
                            <h6 class="font-weight-bold mb-0" style="color: var(--text-primary);"><i class="fas fa-user-md text-success mr-2"></i>Seleccione los Médicos</h6>
                            <small class="text-muted">Filtre por los profesionales responsables que desea incorporar.</small>
                        </div>
                        <div class="btn-group btn-group-sm">
                            <button type="button" class="btn btn-outline-secondary" onclick="seleccionarTodosMedicos(true)">Seleccionar Todos</button>
                            <button type="button" class="btn btn-outline-secondary" onclick="seleccionarTodosMedicos(false)">Deseleccionar</button>
                        </div>
                    </div>

                    <div class="row" id="doctorsCheckboxContainer" style="max-height: 40vh; overflow-y: auto;">
                        <!-- Checkboxes generados dinámicamente -->
                    </div>
                </div>

                <!-- ========================================================================= -->
                <!-- PASO 4: ANÁLISIS, NORMALIZACIÓN Y PREVISUALIZACIÓN -->
                <!-- ========================================================================= -->
                <div id="importWizardStep4" class="wizard-step d-none">
                    
                    <!-- Spinner de filtrado y clasificación -->
                    <div id="filterAnalyzingSpinner" class="text-center py-5 d-none">
                        <div class="spinner-border text-success" role="status" style="width: 3.5rem; height: 3.5rem;">
                            <span class="sr-only">Procesando...</span>
                        </div>
                        <h5 class="font-weight-bold mt-3" style="color: var(--text-primary);">Clasificando registros contra el Histórico Clínico...</h5>
                        <p class="text-muted" style="font-size: 0.85rem;">Normalizando colonias, buscando códigos CIE-10 y calculando huellas SHA256 contra duplicados.</p>
                    </div>

                    <!-- Contenido de Resultados y Previsualización -->
                    <div id="previewResultsContainer" class="d-none">
                        
                        <!-- Tarjetas de Resumen Estadístico -->
                        <div class="row mb-3">
                            <div class="col-md-2 col-sm-4 col-6 mb-2">
                                <div class="p-3 rounded-lg text-center" style="background: var(--bg-subtle, #f8f9fa); border: 1px solid var(--border-color, #e2e8f0);">
                                    <span class="d-block text-muted text-xs font-weight-bold uppercase">SELECCIONADOS</span>
                                    <span class="h4 font-weight-bold mb-0" id="statTotalSel" style="color: var(--text-primary);">0</span>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-4 col-6 mb-2">
                                <div class="p-3 rounded-lg text-center" style="background: rgba(16, 185, 129, 0.1); border: 1px solid rgba(16, 185, 129, 0.3);">
                                    <span class="d-block text-success text-xs font-weight-bold uppercase">NUEVOS</span>
                                    <span class="h4 font-weight-bold mb-0 text-success" id="statNuevos">0</span>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-4 col-6 mb-2">
                                <div class="p-3 rounded-lg text-center" style="background: rgba(59, 130, 246, 0.1); border: 1px solid rgba(59, 130, 246, 0.3);">
                                    <span class="d-block text-primary text-xs font-weight-bold uppercase">YA EXISTENTES</span>
                                    <span class="h4 font-weight-bold mb-0 text-primary" id="statExistentes">0</span>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-4 col-6 mb-2">
                                <div class="p-3 rounded-lg text-center" style="background: rgba(245, 158, 11, 0.1); border: 1px solid rgba(245, 158, 11, 0.3);">
                                    <span class="d-block text-warning text-xs font-weight-bold uppercase">DUPLICADOS</span>
                                    <span class="h4 font-weight-bold mb-0 text-warning" id="statDuplicados">0</span>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-4 col-6 mb-2">
                                <div class="p-3 rounded-lg text-center" style="background: rgba(234, 179, 8, 0.1); border: 1px solid rgba(234, 179, 8, 0.3);">
                                    <span class="d-block text-warning text-xs font-weight-bold uppercase">PENDIENTES</span>
                                    <span class="h4 font-weight-bold mb-0 text-warning" id="statPendientes">0</span>
                                </div>
                            </div>
                            <div class="col-md-2 col-sm-4 col-6 mb-2">
                                <div class="p-3 rounded-lg text-center" style="background: rgba(239, 68, 68, 0.1); border: 1px solid rgba(239, 68, 68, 0.3);">
                                    <span class="d-block text-danger text-xs font-weight-bold uppercase">ERRORES</span>
                                    <span class="h4 font-weight-bold mb-0 text-danger" id="statErrores">0</span>
                                </div>
                            </div>
                        </div>

                        <!-- Resumen por Fecha y Médico -->
                        <div class="card mb-3 border-0 shadow-sm" style="background: var(--bg-subtle, #f8f9fa); border: 1px solid var(--border-color, #e2e8f0) !important;">
                            <div class="card-header py-2 px-3 d-flex justify-content-between align-items-center" style="background: transparent; border-bottom: 1px solid var(--border-color, #e2e8f0);">
                                <span class="font-weight-bold text-xs uppercase" style="color: var(--text-primary);"><i class="fas fa-layer-group text-success mr-2"></i>Resumen de Atenciones por Fecha y Médico</span>
                            </div>
                            <div class="card-body p-0 table-responsive" style="max-height: 180px;">
                                <table class="table table-sm table-hover mb-0" style="font-size: 0.8rem;">
                                    <thead>
                                        <tr style="color: var(--text-muted); background: var(--bg-surface);">
                                            <th>FECHA</th>
                                            <th>MÉDICO RESPONSABLE</th>
                                            <th class="text-center" style="color: #38bdf8;"><i class="fas fa-database mr-1"></i>EN BD ACTUAL</th>
                                            <th class="text-center"><i class="fas fa-file-excel text-success mr-1"></i>EN EXCEL</th>
                                            <th class="text-right text-success font-weight-bold">+ A ANEXAR</th>
                                            <th class="text-right text-primary">YA EN BD</th>
                                            <th class="text-right text-warning">PENDIENTES</th>
                                            <th class="text-center font-weight-bold text-success"><i class="fas fa-equals mr-1"></i>TOTAL RESULTANTE EN BD</th>
                                        </tr>
                                    </thead>
                                    <tbody id="summaryTableBody">
                                        <!-- Filas dinámicas -->
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Pestañas de Filtro para la Tabla Detallada -->
                        <div class="d-flex align-items-center justify-content-between mb-2">
                            <ul class="nav nav-pills nav-fill gap-1" id="previewFilterTabs" style="font-size: 0.75rem;">
                                <li class="nav-item"><a class="nav-link active py-1 px-3" href="#" onclick="cambiarFiltroPreview('TODOS', event)">TODOS</a></li>
                                <li class="nav-item"><a class="nav-link py-1 px-3 text-success" href="#" onclick="cambiarFiltroPreview('NUEVO', event)">NUEVOS</a></li>
                                <li class="nav-item"><a class="nav-link py-1 px-3 text-primary" href="#" onclick="cambiarFiltroPreview('YA_EXISTE', event)">YA EXISTENTES</a></li>
                                <li class="nav-item"><a class="nav-link py-1 px-3 text-warning" href="#" onclick="cambiarFiltroPreview('DUPLICADO', event)">DUPLICADOS</a></li>
                                <li class="nav-item"><a class="nav-link py-1 px-3 text-warning" href="#" onclick="cambiarFiltroPreview('PENDIENTE_REVISION', event)">PENDIENTES</a></li>
                                <li class="nav-item"><a class="nav-link py-1 px-3 text-danger" href="#" onclick="cambiarFiltroPreview('ERROR', event)">ERRORES</a></li>
                            </ul>
                            <div id="previewPaginationInfo" class="text-muted" style="font-size: 0.75rem;">
                                Mostrando 0 registros
                            </div>
                        </div>

                        <!-- Tabla Detallada de Previsualización -->
                        <div class="table-responsive rounded-lg border" style="border-color: var(--border-color, #e2e8f0); max-height: 38vh;">
                            <table class="table table-sm table-hover mb-0" style="font-size: 0.78rem; background: var(--bg-surface);">
                                <thead>
                                    <tr style="background: var(--bg-subtle); color: var(--text-primary); border-bottom: 2px solid var(--border-color);">
                                        <th style="width: 40px;" class="text-center">#</th>
                                        <th style="width: 85px;">FECHA</th>
                                        <th style="width: 140px;">MÉDICO</th>
                                        <th style="width: 120px;">DNI</th>
                                        <th style="width: 180px;">PACIENTE</th>
                                        <th style="width: 140px;">COLONIA</th>
                                        <th>DIAGNÓSTICO 1 (CIE-10)</th>
                                        <th style="width: 100px;" class="text-center">ESTADO</th>
                                        <th style="width: 60px;" class="text-center">ACCIÓN</th>
                                    </tr>
                                </thead>
                                <tbody id="detailedPreviewTableBody">
                                    <!-- Filas dinámicas -->
                                </tbody>
                            </table>
                        </div>

                    </div>
                </div>

                <!-- ========================================================================= -->
                <!-- PASO 5: IMPORTANDO / COMPLETADO -->
                <!-- ========================================================================= -->
                <div id="importWizardStep5" class="wizard-step d-none text-center py-5">
                    
                    <div id="importProgressContainer">
                        <div class="spinner-border text-success mb-3" role="status" style="width: 3.5rem; height: 3.5rem;">
                            <span class="sr-only">Importando...</span>
                        </div>
                        <h4 class="font-weight-bold" style="color: var(--text-primary);">Guardando registros en Base de Datos...</h4>
                        <p class="text-muted" style="font-size: 0.85rem;">Actualizando catálogo de pacientes, calculando semanas epidemiológicas y guardando atenciones clínicas.</p>
                        <div class="progress mt-3 mx-auto" style="height: 12px; max-width: 500px; border-radius: 6px;">
                            <div class="progress-bar progress-bar-striped progress-bar-animated bg-success" style="width: 100%;"></div>
                        </div>
                    </div>

                    <div id="importSuccessContainer" class="d-none">
                        <div class="mb-3 text-success" style="font-size: 4rem;">
                            <i class="fas fa-check-circle"></i>
                        </div>
                        <h4 class="font-weight-bold text-success">¡Importación Completada con Éxito!</h4>
                        <p class="text-muted mx-auto mb-4" id="importSuccessMessage" style="max-width: 600px; font-size: 0.95rem;">-</p>
                        <button type="button" class="btn btn-success px-4 py-2 font-weight-bold" data-dismiss="modal" onclick="window.location.reload();">
                            <i class="fas fa-sync-alt mr-2"></i> Finalizar y Refrescar
                        </button>
                    </div>

                </div>

            </div>

            <!-- Footer con Botones de Navegación del Asistente -->
            <div class="modal-footer px-4 py-3 d-flex justify-content-between align-items-center"
                 style="background-color: var(--bg-subtle, #f8f9fa); border-top: 1px solid var(--border-color, #e2e8f0);">
                
                <button type="button" class="btn btn-sm btn-outline-secondary px-3" id="btnWizardPrev" onclick="retrocederPasoWizard()" style="display: none;">
                    <i class="fas fa-arrow-left mr-1"></i> Anterior
                </button>

                <div class="d-flex align-items-center gap-2 ml-auto flex-wrap">
                    <button type="button" class="btn btn-sm btn-secondary px-3" data-dismiss="modal">
                        Cancelar
                    </button>
                    <button type="button" class="btn btn-sm btn-success px-4 font-weight-bold" id="btnWizardNext" onclick="avanzarPasoWizard()" disabled>
                        Siguiente <i class="fas fa-arrow-right ml-1"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-primary px-3 font-weight-bold d-none" id="btnCargarTabla" onclick="ejecutarConfirmacionImportacion('cargar_tabla', false)" title="Cargar todas las filas a la tabla en pantalla para revisarlas y editarlas">
                        <i class="fas fa-table mr-1"></i> Cargar a Tabla AT-1
                    </button>
                    <button type="button" class="btn btn-sm btn-warning text-dark px-3 font-weight-bold d-none" id="btnSobreescribir" onclick="ejecutarConfirmacionImportacion('sobreescribir', false)" title="Reemplazar y sobreescribir las atenciones existentes de esa fecha y médico">
                        <i class="fas fa-sync-alt mr-1"></i> Sobreescribir Día en BD
                    </button>
                    <button type="button" class="btn btn-sm btn-success px-3 font-weight-bold d-none" id="btnConfirmImport" onclick="ejecutarConfirmacionImportacion('anexar', true)" title="Sumar registros nuevos sin borrar los existentes">
                        <i class="fas fa-plus-circle mr-1"></i> Anexar a BD
                    </button>
                </div>
            </div>

        </div>
    </div>
</div>

<!-- Modal Profesional de Edición / Reasignación de Registro -->
<div class="modal fade" id="modalCorregirFila" tabindex="-1" role="dialog" aria-hidden="true" style="z-index: 2300;">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content border-0 shadow-2xl rounded-xl overflow-hidden" style="background: var(--bg-surface, #ffffff); color: var(--text-primary, #1e293b);">
            
            <div class="modal-header py-3 px-4 border-0 d-flex align-items-center justify-content-between"
                 style="background: linear-gradient(135deg, #10b981 0%, #059669 100%); color: #ffffff;">
                <div class="d-flex align-items-center gap-2">
                    <i class="fas fa-edit font-size-18 mr-2"></i>
                    <div>
                        <h6 class="modal-title font-weight-bold mb-0 text-white" id="corregirModalTitle">Editar / Reasignar Registro</h6>
                        <small class="text-white-50" id="corregirModalSubtitle">Asignar diagnóstico y colonia del catálogo institucional</small>
                    </div>
                </div>
                <button type="button" class="close text-white opacity-80" data-dismiss="modal">&times;</button>
            </div>

            <div class="modal-body p-4" style="max-height: 75vh; overflow-y: auto;">
                <input type="hidden" id="corregirRegistroId">

                <!-- Tarjeta de Contexto del Paciente -->
                <div class="p-3 mb-3 rounded-lg d-flex flex-wrap justify-content-between align-items-center"
                     style="background: var(--bg-subtle, #f8f9fa); border: 1px solid var(--border-color, #e2e8f0);">
                    <div class="mr-3 mb-1">
                        <span class="text-xs text-muted font-weight-bold uppercase d-block">PACIENTE</span>
                        <span class="font-weight-bold" id="ctxPacienteNombre" style="color: var(--text-primary);">-</span>
                    </div>
                    <div class="mr-3 mb-1">
                        <span class="text-xs text-muted font-weight-bold uppercase d-block">DNI / IDENTIDAD</span>
                        <span class="font-weight-bold text-success" id="ctxPacienteDni">-</span>
                    </div>
                    <div class="mr-3 mb-1">
                        <span class="text-xs text-muted font-weight-bold uppercase d-block">FECHA ATENCIÓN</span>
                        <span class="font-weight-bold" id="ctxAtencionFecha">-</span>
                    </div>
                    <div class="mb-1">
                        <span class="text-xs text-muted font-weight-bold uppercase d-block">MÉDICO</span>
                        <span class="font-weight-bold text-truncate" id="ctxAtencionMedico" style="max-width: 180px;">-</span>
                    </div>
                </div>

                <!-- SECCIÓN 1: DIAGNÓSTICO -->
                <div class="card mb-3 border-0 shadow-sm" style="background: var(--bg-subtle, #f8f9fa); border: 1px solid var(--border-color, #e2e8f0) !important;">
                    <div class="card-header py-2 px-3 border-0 font-weight-bold text-xs uppercase d-flex justify-content-between align-items-center"
                         style="background: transparent; color: var(--text-primary);">
                        <span><i class="fas fa-stethoscope text-primary mr-2"></i>1. Diagnóstico Clínico (CIE-10)</span>
                        <span class="badge badge-info text-xs" id="dxMatchingStatusBadge">Verificando</span>
                    </div>
                    <div class="card-body p-3">
                        <!-- Diagnóstico Original en Excel -->
                        <div class="mb-3 p-2 rounded border" style="background: var(--bg-surface); border-color: var(--border-color) !important;">
                            <small class="text-muted font-weight-bold d-block mb-1"><i class="fas fa-file-excel text-success mr-1"></i>Texto que viene en el Excel:</small>
                            <span class="font-weight-bold text-warning" id="ctxDiagnosticoOriginal" style="font-size: 0.85rem;">-</span>
                        </div>

                        <!-- Buscador Interactivo de Diagnósticos -->
                        <div class="form-group mb-3">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="font-weight-bold text-xs mb-0" style="color: var(--text-primary);">
                                    Buscar y Asignar del Catálogo (Código o Patología):
                                </label>
                                <button type="button" class="btn btn-xs btn-outline-info font-weight-bold" onclick="abrirBuscadorDiagnosticosParaModal()" title="Abrir catálogo completo en pantalla">
                                    <i class="fas fa-search mr-1"></i> Abrir Catálogo Completo
                                </button>
                            </div>

                            <div class="input-group input-group-sm mb-2">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" style="background: var(--bg-surface); border-color: var(--border-color); color: var(--text-muted);"><i class="fas fa-search"></i></span>
                                </div>
                                <input type="text" id="busquedaDiagnosticoInput" class="form-control form-control-sm font-weight-bold"
                                       placeholder="Escriba código (ej: 53, 55, 4, I10) o nombre de patología..."
                                       autocomplete="off" oninput="filtrarDiagnosticosCatalogo(this.value)" onfocus="filtrarDiagnosticosCatalogo(this.value)">
                                <div class="input-group-append">
                                    <button class="btn btn-outline-secondary" type="button" onclick="limpiarBusquedaDiagnostico()" title="Limpiar"><i class="fas fa-times"></i></button>
                                </div>
                            </div>

                            <!-- Panel de Resultados Dinámicos (Inline visible) -->
                            <div id="diagnosticosDropdownList" class="rounded p-2 border mb-2"
                                 style="max-height: 190px; overflow-y: auto; background: var(--bg-surface); border-color: var(--border-color) !important; display: none;">
                                <!-- Resultados dinámicos -->
                            </div>
                        </div>

                        <!-- Diagnóstico Seleccionado Actual -->
                        <div class="p-2 rounded d-flex align-items-center justify-content-between"
                             style="background: rgba(16, 185, 129, 0.12); border: 1px solid rgba(16, 185, 129, 0.35);">
                            <div>
                                <small class="text-success font-weight-bold d-block"><i class="fas fa-check-circle mr-1"></i>Diagnóstico que se registrará en BD:</small>
                                <span class="font-weight-bold text-success" id="selectedDxLabel" style="font-size: 0.9rem;">Sin asignar</span>
                            </div>
                            <div>
                                <span class="badge badge-success px-2 py-1 font-weight-bold" id="selectedDxCodeBadge" style="font-size: 0.85rem;">Cód: -</span>
                            </div>
                        </div>

                        <input type="hidden" id="corregirCie10Input">
                        <input type="hidden" id="corregirDiagnosticoInput">
                        <input type="hidden" id="corregirDiagnosticoIdInput">
                    </div>
                </div>

                <!-- SECCIÓN 2: COLONIA / PROCEDENCIA -->
                <div class="card mb-0 border-0 shadow-sm" style="background: var(--bg-subtle, #f8f9fa); border: 1px solid var(--border-color, #e2e8f0) !important;">
                    <div class="card-header py-2 px-3 border-0 font-weight-bold text-xs uppercase d-flex justify-content-between align-items-center"
                         style="background: transparent; color: var(--text-primary);">
                        <span><i class="fas fa-map-marker-alt text-danger mr-2"></i>2. Procedencia y Colonia</span>
                    </div>
                    <div class="card-body p-3">
                        <div class="mb-2 p-2 rounded border" style="background: var(--bg-surface); border-color: var(--border-color) !important;">
                            <small class="text-muted font-weight-bold d-block mb-1"><i class="fas fa-file-excel text-success mr-1"></i>Dirección que viene en el Excel:</small>
                            <span class="text-xs" id="ctxDireccionOriginal" style="color: var(--text-primary);">-</span>
                        </div>

                        <div class="form-group mb-2">
                            <label class="font-weight-bold text-xs mb-1" style="color: var(--text-primary);">
                                Buscar Colonia (por Código o Nombre):
                            </label>
                            <div class="input-group input-group-sm">
                                <div class="input-group-prepend">
                                    <span class="input-group-text" style="background: var(--bg-surface); border-color: var(--border-color); color: var(--text-muted);"><i class="fas fa-search"></i></span>
                                </div>
                                <input type="text" id="busquedaColoniaInput" class="form-control form-control-sm font-weight-bold"
                                       placeholder="Escriba código (ej: 13, 21, 1) o nombre de colonia..."
                                       autocomplete="off" oninput="filtrarColoniaPorCodigoONombre(this.value)">
                            </div>
                        </div>

                        <div class="form-group mb-0">
                            <label class="font-weight-bold text-xs mb-1" style="color: var(--text-primary);">Colonia del Catálogo Oficial (Ordenada por Código):</label>
                            <select id="corregirColoniaSelect" class="form-control form-control-sm text-uppercase font-weight-bold" style="background: var(--bg-surface); color: var(--text-primary); border-color: var(--border-color);" onchange="onColoniaSelectChange()">
                                <option value="">-- Seleccionar Colonia Oficial --</option>
                            </select>
                        </div>
                    </div>
                </div>

            </div>

            <div class="modal-footer py-3 px-4 border-0 d-flex justify-content-between" style="background: var(--bg-subtle, #f8f9fa);">
                <button type="button" class="btn btn-sm btn-secondary px-3" data-dismiss="modal">
                    Cancelar
                </button>
                <button type="button" class="btn btn-sm btn-success px-4 font-weight-bold" onclick="guardarCorreccionFila()">
                    <i class="fas fa-save mr-1"></i> Guardar y Asignar
                </button>
            </div>

        </div>
    </div>
</div>

<!-- ========================================================================= -->
<!-- JAVASCRIPT DEL WIZARD DE IMPORTACIÓN -->
<!-- ========================================================================= -->
<script>
    var currentImportWizardStep = 1;
    var currentImportData = {
        importacion_id: null,
        nombre_archivo: '',
        fechas: [],
        medicos: [],
        matriz_fechas_medicos: {},
        stats: {},
        resumen_dia_medico: [],
        filtroPreview: 'TODOS',
        paginaPreview: 1
    };

    // Inicializar eventos de Drag & Drop
    document.addEventListener('DOMContentLoaded', function() {
        var dropzone = document.getElementById('excelDropzone');
        var fileInput = document.getElementById('excelFileInput');

        if (dropzone && fileInput) {
            ['dragenter', 'dragover'].forEach(eventName => {
                dropzone.addEventListener(eventName, function(e) {
                    e.preventDefault();
                    dropzone.style.borderColor = '#10b981';
                    dropzone.style.background = 'rgba(16, 185, 129, 0.05)';
                }, false);
            });

            ['dragleave', 'drop'].forEach(eventName => {
                dropzone.addEventListener(eventName, function(e) {
                    e.preventDefault();
                    dropzone.style.borderColor = 'var(--border-color, #cbd5e1)';
                    dropzone.style.background = 'var(--bg-subtle, #f8f9fa)';
                }, false);
            });

            dropzone.addEventListener('drop', function(e) {
                var dt = e.dataTransfer;
                var files = dt.files;
                if (files.length > 0) {
                    fileInput.files = files;
                    subirYAnalizarExcel(files[0]);
                }
            });

            fileInput.addEventListener('change', function() {
                if (fileInput.files.length > 0) {
                    subirYAnalizarExcel(fileInput.files[0]);
                }
            });
        }
    });

    // Subir y Analizar Archivo (Paso 1 ➔ Paso 2)
    function subirYAnalizarExcel(file) {
        var formData = new FormData();
        formData.append('archivo', file);
        formData.append('_token', '{{ csrf_token() }}');

        document.getElementById('excelDropzone').classList.add('d-none');
        document.getElementById('uploadAnalyzingSpinner').classList.remove('d-none');

        $.ajax({
            url: '{{ route("ingresos.importar.analizar") }}',
            type: 'POST',
            data: formData,
            processData: false,
            contentType: false,
            success: function(res) {
                document.getElementById('uploadAnalyzingSpinner').classList.add('d-none');
                document.getElementById('excelDropzone').classList.remove('d-none');

                if (res.success) {
                    currentImportData.importacion_id = res.importacion_id;
                    currentImportData.nombre_archivo = res.nombre_archivo;
                    currentImportData.fechas = res.fechas || [];
                    currentImportData.medicos = res.medicos || [];
                    currentImportData.matriz_fechas_medicos = res.matriz_fechas_medicos || {};

                    document.getElementById('loadedFileName').innerText = res.nombre_archivo;
                    document.getElementById('loadedFileMeta').innerText = res.tamano_kb + ' KB | ' + res.total_filas + ' Filas válidas detectadas';

                    if (res.ya_importado) {
                        document.getElementById('prevImportAlert').classList.remove('d-none');
                    } else {
                        document.getElementById('prevImportAlert').classList.add('d-none');
                    }

                    renderizarCheckboxesFechas(res.fechas);
                    actualizarMedicosSegunFechasSeleccionadas();

                    irAPasoWizard(2);
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'No se pudo procesar el archivo Excel.' });
                }
            },
            error: function(xhr) {
                document.getElementById('uploadAnalyzingSpinner').classList.add('d-none');
                document.getElementById('excelDropzone').classList.remove('d-none');
                var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error de comunicación con el servidor.';
                Swal.fire({ icon: 'error', title: 'Error al analizar archivo', text: msg });
            }
        });
    }

    // Actualizar Médicos en Paso 3 según las Fechas seleccionadas en Paso 2
    function actualizarMedicosSegunFechasSeleccionadas() {
        var selectedDates = Array.from(document.querySelectorAll('.date-check:checked')).map(c => c.value);
        
        if (!currentImportData.matriz_fechas_medicos || Object.keys(currentImportData.matriz_fechas_medicos).length === 0) {
            renderizarCheckboxesMedicos(currentImportData.medicos || []);
            return;
        }

        var medicosFiltrados = {};
        selectedDates.forEach(function(fechaIso) {
            var medicosEnFecha = currentImportData.matriz_fechas_medicos[fechaIso] || {};
            for (var med in medicosEnFecha) {
                medicosFiltrados[med] = (medicosFiltrados[med] || 0) + medicosEnFecha[med];
            }
        });

        var medicosList = Object.keys(medicosFiltrados).sort().map(function(med) {
            return {
                medico: med,
                total: medicosFiltrados[med]
            };
        });

        renderizarCheckboxesMedicos(medicosList);
    }

    // Renderizar Tarjetas de Selección de Fechas
    function renderizarCheckboxesFechas(fechas) {
        var container = document.getElementById('datesCheckboxContainer');
        container.innerHTML = '';

        if (!fechas || fechas.length === 0) {
            container.innerHTML = '<div class="col-12 text-muted text-center py-3">No se detectaron fechas válidas en el archivo.</div>';
            return;
        }

        fechas.forEach(function(f) {
            var col = document.createElement('div');
            col.className = 'col-md-4 col-sm-6 mb-2';
            col.innerHTML = `
                <div class="import-selection-card is-selected" id="cardDate_${f.fecha_iso}" onclick="toggleSeleccionItem('date', '${f.fecha_iso}')">
                    <div class="d-flex align-items-center gap-2 text-truncate mr-2">
                        <i class="fas fa-check-circle item-checkbox-icon mr-2" id="iconDate_${f.fecha_iso}"></i>
                        <span class="font-weight-bold" style="font-size: 0.88rem;">${f.fecha_formato}</span>
                    </div>
                    <span class="badge badge-pill item-badge font-weight-bold" id="badgeDate_${f.fecha_iso}">${f.total} reg</span>
                    <input type="checkbox" class="date-check d-none" id="chkDate_${f.fecha_iso}" value="${f.fecha_iso}" checked>
                </div>
            `;
            container.appendChild(col);
        });

        actualizarEstadoBotonSiguiente();
    }

    // Renderizar Tarjetas de Selección de Médicos
    function renderizarCheckboxesMedicos(medicos) {
        var container = document.getElementById('doctorsCheckboxContainer');
        container.innerHTML = '';

        if (!medicos || medicos.length === 0) {
            container.innerHTML = '<div class="col-12 text-muted text-center py-4"><i class="fas fa-user-slash fa-2x mb-2 d-block opacity-50"></i>No hay médicos con atenciones en las fechas seleccionadas.</div>';
            actualizarEstadoBotonSiguiente();
            return;
        }

        medicos.forEach(function(m, idx) {
            var col = document.createElement('div');
            col.className = 'col-md-6 mb-2';
            col.innerHTML = `
                <div class="import-selection-card is-selected" id="cardDoc_${idx}" onclick="toggleSeleccionItem('doc', '${idx}')">
                    <div class="d-flex align-items-center gap-2 text-truncate mr-2">
                        <i class="fas fa-check-circle item-checkbox-icon mr-2" id="iconDoc_${idx}"></i>
                        <span class="font-weight-bold text-truncate" title="${m.medico}" style="font-size: 0.85rem;">${m.medico}</span>
                    </div>
                    <span class="badge badge-pill item-badge font-weight-bold ml-2" id="badgeDoc_${idx}">${m.total} reg</span>
                    <input type="checkbox" class="doc-check d-none" id="chkDoc_${idx}" value="${m.medico}" checked>
                </div>
            `;
            container.appendChild(col);
        });

        actualizarEstadoBotonSiguiente();
    }

    // Alternar selección individual con clic en tarjeta
    function toggleSeleccionItem(tipo, id) {
        var prefix = tipo === 'date' ? 'Date' : 'Doc';
        var chk = document.getElementById('chk' + prefix + '_' + id);
        var card = document.getElementById('card' + prefix + '_' + id);
        var icon = document.getElementById('icon' + prefix + '_' + id);

        if (!chk) return;
        chk.checked = !chk.checked;
        aplicarEstiloCard(card, icon, chk.checked);

        if (tipo === 'date') {
            actualizarMedicosSegunFechasSeleccionadas();
        }

        actualizarEstadoBotonSiguiente();
    }

    // Aplicar clase y estilo visual a la tarjeta
    function aplicarEstiloCard(card, icon, isChecked) {
        if (!card) return;
        if (isChecked) {
            card.classList.remove('is-unselected');
            card.classList.add('is-selected');
            if (icon) {
                icon.className = 'fas fa-check-circle item-checkbox-icon mr-2';
            }
        } else {
            card.classList.remove('is-selected');
            card.classList.add('is-unselected');
            if (icon) {
                icon.className = 'far fa-circle item-checkbox-icon mr-2';
            }
        }
    }

    function seleccionarTodasFechas(checked) {
        document.querySelectorAll('.date-check').forEach(function(chk) {
            chk.checked = checked;
            var id = chk.id.replace('chkDate_', '');
            var card = document.getElementById('cardDate_' + id);
            var icon = document.getElementById('iconDate_' + id);
            aplicarEstiloCard(card, icon, checked);
        });
        actualizarMedicosSegunFechasSeleccionadas();
        actualizarEstadoBotonSiguiente();
    }

    function seleccionarTodosMedicos(checked) {
        document.querySelectorAll('.doc-check').forEach(function(chk) {
            chk.checked = checked;
            var id = chk.id.replace('chkDoc_', '');
            var card = document.getElementById('cardDoc_' + id);
            var icon = document.getElementById('iconDoc_' + id);
            aplicarEstiloCard(card, icon, checked);
        });
        actualizarEstadoBotonSiguiente();
    }

    // Control del Wizard
    function irAPasoWizard(paso) {
        currentImportWizardStep = paso;

        if (paso === 3) {
            actualizarMedicosSegunFechasSeleccionadas();
        }

        for (var i = 1; i <= 5; i++) {
            var stepDiv = document.getElementById('importWizardStep' + i);
            var badge = document.getElementById('stepBadge' + i);
            if (stepDiv) {
                if (i === paso) {
                    stepDiv.classList.remove('d-none');
                    if (badge) {
                        badge.style.background = '#ffffff';
                        badge.style.color = '#059669';
                        badge.style.fontWeight = '700';
                    }
                } else {
                    stepDiv.classList.add('d-none');
                    if (badge) {
                        badge.style.background = 'rgba(255,255,255,0.25)';
                        badge.style.color = '#ffffff';
                        badge.style.fontWeight = '600';
                    }
                }
            }
        }

        var btnPrev = document.getElementById('btnWizardPrev');
        var btnNext = document.getElementById('btnWizardNext');
        var btnConfirm = document.getElementById('btnConfirmImport');
        var btnSobreescribir = document.getElementById('btnSobreescribir');
        var btnCargarTabla = document.getElementById('btnCargarTabla');

        if (paso === 1) {
            btnPrev.style.display = 'none';
            btnNext.classList.remove('d-none');
            btnConfirm.classList.add('d-none');
            if (btnSobreescribir) btnSobreescribir.classList.add('d-none');
            if (btnCargarTabla) btnCargarTabla.classList.add('d-none');
        } else if (paso >= 2 && paso <= 3) {
            btnPrev.style.display = 'inline-block';
            btnNext.classList.remove('d-none');
            btnConfirm.classList.add('d-none');
            if (btnSobreescribir) btnSobreescribir.classList.add('d-none');
            if (btnCargarTabla) btnCargarTabla.classList.add('d-none');
            actualizarEstadoBotonSiguiente();
        } else if (paso === 4) {
            btnPrev.style.display = 'inline-block';
            btnNext.classList.add('d-none');
            btnConfirm.classList.remove('d-none');
            if (btnSobreescribir) btnSobreescribir.classList.remove('d-none');
            if (btnCargarTabla) btnCargarTabla.classList.remove('d-none');
        } else if (paso === 5) {
            btnPrev.style.display = 'none';
            btnNext.classList.add('d-none');
            btnConfirm.classList.add('d-none');
            if (btnSobreescribir) btnSobreescribir.classList.add('d-none');
            if (btnCargarTabla) btnCargarTabla.classList.add('d-none');
        }
    }

    function avanzarPasoWizard() {
        if (currentImportWizardStep === 2) {
            irAPasoWizard(3);
        } else if (currentImportWizardStep === 3) {
            ejecutarFiltradoYAnalisis();
        }
    }

    function retrocederPasoWizard() {
        if (currentImportWizardStep > 1) {
            irAPasoWizard(currentImportWizardStep - 1);
        }
    }

    function actualizarEstadoBotonSiguiente() {
        var btnNext = document.getElementById('btnWizardNext');
        if (currentImportWizardStep === 2) {
            var selectedDates = document.querySelectorAll('.date-check:checked');
            btnNext.disabled = (selectedDates.length === 0);
        } else if (currentImportWizardStep === 3) {
            var selectedDocs = document.querySelectorAll('.doc-check:checked');
            btnNext.disabled = (selectedDocs.length === 0);
        } else {
            btnNext.disabled = false;
        }
    }

    // Paso 4: Filtrar registros seleccionados y clasificar contra BD
    function ejecutarFiltradoYAnalisis() {
        if (!currentImportData.importacion_id) {
            Swal.fire({ icon: 'warning', title: 'Sesión no encontrada', text: 'Por favor, seleccione el archivo Excel nuevamente para continuar.' });
            irAPasoWizard(1);
            return;
        }

        irAPasoWizard(4);

        var selectedDates = Array.from(document.querySelectorAll('.date-check:checked')).map(c => c.value);
        var selectedDocs = Array.from(document.querySelectorAll('.doc-check:checked')).map(c => c.value);

        document.getElementById('filterAnalyzingSpinner').classList.remove('d-none');
        document.getElementById('previewResultsContainer').classList.add('d-none');
        document.getElementById('btnConfirmImport').disabled = true;

        $.ajax({
            url: '{{ route("ingresos.importar.filtrar") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                importacion_id: currentImportData.importacion_id,
                fechas: selectedDates,
                medicos: selectedDocs
            },
            success: function(res) {
                document.getElementById('filterAnalyzingSpinner').classList.add('d-none');
                document.getElementById('previewResultsContainer').classList.remove('d-none');

                if (res.success) {
                    currentImportData.stats = res.stats || {};
                    currentImportData.resumen_dia_medico = res.resumen_dia_medico || [];

                    // Actualizar Tarjetas
                    document.getElementById('statTotalSel').innerText = (res.stats.total_seleccionados || 0).toLocaleString();
                    document.getElementById('statNuevos').innerText = (res.stats.nuevos || 0).toLocaleString();
                    document.getElementById('statExistentes').innerText = (res.stats.ya_existentes || 0).toLocaleString();
                    document.getElementById('statDuplicados').innerText = (res.stats.duplicados || 0).toLocaleString();
                    document.getElementById('statPendientes').innerText = (res.stats.pendientes || 0).toLocaleString();
                    document.getElementById('statErrores').innerText = (res.stats.errores || 0).toLocaleString();

                    // Renderizar Resumen Tabla
                    renderizarTablaResumen(res.resumen_dia_medico);

                    // Cargar Previsualización detallada
                    cargarPaginaPrevisualizacion(1);

                    // Habilitar botones de acción
                    actualizarBotonesAccion(res.stats);
                } else {
                    Swal.fire({ icon: 'error', title: 'Error', text: res.message || 'Error al procesar registros.' });
                }
            },
            error: function(xhr) {
                document.getElementById('filterAnalyzingSpinner').classList.add('d-none');
                var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error de comunicación.';
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
            }
        });
    }

    function actualizarBotonesAccion(stats) {
        if (!stats) return;
        var btnConfirm = document.getElementById('btnConfirmImport');
        var btnSobreescribir = document.getElementById('btnSobreescribir');
        var btnCargarTabla = document.getElementById('btnCargarTabla');

        var nuevos = stats.nuevos || 0;
        var totalValidos = (stats.total_seleccionados || 0) - (stats.errores || 0);

        if (btnConfirm) {
            btnConfirm.disabled = (nuevos === 0);
            btnConfirm.innerHTML = `<i class="fas fa-plus-circle mr-1"></i> Anexar Nuevos (${nuevos})`;
        }
        if (btnSobreescribir) {
            btnSobreescribir.disabled = (totalValidos === 0);
            btnSobreescribir.innerHTML = `<i class="fas fa-sync-alt mr-1"></i> Sobreescribir Día (${totalValidos})`;
        }
        if (btnCargarTabla) {
            btnCargarTabla.disabled = (totalValidos === 0);
            btnCargarTabla.innerHTML = `<i class="fas fa-table mr-1"></i> Cargar a Tabla AT-1 (${totalValidos})`;
        }
    }

    function renderizarTablaResumen(resumenList) {
        var tbody = document.getElementById('summaryTableBody');
        tbody.innerHTML = '';

        if (!resumenList || resumenList.length === 0) {
            tbody.innerHTML = '<tr><td colspan="8" class="text-center py-2 text-muted">Sin datos</td></tr>';
            return;
        }

        resumenList.forEach(function(r) {
            var tr = document.createElement('tr');
            var bdActual = r.en_bd_actual || 0;
            var totalExcel = r.total_excel || r.total || 0;
            var nuevas = r.nuevos || 0;
            var existentes = r.existentes || 0;
            var pendientes = r.pendientes || 0;
            var totalResultante = bdActual + nuevas;

            var bdBadge = bdActual > 0
                ? `<span class="badge badge-info px-2 py-1" style="font-size: 0.78rem;"><i class="fas fa-database mr-1"></i>${bdActual} en BD</span>`
                : `<span class="badge badge-light border text-muted px-2 py-1" style="font-size: 0.75rem;">0 en BD (Nueva)</span>`;

            var resultanteBadge = `<span class="badge badge-success font-weight-bold px-2 py-1" style="font-size: 0.8rem;"><i class="fas fa-check mr-1"></i>${totalResultante} atenciones</span>`;

            tr.innerHTML = `
                <td class="font-weight-bold" style="color: var(--text-primary);">${r.fecha}</td>
                <td class="font-weight-bold text-uppercase" style="color: var(--text-primary);">${r.medico}</td>
                <td class="text-center">${bdBadge}</td>
                <td class="text-center font-weight-bold">${totalExcel}</td>
                <td class="text-right font-weight-bold text-success" style="font-size: 0.88rem;">+${nuevas}</td>
                <td class="text-right font-weight-bold text-primary">${existentes}</td>
                <td class="text-right font-weight-bold text-warning">${pendientes}</td>
                <td class="text-center">${resultanteBadge}</td>
            `;
            tbody.appendChild(tr);
        });
    }

    var catalogoDiagnosticos = [];
    var catalogoColonias = [];
    var cachedRows = {};

    // Cargar catálogos institucionales al iniciar el modal
    function cargarCatalogosParaModal() {
        if (catalogoDiagnosticos.length > 0) return;
        $.ajax({
            url: '{{ route("ingresos.importar.catalogos") }}',
            type: 'GET',
            success: function(res) {
                if (res.success) {
                    catalogoDiagnosticos = res.diagnosticos || [];
                    catalogoColonias = res.colonias || [];
                    poblarSelectColonias();
                }
            }
        });
    }

    function poblarSelectColonias() {
        var sel = document.getElementById('corregirColoniaSelect');
        if (!sel) return;

        // Ordenar correlativamente por código numérico de menor a mayor (1, 2, 3, ... 62)
        catalogoColonias.sort(function(a, b) {
            var numA = parseInt(a.cod_col, 10);
            var numB = parseInt(b.cod_col, 10);
            if (!isNaN(numA) && !isNaN(numB)) {
                return numA - numB;
            }
            return String(a.cod_col).localeCompare(String(b.cod_col));
        });

        sel.innerHTML = '<option value="">-- Seleccionar Colonia Oficial --</option>';
        catalogoColonias.forEach(function(c) {
            var opt = document.createElement('option');
            opt.value = c.colonia;
            opt.dataset.codCol = c.cod_col;
            opt.dataset.colId = c.id;
            opt.innerText = (c.cod_col ? `[${c.cod_col}] ` : '') + c.colonia;
            sel.appendChild(opt);
        });
    }

    // Filtrar y Seleccionar Colonia por Código o Nombre
    function filtrarColoniaPorCodigoONombre(term) {
        var cleanTerm = (term || '').trim();
        var sel = document.getElementById('corregirColoniaSelect');
        if (!sel) return;

        if (!cleanTerm) {
            return;
        }

        var cleanLower = cleanTerm.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');

        // 1. Buscar coincidencia exacta por código (ej: "13", "1", "21")
        var match = catalogoColonias.find(function(c) {
            return String(c.cod_col).trim() === cleanTerm;
        });

        // 2. Si no es por código exacto, buscar por nombre de colonia
        if (!match) {
            match = catalogoColonias.find(function(c) {
                var colNom = String(c.colonia || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                return colNom.includes(cleanLower);
            });
        }

        if (match) {
            sel.value = match.colonia;
        }
    }

    function onColoniaSelectChange() {
        var sel = document.getElementById('corregirColoniaSelect');
        var selectedOpt = sel && sel.selectedOptions[0];
        var busqColInput = document.getElementById('busquedaColoniaInput');
        if (busqColInput && selectedOpt) {
            busqColInput.value = selectedOpt.dataset.codCol ? selectedOpt.dataset.codCol : (sel.value || '');
        }
    }

    function abrirBuscadorDiagnosticosParaModal() {
        window.onDiagnosticoSeleccionadoCallback = function(d) {
            seleccionarDiagnosticoItem(d.codigo, d.patologia, d.id);
        };
        if (typeof window.abrirBuscadorDiagnosticos === 'function') {
            window.abrirBuscadorDiagnosticos();
        } else {
            $('#modalBuscadorDiagnosticos').modal('show');
        }
    }

    function limpiarBusquedaDiagnostico() {
        document.getElementById('busquedaDiagnosticoInput').value = '';
        filtrarDiagnosticosCatalogo('');
    }

    // Filtrar Diagnósticos en Vivo con Auto-Detección Instantánea de Código
    function filtrarDiagnosticosCatalogo(term) {
        var container = document.getElementById('diagnosticosDropdownList');
        if (!container) return;

        var cleanTerm = (term || '').trim();
        var cleanLower = cleanTerm.toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');

        // 1. AUTO-MATCH POR CÓDIGO EXACTO (Ej: si el usuario teclea "53", "55", "4", "I10")
        if (cleanTerm) {
            var exactCodeMatch = catalogoDiagnosticos.find(function(d) {
                return String(d.codigo).trim() === cleanTerm || String(d.codigo).toLowerCase() === cleanLower;
            });

            if (exactCodeMatch) {
                document.getElementById('corregirCie10Input').value = exactCodeMatch.codigo;
                document.getElementById('corregirDiagnosticoInput').value = exactCodeMatch.patologia;
                document.getElementById('corregirDiagnosticoIdInput').value = exactCodeMatch.id || '';

                document.getElementById('selectedDxLabel').innerText = exactCodeMatch.patologia;
                document.getElementById('selectedDxCodeBadge').innerText = 'Cód: ' + exactCodeMatch.codigo;

                var statusBadge = document.getElementById('dxMatchingStatusBadge');
                if (statusBadge) {
                    statusBadge.className = 'badge badge-success text-xs font-weight-bold';
                    statusBadge.innerText = 'Asignado: [' + exactCodeMatch.codigo + ']';
                }
            }
        }

        // 2. BUSCAR RESULTADOS COINCIDENTES
        var resultados = [];
        if (!cleanLower) {
            resultados = catalogoDiagnosticos.slice(0, 8);
        } else {
            resultados = catalogoDiagnosticos.filter(function(d) {
                var pat = String(d.patologia || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                var cod = String(d.codigo || '').toLowerCase();
                var cat = String(d.categoria || '').toLowerCase().normalize('NFD').replace(/[\u0300-\u036f]/g, '');
                return cod === cleanLower || cod.startsWith(cleanLower) || pat.includes(cleanLower) || cat.includes(cleanLower);
            }).slice(0, 25);
        }

        container.innerHTML = '';
        if (resultados.length === 0) {
            container.innerHTML = '<div class="text-muted text-xs text-center py-2"><i class="fas fa-info-circle mr-1"></i>No hay coincidencias con "' + escapeHtml(cleanTerm) + '".</div>';
            container.style.display = 'block';
            return;
        }

        container.style.display = 'block';
        resultados.forEach(function(d) {
            var isCurrent = (String(d.codigo) === String(document.getElementById('corregirCie10Input').value));
            var itemDiv = document.createElement('div');
            itemDiv.className = 'p-2 rounded mb-1 d-flex justify-content-between align-items-center ' + (isCurrent ? 'bg-success text-white' : 'border');
            itemDiv.style.cursor = 'pointer';
            itemDiv.style.fontSize = '0.82rem';
            itemDiv.style.transition = 'all 0.15s ease-in-out';
            if (!isCurrent) {
                itemDiv.style.background = 'var(--bg-subtle, #f8f9fa)';
                itemDiv.style.borderColor = 'var(--border-color, #e2e8f0)';
            }

            itemDiv.innerHTML = `
                <div class="text-truncate mr-2">
                    <span class="font-weight-bold">${d.patologia}</span>
                    ${d.categoria ? `<small class="${isCurrent ? 'text-white-50' : 'text-muted'} d-block" style="font-size: 0.72rem;">${d.categoria}</small>` : ''}
                </div>
                <div class="d-flex align-items-center">
                    <span class="badge ${isCurrent ? 'badge-light text-dark' : 'badge-primary'} font-weight-bold px-2 py-1 mr-2" style="font-size: 0.78rem;">${d.codigo}</span>
                    <i class="fas ${isCurrent ? 'fa-check-circle text-white' : 'fa-plus text-primary'}"></i>
                </div>
            `;
            itemDiv.onclick = function() {
                seleccionarDiagnosticoItem(d.codigo, d.patologia, d.id);
            };
            container.appendChild(itemDiv);
        });
    }

    function seleccionarDiagnosticoItem(codigo, patologia, id) {
        document.getElementById('corregirCie10Input').value = codigo;
        document.getElementById('corregirDiagnosticoInput').value = patologia;
        document.getElementById('corregirDiagnosticoIdInput').value = id || '';

        document.getElementById('selectedDxLabel').innerText = patologia;
        document.getElementById('selectedDxCodeBadge').innerText = 'Cód: ' + codigo;
        document.getElementById('busquedaDiagnosticoInput').value = `[${codigo}] ${patologia}`;

        var statusBadge = document.getElementById('dxMatchingStatusBadge');
        if (statusBadge) {
            statusBadge.className = 'badge badge-success text-xs font-weight-bold';
            statusBadge.innerText = 'Asignado: [' + codigo + ']';
        }

        // Actualizar visualización en lista inline
        var container = document.getElementById('diagnosticosDropdownList');
        if (container) {
            filtrarDiagnosticosCatalogo(String(codigo));
        }
    }

    // Formatear fecha limpia DD/MM/YYYY
    function formatearFechaVisual(fechaStr) {
        if (!fechaStr) return '-';
        var str = String(fechaStr).substring(0, 10);
        var parts = str.split('-');
        if (parts.length === 3 && parts[0].length === 4) {
            return `${parts[2]}/${parts[1]}/${parts[0]}`;
        }
        return str;
    }

    // Cargar Previsualización Paginada
    function cargarPaginaPrevisualizacion(page) {
        currentImportData.paginaPreview = page;
        cargarCatalogosParaModal();

        $.ajax({
            url: '{{ route("ingresos.importar.previsualizar") }}',
            type: 'GET',
            data: {
                importacion_id: currentImportData.importacion_id,
                estado: currentImportData.filtroPreview,
                page: page,
                per_page: 50
            },
            success: function(res) {
                var tbody = document.getElementById('detailedPreviewTableBody');
                tbody.innerHTML = '';
                cachedRows = {};

                document.getElementById('previewPaginationInfo').innerText = `Mostrando ${res.data.length} de ${res.total} registros (Página ${res.page} de ${res.last_page || 1})`;

                if (!res.data || res.data.length === 0) {
                    tbody.innerHTML = '<tr><td colspan="9" class="text-center py-3 text-muted">No se encontraron registros para este filtro.</td></tr>';
                    return;
                }

                res.data.forEach(function(row) {
                    cachedRows[row.id] = row;
                    var tr = document.createElement('tr');

                    var badgeColor = 'badge-secondary';
                    if (row.estado === 'NUEVO') badgeColor = 'badge-success';
                    else if (row.estado === 'YA_EXISTE') badgeColor = 'badge-primary';
                    else if (row.estado === 'DUPLICADO') badgeColor = 'badge-warning text-dark';
                    else if (row.estado === 'PENDIENTE_REVISION') badgeColor = 'badge-warning text-dark';
                    else if (row.estado === 'ERROR') badgeColor = 'badge-danger';

                    var dxList = Array.isArray(row.diagnosticos_json) ? row.diagnosticos_json : [];
                    var dx1 = dxList[0] || {};

                    // Diagnóstico Original del Excel
                    var dxExcelText = dx1.original || 'Sin datos';
                    
                    // Diagnóstico Normalizado / Asignado al Catálogo
                    var dxSistemaHtml = '';
                    if (dx1.codigo && dx1.diagnostico) {
                        dxSistemaHtml = `<span class="badge badge-light border font-weight-bold text-dark mr-1" style="font-size: 0.72rem;">${dx1.codigo}</span> <span class="font-weight-bold text-success text-truncate">${dx1.diagnostico}</span>`;
                    } else {
                        dxSistemaHtml = `<span class="text-warning font-weight-bold"><i class="fas fa-exclamation-triangle mr-1"></i>Pendiente de Asignar</span>`;
                    }

                    var dxCombinedHtml = `
                        <div style="font-size: 0.78rem; line-height: 1.25;">
                            <div class="text-muted d-flex align-items-center mb-1 text-truncate" title="Original en Excel: ${escapeHtml(dxExcelText)}">
                                <span class="badge badge-secondary py-0 px-1 mr-1" style="font-size: 0.62rem;">EXCEL</span>
                                <span class="text-truncate">${escapeHtml(dxExcelText)}</span>
                            </div>
                            <div class="d-flex align-items-center text-truncate" title="Asignado a Base de Datos: [${dx1.codigo || '?'}] ${dx1.diagnostico || 'Pendiente'}">
                                <span class="badge ${dx1.codigo ? 'badge-success' : 'badge-warning text-dark'} py-0 px-1 mr-1" style="font-size: 0.62rem;">SISTEMA</span>
                                ${dxSistemaHtml}
                            </div>
                        </div>
                    `;

                    // Botón de Edición para todas las filas
                    var editBtnClass = row.estado === 'PENDIENTE_REVISION' ? 'btn-warning text-dark font-weight-bold' : 'btn-outline-primary';
                    var actionBtn = `<button class="btn btn-xs ${editBtnClass} py-1 px-2 rounded" onclick="abrirModalCorregir(${row.id})" title="Editar / Asignar Diagnóstico y Colonia"><i class="fas fa-edit"></i></button>`;

                    tr.innerHTML = `
                        <td class="text-center text-muted font-weight-bold">${row.fila_excel}</td>
                        <td class="font-weight-bold">${formatearFechaVisual(row.fecha_atencion)}</td>
                        <td class="text-truncate text-uppercase" style="max-width: 140px;" title="${row.medico || ''}">${row.medico || '-'}</td>
                        <td class="font-weight-bold">${row.numero_identidad || '-'}</td>
                        <td class="text-truncate text-uppercase" style="max-width: 170px;" title="${row.nombre_paciente || ''}">${row.nombre_paciente || '-'}</td>
                        <td class="text-truncate text-uppercase" style="max-width: 130px;" title="${row.colonia_normalizada || ''}">${row.colonia_normalizada || '-'}</td>
                        <td style="max-width: 250px;">${dxCombinedHtml}</td>
                        <td class="text-center"><span class="badge ${badgeColor} px-2 py-1" title="${escapeHtml(row.motivo_estado || row.estado)}" style="cursor: help;">${row.estado}</span></td>
                        <td class="text-center">${actionBtn}</td>
                    `;
                    tbody.appendChild(tr);
                });
            }
        });
    }

    function cambiarFiltroPreview(estado, e) {
        if (e) e.preventDefault();
        currentImportData.filtroPreview = estado;

        document.querySelectorAll('#previewFilterTabs .nav-link').forEach(function(a) {
            a.classList.remove('active');
        });
        if (e && e.target) {
            e.target.classList.add('active');
        }

        cargarPaginaPrevisualizacion(1);
    }

    // Modal de Edición y Asignación de Registro
    function abrirModalCorregir(id) {
        var row = cachedRows[id];
        if (!row) return;

        cargarCatalogosParaModal();

        document.getElementById('corregirRegistroId').value = row.id;
        document.getElementById('corregirModalTitle').innerText = `Editar / Reasignar Registro (Fila #${row.fila_excel})`;
        
        // Contexto del paciente
        document.getElementById('ctxPacienteNombre').innerText = row.nombre_paciente || '-';
        document.getElementById('ctxPacienteDni').innerText = row.numero_identidad || '-';
        document.getElementById('ctxAtencionFecha').innerText = formatearFechaVisual(row.fecha_atencion);
        document.getElementById('ctxAtencionMedico').innerText = row.medico || '-';

        // Diagnóstico Contexto
        var dxList = Array.isArray(row.diagnosticos_json) ? row.diagnosticos_json : [];
        var dx1 = dxList[0] || {};
        document.getElementById('ctxDiagnosticoOriginal').innerText = dx1.original || 'Sin datos en Excel';

        var cie10 = dx1.codigo || '';
        var dxPatologia = dx1.diagnostico || '';
        var dxId = dx1.diagnostico_id || '';

        document.getElementById('corregirCie10Input').value = cie10;
        document.getElementById('corregirDiagnosticoInput').value = dxPatologia;
        document.getElementById('corregirDiagnosticoIdInput').value = dxId;

        document.getElementById('selectedDxLabel').innerText = dxPatologia || 'Sin asignar';
        document.getElementById('selectedDxCodeBadge').innerText = cie10 ? ('Cód: ' + cie10) : 'Cód: -';
        document.getElementById('busquedaDiagnosticoInput').value = cie10 ? `[${cie10}] ${dxPatologia}` : '';

        var statusBadge = document.getElementById('dxMatchingStatusBadge');
        if (statusBadge) {
            if (cie10 && dxPatologia && dx1.coincidencia_exacta) {
                statusBadge.className = 'badge badge-success text-xs';
                statusBadge.innerText = 'Asignado';
            } else {
                statusBadge.className = 'badge badge-warning text-dark text-xs';
                statusBadge.innerText = 'Requiere Asignación';
            }
        }

        // Colonia Contexto
        document.getElementById('ctxDireccionOriginal').innerText = row.direccion_original || 'No especificada en Excel';
        var colSel = document.getElementById('corregirColoniaSelect');
        if (colSel) {
            colSel.value = row.colonia_normalizada || '';
        }
        var busqColInput = document.getElementById('busquedaColoniaInput');
        if (busqColInput) {
            var selectedOpt = colSel && colSel.selectedOptions[0];
            busqColInput.value = selectedOpt && selectedOpt.dataset.codCol ? selectedOpt.dataset.codCol : (row.cod_col || '');
        }

        filtrarDiagnosticosCatalogo(cie10 || '');
        $('#modalCorregirFila').modal('show');
    }

    function guardarCorreccionFila() {
        var id = document.getElementById('corregirRegistroId').value;
        var colSel = document.getElementById('corregirColoniaSelect');
        var colNormalizada = colSel ? colSel.value : '';
        var selectedOpt = colSel && colSel.selectedOptions[0];
        var codCol = selectedOpt ? selectedOpt.dataset.codCol : null;
        var colId = selectedOpt ? selectedOpt.dataset.colId : null;

        if (!codCol && colNormalizada && typeof catalogoColonias !== 'undefined') {
            var colFound = catalogoColonias.find(function(c) { return c.colonia === colNormalizada; });
            if (colFound) {
                codCol = colFound.cod_col;
                colId = colFound.id;
            }
        }

        var cie10 = document.getElementById('corregirCie10Input').value.trim();
        var dx = document.getElementById('corregirDiagnosticoInput').value.trim();
        var dxId = document.getElementById('corregirDiagnosticoIdInput').value.trim();

        if ((!cie10 || !dx) && typeof catalogoDiagnosticos !== 'undefined') {
            var dxFound = catalogoDiagnosticos.find(function(d) { return String(d.codigo).trim() === cie10 || d.patologia === dx; });
            if (dxFound) {
                cie10 = dxFound.codigo;
                dx = dxFound.patologia;
                dxId = dxFound.id;
            }
        }

        if (!dx || !cie10) {
            Swal.fire({ icon: 'warning', title: 'Diagnóstico Requerido', text: 'Por favor, busque y seleccione un diagnóstico del catálogo oficial antes de guardar.' });
            return;
        }

        $.ajax({
            url: '{{ route("ingresos.importar.corregir") }}',
            type: 'POST',
            data: {
                _token: '{{ csrf_token() }}',
                registro_id: id,
                colonia_normalizada: colNormalizada,
                cod_col: codCol,
                colonia_id: colId,
                codigo: cie10,
                diagnostico: dx,
                diagnostico_id: dxId
            },
            success: function(res) {
                $('#modalCorregirFila').modal('hide');
                Swal.fire({ toast: true, position: 'top-end', icon: 'success', title: 'Registro actualizado y asignado', showConfirmButton: false, timer: 1500 });

                if (res.stats) {
                    currentImportData.stats = res.stats;
                    document.getElementById('statTotalSel').innerText = (res.stats.total_seleccionados || 0).toLocaleString();
                    document.getElementById('statNuevos').innerText = (res.stats.nuevos || 0).toLocaleString();
                    document.getElementById('statExistentes').innerText = (res.stats.ya_existentes || 0).toLocaleString();
                    document.getElementById('statDuplicados').innerText = (res.stats.duplicados || 0).toLocaleString();
                    document.getElementById('statPendientes').innerText = (res.stats.pendientes || 0).toLocaleString();
                    document.getElementById('statErrores').innerText = (res.stats.errores || 0).toLocaleString();

                    actualizarBotonesAccion(res.stats);
                }

                cargarPaginaPrevisualizacion(currentImportData.paginaPreview);
            },
            error: function(xhr) {
                var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Error al guardar cambios.';
                Swal.fire({ icon: 'error', title: 'Error', text: msg });
            }
        });
    }

    // Paso 5: Confirmar e Importar Transaccionalmente (accion: 'anexar' | 'sobreescribir' | 'cargar_tabla')
    function ejecutarConfirmacionImportacion(accion = 'anexar', soloNuevos = true) {
        var stats = currentImportData.stats || {};
        var countNuevos = stats.nuevos || 0;
        var countTodos = (stats.total_seleccionados || 0) - (stats.errores || 0);

        var titulo = '';
        var texto = '';
        var btnColor = '#10b981';
        var btnText = 'Sí, continuar';

        if (accion === 'sobreescribir') {
            titulo = `¿Sobreescribir Día del Médico con ${countTodos} atenciones?`;
            texto = '¡Atención! Se eliminarán las atenciones previas en BD para esta fecha y médico, reemplazándolas con los registros de este archivo Excel.';
            btnColor = '#f59e0b';
            btnText = '<i class="fas fa-sync-alt mr-1"></i> Sí, sobreescribir día';
        } else if (accion === 'cargar_tabla') {
            titulo = `¿Cargar ${countTodos} registros a la tabla AT-1?`;
            texto = 'Se trasladarán todas las atenciones a la cuadrícula de Ingresos AT-1 en pantalla para revisarlas y editarlas antes de guardar.';
            btnColor = '#3b82f6';
            btnText = '<i class="fas fa-table mr-1"></i> Sí, cargar a tabla';
        } else {
            titulo = `¿Anexar ${countNuevos} registros nuevos?`;
            texto = 'Se sumarán los registros nuevos al histórico clínico sin alterar los registros existentes.';
            btnColor = '#10b981';
            btnText = '<i class="fas fa-plus-circle mr-1"></i> Sí, anexar nuevos';
        }

        Swal.fire({
            title: titulo,
            text: texto,
            icon: (accion === 'sobreescribir' ? 'warning' : 'question'),
            showCancelButton: true,
            confirmButtonColor: btnColor,
            cancelButtonColor: '#64748b',
            confirmButtonText: btnText,
            cancelButtonText: 'Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                irAPasoWizard(5);
                document.getElementById('importProgressContainer').classList.remove('d-none');
                document.getElementById('importSuccessContainer').classList.add('d-none');

                $.ajax({
                    url: '{{ route("ingresos.importar.confirmar") }}',
                    type: 'POST',
                    data: {
                        _token: '{{ csrf_token() }}',
                        importacion_id: currentImportData.importacion_id,
                        solo_nuevos: soloNuevos ? 1 : 0,
                        modo: (accion === 'sobreescribir' ? 'sobreescribir' : 'anexar')
                    },
                    success: function(res) {
                        document.getElementById('importProgressContainer').classList.add('d-none');
                        document.getElementById('importSuccessContainer').classList.remove('d-none');
                        document.getElementById('importSuccessMessage').innerText = res.message || 'Operación completada exitosamente.';

                        // Inyectar registros directamente en la tabla principal de Ingresos AT-1
                        if (res.filas_tabla && res.filas_tabla.length > 0) {
                            if (typeof window.cargarRegistrosImportadosATabla === 'function') {
                                window.cargarRegistrosImportadosATabla(res.filas_tabla);
                            }
                        }

                        setTimeout(function() {
                            $('#modalImportarExcel').modal('hide');
                            Swal.fire({
                                icon: 'success',
                                title: (accion === 'sobreescribir' ? '¡Día Sobreescrito y Cargado!' : '¡Registros Procesados!'),
                                text: `Se han procesado ${res.total_importados} atenciones y se encuentran visibles en la tabla de Ingresos AT-1.`,
                                confirmButtonColor: '#10b981',
                                confirmButtonText: 'Ver en Tabla'
                            });
                        }, 1000);
                    },
                    error: function(xhr) {
                        document.getElementById('importProgressContainer').classList.add('d-none');
                        var msg = xhr.responseJSON ? xhr.responseJSON.message : 'Ocurrió un error en la importación.';
                        Swal.fire({ icon: 'error', title: 'Error en la importación', text: msg });
                        irAPasoWizard(4);
                    }
                });
            }
        });
    }

    function escapeHtml(text) {
        if (!text) return '';
        return String(text).replace(/"/g, '&quot;').replace(/'/g, '&#39;');
    }
</script>
