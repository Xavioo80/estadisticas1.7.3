<?php

namespace App\Http\Controllers;

use App\Models\Paciente;
use App\Models\NotificacionSvs;
use App\Models\Informe;
use Illuminate\Http\Request;

class PacienteController extends Controller
{
    /**
     * Helper: normalizar un valor "vacío" a NULL.
     */
    private function valorVacioANull($v) {
        if ($v === null) return null;
        $s = is_string($v) ? trim($v) : (string)$v;
        if ($s === '' || $s === '-' || $s === '_' || strtoupper($s) === 'N/A' || strtoupper($s) === 'NULL' || strtoupper($s) === 'SN') return null;
        return $s;
    }

    /**
     * Helper: determinar si un valor se considera "tiene dato".
     */
    private function tieneDato($v) {
        return $this->valorVacioANull($v) !== null;
    }

    /**
     * Helper: fallback para completar colonia, depto, municipio, teléfono, etc.
     * Búsqueda por: DNI formateado / limpio o número de expediente.
     * Fuentes: notificaciones_svs, informes, registros_globales (si existen)
     *
     * Devuelve array asociativo con campos encontrados.
     */
    private function datosFallback($dniLimpio, $dniFmt = null, $expediente = null) {
        $out = [
            'colonia' => null,
            'direccion' => null,
            'departamento' => null,
            'municipio' => null,
            'cod_municipio' => null,
            'telefono' => null,
            'fecha_nacimiento' => null,
            'nombres' => null,
            'apellidos' => null,
            'nombre_completo' => null,
            'expediente' => null,
            '_fuente' => []
        ];

        $dniLimpio = $this->valorVacioANull($dniLimpio);
        $dniFmt = $this->valorVacioANull($dniFmt);
        $exp = $this->valorVacioANull($expediente);

        // ── 1) NotificacionesSvs ──────────────────────────────────
        try {
            $q = NotificacionSvs::query();
            if ($dniLimpio || $dniFmt) {
                $q->where(function ($qq) use ($dniLimpio, $dniFmt) {
                    if ($dniLimpio) $qq->orWhere('no_documento', $dniLimpio)->orWhere('no_documento', $dniFmt);
                    if ($dniFmt)   $qq->orWhere('no_documento', $dniFmt);
                });
            }
            if ($exp) {
                $q->orWhere(function ($qq) use ($exp) {
                    $qq->where('expediente', $exp);
                });
            }
            $svs = $q->orderBy('id', 'desc')->limit(3)->get();

            foreach ($svs as $r) {
                foreach (['colonia','direccion','departamento','municipio','telefono','fecha_nacimiento','nombres','apellidos','expediente'] as $k) {
                    if (!$out[$k] && $this->tieneDato($r->$k ?? null)) {
                        $out[$k] = $r->$k;
                        if (!in_array('notificaciones_svs', $out['_fuente'])) $out['_fuente'][] = 'notificaciones_svs';
                    }
                }
                if (!$out['cod_municipio'] && !empty($r->municipio)) {
                    $cm = $this->inferirCodMunicipio($r->departamento, $r->municipio);
                    if ($cm) { $out['cod_municipio'] = $cm; }
                }
            }
        } catch (\Throwable $e) { \Log::warning("fallback notificaciones_svs: ".$e->getMessage()); }

        // Si notificaciones_svs nos dio un expediente, usarlo para informes
        if (!$exp && !empty($out['expediente'])) {
            $exp = $out['expediente'];
        }

        // ── 2) Informes ───────────────────────────────────────────
        try {
            $qInf = Informe::query();
            // No hay DNI directo, buscamos por expediente (exp)
            if ($exp) {
                $qInf->orWhere('exp', $exp);
            }
            // También podríamos relacionar paciente->id con informe, pero no hay FK directa.
            // Si tenemos nombres, podemos filtrar (opcional, débil): omitimos para no pegar a nombres.

            $infs = $qInf->orderBy('ano', 'desc')->orderBy('fecha', 'desc')->limit(3)->get();
            foreach ($infs as $inf) {
                if (!$out['colonia'] && $this->tieneDato($inf->colonia)) {
                    $out['colonia'] = $inf->colonia;
                    if (!in_array('informes', $out['_fuente'])) $out['_fuente'][] = 'informes';
                }
                if (!$out['expediente'] && $this->tieneDato($inf->exp)) {
                    $out['expediente'] = $inf->exp;
                    if (!in_array('informes', $out['_fuente'])) $out['_fuente'][] = 'informes';
                }
                // cod_col podría usarse como colonia, si no hay string
                if (!$out['colonia'] && $this->tieneDato($inf->cod_col ?? null)) {
                    $out['colonia'] = $inf->cod_col;
                }
            }
        } catch (\Throwable $e) { \Log::warning("fallback informes: ".$e->getMessage()); }

        // ── 3) registros_globales (si existe la tabla) ────────────
        try {
            if (\Illuminate\Support\Facades\Schema::hasTable('registros_globales')) {
                $rg = null;
                $colDni = null;
                $cols = \Illuminate\Support\Facades\Schema::getColumnListing('registros_globales');
                $candidatos = ['identidad','dni','no_identidad','numero_identidad'];
                foreach ($candidatos as $c) if (in_array($c, $cols)) { $colDni = $c; break; }
                if ($colDni && ($dniLimpio || $dniFmt)) {
                    $rg = \Illuminate\Support\Facades\DB::table('registros_globales')
                        ->where(function ($q) use ($colDni, $dniLimpio, $dniFmt) {
                            if ($dniLimpio) $q->orWhere($colDni, $dniLimpio);
                            if ($dniFmt)   $q->orWhere($colDni, $dniFmt);
                        })
                        ->orderBy('id', 'desc')->first();
                }
                if (!$rg && $exp && in_array('exp', $cols)) {
                    $rg = \Illuminate\Support\Facades\DB::table('registros_globales')
                        ->where('exp', $exp)->orderBy('id','desc')->first();
                }
                if ($rg) {
                    $arr = (array)$rg;
                    if (!$out['colonia'] && $this->tieneDato($arr['colonia'] ?? null)) $out['colonia'] = $arr['colonia'];
                    if (!$out['departamento'] && $this->tieneDato($arr['departamento'] ?? null)) $out['departamento'] = $arr['departamento'];
                    if (!$out['municipio'] && $this->tieneDato($arr['municipio'] ?? null)) $out['municipio'] = $arr['municipio'];
                    if (!$out['telefono'] && $this->tieneDato($arr['telefono'] ?? null)) $out['telefono'] = $arr['telefono'];
                    if (!$out['fecha_nacimiento'] && $this->tieneDato($arr['fecha_nacimiento'] ?? null)) $out['fecha_nacimiento'] = $arr['fecha_nacimiento'];
                    if (!$out['nombres'] && $this->tieneDato($arr['nombres'] ?? null)) $out['nombres'] = $arr['nombres'];
                    if (!$out['apellidos'] && $this->tieneDato($arr['apellidos'] ?? null)) $out['apellidos'] = $arr['apellidos'];
                    if (!$out['direccion'] && $this->tieneDato($arr['direccion'] ?? null)) $out['direccion'] = $arr['direccion'];
                    if (!in_array('registros_globales', $out['_fuente'])) $out['_fuente'][] = 'registros_globales';

                    if (!$out['cod_municipio'] && !empty($arr['municipio'] ?? null)) {
                        $cm = $this->inferirCodMunicipio($arr['departamento'] ?? null, $arr['municipio'] ?? null);
                        if ($cm) $out['cod_municipio'] = $cm;
                    }
                }
            }
        } catch (\Throwable $e) { \Log::warning("fallback registros_globales: ".$e->getMessage()); }

        // Nombre completo compuesto
        if (!$out['nombre_completo']) {
            $n = trim(($out['nombres'] ?? '') . ' ' . ($out['apellidos'] ?? ''));
            if ($n) $out['nombre_completo'] = $n;
        }

        return $out;
    }

