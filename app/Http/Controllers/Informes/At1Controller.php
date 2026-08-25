<?php

namespace App\Http\Controllers\Informes;

use App\Http\Controllers\Controller;
use App\Models\RegistroGlobal;
use App\Models\Informe;
use App\Traits\InformesHelperTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Exports\InformesAt1Export;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use App\Services\ColumnValidationService;

class At1Controller extends Controller
{
    use InformesHelperTrait;

    public function __construct(private \App\Services\RegistroGlobalService $service)
    {
    }

    /**
     * Vista principal del módulo Informes (index)
     */
    public function index(Request $request)
    {
        \Illuminate\Support\Facades\Cache::forget('registros.years');

        $currentYear = (int)date('Y');
        $dbAnos = $this->service->getAnosDisponibles()->toArray();
        $rangeAnos = range($currentYear - 4, $currentYear + 5);
        $anos = collect(array_unique(array_merge($dbAnos, $rangeAnos)))->sortDesc()->values();

        if ($anos->isEmpty()) {
            $anos = collect([$currentYear]);
            $anoDefault = $currentYear;
        }
        else {
            $latestWithData = Informe::where('ano', '<=', $currentYear)->orderBy('ano', 'desc')->first();
            $anoDefault = $latestWithData
                ? $latestWithData->ano
                : ($anos->contains($currentYear) ? $currentYear : $anos->first());
        }

        $mesMap = [
            1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL',
            5 => 'MAYO', 6 => 'JUNIO', 7 => 'JULIO', 8 => 'AGOSTO',
            9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE'
        ];

        $meses = Informe::where('ano', $anoDefault)->distinct()->pluck('mes')->toArray();
        $ordenMeses = array_flip($mesMap);
        usort($meses, fn($a, $b) => ($ordenMeses[strtoupper($a)] ?? 0) <=> ($ordenMeses[strtoupper($b)] ?? 0));
        $mesDefault = !empty($meses) ? end($meses) : $mesMap[(int)date('n')];
        reset($meses);

        $columns = [
            'ano', 'mes', 'numero', 'cm', 'medico', 'prof', 'fecha', 'se', 'exp', 'sexo', 'edad', 'tipo',
            'rango', 'rango_5', 'cond', 'cod_col', 'colonia',
            'cod', 'diagnostico', 'cond_diagnostico', 'sg',
            'referido_a', 'referido_de', 'pg_emb', 'jornada'
        ];

        return $this->informesAt1($request);
    }

    /**
     * Vista Informes AT1 (hoja estilo Excel)
     */
    public function informesAt1(Request $request)
    {
        $data = $this->getAnosMesesDisponiblesInformes();

        $columns = [
            'id', 'registro_id', 'numero', 'medico', 'cm', 'ano', 'mes', 'prof', 'fecha', 'se', 'exp', 'sexo', 'edad', 'tipo',
            'rango', 'rango_5', 'cond', 'colonia',
            'cod', 'diagnostico', 'cond_diagnostico', 'diag_index', 'sg',
            'referido_a', 'referido_de', 'pg_emb', 'jornada', 'sm'
        ];

        return view('ata.informes_at1', array_merge($data, compact('columns')));
    }

