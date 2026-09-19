<?php

namespace App\Services;

use App\Models\Notification;
use App\Models\User;
use Illuminate\Support\Collection;

class NotificationService
{
    /**
     * Crear una notificación para un usuario
     */
    public function notificar(
        int $userId,
        string $tipo,
        string $titulo,
        string $mensaje,
        ?string $url = null,
        ?array $datos = null
    ): Notification {
        return Notification::create([
            'user_id' => $userId,
            'tipo' => $tipo,
            'titulo' => $titulo,
            'mensaje' => $mensaje,
            'url' => $url,
            'datos' => $datos,
        ]);
    }

    /**
     * Notificar a múltiples usuarios
     */
    public function notificarGrupo(
        array $userIds,
        string $tipo,
        string $titulo,
        string $mensaje,
        ?string $url = null,
        ?array $datos = null
    ): int {
        $notificaciones = array_map(function ($userId) use ($tipo, $titulo, $mensaje, $url, $datos) {
            return [
                'user_id' => $userId,
                'tipo' => $tipo,
                'titulo' => $titulo,
                'mensaje' => $mensaje,
                'url' => $url,
                'datos' => $datos ? json_encode($datos) : null,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }, $userIds);

        return Notification::insert($notificaciones);
    }

    /**
     * Notificar cuando se publica un examen
     */
    public function notificarExamenPublicado($quizId, $profesorId): void
    {
        // Obtener quiz
        $quiz = \App\Models\Quiz::findOrFail($quizId);

        // Obtener estudiantes inscritos en el horario
        $estudianteIds = $quiz->horario->inscripciones()
            ->where('estado', 'activo')
            ->pluck('estudiante_id')
            ->map(function ($estId) {
                return \App\Models\Estudiante::find($estId)->user_id;
            })
            ->filter()
            ->toArray();

        if (empty($estudianteIds)) {
            return;
        }

        $this->notificarGrupo(
            $estudianteIds,
            'examen_publicado',
            '📝 Nuevo examen publicado',
            "El profesor ha publicado: {$quiz->titulo}",
            "/estudiante/quizzes/{$quizId}",
            [
                'quiz_id' => $quizId,
                'quiz_titulo' => $quiz->titulo,
            ]
        );
    }

    /**
     * Notificar cuando se publica una tarea
     */
    public function notificarTareaPublicada($assignmentId): void
    {
        // Obtener tarea
        $assignment = \App\Models\Assignment::findOrFail($assignmentId);

        // Obtener estudiantes inscritos
        $estudianteIds = $assignment->horario->inscripciones()
            ->where('estado', 'activo')
            ->pluck('estudiante_id')
            ->map(function ($estId) {
                return \App\Models\Estudiante::find($estId)->user_id;
            })
            ->filter()
            ->toArray();

        if (empty($estudianteIds)) {
            return;
        }

        $this->notificarGrupo(
            $estudianteIds,
            'tarea_publicada',
            '📋 Nueva tarea publicada',
            "Tarea: {$assignment->titulo} — Vence: {$assignment->fecha_limite->format('d/m/Y H:i')}",
            "/estudiante/tareas/{$assignmentId}",
            [
                'assignment_id' => $assignmentId,
                'assignment_titulo' => $assignment->titulo,
                'fecha_limite' => $assignment->fecha_limite->toIso8601String(),
            ]
        );
    }

    /**
     * Notificar tarea por vencer en 24 horas
     * (Se ejecuta por queue/cron)
     */
    public function notificarTareasPorVencer(): void
    {
        $tareasProximas = \App\Models\Assignment::where('activa', true)
            ->whereBetween('fecha_limite', [
                now(),
                now()->addHours(24),
            ])
            ->get();

        foreach ($tareasProximas as $tarea) {
            $estudianteIds = $tarea->horario->inscripciones()
                ->where('estado', 'activo')
                ->pluck('estudiante_id')
                ->map(function ($estId) {
                    return \App\Models\Estudiante::find($estId)->user_id;
                })
                ->filter()
                ->toArray();

            if (!empty($estudianteIds)) {
                $horasRestantes = ceil($tarea->fecha_limite->diffInMinutes(now()) / 60);
                
                $this->notificarGrupo(
                    $estudianteIds,
                    'tarea_por_vencer',
                    '⏰ Tarea por vencer',
                    "{$tarea->titulo} vence en {$horasRestantes} horas",
                    "/estudiante/tareas/{$tarea->id}",
                    [
                        'assignment_id' => $tarea->id,
                        'horas_restantes' => $horasRestantes,
                    ]
                );
            }
        }
    }

    /**
     * Notificar cuando se califica un examen
     */
    public function notificarExamenCalificado($attemptId): void
    {
        // Obtener intento
        $attempt = \App\Models\QuizAttempt::findOrFail($attemptId);

        // Solo notificar si está calificado
        if ($attempt->estado !== 'calificado') {
            return;
        }

        $this->notificar(
            $attempt->user_id,
            'examen_calificado',
            '📊 Examen calificado',
            "{$attempt->quiz->titulo} — Nota: {$attempt->nota_obtenida}/{$attempt->quiz->puntos_totales}",
            "/estudiante/quizzes/{$attempt->quiz_id}/resultado",
            [
                'quiz_id' => $attempt->quiz_id,
                'quiz_titulo' => $attempt->quiz->titulo,
                'nota_obtenida' => $attempt->nota_obtenida,
                'puntos_totales' => $attempt->quiz->puntos_totales,
            ]
        );
    }

    /**
     * Notificar a ausentes cuando se cierra sesión de asistencia
     */
    public function notificarAusentes($sessionId): void
    {
        // Obtener sesión de asistencia
        $session = \App\Models\AttendanceSession::findOrFail($sessionId);

        // Obtener estudiantes inscritos
        $inscritos = $session->horario->inscripciones()
            ->where('estado', 'activo')
            ->pluck('estudiante_id')
            ->toArray();

        // Obtener presentes
        $presentes = $session->attendances()
            ->pluck('estudiante_id')
            ->toArray();

        // Diferencia: ausentes
        $ausentes = array_diff($inscritos, $presentes);

        if (empty($ausentes)) {
            return;
        }

        // Convertir student IDs a user IDs
        $ausentes_user_ids = \App\Models\Estudiante::whereIn('id', $ausentes)
            ->pluck('user_id')
            ->toArray();

        if (empty($ausentes_user_ids)) {
            return;
        }

        $this->notificarGrupo(
            $ausentes_user_ids,
            'asistencia_ausente',
            '❌ Ausencia registrada',
            "No marcaste asistencia en {$session->horario->materia->nombre}",
            "/estudiante/asistencia",
            [
                'horario_id' => $session->horario_id,
                'fecha' => $session->created_at->toDateString(),
            ]
        );
    }

    /**
     * Notificar cuando se califica una entrega
     */
    public function notificarEntregaCalificada($submissionId): void
    {
        // Obtener entrega
        $submission = \App\Models\Submission::findOrFail($submissionId);

        // Solo si tiene calificación
        if (is_null($submission->calificacion)) {
            return;
        }

        $this->notificar(
            $submission->estudiante->user_id,
            'entrega_calificada',
            '📌 Entrega calificada',
            "{$submission->assignment->titulo} — Calificación: {$submission->calificacion}/10",
            "/estudiante/tareas/{$submission->assignment_id}/mi-entrega",
            [
                'assignment_id' => $submission->assignment_id,
                'assignment_titulo' => $submission->assignment->titulo,
                'calificacion' => $submission->calificacion,
            ]
        );
    }

    /**
     * Limpiar notificaciones antiguas (> 90 días)
     */
    public function limpiarNotificacionesAntiguas(): int
    {
        return Notification::where('created_at', '<', now()->subDays(90))
            ->where('leida', true)
            ->delete();
    }
}
