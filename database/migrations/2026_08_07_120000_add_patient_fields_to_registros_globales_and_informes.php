<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Agrega campos de datos del paciente a registros_globales e informes:
     * nombre_paciente, identidad (DNI), telefono, fecha_nacimiento, etnia
     */
    public function up(): void
    {
        // ─── registros_globales ───────────────────────────────────────────────
        Schema::table('registros_globales', function (Blueprint $table) {
            if (!Schema::hasColumn('registros_globales', 'identidad')) {
                $table->string('identidad', 50)->nullable()->index()->after('exp');
            }
            if (!Schema::hasColumn('registros_globales', 'nombre_paciente')) {
                $table->string('nombre_paciente', 255)->nullable()->after('identidad');
            }
            if (!Schema::hasColumn('registros_globales', 'telefono')) {
                $table->string('telefono', 30)->nullable()->after('nombre_paciente');
            }
            if (!Schema::hasColumn('registros_globales', 'fecha_nacimiento')) {
                $table->date('fecha_nacimiento')->nullable()->after('telefono');
            }
            if (!Schema::hasColumn('registros_globales', 'etnia')) {
                $table->string('etnia', 80)->nullable()->default('MESTIZO')->after('fecha_nacimiento');
            }
        });

        // ─── informes ─────────────────────────────────────────────────────────
        Schema::table('informes', function (Blueprint $table) {
            if (!Schema::hasColumn('informes', 'identidad')) {
                $table->string('identidad', 50)->nullable()->index()->after('exp');
            }
            if (!Schema::hasColumn('informes', 'nombre_paciente')) {
                $table->string('nombre_paciente', 255)->nullable()->after('identidad');
            }
            if (!Schema::hasColumn('informes', 'telefono')) {
                $table->string('telefono', 30)->nullable()->after('nombre_paciente');
            }
            if (!Schema::hasColumn('informes', 'fecha_nacimiento')) {
                $table->date('fecha_nacimiento')->nullable()->after('telefono');
            }
            if (!Schema::hasColumn('informes', 'etnia')) {
                $table->string('etnia', 80)->nullable()->default('MESTIZO')->after('fecha_nacimiento');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('registros_globales', function (Blueprint $table) {
            $table->dropColumn(['nombre_paciente', 'identidad', 'telefono', 'fecha_nacimiento', 'etnia']);
        });

        Schema::table('informes', function (Blueprint $table) {
            $table->dropColumn(['nombre_paciente', 'identidad', 'telefono', 'fecha_nacimiento', 'etnia']);
        });
    }
};
