<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Carrera;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class CarreraController extends Controller
{
    /**
     * Lista paginada de carreras con filtros.
     */
    public function index(Request $request): JsonResponse
    {
        $perPage = min($request->get('per_page', 50), 100);
        
        $query = Carrera::with('facultad');

        // Filtro de búsqueda
        if ($request->filled('buscar')) {
            $query->buscar($request->buscar);
        }

        // Filtro por facultad
        if ($request->filled('facultad_id')) {
            $query->porFacultad($request->facultad_id);
        }

        // Filtro por modalidad
        if ($request->filled('modalidad')) {
            $query->where('modalidad', $request->modalidad);
        }

        // Filtro por nivel (TSU, Licenciatura, Ingeniería)
        if ($request->filled('nivel')) {
            $query->where('nivel', $request->nivel);
        }

        // Solo activas por defecto
        // TEMPORAL: Comentado para mostrar todas las carreras
        // if (!$request->has('todas')) {
        //     $query->activo();
        // }

        $carreras = $query->orderBy('nivel')->orderBy('nombre')->paginate($perPage);

        return response()->json($carreras);
    }

    /**
     * Mostrar una carrera específica.
     */
    public function show(int $id): JsonResponse
    {
        $carrera = Carrera::with(['facultad', 'materias'])->find($id);

        if (!$carrera) {
            return response()->json([
                'message' => 'Carrera no encontrada'
            ], 404);
        }

        return response()->json($carrera);
    }

    /**
     * Crear una nueva carrera.
     */
    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'facultad_id' => 'required|exists:facultades,id',
            'codigo' => 'required|string|max:20|unique:carreras,codigo',
            'nombre' => 'required|string|max:200',
            'titulo_otorgado' => 'required|string|max:200',
            'duracion_semestres' => 'required|integer|min:6|max:14',
            'creditos_totales' => 'required|integer|min:100|max:300',
            'modalidad' => 'required|in:presencial,semipresencial,distancia',
            'nivel' => 'nullable|in:tsu,licenciatura,ingenieria',
            'carrera_base_id' => 'nullable|exists:carreras,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $carrera = Carrera::create($request->all());
        $carrera->load('facultad');

        return response()->json([
            'message' => 'Carrera creada exitosamente',
            'data' => $carrera
        ], 201);
    }

    /**
     * Actualizar una carrera.
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $carrera = Carrera::find($id);

        if (!$carrera) {
            return response()->json([
                'message' => 'Carrera no encontrada'
            ], 404);
        }

        $validator = Validator::make($request->all(), [
            'facultad_id' => 'sometimes|exists:facultades,id',
            'codigo' => 'sometimes|string|max:20|unique:carreras,codigo,' . $id,
            'nombre' => 'sometimes|string|max:200',
            'titulo_otorgado' => 'sometimes|string|max:200',
            'duracion_semestres' => 'sometimes|integer|min:6|max:14',
            'modalidad' => 'sometimes|in:presencial,semipresencial,distancia',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $carrera->update($request->all());
        $carrera->load('facultad');

        return response()->json([
            'message' => 'Carrera actualizada exitosamente',
            'data' => $carrera
        ]);
    }

    /**
     * Eliminar una carrera.
     */
    public function destroy(int $id): JsonResponse
    {
        $carrera = Carrera::find($id);

        if (!$carrera) {
            return response()->json([
                'message' => 'Carrera no encontrada'
            ], 404);
        }

        $carrera->delete();

        return response()->json([
            'message' => 'Carrera eliminada exitosamente'
        ]);
    }

    /**
     * Obtener estudiantes de una carrera.
     */
    public function estudiantes(Request $request, int $id): JsonResponse
    {
        $carrera = Carrera::find($id);

        if (!$carrera) {
            return response()->json([
                'message' => 'Carrera no encontrada'
            ], 404);
        }

        $query = $carrera->estudiantes();

        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        } else {
            $query->activo();
        }

        $perPage = min($request->get('per_page', 50), 100);
        $estudiantes = $query->paginate($perPage);

        return response()->json($estudiantes);
    }

    /**
     * Obtener materias de una carrera.
     */
    public function materias(Request $request, int $id): JsonResponse
    {
        $carrera = Carrera::find($id);

        if (!$carrera) {
            return response()->json([
                'message' => 'Carrera no encontrada'
            ], 404);
        }

        $query = $carrera->materias();

        if ($request->filled('semestre')) {
            $query->porSemestre($request->semestre);
        }

        if ($request->filled('tipo')) {
            $query->porTipo($request->tipo);
        }

        $materias = $query->activo()->orderBy('semestre_recomendado')->get();

        return response()->json($materias);
    }
}
