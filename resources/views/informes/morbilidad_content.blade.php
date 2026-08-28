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
           SOLUCIÓN: border-collapse:separate + border-spacing:0
           Compatible con position:sticky y modo oscuro
        ═══════════════════════════════════════════════════ */

        /* Cada celda dibuja su propio borde derecho e inferior */
        #morbilidadTable th,
        #morbilidadTable td {
            border-right:  1px solid #cbd5e1 !important;
            border-bottom: 1px solid #cbd5e1 !important;
            border-top:    none !important;
            border-left:   none !important;
        }

        html.dark #morbilidadTable th,
        html.dark #morbilidadTable td,
        [data-theme="dark"] #morbilidadTable th,
        [data-theme="dark"] #morbilidadTable td {
            border-right:  1px solid #334155 !important;
            border-bottom: 1px solid #334155 !important;
        }

        /* La primera fila del thead sí necesita borde superior */
        #morbilidadTable thead tr:first-child th {
            border-top: 1px solid #cbd5e1 !important;
        }
        html.dark #morbilidadTable thead tr:first-child th,
        [data-theme="dark"] #morbilidadTable thead tr:first-child th {
            border-top: 1px solid #334155 !important;
        }

        /* La primera columna (sticky) necesita borde izquierdo */
        #morbilidadTable th:first-child,
        #morbilidadTable td:first-child {
            border-left: 1px solid #cbd5e1 !important;
        }
        html.dark #morbilidadTable th:first-child,
        html.dark #morbilidadTable td:first-child,
        [data-theme="dark"] #morbilidadTable th:first-child,
        [data-theme="dark"] #morbilidadTable td:first-child {
            border-left: 1px solid #334155 !important;
        }

        /* ── thead sticky: fondo opaco ── */
        #morbilidadTable thead {
            position: sticky;
            top: 0;
            z-index: 50;
        }

        /* Fila 1 – rangos de edad: gris claro / oscuro */
        #morbilidadTable thead tr:nth-child(1) th {
            background-color: #e2e8f0 !important;
            color: #1e293b !important;
        }
        html.dark #morbilidadTable thead tr:nth-child(1) th,
        [data-theme="dark"] #morbilidadTable thead tr:nth-child(1) th {
            background-color: #1e293b !important;
            color: #f8fafc !important;
        }

        /* Fila 1 – celdas de rangos: azul índigo claro / oscuro */
        #morbilidadTable thead tr:nth-child(1) th[colspan="4"] {
            background-color: #eef2ff !important;
            color: #312e81 !important;
        }
        html.dark #morbilidadTable thead tr:nth-child(1) th[colspan="4"],
        [data-theme="dark"] #morbilidadTable thead tr:nth-child(1) th[colspan="4"] {
            background-color: #1e293b !important;
            color: #93c5fd !important;
        }

        /* Fila 2 – HOM */
        .th-hom {
            background-color: #eff6ff !important;
            color: #1e40af !important;
        }
        html.dark .th-hom,
        [data-theme="dark"] .th-hom {
            background-color: rgba(59, 130, 246, 0.15) !important;
            color: #60a5fa !important;
        }

        /* Fila 2 – MUJ */
        .th-muj {
            background-color: #fff1f2 !important;
            color: #9f1239 !important;
        }
        html.dark .th-muj,
        [data-theme="dark"] .th-muj {
            background-color: rgba(244, 63, 94, 0.15) !important;
            color: #fb7185 !important;
        }

        /* Celda SUMA (rowspan 3) */
        #morbilidadTable thead tr:nth-child(1) th:last-child {
            background-color: #1e293b !important;
            color: #fff !important;
        }
        html.dark #morbilidadTable thead tr:nth-child(1) th:last-child,
        [data-theme="dark"] #morbilidadTable thead tr:nth-child(1) th:last-child {
            background-color: #0f172a !important;
            color: #f8fafc !important;
        }

        /* Fila 3 – N / S */
        #morbilidadTable thead tr:nth-child(3) th {
            background-color: #ffffff !important;
            color: #64748b !important;
        }
        html.dark #morbilidadTable thead tr:nth-child(3) th,
        [data-theme="dark"] #morbilidadTable thead tr:nth-child(3) th {
            background-color: #0f172a !important;
            color: #94a3b8 !important;
        }

        /* ── Celda DIAGNOSTICO sticky (columna izquierda) ── */
        .sticky-col-first-v2 {
            position: sticky;
            left: 0;
            z-index: 30 !important;
            background-color: #e2e8f0 !important;
            color: #1e293b !important;
        }
        html.dark .sticky-col-first-v2,
        [data-theme="dark"] .sticky-col-first-v2 {
            background-color: #1e293b !important;
            color: #f8fafc !important;
        }

        #morbilidadTable tbody .sticky-col-first-v2 {
            background-color: #fff !important;
            color: #1e293b !important;
        }
        html.dark #morbilidadTable tbody .sticky-col-first-v2,
        [data-theme="dark"] #morbilidadTable tbody .sticky-col-first-v2 {
            background-color: #0f172a !important;
            color: #e2e8f0 !important;
        }

        #morbilidadTable tbody td {
            background-color: #ffffff;
            color: #1e293b;
        }
        html.dark #morbilidadTable tbody td,
        [data-theme="dark"] #morbilidadTable tbody td {
            background-color: #0b1120;
            color: #f8fafc;
        }

        #morbilidadTable tfoot .sticky-col-first-v2 {
            background-color: #312e81 !important;
            color: #fff !important;
        }
        html.dark #morbilidadTable tfoot .sticky-col-first-v2,
        [data-theme="dark"] #morbilidadTable tfoot .sticky-col-first-v2 {
            background-color: #1e293b !important;
            color: #fff !important;
        }

        .thead-premium-v2 th {
            padding: 4px 2px !important;
            font-size: 0.75rem;
            vertical-align: middle !important;
            text-transform: uppercase;
        }

        /* Bordes gruesos en divisores de grupo */
        .border-heavy-right  { border-right:  2px solid #94a3b8 !important; }
        .border-heavy-bottom { border-bottom: 2px solid #94a3b8 !important; }
        html.dark .border-heavy-right  { border-right:  2px solid #475569 !important; }
        html.dark .border-heavy-bottom { border-bottom: 2px solid #475569 !important; }
        [data-theme="dark"] .border-heavy-right  { border-right:  2px solid #475569 !important; }
        [data-theme="dark"] .border-heavy-bottom { border-bottom: 2px solid #475569 !important; }

        /* Borde inferior reforzado en toda la fila N/S para separar thead del tbody */
        #morbilidadTable thead tr:last-child th {
            border-bottom: 2px solid #94a3b8 !important;
        }
        html.dark #morbilidadTable thead tr:last-child th,
        [data-theme="dark"] #morbilidadTable thead tr:last-child th {
            border-bottom: 2px solid #475569 !important;
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
                            <th rowspan="3" class="sticky-col-first-v2 align-middle border-heavy-right" style="width: 280px; min-width: 280px;">DIAGNOSTICO</th>
                            @foreach($ageRanges as $range)
                                <th colspan="4" class="border-heavy-right font-bold" style="font-size: 0.85rem; height: 35px;">{{ $range }}</th>
                            @endforeach
                            <th rowspan="3" class="align-middle font-black" style="width: 70px;">SUMA</th>
                        </tr>
                        <!-- Nivel 2: Sexo -->
                        <tr>
                            @foreach($ageRanges as $range)
                                <th colspan="2" class="border-right font-semibold th-hom">HOM</th>
                                <th colspan="2" class="border-heavy-right font-semibold th-muj">MUJ</th>
                            @endforeach
                        </tr>
                        <!-- Nivel 3: Condición (N / S) -->
                        <tr class="border-heavy-bottom">
                            @foreach($ageRanges as $range)
                                <th class="font-medium" style="width: 43px;">N</th>
                                <th class="font-medium border-right" style="width: 43px;">S</th>
                                <th class="font-medium" style="width: 43px;">N</th>
                                <th class="font-medium border-heavy-right" style="width: 43px;">S</th>
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
                                    <td class="sticky-col-first-v2 text-left px-3 {{ $row['color'] ?: '' }} font-bold border-heavy-right" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; height: 28px;">
                                        {{ $row['label'] }}
                                    </td>
                                    @for($col=1; $col<=32; $col++)
                                        @php $val = $row['cols'][$col] ?? 0; @endphp
                                        <td class="{{ $val > 0 ? 'font-bold' : 'opacity-40 text-slate-400' }} {{ $col % 4 == 0 ? 'border-heavy-right' : '' }}" style="font-size: 1.15rem;">
                                            {{ $val > 0 ? $val : '--' }}
                                        </td>
                                    @endfor
                                    <td class="bg-slate-800 text-white font-bold" style="font-size: 1.15rem;">{{ $row['total'] ?: '0' }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot class="text-white sticky-bottom hover:bg-slate-900 transition-colors" style="z-index: 60; bottom: -1px; position: sticky; background-color: #1e293b !important;">
                        <tr>
                            <td class="sticky-col-first-v2 text-right pr-4 border-heavy-right uppercase font-bold" style="z-index: 70;">TOTAL GENERAL</td>
                            @for($col=1; $col<=32; $col++)
                                <td class="{{ $col % 4 == 0 ? 'border-heavy-right' : '' }} font-bold">{{ $totalGeneral[$col] ?: '0' }}</td>
                            @endfor
                            <td class="bg-black text-white px-2 font-bold">{{ array_sum(array_slice($totalGeneral, 1, 28)) }}</td>
                        </tr>
                    </tfoot>
                </table>
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
