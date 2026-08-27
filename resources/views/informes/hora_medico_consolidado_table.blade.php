{{-- Encabezado Principal Oficial (Solo Impresión) --}}
<div class="report-header mb-4 hidden print:block">
    <!-- Fila 1: Títulos de Secretaría de Salud -->
    <div class="text-center font-bold uppercase text-slate-900 dark:text-white space-y-1 mb-4">
        <h4 class="font-extrabold text-base tracking-wide m-0">SECRETARIA DE SALUD</h4>
        <h5 class="font-bold text-sm tracking-wide m-0">REGIÓN SANITARIA METROPOLITANA DEL DISTRITO CENTRAL</h5>
        <h6 class="font-semibold text-xs tracking-wider m-0">UNIDAD DE PLANEAMIENTO / ÁREA DE GESTIÓN DE LA INFORMACIÓN
        </h6>
        <div class="inline-block border-b-2 border-slate-900 dark:border-white pb-0.5 px-4 mt-1">
            <h5 class="font-black text-sm tracking-widest m-0">RENDIMIENTO MEDICO - INFORME OFICIAL DE OBSERVACIONES
            </h5>
        </div>
    </div>

    <!-- Fila 2: Datos de Establecimiento, Jornada/Servicio Social, Mes, Año, Firma -->
    <div
        class="flex items-center justify-between text-xs font-bold uppercase text-slate-800 dark:text-slate-200 border-b border-gray-300 dark:border-gray-700 pb-2 mb-3">
        <div>
            <span>ESTABLECIMIENTO DE SALUD:</span>
            <span
                class="border-b border-black dark:border-white px-2 font-extrabold text-blue-700 dark:text-blue-400">{{ $settings['nombre_establecimiento'] ?? 'CENTRO INTEGRAL DE SALUD SAN MIGUEL' }}</span>
        </div>
        <div>
            @if($jornada === 'SERVICIO SOCIAL')
                <span class="font-black text-sm px-2 text-blue-700 dark:text-blue-400">MEDICOS EN SERVICIO SOCIAL</span>
            @else
                <span>JORNADA:</span>
                <span
                    class="border-b border-black dark:border-white px-2 font-extrabold text-blue-700 dark:text-blue-400">{{ $jornada }}</span>
            @endif
        </div>
        <div>
            <span>MES:</span>
            <span
                class="border-b border-black dark:border-white px-2 font-extrabold text-blue-700 dark:text-blue-400">{{ $mes }}</span>
        </div>
        <div>
            <span>AÑO:</span>
            <span
                class="border-b border-black dark:border-white px-2 font-extrabold text-blue-700 dark:text-blue-400">{{ $ano }}</span>
        </div>
        <div class="text-right">
            <span class="text-[10px] text-gray-500 uppercase">FIRMA Y SELLO _______________________</span>
        </div>
    </div>
</div>

{{-- Tabla Oficial de Observaciones --}}
<table class="table table-bordered table-sm mb-0 w-full" id="consolidadoTable">
    <thead>
        <tr class="header-row-main">
            <th class="sticky-col-1" style="width: 44px; min-width: 44px; text-align: center;">N°</th>
            <th class="sticky-col-2 col-medico-name" style="width: 320px; min-width: 320px; text-align: left !important; padding-left: 14px !important;">NOMBRE COMPLETO DEL MEDICO</th>
            <th style="text-align: left !important; padding-left: 14px !important;">OBSERVACIONES</th>
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

                // Texto combinado que se muestra
                if ($obsEstatica && $obsDinamica) {
                    $obsTexto = $obsEstatica . ' | ' . $obsDinamica;
                } elseif ($obsEstatica) {
                    $obsTexto = $obsEstatica;
                } else {
                    $obsTexto = $obsDinamica;
                }
            @endphp
            <tr class="medico-obs-row" data-name="{{ strtoupper($medico->NOM_MED) }}"
                ondblclick="abrirModalObservacion({{ $medico->id }}, '{{ addslashes($medico->NOM_MED) }}', '{{ addslashes($obsEstatica) }}', '{{ addslashes($obsDinamica) }}')">
                <td class="sticky-col-1 text-center font-bold" style="width: 44px; color: var(--text-muted); font-size: 0.82rem;">
                    {{ $index + 1 }}</td>
                <td class="sticky-col-2 col-medico-name font-bold"
                    style="text-align: left !important; padding-left: 14px !important; color: var(--text-primary); font-size: 0.84rem; text-transform: uppercase;">
                    {{ $medico->NOM_MED }}
                </td>
                <td class="text-left" style="text-align: left !important; padding: 2px 8px !important;">
                    {{-- Input de texto plano editable y botón de modal detallado --}}
                    <div class="flex items-center gap-1.5 no-print">
                        <input type="text" id="obs_input_{{ $medico->id }}" class="obs-plain-input" value="{{ $obsTexto }}"
                            placeholder="Escriba observaciones para este médico..." data-static="{{ $obsEstatica }}"
                            title="Haga clic para escribir texto de la observación (guardado automático)"
                            onchange="guardarObservacionConsolidado({{ $medico->id }}, this.value, this.dataset.static, this)">
                        <button type="button" class="btn-obs-edit"
                            onclick="abrirModalObservacion({{ $medico->id }}, '{{ addslashes($medico->NOM_MED) }}', '{{ addslashes($obsEstatica) }}', '{{ addslashes($obsDinamica) }}')"
                            title="Editar en ventana emergente / sugerencias rápidas">
                            <i class="bi bi-pencil-square"></i>
                        </button>
                    </div>
                    <span class="hidden print:inline font-bold text-black"
                        style="font-size: 0.84rem; text-transform: uppercase;">{{ $obsTexto }}</span>
                </td>
            </tr>
        @endforeach

        @for($i = $rowsCount + 1; $i <= $minRows; $i++)
            <tr class="empty-row">
                <td class="sticky-col-1 text-center font-bold" style="color: var(--text-muted); opacity: 0.6; font-size: 0.82rem;">
                    {{ $i }}</td>
                <td class="sticky-col-2 col-medico-name" style="text-align: left !important;">&nbsp;</td>
                <td style="text-align: left !important;">&nbsp;</td>
            </tr>
        @endfor
    </tbody>
</table>