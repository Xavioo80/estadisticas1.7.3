<div class="filter-container flex items-center justify-between gap-2 p-3 mb-3 rounded-2xl border border-gray-300 dark:border-gray-800 bg-white dark:bg-slate-900 shadow-theme-xs no-print">
    <div class="flex items-center gap-2.5 shrink-0">
        <div class="w-8 h-8 rounded-xl bg-indigo-500/10 text-indigo-600 dark:bg-indigo-500/20 dark:text-indigo-400 flex items-center justify-center font-bold">
            <i class="fas fa-file-medical text-xs"></i>
        </div>
        <div>
            <h1 class="text-xs font-bold text-gray-900 dark:text-white leading-tight">Consulta y Registro SNVS</h1>
            <span class="text-[10px] text-gray-500 dark:text-gray-400 font-medium">Notificaciones Sanitarias</span>
        </div>
    </div>

    <form id="filter-form-svs" action="{{ route('informes.notificacion_svs') }}" method="GET" class="flex flex-1 items-center gap-2 mb-0 min-w-0">
        <div class="w-20 shrink-0">
            <select name="ano" class="ajax-filter-svs w-full bg-gray-50/50 dark:bg-gray-800/80 text-gray-900 dark:text-white border border-gray-300 dark:border-gray-700 text-xs rounded-xl focus:ring-2 focus:ring-brand-500 py-1.5 px-2 font-medium outline-none">
                @foreach($anos as $a)
                    <option value="{{ $a }}" {{ $a == $ano ? 'selected' : '' }}>{{ $a }}</option>
                @endforeach
            </select>
        </div>

        <div class="w-24 shrink-0">
            <select name="mes" class="ajax-filter-svs w-full bg-gray-50/50 dark:bg-gray-800/80 text-gray-900 dark:text-white border border-gray-300 dark:border-gray-700 text-xs rounded-xl focus:ring-2 focus:ring-brand-500 py-1.5 px-2 font-medium outline-none">
                @foreach($meses as $m)
                    <option value="{{ $m }}" {{ $m == $mes ? 'selected' : '' }}>{{ $m }}</option>
                @endforeach
            </select>
        </div>

        <div class="w-32 shrink-0">
            <select name="se" class="ajax-filter-svs w-full bg-gray-50/50 dark:bg-gray-800/80 text-gray-900 dark:text-white border border-gray-300 dark:border-gray-700 text-xs rounded-xl focus:ring-2 focus:ring-brand-500 py-1.5 px-2 font-medium outline-none">
                <option value="TODAS">SEMANA (TODAS)</option>
                @foreach($semanas as $s)
                    <option value="{{ $s }}" {{ $s == $se ? 'selected' : '' }}>SE {{ $s }}</option>
                @endforeach
            </select>
        </div>

        <div class="w-48 shrink-0">
            <select name="enfermedad" class="ajax-filter-svs w-full bg-gray-50/50 dark:bg-gray-800/80 text-gray-900 dark:text-white border border-gray-300 dark:border-gray-700 text-xs rounded-xl focus:ring-2 focus:ring-brand-500 py-1.5 px-2 font-medium outline-none">
                <option value="TODAS">ENFERMEDAD SNVS (TODAS)</option>
                @foreach($enfermedadesList as $enf)
                    <option value="{{ $enf }}" {{ $enf == $enfermedadFiltro ? 'selected' : '' }}>{{ $enf }}</option>
                @endforeach
            </select>
        </div>

        <div class="flex-1 min-w-[150px]">
            <div class="relative">
                <input type="text" name="search" value="{{ $search }}" placeholder="Buscar expediente, DNI, paciente..." 
                       class="ajax-filter-svs w-full bg-gray-50/50 dark:bg-gray-800/80 text-gray-900 dark:text-white border border-gray-300 dark:border-gray-700 text-xs rounded-xl focus:ring-2 focus:ring-brand-500 py-1.5 pl-8 pr-2.5 font-medium placeholder-gray-400 outline-none">
                <i class="fas fa-search absolute left-2.5 top-2 text-gray-400 text-xs"></i>
            </div>
        </div>
    </form>

    <div class="flex items-center gap-1.5 shrink-0">
        <button type="button" onclick="window.print()" class="btn-action-print" title="Imprimir"><i class="bi bi-printer"></i></button>
    </div>
