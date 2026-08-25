<?php

namespace App\Http\Controllers\Informes;

use App\Http\Controllers\Controller;
use App\Models\Informe;
use App\Models\Diagnostico;
use App\Models\RegistroGlobal;
use App\Traits\InformesHelperTrait;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class Sm107Controller extends Controller
{
    use InformesHelperTrait;

    public function __construct(private \App\Services\RegistroGlobalService $service)
    {
    }

    public function index(Request $request)
    {
        if (!$request->ajax() && $request->getQueryString()) {
            return redirect()->route('informes.sm107');
        }

        $ano = $request->input('ano', date('Y'));
        $mes = $request->input('mes', '');
        if (empty($mes)) {
            $meses2 = ['ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'];
            $mes = $meses2[(int)date('n') - 1];
        }

        $jornada = $request->input('jornada', 'TODAS');
        $viewType = $request->input('view', 'anversa');

        $anos = $this->service->getAnosDisponibles();
        $meses = ['ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE'];
        $jornadas = ['MATUTINA', 'VESPERTINA', 'FIN DE SEMANA'];

        $carbon = \Carbon\Carbon::create($ano, array_flip($meses)[strtoupper($mes)] + 1, 1);
        $totalDays = $carbon->daysInMonth;
        $dayHeaders = [];
        for ($d = 1; $d <= $totalDays; $d++) {
            $dayHeaders[] = \Carbon\Carbon::create($ano, $carbon->month, $d)->format('d/m/Y');
        }

        $nullDatesCount = 0;
        $lastDayInHeader = count($dayHeaders) > 0 ? end($dayHeaders) : null;
        $monthNum = array_flip($meses)[strtoupper(trim($mes))] + 1;
        $anoInt = (int)$ano;

        $smSearchStrings = Diagnostico::where('categoria', 'LIKE', '%SM%')
            ->get()
            ->pluck('patologia')
            ->map(fn($p) => trim($p))
            ->unique()
            ->values()
            ->toArray();
        
        // Asegurar que las referencias estén presentes para que se cuenten como diagnósticos SM
        if (!in_array('CONSEJERIA VIH/SIDA', $smSearchStrings)) $smSearchStrings[] = 'CONSEJERIA VIH/SIDA';
        if (!in_array('TAMIZAJE (+)', $smSearchStrings)) $smSearchStrings[] = 'TAMIZAJE (+)';
        if (!in_array('TAMIZAJE (-)', $smSearchStrings)) $smSearchStrings[] = 'TAMIZAJE (-)';

        // Agregar "DESCARTAR DIAGNOSTICO" si su versión SM está presente, para manejar registros incompletos
        if (!in_array('DESCARTAR DIAGNOSTICO', $smSearchStrings)) {
            $smSearchStrings[] = 'DESCARTAR DIAGNOSTICO';
        }

        $invalidUnderFive = [];

        $actLabels = [
            19 => 'ENTREVISTA V.D',
            20 => 'ENTREVISTA PSICOLÓGICA',
            21 => 'INTERVENSIÓN EN CRISIS',
            22 => 'FICHA DE VIGILANCIA EPIDEMIOLÓGICA.',
            23 => 'PSICOTERAPIA INDIVIDUAL',
            24 => 'PSICOTERAPIA EN GRUPO',
            25 => 'NÚMERO DE PARTICIPANTES',
            26 => 'PSICOTERAPIA DE FAMILIA',
            27 => 'NÚMERO DE PARTICIPANTES',
            28 => 'REUNIÓN COORDINACIÓN GRUPOS DE APOYO',
            29 => 'NÚMERO DE PARTICIPANTES',
            30 => 'PRUEBAS PSICOLÓGICAS APLICADAS',
            31 => 'REUNIÓN DE TRABAJO COMUNITARIO',
            32 => 'NÚMERO DE PARTICIPANTES',
            33 => 'CAPACITACIONES BRINDADAS',
            34 => 'NÚMERO DE PARTICIPANTES',
            35 => 'CAPACITACIONES RECIBIDAS',
            36 => 'CHARLAS BRINDADAS',
            37 => 'NÚMERO DE PARTICIPANTES',
            38 => 'ORGANIZACIÓN Y FORTALECIMIENTO DE GRUPO',
            39 => 'CONSEJERIA VIH/SIDA',
            40 => 'REFERENCIAS RECIBIDAS',
            41 => 'REFERENCIAS ENVIADAS',
            42 => 'TAMIZAJE (+)',
            43 => 'TAMIZAJE (-)',
        ];
        
        // --- ANVERSA ---
        $anversaData = [];
        if ($viewType == 'anversa') {
            $querySM = RegistroGlobal::where('ano', $ano)->where('mes', $mes);
            if ($jornada != 'TODAS')
                $querySM->where('jornada', $jornada);

            // Obtenemos todos los registros del mes/año/jornada y filtramos en PHP para mayor flexibilidad textual
            $allRecords = $querySM->get();
            $records = $allRecords->filter(function($r) use ($smSearchStrings) {
                // Verificar si es menor de 5 años
                $age = (int)$r->edad;
                $tipo = strtoupper(trim($r->tipo));
                $isUnderFive = ($tipo == 'D' || $tipo == 'M' || ($tipo == 'A' && $age < 5));
                $isUnderTen = ($tipo == 'D' || $tipo == 'M' || ($tipo == 'A' && $age < 10));
                
                $prof = strtoupper(trim($r->prof));
                $isSMProfessional = (stripos($prof, 'PSICOLOGIA') !== false || stripos($prof, 'PSIQUIATRA') !== false || stripos($prof, 'PSIQUIATRIA') !== false);

                // Se evalúan las 7 columnas de diagnóstico. Si al menos una coincide con Salud Mental, se cuenta el registro (atención).
                for ($i = 1; $i <= 7; $i++) {
                    $diag = trim($r->{"diagnostico_$i"});
                    if (empty($diag)) continue;
                    
                    foreach ($smSearchStrings as $smText) {
                        if (empty($smText)) continue;
                        // Coincidencia flexible: insensible a mayúsculas/acentos y espacios múltiples
                        $diagNorm = preg_replace('/\s+/', ' ', strtoupper($this->quitarAcentos($diag)));
                        $smNorm = preg_replace('/\s+/', ' ', strtoupper($this->quitarAcentos($smText)));
                        
                        if (stripos($diagNorm, $smNorm) !== false || stripos($smNorm, $diagNorm) !== false) {
                            if (strlen($diagNorm) > 3) {
                                // REGLA: Excluir Referencias para todos los registros en el informe SM107
                                if (stripos($diagNorm, 'REFERENCIAS RECIBIDAS') !== false || 
                                    stripos($diagNorm, 'REFERENCIAS ENVIADAS') !== false ||
                                    stripos($diagNorm, 'REFERIDA') !== false ||
                                    stripos($diagNorm, 'REFERIDO') !== false) {
                                    continue;
                                }

                                // REGLA: DESCARTAR DIAGNOSTICO solo cuenta si el profesional es de Salud Mental
                                if (stripos($diagNorm, 'DESCARTAR DIAGNOSTICO') !== false) {
                                    if ($isSMProfessional) return true;
                                    continue; // Ignorar este diagnóstico para este profesional
                                }

                                // Se cuenta el registro para otros diagnósticos SM (en todas las edades y profesionales)
                                return true;
                            }
                        }
                    }
                }
                return false;
            });

            for ($i = 1; $i <= 18; $i++)
                $anversaData[$i] = array_fill_keys($dayHeaders, 0);

            foreach ($records as $r) {
                if (!$r->fecha) {
                    $nullDatesCount++;
                    $day = $lastDayInHeader;
                }
                else {
                    try {
                        if (str_contains($r->fecha, '/')) {
                            // Usar format j/n/Y para manejar 1/3/2026 o 01/03/2026 indistintamente
                            $parts = explode('/', $r->fecha);
                            if (count($parts) === 3) {
                                $carbonFecha = \Carbon\Carbon::createFromDate($anoInt, $monthNum, (int)$parts[0]);
                            } else {
                                $carbonFecha = \Carbon\Carbon::createFromFormat('j/n/Y', $r->fecha);
                            }
                        } else {
                            $carbonFecha = \Carbon\Carbon::parse($r->fecha);
                        }
                        $carbonFecha->year($anoInt)->month($monthNum);
                        $day = $carbonFecha->format('d/m/Y');
                    } catch (\Exception $e) {
                        try {
                            $carbonFecha = \Carbon\Carbon::parse($r->fecha);
                            $carbonFecha->year($anoInt)->month($monthNum);
                            $day = $carbonFecha->format('d/m/Y');
                        } catch (\Exception $ex) {
                            $day = $lastDayInHeader;
                        }
                    }
                    
                    if (!isset($anversaData[1][$day]))
                        $day = $lastDayInHeader;
                }
                if (!$day)
                    continue;

                $age = (int)$r->edad;
                $tipo = strtoupper(trim($r->tipo));
                $prof = strtoupper(trim($r->prof));
                $isNew = strtoupper(trim($r->cond)) == 'N';
                
                // Definir si es menor de 5 años (Dato informativo, ya no restringe)
                $isUnderFive = ($tipo == 'D' || $tipo == 'M' || ($tipo == 'A' && $age < 5));

                $rowIdx = 0;
                if ($tipo == 'D' || $tipo == 'M' || ($tipo == 'A' && $age < 1))
                    $rowIdx = 1;
                elseif ($age >= 1 && $age <= 4)
                    $rowIdx = 3;
                elseif ($age >= 5 && $age <= 9)
                    $rowIdx = 5;
                elseif ($age >= 10 && $age <= 14)
                    $rowIdx = 7;
                elseif ($age >= 15 && $age <= 19)
                    $rowIdx = 9;
                elseif ($age >= 20 && $age <= 24)
                    $rowIdx = 11;
                elseif ($age >= 25 && $age <= 39)
                    $rowIdx = 13;
                elseif ($age >= 40 && $age <= 59)
                    $rowIdx = 15;
                elseif ($age >= 60)
                    $rowIdx = 17;

                if ($rowIdx > 0) {
                    if (!$isNew)
                        $rowIdx++; // Fila de subsiguiente
                    if (isset($anversaData[$rowIdx][$day]))
                        $anversaData[$rowIdx][$day]++;
                    
                    // Nota: Se han eliminado los incrementos a anversaData[15-18] que se usaban para totales,
                    // ya que ahora esas filas se usan para los nuevos rangos de edad según la vista.
                    // Los totales se calculan dinámicamente en la vista.
                }
            }
        }

        // --- REVERSA ---
        $reversaActivities = [];
        $reversaDiagnoses = [];
        if ($viewType == 'reversa') {
            $querySG = RegistroGlobal::where('ano', $ano)->where('mes', $mes);
            if ($jornada != 'TODAS')
                $querySG->where('jornada', $jornada);

            $smActivities = [
                ['pattern' => 'ENTREVISTA V.D', 'session' => 19, 'code' => '336'],
                ['pattern' => 'ENTREVISTA PSICOLÓGICA', 'session' => 20, 'code' => '131'],
                ['pattern' => 'INTERVENSIÓN EN CRISIS', 'session' => 21, 'code' => '132'],
                ['pattern' => 'FICHA DE VIGILANCIA EPIDEMIOLÓGICA.', 'session' => 22, 'code' => '133'],
                ['pattern' => 'PSICOTERAPIA INDIVIDUAL', 'session' => 23, 'code' => '134'],
                ['pattern' => 'PSICOTERAPIA EN GRUPO', 'session' => 24, 'participants' => 25, 'code' => '135'],
                ['pattern' => 'PSICOTERAPIA DE FAMILIA', 'session' => 26, 'participants' => 27, 'code' => '137'],
                ['pattern' => 'REUNIÓN COORDINACIÓN GRUPOS DE APOYO', 'session' => 28, 'participants' => 29, 'code' => '139'],
                ['pattern' => 'PRUEBAS PSICOLÓGICAS APLICADAS', 'session' => 30, 'code' => '141'],
                ['pattern' => 'REUNIÓN DE TRABAJO COMUNITARIO', 'session' => 31, 'participants' => 32, 'code' => '142'],
                ['pattern' => 'CAPACITACIONES BRINDADAS', 'session' => 33, 'participants' => 34, 'code' => '144'],
                ['pattern' => 'CAPACITACIONES RECIBIDAS', 'session' => 35, 'code' => '146'],
                ['pattern' => 'CHARLAS BRINDADAS', 'session' => 36, 'participants' => 37, 'code' => '147'],
                ['pattern' => 'ORGANIZACIÓN Y FORTALECIMIENTO DE GRUPO', 'session' => 38, 'code' => '149'],
                ['pattern' => 'CONSEJERIA VIH/SIDA', 'session' => 39, 'code' => '150'],
                ['pattern' => 'REFERENCIAS RECIBIDAS', 'session' => 40, 'code' => '151'],
                ['pattern' => 'REFERENCIAS ENVIADAS', 'session' => 41, 'code' => '152'],
                ['pattern' => 'TAMIZAJE (+)', 'session' => 42, 'code' => '153'],
                ['pattern' => 'TAMIZAJE (-)', 'session' => 43, 'code' => '154'],
            ];

            // IMPORTANTE: Agregar los patrones de actividades SM a smSearchStrings ANTES del filtro.
            // Esto garantiza que registros cuyo ÚNICO diagnóstico sea una actividad de la reversa
            // (ej. PSICOTERAPIA INDIVIDUAL sin otro diagnóstico SM) no sean excluidos del conteo.
            foreach ($smActivities as $act) {
                if (!in_array($act['pattern'], $smSearchStrings)) {
                    $smSearchStrings[] = $act['pattern'];
                }
            }

            // Obtenemos todos los registros del mes/año/jornada y filtramos en PHP
            $allSgRecords = $querySG->get();
            $sgRecords = $allSgRecords->filter(function($r) use ($smSearchStrings) {
                $age = (int)$r->edad;
                $tipo = strtoupper(trim($r->tipo));
                
                $prof = strtoupper(trim($r->prof));
                $isSMProfessional = (stripos($prof, 'PSICOLOGIA') !== false || stripos($prof, 'PSIQUIATRA') !== false || stripos($prof, 'PSIQUIATRIA') !== false);

                // Se evalúan las 7 columnas de diagnóstico. Si al menos una coincide con Salud Mental, se cuenta el registro.
                for ($i = 1; $i <= 7; $i++) {
                    $diag = trim($r->{"diagnostico_$i"});
                    if (empty($diag)) continue;
                    
                    foreach ($smSearchStrings as $smText) {
                        if (empty($smText)) continue;
                        $diagNorm = preg_replace('/\s+/', ' ', strtoupper($this->quitarAcentos($diag)));
                        $smNorm = preg_replace('/\s+/', ' ', strtoupper($this->quitarAcentos($smText)));
                        if (stripos($diagNorm, $smNorm) !== false || stripos($smNorm, $diagNorm) !== false) {
                            if (strlen($diagNorm) > 3) {
                                // REGLA: DESCARTAR DIAGNOSTICO solo cuenta si el profesional es de Salud Mental
                                if (stripos($diagNorm, 'DESCARTAR DIAGNOSTICO') !== false) {
                                    if ($isSMProfessional) return true;
                                    continue;
                                }
                                return true;
                            }
                        }
                    }
                }
                return false;
            });

            for ($i = 19; $i <= 43; $i++)
                $reversaActivities[$i] = array_fill_keys($dayHeaders, 0);

            foreach ($sgRecords as $r) {
                if (!$r->fecha) {
                    $nullDatesCount++;
                    $day = $lastDayInHeader;
                }
                else {
                    try {
                        if (str_contains($r->fecha, '/')) {
                            $parts = explode('/', $r->fecha);
                            if (count($parts) === 3) {
                                $carbonFecha = \Carbon\Carbon::createFromDate($anoInt, $monthNum, (int)$parts[0]);
                            } else {
                                $carbonFecha = \Carbon\Carbon::createFromFormat('j/n/Y', $r->fecha);
                            }
                        } else {
                            $carbonFecha = \Carbon\Carbon::parse($r->fecha);
                        }
                        $carbonFecha->year($anoInt)->month($monthNum);
                        $day = $carbonFecha->format('d/m/Y');
                    } catch (\Exception $e) {
                        try {
                            $carbonFecha = \Carbon\Carbon::parse($r->fecha);
                            $carbonFecha->year($anoInt)->month($monthNum);
                            $day = $carbonFecha->format('d/m/Y');
                        } catch (\Exception $ex) {
                            $day = $lastDayInHeader;
                        }
                    }
                    
                    if (!isset($reversaActivities[19][$day]))
                        $day = $lastDayInHeader;
                }
                if (!$day)
                    continue;

                $age = (int)$r->edad;
                $tipo = strtoupper(trim($r->tipo));
                $prof = strtoupper(trim($r->prof));
                
                $isUnderFive = ($tipo == 'D' || $tipo == 'M' || ($tipo == 'A' && $age < 5));
                $isUnderTen = ($tipo == 'D' || $tipo == 'M' || ($tipo == 'A' && $age < 10));
                

                $processedCodesInRow = [];
                foreach ($smActivities as $act) {
                    if (in_array($act['session'], $processedCodesInRow))
                        continue;
                    
                    $found = false;
                    for ($idxDiag = 1; $idxDiag <= 7; $idxDiag++) {
                        $dField = "diagnostico_{$idxDiag}";
                        
                        if (isset($r->$dField) && !empty($r->$dField)) {
                            // Normalizar: quitar acentos, mayúsculas y colapsar espacios múltiples
                            $textNormal = preg_replace('/\s+/', ' ', trim($this->quitarAcentos(strtoupper($r->$dField))));
                            $patternNormal = preg_replace('/\s+/', ' ', trim($this->quitarAcentos(strtoupper($act['pattern']))));
                            
                            if ($textNormal === $patternNormal) {
                                $found = true;
                                break;
                            }
                        }
                    }

                    if ($found) {
                        $reversaActivities[$act['session']][$day]++;
                        $processedCodesInRow[] = $act['session'];
                        
                        if (isset($act['participants'])) {
                            $targetSubRow = $act['participants'];
                            
                            $pCount = 1;
                            $sgVal = (int)$r->sg;
                            
                            if ($sgVal > 0 && $sgVal != $act['session'] && ($sgVal > 43 || !in_array($sgVal, range(19, 43)))) {
                                $pCount = $sgVal;
                            } else {
                                $pCount = (int)($r->numero ?: 1);
                            }

                            $reversaActivities[$targetSubRow][$day] += $pCount;
                        }
                    }
                }
            }

            $queryDiag = Informe::where('ano', $ano)->where('mes', $mes);
            if ($jornada != 'TODAS')
                $queryDiag->where('jornada', $jornada);
            
            // Filtramos en PHP para mayor flexibilidad textual (solo los que NO están en smActivities)
            $allDiagRecords = $queryDiag->get();
            $diagRecordsFiltered = $allDiagRecords->filter(function($dr) use ($smSearchStrings, $smActivities) {
                $diag = trim($dr->diagnostico);
                if (empty($diag)) return false;
                
                $diagNorm = strtoupper($this->quitarAcentos($diag));
                
                // EXCLUIR si el diagnóstico ya es una de las actividades de smActivities
                foreach ($smActivities as $act) {
                    $pNorm = strtoupper($this->quitarAcentos($act['pattern']));
                    if ($diagNorm === $pNorm || stripos($diagNorm, $pNorm) !== false) {
                        return false;
                    }
                }
                
                $prof = strtoupper(trim($dr->prof));
                $isSMProfessional = (stripos($prof, 'PSICOLOGIA') !== false || stripos($prof, 'PSIQUIATRA') !== false || stripos($prof, 'PSIQUIATRIA') !== false);

                foreach ($smSearchStrings as $smText) {
                    if (empty($smText)) continue;
                    $diagNormCheck = preg_replace('/\s+/', ' ', strtoupper($this->quitarAcentos($diag)));
                    $smNorm = preg_replace('/\s+/', ' ', strtoupper($this->quitarAcentos($smText)));
                    if (stripos($diagNormCheck, $smNorm) !== false || stripos($smNorm, $diagNormCheck) !== false) {
                        if (strlen($diagNormCheck) > 3) {
                            // REGLA: DESCARTAR DIAGNOSTICO solo cuenta si el profesional es de Salud Mental
                            if (stripos($diagNormCheck, 'DESCARTAR DIAGNOSTICO') !== false) {
                                if ($isSMProfessional) return true;
                                continue;
                            }
                            return true;
                        }
                    }
                }
                return false;
            });

            foreach ($diagRecordsFiltered as $dr) {
                if (!$dr->fecha)
                    continue;
                
                try {
                    if (str_contains($dr->fecha, '/')) {
                        $parts = explode('/', $dr->fecha);
                        if (count($parts) === 3) {
                            $carbonFecha = \Carbon\Carbon::createFromDate($anoInt, $monthNum, (int)$parts[0]);
                        } else {
                            $carbonFecha = \Carbon\Carbon::createFromFormat('j/n/Y', $dr->fecha);
                        }
                    } else {
                        $carbonFecha = \Carbon\Carbon::parse($dr->fecha);
                    }
                    $day = $carbonFecha->format('d/m/Y');
                } catch (\Exception $e) {
                    try {
                        $carbonFecha = \Carbon\Carbon::parse($dr->fecha);
                        $day = $carbonFecha->format('d/m/Y');
                    } catch (\Exception $ex) {
                        continue;
                    }
                }

                $key = $dr->cod . ' - ' . $dr->diagnostico;
                if (!isset($reversaDiagnoses[$key]))
                    $reversaDiagnoses[$key] = array_fill_keys($dayHeaders, 0);
                if (isset($reversaDiagnoses[$key][$day]))
                    $reversaDiagnoses[$key][$day]++;
            }
            ksort($reversaDiagnoses);
        }

        $viewFile = $request->ajax() ? 'informes.sm107_content' : 'informes.sm107';
        return view($viewFile, compact(
            'anos', 'meses', 'jornadas', 'ano', 'mes', 'jornada', 'viewType',
            'dayHeaders', 'anversaData', 'reversaActivities', 'reversaDiagnoses', 
            'nullDatesCount', 'invalidUnderFive', 'actLabels'
        ));
    }

    /**
     * Obtener detalles de los registros contados en una celda
     */
    public function details(Request $request)
    {
        $ano = $request->input('ano');
        $mes = $request->input('mes');
        $jornada = $request->input('jornada', 'TODAS');
        $view = $request->input('view');
        $rowId = $request->input('rowId'); // El índice de la fila (1-18 para anversa, código para reversa)
        $fecha = $request->input('fecha'); // Formato d/m/Y o null para el total de la fila

        $smSearchStrings = Diagnostico::where('categoria', 'LIKE', '%SM%')
            ->get()
            ->pluck('patologia')
            ->map(fn($p) => trim($p))
            ->unique()
            ->values()
            ->toArray();

        // Asegurar que las referencias estén presentes para que se cuenten como diagnósticos SM
        if (!in_array('CONSEJERIA VIH/SIDA', $smSearchStrings)) $smSearchStrings[] = 'CONSEJERIA VIH/SIDA';

        // Agregar "DESCARTAR DIAGNOSTICO" si su versión SM está presente
        if (!in_array('DESCARTAR DIAGNOSTICO SM', $smSearchStrings)) {
            $smSearchStrings[] = 'DESCARTAR DIAGNOSTICO SM';
        }

        $query = RegistroGlobal::where('ano', $ano)->where('mes', $mes);
        if ($jornada != 'TODAS') {
            $query->where('jornada', $jornada);
        }

        if ($fecha) {
            try {
                $dbDate = \Carbon\Carbon::createFromFormat('d/m/Y', $fecha)->format('Y-m-d');
                $query->where('fecha', $dbDate);
            } catch (\Exception $e) {
                $query->whereNull('fecha');
            }
        }

        // Primero obtenemos todos los registros posibles y luego filtramos por diagnóstico SM en PHP
        $allRecords = $query->select(
            'id', 'fecha', 'numero', 'exp', 'sexo', 'edad', 'tipo', 'cond', 'prof', 'medico',
            'cod_1', 'diagnostico_1', 'cond_1',
            'cod_2', 'diagnostico_2', 'cond_2',
            'cod_3', 'diagnostico_3', 'cond_3',
            'cod_4', 'diagnostico_4', 'cond_4',
            'cod_5', 'diagnostico_5', 'cond_5',
            'cod_6', 'diagnostico_6', 'cond_6',
            'cod_7', 'diagnostico_7', 'cond_7',
            'sm'
        )->orderBy('fecha')->orderBy('id')->get();

        $records = $allRecords->filter(function($r) use ($smSearchStrings, $view) {
            // Verificar si es menor de 5 años
            $age = (int)$r->edad;
            $tipo = strtoupper(trim($r->tipo));
            $isUnderFive = ($tipo == 'D' || $tipo == 'M' || ($tipo == 'A' && $age < 5));
            $isUnderTen = ($tipo == 'D' || $tipo == 'M' || ($tipo == 'A' && $age < 10));
            
            $prof = strtoupper(trim($r->prof));
            $isSMProfessional = (stripos($prof, 'PSICOLOGIA') !== false || stripos($prof, 'PSIQUIATRA') !== false || stripos($prof, 'PSIQUIATRIA') !== false);

            // Se evalúan las 7 columnas de diagnóstico. Si al menos una coincide con Salud Mental, se cuenta el registro.
            for ($i = 1; $i <= 7; $i++) {
                $diag = trim($r->{"diagnostico_$i"});
                if (empty($diag)) continue;
                
                foreach ($smSearchStrings as $smText) {
                    if (empty($smText)) continue;
                    $diagNorm = strtoupper($this->quitarAcentos($diag));
                    $smNorm = strtoupper($this->quitarAcentos($smText));
                    if (stripos($diagNorm, $smNorm) !== false || stripos($smNorm, $diagNorm) !== false) {
                        if (strlen($diagNorm) > 3) {
                            // REGLA: Excluir Referencias solo para la vista ANVERSA en el informe SM107
                            if ($view == 'anversa' && (
                                stripos($diagNorm, 'REFERENCIAS RECIBIDAS') !== false || 
                                stripos($diagNorm, 'REFERENCIAS ENVIADAS') !== false ||
                                stripos($diagNorm, 'REFERIDA') !== false ||
                                stripos($diagNorm, 'REFERIDO') !== false)) {
                                continue;
                            }

                            // REGLA: DESCARTAR DIAGNOSTICO solo cuenta si el profesional es de Salud Mental
                            if (stripos($diagNorm, 'DESCARTAR DIAGNOSTICO') !== false) {
                                if ($isSMProfessional) return true;
                                continue;
                            }
                            
                            // Se cuenta el registro para otros diagnósticos SM (en todas las edades y profesionales)
                            return true;
                        }
                    }
                }
            }
            return false;
        });

        if ($view == 'anversa') {
            if ($rowId !== 'all') {
                $rowId = (int)$rowId;
                $isNew = ($rowId % 2 != 0);
                
                $records = $records->filter(function($r) use ($rowId, $isNew) {
                    // Filtrar por condición (N o S)
                    if (strtoupper(trim($r->cond)) !== ($isNew ? 'N' : 'S')) return false;

                    // Filtrar por rango de edad
                    $age = (int)$r->edad;
                    $tipo = strtoupper(trim($r->tipo));
                    
                    if ($rowId <= 2) { // < 1
                        return ($tipo == 'D' || $tipo == 'M' || ($tipo == 'A' && $age < 1));
                    } elseif ($rowId <= 4) { // 1-4
                        return ($tipo == 'A' && $age >= 1 && $age <= 4);
                    } elseif ($rowId <= 6) { // 5-9
                        return ($tipo == 'A' && $age >= 5 && $age <= 9);
                    } elseif ($rowId <= 8) { // 10-14
                        return ($tipo == 'A' && $age >= 10 && $age <= 14);
                    } elseif ($rowId <= 10) { // 15-19
                        return ($tipo == 'A' && $age >= 15 && $age <= 19);
                    } elseif ($rowId <= 12) { // 20-24
                        return ($tipo == 'A' && $age >= 20 && $age <= 24);
                    } elseif ($rowId <= 14) { // 25-39
                        return ($tipo == 'A' && $age >= 25 && $age <= 39);
                    } elseif ($rowId <= 16) { // 40-59
                        return ($tipo == 'A' && $age >= 40 && $age <= 59);
                    } elseif ($rowId <= 18) { // 60+
                        return ($tipo == 'A' && $age >= 60);
                    }
                    return false;
                });
            }
        }

        if ($view == 'reversa') {
            if ($rowId !== 'all') {
                if (str_starts_with($rowId, 'diag:')) {
                    $diagKey = substr($rowId, 5);
                    $records = $records->filter(function($r) use ($diagKey) {
                        for ($i = 1; $i <= 7; $i++) {
                            $cod = $r->{"cod_$i"};
                            $diag = $r->{"diagnostico_$i"};
                            if (trim($cod . ' - ' . $diag) === trim($diagKey)) return true;
                        }
                        return false;
                    });
                } else {
                    $actCode = (int)$rowId;
                    $smActivities = [
                        ['pattern' => 'ENTREVISTA V.D', 'session' => 19],
                        ['pattern' => 'ENTREVISTA PSICOLÓGICA', 'session' => 20],
                        ['pattern' => 'INTERVENSIÓN EN CRISIS', 'session' => 21],
                        ['pattern' => 'FICHA DE VIGILANCIA EPIDEMIOLÓGICA.', 'session' => 22],
                        ['pattern' => 'PSICOTERAPIA INDIVIDUAL', 'session' => 23],
                        ['pattern' => 'PSICOTERAPIA EN GRUPO', 'session' => 24, 'participants' => 25],
                        ['pattern' => 'PSICOTERAPIA DE FAMILIA', 'session' => 26, 'participants' => 27],
                        ['pattern' => 'REUNIÓN COORDINACIÓN GRUPOS DE APOYO', 'session' => 28, 'participants' => 29],
                        ['pattern' => 'PRUEBAS PSICOLÓGICAS APLICADAS', 'session' => 30],
                        ['pattern' => 'REUNIÓN DE TRABAJO COMUNITARIO', 'session' => 31, 'participants' => 32],
                        ['pattern' => 'CAPACITACIONES BRINDADAS', 'session' => 33, 'participants' => 34],
                        ['pattern' => 'CAPACITACIONES RECIBIDAS', 'session' => 35],
                        ['pattern' => 'CHARLAS BRINDADAS', 'session' => 36, 'participants' => 37],
                        ['pattern' => 'ORGANIZACIÓN Y FORTALECIMIENTO DE GRUPO', 'session' => 38],
                        ['pattern' => 'CONSEJERIA VIH/SIDA', 'session' => 39],
                        ['pattern' => 'REFERENCIAS RECIBIDAS', 'session' => 40],
                        ['pattern' => 'REFERENCIAS ENVIADAS', 'session' => 41],
                        ['pattern' => 'TAMIZAJE (+)', 'session' => 42],
                        ['pattern' => 'TAMIZAJE (-)', 'session' => 43],
                    ];

                    $activity = collect($smActivities)->first(function($a) use ($actCode) {
                        return $a['session'] == $actCode || (isset($a['participants']) && $a['participants'] == $actCode);
                    });

                    if ($activity) {
                        $patternNormal = preg_replace('/\s+/', ' ', trim($this->quitarAcentos(strtoupper($activity['pattern']))));
                        $records = $records->filter(function($r) use ($patternNormal) {
                            for ($i = 1; $i <= 7; $i++) {
                                $diag = $r->{"diagnostico_$i"};
                                if (empty($diag)) continue;
                                $textNormal = preg_replace('/\s+/', ' ', trim($this->quitarAcentos(strtoupper($diag))));
                                if ($textNormal === $patternNormal) return true;
                            }
                            return false;
                        });
                    }
                }
            }
        }

        return response()->json([
            'records' => $records->values(),
            'count' => $records->count(),
            'smSearchStrings' => $smSearchStrings
        ]);
    }

    /**
     * Helper para remover acentos y caracteres especiales antes de comparar
     */
    private function quitarAcentos($string)
    {
        $string = str_replace(
            ['Á', 'É', 'Í', 'Ó', 'Ú', 'ñ', 'Ñ'],
            ['A', 'E', 'I', 'O', 'U', 'n', 'N'],
            $string
        );
        return $string;
    }
}
