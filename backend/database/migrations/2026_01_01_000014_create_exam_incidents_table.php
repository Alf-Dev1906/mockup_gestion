<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_incidents', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attempt_id')->constrained('quiz_attempts')->cascadeOnDelete();
            $table->enum('tipo', ['cambio_pestana', 'perdida_foco', 'pantalla_completa', 'inactividad', 'fraude_timer', 'advertencia_enviada', 'auto_submit']);
            $table->string('descripcion')->nullable();
            $table->json('metadata')->nullable(); // Datos extra del evento
            $table->dateTime('ocurrido_at');
            $table->timestamps();
            
            // Índices
            $table->index('attempt_id');
            $table->index('tipo');
            $table->index('ocurrido_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_incidents');
    }
};
