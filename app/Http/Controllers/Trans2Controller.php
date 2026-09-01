<?php

namespace App\Http\Controllers;

use App\Models\RegistroGlobal;
use App\Models\Setting;
use App\Traits\InformesHelperTrait;
use Illuminate\Http\Request;
use Carbon\Carbon;

class Trans2Controller extends Controller
{
    use InformesHelperTrait;

    public function __construct(private \App\Services\RegistroGlobalService $service)
    {
    }

    public function index(Request $request)
    {
        $anos = $this->getAnosDisponibles();
        $anoDefault = $request->input('ano', (int) date('Y'));
        if (!$anos->contains($anoDefault)) {
            $anoDefault = $anos->first() ?? (int)date('Y');
        }

        $mesMap = [
            1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL', 5 => 'MAYO', 6 => 'JUNIO',
            7 => 'JULIO', 8 => 'AGOSTO', 9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE',
        ];

        $meses = $this->getMesesDisponibles($anoDefault)->toArray() ?: [$mesMap[(int) date('n')]];
        $mesDefault = $request->input('mes');
        if (!$mesDefault || !in_array(strtoupper($mesDefault), array_map('strtoupper', $meses))) {
            $mesDefault = end($meses);
        }

        // Obtener semanas del mes seleccionado y aplicar ajuste de semanas compartidas
        $rawWeeks = RegistroGlobal::where('ano', $anoDefault)
            ->where('mes', $mesDefault)
            ->whereNotNull('se')
            ->distinct()
            ->orderBy('se', 'asc')
            ->pluck('se')
            ->toArray();

        $semanasMes = $this->ajustarSemanasMes($anoDefault, $mesDefault, $rawWeeks);

        $settings = Setting::pluck('value', 'key');
        $sections = $this->getSectionsDefinition();

        // Consulta de datos directamente desde RegistroGlobal (Casos Nuevos)
        $rgRecords = RegistroGlobal::query()
            ->where('ano', $anoDefault)
            ->where('mes', $mesDefault)
            ->get([
                'fecha', 'se', 'edad', 'tipo', 'sexo', 'cond',
                'diagnostico_1', 'cod_1', 'cond_1',
                'diagnostico_2', 'cod_2', 'cond_2',
                'diagnostico_3', 'cod_3', 'cond_3',
                'diagnostico_4', 'cod_4', 'cond_4',
                'diagnostico_5', 'cod_5', 'cond_5',
                'diagnostico_6', 'cod_6', 'cond_6',
                'diagnostico_7', 'cod_7', 'cond_7',
            ]);

        $unrolled = [];
        foreach ($rgRecords as $rg) {
            $se = $rg->se;
            if (!$se && $rg->fecha) {
                $se = $this->getSeDeDate($rg->fecha);
            }
            if (!$se) continue;

            // Ajustar semana para consolidar días compartidos en el mes
            $seMapped = $this->mapearSemanaMes((int)$se, $semanasMes);

            for ($i = 1; $i <= 7; $i++) {
                $diag = trim($rg->{"diagnostico_$i"} ?? '');
                if ($diag === '') continue;

                $cond = strtoupper(trim($rg->{"cond_$i"} ?? ($rg->cond ?? '')));
                // TRANS-2 solo contabiliza casos nuevos
                if ($cond !== 'N') continue;

                $unrolled[] = (object)[
                    'diagnostico' => $diag,
                    'cod' => trim($rg->{"cod_$i"} ?? ''),
                    'sexo' => $rg->sexo,
                    'edad' => $rg->edad,
                    'tipo' => $rg->tipo,
                    'se' => (int)$seMapped,
                ];
            }
        }

        $rawData = collect($unrolled);

        $results = [];
        foreach ($sections as $section) {
            foreach ($section['rows'] as $row) {
                $rowId = $row['id'];
                $results[$rowId] = [];
                
                foreach ($semanasMes as $se) {
                    $results[$rowId][$se] = ['less_1' => 0, '1_4' => 0, '5_14' => 0, '15_plus' => 0, 'total' => 0];
                }

                if (isset($row['diag'])) {
                    $diags = is_array($row['diag']) ? $row['diag'] : [$row['diag']];
                    $diagsNorm = array_map([$this, 'normalizeForMatch'], $diags);

                    foreach ($rawData as $r) {
                        if (in_array($this->normalizeForMatch($r->diagnostico), $diagsNorm)) {
                            $ageRange = $this->getAgeRange($r);
                            if ($ageRange && isset($results[$rowId][$r->se])) {
                                $results[$rowId][$r->se][$ageRange]++;
                                $results[$rowId][$r->se]['total']++;
                            }
                        }
                    }
                }
            }
        }

        $viewData = compact(
            'anos', 'anoDefault', 'meses', 'mesDefault', 'semanasMes',
            'sections', 'results', 'settings'
        );

        if ($request->ajax()) {
            return view('informes.trans2_content', $viewData);
        }

        return view('informes.trans2', $viewData);
    }

