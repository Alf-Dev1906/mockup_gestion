<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Notification;
use App\Services\NotificationService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class NotificationController extends Controller
{
    protected $notificationService;

    public function __construct(NotificationService $notificationService)
    {
        $this->notificationService = $notificationService;
    }

    /**
     * GET /notificaciones
     * Listar notificaciones del usuario autenticado
     */
    public function index(Request $request): JsonResponse
    {
        $user = auth()->user();
        $perPage = $request->query('per_page', 20);
        $filtro = $request->query('filtro', 'todas'); // todas, no-leidas, leidas

        $query = Notification::where('user_id', $user->id);

        // Aplicar filtro
        if ($filtro === 'no-leidas') {
            $query->noLeidas();
        } elseif ($filtro === 'leidas') {
            $query->leidas();
        }

        $notificaciones = $query->orderBy('created_at', 'desc')
            ->paginate($perPage);

        return response()->json([
            'message' => 'Notificaciones listadas',
            'data' => $notificaciones->map(fn($n) => [
                'id' => $n->id,
                'tipo' => $n->tipo,
                'titulo' => $n->titulo,
                'mensaje' => $n->mensaje,
                'url' => $n->url,
                'leida' => $n->leida,
                'creada_hace' => $n->hace,
                'timestamp' => $n->created_at->toIso8601String(),
            ]),
            'pagination' => [
                'current_page' => $notificaciones->currentPage(),
                'total' => $notificaciones->total(),
                'per_page' => $notificaciones->perPage(),
                'last_page' => $notificaciones->lastPage(),
            ],
        ]);
    }

    /**
     * POST /notificaciones/{id}/leer
     * Marcar una notificación como leída
     */
    public function marcarLeida(Notification $notification): JsonResponse
    {
        $user = auth()->user();

        // Validar que pertenezca al usuario
        if ($notification->user_id !== $user->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        if ($notification->leida) {
            return response()->json([
                'message' => 'La notificación ya estaba leída',
                'data' => $notification,
            ]);
        }

        $notification->update([
            'leida' => true,
            'leida_at' => now(),
        ]);

        return response()->json([
            'message' => 'Notificación marcada como leída',
            'data' => $notification,
        ]);
    }

    /**
     * POST /notificaciones/leer-todas
     * Marcar todas las notificaciones como leídas
     */
    public function marcarTodasLeidas(): JsonResponse
    {
        $user = auth()->user();

        $actualizadas = Notification::where('user_id', $user->id)
            ->where('leida', false)
            ->update([
                'leida' => true,
                'leida_at' => now(),
            ]);

        return response()->json([
            'message' => 'Todas las notificaciones marcadas como leídas',
            'notificaciones_actualizadas' => $actualizadas,
        ]);
    }

    /**
     * GET /notificaciones/no-leidas-count
     * Obtener contador de notificaciones no leídas (para polling)
     */
    public function contadorNoLeidas(): JsonResponse
    {
        $user = auth()->user();

        $count = Notification::where('user_id', $user->id)
            ->where('leida', false)
            ->count();

        // Obtener últimas 3 no leídas para preview
        $ultimas = Notification::where('user_id', $user->id)
            ->where('leida', false)
            ->orderBy('created_at', 'desc')
            ->limit(3)
            ->get()
            ->map(fn($n) => [
                'id' => $n->id,
                'tipo' => $n->tipo,
                'titulo' => $n->titulo,
                'mensaje' => substr($n->mensaje, 0, 100),
                'timestamp' => $n->created_at->toIso8601String(),
            ]);

        return response()->json([
            'message' => 'Contador actualizado',
            'data' => [
                'no_leidas_count' => $count,
                'ultimas_notificaciones' => $ultimas,
            ],
        ]);
    }

    /**
     * DELETE /notificaciones/{id}
     * Eliminar una notificación
     */
    public function eliminar(Notification $notification): JsonResponse
    {
        $user = auth()->user();

        // Validar que pertenezca al usuario
        if ($notification->user_id !== $user->id) {
            return response()->json(['message' => 'No autorizado'], 403);
        }

        $notification->delete();

        return response()->json([
            'message' => 'Notificación eliminada',
        ]);
    }

    /**
     * POST /notificaciones/eliminar-todas
     * Eliminar todas las notificaciones leídas
     */
    public function eliminarTodasLeidas(): JsonResponse
    {
        $user = auth()->user();

        $eliminadas = Notification::where('user_id', $user->id)
            ->where('leida', true)
            ->delete();

        return response()->json([
            'message' => 'Notificaciones leídas eliminadas',
            'notificaciones_eliminadas' => $eliminadas,
        ]);
    }

    /**
     * GET /notificaciones/tipos-disponibles
     * Listar tipos de notificaciones y sus contadores
     */
    public function tiposDisponibles(): JsonResponse
    {
        $user = auth()->user();

        $tipos = [
            'examen_publicado' => '📝 Examen publicado',
            'tarea_publicada' => '📋 Tarea publicada',
            'tarea_por_vencer' => '⏰ Tarea por vencer',
            'examen_calificado' => '📊 Examen calificado',
            'asistencia_ausente' => '❌ Ausencia registrada',
            'entrega_calificada' => '📌 Entrega calificada',
            'anuncio_general' => '📢 Anuncio general',
        ];

        $contadores = [];
        foreach (array_keys($tipos) as $tipo) {
            $contadores[$tipo] = Notification::where('user_id', $user->id)
                ->where('tipo', $tipo)
                ->count();
        }

        return response()->json([
            'message' => 'Tipos de notificaciones disponibles',
            'data' => array_map(fn($tipo, $desc) => [
                'tipo' => $tipo,
                'descripcion' => $desc,
                'cantidad' => $contadores[$tipo] ?? 0,
            ], array_keys($tipos), $tipos),
        ]);
    }
}
