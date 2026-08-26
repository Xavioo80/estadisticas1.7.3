    <div class="filter-container flex flex-wrap items-center gap-1.5 p-2 bg-slate-50 shrink-0 border-b border-slate-200 no-print">
        <form id="filter-form" action="{{ route('informes.at2r-rsm') }}" method="GET" class="flex flex-1 items-center gap-2 mb-0">
            <div class="flex items-center gap-1.5">
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
            </div>

            <div class="h-6 w-[1px] bg-slate-200 mx-1"></div>

            <div class="flex items-center gap-1.5 flex-1">
                <div class="w-28">
                    <select name="prof" class="filter-select w-full ajax-filter">
                        <option value="TODAS">PROFESIÓN</option>
                        @foreach($profesiones as $p)
                            <option value="{{ $p }}" {{ $p == $profFilter ? 'selected' : '' }}>{{ $p }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 max-w-[180px]">
                    <select name="medico" class="filter-select w-full ajax-filter">
                        <option value="TODOS">MÉDICO</option>
                        @foreach($nombresMedicos as $nm)
                            <option value="{{ $nm }}" {{ $nm == $medicoFilter ? 'selected' : '' }}>{{ $nm }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-24">
                    <select name="sexo" class="filter-select w-full ajax-filter">
                        <option value="AMBOS">SEXO</option>
                        <option value="M" {{ $sexoFilter == 'M' ? 'selected' : '' }}>MUJER</option>
                        <option value="H" {{ $sexoFilter == 'H' ? 'selected' : '' }}>HOMBRE</option>
                    </select>
                </div>
                <div class="w-24">
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
                <a href="{{ route('informes.at2r-rsm.export', request()->all()) }}" class="font-medium flex items-center justify-center rounded h-7 w-7 text-[10px] bg-emerald-600 text-white hover:bg-emerald-700 transition-all shadow-sm" title="Exportar Excel">
                    <i class="bi bi-file-earmark-excel"></i>
                </a>
            </div>
        </form>
    </div>

    <div class="flex-1 p-0 overflow-hidden relative">
        <div class="table-responsive h-full overflow-auto">
                <table class="table table-bordered table-sm text-center mb-0" id="at2rRsmTable">
                    <thead class="thead-premium sticky-top">
                        <tr class="bg-light">
                            <th rowspan="2" class="sticky-col-first align-middle " style="width: 400px; min-width: 400px;">CONCEPTO</th>
                            <th colspan="2" class="text-primary">ENFERMERA</th>
                            <th colspan="2" class="text-danger">MÉDICO</th>
                            <th rowspan="2" class="align-middle bg-primary-soft" style="width: 100px;">TOTAL</th>
                        </tr>
                        <tr>
                            <th style="width: 120px;">AUXILIARES</th>
                            <th style="width: 120px;">LICENCIADAS</th>
                            <th style="width: 120px;">GENERAL</th>
                            <th style="width: 120px;">ESPECIALISTA</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($finalData as $row)
                            @if(isset($row['header']) && $row['header'])
                                <tr class="{{ $row['color'] }}">
                                    <td colspan="6" class="font-weight-bold text-center py-2" >{{ $row['label'] }}</td>
                                </tr>
                            @else
                                <tr class="{{ $row['color'] }}">
                                    <td class="sticky-col-first text-left pl-3 {{ $row['color'] ? '' : '' }}">
                                        {{ $row['label'] }}
                                    </td>
                                    <td>{{ $row['cols'][1] ?: '0' }}</td>
                                    <td>{{ $row['cols'][2] ?: '0' }}</td>
                                    <td>{{ $row['cols'][3] ?: '0' }}</td>
                                    <td>{{ $row['cols'][4] ?: '0' }}</td>
                                    <td class="font-weight-bold ">{{ $row['total'] ?: '0' }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    
