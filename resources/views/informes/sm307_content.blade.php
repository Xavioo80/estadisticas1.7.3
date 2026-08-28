<!-- Barra de Filtros en Una Sola Fila Horizontal Estricta -->
<div class="filter-container no-print mb-2"
    style="background: var(--bg-surface) !important; border: 1px solid var(--border-color) !important; border-radius: var(--radius-md, 10px) !important; padding: 0.5rem 0.85rem !important; display: flex !important; flex-direction: row !important; align-items: center !important; justify-content: space-between !important; flex-wrap: nowrap !important; gap: 8px !important; box-shadow: var(--shadow-sm) !important; width: 100% !important; overflow-x: auto !important;">
    <form id="filter-form" action="{{ route('informes.sm307') }}" method="GET"
        style="display: flex !important; flex-direction: row !important; align-items: center !important; justify-content: space-between !important; flex-wrap: nowrap !important; gap: 8px !important; margin: 0 !important; width: 100% !important; min-width: 0 !important;">
        <input type="hidden" name="lado" id="lado-input" value="{{ $lado }}">
        
        <div style="display: flex !important; flex-direction: row !important; align-items: center !important; flex-wrap: nowrap !important; gap: 8px !important; flex-shrink: 0 !important;">
            <!-- Alternador Anverso / Reverso -->
            <div style="display: inline-flex !important; flex-direction: row !important; align-items: center !important; background: var(--bg-subtle) !important; padding: 2px !important; border-radius: var(--radius-sm, 8px) !important; border: 1px solid var(--border-color) !important; flex-shrink: 0 !important;">
                <button type="button"
                        onclick="document.getElementById('lado-input').value='obverso'; updateReport();"
                        class="btn-toggle-side px-3 py-1 text-[11px] font-bold rounded transition-all {{ $lado == 'obverso' ? 'btn-primary' : 'btn-subtle' }}"
                        style="height: 28px !important; font-size: 0.75rem !important; font-weight: 700 !important; padding: 0 12px !important;">
                    ANVERSA
                </button>
                <button type="button"
                        onclick="document.getElementById('lado-input').value='reverso'; updateReport();"
                        class="btn-toggle-side px-3 py-1 text-[11px] font-bold rounded transition-all {{ $lado == 'reverso' ? 'btn-primary' : 'btn-subtle' }}"
                        style="height: 28px !important; font-size: 0.75rem !important; font-weight: 700 !important; padding: 0 12px !important;">
                    REVERSA
                </button>
            </div>

            <!-- Separador -->
            <div style="height: 22px !important; width: 1px !important; background: var(--border-color) !important; flex-shrink: 0 !important; margin: 0 2px !important;"></div>

            <!-- Año -->
            <div style="width: 85px !important; min-width: 85px !important; flex-shrink: 0 !important;">
                <select name="ano" class="filter-select ajax-filter"
                    style="width: 100% !important; height: 34px !important; font-weight: 700 !important; font-size: 0.82rem !important;">
                    @foreach($anos as $a)
                        <option value="{{ $a }}" {{ (int)$a == (int)$ano ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Mes -->
            <div style="width: 130px !important; min-width: 130px !important; flex-shrink: 0 !important;">
                <select name="mes" class="filter-select ajax-filter"
                    style="width: 100% !important; height: 34px !important; font-weight: 700 !important; font-size: 0.82rem !important;">
                    @foreach($meses as $m)
                        <option value="{{ $m }}" {{ strtoupper(trim($m)) == strtoupper(trim($mes)) ? 'selected' : '' }}>
                            {{ strtoupper($m) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <!-- Jornada -->
            <div style="width: 165px !important; min-width: 150px !important; flex-shrink: 0 !important;">
                <select name="jornada" class="filter-select ajax-filter" title="Jornada"
                    style="width: 100% !important; height: 34px !important; font-weight: 700 !important; font-size: 0.82rem !important;">
                    <option value="TODAS">TODAS LAS JORNADAS</option>
                    @foreach($jornadas as $j)
                        <option value="{{ $j }}" {{ $j == $jornada ? 'selected' : '' }}>{{ $j }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Separador -->
            <div style="height: 22px !important; width: 1px !important; background: var(--border-color) !important; flex-shrink: 0 !important; margin: 0 2px !important;"></div>

            <span class="badge badge-subtle font-weight-bold" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary); white-space: nowrap;">
                SM3-07 ({{ $lado == 'obverso' ? 'ANVERSA' : 'REVERSA' }})
            </span>
        </div>

        <!-- Acciones a la derecha -->
        <div style="display: flex !important; flex-direction: row !important; align-items: center !important; flex-wrap: nowrap !important; gap: 6px !important; flex-shrink: 0 !important; margin-left: auto !important;">
            <button type="button" onclick="toggleFullScreen()" class="btn-action-fullscreen" title="Pantalla Completa">
                <i class="bi bi-arrows-fullscreen" id="fullScreenIcon"></i>
            </button>
            <button type="button" onclick="window.print()" class="btn-action-print" title="Imprimir">
                <i class="bi bi-printer"></i>
            </button>
            <a href="{{ route('informes.sm307.export', request()->all()) }}"
               class="btn btn-sm btn-subtle-success font-weight-bold"
               title="Exportar a Excel"
               style="height: 34px; padding: 0 0.75rem; display: inline-flex; align-items: center; gap: 0.35rem; font-size: 0.78rem;">
                <i class="bi bi-file-earmark-excel"></i> <span class="d-none d-md-inline">Excel</span>
            </a>
        </div>
    </form>
</div>

<style>
    /* =====================================================
       SM307 — STICKY COLUMNS + STICKY HEADER
       Adaptable a Modo Claro y Modo Oscuro
       ===================================================== */
    #sm307-wrapper {
        overflow: auto;
        flex: 1 1 0%;
        min-height: 0;
        background: var(--bg-surface);
        border: 1px solid var(--border-color);
        border-radius: var(--radius-md, 10px);
        position: relative;
        box-shadow: var(--shadow-sm);
    }

    #sm307Table {
        border-collapse: separate;
        border-spacing: 0;
        table-layout: fixed;
        width: 100%;
        min-width: 1355px;
        margin: 0;
        background: var(--bg-surface);
        color: var(--text-primary);
    }

    #sm307Table th,
    #sm307Table td {
        border-right:  1px solid var(--border-color);
        border-bottom: 1px solid var(--border-color);
        border-top:    0;
        border-left:   0;
        padding: 4px 6px;
        font-size: 0.75rem;
        vertical-align: middle;
        text-align: center;
        box-sizing: border-box;
        background-clip: padding-box;
    }

    #sm307Table .sc1 {
        border-left: 1px solid var(--border-color);
    }

    #sm307Table thead tr:first-child th {
        border-top: 1px solid var(--border-color);
    }

    /* Columnas fijas a la izquierda */
    .sc1 {
        position: sticky;
        left: 0;
        z-index: 50;
        width: 60px;
        min-width: 60px;
        max-width: 60px;
        backface-visibility: hidden;
        transform: translateZ(0);
    }
    .sc2 {
        position: sticky;
        left: 60px;
        z-index: 50;
        width: 290px;
        min-width: 290px;
        max-width: 290px;
        backface-visibility: hidden;
        transform: translateZ(0);
    }

    /* Encabezados de tabla */
    #sm307Table thead tr th {
        z-index: 100 !important;
        font-weight: 700;
    }

    /* Fila 1: Rangos de Edad */
    #sm307Table thead tr:nth-child(1) th {
        position: sticky !important;
        top: 0 !important;
        height: 32px !important;
        background: var(--bg-subtle) !important;
        color: var(--text-primary) !important;
        font-weight: 800;
        font-size: 0.68rem;
        text-transform: uppercase;
        letter-spacing: 0.03em;
    }

    /* Fila 2: Tipo de Consulta */
    #sm307Table thead tr:nth-child(2) th {
        position: sticky !important;
        top: 32px !important;
        height: 28px !important;
        background: var(--bg-surface) !important;
        color: var(--text-secondary) !important;
        font-weight: 700;
        font-size: 0.62rem;
    }

    /* Fila 3: Sexo (H / M) */
    #sm307Table thead tr:nth-child(3) th {
        position: sticky !important;
        top: 60px !important;
        height: 24px !important;
        background: var(--bg-subtle) !important;
        color: var(--text-muted) !important;
        font-weight: 800;
        font-size: 0.65rem;
    }

    /* Esquinas (Código y Diagnóstico) en Header */
    #sm307Table thead tr th.sc1,
    #sm307Table thead tr th.sc2 {
        top: 0 !important;
        z-index: 150 !important;
        height: 84px !important;
        background: var(--bg-subtle) !important;
        color: var(--text-primary) !important;
        vertical-align: middle;
        backface-visibility: hidden;
        transform: translateZ(0);
    }
    #sm307Table thead tr th.sc1 {
        left: 0 !important;
        font-weight: 800;
        font-size: 0.68rem;
        text-transform: uppercase;
    }
    #sm307Table thead tr th.sc2 {
        left: 60px !important;
        font-weight: 800;
        font-size: 0.68rem;
        text-align: left;
        padding-left: 10px !important;
        text-transform: uppercase;
    }

    /* Filas del cuerpo */
    #sm307Table tbody td {
        height: 28px;
        white-space: nowrap;
        background: var(--bg-surface);
        color: var(--text-primary);
        font-size: 0.78rem;
    }

    #sm307Table tbody td.sc1 {
        background: var(--bg-subtle);
        font-weight: 700;
        color: var(--text-primary);
        font-size: 0.72rem;
    }
    #sm307Table tbody td.sc2 {
        background: var(--bg-surface);
        text-align: left;
        font-size: 0.72rem;
        white-space: normal;
        line-height: 1.25;
        padding: 3px 8px;
        color: var(--text-primary);
    }

    /* Interacción Hover */
    #sm307Table tbody tr:hover td {
        background-color: var(--color-primary-light, rgba(99, 102, 241, 0.12)) !important;
    }

    #sm307Table tbody td.cell-interactive:hover {
        background-color: var(--color-primary) !important;
        color: #ffffff !important;
        font-weight: 800 !important;
        transform: scale(1.05);
        z-index: 10;
        box-shadow: 0 0 8px rgba(0,0,0,0.25);
    }

    #sm307Table tbody tr:hover td.sc1,
    #sm307Table tbody tr:hover td.sc2 {
        background-color: var(--color-primary-light, rgba(99, 102, 241, 0.15)) !important;
        color: var(--color-primary) !important;
    }

    /* Valores Positivos y Cero */
    .val-positive {
        color: var(--color-primary) !important;
        font-weight: 700 !important;
        cursor: pointer;
    }
    .val-zero {
        color: var(--text-muted) !important;
        opacity: 0.35;
    }

    /* Total General Footer */
    #sm307Table tfoot tr td {
        position: sticky !important;
        bottom: 0 !important;
        z-index: 100 !important;
        background: var(--bg-subtle) !important;
        color: var(--text-primary) !important;
        font-weight: 800;
        font-size: 0.75rem;
        height: 32px;
        border-top: 2px solid var(--border-color) !important;
        backface-visibility: hidden;
        transform: translateZ(0);
    }
    #sm307Table tfoot tr td.sc1,
    #sm307Table tfoot tr td.sc2 {
        z-index: 150 !important;
        background: var(--bg-subtle) !important;
    }

    /* Scrollbar */
    #sm307-wrapper::-webkit-scrollbar { width: 8px; height: 8px; }
    #sm307-wrapper::-webkit-scrollbar-track { background: var(--bg-subtle); }
    #sm307-wrapper::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 4px; }
    #sm307-wrapper::-webkit-scrollbar-thumb:hover { background: var(--text-muted); }

    @media print {
        #sm307-wrapper { overflow: visible !important; height: auto !important; border: none !important; }
        #sm307Table    { width: 100% !important; min-width: auto !important; }
        .no-print, .informe-filters-card { display: none !important; }
        #sm307Table thead tr th,
        #sm307Table tfoot tr td { position: relative !important; top: auto !important; bottom: auto !important; }
        .sc1, .sc2 { position: relative !important; left: 0 !important; }
        #sm307Table th, #sm307Table td { font-size: 7pt !important; padding: 1px 2px !important; color: #000 !important; border-color: #000 !important; }
    }
