<?php

namespace App\Http\Controllers\Informes;

use App\Http\Controllers\Controller;
use App\Models\Informe;
use App\Models\RegistroGlobal;
use App\Traits\InformesHelperTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ItsController extends Controller
{
    use InformesHelperTrait;

    public function __construct(private \App\Services\RegistroGlobalService $service)
    {
    }

    public function index(Request $request)
    {
        $ano = $request->input('ano');
        if (empty($ano)) {
            $latestAnoRG = RegistroGlobal::max('ano');
            $latestAnoInf = Informe::max('ano');
            $ano = max($latestAnoRG ?: date('Y'), $latestAnoInf ?: date('Y'));
        }

        $mes = $request->input('mes', '');
        if (empty($mes))
            $mes = $this->resolverMesPorDefecto((string)$ano);

        $jornada = $request->input('jornada', 'TODAS') ?: 'TODAS';

        $anos = $this->getAnosDisponibles();
        $meses = $this->getMesesDisponibles($ano);
        $jornadas = $this->getJornadasDisponibles();

        $itsDef = [
            'SINDRÓMICO' => [
                ['label' => 'SECRECION URETRAL', 'diag' => 'SECRECION URETRAL', 'code' => '190'],
                ['label' => 'CERVICITIS', 'diag' => 'CERVICITIS'],
                ['label' => 'VAGINITIS', 'diag' => 'VAGINITIS', 'code' => '192'],
                ['label' => 'ULCERA GENITAL', 'diag' => 'ULCERA GENITAL', 'code' => '193'],
                ['label' => 'EPI', 'diag' => 'EPI', 'code' => '194'],
                ['label' => 'BUBON INGUINAL', 'diag' => 'BUBON INGUINAL'],
            ],
            'CLÍNICO' => [
                ['label' => 'MOLLUSCO CONTAGIOSO', 'diag' => 'MOLUSCO CONTAGIOSO'],
                ['label' => 'GRANULOMA INGUINAL', 'diag' => 'GRANULOMA INGUINAL'],
                ['label' => 'CONDILOMA ACUMINADO', 'diag' => 'CONDILOMA ACUMINADO'],
            ],
            'C/E' => [
                ['label' => 'VAGINOSIS BACTERIANA', 'diag' => 'VAGINOSIS BACTERIANA'],
            ],
            'ETIOLÓGICO' => [
                ['label' => 'SIFILIS CONGENITA', 'diag' => 'SIFILIS CONGENITA', 'code' => '200'],
                ['label' => 'SIFILIS', 'diag' => 'SIFILIS'],
                ['label' => 'CLAMYDIA TRACHOMATIS', 'diag' => 'CLAMYDIA TRACHOMATIS'],
                ['label' => 'TRICHOMONAS', 'diag' => 'TRICHOMONAS'],
                ['label' => 'CANDIDA ALBICANS', 'diag' => 'CANDIDA ALBICANS'],
                ['label' => 'GONORREA', 'diag' => ['GONORREA', 'FICHA DE VIGILANCIA EPIDEMIOLÓGICA.']],
                ['label' => 'HERPES GENITAL', 'diag' => 'HERPES GENITAL', 'code' => '206'],
                ['label' => 'HEPATITIS B', 'diag' => 'HEPATITIS B'],
            ],
        ];

        $query = Informe::query()->where('ano', $ano)->where('mes', $mes);
        if ($jornada != 'TODAS')
            $query->where('jornada', $jornada);
        $allRecords = $query->get();

        if ($allRecords->isEmpty()) {
            $hasRG = RegistroGlobal::where('ano', $ano)->where('mes', $mes)->exists();
            if ($hasRG) {
                try {
                    app(\App\Http\Controllers\Informes\At1Controller::class)->syncSelective([$ano], [$mes]);
                    $allRecords = $query->get();
                } catch (\Throwable $e) {}
            }
        }

        $prenatalDiags = [
            'ATENCION PRENATAL ANTES DE LAS 12 SG',
            'ATENCION PRENATAL DESPUES DE LAS 12 SG',
            'ATENCION PRENATAL EDAD GESTACIONAL NO CONSIGNADA',
            'ATENCION PRENATAL', 'PRENATAL',
        ];

        $pregnantRecords = Informe::where('ano', $ano)->where('mes', $mes)
            ->where(function ($q) use ($prenatalDiags) {
            $q->whereIn('cod', ['104', '105', '106'])
                ->orWhere(function ($q2) use ($prenatalDiags) {
                foreach ($prenatalDiags as $diag) {
                    $q2->orWhereRaw('UPPER(diagnostico) LIKE ?', ['%' . $diag . '%']);
                }
            }
            );
        })->pluck('registro_id')->toArray();

        $pregnantRecords = array_unique($pregnantRecords);

        $finalData = [];
        $totalGeneral = array_fill(0, 36, 0);

        foreach ($itsDef as $cat => $rows) {
            foreach ($rows as $rowDef) {
                $matchingInformes = $allRecords->filter(function ($i) use ($rowDef) {
                    if (isset($rowDef['code'])) {
                        $codes = is_array($rowDef['code']) ? $rowDef['code'] : [$rowDef['code']];
                        if (in_array(trim($i->cod), $codes))
                            return true;
                    }
                    if (isset($rowDef['diag'])) {
                        $diags = is_array($rowDef['diag']) ? $rowDef['diag'] : [$rowDef['diag']];
                        $rDiag = strtoupper(trim($i->diagnostico ?? ''));
                        $rDiag = str_replace(['Á', 'É', 'Í', 'Ó', 'Ú', 'Ü', 'Ñ'], ['A', 'E', 'I', 'O', 'U', 'U', 'N'], $rDiag);
                        foreach ($diags as $d) {
                            $searchDiag = strtoupper(trim($d));
                            $searchDiag = str_replace(['Á', 'É', 'Í', 'Ó', 'Ú', 'Ü', 'Ñ'], ['A', 'E', 'I', 'O', 'U', 'U', 'N'], $searchDiag);
                            if (preg_match('/\b' . preg_quote($searchDiag, '/') . '\b/', $rDiag))
                                return true;
                        }
                    }
                    return false;
                });

                $rowCounts = array_fill(0, 36, 0);
                foreach ($matchingInformes as $inf) {
                    $isNew = strtoupper(trim($inf->cond_diagnostico)) == 'N';
                    if ($isNew)
                        $rowCounts[0]++;
                    else
                        $rowCounts[1]++;

                    $isMale = strtoupper(trim($inf->sexo)) == 'H';
                    if ($isMale)
                        $rowCounts[2]++;
                    else
                        $rowCounts[3]++;

                    $ageCol = $this->getAgeColITS($inf);
                    if ($ageCol !== null)
                        $rowCounts[4 + $ageCol]++;

                    $popCol = $this->getPopColITS($inf, $pregnantRecords);
                    if ($popCol !== null)
                        $rowCounts[22 + $popCol]++;
                }

                $finalData[] = [
                    'category' => $cat,
                    'label' => $rowDef['label'],
                    'cols' => $rowCounts,
                    'total' => array_sum(array_slice($rowCounts, 0, 2)),
                ];

                for ($i = 0; $i < 36; $i++)
                    $totalGeneral[$i] += $rowCounts[$i];
            }
        }

        if ($request->ajax()) {
            return view('informes.its_content', compact('anos', 'meses', 'jornadas', 'ano', 'mes', 'jornada', 'finalData', 'totalGeneral'));
        }
        return view('informes.its', compact('anos', 'meses', 'jornadas', 'ano', 'mes', 'jornada', 'finalData', 'totalGeneral'));
    }

    private function getAgeColITS($inf)
    {
        $tipo = strtoupper(trim($inf->tipo));
        $edad = (int)$inf->edad;
        $isMale = strtoupper(trim($inf->sexo)) == 'H';
        $sexOffset = $isMale ? 0 : 1;
        $rangeIdx = -1;

        if ($tipo == 'D' || $tipo == 'M' || ($tipo == 'A' && $edad < 1))
            $rangeIdx = 0;
        elseif ($tipo == 'A') {
            if ($edad >= 1 && $edad <= 4)
                $rangeIdx = 1;
            elseif ($edad >= 5 && $edad <= 9)
                $rangeIdx = 2;
            elseif ($edad >= 10 && $edad <= 14)
                $rangeIdx = 3;
            elseif ($edad >= 15 && $edad <= 19)
                $rangeIdx = 4;
            elseif ($edad >= 20 && $edad <= 24)
                $rangeIdx = 5;
            elseif ($edad >= 25 && $edad <= 29)
                $rangeIdx = 6;
            elseif ($edad >= 30 && $edad <= 49)
                $rangeIdx = 7;
            elseif ($edad >= 50)
                $rangeIdx = 8;
        }
        if ($rangeIdx == -1)
            return null;
        return ($rangeIdx * 2) + $sexOffset;
    }

    private function getPopColITS($inf, $pregnantRecords)
    {
        $isNew = strtoupper(trim($inf->cond_diagnostico)) == 'N';
        $stat = $isNew ? 0 : 1;
        $isMale = strtoupper(trim($inf->sexo)) == 'H';
        $pemb = strtoupper(trim($inf->pg_emb));
        $referidoA = strtoupper(trim($inf->referido_a));
        $referidoDe = strtoupper(trim($inf->referido_de));
        $diagnostico = strtoupper(trim($inf->diagnostico ?? ''));
        $cod = strtoupper(trim($inf->cod ?? ''));

        $isPregnantFromDiag = in_array($cod, ['104', '105', '106'])
            || str_contains($diagnostico, 'ATENCION PRENATAL')
            || str_contains($diagnostico, 'PRENATAL')
            || str_contains($diagnostico, 'EMBARAZADA');

        $isPregnant = in_array($inf->registro_id, $pregnantRecords)
            || $pemb == 'EMBARAZADA' || $pemb == 'EMB' || $isPregnantFromDiag;
        $isTS = str_contains($pemb, 'TS') || str_contains($referidoA, 'TS') || str_contains($referidoDe, 'TS');

        if (str_contains($referidoDe, 'CONTAC') || str_contains($referidoA, 'CONTAC'))
            return ($isMale ? 12 : 13);
        if ($isTS) {
            if ($isPregnant)
                return 10 + $stat;
            return ($isMale ? 6 : 8) + $stat;
        }
        elseif ($isPregnant) {
            return 4 + $stat;
        }
        else {
            return ($isMale ? 0 : 2) + $stat;
        }
    }

    /**
     * Obtener detalles de atenciones para una celda seleccionada en Informe ITS
     */
    public function details(Request $request)
    {
        $ano = $request->input('ano', date('Y'));
        $mes = $request->input('mes', '');
        $jornada = $request->input('jornada', 'TODAS') ?: 'TODAS';
        $label = $request->input('label', '');
        $colIdx = $request->has('col') ? (int)$request->input('col') : null;

        $itsDef = [
            'SINDRÓMICO' => [
                ['label' => 'SECRECION URETRAL', 'diag' => 'SECRECION URETRAL', 'code' => '190'],
                ['label' => 'CERVICITIS', 'diag' => 'CERVICITIS'],
                ['label' => 'VAGINITIS', 'diag' => 'VAGINITIS', 'code' => '192'],
                ['label' => 'ULCERA GENITAL', 'diag' => 'ULCERA GENITAL', 'code' => '193'],
                ['label' => 'EPI', 'diag' => 'EPI', 'code' => '194'],
                ['label' => 'BUBON INGUINAL', 'diag' => 'BUBON INGUINAL'],
            ],
            'CLÍNICO' => [
                ['label' => 'MOLLUSCO CONTAGIOSO', 'diag' => 'MOLUSCO CONTAGIOSO'],
                ['label' => 'GRANULOMA INGUINAL', 'diag' => 'GRANULOMA INGUINAL'],
                ['label' => 'CONDILOMA ACUMINADO', 'diag' => 'CONDILOMA ACUMINADO'],
            ],
            'C/E' => [
                ['label' => 'VAGINOSIS BACTERIANA', 'diag' => 'VAGINOSIS BACTERIANA'],
            ],
            'ETIOLÓGICO' => [
                ['label' => 'SIFILIS CONGENITA', 'diag' => 'SIFILIS CONGENITA', 'code' => '200'],
                ['label' => 'SIFILIS', 'diag' => 'SIFILIS'],
                ['label' => 'CLAMYDIA TRACHOMATIS', 'diag' => 'CLAMYDIA TRACHOMATIS'],
                ['label' => 'TRICHOMONAS', 'diag' => 'TRICHOMONAS'],
                ['label' => 'CANDIDA ALBICANS', 'diag' => 'CANDIDA ALBICANS'],
                ['label' => 'GONORREA', 'diag' => ['GONORREA', 'FICHA DE VIGILANCIA EPIDEMIOLÓGICA.']],
                ['label' => 'HERPES GENITAL', 'diag' => 'HERPES GENITAL', 'code' => '206'],
                ['label' => 'HEPATITIS B', 'diag' => 'HEPATITIS B'],
            ],
        ];

        $query = Informe::query()->where('ano', $ano)->where('mes', $mes);
        if ($jornada != 'TODAS') {
            $query->where('jornada', $jornada);
        }
        $allRecords = $query->get();

        $prenatalDiags = [
            'ATENCION PRENATAL ANTES DE LAS 12 SG',
            'ATENCION PRENATAL DESPUES DE LAS 12 SG',
            'ATENCION PRENATAL EDAD GESTACIONAL NO CONSIGNADA',
            'ATENCION PRENATAL', 'PRENATAL',
        ];

        $pregnantRecords = Informe::where('ano', $ano)->where('mes', $mes)
            ->where(function ($q) use ($prenatalDiags) {
                $q->whereIn('cod', ['104', '105', '106'])
                    ->orWhere(function ($q2) use ($prenatalDiags) {
                        foreach ($prenatalDiags as $diag) {
                            $q2->orWhereRaw('UPPER(diagnostico) LIKE ?', ['%' . $diag . '%']);
                        }
                    });
            })->pluck('registro_id')->toArray();
        $pregnantRecords = array_unique($pregnantRecords);

        // Buscar la patología por su etiqueta
        $rowDef = null;
        foreach ($itsDef as $cat => $rows) {
            foreach ($rows as $r) {
                if (strtoupper(trim($r['label'])) === strtoupper(trim($label))) {
                    $rowDef = $r;
                    break 2;
                }
            }
        }

        if (!$rowDef) {
            return response()->json(['success' => false, 'message' => 'Patología no encontrada'], 404);
        }

        // Filtrar registros de la patología seleccionada
        $matching = $allRecords->filter(function ($i) use ($rowDef) {
            if (isset($rowDef['code'])) {
                $codes = is_array($rowDef['code']) ? $rowDef['code'] : [$rowDef['code']];
                if (in_array(trim($i->cod), $codes))
                    return true;
            }
            if (isset($rowDef['diag'])) {
                $diags = is_array($rowDef['diag']) ? $rowDef['diag'] : [$rowDef['diag']];
                $rDiag = strtoupper(trim($i->diagnostico ?? ''));
                $rDiag = str_replace(['Á', 'É', 'Í', 'Ó', 'Ú', 'Ü', 'Ñ'], ['A', 'E', 'I', 'O', 'U', 'U', 'N'], $rDiag);
                foreach ($diags as $d) {
                    $searchDiag = strtoupper(trim($d));
                    $searchDiag = str_replace(['Á', 'É', 'Í', 'Ó', 'Ú', 'Ü', 'Ñ'], ['A', 'E', 'I', 'O', 'U', 'U', 'N'], $searchDiag);
                    if (preg_match('/\b' . preg_quote($searchDiag, '/') . '\b/', $rDiag))
                        return true;
                }
            }
            return false;
        });

        // Títulos explicativos por índice de columna (0 a 35)
        $columnTitles = [
            0 => 'Diagnóstico: Nuevo (N)',
            1 => 'Diagnóstico: Subsecuente (S)',
            2 => 'Sexo: Hombre (H)',
            3 => 'Sexo: Mujer (M)',
            4 => 'Grupo de Edad: < 1 Año (H)',
            5 => 'Grupo de Edad: < 1 Año (M)',
            6 => 'Grupo de Edad: 1-4 Años (H)',
            7 => 'Grupo de Edad: 1-4 Años (M)',
            8 => 'Grupo de Edad: 5-9 Años (H)',
            9 => 'Grupo de Edad: 5-9 Años (M)',
            10 => 'Grupo de Edad: 10-14 Años (H)',
            11 => 'Grupo de Edad: 10-14 Años (M)',
            12 => 'Grupo de Edad: 15-19 Años (H)',
            13 => 'Grupo de Edad: 15-19 Años (M)',
            14 => 'Grupo de Edad: 20-24 Años (H)',
            15 => 'Grupo de Edad: 20-24 Años (M)',
            16 => 'Grupo de Edad: 25-29 Años (H)',
            17 => 'Grupo de Edad: 25-29 Años (M)',
            18 => 'Grupo de Edad: 30-49 Años (H)',
            19 => 'Grupo de Edad: 30-49 Años (M)',
            20 => 'Grupo de Edad: 50+ Años (H)',
            21 => 'Grupo de Edad: 50+ Años (M)',
            22 => 'Población: PG HOM (N)',
            23 => 'Población: PG HOM (S)',
            24 => 'Población: PG MUJ (N)',
            25 => 'Población: PG MUJ (S)',
            26 => 'Población: PG EMB (N)',
            27 => 'Población: PG EMB (S)',
            28 => 'Población: TS HOM (N)',
            29 => 'Población: TS HOM (S)',
            30 => 'Población: TS MUJ (N)',
            31 => 'Población: TS MUJ (S)',
            32 => 'Población: TS EMB (N)',
            33 => 'Población: TS EMB (S)',
            34 => 'Población: Contacto (H)',
            35 => 'Población: Contacto (M)',
        ];

        $colName = ($colIdx !== null && isset($columnTitles[$colIdx])) ? $columnTitles[$colIdx] : 'Todos los Registros';

        if ($colIdx !== null) {
            $matching = $matching->filter(function($inf) use ($colIdx, $pregnantRecords) {
                if ($colIdx === 0) return strtoupper(trim($inf->cond_diagnostico)) == 'N';
                if ($colIdx === 1) return strtoupper(trim($inf->cond_diagnostico)) != 'N';
                if ($colIdx === 2) return strtoupper(trim($inf->sexo)) == 'H';
                if ($colIdx === 3) return strtoupper(trim($inf->sexo)) != 'H';
                if ($colIdx >= 4 && $colIdx <= 21) {
                    return $this->getAgeColITS($inf) === ($colIdx - 4);
                }
                if ($colIdx >= 22 && $colIdx <= 35) {
                    return $this->getPopColITS($inf, $pregnantRecords) === ($colIdx - 22);
                }
                return true;
            });
        }

        $recordsFormatted = $matching->map(function($i) {
            $tipoStr = match(strtoupper(trim($i->tipo))) {
                'A' => 'Años',
                'M' => 'Meses',
                'D' => 'Días',
                default => strtoupper(trim($i->tipo))
            };
            $fechaFormatted = $i->fecha ? \Carbon\Carbon::parse($i->fecha)->format('d/m/Y') : '-';

            return [
                'id' => $i->id,
                'fecha' => $fechaFormatted,
                'medico' => $i->medico ?: 'No consignado',
                'prof' => $i->prof ?: '',
                'edad' => trim($i->edad . ' ' . $tipoStr),
                'sexo' => strtoupper(trim($i->sexo)) == 'H' ? 'Hombre (H)' : 'Mujer (M)',
                'exp' => $i->exp ?: '-',
                'numero' => $i->numero ?: '-',
                'cond' => strtoupper(trim($i->cond_diagnostico)) == 'N' ? 'Nueva (N)' : 'Subsecuente (S)',
                'diagnostico' => $i->diagnostico ?: '-',
                'cod' => $i->cod ?: '-'
            ];
        })->values();

        return response()->json([
            'success' => true,
            'label' => $rowDef['label'],
            'columna' => $colName,
            'total' => $recordsFormatted->count(),
            'records' => $recordsFormatted
        ]);
    }
}
