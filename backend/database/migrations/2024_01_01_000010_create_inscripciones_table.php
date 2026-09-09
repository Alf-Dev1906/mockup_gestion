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
        Schema::create('inscripciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->onDelete('cascade');
            $table->foreignId('horario_id')->constrained('horarios')->onDelete('cascade');
            $table->string('periodo_academico', 10);
            $table->date('fecha_inscripcion')->comment('Fecha en que se realizó la inscripción');
            
            // Estado de la inscripción
            $table->enum('estatus', [
                'inscrito',
                'retirado',
                'aprobado',
                'reprobado',
                'cursando',
                'lista_espera'
            ])->default('inscrito');
            
            // Calificaciones
            $table->decimal('nota_parcial_1', 5, 2)->nullable();
            $table->decimal('nota_parcial_2', 5, 2)->nullable();
            $table->decimal('nota_parcial_3', 5, 2)->nullable();
            $table->decimal('nota_final', 5, 2)->nullable();
            $table->integer('inasistencias')->default(0);
            $table->decimal('porcentaje_asistencia', 5, 2)->default(100.00);
            
            // Fechas importantes
            $table->date('fecha_retiro')->nullable()->comment('Fecha de retiro de la materia');
            $table->date('fecha_calificacion_final')->nullable();
            
            // Observaciones
            $table->text('observaciones')->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Un estudiante no puede inscribirse dos veces en el mismo horario en el mismo periodo
            $table->unique(['estudiante_id', 'horario_id', 'periodo_academico'], 'uk_estudiante_horario_periodo');
            
            // Índices para optimizar consultas de alta concurrencia
            $table->index(['estudiante_id', 'periodo_academico', 'estatus']);
            $table->index(['horario_id', 'estatus']);
            $table->index('periodo_academico');
            $table->index('estatus');
            $table->index('fecha_inscripcion');
            
            // Índice compuesto para consultas de reporte
            $table->index(['periodo_academico', 'estatus', 'fecha_inscripcion'], 'idx_reporte_inscripciones');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inscripciones');
    }
};
