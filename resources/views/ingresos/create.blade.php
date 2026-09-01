@extends('layouts.app')

@section('title', 'Ingresos AT-1 - Nuevo Registro')

@push('styles')
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
<style>
  html, body, .app-wrapper, .app-main, .app-content, .container-fluid, .content-header, .page-fade-in {
    background-color: var(--bg-body) !important;
    color: var(--text-primary) !important;
  }
  .app-footer {
    display: none !important;
  }
  .app-content {
    padding: 0.35rem 0.5rem 0.25rem !important;
    height: calc(100vh - var(--navbar-height, 56px)) !important;
    max-height: calc(100vh - var(--navbar-height, 56px)) !important;
    display: flex !important;
    flex-direction: column !important;
    overflow: hidden !important;
  }
  body {
    overflow: hidden !important;
  }
</style>
@endpush

@section('content')
<div class="d-flex flex-column flex-grow-1" style="width: 100%; height: 100%; min-height: 0;">
    <div class="d-flex flex-column flex-grow-1" style="width: 100%; max-width: 100%; height: 100%; min-height: 0; padding: 0;">
        <div ng-app="TablaDemo" ng-cloak ng-controller="TablaCtrl" class="d-flex flex-column flex-grow-1" style="height: 100%; min-height: 0;">
            <div class="page-fade-in d-flex flex-column flex-grow-1" style="height: 100%; min-height: 0;">
                <!-- Header dentro del scope de Angular -->
                <div class="content-header p-0 mb-1 pt-0" style="flex-shrink: 0;">
                    <div class="container-fluid px-0">
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                            <div class="d-flex align-items-center gap-2">
                                <a href="{{ route('ingresos.index') }}"
                                   title="Volver a Ingresos AT-1"
                                   style="
                                       display: inline-flex; align-items: center; justify-content: center;
                                       width: 32px; height: 32px; border-radius: 8px;
                                       background: var(--bg-surface); border: 1px solid var(--border-color);
                                       color: var(--text-secondary); text-decoration: none;
                                       transition: all 0.18s ease; flex-shrink: 0;
                                   "
                                   onmouseover="this.style.background='var(--bg-surface-hover)'; this.style.color='var(--color-primary)'; this.style.borderColor='var(--color-primary)';"
                                   onmouseout="this.style.background='var(--bg-surface)'; this.style.color='var(--text-secondary)'; this.style.borderColor='var(--border-color)';">
                                    <i class="bi bi-arrow-left" style="font-size: 1rem;"></i>
                                </a>
                                <h1 class="mb-0 text-xl font-bold" style="color: var(--text-primary) !important;">Ingresos AT-1</h1>
                            </div>

                            <div class="d-flex align-items-center flex-wrap gap-2 ml-auto">
                                <!-- Card Médico Dinámico -->
                                <div class="compact-stat-card stat-card-medico"
                                     style="cursor: default;"
                                     title="@{{ getResumenMedicos().tooltip }}">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon">
                                            <i class="fas fa-user-md"></i>
                                        </div>
                                        <div class="stat-details ml-2">
                                            <span class="stat-label">@{{ getResumenMedicos().label }}</span>
                                            <span class="stat-value text-truncate"
                                                style="font-weight: 800; font-size: 0.95rem; color: var(--text-primary); display: block; text-transform: uppercase; max-width: 340px;"
                                                title="@{{ getResumenMedicos().tooltip }}">
                                                @{{ getResumenMedicos().valor }}
                                            </span>
                                        </div>
                                    </div>
                                    <div class="stat-count text-success" title="Total de filas en la tabla">
                                        @{{ lista.length }}
                                    </div>
                                </div>

                                <!-- Card Fecha Dinámica -->
                                <div class="compact-stat-card stat-card-fecha"
                                     style="min-width: 190px; cursor: default;"
                                     title="@{{ getResumenFechas().tooltip }}">
                                    <div class="d-flex align-items-center">
                                        <div class="stat-icon">
                                            <i class="fas fa-calendar-alt"></i>
                                        </div>
                                        <div class="stat-details ml-2">
                                            <span class="stat-label">@{{ getResumenFechas().label }}</span>
                                            <span class="stat-value text-truncate"
                                                style="font-weight: 800; font-size: 0.95rem; color: var(--text-primary); display: block; max-width: 220px;"
                                                title="@{{ getResumenFechas().tooltip }}">
                                                @{{ getResumenFechas().valor }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="container-fluid px-0 d-flex flex-column flex-grow-1" style="min-height: 0; flex: 1 1 0%;">
                    <div class="card shadow d-flex flex-column flex-grow-1 mb-0" style="height: 100%; min-height: 0; background-color: var(--bg-surface) !important; border: 1px solid var(--border-color) !important;">
                        <div class="card-body p-0 flex-grow-1 position-relative overflow-hidden" style="min-height: 0; background-color: var(--bg-surface) !important;">
                            <div class="table-responsive position-absolute top-0 start-0 end-0 bottom-0" style="background-color: var(--bg-surface) !important; overflow: auto !important;">
                                <table class="table table-bordered table-hover table-sm mb-0 table-ingresos spreadsheet-table"
                                    style="table-layout: fixed; width: max-content !important; min-width: 100% !important; background-color: var(--bg-surface) !important;">
                                    <thead class="thead-dark-custom shadow-sm">
                                        <tr>
                                            <th class="text-center sticky-col text-white"
                                                style="width: 32px; min-width: 32px; max-width: 32px;">#</th>
                                            @foreach($columns as $col)
                                                @php
                                                    $width = '80px';
                                                    $align = 'text-left';
                                                    $title = strtoupper(str_replace('_', ' ', $col));

                                                    switch ($col) {
                                                        case 'numero':
                                                            $width = '52px';
                                                            $align = 'text-center';
                                                            break;
                                                        case 'ano':
                                                            $width = '48px';
                                                            $title = 'AÑO';
                                                            $align = 'text-center';
                                                            break;
                                                        case 'mes':
                                                            $width = '75px';
                                                            $align = 'text-center';
                                                            break;
                                                        case 'cm':
                                                            $width = '42px';
                                                            $align = 'text-center';
                                                            break;
                                                        case 'medico':
                                                            $width = '210px';
                                                            $align = 'text-left';
                                                            break;
                                                        case 'prof':
                                                            $width = '125px';
                                                            $align = 'text-left';
                                                            break;
                                                        case 'fecha':
                                                            $width = '85px';
                                                            $align = 'text-center';
                                                            break;
                                                        case 'se':
                                                            $width = '36px';
                                                            $align = 'text-center';
                                                            break;
                                                        case 'exp':
                                                            $width = '65px';
                                                            $align = 'text-center';
                                                            break;
                                                        case 'identidad':
                                                            $width = '130px';
                                                            $title = 'DNI';
                                                            $align = 'text-center';
                                                            break;
                                                        case 'nombre_paciente':
                                                            $width = '220px';
                                                            $title = 'NOMBRE PACIENTE';
                                                            $align = 'text-left';
                                                            break;
                                                        case 'fecha_nacimiento':
                                                            $width = '100px';
                                                            $title = 'F. NACIMIENTO';
                                                            $align = 'text-center';
                                                            break;
                                                        case 'sexo':
                                                            $width = '42px';
                                                            $align = 'text-center';
                                                            break;
                                                        case 'edad':
                                                            $width = '42px';
                                                            $align = 'text-center';
                                                            break;
                                                        case 'tipo':
                                                            $width = '42px';
                                                            $align = 'text-center';
                                                            break;
                                                        case 'rango':
                                                            $width = '125px';
                                                            $align = 'text-left';
                                                            break;
                                                        case 'rango_2':
                                                        case 'rango_5':
                                                            $width = '120px';
                                                            $align = 'text-left';
                                                            break;
                                                        case 'rango_3':
                                                            $width = '95px';
                                                            $align = 'text-left';
                                                            break;
                                                        case 'rango_4':
                                                            $width = '90px';
                                                            $align = 'text-left';
                                                            break;
                                                        case 'cond':
                                                            $width = '45px';
                                                            $align = 'text-center';
                                                            break;
                                                        case 'cod_col':
                                                            $width = '60px';
                                                            $align = 'text-center';
                                                            break;
                                                        case 'colonia':
                                                            $width = '160px';
                                                            $align = 'text-left';
                                                            break;
                                                        case 'sg':
                                                            $width = '38px';
                                                            $align = 'text-center';
                                                            break;
                                                        case 'referido_a':
                                                        case 'referido_de':
                                                            $width = '95px';
                                                            $align = 'text-left';
                                                            break;
                                                        case 'pg_emb':
                                                            $width = '80px';
                                                            $align = 'text-center';
                                                            break;
                                                        case 'jornada':
                                                            $width = '75px';
                                                            $align = 'text-center';
                                                            break;
                                                        case 'sm':
                                                            $width = '42px';
                                                            $align = 'text-center';
                                                            break;
                                                        default:
                                                            if (strpos($col, 'cod_') === 0 && $col !== 'cod_col') {
                                                                $width = '50px';
                                                                $align = 'text-center';
                                                            } else if (strpos($col, 'diagnostico_') === 0) {
                                                                $width = '200px';
                                                                $align = 'text-left';
                                                            } else if (strpos($col, 'cond_') === 0) {
                                                                $width = '45px';
                                                                $align = 'text-center';
                                                            }
                                                    }
                                                @endphp
                                                <th class="text-uppercase {{ $align }} text-truncate text-white"
                                                    style="width: {{ $width }}; min-width: {{ $width }}; max-width: {{ $width }};"
                                                    title="{{ $title }}">
                                                    {{ $title }}
                                                </th>
                                            @endforeach
                                            <th class="text-center" style="width: 32px; min-width: 32px; max-width: 32px;">✕</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr ng-repeat="item in lista">
                                            <td class="text-center font-weight-bold sticky-col"
                                                ng-class="{'active-row': $index === filaSeleccionada}"
                                                ng-bind="$index + 1"></td>
                                            @foreach($columns as $col)
                                                @php
                                                    $align = 'text-left';
                                                    switch ($col) {
                                                        case 'numero':
                                                        case 'ano':
                                                        case 'mes':
                                                        case 'cm':
                                                        case 'fecha':
                                                        case 'se':
                                                        case 'exp':
                                                        case 'identidad':
                                                        case 'fecha_nacimiento':
                                                        case 'sexo':
                                                        case 'edad':
                                                        case 'tipo':
                                                        case 'cond':
                                                        case 'cod_col':
                                                        case 'sg':
                                                        case 'pg_emb':
                                                        case 'jornada':
                                                        case 'sm':
                                                            $align = 'text-center';
                                                            break;
                                                        default:
                                                            if (strpos($col, 'cod_') === 0 && $col !== 'cod_col') $align = 'text-center';
                                                            else if (strpos($col, 'cond_') === 0) $align = 'text-center';
                                                    }
                                                @endphp
                                                 <td editable-td row="@{{$index}}" field="{{ $col }}" class="{{ $align }}"></td>
                                            @endforeach
                                            <td class="text-center p-0" style="vertical-align: middle;">
                                                <button class="btn btn-xs btn-outline-danger p-0 d-inline-flex align-items-center justify-content-center" ng-click="eliminar($index)"
                                                    title="Eliminar fila"
                                                    style="width: 18px; height: 18px; font-size: 0.60rem;">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer py-1 px-2 border-top" style="flex-shrink: 0; background-color: var(--bg-surface) !important; border-color: var(--border-color) !important;">
                            <div class="btn-group">
                                <button class="btn btn-sm btn-success px-3" ng-click="agregar()">
                                    <i class="fas fa-plus mr-1"></i> Agregar Fila
                                </button>
                                <button type="button" class="btn btn-sm btn-warning px-3 text-dark font-weight-bold" data-toggle="modal"
                                    data-target="#modalIngresoMasivo">
                                    <i class="fas fa-layer-group mr-1"></i> Ingresar Datos
                                </button>
                                <button type="button" class="btn btn-sm btn-info px-3 text-white font-weight-bold" data-toggle="modal"
                                    data-target="#modalImportarExcel" title="Importar registros clínicos desde archivo Excel">
                                    <i class="fas fa-file-excel mr-1"></i> Importar Excel
                                </button>
                                <button type="button" class="btn btn-sm btn-danger px-3" ng-click="limpiarTabla()">
                                    <i class="fas fa-trash-alt mr-1"></i> Limpiar Tabla
                                </button>

                                <button type="button" class="btn btn-sm px-3"
                                    ng-class="{'btn-primary': !tablaGuardada, 'btn-success': tablaGuardada}"
                                    ng-click="guardarDatos()" ng-disabled="tablaGuardada"
                                    title="@{{ tablaGuardada ? 'Los datos actuales ya están guardados' : 'Guardar cambios' }}">
                                    <i class="fas"
                                        ng-class="{'fa-save': !tablaGuardada, 'fa-check': tablaGuardada}"></i>
                                    @{{ tablaGuardada ? 'Datos Guardados' : 'Guardar' }}
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    ng-click="abrirBuscadorMedicos()" title="Buscar Médico (Alt+M)">
                                    <i class="fas fa-user-md mr-1"></i> Médicos <span
                                        class="badge badge-theme ml-1">Alt+M</span>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    ng-click="abrirBuscadorDiagnosticos()" title="Buscar Diagnóstico (Alt+D)">
                                    <i class="fas fa-stethoscope mr-1"></i> Diagnósticos <span
                                        class="badge badge-theme ml-1">Alt+D</span>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    ng-click="abrirBuscadorColonias()" title="Buscar Colonia (Alt+C)">
                                    <i class="fas fa-map-marker-alt mr-1"></i> Colonias <span
                                        class="badge badge-theme ml-1">Alt+C</span>
                                </button>
                                <button type="button" class="btn btn-sm btn-outline-secondary"
                                    ng-click="abrirBuscadorReferencias()" title="Buscar Referencia (Alt+R)">
                                    <i class="fas fa-exchange-alt mr-1"></i> Referencias <span
                                        class="badge badge-theme ml-1">Alt+R</span>
                                </button>
                                <a href="{{ route('diagnosticos.condicionamientos') }}" class="btn btn-sm btn-success"
                                    target="_blank" title="Configurar validaciones de diagnósticos">
                                    <i class="fas fa-shield-alt mr-1"></i> Condicionamientos
                                </a>
                            </div>

                            <div class="mt-4" id="json-output-container" style="display: none;">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <h5 class="mb-0 text-muted"><i class="fas fa-database mr-2"></i>Estructura de Datos:
                                    </h5>
                                    <button class="btn btn-sm btn-outline-secondary"
                                        onclick="document.getElementById('json-output-container').style.display='none'">Cerrar</button>
                                </div>
                                <pre id="JSON" class="p-3 bg-dark text-success rounded shadow-inner"
                                    style="border: 1px solid #444;"></pre>
                            </div>
                        </div>
                    </div>
                </div> <!-- Fin page-fade-in -->
                <!-- Modal Ingreso Masivo -->
                <div class="modal fade" id="modalIngresoMasivo" tabindex="-1" role="dialog" aria-hidden="true"
                    style="z-index: 9999;">
                    <div class="modal-dialog modal-lg" role="document" style="z-index: 10000;">
                        <div class="modal-content border-0">
                            <div class="modal-header text-white bg-success">
                                <h5 class="modal-title font-weight-bold"><i class="fas fa-edit mr-2"></i>Datos para
                                    Ingreso Masivo</h5>
                                <button type="button" class="close text-white" data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body p-2">
                                <!-- Fila 1: Datos Generales -->
                                <div class="row no-gutters mb-1">
                                    <div class="col-md-1 px-1">
                                        <label class="text-primary font-weight-bold text-xs mb-0">Cant.</label>
                                        <input type="number"
                                            class="form-control form-control-sm border-primary font-weight-bold text-center"
                                            ng-model="modalData.cantidad" min="1" value="1" style="font-size: 1rem;">
                                    </div>
                                    <div class="px-1" style="flex: 0 0 95px; max-width: 95px;">
                                        <label class="font-weight-bold text-xs mb-0">Fecha</label>
                                        <input type="text" class="form-control form-control-sm flatpickr-modal"
                                            ng-model="modalData.fecha" placeholder="dd/mm/aaaa">
                                    </div>
                                    <div class="px-1" style="flex: 0 0 65px; max-width: 65px;">
                                        <label class="font-weight-bold text-xs mb-0">Cód. Med</label>
                                        <input type="text" class="form-control form-control-sm text-uppercase"
                                            ng-model="modalData.cm" placeholder="COD">
                                    </div>
                                    <div class="col px-1">
                                        <label class="font-weight-bold text-xs mb-0">Nombre Médico</label>
                                        <input type="text" class="form-control form-control-sm text-uppercase"
                                            ng-model="modalData.medico" placeholder="NOMBRE">
                                    </div>
                                    <div class="col-md-1 px-1">
                                        <label class="font-weight-bold text-xs mb-0">Sexo</label>
                                        <input type="text"
                                            class="form-control form-control-sm text-center text-uppercase"
                                            ng-model="modalData.sexo" placeholder="S">
                                    </div>
                                    <div class="col-md-1 px-1">
                                        <label class="font-weight-bold text-xs mb-0">Edad</label>
                                        <input type="text" class="form-control form-control-sm text-center"
                                            ng-model="modalData.edad" placeholder="0">
                                    </div>
                                    <div class="col-md-1 px-1">
                                        <label class="font-weight-bold text-xs mb-0">Tipo</label>
                                        <input type="text"
                                            class="form-control form-control-sm text-uppercase text-center"
                                            ng-model="modalData.tipo" placeholder="T">
                                    </div>
                                    <div class="col-md-1 px-1">
                                        <label class="font-weight-bold text-xs mb-0">Cond</label>
                                        <input type="text"
                                            class="form-control form-control-sm text-uppercase text-center"
                                            ng-model="modalData.cond" placeholder="C">
                                    </div>
                                </div>

                                <!-- Fila Paciente: DNI, Nombre, F. Nacimiento -->
                                <div class="row no-gutters mb-1">
                                    <div class="px-1" style="flex: 0 0 150px; max-width: 150px;">
                                        <label class="text-info font-weight-bold text-xs mb-0"><i class="fas fa-id-card mr-1"></i>DNI / Identidad</label>
                                        <input type="text" class="form-control form-control-sm text-center"
                                            ng-model="modalData.identidad" placeholder="0000-0000-00000" ng-blur="consultarIdentidadModal()">
                                    </div>
                                    <div class="col px-1">
                                        <label class="text-info font-weight-bold text-xs mb-0"><i class="fas fa-user mr-1"></i>Nombre del Paciente</label>
                                        <input type="text" class="form-control form-control-sm text-uppercase"
                                            ng-model="modalData.nombre_paciente" placeholder="NOMBRE COMPLETO DEL PACIENTE">
                                    </div>
                                    <div class="px-1" style="flex: 0 0 120px; max-width: 120px;">
                                        <label class="text-info font-weight-bold text-xs mb-0"><i class="fas fa-birthday-cake mr-1"></i>F. Nacimiento</label>
                                        <input type="text" class="form-control form-control-sm text-center"
                                            ng-model="modalData.fecha_nacimiento" placeholder="dd/mm/aaaa" ng-blur="calcularEdadDesdeNacimientoModal()">
                                    </div>
                                </div>

                                <div class="row no-gutters mb-2">
                                    <div class="px-1" style="flex: 0 0 80px; max-width: 80px;">
                                        <label class="font-weight-bold text-xs mb-0">Cod. Col</label>
                                        <input type="text" class="form-control form-control-sm text-uppercase"
                                            ng-model="modalData.cod_col" placeholder="COD">
                                    </div>
                                    <div class="col px-1">
                                        <label class="font-weight-bold text-xs mb-0">Colonia</label>
                                        <input type="text" class="form-control form-control-sm text-uppercase"
                                            ng-model="modalData.colonia" placeholder="NOMBRE COLONIA">
                                    </div>
                                    <div class="col px-1">
                                        <label class="font-weight-bold text-xs mb-0">Referido A</label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control form-control-sm text-uppercase"
                                                ng-model="modalData.referido_a" placeholder="REFERIDO A">
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary" type="button"
                                                    ng-click="abrirBuscadorReferencias()" title="Buscar Referencia">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col px-1">
                                        <label class="font-weight-bold text-xs mb-0">Referido De</label>
                                        <div class="input-group input-group-sm">
                                            <input type="text" class="form-control form-control-sm text-uppercase"
                                                ng-model="modalData.referido_de" placeholder="REFERIDO DE">
                                            <div class="input-group-append">
                                                <button class="btn btn-outline-secondary" type="button"
                                                    ng-click="abrirBuscadorReferencias()" title="Buscar Referencia">
                                                    <i class="fas fa-search"></i>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col px-1">
                                        <label class="font-weight-bold text-xs mb-0 text-info">PG EMB (Auto)</label>
                                        <input type="text"
                                            class="form-control form-control-sm text-uppercase font-weight-bold text-info"
                                            ng-model="modalData.pg_emb" placeholder="AUTO" readonly tabindex="-1">
                                    </div>
                                </div>

                                <hr class="my-1 border-top-0">

                                <!-- Sección Diagnósticos Compacta -->
                                <div class="card shadow-none border mb-0" style="background-color: var(--bg-body) !important; border-color: var(--border-color) !important;">
                                    <div class="card-header py-1 px-2 border-bottom" style="background-color: var(--bg-subtle) !important; border-color: var(--border-color) !important;">
                                        <div class="row no-gutters font-weight-bold text-xs" style="white-space: nowrap;">
                                            <div class="col-2 px-1">CÓDIGO</div>
                                            <div class="col-8 px-1">DIAGNÓSTICO</div>
                                            <div class="col-2 px-1 text-center">COND</div>
                                        </div>
                                    </div>
                                    <div class="card-body p-1" style="max-height: 250px; overflow-y: auto; background-color: var(--bg-body) !important;">
                                        <div class="row no-gutters mb-1" ng-repeat="i in [1,2,3,4,5,6,7]">
                                            <div class="col-2 px-1">
                                                <input type="text"
                                                    class="form-control form-control-sm text-uppercase p-1"
                                                    ng-model="modalData['cod_' + i]" placeholder="COD"
                                                    style="height: 24px; font-size: 0.8rem;">
                                            </div>
                                            <div class="col-8 px-1">
                                                <input type="text"
                                                    class="form-control form-control-sm text-uppercase p-1"
                                                    ng-model="modalData['diagnostico_' + i]"
                                                    placeholder="Descripción..."
                                                    style="height: 24px; font-size: 0.8rem;">
                                            </div>
                                            <div class="col-2 px-1">
                                                <input type="text"
                                                    class="form-control form-control-sm text-uppercase p-1 text-center"
                                                    ng-model="modalData['cond_' + i]" placeholder="C"
                                                    style="height: 24px; font-size: 0.8rem;">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="modal-footer p-2" style="background-color: var(--bg-subtle) !important; border-color: var(--border-color) !important;">
                                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">
                                    Cancelar
                                </button>
                                <button type="button" class="btn btn-sm btn-warning text-dark"
                                    ng-click="limpiarModal()">
                                    Limpiar
                                </button>
                                <button type="button" class="btn btn-sm btn-success" ng-click="ingresarMasivo()">
                                    Ingresar
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- Incluir modales de búsqueda (DENTRO del scope de Angular) -->
                @include('partials.modales-buscadores')

                <!-- Modal Importar Excel e Histórico Clínico -->
                @include('ingresos.modal-importar-excel')

                <!-- Modal para adolescentes (AngularJS) -->
                <div class="modal fade shadow-lg" id="modalAdolescentes" data-backdrop="static" tabindex="-1"
                    role="dialog" aria-hidden="true" style="z-index: 2100;">
                    <div class="modal-dialog modal-xl modal-dialog-centered" role="document" style="max-width: 95%;">
                        <div class="modal-content border-0 rounded-xl overflow-hidden shadow-2xl">
                            <div
                                class="modal-header bg-gradient-to-r from-indigo-700 to-blue-600 text-white p-3 border-0">
                                <h5 class="modal-title font-black text-lg flex items-center tracking-tighter">
                                    <i class="fas fa-user-graduate mr-3 animate-pulse"></i> REGISTRO INTEGRAL DE
                                    ADOLESCENTES (10-19 AÑOS)
                                </h5>
                                <button type="button" class="close text-white opacity-80 hover:opacity-100"
                                    data-dismiss="modal" aria-label="Close">
                                    <span aria-hidden="true">&times;</span>
                                </button>
                            </div>
                            <div class="modal-body p-0">
                                <div class="p-3 border-b flex justify-between items-center shadow-sm" style="background-color: var(--bg-subtle); border-color: var(--border-color);">
                                    <div class="text-xs font-bold uppercase italic" style="color: var(--text-muted);">
                                        <i class="fas fa-info-circle mr-1 text-indigo-500"></i> Se detectaron <span
                                            class="text-indigo-400 font-black text-lg">@{{listadoAdolescentes.length}}</span>
                                        pacientes adolescentes. Por favor, complete su ficha técnica.
                                    </div>
                                    <div class="flex gap-2">
                                        <span class="badge badge-pill badge-success text-[10px] px-2 py-1 uppercase"><i
                                                class="fas fa-database mr-1"></i> Adolescentes_DB</span>
                                    </div>
                                </div>
                                <div class="table-responsive" style="max-height: 60vh; overflow-y: auto;">
                                    <table class="table table-sm table-hover m-0" style="min-width: 1400px;">
                                        <thead>
                                            <tr style="background-color: var(--bg-subtle); color: var(--text-primary); border-bottom: 2px solid var(--border-color);" class="text-[10px] font-black uppercase tracking-widest">
                                                <th class="text-center py-3" style="width: 50px;">#</th>
                                                <th style="width: 200px;">IDENTIDAD (DNI) <span
                                                        class="text-danger">*</span></th>
                                                <th style="width: 120px;">No. EXPEDIENTE</th>
                                                <th style="width: 180px;">NOMBRE COMPLETO<span
                                                        class="text-danger">*</span></th>
                                                <th class="text-center" style="width: 70px;">SEXO</th>
                                                <th style="width: 150px;">FECHA NAC. <span class="text-danger">*</span>
                                                </th>
                                                <th class="text-center" style="width: 80px;">EDAD</th>
                                                <th style="width: 150px;">COLONIA</th>
                                                <th style="width: 130px;">TELÉFONO</th>
                                                <th style="width: 130px;">EST. CIVIL</th>
                                                <th style="width: 130px;">ESCOLARIDAD</th>
                                                <th style="width: 60px;">ÁÑOS</th>
                                                <th style="width: 130px;">OCUPACIÓN</th>
                                                <th style="width: 150px;">MÉDICO</th>
                                                <th style="width: 120px;">USUARIO</th>
                                                <th class="text-center" style="width: 60px;">ACCIONES</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <tr ng-repeat="adol in listadoAdolescentes" class="border-b transition-colors">
                                                <td
                                                    class="text-center align-middle font-black border-r text-xs font-mono" style="background-color: var(--bg-subtle); color: var(--text-muted);">
                                                    @{{$index + 1}}</td>
                                                <td class="p-1">
                                                    <div class="input-group input-group-sm">
                                                        <input type="text"
                                                            class="form-control form-control-sm font-black text-xs"
                                                            ng-model="adol.numero_identidad"
                                                            ng-change="adol.guardado_ok = false"
                                                            ng-blur="buscarAdolescente(adol)"
                                                            placeholder="0000-0000-00000">
                                                        <div class="input-group-append" ng-if="adol.cargado">
                                                            <span
                                                                class="input-group-text bg-emerald-500 text-white border-0 py-0"><i
                                                                    class="fas fa-check-double scale-75"></i></span>
                                                        </div>
                                                    </div>
                                                </td>
                                                <td class="p-1"><input type="text"
                                                        class="form-control form-control-sm font-bold text-xs"
                                                        ng-model="adol.no_expediente" 
                                                        ng-change="adol.guardado_ok = false"
                                                        ng-blur="buscarAdolescente(adol)"
                                                        placeholder="Expediente..."></td>
                                                <td class="p-1"><input type="text"
                                                        class="form-control form-control-sm font-black text-xs uppercase"
                                                        ng-model="adol.nombre_completo"
                                                        ng-change="adol.guardado_ok = false"
                                                        placeholder="Nombre completo del joven..."></td>
                                                <td class="text-center align-middle"><span class="badge badge-pill"
                                                        ng-class="{'badge-info': adol.sexo=='M', 'badge-primary': adol.sexo=='H'}"
                                                        style="width: 30px;">@{{adol.sexo}}</span></td>
                                                <td class="p-1"><input type="date"
                                                        class="form-control form-control-sm text-xs"
                                                        ng-model="adol.fecha_nacimiento"
                                                        ng-change="adol.guardado_ok = false"></td>
                                                <td class="text-center align-middle font-bold text-xs">@{{adol.edad}}
                                                     @{{adol.tipo}}</td>
                                                <td class="p-1">
                                                    <select
                                                        class="form-control form-control-sm text-xs"
                                                        ng-model="adol.colonia"
                                                        ng-change="adol.guardado_ok = false">
                                                        <option value="">-- COLONIA --</option>
                                                        @foreach($colonias as $col)
                                                            <option value="{{ $col->COLONIA }}">{{ $col->COLONIA }}</option>
                                                        @endforeach
                                                    </select>
                                                </td>
                                                <td class="p-1"><input type="text"
                                                        class="form-control form-control-sm text-xs"
                                                        ng-model="adol.numero_telefono" 
                                                        ng-change="adol.guardado_ok = false"
                                                        placeholder="Celular..."></td>
                                                <td class="p-1">
                                                    <select
                                                        class="form-control form-control-sm text-xs"
                                                        ng-model="adol.estado_civil"
                                                        ng-change="adol.guardado_ok = false">
                                                        <option value="">-- SELEC. --</option>
                                                        <option value="SOLTERO/A">SOLTERO/A</option>
                                                        <option value="CASADO/A">CASADO/A</option>
                                                        <option value="UNION LIBRE">UNIÓN LIBRE</option>
                                                        <option value="OTRO">OTRO</option>
                                                    </select>
                                                </td>
                                                <td class="p-1">
                                                    <select
                                                        class="form-control form-control-sm text-xs"
                                                        ng-model="adol.escolaridad"
                                                        ng-change="adol.guardado_ok = false">
                                                        <option value="">-- ESCOLARIDAD --</option>
                                                        <option value="PRIMARIA">PRIMARIA</option>
                                                        <option value="SECUNDARIA">SECUNDARIA</option>
                                                        <option value="UNIVERSITARIO">UNIVERSITARIO</option>
                                                        <option value="NINGUNA">NINGUNA</option>
                                                    </select>
                                                </td>
                                                <td class="p-1">
                                                    <input type="number"
                                                        class="form-control form-control-sm text-xs text-center"
                                                        ng-model="adol.anios_cursados"
                                                        ng-change="adol.guardado_ok = false"
                                                        min="0" max="25" placeholder="Años">
                                                </td>
                                                <td class="p-1">
                                                    <select
                                                        class="form-control form-control-sm text-xs"
                                                        ng-model="adol.ocupacion"
                                                        ng-change="adol.guardado_ok = false">
                                                        <option value="">-- OCUPACIÓN --</option>
                                                        <option value="TRABAJA">TRABAJA</option>
                                                        <option value="ESTUDIA">ESTUDIA</option>
                                                        <option value="TRABAJA Y ESTUDIA">TRABAJA Y ESTUDIA</option>
                                                        <option value="NINGUNA">NINGUNA</option>
                                                    </select>
                                                </td>
                                                <td class="text-center align-middle p-1">
                                                    <span class="badge badge-secondary text-xs font-mono" style="font-size:9px;">@{{adol.medico_atencion || '-'}}</span>
                                                </td>
                                                <td class="text-center align-middle p-1">
                                                    <span class="badge badge-theme text-xs" style="font-size:9px;">@{{adol.usuario_registro || '-'}}</span>
                                                </td>
                                                <td class="text-center align-middle p-1">
                                                    <button type="button"
                                                        class="btn btn-xs btn-success rounded-circle shadow-sm"
                                                        ng-click="guardarAdolescenteIndividual(adol)"
                                                        data-toggle="tooltip" title="Guardar este registro">
                                                        <i class="fas"
                                                            ng-class="{'fa-save': !adol.guardado_ok, 'fa-check': adol.guardado_ok}"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                            <div class="modal-footer p-3 border-t flex justify-between">
                                <button type="button"
                                    class="btn btn-outline-secondary font-black text-[10px] uppercase px-4"
                                    ng-click="cancelarAdolescentes()">
                                    <i class="fas fa-forward mr-2"></i> IGNORAR REGISTRO Y CONTINUAR
                                </button>
                                <div class="flex gap-2">
                                    <button type="button"
                                        class="btn btn-sm btn-success px-4 py-2 font-black uppercase shadow-lg text-[10px]"
                                        ng-click="guardarAdolescentes(false, true)">
                                        <i class="fas fa-sync-alt mr-2"></i> SOLO GUARDAR ADOLESCENTES
                                    </button>
                                    <button type="button"
                                        class="btn btn-sm btn-primary px-4 py-2 font-black uppercase shadow-lg text-[10px]"
                                        ng-click="guardarAdolescentes(false, false)">
                                        <i class="fas fa-save mr-2"></i> PROCESAR Y GUARDAR TODO (NUEVOS + SEGUIMIENTOS)
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        /* Estilos para Flatpickr dentro de la tabla */
        .flatpickr-calendar {
            z-index: 99999 !important;
        }

        [ng\:cloak],
        [ng-cloak],
        [data-ng-cloak],
        [x-ng-cloak],
        .ng-cloak,
        .x-ng-cloak {
            display: none !important;
        }

        /* Compactación y espaciado lateral para tabla spreadsheet */
        /* Compactación y espaciado lateral para tabla spreadsheet de Ingresos */
        .table-ingresos,
        table.table.table-ingresos {
            border-collapse: separate !important;
            border-spacing: 0 !important;
            border: 1px solid var(--border-color, #cbd5e1) !important;
            background-color: var(--bg-surface, #ffffff) !important;
            color: var(--text-primary, #1e293b) !important;
            width: max-content !important;
            min-width: 100% !important;
            table-layout: fixed !important;
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", Arial, sans-serif !important;
            font-size: 0.78rem !important;
        }

        .table-ingresos th,
        table.table.table-ingresos th {
            padding: 0 4px !important;
            vertical-align: middle !important;
            white-space: nowrap !important;
            height: 24px !important;
            line-height: 24px !important;
            border: 1px solid var(--border-color, #e2e8f0) !important;
            color: var(--text-primary, #1e293b) !important;
            font-size: 0.70rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.02em;
        }

        .table-ingresos td,
        table.table.table-ingresos td,
        table.table.table-ingresos tbody td,
        table.table.table-ingresos tbody td:not(.sticky-col-first),
        table.table.table-ingresos td[contenteditable="true"] {
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            padding-left: 5px !important;
            padding-right: 5px !important;
            padding: 0 5px !important;
            vertical-align: middle !important;
            white-space: nowrap !important;
            height: 20px !important;
            min-height: 20px !important;
            line-height: 20px !important;
            border: 1px solid var(--border-color, #e2e8f0) !important;
            color: var(--text-primary, #1e293b) !important;
            font-size: 0.78rem !important;
            font-weight: 500 !important;
            background-color: var(--bg-surface, #ffffff);
            letter-spacing: normal !important;
        }

        table.table.table-ingresos tbody tr {
            height: 20px !important;
            min-height: 20px !important;
        }

        /* Inmovilizar primera columna (#) */
        table.table.table-ingresos .sticky-col,
        .table-ingresos .sticky-col,
        .sticky-col {
            position: sticky;
            left: 0;
            z-index: 2 !important;
            background-color: var(--bg-subtle, #f8f9fa) !important;
            color: var(--text-primary, #1e293b) !important;
            border-left: 1px solid var(--border-color, #cbd5e1) !important;
            border-right: 2px solid var(--border-color, #cbd5e1) !important;
            font-size: 0.75rem !important;
            font-weight: 600 !important;
            text-align: center !important;
            width: 34px !important;
            min-width: 34px !important;
            max-width: 34px !important;
            padding: 0 !important;
            height: 20px !important;
            line-height: 20px !important;
        }

        /* Clase para fila activa en columna sticky */
        table.table.table-ingresos .sticky-col.active-row,
        .sticky-col.active-row {
            background-color: var(--color-primary, #4d7cfe) !important;
            color: #ffffff !important;
            border-right: 2px solid var(--color-primary-hover, #3b6bef) !important;
            z-index: 3 !important;
        }

        /* La celda de la esquina (# en el header) */
        table.table.table-ingresos thead th.sticky-col,
        thead th.sticky-col {
            z-index: 11 !important;
            position: sticky;
            top: 0;
            left: 0;
            border-top: 1px solid var(--border-color, #454d55) !important;
            background-color: var(--bg-subtle, #1e293b) !important;
            color: var(--text-primary, #ffffff) !important;
            font-size: 0.70rem !important;
            font-weight: 700 !important;
            text-align: center !important;
            height: 24px !important;
            line-height: 24px !important;
        }

        .text-xs {
            font-size: 0.75rem !important;
        }

        table.table.table-ingresos tbody tr:hover {
            background-color: var(--sidebar-item-hover, rgba(77, 124, 254, 0.12)) !important;
        }

        /* Celdas editables */
        table.table.table-ingresos td[contenteditable="true"] {
            outline: none;
            min-height: 20px !important;
            height: 20px !important;
            line-height: 20px !important;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            padding-left: 5px !important;
            padding-right: 5px !important;
            padding: 0 5px !important;
            border: 1px solid var(--border-color, #e2e8f0) !important;
            transition: background 0.15s;
            cursor: cell;
            background-color: var(--bg-surface, #ffffff) !important;
            color: var(--text-primary, #1e293b) !important;
            font-size: 0.78rem !important;
            font-weight: 500 !important;
        }

        table.table.table-ingresos td[contenteditable="true"].edit-mode,
        table.table.table-ingresos td[contenteditable="true"]:focus:not(.nav-mode) {
            cursor: text !important;
        }

        table.table.table-ingresos td[contenteditable="true"]:focus {
            background-color: var(--bg-surface, #ffffff) !important;
            color: var(--text-primary, #1e293b) !important;
            box-shadow: inset 0 0 0 2px var(--color-primary, #4d7cfe);
            border: 1px solid var(--color-primary, #4d7cfe) !important;
            z-index: 5;
            position: relative;
        }

        table.table.table-ingresos td {
            cursor: cell;
            outline: none;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
            padding-top: 0 !important;
            padding-bottom: 0 !important;
            padding-left: 5px !important;
            padding-right: 5px !important;
            padding: 0 5px !important;
            vertical-align: middle !important;
            height: 20px !important;
            min-height: 20px !important;
            line-height: 20px !important;
            font-size: 0.78rem !important;
            font-weight: 500 !important;
        }

        table.table.table-ingresos td.edit-mode {
            cursor: text;
        }

        table.table.table-ingresos td.nav-mode,
        table.table.table-ingresos td:focus,
        table.table.table-ingresos td[contenteditable="true"]:focus {
            overflow: visible !important;
            white-space: normal !important;
            background-color: var(--bg-surface, #ffffff) !important;
            color: var(--text-primary, #1e293b) !important;
            z-index: 50 !important;
            position: relative !important;
            outline: 1.5px solid var(--color-primary, #4d7cfe) !important;
            outline-offset: -1px;
            box-shadow: 0 1px 4px rgba(77, 124, 254, 0.25) !important;
            min-width: max-content;
            font-size: 0.78rem !important;
            font-weight: 500 !important;
        }

        .nav-mode {
            caret-color: transparent !important;
        }

        .nav-mode::selection {
            background: var(--color-primary, #4d7cfe);
            color: white;
        }

        table.table.table-ingresos thead th,
        .thead-dark-custom th {
            border: 1px solid var(--border-color, #334155) !important;
            padding: 0 4px !important;
            height: 26px !important;
            line-height: 26px !important;
            top: 0;
            position: sticky;
            background-color: var(--bg-subtle, #1e293b) !important;
            color: var(--text-primary, #ffffff) !important;
            font-size: 0.70rem !important;
            font-weight: 700 !important;
            letter-spacing: 0.02em;
            vertical-align: middle !important;
        }

        /* Soporte Tema Oscuro y Claro según AGENTS.md */
        [data-theme="dark"] table.table.table-ingresos {
            border-color: var(--border-color, #334155) !important;
            background-color: var(--bg-surface, #111c44) !important;
            color: var(--text-primary, #f1f5f9) !important;
        }

        [data-theme="dark"] table.table.table-ingresos th,
        [data-theme="dark"] table.table.table-ingresos td,
        [data-theme="dark"] table.table.table-ingresos tbody td,
        [data-theme="dark"] table.table.table-ingresos td[contenteditable="true"] {
            border-color: var(--border-color, #1e293b) !important;
            background-color: var(--bg-surface, #111c44) !important;
            color: var(--text-primary, #f1f5f9) !important;
        }

        [data-theme="dark"] table.table.table-ingresos .sticky-col {
            background-color: var(--bg-subtle, #0b1437) !important;
            color: var(--text-primary, #f1f5f9) !important;
            border-color: var(--border-color, #334155) !important;
        }

        [data-theme="light"] table.table.table-ingresos {
            border-color: var(--border-color, #cbd5e1) !important;
            background-color: var(--bg-surface, #ffffff) !important;
            color: var(--text-primary, #1e293b) !important;
        }

        [data-theme="light"] table.table.table-ingresos th,
        [data-theme="light"] table.table.table-ingresos td,
        [data-theme="light"] table.table.table-ingresos tbody td,
        [data-theme="light"] table.table.table-ingresos td[contenteditable="true"] {
            border-color: var(--border-color, #e2e8f0) !important;
            background-color: var(--bg-surface, #ffffff) !important;
            color: var(--text-primary, #1e293b) !important;
        }

        [data-theme="light"] table.table.table-ingresos .sticky-col {
            background-color: var(--bg-subtle, #f8f9fa) !important;
            color: var(--text-primary, #1e293b) !important;
            border-color: var(--border-color, #cbd5e1) !important;
        }

        /* Card Container & Footer */
        .card {
            background-color: var(--bg-surface, #ffffff) !important;
            border: 1px solid var(--border-color, #cbd5e1) !important;
            color: var(--text-primary, #1e293b) !important;
        }

        .card-footer {
            background-color: var(--bg-surface, #ffffff) !important;
            border-top: 1px solid var(--border-color, #cbd5e1) !important;
        }

        .btn-outline-secondary {
            color: var(--text-primary, #1e293b) !important;
            border-color: var(--border-color, #cbd5e1) !important;
            background-color: var(--bg-subtle, #f8f9fa) !important;
        }

        .btn-outline-secondary:hover {
            background-color: var(--sidebar-item-hover, #e2e8f0) !important;
            border-color: var(--color-primary, #4d7cfe) !important;
            color: var(--text-primary, #1e293b) !important;
        }

        .badge-theme {
            background-color: var(--bg-body, #0b1329) !important;
            color: var(--text-secondary, #a3aed0) !important;
            border: 1px solid var(--border-color, #222f5d);
        }

        /* --- COMPACT STAT CARD --- */
        .compact-stat-container {
            display: flex;
            gap: 15px;
            justify-content: flex-start;
        }

        .compact-stat-card {
            background: var(--bg-surface, #ffffff);
            border: 1px solid var(--border-color, #cbd5e1);
            border-radius: 8px;
            padding: 5px 15px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 15px;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
            border-left: 5px solid #28a745;
            transition: all 0.3s ease;
            min-width: auto;
        }

        .compact-stat-card:hover {
            transform: translateY(-1px);
            background: var(--bg-surface-hover, #f1f5f9);
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.12);
        }

        .stat-icon {
            width: 32px;
            height: 32px;
            background: var(--bg-subtle, #ffffff) !important;
            color: #28a745;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .stat-count {
            font-size: 1.8rem;
            font-weight: 900;
            color: var(--text-primary, #1a1a1a);
            line-height: 1;
            font-family: inherit;
            letter-spacing: -1px;
        }

        .stat-details {
            display: flex;
            flex-direction: column;
        }

        .stat-label {
            font-size: 0.65rem;
            color: var(--text-muted, #6c757d);
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            line-height: 1;
            margin-bottom: 2px;
        }

        .stat-value {
            font-size: 0.95rem;
            font-weight: 800;
            color: var(--text-primary, #2d3436);
            line-height: 1;
        }

        pre#JSON {
            max-height: 350px;
            overflow: auto;
            font-size: 0.85rem;
            font-family: 'Courier New', Courier, monospace;
        }

        /* Scrollbar personalizado y fluido */
        .table-responsive {
            overflow: auto !important;
            overflow-x: auto !important;
            overflow-y: auto !important;
            height: 100% !important;
            width: 100% !important;
            scrollbar-width: thin;
            scrollbar-color: var(--border-color, #94a3b8) var(--bg-subtle, #f1f5f9);
        }

        .table-responsive::-webkit-scrollbar {
            width: 10px !important;
            height: 10px !important;
            display: block !important;
        }

        .table-responsive::-webkit-scrollbar-track {
            background: var(--bg-subtle, #f1f5f9) !important;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: var(--border-color, #94a3b8) !important;
            border-radius: 5px !important;
        }

        .table-responsive::-webkit-scrollbar-thumb:hover {
            background: var(--color-primary, #4d7cfe) !important;
        }

        [data-theme="dark"] .table-responsive {
            scrollbar-color: var(--border-color, #334155) var(--bg-subtle, #1b254b);
        }

        [data-theme="dark"] .table-responsive::-webkit-scrollbar-track {
            background: var(--bg-subtle, #1b254b) !important;
        }

        [data-theme="dark"] .table-responsive::-webkit-scrollbar-thumb {
            background: var(--border-color, #334155) !important;
        }

        [data-theme="dark"] .table-responsive::-webkit-scrollbar-thumb:hover {
            background: var(--color-primary, #4d7cfe) !important;
        }

        /* Estilos de Modales y Formularios para Modo Claro y Modo Oscuro */
        .modal {
            z-index: 1050 !important;
        }
        .modal-backdrop {
            z-index: 1040 !important;
        }
        .modal-dialog {
            z-index: 1055 !important;
            margin-top: 2rem;
        }
        .modal-content {
            background-color: var(--bg-surface, #ffffff) !important;
            color: var(--text-primary, #1e293b) !important;
            border: 1px solid var(--border-color, #cbd5e1) !important;
            border-radius: 12px !important;
            box-shadow: 0 15px 35px rgba(0, 0, 0, 0.45) !important;
            overflow: hidden;
        }
        .modal-header {
            border-bottom: 1px solid var(--border-color, #cbd5e1) !important;
            padding: 0.85rem 1.25rem !important;
        }
        .modal-header .close {
            text-shadow: none !important;
            opacity: 0.8;
            font-size: 1.5rem;
            outline: none;
            color: #ffffff !important;
        }
        .modal-header .close:hover {
            opacity: 1;
        }
        .modal-header.bg-success {
            background: linear-gradient(135deg, #10b981, #059669) !important;
            color: #ffffff !important;
        }
        .modal-header.bg-primary {
            background: linear-gradient(135deg, #4d7cfe, #3b6bef) !important;
            color: #ffffff !important;
        }
        .modal-header.bg-info {
            background: linear-gradient(135deg, #06b6d4, #0891b2) !important;
            color: #ffffff !important;
        }
        .modal-header.bg-warning {
            background: linear-gradient(135deg, #f59e0b, #d97706) !important;
            color: #ffffff !important;
        }
        .modal-header.bg-indigo-600 {
            background: linear-gradient(135deg, #6366f1, #4f46e5) !important;
            color: #ffffff !important;
        }
        .modal-body {
            background-color: var(--bg-surface, #ffffff) !important;
            color: var(--text-primary, #1e293b) !important;
        }
        .modal-body label {
            color: var(--text-muted, #64748b) !important;
            font-weight: 700;
        }
        .modal-body .form-control {
            background-color: var(--input-bg, #ffffff) !important;
            border: 1px solid var(--border-color, #cbd5e1) !important;
            color: var(--text-primary, #1e293b) !important;
            border-radius: 6px;
        }
        .modal-body .form-control:focus {
            background-color: var(--input-bg, #ffffff) !important;
            border-color: var(--color-primary, #4d7cfe) !important;
            box-shadow: 0 0 0 2px rgba(77, 124, 254, 0.25) !important;
            color: var(--text-primary, #1e293b) !important;
        }
        .modal-body select.form-control option {
            background-color: var(--bg-surface, #ffffff) !important;
            color: var(--text-primary, #1e293b) !important;
        }
        .modal-body .card {
            background-color: var(--bg-body, #ffffff) !important;
            border: 1px solid var(--border-color, #cbd5e1) !important;
            border-radius: 8px;
        }
        .modal-body .card-header {
            background-color: var(--bg-subtle, #f1f5f9) !important;
            border-bottom: 1px solid var(--border-color, #cbd5e1) !important;
            color: var(--text-primary, #1e293b) !important;
        }
        .modal-footer {
            background-color: var(--bg-subtle, #f8f9fa) !important;
            border-top: 1px solid var(--border-color, #cbd5e1) !important;
            padding: 0.75rem 1.25rem !important;
        }
        .modal-body .table {
            background-color: var(--bg-surface, #ffffff) !important;
            color: var(--text-primary, #1e293b) !important;
        }
        .modal-body .table tbody tr {
            background-color: transparent !important;
            color: var(--text-primary, #1e293b) !important;
        }
        .modal-body .table tbody tr:hover {
            background-color: var(--sidebar-item-hover, rgba(77, 124, 254, 0.12)) !important;
        }
        .modal-body .thead-light th {
            background-color: var(--bg-subtle, #f1f5f9) !important;
            color: var(--text-primary, #1e293b) !important;
            border-bottom: 2px solid var(--border-color, #cbd5e1) !important;
            border-top: none !important;
        }
        .modal-body .input-group-text {
            background-color: var(--bg-subtle, #f1f5f9) !important;
            border-color: var(--border-color, #cbd5e1) !important;
            color: var(--text-muted, #64748b) !important;
        }

        /* Flatpickr Theme Integration */
        [data-theme="dark"] .flatpickr-calendar {
            background: var(--bg-surface, #111c44) !important;
            border-color: var(--border-color, #222f5d) !important;
            box-shadow: 0 15px 30px rgba(0, 0, 0, 0.6) !important;
            color: var(--text-primary, #ffffff) !important;
        }
        [data-theme="dark"] .flatpickr-day {
            color: var(--text-primary, #ffffff) !important;
        }
        [data-theme="dark"] .flatpickr-day:hover {
            background: var(--sidebar-item-hover, #1e295a) !important;
        }
        [data-theme="dark"] .flatpickr-day.selected {
            background: var(--color-primary, #4d7cfe) !important;
            border-color: var(--color-primary, #4d7cfe) !important;
            color: #ffffff !important;
        }
        [data-theme="dark"] .flatpickr-months .flatpickr-month,
        [data-theme="dark"] .flatpickr-current-month .numInputWrapper span.arrowUp,
        [data-theme="dark"] .flatpickr-current-month .numInputWrapper span.arrowDown {
            color: var(--text-primary, #ffffff) !important;
            fill: var(--text-primary, #ffffff) !important;
        }
        [data-theme="dark"] span.flatpickr-weekday {
            color: var(--text-muted, #707eae) !important;
        }

        /* Explicit Dark Mode Overrides for High-Contrast Clean Borders & Pastel Buttons */
        [data-theme="dark"] .card,
        [data-theme="dark"] .card-body,
        [data-theme="dark"] .table-responsive,
        [data-theme="dark"] .page-fade-in {
            background-color: #111827 !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }

        /* Stat Cards con bordes definidos y contraste en Modo Oscuro */
        [data-theme="dark"] .compact-stat-card {
            background-color: #1e293b !important;
            border: 1px solid #475569 !important;
            border-left: 5px solid #10b981 !important;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.45) !important;
        }

        [data-theme="dark"] .compact-stat-card.stat-card-fecha {
            border-left: 5px solid #3b82f6 !important;
        }

        [data-theme="dark"] .compact-stat-card .stat-icon {
            background-color: #0f172a !important;
            border: 1px solid #334155 !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.3) !important;
        }

        [data-theme="dark"] .compact-stat-card.stat-card-medico .stat-icon {
            color: #34d399 !important;
        }

        [data-theme="dark"] .compact-stat-card.stat-card-fecha .stat-icon {
            color: #60a5fa !important;
        }

        [data-theme="dark"] .stat-count {
            color: #34d399 !important;
        }

        [data-theme="dark"] .stat-value {
            color: #f8fafc !important;
        }

        [data-theme="dark"] .stat-label {
            color: #94a3b8 !important;
        }

        /* Tabla con bordes marcados */
        [data-theme="dark"] .table {
            background-color: #111827 !important;
            color: #f8fafc !important;
            border: 1px solid #334155 !important;
        }

        [data-theme="dark"] .table th,
        [data-theme="dark"] .table td {
            border: 1px solid #1e293b !important;
            color: #f8fafc !important;
        }

        [data-theme="dark"] td[contenteditable="true"] {
            background-color: #111827 !important;
            color: #f8fafc !important;
            border: 1px solid #1e293b !important;
        }

        /* Posición y Diseño Fino de la Celda Activa / Foco en Modo Oscuro */
        [data-theme="dark"] td.nav-mode,
        [data-theme="dark"] td:focus,
        [data-theme="dark"] td[contenteditable="true"]:focus {
            background-color: #1a2744 !important;
            color: #ffffff !important;
            outline: 1.5px solid #60a5fa !important;
            outline-offset: -1px;
            border-color: #60a5fa !important;
            box-shadow: 0 0 6px rgba(96, 165, 250, 0.35) !important;
            z-index: 50 !important;
            position: relative !important;
        }

        [data-theme="dark"] td.edit-mode,
        [data-theme="dark"] td[contenteditable="true"].edit-mode {
            background-color: #0f203d !important;
            color: #ffffff !important;
            outline: 1.5px solid #38bdf8 !important;
            outline-offset: -1px;
            border-color: #38bdf8 !important;
            box-shadow: 0 0 6px rgba(56, 189, 248, 0.35) !important;
            z-index: 50 !important;
            position: relative !important;
        }

        /* Hover de fila y celdas en Modo Oscuro */
        [data-theme="dark"] .table tbody tr:hover td {
            background-color: rgba(96, 165, 250, 0.08) !important;
        }

        [data-theme="dark"] .table tbody td:hover:not(.nav-mode):not(:focus) {
            background-color: rgba(96, 165, 250, 0.15) !important;
        }

        /* Fila activa en columna fija # */
        [data-theme="dark"] .sticky-col.active-row {
            background-color: #2563eb !important;
            color: #ffffff !important;
            font-weight: 700 !important;
            border-right: 2px solid #60a5fa !important;
            box-shadow: 0 0 10px rgba(37, 99, 235, 0.5) !important;
        }

        [data-theme="dark"] .sticky-col {
            background-color: #161f30 !important;
            color: #f8fafc !important;
            border-left: 1px solid #334155 !important;
            border-right: 2px solid #334155 !important;
        }

        [data-theme="dark"] thead.thead-dark-custom th,
        [data-theme="dark"] thead th.sticky-col {
            background-color: #1e293b !important;
            color: #ffffff !important;
            border: 1px solid #334155 !important;
        }

        /* Footer Toolbar con borde superior y botones en Colores Pasteles */
        [data-theme="dark"] .card-footer {
            background-color: #1a233a !important;
            border-top: 1px solid #334155 !important;
        }

        [data-theme="dark"] .card-footer .btn-group {
            display: flex !important;
            flex-wrap: wrap !important;
            gap: 8px !important;
        }

        [data-theme="dark"] .card-footer .btn {
            border-radius: 6px !important;
            margin: 0 !important;
            font-weight: 700 !important;
            font-size: 0.82rem !important;
            transition: all 0.2s ease !important;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.25) !important;
        }

        /* 1. Pastel Menta / Verde (Agregar Fila) */
        [data-theme="dark"] .card-footer .btn-success:not([href*="condicionamientos"]) {
            background-color: #86efac !important;
            color: #064e3b !important;
            border: 1px solid #4ade80 !important;
        }
        [data-theme="dark"] .card-footer .btn-success:not([href*="condicionamientos"]):hover {
            background-color: #4ade80 !important;
            color: #022c22 !important;
            box-shadow: 0 0 10px rgba(74, 222, 128, 0.5) !important;
        }

        /* 2. Pastel Amarillo / Ámbar (Ingresar Datos) */
        [data-theme="dark"] .card-footer .btn-warning {
            background-color: #fde047 !important;
            color: #713f12 !important;
            border: 1px solid #facc15 !important;
        }
        [data-theme="dark"] .card-footer .btn-warning:hover {
            background-color: #facc15 !important;
            color: #422006 !important;
            box-shadow: 0 0 10px rgba(250, 204, 21, 0.5) !important;
        }

        /* 3. Pastel Rosa / Coral (Limpiar Tabla) */
        [data-theme="dark"] .card-footer .btn-danger {
            background-color: #fca5a5 !important;
            color: #7f1d1d !important;
            border: 1px solid #f87171 !important;
        }
        [data-theme="dark"] .card-footer .btn-danger:hover {
            background-color: #f87171 !important;
            color: #450a0a !important;
            box-shadow: 0 0 10px rgba(248, 113, 113, 0.5) !important;
        }

        /* 4. Pastel Celeste / Azul (Guardar) */
        [data-theme="dark"] .card-footer .btn-primary {
            background-color: #93c5fd !important;
            color: #1e3a8a !important;
            border: 1px solid #60a5fa !important;
        }
        [data-theme="dark"] .card-footer .btn-primary:hover {
            background-color: #60a5fa !important;
            color: #172554 !important;
            box-shadow: 0 0 10px rgba(96, 165, 250, 0.5) !important;
        }

        /* 5. Pastel Lavanda / Púrpura (Condicionamientos) */
        [data-theme="dark"] .card-footer a.btn-success[href*="condicionamientos"] {
            background-color: #d8b4fe !important;
            color: #581c87 !important;
            border: 1px solid #c084fc !important;
        }
        [data-theme="dark"] .card-footer a.btn-success[href*="condicionamientos"]:hover {
            background-color: #c084fc !important;
            color: #3b0764 !important;
            box-shadow: 0 0 10px rgba(192, 132, 252, 0.5) !important;
        }

        /* 6. Pastel Slate / Indigo (Buscadores: Médicos, Diagnósticos, Colonias, Referencias) */
        [data-theme="dark"] .card-footer .btn-outline-secondary {
            background-color: #1e293b !important;
            color: #cbd5e1 !important;
            border: 1px solid #475569 !important;
        }
        [data-theme="dark"] .card-footer .btn-outline-secondary:hover {
            background-color: #334155 !important;
            color: #ffffff !important;
            border-color: #64748b !important;
            box-shadow: 0 0 8px rgba(148, 163, 184, 0.3) !important;
        }

        [data-theme="dark"] .badge-theme {
            background-color: #0f172a !important;
            color: #93c5fd !important;
            border: 1px solid #334155 !important;
        }

        /* Modales en Dark Mode */
        [data-theme="dark"] .modal-content,
        [data-theme="dark"] .modal-body {
            background-color: #151e32 !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }

        [data-theme="dark"] .modal-body .card {
            background-color: #0b1329 !important;
            border-color: #334155 !important;
        }

        [data-theme="dark"] .modal-body .card-header,
        [data-theme="dark"] .modal-footer {
            background-color: #1b254b !important;
            border-color: #334155 !important;
        }

        [data-theme="dark"] .modal-body .form-control {
            background-color: #0b1437 !important;
            border-color: #334155 !important;
            color: #f8fafc !important;
        }

        [data-theme="dark"] .modal-body .form-control:focus {
            background-color: #0b1437 !important;
            border-color: #4d7cfe !important;
            color: #f8fafc !important;
        }

        [data-theme="dark"] .modal-body .table {
            background-color: #151e32 !important;
            color: #f8fafc !important;
        }

        [data-theme="dark"] .modal-body .table tbody tr:hover {
            background-color: #1e295a !important;
        }

        [data-theme="dark"] .modal-body .thead-light th {
            background-color: #1b254b !important;
            color: #f8fafc !important;
            border-color: #334155 !important;
        }

        [data-theme="dark"] .input-group-text {
            background-color: #1b254b !important;
            border-color: #334155 !important;
            color: #94a3b8 !important;
        }
    </style>

    <!-- Incluir modales de búsqueda -->

    <script>
        // Limpiar configuración heredada de tabla en localStorage si existía
        try {
            localStorage.removeItem('tablaConfig');
            var oldDynamicStyles = document.getElementById('dynamic-table-styles');
            if (oldDynamicStyles) oldDynamicStyles.remove();
        } catch(e) {}
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <script>
        'use strict';

        var app = angular.module('TablaDemo', []);

        app.controller('TablaCtrl', ['$scope', function ($scope) {
            // Definir usuario actual desde Laravel
            var currentUser = '{{ auth()->check() ? auth()->user()->name : "ADMINISTRADOR" }}';

            // Obtener columnas dinámicamente de PHP
            var columns = {!! json_encode($columns) !!};

            // Lista de médicos para autocompletar PROF
            // Lista de médicos para autocompletar PROF
            // Inyectada desde el controlador
            $scope.medicosList = @json($medicos ?? []);
            $scope.coloniasList = @json($colonias ?? []);
            $scope.diagnosticosList = @json($diagnosticos ?? []);
            $scope.referenciasList = @json($referencias ?? []);
            $scope.validacionesDiagnosticos = @json($validacionesDiagnosticos ?? []);

            $scope.lista = [];

            // Estado de fila seleccionada
            $scope.filaSeleccionada = -1;
            $scope.setFilaSeleccionada = function (index) {
                $scope.filaSeleccionada = parseInt(index);
            };

            // Estado de guardado de la tabla
            $scope.tablaGuardada = false;
            $scope.marcarModificado = function () {
                if ($scope.tablaGuardada) {
                    $scope.tablaGuardada = false;
                    // Forzar digest si es llamado desde evento DOM ajeno
                    if (!$scope.$$phase) $scope.$digest();
                }
            };

            // Normalizar clave de médico para búsqueda inteligente
            function normalizarClaveMedico(texto) {
                if (!texto) return '';
                var str = texto.toString().toUpperCase()
                    .normalize("NFD").replace(/[\u0300-\u036f]/g, "")
                    .replace(/^(DR\.|DRA\.|MSS\.|G\.O\.\s*DRA\.|LIC\.|LICDA\.)\s*/i, '')
                    .replace(/[^A-Z0-9\s]/g, ' ')
                    .replace(/\s+/g, ' ')
                    .trim();
                return str;
            }

            // Mapeo de alias conocidos a nombres oficiales en tabla medicos
            var aliasMedicosMap = {
                'ANDREA MEJIA': 'MSS. ANDREA MICHELLE MEJIA MORAZAN',
                'DRA. MAGALY COELLO': 'DRA. MAGALY ROCIO COELLO GARCIA',
                'MAGALY COELLO': 'DRA. MAGALY ROCIO COELLO GARCIA',
                'ISSIS NOHEMY RIVAS ARTILES': 'DRA. ISSIS NOHEMY RIVAS ARTILES',
                'DRA. ISSIS RIVAS': 'DRA. ISSIS NOHEMY RIVAS ARTILES',
                'DRA.ISSIS RIVAS': 'DRA. ISSIS NOHEMY RIVAS ARTILES',
                'KATHERINE ATENA FERNANDEZ PEREZ': 'MSS.KATHERINE ATENA FERNANDEZ PEREZ',
                'MARCELA DE JESÚS CRUZ COLINDRES': 'MSS. MARCELA DE JESUS CRUZ COLINDRES',
                'MARCELA DE JESUS CRUZ COLINDRES': 'MSS. MARCELA DE JESUS CRUZ COLINDRES',
                'DRA. YUSEN NUÑEZ': 'DRA. YUSEN NIESVANOVA NUÑEZ',
                'DR. EDWIN JOSUE ESPINAL MARTINEZ': 'DR. EDWIN JOSE ESPINAL MARTINEZ'
            };

            // Helper para resolver un médico por código o nombre contra el catálogo oficial
            $scope.buscarMedicoPorTexto = function (input) {
                if (!input) return null;
                var raw = input.toString().trim().toUpperCase();
                if (!raw) return null;

                var list = $scope.medicosList || [];

                // 1. Coincidencia por Código (COD_MED)
                var porCodigo = list.find(function (m) {
                    return m.COD_MED && m.COD_MED.toString().trim() === raw;
                });
                if (porCodigo) return porCodigo;

                // 2. Coincidencia exacta por Nombre Oficial (NOM_MED)
                var porNombreExacto = list.find(function (m) {
                    return m.NOM_MED && m.NOM_MED.trim().toUpperCase() === raw;
                });
                if (porNombreExacto) return porNombreExacto;

                // 3. Coincidencia por Alias conocido
                if (aliasMedicosMap[raw]) {
                    var target = aliasMedicosMap[raw];
                    var porAlias = list.find(function (m) {
                        return m.NOM_MED && m.NOM_MED.trim().toUpperCase() === target;
                    });
                    if (porAlias) return porAlias;
                }

                // 4. Coincidencia limpia normalizada
                var clean = normalizarClaveMedico(raw);
                if (!clean) return null;

                var porLimpio = list.find(function (m) {
                    return normalizarClaveMedico(m.NOM_MED) === clean;
                });
                if (porLimpio) return porLimpio;

                // 5. Coincidencia por conjunto de palabras significativas (>2 letras)
                var palabras = clean.split(' ').filter(function (p) { return p.length > 2; });
                if (palabras.length > 0) {
                    var candidatos = list.filter(function (m) {
                        var cleanM = normalizarClaveMedico(m.NOM_MED);
                        return palabras.every(function (p) { return cleanM.indexOf(p) !== -1; });
                    });
                    if (candidatos.length === 1) return candidatos[0];
                }

                // 6. Coincidencia por contención de subcadena
                var porSubstring = list.find(function (m) {
                    var cleanM = normalizarClaveMedico(m.NOM_MED);
                    return cleanM.indexOf(clean) !== -1 || clean.indexOf(cleanM) !== -1;
                });
                if (porSubstring) return porSubstring;

                return null;
            };

            // Helper para buscar especialidad
            function buscarEspecialidad(nombreMedico) {
                if (!nombreMedico) return '';
                var medico = $scope.buscarMedicoPorTexto(nombreMedico);
                return medico ? medico.ESPECIALIDAD : 'MEDICO GENERAL';
            }

            // Datos del modal (Base)
            var defaultModalData = {
                cantidad: 1,
                fecha: '', cm: '', medico: '',
                identidad: '', nombre_paciente: '', fecha_nacimiento: '',
                sexo: '', edad: '', tipo: '', cond: '',
                cod_col: '', colonia: '',
                referido_a: '', referido_de: '', pg_emb: ''
            };
            for (var i = 1; i <= 7; i++) {
                defaultModalData['cod_' + i] = '';
                defaultModalData['diagnostico_' + i] = '';
                defaultModalData['cond_' + i] = '';
            }

            // Intentar cargar desde LocalStorage
            var savedData = localStorage.getItem('modalIngresoData');
            $scope.modalData = savedData ? JSON.parse(savedData) : angular.copy(defaultModalData);

            // Watcher para forzar mayúsculas y VALIDAR DATOS en el modal
            $scope.$watch('modalData', function (newVal) {
                if (newVal) {
                    for (var key in newVal) {
                        if (typeof newVal[key] === 'string' && key !== 'cantidad') {
                            // 1. Mayúsculas
                            newVal[key] = newVal[key].toUpperCase();
                            var val = newVal[key];

                            // 2. Validaciones Específicas
                            if (key === 'sexo') {
                                if (val !== 'M' && val !== 'H') newVal[key] = '';
                            }
                            else if (key === 'se') {
                                newVal[key] = val.replace(/[^0-9]/g, '');
                            }
                            else if (key === 'tipo') {
                                if (val !== 'A' && val !== 'M' && val !== 'D') newVal[key] = '';
                            }
                            else if (key.indexOf('cond') === 0) { // cond, cond_1, cond_2...
                                if (val !== 'N' && val !== 'S') newVal[key] = '';
                            }
                            else if (key === 'edad') {
                                // Permitir escribir, pero validar rango final numerico
                                // Mientras sea string con numeros se deja, pero si rompe rango puede borrarse
                                // Para UX en modal, mejor solo dejar numeros y validar rango
                                var clean = val.replace(/[^0-9]/g, '');
                                if (clean !== val) {
                                    newVal[key] = clean;
                                    val = clean;
                                }
                                var num = parseInt(val, 10);
                                if (!isNaN(num)) {
                                    if (num < 0) newVal[key] = ''; // Solo borrar negativos en input
                                }
                            }
                            // Auto-completar diagnósticos (cod_1 -> diagnostico_1, etc.)
                            else if (key.match(/^cod_[1-7]$/)) {
                                var numero = key.split('_')[1];
                                var diagKey = 'diagnostico_' + numero;
                                if (val) {
                                    var diagEncontrado = ($scope.diagnosticosList || []).find(function (d) {
                                        return d.codigo == val;
                                    });
                                    if (diagEncontrado) {
                                        newVal[diagKey] = diagEncontrado.patologia;
                                    } else {
                                        newVal[diagKey] = 'DIAGNOSTICO NO EXISTE';
                                    }
                                } else {
                                    newVal[diagKey] = '';
                                }
                            }
                        }
                    }
                    // Aplicar conversión de edad al final de la validación
                    $scope.calcularConversion(newVal);
                    $scope.calcularRangos(newVal);
                    $scope.calcularPgEmbYSm(newVal);
                }
                localStorage.setItem('modalIngresoData', JSON.stringify(newVal));
            }, true);

            // Helper para conversion de Edad en cascada (D->M->A)
            $scope.calcularConversion = function (obj) {
                if (!obj.edad || !obj.tipo) return;
                var edad = parseInt(obj.edad, 10);
                var tipo = (obj.tipo || '').toUpperCase();

                if (isNaN(edad)) return;

                var changed = true;
                while (changed) {
                    changed = false;
                    if (tipo === 'D' && edad > 30) {
                        edad = Math.floor(edad / 30);
                        tipo = 'M';
                        changed = true;
                    } else if (tipo === 'M' && edad > 11) {
                        edad = Math.floor(edad / 12);
                        tipo = 'A';
                        changed = true;
                    }
                }
                obj.edad = edad.toString();
                obj.tipo = tipo;
            };

            // --- LÓGICA DE RANGOS (Adaptada de create.blade.php) ---
            var rango1Categorias = ['1. MENOR DE 1 MES', '2. DE 1 MES A 1 AÑO', '3. DE 1 A 4 AÑOS', '4. DE 5 A 9 AÑOS', '5. DE 10 A 14 AÑOS', '6. DE 15 A 19 AÑOS', '7. DE 20 A 49 AÑOS', '8. DE 50 A 59 AÑOS', '9. MAYORES DE 60 AÑOS'];
            var rango2Categorias = ['MENOR DE 1 MES', 'DE 1 A 2 MESES', 'DE 2 MES A 1 AÑO', 'DE 1 A 4 AÑOS', 'DE 5 A 9 AÑOS', 'DE 10 A 14 AÑOS', 'DE 15 A 19 AÑOS', 'DE 20 A 49 AÑOS', 'DE 50 A 59 AÑOS', 'MAYORES DE 60 AÑOS'];
            var rango3Categorias = ['MENOR 1 AÑO', '1 - 4 AÑOS', '5 A 9 AÑOS', '10 A 14 AÑOS', '15 A 19 AÑOS', '20 A 24 AÑOS', '25 A 39 AÑOS', '40 A 59 AÑOS', '60 Y MAS'];
            var rango4Categorias = ['MENOR 1 AÑO', '1 - 4 AÑOS', '5 A 9 AÑOS', '10 A 14 AÑOS', '15 A 19 AÑOS', '20 A 24 AÑOS', '25 A 29 AÑOS', '30 A 49 AÑOS', '50 Y +'];
            var rango5Categorias = ['MENORES DE 5 AÑOS', 'DE 5 A 14 AÑOS', 'MAYORES DE 15 AÑOS'];

            function getCategoriaRango1(e) { if (e < .08) return 0; if (e < 1) return 1; if (e < 5) return 2; if (e < 10) return 3; if (e < 15) return 4; if (e < 20) return 5; if (e < 50) return 6; if (e < 60) return 7; return 8 }
            function getCategoriaRango2(e) { if (e < .08) return 0; if (e < .16) return 1; if (e < 1) return 2; if (e < 5) return 3; if (e < 10) return 4; if (e < 15) return 5; if (e < 20) return 6; if (e < 50) return 7; if (e < 60) return 8; return 9 }
            function getCategoriaRango3(e) { if (e < 1) return 0; if (e < 5) return 1; if (e < 10) return 2; if (e < 15) return 3; if (e < 20) return 4; if (e < 25) return 5; if (e < 40) return 6; if (e < 60) return 7; return 8 }
            function getCategoriaRango4(e) { if (e < 1) return 0; if (e < 5) return 1; if (e < 10) return 2; if (e < 15) return 3; if (e < 20) return 4; if (e < 25) return 5; if (e < 30) return 6; if (e < 50) return 7; return 8 }
            function getCategoriaRango5(e) { if (e < 5) return 0; if (e < 15) return 1; return 2 }

            $scope.calcularRangos = function (obj) {
                if (!obj.edad || !obj.tipo) return;
                var edad = parseFloat(obj.edad);
                var tipo = obj.tipo.toUpperCase();
                if (isNaN(edad)) return;

                // Normalizar a años
                var edadEnAnios = edad;
                if (tipo === 'M') edadEnAnios = edad / 12;
                else if (tipo === 'D') edadEnAnios = edad / 365;

                obj.rango = rango1Categorias[getCategoriaRango1(edadEnAnios)];
                obj.rango_2 = rango2Categorias[getCategoriaRango2(edadEnAnios)];
                obj.rango_3 = rango3Categorias[getCategoriaRango3(edadEnAnios)];
                obj.rango_4 = rango4Categorias[getCategoriaRango4(edadEnAnios)];
                obj.rango_5 = rango5Categorias[getCategoriaRango5(edadEnAnios)];
            };

            // Cálculo automático de PG_EMB y SM
            $scope.calcularPgEmbYSm = function (obj) {
                var tieneEmbarazo = false;
                var categoriaSM = '';

                // Palabras clave para detectar embarazo
                var palabrasEmbarazo = [
                    'PRENATAL',
                    'GESTACION',
                    'GESTACIONAL',

                ];

                // Revisar todos los diagnósticos (cod_1 a cod_7)
                for (var i = 1; i <= 7; i++) {
                    var codKey = 'cod_' + i;
                    var diagKey = 'diagnostico_' + i;
                    var codigo = obj[codKey];
                    var textodiag = (obj[diagKey] || '').toUpperCase();

                    if (codigo) {
                        var diagEncontrado = ($scope.diagnosticosList || []).find(function (d) {
                            return d.codigo == codigo;
                        });

                        if (diagEncontrado) {
                            // Verificar si requiere embarazo (campo en BD)
                            if (diagEncontrado.requiere_embarazo) {
                                tieneEmbarazo = true;
                            }

                            // Verificar si tiene categoría SM (SM03, SM07, etc.)
                            var categoria = (diagEncontrado.categoria || '').toString().trim().toUpperCase();
                            if (categoria.indexOf('SM') !== -1) {
                                categoriaSM = 'SM1';
                                console.log('✅ SM detectado en diagnóstico:', diagEncontrado.codigo, diagEncontrado.patologia, 'categoria:', categoria);
                            }

                            // Verificar por texto de patología
                            var patologia = (diagEncontrado.patologia || '').toUpperCase();
                            for (var j = 0; j < palabrasEmbarazo.length; j++) {
                                if (patologia.indexOf(palabrasEmbarazo[j]) !== -1) {
                                    tieneEmbarazo = true;
                                    break;
                                }
                            }
                        }
                    }

                    // También verificar en el texto del diagnóstico ingresado
                    if (textodiag) {
                        for (var k = 0; k < palabrasEmbarazo.length; k++) {
                            if (textodiag.indexOf(palabrasEmbarazo[k]) !== -1) {
                                tieneEmbarazo = true;
                                break;
                            }
                        }
                    }
                }

                // Actualizar PG_EMB
                obj.pg_emb = tieneEmbarazo ? 'EMBARAZADA' : 'POBLACIONGENERAL';

                // Actualizar SM
                obj.sm = categoriaSM;

                // Log para debugging
                console.log('📊 PG_EMB y SM calculados:', {
                    pg_emb: obj.pg_emb,
                    sm: obj.sm,
                    codigos_revisados: [obj.cod_1, obj.cod_2, obj.cod_3, obj.cod_4, obj.cod_5, obj.cod_6, obj.cod_7].filter(Boolean)
                });
            };

            // Watcher para autocompletar Médico en el Modal (CM -> Nombre/Prof)
            $scope.$watch('modalData.cm', function (newVal) {
                if (newVal) {
                    var codigo = newVal.toString().trim();
                    if (codigo) {
                        var medicoEncontrado = $scope.buscarMedicoPorTexto(codigo);
                        if (medicoEncontrado) {
                            $scope.modalData.cm = medicoEncontrado.COD_MED;
                            $scope.modalData.medico = medicoEncontrado.NOM_MED;
                            $scope.modalData.prof = medicoEncontrado.ESPECIALIDAD;
                            $scope.modalData.jornada = medicoEncontrado.JORNADA || '';
                        }
                    }
                }
            });

            // Watcher para autocompletar CM y Profesión cuando se ingresa o pega el Nombre del Médico
            $scope.$watch('modalData.medico', function (newVal, oldVal) {
                if (newVal && newVal !== oldVal) {
                    var medicoEncontrado = $scope.buscarMedicoPorTexto(newVal);
                    if (medicoEncontrado && $scope.modalData.medico !== medicoEncontrado.NOM_MED) {
                        $scope.modalData.medico = medicoEncontrado.NOM_MED;
                        $scope.modalData.cm = medicoEncontrado.COD_MED;
                        $scope.modalData.prof = medicoEncontrado.ESPECIALIDAD;
                        $scope.modalData.jornada = medicoEncontrado.JORNADA || '';
                    }
                }
            });

            // Watcher para autocompletar Colonia en el Modal (COD_COL -> Nombre)
            $scope.$watch('modalData.cod_col', function (newVal) {
                if (newVal) {
                    var codigo = newVal.toString().trim();
                    if (codigo) {
                        var coloniaEncontrada = ($scope.coloniasList || []).find(function (c) {
                            return c.COD_COL == codigo;
                        });

                        if (coloniaEncontrada) {
                            $scope.modalData.colonia = coloniaEncontrada.COLONIA;
                        } else {
                            $scope.modalData.colonia = 'COLONIA NO EXISTE';
                        }
                    } else {
                        $scope.modalData.colonia = '';
                    }
                }
            });

            function createRow(data = {}) {
                var row = {};
                columns.forEach(function (col) { row[col] = ''; });

                // Si viene data del modal, sobreescribir
                if (data) {
                    for (var key in data) {
                        if (row.hasOwnProperty(key)) {
                            row[key] = data[key];
                        }
                    }
                }

                // Asignar número consecutivo automáticamente
                // Si la llamamos desde ingresarMasivo, el cálculo se ajustará después con el watcher
                // Pero un valor inicial útil es length + 1
                row['numero'] = ($scope.lista ? $scope.lista.length : 0) + 1;

                return row;
            }

            // Iniciar con 1 fila vacía o recuperar de LocalStorage
            var savedTableData = localStorage.getItem('tablaIngresoData');
            if (savedTableData) {
                $scope.lista = JSON.parse(savedTableData);
            } else {
                $scope.lista = []; // Iniciar completamente vacía
            }

            // Inyectar registros importados de Excel a la tabla principal
            $scope.cargarRegistrosImportados = function(filas, yaGuardado) {
                if (!filas || !filas.length) return;
                
                // Reemplazar la lista con las nuevas filas para evitar duplicación con datos previos
                $scope.lista = angular.copy(filas);

                // Recalcular PG_EMB, SM, rangos y forzar actualización
                $scope.lista.forEach(function(row) {
                    $scope.calcularRangos(row);
                    $scope.calcularPgEmbYSm(row);
                });

                if (yaGuardado) {
                    $scope.tablaGuardada = true;
                } else {
                    $scope.marcarModificado();
                }
                localStorage.setItem('tablaIngresoData', JSON.stringify($scope.lista));
            };

            // Resumen dinámico de médicos presentes en la tabla
            $scope.getResumenMedicos = function () {
                var medicosMap = {};
                var totalFilas = ($scope.lista || []).length;

                ($scope.lista || []).forEach(function (row) {
                    var m = (row.medico || '').trim();
                    if (m && m !== 'SIN ASIGNAR' && m !== 'MEDICO NO EXISTE') {
                        medicosMap[m] = (medicosMap[m] || 0) + 1;
                    }
                });

                var nombres = Object.keys(medicosMap);
                var distinctCount = nombres.length;

                if (distinctCount === 0) {
                    var primerFila = ($scope.lista && $scope.lista[0]) ? ($scope.lista[0].medico || 'SIN ASIGNAR') : 'SIN ASIGNAR';
                    return {
                        label: 'Médico',
                        valor: primerFila,
                        count: totalFilas,
                        isMultiple: false,
                        tooltip: 'No hay médicos asignados'
                    };
                }

                if (distinctCount === 1) {
                    return {
                        label: 'Médico Responsable',
                        valor: nombres[0],
                        count: totalFilas,
                        isMultiple: false,
                        tooltip: nombres[0] + ' (' + totalFilas + ' atenciones)'
                    };
                }

                var tooltipList = nombres.map(function (nombre) {
                    return nombre + ': ' + medicosMap[nombre] + ' atenciones';
                }).join('\n');

                return {
                    label: 'Médicos (' + distinctCount + ')',
                    valor: 'VARIOS MÉDICOS (' + distinctCount + ')',
                    count: totalFilas,
                    isMultiple: true,
                    tooltip: 'Médicos presentes en la tabla:\n' + tooltipList
                };
            };

            // Resumen dinámico de fechas presentes en la tabla
            $scope.getResumenFechas = function () {
                var fechasMap = {};
                var totalFilas = ($scope.lista || []).length;

                ($scope.lista || []).forEach(function (row) {
                    var f = (row.fecha || '').trim();
                    if (f && f !== '00/00/0000') {
                        fechasMap[f] = (fechasMap[f] || 0) + 1;
                    }
                });

                var fechas = Object.keys(fechasMap);
                var distinctCount = fechas.length;

                if (distinctCount === 0) {
                    var primerFecha = ($scope.lista && $scope.lista[0]) ? ($scope.lista[0].fecha || '00/00/0000') : '00/00/0000';
                    return {
                        label: 'Fecha de Carga',
                        valor: primerFecha,
                        isMultiple: false,
                        tooltip: 'Sin fechas asignadas'
                    };
                }

                if (distinctCount === 1) {
                    return {
                        label: 'Fecha de Carga',
                        valor: fechas[0],
                        isMultiple: false,
                        tooltip: fechas[0] + ' (' + totalFilas + ' registros)'
                    };
                }

                var tooltipList = fechas.map(function (f) {
                    return f + ': ' + fechasMap[f] + ' registros';
                }).join('\n');

                return {
                    label: 'Fechas (' + distinctCount + ')',
                    valor: 'VARIAS FECHAS (' + distinctCount + ')',
                    isMultiple: true,
                    tooltip: 'Fechas presentes en la tabla:\n' + tooltipList
                };
            };

            window.cargarRegistrosImportadosATabla = function(filas, yaGuardado) {
                var scope = angular.element(document.querySelector('[ng-controller="TablaCtrl"]')).scope();
                if (scope) {
                    scope.$apply(function() {
                        scope.cargarRegistrosImportados(filas, yaGuardado);
                    });
                }
            };

            // Watcher para mantener 'numero' actualizado y GUARDAR EN LOCALSTORAGE
            $scope.$watch('lista', function (newVal) {
                if (newVal) {
                    // 1. Renumerar filas
                    newVal.forEach(function (row, index) {
                        row.numero = index + 1;
                    });
                    // 2. Guardar persistencia
                    localStorage.setItem('tablaIngresoData', JSON.stringify(newVal));
                }
            }, true); // true = Deep Watch (detecta cambios en celdas)

            $scope.eliminar = function (row) {
                if ($scope.lista.length <= 1) {
                    Swal.fire('Atención', 'Debe haber al menos una fila en la tabla.', 'warning');
                    return;
                }
                $scope.lista.splice(row, 1);
                $scope.marcarModificado();
            };

            $scope.agregar = function () {
                $scope.lista.push(createRow());
                $scope.marcarModificado();
                // Scroll al final
                setTimeout(function () { window.scrollTo(0, document.body.scrollHeight); }, 100);
            };

            $scope.limpiarTabla = function () {
                Swal.fire({
                    title: '¿Estás seguro?',
                    text: "Se borrarán todos los datos de la tabla.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: 'Sí, limpiar todo',
                    cancelButtonText: 'Cancelar'
                }).then(function (result) {
                    if (result.isConfirmed) {
                        $scope.$apply(function () {
                            $scope.lista = []; // Tabla totalmente vacía
                            $scope.tablaGuardada = false;
                            localStorage.removeItem('tablaIngresoData');
                        });
                        Swal.fire('¡Limpia!', 'La tabla ha sido reiniciada.', 'success');
                    }
                });
            };

            $scope.limpiarModal = function () {
                $scope.modalData = angular.copy(defaultModalData);
                // El watcher se encargará de actualizar el LocalStorage automáticamente
            };

            $scope.ingresarMasivo = function () {
                var qty = parseInt($scope.modalData.cantidad) || 1;

                // Helper para verificar si una fila está vacía
                function isRowEmpty(row) {
                    for (var i = 0; i < columns.length; i++) {
                        var val = row[columns[i]];
                        // Verificar si tiene valor y no es solo espacios
                        if (val && val.toString().trim() !== '') return false;
                    }
                    return true;
                }

                // 1. Limpiar la tabla completamente antes de agregar las nuevas filas
                $scope.lista = [];

                // Copiar datos del modal para las nuevas filas
                var rowData = angular.copy($scope.modalData);
                delete rowData.cantidad;

                // Pre-calcular campos derivados (Año, Mes, SE) si hay fecha
                // Esto asegura que la primera fila (y todas) tengan estos datos
                if (rowData.fecha && rowData.fecha.length === 10) {
                    var parts = rowData.fecha.split('/');
                    if (parts.length === 3) {
                        var day = parseInt(parts[0], 10);
                        var month = parseInt(parts[1], 10) - 1;
                        var year = parseInt(parts[2], 10);
                        var monthNames = ["ENERO", "FEBRERO", "MARZO", "ABRIL", "MAYO", "JUNIO", "JULIO", "AGOSTO", "SEPTIEMBRE", "OCTUBRE", "NOVIEMBRE", "DICIEMBRE"];

                        rowData.ano = parts[2];
                        rowData.mes = monthNames[month] || parts[1];

                        // Función interna duplicada para acceso scope (o mover getEpidemiologicalWeek a scope superior)
                        // Para simplicidad, recalculamos aquí mismo con la misma lógica
                        // Nota: La función getEpidemiologicalWeek está dentro de la directiva, necesitamos moverla o duplicarla.
                        // Lo mejor es moverla al controlador. 

                        function getEpidemWeek(date) {
                            var d = new Date(date.getTime());
                            d.setHours(12, 0, 0, 0);
                            var y = d.getFullYear();
                            var jan1 = new Date(y, 0, 1, 12, 0, 0);
                            var dayOfJan1 = jan1.getDay();
                            var firstSun = (dayOfJan1 === 0) ? jan1 : new Date(y, 0, 1 + (7 - dayOfJan1), 12, 0, 0);
                            if (d < firstSun) return 53;
                            return Math.floor(((d - firstSun) / 86400000) / 7) + 1;
                        }

                        var dateObj = new Date(year, month, day, 12, 0, 0);
                        rowData.se = getEpidemWeek(dateObj);
                    }
                }

                // Auto-llenar especialidad (PROF) si hay médico
                if (rowData.medico) {
                    rowData.prof = buscarEspecialidad(rowData.medico);
                }

                // 2. Agregar las nuevas filas con los datos del modal
                for (var k = 0; k < qty; k++) {
                    $scope.lista.push(createRow(rowData));
                }

                $scope.marcarModificado();

                // Cerrar modal
                $('#modalIngresoMasivo').modal('hide');

                const Toast = Swal.mixin({
                    toast: true,
                    position: 'top-end',
                    showConfirmButton: false,
                    timer: 3000
                });
                Toast.fire({
                    icon: 'success',
                    title: qty + ' filas agregadas (vacías limpiadas)'
                });
            };

            $scope.recuperarValores = function () {
                document.getElementById('json-output-container').style.display = 'block';
                document.getElementById('JSON').textContent = JSON.stringify($scope.lista, null, 2);
                window.scrollTo(0, document.body.scrollHeight);
            };


            // Variables para modales de búsqueda
            $scope.searchMedicoText = '';
            $scope.searchDiagnosticoText = '';
            $scope.searchColoniaText = '';
            $scope.currentFieldForModal = null; // Guardar referencia del campo actual

            // Funciones para abrir modales
            $scope.abrirBuscadorMedicos = function (field) {
                $scope.currentFieldForModal = field;
                $scope.searchMedicoText = '';
                $('#modalBuscadorMedicos').modal('show');
                setTimeout(function () { $('#searchMedico').focus(); }, 500);
            };

            $scope.abrirBuscadorDiagnosticos = function (field) {
                $scope.currentFieldForModal = field;
                $scope.searchDiagnosticoText = '';
                $('#modalBuscadorDiagnosticos').modal('show');
                setTimeout(function () { $('#searchDiagnostico').focus(); }, 500);
            };

            $scope.abrirBuscadorColonias = function (context) {
                $scope.currentFieldForModal = context;
                $scope.searchColoniaText = '';
                $('#modalBuscadorColonias').modal('show');
                setTimeout(function () { $('#searchColonia').focus(); }, 500);
            };

            $scope.abrirBuscadorReferencias = function (context) {
                $scope.currentFieldForModal = context;
                $scope.searchReferenciaText = '';
                $('#modalBuscadorReferencias').modal('show');
                setTimeout(function () { $('#searchReferencia').focus(); }, 500);
            };

            // Funciones para seleccionar desde modales
            $scope.seleccionarMedico = function (medico) {
                if ($scope.currentFieldForModal && $scope.currentFieldForModal.row !== undefined) {
                    var row = $scope.currentFieldForModal.row;
                    $scope.lista[row].cm = medico.COD_MED;
                    $scope.lista[row].medico = medico.NOM_MED;
                    $scope.lista[row].prof = medico.ESPECIALIDAD;
                    $scope.lista[row].jornada = medico.JORNADA || '';
                    $scope.marcarModificado();
                } else {
                    $scope.modalData.cm = medico.COD_MED;
                    $scope.modalData.medico = medico.NOM_MED;
                    $scope.modalData.prof = medico.ESPECIALIDAD;
                    $scope.modalData.jornada = medico.JORNADA || '';
                    const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2500 });
                    Toast.fire({ icon: 'success', title: 'Médico seleccionado: ' + medico.NOM_MED });
                }
                $('#modalBuscadorMedicos').modal('hide');
            };

            $scope.seleccionarDiagnostico = function (diagnostico) {
                if ($scope.currentFieldForModal) {
                    var row = $scope.currentFieldForModal.row;
                    var field = $scope.currentFieldForModal.field;

                    // Determinar qué par de campos actualizar (cod_X / diagnostico_X)
                    // Si el campo clickeado es cod_X o diagnostico_X, actualizamos ambos
                    var num = null;
                    if (field.match(/^cod_\d$/)) num = field.split('_')[1];
                    else if (field.match(/^diagnostico_\d$/)) num = field.split('_')[1];

                    if (num) {
                        $scope.lista[row]['cod_' + num] = diagnostico.codigo;
                        $scope.lista[row]['diagnostico_' + num] = diagnostico.patologia;
                        // Recalcular PG_EMB y SM
                        $scope.calcularPgEmbYSm($scope.lista[row]);
                    }
                } else {
                    Swal.fire('Seleccionado', 'Diagnóstico: ' + diagnostico.patologia, 'info');
                }
                $('#modalBuscadorDiagnosticos').modal('hide');
            };

            $scope.seleccionarColonia = function (colonia) {
                if ($scope.currentFieldForModal) {
                    var row = $scope.currentFieldForModal.row;
                    $scope.lista[row].cod_col = colonia.COD_COL;
                    $scope.lista[row].colonia = colonia.COLONIA;
                } else {
                    Swal.fire('Seleccionado', 'Colonia: ' + colonia.COLONIA, 'info');
                }
                $('#modalBuscadorColonias').modal('hide');
            };

            $scope.seleccionarReferencia = function (referencia, tipo) {
                if ($scope.currentFieldForModal) {
                    var row = $scope.currentFieldForModal.row;
                    $scope.lista[row][tipo] = referencia.nombre;
                } else {
                    // Si se abrió desde el botón general, preguntar dónde aplicarlo o usar modalData si está abierto
                    if ($('#modalIngresoMasivo').is(':visible')) {
                        $scope.modalData[tipo] = referencia.nombre;
                    } else {
                        Swal.fire('Seleccionado', 'Referencia: ' + referencia.nombre, 'info');
                    }
                }
                $('#modalBuscadorReferencias').modal('hide');
            };

            // Consulta de Identidad para Modal de Ingreso Masivo
            $scope.consultarIdentidadModal = function () {
                var dni = ($scope.modalData.identidad || '').trim();
                if (!dni) {
                    $scope.modalData.identidad = '';
                    $scope.modalData.nombre_paciente = '';
                    $scope.modalData.fecha_nacimiento = '';
                    $scope.modalData.sexo = '';
                    $scope.modalData.edad = '';
                    $scope.modalData.tipo = '';
                    $scope.modalData.rango = '';
                    $scope.modalData.rango_2 = '';
                    $scope.modalData.rango_3 = '';
                    $scope.modalData.rango_4 = '';
                    $scope.modalData.rango_5 = '';
                    return;
                }
                if (dni.length < 8) return;
                var clean = dni.replace(/\D/g, '');
                if (clean.length === 13) {
                    $scope.modalData.identidad = clean.substr(0,4) + '-' + clean.substr(4,4) + '-' + clean.substr(8,5);
                    dni = $scope.modalData.identidad;
                }

                $.ajax({
                    url: '{{ route("ingresos.buscar-identidad") }}',
                    type: 'GET',
                    data: { identidad: dni },
                    success: function (res) {
                        if (res && res.success) {
                            $scope.$apply(function () {
                                if (res.nombre_paciente) $scope.modalData.nombre_paciente = res.nombre_paciente.toUpperCase();
                                if (res.fecha_nacimiento) {
                                    var fn = res.fecha_nacimiento;
                                    if (fn.match(/^\d{4}-\d{2}-\d{2}$/)) {
                                        var p = fn.split('-');
                                        fn = p[2] + '/' + p[1] + '/' + p[0];
                                    }
                                    $scope.modalData.fecha_nacimiento = fn;
                                }
                                if (res.sexo) {
                                    var sx = res.sexo.toUpperCase();
                                    if (sx === 'FEMENINO' || sx === 'MUJER' || sx === 'F' || sx === 'M') sx = 'M';
                                    else if (sx === 'MASCULINO' || sx === 'HOMBRE' || sx === 'H' || sx === 'V') sx = 'H';
                                    $scope.modalData.sexo = sx;
                                }
                                if (res.edad !== undefined && res.edad !== null && res.edad !== '') $scope.modalData.edad = res.edad.toString();
                                if (res.tipo) $scope.modalData.tipo = res.tipo.toUpperCase();
                                if (res.colonia && !$scope.modalData.colonia) $scope.modalData.colonia = res.colonia.toUpperCase();

                                $scope.calcularConversion($scope.modalData);
                                $scope.calcularRangos($scope.modalData);
                            });

                            const Toast = Swal.mixin({
                                toast: true,
                                position: 'top-end',
                                showConfirmButton: false,
                                timer: 2500
                            });
                            Toast.fire({
                                icon: 'success',
                                title: (res.origen === 'snvs' ? '⚡ SNVS: ' : '✅ ') + (res.nombre_paciente || 'Paciente encontrado')
                            });
                        }
                    }
                });
            };

            // Cálculo de edad desde fecha de nacimiento en Modal
            $scope.calcularEdadDesdeNacimientoModal = function () {
                var text = ($scope.modalData.fecha_nacimiento || '').trim();
                if (!text || text.length < 8) return;
                var parts = text.split(/[\/\-\.]/);
                if (parts.length === 3) {
                    var day = parseInt(parts[0], 10);
                    var month = parseInt(parts[1], 10) - 1;
                    var year = parseInt(parts[2], 10);
                    if (year < 100) year += (year > 30 ? 1900 : 2000);
                    var bDate = new Date(year, month, day);
                    if (!isNaN(bDate.getTime())) {
                        var today = new Date();
                        var ageYears = today.getFullYear() - bDate.getFullYear();
                        var mDiff = today.getMonth() - bDate.getMonth();
                        if (mDiff < 0 || (mDiff === 0 && today.getDate() < bDate.getDate())) {
                            ageYears--;
                        }
                        if (ageYears >= 1) {
                            $scope.modalData.edad = ageYears.toString();
                            $scope.modalData.tipo = 'A';
                        } else {
                            var months = (today.getFullYear() - bDate.getFullYear()) * 12 + (today.getMonth() - bDate.getMonth());
                            if (today.getDate() < bDate.getDate()) months--;
                            if (months >= 1) {
                                $scope.modalData.edad = months.toString();
                                $scope.modalData.tipo = 'M';
                            } else {
                                var diffTime = Math.abs(today - bDate);
                                var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                                $scope.modalData.edad = diffDays.toString();
                                $scope.modalData.tipo = 'D';
                            }
                        }
                        $scope.calcularRangos($scope.modalData);
                    }
                }
            };

            // Atajos de teclado globales
            document.addEventListener('keydown', function (e) {
                // Alt+M para Médicos
                if (e.altKey && (e.key === 'm' || e.key === 'M')) {
                    e.preventDefault();
                    $scope.$apply(function () {
                        // Si estamos en una celda relevante, pasamos el contexto, sino null
                        $scope.abrirBuscadorMedicos($scope.currentFieldForModal);
                    });
                }
                // Alt+D para Diagnósticos
                else if (e.altKey && (e.key === 'd' || e.key === 'D')) {
                    e.preventDefault();
                    $scope.$apply(function () {
                        $scope.abrirBuscadorDiagnosticos($scope.currentFieldForModal);
                    });
                }
                // Alt+C para Colonias
                else if (e.altKey && (e.key === 'c' || e.key === 'C')) {
                    e.preventDefault();
                    $scope.$apply(function () {
                        $scope.abrirBuscadorColonias($scope.currentFieldForModal);
                    });
                }
                // Alt+R para Referencias
                else if (e.altKey && (e.key === 'r' || e.key === 'R')) {
                    e.preventDefault();
                    $scope.$apply(function () {
                        $scope.abrirBuscadorReferencias($scope.currentFieldForModal);
                    });
                }
            });
            // Función para enviar datos al servidor
            function enviarDatosServidor(datos) {
                // CONVERSIÓN DE FECHAS FINAL: Asegurar YYYY-MM-DD
                // Esto protege contra caché o llamadas desde otros puntos
                var datosParaEnviar = datos.map(function (item) {
                    var fila = angular.copy(item);
                    
                    // Recalcular rangos para estandarizar (asegurar formato sin prefijos y corregir discrepancias)
                    $scope.calcularRangos(fila);

                    if (fila.fecha && typeof fila.fecha === 'string' && fila.fecha.indexOf('/') > -1) {
                        var parts = fila.fecha.split('/');
                        if (parts.length === 3) {
                            fila.fecha = parts[2] + '-' + parts[1] + '-' + parts[0];
                        }
                    }
                    if (fila.fecha_nacimiento && typeof fila.fecha_nacimiento === 'string' && fila.fecha_nacimiento.indexOf('/') > -1) {
                        var pfn = fila.fecha_nacimiento.split('/');
                        if (pfn.length === 3) {
                            fila.fecha_nacimiento = pfn[2] + '-' + pfn[1] + '-' + pfn[0];
                        }
                    }
                    return fila;
                });

                Swal.fire({
                    title: 'Guardando...',
                    text: 'Por favor espere',
                    allowOutsideClick: false,
                    didOpen: function () {
                        Swal.showLoading();
                    }
                });

                // Agregar token CSRF con validación
                var tokenTag = document.querySelector('meta[name="csrf-token"]');
                var token = tokenTag ? tokenTag.getAttribute('content') : '';

                if (!token) {
                    console.error('❌ Error: No se encontró el token CSRF en el meta tag.');
                    Swal.fire('Error de Seguridad', 'El token de seguridad ha expirado o no se encuentra. Por favor, recargue la página.', 'error');
                    return;
                }

                fetch('{{ route("ingresos.storeMassive") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': token,
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ rows: datosParaEnviar })
                })
                    .then(function (response) { return response.json(); })
                    .then(function (data) {
                        if (data.success) {
                            $scope.$apply(function () {
                                $scope.tablaGuardada = true;
                                localStorage.setItem('tablaIngresoData', JSON.stringify($scope.lista));
                            });
                            Swal.fire({
                                title: '¡Guardado Exitoso!',
                                text: data.message,
                                icon: 'success',
                                confirmButtonText: 'Aceptar'
                            });
                        } else {
                            Swal.fire('Error', 'Hubo un error al guardar: ' + (data.message || ''), 'error');
                            if (data.errors) console.error(data.errors);
                        }
                    })
                    .catch(function (error) {
                        Swal.fire('Error', 'Error de red o servidor. Ver consola.', 'error');
                        console.error(error);
                    });
            }

            // Función global para enfocar errores desde SweetAlert
            window.enfocarError = function (rowIndex, fieldName) {
                Swal.close();
                var cell = $('td[row="' + rowIndex + '"][field="' + fieldName + '"]');
                if (cell.length) {
                    $('html, body').animate({ scrollTop: cell.offset().top - 200 }, 500);
                    cell.addClass('bg-danger text-white');
                    setTimeout(function () { cell.removeClass('bg-danger text-white'); cell.focus(); }, 2000);
                }
            };

            // Función global para borrar dato erróneo
            window.borrarDatoErroneo = function (rowIndex, fieldName) {
                var scope = angular.element(document.querySelector('[ng-controller="TablaCtrl"]')).scope();
                if (scope) {
                    scope.$apply(function () {
                        scope.lista[rowIndex][fieldName] = '';
                        // Si es un código de diagnóstico, limpiar también asociados
                        if (fieldName && fieldName.match(/^cod_\d+$/)) {
                            var k = fieldName.split('_')[1];
                            scope.lista[rowIndex]['diagnostico_' + k] = '';
                            scope.lista[rowIndex]['cond_' + k] = '';
                        }
                    });
                    Swal.close();
                    Swal.fire({
                        icon: 'success',
                        title: 'Dato borrado',
                        text: 'Puede intentar guardar nuevamente.',
                        toast: true,
                        position: 'top-end',
                        showConfirmButton: false,
                        timer: 3000
                    });
                }
            };

            // --- Lógica para Adolescentes ---
            $scope.listadoAdolescentes = [];
            $scope.adolescentesProcesados = false; // Flag para saber si ya pasamos por el modal

            $scope.buscarAdolescente = function (adol) {
                var searchParam = '';
                if (adol.numero_identidad && adol.numero_identidad.length >= 5) {
                    searchParam = 'numero_identidad=' + adol.numero_identidad;
                } else if (adol.no_expediente && adol.no_expediente.length >= 2) {
                    searchParam = 'no_expediente=' + adol.no_expediente;
                } else {
                    return;
                }

                const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });

                fetch('{{ route("adolescentes.buscar") }}?' + searchParam)
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data && data.nombre_completo) {
                            $scope.$apply(function () {
                                adol.no_expediente = data.no_expediente || adol.no_expediente;
                                adol.numero_identidad = data.numero_identidad || adol.numero_identidad;
                                adol.nombre_completo = data.nombre_completo || adol.nombre_completo;
                                adol.sexo = data.sexo || adol.sexo;

                                // En AngularJS, input[type="date"] requiere un objeto Date para mostrarse
                                if (data.fecha_nacimiento) {
                                    const dateParts = data.fecha_nacimiento.split('-');
                                    if (dateParts.length === 3) {
                                        // Mes es 0-indexado en el constructor de Date
                                        adol.fecha_nacimiento = new Date(dateParts[0], dateParts[1] - 1, dateParts[2]);
                                    }
                                }

                                adol.nombre_tutor = data.nombre_tutor || adol.nombre_tutor;
                                adol.colonia = data.colonia || adol.colonia;
                                adol.numero_telefono = data.numero_telefono || adol.numero_telefono;
                                adol.estado_civil = data.estado_civil || adol.estado_civil;
                                adol.escolaridad = data.escolaridad || adol.escolaridad;
                                adol.ocupacion = data.ocupacion || adol.ocupacion;
                                adol.cargado = true;

                                // Si ya existe en cualquier tabla, forzar Seguimiento (S)
                                // para el registro de adolescente
                                if (adol.cond === 'N') {
                                    adol.cond = 'S';
                                }

                                var msg = 'Paciente encontrado (' + (data.procedencia || 'Sistema') + '): ' + data.nombre_completo;
                                Toast.fire({ icon: 'success', title: msg });
                            });
                        }
                    });
            };

            $scope.cancelarAdolescentes = function () {
                var pendientes = $scope.listadoAdolescentes.filter(function (a) { return !a.guardado_ok; });
                const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
                
                // Marcamos como procesado inmediatamente para que pueda continuar con el sistema principal
                // sin importar si el guardado de adolescentes falla o tiene vacíos.
                $('#modalAdolescentes').modal('hide');
                $scope.adolescentesProcesados = true;

                if (pendientes.length > 0) {
                    Toast.fire({ icon: 'info', title: 'Sincronizando adolescentes en segundo plano...' });
                    
                    fetch('{{ route("adolescentes.guardar") }}', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json',
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Accept': 'application/json'
                        },
                        credentials: 'same-origin',
                        body: JSON.stringify({ adolescentes: pendientes })
                    })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.success) {
                            pendientes.forEach(function (a) { a.guardado_ok = true; });
                            Toast.fire({ icon: 'success', title: 'Adolescentes sincronizados.' });
                        }
                        // Siempre intentamos guardar los datos principales al final
                        $scope.$apply(function () { $scope.guardarDatos(); });
                    })
                    .catch(function (err) {
                        console.error('Error sincronizando adolescentes:', err);
                        // A pesar del error, permitimos guardar los datos principales (el modal ya no estorba)
                        $scope.$apply(function () { $scope.guardarDatos(); });
                    });
                } else {
                    $scope.guardarDatos();
                }
            };

            $scope.guardarAdolescenteIndividual = function (adol) {
                // Validación removida para permitir guardado flexible
                const Toast = Swal.mixin({ toast: true, position: 'top-end', showConfirmButton: false, timer: 2000 });
                Toast.fire({ icon: 'info', title: 'Sincronizando registro...' });

                fetch('{{ route("adolescentes.guardar") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ adolescentes: [adol] })
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        $scope.$apply(function () {
                            if (data.success) {
                                adol.guardado_ok = true;
                                Toast.fire({ icon: 'success', title: 'Adolescente guardado correctamente' });
                            } else {
                                Swal.fire('Error', 'No se pudo guardar: ' + data.error, 'error');
                            }
                        });
                    })
                    .catch(function (err) {
                        Swal.fire('Error de Conexión', 'No se pudo comunicar con el servidor.', 'error');
                    });
            };

            $scope.ejecutarGuardadoAdolescentes = function (pendientes, soloValidar, soloAdol) {
                Swal.fire({
                    title: 'Procesando Fichas...',
                    html: soloAdol ? 'Sincronizando adolescentes...' : 'Guardando datos de adolescentes (Nuevos y Seguimientos) y validando tabla principal...',
                    allowOutsideClick: false,
                    didOpen: function () { Swal.showLoading(); }
                });

                fetch('{{ route("adolescentes.guardar") }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Accept': 'application/json'
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify({ adolescentes: pendientes })
                })
                    .then(function (r) { return r.json(); })
                    .then(function (data) {
                        if (data.success) {
                            // Marcar como guardados
                            pendientes.forEach(function (a) { a.guardado_ok = true; });

                            if (soloAdol) {
                                $scope.adolescentesProcesados = true;
                                $('#modalAdolescentes').modal('hide');
                                $scope.$apply();
                                Swal.fire('Sincronización Exitosa', 'Se han sincronizado los datos de los adolescentes.', 'success');
                            } else {
                                $scope.adolescentesProcesados = true;
                                $('#modalAdolescentes').modal('hide');

                                // Una vez guardados los adolescentes, guardamos la tabla de ingresos
                                setTimeout(function () {
                                    $scope.$apply(function () {
                                        $scope.guardarDatos(soloValidar);
                                    });
                                }, 300);
                            }
                        } else {
                            Swal.fire('Error', 'No se pudieron sincronizar los datos: ' + data.error, 'error');
                        }
                    })
                    .catch(function (err) {
                        Swal.fire('Error de Conexión', 'No se pudo comunicar con el servidor de adolescentes.', 'error');
                    });
            };

            $scope.guardarAdolescentes = function (soloValidar, soloAdol) {
                // Filtrar solo los que NO han sido guardados individualmente
                var pendientes = $scope.listadoAdolescentes.filter(function (a) { return !a.guardado_ok; });

                if (pendientes.length === 0) {
                    if (!soloAdol) {
                        $('#modalAdolescentes').modal('hide');
                        $scope.adolescentesProcesados = true;
                        $scope.guardarDatos(soloValidar);
                    } else {
                        Swal.fire('Información', 'No hay registros pendientes de sincronizar.', 'info');
                    }
                    return;
                }

                // Validación de campos obligatorios removida para permitir guardado flexible

                // --- VALIDACIÓN DE DUPLICADOS (NUEVOS QUE YA EXISTEN) ---
                var duplicados = pendientes.filter(function (a) { return a.cond === 'N' && a.cargado; });
                if (duplicados.length > 0) {
                    var nombresLote = duplicados.map(function (a) {
                        return '<li class="text-left">' + a.nombre_completo + ' (' + a.numero_identidad + ')</li>';
                    }).join('');

                    Swal.fire({
                        title: 'Pacientes ya registrados',
                        html: '<div class="alert alert-warning text-xs p-2 mb-3">Se detectaron pacientes marcados como <b>NUEVOS</b> que ya tienen una ficha técnica en el sistema:</div>' +
                            '<ul class="text-xs font-bold mb-3">' + nombresLote + '</ul>' +
                            '<p class="text-sm">¿Cómo desea proceder con estos registros?</p>',
                        icon: 'question',
                        showDenyButton: true,
                        showCancelButton: true,
                        confirmButtonText: '<i class="fas fa-sync-alt mr-2"></i> CAMBIAR A SEGUIMIENTO (S)',
                        denyButtonText: '<i class="fas fa-edit mr-2"></i> GUARDAR COMO NUEVOS (ACTUALIZAR)',
                        cancelButtonText: 'CANCELAR Y REVISAR',
                        confirmButtonColor: '#0891b2',
                        denyButtonColor: '#f59e0b',
                        width: '600px'
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            // Cambiar a Seguimiento (Solo para registro de adolescente) y continuar
                            duplicados.forEach(function (a) {
                                a.cond = 'S';
                            });
                            $scope.$apply();
                            $scope.ejecutarGuardadoAdolescentes(pendientes, soloValidar, soloAdol);
                        } else if (result.isDenied) {
                            // Continuar como nuevos (El backend actualizará la ficha existente)
                            $scope.ejecutarGuardadoAdolescentes(pendientes, soloValidar, soloAdol);
                        }
                    });
                    return;
                }

                // Si no hay duplicados, ejecutar guardado normal
                $scope.ejecutarGuardadoAdolescentes(pendientes, soloValidar, soloAdol);
            };

            // Función Principal de Guardado con Validaciones
            $scope.guardarDatos = function (soloValidar) {
                var filasAProcesar = $scope.lista.filter(function (fila) {
                    return fila.fecha || fila.medico || fila.cm || fila.cod_1;
                });

                if (filasAProcesar.length === 0) {
                    Swal.fire('Atención', 'La tabla está vacía o no tiene datos válidos para guardar.', 'warning');
                    return;
                }

                // --- DETECCIÓN DE ADOLESCENTES (10-19 AÑOS) ---
                if (!$scope.adolescentesProcesados) {
                    if (soloValidar) {
                        $scope.adolescentesProcesados = true;
                    } else {
                        var ados = filasAProcesar.filter(function (f) {
                            if (!f.edad || f.tipo !== 'A') return false;
                            var e = parseInt(f.edad);
                            // Solo adolescentes de 10-19 años y cuyo expediente empiece con 'A'
                            var exp = (f.exp || '').toString().toUpperCase();
                            return e >= 10 && e <= 19 && exp.startsWith('A');
                        });

                        if (ados.length > 0) {
                            $scope.listadoAdolescentes = ados.map(function (f) {
                                return {
                                    _original: f, // Mantenemos referencia para sincronizar cambios de COND
                                    no_expediente: f.exp || '',
                                    nombre_completo: '',
                                    sexo: f.sexo,
                                    fecha_nacimiento: null,
                                    edad: f.edad,
                                    tipo: f.tipo,
                                    numero_identidad: '',
                                    nombre_tutor: '',
                                    colonia: f.colonia || '',
                                    medico_atencion: f.medico || '',
                                    usuario_registro: currentUser,
                                    numero_telefono: '',
                                    estado_civil: '',
                                    escolaridad: '',
                                    ocupacion: 'ESTUDIA',
                                    cond: f.cond || 'N',
                                    fecha: f.fecha, // Para seguimientos
                                    diagnostico_1: f.diagnostico_1,
                                    diagnostico_2: f.diagnostico_2,
                                    diagnostico_3: f.diagnostico_3,
                                    diagnostico_4: f.diagnostico_4,
                                    diagnostico_5: f.diagnostico_5,
                                    diagnostico_6: f.diagnostico_6,
                                    diagnostico_7: f.diagnostico_7,
                                    cargado: false,
                                    guardado_ok: false
                                };
                            });
                            $('#modalAdolescentes').modal('show');

                            // Búsqueda automática e inmediata de todos los adolescentes al abrir el modal
                            setTimeout(function () {
                                $scope.listadoAdolescentes.forEach(function (adol) {
                                    $scope.buscarAdolescente(adol);
                                });
                            }, 500);

                            return;
                        }
                    }
                }

                var filasConErrores = [];
                var validaciones = $scope.validacionesDiagnosticos || [];

                $scope.lista.forEach(function (fila, index) {
                    // Verificar si está vacía
                    if (!(fila.fecha || fila.medico || fila.cm || fila.cod_1)) return;

                    var numeroFila = fila.numero || (index + 1);
                    var erroresFila = [];

                    // 1. Validar Campos Obligatorios
                    if (!fila.fecha) erroresFila.push({ msg: "Falta la FECHA", field: 'fecha' });
                    if (!fila.cm && !fila.medico) erroresFila.push({ msg: "Falta el MÉDICO", field: 'cm' });

                    // Validar que el campo COND no esté vacío
                    if (!fila.cond) erroresFila.push({ msg: "Falta el campo COND - Es obligatorio", field: 'cond' });

                    // Calcular edad en días para validaciones
                    var edadDias = 0;
                    if (fila.edad && fila.tipo) {
                        var valor = parseInt(fila.edad);
                        if (fila.tipo === 'D') edadDias = valor;
                        else if (fila.tipo === 'M') edadDias = valor * 30;
                        else if (fila.tipo === 'A') edadDias = valor * 365;
                    }

                    // 2. Validar Diagnósticos
                    for (var k = 1; k <= 7; k++) {
                        var codigo = fila['cod_' + k];
                        var condicion = fila['cond_' + k];

                        // Validación: Si tiene código, debe tener COND (N o S)
                        if (codigo && !condicion) {
                            erroresFila.push({
                                msg: "El Dx <b>" + codigo + "</b> no tiene Condición (COND) - Este campo es obligatorio",
                                field: 'cond_' + k
                            });
                        }
                        // Si hay condicion pero no código (menos probable pero posible error de carga)
                        if (!codigo && condicion) {
                            erroresFila.push({
                                msg: "Hay Condición (COND) pero falta el Código del Dx",
                                field: 'cod_' + k
                            });
                        }

                        if (codigo && validaciones.length > 0) {
                            // Buscar regla para este código
                            var regla = validaciones.find(function (v) { return v.codigo == codigo; });

                            if (regla) {
                                var nombreDiag = regla.patologia || ("Dx " + codigo);

                                // --- VALIDACIÓN DE SEXO ---
                                if (fila.sexo && regla.sexo_permitido && regla.sexo_permitido !== 'ambos') {
                                    // Mapeo: Regla 'M' (Masculino) -> Paciente 'H' (Hombres)
                                    //        Regla 'F' (Femenino)  -> Paciente 'M' (Mujeres)
                                    // Nota: Se manejan ambos casos (H/M y M/F) por si el catálogo tiene distintos formatos
                                    var pacienteEsHombre = (fila.sexo === 'H');
                                    var pacienteEsMujer = (fila.sexo === 'M');

                                    var reglaSoloHombres = (regla.sexo_permitido === 'H');
                                    var reglaSoloMujeres = (regla.sexo_permitido === 'M' || regla.sexo_permitido === 'F');

                                    if (reglaSoloHombres && !pacienteEsHombre) {
                                        erroresFila.push({
                                            msg: "<b>" + nombreDiag + "</b> es exclusivo para PACIENTES HOMBRES.",
                                            field: 'cod_' + k
                                        });
                                    }
                                    if (reglaSoloMujeres && !pacienteEsMujer) {
                                        erroresFila.push({
                                            msg: "<b>" + nombreDiag + "</b> es exclusivo para PACIENTES MUJERES.",
                                            field: 'cod_' + k
                                        });
                                    }
                                }

                                // --- VALIDACIÓN DE EDAD Y TIPO REQUERIDOS ---
                                var tieneRestriccionEdad = (regla.edad_minima !== null && regla.edad_minima > 0) ||
                                    (regla.edad_maxima !== null && regla.edad_maxima < 150) ||
                                    regla.es_pediatrico ||
                                    regla.es_adulto;

                                if (tieneRestriccionEdad && (!fila.edad || !fila.tipo)) {
                                    erroresFila.push({
                                        msg: "<b>" + nombreDiag + "</b> condiciona la edad, debe ingresar la EDAD y el TIPO.",
                                        field: 'cod_' + k
                                    });
                                }

                                // --- VALIDACIÓN DE EDAD (MÍNIMA Y MÁXIMA) ---
                                if (fila.edad && fila.tipo) {
                                    // Convertir edad del paciente a días para comparación precisa
                                    var factorPaciente = 1;
                                    if (fila.tipo === 'M') factorPaciente = 30;
                                    else if (fila.tipo === 'A') factorPaciente = 365;
                                    var edadPacienteDias = parseFloat(fila.edad) * factorPaciente;

                                    // Determinar factor de la regla (basado en tipo_edad de la regla)
                                    var factorRegla = 365; // Por defecto años
                                    if (regla.tipo_edad === 'M') factorRegla = 30;
                                    else if (regla.tipo_edad === 'D') factorRegla = 1;

                                    // Validar Mínimo
                                    if (regla.edad_minima !== null && regla.edad_minima > 0) {
                                        var minDias = regla.edad_minima * factorRegla;
                                        if (edadPacienteDias < minDias) {
                                            var unidadLabel = (regla.tipo_edad === 'A' ? 'AÑOS' : (regla.tipo_edad === 'M' ? 'MESES' : 'DÍAS'));
                                            erroresFila.push({
                                                msg: "<b>" + nombreDiag + "</b> requiere una edad mínima de " + regla.edad_minima + " " + unidadLabel + ". (Paciente tiene " + fila.edad + " " + (fila.tipo === 'A' ? 'Años' : (fila.tipo === 'M' ? 'Meses' : 'Días')) + ")",
                                                field: 'cod_' + k
                                            });
                                        }
                                    }

                                    // Validar Máximo
                                    if (regla.edad_maxima !== null && regla.edad_maxima < 150) {
                                        var maxDias = regla.edad_maxima * factorRegla;
                                        if (edadPacienteDias > maxDias) {
                                            var unidadLabel = (regla.tipo_edad === 'A' ? 'AÑOS' : (regla.tipo_edad === 'M' ? 'MESES' : 'DÍAS'));
                                            erroresFila.push({
                                                msg: "<b>" + nombreDiag + "</b> permite una edad máxima de " + regla.edad_maxima + " " + unidadLabel + ". (Paciente tiene " + fila.edad + " " + (fila.tipo === 'A' ? 'Años' : (fila.tipo === 'M' ? 'Meses' : 'Días')) + ")",
                                                field: 'cod_' + k
                                            });
                                        }
                                    }
                                }

                                // --- VALIDACIÓN DE EMBARAZO ---
                                if (regla.requiere_embarazo && fila.pg_emb !== 'EMBARAZADA') {
                                    erroresFila.push({
                                        msg: "<b>" + nombreDiag + "</b> requiere la condición de EMBARAZADA (PG_EMB).",
                                        field: 'cod_' + k
                                    });
                                }

                                // --- VALIDACIÓN TIPO EDAD (PEDIÁTRICO/ADULTO) ---
                                if (fila.edad && fila.tipo) {
                                    if (regla.es_pediatrico && edadDias >= 5475) {
                                        erroresFila.push({
                                            msg: "<b>" + nombreDiag + "</b> es para pacientes PEDIÁTRICOS (menores de 15 años).",
                                            field: 'cod_' + k
                                        });
                                    }
                                    if (regla.es_adulto && edadDias < 5475) {
                                        erroresFila.push({
                                            msg: "<b>" + nombreDiag + "</b> es para pacientes ADULTOS (15 años o más).",
                                            field: 'cod_' + k
                                        });
                                    }
                                }
                            }
                        }
                    }

                    // 3. Validar Colonia (Consistencia Cód y Nombre)
                    if (fila.cod_col && !fila.colonia) {
                        erroresFila.push({
                            msg: "Tiene Cód. Colonia (" + fila.cod_col + ") pero falta el NOMBRE de la colonia",
                            field: 'colonia'
                        });
                    }
                    if (!fila.cod_col && fila.colonia) {
                        erroresFila.push({
                            msg: "Tiene Nombre de Colonia (" + fila.colonia + ") pero falta el CÓDIGO",
                            field: 'cod_col'
                        });
                    }

                    if (erroresFila.length > 0) {
                        filasConErrores.push({ fila: numeroFila, index: index, errores: erroresFila });
                    }
                });

                if (filasConErrores.length > 0) {
                    var totalErrores = filasConErrores.reduce(function(acc, item) { return acc + item.errores.length; }, 0);

                    var html = '<div class="text-left custom-scrollbar pr-1" style="max-height: 48vh; overflow-y: auto; overflow-x: hidden;">' +
                        '<div class="d-flex flex-column gap-2">';

                    filasConErrores.forEach(function (item) {
                        var erroresHtml = item.errores.map(function (err) {
                            return '<div class="d-flex align-items-center justify-content-between p-2 mb-1 rounded" style="background: rgba(239, 68, 68, 0.08); border: 1px solid rgba(239, 68, 68, 0.22); border-left: 3px solid #ef4444;">' +
                                '<div class="d-flex align-items-center" style="flex: 1; min-width: 0;">' +
                                    '<i class="fas fa-exclamation-circle text-danger mr-2" style="font-size: 13px; flex-shrink: 0;"></i>' +
                                    '<span style="font-size: 0.82rem; color: var(--text-primary, #1e293b); font-weight: 500; line-height: 1.35; word-break: break-word;">' + err.msg + '</span>' +
                                '</div>' +
                                '<div class="d-flex align-items-center gap-1 ml-2" style="flex-shrink: 0;">' +
                                    '<button type="button" class="btn btn-xs btn-outline-primary px-2 py-1 font-weight-bold" onclick="window.enfocarError(' + item.index + ', \'' + (err.field || '') + '\')" title="Ubicar y enfocar celda en la tabla">' +
                                        '<i class="fas fa-crosshairs mr-1"></i> Ir a Celda' +
                                    '</button>' +
                                    '<button type="button" class="btn btn-xs btn-outline-danger px-2 py-1 font-weight-bold" onclick="window.borrarDatoErroneo(' + item.index + ', \'' + (err.field || '') + '\', this)" title="Limpiar valor erróneo">' +
                                        '<i class="fas fa-trash-alt mr-1"></i> Limpiar' +
                                    '</button>' +
                                '</div>' +
                            '</div>';
                        }).join('');

                        html += '<div class="p-2 mb-2 rounded-lg" style="background: var(--bg-surface, #ffffff); border: 1px solid var(--border-color, #e2e8f0); box-shadow: 0 1px 3px rgba(0,0,0,0.04);">' +
                            '<div class="d-flex align-items-center justify-content-between mb-2 pb-1 border-bottom" style="border-color: var(--border-color, #e2e8f0) !important;">' +
                                '<span class="badge badge-secondary px-2 py-1 font-weight-bold" style="background: var(--bg-subtle, #f1f5f9); color: var(--text-muted, #64748b); border: 1px solid var(--border-color, #cbd5e1); font-size: 0.75rem;">' +
                                    '<i class="fas fa-list-ol mr-1"></i> FILA #' + item.fila +
                                '</span>' +
                                '<span class="text-xs text-danger font-weight-bold">' +
                                    item.errores.length + ' ' + (item.errores.length === 1 ? 'observación' : 'observaciones') +
                                '</span>' +
                            '</div>' +
                            '<div>' + erroresHtml + '</div>' +
                        '</div>';
                    });

                    html += '</div></div>' +
                        '<div class="mt-3 p-3 rounded-lg text-left" style="background: rgba(245, 158, 11, 0.08); border: 1px solid rgba(245, 158, 11, 0.25);">' +
                            '<div class="d-flex align-items-center mb-1">' +
                                '<i class="fas fa-shield-alt text-warning mr-2 font-size-16"></i>' +
                                '<span class="font-weight-bold text-warning" style="font-size: 0.85rem;">Condicionamientos pendientes de resolución</span>' +
                            '</div>' +
                            '<p class="m-0" style="color: var(--text-muted, #64748b); font-size: 0.8rem; line-height: 1.4;">' +
                                'Se detectaron datos que no cumplen las reglas de consistencia clínica del AT-1. Puede regresar a la tabla interactiva para corregirlos o proceder a forzar el guardado bajo su responsabilidad.' +
                            '</p>' +
                        '</div>';

                    Swal.fire({
                        title: '<div class="d-flex align-items-center justify-content-center gap-2 mb-2">' +
                            '<div class="d-inline-flex align-items-center justify-content-center rounded-circle mr-2" style="width: 36px; height: 36px; background: rgba(245, 158, 11, 0.15); color: #f59e0b;">' +
                                '<i class="fas fa-exclamation-triangle font-size-18"></i>' +
                            '</div>' +
                            '<span style="font-size: 1.15rem; color: var(--text-primary, #1e293b); font-weight: 800; letter-spacing: 0.3px;">OBSERVACIONES DE VALIDACIÓN (' + totalErrores + ')</span>' +
                        '</div>',
                        html: html,
                        showCancelButton: true,
                        confirmButtonText: '<i class="fas fa-save mr-1"></i> GUARDAR DE TODOS MODOS',
                        cancelButtonText: '<i class="fas fa-undo mr-1"></i> VOLVER A CORREGIR',
                        confirmButtonColor: '#ef4444',
                        cancelButtonColor: '#64748b',
                        width: '780px',
                        background: 'var(--bg-surface, #ffffff)',
                        color: 'var(--text-primary, #1e293b)',
                        customClass: {
                            popup: 'rounded-xl shadow-2xl border',
                            confirmButton: 'font-bold px-4 py-2 uppercase text-xs rounded-lg shadow-sm',
                            cancelButton: 'font-bold px-4 py-2 uppercase text-xs rounded-lg'
                        }
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            // Convertir fechas a YYYY-MM-DD antes de enviar
                            var datosEnviables = filasAProcesar.map(function (fila) {
                                var f = angular.copy(fila);
                                if (f.fecha && f.fecha.indexOf('/') > -1) {
                                    var parts = f.fecha.split('/');
                                    if (parts.length === 3) {
                                        f.fecha = parts[2] + '-' + parts[1] + '-' + parts[0];
                                    }
                                }
                                return f;
                            });
                            enviarDatosServidor(datosEnviables);
                        }
                    });
                } else {
                    // Si no hay errores y estamos en modo "solo validar"
                    if (soloValidar) {
                        Swal.fire({
                            title: 'Validación Exitosa',
                            text: 'Los datos de adolescentes se sincronizaron y la tabla principal no presenta errores de condicionamiento.',
                            icon: 'success',
                            confirmButtonText: 'ENTENDIDO'
                        });
                        return;
                    }

                    // Si no hay errores, pedir confirmación simple
                    Swal.fire({
                        title: '¿Guardar datos?',
                        text: "Se guardarán " + filasAProcesar.length + " filas.",
                        icon: 'question',
                        showCancelButton: true,
                        confirmButtonText: 'Sí, guardar',
                        cancelButtonText: 'Cancelar'
                    }).then(function (result) {
                        if (result.isConfirmed) {
                            // Convertir fechas a YYYY-MM-DD antes de enviar
                            var datosEnviables = filasAProcesar.map(function (fila) {
                                var f = angular.copy(fila);
                                if (f.fecha && f.fecha.indexOf('/') > -1) {
                                    var parts = f.fecha.split('/');
                                    if (parts.length === 3) {
                                        f.fecha = parts[2] + '-' + parts[1] + '-' + parts[0];
                                    }
                                }
                                return f;
                            });
                            enviarDatosServidor(datosEnviables);
                        }
                    });
                }
            };

        }]);

        // Helpers interactivos para el modal de errores de validación
        window.enfocarError = function(rowIndex, fieldName) {
            Swal.close();
            setTimeout(function() {
                var scope = angular.element(document.querySelector('[ng-controller="TablaCtrl"]')).scope();
                if (scope) {
                    scope.$apply(function() {
                        scope.filaSeleccionada = rowIndex;
                    });
                }
                var trs = document.querySelectorAll('#tableDemo tbody tr');
                if (trs && trs[rowIndex]) {
                    var tr = trs[rowIndex];
                    tr.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    tr.style.transition = 'all 0.3s ease';
                    tr.style.background = 'rgba(245, 158, 11, 0.25)';
                    setTimeout(function() {
                        tr.style.background = '';
                    }, 2200);

                    if (fieldName) {
                        var targetCell = tr.querySelector('[ng-model*="' + fieldName + '"]') || tr.querySelector('.' + fieldName);
                        if (targetCell && typeof targetCell.focus === 'function') {
                            targetCell.focus();
                        }
                    }
                }
            }, 250);
        };

        window.borrarDatoErroneo = function(rowIndex, fieldName, btnElement) {
            var scope = angular.element(document.querySelector('[ng-controller="TablaCtrl"]')).scope();
            if (scope && scope.lista && scope.lista[rowIndex]) {
                scope.$apply(function() {
                    if (fieldName) {
                        scope.lista[rowIndex][fieldName] = '';
                    }
                    scope.marcarModificado();
                });
                if (btnElement) {
                    var container = btnElement.closest('.d-flex.align-items-center.justify-content-between');
                    if (container) {
                        container.style.opacity = '0.5';
                        container.style.pointerEvents = 'none';
                        var textSpan = container.querySelector('span');
                        if (textSpan) {
                            textSpan.innerHTML = '<span class="text-success font-weight-bold"><i class="fas fa-check-circle mr-1"></i> Dato limpiado</span>';
                        }
                    }
                }
            }
        };

        // Inicializar Flatpickr en el modal cuando se muestra
        $(document).ready(function () {
            $('#modalIngresoMasivo').on('shown.bs.modal', function () {
                var fechaInput = document.querySelector('.flatpickr-modal');
                if (fechaInput) {
                    if (fechaInput.value === undefined) fechaInput.value = "";
                    if (!fechaInput._flatpickr) {
                        try {
                            var fp = flatpickr(fechaInput, {
                                dateFormat: "d/m/Y",
                                allowInput: true,
                                locale: "es"
                            });
                        // Eventos para abrir el calendario
                        fechaInput.addEventListener('click', function (e) {
                            e.stopPropagation();
                            fp.open();
                        });
                        fechaInput.addEventListener('dblclick', function (e) {
                            e.stopPropagation();
                            fp.open();
                        });
                        fechaInput.addEventListener('focus', function () {
                            fp.open();
                        });
                    } catch (e) {
                    }
                    }
                }
            });
        });

        // Fix para modales Bootstrap - asegurar z-index correcto
        $(document).on('show.bs.modal', '.modal', function () {
            // Remover z-index de celdas editables para que no interfieran con el modal
            $('td[contenteditable="true"]').css('z-index', '');

            var zIndex = 1040 + (10 * $('.modal:visible').length);
            $(this).css('z-index', zIndex);
            setTimeout(function () {
                $('.modal-backdrop').not('.modal-stack').css('z-index', zIndex - 1).addClass('modal-stack');
            }, 0);
        });

        $(document).on('hidden.bs.modal', '.modal', function () {
            if ($('.modal:visible').length > 0) {
                $('body').addClass('modal-open');
            }
        });

        app.directive('editableTd', [function () {
            return {
                restrict: 'A',
                link: function (scope, element, attrs) {
                    element.attr('contenteditable', 'true');

                    // Fecha: Configurar Flatpickr si es la columna de fecha
                    var fp = null;
                    if (attrs.field === 'fecha') {
                        fp = flatpickr(element[0], {
                            dateFormat: "d/m/Y",
                            allowInput: true,
                            disableMobile: "true",
                            clickOpens: false,
                            onClose: function (selectedDates, dateStr, instance) {
                                if (!dateStr) return;
                                
                                scope.$apply(function () {
                                    scope.lista[attrs.row][attrs.field] = dateStr;

                                    if (dateStr.length >= 6) {
                                        var parts = dateStr.split(/[\/\-\.]/);
                                        if (parts.length === 3) {
                                            var day = parseInt(parts[0], 10);
                                            var month = parseInt(parts[1], 10) - 1;
                                            var year = parseInt(parts[2], 10);
                                            if (year < 100) year += 2000;

                                            var finalStr = ('0' + day).slice(-2) + '/' + ('0' + (month + 1)).slice(-2) + '/' + year;
                                            scope.lista[attrs.row][attrs.field] = finalStr;

                                            var monthNames = ["Enero", "Febrero", "Marzo", "Abril", "Mayo", "Junio", "Julio", "Agosto", "Septiembre", "Octubre", "Noviembre", "Diciembre"];
                                            scope.lista[attrs.row]['ano'] = year;
                                            scope.lista[attrs.row]['mes'] = monthNames[month] || parts[1];

                                            var dateObj = new Date(year, month, day, 12, 0, 0);
                                            if (typeof getEpidemiologicalWeek === 'function') {
                                                scope.lista[attrs.row]['se'] = getEpidemiologicalWeek(dateObj);
                                            }
                                        }
                                    }
                                });
                            }
                        });
                    }

                    // Función para calcular Semana Epidemiológica - AJUSTADA AL CALENDARIO DE LA IMAGEN
                    // La Semana 1 comienza el primer DOMINGO del año (o el último de dic si Enero empieza lun-sab)
                    // Basado en la imagen: El 1 de Enero (Jueves) es Semana 53.
                    // El domingo 4 de Enero comienza la Semana 1.
                    // REGLA INTUÍDA: La semana 1 es la PRIMERA semana completa o que empieza en Domingo dentro del año.
                    // Si el 1 de Enero cae Jueves, esos días pertenecen a la semana 53 del año anterior.
                    function getEpidemiologicalWeek(date) {
                        var d = new Date(date.getTime());
                        d.setHours(12, 0, 0, 0);
                        var year = d.getFullYear();

                        // Encontrar el primer domingo de este año
                        var jan1 = new Date(year, 0, 1, 12, 0, 0);
                        var dayOfJan1 = jan1.getDay(); // 0=Domingo

                        var firstSundayOfYear;
                        if (dayOfJan1 === 0) {
                            firstSundayOfYear = jan1;
                        } else {
                            // Si Enero 1 no es domingo, el primer domingo es...
                            firstSundayOfYear = new Date(year, 0, 1 + (7 - dayOfJan1), 12, 0, 0);
                        }

                        // Si la fecha actual es ANTERIOR al primer domingo del año...
                        // Entonces pertenece a la ultima semana del año anterior (o semana 53)
                        if (d < firstSundayOfYear) {
                            // Caso especial imagen: 1, 2, 3 Enero son semana 53
                            return 53; // Simplificación basada en la imagen. Lo correcto seria calcular el año anterior.
                        }

                        // Calcular semanas desde el primer domingo
                        var diffInMillis = d - firstSundayOfYear;
                        var diffInDays = Math.floor(diffInMillis / (1000 * 60 * 60 * 24));

                        return Math.floor(diffInDays / 7) + 1;
                    }

                    // Sincronización del modelo a la vista (DOM)
                    scope.$watch('lista[' + attrs.row + '].' + attrs.field, function (newVal) {
                        if (element.text() !== newVal) {
                            element.text(newVal || '');
                        }
                    });

                    // Sincronización de la vista (DOM) al modelo
                    element.bind('blur', function () {
                        // FORZAR MAYÚSCULAS
                        var rawText = element.text() || "";
                        var text = rawText.trim().toUpperCase();

                        // --- VALIDACIONES DE CAMPO ---
                        // SEXO: Solo M o H
                        if (attrs.field === 'sexo') {
                            if (text !== 'M' && text !== 'H') text = '';
                        }
                        // SE: Solo números
                        else if (attrs.field === 'se') {
                            text = text.replace(/[^0-9]/g, '');
                        }
                        // TIPO: Solo A, M, D
                        else if (attrs.field === 'tipo') {
                            if (text !== 'A' && text !== 'M' && text !== 'D') text = '';
                        }
                        // COND y COND_X: Solo N o S
                        else if (attrs.field.indexOf('cond') === 0) {
                            if (text !== 'N' && text !== 'S') text = '';
                        }
                        // EDAD: Solo números (Validación rango movida a post-computo)
                        else if (attrs.field === 'edad') {
                            var clean = text.replace(/[^0-9]/g, '');
                            if (clean) text = parseInt(clean, 10).toString();
                            else text = '';
                        }

                        element.text(text); // Actualizar visualmente de inmediato

                        element.removeClass('edit-mode nav-mode');

                        if (scope.lista[attrs.row][attrs.field] !== text || attrs.field === 'edad' || attrs.field === 'tipo') {
                            scope.$apply(function () {
                                scope.lista[attrs.row][attrs.field] = text;
                                scope.marcarModificado();

                                // Calcular converiones (Edad/Tipo)
                                scope.calcularConversion(scope.lista[attrs.row]);
                                scope.calcularRangos(scope.lista[attrs.row]);

                                // Verificar rango edad DESMUES de conversión
                                var finalEdad = parseInt(scope.lista[attrs.row].edad, 10);
                                if (!isNaN(finalEdad) && finalEdad > 150) {
                                    Swal.fire({
                                        title: 'Edad fuera de rango',
                                        text: "¿Es correcta la edad de " + finalEdad + " años?",
                                        icon: 'warning',
                                        showCancelButton: true,
                                        confirmButtonText: 'Sí, guardar',
                                        cancelButtonText: 'No, corregir'
                                    }).then(function (result) {
                                        if (!result.isConfirmed) {
                                            scope.$apply(function () {
                                                scope.lista[attrs.row].edad = '';
                                                if (attrs.field === 'edad') element.text('');
                                            });
                                            if (attrs.field === 'edad') setTimeout(function () { element.focus(); }, 100);
                                        }
                                    });
                                }

                                // Actualizar vista DOM si cambió por conversión (para el campo actual)
                                if (scope.lista[attrs.row][attrs.field] !== text) {
                                    element.text(scope.lista[attrs.row][attrs.field]);
                                }

                                // Auto-calcular Año, Mes y SE si se editó manualmente la Fecha
                                // Nota: la fecha también se pasa a mayuscula, no afecta numeros
                                // Auto-calcular Año, Mes y SE si se editó manualmente la Fecha
                                // Nota: la fecha también se pasa a mayuscula, no afecta numeros
                                if (attrs.field === 'fecha' && text.length >= 6) {
                                    var parts = text.split(/[\/\-\.]/);
                                    if (parts.length === 3) {
                                        var day = parseInt(parts[0], 10);
                                        var month = parseInt(parts[1], 10) - 1;
                                        var year = parseInt(parts[2], 10);
                                        if (year < 100) year += 2000;

                                        var finalStr = ('0' + day).slice(-2) + '/' + ('0' + (month + 1)).slice(-2) + '/' + year;
                                        if (scope.lista[attrs.row][attrs.field] !== finalStr) {
                                            scope.lista[attrs.row][attrs.field] = finalStr;
                                            element.text(finalStr);
                                        }

                                        var monthNames = ["ENERO", "FEBRERO", "MARZO", "ABRIL", "MAYO", "JUNIO", "JULIO", "AGOSTO", "SEPTIEMBRE", "OCTUBRE", "NOVIEMBRE", "DICIEMBRE"];

                                        scope.lista[attrs.row]['ano'] = year;
                                        scope.lista[attrs.row]['mes'] = monthNames[month] || parts[1];

                                        var dateObj = new Date(year, month, day, 12, 0, 0);
                                        scope.lista[attrs.row]['se'] = getEpidemiologicalWeek(dateObj);
                                    }
                                }

                                // Auto-consultar Identidad (DNI) en BD local y SNVS/SESAL
                                if (attrs.field === 'identidad') {
                                    var dni = text.trim();
                                    if (!dni) {
                                        // Al borrar el DNI, limpiar todos los datos del paciente asociados
                                        scope.lista[attrs.row]['identidad'] = '';
                                        scope.lista[attrs.row]['nombre_paciente'] = '';
                                        scope.lista[attrs.row]['fecha_nacimiento'] = '';
                                        scope.lista[attrs.row]['sexo'] = '';
                                        scope.lista[attrs.row]['edad'] = '';
                                        scope.lista[attrs.row]['tipo'] = '';
                                        scope.lista[attrs.row]['rango'] = '';
                                        scope.lista[attrs.row]['rango_2'] = '';
                                        scope.lista[attrs.row]['rango_3'] = '';
                                        scope.lista[attrs.row]['rango_4'] = '';
                                        scope.lista[attrs.row]['rango_5'] = '';
                                        scope.marcarModificado();
                                    } else if (dni.length >= 8) {
                                        var cleanDni = dni.replace(/\D/g, '');
                                        if (cleanDni.length === 13) {
                                            var formatted = cleanDni.substr(0,4) + '-' + cleanDni.substr(4,4) + '-' + cleanDni.substr(8,5);
                                            scope.lista[attrs.row]['identidad'] = formatted;
                                            element.text(formatted);
                                            dni = formatted;
                                        }

                                        $.ajax({
                                            url: '{{ route("ingresos.buscar-identidad") }}',
                                            type: 'GET',
                                            data: { identidad: dni },
                                            success: function (res) {
                                                if (res && res.success) {
                                                    scope.$apply(function () {
                                                        if (res.nombre_paciente) {
                                                            scope.lista[attrs.row]['nombre_paciente'] = res.nombre_paciente.toUpperCase();
                                                        }
                                                        if (res.fecha_nacimiento) {
                                                            var fn = res.fecha_nacimiento;
                                                            if (fn.match(/^\d{4}-\d{2}-\d{2}$/)) {
                                                                var p = fn.split('-');
                                                                fn = p[2] + '/' + p[1] + '/' + p[0];
                                                            }
                                                            scope.lista[attrs.row]['fecha_nacimiento'] = fn;
                                                        }
                                                        if (res.sexo) {
                                                            var sx = res.sexo.toUpperCase();
                                                            if (sx === 'FEMENINO' || sx === 'MUJER' || sx === 'F' || sx === 'M') sx = 'M';
                                                            else if (sx === 'MASCULINO' || sx === 'HOMBRE' || sx === 'H' || sx === 'V') sx = 'H';
                                                            scope.lista[attrs.row]['sexo'] = sx;
                                                        }
                                                        if (res.edad !== undefined && res.edad !== null && res.edad !== '') {
                                                            scope.lista[attrs.row]['edad'] = res.edad.toString();
                                                        }
                                                        if (res.tipo) {
                                                            scope.lista[attrs.row]['tipo'] = res.tipo.toUpperCase();
                                                        }
                                                        if (res.colonia && (!scope.lista[attrs.row]['colonia'] || scope.lista[attrs.row]['colonia'] === '')) {
                                                            scope.lista[attrs.row]['colonia'] = res.colonia.toUpperCase();
                                                        }

                                                        scope.calcularConversion(scope.lista[attrs.row]);
                                                        scope.calcularRangos(scope.lista[attrs.row]);
                                                        scope.calcularPgEmbYSm(scope.lista[attrs.row]);
                                                        scope.marcarModificado();
                                                    });

                                                    const Toast = Swal.mixin({
                                                        toast: true,
                                                        position: 'top-end',
                                                        showConfirmButton: false,
                                                        timer: 2500
                                                    });
                                                    Toast.fire({
                                                        icon: 'success',
                                                        title: (res.origen === 'snvs' ? '⚡ SNVS: ' : '✅ ') + (res.nombre_paciente || 'Paciente encontrado')
                                                    });
                                                }
                                            }
                                        });
                                    }
                                }

                                // Auto-calcular Edad y Rangos desde Fecha de Nacimiento
                                if (attrs.field === 'fecha_nacimiento' && text.length >= 8) {
                                    var parts = text.split(/[\/\-\.]/);
                                    if (parts.length === 3) {
                                        var day = parseInt(parts[0], 10);
                                        var month = parseInt(parts[1], 10) - 1;
                                        var year = parseInt(parts[2], 10);
                                        if (year < 100) year += (year > 30 ? 1900 : 2000);

                                        var finalStr = ('0' + day).slice(-2) + '/' + ('0' + (month + 1)).slice(-2) + '/' + year;
                                        if (scope.lista[attrs.row][attrs.field] !== finalStr) {
                                            scope.lista[attrs.row][attrs.field] = finalStr;
                                            element.text(finalStr);
                                        }

                                        var bDate = new Date(year, month, day);
                                        if (!isNaN(bDate.getTime())) {
                                            var today = new Date();
                                            var ageYears = today.getFullYear() - bDate.getFullYear();
                                            var mDiff = today.getMonth() - bDate.getMonth();
                                            if (mDiff < 0 || (mDiff === 0 && today.getDate() < bDate.getDate())) {
                                                ageYears--;
                                            }
                                            if (ageYears >= 1) {
                                                scope.lista[attrs.row]['edad'] = ageYears.toString();
                                                scope.lista[attrs.row]['tipo'] = 'A';
                                            } else {
                                                var months = (today.getFullYear() - bDate.getFullYear()) * 12 + (today.getMonth() - bDate.getMonth());
                                                if (today.getDate() < bDate.getDate()) months--;
                                                if (months >= 1) {
                                                    scope.lista[attrs.row]['edad'] = months.toString();
                                                    scope.lista[attrs.row]['tipo'] = 'M';
                                                } else {
                                                    var diffTime = Math.abs(today - bDate);
                                                    var diffDays = Math.ceil(diffTime / (1000 * 60 * 60 * 24));
                                                    scope.lista[attrs.row]['edad'] = diffDays.toString();
                                                    scope.lista[attrs.row]['tipo'] = 'D';
                                                }
                                            }
                                            scope.calcularRangos(scope.lista[attrs.row]);
                                        }
                                    }
                                }

                                // Auto-calcular Médico y Profesión al ingresar CM
                                if (attrs.field === 'cm') {
                                    var codigo = text.trim();
                                    if (codigo) {
                                        var medicoEncontrado = scope.buscarMedicoPorTexto(codigo);

                                        if (medicoEncontrado) {
                                            scope.lista[attrs.row]['cm'] = medicoEncontrado.COD_MED;
                                            scope.lista[attrs.row]['medico'] = medicoEncontrado.NOM_MED;
                                            scope.lista[attrs.row]['prof'] = medicoEncontrado.ESPECIALIDAD;
                                            scope.lista[attrs.row]['jornada'] = medicoEncontrado.JORNADA || '';
                                            element.text(medicoEncontrado.COD_MED);
                                        } else {
                                            // Médico no encontrado - Mostrar alerta
                                            scope.lista[attrs.row]['medico'] = 'MEDICO NO EXISTE';
                                            scope.lista[attrs.row]['prof'] = '';

                                            Swal.fire({
                                                title: 'Médico no encontrado',
                                                text: "El código '" + codigo + "' no existe. ¿Desea mantenerlo o borrarlo?",
                                                icon: 'warning',
                                                showCancelButton: true,
                                                confirmButtonText: 'Mantener',
                                                cancelButtonText: 'Borrar',
                                                confirmButtonColor: '#3085d6',
                                                cancelButtonColor: '#d33'
                                            }).then(function (result) {
                                                if (!result.isConfirmed) {
                                                    scope.$apply(function () {
                                                        scope.lista[attrs.row]['cm'] = '';
                                                        scope.lista[attrs.row]['medico'] = '';
                                                        scope.lista[attrs.row]['prof'] = '';
                                                        if (attrs.field === 'cm') element.text('');
                                                    });
                                                    if (attrs.field === 'cm') setTimeout(function () { element.focus(); }, 100);
                                                }
                                            });
                                        }
                                    } else {
                                        scope.lista[attrs.row]['medico'] = '';
                                        scope.lista[attrs.row]['prof'] = '';
                                    }
                                }

                                // Auto-calcular y normalizar al ingresar o escribir en Nombre del Médico
                                if (attrs.field === 'medico') {
                                    var nombreIngresado = text.trim();
                                    if (nombreIngresado) {
                                        var medicoEncontrado = scope.buscarMedicoPorTexto(nombreIngresado);

                                        if (medicoEncontrado) {
                                            scope.lista[attrs.row]['medico'] = medicoEncontrado.NOM_MED;
                                            scope.lista[attrs.row]['cm'] = medicoEncontrado.COD_MED;
                                            scope.lista[attrs.row]['prof'] = medicoEncontrado.ESPECIALIDAD;
                                            scope.lista[attrs.row]['jornada'] = medicoEncontrado.JORNADA || '';
                                            element.text(medicoEncontrado.NOM_MED);
                                        }
                                    } else {
                                        scope.lista[attrs.row]['cm'] = '';
                                        scope.lista[attrs.row]['prof'] = '';
                                    }
                                }

                                // Auto-calcular Colonia al ingresar COD_COL
                                if (attrs.field === 'cod_col') {
                                    var codigo = text.trim();
                                    if (codigo) {
                                        var coloniaEncontrada = (scope.coloniasList || []).find(function (c) {
                                            return c.COD_COL == codigo;
                                        });

                                        if (coloniaEncontrada) {
                                            scope.lista[attrs.row]['colonia'] = coloniaEncontrada.COLONIA;
                                        } else {
                                            // Colonia no encontrada - Mostrar alerta
                                            scope.lista[attrs.row]['colonia'] = 'COLONIA NO EXISTE';

                                            Swal.fire({
                                                title: 'Colonia no encontrada',
                                                text: "El código '" + codigo + "' no existe. ¿Desea mantenerlo o borrarlo?",
                                                icon: 'warning',
                                                showCancelButton: true,
                                                confirmButtonText: 'Mantener',
                                                cancelButtonText: 'Borrar',
                                                confirmButtonColor: '#3085d6',
                                                cancelButtonColor: '#d33'
                                            }).then(function (result) {
                                                if (!result.isConfirmed) {
                                                    scope.$apply(function () {
                                                        scope.lista[attrs.row]['cod_col'] = '';
                                                        scope.lista[attrs.row]['colonia'] = '';
                                                        if (attrs.field === 'cod_col') element.text('');
                                                    });
                                                    if (attrs.field === 'cod_col') setTimeout(function () { element.focus(); }, 100);
                                                }
                                            });
                                        }
                                    } else {
                                        scope.lista[attrs.row]['colonia'] = '';
                                    }
                                }

                                // Auto-calcular Diagnóstico al ingresar COD_X (cod_1 a cod_7)
                                if (attrs.field.match(/^cod_[1-7]$/)) {
                                    var numero = attrs.field.split('_')[1];
                                    var diagKey = 'diagnostico_' + numero;
                                    var codigo = text.trim();
                                    if (codigo) {
                                        var diagEncontrado = (scope.diagnosticosList || []).find(function (d) {
                                            return d.codigo == codigo;
                                        });

                                        if (diagEncontrado) {
                                            scope.lista[attrs.row][diagKey] = diagEncontrado.patologia;
                                        } else {
                                            // Diagnóstico no encontrado - Mostrar alerta
                                            scope.lista[attrs.row][diagKey] = 'DIAGNOSTICO NO EXISTE';

                                            Swal.fire({
                                                title: 'Diagnóstico no encontrado',
                                                text: "El código '" + codigo + "' no existe. ¿Desea mantenerlo o borrarlo?",
                                                icon: 'warning',
                                                showCancelButton: true,
                                                confirmButtonText: 'Mantener',
                                                cancelButtonText: 'Borrar',
                                                confirmButtonColor: '#3085d6',
                                                cancelButtonColor: '#d33'
                                            }).then(function (result) {
                                                if (!result.isConfirmed) {
                                                    scope.$apply(function () {
                                                        scope.lista[attrs.row][attrs.field] = '';
                                                        scope.lista[attrs.row][diagKey] = '';
                                                        element.text('');
                                                    });
                                                    setTimeout(function () { element.focus(); }, 100);
                                                }
                                            });
                                        }
                                    } else {
                                        scope.lista[attrs.row][diagKey] = '';
                                    }

                                    // Recalcular PG_EMB y SM después de cambiar diagnóstico
                                    scope.calcularPgEmbYSm(scope.lista[attrs.row]);
                                }

                            });
                        }
                    });

                    // Navegación: un click o focus selecciona la celda (Modo Navegación)
                    element.bind('focus', function () {
                        // Resaltar visualmente la fila en el DOM de forma instantánea sin bloquear con $apply
                        var tr = element[0].closest('tr');
                        if (tr && tr.parentElement) {
                            var prevActive = tr.parentElement.querySelector('.sticky-col.active-row');
                            if (prevActive) prevActive.classList.remove('active-row');
                            var currentSticky = tr.querySelector('.sticky-col');
                            if (currentSticky) currentSticky.classList.add('active-row');
                        }

                        // Sincronizar scope.filaSeleccionada de forma debounced para no saturar el digest loop de Angular
                        if (window._syncFilaTimeout) clearTimeout(window._syncFilaTimeout);
                        window._syncFilaTimeout = setTimeout(function () {
                            if (scope.setFilaSeleccionada) {
                                scope.setFilaSeleccionada(attrs.row);
                            } else if (scope.$parent && scope.$parent.setFilaSeleccionada) {
                                scope.$parent.setFilaSeleccionada(attrs.row);
                            }
                        }, 120);

                        element.addClass('nav-mode').removeClass('edit-mode');

                        // Solo seleccionar texto si no estamos desplazándonos a alta velocidad con flechas
                        if (!window._isArrowNavigating) {
                            if (window._selTimer) clearTimeout(window._selTimer);
                            window._selTimer = setTimeout(function () {
                                if (document.activeElement === element[0]) {
                                    try {
                                        var sel = window.getSelection();
                                        var range = document.createRange();
                                        range.selectNodeContents(element[0]);
                                        sel.removeAllRanges();
                                        sel.addRange(range);
                                    } catch (err) {}
                                }
                            }, 10);
                        }
                    });

                    // Doble click entra en Modo Edición (como Excel)
                    element.bind('dblclick', function () {
                        // Si es campo de referencia, mostrar selector
                        if (attrs.field === 'referido_a' || attrs.field === 'referido_de') {
                            scope.$apply(function () {
                                scope.abrirBuscadorReferencias({ row: attrs.row, field: attrs.field });
                            });
                            return;
                        }

                        // Si es campo de Médico (CM o Nombre)
                        if (attrs.field === 'cm' || attrs.field === 'medico') {
                            scope.$apply(function () {
                                scope.abrirBuscadorMedicos({ row: attrs.row, field: attrs.field });
                            });
                            return;
                        }

                        // Si es campo de Colonia (Cod o Nombre)
                        if (attrs.field === 'cod_col' || attrs.field === 'colonia') {
                            scope.$apply(function () {
                                scope.abrirBuscadorColonias({ row: attrs.row, field: attrs.field });
                            });
                            return;
                        }

                        // Si es campo de Diagnóstico (cod_X o diagnostico_X)
                        if (attrs.field.match(/^cod_\d$/) || attrs.field.match(/^diagnostico_\d$/)) {
                            // Extraer número
                            var num = attrs.field.split('_')[1];
                            scope.$apply(function () {
                                scope.abrirBuscadorDiagnosticos({ row: attrs.row, field: attrs.field, numero: num });
                            });
                            return;
                        }

                        element.addClass('edit-mode').removeClass('nav-mode');

                        if (fp) {
                            fp.open();
                        }

                        var sel = window.getSelection();
                        var range = document.createRange();
                        range.selectNodeContents(element[0]);
                        range.collapse(false); // Al final
                        sel.removeAllRanges();
                        sel.addRange(range);
                    });

                    // Al presionar una tecla imprimible en modo Nav, borrar y editar
                    element.bind('keypress', function (e) {
                        if (element.hasClass('nav-mode') && e.which !== 0 && !e.ctrlKey && !e.metaKey && !e.altKey) {
                            element.text(''); // Borrar contenido
                            element.addClass('edit-mode').removeClass('nav-mode');
                        }
                    });

                    // Manejador único unificado de teclado: Navegación + Control + Validación
                    element.bind('keydown', function (e) {
                        var k = e.which || e.keyCode;
                        var $this = $(this);
                        var isNav = $this.hasClass('nav-mode');

                        // --- 1. NAVEGACIÓN CON FLECHAS DIRECCIONALES ---
                        if (k >= 37 && k <= 40) {
                            // Protección contra saturación por repetición continua (throttle a ~40ms)
                            var now = Date.now();
                            if (window._lastNavTime && (now - window._lastNavTime < 40)) {
                                e.preventDefault();
                                return;
                            }
                            window._lastNavTime = now;
                            window._isArrowNavigating = true;
                            if (window._navIdleTimer) clearTimeout(window._navIdleTimer);
                            window._navIdleTimer = setTimeout(function () {
                                window._isArrowNavigating = false;
                            }, 100);

                            var colIdx = $this.index();
                            var $target = null;

                            if (k === 37) { // Flecha Izquierda
                                if (isNav || (window.getSelection && window.getSelection().anchorOffset === 0) || e.ctrlKey) {
                                    e.preventDefault();
                                    $target = $this.prevAll('td[editable-td]').first();
                                    if (!$target.length) {
                                        var prevRow = $this.closest('tr').prev('tr');
                                        if (prevRow.length) $target = prevRow.find('td[editable-td]').last();
                                    }
                                }
                            } else if (k === 38) { // Flecha Arriba
                                e.preventDefault();
                                var prevRow = $this.closest('tr').prev('tr');
                                if (prevRow.length) {
                                    $target = prevRow.children('td').eq(colIdx);
                                    if (!$target.length || $target.attr('editable-td') === undefined) {
                                        $target = prevRow.find('td[editable-td]').first();
                                    }
                                }
                            } else if (k === 39) { // Flecha Derecha
                                var textLen = $this.text().trim().length;
                                var selOffset = (window.getSelection && window.getSelection().anchorOffset !== undefined) ? window.getSelection().anchorOffset : 0;
                                if (isNav || selOffset >= textLen || e.ctrlKey) {
                                    e.preventDefault();
                                    $target = $this.nextAll('td[editable-td]').first();
                                    if (!$target.length) {
                                        var nextRow = $this.closest('tr').next('tr');
                                        if (nextRow.length) $target = nextRow.find('td[editable-td]').first();
                                    }
                                }
                            } else if (k === 40) { // Flecha Abajo
                                e.preventDefault();
                                var nextRow = $this.closest('tr').next('tr');
                                if (nextRow.length) {
                                    $target = nextRow.children('td').eq(colIdx);
                                    if (!$target.length || $target.attr('editable-td') === undefined) {
                                        $target = nextRow.find('td[editable-td]').first();
                                    }
                                }
                            }

                            if ($target && $target.length) {
                                $target[0].focus();
                            }
                            return;
                        }

                        // --- 2. TECLA ENTER ---
                        if (k === 13) {
                            e.preventDefault();
                            if (isNav) {
                                $this.dblclick(); // Entrar a editar
                            } else {
                                $this.blur();
                                var nextRow = $this.closest('tr').next('tr');
                                if (nextRow.length) {
                                    var cell = nextRow.children('td').eq($this.index());
                                    if (cell.length) cell[0].focus();
                                }
                            }
                            return;
                        }

                        // --- 3. TECLAS DE CONTROL Y ATAJOS ---
                        // Backspace(8), Tab(9), Esc(27), Delete(46), atajos con Ctrl/Meta/Alt
                        if ([8, 9, 27, 46].indexOf(k) !== -1 || e.ctrlKey || e.metaKey || e.altKey) {
                            return;
                        }

                        // --- 4. VALIDACIÓN EN TIEMPO REAL SEGÚN EL CAMPO ---
                        var char = e.key ? e.key.toUpperCase() : '';
                        var isValid = true;

                        if (attrs.field === 'sexo') {
                            if (char !== 'H' && char !== 'M') isValid = false;
                        }
                        else if (attrs.field === 'se') {
                            if (!/^[0-9]$/.test(e.key)) isValid = false;
                        }
                        else if (attrs.field === 'tipo') {
                            if (char !== 'A' && char !== 'M' && char !== 'D') isValid = false;
                        }
                        else if (attrs.field && attrs.field.indexOf('cond') === 0) {
                            if (char !== 'N' && char !== 'S') isValid = false;
                        }
                        else if (attrs.field === 'edad') {
                            if (!/^[0-9]$/.test(e.key)) isValid = false;
                        }

                        if (!isValid) {
                            e.preventDefault();
                        }
                    });
                }
            };
        }]);
    </script>
@endsection