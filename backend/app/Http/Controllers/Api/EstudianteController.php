<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Estudiante;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class EstudianteController extends Controller
{
    /**
     * Lista paginada de estudiantes con filtros y búsqueda.
     * 
     * Query params:
     * - page: número de página
     * - per_page: registros por página (default: 50, max: 100)
     * - buscar: término de búsqueda (nombre, apellido, cédula, matrícula)
     * - carrera_id: filtrar por carrera
     * - semestre: filtrar por semestre actual
     * - estatus: filtrar por estatus (activo, inactivo, egresado, etc.)
     * - sort: campo para ordenar (default: apellido)
     * - direction: dirección de orden (asc, desc)
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min($request->get('per_page', 50), 100);
        
        $query = Estudiante::with(['carrera.facultad']);

        // Filtro de búsqueda
        if ($request->filled('buscar')) {
            $query->buscar($request->buscar);
        }

        // Filtro por carrera
        if ($request->filled('carrera_id')) {
            $query->porCarrera($request->carrera_id);
        }

        // Filtro por semestre
        if ($request->filled('semestre')) {
            $query->porSemestre($request->semestre);
        }

        // Filtro por estatus
        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }
        // TEMPORAL: Comentado para mostrar todos los estudiantes
        // else {
        //     // Por defecto, solo activos
        //     $query->activo();
        // }

        // Ordenamiento
        $sortField = $request->get('sort', 'apellido');
        $direction = $request->get('direction', 'asc');
        
        if (in_array($sortField, ['apellido', 'nombre', 'matricula', 'semestre_actual', 'indice_academico', 'created_at'])) {
            $query->orderBy($sortField, $direction);
        }

        $estudiantes = $query->paginate($perPage);

        return response()->json($estudiantes);
    }

    /**
     * Mostrar un estudiante específico con todas sus relaciones.
     */
    public function show(int $id): JsonResponse
    {
        $estudiante = Estudiante::with([
            'carrera.facultad',
            'inscripciones.horario.materia',
            'inscripciones.horario.profesor',
        ])->find($id);

        if (!$estudiante) {
            return response()->json([
                'message' => 'Estudiante no encontrado'
            ], 404);
        }

        return response()->json($estudiante);
    }

    /**
     * Crear un nuevo estudiante.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'carrera_id' => 'required|exists:carreras,id',
            'cedula' => 'required|string|max:20|unique:estudiantes,cedula',
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:estudiantes,email',
            'telefono' => 'nullable|string|max:20',
            'fecha_nacimiento' => 'required|date',
            'genero' => 'nullable|in:M,F,Otro',
            'direccion' => 'nullable|string',
            'ciudad' => 'nullable|string|max:100',
            'estado' => 'nullable|string|max:100',
            'matricula' => 'required|string|max:20|unique:estudiantes,matricula',
            'fecha_ingreso' => 'required|date',
            'semestre_actual' => 'required|integer|min:1|max:12',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $estudiante = Estudiante::create($request->all());
        $estudiante->load('carrera.facultad');

        return response()->json([
            'message' => 'Estudiante creado exitosamente',
            'data' => $estudiante
        ], 201);
    }

    /**
     * Actualizar un estudiante existente.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $estudiante = Estudiante::find($id);

        if (!$estudiante) {
            return response()->json([
                'message' => 'Estudiante no encontrado'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'carrera_id' => 'sometimes|exists:carreras,id',
            'cedula' => 'sometimes|string|max:20|unique:estudiantes,cedula,' . $id,
            'nombre' => 'sometimes|string|max:100',
            'apellido' => 'sometimes|string|max:100',
            'email' => 'sometimes|email|max:100|unique:estudiantes,email,' . $id,
            'telefono' => 'nullable|string|max:20',
            'semestre_actual' => 'sometimes|integer|min:1|max:12',
            'estatus' => 'sometimes|in:activo,inactivo,egresado,retirado,suspendido',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $estudiante->update($request->all());
        $estudiante->load('carrera.facultad');

        return response()->json([
            'message' => 'Estudiante actualizado exitosamente',
            'data' => $estudiante
        ]);
    }

    /**
     * Eliminar un estudiante (soft delete).
     */
    public function destroy(int $id): JsonResponse
    {
        $estudiante = Estudiante::find($id);

        if (!$estudiante) {
            return response()->json([
                'message' => 'Estudiante no encontrado'
            ], 404);
        }

        $estudiante->delete();

        return response()->json([
            'message' => 'Estudiante eliminado exitosamente'
        ]);
    }

    /**
     * Obtener inscripciones del estudiante en un periodo.
     */
    public function inscripciones(Request $request, int $id): JsonResponse
    {
        $estudiante = Estudiante::find($id);

        if (!$estudiante) {
            return response()->json([
                'message' => 'Estudiante no encontrado'
            ], 404);
        }

        $query = $estudiante->inscripciones()
            ->with(['horario.materia', 'horario.profesor', 'horario.aula']);

        if ($request->filled('periodo_academico')) {
            $query->porPeriodo($request->periodo_academico);
        }

        $inscripciones = $query->get();

        return response()->json($inscripciones);
    }

    /**
     * Obtener estadísticas del estudiante.
     */
    public function estadisticas(int $id): JsonResponse
    {
        $estudiante = Estudiante::with('inscripciones')->find($id);

        if (!$estudiante) {
            return response()->json([
                'message' => 'Estudiante no encontrado'
            ], 404);
        }

        $stats = [
            'indice_academico' => $estudiante->indice_academico,
            'creditos_aprobados' => $estudiante->creditos_aprobados,
            'semestre_actual' => $estudiante->semestre_actual,
            'total_materias_inscritas' => $estudiante->inscripciones->count(),
            'materias_aprobadas' => $estudiante->inscripciones()->aprobado()->count(),
            'materias_reprobadas' => $estudiante->inscripciones()->reprobado()->count(),
            'materias_cursando' => $estudiante->inscripciones()->cursando()->count(),
        ];

        return response()->json($stats);
    }
}
