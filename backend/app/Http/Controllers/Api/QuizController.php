<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Quiz;
use App\Models\QuizQuestion;
use App\Models\Horario;
use App\Services\QuizGradingService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;

class QuizController extends Controller
{
    protected QuizGradingService $gradingService;

    public function __construct(QuizGradingService $gradingService)
    {
        $this->gradingService = $gradingService;
    }

    /**
     * Listar quizzes del profesor autenticado
     * GET /api/profesor/quizzes
     */
    public function index(Request $request): JsonResponse
    {
        $user = Auth::user();
        
        // Obtener profesor_id desde el email
        $profesor = \App\Models\Profesor::where('email', $user->email)->first();
        
        if (!$profesor) {
            return response()->json([
                'success' => false,
                'message' => 'Profesor no encontrado'
            ], 404);
        }

        $query = Quiz::with(['horario.materia', 'preguntas'])
            ->whereHas('horario', function($q) use ($profesor) {
                $q->where('profesor_id', $profesor->id);
            });

        // Filtros
        if ($request->filled('horario_id')) {
            $query->where('horario_id', $request->horario_id);
        }

        if ($request->filled('estado')) {
            $query->where('estado', $request->estado);
        }

        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }

        $quizzes = $query->orderBy('created_at', 'desc')->get();

