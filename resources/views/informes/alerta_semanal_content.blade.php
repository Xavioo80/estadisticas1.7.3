<!-- Barra de Filtros y Acciones en Una Sola Fila Horizontal -->
<div class="filter-container no-print mb-2"
    style="background: var(--bg-surface) !important; border: 1px solid var(--border-color) !important; border-radius: var(--radius-md, 10px) !important; padding: 0.5rem 0.85rem !important; display: flex !important; flex-direction: row !important; align-items: center !important; justify-content: space-between !important; flex-wrap: nowrap !important; gap: 8px !important; box-shadow: var(--shadow-sm) !important; width: 100% !important;">
    <form id="filter-form" action="{{ route('informes.alerta-semanal') }}" method="GET"
        style="display: flex !important; flex-direction: row !important; align-items: center !important; flex-wrap: nowrap !important; gap: 8px !important; margin: 0 !important; flex: 1 1 auto !important; min-width: 0 !important;">
        <!-- Año -->
        <div style="width: 95px !important; min-width: 95px !important; flex-shrink: 0 !important;">
            <select name="ano" class="filter-select w-full ajax-filter"
                style="width: 100% !important; height: 34px !important; font-weight: 700 !important; font-size: 0.82rem !important;">
                @foreach($anos as $a)
                    <option value="{{ $a }}" {{ $a == $anoDefault ? 'selected' : '' }}>{{ $a }}</option>
                @endforeach
            </select>
        </div>

        <!-- Mes -->
        <div style="width: 140px !important; min-width: 140px !important; flex-shrink: 0 !important;">
            <select name="mes" class="filter-select w-full ajax-filter"
                style="width: 100% !important; height: 34px !important; font-weight: 700 !important; font-size: 0.82rem !important;">
                <option value="">-- Todos los Meses --</option>
                @foreach($meses as $m)
                    <option value="{{ $m }}" {{ strtoupper($m) == strtoupper($mesDefault) ? 'selected' : '' }}>{{ $m }}
                    </option>
                @endforeach
            </select>
        </div>

        <!-- Semana Epidemiológica -->
        <div style="width: 135px !important; min-width: 135px !important; flex-shrink: 0 !important;">
            <select name="se" class="filter-select w-full ajax-filter"
                style="width: 100% !important; height: 34px !important; font-weight: 700 !important; font-size: 0.82rem !important;">
                @foreach($semanas as $s)
                    <option value="{{ $s }}" {{ $s == $seDefault ? 'selected' : '' }}>Semana {{ $s }}</option>
                @endforeach
            </select>
        </div>

        <!-- Rango Epidemiológico Badge -->
        <div style="display: inline-flex !important; flex-direction: row !important; align-items: center !important; justify-content: center !important; flex-wrap: nowrap !important; height: 34px !important; padding: 0 14px !important; background: var(--bg-subtle) !important; border: 1px solid var(--border-color) !important; border-radius: 9999px !important; white-space: nowrap !important; flex-shrink: 0; box-shadow: 0 1px 2px rgba(0,0,0,0.03);">
            <i class="bi bi-calendar3 text-primary" style="font-size: 0.85rem; margin-right: 7px; display: inline-flex; align-items: center;"></i>
            <span class="text-muted font-weight-bold" style="font-size: 0.70rem; text-transform: uppercase; letter-spacing: 0.06em; margin-right: 6px; display: inline-flex; align-items: center;">RANGO:</span>
            <span class="font-weight-bold text-primary" style="font-size: 0.82rem; display: inline-flex; align-items: center;">{{ $fechaInfo['start'] }} <span class="text-muted font-weight-normal" style="margin: 0 4px; font-size: 0.75rem;">al</span> {{ $fechaInfo['end'] }}</span>
        </div>
    </form>

    <!-- Acciones -->
    <div
        style="display: flex !important; flex-direction: row !important; align-items: center !important; flex-shrink: 0 !important; gap: 6px !important;">
        <button type="button" class="btn btn-subtle btn-sm" onclick="copyToExcel()" title="Copiar al Portapapeles"
            style="font-weight: 600; height: 34px; padding: 0 0.85rem; border-radius: 8px; display: inline-flex; align-items: center; gap: 4px;">
            <i class="bi bi-clipboard-check text-primary"></i> <span>Copiar</span>
        </button>
        <button type="button" class="btn btn-subtle btn-sm" onclick="window.print()" title="Imprimir informe"
            style="font-weight: 600; height: 34px; padding: 0 0.85rem; border-radius: 8px; display: inline-flex; align-items: center; gap: 4px;">
            <i class="bi bi-printer"></i> <span>Imprimir</span>
        </button>
    </div>
</div>

