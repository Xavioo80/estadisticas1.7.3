<?php

namespace App\Services;

use App\Models\RegistroGlobal;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Cache;

class RegistroGlobalQueryService
{
    /**
     * Obtener registros con filtros optimizados
     */
    public function getRegistrosConFiltros(array $filtros, int $cacheMinutes = 30): \Illuminate\Support\Collection
    {
        $cacheKey = $this->generateCacheKey($filtros);
        
        return Cache::remember($cacheKey, $cacheMinutes * 60, function () use ($filtros) {
            $query = RegistroGlobal::query();
            
            // Aplicar filtros de forma optimizada
            $this->aplicarFiltros($query, $filtros);
            
            // Eager loading para relaciones importantes
            $query->with(['medico', 'colonia', 'diagnosticos'])
                  ->orderBy('fecha', 'desc')
                  ->orderBy('medico', 'asc')
                  ->orderBy('numero', 'asc');
            
            return $query->get();
        });
    }
    
    /**
     * Obtener estadísticas optimizadas
     */
    public function getEstadisticasOptimizadas(array $filtros): array
    {
        $cacheKey = $this->generateCacheKey($filtros, 'estadisticas');
        
        return Cache::remember($cacheKey, 60 * 60, function () use ($filtros) {
            $query = RegistroGlobal::query();
            $this->aplicarFiltros($query, $filtros);
            
            // Consultas optimizadas para estadísticas
            $totalRegistros = $query->count();
            $totalMedicos = $query->distinct('medico')->count('medico');
            $totalDiagnosticos = $this->contarDiagnosticos($query);
            $totalMenores5 = $query->where('edad', '<', 5)->count();
            
            return [
                'total_registros' => $totalRegistros,
                'total_medicos' => $totalMedicos,
                'total_diagnosticos' => $totalDiagnosticos,
                'total_menores_5' => $totalMenores5,
                'registros_hoy' => RegistroGlobal::whereDate('fecha', now()->toDateString())->count()
            ];
        });
    }
    
    /**
     * Obtener valores únicos para filtros con cache
     */
    public function getValoresUnicos(string $campo, array $filtros = []): \Illuminate\Support\Collection
    {
        $cacheKey = "valores_unicos_{$campo}_" . md5(serialize($filtros));
        
        return Cache::remember($cacheKey, 60 * 60, function () use ($campo, $filtros) {
            $query = RegistroGlobal::query();
            
            // Aplicar filtros base
            if (!empty($filtros['ano'])) {
                $query->where('ano', $filtros['ano']);
            }
            
            if (!empty($filtros['mes'])) {
                $query->where('mes', $filtros['mes']);
            }
            
            if (!empty($filtros['fecha_calendario'])) {
                $query->whereDate('fecha', $filtros['fecha_calendario']);
            }
            
            return $query->distinct($campo)
                ->whereNotNull($campo)
                ->where($campo, '!=', '')
                ->orderBy($campo)
                ->pluck($campo);
        });
    }
    
    /**
     * Obtener registros agrupados por fecha para la vista principal
     */
    public function getRegistrosAgrupadosPorFecha(array $filtros): \Illuminate\Support\Collection
    {
        $cacheKey = $this->generateCacheKey($filtros, 'agrupados');
        
        return Cache::remember($cacheKey, 30 * 60, function () use ($filtros) {
            // Primero obtener fechas agrupadas
            $fechasQuery = RegistroGlobal::query();
            $this->aplicarFiltros($fechasQuery, $filtros);
            
            $fechasAgrupadas = $fechasQuery->select('fecha')
                ->selectRaw('COUNT(*) as total_atenciones_fecha')
                ->whereNotNull('fecha')
                ->whereNotNull('medico')
                ->where('medico', '!=', '')
                ->groupBy('fecha')
                ->orderBy('fecha', 'desc')
                ->get();
            
            // Para cada fecha, obtener médicos y estadísticas
            return $fechasAgrupadas->map(function($fechaGrupo) use ($filtros) {
                return $this->getMedicosPorFecha($fechaGrupo->fecha, $filtros);
            });
        });
    }
    