    /**
     * Intentar inferir cod_municipio según depto + municipio (lookup básico HN).
     * Retorna string o null.
     */
    private function inferirCodMunicipio($depto, $mpio) {
        $d  = mb_strtoupper(trim((string)$depto));
        $m  = mb_strtoupper(trim((string)$mpio));
        if ($d === '' && $m === '') return null;

        $map = [
            'FRANCISCO MORAZAN___DISTRO CENTRAL'   => '0801',
            'FRANCISCO MORAZAN___DISTRITO CENTRAL' => '0801',
            'FRANCISCO MORAZAN___D.C.'   => '0801',
            'FRANCISCO MORAZAN___DC'     => '0801',
            'CORTES___SAN PEDRO SULA'    => '1201',
            'CORTES___SPS'               => '1201',
            'ATLANTIDA___LA CEIBA'       => '0101',
            'COMAYAGUA___COMAYAGUA'      => '0301',
            'CHOLUTECA___CHOLUTECA'      => '0501',
            'EL PARAISO___YUSCARAN'      => '0701',
            'YORO___YORO'                => '1701',
            'INTIBUCA___LA ESPERANZA'    => '0901',
            'VALLE___NAZARETH'           => '1501',
            'VALLE___SAN CARLOS'         => '1501',
            'OCOTEPEQUE___OCOTEPEQUE'    => '1101',
            'LEMPIRA___GRACIAS'          => '1001',
            'OCCIDENTE___SANTA BARBARA'  => '1401',
            'SANTA BARBARA___SANTA BARBARA' => '1401',
            'ISLAS DE LA BAHIA___ROATAN' => '1801',
            'BAY___ROATAN'               => '1801',
            'GRACIAS A DIOS___PUERTO LEMPIRA' => '0601',
            'GRACIAS A DIOS___LEMPIRA'   => '0601',
            'COLON___TRUJILLO'           => '0401',
        ];
        $k = $d . '___' . $m;
        return $map[$k] ?? null;
    }

