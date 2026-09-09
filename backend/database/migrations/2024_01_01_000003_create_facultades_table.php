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
        Schema::create('facultades', function (Blueprint $table) {
            $table->id();
            $table->string('codigo', 10)->unique()->comment('Código único de la facultad');
            $table->string('nombre', 150)->comment('Nombre de la facultad');
            $table->string('decano', 150)->nullable()->comment('Nombre del decano');
            $table->string('email', 100)->nullable();
            $table->string('telefono', 20)->nullable();
            $table->text('descripcion')->nullable();
            $table->boolean('activo')->default(true)->index();
            $table->timestamps();
            $table->softDeletes();
            
            // Índices para optimizar búsquedas
            $table->index('codigo');
            $table->index('nombre');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('facultades');
    }
};
