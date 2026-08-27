<?php

namespace Database\Seeders;

use App\Models\Diagnostico;
use Illuminate\Database\Seeder;

class MorbidityDiagnosticosSeeder extends Seeder
{
    public function run()
    {
        $diagnosticos = [
            ['codigo' => 'A01.0', 'patologia' => 'FIEBRE TIFOIDEA Y PARATIFOIDEA', 'categoria' => 'MORBILIDAD'],
            ['codigo' => 'M001', 'patologia' => 'FIEBRE DE ORIGEN DESCONOCIDO', 'categoria' => 'MORBILIDAD'],
            ['codigo' => 'M002', 'patologia' => 'DISENTERIA', 'categoria' => 'MORBILIDAD'],
            ['codigo' => 'A09.X', 'patologia' => 'DIARREAS', 'categoria' => 'MORBILIDAD'],
            ['codigo' => 'J16.4', 'patologia' => 'TUBERCULOSIS', 'categoria' => 'MORBILIDAD'],
            ['codigo' => 'B15.9', 'patologia' => 'HEPATITIS INFECCIOSA', 'categoria' => 'MORBILIDAD'],
            ['codigo' => 'A36.9', 'patologia' => 'DIFTERIA', 'categoria' => 'MORBILIDAD'],
            ['codigo' => 'A37.9', 'patologia' => 'TOSFERINA', 'categoria' => 'MORBILIDAD'],
            ['codigo' => 'M003', 'patologia' => 'INFECCION MENINGOCOCICA', 'categoria' => 'MORBILIDAD'],
            ['codigo' => 'A33.X', 'patologia' => 'TETANO NEONATORUM', 'categoria' => 'MORBILIDAD'],
            ['codigo' => 'A80.9', 'patologia' => 'POLIOMIELITIS AGUDA', 'categoria' => 'MORBILIDAD'],
            ['codigo' => 'B05.9', 'patologia' => 'SARAMPION', 'categoria' => 'MORBILIDAD'],
            ['codigo' => 'B06.9', 'patologia' => 'RUBEOLA', 'categoria' => 'MORBILIDAD'],
            ['codigo' => 'A90.X', 'patologia' => 'SOSP. DENGUE SIN SIGNOS DE ALARMA', 'categoria' => 'MORBILIDAD'],
            ['codigo' => 'M004', 'patologia' => 'SOSP. DENGUE CON SIGNOS DE ALARMA', 'categoria' => 'MORBILIDAD'],
            ['codigo' => 'M005', 'patologia' => 'DENGUE GRAVE', 'categoria' => 'MORBILIDAD'],
            ['codigo' => 'M006', 'patologia' => 'ZIKA', 'categoria' => 'MORBILIDAD'],
            ['codigo' => 'M007', 'patologia' => 'CHIKUNGUNYA', 'categoria' => 'MORBILIDAD'],
            ['codigo' => 'M008', 'patologia' => 'LUMBALGIA', 'categoria' => 'MORBILIDAD'],
            ['codigo' => 'M009', 'patologia' => 'HEMORROIDES', 'categoria' => 'MORBILIDAD'],
            ['codigo' => 'B54.X', 'patologia' => 'MALARIA', 'categoria' => 'MORBILIDAD'],
            ['codigo' => 'B55.1', 'patologia' => 'LEISHMANIASIS CUTANEA', 'categoria' => 'MORBILIDAD'],
            ['codigo' => 'A53.9', 'patologia' => 'SIFILIS', 'categoria' => 'MORBILIDAD'],
            ['codigo' => 'A54.9', 'patologia' => 'GONORREA', 'categoria' => 'MORBILIDAD'],
            ['codigo' => 'M010', 'patologia' => 'MICOSIS', 'categoria' => 'MORBILIDAD'],
            ['codigo' => 'M011', 'patologia' => 'VIH', 'categoria' => 'MORBILIDAD'],
            ['codigo' => 'M012', 'patologia' => 'PARASITOSIS INTESTINAL', 'categoria' => 'MORBILIDAD'],
            ['codigo' => 'M013', 'patologia' => 'TUMORES MALIGNOS', 'categoria' => 'MORBILIDAD'],
            ['codigo' => 'M014', 'patologia' => 'TUMORES BENIGNOS', 'categoria' => 'MORBILIDAD'],
            ['codigo' => 'M015', 'patologia' => 'CANCER IN SITU', 'categoria' => 'MORBILIDAD'],
            ['codigo' => 'M016', 'patologia' => 'BOCIO', 'categoria' => 'MORBILIDAD'],
            ['codigo' => 'E14.9', 'patologia' => 'DIABETES', 'categoria' => 'MORBILIDAD'],
            ['codigo' => 'M017', 'patologia' => 'ANEMIAS', 'categoria' => 'MORBILIDAD'],
            ['codigo' => 'AA', 'patologia' => 'VIOLENCIA DOMESTICA EN TODAS SUS FORMAS', 'categoria' => 'MORBILIDAD'],
            ['codigo' => 'F40-F49', 'patologia' => 'ANSIEDAD Y ESTRÉS', 'categoria' => 'MORBILIDAD'],
            ['codigo' => 'F10', 'patologia' => 'TRASTORNOS MENTALES Y DEL COMPORTAMIENTO DEBIDO AL ALCOHOL', 'categoria' => 'MORBILIDAD'],
            ['codigo' => 'F11-F19', 'patologia' => 'TRASTORNO DEBIDO A CONSUMO DE OTRAS DROGAS', 'categoria' => 'MORBILIDAD'],
        ];

        foreach ($diagnosticos as $diag) {
            Diagnostico::updateOrCreate(
            ['patologia' => $diag['patologia']],
                $diag
            );
        }
    }
}