    /**
     * Combina (merge) datos entrantes (SNVS/SESAL) con datos locales y fallback.
     * Reglas:
     *   - No sobreescribir con vacíos (si el dato nuevo es null / vacío / '-', mantener local si existe)
     *   - Si ambos tienen dato: preferencia por el nuevo (SNVS/SESAL), excepto si se pide lo contrario vía $camposSeleccionados
     *   - Si ni local ni SNVS tienen dato, usar fallback (colonia, depto, etc.)
     *
     * @param Paciente|null $local   Registro local (puede ser null si es nuevo)
     * @param array $snvs            Datos crudos que vienen de SNVS/SESAL (ya parseados)
     * @param array $fallback        Datos adicionales de notificaciones/informes
     * @param array|null $camposSeleccionados Campos que SÍ se deben sobreescribir con SNVS/fallback (null = todo)
     * @return array updateData listo para fill/save
     */
    private function combinarDatosPaciente($local, $snvs, $fallback, $camposSeleccionados = null) {
        $esNuevo = !($local instanceof Paciente);

        // Campos que manejamos:
        $campos = [
            'nombre_completo','fecha_nacimiento','edad','colonia','telefono',
            'departamento','municipio','cod_municipio'
        ];

        $out = [];

        foreach ($campos as $campo) {
            // Qué hacer si se especifican campos seleccionados
            $sobrescribir = ($camposSeleccionados === null) || in_array($campo, $camposSeleccionados);

            // Extraer valor SNVS normalizado
            $vSnvs = null;
            switch ($campo) {
                case 'nombre_completo':
                    $vSnvs = $this->valorVacioANull($snvs['nombre_completo'] ?? null);
                    if (!$vSnvs) {
                        $n = trim(($snvs['nombres'] ?? '') . ' ' . ($snvs['apellidos'] ?? ''));
                        $vSnvs = $this->valorVacioANull($n);
                    }
                    if ($vSnvs && function_exists('mb_strtoupper')) $vSnvs = mb_strtoupper($vSnvs);
                    break;
                case 'fecha_nacimiento':
                    $vSnvs = $this->valorVacioANull($snvs['fecha_nacimiento'] ?? null);
                    break;
                case 'edad':
                    $vSnvs = null;
                    if (isset($snvs['fecha_nacimiento']) && $this->tieneDato($snvs['fecha_nacimiento'])) {
                        try {
                            $vSnvs = \Carbon\Carbon::createFromFormat('d/m/Y', $snvs['fecha_nacimiento'])->age;
                        } catch (\Exception $e) {
                            try {
                                $c = \Carbon\Carbon::parse($snvs['fecha_nacimiento']);
                                if ($c->year > 1900 && $c->year <= now()->year) $vSnvs = $c->age;
                            } catch (\Exception $e2) {}
                        }
                    }
                    if ($vSnvs === null && is_numeric($snvs['edad'] ?? null)) {
                        $vSnvs = (int)$snvs['edad'];
                    }
                    break;
                case 'colonia':
                    $vSnvs = $this->valorVacioANull($snvs['colonia'] ?? null);
                    if (!$vSnvs) $vSnvs = $this->valorVacioANull($snvs['direccion'] ?? null);
                    if ($vSnvs && function_exists('mb_strtoupper')) $vSnvs = mb_strtoupper($vSnvs);
                    break;
                case 'telefono':
                    $vSnvs = $this->valorVacioANull($snvs['telefono'] ?? null);
                    break;
                case 'departamento':
                    $vSnvs = $this->valorVacioANull($snvs['departamento'] ?? null);
                    break;
                case 'municipio':
                    $vSnvs = $this->valorVacioANull($snvs['municipio'] ?? null);
                    break;
                case 'cod_municipio':
                    $vSnvs = $this->valorVacioANull($snvs['cod_municipio'] ?? null);
                    break;
            }

            // Valor local (normalizado)
            $vLocal = null;
            if (!$esNuevo) {
                if ($campo === 'edad') {
                    $vLocal = $local->edad !== null && $local->edad !== '' ? (int)$local->edad : null;
                } else {
                    $vLocal = $this->valorVacioANull($local->$campo ?? null);
                }
            }

            // Fallback
            $vFallback = null;
            switch ($campo) {
                case 'colonia':
                    $vFallback = $this->valorVacioANull($fallback['colonia'] ?? null);
                    if (!$vFallback) $vFallback = $this->valorVacioANull($fallback['direccion'] ?? null);
                    if ($vFallback && function_exists('mb_strtoupper')) $vFallback = mb_strtoupper($vFallback);
                    break;
                case 'nombre_completo':
                    $vFallback = $this->valorVacioANull($fallback['nombre_completo'] ?? null);
                    if ($vFallback && function_exists('mb_strtoupper')) $vFallback = mb_strtoupper($vFallback);
                    break;
                case 'fecha_nacimiento':
                    $vFallback = $this->valorVacioANull($fallback['fecha_nacimiento'] ?? null);
                    // Intentar normalizar formato si es fecha
                    if ($vFallback) {
                        try {
                            $c = \Carbon\Carbon::parse($vFallback);
                            if ($c->year > 1900 && $c->year <= now()->year) $vFallback = $c->format('d/m/Y');
                        } catch (\Throwable $e) {}
                    }
                    break;
                case 'edad':
                    if (!empty($fallback['fecha_nacimiento'])) {
                        try {
                            $vFallback = \Carbon\Carbon::createFromFormat('d/m/Y', $fallback['fecha_nacimiento'])->age;
                        } catch (\Exception $e) {
                            try {
                                $c = \Carbon\Carbon::parse($fallback['fecha_nacimiento']);
                                if ($c->year > 1900 && $c->year <= now()->year) $vFallback = $c->age;
                            } catch (\Exception $e2) {}
                        }
                    }
                    break;
                case 'telefono':
                case 'departamento':
                case 'municipio':
                case 'cod_municipio':
                    $vFallback = $this->valorVacioANull($fallback[$campo] ?? null);
                    break;
            }

            // Decision final por campo
            $valorFinal = null;

            if ($esNuevo) {
                // Si es paciente NUEVO:
                //   1) SNVS si tiene dato, 2) si no: Fallback, 3) si no: null
                if ($sobrescribir) {
                    $valorFinal = $vSnvs ?? $vFallback ?? null;
                } else {
                    // Si no se seleccionó el campo pero es nuevo, igual tomamos algo si existe
                    $valorFinal = $vSnvs ?? $vFallback ?? null;
                }
            } else {
                // Paciente EXISTENTE:
                // Si NO está seleccionado para sobrescribir → MANTENER local (no lo agregamos a $out)
                if (!$sobrescribir) {
                    // No hacemos nada → se conserva el actual
                    continue;
                }
                // Regla combinación: no sobreescribir local NO vacío con SNVS vacío.
                // Pero si SNVS o FALLBACK tiene dato → usarlo (prioridad SNVS > fallback > mantener local)
                if ($vSnvs !== null) {
                    $valorFinal = $vSnvs;
                } elseif ($vFallback !== null) {
                    $valorFinal = $vFallback;
                } else {
                    // Ambos vacíos → NO sobreescribir (dejar local como está)
                    continue;
                }
            }

            // Para evitar escribir exactamente lo mismo que ya existe (opcional):
            if (!$esNuevo && $campo !== 'edad' && $vLocal !== null && $valorFinal !== null) {
                $vl = (string)$vLocal;
                $vf = (string)$valorFinal;
                if ($campo === 'telefono') {
                    $vl = preg_replace('/\D/', '', $vl);
                    $vf = preg_replace('/\D/', '', $vf);
                }
                if ($vl === $vf) continue;
            }
            if (!$esNuevo && $campo === 'edad' && $vLocal !== null && $valorFinal !== null && (int)$vLocal === (int)$valorFinal) {
                continue;
            }

            $out[$campo] = $valorFinal;
        }

        return $out;
    }

