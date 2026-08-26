    <div class="filter-container flex flex-wrap items-center gap-1.5 p-2 bg-slate-50 shrink-0 border-b border-slate-200 no-print">
        <form id="filter-form" action="{{ route('informes.morbilidad') }}" method="GET" class="flex flex-1 items-center gap-2 mb-0">
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

                <!-- Botón de Alternancia de Páginas -->
                <div class="flex bg-slate-200 p-0.5 rounded-lg border border-slate-300 ml-2 shadow-inner">
                    <button type="button" onclick="setPage(1)" id="btn-page-1" 
                            class="page-btn active-page-btn px-3 py-1 text-[10px] font-bold rounded-md transition-all">
                        ANVERSO
                    </button>
                    <button type="button" onclick="setPage(2)" id="btn-page-2" 
                            class="page-btn inactive-page-btn px-3 py-1 text-[10px] font-bold rounded-md transition-all">
                        REVERSO
                    </button>
                </div>
            </div>

            <div class="flex items-center gap-1.5 ml-auto">
                <button type="button" onclick="toggleFullScreen()" class="btn-action-fullscreen" title="Pantalla Completa"><i class="bi bi-arrows-fullscreen" id="fullScreenIcon"></i></button>
                <button type="button" onclick="window.print()" class="btn-action-print" title="Imprimir"><i class="bi bi-printer"></i></button>
                <a href="{{ route('informes.morbilidad.export', request()->all()) }}" class="font-medium flex items-center justify-center rounded h-7 w-7 text-[10px] bg-emerald-600 text-white hover:bg-emerald-700 transition-all shadow-sm" title="Exportar Excel">
                    <i class="bi bi-file-earmark-excel"></i>
                </a>
            </div>
        </form>
    </div>

    <style>
        /* ═══════════════════════════════════════════════════
           SOLUCIÓN: border-collapse:separate + border-spacing:0
           Es la única forma compatible con position:sticky
           para que los bordes no desaparezcan al hacer scroll.
        ═══════════════════════════════════════════════════ */

        /* Cada celda dibuja su propio borde derecho e inferior */
        #morbilidadTable th,
        #morbilidadTable td {
            border-right:  1px solid #000 !important;
            border-bottom: 1px solid #000 !important;
            border-top:    none !important;
            border-left:   none !important;
        }

        /* La primera fila del thead sí necesita borde superior */
        #morbilidadTable thead tr:first-child th {
            border-top: 1px solid #000 !important;
        }

        /* La primera columna (sticky) necesita borde izquierdo */
        #morbilidadTable th:first-child,
        #morbilidadTable td:first-child {
            border-left: 1px solid #000 !important;
        }

        /* ── thead sticky: fondo opaco ── */
        #morbilidadTable thead {
            position: sticky;
            top: 0;
            z-index: 50;
        }

        /* Fila 1 – rangos de edad: gris claro */
        #morbilidadTable thead tr:nth-child(1) th {
            background-color: #e2e8f0 !important;
        }
        /* Fila 1 – celdas de rangos: azul índigo muy claro */
        #morbilidadTable thead tr:nth-child(1) th[colspan="4"] {
            background-color: #eef2ff !important;
            color: #312e81 !important;
        }
        /* Celda SUMA (rowspan 3) */
        #morbilidadTable thead tr:nth-child(1) th:last-child {
            background-color: #1e293b !important;
            color: #fff !important;
        }
        /* Fila 3 – N / S */
        #morbilidadTable thead tr:nth-child(3) th {
            background-color: #ffffff !important;
            color: #64748b !important;
        }

        /* ── Celda DIAGNOSTICO sticky (columna izquierda) ── */
        .sticky-col-first-v2 {
            position: sticky;
            left: 0;
            z-index: 30 !important;
            background-color: #e2e8f0 !important;
        }
        #morbilidadTable tbody .sticky-col-first-v2 {
            background-color: #fff !important;
        }
        #morbilidadTable tfoot .sticky-col-first-v2 {
            background-color: #312e81 !important;
            color: #fff !important;
        }

        .thead-premium-v2 th {
            padding: 4px 2px !important;
            font-size: 0.75rem;
            vertical-align: middle !important;
            text-transform: uppercase;
        }

        /* Bordes gruesos en divisores de grupo */
        .border-heavy-right  { border-right:  2px solid #000 !important; }
        .border-heavy-bottom { border-bottom: 2px solid #000 !important; }

        /* Borde inferior reforzado en toda la fila N/S para separar thead del tbody */
        #morbilidadTable thead tr:last-child th {
            border-bottom: 2px solid #000 !important;
        }

        .page-btn { cursor: pointer; }
        .active-page-btn   { background-color: #1e293b; color: #fff; box-shadow: 0 1px 2px rgba(0,0,0,0.2); }
        .inactive-page-btn { color: #64748b; }
        .inactive-page-btn:hover { background-color: #cbd5e1; }

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
                        <tr >
                            <th rowspan="3" class="sticky-col-first-v2 align-middle border-heavy-right" style="width: 280px; min-width: 280px; !important;">DIAGNOSTICO</th>
                            @foreach($ageRanges as $range)
                                <th colspan="4" class="border-heavy-right font-bold" style="font-size: 0.85rem; height: 35px; background-color: #eef2ff !important; color: #312e81 !important;">{{ $range }}</th>
                            @endforeach
                            <th rowspan="3" class="align-middle font-black" style="width: 70px; background-color: #1e293b !important; color: #fff !important;">SUMA</th>
                        </tr>
                        <!-- Nivel 2: Sexo -->
                        <tr>
                            @foreach($ageRanges as $range)
                                <th colspan="2" class="border-right font-semibold" style="background-color: #eff6ff !important; color: #1e40af !important;">HOM</th>
                                <th colspan="2" class="border-heavy-right font-semibold" style="1f2 !important; color: #9f1239 !important;">MUJ</th>
                            @endforeach
                        </tr>
                        <!-- Nivel 3: Condición (N / S) -->
                        <tr class="border-heavy-bottom">
                            @foreach($ageRanges as $range)
                                <th class="font-medium" style="width: 43px; fff !important; color: #64748b !important;">N</th>
                                <th class="font-medium border-right" style="width: 43px; fff !important; color: #64748b !important;">S</th>
                                <th class="font-medium" style="width: 43px; fff !important; color: #64748b !important;">N</th>
                                <th class="font-medium border-heavy-right" style="width: 43px; fff !important; color: #64748b !important;">S</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody id="morbilidad-body">
                        @php $curPage = 1; @endphp
                        @foreach($finalData as $row)
                            @if(isset($row['is_extra']))
                                @php $curPage = 2; @endphp
                                <tr class="row-page-1" style="height: 10px; line-height: 10px;">
                                    <td colspan="34" style="background-color: #1e293b; padding: 0 !important; border: 2px solid #000 !important; height: 10px;"></td>
                                </tr>
                            @else
                                <tr class="row-page-{{ $curPage }} {{ $row['color'] }} {{ $curPage == 2 ? 'd-none' : '' }}">
                                    <td class="sticky-col-first-v2 text-left px-3 {{ $row['color'] ?: '' }} font-bold border-heavy-right text-slate-800" style="white-space: nowrap; overflow: hidden; text-overflow: ellipsis; height: 28px;">
                                        {{ $row['label'] }}
                                    </td>
                                    @for($col=1; $col<=32; $col++)
                                        <td class="{{ ($row['cols'][$col] ?? 0) > 0 ? 'text-slate-900' : 'text-slate-300' }} {{ $col % 4 == 0 ? 'border-heavy-right' : '' }}" style="font-size: 1.3rem;">
                                            {{ ($row['cols'][$col] ?? 0) > 0 ? $row['cols'][$col] : '--' }}
                                        </td>
                                    @endfor
                                    <td class="bg-slate-800 text-white" style="font-size: 1.3rem;">{{ $row['total'] ?: '0' }}</td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                    <tfoot class="text-white sticky-bottom hover:bg-slate-900 transition-colors" style="z-index: 60; bottom: -1px; position: sticky; background-color: #1e293b !important;">
                        <tr>
                            <td class="sticky-col-first-v2 text-right pr-4 border-heavy-right uppercase" style="z-index: 70; background-color: #1e293b !important;">TOTAL GENERAL</td>
                            @for($col=1; $col<=32; $col++)
                                <td class="{{ $col % 4 == 0 ? 'border-heavy-right' : '' }}" style="border-bottom: 1px solid #000 !important; border-right: 1px solid #000 !important;">{{ $totalGeneral[$col] ?: '0' }}</td>
                            @endfor
                            <td class="bg-black text-white px-2" style="border-bottom: 1px solid #000 !important; border-right: 1px solid #000 !important;">{{ array_sum(array_slice($totalGeneral, 1, 28)) }}</td>
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
