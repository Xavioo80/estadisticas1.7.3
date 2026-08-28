@php
    \Carbon\Carbon::setLocale('es');
    $fechasDetalle = [];
    foreach($dayHeaders as $h) {
        $dateObj = \Carbon\Carbon::createFromFormat('d/m/Y', $h);
        $fechasDetalle[] = [
            'formatted' => $h, 
            'day' => $dateObj->day, 
            'initial' => strtoupper(substr($dateObj->isoFormat('dddd'), 0, 1))
        ];
    }
@endphp

<!-- Barra de Filtros en Una Sola Fila Horizontal Estricta -->
<div class="filter-container no-print mb-2"
    style="background: var(--bg-surface) !important; border: 1px solid var(--border-color) !important; border-radius: var(--radius-md, 10px) !important; padding: 0.5rem 0.85rem !important; display: flex !important; flex-direction: row !important; align-items: center !important; justify-content: space-between !important; flex-wrap: nowrap !important; gap: 8px !important; box-shadow: var(--shadow-sm) !important; width: 100% !important; overflow-x: auto !important;">
    <form id="filter-form" action="{{ route('informes.sm107') }}" method="GET"
        style="display: flex !important; flex-direction: row !important; align-items: center !important; justify-content: space-between !important; flex-wrap: nowrap !important; gap: 8px !important; margin: 0 !important; width: 100% !important; min-width: 0 !important;">
        <input type="hidden" name="view" id="view-input" value="{{ $viewType }}">
        
        <div style="display: flex !important; flex-direction: row !important; align-items: center !important; flex-wrap: nowrap !important; gap: 8px !important; flex-shrink: 0 !important;">
            <!-- Alternador Anverso / Reverso -->
            <div style="display: inline-flex !important; flex-direction: row !important; align-items: center !important; background: var(--bg-subtle) !important; padding: 2px !important; border-radius: var(--radius-sm, 8px) !important; border: 1px solid var(--border-color) !important; flex-shrink: 0 !important;">
                <button type="button" class="btn-toggle-view px-3 py-1 text-[11px] font-bold rounded transition-all {{ $viewType == 'anversa' ? 'btn-primary' : 'btn-subtle' }}" data-view="anversa" style="height: 28px !important; font-size: 0.75rem !important; font-weight: 700 !important; padding: 0 12px !important;">
                    ANVERSA
                </button>
                <button type="button" class="btn-toggle-view px-3 py-1 text-[11px] font-bold rounded transition-all {{ $viewType == 'reversa' ? 'btn-primary' : 'btn-subtle' }}" data-view="reversa" style="height: 28px !important; font-size: 0.75rem !important; font-weight: 700 !important; padding: 0 12px !important;">
                    REVERSA
                </button>
            </div>

            <!-- Separador vertical -->
            <div style="height: 22px !important; width: 1px !important; background: var(--border-color) !important; flex-shrink: 0 !important; margin: 0 2px !important;"></div>

            <!-- Año -->
            <div style="width: 85px !important; min-width: 85px !important; flex-shrink: 0 !important;">
                <select name="ano" class="filter-select ajax-filter"
                    style="width: 100% !important; height: 34px !important; font-weight: 700 !important; font-size: 0.82rem !important;">
                    @foreach($anos as $a)
                        <option value="{{ $a }}" {{ $a == $ano ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Mes -->
            <div style="width: 130px !important; min-width: 130px !important; flex-shrink: 0 !important;">
                <select name="mes" class="filter-select ajax-filter"
                    style="width: 100% !important; height: 34px !important; font-weight: 700 !important; font-size: 0.82rem !important;">
                    @foreach($meses as $m)
                        <option value="{{ $m }}" {{ strtoupper($mes) == $m ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Jornada -->
            <div style="width: 165px !important; min-width: 150px !important; flex-shrink: 0 !important;">
                <select name="jornada" class="filter-select ajax-filter" title="Jornada"
                    style="width: 100% !important; height: 34px !important; font-weight: 700 !important; font-size: 0.82rem !important;">
                    <option value="TODAS">TODAS LAS JORNADAS</option>
                    @foreach($jornadas as $j)
                        <option value="{{ $j }}" {{ $jornada == $j ? 'selected' : '' }}>{{ $j }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Separador -->
            <div style="height: 22px !important; width: 1px !important; background: var(--border-color) !important; flex-shrink: 0 !important; margin: 0 2px !important;"></div>

            <span class="badge badge-subtle font-weight-bold" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary); white-space: nowrap;">
                SM1-07 ({{ $viewType == 'anversa' ? 'ANVERSA' : 'REVERSA' }})
            </span>
        </div>

        <!-- Acciones a la derecha -->
        <div style="display: flex !important; flex-direction: row !important; align-items: center !important; flex-wrap: nowrap !important; gap: 6px !important; flex-shrink: 0 !important; margin-left: auto !important;">
            <button type="button" id="btn-fullscreen" onclick="toggleFullScreen()" class="btn-action-fullscreen" title="Pantalla Completa">
                <i class="bi bi-arrows-fullscreen" id="fullScreenIcon"></i>
            </button>
            <button type="button" onclick="window.print()" class="btn-action-print" title="Imprimir">
                <i class="bi bi-printer"></i>
            </button>
        </div>
    </form>
</div>

{{-- VISTA DE PANTALLA --}}
<div class="print:hidden" style="flex: 1 1 0% !important; min-height: 0 !important; height: calc(100vh - 145px) !important; max-height: calc(100vh - 145px) !important; display: flex !important; flex-direction: column !important; overflow: hidden !important; width: 100% !important;">
    {{-- Banner de Notificación de Fechas Nulas --}}
    @if(isset($nullDatesCount) && $nullDatesCount > 0)
        <div class="alert alert-warning shadow-sm mb-2 d-flex align-items-center" style="border-radius: var(--radius-md); background: rgba(245, 158, 11, 0.1); border: 1px solid var(--color-warning); color: var(--text-primary); padding: 0.5rem 0.85rem; flex-shrink: 0 !important;">
            <i class="bi bi-exclamation-triangle-fill mr-2 text-warning" style="font-size: 1.25rem;"></i>
            <div style="font-size: 0.8rem;">
                <strong style="color: var(--color-warning);">Aviso: Registros con Fecha Indefinida.</strong> Se han detectado {{ $nullDatesCount }} registros sin fecha asignada, contabilizados en la última columna.
            </div>
        </div>
    @endif

    {{-- Banner de Atenciones en Menores de 5 por Profesiones no Especializadas --}}
    @if(isset($invalidUnderFive) && count($invalidUnderFive) > 0)
        <div class="alert alert-danger shadow-sm mb-2 d-flex flex-column" style="border-radius: var(--radius-md); background: rgba(239, 68, 68, 0.1); border: 1px solid var(--color-danger); color: var(--text-primary); padding: 0.5rem 0.85rem; flex-shrink: 0 !important;">
            <div class="d-flex align-items-center mb-1">
                <i class="bi bi-shield-fill-x mr-2 text-danger" style="font-size: 1.25rem;"></i>
                <div style="font-size: 0.8rem;">
                    <strong class="text-danger">Auditoría: Atenciones SM en Menores de 5 Años (No Especializadas).</strong> Se detectaron {{ count($invalidUnderFive) }} registros de menores de 5 atendidos por personal diferente a Psicología o Psiquiatría.
                </div>
            </div>
            <div class="table-responsive mt-1 custom-scrollbar" style="max-height: 120px;">
                <table class="table table-sm table-bordered mb-0" style="font-size: 0.72rem; background: var(--bg-surface);">
                    <thead style="background: var(--bg-subtle);">
                        <tr>
                            <th>Fecha</th>
                            <th>Paciente/Reg</th>
                            <th>Edad</th>
                            <th>Profesión</th>
                            <th>Diagnóstico Detectado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invalidUnderFive as $inv)
                            <tr>
                                <td>{{ $inv['fecha'] }}</td>
                                <td>{{ $inv['paciente'] }}</td>
                                <td>{{ $inv['edad'] }}</td>
                                <td class="font-weight-bold text-danger">{{ $inv['profesion'] }}</td>
                                <td>{{ $inv['diagnostico'] }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <!-- Contenedor Principal con Scroll X e Y desbloqueado y sticky bloqueado -->
    <div id="sm107-table-wrapper" class="card shadow-sm border-0" style="flex: 1 1 0% !important; min-height: 0 !important; height: 100% !important; display: flex !important; flex-direction: column !important; overflow: hidden !important; border-radius: var(--radius-md); background: var(--bg-surface); border: 1px solid var(--border-color) !important;">
        <div class="card-body p-0 table-responsive custom-scrollbar" style="flex: 1 1 0% !important; min-height: 0 !important; height: 100% !important; max-height: 100% !important; overflow-x: auto !important; overflow-y: auto !important; position: relative !important;">
            <table class="table table-bordered table-sm table-hover mb-0 text-center align-middle" id="screenTable" style="border-collapse: separate; border-spacing: 0; font-size: 0.78rem; background: var(--bg-surface); width: 100%; min-width: 1200px;">
                <thead>
                    <tr>
                        <th class="sticky-col-first align-middle text-left py-2 px-3" style="width: 260px; min-width: 260px; background-color: var(--bg-surface-elevated, #1e293b) !important; color: var(--text-primary); border-right: 1px solid var(--border-color); border-bottom: 2px solid var(--border-color);">
                            <div class="font-weight-bold text-uppercase letter-spacing-1" style="font-size: 0.74rem;">CONCEPTO / ATENCIONES</div>
                        </th>
                        @if($viewType == 'anversa')
                            <th class="sticky-col-second align-middle text-center py-2" style="width: 42px; min-width: 42px; background-color: var(--bg-surface-elevated, #1e293b) !important; color: var(--text-primary); border-right: 1px solid var(--border-color); border-bottom: 2px solid var(--border-color); font-size: 0.74rem;">
                                CN
                            </th>
                        @endif
                        
                        @foreach($fechasDetalle as $f)
                            <th class="text-center p-0" style="width: 32px; min-width: 32px; background-color: var(--bg-surface-elevated, #1e293b) !important; color: var(--text-primary); border-right: 1px solid var(--border-color); border-bottom: 2px solid var(--border-color); vertical-align: middle;">
                                <div style="display: flex !important; flex-direction: column !important; align-items: center !important; justify-content: center !important; padding: 4px 1px !important; line-height: 1 !important; width: 100% !important;">
                                    <span style="font-size: 0.84rem !important; font-weight: 800 !important; color: var(--text-primary) !important; line-height: 1.1 !important; display: block !important;">{{ $f['day'] }}</span>
                                    <span style="font-size: 0.65rem !important; font-weight: 700 !important; color: var(--text-muted) !important; text-transform: uppercase !important; line-height: 1 !important; margin-top: 3px !important; display: block !important;">{{ $f['initial'] }}</span>
                                </div>
                            </th>
                        @endforeach

                        <th class="text-center align-middle py-2 px-2" style="width: 60px; min-width: 60px; background-color: rgba(77, 124, 254, 0.25) !important; color: var(--color-primary); font-weight: 800; border-right: 1px solid var(--border-color); border-bottom: 2px solid var(--border-color); font-size: 0.8rem;">
                            TOTAL
                        </th>
                    </tr>
                </thead>
                <tbody>
                    @if($viewType == 'anversa')
                        @php
                            $ranges = [
                                1 => ['label' => '1. MENOR 1 AÑO', 'type' => 'N'],
                                2 => ['label' => '1. MENOR 1 AÑO', 'type' => 'S'],
                                3 => ['label' => '2. 1 - 4 AÑOS', 'type' => 'N'],
                                4 => ['label' => '2. 1 - 4 AÑOS', 'type' => 'S'],
                                5 => ['label' => '3. 5 A 9 AÑOS', 'type' => 'N'],
                                6 => ['label' => '3. 5 A 9 AÑOS', 'type' => 'S'],
                                7 => ['label' => '4. 10 A 14 AÑOS', 'type' => 'N'],
                                8 => ['label' => '4. 10 A 14 AÑOS', 'type' => 'S'],
                                9 => ['label' => '5. 15 A 19 AÑOS', 'type' => 'N'],
                                10 => ['label' => '5. 15 A 19 AÑOS', 'type' => 'S'],
                                11 => ['label' => '6. 20 A 24 AÑOS', 'type' => 'N'],
                                12 => ['label' => '6. 20 A 24 AÑOS', 'type' => 'S'],
                                13 => ['label' => '7. 25 a 39 AÑOS', 'type' => 'N'],
                                14 => ['label' => '7. 25 a 39 AÑOS', 'type' => 'S'],
                                15 => ['label' => '8. 40 A 59 AÑOS', 'type' => 'N'],
                                16 => ['label' => '8. 40 A 59 AÑOS', 'type' => 'S'],
                                17 => ['label' => '9. 60 Y MAS', 'type' => 'N'],
                                18 => ['label' => '9. 60 Y MAS', 'type' => 'S'],
                            ];
                        @endphp
                        @foreach($ranges as $idx => $r)
                            <tr>
                                <td class="sticky-col-first text-left px-3 font-weight-bold" style="background: var(--bg-surface); color: var(--text-primary); font-size: 0.76rem;">
                                    {{ $r['label'] }}
                                </td>
                                <td class="sticky-col-second text-center" style="background: var(--bg-surface);">
                                    <span class="badge {{ $r['type'] === 'N' ? 'badge-subtle-primary' : 'badge-subtle-secondary' }}" style="font-size: 0.68rem; padding: 2px 6px;">
                                        {{ $r['type'] }}
                                    </span>
                                </td>
                                @php $rowTotal = 0; @endphp
                                @foreach($fechasDetalle as $f)
                                    @php $val = $anversaData[$idx][$f['formatted']] ?? 0; $rowTotal += $val; @endphp
                                    <td class="{{ $val > 0 ? 'font-weight-bold text-primary cell-clickable' : 'text-muted' }}"
                                        style="font-size: 0.78rem; cursor: {{ $val > 0 ? 'pointer' : 'default' }}; {{ $val > 0 ? 'background: rgba(77, 124, 254, 0.06);' : '' }}"
                                        @if($val > 0) onclick="showDetails('{{ $idx }}', '{{ $f['formatted'] }}')" title="Ver detalles ({{ $val }} atenciones)" @endif>
                                        {{ $val ?: '·' }}
                                    </td>
                                @endforeach
                                <td class="font-weight-bold text-center {{ $rowTotal > 0 ? 'cell-clickable' : '' }}" 
                                    style="background: rgba(77, 124, 254, 0.08); color: var(--color-primary); font-size: 0.82rem; cursor: {{ $rowTotal > 0 ? 'pointer' : 'default' }};"
                                    @if($rowTotal > 0) onclick="showDetails('{{ $idx }}', null)" title="Ver total mensual ({{ $rowTotal }})" @endif>
                                    {{ $rowTotal }}
                                </td>
                            </tr>
                        @endforeach
                    @else
                        {{-- REVERSA SCREEN --}}
                        @php
                            $actLabels = [
                                19 => 'ENTREVISTA V.D', 20 => 'ENTREVISTA PSICOLÓGICA', 21 => 'INTERVENSIÓN En CRISIS',
                                22 => 'FICHA DE VIGILANCIA EPIDEMIOLÓGICA.', 23 => 'PSICOTERAPIA INDIVIDUAL',
                                24 => 'PSICOTERAPIA EN GRUPO', 25 => 'NÚMERO DE PARTICIPANTES',
                                26 => 'PSICOTERAPIA DE FAMILIA', 27 => 'NÚMERO DE PARTICIPANTES',
                                28 => 'REUNIÓN COORDINACIÓN GRUPOS DE APOYO', 29 => 'NÚMERO DE PARTICIPANTES',
                                30 => 'PRUEBAS PSICOLÓGICAS APLICADAS', 31 => 'REUNIÓN DE TRABAJO COMUNITARIO',
                                32 => 'NÚMERO DE PARTICIPANTES', 33 => 'CAPACITACIONES BRINDADAS',
                                34 => 'NÚMERO DE PARTICIPANTES', 35 => 'CAPACITACIONES RECIBIDAS',
                                36 => 'CHARLAS BRINDADAS', 37 => 'NÚMERO DE PARTICIPANTES',
                                38 => 'ORGANIZACIÓN Y FORTALECIMIENTO DE GRUPO', 39 => 'CONSEJERIA VIH/SIDA',
                                40 => 'REFERENCIAS RECIBIDAS', 41 => 'REFERENCIAS ENVIADAS',
                                42 => 'TAMIZAJE (+)', 43 => 'TAMIZAJE (-)'
                            ];
                        @endphp
                        @foreach($actLabels as $code => $label)
                            <tr>
                                <td class="sticky-col-first text-left px-3 font-weight-bold" style="background: var(--bg-surface); color: var(--text-primary); font-size: 0.76rem;">
                                    {{ $label }}
                                </td>
                                @php $rowTotal = 0; @endphp
                                @foreach($fechasDetalle as $f)
                                    @php $val = $reversaActivities[$code][$f['formatted']] ?? 0; $rowTotal += $val; @endphp
                                    <td class="{{ $val > 0 ? 'font-weight-bold text-primary cell-clickable' : 'text-muted' }}"
                                        style="font-size: 0.78rem; cursor: {{ $val > 0 ? 'pointer' : 'default' }}; {{ $val > 0 ? 'background: rgba(77, 124, 254, 0.06);' : '' }}"
                                        @if($val > 0) onclick="showDetails('{{ $code }}', '{{ $f['formatted'] }}')" title="Ver detalles ({{ $val }})" @endif>
                                        {{ $val ?: '·' }}
                                    </td>
                                @endforeach
                                <td class="font-weight-bold text-center {{ $rowTotal > 0 ? 'cell-clickable' : '' }}" 
                                    style="background: rgba(77, 124, 254, 0.08); color: var(--color-primary); font-size: 0.82rem; cursor: {{ $rowTotal > 0 ? 'pointer' : 'default' }};"
                                    @if($rowTotal > 0) onclick="showDetails('{{ $code }}', null)" title="Ver total mensual ({{ $rowTotal }})" @endif>
                                    {{ $rowTotal }}
                                </td>
                            </tr>
                        @endforeach
                    @endif
                </tbody>
                <tfoot>
                    <tr>
                        <td class="sticky-col-first text-right pr-3 font-weight-bold" style="background-color: var(--bg-surface-elevated, #1e293b) !important; color: var(--text-primary); font-size: 0.8rem;">TOTALES</td>
                        @if($viewType == 'anversa')
                            <td class="sticky-col-second text-center" style="background-color: var(--bg-surface-elevated, #1e293b) !important;"></td>
                        @endif
                        @php $grandTotal = 0; @endphp
                        @foreach($fechasDetalle as $f)
                            @php 
                                $colSum = 0;
                                if($viewType == 'anversa') {
                                    foreach($ranges as $idx => $r) {
                                        $colSum += ($anversaData[$idx][$f['formatted']] ?? 0);
                                    }
                                } else {
                                    foreach($actLabels as $code => $label) {
                                        $colSum += ($reversaActivities[$code][$f['formatted']] ?? 0);
                                    }
                                }
                                $grandTotal += $colSum;
                            @endphp
                            <td class="text-center font-weight-bold {{ $colSum > 0 ? 'text-primary cell-clickable' : 'text-muted' }}"
                                style="background-color: var(--bg-surface-elevated, #1e293b) !important; font-size: 0.8rem; cursor: {{ $colSum > 0 ? 'pointer' : 'default' }};"
                                @if($colSum > 0) onclick="showDetails('all', '{{ $f['formatted'] }}')" @endif>
                                {{ $colSum ?: '0' }}
                            </td>
                        @endforeach
                        <td class="text-center font-weight-bold" style="background-color: var(--color-primary) !important; color: #ffffff !important; font-size: 0.88rem;">
                            {{ $grandTotal }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

<style>
    /* =====================================================
       SM107 — STICKY HEADER, STICKY TOTALS FOOTER & BORDERS
       ===================================================== */
    #sm107-table-wrapper {
        border: 1px solid var(--border-color) !important;
        box-shadow: var(--shadow-sm);
        flex: 1 1 0% !important;
        min-height: 0 !important;
        height: 100% !important;
        display: flex !important;
        flex-direction: column !important;
        overflow: hidden !important;
        background: var(--bg-surface) !important;
    }

    #sm107-table-wrapper .card-body {
        flex: 1 1 0% !important;
        min-height: 0 !important;
        height: 100% !important;
        max-height: 100% !important;
        overflow-x: auto !important;
        overflow-y: auto !important;
        position: relative !important;
        background: var(--bg-surface) !important;
    }

    #screenTable {
        border-collapse: separate !important;
        border-spacing: 0 !important;
        width: 100% !important;
        min-width: 1250px !important;
        margin: 0 !important;
        background-color: var(--bg-surface) !important;
    }

    #screenTable th,
    #screenTable td {
        border-right: 1px solid var(--border-color) !important;
        border-bottom: 1px solid var(--border-color) !important;
        box-sizing: border-box !important;
        background-clip: padding-box !important;
    }

    /* Encabezados Sticky al tope (100% Sólido Opaque) */
    #screenTable thead th {
        position: sticky !important;
        top: 0 !important;
        z-index: 60 !important;
        background-color: var(--bg-surface-elevated, #1e293b) !important;
        color: var(--text-primary) !important;
        border-bottom: 2px solid var(--border-color) !important;
        box-shadow: 0 2px 5px rgba(0, 0, 0, 0.4) !important;
        opacity: 1 !important;
    }

    /* Totales Sticky al fondo (100% Sólido Opaque) */
    #screenTable tfoot td {
        position: sticky !important;
        bottom: 0 !important;
        z-index: 60 !important;
        background-color: var(--bg-surface-elevated, #1e293b) !important;
        color: var(--text-primary) !important;
        border-top: 2px solid var(--border-color) !important;
        font-weight: 800 !important;
        box-shadow: 0 -2px 6px rgba(0, 0, 0, 0.45) !important;
        opacity: 1 !important;
    }

    /* Columnas fijas a la izquierda (100% Sólido Opaque) */
    #screenTable tbody td.sticky-col-first {
        position: sticky !important;
        left: 0 !important;
        z-index: 20 !important;
        width: 260px !important;
        min-width: 260px !important;
        background-color: var(--bg-surface, #151e32) !important;
        color: var(--text-primary) !important;
        box-shadow: 2px 0 4px rgba(0, 0, 0, 0.25) !important;
        opacity: 1 !important;
    }

    #screenTable tbody td.sticky-col-second {
        position: sticky !important;
        left: 260px !important;
        z-index: 20 !important;
        width: 42px !important;
        min-width: 42px !important;
        background-color: var(--bg-surface, #151e32) !important;
        color: var(--text-primary) !important;
        box-shadow: 2px 0 4px rgba(0, 0, 0, 0.25) !important;
        opacity: 1 !important;
    }

    /* Intersección de Encabezado y Columna Fija (100% Sólido Opaque) */
    #screenTable thead th.sticky-col-first {
        z-index: 100 !important;
        top: 0 !important;
        left: 0 !important;
        background-color: var(--bg-surface-elevated, #1e293b) !important;
        box-shadow: 2px 2px 6px rgba(0, 0, 0, 0.5) !important;
        opacity: 1 !important;
    }

    #screenTable thead th.sticky-col-second {
        z-index: 100 !important;
        top: 0 !important;
        left: 260px !important;
        background-color: var(--bg-surface-elevated, #1e293b) !important;
        box-shadow: 2px 2px 6px rgba(0, 0, 0, 0.5) !important;
        opacity: 1 !important;
    }

    /* Intersección de Pie de Totales y Columna Fija (100% Sólido Opaque) */
    #screenTable tfoot td.sticky-col-first {
        z-index: 100 !important;
        bottom: 0 !important;
        left: 0 !important;
        background-color: var(--bg-surface-elevated, #1e293b) !important;
        box-shadow: 2px -2px 6px rgba(0, 0, 0, 0.5) !important;
        opacity: 1 !important;
    }

    #screenTable tfoot td.sticky-col-second {
        z-index: 100 !important;
        bottom: 0 !important;
        left: 260px !important;
        background-color: var(--bg-surface-elevated, #1e293b) !important;
        box-shadow: 2px -2px 6px rgba(0, 0, 0, 0.5) !important;
        opacity: 1 !important;
    }

    /* Hover effect */
    #screenTable tbody tr:hover td {
        background-color: rgba(77, 124, 254, 0.12) !important;
    }

    #screenTable tbody td.cell-clickable:hover {
        background-color: var(--color-primary) !important;
        color: #ffffff !important;
        font-weight: 800 !important;
    }

    /* Scrollbars suaves */
    .custom-scrollbar::-webkit-scrollbar {
        width: 8px;
        height: 8px;
    }
    .custom-scrollbar::-webkit-scrollbar-track {
        background: var(--bg-subtle);
    }
    .custom-scrollbar::-webkit-scrollbar-thumb {
        background: var(--border-color);
        border-radius: 4px;
    }
    .custom-scrollbar::-webkit-scrollbar-thumb:hover {
        background: var(--text-muted);
    }
