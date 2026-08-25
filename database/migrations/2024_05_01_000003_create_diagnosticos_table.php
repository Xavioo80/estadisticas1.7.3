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
        Schema::create('diagnosticos', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('codigo')->unique();
            $blueprint->string('patologia');
            $blueprint->string('auxiliar')->nullable();
            $blueprint->string('secundario')->nullable();
            $blueprint->string('categoria')->nullable();

            // Columnas de validación
            $blueprint->integer('edad_minima')->nullable();
            $blueprint->integer('edad_maxima')->nullable();
            $blueprint->string('tipo_edad')->nullable();
            $blueprint->string('sexo_permitido')->nullable();
            $blueprint->boolean('requiere_embarazo')->default(false);
            $blueprint->boolean('es_pediatrico')->default(false);
            $blueprint->boolean('es_adulto')->default(false);
            $blueprint->text('notas_validacion')->nullable();
            $blueprint->datetime('validaciones_actualizadas_en')->nullable();
            $blueprint->string('validaciones_actualizadas_por')->nullable();

            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('diagnosticos');
    }
};
