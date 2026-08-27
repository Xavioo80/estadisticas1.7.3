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
        if (!Schema::hasColumn('registros_globales', 'user_id')) {
            Schema::table('registros_globales', function (Blueprint $table) {
                $table->unsignedBigInteger('user_id')->nullable()->after('sm');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('registros_globales', 'user_id')) {
            Schema::table('registros_globales', function (Blueprint $table) {
                $table->dropColumn('user_id');
            });
        }
    }
};
