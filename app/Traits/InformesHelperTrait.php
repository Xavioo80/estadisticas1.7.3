<?php

namespace App\Traits;

use App\Models\Informe;
use App\Models\RegistroGlobal;

trait InformesHelperTrait
{
    /**
     * Normaliza una cadena para comparaciones: mayúsculas, sin tildes, sin espacios, sin símbolos innecesarios
     */
    private function normalizeForMatch($string): string
    {
        if (empty($string))
            return '';
        
        // Convertir a mayúsculas
        $string = mb_strtoupper((string)$string, 'UTF-8');
        
        // Quitar acentos
        $string = str_replace(
            ['Á', 'É', 'Í', 'Ó', 'Ú', 'Ü', 'Ñ'],
            ['A', 'E', 'I', 'O', 'U', 'U', 'N'],
            $string
        );
        
        // Quitar espacios y algunos símbolos de puntuación (.,;()[])
        // Pero mantenemos + y - para casos como TAMIZAJE (+) o rangos X60-X69
        $string = preg_replace('/[\s\.,;\(\)\[\]]/', '', $string);
        
        return $string;
    }

    /**
     * Helper para obtener años/meses disponibles en la tabla de informes
     */
    private function getAnosMesesDisponiblesInformes(): array
    {
        $currentYear = (int)date('Y');
        $anos = $this->getAnosDisponibles();
        $anoDefault = $anos->contains($currentYear) ? $currentYear : ($anos->first() ?? $currentYear);

        $mesMap = [
            1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO',
            4 => 'ABRIL', 5 => 'MAYO', 6 => 'JUNIO',
            7 => 'JULIO', 8 => 'AGOSTO', 9 => 'SEPTIEMBRE',
            10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE',
        ];

        $meses = $this->getMesesDisponibles($anoDefault)->toArray();
        if (empty($meses) && $anoDefault == $currentYear) {
            $meses = [$mesMap[(int)date('n')]];
        }

        $mesDefault = !empty($meses)
            ? end($meses)
            : ($anoDefault == $currentYear ? $mesMap[(int)date('n')] : 'ENERO');
        reset($meses);

        return compact('anos', 'anoDefault', 'meses', 'mesDefault');
    }

    public function getJornadasDisponibles(): \Illuminate\Support\Collection
    {
        return \Illuminate\Support\Facades\Cache::remember('rg_distinct_jornadas', 1800, function() {
            return RegistroGlobal::distinct()
                ->whereNotNull('jornada')
                ->where('jornada', '!=', '')
                ->orderBy('jornada')
                ->pluck('jornada');
        });
    }

    public function getProfesionesDisponibles(): \Illuminate\Support\Collection
    {
        return \Illuminate\Support\Facades\Cache::remember('rg_distinct_profesiones', 1800, function() {
            return RegistroGlobal::distinct()
                ->whereNotNull('prof')
                ->where('prof', '!=', '')
                ->orderBy('prof')
                ->pluck('prof');
        });
    }

    public function getAnosDisponibles(): \Illuminate\Support\Collection
    {
        return \Illuminate\Support\Facades\Cache::remember('rg_distinct_anos', 1800, function() {
            $currentYear = (int)date('Y');
            $dbAnos = RegistroGlobal::distinct()
                ->whereNotNull('ano')
                ->where('ano', '>', 1900)
                ->orderBy('ano', 'desc')
                ->pluck('ano')
                ->toArray();
            
            $rangeAnos = range($currentYear - 4, $currentYear + 5);
            $anos = collect(array_unique(array_merge($dbAnos, $rangeAnos)))->sortDesc()->values();
            return $anos->isEmpty() ? collect([$currentYear]) : $anos;
        });
    }

    public function getMesesDisponibles($ano): \Illuminate\Support\Collection
    {
        $mesMap = [
            'ENERO' => 1, 'FEBRERO' => 2, 'MARZO' => 3, 'ABRIL' => 4, 'MAYO' => 5, 'JUNIO' => 6,
            'JULIO' => 7, 'AGOSTO' => 8, 'SEPTIEMBRE' => 9, 'OCTUBRE' => 10, 'NOVIEMBRE' => 11, 'DICIEMBRE' => 12
        ];
        return \Illuminate\Support\Facades\Cache::remember("rg_distinct_meses_{$ano}", 1800, function() use ($ano, $mesMap) {
            $raw = RegistroGlobal::where('ano', $ano)
                ->whereNotNull('mes')
                ->where('mes', '!=', '')
                ->distinct()
                ->pluck('mes')
                ->toArray();
            return collect($raw)->filter()->map(fn($m) => strtoupper(trim($m ?? '')))
                ->unique()
                ->sort(fn($a, $b) => ($mesMap[$a] ?? 0) <=> ($mesMap[$b] ?? 0))
                ->values();
        });
    }

