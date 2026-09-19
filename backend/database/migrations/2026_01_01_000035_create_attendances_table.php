<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('session_id')->constrained('attendance_sessions')->cascadeOnDelete();
            $table->foreignId('estudiante_id')->constrained('estudiantes')->cascadeOnDelete();
            $table->foreignId('inscripcion_id')->constrained('inscripciones')->cascadeOnDelete();
            $table->enum('estatus', ['presente', 'ausente', 'justificado'])->default('presente');
            $table->string('observacion')->nullable();
            $table->string('ip_address')->nullable();
            $table->string('user_agent')->nullable();
            $table->timestamps();
            
            // Imposible tener dos registros del mismo estudiante en la misma sesión
            $table->unique(['session_id', 'estudiante_id'], 'unique_attendance_per_session');
            
            // Índices
            $table->index('session_id');
            $table->index('estudiante_id');
            $table->index('inscripcion_id');
            $table->index('estatus');
            $table->index('created_at');
            
            // NO PERMITIR MODIFICACIÓN: Usar regla en modelo
            // Ver Attendance.php para booted() y regla de inmutabilidad
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
