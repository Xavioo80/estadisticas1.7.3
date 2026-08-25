<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('pacientes')) {
            Schema::create('pacientes', function (Blueprint $table) {
                $table->id();
                $table->string('nombre_completo', 255)->nullable();
                $table->string('dni', 50)->nullable()->index('idx_pacientes_dni');
                $table->string('dni_limpio', 50)->nullable()->index('idx_pacientes_dni_limpio');
                $table->string('fecha_nacimiento', 30)->nullable();
                $table->string('colonia', 150)->nullable();
                $table->integer('edad')->nullable();
                $table->string('departamento', 100)->nullable()->default('FRANCISCO MORAZAN');
                $table->string('municipio', 100)->nullable()->default('DISTRITO CENTRAL');
                $table->string('cod_municipio', 50)->nullable()->default('0801');
                $table->timestamps();
            });
        }

        // Índices de velocidad en tablas relacionadas
        try {
            if (Schema::hasTable('registros_globales')) {
                Schema::table('registros_globales', function (Blueprint $table) {
                    $table->index('identidad', 'idx_registros_globales_identidad_fast');
                    $table->index('exp', 'idx_registros_globales_exp_fast');
                });
            }
        } catch (\Throwable $e) {}

        try {
            if (Schema::hasTable('notificaciones_svs')) {
                Schema::table('notificaciones_svs', function (Blueprint $table) {
                    $table->index('no_documento', 'idx_notificaciones_svs_no_doc_fast');
                });
            }
        } catch (\Throwable $e) {}
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('pacientes');
    }
};
