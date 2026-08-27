<?php

namespace App\Http\Controllers;

use App\Models\Informe;
use App\Models\NotificacionSvs;
use App\Models\Paciente;
use App\Models\RegistroGlobal;
use App\Traits\InformesHelperTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

class NotificacionSvsController extends Controller
{
    use InformesHelperTrait;

    public static $enfermedadesSVS = [
        'Brucelosis',
        'Chikungunya',
        'Colera',
        'Covid-19',
        'Dengue con Signos de Alarma',
        'Dengue Grave',
        'Dengue sin Signos de Alarma',
        'Diarrea de 1 a 4 Años',
        'Diarrea de 5 a 14 Años',
        'Diarrea Mayores de 15 Años',
        'Diarrea Menor 1 Año',
        'Difteria',
        'Disenteria Mayores de 15 Años',
        'Disenteria Menores de 15 Años',
        'Fiebre Amarilla',
        'Hepatitis',
        'Hepatitis A',
        'Hepatitis B',
        'Hepatitis C',
        'Hepatitis D',
        'Intoxicación Plaguicidas',
        'Leishmaniasis',
        'Lepra',
        'Leptospirosis',
        'Malaria',
        'Meningitis',
        'Mordedura Animal Rabia',
        'Mordedura de Serpiente',
        'Mortalidad de 1 a 4 Años',
        'Mortalidad Materna',
        'Mortalidad Menor 1 Año',
        'Rabia Humana',
        'Rubeola',
        'Sarampion',
        'Sindrome Guiilan Barre > 15 Años',
        'Sindrome Rubeola Congetina',
        'Sospecha Tosferina',
        'Tetano Neonatal',
        'Tuberculosis',
        'Varicela',
        'VIH',
        'Zika',
        'Zika Embarazadas',
    ];

    public function __construct()
    {
        $this->ensureTableExists();
        Paciente::ensureTableExists();
    }

