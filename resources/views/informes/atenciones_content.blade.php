<!-- Barra de Filtros Integrada -->
<div class="filter-container no-print" style="padding: 0.5rem 0.85rem; background: var(--bg-surface); border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; flex-wrap: nowrap; flex-shrink: 0;">
    <form id="filter-form" action="{{ route('informes.atenciones') }}" method="GET" style="display: flex; align-items: center; gap: 0.45rem; margin: 0; flex: 1 1 0%; min-width: 0; flex-wrap: nowrap;">
        <!-- Año y Mes -->
        <div style="display: flex; align-items: center; gap: 0.35rem; flex-shrink: 0;">
            <div style="width: 78px;">
                <select name="ano" class="filter-select w-full ajax-filter">
                    @foreach($anos as $a)
                        <option value="{{ $a }}" {{ $a == $ano ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <div style="width: 110px;">
                <select name="mes" class="filter-select w-full ajax-filter">
                    @foreach($meses as $m)
                        <option value="{{ $m }}" {{ $m == $mes ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div style="height: 20px; width: 1px; background: var(--border-color); flex-shrink: 0; margin: 0 2px;"></div>

        <!-- Filtros Secundarios -->
        <div style="display: flex; align-items: center; gap: 0.35rem; flex: 1 1 0%; min-width: 0; overflow: hidden;">
            <div style="width: 105px; flex-shrink: 0;">
                <select name="personal" class="filter-select w-full ajax-filter">
                    <option value="MEDICOS" {{ $personal == 'MEDICOS' ? 'selected' : '' }}>MÉDICOS</option>
                    <option value="OTROS" {{ $personal == 'OTROS' ? 'selected' : '' }}>OTROS PROF.</option>
                </select>
            </div>
            <div style="width: 220px; flex-shrink: 0;">
                <select name="search" class="filter-select w-full ajax-filter">
                    <option value="TODOS">TODOS LOS PROFESIONALES</option>
                    @foreach($nombresMedicos as $nm)
                        <option value="{{ $nm }}" {{ $nm == $search ? 'selected' : '' }}>{{ $nm }}</option>
                    @endforeach
                </select>
            </div>
            <div style="width: 130px; flex-shrink: 0;">
                <select name="prof" class="filter-select w-full ajax-filter">
                    <option value="TODAS">PROFESIÓN</option>
                    @foreach($profesiones as $p)
                        <option value="{{ $p }}" {{ $p == $profFilter ? 'selected' : '' }}>{{ $p }}</option>
                    @endforeach
                </select>
            </div>
            <div style="width: 110px; flex-shrink: 0;">
                <select name="jornada" class="filter-select w-full ajax-filter">
                    <option value="TODAS">JORNADA</option>
                    @foreach($jornadas as $j)
                        <option value="{{ $j }}" {{ $j == $jornada ? 'selected' : '' }}>{{ $j }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div style="display: flex; align-items: center; gap: 0.35rem; flex-shrink: 0; margin-left: auto;">
            <button type="button" id="toggle-fullscreen" class="btn-action-fullscreen" title="Pantalla Completa">
                <i class="bi bi-arrows-fullscreen" id="fullScreenIcon"></i>
            </button>
            <button type="button" onclick="window.print()" class="btn-action-print" title="Imprimir">
                <i class="bi bi-printer"></i>
            </button>
            <a id="btn-export-atenciones" href="{{ route('informes.atenciones.export', request()->all()) }}" class="btn-action-excel" title="Exportar Excel">
                <i class="bi bi-file-earmark-excel"></i>
            </a>
        </div>
    </form>
</div>

<!-- Tabla de Atenciones (Matrix) -->
<div style="flex: 1 1 0%; min-height: 0; display: flex; flex-direction: column; overflow: hidden; position: relative;">
    <div class="table-responsive" style="flex: 1 1 0%; min-height: 0; overflow: auto;">
        <table class="table table-bordered table-sm text-center mb-0 w-full" id="atencionesTable">
            <thead class="thead-premium sticky-top">
                <tr>
                    <th class="sticky-col-first align-middle" style="width: 220px; min-width: 200px; padding: 4px 8px !important; text-align: left;">
                        <span style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-primary);">
                            MÉDICO / PROFESIONAL
                        </span>
                    </th>
                    
                    @php
                        \Carbon\Carbon::setLocale('es');
                        
                        if (!function_exists('getEpiWeekAtenciones')) {
                            function getEpiWeekAtenciones($date) {
                                $dayOfWeek = $date->dayOfWeek;
                                $wednesday = $date->copy()->addDays(3 - $dayOfWeek)->startOfDay();
                                $year = $wednesday->year;
                                $jan4 = \Carbon\Carbon::create($year, 1, 4)->startOfDay();
                                $wednesdayOfJan4 = $jan4->copy()->addDays(3 - $jan4->dayOfWeek)->startOfDay();
                                $diffDays = (int) round(($wednesday->timestamp - $wednesdayOfJan4->timestamp) / 86400);
                                return (int) round($diffDays / 7) + 1;
                            }
                        }

                        $weeks = [];
                        foreach($fechasObjs as $fo) {
                            $w = getEpiWeekAtenciones($fo['obj']);
                            if(!isset($weeks[$w])) $weeks[$w] = [];
                            $weeks[$w][] = $fo;
                        }
                    @endphp

                    @foreach($weeks as $weekNum => $days)
                        @foreach($days as $day)
                            @php
                                $parsedDay = $day['obj'];
                                $initial = strtoupper(substr($parsedDay->locale('es')->isoFormat('dddd'), 0, 1));
                                $isWeekend = in_array($parsedDay->dayOfWeek, [0, 6]);
                            @endphp
                            <th class="vertical-col align-middle {{ $isWeekend ? 'bg-weekend-header' : '' }}" style="width: 30px; min-width: 29px; padding: 3px 1px !important;">
                                <div class="vertical-text-wrapper">
                                    <span class="day-num" style="font-size: 0.85rem; font-weight: 800;">{{ $parsedDay->day }}</span>
                                    <span class="day-name" style="font-size: 0.70rem; font-weight: 700;">{{ $initial }}</span>
                                </div>
                            </th>
                        @endforeach
                        <th class="align-middle bg-week-header" style="width: 40px; min-width: 38px; padding: 3px 1px !important;" title="Semana Epidemiológica {{ $weekNum }}">
                            <div class="vertical-text-wrapper">
                                <span class="day-num" style="font-size: 0.88rem; font-weight: 800;">{{ $weekNum }}</span>
                                <span class="day-name" style="font-size: 0.65rem; font-weight: 800;">SEM</span>
                            </div>
                        </th>
                    @endforeach
                    
                    <th class="align-middle col-mes-total" style="width: 58px; min-width: 55px; padding: 3px 1px !important;">
                        <div class="vertical-text-wrapper">
                            <span class="day-num" style="font-size: 0.88rem; font-weight: 800;">MES</span>
                            <span class="day-name" style="font-size: 0.65rem; font-weight: 800;">TOTAL</span>
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
                
                @forelse($data as $medico => $mD)
                    <tr class="doctor-row">
                        <td class="sticky-col-first text-left" style="padding: 4px 8px !important;">
                            <div class="doctor-name-container">
                                <span class="doctor-name-text" title="{{ $medico }}">{{ $medico }}</span>
                                <a href="{{ route('informes.at2', ['ano' => $ano, 'mes' => $mes, 'medico' => $medico]) }}" 
                                   class="btn-doctor-at2 no-print" 
                                   title="Ver informe AT2 (Diario) de {{ $medico }}"
                                   target="_blank">
                                    <i class="bi bi-box-arrow-up-right"></i>
                                </a>
                            </div>
                        </td>
                        
                        @php $rowTotal = 0; @endphp
                        @foreach($weeks as $weekNum => $days)
                            @php $weekTotal = 0; @endphp
                            @foreach($days as $day)
                                @php
                                    $val = $mD['dates'][$day['fecha']] ?? 0;
                                    $rowTotal += $val;
                                    $weekTotal += $val;
                                    
                                    if(!isset($grandTotals[$day['fecha']])) $grandTotals[$day['fecha']] = 0;
                                    $grandTotals[$day['fecha']] += $val;
                                    
                                    if(!isset($weekGrandTotals[$weekNum])) $weekGrandTotals[$weekNum] = 0;
                                    $weekGrandTotals[$weekNum] += $val;
                                    
                                    $isW = $day['obj']->isWeekend();
                                    $cellClass = $isW ? 'cell-weekend' : '';
                                @endphp
                                <td class="{{ $cellClass }}">
                                    @if($val > 0)
                                        <span class="cell-atencion-val">{{ $val }}</span>
                                    @else
                                        <span class="cell-atencion-empty">·</span>
                                    @endif
                                </td>
                            @endforeach
                            <td class="cell-week-subtotal">
                                {{ $weekTotal > 0 ? $weekTotal : '-' }}
                            </td>
                        @endforeach
                        
                        <td class="col-mes-total">
                            {{ $rowTotal }}
                        </td>
                        @php $monthGrandTotal += $rowTotal; @endphp
                    </tr>
                @empty
                    <tr>
                        <td colspan="100%" class="text-center py-5">
                            <div style="color: var(--text-muted); padding: 2rem 0;">
                                <i class="bi bi-search" style="font-size: 2.5rem; display: block; margin-bottom: 0.75rem; opacity: 0.5;"></i>
                                <h4 style="font-weight: 700; font-size: 1rem; color: var(--text-primary); margin-bottom: 0.25rem;">No se encontraron registros</h4>
                                <p style="font-size: 0.8rem; margin: 0;">Intente seleccionando otros filtros de búsqueda.</p>
                            </div>
                        </td>
                    </tr>
                @endforelse
            </tbody>
            
            <!-- FILA DE TOTAL GENERAL (Sticky en pie de tabla) -->
            @if(!empty($data))
            <tfoot class="atenciones-tfoot">
                <tr class="atenciones-total-row">
                    <td class="sticky-col-first text-right pr-3" style="font-weight: 800; letter-spacing: 0.05em;">
                        TOTAL GENERAL
                    </td>
                    @foreach($weeks as $weekNum => $days)
                        @foreach($days as $day)
                            <td class="align-middle">
                                {{ $grandTotals[$day['fecha']] ?? 0 }}
                            </td>
                        @endforeach
                        <td class="align-middle cell-week-subtotal" style="font-weight: 800;">
                            {{ $weekGrandTotals[$weekNum] ?? 0 }}
                        </td>
                    @endforeach
                    <td class="align-middle col-mes-total" style="font-size: 0.95rem; font-weight: 900;">
                        {{ $monthGrandTotal }}
                    </td>
                </tr>
            </tfoot>
            @endif
        </table>
    </div>
</div>
