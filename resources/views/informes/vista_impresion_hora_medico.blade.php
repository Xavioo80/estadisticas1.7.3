<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Impresión Hora Médico - {{ $mesNombre }} {{ $ano }}</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <style>
        @page {
            size: landscape;
            margin: 6mm 6mm;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: white;
            color: black !important;
            margin: 0;
            padding: 0;
        }

        .print-page {
            page-break-after: always;
            page-break-inside: avoid;
            width: 100%;
            display: flex;
            flex-direction: column;
            padding: 0;
            margin: 0;
            box-sizing: border-box;
            border: 1px solid transparent; /* Evita colapsos de margen */
        }

        .print-page:last-child {
            page-break-after: avoid;
        }

        .official-header {
            width: 100%;
            margin-bottom: 5px;
        }

        .header-logos {
            display: grid;
            grid-template-columns: 1fr auto 1fr;
            align-items: center;
            padding: 0 20px;
            width: 100%;
        }

        .header-logos > div:first-child {
            justify-self: start;
        }

        .header-logos > div:last-child {
            justify-self: end;
        }

        /* Contenedor de imagen redimensionable */
        .resizable-logo-container {
            width: 80px;
            height: 80px;
            position: relative;
            display: inline-block;
            cursor: pointer;
            overflow: visible;
        }

        .resizable-logo-container img {
            width: 100%;
            height: 100%;
            object-fit: contain;
            display: block;
        }

        /* Solo visible en pantalla para redimensionar */
        @media screen {
            .resizable-logo-container {
                resize: both;
                overflow: hidden;
                border: 1px dashed #ccc;
            }
            .resizable-logo-container:hover {
                border-color: #007bff;
            }
            .resizable-logo-container::after {
                content: "⇲";
                position: absolute;
                bottom: 0;
                right: 0;
                background: rgba(0,0,0,0.1);
                font-size: 10px;
                padding: 2px;
            }
        }

        .header-text {
            text-align: center;
            flex-grow: 1;
        }

        .header-info {
            display: flex;
            justify-content: space-between;
            padding: 0 10px;
            font-weight: bold;
            font-size: 0.85rem;
            margin-top: 5px;
        }

        .print-footer {
            width: 100%;
            display: flex;
            justify-content: flex-end;
            padding: 2px 10px;
            font-size: 0.65rem;
            font-weight: bold;
            margin-top: auto;
        }
        
        /* Ocultar elementos de UI al imprimir */
        @media print {
            .no-print { display: none !important; }
            .resizable-logo-container { border: none !important; resize: none !important; }
            .resizable-logo-container::after { display: none !important; }
        }

        .table-container {
            flex-grow: 1;
            width: 100%;
        }

        table {
            width: 100%;
            border-collapse: collapse !important;
            border: 1.2px solid black !important;
            table-layout: fixed;
        }

        th, td {
            border: 1px solid black !important;
            padding: 1px 2px !important;
            text-align: center;
            vertical-align: middle !important;
            color: black !important;
            overflow: hidden;
            word-wrap: break-word;
        }

        th {
            font-size: 0.65rem !important;
        }

        td {
            font-size: 0.75rem !important;
        }

        .header-row th {
            background-color: #f1f5f9 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 0.6rem !important;
        }

        .vertical-text {
            writing-mode: vertical-rl;
            transform: rotate(180deg);
            white-space: nowrap;
            padding: 5px 0 !important;
            font-size: 0.55rem !important;
        }

        .text-left { text-align: left !important; }
        .font-weight-bold { font-weight: bold !important; }

        .obs-column {
            font-size: 0.55rem !important;
            line-height: 1;
        }

        tr {
            height: 0.65cm !important;
        }

        .total-row {
            background-color: #f2f2f2 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            font-weight: bold;
        }

        .grand-total-row {
            background-color: #e2e2e2 !important;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            font-weight: bold;
        }
    </style>