    /**
     * Ajusta las semanas mostradas en el mes:
     * Si la última semana tiene <= 3 días en este mes, se consolida con la anterior.
     */
    private function ajustarSemanasMes(int $ano, string $mes, array $rawWeeks): array
    {
        if (empty($rawWeeks)) return [];
        $semanas = array_values($rawWeeks);

        $lastWeek = end($semanas);
        $diasEnMes = RegistroGlobal::where('ano', $ano)
            ->where('mes', $mes)
            ->where('se', $lastWeek)
            ->distinct('fecha')
            ->count('fecha');

        if ($diasEnMes <= 3 && count($semanas) > 1) {
            array_pop($semanas);
        }

        return array_values($semanas);
    }

    /**
     * Mapea una semana original hacia la semana correspondiente del mes:
     * - Semanas anteriores a la primera semana del mes (ej. SE 29) -> se asignan a la primera semana (ej. SE 30).
     * - Semanas posteriores a la última semana mostrada (ej. SE 35) -> se asignan a la última semana (ej. SE 34).
     */
    private function mapearSemanaMes(int $seOriginal, array $semanasAjustadas): int
    {
        if (empty($semanasAjustadas)) return $seOriginal;
        $min = min($semanasAjustadas);
        $max = max($semanasAjustadas);

        if ($seOriginal < $min) return $min;
        if ($seOriginal > $max) return $max;
        return $seOriginal;
    }


    private function getSeDeDate(string $fecha): int
    {
        $d = Carbon::parse($fecha);
        $anio = $d->year;
        $d4Enero = Carbon::create($anio, 1, 4);
        $dow4Enero = $d4Enero->dayOfWeek;
        $primeroSE1 = $d4Enero->copy()->subDays($dow4Enero);

        if ($d->lt($primeroSE1)) {
            return $this->getSeDeDate(Carbon::create($anio - 1, 12, 31)->format('Y-m-d'));
        }

        return (int) floor($primeroSE1->diffInDays($d) / 7) + 1;
    }

    private function getAgeRange($r)
    {
        $t = strtoupper(trim($r->tipo));
        $e = (int) $r->edad;
        if ($t === 'D' || $t === 'M' || ($t === 'A' && $e < 1)) return 'less_1';
        if ($t === 'A') {
            if ($e >= 1 && $e <= 4) return '1_4';
            if ($e >= 5 && $e <= 14) return '5_14';
            if ($e >= 15) return '15_plus';
        }
        return null;
    }

    private function getDatesFromWeek($year, $week)
    {
        $date = Carbon::now()->setISODate($year, $week);
        return [
            'start' => $date->startOfWeek()->format('d/m/Y'),
            'end' => $date->endOfWeek()->format('d/m/Y')
        ];
    }

