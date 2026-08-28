<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-slate-900 leading-tight">
            {{ __('Detalles de Ingresos - ') . \Carbon\Carbon::parse($fecha)->format('d-m-Y') }}
        </h2>
    </x-slot>



    @php
        $columns = [
            'ano',
            'mes',
            'numero',
            'cm',
            'medico',
            'prof',
            'fecha',
            'se',
            'exp',
            'sexo',
            'edad',
            'tipo',
            'rango',
            'rango_2',
            'rango_3',
            'rango_4',
            'rango_5',
            'cond',
            'cod_col',
            'colonia',
            'cod_1',
            'diagnostico_1',
            'cond_1',
            'sg',
            'cod_2',
            'diagnostico_2',
            'cond_2',
            'cod_3',
            'diagnostico_3',
            'cond_3',
            'cod_4',
            'diagnostico_4',
            'cond_4',
            'cod_5',
            'diagnostico_5',
            'cond_5',
            'cod_6',
            'diagnostico_6',
            'cond_6',
            'cod_7',
            'diagnostico_7',
            'cond_7',
            'referido_a',
            'referido_de',
            'pg_emb',
            'jornada',
            'sm'
        ];

        if (!isset($validacionesDiagnosticos)) {
            $validacionesDiagnosticos = App\Models\Diagnostico::where(function ($query) {
                $query->whereNotNull('edad_minima')
                    ->orWhereNotNull('edad_maxima')
                    ->orWhere('sexo_permitido', '!=', 'ambos')
                    ->orWhere('requiere_embarazo', true)
                    ->orWhere('es_pediatrico', true)
                    ->orWhere('es_adulto', true);
            })->get([
                        'codigo',
                        'patologia',
                        'edad_minima',
                        'edad_maxima',
                        'tipo_edad',
                        'sexo_permitido',
                        'requiere_embarazo',
                        'es_pediatrico',
                        'es_adulto',
                        'notas_validacion'
                    ]);
        }
    @endphp

    <style>
        .detalles-page-wrapper {
            padding: 2px 8px;
            height: calc(100vh - var(--navbar-height, 56px) - var(--footer-height, 0px) - 6px);
            display: flex;
            flex-direction: column;
            overflow: hidden;
            background-color: var(--bg-body, #f8f9fa);
        }
        .detalles-header-row {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: space-between !important;
            flex-wrap: wrap !important;
            gap: 12px !important;
            margin-bottom: 6px !important;
            flex-shrink: 0 !important;
            width: 100% !important;
        }
        .detalles-header-left {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            gap: 12px !important;
            flex-wrap: wrap !important;
        }
        .detalles-header-right {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            gap: 10px !important;
            margin-left: auto !important;
            flex-wrap: nowrap !important;
            flex-shrink: 0 !important;
        }
        .detalles-title {
            margin: 0 !important;
            font-size: 1.1rem !important;
            font-weight: 800 !important;
            color: var(--text-primary) !important;
            line-height: 1.2 !important;
        }
        .detalles-actions {
            display: flex !important;
            flex-direction: row !important;
            align-items: center !important;
            gap: 6px !important;
            margin-top: 4px !important;
        }
        .btn-volver-icon {
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            width: 34px !important;
            height: 34px !important;
            border-radius: 8px !important;
            background: var(--bg-surface) !important;
            border: 1px solid var(--border-color) !important;
            color: var(--text-secondary) !important;
            text-decoration: none !important;
            transition: all 0.18s ease !important;
            flex-shrink: 0 !important;
        }
        .btn-volver-icon:hover {
            background: var(--bg-surface-hover) !important;
            color: var(--color-primary) !important;
            border-color: var(--color-primary) !important;
        }
        .btn-compact {
            font-size: 0.72rem !important;
            padding: 2px 8px !important;
            height: 25px !important;
            display: inline-flex !important;
            align-items: center !important;
            border-radius: var(--radius-xs, 4px) !important;
        }

        .compact-stat-card {
            background: var(--bg-surface, #ffffff) !important;
            border: 1px solid var(--border-color, #cbd5e1) !important;
            border-radius: 8px !important;
            padding: 4px 12px !important;
            height: 40px !important;
            box-sizing: border-box !important;
            display: inline-flex !important;
            flex-direction: row !important;
            align-items: center !important;
            justify-content: space-between !important;
            gap: 10px !important;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08) !important;
            border-left: 4px solid var(--color-primary, #4d7cfe) !important;
            transition: all 0.2s ease !important;
            flex: 0 0 auto !important;
            width: auto !important;
            white-space: nowrap !important;
        }

        .compact-stat-card:hover {
            transform: translateY(-1px);
            box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.1) !important;
        }

        .compact-stat-card.stat-card-registros {
            border-left-color: #10b981 !important;
        }
        .compact-stat-card.stat-card-medico {
            border-left-color: #8b5cf6 !important;
        }
        .compact-stat-card.stat-card-fecha {
            border-left-color: #3b82f6 !important;
        }

        .compact-stat-card .stat-icon {
            width: 28px !important;
            height: 28px !important;
            min-width: 28px !important;
            background: var(--bg-subtle, #f1f5f9) !important;
            border-radius: 6px !important;
            display: inline-flex !important;
            align-items: center !important;
            justify-content: center !important;
            font-size: 1rem !important;
            line-height: 1 !important;
            flex-shrink: 0 !important;
        }

        .stat-icon-registros {
            color: #10b981 !important;
        }
        .stat-icon-medico {
            color: #8b5cf6 !important;
        }
        .stat-icon-fecha {
            color: #3b82f6 !important;
        }

        .compact-stat-card .stat-details {
            display: flex !important;
            flex-direction: column !important;
            justify-content: center !important;
        }

        .compact-stat-card .stat-label {
            font-size: 0.60rem !important;
            color: var(--text-muted, #6c757d) !important;
            font-weight: 700 !important;
            text-transform: uppercase !important;
            letter-spacing: 0.5px !important;
            line-height: 1 !important;
            margin-bottom: 2px !important;
            white-space: nowrap !important;
        }

        .compact-stat-card .stat-value {
            font-size: 0.90rem !important;
            font-weight: 800 !important;
            color: var(--text-primary, #2d3436) !important;
            line-height: 1 !important;
            white-space: nowrap !important;
        }

        .compact-stat-card .stat-count {
            font-size: 1.3rem !important;
            font-weight: 900 !important;
            line-height: 1 !important;
            font-family: inherit !important;
        }

        /* Dark Mode */
        [data-theme="dark"] .compact-stat-card {
            background-color: #1e293b !important;
            border-color: #334155 !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.4) !important;
        }

        [data-theme="dark"] .compact-stat-card.stat-card-registros {
            border-left-color: #10b981 !important;
        }
        [data-theme="dark"] .compact-stat-card.stat-card-medico {
            border-left-color: #a78bfa !important;
        }
        [data-theme="dark"] .compact-stat-card.stat-card-fecha {
            border-left-color: #60a5fa !important;
        }

        [data-theme="dark"] .compact-stat-card .stat-icon {
            background-color: #0f172a !important;
            border: 1px solid #334155 !important;
        }

        [data-theme="dark"] .stat-icon-registros {
            color: #34d399 !important;
        }
        [data-theme="dark"] .stat-icon-medico {
            color: #a78bfa !important;
        }
        [data-theme="dark"] .stat-icon-fecha {
            color: #60a5fa !important;
        }

        [data-theme="dark"] .compact-stat-card .stat-count {
            color: #34d399 !important;
        }
        [data-theme="dark"] .compact-stat-card .stat-value {
            color: var(--text-primary, #f8fafc) !important;
        }
    </style>

    <div class="detalles-page-wrapper">
        <div ng-app="TablaDetalles" ng-cloak ng-controller="TablaDetallesCtrl" id="ingresosApp" class="h-100 d-flex flex-column">
            <!-- Header con Título, Botones de Acción y Stat Cards -->
            <div class="detalles-header-row">
                <!-- Izquierda: Volver + Título + Botones de Acción -->
                <div class="detalles-header-left">
                    <a href="{{ route('ingresos.index') }}"
                       class="btn-volver-icon"
                       title="Volver a Ingresos AT-1">
                        <i class="bi bi-arrow-left" style="font-size: 1.1rem;"></i>
                    </a>
                    <div>
                        <h1 class="detalles-title">
                            Detalles: {{ \Carbon\Carbon::parse($fecha)->format('d-m-Y') }}
                        </h1>
                        <div class="detalles-actions">
                            <button type="button" class="btn btn-compact font-weight-bold"
                                ng-class="{'btn-primary': !tablaGuardada, 'btn-success': tablaGuardada}"
                                ng-click="guardarCambios()">
                                <i class="bi mr-1" ng-class="{'bi-floppy': !tablaGuardada, 'bi-check-lg': tablaGuardada}"></i>
                                @{{ tablaGuardada ? 'Guardado' : 'Guardar' }}
                            </button>
                            <button type="button" class="btn btn-compact btn-subtle font-weight-bold"
                                ng-click="abrirBuscadorMedicos()" title="Buscar Médico (Alt+M)">
                                <i class="bi bi-person-badge text-primary mr-1"></i> Médicos <span class="badge badge-secondary ml-1" style="font-size: 0.58rem; padding: 1px 3px;">Alt+M</span>
                            </button>
                            <button type="button" class="btn btn-compact btn-subtle font-weight-bold"
                                ng-click="abrirBuscadorDiagnosticos()" title="Buscar Diagnóstico (Alt+D)">
                                <i class="bi bi-heart-pulse text-info mr-1"></i> Diagnósticos <span class="badge badge-secondary ml-1" style="font-size: 0.58rem; padding: 1px 3px;">Alt+D</span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Derecha: Stat Cards Horizontales -->
                <div class="detalles-header-right">
                    <!-- Card Total Registros -->
                    <div class="compact-stat-card stat-card-registros">
                        <div style="display: flex; align-items: center;">
                            <div class="stat-icon stat-icon-registros">
                                <i class="bi bi-card-checklist"></i>
                            </div>
                            <div class="stat-details ml-2">
                                <span class="stat-label">Total Registros</span>
                                <span class="stat-value">
                                    @{{lista.length}}
                                </span>
                            </div>
                        </div>
                        <div class="stat-count text-success ml-2">
                            @{{lista.length}}
                        </div>
                    </div>

                    <!-- Card Total Médicos -->
                    <div class="compact-stat-card stat-card-medico">
                        <div style="display: flex; align-items: center;">
                            <div class="stat-icon stat-icon-medico">
                                <i class="bi bi-people-fill"></i>
                            </div>
                            <div class="stat-details ml-2">
                                <span class="stat-label">Total Médicos</span>
                                <span class="stat-value">
                                    {{ $estadisticas['total_medicos'] ?? 0 }}
                                </span>
                            </div>
                        </div>
                    </div>

                    <!-- Card Fecha de Carga -->
                    <div class="compact-stat-card stat-card-fecha" style="min-width: 165px;">
                        <div style="display: flex; align-items: center;">
                            <div class="stat-icon stat-icon-fecha">
                                <i class="bi bi-calendar3"></i>
                            </div>
                            <div class="stat-details ml-2">
                                <span class="stat-label">Fecha de Carga</span>
                                <span class="stat-value">
                                    {{ \Carbon\Carbon::parse($fecha)->format('d/m/Y') }}
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card Contenedor de la Tabla -->
            <div class="detalles-card card shadow-sm flex-grow-1 d-flex flex-column mb-0" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md, 6px); overflow: hidden;">
                <div class="card-body p-0 flex-grow-1 position-relative" style="min-height: 0; overflow: hidden;">
                    <div class="excel-table-scroll h-100 w-100" style="overflow: auto;">
                        <table class="sing-table-excel table-detalles mb-0">
                            <thead>
                                <tr>
                                    <th class="col-row-num text-center sticky-col">#</th>
                                    @foreach($columns as $col)
                                         @php
                                             $width = '85px';
                                             switch ($col) {
                                                 case 'numero': $width = '48px'; break;
                                                 case 'ano': $width = '52px'; break;
                                                 case 'mes': $width = '75px'; break;
                                                 case 'cm': $width = '52px'; break;
                                                 case 'medico': $width = '210px'; break;
                                                 case 'prof': $width = '135px'; break;
                                                 case 'fecha': $width = '95px'; break;
                                                 case 'se': $width = '42px'; break;
                                                 case 'exp': $width = '75px'; break;
                                                 case 'sexo': $width = '42px'; break;
                                                 case 'edad': $width = '45px'; break;
                                                 case 'tipo': $width = '42px'; break;
                                                 case 'rango': $width = '120px'; break;
                                                 case 'rango_2':
                                                 case 'rango_3':
                                                 case 'rango_4':
                                                 case 'rango_5': $width = '110px'; break;
                                                 case 'cond': $width = '42px'; break;
                                                 case 'cod_col': $width = '52px'; break;
                                                 case 'colonia': $width = '150px'; break;
                                                 case 'sg': $width = '42px'; break;
                                                 case 'referido_a':
                                                 case 'referido_de': $width = '90px'; break;
                                                 case 'pg_emb': $width = '70px'; break;
                                                 case 'jornada': $width = '75px'; break;
                                                 case 'sm': $width = '42px'; break;
                                                 default:
                                                     if (strpos($col, 'cod_') === 0 && $col !== 'cod_col') $width = '50px';
                                                     else if (strpos($col, 'diagnostico_') === 0) $width = '190px';
                                                     else if (strpos($col, 'cond_') === 0) $width = '42px';
                                             }
                                         @endphp
                                         <th class="text-uppercase text-truncate"
                                             style="width: {{ $width }}; min-width: {{ $width }}; max-width: {{ $width }};"
                                             title="{{ str_replace('_', ' ', $col) }}">
                                             {{ str_replace('_', ' ', $col) }}
                                         </th>
                                    @endforeach
                                    <th class="text-center" style="width: 32px; min-width: 32px;">✕</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr ng-repeat="item in lista">
                                    <td class="col-row-num text-center font-weight-bold sticky-col"
                                        ng-class="{'active-row': $index === filaSeleccionada}"
                                        ng-bind="item.numero || ($index + 1)"></td>
                                    @foreach($columns as $col)
                                        <td editable-td row="@{{$index}}" field="{{ $col }}"></td>
                                    @endforeach
                                    <td class="text-center align-middle p-0">
                                        <button type="button" class="btn btn-sm btn-outline-danger p-0 d-inline-flex align-items-center justify-content-center" style="width: 18px; height: 18px;" ng-click="eliminarRegistro($index, item.id)" title="Eliminar fila">
                                            <i class="fas fa-trash-alt" style="font-size: 0.60rem;"></i>
                                        </button>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
                <div class="card-footer py-1 px-3 d-flex align-items-center justify-content-between" style="height: 28px; background-color: var(--bg-surface); border-top: 1px solid var(--border-color); flex-shrink: 0;">
                    <div class="d-flex align-items-center" style="gap: 8px;">
                        <span class="text-muted" style="font-size: 0.72rem; font-weight: 500;">
                            <i class="fas fa-keyboard mr-1"></i> Tab/Enter para navegar | Alt+M (Médicos) | Alt+D (Diagnósticos)
                        </span>
                    </div>
                    <div class="d-flex align-items-center" style="gap: 8px;">
                        <span class="text-muted" style="font-size: 0.72rem;">Filas: <strong>@{{lista.length}}</strong></span>
                    </div>
                </div>
            </div>
            @include('partials.modales-buscadores')
        </div>
    </div>

    <link rel="stylesheet" href="{{ asset('assets/css/sing-excel-table.css') }}?v={{ @filemtime(public_path('assets/css/sing-excel-table.css')) }}">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <style>
        .flatpickr-calendar {
            z-index: 9999 !important;
        }

        [ng\:cloak],
        [ng-cloak],
        .ng-cloak {
            display: none !important;
        }

        /* Pantalla Estática - Sin Scroll en la Ventana */
        .detalles-page-wrapper {
            height: 100% !important;
            max-height: 100% !important;
            flex: 1 1 0% !important;
            min-height: 0 !important;
            display: flex !important;
            flex-direction: column !important;
            overflow: hidden !important;
            padding: 2px 6px;
            background-color: var(--bg-body, #f8f9fa);
        }

        #ingresosApp {
            display: flex !important;
            flex-direction: column !important;
            flex: 1 1 0% !important;
            min-height: 0 !important;
            overflow: hidden !important;
        }

        .detalles-card {
            border: 1px solid var(--border-color, #dee2e6);
            border-radius: var(--radius-md, 6px);
            background-color: var(--bg-surface, #ffffff);
            overflow: hidden !important;
            display: flex !important;
            flex-direction: column !important;
            flex: 1 1 0% !important;
            min-height: 0 !important;
        }

        .detalles-card .card-body {
            display: flex !important;
            flex-direction: column !important;
            flex: 1 1 0% !important;
            min-height: 0 !important;
            overflow: hidden !important;
            position: relative !important;
        }

        /* Celdas editables */
        .table-detalles td[contenteditable="true"] {
            outline: none;
            cursor: cell;
            transition: background 0.1s;
        }

        .table-detalles td[contenteditable="true"]:focus {
            background-color: var(--bg-surface) !important;
            color: var(--text-primary) !important;
            box-shadow: inset 0 0 0 2px var(--color-primary, #4d7cfe) !important;
            z-index: 25 !important;
            position: relative;
            overflow: visible !important;
            white-space: nowrap !important;
        }

        .app-footer {
            display: none !important;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <script>
        'use strict';
        flatpickr.localize(flatpickr.l10ns.es);

        // Helpers Globales para SweetAlert
        window.enfocarError = function (rowIndex, fieldName) {
            Swal.close();
            var cell = $('td[row="' + rowIndex + '"][field="' + fieldName + '"]');
            if (cell.length) {
                $('html, body').animate({ scrollTop: cell.offset().top - 200 }, 500);
                cell.addClass('bg-danger text-white');
                setTimeout(function () { cell.removeClass('bg-danger text-white'); cell.focus(); }, 2000);
            }
        };
        window.borrarDatoErroneo = function (rowIndex, fieldName) {
            var scope = angular.element(document.getElementById('ingresosApp')).scope();
            if (scope) {
                scope.$apply(function () {
                    scope.lista[rowIndex][fieldName] = '';
                    if (fieldName && fieldName.match(/^cod_\d+$/)) {
                        var k = fieldName.split('_')[1];
                        scope.lista[rowIndex]['diagnostico_' + k] = '';
                        scope.lista[rowIndex]['cond_' + k] = '';
                    }
                });
                Swal.close();
                Swal.fire({ icon: 'success', title: 'Dato borrado', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 });
            }
        };

        var app = angular.module('TablaDetalles', []);

        app.controller('TablaDetallesCtrl', ['$scope', function ($scope) {
            $scope.medicosList = @json($medicos ?? []);
            $scope.coloniasList = @json($colonias ?? []);
            $scope.diagnosticosList = @json($diagnosticos ?? []);
            $scope.referenciasList = @json($referencias ?? []);
            $scope.validacionesDiagnosticos = @json($validacionesDiagnosticos ?? []);

            var rawRegistros = @json($registros);
            $scope.lista = rawRegistros.map(function (item) {
                if (item.fecha) {
                    var f = item.fecha;
                    if (typeof f === 'string' && f.indexOf('T') > -1) f = f.split('T')[0];
                    if (typeof f === 'string' && f.indexOf('-') > -1) { var p = f.split('-'); if (p.length === 3) f = p[2] + '/' + p[1] + '/' + p[0]; }
                    item.fecha = f;
                }
                for (var k in item) { if (item[k] === null) item[k] = ''; }
                return item;
            });

            $scope.filaSeleccionada = -1;
            $scope.tablaGuardada = true;
            $scope.setFilaSeleccionada = function (index) { $scope.filaSeleccionada = parseInt(index); };

            $scope.marcarModificado = function () {
                if ($scope.tablaGuardada) {
                    $scope.tablaGuardada = false;
                    if (!$scope.$$phase) $scope.$digest();
                }
            };

            $scope.eliminarRegistro = function(index, id) {
                Swal.fire({
                    title: '¿Eliminar registro?',
                    text: "Esta acción eliminará el registro permanentemente.",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d33',
                    cancelButtonColor: '#3085d6',
                    confirmButtonText: '<i class="fas fa-trash-alt mr-1"></i> Sí, eliminar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        if(id) {
                            Swal.fire({ title: 'Eliminando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                            fetch('{{ url("ingresos") }}/' + id, {
                                method: 'DELETE',
                                headers: {
                                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                    'Accept': 'application/json'
                                }
                            })
                            .then(r => r.json())
                            .then(data => {
                                if (data.success || data.mensaje) {
                                    $scope.$apply(() => {
                                        $scope.lista.splice(index, 1);
                                        $scope.lista.forEach(function(item, i) { item.numero = i + 1; });
                                    });
                                    Swal.fire('¡Eliminado!', 'El registro ha sido eliminado.', 'success');
                                } else {
                                    Swal.fire('Error', data.error || data.message || 'No se pudo eliminar el registro.', 'error');
                                }
                            })
                            .catch(e => {
                                Swal.fire('Error', 'Error de conexión al eliminar', 'error');
                                console.error(e);
                            });
                        } else {
                            $scope.$apply(() => { 
                                $scope.lista.splice(index, 1); 
                                $scope.lista.forEach(function(item, i) { item.numero = i + 1; });
                            });
                        }
                    }
                });
            };

            var rango1Categorias = ['1. MENOR DE 1 MES', '2. DE 1 MES A 1 AÑO', '3. DE 1 A 4 AÑOS', '4. DE 5 A 9 AÑOS', '5. DE 10 A 14 AÑOS', '6. DE 15 A 19 AÑOS', '7. DE 20 A 49 AÑOS', '8. DE 50 A 59 AÑOS', '9. MAYORES DE 60 AÑOS'];
            var rango2Categorias = ['MENOR DE 1 MES', 'DE 1 A 2 MESES', 'DE 2 MES A 1 AÑO', 'DE 1 A 4 AÑOS', 'DE 5 A 9 AÑOS', 'DE 10 A 14 AÑOS', 'DE 15 A 19 AÑOS', 'DE 20 A 49 AÑOS', 'DE 50 A 59 AÑOS', 'MAYORES DE 60 AÑOS'];
            var rango3Categorias = ['MENOR 1 AÑO', '1 - 4 AÑOS', '5 A 9 AÑOS', '10 A 14 AÑOS', '15 A 19 AÑOS', '20 A 24 AÑOS', '25 A 39 AÑOS', '40 A 59 AÑOS', '60 Y MAS'];
            var rango4Categorias = ['MENOR 1 AÑO', '1 - 4 AÑOS', '5 A 9 AÑOS', '10 A 14 AÑOS', '15 A 19 AÑOS', '20 A 24 AÑOS', '25 A 29 AÑOS', '30 A 49 AÑOS', '50 y +'];
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

                var edadEnAnios = edad;
                if (tipo === 'M') edadEnAnios = edad / 12;
                else if (tipo === 'D') edadEnAnios = edad / 365;

                obj.rango = rango1Categorias[getCategoriaRango1(edadEnAnios)];
                obj.rango_2 = rango2Categorias[getCategoriaRango2(edadEnAnios)];
                obj.rango_3 = rango3Categorias[getCategoriaRango3(edadEnAnios)];
                obj.rango_4 = rango4Categorias[getCategoriaRango4(edadEnAnios)];
                obj.rango_5 = rango5Categorias[getCategoriaRango5(edadEnAnios)];
            };

            $scope.calcularConversion = function (obj) {
                if (!obj.edad || !obj.tipo) return;
                var edad = parseInt(obj.edad, 10);
                var tipo = (obj.tipo || '').toUpperCase();
                if (isNaN(edad)) return;
                var changed = true;
                while (changed) {
                    changed = false;
                    if (tipo === 'D' && edad > 30) { edad = Math.floor(edad / 30); tipo = 'M'; changed = true; }
                    else if (tipo === 'M' && edad > 11) { edad = Math.floor(edad / 12); tipo = 'A'; changed = true; }
                }
                obj.edad = edad.toString();
                obj.tipo = tipo;
            };

            $scope.calcularPgEmbYSm = function (obj) {
                var tieneEmbarazo = false;
                var categoriaSM = '';
                var palabrasEmbarazo = ['PRENATAL', 'GESTACION', 'GESTACIONAL'];

                for (var i = 1; i <= 7; i++) {
                    var codigo = obj['cod_' + i];
                    var textodiag = (obj['diagnostico_' + i] || '').toUpperCase();
                    if (codigo) {
                        var diagEncontrado = ($scope.diagnosticosList || []).find(d => d.codigo == codigo);
                        if (diagEncontrado) {
                            if (diagEncontrado.requiere_embarazo) tieneEmbarazo = true;
                            if ((diagEncontrado.categoria || '').toString().trim().toUpperCase().indexOf('SM') !== -1) categoriaSM = 'SM';
                            if (!tieneEmbarazo) {
                                var pat = (diagEncontrado.patologia || '').toUpperCase();
                                if (palabrasEmbarazo.some(p => pat.indexOf(p) !== -1)) tieneEmbarazo = true;
                            }
                        }
                    }
                    if (!tieneEmbarazo && textodiag && palabrasEmbarazo.some(p => textodiag.indexOf(p) !== -1)) tieneEmbarazo = true;
                }
                obj.pg_emb = tieneEmbarazo ? 'EMBARAZADA' : 'POBLACION GENERAL';
                obj.sm = categoriaSM;
            };

            $scope.searchMedicoText = ''; $scope.searchDiagnosticoText = ''; $scope.searchColoniaText = ''; $scope.currentFieldForModal = null;
            $scope.abrirBuscadorMedicos = function (field) { $scope.currentFieldForModal = field; $scope.searchMedicoText = ''; $('#modalBuscadorMedicos').modal('show'); setTimeout(() => $('#searchMedico').focus(), 500); };
            $scope.abrirBuscadorDiagnosticos = function (field) { $scope.currentFieldForModal = field; $scope.searchDiagnosticoText = ''; $scope.numeroDiagnostico = (field && field.numero) ? field.numero : null; $('#modalBuscadorDiagnosticos').modal('show'); setTimeout(() => $('#searchDiagnostico').focus(), 500); };
            $scope.abrirBuscadorColonias = function (field) { $scope.currentFieldForModal = field; $scope.searchColoniaText = ''; $('#modalBuscadorColonias').modal('show'); setTimeout(() => $('#searchColonia').focus(), 500); };

            $scope.seleccionarMedico = function (medico) {
                if ($scope.currentFieldForModal && $scope.lista[$scope.currentFieldForModal.row]) {
                    var row = $scope.currentFieldForModal.row;
                    $scope.lista[row].cm = medico.COD_MED;
                    $scope.lista[row].medico = medico.NOM_MED;
                    $scope.lista[row].prof = medico.ESPECIALIDAD;
                    $scope.lista[row].jornada = medico.JORNADA || '';
                    $scope.marcarModificado();
                }
                $('#modalBuscadorMedicos').modal('hide');
            };

            $scope.seleccionarDiagnostico = function (diagnostico) {
                if ($scope.currentFieldForModal && $scope.lista[$scope.currentFieldForModal.row]) {
                    var row = $scope.currentFieldForModal.row;
                    var field = $scope.currentFieldForModal.field;
                    var num = $scope.currentFieldForModal.numero;

                    if (!num && typeof field === 'string') {
                        if (field.match(/^cod_\d$/)) num = field.split('_')[1];
                        else if (field.match(/^diagnostico_\d$/)) num = field.split('_')[1];
                    }

                    if (num) {
                        $scope.lista[row]['cod_' + num] = diagnostico.codigo;
                        $scope.lista[row]['diagnostico_' + num] = diagnostico.patologia;
                        $scope.calcularPgEmbYSm($scope.lista[row]);
                        $scope.marcarModificado();
                    }
                }
                $('#modalBuscadorDiagnosticos').modal('hide');
            };

            $scope.seleccionarColonia = function (colonia) {
                if ($scope.currentFieldForModal && $scope.lista[$scope.currentFieldForModal.row]) {
                    var row = $scope.currentFieldForModal.row;
                    $scope.lista[row].cod_col = colonia.COD_COL;
                    $scope.lista[row].colonia = colonia.COLONIA;
                    $scope.marcarModificado();
                }
                $('#modalBuscadorColonias').modal('hide');
            };

            $scope.guardarCambios = function () {
                // Validación forzada: evaluamos siempre los errores de la tabla aunque no se hayan hecho cambios explícitas.

                var filasConErrores = [];
                var validaciones = $scope.validacionesDiagnosticos || [];
                $scope.lista.forEach(function (fila, index) {
                    var numeroFila = fila.numero || (index + 1);
                    var erroresFila = [];

                    if (!fila.fecha) erroresFila.push({ msg: "Falta la FECHA", field: 'fecha' });
                    if (!fila.cm && !fila.medico) erroresFila.push({ msg: "Falta el MÉDICO", field: 'cm' });

                    var edadDias = 0;
                    if (fila.edad && fila.tipo) {
                        var valor = parseInt(fila.edad);
                        if (fila.tipo === 'D') edadDias = valor;
                        else if (fila.tipo === 'M') edadDias = valor * 30;
                        else if (fila.tipo === 'A') edadDias = valor * 365;
                    }

                    for (var k = 1; k <= 7; k++) {
                        var codigo = fila['cod_' + k];
                        var condicion = fila['cond_' + k];

                        if (codigo && !condicion) erroresFila.push({ msg: "El Dx <b>" + codigo + "</b> no tiene Condición", field: 'cond_' + k });
                        if (!codigo && condicion) erroresFila.push({ msg: "Hay Condición pero falta el Código del Dx", field: 'cod_' + k });

                        if (codigo && validaciones.length > 0) {
                            var regla = validaciones.find(v => v.codigo == codigo);

                            if (regla) {
                                var nombreDiag = regla.patologia || ("Dx " + codigo);

                                // --- VALIDACIÓN DE SEXO ---
                                if (fila.sexo && regla.sexo_permitido && regla.sexo_permitido !== 'ambos') {
                                    var pacienteEsHombre = (fila.sexo === 'H');
                                    var pacienteEsMujer = (fila.sexo === 'M');
                                    var reglaSoloHombres = (regla.sexo_permitido === 'H');
                                    var reglaSoloMujeres = (regla.sexo_permitido === 'M' || regla.sexo_permitido === 'F');

                                    if (reglaSoloHombres && !pacienteEsHombre) {
                                        erroresFila.push({ msg: "<b>" + nombreDiag + "</b> es exclusivo para PACIENTES HOMBRES.", field: 'cod_' + k });
                                    }
                                    if (reglaSoloMujeres && !pacienteEsMujer) {
                                        erroresFila.push({ msg: "<b>" + nombreDiag + "</b> es exclusivo para PACIENTES MUJERES.", field: 'cod_' + k });
                                    }
                                }

                                // --- VALIDACIÓN DE EDAD Y TIPO REQUERIDOS ---
                                var tieneRestriccionEdad = (regla.edad_minima !== null && regla.edad_minima > 0) ||
                                    (regla.edad_maxima !== null && regla.edad_maxima < 150) ||
                                    regla.es_pediatrico ||
                                    regla.es_adulto;

                                if (tieneRestriccionEdad && (!fila.edad || !fila.tipo)) {
                                    erroresFila.push({ msg: "<b>" + nombreDiag + "</b> condiciona la edad, debe ingresar la EDAD y TIPO.", field: 'cod_' + k });
                                }

                                // --- VALIDACIÓN DE EDAD Y RANGOS ---
                                if (fila.edad && fila.tipo) {
                                    var factorRegla = (regla.tipo_edad === 'M') ? 30 : ((regla.tipo_edad === 'D') ? 1 : 365);

                                    if (regla.edad_minima !== null && regla.edad_minima > 0) {
                                        var minDias = regla.edad_minima * factorRegla;
                                        if (edadDias < minDias) {
                                            var uLabel = (regla.tipo_edad === 'A' ? 'AÑOS' : (regla.tipo_edad === 'M' ? 'MESES' : 'DÍAS'));
                                            erroresFila.push({ msg: "<b>" + nombreDiag + "</b> requiere edad mínima de " + regla.edad_minima + " " + uLabel + ". (Paciente tiene " + fila.edad + fila.tipo + ")", field: 'cod_' + k });
                                        }
                                    }

                                    if (regla.edad_maxima !== null && regla.edad_maxima < 150) {
                                        var maxDias = regla.edad_maxima * factorRegla;
                                        if (edadDias > maxDias) {
                                            var uLabel = (regla.tipo_edad === 'A' ? 'AÑOS' : (regla.tipo_edad === 'M' ? 'MESES' : 'DÍAS'));
                                            erroresFila.push({ msg: "<b>" + nombreDiag + "</b> permite edad máxima de " + regla.edad_maxima + " " + uLabel + ". (Paciente tiene " + fila.edad + fila.tipo + ")", field: 'cod_' + k });
                                        }
                                    }

                                    if (regla.es_pediatrico && edadDias >= 5475) erroresFila.push({ msg: "<b>" + nombreDiag + "</b> es PEDIÁTRICO", field: 'cod_' + k });
                                    if (regla.es_adulto && edadDias < 5475) erroresFila.push({ msg: "<b>" + nombreDiag + "</b> es de ADULTO", field: 'cod_' + k });
                                }

                                // --- VALIDACIÓN DE EMBARAZO ---
                                if (regla.requiere_embarazo && fila.pg_emb !== 'EMBARAZADA') {
                                    erroresFila.push({ msg: "<b>" + nombreDiag + "</b> requiere la condición de EMBARAZADA (PG_EMB).", field: 'cod_' + k });
                                }
                            }
                        }
                    }
                    if (fila.cod_col && !fila.colonia) erroresFila.push({ msg: "Tiene Cód. Colonia pero falta el NOMBRE", field: 'colonia' });
                    if (!fila.cod_col && fila.colonia) erroresFila.push({ msg: "Tiene Nombre de Colonia pero falta el CÓDIGO", field: 'cod_col' });

                    if (erroresFila.length > 0) filasConErrores.push({ fila: numeroFila, index: index, errores: erroresFila });
                });

                if (filasConErrores.length > 0) {
                    var html = '<div class="text-left custom-scrollbar" style="max-height: 45vh; overflow-y: auto; overflow-x: hidden; padding-right: 5px;">' +
                        '<table class="table table-sm table-borderless m-0" style="table-layout: fixed; width: 100%; border-collapse: separate; border-spacing: 0 8px;">' +
                        '<tbody>';

                    filasConErrores.forEach(function (item) {
                        var erroresHtml = item.errores.map(function (err) {
                            return '<div class="d-flex align-items-center mb-1 py-1 px-2 rounded" style="background: #fff5f5; border-left: 3px solid #f87171;">' +
                                '<div style="flex: 1; min-width: 0; font-size: 11px; color: #b91c1c; text-align: left; line-height: 1.3; padding-right: 15px; word-break: break-word;"><span class="text-danger mr-1">●</span>' + err.msg + '</div>' +
                                '<div class="ml-2 d-flex" style="flex-shrink: 0;">' +
                                '<button type="button" class="btn btn-sm btn-link p-0 text-primary mr-2" onclick="window.enfocarError(' + item.index + ', \'' + err.field + '\')" title="Ver en tabla"><i class="fas fa-search-plus" style="font-size: 14px;"></i></button>' +
                                '<button type="button" class="btn btn-sm btn-link p-0 text-danger" onclick="window.borrarDatoErroneo(' + item.index + ', \'' + err.field + '\')" title="Borrar"><i class="fas fa-times-circle" style="font-size: 14px;"></i></button>' +
                                '</div>' +
                                '</div>';
                        }).join('');

                        html += '<tr style="background: #f8fafc; border-radius: 8px; box-shadow: 0 1px 2px rgba(0,0,0,0.05); border: 1px solid #e2e8f0;">' +
                            '<td style="width: 50px; text-align: center; vertical-align: top; padding-top: 10px; font-weight: 800; color: #64748b; font-size: 13px; border-radius: 8px 0 0 8px;">Fila<br><span style="font-size: 16px; color: #0f172a;">' + item.fila + '</span></td>' +
                            '<td style="padding: 6px 10px 6px 0; border-radius: 0 8px 8px 0;">' + erroresHtml + '</td>' +
                            '</tr>';
                    });

                    html += '</tbody></table></div>' +
                        '<div class="mt-2 p-2 rounded bg-light border">' +
                        '<p class="m-0 text-secondary font-weight-bold text-sm"><i class="fas fa-exclamation-triangle text-warning mt-1 mr-2" style="float: left;"></i>Se detectaron datos que no cumplen las reglas.<br>¿Desea forzar el guardado o prefiere corregirlos?</p>' +
                        '</div>';

                    Swal.fire({
                        title: '<div style="display: flex; align-items: center; justify-content: center; gap: 10px; margin-bottom: -20px;"><i class="fas fa-exclamation-triangle" style="color: #f59e0b; font-size: 24px;"></i><span style="font-size: 18px; color: #1e293b; font-weight: bold; text-transform: uppercase;">Errores de Validación</span></div>',
                        html: html,
                        width: '850px',
                        showCancelButton: true,
                        confirmButtonText: '<i class="fas fa-save mr-2"></i> Forzar Guardado',
                        cancelButtonText: 'Volver para Corregir',
                        confirmButtonColor: '#f43f5e',
                        cancelButtonColor: '#64748b',
                        reverseButtons: true,
                        customClass: {
                            popup: 'rounded shadow',
                            confirmButton: 'font-weight-bold px-4 py-2 text-xs',
                            cancelButton: 'font-weight-bold px-4 py-2 text-xs'
                        }
                    }).then((result) => {
                        if (result.isConfirmed) {
                            enviarAlServidor();
                        }
                    });
                } else {
                    enviarAlServidor();
                }

                function enviarAlServidor() {
                    Swal.fire({ title: 'Guardando...', allowOutsideClick: false, didOpen: () => Swal.showLoading() });
                    var datosParaEnviar = $scope.lista.map(function (item) {
                        var fila = angular.copy(item);
                        
                        // Recalcular rangos para estandarizar (quitar prefijos numéricos si existen y corregir discrepancias)
                        $scope.calcularRangos(fila);

                        if (fila.fecha && typeof fila.fecha === 'string' && fila.fecha.indexOf('/') > -1) {
                            var parts = fila.fecha.split('/');
                            if (parts.length === 3) fila.fecha = parts[2] + '-' + parts[1] + '-' + parts[0];
                        }
                        return fila;
                    });

                    fetch('{{ route("ingresos.update-batch") }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                            'Content-Type': 'application/json',
                            'Accept': 'application/json'
                        },
                        body: JSON.stringify({ rows: datosParaEnviar })
                    })
                        .then(r => r.json())
                        .then(data => {
                            if (data.success) {
                                $scope.$apply(() => { $scope.tablaGuardada = true; });
                                Swal.fire('¡Actualizado!', data.message, 'success');
                            } else {
                                Swal.fire('Atención', data.message + (data.errors ? ':\n' + data.errors.slice(0, 3).join('\n') : ''), 'warning');
                            }
                        })
                        .catch(e => { Swal.fire('Error', 'Error de conexión', 'error'); console.error(e); });
                }
            };

            document.addEventListener('keydown', function (e) {
                if (e.altKey && (e.key === 'm' || e.key === 'M')) { e.preventDefault(); $scope.$apply(() => $scope.abrirBuscadorMedicos($scope.currentFieldForModal)); }
                else if (e.altKey && (e.key === 'd' || e.key === 'D')) { e.preventDefault(); $scope.$apply(() => $scope.abrirBuscadorDiagnosticos($scope.currentFieldForModal)); }
                else if (e.altKey && (e.key === 'c' || e.key === 'C')) { e.preventDefault(); $scope.$apply(() => $scope.abrirBuscadorColonias($scope.currentFieldForModal)); }
            });
        }]);

        app.directive('editableTd', [function () {
            return {
                restrict: 'A',
                link: function (scope, element, attrs) {
                    element.attr('contenteditable', 'true');
                    var fp = null;
                    if (attrs.field === 'fecha') {
                        fp = flatpickr(element[0], {
                            dateFormat: "d/m/Y", allowInput: true, disableMobile: "true", clickOpens: false, defaultDate: element.text().trim(),
                            onClose: function (selectedDates, dateStr) {
                                scope.$apply(() => {
                                    if (dateStr || element.text().trim() === '') {
                                        scope.lista[attrs.row][attrs.field] = dateStr;
                                        actualizarFecha(dateStr);
                                        scope.marcarModificado();
                                    }
                                });
                            }
                        });
                    }

                    function getEpidemiologicalWeek(d) {
                        d = new Date(d); d.setHours(12, 0, 0, 0);
                        var y = d.getFullYear();
                        var jan1 = new Date(y, 0, 1, 12);
                        var firstSun = jan1.getDay() === 0 ? jan1 : new Date(y, 0, 1 + (7 - jan1.getDay()), 12);
                        if (d < firstSun) return 53;
                        return Math.floor(((d - firstSun) / 86400000) / 7) + 1;
                    }

                    function actualizarFecha(dateStr) {
                        if (dateStr && dateStr.length >= 6) {
                            // Intentar normalizar
                            var parts = dateStr.split(/[\/\-\.]/);
                            if (parts.length === 3) {
                                var d = parseInt(parts[0], 10);
                                var m = parseInt(parts[1], 10);
                                var y = parseInt(parts[2], 10);
                                if (y < 100) y += 2000;

                                if (m >= 1 && m <= 12 && d >= 1 && d <= 31) {
                                    var dObj = new Date(y, m - 1, d, 12);
                                    if (!isNaN(dObj.getTime())) {
                                        var finalStr = ('0' + d).slice(-2) + '/' + ('0' + m).slice(-2) + '/' + y;
                                        if (scope.lista[attrs.row][attrs.field] !== finalStr) {
                                            scope.lista[attrs.row][attrs.field] = finalStr;
                                            element.text(finalStr);
                                        }
                                        var monthNames = ["ENERO", "FEBRERO", "MARZO", "ABRIL", "MAYO", "JUNIO", "JULIO", "AGOSTO", "SEPTIEMBRE", "OCTUBRE", "NOVIEMBRE", "DICIEMBRE"];
                                        scope.lista[attrs.row]['se'] = getEpidemiologicalWeek(dObj);
                                        scope.lista[attrs.row]['mes'] = monthNames[dObj.getMonth()];
                                        scope.lista[attrs.row]['ano'] = y;
                                    }
                                }
                            }
                        }
                    }

                    element.bind('blur', function () {
                        var text = element.text().trim().toUpperCase();

                        // Parseo manual de fecha
                        if (attrs.field === 'fecha' && text.length > 0) {
                            actualizarFecha(text);
                            text = scope.lista[attrs.row][attrs.field]; // Actualizar text con el formateado
                        }

                        if (attrs.field === 'sexo' && text !== 'M' && text !== 'H') text = '';
                        else if (attrs.field === 'se') text = text.replace(/[^0-9]/g, '');
                        else if (attrs.field === 'tipo' && text !== 'A' && text !== 'M' && text !== 'D') text = '';
                        else if (attrs.field === 'edad') { var c = text.replace(/[^0-9]/g, ''); text = c ? parseInt(c).toString() : ''; }

                        element.text(text); element.removeClass('edit-mode nav-mode');

                        scope.$apply(function () {
                            if (scope.lista[attrs.row][attrs.field] !== text || attrs.field === 'edad' || attrs.field === 'tipo') {
                                scope.lista[attrs.row][attrs.field] = text;
                                scope.marcarModificado();
                                scope.calcularConversion(scope.lista[attrs.row]);
                                scope.calcularRangos(scope.lista[attrs.row]);

                                var finalEdad = parseInt(scope.lista[attrs.row].edad, 10);
                                if (!isNaN(finalEdad) && finalEdad > 150) {
                                    Swal.fire({ title: 'Edad fuera de rango', text: "¿Es correcta?", icon: 'warning', showCancelButton: true }).then((r) => {
                                        if (!r.isConfirmed) scope.$apply(() => { scope.lista[attrs.row].edad = ''; element.text(''); setTimeout(() => element.focus(), 100); });
                                    });
                                }
                                if (scope.lista[attrs.row][attrs.field] !== text) element.text(scope.lista[attrs.row][attrs.field]);

                                if (attrs.field === 'cm' && text) {
                                    var med = scope.medicosList.find(m => m.COD_MED == text);
                                    if (med) { scope.lista[attrs.row].medico = med.NOM_MED; scope.lista[attrs.row].prof = med.ESPECIALIDAD; scope.lista[attrs.row].jornada = med.JORNADA || ''; }
                                    else { Swal.fire({ title: 'Médico no encontrado', text: "El código no existe", icon: 'warning' }); }
                                }
                                if (attrs.field === 'cod_col' && text) {
                                    var col = scope.coloniasList.find(c => c.COD_COL == text);
                                    if (col) scope.lista[attrs.row].colonia = col.COLONIA;
                                    else { Swal.fire({ title: 'Colonia no encontrada', text: "El código no existe", icon: 'warning' }); }
                                }
                                if (attrs.field.match(/^cod_[1-7]$/) && text) {
                                    var num = attrs.field.split('_')[1];
                                    var diag = scope.diagnosticosList.find(d => d.codigo == text);
                                    if (diag) scope.lista[attrs.row]['diagnostico_' + num] = diag.patologia;
                                    else { Swal.fire({ title: 'Diagnóstico no encontrado', text: "El código no existe", icon: 'warning' }); }
                                    scope.calcularPgEmbYSm(scope.lista[attrs.row]);
                                }
                            }
                        });
                    });

                    element.bind('dblclick', function () {
                        if (attrs.field === 'cm' || attrs.field === 'medico') scope.$apply(() => scope.abrirBuscadorMedicos({ row: attrs.row, field: attrs.field }));
                        if (attrs.field === 'cod_col' || attrs.field === 'colonia') scope.$apply(() => scope.abrirBuscadorColonias({ row: attrs.row, field: attrs.field }));
                        if (attrs.field.match(/^cod_\d$/) || attrs.field.match(/^diagnostico_\d$/)) {
                            var num = attrs.field.split('_')[1];
                            scope.$apply(() => scope.abrirBuscadorDiagnosticos({ row: attrs.row, field: attrs.field, numero: num }));
                        }
                        element.addClass('edit-mode').removeClass('nav-mode');
                        if (fp) fp.open();
                        var r = document.createRange(); r.selectNodeContents(element[0]); r.collapse(false);
                        var s = window.getSelection(); s.removeAllRanges(); s.addRange(r);
                    });

                    element.bind('focus', function () {
                        element.addClass('nav-mode').removeClass('edit-mode');
                        if (!scope.$$phase) scope.$apply(() => scope.setFilaSeleccionada(attrs.row));
                        setTimeout(() => { var r = document.createRange(); r.selectNodeContents(element[0]); var s = window.getSelection(); s.removeAllRanges(); s.addRange(r); }, 0);
                    });

                    element.bind('keydown', function (e) {
                        var $this = $(this);
                        var code = e.which;
                        var isNav = $this.hasClass('nav-mode');
                        if (code === 13) {
                            e.preventDefault();
                            if (isNav) { $this.dblclick(); }
                            else { $this.blur(); var nextRow = $this.closest('tr').next(); if (nextRow.length) setTimeout(() => nextRow.find('td').eq($this.index()).focus(), 20); }
                        }
                        else if (code === 37) {
                            if (isNav || window.getSelection().anchorOffset === 0 || e.ctrlKey) {
                                var prev = $this.prev('td[editable-td]'); if (prev.length) { e.preventDefault(); prev.focus(); }
                            }
                        }
                        else if (code === 38) {
                            e.preventDefault(); var prevRow = $this.closest('tr').prev(); if (prevRow.length) prevRow.find('td').eq($this.index()).focus();
                        }
                        else if (code === 39) {
                            if (isNav || window.getSelection().anchorOffset === $this.text().trim().length || e.ctrlKey) {
                                var next = $this.next('td[editable-td]'); if (next.length) { e.preventDefault(); next.focus(); }
                            }
                        }
                        else if (code === 40) {
                            e.preventDefault(); var nextRow = $this.closest('tr').next(); if (nextRow.length) nextRow.find('td').eq($this.index()).focus();
                        }
                    });
                    scope.$watch('lista[' + attrs.row + '].' + attrs.field, function (newVal) { if (element.text() !== newVal) element.text(newVal || ''); });
                }
            };
        }]);
    </script>
</x-app-layout>