@if(isset($dataByJornada) && $jornada === 'TOTAL JORNADAS')
    @php
        $grandTotals = [
            'acuerdo' => 0, 'contrato' => 0, 'm_general' => 0, 'm_especialista' => 0,
            'hrs_tadas' => 0, 'dia_cont' => 0, 'dia_cump' => 0, 'hr_cont' => 0, 'hr_cump' => 0,
            'prog' => 0, 'repr' => 0, 'atend' => 0,
            'hsc_comp' => 0, 'hsc_esfam' => 0, 'hsc_prom' => 0, 'hsc_cong' => 0,
            'hsc_campo' => 0, 'hsc_asam' => 0, 'hsc_citas' => 0,
            'hsc_ord' => 0, 'hsc_profil' => 0, 'hsc_pers' => 0
        ];
    @endphp
    <tbody>
        @foreach($dataByJornada as $jorName => $jorData)
            @php
                $jTotals = [
                    'acuerdo' => 0, 'contrato' => 0, 'm_general' => 0, 'm_especialista' => 0,
                    'hrs_tadas' => 0, 'dia_cont' => 0, 'dia_cump' => 0, 'hr_cont' => 0, 'hr_cump' => 0,
                    'prog' => 0, 'repr' => 0, 'atend' => 0,
                    'hsc_comp' => 0, 'hsc_esfam' => 0, 'hsc_prom' => 0, 'hsc_cong' => 0,
                    'hsc_campo' => 0, 'hsc_asam' => 0, 'hsc_citas' => 0,
                    'hsc_ord' => 0, 'hsc_profil' => 0, 'hsc_pers' => 0
                ];
                foreach ($jorData as $row) {
                    $medicoRecord = $row['medico'];
                    $nomina = strtoupper($medicoRecord->NOMINA ?? '');
                    $modalidad = strtoupper($medicoRecord->MODALIDAD ?? '');
                    $especialidad = trim(strtoupper($medicoRecord->ESPECIALIDAD ?? ''));

                    $isSS = (str_contains($nomina, 'SOCIAL') || str_contains($modalidad, 'SOCIAL') || str_contains($especialidad, 'SOCIAL'));
                    $isAcuerdo = (!$isSS && (str_contains($nomina, 'ACUERDO') || str_contains($nomina, 'PERMANENTE') || str_contains($modalidad, 'ACUERDO') || str_contains($modalidad, 'PERMANENTE')));
                    $isContrato = ($isSS || str_contains($nomina, 'CONTRATO') || str_contains($nomina, 'INTERINATO') || str_contains($modalidad, 'CONTRATO') || str_contains($modalidad, 'INTERINATO') || (!$isAcuerdo && ($nomina != '' || $modalidad != '')));

                    $isEspecialista = ($especialidad !== '' && $especialidad !== 'MEDICO GENERAL' && $especialidad !== 'MÉDICO GENERAL' && !$isSS);
                    $isGeneral = !$isEspecialista;

                    if ($isAcuerdo)
                        $jTotals['acuerdo']++;
                    if ($isContrato)
                        $jTotals['contrato']++;
                    if ($isGeneral)
                        $jTotals['m_general']++;
                    if ($isEspecialista)
                        $jTotals['m_especialista']++;

                    $jTotals['hrs_tadas'] += $row['horasPorDia'];
                    $jTotals['dia_cont'] += $row['diasContratados'];
                    $jTotals['dia_cump'] += $row['diasCumplidos'];
                    $jTotals['hr_cont'] += $row['horasContratadasMes'];
                    $jTotals['hr_cump'] += $row['horasCumplidas'];
                    $jTotals['prog'] += $row['prog'];
                    $jTotals['repr'] += $row['repr'];
                    $jTotals['atend'] += $row['atenciones'];
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

                foreach ($jTotals as $k => $v)
                    $grandTotals[$k] += $v;
            @endphp
            <tr>
                <td class="border-black sticky-col" style="left: 0; z-index: 40; !important;">
                    {{ $loop->iteration }}</td>
                <td class="border-black text-left font-weight-bold sticky-col"
                    style="padding-left: 10px; left: 40px; z-index: 40; !important;">TOTAL JORNADA
                    {{ $jorName }}</td>
                <td class="border-black">{{ round($jTotals['acuerdo']) }}</td>
                <td class="border-black">{{ round($jTotals['contrato']) }}</td>
                <td class="border-black">{{ round($jTotals['m_general']) }}</td>
                <td class="border-black">{{ round($jTotals['m_especialista']) }}</td>
                <td class="border-black">{{ round($jTotals['hrs_tadas']) }}</td>
                <td class="border-black">{{ round($jTotals['dia_cont']) }}</td>
                <td class="border-black font-weight-bold">{{ round($jTotals['dia_cump']) }}</td>
                <td class="border-black">{{ round($jTotals['hr_cont']) }}</td>
                <td class="border-black font-weight-bold">{{ round($jTotals['hr_cump']) }}</td>
                <td class="border-black">{{ round($jTotals['prog']) }}</td>
                <td class="border-black font-weight-bold">{{ round($jTotals['repr']) }}</td>
                <td class="border-black font-weight-bold">{{ round($jTotals['atend']) }}</td>
                <td class="border-black font-weight-bold">
                    @php $p = $jTotals['repr'] > 0 ? ($jTotals['atend'] / $jTotals['repr'] * 100) : 0; @endphp
                    {{ number_format($p, 0) }}%
                </td>
                <td class="border-black middle">{{ round($jTotals['hsc_comp']) > 0 ? round($jTotals['hsc_comp']) : '0' }}</td>
                <td class="border-black middle">{{ round($jTotals['hsc_esfam']) > 0 ? round($jTotals['hsc_esfam']) : '0' }}</td>
                <td class="border-black middle">{{ round($jTotals['hsc_prom']) > 0 ? round($jTotals['hsc_prom']) : '0' }}</td>
                <td class="border-black middle">{{ round($jTotals['hsc_cong']) > 0 ? round($jTotals['hsc_cong']) : '0' }}</td>
                <td class="border-black middle">{{ round($jTotals['hsc_campo']) > 0 ? round($jTotals['hsc_campo']) : '0' }}</td>
                <td class="border-black middle">{{ round($jTotals['hsc_asam']) > 0 ? round($jTotals['hsc_asam']) : '0' }}</td>
                <td class="border-black middle">{{ round($jTotals['hsc_citas']) > 0 ? round($jTotals['hsc_citas']) : '0' }}</td>
                <td class="border-black middle">{{ round($jTotals['hsc_ord']) > 0 ? round($jTotals['hsc_ord']) : '0' }}</td>
                <td class="border-black middle">{{ round($jTotals['hsc_profil']) > 0 ? round($jTotals['hsc_profil']) : '0' }}</td>
                <td class="border-black middle">{{ round($jTotals['hsc_pers']) > 0 ? round($jTotals['hsc_pers']) : '0' }}</td>
                <td class="border-black no-print"></td>
            </tr>
        @endforeach
    </tbody>
    <tfoot class="font-weight-bold tfoot-dark">
        <tr>
            <td colspan="2" class="border-black text-right sticky-col-footer" style="padding-right: 10px; left: 0;">TOTAL REGIONAL
                (TODAS LAS JORNADAS)</td>
            <td class="border-black">{{ round($grandTotals['acuerdo']) }}</td>
            <td class="border-black">{{ round($grandTotals['contrato']) }}</td>
            <td class="border-black">{{ round($grandTotals['m_general']) }}</td>
            <td class="border-black">{{ round($grandTotals['m_especialista']) }}</td>
            <td class="border-black">{{ round($grandTotals['hrs_tadas']) }}</td>
            <td class="border-black">{{ round($grandTotals['dia_cont']) }}</td>
            <td class="border-black text-success">{{ round($grandTotals['dia_cump']) }}</td>
            <td class="border-black">{{ round($grandTotals['hr_cont']) }}</td>
            <td class="border-black text-success">{{ round($grandTotals['hr_cump']) }}</td>
            <td class="border-black">{{ round($grandTotals['prog']) }}</td>
            <td class="border-black font-weight-bold">{{ round($grandTotals['repr']) }}</td>
            <td class="border-black font-weight-bold">{{ round($grandTotals['atend']) }}</td>
            <td class="border-black">
                @php $gp = $grandTotals['repr'] > 0 ? ($grandTotals['atend'] / $grandTotals['repr'] * 100) : 0; @endphp
                {{ number_format($gp, 0) }}%
            </td>
            <td class="border-black font-weight-bold">{{ round($grandTotals['hsc_comp']) > 0 ? round($grandTotals['hsc_comp']) : '0' }}</td>
            <td class="border-black font-weight-bold">{{ round($grandTotals['hsc_esfam']) > 0 ? round($grandTotals['hsc_esfam']) : '0' }}</td>
            <td class="border-black font-weight-bold">{{ round($grandTotals['hsc_prom']) > 0 ? round($grandTotals['hsc_prom']) : '0' }}</td>
            <td class="border-black font-weight-bold">{{ round($grandTotals['hsc_cong']) > 0 ? round($grandTotals['hsc_cong']) : '0' }}</td>
            <td class="border-black font-weight-bold">{{ round($grandTotals['hsc_campo']) > 0 ? round($grandTotals['hsc_campo']) : '0' }}</td>
            <td class="border-black font-weight-bold">{{ round($grandTotals['hsc_asam']) > 0 ? round($grandTotals['hsc_asam']) : '0' }}</td>
            <td class="border-black font-weight-bold">{{ round($grandTotals['hsc_citas']) > 0 ? round($grandTotals['hsc_citas']) : '0' }}</td>
            <td class="border-black font-weight-bold">{{ round($grandTotals['hsc_ord']) > 0 ? round($grandTotals['hsc_ord']) : '0' }}</td>
            <td class="border-black font-weight-bold">{{ round($grandTotals['hsc_profil']) > 0 ? round($grandTotals['hsc_profil']) : '0' }}</td>
            <td class="border-black font-weight-bold">{{ round($grandTotals['hsc_pers']) > 0 ? round($grandTotals['hsc_pers']) : '0' }}</td>
            <td class="border-black no-print"></td>
        </tr>
    </tfoot>
@else
    <tbody>
        @php
            $totals = [
                'acuerdo' => 0, 'contrato' => 0, 'm_general' => 0, 'm_especialista' => 0,
                'hrs_tadas' => 0, 'dia_cont' => 0, 'dia_cump' => 0, 'hr_cont' => 0, 'hr_cump' => 0,
                'prog' => 0, 'repr' => 0, 'atend' => 0,
                'hsc_comp' => 0, 'hsc_esfam' => 0, 'hsc_prom' => 0, 'hsc_cong' => 0,
                'hsc_campo' => 0, 'hsc_asam' => 0, 'hsc_citas' => 0,
                'hsc_ord' => 0, 'hsc_profil' => 0, 'hsc_pers' => 0
            ];
        @endphp
        @php
            $rowsCount = count($data);
            $remainingRows = $rowsCount == 0 ? 10 : 0;
        @endphp

        @foreach($data as $index => $row)
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

                if ($isAcuerdo)
                    $totals['acuerdo']++;
                if ($isContrato)
                    $totals['contrato']++;
                if ($isGeneral)
                    $totals['m_general']++;
                if ($isEspecialista)
                    $totals['m_especialista']++;

                $totals['hrs_tadas'] += $row['horasPorDia'];
                $totals['dia_cont'] += $row['diasContratados'];
                $totals['dia_cump'] += $row['diasCumplidos'];
                $totals['hr_cont'] += $row['horasContratadasMes'];
                $totals['hr_cump'] += $row['horasCumplidas'];
                $totals['prog'] += $row['prog'];
                $totals['repr'] += $row['repr'];
                $totals['atend'] += $row['atenciones'];

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

                $totals['hsc_comp']  += $hsc_comp;
                $totals['hsc_esfam'] += $hsc_esfam;
                $totals['hsc_prom']  += $hsc_prom;
                $totals['hsc_cong']  += $hsc_cong;
                $totals['hsc_campo'] += $hsc_campo;
                $totals['hsc_asam']  += $hsc_asam;
                $totals['hsc_citas'] += $hsc_citas;
                $totals['hsc_ord']   += $hsc_ord;
                $totals['hsc_profil']+= $hsc_profil;
                $totals['hsc_pers']  += $hsc_pers;
            @endphp
            <tr style="height: auto;" class="medico-row {{ $isONG ? 'ong-row' : '' }}" data-medico-id="{{ $medicoRecord->id }}">
                <td class="border-black sticky-col-1">{{ $index + 1 }}</td>
                <td class="border-black text-left sticky-col-2" style="padding-left: 10px; padding-right: 5px;">
                    @php
                        $nombreLimpio = trim($medicoRecord->NOM_MED);
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
                    @endphp
                    {{ trim($nombreLimpio) }}
                </td>
                <td class="border-black middle">{{ $isAcuerdo ? 'X' : '-' }}</td>
                <td class="border-black middle">{{ $isContrato ? ($isSS ? 'SS' : 'X') : '-' }}</td>
                <td class="border-black middle">{{ $isGeneral ? ($isSS ? 'SS' : 'X') : '-' }}</td>
                <td class="border-black middle">{{ $isEspecialista ? 'X' : '-' }}</td>
                <td class="border-black middle">{{ $isONG ? '-' : ($row['horasPorDia'] > 0 ? (round($row['horasPorDia']) == $row['horasPorDia'] ? round($row['horasPorDia']) : number_format($row['horasPorDia'], 1)) : '0') }}</td>
                <td class="border-black middle">{{ $isONG ? '-' : ($row['diasContratados'] > 0 ? round($row['diasContratados']) : '0') }}</td>
                <td class="border-black middle">{{ $isONG ? '-' : ($row['diasCumplidos'] > 0 ? round($row['diasCumplidos']) : '0') }}</td>
                <td class="border-black middle">{{ $isONG ? '-' : ($row['horasContratadasMes'] > 0 ? round($row['horasContratadasMes']) : '0') }}</td>
                <td class="border-black middle">{{ $isONG ? '-' : ($row['horasCumplidas'] > 0 ? round($row['horasCumplidas']) : '0') }}</td>
                <td class="border-black middle">{{ $isONG ? '-' : ($row['prog'] > 0 ? round($row['prog']) : '0') }}</td>
                <td class="border-black middle font-weight-bold">{{ $isONG ? '-' : ($row['repr'] > 0 ? round($row['repr']) : '0') }}</td>
                <td class="border-black middle font-weight-bold text-primary">{{ round($row['atenciones']) }}</td>
                <td class="border-black middle">{{ $isONG ? '-' : ($row['rendimiento'] > 0 ? round($row['rendimiento']) . '%' : '0%') }}</td>
                <td class="border-black middle">{{ $hsc_comp > 0 ? $hsc_comp : '0' }}</td>
                <td class="border-black middle">{{ $hsc_esfam > 0 ? $hsc_esfam : '0' }}</td>
                <td class="border-black middle">{{ $hsc_prom > 0 ? $hsc_prom : '0' }}</td>
                <td class="border-black middle">{{ $hsc_cong > 0 ? $hsc_cong : '0' }}</td>
                <td class="border-black middle">{{ $hsc_campo > 0 ? $hsc_campo : '0' }}</td>
                <td class="border-black middle">{{ $hsc_asam > 0 ? $hsc_asam : '0' }}</td>
                <td class="border-black middle">{{ $hsc_citas > 0 ? $hsc_citas : '0' }}</td>
                <td class="border-black middle">{{ $hsc_ord > 0 ? $hsc_ord : '0' }}</td>
                <td class="border-black middle">{{ $hsc_profil > 0 ? $hsc_profil : '0' }}</td>
                <td class="border-black middle">{{ $hsc_pers > 0 ? $hsc_pers : '0' }}</td>
                <td class="border-black no-print middle">
                    @php
                        $atenciones = $row['atenciones'] ?? 0;
                        $prog = $row['prog'] ?? 0;
                        $pxh = $row['pacientesPorHour'] ?? 0;
                        $tdias = $totalDias ?? 0;
                        $dcont = $row['diasContratados'] ?? 0;
                        $hpd = $row['horasPorDia'] ?? 0;
                        $rend = $row['rendimiento'] ?? 0;
                        $medName = addslashes($medicoRecord->NOM_MED);
                    @endphp
                    <button type="button" class="btn btn-hsc-modal"
                        data-id="{{ $medicoRecord->id }}"
                        data-name="{{ $medName }}"
                        data-atenciones="{{ $atenciones }}"
                        data-prog="{{ $prog }}"
                        data-pxh="{{ $pxh }}"
                        data-diasmes="{{ $tdias }}"
                        data-diascont="{{ $dcont }}"
                        data-hrsdia="{{ $hpd }}"
                        data-rend="{{ $rend }}"
                        data-observaciones="{{ e($h->observaciones ?? '') }}"
                        title="Observaciones / Horas Sin Consulta">
                        <i class="bi bi-pencil-square"></i>
                    </button>
                </td>
            </tr>
        @endforeach

        @for ($i = 0; $i < $remainingRows; $i++)
            <tr class="empty-row">
                <td class="border-black sticky-col-1">{{ $rowsCount + $i + 1 }}</td>
                <td class="border-black sticky-col-2">&nbsp;</td>
                <td class="border-black">&nbsp;</td>
                <td class="border-black">&nbsp;</td>
                <td class="border-black">&nbsp;</td>
                <td class="border-black">&nbsp;</td>
                <td class="border-black">&nbsp;</td>
                <td class="border-black">&nbsp;</td>
                <td class="border-black">&nbsp;</td>
                <td class="border-black">&nbsp;</td>
                <td class="border-black">&nbsp;</td>
                <td class="border-black">&nbsp;</td>
                <td class="border-black">&nbsp;</td>
                <td class="border-black">&nbsp;</td>
                <td class="border-black">&nbsp;</td>
                <td class="border-black">&nbsp;</td>
                <td class="border-black">&nbsp;</td>
                <td class="border-black">&nbsp;</td>
                <td class="border-black">&nbsp;</td>
                <td class="border-black">&nbsp;</td>
                <td class="border-black">&nbsp;</td>
                <td class="border-black">&nbsp;</td>
                <td class="border-black">&nbsp;</td>
                <td class="border-black">&nbsp;</td>
                <td class="border-black">&nbsp;</td>
                <td class="border-black no-print">&nbsp;</td>
            </tr>
        @endfor
    </tbody>
    <tfoot class="font-weight-bold tfoot-light">
        <tr>
            <td colspan="2" class="border-black sticky-col-footer" style="text-align: right; padding-right: 20px;">TOTAL
                JORNADA {{ $jornada }}</td>
            <td class="border-black">{{ round($totals['acuerdo']) }}</td>
            <td class="border-black">{{ round($totals['contrato']) }}</td>
            <td class="border-black">{{ round($totals['m_general']) }}</td>
            <td class="border-black">{{ round($totals['m_especialista']) }}</td>
            <td class="border-black">{{ round($totals['hrs_tadas']) }}</td>
            <td class="border-black">{{ number_format($totals['dia_cont'], 0) }}</td>
            <td class="border-black">{{ round($totals['dia_cump']) }}</td>
            <td class="border-black">{{ number_format($totals['hr_cont'], 0) }}</td>
            <td class="border-black">{{ round($totals['hr_cump']) }}</td>
            <td class="border-black">{{ round($totals['prog']) }}</td>
            <td class="border-black font-weight-bold">{{ round($totals['repr']) }}</td>
            <td class="border-black font-weight-bold">{{ round($totals['atend']) }}</td>
            <td class="border-black">
                @php
                    $global_percent = $totals['repr'] > 0 ? ($totals['atend'] / $totals['repr'] * 100) : 0;
                @endphp
                {{ number_format($global_percent, 0) }}%
            </td>
            <td class="border-black font-weight-bold">{{ round($totals['hsc_comp']) > 0 ? round($totals['hsc_comp']) : '0' }}</td>
            <td class="border-black font-weight-bold">{{ round($totals['hsc_esfam']) > 0 ? round($totals['hsc_esfam']) : '0' }}</td>
            <td class="border-black font-weight-bold">{{ round($totals['hsc_prom']) > 0 ? round($totals['hsc_prom']) : '0' }}</td>
            <td class="border-black font-weight-bold">{{ round($totals['hsc_cong']) > 0 ? round($totals['hsc_cong']) : '0' }}</td>
            <td class="border-black font-weight-bold">{{ round($totals['hsc_campo']) > 0 ? round($totals['hsc_campo']) : '0' }}</td>
            <td class="border-black font-weight-bold">{{ round($totals['hsc_asam']) > 0 ? round($totals['hsc_asam']) : '0' }}</td>
            <td class="border-black font-weight-bold">{{ round($totals['hsc_citas']) > 0 ? round($totals['hsc_citas']) : '0' }}</td>
            <td class="border-black font-weight-bold">{{ round($totals['hsc_ord']) > 0 ? round($totals['hsc_ord']) : '0' }}</td>
            <td class="border-black font-weight-bold">{{ round($totals['hsc_profil']) > 0 ? round($totals['hsc_profil']) : '0' }}</td>
            <td class="border-black font-weight-bold">{{ round($totals['hsc_pers']) > 0 ? round($totals['hsc_pers']) : '0' }}</td>
            <td class="border-black no-print"></td>
        </tr>
    </tfoot>
@endif