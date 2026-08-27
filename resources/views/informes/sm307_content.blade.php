
    <div class="filter-container flex flex-wrap items-center gap-1.5 p-3 bg-slate-50 shrink-0 border-b border-slate-200 no-print">
        <form id="filter-form" action="{{ route('informes.sm307') }}" method="GET" class="flex flex-1 items-center gap-3 mb-0" style="max-width:1355px; margin:0 auto;">
            <input type="hidden" name="lado" id="lado-input" value="{{ $lado }}">
            <div class="flex items-center gap-2 flex-1">
                <div class="flex bg-slate-200 p-1 rounded-lg mr-3">
                    <button type="button"
                            onclick="document.getElementById('lado-input').value='obverso'; updateReport();"
                            class="px-4 py-1.5 text-[12px] font-bold uppercase rounded transition-all {{ $lado == 'obverso' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700 border-transparent' }}">
                        Anversa
                    </button>
                    <button type="button"
                            onclick="document.getElementById('lado-input').value='reverso'; updateReport();"
                            class="px-4 py-1.5 text-[12px] font-bold uppercase rounded transition-all {{ $lado == 'reverso' ? 'bg-white text-blue-600 shadow-sm' : 'text-slate-500 hover:text-slate-700 border-transparent' }}">
                        Reversa
                    </button>
                </div>

                <div class="w-32">
                    <select name="ano" class="filter-select w-full h-10 ajax-filter text-[16px] font-bold px-3">
                        @foreach($anos as $a)
                            <option value="{{ $a }}" {{ (int)$a == (int)$ano ? 'selected' : '' }}>{{ $a }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="w-40">
                    <select name="mes" class="filter-select w-full h-10 ajax-filter text-[16px] font-bold px-3">
                        @foreach($meses as $m)
                            <option value="{{ $m }}" {{ strtoupper(trim($m)) == strtoupper(trim($mes)) ? 'selected' : '' }}>
                                {{ strtoupper($m) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="w-48">
                    <select name="jornada" class="filter-select w-full h-10 ajax-filter text-[16px] font-bold px-3">
                        <option value="TODAS">JORNADA</option>
                        @foreach($jornadas as $j)
                            <option value="{{ $j }}" {{ $j == $jornada ? 'selected' : '' }}>{{ $j }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="h-8 w-[1px] bg-slate-300 mx-2"></div>
                <span class="text-sm font-bold text-slate-500 uppercase tracking-tight">
                    SM3-07: SALUD MENTAL ({{ $lado == 'obverso' ? 'ANVERSA' : 'REVERSA' }})
                </span>
            </div>

            <div class="flex items-center gap-2 ml-auto">
                <button type="button" onclick="toggleFullScreen()" class="btn-action-fullscreen" title="Pantalla Completa"><i class="bi bi-arrows-fullscreen" id="fullScreenIcon"></i></button>
                <button type="button" onclick="window.print()" class="btn-action-print" title="Imprimir"><i class="bi bi-printer"></i></button>
                <a href="{{ route('informes.sm307.export', request()->all()) }}"
                    class="font-medium flex items-center justify-center rounded h-10 w-10 text-[14px] bg-emerald-600 text-white hover:bg-emerald-700 transition-all shadow-sm"
                    title="Exportar Excel">
                    <i class="bi bi-file-earmark-excel"></i>
                </a>
            </div>
        </form>
    </div>

    <style>
        /* =====================================================
           SM307 — STICKY COLUMNS + STICKY HEADER
           col-1: width 80px  → left: 0px
           col-2: width 300px → left: 80px
           ===================================================== */

        #sm307-wrapper {
            overflow: auto;
            flex: 1;
            background: #f8fafc;
            position: relative;
        }

        #sm307Table {
            border-collapse: separate;
            border-spacing: 0;
            table-layout: fixed;
            width: 1355px;   /* 55 + 300 + 25×40 */
            min-width: 1355px;
            margin: 0 auto;
        }

        /* --- All cells base ---
           Con border-collapse:separate cada celda tiene su propio borde.
           Usamos right+bottom en todas; top solo en la primera fila del thead.
           border-left SOLO en .sc1 (columna más a la izquierda siempre). */
        #sm307Table th,
        #sm307Table td {
            border-right:  1px solid #000;
            border-bottom: 1px solid #000;
            border-top:    0;
            border-left:   0;
            padding: 2px 4px;
            font-size: 0.75rem; /* Aumentado */
            vertical-align: middle;
            text-align: center;
            box-sizing: border-box;
            background-clip: padding-box; /* Previene fugas de color tras los bordes */
        }

        /* .sc1 es siempre la columna física más a la izquierda → necesita border-left */
        #sm307Table .sc1 {
            border-left: 1px solid #000;
        }

        /* Borde superior solo en la primera fila del thead (cierre superior de la tabla) */
        #sm307Table thead tr:first-child th {
            border-top: 1px solid #000;
        }

        /* =====================================================
           STICKY COLUMN CLASSES
           sc1 → first frozen column  (left: 0)
           sc2 → second frozen column (left: 80px)
           ===================================================== */
        .sc1 {
            position: sticky;
            left: 0;
            z-index: 50; /* Por encima de los datos, por debajo de los encabezados */
            width: 55px;
            min-width: 55px;
            backface-visibility: hidden;
            transform: translateZ(0);
        }
        .sc2 {
            position: sticky;
            left: 55px;
            z-index: 50; /* Por encima de los datos, por debajo de los encabezados */
            backface-visibility: hidden;
            transform: translateZ(0);
        }

        /* =====================================================
           STICKY HEADER ROWS — Fixed positions (Hardened)
           Ladder: Corners (150) > Headers (100) > Body Cols (50)
           ===================================================== */

        /* Regular Header Cells (Not fixed columns) */
        #sm307Table thead tr th {
            z-index: 100 !important;
        }

        /* Row 1: Age Ranges */
        #sm307Table thead tr:nth-child(1) th {
            position: sticky !important;
            top: 0 !important;
            height: 32px !important;
            background: #d1d5db !important;
            color: #111827 !important;
            font-weight: 800;
            font-size: 0.6rem;
            text-transform: uppercase;
            vertical-align: middle;
            padding: 0 4px !important;
            box-sizing: border-box !important;
        }

        /* Row 2: Consult Type (h: 31px calibrado) */
        #sm307Table thead tr:nth-child(2) th {
            position: sticky !important;
            top: 32px !important;
            height: 31px !important;
            line-height: 1.1 !important;
            background: #e5e7eb !important;
            color: #1f2937 !important;
            font-weight: 700;
            font-size: 0.55rem;
            vertical-align: middle;
            padding: 0 4px !important;
            box-sizing: border-box !important;
        }

        /* Row 3: Sex (H / M) (h: 22px) */
        #sm307Table thead tr:nth-child(3) th {
            position: sticky !important;
            top: 63px !important;
            height: 22px !important;
            background: #f3f4f6 !important;
            color: #111827 !important;
            font-weight: 800;
            font-size: 0.65rem;
            vertical-align: middle;
            padding: 0 !important;
            box-sizing: border-box !important;
        }

        /* Corner Headers: HIGHEST SPECIFICITY to stay on top of everything */
        #sm307Table thead tr th.sc1,
        #sm307Table thead tr th.sc2 {
            top: 0 !important;
            z-index: 150 !important; /* Top level priority */
            height: 85px !important;
            background: #9ca3af !important;
            color: #111827 !important;
            vertical-align: middle;
            backface-visibility: hidden;
            transform: translateZ(0);
        }
        #sm307Table thead tr th.sc1 { left: 0 !important; font-weight: 800; font-size: 0.6rem; text-transform: uppercase; }
        #sm307Table thead tr th.sc2 { left: 55px !important; font-weight: 700; font-size: 0.58rem; text-align: left; padding-left: 8px !important; }

        /* Tfoot Sticky Header */
        #sm307Table tfoot tr td {
            position: sticky !important;
            bottom: 0 !important;
            z-index: 100 !important;
            background: #1e293b !important;
            color: #ffffff !important;
            font-weight: 700;
            padding: 4px 8px !important;
            backface-visibility: hidden;
            transform: translateZ(0);
        }
        #sm307Table tfoot tr td.sc1,
        #sm307Table tfoot tr td.sc2 {
            z-index: 150 !important;
            background: #0f172a !important;
        }

        /* =====================================================
           BODY ROWS
           ===================================================== */
        #sm307Table tbody td {
            height: 26px;
            white-space: nowrap;
        }

        #sm307Table tbody td.sc1 {
            background: #f1f5f9;
            font-weight: 700;
            color: #334155;
            font-size: 0.7rem;
        }
        #sm307Table tbody td.sc2 {
            background: #ffffff;
            text-align: left;
            font-size: 0.6rem; /* Reducido */
            white-space: normal;
            line-height: 1.3;
            padding: 2px 6px;
        }

        /* =====================================================
           INTERACTIVE HIGHLIGHT (Azul) - Stable & Fixed
           ===================================================== */
        /* Resalte de fila completo */
        #sm307Table tbody tr:hover td {
            background-color: #dbeafe !important;
        }

        /* Celda de Intersección (Donde está el cursor) */
        #sm307Table tbody td:hover {
            background-color: #2563eb !important; /* Azul Intenso */
            color: #000000 !important; /* Texto negro solicitado */
            font-weight: 900 !important;
            /* Mantenemos posición y peso originales para evitar descuadres */
        }

        /* Reacción de columnas fijas al hover de fila */
        #sm307Table tbody tr:hover td.sc1,
        #sm307Table tbody tr:hover td.sc2 {
            background-color: #bfdbfe !important;
            color: #1e40af !important;
        }

        /* Asegurar que el el encabezado siempre gane en capas */
        #sm307Table thead {
            z-index: 100 !important;
        }

        /* Text positive/data indicators */
        .val-positive {
            color: #1d4ed8 !important;
            font-weight: 700 !important;
            background: #eff6ff !important;
        }
        .val-zero { color: #cbd5e1 !important; }

        /* =====================================================
           SECTION ROWS
           ===================================================== */
        .section-row td {
            background: #dde3ed !important;
            font-weight: 800;
            font-size: 0.65rem; /* Aumentado */
            text-transform: uppercase;
            letter-spacing: 0.05em;
            color: #334155;
            height: 22px;
        }
        .section-row .sc1 { background: #b2bdcf !important; }
        .section-row .sc2 {
            background: #c0cde0 !important;
            text-align: left !important;
            padding-left: 8px;
        }

        /* =====================================================
           FOOTER
           ===================================================== */
        #sm307Table tfoot td {
            background: #1e293b;
            color: #f1f5f9;
            font-weight: 700;
            font-size: 0.65rem;
            height: 28px;
        }
        #sm307Table tfoot .sc1,
        #sm307Table tfoot .sc2 {
            background: #0f172a !important;
        }

        /* =====================================================
           SCROLLBAR
           ===================================================== */
        #sm307-wrapper::-webkit-scrollbar         { width: 10px; height: 10px; }
        #sm307-wrapper::-webkit-scrollbar-track   { background: #f1f5f9; }
        #sm307-wrapper::-webkit-scrollbar-thumb   { background: #94a3b8; border-radius: 5px; border: 2px solid #f1f5f9; }
        #sm307-wrapper::-webkit-scrollbar-thumb:hover { background: #64748b; }
        #sm307-wrapper::-webkit-scrollbar-corner  { background: #f1f5f9; }

        .filter-select {
            appearance: none !important;
            -webkit-appearance: none !important;
            -moz-appearance: none !important;
            background-color: #fff !important;
            background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e") !important;
            background-position: right 0.5rem center !important;
            background-repeat: no-repeat !important;
            background-size: 1.5em 1.5em !important;
            border: 1px solid #cbd5e1 !important;
            border-radius: 6px !important;
            color: #1e293b !important;
            line-height: normal !important;
            padding-top: 4px !important;
            padding-bottom: 4px !important;
            padding-right: 2.5rem !important;
            font-weight: 500 !important;
            cursor: pointer;
        }

        /* =====================================================
           PRINT
           ===================================================== */
        @media print {
            #sm307-wrapper { overflow: visible !important; height: auto !important; }
            #sm307Table    { width: 100% !important; min-width: auto !important; }
            .no-print, .filter-container { display: none !important; }
            #sm307Table thead tr th,
            #sm307Table tfoot tr td { position: relative !important; top: auto !important; bottom: auto !important; }
            .sc1, .sc2 { position: relative !important; left: 0 !important; }
            #sm307Table th, #sm307Table td { font-size: 7pt !important; padding: 1px 2px !important; }
        }
    </style>

    <div id="sm307-wrapper">
        <table id="sm307Table">
            <colgroup>
                <col style="width:55px; min-width:55px;">
                <col style="width:300px; min-width:300px;">
                @for($i = 1; $i <= 40; $i++)
                    <col style="width:25px; min-width:25px;">
                @endfor
            </colgroup>

            @php
                $ageRanges = [
                    'MENOR 1 AÑO', '1-4 AÑOS', '5-9 AÑOS', '10-14 AÑOS', '15-19 AÑOS',
                    '20-24 AÑOS', '25-39 AÑOS', '40-59 AÑOS', '60 Y MÁS', 'TOTAL'
                ];
            @endphp

            <thead>
                <!-- Fila 1: Rangos de edad -->
                <tr>
                    <th rowspan="3" class="sc1" style="width:55px;">CÓDIGO</th>
                    <th rowspan="3" class="sc2" style="width:300px; text-align:left; padding-left:8px;">
                        DIAGNÓSTICO / CLASIFICACIÓN CIE-10
                    </th>
                    @foreach($ageRanges as $range)
                        <th colspan="4">{{ $range }}</th>
                    @endforeach
                </tr>
                <!-- Fila 2: 1ra Vez / Subs -->
                <tr>
                    @foreach($ageRanges as $range)
                        <th colspan="2">1ERA. VEZ</th>
                        <th colspan="2">SUBS.</th>
                    @endforeach
                </tr>
                <!-- Fila 3: H / M -->
                <tr>
                    @foreach($ageRanges as $range)
                        <th>H</th><th>M</th><th>H</th><th>M</th>
                    @endforeach
                </tr>
            </thead>

            <tbody>
                @php $lastSeccion = ''; @endphp
                @foreach($finalData as $row)
                    <tr>
                        <td class="sc1">{{ $row['code'] }}</td>
                        <td class="sc2">{{ $row['label'] }}</td>
                        @for($col = 1; $col <= 40; $col++)
                            @php $val = $row['cols'][$col] ?? 0; @endphp
                            <td class="{{ $val > 0 ? 'val-positive cursor-pointer hover:bg-blue-600 hover:text-white font-bold transition-all' : 'val-zero' }}"
                                @if($val > 0)
                                    onclick="showSm307CellDetails('{{ $row['id'] }}', {{ $col }})"
                                    title="Haga clic para ver qué médico lo atendió y los días de atención ({{ $val }} atenciones)"
                                @endif>
                                {{ $val ?: '-' }}
                            </td>
                        @endfor
                    </tr>
                @endforeach
            </tbody>

            <tfoot>
                <tr>
                    <td colspan="2" class="sc1" style="text-align:right; padding-right:10px;">
                        TOTAL GENERAL:
                    </td>
                    @for($col = 1; $col <= 40; $col++)
                        @php $totVal = $totalGeneral[$col] ?? 0; @endphp
                        <td class="{{ $totVal > 0 ? 'cursor-pointer hover:bg-blue-600 hover:text-white font-bold transition-all' : '' }}"
                            @if($totVal > 0)
                                onclick="showSm307CellDetails('TOTAL_ROW', {{ $col }})"
                                title="Ver médicos y días de atención del Total General ({{ $totVal }} atenciones)"
                            @endif>
                            {{ $totVal ?: '0' }}
                        </td>
                    @endfor
                </tr>
            </tfoot>
        </table>
    </div>

    <!-- Modal de Detalles de Atenciones SM307 -->
    <div id="sm307CellModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm z-[9999] hidden flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-4xl max-h-[90vh] flex flex-col overflow-hidden border border-slate-200">
            <!-- Header -->
            <div class="px-6 py-4 bg-gradient-to-r from-blue-800 to-indigo-800 text-white flex items-center justify-between shadow-md">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center text-white shrink-0">
                        <i class="fas fa-user-md text-lg"></i>
                    </div>
                    <div>
                        <h3 id="sm307ModalConceptoTitle" class="font-bold text-base text-white m-0 leading-tight">Detalle de Médicos y Atenciones</h3>
                        <p id="sm307ModalColumnaSubTitle" class="text-xs text-blue-200 font-medium m-0 mt-0.5"></p>
                    </div>
                </div>
                <div class="flex items-center gap-3">
                    <span id="sm307ModalTotalBadge" class="bg-white/20 text-white font-bold text-xs px-3 py-1 rounded-full border border-white/30 shadow-sm">
                        0 Atenciones
                    </span>
                    <button type="button" onclick="closeSm307Modal()" class="text-white/80 hover:text-white text-2xl font-light leading-none outline-none border-0 bg-transparent transition-all hover:scale-110">&times;</button>
                </div>
            </div>

            <!-- Body -->
            <div class="p-6 overflow-y-auto flex-1 bg-slate-50">
                <!-- Loader -->
                <div id="sm307ModalLoader" class="py-12 text-center">
                    <div class="w-10 h-10 border-4 border-blue-600 border-t-transparent rounded-full animate-spin mx-auto mb-3"></div>
                    <p class="text-xs font-bold text-slate-500 uppercase tracking-widest">Consultando registros...</p>
                </div>

                <!-- Content Area -->
                <div id="sm307ModalBody" class="hidden">
                    <div id="sm307MedicosList" class="space-y-4"></div>
                </div>
            </div>

            <!-- Footer -->
            <div class="px-6 py-3 bg-white border-t border-slate-200 flex justify-end">
                <button type="button" onclick="closeSm307Modal()" class="px-5 py-2 text-xs font-bold text-slate-700 hover:text-slate-900 bg-slate-100 hover:bg-slate-200 border border-slate-300 rounded-lg uppercase tracking-wider transition-all shadow-sm">
                    Cerrar
                </button>
            </div>
        </div>
    

    <script>
        window.showSm307CellDetails = function(diagId, col) {
            const modal = document.getElementById('sm307CellModal');
            const loader = document.getElementById('sm307ModalLoader');
            const body = document.getElementById('sm307ModalBody');

            if (!modal) return;

            if (modal.parentElement !== document.body) {
                document.body.appendChild(modal);
            }

            modal.classList.remove('hidden');
            loader.classList.remove('hidden');
            body.classList.add('hidden');

            const ano = document.querySelector('select[name="ano"]')?.value || '{{ $ano }}';
            const mes = document.querySelector('select[name="mes"]')?.value || '{{ $mes }}';
            const jornada = document.querySelector('select[name="jornada"]')?.value || '{{ $jornada }}';
            const lado = document.getElementById('lado-input')?.value || '{{ $lado }}';

            const params = new URLSearchParams({
                ano: ano,
                mes: mes,
                jornada: jornada,
                lado: lado,
                diag_id: diagId,
                col: col
            });

            fetch(`{{ route('informes.sm307.cell-details') }}?${params.toString()}`)
                .then(r => r.json())
                .then(data => {
                    document.getElementById('sm307ModalConceptoTitle').innerText = data.concepto;
                    document.getElementById('sm307ModalColumnaSubTitle').innerText = data.columna_nombre;
                    document.getElementById('sm307ModalTotalBadge').innerText = `${data.total_registros} Atenciones`;

                    renderSm307MedicosModal(data.medicos);

                    loader.classList.add('hidden');
                    body.classList.remove('hidden');
                })
                .catch(err => {
                    console.error('Error al obtener detalles:', err);
                    loader.innerHTML = `<div class="p-6 text-center text-red-600 font-bold">Ocurrió un error al cargar la información. Intente nuevamente.</div>`;
                });
        };

        window.closeSm307Modal = function() {
            const modal = document.getElementById('sm307CellModal');
            if (modal) modal.classList.add('hidden');
        };

        function renderSm307MedicosModal(medicos) {
            const list = document.getElementById('sm307MedicosList');
            list.innerHTML = '';

            if (!medicos || medicos.length === 0) {
                list.innerHTML = `
                    <div class="p-8 text-center bg-white rounded-xl border border-slate-200 text-slate-500 font-medium shadow-sm">
                        No se encontraron registros de médicos o atenciones en esta casilla.
                    </div>`;
                return;
            }

            medicos.forEach((m, idx) => {
                let fechasHtml = m.fechas.map(f => `
                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-blue-50 text-blue-800 border border-blue-200 text-xs font-bold">
                        <i class="far fa-calendar-alt text-blue-600"></i>
                        ${f.fecha}: <strong class="text-blue-950">${f.count} ${f.count === 1 ? 'atención' : 'atenciones'}</strong>
                    </span>
                `).join('');

                let atencionesRows = m.atenciones.map((at, i) => `
                    <tr class="hover:bg-blue-50/50 text-xs transition-colors">
                        <td class="px-3 py-2 font-bold text-slate-600 border-b border-slate-100">${i + 1}</td>
                        <td class="px-3 py-2 font-bold text-blue-700 border-b border-slate-100">${at.fecha}</td>
                        <td class="px-3 py-2 text-slate-700 font-semibold border-b border-slate-100">${at.expediente}</td>
                        <td class="px-3 py-2 text-slate-600 border-b border-slate-100">${at.sexo} (${at.edad})</td>
                        <td class="px-3 py-2 border-b border-slate-100">
                            <span class="px-2 py-0.5 rounded-full text-[10px] font-extrabold ${at.condicion.includes('Nueva') ? 'bg-emerald-100 text-emerald-800 border border-emerald-200' : 'bg-amber-100 text-amber-800 border border-amber-200'}">
                                ${at.condicion}
                            </span>
                        </td>
                        <td class="px-3 py-2 text-slate-800 font-medium border-b border-slate-100">${at.diagnostico}</td>
                    </tr>
                `).join('');

                let card = document.createElement('div');
                card.className = 'bg-white border border-slate-200 rounded-xl p-4 shadow-sm hover:shadow-md transition-shadow';
                card.innerHTML = `
                    <div class="flex items-center justify-between gap-3 mb-3 border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-9 h-9 rounded-full bg-slate-900 text-white font-bold text-sm flex items-center justify-center shrink-0 shadow-sm">
                                <i class="fas fa-user-md"></i>
                            </div>
                            <div>
                                <h4 class="font-black text-slate-800 text-sm m-0 leading-tight">${m.medico}</h4>
                                <span class="text-[11px] font-semibold text-slate-400 uppercase tracking-wider">${m.profesion}</span>
                            </div>
                        </div>
                        <div>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-blue-100 text-blue-900 text-xs font-black border border-blue-200">
                                ${m.total} ${m.total === 1 ? 'atención' : 'atenciones'}
                            </span>
                        </div>
                    </div>

                    <div class="mb-3">
                        <p class="text-[11px] font-bold text-slate-500 uppercase tracking-wider mb-1.5 flex items-center gap-1">
                            <i class="far fa-clock text-slate-400"></i> Días de atención contados:
                        </p>
                        <div class="flex flex-wrap gap-2">
                            ${fechasHtml}
                        </div>
                    </div>

                    <div class="mt-3 overflow-x-auto rounded-lg border border-slate-200">
                        <table class="w-full text-left border-collapse">
                            <thead class="bg-slate-100 text-[11px] font-bold text-slate-700 uppercase border-b border-slate-200">
                                <tr>
                                    <th class="px-3 py-2">#</th>
                                    <th class="px-3 py-2">Fecha</th>
                                    <th class="px-3 py-2">Expediente</th>
                                    <th class="px-3 py-2">Sexo/Edad</th>
                                    <th class="px-3 py-2">Condición</th>
                                    <th class="px-3 py-2">Diagnóstico Registrado</th>
                                </tr>
                            </thead>
                            <tbody>
                                ${atencionesRows}
                            </tbody>
                        </table>
                    </div>
                `;
                list.appendChild(card);
            });
        }
    </script>