    private function getSectionsDefinition()
    {
        return [
            [
                'title' => 'ENFERMEDADES INMUNOPREVENIBLES',
                'side' => 'obverso',
                'rows' => [
                    ['id' => 'polio', 'code' => 'A.80.9', 'label' => 'POLIOMIELITIS', 'diag' => ['POLIOMIELITIS', 'POLIOMIELITIS AGUDA']],
                    ['id' => 'sarampion', 'code' => 'B.05.9', 'label' => 'SARAMPION', 'diag' => 'SARAMPION'],
                    ['id' => 'tosferina', 'code' => 'A.37.9', 'label' => 'TOSFERINA', 'diag' => 'TOSFERINA'],
                    ['id' => 'difteria', 'code' => 'A.36.9', 'label' => 'DIFTERIA', 'diag' => 'DIFTERIA'],
                    ['id' => 'tetanos_neo', 'code' => 'A.33.X', 'label' => 'TETANOS NEONATORUM', 'diag' => ['TETANOS NEONATORUM', 'TETANO NEONATORUM']],
                    ['id' => 'tetanos_exc', 'code' => 'A.35.X', 'label' => 'TETANOS (Excepto Neonatorum)', 'diag' => 'TETANOS'],
                    ['id' => 'parotiditis', 'code' => 'K.11.2', 'label' => 'PAROTIDITIS', 'diag' => 'PAROTIDITIS'],
                    ['id' => 'rubeola', 'code' => 'B.06.9', 'label' => 'RUBEOLA', 'diag' => 'RUBEOLA'],
                    ['id' => 'rubeola_cong', 'code' => 'P.35.0', 'label' => 'SINDROME DE RUBEOLA CONGENITA', 'diag' => ['RUBEOLA CONGENITA', 'SINDROME DE RUBEOLA CONGENITA']],
                    ['id' => 'varicela', 'code' => 'B.01.9', 'label' => 'VARICELA', 'diag' => 'VARICELA'],
                ]
            ],
            [
                'title' => 'OTRAS ENFERMEDADES PREVENIBLES',
                'side' => 'obverso',
                'rows' => [
                    ['id' => 'hepatitis_a', 'code' => 'B.15.9', 'label' => 'HEPATITIS "A"', 'diag' => ['HEPATITIS A', 'HEPATITIS INFECCIOSA']],
                    ['id' => 'hepatitis_b', 'code' => 'B.16.9', 'label' => 'HEPATITIS "B"', 'diag' => 'HEPATITIS B'],
                    ['id' => 'hepatitis_c', 'code' => '', 'label' => 'HEPATITIS "C"', 'diag' => 'HEPATITIS C'],
                    ['id' => 'hepatitis_d', 'code' => '', 'label' => 'HEPATITIS "D"', 'diag' => 'HEPATITIS D'],
                ]
            ],
            [
                'title' => 'ENFERMEDADES INTESTINALES',
                'side' => 'obverso',
                'rows' => [
                    ['id' => 'diarrea', 'code' => 'A.09.X', 'label' => 'DIARREA', 'diag' => ['DIARREA', 'DIARREA SIN SANGRE', 'DIARREAS', 'DIARREAS CON DESHIDRATACION', 'DIARREAS SIN DESHIDRATACION', 'DIARREA CON DESHIDRATACION', 'DIARREA SIN DESHIDRATACION']],
                    ['id' => 'disenteria', 'code' => 'A.09.X', 'label' => 'DISENTERIA', 'diag' => ['DISENTERIA', 'DISENTERIA BACILAR', 'DISENTERIA AMEBIANA']],
                    ['id' => 'colera', 'code' => 'A.00.9', 'label' => 'COLERA', 'diag' => 'COLERA'],
                    ['id' => 'tifoidea', 'code' => 'A.01.0', 'label' => 'FIEBRE TIFOIDEA Y PARATIFOIDEA', 'diag' => ['FIEBRE TIFOIDEA', 'PARATIFOIDEA', 'FIEBRE TIFOIDEA Y PARATIFOIDEA']],
                ]
            ],
            [
                'title' => 'ENFERMEDADES RESPIRATORIAS',
                'side' => 'obverso',
                'rows' => [
                    ['id' => 'bronquitis', 'code' => 'J40.9-J21.9', 'label' => 'BRONQUITIS', 'diag' => ['BRONQUITIS', 'BRONQUITIS AGUDA', 'BRONQUITIS CRONICA']],
                    ['id' => 'asma', 'code' => '', 'label' => 'ASMA', 'diag' => ['ASMA', 'ASMA BRONQUIAL']],
                    ['id' => 'neumonia', 'code' => 'J18.9-J10.0', 'label' => 'NEUMONÍA / BRONCONEUMONÍA', 'diag' => ['NEUMONIA', 'BRONCONEUMONIA', 'NEUMONIAS', 'BRONCONEUMONIAS', 'NEUMONIAS/BRONCONEUMONIAS']],
                    ['id' => 'faringo', 'code' => 'J02.0-J03.0', 'label' => 'FARINGO AMIGDALITIS', 'diag' => ['FARINGOAMIGDALITIS', 'FARINGITIS', 'FARINGOAMIGDALITIS ESTREPTOCOCICA', 'FARINGITIS ESTREPTOCOCICA', 'AMIGDALITIS AGUDA', 'AMIGDALITIS', 'FARINGO AMIGDALITIS', 'FARINGOAMIGDALITIS VIRAL', 'FARING.AMIG. ESTREPTOCOCICAS', 'FARINGOAMIGDALITIS ESTREPTOCOCICAS']],
                    ['id' => 'tuberculosis', 'code' => 'A.16.4', 'label' => 'TUBERCULOSIS RESPIRATORIA', 'diag' => ['TUBERCULOSIS', 'TUBERCULOSIS PULMONAR', 'TUBERCULOSIS RESPIRATORIA']],
                    ['id' => 'covid', 'code' => 'U07.2', 'label' => 'INFECCIÓN POR COVID 19', 'diag' => ['COVID-19', 'CORONAVIRUS', 'ATENCION CLINICA POR COVID-19']],
                ]
            ],
            [
                'title' => 'INFECCIONES MENINGEAS',
                'side' => 'obverso',
                'rows' => [
                    ['id' => 'meningitis_tub', 'code' => 'A.17.0', 'label' => 'MENINGITIS TUBERCULOSA', 'diag' => 'MENINGITIS TUBERCULOSA'],
                    ['id' => 'meningitis_men', 'code' => 'A.39.0', 'label' => 'MENINGITIS MENINGOCOCICA', 'diag' => ['MENINGITIS MENINGOCOCICA', 'INFECCION MENINGOCOCICA']],
                    ['id' => 'otras_men', 'code' => 'G03.0-G03.9', 'label' => 'OTRAS MENINGITIS', 'diag' => 'OTRAS MENINGITIS'],
                ]
            ],
            [
                'title' => 'ENFERMEDADES VECTORIALES',
                'side' => 'reverso',
                'rows' => [
                    ['id' => 'malaria', 'code' => 'B.54.X', 'label' => 'MALARIA CASOS CONFIRMADOS', 'diag' => 'MALARIA'],
                    ['id' => 'dengue_ss', 'code' => 'A.90.X', 'label' => 'DENGUE SIN SIGNOS DE ALARMA', 'diag' => ['DENGUE SIN SIGNOS DE ALARMA', 'SOSP. DENGUE SIN SIGNOS DE ALARMA', 'DSSA', 'D.S.S.A', 'SOSPECHA DENGUE SIN SIGNOS DE ALARMA', 'DENGUE S.S.A']],
                    ['id' => 'dengue_cs', 'code' => '', 'label' => 'DENGUE CON SIGNOS DE ALARMA', 'diag' => ['DENGUE CON SIGNOS DE ALARMA', 'SOSP. DENGUE CON SIGNOS DE ALARMA', 'DCSA', 'D.C.S.A', 'SOSPECHA DENGUE CON SIGNOS DE ALARMA', 'DENGUE C.S.A']],
                    ['id' => 'dengue_grave', 'code' => 'A.91.X', 'label' => 'DENGUE GRAVE', 'diag' => ['DENGUE GRAVE', 'SOSP. DENGUE GRAVE', 'SOSPECHOSO DENGUE GRAVE', 'SOSPECHA DENGUE GRAVE']],
                    ['id' => 'chikungunya', 'code' => 'A.92.0', 'label' => 'CHIKUNGUNYA', 'diag' => 'CHIKUNGUNYA'],
                    ['id' => 'zika', 'code' => '', 'label' => 'ZIKA, NO ESPECIFICADO (EXCEPTO EMB.)', 'diag' => ['ZIKA', 'SOSP. ZIKA']],
                    ['id' => 'zika_emb', 'code' => '', 'label' => 'ZIKA EMBARAZADAS', 'diag' => ['ZIKA EMBARAZADAS', 'ZIKA EN EMBARAZADAS']],
                    ['id' => 'leishmaniasis_v', 'code' => 'B.55.0', 'label' => 'LEISHMANIASIS VICERAL', 'diag' => ['LEISHMANIASIS VISCERAL', 'LEISHMANIASIS VICERAL']],
                    ['id' => 'leishmaniasis_c', 'code' => 'B.55.1', 'label' => 'LEISHMANIASIS CUTANEA', 'diag' => 'LEISHMANIASIS CUTANEA'],
                    ['id' => 'chagas_a', 'code' => 'B.57.1', 'label' => 'CHAGAS AGUDO', 'diag' => 'CHAGAS AGUDO'],
                    ['id' => 'chagas_c', 'code' => 'B.57.2', 'label' => 'CHAGAS CRONICO', 'diag' => ['CHAGAS CRONICO', 'CHAGAS']],
                ]
            ],
            [
                'title' => 'INFECCIONES DE TRANSMISIÓN SEXUAL',
                'side' => 'reverso',
                'rows' => [
                    ['id' => 'sifilis', 'code' => 'A.53.9', 'label' => 'SIFILIS', 'diag' => ['SIFILIS', 'SIFILIS PRIMARIA', 'SIFILIS SECUNDARIA', 'SIFILIS CONGENITA']],
                    ['id' => 'gonorrea', 'code' => 'A.54.9', 'label' => 'GONORREA', 'diag' => ['GONORREA', 'INFECCION GONOCOCICA']],
                    ['id' => 'sida', 'code' => 'B.24.9', 'label' => 'SIDA', 'diag' => ['SIDA', 'VIH', 'VIH-SIDA', 'VIH POSITIVO']],
                    ['id' => 'herpes', 'code' => 'A.60.0', 'label' => 'HERPES GENITAL', 'diag' => ['HERPES GENITAL', 'HERPES']],
                ]
            ],
            [
                'title' => 'ENFERMEDADES ZOONOTICAS',
                'side' => 'reverso',
                'rows' => [
                    ['id' => 'rabia', 'code' => 'A.82.0', 'label' => 'RABIA HUMANA', 'diag' => 'RABIA HUMANA'],
                    ['id' => 'leptospirosis', 'code' => 'A.27.0-A.27.9', 'label' => 'LEPTOSPIROSIS', 'diag' => 'LEPTOSPIROSIS'],
                    ['id' => 'peste', 'code' => 'A.20.9', 'label' => 'PESTE', 'diag' => 'PESTE'],
                    ['id' => 'fiebre_ama', 'code' => 'A.95.9', 'label' => 'FIEBRE AMARILLA', 'diag' => 'FIEBRE AMARILLA'],
                ]
            ],
            [
                'title' => 'ENF. CRÓNICO DEGENERATIVAS',
                'side' => 'reverso',
                'rows' => [
                    ['id' => 'hipertension', 'code' => 'I.10.X', 'label' => 'HIPERTENSIÓN ARTERIAL', 'diag' => 'HIPERTENSION'],
                    ['id' => 'diabetes', 'code' => 'E.14.9', 'label' => 'DIABETES MELLITUS', 'diag' => 'DIABETES'],
                    ['id' => 'renal', 'code' => '', 'label' => 'ENFERMEDAD RENAL CRONICA', 'diag' => 'INSUFICIENCIA RENAL'],
                ]
            ],
            [
                'title' => 'INTOXICACIONES',
                'side' => 'reverso',
                'rows' => [
                    ['id' => 'plaguicidas', 'code' => 'I.65.9', 'label' => 'INTOXICACIONES AGUDAS PLAGUICIDAS', 'diag' => 'INTOXICACION POR PLAGUICIDAS'],
                    ['id' => 'animales_tox', 'code' => '', 'label' => 'INTOX. POR MORDEDURAS DE ANIMALES TOXICOS', 'diag' => 'MORDEDURA DE ANIMAL'],
                ]
            ]
        ];
    }

