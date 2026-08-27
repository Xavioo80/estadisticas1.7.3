<?php

namespace Database\Seeders;

use App\Models\LoginSetting;
use Illuminate\Database\Seeder;

class LoginSettingSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LoginSetting::updateOrCreate(
            ['id' => 1],
            [
                'title' => 'Estadísticas 1.5',
                'primary_color' => '#2563eb', // Azul moderno
                'secondary_color' => '#1e40af', // Azul oscuro
                'logo_path' => null,
                'background_image_path' => null,
            ]
        );
    }
}
