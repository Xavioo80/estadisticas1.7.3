<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('alerta_not_enlaces')) {
            Schema::create('alerta_not_enlaces', function (Blueprint $table) {
                $table->id();
                $table->string('titulo');
                $table->text('url');
                $table->string('icono')->default('bi bi-globe');
                $table->string('descripcion')->nullable();
                $table->integer('orden')->default(0);
                $table->boolean('is_active')->default(true);
                $table->timestamps();
            });

            // Insertar los enlaces iniciales requeridos por el usuario
            DB::table('alerta_not_enlaces')->insert([
                [
                    'titulo' => 'Planilla Notificaciones (Google Sheets)',
                    'url' => 'https://docs.google.com/spreadsheets/d/1Vm_L789mmEOXcWfGYK4Ic-dGXxOuWBuBvfP6GS72dD8/edit?gid=0#gid=0',
                    'icono' => 'bi bi-file-earmark-spreadsheet-fill',
                    'descripcion' => 'Planilla de vigilancia epidemiológica y seguimiento SNVS',
                    'orden' => 1,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
                [
                    'titulo' => 'Correo Electrónico (Gmail)',
                    'url' => 'https://mail.google.com/',
                    'icono' => 'bi bi-envelope-at-fill',
                    'descripcion' => 'Bandeja de entrada y notificaciones oficiales por correo',
                    'orden' => 2,
                    'is_active' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ],
            ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alerta_not_enlaces');
    }
};
