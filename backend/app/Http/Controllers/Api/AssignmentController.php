<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Assignment;
use App\Models\Submission;
use App\Models\Horario;
use App\Models\Estudiante;
use App\Services\SubmissionService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;

class AssignmentController extends Controller
{
    protected $submissionService;

    public function __construct(SubmissionService $submissionService)
    {
        $this->submissionService = $submissionService;
    }

    // ════════════════════════════════════════════════════════════════════
    // PROFESOR - CREAR Y GESTIONAR TAREAS
    // ════════════════════════════════════════════════════════════════════

    /**
     * GET /profesor/tareas
     * Listar todas las tareas del profesor
     */
    public function listarProfesor(Request $request): JsonResponse
    {
        try {
            $user = auth()->user();
            $profesor = $user->profesor;

            if (!$profesor) {
                return response()->json([
                    'message' => 'No tienes un perfil de profesor asociado',
                    'data' => [],
                    'pagination' => ['current_page' => 1, 'total' => 0, 'per_page' => 20],
                ], 404);
            }

            // Tareas de horarios que dicta este profesor
            $tareas = Assignment::whereIn('horario_id', function ($query) use ($profesor) {
                $query->select('id')
                    ->from('horarios')
                    ->where('profesor_id', $profesor->id);
            })
            ->with('horario.materia')
            ->orderBy('fecha_limite', 'desc')
            ->paginate(20);

            return response()->json([
                'message' => 'Tareas listadas',
                'data' => $tareas->map(fn($t) => [
                    'id' => $t->id,
                    'titulo' => $t->titulo,
                    'materia' => $t->horario->materia->nombre ?? 'N/A',
                    'fecha_limite' => $t->fecha_limite->format('Y-m-d H:i'),
                    'tiempo_restante_seg' => $t->tiempo_restante,
                    'entregas_total' => 0, // TODO: implementar cuando exista tabla submissions
                    'entregas_calificadas' => 0,
                    'activa' => $t->activa,
                ]),
                'pagination' => [
                    'current_page' => $tareas->currentPage(),
                    'total' => $tareas->total(),
                    'per_page' => $tareas->perPage(),
                ],
            ]);
        } catch (\Exception $e) {
            \Log::error('Error en listarProfesor: ' . $e->getMessage());
            return response()->json([
                'message' => 'Error al cargar tareas',
                'error' => $e->getMessage(),
                'data' => [],
            ], 500);
        }
    }

