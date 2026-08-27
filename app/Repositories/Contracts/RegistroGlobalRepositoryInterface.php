<?php

namespace App\Repositories\Contracts;

use App\Models\RegistroGlobal;
use Illuminate\Support\Collection;

interface RegistroGlobalRepositoryInterface
{
    public function findByPeriodo(int $ano, string $mes): Collection;
    public function create(array $data): RegistroGlobal;
    public function update(int $id, array $data): RegistroGlobal;
    public function delete(int $id): bool;
    public function getYearsAvailable(): Collection;
    public function getMonthsAvailable(int $ano): Collection;
}