</div>

<div class="flex-1 overflow-hidden relative">
    <div class="h-full overflow-x-auto overflow-y-auto bg-white dark:bg-slate-900 rounded-2xl shadow-theme-xs border border-gray-300 dark:border-gray-800">
        <table class="w-full text-left border-collapse text-xs whitespace-nowrap">
            <thead class="bg-gray-100 dark:bg-slate-800 border-b border-gray-300 dark:border-gray-700 text-gray-700 dark:text-gray-300 font-normal sticky top-0 z-10 text-xs">
                <tr class="whitespace-nowrap">
                    <th class="py-2 px-2.5 text-center border-r border-gray-300 dark:border-gray-700 font-normal whitespace-nowrap">N°</th>
                    <th class="py-2 px-2.5 text-center border-r border-gray-300 dark:border-gray-700 font-normal whitespace-nowrap">Fecha Consulta</th>
                    <th class="py-2 px-2.5 text-center border-r border-gray-300 dark:border-gray-700 font-normal whitespace-nowrap">SE</th>
                    <th class="py-2 px-2.5 text-center border-r border-gray-300 dark:border-gray-700 font-normal whitespace-nowrap">Inicio Síntomas</th>
                    <th class="py-2 px-2.5 text-center border-r border-gray-300 dark:border-gray-700 font-normal whitespace-nowrap">Expediente</th>
                    <th class="py-2 px-2.5 text-center border-r border-gray-300 dark:border-gray-700 font-normal whitespace-nowrap">DNI (0000-0000-00000)</th>
                    <th class="py-2 px-2.5 border-r border-gray-300 dark:border-gray-700 font-normal whitespace-nowrap">Paciente</th>
                    <th class="py-2 px-2.5 text-center border-r border-gray-300 dark:border-gray-700 font-normal whitespace-nowrap">Fecha Nacimiento</th>
                    <th class="py-2 px-2.5 text-center border-r border-gray-300 dark:border-gray-700 font-normal whitespace-nowrap">Edad</th>
                    <th class="py-2 px-2.5 text-center border-r border-gray-300 dark:border-gray-700 font-normal whitespace-nowrap">Sexo</th>
                    <th class="py-2 px-2.5 text-center border-r border-gray-300 dark:border-gray-700 font-normal whitespace-nowrap">Teléfono</th>
                    <th class="py-2 px-2.5 border-r border-gray-300 dark:border-gray-700 font-normal whitespace-nowrap">Colonia / Dirección</th>
                    <th class="py-2 px-2.5 border-r border-gray-300 dark:border-gray-700 font-normal whitespace-nowrap">Médico</th>
                    <th class="py-2 px-2.5 border-r border-gray-300 dark:border-gray-700 font-normal whitespace-nowrap">Diagnóstico Consignado</th>
                    <th class="py-2 px-2.5 border-r border-gray-300 dark:border-gray-700 font-normal whitespace-nowrap">Diagnóstico Correspondiente (SNVS)</th>
                    <th class="py-2 px-2.5 border-r border-gray-300 dark:border-gray-700 font-normal whitespace-nowrap">Observaciones</th>
                    <th class="py-2 px-2.5 text-center font-normal whitespace-nowrap">Checklist Notificado</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-200 dark:divide-gray-800 font-normal text-gray-900 dark:text-gray-200 whitespace-nowrap text-xs">
                @forelse($rows as $idx => $r)
                    <tr class="row-patient-svs hover:bg-gray-50 dark:hover:bg-gray-800/40 transition-colors whitespace-nowrap" data-informe-id="{{ $r->informe_id }}">
                        <td class="py-1 px-2 text-center font-normal text-gray-500 dark:text-gray-400 border-r border-gray-200 dark:border-gray-800 whitespace-nowrap">{{ $idx + 1 }}</td>
                        <td class="py-1 px-2 text-center border-r border-gray-200 dark:border-gray-800 font-normal text-gray-900 dark:text-gray-200 whitespace-nowrap">{{ $r->fecha_consulta }}</td>
                        <td class="py-1 px-2 text-center border-r border-gray-200 dark:border-gray-800 font-normal text-gray-900 dark:text-gray-200 whitespace-nowrap">{{ $r->se }}</td>
                        
                        <!-- CASILLA FECHA INICIO SÍNTOMAS EDITABLE -->
                        <td class="py-0.5 px-1.5 text-center border-r border-gray-200 dark:border-gray-800 whitespace-nowrap">
                            <input type="date" 
                                   class="input-fecha-sintomas-row bg-transparent border border-gray-300 dark:border-gray-700 rounded px-1.5 py-0.5 text-xs text-gray-900 dark:text-gray-200 font-normal outline-none focus:ring-1 focus:ring-brand-500" 
                                   value="{{ $r->fecha_inicio_sintomas }}" 
                                   data-informe-id="{{ $r->informe_id }}" 
                                   onchange="guardarFechaSintomasFila(this, '{{ $r->informe_id }}')">
                        </td>

                        <!-- COLUMNA EXPEDIENTE INDEPENDIENTE -->
                        <td class="cell-expediente py-1 px-2 text-center border-r border-gray-200 dark:border-gray-800 font-normal text-gray-900 dark:text-gray-200 whitespace-nowrap">
                            {{ $r->expediente }}
                        </td>
                        
                        <!-- CASILLA DNI EDITABLE ESPECÍFICA CON BOTÓN GUARDAR -->
                        <td class="py-0.5 px-1.5 text-center border-r border-gray-200 dark:border-gray-800 whitespace-nowrap">
                            <div class="inline-flex items-center justify-center gap-1">
                                <input type="text" 
                                       class="input-dni-row w-28 bg-transparent border border-gray-300 dark:border-gray-700 rounded px-1.5 py-0.5 text-xs font-mono text-gray-900 dark:text-gray-200 font-normal outline-none focus:ring-1 focus:ring-brand-500" 
                                       value="{{ $r->no_documento }}" 
                                       data-informe-id="{{ $r->informe_id }}" 
                                       placeholder="0000-0000-00000" 
                                       maxlength="15"
                                       oninput="formatearDniFila(this)" 
                                       onkeydown="if(event.key==='Enter'){ buscarPacienteEnFila(this, '{{ $r->informe_id }}'); }">
                                <button type="button" 
                                        class="btn-guardar-row p-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded text-[10px] shadow-sm transition-all flex items-center justify-center shrink-0 cursor-pointer border-0" 
                                        onclick="guardarRegistroFila(this, '{{ $r->informe_id }}')" 
                                        title="Guardar registro completo en MySQL (notificaciones_svs)">
                                    <i class="fas fa-save text-[10px]"></i>
                                </button>
                            </div>
                        </td>

                        <!-- CELDA NOMBRE PACIENTE -->
                        <td class="cell-paciente py-1 px-2 font-normal text-gray-900 dark:text-gray-200 border-r border-gray-200 dark:border-gray-800 whitespace-nowrap">
                            {{ $r->nombre_paciente }}
                        </td>

                        <!-- CELDA FECHA NACIMIENTO -->
                        <td class="cell-fecha-nac py-1 px-2 text-center border-r border-gray-200 dark:border-gray-800 font-normal text-gray-900 dark:text-gray-200 whitespace-nowrap">
                            {{ $r->fecha_nacimiento ?: '-' }}
                        </td>

                        <!-- CELDA EDAD (DESDE DNI SNVS) -->
                        <td class="cell-edad py-1 px-2 text-center border-r border-gray-200 dark:border-gray-800 font-normal text-gray-900 dark:text-gray-200 whitespace-nowrap">
                            {{ ($r->edad !== null && $r->edad !== '') ? $r->edad : '-' }}
                        </td>

                        <!-- CELDA SEXO SEPARADA -->
                        <td class="cell-sexo py-1 px-2 text-center border-r border-gray-200 dark:border-gray-800 font-normal text-gray-900 dark:text-gray-200 whitespace-nowrap">
                            {{ $r->sexo ?: '-' }}
                        </td>

                        <!-- CELDA TELÉFONO -->
                        <td class="cell-telefono py-1 px-2 text-center font-normal text-gray-900 dark:text-gray-200 border-r border-gray-200 dark:border-gray-800 whitespace-nowrap">
                            <div class="inline-edit-tel-wrap group relative inline-flex items-center justify-center gap-1 cursor-pointer" 
                                 data-informe-id="{{ $r->informe_id }}" 
                                 data-value="{{ $r->telefono }}">
                                <span class="inline-edit-tel-disp font-normal {{ $r->telefono && $r->telefono !== '-' ? 'text-gray-900 dark:text-gray-200' : 'text-gray-400 italic' }}">{{ $r->telefono }}</span>
                                <i class="fas fa-pencil-alt text-[8px] text-gray-400 opacity-0 group-hover:opacity-100 transition-opacity"></i>
                                <input type="text" 
                                       class="inline-edit-tel-input hidden w-28 bg-transparent text-gray-900 dark:text-gray-200 text-xs px-1.5 py-0.5 border border-gray-300 dark:border-gray-700 rounded text-center focus:ring-1 focus:ring-brand-500 focus:outline-none" 
                                       value="{{ $r->telefono !== '-' ? $r->telefono : '' }}" 
                                       placeholder="Teléfono...">
                            </div>
                        </td>

                        <!-- CELDA COLONIA / DIRECCIÓN -->
                        <td class="cell-direccion py-1 px-2 text-gray-900 dark:text-gray-200 border-r border-gray-200 dark:border-gray-800 font-normal whitespace-nowrap">{{ $r->direccion }}</td>

                        <td class="py-1 px-2 font-normal text-gray-900 dark:text-gray-200 border-r border-gray-200 dark:border-gray-800 whitespace-nowrap">{{ $r->medico }}</td>
                        
                        <!-- DIAGNÓSTICO CONSIGNADO -->
                        <td class="cell-diagnostico-consignado py-1 px-2 border-r border-gray-200 dark:border-gray-800 text-gray-900 dark:text-gray-200 font-normal whitespace-nowrap">
                            {{ $r->diagnostico_consignado }}
                        </td>

                        <!-- DIAGNÓSTICO CORRESPONDIENTE SNVS -->
                        <td class="py-0.5 px-1.5 border-r border-gray-200 dark:border-gray-800 whitespace-nowrap">
                            <select data-informe-id="{{ $r->informe_id }}" 
                                    class="svs-disease-select w-full bg-transparent text-gray-900 dark:text-gray-200 font-normal border border-gray-300 dark:border-gray-700 rounded text-xs py-0.5 px-1.5 outline-none focus:ring-1 focus:ring-brand-500">
                                @foreach($enfermedadesList as $enf)
                                    <option value="{{ $enf }}" class="bg-white dark:bg-gray-800 text-gray-900 dark:text-white" {{ $enf == $r->enfermedad_svs ? 'selected' : '' }}>
                                        {{ $enf }}
                                    </option>
                                @endforeach
                            </select>
                        </td>

                        <td class="py-1 px-2 text-gray-700 dark:text-gray-300 border-r border-gray-200 dark:border-gray-800 text-xs font-normal whitespace-nowrap">
                            {{ $r->observaciones ?: '-' }}
                        </td>
                        
                        <!-- CASILLA CHECKLIST NOTIFICADO -->
                        <td class="py-1 px-2 text-center whitespace-nowrap">
                            <label class="inline-flex items-center gap-1 cursor-pointer whitespace-nowrap">
                                <input type="checkbox" 
                                       class="chk-notificado-svs rounded border-gray-300 dark:border-gray-700 text-brand-500 focus:ring-brand-500 w-3.5 h-3.5 cursor-pointer" 
                                       data-informe-id="{{ $r->informe_id }}" 
                                       {{ $r->estado_notificacion === 'Notificado' ? 'checked' : '' }}>
                                <span class="lbl-notificado-svs text-xs font-normal text-gray-700 dark:text-gray-300 whitespace-nowrap">
                                    {{ $r->estado_notificacion === 'Notificado' ? 'Notificado' : 'Pendiente' }}
                                </span>
                            </label>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="17" class="py-6 text-center text-gray-400 font-normal whitespace-nowrap">
                            No se encontraron casos de notificación obligatoria
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
