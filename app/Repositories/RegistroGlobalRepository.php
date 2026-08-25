<?php

namespace App\Repositories;

use App\Models\RegistroGlobal;
use App\Repositories\Contracts\RegistroGlobalRepositoryInterface;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Cache;

class RegistroGlobalRepository implements RegistroGlobalRepositoryInterface
{
    public function findByPeriodo(int $ano, string $mes): Collection
    {
        $cacheKey = "registros.{$ano}.{$mes}";
        
        return Cache::remember($cacheKey, 1800, function () use ($ano, $mes) {
            return RegistroGlobal::where('ano', $ano)
                ->where('mes', $mes)
                ->orderBy('fecha', 'desc')
                ->get();
        });
    }

    public function create(array $data): RegistroGlobal
    {
        $registro = RegistroGlobal::create($data);
        $this->clearCache($registro->ano, $registro->mes);
        return $registro;
    }

    public function update(int $id, array $data): RegistroGlobal
    {
        $registro = RegistroGlobal::findOrFail($id);
        $registro->update($data);
        $this->clearCache($registro->ano, $registro->mes);
        return $registro;
    }

    public function delete(int $id): bool
    {
        $registro = RegistroGlobal::findOrFail($id);
        $ano = $registro->ano;
        $mes = $registro->mes;
        $deleted = $registro->delete();
        $this->clearCache($ano, $mes);
        return $deleted;
    }

    public function getYearsAvailable(): Collection
    {
        return Cache::remember('registros.years', 3600, function () {
            return RegistroGlobal::select('ano')
                ->distinct()
                ->whereNotNull('ano')
                ->orderBy('ano', 'desc')
                ->pluck('ano');
        });
    }

    public function getMonthsAvailable(int $ano): Collection
    {
        return Cache::remember("registros.months.{$ano}", 3600, function () use ($ano) {
            return RegistroGlobal::where('ano', $ano)
                ->select('mes')
                ->distinct()
                ->whereNotNull('mes')
                ->orderBy('mes')
                ->pluck('mes');
        });
    }

    private function clearCache(int $ano, string $mes): void
    {
        Cache::forget("registros.{$ano}.{$mes}");
        Cache::forget("informes.{$ano}.{$mes}");
        Cache::forget('registros.years');
        Cache::forget("registros.months.{$ano}");
    }
}
