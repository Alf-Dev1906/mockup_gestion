<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\QuizAttempt;
use App\Models\QuizAnswer;
use App\Models\Profesor;
use App\Services\QuizGradingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class QuizResultController extends Controller
{
    protected QuizGradingService $gradingService;

    public function __construct(QuizGradingService $gradingService)
    {
        $this->gradingService = $gradingService;
    }

    /**
     * Ver intento completo (profesor)
     * GET /api/profesor/quiz-attempts/{id}
     */
    public function show(int $id): JsonResponse
    {
        $user = Auth::user();
        $profesor = Profesor::where('email', $user->email)->first();

        $intento = QuizAttempt::with([
            'quiz.preguntas',
            'estudiante',
            'inscripcion',
            'respuestas' => function($q) {
                $q->with('pregunta')->orderBy('created_at');
            },
            'incidencias' => function($q) {
                $q->orderBy('ocurrido_at', 'desc');
            }
        ])->find($id);

        if (!$intento) {
            return response()->json([
                'success' => false,
                'message' => 'Intento no encontrado'
            ], 404);
        }

        // Verificar que el profesor tenga acceso al horario del quiz
        if ($intento->quiz->horario->profesor_id !== $profesor->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a este intento'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $intento
        ]);
    }

    /**
     * Calificar respuesta manualmente (preguntas multimedia o con justificación)
     * PUT /api/profesor/quiz-answers/{id}/calificar
     */
    public function calificarRespuesta(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'es_correcta' => 'required|boolean',
            'puntos_obtenidos' => 'required|numeric|min:0',
            'comentario' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $profesor = Profesor::where('email', $user->email)->first();

        $respuesta = QuizAnswer::with('intento.quiz.horario')->find($id);

        if (!$respuesta) {
            return response()->json([
                'success' => false,
                'message' => 'Respuesta no encontrada'
            ], 404);
        }

        // Verificar acceso
        if ($respuesta->intento->quiz->horario->profesor_id !== $profesor->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a esta respuesta'
            ], 403);
        }

        // Verificar que no exceda los puntos máximos de la pregunta
        if ($request->puntos_obtenidos > $respuesta->pregunta->puntos) {
            return response()->json([
                'success' => false,
                'message' => 'Los puntos obtenidos no pueden exceder los puntos de la pregunta'
            ], 422);
        }

        // Actualizar respuesta
        $respuesta->update([
            'es_correcta' => $request->es_correcta,
            'puntos_obtenidos' => $request->puntos_obtenidos,
        ]);

        // Recalcular nota total del intento
        $intento = $respuesta->intento;
        $notaTotal = $intento->respuestas()->sum('puntos_obtenidos');
        
        $intento->update([
            'nota_obtenida' => $notaTotal,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Respuesta calificada exitosamente',
            'data' => [
                'respuesta' => $respuesta->fresh(),
                'nota_total_intento' => $notaTotal
            ]
        ]);
    }

    /**
     * Publicar nota final del intento
     * POST /api/profesor/quiz-attempts/{id}/publicar-nota
     */
    public function publicarNota(int $id): JsonResponse
    {
        $user = Auth::user();
        $profesor = Profesor::where('email', $user->email)->first();

        $intento = QuizAttempt::with('quiz.horario', 'estudiante')->find($id);

        if (!$intento) {
            return response()->json([
                'success' => false,
                'message' => 'Intento no encontrado'
            ], 404);
        }

        // Verificar acceso
        if ($intento->quiz->horario->profesor_id !== $profesor->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a este intento'
            ], 403);
        }

        // Verificar que todas las respuestas estén calificadas
        $respuestasSinCalificar = $intento->respuestas()
                                         ->whereNull('es_correcta')
                                         ->count();

        if ($respuestasSinCalificar > 0) {
            return response()->json([
                'success' => false,
                'message' => "Hay {$respuestasSinCalificar} respuestas sin calificar"
            ], 422);
        }

        // Marcar como calificado
        $intento->update([
            'estado' => 'calificado',
        ]);

        // Crear notificación para el estudiante
        \App\Models\Notification::crearNotificacionCalificacion(
            $intento->estudiante->user_id ?? null,
            'quiz',
            "Tu examen '{$intento->quiz->titulo}' ha sido calificado. Nota: {$intento->nota_obtenida}/{$intento->nota_maxima}",
            $intento->nota_obtenida,
            "/estudiante/quizzes/{$intento->quiz_id}/resultado/{$intento->id}"
        );

        return response()->json([
            'success' => true,
            'message' => 'Nota publicada exitosamente',
            'data' => $intento->fresh()
        ]);
    }

    /**
     * Anular intento
     * POST /api/profesor/quiz-attempts/{id}/anular
     */
    public function anularIntento(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'razon' => 'required|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $profesor = Profesor::where('email', $user->email)->first();

        $intento = QuizAttempt::with('quiz.horario')->find($id);

        if (!$intento) {
            return response()->json([
                'success' => false,
                'message' => 'Intento no encontrado'
            ], 404);
        }

        // Verificar acceso
        if ($intento->quiz->horario->profesor_id !== $profesor->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a este intento'
            ], 403);
        }

        // Marcar como anulado
        $intento->update([
            'estado' => 'anulado',
            'nota_obtenida' => 0,
        ]);

        // Crear notificación para el estudiante
        \App\Models\Notification::crearNotificacion(
            $intento->estudiante->user_id ?? null,
            'quiz_anulado',
            "Intento de examen anulado",
            "Tu intento del examen '{$intento->quiz->titulo}' ha sido anulado. Razón: {$request->razon}",
            null,
            ['razon' => $request->razon]
        );

        return response()->json([
            'success' => true,
            'message' => 'Intento anulado exitosamente',
            'data' => $intento->fresh()
        ]);
    }

    /**
     * Obtener estadísticas del quiz
     * GET /api/profesor/quizzes/{id}/estadisticas
     */
    public function estadisticas(int $quizId): JsonResponse
    {
        $user = Auth::user();
        $profesor = Profesor::where('email', $user->email)->first();

        $quiz = \App\Models\Quiz::with('horario')->find($quizId);

        if (!$quiz) {
            return response()->json([
                'success' => false,
                'message' => 'Quiz no encontrado'
            ], 404);
        }

        // Verificar acceso
        if ($quiz->horario->profesor_id !== $profesor->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a este quiz'
            ], 403);
        }

        $resumen = $this->gradingService->obtenerResumenCalificaciones($quiz);

        // Estadísticas adicionales
        $intentosTotales = $quiz->intentos()->count();
        $intentosEnProgreso = $quiz->intentos()->where('estado', 'en_progreso')->count();
        $intentosEnviados = $quiz->intentos()->where('estado', 'enviado')->count();
        $intentosCalificados = $quiz->intentos()->where('estado', 'calificado')->count();
        $intentosAnulados = $quiz->intentos()->where('estado', 'anulado')->count();

        // Distribución de notas
        $intentosCalificadosData = $quiz->intentos()
                                       ->where('estado', 'calificado')
                                       ->get();

        $distribucion = [
            '0-5' => $intentosCalificadosData->where('nota_obtenida', '>=', 0)->where('nota_obtenida', '<', 5)->count(),
            '5-10' => $intentosCalificadosData->where('nota_obtenida', '>=', 5)->where('nota_obtenida', '<', 10)->count(),
            '10-15' => $intentosCalificadosData->where('nota_obtenida', '>=', 10)->where('nota_obtenida', '<', 15)->count(),
            '15-20' => $intentosCalificadosData->where('nota_obtenida', '>=', 15)->where('nota_obtenida', '<=', 20)->count(),
        ];

        return response()->json([
            'success' => true,
            'data' => [
                'resumen' => $resumen,
                'intentos' => [
                    'total' => $intentosTotales,
                    'en_progreso' => $intentosEnProgreso,
                    'enviados' => $intentosEnviados,
                    'calificados' => $intentosCalificados,
                    'anulados' => $intentosAnulados,
                ],
                'distribucion_notas' => $distribucion,
            ]
        ]);
    }

    /**
     * Ver incidencias de proctoring de un intento (profesor)
     * GET /api/profesor/quiz-attempts/{id}/incidencias
     */
    public function incidencias(int $id): JsonResponse
    {
        $user = Auth::user();
        $profesor = Profesor::where('email', $user->email)->first();

        $intento = QuizAttempt::with(['quiz.horario.materia', 'estudiante', 'incidencias'])
            ->find($id);

        if (!$intento) {
            return response()->json([
                'success' => false,
                'message' => 'Intento no encontrado'
            ], 404);
        }

        // Verificar que el quiz pertenece al profesor
        $materia = $intento->quiz->horario->materia;
        if ($materia->profesor_id !== $profesor->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a este quiz'
            ], 403);
        }

        // Obtener incidencias ordenadas por fecha
        $incidencias = $intento->incidencias()
            ->orderBy('ocurrido_at', 'asc')
            ->get()
            ->map(function($incidencia) {
                return [
                    'id' => $incidencia->id,
                    'tipo' => $incidencia->tipo,
                    'descripcion' => $incidencia->descripcion,
                    'descripcion_legible' => $incidencia->obtenerDescripcionTipo(),
                    'metadata' => $incidencia->metadata,
                    'ocurrido_at' => $incidencia->ocurrido_at->format('Y-m-d H:i:s'),
                    'minutos_desde_inicio' => $intento->inicio_at->diffInMinutes($incidencia->ocurrido_at),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'intento' => [
                    'id' => $intento->id,
                    'estudiante' => $intento->estudiante->nombre_completo,
                    'inicio_at' => $intento->inicio_at->format('Y-m-d H:i:s'),
                    'fin_at' => $intento->fin_at?->format('Y-m-d H:i:s'),
                    'advertencias_count' => $intento->advertencias_count,
                    'advertencias_max' => $intento->quiz->advertencias_max,
                    'auto_enviado' => $intento->auto_enviado,
                ],
                'incidencias' => $incidencias,
                'estadisticas' => [
                    'total' => $incidencias->count(),
                    'por_tipo' => $incidencias->groupBy('tipo')->map->count(),
                ],
            ]
        ]);
    }
}
