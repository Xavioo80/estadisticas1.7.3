<div class="comparacion-cruzada-wrapper" style="padding: 0.25rem 0.35rem;">

    {{-- ═══ BARRA DE FILTROS ═══ --}}
    <div class="cmp-filters-bar">

        {{-- Controles izquierda --}}
        <div class="cmp-filters-left">
            <span class="cmp-filters-label">
                <i class="bi bi-funnel-fill text-primary"></i> Filtros:
            </span>

            <select id="modal-cmp-ano" class="form-control form-control-sm cmp-select" onchange="onModalAnoChange(this.value)">
                @foreach($anos as $a)
                    <option value="{{ $a }}" {{ $a == $ano ? 'selected' : '' }}>{{ $a }}</option>
                @endforeach
            </select>

            <select id="modal-cmp-mes" class="form-control form-control-sm cmp-select text-uppercase">
                @foreach($meses as $m)
                    <option value="{{ $m }}" {{ strtoupper($m) == strtoupper($mes) ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
            </select>

            <select id="modal-cmp-jornada" class="form-control form-control-sm cmp-select">
                <option value="TODAS" {{ $jornada == 'TODAS' ? 'selected' : '' }}>TODAS LAS JORNADAS</option>
                @foreach($jornadas as $j)
                    <option value="{{ $j }}" {{ $j == $jornada ? 'selected' : '' }}>{{ $j }}</option>
                @endforeach
            </select>

            <select id="modal-cmp-condicion-filter" class="form-control form-control-sm cmp-select" onchange="filtrarFilasPorCondicion(this.value)">
                <option value="ALL">TODAS LAS COND.</option>
                <option value="N">SOLO NUEVOS (N)</option>
                <option value="S">SOLO SUBSIG. (S)</option>
                <option value="TOTAL">TOTALES (N+S)</option>
            </select>

            <button type="button" class="btn btn-sm btn-primary cmp-btn-actualizar" onclick="recargarModalComparacion()">
                <i class="bi bi-arrow-clockwise"></i> <span class="cmp-btn-text">Actualizar</span>
            </button>
        </div>

        {{-- Badges derecha --}}
        <div class="cmp-filters-right">
            <span class="cmp-badge-stat">
                <i class="bi bi-database-check"></i> {{ $resultado['resumen']['total_informes_raw'] }} Dx
            </span>
            <span class="cmp-badge-stat cmp-badge-stat--secondary">
                <i class="bi bi-people"></i> {{ $resultado['resumen']['total_registros_globales'] }} Raw
            </span>
        </div>
    </div>

    {{-- ═══ KPI STRIP COMPACTO (una sola fila horizontal) ═══ --}}
    <div class="cmp-kpi-strip">

        <div class="cmp-kpi-item">
            <span class="cmp-kpi-label">Consistencia</span>
            <span class="cmp-kpi-value" style="color: {{ $resultado['resumen']['porcentaje_consistencia'] == 100 ? 'var(--color-success, #10b981)' : ($resultado['resumen']['porcentaje_consistencia'] >= 80 ? 'var(--color-warning, #f59e0b)' : 'var(--color-danger, #ef4444)') }};">
                {{ $resultado['resumen']['porcentaje_consistencia'] }}%
            </span>
            <span class="cmp-kpi-sub">{{ $resultado['resumen']['total_comparables'] }} cruces</span>
        </div>

        <div class="cmp-kpi-sep"></div>

        <div class="cmp-kpi-item">
            <span class="cmp-kpi-label">Coincidencias</span>
            <span class="cmp-kpi-value text-success">
                <i class="bi bi-check-circle-fill" style="font-size:0.75rem;"></i> {{ $resultado['resumen']['coincidencias'] }}
            </span>
            <span class="cmp-kpi-sub">100% idénticas</span>
        </div>

        <div class="cmp-kpi-sep"></div>

        <div class="cmp-kpi-item">
            <span class="cmp-kpi-label">Discrepancias</span>
            <span class="cmp-kpi-value {{ $resultado['resumen']['discrepancias'] > 0 ? 'text-warning' : 'text-muted' }}">
                <i class="bi bi-exclamation-triangle-fill" style="font-size:0.75rem;"></i> {{ $resultado['resumen']['discrepancias'] }}
            </span>
            <span class="cmp-kpi-sub">Req. revisión</span>
        </div>

        <div class="cmp-kpi-sep"></div>

        <div class="cmp-kpi-item">
            <span class="cmp-kpi-label">Fuentes</span>
            <span class="cmp-kpi-value text-primary">5</span>
            <span class="cmp-kpi-sub">AT2RN · Morb · T2 · ITS · RG</span>
        </div>

    </div>

    {{-- ═══ TABLA COMPARATIVA COMPACTA ═══ --}}
    <div class="table-responsive custom-scrollbar cmp-table-wrap">
        <table class="table table-sm table-hover mb-0 text-center cmp-table">
            <thead class="sticky-top">
                <tr>
                    <th class="text-left cmp-th-cond">ENFERMEDAD / CONDICIÓN</th>
                    <th class="cmp-th-num" title="Informe Mensual AT2-R (N)">AT2-R</th>
                    <th class="cmp-th-num" title="Informe Mensual de Morbilidad">MORB.</th>
                    <th class="cmp-th-num" title="Informe Semanal TRANS-2 (Únicamente Casos Nuevos)">T-2</th>
                    <th class="cmp-th-num" title="Informe Mensual de Infecciones de Transmisión Sexual (ITS)">ITS</th>
                    <th class="cmp-th-num" title="Base de Datos Registros Globales / AT1">R. GLOB.</th>
                    <th class="cmp-th-estado">ESTADO</th>
                </tr>
            </thead>
            <tbody>
                @php $curCat = null; @endphp
                @foreach($resultado['comparaciones'] as $cmp)
                    @if($curCat !== $cmp['categoria'])
                        @php $curCat = $cmp['categoria']; @endphp
                        <tr class="cmp-cat-row" data-cat="{{ $curCat }}">
                            <td colspan="7" class="cmp-cat-cell">
                                <i class="bi bi-folder2-open mr-1"></i>{{ $curCat }}
                            </td>
                        </tr>
                    @endif
                    <tr class="cmp-row" data-cond="{{ $cmp['condicion'] ?? 'ALL' }}">
                        {{-- Celda condición --}}
                        <td class="text-left cmp-td-label">
                            <div class="cmp-label-top">
                                <span class="cmp-label-name">{{ $cmp['label'] }}</span>
                                @php $cond = $cmp['condicion'] ?? ''; @endphp
                                @if($cond === 'N')
                                    <span class="cmp-pill cmp-pill--primary">N</span>
                                @elseif($cond === 'S')
                                    <span class="cmp-pill cmp-pill--secondary">S</span>
                                @elseif($cond === 'TOTAL')
                                    <span class="cmp-pill cmp-pill--info">N+S</span>
                                @endif
                                <span class="cmp-cie">{{ $cmp['codigo_cie'] }}</span>
                            </div>
                            <div class="cmp-label-desc">{{ $cmp['descripcion'] }}</div>
                        </td>

                        {{-- AT2-R (N) --}}
                        <td class="cmp-td-num">
                            @if($cmp['at2rn'] !== null)
                                <span class="cmp-num-badge {{ $cmp['at2rn'] > 0 ? 'cmp-num--primary' : 'cmp-num--zero' }}">{{ $cmp['at2rn'] }}</span>
                            @else
                                <span class="cmp-na">—</span>
                            @endif
                        </td>

                        {{-- Morbilidad --}}
                        <td class="cmp-td-num">
                            @if($cmp['morbilidad'] !== null)
                                <span class="cmp-num-badge {{ $cmp['morbilidad'] > 0 ? 'cmp-num--info' : 'cmp-num--zero' }}">{{ $cmp['morbilidad'] }}</span>
                            @else
                                <span class="cmp-na">—</span>
                            @endif
                        </td>

                        {{-- TRANS-2 --}}
                        <td class="cmp-td-num">
                            @if(($cmp['condicion'] ?? '') === 'S' || ($cmp['condicion'] ?? '') === 'TOTAL')
                                <span class="cmp-only-new" title="TRANS-2 solo contabiliza casos nuevos">↑N</span>
                            @elseif($cmp['trans2'] !== null)
                                <span class="cmp-num-badge {{ $cmp['trans2'] > 0 ? 'cmp-num--warning' : 'cmp-num--zero' }}">{{ $cmp['trans2'] }}</span>
                            @else
                                <span class="cmp-na">—</span>
                            @endif
                        </td>

                        {{-- ITS --}}
                        <td class="cmp-td-num">
                            @if($cmp['its'] !== null)
                                <span class="cmp-num-badge {{ $cmp['its'] > 0 ? 'cmp-num--its' : 'cmp-num--zero' }}">{{ $cmp['its'] }}</span>
                            @else
                                <span class="cmp-na">—</span>
                            @endif
                        </td>

                        {{-- Registros Globales --}}
                        <td class="cmp-td-num">
                            @if($cmp['globales'] !== null)
                                <span class="cmp-num-badge cmp-num--glob">{{ $cmp['globales'] }}</span>
                            @else
                                <span class="cmp-na">—</span>
                            @endif
                        </td>

                        {{-- Estado --}}
                        <td class="cmp-td-estado">
                            @if($cmp['estado'] === 'match')
                                <span class="cmp-status cmp-status--ok"><i class="bi bi-check-circle-fill"></i> OK</span>
                            @elseif($cmp['estado'] === 'discrepancia')
                                <span class="cmp-status cmp-status--warn" title="Diferencia máxima: ±{{ $cmp['diferencia_max'] }}">
                                    <i class="bi bi-exclamation-triangle-fill"></i> ±{{ $cmp['diferencia_max'] }}
                                </span>
                            @else
                                <span class="cmp-status cmp-status--info">INFO</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    {{-- ═══ PIE COMPACTO ═══ --}}
    <div class="cmp-footer">
        <span class="cmp-footer-note">
            <i class="bi bi-info-circle"></i>
            <strong>↑N</strong> = TRANS-2 solo contabiliza casos nuevos (N). No penaliza consistencia en totales N+S.
        </span>
        <button type="button" class="btn btn-sm btn-subtle" data-dismiss="modal" data-bs-dismiss="modal">
            <i class="bi bi-x-lg mr-1"></i> Cerrar
        </button>
    </div>

</div>

<style>
/* ══════════════════════════════════════════
   MODAL COMPARACION CRUZADA – COMPACT MODE
   ══════════════════════════════════════════ */

/* ── Filters bar ── */
.cmp-filters-bar {
    display: flex;
    flex-direction: row;
    flex-wrap: nowrap;
    align-items: center;
    justify-content: space-between;
    gap: 6px;
    padding: 7px 10px;
    margin-bottom: 8px;
    background: var(--bg-subtle);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    overflow-x: auto;
}
.cmp-filters-left {
    display: flex;
    flex-direction: row;
    flex-wrap: nowrap;
    align-items: center;
    gap: 6px;
    flex-shrink: 0;
}
.cmp-filters-right {
    display: flex;
    flex-direction: row;
    flex-wrap: nowrap;
    align-items: center;
    gap: 5px;
    flex-shrink: 0;
}
.cmp-filters-label {
    font-weight: 700;
    font-size: 0.72rem;
    color: var(--text-secondary);
    text-transform: uppercase;
    white-space: nowrap;
}
.cmp-select {
    height: 28px !important;
    font-size: 0.75rem !important;
    padding: 1px 5px !important;
    background: var(--input-bg) !important;
    color: var(--text-primary) !important;
    border-color: var(--border-color) !important;
    flex-shrink: 0;
}
#modal-cmp-ano   { width: 72px; }
#modal-cmp-mes   { width: 108px; }
#modal-cmp-jornada { width: 138px; }
#modal-cmp-condicion-filter { width: 150px; }

.cmp-btn-actualizar {
    height: 28px !important;
    font-size: 0.73rem !important;
    font-weight: 700 !important;
    padding: 0 10px !important;
    white-space: nowrap;
    flex-shrink: 0;
    display: inline-flex;
    align-items: center;
    gap: 4px;
}
.cmp-badge-stat {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    font-size: 0.68rem;
    font-weight: 700;
    padding: 3px 8px;
    border-radius: var(--radius-xs);
    border: 1px solid var(--border-color);
    background: var(--bg-surface);
    color: var(--color-primary);
    white-space: nowrap;
}
.cmp-badge-stat--secondary { color: var(--text-secondary); }

/* ── KPI Strip horizontal ── */
.cmp-kpi-strip {
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: space-around;
    gap: 0;
    padding: 6px 10px;
    margin-bottom: 8px;
    background: var(--bg-surface);
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
    box-shadow: var(--shadow-sm);
}
.cmp-kpi-item {
    display: flex;
    flex-direction: row;
    align-items: center;
    gap: 6px;
    flex: 1;
    justify-content: center;
    padding: 2px 6px;
}
.cmp-kpi-sep {
    width: 1px;
    height: 28px;
    background: var(--border-color);
    flex-shrink: 0;
}
.cmp-kpi-label {
    font-size: 0.67rem;
    font-weight: 700;
    text-transform: uppercase;
    color: var(--text-secondary);
    white-space: nowrap;
}
.cmp-kpi-value {
    font-size: 1.05rem;
    font-weight: 800;
    line-height: 1;
    white-space: nowrap;
}
.cmp-kpi-sub {
    font-size: 0.62rem;
    color: var(--text-muted, var(--text-secondary));
    white-space: nowrap;
}

/* ── Table ── */
.cmp-table-wrap {
    max-height: calc(100vh - 320px);
    min-height: 140px;
    overflow-y: auto;
    border: 1px solid var(--border-color);
    border-radius: var(--radius-md);
}
.cmp-table {
    font-size: 0.76rem !important;
    background: var(--bg-surface) !important;
    border-collapse: separate !important;
    border-spacing: 0 !important;
    width: 100% !important;
}
.cmp-table thead {
    background: var(--bg-subtle) !important;
    position: sticky;
    top: 0;
    z-index: 10;
    border-bottom: 2px solid var(--border-color);
}
.cmp-table thead th {
    padding: 5px 6px !important;
    font-size: 0.68rem !important;
    font-weight: 700 !important;
    color: var(--text-primary) !important;
    text-transform: uppercase;
    white-space: nowrap;
}
.cmp-th-cond  { width: 32%; text-align: left; padding-left: 10px !important; }
.cmp-th-num   { width: 9%; }
.cmp-th-estado{ width: 13%; }

/* Category rows */
.cmp-cat-row { background: rgba(77, 124, 254, 0.07) !important; }
.cmp-cat-cell {
    text-align: left;
    font-weight: 700;
    font-size: 0.68rem !important;
    text-transform: uppercase;
    letter-spacing: 0.05em;
    color: var(--color-primary);
    padding: 4px 10px !important;
    border-top: 1px solid var(--border-color);
    border-bottom: 1px solid var(--border-color);
}

/* Data rows */
.cmp-row { border-bottom: 1px solid var(--border-color) !important; }
.cmp-row:hover { background: var(--bg-subtle) !important; }

.cmp-td-label {
    padding: 3px 6px 3px 10px !important;
    vertical-align: middle;
}
.cmp-label-top {
    display: flex;
    flex-direction: row;
    align-items: center;
    flex-wrap: wrap;
    gap: 3px;
}
.cmp-label-name {
    font-weight: 700;
    color: var(--text-primary);
    font-size: 0.76rem;
    white-space: nowrap;
}
.cmp-label-desc {
    font-size: 0.63rem;
    color: var(--text-secondary);
    line-height: 1.2;
    margin-top: 1px;
}

/* Pills de condición */
.cmp-pill {
    display: inline-flex;
    align-items: center;
    font-size: 0.6rem;
    font-weight: 800;
    padding: 1px 5px;
    border-radius: 20px;
    line-height: 1;
}
.cmp-pill--primary   { background: rgba(77,124,254,0.15); color: var(--color-primary); }
.cmp-pill--secondary { background: var(--bg-subtle); color: var(--text-secondary); border: 1px solid var(--border-color); }
.cmp-pill--info      { background: rgba(6,182,212,0.15); color: #06b6d4; }

/* CIE code */
.cmp-cie {
    font-size: 0.6rem;
    color: var(--text-secondary);
    background: var(--bg-subtle);
    border: 1px solid var(--border-color);
    border-radius: 3px;
    padding: 1px 4px;
    font-family: monospace;
    white-space: nowrap;
}

/* Numeric cells */
.cmp-td-num { padding: 3px 4px !important; vertical-align: middle; }
.cmp-num-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    min-width: 24px;
    height: 22px;
    border-radius: var(--radius-xs, 4px);
    font-size: 0.8rem;
    font-weight: 700;
    padding: 0 6px;
}
.cmp-num--primary  { background: rgba(77,124,254,0.18); color: var(--color-primary); }
.cmp-num--info     { background: rgba(6,182,212,0.18); color: #06b6d4; }
.cmp-num--warning  { background: rgba(245,158,11,0.18); color: #d97706; }
.cmp-num--its      { background: rgba(168,85,247,0.18); color: #9333ea; }
.cmp-num--glob     { background: var(--bg-subtle); color: var(--text-primary); border: 1px solid var(--border-color); }
.cmp-num--zero     { background: transparent; color: var(--text-secondary); border: 1px solid var(--border-color); }
.cmp-na    { color: var(--text-secondary); font-size: 0.7rem; }
.cmp-only-new {
    font-size: 0.62rem;
    color: var(--text-secondary);
    font-style: italic;
    font-weight: 600;
    cursor: help;
}

/* Status badges */
.cmp-td-estado { padding: 3px 6px !important; vertical-align: middle; }
.cmp-status {
    display: inline-flex;
    align-items: center;
    gap: 3px;
    font-size: 0.68rem;
    font-weight: 700;
    padding: 3px 7px;
    border-radius: var(--radius-sm, 4px);
    white-space: nowrap;
}
.cmp-status--ok   { background: rgba(16,185,129,0.15); color: #059669; }
.cmp-status--warn { background: rgba(245,158,11,0.15); color: #d97706; }
.cmp-status--info { background: var(--bg-subtle); color: var(--text-secondary); border: 1px solid var(--border-color); }

/* ── Footer ── */
.cmp-footer {
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    margin-top: 8px;
    flex-wrap: wrap;
}
.cmp-footer-note {
    font-size: 0.67rem;
    color: var(--text-secondary);
    display: flex;
    align-items: center;
    gap: 4px;
    flex-wrap: wrap;
}

/* ── Responsive: pantallas pequeñas ── */
@media (max-width: 900px) {
    #modal-cmp-jornada { width: 115px; }
    #modal-cmp-condicion-filter { width: 120px; }
    .cmp-btn-text { display: none; }
    .cmp-kpi-sub { display: none; }
    .cmp-kpi-label { font-size: 0.62rem; }
    .cmp-kpi-value { font-size: 0.9rem; }
    .cmp-badge-stat { display: none; }
}
@media (max-width: 700px) {
    .cmp-filters-bar { flex-wrap: wrap; }
    #modal-cmp-jornada, #modal-cmp-condicion-filter { width: 100%; }
    .cmp-kpi-strip { flex-wrap: wrap; gap: 6px; }
    .cmp-kpi-sep { display: none; }
    .cmp-kpi-item { min-width: 40%; }
    .cmp-table { font-size: 0.7rem !important; }
}
</style>

<script>
function onModalAnoChange(nuevoAno) {
    const jornada = $('#modal-cmp-jornada').val() || 'TODAS';
    cargarDatosComparacionCruzada(nuevoAno, '', jornada);
}

function filtrarFilasPorCondicion(val) {
    const rows = document.querySelectorAll('.cmp-row');
    rows.forEach(r => {
        const cond = r.getAttribute('data-cond');
        if (val === 'ALL' || cond === val || (val === 'TOTAL' && cond === 'TOTAL')) {
            r.style.display = '';
        } else {
            r.style.display = 'none';
        }
    });
}
</script>