    public function index(Request $request)
    {
        Paciente::ensureTableExists();

        $search = trim($request->input('search', ''));

        $query = Paciente::query();

        if (!empty($search)) {
            $cleanSearch = preg_replace('/\D/', '', $search);
            $query->where(function ($q) use ($search, $cleanSearch) {
                $q->where('nombre_completo', 'LIKE', "%{$search}%")
                  ->orWhere('dni', 'LIKE', "%{$search}%")
                  ->orWhere('colonia', 'LIKE', "%{$search}%");

                if (!empty($cleanSearch)) {
                    $q->orWhere('dni_limpio', 'LIKE', "%{$cleanSearch}%");
                }
            });
        }

        $totalPacientes = Paciente::count();
        $pacientes = $query->orderBy('pacientes.id', 'desc')->paginate(50)->appends(['search' => $search]);

        if ($request->ajax()) {
            return view('pacientes.partials.table', compact('pacientes', 'totalPacientes', 'search'));
        }

        return view('pacientes.index', compact('pacientes', 'totalPacientes', 'search'));
    }

    /**
     * Actualización inline de campos permitidos (teléfono, colonia) vía AJAX
     */
    public function updateField(Request $request, $id)
    {
        // Sólo permitimos editar estos campos de forma segura
        $camposPermitidos = ['telefono', 'colonia'];
        $campo = $request->input('field');
        $valor = trim($request->input('value', ''));

        if (!in_array($campo, $camposPermitidos)) {
            return response()->json(['success' => false, 'message' => 'Campo no permitido.'], 422);
        }

        $paciente = Paciente::find($id);
        if (!$paciente) {
            return response()->json(['success' => false, 'message' => 'Paciente no encontrado.'], 404);
        }

        // Normalizar vacíos y guiones
        $paciente->$campo = ($valor === '' || $valor === '-') ? null : strtoupper($valor);
        $paciente->save();

        return response()->json([
            'success' => true,
            'field'   => $campo,
            'value'   => $paciente->$campo,
        ]);
    }

