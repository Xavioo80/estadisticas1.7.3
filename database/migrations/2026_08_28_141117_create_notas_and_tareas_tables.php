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
        Schema::create('notas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('assigned_user_id')->nullable();
            $table->string('assigned_user_name')->nullable();
            $table->string('titulo');
            $table->text('contenido')->nullable();
            $table->string('tipo')->default('nota'); // nota, checklist, tarea
            $table->json('checklist_items')->nullable();
            $table->string('etiqueta')->nullable();
            $table->string('color')->default('#6c757d');
            $table->string('captura_url')->nullable();
            $table->boolean('pinned')->default(false);
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assigned_user_id')->references('id')->on('users')->onDelete('set null');
        });

        Schema::create('tareas', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('user_id');
            $table->unsignedBigInteger('assigned_to')->nullable();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->date('fecha_asignacion')->nullable();
            $table->date('fecha_limite')->nullable();
            $table->string('prioridad')->default('normal'); // baja, normal, alta, urgente
            $table->string('estado')->default('pendiente'); // pendiente, en_progreso, completada
            $table->timestamps();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('assigned_to')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tareas');
        Schema::dropIfExists('notas');
    }
};
