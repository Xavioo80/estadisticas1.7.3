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
        Schema::create('categorias_documentacion', function (Blueprint $table) {
            $table->id();
            $table->string('nombre')->unique();
            $table->string('color')->default('primary');
            $table->string('icono')->default('fa-folder');
            $table->timestamps();
        });

        Schema::table('documentacions', function (Blueprint $table) {
            $table->foreignId('categoria_id')->nullable()->constrained('categorias_documentacion')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('documentacions', function (Blueprint $table) {
            $table->dropForeign(['categoria_id']);
            $table->dropColumn('categoria_id');
        });
        Schema::dropIfExists('categorias_documentacion');
    }
};
