<?php

namespace App\Services;

use App\Models\Colonia;
use App\Models\Diagnostico;
use App\Models\Importacion;
use App\Models\ImportacionRegistro;
use App\Models\Medico;
use App\Models\Paciente;
use App\Models\RegistroGlobal;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Shared\Date as ExcelDate;
use Throwable;

class ExcelImportService
{
    /**
     * Catálogos en memoria para búsquedas ultrarrápidas
     */
    protected array $coloniasCatalog = [];
    protected array $diagnosticosCatalog = [];
    protected array $medicosCatalog = [];
    protected array $diagnosticosAliasCatalog = [];
    protected array $coloniasAliasCatalog = [];

    public function __construct()
    {
        // Los catálogos se cargan bajo demanda para optimizar memoria
    }

    /**
     * Normalizar clave de texto para búsqueda de alias y equivalencias aprendidas
     */
    public static function normalizarTextoClave(?string $texto): string
    {
        if (empty($texto)) return '';
        $str = mb_strtoupper(trim($texto), 'UTF-8');
        $str = strtr($str, [
            'Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ú'=>'U','Ñ'=>'N','Ü'=>'U'
        ]);
        $str = preg_replace('/[^A-Z0-9]/', ' ', $str);
        return trim(preg_replace('/\s+/', ' ', $str));
    }

    /**
     * Cargar catálogos maestros y memoria de equivalencias
     */
    protected function loadCatalogs(): void
    {
        if (empty($this->coloniasCatalog)) {
            $this->coloniasCatalog = Colonia::select('id', 'COD_COL', 'COLONIA')->get()->map(function ($c) {
                return [
                    'id' => $c->id,
                    'cod_col' => $c->COD_COL,
                    'nombre' => trim(mb_strtoupper($c->COLONIA, 'UTF-8')),
                ];
            })->toArray();
        }

        if (empty($this->diagnosticosCatalog)) {
            $this->diagnosticosCatalog = Diagnostico::select('id', 'codigo', 'patologia', 'auxiliar')->get()->map(function ($d) {
                return [
                    'id' => $d->id,
                    'codigo' => trim(mb_strtoupper($d->codigo, 'UTF-8')),
                    'patologia' => trim(mb_strtoupper($d->patologia, 'UTF-8')),
                    'auxiliar' => trim(mb_strtoupper($d->auxiliar ?? '', 'UTF-8')),
                ];
            })->toArray();
        }

        if (empty($this->medicosCatalog)) {
            $this->medicosCatalog = Medico::select('id', 'COD_MED', 'NOM_MED', 'ESPECIALIDAD', 'JORNADA')->get()->map(function ($m) {
                return [
                    'id' => $m->id,
                    'cod_med' => $m->COD_MED,
                    'nom_med' => trim(mb_strtoupper($m->NOM_MED, 'UTF-8')),
                    'especialidad' => $m->ESPECIALIDAD,
                    'jornada' => $m->JORNADA,
                ];
            })->toArray();
        }

        if (empty($this->diagnosticosAliasCatalog)) {
            $this->diagnosticosAliasCatalog = DB::table('importacion_diagnosticos_alias')
                ->get()
                ->keyBy('texto_normalizado')
                ->toArray();
        }

        if (empty($this->coloniasAliasCatalog)) {
            $this->coloniasAliasCatalog = DB::table('importacion_colonias_alias')
                ->get()
                ->keyBy('texto_normalizado')
                ->toArray();
        }
    }

    /**
     * Paso 1: Analizar archivo Excel y extraer metadatos, fechas y médicos
     */
    public function analizarArchivo(string $filePath, string $originalName, ?int $userId = null): array
    {
        $hashArchivo = hash_file('sha256', $filePath);
        $tamano = filesize($filePath);

        // Verificar si este archivo exacto ya fue subido antes
        $importacionPrevia = Importacion::where('hash_archivo', $hashArchivo)->first();

        // Cargar hoja con PhpSpreadsheet
        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getActiveSheet();

        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();

        [$columnMap, $dataStartRow] = $this->detectarEstructuraEncabezados($sheet, $highestColumn, $highestRow);

        $fechasDisponibles = [];
        $medicosDisponibles = [];
        $matrizFechasMedicos = [];
        $totalFilasValidas = 0;

        // Leer filas y extraer fechas y médicos desde dataStartRow
        for ($row = $dataStartRow; $row <= $highestRow; $row++) {
            $rowData = $sheet->rangeToArray("A{$row}:{$highestColumn}{$row}", null, true, false)[0];
            
            // Verificar si la fila está vacía
            if (empty(array_filter($rowData, fn($v) => $v !== null && trim((string)$v) !== ''))) {
                continue;
            }

            $totalFilasValidas++;

            // Extraer fecha
            $fechaRaw = $this->getValueByMap($rowData, $columnMap, 'fecha');
            $fecha = $this->normalizarFecha($fechaRaw);
            $fechaIso = null;
            if ($fecha) {
                $fechaStr = $fecha->format('d/m/Y');
                $fechaIso = $fecha->format('Y-m-d');
                if (!isset($fechasDisponibles[$fechaIso])) {
                    $fechasDisponibles[$fechaIso] = [
                        'fecha_iso' => $fechaIso,
                        'fecha_formato' => $fechaStr,
                        'total' => 0,
                    ];
                }
                $fechasDisponibles[$fechaIso]['total']++;
            }

            // Extraer médico
            $medicoRaw = $this->getValueByMap($rowData, $columnMap, 'medico');
            $medicoNorm = trim(mb_strtoupper((string)$medicoRaw, 'UTF-8'));
            if ($medicoNorm) {
                if (!isset($medicosDisponibles[$medicoNorm])) {
                    $medicosDisponibles[$medicoNorm] = [
                        'medico' => $medicoNorm,
                        'total' => 0,
                    ];
                }
                $medicosDisponibles[$medicoNorm]['total']++;
            }

            // Mapeo conjunto Fecha -> Médico
            if ($fechaIso && $medicoNorm) {
                if (!isset($matrizFechasMedicos[$fechaIso])) {
                    $matrizFechasMedicos[$fechaIso] = [];
                }
                $matrizFechasMedicos[$fechaIso][$medicoNorm] = ($matrizFechasMedicos[$fechaIso][$medicoNorm] ?? 0) + 1;
            }
        }

        // Ordenar fechas cronológicamente
        ksort($fechasDisponibles);
        // Ordenar médicos alfabéticamente
        ksort($medicosDisponibles);

        // Guardar registro de importación
        $importacion = Importacion::create([
            'nombre_archivo' => $originalName,
            'hash_archivo' => $hashArchivo,
            'tamano_archivo' => $tamano,
            'total_filas' => $totalFilasValidas,
            'fechas_disponibles' => array_values($fechasDisponibles),
            'medicos_disponibles' => array_values($medicosDisponibles),
            'usuario_id' => $userId,
            'estado' => 'analizado',
        ]);

        return [
            'success' => true,
            'importacion_id' => $importacion->id,
            'nombre_archivo' => $originalName,
            'tamano_kb' => round($tamano / 1024, 2),
            'total_filas' => $totalFilasValidas,
            'fechas' => array_values($fechasDisponibles),
            'medicos' => array_values($medicosDisponibles),
            'matriz_fechas_medicos' => $matrizFechasMedicos,
            'columnas_detectadas' => $columnMap,
            'ya_importado' => (bool)$importacionPrevia,
            'importacion_previa_fecha' => $importacionPrevia ? $importacionPrevia->created_at->format('d/m/Y H:i') : null,
        ];
    }

    /**
     * Paso 2: Filtrar filas por fechas y médicos seleccionados, normalizar datos y clasificar contra BD
     */
    public function filtrarYClasificar(int $importacionId, array $fechasSeleccionadas, array $medicosSeleccionados, string $filePath): array
    {
        $this->loadCatalogs();

        $importacion = Importacion::findOrFail($importacionId);

        // Limpiar registros temporales previos de esta importación
        ImportacionRegistro::where('importacion_id', $importacionId)->delete();

        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);
        $sheet = $spreadsheet->getActiveSheet();

        $highestRow = $sheet->getHighestRow();
        $highestColumn = $sheet->getHighestColumn();
        
        [$columnMap, $dataStartRow] = $this->detectarEstructuraEncabezados($sheet, $highestColumn, $highestRow);

        $fechasSet = array_flip($fechasSeleccionadas);
        $medicosSet = array_flip(array_map(fn($m) => mb_strtoupper(trim($m), 'UTF-8'), $medicosSeleccionados));

        $filasFiltradas = [];
        $hashesEnLote = [];
        $dnisEnLote = [];

