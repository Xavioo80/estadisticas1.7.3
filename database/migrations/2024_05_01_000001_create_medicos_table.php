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
        Schema::create('medicos', function (Blueprint $blueprint) {
            $blueprint->id();
            $blueprint->string('COD_MED')->unique();
            $blueprint->string('NOM_MED');
            $blueprint->string('JORNADA')->nullable();
            $blueprint->string('NOMINA')->nullable();
            $blueprint->string('ESPECIALIDAD')->nullable();
            $blueprint->string('MODALIDAD')->nullable();
            $blueprint->date('FECHA_INGRESO')->nullable();
            $blueprint->string('CORREO')->nullable();
            $blueprint->string('TELEFONO')->nullable();
            $blueprint->decimal('HORAS_CONTRATADAS', 8, 2)->nullable();
            $blueprint->string('CONSULTAS')->nullable();
            $blueprint->decimal('consultas_por_hora', 8, 2)->nullable();
            $blueprint->integer('consultas_dia')->nullable();
            $blueprint->string('estado')->default('activo');
            $blueprint->text('observaciones')->nullable();
            $blueprint->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('medicos');
    }
};
