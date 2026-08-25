<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        if (!Schema::hasTable('adolescentes')) {
            Schema::create('adolescentes', function (Blueprint $table) {
                $table->id();
                $table->string('no_expediente')->nullable()->index();
                $table->string('nombre_completo')->nullable();
                $table->string('sexo')->nullable();
                $table->date('fecha_nacimiento')->nullable();
                $table->date('fecha_ingreso')->nullable();
                $table->integer('edad')->nullable();
                $table->string('numero_identidad')->nullable()->index();
                $table->string('colonia')->nullable();
                $table->string('medico_atencion')->nullable();
                $table->string('usuario_registro')->nullable();
                $table->string('nombre_tutor')->nullable();
                $table->text('direccion_completa')->nullable();
                $table->string('numero_telefono')->nullable();
                $table->string('estado_civil')->nullable();
                $table->string('escolaridad')->nullable();
                $table->integer('anios_cursados')->nullable();
                $table->string('ocupacion')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('adolescentes_control') && !Schema::hasTable('adolescentes_seguimientos')) {
            Schema::create('adolescentes_control', function (Blueprint $table) {
                $table->id();
                $table->string('no_expediente')->nullable()->index();
                $table->string('nombre_completo')->nullable();
                $table->string('sexo')->nullable();
                $table->date('fecha_nacimiento')->nullable();
                $table->integer('edad')->nullable();
                $table->string('numero_identidad')->nullable()->index();
                $table->string('colonia')->nullable();
                $table->string('medico_atencion')->nullable();
                $table->string('usuario_registro')->nullable();
                $table->string('nombre_tutor')->nullable();
                $table->text('direccion_completa')->nullable();
                $table->string('numero_telefono')->nullable();
                $table->string('estado_civil')->nullable();
                $table->string('escolaridad')->nullable();
                $table->integer('anios_cursados')->nullable();
                $table->string('ocupacion')->nullable();
                $table->date('fecha_consulta')->nullable();
                $table->text('diagnostico_seguimiento')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('adolescentes_control');
        Schema::dropIfExists('adolescentes_seguimientos');
        Schema::dropIfExists('adolescentes');
    }
};
