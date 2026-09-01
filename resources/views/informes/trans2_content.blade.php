<style>
    :root {
        --trans2-border-internal: rgba(0, 0, 0, 0.15);
        --trans2-semana-border: #0284c7; /* Azul / Cyan intenso */
        --trans2-semana-hdr-bg: rgba(2, 132, 199, 0.12);
        --trans2-semana-odd-bg: #ffffff;
        --trans2-semana-odd-hover: rgba(0, 0, 0, 0.02);
        --trans2-semana-even-bg: #f0f9ff;
        --trans2-semana-even-hover: #e0f2fe;
    }
    [data-theme="dark"] {
        --trans2-border-internal: rgba(255, 255, 255, 0.18);
        --trans2-semana-border: #38bdf8; /* Cyan luminoso eléctrico */
        --trans2-semana-hdr-bg: rgba(56, 189, 248, 0.18);
        --trans2-semana-odd-bg: transparent;
        --trans2-semana-odd-hover: rgba(255, 255, 255, 0.03);
        --trans2-semana-even-bg: rgba(56, 189, 248, 0.06);
        --trans2-semana-even-hover: rgba(56, 189, 248, 0.11);
    }

    /* Líneas finas internas de la cuadrícula con background-clip */
    .table-trans2 th, .table-trans2 td {
        border: 1px solid var(--trans2-border-internal) !important;
        background-clip: padding-box !important;
        box-sizing: border-box;
    }

    /* ─── CAJA ENCABEZADO DE SEMANA (Líneas finas de 1.5px) ─── */
    .table-trans2 thead tr.tr-semanas th.th-semana {
        border: 1.5px solid var(--trans2-semana-border) !important;
        box-shadow: inset 0 0 0 0.5px var(--trans2-semana-border) !important;
        background: var(--trans2-semana-hdr-bg) !important;
        color: var(--trans2-semana-border) !important;
        font-weight: 800 !important;
        font-size: 0.82rem !important;
        letter-spacing: 0.5px;
    }

    .table-trans2 thead tr.tr-rangos th {
        border-bottom: 1.5px solid var(--trans2-semana-border) !important;
        box-shadow: inset 0 -1.5px 0 var(--trans2-semana-border) !important;
        color: var(--text-primary) !important;
    }
    .table-trans2 thead tr.tr-rangos th.th-semana-start {
        border-left: 1.5px solid var(--trans2-semana-border) !important;
        box-shadow: inset 1.5px 0 0 var(--trans2-semana-border), inset 0 -1.5px 0 var(--trans2-semana-border) !important;
    }
    .table-trans2 thead tr.tr-rangos th.th-semana-end {
        border-right: 1.5px solid var(--trans2-semana-border) !important;
        box-shadow: inset -1.5px 0 0 var(--trans2-semana-border), inset 0 -1.5px 0 var(--trans2-semana-border) !important;
    }

    /* ─── MARCO PERIMETRAL EXTERIOR DEL BLOQUE DE DATOS (1.5px) ─── */
    /* Borde izquierdo (columna <1) */
    .table-trans2 tbody td.td-semana-start {
        border-left: 1.5px solid var(--trans2-semana-border) !important;
        box-shadow: inset 1.5px 0 0 var(--trans2-semana-border) !important;
    }
    /* Borde derecho (columna 15+) */
    .table-trans2 tbody td.td-semana-end {
        border-right: 1.5px solid var(--trans2-semana-border) !important;
        box-shadow: inset -1.5px 0 0 var(--trans2-semana-border) !important;
    }
    /* Borde superior del bloque de datos */
    .table-trans2 tbody tr.row-semana-top td.td-semana-cell {
        border-top: 1.5px solid var(--trans2-semana-border) !important;
        box-shadow: inset 0 1.5px 0 var(--trans2-semana-border) !important;
    }
    /* Borde inferior del bloque de datos */
    .table-trans2 tbody tr.row-semana-bottom td.td-semana-cell {
        border-bottom: 1.5px solid var(--trans2-semana-border) !important;
        box-shadow: inset 0 -1.5px 0 var(--trans2-semana-border) !important;
    }

    /* Esquinas compuestas */
    .table-trans2 tbody tr.row-semana-top td.td-semana-start {
        box-shadow: inset 1.5px 1.5px 0 var(--trans2-semana-border) !important;
    }
    .table-trans2 tbody tr.row-semana-top td.td-semana-end {
        box-shadow: inset -1.5px 1.5px 0 var(--trans2-semana-border) !important;
    }
    .table-trans2 tbody tr.row-semana-bottom td.td-semana-start {
        box-shadow: inset 1.5px -1.5px 0 var(--trans2-semana-border) !important;
    }
    .table-trans2 tbody tr.row-semana-bottom td.td-semana-end {
        box-shadow: inset -1.5px -1.5px 0 var(--trans2-semana-border) !important;
    }

    /* Separador en columna de títulos fijos */
    .table-trans2 thead tr.tr-semanas th.th-sticky-enfermedad,
    .table-trans2 tbody td.td-sticky-enfermedad {
        border-right: 1.5px solid var(--trans2-semana-border) !important;
        box-shadow: inset -1.5px 0 0 var(--trans2-semana-border), 2px 0 4px rgba(0, 0, 0, 0.08) !important;
    }

    .table-trans2 th.col-semana-even,
    .table-trans2 td.col-semana-even {
        background-color: var(--trans2-semana-even-bg) !important;
    }
    .table-trans2 tr:hover td.col-semana-even {
        background-color: var(--trans2-semana-even-hover) !important;
    }
    .table-trans2 th.col-semana-odd,
    .table-trans2 td.col-semana-odd {
        background-color: var(--trans2-semana-odd-bg) !important;
    }
    .table-trans2 tr:hover td.col-semana-odd {
        background-color: var(--trans2-semana-odd-hover) !important;
    }