    /**
     * Obtener datos simples de la tabla informes para Jspreadsheet
     */
    public function getInformesDataSimple(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        try {
            $limit = $request->input('length', 10000);
            $anos = (array)$request->input('anos', $request->input('years', []));
            $mesesInput = (array)$request->input('meses', $request->input('months', []));
            $meses = array_map('strtoupper', array_filter($mesesInput));

            $query = Informe::query();
            if (!empty($anos))
                $query->whereIn('ano', $anos);
            if (!empty($meses))
                $query->whereIn('mes', $meses);

            $columnFilters = $request->input('columnFilters', []);
            foreach ($columnFilters as $colKey => $values) {
                if (!empty($values) && $colKey !== 'undefined') {
                    $cleanValues = array_map(function($v) {
                        if (!is_string($v)) return $v;
                        return mb_check_encoding($v, 'UTF-8') ? $v : mb_convert_encoding($v, 'UTF-8', 'ISO-8859-1');
                    }, (array)$values);
                    $query->whereIn($colKey, $cleanValues);
                }
            }

            $search = $request->input('search');
            if ($search) {
                $query->where(function ($q) use ($search) {
                    $q->where('diagnostico', 'LIKE', "%$search%")
                        ->orWhere('medico', 'LIKE', "%$search%")
                        ->orWhere('colonia', 'LIKE', "%$search%")
                        ->orWhere('numero', 'LIKE', "%$search%")
                        ->orWhere('exp', 'LIKE', "%$search%");
                });
            }

            $recordsTotal = Informe::count();
            $recordsFiltered = $query->count();
            $offset = $request->input('offset', 0);
            $limit = $request->input('limit', 100);

            if ($recordsFiltered == 0 && empty($search) && empty($columnFilters)) {
                $hasSourceData = RegistroGlobal::query()
                    ->when(!empty($anos), fn($q) => $q->whereIn('ano', (array)$anos))
                    ->when(!empty($meses), fn($q) => $q->whereIn('mes', (array)$meses))
                    ->exists();

                if ($hasSourceData) {
                    $this->syncSelective($anos, $meses);
                    $recordsFiltered = $query->count();
                }
            }

            $data = $query->orderBy('fecha', 'desc')
                ->orderBy('medico', 'asc')
                ->orderBy('numero', 'asc')
                ->offset($offset)
                ->limit($limit)
                ->get()
                ->map(function ($informe) {
                    if ($informe->fecha) {
                        try {
                            $informe->fecha = Carbon::parse($informe->fecha)->format('d-m-Y');
                        } catch (\Throwable $e) {}
                    }
                    return $informe;
                });

            if (ob_get_length()) ob_clean();
            return response()->json([
                'success' => true,
                'data' => $data,
                'offset' => $offset + $data->count(),
                'hasMore' => $data->count() >= $limit,
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
            ]);
        }
        catch (\Throwable $e) {
            Log::error('Error en getInformesDataSimple: ' . $e->getMessage());
            if (ob_get_length()) ob_clean();
            return response()->json(['success' => false, 'message' => 'Error al cargar los datos: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Exportar AT1
     */
    public function exportInformesAt1(Request $request)
    {
        try {
            ini_set('memory_limit', '-1');
            set_time_limit(0);

            $anos = (array)$request->input('anos', []);
            $meses = (array)$request->input('meses', []);
            $filters = (array)$request->input('filters', []);
            $selectedColumns = (array)$request->input('selected_columns', []);
            $filename = 'Reporte_Informes_AT1_' . date('Y-m-d_His') . '.xlsx';

            if (ob_get_length()) ob_end_clean();
            return Excel::download(new InformesAt1Export($anos, $meses, $filters, $selectedColumns), $filename);
        }
        catch (\Throwable $e) {
            Log::error('Error en exportación Informes AT1: ' . $e->getMessage());
            throw $e;
        }
    }

    public function printInformesAt1(Request $request)
    {
        try {
            $anos = (array)$request->input('years', $request->input('anos', []));
            $meses = (array)$request->input('months', $request->input('meses', []));
            $filters = (array)$request->input('filters', []);
            $selectedColumns = (array)$request->input('selected_columns', []);

            $export = new InformesAt1Export($anos, $meses, $filters, $selectedColumns);
            $query = $export->query();
            $data = $query->get();
            $headings = $export->headings();
            
            $mappedData = $data->map(fn($item) => $export->map($item));

            return view('ata.print_informes', [
                'data' => $mappedData,
                'headings' => $headings,
                'title' => 'Reporte de Informes AT1'
            ]);
        }
        catch (\Throwable $e) {
            Log::error('Error en impresión Informes AT1: ' . $e->getMessage());
            return "Error: " . $e->getMessage();
        }
    }


    /**
     * Sincronización selectiva para periodos específicos (usada por Lazy Sync)
     */
    private function syncSelective($anos, $meses)
    {
        try {
            $anos = (array)$anos;
            $meses = (array)$meses;

            $delQuery = Informe::query();
            if (!empty($anos)) $delQuery->whereIn('ano', $anos);
            if (!empty($meses)) $delQuery->whereIn('mes', $meses);
            $delQuery->delete();

            $columns = "id, registro_id, diag_index, fecha, ano, mes, numero, cm, medico, prof, se, exp, sexo, edad, tipo, rango, rango_2, rango_3, rango_4, rango_5, cond, cod_col, colonia, referido_a, referido_de, pg_emb, jornada, sm, sg2, sg, cod, diagnostico, cond_diagnostico, created_at, updated_at";

            $dateExpr = "
                CASE 
                    WHEN fecha REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$' THEN STR_TO_DATE(fecha, '%Y-%m-%d')
                    WHEN fecha REGEXP '^[0-9]{1,2}/[0-9]{1,2}/[0-9]{4}$' THEN 
                        CASE 
                            WHEN CAST(SUBSTRING_INDEX(fecha, '/', 1) AS UNSIGNED) > 12 THEN STR_TO_DATE(fecha, '%d/%m/%Y')
                            WHEN CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(fecha, '/', 2), '/', -1) AS UNSIGNED) > 12 THEN STR_TO_DATE(fecha, '%m/%d/%Y')
                            ELSE STR_TO_DATE(fecha, '%d/%m/%Y')
                        END
                    ELSE NULL
                END";

            $sourceExpr = "$dateExpr, CAST(NULLIF(ano, '') AS SIGNED), mes, CAST(NULLIF(numero, '') AS SIGNED), cm, medico, prof, CAST(NULLIF(se, '') AS SIGNED), exp, sexo, CAST(NULLIF(edad, '') AS SIGNED), tipo, rango, rango_2, rango_3, rango_4, rango_5, cond, cod_col, colonia, referido_a, referido_de, pg_emb, jornada, sm, sg2, sg";

            DB::beginTransaction();
            for ($i = 1; $i <= 7; $i++) {
                $whereClause = "((cod_$i <> '' AND cod_$i IS NOT NULL AND cod_$i <> '0') OR (diagnostico_$i <> '' AND diagnostico_$i IS NOT NULL AND diagnostico_$i <> '0'))";
                
                if (!empty($anos)) {
                    $anoValues = array_map('intval', (array)$anos);
                    $whereClause .= " AND ano IN (" . implode(',', $anoValues) . ")";
                }
                if (!empty($meses)) {
                    $mesValues = array_map(fn($m) => DB::getPdo()->quote($m), (array)$meses);
                    $whereClause .= " AND mes IN (" . implode(',', $mesValues) . ")";
                }

                DB::statement("
                    INSERT INTO informes ($columns)
                    SELECT CONCAT(id, '_', $i), id, $i, $sourceExpr, cod_$i, diagnostico_$i, cond_$i, NOW(), NOW()
                    FROM registros_globales
                    WHERE $whereClause
                ");
            }
            DB::commit();
        } catch (\Throwable $e) {
            if (DB::transactionLevel() > 0) DB::rollBack();
            Log::error('Error en syncSelective Informes: ' . $e->getMessage());
        }
    }

    /**
     * Sincronización manual de informes desde registros_globales
     */
    public function syncManual(Request $request)
    {
        try {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            Informe::truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            $columns = "id, registro_id, diag_index, fecha, ano, mes, numero, cm, medico, prof, se, exp, sexo, edad, tipo, rango, rango_2, rango_3, rango_4, rango_5, cond, cod_col, colonia, referido_a, referido_de, pg_emb, jornada, sm, sg2, sg, cod, diagnostico, cond_diagnostico, created_at, updated_at";

            $dateExpr = "
                CASE 
                    WHEN fecha REGEXP '^[0-9]{4}-[0-9]{2}-[0-9]{2}$' THEN STR_TO_DATE(fecha, '%Y-%m-%d')
                    WHEN fecha REGEXP '^[0-9]{1,2}/[0-9]{1,2}/[0-9]{4}$' THEN 
                        CASE 
                            WHEN CAST(SUBSTRING_INDEX(fecha, '/', 1) AS UNSIGNED) > 12 THEN STR_TO_DATE(fecha, '%d/%m/%Y')
                            WHEN CAST(SUBSTRING_INDEX(SUBSTRING_INDEX(fecha, '/', 2), '/', -1) AS UNSIGNED) > 12 THEN STR_TO_DATE(fecha, '%m/%d/%Y')
                            ELSE STR_TO_DATE(fecha, '%d/%m/%Y')
                        END
                    ELSE NULL
                END";

            $sourceExpr = "
                $dateExpr,
                CAST(NULLIF(ano, '') AS SIGNED), 
                mes, 
                CAST(NULLIF(numero, '') AS SIGNED), 
                cm, 
                medico, 
                prof, 
                CAST(NULLIF(se, '') AS SIGNED), 
                exp, 
                sexo, 
                CAST(NULLIF(edad, '') AS SIGNED), 
                tipo, 
                rango, rango_2, rango_3, rango_4, rango_5, cond, cod_col, colonia, referido_a, referido_de, pg_emb, jornada, sm, sg2, sg";

            DB::beginTransaction();
            for ($i = 1; $i <= 7; $i++) {
                DB::statement("
                    INSERT INTO informes ($columns)
                    SELECT CONCAT(id, '_', $i), id, $i, $sourceExpr, cod_$i, diagnostico_$i, cond_$i, NOW(), NOW()
                    FROM registros_globales
                    WHERE (cod_$i <> '' AND cod_$i IS NOT NULL AND cod_$i <> '0')
                       OR (diagnostico_$i <> '' AND diagnostico_$i IS NOT NULL AND diagnostico_$i <> '0')
                ");
            }
            DB::commit();

            return response()->json(['success' => true, 'message' => 'Sincronización manual completada con éxito.']);
        }
        catch (\Throwable $e) {
            if (DB::transactionLevel() > 0)
                DB::rollBack();
            Log::error('Error en syncManual Informes: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al sincronizar: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Actualiza una celda individual (Real-time sync)
     */
    public function updateCell(Request $request)
    {
        try {
            $id = $request->input('id');
            $field = $request->input('field');
            $value = $request->input('value');

            // Validar que el campo está en la lista blanca de campos permitidos
            $allowedFields = ['cod', 'diagnostico', 'cond_diagnostico'];
            if (!in_array($field, $allowedFields, true)) {
                return response()->json(['success' => false, 'message' => 'Campo no permitido'], 400);
            }

            $informe = Informe::find($id);
            if (!$informe) {
                $regId = $request->input('registro_id');
                $idx = $request->input('diag_index', 1);
            }
            else {
                $regId = $informe->registro_id;
                $idx = $informe->diag_index;
            }

            $registro = RegistroGlobal::find($regId);
            if (!$registro) {
                return response()->json(['success' => false, 'message' => 'Registro ATA no encontrado'], 404);
            }

            // Construir el nombre de campo seguro
            $ataField = match($field) {
                'cod' => "cod_$idx",
                'diagnostico' => "diagnostico_$idx",
                'cond_diagnostico' => "cond_$idx",
                default => null
            };

            if (!$ataField) {
                return response()->json(['success' => false, 'message' => 'Campo inválido'], 400);
            }

            $registro->{ $ataField} = $value;
            $registro->save();

            return response()->json(['success' => true]);
        }
        catch (\Throwable $e) {
            Log::error('Error en Informes updateCell: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Crea un nuevo registro base
     */
    public function store(Request $request)
    {
        try {
            $years = $request->input('years', []);
            $months = $request->input('months', []);

            $registro = new RegistroGlobal();
            if (!empty($years))
                $registro->ano = $years[0];
            if (!empty($months))
                $registro->mes = $months[0];
            $registro->diagnostico_1 = 'NUEVO REGISTRO';
            $registro->save();

            return response()->json([
                'success' => true,
                'id' => $registro->id . '_1',
                'registro_id' => $registro->id,
                'diag_index' => 1,
            ]);
        }
        catch (\Throwable $e) {
            Log::error('Error en Informes store: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    /**
     * Eliminar diagnósticos en bloque
     */
    public function bulkDelete(Request $request)
    {
        try {
            $items = $request->input('items', []);
            foreach ($items as $item) {
                $registro = RegistroGlobal::find($item['registro_id']);
                if ($registro) {
                    $idx = $item['diag_index'];
                    $registro->{ "cod_$idx"} = null;
                    $registro->{ "diagnostico_$idx"} = null;
                    $registro->{ "cond_$idx"} = null;
                    $registro->save();
                }
            }
            return response()->json(['success' => true]);
        }
        catch (\Throwable $e) {
            Log::error('Error en Informes bulkDelete: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
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

            $query = DB::table('informes');
            if (!empty($anos))
                $query->whereIn('ano', (array)$anos);
            if (!empty($meses))
                $query->whereIn('mes', (array)$meses);

            $requestColumns = $request->input('columns', []);
            foreach ($requestColumns as $column) {
                $colSearch = $column['search']['value'] ?? null;
                $dataName = $column['data'] ?? null;
                if ($colSearch && $dataName) {
                    // Validar que la columna está en la lista blanca
                    if (!ColumnValidationService::isValidColumn($dataName)) {
                        continue;
                    }

                    $isRegex = str_starts_with($colSearch, '^(') && str_ends_with($colSearch, ')$');
                    $isExact = !$isRegex && str_starts_with($colSearch, '^') && str_ends_with($colSearch, '$');
                    $value = $isRegex ? substr($colSearch, 2, -2) : ($isExact ? substr($colSearch, 1, -1) : $colSearch);

                    if ($isRegex)
                        $query->whereRaw("$dataName REGEXP ?", [$value]);
                    elseif ($isExact)
                        $query->where($dataName, $value);
                    else
                        $query->where($dataName, 'LIKE', "%$value%");
                }
            }

            $recordsTotal = DB::table('informes')->count();
            $recordsFiltered = (clone $query)->count();

            $registros = $query->orderBy('fecha', 'desc')->orderBy('medico', 'asc')->orderBy('numero', 'asc')->offset($start)->limit($length)->get();

            return response()->json([
                'draw' => intval($draw),
                'recordsTotal' => $recordsTotal,
                'recordsFiltered' => $recordsFiltered,
                'data' => $registros,
            ]);
        }
        catch (\Throwable $e) {
            Log::error('InformesController Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getStats(Request $request)
    {
        ini_set('memory_limit', '512M');
        set_time_limit(120);

        try {
            $anos = $request->input('anos', []);
            $meses = $request->input('meses', []);
            $query = DB::table('informes');
            if (!empty($anos))
                $query->whereIn('ano', (array)$anos);
            if (!empty($meses))
                $query->whereIn('mes', (array)$meses);

            $statsResults = (clone $query)->selectRaw("
                COUNT(*) as total_records,
                COUNT(DISTINCT medico) as distinct_medicos,
                SUM(CASE WHEN cond_diagnostico = 'N' THEN 1 ELSE 0 END) as diag_n,
                SUM(CASE WHEN cond_diagnostico = 'S' THEN 1 ELSE 0 END) as diag_s,
                SUM(CASE WHEN (diagnostico LIKE 'DENGUE SIN SIGNOS%') THEN 1 ELSE 0 END) as dengue_ss,
                SUM(CASE WHEN (diagnostico LIKE 'DENGUE CON SIGNOS%') THEN 1 ELSE 0 END) as dengue_cs,
                SUM(CASE WHEN (diagnostico LIKE 'DENGUE GRAVE%') THEN 1 ELSE 0 END) as dengue_grave,
                SUM(CASE WHEN (diagnostico LIKE '%DIARREA%' OR cod LIKE '%DIA%') THEN 1 ELSE 0 END) as diarrea,
                SUM(CASE WHEN (diagnostico LIKE '%NEUMONIA%' OR cod LIKE '%NEU%') THEN 1 ELSE 0 END) as neumonia,
                SUM(CASE WHEN (diagnostico LIKE '%HIPERTENSION%' OR cod LIKE '%HTA%') THEN 1 ELSE 0 END) as hipertension,
                SUM(CASE WHEN (diagnostico LIKE '%DIABETES%' OR cod LIKE '%DM%') THEN 1 ELSE 0 END) as diabetes
            ")->first();

            return response()->json([
                'total' => (int)($statsResults->total_records ?? 0),
                'medicos_count' => (int)($statsResults->distinct_medicos ?? 0),
                'diag_n' => (int)($statsResults->diag_n ?? 0),
                'diag_s' => (int)($statsResults->diag_s ?? 0),
                'disease_stats' => [
                    'diarrea' => (int)($statsResults->diarrea ?? 0),
                    'neumonia' => (int)($statsResults->neumonia ?? 0),
                    'hipertension' => (int)($statsResults->hipertension ?? 0),
                    'diabetes' => (int)($statsResults->diabetes ?? 0),
                    'dengue_ss' => (int)($statsResults->dengue_ss ?? 0),
                    'dengue_cs' => (int)($statsResults->dengue_cs ?? 0),
                    'dengue_grave' => (int)($statsResults->dengue_grave ?? 0),
                ],
            ]);
        }
        catch (\Throwable $e) {
            Log::error('InformesController getStats Error: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getMeses(Request $request)
    {
        $anos = $request->input('anos', []);
        $query = Informe::query();
        if (!empty($anos))
            $query->whereIn('ano', (array)$anos);

        $meses = $query->distinct()->pluck('mes')->toArray();

        $ordenMeses = [
            'ENERO' => 1, 'FEBRERO' => 2, 'MARZO' => 3, 'ABRIL' => 4,
            'MAYO' => 5, 'JUNIO' => 6, 'JULIO' => 7, 'AGOSTO' => 8,
            'SEPTIEMBRE' => 9, 'OCTUBRE' => 10, 'NOVIEMBRE' => 11, 'DICIEMBRE' => 12,
        ];

        usort($meses, fn($a, $b) => ($ordenMeses[strtoupper($a)] ?? 0) <=> ($ordenMeses[strtoupper($b)] ?? 0));

        return response()->json(['success' => true, 'meses' => $meses]);
    }

    public function getColumnValues(Request $request)
    {
        $column = $request->input('column');
        $ano = $request->input('ano');
        $mes = $request->input('mes');
        $filterSearch = trim($request->input('filterSearch', ''));

        $query = Informe::query();

        if (!empty($request->input('global_anos')))
            $query->whereIn('ano', (array)$request->input('global_anos'));
        elseif (!empty($ano)) {
            str_contains($ano, ',') ? $query->whereIn('ano', explode(',', $ano)) : $query->where('ano', $ano);
        }

        if (!empty($request->input('global_meses')))
            $query->whereIn('mes', (array)$request->input('global_meses'));
        elseif (!empty($mes) && $mes !== 'TODOS') {
            str_contains($mes, ',') ? $query->whereIn('mes', explode(',', $mes)) : $query->where('mes', $mes);
        }

        // Aplicar filtros en cascada (otros filtros activos)
        $columnFilters = $request->input('columnFilters', []);
        foreach ($columnFilters as $colKey => $values) {
            if (!empty($values) && $colKey !== $column && $colKey !== 'undefined') {
                $query->whereIn($colKey, (array)$values);
            }
        }

        if ($column === 'fecha') {
            $rawResults = $query->select('fecha')->distinct()->get();
            $tree = [];
            $hasEmptyDate = false;
            $searchLower = mb_strtolower($filterSearch);
            $monthMap = [
                1 => 'enero', 2 => 'febrero', 3 => 'marzo', 4 => 'abril',
                5 => 'mayo', 6 => 'junio', 7 => 'julio', 8 => 'agosto',
                9 => 'septiembre', 10 => 'octubre', 11 => 'noviembre', 12 => 'diciembre'
            ];

            foreach ($rawResults as $d) {
                try {
                    $fechaStr = trim($d->fecha ?? '');
                    if (empty($fechaStr)) {
                        if ($searchLower === '' || str_contains('(vacío)', $searchLower) || str_contains('vacio', $searchLower)) {
                            $hasEmptyDate = true;
                        }
                        continue;
                    }

                    $date = null;
                    if (is_string($fechaStr) && strpos($fechaStr, '/') !== false) {
                        try {
                            $date = Carbon::createFromFormat('d/m/Y', $fechaStr);
                        } catch (\Exception $ex1) {
                            try { $date = Carbon::createFromFormat('j/n/Y', $fechaStr); } catch (\Exception $ex2) { }
                        }
                    } 
                    
                    if (!$date) {
                        $date = Carbon::parse($fechaStr);
                    }

                    $y = $date->year;
                    $mId = $date->month;
                    $mText = $monthMap[$mId] ?? strval($mId);
                    $dayStr = $date->format('d');
                    $full = $date->toDateString();
                    $formattedDMY = $date->format('d/m/Y');

                    if ($searchLower !== '') {
                        $matches = (strpos(mb_strtolower($fechaStr), $searchLower) !== false)
                            || (strpos(mb_strtolower($formattedDMY), $searchLower) !== false)
                            || (strpos(mb_strtolower($full), $searchLower) !== false)
                            || (strpos(mb_strtolower((string)$y), $searchLower) !== false)
                            || (strpos(mb_strtolower($mText), $searchLower) !== false)
                            || (strpos(mb_strtolower((string)$mId), $searchLower) !== false)
                            || (strpos(mb_strtolower($dayStr), $searchLower) !== false);
                        if (!$matches) {
                            continue;
                        }
                    }

                    $nodeId = $fechaStr;
                    $altIds = array_unique([$fechaStr, $full, $formattedDMY, $date->format('d-m-Y')]);

                    if (!isset($tree[$y])) {
                        $tree[$y] = ['id' => $y, 'text' => $y, 'type' => 'year', 'children' => []];
                    }
                    if (!isset($tree[$y]['children'][$mId])) {
                        $tree[$y]['children'][$mId] = ['id' => "$y-$mId", 'text' => $mText, 'type' => 'month', 'days' => []];
                    }
                    
                    if (!isset($tree[$y]['children'][$mId]['days'][$nodeId])) {
                        $tree[$y]['children'][$mId]['days'][$nodeId] = [
                            'id' => $nodeId, 
                            'altIds' => array_values($altIds),
                            'text' => $dayStr, 
                            'type' => 'day', 
                            'dayNum' => (int)$dayStr
                        ];
                    } else {
                        $existingAlts = $tree[$y]['children'][$mId]['days'][$nodeId]['altIds'];
                        $tree[$y]['children'][$mId]['days'][$nodeId]['altIds'] = array_values(array_unique(array_merge($existingAlts, $altIds)));
                    }
                } catch (\Throwable $e) {
                    continue;
                }
            }

            foreach ($tree as $year => &$yData) {
                foreach ($yData['children'] as $mId => &$mData) {
                    $daysList = array_values($mData['days']);
                    usort($daysList, function($a, $b) { return $a['dayNum'] - $b['dayNum']; });
                    $mData['children'] = $daysList;
                    unset($mData['days']);
                }
                ksort($yData['children']);
                $yData['children'] = array_values($yData['children']);
            }
            unset($yData);
            ksort($tree);
            $finalTree = array_values($tree);
            
            if (ob_get_length()) ob_clean();
            return response()->json([
                'success' => true, 
                'isDate' => true, 
                'tree' => array_reverse($finalTree),
                'hasEmpty' => $hasEmptyDate
            ]);
        }
        
        if ($column === 'diagnostico') {
            $query->select('diagnostico')->distinct();
            if ($filterSearch !== '') {
                $query->where('diagnostico', 'LIKE', "%{$filterSearch}%");
            }
            $values = $query->pluck('diagnostico')->sort()->values();
            return response()->json(['success' => true, 'isDate' => false, 'values' => $values->toArray()]);
        }

        // Usamos COALESCE para tratar NULL y '' como lo mismo
        if ($filterSearch !== '') {
            $query->where($column, 'LIKE', "%{$filterSearch}%");
        }
        $values = $query->select(DB::raw("DISTINCT COALESCE($column, '') as $column"))->pluck($column);

        if ($column === 'mes') {
            $ordenMeses = [
                'ENERO' => 1, 'FEBRERO' => 2, 'MARZO' => 3, 'ABRIL' => 4,
                'MAYO' => 5, 'JUNIO' => 6, 'JULIO' => 7, 'AGOSTO' => 8,
                'SEPTIEMBRE' => 9, 'OCTUBRE' => 10, 'NOVIEMBRE' => 11, 'DICIEMBRE' => 12,
            ];
            $values = $values->sort(fn($a, $b) => ($ordenMeses[strtoupper($a)] ?? 0) <=> ($ordenMeses[strtoupper($b)] ?? 0));
        }
        else {
            $values = $values->sort();
        }

        if (ob_get_length()) ob_clean();
        return response()->json(['success' => true, 'isDate' => false, 'values' => $values->values()->toArray()]);
    }

    public function getSuggestions(Request $request)
    {
        $term = trim($request->input('term', ''));
        if (empty($term))
            return response()->json([]);

        $results = Informe::where(function ($q) use ($term) {
            $q->where('diagnostico', 'LIKE', "%$term%")
                ->orWhere('cod', 'LIKE', "%$term%");
        })->distinct()->limit(30)->pluck('diagnostico');

        return response()->json($results->filter()->unique()->sort()->values());
    }

    public function bulkDestroy(Request $request)
    {
        $items = $request->input('items', []);
        if (empty($items) || !is_array($items)) {
            return response()->json(['success' => false, 'message' => 'No se seleccionaron registros.'], 422);
        }

        DB::beginTransaction();
        try {
            foreach ($items as $item) {
                if (!isset($item['id'], $item['registro_id'], $item['diag_index']))
                    continue;
                $idx = $item['diag_index'];
                if ($idx >= 1 && $idx <= 7) {
                    $registro = RegistroGlobal::find($item['registro_id']);
                    if ($registro) {
                        $registro->update([
                            "diagnostico_$idx" => "",
                            "cod_$idx" => "",
                            "cond_$idx" => "",
                        ]);
                    }
                }
            }
            DB::commit();
            return response()->json(['success' => true]);
        }
        catch (\Throwable $e) {
            DB::rollBack();
            Log::error('Informes Bulk Delete Error: ' . $e->getMessage());
            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    public function deleteDiagnostico(Request $request)
    {
        return response()->json(['success' => true]);
    }

    public function update(Request $request, $id)
    {
        try {
            $informe = Informe::find($id);
            if (!$informe)
                return response()->json(['success' => false, 'message' => 'Informe no encontrado'], 404);

            $registro = RegistroGlobal::find($informe->registro_id);
            if (!$registro)
                return response()->json(['success' => false, 'message' => 'Registro padre ATA no encontrado'], 404);

            $idx = $informe->diag_index;
            $data = $request->except(['_token', '_method', 'id', 'registro_id', 'diag_index']);
            $updateData = [];

            foreach ($data as $key => $value) {
                if ($key === 'diagnostico')
                    $updateData["diagnostico_$idx"] = $value;
                elseif ($key === 'cod')
                    $updateData["cod_$idx"] = $value;
                elseif ($key === 'cond_diagnostico')
                    $updateData["cond_$idx"] = $value;
                elseif ($key === 'cond')
                    $updateData['cond'] = $value;
                else
                    $updateData[$key] = $value;
            }

            if (!empty($updateData))
                $registro->update($updateData);

            $informeActualizado = Informe::find($id);
            return response()->json(['success' => true, 'message' => 'Actualizado correctamente', 'data' => $informeActualizado]);
        }
        catch (\Throwable $e) {
            Log::error("Error updating informe $id: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al actualizar: ' . $e->getMessage()], 500);
        }
    }

    public function edit($id)
    {
        return redirect()->route('registros.show', $id);
    }
}
