<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            DiagnosticosSeeder::class,
            MedicosSeeder::class,
            ColoniasSeeder::class,
            ReferenciasSeeder::class,
            NuevosDiagnosticosSeeder::class,
            LoginSettingSeeder::class,
        ]);

        // Usuario Administrador por defecto
        User::updateOrCreate(
            ['email' => 'admin@admin.com'],
            [
                'name' => 'Administrador Sistema',
                'password' => \Hash::make('password'),
                'role' => 'administrador'
            ]
        );

        // Usuario Analista por defecto
        User::updateOrCreate(
            ['email' => 'analista@analista.com'],
            [
                'name' => 'Analista de Datos',
                'password' => \Hash::make('password'),
                'role' => 'analista'
            ]
        );
    }
}
