<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('adolescentes')) {
            Schema::table('adolescentes', function (Blueprint $table) {
                if (!Schema::hasColumn('adolescentes', 'medico_atencion')) {
                    $table->string('medico_atencion')->nullable()->after('colonia');
                }
                if (!Schema::hasColumn('adolescentes', 'usuario_registro')) {
                    $table->string('usuario_registro')->nullable()->after('medico_atencion');
                }
            });
        }

        if (Schema::hasTable('adolescentes_control')) {
            Schema::table('adolescentes_control', function (Blueprint $table) {
                if (!Schema::hasColumn('adolescentes_control', 'medico_atencion')) {
                    $table->string('medico_atencion')->nullable()->after('colonia');
                }
                if (!Schema::hasColumn('adolescentes_control', 'usuario_registro')) {
                    $table->string('usuario_registro')->nullable()->after('medico_atencion');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('adolescentes')) {
            Schema::table('adolescentes', function (Blueprint $table) {
                if (Schema::hasColumn('adolescentes', 'medico_atencion')) {
                    $table->dropColumn('medico_atencion');
                }
                if (Schema::hasColumn('adolescentes', 'usuario_registro')) {
                    $table->dropColumn('usuario_registro');
                }
            });
        }

        if (Schema::hasTable('adolescentes_control')) {
            Schema::table('adolescentes_control', function (Blueprint $table) {
                if (Schema::hasColumn('adolescentes_control', 'medico_atencion')) {
                    $table->dropColumn('medico_atencion');
                }
                if (Schema::hasColumn('adolescentes_control', 'usuario_registro')) {
                    $table->dropColumn('usuario_registro');
                }
            });
        }
    }
};
