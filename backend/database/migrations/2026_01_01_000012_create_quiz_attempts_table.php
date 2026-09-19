<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_attempts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete();
            $table->foreignId('inscripcion_id')->constrained('inscripciones');
            $table->enum('estado', ['en_progreso', 'enviado', 'calificado', 'anulado'])->default('en_progreso');
            $table->dateTime('inicio_at');
            $table->dateTime('fin_at')->nullable();
            $table->integer('tiempo_restante_seg')->default(0);
            $table->decimal('nota_obtenida', 5, 2)->nullable();
            $table->decimal('nota_maxima', 5, 2);
            $table->string('ip_address')->nullable();
            $table->text('user_agent')->nullable();
            $table->integer('advertencias_count')->default(0);
            $table->boolean('auto_enviado')->default(false);
            $table->timestamps();
            
            // Restricción única cuando intentos=1
            $table->unique(['quiz_id', 'estudiante_id']);
            
            // Índices
            $table->index('estado');
            $table->index('estudiante_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_attempts');
    }
};
