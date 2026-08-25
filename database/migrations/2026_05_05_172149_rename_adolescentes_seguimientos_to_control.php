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
        if (Schema::hasTable('adolescentes_seguimientos') && !Schema::hasTable('adolescentes_control')) {
            Schema::rename('adolescentes_seguimientos', 'adolescentes_control');
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('adolescentes_control') && !Schema::hasTable('adolescentes_seguimientos')) {
            Schema::rename('adolescentes_control', 'adolescentes_seguimientos');
        }
    }
};
