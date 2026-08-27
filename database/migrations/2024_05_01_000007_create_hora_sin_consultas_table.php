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
        Schema::create('hora_sin_consultas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('medico_id')->constrained('medicos')->onDelete('cascade');
            $table->integer('ano')->index();
            $table->string('mes', 20)->index();

            // Campos de horas administrativas y otros
            $table->decimal('administrativas_evaluacion', 8, 2)->default(0);
            $table->decimal('reuniones_trabajo', 8, 2)->default(0);
            $table->decimal('cita_ihss', 8, 2)->default(0);
            $table->decimal('taller', 8, 2)->default(0);
            $table->decimal('capacitaciones', 8, 2)->default(0);
            $table->decimal('incapacidad', 8, 2)->default(0);
            $table->decimal('compensatorio', 8, 2)->default(0);
            $table->decimal('duelo', 8, 2)->default(0);
            $table->decimal('congresos_medicos', 8, 2)->default(0);
            $table->decimal('charlas_ambiente', 8, 2)->default(0);
            $table->decimal('trabajo_campo', 8, 2)->default(0);
            $table->decimal('promocion', 8, 2)->default(0);
            $table->decimal('esfam', 8, 2)->default(0);
            $table->decimal('convocatoria_general', 8, 2)->default(0);
            $table->decimal('permiso_personal', 8, 2)->default(0);
            $table->decimal('vacaciones_ordinarias', 8, 2)->default(0);
            $table->decimal('descanso_profilactico', 8, 2)->default(0);

            // Totales
            $table->decimal('total_vacaciones', 8, 2)->default(0);
            $table->decimal('total_horas_personales', 8, 2)->default(0);
            $table->decimal('total_horas_oficiales', 8, 2)->default(0);
            $table->decimal('total_horas', 8, 2)->default(0);

            $table->text('observaciones')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hora_sin_consultas');
    }
};
