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
        if (Schema::hasTable('adolescentes') && !Schema::hasColumn('adolescentes', 'colonia')) {
            Schema::table('adolescentes', function (Blueprint $table) {
                $table->string('colonia')->nullable()->after('numero_identidad');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('adolescentes') && Schema::hasColumn('adolescentes', 'colonia')) {
            Schema::table('adolescentes', function (Blueprint $table) {
                $table->dropColumn('colonia');
            });
        }
    }
};
