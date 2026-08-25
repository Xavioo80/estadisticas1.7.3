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
        Schema::create('informes', function (Blueprint $table) {
            // ID compuesto (ej: 123_1)
            $table->string('id')->primary();
            $table->unsignedBigInteger('registro_id')->index();
            $table->integer('diag_index')->index();

            // Campos heredados de RegistroGlobal
            $table->integer('ano')->index();
            $table->string('mes', 20)->index();
            $table->integer('numero')->nullable();
            $table->string('cm', 50)->nullable();
            $table->string('medico')->nullable()->index();
            $table->string('prof', 20)->nullable();
            $table->date('fecha')->nullable();
            $table->integer('se')->nullable();
            $table->string('exp', 50)->nullable();
            $table->string('sexo', 10)->nullable();
            $table->integer('edad')->nullable();
            $table->string('tipo', 10)->nullable();

            // Rangos y otros
            $table->string('rango', 20)->nullable();
            $table->string('rango_2', 20)->nullable();
            $table->string('rango_3', 20)->nullable();
            $table->string('rango_4', 20)->nullable();
            $table->string('rango_5', 20)->nullable();
            $table->string('cond', 5)->nullable();
            $table->string('cod_col', 20)->nullable();
            $table->string('colonia')->nullable();

            // Campos de diagnóstico específicos (unificados para el reporte)
            $table->string('cod', 20)->nullable()->index();
            $table->string('diagnostico')->nullable();
            $table->string('cond_diagnostico', 10)->nullable();
            $table->string('sg', 20)->nullable();

            // Referencias y jornada
            $table->string('referido_a')->nullable();
            $table->string('referido_de')->nullable();
            $table->string('pg_emb', 10)->nullable();
            $table->string('jornada', 20)->nullable();
            $table->string('sm', 10)->nullable();
            $table->string('sg2', 20)->nullable();

            $table->timestamps();

        // Clave foránea opcional si se quiere integridad a nivel DB
        // $table->foreign('registro_id')->references('id')->on('registros_globales')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('informes');
    }
};
