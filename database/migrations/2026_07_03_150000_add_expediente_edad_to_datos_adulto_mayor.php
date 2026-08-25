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
        Schema::table('datos_adulto_mayor', function (Blueprint $table) {
            $table->string('expediente', 50)->nullable()->first();
            $table->unsignedSmallInteger('edad')->nullable()->after('dni');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('datos_adulto_mayor', function (Blueprint $table) {
            $table->dropColumn(['expediente', 'edad']);
        });
    }
};