</style>

<!-- Barra de Filtros y Alternador Anverso/Reverso en Una Sola Fila Horizontal -->
<div class="filter-container no-print mb-2"
    style="background: var(--bg-surface) !important; border: 1px solid var(--border-color) !important; border-radius: var(--radius-md, 10px) !important; padding: 0.5rem 0.85rem !important; display: flex !important; flex-direction: row !important; align-items: center !important; justify-content: space-between !important; flex-wrap: nowrap !important; gap: 8px !important; box-shadow: var(--shadow-sm) !important; width: 100% !important;">
    <form id="filter-form" action="{{ route('informes.trans2') }}" method="GET"
        style="display: flex !important; flex-direction: row !important; align-items: center !important; flex-wrap: nowrap !important; gap: 8px !important; margin: 0 !important; flex: 1 1 auto !important; min-width: 0 !important;">
        <!-- Año -->
        <div style="width: 95px !important; min-width: 95px !important; flex-shrink: 0 !important;">
            <select name="ano" class="form-control form-control-sm font-weight-bold ajax-filter"
                style="width: 100% !important; height: 34px !important; font-weight: 700 !important; font-size: 0.82rem !important; background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color);">
                @foreach($anos as $a)
                    <option value="{{ $a }}" {{ $a == $anoDefault ? 'selected' : '' }}>{{ $a }}</option>
                @endforeach
            </select>
        </div>

        <!-- Mes -->
        <div style="width: 140px !important; min-width: 140px !important; flex-shrink: 0 !important;">
            <select name="mes" class="form-control form-control-sm font-weight-bold text-uppercase ajax-filter"
                style="width: 100% !important; height: 34px !important; font-weight: 700 !important; font-size: 0.82rem !important; background-color: var(--input-bg); color: var(--text-primary); border-color: var(--border-color);">
                @foreach($meses as $m)
                    <option value="{{ $m }}" {{ strtoupper($m) == strtoupper($mesDefault) ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
            </select>
        </div>

        <!-- Segmented Button Anverso / Reverso -->
        <div style="display: inline-flex !important; flex-direction: row !important; align-items: center !important; background: var(--bg-subtle) !important; padding: 2px !important; border-radius: var(--radius-sm, 8px) !important; border: 1px solid var(--border-color) !important; flex-shrink: 0 !important; margin-left: 4px !important;">
            <button type="button" class="btn btn-sm btn-primary btn-toggle-side px-3 py-1 font-weight-bold" data-side="obverso" style="font-size: 0.75rem; border-radius: var(--radius-xs, 4px);">
                <i class="bi bi-file-earmark-text mr-1"></i> ANVERSO (PARTE A)
            </button>
            <button type="button" class="btn btn-sm btn-subtle btn-toggle-side px-3 py-1 font-weight-bold" data-side="reverso" style="font-size: 0.75rem; border-radius: var(--radius-xs, 4px);">
                <i class="bi bi-file-earmark-medical mr-1"></i> REVERSO (PARTE B)
            </button>
        </div>
    </form>

    <!-- Acciones a la derecha -->
    <div style="display: flex !important; flex-direction: row !important; align-items: center !important; gap: 6px !important; flex-shrink: 0 !important; margin-left: auto !important;">
        <button type="button" class="btn btn-sm btn-subtle-primary" onclick="abrirModalComparacion()" title="Auditoría y Comparación Cruzada" style="height: 32px; font-weight: 700; font-size: 0.75rem; display: inline-flex; align-items: center; gap: 4px; padding: 0 10px;">
            <i class="bi bi-diagram-3-fill"></i> Comparar
        </button>
        <button type="button" class="btn btn-icon btn-subtle btn-sm" onclick="copyToExcel()" title="Copiar al Portapapeles" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
            <i class="bi bi-clipboard-check"></i>
        </button>
        <button type="button" class="btn btn-icon btn-subtle btn-sm" onclick="window.print()" title="Imprimir informe" style="width: 32px; height: 32px; display: inline-flex; align-items: center; justify-content: center;">
            <i class="bi bi-printer"></i>
        </button>
    </div>
</div>

<!-- Contenedor de Tablas TRANS-2 -->
<div class="informe-table-container custom-scrollbar mb-3 flex-grow-1 position-relative" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md); overflow: auto; box-shadow: var(--shadow-sm); max-height: calc(100vh - 220px);">

    <!-- PARTE A: ANVERSO -->
    <div id="side-obverso" class="trans2-side-container">
        <div class="trans2-side-banner py-2 px-3 d-flex justify-content-between align-items-center">
            <span class="font-weight-bold small text-uppercase d-flex align-items-center gap-1.5" style="color: var(--text-primary); letter-spacing: 0.5px;">
                <i class="bi bi-file-earmark-text text-primary"></i> PARTE A: ANVERSO - {{ $mesDefault }} {{ $anoDefault }}
            </span>
            <span class="badge badge-subtle-primary font-weight-bold px-2 py-0.5" style="font-size: 0.72rem;">Notificación Obligatoria</span>
        </div>

        <table class="table table-trans2 mb-0" style="width: 100%; min-width: {{ 325 + (count($semanasMes) * 160) }}px;">
            <thead>
                <tr class="tr-semanas">
                    <th rowspan="2" class="text-center align-middle th-sticky-codigo" style="width: 85px; min-width: 85px; font-size: 0.76rem; font-weight: 800; color: var(--text-primary);">
                        CÓDIGO
                    </th>
                    <th rowspan="2" class="align-middle th-sticky-enfermedad" style="width: 240px; min-width: 240px; font-size: 0.8rem; font-weight: 800; color: var(--text-primary);">
                        ENFERMEDADES / EVENTOS
                    </th>
                    @foreach($semanasMes as $idx => $se)
                        @php $weekClass = ($idx % 2 === 0) ? 'col-semana-odd' : 'col-semana-even'; @endphp
                        <th colspan="4" class="text-center py-1 th-semana {{ $weekClass }}">
                            SEMANA No. {{ $se }}
                        </th>
                    @endforeach
                </tr>
                <tr class="tr-rangos">
                    @foreach($semanasMes as $idx => $se)
                        @php $weekClass = ($idx % 2 === 0) ? 'col-semana-odd' : 'col-semana-even'; @endphp
                        <th class="text-center py-1 th-rango th-semana-start {{ $weekClass }}" style="width: 40px; font-size: 0.75rem; font-weight: 700;">&lt;1</th>
                        <th class="text-center py-1 th-rango {{ $weekClass }}" style="width: 40px; font-size: 0.75rem; font-weight: 700;">1-4</th>
                        <th class="text-center py-1 th-rango {{ $weekClass }}" style="width: 40px; font-size: 0.75rem; font-weight: 700;">5-14</th>
                        <th class="text-center py-1 th-rango th-semana-end {{ $weekClass }}" style="width: 40px; font-size: 0.75rem; font-weight: 700;">15+</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($sections as $section)
                    @if($section['side'] === 'obverso')
                        <tr>
                            <td colspan="{{ 2 + (count($semanasMes) * 4) }}" style="padding: 7px 14px; font-weight: 800; font-size: 0.8rem; color: var(--color-primary); background: rgba(77, 124, 254, 0.12); border-color: var(--border-color); text-transform: uppercase; letter-spacing: 0.8px;">
                                <i class="bi bi-folder-fill mr-1.5 opacity-75"></i> {{ $section['title'] }}
                            </td>
                        </tr>
                        @php $rowCount = count($section['rows']); @endphp
                        @foreach($section['rows'] as $rIdx => $row)
                            @php
                                $isFirstRow = ($rIdx === 0);
                                $isLastRow = ($rIdx === $rowCount - 1);
                                $edgeRowClass = ($isFirstRow ? 'row-semana-top ' : '') . ($isLastRow ? 'row-semana-bottom ' : '');
                            @endphp
                            <tr class="{{ $edgeRowClass }}" style="border-color: var(--border-color);">
                                <td class="text-center font-monospace font-weight-bold td-sticky-codigo" style="font-size: 0.76rem; color: var(--color-primary);">
                                    {{ $row['code'] }}
                                </td>
                                <td class="font-weight-600 text-uppercase td-sticky-enfermedad" style="font-size: 0.8rem; color: var(--text-primary);">
                                    {{ $row['label'] }}
                                </td>
                                @foreach($semanasMes as $idx => $se)
                                    @php
                                        $weekClass = ($idx % 2 === 0) ? 'col-semana-odd' : 'col-semana-even';
                                        $cLess1 = $results[$row['id']][$se]['less_1'] ?? 0;
                                        $c14 = $results[$row['id']][$se]['1_4'] ?? 0;
                                        $c514 = $results[$row['id']][$se]['5_14'] ?? 0;
                                        $c15p = $results[$row['id']][$se]['15_plus'] ?? 0;
                                    @endphp
                                    <td class="text-center cell-clickable td-semana-cell td-semana-start {{ $weekClass }} {{ $cLess1 > 0 ? 'font-weight-bold text-primary font-monospace' : 'text-muted' }}" 
                                        onclick="fetchTrans2Details('{{ $row['id'] }}', 'less_1', {{ $se }})" 
                                        style="cursor: pointer; font-size: 0.86rem; {{ $cLess1 > 0 ? 'background: rgba(77, 124, 254, 0.14) !important;' : '' }}">
                                        {{ $cLess1 ?: '0' }}
                                    </td>
                                    <td class="text-center cell-clickable td-semana-cell {{ $weekClass }} {{ $c14 > 0 ? 'font-weight-bold text-primary font-monospace' : 'text-muted' }}" 
                                        onclick="fetchTrans2Details('{{ $row['id'] }}', '1_4', {{ $se }})" 
                                        style="cursor: pointer; font-size: 0.86rem; {{ $c14 > 0 ? 'background: rgba(77, 124, 254, 0.14) !important;' : '' }}">
                                        {{ $c14 ?: '0' }}
                                    </td>
                                    <td class="text-center cell-clickable td-semana-cell {{ $weekClass }} {{ $c514 > 0 ? 'font-weight-bold text-primary font-monospace' : 'text-muted' }}" 
                                        onclick="fetchTrans2Details('{{ $row['id'] }}', '5_14', {{ $se }})" 
                                        style="cursor: pointer; font-size: 0.86rem; {{ $c514 > 0 ? 'background: rgba(77, 124, 254, 0.14) !important;' : '' }}">
                                        {{ $c514 ?: '0' }}
                                    </td>
                                    <td class="text-center cell-clickable td-semana-cell td-semana-end {{ $weekClass }} {{ $c15p > 0 ? 'font-weight-bold text-primary font-monospace' : 'text-muted' }}" 
                                        onclick="fetchTrans2Details('{{ $row['id'] }}', '15_plus', {{ $se }})" 
                                        style="cursor: pointer; font-size: 0.86rem; {{ $c15p > 0 ? 'background: rgba(77, 124, 254, 0.14) !important;' : '' }}">
                                        {{ $c15p ?: '0' }}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

    <!-- PARTE B: REVERSO -->
    <div id="side-reverso" class="trans2-side-container d-none">
        <div class="trans2-side-banner py-2 px-3 d-flex justify-content-between align-items-center">
            <span class="font-weight-bold small text-uppercase d-flex align-items-center gap-1.5" style="color: var(--text-primary); letter-spacing: 0.5px;">
                <i class="bi bi-file-earmark-medical text-primary"></i> PARTE B: REVERSO - {{ $mesDefault }} {{ $anoDefault }}
            </span>
            <span class="badge badge-subtle-info font-weight-bold px-2 py-0.5" style="font-size: 0.72rem;">Zoonosis y Enfermedades Vectoriales</span>
        </div>

        <table class="table table-trans2 mb-0" style="width: 100%; min-width: {{ 325 + (count($semanasMes) * 160) }}px;">
            <thead>
                <tr class="tr-semanas">
                    <th rowspan="2" class="text-center align-middle th-sticky-codigo" style="width: 85px; min-width: 85px; font-size: 0.76rem; font-weight: 800; color: var(--text-primary);">
                        CÓDIGO
                    </th>
                    <th rowspan="2" class="align-middle th-sticky-enfermedad" style="width: 240px; min-width: 240px; font-size: 0.8rem; font-weight: 800; color: var(--text-primary);">
                        ENFERMEDADES / EVENTOS
                    </th>
                    @foreach($semanasMes as $idx => $se)
                        @php $weekClass = ($idx % 2 === 0) ? 'col-semana-odd' : 'col-semana-even'; @endphp
                        <th colspan="4" class="text-center py-1 th-semana {{ $weekClass }}">
                            SEMANA No. {{ $se }}
                        </th>
                    @endforeach
                </tr>
                <tr class="tr-rangos">
                    @foreach($semanasMes as $idx => $se)
                        @php $weekClass = ($idx % 2 === 0) ? 'col-semana-odd' : 'col-semana-even'; @endphp
                        <th class="text-center py-1 th-rango th-semana-start {{ $weekClass }}" style="width: 40px; font-size: 0.75rem; font-weight: 700;">&lt;1</th>
                        <th class="text-center py-1 th-rango {{ $weekClass }}" style="width: 40px; font-size: 0.75rem; font-weight: 700;">1-4</th>
                        <th class="text-center py-1 th-rango {{ $weekClass }}" style="width: 40px; font-size: 0.75rem; font-weight: 700;">5-14</th>
                        <th class="text-center py-1 th-rango th-semana-end {{ $weekClass }}" style="width: 40px; font-size: 0.75rem; font-weight: 700;">15+</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($sections as $section)
                    @if($section['side'] === 'reverso')
                        <tr>
                            <td colspan="{{ 2 + (count($semanasMes) * 4) }}" style="padding: 7px 14px; font-weight: 800; font-size: 0.8rem; color: var(--color-primary); background: rgba(77, 124, 254, 0.12); border-color: var(--border-color); text-transform: uppercase; letter-spacing: 0.8px;">
                                <i class="bi bi-folder-fill mr-1.5 opacity-75"></i> {{ $section['title'] }}
                            </td>
                        </tr>
                        @php $rowCount = count($section['rows']); @endphp
                        @foreach($section['rows'] as $rIdx => $row)
                            @php
                                $isFirstRow = ($rIdx === 0);
                                $isLastRow = ($rIdx === $rowCount - 1);
                                $edgeRowClass = ($isFirstRow ? 'row-semana-top ' : '') . ($isLastRow ? 'row-semana-bottom ' : '');
                            @endphp
                            <tr class="{{ $edgeRowClass }}" style="border-color: var(--border-color);">
                                <td class="text-center font-monospace font-weight-bold td-sticky-codigo" style="font-size: 0.76rem; color: var(--color-primary);">
                                    {{ $row['code'] }}
                                </td>
                                <td class="font-weight-600 text-uppercase td-sticky-enfermedad" style="font-size: 0.8rem; color: var(--text-primary);">
                                    {{ $row['label'] }}
                                </td>
                                @foreach($semanasMes as $idx => $se)
                                    @php
                                        $weekClass = ($idx % 2 === 0) ? 'col-semana-odd' : 'col-semana-even';
                                        $cLess1 = $results[$row['id']][$se]['less_1'] ?? 0;
                                        $c14 = $results[$row['id']][$se]['1_4'] ?? 0;
                                        $c514 = $results[$row['id']][$se]['5_14'] ?? 0;
                                        $c15p = $results[$row['id']][$se]['15_plus'] ?? 0;
                                    @endphp
                                    <td class="text-center cell-clickable td-semana-cell td-semana-start {{ $weekClass }} {{ $cLess1 > 0 ? 'font-weight-bold text-primary font-monospace' : 'text-muted' }}" 
                                        onclick="fetchTrans2Details('{{ $row['id'] }}', 'less_1', {{ $se }})" 
                                        style="cursor: pointer; font-size: 0.86rem; {{ $cLess1 > 0 ? 'background: rgba(77, 124, 254, 0.14) !important;' : '' }}">
                                        {{ $cLess1 ?: '0' }}
                                    </td>
                                    <td class="text-center cell-clickable td-semana-cell {{ $weekClass }} {{ $c14 > 0 ? 'font-weight-bold text-primary font-monospace' : 'text-muted' }}" 
                                        onclick="fetchTrans2Details('{{ $row['id'] }}', '1_4', {{ $se }})" 
                                        style="cursor: pointer; font-size: 0.86rem; {{ $c14 > 0 ? 'background: rgba(77, 124, 254, 0.14) !important;' : '' }}">
                                        {{ $c14 ?: '0' }}
                                    </td>
                                    <td class="text-center cell-clickable td-semana-cell {{ $weekClass }} {{ $c514 > 0 ? 'font-weight-bold text-primary font-monospace' : 'text-muted' }}" 
                                        onclick="fetchTrans2Details('{{ $row['id'] }}', '5_14', {{ $se }})" 
                                        style="cursor: pointer; font-size: 0.86rem; {{ $c514 > 0 ? 'background: rgba(77, 124, 254, 0.14) !important;' : '' }}">
                                        {{ $c514 ?: '0' }}
                                    </td>
                                    <td class="text-center cell-clickable td-semana-cell td-semana-end {{ $weekClass }} {{ $c15p > 0 ? 'font-weight-bold text-primary font-monospace' : 'text-muted' }}" 
                                        onclick="fetchTrans2Details('{{ $row['id'] }}', '15_plus', {{ $se }})" 
                                        style="cursor: pointer; font-size: 0.86rem; {{ $c15p > 0 ? 'background: rgba(77, 124, 254, 0.14) !important;' : '' }}">
                                        {{ $c15p ?: '0' }}
                                    </td>
                                @endforeach
                            </tr>
                        @endforeach
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>

</div>

<!-- Modal de Detalles TRANS-2 -->
<div class="modal fade" id="modalTrans2Detalles" tabindex="-1" role="dialog" aria-labelledby="modalTrans2DetallesLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content" style="background: var(--bg-surface); border: 1px solid var(--border-color); color: var(--text-primary); border-radius: var(--radius-md); box-shadow: var(--shadow-xl);">
            <div class="modal-header" style="background-color: var(--bg-subtle); border-bottom-color: var(--border-color);">
                <div>
                    <h5 class="modal-title font-weight-bold" id="modalTrans2Title" style="color: var(--text-primary);"></h5>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        <span class="badge badge-subtle-primary font-weight-bold" id="modalTrans2SemanaBadge" style="font-size: 0.75rem;"></span>
                        <span class="text-muted small">Desglose de pacientes TRANS-2</span>
                    </div>
                </div>
                <button type="button" class="btn btn-icon btn-sm btn-subtle" data-dismiss="modal" aria-label="Close">
                    <i class="bi bi-x-lg"></i>
                </button>
            </div>

            <div class="modal-body p-0">
                <div class="table-responsive p-3" style="max-height: 420px; overflow-y: auto;">
                    <table class="table table-sm table-hover mb-0" style="font-size: 0.82rem;">
                        <thead>
                            <tr style="background: var(--bg-subtle); color: var(--text-muted);">
                                <th class="py-2 px-3">Fecha</th>
                                <th class="py-2 px-3">Expediente</th>
                                <th class="py-2 px-3">Sexo</th>
                                <th class="py-2 px-3">Edad</th>
                                <th class="py-2 px-3">Diagnóstico</th>
                                <th class="py-2 px-3">Médico</th>
                            </tr>
                        </thead>
                        <tbody id="modalTrans2TableBody">
                            <!-- Renderizado dinámico -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer" style="background-color: var(--bg-subtle); border-top-color: var(--border-color);">
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal" style="font-weight: 600;">Cerrar</button>
            </div>
        </div>
    </div>
</div>