</head>
<body>
    @php
        $grandTotals = [
            'acuerdo' => 0, 'contrato' => 0, 'm_general' => 0, 'm_especialista' => 0,
            'hrs_tadas' => 0, 'dia_cont' => 0, 'dia_cump' => 0, 'hr_cont' => 0,
            'hr_cump' => 0, 'prog' => 0, 'repr' => 0, 'atend' => 0, 'ofic' => 0,
            'vac' => 0, 'pers' => 0
        ];

        if ($jornada === 'TOTAL JORNADAS') {
            // Caso: Resumen de todas las jornadas
            $summaryData = [];
            foreach ($dataByJornada as $jorName => $jorData) {
                $jTotals = [
                    'acuerdo' => 0, 'contrato' => 0, 'm_general' => 0, 'm_especialista' => 0,
                    'hrs_tadas' => 0, 'dia_cont' => 0, 'dia_cump' => 0, 'hr_cont' => 0,
                    'hr_cump' => 0, 'prog' => 0, 'repr' => 0, 'atend' => 0, 'ofic' => 0,
                    'vac' => 0, 'pers' => 0
                ];
                foreach ($jorData as $row) {
                    $medRecord = $row['medico'];
                    $nom = strtoupper($medRecord->NOMINA ?? '');
                    $mod = strtoupper($medRecord->MODALIDAD ?? '');
                    $esp = trim(strtoupper($medRecord->ESPECIALIDAD ?? ''));

                    $isSS = (str_contains($nom, 'SOCIAL') || str_contains($mod, 'SOCIAL') || str_contains($esp, 'SOCIAL'));
                    $isAc = (!$isSS && (str_contains($nom, 'ACUERDO') || str_contains($nom, 'PERMANENTE') || str_contains($mod, 'ACUERDO') || str_contains($mod, 'PERMANENTE')));
                    $isCt = ($isSS || str_contains($nom, 'CONTRATO') || str_contains($nom, 'INTERINATO') || str_contains($mod, 'CONTRATO') || str_contains($mod, 'INTERINATO') || (!$isAc && ($nom != '' || $mod != '')));
                    $isEsp = ($esp !== '' && $esp !== 'MEDICO GENERAL' && $esp !== 'MÉDICO GENERAL' && !$isSS);
                    $isGen = !$isEsp;

                    if ($isAc) $jTotals['acuerdo']++;
                    if ($isCt) $jTotals['contrato']++;
                    if ($isGen) $jTotals['m_general']++;
                    if ($isEsp) $jTotals['m_especialista']++;

                    $jTotals['hrs_tadas'] += $row['horasPorDia'];
                    $jTotals['dia_cont'] += $row['diasContratados'];
                    $jTotals['dia_cump'] += $row['diasCumplidos'];
                    $jTotals['hr_cont'] += $row['horasContratadasMes'];
                    $jTotals['hr_cump'] += $row['horasCumplidas'];
                    $jTotals['prog'] += $row['prog'];
                    $jTotals['repr'] += $row['repr'];
                    $jTotals['atend'] += $row['atenciones'];
                    $jTotals['ofic'] += $row['totalOfic'];
                    $jTotals['vac'] += $row['totalVac'];
                    $jTotals['pers'] += $row['totalPers'];
                }
                foreach ($jTotals as $k => $v) $grandTotals[$k] += $v;
                $summaryData[] = ['name' => "TOTAL JORNADA $jorName", 'totals' => $jTotals];
            }
            $summaryData[] = ['name' => "TOTAL REGIONAL (TODAS LAS JORNADAS)", 'totals' => $grandTotals, 'isGrandTotal' => true];
            $chunks = [$summaryData]; 
        } else {
            // Calcular Gran Total primero
            foreach ($data as $row) {
                $medRecord = $row['medico'];
                $nom = strtoupper($medRecord->NOMINA ?? '');
                $mod = strtoupper($medRecord->MODALIDAD ?? '');
                $esp = trim(strtoupper($medRecord->ESPECIALIDAD ?? ''));

                $isSS = (str_contains($nom, 'SOCIAL') || str_contains($mod, 'SOCIAL') || str_contains($esp, 'SOCIAL'));
                $isAc = (!$isSS && (str_contains($nom, 'ACUERDO') || str_contains($nom, 'PERMANENTE') || str_contains($mod, 'ACUERDO') || str_contains($mod, 'PERMANENTE')));
                $isCt = ($isSS || str_contains($nom, 'CONTRATO') || str_contains($nom, 'INTERINATO') || str_contains($mod, 'CONTRATO') || str_contains($mod, 'INTERINATO') || (!$isAc && ($nom != '' || $mod != '')));
                $isEsp = ($esp !== '' && $esp !== 'MEDICO GENERAL' && $esp !== 'MÉDICO GENERAL' && !$isSS);
                $isGen = !$isEsp;

                if ($isAc) $grandTotals['acuerdo']++;
                if ($isCt) $grandTotals['contrato']++;
                if ($isGen) $grandTotals['m_general']++;
                if ($isEsp) $grandTotals['m_especialista']++;

                $grandTotals['hrs_tadas'] += $row['horasPorDia'];
                $grandTotals['dia_cont'] += $row['diasContratados'];
                $grandTotals['dia_cump'] += $row['diasCumplidos'];
                $grandTotals['hr_cont'] += $row['horasContratadasMes'];
                $grandTotals['hr_cump'] += $row['horasCumplidas'];
                $grandTotals['prog'] += $row['prog'];
                $grandTotals['repr'] += $row['repr'];
                $grandTotals['atend'] += $row['atenciones'];
                $grandTotals['ofic'] += $row['totalOfic'];
                $grandTotals['vac'] += $row['totalVac'];
                $grandTotals['pers'] += $row['totalPers'];
            }
            $rowsPerPage = 14;
            $chunks = array_chunk($data, $rowsPerPage);
        }
        $totalChunks = count($chunks);
        if ($totalChunks == 0) $chunks = [[]];
    @endphp

    @foreach($chunks as $pageIndex => $pageData)
        <div class="print-page">
            @php
                $pageSubtotals = [
                    'acuerdo' => 0, 'contrato' => 0, 'm_general' => 0, 'm_especialista' => 0,
                    'hrs_tadas' => 0, 'dia_cont' => 0, 'dia_cump' => 0, 'hr_cont' => 0,
                    'hr_cump' => 0, 'prog' => 0, 'repr' => 0, 'atend' => 0, 'ofic' => 0,
                    'vac' => 0, 'pers' => 0
                ];
                $isLastPage = ($pageIndex == $totalChunks - 1);
            @endphp
            <div class="official-header">
                <div class="header-logos">
                    <div class="resizable-logo-container" title="Doble clic para cambiar imagen" data-logo-name="logo_izquierdo"
                         style="width: <?php echo $settings['logo_izquierdo_width'] ?? '80px'; ?>; height: <?php echo $settings['logo_izquierdo_height'] ?? '80px'; ?>;">
                        <img src="{{ asset('img/logos/logo_izquierdo.png') }}" alt="Logo Izquierdo" id="img_logo_izquierdo">
                    </div>
                    <div class="header-text">
                        <h6 class="mb-0 font-weight-bold" style="font-size: 0.9rem;">REGION SANITARIA METROPOLITANA DISTRITO CENTRAL</h6>
                        <h6 class="mb-0 font-weight-bold" style="font-size: 0.9rem;">AREA DE GESTION A LA INFORMACION</h6>
                        <h6 class="mb-0 font-weight-bold" style="font-size: 0.9rem;">INFORME DE CONSULTAS BRINDADAS POR MEDICO</h6>
                    </div>
                    <div class="resizable-logo-container" title="Doble clic para cambiar imagen" data-logo-name="logo_derecho"
                         style="width: <?php echo $settings['logo_derecho_width'] ?? '80px'; ?>; height: <?php echo $settings['logo_derecho_height'] ?? '80px'; ?>;">
                        <img src="{{ asset('img/logos/logo_derecho.png') }}" alt="Logo Derecho" id="img_logo_derecho">
                    </div>
                </div>

                <div class="header-info">
                    <div style="width: 40%;">
                        <div>JORNADA: {{ $jornada }}</div>
                        <div style="font-size: 0.75rem;">CENTRO INTEGRAL DE SALUD SAN MIGUEL</div>
                    </div>
                    <div style="width: 30%; text-align: center;">MES: {{ $mesNombre }}</div>
                    <div style="width: 30%; text-align: center;">AÑO: {{ $ano }}</div>
                </div>
            </div>

            <div class="table-container">
                <table>
                    <thead>
                        <tr class="header-row" style="height: 25px;">
                            <th rowspan="2" style="width: 25px;">N°</th>
                            <th rowspan="2" style="width: 180px;">NOMBRE</th>
                            <th rowspan="2" class="vertical-text" style="width: 30px;">ACUERDO</th>
                            <th rowspan="2" class="vertical-text" style="width: 30px;">CONTRATO</th>
                            <th colspan="2" style="width: 60px; font-size: 0.55rem !important;">CATEGORIA</th>
                            <th rowspan="2" style="width: 35px; line-height: 1.1; padding: 2px 0px !important;">HRS<br>CONT.<br><small style="font-size: 0.4rem;">X DIA</small></th>
                            <th colspan="2">DIA MES</th>
                            <th colspan="2">HORAS MES</th>
                            <th colspan="3">CONSULTAS</th>
                            <th rowspan="2" style="width: 40px;">%</th>
                            <th colspan="3">HORA SIN CONSULTA</th>
                            <th rowspan="2" style="width: 200px;">OBSERVACIONES</th>
                        </tr>
                        <tr class="header-row" style="height: 35px;">
                            <th class="vertical-text" style="width: 30px;">MEDICO<br>GENERAL</th>
                            <th class="vertical-text" style="width: 30px;">MEDICO<br>ESPECIALISTA</th>
                            <th style="width: 45px;">CONT.</th>
                            <th style="width: 45px;">CUMP.</th>
                            <th style="width: 55px;">CONT.</th>
                            <th style="width: 55px;">CUMP.</th>
                            <th style="width: 55px;">PROG.</th>
                            <th style="width: 55px;">REPR.</th>
                            <th style="width: 55px;">ATEND</th>
                            <th style="width: 45px;">OFIC</th>
                            <th style="width: 45px;">VAC.</th>
                            <th style="width: 45px;">PERS</th>
                        </tr>
                    </thead>
                    <tbody>
                        @if($jornada === 'TOTAL JORNADAS')
                            @foreach($pageData as $idx => $row)
                                <tr class="{{ isset($row['isGrandTotal']) ? 'grand-total-row' : '' }}">
                                    <td>{{ $idx + 1 }}</td>
                                    <td class="text-left font-weight-bold" style="padding-left: 5px !important;">{{ $row['name'] }}</td>
                                    <td>{{ round($row['totals']['acuerdo']) }}</td>
                                    <td>{{ round($row['totals']['contrato']) }}</td>
                                    <td>{{ round($row['totals']['m_general']) }}</td>
                                    <td>{{ round($row['totals']['m_especialista']) }}</td>
                                    <td>{{ round($row['totals']['hrs_tadas']) }}</td>
                                    <td>{{ round($row['totals']['dia_cont']) }}</td>
                                    <td class="font-weight-bold">{{ round($row['totals']['dia_cump']) }}</td>
                                    <td>{{ round($row['totals']['hr_cont']) }}</td>
                                    <td class="font-weight-bold">{{ round($row['totals']['hr_cump']) }}</td>
                                    <td>{{ round($row['totals']['prog']) }}</td>
                                    <td class="font-weight-bold">{{ round($row['totals']['repr']) }}</td>
                                    <td class="font-weight-bold">{{ round($row['totals']['atend']) }}</td>
                                    <td class="font-weight-bold">
                                        @php $p = $row['totals']['repr'] > 0 ? ($row['totals']['atend'] / $row['totals']['repr'] * 100) : 0; @endphp
                                        {{ number_format($p, 0) }}%
                                    </td>
                                    <td class="font-weight-bold">{{ round($row['totals']['ofic']) > 0 ? round($row['totals']['ofic']) : '' }}</td>
                                    <td class="font-weight-bold">{{ round($row['totals']['vac']) > 0 ? round($row['totals']['vac']) : '' }}</td>
                                    <td class="font-weight-bold">{{ round($row['totals']['pers']) > 0 ? round($row['totals']['pers']) : '' }}</td>
                                    <td>Resumen jornada</td>
                                </tr>
                            @endforeach
                            @php $emptyRows = max(0, 14 - count($pageData)); @endphp
                            @for($i = 0; $i < $emptyRows; $i++)
                                <tr>
                                    <td>{{ count($pageData) + $i + 1 }}</td>
                                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                                </tr>
                            @endfor
                        @else
                            @foreach($pageData as $idx => $row)
                                @php
                                    $medicoRecord = $row['medico'];
                                    $nomina = strtoupper($medicoRecord->NOMINA ?? '');
                                    $modalidad = strtoupper($medicoRecord->MODALIDAD ?? '');
                                    $especialidad = trim(strtoupper($medicoRecord->ESPECIALIDAD ?? ''));
                                    $nombre = strtoupper($medicoRecord->NOM_MED ?? '');
                                    $obs = strtoupper($medicoRecord->observaciones ?? '');

                                    $isONG = (!empty($row['is_ong']) || !empty($medicoRecord->es_ong) || str_contains($modalidad, 'ONG') || str_contains($nomina, 'ONG') || str_contains($nombre, 'MEDICOS SIN FRONTERAS') || str_contains($nombre, 'ONG') || str_contains($obs, 'MEDICOS SIN FRONTERAS'));

                                    $isSS = (str_contains($nomina, 'SOCIAL') || str_contains($modalidad, 'SOCIAL') || str_contains($especialidad, 'SOCIAL'));
                                    $isAcuerdo = (!$isONG && !$isSS && (str_contains($nomina, 'ACUERDO') || str_contains($nomina, 'PERMANENTE') || str_contains($modalidad, 'ACUERDO') || str_contains($modalidad, 'PERMANENTE')));
                                    $isContrato = (!$isONG && ($isSS || str_contains($nomina, 'CONTRATO') || str_contains($nomina, 'INTERINATO') || str_contains($modalidad, 'CONTRATO') || str_contains($modalidad, 'INTERINATO') || (!$isAcuerdo && ($nomina != '' || $modalidad != ''))));

                                    $isEspecialista = (!$isONG && $especialidad !== '' && $especialidad !== 'MEDICO GENERAL' && $especialidad !== 'MÉDICO GENERAL' && !$isSS);
                                    $isGeneral = (!$isONG && !$isEspecialista);

                                    if ($isAcuerdo) $pageSubtotals['acuerdo']++;
                                    if ($isContrato) $pageSubtotals['contrato']++;
                                    if ($isGeneral) $pageSubtotals['m_general']++;
                                    if ($isEspecialista) $pageSubtotals['m_especialista']++;
                                    $pageSubtotals['hrs_tadas'] += $row['horasPorDia'];
                                    $pageSubtotals['dia_cont'] += $row['diasContratados'];
                                    $pageSubtotals['dia_cump'] += $row['diasCumplidos'];
                                    $pageSubtotals['hr_cont'] += $row['horasContratadasMes'];
                                    $pageSubtotals['hr_cump'] += $row['horasCumplidas'];
                                    $pageSubtotals['prog'] += $row['prog'];
                                    $pageSubtotals['repr'] += $row['repr'];
                                    $pageSubtotals['atend'] += $row['atenciones'];
                                    $pageSubtotals['ofic'] += $row['totalOfic'];
                                    $pageSubtotals['vac'] += $row['totalVac'];
                                    $pageSubtotals['pers'] += $row['totalPers'];

                                    $nombreLimpio = trim($medicoRecord->NOM_MED);
                                    $prefijos = ['DR. ', 'DRA. ', 'DR ', 'DRA ', 'G.O. ', 'G.O ', 'GO. ', 'GO '];
                                    $cambio = true;
                                    while ($cambio) {
                                        $cambio = false;
                                        foreach ($prefijos as $prefijo) {
                                            if (str_starts_with(strtoupper($nombreLimpio), $prefijo)) {
                                                $nombreLimpio = trim(substr($nombreLimpio, strlen($prefijo)));
                                                $cambio = true; break;
                                            }
                                        }
                                    }
                                @endphp
                                <tr>
                                    <td>{{ ($pageIndex * $rowsPerPage) + $idx + 1 }}</td>
                                    <td class="text-left" style="padding-left: 5px !important;">{{ $nombreLimpio }}</td>
                                    <td>{{ $isAcuerdo ? 'X' : '' }}</td>
                                    <td>{{ $isContrato ? ($isSS ? 'SS' : 'X') : '' }}</td>
                                    <td>{{ $isGeneral ? ($isSS ? 'SS' : 'X') : '' }}</td>
                                    <td>{{ $isEspecialista ? 'X' : '' }}</td>
                                    <td>{{ $isONG ? '' : ($row['horasPorDia'] > 0 ? (round($row['horasPorDia']) == $row['horasPorDia'] ? round($row['horasPorDia']) : number_format($row['horasPorDia'], 1)) : '') }}</td>
                                    <td>{{ $isONG ? '' : ($row['diasContratados'] > 0 ? round($row['diasContratados']) : '') }}</td>
                                    <td>{{ $isONG ? '' : ($row['diasCumplidos'] > 0 ? round($row['diasCumplidos']) : '') }}</td>
                                    <td>{{ $isONG ? '' : ($row['horasContratadasMes'] > 0 ? round($row['horasContratadasMes']) : '') }}</td>
                                    <td>{{ $isONG ? '' : ($row['horasCumplidas'] > 0 ? round($row['horasCumplidas']) : '') }}</td>
                                    <td>{{ $isONG ? '' : ($row['prog'] > 0 ? round($row['prog']) : '') }}</td>
                                    <td class="font-weight-bold">{{ $isONG ? '' : ($row['repr'] > 0 ? round($row['repr']) : '') }}</td>
                                    <td class="font-weight-bold">{{ round($row['atenciones']) }}</td>
                                    <td>{{ $isONG ? '' : ($row['rendimiento'] > 0 ? round($row['rendimiento']) . '%' : '') }}</td>
                                    <td>{{ round($row['totalOfic']) > 0 ? round($row['totalOfic']) : '' }}</td>
                                    <td>{{ round($row['totalVac']) > 0 ? round($row['totalVac']) : '' }}</td>
                                    <td>{{ round($row['totalPers']) > 0 ? round($row['totalPers']) : '' }}</td>
                                    <td class="text-left obs-column" style="padding-left: 5px !important;">
                                        @php
                                            $obsMedico = trim($row['medico']->observaciones ?? '');
                                            $obsHsc = isset($row['hsc']) ? trim($row['hsc']->observaciones ?? '') : '';
                                            $allObs = $obsMedico . (!empty($obsMedico) && !empty($obsHsc) ? ', ' : '') . $obsHsc;
                                            if ($isONG && empty($allObs)) {
                                                $allObs = 'INSTITUCION / PROGRAMA ONG';
                                            }
                                        @endphp
                                        {{ $allObs }}
                                    </td>
                                </tr>
                            @endforeach

                            @php $emptyRows = max(0, $rowsPerPage - count($pageData)); @endphp
                            @for($i = 0; $i < $emptyRows; $i++)
                                <tr>
                                    <td>{{ ($pageIndex * $rowsPerPage) + count($pageData) + $i + 1 }}</td>
                                    <td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td><td></td>
                                </tr>
                            @endfor

                            <!-- Fila de SUBTOTALES (Se muestra al final de cada hoja) -->
                            <tr class="total-row">
                                <td colspan="2" class="text-right pr-2 font-weight-bold">SUBTOTAL</td>
                                <td>{{ round($pageSubtotals['acuerdo']) }}</td>
                                <td>{{ round($pageSubtotals['contrato']) }}</td>
                                <td>{{ round($pageSubtotals['m_general']) }}</td>
                                <td>{{ round($pageSubtotals['m_especialista']) }}</td>
                                <td>{{ round($pageSubtotals['hrs_tadas']) }}</td>
                                <td>{{ round($pageSubtotals['dia_cont']) }}</td>
                                <td class="font-weight-bold">{{ round($pageSubtotals['dia_cump']) }}</td>
                                <td>{{ round($pageSubtotals['hr_cont']) }}</td>
                                <td class="font-weight-bold">{{ round($pageSubtotals['hr_cump']) }}</td>
                                <td>{{ round($pageSubtotals['prog']) }}</td>
                                <td class="font-weight-bold">{{ round($pageSubtotals['repr']) }}</td>
                                <td class="font-weight-bold">{{ round($pageSubtotals['atend']) }}</td>
                                <td class="font-weight-bold">
                                    @php $p = $pageSubtotals['repr'] > 0 ? ($pageSubtotals['atend'] / $pageSubtotals['repr'] * 100) : 0; @endphp
                                    {{ number_format($p, 0) }}%
                                </td>
                                <td class="font-weight-bold">{{ round($pageSubtotals['ofic']) > 0 ? round($pageSubtotals['ofic']) : '' }}</td>
                                <td class="font-weight-bold">{{ round($pageSubtotals['vac']) > 0 ? round($pageSubtotals['vac']) : '' }}</td>
                                <td class="font-weight-bold">{{ round($pageSubtotals['pers']) > 0 ? round($pageSubtotals['pers']) : '' }}</td>
                                <td>Subtotal hoja</td>
                            </tr>

                            <!-- Fila de TOTAL JORNADA (Solo en la última página, debajo de SUBTOTAL) -->
                            @if($isLastPage)
                                <tr class="grand-total-row">
                                    <td colspan="2" class="text-right pr-2 font-weight-bold">TOTAL JORNADA {{ $jornada }}</td>
                                    <td>{{ round($grandTotals['acuerdo']) }}</td>
                                    <td>{{ round($grandTotals['contrato']) }}</td>
                                    <td>{{ round($grandTotals['m_general']) }}</td>
                                    <td>{{ round($grandTotals['m_especialista']) }}</td>
                                    <td>{{ round($grandTotals['hrs_tadas']) }}</td>
                                    <td>{{ round($grandTotals['dia_cont']) }}</td>
                                    <td class="font-weight-bold">{{ round($grandTotals['dia_cump']) }}</td>
                                    <td>{{ round($grandTotals['hr_cont']) }}</td>
                                    <td class="font-weight-bold">{{ round($grandTotals['hr_cump']) }}</td>
                                    <td>{{ round($grandTotals['prog']) }}</td>
                                    <td class="font-weight-bold">{{ round($grandTotals['repr']) }}</td>
                                    <td class="font-weight-bold">{{ round($grandTotals['atend']) }}</td>
                                    <td class="font-weight-bold">
                                        @php $gp = $grandTotals['repr'] > 0 ? ($grandTotals['atend'] / $grandTotals['repr'] * 100) : 0; @endphp
                                        {{ number_format($gp, 0) }}%
                                    </td>
                                    <td class="font-weight-bold">{{ round($grandTotals['ofic']) }}</td>
                                    <td class="font-weight-bold">{{ round($grandTotals['vac']) }}</td>
                                    <td class="font-weight-bold">{{ round($grandTotals['pers']) }}</td>
                                    <td>Total general</td>
                                </tr>
                            @endif
                        @endif
                    </tbody>
                </table>
            </div>

            <div class="print-footer">
            </div>
        </div>
    @endforeach

    <!-- PAGINA DE OBSERVACIONES Y RENDIMIENTO MEDICO OFICIAL -->
    @php
        $obsDoctors = [];
        if ($jornada === 'TOTAL JORNADAS') {
            foreach ($dataByJornada as $jData) {
                foreach ($jData as $row) {
                    $obsDoctors[] = $row;
                }
            }
        } else {
            $obsDoctors = $data;
        }
    @endphp

    <div class="print-page">
        <div class="official-header text-center pt-1">
            <div class="header-logos">
                <div class="resizable-logo-container" data-logo-name="logo_izquierdo" style="width: {{ $settings['logo_izquierdo_width'] ?? '75px' }}; height: {{ $settings['logo_izquierdo_height'] ?? '75px' }};">
                    <img src="{{ asset('img/logos/logo_izquierdo.png') }}" alt="Logo Izquierdo">
                </div>
                <div class="header-text">
                    <h5 class="mb-0 font-weight-bold" style="font-size: 1.05rem; letter-spacing: 0.5px;">SECRETARIA DE SALUD</h5>
                    <h6 class="mb-0 font-weight-bold" style="font-size: 0.95rem;">REGIÓN SANITARIA METROPOLITANA DEL DISTRITO CENTRAL</h6>
                    <h6 class="mb-0 font-weight-bold" style="font-size: 0.95rem;">UNIDAD DE PLANEAMIENTO / ÁREA DE GESTIÓN DE LA INFORMACIÓN</h6>
                    <h6 class="mb-0 font-weight-bold text-decoration-underline" style="font-size: 0.95rem; margin-top: 2px;">RENDIMIENTO MEDICO</h6>
                </div>
                <div class="resizable-logo-container" data-logo-name="logo_derecho" style="width: {{ $settings['logo_derecho_width'] ?? '75px' }}; height: {{ $settings['logo_derecho_height'] ?? '75px' }};">
                    <img src="{{ asset('img/logos/logo_derecho.png') }}" alt="Logo Derecho">
                </div>
            </div>
            <div class="header-info px-2 mt-2 font-weight-bold" style="display: flex; justify-content: space-between; font-size: 0.78rem;">
                <div>ESTABLECIMIENTO DE SALUD: <span style="border-bottom: 1px solid black; padding: 0 5px;">CENTRO INTEGRAL DE SALUD SAN MIGUEL</span></div>
                <div>JORNADA: <span style="border-bottom: 1px solid black; padding: 0 5px;">{{ $jornada }}</span></div>
                <div>MES: <span style="border-bottom: 1px solid black; padding: 0 5px;">{{ $mesNombre }}</span></div>
                <div>AÑO: <span style="border-bottom: 1px solid black; padding: 0 5px;">{{ $ano }}</span></div>
                <div>FIRMA Y SELLO: ____________________</div>
            </div>
        </div>

        <!-- Tabla Observaciones -->
        <div class="table-container mt-2">
            <table style="width: 100%; border: 1.5px solid black !important;">
                <thead>
                    <tr class="header-row" style="height: 28px; background-color: #f1f5f9;">
                        <th style="width: 45px; border: 1.5px solid black !important; font-size: 0.75rem !important;">N°</th>
                        <th style="width: 320px; border: 1.5px solid black !important; font-size: 0.75rem !important; text-align: left; padding-left: 8px !important;">NOMBRE COMPLETO DEL MEDICO</th>
                        <th style="border: 1.5px solid black !important; font-size: 0.75rem !important; text-align: center;">OBSERVACIONES</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($obsDoctors as $idx => $row)
                        @php
                            $m = $row['medico'];
                            $hsc = $row['hsc'];
                            $obsTexto = $hsc ? $hsc->observaciones : ($m->observaciones ?? '');
                        @endphp
                        <tr style="height: 0.65cm !important;">
                            <td style="border: 1px solid black !important; font-size: 0.75rem !important; text-align: center;">{{ $idx + 1 }}</td>
                            <td style="border: 1px solid black !important; font-size: 0.75rem !important; text-align: left !important; padding-left: 8px !important;" class="font-weight-bold">
                                {{ $m->NOM_MED }}
                                @if(!empty($m->es_director))
                                    <span style="font-size: 0.62rem; color: #1e40af; font-weight: normal;"> (DIRECTOR DEL ESTABLECIMIENTO)</span>
                                @endif
                            </td>
                            <td style="border: 1px solid black !important; font-size: 0.72rem !important; text-align: left !important; padding-left: 8px !important;">
                                {{ $obsTexto }}
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <!-- Notas al pie oficiales -->
        <div class="official-notes mt-auto pt-2 text-left" style="font-size: 0.68rem; font-weight: bold; border-top: 1px solid black; margin-top: 15px;">
            <div style="display: flex; justify-content: space-between; gap: 20px;">
                <div style="flex: 1;">
                    <div>*** ESTE INFORME DEBE COINCIDIR CON EL TOTAL DE ATENCIONES DEL AT2R.</div>
                    <div>*** EL ORDEN DE LOS MEDICOS DEBE SER IGUAL AL DE LOS MESES ANTERIORES.</div>
                    <div>*** EN LA PRIMERA CASILLA COLOCAR SIEMPRE EL NOMBRE COMPLETO DEL DIRECTOR DEL ESTABLECIMIENTO DE SALUD.</div>
                </div>
                <div style="flex: 1;">
                    <div>*** COLOCAR EL PERSONAL QUE ESTE DE VACACIONES O INCAPACITADO (DE LO CONTRARIO SE REPORTARA COMO FALTANTE).</div>
                    <div>*** COLOCAR FECHA DE INICIO Y DE FINAL DE CADA MEDICO EN SERVICIO SOCIAL.</div>
                    <div>*** LLENAR UNA HOJA POR JORNADA (MATUTINA, VESPERTINA, FIN DE SEMANA Y SERVICIO SOCIAL).</div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            // Manejo de carga de imágenes por doble clic
            $('.resizable-logo-container').on('dblclick', function() {
                const container = $(this);
                const logoName = container.data('logo-name');
                const imgElement = container.find('img');
                
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
                                    // Actualizar todas las instancias de este logo en todas las páginas
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

            // Guardar tamaño después de redimensionar
            let resizeTimer;
            $('.resizable-logo-container').on('mouseup', function() {
                const container = $(this);
                const logoName = container.data('logo-name');
                const width = container.css('width');
                const height = container.css('height');

                // Usar un pequeño delay para evitar múltiples llamadas si hay clics rápidos
                clearTimeout(resizeTimer);
                resizeTimer = setTimeout(() => {
                    // Actualizar todas las instancias visualmente
                    $(`[data-logo-name="${logoName}"]`).css({
                        'width': width,
                        'height': height
                    });

                    // Guardar en base de datos
                    saveSetting(logoName + '_width', width);
                    saveSetting(logoName + '_height', height);
                }, 500);
            });

            function saveSetting(key, value) {
                $.post('{{ route("informes.hora-medico.save-setting") }}', {
                    _token: '{{ csrf_token() }}',
                    key: key,
                    value: value
                });
            }
        });

        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>
