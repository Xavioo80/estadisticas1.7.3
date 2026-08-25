<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('calendario_epi_eventos', function (Blueprint $table) {
            $table->id();
            $table->date('fecha')->index();
            $table->integer('anio')->index();
            $table->integer('mes');
            $table->integer('se')->index(); // semana epidemiológica
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->enum('tipo', ['tarea', 'observacion', 'alerta', 'feriado', 'reunion'])
                ->default('observacion');
            $table->string('color', 20)->default('#4e73df');
            $table->boolean('completado')->default(false);
            $table->unsignedBigInteger('user_id')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calendario_epi_eventos');
    }
};
