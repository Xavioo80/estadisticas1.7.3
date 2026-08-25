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
        Schema::create('datos_adulto_mayor', function (Blueprint $table) {
            $table->id();
            $table->string('nombre_completo', 150);
            $table->string('dni', 20)->unique();
            $table->text('direccion')->nullable();
            $table->string('colonia', 150)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('datos_adulto_mayor');
    }
};