    /**
     * Obtener médicos para una fecha específica
     */
    private function getMedicosPorFecha(string $fecha, array $filtros): object
    {
        $medicosQuery = RegistroGlobal::query()
            ->select(['medico'])
            ->selectRaw('medico as nom_med')
            ->selectRaw('"" as cod_med')
            ->selectRaw('MAX(jornada) as jornada')
            ->selectRaw('COUNT(*) as total_registros')
            ->selectRaw('(
                SUM(CASE WHEN cod_1 IS NOT NULL AND cod_1 != "" AND cod_1 != "N" THEN 1 ELSE 0 END) +
                SUM(CASE WHEN cod_2 IS NOT NULL AND cod_2 != "" AND cod_2 != "N" THEN 1 ELSE 0 END) +
                SUM(CASE WHEN cod_3 IS NOT NULL AND cod_3 != "" AND cod_3 != "N" THEN 1 ELSE 0 END) +
                SUM(CASE WHEN cod_4 IS NOT NULL AND cod_4 != "" AND cod_4 != "N" THEN 1 ELSE 0 END) +
                SUM(CASE WHEN cod_5 IS NOT NULL AND cod_5 != "" AND cod_5 != "N" THEN 1 ELSE 0 END) +
                SUM(CASE WHEN cod_6 IS NOT NULL AND cod_6 != "" AND cod_6 != "N" THEN 1 ELSE 0 END) +
                SUM(CASE WHEN cod_7 IS NOT NULL AND cod_7 != "" AND cod_7 != "N" THEN 1 ELSE 0 END)
            ) as total_diagnosticos')
            ->selectRaw('SUM(CASE WHEN edad < 5 THEN 1 ELSE 0 END) as total_menores_5')
            ->where('fecha', $fecha)
            ->whereNotNull('medico')
            ->where('medico', '!=', '');
        
        // Aplicar filtros de médico y jornada
        if (!empty($filtros['medico'])) {
            $medicosQuery->where('medico', $filtros['medico']);
        }
        
        if (!empty($filtros['jornada'])) {
            $medicosQuery->where('jornada', $filtros['jornada']);
        }
        
        $medicos = $medicosQuery
            ->groupBy(['medico'])
            ->orderBy('medico', 'asc')
            ->get();
        
        // Asignar valores por defecto
        foreach ($medicos as $medico) {
            $medico->total_embarazadas = 0;
            $medico->total_embarazos_adolescentes = 0;
        }
        
        return (object)[
            'fecha' => $fecha,
            'total_atenciones_fecha' => $medicos->sum('total_registros'),
            'medicos' => $medicos
        ];
    }
    
