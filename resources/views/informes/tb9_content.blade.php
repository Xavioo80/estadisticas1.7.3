    <div class="filter-container no-print">
        <form id="filter-form" action="{{ route('informes.tb9') }}" method="GET" class="flex flex-1 items-center gap-2 mb-0">
            <div class="flex items-center gap-1.5 flex-1">
                <div style="width: 82px; flex-shrink: 0;">
                    <select name="ano" class="filter-select w-full ajax-filter">
                        @foreach($anos as $a)
                            <option value="{{ $a }}" {{ $a == $ano ? 'selected' : '' }}>{{ $a }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="width: 120px; flex-shrink: 0;">
                    <select name="mes" class="filter-select w-full ajax-filter">
                        @foreach($meses as $m)
                            <option value="{{ $m }}" {{ $m == $mes ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="width: 120px; flex-shrink: 0;">
                    <select name="jornada" class="filter-select w-full ajax-filter">
                        <option value="TODAS">JORNADA</option>
                        @foreach($jornadas as $j)
                            <option value="{{ $j }}" {{ $j == $jornada ? 'selected' : '' }}>{{ $j }}</option>
                        @endforeach
                    </select>
                </div>
                <div style="width: 250px; flex-shrink: 0;">
                    <select name="profesiones[]" id="filter-profesiones" class="filter-select w-full ajax-filter select2-multiple" multiple="multiple" data-placeholder="PROFESIONES">
                        @foreach($profesiones as $p)
                            <option value="{{ $p }}" {{ in_array($p, $selectedProfs) ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-1.5 ml-auto">
                <button type="button" id="toggle-fullscreen" class="btn-action-fullscreen" title="Pantalla Completa"><i class="bi bi-arrows-fullscreen" id="fullScreenIcon"></i></button>
                <button type="button" onclick="window.print()" class="btn-action-print" title="Imprimir"><i class="bi bi-printer"></i></button>
                <a id="btn-export-tb9" href="{{ route('informes.tb9.export', request()->all()) }}" class="btn-action-excel" title="Exportar Excel">
                    <i class="bi bi-file-earmark-excel"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="flex-1 p-0 overflow-hidden relative">
        <div class="table-responsive h-full overflow-auto">
            <table class="table table-bordered table-sm text-center mb-0" id="tb9Table">
                    <thead class="thead-premium sticky-top">
                        <tr>
                            <th class="sticky-col-first align-middle shadow-sm" style="width: 170px; min-width: 170px; z-index: 20;">
                                <div class="p-2 text-uppercase letter-spacing-1" style="font-size: 0.75rem;">Rango de Edad (TB9)</div>
                            </th>
                            
                            @php
                                \Carbon\Carbon::setLocale('es');
                                $weekMap = [];
                                $currentWeek = null;
                                $weeks = [];
                                foreach($fechasObjs as $fo) {
                                    $w = $fo['obj']->weekOfYear;
                                    if(!isset($weeks[$w])) $weeks[$w] = [];
                                    $weeks[$w][] = $fo;
                                }
                                
                                // Colores para semanas cíclicos
                                $weekColors = [
                                    'bg-primary-soft', 'bg-success-soft', 'bg-warning-soft', 'bg-info-soft', 'bg-danger-soft'
                                ];
                                $i = 0;
                            @endphp

                            @foreach($weeks as $weekNum => $days)
                                @php 
                                    $colorClass = $weekColors[$i % count($weekColors)];
                                    $i++;
                                @endphp
                                @foreach($days as $day)
                                    @php
                                        $parsedDay = $day['obj'];
                                        $initial = strtoupper(substr($parsedDay->locale('es')->isoFormat('dddd'), 0, 1));
                                        $isWeekend = in_array($parsedDay->dayOfWeek, [0, 6]);
                                        $textColor = $isWeekend ? 'color: #0d5f30 !important;' : '';
                                    @endphp
                                    <th class="vertical-col align-middle" style="width: 26px; min-width: 26px; padding: 1px !important; {{ $textColor }}">
                                        <div class="vertical-text-wrapper">
                                            <span class="day-num">{{ $parsedDay->day }}</span>
                                            <span class="day-name font-weight-bold">{{ $initial }}</span>
                                        </div>
                                    </th>
                                @endforeach
                                {{-- Weekly total removed per request --}}
                            @endforeach
                            
                            <th class="align-middle bg-primary-light border-thick-vertical" style="width: 45px; min-width: 45px; z-index: 10;">
                                <div class="vertical-text-wrapper">
                                    <span class="day-num">MES</span>
                                    <small class="sem-label" style="font-size: 0.55rem;">TOTAL</small>
                                </div>
                            </th>
                        </tr>

                    </thead>
                    <tbody>
                        @php 
                            $grandTotals = []; 
                            $weekGrandTotals = [];
                            $monthGrandTotal = 0;
                        @endphp
                        
                        @forelse($data as $rango => $rD)
                            <tr class="data-row">
                                <td class="sticky-col-first text-left pl-3 font-weight-bold text-black" >
                                    {{ $rango }}
                                </td>
                                
                                @php $rowTotal = 0; @endphp
                                @foreach($weeks as $weekNum => $days)
                                    @php $weekTotal = 0; @endphp
                                    @foreach($days as $day)
                                        @php
                                            $val = $rD['dates'][$day['fecha']] ?? 0;
                                            $rowTotal += $val;
                                            $weekTotal += $val;
                                            
                                            // Acumular totales por fecha
                                            if(!isset($grandTotals[$day['fecha']])) $grandTotals[$day['fecha']] = 0;
                                            $grandTotals[$day['fecha']] += $val;
                                            
                                            // Acumular total general de la semana
                                            if(!isset($weekGrandTotals[$weekNum])) $weekGrandTotals[$weekNum] = 0;
                                            $weekGrandTotals[$weekNum] += $val;
                                            
                                            $isW = $day['obj']->isWeekend();
                                            $cellClass = $val > 0 ? 'font-weight-bold text-dark' : 'text-muted-light';
                                            if($isW) $cellClass .= ' cell-weekend';
                                        @endphp
                                        <td class="{{ $cellClass }}">{{ $val > 0 ? $val : '·' }}</td>
                                    @endforeach
                                    {{-- Weekly total removed --}}
                                @endforeach
                                
                                <td class="font-weight-bold border-thick-vertical" style="background-color: #cbd5e1; color: #000;">{{ $rowTotal }}</td>
                                @php $monthGrandTotal += $rowTotal; @endphp
                            </tr>
                        @empty
                            <tr>
                                <td colspan="100%" class="text-center py-5">
                                    <div class="text-muted">
                                        <i class="fas fa-search fa-3x mb-3 text-gray-300"></i>
                                        <h4>No se encontraron resultados</h4>
                                        <p>Intente con otro término de búsqueda o filtros</p>
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                        
                        <!-- FILA DE TOTALES -->
                        @if(!empty($data))
                        <tr class="total-row bg-dark text-white font-weight-bold shadow">
                            <td class="sticky-col-first bg-dark text-white py-3 text-right pr-3">TOTAL GENERAL</td>
                            @foreach($weeks as $weekNum => $days)
                                @foreach($days as $day)
                                    <td class="align-middle">{{ $grandTotals[$day['fecha']] ?? 0 }}</td>
                                @endforeach
                                {{-- Weekly total removed --}}
                            @endforeach
                            <td class="align-middle h5 m-0 font-weight-bold border-thick-vertical" style="background-color: #cbd5e1; color: #000;">{{ $monthGrandTotal }}</td>
                        </tr>
                        @endif
                    </tbody>
                </table>
            </div>
        </div>
    
