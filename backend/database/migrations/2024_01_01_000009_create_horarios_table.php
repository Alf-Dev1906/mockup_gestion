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
        Schema::create('horarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('materia_id')->constrained('materias')->onDelete('cascade');
            $table->foreignId('profesor_id')->constrained('profesores')->onDelete('cascade');
            $table->foreignId('aula_id')->constrained('aulas')->onDelete('cascade');
            $table->string('seccion', 5)->comment('Sección de la materia (ej: A, B, 01, 02)');
            $table->string('periodo_academico', 10)->comment('Ej: 2024-1, 2024-2');
            $table->integer('anno')->comment('Año del periodo académico');
            $table->integer('periodo')->comment('1=primer semestre, 2=segundo semestre, 3=intensivo');
            
            // Horario semanal
            $table->enum('dia_semana', ['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado', 'domingo']);
            $table->time('hora_inicio');
            $table->time('hora_fin');
            
            // Capacidad y cupos
            $table->integer('cupo_maximo')->default(30);
            $table->integer('cupo_actual')->default(0);
            $table->integer('lista_espera')->default(0);
            
            // Estado
            $table->enum('estatus', ['abierto', 'cerrado', 'en_curso', 'finalizado', 'cancelado'])->default('abierto');
            $table->date('fecha_inicio')->nullable();
            $table->date('fecha_fin')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices para optimizar consultas de alta concurrencia
            $table->index(['materia_id', 'periodo_academico', 'estatus']);
            $table->index(['profesor_id', 'periodo_academico']);
            $table->index(['aula_id', 'dia_semana', 'hora_inicio']); // Evitar conflictos de horario
            $table->index(['periodo_academico', 'estatus']);
            $table->index(['anno', 'periodo']);
            $table->index('seccion');
            
            // Índice compuesto para detectar conflictos de horario
            $table->index(['aula_id', 'dia_semana', 'hora_inicio', 'hora_fin', 'periodo_academico'], 'idx_conflicto_aula');
            $table->index(['profesor_id', 'dia_semana', 'hora_inicio', 'hora_fin', 'periodo_academico'], 'idx_conflicto_profesor');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('horarios');
    }
};
