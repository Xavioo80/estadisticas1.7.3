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
        if (Schema::hasTable('adolescentes_control')) {
            Schema::table('adolescentes_control', function (Blueprint $table) {
                if (!Schema::hasColumn('adolescentes_control', 'colonia')) {
                    $table->string('colonia')->nullable()->after('numero_identidad');
                }
                if (!Schema::hasColumn('adolescentes_control', 'anios_cursados')) {
                    $table->integer('anios_cursados')->nullable()->after('escolaridad');
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('adolescentes_control')) {
            Schema::table('adolescentes_control', function (Blueprint $table) {
                if (Schema::hasColumn('adolescentes_control', 'colonia')) {
                    $table->dropColumn('colonia');
                }
                if (Schema::hasColumn('adolescentes_control', 'anios_cursados')) {
                    $table->dropColumn('anios_cursados');
                }
            });
        }
    }
};
