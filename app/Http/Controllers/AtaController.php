<?php

namespace App\Http\Controllers;

use App\Models\RegistroGlobal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\RegistrosAt1Export;
use App\Imports\RegistrosAt1Import;
use App\Services\ColumnValidationService;

class AtaController extends Controller
{
    public function __construct(private
        \App\Services\RegistroGlobalService $service
        )
    {
    }

    /**
     * Método helper compartido para obtener años/meses disponibles
     */
    private function getAnosMesesDisponibles(): array
    {
        Cache::forget('registros.years');
        
        $currentYear = (int)date('Y');
        $dbAnos = $this->service->getAnosDisponibles()->toArray();
        $rangeAnos = range($currentYear - 4, $currentYear + 5);
        $anos = collect(array_unique(array_merge($dbAnos, $rangeAnos)))->sortDesc()->values();

        if ($anos->isEmpty()) {
            $anos = collect([$currentYear]);
            $anoDefault = $currentYear;
        }
        else {
            // Priorizar el año más reciente que tenga datos (hasta el año actual máximo)
            $latestWithData = RegistroGlobal::where('ano', '<=', $currentYear)->orderBy('ano', 'desc')->first();
            $anoDefault = $latestWithData ? $latestWithData->ano : ($anos->contains($currentYear) ? $currentYear : $anos->first());
        }

        $mesMap = [
            1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL',
            5 => 'MAYO', 6 => 'JUNIO', 7 => 'JULIO', 8 => 'AGOSTO',
            9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE'
        ];

        // Buscar el último mes con datos para ese año (el más reciente cronológicamente)
        $mesesConDatos = RegistroGlobal::where('ano', $anoDefault)
            ->distinct()
            ->pluck('mes')
            ->toArray();

        // Ordenar cronológicamente y tomar el último
        $ordenMesMap = array_flip($mesMap); // 'ENERO' => 1, ...
        usort($mesesConDatos, function ($a, $b) use ($ordenMesMap) {
            return ($ordenMesMap[strtoupper($a)] ?? 0) <=> ($ordenMesMap[strtoupper($b)] ?? 0);
        });
        $mesDefault = !empty($mesesConDatos) ? end($mesesConDatos) : ($anoDefault == $currentYear ? $mesMap[(int)date('n')] : 'ENERO');
        reset($mesesConDatos);

        // Reusar mesesConDatos como $meses (ya fueron obtenidos arriba)
        $meses = $mesesConDatos;
        if (empty($meses) && $anoDefault == $currentYear) {
            $meses = [$mesDefault];
        }

        // Ya están ordenados; asegurarnos de tener orden correcto
        $ordenMeses = array_flip($mesMap);
        usort($meses, function ($a, $b) use ($ordenMeses) {
            return ($ordenMeses[strtoupper($a)] ?? 0) <=> ($ordenMeses[strtoupper($b)] ?? 0);
        });

        return compact('anos', 'anoDefault', 'meses', 'mesDefault');
    }


    public function index(Request $request)
    {

        $columns = [
            'ano', 'mes', 'numero', 'cm', 'medico', 'prof', 'fecha', 'se', 'exp', 'sexo', 'edad', 'tipo',
            'rango', 'rango_2', 'rango_3', 'rango_4', 'rango_5', 'cond', 'cod_col', 'colonia',
            'cod_1', 'diagnostico_1', 'cond_1', 'sg',
            'cod_2', 'diagnostico_2', 'cond_2',
            'cod_3', 'diagnostico_3', 'cond_3',
            'cod_4', 'diagnostico_4', 'cond_4',
            'cod_5', 'diagnostico_5', 'cond_5',
            'cod_6', 'diagnostico_6', 'cond_6',
            'cod_7', 'diagnostico_7', 'cond_7',
            'referido_a', 'referido_de', 'pg_emb', 'jornada', 'sm'
        ];

        $data = $this->getAnosMesesDisponibles();

        // Cargar validaciones de diagnósticos para frontend
        $validacionesDiagnosticos = \App\Models\Diagnostico::where(function ($query) {
            $query->whereNotNull('edad_minima')
                ->orWhereNotNull('edad_maxima')
                ->orWhere('sexo_permitido', '!=', 'ambos')
                ->orWhere('requiere_embarazo', true)
                ->orWhere('es_pediatrico', true)
                ->orWhere('es_adulto', true);
        })->get(['codigo', 'patologia', 'edad_minima', 'edad_maxima', 'tipo_edad',
            'sexo_permitido', 'requiere_embarazo', 'es_pediatrico', 'es_adulto', 'notas_validacion']);

        return view('ata.index', array_merge($data, compact('columns', 'validacionesDiagnosticos')));
    }