    /**
     * POST /profesor/tareas
     * Crear nueva tarea con archivo guía
     */
    public function crearProfesor(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'horario_id' => 'required|exists:horarios,id',
            'titulo' => 'required|string|max:255',
            'descripcion' => 'nullable|string',
            'archivo_guia' => 'nullable|file|max:102400', // 100MB
            'fecha_limite' => 'required|date_format:Y-m-d H:i|after:now',
            'permitir_entrega_tardia' => 'boolean',
            'dias_extension_tardia' => 'integer|min:0|max:30',
            'tamano_maximo_mb' => 'integer|min:1|max:500',
            'extensiones_permitidas' => 'string',
            'puntos_totales' => 'nullable|integer|min:1|max:1000',
        ]);

        $user = auth()->user();
        $profesor = $user->profesor;

        // Validar que sea profesor del horario
        $horario = Horario::findOrFail($validated['horario_id']);
        if ($horario->profesor_id !== $profesor->id) {
            return response()->json(['message' => 'No tienes permiso para crear tareas en este horario'], 403);
        }

        // Guardar archivo guía si existe
        $archivo_path = null;
        if ($request->hasFile('archivo_guia')) {
            $archivo_path = $request->file('archivo_guia')->store(
                "assignments/{$validated['horario_id']}",
                'public'
            );
        }

        $assignment = Assignment::create([
            'horario_id' => $validated['horario_id'],
            'titulo' => $validated['titulo'],
            'descripcion' => $validated['descripcion'] ?? null,
            'archivo_guia_path' => $archivo_path,
            'fecha_limite' => $validated['fecha_limite'],
            'permitir_entrega_tardia' => $validated['permitir_entrega_tardia'] ?? false,
            'dias_extension_tardia' => $validated['dias_extension_tardia'] ?? 0,
            'tamano_maximo_mb' => $validated['tamano_maximo_mb'] ?? 50,
            'extensiones_permitidas' => $validated['extensiones_permitidas'] ?? 'pdf,docx,xlsx,zip',
            'puntos_totales' => $validated['puntos_totales'] ?? null,
            'activa' => true,
        ]);

        return response()->json([
            'message' => 'Tarea creada exitosamente',
            'data' => [
                'id' => $assignment->id,
                'titulo' => $assignment->titulo,
                'fecha_limite' => $assignment->fecha_limite->format('Y-m-d H:i'),
            ],
        ], 201);
    }

    /**
     * PUT /profesor/tareas/{id}
     * Actualizar tarea (solo si no hay entregas)
     */
    public function actualizarProfesor(Request $request, Assignment $assignment): JsonResponse
    {
        $user = auth()->user();
        $profesor = $user->profesor;

        // Validar que sea profesor del horario
        if ($assignment->horario->profesor_id !== $profesor->id) {
            return response()->json(['message' => 'No tienes permiso'], 403);
        }

        // No permite actualizar si hay entregas
        if ($assignment->submissions()->exists()) {
            return response()->json([
                'message' => 'No se puede modificar una tarea que ya tiene entregas',
            ], 422);
        }

        $validated = $request->validate([
            'titulo' => 'string|max:255',
            'descripcion' => 'nullable|string',
            'fecha_limite' => 'date_format:Y-m-d H:i|after:now',
            'permitir_entrega_tardia' => 'boolean',
            'dias_extension_tardia' => 'integer|min:0|max:30',
            'tamano_maximo_mb' => 'integer|min:1|max:500',
            'extensiones_permitidas' => 'string',
            'puntos_totales' => 'nullable|integer|min:1|max:1000',
        ]);

        $assignment->update($validated);

        return response()->json([
            'message' => 'Tarea actualizada',
            'data' => $assignment,
        ]);
    }

    /**
     * DELETE /profesor/tareas/{id}
     * Eliminar tarea (solo si no hay entregas)
     */
    public function eliminarProfesor(Assignment $assignment): JsonResponse
    {
        $user = auth()->user();
        $profesor = $user->profesor;

        // Validar que sea profesor del horario
        if ($assignment->horario->profesor_id !== $profesor->id) {
            return response()->json(['message' => 'No tienes permiso'], 403);
        }

        if ($assignment->submissions()->exists()) {
            return response()->json([
                'message' => 'No se puede eliminar una tarea que tiene entregas',
            ], 422);
        }

        // Eliminar archivo guía
        if ($assignment->archivo_guia_path) {
            Storage::disk('public')->delete($assignment->archivo_guia_path);
        }

        $assignment->delete();

        return response()->json([
            'message' => 'Tarea eliminada',
        ]);
    }

    /**
     * GET /profesor/tareas/{id}/entregas
     * Listar todas las entregas de una tarea
     */
    public function listarEntregasProfesor(Assignment $assignment): JsonResponse
    {
        $user = auth()->user();
        $profesor = $user->profesor;

        // Validar que sea profesor del horario
        if ($assignment->horario->profesor_id !== $profesor->id) {
            return response()->json(['message' => 'No tienes permiso'], 403);
        }

        $submissions = $assignment->submissions()
            ->with('estudiante', 'inscripcion')
            ->get();

        // Contar inscritos
        $inscritos_total = $assignment->horario->inscripciones()->count();
        $entregados = $submissions->count();
        $no_entregados = $inscritos_total - $entregados;

        return response()->json([
            'message' => 'Entregas listadas',
            'data' => [
                'tarea' => [
                    'id' => $assignment->id,
                    'titulo' => $assignment->titulo,
                    'fecha_limite' => $assignment->fecha_limite->format('Y-m-d H:i'),
                ],
                'estadisticas' => [
                    'inscritos' => $inscritos_total,
                    'entregados' => $entregados,
                    'no_entregados' => $no_entregados,
                    'porcentaje_entrega' => $inscritos_total > 0 ? round(($entregados / $inscritos_total) * 100, 1) : 0,
                ],
                'entregas' => $submissions->map(fn($s) => [
                    'id' => $s->id,
                    'estudiante' => $s->estudiante->nombre_completo,
                    'archivo' => $s->nombre_archivo,
                    'tamano_kb' => round($s->tamano_bytes / 1024, 2),
                    'entregado_at' => $s->entregado_at->format('Y-m-d H:i'),
                    'es_tardia' => $s->es_tardia,
                    'dias_retraso' => $s->dias_retraso,
                    'estado' => $s->estado,
                    'calificacion' => $s->calificacion,
                    'comentario' => $s->comentario_profesor,
                ]),
            ],
        ]);
    }

    // ════════════════════════════════════════════════════════════════════
    // ESTUDIANTE - VER Y ENTREGAR TAREAS
    // ════════════════════════════════════════════════════════════════════

    /**
     * GET /estudiante/tareas
     * Listar tareas disponibles para el estudiante
     */
    public function listarEstudiante(Request $request): JsonResponse
    {
        $user = auth()->user();
        $estudiante = $user->estudiante;

        if (!$estudiante) {
            return response()->json([
                'message' => 'No tienes un perfil de estudiante asociado',
                'data' => []
            ], 404);
        }

        // Tareas de horarios en que está inscrito
        $tareas = Assignment::whereIn('horario_id', function ($query) use ($estudiante) {
            $query->select('horario_id')
                ->from('inscripciones')
                ->where('estudiante_id', $estudiante->id)
                ->whereIn('estatus', ['inscrito', 'cursando']);
        })
        ->where('estatus', 'publicado')
        ->with('horario.materia', 'submissions')
        ->orderBy('fecha_limite', 'asc')
        ->get()
        ->map(fn($t) => [
            'id' => $t->id,
            'titulo' => $t->titulo,
            'descripcion' => $t->descripcion,
            'materia' => $t->horario->materia->nombre ?? 'N/A',
            'fecha_limite' => $t->fecha_limite->format('Y-m-d H:i'),
            'tiempo_restante_seg' => $this->submissionService->getTiempoRestante($t),
            'esta_vencida' => $t->esta_vencida,
            'ya_entregue' => $t->submissions->where('estudiante_id', $estudiante->id)->isNotEmpty(),
            'puntos_totales' => $t->puntos_totales,
        ]);

        return response()->json([
            'message' => 'Tareas listadas',
            'data' => $tareas,
        ]);
    }

    /**
     * GET /estudiante/tareas/{id}
     * Ver detalle de una tarea
     */
    public function verEstudiante(Assignment $assignment, Request $request): JsonResponse
    {
        $user = auth()->user();
        $estudiante = $user->estudiante;

        // Validar que esté inscrito
        $inscripcion = $assignment->horario->inscripciones()
            ->where('estudiante_id', $estudiante->id)
            ->firstOrFail();

        $submission = $assignment->submissions()
            ->where('estudiante_id', $estudiante->id)
            ->first();

        return response()->json([
            'message' => 'Detalle de tarea',
            'data' => [
                'id' => $assignment->id,
                'titulo' => $assignment->titulo,
                'descripcion' => $assignment->descripcion,
                'materia' => $assignment->horario->materia->nombre,
                'fecha_limite' => $assignment->fecha_limite->format('Y-m-d H:i'),
                'tiempo_restante_seg' => $this->submissionService->getTiempoRestante($assignment),
                'esta_vencida' => $assignment->esta_vencida,
                'puede_entregar' => $assignment->puede_entregar,
                'permite_tardia' => $assignment->permitir_entrega_tardia,
                'archivo_guia_url' => $assignment->archivo_guia_path ? Storage::disk('public')->url($assignment->archivo_guia_path) : null,
                'extensiones_permitidas' => $assignment->getExtensionesPermitidas(),
                'tamano_maximo_mb' => $assignment->tamano_maximo_mb,
                'puntos_totales' => $assignment->puntos_totales,
                'mi_entrega' => $submission ? [
                    'id' => $submission->id,
                    'archivo' => $submission->nombre_archivo,
                    'entregado_at' => $submission->entregado_at->format('Y-m-d H:i'),
                    'es_tardia' => $submission->es_tardia,
                    'dias_retraso' => $submission->dias_retraso,
                    'estado' => $submission->estado,
                    'calificacion' => $submission->calificacion,
                    'comentario_profesor' => $submission->comentario_profesor,
                ] : null,
            ],
        ]);
    }

    /**
     * POST /estudiante/tareas/{id}/entregar
     * Entregar (o reentrega) de una tarea
     */
    public function entregarEstudiante(Request $request, Assignment $assignment): JsonResponse
    {
        $user = auth()->user();
        $estudiante = $user->estudiante;

        // Validar que esté inscrito
        $inscripcion = $assignment->horario->inscripciones()
            ->where('estudiante_id', $estudiante->id)
            ->firstOrFail();

        // Validar archivo
        $request->validate([
            'archivo' => 'required|file|max:524288000', // 500MB
        ]);

        try {
            $submission = $this->submissionService->guardarEntrega(
                $assignment,
                $estudiante,
                $request->file('archivo')
            );

            return response()->json([
                'message' => 'Entrega guardada exitosamente',
                'data' => [
                    'id' => $submission->id,
                    'archivo' => $submission->nombre_archivo,
                    'entregado_at' => $submission->entregado_at->format('Y-m-d H:i'),
                    'es_tardia' => $submission->es_tardia,
                    'dias_retraso' => $submission->dias_retraso,
                    'estado' => $submission->estado,
                ],
            ], 201);
        } catch (\Exception $e) {
            // Si es error de validación de tiempo, retornar 423 (Locked)
            if (strpos($e->getMessage(), 'Plazo cerrado') !== false) {
                return response()->json([
                    'message' => 'Plazo cerrado',
                    'error' => $e->getMessage(),
                ], 423);
            }

            return response()->json([
                'message' => 'Error al guardar entrega',
                'error' => $e->getMessage(),
            ], 422);
        }
    }

    /**
     * GET /estudiante/tareas/{id}/mi-entrega
     * Ver detalle de mi entrega
     */
    public function verMiEntrega(Assignment $assignment, Request $request): JsonResponse
    {
        $user = auth()->user();
        $estudiante = $user->estudiante;

        // Validar que esté inscrito
        $inscripcion = $assignment->horario->inscripciones()
            ->where('estudiante_id', $estudiante->id)
            ->firstOrFail();

        $submission = $assignment->submissions()
            ->where('estudiante_id', $estudiante->id)
            ->first();

        if (!$submission) {
            return response()->json([
                'message' => 'No has entregado esta tarea aún',
            ], 404);
        }

        return response()->json([
            'message' => 'Detalle de mi entrega',
            'data' => [
                'id' => $submission->id,
                'archivo' => $submission->nombre_archivo,
                'tamano_kb' => round($submission->tamano_bytes / 1024, 2),
                'entregado_at' => $submission->entregado_at->format('Y-m-d H:i'),
                'es_tardia' => $submission->es_tardia,
                'dias_retraso' => $submission->dias_retraso,
                'estado' => $submission->estado,
                'calificacion' => $submission->calificacion,
                'comentario_profesor' => $submission->comentario_profesor,
                'archivo_url' => Storage::disk('public')->url($submission->archivo_path),
            ],
        ]);
    }
}
