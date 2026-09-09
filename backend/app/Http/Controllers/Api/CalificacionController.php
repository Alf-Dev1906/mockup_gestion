<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Calificacion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CalificacionController extends Controller
{
    /**
     * Lista de calificaciones con filtros.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min($request->get('per_page', 50), 100);
        
        $query = Calificacion::with(['inscripcion.estudiante', 'inscripcion.horario.materia']);

        // Filtro por estudiante
        if ($request->filled('estudiante_id')) {
            $query->whereHas('inscripcion', function($q) use ($request) {
                $q->where('estudiante_id', $request->estudiante_id);
            });
        }

        // Filtro por estatus
        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }

        // Filtro por nota mínima
        if ($request->filled('nota_minima')) {
            $query->where('nota_final', '>=', $request->nota_minima);
        }

        $calificaciones = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($calificaciones);
    }

    /**
     * Mostrar una calificación específica.
     */
    public function show(int $id): JsonResponse
    {
        $calificacion = Calificacion::with(['inscripcion.estudiante', 'inscripcion.horario.materia'])
            ->find($id);

        if (!$calificacion) {
            return response()->json(['message' => 'Calificación no encontrada'], 404);
        }

        return response()->json($calificacion);
    }

    /**
     * Crear calificación para una inscripción (una sola por inscripción).
     */
    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'inscripcion_id' => 'required|exists:inscripciones,id|unique:calificaciones,inscripcion_id',
            'nota_corte_1' => 'nullable|numeric|min:0|max:20',
            'nota_corte_2' => 'nullable|numeric|min:0|max:20',
            'nota_corte_3' => 'nullable|numeric|min:0|max:20',
            'observaciones' => 'nullable|string',
            'asistencias' => 'nullable|integer|min:0|max:100',
        ]);

        $calificacion = Calificacion::create($validated);
        $calificacion->calcularNotaFinal();
        $calificacion->load(['inscripcion.estudiante', 'inscripcion.horario.materia']);

        return response()->json([
            'message' => 'Calificación creada exitosamente',
            'data' => $calificacion->fresh(),
        ], 201);
    }

    /**
     * Actualizar una calificación.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $calificacion = Calificacion::find($id);

        if (!$calificacion) {
            return response()->json(['message' => 'Calificación no encontrada'], 404);
        }

        $validated = $request->validate([
            'nota_corte_1' => 'nullable|numeric|min:0|max:20',
            'nota_corte_2' => 'nullable|numeric|min:0|max:20',
            'nota_corte_3' => 'nullable|numeric|min:0|max:20',
            'observaciones' => 'nullable|string',
            'asistencias' => 'nullable|integer|min:0|max:100',
        ]);

        $calificacion->update($validated);
        $calificacion->calcularNotaFinal();

        return response()->json($calificacion);
    }

    /**
     * Estadísticas de calificaciones.
     */
    public function estadisticas(): JsonResponse
    {
        $total = Calificacion::count();
        $aprobados = Calificacion::aprobados()->count();
        $reprobados = Calificacion::reprobados()->count();
        $cursando = Calificacion::cursando()->count();
        $promedioGeneral = Calificacion::whereNotNull('nota_final')->avg('nota_final');

        return response()->json([
            'total' => $total,
            'aprobados' => $aprobados,
            'reprobados' => $reprobados,
            'cursando' => $cursando,
            'promedio_general' => round($promedioGeneral, 2),
            'porcentaje_aprobados' => $total > 0 ? round($aprobados / $total * 100, 1) : 0,
            'porcentaje_reprobados' => $total > 0 ? round($reprobados / $total * 100, 1) : 0,
        ]);
    }
}
