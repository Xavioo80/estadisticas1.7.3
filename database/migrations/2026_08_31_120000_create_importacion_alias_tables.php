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
        if (!Schema::hasTable('importacion_diagnosticos_alias')) {
            Schema::create('importacion_diagnosticos_alias', function (Blueprint $table) {
                $table->id();
                $table->string('texto_original', 255);
                $table->string('texto_normalizado', 255)->unique();
                $table->string('codigo', 50)->index();
                $table->string('patologia', 255);
                $table->unsignedBigInteger('diagnostico_id')->nullable()->index();
                $table->integer('veces_usado')->default(1);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('importacion_colonias_alias')) {
            Schema::create('importacion_colonias_alias', function (Blueprint $table) {
                $table->id();
                $table->string('texto_original', 255);
                $table->string('texto_normalizado', 255)->unique();
                $table->string('cod_col', 50)->index();
                $table->string('colonia', 255);
                $table->unsignedBigInteger('colonia_id')->nullable()->index();
                $table->integer('veces_usado')->default(1);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('importacion_colonias_alias');
        Schema::dropIfExists('importacion_diagnosticos_alias');
    }
};
