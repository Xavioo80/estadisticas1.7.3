<!-- Barra de Filtros y Acciones en Una Sola Fila Horizontal -->
<div class="filter-container no-print mb-2" style="background: var(--bg-surface) !important; border: 1px solid var(--border-color) !important; border-radius: var(--radius-md, 10px) !important; padding: 0.5rem 0.85rem !important; display: flex !important; flex-direction: row !important; align-items: center !important; justify-content: space-between !important; flex-wrap: nowrap !important; gap: 8px !important; box-shadow: var(--shadow-sm) !important; width: 100% !important;">
    <form id="filter-form" action="{{ route('informes.alerta-semanal') }}" method="GET" style="display: flex !important; flex-direction: row !important; align-items: center !important; flex-wrap: nowrap !important; gap: 8px !important; margin: 0 !important; flex: 1 1 auto !important; min-width: 0 !important;">
        <!-- Año -->
        <div style="width: 95px !important; min-width: 95px !important; flex-shrink: 0 !important;">
            <select name="ano" class="filter-select w-full ajax-filter" style="width: 100% !important; height: 34px !important; font-weight: 700 !important; font-size: 0.82rem !important;">
                @foreach($anos as $a)
                    <option value="{{ $a }}" {{ $a == $anoDefault ? 'selected' : '' }}>{{ $a }}</option>
                @endforeach
            </select>
        </div>

        <!-- Mes -->
        <div style="width: 140px !important; min-width: 140px !important; flex-shrink: 0 !important;">
            <select name="mes" class="filter-select w-full ajax-filter" style="width: 100% !important; height: 34px !important; font-weight: 700 !important; font-size: 0.82rem !important;">
                <option value="">-- Todos los Meses --</option>
                @foreach($meses as $m)
                    <option value="{{ $m }}" {{ strtoupper($m) == strtoupper($mesDefault) ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
            </select>
        </div>

        <!-- Semana Epidemiológica -->
        <div style="width: 135px !important; min-width: 135px !important; flex-shrink: 0 !important;">
            <select name="se" class="filter-select w-full ajax-filter" style="width: 100% !important; height: 34px !important; font-weight: 700 !important; font-size: 0.82rem !important;">
                @foreach($semanas as $s)
                    <option value="{{ $s }}" {{ $s == $seDefault ? 'selected' : '' }}>Semana {{ $s }}</option>
                @endforeach
            </select>
        </div>

        <!-- Rango Epidemiológico -->
        <div class="d-flex align-items-center px-2.5" style="background: var(--bg-subtle); border: 1px solid var(--border-color); border-radius: 8px; height: 34px; white-space: nowrap; flex-shrink: 0;">
            <span class="text-muted mr-1.5" style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Rango:</span>
            <span class="font-weight-bold text-primary" style="font-size: 0.82rem;">{{ $fechaInfo['start'] }} al {{ $fechaInfo['end'] }}</span>
        </div>
    </form>

    <!-- Acciones -->
    <div style="display: flex !important; flex-direction: row !important; align-items: center !important; flex-shrink: 0 !important; gap: 6px !important;">
        <button type="button" class="btn btn-subtle btn-sm" onclick="copyToExcel()" title="Copiar al Portapapeles" style="font-weight: 600; height: 34px; padding: 0 0.85rem; border-radius: 8px; display: inline-flex; align-items: center; gap: 4px;">
            <i class="bi bi-clipboard-check text-primary"></i> <span>Copiar</span>
        </button>
        <button type="button" class="btn btn-subtle btn-sm" onclick="window.print()" title="Imprimir informe" style="font-weight: 600; height: 34px; padding: 0 0.85rem; border-radius: 8px; display: inline-flex; align-items: center; gap: 4px;">
            <i class="bi bi-printer"></i> <span>Imprimir</span>
        </button>
    </div>
</div>

<!-- Tabla del Telegrama Semanal -->
<div class="informe-table-container custom-scrollbar mb-4" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md, 10px); overflow: auto; box-shadow: var(--shadow-sm); width: 100%;">
    <table class="table table-bordered table-alerta mb-0" style="width: 100%; border-collapse: separate; border-spacing: 0;">
        <colgroup>
            <col style="width: 45%;">
            <col style="width: 11%;">
            <col style="width: 11%;">
            <col style="width: 11%;">
            <col style="width: 11%;">
            <col style="width: 11%;">
        </colgroup>
        <thead>
            <tr style="background: var(--bg-subtle);">
                <th rowspan="2" class="sticky-header text-left" style="padding: 7px 10px; font-size: 0.80rem; font-weight: 700; color: var(--text-primary); border-color: var(--border-color); vertical-align: middle;">
                    ENFERMEDADES / EVENTOS
                </th>
                <th colspan="4" class="sticky-header text-center" style="padding: 5px 8px; font-size: 0.78rem; font-weight: 700; color: var(--text-primary); border-color: var(--border-color);">
                    NÚMERO DE CASOS POR GRUPO DE EDAD
                </th>
                <th rowspan="2" class="sticky-header text-center" style="padding: 7px 10px; font-size: 0.80rem; font-weight: 700; color: var(--text-primary); border-color: var(--border-color); vertical-align: middle;">
                    TOTAL
                </th>
            </tr>
            <tr style="background: var(--bg-subtle);">
                <th class="sticky-header text-center" style="padding: 5px 6px; font-size: 0.73rem; font-weight: 600; color: var(--text-muted); border-color: var(--border-color);">&lt; 1 AÑO</th>
                <th class="sticky-header text-center" style="padding: 5px 6px; font-size: 0.73rem; font-weight: 600; color: var(--text-muted); border-color: var(--border-color);">1 - 4 AÑOS</th>
                <th class="sticky-header text-center" style="padding: 5px 6px; font-size: 0.73rem; font-weight: 600; color: var(--text-muted); border-color: var(--border-color);">5 - 14 AÑOS</th>
                <th class="sticky-header text-center" style="padding: 5px 6px; font-size: 0.73rem; font-weight: 600; color: var(--text-muted); border-color: var(--border-color);">15 Y + AÑOS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rowsDef as $idx => $row)
                <tr style="border-color: var(--border-color);">
                    <td class="text-left font-weight-600" style="padding: 4px 10px; color: var(--text-primary); font-size: 0.80rem; border-color: var(--border-color);">
                        {{ $row['label'] }}
                    </td>

                    @if($row['label'] === 'CADENA RED DE FRIO')
                        <td colspan="4" class="text-center cursor-pointer font-weight-bold select-none p-1" id="coldChainCell" onclick="toggleColdChain()" style="background: #22c55e; color: #fff; font-size: 0.80rem; border-color: var(--border-color);">
                            <span id="coldChainLabel">VERDE</span>
                        </td>
                        <td class="text-center text-muted" style="padding: 4px 6px; border-color: var(--border-color);">-</td>
                    @else
                        <!-- < 1 AÑO -->
                        <td class="text-center cell-clickable {{ $results[$idx]['less_1'] > 0 ? 'font-weight-bold text-primary' : 'text-muted' }}" 
                            onclick="fetchDetails({{ $idx }}, 'less_1')"
                            style="padding: 4px 6px; font-size: 0.85rem; cursor: pointer; border-color: var(--border-color); transition: background 0.15s;">
                            {{ $results[$idx]['less_1'] ?: '0' }}
                        </td>

                        <!-- 1 - 4 AÑOS -->
                        <td class="text-center cell-clickable {{ $results[$idx]['1_4'] > 0 ? 'font-weight-bold text-primary' : 'text-muted' }}" 
                            onclick="fetchDetails({{ $idx }}, '1_4')"
                            style="padding: 4px 6px; font-size: 0.85rem; cursor: pointer; border-color: var(--border-color); transition: background 0.15s;">
                            {{ $results[$idx]['1_4'] ?: '0' }}
                        </td>

                        <!-- 5 - 14 AÑOS -->
                        <td class="text-center cell-clickable {{ $results[$idx]['5_14'] > 0 ? 'font-weight-bold text-primary' : 'text-muted' }}" 
                            onclick="fetchDetails({{ $idx }}, '5_14')"
                            style="padding: 4px 6px; font-size: 0.85rem; cursor: pointer; border-color: var(--border-color); transition: background 0.15s;">
                            {{ $results[$idx]['5_14'] ?: '0' }}
                        </td>

                        <!-- 15 Y + AÑOS -->
                        <td class="text-center cell-clickable {{ $results[$idx]['15_plus'] > 0 ? 'font-weight-bold text-primary' : 'text-muted' }}" 
                            onclick="fetchDetails({{ $idx }}, '15_plus')"
                            style="padding: 4px 6px; font-size: 0.85rem; cursor: pointer; border-color: var(--border-color); transition: background 0.15s;">
                            {{ $results[$idx]['15_plus'] ?: '0' }}
                        </td>

                        <!-- TOTAL -->
                        <td class="text-center cell-clickable {{ $results[$idx]['total'] > 0 ? 'font-weight-bold' : 'text-muted' }}" 
                            onclick="fetchDetails({{ $idx }}, 'total')"
                            style="padding: 4px 6px; font-size: 0.85rem; cursor: pointer; background: var(--bg-subtle); color: var(--text-primary); border-color: var(--border-color); transition: background 0.15s;">
                            {{ $results[$idx]['total'] ?: '0' }}
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Firmas para Impresión -->
    <div class="mt-3 p-3 d-flex justify-content-between" style="border-top: 1px dashed var(--border-color);">
        <div class="text-center" style="width: 40%;">
            <div style="border-top: 1px solid var(--text-primary); padding-top: 4px; font-size: 0.78rem; font-weight: 700; color: var(--text-primary);">
                FIRMA Y SELLO RESPONSABLE
            </div>
        </div>
        <div class="text-center" style="width: 40%;">
            <div style="border-top: 1px solid var(--text-primary); padding-top: 4px; font-size: 0.78rem; font-weight: 700; color: var(--text-primary);">
                FECHA DE ENTREGA: {{ now()->format('d/m/Y') }}
            </div>
        </div>
    </div>
