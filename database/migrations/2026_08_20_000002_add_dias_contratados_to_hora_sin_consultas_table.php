<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('hora_sin_consultas') && !Schema::hasColumn('hora_sin_consultas', 'dias_contratados')) {
            Schema::table('hora_sin_consultas', function (Blueprint $table) {
                $table->decimal('dias_contratados', 8, 2)->nullable()->default(null)->after('mes');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('hora_sin_consultas') && Schema::hasColumn('hora_sin_consultas', 'dias_contratados')) {
            Schema::table('hora_sin_consultas', function (Blueprint $table) {
                $table->dropColumn('dias_contratados');
            });
        }
    }
};
