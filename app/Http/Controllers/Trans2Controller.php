<?php

namespace App\Http\Controllers;

use App\Models\Informe;
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
        $anos = Informe::distinct()->orderBy('ano', 'desc')->pluck('ano');
        if ($anos->isEmpty()) {
            $anos = collect([(int) date('Y')]);
        }

        $anoDefault = $request->input('ano', (int) date('Y'));
        if (!$anos->contains($anoDefault)) {
            $anoDefault = $anos->first();
        }

        $mesesDisponibles = Informe::where('ano', $anoDefault)
            ->distinct()
            ->pluck('mes')
            ->toArray();

        $mesMap = [
            1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL', 5 => 'MAYO', 6 => 'JUNIO',
            7 => 'JULIO', 8 => 'AGOSTO', 9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE',
        ];
        $ordenMeses = array_flip($mesMap);

        usort($mesesDisponibles, function ($a, $b) use ($ordenMeses) {
            return ($ordenMeses[strtoupper($a)] ?? 0) <=> ($ordenMeses[strtoupper($b)] ?? 0);
        });

        $meses = $mesesDisponibles ?: [$mesMap[(int) date('n')]];
        $mesDefault = $request->input('mes');
        if (!$mesDefault || !in_array(strtoupper($mesDefault), array_map('strtoupper', $meses))) {
            $mesDefault = end($meses);
        }

        // Obtener TODAS las semanas del mes seleccionado
        $semanasMes = Informe::where('ano', $anoDefault)
            ->where('mes', $mesDefault)
            ->whereNotNull('se')
            ->distinct()
            ->orderBy('se', 'asc')
            ->pluck('se');

        $settings = Setting::pluck('value', 'key');
        $sections = $this->getSectionsDefinition();

        // Consulta de datos para todo el mes
        $rawData = Informe::query()
            ->where('ano', $anoDefault)
            ->whereIn('se', $semanasMes)
            ->where('cond_diagnostico', 'N')
            ->select('diagnostico', 'sexo', 'edad', 'tipo', 'se')
            ->get();

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

        return view('informes.trans2', compact(
            'anos', 'anoDefault', 'meses', 'mesDefault', 'semanasMes',
            'sections', 'results', 'settings'
        ));
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
                    ['id' => 'polio', 'code' => 'A.80.9', 'label' => 'POLIOMIELITIS', 'diag' => 'POLIOMIELITIS'],
                    ['id' => 'sarampion', 'code' => 'B.05.9', 'label' => 'SARAMPION', 'diag' => 'SARAMPION'],
                    ['id' => 'tosferina', 'code' => 'A.37.9', 'label' => 'TOSFERINA', 'diag' => 'TOSFERINA'],
                    ['id' => 'difteria', 'code' => 'A.36.9', 'label' => 'DIFTERIA', 'diag' => 'DIFTERIA'],
                    ['id' => 'tetanos_neo', 'code' => 'A.33.X', 'label' => 'TETANOS NEONATORUM', 'diag' => 'TETANOS NEONATORUM'],
                    ['id' => 'tetanos_exc', 'code' => 'A.35.X', 'label' => 'TETANOS (Excepto Neonatorum)', 'diag' => 'TETANOS'],
                    ['id' => 'parotiditis', 'code' => 'K.11.2', 'label' => 'PAROTIDITIS', 'diag' => 'PAROTIDITIS'],
                    ['id' => 'rubeola', 'code' => 'B.06.9', 'label' => 'RUBEOLA', 'diag' => 'RUBEOLA'],
                    ['id' => 'rubeola_cong', 'code' => 'P.35.0', 'label' => 'SINDROME DE RUBEOLA CONGENITA', 'diag' => 'RUBEOLA CONGENITA'],
                    ['id' => 'varicela', 'code' => 'B.01.9', 'label' => 'VARICELA', 'diag' => 'VARICELA'],
                ]
            ],
            [
                'title' => 'OTRAS ENFERMEDADES PREVENIBLES',
                'side' => 'obverso',
                'rows' => [
                    ['id' => 'hepatitis_a', 'code' => 'B.15.9', 'label' => 'HEPATITIS "A"', 'diag' => 'HEPATITIS A'],
                    ['id' => 'hepatitis_b', 'code' => 'B.16.9', 'label' => 'HEPATITIS "B"', 'diag' => 'HEPATITIS B'],
                    ['id' => 'hepatitis_c', 'code' => '', 'label' => 'HEPATITIS "C"', 'diag' => 'HEPATITIS C'],
                    ['id' => 'hepatitis_d', 'code' => '', 'label' => 'HEPATITIS "D"', 'diag' => 'HEPATITIS D'],
                ]
            ],
            [
                'title' => 'ENFERMEDADES INTESTINALES',
                'side' => 'obverso',
                'rows' => [
                    ['id' => 'diarrea', 'code' => 'A.09.X', 'label' => 'DIARREA', 'diag' => ['DIARREA', 'DIARREA SIN SANGRE', 'DIARREAS']],
                    ['id' => 'disenteria', 'code' => 'A.09.X', 'label' => 'DISENTERIA', 'diag' => 'DISENTERIA'],
                    ['id' => 'colera', 'code' => 'A.00.9', 'label' => 'COLERA', 'diag' => 'COLERA'],
                    ['id' => 'tifoidea', 'code' => 'A.01.0', 'label' => 'FIEBRE TIFOIDEA Y PARATIFOIDEA', 'diag' => ['FIEBRE TIFOIDEA', 'PARATIFOIDEA']],
                ]
            ],
            [
                'title' => 'ENFERMEDADES RESPIRATORIAS',
                'side' => 'obverso',
                'rows' => [
                    ['id' => 'bronquitis', 'code' => 'J40.9-J21.9', 'label' => 'BRONQUITIS', 'diag' => 'BRONQUITIS'],
                    ['id' => 'asma', 'code' => '', 'label' => 'ASMA', 'diag' => 'ASMA'],
                    ['id' => 'neumonia', 'code' => 'J18.9-J10.0', 'label' => 'NEUMONÍA / BRONCONEUMONÍA', 'diag' => ['NEUMONIA', 'BRONCONEUMONIA']],
                    ['id' => 'faringo', 'code' => 'J02.0-J03.0', 'label' => 'FARINGO AMIGDALITIS', 'diag' => ['FARINGOAMIGDALITIS', 'FARINGITIS', 'FARINGOAMIGDALITIS ESTREPTOCOCICA', 'FARINGITIS ESTREPTOCOCICA', 'AMIGDALITIS AGUDA', 'AMIGDALITIS']],
                    ['id' => 'tuberculosis', 'code' => 'A.16.4', 'label' => 'TUBERCULOSIS RESPIRATORIA', 'diag' => 'TUBERCULOSIS'],
                    ['id' => 'covid', 'code' => 'U07.2', 'label' => 'INFECCIÓN POR COVID 19', 'diag' => ['COVID-19', 'CORONAVIRUS']],
                ]
            ],
            [
                'title' => 'INFECCIONES MENINGEAS',
                'side' => 'obverso',
                'rows' => [
                    ['id' => 'meningitis_tub', 'code' => 'A.17.0', 'label' => 'MENINGITIS TUBERCULOSA', 'diag' => 'MENINGITIS TUBERCULOSA'],
                    ['id' => 'meningitis_men', 'code' => 'A.39.0', 'label' => 'MENINGITIS MENINGOCOCICA', 'diag' => 'MENINGITIS MENINGOCOCICA'],
                    ['id' => 'otras_men', 'code' => 'G03.0-G03.9', 'label' => 'OTRAS MENINGITIS', 'diag' => 'OTRAS MENINGITIS'],
                ]
            ],
            [
                'title' => 'ENFERMEDADES VECTORIALES',
                'side' => 'reverso',
                'rows' => [
                    ['id' => 'malaria', 'code' => 'B.54.X', 'label' => 'MALARIA CASOS CONFIRMADOS', 'diag' => 'MALARIA'],
                    ['id' => 'dengue_ss', 'code' => 'A.90.X', 'label' => 'DENGUE SIN SIGNOS DE ALARMA', 'diag' => 'DENGUE SIN SIGNOS DE ALARMA'],
                    ['id' => 'dengue_cs', 'code' => '', 'label' => 'DENGUE CON SIGNOS DE ALARMA', 'diag' => 'DENGUE CON SIGNOS DE ALARMA'],
                    ['id' => 'dengue_grave', 'code' => 'A.91.X', 'label' => 'DENGUE GRAVE', 'diag' => 'DENGUE GRAVE'],
                    ['id' => 'chikungunya', 'code' => 'A.92.0', 'label' => 'CHIKUNGUNYA', 'diag' => 'CHIKUNGUNYA'],
                    ['id' => 'zika', 'code' => '', 'label' => 'ZIKA, NO ESPECIFICADO (EXCEPTO EMB.)', 'diag' => 'ZIKA'],
                    ['id' => 'zika_emb', 'code' => '', 'label' => 'ZIKA EMBARAZADAS', 'diag' => 'ZIKA EMBARAZADAS'],
                    ['id' => 'leishmaniasis_v', 'code' => 'B.55.0', 'label' => 'LEISHMANIASIS VICERAL', 'diag' => 'LEISHMANIASIS VISCERAL'],
                    ['id' => 'leishmaniasis_c', 'code' => 'B.55.1', 'label' => 'LEISHMANIASIS CUTANEA', 'diag' => 'LEISHMANIASIS CUTANEA'],
                    ['id' => 'chagas_a', 'code' => 'B.57.1', 'label' => 'CHAGAS AGUDO', 'diag' => 'CHAGAS AGUDO'],
                    ['id' => 'chagas_c', 'code' => 'B.57.2', 'label' => 'CHAGAS CRONICO', 'diag' => 'CHAGAS CRONICO'],
                ]
            ],
            [
                'title' => 'INFECCIONES DE TRANSMISIÓN SEXUAL',
                'side' => 'reverso',
                'rows' => [
                    ['id' => 'sifilis', 'code' => 'A.53.9', 'label' => 'SIFILIS', 'diag' => 'SIFILIS'],
                    ['id' => 'gonorrea', 'code' => 'A.54.9', 'label' => 'GONORREA', 'diag' => 'GONORREA'],
                    ['id' => 'sida', 'code' => 'B.24.9', 'label' => 'SIDA', 'diag' => 'SIDA'],
                    ['id' => 'herpes', 'code' => 'A.60.0', 'label' => 'HERPES GENITAL', 'diag' => 'HERPES GENITAL'],
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

    public function getDetails(Request $request)
    {
        $ano = $request->input('ano');
        $se = $request->input('se');
        $rowId = $request->input('row_id');
        $range = $request->input('range');

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

        if (!$targetRow) return response()->json(['error' => 'No encontrado'], 404);

        $diags = is_array($targetRow['diag']) ? $targetRow['diag'] : [$targetRow['diag']];
        $diagsNorm = array_map([$this, 'normalizeForMatch'], $diags);

        $rawData = Informe::where('ano', $ano)->where('se', $se)->where('cond_diagnostico', 'N')->get()
            ->filter(fn($r) => in_array($this->normalizeForMatch($r->diagnostico), $diagsNorm));

        $filteredData = $rawData->filter(fn($r) => $range === 'total' || $this->getAgeRange($r) === $range);

        $details = $filteredData->map(fn($r) => [
            'fecha' => $r->fecha, 'exp' => $r->exp, 'sexo' => $r->sexo,
            'edad' => $r->edad . ' ' . $r->tipo, 'diagnostico' => $r->diagnostico, 'medico' => $r->medico
        ])->values();

        return response()->json([
            'label' => $targetRow['label'],
            'count' => $details->count(),
            'details' => $details,
            'summaryByDay' => $filteredData->groupBy('fecha')->map(fn($g) => $g->count())->sortKeys(),
            'summaryByRange' => $filteredData->groupBy(fn($r) => $this->getAgeRange($r))->map(fn($g) => $g->count())
        ]);
    }
}
