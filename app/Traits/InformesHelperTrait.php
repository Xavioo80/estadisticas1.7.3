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
        $dbAnos = RegistroGlobal::distinct()->orderBy('ano', 'desc')->pluck('ano')->toArray();
        
        // Crear un rango de años por defecto sin límite estricto
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
            1 => 'ENERO', 2 => 'FEBRERO', 3 => 'MARZO',
            4 => 'ABRIL', 5 => 'MAYO', 6 => 'JUNIO',
            7 => 'JULIO', 8 => 'AGOSTO', 9 => 'SEPTIEMBRE',
            10 => 'OCTUBRE', 11 => 'NOVIEMBRE', 12 => 'DICIEMBRE',
        ];

        $meses = Informe::where('ano', $anoDefault)->distinct()->pluck('mes')->toArray();

        if (empty($meses) && $anoDefault == $currentYear) {
            $meses = [$mesMap[(int)date('n')]];
        }

        $ordenMeses = array_flip($mesMap);
        usort($meses, function ($a, $b) use ($ordenMeses) {
            return ($ordenMeses[strtoupper($a)] ?? 0) <=> ($ordenMeses[strtoupper($b)] ?? 0);
        });

        $mesDefault = !empty($meses)
            ? end($meses)
            : ($anoDefault == $currentYear ? $mesMap[(int)date('n')] : 'ENERO');
        reset($meses);

        return compact('anos', 'anoDefault', 'meses', 'mesDefault');
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
        $currentMonth = $mesMap[(int)date('n')];

        /** @var \Illuminate\Database\Eloquent\Model $modelClass */
        $modelClass = $useRG ? RegistroGlobal::class : Informe::class;

        // Obtener el último mes con registros para el año seleccionado (ordenado de DICIEMBRE a ENERO)
        $lastWithData = $modelClass::where('ano', $ano)
            ->whereNotNull('mes')
            ->where('mes', '!=', '')
            ->orderByRaw("FIELD(UPPER(TRIM(mes)), 'ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE') DESC")
            ->first();

        if ($lastWithData && !empty($lastWithData->mes)) {
            return strtoupper(trim($lastWithData->mes));
        }

        // Si no hay datos en Informe, probar con RegistroGlobal
        $lastWithDataRG = RegistroGlobal::where('ano', $ano)
            ->whereNotNull('mes')
            ->where('mes', '!=', '')
            ->orderByRaw("FIELD(UPPER(TRIM(mes)), 'ENERO', 'FEBRERO', 'MARZO', 'ABRIL', 'MAYO', 'JUNIO', 'JULIO', 'AGOSTO', 'SEPTIEMBRE', 'OCTUBRE', 'NOVIEMBRE', 'DICIEMBRE') DESC")
            ->first();

        return ($lastWithDataRG && !empty($lastWithDataRG->mes)) ? strtoupper(trim($lastWithDataRG->mes)) : $currentMonth;
    }
}
