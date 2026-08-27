<!-- Barra de Filtros y Acciones en Una Sola Fila -->
<div class="informe-filters-card mb-3 no-print" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md, 10px); padding: 0.65rem 1rem; box-shadow: var(--shadow-sm);">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <form id="filter-form" action="{{ route('informes.alerta-semanal') }}" method="GET" class="d-flex flex-wrap align-items-center gap-2 mb-0" style="margin: 0;">
            <!-- Año -->
            <div class="d-inline-flex align-items-center">
                <select name="ano" class="form-control form-control-sm ajax-filter font-weight-bold" style="width: 95px; height: 36px; background: var(--input-bg); border-color: var(--border-color); color: var(--text-primary); border-radius: var(--radius-sm, 6px); font-size: 0.85rem;">
                    @foreach($anos as $a)
                        <option value="{{ $a }}" {{ $a == $anoDefault ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Mes -->
            <div class="d-inline-flex align-items-center">
                <select name="mes" class="form-control form-control-sm ajax-filter font-weight-bold" style="width: 140px; height: 36px; background: var(--input-bg); border-color: var(--border-color); color: var(--text-primary); border-radius: var(--radius-sm, 6px); font-size: 0.85rem;">
                    <option value="">-- Todos los Meses --</option>
                    @foreach($meses as $m)
                        <option value="{{ $m }}" {{ strtoupper($m) == strtoupper($mesDefault) ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Semana Epidemiológica -->
            <div class="d-inline-flex align-items-center">
                <select name="se" class="form-control form-control-sm ajax-filter font-weight-bold" style="width: 135px; height: 36px; background: var(--input-bg); border-color: var(--border-color); color: var(--text-primary); border-radius: var(--radius-sm, 6px); font-size: 0.85rem;">
                    @foreach($semanas as $s)
                        <option value="{{ $s }}" {{ $s == $seDefault ? 'selected' : '' }}>Semana {{ $s }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Rango Epidemiológico -->
            <div class="d-inline-flex align-items-center px-2.5 ml-1" style="background: var(--bg-subtle); border: 1px solid var(--border-color); border-radius: var(--radius-sm, 6px); height: 36px; white-space: nowrap;">
                <span class="text-muted mr-1.5" style="font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em;">Rango:</span>
                <span class="font-weight-bold text-primary" style="font-size: 0.82rem;">{{ $fechaInfo['start'] }} al {{ $fechaInfo['end'] }}</span>
            </div>
        </form>

        <!-- Acciones -->
        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-subtle btn-sm" onclick="copyToExcel()" title="Copiar al Portapapeles" style="font-weight: 600; height: 36px; padding: 0 0.85rem; border-radius: var(--radius-sm, 6px);">
                <i class="bi bi-clipboard-check mr-1 text-primary"></i> Copiar
            </button>
            <button type="button" class="btn btn-subtle btn-sm" onclick="window.print()" title="Imprimir informe" style="font-weight: 600; height: 36px; padding: 0 0.85rem; border-radius: var(--radius-sm, 6px);">
                <i class="bi bi-printer mr-1"></i> Imprimir
            </button>
        </div>
    </div>
</div>

<!-- Tabla del Telegrama Semanal -->
<div class="informe-table-container custom-scrollbar mb-4" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md, 10px); overflow: auto; box-shadow: var(--shadow-sm);">
    <table class="table table-bordered table-alerta mb-0" style="width: 100%; border-collapse: separate; border-spacing: 0;">
        <thead>
            <tr style="background: var(--bg-subtle);">
                <th rowspan="2" class="sticky-header text-left" style="width: 38%; padding: 8px 12px; font-size: 0.82rem; font-weight: 700; color: var(--text-primary); border-color: var(--border-color); vertical-align: middle;">
                    ENFERMEDADES / EVENTOS
                </th>
                <th colspan="4" class="sticky-header text-center" style="padding: 6px 10px; font-size: 0.8rem; font-weight: 700; color: var(--text-primary); border-color: var(--border-color);">
                    NÚMERO DE CASOS POR GRUPO DE EDAD
                </th>
                <th rowspan="2" class="sticky-header text-center" style="width: 10%; padding: 8px 12px; font-size: 0.82rem; font-weight: 700; color: var(--text-primary); border-color: var(--border-color); vertical-align: middle;">
                    TOTAL
                </th>
            </tr>
            <tr style="background: var(--bg-subtle);">
                <th class="sticky-header text-center" style="width: 13%; padding: 6px 10px; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); border-color: var(--border-color);">&lt; 1 AÑO</th>
                <th class="sticky-header text-center" style="width: 13%; padding: 6px 10px; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); border-color: var(--border-color);">1 - 4 AÑOS</th>
                <th class="sticky-header text-center" style="width: 13%; padding: 6px 10px; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); border-color: var(--border-color);">5 - 14 AÑOS</th>
                <th class="sticky-header text-center" style="width: 13%; padding: 6px 10px; font-size: 0.75rem; font-weight: 600; color: var(--text-muted); border-color: var(--border-color);">15 Y + AÑOS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rowsDef as $idx => $row)
                <tr style="border-color: var(--border-color);">
                    <td class="text-left font-weight-600" style="padding: 6px 12px; color: var(--text-primary); font-size: 0.82rem; border-color: var(--border-color);">
                        {{ $row['label'] }}
                    </td>

                    @if($row['label'] === 'CADENA RED DE FRIO')
                        <td colspan="4" class="text-center cursor-pointer font-weight-bold select-none p-1" id="coldChainCell" onclick="toggleColdChain()" style="background: #22c55e; color: #fff; font-size: 0.82rem; border-color: var(--border-color);">
                            <span id="coldChainLabel">VERDE</span>
                        </td>
                        <td class="text-center text-muted" style="padding: 6px 10px; border-color: var(--border-color);">-</td>
                    @else
                        <!-- < 1 AÑO -->
                        <td class="text-center cell-clickable {{ $results[$idx]['less_1'] > 0 ? 'font-weight-bold text-primary' : 'text-muted' }}" 
                            onclick="fetchDetails({{ $idx }}, 'less_1')"
                            style="padding: 6px 10px; font-size: 0.88rem; cursor: pointer; border-color: var(--border-color); transition: background 0.15s;">
                            {{ $results[$idx]['less_1'] ?: '0' }}
                        </td>

                        <!-- 1 - 4 AÑOS -->
                        <td class="text-center cell-clickable {{ $results[$idx]['1_4'] > 0 ? 'font-weight-bold text-primary' : 'text-muted' }}" 
                            onclick="fetchDetails({{ $idx }}, '1_4')"
                            style="padding: 6px 10px; font-size: 0.88rem; cursor: pointer; border-color: var(--border-color); transition: background 0.15s;">
                            {{ $results[$idx]['1_4'] ?: '0' }}
                        </td>

                        <!-- 5 - 14 AÑOS -->
                        <td class="text-center cell-clickable {{ $results[$idx]['5_14'] > 0 ? 'font-weight-bold text-primary' : 'text-muted' }}" 
                            onclick="fetchDetails({{ $idx }}, '5_14')"
                            style="padding: 6px 10px; font-size: 0.88rem; cursor: pointer; border-color: var(--border-color); transition: background 0.15s;">
                            {{ $results[$idx]['5_14'] ?: '0' }}
                        </td>

                        <!-- 15 Y + AÑOS -->
                        <td class="text-center cell-clickable {{ $results[$idx]['15_plus'] > 0 ? 'font-weight-bold text-primary' : 'text-muted' }}" 
                            onclick="fetchDetails({{ $idx }}, '15_plus')"
                            style="padding: 6px 10px; font-size: 0.88rem; cursor: pointer; border-color: var(--border-color); transition: background 0.15s;">
                            {{ $results[$idx]['15_plus'] ?: '0' }}
                        </td>

                        <!-- TOTAL -->
                        <td class="text-center cell-clickable {{ $results[$idx]['total'] > 0 ? 'font-weight-bold' : 'text-muted' }}" 
                            onclick="fetchDetails({{ $idx }}, 'total')"
                            style="padding: 6px 10px; font-size: 0.88rem; cursor: pointer; background: var(--bg-subtle); color: var(--text-primary); border-color: var(--border-color); transition: background 0.15s;">
                            {{ $results[$idx]['total'] ?: '0' }}
                        </td>
                    @endif
                </tr>
            @endforeach
        </tbody>
    </table>

    <!-- Firmas para Impresión -->
    <div class="mt-4 p-4 d-flex justify-content-between" style="border-top: 1px dashed var(--border-color);">
        <div class="text-center" style="width: 40%;">
            <div style="border-top: 1px solid var(--text-primary); padding-top: 4px; font-size: 0.8rem; font-weight: 700; color: var(--text-primary);">
                FIRMA Y SELLO RESPONSABLE
            </div>
        </div>
        <div class="text-center" style="width: 40%;">
            <div style="border-top: 1px solid var(--text-primary); padding-top: 4px; font-size: 0.8rem; font-weight: 700; color: var(--text-primary);">
                FECHA DE ENTREGA: {{ now()->format('d/m/Y') }}
            </div>
        </div>
    </div>
</div>

<!-- Modal de Detalles de Pacientes -->
<div class="modal fade" id="modalAlertaDetalles" tabindex="-1" role="dialog" aria-labelledby="modalAlertaDetallesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content" style="background: var(--bg-surface); border: 1px solid var(--border-color); color: var(--text-primary); border-radius: var(--radius-md, 10px); box-shadow: var(--shadow-xl);">
            <div class="modal-header" style="background: var(--bg-subtle); border-bottom: 1px solid var(--border-color); padding: 0.85rem 1.25rem;">
                <div>
                    <h5 class="modal-title font-weight-bold mb-0" id="modalAlertaTitle" style="color: var(--text-primary); font-size: 1rem;"></h5>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        <span class="badge badge-primary px-2.5 py-0.5" id="modalAlertaRangeLabel" style="font-size: 0.72rem; font-weight: 700;"></span>
                        <span class="text-muted small">Desglose de pacientes</span>
                    </div>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: var(--text-primary); opacity: 0.7;">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body p-0">
                <!-- Bloques de Resumen -->
                <div class="d-flex flex-wrap p-3 gap-2 justify-content-center" style="background: var(--bg-subtle); border-bottom: 1px solid var(--border-color);">
                    <div class="p-2 text-center rounded border" style="background: var(--bg-surface); border-color: var(--border-color); min-width: 80px;">
                        <span class="d-block text-muted" style="font-size: 0.7rem; font-weight: 600;">TOTAL</span>
                        <span class="font-weight-bold" style="font-size: 1.3rem; color: var(--color-primary);" id="modalSummaryTotal">0</span>
                    </div>
                    <div class="p-2 text-center rounded border flex-grow-1" style="background: var(--bg-surface); border-color: var(--border-color);">
                        <span class="d-block text-muted mb-1" style="font-size: 0.7rem; font-weight: 600;">POR DÍA</span>
                        <div class="d-flex justify-content-center gap-2 flex-wrap" id="modalSummaryDays"></div>
                    </div>
                    <div class="p-2 text-center rounded border flex-grow-1" style="background: var(--bg-surface); border-color: var(--border-color);">
                        <span class="d-block text-muted mb-1" style="font-size: 0.7rem; font-weight: 600;">POR EDAD</span>
                        <div class="d-flex justify-content-center gap-2 flex-wrap" id="modalSummaryRanges"></div>
                    </div>
                </div>

                <!-- Tabla de Pacientes -->
                <div class="table-responsive p-3" style="max-height: 400px; overflow-y: auto;">
                    <table class="table table-sm table-hover mb-0" style="font-size: 0.8rem;">
                        <thead>
                            <tr style="background: var(--bg-subtle); color: var(--text-muted);">
                                <th>Fecha</th>
                                <th>Expediente</th>
                                <th>Sexo</th>
                                <th>Edad</th>
                                <th>Diagnóstico</th>
                                <th>Médico</th>
                            </tr>
                        </thead>
                        <tbody id="modalAlertaTableBody">
                            <!-- Renderizado dinámico -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer" style="background: var(--bg-subtle); border-top: 1px solid var(--border-color); padding: 0.75rem 1.25rem;">
                <button type="button" class="btn btn-sm btn-secondary font-weight-bold" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
