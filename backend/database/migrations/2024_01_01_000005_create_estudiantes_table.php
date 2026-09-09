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
        Schema::create('estudiantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('carrera_id')->constrained('carreras')->onDelete('cascade');
            $table->string('cedula', 20)->unique()->comment('Cédula de identidad');
            $table->string('nombre', 100)->comment('Nombre completo del estudiante');
            $table->string('apellido', 100);
            $table->string('email', 100)->unique();
            $table->string('telefono', 20)->nullable();
            $table->date('fecha_nacimiento');
            $table->enum('genero', ['M', 'F', 'Otro'])->nullable();
            $table->text('direccion')->nullable();
            $table->string('ciudad', 100)->nullable();
            $table->string('estado', 100)->nullable();
            $table->string('codigo_postal', 10)->nullable();
            $table->string('foto_url', 255)->nullable();
            
            // Datos académicos
            $table->string('matricula', 20)->unique()->comment('Matrícula o código estudiantil');
            $table->date('fecha_ingreso')->comment('Fecha de ingreso a la universidad');
            $table->integer('semestre_actual')->default(1);
            $table->decimal('indice_academico', 4, 2)->default(0.00)->comment('Promedio ponderado');
            $table->integer('creditos_aprobados')->default(0);
            $table->enum('estatus', ['activo', 'inactivo', 'egresado', 'retirado', 'suspendido'])->default('activo');
            
            // Contacto de emergencia
            $table->string('contacto_emergencia_nombre', 150)->nullable();
            $table->string('contacto_emergencia_telefono', 20)->nullable();
            $table->string('contacto_emergencia_relacion', 50)->nullable();
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices para optimizar búsquedas frecuentes
            $table->index(['carrera_id', 'estatus']);
            $table->index('cedula');
            $table->index('matricula');
            $table->index('email');
            $table->index(['apellido', 'nombre']);
            $table->index('estatus');
            $table->index('semestre_actual');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('estudiantes');
    }
};
