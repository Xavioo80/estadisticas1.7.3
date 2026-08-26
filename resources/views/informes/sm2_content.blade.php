<div id="sm2-full-wrapper" class="bg-white dark:bg-gray-900 p-4 overflow-auto">
    <div class="flex items-center gap-2 mb-3 bg-slate-50 p-2 rounded-lg border border-slate-200 controls-container">
        <div class="flex items-center gap-2 mr-auto overflow-hidden">
            <div class="w-28 flex-shrink-0">
                <select name="ano" class="filter-select w-full ajax-filter text-sm h-9 px-3">
                    @foreach($anos as $a)
                        <option value="{{ $a }}" {{ (int)$a == (int)$ano ? 'selected' : '' }}>{{ $a }}</option>
                    @endforeach
                </select>
            </div>
            <div class="w-32 flex-shrink-0">
                <select name="mes" class="filter-select w-full ajax-filter text-sm h-9 px-3">
                    @foreach($meses as $m)
                        <option value="{{ $m }}" {{ strtoupper(trim($m)) == strtoupper(trim($mes)) ? 'selected' : '' }}>
                            {{ strtoupper($m) }}
                        </option>
                    @endforeach
                </select>
            </div>
            <div class="w-40 flex-shrink-0">
                <select name="jornada" class="filter-select w-full ajax-filter text-sm h-9 px-3">
                    <option value="TODAS">JORNADA</option>
                    @foreach($jornadas as $j)
                        <option value="{{ $j }}" {{ $j == $jornada ? 'selected' : '' }}>{{ $j }}</option>
                    @endforeach
                </select>
            </div>
            <div class="h-6 w-[1px] bg-slate-300 mx-1"></div>
            <span class="text-xs font-bold text-slate-500 uppercase tracking-tight truncate">
                SM2: ACTIVIDADES DE SALUD MENTAL
            </span>
        </div>

        <div class="flex items-center gap-1.5 ml-auto">
            <button type="button" onclick="toggleFullScreen()" class="btn-action-fullscreen" title="Pantalla Completa"><i class="bi bi-arrows-fullscreen" id="fullScreenIcon"></i></button>
            <button type="button" onclick="window.print()" class="btn-action-print" title="Imprimir"><i class="bi bi-printer"></i></button>
        </div>
    </div>

    <div id="sm2-table-wrapper" class="table-container relative overflow-auto border border-black bg-white rounded-sm h-[calc(100vh-180px)]">
    <table id="sm2Table" class="w-full border-collapse">
        <thead>
            <tr>
                <th rowspan="3" class="w-12">Nº</th>
                <th rowspan="3" class="w-72">ACTIVIDADES</th>
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
                <th class="w-10">N</th>
                <th class="w-10">SUBS.</th>
                @endfor
            </tr>
        </thead>
        <tbody>
            @foreach($reportData as $row)
            <tr>
                <td class="sticky-col sc1">{{ $row['n'] }}</td>
                <td class="sticky-col sc2 text-left px-3">{{ $row['label'] }}</td>
                
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
                <td colspan="2" class="text-right px-3 uppercase font-black">TOTALES</td>
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
        background: #1e293b;
        color: white;
        border: 1px solid #000;
        font-size: 0.65rem;
        padding: 4px;
        text-transform: uppercase;
        position: sticky;
        top: 0;
        z-index: 20;
    }
    
    #sm2Table td {
        border: 1px solid #000;
        font-size: 0.75rem;
        text-align: center;
        padding: 4px;
        background: white;
    }

    .sticky-col {
        position: sticky;
        left: 0;
        background: #f8fafc;
        z-index: 10;
        border: 1px solid #000 !important;
    }
    
    #sm2Table thead tr:nth-child(1) th { z-index: 25; top: 0; }
    #sm2Table thead tr:nth-child(2) th { z-index: 24; top: 22px; }

    #sm2-full-wrapper:fullscreen {
        padding: 20px;
        background: white;
        width: 100vw;
        height: 100vh;
    }

    #sm2-full-wrapper:fullscreen #sm2-table-wrapper {
        height: calc(100vh - 100px);
    }

    .sc1 { width: 30px; left: 0; }
    .sc2 { width: 300px; left: 30px; }

    .val-positive { color: #1d4ed8 !important; font-weight: 700 !important; background: #eff6ff !important; }
    .val-zero { color: #cbd5e1; }
    .val-total { background: #f1f5f9 !important; font-weight: 800 !important; color: #000 !important; }

    #sm2Table tbody tr:hover td { background-color: #dbeafe !important; }
    #sm2Table tbody td:hover { background-color: #2563eb !important; color: #ffffff !important; font-weight: 900 !important; }

    .filter-select {
        appearance: none !important;
        background-color: #fff !important;
        background-image: url("data:image/svg+xml,%3csvg xmlns='http://www.w3.org/2000/svg' fill='none' viewBox='0 0 20 20'%3e%3cpath stroke='%2364748b' stroke-linecap='round' stroke-linejoin='round' stroke-width='1.5' d='M6 8l4 4 4-4'/%3e%3c/svg%3e") !important;
        background-position: right 0.5rem center !important;
        background-repeat: no-repeat !important;
        background-size: 1.5em 1.5em !important;
        border: 1px solid #cbd5e1 !important;
        border-radius: 6px !important;
        color: #1e293b !important;
        padding: 4px 2.5rem 4px 12px !important;
        font-weight: 500 !important;
    }
</style>