</div>

<!-- Modal de Detalles de Pacientes Alerta Semanal -->
<div class="modal fade" id="modalAlertaDetalles" tabindex="-1" role="dialog" aria-labelledby="modalAlertaDetallesLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content" style="background: var(--bg-surface); border: 1px solid var(--border-color); color: var(--text-primary); border-radius: var(--radius-lg, 12px); box-shadow: var(--shadow-2xl);">
            <!-- Modal Header -->
            <div class="modal-header" style="background: var(--bg-subtle); border-bottom: 1px solid var(--border-color); padding: 1rem 1.25rem;">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 38px; height: 38px; border-radius: 50%; background: var(--color-primary-light, rgba(99, 102, 241, 0.15)); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
                        <i class="bi bi-shield-exclamation"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-weight-bold mb-0" id="modalAlertaTitle" style="color: var(--text-primary); font-size: 1.05rem;">
                            Detalle de Pacientes
                        </h5>
                        <p class="small mb-0 mt-0.5" id="modalAlertaSubtitle" style="color: var(--text-muted); font-size: 0.8rem; font-weight: 500;">
                            Desglose de atenciones epidemiológicas
                        </p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge badge-primary px-3 py-1" id="modalAlertaTotalBadge" style="font-size: 0.8rem; font-weight: 700; border-radius: 9999px;">
                        0 Casos
                    </span>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: var(--text-primary); opacity: 0.7; font-size: 1.5rem; outline: none; border: none; background: transparent;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-3" style="background: var(--bg-surface); color: var(--text-primary);">
                <!-- Loader -->
                <div id="modalAlertaLoader" class="py-5 text-center" style="display: none;">
                    <div class="spinner-border text-primary" role="status" style="width: 2.5rem; height: 2.5rem;">
                        <span class="sr-only">Cargando...</span>
                    </div>
                    <p class="small text-muted mt-2 font-weight-bold text-uppercase" style="letter-spacing: 0.05em;">Consultando registros...</p>
                </div>

                <div id="modalAlertaBodyContent">
                    <!-- Resumen KPIs Cards -->
                    <div class="row no-gutters mb-3" style="gap: 8px;">
                        <div class="col-auto">
                            <div class="p-2 px-3 text-center rounded border" style="background: var(--bg-subtle); border-color: var(--border-color) !important; min-width: 95px;">
                                <span class="d-block text-muted text-uppercase" style="font-size: 0.68rem; font-weight: 700; letter-spacing: 0.05em;">Total Casos</span>
                                <span class="font-weight-bold" style="font-size: 1.3rem; color: var(--color-primary);" id="modalSummaryTotal">0</span>
                            </div>
                        </div>
                        <div class="col">
                            <div class="p-2 px-3 rounded border h-100 d-flex flex-column justify-content-center" style="background: var(--bg-subtle); border-color: var(--border-color) !important;">
                                <span class="text-muted text-uppercase d-block mb-1" style="font-size: 0.68rem; font-weight: 700; letter-spacing: 0.05em;">
                                    <i class="bi bi-calendar-event mr-1 text-primary"></i> Casos por Fecha:
                                </span>
                                <div class="d-flex flex-wrap" id="modalSummaryDays"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Pacientes -->
                    <div class="table-responsive rounded border" style="max-height: 380px; overflow-y: auto; border-color: var(--border-color) !important; background: var(--bg-surface);">
                        <table class="table table-sm table-hover mb-0" style="font-size: 0.8rem;">
                            <thead style="background: var(--bg-subtle); color: var(--text-primary); position: sticky; top: 0; z-index: 5;">
                                <tr style="border-bottom: 1px solid var(--border-color);">
                                    <th style="width: 35px; border-color: var(--border-color);">#</th>
                                    <th style="width: 100px; border-color: var(--border-color);">Fecha</th>
                                    <th style="width: 110px; border-color: var(--border-color);">Expediente</th>
                                    <th style="width: 80px; border-color: var(--border-color);">Sexo</th>
                                    <th style="width: 80px; border-color: var(--border-color);">Edad</th>
                                    <th style="border-color: var(--border-color);">Diagnóstico</th>
                                    <th style="border-color: var(--border-color);">Médico</th>
                                </tr>
                            </thead>
                            <tbody id="modalAlertaTableBody">
                                <!-- Filas dinámicas -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer" style="background: var(--bg-subtle); border-top: 1px solid var(--border-color); padding: 0.75rem 1.25rem;">
                <button type="button" class="btn btn-secondary btn-sm font-weight-bold px-3" data-dismiss="modal" style="border-radius: var(--radius-sm, 6px);">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>
