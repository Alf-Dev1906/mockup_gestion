<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('assignment_id')->constrained('assignments')->cascadeOnDelete();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete();
            $table->foreignId('inscripcion_id')->constrained('inscripciones');
            $table->string('archivo_url');
            $table->string('nombre_archivo_original');
            $table->integer('tamano_bytes');
            $table->boolean('es_tardia')->default(false);
            $table->decimal('nota', 5, 2)->nullable();
            $table->text('retroalimentacion')->nullable();
            $table->dateTime('calificado_at')->nullable();
            $table->foreignId('calificado_por')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            
            // Restricción única
            $table->unique(['assignment_id', 'estudiante_id']);
            
            // Índices
            $table->index('estudiante_id');
            $table->index('es_tardia');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('submissions');
    }
};