<!-- Tabla del Telegrama Semanal -->
<div class="informe-table-container custom-scrollbar mb-4"
    style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md, 10px); overflow: auto; box-shadow: var(--shadow-sm); width: 100%;">
    <table class="table table-bordered table-alerta mb-0"
        style="width: 100%; border-collapse: separate; border-spacing: 0;">
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
                <th rowspan="2" class="sticky-header text-left"
                    style="padding: 7px 10px; font-size: 0.80rem; font-weight: 700; color: var(--text-primary); border-color: var(--border-color); vertical-align: middle;">
                    ENFERMEDADES / EVENTOS
                </th>
                <th colspan="4" class="sticky-header text-center"
                    style="padding: 5px 8px; font-size: 0.78rem; font-weight: 700; color: var(--text-primary); border-color: var(--border-color);">
                    NÚMERO DE CASOS POR GRUPO DE EDAD
                </th>
                <th rowspan="2" class="sticky-header text-center"
                    style="padding: 7px 10px; font-size: 0.80rem; font-weight: 700; color: var(--text-primary); border-color: var(--border-color); vertical-align: middle;">
                    TOTAL
                </th>
            </tr>
            <tr style="background: var(--bg-subtle);">
                <th class="sticky-header text-center"
                    style="padding: 5px 6px; font-size: 0.73rem; font-weight: 600; color: var(--text-muted); border-color: var(--border-color);">
                    &lt; 1 AÑO</th>
                <th class="sticky-header text-center"
                    style="padding: 5px 6px; font-size: 0.73rem; font-weight: 600; color: var(--text-muted); border-color: var(--border-color);">
                    1 - 4 AÑOS</th>
                <th class="sticky-header text-center"
                    style="padding: 5px 6px; font-size: 0.73rem; font-weight: 600; color: var(--text-muted); border-color: var(--border-color);">
                    5 - 14 AÑOS</th>
                <th class="sticky-header text-center"
                    style="padding: 5px 6px; font-size: 0.73rem; font-weight: 600; color: var(--text-muted); border-color: var(--border-color);">
                    15 Y + AÑOS</th>
            </tr>
        </thead>
        <tbody>
            @foreach($rowsDef as $idx => $row)
                <tr style="border-color: var(--border-color);">
                    <td class="text-left font-weight-600"
                        style="padding: 4px 10px; color: var(--text-primary); font-size: 0.80rem; border-color: var(--border-color);">
                        {{ $row['label'] }}
                    </td>

                    @if($row['label'] === 'CADENA RED DE FRIO')
                        <td colspan="4" class="text-center cursor-pointer font-weight-bold select-none p-1" id="coldChainCell"
                            onclick="toggleColdChain()"
                            style="background: #22c55e; color: #fff; font-size: 0.80rem; border-color: var(--border-color);">
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
            <div
                style="border-top: 1px solid var(--text-primary); padding-top: 4px; font-size: 0.78rem; font-weight: 700; color: var(--text-primary);">
                FIRMA Y SELLO RESPONSABLE
            </div>
        </div>
        <div class="text-center" style="width: 40%;">
            <div
                style="border-top: 1px solid var(--text-primary); padding-top: 4px; font-size: 0.78rem; font-weight: 700; color: var(--text-primary);">
                FECHA DE ENTREGA: {{ now()->format('d/m/Y') }}
            </div>
        </div>
    </div>
</div>