    /**
     * Contar diagnósticos de forma optimizada
     */
    private function contarDiagnosticos(Builder $query): int
    {
        return $query->selectRaw('
            SUM(
                CASE WHEN cod_1 IS NOT NULL AND cod_1 != "" AND cod_1 != "N" THEN 1 ELSE 0 END +
                CASE WHEN cod_2 IS NOT NULL AND cod_2 != "" AND cod_2 != "N" THEN 1 ELSE 0 END +
                CASE WHEN cod_3 IS NOT NULL AND cod_3 != "" AND cod_3 != "N" THEN 1 ELSE 0 END +
                CASE WHEN cod_4 IS NOT NULL AND cod_4 != "" AND cod_4 != "N" THEN 1 ELSE 0 END +
                CASE WHEN cod_5 IS NOT NULL AND cod_5 != "" AND cod_5 != "N" THEN 1 ELSE 0 END +
                CASE WHEN cod_6 IS NOT NULL AND cod_6 != "" AND cod_6 != "N" THEN 1 ELSE 0 END +
                CASE WHEN cod_7 IS NOT NULL AND cod_7 != "" AND cod_7 != "N" THEN 1 ELSE 0 END
            ) as total_diagnosticos
        ')->value('total_diagnosticos') ?? 0;
    }
    
    /**
     * Aplicar filtros a una consulta
     */
    private function aplicarFiltros(Builder $query, array $filtros): void
    {
        // Filtros de fecha
        if (!empty($filtros['fecha_calendario'])) {
            $query->whereDate('fecha', $filtros['fecha_calendario']);
        } elseif (!empty($filtros['ano']) && !empty($filtros['mes'])) {
            $query->where('ano', $filtros['ano'])
                  ->where('mes', $filtros['mes']);
        } elseif (!empty($filtros['ano'])) {
            $query->where('ano', $filtros['ano']);
        }
        
        // Filtros de médico
        if (!empty($filtros['medico'])) {
            $query->where('medico', $filtros['medico']);
        }
        
        // Filtros de jornada
        if (!empty($filtros['jornada'])) {
            $query->where('jornada', $filtros['jornada']);
        }
        
        // Filtros de profesión
        if (!empty($filtros['prof'])) {
            $query->where('prof', $filtros['prof']);
        }
        
        // Filtros de colonia
        if (!empty($filtros['cod_col'])) {
            $query->where('cod_col', $filtros['cod_col']);
        }
        
        // Filtros de sexo
        if (!empty($filtros['sexo'])) {
            $query->where('sexo', $filtros['sexo']);
        }
        
        // Filtros de edad
        if (!empty($filtros['edad_min'])) {
            $query->where('edad', '>=', $filtros['edad_min']);
        }
        
        if (!empty($filtros['edad_max'])) {
            $query->where('edad', '<=', $filtros['edad_max']);
        }
    }
    
    /**
     * Generar clave de cache única para filtros
     */
    private function generateCacheKey(array $filtros, string $prefix = 'registros'): string
    {
        $filtrosNormalizados = $this->normalizarFiltros($filtros);
        $hash = md5(serialize($filtrosNormalizados));
        
        return "{$prefix}_{$hash}";
    }
    
    /**
     * Normalizar filtros para cache consistente
     */
    private function normalizarFiltros(array $filtros): array
    {
        $filtrosNormalizados = [];
        
        // Solo incluir filtros que tengan valor
        $camposClave = ['ano', 'mes', 'fecha_calendario', 'medico', 'jornada', 'prof', 'cod_col', 'sexo'];
        
        foreach ($camposClave as $campo) {
            if (!empty($filtros[$campo])) {
                $filtrosNormalizados[$campo] = $filtros[$campo];
            }
        }
        
        // Añadir rangos de edad si existen
        if (!empty($filtros['edad_min']) || !empty($filtros['edad_max'])) {
            $filtrosNormalizados['edad_min'] = $filtros['edad_min'] ?? 0;
            $filtrosNormalizados['edad_max'] = $filtros['edad_max'] ?? 150;
        }
        
        ksort($filtrosNormalizados);
        
        return $filtrosNormalizados;
    }
    
    /**
     * Limpiar cache relacionada con registros
     */
    public function limpiarCache(array $filtros = []): void
    {
        // Limpiar cache específica
        if (!empty($filtros)) {
            $cacheKey = $this->generateCacheKey($filtros);
            Cache::forget($cacheKey);
            Cache::forget($this->generateCacheKey($filtros, 'estadisticas'));
            Cache::forget($this->generateCacheKey($filtros, 'agrupados'));
        }
        
        // Limpiar cache general de valores únicos
        Cache::forget('valores_unicos_medico');
        Cache::forget('valores_unicos_jornada');
        Cache::forget('valores_unicos_prof');
        
        // Limpiar cache de años y meses
        Cache::forget('registros.years');
        Cache::forget('registros.months');
    }
}
