<?php

namespace App\Http\Controllers\Informes;

use App\Http\Controllers\Controller;
use App\Models\Informe;
use App\Traits\InformesHelperTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\ImplantesExport;
use Maatwebsite\Excel\Facades\Excel;

class ImplantesController extends Controller
{
    use InformesHelperTrait;

    public function __construct(private \App\Services\RegistroGlobalService $service)
    {
    }

    public function index(Request $request)
    {
        if (!$request->ajax() && $request->getQueryString()) {
            return redirect()->route('informes.implantes');
        }

        $ano = $request->input('ano', date('Y'));
        $mes = $request->input('mes', '');
        if (empty($mes))
            $mes = $this->resolverMesPorDefecto($ano);

        $jornada = $request->input('jornada', 'TODAS') ?: 'TODAS';
        $profFilter = $request->input('prof', 'TODAS') ?: 'TODAS';
        $search = $request->input('search', 'TODOS') ?: 'TODOS';

        $anos = $this->service->getAnosDisponibles();
        $meses = Informe::distinct()->orderByRaw("FIELD(mes, 'ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE')")->pluck('mes');
        $jornadas = Informe::distinct()->whereNotNull('jornada')->where('jornada', '!=', '')->pluck('jornada');
        $profesiones = Informe::distinct()->whereNotNull('prof')->where('prof', '!=', '')->orderBy('prof')->pluck('prof');

        $implanteNames = [
            'INSERCIÓN DE IMPLANTE CON LEVONORGESTREL 5 AÑOS (JADELLE)',
            'INSERCIÓN DE IMPLANTE CON ETONOGESTREL 3 AÑOS (NXT)',
            'IMPLANTE SUB DERMICO',
        ];

        $nombresMedicosQuery = Informe::distinct()
            ->where('ano', $ano)->where('mes', $mes)
            ->whereIn('diagnostico', $implanteNames)
            ->whereNotNull('medico')->where('medico', '!=', '');
        if ($profFilter && $profFilter != 'TODAS')
            $nombresMedicosQuery->where('prof', $profFilter);
        $nombresMedicos = $nombresMedicosQuery->orderBy('medico')->pluck('medico');

        $query = Informe::query()->where('ano', $ano)->where('mes', $mes)->whereIn('diagnostico', $implanteNames);
        if ($jornada && $jornada != 'TODAS')
            $query->where('jornada', $jornada);
        if ($profFilter && $profFilter != 'TODAS')
            $query->where('prof', $profFilter);
        if (!empty($search) && $search != 'TODOS') {
            is_array($search) ? $query->whereIn('medico', $search) : $query->where('medico', 'LIKE', "%$search%");
        }

        $rawData = $query->select('medico', 'prof', 'fecha', 'diagnostico', DB::raw('count(*) as total'))
            ->groupBy('medico', 'prof', 'fecha', 'diagnostico')
            ->orderBy('medico')
            ->get();

        $data = [];
        $dates = [];

        foreach ($rawData as $row) {
            $f = \Carbon\Carbon::parse($row->fecha);
            $dateStr = $f->format('d/m/Y');
            $dates[$f->timestamp] = $dateStr;
            $medico = $row->medico ?: 'SIN NOMBRE';

            if (!isset($data[$medico]))
                $data[$medico] = ['prof' => $row->prof, 'dates' => []];
            if (!isset($data[$medico]['dates'][$dateStr]))
                $data[$medico]['dates'][$dateStr] = ['total' => 0, 'breakdown' => []];

            $diag = strtoupper(trim($row->diagnostico));
            $short = 'OTRO';
            if (str_contains($diag, 'JADELLE') || str_contains($diag, '5 AÑOS'))
                $short = 'JADELLE';
            elseif (str_contains($diag, 'NXT') || str_contains($diag, '3 AÑOS') || str_contains($diag, 'SUB DERMICO'))
                $short = 'NXT';

            $data[$medico]['dates'][$dateStr]['total'] += $row->total;
            $data[$medico]['dates'][$dateStr]['breakdown'][$short] = ($data[$medico]['dates'][$dateStr]['breakdown'][$short] ?? 0) + $row->total;
        }

        ksort($dates);
        $headers = array_values($dates);
        $fechasObjs = array_map(fn($h) => ['fecha' => $h, 'obj' => \Carbon\Carbon::createFromFormat('d/m/Y', $h)], $headers);

        if ($request->ajax()) {
            return view('informes.implantes_content', compact(
                'anos', 'meses', 'jornadas', 'profesiones', 'nombresMedicos',
                'ano', 'mes', 'jornada', 'profFilter', 'search', 'data', 'headers', 'fechasObjs'
            ));
        }
        return view('informes.implantes', compact(
            'anos', 'meses', 'jornadas', 'profesiones', 'nombresMedicos',
            'ano', 'mes', 'jornada', 'profFilter', 'search', 'data', 'headers', 'fechasObjs'
        ));
    }

    public function export(Request $request)
    {
        $ano = $request->input('ano', date('Y'));
        $mes = $request->input('mes', '');
        $jornada = $request->input('jornada', 'TODAS');
        $profFilter = $request->input('prof', 'TODAS');
        $search = $request->input('search', 'TODOS');

        $implanteNames = [
            'INSERCIÓN DE IMPLANTE CON LEVONORGESTREL 5 AÑOS (JADELLE)',
            'INSERCIÓN DE IMPLANTE CON ETONOGESTREL 3 AÑOS (NXT)',
            'IMPLANTE SUB DERMICO',
        ];

        $query = Informe::query()->where('ano', $ano)->whereIn('diagnostico', $implanteNames);
        if ($mes)
            $query->where('mes', $mes);
        if ($jornada && $jornada != 'TODAS')
            $query->where('jornada', $jornada);
        if ($profFilter && $profFilter != 'TODAS')
            $query->where('prof', $profFilter);
        if (!empty($search) && $search != 'TODOS') {
            is_array($search) ? $query->whereIn('medico', $search) : $query->where('medico', 'LIKE', "%$search%");
        }

        $rawData = $query->whereIn('cond', ['N', 'S'])
            ->select('medico', 'prof', 'fecha', DB::raw('count(DISTINCT registro_id) as total'))
            ->groupBy('medico', 'prof', 'fecha')
            ->orderBy('medico')
            ->get();

        $data = [];
        $dates = [];
        foreach ($rawData as $row) {
            $f = \Carbon\Carbon::parse($row->fecha);
            $dateStr = $f->format('d/m/Y');
            $dates[$f->timestamp] = $dateStr;
            $medico = $row->medico ?: 'SIN NOMBRE';
            if (!isset($data[$medico]))
                $data[$medico] = ['prof' => $row->prof, 'dates' => []];
            $data[$medico]['dates'][$dateStr] = $row->total;
        }
        ksort($dates);
        $headers = array_values($dates);

        return Excel::download(new ImplantesExport($data, $headers, $ano, $mes),
            'Reporte_Implantes_' . $mes . '_' . $ano . '_' . date('His') . '.xlsx');
    }
}
