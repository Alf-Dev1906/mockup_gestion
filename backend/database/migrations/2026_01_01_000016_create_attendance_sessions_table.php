<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendance_sessions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('horario_id')->constrained('horarios')->cascadeOnDelete();
            $table->foreignId('profesor_id')->constrained('profesores')->cascadeOnDelete();
            $table->date('session_date');
            $table->string('codigo_dinamico', 10);
            $table->dateTime('codigo_expira_at');
            $table->boolean('abierta')->default(true);
            $table->dateTime('cierre_automatico_at')->nullable();
            $table->integer('total_presentes')->default(0);
            $table->timestamps();
            
            // Restricción única
            $table->unique(['horario_id', 'session_date']);
            
            // Índices
            $table->index('session_date');
            $table->index('abierta');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendance_sessions');
    }
};
