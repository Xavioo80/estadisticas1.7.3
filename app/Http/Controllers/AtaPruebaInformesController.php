<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\RegistroGlobalPrueba;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exports\InformesAt1PruebaExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class AtaPruebaInformesController extends Controller
{
    private function getAnosMesesDisponiblesBase(): array
    {
        $currentYear = (int) date('Y');
        $dbAnos = RegistroGlobalPrueba::distinct()->orderBy('ano', 'desc')->pluck('ano')->toArray();

        $rangeAnos = range($currentYear - 4, $currentYear + 5);
        $anos = collect(array_unique(array_merge($dbAnos, $rangeAnos)))->sortDesc()->values();

        if ($anos->isEmpty()) {
            $anos = collect([$currentYear]);
            $anoDefault = $currentYear;
        } else {
            $latestWithData = RegistroGlobalPrueba::where('ano', '<=', $currentYear)->orderBy('ano', 'desc')->first();
            $anoDefault = $latestWithData
                ? $latestWithData->ano
                : ($anos->contains($currentYear) ? $currentYear : $anos->first());
        }

        $mesMap = [
            1 => 'ENERO',
            2 => 'FEBRERO',
            3 => 'MARZO',
            4 => 'ABRIL',
            5 => 'MAYO',
            6 => 'JUNIO',
            7 => 'JULIO',
            8 => 'AGOSTO',
            9 => 'SEPTIEMBRE',
            10 => 'OCTUBRE',
            11 => 'NOVIEMBRE',
            12 => 'DICIEMBRE',
        ];

        $meses = RegistroGlobalPrueba::where('ano', $anoDefault)->distinct()->pluck('mes')->toArray();

        if (empty($meses) && $anoDefault == $currentYear) {
            $meses = [$mesMap[(int) date('n')]];
        }

        $ordenMeses = array_flip($mesMap);
        usort($meses, function ($a, $b) use ($ordenMeses) {
            return ($ordenMeses[strtoupper($a)] ?? 0) <=> ($ordenMeses[strtoupper($b)] ?? 0);
        });

        $mesDefault = !empty($meses)
            ? end($meses)
            : ($anoDefault == $currentYear ? $mesMap[(int) date('n')] : 'ENERO');
        reset($meses);

        return compact('anos', 'anoDefault', 'meses', 'mesDefault');
    }

    public function informesAt1(Request $request)
    {
        $data = $this->getAnosMesesDisponiblesBase();

        // Lista de columnas REALES de la tabla (sin expansion)
        $columns = [
            'id',
            'ano',
            'mes',
            'numero',
            'cm',
            'medico',
            'prof',
            'fecha',
            'se',
            'exp',
            'sexo',
            'edad',
            'tipo',
            'rango',
            'rango_2',
            'rango_3',
            'rango_4',
            'rango_5',
            'cond',
            'cod_col',
            'colonia',
            'cod_1',
            'diagnostico_1',
            'cond_1',
            'cod_2',
            'diagnostico_2',
            'cond_2',
            'cod_3',
            'diagnostico_3',
            'cond_3',
            'cod_4',
            'diagnostico_4',
            'cond_4',
            'cod_5',
            'diagnostico_5',
            'cond_5',
            'cod_6',
            'diagnostico_6',
            'cond_6',
            'cod_7',
            'diagnostico_7',
            'cond_7',
            'sg',
            'sg2',
            'referido_a',
            'referido_de',
            'pg_emb',
            'jornada',
            'sm',
            'user_id',
            'created_at'
        ];

        return view('registros.index', array_merge($data, compact('columns')));
    }

    public function getInformesDataSimple(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        try {
            $offset = (int) $request->input('offset', 0);
            $limit = (int) $request->input('limit', 100);
            $anos = $request->input('anos', []);
            $meses = $request->input('meses', []);
            $search = $request->input('search');
            $columnFilters = $request->input('columnFilters', []);

            $query = RegistroGlobalPrueba::query();

            if (!empty($anos))
                $query->whereIn('ano', (array) $anos);
            if (!empty($meses))
                $query->whereIn('mes', (array) $meses);

            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('medico', 'LIKE', "%$search%")
                        ->orWhere('colonia', 'LIKE', "%$search%")
                        ->orWhere('numero', 'LIKE', "%$search%")
                        ->orWhere('exp', 'LIKE', "%$search%")
                        ->orWhere('diagnostico_1', 'LIKE', "%$search%")
                        ->orWhere('diagnostico_2', 'LIKE', "%$search%")
                        ->orWhere('diagnostico_3', 'LIKE', "%$search%")
                        ->orWhere('diagnostico_4', 'LIKE', "%$search%")
                        ->orWhere('diagnostico_5', 'LIKE', "%$search%")
                        ->orWhere('diagnostico_6', 'LIKE', "%$search%")
                        ->orWhere('diagnostico_7', 'LIKE', "%$search%");
                });
            }

            foreach ($columnFilters as $colKey => $values) {
                if (!empty($values) && $colKey !== 'undefined') {
                    $query->where(function ($q) use ($colKey, $values) {
                        if (in_array('', $values) || in_array('null', $values)) {
                            $q->whereIn($colKey, array_filter((array) $values))
                                ->orWhereNull($colKey)
                                ->orWhere($colKey, '');
                        } else {
                            $q->whereIn($colKey, (array) $values);
                        }
                    });
                }
            }

            $recordsFiltered = $query->count();

            $data = $query->orderBy('fecha', 'desc')
                ->orderBy('medico', 'asc')
                ->orderByRaw('CAST(numero AS UNSIGNED) ASC')
                ->offset($offset)
                ->limit($limit)
                ->get()
                ->map(function ($registro) {
                    if ($registro->fecha) {
                        try {
                            $registro->fecha = Carbon::parse($registro->fecha)->format('d-m-Y');
                        } catch (\Throwable $e) {
                        }
                    }
                    return $registro;
                });

            return response()->json([
                'success' => true,
                'data' => $data,
                'offset' => $offset + $data->count(),
                'hasMore' => $data->count() >= $limit,
                'recordsTotal' => $recordsFiltered,
                'recordsFiltered' => $recordsFiltered,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en getInformesDataSimple Raw Prueba: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al cargar los datos: ' . $e->getMessage()], 500);
        }
    }

    public function exportInformesAt1(Request $request)
    {
        try {
            ini_set('memory_limit', '-1');
            set_time_limit(0);

            $anos = (array) $request->input('years', $request->input('anos', []));
            $meses = (array) $request->input('months', $request->input('meses', []));
            $search = $request->input('search');
            
            // Combinar filtros de la tabla y filtros personalizados
            $columnFilters = (array) $request->input('columnFilters', []);
            $customFilters = (array) $request->input('filters', []);
            $filters = array_merge($columnFilters, $customFilters);
            
            $selectedColumns = (array) $request->input('selected_columns', []);
            $filename = 'Reporte_Registros_AT1_' . date('Y-m-d_His') . '.xlsx';

            if (ob_get_length()) ob_end_clean();
            return Excel::download(new InformesAt1PruebaExport($anos, $meses, $search, $filters, $selectedColumns), $filename);
        } catch (\Throwable $e) {
            Log::error('Error en exportación Raw Prueba: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function printInformesAt1(Request $request)
    {
        try {
            $anos = (array) $request->input('years', $request->input('anos', []));
            $meses = (array) $request->input('months', $request->input('meses', []));
            $search = $request->input('search');
            $filters = (array) $request->input('filters', []);
            $selectedColumns = (array) $request->input('selected_columns', []);

            $export = new InformesAt1PruebaExport($anos, $meses, $search, $filters, $selectedColumns);
            $query = $export->query();
            $data = $query->get();
            $headings = $export->headings();
            
            // Mapear los datos para la vista
            $mappedData = $data->map(fn($item) => $export->map($item));

            return view('ata.print_informes', [
                'data' => $mappedData,
                'headings' => $headings,
                'title' => 'Reporte de Registros AT1'
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en impresión Prueba: ' . $e->getMessage());
            return "Error: " . $e->getMessage();
        }
    }


    public function updateCell(Request $request)
    {
        try {
            $id = $request->input('id');
            $field = $request->input('field');
            $value = $request->input('value');

            $registro = RegistroGlobalPrueba::find($id);
            if (!$registro) {
                return response()->json(['success' => false, 'message' => 'Registro no encontrado'], 404);
            }

            $registro->{$field} = $value;
            $registro->save();

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error('Error en UpdateCell Raw Prueba: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function store(Request $request)
    {
        try {
            $years = $request->input('years', []);
            $months = $request->input('months', []);

            $registro = new RegistroGlobalPrueba();
            if (!empty($years))
                $registro->ano = $years[0];
            if (!empty($months))
                $registro->mes = $months[0];
            $registro->diagnostico_1 = 'NUEVO REGISTRO PRUEBA';
            $registro->save();

            return response()->json([
                'success' => true,
                'id' => $registro->id,
            ]);
        } catch (\Throwable $e) {
            Log::error('Error en Store Raw Prueba: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function bulkDelete(Request $request)
    {
        try {
            $items = $request->input('items', []);
            $ids = collect($items)->pluck('id')->toArray();
            RegistroGlobalPrueba::whereIn('id', $ids)->delete();

            return response()->json(['success' => true]);
        } catch (\Throwable $e) {
            Log::error('Error en BulkDelete Raw Prueba: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function getMeses(Request $request)
    {
        $anos = $request->input('anos', []);
        $query = RegistroGlobalPrueba::query();
        if (!empty($anos))
            $query->whereIn('ano', (array) $anos);

        $meses = $query->distinct()->pluck('mes')->toArray();

        $ordenMeses = [
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
            'DICIEMBRE' => 12,
        ];

        usort($meses, fn($a, $b) => ($ordenMeses[strtoupper($a)] ?? 0) <=> ($ordenMeses[strtoupper($b)] ?? 0));

        return response()->json(['success' => true, 'meses' => $meses]);
    }

    public function getColumnValues(Request $request)
    {
        try {
            $column = $request->input('column');
            $anos = $request->input('global_anos', []);
            $meses = $request->input('global_meses', []);
            $columnFilters = $request->input('columnFilters', []);
            $search = $request->input('search');
            $query = DB::table('registros_globales');

            // Filtros globales obligatorios
            if (!empty($anos))
                $query->whereIn('ano', (array) $anos);
            if (!empty($meses))
                $query->whereIn('mes', (array) $meses);

            // Aplicar buscador global
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('medico', 'LIKE', "%$search%")
                        ->orWhere('colonia', 'LIKE', "%$search%")
                        ->orWhere('numero', 'LIKE', "%$search%")
                        ->orWhere('exp', 'LIKE', "%$search%")
                        ->orWhere('diagnostico_1', 'LIKE', "%$search%")
                        ->orWhere('diagnostico_2', 'LIKE', "%$search%");
                });
            }

            // Aplicar filtros de otras columnas (CASCADA)
            if (!empty($columnFilters)) {
                foreach ($columnFilters as $col => $values) {
                    if ($col !== $column && !empty($values)) {
                        $query->whereIn($col, (array) $values);
                    }
                }
            }

            // Manejo especial para FECHAS (Árbol Jerárquico)
            if ($column === 'fecha') {
                $dates = $query->select('fecha')->distinct()->orderBy('fecha', 'desc')->pluck('fecha');

                $treeData = [];
                $mesesNombres = [
                    1 => 'Enero', 2 => 'Febrero', 3 => 'Marzo', 4 => 'Abril', 5 => 'Mayo', 6 => 'Junio',
                    7 => 'Julio', 8 => 'Agosto', 9 => 'Septiembre', 10 => 'Octubre', 11 => 'Noviembre', 12 => 'Diciembre'
                ];

                foreach ($dates as $dbDate) {
                    if (!$dbDate) continue;
                    $carbonDate = Carbon::parse($dbDate);
                    $year = $carbonDate->year;
                    $monthNum = $carbonDate->month;
                    $monthName = $mesesNombres[$monthNum];
                    $day = $carbonDate->day;

                    if (!isset($treeData[$year])) {
                        $treeData[$year] = ['id' => $year, 'text' => $year, 'type' => 'year', 'children' => []];
                    }
                    if (!isset($treeData[$year]['children'][$monthNum])) {
                        $treeData[$year]['children'][$monthNum] = ['id' => "$year-$monthNum", 'text' => $monthName, 'type' => 'month', 'children' => []];
                    }
                    $treeData[$year]['children'][$monthNum]['children'][] = [
                        'id' => $dbDate, 'text' => $day, 'type' => 'day'
                    ];
                }

                $finalTree = [];
                foreach ($treeData as $yData) {
                    $yData['children'] = array_values($yData['children']);
                    $finalTree[] = $yData;
                }

                return response()->json(['success' => true, 'isDate' => true, 'tree' => $finalTree]);
            }

            // Manejo especial para DIAGNÓSTICOS (Búsqueda interactiva)
            if ($column === 'diagnostico_1') {
                $search = $request->input('search');
                
                $diagnosticos = collect();
                
                for ($i = 1; $i <= 7; $i++) {
                    $q = DB::table('registros_globales')
                        ->select("diagnostico_$i as diag")
                        ->distinct();
                    
                    if (!empty($anos)) $q->whereIn('ano', (array)$anos);
                    if (!empty($meses)) $q->whereIn('mes', (array)$meses);

                    // Aplicar filtros de otras columnas (CASCADA)
                    if (!empty($columnFilters)) {
                        foreach ($columnFilters as $col => $vals) {
                            if ($col !== 'diagnostico_1' && !empty($vals)) {
                                $q->whereIn($col, (array) $vals);
                            }
                        }
                    }

                    $q->whereNotNull("diagnostico_$i")->where("diagnostico_$i", '!=', '');
                    if ($search) $q->where("diagnostico_$i", 'LIKE', "%$search%");
                    
                    $diagnosticos = $diagnosticos->merge($q->pluck('diag'));
                }
                
                $results = $diagnosticos
                    ->unique()
                    ->sort()
                    ->take(500)
                    ->values();
                
                return response()->json(['success' => true, 'values' => $results]);
            }

            // Consulta optimizada para valores únicos
            $values = $query->select($column)
                ->distinct()
                ->orderBy($column)
                ->limit(5000)
                ->pluck($column)
                ->map(function ($val) {
                    return $val === null ? '' : (string)$val;
                });

            $hasEmpty = $values->contains('');

            return response()->json([
                'success' => true,
                'values' => $values->values()->toArray(),
                'hasEmpty' => $hasEmpty
            ]);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }
}

