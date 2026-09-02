    <style>
        /* ═══════════════════════════════════════════════════
           INFORME DE ITS - RECUADROS POR GRUPO DE EDAD Y SECCIONES
           ═══════════════════════════════════════════════════ */
        :root {
            --its-border-internal: #cbd5e1;
            --its-group-border: #0284c7; /* Azul / Cyan en Light Mode */
            --its-hdr-edad-bg: #e0f2fe; /* Sólido */
            --its-hdr-pop-bg: #f3e8ff;  /* Sólido Púrpura */
            --its-hdr-diag-bg: #e0f7fa; /* Sólido Teal */
            --its-hdr-sex-bg: #ecfdf5;  /* Sólido Esmeralda */
            --its-diag-col-bg: #f1f5f9;
            --its-alt-bg: #f8fafc;
            --its-alt-hover: #f1f5f9;
        }

        [data-theme="dark"] {
            --its-border-internal: rgba(255, 255, 255, 0.12);
            --its-group-border: #38bdf8; /* Cyan luminoso en Dark Mode */
            --its-hdr-edad-bg: #0f2b48; /* Sólido Azul Petróleo */
            --its-hdr-pop-bg: #281b3d;  /* Sólido Púrpura Oscuro */
            --its-hdr-diag-bg: #0d2830; /* Sólido Teal Oscuro */
            --its-hdr-sex-bg: #0d2d22;  /* Sólido Esmeralda Oscuro */
            --its-diag-col-bg: #172338;
            --its-alt-bg: #0c1524;
            --its-alt-hover: #121e33;
        }

        #itsTable {
            border-collapse: separate !important;
            border-spacing: 0 !important;
            background-color: var(--bg-surface, #ffffff);
        }

        /* Cada celda dibuja su cuadrícula fina interna con padding-box */
        #itsTable th,
        #itsTable td {
            border: 1px solid var(--its-border-internal) !important;
            background-clip: padding-box !important;
            box-sizing: border-box;
        }

        /* thead sticky: fondo 100% OPACO para evitar transparencias al hacer scroll */
        #itsTable thead {
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .thead-premium th {
            padding: 4px 2px !important;
            font-size: 0.75rem;
            vertical-align: middle !important;
            text-transform: uppercase;
        }

        /* ─── ENCABEZADOS PRINCIPALES (NIVEL 1) ─── */
        #itsTable thead tr:first-child th.th-section-diag {
            border: 1.5px solid var(--its-group-border) !important;
            box-shadow: inset 0 0 0 0.5px var(--its-group-border) !important;
            background-color: var(--its-hdr-diag-bg) !important;
            color: var(--text-primary) !important;
            font-weight: 800 !important;
        }
        #itsTable thead tr:first-child th.th-section-sex {
            border: 1.5px solid var(--its-group-border) !important;
            box-shadow: inset 0 0 0 0.5px var(--its-group-border) !important;
            background-color: var(--its-hdr-sex-bg) !important;
            color: var(--text-primary) !important;
            font-weight: 800 !important;
        }
        #itsTable thead tr:first-child th.th-section-edad {
            border: 1.5px solid var(--its-group-border) !important;
            box-shadow: inset 0 0 0 0.5px var(--its-group-border) !important;
            background-color: var(--its-hdr-edad-bg) !important;
            color: var(--its-group-border) !important;
            font-weight: 800 !important;
            letter-spacing: 0.5px;
        }
        #itsTable thead tr:first-child th.th-section-pop {
            border: 1.5px solid var(--its-group-border) !important;
            box-shadow: inset 0 0 0 0.5px var(--its-group-border) !important;
            background-color: var(--its-hdr-pop-bg) !important;
            color: var(--text-primary) !important;
            font-weight: 800 !important;
        }

        /* ─── ENCABEZADOS DE RANGOS INDIVIDUALES DE EDAD (NIVEL 2) ─── */
        #itsTable thead tr:nth-child(2) th.th-age-group {
            border-top: 1.5px solid var(--its-group-border) !important;
            border-left: 1.5px solid var(--its-group-border) !important;
            border-right: 1.5px solid var(--its-group-border) !important;
            background-color: var(--its-hdr-edad-bg) !important;
            color: var(--its-group-border) !important;
            font-weight: 800 !important;
        }

        #itsTable thead tr:nth-child(2) th.th-pop-group {
            border-top: 1.5px solid var(--its-group-border) !important;
            border-left: 1.5px solid var(--its-group-border) !important;
            border-right: 1.5px solid var(--its-group-border) !important;
            background-color: var(--its-hdr-pop-bg) !important;
            color: var(--text-primary) !important;
            font-weight: 700 !important;
        }

        /* Borde inferior del thead (Nivel 3) */
        #itsTable thead tr:last-child th {
            border-bottom: 2px solid var(--its-group-border) !important;
            box-shadow: inset 0 -2px 0 var(--its-group-border) !important;
            background-color: var(--bg-surface, #ffffff) !important;
            color: var(--text-muted, #64748b) !important;
        }
        [data-theme="dark"] #itsTable thead tr:last-child th {
            background-color: #0b1320 !important;
            color: #94a3b8 !important;
        }

        /* ─── MARCO LATERAL DE CADA GRUPO (1.5px) ─── */
        #itsTable .th-group-start,
        #itsTable .td-group-start {
            border-left: 1.5px solid var(--its-group-border) !important;
            box-shadow: inset 1.5px 0 0 var(--its-group-border) !important;
        }

        #itsTable .th-group-end,
        #itsTable .td-group-end {
            border-right: 1.5px solid var(--its-group-border) !important;
            box-shadow: inset -1.5px 0 0 var(--its-group-border) !important;
        }

        /* Combinaciones en cabecera Nivel 3 */
        #itsTable thead tr:last-child th.th-group-start {
            box-shadow: inset 1.5px 0 0 var(--its-group-border), inset 0 -2px 0 var(--its-group-border) !important;
        }
        #itsTable thead tr:last-child th.th-group-end {
            box-shadow: inset -1.5px 0 0 var(--its-group-border), inset 0 -2px 0 var(--its-group-border) !important;
        }

        /* ── Celda PATOLOGIA sticky (columna izquierda 100% sólida) ── */
        #itsTable .sticky-col-first {
            position: sticky;
            left: 0;
            z-index: 30 !important;
            background-color: var(--its-diag-col-bg) !important;
            color: var(--text-primary, #1e293b) !important;
            border-right: 2px solid var(--its-group-border) !important;
            box-shadow: inset -2px 0 0 var(--its-group-border), 3px 0 6px rgba(0, 0, 0, 0.15) !important;
        }
        [data-theme="dark"] #itsTable .sticky-col-first {
            color: #f8fafc !important;
        }

        #itsTable tbody .sticky-col-first {
            background-color: var(--bg-surface, #fff) !important;
            color: var(--text-primary, #1e293b) !important;
        }
        [data-theme="dark"] #itsTable tbody .sticky-col-first {
            background-color: #0b1120 !important;
            color: #e2e8f0 !important;
        }

        #itsTable tbody td {
            background-color: var(--bg-surface, #ffffff);
            color: var(--text-primary, #1e293b);
        }
        [data-theme="dark"] #itsTable tbody td {
            background-color: #0b1120;
            color: #f8fafc;
        }

        /* ─── PIE DE TABLA (TFOOT) ─── */
        #itsTable tfoot .sticky-col-first {
            background-color: #1e1b4b !important;
            color: #fff !important;
        }
        [data-theme="dark"] #itsTable tfoot .sticky-col-first {
            background-color: #0f172a !important;
            color: #fff !important;
        }

        #itsTable tfoot td {
            border-top: 2px solid var(--its-group-border) !important;
            box-shadow: inset 0 2px 0 var(--its-group-border) !important;
            background-color: #0f172a !important;
            color: #fff !important;
            font-weight: 700;
        }
        [data-theme="dark"] #itsTable tfoot td {
            background-color: #030712 !important;
        }

        /* ─── CARRILES ALTERNADOS (FONDO SUAVE) ─── */
        #itsTable tbody td.col-group-alt {
            background-color: var(--its-alt-bg) !important;
        }
        #itsTable tbody tr:hover td.col-group-alt {
            background-color: var(--its-alt-hover) !important;
        }
    </style>

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
            <table class="table table-sm text-center mb-0 mx-auto" id="itsTable" style="table-layout: fixed; width: 1450px;">
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
                    <th rowspan="3" data-col-idx="0" class="align-middle sticky-col-first" style="width: 230px; text-align: center; font-weight: 800;">PATOLOGIA</th>
                    <th colspan="2" class="th-section-diag th-group-start th-group-end">DIAGNOSTICO</th>
                    <th colspan="2" class="th-section-sex th-group-start th-group-end">SEXO</th>
                    <th colspan="18" class="th-section-edad th-group-start th-group-end">GRUPOS DE EDAD</th>
                    <th colspan="14" class="th-section-pop th-group-start th-group-end">DISTRIBUCION POR GRUPOS DE POBLACION</th>
                </tr>
                <!-- Nivel 2 -->
                <tr>
                    <th rowspan="2" data-col-idx="1" class="align-middle th-group-start">N</th>
                    <th rowspan="2" data-col-idx="2" class="align-middle th-group-end">S</th>
                    <th rowspan="2" data-col-idx="3" class="align-middle th-group-start">H</th>
                    <th rowspan="2" data-col-idx="4" class="align-middle th-group-end">M</th>
                    @php $ageRanges = ['< 1 AÑO', '1-4', '5-9', '10-14', '15-19', '20-24', '25-29', '30-49', '50+']; @endphp
                    @foreach($ageRanges as $index => $range)
                        @php $altClass = ($index % 2 === 1) ? 'col-group-alt' : ''; @endphp
                        <th colspan="2" class="th-age-group th-group-start th-group-end {{ $altClass }}">{{ $range }}</th>
                    @endforeach
                    @php $popGroups = ['PG HOM', 'PG MUJ', 'PG EMB', 'TS HOM', 'TS MUJ', 'TS EMB']; @endphp
                    @foreach($popGroups as $index => $pg)
                        @php $altClass = ($index % 2 === 1) ? 'col-group-alt' : ''; @endphp
                        <th colspan="2" class="th-pop-group th-group-start th-group-end {{ $altClass }}">{{ $pg }}</th>
                    @endforeach
                    <th colspan="2" class="th-pop-group th-group-start th-group-end">CONTAC</th>
                </tr>
                <!-- Nivel 3 -->
                <tr>
                    @for($i=0; $i<9; $i++)
                        @php $altClass = ($i % 2 === 1) ? 'col-group-alt' : ''; @endphp
                        <th data-col-idx="{{ 5 + ($i*2) }}" class="th-group-start {{ $altClass }}">H</th>
                        <th data-col-idx="{{ 6 + ($i*2) }}" class="th-group-end {{ $altClass }}">M</th>
                    @endfor
                    @for($i=0; $i<6; $i++)
                        @php $altClass = ($i % 2 === 1) ? 'col-group-alt' : ''; @endphp
                        <th data-col-idx="{{ 23 + ($i*2) }}" class="th-group-start {{ $altClass }}">N</th>
                        <th data-col-idx="{{ 24 + ($i*2) }}" class="th-group-end {{ $altClass }}">S</th>
                    @endfor
                    <th data-col-idx="35" class="th-group-start">H</th>
                    <th data-col-idx="36" class="th-group-end">M</th>
                </tr>
            </thead>
            <tbody>
                @foreach($finalData as $row)
                    <tr>
                        <td data-col-idx="0" class="text-left font-weight-bold sticky-col-first" style="width: 230px;">
                            {{ $row['label'] }}
                        </td>
                        
                        @for($i=0; $i<36; $i++)
                            @if(isset($row['cols'][$i]))
                                @php
                                    $colNum = $i + 1;
                                    $isStart = ($colNum % 2 === 1);
                                    $isEnd = ($colNum % 2 === 0);
                                    
                                    // Determinar carril alterno
                                    $isAgeCol = ($colNum >= 5 && $colNum <= 22);
                                    $isPopCol = ($colNum >= 23 && $colNum <= 36);
                                    $altClass = '';
                                    if ($isAgeCol) {
                                        $agePairIdx = intdiv($colNum - 5, 2);
                                        $altClass = ($agePairIdx % 2 === 1) ? 'col-group-alt' : '';
                                    } elseif ($isPopCol) {
                                        $popPairIdx = intdiv($colNum - 23, 2);
                                        $altClass = ($popPairIdx % 2 === 1) ? 'col-group-alt' : '';
                                    }
                                @endphp
                                <td data-col-idx="{{ $colNum }}" 
                                    class="col-mini-text {{ $altClass }} {{ $isStart ? 'td-group-start' : '' }} {{ $isEnd ? 'td-group-end' : '' }} {{ $row['cols'][$i] > 0 ? 'text-bold cursor-pointer hover:bg-blue-200 hover:text-blue-950 font-bold transition-all' : 'text-muted' }}"
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
                    <td class="sticky-col-first text-right pr-3" style="width: 230px; font-weight: 800;">TOTAL GENERAL</td>
                    @for($i=0; $i<36; $i++)
                        @php
                            $colNum = $i + 1;
                            $isStart = ($colNum % 2 === 1);
                            $isEnd = ($colNum % 2 === 0);
                            $isAgeCol = ($colNum >= 5 && $colNum <= 22);
                            $isPopCol = ($colNum >= 23 && $colNum <= 36);
                            $altClass = '';
                            if ($isAgeCol) {
                                $agePairIdx = intdiv($colNum - 5, 2);
                                $altClass = ($agePairIdx % 2 === 1) ? 'col-group-alt' : '';
                            } elseif ($isPopCol) {
                                $popPairIdx = intdiv($colNum - 23, 2);
                                $altClass = ($popPairIdx % 2 === 1) ? 'col-group-alt' : '';
                            }
                        @endphp
                        <td data-col-idx="{{ $colNum }}" class="{{ $altClass }} {{ $isStart ? 'td-group-start' : '' }} {{ $isEnd ? 'td-group-end' : '' }}">
                            {{ $totalGeneral[$i] ?: '0' }}
                        </td>
                    @endfor
                </tr>
            </tfoot>
        </table>
    </div>
</div>
