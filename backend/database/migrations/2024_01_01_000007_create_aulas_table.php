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
        Schema::create('aulas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('facultad_id')->constrained('facultades')->onDelete('cascade');
            $table->string('codigo', 20)->unique()->comment('Código único del aula (ej: A-101)');
            $table->string('nombre', 100)->comment('Nombre descriptivo del aula');
            $table->string('edificio', 50)->comment('Edificio donde se encuentra');
            $table->string('piso', 10)->nullable();
            $table->integer('capacidad')->default(30)->comment('Capacidad máxima de estudiantes');
            $table->enum('tipo', ['aula_regular', 'laboratorio', 'auditorio', 'salon_conferencias', 'taller'])->default('aula_regular');
            $table->text('equipamiento')->nullable()->comment('Descripción del equipamiento disponible');
            $table->boolean('tiene_proyector')->default(false);
            $table->boolean('tiene_aire_acondicionado')->default(false);
            $table->boolean('tiene_computadoras')->default(false);
            $table->integer('numero_computadoras')->default(0);
            $table->boolean('accesible_discapacitados')->default(true);
            $table->enum('estatus', ['disponible', 'mantenimiento', 'fuera_servicio'])->default('disponible');
            $table->timestamps();
            $table->softDeletes();
            
            // Índices
            $table->index(['facultad_id', 'estatus']);
            $table->index('codigo');
            $table->index('edificio');
            $table->index('tipo');
            $table->index('estatus');
            $table->index('capacidad');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aulas');
    }
};
