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
        if (Schema::hasTable('adolescentes') && !Schema::hasColumn('adolescentes', 'anios_cursados')) {
            Schema::table('adolescentes', function (Blueprint $table) {
                $table->integer('anios_cursados')->nullable()->after('escolaridad');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('adolescentes') && Schema::hasColumn('adolescentes', 'anios_cursados')) {
            Schema::table('adolescentes', function (Blueprint $table) {
                $table->dropColumn('anios_cursados');
            });
        }
    }
};
