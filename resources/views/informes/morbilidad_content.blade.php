    <!-- Barra de Filtros en Una Sola Fila Horizontal -->
    <div class="filter-container no-print mb-2"
        style="background: var(--bg-surface) !important; border: 1px solid var(--border-color) !important; border-radius: var(--radius-md, 10px) !important; padding: 0.5rem 0.85rem !important; display: flex !important; flex-direction: row !important; align-items: center !important; justify-content: space-between !important; flex-wrap: nowrap !important; gap: 8px !important; box-shadow: var(--shadow-sm) !important; width: 100% !important;">
        <form id="filter-form" action="{{ route('informes.morbilidad') }}" method="GET"
            style="display: flex !important; flex-direction: row !important; align-items: center !important; flex-wrap: nowrap !important; gap: 8px !important; margin: 0 !important; flex: 1 1 auto !important; min-width: 0 !important;">
            <!-- Año -->
            <div style="width: 90px !important; min-width: 90px !important; flex-shrink: 0 !important;">
                <select name="ano" class="filter-select w-full ajax-filter"
                    style="width: 100% !important; height: 34px !important; font-weight: 700 !important; font-size: 0.82rem !important;">
                    @foreach($anos as $a)
                        <option value="{{ $a }}" {{ $a == $ano ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Mes -->
            <div style="width: 130px !important; min-width: 130px !important; flex-shrink: 0 !important;">
                <select name="mes" class="filter-select w-full ajax-filter"
                    style="width: 100% !important; height: 34px !important; font-weight: 700 !important; font-size: 0.82rem !important;">
                    @foreach($meses as $m)
                        <option value="{{ $m }}" {{ $m == $mes ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Jornada -->
            <div style="width: 140px !important; min-width: 140px !important; flex-shrink: 0 !important;">
                <select name="jornada" class="filter-select w-full ajax-filter"
                    style="width: 100% !important; height: 34px !important; font-weight: 700 !important; font-size: 0.82rem !important;">
                    <option value="TODAS">JORNADA</option>
                    @foreach($jornadas as $j)
                        <option value="{{ $j }}" {{ $j == $jornada ? 'selected' : '' }}>{{ $j }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Alternancia de Páginas -->
            <div style="display: inline-flex !important; flex-direction: row !important; align-items: center !important; background: var(--bg-subtle) !important; padding: 2px !important; border-radius: var(--radius-sm, 8px) !important; border: 1px solid var(--border-color) !important; flex-shrink: 0 !important; margin-left: 4px !important;">
                <button type="button" onclick="setPage(1)" id="btn-page-1" 
                        class="page-btn active-page-btn px-3 py-1 text-[11px] font-bold rounded transition-all">
                    ANVERSO
                </button>
                <button type="button" onclick="setPage(2)" id="btn-page-2" 
                        class="page-btn inactive-page-btn px-3 py-1 text-[11px] font-bold rounded transition-all">
                    REVERSO
                </button>
            </div>
        </form>

        <!-- Acciones a la derecha -->
        <div style="display: flex !important; flex-direction: row !important; align-items: center !important; gap: 6px !important; flex-shrink: 0 !important; margin-left: auto !important;">
            <button type="button" class="btn btn-sm btn-subtle-primary" onclick="abrirModalComparacion()" title="Auditoría y Comparación Cruzada" style="height: 32px; font-weight: 700; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 4px; padding: 0 10px;">
                <i class="bi bi-diagram-3-fill"></i> Comparar
            </button>
            <button type="button" onclick="toggleFullScreen()" class="btn-action-fullscreen" title="Pantalla Completa" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;"><i class="bi bi-arrows-fullscreen" id="fullScreenIcon"></i></button>
            <button type="button" onclick="window.print()" class="btn-action-print" title="Imprimir" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;"><i class="bi bi-printer"></i></button>
            <a href="{{ route('informes.morbilidad.export', request()->all()) }}" class="font-medium rounded text-[11px] bg-emerald-600 text-white hover:bg-emerald-700 transition-all shadow-sm" title="Exportar Excel" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
                <i class="bi bi-file-earmark-excel"></i>
            </a>
        </div>
    </div>

     <style>
        /* ═══════════════════════════════════════════════════
           INFORME DE MORBILIDAD - RECUADROS Y FONDOS 100% SÓLIDOS
           ═══════════════════════════════════════════════════ */
        :root {
            --morb-border-internal: #cbd5e1;
            --morb-rango-border: #0284c7; /* Azul / Cyan en Light Mode */
            --morb-rango-hdr-bg: #e0f2fe; /* 100% Sólido en Light Mode */
            --morb-hom-bg: #eff6ff;
            --morb-muj-bg: #fff1f2;
            --morb-ns-bg: #ffffff;
            --morb-diag-bg: #f1f5f9;
            --morb-suma-bg: #0f172a;
            --morb-rango-alt-bg: #f8fafc;
            --morb-rango-alt-hover: #f1f5f9;
        }

        [data-theme="dark"] {
            --morb-border-internal: rgba(255, 255, 255, 0.12);
            --morb-rango-border: #38bdf8; /* Cyan luminoso en Dark Mode */
            --morb-rango-hdr-bg: #0f2b48; /* 100% Sólido Azul Petróleo en Dark Mode */
            --morb-hom-bg: #132742;
            --morb-muj-bg: #2d1822;
            --morb-ns-bg: #0b1320;
            --morb-diag-bg: #172338;
            --morb-suma-bg: #030712;
            --morb-rango-alt-bg: #0c1524;
            --morb-rango-alt-hover: #121e33;
        }

        #morbilidadTable {
            border-collapse: separate !important;
            border-spacing: 0 !important;
            background-color: var(--bg-surface, #ffffff);
        }

        /* Cada celda dibuja su cuadrícula fina interna con padding-box */
        #morbilidadTable th,
        #morbilidadTable td {
            border: 1px solid var(--morb-border-internal) !important;
            background-clip: padding-box !important;
            box-sizing: border-box;
        }

        /* ── thead sticky: fondo 100% OPACO para evitar transparencias al hacer scroll ── */
        #morbilidadTable thead {
            position: sticky;
            top: 0;
            z-index: 50;
        }

        .thead-premium-v2 th {
            padding: 4px 2px !important;
            font-size: 0.75rem;
            vertical-align: middle !important;
            text-transform: uppercase;
        }

        /* ─── ENCABEZADOS DE RANGOS DE EDAD (NIVEL 1) ─── */
        #morbilidadTable thead tr:first-child th.th-rango-box {
            border: 1.5px solid var(--morb-rango-border) !important;
            box-shadow: inset 0 0 0 0.5px var(--morb-rango-border) !important;
            background-color: var(--morb-rango-hdr-bg) !important;
            color: var(--morb-rango-border) !important;
            font-weight: 800 !important;
            letter-spacing: 0.5px;
        }

        /* Fila 2 – HOM (Sólido) */
        .th-hom {
            background-color: var(--morb-hom-bg) !important;
            color: #2563eb !important;
        }
        [data-theme="dark"] .th-hom,
        html.dark .th-hom {
            color: #60a5fa !important;
        }

        /* Fila 2 – MUJ (Sólido) */
        .th-muj {
            background-color: var(--morb-muj-bg) !important;
            color: #e11d48 !important;
        }
        [data-theme="dark"] .th-muj,
        html.dark .th-muj {
            color: #fb7185 !important;
        }

        /* Fila 3 – N / S (Sólido) */
        #morbilidadTable thead tr:nth-child(3) th {
            background-color: var(--morb-ns-bg) !important;
            color: var(--text-muted, #64748b) !important;
        }
        [data-theme="dark"] #morbilidadTable thead tr:nth-child(3) th {
            color: #94a3b8 !important;
        }

        /* Borde inferior del thead (Nivel 3) */
        #morbilidadTable thead tr:last-child th {
            border-bottom: 2px solid var(--morb-rango-border) !important;
            box-shadow: inset 0 -2px 0 var(--morb-rango-border) !important;
        }

        /* ─── MARCO LATERAL DE CADA RANGO (1.5px) ─── */
        #morbilidadTable .th-rango-start,
        #morbilidadTable .td-rango-start {
            border-left: 1.5px solid var(--morb-rango-border) !important;
            box-shadow: inset 1.5px 0 0 var(--morb-rango-border) !important;
        }

        #morbilidadTable .th-rango-end,
        #morbilidadTable .td-rango-end {
            border-right: 1.5px solid var(--morb-rango-border) !important;
            box-shadow: inset -1.5px 0 0 var(--morb-rango-border) !important;
        }

        /* Combinaciones en cabecera Nivel 3 */
        #morbilidadTable thead tr:last-child th.th-rango-start {
            box-shadow: inset 1.5px 0 0 var(--morb-rango-border), inset 0 -2px 0 var(--morb-rango-border) !important;
        }
        #morbilidadTable thead tr:last-child th.th-rango-end {
            box-shadow: inset -1.5px 0 0 var(--morb-rango-border), inset 0 -2px 0 var(--morb-rango-border) !important;
        }

        /* ── Celda DIAGNOSTICO sticky (columna izquierda 100% sólida) ── */
        .sticky-col-first-v2 {
            position: sticky;
            left: 0;
            z-index: 30 !important;
            background-color: var(--morb-diag-bg) !important;
            color: var(--text-primary, #1e293b) !important;
            border-right: 2px solid var(--morb-rango-border) !important;
            box-shadow: inset -2px 0 0 var(--morb-rango-border), 3px 0 6px rgba(0, 0, 0, 0.15) !important;
        }
        [data-theme="dark"] .sticky-col-first-v2 {
            color: #f8fafc !important;
        }

        #morbilidadTable tbody .sticky-col-first-v2 {
            background-color: var(--bg-surface, #fff) !important;
            color: var(--text-primary, #1e293b) !important;
        }
        [data-theme="dark"] #morbilidadTable tbody .sticky-col-first-v2 {
            background-color: #0b1120 !important;
            color: #e2e8f0 !important;
        }

        #morbilidadTable tbody td {
            background-color: var(--bg-surface, #ffffff);
            color: var(--text-primary, #1e293b);
        }
        [data-theme="dark"] #morbilidadTable tbody td {
            background-color: #0b1120;
            color: #f8fafc;
        }

        /* ── Celda SUMA (rowspan 3 y total final 100% sólida) ── */
        #morbilidadTable .th-suma,
        #morbilidadTable .td-suma {
            background-color: var(--morb-suma-bg) !important;
            color: #fff !important;
            border-left: 2px solid var(--morb-rango-border) !important;
            border-right: 2px solid var(--morb-rango-border) !important;
            box-shadow: inset 2px 0 0 var(--morb-rango-border), inset -2px 0 0 var(--morb-rango-border) !important;
        }
        [data-theme="dark"] #morbilidadTable .th-suma,
        [data-theme="dark"] #morbilidadTable .td-suma {
            color: #f8fafc !important;
        }

        /* ─── PIE DE TABLA (TFOOT) ─── */
        #morbilidadTable tfoot .sticky-col-first-v2 {
            background-color: #1e1b4b !important;
            color: #fff !important;
        }
        [data-theme="dark"] #morbilidadTable tfoot .sticky-col-first-v2 {
            background-color: #0f172a !important;
            color: #fff !important;
        }

        #morbilidadTable tfoot td {
            border-top: 2px solid var(--morb-rango-border) !important;
            box-shadow: inset 0 2px 0 var(--morb-rango-border) !important;
            background-color: #0f172a !important;
            color: #fff !important;
        }
        [data-theme="dark"] #morbilidadTable tfoot td {
            background-color: #030712 !important;
        }

        /* ─── CARRILES ALTERNADOS POR RANGO (FONDO SÓLIDO Y SUAVE) ─── */
        #morbilidadTable tbody td.col-rango-alt {
            background-color: var(--morb-rango-alt-bg) !important;
        }
        #morbilidadTable tbody tr:hover td.col-rango-alt {
            background-color: var(--morb-rango-alt-hover) !important;
        }

        .page-btn { cursor: pointer; }
        .active-page-btn   { background-color: #1e293b; color: #fff; box-shadow: 0 1px 2px rgba(0,0,0,0.2); }
        .inactive-page-btn { color: #64748b; }
        .inactive-page-btn:hover { background-color: #cbd5e1; }
        html.dark .active-page-btn { background-color: #3b82f6; color: #fff; }
        html.dark .inactive-page-btn { color: #94a3b8; }
        html.dark .inactive-page-btn:hover { background-color: #334155; }
        [data-theme="dark"] .active-page-btn { background-color: #3b82f6; color: #fff; }
        [data-theme="dark"] .inactive-page-btn { color: #94a3b8; }
        [data-theme="dark"] .inactive-page-btn:hover { background-color: #334155; }

        .row-page-1, .row-page-2 { transition: opacity 0.2s; }
        .d-none { display: none !important; }
    </style>

    <div class="flex-1 p-0 overflow-hidden relative">
        <div class="table-responsive h-full overflow-auto">
            <table class="table table-sm text-center mb-0" id="morbilidadTable" style="table-layout: fixed; width: 1726px; border-collapse: separate; border-spacing: 0;">
                    <thead class="thead-premium-v2 sticky-top" style="z-index: 50;">
                        <!-- Nivel 1: Rangos de Edad -->
                        @php 
                            $ageRanges = [
                                '< 1 AÑO', '1 A 4 A.', '5 A 14 A.', '15 A 19', 
                                '20 A 49', '50 A 59', '60 AÑOS Y MÁS', 'TOTAL'
                            ];
                        @endphp
                        <tr>
                            <th rowspan="3" class="sticky-col-first-v2 align-middle" style="width: 280px; min-width: 280px; font-weight: 800;">DIAGNOSTICO</th>
                            @foreach($ageRanges as $rIdx => $range)
                                @php $rangeClass = ($rIdx % 2 === 1) ? 'col-rango-alt' : ''; @endphp
                                <th colspan="4" class="th-rango-box {{ $rangeClass }} font-bold" style="font-size: 0.85rem; height: 35px;">
                                    {{ $range }}
                                </th>
                            @endforeach
                            <th rowspan="3" class="align-middle font-black th-suma" style="width: 70px;">SUMA</th>
                        </tr>
                        <!-- Nivel 2: Sexo -->
                        <tr>
                            @foreach($ageRanges as $rIdx => $range)
                                @php $rangeClass = ($rIdx % 2 === 1) ? 'col-rango-alt' : ''; @endphp
                                <th colspan="2" class="font-semibold th-hom th-rango-start {{ $rangeClass }}">HOM</th>
                                <th colspan="2" class="font-semibold th-muj th-rango-end {{ $rangeClass }}">MUJ</th>
                            @endforeach
                        </tr>
                        <!-- Nivel 3: Condición (N / S) -->
                        <tr>
                            @foreach($ageRanges as $rIdx => $range)
                                @php $rangeClass = ($rIdx % 2 === 1) ? 'col-rango-alt' : ''; @endphp
                                <th class="font-medium th-rango-start {{ $rangeClass }}" style="width: 43px;">N</th>
                                <th class="font-medium {{ $rangeClass }}" style="width: 43px;">S</th>
                                <th class="font-medium {{ $rangeClass }}" style="width: 43px;">N</th>
                                <th class="font-medium th-rango-end {{ $rangeClass }}" style="width: 43px;">S</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody id="morbilidad-body">
                        @php $curPage = 1; @endphp
                        @foreach($finalData as $row)
                            @if(isset($row['is_extra']))
                                @php $curPage = 2; @endphp
                                <tr class="row-page-1" style="height: 10px; line-height: 10px;">
                                    <td colspan="34" style="background-color: #1e293b; padding: 0 !important; border: 2px solid #334155 !important; height: 10px;"></td>
                                </tr>
                            @else
                                <tr class="row-page-{{ $curPage }} {{ $row['color'] }} {{ $curPage == 2 ? 'd-none' : '' }}">
                                    <td class="sticky-col-first-v2 text-left px-3 {{ $row['color'] ?: '' }} font-bold" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; height: 28px;">
                                        {{ $row['label'] }}
                                    </td>
                                    @for($col=1; $col<=32; $col++)
                                        @php 
                                            $val = $row['cols'][$col] ?? 0;
                                            $isStart = ($col % 4 == 1);
                                            $isEnd = ($col % 4 == 0);
                                            $rangeIdx = intdiv($col - 1, 4);
                                            $rangeClass = ($rangeIdx % 2 === 1) ? 'col-rango-alt' : '';
                                        @endphp
                                        <td class="td-rango-cell {{ $rangeClass }} {{ $isStart ? 'td-rango-start' : '' }} {{ $isEnd ? 'td-rango-end' : '' }} {{ $val > 0 ? 'font-bold clickable-cell' : 'opacity-40 text-slate-400' }}" 
                                            style="font-size: 1.15rem; {{ $val > 0 ? 'cursor: pointer;' : '' }}"
                                            @if($val > 0) onclick="fetchMorbilidadDetails('{{ $row['id'] }}', {{ $col }})" title="Ver detalle de pacientes ({{ $val }})" @endif>
                                            {{ $val > 0 ? $val : '--' }}
                                        </td>
                                    @endfor
                                    <td class="td-suma font-bold {{ ($row['total'] ?? 0) > 0 ? 'clickable-cell' : '' }}" 
                                        style="font-size: 1.15rem; {{ ($row['total'] ?? 0) > 0 ? 'cursor: pointer;' : '' }}"
                                        @if(($row['total'] ?? 0) > 0) onclick="fetchMorbilidadDetails('{{ $row['id'] }}', 'suma')" title="Ver todos los pacientes del mes ({{ $row['total'] }})" @endif>
                                        {{ $row['total'] ?: '0' }}
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot class="text-white sticky-bottom hover:bg-slate-900 transition-colors" style="z-index: 60; bottom: -1px; position: sticky; background-color: #1e293b !important;">
                        <tr>
                            <td class="sticky-col-first-v2 text-right pr-4 uppercase font-bold" style="z-index: 70;">TOTAL GENERAL</td>
                            @for($col=1; $col<=32; $col++)
                                @php 
                                    $isStart = ($col % 4 == 1);
                                    $isEnd = ($col % 4 == 0);
                                    $rangeIdx = intdiv($col - 1, 4);
                                    $rangeClass = ($rangeIdx % 2 === 1) ? 'col-rango-alt' : '';
                                @endphp
                                <td class="{{ $rangeClass }} {{ $isStart ? 'td-rango-start' : '' }} {{ $isEnd ? 'td-rango-end' : '' }} font-bold">{{ $totalGeneral[$col] ?: '0' }}</td>
                            @endfor
                            <td class="td-suma px-2 font-bold">{{ array_sum(array_slice($totalGeneral, 1, 28)) }}</td>
                        </tr>
                    </tfoot>
                </table>
            </div>
        </div>
    

    <!-- Modal de Detalles de Pacientes en Morbilidad -->
    <div class="modal fade" id="modalMorbilidadDetalles" tabindex="-1" role="dialog" aria-labelledby="modalMorbilidadDetallesLabel" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document" style="max-width: 95vw; width: 1200px;">
            <div class="modal-content" style="background: var(--bg-surface); border: 1px solid var(--border-color); color: var(--text-primary); border-radius: var(--radius-md); box-shadow: var(--shadow-xl);">
                <div class="modal-header d-flex align-items-center justify-content-between" style="background-color: var(--bg-subtle); border-bottom-color: var(--border-color); padding: 1rem 1.5rem;">
                    <div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-person-lines-fill text-primary" style="font-size: 1.25rem;"></i>
                            <h5 class="modal-title font-weight-bold mb-0" id="modalMorbilidadTitle" style="color: var(--text-primary); letter-spacing: 0.3px;"></h5>
                        </div>
                        <div class="d-flex align-items-center gap-2 mt-1">
                            <span class="badge badge-subtle-primary font-weight-bold" id="modalMorbilidadFiltroBadge" style="font-size: 0.78rem;"></span>
                            <span class="badge badge-subtle-secondary" id="modalMorbilidadCountBadge" style="font-size: 0.78rem;"></span>
                        </div>
                    </div>
                    <div class="d-flex align-items-center gap-2">
                        <div class="input-group input-group-sm" style="width: 250px;">
                            <div class="input-group-prepend">
                                <span class="input-group-text" style="background: var(--input-bg); border-color: var(--input-border); color: var(--text-muted);"><i class="bi bi-search"></i></span>
                            </div>
                            <input type="text" id="modalMorbilidadSearch" class="form-control form-control-sm" placeholder="Buscar paciente, médico, exp..." style="background: var(--input-bg); border-color: var(--input-border); color: var(--text-primary);">
                        </div>
                        <button type="button" class="btn btn-icon btn-sm btn-subtle" data-dismiss="modal" aria-label="Close">
                            <i class="bi bi-x-lg"></i>
                        </button>
                    </div>
                </div>

                <div class="modal-body p-0">
                    <div class="table-responsive" style="max-height: 500px; overflow-y: auto;">
                        <table class="table table-sm table-hover mb-0" id="modalMorbilidadTable" style="font-size: 0.82rem; border-collapse: separate; border-spacing: 0;">
                            <thead style="position: sticky; top: 0; z-index: 10;">
                                <tr style="background: var(--bg-subtle); color: var(--text-muted); border-bottom: 2px solid var(--border-color);">
                                    <th class="py-2 px-2 text-center text-nowrap" style="width: 75px;">N° Reg.</th>
                                    <th class="py-2 px-3 text-nowrap">Fecha</th>
                                    <th class="py-2 px-3 text-nowrap">Expediente</th>
                                    <th class="py-2 px-3 text-nowrap">Identidad</th>
                                    <th class="py-2 px-3 text-nowrap">Paciente</th>
                                    <th class="py-2 px-2 text-center text-nowrap">Sexo</th>
                                    <th class="py-2 px-2 text-center text-nowrap">Edad</th>
                                    <th class="py-2 px-2 text-center text-nowrap">Cond.</th>
                                    <th class="py-2 px-3 text-nowrap">Diagnóstico Registrado</th>
                                    <th class="py-2 px-3 text-nowrap">Médico / Quien atendió</th>
                                    <th class="py-2 px-2 text-center text-nowrap">Jornada</th>
                                </tr>
                            </thead>
                            <tbody id="modalMorbilidadTableBody">
                                <!-- Renderizado dinámico -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <div class="modal-footer d-flex justify-content-between align-items-center" style="background-color: var(--bg-subtle); border-top-color: var(--border-color); padding: 0.75rem 1.5rem;">
                    <span class="text-muted small" id="modalMorbilidadFooterInfo">Haga clic en cualquier fila para más contexto.</span>
                    <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal" style="font-weight: 600;">Cerrar</button>
                </div>
            </div>
        </div>
    </div>

    <script>
        function setPage(pageNum) {
            const rows1 = document.querySelectorAll('.row-page-1');
            const rows2 = document.querySelectorAll('.row-page-2');
            const btn1 = document.getElementById('btn-page-1');
            const btn2 = document.getElementById('btn-page-2');

            if (pageNum === 1) {
                rows1.forEach(r => r.classList.remove('d-none'));
                rows2.forEach(r => r.classList.add('d-none'));
                btn1.classList.add('active-page-btn');
                btn1.classList.remove('inactive-page-btn');
                btn2.classList.add('inactive-page-btn');
                btn2.classList.remove('active-page-btn');
            } else {
                rows1.forEach(r => r.classList.add('d-none'));
                rows2.forEach(r => r.classList.remove('d-none'));
                btn2.classList.add('active-page-btn');
                btn2.classList.remove('inactive-page-btn');
                btn1.classList.add('inactive-page-btn');
                btn1.classList.remove('active-page-btn');
            }
            
            // Reposicionar scroll al inicio al cambiar página
            const container = document.querySelector('.table-responsive');
            if (container) container.scrollTop = 0;
        }
    </script>
