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
        Schema::create('registros_globales', function (Blueprint $table) {
            $table->id();
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

            // Rangos (vienen de lógica de negocio)
            $table->string('rango', 20)->nullable();
            $table->string('rango_2', 20)->nullable();
            $table->string('rango_3', 20)->nullable();
            $table->string('rango_4', 20)->nullable();
            $table->string('rango_5', 20)->nullable();
            $table->string('cond', 5)->nullable();

            // Ubicación
            $table->string('cod_col', 20)->nullable();
            $table->string('colonia')->nullable();

            // Diagnósticos (hasta 7 según el Observer y Modelo)
            for ($i = 1; $i <= 7; $i++) {
                $table->string("cod_$i", 20)->nullable();
                $table->string("diagnostico_$i")->nullable();
                $table->string("cond_$i", 10)->nullable();
            }

            $table->string('sg', 20)->nullable();
            $table->string('referido_a')->nullable();
            $table->string('referido_de')->nullable();
            $table->string('pg_emb', 10)->nullable();
            $table->string('jornada', 20)->nullable();
            $table->string('sm', 10)->nullable();
            $table->string('sg2', 20)->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('registros_globales');
    }
};
