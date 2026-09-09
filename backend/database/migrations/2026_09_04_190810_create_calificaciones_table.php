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
        Schema::create('calificaciones', function (Blueprint $table) {
            $table->id();
            $table->foreignId('inscripcion_id')->constrained('inscripciones')->onDelete('cascade');
            
            // Notas por corte (sistema venezolano: 3 cortes)
            $table->decimal('nota_corte_1', 4, 2)->nullable()->comment('Primer corte (0-20)');
            $table->decimal('nota_corte_2', 4, 2)->nullable()->comment('Segundo corte (0-20)');
            $table->decimal('nota_corte_3', 4, 2)->nullable()->comment('Tercer corte (0-20)');
            
            // Nota final calculada
            $table->decimal('nota_final', 4, 2)->nullable()->comment('Promedio final (0-20)');
            
            // Estatus académico
            $table->enum('estatus', [
                'cursando',
                'aprobado',
                'reprobado',
                'retirado',
                'aplazado'
            ])->default('cursando')->index();
            
            // Observaciones del docente
            $table->text('observaciones')->nullable();
            
            // Asistencias (porcentaje)
            $table->integer('asistencias')->default(0)->comment('Porcentaje de asistencia');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices para optimizar consultas
            $table->index(['inscripcion_id', 'estatus']);
            $table->index('nota_final');
            $table->unique('inscripcion_id'); // Una calificación por inscripción
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('calificaciones');
    }
};
