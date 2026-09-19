<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assignments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('horario_id')->constrained('horarios')->cascadeOnDelete();
            $table->string('titulo');
            $table->text('descripcion')->nullable();
            $table->text('instrucciones')->nullable();
            $table->string('archivo_guia_url')->nullable();
            $table->dateTime('fecha_apertura');
            $table->dateTime('fecha_limite');
            $table->boolean('permitir_entrega_tardia')->default(false);
            $table->decimal('peso_calificacion', 5, 2);
            $table->decimal('nota_maxima', 5, 2)->default(20);
            $table->string('tipos_permitidos')->default('pdf,zip,docx');
            $table->integer('tamano_maximo_mb')->default(10);
            $table->enum('estatus', ['borrador', 'publicado', 'cerrado'])->default('borrador');
            $table->timestamps();
            
            // Índices
            $table->index('horario_id');
            $table->index('estatus');
            $table->index('fecha_limite');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assignments');
    }
};
