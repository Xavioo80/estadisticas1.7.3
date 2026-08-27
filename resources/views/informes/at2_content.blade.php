<!-- Barra de Filtros Integrada -->
<div class="filter-container no-print" style="padding: 0.5rem 0.85rem; background: var(--bg-surface); border-bottom: 1px solid var(--border-color); display: flex; align-items: center; justify-content: space-between; gap: 0.75rem; flex-wrap: nowrap; flex-shrink: 0;">
    <form id="filter-form" action="{{ route('informes.at2') }}" method="GET" style="display: flex; align-items: center; gap: 0.45rem; margin: 0; flex: 1 1 0%; min-width: 0; flex-wrap: nowrap;">
        <!-- Año y Mes -->
        <div style="display: flex; align-items: center; gap: 0.35rem; flex-shrink: 0;">
            <div style="width: 78px;">
                <select name="ano" class="filter-select w-full ajax-filter">
                    @foreach($anos as $a)
                        <option value="{{ $a }}" {{ $a == $ano ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <div style="width: 110px;">
                <select name="mes" class="filter-select w-full ajax-filter">
                    @foreach($meses as $m)
                        <option value="{{ $m }}" {{ $m == ($mesStr ?? '') ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div style="height: 20px; width: 1px; background: var(--border-color); flex-shrink: 0; margin: 0 2px;"></div>

        <!-- Selector de Médico -->
        <div style="display: flex; align-items: center; gap: 0.35rem; flex: 1 1 0%; min-width: 0;">
            <div style="width: 320px; max-width: 100%;">
                <select name="medico" class="filter-select w-full ajax-filter" style="font-weight: 700;">
                    @foreach($medicos as $m)
                        <option value="{{ $m }}" {{ $m == $medico ? 'selected' : '' }}>{{ $m }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <!-- Botones de Acción -->
        <div style="display: flex; align-items: center; gap: 0.35rem; flex-shrink: 0; margin-left: auto;">
            <button type="button" id="toggle-fullscreen" class="btn-action-fullscreen" title="Pantalla Completa">
                <i class="bi bi-arrows-fullscreen" id="fullScreenIcon"></i>
            </button>
            <button type="button" onclick="window.print()" class="btn-action-print" title="Imprimir">
                <i class="bi bi-printer"></i>
            </button>
        </div>
    </form>
</div>

<!-- Tabla AT2 Individual -->
<div style="flex: 1 1 0%; min-height: 0; display: flex; flex-direction: column; overflow: hidden; position: relative;">
    <div class="table-responsive" style="flex: 1 1 0%; min-height: 0; overflow: auto;">
        <table class="table table-bordered table-sm text-center mb-0 w-full" id="at2Table">
            <thead class="thead-premium sticky-top">
                <tr>
                    <th class="sticky-col-first align-middle" style="width: 320px; min-width: 300px; text-align: left; padding: 4px 8px !important;">
                        <span style="font-size: 0.75rem; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-primary);">
                            CONCEPTO
                        </span>
                    </th>
                    @for($d = 1; $d <= $daysInMonth; $d++)
                        @php $isWeekend = $dayMeta[$d]['isWeekend'] ?? false; @endphp
                        <th style="width: 32px; min-width: 30px; padding: 3px 1px !important;" class="{{ $isWeekend ? 'bg-weekend-header' : '' }}">
                            <span style="font-size: 0.75rem; font-weight: 700;">{{ $d }}</span>
                        </th>
                    @endfor
                    <th class="align-middle col-mes-total" style="width: 65px; min-width: 60px; padding: 3px 2px !important;">
                        <span style="font-size: 0.78rem; font-weight: 800;">TOTAL</span>
                    </th>
                </tr>
            </thead>
            <tbody>
                @foreach($finalData as $row)
                    @if($row['header'] ?? false)
                        <tr class="row-section-header">
                            <td colspan="{{ $daysInMonth + 2 }}" class="text-left pl-3" style="padding: 5px 10px !important; font-weight: 800; text-transform: uppercase; letter-spacing: 0.05em; background: var(--bg-subtle); color: var(--color-primary);">
                                <i class="bi bi-tag-fill mr-1"></i> {{ $row['label'] }}
                            </td>
                        </tr>
                    @else
                        @php 
                            $label = strtolower(trim($row['label']));
                            $isTotal = str_starts_with($label, 'total');
                            $isMenores5 = str_contains($label, 'menores de 5') || str_contains($label, 'menores 5');
                            $isEmbarazo = str_contains($label, 'embaraz') || str_contains($label, 'prenatal');
                            $isPuerperio = str_contains($label, 'puerperio') || str_contains($label, 'puérpera') || str_contains($label, 'puerpera');
                            $isNino = str_contains($label, 'niño') || str_contains($label, 'niña') || str_contains($label, 'nino') || str_contains($label, 'nina');

                            $rowClass = '';
                            if ($isTotal) $rowClass = 'row-total-highlight';
                            elseif ($isMenores5) $rowClass = 'row-menores5';
                            elseif ($isEmbarazo) $rowClass = 'row-embarazo';
                            elseif ($isPuerperio) $rowClass = 'row-puerperio';
                            elseif ($isNino) $rowClass = 'row-nino';
                        @endphp
                        <tr class="{{ $rowClass }}">
                            <td class="sticky-col-first text-left pl-3 {{ $rowClass ? $rowClass.'-sticky' : '' }}" style="padding: 3px 8px !important;">
                                {{ $row['label'] }}
                            </td>
                            @for($d = 1; $d <= $daysInMonth; $d++)
                                @php $isWeekend = $dayMeta[$d]['isWeekend'] ?? false; @endphp
                                <td class="{{ $isWeekend ? 'cell-weekend' : '' }}" style="padding: 1px !important;">
                                    @if($row['is_manual'] ?? false)
                                        <input type="number" 
                                               min="0" 
                                               class="manual-input text-center"
                                               data-day="{{ $d }}" 
                                               data-key="{{ $row['manual_key'] }}" 
                                               value="{{ $row['days'][$d] ?: '0' }}"
                                               style="width: 100%; border: none; background: transparent; font-weight: 700; color: var(--color-primary); font-size: 0.8rem;">
                                    @else
                                        @php $val = $row['days'][$d] ?? 0; @endphp
                                        @if($val > 0)
                                            <span class="cell-atencion-val">{{ $val }}</span>
                                        @else
                                            <span class="cell-atencion-empty">·</span>
                                        @endif
                                    @endif
                                </td>
                            @endfor
                            <td class="col-mes-total" style="font-weight: 800;">
                                {{ $row['total'] ?: '0' }}
                            </td>
                        </tr>
                    @endif
                @endforeach
            </tbody>
        </table>
    </div>
</div>