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
            margin: 4mm 8mm 4mm 8mm;
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
            padding: 3mm 8mm 2mm 8mm;
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
                height: auto !important;
                min-height: 98% !important;
                padding: 0 !important;
                display: flex !important;
                flex-direction: column !important;
                justify-content: flex-start !important;
                gap: 2px !important;
                page-break-after: always !important;
                page-break-inside: avoid !important;
                overflow: hidden !important;
            }
            .print-sheet:last-of-type {
                page-break-after: avoid !important;
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
                padding: 4mm 8mm;
            }
        }

        /* ─── Encabezado Oficial Full HD (Negrita y Todo Subrayado) ─── */
        .header-grid {
            display: grid;
            grid-template-columns: 18% 64% 18%;
            align-items: center;
            width: 100%;
            margin-bottom: 5px;
        }

        .resizable-logo-container {
            max-width: 100%;
        }

        .header-title-box {
            text-align: center;
            line-height: 1.18;
        }

        .header-title-box .inst-1 {
            font-size: 15.5px;
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
            padding-top: 2px;
            padding-bottom: 4px;
            margin-top: 6px;
            margin-bottom: 5px;
        }

        .header-info-bar span.val {
            font-weight: bold;
            text-transform: uppercase;
            padding: 0 4px;
        }

        /* ─── Tabla Oficial Rendimiento Médico ─── */
        .table-wrap {
            flex: 0 0 auto;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            margin-top: 3px;
            margin-bottom: 0px !important;
        }

        table.table-oficial {
            width: 100%;
            border-collapse: collapse !important;
            border: 2.5px solid #000000 !important;
            table-layout: fixed;
            margin: 0 !important;
            -webkit-font-smoothing: antialiased;
            font-family: Arial, Helvetica, sans-serif !important;
        }

        table.table-oficial th,
        table.table-oficial td {
            border: 1px solid #000000 !important;
            text-align: center;
            vertical-align: middle !important;
            color: #000000 !important;
            padding: 0 2px !important;
            overflow: hidden;
            white-space: nowrap;
            font-weight: normal !important;
            font-family: Arial, Helvetica, sans-serif !important;
        }

        /* Encabezados de la Tabla (Negrita Intensa - 900) */
        table.table-oficial thead th {
            font-weight: 900 !important;
            background-color: #ffffff !important;
            color: #000000 !important;
        }

        table.table-oficial thead tr.row-main th {
            height: 21px !important;
            font-size: 0.62rem !important;
            letter-spacing: 0.2px;
            font-weight: 900 !important;
        }

        table.table-oficial thead tr.row-mid th {
            height: 19px !important;
            font-size: 0.58rem !important;
            letter-spacing: 0.1px;
            font-weight: 900 !important;
        }

        table.table-oficial thead tr.row-sub th {
            height: 92px !important;
            max-height: 92px !important;
            font-weight: 900 !important;
        }

        /* Texto Vertical en Columnas (Negrita Intensa - 900) */
        .v-text {
            writing-mode: vertical-rl !important;
            transform: rotate(180deg) !important;
            font-size: 0.48rem !important;
            font-weight: 900 !important;
            line-height: 1.06 !important;
            letter-spacing: 0px !important;
            white-space: normal !important;
            word-break: normal !important;
            padding: 1px 1px !important;
            margin: 0 auto;
            max-height: 73px;
        }

        /* Bordes Gruesos Oficiales (Divisores de grupos según matriz física) */
        .b-thick-r {
            border-right: 2.5px solid #000000 !important;
        }

        table.table-oficial thead {
            border-bottom: 2.5px solid #000000 !important;
        }

        /* Filas del Cuerpo (Sin Negrita y Altura Reducida Proporcionalmente) */
        table.table-oficial tbody tr {
            height: 20.5px !important;
            max-height: 21px !important;
        }

        table.table-oficial tbody td {
            font-size: 0.73rem !important;
            font-weight: normal !important;
            line-height: 1 !important;
            padding: 0 1px !important;
        }

        table.table-oficial tbody td.col-name {
            text-align: left !important;
            padding-left: 6px !important;
            padding-right: 2px !important;
            font-size: 0.72rem !important;
            font-weight: normal !important;
            text-transform: uppercase;
        }

        /* Fila de Totales (Sin Negrita, bordes superior e inferior gruesos) */
        table.table-oficial tr.total-row {
            height: 23px !important;
            background-color: #f1f5f9 !important;
            border-top: 2.5px solid #000000 !important;
            border-bottom: 2.5px solid #000000 !important;
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
            grid-template-columns: 47% 53%;
            gap: 10px;
            width: 100%;
            max-width: 100%;
            font-size: 0.47rem;
            font-weight: 800;
            line-height: 1.35;
            border-top: none !important;
            padding-top: 2px !important;
            margin-top: 2px !important;
            text-align: left !important;
            box-sizing: border-box;
            overflow: hidden;
        }

        .print-footer-notes .notes-col {
            text-align: left !important;
        }

        .print-footer-notes .notes-col div {
            white-space: nowrap !important;
            padding: 0.5px 0 !important;
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
        @if($jornada === 'TOTAL JORNADAS' || $jornada === 'TODAS LAS JORNADAS')
            <button type="button" class="btn-ctrl btn-ctrl-subtle" onclick="toggleDetalleHojas()" title="Alternar entre imprimir solo la tabla resumen de todas las jornadas o incluir las hojas detalladas por jornada">
                <i class="fas fa-layer-group"></i> <span id="btn-toggle-detalle-txt">Incluir Hojas por Jornada</span>
            </button>
        @endif
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

        $isTotalJornadas = ($jornada === 'TOTAL JORNADAS' || $jornada === 'TODAS LAS JORNADAS') && isset($dataByJornada);

        $summaryJornadasData = [];
        $grandTotals = [
            'acuerdo' => 0, 'contrato' => 0, 'm_general' => 0, 'm_especialista' => 0,
            'hrs_tadas' => 0, 'dia_cont' => 0, 'dia_cump' => 0, 'hr_cont' => 0, 'hr_cump' => 0,
            'prog' => 0, 'repr' => 0, 'atend' => 0,
            'hsc_comp' => 0, 'hsc_esfam' => 0, 'hsc_prom' => 0, 'hsc_cong' => 0,
            'hsc_campo' => 0, 'hsc_asam' => 0, 'hsc_citas' => 0,
            'hsc_ord' => 0, 'hsc_profil' => 0, 'hsc_pers' => 0
        ];

        if ($isTotalJornadas) {
            foreach (['MATUTINA', 'VESPERTINA', 'FIN DE SEMANA'] as $jName) {
                $jData = $dataByJornada[$jName] ?? [];
                $jTotals = [
                    'acuerdo' => 0, 'contrato' => 0, 'm_general' => 0, 'm_especialista' => 0,
                    'hrs_tadas' => 0, 'dia_cont' => 0, 'dia_cump' => 0, 'hr_cont' => 0, 'hr_cump' => 0,
                    'prog' => 0, 'repr' => 0, 'atend' => 0,
                    'hsc_comp' => 0, 'hsc_esfam' => 0, 'hsc_prom' => 0, 'hsc_cong' => 0,
                    'hsc_campo' => 0, 'hsc_asam' => 0, 'hsc_citas' => 0,
                    'hsc_ord' => 0, 'hsc_profil' => 0, 'hsc_pers' => 0
                ];
                foreach ($jData as $row) {
                    $m = $row['medico'];
                    $nomina = strtoupper($m->NOMINA ?? '');
                    $modalidad = strtoupper($m->MODALIDAD ?? '');
                    $especialidad = trim(strtoupper($m->ESPECIALIDAD ?? ''));
                    $nombre = strtoupper($m->NOM_MED ?? '');
                    $obs = strtoupper($m->observaciones ?? '');

                    $isONG = (!empty($row['is_ong']) || !empty($m->es_ong) || str_contains($modalidad, 'ONG') || str_contains($nomina, 'ONG') || str_contains($modalidad, 'TEMPORAL') || str_contains($nomina, 'TEMPORAL') || str_contains($nombre, 'MEDICOS SIN FRONTERAS') || str_contains($nombre, 'UNITEC') || str_contains($nombre, 'TEMPORAL') || str_contains($nombre, 'ONG') || str_contains($obs, 'MEDICOS SIN FRONTERAS') || str_contains($obs, 'UNITEC') || str_contains($obs, 'TEMPORAL'));
                    $isSS = (str_contains($nomina, 'SOCIAL') || str_contains($modalidad, 'SOCIAL') || str_contains($especialidad, 'SOCIAL') || str_contains($nombre, 'MSS.'));
                    $isAcuerdo = (!$isONG && !$isSS && (str_contains($nomina, 'ACUERDO') || str_contains($nomina, 'PERMANENTE') || str_contains($modalidad, 'ACUERDO') || str_contains($modalidad, 'PERMANENTE')));
                    $isContrato = (!$isONG && ($isSS || str_contains($nomina, 'CONTRATO') || str_contains($nomina, 'INTERINATO') || str_contains($modalidad, 'CONTRATO') || str_contains($modalidad, 'INTERINATO') || (!$isAcuerdo && ($nomina != '' || $modalidad != ''))));

                    $isEspecialista = (!$isONG && $especialidad !== '' && $especialidad !== 'MEDICO GENERAL' && $especialidad !== 'MÉDICO GENERAL' && !$isSS);
                    $isGeneral = (!$isONG && !$isEspecialista);

                    if ($isAcuerdo) $jTotals['acuerdo']++;
                    if ($isContrato) $jTotals['contrato']++;
                    if ($isGeneral) $jTotals['m_general']++;
                    if ($isEspecialista) $jTotals['m_especialista']++;

                    $jTotals['hrs_tadas'] += $row['horasPorDia'];
                    $jTotals['dia_cont']  += $row['diasContratados'];
                    $jTotals['dia_cump']  += $row['diasCumplidos'];
                    $jTotals['hr_cont']   += $row['horasContratadasMes'];
                    $jTotals['hr_cump']   += $row['horasCumplidas'];
                    $jTotals['prog']      += $row['prog'];
                    $jTotals['repr']      += $row['repr'];
                    $jTotals['atend']     += $row['atenciones'];

                    if (!$isONG) {
                        $h = $row['hsc'] ?? null;
                        $jTotals['hsc_comp']   += $h ? round((float)($h->compensatorio ?? 0)) : 0;
                        $jTotals['hsc_esfam']  += $h ? round((float)($h->esfam ?? 0)) : 0;
                        $jTotals['hsc_prom']   += $h ? round((float)($h->promocion ?? 0)) : 0;
                        $jTotals['hsc_cong']   += $h ? round((float)($h->congresos_medicos ?? 0)) : 0;
                        $jTotals['hsc_campo']  += $h ? round((float)($h->trabajo_campo ?? 0)) : 0;
                        $jTotals['hsc_asam']   += $h ? round((float)($h->convocatoria_general ?? 0)) : 0;
                        $jTotals['hsc_citas']  += $h ? round((float)($h->incapacidad ?? 0) + (float)($h->cita_ihss ?? 0)) : 0;
                        $jTotals['hsc_ord']    += $h ? round((float)($h->vacaciones_ordinarias ?? 0)) : 0;
                        $jTotals['hsc_profil'] += $h ? round((float)($h->descanso_profilactico ?? 0)) : 0;
                        $jTotals['hsc_pers']   += $h ? round((float)($h->permiso_personal ?? 0)) : 0;
                    }
                }

                $rendVal = ($jTotals['repr'] > 0) ? round(($jTotals['atend'] / $jTotals['repr']) * 100) . '%' : '0%';
                $summaryJornadasData[$jName] = [
                    'nombre' => 'TOTAL JORNADA ' . $jName,
                    'totals' => $jTotals,
                    'rend'   => $rendVal
                ];

                foreach ($jTotals as $k => $v) {
                    $grandTotals[$k] += $v;
                }
            }
        }
        $grandRend = ($grandTotals['repr'] > 0) ? round(($grandTotals['atend'] / $grandTotals['repr']) * 100) . '%' : '0%';

        $jornadasSheets = [];
        if ($isTotalJornadas) {
            foreach ($dataByJornada as $jName => $jData) {
                $jornadasSheets[$jName] = $jData;
            }
        } else {
            $jornadasSheets[$jornada] = $data ?? [];
        }

        $nombreEstablecimiento = $settings['nombre_establecimiento'] ?? 'CENTRO INTEGRAL DE SALUD SAN MIGUEL';
        $mostrarLogoDer = !empty($settings['mostrar_logo_derecho']);
    @endphp

    @if($isTotalJornadas)
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        {{-- HOJA OFICIAL RESUMEN: TODAS LAS JORNADAS (TABLA MOSTRADA)        --}}
        {{-- ══════════════════════════════════════════════════════════════════ --}}
        <div class="print-sheet print-sheet-summary">
            <!-- ── 1. Encabezado Oficial ── -->
            <div class="print-header">
                <div class="header-grid">
                    <div style="text-align: left; padding-left: 5px;">
                        <div id="box-logo-izquierdo" class="resizable-logo-container" data-logo-name="logo_izquierdo" title="Doble clic para cambiar logo"
                             style="width: {{ $settings['logo_izquierdo_width'] ?? '145px' }}; height: {{ $settings['logo_izquierdo_height'] ?? '44px' }};">
                            <img src="{{ asset('img/logos/logo_izquierdo.png') }}" alt="Logo Izquierdo" id="img_logo_izquierdo" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                        </div>
                    </div>

                    <div class="header-title-box">
                        <div class="inst-1">SECRETARIA DE SALUD</div>
                        <div class="inst-2">REGIÓN SANITARIA METROPOLITANA DEL DISTRITO CENTRAL</div>
                        <div class="inst-3">UNIDAD DE PLANEAMIENTO / ÁREA DE GESTIÓN DE LA INFORMACIÓN</div>
                        <div class="inst-report">RENDIMIENTO MEDICO</div>
                    </div>

                    <div style="text-align: center; position: relative;">
                        <div id="box-logo-derecho" class="resizable-logo-container" data-logo-name="logo_derecho" title="Doble clic para cambiar logo"
                             style="display: {{ $mostrarLogoDer ? 'inline-block' : 'none' }}; margin: 0 auto; width: {{ $settings['logo_derecho_width'] ?? '65px' }}; height: {{ $settings['logo_derecho_height'] ?? '65px' }};">
                            <img src="{{ asset('img/logos/logo_derecho.png') }}" alt="Logo Derecho" id="img_logo_derecho" style="max-width: 100%; max-height: 100%; object-fit: contain;">
                        </div>
                        <div class="signature-box" style="width: 175px; margin: 0 auto; padding-top: {{ $mostrarLogoDer ? '4px' : '36px' }};">
                            <div style="border-top: 1.8px solid #000; width: 100%;"></div>
                            <div style="font-size: 0.62rem; font-weight: bold; letter-spacing: 0.5px; padding-top: 3px;">FIRMA Y SELLO</div>
                        </div>
                    </div>
                </div>

                <div class="header-info-bar">
                    <div>ESTABLECIMIENTO DE SALUD: <span class="val" id="lbl-establecimiento-summary">{{ $nombreEstablecimiento }}</span></div>
                    <div>JORNADA: <span class="val">TODAS LAS JORNADAS{{ !empty($onlySS) ? ' (SERVICIO SOCIAL)' : '' }}</span></div>
                    <div>MES: <span class="val">{{ $mesNombre }}</span></div>
                    <div>AÑO: <span class="val">{{ $ano }}</span></div>
                </div>
            </div>

            <div class="table-wrap">
                <table class="table-oficial">
                    <thead>
                        <tr class="row-main">
                            <th rowspan="3" class="b-thick-r" style="width: 2.5%;">#</th>
                            <th rowspan="3" class="b-thick-r" style="width: 21.5%; text-align: left; padding-left: 6px !important;">NOMBRE COMPLETO DEL MEDICO</th>
                            <th colspan="2" rowspan="2" class="b-thick-r" style="width: 6.2%;">MODALIDAD</th>
                            <th colspan="2" rowspan="2" class="b-thick-r" style="width: 6.4%;">CATEGORIA</th>
                            <th rowspan="3" class="b-thick-r" style="width: 3.5%;"><div class="v-text">HORAS / DÍA</div></th>
                            <th colspan="2" rowspan="2" class="b-thick-r" style="width: 6.4%;">DIAS MES</th>
                            <th colspan="2" rowspan="2" class="b-thick-r" style="width: 6.4%;">HORAS MES</th>
                            <th colspan="3" rowspan="2" class="b-thick-r" style="width: 10.5%;">ATENCIONES</th>
                            <th rowspan="3" class="b-thick-r" style="width: 3.6%;"><div class="v-text">% RENDIMIENTO</div></th>
                            <th colspan="10" style="width: 33.0%; background-color: #f8fafc !important;">HORAS SIN CONSULTA</th>
                        </tr>
                        <tr class="row-mid">
                            <th colspan="7" class="b-thick-r" style="width: 23.1%;">TOTAL DE HORAS OFICIALES</th>
                            <th colspan="2" class="b-thick-r" style="width: 6.6%;">VACACIONES</th>
                            <th rowspan="2" style="width: 3.3%;"><div class="v-text">PERMISOS PERSONALES</div></th>
                        </tr>
                        <tr class="row-sub">
                            <th style="width: 3.1%;"><div class="v-text">ACUERDO</div></th>
                            <th class="b-thick-r" style="width: 3.1%;"><div class="v-text">CONTRATO</div></th>
                            <th style="width: 3.2%;"><div class="v-text">GENERAL</div></th>
                            <th class="b-thick-r" style="width: 3.2%;"><div class="v-text">ESPECIALISTA</div></th>
                            <th style="width: 3.2%;"><div class="v-text">CONTRATADOS</div></th>
                            <th class="b-thick-r" style="width: 3.2%;"><div class="v-text">CUMPLIDOS</div></th>
                            <th style="width: 3.2%;"><div class="v-text">CONTRATADAS</div></th>
                            <th class="b-thick-r" style="width: 3.2%;"><div class="v-text">CUMPLIDAS</div></th>
                            <th style="width: 3.5%;"><div class="v-text">PROGRAMADAS</div></th>
                            <th style="width: 3.5%;"><div class="v-text">REPROGRAMADAS</div></th>
                            <th class="b-thick-r" style="width: 3.5%;"><div class="v-text">ATENDIDAS</div></th>
                            <th style="width: 3.3%;"><div class="v-text">FERIADOS / COMPENSATORIOS</div></th>
                            <th style="width: 3.3%;"><div class="v-text">ESFAM</div></th>
                            <th style="width: 3.3%;"><div class="v-text">**** OTRAS ACTIVIDADES</div></th>
                            <th style="width: 3.3%;"><div class="v-text">CONGRESOS / TALLERES</div></th>
                            <th style="width: 3.3%;"><div class="v-text">INVESTIGACION DE CAMPO</div></th>
                            <th style="width: 3.3%;"><div class="v-text">ASAMBLEA COLEGIO MÉDICO</div></th>
                            <th class="b-thick-r" style="width: 3.3%;"><div class="v-text">CITAS / INCAPACIDADES</div></th>
                            <th style="width: 3.3%;"><div class="v-text">ORDINARIAS</div></th>
                            <th class="b-thick-r" style="width: 3.3%;"><div class="v-text">PROFILÁCTICAS</div></th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $rIdx = 0; @endphp
                        @foreach(['MATUTINA', 'VESPERTINA', 'FIN DE SEMANA'] as $jName)
                            @php
                                $sRow = $summaryJornadasData[$jName] ?? null;
                                $rIdx++;
                            @endphp
                            @if($sRow)
                            <tr>
                                <td class="b-thick-r">{{ $rIdx }}</td>
                                <td class="b-thick-r font-bold" style="text-align: left; padding-left: 6px !important;">{{ $sRow['nombre'] }}</td>
                                <td>{{ round($sRow['totals']['acuerdo']) }}</td>
                                <td class="b-thick-r">{{ round($sRow['totals']['contrato']) }}</td>
                                <td>{{ round($sRow['totals']['m_general']) }}</td>
                                <td class="b-thick-r">{{ round($sRow['totals']['m_especialista']) }}</td>
                                <td class="b-thick-r">{{ round($sRow['totals']['hrs_tadas']) }}</td>
                                <td>{{ round($sRow['totals']['dia_cont']) }}</td>
                                <td class="b-thick-r">{{ round($sRow['totals']['dia_cump']) }}</td>
                                <td>{{ round($sRow['totals']['hr_cont']) }}</td>
                                <td class="b-thick-r">{{ round($sRow['totals']['hr_cump']) }}</td>
                                <td>{{ round($sRow['totals']['prog']) }}</td>
                                <td>{{ round($sRow['totals']['repr']) }}</td>
                                <td class="b-thick-r">{{ round($sRow['totals']['atend']) }}</td>
                                <td class="b-thick-r font-bold">{{ $sRow['rend'] }}</td>
                                <td>{{ $sRow['totals']['hsc_comp'] }}</td>
                                <td>{{ $sRow['totals']['hsc_esfam'] }}</td>
                                <td>{{ $sRow['totals']['hsc_prom'] }}</td>
                                <td>{{ $sRow['totals']['hsc_cong'] }}</td>
                                <td>{{ $sRow['totals']['hsc_campo'] }}</td>
                                <td>{{ $sRow['totals']['hsc_asam'] }}</td>
                                <td class="b-thick-r">{{ $sRow['totals']['hsc_citas'] }}</td>
                                <td>{{ $sRow['totals']['hsc_ord'] }}</td>
                                <td class="b-thick-r">{{ $sRow['totals']['hsc_profil'] }}</td>
                                <td>{{ $sRow['totals']['hsc_pers'] }}</td>
                            </tr>
                            @endif
                        @endforeach

                        @for($i = 3; $i < 24; $i++)
                            <tr class="empty-row">
                                <td class="b-thick-r">{{ $i + 1 }}</td>
                                <td class="b-thick-r">&nbsp;</td>
                                <td>&nbsp;</td>
                                <td class="b-thick-r">&nbsp;</td>
                                <td>&nbsp;</td>
                                <td class="b-thick-r">&nbsp;</td>
                                <td class="b-thick-r">&nbsp;</td>
                                <td>&nbsp;</td>
                                <td class="b-thick-r">&nbsp;</td>
                                <td>&nbsp;</td>
                                <td class="b-thick-r">&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td class="b-thick-r">&nbsp;</td>
                                <td class="b-thick-r">&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td>&nbsp;</td>
                                <td class="b-thick-r">&nbsp;</td>
                                <td>&nbsp;</td>
                                <td class="b-thick-r">&nbsp;</td>
                                <td>&nbsp;</td>
                            </tr>
                        @endfor

                        <tr class="row-totals">
                            <td colspan="2" class="b-thick-r font-bold" style="text-align: right; padding-right: 8px !important;">TOTAL REGIONAL (TODAS LAS JORNADAS)</td>
                            <td>{{ round($grandTotals['acuerdo']) }}</td>
                            <td class="b-thick-r">{{ round($grandTotals['contrato']) }}</td>
                            <td>{{ round($grandTotals['m_general']) }}</td>
                            <td class="b-thick-r">{{ round($grandTotals['m_especialista']) }}</td>
                            <td class="b-thick-r">{{ round($grandTotals['hrs_tadas']) }}</td>
                            <td>{{ round($grandTotals['dia_cont']) }}</td>
                            <td class="b-thick-r">{{ round($grandTotals['dia_cump']) }}</td>
                            <td>{{ round($grandTotals['hr_cont']) }}</td>
                            <td class="b-thick-r">{{ round($grandTotals['hr_cump']) }}</td>
                            <td>{{ round($grandTotals['prog']) }}</td>
                            <td>{{ round($grandTotals['repr']) }}</td>
                            <td class="b-thick-r">{{ round($grandTotals['atend']) }}</td>
                            <td class="b-thick-r font-bold">{{ $grandRend }}</td>
                            <td>{{ $grandTotals['hsc_comp'] }}</td>
                            <td>{{ $grandTotals['hsc_esfam'] }}</td>
                            <td>{{ $grandTotals['hsc_prom'] }}</td>
                            <td>{{ $grandTotals['hsc_cong'] }}</td>
                            <td>{{ $grandTotals['hsc_campo'] }}</td>
                            <td>{{ $grandTotals['hsc_asam'] }}</td>
                            <td class="b-thick-r">{{ $grandTotals['hsc_citas'] }}</td>
                            <td>{{ $grandTotals['hsc_ord'] }}</td>
                            <td class="b-thick-r">{{ $grandTotals['hsc_profil'] }}</td>
                            <td>{{ $grandTotals['hsc_pers'] }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

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
                    <div>*** <span style="color: #dc2626; font-weight: bold;">OTRAS ACTIVIDADES</span> = SOLICITUD Y RECEPCIÓN DE INSUMOS, REALIZACIÓN Y ENTREGA DE INFORMES, ACT EXTRAMUROS,<br>CLUB DE ENFERMOS CRONICOS, EMBARAZADAS, CHARLAS, TAMIZAJES, REUNION INTERSECTORIALES, OTROS.</div>
                </div>
            </div>
        </div>
    @endif

    <div class="print-sheets-detalle" style="{{ $isTotalJornadas ? 'display: none;' : '' }}">


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
            $MAX_ROWS = max(24, count($doctorList));
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
                        <div class="signature-box" style="width: 175px; margin: 0 auto; padding-top: {{ $mostrarLogoDer ? '4px' : '36px' }};">
                            <div style="border-top: 1.8px solid #000; width: 100%;"></div>
                            <div style="font-size: 0.62rem; font-weight: bold; letter-spacing: 0.5px; padding-top: 3px;">FIRMA Y SELLO</div>
                        </div>
                    </div>
                </div>

                <div class="header-info-bar">
                    <div>ESTABLECIMIENTO DE SALUD: <span class="val" id="lbl-establecimiento">{{ $nombreEstablecimiento }}</span></div>
                    <div>JORNADA: <span class="val">{{ $currentJornada === 'CONGLOMERADO SOCIALES' ? 'CONGLOMERADO SOCIALES' : ($currentJornada . (!empty($onlySS) ? ' (SERVICIO SOCIAL)' : '')) }}</span></div>
                    <div>MES: <span class="val">{{ $mesNombre }}</span></div>
                    <div>AÑO: <span class="val">{{ $ano }}</span></div>
                </div>
            </div>

            <div class="table-wrap">
                <table class="table-oficial">
                    <thead>
                        <tr class="row-main">
                            <th rowspan="3" class="b-thick-r" style="width: 2.5%;">#</th>
                            <th rowspan="3" class="b-thick-r" style="width: 21.5%; text-align: left; padding-left: 6px !important;">NOMBRE COMPLETO DEL MEDICO</th>
                            <th colspan="2" rowspan="2" class="b-thick-r" style="width: 6.2%;">MODALIDAD</th>
                            <th colspan="2" rowspan="2" class="b-thick-r" style="width: 6.4%;">CATEGORIA</th>
                            <th rowspan="3" class="b-thick-r" style="width: 3.5%;"><div class="v-text">HORAS / DÍA</div></th>
                            <th colspan="2" rowspan="2" class="b-thick-r" style="width: 6.4%;">DIAS MES</th>
                            <th colspan="2" rowspan="2" class="b-thick-r" style="width: 6.4%;">HORAS MES</th>
                            <th colspan="3" rowspan="2" class="b-thick-r" style="width: 10.5%;">ATENCIONES</th>
                            <th rowspan="3" class="b-thick-r" style="width: 3.6%;"><div class="v-text">% RENDIMIENTO</div></th>
                            <th colspan="10" style="width: 33.0%; background-color: #f8fafc !important;">HORAS SIN CONSULTA</th>
                        </tr>
                        <tr class="row-mid">
                            <th colspan="7" class="b-thick-r" style="width: 23.1%;">TOTAL DE HORAS OFICIALES</th>
                            <th colspan="2" class="b-thick-r" style="width: 6.6%;">VACACIONES</th>
                            <th rowspan="2" style="width: 3.3%;"><div class="v-text">PERMISOS PERSONALES</div></th>
                        </tr>
                        <tr class="row-sub">
                            <th style="width: 3.1%;"><div class="v-text">ACUERDO</div></th>
                            <th class="b-thick-r" style="width: 3.1%;"><div class="v-text">CONTRATO</div></th>
                            <th style="width: 3.2%;"><div class="v-text">GENERAL</div></th>
                            <th class="b-thick-r" style="width: 3.2%;"><div class="v-text">ESPECIALISTA</div></th>
                            <th style="width: 3.2%;"><div class="v-text">CONTRATADOS</div></th>
                            <th class="b-thick-r" style="width: 3.2%;"><div class="v-text">CUMPLIDOS</div></th>
                            <th style="width: 3.2%;"><div class="v-text">CONTRATADAS</div></th>
                            <th class="b-thick-r" style="width: 3.2%;"><div class="v-text">CUMPLIDAS</div></th>
                            <th style="width: 3.5%;"><div class="v-text">PROGRAMADAS</div></th>
                            <th style="width: 3.5%;"><div class="v-text">REPROGRAMADAS</div></th>
                            <th class="b-thick-r" style="width: 3.5%;"><div class="v-text">ATENDIDAS</div></th>
                            <th style="width: 3.3%;"><div class="v-text">FERIADOS / COMPENSATORIOS</div></th>
                            <th style="width: 3.3%;"><div class="v-text">ESFAM</div></th>
                            <th style="width: 3.3%;"><div class="v-text">**** OTRAS ACTIVIDADES</div></th>
                            <th style="width: 3.3%;"><div class="v-text">CONGRESOS / TALLERES</div></th>
                            <th style="width: 3.3%;"><div class="v-text">INVESTIGACION DE CAMPO</div></th>
                            <th style="width: 3.3%;"><div class="v-text">ASAMBLEA COLEGIO MÉDICO</div></th>
                            <th class="b-thick-r" style="width: 3.3%;"><div class="v-text">CITAS / INCAPACIDADES</div></th>
                            <th style="width: 3.3%;"><div class="v-text">ORDINARIAS</div></th>
                            <th class="b-thick-r" style="width: 3.3%;"><div class="v-text">PROFILÁCTICAS</div></th>
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

                                    $isONG = (!empty($row['is_ong']) || !empty($m->es_ong) || str_contains($modalidad, 'ONG') || str_contains($nomina, 'ONG') || str_contains($modalidad, 'TEMPORAL') || str_contains($nomina, 'TEMPORAL') || str_contains($nombre, 'MEDICOS SIN FRONTERAS') || str_contains($nombre, 'UNITEC') || str_contains($nombre, 'TEMPORAL') || str_contains($nombre, 'ONG') || str_contains($obs, 'MEDICOS SIN FRONTERAS') || str_contains($obs, 'UNITEC') || str_contains($obs, 'TEMPORAL'));
                                    $isSS = (str_contains($nomina, 'SOCIAL') || str_contains($modalidad, 'SOCIAL') || str_contains($especialidad, 'SOCIAL') || str_contains($nombre, 'MSS.'));
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

                                    if (!$isONG) {
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
                                    }

                                    $rendVal = $isONG ? '-' : (($row['repr'] > 0) ? round(($row['atenciones'] / $row['repr']) * 100) . '%' : '0%');
                                    $nombreLimpio = limpiarPrefijoMedico($m->NOM_MED);
                                @endphp

                                <tr>
                                    <td class="b-thick-r">{{ $i + 1 }}</td>
                                    <td class="col-name b-thick-r">{{ $nombreLimpio }}</td>
                                    <td class="{{ $isSS ? 'td-hatched' : '' }}">{{ $isONG || $isSS ? '-' : ($isAcuerdo ? 'X' : '-') }}</td>
                                    <td class="b-thick-r">{{ $isONG ? '-' : ($isContrato ? 'X' : '-') }}</td>
                                    <td>{{ $isONG ? '-' : ($isGeneral ? 'X' : '-') }}</td>
                                    <td class="{{ $isSS ? 'td-hatched' : '' }} b-thick-r">{{ $isONG || $isSS ? '-' : ($isEspecialista ? 'X' : '-') }}</td>
                                    <td class="b-thick-r">{{ $isONG ? '-' : ($row['horasPorDia'] > 0 ? round((float)$row['horasPorDia']) : '0') }}</td>
                                    <td>{{ $isONG ? '-' : ($row['diasContratados'] > 0 ? round((float)$row['diasContratados']) : '0') }}</td>
                                    <td class="b-thick-r">{{ $isONG ? '-' : ($row['diasCumplidos'] > 0 ? round((float)$row['diasCumplidos']) : '0') }}</td>
                                    <td>{{ $isONG ? '-' : ($row['horasContratadasMes'] > 0 ? round((float)$row['horasContratadasMes']) : '0') }}</td>
                                    <td class="b-thick-r">{{ $isONG ? '-' : ($row['horasCumplidas'] > 0 ? round((float)$row['horasCumplidas']) : '0') }}</td>
                                    <td>{{ $isONG ? '-' : ($row['prog'] > 0 ? round((float)$row['prog']) : '0') }}</td>
                                    <td>{{ $isONG ? '-' : ($row['repr'] > 0 ? round((float)$row['repr']) : '0') }}</td>
                                    <td class="b-thick-r">{{ round((float)$row['atenciones']) }}</td>
                                    <td class="b-thick-r">{{ $rendVal }}</td>
                                    <td>{{ $isONG ? '-' : ($hsc_comp > 0 ? round((float)$hsc_comp) : '0') }}</td>
                                    <td>{{ $isONG ? '-' : ($hsc_esfam > 0 ? round((float)$hsc_esfam) : '0') }}</td>
                                    <td>{{ $isONG ? '-' : ($hsc_prom > 0 ? round((float)$hsc_prom) : '0') }}</td>
                                    <td>{{ $isONG ? '-' : ($hsc_cong > 0 ? round((float)$hsc_cong) : '0') }}</td>
                                    <td>{{ $isONG ? '-' : ($hsc_campo > 0 ? round((float)$hsc_campo) : '0') }}</td>
                                    <td>{{ $isONG ? '-' : ($hsc_asam > 0 ? round((float)$hsc_asam) : '0') }}</td>
                                    <td class="b-thick-r">{{ $isONG ? '-' : ($hsc_citas > 0 ? round((float)$hsc_citas) : '0') }}</td>
                                    <td class="{{ $isSS ? 'td-hatched' : '' }}">{{ $isONG || $isSS ? '-' : ($hsc_ord > 0 ? round((float)$hsc_ord) : '0') }}</td>
                                    <td class="{{ $isSS ? 'td-hatched' : '' }} b-thick-r">{{ $isONG || $isSS ? '-' : ($hsc_profil > 0 ? round((float)$hsc_profil) : '0') }}</td>
                                    <td>{{ $isONG ? '-' : ($hsc_pers > 0 ? round((float)$hsc_pers) : '0') }}</td>
                                </tr>
                            @else
                                <tr>
                                    <td class="b-thick-r">{{ $i + 1 }}</td>
                                    <td class="col-name b-thick-r">&nbsp;</td>
                                    <td>&nbsp;</td><td class="b-thick-r">&nbsp;</td><td>&nbsp;</td><td class="b-thick-r">&nbsp;</td>
                                    <td class="b-thick-r">&nbsp;</td>
                                    <td>&nbsp;</td><td class="b-thick-r">&nbsp;</td>
                                    <td>&nbsp;</td><td class="b-thick-r">&nbsp;</td>
                                    <td>&nbsp;</td><td>&nbsp;</td><td class="b-thick-r">&nbsp;</td>
                                    <td class="b-thick-r">&nbsp;</td>
                                    <td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td class="b-thick-r">&nbsp;</td>
                                    <td>&nbsp;</td><td class="b-thick-r">&nbsp;</td>
                                    <td>&nbsp;</td>
                                </tr>
                            @endif
                        @endfor

                        @php
                            $rendTotal = ($totals['repr'] > 0) ? round(($totals['atend'] / $totals['repr']) * 100) . '%' : '0%';
                        @endphp
                        <tr class="total-row">
                            <td class="b-thick-r">&nbsp;</td>
                            <td class="col-name b-thick-r" style="font-size: 0.72rem !important;">TOTAL JORNADA.</td>
                            <td>{{ round($totals['acuerdo']) }}</td>
                            <td class="b-thick-r">{{ round($totals['contrato']) }}</td>
                            <td>{{ round($totals['m_general']) }}</td>
                            <td class="b-thick-r">{{ round($totals['m_especialista']) }}</td>
                            <td class="b-thick-r">{{ round($totals['hrs_tadas']) }}</td>
                            <td>{{ round($totals['dia_cont']) }}</td>
                            <td class="b-thick-r">{{ round($totals['dia_cump']) }}</td>
                            <td>{{ round($totals['hr_cont']) }}</td>
                            <td class="b-thick-r">{{ round($totals['hr_cump']) }}</td>
                            <td>{{ round($totals['prog']) }}</td>
                            <td>{{ round($totals['repr']) }}</td>
                            <td class="b-thick-r">{{ round($totals['atend']) }}</td>
                            <td class="b-thick-r">{{ $rendTotal }}</td>
                            <td>{{ round($totals['hsc_comp']) }}</td>
                            <td>{{ round($totals['hsc_esfam']) }}</td>
                            <td>{{ round($totals['hsc_prom']) }}</td>
                            <td>{{ round($totals['hsc_cong']) }}</td>
                            <td>{{ round($totals['hsc_campo']) }}</td>
                            <td>{{ round($totals['hsc_asam']) }}</td>
                            <td class="b-thick-r">{{ round($totals['hsc_citas']) }}</td>
                            <td>{{ round($totals['hsc_ord']) }}</td>
                            <td class="b-thick-r">{{ round($totals['hsc_profil']) }}</td>
                            <td>{{ round($totals['hsc_pers']) }}</td>
                        </tr>
                    </tbody>
                </table>
            </div>

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
                    <div>*** <span style="color: #dc2626; font-weight: bold;">OTRAS ACTIVIDADES</span> = SOLICITUD Y RECEPCIÓN DE INSUMOS, REALIZACIÓN Y ENTREGA DE INFORMES, ACT EXTRAMUROS,<br>CLUB DE ENFERMOS CRONICOS, EMBARAZADAS, CHARLAS, TAMIZAJES, REUNION INTERSECTORIALES, OTROS.</div>
                </div>
            </div>
        </div>
    @endforeach
    </div>

    <script>
        // Alternar visualización de las hojas detalladas cuando es TOTAL JORNADAS
        function toggleDetalleHojas() {
            const detalle = $('.print-sheets-detalle');
            if (detalle.is(':visible')) {
                detalle.hide();
                $('#btn-toggle-detalle-txt').text('Incluir Hojas por Jornada');
            } else {
                detalle.show();
                $('#btn-toggle-detalle-txt').text('Solo Hoja Resumen');
            }
        }

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
            const actual = $('#lbl-establecimiento, #lbl-establecimiento-summary').first().text().trim();
            const nuevo = prompt('Escriba el nombre del Establecimiento de Salud:', actual);
            if (nuevo !== null && nuevo.trim() !== '') {
                $('#lbl-establecimiento, #lbl-establecimiento-summary').text(nuevo.trim().toUpperCase());
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
