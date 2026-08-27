<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class DashboardEpiController extends Controller
{
    // ─── Agrupaciones de diagnósticos ────────────────────────────────────────

    /** Palabras clave / códigos que identifican cada enfermedad.
     *  Se busca con LIKE en cualquiera de los campos diagnostico_1..7 */
    protected $grupos = [
        'hidricas' => [
            'label' => 'Enfermedades Hídricas',
            'color' => 'primary',
            'icon' => 'fa-tint',
            'enfermedades' => [
                'DIARREAS' => ['DIARREA'],
                'DISENTERÍA' => ['DISENTERIA', 'DISENTERÍA'],
                'CÓLERA' => ['COLERA', 'CÓLERA'],
                'HEPATITIS A' => ['HEPATITIS A'],
                'FIEBRE TIFOIDEA' => ['FIEBRE TIFOIDEA', 'TIFOIDEA'],
                'PARASITOSIS INT.' => ['PARASITOSIS', 'GIARDIASIS', 'AMIBIASIS'],
                'GASTROENTERITIS' => ['GASTROENTERITIS'],
            ],
        ],
        'arbovirosis' => [
            'label' => 'Arbovirosis',
            'color' => 'danger',
            'icon' => 'fa-bug',
            'enfermedades' => [
                'DENGUE C/SIGNOS' => ['DENGUE CON SIGNOS', 'SOSPECHA DENGUE CON'],
                'DENGUE S/SIGNOS' => ['DENGUE SIN SIGNOS', 'SOSPECHA DENGUE SIN'],
                'DENGUE GRAVE' => ['DENGUE GRAVE'],
                'ZIKA' => ['ZIKA'],
                'CHIKUNGUNYA' => ['CHIKUNGUNYA', 'CHIKUNGUÑA'],
                'FIEBRE AMARILLA' => ['FIEBRE AMARILLA'],
            ],
        ],
        'eti' => [
            'label' => 'Enfermedades Tipo Influenza (ETI)',
            'color' => 'info',
            'icon' => 'fa-head-side-cough',
            'enfermedades' => [
                'RESFRÍO COMÚN' => ['RESFRIO COMUN', 'RESFRIADO COMUN', 'CATARRO COMUN'],
                'FARINGITIS' => ['FARINGITIS'],
                'FARINGITIS VIRAL' => ['FARINGITIS VIRAL'],
                'FARINGOAMIG. ESTREP.' => ['FARINGOAMIGDALITIS ESTREPTOCOCICA', 'FARINGOAMIGDALITIS ESTREPTOCÓCICA'],
                'INFLUENZA' => ['INFLUENZA', 'GRIPE'],
                'AMIGDALITIS' => ['AMIGDALITIS'],
                'BRONQUITIS AGUDA' => ['BRONQUITIS AGUDA'],
                'NEUMONÍA' => ['NEUMONIA', 'NEUMONÍA'],
            ],
        ],
        'desatendidas' => [
            'label' => 'Enfermedades Desatendidas',
            'color' => 'warning',
            'icon' => 'fa-virus',
            'enfermedades' => [
                'CHAGAS' => ['CHAGAS'],
                'LEISHMANIASIS' => ['LEISHMANIASIS'],
                'MORDEDURA DE PERRO' => ['MORDEDURA DE PERRO', 'MORDEDURA PERRO'],
                'RABIA' => ['RABIA'],
                'TRACOMA' => ['TRACOMA'],
                'ONCOCERCOSIS' => ['ONCOCERCOSIS'],
                'LEPRA' => ['LEPRA'],
                'FILARIASIS' => ['FILARIASIS'],
            ],
        ],
        'prevenibles' => [
            'label' => 'Enfermedades Prevenibles por Vacunación',
            'color' => 'success',
            'icon' => 'fa-syringe',
            'enfermedades' => [
                'SARAMPIÓN' => ['SARAMPION', 'SARAMPIÓN'],
                'RUBÉOLA' => ['RUBEOLA', 'RUBÉOLA'],
                'PAPERAS' => ['PAPERAS', 'PAROTIDITIS'],
                'POLIOMIELITIS' => ['POLIOMELITIS', 'POLIOMIELITIS'],
                'TÉTANOS' => ['TETANOS', 'TÉTANOS'],
                'TOS FERINA' => ['TOS FERINA', 'PERTUSSIS'],
                'VARICELA' => ['VARICELA'],
                'HEPATITIS B' => ['HEPATITIS B'],
                'DIFTERIA' => ['DIFTERIA'],
            ],
        ],
    ];

    // ─── Index ────────────────────────────────────────────────────────────────

    public function index(Request $request)
    {
        $aniosDisponibles = DB::table('registros_globales')
            ->select('ano')->distinct()->orderByDesc('ano')->pluck('ano');

        $seActual = (int) Carbon::now()->format('W');
        $anioActual = (int) date('Y');

        $selectedAno = $request->input('ano', $anioActual);
        $selectedMes = $request->input('mes', '');
        $selectedSe = $request->input('se', '');

        $mesesNomina = [
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre',
        ];
        $mesesNumericos = [
            'enero' => 1,
            'febrero' => 2,
            'marzo' => 3,
            'abril' => 4,
            'mayo' => 5,
            'junio' => 6,
            'julio' => 7,
            'agosto' => 8,
            'septiembre' => 9,
            'octubre' => 10,
            'noviembre' => 11,
            'diciembre' => 12,
            '1' => 1,
            '2' => 2,
            '3' => 3,
            '4' => 4,
            '5' => 5,
            '6' => 6,
            '7' => 7,
            '8' => 8,
            '9' => 9,
            '10' => 10,
            '11' => 11,
            '12' => 12,
        ];

        $mesesDisponibles = DB::table('registros_globales')
            ->select('mes')->distinct()
            ->when($selectedAno, fn($q) => $q->where('ano', $selectedAno))
            ->get()
            ->sortBy(fn($i) => $mesesNumericos[strtolower($i->mes)] ?? 99)
            ->map(fn($i) => [
                'id' => $i->mes,
                'nombre' => $mesesNomina[$mesesNumericos[strtolower($i->mes)] ?? 0] ?? ucfirst($i->mes),
            ]);

        $seDisponibles = DB::table('registros_globales')
            ->select('se')->whereNotNull('se')->distinct()
            ->when($selectedAno, fn($q) => $q->where('ano', $selectedAno))
            ->when($selectedMes, fn($q) => $q->where('mes', $selectedMes))
            ->orderBy(DB::raw('CAST(se AS UNSIGNED)'))
            ->pluck('se');

        // Build groups data
        $groupsData = $this->buildGroupsData($selectedAno, $selectedMes, $selectedSe, $seActual, $mesesNumericos);

        return view('dashboard_epi', compact(
            'groupsData',
            'aniosDisponibles',
            'mesesDisponibles',
            'seDisponibles',
            'selectedAno',
            'selectedMes',
            'selectedSe',
            'seActual',
            'anioActual',
        ));
    }

    public function getFilters(Request $request)
    {
        $selectedAno = $request->input('ano');
        $selectedMes = $request->input('mes');

        $mesesTraduccion = [
            'enero' => 'Enero',
            'febrero' => 'Febrero',
            'marzo' => 'Marzo',
            'abril' => 'Abril',
            'mayo' => 'Mayo',
            'junio' => 'Junio',
            'julio' => 'Julio',
            'agosto' => 'Agosto',
            'septiembre' => 'Septiembre',
            'octubre' => 'Octubre',
            'noviembre' => 'Noviembre',
            'diciembre' => 'Diciembre',
            1 => 'Enero',
            2 => 'Febrero',
            3 => 'Marzo',
            4 => 'Abril',
            5 => 'Mayo',
            6 => 'Junio',
            7 => 'Julio',
            8 => 'Agosto',
            9 => 'Septiembre',
            10 => 'Octubre',
            11 => 'Noviembre',
            12 => 'Diciembre'
        ];

        $mesesNumericos = [
            'enero' => 1,
            'febrero' => 2,
            'marzo' => 3,
            'abril' => 4,
            'mayo' => 5,
            'junio' => 6,
            'julio' => 7,
            'agosto' => 8,
            'septiembre' => 9,
            'octubre' => 10,
            'noviembre' => 11,
            'diciembre' => 12,
            '1' => 1,
            '2' => 2,
            '3' => 3,
            '4' => 4,
            '5' => 5,
            '6' => 6,
            '7' => 7,
            '8' => 8,
            '9' => 9,
            '10' => 10,
            '11' => 11,
            '12' => 12
        ];

        $meses = DB::table('registros_globales')
            ->select('mes')->distinct()
            ->when($selectedAno, fn($q) => $q->where('ano', $selectedAno))
            ->get()
            ->sortBy(fn($i) => $mesesNumericos[strtolower($i->mes)] ?? 99)
            ->map(fn($i) => [
                'id' => $i->mes,
                'nombre' => $mesesTraduccion[strtolower($i->mes)] ?? (is_numeric($i->mes) ? ($mesesTraduccion[(int) $i->mes] ?? $i->mes) : ucfirst($i->mes)),
            ])->values();

        $semanas = DB::table('registros_globales')
            ->select('se')->whereNotNull('se')->where('se', '!=', '')->distinct()
            ->when($selectedAno, fn($q) => $q->where('ano', $selectedAno))
            ->when($selectedMes, fn($q) => $q->where('mes', $selectedMes))
            ->orderBy(DB::raw('CAST(se AS UNSIGNED)'))
            ->pluck('se');

        return response()->json([
            'meses' => $meses,
            'semanas' => $semanas
        ]);
    }

    // ─── AJAX endpoint ────────────────────────────────────────────────────────

    public function getData(Request $request)
    {
        $seActual = (int) Carbon::now()->format('W');
        $mesesNumericos = [
            'enero' => 1,
            'febrero' => 2,
            'marzo' => 3,
            'abril' => 4,
            'mayo' => 5,
            'junio' => 6,
            'julio' => 7,
            'agosto' => 8,
            'septiembre' => 9,
            'octubre' => 10,
            'noviembre' => 11,
            'diciembre' => 12,
            '1' => 1,
            '2' => 2,
            '3' => 3,
            '4' => 4,
            '5' => 5,
            '6' => 6,
            '7' => 7,
            '8' => 8,
            '9' => 9,
            '10' => 10,
            '11' => 11,
            '12' => 12,
        ];

        $selectedAno = $request->input('ano');
        $selectedMes = $request->input('mes', '');
        $selectedSe = $request->input('se', '');

        $data = $this->buildGroupsData($selectedAno, $selectedMes, $selectedSe, $seActual, $mesesNumericos);
        return response()->json($data);
    }

    // ─── Core logic ──────────────────────────────────────────────────────────

    private function buildGroupsData($ano, $mes, $se, $seActual, $mesesNumericos): array
    {
        // 1. Obtener datos filtrados (Dataset A)
        $qFiltered = DB::table('registros_globales')
            ->select('diagnostico_1', 'diagnostico_2', 'diagnostico_3', 'diagnostico_4', 'diagnostico_5', 'diagnostico_6', 'diagnostico_7', 'cond', 'se', 'ano', 'mes');

        if ($ano)
            $qFiltered->where('ano', $ano);
        if ($mes && $mes !== '')
            $qFiltered->where('mes', $mes);
        if ($se && $se !== '')
            $qFiltered->where('se', $se);

        $filteredRecords = $qFiltered->get()->map(function ($r) {
            $r->diags = [];
            for ($i = 1; $i <= 7; $i++) {
                $f = "diagnostico_$i";
                if ($r->$f)
                    $r->diags[] = strtoupper(trim($r->$f));
            }
            $r->cond_u = strtoupper(trim($r->cond ?? ''));
            return $r;
        });

        // 2. Obtener datos de Semana Actual (Dataset B)
        // Solo si la semana actual no está ya incluida en los filtros o para asegurar precisión inter-anual
        $seActualRecords = DB::table('registros_globales')
            ->select('diagnostico_1', 'diagnostico_2', 'diagnostico_3', 'diagnostico_4', 'diagnostico_5', 'diagnostico_6', 'diagnostico_7', 'se')
            ->where('ano', date('Y'))
            ->where('se', $seActual)
            ->get()->map(function ($r) {
                $r->diags = [];
                for ($i = 1; $i <= 7; $i++) {
                    $f = "diagnostico_$i";
                    if ($r->$f)
                        $r->diags[] = strtoupper(trim($r->$f));
                }
                return $r;
            });

        $result = [];

        foreach ($this->grupos as $grupoKey => $grupo) {
            $result[$grupoKey] = [
                'label' => $grupo['label'],
                'color' => $grupo['color'],
                'icon' => $grupo['icon'],
                'cards' => [],
            ];

            foreach ($grupo['enfermedades'] as $enfNombre => $keywords) {
                // Preparar keywords en mayúsculas
                $upperKeywords = array_map(fn($k) => strtoupper(trim($k)), $keywords);

                // Función de matching
                $matches = function ($record) use ($upperKeywords) {
                    foreach ($record->diags as $d) {
                        foreach ($upperKeywords as $kw) {
                            if (str_contains($d, $kw))
                                return true;
                        }
                    }
                    return false;
                };

                // Contar en Dataset A (Filtrados)
                $total = 0;
                $nuevas = 0;
                $subsec = 0;

                foreach ($filteredRecords as $r) {
                    if ($matches($r)) {
                        $total++;
                        if ($r->cond_u === 'N')
                            $nuevas++;
                        elseif ($r->cond_u === 'S')
                            $subsec++;
                    }
                }

                // Contar en Dataset B (Semana Actual)
                $seActualCnt = 0;
                foreach ($seActualRecords as $r) {
                    if ($matches($r))
                        $seActualCnt++;
                }

                $result[$grupoKey]['cards'][$enfNombre] = [
                    'total' => $total,
                    'nuevas' => $nuevas,
                    'subsecuentes' => $subsec,
                    'semana_actual' => $seActualCnt,
                ];
            }
        }

        return $result;
    }

}
