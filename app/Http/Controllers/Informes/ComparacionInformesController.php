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
        $anos = $this->getAnosDisponibles();
        
        $anoInput = $request->input('ano');
        if (empty($anoInput) || !is_numeric($anoInput) || (int)$anoInput <= 1900) {
            $ano = (string)$this->resolverUltimoAnoConDatos();
        } else {
            $ano = (string)$anoInput;
        }

        $meses = $this->getMesesDisponibles($ano);
        if ($meses->isEmpty()) {
            $meses = collect(['ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE']);
        }

        $mesInput = strtoupper(trim((string)$request->input('mes', '')));
        if (empty($mesInput) || !$meses->contains($mesInput)) {
            $mes = $this->resolverMesPorDefecto($ano, true);
        } else {
            $mes = $mesInput;
        }

        $jornadas = $this->getJornadasDisponibles();
        $jornada = strtoupper(trim((string)$request->input('jornada', 'TODAS'))) ?: 'TODAS';

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
