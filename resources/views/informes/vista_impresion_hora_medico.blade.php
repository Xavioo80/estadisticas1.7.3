<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe Rendimiento Médico - {{ $mesNombre }} {{ $ano }}</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <style>
        /* ─── Configuración de Página: Ajustado al Área de Impresión (Landscape) ─── */
        @page {
            size: landscape;
            margin: 2mm 4mm 1mm 4mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            font-family: Arial, Helvetica, sans-serif !important;
        }

        html, body {
            margin: 0;
            padding: 0;
            background-color: #e2e8f0;
            font-family: Arial, Helvetica, sans-serif !important;
            color: #000000;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            text-rendering: optimizeLegibility;
        }

        /* ─── Hoja Imprimible: Ocupa Toda la Hoja en 1 Sola Página (Full HD) ─── */
        .print-sheet {
            width: 100%;
            max-width: 1350px;
            padding: 3mm 5mm 2mm 5mm;
            margin: 10px auto;
            background: #ffffff;
            box-shadow: 0 4px 14px rgba(0, 0, 0, 0.15);
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            gap: 2px;
            page-break-inside: avoid;
            page-break-after: always;
        }

        .print-sheet:last-of-type {
            page-break-after: avoid;
        }

        @media print {
            html, body {
                background: transparent !important;
                margin: 0 !important;
                padding: 0 !important;
                width: 100% !important;
                height: 100% !important;
                font-family: Arial, Helvetica, sans-serif !important;
                -webkit-font-smoothing: antialiased !important;
            }
            .print-sheet {
                box-shadow: none !important;
                margin: 0 !important;
                width: 100% !important;
                max-width: 100% !important;
                height: 100% !important;
                max-height: 100vh !important;
                padding: 0 !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: flex-start !important;
                gap: 2px !important;
                page-break-after: avoid !important;
                page-break-before: avoid !important;
                page-break-inside: avoid !important;
                overflow: hidden !important;
            }
            .no-print {
                display: none !important;
            }
        }

        @media screen {
            body {
                background-color: #cbd5e1;
                padding: 15px 15px 50px 15px;
                display: flex;
                flex-direction: column;
                align-items: center;
            }
            .print-sheet {
                width: 98vw;
                max-width: 1350px;
                min-height: 215mm;
                margin: 15px auto;
                padding: 4mm 6mm;
            }
        }

        /* ─── Encabezado Oficial Full HD (Negrita y Todo Subrayado) ─── */
        .header-grid {
            display: grid;
            grid-template-columns: 22% 56% 22%;
            align-items: center;
            width: 100%;
            margin-bottom: 2px;
        }

        .header-title-box {
            text-align: center;
            line-height: 1.18;
        }

        .header-title-box .inst-1 {
            font-size: 15px;
            font-weight: bold !important;
            text-decoration: underline !important;
            letter-spacing: 0.5px;
        }
        .header-title-box .inst-2 {
            font-size: 13px;
            font-weight: bold !important;
            text-decoration: underline !important;
            letter-spacing: 0.2px;
        }
        .header-title-box .inst-3 {
            font-size: 12px;
            font-weight: bold !important;
            text-decoration: underline !important;
        }
        .header-title-box .inst-report {
            font-size: 13.5px;
            font-weight: bold !important;
            text-decoration: underline !important;
            margin-top: 1px;
            letter-spacing: 0.4px;
        }

        .header-info-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            font-size: 0.76rem;
            font-weight: normal;
            border-bottom: 1.8px solid #000;
            padding-bottom: 3px;
            margin-top: 3px;
            margin-bottom: 3px;
        }

        .header-info-bar span.val {
            font-weight: bold;
            text-transform: uppercase;
            padding: 0 4px;
        }

        /* ─── Tabla Oficial Rendimiento Médico (Toda la tabla sin negrita) ─── */
        .table-wrap {
            flex: 0 0 auto;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            margin-top: 1px;
            margin-bottom: 0px !important;
        }

        table.table-oficial {
            width: 100%;
            border-collapse: collapse !important;
            border: 2px solid #000000 !important;
            table-layout: fixed;
            margin: 0 !important;
            -webkit-font-smoothing: antialiased;
            font-family: Arial, Helvetica, sans-serif !important;
        }

        table.table-oficial th,
        table.table-oficial td {
            border: 1.2px solid #000000 !important;
            text-align: center;
            vertical-align: middle !important;
            color: #000000 !important;
            padding: 0 2px !important;
            overflow: hidden;
            white-space: nowrap;
            font-weight: normal !important;
            font-family: Arial, Helvetica, sans-serif !important;
        }

        /* Encabezados de la Tabla (Sin Negrita y tamaño reducido en 1px) */
        table.table-oficial thead th {
            font-weight: normal !important;
            background-color: #ffffff !important;
            color: #000000 !important;
        }

        table.table-oficial thead tr.row-main th {
            height: 21px !important;
            font-size: 0.62rem !important;
            letter-spacing: 0.2px;
            font-weight: normal !important;
        }

        table.table-oficial thead tr.row-mid th {
            height: 19px !important;
            font-size: 0.58rem !important;
            letter-spacing: 0.1px;
            font-weight: normal !important;
        }

        table.table-oficial thead tr.row-sub th {
            height: 75px !important;
            max-height: 76px !important;
            font-weight: normal !important;
        }

        /* Texto Vertical en Columnas (Sin Negrita y reducido 1px) */
        .v-text {
            writing-mode: vertical-rl !important;
            transform: rotate(180deg) !important;
            font-size: 0.48rem !important;
            font-weight: normal !important;
            line-height: 1.06 !important;
            letter-spacing: 0px !important;
            white-space: normal !important;
            word-break: normal !important;
            padding: 1px 1px !important;
            margin: 0 auto;
            max-height: 73px;
        }

        /* Filas del Cuerpo (Sin Negrita) */
        table.table-oficial tbody tr {
            height: 22px !important;
            max-height: 22px !important;
        }

        table.table-oficial tbody td {
            font-size: 0.74rem !important;
            font-weight: normal !important;
            line-height: 1 !important;
        }

        table.table-oficial tbody td.col-name {
            text-align: left !important;
            padding-left: 6px !important;
            padding-right: 2px !important;
            font-size: 0.72rem !important;
            font-weight: normal !important;
            text-transform: uppercase;
        }

        /* Fila de Totales (Sin Negrita) */
        table.table-oficial tr.total-row {
            height: 23px !important;
            background-color: #f1f5f9 !important;
            border-top: 2px solid #000000 !important;
        }

        table.table-oficial tr.total-row td {
            font-size: 0.74rem !important;
            font-weight: normal !important;
        }

        /* Tramado de rayado para celdas no aplicables */
        .td-hatched {
            background: repeating-linear-gradient(
                -45deg,
                #ffffff,
                #ffffff 2px,
                #94a3b8 2px,
                #94a3b8 3px
            ) !important;
            color: #64748b !important;
            font-weight: bold;
        }

        /* ─── Pie de Página: Notas Oficiales Full HD ─── */
        .print-footer-notes {
            display: grid;
            grid-template-columns: 45% 55%;
            gap: 8px;
            font-size: 0.49rem;
            font-weight: 900;
            line-height: 1.45;
            border-top: none !important;
            padding-top: 2px !important;
            margin-top: 1px !important;
            text-align: left !important;
            white-space: nowrap !important;
        }

        .print-footer-notes .notes-col {
            text-align: left !important;
            white-space: nowrap !important;
        }

        .print-footer-notes .notes-col div {
            white-space: nowrap !important;
            padding: 1.5px 0 !important;
        }

        /* ─── Toolbar Flotante de Controles (Solo en Pantalla) ─── */
        .floating-print-controls {
            position: fixed;
            top: 16px;
            right: 20px;
            z-index: 9999;
            background: rgba(15, 23, 42, 0.92);
            backdrop-filter: blur(10px);
            padding: 8px 12px;
            border-radius: 12px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.3);
            display: flex;
            gap: 8px;
            align-items: center;
        }
        .btn-ctrl {
            font-size: 0.78rem;
            font-weight: 700;
            padding: 6px 12px;
            border-radius: 6px;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 5px;
            cursor: pointer;
            transition: all 0.15s ease;
        }
        .btn-ctrl-primary {
            background: #22c55e;
            color: #ffffff;
        }
        .btn-ctrl-primary:hover { background: #16a34a; }
        .btn-ctrl-subtle {
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
        }
        .btn-ctrl-subtle:hover { background: rgba(255, 255, 255, 0.25); }
    </style>
</head>
<body>

    <!-- Controles Flotantes para Pantalla -->
    <div class="floating-print-controls no-print">
        <button type="button" class="btn-ctrl btn-ctrl-primary" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimir Informe (Oficio Horizontal)
        </button>
        <button type="button" class="btn-ctrl btn-ctrl-subtle" onclick="toggleLogoDerecho()" title="Mostrar u ocultar logo derecho si se requiere">
            <i class="fas fa-image"></i> <span id="btn-toggle-logo-txt">{{ !empty($settings['mostrar_logo_derecho']) ? 'Ocultar Logo Der.' : 'Mostrar Logo Der.' }}</span>
        </button>
        <button type="button" class="btn-ctrl btn-ctrl-subtle" onclick="editarCentroSalud()" title="Cambiar nombre del centro de salud">
            <i class="fas fa-clinic-medical"></i> Editar Establecimiento
        </button>
        <button type="button" class="btn-ctrl btn-ctrl-subtle" onclick="window.close(); history.back();">
            <i class="fas fa-times"></i> Volver
        </button>
    </div>

    @php
        // Función auxiliar para extraer prefijos médicos
        function limpiarPrefijoMedico($nombre) {
            $nombreLimpio = trim($nombre ?? '');
            $prefijos = ['DR. ', 'DRA. ', 'DR ', 'DRA ', 'G.O. ', 'G.O ', 'GO. ', 'GO '];
            $cambio = true;
            while ($cambio) {
                $cambio = false;
                foreach ($prefijos as $prefijo) {
                    if (str_starts_with(strtoupper($nombreLimpio), $prefijo)) {
                        $nombreLimpio = trim(substr($nombreLimpio, strlen($prefijo)));
                        $cambio = true;
                        break;
                    }
                }
            }
            return $nombreLimpio;
        }

        // Determinar las hojas a imprimir
        // Si es TOTAL JORNADAS, se generan las hojas por jornada para que cada una tenga su hoja oficial
        $jornadasSheets = [];
        if ($jornada === 'TOTAL JORNADAS' && isset($dataByJornada)) {
            foreach ($dataByJornada as $jName => $jData) {
                $jornadasSheets[$jName] = $jData;
            }
        } else {
            $jornadasSheets[$jornada] = $data ?? [];
        }

        $nombreEstablecimiento = $settings['nombre_establecimiento'] ?? 'CENTRO INTEGRAL DE SALUD SAN MIGUEL';
        $mostrarLogoDer = !empty($settings['mostrar_logo_derecho']);
    @endphp

    @foreach($jornadasSheets as $currentJornada => $doctorList)
        @php
            $totals = [
                'acuerdo' => 0, 'contrato' => 0, 'm_general' => 0, 'm_especialista' => 0,
                'hrs_tadas' => 0, 'dia_cont' => 0, 'dia_cump' => 0, 'hr_cont' => 0, 'hr_cump' => 0,
                'prog' => 0, 'repr' => 0, 'atend' => 0,
                'hsc_comp' => 0, 'hsc_esfam' => 0, 'hsc_prom' => 0, 'hsc_cong' => 0,
                'hsc_campo' => 0, 'hsc_asam' => 0, 'hsc_citas' => 0,
                'hsc_ord' => 0, 'hsc_profil' => 0, 'hsc_pers' => 0
            ];

            // 24 filas oficiales
            $MAX_ROWS = 24;
            $docCount = count($doctorList);
        @endphp

        <div class="print-sheet">
            <!-- ── 1. Encabezado Oficial ── -->
            <div class="print-header">
                <div class="header-grid">
                    <!-- Área Izquierda: Logo Oficial Izquierdo -->
                    <div style="text-align: left; padding-left: 5px;">
                        <div id="box-logo-izquierdo" class="resizable-logo-container" data-logo-name="logo_izquierdo" title="Doble clic para cambiar logo"
                             style="width: {{ $settings['logo_izquierdo_width'] ?? '145px' }}; height: {{ $settings['logo_izquierdo_height'] ?? '44px' }};">
                            <img src="{{ asset('img/logos/logo_izquierdo.png') }}" alt="Logo Izquierdo" id="img_logo_izquierdo" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                        </div>
                    </div>

                    <!-- Centro: Título Oficial Nacional -->
                    <div class="header-title-box">
                        <div class="inst-1">SECRETARIA DE SALUD</div>
                        <div class="inst-2">REGIÓN SANITARIA METROPOLITANA DEL DISTRITO CENTRAL</div>
                        <div class="inst-3">UNIDAD DE PLANEAMIENTO / ÁREA DE GESTIÓN DE LA INFORMACIÓN</div>
                        <div class="inst-report">RENDIMIENTO MEDICO</div>
                    </div>

                    <!-- Área Derecha: Línea de Firma (con opción de logo derecho si se activa) -->
                    <div style="text-align: center; position: relative;">
                        <div id="box-logo-derecho" class="resizable-logo-container" data-logo-name="logo_derecho" title="Doble clic para cambiar logo"
                             style="display: {{ $mostrarLogoDer ? 'inline-block' : 'none' }}; margin: 0 auto; width: {{ $settings['logo_derecho_width'] ?? '65px' }}; height: {{ $settings['logo_derecho_height'] ?? '65px' }};">
                            <img src="{{ asset('img/logos/logo_derecho.png') }}" alt="Logo Derecho" id="img_logo_derecho" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                        </div>
                        <div class="signature-box" style="width: 175px; margin: 0 auto; padding-top: {{ $mostrarLogoDer ? '4px' : '22px' }};">
                            <div style="border-top: 1.5px solid #000; width: 100%;"></div>
                            <div style="font-size: 0.62rem; font-weight: 900; letter-spacing: 0.5px; padding-top: 2px;">FIRMA Y SELLO</div>
                        </div>
                    </div>
                </div>

                <!-- Barra de Metadatos Oficiales -->
                <div class="header-info-bar">
                    <div>ESTABLECIMIENTO DE SALUD: <span class="val" id="lbl-establecimiento">{{ $nombreEstablecimiento }}</span></div>
                    <div>JORNADA: <span class="val">{{ $currentJornada }}</span></div>
                    <div>MES: <span class="val">{{ $mesNombre }}</span></div>
                    <div>AÑO: <span class="val">{{ $ano }}</span></div>
                </div>
            </div>

            <!-- ── 2. Tabla Oficial de 25 Columnas (Exacta a la Matriz Oficial) ── -->
            <div class="table-wrap">
                <table class="table-oficial">
                    <thead>
                        {{-- Fila 1: Grupos Superiores --}}
                        <tr class="row-main">
                            <th rowspan="3" style="width: 2.5%;">#</th>
                            <th rowspan="3" style="width: 21.5%; text-align: left; padding-left: 6px !important;">NOMBRE COMPLETO DEL MEDICO</th>
                            <th colspan="2" rowspan="2" style="width: 6.2%;">MODALIDAD</th>
                            <th colspan="2" rowspan="2" style="width: 6.4%;">CATEGORIA</th>
                            <th rowspan="3" style="width: 3.5%;"><div class="v-text">HORAS CONTRATADAS X DIA</div></th>
                            <th colspan="2" rowspan="2" style="width: 6.4%;">DIAS MES</th>
                            <th colspan="2" rowspan="2" style="width: 6.4%;">HORAS MES</th>
                            <th colspan="3" rowspan="2" style="width: 10.5%;">ATENCIONES</th>
                            <th rowspan="3" style="width: 3.6%;"><div class="v-text">% DE RENDIMIENTO</div></th>
                            <th colspan="10" style="width: 33.0%; background-color: #f8fafc !important;">HORAS SIN CONSULTA</th>
                        </tr>
                        {{-- Fila 2: Subgrupos bajo Horas Sin Consulta --}}
                        <tr class="row-mid">
                            <th colspan="7" style="width: 23.1%;">TOTAL DE HORAS OFICIALES</th>
                            <th colspan="2" style="width: 6.6%;">VACACIONES</th>
                            <th rowspan="2" style="width: 3.3%;"><div class="v-text">PERMISOS PERSONALES.</div></th>
                        </tr>
                        {{-- Fila 3: Subcolumnas Verticales Detalladas --}}
                        <tr class="row-sub">
                            {{-- Modalidad --}}
                            <th style="width: 3.1%;"><div class="v-text">ACUERDO.</div></th>
                            <th style="width: 3.1%;"><div class="v-text">CONTRATO.</div></th>
                            {{-- Categoría --}}
                            <th style="width: 3.2%;"><div class="v-text">MÉDICO GENERAL.</div></th>
                            <th style="width: 3.2%;"><div class="v-text">MÉDICO ESPECIALISTA.</div></th>
                            {{-- Días Mes --}}
                            <th style="width: 3.2%;"><div class="v-text">CONTRATADOS.</div></th>
                            <th style="width: 3.2%;"><div class="v-text">CUMPLIDOS.</div></th>
                            {{-- Horas Mes --}}
                            <th style="width: 3.2%;"><div class="v-text">CONTRATADAS.</div></th>
                            <th style="width: 3.2%;"><div class="v-text">CUMPLIDAS.</div></th>
                            {{-- Atenciones --}}
                            <th style="width: 3.5%;"><div class="v-text">PROGRAMADAS.</div></th>
                            <th style="width: 3.5%;"><div class="v-text">REPROGRAMADAS.</div></th>
                            <th style="width: 3.5%;"><div class="v-text">ATENDIDAS.</div></th>
                            {{-- Horas Sin Consulta: Oficiales --}}
                            <th style="width: 3.3%;"><div class="v-text">FERIADOS / COMPENSATORIOS</div></th>
                            <th style="width: 3.3%;"><div class="v-text">ESFAM.</div></th>
                            <th style="width: 3.3%;"><div class="v-text">ACTIVIDADES DE PROMOCION.</div></th>
                            <th style="width: 3.3%;"><div class="v-text">CONGRESOS / TALLERES.</div></th>
                            <th style="width: 3.3%;"><div class="v-text">INVESTIGACION DE CAMPO.</div></th>
                            <th style="width: 3.3%;"><div class="v-text">ASAMBLEAS COLEGIO MEDICO.</div></th>
                            <th style="width: 3.3%;"><div class="v-text">CITAS, INCAPACIDADES IHSS / PRIVADA.</div></th>
                            {{-- Horas Sin Consulta: Vacaciones --}}
                            <th style="width: 3.3%;"><div class="v-text">ORDINARIAS.</div></th>
                            <th style="width: 3.3%;"><div class="v-text">PROFILACTICAS.</div></th>
                        </tr>
                    </thead>
                    <tbody>
                        @for($i = 0; $i < $MAX_ROWS; $i++)
                            @php
                                $row = $doctorList[$i] ?? null;
                            @endphp

                            @if($row)
                                @php
                                    $m = $row['medico'];
                                    $nomina = strtoupper($m->NOMINA ?? '');
                                    $modalidad = strtoupper($m->MODALIDAD ?? '');
                                    $especialidad = trim(strtoupper($m->ESPECIALIDAD ?? ''));
                                    $nombre = strtoupper($m->NOM_MED ?? '');
                                    $obs = strtoupper($m->observaciones ?? '');

                                    $isONG = (!empty($row['is_ong']) || !empty($m->es_ong) || str_contains($modalidad, 'ONG') || str_contains($nomina, 'ONG') || str_contains($nombre, 'MEDICOS SIN FRONTERAS') || str_contains($nombre, 'ONG') || str_contains($obs, 'MEDICOS SIN FRONTERAS'));
                                    $isSS = (str_contains($nomina, 'SOCIAL') || str_contains($modalidad, 'SOCIAL') || str_contains($especialidad, 'SOCIAL'));
                                    $isAcuerdo = (!$isONG && !$isSS && (str_contains($nomina, 'ACUERDO') || str_contains($nomina, 'PERMANENTE') || str_contains($modalidad, 'ACUERDO') || str_contains($modalidad, 'PERMANENTE')));
                                    $isContrato = (!$isONG && ($isSS || str_contains($nomina, 'CONTRATO') || str_contains($nomina, 'INTERINATO') || str_contains($modalidad, 'CONTRATO') || str_contains($modalidad, 'INTERINATO') || (!$isAcuerdo && ($nomina != '' || $modalidad != ''))));

                                    $isEspecialista = (!$isONG && $especialidad !== '' && $especialidad !== 'MEDICO GENERAL' && $especialidad !== 'MÉDICO GENERAL' && !$isSS);
                                    $isGeneral = (!$isONG && !$isEspecialista);

                                    if ($isAcuerdo) $totals['acuerdo']++;
                                    if ($isContrato) $totals['contrato']++;
                                    if ($isGeneral) $totals['m_general']++;
                                    if ($isEspecialista) $totals['m_especialista']++;

                                    $totals['hrs_tadas'] += $row['horasPorDia'];
                                    $totals['dia_cont']  += $row['diasContratados'];
                                    $totals['dia_cump']  += $row['diasCumplidos'];
                                    $totals['hr_cont']   += $row['horasContratadasMes'];
                                    $totals['hr_cump']   += $row['horasCumplidas'];
                                    $totals['prog']      += $row['prog'];
                                    $totals['repr']      += $row['repr'];
                                    $totals['atend']     += $row['atenciones'];

                                    $h = $row['hsc'] ?? null;
                                    $hsc_comp   = $h ? round((float)($h->compensatorio ?? 0)) : 0;
                                    $hsc_esfam  = $h ? round((float)($h->esfam ?? 0)) : 0;
                                    $hsc_prom   = $h ? round((float)($h->promocion ?? 0)) : 0;
                                    $hsc_cong   = $h ? round((float)($h->congresos_medicos ?? 0)) : 0;
                                    $hsc_campo  = $h ? round((float)($h->trabajo_campo ?? 0)) : 0;
                                    $hsc_asam   = $h ? round((float)($h->convocatoria_general ?? 0)) : 0;
                                    $hsc_citas  = $h ? round((float)($h->incapacidad ?? 0) + (float)($h->cita_ihss ?? 0)) : 0;
                                    $hsc_ord    = $h ? round((float)($h->vacaciones_ordinarias ?? 0)) : 0;
                                    $hsc_profil = $h ? round((float)($h->descanso_profilactico ?? 0)) : 0;
                                    $hsc_pers   = $h ? round((float)($h->permiso_personal ?? 0)) : 0;

                                    $totals['hsc_comp']   += $hsc_comp;
                                    $totals['hsc_esfam']  += $hsc_esfam;
                                    $totals['hsc_prom']   += $hsc_prom;
                                    $totals['hsc_cong']   += $hsc_cong;
                                    $totals['hsc_campo']  += $hsc_campo;
                                    $totals['hsc_asam']   += $hsc_asam;
                                    $totals['hsc_citas']  += $hsc_citas;
                                    $totals['hsc_ord']    += $hsc_ord;
                                    $totals['hsc_profil'] += $hsc_profil;
                                    $totals['hsc_pers']   += $hsc_pers;

                                    $nombreLimpio = limpiarPrefijoMedico($m->NOM_MED);
                                    $rendVal = ($row['rendimiento'] > 0) ? (round($row['rendimiento']) . '%') : '0%';
                                @endphp

                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td class="col-name">{{ $nombreLimpio }}</td>
                                    <td class="{{ $isSS ? 'td-hatched' : '' }}">{{ $isSS ? '-' : ($isAcuerdo ? 'X' : '-') }}</td>
                                    <td>{{ $isContrato ? 'X' : '-' }}</td>
                                    <td>{{ $isGeneral ? 'X' : '-' }}</td>
                                    <td class="{{ $isSS ? 'td-hatched' : '' }}">{{ $isSS ? '-' : ($isEspecialista ? 'X' : '-') }}</td>
                                    <td>{{ $isONG ? '-' : ($row['horasPorDia'] > 0 ? (round($row['horasPorDia']) == $row['horasPorDia'] ? round($row['horasPorDia']) : number_format($row['horasPorDia'], 1)) : '0') }}</td>
                                    <td>{{ $isONG ? '-' : ($row['diasContratados'] > 0 ? round($row['diasContratados']) : '0') }}</td>
                                    <td>{{ $isONG ? '-' : ($row['diasCumplidos'] > 0 ? round($row['diasCumplidos']) : '0') }}</td>
                                    <td>{{ $isONG ? '-' : ($row['horasContratadasMes'] > 0 ? round($row['horasContratadasMes']) : '0') }}</td>
                                    <td>{{ $isONG ? '-' : ($row['horasCumplidas'] > 0 ? round($row['horasCumplidas']) : '0') }}</td>
                                    <td>{{ $isONG ? '-' : ($row['prog'] > 0 ? round($row['prog']) : '0') }}</td>
                                    <td style="font-weight: 800;">{{ $isONG ? '-' : ($row['repr'] > 0 ? round($row['repr']) : '0') }}</td>
                                    <td style="font-weight: 900;">{{ round($row['atenciones']) }}</td>
                                    <td style="font-weight: 800;">{{ $isONG ? '-' : $rendVal }}</td>
                                    <td>{{ $hsc_comp > 0 ? $hsc_comp : '0' }}</td>
                                    <td>{{ $hsc_esfam > 0 ? $hsc_esfam : '0' }}</td>
                                    <td>{{ $hsc_prom > 0 ? $hsc_prom : '0' }}</td>
                                    <td>{{ $hsc_cong > 0 ? $hsc_cong : '0' }}</td>
                                    <td>{{ $hsc_campo > 0 ? $hsc_campo : '0' }}</td>
                                    <td>{{ $hsc_asam > 0 ? $hsc_asam : '0' }}</td>
                                    <td>{{ $hsc_citas > 0 ? $hsc_citas : '0' }}</td>
                                    <td class="{{ $isSS ? 'td-hatched' : '' }}">{{ $isSS ? '-' : ($hsc_ord > 0 ? $hsc_ord : '0') }}</td>
                                    <td class="{{ $isSS ? 'td-hatched' : '' }}">{{ $isSS ? '-' : ($hsc_profil > 0 ? $hsc_profil : '0') }}</td>
                                    <td>{{ $hsc_pers > 0 ? $hsc_pers : '0' }}</td>
                                </tr>
                            @else
                                {{-- Fila Vacía para Completar la Matriz de 24 Filas Oficial --}}
                                <tr>
                                    <td>{{ $i + 1 }}</td>
                                    <td class="col-name">&nbsp;</td>
                                    <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                                    <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                                    <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                                    <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                                    <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                                    <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td>
                                </tr>
                            @endif
                        @endfor

                        {{-- Fila Final: TOTAL JORNADA --}}
                        @php
                            $rendTotal = ($totals['repr'] > 0) ? round(($totals['atend'] / $totals['repr']) * 100) . '%' : '0%';
                        @endphp
                        <tr class="total-row">
                            <td>&nbsp;</td>
                            <td class="col-name" style="font-size: 0.72rem !important;">TOTAL JORNADA.</td>
                            <td>{{ round($totals['acuerdo']) }}</td>
                            <td>{{ round($totals['contrato']) }}</td>
                            <td>{{ round($totals['m_general']) }}</td>
                            <td>{{ round($totals['m_especialista']) }}</td>
                            <td>{{ round($totals['hrs_tadas']) }}</td>
                            <td>{{ round($totals['dia_cont']) }}</td>
                            <td>{{ round($totals['dia_cump']) }}</td>
                            <td>{{ round($totals['hr_cont']) }}</td>
                            <td>{{ round($totals['hr_cump']) }}</td>
                            <td>{{ round($totals['prog']) }}</td>
                            <td>{{ round($totals['repr']) }}</td>
                            <td>{{ round($totals['atend']) }}</td>
                            <td>{{ $rendTotal }}</td>
                            <td>{{ $totals['hsc_comp'] }}</td>
                            <td>{{ $totals['hsc_esfam'] }}</td>
                            <td>{{ $totals['hsc_prom'] }}</td>
                            <td>{{ $totals['hsc_cong'] }}</td>
                            <td>{{ $totals['hsc_campo'] }}</td>
                            <td>{{ $totals['hsc_asam'] }}</td>
                            <td>{{ $totals['hsc_citas'] }}</td>
                            <td>{{ $totals['hsc_ord'] }}</td>
                            <td>{{ $totals['hsc_profil'] }}</td>
                            <td>{{ $totals['hsc_pers'] }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <!-- ── 3. Notas Oficiales al Pie (Exactas a la Imagen 4) ── -->
            <div class="print-footer-notes">
                <div class="notes-col text-left">
                    <div>*** ESTE INFORME DEBE COINCIDIR CON EL TOTAL DE ATENCIONES DEL AT2R.</div>
                    <div>*** EL ORDEN DE LOS MEDICOS DEBE SER IGUAL AL DE LOS MESES ANTERIORES.</div>
                    <div>*** EN LA PRIMERA CASILLA COLOCAR SIEMPRE EL NOMBRE COMPLETO DEL DIRECTOR / COORDINADOR DEL E.S.</div>
                </div>
                <div class="notes-col text-left">
                    <div>*** COLOCAR EL PERSONAL QUE ESTE DE VACACIONES / INCAPACITADO / TRASLADO (DE LO CONTRARIO SE REPORTARA COMO FALTANTE)</div>
                    <div>*** COLOCAR FECHA DE INICIO Y DE FINAL DE CADA MEDICO EN SERVICIO SOCIAL.</div>
                    <div>*** LLENAR UNA HOJA POR JORNADA (MATUTINA, VESPERTINA, FIN DE SEMANA Y SERVICIO SOCIAL).</div>
                </div>
            </div>
        </div>
    @endforeach

    <script>
        // Alternar visualización del logo derecho (disponible por si deciden mostrarlo)
        function toggleLogoDerecho() {
            const box = $('#box-logo-derecho');
            const isVisible = box.is(':visible');
            if (isVisible) {
                box.hide();
                $('#btn-toggle-logo-txt').text('Mostrar Logo Der.');
                saveSetting('mostrar_logo_derecho', '0');
            } else {
                box.show();
                $('#btn-toggle-logo-txt').text('Ocultar Logo Der.');
                saveSetting('mostrar_logo_derecho', '1');
            }
        }

        // Editar el nombre del Centro de Salud de forma instantánea
        function editarCentroSalud() {
            const actual = $('#lbl-establecimiento').first().text().trim();
            const nuevo = prompt('Escriba el nombre del Establecimiento de Salud:', actual);
            if (nuevo !== null && nuevo.trim() !== '') {
                $('#lbl-establecimiento').text(nuevo.trim().toUpperCase());
                saveSetting('nombre_establecimiento', nuevo.trim().toUpperCase());
            }
        }

        function saveSetting(key, value) {
            $.post('{{ route("informes.hora-medico.save-setting") }}', {
                _token: '{{ csrf_token() }}',
                key: key,
                value: value
            });
        }

        $(document).ready(function() {
            // Manejo de carga de imágenes por doble clic
            $('.resizable-logo-container').on('dblclick', function() {
                const container = $(this);
                const logoName = container.data('logo-name');
                
                const input = $('<input type="file" accept="image/*" style="display:none;">');
                input.on('change', function(e) {
                    const file = e.target.files[0];
                    if (file) {
                        const formData = new FormData();
                        formData.append('image', file);
                        formData.append('name', logoName);
                        formData.append('_token', '{{ csrf_token() }}');

                        $.ajax({
                            url: '{{ route("informes.hora-medico.upload-logo") }}',
                            type: 'POST',
                            data: formData,
                            processData: false,
                            contentType: false,
                            success: function(response) {
                                if (response.success) {
                                    $(`[data-logo-name="${logoName}"] img`).attr('src', response.url + '?t=' + new Date().getTime());
                                }
                            },
                            error: function() {
                                alert('Error al cargar la imagen');
                            }
                        });
                    }
                });
                input.click();
            });
        });
    </script>
</body>
</html>
