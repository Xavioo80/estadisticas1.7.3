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

    <div class="filter-container flex flex-wrap items-center gap-4 p-3.5 bg-slate-50 shrink-0 border-b border-slate-200 no-print">
        <form id="filter-form" action="{{ route('informes.sm107') }}" method="GET" class="flex flex-1 items-center gap-4 mb-0" style="max-width:1400px; margin:0 auto;">
            <input type="hidden" name="view" id="view-input" value="{{ $viewType }}">
            
            <div class="flex items-center gap-2">
                <div class="flex bg-slate-200 p-1.5 rounded-xl shadow-inner">
                    <button type="button" class="px-6 py-2 text-[14px] font-bold rounded-lg transition-all btn-toggle-view {{ $viewType == 'anversa' ? 'bg-white text-blue-700 shadow-md' : 'text-slate-600 hover:text-slate-800' }}" data-view="anversa">
                        ANVERSA
                    </button>
                    <button type="button" class="px-6 py-2 text-[14px] font-bold rounded-lg transition-all btn-toggle-view {{ $viewType == 'reversa' ? 'bg-white text-blue-700 shadow-md' : 'text-slate-600 hover:text-slate-800' }}" data-view="reversa">
                        REVERSA
                    </button>
                </div>
            </div>

            <div class="h-8 w-[1.5px] bg-slate-200 mx-1"></div>

            <div class="flex items-center gap-3">
                <div class="w-28">
                    <select name="ano" class="filter-select w-full ajax-filter">
                        @foreach($anos as $a)
                            <option value="{{ $a }}" {{ $a == $ano ? 'selected' : '' }}>{{ $a }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-40">
                    <select name="mes" class="filter-select w-full ajax-filter">
                        @foreach($meses as $m)
                            <option value="{{ $m }}" {{ strtoupper($mes) == $m ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-48">
                    <select name="jornada" class="filter-select w-full ajax-filter">
                        <option value="TODAS">JORNADA</option>
                        @foreach($jornadas as $j)
                            <option value="{{ $j }}" {{ $jornada == $j ? 'selected' : '' }}>{{ $j }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-2.5 ml-auto">
                <button type="button" id="btn-fullscreen" onclick="toggleFullScreen()" class="font-bold flex items-center justify-center rounded-xl h-11 w-11 text-lg bg-white text-slate-600 border border-slate-200 hover:bg-slate-50 hover:border-slate-300 hover:text-slate-900 transition-all shadow-sm" title="Pantalla Completa">
                    <i class="fas bi bi-arrows-fullscreen" id="fullScreenIcon"></i>
                </button>
                <button type="button" onclick="window.print()" class="btn-action-print" title="Imprimir"><i class="bi bi-printer"></i></button>
                <button type="button" id="btn-show-sidebar" class="font-bold flex items-center justify-center rounded-xl h-11 w-11 text-lg bg-emerald-600 text-white hover:bg-emerald-700 transition-all shadow-md" title="Ajustes de Tabla">
                    <i class="fas fa-cog"></i>
                </button>
            </div>
        </form>
    </div>


{{-- VISTA DE PANTALLA (SIN COLUMNAS DE SEMANA) --}}
<div class="print:hidden">
    {{-- Banner de Notificación de Fechas Nulas --}}
    @if(isset($nullDatesCount) && $nullDatesCount > 0)
        <div class="alert alert-warning shadow-sm border-dark mb-3 d-flex align-items-center" style="border-radius: 10px; border-left: 5px solid #ffa000;">
            <i class="fas fa-exclamation-triangle mr-3 fa-2x text-warning"></i>
            <div>
                <strong class="d-block text-dark">Aviso: Registros con Fecha Indefinida</strong>
                <span class="text-dark">Se han detectado <strong>{{ $nullDatesCount }}</strong> registros sin fecha asignada en la base de datos. Para no afectar tus totales, el sistema los ha contabilizado automáticamente en la última columna del mes.</span>
            </div>
        </div>
    @endif

    {{-- Banner de Atenciones en Menores de 5 por Profesiones no Especializadas --}}
    @if(isset($invalidUnderFive) && count($invalidUnderFive) > 0)
        <div class="alert alert-danger shadow-sm border-dark mb-3 d-flex flex-column" style="border-radius: 10px; border-left: 5px solid #d32f2f; background-color: #fce4ec;">
            <div class="d-flex align-items-center mb-2">
                <i class="fas fa-user-shield mr-3 fa-2x text-danger"></i>
                <div>
                    <strong class="d-block text-danger">Auditoría: Atenciones SM en Menores de 5 Años (No Especializadas)</strong>
                    <span class="text-dark" style="font-size: 13px;">Se han detectado <strong>{{ count($invalidUnderFive) }}</strong> registros de menores de 5 años atendidos por personal <strong>DIFERENTE</strong> a Psicología o Psiquiatría. Estas atenciones <strong>NO</strong> se cuentan en los totales del informe oficial.</span>
                </div>
            </div>
            <div class="table-responsive mt-2" style="max-height: 150px;">
                <table class="table table-sm table-bordered mb-0 bg-white" style="font-size: 11px;">
                    <thead class="bg-danger text-white">
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

    <div class="card shadow-sm border-0" style="border-radius: 15px; overflow: hidden;">
        <div class="card-body p-0 table-responsive {{ $viewType == 'reversa' ? 'view-reversa' : 'view-anversa' }}" style="max-height: calc(100vh - 160px);">
            <table class="table-premium" id="screenTable">
                <thead class="thead-premium">
                    <tr>
                        <th class="sticky-col-first align-middle" style="width: 280px; min-width: 280px;">
                            <div class="px-2">CONCEPTO / ATENCIONES</div>
                        </th>
                        @if($viewType == 'anversa')
                            <th class="sticky-col-second align-middle text-center" style="width: 35px; min-width: 35px;">CN</th>
                        @endif
                        
                        @foreach($fechasDetalle as $f)
                            <th class="text-center" style="width: 32px; min-width: 32px; background-color: #f8f9fa;">
                                <div class="d-flex flex-column align-items-center">
                                    <span class="font-weight-bold" style="font-size: 0.95rem; line-height: 1;">{{ $f['day'] }}</span>
                                    <small class="font-weight-bold text-uppercase" style="font-size: 0.65rem; color: #555;">{{ $f['initial'] }}</small>
                                </div>
                            </th>
                        @endforeach

                        <th class="text-center align-middle" style="width: 50px; background-color: #e3f2fd;">TOTAL</th>
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
                                <td class="sticky-col-first">{{ $r['label'] }}</td>
                                <td class="sticky-col-second">{{ $r['type'] }}</td>
                                @php $rowTotal = 0; @endphp
                                @foreach($fechasDetalle as $f)
                                    @php $val = $anversaData[$idx][$f['formatted']] ?? 0; $rowTotal += $val; @endphp
                                    <td class="{{ $val > 0 ? 'font-weight-bold text-primary clickable-cell' : 'text-muted' }}"
                                        @if($val > 0) onclick="showDetails('{{ $idx }}', '{{ $f['formatted'] }}')" @endif>
                                        {{ $val ?: '·' }}
                                    </td>
                                @endforeach
                                <td class="font-weight-bold text-center {{ $rowTotal > 0 ? 'clickable-cell' : '' }}" 
                                    style="background-color: #e3f2fd;"
                                    @if($rowTotal > 0) onclick="showDetails('{{ $idx }}', null)" @endif>
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
                            <tr @if(in_array($code, [25,27,29,32,34,37])) style="de7;" @endif>
                                <td class="sticky-col-first">{{ $label }}</td>
                                @if($viewType == 'anversa')
                                    <td class="text-muted">-</td>
                                @endif
                                @php $rowTotal = 0; @endphp
                                @foreach($fechasDetalle as $f)
                                    @php $val = $reversaActivities[$code][$f['formatted']] ?? 0; $rowTotal += $val; @endphp
                                    <td class="{{ $val > 0 ? 'font-weight-bold text-primary clickable-cell' : 'text-muted' }}"
                                        @if($val > 0) onclick="showDetails('{{ $code }}', '{{ $f['formatted'] }}')" @endif>
                                        {{ $val ?: '·' }}
                                    </td>
                                @endforeach
                                <td class="font-weight-bold text-center {{ $rowTotal > 0 ? 'clickable-cell' : '' }}" 
                                    style="background-color: #e3f2fd;"
                                    @if($rowTotal > 0) onclick="showDetails('{{ $code }}', null)" @endif>
                                    {{ $rowTotal }}
                                </td>
                            </tr>
                        @endforeach

                    @endif
                </tbody>
                <tfoot class="bg-light font-weight-bold">
                    <tr>
                        <td class="sticky-col-first text-right" colspan="{{ $viewType == 'anversa' ? 1 : 1 }}">TOTALES</td>
                        @if($viewType == 'anversa')
                            <td class="sticky-col-second text-center"></td>
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
                                        // No sumamos los "Número de participantes" (25,27,29,32,34,37) para no duplicar? 
                                        // O sumamos todo? Por ahora sumamos todo ya que son "Actividades"
                                        $colSum += ($reversaActivities[$code][$f['formatted']] ?? 0);
                                    }
                                }
                                $grandTotal += $colSum;
                            @endphp
                            <td class="text-center {{ $colSum > 0 ? 'text-primary clickable-cell font-weight-bold' : '' }}"
                                @if($colSum > 0) onclick="showDetails('all', '{{ $f['formatted'] }}')" @endif>
                                {{ $colSum ?: '0' }}
                            </td>
                        @endforeach
                        <td class="text-center text-white bg-primary {{ $grandTotal > 0 ? 'clickable-cell' : '' }}"
                            @if($grandTotal > 0) onclick="showDetails('all', null)" @endif>
                            {{ $grandTotal }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</div>

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
                        <td class="font-weight-bold">{{ $r['type'] }}</td>
                        @php $rowTotal = 0; @endphp
                        @foreach($dayHeaders as $h)
                            @php $val = $anversaData[$idx][$h] ?? 0; $rowTotal += $val; @endphp
                            <td>{{ $val ?: '0' }}</td>
                        @endforeach
                        <td class="font-weight-bold">{{ $rowTotal }}</td>
                    </tr>
                @endforeach
            @else
                @foreach($actLabels as $code => $label)
                    <tr>
                        <td>{{ $code }}</td>
                        <td class="text-left px-1">{{ $label }}</td>
                        @if($viewType == 'anversa')
                            <td>-</td>
                        @endif
                        @php $rowTotal = 0; @endphp
                        @foreach($dayHeaders as $h)
                            @php $val = $reversaActivities[$code][$h] ?? 0; $rowTotal += $val; @endphp
                            <td>{{ $val ?: '0' }}</td>
                        @endforeach
                        <td class="font-weight-bold">{{ $rowTotal }}</td>
                    </tr>
                @endforeach

            @endif
            <tr class="font-weight-bold" style="background-color: #f0f0f0;">
                <td colspan="{{ $viewType == 'anversa' ? 2 : 1 }}" class="text-right">TOTALES</td>
                @if($viewType == 'anversa')
                    <td></td>
                @endif
                @php $printGrandTotal = 0; @endphp
                @foreach($dayHeaders as $h)
                    @php 
                        $printColSum = 0;
                        if($viewType == 'anversa') {
                            foreach($ranges as $idx => $r) {
                                $printColSum += ($anversaData[$idx][$h] ?? 0);
                            }
                        } else {
                            foreach($actLabels as $code => $label) {
                                $printColSum += ($reversaActivities[$code][$h] ?? 0);
                            }
                        }
                        $printGrandTotal += $printColSum;
                    @endphp
                    <td>{{ $printColSum ?: '0' }}</td>
                @endforeach
                <td>{{ $printGrandTotal }}</td>
            </tr>
        </tbody>
    </table>
</div>