    /**
     * Resuelve el mes por defecto cuando no se recibe ninguno.
     *
     * @param  string $ano   Año a resolver
     * @param  bool   $useRG Si true, usa RegistroGlobal en lugar de Informe
     */
    private function resolverMesPorDefecto(string $ano, bool $useRG = false): string
    {
        $mesMap = [
            1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO',
            4 => 'ABRIL', 5 => 'MAYO', 6 => 'JUNIO',
            7 => 'JULIO', 8 => 'AGOSTO', 9 => 'SEPTIEMBRE',
            10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE',
        ];
        $mesOrder = [
            'DICIEMBRE' => 12, 'NOVIEMBRE' => 11, 'OCTUBRE' => 10,
            'SEPTIEMBRE' => 9, 'AGOSTO' => 8, 'JULIO' => 7,
            'JUNIO' => 6, 'MAYO' => 5, 'ABRIL' => 4,
            'MARZO' => 3, 'FEBRERO' => 2, 'ENERO' => 1
        ];
        $currentMonth = $mesMap[(int)date('n')];

        /** @var \Illuminate\Database\Eloquent\Model $modelClass */
        $modelClass = $useRG ? RegistroGlobal::class : Informe::class;

        // Obtener meses únicos con datos para el año (Ultra rápido vía distinct)
        $meses = $modelClass::where('ano', $ano)
            ->whereNotNull('mes')
            ->where('mes', '!=', '')
            ->distinct()
            ->pluck('mes')
            ->toArray();

        if (empty($meses) && !$useRG) {
            $meses = RegistroGlobal::where('ano', $ano)
                ->whereNotNull('mes')
                ->where('mes', '!=', '')
                ->distinct()
                ->pluck('mes')
                ->toArray();
        }

        if (!empty($meses)) {
            $latestMonth = null;
            $maxScore = -1;
            foreach ($meses as $m) {
                $upper = strtoupper(trim($m));
                $score = $mesOrder[$upper] ?? -1;
                if ($score > $maxScore) {
                    $maxScore = $score;
                    $latestMonth = $upper;
                }
            }
            if ($latestMonth) {
                return $latestMonth;
            }
        }

        return $currentMonth;
    }

    public function resolveColumnaProfesion($prof, $medico = '', bool $force = false): ?int
    {
        $prof = strtoupper(trim($prof ?? ''));
        $medico = strtoupper(trim($medico ?? ''));

        $mapping = [
            'ENFERMERAS AUXILIARES' => 1,
            'ENFERMERA AUXILIAR' => 1,
            'LICENCIADA EN ENFERMERIA' => 2,
            'LICENCIADAS EN ENFERMERIA' => 2,
            'ENFERMERA PROFESIONAL' => 2,
            'NUTRICION' => 2,
            'NUTRICIÓN' => 2,
            'LICENCIADA EN NUTRICION' => 2,
            'LICENCIADAS EN NUTRICION' => 2,
            'LICENCIADA EN NUTRICIÓN' => 2,
            'LICENCIADAS EN NUTRICIÓN' => 2,
            'NUTRICIONISTA' => 2,
            'PSICOLOGIA' => 2,
            'PSICOLOGÍA' => 2,
            'PSICOLOGO' => 2,
            'PSICÓLOGO' => 2,
            'CONSEJERIA' => 2,
            'CONSEJERÍA' => 2,
            'SALUD MENTAL' => 2,
            'MEDICO GENERAL' => 3,
            'MEDICO ESPECIALISTA' => 4,
            'MÉDICO ESPECIALISTA' => 4,
            'PSIQUIATRA' => 4,
            'PSIQUIATRIA' => 4,
            'PSIQUIATRÍA' => 4,
        ];
        $omitir = ['TRABAJO SOCIAL', 'ODONTOLOGIA'];

        // Si el nombre del médico o la profesión menciona especialista
        if (str_contains($medico, 'GINECOL') || str_contains($medico, 'PEDIATR') || str_contains($medico, 'PSIQUIAT') || str_contains($medico, 'ESPECIALISTA')) {
            return 4;
        }

        if ($prof === 'MÉDICO GENERAL' || $prof === 'MÉDICO ESPECIALISTA' || str_contains($prof, 'ESPECIALISTA')) {
            return 4;
        }

        if ($prof === 'MEDICO GENERAL') {
            return 3;
        }

        if (isset($mapping[$prof])) {
            return $mapping[$prof];
        }

        if (str_contains($prof, 'AUXILIAR')) return 1;
        if (str_contains($prof, 'ENFERMER') || str_contains($prof, 'NUTRICI') || str_contains($prof, 'PSICOLOG')) return 2;
        if (str_contains($prof, 'ESPECIALISTA') || str_contains($prof, 'PSIQUIATR') || str_contains($prof, 'GINECOL') || str_contains($prof, 'PEDIATR')) return 4;
        if (!$force && in_array($prof, $omitir)) return null;

        return 4;
    }

    public function cleanDiag($str): string
    {
        $s = strtoupper(trim((string)$str));
        return str_replace(['Á','É','Í','Ó','Ú','Ñ','Ü'], ['A','E','I','O','U','N','U'], $s);
    }
}
