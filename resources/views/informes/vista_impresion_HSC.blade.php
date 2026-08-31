<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Informe de Observaciones - {{ $mesNombre }} {{ $ano }}</title>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
    <style>
        /* ─── Configuración de Página: Ajustado al Área de Impresión (Landscape / Oficio) ─── */
        @page {
            size: landscape;
            margin: 2mm 2mm 2mm 2mm;
        }

        * {
            box-sizing: border-box;
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            font-family: Arial, Helvetica, sans-serif !important;
        }

        html,
        body {
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

            html,
            body {
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
            margin-bottom: 5px;
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

        /* ─── Tabla Oficial de Observaciones ─── */
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
            vertical-align: middle !important;
            color: #000000 !important;
            padding: 0 4px !important;
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
            text-align: center;
            height: 24px !important;
            font-size: 0.68rem !important;
            letter-spacing: 0.3px;
        }

        /* Bordes Gruesos Oficiales */
        .b-thick-r {
            border-right: 2.5px solid #000000 !important;
        }

        table.table-oficial thead {
            border-bottom: 2.5px solid #000000 !important;
        }

        /* Filas del Cuerpo (Altura Exacta a Matriz de 24 Filas) */
        table.table-oficial tbody tr {
            height: 22px !important;
            max-height: 23px !important;
        }

        table.table-oficial tbody td {
            font-size: 0.74rem !important;
            font-weight: normal !important;
            line-height: 1 !important;
            padding: 0 4px !important;
        }

        table.table-oficial tbody td.col-num {
            text-align: center !important;
            font-size: 0.74rem !important;
            font-weight: normal !important;
        }

        table.table-oficial tbody td.col-name {
            text-align: left !important;
            padding-left: 8px !important;
            padding-right: 4px !important;
            font-size: 0.73rem !important;
            font-weight: normal !important;
            text-transform: uppercase;
        }

        table.table-oficial tbody td.col-obs {
            text-align: left !important;
            padding-left: 8px !important;
            padding-right: 4px !important;
            font-size: 0.66rem !important;
            font-weight: normal !important;
            line-height: 1.15 !important;
            white-space: normal !important;
            word-break: break-word !important;
            overflow-wrap: anywhere !important;
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
            padding-top: 3px !important;
            margin-top: 2px !important;
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

        .btn-ctrl-primary:hover {
            background: #16a34a;
        }

        .btn-ctrl-subtle {
            background: rgba(255, 255, 255, 0.15);
            color: #ffffff;
        }

        .btn-ctrl-subtle:hover {
            background: rgba(255, 255, 255, 0.25);
        }
    </style>
</head>

<body>

    <!-- Controles Flotantes para Pantalla -->
    <div class="floating-print-controls no-print">
        <button type="button" class="btn-ctrl btn-ctrl-primary" onclick="window.print()">
            <i class="fas fa-print"></i> Imprimir Observaciones (Oficio Horizontal)
        </button>
        <button type="button" class="btn-ctrl btn-ctrl-subtle" onclick="toggleLogoDerecho()"
            title="Mostrar u ocultar logo derecho si se requiere">
            <i class="fas fa-image"></i> <span
                id="btn-toggle-logo-txt">{{ !empty($settings['mostrar_logo_derecho']) ? 'Ocultar Logo Der.' : 'Mostrar Logo Der.' }}</span>
        </button>
        <button type="button" class="btn-ctrl btn-ctrl-subtle" onclick="editarCentroSalud()"
            title="Cambiar nombre del centro de salud">
            <i class="fas fa-clinic-medical"></i> Editar Establecimiento
        </button>
        <button type="button" class="btn-ctrl btn-ctrl-subtle" onclick="window.close(); history.back();">
            <i class="fas fa-times"></i> Volver
        </button>
    </div>

    @php
        // Función auxiliar para extraer prefijos médicos
        function limpiarPrefijoMedico($nombre)
        {
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
            $MAX_ROWS = 24;
            $docCount = count($doctorList);
        @endphp

        <div class="print-sheet">
            <!-- ── 1. Encabezado Oficial ── -->
            <div class="print-header">
                <div class="header-grid">
                    <!-- Área Izquierda: Logo Oficial Izquierdo -->
                    <div style="text-align: left; padding-left: 5px;">
                        <div id="box-logo-izquierdo" class="resizable-logo-container" data-logo-name="logo_izquierdo"
                            title="Doble clic para cambiar logo"
                            style="width: {{ $settings['logo_izquierdo_width'] ?? '145px' }}; height: {{ $settings['logo_izquierdo_height'] ?? '44px' }};">
                            <img src="{{ asset('img/logos/logo_izquierdo.png') }}" alt="Logo Izquierdo"
                                id="img_logo_izquierdo" style="max-width: 100%; max-height: 100%; object-fit: contain;">
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
                        <div id="box-logo-derecho" class="resizable-logo-container" data-logo-name="logo_derecho"
                            title="Doble clic para cambiar logo"
                            style="display: {{ $mostrarLogoDer ? 'inline-block' : 'none' }}; margin: 0 auto; width: {{ $settings['logo_derecho_width'] ?? '65px' }}; height: {{ $settings['logo_derecho_height'] ?? '65px' }};">
                            <img src="{{ asset('img/logos/logo_derecho.png') }}" alt="Logo Derecho" id="img_logo_derecho"
                                style="max-width: 100%; max-height: 100%; object-fit: contain;">
                        </div>
                        <div class="signature-box"
                            style="width: 175px; margin: 0 auto; padding-top: {{ $mostrarLogoDer ? '4px' : '36px' }};">
                            <div style="border-top: 1.8px solid #000; width: 100%;"></div>
                            <div style="font-size: 0.62rem; font-weight: bold; letter-spacing: 0.5px; padding-top: 3px;">
                                FIRMA Y SELLO</div>
                        </div>
                    </div>
                </div>

                <!-- Barra de Metadatos Oficiales -->
                <div class="header-info-bar">
                    <div>ESTABLECIMIENTO DE SALUD: <span class="val"
                            id="lbl-establecimiento">{{ $nombreEstablecimiento }}</span></div>
                    <div>JORNADA: <span class="val">{{ $currentJornada }}</span></div>
                    <div>MES: <span class="val">{{ $mesNombre }}</span></div>
                    <div>AÑO: <span class="val">{{ $ano }}</span></div>
                </div>
            </div>

            <!-- ── 2. Tabla Oficial de Observaciones (24 Filas Exactas a Matriz) ── -->
            <div class="table-wrap">
                <table class="table-oficial">
                    <thead>
                        <tr>
                            <th class="b-thick-r" style="width: 3.5%;">N°</th>
                            <th class="b-thick-r" style="width: 30.5%; text-align: left; padding-left: 8px !important;">
                                NOMBRE COMPLETO DEL MEDICO</th>
                            <th style="width: 66.0%; text-align: left; padding-left: 8px !important;">OBSERVACIONES</th>
                        </tr>
                    </thead>
                    <tbody>
                        @for($i = 0; $i < $MAX_ROWS; $i++)
                            @php
                                $row = $doctorList[$i] ?? null;
                            @endphp

                            @if($row)
                                @php
                                    $medico = $row['medico'];
                                    $hsc = $row['hsc'] ?? null;
                                    $obsMedico = trim($medico->observaciones ?? '');
                                    $obsHsc = $hsc ? trim($hsc->observaciones ?? '') : '';
                                    if ($obsMedico && $obsHsc) {
                                        $obsTexto = $obsMedico . ', ' . $obsHsc;
                                    } elseif ($obsMedico) {
                                        $obsTexto = $obsMedico;
                                    } else {
                                        $obsTexto = $obsHsc;
                                    }
                                    $nombreLimpio = limpiarPrefijoMedico($medico->NOM_MED);
                                @endphp
                                <tr>
                                    <td class="col-num b-thick-r">{{ $i + 1 }}</td>
                                    <td class="col-name b-thick-r">{{ $nombreLimpio }}</td>
                                    <td class="col-obs">{{ $obsTexto }}</td>
                                </tr>
                            @else
                                <tr>
                                    <td class="col-num b-thick-r">{{ $i + 1 }}</td>
                                    <td class="col-name b-thick-r">&nbsp;</td>
                                    <td class="col-obs">&nbsp;</td>
                                </tr>
                            @endif
                        @endfor
                    </tbody>
                </table>
            </div>

            <!-- ── 3. Notas Oficiales al Pie ── -->
            <div class="print-footer-notes">
                <div class="notes-col text-left">
                    <div>*** ESTE INFORME DEBE COINCIDIR CON EL TOTAL DE ATENCIONES DEL AT2R.</div>
                    <div>*** EL ORDEN DE LOS MEDICOS DEBE SER IGUAL AL DE LOS MESES ANTERIORES.</div>
                    <div>*** EN LA PRIMERA CASILLA COLOCAR SIEMPRE EL NOMBRE COMPLETO DEL DIRECTOR DEL ESTABLECIMIENTO DE
                        SALUD.</div>
                </div>
                <div class="notes-col text-left">
                    <div>*** COLOCAR EL PERSONAL QUE ESTE DE VACACIONES O INCAPACITADO (DE LO CONTRARIO SE REPORTARA COMO
                        FALTANTE).</div>
                    <div>*** COLOCAR FECHA DE INICIO Y DE FINAL DE CADA MEDICO EN SERVICIO SOCIAL.</div>
                    <div>*** LLENAR UNA HOJA POR JORNADA (MATUTINA, VESPERTINA, FIN DE SEMANA Y SERVICIO SOCIAL).</div>
                </div>
            </div>
        </div>
    @endforeach

    <script>
        // Alternar visualización del logo derecho
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

        $(document).ready(function () {
            // Manejo de carga de imágenes por doble clic
            $('.resizable-logo-container').on('dblclick', function () {
                const container = $(this);
                const logoName = container.data('logo-name');

                const input = $('<input type="file" accept="image/*" style="display:none;">');
                input.on('change', function (e) {
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
                            success: function (response) {
                                if (response.success) {
                                    $(`[data-logo-name="${logoName}"] img`).attr('src', response.url + '?t=' + new Date().getTime());
                                }
                            },
                            error: function () {
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