</style>

<div id="sm307-wrapper">
    <table id="sm307Table">
        <colgroup>
            <col style="width:60px; min-width:60px;">
            <col style="width:290px; min-width:290px;">
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
                <th rowspan="3" class="sc1">CÓDIGO</th>
                <th rowspan="3" class="sc2">DIAGNÓSTICO / CLASIFICACIÓN CIE-10</th>
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
            @foreach($finalData as $row)
                <tr>
                    <td class="sc1">{{ $row['code'] }}</td>
                    <td class="sc2">{{ $row['label'] }}</td>
                    @for($col = 1; $col <= 40; $col++)
                        @php $val = $row['cols'][$col] ?? 0; @endphp
                        <td class="{{ $val > 0 ? 'val-positive cell-interactive' : 'val-zero' }}"
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
                <td colspan="2" class="sc1" style="text-align:right; padding-right:12px; letter-spacing:0.05em;">
                    TOTAL GENERAL:
                </td>
                @for($col = 1; $col <= 40; $col++)
                    @php $totVal = $totalGeneral[$col] ?? 0; @endphp
                    <td class="{{ $totVal > 0 ? 'val-positive cell-interactive' : '' }}"
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

<!-- Modal Bootstrap de Detalles de Atenciones SM307 -->
<div class="modal fade" id="sm307CellModal" tabindex="-1" role="dialog" aria-labelledby="sm307ModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered modal-dialog-scrollable" role="document">
        <div class="modal-content" style="background: var(--bg-surface); border: 1px solid var(--border-color); color: var(--text-primary); border-radius: var(--radius-lg, 12px); box-shadow: var(--shadow-2xl);">
            <!-- Header -->
            <div class="modal-header" style="background: var(--bg-subtle); border-bottom: 1px solid var(--border-color); padding: 1rem 1.25rem;">
                <div class="d-flex align-items-center gap-3">
                    <div style="width: 38px; height: 38px; border-radius: 50%; background: var(--color-primary-light, rgba(99, 102, 241, 0.15)); color: var(--color-primary); display: flex; align-items: center; justify-content: center; font-size: 1.2rem; flex-shrink: 0;">
                        <i class="bi bi-person-badge"></i>
                    </div>
                    <div>
                        <h5 class="modal-title font-weight-bold mb-0" id="sm307ModalConceptoTitle" style="color: var(--text-primary); font-size: 1.05rem;">
                            Detalle de Médicos y Atenciones
                        </h5>
                        <p class="small mb-0 mt-0.5" id="sm307ModalColumnaSubTitle" style="color: var(--text-muted); font-size: 0.8rem; font-weight: 500;"></p>
                    </div>
                </div>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge badge-primary px-3 py-1" id="sm307ModalTotalBadge" style="font-size: 0.8rem; font-weight: 700; border-radius: 9999px;">
                        0 Atenciones
                    </span>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: var(--text-primary); opacity: 0.7; font-size: 1.5rem; outline: none; border: none; background: transparent;">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
            </div>

            <!-- Body -->
            <div class="modal-body p-3" style="background: var(--bg-surface); color: var(--text-primary);">
                <!-- Loader -->
                <div id="sm307ModalLoader" class="py-5 text-center">
                    <div class="spinner-border text-primary" role="status" style="width: 2.5rem; height: 2.5rem;">
                        <span class="sr-only">Cargando...</span>
                    </div>
                    <p class="small text-muted mt-2 font-weight-bold text-uppercase" style="letter-spacing: 0.05em;">Consultando registros...</p>
                </div>

                <!-- Content Area -->
                <div id="sm307ModalBody" style="display: none;">
                    <div id="sm307MedicosList" class="d-flex flex-column gap-3"></div>
                </div>
            </div>

            <!-- Footer -->
            <div class="modal-footer" style="background: var(--bg-subtle); border-top: 1px solid var(--border-color); padding: 0.75rem 1.25rem;">
                <button type="button" class="btn btn-secondary btn-sm" data-dismiss="modal" style="font-weight: 600; padding: 0.4rem 1.25rem; border-radius: var(--radius-sm, 6px);">
                    Cerrar
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    window.showSm307CellDetails = function(diagId, col) {
        const modal = $('#sm307CellModal');
        const loader = $('#sm307ModalLoader');
        const body = $('#sm307ModalBody');

        loader.show();
        body.hide();
        modal.modal('show');

        const ano = $('select[name="ano"]').val() || '{{ $ano }}';
        const mes = $('select[name="mes"]').val() || '{{ $mes }}';
        const jornada = $('select[name="jornada"]').val() || '{{ $jornada }}';
        const lado = $('#lado-input').val() || '{{ $lado }}';

        const params = {
            ano: ano,
            mes: mes,
            jornada: jornada,
            lado: lado,
            diag_id: diagId,
            col: col
        };

        $.ajax({
            url: "{{ route('informes.sm307.cell-details') }}",
            type: 'GET',
            data: params,
            success: function(data) {
                $('#sm307ModalConceptoTitle').text(data.concepto || 'Detalle de Atenciones');
                $('#sm307ModalColumnaSubTitle').text(data.columna_nombre || ('Columna ' + col));
                $('#sm307ModalTotalBadge').text((data.total_registros || 0) + ' Atenciones');

                renderSm307MedicosModal(data.medicos || []);

                loader.hide();
                body.show();
            },
            error: function(err) {
                console.error('Error al obtener detalles:', err);
                loader.html('<div class="p-4 text-center text-danger font-weight-bold">Ocurrió un error al cargar la información. Intente nuevamente.</div>');
            }
        });
    };

    function renderSm307MedicosModal(medicos) {
        const list = $('#sm307MedicosList');
        list.empty();

        if (!medicos || medicos.length === 0) {
            list.html(`
                <div class="p-4 text-center rounded border text-muted" style="background: var(--bg-subtle); border-color: var(--border-color);">
                    <i class="bi bi-info-circle mr-1"></i> No se encontraron registros de médicos o atenciones en esta casilla.
                </div>
            `);
            return;
        }

        medicos.forEach((m, idx) => {
            let fechasHtml = m.fechas.map(f => `
                <span class="badge badge-subtle px-2 py-1 mr-1 mb-1" style="font-size: 0.75rem; border: 1px solid var(--border-color); color: var(--text-primary); background: var(--bg-surface);">
                    <i class="bi bi-calendar-event text-primary mr-1"></i> ${f.fecha}: <strong>${f.count} ${f.count === 1 ? 'atención' : 'atenciones'}</strong>
                </span>
            `).join('');

            let atencionesRows = m.atenciones.map((at, i) => {
                let isNueva = at.condicion && at.condicion.includes('Nueva');
                let badgeClass = isNueva ? 'badge-success' : 'badge-warning';
                return `
                    <tr style="border-color: var(--border-color);">
                        <td class="font-weight-bold text-muted">${i + 1}</td>
                        <td class="font-weight-bold text-primary">${at.fecha}</td>
                        <td class="font-weight-bold" style="color: var(--text-primary);">${at.expediente}</td>
                        <td class="text-muted">${at.sexo} (${at.edad})</td>
                        <td>
                            <span class="badge ${badgeClass}" style="font-size: 0.72rem; font-weight: 700;">
                                ${at.condicion}
                            </span>
                        </td>
                        <td style="color: var(--text-primary); font-size: 0.8rem;">${at.diagnostico}</td>
                    </tr>
                `;
            }).join('');

            let cardHtml = `
                <div class="card mb-3 shadow-sm" style="background: var(--bg-subtle); border: 1px solid var(--border-color); border-radius: var(--radius-md, 8px);">
                    <div class="card-header d-flex align-items-center justify-content-between py-2 px-3" style="background: var(--bg-surface); border-bottom: 1px solid var(--border-color);">
                        <div class="d-flex align-items-center gap-2">
                            <div style="width: 32px; height: 32px; border-radius: 50%; background: var(--color-primary); color: #fff; display: flex; align-items: center; justify-content: center; font-size: 0.9rem; font-weight: bold;">
                                <i class="bi bi-person-fill"></i>
                            </div>
                            <div>
                                <h6 class="mb-0 font-weight-bold" style="color: var(--text-primary); font-size: 0.92rem;">${m.medico}</h6>
                                <small class="text-muted font-weight-bold text-uppercase" style="font-size: 0.7rem;">${m.profesion}</small>
                            </div>
                        </div>
                        <span class="badge badge-primary px-2.5 py-1" style="font-size: 0.78rem; font-weight: 700;">
                            ${m.total} ${m.total === 1 ? 'atención' : 'atenciones'}
                        </span>
                    </div>

                    <div class="card-body p-3">
                        <div class="mb-2">
                            <small class="text-muted font-weight-bold text-uppercase d-block mb-1" style="font-size: 0.7rem; letter-spacing: 0.05em;">
                                <i class="bi bi-clock-history mr-1"></i> Días de atención:
                            </small>
                            <div class="d-flex flex-wrap">
                                ${fechasHtml}
                            </div>
                        </div>

                        <div class="table-responsive rounded border mt-2" style="max-height: 250px; overflow-y: auto; border-color: var(--border-color) !important; background: var(--bg-surface);">
                            <table class="table table-sm table-hover mb-0" style="font-size: 0.78rem;">
                                <thead style="background: var(--bg-subtle); color: var(--text-muted); position: sticky; top: 0; z-index: 5;">
                                    <tr>
                                        <th style="width: 35px;">#</th>
                                        <th>Fecha</th>
                                        <th>Expediente</th>
                                        <th>Sexo/Edad</th>
                                        <th>Condición</th>
                                        <th>Diagnóstico Registrado</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    ${atencionesRows}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            `;
            list.append(cardHtml);
        });
    }
</script>