    /**
     * Re-consulta SESAL/SNVS para un paciente y actualiza la BD local.
     * Ahora usa: combinación inteligente + fallback por expediente/DNI.
     * Acepta `campos[]` para seleccionar QUÉ campos sobrescribir (opcional).
     */
    public function resync(Request $request, $id)
    {
        $paciente = Paciente::find($id);
        if (!$paciente) {
            return response()->json(['success' => false, 'message' => 'Paciente no encontrado.'], 404);
        }

        $dniLimpio  = $paciente->dni_limpio;
        $dniFmt     = $paciente->dni;

        \Illuminate\Support\Facades\Cache::forget("paciente_realtime_{$dniLimpio}");
        \Illuminate\Support\Facades\Cache::forget("paciente_realtime_{$dniFmt}");

        $datos = null;
        try {
            $reqSesal = new \Illuminate\Http\Request(['identidad' => $dniLimpio]);
            $pruebaCtrl = app(\App\Http\Controllers\PruebaConsultaController::class);
            $res = $pruebaCtrl->buscar($reqSesal);

            if ($res && method_exists($res, 'getData')) {
                $json = $res->getData(true);
                $dataObj = isset($json['data']) && is_array($json['data']) ? $json['data'] : $json;

                if (!empty($dataObj['nombre_completo']) || !empty($dataObj['nombres'])) {
                    $datos = $dataObj;
                }
            }
        } catch (\Throwable $e) {
            \Log::warning("resync SESAL error para {$dniLimpio}: " . $e->getMessage());
        }

        if (!$datos) {
            return response()->json(['success' => false, 'message' => 'No se encontraron datos en SESAL/SNVS para este paciente.'], 422);
        }

        // Fallback: buscar expediente / colonia / etc. en tablas locales
        // Tomar posible expediente ya sea de datos SNVS o de paciente
        $exp = $this->valorVacioANull($datos['expediente'] ?? $paciente->expediente ?? null);
        $fallback = $this->datosFallback($dniLimpio, $dniFmt, $exp);

        $modo = $request->input('modo', 'confirmar');

        if ($modo === 'preview') {
            $previewMerge = $this->combinarDatosPaciente($paciente, $datos, $fallback, null);
            $previewData = [
                'dni'              => $dniFmt,
                'dni_limpio'       => $dniLimpio,
                'nombre_completo'  => $previewMerge['nombre_completo']
                    ?? $this->valorVacioANull($datos['nombre_completo'] ?? null)
                    ?? trim(($datos['nombres'] ?? '') . ' ' . ($datos['apellidos'] ?? '')) ?: null,
                'fecha_nacimiento' => $previewMerge['fecha_nacimiento'] ?? $this->valorVacioANull($datos['fecha_nacimiento'] ?? null),
                'edad'             => $previewMerge['edad'] ?? null,
                'colonia'          => $previewMerge['colonia']
                    ?? $this->valorVacioANull($datos['colonia'] ?? $datos['direccion'] ?? null)
                    ?? $this->valorVacioANull($fallback['colonia'] ?? $fallback['direccion'] ?? null),
                'telefono'         => $previewMerge['telefono']
                    ?? $this->valorVacioANull($datos['telefono'] ?? null)
                    ?? $this->valorVacioANull($fallback['telefono'] ?? null),
                'departamento'     => $previewMerge['departamento']
                    ?? $this->valorVacioANull($datos['departamento'] ?? null)
                    ?? $this->valorVacioANull($fallback['departamento'] ?? null),
                'municipio'        => $previewMerge['municipio']
                    ?? $this->valorVacioANull($datos['municipio'] ?? null)
                    ?? $this->valorVacioANull($fallback['municipio'] ?? null),
                'cod_municipio'    => $previewMerge['cod_municipio']
                    ?? $this->valorVacioANull($datos['cod_municipio'] ?? null)
                    ?? $this->valorVacioANull($fallback['cod_municipio'] ?? null),
            ];

            if (!empty($fallback['expediente'])) {
                $previewData['expediente'] = $fallback['expediente'];
            } elseif (!empty($datos['expediente'])) {
                $previewData['expediente'] = $datos['expediente'];
            }

            return response()->json([
                'success' => true,
                'modo'    => 'preview',
                'es_nuevo'=> false,
                'paciente_id' => $paciente->id,
                'message' => 'Paciente YA registrado localmente. Revisa y selecciona qué campos actualizar.',
                'fallback_fuentes' => $fallback['_fuente'] ?? [],
                'preview_data' => $previewData,
                'existente' => [
                    'id'               => $paciente->id,
                    'nombre_completo'  => $paciente->nombre_completo,
                    'dni'              => $paciente->dni,
                    'dni_limpio'       => $paciente->dni_limpio,
                    'fecha_nacimiento' => $paciente->fecha_nacimiento,
                    'edad'             => $paciente->edad,
                    'colonia'          => $paciente->colonia,
                    'telefono'         => $paciente->telefono,
                    'departamento'     => $paciente->departamento,
                    'municipio'        => $paciente->municipio,
                    'cod_municipio'    => $paciente->cod_municipio,
                ],
            ]);
        }

        // Campos seleccionados (si vienen)
        $camposSeleccionados = $request->input('campos');
        if (is_string($camposSeleccionados)) $camposSeleccionados = [$camposSeleccionados];
        if (is_array($camposSeleccionados)) {
            $camposSeleccionados = array_values(array_filter(array_map('strval', $camposSeleccionados)));
            if (empty($camposSeleccionados)) $camposSeleccionados = null;
        } else {
            $camposSeleccionados = null;
        }

        $updateData = $this->combinarDatosPaciente($paciente, $datos, $fallback, $camposSeleccionados);

        if (!empty($updateData)) {
            $paciente->fill($updateData)->save();
        }

        $fresh = $paciente->fresh();
        return response()->json([
            'success'   => true,
            'message'   => empty($updateData)
                ? 'No había cambios para aplicar (datos idénticos o sin datos nuevos).'
                : 'Paciente actualizado desde SESAL.',
            'cambios_aplicados' => $updateData,
            'fallback_fuentes'  => $fallback['_fuente'] ?? [],
            'origen'    => $datos['origen'] ?? 'sesal',
            'paciente'  => [
                'id'               => $fresh->id,
                'nombre_completo'  => $fresh->nombre_completo,
                'fecha_nacimiento' => $fresh->fecha_nacimiento,
                'edad'             => $fresh->edad,
                'colonia'          => $fresh->colonia,
                'telefono'         => $fresh->telefono,
                'departamento'     => $fresh->departamento,
                'municipio'        => $fresh->municipio,
                'cod_municipio'    => $fresh->cod_municipio,
            ],
        ]);
    }

