    <!-- Barra de Filtros en Una Sola Fila Horizontal Estricta -->
    <div class="filter-container no-print mb-2"
        style="background: var(--bg-surface) !important; border: 1px solid var(--border-color) !important; border-radius: var(--radius-md, 10px) !important; padding: 0.5rem 0.85rem !important; display: flex !important; flex-direction: row !important; align-items: center !important; justify-content: space-between !important; flex-wrap: nowrap !important; gap: 8px !important; box-shadow: var(--shadow-sm) !important; width: 100% !important; overflow-x: auto !important;">
        <form id="filter-form" action="{{ route('informes.implantes') }}" method="GET"
            style="display: flex !important; flex-direction: row !important; align-items: center !important; justify-content: space-between !important; flex-wrap: nowrap !important; gap: 8px !important; margin: 0 !important; width: 100% !important; min-width: 0 !important;">
            
            <div style="display: flex !important; flex-direction: row !important; align-items: center !important; flex-wrap: nowrap !important; gap: 8px !important; flex-shrink: 0 !important;">
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
                <div style="width: 120px !important; min-width: 120px !important; flex-shrink: 0 !important;">
                    <select name="mes" class="filter-select ajax-filter"
                        style="width: 100% !important; height: 34px !important; font-weight: 700 !important; font-size: 0.82rem !important;">
                        @foreach($meses as $m)
                            <option value="{{ $m }}" {{ $m == $mes ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Separador vertical -->
                <div style="height: 22px !important; width: 1px !important; background: var(--border-color) !important; flex-shrink: 0 !important; margin: 0 2px !important;"></div>

                <!-- Médico / Profesional (Acortado) -->
                <div style="width: 220px !important; min-width: 200px !important; flex-shrink: 0 !important;">
                    <select name="search" class="filter-select ajax-filter" title="Médico / Profesional"
                        style="width: 100% !important; height: 34px !important; font-weight: 700 !important; font-size: 0.82rem !important;">
                        <option value="TODOS">TODOS LOS PROFESIONALES</option>
                        @foreach($nombresMedicos as $nm)
                            <option value="{{ $nm }}" {{ $nm == $search ? 'selected' : '' }}>{{ $nm }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Profesión (Alargado) -->
                <div style="width: 180px !important; min-width: 160px !important; flex-shrink: 0 !important;">
                    <select name="prof" class="filter-select ajax-filter" title="Profesión"
                        style="width: 100% !important; height: 34px !important; font-weight: 700 !important; font-size: 0.82rem !important;">
                        <option value="TODAS">TODAS LAS PROFESIONES</option>
                        @foreach($profesiones as $p)
                            <option value="{{ $p }}" {{ $p == $profFilter ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Jornada (Alargado) -->
                <div style="width: 165px !important; min-width: 150px !important; flex-shrink: 0 !important;">
                    <select name="jornada" class="filter-select ajax-filter" title="Jornada"
                        style="width: 100% !important; height: 34px !important; font-weight: 700 !important; font-size: 0.82rem !important;">
                        <option value="TODAS">TODAS LAS JORNADAS</option>
                        @foreach($jornadas as $j)
                            <option value="{{ $j }}" {{ $j == $jornada ? 'selected' : '' }}>{{ $j }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <!-- Acciones a la derecha -->
            <div style="display: flex !important; flex-direction: row !important; align-items: center !important; flex-wrap: nowrap !important; gap: 6px !important; flex-shrink: 0 !important; margin-left: auto !important;">
                <button type="button" onclick="toggleFullScreen()" class="btn-action-fullscreen" title="Pantalla Completa"><i class="bi bi-arrows-fullscreen" id="fullScreenIcon"></i></button>
                <button type="button" onclick="window.print()" class="btn-action-print" title="Imprimir"><i class="bi bi-printer"></i></button>
                <a id="btn-export-implantes" href="{{ route('informes.implantes.export', request()->all()) }}" class="font-medium d-flex align-items-center justify-content-center rounded text-white shadow-sm" style="width: 28px !important; height: 28px !important; font-size: 13px !important; background: #059669 !important;" title="Exportar Excel">
                    <i class="bi bi-file-earmark-excel"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="flex-1 p-0 overflow-hidden relative">
        <div class="table-responsive h-full overflow-auto">
            <table class="table table-bordered table-sm text-center mb-0" id="implantesTable">
                    <thead class="thead-premium sticky-top">
                        <tr>
                            <th class="sticky-col-first align-middle shadow-sm" style="width: 320px; min-width: 320px; z-index: 20;">
                                <div class="p-2 text-uppercase letter-spacing-1">Médico / Profesional</div>
                            </th>
                            
                            @foreach($fechasObjs as $day)
                                @php $initial = strtoupper(substr(\Carbon\Carbon::parse($day['obj'])->locale('es')->isoFormat('dddd'), 0, 1)); @endphp
                                <th class="vertical-col" style="width: 45px; min-width: 45px;">
                                    <div class="vertical-text-wrapper">
                                        <span class="day-num">{{ \Carbon\Carbon::parse($day['obj'])->day }}</span>
                                        <span class="day-name text-xs font-weight-bold">{{ $initial }}</span>
                                    </div>
                                </th>
                            @endforeach
                            
                            <th class="align-middle bg-primary-light border-thick-vertical" style="width: 70px; min-width: 70px; z-index: 10;">
                                <div class="text-xs">TOTAL</div>
                                <div class="h4 mb-0 font-weight-bold">MES</div>
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        @php 
                            $grandTotals = []; 
                            $monthGrandTotal = 0;
                            $grandBreakdowns = []; 
                            $monthGrandBreakdown = [];
                        @endphp
                        
                        @forelse($data as $medico => $mD)
                            <tr>
                                <td class="sticky-col-first text-left pl-3 font-weight-bold" style="color: var(--text-primary);">
                                    {{ $medico }}
                                </td>
                                
                                @php 
                                    $rowTotal = 0; 
                                    $rowBreakdown = [];
                                @endphp
                                @foreach($fechasObjs as $day)
                                    @php
                                        $item = $mD['dates'][$day['fecha']] ?? null;
                                        $val = $item ? $item['total'] : 0;
                                        $breakdown = $item ? $item['breakdown'] : [];
                                        
                                        $rowTotal += $val;
                                        foreach($breakdown as $type => $count) {
                                            $rowBreakdown[$type] = ($rowBreakdown[$type] ?? 0) + $count;
                                            if(!isset($grandBreakdowns[$day['fecha']])) $grandBreakdowns[$day['fecha']] = [];
                                            $grandBreakdowns[$day['fecha']][$type] = ($grandBreakdowns[$day['fecha']][$type] ?? 0) + $count;
                                            $monthGrandBreakdown[$type] = ($monthGrandBreakdown[$type] ?? 0) + $count;
                                        }

                                        if(!isset($grandTotals[$day['fecha']])) $grandTotals[$day['fecha']] = 0;
                                        $grandTotals[$day['fecha']] += $val;
                                        $cellClass = $val > 0 ? 'font-weight-bold text-dark' : 'text-muted-light';

                                        $title = "";
                                        if (count($breakdown) > 0) {
                                            $title = "Breakdown: ";
                                            foreach($breakdown as $t => $c) $title .= "$t: $c | ";
                                            $title = rtrim($title, " | ");
                                        }
                                    @endphp
                                    <td class="{{ $cellClass }}" {!! $title ? 'data-toggle="tooltip" data-placement="top" title="'.$title.'"' : '' !!}>
                                        {{ $val > 0 ? $val : '·' }}
                                    </td>
                                @endforeach
                                @php
                                    $rowTitle = "";
                                    if (count($rowBreakdown) > 0) {
                                        $rowTitle = "Total Breakdown: ";
                                        foreach($rowBreakdown as $t => $c) $rowTitle .= "$t: $c | ";
                                        $rowTitle = rtrim($rowTitle, " | ");
                                    }
                                @endphp
                                <td class="bg-primary-light font-weight-bold border-thick-vertical" {!! $rowTitle ? 'data-toggle="tooltip" data-placement="top" title="'.$rowTitle.'"' : '' !!}>
                                    {{ $rowTotal }}
                                </td>
                                @php $monthGrandTotal += $rowTotal; @endphp
                            </tr>
                        @empty
                            <tr>
                                <td colspan="100%" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-search fa-3x mb-3 text-gray-300"></i>
                                        <h4>No se encontraron resultados</h4>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        
                        @if(!empty($data))
                        <tr class="total-row bg-dark text-white font-weight-bold shadow">
                            <td class="sticky-col-first bg-dark text-white py-3 text-right pr-3">TOTAL GENERAL</td>
                            @foreach($fechasObjs as $day)
                                @php
                                    $gb = $grandBreakdowns[$day['fecha']] ?? [];
                                    $gTitle = "";
                                    if (count($gb) > 0) {
                                        $gTitle = "Daily Total Breakdown: ";
                                        foreach($gb as $t => $c) $gTitle .= "$t: $c | ";
                                        $gTitle = rtrim($gTitle, " | ");
                                    }
                                @endphp
                                <td class="align-middle" {!! $gTitle ? 'data-toggle="tooltip" data-placement="top" title="'.$gTitle.'"' : '' !!}>
                                    {{ $grandTotals[$day['fecha']] ?? 0 }}
                                </td>
                            @endforeach
                            @php
                                $mTitle = "";
                                if (count($monthGrandBreakdown) > 0) {
                                    $mTitle = "Monthly Grand Total Breakdown: ";
                                    foreach($monthGrandBreakdown as $t => $c) $mTitle .= "$t: $c | ";
                                    $mTitle = rtrim($mTitle, " | ");
                                }
                            @endphp
                            <td class="bg-primary-light align-middle h5 m-0 font-weight-bold border-thick-vertical" {!! $mTitle ? 'data-toggle="tooltip" data-placement="top" title="'.$mTitle.'"' : '' !!}>
                                {{ $monthGrandTotal }}
                            </td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    