        for ($row = $dataStartRow; $row <= $highestRow; $row++) {
            $rowData = $sheet->rangeToArray("A{$row}:{$highestColumn}{$row}", null, true, false)[0];
            if (empty(array_filter($rowData, fn($v) => $v !== null && trim((string)$v) !== ''))) {
                continue;
            }

            $fechaRaw = $this->getValueByMap($rowData, $columnMap, 'fecha');
            $fecha = $this->normalizarFecha($fechaRaw);
            $fechaIso = $fecha ? $fecha->format('Y-m-d') : null;

            $medicoRaw = $this->getValueByMap($rowData, $columnMap, 'medico');
            $medicoNorm = trim(mb_strtoupper((string)$medicoRaw, 'UTF-8'));

            // Aplicar filtro
            if (!empty($fechasSeleccionadas) && (!$fechaIso || !isset($fechasSet[$fechaIso]))) {
                continue;
            }
            if (!empty($medicosSeleccionados) && (!$medicoNorm || !isset($medicosSet[$medicoNorm]))) {
                continue;
            }

            // Extraer y normalizar campos
            $identidadRaw = (string)$this->getValueByMap($rowData, $columnMap, 'identidad');
            $identidadLimpia = preg_replace('/\D/', '', $identidadRaw);
            $identidadFormateada = strlen($identidadLimpia) === 13
                ? substr($identidadLimpia, 0, 4) . '-' . substr($identidadLimpia, 4, 4) . '-' . substr($identidadLimpia, 8, 5)
                : $identidadRaw;

            $nombrePacienteRaw = (string)$this->getValueByMap($rowData, $columnMap, 'nombre_paciente');
            $nombrePacienteNorm = trim(mb_strtoupper($nombrePacienteRaw, 'UTF-8'));

            $fechaNacRaw = $this->getValueByMap($rowData, $columnMap, 'fecha_nacimiento');
            $fechaNac = $this->normalizarFecha($fechaNacRaw);

            $sexoRaw = (string)$this->getValueByMap($rowData, $columnMap, 'sexo');
            $sexoNorm = $this->normalizarSexo($sexoRaw);

            $edadRaw = (string)$this->getValueByMap($rowData, $columnMap, 'edad');
            $tipoRaw = (string)$this->getValueByMap($rowData, $columnMap, 'tipo');
            [$edadNorm, $tipoNorm] = $this->calcularEdadYTipo($edadRaw, $tipoRaw, $fechaNac, $fecha);

            $expedienteRaw = (string)$this->getValueByMap($rowData, $columnMap, 'expediente');
            $telefonoRaw = (string)$this->getValueByMap($rowData, $columnMap, 'telefono');
            $direccionRaw = (string)$this->getValueByMap($rowData, $columnMap, 'direccion');

            // Normalización de Colonia
            $coloniaResult = $this->extraerYNormalizarColonia($direccionRaw);

            // Normalización de Diagnósticos 1 a 7
            $diagnosticosResult = $this->normalizarDiagnosticosFila($rowData, $columnMap);

            // Resolver Médico y CM
            $medicoInfo = $this->resolverMedico($medicoNorm);

            // Generar Hash Único de Atención
            $hashAtencion = $this->generarHashAtencion(
                $identidadLimpia,
                $fechaIso,
                $medicoNorm,
                $expedienteRaw,
                $diagnosticosResult
            );

            if ($identidadLimpia) {
                $dnisEnLote[] = $identidadLimpia;
            }

            $filasFiltradas[] = [
                'fila_excel' => $row,
                'hash_registro' => $hashAtencion,
                'fecha_atencion' => $fechaIso,
                'medico' => $medicoInfo['medico'] ?? $medicoNorm,
                'cm' => $medicoInfo['cm'] ?? '',
                'prof' => $medicoInfo['prof'] ?? 'MÉDICO GENERAL',
                'numero_identidad' => $identidadFormateada,
                'identidad_limpia' => $identidadLimpia,
                'identidad_original' => $identidadRaw,
                'nombre_paciente' => $nombrePacienteNorm,
                'fecha_nacimiento' => $fechaNac ? $fechaNac->format('Y-m-d') : null,
                'edad' => $edadNorm,
                'tipo' => $tipoNorm,
                'sexo' => $sexoNorm,
                'expediente' => $expedienteRaw,
                'telefono' => $telefonoRaw,
                'direccion_original' => $direccionRaw,
                'colonia_normalizada' => $coloniaResult['colonia_normalizada'],
                'cod_col' => $coloniaResult['cod_col'],
                'colonia_id' => $coloniaResult['colonia_id'],
                'diagnosticos_json' => $diagnosticosResult,
                'datos_originales_json' => $rowData,
                'requiere_revision' => $coloniaResult['requiere_revision'] || $this->tieneDiagnosticosPendientes($diagnosticosResult),
                'motivo_revision' => $this->obtenerMotivoRevision($coloniaResult, $diagnosticosResult),
            ];
        }

        // Consultas agrupadas a la BD para verificar duplicados e históricos
        $hashesExistentesDb = [];
        if (!empty($filasFiltradas)) {
            $hashesList = array_column($filasFiltradas, 'hash_registro');
            $chunks = array_chunk($hashesList, 1000);
            foreach ($chunks as $chunk) {
                // Checar si existe en importaciones pasadas ya confirmadas
                $existingImported = DB::table('importacion_registros')
                    ->whereIn('hash_registro', $chunk)
                    ->where('estado', 'IMPORTADO')
                    ->pluck('hash_registro')
                    ->toArray();
                
                $hashesExistentesDb = array_merge($hashesExistentesDb, $existingImported);
            }

            // También verificar directamente en registros_globales por fecha e identidad
            $dnisList = array_filter(array_unique(array_column($filasFiltradas, 'identidad_limpia')));
            $fechasList = array_filter(array_unique(array_column($filasFiltradas, 'fecha_atencion')));
            if (!empty($dnisList) && !empty($fechasList)) {
                $rgRecords = DB::table('registros_globales')
                    ->whereIn('fecha', $fechasList)
                    ->get();
                
                foreach ($rgRecords as $rg) {
                    $rgDniLimpio = preg_replace('/\D/', '', $rg->identidad ?? '');
                    $rgMedicoNorm = trim(mb_strtoupper((string)($rg->medico ?? ''), 'UTF-8'));
                    $rgDxList = [];
                    for ($k = 1; $k <= 7; $k++) {
                        $colCod = "cod_{$k}";
                        $colDx = "diagnostico_{$k}";
                        if (!empty($rg->$colCod) || !empty($rg->$colDx)) {
                            $rgDxList[] = ['codigo' => $rg->$colCod ?? '', 'original' => $rg->$colDx ?? ''];
                        }
                    }
                    $rgHash = $this->generarHashAtencion($rgDniLimpio, $rg->fecha, $rgMedicoNorm, (string)($rg->exp ?? ''), $rgDxList);
                    $hashesExistentesDb[] = $rgHash;
                }
            }
        }

        // Consultar pacientes existentes por DNI limpio
        $pacientesExistentes = [];
        if (!empty($dnisEnLote)) {
            $chunksDni = array_chunk(array_unique($dnisEnLote), 1000);
            foreach ($chunksDni as $c) {
                $pacs = Paciente::whereIn('dni_limpio', $c)->orWhereIn('dni', $c)->get()->keyBy('dni_limpio')->toArray();
                $pacientesExistentes = array_merge($pacientesExistentes, $pacs);
            }
        }

        // Contar registros existentes en registros_globales agrupados por (fecha, medico)
        $existentesEnBdPorDiaMedico = [];
        if (!empty($fechasList)) {
            $rgCounts = DB::table('registros_globales')
                ->whereIn('fecha', $fechasList)
                ->select('fecha', 'medico', DB::raw('COUNT(*) as total'))
                ->groupBy('fecha', 'medico')
                ->get();

            foreach ($rgCounts as $rg) {
                $fFormato = Carbon::parse($rg->fecha)->format('d/m/Y');
                $mNorm = trim(mb_strtoupper((string)($rg->medico ?? ''), 'UTF-8'));
                $key = $fFormato . ' — ' . $mNorm;
                $existentesEnBdPorDiaMedico[$key] = (int)$rg->total;
            }
        }

        // Clasificar registros
        $seenHashesInBatch = [];
        $recordsToInsert = [];
        $now = now();

        $stats = [
            'total_seleccionados' => count($filasFiltradas),
            'nuevos' => 0,
            'ya_existentes' => 0,
            'duplicados' => 0,
            'pendientes' => 0,
            'errores' => 0,
        ];

        $resumenPorDiaMedico = [];

        foreach ($filasFiltradas as $f) {
            $estado = 'NUEVO';
            $motivo = null;

            // Validación de errores críticos
            if (empty($f['identidad_limpia']) && empty($f['nombre_paciente'])) {
                $estado = 'ERROR';
                $motivo = 'Sin DNI ni nombre de paciente';
                $stats['errores']++;
            } elseif (empty($f['fecha_atencion'])) {
                $estado = 'ERROR';
                $motivo = 'Fecha de atención no válida';
                $stats['errores']++;
            } elseif (empty($f['medico'])) {
                $estado = 'ERROR';
                $motivo = 'Sin médico asignado';
                $stats['errores']++;
            } elseif (isset($seenHashesInBatch[$f['hash_registro']])) {
                $estado = 'DUPLICADO';
                $motivo = 'Registro repetido dentro del mismo archivo Excel (Fila ' . $seenHashesInBatch[$f['hash_registro']] . ')';
                $stats['duplicados']++;
            } elseif (in_array($f['hash_registro'], $hashesExistentesDb)) {
                $estado = 'YA_EXISTE';
                $motivo = 'Atención ya registrada previamente en el histórico';
                $stats['ya_existentes']++;
            } elseif ($f['requiere_revision']) {
                $estado = 'PENDIENTE_REVISION';
                $motivo = $f['motivo_revision'];
                $stats['pendientes']++;
            } else {
                $estado = 'NUEVO';
                $stats['nuevos']++;
            }

            $seenHashesInBatch[$f['hash_registro']] = $f['fila_excel'];

            // Agrupar resumen por (Fecha + Médico)
            $fFormato = $f['fecha_atencion'] ? Carbon::parse($f['fecha_atencion'])->format('d/m/Y') : 'SIN FECHA';
            $mNorm = $f['medico'] ?: 'SIN MÉDICO';
            $diaKey = $fFormato . ' — ' . $mNorm;

            if (!isset($resumenPorDiaMedico[$diaKey])) {
                $bdActual = $existentesEnBdPorDiaMedico[$diaKey] ?? 0;
                $resumenPorDiaMedico[$diaKey] = [
                    'fecha' => $fFormato,
                    'fecha_iso' => $f['fecha_atencion'],
                    'medico' => $mNorm,
                    'en_bd_actual' => $bdActual,
                    'total_excel' => 0,
                    'nuevos' => 0,
                    'existentes' => 0,
                    'pendientes' => 0,
                ];
            }
            $resumenPorDiaMedico[$diaKey]['total_excel']++;
            if ($estado === 'NUEVO') $resumenPorDiaMedico[$diaKey]['nuevos']++;
            elseif ($estado === 'YA_EXISTE') $resumenPorDiaMedico[$diaKey]['existentes']++;
            elseif ($estado === 'PENDIENTE_REVISION') $resumenPorDiaMedico[$diaKey]['pendientes']++;

            $pacId = isset($pacientesExistentes[$f['identidad_limpia']]) ? $pacientesExistentes[$f['identidad_limpia']]['id'] : null;

            $recordsToInsert[] = [
                'importacion_id' => $importacionId,
                'fila_excel' => $f['fila_excel'],
                'hash_registro' => $f['hash_registro'],
                'fecha_atencion' => $f['fecha_atencion'],
                'medico' => $f['medico'],
                'cm' => $f['cm'],
                'prof' => $f['prof'],
                'numero_identidad' => $f['numero_identidad'],
                'identidad_original' => $f['identidad_original'],
                'nombre_paciente' => $f['nombre_paciente'],
                'fecha_nacimiento' => $f['fecha_nacimiento'],
                'edad' => $f['edad'],
                'tipo' => $f['tipo'],
                'sexo' => $f['sexo'],
                'expediente' => $f['expediente'],
                'telefono' => $f['telefono'],
                'direccion_original' => $f['direccion_original'],
                'colonia_normalizada' => $f['colonia_normalizada'],
                'cod_col' => $f['cod_col'],
                'colonia_id' => $f['colonia_id'],
                'diagnosticos_json' => json_encode($f['diagnosticos_json']),
                'datos_originales_json' => json_encode($f['datos_originales_json']),
                'datos_normalizados_json' => json_encode($f),
                'paciente_id' => $pacId,
                'registro_global_id' => null,
                'estado' => $estado,
                'motivo_estado' => $motivo,
                'requiere_revision' => $f['requiere_revision'],
                'created_at' => $now,
                'updated_at' => $now,
            ];
        }

