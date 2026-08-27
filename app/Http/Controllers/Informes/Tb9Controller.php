<?php

namespace App\Http\Controllers\Informes;

use App\Http\Controllers\Controller;
use App\Models\Informe;
use App\Models\RegistroGlobal;
use App\Traits\InformesHelperTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Exports\TB9Export;
use Maatwebsite\Excel\Facades\Excel;

class Tb9Controller extends Controller
{
    use InformesHelperTrait;

    public function __construct(private \App\Services\RegistroGlobalService $service)
    {
    }

    public function index(Request $request)
    {
        if (!$request->ajax() && $request->getQueryString()) {
            return redirect()->route('informes.tb9');
        }

        $latestAno = RegistroGlobal::whereNotNull('ano')->where('ano', '>', 1900)->orderBy('ano', 'desc')->value('ano');
        $ano = $request->input('ano', $latestAno ?: date('Y'));
        $mes = $request->input('mes', '');
        if (empty($mes)) {
            $mes = $this->resolverMesPorDefecto((string)$ano, true);
        }

        $jornada = $request->input('jornada', 'TODAS') ?: 'TODAS';
        $selectedProfs = (array) $request->input('profesiones', []);

        $anos = RegistroGlobal::distinct()
            ->whereNotNull('ano')
            ->where('ano', '>', 1900)
            ->orderBy('ano', 'desc')
            ->pluck('ano');

        if ($anos->isEmpty()) {
            $anos = collect([date('Y')]);
        }

        $mesMap = [
            'ENERO' => 1, 'FEBRERO' => 2, 'MARZO' => 3, 'ABRIL' => 4,
            'MAYO' => 5, 'JUNIO' => 6, 'JULIO' => 7, 'AGOSTO' => 8,
            'SEPTIEMBRE' => 9, 'OCTUBRE' => 10, 'NOVIEMBRE' => 11, 'DICIEMBRE' => 12
        ];

        $mesesRaw = RegistroGlobal::where('ano', $ano)
            ->whereNotNull('mes')
            ->where('mes', '!=', '')
            ->distinct()
            ->pluck('mes')
            ->toArray();

        if (empty($mesesRaw)) {
            $mesesRaw = RegistroGlobal::whereNotNull('mes')->where('mes', '!=', '')->distinct()->pluck('mes')->toArray();
        }

        $meses = collect($mesesRaw)->map(fn($m) => strtoupper(trim($m)))
            ->unique()
            ->sort(fn($a, $b) => ($mesMap[$a] ?? 0) <=> ($mesMap[$b] ?? 0))
            ->values();

        $jornadas = RegistroGlobal::distinct()->whereNotNull('jornada')->where('jornada', '!=', '')->orderBy('jornada')->pluck('jornada');
        $profesiones = RegistroGlobal::distinct()->whereNotNull('prof')->where('prof', '!=', '')->orderBy('prof')->pluck('prof');

        $query = RegistroGlobal::query()->where('ano', $ano)->where('mes', $mes);
        if ($jornada && $jornada != 'TODAS')
            $query->where('jornada', $jornada);
        if (!empty($selectedProfs))
            $query->whereIn('prof', $selectedProfs);

        $rawData = $query->select('rango_5', 'fecha', DB::raw('count(id) as total'))
            ->groupBy('rango_5', 'fecha')
            ->orderBy('rango_5')
            ->get();

        $data = [];
        $dates = [];
        foreach ($rawData as $row) {
            $f = \Carbon\Carbon::parse($row->fecha);
            $dateStr = $f->format('d/m/Y');
            $dates[$f->timestamp] = $dateStr;
            $rango = $row->rango_5 ?: 'SIN RANGO';
            if (!isset($data[$rango]))
                $data[$rango] = ['dates' => []];
            $data[$rango]['dates'][$dateStr] = $row->total;
        }

        ksort($dates);
        $headers = array_values($dates);
        $fechasObjs = array_map(fn($h) => ['fecha' => $h, 'obj' => \Carbon\Carbon::createFromFormat('d/m/Y', $h)], $headers);

        if ($request->ajax()) {
            return view('informes.tb9_content', compact('anos', 'meses', 'jornadas', 'profesiones', 'ano', 'mes', 'jornada', 'selectedProfs', 'data', 'headers', 'fechasObjs'));
        }
        return view('informes.tb9', compact('anos', 'meses', 'jornadas', 'profesiones', 'ano', 'mes', 'jornada', 'selectedProfs', 'data', 'headers', 'fechasObjs'));
    }

    public function export(Request $request)
    {
        $latestAno = RegistroGlobal::whereNotNull('ano')->where('ano', '>', 1900)->orderBy('ano', 'desc')->value('ano');
        $ano = $request->input('ano', $latestAno ?: date('Y'));
        $mes = $request->input('mes', '');
        if (empty($mes)) {
            $mes = $this->resolverMesPorDefecto((string)$ano, true);
        }
        $jornada = $request->input('jornada', 'TODAS');
        $selectedProfs = (array) $request->input('profesiones', []);

        $query = RegistroGlobal::query()->where('ano', $ano);
        if ($mes)
            $query->where('mes', $mes);
        if ($jornada && $jornada != 'TODAS')
            $query->where('jornada', $jornada);
        if (!empty($selectedProfs))
            $query->whereIn('prof', $selectedProfs);

        $rawData = $query->select('rango_5', 'fecha', DB::raw('count(id) as total'))
            ->groupBy('rango_5', 'fecha')
            ->orderBy('rango_5')
            ->get();

        $data = [];
        $dates = [];
        foreach ($rawData as $row) {
            $f = \Carbon\Carbon::parse($row->fecha);
            $dateStr = $f->format('d/m/Y');
            $dates[$f->timestamp] = $dateStr;
            $rango = $row->rango_5 ?: 'SIN RANGO';
            if (!isset($data[$rango]))
                $data[$rango] = ['dates' => []];
            $data[$rango]['dates'][$dateStr] = $row->total;
        }
        ksort($dates);
        $headers = array_values($dates);

        return Excel::download(new TB9Export($data, $headers, $ano, $mes, $jornada, $selectedProfs),
            'Reporte_TB9_' . $mes . '_' . $ano . '_' . date('His') . '.xlsx');
    }
}
