<?php

namespace App\Services;

use App\Repositories\Contracts\RegistroGlobalRepositoryInterface;
use Illuminate\Support\Facades\DB;

class RegistroGlobalPruebaService
{
    public function __construct(
        private RegistroGlobalRepositoryInterface $repository
    ) {}

    public function getRegistrosPorPeriodo(int $ano, string $mes)
    {
        return $this->repository->findByPeriodo($ano, $mes);
    }

    public function crearRegistro(array $data)
    {
        return DB::transaction(function () use ($data) {
            return $this->repository->create($data);
        });
    }

    public function actualizarRegistro(int $id, array $data)
    {
        return DB::transaction(function () use ($id, $data) {
            return $this->repository->update($id, $data);
        });
    }

    public function eliminarRegistro(int $id): bool
    {
        return DB::transaction(function () use ($id) {
            return $this->repository->delete($id);
        });
    }

    public function getAnosDisponibles()
    {
        return $this->repository->getYearsAvailable();
    }

    public function getMesesDisponibles(int $ano)
    {
        return $this->repository->getMonthsAvailable($ano);
    }

    public function expandirDiagnosticos($registros): array
    {
        $informes = [];
        
        foreach ($registros as $registro) {
            $attrs = $registro->getAttributes();
            for ($i = 1; $i <= 7; $i++) {
                $cod = $attrs["cod_$i"] ?? null;
                $diag = $attrs["diagnostico_$i"] ?? null;
                
                if (!empty($cod) || !empty($diag)) {
                    $informes[] = [
                        'id' => $attrs['id'] . '_' . $i,
                        'registro_global_id' => $attrs['id'],
                        'diagnostico_index' => $i,
                        'ano' => $attrs['ano'],
                        'mes' => $attrs['mes'],
                        'numero' => $attrs['numero'],
                        'cm' => $attrs['cm'],
                        'medico' => $attrs['medico'],
                        'prof' => $attrs['prof'],
                        'fecha' => !empty($attrs['fecha']) ? date('Y-m-d', strtotime($attrs['fecha'])) : null,
                        'se' => $attrs['se'],
                        'exp' => $attrs['exp'],
                        'sexo' => $attrs['sexo'],
                        'edad' => $attrs['edad'],
                        'tipo' => $attrs['tipo'],
                        'rango' => $attrs['rango'],
                        'rango_2' => $attrs['rango_2'],
                        'rango_3' => $attrs['rango_3'],
                        'rango_4' => $attrs['rango_4'],
                        'rango_5' => $attrs['rango_5'],
                        'cond' => $attrs['cond'],
                        'cod_col' => $attrs['cod_col'],
                        'colonia' => $attrs['colonia'],
                        'cod' => $cod,
                        'diagnostico' => $diag,
                        'cond_diagnostico' => $attrs["cond_$i"] ?? null,
                        'sg' => $attrs['sg'],
                        'referido_a' => $attrs['referido_a'],
                        'referido_de' => $attrs['referido_de'],
                        'pg_emb' => $attrs['pg_emb'],
                        'jornada' => $attrs['jornada'],
                        'sm' => $attrs['sm'],
                        'sg2' => $attrs['sg2'],
                    ];
                }
            }
        }
        
        return $informes;
    }
}
