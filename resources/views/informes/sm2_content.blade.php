<div id="sm2-full-wrapper" class="d-flex flex-column h-100" style="background: transparent;">
    <!-- Barra de Filtros -->
    <div class="informe-filters-card mb-3 no-print" style="background: var(--bg-surface); border: 1px solid var(--border-color); border-radius: var(--radius-md, 10px); padding: 0.75rem 1rem; box-shadow: var(--shadow-sm);">
        <div class="d-flex flex-wrap align-items-center justify-content-between gap-3">
            <div class="d-flex flex-wrap align-items-center gap-2">
                <div class="filter-group">
                    <select name="ano" class="form-control form-control-sm ajax-filter font-weight-bold" style="min-width: 100px; height: 36px; background: var(--input-bg); border-color: var(--border-color); color: var(--text-primary); border-radius: var(--radius-sm, 6px);">
                        @foreach($anos as $a)
                            <option value="{{ $a }}" {{ (int)$a == (int)$ano ? 'selected' : '' }}>{{ $a }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <select name="mes" class="form-control form-control-sm ajax-filter font-weight-bold" style="min-width: 140px; height: 36px; background: var(--input-bg); border-color: var(--border-color); color: var(--text-primary); border-radius: var(--radius-sm, 6px);">
                        @foreach($meses as $m)
                            <option value="{{ $m }}" {{ strtoupper(trim($m)) == strtoupper(trim($mes)) ? 'selected' : '' }}>
                                {{ strtoupper($m) }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="filter-group">
                    <select name="jornada" class="form-control form-control-sm ajax-filter font-weight-bold" style="min-width: 140px; height: 36px; background: var(--input-bg); border-color: var(--border-color); color: var(--text-primary); border-radius: var(--radius-sm, 6px);">
                        <option value="TODAS">TODAS LAS JORNADAS</option>
                        @foreach($jornadas as $j)
                            <option value="{{ $j }}" {{ $j == $jornada ? 'selected' : '' }}>{{ $j }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="d-none d-lg-block ml-2 pl-2" style="border-left: 1px solid var(--border-color);">
                    <span class="badge badge-subtle font-weight-bold" style="font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.05em; color: var(--text-secondary);">
                        SM2: ACTIVIDADES DE SALUD MENTAL
                    </span>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <button type="button" onclick="toggleFullScreen()" class="btn btn-subtle btn-sm" title="Pantalla Completa" style="font-weight: 600; height: 36px; padding: 0 0.75rem;">
                    <i class="bi bi-arrows-fullscreen mr-1" id="fullScreenIcon"></i> <span class="d-none d-md-inline">Pantalla Completa</span>
                </button>
                <button type="button" onclick="window.print()" class="btn btn-subtle btn-sm" title="Imprimir informe" style="font-weight: 600; height: 36px; padding: 0 0.75rem;">
                    <i class="bi bi-printer mr-1"></i> <span class="d-none d-md-inline">Imprimir</span>
                </button>
            </div>
        </div>
    </div>

    <!-- Tabla SM2 -->
    <div id="sm2-table-wrapper" style="flex: 1 1 0%; min-height: 0; overflow: auto; border: 1px solid var(--border-color); background: var(--bg-surface); border-radius: var(--radius-md, 10px); box-shadow: var(--shadow-sm); position: relative;">
        <table id="sm2Table" class="w-100" style="border-collapse: separate; border-spacing: 0; min-width: 1200px;">
            <thead>
                <tr>
                    <th rowspan="3" class="sticky-col sc1" style="width: 45px; min-width: 45px;">Nº</th>
                    <th rowspan="3" class="sticky-col sc2" style="width: 320px; min-width: 320px; text-align: left; padding-left: 10px;">ACTIVIDADES</th>
                    <th colspan="2">PSICÓLOGO</th>
                    <th colspan="2">MÉDICO GENERAL</th>
                    <th colspan="2">PSIQUIATRA</th>
                    <th colspan="2">TRABAJADOR SOCIAL</th>
                    <th colspan="2">ABOGADO</th>
                    <th colspan="2">AUXILIAR ENFERMERÍA</th>
                    <th colspan="2">LIC. ENFERMERÍA</th>
                    <th colspan="2">OTROS</th>
                    <th colspan="2">TOTAL</th>
                </tr>
                <tr>
                    @for($i = 0; $i < 9; $i++)
                        <th style="min-width: 38px;">N</th>
                        <th style="min-width: 38px;">SUBS.</th>
                    @endfor
                </tr>
            </thead>
            <tbody>
                @foreach($reportData as $row)
                    <tr>
                        <td class="sticky-col sc1 font-weight-bold">{{ $row['n'] }}</td>
                        <td class="sticky-col sc2 text-left" style="padding-left: 10px;">{{ $row['label'] }}</td>
                        
                        @foreach(['psicologo', 'medico_general', 'psiquiatra', 'trabajador_social', 'abogado', 'auxiliar_enfermeria', 'licenciada_enfermeria', 'otros'] as $pk)
                            <td class="{{ $row['values'][$pk]['n'] > 0 ? 'val-positive' : 'val-zero' }}">{{ $row['values'][$pk]['n'] ?: '-' }}</td>
                            <td class="{{ $row['values'][$pk]['s'] > 0 ? 'val-positive' : 'val-zero' }}">{{ $row['values'][$pk]['s'] ?: '-' }}</td>
                        @endforeach

                        <td class="val-total">{{ $row['row_total']['n'] ?: '-' }}</td>
                        <td class="val-total">{{ $row['row_total']['s'] ?: '-' }}</td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <td colspan="2" class="sticky-col sc1 text-right font-weight-bold" style="padding-right: 12px; letter-spacing: 0.05em;">TOTALES:</td>
                    @foreach(['psicologo', 'medico_general', 'psiquiatra', 'trabajador_social', 'abogado', 'auxiliar_enfermeria', 'licenciada_enfermeria', 'otros'] as $pk)
                        <td>{{ $totals[$pk]['n'] ?: '0' }}</td>
                        <td>{{ $totals[$pk]['s'] ?: '0' }}</td>
                    @endforeach
                    <td>{{ $totalGeneral['n'] ?: '0' }}</td>
                    <td>{{ $totalGeneral['s'] ?: '0' }}</td>
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
        font-size: 0.68rem;
        font-weight: 700;
        padding: 5px 6px;
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
        padding: 4px 6px;
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

    .val-positive { color: var(--color-primary) !important; font-weight: 700 !important; }
    .val-zero { color: var(--text-muted); opacity: 0.35; }
    .val-total { background: var(--bg-subtle) !important; font-weight: 800 !important; color: var(--text-primary) !important; }

    #sm2Table tbody tr:hover td { background-color: var(--color-primary-light, rgba(99, 102, 241, 0.12)) !important; }
    #sm2Table tbody td:hover { background-color: var(--color-primary) !important; color: #ffffff !important; font-weight: 800 !important; }

    #sm2-table-wrapper::-webkit-scrollbar { width: 8px; height: 8px; }
    #sm2-table-wrapper::-webkit-scrollbar-track { background: var(--bg-subtle); }
    #sm2-table-wrapper::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 4px; }
    #sm2-table-wrapper::-webkit-scrollbar-thumb:hover { background: var(--text-muted); }

    @media print {
        #sm2-table-wrapper { overflow: visible !important; height: auto !important; border: none !important; }
        #sm2Table { width: 100% !important; min-width: auto !important; }
        .no-print, .informe-filters-card { display: none !important; }
        #sm2Table thead tr th, #sm2Table tfoot tr td { position: relative !important; top: auto !important; bottom: auto !important; }
        .sticky-col { position: relative !important; left: 0 !important; }
        #sm2Table th, #sm2Table td { font-size: 7pt !important; padding: 1px 2px !important; color: #000 !important; border-color: #000 !important; }
    }
</style>
