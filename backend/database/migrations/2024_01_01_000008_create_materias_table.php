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
        Schema::create('materias', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrera_id')->constrained('carreras')->onDelete('cascade');
            $table->string('codigo', 20)->unique()->comment('Código único de la materia');
            $table->string('nombre', 200);
            $table->text('descripcion')->nullable();
            $table->integer('creditos')->default(3)->comment('Unidades crédito');
            $table->integer('horas_teoricas')->default(3);
            $table->integer('horas_practicas')->default(0);
            $table->integer('horas_laboratorio')->default(0);
            $table->integer('semestre_recomendado')->default(1)->comment('Semestre en el que se recomienda cursar');
            $table->enum('tipo', ['obligatoria', 'electiva', 'especialidad'])->default('obligatoria');
            $table->text('competencias')->nullable()->comment('Competencias que desarrolla');
            $table->text('bibliografia')->nullable();
            $table->boolean('activo')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index(['carrera_id', 'activo']);
            $table->index('codigo');
            $table->index('nombre');
            $table->index('semestre_recomendado');
            $table->index('tipo');
        });
        
        // Tabla pivot para prerrequisitos (una materia puede tener varias materias como requisito previo)
        Schema::create('materia_prerrequisitos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('materia_id')->constrained('materias')->onDelete('cascade')->comment('Materia que requiere el prerrequisito');
            $table->foreignId('prerrequisito_id')->constrained('materias')->onDelete('cascade')->comment('Materia que es prerrequisito');
            $table->timestamps();
            
            // Evitar duplicados
            $table->unique(['materia_id', 'prerrequisito_id']);
            $table->index('materia_id');
            $table->index('prerrequisito_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('materia_prerrequisitos');
        Schema::dropIfExists('materias');
    }
};
