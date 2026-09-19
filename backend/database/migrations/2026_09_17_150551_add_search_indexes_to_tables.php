<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     * 
     * Agregar índices para mejorar performance en búsquedas frecuentes
     * Solo agrega índices que no existen
     */
    public function up(): void
    {
        // Usar SQL raw para evitar errores de índices duplicados
        // MySQL 8.x no soporta IF NOT EXISTS en CREATE INDEX, usar try-catch
        $indexes = [
            // HORARIOS
            ["CREATE INDEX idx_horarios_profesor_id ON horarios(profesor_id)", 'idx_horarios_profesor_id'],
            ["CREATE INDEX idx_horarios_materia_id ON horarios(materia_id)", 'idx_horarios_materia_id'],
            ["CREATE INDEX idx_horarios_aula_id ON horarios(aula_id)", 'idx_horarios_aula_id'],
            
            // CALIFICACIONES
            ["CREATE INDEX idx_calificaciones_inscripcion_id ON calificaciones(inscripcion_id)", 'idx_calificaciones_inscripcion_id'],
            ["CREATE INDEX idx_calificaciones_estudiante_id ON calificaciones(estudiante_id)", 'idx_calificaciones_estudiante_id'],
            ["CREATE INDEX idx_calificaciones_periodo ON calificaciones(periodo)", 'idx_calificaciones_periodo'],
            
            // QUIZZES
            ["CREATE INDEX idx_quizzes_horario_id ON quizzes(horario_id)", 'idx_quizzes_horario_id'],
            ["CREATE INDEX idx_quizzes_estado ON quizzes(estado)", 'idx_quizzes_estado'],
            ["CREATE INDEX idx_quizzes_tipo ON quizzes(tipo)", 'idx_quizzes_tipo'],
            
            // QUIZ_ATTEMPTS
            ["CREATE INDEX idx_quiz_attempts_quiz_id ON quiz_attempts(quiz_id)", 'idx_quiz_attempts_quiz_id'],
            ["CREATE INDEX idx_quiz_attempts_estudiante_id ON quiz_attempts(estudiante_id)", 'idx_quiz_attempts_estudiante_id'],
            ["CREATE INDEX idx_quiz_attempts_estado ON quiz_attempts(estado)", 'idx_quiz_attempts_estado'],
            
            // ASSIGNMENTS
            ["CREATE INDEX idx_assignments_horario_id ON assignments(horario_id)", 'idx_assignments_horario_id'],
            ["CREATE INDEX idx_assignments_estado ON assignments(estado)", 'idx_assignments_estado'],
            ["CREATE INDEX idx_assignments_fecha_limite ON assignments(fecha_limite)", 'idx_assignments_fecha_limite'],
            
            // ASSIGNMENT_SUBMISSIONS
            ["CREATE INDEX idx_assignment_submissions_assignment_id ON assignment_submissions(assignment_id)", 'idx_assignment_submissions_assignment_id'],
            ["CREATE INDEX idx_assignment_submissions_estudiante_id ON assignment_submissions(estudiante_id)", 'idx_assignment_submissions_estudiante_id'],
            ["CREATE INDEX idx_assignment_submissions_estado ON assignment_submissions(estado)", 'idx_assignment_submissions_estado'],
            
            // ATTENDANCE_SESSIONS
            ["CREATE INDEX idx_attendance_sessions_horario_id ON attendance_sessions(horario_id)", 'idx_attendance_sessions_horario_id'],
            ["CREATE INDEX idx_attendance_sessions_estado ON attendance_sessions(estado)", 'idx_attendance_sessions_estado'],
            ["CREATE INDEX idx_attendance_sessions_codigo_acceso ON attendance_sessions(codigo_acceso)", 'idx_attendance_sessions_codigo_acceso'],
            
            // ATTENDANCES
            ["CREATE INDEX idx_attendances_session_id ON attendances(session_id)", 'idx_attendances_session_id'],
            ["CREATE INDEX idx_attendances_estudiante_id ON attendances(estudiante_id)", 'idx_attendances_estudiante_id'],
            ["CREATE INDEX idx_attendances_estado ON attendances(estado)", 'idx_attendances_estado'],
            
            // NOTIFICATIONS
            ["CREATE INDEX idx_notifications_user_id ON notifications(user_id)", 'idx_notifications_user_id'],
            ["CREATE INDEX idx_notifications_leida ON notifications(leida)", 'idx_notifications_leida'],
            ["CREATE INDEX idx_notifications_tipo ON notifications(tipo)", 'idx_notifications_tipo'],
        ];

        foreach ($indexes as [$sql, $indexName]) {
            try {
                DB::statement($sql);
            } catch (\Exception $e) {
                // Ignorar si el índice ya existe o la tabla no existe
                if (!str_contains($e->getMessage(), 'Duplicate key name') && 
                    !str_contains($e->getMessage(), "doesn't exist")) {
                    // Log pero no fallar
                    \Log::warning("Could not create index {$indexName}: " . $e->getMessage());
                }
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Los índices se eliminarán automáticamente si se hace rollback de las tablas
        // No es necesario eliminarlos manualmente
    }
};
