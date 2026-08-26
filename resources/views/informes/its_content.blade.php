    <div class="filter-container flex flex-wrap items-center gap-1.5 p-2 bg-slate-50 shrink-0 border-b border-slate-200 no-print">
        <form id="filter-form" action="{{ route('informes.its') }}" method="GET" class="flex flex-1 items-center gap-2 mb-0">
            <div class="flex items-center gap-1.5 flex-1">
                <div class="w-16">
                    <select name="ano" class="filter-select w-full ajax-filter">
                        @foreach($anos as $a)
                            <option value="{{ $a }}" {{ $a == $ano ? 'selected' : '' }}>{{ $a }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-24">
                    <select name="mes" class="filter-select w-full ajax-filter">
                        @foreach($meses as $m)
                            <option value="{{ $m }}" {{ $m == $mes ? 'selected' : '' }}>{{ $m }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-32">
                    <select name="jornada" class="filter-select w-full ajax-filter">
                        <option value="TODAS">JORNADA</option>
                        @foreach($jornadas as $j)
                            <option value="{{ $j }}" {{ $j == $jornada ? 'selected' : '' }}>{{ $j }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="flex items-center gap-1.5 ml-auto">
                <button type="button" onclick="toggleFullScreen()" class="btn-action-fullscreen" title="Pantalla Completa"><i class="bi bi-arrows-fullscreen" id="fullScreenIcon"></i></button>
                <button type="button" onclick="window.print()" class="btn-action-print" title="Imprimir"><i class="bi bi-printer"></i></button>
                <a href="{{ route('informes.its.export', request()->all()) }}" class="font-medium flex items-center justify-center rounded h-7 w-7 text-[10px] bg-emerald-600 text-white hover:bg-emerald-700 transition-all shadow-sm" title="Exportar Excel">
                    <i class="bi bi-file-earmark-excel"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="flex-1 p-0 overflow-hidden relative">
        <div class="table-responsive h-full overflow-auto">
            <table class="table table-bordered table-sm text-center mb-0 mx-auto" id="itsTable" style="table-layout: fixed; width: 1450px;">
            <colgroup>
                <col style="width: 230px;"> {{-- Patologia Label (Unified) --}}
                <col style="width: 30px;">  {{-- Diag N --}}
                <col style="width: 30px;">  {{-- Diag S --}}
                <col style="width: 30px;">  {{-- Sex H --}}
                <col style="width: 30px;">  {{-- Sex M --}}
                @for($i=0; $i<18; $i++)
                    <col style="width: 30px;"> {{-- Age Ranges --}}
                @endfor
                @for($i=0; $i<14; $i++)
                    <col style="width: 30px;"> {{-- Pop Distribution & Contacts --}}
                @endfor
            </colgroup>
            <thead class="thead-premium sticky-top">
                <!-- Nivel 1 -->
                <tr>
                    <th rowspan="3" data-col-idx="0" class="align-middle  sticky-col-first" style="width: 230px; text-align: center;">PATOLOGIA</th>
                    <th colspan="2" class="bg-info-soft">DIAGNOSTICO</th>
                    <th colspan="2" class="bg-success-soft">SEXO</th>
                    <th colspan="18" class="bg-warning-soft">GRUPOS DE EDAD</th>
                    <th colspan="14" class="bg-purple-soft">DISTRIBUCION POR GRUPOS DE POBLACION</th>
                </tr>
                <!-- Nivel 2 -->
                <tr>
                    <th rowspan="2" data-col-idx="1" class="align-middle col-age-header">N</th>
                    <th rowspan="2" data-col-idx="2" class="align-middle col-age-header">S</th>
                    <th rowspan="2" data-col-idx="3" class="align-middle col-age-header">H</th>
                    <th rowspan="2" data-col-idx="4" class="align-middle col-age-header">M</th>
                    @php $ageRanges = ['< 1 AÑO', '1-4', '5-9', '10-14', '15-19', '20-24', '25-29', '30-49', '50+']; @endphp
                    @foreach($ageRanges as $index => $range)
                        <th colspan="2" class="col-age-header">{{ $range }}</th>
                    @endforeach
                    @php $popGroups = ['PG HOM', 'PG MUJ', 'PG EMB', 'TS HOM', 'TS MUJ', 'TS EMB']; @endphp
                    @foreach($popGroups as $index => $pg)
                        <th colspan="2" class="col-age-header">{{ $pg }}</th>
                    @endforeach
                    <th colspan="2" class="col-age-header">CONTAC</th>
                </tr>
                <!-- Nivel 3 -->
                <tr>
                    @for($i=0; $i<9; $i++)
                        <th data-col-idx="{{ 5 + ($i*2) }}" class="col-age-header">H</th>
                        <th data-col-idx="{{ 6 + ($i*2) }}" class="col-age-header">M</th>
                    @endfor
                    @for($i=0; $i<6; $i++)
                        <th data-col-idx="{{ 23 + ($i*2) }}" class="col-age-header">N</th>
                        <th data-col-idx="{{ 24 + ($i*2) }}" class="col-age-header">S</th>
                    @endfor
                    <th data-col-idx="35" class="col-age-header">H</th>
                    <th data-col-idx="36" class="col-age-header">M</th>
                </tr>
            </thead>
            <tbody>
                @php 
                    $currentCat = ''; 
                    $catCounts = [];
                    foreach($finalData as $row) {
                        $cat = $row['category'];
                        $catCounts[$cat] = ($catCounts[$cat] ?? 0) + 1;
                    }
                    $catsPrinted = [];
                @endphp

                @foreach($finalData as $row)
                    <tr>
                        <td data-col-idx="0" class="text-left font-weight-bold sticky-col-first" style="width: 230px;">
                            {{ $row['label'] }}
                        </td>
                        
                        @for($i=0; $i<36; $i++)
                            @if(isset($row['cols'][$i]))
                                <td data-col-idx="{{ $i + 1 }}" 
                                    class="col-mini-text {{ $row['cols'][$i] > 0 ? 'text-bold cursor-pointer hover:bg-blue-200 hover:text-blue-950 font-bold transition-all' : 'text-muted' }}"
                                    @if($row['cols'][$i] > 0)
                                        onclick="openItsDetailsModal('{{ addslashes($row['label']) }}', {{ $i }})"
                                        title="Ver {{ $row['cols'][$i] }} registro(s) de {{ $row['label'] }}"
                                    @endif>
                                    {{ $row['cols'][$i] ?: '-' }}
                                </td>
                            @endif
                        @endfor
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td class="sticky-col-first text-right pr-3" style="width: 230px;">TOTAL GENERAL</td>
                    @for($i=0; $i<36; $i++)
                        <td data-col-idx="{{ $i + 1 }}">
                            {{ $totalGeneral[$i] ?: '0' }}
                        </td>
                    @endfor
                </tr>
            </tfoot>
        </table>
    </div>
</div>
