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
        Schema::create('condicionamientos_diagnosticos', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('codigo_diagnostico')->index();
            $blueprint->string('nombre_diagnostico')->nullable();
            $blueprint->boolean('embarazo')->default(false);
            $blueprint->boolean('pediatrico')->default(false);
            $blueprint->boolean('adulto')->default(false);
            $blueprint->integer('edad_min')->nullable();
            $blueprint->integer('edad_max')->nullable();
            $blueprint->integer('sg_min')->nullable();
            $blueprint->integer('sg_max')->nullable();
            $blueprint->text('notas_validacion')->nullable();
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('condicionamientos_diagnosticos');
    }
};
