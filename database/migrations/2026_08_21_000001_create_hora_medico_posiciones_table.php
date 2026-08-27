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
        if (!Schema::hasTable('hora_medico_posiciones')) {
            Schema::create('hora_medico_posiciones', function (Blueprint $table) {
                $table->id();
                $table->integer('ano');
                $table->string('mes');
                $table->string('jornada');
                $table->foreignId('medico_id')->constrained('medicos')->onDelete('cascade');
                $table->integer('posicion');
                $table->timestamps();

                $table->unique(['ano', 'mes', 'jornada', 'medico_id'], 'hm_pos_unique');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hora_medico_posiciones');
    }
};
