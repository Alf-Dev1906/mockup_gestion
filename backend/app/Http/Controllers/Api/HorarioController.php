<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Horario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class HorarioController extends Controller
{
    /**
     * Lista paginada de horarios con filtros.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min($request->get('per_page', 50), 100);
        
        $query = Horario::with(['materia', 'profesor', 'aula']);

        // Filtro de búsqueda
        if ($request->filled('buscar')) {
            $query->buscar($request->buscar);
        }

        // Filtro por periodo académico
        if ($request->filled('periodo_academico')) {
            $query->porPeriodo($request->periodo_academico);
        } else {
            // Por defecto: periodo actual 2026-1
            $query->porPeriodo('2026-1');
        }

        // Filtro por materia
        if ($request->filled('materia_id')) {
            $query->porMateria($request->materia_id);
        }

        // Filtro por profesor
        if ($request->filled('profesor_id')) {
            $query->porProfesor($request->profesor_id);
        }

        // Filtro por día
        if ($request->filled('dia_semana')) {
            $query->porDia($request->dia_semana);
        }

        // Filtro por estatus
        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }

        // Filtro: solo con cupos disponibles
        if ($request->boolean('con_cupos')) {
            $query->conCuposDisponibles();
        }

        $horarios = $query->orderBy('dia_semana')->orderBy('hora_inicio')->paginate($perPage);

        return response()->json($horarios);
    }

    /**
     * Mostrar un horario específico.
     */
    public function show(int $id): JsonResponse
    {
        $horario = Horario::with([
            'materia.carrera',
            'profesor',
            'aula',
            'inscripciones.estudiante'
        ])->find($id);

        if (!$horario) {
            return response()->json([
                'message' => 'Horario no encontrado'
            ], 404);
        }

        // Agregar información calculada
        $horario->cupos_disponibles = $horario->cupos_disponibles;
        $horario->porcentaje_ocupacion = $horario->porcentaje_ocupacion;

        return response()->json($horario);
    }

    /**
     * Crear un nuevo horario.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'materia_id' => 'required|exists:materias,id',
            'profesor_id' => 'required|exists:profesores,id',
            'aula_id' => 'required|exists:aulas,id',
            'seccion' => 'required|string|max:5',
            'periodo_academico' => 'required|string|max:10',
            'anno' => 'required|integer|min:2020|max:2030',
            'periodo' => 'required|integer|min:1|max:3',
            'dia_semana' => 'required|in:lunes,martes,miércoles,jueves,viernes,sábado',
            'hora_inicio' => 'required|date_format:H:i',
            'hora_fin' => 'required|date_format:H:i|after:hora_inicio',
            'cupo_maximo' => 'required|integer|min:10|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $horario = Horario::create($request->all());

        // Verificar conflictos
        if ($horario->tieneConflicto()) {
            $horario->delete();
            return response()->json([
                'message' => 'Conflicto de horario detectado',
                'error' => 'El aula o el profesor ya tienen una clase asignada en ese horario'
            ], 409);
        }

        $horario->load(['materia', 'profesor', 'aula']);

        return response()->json([
            'message' => 'Horario creado exitosamente',
            'data' => $horario
        ], 201);
    }

    /**
     * Actualizar un horario.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $horario = Horario::find($id);

        if (!$horario) {
            return response()->json([
                'message' => 'Horario no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'materia_id' => 'sometimes|exists:materias,id',
            'profesor_id' => 'sometimes|exists:profesores,id',
            'aula_id' => 'sometimes|exists:aulas,id',
            'seccion' => 'sometimes|string|max:5',
            'dia_semana' => 'sometimes|in:lunes,martes,miércoles,jueves,viernes,sábado',
            'hora_inicio' => 'sometimes|date_format:H:i',
            'hora_fin' => 'sometimes|date_format:H:i',
            'cupo_maximo' => 'sometimes|integer|min:10|max:100',
            'estatus' => 'sometimes|in:abierto,cerrado,en_curso,finalizado,cancelado',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $horario->update($request->all());
        $horario->load(['materia', 'profesor', 'aula']);

        return response()->json([
            'message' => 'Horario actualizado exitosamente',
            'data' => $horario
        ]);
    }

    /**
     * Eliminar un horario.
     */
    public function destroy(int $id): JsonResponse
    {
        $horario = Horario::find($id);

        if (!$horario) {
            return response()->json([
                'message' => 'Horario no encontrado'
            ], 404);
        }

        // Verificar si tiene inscripciones
        if ($horario->inscripciones()->count() > 0) {
            return response()->json([
                'message' => 'No se puede eliminar',
                'error' => 'El horario tiene estudiantes inscritos'
            ], 409);
        }

        $horario->delete();

        return response()->json([
            'message' => 'Horario eliminado exitosamente'
        ]);
    }

    /**
     * Obtener estudiantes inscritos en un horario.
     */
    public function estudiantes(Request $request, int $id): JsonResponse
    {
        $horario = Horario::find($id);

        if (!$horario) {
            return response()->json([
                'message' => 'Horario no encontrado'
            ], 404);
        }

        $query = $horario->inscripciones()->with('estudiante');

        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }

        $inscripciones = $query->get();

        return response()->json($inscripciones);
    }

    /**
     * Obtener horarios disponibles para inscripción.
     */
    public function disponibles(Request $request): JsonResponse
    {
        $query = Horario::with(['materia', 'profesor', 'aula'])
            ->abierto()
            ->conCuposDisponibles();

        if ($request->filled('periodo_academico')) {
            $query->porPeriodo($request->periodo_academico);
        } else {
            $query->porPeriodo('2026-1');
        }

        if ($request->filled('carrera_id')) {
            $query->whereHas('materia', function ($q) use ($request) {
                $q->where('carrera_id', $request->carrera_id);
            });
        }

        $horarios = $query->get();

        return response()->json($horarios);
    }
}
