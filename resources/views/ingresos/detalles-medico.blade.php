<x-app-layout>
    <x-slot name="header">
        <h2 class="font-black text-2xl text-slate-900 leading-tight">
            {{ __('Detalles Médico - ') . $medicoNombre . ' - ' . \Carbon\Carbon::parse($fecha)->format('d-m-Y') }}
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

    <div class="py-2">
        <div class="max-w-full mx-auto sm:px-2 lg:px-4">
            <div ng-app="TablaDetalles" ng-cloak ng-controller="TablaDetallesCtrl" id="ingresosApp">
                <div class="page-fade-in">
                    <div class="content-header p-0 mb-2 pt-2">
                    <div class="container-fluid">
                        <div class="d-flex align-items-center flex-wrap" style="gap: 10px;">
                            <h1 class="mr-3 text-dark mb-0" style="font-size: 1.4rem; font-weight: 700;">
                                Detalles: {{ $medicoNombre }} - {{ \Carbon\Carbon::parse($fecha)->format('d-m-Y') }}
                            </h1>
                            <div class="compact-stat-card">
                                <div class="d-flex align-items-center">
                                    <div class="stat-icon"><i class="fas fa-list-ol"></i></div>
                                    <div class="stat-details ml-2">
                                        <span class="stat-label">Total Registros</span>
                                        <span class="stat-value">@{{lista.length}}</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="container-fluid">
                    <div class="card shadow"
                        style="height: calc(100vh - 140px); display: flex; flex-direction: column;">
                        <div class="card-body p-0" style="flex: 1; overflow: hidden; position: relative;">
                            <div class="table-responsive"
                                style="position: absolute; top: 0; left: 0; right: 0; bottom: 0; overflow: auto; max-height: none;">
                                <table class="table table-bordered table-hover table-sm mb-0"
                                    style="table-layout: fixed;">
                                    <thead class="thead-dark-custom shadow-sm"
                                        style="position: sticky; top: 0; z-index: 10;">
                                        <tr>
                                            <th class="text-center sticky-col text-white"
                                                style="width: 40px; min-width: 40px;">#</th>
                                            @foreach($columns as $col)
                                                @php
                                                    $width = '100px';
                                                    switch ($col) {
                                                        case 'numero':
                                                            $width = '60px';
                                                            break;
                                                        case 'ano':
                                                            $width = '50px';
                                                            break;
                                                        case 'mes':
                                                            $width = '80px';
                                                            break;
                                                        case 'cm':
                                                            $width = '50px';
                                                            break;
                                                        case 'medico':
                                                            $width = '250px';
                                                            break;
                                                        case 'prof':
                                                            $width = '120px';
                                                            break;
                                                        case 'fecha':
                                                            $width = '90px';
                                                            break;
                                                        case 'se':
                                                            $width = '40px';
                                                            break;
                                                        case 'exp':
                                                            $width = '80px';
                                                            break;
                                                        case 'sexo':
                                                            $width = '40px';
                                                            break;
                                                        case 'edad':
                                                            $width = '40px';
                                                            break;
                                                        case 'tipo':
                                                            $width = '40px';
                                                            break;
                                                        case 'cond':
                                                            $width = '60px';
                                                            break;
                                                        case 'cod_col':
                                                            $width = '60px';
                                                            break;
                                                        case 'colonia':
                                                            $width = '200px';
                                                            break;
                                                        case 'sg':
                                                            $width = '40px';
                                                            break;
                                                        default:
                                                            if (strpos($col, 'cod_') === 0 && $col !== 'cod_col')
                                                                $width = '50px';
                                                            else if (strpos($col, 'diagnostico_') === 0)
                                                                $width = '250px';
                                                            else if (strpos($col, 'cond_') === 0)
                                                                $width = '40px';
                                                    }
                                                @endphp
                                                <th class="text-uppercase text-xs text-truncate text-white"
                                                    style="width: {{ $width }}; min-width: {{ $width }}; max-width: {{ $width }}; border-bottom: 2px solid #495057;"
                                                    title="{{ str_replace('_', ' ', $col) }}">
                                                    {{ str_replace('_', ' ', $col) }}
                                                </th>
                                            @endforeach
                                            <th class="text-center text-white" style="width: 50px; min-width: 50px; border-bottom: 2px solid #495057;">Acciones</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr ng-repeat="item in lista">
                                            <td class="text-center font-weight-bold bg-light text-xs sticky-col"
                                                ng-class="{'active-row': $index === filaSeleccionada}">
                                                @{{$index + 1}}
                                            </td>
                                            @foreach($columns as $col)
                                                <td editable-td row="@{{$index}}" field="{{ $col }}" class="text-xs"></td>
                                            @endforeach
                                            <td class="text-center align-middle">
                                                <button type="button" class="btn btn-sm btn-outline-danger px-2 py-0" ng-click="eliminarRegistro($index, item.id)" title="Eliminar fila">
                                                    <i class="fas fa-trash-alt text-xs"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <div class="card-footer bg-white border-top">
                            <div class="btn-group">
                                <a href="{{ route('ingresos.index') }}" class="btn btn-secondary px-4">
                                    <i class="fas fa-arrow-left mr-1"></i> Volver
                                </a>
                                <button type="button" class="btn px-4"
                                    ng-class="{'btn-primary': !tablaGuardada, 'btn-success': tablaGuardada}"
                                    ng-click="guardarCambios()"
                                    title="@{{ tablaGuardada ? 'Los datos actuales ya están guardados' : 'Guardar cambios' }}">
                                    <i class="fas"
                                        ng-class="{'fa-save': !tablaGuardada, 'fa-check': tablaGuardada}"></i>
                                    @{{ tablaGuardada ? 'Cambios Guardados' : 'Guardar Cambios' }}
                                </button>
                                <button type="button" class="btn btn-outline-secondary"
                                    ng-click="abrirBuscadorMedicos()" title="Buscar Médico (Alt+M)">
                                    <i class="fas fa-user-md mr-1"></i> Médicos
                                </button>
                                <button type="button" class="btn btn-outline-secondary"
                                    ng-click="abrirBuscadorDiagnosticos()" title="Buscar Diagnóstico (Alt+D)">
                                    <i class="fas fa-stethoscope mr-1"></i> Diagnósticos
                                </button>
                            </div> <!-- Fin btn-group -->
                        </div> <!-- Fin card-footer -->
                    </div> <!-- Fin card shadow -->
                </div> <!-- Fin container-fluid -->
                </div> <!-- Fin page-fade-in -->
                @include('partials.modales-buscadores')
            </div>
        </div>
    </div>

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

        .table {
            border-collapse: separate !important;
            border-spacing: 0 !important;
            border: 1px solid #c0c0c0 !important;
        }

        .table th,
        .table td {
            padding: 0 5px !important;
            vertical-align: middle;
            white-space: nowrap;
            height: 24px;
            border: 1px solid #c0c0c0 !important;
        }

        .sticky-col {
            position: sticky;
            left: 0;
            z-index: 2 !important;
            background-color: #f8f9fa !important;
            border-left: 1px solid #c0c0c0 !important;
            border-right: 2px solid #a0a0a0 !important;
        }

        .sticky-col.active-row {
            background-color: #007bff !important;
            color: #ffffff !important;
            border-right: 2px solid #0056b3 !important;
            z-index: 3 !important;
        }

        thead th.sticky-col {
            z-index: 11 !important;
            top: 0;
            left: 0;
            border-top: 1px solid #454d55 !important;
            background-color: #343a40 !important;
            color: #ffffff !important;
        }

        .text-xs {
            font-size: 0.75rem !important;
        }

        table tbody tr:hover {
            background-color: rgba(0, 123, 255, 0.08) !important;
        }

        td[contenteditable="true"] {
            outline: none;
            min-height: 20px;
            padding: 0 5px !important;
            border: 1px solid #c0c0c0;
            transition: background 0.2s;
            cursor: cell;
        }

        td[contenteditable="true"]:focus {
            background-color: #ffffff;
            box-shadow: inset 0 0 0 2px #007bff;
            border: 1px solid #007bff !important;
            z-index: 5;
            position: relative;
        }

        td {
            overflow: hidden;
            text-overflow: ellipsis;
            text-align: left !important;
            padding: 0 8px !important;
            height: 20px;
            line-height: 20px;
        }

        td:focus,
        td.nav-mode {
            overflow: visible !important;
            white-space: normal !important;
            min-width: max-content;
            z-index: 100 !important;
            background-color: white !important;
            box-shadow: 0 2px 8px rgba(0, 0, 0, 0.15);
        }

        .thead-dark-custom th {
            border: 1px solid #454d55 !important;
            position: sticky;
            top: 0;
            background-color: #343a40 !important;
            color: #ffffff !important;
            font-size: 0.7rem !important;
        }

        .compact-stat-card {
            background: #d1dce7;
            border: 1px solid #b8c5d3;
            border-radius: 8px;
            padding: 5px 15px;
            display: flex;
            align-items: center;
            gap: 15px;
            box-shadow: 0 3px 6px rgba(0, 0, 0, 0.1);
            border-left: 5px solid #28a745;
            min-width: auto;
        }

        .stat-icon {
            width: 32px;
            height: 32px;
            background: #ffffff !important;
            color: #28a745;
            box-shadow: 0 1px 3px rgba(0, 0, 0, 0.1);
            border-radius: 6px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
        }

        .stat-details {
            display: flex;
            flex-direction: column;
        }

        .stat-label {
            font-size: 0.65rem;
            color: #6c757d;
            font-weight: 700;
            text-transform: uppercase;
        }

        .stat-value {
            font-size: 0.95rem;
            font-weight: 800;
            color: #2d3436;
        }

        .table-responsive::-webkit-scrollbar {
            width: 10px;
            height: 10px;
        }

        .table-responsive::-webkit-scrollbar-thumb {
            background: #aaa;
            border-radius: 5px;
        }
    </style>

    <script>
        // --- INYECCIÓN MANUAL DEL SIDEBAR DERECHO ---
        document.addEventListener('DOMContentLoaded', function () {
            var sidebarHtml = `
            <div class="p-3">
                <h5 class="mb-2">Configuración de Tabla</h5>
                <hr class="mb-3">
                <div class="mb-3">
                    <label class="mb-1">Tamaño de Fuente</label>
                    <select id="configFontSize" class="form-control form-control-sm">
                        <option value="10px">Extra Pequeña (10px)</option>
                        <option value="12px" selected>Pequeña (12px)</option>
                        <option value="14px">Normal (14px)</option>
                        <option value="16px">Grande (16px)</option>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="mb-1">Altura de Filas (Padding V)</label>
                    <input type="range" class="custom-range" id="configRowHeight" min="0" max="16" step="1" value="0">
                    <small class="text-muted d-block text-right"><span id="lblRowHeight">0</span>px</small>
                </div>
                <div class="mb-3">
                    <label class="mb-1">Espacio Lateral (Padding H)</label>
                    <input type="range" class="custom-range" id="configCellPadding" min="0" max="30" step="1" value="5">
                    <small class="text-muted d-block text-right"><span id="lblCellPadding">5</span>px</small>
                </div>
                <hr>
                <button class="btn btn-sm btn-outline-secondary btn-block" onclick="resetConfigTabla()">
                    Restaurar Valores
                </button>
            </div>`;

            // Intentar inyectar en el contenido
            var $target = $('.control-sidebar-content');
            if ($target.length === 0) $target = $('.control-sidebar');

            if ($target.length > 0) {
                // Limpiar cualquier contenido previo (como temas de AdminLTE) si queremos exclusividad
                // O mejor, inyectarlo.
                $target.html(sidebarHtml);

                // --- FIX VISUAL: Forzar altura completa ---
                $target.css({
                    'height': '100vh',
                    'min-height': '100%',
                    'overflow-y': 'auto',
                    'padding-bottom': '50px'
                });
                $('.control-sidebar').css({
                    'bottom': '0',
                    'top': '57px',
                    'height': 'calc(100vh - 57px)',
                    'position': 'fixed'
                });

                setTimeout(function () {
                    if ($target.html().trim() === '' || $target.html().indexOf('Configuración de Tabla') === -1) {
                        $target.html(sidebarHtml);
                        initTableConfig();
                    }
                    $target.css('height', '100vh');
                    $('.control-sidebar').css('height', 'calc(100vh - 57px)');
                }, 800);
            }

            // Inicializar controles después de inyectar
            setTimeout(initTableConfig, 100);
        });

        function initTableConfig() {
            var confFontSize = document.getElementById('configFontSize');
            if (!confFontSize) return;

            var confRowHeight = document.getElementById('configRowHeight');
            var confCellPadding = document.getElementById('configCellPadding');
            var lblRowHeight = document.getElementById('lblRowHeight');
            var lblCellPadding = document.getElementById('lblCellPadding');

            var styleTag = document.getElementById('dynamic-table-styles');
            if (!styleTag) {
                styleTag = document.createElement('style');
                styleTag.id = 'dynamic-table-styles';
                document.head.appendChild(styleTag);
            }

            function updateTableStyles() {
                var fs = confFontSize.value;
                var pv = confRowHeight.value + 'px';
                var ph = confCellPadding.value + 'px';

                if (lblRowHeight) lblRowHeight.textContent = confRowHeight.value;
                if (lblCellPadding) lblCellPadding.textContent = confCellPadding.value;

                var css = `
                    /* 1. Fuente solo para contenido */
                    .table td, .table td input, .table td select, .text-xs, .form-control-sm { font-size: ${fs} !important; }
                    
                    /* 2. Padding */
                    .table th, .table td { padding-top: ${pv} !important; padding-bottom: ${pv} !important; padding-left: ${ph} !important; padding-right: ${ph} !important; }
                    .table input.form-control-sm { height: auto !important; padding: ${pv} ${ph} !important; }

                    /* 3. Auto widths */
                    .table { table-layout: auto !important; }
                    .table th, .table td { white-space: nowrap; max-width: none !important; width: auto !important; min-width: 40px; }
                `;
                styleTag.innerHTML = css;

                var config = { fontSize: fs, rowHeight: confRowHeight.value, cellPadding: confCellPadding.value };
                localStorage.setItem('tablaConfig', JSON.stringify(config));
            }

            var saved = localStorage.getItem('tablaConfig');
            if (saved) {
                try {
                    var c = JSON.parse(saved);
                    if (c.fontSize && confFontSize) confFontSize.value = c.fontSize;
                    if (c.rowHeight && confRowHeight) confRowHeight.value = c.rowHeight;
                    if (c.cellPadding && confCellPadding) confCellPadding.value = c.cellPadding;
                } catch (e) { }
            }

            if (confFontSize) {
                confFontSize.removeEventListener('change', updateTableStyles);
                confRowHeight.removeEventListener('input', updateTableStyles);
                confCellPadding.removeEventListener('input', updateTableStyles);

                confFontSize.addEventListener('change', updateTableStyles);
                confRowHeight.addEventListener('input', updateTableStyles);
                confCellPadding.addEventListener('input', updateTableStyles);
                updateTableStyles();
            }

            window.resetConfigTabla = function () {
                confFontSize.value = '12px';
                confRowHeight.value = '0';
                confCellPadding.value = '5';
                updateTableStyles();
            };
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://ajax.googleapis.com/ajax/libs/angularjs/1.8.2/angular.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/es.js"></script>
    <script>
        'use strict';
        flatpickr.localize(flatpickr.l10ns.es);
        window.enfocarError = function (rowIndex, fieldName) {
            Swal.close(); var cell = $('td[row="' + rowIndex + '"][field="' + fieldName + '"]');
            if (cell.length) { $('html, body').animate({ scrollTop: cell.offset().top - 200 }, 500); cell.addClass('bg-danger text-white'); setTimeout(function () { cell.removeClass('bg-danger text-white'); cell.focus(); }, 2000); }
        };
        window.borrarDatoErroneo = function (rowIndex, fieldName) {
            var scope = angular.element(document.getElementById('ingresosApp')).scope();
            if (scope) { scope.$apply(function () { scope.lista[rowIndex][fieldName] = ''; if (fieldName && fieldName.match(/^cod_\d+$/)) { var k = fieldName.split('_')[1]; scope.lista[rowIndex]['diagnostico_' + k] = ''; scope.lista[rowIndex]['cond_' + k] = ''; } }); Swal.close(); Swal.fire({ icon: 'success', title: 'Dato borrado', toast: true, position: 'top-end', showConfirmButton: false, timer: 3000 }); }
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
            $scope.filaSeleccionada = -1; $scope.tablaGuardada = true; $scope.setFilaSeleccionada = function (index) { $scope.filaSeleccionada = parseInt(index); };
            $scope.marcarModificado = function () { if ($scope.tablaGuardada) { $scope.tablaGuardada = false; if (!$scope.$$phase) $scope.$digest(); } };

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
            $scope.calcularRangos = function (obj) { if (!obj.edad || !obj.tipo) return; var edad = parseFloat(obj.edad); var tipo = obj.tipo.toUpperCase(); if (isNaN(edad)) return; var edadEnAnios = edad; if (tipo === 'M') edadEnAnios = edad / 12; else if (tipo === 'D') edadEnAnios = edad / 365; obj.rango = rango1Categorias[getCategoriaRango1(edadEnAnios)]; obj.rango_2 = rango2Categorias[getCategoriaRango2(edadEnAnios)]; obj.rango_3 = rango3Categorias[getCategoriaRango3(edadEnAnios)]; obj.rango_4 = rango4Categorias[getCategoriaRango4(edadEnAnios)]; obj.rango_5 = rango5Categorias[getCategoriaRango5(edadEnAnios)]; };
            $scope.calcularConversion = function (obj) { if (!obj.edad || !obj.tipo) return; var edad = parseInt(obj.edad, 10); var tipo = (obj.tipo || '').toUpperCase(); if (isNaN(edad)) return; var changed = true; while (changed) { changed = false; if (tipo === 'D' && edad > 30) { edad = Math.floor(edad / 30); tipo = 'M'; changed = true; } else if (tipo === 'M' && edad > 11) { edad = Math.floor(edad / 12); tipo = 'A'; changed = true; } } obj.edad = edad.toString(); obj.tipo = tipo; };
            $scope.calcularPgEmbYSm = function (obj) { var tieneEmbarazo = false; var categoriaSM = ''; var palabrasEmbarazo = ['PRENATAL', 'GESTACION', 'GESTACIONAL']; for (var i = 1; i <= 7; i++) { var codigo = obj['cod_' + i]; var textodiag = (obj['diagnostico_' + i] || '').toUpperCase(); if (codigo) { var diagEncontrado = ($scope.diagnosticosList || []).find(d => d.codigo == codigo); if (diagEncontrado) { if (diagEncontrado.requiere_embarazo) tieneEmbarazo = true; if ((diagEncontrado.categoria || '').toString().trim().toUpperCase().indexOf('SM') !== -1) categoriaSM = 'SM'; if (!tieneEmbarazo) { var pat = (diagEncontrado.patologia || '').toUpperCase(); if (palabrasEmbarazo.some(p => pat.indexOf(p) !== -1)) tieneEmbarazo = true; } } } if (!tieneEmbarazo && textodiag && palabrasEmbarazo.some(p => textodiag.indexOf(p) !== -1)) tieneEmbarazo = true; } obj.pg_emb = tieneEmbarazo ? 'EMBARAZADA' : 'POBLACION GENERAL'; obj.sm = categoriaSM; };

            $scope.searchMedicoText = ''; $scope.searchDiagnosticoText = ''; $scope.searchColoniaText = ''; $scope.currentFieldForModal = null;
            $scope.abrirBuscadorMedicos = function (field) { $scope.currentFieldForModal = field; $scope.searchMedicoText = ''; $('#modalBuscadorMedicos').modal('show'); setTimeout(() => $('#searchMedico').focus(), 500); };
            $scope.abrirBuscadorDiagnosticos = function (field) { $scope.currentFieldForModal = field; $scope.searchDiagnosticoText = ''; $scope.numeroDiagnostico = (field && field.numero) ? field.numero : null; $('#modalBuscadorDiagnosticos').modal('show'); setTimeout(() => $('#searchDiagnostico').focus(), 500); };
            $scope.abrirBuscadorColonias = function (field) { $scope.currentFieldForModal = field; $scope.searchColoniaText = ''; $('#modalBuscadorColonias').modal('show'); setTimeout(() => $('#searchColonia').focus(), 500); };
            $scope.seleccionarMedico = function (medico) { if ($scope.currentFieldForModal && $scope.lista[$scope.currentFieldForModal.row]) { var row = $scope.currentFieldForModal.row; $scope.lista[row].cm = medico.COD_MED; $scope.lista[row].medico = medico.NOM_MED; $scope.lista[row].prof = medico.ESPECIALIDAD; $scope.lista[row].jornada = medico.JORNADA || ''; $scope.marcarModificado(); } $('#modalBuscadorMedicos').modal('hide'); };
            $scope.seleccionarDiagnostico = function (diagnostico) { if ($scope.currentFieldForModal && $scope.lista[$scope.currentFieldForModal.row]) { var row = $scope.currentFieldForModal.row; var field = $scope.currentFieldForModal.field; var num = $scope.currentFieldForModal.numero; if (!num && typeof field === 'string') { if (field.match(/^cod_\d$/)) num = field.split('_')[1]; else if (field.match(/^diagnostico_\d$/)) num = field.split('_')[1]; } if (num) { $scope.lista[row]['cod_' + num] = diagnostico.codigo; $scope.lista[row]['diagnostico_' + num] = diagnostico.patologia; $scope.calcularPgEmbYSm($scope.lista[row]); $scope.marcarModificado(); } } $('#modalBuscadorDiagnosticos').modal('hide'); };
            $scope.seleccionarColonia = function (colonia) { if ($scope.currentFieldForModal && $scope.lista[$scope.currentFieldForModal.row]) { var row = $scope.currentFieldForModal.row; $scope.lista[row].cod_col = colonia.COD_COL; $scope.lista[row].colonia = colonia.COLONIA; $scope.marcarModificado(); } $('#modalBuscadorColonias').modal('hide'); };

            $scope.guardarCambios = function () {
                // Validación forzada: evaluamos siempre los errores de la tabla
                var filasConErrores = [], validaciones = $scope.validacionesDiagnosticos || [];
                $scope.lista.forEach(function (fila, index) {
                    var numeroFila = fila.numero || (index + 1), erroresFila = [];
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
                    }).then(r => r.json()).then(data => { if (data.success) { $scope.$apply(() => { $scope.tablaGuardada = true; }); Swal.fire('¡Actualizado!', data.message, 'success'); } else { Swal.fire('Atención', data.message + (data.errors ? ':\n' + data.errors.slice(0, 3).join('\n') : ''), 'warning'); } }).catch(e => { Swal.fire('Error', 'Error de conexión', 'error'); console.error(e); });
                }
            };
            document.addEventListener('keydown', function (e) { if (e.altKey && (e.key === 'm' || e.key === 'M')) { e.preventDefault(); $scope.$apply(() => $scope.abrirBuscadorMedicos($scope.currentFieldForModal)); } else if (e.altKey && (e.key === 'd' || e.key === 'D')) { e.preventDefault(); $scope.$apply(() => $scope.abrirBuscadorDiagnosticos($scope.currentFieldForModal)); } else if (e.altKey && (e.key === 'c' || e.key === 'C')) { e.preventDefault(); $scope.$apply(() => $scope.abrirBuscadorColonias($scope.currentFieldForModal)); } });
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
                    function getEpidemiologicalWeek(d) { d = new Date(d); d.setHours(12, 0, 0, 0); var y = d.getFullYear(); var jan1 = new Date(y, 0, 1, 12); var firstSun = jan1.getDay() === 0 ? jan1 : new Date(y, 0, 1 + (7 - jan1.getDay()), 12); if (d < firstSun) return 53; return Math.floor(((d - firstSun) / 86400000) / 7) + 1; }
                    function actualizarFecha(dateStr) {
                        if (dateStr && dateStr.length >= 6) {
                            var parts = dateStr.split(/[\/\-\.]/);
                            if (parts.length === 3) {
                                var d = parseInt(parts[0], 10), m = parseInt(parts[1], 10), y = parseInt(parts[2], 10);
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

                        if (attrs.field === 'fecha' && text.length > 0) { actualizarFecha(text); text = scope.lista[attrs.row][attrs.field]; }
                        if (attrs.field === 'sexo' && text !== 'M' && text !== 'H') text = ''; else if (attrs.field === 'se') text = text.replace(/[^0-9]/g, ''); else if (attrs.field === 'tipo' && text !== 'A' && text !== 'M' && text !== 'D') text = ''; else if (attrs.field === 'edad') { var c = text.replace(/[^0-9]/g, ''); text = c ? parseInt(c).toString() : ''; }

                        element.text(text); element.removeClass('edit-mode nav-mode');
                        scope.$apply(function () {
                            if (scope.lista[attrs.row][attrs.field] !== text || attrs.field === 'edad' || attrs.field === 'tipo') {
                                scope.lista[attrs.row][attrs.field] = text; scope.marcarModificado(); scope.calcularConversion(scope.lista[attrs.row]); scope.calcularRangos(scope.lista[attrs.row]);
                                var finalEdad = parseInt(scope.lista[attrs.row].edad, 10); if (!isNaN(finalEdad) && finalEdad > 150) { Swal.fire({ title: 'Edad fuera de rango', text: "¿Es correcta?", icon: 'warning', showCancelButton: true }).then((r) => { if (!r.isConfirmed) scope.$apply(() => { scope.lista[attrs.row].edad = ''; element.text(''); setTimeout(() => element.focus(), 100); }); }); }
                                if (scope.lista[attrs.row][attrs.field] !== text) element.text(scope.lista[attrs.row][attrs.field]);
                                if (attrs.field === 'cm') {
                                    if (!text) {
                                        scope.lista[attrs.row].medico = '';
                                        scope.lista[attrs.row].prof = '';
                                        scope.lista[attrs.row].jornada = '';
                                    } else {
                                        var med = scope.medicosList.find(m => m.COD_MED == text);
                                        if (med) {
                                            scope.lista[attrs.row].medico = med.NOM_MED;
                                            scope.lista[attrs.row].prof = med.ESPECIALIDAD;
                                            scope.lista[attrs.row].jornada = med.JORNADA || '';
                                        } else {
                                            scope.lista[attrs.row].medico = 'MEDICO NO ENCONTRADO';
                                            Swal.fire({ title: 'Médico no encontrado', text: "El código " + text + " no existe", icon: 'warning' });
                                        }
                                    }
                                }
                                if (attrs.field === 'cod_col') {
                                    if (!text) {
                                        scope.lista[attrs.row].colonia = '';
                                    } else {
                                        var col = scope.coloniasList.find(c => c.COD_COL == text);
                                        if (col) scope.lista[attrs.row].colonia = col.COLONIA;
                                        else {
                                            scope.lista[attrs.row].colonia = 'COLONIA NO ENCONTRADA';
                                            Swal.fire({ title: 'Colonia no encontrada', text: "El código " + text + " no existe", icon: 'warning' });
                                        }
                                    }
                                }
                                if (attrs.field.match(/^cod_[1-7]$/)) {
                                    var num = attrs.field.split('_')[1];
                                    if (!text) {
                                        // SI ELIMINA EL CÓDIGO, SE BORRA EL DIAGNÓSTICO Y LA CONDICIÓN
                                        scope.lista[attrs.row]['diagnostico_' + num] = '';
                                        scope.lista[attrs.row]['cond_' + num] = '';
                                    } else {
                                        // Buscar por código o código auxiliar (algunos diagnósticos usan el auxiliar como código principal de búsqueda)
                                        var diag = scope.diagnosticosList.find(d => d.codigo == text || (d.auxiliar && d.auxiliar == text));
                                        if (diag) {
                                            scope.lista[attrs.row]['diagnostico_' + num] = diag.patologia;
                                        } else {
                                            scope.lista[attrs.row]['diagnostico_' + num] = 'SIN DIAGNOSTICO PARA ESTE CODIGO';
                                            Swal.fire({
                                                title: 'Diagnóstico no encontrado',
                                                text: "El código '" + text + "' no existe en la base de datos.",
                                                icon: 'warning',
                                                confirmButtonText: 'Entendido'
                                            });
                                        }
                                    }
                                    scope.calcularPgEmbYSm(scope.lista[attrs.row]);
                                }
                            }
                        });
                    });
                    element.bind('dblclick', function () {
                        if (attrs.field === 'cm' || attrs.field === 'medico') scope.$apply(() => scope.abrirBuscadorMedicos({ row: attrs.row, field: attrs.field }));
                        if (attrs.field === 'cod_col' || attrs.field === 'colonia') scope.$apply(() => scope.abrirBuscadorColonias({ row: attrs.row, field: attrs.field }));
                        if (attrs.field.match(/^cod_\d$/) || attrs.field.match(/^diagnostico_\d$/)) { var num = attrs.field.split('_')[1]; scope.$apply(() => scope.abrirBuscadorDiagnosticos({ row: attrs.row, field: attrs.field, numero: num })); }
                        element.addClass('edit-mode').removeClass('nav-mode'); if (fp) fp.open(); var r = document.createRange(); r.selectNodeContents(element[0]); r.collapse(false); var s = window.getSelection(); s.removeAllRanges(); s.addRange(r);
                    });
                    element.bind('focus', function () { element.addClass('nav-mode').removeClass('edit-mode'); if (!scope.$$phase) scope.$apply(() => scope.setFilaSeleccionada(attrs.row)); setTimeout(() => { var r = document.createRange(); r.selectNodeContents(element[0]); var s = window.getSelection(); s.removeAllRanges(); s.addRange(r); }, 0); });

                    element.bind('input', function () {
                        var text = element.text().trim().toUpperCase();
                        scope.$apply(function () {
                            scope.lista[attrs.row][attrs.field] = text;
                            scope.marcarModificado();

                            // Búsqueda interactiva y LIMPIEZA INMEDIATA
                            if (attrs.field === 'cm') {
                                if (text) {
                                    var med = scope.medicosList.find(m => m.COD_MED == text);
                                    if (med) {
                                        scope.lista[attrs.row].medico = med.NOM_MED;
                                        scope.lista[attrs.row].prof = med.ESPECIALIDAD;
                                        scope.lista[attrs.row].jornada = med.JORNADA || '';
                                    } else {
                                        scope.lista[attrs.row].medico = 'MEDICO NO ENCONTRADO';
                                    }
                                } else {
                                    scope.lista[attrs.row].medico = '';
                                    scope.lista[attrs.row].prof = '';
                                    scope.lista[attrs.row].jornada = '';
                                }
                            }
                            if (attrs.field === 'cod_col') {
                                if (text) {
                                    var col = scope.coloniasList.find(c => c.COD_COL == text);
                                    if (col) scope.lista[attrs.row].colonia = col.COLONIA;
                                    else scope.lista[attrs.row].colonia = 'COLONIA NO ENCONTRADA';
                                } else {
                                    scope.lista[attrs.row].colonia = '';
                                }
                            }
                            if (attrs.field.match(/^cod_[1-7]$/)) {
                                var num = attrs.field.split('_')[1];
                                if (text) {
                                    var diag = scope.diagnosticosList.find(d => d.codigo == text || (d.auxiliar && d.auxiliar == text));
                                    if (diag) {
                                        scope.lista[attrs.row]['diagnostico_' + num] = diag.patologia;
                                        scope.calcularPgEmbYSm(scope.lista[attrs.row]);
                                    } else {
                                        scope.lista[attrs.row]['diagnostico_' + num] = 'SIN DIAGNOSTICO PARA ESTE CODIGO';
                                    }
                                } else {
                                    // LIMPIAR AL INSTANTE SI SE BORRA EL CÓDIGO
                                    scope.lista[attrs.row]['diagnostico_' + num] = '';
                                    scope.lista[attrs.row]['cond_' + num] = '';
                                    scope.calcularPgEmbYSm(scope.lista[attrs.row]);
                                }
                            }
                        });
                    });

                    element.bind('keydown', function (e) {
                        var $this = $(this), code = e.which, isNav = $this.hasClass('nav-mode');
                        if (code === 13) {
                            e.preventDefault();
                            if (isNav) { $this.dblclick(); } else {
                                $this.blur(); var nextRow = $this.closest('tr').next();
                                if (nextRow.length) setTimeout(() => nextRow.find('td').eq($this.index()).focus(), 20);
                            }
                        }
                        else if (code === 37) { if (isNav || window.getSelection().anchorOffset === 0 || e.ctrlKey) { var prev = $this.prev('td[editable-td]'); if (prev.length) { e.preventDefault(); prev.focus(); } } }
                        else if (code === 38) { e.preventDefault(); var prevRow = $this.closest('tr').prev(); if (prevRow.length) prevRow.find('td').eq($this.index()).focus(); }
                        else if (code === 39) { if (isNav || window.getSelection().anchorOffset === $this.text().trim().length || e.ctrlKey) { var next = $this.next('td[editable-td]'); if (next.length) { e.preventDefault(); next.focus(); } } }
                        else if (code === 40) { e.preventDefault(); var nextRow = $this.closest('tr').next(); if (nextRow.length) nextRow.find('td').eq($this.index()).focus(); }
                    });

                    scope.$watch('lista[' + attrs.row + '].' + attrs.field, function (newVal) { if (element.text() !== newVal) element.text(newVal || ''); });
                }
            };
        }]);
    </script>
</x-app-layout>