<!-- Modal de Detalles de Pacientes Alerta Semanal (Diseño Moderno) -->
<div class="modal fade" id="modalAlertaDetalles" tabindex="-1" role="dialog" aria-labelledby="modalAlertaDetallesLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content" style="background: var(--bg-surface); border: 1px solid var(--border-color); color: var(--text-primary); border-radius: 16px; box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.35); overflow: hidden;">
            <!-- Modal Header -->
            <div class="modal-header" style="background: var(--bg-subtle); border-bottom: 1px solid var(--border-color); padding: 1rem 1.4rem; display: flex; align-items: center; justify-content: space-between;">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 42px; height: 42px; border-radius: 12px; background: linear-gradient(135deg, #4f46e5, #6366f1); color: #ffffff; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; box-shadow: 0 4px 12px rgba(79, 70, 229, 0.35);">
                        <i class="bi bi-shield-check"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-weight-bold mb-0" id="modalAlertaTitle" style="color: var(--text-primary); font-size: 1.12rem; letter-spacing: -0.01em;">
                            Detalle de Pacientes
                        </h5>
                        <div class="d-flex align-items-center mt-1" style="gap: 8px;">
                            <span class="badge" id="modalAlertaSubtitleBadge" style="background: var(--color-primary-light, rgba(99, 102, 241, 0.12)); color: var(--color-primary); font-size: 0.72rem; font-weight: 700; padding: 2px 8px; border-radius: 6px; border: 1px solid rgba(99, 102, 241, 0.2);">
                                Grupo: -
                            </span>
                            <span class="text-muted small" style="font-size: 0.76rem;">Desglose de atenciones del período</span>
                        </div>
                    </div>
                </div>
                <div class="d-flex align-items-center" style="gap: 12px;">
                    <span class="badge badge-primary px-3 py-1.5" id="modalAlertaTotalBadge" style="font-size: 0.82rem; font-weight: 700; border-radius: 20px; box-shadow: 0 2px 6px rgba(79, 70, 229, 0.25);">
                        0 Casos
                    </span>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: var(--text-primary); opacity: 0.6; font-size: 1.4rem; padding: 6px; margin: -6px; border: none; background: transparent; outline: none; transition: opacity 0.15s;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>

            <!-- Modal Body -->
            <div class="modal-body p-3.5" style="background: var(--bg-surface); color: var(--text-primary); overflow-y: auto;">
                <!-- Loader -->
                <div id="modalAlertaLoader" class="py-5 text-center" style="display: none;">
                    <div class="spinner-border text-primary" role="status" style="width: 2.4rem; height: 2.4rem;">
                        <span class="sr-only">Cargando...</span>
                    </div>
                    <p class="small text-muted mt-2 font-weight-bold text-uppercase" style="letter-spacing: 0.06em; font-size: 0.75rem;">Consultando registros...</p>
                </div>

                <div id="modalAlertaBodyContent">
                    <!-- Resumen KPIs en una sola barra moderna y unificada -->
                    <div class="kpi-summary-bar mb-3 p-2.5 px-3 rounded-lg border" style="background: var(--bg-subtle); border-color: var(--border-color) !important; border-radius: 12px; display: flex; flex-wrap: wrap; align-items: center; justify-content: flex-start; gap: 12px;">
                        <!-- Bloque Izquierdo: Total General -->
                        <div class="d-flex flex-column justify-content-center" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: 10px; padding: 6px 14px; gap: 4px; box-shadow: 0 1px 3px rgba(0,0,0,0.04); flex-shrink: 0;">
                            <div class="d-flex align-items-center" style="gap: 8px;">
                                <div style="width: 28px; height: 28px; border-radius: 8px; background: linear-gradient(135deg, #4f46e5, #6366f1); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.95rem; flex-shrink: 0; box-shadow: 0 2px 5px rgba(79, 70, 229, 0.3);">
                                    <i class="bi bi-people-fill"></i>
                                </div>
                                <span style="font-size: 1.35rem; font-weight: 800; color: var(--color-primary); line-height: 1;" id="modalSummaryTotal">0</span>
                            </div>
                            <span style="font-size: 0.62rem; color: var(--text-muted); text-transform: uppercase; font-weight: 700; letter-spacing: 0.06em; line-height: 1;">TOTAL CASOS</span>
                        </div>

                        <!-- Divisor vertical sutil -->
                        <div style="width: 1px; height: 32px; background: var(--border-color); flex-shrink: 0;"></div>

                        <!-- Bloque Derecho: Fechas (Flex row) -->
                        <div class="d-flex align-items-center flex-wrap flex-grow-1" style="gap: 8px; min-width: 0;">
                            <div class="d-flex align-items-center" style="gap: 5px; flex-shrink: 0;">
                                <i class="bi bi-calendar3-range text-primary" style="font-size: 0.78rem;"></i>
                                <span class="text-muted text-uppercase font-weight-bold" style="font-size: 0.65rem; letter-spacing: 0.05em;">DISTRIBUCIÓN:</span>
                            </div>
                            <div class="d-flex flex-wrap align-items-center flex-grow-1" id="modalSummaryDays" style="gap: 8px;">
                                <!-- Date cards -->
                            </div>
                        </div>
                    </div>

                    <!-- Tabla de Pacientes Moderna (Sin bordes duros) -->
                    <div class="table-responsive rounded-lg border" style="max-height: 360px; overflow-y: auto; border-color: var(--border-color) !important; border-radius: 12px; background: var(--bg-surface); box-shadow: var(--shadow-xs);">
                        <table class="table mb-0 modal-patient-table" style="width: 100%; border-collapse: collapse; font-size: 0.82rem;">
                            <thead style="background: var(--bg-subtle); position: sticky; top: 0; z-index: 5;">
                                <tr style="border-bottom: 2px solid var(--border-color);">
                                    <th style="padding: 10px 12px; width: 45px; text-align: center; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); border: none;">#</th>
                                    <th style="padding: 10px 12px; width: 110px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); border: none;">Fecha</th>
                                    <th style="padding: 10px 12px; width: 110px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); border: none;">Expediente</th>
                                    <th style="padding: 10px 12px; width: 75px; text-align: center; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); border: none;">Sexo</th>
                                    <th style="padding: 10px 12px; width: 85px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); border: none;">Edad</th>
                                    <th style="padding: 10px 12px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); border: none;">Diagnóstico</th>
                                    <th style="padding: 10px 12px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-muted); border: none;">Médico Tratante</th>
                                </tr>
                            </thead>
                            <tbody id="modalAlertaTableBody">
                                <!-- Filas dinámicas generadas en JS -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Modal Footer -->
            <div class="modal-footer" style="background: var(--bg-subtle); border-top: 1px solid var(--border-color); padding: 0.75rem 1.4rem; display: flex; align-items: center; justify-content: space-between;">
                <span class="text-muted small" style="font-size: 0.75rem;">
                    <i class="bi bi-info-circle mr-1"></i> Haga clic en Cerrar para regresar al informe.
                </span>
                <button type="button" class="btn btn-subtle btn-sm font-weight-bold px-4" data-dismiss="modal" style="border-radius: 8px; height: 34px;">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>