        return response()->json([
            'success' => true,
            'data' => $quizzes
        ]);
    }

    /**
     * Crear quiz
     * POST /api/profesor/quizzes
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'horario_id' => 'required|exists:horarios,id',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'tipo' => 'required|in:quiz,examen,practica',
            'instrucciones' => 'nullable|string',
            'duracion_minutos' => 'required|integer|min:1|max:300',
            'intentos_permitidos' => 'required|integer|min:1|max:5',
            'advertencias_max' => 'required|integer|min:1|max:10',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date|after:fecha_inicio',
            'visible_desde' => 'nullable|date',
            'orden_aleatorio' => 'boolean',
            'mostrar_resultado_inmediato' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $profesor = \App\Models\Profesor::where('email', $user->email)->first();

        // Verificar que el horario pertenezca al profesor
        $horario = Horario::where('id', $request->horario_id)
                         ->where('profesor_id', $profesor->id)
                         ->first();

        if (!$horario) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a este horario'
            ], 403);
        }

        $quiz = Quiz::create([
            'horario_id' => $request->horario_id,
            'titulo' => $request->titulo,
            'descripcion' => $request->descripcion,
            'tipo' => $request->tipo,
            'instrucciones' => $request->instrucciones,
            'duracion_minutos' => $request->duracion_minutos,
            'intentos_permitidos' => $request->intentos_permitidos,
            'advertencias_max' => $request->advertencias_max ?? 3,
            'fecha_inicio' => $request->fecha_inicio,
            'fecha_fin' => $request->fecha_fin,
            'visible_desde' => $request->visible_desde,
            'estado' => 'borrador',
            'orden_aleatorio' => $request->orden_aleatorio ?? false,
            'mostrar_resultado_inmediato' => $request->mostrar_resultado_inmediato ?? true,
            'created_by' => $user->id,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Quiz creado exitosamente',
            'data' => $quiz->load('horario.materia')
        ], 201);
    }

    /**
     * Ver quiz individual
     * GET /api/profesor/quizzes/{id}
     */
    public function show(int $id): JsonResponse
    {
        $user = Auth::user();
        $profesor = \App\Models\Profesor::where('email', $user->email)->first();

        $quiz = Quiz::with(['horario.materia', 'preguntas' => function($q) {
            $q->orderBy('orden');
        }])->find($id);

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

        return response()->json([
            'success' => true,
            'data' => $quiz
        ]);
    }

    /**
     * Actualizar quiz
     * PUT /api/profesor/quizzes/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'titulo' => 'string|max:255',
            'descripcion' => 'nullable|string',
            'tipo' => 'in:quiz,examen,practica',
            'instrucciones' => 'nullable|string',
            'duracion_minutos' => 'integer|min:1|max:300',
            'intentos_permitidos' => 'integer|min:1|max:5',
            'advertencias_max' => 'integer|min:1|max:10',
            'fecha_inicio' => 'nullable|date',
            'fecha_fin' => 'nullable|date',
            'visible_desde' => 'nullable|date',
            'estado' => 'in:borrador,publicado,cerrado',
            'orden_aleatorio' => 'boolean',
            'mostrar_resultado_inmediato' => 'boolean',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $profesor = \App\Models\Profesor::where('email', $user->email)->first();

        $quiz = Quiz::find($id);

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

        $quiz->update($request->only([
            'titulo', 'descripcion', 'tipo', 'instrucciones',
            'duracion_minutos', 'intentos_permitidos', 'advertencias_max',
            'fecha_inicio', 'fecha_fin', 'visible_desde', 'estado',
            'orden_aleatorio', 'mostrar_resultado_inmediato'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Quiz actualizado exitosamente',
            'data' => $quiz->fresh()->load('horario.materia')
        ]);
    }

    /**
     * Eliminar quiz
     * DELETE /api/profesor/quizzes/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $user = Auth::user();
        $profesor = \App\Models\Profesor::where('email', $user->email)->first();

        $quiz = Quiz::find($id);

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

        // No permitir eliminar si tiene intentos
        if ($quiz->intentos()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar un quiz que tiene intentos registrados'
            ], 422);
        }

        $quiz->delete();

        return response()->json([
            'success' => true,
            'message' => 'Quiz eliminado exitosamente'
        ]);
    }

    /**
     * Ver resultados del quiz
     * GET /api/profesor/quizzes/{id}/resultados
     */
    public function resultados(Request $request, int $id): JsonResponse
    {
        $user = Auth::user();
        $profesor = \App\Models\Profesor::where('email', $user->email)->first();

        $quiz = Quiz::with('horario')->find($id);

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

        // ✅ Paginación de intentos (50 por página, máximo 100)
        $perPage = min((int) $request->input('per_page', 50), 100);
        
        $intentos = \App\Models\QuizAttempt::where('quiz_id', $id)
            ->with('estudiante')
            ->whereIn('estado', ['enviado', 'calificado'])
            ->orderBy('created_at', 'desc')
            ->paginate($perPage);

        // Obtener resumen estadístico (solo calcular sobre todos los intentos, no paginados)
        $resumen = $this->gradingService->obtenerResumenCalificaciones($quiz);

        return response()->json([
            'success' => true,
            'data' => [
                'quiz' => $quiz->only(['id', 'titulo', 'tipo', 'duracion_minutos', 'intentos_permitidos']),
                'intentos' => $intentos->items(), // Solo items de la página actual
                'resumen' => $resumen,
                'pagination' => [
                    'current_page' => $intentos->currentPage(),
                    'last_page' => $intentos->lastPage(),
                    'per_page' => $intentos->perPage(),
                    'total' => $intentos->total(),
                    'from' => $intentos->firstItem(),
                    'to' => $intentos->lastItem(),
                ]
            ]
        ]);
    }

    /**
     * Listar preguntas del quiz
     * GET /api/profesor/quizzes/{id}/preguntas
     */
    public function indexPreguntas(int $quizId): JsonResponse
    {
        $user = Auth::user();
        $profesor = \App\Models\Profesor::where('email', $user->email)->first();

        $quiz = Quiz::find($quizId);

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

        $preguntas = $quiz->preguntas()->orderBy('orden')->get();

        return response()->json([
            'success' => true,
            'data' => $preguntas
        ]);
    }

    /**
     * Crear pregunta
     * POST /api/profesor/quizzes/{id}/preguntas
     */
    public function storePregunta(Request $request, int $quizId): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'orden' => 'required|integer|min:1',
            'tipo' => 'required|in:seleccion,verdadero_falso,relacion_columnas,espacios,multimedia',
            'enunciado' => 'required|string',
            'contenido' => 'required|array',
            'puntos' => 'required|numeric|min:0|max:100',
            'obligatoria' => 'boolean',
            'retroalimentacion' => 'nullable|string',
            'archivo_url' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // ✅ Validar estructura JSON del contenido según tipo de pregunta
        $structureValidation = $this->validateQuestionStructure($request->contenido, $request->tipo);
        if (!$structureValidation['valid']) {
            return response()->json([
                'success' => false,
                'message' => 'La estructura del contenido no es válida para el tipo de pregunta',
                'errors' => $structureValidation['errors']
            ], 422);
        }

        $user = Auth::user();
        $profesor = \App\Models\Profesor::where('email', $user->email)->first();

        $quiz = Quiz::find($quizId);

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

        $pregunta = QuizQuestion::create([
            'quiz_id' => $quizId,
            'orden' => $request->orden,
            'tipo' => $request->tipo,
            'enunciado' => $request->enunciado,
            'contenido' => $request->contenido,
            'puntos' => $request->puntos,
            'obligatoria' => $request->obligatoria ?? true,
            'retroalimentacion' => $request->retroalimentacion,
            'archivo_url' => $request->archivo_url,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Pregunta creada exitosamente',
            'data' => $pregunta
        ], 201);
    }

    /**
     * Actualizar pregunta
     * PUT /api/profesor/preguntas/{id}
     */
    public function updatePregunta(Request $request, int $id): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'orden' => 'integer|min:1',
            'tipo' => 'in:seleccion,verdadero_falso,relacion_columnas,espacios,multimedia',
            'enunciado' => 'string',
            'contenido' => 'array',
            'puntos' => 'numeric|min:0|max:100',
            'obligatoria' => 'boolean',
            'retroalimentacion' => 'nullable|string',
            'archivo_url' => 'nullable|string',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $profesor = \App\Models\Profesor::where('email', $user->email)->first();

        $pregunta = QuizQuestion::with('quiz.horario')->find($id);

        if (!$pregunta) {
            return response()->json([
                'success' => false,
                'message' => 'Pregunta no encontrada'
            ], 404);
        }

        // Verificar acceso
        if ($pregunta->quiz->horario->profesor_id !== $profesor->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a esta pregunta'
            ], 403);
        }

        // ✅ Validar estructura JSON si se está actualizando contenido o tipo
        if ($request->filled('contenido') && $request->filled('tipo')) {
            $structureValidation = $this->validateQuestionStructure($request->contenido, $request->tipo);
            if (!$structureValidation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => 'La estructura del contenido no es válida para el tipo de pregunta',
                    'errors' => $structureValidation['errors']
                ], 422);
            }
        } elseif ($request->filled('contenido')) {
            // Si solo se actualiza contenido, usar el tipo existente
            $structureValidation = $this->validateQuestionStructure($request->contenido, $pregunta->tipo);
            if (!$structureValidation['valid']) {
                return response()->json([
                    'success' => false,
                    'message' => 'La estructura del contenido no es válida para el tipo de pregunta',
                    'errors' => $structureValidation['errors']
                ], 422);
            }
        }

        $pregunta->update($request->only([
            'orden', 'tipo', 'enunciado', 'contenido', 'puntos',
            'obligatoria', 'retroalimentacion', 'archivo_url'
        ]));

        return response()->json([
            'success' => true,
            'message' => 'Pregunta actualizada exitosamente',
            'data' => $pregunta->fresh()
        ]);
    }

    /**
     * Eliminar pregunta
     * DELETE /api/profesor/preguntas/{id}
     */
    public function destroyPregunta(int $id): JsonResponse
    {
        $user = Auth::user();
        $profesor = \App\Models\Profesor::where('email', $user->email)->first();

        $pregunta = QuizQuestion::with('quiz.horario')->find($id);

        if (!$pregunta) {
            return response()->json([
                'success' => false,
                'message' => 'Pregunta no encontrada'
            ], 404);
        }

        // Verificar acceso
        if ($pregunta->quiz->horario->profesor_id !== $profesor->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a esta pregunta'
            ], 403);
        }

        // No permitir eliminar si el quiz tiene intentos
        if ($pregunta->quiz->intentos()->count() > 0) {
            return response()->json([
                'success' => false,
                'message' => 'No se puede eliminar una pregunta de un quiz que tiene intentos'
            ], 422);
        }

        $pregunta->delete();

        return response()->json([
            'success' => true,
            'message' => 'Pregunta eliminada exitosamente'
        ]);
    }

    /**
     * Validar estructura JSON del contenido según tipo de pregunta
     * 
     * @param array $contenido
     * @param string $tipo
     * @return array ['valid' => bool, 'errors' => array]
     */
    private function validateQuestionStructure(array $contenido, string $tipo): array
    {
        $errors = [];

        switch ($tipo) {
            case 'seleccion':
                // Validar estructura de selección múltiple
                if (!isset($contenido['opciones']) || !is_array($contenido['opciones'])) {
                    $errors[] = 'El campo "opciones" es requerido y debe ser un array';
                    break;
                }

                if (count($contenido['opciones']) < 2) {
                    $errors[] = 'Debe haber al menos 2 opciones';
                    break;
                }

                $tieneCorrecta = false;
                foreach ($contenido['opciones'] as $index => $opcion) {
                    if (!isset($opcion['id']) || !isset($opcion['texto'])) {
                        $errors[] = "La opción {$index} debe tener 'id' y 'texto'";
                    }
                    if (!isset($opcion['es_correcta'])) {
                        $errors[] = "La opción {$index} debe tener 'es_correcta' (boolean)";
                    }
                    if (isset($opcion['es_correcta']) && $opcion['es_correcta'] === true) {
                        $tieneCorrecta = true;
                    }
                }

                if (!$tieneCorrecta) {
                    $errors[] = 'Debe haber al menos una opción correcta marcada';
                }

                // Validar permite_multiple
                if (isset($contenido['permite_multiple']) && !is_bool($contenido['permite_multiple'])) {
                    $errors[] = 'El campo "permite_multiple" debe ser boolean';
                }
                break;

            case 'verdadero_falso':
                // Validar verdadero/falso
                if (!isset($contenido['respuesta_correcta'])) {
                    $errors[] = 'El campo "respuesta_correcta" es requerido';
                    break;
                }

                if (!is_bool($contenido['respuesta_correcta'])) {
                    $errors[] = 'El campo "respuesta_correcta" debe ser boolean (true o false)';
                }

                // Validar justificación si está presente
                if (isset($contenido['justificacion_requerida']) && !is_bool($contenido['justificacion_requerida'])) {
                    $errors[] = 'El campo "justificacion_requerida" debe ser boolean';
                }

                if (isset($contenido['puntos_justificacion']) && !is_numeric($contenido['puntos_justificacion'])) {
                    $errors[] = 'El campo "puntos_justificacion" debe ser numérico';
                }
                break;

            case 'relacion_columnas':
                // Validar relación de columnas
                if (!isset($contenido['columna_izquierda']) || !is_array($contenido['columna_izquierda'])) {
                    $errors[] = 'El campo "columna_izquierda" es requerido y debe ser un array';
                }

                if (!isset($contenido['columna_derecha']) || !is_array($contenido['columna_derecha'])) {
                    $errors[] = 'El campo "columna_derecha" es requerido y debe ser un array';
                }

                if (!isset($contenido['relaciones_correctas']) || !is_array($contenido['relaciones_correctas'])) {
                    $errors[] = 'El campo "relaciones_correctas" es requerido y debe ser un array';
                }

                // Validar que ambas columnas tengan el mismo número de elementos
                if (isset($contenido['columna_izquierda']) && isset($contenido['columna_derecha'])) {
                    if (count($contenido['columna_izquierda']) !== count($contenido['columna_derecha'])) {
                        $errors[] = 'Ambas columnas deben tener el mismo número de elementos';
                    }
                }
                break;

            case 'espacios':
                // Validar rellenar espacios
                if (!isset($contenido['texto_con_espacios']) || !is_string($contenido['texto_con_espacios'])) {
                    $errors[] = 'El campo "texto_con_espacios" es requerido y debe ser string';
                }

                if (!isset($contenido['respuestas_correctas']) || !is_array($contenido['respuestas_correctas'])) {
                    $errors[] = 'El campo "respuestas_correctas" es requerido y debe ser un array';
                }

                // Validar que el número de espacios coincida con respuestas
                if (isset($contenido['texto_con_espacios']) && isset($contenido['respuestas_correctas'])) {
                    $numEspacios = substr_count($contenido['texto_con_espacios'], '___');
                    $numRespuestas = count($contenido['respuestas_correctas']);
                    if ($numEspacios !== $numRespuestas) {
                        $errors[] = "El número de espacios (___) debe coincidir con el número de respuestas correctas. Encontrados: {$numEspacios} espacios, {$numRespuestas} respuestas";
                    }
                }

                // Validar sensibilidad a mayúsculas
                if (isset($contenido['case_sensitive']) && !is_bool($contenido['case_sensitive'])) {
                    $errors[] = 'El campo "case_sensitive" debe ser boolean';
                }
                break;

            case 'multimedia':
                // Validar pregunta multimedia (ensayo con archivo)
                if (!isset($contenido['tipo_archivo'])) {
                    $errors[] = 'El campo "tipo_archivo" es requerido para preguntas multimedia';
                }

                if (isset($contenido['tipo_archivo']) && !in_array($contenido['tipo_archivo'], ['imagen', 'audio', 'video'])) {
                    $errors[] = 'El campo "tipo_archivo" debe ser: imagen, audio o video';
                }

                if (!isset($contenido['requiere_calificacion_manual'])) {
                    $contenido['requiere_calificacion_manual'] = true;
                }
                break;

            default:
                $errors[] = "Tipo de pregunta '{$tipo}' no reconocido";
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Listar quizzes disponibles para el estudiante
     * GET /api/estudiante/quizzes
     */
    public function listarEstudiante(Request $request): JsonResponse
    {
        $user = Auth::user();
        $estudiante = \App\Models\Estudiante::where('email', $user->email)->first();

        if (!$estudiante) {
            return response()->json([
                'success' => false,
                'message' => 'Estudiante no encontrado'
            ], 404);
        }

        // Obtener quizzes de horarios en que está inscrito
        $quizzes = Quiz::with(['horario.materia', 'horario.profesor'])
            ->whereIn('horario_id', function ($query) use ($estudiante) {
                $query->select('horario_id')
                    ->from('inscripciones')
                    ->where('estudiante_id', $estudiante->id)
                    ->whereIn('estatus', ['inscrito', 'cursando']);
            })
            ->where('estado', 'publicado')
            ->where(function ($query) {
                $query->whereNull('fecha_inicio')
                    ->orWhere('fecha_inicio', '<=', now());
            })
            ->get()
            ->map(function ($quiz) use ($estudiante) {
                // Obtener intento actual (en progreso)
                $intentoActual = \App\Models\QuizAttempt::where('quiz_id', $quiz->id)
                    ->where('estudiante_id', $estudiante->id)
                    ->where('estado', 'en_progreso')
                    ->first();

                // Contar intentos completados
                $intentosCompletados = \App\Models\QuizAttempt::where('quiz_id', $quiz->id)
                    ->where('estudiante_id', $estudiante->id)
                    ->whereIn('estado', ['enviado', 'calificado'])
                    ->count();

                $tieneIntentosDisponibles = $intentosCompletados < $quiz->intentos_permitidos;

                return [
                    'id' => $quiz->id,
                    'titulo' => $quiz->titulo,
                    'descripcion' => $quiz->descripcion,
                    'tipo' => $quiz->tipo,
                    'duracion_minutos' => $quiz->duracion_minutos,
                    'intentos_permitidos' => $quiz->intentos_permitidos,
                    'intentos_realizados' => $intentosCompletados,
                    'tiene_intentos_disponibles' => $tieneIntentosDisponibles,
                    'fecha_inicio' => $quiz->fecha_inicio,
                    'fecha_fin' => $quiz->fecha_fin,
                    'horario' => [
                        'id' => $quiz->horario->id,
                        'materia' => [
                            'nombre' => $quiz->horario->materia->nombre ?? 'N/A',
                        ],
                        'profesor' => [
                            'nombre' => $quiz->horario->profesor 
                                ? $quiz->horario->profesor->nombre . ' ' . $quiz->horario->profesor->apellido
                                : 'N/A',
                        ],
                    ],
                    'intento_actual' => $intentoActual ? [
                        'id' => $intentoActual->id,
                        'tiempo_transcurrido_seg' => $intentoActual->getTiempoTranscurrido(),
                    ] : null,
                ];
            });

        return response()->json([
            'success' => true,
            'data' => $quizzes
        ]);
    }
}