    private function sortMesesCronologicamente($meses)
    {
        $ordenMeses = [
            'ENERO' => 1, 'FEBRERO' => 2, 'MARZO' => 3, 'ABRIL' => 4,
            'MAYO' => 5, 'JUNIO' => 6, 'JULIO' => 7, 'AGOSTO' => 8,
            'SEPTIEMBRE' => 9, 'OCTUBRE' => 10, 'NOVIEMBRE' => 11, 'DICIEMBRE' => 12
        ];

        return $meses->sort(function ($a, $b) use ($ordenMeses) {
            return ($ordenMeses[strtoupper($a)] ?? 0) <=> ($ordenMeses[strtoupper($b)] ?? 0);
        })->values();
    }

    public function getMeses(Request $request)
    {
        $anos = $request->input('anos', []);

        $query = RegistroGlobal::query();
        if (!empty($anos)) {
            $query->whereIn('ano', (array)$anos);
        }

        $meses = $query->distinct()->pluck('mes')->toArray();

        // Sort months chronologically
        $ordenMeses = [
            'ENERO' => 1, 'FEBRERO' => 2, 'MARZO' => 3, 'ABRIL' => 4,
            'MAYO' => 5, 'JUNIO' => 6, 'JULIO' => 7, 'AGOSTO' => 8,
            'SEPTIEMBRE' => 9, 'OCTUBRE' => 10, 'NOVIEMBRE' => 11, 'DICIEMBRE' => 12
        ];

        usort($meses, function ($a, $b) use ($ordenMeses) {
            return ($ordenMeses[strtoupper($a)] ?? 0) <=> ($ordenMeses[strtoupper($b)] ?? 0);
        });

        // Get last month with data for these years
        $lastMonth = null;
        if (!empty($anos)) {
            $lastRecord = RegistroGlobal::whereIn('ano', (array)$anos)
                ->orderBy('ano', 'desc')
                ->orderBy(DB::raw("FIELD(mes, 'DICIEMBRE','NOVIEMBRE','OCTUBRE','SEPTIEMBRE','AGOSTO','JULIO','JUNIO','MAYO','ABRIL','MARZO','FEBRERO','ENERO')"), 'asc')
                ->first();
            if ($lastRecord) {
                $lastMonth = $lastRecord->mes;
            }
        }

        return response()->json([
            'success' => true,
            'meses' => $meses,
            'lastMonth' => $lastMonth
        ]);
    }

