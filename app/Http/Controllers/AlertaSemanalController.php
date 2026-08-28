<?php

namespace App\Http\Controllers;

use App\Models\RegistroGlobal;
use App\Models\Setting;
use App\Traits\InformesHelperTrait;
use Illuminate\Http\Request;
use Carbon\Carbon;

class AlertaSemanalController extends Controller
{
    use InformesHelperTrait;

    public function __construct(private \App\Services\RegistroGlobalService $service)
    {
    }

    public function index(Request $request)
    {
        // 1. Obtener AÑOS disponibles en la base de datos
        $anos = RegistroGlobal::distinct()->orderBy('ano', 'desc')->pluck('ano');
        if ($anos->isEmpty()) {
            $anos = collect([(int) date('Y')]);
        }

        $anoDefault = $request->input('ano', (int) date('Y'));
        // Si el año solicitado no está en la lista (y no es el actual), usar el primero disponible
        if (!$anos->contains($anoDefault)) {
            $anoDefault = $anos->first();
        }

        // 2. Obtener MESES disponibles para el AÑO seleccionado
        $mesesDisponibles = RegistroGlobal::where('ano', $anoDefault)
            ->distinct()
            ->pluck('mes')
            ->toArray();

        $mesMap = [
            1 => 'ENERO',
            2 => 'FEBRERO',
            3 => 'MARZO',
            4 => 'ABRIL',
            5 => 'MAYO',
            6 => 'JUNIO',
            7 => 'JULIO',
            8 => 'AGOSTO',
            9 => 'SEPTIEMBRE',
            10 => 'OCTUBRE',
            11 => 'NOVIEMBRE',
            12 => 'DICIEMBRE',
        ];
        $ordenMeses = array_flip($mesMap);

        // Ordenar meses cronológicamente
        usort($mesesDisponibles, function ($a, $b) use ($ordenMeses) {
            return ($ordenMeses[strtoupper($a)] ?? 0) <=> ($ordenMeses[strtoupper($b)] ?? 0);
        });

        $meses = $mesesDisponibles;
        if (empty($meses)) {
            $meses = [$this->getMesNombre((int) date('n'))];
        }

        $mesDefault = $request->input('mes');
        // Si no hay mes seleccionado o el seleccionado no existe para este año, usar el último disponible
        if (!$mesDefault || !in_array(strtoupper($mesDefault), array_map('strtoupper', $meses))) {
            $mesDefault = end($meses);
        }

        // 3. Obtener SEMANAS disponibles para el AÑO y MES seleccionado
        $semanas = RegistroGlobal::where('ano', $anoDefault)
            ->where('mes', $mesDefault)
            ->whereNotNull('se')
            ->distinct()
            ->orderBy('se', 'desc')
            ->pluck('se');

        $currentYear = (int) date('Y');
        $currentSE = $this->getSeActual();

        $seDefault = $request->input('se');
        if (!$seDefault || !$semanas->contains($seDefault)) {
            $seDefault = ($anoDefault == $currentYear && $semanas->contains($currentSE)) ? $currentSE : ($semanas->first() ?? 1);
        }

        $settings = Setting::pluck('value', 'key');

        // Definición de filas del Telegrama Semanal
        $rowsDef = $this->getRowsDefinition();

        // Consulta de datos directamente desde RegistroGlobal
        $rgRecords = RegistroGlobal::query()
            ->where('ano', $anoDefault)
            ->where(function($q) use ($seDefault) {
                $q->where('se', $seDefault);
            })
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
            if ((int)$se !== (int)$seDefault) continue;

            for ($i = 1; $i <= 7; $i++) {
                $diag = trim($rg->{"diagnostico_$i"} ?? '');
                if ($diag === '') continue;

                $cond = strtoupper(trim($rg->{"cond_$i"} ?? ($rg->cond ?? '')));
                if ($cond !== 'N') continue;

                $unrolled[] = (object)[
                    'diagnostico' => $diag,
                    'cod' => trim($rg->{"cod_$i"} ?? ''),
                    'sexo' => $rg->sexo,
                    'edad' => $rg->edad,
                    'tipo' => $rg->tipo,
                    'cond_diagnostico' => 'N',
                ];
            }
        }

        $rawData = collect($unrolled);

        $results = [];
        foreach ($rowsDef as $idx => $row) {
            $results[$idx] = [
                'less_1' => 0,
                '1_4' => 0,
                '5_14' => 0,
                '15_plus' => 0,
                'total' => 0
            ];

            if (isset($row['diag'])) {
                $diags = is_array($row['diag']) ? $row['diag'] : [$row['diag']];
                $diagsNorm = array_map([$this, 'normalizeForMatch'], $diags);

                foreach ($rawData as $r) {
                    $rDiagNorm = $this->normalizeForMatch($r->diagnostico);

                    if (in_array($rDiagNorm, $diagsNorm)) {
                        $ageRange = $this->getAgeRange($r);
                        if ($ageRange) {
                            $results[$idx][$ageRange]++;
                            $results[$idx]['total']++;
                        }
                    }
                }
            }
        }