    /**
     * Busca un paciente por DNI en SESAL/SNVS.
     * Dos modos:
     *   - preview (default): SOLO devuelve los datos encontrados + si ya existe localmente. NO guarda nada.
     *   - confirmar: Realmente guarda/actualiza el registro en la BD local (respetando campos seleccionados).
     */
    public function buscarYAgregar(Request $request)
    {
        $dniInput = trim($request->input('dni', ''));
        $modo     = $request->input('modo', 'preview');
        $dniLimpio = preg_replace('/\D/', '', $dniInput);

        if (empty($dniLimpio) || strlen($dniLimpio) < 5) {
            return response()->json(['success' => false, 'message' => 'Ingrese un DNI válido (mínimo 5 dígitos).'], 422);
        }

        $dniFmt = '';
        if (strlen($dniLimpio) >= 4) {
            $dniFmt = substr($dniLimpio, 0, 4);
            if (strlen($dniLimpio) >= 8) {
                $dniFmt .= '-' . substr($dniLimpio, 4, 4);
                if (strlen($dniLimpio) >= 9) {
                    $dniFmt .= '-' . substr($dniLimpio, 8, min(5, strlen($dniLimpio) - 8));
                }
            } else {
                $dniFmt .= '-' . substr($dniLimpio, 4);
            }
        } else {
            $dniFmt = $dniLimpio;
        }

        \Illuminate\Support\Facades\Cache::forget("paciente_realtime_{$dniLimpio}");
        \Illuminate\Support\Facades\Cache::forget("paciente_realtime_{$dniFmt}");

        $datos = null;
        try {
            $reqSesal = new \Illuminate\Http\Request(['identidad' => $dniLimpio]);
            $pruebaCtrl = app(\App\Http\Controllers\PruebaConsultaController::class);
            $res = $pruebaCtrl->buscar($reqSesal);

            if ($res && method_exists($res, 'getData')) {
                $json = $res->getData(true);
                $dataObj = isset($json['data']) && is_array($json['data']) ? $json['data'] : $json;

                if (!empty($dataObj['nombre_completo']) || !empty($dataObj['nombres'])) {
                    $datos = $dataObj;
                }
            }
        } catch (\Throwable $e) {
            \Log::warning("buscarYAgregar SESAL error para {$dniLimpio}: " . $e->getMessage());
            return response()->json(['success' => false, 'message' => 'Error al consultar SESAL: ' . $e->getMessage()], 500);
        }

        if (!$datos) {
            return response()->json([
                'success' => false,
                'message' => 'No se encontraron datos en SESAL/SNVS para este DNI.',
                'dni_limpio' => $dniLimpio,
                'dni' => $dniFmt
            ], 422);
        }

        // Fallback local (expediente, colonia, etc.)
        $exp = $this->valorVacioANull($datos['expediente'] ?? null);
        $fallback = $this->datosFallback($dniLimpio, $dniFmt, $exp);

        // Precalcular datos combinados (para preview y confirmar)
        $pacienteExistente = Paciente::where('dni_limpio', $dniLimpio)->first();
        $esNuevo = !$pacienteExistente;

        // "Preview" combinado = aplicar merge pero sobre un paciente fantasma (null si nuevo)
        $previewMerge = $this->combinarDatosPaciente($pacienteExistente, $datos, $fallback, null);

        $previewData = [
            'dni'              => $dniFmt,
            'dni_limpio'       => $dniLimpio,
            'nombre_completo'  => $previewMerge['nombre_completo']
                ?? $this->valorVacioANull($datos['nombre_completo'] ?? null)
                ?? trim(($datos['nombres'] ?? '') . ' ' . ($datos['apellidos'] ?? '')) ?: null,
            'fecha_nacimiento' => $previewMerge['fecha_nacimiento'] ?? $this->valorVacioANull($datos['fecha_nacimiento'] ?? null),
            'edad'             => $previewMerge['edad'] ?? null,
            'colonia'          => $previewMerge['colonia']
                ?? $this->valorVacioANull($datos['colonia'] ?? $datos['direccion'] ?? null)
                ?? $this->valorVacioANull($fallback['colonia'] ?? $fallback['direccion'] ?? null),
            'telefono'         => $previewMerge['telefono']
                ?? $this->valorVacioANull($datos['telefono'] ?? null)
                ?? $this->valorVacioANull($fallback['telefono'] ?? null),
            'departamento'     => $previewMerge['departamento']
                ?? $this->valorVacioANull($datos['departamento'] ?? null)
                ?? $this->valorVacioANull($fallback['departamento'] ?? null),
            'municipio'        => $previewMerge['municipio']
                ?? $this->valorVacioANull($datos['municipio'] ?? null)
                ?? $this->valorVacioANull($fallback['municipio'] ?? null),
            'cod_municipio'    => $previewMerge['cod_municipio']
                ?? $this->valorVacioANull($datos['cod_municipio'] ?? null)
                ?? $this->valorVacioANull($fallback['cod_municipio'] ?? null),
        ];

        // Incluimos expediente encontrado (lo usamos como dato contextual)
        if (!empty($fallback['expediente'])) {
            $previewData['expediente'] = $fallback['expediente'];
        } elseif (!empty($datos['expediente'])) {
            $previewData['expediente'] = $datos['expediente'];
        }

        // Campos seleccionados en confirmar
        $camposSeleccionados = $request->input('campos');
        if (is_string($camposSeleccionados)) $camposSeleccionados = [$camposSeleccionados];
        if (is_array($camposSeleccionados)) {
            $camposSeleccionados = array_values(array_filter(array_map('strval', $camposSeleccionados)));
            if (empty($camposSeleccionados)) $camposSeleccionados = null;
        } else {
            $camposSeleccionados = null;
        }

        // ─────────────────────────────────────────────────────
        // MODO PREVIEW
        // ─────────────────────────────────────────────────────
        if ($modo !== 'confirmar') {
            return response()->json([
                'success' => true,
                'modo'    => 'preview',
                'es_nuevo'=> $esNuevo,
                'message' => $esNuevo
                    ? 'Paciente NO registrado localmente. ¿Desea agregarlo?'
                    : 'Paciente YA registrado localmente. ¿Desea actualizar sus datos?',
                'fallback_fuentes' => $fallback['_fuente'] ?? [],
                'preview_data' => $previewData,
                'existente' => $pacienteExistente ? [
                    'id'               => $pacienteExistente->id,
                    'nombre_completo'  => $pacienteExistente->nombre_completo,
                    'dni'              => $pacienteExistente->dni,
                    'fecha_nacimiento' => $pacienteExistente->fecha_nacimiento,
                    'edad'             => $pacienteExistente->edad,
                    'colonia'          => $pacienteExistente->colonia,
                    'telefono'         => $pacienteExistente->telefono,
                    'departamento'     => $pacienteExistente->departamento,
                    'municipio'        => $pacienteExistente->municipio,
                    'cod_municipio'    => $pacienteExistente->cod_municipio,
                ] : null,
            ]);
        }

        // ─────────────────────────────────────────────────────
        // MODO CONFIRMAR
        // ─────────────────────────────────────────────────────
        $updateData = $this->combinarDatosPaciente($pacienteExistente, $datos, $fallback, $camposSeleccionados);

        if ($esNuevo) {
            $updateData['dni'] = $dniFmt;
            $updateData['dni_limpio'] = $dniLimpio;
            $paciente = Paciente::create($updateData);
        } else {
            if (!empty($updateData)) {
                $pacienteExistente->fill($updateData)->save();
            }
            $paciente = $pacienteExistente->fresh();
        }

        return response()->json([
            'success'   => true,
            'modo'      => 'confirmar',
            'nuevo'     => $esNuevo,
            'message'   => $esNuevo ? 'Paciente agregado correctamente.' : (empty($updateData) ? 'No había cambios para aplicar.' : 'Paciente actualizado correctamente.'),
            'cambios_aplicados' => $updateData,
            'origen'    => $datos['origen'] ?? 'sesal',
            'paciente'  => [
                'id'               => $paciente->id,
                'nombre_completo'  => $paciente->nombre_completo,
                'dni'              => $paciente->dni,
                'dni_limpio'       => $paciente->dni_limpio,
                'fecha_nacimiento' => $paciente->fecha_nacimiento,
                'edad'             => $paciente->edad,
                'colonia'          => $paciente->colonia,
                'telefono'         => $paciente->telefono,
                'departamento'     => $paciente->departamento,
                'municipio'        => $paciente->municipio,
                'cod_municipio'    => $paciente->cod_municipio,
            ],
        ]);
    }

