<!-- Barra de Filtros y Alternador Anverso/Reverso -->
<div class="informe-filters-card mb-3 no-print">
    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
        <form id="filter-form" action="{{ route('informes.trans2') }}" method="GET" class="d-flex flex-wrap align-items-center gap-3">
            <div class="filter-group">
                <label class="filter-label">Año</label>
                <select name="ano" class="form-control form-control-sm ajax-filter" style="min-width: 100px;">
                    @foreach($anos as $a)
                        <option value="{{ $a }}" {{ $a == $anoDefault ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
            </div>

            <div class="filter-group">
                <label class="filter-label">Mes</label>
                <select name="mes" class="form-control form-control-sm ajax-filter" style="min-width: 140px;">
                    @foreach($meses as $m)
                        <option value="{{ $m }}" {{ strtoupper($m) == strtoupper($mesDefault) ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
        </form>

        <!-- Toggle Anverso / Reverso -->
        <div class="btn-group" role="group" aria-label="Lado de TRANS-2">
            <button type="button" class="btn btn-sm btn-primary active btn-toggle-side" data-side="obverso">
                <i class="bi bi-file-earmark-text mr-1"></i> ANVERSO (PARTE A)
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary btn-toggle-side" data-side="reverso">
                <i class="bi bi-file-earmark-medical mr-1"></i> REVERSO (PARTE B)
            </button>
        </div>

        <div class="d-flex align-items-center gap-2">
            <button type="button" class="btn btn-sm btn-outline-primary" onclick="copyToExcel()" title="Copiar al Portapapeles">
                <i class="bi bi-clipboard-check mr-1"></i> Copiar
            </button>
            <button type="button" class="btn btn-sm btn-outline-secondary" onclick="window.print()" title="Imprimir informe">
                <i class="bi bi-printer mr-1"></i> Imprimir
            </button>
        </div>
    </div>
</div>

<!-- Contenedor de Tablas TRANS-2 -->
<div class="informe-table-container custom-scrollbar mb-4" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md); overflow: auto; box-shadow: var(--shadow-sm); max-height: calc(100vh - 220px);">

    <!-- PARTE A: ANVERSO -->
    <div id="side-obverso" class="trans2-side-container">
        <div class="py-2 px-3 d-flex justify-content-between align-items-center sticky-top" style="background: var(--bg-subtle); border-bottom: 1px solid var(--border-color); z-index: 10;">
            <span class="font-weight-bold small text-uppercase" style="color: var(--text-primary); letter-spacing: 0.5px;">
                PARTE A: ANVERSO - {{ $mesDefault }} {{ $anoDefault }}
            </span>
            <span class="text-muted small">Notificación Obligatoria</span>
        </div>

        <table class="table table-bordered table-trans2 mb-0" style="width: 100%; border-collapse: separate; border-spacing: 0; min-width: {{ 260 + (count($semanasMes) * 160) }}px;">
            <thead>
                <tr style="background: var(--bg-subtle);">
                    <th rowspan="2" style="width: 65px; text-align: center; vertical-align: middle; position: sticky; left: 0; background: var(--bg-subtle); z-index: 5; border-color: var(--border-color); font-size: 0.76rem; font-weight: 700; color: var(--text-primary);">
                        CÓDIGO
                    </th>
                    <th rowspan="2" style="width: 220px; vertical-align: middle; position: sticky; left: 65px; background: var(--bg-subtle); z-index: 5; border-color: var(--border-color); font-size: 0.8rem; font-weight: 700; color: var(--text-primary);">
                        ENFERMEDADES / EVENTOS
                    </th>
                    @foreach($semanasMes as $se)
                        <th colspan="4" class="text-center" style="border-color: var(--border-color); font-size: 0.78rem; font-weight: 700; color: var(--color-primary); background: var(--bg-subtle);">
                            SEMANA No. {{ $se }}
                        </th>
                    @endforeach
                </tr>
                <tr style="background: var(--bg-subtle);">
                    @foreach($semanasMes as $se)
                        <th class="text-center" style="width: 40px; font-size: 0.72rem; font-weight: 600; color: var(--text-muted); border-color: var(--border-color);">&lt;1</th>
                        <th class="text-center" style="width: 40px; font-size: 0.72rem; font-weight: 600; color: var(--text-muted); border-color: var(--border-color);">1-4</th>
                        <th class="text-center" style="width: 40px; font-size: 0.72rem; font-weight: 600; color: var(--text-muted); border-color: var(--border-color);">5-14</th>
                        <th class="text-center" style="width: 40px; font-size: 0.72rem; font-weight: 600; color: var(--text-muted); border-color: var(--border-color);">15+</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($sections as $section)
                    @if($section['side'] === 'obverso')
                        <tr style="background: rgba(245, 158, 11, 0.12);">
                            <td colspan="{{ 2 + (count($semanasMes) * 4) }}" style="padding: 6px 12px; font-weight: 800; font-size: 0.82rem; color: #b45309; border-color: var(--border-color); position: sticky; left: 0; z-index: 4;">
                                {{ $section['title'] }}
                            </td>
                        </tr>
                        @foreach($section['rows'] as $row)
                            <tr style="border-color: var(--border-color);">
                                <td class="text-center text-muted small" style="position: sticky; left: 0; background: var(--bg-surface); z-index: 3; border-color: var(--border-color); font-size: 0.75rem;">
                                    {{ $row['code'] }}
                                </td>
                                <td class="font-weight-600" style="position: sticky; left: 65px; background: var(--bg-surface); z-index: 3; border-color: var(--border-color); font-size: 0.82rem; color: var(--text-primary);">
                                    {{ $row['label'] }}
                                </td>
                                @foreach($semanasMes as $se)
                                    @php
                                        $cLess1 = $results[$row['id']][$se]['less_1'] ?? 0;
                                        $c14 = $results[$row['id']][$se]['1_4'] ?? 0;
                                        $c514 = $results[$row['id']][$se]['5_14'] ?? 0;
                                        $c15p = $results[$row['id']][$se]['15_plus'] ?? 0;
                                    @endphp
                                    <td class="text-center cell-clickable {{ $cLess1 > 0 ? 'font-weight-bold text-primary' : 'text-muted' }}" 
                                        onclick="fetchTrans2Details('{{ $row['id'] }}', 'less_1', {{ $se }})" 
                                        style="cursor: pointer; font-size: 0.85rem; border-color: var(--border-color);">
                                        {{ $cLess1 ?: '0' }}
                                    </td>
                                    <td class="text-center cell-clickable {{ $c14 > 0 ? 'font-weight-bold text-primary' : 'text-muted' }}" 
                                        onclick="fetchTrans2Details('{{ $row['id'] }}', '1_4', {{ $se }})" 
                                        style="cursor: pointer; font-size: 0.85rem; border-color: var(--border-color);">
                                        {{ $c14 ?: '0' }}
                                    </td>
                                    <td class="text-center cell-clickable {{ $c514 > 0 ? 'font-weight-bold text-primary' : 'text-muted' }}" 
                                        onclick="fetchTrans2Details('{{ $row['id'] }}', '5_14', {{ $se }})" 
                                        style="cursor: pointer; font-size: 0.85rem; border-color: var(--border-color);">
                                        {{ $c514 ?: '0' }}
                                    </td>
                                    <td class="text-center cell-clickable {{ $c15p > 0 ? 'font-weight-bold text-primary' : 'text-muted' }}" 
                                        onclick="fetchTrans2Details('{{ $row['id'] }}', '15_plus', {{ $se }})" 
                                        style="cursor: pointer; font-size: 0.85rem; border-color: var(--border-color);">
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
        <div class="py-2 px-3 d-flex justify-content-between align-items-center sticky-top" style="background: var(--bg-subtle); border-bottom: 1px solid var(--border-color); z-index: 10;">
            <span class="font-weight-bold small text-uppercase" style="color: var(--text-primary); letter-spacing: 0.5px;">
                PARTE B: REVERSO - {{ $mesDefault }} {{ $anoDefault }}
            </span>
            <span class="text-muted small">Zoonosis y Enfermedades Vectoriales</span>
        </div>

        <table class="table table-bordered table-trans2 mb-0" style="width: 100%; border-collapse: separate; border-spacing: 0; min-width: {{ 260 + (count($semanasMes) * 160) }}px;">
            <thead>
                <tr style="background: var(--bg-subtle);">
                    <th rowspan="2" style="width: 65px; text-align: center; vertical-align: middle; position: sticky; left: 0; background: var(--bg-subtle); z-index: 5; border-color: var(--border-color); font-size: 0.76rem; font-weight: 700; color: var(--text-primary);">
                        CÓDIGO
                    </th>
                    <th rowspan="2" style="width: 220px; vertical-align: middle; position: sticky; left: 65px; background: var(--bg-subtle); z-index: 5; border-color: var(--border-color); font-size: 0.8rem; font-weight: 700; color: var(--text-primary);">
                        ENFERMEDADES / EVENTOS
                    </th>
                    @foreach($semanasMes as $se)
                        <th colspan="4" class="text-center" style="border-color: var(--border-color); font-size: 0.78rem; font-weight: 700; color: var(--color-primary); background: var(--bg-subtle);">
                            SEMANA No. {{ $se }}
                        </th>
                    @endforeach
                </tr>
                <tr style="background: var(--bg-subtle);">
                    @foreach($semanasMes as $se)
                        <th class="text-center" style="width: 40px; font-size: 0.72rem; font-weight: 600; color: var(--text-muted); border-color: var(--border-color);">&lt;1</th>
                        <th class="text-center" style="width: 40px; font-size: 0.72rem; font-weight: 600; color: var(--text-muted); border-color: var(--border-color);">1-4</th>
                        <th class="text-center" style="width: 40px; font-size: 0.72rem; font-weight: 600; color: var(--text-muted); border-color: var(--border-color);">5-14</th>
                        <th class="text-center" style="width: 40px; font-size: 0.72rem; font-weight: 600; color: var(--text-muted); border-color: var(--border-color);">15+</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @foreach($sections as $section)
                    @if($section['side'] === 'reverso')
                        <tr style="background: rgba(245, 158, 11, 0.12);">
                            <td colspan="{{ 2 + (count($semanasMes) * 4) }}" style="padding: 6px 12px; font-weight: 800; font-size: 0.82rem; color: #b45309; border-color: var(--border-color); position: sticky; left: 0; z-index: 4;">
                                {{ $section['title'] }}
                            </td>
                        </tr>
                        @foreach($section['rows'] as $row)
                            <tr style="border-color: var(--border-color);">
                                <td class="text-center text-muted small" style="position: sticky; left: 0; background: var(--bg-surface); z-index: 3; border-color: var(--border-color); font-size: 0.75rem;">
                                    {{ $row['code'] }}
                                </td>
                                <td class="font-weight-600" style="position: sticky; left: 65px; background: var(--bg-surface); z-index: 3; border-color: var(--border-color); font-size: 0.82rem; color: var(--text-primary);">
                                    {{ $row['label'] }}
                                </td>
                                @foreach($semanasMes as $se)
                                    @php
                                        $cLess1 = $results[$row['id']][$se]['less_1'] ?? 0;
                                        $c14 = $results[$row['id']][$se]['1_4'] ?? 0;
                                        $c514 = $results[$row['id']][$se]['5_14'] ?? 0;
                                        $c15p = $results[$row['id']][$se]['15_plus'] ?? 0;
                                    @endphp
                                    <td class="text-center cell-clickable {{ $cLess1 > 0 ? 'font-weight-bold text-primary' : 'text-muted' }}" 
                                        onclick="fetchTrans2Details('{{ $row['id'] }}', 'less_1', {{ $se }})" 
                                        style="cursor: pointer; font-size: 0.85rem; border-color: var(--border-color);">
                                        {{ $cLess1 ?: '0' }}
                                    </td>
                                    <td class="text-center cell-clickable {{ $c14 > 0 ? 'font-weight-bold text-primary' : 'text-muted' }}" 
                                        onclick="fetchTrans2Details('{{ $row['id'] }}', '1_4', {{ $se }})" 
                                        style="cursor: pointer; font-size: 0.85rem; border-color: var(--border-color);">
                                        {{ $c14 ?: '0' }}
                                    </td>
                                    <td class="text-center cell-clickable {{ $c514 > 0 ? 'font-weight-bold text-primary' : 'text-muted' }}" 
                                        onclick="fetchTrans2Details('{{ $row['id'] }}', '5_14', {{ $se }})" 
                                        style="cursor: pointer; font-size: 0.85rem; border-color: var(--border-color);">
                                        {{ $c514 ?: '0' }}
                                    </td>
                                    <td class="text-center cell-clickable {{ $c15p > 0 ? 'font-weight-bold text-primary' : 'text-muted' }}" 
                                        onclick="fetchTrans2Details('{{ $row['id'] }}', '15_plus', {{ $se }})" 
                                        style="cursor: pointer; font-size: 0.85rem; border-color: var(--border-color);">
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
            <div class="modal-header" style="border-bottom-color: var(--border-color);">
                <div>
                    <h5 class="modal-title font-weight-bold" id="modalTrans2Title" style="color: var(--text-primary);"></h5>
                    <div class="d-flex align-items-center gap-2 mt-1">
                        <span class="badge badge-primary" id="modalTrans2SemanaBadge" style="font-size: 0.72rem;"></span>
                        <span class="text-muted small">Desglose de pacientes TRANS-2</span>
                    </div>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close" style="color: var(--text-primary);">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>

            <div class="modal-body p-0">
                <div class="table-responsive p-3" style="max-height: 420px; overflow-y: auto;">
                    <table class="table table-sm table-hover mb-0" style="font-size: 0.8rem;">
                        <thead>
                            <tr style="background: var(--bg-subtle); color: var(--text-muted);">
                                <th>Fecha</th>
                                <th>Expediente</th>
                                <th>Sexo</th>
                                <th>Edad</th>
                                <th>Diagnóstico</th>
                                <th>Médico</th>
                            </tr>
                        </thead>
                        <tbody id="modalTrans2TableBody">
                            <!-- Renderizado dinámico -->
                        </tbody>
                    </table>
                </div>
            </div>

            <div class="modal-footer" style="border-top-color: var(--border-color);">
                <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
            </div>
        </div>
    </div>
</div>
