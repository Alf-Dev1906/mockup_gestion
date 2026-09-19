<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_answers', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('quiz_attempts')->cascadeOnDelete();
            $table->foreignId('question_id')->constrained('quiz_questions')->cascadeOnDelete();
            $table->json('respuesta'); // Respuesta del alumno en formato libre
            $table->boolean('es_correcta')->nullable();
            $table->decimal('puntos_obtenidos', 5, 2)->nullable();
            $table->string('archivo_url')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index('attempt_id');
            $table->index('question_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_answers');
    }
};
