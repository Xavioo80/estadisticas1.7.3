<?php

namespace App\Http\Controllers\Informes;

use App\Http\Controllers\Controller;
use App\Models\RegistroGlobal;
use App\Models\Diagnostico;
use App\Models\Medico;
use App\Traits\InformesHelperTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class Sm2Controller extends Controller
{
    use InformesHelperTrait;

    public function index(Request $request)
    {
        $ano = $request->input('ano', date('Y'));
        $mes = $request->input('mes', '');

        if (empty($mes)) {
            $mes = $this->resolverMesPorDefecto($ano, true);
        }

        $jornada = $request->input('jornada', 'TODAS') ?: 'TODAS';

        $anos = RegistroGlobal::distinct()->orderBy('ano', 'desc')->pluck('ano');
        $mesesRaw = RegistroGlobal::distinct()->pluck('mes')->toArray();
        $mesMap = [
            'ENERO' => 1,
            'FEBRERO' => 2,
            'MARZO' => 3,
            'ABRIL' => 4,
            'MAYO' => 5,
            'JUNIO' => 6,
            'JULIO' => 7,
            'AGOSTO' => 8,
            'SEPTIEMBRE' => 9,
            'OCTUBRE' => 10,
            'NOVIEMBRE' => 11,
            'DICIEMBRE' => 12
        ];

        $meses = collect($mesesRaw)->filter()->map(fn($m) => strtoupper(trim($m ?? '')))
            ->unique()
            ->sort(fn($a, $b) => ($mesMap[$a] ?? 0) <=> ($mesMap[$b] ?? 0))
            ->values();

        $jornadas = RegistroGlobal::distinct()->whereNotNull('jornada')->where('jornada', '!=', '')->pluck('jornada');

        // Definición de actividades y sus códigos de diagnóstico mapeados
        // Basado en el catálogo SM1 que ya tiene estas actividades
        $actividadesDef = [
            ['n' => 1, 'label' => 'PSICOTERAPIA INDIVIDUAL', 'codigos' => ['134']],
            ['n' => 2, 'label' => 'PSICOTERAPIA GRUPAL / FAM', 'codigos' => ['135', '137']],
            ['n' => 3, 'label' => 'INTERVENCION EN CRISIS (P.A.P)', 'codigos' => ['132']],
            ['n' => 4, 'label' => 'PRUEBAS PSICOLOGICAS', 'codigos' => ['141']],
            ['n' => 5, 'label' => 'CAPACITACIONES REALIZADAS', 'codigos' => ['144']],
            ['n' => 6, 'label' => 'CONSEJERIAS VIH/FAM.', 'codigos' => ['150']],
            ['n' => 7, 'label' => 'ORGANIZACIÓN Y FORTALECIMIENTO DE GRUPO', 'codigos' => ['149']],
            ['n' => 8, 'label' => 'FORMACIÓN Y ASISTENCIA A REDES COMUNITARIAS', 'codigos' => ['142']],
            ['n' => 9, 'label' => 'ASESORAMIENTO A LAS VICTIMAS', 'codigos' => []],
            ['n' => 10, 'label' => 'ACOMPAÑAMIENTOS A LAS VICTIMAS', 'codigos' => []],
            ['n' => 11, 'label' => 'VISITAS DOMICILIARIAS', 'codigos' => ['336']],
            ['n' => 12, 'label' => 'GRUPOS COESCUCHA Y AUTOAYUDA', 'codigos' => ['139']],
            ['n' => 13, 'label' => 'OTRAS', 'codigos' => ['000000000']],
            ['n' => 14, 'label' => 'NUMERO DE PARTICIPANTES', 'codigos' => ['136', '138', '140', '143', '145', '148']],
        ];

        $allInteresCodes = collect($actividadesDef)->pluck('codigos')->flatten()->unique()->toArray();

        // Mapeo robusto de especialidades a columnas del reporte
        $profMap = [
            'PSICOLOGIA' => 'psicologo',
            'PSICOLOGO' => 'psicologo',
            'MEDICO GENERAL' => 'medico_general',
            'PEDIATRA' => 'otros',
            'GINECOLOGIA' => 'otros',
            'MEDICO' => 'medico_general',
            'PSIQUIATRA' => 'psiquiatra',
            'TRABAJADOR SOCIAL' => 'trabajador_social',
            'ABOGADO' => 'abogado',
            'ENFERMERAS AUXILIARES' => 'auxiliar_enfermeria',
            'ENFERMERA AUXILIAR' => 'auxiliar_enfermeria',
            'LICENCIADAS EN ENFERMERIA' => 'licenciada_enfermeria',
            'LICENCIADA EN ENFERMERIA' => 'licenciada_enfermeria',
            'CONSEJERIA' => 'otros',
            'OTROS' => 'otros'
        ];

        // Consulta de datos
        $query = RegistroGlobal::query()->where('ano', $ano)->where('mes', $mes);
        if ($jornada != 'TODAS') {
            $query->where('jornada', $jornada);
        }

        $rawData = $query->select(
            'cm',
            'cond',
            'sg',
            'cod_1',
            'cond_1',
            'cod_2',
            'cond_2',
            'cod_3',
            'cond_3',
            'cod_4',
            'cond_4',
            'cod_5',
            'cond_5',
            'cod_6',
            'cond_6',
            'cod_7',
            'cond_7'
        )->get();

        // Obtener especialidades de médicos una sola vez
        $medicosEspecialidad = Medico::pluck('ESPECIALIDAD', 'COD_MED')->toArray();

        $results = []; // [actividad_index][prof_key][N|S]

        foreach ($rawData as $r) {
            $profInternal = $medicosEspecialidad[$r->cm] ?? 'OTROS';
            $profKey = $profMap[$profInternal] ?? 'otros';

            // Variable para rastrear si ya sumamos SG en este registro para la actividad 14
            $sgSumadoParaActividad14 = false;

            for ($i = 1; $i <= 7; $i++) {
                $codField = "cod_$i";
                $rawCod = trim($r->$codField ?? '');
                if (empty($rawCod))
                    continue;

                // Encontrar a qué actividad pertenece este código
                foreach ($actividadesDef as $idx => $def) {
                    if (in_array($rawCod, $def['codigos'])) {
                        $condField = "cond_$i";
                        $cond = strtoupper(trim($r->$condField ?? ''));
                        if (empty($cond))
                            $cond = strtoupper(trim($r->cond ?? ''));

                        $isN = ($cond == 'N');
                        $isS = ($cond == 'S');
                        if (!$isN && !$isS)
                            $isN = true; // Por defecto Nuevo si no hay dato

                        if (!isset($results[$idx]))
                            $results[$idx] = [];
                        if (!isset($results[$idx][$profKey]))
                            $results[$idx][$profKey] = ['n' => 0, 's' => 0];

                        // Si es la actividad 14 (Numero de Participantes), sumamos SG
                        if ($def['n'] == 14) {
                            if (!$sgSumadoParaActividad14) {
                                $valSG = is_numeric($r->sg) ? (int) $r->sg : 0;
                                // Si sg está vacío pero el código existe, tal vez cuenta como 1?
                                // Pero el usuario dice que están en SG.
                                if ($isN)
                                    $results[$idx][$profKey]['n'] += $valSG;
                                else
                                    $results[$idx][$profKey]['s'] += $valSG;
                                $sgSumadoParaActividad14 = true;
                            }
                        } else {
                            // Actividades normales: sumamos 1 por cada aparición
                            if ($isN)
                                $results[$idx][$profKey]['n']++;
                            else
                                $results[$idx][$profKey]['s']++;
                        }
                    }
                }
            }
        }

        // Formatear datos para la vista
        $reportData = [];
        // IMPORTANTE: usar lista fija y única de profKeys (NO array_values($profMap) que tiene duplicados
        // porque varias especialidades mapean al mismo profKey, ej. PSICOLOGIA y PSICOLOGO → psicologo)
        $profKeys = ['psicologo', 'medico_general', 'psiquiatra', 'trabajador_social', 'abogado', 'auxiliar_enfermeria', 'licenciada_enfermeria', 'otros'];
        $totals = []; // [prof_key][n|s]
        foreach ($profKeys as $pk)
            $totals[$pk] = ['n' => 0, 's' => 0];
        $totalGeneral = ['n' => 0, 's' => 0];

        foreach ($actividadesDef as $idx => $def) {
            $row = [
                'n' => $def['n'],
                'label' => $def['label'],
                'values' => [],
                'row_total' => ['n' => 0, 's' => 0]
            ];

            foreach ($profKeys as $pk) {
                $n = $results[$idx][$pk]['n'] ?? 0;
                $s = $results[$idx][$pk]['s'] ?? 0;
                $row['values'][$pk] = ['n' => $n, 's' => $s];

                $row['row_total']['n'] += $n;
                $row['row_total']['s'] += $s;

                $totals[$pk]['n'] += $n;
                $totals[$pk]['s'] += $s;
            }

            $totalGeneral['n'] += $row['row_total']['n'];
            $totalGeneral['s'] += $row['row_total']['s'];

            $reportData[] = $row;
        }

        $viewData = compact('ano', 'mes', 'jornada', 'anos', 'meses', 'jornadas', 'reportData', 'totals', 'totalGeneral');

        if ($request->ajax()) {
            return view('informes.sm2_content', $viewData);
        }

        return view('informes.sm2', $viewData);
    }
}