    /**
     * Retorna el detalle de pacientes para una celda seleccionada en TRANS-2.
     *
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getDetails(Request $request): \Illuminate\Http\JsonResponse
    {
        $ano = (int)$request->input('ano', (int)date('Y'));
        $mes = (string)$request->input('mes', '');
        $se = (int)$request->input('se', 0);
        $rowId = (string)$request->input('row_id', '');
        $range = (string)$request->input('range', 'total');

        $sections = $this->getSectionsDefinition();
        $targetRow = null;
        foreach ($sections as $section) {
            foreach ($section['rows'] as $row) {
                if ($row['id'] === $rowId) {
                    $targetRow = $row;
                    break 2;
                }
            }
        }

        if (!$targetRow) {
            return response()->json(['error' => 'No encontrado'], 404);
        }

        $diags = is_array($targetRow['diag']) ? $targetRow['diag'] : [$targetRow['diag']];
        $diagsNorm = array_map([$this, 'normalizeForMatch'], $diags);

        // Obtener semanas ajustadas para este mes
        $semanasMes = [];
        if ($mes !== '') {
            $rawWeeks = RegistroGlobal::where('ano', $ano)
                ->where('mes', $mes)
                ->whereNotNull('se')
                ->distinct()
                ->orderBy('se', 'asc')
                ->pluck('se')
                ->toArray();
            $semanasMes = $this->ajustarSemanasMes($ano, $mes, $rawWeeks);
        }

        // Consultar directamente desde RegistroGlobal
        $query = RegistroGlobal::query()->where('ano', $ano);
        if ($mes !== '') {
            $query->where('mes', $mes);
        }

        $rgRecords = $query->get([
            'id', 'fecha', 'se', 'edad', 'tipo', 'sexo', 'cond', 'exp', 'medico', 'prof',
            'diagnostico_1', 'cod_1', 'cond_1',
            'diagnostico_2', 'cod_2', 'cond_2',
            'diagnostico_3', 'cod_3', 'cond_3',
            'diagnostico_4', 'cod_4', 'cond_4',
            'diagnostico_5', 'cod_5', 'cond_5',
            'diagnostico_6', 'cod_6', 'cond_6',
            'diagnostico_7', 'cod_7', 'cond_7',
        ]);

        $details = [];
        $summaryByDay = [];
        $summaryByRange = [];

        foreach ($rgRecords as $rg) {
            $recordSe = $rg->se;
            if (!$recordSe && $rg->fecha) {
                $recordSe = $this->getSeDeDate($rg->fecha);
            }
            if ($recordSe) {
                $recordSe = $this->mapearSemanaMes((int)$recordSe, $semanasMes);
            }
            if ((int)$recordSe !== $se) {
                continue;
            }

            for ($i = 1; $i <= 7; $i++) {
                $diag = trim($rg->{"diagnostico_$i"} ?? '');
                if ($diag === '') {
                    continue;
                }

                $cond = strtoupper(trim($rg->{"cond_$i"} ?? ($rg->cond ?? '')));
                if ($cond !== 'N') {
                    continue;
                }

                if (in_array($this->normalizeForMatch($diag), $diagsNorm, true)) {
                    $itemObj = (object)[
                        'edad' => (int)$rg->edad,
                        'tipo' => (string)$rg->tipo,
                    ];
                    $ageRange = $this->getAgeRange($itemObj);

                    if ($range === 'total' || $ageRange === $range) {
                        $fechaFmt = $rg->fecha ? Carbon::parse($rg->fecha)->format('d/m/Y') : '-';
                        $codStr = trim($rg->{"cod_$i"} ?? '');
                        $medicoStr = (string)($rg->medico ?: 'No asignado');
                        if ($rg->prof) {
                            $medicoStr .= " - {$rg->prof}";
                        }

                        $details[] = [
                            'fecha' => $fechaFmt,
                            'exp' => $rg->exp ?: '-',
                            'sexo' => strtoupper(trim((string)$rg->sexo)) === 'H' ? 'H' : 'M',
                            'edad' => $rg->edad . ' ' . strtoupper(trim((string)$rg->tipo)),
                            'diagnostico' => $diag . ($codStr !== '' ? " ({$codStr})" : ''),
                            'medico' => $medicoStr,
                        ];

                        if ($fechaFmt !== '-') {
                            $summaryByDay[$fechaFmt] = ($summaryByDay[$fechaFmt] ?? 0) + 1;
                        }
                        if ($ageRange) {
                            $summaryByRange[$ageRange] = ($summaryByRange[$ageRange] ?? 0) + 1;
                        }
                    }
                }
            }
        }

        ksort($summaryByDay);

        return response()->json([
            'label' => $targetRow['label'] ?? '',
            'count' => count($details),
            'details' => $details,
            'summaryByDay' => $summaryByDay,
            'summaryByRange' => $summaryByRange,
        ]);
    }
}
