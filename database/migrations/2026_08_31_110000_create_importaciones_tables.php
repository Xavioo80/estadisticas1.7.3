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
        if (!Schema::hasTable('importaciones')) {
            Schema::create('importaciones', function (Blueprint $table) {
                $table->id();
                $table->string('nombre_archivo', 255);
                $table->string('hash_archivo', 64)->index();
                $table->bigInteger('tamano_archivo')->default(0);
                $table->integer('total_filas')->default(0);
                $table->integer('filas_analizadas')->default(0);
                $table->integer('filas_importadas')->default(0);
                $table->json('fechas_disponibles')->nullable();
                $table->json('medicos_disponibles')->nullable();
                $table->json('resumen_estadistico')->nullable();
                $table->unsignedBigInteger('usuario_id')->nullable()->index();
                $table->string('estado', 30)->default('analizado'); // analizado, procesado, completado, cancelado
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('importacion_registros')) {
            Schema::create('importacion_registros', function (Blueprint $table) {
                $table->id();
                $table->foreignId('importacion_id')->constrained('importaciones')->cascadeOnDelete();
                $table->integer('fila_excel');
                $table->string('hash_registro', 64)->index();
                $table->date('fecha_atencion')->nullable()->index();
                $table->string('medico', 150)->nullable()->index();
                $table->string('cm', 20)->nullable();
                $table->string('prof', 100)->nullable();
                $table->string('numero_identidad', 50)->nullable()->index();
                $table->string('identidad_original', 100)->nullable();
                $table->string('nombre_paciente', 255)->nullable();
                $table->date('fecha_nacimiento')->nullable();
                $table->string('edad', 10)->nullable();
                $table->string('tipo', 5)->nullable();
                $table->string('sexo', 5)->nullable();
                $table->string('expediente', 50)->nullable();
                $table->string('telefono', 80)->nullable();
                $table->text('direccion_original')->nullable();
                $table->string('colonia_normalizada', 150)->nullable();
                $table->string('cod_col', 50)->nullable();
                $table->unsignedBigInteger('colonia_id')->nullable()->index();
                $table->json('diagnosticos_json')->nullable();
                $table->json('datos_originales_json')->nullable();
                $table->json('datos_normalizados_json')->nullable();
                $table->unsignedBigInteger('paciente_id')->nullable()->index();
                $table->integer('registro_global_id')->nullable()->index();
                $table->string('estado', 30)->default('NUEVO')->index(); // NUEVO, YA_EXISTE, DUPLICADO, PENDIENTE_REVISION, ERROR, IMPORTADO
                $table->string('motivo_estado', 255)->nullable();
                $table->boolean('requiere_revision')->default(false);
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('importacion_registros');
        Schema::dropIfExists('importaciones');
    }
};
