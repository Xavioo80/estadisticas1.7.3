<?php

namespace App\Http\Controllers\Informes;

use App\Http\Controllers\Controller;
use App\Models\Informe;
use App\Traits\InformesHelperTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exports\AtencionesExport;
use Maatwebsite\Excel\Facades\Excel;

class AtencionesController extends Controller
{
    use InformesHelperTrait;

    public function __construct(private \App\Services\RegistroGlobalService $service)
    {
    }

    public function index(Request $request)
    {
        if (!$request->ajax() && $request->getQueryString()) {
            return redirect()->route('informes.atenciones');
        }

        $helperData = $this->getAnosMesesDisponiblesInformes();
        $anos = $helperData['anos'];
        $meses = $helperData['meses'];

        $ano = $request->input('ano', $helperData['anoDefault']);
        $mes = $request->input('mes', '');

        if (empty($mes))
            $mes = $this->resolverMesPorDefecto($ano);

        $jornada = $request->input('jornada', 'TODAS') ?: 'TODAS';
        $profFilter = $request->input('prof', 'TODAS') ?: 'TODAS';
        $search = $request->input('search', 'TODOS') ?: 'TODOS';
        $personal = $request->input('personal', 'MEDICOS') ?: 'MEDICOS';

        $medicoProfs = ['MEDICO GENERAL', 'PEDIATRA', 'GINECOLOGIA', 'PSIQUIATRA', 'MÉDICO GENERAL', 'MEDICO ASISTENCIAL'];
        $otrosProfs = ['CONSEJERIA', 'PSICOLOGIA', 'ENFERMERAS AUXILIARES', 'LICENCIADAS EN ENFERMERIA'];

        $jornadas = Informe::distinct()->whereNotNull('jornada')->where('jornada', '!=', '')->pluck('jornada');
        $profesiones = Informe::distinct()->whereNotNull('prof')->where('prof', '!=', '')->orderBy('prof')->pluck('prof');

        // 1. Obtener medicos que registraron atenciones en el informe
        $nombresInformeQuery = Informe::distinct()
            ->where('ano', $ano)->where('mes', $mes)
            ->whereIn('cond', ['N', 'S'])
            ->whereNotNull('medico')->where('medico', '!=', '');

        if ($jornada && $jornada != 'TODAS') {
            $nombresInformeQuery->where('jornada', $jornada);
        }

        if ($profFilter && $profFilter != 'TODAS') {
            $nombresInformeQuery->where('prof', $profFilter);
        } else {
            $nombresInformeQuery->whereIn('prof', $personal == 'MEDICOS' ? $medicoProfs : $otrosProfs);
        }
        $nombresInforme = $nombresInformeQuery->pluck('medico')->toArray();

        if ($personal == 'MEDICOS') {
            // Incluir exactamente los médicos activos correspondientes a la jornada (coincidencia 100% con Hora Médico)
            $medicosConRegistros = \App\Models\RegistroGlobal::where('ano', $ano)->where('mes', $mes)->distinct()->pluck('medico');
            $medicosHSC = \App\Models\HoraSinConsulta::where('ano', $ano)->where('mes', $mes)->pluck('medico_id');

            $medicosMasterQuery = \App\Models\Medico::where('estado', 'activo');
            if ($jornada && $jornada !== 'TODAS' && $jornada !== 'TOTAL JORNADAS') {
                $medicosMasterQuery->where('JORNADA', $jornada);
            }
            $medicosMasterQuery->where(function($q) use ($medicosConRegistros, $medicosHSC) {
                $q->whereIn('NOM_MED', $medicosConRegistros)->orWhereIn('id', $medicosHSC);
            });

            // Excluir profesiones no médicas
            $medicosMasterQuery->where(function($q) {
                $q->where('ESPECIALIDAD', 'NOT LIKE', '%NUTRI%')
                  ->where('ESPECIALIDAD', 'NOT LIKE', '%PSICOL%')
                  ->where('ESPECIALIDAD', 'NOT LIKE', '%ENFERM%')
                  ->where('ESPECIALIDAD', 'NOT LIKE', '%AUXILIAR%')
                  ->where('ESPECIALIDAD', 'NOT LIKE', '%CONSEJ%')
                  ->where('NOMINA', 'NOT LIKE', '%NUTRI%')
                  ->where('NOMINA', 'NOT LIKE', '%PSICOL%')
                  ->where('NOMINA', 'NOT LIKE', '%ENFERM%')
                  ->where('NOMINA', 'NOT LIKE', '%AUXILIAR%')
                  ->where('NOMINA', 'NOT LIKE', '%CONSEJ%')
                  ->where('NOM_MED', 'NOT LIKE', '%NUTRI%')
                  ->where('NOM_MED', 'NOT LIKE', '%PSICOL%')
                  ->where('NOM_MED', 'NOT LIKE', '%ENFERM%')
                  ->where('NOM_MED', 'NOT LIKE', '%AUXILIAR%')
                  ->where('NOM_MED', 'NOT LIKE', '%CONSEJ%');
            });

            $nombresMedicos = $medicosMasterQuery->orderBy('NOM_MED')->pluck('NOM_MED')->unique()->values();
        } else {
            $nombresMedicos = collect($nombresInforme)->unique()->sort()->values();
        }

        $query = Informe::query()->where('ano', $ano)->where('mes', $mes);
        if ($jornada && $jornada != 'TODAS')
            $query->where('jornada', $jornada);

        if ($profFilter && $profFilter != 'TODAS') {
            $query->where('prof', $profFilter);
        }
        else {
            $query->whereIn('prof', $personal == 'MEDICOS' ? $medicoProfs : $otrosProfs);
        }

        if (!empty($search) && $search != 'TODOS') {
            is_array($search) ? $query->whereIn('medico', $search) : $query->where('medico', 'LIKE', "%$search%");
        }

        $rawData = $query->whereIn('cond', ['N', 'S'])
            ->select('medico', 'prof', 'fecha', DB::raw('count(DISTINCT registro_id) as total'))
            ->groupBy('medico', 'prof', 'fecha')
            ->orderBy('medico')
            ->get();

        $data = [];
        foreach ($nombresMedicos as $mName) {
            if (!empty($search) && $search != 'TODOS' && $mName !== $search && (!is_array($search) || !in_array($mName, $search))) {
                continue;
            }
            $data[$mName] = ['prof' => 'MÉDICO GENERAL', 'dates' => []];
        }

        foreach ($rawData as $row) {
            $medico = $row->medico ?: 'SIN NOMBRE';
            if (!isset($data[$medico]))
                $data[$medico] = ['prof' => $row->prof, 'dates' => []];
            $f = \Carbon\Carbon::parse($row->fecha);
            $dateStr = $f->format('d/m/Y');
            $data[$medico]['prof'] = $row->prof;
            $data[$medico]['dates'][$dateStr] = $row->total;
        }

        $mesesNumericos = [
            'ENERO' => 1, 'FEBRERO' => 2, 'MARZO' => 3, 'ABRIL' => 4,
            'MAYO' => 5, 'JUNIO' => 6, 'JULIO' => 7, 'AGOSTO' => 8,
            'SEPTIEMBRE' => 9, 'OCTUBRE' => 10, 'NOVIEMBRE' => 11, 'DICIEMBRE' => 12
        ];
        $mesNum = $mesesNumericos[strtoupper($mes)] ?? 1;
        $daysInMonth = \Carbon\Carbon::create($ano, $mesNum, 1)->daysInMonth;

        $headers = [];
        $fechasObjs = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $f = \Carbon\Carbon::create($ano, $mesNum, $d);
            $dateStr = $f->format('d/m/Y');
            $headers[] = $dateStr;
            $fechasObjs[] = ['fecha' => $dateStr, 'obj' => $f];
        }

