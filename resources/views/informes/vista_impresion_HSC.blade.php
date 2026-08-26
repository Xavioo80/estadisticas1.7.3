<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe de Observaciones - {{ $mes }} {{ $ano }}</title>
    <style>
        @page {
            size: portrait;
            margin: 10mm;
        }
        body {
            font-family: Arial, Helvetica, sans-serif;
            margin: 0;
            padding: 0;
            background-color: white;
            color: black;
            font-size: 11px;
        }
        .header-title {
            text-align: center;
            font-weight: bold;
            text-transform: uppercase;
            margin-bottom: 15px;
            line-height: 1.3;
        }
        .header-title h3 {
            margin: 0 0 4px 0;
            font-size: 14px;
            font-weight: 800;
        }
        .header-title h4 {
            margin: 0 0 4px 0;
            font-size: 12px;
            font-weight: 700;
        }
        .header-title h5 {
            margin: 0 0 6px 0;
            font-size: 11px;
            font-weight: 600;
        }
        .underline-box {
            display: inline-block;
            border-bottom: 2px solid black;
            padding-bottom: 2px;
            padding-left: 15px;
            padding-right: 15px;
        }
        .meta-bar {
            width: 100%;
            margin-bottom: 12px;
            font-weight: bold;
            text-transform: uppercase;
            font-size: 11px;
        }
        .meta-bar table {
            width: 100%;
            border: none;
        }
        .meta-bar td {
            border: none;
            padding: 2px 5px;
        }
        .field-val {
            border-bottom: 1px solid black;
            padding: 0 5px;
            display: inline-block;
            min-width: 120px;
        }
        .table-report {
            width: 100%;
            border-collapse: collapse;
            border: 2px solid black;
            margin-bottom: 15px;
        }
        .table-report th, .table-report td {
            border: 1px solid black;
            padding: 4px 6px;
            vertical-align: middle;
        }
        .table-report th {
            background-color: #f2f2f2;
            font-size: 11px;
            font-weight: bold;
            text-transform: uppercase;
            text-align: center;
        }
        .col-num {
            width: 30px;
            text-align: center;
            font-weight: bold;
        }
        .col-name {
            width: 280px;
            text-align: left;
            font-weight: bold;
            text-transform: uppercase;
        }
        .col-obs {
            text-align: left;
        }
        .footer-notes {
            margin-top: 15px;
            font-size: 9px;
            font-weight: bold;
            line-height: 1.4;
            display: flex;
            justify-content: space-between;
        }
        .footer-col {
            width: 48%;
        }
        @media print {
            .no-print { display: none; }
            body { margin: 0; }
        }
    </style>
</head>
<body>
    <!-- Encabezado Oficial del Formato -->
    <div class="header-title">
        <h3>SECRETARIA DE SALUD</h3>
        <h4>REGIÓN SANITARIA METROPOLITANA DEL DISTRITO CENTRAL</h4>
        <h5>UNIDAD DE PLANEAMIENTO / ÁREA DE GESTIÓN DE LA INFORMACIÓN</h5>
        <div class="underline-box">
            <h4 style="margin: 0;">RENDIMIENTO MEDICO</h4>
        </div>
    </div>

    <!-- Barra de Metadatos -->
    <div class="meta-bar">
        <table>
            <tr>
                <td style="width: 35%;">ESTABLECIMIENTO DE SALUD: <span class="field-val">{{ $settings['nombre_establecimiento'] ?? 'CENTRO DE SALUD' }}</span></td>
                <td style="width: 25%;">JORNADA: <span class="field-val">{{ $jornada }}</span></td>
                <td style="width: 20%;">MES: <span class="field-val">{{ $mes }}</span></td>
                <td style="width: 20%;">AÑO: <span class="field-val">{{ $ano }}</span></td>
            </tr>
        </table>
    </div>

    <!-- Tabla Oficial de Observaciones (Mismo Orden Histórico) -->
    <table class="table-report">
        <thead>
            <tr>
                <th class="col-num">N°</th>
                <th class="col-name">NOMBRE COMPLETO DEL MEDICO</th>
                <th class="col-obs">OBSERVACIONES</th>
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
                    $obsTexto = $hsc ? ($hsc->observaciones ?? '') : '';
                @endphp
                <tr>
                    <td class="col-num">{{ $index + 1 }}</td>
                    <td class="col-name">{{ $medico->NOM_MED }}</td>
                    <td class="col-obs">{{ $obsTexto }}</td>
                </tr>
            @endforeach

            @for($i = $rowsCount + 1; $i <= $minRows; $i++)
                <tr>
                    <td class="col-num">{{ $i }}</td>
                    <td>&nbsp;</td>
                    <td>&nbsp;</td>
                </tr>
            @endfor
        </tbody>
    </table>

    <!-- Pie de Página Oficial (Notas Explicativas) -->
    <div class="footer-notes">
        <div class="footer-col">
            <div>*** ESTE INFORME DEBE COINCIDIR CON EL TOTAL DE ATENCIONES DEL AT2R.</div>
            <div>*** EL ORDEN DE LOS MEDICOS DEBE SER IGUAL AL DE LOS MESES ANTERIORES.</div>
            <div>*** EN LA PRIMERA CASILLA COLOCAR SIEMPRE EL NOMBRE COMPLETO DEL DIRECTOR DEL ESTABLECIMIENTO DE SALUD.</div>
        </div>
        <div class="footer-col">
            <div>*** COLOCAR EL PERSONAL QUE ESTE DE VACACIONES O INCAPACITADO (DE LO CONTRARIO SE REPORTARA COMO FALTANTE).</div>
            <div>*** COLOCAR FECHA DE INICIO Y DE FINAL DE CADA MEDICO EN SERVICIO SOCIAL.</div>
            <div>*** LLENAR UNA HOJA POR JORNADA (MATUTINA, VESPERTINA, FIN DE SEMANA Y SERVICIO SOCIAL).</div>
        </div>
    </div>
</body>
</html>