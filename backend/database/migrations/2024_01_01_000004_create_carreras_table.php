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
        Schema::create('carreras', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facultad_id')->constrained('facultades')->onDelete('cascade');
            $table->string('codigo', 20)->unique()->comment('Código único de la carrera');
            $table->string('nombre', 200)->comment('Nombre de la carrera');
            $table->string('titulo_otorgado', 200)->comment('Ej: Licenciado en..., Ingeniero en...');
            $table->integer('duracion_semestres')->default(10)->comment('Duración en semestres');
            $table->integer('creditos_totales')->default(180)->comment('Total de créditos requeridos');
            $table->string('coordinador', 150)->nullable();
            $table->text('descripcion')->nullable();
            $table->enum('modalidad', ['presencial', 'semipresencial', 'distancia'])->default('presencial');
            $table->boolean('activo')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
            
            // Índices compuestos para optimizar consultas comunes
            $table->index(['facultad_id', 'activo']);
            $table->index('codigo');
            $table->index('nombre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('carreras');
    }
};