        if ($request->ajax()) {
            return view('informes.atenciones_content', compact(
                'anos', 'meses', 'jornadas', 'profesiones', 'nombresMedicos',
                'ano', 'mes', 'jornada', 'profFilter', 'search', 'personal',
                'data', 'headers', 'fechasObjs'
            ));
        }

        return view('informes.atenciones', compact(
            'anos', 'meses', 'jornadas', 'profesiones', 'nombresMedicos',
            'ano', 'mes', 'jornada', 'profFilter', 'search', 'personal',
            'data', 'headers', 'fechasObjs'
        ));
    }

    public function export(Request $request)
    {
        $ano = $request->input('ano', date('Y'));
        $mes = $request->input('mes', '');
        if (empty($mes)) {
            $mes = $this->resolverMesPorDefecto($ano);
        }
        
        $jornada = $request->input('jornada', 'TODAS');
        $profFilter = $request->input('prof', 'TODAS');
        $search = $request->input('search', 'TODOS');
        $personal = $request->input('personal', 'MEDICOS');

        $medicoProfs = ['MEDICO GENERAL', 'PEDIATRA', 'GINECOLOGIA', 'PSIQUIATRA', 'MÉDICO GENERAL', 'MEDICO ASISTENCIAL'];
        $otrosProfs = ['CONSEJERIA', 'PSICOLOGIA', 'ENFERMERAS AUXILIARES', 'LICENCIADAS EN ENFERMERIA'];

        // 1. Obtener medicos que registraron atenciones en el informe
        $nombresInformeQuery = Informe::distinct()
            ->where('ano', $ano)->where('mes', $mes)
            ->whereIn('cond', ['N', 'S'])
            ->whereNotNull('medico')->where('medico', '!=', '');

        if ($jornada && $jornada != 'TODAS') {
            $nombresInformeQuery->where('jornada', $jornada);
        }

        if ($profFilter && $profFilter != 'TODAS') {
            $nombresInformeQuery->where('prof', $profFilter);
        } else {
            $nombresInformeQuery->whereIn('prof', $personal == 'MEDICOS' ? $medicoProfs : $otrosProfs);
        }
        $nombresInforme = $nombresInformeQuery->pluck('medico')->toArray();

        if ($personal == 'MEDICOS') {
            $medicosConRegistros = \App\Models\RegistroGlobal::where('ano', $ano)->where('mes', $mes)->distinct()->pluck('medico');
            $medicosHSC = \App\Models\HoraSinConsulta::where('ano', $ano)->where('mes', $mes)->pluck('medico_id');

            $medicosMasterQuery = \App\Models\Medico::where('estado', 'activo');
            if ($jornada && $jornada !== 'TODAS' && $jornada !== 'TOTAL JORNADAS') {
                $medicosMasterQuery->where('JORNADA', $jornada);
            }
            $medicosMasterQuery->where(function($q) use ($medicosConRegistros, $medicosHSC) {
                $q->whereIn('NOM_MED', $medicosConRegistros)->orWhereIn('id', $medicosHSC);
            });

            $medicosMasterQuery->where(function($q) {
                $q->where('ESPECIALIDAD', 'NOT LIKE', '%NUTRI%')
                  ->where('ESPECIALIDAD', 'NOT LIKE', '%PSICOL%')
                  ->where('ESPECIALIDAD', 'NOT LIKE', '%ENFERM%')
                  ->where('ESPECIALIDAD', 'NOT LIKE', '%AUXILIAR%')
                  ->where('ESPECIALIDAD', 'NOT LIKE', '%CONSEJ%')
                  ->where('NOMINA', 'NOT LIKE', '%NUTRI%')
                  ->where('NOMINA', 'NOT LIKE', '%PSICOL%')
                  ->where('NOMINA', 'NOT LIKE', '%ENFERM%')
                  ->where('NOMINA', 'NOT LIKE', '%AUXILIAR%')
                  ->where('NOMINA', 'NOT LIKE', '%CONSEJ%')
                  ->where('NOM_MED', 'NOT LIKE', '%NUTRI%')
                  ->where('NOM_MED', 'NOT LIKE', '%PSICOL%')
                  ->where('NOM_MED', 'NOT LIKE', '%ENFERM%')
                  ->where('NOM_MED', 'NOT LIKE', '%AUXILIAR%')
                  ->where('NOM_MED', 'NOT LIKE', '%CONSEJ%');
            });

            $nombresMedicos = $medicosMasterQuery->orderBy('NOM_MED')->pluck('NOM_MED')->unique()->values();
        } else {
            $nombresMedicos = collect($nombresInforme)->unique()->sort()->values();
        }

        $query = Informe::query()->where('ano', $ano);
        if ($mes)
            $query->where('mes', $mes);
        if ($jornada && $jornada != 'TODAS')
            $query->where('jornada', $jornada);
        if ($profFilter && $profFilter != 'TODAS') {
            $query->where('prof', $profFilter);
        }
        else {
            $query->whereIn('prof', $personal == 'MEDICOS' ? $medicoProfs : $otrosProfs);
        }
        if (!empty($search) && $search != 'TODOS') {
            is_array($search) ? $query->whereIn('medico', $search) : $query->where('medico', 'LIKE', "%$search%");
        }

        $rawData = $query->whereIn('cond', ['N', 'S'])
            ->select('medico', 'prof', 'fecha', DB::raw('count(DISTINCT registro_id) as total'))
            ->groupBy('medico', 'prof', 'fecha')
            ->orderBy('medico')
            ->get();

        $data = [];
        foreach ($nombresMedicos as $mName) {
            if (!empty($search) && $search != 'TODOS' && $mName !== $search && (!is_array($search) || !in_array($mName, $search))) {
                continue;
            }
            $data[$mName] = ['prof' => 'MÉDICO GENERAL', 'dates' => []];
        }

        foreach ($rawData as $row) {
            $medico = $row->medico ?: 'SIN NOMBRE';
            if (!isset($data[$medico]))
                $data[$medico] = ['prof' => $row->prof, 'dates' => []];
            $f = \Carbon\Carbon::parse($row->fecha);
            $dateStr = $f->format('d/m/Y');
            $data[$medico]['prof'] = $row->prof;
            $data[$medico]['dates'][$dateStr] = $row->total;
        }

        $mesesNumericos = [
            'ENERO' => 1, 'FEBRERO' => 2, 'MARZO' => 3, 'ABRIL' => 4,
            'MAYO' => 5, 'JUNIO' => 6, 'JULIO' => 7, 'AGOSTO' => 8,
            'SEPTIEMBRE' => 9, 'OCTUBRE' => 10, 'NOVIEMBRE' => 11, 'DICIEMBRE' => 12
        ];
        $mesNum = $mesesNumericos[strtoupper($mes)] ?? 1;
        $daysInMonth = \Carbon\Carbon::create($ano, $mesNum, 1)->daysInMonth;

        $fechasObjs = [];
        for ($d = 1; $d <= $daysInMonth; $d++) {
            $f = \Carbon\Carbon::create($ano, $mesNum, $d);
            $dateStr = $f->format('d/m/Y');
            $fechasObjs[] = ['fecha' => $dateStr, 'obj' => $f];
        }

        return Excel::download(new AtencionesExport($data, $fechasObjs, $ano, $mes),
            'Reporte_Atenciones_' . $mes . '_' . $ano . '_' . date('His') . '.xlsx');
    }
}