        // Obtener rango de fechas de la semana epidemiológica (estimado)
        $fechaInfo = $this->getDatesFromWeek($anoDefault, $seDefault);

        $viewData = compact(
            'anos',
            'anoDefault',
            'meses',
            'mesDefault',
            'semanas',
            'seDefault',
            'rowsDef',
            'results',
            'settings',
            'fechaInfo'
        );

        if ($request->ajax()) {
            return view('informes.alerta_semanal_content', $viewData);
        }

        return view('informes.alerta_semanal', $viewData);
    }


    private function getMesNombre($n)
    {
        $mesMap = [
            1 => 'ENERO',
            2 => 'FEBRERO',
            3 => 'MARZO',
            4 => 'ABRIL',
            5 => 'MAYO',
            6 => 'JUNIO',
            7 => 'JULIO',
            8 => 'AGOSTO',
            9 => 'SEPTIEMBRE',
            10 => 'OCTUBRE',
            11 => 'NOVIEMBRE',
            12 => 'DICIEMBRE',
        ];
        return $mesMap[$n] ?? 'ENERO';
    }

    private function getSeActual()
    {
        return $this->getSeDeDate(date('Y-m-d'));
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

        $diffDias = $primeroSE1->diffInDays($d);
        $se = (int) floor($diffDias / 7) + 1;

        return $se;
    }

    private function getAgeRange($r)
    {
        $t = strtoupper(trim($r->tipo));
        $e = (int) $r->edad;

        if ($t === 'D' || $t === 'M' || ($t === 'A' && $e < 1)) {
            return 'less_1';
        } elseif ($t === 'A') {
            if ($e >= 1 && $e <= 4)
                return '1_4';
            if ($e >= 5 && $e <= 14)
                return '5_14';
            if ($e >= 15)
                return '15_plus';
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

    private function getRowsDefinition()
    {
        return [
            ['label' => 'DIARREA SIN SANGRE', 'diag' => ['DIARREA SIN SANGRE', 'DIARREAS', 'DIARREAS CON DESHIDRATACION']],
            ['label' => 'DISENTERIA', 'diag' => ['DISENTERIA', 'DISENTERIA BACILAR', 'DISENTERIA AMEBIANA']],
            ['label' => '*COLERA', 'diag' => 'COLERA'],
            ['label' => '*PARALISIS FLACIDA', 'diag' => 'PARALISIS FLACIDA'],
            ['label' => '*SOSPECHOSO SARAMPION', 'diag' => 'SARAMPION'],
            ['label' => '*SOSPECHOSO TOSFERINA', 'diag' => 'TOSFERINA'],
            ['label' => '*DIFTERIA', 'diag' => 'DIFTERIA'],
            ['label' => 'TETANO NEONATAL', 'diag' => 'TETANO NEONATAL'],
            ['label' => 'PAROTIDITIS', 'diag' => 'PAROTIDITIS'],
            ['label' => '*SOSPECHOSO DE RUBEOLA', 'diag' => 'RUBEOLA'],
            ['label' => '*SINDORME CONGENITO DE RUBEOLA', 'diag' => 'SINDROME CONGENITO DE RUBEOLA'],
            ['label' => '*VARICELA', 'diag' => 'VARICELA'],
            ['label' => '*MENINGITIS', 'diag' => 'MENINGITIS'],
            ['label' => '*DENGUE SIN SIGNOS DE ALARMA', 'diag' => ['SOSP. DENGUE SIN SIGNOS DE ALARMA', 'DENGUE SIN SIGNOS DE ALARMA', 'DSSA', 'D.S.S.A', 'SOSPECHA DENGUE SIN SIGNOS DE ALARMA', 'DENGUE S.S.A']],
            ['label' => '*DENGUE CON SIGNOS DE ALARMA', 'diag' => ['SOSP. DENGUE CON SIGNOS DE ALARMA', 'DENGUE CON SIGNOS DE ALARMA', 'DCSA', 'D.C.S.A', 'SOSPECHA DENGUE CON SIGNOS DE ALARMA', 'DENGUE C.S.A']],
            ['label' => '*SOSPECHOSO DENGUE GRAVE', 'diag' => 'DENGUE GRAVE'],
            ['label' => '*SOSPECHOSO CHICUNGUNYA', 'diag' => 'CHIKUNGUNYA'],
            ['label' => '* SOSP. ZIKA', 'diag' => 'ZIKA'],
            ['label' => '* MUJERES EMBARAZADAS SOSP. POR ZIKA', 'diag' => 'MUJERES EMBARAZADAS SOSP. POR ZIKA'],
            ['label' => '*SOSP. GUILLIAN BARRE > 15 AÑOS', 'diag' => 'SINDROME DE GUILLAIN BARRE'],
            ['label' => '*RABIA HUMANA', 'diag' => 'RABIA HUMANA'],
            ['label' => 'HEPATITIS', 'diag' => ['HEPATITIS', 'HEPATITIS INFECCIOSA', 'HEPATITIS B']],
            ['label' => 'NEUMONIA/BRONCONEUMONIA', 'diag' => ['NEUMONIA', 'BRONCONEUMONIA', 'NEUMONIAS', 'BRONCONEUMONIAS']],
            ['label' => '*PESTE', 'diag' => 'PESTE'],
            ['label' => '*FIEBRE AMARILLA', 'diag' => 'FIEBRE AMARILLA'],
            ['label' => '*LEPTOSPIROSIS', 'diag' => 'LEPTOSPIROSIS'],
            ['label' => '*INTOXICACION POR PLAGUICIDAS', 'diag' => 'INTOXICACION POR PLAGUICIDAS'],
            ['label' => 'MORDEDURAS DE ANIMALES, TRANSMISORES DE LA RABIA', 'diag' => 'MORDEDURAS ANIMALES TRASM DE RABIA'],
            ['label' => 'MORDEDURAS DE SERPIENTE', 'diag' => 'MORDEDURA DE SERPIENTE'],
            ['label' => 'MORTALIDAD INFANTIL (MENOR DE 1 AÑO)', 'diag' => 'MUERTE INFANTIL'],
            ['label' => 'MORTALIDAD INFANTIL (1 - 4 AÑOS)', 'diag' => 'MUERTE INFANTIL 1-4'],
            ['label' => 'MORTALIDAD MATERNA', 'diag' => 'MUERTE MATERNA'],
            ['label' => 'NÚMERO DE SINDROME FEBRIL', 'diag' => 'SINDROME FEBRIL'],
            ['label' => 'CONJUNTIVITIS', 'diag' => 'CONJUNTIVITIS'],
            ['label' => 'SOSPECHOSO DE COVID-19', 'diag' => ['COVID-19', 'ATENCION CLINICA POR COVID-19']],
            ['label' => 'CASOS DE E.T.I.', 'diag' => ['RESFRIADO COMUN', 'RESFRIO COMUN', 'FARINGOAMIGDALITIS VIRAL', 'FARINGITIS AGUDA', 'FARINGITIS', 'FARINGOAMIGDALITIS', 'AMIGDALITIS']],
            ['label' => 'CADENA RED DE FRIO', 'diag' => 'RED DE FRIO'],
        ];
    }

    public function getDetails(Request $request)
    {
        $ano = $request->input('ano');
        $se = $request->input('se');
        $idx = $request->input('idx');
        $range = $request->input('range');

        $rowsDef = $this->getRowsDefinition();
        if (!isset($rowsDef[$idx])) {
            return response()->json(['error' => 'Definición no encontrada'], 404);
        }

        $row = $rowsDef[$idx];
        $diags = is_array($row['diag']) ? $row['diag'] : [$row['diag']];
        $diagsNorm = array_map([$this, 'normalizeForMatch'], $diags);

        $query = Informe::where('ano', $ano)
            ->where('se', $se)
            ->where('cond_diagnostico', 'N');

        // Filtrar por los diagnósticos normalizados
        $rawData = $query->get()->filter(function ($r) use ($diagsNorm) {
            return in_array($this->normalizeForMatch($r->diagnostico), $diagsNorm);
        });

        // Filtrar por rango de edad si no es 'total'
        $filteredData = $rawData->filter(function ($r) use ($range) {
            if ($range === 'total')
                return true;
            return $this->getAgeRange($r) === $range;
        });

        $details = $filteredData->map(function ($r) {
            return [
                'fecha' => $r->fecha,
                'exp' => $r->exp,
                'expediente' => $r->exp,
                'sexo' => $r->sexo,
                'edad' => $r->edad . ' ' . $r->tipo,
                'diagnostico' => $r->diagnostico,
                'medico' => $r->medico,
            ];
        })->values();

        // Resumen por día
        $summaryByDay = $filteredData->groupBy('fecha')->map(function ($group) {
            return $group->count();
        })->sortKeys();

        // Resumen por rango de edad
        $summaryByRange = $filteredData->groupBy(function ($r) {
            return $this->getAgeRange($r);
        })->map(function ($group) {
            return $group->count();
        });

        $rangeLabel = $this->getRangeLabel($range);
        $totalCount = $details->count();

        return response()->json([
            'title' => $row['label'],
            'label' => $row['label'],
            'range_label' => $rangeLabel,
            'count' => $totalCount,
            'total' => $totalCount,
            'details' => $details,
            'patients' => $details,
            'summaryByDay' => $summaryByDay,
            'summary_days' => $summaryByDay,
            'summaryByRange' => $summaryByRange,
            'summary_ranges' => $summaryByRange
        ]);
    }

    private function getRangeLabel($range)
    {
        $labels = [
            'less_1' => '< 1 AÑO',
            '1_4' => '1 - 4 AÑOS',
            '5_14' => '5 - 14 AÑOS',
            '15_plus' => '15 Y + AÑOS',
            'total' => 'TOTAL'
        ];
        return $labels[$range] ?? $range;
    }
}
