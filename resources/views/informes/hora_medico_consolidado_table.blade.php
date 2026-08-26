{{-- Encabezado Principal Oficial (Solo Impresión) --}}
<div class="report-header mb-4 hidden print:block">
    <!-- Fila 1: Títulos de Secretaría de Salud -->
    <div class="text-center font-bold uppercase text-slate-900 dark:text-white space-y-1 mb-4">
        <h4 class="font-extrabold text-base tracking-wide m-0">SECRETARIA DE SALUD</h4>
        <h5 class="font-bold text-sm tracking-wide m-0">REGIÓN SANITARIA METROPOLITANA DEL DISTRITO CENTRAL</h5>
        <h6 class="font-semibold text-xs tracking-wider m-0">UNIDAD DE PLANEAMIENTO / ÁREA DE GESTIÓN DE LA INFORMACIÓN</h6>
        <div class="inline-block border-b-2 border-slate-900 dark:border-white pb-0.5 px-4 mt-1">
            <h5 class="font-black text-sm tracking-widest m-0">RENDIMIENTO MEDICO</h5>
        </div>
    </div>

    <!-- Fila 2: Datos de Establecimiento, Jornada, Mes, Año, Firma -->
    <div class="flex items-center justify-between text-xs font-bold uppercase text-slate-800 dark:text-slate-200 border-b border-gray-300 dark:border-gray-700 pb-2 mb-3">
        <div>
            <span>ESTABLECIMIENTO DE SALUD:</span>
            <span class="border-b border-black dark:border-white px-2 font-extrabold text-blue-700 dark:text-blue-400">{{ $settings['nombre_establecimiento'] ?? 'CENTRO DE SALUD' }}</span>
        </div>
        <div>
            <span>JORNADA:</span>
            <span class="border-b border-black dark:border-white px-2 font-extrabold text-blue-700 dark:text-blue-400">{{ $jornada }}</span>
        </div>
        <div>
            <span>MES:</span>
            <span class="border-b border-black dark:border-white px-2 font-extrabold text-blue-700 dark:text-blue-400">{{ $mes }}</span>
        </div>
        <div>
            <span>AÑO:</span>
            <span class="border-b border-black dark:border-white px-2 font-extrabold text-blue-700 dark:text-blue-400">{{ $ano }}</span>
        </div>
        <div class="text-right">
            <span class="text-[10px] text-gray-500 uppercase">FIRMA Y SELLO _______________________</span>
        </div>
    </div>
</div>

{{-- Tabla Oficial de Observaciones --}}
<div class="table-responsive w-full">
    <table class="table table-bordered text-xs text-left mb-0 w-full border-collapse border border-slate-300 dark:border-slate-700" style="width: 100%;">
        <thead>
            <tr class="bg-slate-800 dark:bg-slate-800 text-white font-bold uppercase text-center">
                <th class="border border-slate-700 dark:border-slate-700 p-2 text-center" style="width: 45px;">N°</th>
                <th class="border border-slate-700 dark:border-slate-700 p-2 text-left" style="width: 340px;">NOMBRE COMPLETO DEL MEDICO</th>
                <th class="border border-slate-700 dark:border-slate-700 p-2 text-left">OBSERVACIONES</th>
            </tr>
        </thead>
        <tbody>
            @php
                $rowsCount = count($data);
                $minRows = max(25, $rowsCount);
            @endphp
            @foreach($data as $index => $row)
                @php
                    $medico = $row['medico'];
                    $hsc = $row['hsc'];
                    $obsEstatica = trim($medico->observaciones ?? '');
                    $obsDinamica = $hsc ? trim($hsc->observaciones ?? '') : '';
                    // Texto combinado que se muestra en el input
                    if ($obsEstatica && $obsDinamica) {
                        $obsTexto = $obsEstatica . ' | ' . $obsDinamica;
                    } elseif ($obsEstatica) {
                        $obsTexto = $obsEstatica;
                    } else {
                        $obsTexto = $obsDinamica;
                    }
                @endphp
                <tr class="hover:bg-blue-50/50 dark:hover:bg-slate-800/60 transition-all border-b border-slate-200 dark:border-slate-800">
                    <td class="border border-slate-300 dark:border-slate-700 p-2 text-center font-bold text-slate-700 dark:text-slate-300">{{ $index + 1 }}</td>
                    <td class="border border-slate-300 dark:border-slate-700 p-2 text-left font-bold uppercase text-slate-900 dark:text-white">
                        {{ $medico->NOM_MED }}
                    </td>
                    <td class="border border-slate-300 dark:border-slate-700 p-1.5 text-left">
                        {{-- Input de texto plano: muestra el texto combinado; al guardar se salva la parte dinámica --}}
                        <input type="text"
                               class="w-full bg-transparent border-0 focus:ring-1 focus:ring-blue-400 text-xs font-medium text-slate-900 dark:text-slate-100 placeholder-slate-400 no-print"
                               value="{{ $obsTexto }}"
                               placeholder="Escriba observaciones para este médico..."
                               data-static="{{ $obsEstatica }}"
                               onchange="guardarObservacionConsolidado({{ $medico->id }}, this.value, this.dataset.static)">
                        <span class="hidden print:inline font-medium text-slate-800">{{ $obsTexto }}</span>
                    </td>
                </tr>
            @endforeach

            @for($i = $rowsCount + 1; $i <= $minRows; $i++)
                <tr class="border-b border-slate-200 dark:border-slate-800">
                    <td class="border border-slate-300 dark:border-slate-700 p-2 text-center font-bold text-slate-400 dark:text-slate-600">{{ $i }}</td>
                    <td class="border border-slate-300 dark:border-slate-700 p-2 text-left">&nbsp;</td>
                    <td class="border border-slate-300 dark:border-slate-700 p-2 text-left">&nbsp;</td>
                </tr>
            @endfor
        </tbody>
    </table>
</div>

{{-- Pie de Página Oficial (Notas Explicativas del Formato) --}}
<div class="mt-4 pt-2 text-[10px] leading-relaxed font-bold text-slate-800 dark:text-slate-300 border-t border-slate-300 dark:border-slate-700 grid grid-cols-1 md:grid-cols-2 gap-x-6 gap-y-1">
    <div>*** ESTE INFORME DEBE COINCIDIR CON EL TOTAL DE ATENCIONES DEL AT2R.</div>
    <div>*** COLOCAR EL PERSONAL QUE ESTE DE VACACIONES O INCAPACITADO (DE LO CONTRARIO SE REPORTARA COMO FALTANTE).</div>
    <div>*** EL ORDEN DE LOS MEDICOS DEBE SER IGUAL AL DE LOS MESES ANTERIORES.</div>
    <div>*** COLOCAR FECHA DE INICIO Y DE FINAL DE CADA MEDICO EN SERVICIO SOCIAL.</div>
    <div>*** EN LA PRIMERA CASILLA COLOCAR SIEMPRE EL NOMBRE COMPLETO DEL DIRECTOR DEL ESTABLECIMIENTO DE SALUD.</div>
    <div>*** LLENAR UNA HOJA POR JORNADA (MATUTINA, VESPERTINA, FIN DE SEMANA Y SERVICIO SOCIAL).</div>
</div>
