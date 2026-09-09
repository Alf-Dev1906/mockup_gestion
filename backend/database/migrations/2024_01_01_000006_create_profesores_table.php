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
        Schema::create('profesores', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facultad_id')->constrained('facultades')->onDelete('cascade');
            $table->string('cedula', 20)->unique();
            $table->string('nombre', 100);
            $table->string('apellido', 100);
            $table->string('email', 100)->unique();
            $table->string('telefono', 20)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->enum('genero', ['M', 'F', 'Otro'])->nullable();
            $table->text('direccion')->nullable();
            $table->string('foto_url', 255)->nullable();
            
            // Datos profesionales
            $table->string('codigo_empleado', 20)->unique();
            $table->date('fecha_contratacion');
            $table->enum('tipo_contrato', ['tiempo_completo', 'medio_tiempo', 'hora_clase', 'contratado'])->default('tiempo_completo');
            $table->enum('categoria', ['instructor', 'asistente', 'agregado', 'asociado', 'titular'])->default('asistente');
            $table->string('especialidad', 200)->nullable();
            $table->string('titulo_academico', 200)->nullable()->comment('Ej: Doctor, Magister, Licenciado');
            $table->text('experiencia')->nullable();
            $table->integer('horas_semanales')->default(40);
            $table->enum('estatus', ['activo', 'inactivo', 'licencia', 'jubilado'])->default('activo');
            
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index(['facultad_id', 'estatus']);
            $table->index('cedula');
            $table->index('codigo_empleado');
            $table->index('email');
            $table->index(['apellido', 'nombre']);
            $table->index('estatus');
            $table->index('especialidad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('profesores');
    }
};
