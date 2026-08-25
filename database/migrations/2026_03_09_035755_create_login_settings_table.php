<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('login_settings', function (Blueprint $table) {
            $table->id();
            $table->string('title')->default('Estadísticas 1.5');
            $table->string('primary_color')->default('#2563eb');
            $table->string('secondary_color')->default('#1e40af');
            $table->string('logo_path')->nullable();
            $table->string('background_image_path')->nullable();
            $table->timestamps();
        });

        // Insert default record
        DB::table('login_settings')->insert([
            'title' => 'Estadísticas 1.5',
            'primary_color' => '#2563eb',
            'secondary_color' => '#1e40af',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('login_settings');
    }
};
