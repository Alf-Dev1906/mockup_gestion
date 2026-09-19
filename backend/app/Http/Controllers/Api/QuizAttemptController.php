<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizAnswer;
use App\Models\Estudiante;
use App\Models\Inscripcion;
use App\Services\QuizGradingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class QuizAttemptController extends Controller
{
    protected QuizGradingService $gradingService;

    public function __construct(QuizGradingService $gradingService)
    {
        $this->gradingService = $gradingService;
    }

    /**
     * Listar quizzes disponibles para el estudiante
     * GET /api/estudiante/quizzes
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        $estudiante = Estudiante::where('email', $user->email)->first();

        if (!$estudiante) {
            return response()->json([
                'success' => false,
                'message' => 'Estudiante no encontrado'
            ], 404);
        }

        // Obtener horarios del estudiante
        $horarios = Inscripcion::where('estudiante_id', $estudiante->id)
                              ->pluck('horario_id');

        // Obtener quizzes publicados de esos horarios
        $query = Quiz::with(['horario.materia', 'horario.profesor'])
            ->whereIn('horario_id', $horarios)
            ->where('estado', 'publicado');

        // Filtrar por fechas si existen
        $query->where(function($q) {
            $q->whereNull('fecha_inicio')
              ->orWhere('fecha_inicio', '<=', now());
        });

        $query->where(function($q) {
            $q->whereNull('fecha_fin')
              ->orWhere('fecha_fin', '>', now());
        });

        $quizzes = $query->orderBy('fecha_inicio', 'desc')->get();

        // Agregar información de intentos del estudiante
        $quizzes->each(function($quiz) use ($estudiante) {
            $intentos = QuizAttempt::where('quiz_id', $quiz->id)
                                  ->where('estudiante_id', $estudiante->id)
                                  ->get();
            
            $quiz->mis_intentos_count = $intentos->count();
            $quiz->tiene_intentos_disponibles = $quiz->tieneIntentosDisponibles($estudiante->id);
            $quiz->intento_actual = $quiz->intentoActual($estudiante->id);
        });

        return response()->json([
            'success' => true,
            'data' => $quizzes
        ]);
    }

    /**
     * Ver quiz individual (sin respuestas correctas)
     * GET /api/estudiante/quizzes/{id}
     */
    public function show(int $id): JsonResponse
    {
        $user = Auth::user();
        $estudiante = Estudiante::where('email', $user->email)->first();

        $quiz = Quiz::with(['horario.materia', 'preguntas' => function($q) {
            $q->orderBy('orden');
        }])->find($id);

        if (!$quiz) {
            return response()->json([
                'success' => false,
                'message' => 'Quiz no encontrado'
            ], 404);
        }

        // Verificar que el estudiante esté inscrito en el horario
        $inscripcion = Inscripcion::where('estudiante_id', $estudiante->id)
                                 ->where('horario_id', $quiz->horario_id)
                                 ->first();

        if (!$inscripcion) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a este quiz'
            ], 403);
        }

        // Remover respuestas correctas del contenido
        $quiz->preguntas->each(function($pregunta) {
            $contenido = $pregunta->contenido;
            
            // Sanitizar según tipo
            switch ($pregunta->tipo) {
                case 'seleccion':
                    // Remover es_correcta de las opciones
                    if (isset($contenido['opciones'])) {
                        foreach ($contenido['opciones'] as &$opcion) {
                            unset($opcion['es_correcta']);
                        }
                    }
                    break;
                
                case 'verdadero_falso':
                    // Remover respuesta_correcta
                    unset($contenido['respuesta_correcta']);
                    break;
                
                case 'relacion_columnas':
                    // Remover pares_correctos
                    unset($contenido['pares_correctos']);
                    break;
                
                case 'espacios':
                    // Remover respuestas_validas
                    if (isset($contenido['espacios'])) {
                        foreach ($contenido['espacios'] as &$espacio) {
                            unset($espacio['respuestas_validas']);
                        }
                    }
                    break;
            }
            
            $pregunta->contenido = $contenido;
        });

        // Información de intentos
        $intentos = QuizAttempt::where('quiz_id', $id)
                              ->where('estudiante_id', $estudiante->id)
                              ->get();

        return response()->json([
            'success' => true,
            'data' => [
                'quiz' => $quiz,
                'mis_intentos_count' => $intentos->count(),
                'tiene_intentos_disponibles' => $quiz->tieneIntentosDisponibles($estudiante->id),
                'intento_actual' => $quiz->intentoActual($estudiante->id)
            ]
        ]);
    }

    /**
     * Iniciar intento
     * POST /api/estudiante/quizzes/{id}/iniciar
     */
    public function iniciar(int $id): JsonResponse
    {
        $user = Auth::user();
        $estudiante = Estudiante::where('email', $user->email)->first();

        $quiz = Quiz::with('preguntas')->find($id);

        if (!$quiz) {
            return response()->json([
                'success' => false,
                'message' => 'Quiz no encontrado'
            ], 404);
        }

        // Verificar inscripción
        $inscripcion = Inscripcion::where('estudiante_id', $estudiante->id)
                                 ->where('horario_id', $quiz->horario_id)
                                 ->first();

        if (!$inscripcion) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a este quiz'
            ], 403);
        }

        // Verificar intentos disponibles
        if (!$quiz->tieneIntentosDisponibles($estudiante->id)) {
            return response()->json([
                'success' => false,
                'message' => 'Has agotado el número de intentos permitidos'
            ], 422);
        }

        // Verificar si ya tiene un intento en progreso
        $intentoEnProgreso = $quiz->intentoActual($estudiante->id);
        if ($intentoEnProgreso) {
            return response()->json([
                'success' => false,
                'message' => 'Ya tienes un intento en progreso',
                'data' => $intentoEnProgreso
            ], 422);
        }

        // Calcular nota máxima
        $notaMaxima = $quiz->preguntas->sum('puntos');

        // Crear intento
        $intento = QuizAttempt::create([
            'quiz_id' => $id,
            'estudiante_id' => $estudiante->id,
            'inscripcion_id' => $inscripcion->id,
            'estado' => 'en_progreso',
            'inicio_at' => now(),
            'tiempo_restante_seg' => $quiz->duracion_minutos * 60,
            'nota_maxima' => $notaMaxima,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);

        // Crear respuestas vacías para cada pregunta
        foreach ($quiz->preguntas as $pregunta) {
            QuizAnswer::create([
                'attempt_id' => $intento->id,
                'question_id' => $pregunta->id,
                'respuesta' => [],
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Intento iniciado exitosamente',
            'data' => $intento->load('respuestas.pregunta')
        ], 201);
    }

    /**
     * Guardar respuesta (autosave)
     * POST /api/estudiante/quizzes/{id}/responder
     */
    public function responder(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'attempt_id' => 'required|exists:quiz_attempts,id',
            'question_id' => 'required|exists:quiz_questions,id',
            'respuesta' => 'required|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $estudiante = Estudiante::where('email', $user->email)->first();

        $intento = QuizAttempt::with('quiz')->find($request->attempt_id);

        // Verificar que el intento pertenezca al estudiante
        if ($intento->estudiante_id !== $estudiante->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a este intento'
            ], 403);
        }

        // Verificar que el intento esté en progreso
        if ($intento->estado !== 'en_progreso') {
            return response()->json([
                'success' => false,
                'message' => 'Este intento ya ha sido enviado'
            ], 422);
        }

        // Verificar tiempo restante
        if ($intento->estaVencido()) {
            // Auto-enviar
            $this->enviarAutomaticamente($intento);
            
            return response()->json([
                'success' => false,
                'message' => 'El tiempo se ha agotado. El examen fue enviado automáticamente'
            ], 422);
        }

        // Buscar o crear respuesta
        $respuesta = QuizAnswer::updateOrCreate(
            [
                'attempt_id' => $request->attempt_id,
                'question_id' => $request->question_id,
            ],
            [
                'respuesta' => $request->respuesta
            ]
        );

        return response()->json([
            'success' => true,
            'message' => 'Respuesta guardada',
            'data' => $respuesta
        ]);
    }

    /**
     * Enviar intento (finalizar)
     * POST /api/estudiante/quizzes/{id}/enviar
     */
    public function enviar(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'attempt_id' => 'required|exists:quiz_attempts,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $estudiante = Estudiante::where('email', $user->email)->first();

        $intento = QuizAttempt::with(['quiz', 'respuestas.pregunta'])->find($request->attempt_id);

        // Verificar que el intento pertenezca al estudiante
        if ($intento->estudiante_id !== $estudiante->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a este intento'
            ], 403);
        }

        // Verificar que el intento esté en progreso
        if ($intento->estado !== 'en_progreso') {
            return response()->json([
                'success' => false,
                'message' => 'Este intento ya ha sido enviado'
            ], 422);
        }

        // Marcar como enviado
        $intento->update([
            'estado' => 'enviado',
            'fin_at' => now(),
        ]);

        // Calificar automáticamente
        $this->gradingService->calificarIntento($intento);

        // Recargar intento con datos actualizados
        $intento->fresh()->load('respuestas.pregunta');

        return response()->json([
            'success' => true,
            'message' => 'Intento enviado y calificado exitosamente',
            'data' => $intento
        ]);
    }

    /**
     * Registrar incidencia de proctoring
     * POST /api/estudiante/quizzes/{id}/incidencias
     */
    public function registrarIncidencia(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'attempt_id' => 'required|exists:quiz_attempts,id',
            'tipo' => 'required|in:cambio_pestana,perdida_foco,pantalla_completa,inactividad,fraude_timer,advertencia_enviada,auto_submit',
            'descripcion' => 'nullable|string|max:500',
            'ocurrido_at' => 'nullable|date',
            'metadata' => 'nullable|array',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $estudiante = Estudiante::where('email', $user->email)->first();

        $intento = QuizAttempt::find($request->attempt_id);

        if (!$intento || $intento->estudiante_id !== $estudiante->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a este intento'
            ], 403);
        }

        if ($intento->estado !== 'en_progreso') {
            return response()->json(['success' => false, 'message' => 'El intento ya finalizó'], 422);
        }

        // Registrar incidencia
        \App\Models\ExamIncident::create([
            'attempt_id' => $intento->id,
            'tipo' => $request->tipo,
            'descripcion' => $request->descripcion,
            'metadata' => $request->metadata,
            'ocurrido_at' => $request->ocurrido_at ?? now(),
        ]);

        // Incrementar advertencias en el intento
        $intento->increment('advertencias_count');
        $intento->refresh();

        // Auto-enviar si se supera el límite
        $autoEnviado = false;
        if ($intento->advertencias_count >= $intento->quiz->advertencias_max) {
            $this->enviarAutomaticamente($intento);
            $autoEnviado = true;
        }

        return response()->json([
            'success' => true,
            'data' => [
                'advertencias_count' => $intento->advertencias_count,
                'advertencias_max' => $intento->quiz->advertencias_max,
                'auto_enviado' => $autoEnviado,
            ]
        ]);
    }

    /**
     * Ver resultado del intento
     * GET /api/estudiante/quizzes/{id}/resultado
     */
    public function resultado(int $id): JsonResponse
    {
        $user = Auth::user();
        $estudiante = Estudiante::where('email', $user->email)->first();

        $intento = QuizAttempt::with([
            'quiz',
            'respuestas' => function($q) {
                $q->with('pregunta')->orderBy('created_at');
            }
        ])->find($id);

        if (!$intento) {
            return response()->json([
                'success' => false,
                'message' => 'Intento no encontrado'
            ], 404);
        }

        // Verificar que el intento pertenezca al estudiante
        if ($intento->estudiante_id !== $estudiante->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a este intento'
            ], 403);
        }

        // Verificar que esté calificado
        if ($intento->estado !== 'calificado') {
            return response()->json([
                'success' => false,
                'message' => 'Este intento aún no ha sido calificado'
            ], 422);
        }

        // Verificar si el quiz permite ver resultado inmediato
        if (!$intento->quiz->mostrar_resultado_inmediato) {
            return response()->json([
                'success' => false,
                'message' => 'El resultado aún no está disponible'
            ], 403);
        }

        return response()->json([
            'success' => true,
            'data' => $intento
        ]);
    }

    /**
     * Auto-enviar intento cuando se agota el tiempo
     */
    private function enviarAutomaticamente(QuizAttempt $intento): void
    {
        $intento->update([
            'estado' => 'enviado',
            'fin_at' => now(),
            'auto_enviado' => true,
        ]);

        // Calificar automáticamente
        $this->gradingService->calificarIntento($intento);
    }

    /**
     * Sincronizar timer del examen
     * PATCH /api/estudiante/quiz-attempts/{id}
     * 
     * Llamada cada 30 segundos desde el frontend para persistir
     * el tiempo restante. Así si el estudiante recarga, no se reinicia.
     */
    public function sincronizarTimer(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'tiempo_restante_seg' => 'required|integer|min:0',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $estudiante = Estudiante::where('email', $user->email)->first();
        $intento = QuizAttempt::find($id);

        if (!$intento || $intento->estudiante_id !== $estudiante->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a este intento'
            ], 403);
        }

        // Solo actualizar si el intento está en progreso
        if ($intento->estado !== 'en_progreso') {
            return response()->json([
                'success' => false,
                'message' => 'El intento ya finalizó'
            ], 422);
        }

        // Verificar que el tiempo no sea negativo o fuera de rango
        $tiempoRestante = $request->input('tiempo_restante_seg');
        $tiempoOriginal = $intento->quiz->duracion_minutos * 60;

        if ($tiempoRestante > $tiempoOriginal) {
            // Intento de fraude: reiniciar el timer
            $tiempoRestante = 0;
            $intento->estado = 'enviado';
            
            registrarIncidencia('fraude_timer', 'Intento de reiniciar el timer');
            $this->enviarAutomaticamente($intento);
        }

        // Actualizar tiempo restante
        $intento->update([
            'tiempo_restante_seg' => $tiempoRestante,
            'ultima_sincronizacion' => now(),
        ]);

        return response()->json([
            'success' => true,
            'data' => [
                'tiempo_restante_seg' => $intento->tiempo_restante_seg,
                'sincronizado_at' => $intento->ultima_sincronizacion,
            ]
        ]);
    }
}