</style>

{{-- VISTA DE IMPRESIÓN (OFICIAL ORIGINAL) --}}
<div class="hidden print:block report-container view-{{ $viewType }}">
    <div class="text-center mb-2">
        <h3 class="font-weight-bold mb-1">INFORME MENSUAL DE SALUD MENTAL SM1-07</h3>
        <h5 class="text-uppercase mb-2">JORNADA {{ $jornada }} - PERIODO: {{ $mes }} {{ $ano }}</h5>
        <div class="d-flex justify-content-center" style="gap: 50px;">
            <span><strong>CENTRO:</strong> CIS SAN MIGUEL</span>
            <span><strong>CARA:</strong> {{ strtoupper($viewType) }}</span>
        </div>
    </div>

    <table class="table-sm107">
        <thead class="thead-sm107">
            <tr class="bg-header-pink">
                <th rowspan="2" style="width: 30px;">N°</th>
                <th rowspan="2" style="width: 200px;">CONCEPTO / ATENCIONES</th>
                @if($viewType == 'anversa')
                    <th rowspan="2" style="width: 25px;">CN</th>
                @endif
                <th colspan="{{ count($dayHeaders) }}">DIAS DEL MES</th>
                <th rowspan="2" style="width: 40px;">TOTAL</th>
            </tr>
            <tr class="bg-header-blue">
                @foreach($dayHeaders as $h)
                    <th style="width: 25px; font-size: 7px; height: 60px; writing-mode: vertical-rl; transform: rotate(180deg);">{{ $h }}</th>
                @endforeach
            </tr>
        </thead>
        <tbody>
            @if($viewType == 'anversa')
                @foreach($ranges as $idx => $r)
                    <tr>
                        <td>{{ $idx }}</td>
                        <td class="text-left px-1">{{ $r['label'] }}</td>
                        <td>{{ $r['type'] }}</td>
                        @php $rTot = 0; @endphp
                        @foreach($dayHeaders as $h)
                            @php $v = $anversaData[$idx][$h] ?? 0; $rTot += $v; @endphp
                            <td>{{ $v ?: '' }}</td>
                        @endforeach
                        <td class="font-weight-bold">{{ $rTot }}</td>
                    </tr>
                @endforeach
            @else
                @foreach($actLabels as $code => $label)
                    <tr>
                        <td>{{ $code }}</td>
                        <td class="text-left px-1">{{ $label }}</td>
                        @php $rTot = 0; @endphp
                        @foreach($dayHeaders as $h)
                            @php $v = $reversaActivities[$code][$h] ?? 0; $rTot += $v; @endphp
                            <td>{{ $v ?: '' }}</td>
                        @endforeach
                        <td class="font-weight-bold">{{ $rTot }}</td>
                    </tr>
                @endforeach
            @endif
        </tbody>
    </table>
</div>