    public function getData(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        try {
            $draw = $request->input('draw');
            $start = $request->input('start', 0);
            $length = $request->input('length', 100);

            $anos = $request->input('anos', []);
            $meses = $request->input('meses', []);
            Log::info('ATA Request Params:', ['draw' => $draw, 'start' => $start, 'length' => $length, 'anos' => $anos, 'meses' => $meses]);

            $query = RegistroGlobal::query();

            if (!empty($anos)) {
                $query->whereIn('ano', (array)$anos);
            }
            if (!empty($meses)) {
                $query->whereIn('mes', (array)$meses);
            }

            // Global Search (DataTables standard)
            $searchValue = $request->input('search')['value'] ?? null;
            if ($searchValue) {
                $query->where(function ($q) use ($searchValue) {
                    $q->where('medico', 'LIKE', "%$searchValue%")
                        ->orWhere('diagnostico_1', 'LIKE', "%$searchValue%")
                        ->orWhere('numero', 'LIKE', "%$searchValue%")
                        ->orWhere('exp', 'LIKE', "%$searchValue%");
                });
            }

            // Custom Diagnostic Search (Multi-tag Modal + Live search while typing)
            $diagTagsJson = $request->input('diag_tags');
            $diagTags = $diagTagsJson ? json_decode($diagTagsJson, true) : [];
            $tempDiag = trim($request->input('temp_diag', ''));
            $tempCond = $request->input('temp_cond');

            if (!empty($diagTags) || !empty($tempDiag)) {
                $query->where(function ($q) use ($diagTags, $tempDiag, $tempCond) {
                    $allSearchTerms = $diagTags;
                    if (!empty($tempDiag)) {
                        $allSearchTerms[] = ['term' => $tempDiag, 'cond' => $tempCond];
                    }

                    foreach ($allSearchTerms as $index => $search) {
                        $term = trim($search['term']);
                        $cond = $search['cond'] ?? null;

                        $method = ($index === 0) ? 'where' : 'orWhere';

                        $q->$method(function ($sq) use ($term, $cond) {
                                    for ($i = 1; $i <= 7; $i++) {
                                        $sq->orWhere(function ($ssq) use ($i, $term, $cond) {
                                                    $ssq->where(function ($finalQ) use ($i, $term) {
                                                            $finalQ->where("diagnostico_$i", 'LIKE', "%$term%")
                                                                ->orWhere("cod_$i", 'LIKE', "%$term%");
                                                        }
                                                        );
                                                        if (!empty($cond)) {
                                                            $ssq->where("cond_$i", $cond);
                                                        }
                                                    }
                                                    );
                                                }
                                            }
                                            );
                                        }
                                    });
            }

            $recordsTotal = RegistroGlobal::count();

            // Column Search / Filters
            $requestColumns = $request->input('columns', []);
            foreach ($requestColumns as $column) {
                $colSearch = $column['search']['value'] ?? null;
                $dataName = $column['data'] ?? null;

                if ($colSearch && $dataName) {
                    // Validar que la columna existe en la lista blanca
                    if (!ColumnValidationService::isValidColumn($dataName)) {
                        continue;
                    }

                    $isRegex = str_starts_with($colSearch, '^(') && str_ends_with($colSearch, ')$');
                    $isExact = !$isRegex && str_starts_with($colSearch, '^') && str_ends_with($colSearch, '$');

                    if ($isRegex) {
                        $value = substr($colSearch, 2, -2);
                    }
                    elseif ($isExact) {
                        $value = substr($colSearch, 1, -1);
                    }
                    else {
                        $value = $colSearch;
                    }

                    if (in_array($dataName, ['cod', 'diagnostico', 'cond_diagnostico'])) {
                        $query->where(function ($q) use ($dataName, $value, $isExact, $isRegex) {
                            $prefix = ($dataName === 'cod' ? 'cod_' : ($dataName === 'diagnostico' ? 'diagnostico_' : 'cond_'));
                            for ($i = 1; $i <= 7; $i++) {
                                $col = $prefix . $i;
                                if ($isRegex) {
                                    $q->orWhereRaw("$col REGEXP ?", [$value]);
                                }
                                elseif ($isExact) {
                                    $q->orWhere($col, $value);
                                }
                                else {
                                    $q->orWhere($col, 'LIKE', "%$value%");
                                }
                            }
                        });
                    }
                    else {
                        if ($isRegex) {
                            $query->whereRaw("$dataName REGEXP ?", [$value]);
                        }
                        elseif ($isExact) {
                            $query->where($dataName, $value);
                        }
                        else {
                            $query->where($dataName, 'LIKE', "%$value%");
                        }
                    }
                }
            }

            $recordsFiltered = (clone $query)->count();

            // Card and Footer statistics - Removed heavy unused calculations
            $stats = [
                'total' => $recordsFiltered,
                'medicos_count' => 0,
                'general_n' => 0,
                'general_s' => 0,
                'diag_n' => 0,
                'diag_s' => 0,
                'disease_stats' => [
                    'diarrea' => 0,
                    'neumonia' => 0,
                    'hipertension' => 0,
                    'diabetes' => 0,
                    'dengue_ss' => 0,
                    'dengue_cs' => 0,
                    'dengue_grave' => 0
                ]
            ];

            // Sorting
            $order = $request->input('order');
            if ($order && isset($order[0])) {
                $colIdx = $order[0]['column'];
                $colDir = $order[0]['dir'];
                $colName = $request->input('columns')[$colIdx]['data'] ?? null;
                if ($colName && $colName !== null && $colName !== '') {
                    $query->orderBy($colName, $colDir);
                }
            }
            else {
                $query->orderBy('fecha', 'desc')->orderBy('medico', 'asc')->orderByRaw('CAST(numero AS UNSIGNED) ASC');
            }

            $registros = $query->offset($start)->limit($length)->get();

            $data = $registros->map(function ($registro) {
                // Ensure we have an array, whether it's a Model or stdClass
                $arr = method_exists($registro, 'toArray') ? $registro->toArray() : (array)$registro;
                $arr['fecha'] = $registro->fecha ? \Carbon\Carbon::parse($registro->fecha)->format('d-m-Y') : null;
                return $arr;
            });

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $data,
                'stats' => $stats
            ]);

        }
        catch (\Throwable $e) {
            Log::error('AtaController Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }


    public function getColumnValues(Request $request)
    {
        $column = $request->input('column');
        $ano = $request->input('ano');
        $mes = $request->input('mes');

        Log::info('getColumnValues Request:', ['column' => $column, 'ano' => $ano, 'mes' => $mes, 'filters' => $request->input('filters')]);

        // Manejar columnas de diagnósticos (individuales o expandidos)
        $isDiagGroup = false;
        $diagPrefix = '';

        if (in_array($column, ['cod', 'diagnostico', 'cond_diagnostico'])) {
            $isDiagGroup = true;
            $diagPrefix = ($column === 'cod' ? 'cod_' : ($column === 'diagnostico' ? 'diagnostico_' : 'cond_'));
        }
        elseif (preg_match('/^(cod|diagnostico|cond)_([1-7])$/', $column, $matches)) {
            $isDiagGroup = true;
            $diagPrefix = $matches[1] . '_';
        }

        if ($isDiagGroup) {
            $values = [];
            $realColumns = [];
            for ($i = 1; $i <= 7; $i++) {
                $realColumns[] = $diagPrefix . $i;
            }

            foreach ($realColumns as $realColumn) {
                $query = RegistroGlobal::query();

                // Filtro de AÑO (Global)
                if (!empty($request->input('global_anos'))) {
                    $query->whereIn('ano', (array)$request->input('global_anos'));
                }
                elseif (!empty($ano)) {
                    if (str_contains($ano, ','))
                        $query->whereIn('ano', explode(',', $ano));
                    else
                        $query->where('ano', $ano);
                }

                // Filtro de MES (Global)
                if (!empty($request->input('global_meses'))) {
                    $query->whereIn('mes', (array)$request->input('global_meses'));
                }
                elseif (!empty($mes) && $mes !== 'TODOS') {
                    if (str_contains($mes, ','))
                        $query->whereIn('mes', explode(',', $mes));
                    else
                        $query->where('mes', $mes);
                }

                // En lugar de excluir vacíos, los permitimos para poder auditarlos
                /*
                $query->whereNotNull($realColumn)
                    ->where($realColumn, '!=', '')
                    ->where($realColumn, '!=', '0');
                */

                // Apply filters if present
                if ($request->has('filters')) {
                    $filters = $request->input('filters');
                    if (is_array($filters)) {
                        foreach ($filters as $fCol => $fVal) {
                            if (empty($fVal) && $fVal !== '0')
                                continue;

                            // Saltar el filtro de la propia columna que estamos consultando
                            if ($fCol === $column)
                                continue;
                            if (($column === 'diagnostico' || $column === 'cod' || $column === 'cond_diagnostico') &&
                            ($fCol === 'diagnostico' || $fCol === 'cod' || $fCol === 'cond_diagnostico')) {
                                // En grupos diagnósticos, si pedimos 'cod' no filtramos por 'diagnostico' de la misma fila lógica
                                // pero para ser simples, saltamos si es el mismo grupo temático
                                continue;
                            }

                            if (in_array($fCol, ['cod', 'diagnostico', 'cond_diagnostico'])) {
                                $query->where(function ($q) use ($fCol, $fVal) {
                                    $prefix = ($fCol === 'cod' ? 'cod_' : ($fCol === 'diagnostico' ? 'diagnostico_' : 'cond_'));
                                    $regexVal = is_array($fVal) ? implode('|', $fVal) : $fVal;
                                    for ($i = 1; $i <= 7; $i++)
                                        $q->orWhere($prefix . $i, 'REGEXP', $regexVal);
                                });
                            }
                            else {
                                $regexVal = is_array($fVal) ? implode('|', $fVal) : $fVal;
                                $query->where($fCol, 'REGEXP', $regexVal);
                            }
                        }
                    }
                }

                // Apply quick text search if present
                if ($request->has('search_diag')) {
                    $searchTerm = $request->input('search_diag');
                    if (!empty($searchTerm)) {
                        $query->where(function ($q) use ($searchTerm) {
                            for ($i = 1; $i <= 7; $i++) {
                                $q->orWhere("diagnostico_$i", 'LIKE', "%{$searchTerm}%");
                                $q->orWhere("cod_$i", 'LIKE', "%{$searchTerm}%");
                            }
                        });
                    }
                }

                $columnValues = $query->distinct()
                    ->pluck($realColumn)
                    ->toArray();

                $values = array_merge($values, $columnValues);
            }

            $values = array_unique($values);
            sort($values);

            return response()->json([
                'success' => true,
                'values' => array_values($values),
                'isDate' => false
            ]);
        }

        // Standard logic for simple columns
        // Validar que la columna está en la lista blanca
        $validatedColumn = ColumnValidationService::validateColumn($column);
        if (!$validatedColumn) {
            return response()->json([
                'success' => false,
                'message' => 'Columna no válida'
            ], 400);
        }

        $query = RegistroGlobal::query();

        // Filtro de AÑO (Global)
        if (!empty($request->input('global_anos'))) {
            $query->whereIn('ano', (array)$request->input('global_anos'));
        }
        elseif (!empty($ano)) {
            if (str_contains($ano, ','))
                $query->whereIn('ano', explode(',', $ano));
            else
                $query->where('ano', $ano);
        }

        // Filtro de MES (Global)
        if (!empty($request->input('global_meses'))) {
            $query->whereIn('mes', (array)$request->input('global_meses'));
        }
        elseif (!empty($mes) && $mes !== 'TODOS') {
            if (str_contains($mes, ','))
                $query->whereIn('mes', explode(',', $mes));
            else
                $query->where('mes', $mes);
        }

        if ($request->has('filters')) {
            $filters = $request->input('filters');
            if (is_array($filters)) {
                foreach ($filters as $fCol => $fVal) {
                    if (empty($fVal) && $fVal !== '0')
                        continue;

                    // Saltar filtro propio
                    if ($fCol === $column)
                        continue;

                    // Validar que la columna está en la lista blanca
                    if (!ColumnValidationService::isValidColumn($fCol)) {
                        continue;
                    }

                    // Use whereRaw for REGEXP
                    if (in_array($fCol, ['cod', 'diagnostico', 'cond_diagnostico'])) {
                        $query->where(function ($q) use ($fCol, $fVal) {
                            $prefix = ($fCol === 'cod' ? 'cod_' : ($fCol === 'diagnostico' ? 'diagnostico_' : 'cond_'));
                            $regexValue = is_array($fVal) ? implode('|', $fVal) : $fVal;
                            for ($i = 1; $i <= 7; $i++) {
                                $col = $prefix . $i;
                                $q->orWhereRaw("$col REGEXP ?", [$regexValue]);
                            }
                        });
                    }
                    else {
                        $regexValue = is_array($fVal) ? implode('|', $fVal) : $fVal;
                        $query->whereRaw("$fCol REGEXP ?", [$regexValue]);
                    }
                }
            }
        }

        // Usamos COALESCE para tratar NULL y '' como lo mismo
        $values = $query->select(DB::raw("DISTINCT COALESCE($validatedColumn, '') as $validatedColumn"))->pluck($validatedColumn);

        if ($validatedColumn === 'mes') {
            $ordenMeses = [
                'ENERO' => 1, 'FEBRERO' => 2, 'MARZO' => 3, 'ABRIL' => 4,
                'MAYO' => 5, 'JUNIO' => 6, 'JULIO' => 7, 'AGOSTO' => 8,
                'SEPTIEMBRE' => 9, 'OCTUBRE' => 10, 'NOVIEMBRE' => 11, 'DICIEMBRE' => 12
            ];
            $values = $values->sort(function ($a, $b) use ($ordenMeses) {
                return ($ordenMeses[strtoupper($a)] ?? 0) <=> ($ordenMeses[strtoupper($b)] ?? 0);
            });
        }
        elseif ($column === 'ano' || $column === 'numero') {
            $values = $values->sort();
        }
        else {
            $values = $values->sort();
        }

        $values = $values->values()->toArray();

        return response()->json([
            'success' => true,
            'values' => $values,
            'isDate' => in_array($column, ['fecha']) // Simple check for date column
        ]);
    }

    // Helper to update a single record (for inline editing)
    // Helper to update a single record (for inline editing)
    public function update(Request $request, $id)
    {
        try {
            $registro = RegistroGlobal::findOrFail($id);
            // Ignorar campos de control
            $data = $request->except(['_token', '_method']);

            $registro->update($data);

            // Formatear fecha para respuesta JSON
            if ($registro->fecha) {
                try {
                    $registro->fecha = \Carbon\Carbon::parse($registro->fecha)->format('d-m-Y');
                } catch (\Throwable $e) {}
            }

            return response()->json(['success' => true, 'data' => $registro]);
        }
        catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return response()->json(['success' => false, 'message' => 'Registro no encontrado'], 404);
        }
        catch (\Throwable $e) {
            Log::error("Error actualizando ATA ID $id: " . $e->getMessage() . "\n" . $e->getTraceAsString());
            return response()->json(['success' => false, 'message' => 'Error servidor: ' . $e->getMessage()], 500);
        }
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);
        if (!is_array($ids) || empty($ids)) {
            return response()->json([
                'success' => false,
                'message' => 'No se recibieron registros para eliminar.',
            ], 422);
        }

        try {
            RegistroGlobal::whereIn('id', $ids)->delete();

            return response()->json([
                'success' => true,
                'message' => 'Registros eliminados correctamente.',
            ]);
        }
        catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al eliminar los registros: ' . $e->getMessage(),
            ], 500);
        }
    }


    public function getSuggestions(Request $request)
    {
        $term = trim($request->input('term', ''));
        if (empty($term))
            return response()->json([]);

        $query = RegistroGlobal::query();
        if ($request->has('anos'))
            $query->whereIn('ano', (array)$request->input('anos'));
        if ($request->has('meses'))
            $query->whereIn('mes', (array)$request->input('meses'));

        $results = collect();
        // Buscamos en los 7 slots de diagnóstico y también en códigos
        for ($i = 1; $i <= 7; $i++) {
            $colDiag = "diagnostico_$i";
            $colCod = "cod_$i";

            // Sugerencias por nombre
            $names = (clone $query)->where($colDiag, 'LIKE', "%$term%")
                ->distinct()->limit(10)->pluck($colDiag);
            $results = $results->merge($names);

            // Sugerencias por código (retornamos el nombre asociado)
            $byCod = (clone $query)->where($colCod, 'LIKE', "%$term%")
                ->distinct()->limit(5)->pluck($colDiag);
            $results = $results->merge($byCod);
        }

        return response()->json($results->filter()->unique()->sort()->values()->take(30));
    }

    public function registrosAt1(Request $request)
    {
        $data = $this->getAnosMesesDisponibles();

        $columns = [
            'id', 'numero', 'cm', 'medico', 'ano', 'mes', 'prof', 'fecha', 'se', 'exp', 'sexo', 'edad', 'tipo',
            'rango', 'rango_2', 'rango_3', 'rango_4', 'rango_5', 'cond', 'cod_col', 'colonia',
            'cod_1', 'diagnostico_1', 'cond_1', 'sg',
            'cod_2', 'diagnostico_2', 'cond_2',
            'cod_3', 'diagnostico_3', 'cond_3',
            'cod_4', 'diagnostico_4', 'cond_4',
            'cod_5', 'diagnostico_5', 'cond_5',
            'cod_6', 'diagnostico_6', 'cond_6',
            'cod_7', 'diagnostico_7', 'cond_7',
            'referido_a', 'referido_de', 'pg_emb', 'jornada', 'sm'
        ];

        // Cargar validaciones de diagnósticos desde la tabla diagnosticos
        $validacionesDiagnosticos = \App\Models\Diagnostico::where(function ($query) {
            $query->whereNotNull('edad_minima')
                ->orWhereNotNull('edad_maxima')
                ->orWhere('sexo_permitido', '!=', 'ambos')
                ->orWhere('requiere_embarazo', true)
                ->orWhere('es_pediatrico', true)
                ->orWhere('es_adulto', true);
        })->get(['codigo', 'patologia', 'edad_minima', 'edad_maxima', 'tipo_edad',
            'sexo_permitido', 'requiere_embarazo', 'es_pediatrico', 'es_adulto', 'notas_validacion']);

        // Cargar condicionamientos adicionales desde condicionamientos_diagnosticos
        $condicionamientos = \App\Models\CondicionamientoDiagnostico::all()->keyBy('codigo_diagnostico');

        // Combinar ambas fuentes de datos
        $validacionesCombinadas = $validacionesDiagnosticos->map(function ($diag) use ($condicionamientos) {
            $cond = $condicionamientos->get($diag->codigo);

            // Si existe condicionamiento, combinar datos (condicionamiento tiene prioridad)
            if ($cond) {
                return [
                'codigo' => $diag->codigo,
                'patologia' => $diag->patologia,
                'edad_minima' => $cond->edad_min ?? $diag->edad_minima,
                'edad_maxima' => $cond->edad_max ?? $diag->edad_maxima,
                'tipo_edad' => $diag->tipo_edad,
                'sexo_permitido' => $diag->sexo_permitido,
                'requiere_embarazo' => $cond->embarazo ?? $diag->requiere_embarazo,
                'es_pediatrico' => $cond->pediatrico ?? $diag->es_pediatrico,
                'es_adulto' => $cond->adulto ?? $diag->es_adulto,
                'sg_min' => $cond->sg_min,
                'sg_max' => $cond->sg_max,
                'notas_validacion' => $cond->notas_validacion ?? $diag->notas_validacion
                ];
            }

            // Ensure we have an array
            return method_exists($diag, 'toArray') ? $diag->toArray() : (array)$diag;
        });

        return view('ata.registros_at1', array_merge($data, compact('columns', 'validacionesCombinadas')));
    }

    public function edit($id)
    {
        return redirect()->route('registros.show', $id);
    }

    public function exportAt1(Request $request)
    {
        try {
            // Eliminar límites de memoria y tiempo para exportaciones masivas sin formato adicional
            ini_set('memory_limit', '-1');
            set_time_limit(0);

            $anos = (array)$request->input('anos', []);
            $meses = (array)$request->input('meses', []);
            $filters = (array)$request->input('filters', []);

            $filename = 'Registros_AT1_' . date('Y-m-d_His') . '.xlsx';

            if (ob_get_length()) ob_end_clean();
            return Excel::download(
                new RegistrosAt1Export($anos, $meses, $filters),
                $filename
            );
        }
        catch (\Exception $e) {
            Log::error('Error en exportación AT1: ' . $e->getMessage(), [
                'stack' => $e->getTraceAsString(),
                'params' => $request->all()
            ]);
            throw $e;
        }
    }

    public function importAt1(Request $request)
    {
        $request->validate([
            'file' => 'required|mimes:xlsx,xls,csv|max:10240'
        ]);

        try {
            Excel::import(new RegistrosAt1Import, $request->file('file'));

            return response()->json([
                'success' => true,
                'message' => 'Archivo importado correctamente'
            ]);
        }
        catch (\Exception $e) {
            Log::error('Error importando archivo AT1: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al importar el archivo: ' . $e->getMessage()
            ], 500);
        }
    }

    public function getDataSimple(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        try {
            $limit = $request->has('length') ? (int)$request->input('length') : null;
            $start = (int)$request->input('start', 0);
            $anos = $request->input('anos', []);
            $meses = $request->input('meses', []);

            Log::info('Registros AT1 Data Request:', [
                'anos' => $anos,
                'meses' => $meses,
                'start' => $start,
                'limit' => $limit
            ]);

            $query = RegistroGlobal::query();

            if (!empty($anos)) {
                $query->whereIn('ano', (array)$anos);
            }
            if (!empty($meses)) {
                $query->whereIn('mes', (array)$meses);
            }

            // Total de registros para esta consulta
            $recordsTotal = (clone $query)->count();

            $query->select('*', DB::raw("DATE_FORMAT(fecha, '%d-%m-%Y') as fecha_formateada"))
                ->orderBy('fecha', 'desc')
                ->orderBy('medico', 'asc')
                ->orderByRaw('CAST(numero AS UNSIGNED) ASC');

            if ($start > 0) {
                $query->offset($start);
            }

            if (!empty($limit) && $limit > 0) {
                $query->limit($limit);
            }

            $data = $query->get()->map(function ($registro) {
                if ($registro->fecha_formateada) {
                    $registro->fecha = $registro->fecha_formateada;
                }
                unset($registro->fecha_formateada);
                return $registro;
            });

            return response()->json([
                'success' => true,
                'data' => $data,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsTotal,
                'hasMore' => !empty($limit) && $limit > 0 && ($start + count($data)) < $recordsTotal
            ]);
        }
        catch (\Exception $e) {
            Log::error('Error en getDataSimple: ' . $e->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar los datos: ' . $e->getMessage()
            ], 500);
        }
    }


    public function updateCell(Request $request)
    {
        try {
            $id = $request->id;
            $field = $request->field;
            $value = $request->value;

            $registro = RegistroGlobal::find($id);
            if (!$registro) {
                return response()->json(['success' => false, 'message' => 'Registro no encontrado'], 404);
            }

            // Validar que el campo sea uno de los permitidos (evitar inyección masiva si aplica)
            $registro->{ $field} = $value;
            $registro->save();

            return response()->json(['success' => true]);
        }
        catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}
