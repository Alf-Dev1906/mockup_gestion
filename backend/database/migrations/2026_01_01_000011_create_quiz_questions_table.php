<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('quiz_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('quiz_id')->constrained('quizzes')->cascadeOnDelete();
            $table->integer('orden');
            $table->enum('tipo', ['seleccion', 'verdadero_falso', 'relacion_columnas', 'espacios', 'multimedia']);
            $table->text('enunciado');
            $table->json('contenido'); // Toda la lógica del tipo de pregunta
            $table->decimal('puntos', 5, 2);
            $table->boolean('obligatoria')->default(true);
            $table->text('retroalimentacion')->nullable();
            $table->string('archivo_url')->nullable();
            $table->timestamps();
            
            // Índices
            $table->index('quiz_id');
            $table->index('orden');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('quiz_questions');
    }
};
