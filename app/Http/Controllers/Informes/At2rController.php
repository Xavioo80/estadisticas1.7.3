<?php

namespace App\Http\Controllers\Informes;

use App\Http\Controllers\Controller;
use App\Models\Informe;
use App\Models\Diagnostico;
use App\Traits\InformesHelperTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class At2rController extends Controller
{
    use InformesHelperTrait;

    public function __construct(private \App\Services\RegistroGlobalService $service)
    {
    }

    public function index(Request $request)
    {
        if (!$request->ajax() && $request->getQueryString()) {
            return redirect()->route('informes.at2r');
        }

        $ano = $request->input('ano', date('Y'));
        $mes = $request->input('mes', '');
        if (empty($mes))
            $mes = $this->resolverMesPorDefecto($ano);

        $jornada = $request->input('jornada', 'TODAS');
        $profFilter = $request->input('prof', 'TODAS');
        $medicoFilter = $request->input('medico', 'TODOS');
        $sexoFilter = $request->input('sexo', 'AMBOS');

        $anos = $this->service->getAnosDisponibles();
        $meses = Informe::distinct()->orderByRaw("FIELD(mes, 'ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE')")->pluck('mes');
        $jornadas = Informe::distinct()->whereNotNull('jornada')->where('jornada', '!=', '')->pluck('jornada');
        $profesiones = Informe::distinct()->whereNotNull('prof')->where('prof', '!=', '')->orderBy('prof')->pluck('prof');

        $nombresMedicos = Informe::distinct()
            ->where('ano', $ano)->where('mes', $mes)
            ->whereNotNull('medico')->where('medico', '!=', '')
            ->orderBy('medico')->pluck('medico');

        $mapping = [
            'ENFERMERAS AUXILIARES' => 1,
            'LICENCIADA EN ENFERMERIA' => 2,
            'ENFERMERA PROFESIONAL' => 2,
            'MEDICO GENERAL' => 3,
        ];
        $omitir = ['CONSEJERIA', 'PSICOLOGIA', 'TRABAJO SOCIAL', 'ODONTOLOGIA'];

        $queryBase = Informe::query()->where('ano', $ano)->where('mes', $mes);
        if ($jornada != 'TODAS')
            $queryBase->where('jornada', $jornada);
        if ($profFilter != 'TODAS')
            $queryBase->where('prof', $profFilter);
        if ($medicoFilter != 'TODOS')
            $queryBase->where('medico', $medicoFilter);
        if ($sexoFilter != 'AMBOS')
            $queryBase->where('sexo', $sexoFilter);

        $atencionesRaw = (clone $queryBase)
            ->select('id', 'sexo', 'edad', 'tipo', 'cond', 'prof', 'referido_de', 'registro_id')
            ->whereIn('cond', ['N', 'S'])
            ->get()->unique('registro_id');

        $diagRows = Diagnostico::where('categoria', 'AT2-R')->orderBy('id')->get();
        $extraCodigos = ['30', '55', '130', '47', '31', '240', '301', '4', '128', '223', '165', '129', '110', '111', '107', '108', '114', '115', '127', '99999', '99998', '99997', '103', '104', '105', '106', '109', '112', '113', '116', '117', '118', '119', '120', '121', '122', '123', '124', '125', '126'];
        $at2rCodigos = array_unique(array_merge($diagRows->pluck('codigo')->map(fn($v) => trim($v))->toArray(), $extraCodigos));

        $diagnosticosRaw = (clone $queryBase)
            ->whereIn('cod', $at2rCodigos)
            ->select('cod', 'cond', 'cond_diagnostico', 'prof', 'edad', 'tipo')
            ->get();

        $results = [];
        $getCol = function ($prof) use ($mapping, $omitir) {
            $prof = strtoupper(trim($prof));
            if (isset($mapping[$prof]))
                return $mapping[$prof];
            if (in_array($prof, $omitir))
                return null;
            return 4;
        };

        // ageRows definition
        $ageRows = $this->buildAt2rAgeRows();

        foreach ($ageRows as $idx => $def) {
            $results[$idx] = [1 => 0, 2 => 0, 3 => 0, 4 => 0];
            if (isset($def['match'])) {
                foreach ($atencionesRaw as $r) {
                    $col = $getCol($r->prof);
                    if ($col && $def['match']($r))
                        $results[$idx][$col]++;
                }
            }
        }
        foreach ($ageRows as $idx => $def) {
            if (isset($def['sum'])) {
                foreach ($def['sum'] as $sourceIdx) {
                    for ($c = 1; $c <= 4; $c++)
                        $results[$idx][$c] += $results[$sourceIdx][$c];
                }
            }
        }

        $progRows = $this->buildAt2rProgRows();
        $startIdx = count($ageRows);

        foreach ($progRows as $pIdx => $def) {
            $absIdx = $startIdx + $pIdx;
            $results[$absIdx] = [1 => 0, 2 => 0, 3 => 0, 4 => 0];

            if (isset($def['match'])) {
                foreach ($queryBase->get() as $r) {
                    $col = $getCol($r->prof);
                    if ($col && $def['match']($r))
                        $results[$absIdx][$col]++;
                }
            }
            elseif (isset($def['code']) || isset($def['diag'])) {
                foreach ($diagnosticosRaw as $r) {
                    $col = $getCol($r->prof);
                    if (!$col)
                        continue;
                    $match = false;
                    if (isset($def['diag'])) {
                        $diags = is_array($def['diag']) ? $def['diag'] : [$def['diag']];
                        $rDiag = strtoupper(trim($r->diagnostico ?? ''));
                        $diagsUpper = array_map('strtoupper', $diags);
                        if (in_array($rDiag, $diagsUpper))
                            $match = true;
                    }
                    elseif (isset($def['code'])) {
                        $codes = is_array($def['code']) ? $def['code'] : [$def['code']];
                        if (in_array(trim($r->cod), $codes))
                            $match = true;
                    }
                    if ($match) {
                        if (isset($def['cond']) && strtoupper(trim($r->cond_diagnostico)) != strtoupper(trim($def['cond'])))
                            $match = false;
                        if (isset($def['age_max']) && strtoupper(trim($r->tipo)) == 'A' && $r->edad > $def['age_max'])
                            $match = false;
                        if ($match)
                            $results[$absIdx][$col]++;
                    }
                }
            }
            elseif (isset($def['sum_diags'])) {
                foreach ($diagnosticosRaw as $r) {
                    $col = $getCol($r->prof);
                    if (!$col)
                        continue;
                    $rDiag = strtoupper(trim($r->diagnostico ?? ''));
                    $rCond = strtoupper(trim($r->cond_diagnostico ?? ''));
                    foreach ($def['sum_diags'] as $pair) {
                        $pCond = strtoupper(trim($pair[1]));
                        $search = strtoupper(trim($pair[0]));
                        if ((str_contains($rDiag, $search) || $rDiag === $search) && ($pCond == '*' || $rCond == $pCond)) {
                            $results[$absIdx][$col]++;
                        }
                    }
                }
            }
        }

        // Calcular sumas/diff/pct en progRows
        foreach ($progRows as $pIdx => $def) {
            $absIdx = $startIdx + $pIdx;
            if (isset($def['sum_rows'])) {
                foreach ($def['sum_rows'] as $sourceIdx) {
                    if (isset($results[$sourceIdx])) {
                        for ($c = 1; $c <= 4; $c++)
                            $results[$absIdx][$c] += $results[$sourceIdx][$c];
                    }
                }
            }
            elseif (isset($def['diff'])) {
                $baseIdx = $def['diff'][0];
                for ($c = 1; $c <= 4; $c++) {
                    $val = $results[$baseIdx][$c] ?? 0;
                    for ($i = 1; $i < count($def['diff']); $i++) {
                        if (isset($results[$def['diff'][$i]]))
                            $val -= $results[$def['diff'][$i]][$c];
                    }
                    $results[$absIdx][$c] = max(0, $val);
                }
            }
            elseif (isset($def['pct'])) {
                $numIdx = $def['pct'][0];
                $denIdx = $def['pct'][1];
                if (isset($results[$numIdx], $results[$denIdx])) {
                    for ($c = 1; $c <= 4; $c++) {
                        $den = $results[$denIdx][$c];
                        $results[$absIdx][$c] = $den > 0 ? round(($results[$numIdx][$c] / $den) * 100, 2) : 0;
                    }
                }
            }
        }

        $allRows = array_merge($ageRows, $progRows);
        $finalData = [];
        foreach ($allRows as $idx => $row) {
            $finalData[] = [
                'label' => $row['label'],
                'cols' => $results[$idx],
                'total' => array_sum($results[$idx]),
                'color' => $row['color'] ?? '',
            ];
        }

        if ($request->ajax()) {
            return view('informes.at2r_content', compact('anos', 'meses', 'jornadas', 'nombresMedicos', 'profesiones', 'ano', 'mes', 'jornada', 'profFilter', 'medicoFilter', 'sexoFilter', 'finalData'));
        }
        return view('informes.at2r', compact('anos', 'meses', 'jornadas', 'nombresMedicos', 'profesiones', 'ano', 'mes', 'jornada', 'profFilter', 'medicoFilter', 'sexoFilter', 'finalData'));
    }

    private function buildAt2rAgeRows(): array
    {
        return [
            ['label' => 'Menores de 1 mes de 1a Vez', 'match' => fn($r) => $r->tipo == 'D' && $r->edad <= 28 && $r->cond == 'N'],
            ['label' => 'Menores de 1 mes subsiguiente', 'match' => fn($r) => $r->tipo == 'D' && $r->edad <= 28 && $r->cond == 'S'],
            ['label' => 'Total menores de 1 mes', 'sum' => [0, 1], 'color' => 'bg-warning-soft'],
            ['label' => '1 mes a 2 meses de 1a Vez', 'match' => fn($r) => $r->tipo == 'M' && $r->edad == 1 && $r->cond == 'N'],
            ['label' => '1 mes a 2 meses subsiguiente', 'match' => fn($r) => $r->tipo == 'M' && $r->edad == 1 && $r->cond == 'S'],
            ['label' => 'Total 1 a 2 meses', 'sum' => [3, 4], 'color' => 'bg-warning-soft'],
            ['label' => '2 meses a 1 año de 1a Vez', 'match' => fn($r) => ($r->tipo == 'M' && $r->edad >= 2 && $r->edad < 12) && $r->cond == 'N'],
            ['label' => '2 meses a 1 año subsiguiente', 'match' => fn($r) => ($r->tipo == 'M' && $r->edad >= 2 && $r->edad < 12) && $r->cond == 'S'],
            ['label' => 'Total 2 meses a 1 año', 'sum' => [6, 7]],
            ['label' => '1 año a < 5 años 1a Vez', 'match' => fn($r) => ($r->tipo == 'A' && $r->edad >= 1 && $r->edad < 5) && $r->cond == 'N'],
            ['label' => '1 año a < 5 años subsiguiente', 'match' => fn($r) => ($r->tipo == 'A' && $r->edad >= 1 && $r->edad < 5) && $r->cond == 'S'],
            ['label' => 'Total 1 a 4 años', 'sum' => [9, 10]],
            ['label' => 'Total Niños/as menores de 5 años Atendidos 1a vez', 'sum' => [0, 3, 6, 9], 'color' => 'bg-info-soft'],
            ['label' => 'Total Niños/as menores de 5 años Atendidos Subs.', 'sum' => [1, 4, 7, 10], 'color' => 'bg-info-soft'],
            ['label' => 'Total Niños/as menores de 5 años Atendidos', 'sum' => [2, 5, 8, 11], 'color' => 'bg-info-soft'],
            ['label' => '5 a 9 años 1a Vez', 'match' => fn($r) => $r->tipo == 'A' && $r->edad >= 5 && $r->edad <= 9 && $r->cond == 'N'],
            ['label' => '5 a 9 años subsiguiente', 'match' => fn($r) => $r->tipo == 'A' && $r->edad >= 5 && $r->edad <= 9 && $r->cond == 'S'],
            ['label' => 'Total 5 - 9 años', 'sum' => [15, 16]],
            ['label' => '10 a 14 años 1a Vez', 'match' => fn($r) => $r->tipo == 'A' && $r->edad >= 10 && $r->edad <= 14 && $r->cond == 'N'],
            ['label' => '10 a 14 años subsiguiente', 'match' => fn($r) => $r->tipo == 'A' && $r->edad >= 10 && $r->edad <= 14 && $r->cond == 'S'],
            ['label' => 'Total 10 - 14 años', 'sum' => [18, 19]],
            ['label' => '5 a 14 años 1a Vez', 'sum' => [15, 18], 'color' => 'bg-info-soft'],
            ['label' => '5 a 14 años subsiguiente', 'sum' => [16, 19], 'color' => 'bg-info-soft'],
            ['label' => 'Total 5 a 14 años', 'sum' => [17, 20], 'color' => 'bg-info-soft'],
            ['label' => '15 a 19 años 1a Vez', 'match' => fn($r) => $r->tipo == 'A' && $r->edad >= 15 && $r->edad <= 19 && $r->cond == 'N'],
            ['label' => '15 a 19 años subsiguiente', 'match' => fn($r) => $r->tipo == 'A' && $r->edad >= 15 && $r->edad <= 19 && $r->cond == 'S'],
            ['label' => 'Total 15 - 19 años', 'sum' => [24, 25]],
            ['label' => '20 a 49 años 1a Vez', 'match' => fn($r) => $r->tipo == 'A' && $r->edad >= 20 && $r->edad <= 49 && $r->cond == 'N'],
            ['label' => '20 a 49 años subsiguiente', 'match' => fn($r) => $r->tipo == 'A' && $r->edad >= 20 && $r->edad <= 49 && $r->cond == 'S'],
            ['label' => 'Total 20 - 49 años', 'sum' => [27, 28]],
            ['label' => '15 a 49 años 1a Vez', 'sum' => [24, 27], 'color' => 'bg-info-soft'],
            ['label' => '15 a 49 años subsiguiente', 'sum' => [25, 28], 'color' => 'bg-info-soft'],
            ['label' => 'Total 15 a 49 años', 'sum' => [26, 29], 'color' => 'bg-info-soft'],
            ['label' => '50 a 59 años 1a Vez', 'match' => fn($r) => $r->tipo == 'A' && $r->edad >= 50 && $r->edad <= 59 && $r->cond == 'N'],
            ['label' => '50 a 59 años subsiguiente', 'match' => fn($r) => $r->tipo == 'A' && $r->edad >= 50 && $r->edad <= 59 && $r->cond == 'S'],
            ['label' => 'Total 50 - 59 años', 'sum' => [33, 34], 'color' => 'bg-info-soft'],
            ['label' => '60 y + años 1a Vez', 'match' => fn($r) => $r->tipo == 'A' && $r->edad >= 60 && $r->cond == 'N'],
            ['label' => '60 y + años subsiguiente', 'match' => fn($r) => $r->tipo == 'A' && $r->edad >= 60 && $r->cond == 'S'],
            ['label' => 'Total 60 y más años', 'sum' => [36, 37], 'color' => 'bg-info-soft'],
            ['label' => 'Total Pacientes 1a vez', 'sum' => [12, 21, 30, 33, 36], 'color' => 'bg-primary-soft'],
            ['label' => 'Total Pacientes subsiguientes', 'sum' => [13, 22, 31, 34, 37], 'color' => 'bg-primary-soft'],
            ['label' => 'Total Pacientes Atendidos', 'sum' => [14, 23, 32, 35, 38], 'color' => 'bg-primary-soft'],
            ['label' => 'No. Atenciones de mujeres', 'match' => fn($r) => strtoupper($r->sexo) === 'M'],
            ['label' => 'No. Atenciones de hombres', 'match' => fn($r) => strtoupper($r->sexo) === 'H'],
            ['label' => 'Total atenciones a mujeres y hombres', 'sum' => [42, 43], 'color' => 'bg-info-soft'],
            ['label' => 'No. Consultas Espontáneas', 'match' => fn($r) => empty($r->referido_de)],
            ['label' => 'No. Consultas Referidas', 'match' => fn($r) => !empty($r->referido_de)],
            ['label' => 'Total de Consultas', 'sum' => [45, 46], 'color' => 'bg-info-soft'],
        ];
    }

    private function buildAt2rProgRows(): array
    {
        return [
            ['label' => 'Deteccion de Sintomáticos Respiratorios', 'diag' => 'SINTOMATICO RESPIRATORIO'],
            ['label' => 'Atención prenatal antes de las 12 SG Nuevas', 'diag' => 'ATENCION PRENATAL ANTES DE LAS 12 SG', 'cond' => 'N'],
            ['label' => 'Atención prenatal antes de las 12 SG Subsiguiente', 'diag' => 'ATENCION PRENATAL ANTES DE LAS 12 SG', 'cond' => 'S'],
            ['label' => 'Atención prenatal después de las 12 SG Nueva', 'diag' => 'ATENCION PRENATAL DESPUES DE LAS 12 SG', 'cond' => 'N'],
            ['label' => 'Atención prenatal después de las 12 SG subsiguiente', 'diag' => 'ATENCION PRENATAL DESPUES DE LAS 12 SG', 'cond' => 'S'],
            ['label' => 'Atención prenatal edad gestacional no consignada nuevas', 'diag' => 'ATENCION PRENATAL EDAD GESTACIONAL NO CONSIGNADA', 'cond' => 'N'],
            ['label' => 'Atención prenatal edad gestacional no consignada subs', 'diag' => 'ATENCION PRENATAL EDAD GESTACIONAL NO CONSIGNADA', 'cond' => 'S'],
            ['label' => 'Embarazadas Nuevas', 'sum_diags' => [['ATENCION PRENATAL ANTES DE LAS 12 SG', 'N'], ['ATENCION PRENATAL DESPUES DE LAS 12 SG', 'N'], ['ATENCION PRENATAL EDAD GESTACIONAL NO CONSIGNADA', 'N']], 'color' => 'bg-purple-soft'],
            ['label' => 'Embarazadas en control', 'sum_diags' => [['ATENCION PRENATAL ANTES DE LAS 12 SG', 'S'], ['ATENCION PRENATAL DESPUES DE LAS 12 SG', 'S'], ['ATENCION PRENATAL EDAD GESTACIONAL NO CONSIGNADA', 'S']], 'color' => 'bg-purple-soft'],
            ['label' => 'Citología a embarazada', 'diag' => 'CITOLOGIA A EMBARAZADA', 'color' => 'bg-success-soft'],
            ['label' => 'Citologia a paciente no embarazada', 'diag' => 'CITOLOGIA A PACIENTE NO EMBARAZADA', 'color' => 'bg-success-soft'],
            ['label' => 'Detección de Cáncer Cervico Uterino', 'sum_diags' => [['CITOLOGIA A PACIENTE NO EMBARAZADA', '*'], ['CITOLOGIA A EMBARAZADA', '*']], 'color' => 'bg-success-soft'],
            ['label' => 'Colposcopías realizadas', 'diag' => 'COLPOSCOPÍAS REALIZADAS'],
            ['label' => 'USG realizados a embarazadas', 'diag' => 'USG REALIZADOS A EMBARAZADAS'],
            ['label' => 'USG realizados a otros pacientes', 'diag' => 'USG REALIZADOS A OTROS PACIENTES'],
            ['label' => 'Total USG realizados', 'sum_diags' => [['USG REALIZADOS A EMBARAZADAS', '*'], ['USG REALIZADOS A OTROS PACIENTES', '*']], 'color' => 'bg-info-soft'],
            ['label' => 'Partos Atendidos', 'diag' => 'PARTOS ATENDIDOS'],
            ['label' => 'AMEU realizadas', 'diag' => 'AMEU REALIZADAS'],
            ['label' => 'Atención puerperal en los primeros 10 días Nueva', 'diag' => 'ATENCIÓN PUERPERAL EN LOS PRIMEROS 10 DÍAS', 'cond' => 'N'],
            ['label' => 'Atención puerperal primeros 10 d Subsiguiente', 'diag' => 'ATENCIÓN PUERPERAL EN LOS PRIMEROS 10 DÍAS', 'cond' => 'S'],
            ['label' => 'Atención puerperal entre los 11 y 40 dias Nueva', 'diag' => 'ATENCIÓN PUERPERAL ENTRE LOS 11 Y 40 DIAS', 'cond' => 'N'],
            ['label' => 'Atención puerperal entre los 11 y 40 días Subsiguiente', 'diag' => 'ATENCIÓN PUERPERAL ENTRE LOS 11 Y 40 DIAS', 'cond' => 'S'],
            ['label' => 'Controles Puerperales Nuevos', 'sum_diags' => [['ATENCIÓN PUERPERAL EN LOS PRIMEROS 10 DÍAS', 'N'], ['ATENCIÓN PUERPERAL ENTRE LOS 11 Y 40 DIAS', 'N']], 'color' => 'bg-info-soft'],
            ['label' => 'Controles Puerperales Subsiguientes', 'sum_diags' => [['ATENCIÓN PUERPERAL EN LOS PRIMEROS 10 DÍAS', 'S'], ['ATENCIÓN PUERPERAL ENTRE LOS 11 Y 40 DIAS', 'S']], 'color' => 'bg-info-soft'],
            ['label' => 'Anticonceptivo Oral 1 Ciclo', 'diag' => 'ANTICONCEPTIVO ORAL 1 CICLO'],
            ['label' => 'Anticonceptivo Oral 3 Ciclo', 'diag' => 'ANTICONCEPTIVO ORAL 3 CICLO'],
            ['label' => 'Anticonceptivo Oral 6 Ciclo', 'diag' => 'ANTICONCEPTIVO ORAL 6 CICLO'],
            ['label' => 'Condones 10 Unidades', 'diag' => 'CONDONES 10 UNIDADES'],
            ['label' => 'Condones 30 Unidades', 'diag' => 'CONDONES 30 UNIDADES'],
            ['label' => 'Depo Proveras Aplicadas', 'diag' => 'DEPO PROVERAS APLICADAS'],
            ['label' => 'DIU insertados', 'diag' => 'DIU INSERTADOS'],
            ['label' => 'No.Usuarias Uilizando el Metodo de Dias fijos (collar)', 'diag' => 'NO.USUARIAS UILIZANDO EL METODO DE DIAS FIJOS (COLLAR)'],
            ['label' => 'AQV realizados', 'diag' => 'AQV REALIZADOS'],
            ['label' => 'Vasectomías realizadas', 'diag' => 'VASECTORMÍAS REALIZADAS'],
            ['label' => 'Implante sub Dermico', 'diag' => 'IMPLANTE SUB DERMICO'],
            ['label' => 'No. De niños/as menores de 5 años con diarrea nuevo', 'diag' => ['DIARREAS SIN SANGRE', 'DIARREAS CON DESHIDRATACION', 'DIARREAS'], 'cond' => 'N', 'age_max' => 4],
            ['label' => 'No. De niños/as menores de 5 años con diarrea que acuden a cita', 'diag' => ['DIARREAS SIN SANGRE', 'DIARREAS CON DESHIDRATACION', 'DIARREAS'], 'cond' => 'S', 'age_max' => 4],
            ['label' => 'No. De niños/as menores de 5 años con deshidratación rehidratados', 'diag' => 'REHIDRATACION ORAL', 'age_max' => 4],
            ['label' => 'No. De Niños/as menores de 5 años con casos de Neumonía nuevos', 'diag' => 'NEUMONIAS', 'cond' => 'N', 'age_max' => 4],
            ['label' => 'No. De niños/as menores de 5 años con Neumonía cita Seguimiento', 'diag' => 'NEUMONIAS', 'cond' => 'S', 'age_max' => 4],
            ['label' => 'No. de niños/as menores de 5 años con Síndrome anémico', 'diag' => 'REUNIÓN DE TRABAJO COMUNITARIO', 'age_max' => 4],
            ['label' => 'No. de niños/as menores de 5 años con crecimiento adecuado NUEVOS', 'diff' => [12, 91, 93, 95]],
            ['label' => 'No. de niños/as menores de 5 años con crecimiento adecuado SUBSIGUIENTES', 'diff' => [13, 92, 94, 96]],
            ['label' => 'No. de niños/as menores de 5 años con crecimiento inadecuado NUEVOS', 'diag' => 'CRECIMIENTO INADECUADO', 'cond' => 'N', 'age_max' => 4],
            ['label' => 'No. de niños/as menores de 5 años con crecimiento inadecuado SUBSIG.', 'diag' => 'CRECIMIENTO INADECUADO', 'cond' => 'S', 'age_max' => 4],
            ['label' => 'No. de Niños/as menores de 5 años con bajo percentil 3 NUEVOS', 'code' => '99998', 'cond' => 'N', 'age_max' => 4],
            ['label' => 'No. de Niños/as menores de 5 años con bajo percentil 3 SUBSIGUIENTE', 'code' => '99998', 'cond' => 'S', 'age_max' => 4],
            ['label' => 'No. de Niños/as menores de 5 años con daño nutricional severo NUEVOS', 'diag' => 'DAÑO NUTRICIONAL GRAVE', 'cond' => 'N', 'age_max' => 4],
            ['label' => 'No. de Niños/as menores de 5 años con daño nutricional severo SUBSIG.', 'diag' => 'DAÑO NUTRICIONAL GRAVE', 'cond' => 'S', 'age_max' => 4],
            ['label' => 'Total de niños/as menores de 5 años con eval nutric. Nuevos', 'sum_rows' => [89, 91, 93, 95], 'color' => 'bg-info-soft'],
            ['label' => 'Total de niños/as menores de 5 años con eval nutric. Subs', 'sum_rows' => [90, 92, 94, 96], 'color' => 'bg-info-soft'],
            ['label' => 'Total de atenciones a Niños/as menores de 5 años con eval nutricional', 'sum_rows' => [97, 98], 'color' => 'bg-info-soft'],
            ['label' => '% de niños menores de 5 años en atención de 1ra vez con eval nutricional', 'pct' => [97, 12]],
            ['label' => '% de niños menores de 5 años en atención subs con eval nutricional', 'pct' => [98, 13]],
            ['label' => '% de niños menores de 5 años con evaluacion nutricional', 'pct' => [99, 14]],
            ['label' => 'ATENCION CLINICA POR COVID-19 NUEVO', 'diag' => 'ATENCION CLINICA POR COVID-19', 'cond' => 'N'],
            ['label' => 'ATENCION CLINICA POR COVID-19 SUBSIGUIENTE', 'diag' => 'ATENCION CLINICA POR COVID-19', 'cond' => 'S'],
            ['label' => 'Hipertensión Arterial 1a vez', 'diag' => 'HIPERTENSION ARTERIAL', 'cond' => 'N'],
            ['label' => 'Hipertensión Arterial subsiguiente', 'diag' => 'HIPERTENSION ARTERIAL', 'cond' => 'S'],
            ['label' => 'Total Hipertensión Arterial', 'sum_diags' => [['55', '*'], ['HIPERTENSION ARTERIAL', '*']], 'color' => 'bg-purple-soft'],
            ['label' => 'Diabetes mellitus 1a Vez', 'diag' => ['DIABETES', 'ATENCIONES BRINDADAS NUEVAS DE DIABETES MELLITUS'], 'cond' => 'N'],
            ['label' => 'Diabetes mellitus subs', 'diag' => ['DIABETES', 'ATENCIONES BRINDADAS SUBSIGUIENTES DE DIABETES MELLITUS'], 'cond' => 'S'],
            ['label' => 'Total Diabetes mellitus', 'sum_diags' => [['30', '*'], ['DIABETES', '*'], ['ATENCIONES BRINDADAS NUEVAS DE DIABETES MELLITUS', '*'], ['ATENCIONES BRINDADAS SUBSIGUIENTES DE DIABETES MELLITUS', '*']], 'color' => 'bg-success-soft'],
        ];
    }
}
