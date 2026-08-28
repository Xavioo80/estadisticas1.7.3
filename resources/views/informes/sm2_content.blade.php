<div id="sm2-full-wrapper" class="d-flex flex-column h-100" style="background: transparent;">
    <!-- Barra de Filtros en Una Sola Fila Horizontal Estricta -->
    <div class="filter-container no-print mb-2"
        style="background: var(--bg-surface) !important; border: 1px solid var(--border-color) !important; border-radius: var(--radius-md, 10px) !important; padding: 0.5rem 0.85rem !important; display: flex !important; flex-direction: row !important; align-items: center !important; justify-content: space-between !important; flex-wrap: nowrap !important; gap: 8px !important; box-shadow: var(--shadow-sm) !important; width: 100% !important; overflow-x: auto !important;">
        <form id="filter-form" action="{{ route('informes.sm2') }}" method="GET"
            style="display: flex !important; flex-direction: row !important; align-items: center !important; justify-content: space-between !important; flex-wrap: nowrap !important; gap: 8px !important; margin: 0 !important; width: 100% !important; min-width: 0 !important;">
            
            <div style="display: flex !important; flex-direction: row !important; align-items: center !important; flex-wrap: nowrap !important; gap: 8px !important; flex-shrink: 0 !important;">
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
                <div style="height: 22px !important; width: 1px !important; background: var(--border-color) !important; flex-shrink: 0 !important; margin: 0 4px !important;"></div>

                <span class="badge badge-subtle font-weight-bold" style="font-size: 0.72rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary); white-space: nowrap;">
                    SM2: ACTIVIDADES DE SALUD MENTAL
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
            </div>
        </form>
    </div>

    <!-- Tabla SM2 -->
    <div id="sm2-table-wrapper" style="flex: 1 1 0%; min-height: 0; overflow: auto; border: 1px solid var(--border-color); background: var(--bg-surface); border-radius: var(--radius-md, 10px); box-shadow: var(--shadow-sm); position: relative;">
        <table id="sm2Table" class="w-100" style="border-collapse: separate; border-spacing: 0; min-width: 1200px;">
            <thead>
                <tr>
                    <th rowspan="2" class="sticky-col sc1" style="width: 45px; min-width: 45px;">Nº</th>
                    <th rowspan="2" class="sticky-col sc2" style="width: 320px; min-width: 320px; text-align: left; padding-left: 10px;">ACTIVIDADES</th>
                    <th colspan="2">PSICÓLOGO</th>
                    <th colspan="2">MÉDICO GENERAL</th>
                    <th colspan="2">PSIQUIATRA</th>
                    <th colspan="2">TRABAJADOR SOCIAL</th>
                    <th colspan="2">ABOGADO</th>
                    <th colspan="2">AUXILIAR ENFERMERÍA</th>
                    <th colspan="2">LIC. ENFERMERÍA</th>
                    <th colspan="2">OTROS</th>
                    <th colspan="2" style="background: rgba(77, 124, 254, 0.15) !important; color: var(--color-primary) !important;">TOTAL</th>
                </tr>
                <tr>
                    @for($i = 0; $i < 8; $i++)
                        <th style="min-width: 38px;">N</th>
                        <th style="min-width: 38px;">SUBS.</th>
                    @endfor
                    <th style="min-width: 42px; background: rgba(77, 124, 254, 0.15) !important; color: var(--color-primary) !important;">N</th>
                    <th style="min-width: 42px; background: rgba(77, 124, 254, 0.15) !important; color: var(--color-primary) !important;">SUBS.</th>
                </tr>
            </thead>
            <tbody>
                @foreach($reportData as $row)
                    <tr>
                        <td class="sticky-col sc1 font-weight-bold" style="font-size: 0.76rem;">{{ $row['n'] }}</td>
                        <td class="sticky-col sc2 text-left font-weight-bold" style="padding-left: 10px; font-size: 0.78rem; color: var(--text-primary);">{{ $row['label'] }}</td>
                        
                        @foreach(['psicologo', 'medico_general', 'psiquiatra', 'trabajador_social', 'abogado', 'auxiliar_enfermeria', 'licenciada_enfermeria', 'otros'] as $pk)
                            <td class="{{ $row['values'][$pk]['n'] > 0 ? 'val-positive' : 'val-zero' }}">{{ $row['values'][$pk]['n'] ?: '·' }}</td>
                            <td class="{{ $row['values'][$pk]['s'] > 0 ? 'val-positive' : 'val-zero' }}">{{ $row['values'][$pk]['s'] ?: '·' }}</td>
                        @endforeach

                        <td class="val-total">{{ $row['row_total']['n'] ?: '0' }}</td>
                        <td class="val-total">{{ $row['row_total']['s'] ?: '0' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" class="sticky-col sc1 text-right font-weight-bold" style="padding-right: 12px; letter-spacing: 0.05em; font-size: 0.82rem;">TOTALES:</td>
                    @foreach(['psicologo', 'medico_general', 'psiquiatra', 'trabajador_social', 'abogado', 'auxiliar_enfermeria', 'licenciada_enfermeria', 'otros'] as $pk)
                        <td class="font-weight-bold" style="font-size: 0.82rem; color: {{ $totals[$pk]['n'] > 0 ? 'var(--color-primary)' : 'var(--text-muted)' }};">{{ $totals[$pk]['n'] ?: '0' }}</td>
                        <td class="font-weight-bold" style="font-size: 0.82rem; color: {{ $totals[$pk]['s'] > 0 ? 'var(--color-primary)' : 'var(--text-muted)' }};">{{ $totals[$pk]['s'] ?: '0' }}</td>
                    @endforeach
                    <td class="font-weight-bold" style="background: var(--color-primary) !important; color: #fff !important; font-size: 0.86rem;">{{ $totalGeneral['n'] ?: '0' }}</td>
                    <td class="font-weight-bold" style="background: var(--color-primary) !important; color: #fff !important; font-size: 0.86rem;">{{ $totalGeneral['s'] ?: '0' }}</td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<style>
    #sm2Table th {
        background: var(--bg-subtle);
        color: var(--text-primary);
        border-right: 1px solid var(--border-color);
        border-bottom: 1px solid var(--border-color);
        font-size: 0.7rem;
        font-weight: 700;
        padding: 6px 6px;
        text-transform: uppercase;
        position: sticky;
        top: 0;
        z-index: 20;
        text-align: center;
    }
    
    #sm2Table td {
        border-right: 1px solid var(--border-color);
        border-bottom: 1px solid var(--border-color);
        font-size: 0.78rem;
        text-align: center;
        padding: 5px 6px;
        background: var(--bg-surface);
        color: var(--text-primary);
    }

    #sm2Table thead tr:first-child th {
        border-top: 1px solid var(--border-color);
    }
    #sm2Table .sc1 {
        border-left: 1px solid var(--border-color);
    }

    .sticky-col {
        position: sticky;
        background: var(--bg-surface) !important;
        z-index: 10;
    }
    .sticky-col.sc1 {
        left: 0;
        width: 45px;
        min-width: 45px;
        background: var(--bg-subtle) !important;
    }
    .sticky-col.sc2 {
        left: 45px;
        width: 320px;
        min-width: 320px;
    }
    
    #sm2Table thead tr:nth-child(1) th { z-index: 30; top: 0; height: 32px; }
    #sm2Table thead tr:nth-child(2) th { z-index: 28; top: 32px; height: 26px; }
    #sm2Table thead tr th.sc1,
    #sm2Table thead tr th.sc2 { z-index: 40 !important; top: 0; background: var(--bg-subtle) !important; }

    #sm2Table tfoot tr td {
        position: sticky;
        bottom: 0;
        z-index: 25;
        background: var(--bg-subtle) !important;
        color: var(--text-primary);
        font-weight: 800;
        border-top: 2px solid var(--border-color);
    }
    #sm2Table tfoot tr td.sc1,
    #sm2Table tfoot tr td.sc2 {
        z-index: 35 !important;
    }

    .val-positive { color: var(--color-primary) !important; font-weight: 700 !important; background: rgba(77, 124, 254, 0.05) !important; }
    .val-zero { color: var(--text-muted); opacity: 0.4; }
    .val-total { background: rgba(77, 124, 254, 0.08) !important; font-weight: 800 !important; color: var(--color-primary) !important; }

    #sm2Table tbody tr:hover td { background-color: var(--color-primary-light, rgba(77, 124, 254, 0.1)) !important; }
    #sm2Table tbody td:hover { background-color: var(--color-primary) !important; color: #ffffff !important; font-weight: 800 !important; }

    #sm2-table-wrapper::-webkit-scrollbar { width: 8px; height: 8px; }
    #sm2-table-wrapper::-webkit-scrollbar-track { background: var(--bg-subtle); }
    #sm2-table-wrapper::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 4px; }
    #sm2-table-wrapper::-webkit-scrollbar-thumb:hover { background: var(--text-muted); }

    @media print {
        #sm2-table-wrapper { overflow: visible !important; height: auto !important; border: none !important; }
        #sm2Table { width: 100% !important; min-width: auto !important; }
        .no-print, .informe-filters-card, .filter-container { display: none !important; }
        #sm2Table thead tr th, #sm2Table tfoot tr td { position: relative !important; top: auto !important; bottom: auto !important; }
        .sticky-col { position: relative !important; left: 0 !important; }
        #sm2Table th, #sm2Table td { font-size: 7pt !important; padding: 1px 2px !important; color: #000 !important; border-color: #000 !important; }
    }
</style>
