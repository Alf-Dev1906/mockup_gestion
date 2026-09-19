<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Horario;
use App\Models\Estudiante;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Submission;
use App\Models\Attendance;
use App\Models\ExamIncident;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class AuditoriaController extends Controller
{
    /**
     * GET /profesor/auditoria/{horario_id}/estudiantes
     * Lista de estudiantes con resumen académico
     */
    public function listarEstudiantes(Request $request, $horarioId): JsonResponse
    {
        try {
            $user = auth()->user();
            $profesor = $user->profesor;

            if (!$profesor) {
                return response()->json([
                    'message' => 'No tienes un perfil de profesor asociado',
                    'data' => [],
                ], 404);
            }

            // Validar que el horario pertenezca al profesor
            $horario = Horario::where('id', $horarioId)
                ->where('profesor_id', $profesor->id)
                ->first();

            if (!$horario) {
                return response()->json([
                    'message' => 'Horario no encontrado o no tienes acceso',
                    'data' => [],
                ], 404);
            }

            // Obtener estudiantes inscritos con sus asistencias
            $inscripciones = DB::table('inscripciones')
                ->where('horario_id', $horarioId)
                ->whereIn('estatus', ['cursando', 'inscrito'])
                ->get();

            $estudiantes = [];

            foreach ($inscripciones as $inscripcion) {
                $estudiante = DB::table('estudiantes')
                    ->where('id', $inscripcion->estudiante_id)
                    ->first();

                if (!$estudiante) continue;

                // Obtener asistencias del estudiante en este horario
                // Las asistencias están vinculadas a sesiones que pertenecen a horarios
                $asistencias = DB::table('attendances as a')
                    ->join('attendance_sessions as s', 'a.session_id', '=', 's.id')
                    ->where('s.horario_id', $horarioId)
                    ->where('a.estudiante_id', $estudiante->id)
                    ->select('s.session_date', 'a.estatus', 'a.observacion')
                    ->get();

                $estudiantes[] = [
                    'id' => $estudiante->id,
                    'nombre' => $estudiante->nombre,
                    'apellido' => $estudiante->apellido,
                    'email' => $estudiante->email,
                    'matricula' => $estudiante->matricula,
                    'asistencias' => $asistencias->map(fn($a) => [
                        'fecha' => $a->session_date,
                        'estatus' => $a->estatus,
                        'observacion' => $a->observacion
                    ])->toArray()
                ];
            }

            return response()->json([
                'message' => 'Estudiantes del horario listados',
                'data' => $estudiantes,
            ]);

        } catch (\Exception $e) {
            \Log::error('Error en listarEstudiantes: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al cargar estudiantes',
                'error' => $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    /**
     * GET /profesor/auditoria/{horario_id}/estudiante/{estudiante_id}
     * Expediente completo del estudiante
     */
    public function expedienteEstudiante(Request $request, $horarioId, $estudianteId): JsonResponse
    {
        $user = auth()->user();
        $profesor = $user->profesor;

        // Validar que el horario pertenezca al profesor
        $horario = Horario::where('id', $horarioId)
            ->where('profesor_id', $profesor->id)
            ->with('materia')
            ->firstOrFail();

        // Validar que el estudiante esté inscrito
        $estudiante = Estudiante::findOrFail($estudianteId);
        $inscripcion = $horario->inscripciones()
            ->where('estudiante_id', $estudianteId)
            ->firstOrFail();

        // ════════════════════════════════════════════════════════════════
        // 1. EXÁMENES
        // ════════════════════════════════════════════════════════════════

        $examenes = QuizAttempt::with(['quiz' => function ($query) {
                $query->select('id', 'titulo', 'puntos_totales', 'duracion_minutos');
            }])
            ->whereIn('quiz_id', function ($query) use ($horarioId) {
                $query->select('id')
                    ->from('quizzes')
                    ->where('horario_id', $horarioId);
            })
            ->where('user_id', $estudiante->user_id)
            ->whereIn('estado', ['enviado', 'calificado'])
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($attempt) => [
                'id' => $attempt->id,
                'quiz_id' => $attempt->quiz_id,
                'quiz_titulo' => $attempt->quiz->titulo ?? 'N/A',
                'puntos_totales' => $attempt->quiz->puntos_totales ?? 0,
                'nota_obtenida' => $attempt->nota_obtenida,
                'porcentaje' => $attempt->quiz->puntos_totales > 0
                    ? round(($attempt->nota_obtenida / $attempt->quiz->puntos_totales) * 100, 1)
                    : 0,
                'estado' => $attempt->estado,
                'advertencias_count' => $attempt->advertencias_count,
                'tiempo_usado_minutos' => $attempt->tiempo_restante_seg
                    ? round(($attempt->quiz->duracion_minutos * 60 - $attempt->tiempo_restante_seg) / 60, 1)
                    : null,
                'fecha' => $attempt->created_at->format('Y-m-d H:i'),
            ]);

        // ════════════════════════════════════════════════════════════════
        // 2. TAREAS
        // ════════════════════════════════════════════════════════════════

        $tareas = Submission::with(['assignment' => function ($query) {
                $query->select('id', 'titulo', 'puntos_totales', 'fecha_limite');
            }])
            ->whereIn('assignment_id', function ($query) use ($horarioId) {
                $query->select('id')
                    ->from('assignments')
                    ->where('horario_id', $horarioId);
            })
            ->where('estudiante_id', $estudianteId)
            ->orderBy('entregado_at', 'desc')
            ->get()
            ->map(fn($submission) => [
                'id' => $submission->id,
                'assignment_id' => $submission->assignment_id,
                'assignment_titulo' => $submission->assignment->titulo ?? 'N/A',
                'puntos_totales' => $submission->assignment->puntos_totales ?? 0,
                'calificacion' => $submission->calificacion,
                'porcentaje' => $submission->assignment->puntos_totales > 0 && $submission->calificacion
                    ? round(($submission->calificacion / $submission->assignment->puntos_totales) * 100, 1)
                    : null,
                'estado' => $submission->estado,
                'es_tardia' => $submission->es_tardia,
                'dias_retraso' => $submission->dias_retraso,
                'archivo' => $submission->nombre_archivo,
                'fecha_entrega' => $submission->entregado_at->format('Y-m-d H:i'),
                'comentario_profesor' => $submission->comentario_profesor,
            ]);

        // ════════════════════════════════════════════════════════════════
        // 3. ASISTENCIAS
        // ════════════════════════════════════════════════════════════════

        $asistencias = Attendance::with(['attendanceSession' => function ($query) {
                $query->select('id', 'horario_id', 'created_at');
            }])
            ->whereIn('attendance_session_id', function ($query) use ($horarioId) {
                $query->select('id')
                    ->from('attendance_sessions')
                    ->where('horario_id', $horarioId);
            })
            ->where('estudiante_id', $estudianteId)
            ->orderBy('registrado_at', 'desc')
            ->get()
            ->map(fn($attendance) => [
                'id' => $attendance->id,
                'fecha' => $attendance->attendanceSession->created_at->format('Y-m-d'),
                'estatus' => $attendance->estatus,
                'registrado_at' => $attendance->registrado_at?->format('H:i'),
                'observacion' => $attendance->observacion,
            ]);

        // ════════════════════════════════════════════════════════════════
        // 4. INCIDENCIAS ANTI-COPIA
        // ════════════════════════════════════════════════════════════════

        $incidencias = ExamIncident::with(['quizAttempt.quiz' => function ($query) {
                $query->select('id', 'titulo');
            }])
            ->whereIn('quiz_attempt_id', function ($query) use ($horarioId, $estudiante) {
                $query->select('qa.id')
                    ->from('quiz_attempts as qa')
                    ->join('quizzes as q', 'qa.quiz_id', '=', 'q.id')
                    ->where('q.horario_id', $horarioId)
                    ->where('qa.user_id', $estudiante->user_id);
            })
            ->orderBy('detected_at', 'desc')
            ->get()
            ->map(fn($incident) => [
                'id' => $incident->id,
                'quiz_titulo' => $incident->quizAttempt->quiz->titulo ?? 'N/A',
                'tipo' => $incident->tipo,
                'descripcion' => $incident->descripcion,
                'gravedad' => $incident->gravedad,
                'detected_at' => $incident->detected_at->format('Y-m-d H:i:s'),
            ]);

        // ════════════════════════════════════════════════════════════════
        // RESUMEN ESTADÍSTICO
        // ════════════════════════════════════════════════════════════════

        $totalSesiones = DB::table('attendance_sessions')
            ->where('horario_id', $horarioId)
            ->where('abierta', false)
            ->count();

        $asistenciasPresentes = $asistencias->where('estatus', 'presente')->count();

        return response()->json([
            'message' => 'Expediente del estudiante',
            'data' => [
                'estudiante' => [
                    'id' => $estudiante->id,
                    'nombre_completo' => $estudiante->nombre_completo,
                    'cedula' => $estudiante->cedula,
                    'matricula' => $estudiante->matricula,
                    'email' => $estudiante->email,
                ],
                'horario' => [
                    'id' => $horario->id,
                    'materia' => $horario->materia->nombre,
                    'codigo' => $horario->codigo,
                ],
                'resumen' => [
                    'examenes_realizados' => $examenes->count(),
                    'tareas_entregadas' => $tareas->count(),
                    'asistencias_presentes' => $asistenciasPresentes,
                    'total_sesiones' => $totalSesiones,
                    'porcentaje_asistencia' => $totalSesiones > 0
                        ? round(($asistenciasPresentes / $totalSesiones) * 100, 1)
                        : 0,
                    'incidencias_total' => $incidencias->count(),
                ],
                'examenes' => $examenes,
                'tareas' => $tareas,
                'asistencias' => $asistencias,
                'incidencias' => $incidencias,
            ],
        ]);
    }

    /**
     * GET /profesor/auditoria/quiz/{quiz_id}/incidencias
     * Vista de incidencias por examen
     */
    public function incidenciasPorExamen(Request $request, $quizId): JsonResponse
    {
        $user = auth()->user();
        $profesor = $user->profesor;

        // Validar que el quiz pertenezca al profesor
        $quiz = Quiz::where('id', $quizId)
            ->whereIn('horario_id', function ($query) use ($profesor) {
                $query->select('id')
                    ->from('horarios')
                    ->where('profesor_id', $profesor->id);
            })
            ->with('horario.materia')
            ->firstOrFail();

        // Obtener todos los intentos con incidencias
        $intentosConIncidencias = QuizAttempt::with([
                'examIncidents' => function ($query) {
                    $query->orderBy('detected_at', 'asc');
                }
            ])
            ->where('quiz_id', $quizId)
            ->whereHas('examIncidents')
            ->get()
            ->map(function ($attempt) {
                // Obtener estudiante desde user_id
                $user = \App\Models\User::find($attempt->user_id);
                $estudiante = $user ? $user->estudiante : null;

                return [
                    'attempt_id' => $attempt->id,
                    'estudiante' => [
                        'id' => $estudiante->id ?? null,
                        'nombre_completo' => $estudiante->nombre_completo ?? 'Desconocido',
                        'matricula' => $estudiante->matricula ?? 'N/A',
                    ],
                    'estado' => $attempt->estado,
                    'nota_obtenida' => $attempt->nota_obtenida,
                    'advertencias_count' => $attempt->advertencias_count,
                    'incidencias' => $attempt->examIncidents->map(fn($incident) => [
                        'id' => $incident->id,
                        'tipo' => $incident->tipo,
                        'descripcion' => $incident->descripcion,
                        'gravedad' => $incident->gravedad,
                        'detected_at' => $incident->detected_at->format('H:i:s'),
                    ]),
                ];
            });

        // Estadísticas generales
        $totalIntentos = QuizAttempt::where('quiz_id', $quizId)->count();
        $intentosConIncidenciasCount = $intentosConIncidencias->count();
        $totalIncidencias = ExamIncident::whereIn('quiz_attempt_id', function ($query) use ($quizId) {
            $query->select('id')
                ->from('quiz_attempts')
                ->where('quiz_id', $quizId);
        })->count();

        return response()->json([
            'message' => 'Incidencias del examen',
            'data' => [
                'quiz' => [
                    'id' => $quiz->id,
                    'titulo' => $quiz->titulo,
                    'materia' => $quiz->horario->materia->nombre,
                ],
                'estadisticas' => [
                    'total_intentos' => $totalIntentos,
                    'intentos_con_incidencias' => $intentosConIncidenciasCount,
                    'porcentaje_incidencias' => $totalIntentos > 0
                        ? round(($intentosConIncidenciasCount / $totalIntentos) * 100, 1)
                        : 0,
                    'total_incidencias' => $totalIncidencias,
                ],
                'intentos' => $intentosConIncidencias,
            ],
        ]);
    }
}