        // Inserción en bloques (chunks) a la tabla intermedia
        if (!empty($recordsToInsert)) {
            $chunks = array_chunk($recordsToInsert, 500);
            foreach ($chunks as $chunk) {
                ImportacionRegistro::insert($chunk);
            }
        }

        // Actualizar resumen en la importación
        $importacion->update([
            'filas_analizadas' => count($filasFiltradas),
            'resumen_estadistico' => $stats,
            'estado' => 'procesado',
        ]);

        return [
            'success' => true,
            'importacion_id' => $importacionId,
            'stats' => $stats,
            'resumen_dia_medico' => array_values($resumenPorDiaMedico),
        ];
    }

    /**
     * Obtener registros previsualizables con paginación y filtro por estado
     */
    public function obtenerPrevisualizacion(int $importacionId, string $filtroEstado = 'TODOS', int $page = 1, int $perPage = 50): array
    {
        $query = ImportacionRegistro::where('importacion_id', $importacionId);

        if ($filtroEstado !== 'TODOS') {
            $query->where('estado', $filtroEstado);
        }

        $total = $query->count();
        $registros = $query->orderBy('fila_excel', 'asc')
            ->skip(($page - 1) * $perPage)
            ->take($perPage)
            ->get();

        return [
            'total' => $total,
            'page' => $page,
            'per_page' => $perPage,
            'last_page' => ceil($total / $perPage),
            'data' => $registros,
        ];
    }

    /**
     * Paso 5: Corregir un registro en revisión puntual, guardar en memoria de aprendizaje y auto-propagar en el lote
     */
    public function corregirRegistro(int $registroId, array $datosCorregidos): array
    {
        $registro = ImportacionRegistro::findOrFail($registroId);

        // 1. Guardar y aprender equivalencia de Colonia
        if (isset($datosCorregidos['colonia_normalizada']) && !empty($datosCorregidos['colonia_normalizada'])) {
            $registro->colonia_normalizada = mb_strtoupper(trim($datosCorregidos['colonia_normalizada']), 'UTF-8');
            $codCol = $datosCorregidos['cod_col'] ?? $registro->cod_col;
            $colId = $datosCorregidos['colonia_id'] ?? $registro->colonia_id;

            if (empty($codCol) || empty($colId)) {
                $colDb = DB::table('colonias')->where('COLONIA', $registro->colonia_normalizada)->first();
                if ($colDb) {
                    $codCol = $colDb->COD_COL;
                    $colId = $colDb->id;
                }
            }

            $registro->cod_col = $codCol;
            $registro->colonia_id = $colId;

            if (!empty($registro->direccion_original)) {
                $normDirKey = self::normalizarTextoClave($registro->direccion_original);
                if ($normDirKey) {
                    DB::table('importacion_colonias_alias')->updateOrInsert(
                        ['texto_normalizado' => $normDirKey],
                        [
                            'texto_original' => $registro->direccion_original,
                            'cod_col' => (string)($registro->cod_col ?? ''),
                            'colonia' => (string)$registro->colonia_normalizada,
                            'colonia_id' => $registro->colonia_id,
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );
                }
            }
        }

        // 2. Guardar y aprender equivalencia de Diagnóstico
        if (isset($datosCorregidos['codigo']) || isset($datosCorregidos['diagnostico'])) {
            $dxList = is_array($registro->diagnosticos_json) ? $registro->diagnosticos_json : [];
            $orig = $dxList[0]['original'] ?? ($datosCorregidos['diagnostico'] ?? '');
            $codigo = trim((string)($datosCorregidos['codigo'] ?? ($dxList[0]['codigo'] ?? '')));
            $patologia = trim((string)($datosCorregidos['diagnostico'] ?? ($dxList[0]['diagnostico'] ?? '')));
            $dxId = $datosCorregidos['diagnostico_id'] ?? null;

            if (empty($codigo) || empty($patologia) || empty($dxId)) {
                $dxDb = null;
                if (!empty($codigo)) {
                    $dxDb = DB::table('diagnosticos')->where('codigo', $codigo)->first();
                } elseif (!empty($patologia)) {
                    $dxDb = DB::table('diagnosticos')->where('patologia', $patologia)->first();
                }
                if ($dxDb) {
                    $codigo = $dxDb->codigo;
                    $patologia = $dxDb->patologia;
                    $dxId = $dxDb->id;
                }
            }

            $dxList[0] = [
                'posicion' => 1,
                'original' => $orig,
                'diagnostico' => $patologia,
                'codigo' => $codigo,
                'diagnostico_id' => $dxId,
                'condicion' => $dxList[0]['condicion'] ?? 'N',
                'coincidencia_exacta' => true,
            ];
            $registro->diagnosticos_json = $dxList;

            // Persistir aprendizaje en la memoria institucional
            if (!empty($orig)) {
                $normDxKey = self::normalizarTextoClave($orig);
                if ($normDxKey) {
                    DB::table('importacion_diagnosticos_alias')->updateOrInsert(
                        ['texto_normalizado' => $normDxKey],
                        [
                            'texto_original' => $orig,
                            'codigo' => $codigo,
                            'patologia' => $patologia,
                            'diagnostico_id' => $dxId,
                            'updated_at' => now(),
                            'created_at' => now(),
                        ]
                    );

                    // PROPAGACIÓN AUTOMÁTICA EN EL MISMO LOTE:
                    // Si hay otros registros en la misma importación con ese mismo texto original no asignado, actualizarlos también
                    /** @var \Illuminate\Database\Eloquent\Collection<int, ImportacionRegistro> $otrosRegistros */
                    $otrosRegistros = ImportacionRegistro::where('importacion_id', $registro->importacion_id)
                        ->where('id', '!=', $registroId)
                        ->where('estado', 'PENDIENTE_REVISION')
                        ->get();

                    foreach ($otrosRegistros as $otro) {
                        /** @var ImportacionRegistro $otro */
                        $otroDxList = is_array($otro->diagnosticos_json) ? $otro->diagnosticos_json : [];
                        if (isset($otroDxList[0]['original']) && self::normalizarTextoClave($otroDxList[0]['original']) === $normDxKey) {
                            $otroDxList[0]['diagnostico'] = $patologia;
                            $otroDxList[0]['codigo'] = $codigo;
                            $otroDxList[0]['diagnostico_id'] = $dxId;
                            $otroDxList[0]['coincidencia_exacta'] = true;
                            $otro->diagnosticos_json = $otroDxList;
                            $otro->requiere_revision = false;
                            $otro->estado = 'NUEVO';
                            $otro->motivo_estado = 'Auto-asignado por memoria de alias';
                            $otro->save();
                        }
                    }
                }
            }
        } elseif (isset($datosCorregidos['diagnosticos_json'])) {
            $registro->diagnosticos_json = $datosCorregidos['diagnosticos_json'];
        }

        $registro->requiere_revision = false;
        $registro->estado = 'NUEVO';
        $registro->motivo_estado = 'Corregido / Asignado por usuario';
        $registro->save();

        $importacionId = $registro->importacion_id;
        $stats = [
            'total_seleccionados' => ImportacionRegistro::where('importacion_id', $importacionId)->count(),
            'nuevos' => ImportacionRegistro::where('importacion_id', $importacionId)->where('estado', 'NUEVO')->count(),
            'ya_existentes' => ImportacionRegistro::where('importacion_id', $importacionId)->where('estado', 'YA_EXISTE')->count(),
            'duplicados' => ImportacionRegistro::where('importacion_id', $importacionId)->where('estado', 'DUPLICADO')->count(),
            'pendientes' => ImportacionRegistro::where('importacion_id', $importacionId)->where('estado', 'PENDIENTE_REVISION')->count(),
            'errores' => ImportacionRegistro::where('importacion_id', $importacionId)->where('estado', 'ERROR')->count(),
        ];

        return [
            'success' => true,
            'registro' => $registro,
            'stats' => $stats,
        ];
    }

    /**
     * Paso 6: Confirmación e Importación Transaccional a las tablas principales
     */
    public function confirmarImportacion(int $importacionId, array $opciones = []): array
    {
        $importacion = Importacion::findOrFail($importacionId);

        $soloNuevos = $opciones['solo_nuevos'] ?? true;
        $idsSeleccionados = $opciones['ids'] ?? [];

        $query = ImportacionRegistro::where('importacion_id', $importacionId);

        if (!empty($idsSeleccionados)) {
            $query->whereIn('id', $idsSeleccionados);
        } elseif ($soloNuevos) {
            $query->where('estado', 'NUEVO');
        } else {
            $query->where('estado', '!=', 'ERROR');
        }

        $registrosAImportar = $query->get();

        if ($registrosAImportar->isEmpty()) {
            return [
                'success' => false,
                'message' => 'No hay registros válidos seleccionados para importar.',
                'total_importados' => 0,
            ];
        }

        $totalImportados = 0;
        $now = now();

        $modo = $opciones['modo'] ?? 'anexar';

        // Si el modo es solo CARGAR A TABLA, no insertar en la base de datos
        if ($modo === 'cargar_tabla') {
            $mesesNombres = [
                1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL',
                5 => 'MAYO', 6 => 'JUNIO', 7 => 'JULIO', 8 => 'AGOSTO',
                9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE'
            ];
            $filasTabla = [];
            $totalImportados = 0;

            foreach ($registrosAImportar as $reg) {
                $fechaAtencion = $reg->fecha_atencion ? Carbon::parse($reg->fecha_atencion) : now();
                $ano = $fechaAtencion->year;
                $mes = $mesesNombres[$fechaAtencion->month] ?? 'AGOSTO';
                $se = $this->calcularSemanaEpidemiologica($fechaAtencion);
                $rangos = $this->calcularRangosEpidemiologicos($reg->edad, $reg->tipo);

                $dxList = is_array($reg->diagnosticos_json) ? $reg->diagnosticos_json : [];
                $dxData = [];
                for ($i = 1; $i <= 7; $i++) {
                    $dx = $dxList[$i - 1] ?? null;
                    $dxData["cod_{$i}"] = $dx['codigo'] ?? null;
                    $dxData["diagnostico_{$i}"] = $dx['diagnostico'] ?? null;
                    $dxData["cond_{$i}"] = $dx['condicion'] ?? null;
                }

                $medicoInfo = $this->buscarMedicoEnCatalogo($reg->medico);

                $filaObj = [
                    'numero' => $totalImportados + 1,
                    'ano' => (string)$ano,
                    'mes' => (string)$mes,
                    'cm' => (string)($reg->cm ?: ($medicoInfo['cod_med'] ?? '')),
                    'medico' => (string)($medicoInfo['nom_med'] ?? $reg->medico ?? ''),
                    'prof' => (string)($medicoInfo['especialidad'] ?? $reg->prof ?: 'MÉDICO GENERAL'),
                    'fecha' => $fechaAtencion->format('d/m/Y'),
                    'se' => (int)$se,
                    'exp' => (string)($reg->expediente ?? ''),
                    'identidad' => (string)($reg->numero_identidad ?? ''),
                    'nombre_paciente' => (string)($reg->nombre_paciente ?? ''),
                    'fecha_nacimiento' => $reg->fecha_nacimiento ? $reg->fecha_nacimiento->format('d/m/Y') : '',
                    'sexo' => (string)($reg->sexo ?? ''),
                    'edad' => (string)($reg->edad ?? ''),
                    'tipo' => (string)($reg->tipo ?: 'A'),
                    'rango' => (string)$rangos['rango'],
                    'rango_2' => (string)$rangos['rango_2'],
                    'rango_3' => (string)$rangos['rango_3'],
                    'rango_4' => (string)$rangos['rango_4'],
                    'rango_5' => (string)$rangos['rango_5'],
                    'cond' => (string)($dxData['cond_1'] ?? 'N'),
                    'cod_col' => (string)($reg->cod_col ?? ''),
                    'colonia' => (string)($reg->colonia_normalizada ?? ''),
                    'referido_a' => '',
                    'referido_de' => '',
                    'pg_emb' => 'POBLACIONGENERAL',
                    'jornada' => $medicoInfo['jornada'] ?? 'MATUTINA',
                    'sm' => '',
                ];

                for ($j = 1; $j <= 7; $j++) {
                    $filaObj["cod_{$j}"] = (string)($dxData["cod_{$j}"] ?? '');
                    $filaObj["diagnostico_{$j}"] = (string)($dxData["diagnostico_{$j}"] ?? '');
                    $filaObj["cond_{$j}"] = (string)($dxData["cond_{$j}"] ?? '');
                }

                $filasTabla[] = $filaObj;
                $totalImportados++;
            }

            return [
                'success' => true,
                'message' => "Se cargaron {$totalImportados} registros a la tabla AT-1 para revisión y edición.",
                'total_importados' => $totalImportados,
                'filas_tabla' => $filasTabla,
            ];
        }

        DB::beginTransaction();

        try {
            // Si el modo es SOBREESCRIBIR, limpiar los registros previos de esa fecha y médico en registros_globales
            if ($modo === 'sobreescribir') {
                $fechasAfectadas = $registrosAImportar->pluck('fecha_atencion')->unique()->filter()->values()->toArray();
                $medicosAfectados = $registrosAImportar->pluck('medico')->unique()->filter()->values()->toArray();

                foreach ($fechasAfectadas as $fIso) {
                    foreach ($medicosAfectados as $mNorm) {
                        DB::table('registros_globales')
                            ->where('fecha', $fIso)
                            ->where('medico', $mNorm)
                            ->delete();
                    }
                }
            }

            // 1. Reconstruir / Actualizar Pacientes en masa
            $pacientesData = [];
            foreach ($registrosAImportar as $reg) {
                $dniLimpio = preg_replace('/\D/', '', $reg->numero_identidad);
                if ($dniLimpio && strlen($dniLimpio) >= 8) {
                    $pacientesData[$dniLimpio] = [
                        'nombre_completo' => $reg->nombre_paciente,
                        'dni' => $reg->numero_identidad,
                        'dni_limpio' => $dniLimpio,
                        'expediente' => $reg->expediente,
                        'fecha_nacimiento' => $reg->fecha_nacimiento ? $reg->fecha_nacimiento->format('Y-m-d') : null,
                        'colonia' => $reg->colonia_normalizada,
                        'telefono' => $reg->telefono,
                        'sexo' => $reg->sexo,
                        'edad' => is_numeric($reg->edad) ? (int)$reg->edad : null,
                    ];
                }
            }

            // Upsert / Save Pacientes
            $pacientesMap = [];
            foreach ($pacientesData as $dniL => $pData) {
                $paciente = Paciente::where('dni_limpio', $dniL)->first();
                if (!$paciente) {
                    $paciente = Paciente::create($pData);
                } else {
                    // Actualizar campos que vengan más completos
                    if (!empty($pData['nombre_completo'])) $paciente->nombre_completo = $pData['nombre_completo'];
                    if (!empty($pData['fecha_nacimiento'])) $paciente->fecha_nacimiento = $pData['fecha_nacimiento'];
                    if (!empty($pData['colonia'])) $paciente->colonia = $pData['colonia'];
                    if (!empty($pData['telefono'])) $paciente->telefono = $pData['telefono'];
                    if (!empty($pData['expediente'])) $paciente->expediente = $pData['expediente'];
                    if (!empty($pData['sexo'])) $paciente->sexo = $pData['sexo'];
                    if (!empty($pData['edad'])) $paciente->edad = $pData['edad'];
                    $paciente->save();
                }
                $pacientesMap[$dniL] = $paciente->id;
            }

            // 2. Insertar en registros_globales
            $registrosGlobalesAInsertar = [];
            $registrosImportadosIds = [];

            // Meses en español
            $mesesNombres = [
                1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO', 4 => 'ABRIL',
                5 => 'MAYO', 6 => 'JUNIO', 7 => 'JULIO', 8 => 'AGOSTO',
                9 => 'SEPTIEMBRE', 10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE'
            ];

            foreach ($registrosAImportar as $reg) {
                $fechaAtencion = $reg->fecha_atencion ? Carbon::parse($reg->fecha_atencion) : now();
                $ano = $fechaAtencion->year;
                $mes = $mesesNombres[$fechaAtencion->month] ?? 'AGOSTO';
                $se = $this->calcularSemanaEpidemiologica($fechaAtencion);

                // Calcular Rangos 1 a 5
                $rangos = $this->calcularRangosEpidemiologicos($reg->edad, $reg->tipo);

                // Desglosar diagnósticos
                $dxList = is_array($reg->diagnosticos_json) ? $reg->diagnosticos_json : [];
                $dxData = [];
                for ($i = 1; $i <= 7; $i++) {
                    $dx = $dxList[$i - 1] ?? null;
                    $dxData["cod_{$i}"] = $dx['codigo'] ?? null;
                    $dxData["diagnostico_{$i}"] = $dx['diagnostico'] ?? null;
                    $dxData["cond_{$i}"] = $dx['condicion'] ?? null;
                }

                $dniLimpio = preg_replace('/\D/', '', $reg->numero_identidad);
                $pacienteId = $pacientesMap[$dniLimpio] ?? null;

                $regGlobalId = DB::table('registros_globales')->insertGetId([
                    'ano' => $ano,
                    'mes' => $mes,
                    'numero' => $reg->fila_excel,
                    'cm' => $reg->cm,
                    'medico' => $reg->medico,
                    'prof' => $reg->prof ?: 'MÉDICO GENERAL',
                    'fecha' => $fechaAtencion->format('Y-m-d'),
                    'se' => $se,
                    'exp' => $reg->expediente,
                    'identidad' => $reg->numero_identidad,
                    'nombre_paciente' => $reg->nombre_paciente,
                    'telefono' => $reg->telefono,
                    'fecha_nacimiento' => $reg->fecha_nacimiento ? $reg->fecha_nacimiento->format('Y-m-d') : null,
                    'etnia' => null,
                    'sexo' => $reg->sexo,
                    'edad' => $reg->edad,
                    'tipo' => $reg->tipo ?: 'A',
                    'rango' => $rangos['rango'],
                    'rango_2' => $rangos['rango_2'],
                    'rango_3' => $rangos['rango_3'],
                    'rango_4' => $rangos['rango_4'],
                    'rango_5' => $rangos['rango_5'],
                    'cond' => $dxData['cond_1'] ?? 'N',
                    'cod_col' => $reg->cod_col,
                    'colonia' => $reg->colonia_normalizada,
                    'cod_1' => $dxData['cod_1'],
                    'diagnostico_1' => $dxData['diagnostico_1'],
                    'cond_1' => $dxData['cond_1'],
                    'cod_2' => $dxData['cod_2'],
                    'diagnostico_2' => $dxData['diagnostico_2'],
                    'cond_2' => $dxData['cond_2'],
                    'cod_3' => $dxData['cod_3'],
                    'diagnostico_3' => $dxData['diagnostico_3'],
                    'cond_3' => $dxData['cond_3'],
                    'cod_4' => $dxData['cod_4'],
                    'diagnostico_4' => $dxData['diagnostico_4'],
                    'cond_4' => $dxData['cond_4'],
                    'cod_5' => $dxData['cod_5'],
                    'diagnostico_5' => $dxData['diagnostico_5'],
                    'cond_5' => $dxData['cond_5'],
                    'cod_6' => $dxData['cod_6'],
                    'diagnostico_6' => $dxData['diagnostico_6'],
                    'cond_6' => $dxData['cond_6'],
                    'cod_7' => $dxData['cod_7'],
                    'diagnostico_7' => $dxData['diagnostico_7'],
                    'cond_7' => $dxData['cond_7'],
                    'sg' => null,
                    'sg2' => null,
                    'referido_a' => null,
                    'referido_de' => null,
                    'pg_emb' => 'PG',
                    'jornada' => $medicoInfo['jornada'] ?? 'MATUTINA',
                    'sm' => null,
                    'user_id' => $importacion->usuario_id,
                    'created_at' => $now,
                ]);

                // Construir fila para inyectar en la tabla de create.blade.php
                $fechaDdmmyyyy = $fechaAtencion->format('d/m/Y');
                $filaObj = [
                    'numero' => $totalImportados + 1,
                    'ano' => (string)$ano,
                    'mes' => (string)$mes,
                    'cm' => (string)($reg->cm ?? ''),
                    'medico' => (string)($reg->medico ?? ''),
                    'prof' => (string)($reg->prof ?: 'MÉDICO GENERAL'),
                    'fecha' => $fechaDdmmyyyy,
                    'se' => (int)$se,
                    'exp' => (string)($reg->expediente ?? ''),
                    'identidad' => (string)($reg->numero_identidad ?? ''),
                    'nombre_paciente' => (string)($reg->nombre_paciente ?? ''),
                    'fecha_nacimiento' => $reg->fecha_nacimiento ? $reg->fecha_nacimiento->format('Y-m-d') : '',
                    'sexo' => (string)($reg->sexo ?? ''),
                    'edad' => (string)($reg->edad ?? ''),
                    'tipo' => (string)($reg->tipo ?: 'A'),
                    'rango' => (string)$rangos['rango'],
                    'rango_2' => (string)$rangos['rango_2'],
                    'rango_3' => (string)$rangos['rango_3'],
                    'rango_4' => (string)$rangos['rango_4'],
                    'rango_5' => (string)$rangos['rango_5'],
                    'cond' => (string)($dxData['cond_1'] ?? 'N'),
                    'cod_col' => (string)($reg->cod_col ?? ''),
                    'colonia' => (string)($reg->colonia_normalizada ?? ''),
                    'referido_a' => '',
                    'referido_de' => '',
                    'pg_emb' => 'POBLACIONGENERAL',
                    'jornada' => $medicoInfo['jornada'] ?? 'MATUTINA',
                    'sm' => '',
                ];

                for ($j = 1; $j <= 7; $j++) {
                    $filaObj["cod_{$j}"] = (string)($dxData["cod_{$j}"] ?? '');
                    $filaObj["diagnostico_{$j}"] = (string)($dxData["diagnostico_{$j}"] ?? '');
                    $filaObj["cond_{$j}"] = (string)($dxData["cond_{$j}"] ?? '');
                }

                $filasTabla[] = $filaObj;

                $totalImportados++;
            }

            // Actualizar estado general de la importación
            $importacion->update([
                'filas_importadas' => $importacion->filas_importadas + $totalImportados,
                'estado' => 'completado',
            ]);

            DB::commit();

            return [
                'success' => true,
                'message' => "Se importaron exitosamente {$totalImportados} atenciones al histórico clínico.",
                'total_importados' => $totalImportados,
                'filas_tabla' => $filasTabla,
            ];

        } catch (Throwable $e) {
            DB::rollBack();
            Log::error('Error en confirmación de importación Excel: ' . $e->getMessage());

            return [
                'success' => false,
                'message' => 'Ocurrió un error durante la importación: ' . $e->getMessage(),
                'total_importados' => 0,
            ];
        }
    }

    /* =========================================================================
     * MÉTODOS AUXILIARES Y DE NORMALIZACIÓN
     * ========================================================================= */

    /**
     * Detección flexible de encabezados con prioridad de coincidencias
     */
    protected function detectarColumnas(array $headers): array
    {
        $cleanHeaders = array_map(function ($h) {
            $h = (string)$h;
            $h = mb_strtolower($h, 'UTF-8');
            $h = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ'], ['a', 'e', 'i', 'o', 'u', 'n'], $h);
            $h = preg_replace('/[^a-z0-9]/', '', $h);
            return $h;
        }, $headers);

        $aliases = [
            'identidad' => ['numerodeidentidad', 'numeroidentidad', 'noidentidad', 'identidad', 'dni', 'cedula', 'documento', 'nodocumento', 'numerodocumento', 'identificacion'],
            'nombre_paciente' => ['nombresyapellidos', 'nombrepaciente', 'paciente', 'nombrecompleto', 'nombres', 'nombre', 'apellidosynombres'],
            'fecha' => ['fechadeatencion', 'fechaatencion', 'fecha', 'fechaconsulta', 'fechadeconsulta', 'fconsulta', 'date', 'atencion'],
            'medico' => ['medico', 'nombremedico', 'nombredelmedico', 'doctor', 'profesional', 'atendidopor', 'responsable', 'medicoconsulta'],
            'cm' => ['codigomedico', 'codmed', 'colegiomedico', 'cm'],
            'prof' => ['profesion', 'cargo', 'especialidadmedico', 'especialidad', 'prof'],
            'fecha_nacimiento' => ['fechanacimiento', 'fechadenacimiento', 'fnacimiento', 'nacimiento', 'fdenacimiento'],
            'edad' => ['edadanios', 'anios', 'anos', 'edad', 'age'],
            'edad_meses' => ['meses', 'edadmeses'],
            'edad_dias' => ['dias', 'edaddias'],
            'tipo' => ['tipodeedad', 'tipoedad', 'unidadedad', 'unidaddeedad'],
            'sexo' => ['sexofm', 'sexo', 'genero', 'gender'],
            'expediente' => ['numerodeexpediente', 'numeroexpediente', 'noexpediente', 'numexpediente', 'expediente', 'noexp', 'exp'],
            'historia_clinica' => ['numerodehistoriaclinica', 'numerohistoriaclinica', 'historiaclinica', 'nohc', 'hc'],
            'telefono' => ['numerodetelefono', 'telefono1', 'telefono', 'tel', 'celular'],
            'direccion' => ['procedencia', 'direccionactualdelpaciente', 'direccionpaciente', 'residencia', 'direccion', 'colonia', 'localidad', 'domicilio'],
            'diagnostico_1' => ['diagnosticoactividad', 'diagnosticoprincipal', 'diagnosticoingreso', 'patologia1', 'diagnostico1', 'diagnostico', 'diag1', 'dx1'],
            'cond_1' => ['condiciondiagnostico1', 'condicion1', 'cond1', 'condicion', 'tipo1'],
            'diagnostico_2' => ['diagnostico2', 'diag2', 'patologia2', 'dx2'],
            'cond_2' => ['condicion2', 'cond2', 'tipo2'],
            'diagnostico_3' => ['diagnostico3', 'diag3', 'patologia3', 'dx3'],
            'cond_3' => ['condicion3', 'cond3', 'tipo3'],
            'diagnostico_4' => ['diagnostico4', 'diag4', 'patologia4', 'dx4'],
            'cond_4' => ['condicion4', 'cond4', 'tipo4'],
            'diagnostico_5' => ['diagnostico5', 'diag5', 'patologia5', 'dx5'],
            'cond_5' => ['condicion5', 'cond5', 'tipo5'],
            'diagnostico_6' => ['diagnostico6', 'diag6', 'patologia6', 'dx6'],
            'cond_6' => ['condicion6', 'cond6', 'tipo6'],
            'diagnostico_7' => ['diagnostico7', 'diag7', 'patologia7', 'dx7'],
            'cond_7' => ['condicion7', 'cond7', 'tipo7'],
        ];

        $map = [];
        foreach ($aliases as $field => $fieldAliases) {
            // 1. Coincidencia exacta prioritaria
            foreach ($fieldAliases as $alias) {
                foreach ($cleanHeaders as $colIdx => $cleanH) {
                    if ($cleanH === $alias) {
                        $map[$field] = $colIdx;
                        break 2;
                    }
                }
            }
            // 2. Coincidencia por subcadena si no hubo exacta
            if (!isset($map[$field])) {
                foreach ($fieldAliases as $alias) {
                    foreach ($cleanHeaders as $colIdx => $cleanH) {
                        if (str_contains($cleanH, $alias)) {
                            $map[$field] = $colIdx;
                            break 2;
                        }
                    }
                }
            }
        }

        // Si no se encontró expediente pero sí historia clínica (para archivos que no tengan columna expediente)
        if (!isset($map['expediente']) && isset($map['historia_clinica'])) {
            $map['expediente'] = $map['historia_clinica'];
        }

        return $map;
    }

    protected function getValueByMap(array $row, array $map, string $field)
    {
        if (isset($map[$field]) && isset($row[$map[$field]])) {
            return $row[$map[$field]];
        }
        return null;
    }

    /**
     * Normalizar fecha desde Excel (Serial o String)
     */
    protected function normalizarFecha($val): ?Carbon
    {
        if ($val === null || trim((string)$val) === '') return null;

        if (is_numeric($val) && $val > 20000) {
            try {
                return Carbon::instance(ExcelDate::excelToDateTimeObject($val));
            } catch (Throwable $e) {}
        }

        $str = trim((string)$val);
        $formats = ['d/m/Y', 'Y-m-d', 'd-m-Y', 'Y/m/d', 'd.m.Y', 'Y.m.d'];
        foreach ($formats as $fmt) {
            try {
                $d = Carbon::createFromFormat($fmt, $str);
                if ($d !== false && $d->year >= 1900 && $d->year <= 2100) {
                    return $d;
                }
            } catch (Throwable $e) {}
        }

        try {
            return new Carbon($str);
        } catch (Throwable $e) {
            return null;
        }
    }

    /**
     * Normalizar sexo según estándar AT-1 (Honduras):
     * 'M' = Mujer / Femenino
     * 'H' = Hombre / Masculino
     * En formato SESAL (F / M): 'F' -> 'M' (Mujer), 'M' -> 'H' (Hombre)
     */
    protected function normalizarSexo(?string $sexo): string
    {
        if (!$sexo) return 'M';
        $s = mb_strtoupper(trim($sexo), 'UTF-8');

        // Femenino / Mujer -> 'M' (Mujer en AT-1)
        if (in_array($s, ['F', 'FEMENINO', 'FEM', 'MUJER', 'FEMALE', 'W', 'WOMAN', '2'])) {
            return 'M';
        }

        // Masculino / Hombre -> 'H' (Hombre en AT-1)
        if (in_array($s, ['M', 'H', 'HOMBRE', 'MASCULINO', 'MASC', 'MALE', 'V', 'VARON', '1'])) {
            return 'H';
        }

        if (str_starts_with($s, 'FEM') || str_starts_with($s, 'MUJ')) {
            return 'M';
        }

        if (str_starts_with($s, 'MASC') || str_starts_with($s, 'HOMB') || str_starts_with($s, 'VAR')) {
            return 'H';
        }

        return ($s === 'H' || $s === 'M') ? 'H' : 'M';
    }

    /**
     * Calcular Edad y Tipo (Años 'A', Meses 'M', Días 'D')
     */
    protected function calcularEdadYTipo(?string $edadRaw, ?string $tipoRaw, ?Carbon $fechaNac, ?Carbon $fechaAtencion): array
    {
        $edad = trim((string)$edadRaw);
        $tipo = mb_strtoupper(trim((string)$tipoRaw), 'UTF-8');

        if ($fechaNac) {
            $refDate = $fechaAtencion ?: Carbon::now();
            $years = (int)$fechaNac->diffInYears($refDate);
            if ($years >= 1) {
                return [(string)$years, 'A'];
            }
            $months = (int)$fechaNac->diffInMonths($refDate);
            if ($months >= 1) {
                return [(string)$months, 'M'];
            }
            $days = (int)$fechaNac->diffInDays($refDate);
            return [(string)$days, 'D'];
        }

        if (is_numeric($edad)) {
            if (!$tipo || !in_array($tipo, ['A', 'M', 'D'])) {
                $tipo = 'A';
            }
            return [(string)(int)$edad, $tipo];
        }

        // Parsear si viene como "37 AÑOS" o "5 MESES"
        if (preg_match('/(\d+)\s*(AÑO|AÑOS|MES|MESES|DIA|DIAS|DÍAS)?/i', $edadRaw, $m)) {
            $num = $m[1];
            $unit = mb_strtoupper($m[2] ?? 'A', 'UTF-8');
            $t = str_starts_with($unit, 'M') ? 'M' : (str_starts_with($unit, 'D') ? 'D' : 'A');
            return [$num, $t];
        }

        return ['', 'A'];
    }

    /**
     * Extraer y normalizar colonia desde procedencia/dirección
     */
    public function extraerYNormalizarColonia(?string $direccion): array
    {
        $this->loadCatalogs();

        if (empty($direccion) || trim($direccion) === '') {
            return [
                'colonia_normalizada' => 'NO ESPECIFICADA',
                'cod_col' => null,
                'colonia_id' => null,
                'requiere_revision' => false,
            ];
        }

        $dirUpper = mb_strtoupper(trim($direccion), 'UTF-8');
        $normDirKey = self::normalizarTextoClave($direccion);

        // 0. Comprobar Memoria de Aprendizaje de Colonias previa
        if ($normDirKey && isset($this->coloniasAliasCatalog[$normDirKey])) {
            $aliasCol = $this->coloniasAliasCatalog[$normDirKey];
            return [
                'colonia_normalizada' => $aliasCol->colonia,
                'cod_col' => $aliasCol->cod_col,
                'colonia_id' => $aliasCol->colonia_id,
                'requiere_revision' => false,
            ];
        }

        // Regex para extraer el nombre tras prefijos comunes
        $pattern = '/(?:COL\.|COLONIA|COL\,|COL|BARRIO|B\°|ALDEA|RES\.|RESIDENCIAL)\s*([A-ZÁÉÍÓÚÑ0-9\s\.\-]+?)(?:\,|\.|\;|\-|\bSECTOR\b|\bCALLE\b|\bBLOQUE\b|\bCASA\b|\bAVENIDA\b|\bDETRAS\b|\bATR[AÁ]S\b|\bFRENTE\b|\bLOTE\b|$)/i';
        $coloniaExtraida = $dirUpper;
        if (preg_match($pattern, $dirUpper, $matches)) {
            $coloniaExtraida = trim($matches[1]);
        }

        // Limpieza de caracteres extra
        $coloniaExtraida = trim(preg_replace('/\s+/', ' ', $coloniaExtraida));

        // Buscar en catálogo de colonias
        $mejorCoincidencia = null;
        $menorDistancia = 999;

        foreach ($this->coloniasCatalog as $c) {
            if ($c['nombre'] === $coloniaExtraida) {
                return [
                    'colonia_normalizada' => $c['nombre'],
                    'cod_col' => $c['cod_col'],
                    'colonia_id' => $c['id'],
                    'requiere_revision' => false,
                ];
            }

            // Coincidencia por contención
            if (str_contains($c['nombre'], $coloniaExtraida) || str_contains($coloniaExtraida, $c['nombre'])) {
                $mejorCoincidencia = $c;
                $menorDistancia = 0;
            }
        }

        if ($mejorCoincidencia) {
            return [
                'colonia_normalizada' => $mejorCoincidencia['nombre'],
                'cod_col' => $mejorCoincidencia['cod_col'],
                'colonia_id' => $mejorCoincidencia['id'],
                'requiere_revision' => false,
            ];
        }

        // Si no se encuentra en el catálogo, se conserva el texto limpio pero se marca para revisión si es ambiguo
        return [
            'colonia_normalizada' => $coloniaExtraida ?: 'NO ESPECIFICADA',
            'cod_col' => null,
            'colonia_id' => null,
            'requiere_revision' => strlen($coloniaExtraida) > 3,
        ];
    }

    /**
     * Normalizar diagnósticos 1 a 7 de la fila
     */
    protected function normalizarDiagnosticosFila(array $rowData, array $columnMap): array
    {
        $diagnosticos = [];

        for ($i = 1; $i <= 7; $i++) {
            $dxRaw = $this->getValueByMap($rowData, $columnMap, "diagnostico_{$i}");
            $condRaw = (string)$this->getValueByMap($rowData, $columnMap, "cond_{$i}");

            if ($dxRaw === null || trim((string)$dxRaw) === '') {
                continue;
            }

            $dxStr = trim(mb_strtoupper((string)$dxRaw, 'UTF-8'));
            $cond = in_array(mb_strtoupper($condRaw, 'UTF-8'), ['S', 'SUBSECUENTE', 'SUB']) ? 'S' : 'N';

            // Buscar en catálogo de diagnósticos
            $match = $this->buscarDiagnosticoCatalogo($dxStr);

            $diagnosticos[] = [
                'posicion' => $i,
                'original' => $dxStr,
                'diagnostico' => $match ? $match['patologia'] : $dxStr,
                'codigo' => $match ? $match['codigo'] : '',
                'diagnostico_id' => $match ? $match['id'] : null,
                'condicion' => $cond,
                'coincidencia_exacta' => (bool)$match,
            ];
        }

        return $diagnosticos;
    }

    /**
     * Buscar diagnóstico en catálogo
     */
    protected function buscarDiagnosticoCatalogo(string $texto): ?array
    {
        $cleanTexto = trim(mb_strtoupper($texto, 'UTF-8'));
        if (!$cleanTexto) return null;

        // 0. Comprobar Memoria de Aprendizaje y Equivalencias previas
        $normDxKey = self::normalizarTextoClave($texto);
        if ($normDxKey && isset($this->diagnosticosAliasCatalog[$normDxKey])) {
            $alias = $this->diagnosticosAliasCatalog[$normDxKey];
            return [
                'id' => $alias->diagnostico_id,
                'codigo' => $alias->codigo,
                'patologia' => $alias->patologia,
                'auxiliar' => '',
            ];
        }

        // 1. Coincidencia exacta por Código de Catálogo / CIE-10
        foreach ($this->diagnosticosCatalog as $d) {
            if ($d['codigo'] === $cleanTexto) {
                return $d;
            }
        }

        // 2. Coincidencia exacta por Patología
        foreach ($this->diagnosticosCatalog as $d) {
            if ($d['patologia'] === $cleanTexto) {
                return $d;
            }
        }

        // 3. Coincidencia por prefijo CIE-10 dentro del texto (ej. "I10 - HIPERTENSION")
        if (preg_match('/^([A-Z][0-9]{2}(?:\.[0-9]+)?)/i', $cleanTexto, $m)) {
            $code = mb_strtoupper($m[1], 'UTF-8');
            foreach ($this->diagnosticosCatalog as $d) {
                if ($d['codigo'] === $code) {
                    return $d;
                }
            }
        }

        // 4. Coincidencia por Contención Directa
        foreach ($this->diagnosticosCatalog as $d) {
            if (str_contains($cleanTexto, $d['patologia']) || str_contains($d['patologia'], $cleanTexto)) {
                return $d;
            }
        }

        // 5. Diccionario de Raíces Médicas Comunes a Catálogo
        $raices = [
            'HIPERTENSION' => 'HIPERTENSION ARTERIAL',
            'ASMA' => 'ASMA BRONQUIAL',
            'DIARREA' => 'DIARREAS',
            'GASTROENTERITIS' => 'DIARREAS',
            'DIABETES' => 'DIABETES MELLITUS',
            'NEUMONIA' => 'NEUMONIAS',
            'FIEBRE' => 'FIEBRE',
            'OTITIS' => 'OTITIS',
            'FARINGITIS' => 'FARINGITIS',
            'AMIGDALITIS' => 'AMIGDALITIS',
            'PARASITOSIS' => 'PARASITOSIS INTESTINAL',
            'ANEMIA' => 'ANEMIA',
            'GASTRITIS' => 'GASTRITIS',
            'LUMBALGIA' => 'LUMBALGIA',
            'RINOFARINGITIS' => 'RESFRIADO COMUN',
            'RESFRIADO' => 'RESFRIADO COMUN',
            'INFECCION URINARIA' => 'ENF. APARATO GENITOURINARIO',
            'VIAS URINARIAS' => 'ENF. APARATO GENITOURINARIO',
            'CONJUNTIVITIS' => 'CONJUNTIVITIS',
            'DERMATITIS' => 'DERMATITIS',
            'MICOSIS' => 'MICOSIS',
        ];

        foreach ($raices as $raiz => $patologiaDestino) {
            if (str_contains($cleanTexto, $raiz)) {
                foreach ($this->diagnosticosCatalog as $d) {
                    if (str_contains($d['patologia'], $patologiaDestino) || str_contains($patologiaDestino, $d['patologia'])) {
                        return $d;
                    }
                }
            }
        }

        return null;
    }

    protected function tieneDiagnosticosPendientes(array $diagnosticos): bool
    {
        foreach ($diagnosticos as $d) {
            if (empty($d['codigo'])) return true;
        }
        return false;
    }

    protected function obtenerMotivoRevision(array $coloniaRes, array $diagnosticos): string
    {
        $motivos = [];
        if ($coloniaRes['requiere_revision']) {
            $motivos[] = "Colonia '{$coloniaRes['colonia_normalizada']}' no encontrada en catálogo";
        }
        foreach ($diagnosticos as $d) {
            if (empty($d['codigo'])) {
                $motivos[] = "Diagnóstico '{$d['original']}' no asociado a CIE-10";
            }
        }
        return implode('; ', $motivos);
    }

    /**
     * Resolver médico y obtener su CM, Profesión y Jornada exacta según catálogo
     */
    protected function resolverMedico(string $medicoNorm): array
    {
        $cleanInput = self::normalizarClaveMedico($medicoNorm);
        if (empty($cleanInput)) {
            return [
                'medico' => $medicoNorm,
                'cm' => '',
                'prof' => 'MÉDICO GENERAL',
                'jornada' => 'MATUTINA',
            ];
        }

        // 1. Coincidencia por Código (si es numérico)
        if (is_numeric($medicoNorm)) {
            foreach ($this->medicosCatalog as $m) {
                if ((string)$m['cod_med'] === (string)$medicoNorm) {
                    return [
                        'medico' => $m['nom_med'],
                        'cm' => $m['cod_med'],
                        'prof' => $m['especialidad'] ?: 'MÉDICO GENERAL',
                        'jornada' => $m['jornada'] ?: 'MATUTINA',
                    ];
                }
            }
        }

        // 2. Coincidencia exacta de texto
        foreach ($this->medicosCatalog as $m) {
            if ($m['nom_med'] === $medicoNorm) {
                return [
                    'medico' => $m['nom_med'],
                    'cm' => $m['cod_med'],
                    'prof' => $m['especialidad'] ?: 'MÉDICO GENERAL',
                    'jornada' => $m['jornada'] ?: 'MATUTINA',
                ];
            }
        }

        // 3. Coincidencia normalizada (sin títulos ni acentos)
        foreach ($this->medicosCatalog as $m) {
            if (self::normalizarClaveMedico($m['nom_med']) === $cleanInput) {
                return [
                    'medico' => $m['nom_med'],
                    'cm' => $m['cod_med'],
                    'prof' => $m['especialidad'] ?: 'MÉDICO GENERAL',
                    'jornada' => $m['jornada'] ?: 'MATUTINA',
                ];
            }
        }

        // 4. Mapeos manuales directos para casos conocidos
        $aliasMap = [
            'ANDREA MEJIA' => 'MSS. ANDREA MICHELLE MEJIA MORAZAN',
            'DRA. MAGALY COELLO' => 'DRA. MAGALY ROCIO COELLO GARCIA',
            'MAGALY COELLO' => 'DRA. MAGALY ROCIO COELLO GARCIA',
            'ISSIS NOHEMY RIVAS ARTILES' => 'DRA. ISSIS NOHEMY RIVAS ARTILES',
            'DRA. ISSIS RIVAS' => 'DRA. ISSIS NOHEMY RIVAS ARTILES',
            'DRA.ISSIS RIVAS' => 'DRA. ISSIS NOHEMY RIVAS ARTILES',
            'KATHERINE ATENA FERNANDEZ PEREZ' => 'MSS.KATHERINE ATENA FERNANDEZ PEREZ',
            'MARCELA DE JESÚS CRUZ COLINDRES' => 'MSS. MARCELA DE JESUS CRUZ COLINDRES',
            'MARCELA DE JESUS CRUZ COLINDRES' => 'MSS. MARCELA DE JESUS CRUZ COLINDRES',
            'DRA. YUSEN NUÑEZ' => 'DRA. YUSEN NIESVANOVA NUÑEZ',
            'DR. EDWIN JOSUE ESPINAL MARTINEZ' => 'DR. EDWIN JOSE ESPINAL MARTINEZ',
        ];
        if (isset($aliasMap[$medicoNorm])) {
            $target = $aliasMap[$medicoNorm];
            foreach ($this->medicosCatalog as $m) {
                if ($m['nom_med'] === $target) {
                    return [
                        'medico' => $m['nom_med'],
                        'cm' => $m['cod_med'],
                        'prof' => $m['especialidad'] ?: 'MÉDICO GENERAL',
                        'jornada' => $m['jornada'] ?: 'MATUTINA',
                    ];
                }
            }
        }

        // 5. Coincidencia por conjunto de palabras significativas (>2 letras)
        $palabras = array_filter(explode(' ', $cleanInput), fn($p) => strlen($p) > 2);
        if (!empty($palabras)) {
            $candidatos = [];
            foreach ($this->medicosCatalog as $m) {
                $cleanM = self::normalizarClaveMedico($m['nom_med']);
                $todasCoinciden = true;
                foreach ($palabras as $p) {
                    if (!str_contains($cleanM, $p)) {
                        $todasCoinciden = false;
                        break;
                    }
                }
                if ($todasCoinciden) {
                    $candidatos[] = $m;
                }
            }
            if (count($candidatos) === 1) {
                $m = $candidatos[0];
                return [
                    'medico' => $m['nom_med'],
                    'cm' => $m['cod_med'],
                    'prof' => $m['especialidad'] ?: 'MÉDICO GENERAL',
                    'jornada' => $m['jornada'] ?: 'MATUTINA',
                ];
            }
        }

        // 6. Coincidencia por subcadena
        foreach ($this->medicosCatalog as $m) {
            $cleanM = self::normalizarClaveMedico($m['nom_med']);
            if (str_contains($cleanM, $cleanInput) || str_contains($cleanInput, $cleanM)) {
                return [
                    'medico' => $m['nom_med'],
                    'cm' => $m['cod_med'],
                    'prof' => $m['especialidad'] ?: 'MÉDICO GENERAL',
                    'jornada' => $m['jornada'] ?: 'MATUTINA',
                ];
            }
        }

        return [
            'medico' => $medicoNorm,
            'cm' => '',
            'prof' => 'MÉDICO GENERAL',
            'jornada' => 'MATUTINA',
        ];
    }

    public static function normalizarClaveMedico(?string $texto): string
    {
        if (empty($texto)) return '';
        $str = mb_strtoupper(trim($texto), 'UTF-8');
        $str = strtr($str, [
            'Á'=>'A','É'=>'E','Í'=>'I','Ó'=>'O','Ú'=>'U','Ñ'=>'N','Ü'=>'U'
        ]);
        $str = preg_replace('/^(DR\.|DRA\.|MSS\.|G\.O\.\s*DRA\.|LIC\.|LICDA\.)\s*/i', '', $str);
        $str = preg_replace('/[^A-Z0-9\s]/', ' ', $str);
        return trim(preg_replace('/\s+/', ' ', $str));
    }

    /**
     * Generar Hash Criptográfico Único de Atención
     */
    protected function generarHashAtencion(string $dniLimpio, ?string $fechaIso, string $medicoNorm, string $expediente, array $diagnosticos): string
    {
        $codigosDx = [];
        foreach ($diagnosticos as $d) {
            $codigosDx[] = $d['codigo'] ?: $d['original'];
        }
        $dxStr = implode(',', $codigosDx);

        $rawFingerprint = "{$dniLimpio}|{$fechaIso}|{$medicoNorm}|{$expediente}|{$dxStr}";

        return hash('sha256', $rawFingerprint);
    }

    /**
     * Calcular semana epidemiológica estándar (Domingo a Sábado, primera semana de enero inicia SE 1)
     */
    public static function calcularSemanaEpidemiologica(Carbon $date): int
    {
        $year = $date->year;
        $jan1 = Carbon::create($year, 1, 1, 12, 0, 0);
        $dayOfJan1 = $jan1->dayOfWeek; // 0=Domingo, 1=Lunes... 6=Sábado

        $firstSunday = ($dayOfJan1 === 0) ? $jan1 : $jan1->copy()->addDays(7 - $dayOfJan1);
        $d = $date->copy()->setTime(12, 0, 0);

        if ($d->lt($firstSunday)) {
            return 53;
        }

        $diffInDays = $firstSunday->diffInDays($d);
        return (int)floor($diffInDays / 7) + 1;
    }

    /**
     * Calcular Rangos 1 a 5 según edad y tipo con la nomenclatura oficial del sistema
     */
    public static function calcularRangosEpidemiologicos($edad, $tipo): array
    {
        $edadNum = is_numeric($edad) ? (float)$edad : null;
        $t = mb_strtoupper(trim((string)$tipo), 'UTF-8');

        if ($edadNum === null || empty($t)) {
            return [
                'rango' => '',
                'rango_2' => '',
                'rango_3' => '',
                'rango_4' => '',
                'rango_5' => '',
            ];
        }

        // Convertir edad a años (idéntico a create.blade.php)
        $edadEnAnios = $edadNum;
        if ($t === 'M') {
            $edadEnAnios = $edadNum / 12.0;
        } elseif ($t === 'D') {
            $edadEnAnios = $edadNum / 365.0;
        }

        // Rango 1
        $idx1 = 8;
        if ($edadEnAnios < 0.08) $idx1 = 0;
        elseif ($edadEnAnios < 1.0) $idx1 = 1;
        elseif ($edadEnAnios < 5.0) $idx1 = 2;
        elseif ($edadEnAnios < 10.0) $idx1 = 3;
        elseif ($edadEnAnios < 15.0) $idx1 = 4;
        elseif ($edadEnAnios < 20.0) $idx1 = 5;
        elseif ($edadEnAnios < 50.0) $idx1 = 6;
        elseif ($edadEnAnios < 60.0) $idx1 = 7;

        // Rango 2
        $idx2 = 9;
        if ($edadEnAnios < 0.08) $idx2 = 0;
        elseif ($edadEnAnios < 0.16) $idx2 = 1;
        elseif ($edadEnAnios < 1.0) $idx2 = 2;
        elseif ($edadEnAnios < 5.0) $idx2 = 3;
        elseif ($edadEnAnios < 10.0) $idx2 = 4;
        elseif ($edadEnAnios < 15.0) $idx2 = 5;
        elseif ($edadEnAnios < 20.0) $idx2 = 6;
        elseif ($edadEnAnios < 50.0) $idx2 = 7;
        elseif ($edadEnAnios < 60.0) $idx2 = 8;

        // Rango 3
        $idx3 = 8;
        if ($edadEnAnios < 1.0) $idx3 = 0;
        elseif ($edadEnAnios < 5.0) $idx3 = 1;
        elseif ($edadEnAnios < 10.0) $idx3 = 2;
        elseif ($edadEnAnios < 15.0) $idx3 = 3;
        elseif ($edadEnAnios < 20.0) $idx3 = 4;
        elseif ($edadEnAnios < 25.0) $idx3 = 5;
        elseif ($edadEnAnios < 40.0) $idx3 = 6;
        elseif ($edadEnAnios < 60.0) $idx3 = 7;

        // Rango 4
        $idx4 = 8;
        if ($edadEnAnios < 1.0) $idx4 = 0;
        elseif ($edadEnAnios < 5.0) $idx4 = 1;
        elseif ($edadEnAnios < 10.0) $idx4 = 2;
        elseif ($edadEnAnios < 15.0) $idx4 = 3;
        elseif ($edadEnAnios < 20.0) $idx4 = 4;
        elseif ($edadEnAnios < 25.0) $idx4 = 5;
        elseif ($edadEnAnios < 30.0) $idx4 = 6;
        elseif ($edadEnAnios < 50.0) $idx4 = 7;

        // Rango 5
        $idx5 = 2;
        if ($edadEnAnios < 5.0) $idx5 = 0;
        elseif ($edadEnAnios < 15.0) $idx5 = 1;

        $r1 = [
            '1. MENOR DE 1 MES',
            '2. DE 1 MES A 1 AÑO',
            '3. DE 1 A 4 AÑOS',
            '4. DE 5 A 9 AÑOS',
            '5. DE 10 A 14 AÑOS',
            '6. DE 15 A 19 AÑOS',
            '7. DE 20 A 49 AÑOS',
            '8. DE 50 A 59 AÑOS',
            '9. MAYORES DE 60 AÑOS'
        ];

        $r2 = [
            'MENOR DE 1 MES',
            'DE 1 A 2 MESES',
            'DE 2 MES A 1 AÑO',
            'DE 1 A 4 AÑOS',
            'DE 5 A 9 AÑOS',
            'DE 10 A 14 AÑOS',
            'DE 15 A 19 AÑOS',
            'DE 20 A 49 AÑOS',
            'DE 50 A 59 AÑOS',
            'MAYORES DE 60 AÑOS'
        ];

        $r3 = [
            'MENOR 1 AÑO',
            '1 - 4 AÑOS',
            '5 A 9 AÑOS',
            '10 A 14 AÑOS',
            '15 A 19 AÑOS',
            '20 A 24 AÑOS',
            '25 A 39 AÑOS',
            '40 A 59 AÑOS',
            '60 Y MAS'
        ];

        $r4 = [
            'MENOR 1 AÑO',
            '1 - 4 AÑOS',
            '5 A 9 AÑOS',
            '10 A 14 AÑOS',
            '15 A 19 AÑOS',
            '20 A 24 AÑOS',
            '25 A 29 AÑOS',
            '30 A 49 AÑOS',
            '50 Y +'
        ];

        $r5 = [
            'MENORES DE 5 AÑOS',
            'DE 5 A 14 AÑOS',
            'MAYORES DE 15 AÑOS'
        ];

        return [
            'rango' => $r1[$idx1] ?? '',
            'rango_2' => $r2[$idx2] ?? '',
            'rango_3' => $r3[$idx3] ?? '',
            'rango_4' => $r4[$idx4] ?? '',
            'rango_5' => $r5[$idx5] ?? '',
        ];
    }

    /**
     * Detectar fila principal de encabezados, subencabezados combinados y fila de inicio de datos
     */
    protected function detectarEstructuraEncabezados($sheet, string $highestColumn, int $highestRow): array
    {
        $headerRowIdx = 1;
        $maxScore = 0;

        $conceptosClave = [
            'identidad' => ['identidad', 'dni', 'cedula', 'documento', 'noidentidad', 'numeroidentidad'],
            'fecha' => ['fechadeatencion', 'fechaatencion', 'fecha', 'fechaconsulta', 'date'],
            'medico' => ['medico', 'doctor', 'profesional', 'atendidopor'],
            'paciente' => ['nombresyapellidos', 'nombrepaciente', 'paciente', 'nombres', 'nombre'],
            'historia' => ['historia', 'expediente', 'hc', 'numerodehistoriaclinica', 'no'],
            'diagnostico' => ['diagnostico', 'actividad', 'patologia', 'cie10'],
            'edad' => ['edad', 'anios', 'anos', 'age'],
            'sexo' => ['sexo', 'genero', 'gender'],
            'direccion' => ['procedencia', 'direccion', 'localidad', 'domicilio', 'colonia'],
        ];

        for ($r = 1; $r <= min(25, $highestRow); $r++) {
            $row = $sheet->rangeToArray("A{$r}:{$highestColumn}{$r}", null, true, false)[0];
            
            // Ignorar si todos los valores no nulos son idénticos (ej. título repetido en celdas combinadas)
            $nonEmpty = array_filter($row, fn($v) => $v !== null && trim((string)$v) !== '');
            if (count(array_unique($nonEmpty)) <= 1 && count($nonEmpty) > 1) {
                continue;
            }

            $matched = [];
            foreach ($row as $val) {
                if ($val !== null && trim((string)$val) !== '') {
                    $clean = mb_strtolower(trim((string)$val), 'UTF-8');
                    $clean = str_replace(['á', 'é', 'í', 'ó', 'ú', 'ñ'], ['a', 'e', 'i', 'o', 'u', 'n'], $clean);
                    $clean = preg_replace('/[^a-z0-9]/', '', $clean);

                    foreach ($conceptosClave as $concept => $synonyms) {
                        if (isset($matched[$concept])) continue;
                        foreach ($synonyms as $syn) {
                            if (str_contains($clean, $syn)) {
                                $matched[$concept] = true;
                                break;
                            }
                        }
                    }
                }
            }

            $score = count($matched);
            if ($score >= 3 && $score > $maxScore) {
                $maxScore = $score;
                $headerRowIdx = $r;
            }
        }

        // Combinar encabezados principales y subencabezados (hasta que empiecen los datos)
        $combinedHeaders = [];
        $dataStartRow = $headerRowIdx + 1;

        for ($r = $headerRowIdx; $r <= min($headerRowIdx + 5, $highestRow); $r++) {
            $row = $sheet->rangeToArray("A{$r}:{$highestColumn}{$r}", null, true, false)[0];

            // Si detectamos que es una fila de datos reales (número 1, 2 en primeras columnas)
            $c0 = isset($row[0]) ? trim((string)$row[0]) : '';
            $c1 = isset($row[1]) ? trim((string)$row[1]) : '';
            if ($r > $headerRowIdx && ((is_numeric($c0) && (int)$c0 >= 1 && (int)$c0 <= 5) || (is_numeric($c1) && (int)$c1 >= 1 && (int)$c1 <= 5))) {
                $dataStartRow = $r;
                break;
            }

            foreach ($row as $colIdx => $val) {
                if ($val !== null && trim((string)$val) !== '') {
                    $v = trim((string)$val);
                    if (!isset($combinedHeaders[$colIdx])) {
                        $combinedHeaders[$colIdx] = $v;
                    } else {
                        $combinedHeaders[$colIdx] .= ' ' . $v;
                    }
                }
            }
        }

        $columnMap = $this->detectarColumnas($combinedHeaders);

        // Si diagnósticos 1, 2, 3 no fueron detectados por nombre completo, detectarlos por posición de subencabezados
        if (!isset($columnMap['diagnostico_1'])) {
            foreach ($combinedHeaders as $colIdx => $hText) {
                if (preg_match('/\bdiagnostico\b/i', $hText) || preg_match('/\b1\b/', $hText)) {
                    if ($colIdx >= 15 && !isset($columnMap['diagnostico_1'])) {
                        $columnMap['diagnostico_1'] = $colIdx;
                        $columnMap['cond_1'] = $colIdx + 1;
                        if (isset($combinedHeaders[$colIdx + 2])) {
                            $columnMap['diagnostico_2'] = $colIdx + 2;
                            $columnMap['cond_2'] = $colIdx + 3;
                        }
                        if (isset($combinedHeaders[$colIdx + 4])) {
                            $columnMap['diagnostico_3'] = $colIdx + 4;
                            $columnMap['cond_3'] = $colIdx + 5;
                        }
                        break;
                    }
                }
            }
        }

        // Si localidad / dirección no fue detectada
        if (!isset($columnMap['direccion'])) {
            foreach ($combinedHeaders as $colIdx => $hText) {
                if (preg_match('/localidad|domicilio|residencia|procedencia/i', $hText)) {
                    $columnMap['direccion'] = $colIdx;
                    break;
                }
            }
        }

        return [$columnMap, $dataStartRow];
    }
}
