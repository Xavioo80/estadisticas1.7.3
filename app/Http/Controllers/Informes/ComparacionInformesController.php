<?php

namespace App\Http\Controllers\Informes;

use App\Http\Controllers\Controller;
use App\Models\Informe;
use App\Models\RegistroGlobal;
use App\Services\ComparacionInformesService;
use App\Traits\InformesHelperTrait;
use Illuminate\Http\Request;

class ComparacionInformesController extends Controller
{
    use InformesHelperTrait;

    public function __construct(private ComparacionInformesService $service)
    {
    }

    /**
     * Endpoint principal para obtener la matriz de comparación cruzada.
     */
    public function index(Request $request)
    {
        $helperData = $this->getAnosMesesDisponiblesInformes();
        $anos = $helperData['anos'];
        $meses = $helperData['meses'];

        $ano = $request->input('ano', $helperData['anoDefault']);
        $mes = $request->input('mes', '');
        if (empty($mes)) {
            $mes = $this->resolverMesPorDefecto($ano);
        }

        $jornada = $request->input('jornada', 'TODAS') ?: 'TODAS';
        $jornadas = Informe::distinct()->whereNotNull('jornada')->where('jornada', '!=', '')->pluck('jornada');

        $resultado = $this->service->comparar($ano, $mes, $jornada);

        if ($request->wantsJson() || $request->ajax()) {
            if ($request->has('json_only')) {
                return response()->json($resultado);
            }
            return view('informes.partials.modal_comparacion_cruzada_content', compact('resultado', 'anos', 'meses', 'jornadas', 'ano', 'mes', 'jornada'))->render();
        }

        return view('informes.partials.modal_comparacion_cruzada_content', compact('resultado', 'anos', 'meses', 'jornadas', 'ano', 'mes', 'jornada'));
    }

    /**
     * Endpoint para obtener el desglose detallado de registros de una condición auditada.
     */
    public function detalles(Request $request)
    {
        $id = $request->input('id');
        $ano = $request->input('ano');
        $mes = strtoupper(trim($request->input('mes')));
        $jornada = $request->input('jornada', 'TODAS');

        $infQuery = Informe::query()->where('ano', $ano)->where('mes', $mes);
        if ($jornada !== 'TODAS') {
            $infQuery->where('jornada', $jornada);
        }
        $informes = $infQuery->get();

        $rgQuery = RegistroGlobal::query()->where('ano', $ano)->where('mes', $mes);
        if ($jornada !== 'TODAS') {
            $rgQuery->where('jornada', $jornada);
        }
        $registrosGlobales = $rgQuery->get();

        return response()->json([
            'id' => $id,
            'ano' => $ano,
            'mes' => $mes,
            'total_informes' => $informes->count(),
            'total_globales' => $registrosGlobales->count(),
        ]);
    }
}