    /**
     * Re-sincroniza TODOS los pacientes (o los de la página actual) desde SESAL/SNVS.
     * Ahora con combinación inteligente + fallback.
     */
    public function resyncMasivo(Request $request)
    {
        $todos = $request->input('todos', false);
        $limite = (int)$request->input('limite', $todos ? 0 : 50);

        $query = Paciente::whereNotNull('dni_limpio')->orderBy('id', 'desc');
        if (!$todos && $limite > 0) {
            $query->limit($limite);
        }

        $pacientes = $query->get();
        $total = $pacientes->count();
        $actualizados = 0;
        $sinDatos = 0;
        $errores = 0;
        $idsActualizados = [];

        foreach ($pacientes as $p) {
            try {
                $dniLimpio = $p->dni_limpio;
                $dniFmt = $p->dni;

                \Illuminate\Support\Facades\Cache::forget("paciente_realtime_{$dniLimpio}");
                \Illuminate\Support\Facades\Cache::forget("paciente_realtime_{$dniFmt}");

                $datos = null;
                try {
                    $reqSesal = new \Illuminate\Http\Request(['identidad' => $dniLimpio]);
                    $pruebaCtrl = app(\App\Http\Controllers\PruebaConsultaController::class);
                    $res = $pruebaCtrl->buscar($reqSesal);

                    if ($res && method_exists($res, 'getData')) {
                        $json = $res->getData(true);
                        $dataObj = isset($json['data']) && is_array($json['data']) ? $json['data'] : $json;
                        if (!empty($dataObj['nombre_completo']) || !empty($dataObj['nombres'])) {
                            $datos = $dataObj;
                        }
                    }
                } catch (\Throwable $e) {
                    \Log::warning("resyncMasivo error para {$dniLimpio}: " . $e->getMessage());
                }

                if (!$datos) {
                    $sinDatos++;
                    continue;
                }

                // Fallback + combinación
                $exp = $this->valorVacioANull($datos['expediente'] ?? $p->expediente ?? null);
                $fallback = $this->datosFallback($dniLimpio, $dniFmt, $exp);
                $updateData = $this->combinarDatosPaciente($p, $datos, $fallback, null);

                if (!empty($updateData)) {
                    $p->fill($updateData)->save();
                    $actualizados++;
                    $idsActualizados[] = $p->id;
                }
            } catch (\Throwable $e) {
                $errores++;
                \Log::error("resyncMasivo error paciente ID {$p->id}: " . $e->getMessage());
            }
        }

        return response()->json([
            'success'   => true,
            'message'   => "Procesados: {$total}. Actualizados: {$actualizados}. Sin datos en SESAL: {$sinDatos}. Errores: {$errores}.",
            'total'     => $total,
            'actualizados' => $actualizados,
            'sin_datos' => $sinDatos,
            'errores'   => $errores,
            'ids_actualizados' => $idsActualizados,
        ]);
    }