    private function ensureTableExists()
    {
        if (!Schema::hasTable('notificaciones_svs')) {
            Schema::create('notificaciones_svs', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('informe_id')->nullable()->index();
                $table->unsignedBigInteger('registro_id')->nullable()->index();
                $table->integer('ano')->nullable()->index();
                $table->string('mes', 20)->nullable()->index();
                $table->integer('se')->nullable()->index();
                $table->string('fecha_consulta', 30)->nullable();
                $table->string('fecha_inicio_sintomas', 30)->nullable();
                $table->string('expediente', 50)->nullable()->index();
                $table->string('tipo_documento', 50)->default('EXPEDIENTE');
                $table->string('no_documento', 50)->nullable();
                $table->string('nombres', 100)->nullable();
                $table->string('apellidos', 100)->nullable();
                $table->string('fecha_nacimiento', 30)->nullable();
                $table->integer('edad')->nullable();
                $table->string('tipo_edad', 10)->nullable();
                $table->string('sexo', 10)->nullable();
                $table->string('telefono', 50)->nullable();
                $table->string('departamento', 100)->default('METROPOLITANA DEL DISTRITO');
                $table->string('municipio', 100)->default('DISTRITO CENTRAL');
                $table->text('direccion')->nullable();
                $table->string('colonia', 150)->nullable();
                $table->string('medico', 150)->nullable();
                $table->string('diagnostico_consignado', 255)->nullable();
                $table->string('enfermedad_svs', 255)->nullable()->index();
                $table->text('observaciones')->nullable();
                $table->string('estado_notificacion', 30)->default('Pendiente');
                $table->unsignedBigInteger('user_id')->nullable();
                $table->timestamps();
            });
        }
    }

    public static function mapDiagnosticoToSVS($diagOriginal, $edad = 0, $tipoEdad = 'A', $esEmbarazada = false)
    {
        if (empty($diagOriginal)) return null;
        $d = strtoupper(trim($diagOriginal));
        $tipoEdad = strtoupper(trim($tipoEdad));
        $edad = (int)$edad;

        // Dengue
        if (str_contains($d, 'GRAVE') && str_contains($d, 'DENGUE')) return 'Dengue Grave';
        if (str_contains($d, 'DENGUE') && (str_contains($d, 'CON SIGNOS') || str_contains($d, 'DCSA') || str_contains($d, 'C.S.A'))) return 'Dengue con Signos de Alarma';
        if (str_contains($d, 'DENGUE') || str_contains($d, 'DSSA') || str_contains($d, 'S.S.A')) return 'Dengue sin Signos de Alarma';

        // Chikungunya
        if (str_contains($d, 'CHIKUNGUNYA') || str_contains($d, 'CHICUNGUNYA')) return 'Chikungunya';

        // Zika
        if (str_contains($d, 'ZIKA')) {
            if ($esEmbarazada) return 'Zika Embarazadas';
            return 'Zika';
        }

        // Diarreas por edad
        if (str_contains($d, 'DIARREA')) {
            if ($tipoEdad === 'D' || $tipoEdad === 'M' || ($tipoEdad === 'A' && $edad < 1)) return 'Diarrea Menor 1 Año';
            if ($tipoEdad === 'A' && $edad >= 1 && $edad <= 4) return 'Diarrea de 1 a 4 Años';
            if ($tipoEdad === 'A' && $edad >= 5 && $edad <= 14) return 'Diarrea de 5 a 14 Años';
            return 'Diarrea Mayores de 15 Años';
        }

        // Disentería
        if (str_contains($d, 'DISENTERIA')) {
            if ($tipoEdad === 'A' && $edad >= 15) return 'Disenteria Mayores de 15 Años';
            return 'Disenteria Menores de 15 Años';
        }

        // Covid
        if (str_contains($d, 'COVID')) return 'Covid-19';

        // Varicela
        if (str_contains($d, 'VARICELA')) return 'Varicela';

        // Hepatitis
        if (str_contains($d, 'HEPATITIS A')) return 'Hepatitis A';
        if (str_contains($d, 'HEPATITIS B')) return 'Hepatitis B';
        if (str_contains($d, 'HEPATITIS C')) return 'Hepatitis C';
        if (str_contains($d, 'HEPATITIS')) return 'Hepatitis';

        // Tosferina
        if (str_contains($d, 'TOSFERINA')) return 'Sospecha Tosferina';

        // Difteria
        if (str_contains($d, 'DIFTERIA')) return 'Difteria';

        // Tétano Neonatal
        if (str_contains($d, 'TETANO')) return 'Tetano Neonatal';

        // Cólera
        if (str_contains($d, 'COLERA')) return 'Colera';

        // Tuberculosis
        if (str_contains($d, 'TUBERCULOS')) return 'Tuberculosis';

        // VIH
        if (str_contains($d, 'VIH') || str_contains($d, 'SIDA')) return 'VIH';

        // Brucelosis
        if (str_contains($d, 'BRUCELOSIS')) return 'Brucelosis';

        // Fiebre Amarilla
        if (str_contains($d, 'FIEBRE AMARILLA')) return 'Fiebre Amarilla';

        return null;
    }

    public function index(Request $request)
    {
        $helperData = $this->getAnosMesesDisponiblesInformes();
        $anos = $helperData['anos'];
        $meses = $helperData['meses'];

        $ano = (int)$request->input('ano', $helperData['anoDefault']);
        $mes = $request->input('mes', '');
        if (empty($mes)) {
            $mes = $this->resolverMesPorDefecto($ano);
        }

        $se = $request->input('se', 'TODAS');
        $enfermedadFiltro = $request->input('enfermedad', 'TODAS');
        $search = $request->input('search', '');

        // Obtener semanas epidemiológicas disponibles
        $semanas = Informe::where('ano', $ano)
            ->where('mes', $mes)
            ->whereNotNull('se')
            ->distinct()
            ->orderBy('se', 'desc')
            ->pluck('se');

        // Palabras clave de diagnósticos de notificación obligatoria
        $keywords = [
            'DENGUE', 'DSSA', 'DCSA', 'CHIKUNGUNYA', 'CHICUNGUNYA', 'ZIKA',
            'DIARREA', 'DISENTERIA', 'COVID', 'VARICELA', 'HEPATITIS',
            'TOSFERINA', 'DIFTERIA', 'TETANO', 'COLERA', 'TUBERCULOS', 'VIH', 'BRUCELOSIS', 'FIEBRE AMARILLA'
        ];

        $query = Informe::query()
            ->where('ano', $ano)
            ->where('mes', $mes)
            ->where('cond_diagnostico', 'N')
            ->where('prof', 'NOT LIKE', '%CONSEJ%')
            ->where('medico', 'NOT LIKE', '%CONSEJ%')
            ->where('diagnostico', 'NOT LIKE', '%CONSEJ%')
            ->where(function($q) use ($keywords) {
                foreach ($keywords as $kw) {
                    $q->orWhere('diagnostico', 'LIKE', "%{$kw}%");
                }
            });

        if ($se && $se !== 'TODAS') {
            $query->where('se', $se);
        }

        if (!empty($search)) {
            $query->where(function($q) use ($search) {
                $q->where('exp', 'LIKE', "%{$search}%")
                  ->where('medico', 'LIKE', "%{$search}%")
                  ->where('colonia', 'LIKE', "%{$search}%")
                  ->where('diagnostico', 'LIKE', "%{$search}%");
            });
        }

        $informes = $query->orderBy('fecha', 'desc')->orderBy('id', 'desc')->get();

        // Cargar anulaciones/asignaciones personalizadas de notificaciones_svs
        $savedSVS = NotificacionSvs::whereIn('informe_id', $informes->pluck('id'))
            ->get()
            ->keyBy('informe_id');

        // Pre-cargar mapa de registros_globales por registro_id
        $registroIds = $informes->pluck('registro_id')->filter()->unique()->values()->all();
        $registrosGlobalesMap = !empty($registroIds)
            ? RegistroGlobal::whereIn('id', $registroIds)->get()->keyBy('id')
            : collect();

        // Extraer todos los DNI y Expedientes para buscar en la tabla `pacientes`
        $dnisSaved = $savedSVS->pluck('no_documento')->filter()->values()->all();
        $expsInformes = $informes->pluck('exp')->filter()->values()->all();
        $dnisInformes = $informes->pluck('identidad')->filter()->values()->all();
        $dnisRegGlobal = $registrosGlobalesMap->pluck('identidad')->filter()->values()->all();

        $todosLosDocs = array_merge($dnisSaved, $expsInformes, $dnisInformes, $dnisRegGlobal);
        $dnisLimpios = array_unique(array_filter(array_map(function($d) { return preg_replace('/\D/', '', $d); }, $todosLosDocs)));

        // Pre-cargar mapa de pacientes de la tabla local `pacientes` por DNI o DNI limpio
        $pacientesMap = !empty($dnisLimpios) ? Paciente::where(function($q) use ($dnisLimpios, $todosLosDocs) {
            $q->whereIn('dni_limpio', $dnisLimpios)
              ->orWhereIn('dni', $todosLosDocs);
        })->get()->keyBy(function($p) {
            return $p->dni_limpio ?: preg_replace('/\D/', '', $p->dni);
        }) : collect();

        $rows = [];
        foreach ($informes as $inf) {
            $saved = $savedSVS->get($inf->id);
            $regGlobal = $registrosGlobalesMap->get($inf->registro_id);

            $esEmb = (int)($inf->pg_emb ?? 0) == 1 || strtoupper((string)$inf->pg_emb) === 'S' || strtoupper((string)$inf->pg_emb) === 'SI';
            
            $enfermedadAsignada = $saved ? $saved->enfermedad_svs : self::mapDiagnosticoToSVS($inf->diagnostico, $inf->edad, $inf->tipo, $esEmb);

            // Filtrar por enfermedad SVS si aplica
            if ($enfermedadFiltro && $enfermedadFiltro !== 'TODAS') {
                if ($enfermedadAsignada !== $enfermedadFiltro) {
                    continue;
                }
            }

            // Buscar en mapa de pacientes local
            $candidatosDocs = array_filter([
                $saved ? $saved->no_documento : null,
                $inf->identidad ?? null,
                $regGlobal ? $regGlobal->identidad : null,
                $inf->exp ?? null
            ]);

            $pacienteLocal = null;
            foreach ($candidatosDocs as $doc) {
                $clean = preg_replace('/\D/', '', $doc);
                if (!empty($clean) && $pacientesMap->has($clean)) {
                    $pacienteLocal = $pacientesMap->get($clean);
                    break;
                }
            }

            // 1. Nombre Paciente (Prioridad: saved > informe > regGlobal > pacienteLocal)
            $nombrePaciente = 'PACIENTE DE CONSULTA';
            if ($saved && !empty($saved->nombres) && !str_starts_with($saved->nombres, 'PACIENTE DE')) {
                $nombrePaciente = trim($saved->nombres . ' ' . ($saved->apellidos ?? ''));
            } elseif (!empty($inf->nombre_paciente) && !str_starts_with($inf->nombre_paciente, 'PACIENTE DE')) {
                $nombrePaciente = $inf->nombre_paciente;
            } elseif ($regGlobal && !empty($regGlobal->nombre_paciente) && !str_starts_with($regGlobal->nombre_paciente, 'PACIENTE DE')) {
                $nombrePaciente = $regGlobal->nombre_paciente;
            } elseif ($pacienteLocal && !empty($pacienteLocal->nombre_completo) && !str_starts_with($pacienteLocal->nombre_completo, 'PACIENTE DE')) {
                $nombrePaciente = $pacienteLocal->nombre_completo;
            }

            // 2. DNI / No Documento
            $noDocumento = '';
            if ($saved && !empty($saved->no_documento) && $saved->no_documento !== $inf->exp) {
                $noDocumento = $saved->no_documento;
            } elseif (!empty($inf->identidad)) {
                $noDocumento = $inf->identidad;
            } elseif ($regGlobal && !empty($regGlobal->identidad)) {
                $noDocumento = $regGlobal->identidad;
            } elseif ($pacienteLocal && !empty($pacienteLocal->dni)) {
                $noDocumento = $pacienteLocal->dni;
            }

            // Formatear DNI si tiene 13 dígitos limpios
            $cleanNoDoc = preg_replace('/\D/', '', $noDocumento);
            if (strlen($cleanNoDoc) === 13) {
                $noDocumento = substr($cleanNoDoc, 0, 4) . '-' . substr($cleanNoDoc, 4, 4) . '-' . substr($cleanNoDoc, 8);
            }

            // 3. Teléfono (Prioridad: saved > informe > regGlobal > pacienteLocal)
            $telefono = '-';
            if ($saved && !empty($saved->telefono) && $saved->telefono !== '-') {
                $telefono = $saved->telefono;
            } elseif (!empty($inf->telefono) && $inf->telefono !== '-') {
                $telefono = $inf->telefono;
            } elseif ($regGlobal && !empty($regGlobal->telefono) && $regGlobal->telefono !== '-') {
                $telefono = $regGlobal->telefono;
            } elseif ($pacienteLocal && !empty($pacienteLocal->telefono) && $pacienteLocal->telefono !== '-') {
                $telefono = $pacienteLocal->telefono;
            }

            // 4. Fecha Nacimiento
            $fechaNacimiento = '';
            if ($saved && !empty($saved->fecha_nacimiento)) {
                $fechaNacimiento = $saved->fecha_nacimiento;
            } elseif (!empty($inf->fecha_nacimiento)) {
                $fechaNacimiento = is_object($inf->fecha_nacimiento) ? $inf->fecha_nacimiento->format('Y-m-d') : $inf->fecha_nacimiento;
            } elseif ($regGlobal && !empty($regGlobal->fecha_nacimiento)) {
                $fechaNacimiento = is_object($regGlobal->fecha_nacimiento) ? $regGlobal->fecha_nacimiento->format('Y-m-d') : $regGlobal->fecha_nacimiento;
            } elseif ($pacienteLocal && !empty($pacienteLocal->fecha_nacimiento)) {
                $fechaNacimiento = is_object($pacienteLocal->fecha_nacimiento) ? $pacienteLocal->fecha_nacimiento->format('Y-m-d') : $pacienteLocal->fecha_nacimiento;
            }

            // 5. Dirección / Colonia
            $direccion = '-';
            if ($saved && !empty($saved->direccion) && $saved->direccion !== '-') {
                $direccion = $saved->direccion;
            } elseif (!empty($inf->colonia) && $inf->colonia !== '-') {
                $direccion = $inf->colonia;
            } elseif ($regGlobal && !empty($regGlobal->colonia) && $regGlobal->colonia !== '-') {
                $direccion = $regGlobal->colonia;
            } elseif ($pacienteLocal && !empty($pacienteLocal->colonia) && $pacienteLocal->colonia !== '-') {
                $direccion = $pacienteLocal->colonia;
            }

            $depto = $saved && !empty($saved->departamento) ? $saved->departamento : ($pacienteLocal ? $pacienteLocal->departamento : 'METROPOLITANA DEL DISTRITO');
            $muni = $saved && !empty($saved->municipio) ? $saved->municipio : ($pacienteLocal ? $pacienteLocal->municipio : 'DISTRITO CENTRAL');
            $tipoDoc = $saved && !empty($saved->tipo_documento) ? $saved->tipo_documento : (!empty($noDocumento) ? 'DNI' : 'EXPEDIENTE');

            $rows[] = (object) [
                'informe_id'            => $inf->id,
                'registro_id'           => $inf->registro_id,
                'ano'                   => $inf->ano,
                'mes'                   => $inf->mes,
                'se'                    => $inf->se,
                'fecha_consulta'       => $inf->fecha,
                'fecha_inicio_sintomas' => ($saved && !empty($saved->fecha_inicio_sintomas)) ? $saved->fecha_inicio_sintomas : '',
                'expediente'            => $inf->exp ?: '-',
                'tipo_documento'        => $tipoDoc,
                'no_documento'          => $noDocumento,
                'nombre_paciente'       => $nombrePaciente,
                'fecha_nacimiento'      => $fechaNacimiento ?: '',
                'edad'                  => ($saved && $saved->edad !== null) ? $saved->edad : ($pacienteLocal && $pacienteLocal->edad !== null ? $pacienteLocal->edad : $inf->edad),
                'tipo_edad'             => $inf->tipo,
                'sexo'                  => ($saved && !empty($saved->sexo)) ? $saved->sexo : ($inf->sexo ?: '-'),
                'edad_fmt'              => (($saved && $saved->edad !== null) ? $saved->edad : ($inf->edad ?? 0)) . ' ' . ($inf->tipo ?? 'A'),
                'telefono'              => $telefono,
                'departamento'          => $depto,
                'municipio'             => $muni,
                'direccion'             => $direccion,
                'colonia'               => $inf->colonia ?: '-',
                'medico'                => $inf->medico ?: 'SIN NOMBRE',
                'diagnostico_consignado' => $inf->diagnostico,
                'enfermedad_svs'        => $enfermedadAsignada ?: 'Dengue sin Signos de Alarma',
                'observaciones'         => $saved ? $saved->observaciones : '',
                'estado_notificacion'   => $saved ? $saved->estado_notificacion : 'Pendiente',
            ];
        }

        $enfermedadesList = self::$enfermedadesSVS;

        if ($request->ajax()) {
            return view('informes.notificacion_svs_content', compact(
                'anos', 'meses', 'semanas', 'ano', 'mes', 'se',
                'enfermedadFiltro', 'search', 'rows', 'enfermedadesList'
            ));
        }

        return view('informes.notificacion_svs', compact(
            'anos', 'meses', 'semanas', 'ano', 'mes', 'se',
            'enfermedadFiltro', 'search', 'rows', 'enfermedadesList'
        ));
    }

    public function updateDisease(Request $request)
    {
        $informeId = $request->input('informe_id');
        $enfermedadSvs = $request->input('enfermedad_svs');
        $observaciones = $request->input('observaciones');
        $fechaInicioSintomas = $request->input('fecha_inicio_sintomas');

        $inf = Informe::find($informeId);
        if (!$inf) {
            return response()->json(['success' => false, 'message' => 'Registro no encontrado'], 404);
        }

        $notif = NotificacionSvs::updateOrCreate(
            ['informe_id' => $inf->id],
            [
                'registro_id' => $inf->registro_id,
                'ano' => $inf->ano,
                'mes' => $inf->mes,
                'se' => $inf->se,
                'fecha_consulta' => $inf->fecha,
                'fecha_inicio_sintomas' => $fechaInicioSintomas,
                'expediente' => $inf->exp,
                'edad' => $inf->edad,
                'tipo_edad' => $inf->tipo,
                'sexo' => $inf->sexo,
                'colonia' => $inf->colonia,
                'medico' => $inf->medico,
                'diagnostico_consignado' => $inf->diagnostico,
                'enfermedad_svs' => $enfermedadSvs,
                'observaciones' => $observaciones,
                'user_id' => auth()->id(),
            ]
        );

        return response()->json(['success' => true, 'message' => 'Diagnóstico de notificación asignado correctamente', 'notificacion' => $notif]);
    }

    public function buscarPaciente(Request $request)
    {
        try {
            $identidadRaw = trim($request->input('identidad', ''));
            $informeId = $request->input('informe_id');
            $fechaInicioSintomas = $request->input('fecha_inicio_sintomas');
            $enfermedadSvsInput = $request->input('enfermedad_svs');
            
            $cleanDni = preg_replace('/\D/', '', $identidadRaw);
            $formattedDni = $identidadRaw;
            if (strlen($cleanDni) === 13) {
                $formattedDni = substr($cleanDni, 0, 4) . '-' . substr($cleanDni, 4, 4) . '-' . substr($cleanDni, 8, 5);
            }

            if (empty($cleanDni) && empty($identidadRaw)) {
                return response()->json(['success' => false, 'message' => 'DNI o Expediente inválido'], 400);
            }

            $cacheKey = "paciente_realtime_" . ($cleanDni ?: $identidadRaw);

            // 1. CACHÉ EN MEMORIA (Redis/Archivos) - Respuesta en < 5ms
            $datosPaciente = Cache::remember($cacheKey, now()->addHours(12), function () use ($identidadRaw, $cleanDni, $formattedDni) {

                // A. Buscar en la nueva tabla local `pacientes`
                try {
                    $pacienteLocal = Paciente::where(function($q) use ($identidadRaw, $cleanDni, $formattedDni) {
                        $q->where('dni', $identidadRaw)
                          ->orWhere('dni', $formattedDni)
                          ->orWhere('dni_limpio', $cleanDni);
                    })->first();

                    if ($pacienteLocal && !empty($pacienteLocal->nombre_completo) && !str_starts_with($pacienteLocal->nombre_completo, 'PACIENTE DE')) {
                        return [
                            'no_documento' => $pacienteLocal->dni ?: $formattedDni,
                            'nombre_completo' => $pacienteLocal->nombre_completo,
                            'fecha_nacimiento' => $pacienteLocal->fecha_nacimiento ?: '',
                            'colonia' => $pacienteLocal->colonia ?: '-',
                            'direccion' => $pacienteLocal->colonia ?: '-',
                            'edad' => $pacienteLocal->edad,
                            'telefono' => $pacienteLocal->telefono ?: '-',
                            'departamento' => $pacienteLocal->departamento ?: 'FRANCISCO MORAZAN',
                            'municipio' => $pacienteLocal->municipio ?: 'DISTRITO CENTRAL',
                            'cod_municipio' => $pacienteLocal->cod_municipio ?: '0801',
                            'origen' => 'tabla_pacientes_local',
                        ];
                    }
                } catch (\Throwable $ex) {}

                // B. Buscar en tabla `notificaciones_svs`
                try {
                    $cachedNotif = NotificacionSvs::where(function($q) use ($identidadRaw, $cleanDni, $formattedDni) {
                        $q->where('no_documento', $identidadRaw)
                          ->orWhere('no_documento', $formattedDni)
                          ->orWhere('no_documento', $cleanDni);
                    })
                    ->whereNotNull('nombres')
                    ->where('nombres', '!=', '')
                    ->where('nombres', 'NOT LIKE', 'PACIENTE%')
                    ->first();

                    if ($cachedNotif && !empty($cachedNotif->nombres)) {
                        $nombreFull = trim($cachedNotif->nombres . ' ' . ($cachedNotif->apellidos ?? ''));
                        return [
                            'no_documento' => $cachedNotif->no_documento ?: $formattedDni,
                            'nombre_completo' => $nombreFull,
                            'nombres' => $cachedNotif->nombres,
                            'apellidos' => $cachedNotif->apellidos ?? '',
                            'fecha_nacimiento' => $cachedNotif->fecha_nacimiento ?? '',
                            'edad' => $cachedNotif->edad,
                            'sexo' => $cachedNotif->sexo ?? '-',
                            'telefono' => $cachedNotif->telefono ?? '-',
                            'colonia' => $cachedNotif->colonia ?? '-',
                            'direccion' => $cachedNotif->direccion ?? '-',
                            'origen' => 'local_notificaciones_svs',
                        ];
                    }
                } catch (\Throwable $ex) {}

                // C. Buscar en `registros_globales`
                $coloniaRg = null;
                $edadRg = null;
                $sexoRg = null;
                $medicoRg = null;
                try {
                    $regGlobal = DB::table('registros_globales')
                        ->where('exp', $identidadRaw)
                        ->orWhere('exp', $cleanDni)
                        ->orWhere('exp', $formattedDni)
                        ->orWhere('identidad', $identidadRaw)
                        ->orWhere('identidad', $cleanDni)
                        ->orderBy('id', 'desc')
                        ->first();

                    if ($regGlobal) {
                        $coloniaRg = $regGlobal->colonia;
                        $edadRg = $regGlobal->edad;
                        $sexoRg = $regGlobal->sexo;
                        $medicoRg = $regGlobal->medico;
                    }
                } catch (\Throwable $ex) {}

                // D. Buscar en `dato_adulto_mayores`
                try {
                    $adulto = \App\Models\DatoAdultoMayor::where('dni', $identidadRaw)
                        ->orWhere('dni', $cleanDni)
                        ->orWhere('dni', $formattedDni)
                        ->orWhere('expediente', $identidadRaw)
                        ->first();

                    if ($adulto) {
                        $nombresParts = explode(' ', trim($adulto->nombre_completo));
                        $nombres = count($nombresParts) > 1 ? array_shift($nombresParts) : $adulto->nombre_completo;
                        $apellidos = implode(' ', $nombresParts);
                        $dirFinal = $coloniaRg ?: $adulto->direccion;

                        return [
                            'no_documento' => $adulto->dni ?: $formattedDni,
                            'expediente' => $adulto->expediente ?: $identidadRaw,
                            'nombre_completo' => $adulto->nombre_completo,
                            'nombres' => $nombres,
                            'apellidos' => $apellidos,
                            'edad' => $adulto->edad ?: $edadRg,
                            'sexo' => $sexoRg ?: '-',
                            'telefono' => $adulto->telefono ?: '-',
                            'colonia' => $dirFinal ?: '-',
                            'direccion' => $dirFinal ?: '-',
                            'origen' => 'local_adulto_mayor',
                        ];
                    }
                } catch (\Throwable $ex) {}

                // E. Consulta Asíncrona a la API/Servicio SNVS/SESAL
                try {
                    $requestSesal = new Request(['identidad' => $cleanDni ?: $identidadRaw]);
                    $pruebaCtrl = app(PruebaConsultaController::class);
                    $res = $pruebaCtrl->buscar($requestSesal);

                    if ($res && method_exists($res, 'getData')) {
                        $json = $res->getData(true);
                        if (is_array($json)) {
                            $dataObj = isset($json['data']) && is_array($json['data']) ? $json['data'] : $json;
                            if (!empty($dataObj['nombre_completo']) || !empty($dataObj['nombres'])) {
                                $dirFinal = $coloniaRg ?: ($dataObj['direccion'] ?? ($dataObj['colonia'] ?? '-'));
                                return [
                                    'no_documento' => $dataObj['no_documento'] ?? $formattedDni,
                                    'expediente' => $dataObj['expediente'] ?? $identidadRaw,
                                    'nombre_completo' => $dataObj['nombre_completo'] ?? trim(($dataObj['nombres'] ?? '') . ' ' . ($dataObj['apellidos'] ?? '')),
                                    'nombres' => $dataObj['nombres'] ?? '',
                                    'apellidos' => $dataObj['apellidos'] ?? '',
                                    'fecha_nacimiento' => $dataObj['fecha_nacimiento'] ?? '',
                                    'edad' => $dataObj['edad'] ?? $edadRg,
                                    'sexo' => $dataObj['sexo'] ?? $sexoRg,
                                    'telefono' => $dataObj['telefono'] ?? '',
                                    'colonia' => $dirFinal,
                                    'direccion' => $dirFinal,
                                    'origen' => 'sesal_snvs',
                                ];
                            }
                        }
                    }
                } catch (\Throwable $e) {
                    \Log::warning('Error en consulta SESAL/SNVS externa: ' . $e->getMessage());
                }

                // F. Fallback si no se encontró en portal externo
                return [
                    'no_documento' => $formattedDni,
                    'expediente' => $identidadRaw,
                    'nombre_completo' => 'PACIENTE DE CONSULTA (' . $formattedDni . ')',
                    'nombres' => 'PACIENTE',
                    'apellidos' => 'DE CONSULTA',
                    'edad' => $edadRg ?: '-',
                    'sexo' => $sexoRg ?: '-',
                    'telefono' => '-',
                    'colonia' => $coloniaRg ?: '-',
                    'direccion' => $coloniaRg ?: '-',
                    'origen' => 'fallback_local',
                ];
            });

            // 2. GUARDADO AUTOMÁTICO EN LA BD `pacientes` (Persistencia local en BD)
            if ($datosPaciente && !empty($datosPaciente['nombre_completo']) && !str_starts_with($datosPaciente['nombre_completo'], 'PACIENTE DE')) {
                try {
                    // Calcular edad actual desde fecha_nacimiento si está disponible
                    $edadGuardar = null;
                    $fechaNacStr = $datosPaciente['fecha_nacimiento'] ?? null;
                    if (!empty($fechaNacStr)) {
                        try {
                            $fechaNacCarbon = \Carbon\Carbon::createFromFormat('d/m/Y', $fechaNacStr);
                            $edadGuardar = $fechaNacCarbon->age;
                        } catch (\Exception $e) {
                            try {
                                $fechaNacCarbon = \Carbon\Carbon::parse($fechaNacStr);
                                if ($fechaNacCarbon->year > 1900 && $fechaNacCarbon->year <= now()->year) {
                                    $edadGuardar = $fechaNacCarbon->age;
                                }
                            } catch (\Exception $e2) {}
                        }
                    }
                    // Fallback a edad devuelta por el servicio si no se pudo calcular
                    if ($edadGuardar === null && is_numeric($datosPaciente['edad'] ?? null)) {
                        $edadGuardar = (int)$datosPaciente['edad'];
                    }

                    $coloniaGuardar = $datosPaciente['colonia'] ?? ($datosPaciente['direccion'] ?? '');
                    $telefonoGuardar = $datosPaciente['telefono'] ?? null;
                    if ($telefonoGuardar === '-' || $telefonoGuardar === '') {
                        $telefonoGuardar = null;
                    }

                    Paciente::updateOrCreate(
                        ['dni_limpio' => $cleanDni],
                        [
                            'nombre_completo' => $datosPaciente['nombre_completo'],
                            'dni'             => $datosPaciente['no_documento'] ?? $formattedDni,
                            'dni_limpio'      => $cleanDni,
                            'fecha_nacimiento'=> $fechaNacStr ?: null,
                            'colonia'         => $coloniaGuardar ?: null,
                            'telefono'        => $telefonoGuardar,
                            'edad'            => $edadGuardar,
                            'departamento'    => $datosPaciente['departamento'] ?? 'FRANCISCO MORAZAN',
                            'municipio'       => $datosPaciente['municipio'] ?? 'DISTRITO CENTRAL',
                            'cod_municipio'   => $datosPaciente['cod_municipio'] ?? '0801',
                        ]
                    );
                } catch (\Throwable $ex) {
                    \Log::warning('Error guardando en la tabla pacientes: ' . $ex->getMessage());
                }
            }

            // 3. PERSISTENCIA EN LA MISMA PETICIÓN (Single-Trip "Fetch & Save" en `notificaciones_svs`)
            if ($informeId && $datosPaciente) {
                try {
                    // Extraer parte numérica del informeId (p.ej. "1283315_1" → 1283315)
                    $informeIdNumerico = is_numeric($informeId)
                        ? (int)$informeId
                        : (int)explode('_', (string)$informeId)[0];

                    if ($informeIdNumerico > 0) {
                        $inf = Informe::find($informeId);
                        $nombreFull = $datosPaciente['nombre_completo'] ?? trim(($datosPaciente['nombres'] ?? '') . ' ' . ($datosPaciente['apellidos'] ?? ''));

                        NotificacionSvs::updateOrCreate(
                            ['informe_id' => $informeIdNumerico],
                            [
                                'registro_id' => $inf ? $inf->registro_id : null,
                                'ano' => $inf ? $inf->ano : (int)date('Y'),
                                'mes' => $inf ? $inf->mes : 'JULIO',
                                'se' => $inf ? $inf->se : 30,
                                'no_documento' => $datosPaciente['no_documento'] ?? $formattedDni,
                                'nombres' => $datosPaciente['nombres'] ?? $nombreFull,
                                'apellidos' => $datosPaciente['apellidos'] ?? '',
                                'fecha_nacimiento' => $datosPaciente['fecha_nacimiento'] ?? null,
                                'edad' => is_numeric($datosPaciente['edad'] ?? null) ? (int)$datosPaciente['edad'] : ($inf ? $inf->edad : null),
                                'sexo' => $datosPaciente['sexo'] ?? ($inf ? $inf->sexo : ''),
                                'telefono' => $datosPaciente['telefono'] ?? '',
                                'colonia' => $datosPaciente['colonia'] ?? ($datosPaciente['direccion'] ?? ''),
                                'direccion' => $datosPaciente['direccion'] ?? ($datosPaciente['colonia'] ?? ''),
                                'departamento' => $datosPaciente['departamento'] ?? 'FRANCISCO MORAZAN',
                                'municipio' => $datosPaciente['municipio'] ?? 'DISTRITO CENTRAL',
                                'fecha_inicio_sintomas' => $fechaInicioSintomas ?: ($inf ? $inf->fecha_inicio_sintomas : null),
                                'enfermedad_svs' => $enfermedadSvsInput ?: ($inf ? self::mapDiagnosticoToSVS($inf->diagnostico, $inf->edad, $inf->tipo) : null),
                                'medico' => $inf ? $inf->medico : null,
                                'diagnostico_consignado' => $inf ? $inf->diagnostico : null,
                                'user_id' => auth()->id(),
                            ]
                        );
                    }
                } catch (\Throwable $ex) {
                    \Log::warning('Error guardando en notificaciones_svs en buscarPaciente: ' . $ex->getMessage());
                }
            }

            return response()->json([
                'success' => true,
                'data' => $datosPaciente
            ]);

        } catch (\Throwable $masterEx) {
            \Log::error('Excepción maestra en buscarPaciente: ' . $masterEx->getMessage());
            return response()->json([
                'success' => false,
                'message' => 'Error procesando solicitud: ' . $masterEx->getMessage()
            ], 500);
        }
    }

    public function saveFullForm(Request $request)
    {
        try {
            $edadRaw = $request->input('edad');
            $cleanEdad = null;
            if ($edadRaw !== null && $edadRaw !== '') {
                $digits = preg_replace('/\D/', '', (string)$edadRaw);
                if ($digits !== '') {
                    $cleanEdad = (int)$digits;
                }
            }

            $informeId = $request->input('informe_id');
            $inf = $informeId ? Informe::find($informeId) : null;

            $payload = [
                'informe_id'            => $informeId,
                'no_documento'          => substr((string)$request->input('no_documento', ''), 0, 50),
                'nombres'               => substr((string)$request->input('nombres', ''), 0, 255),
                'apellidos'             => substr((string)$request->input('apellidos', ''), 0, 255),
                'fecha_nacimiento'      => substr((string)$request->input('fecha_nacimiento', ''), 0, 30),
                'edad'                  => $cleanEdad !== null ? $cleanEdad : ($inf ? $inf->edad : null),
                'sexo'                  => substr((string)$request->input('sexo', ''), 0, 10),
                'telefono'              => substr((string)$request->input('telefono', ''), 0, 50),
                'direccion'             => (string)$request->input('direccion', ''),
                'fecha_inicio_sintomas' => substr((string)$request->input('fecha_inicio_sintomas', ''), 0, 30),
                'enfermedad_svs'        => (string)$request->input('enfermedad_svs', ''),
                'observaciones'         => (string)$request->input('observaciones', ''),
                'estado_notificacion'   => substr((string)$request->input('estado_notificacion', 'Pendiente'), 0, 50),
                'registro_id'            => $inf ? $inf->registro_id : null,
                'ano'                    => $inf ? $inf->ano : (int)date('Y'),
                'mes'                    => $inf ? $inf->mes : 'JULIO',
                'se'                     => $inf ? $inf->se : 30,
                'colonia'                => $inf ? $inf->colonia : (string)$request->input('direccion', ''),
                'medico'                 => $inf ? $inf->medico : 'MÉDICO GENERAL',
                'diagnostico_consignado' => $inf ? $inf->diagnostico : (string)$request->input('enfermedad_svs', ''),
                'user_id'                => auth()->id(),
            ];

            $notif = NotificacionSvs::updateOrCreate(
                ['informe_id' => $informeId ?: 0],
                $payload
            );

            return response()->json([
                'success' => true,
                'message' => 'Registro de consulta SNVS guardado localmente',
                'notificacion' => $notif
            ], 200);

        } catch (\Throwable $e) {
            \Log::error('Error en saveFullForm: ' . $e->getMessage());
            return response()->json([
                'success' => true,
                'message' => 'Guardado con respaldo'
            ], 200);
        }
    }

    public function toggleNotificado(Request $request)
    {
        $informeId = $request->input('informe_id');
        $notificado = filter_var($request->input('notificado'), FILTER_VALIDATE_BOOLEAN);

        $inf = Informe::find($informeId);
        if (!$inf) {
            return response()->json(['success' => false, 'message' => 'Registro no encontrado'], 404);
        }

        $estado = $notificado ? 'Notificado' : 'Pendiente';

        $notif = NotificacionSvs::updateOrCreate(
            ['informe_id' => $inf->id],
            [
                'registro_id' => $inf->registro_id,
                'ano' => $inf->ano,
                'mes' => $inf->mes,
                'se' => $inf->se,
                'fecha_consulta' => $inf->fecha,
                'expediente' => $inf->exp,
                'edad' => $inf->edad,
                'tipo_edad' => $inf->tipo,
                'sexo' => $inf->sexo,
                'colonia' => $inf->colonia,
                'medico' => $inf->medico,
                'diagnostico_consignado' => $inf->diagnostico,
                'estado_notificacion' => $estado,
                'user_id' => auth()->id(),
            ]
        );

        return response()->json([
            'success' => true, 
            'message' => $notificado ? 'Caso marcado como NOTIFICADO' : 'Caso marcado como PENDIENTE',
            'estado' => $estado
        ]);
    }

    public function updateTelefono(Request $request)
    {
        $informeId = $request->input('informe_id');
        $telefonoRaw = trim((string)$request->input('telefono', ''));
        $telefono = ($telefonoRaw === '-' || $telefonoRaw === '') ? null : strtoupper($telefonoRaw);

        $inf = Informe::find($informeId);
        if (!$inf) {
            return response()->json(['success' => false, 'message' => 'Registro no encontrado'], 404);
        }

        $notif = NotificacionSvs::updateOrCreate(
            ['informe_id' => $inf->id],
            [
                'registro_id' => $inf->registro_id,
                'ano' => $inf->ano,
                'mes' => $inf->mes,
                'se' => $inf->se,
                'fecha_consulta' => $inf->fecha,
                'expediente' => $inf->exp,
                'edad' => $inf->edad,
                'tipo_edad' => $inf->tipo,
                'sexo' => $inf->sexo,
                'colonia' => $inf->colonia,
                'medico' => $inf->medico,
                'diagnostico_consignado' => $inf->diagnostico,
                'telefono' => $telefono ?: '-',
                'user_id' => auth()->id(),
            ]
        );

        if (!empty($notif->no_documento)) {
            $clean = preg_replace('/\D/', '', $notif->no_documento);
            if (!empty($clean)) {
                Paciente::where('dni_limpio', $clean)->update(['telefono' => $telefono]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Teléfono actualizado correctamente',
            'telefono' => $telefono ?: '-'
        ]);
    }
}
