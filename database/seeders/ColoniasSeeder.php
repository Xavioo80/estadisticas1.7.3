<?php

namespace Database\Seeders;

use App\Models\Colonia;
use Illuminate\Database\Seeder;

class ColoniasSeeder extends Seeder
{
    public function run(): void
    {
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Colonia::truncate();
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $colonias = [
            ['COD_COL' => 1, 'COLONIA' => 'IZAGUIRRE'],
            ['COD_COL' => 2, 'COLONIA' => 'VILLA EL MOLINON'],
            ['COD_COL' => 3, 'COLONIA' => 'ESPERANZA'],
            ['COD_COL' => 4, 'COLONIA' => 'BRISAS DEL VALLE'],
            ['COD_COL' => 5, 'COLONIA' => '28 DE MARZO'],
            ['COD_COL' => 6, 'COLONIA' => 'VILLA UNAH 1'],
            ['COD_COL' => 7, 'COLONIA' => 'VILLA UNAH 2'],
            ['COD_COL' => 8, 'COLONIA' => 'CENTROAMERICANA'],
            ['COD_COL' => 9, 'COLONIA' => 'RES MAYA'],
            ['COD_COL' => 10, 'COLONIA' => '13 DE JULIO'],
            ['COD_COL' => 11, 'COLONIA' => 'NUEVA ERA'],
            ['COD_COL' => 12, 'COLONIA' => 'LA ERA'],
            ['COD_COL' => 13, 'COLONIA' => 'TRAVESIA'],
            ['COD_COL' => 14, 'COLONIA' => 'BAO'],
            ['COD_COL' => 15, 'COLONIA' => 'ALTOS DE LA SOSA'],
            ['COD_COL' => 16, 'COLONIA' => 'MOLOLOA'],
            ['COD_COL' => 17, 'COLONIA' => 'QUEBRACHITOS'],
            ['COD_COL' => 18, 'COLONIA' => 'OJO DE AGUA'],
            ['COD_COL' => 19, 'COLONIA' => 'STA. MARIA'],
            ['COD_COL' => 20, 'COLONIA' => 'SITIO'],
            ['COD_COL' => 21, 'COLONIA' => 'SOSA'],
            ['COD_COL' => 22, 'COLONIA' => 'ESTADOS UNIDOS'],
            ['COD_COL' => 23, 'COLONIA' => 'TRINIDAD'],
            ['COD_COL' => 24, 'COLONIA' => '30 DE NOVIEMBRE'],
            ['COD_COL' => 25, 'COLONIA' => 'MOLINON'],
            ['COD_COL' => 26, 'COLONIA' => 'RES FLORIDA'],
            ['COD_COL' => 27, 'COLONIA' => 'AGUA BLANCA'],
            ['COD_COL' => 28, 'COLONIA' => 'LAS TABLAS'],
            ['COD_COL' => 29, 'COLONIA' => 'otros'],
            ['COD_COL' => 43, 'COLONIA' => 'RES ZARAHEMLA'],
            ['COD_COL' => 44, 'COLONIA' => 'SAN MIGUEL'],
            ['COD_COL' => 45, 'COLONIA' => 'ESCUELA 4 DE JUNIO'],
            ['COD_COL' => 46, 'COLONIA' => 'ESCUELA JUAN RAMON MOLINA'],
            ['COD_COL' => 47, 'COLONIA' => 'ESCUELA SAN MIGUEL DE HEREDIA'],
            ['COD_COL' => 48, 'COLONIA' => 'ESCUELA OSCAR A. FLORES'],
            ['COD_COL' => 49, 'COLONIA' => 'KINDER RAFAEL PINEDA PONCE'],
            ['COD_COL' => 50, 'COLONIA' => 'KINDER YOLANDA BRITO'],
            ['COD_COL' => 51, 'COLONIA' => 'ESCUELA RAMON MONTOYA'],
            ['COD_COL' => 52, 'COLONIA' => 'ESCUELA MIGUEL ANDONI'],
            ['COD_COL' => 53, 'COLONIA' => 'KINDER LA ERA'],
            ['COD_COL' => 54, 'COLONIA' => 'ESCUELA ESPAÑA'],
            ['COD_COL' => 55, 'COLONIA' => 'KINDER ROBERTO RAMON CASTILLO'],
            ['COD_COL' => 56, 'COLONIA' => 'ESCUELA JOSE MARIA CASCO'],
            ['COD_COL' => 57, 'COLONIA' => 'KINDER SAUL ZELAYA'],
            ['COD_COL' => 58, 'COLONIA' => 'ESCUELA ROCA FUERTE']
        ];

        foreach ($colonias as $colonia) {
            Colonia::updateOrCreate(
                ['COD_COL' => $colonia['COD_COL']], // Buscar por COD_COL
                $colonia // Crear o actualizar con estos datos
            );
        }
    }
}
