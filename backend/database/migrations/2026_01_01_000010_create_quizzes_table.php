<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quizzes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('horario_id')->constrained('horarios')->cascadeOnDelete();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->enum('tipo', ['quiz', 'examen', 'practica'])->default('quiz');
            $table->text('instrucciones')->nullable();
            $table->integer('duracion_minutos')->default(60);
            $table->integer('intentos_permitidos')->default(1);
            $table->integer('advertencias_max')->default(3);
            $table->dateTime('fecha_inicio')->nullable();
            $table->dateTime('fecha_fin')->nullable();
            $table->dateTime('visible_desde')->nullable();
            $table->enum('estado', ['borrador', 'publicado', 'cerrado'])->default('borrador');
            $table->boolean('orden_aleatorio')->default(false);
            $table->boolean('mostrar_resultado_inmediato')->default(true);
            $table->foreignId('created_by')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
            
            // Índice para búsquedas frecuentes
            $table->index('horario_id');
            $table->index('estado');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quizzes');
    }
};