    /**
     * Recalcula todas las edades en BD a partir de fecha_nacimiento y guarda el resultado en el campo edad.
     * También sirve como "actualizar tabla" para edades.
     */
    public function recalcularEdades()
    {
        $pacientes = Paciente::whereNotNull('fecha_nacimiento')->get();
        $actualizados = 0;

        foreach ($pacientes as $p) {
            $fechaNacStr = $p->fecha_nacimiento;
            if (empty($fechaNacStr) || $fechaNacStr === '-') continue;

            $edadCalculada = null;
            try {
                $edadCalculada = \Carbon\Carbon::createFromFormat('d/m/Y', $fechaNacStr)->age;
            } catch (\Exception $e) {
                try {
                    $c = \Carbon\Carbon::parse($fechaNacStr);
                    if ($c->year > 1900 && $c->year <= now()->year) $edadCalculada = $c->age;
                } catch (\Exception $e2) {}
            }

            if ($edadCalculada !== null && $edadCalculada !== (int)$p->edad) {
                $p->edad = $edadCalculada;
                $p->save();
                $actualizados++;
            }
        }

        return response()->json([
            'success' => true,
            'message' => "Edades recalculadas. Actualizados: {$actualizados} de {$pacientes->count()} registros evaluados.",
            'actualizados' => $actualizados,
            'evaluados' => $pacientes->count(),
        ]);
    }

    /**
     * Recalcula la edad de UN solo paciente a partir de fecha_nacimiento.
     */
    public function recalcularEdadIndividual($id)
    {
        $paciente = Paciente::find($id);
        if (!$paciente) {
            return response()->json(['success' => false, 'message' => 'Paciente no encontrado.'], 404);
        }

        $fechaNacStr = $paciente->fecha_nacimiento;
        if (empty($fechaNacStr) || $fechaNacStr === '-') {
            return response()->json([
                'success' => false,
                'message' => 'El paciente no tiene fecha de nacimiento registrada.',
                'edad' => $paciente->edad
            ], 422);
        }

        $edadCalculada = null;
        try {
            $edadCalculada = \Carbon\Carbon::createFromFormat('d/m/Y', $fechaNacStr)->age;
        } catch (\Exception $e) {
            try {
                $c = \Carbon\Carbon::parse($fechaNacStr);
                if ($c->year > 1900 && $c->year <= now()->year) $edadCalculada = $c->age;
            } catch (\Exception $e2) {}
        }

        if ($edadCalculada === null) {
            return response()->json([
                'success' => false,
                'message' => 'No se pudo calcular la edad con la fecha registrada.',
                'fecha_nacimiento' => $fechaNacStr
            ], 422);
        }

        $cambio = ((int)$paciente->edad !== $edadCalculada);
        $paciente->edad = $edadCalculada;
        $paciente->save();

        return response()->json([
            'success' => true,
            'message' => $cambio ? "Edad actualizada de {$paciente->edad} a {$edadCalculada} años." : "La edad ya estaba correcta ({$edadCalculada} años).",
            'cambio' => $cambio,
            'edad' => $edadCalculada,
            'fecha_nacimiento' => $fechaNacStr,
        ]);
    }

    /**
     * Busca pacientes para modales y selectores en tiempo real.
     */
    public function buscarModal(Request $request)
    {
        $search = trim($request->input('search', ''));

        $searchClean = preg_replace('/\D/', '', $search);

        $query = Paciente::query();

        if (!empty($searchClean) && strlen($searchClean) >= 3) {
            $query->where(function($q) use ($search, $searchClean) {
                $q->where('dni_limpio', 'like', "%{$searchClean}%")
                  ->orWhere('dni', 'like', "%{$search}%")
                  ->orWhere('nombre_completo', 'like', "%{$search}%");
            });
        } else if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('nombre_completo', 'like', "%{$search}%")
                  ->orWhere('dni', 'like', "%{$search}%");
            });
        }

        $pacientes = $query->limit(30)->get()->map(function($p) {
            return [
                'id'               => $p->id,
                'dni'              => $p->dni ?? $p->dni_limpio,
                'dni_limpio'       => $p->dni_limpio ?? $p->dni,
                'nombre_completo'  => $p->nombre_completo,
                'fecha_nacimiento' => $p->fecha_nacimiento,
                'edad'             => $p->edad,
                'telefono'         => $p->telefono,
                'colonia'          => $p->colonia,
                'departamento'     => $p->departamento,
                'municipio'        => $p->municipio,
                'cod_municipio'    => $p->cod_municipio,
            ];
        });

        return response()->json($pacientes);
    }
}
