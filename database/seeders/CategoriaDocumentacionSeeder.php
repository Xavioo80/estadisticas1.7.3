<?php

namespace Database\Seeders;

use App\Models\CategoriaDocumentacion;
use Illuminate\Database\Seeder;

class CategoriaDocumentacionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $categorias = [
            ['nombre' => '0.1 Hoja de Control', 'color' => 'secondary'],
            ['nombre' => '1. AT2R', 'color' => 'primary'],
            ['nombre' => '2. Hora Medico', 'color' => 'info'],
            ['nombre' => '3. Vacunas (VAC_2, G.E, INFL_2, DESP_2, C...)', 'color' => 'success'],
            ['nombre' => '4. Actividades Odontologicas y Hora Od...', 'color' => 'warning'],
            ['nombre' => '5. TB9 y TB3', 'color' => 'danger'],
            ['nombre' => '6. Morbilidad', 'color' => 'info'],
            ['nombre' => '7. Voluntarios de Salud (VS-2005)', 'color' => 'success'],
            ['nombre' => '8. Salud Mental (SM1, SM3, SM2)', 'color' => 'primary'],
            ['nombre' => '9. Consejerias de Familia', 'color' => 'secondary'],
            ['nombre' => '10. Trasmisible (TRANS-2)', 'color' => 'danger'],
            ['nombre' => '11. Zoonosis (RAB-05)', 'color' => 'warning'],
            ['nombre' => '12. ITS-2', 'color' => 'info'],
            ['nombre' => '13. CS-2', 'color' => 'primary'],
            ['nombre' => '14. Madre e Hijo', 'color' => 'success'],
            ['nombre' => '15. Defunciones comunitarias', 'color' => 'danger'],
            ['nombre' => '16. Informe 3.1', 'color' => 'secondary'],
            ['nombre' => '17. Siafi', 'color' => 'warning'],
            ['nombre' => '18. VPH', 'color' => 'primary'],
            ['nombre' => '19. NUTRICIÓN', 'color' => 'success'],
            ['nombre' => 'CALENDARIO EPIDEMIOLOGICO 2026', 'color' => 'danger'],
        ];

        foreach ($categorias as $cat) {
            CategoriaDocumentacion::updateOrCreate(['nombre' => $cat['nombre']], $cat);
        }
    }
}
