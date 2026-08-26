<!-- Modal Buscador de Médicos -->
<div class="modal fade" id="modalBuscadorMedicos" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title">
                    <i class="fas fa-user-md mr-2"></i>Buscar Médico
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <input type="text" class="form-control" id="searchMedico"
                        placeholder="Buscar por código o nombre..." ng-model="searchMedicoText">
                </div>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm table-hover">
                        <thead class="thead-light sticky-top">
                            <tr>
                                <th>Código</th>
                                <th>Nombre</th>
                                <th>Especialidad</th>
                                <th>Jornada</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr ng-repeat="medico in medicosList | filter:searchMedicoText">
                                <td>@{{medico.COD_MED}}</td>
                                <td>@{{medico.NOM_MED}}</td>
                                <td>@{{medico.ESPECIALIDAD}}</td>
                                <td>@{{medico.JORNADA}}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-success"
                                        ng-click="seleccionarMedico(medico)">
                                        <i class="fas fa-check"></i> Seleccionar
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Buscador de Diagnósticos -->
<div class="modal fade" id="modalBuscadorDiagnosticos" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-xl" role="document">
        <div class="modal-content">
            <div class="modal-header bg-info text-white">
                <h5 class="modal-title">
                    <i class="fas fa-stethoscope mr-2"></i>Buscar Diagnóstico
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <input type="text" class="form-control" id="searchDiagnostico"
                        placeholder="Buscar por código o patología..." ng-model="searchDiagnosticoText">
                </div>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm table-hover">
                        <thead class="thead-light sticky-top">
                            <tr>
                                <th>Código</th>
                                <th>Auxiliar</th>
                                <th>Patología</th>
                                <th>Categoría</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr
                                ng-repeat="diagnostico in diagnosticosList | filter:searchDiagnosticoText | limitTo:100">
                                <td>@{{diagnostico.codigo}}</td>
                                <td>@{{diagnostico.auxiliar}}</td>
                                <td>
                                    <div style="max-width: 350px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"
                                        title="@{{diagnostico.patologia}}">
                                        @{{diagnostico.patologia}}
                                    </div>
                                </td>
                                <td>@{{diagnostico.categoria}}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-success"
                                        ng-click="seleccionarDiagnostico(diagnostico)">
                                        <i class="fas fa-check"></i> Seleccionar
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <small class="text-muted">Mostrando máximo 100 resultados. Use el buscador para filtrar.</small>
            </div>
        </div>
    </div>
</div>

<!-- Modal Buscador de Colonias -->
<div class="modal fade" id="modalBuscadorColonias" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title">
                    <i class="fas fa-map-marker-alt mr-2"></i>Buscar Colonia
                </h5>
                <button type="button" class="close" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <input type="text" class="form-control" id="searchColonia"
                        placeholder="Buscar por código o nombre..." ng-model="searchColoniaText">
                </div>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm table-hover">
                        <thead class="thead-light sticky-top">
                            <tr>
                                <th>Código</th>
                                <th>Colonia</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr ng-repeat="colonia in coloniasList | filter:searchColoniaText">
                                <td>@{{colonia.COD_COL}}</td>
                                <td>@{{colonia.COLONIA}}</td>
                                <td>
                                    <button type="button" class="btn btn-sm btn-success"
                                        ng-click="seleccionarColonia(colonia)">
                                        <i class="fas fa-check"></i> Seleccionar
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal Buscador de Referencias -->
<div class="modal fade" id="modalBuscadorReferencias" tabindex="-1" role="dialog">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header bg-indigo-600 text-white">
                <h5 class="modal-title">
                    <i class="fas fa-exchange-alt mr-2"></i>Buscar Establecimiento de Referencia
                </h5>
                <button type="button" class="close text-white" data-dismiss="modal">
                    <span>&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <div class="form-group">
                    <input type="text" class="form-control" id="searchReferencia"
                        placeholder="Buscar establecimiento..." ng-model="searchReferenciaText">
                </div>
                <div class="table-responsive" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm table-hover">
                        <thead class="thead-light sticky-top">
                            <tr>
                                <th>Nombre del Establecimiento</th>
                                <th>Acción</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr ng-repeat="referencia in referenciasList | filter:searchReferenciaText">
                                <td class="font-weight-bold">@{{referencia.nombre}}</td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-primary"
                                            ng-click="seleccionarReferencia(referencia, 'referido_de')">
                                            <i class="fas fa-sign-in-alt mr-1"></i> Referido DE
                                        </button>
                                        <button type="button" class="btn btn-sm btn-success"
                                            ng-click="seleccionarReferencia(referencia, 'referido_a')">
                                            <i class="fas fa-sign-out-alt mr-1"></i> Referido A
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>
