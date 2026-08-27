<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Referencia;

class ReferenciasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        Referencia::truncate();
        \Illuminate\Support\Facades\DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $referencias = [
            [
                'nombre' => 'HOSPITAL GENERAL SAN FELIPE',
                'tipo' => 'HOSPITAL GENERAL',
                'direccion' => 'Tegucigalpa, Francisco Morazán, Honduras',
                'telefono' => '2232-1234',
                'email' => 'contacto@sanfelipe.hn',
                'contacto' => 'Dr. Carlos Mendoza - Director Médico',
                'estado' => true
            ],
            [
                'nombre' => 'INSTITUTO NACIONAL CARDIOPULMONAR',
                'tipo' => 'INSTITUTO',
                'direccion' => 'Tegucigalpa, Francisco Morazán, Honduras',
                'telefono' => '2232-5678',
                'email' => 'info@cardiopulmonar.hn',
                'contacto' => 'Dr. Ana Rodríguez - Jefa de Cardiología',
                'estado' => true
            ],
            [
                'nombre' => 'HOSPITAL MATERNO INFANTIL',
                'tipo' => 'HOSPITAL ESPECIALIZADO',
                'direccion' => 'Tegucigalpa, Francisco Morazán, Honduras',
                'telefono' => '2232-9012',
                'email' => 'contacto@maternoinfantil.hn',
                'contacto' => 'Dra. María González - Directora',
                'estado' => true
            ],
            [
                'nombre' => 'HOSPITAL ESCUELA',
                'tipo' => 'HOSPITAL GENERAL',
                'direccion' => 'Tegucigalpa, Francisco Morazán, Honduras',
                'telefono' => '2232-3456',
                'email' => 'info@hospitalescuela.hn',
                'contacto' => 'Dr. Roberto Silva - Director General',
                'estado' => true
            ],
            [
                'nombre' => 'HOSPITAL PSIQUIATRICO MARIO MENDOZA',
                'tipo' => 'HOSPITAL PSIQUIATRICO',
                'direccion' => 'Tegucigalpa, Francisco Morazán, Honduras',
                'telefono' => '2232-7890',
                'email' => 'contacto@mariomendoza.hn',
                'contacto' => 'Dr. Luis Herrera - Director de Psiquiatría',
                'estado' => true
            ],
            [
                'nombre' => 'HOSPITAL PSIQUIATRICO SANTA ROSITA',
                'tipo' => 'HOSPITAL PSIQUIATRICO',
                'direccion' => 'Tegucigalpa, Francisco Morazán, Honduras',
                'telefono' => '2232-2345',
                'email' => 'info@santarosita.hn',
                'contacto' => 'Dra. Carmen López - Directora Médica',
                'estado' => true
            ],
            [
                'nombre' => 'HOSPITAL ECMA ROMERO DE CALLEJAS',
                'tipo' => 'HOSPITAL ESPECIALIZADO',
                'direccion' => 'Tegucigalpa, Francisco Morazán, Honduras',
                'telefono' => '2232-6789',
                'email' => 'contacto@ecmaromero.hn',
                'contacto' => 'Dr. Fernando Callejas - Director',
                'estado' => true
            ],
            [
                'nombre' => 'HOSPITAL DEL DIABETICO',
                'tipo' => 'HOSPITAL ESPECIALIZADO',
                'direccion' => 'Tegucigalpa, Francisco Morazán, Honduras',
                'telefono' => '2232-4567',
                'email' => 'info@diabetico.hn',
                'contacto' => 'Dr. Miguel Torres - Especialista en Endocrinología',
                'estado' => true
            ],
            [
                'nombre' => 'HOSPITAL DE PACIENTES RENALES',
                'tipo' => 'HOSPITAL ESPECIALIZADO',
                'direccion' => 'Tegucigalpa, Francisco Morazán, Honduras',
                'telefono' => '2232-8901',
                'email' => 'contacto@renales.hn',
                'contacto' => 'Dra. Patricia Morales - Nefróloga',
                'estado' => true
            ],
            [
                'nombre' => 'CIS ALONZO SUAZO',
                'tipo' => 'CENTRO DE SALUD',
                'direccion' => 'Tegucigalpa, Francisco Morazán, Honduras',
                'telefono' => '2232-1357',
                'email' => 'info@cisalonzo.hn',
                'contacto' => 'Dr. Alonzo Suazo - Director',
                'estado' => true
            ],
            [
                'nombre' => 'CLIPER',
                'tipo' => 'CLINICA',
                'direccion' => 'Tegucigalpa, Francisco Morazán, Honduras',
                'telefono' => '2232-2468',
                'email' => 'contacto@cliper.hn',
                'contacto' => 'Dr. Eduardo Martínez - Gerente Médico',
                'estado' => true
            ],
            [
                'nombre' => 'IHSS',
                'tipo' => 'INSTITUTO',
                'direccion' => 'Tegucigalpa, Francisco Morazán, Honduras',
                'telefono' => '2232-1111',
                'email' => 'info@ihss.hn',
                'contacto' => 'Dr. José Ramírez - Director Ejecutivo',
                'estado' => true
            ]
        ];

        foreach ($referencias as $referencia) {
            Referencia::updateOrCreate(
                ['nombre' => $referencia['nombre']], // Buscar por nombre
                $referencia // Crear o actualizar con estos datos
            );
        }
    }
}
