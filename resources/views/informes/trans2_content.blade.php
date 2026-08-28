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
        <div class="py-2 px-3 d-flex justify-content-between align-items-center sticky-top" style="background: var(--bg-subtle); border-bottom: 1px solid var(--border-color); z-index: 60;">
            <span class="font-weight-bold small text-uppercase d-flex align-items-center gap-1.5" style="color: var(--text-primary); letter-spacing: 0.5px;">
                <i class="bi bi-file-earmark-text text-primary"></i> PARTE A: ANVERSO - {{ $mesDefault }} {{ $anoDefault }}
            </span>
            <span class="badge badge-subtle-primary font-weight-bold px-2 py-0.5" style="font-size: 0.72rem;">Notificación Obligatoria</span>
        </div>

        <table class="table table-bordered table-trans2 mb-0" style="width: 100%; border-collapse: separate; border-spacing: 0; min-width: {{ 325 + (count($semanasMes) * 160) }}px;">
            <thead class="sticky-top" style="z-index: 50; top: 37px;">
                <tr style="background: var(--bg-subtle);">
                    <th rowspan="2" class="text-center align-middle" style="width: 85px; min-width: 85px; position: sticky; left: 0; background: var(--bg-subtle); z-index: 55; border-color: var(--border-color); font-size: 0.76rem; font-weight: 800; color: var(--text-primary); box-shadow: 2px 0 4px rgba(0,0,0,0.06);">
                        CÓDIGO
                    </th>
                    <th rowspan="2" class="align-middle" style="width: 240px; min-width: 240px; position: sticky; left: 85px; background: var(--bg-subtle); z-index: 55; border-color: var(--border-color); font-size: 0.8rem; font-weight: 800; color: var(--text-primary); box-shadow: 2px 0 4px rgba(0,0,0,0.06);">
                        ENFERMEDADES / EVENTOS
                    </th>
                    @foreach($semanasMes as $se)
                        <th colspan="4" class="text-center py-2" style="border-color: var(--border-color); font-size: 0.78rem; font-weight: 800; color: var(--color-primary); background: rgba(77, 124, 254, 0.12);">
                            SEMANA No. {{ $se }}
                        </th>
                    @endforeach
                </tr>
                <tr style="background: var(--bg-surface);">
                    @foreach($semanasMes as $se)
                        <th class="text-center py-1" style="width: 40px; font-size: 0.72rem; font-weight: 600; color: var(--text-muted); border-color: var(--border-color); background: var(--bg-surface);">&lt;1</th>
                        <th class="text-center py-1" style="width: 40px; font-size: 0.72rem; font-weight: 600; color: var(--text-muted); border-color: var(--border-color); background: var(--bg-surface);">1-4</th>
                        <th class="text-center py-1" style="width: 40px; font-size: 0.72rem; font-weight: 600; color: var(--text-muted); border-color: var(--border-color); background: var(--bg-surface);">5-14</th>
                        <th class="text-center py-1" style="width: 40px; font-size: 0.72rem; font-weight: 600; color: var(--text-muted); border-color: var(--border-color); background: var(--bg-surface);">15+</th>
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
                        @foreach($section['rows'] as $row)
                            <tr style="border-color: var(--border-color);">
                                <td class="text-center font-monospace font-weight-bold" style="position: sticky; left: 0; background: var(--bg-surface); z-index: 30; border-color: var(--border-color); font-size: 0.76rem; color: var(--color-primary); box-shadow: 2px 0 4px rgba(0,0,0,0.06);">
                                    {{ $row['code'] }}
                                </td>
                                <td class="font-weight-600 text-uppercase" style="position: sticky; left: 85px; background: var(--bg-surface); z-index: 30; border-color: var(--border-color); font-size: 0.8rem; color: var(--text-primary); box-shadow: 2px 0 4px rgba(0,0,0,0.06);">
                                    {{ $row['label'] }}
                                </td>
                                @foreach($semanasMes as $se)
                                    @php
                                        $cLess1 = $results[$row['id']][$se]['less_1'] ?? 0;
                                        $c14 = $results[$row['id']][$se]['1_4'] ?? 0;
                                        $c514 = $results[$row['id']][$se]['5_14'] ?? 0;
                                        $c15p = $results[$row['id']][$se]['15_plus'] ?? 0;
                                    @endphp
                                    <td class="text-center cell-clickable {{ $cLess1 > 0 ? 'font-weight-bold text-primary font-monospace' : 'text-muted' }}" 
                                        onclick="fetchTrans2Details('{{ $row['id'] }}', 'less_1', {{ $se }})" 
                                        style="cursor: pointer; font-size: 0.86rem; border-color: var(--border-color); {{ $cLess1 > 0 ? 'background: rgba(77, 124, 254, 0.08);' : '' }}">
                                        {{ $cLess1 ?: '0' }}
                                    </td>
                                    <td class="text-center cell-clickable {{ $c14 > 0 ? 'font-weight-bold text-primary font-monospace' : 'text-muted' }}" 
                                        onclick="fetchTrans2Details('{{ $row['id'] }}', '1_4', {{ $se }})" 
                                        style="cursor: pointer; font-size: 0.86rem; border-color: var(--border-color); {{ $c14 > 0 ? 'background: rgba(77, 124, 254, 0.08);' : '' }}">
                                        {{ $c14 ?: '0' }}
                                    </td>
                                    <td class="text-center cell-clickable {{ $c514 > 0 ? 'font-weight-bold text-primary font-monospace' : 'text-muted' }}" 
                                        onclick="fetchTrans2Details('{{ $row['id'] }}', '5_14', {{ $se }})" 
                                        style="cursor: pointer; font-size: 0.86rem; border-color: var(--border-color); {{ $c514 > 0 ? 'background: rgba(77, 124, 254, 0.08);' : '' }}">
                                        {{ $c514 ?: '0' }}
                                    </td>
                                    <td class="text-center cell-clickable {{ $c15p > 0 ? 'font-weight-bold text-primary font-monospace' : 'text-muted' }}" 
                                        onclick="fetchTrans2Details('{{ $row['id'] }}', '15_plus', {{ $se }})" 
                                        style="cursor: pointer; font-size: 0.86rem; border-color: var(--border-color); {{ $c15p > 0 ? 'background: rgba(77, 124, 254, 0.08);' : '' }}">
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
        <div class="py-2 px-3 d-flex justify-content-between align-items-center sticky-top" style="background: var(--bg-subtle); border-bottom: 1px solid var(--border-color); z-index: 60;">
            <span class="font-weight-bold small text-uppercase d-flex align-items-center gap-1.5" style="color: var(--text-primary); letter-spacing: 0.5px;">
                <i class="bi bi-file-earmark-medical text-primary"></i> PARTE B: REVERSO - {{ $mesDefault }} {{ $anoDefault }}
            </span>
            <span class="badge badge-subtle-info font-weight-bold px-2 py-0.5" style="font-size: 0.72rem;">Zoonosis y Enfermedades Vectoriales</span>
        </div>

        <table class="table table-bordered table-trans2 mb-0" style="width: 100%; border-collapse: separate; border-spacing: 0; min-width: {{ 325 + (count($semanasMes) * 160) }}px;">
            <thead class="sticky-top" style="z-index: 50; top: 37px;">
                <tr style="background: var(--bg-subtle);">
                    <th rowspan="2" class="text-center align-middle" style="width: 85px; min-width: 85px; position: sticky; left: 0; background: var(--bg-subtle); z-index: 55; border-color: var(--border-color); font-size: 0.76rem; font-weight: 800; color: var(--text-primary); box-shadow: 2px 0 4px rgba(0,0,0,0.06);">
                        CÓDIGO
                    </th>
                    <th rowspan="2" class="align-middle" style="width: 240px; min-width: 240px; position: sticky; left: 85px; background: var(--bg-subtle); z-index: 55; border-color: var(--border-color); font-size: 0.8rem; font-weight: 800; color: var(--text-primary); box-shadow: 2px 0 4px rgba(0,0,0,0.06);">
                        ENFERMEDADES / EVENTOS
                    </th>
                    @foreach($semanasMes as $se)
                        <th colspan="4" class="text-center py-2" style="border-color: var(--border-color); font-size: 0.78rem; font-weight: 800; color: var(--color-primary); background: rgba(77, 124, 254, 0.12);">
                            SEMANA No. {{ $se }}
                        </th>
                    @endforeach
                </tr>
                <tr style="background: var(--bg-surface);">
                    @foreach($semanasMes as $se)
                        <th class="text-center py-1" style="width: 40px; font-size: 0.72rem; font-weight: 600; color: var(--text-muted); border-color: var(--border-color); background: var(--bg-surface);">&lt;1</th>
                        <th class="text-center py-1" style="width: 40px; font-size: 0.72rem; font-weight: 600; color: var(--text-muted); border-color: var(--border-color); background: var(--bg-surface);">1-4</th>
                        <th class="text-center py-1" style="width: 40px; font-size: 0.72rem; font-weight: 600; color: var(--text-muted); border-color: var(--border-color); background: var(--bg-surface);">5-14</th>
                        <th class="text-center py-1" style="width: 40px; font-size: 0.72rem; font-weight: 600; color: var(--text-muted); border-color: var(--border-color); background: var(--bg-surface);">15+</th>
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
                        @foreach($section['rows'] as $row)
                            <tr style="border-color: var(--border-color);">
                                <td class="text-center font-monospace font-weight-bold" style="position: sticky; left: 0; background: var(--bg-surface); z-index: 30; border-color: var(--border-color); font-size: 0.76rem; color: var(--color-primary); box-shadow: 2px 0 4px rgba(0,0,0,0.06);">
                                    {{ $row['code'] }}
                                </td>
                                <td class="font-weight-600 text-uppercase" style="position: sticky; left: 85px; background: var(--bg-surface); z-index: 30; border-color: var(--border-color); font-size: 0.8rem; color: var(--text-primary); box-shadow: 2px 0 4px rgba(0,0,0,0.06);">
                                    {{ $row['label'] }}
                                </td>
                                @foreach($semanasMes as $se)
                                    @php
                                        $cLess1 = $results[$row['id']][$se]['less_1'] ?? 0;
                                        $c14 = $results[$row['id']][$se]['1_4'] ?? 0;
                                        $c514 = $results[$row['id']][$se]['5_14'] ?? 0;
                                        $c15p = $results[$row['id']][$se]['15_plus'] ?? 0;
                                    @endphp
                                    <td class="text-center cell-clickable {{ $cLess1 > 0 ? 'font-weight-bold text-primary font-monospace' : 'text-muted' }}" 
                                        onclick="fetchTrans2Details('{{ $row['id'] }}', 'less_1', {{ $se }})" 
                                        style="cursor: pointer; font-size: 0.86rem; border-color: var(--border-color); {{ $cLess1 > 0 ? 'background: rgba(77, 124, 254, 0.08);' : '' }}">
                                        {{ $cLess1 ?: '0' }}
                                    </td>
                                    <td class="text-center cell-clickable {{ $c14 > 0 ? 'font-weight-bold text-primary font-monospace' : 'text-muted' }}" 
                                        onclick="fetchTrans2Details('{{ $row['id'] }}', '1_4', {{ $se }})" 
                                        style="cursor: pointer; font-size: 0.86rem; border-color: var(--border-color); {{ $c14 > 0 ? 'background: rgba(77, 124, 254, 0.08);' : '' }}">
                                        {{ $c14 ?: '0' }}
                                    </td>
                                    <td class="text-center cell-clickable {{ $c514 > 0 ? 'font-weight-bold text-primary font-monospace' : 'text-muted' }}" 
                                        onclick="fetchTrans2Details('{{ $row['id'] }}', '5_14', {{ $se }})" 
                                        style="cursor: pointer; font-size: 0.86rem; border-color: var(--border-color); {{ $c514 > 0 ? 'background: rgba(77, 124, 254, 0.08);' : '' }}">
                                        {{ $c514 ?: '0' }}
                                    </td>
                                    <td class="text-center cell-clickable {{ $c15p > 0 ? 'font-weight-bold text-primary font-monospace' : 'text-muted' }}" 
                                        onclick="fetchTrans2Details('{{ $row['id'] }}', '15_plus', {{ $se }})" 
                                        style="cursor: pointer; font-size: 0.86rem; border-color: var(--border-color); {{ $c15p > 0 ? 'background: rgba(77, 124, 254, 0.08);' : '' }}">
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
