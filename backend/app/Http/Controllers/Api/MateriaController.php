<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Materia;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class MateriaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min($request->get('per_page', 50), 100);
        
        $query = Materia::with('carrera');

        if ($request->filled('buscar')) {
            $query->buscar($request->buscar);
        }

        if ($request->filled('carrera_id')) {
            $query->porCarrera($request->carrera_id);
        }

        if ($request->filled('semestre')) {
            $query->porSemestre($request->semestre);
        }

        if ($request->filled('tipo')) {
            $query->porTipo($request->tipo);
        }

        if (!$request->has('todas')) {
            $query->activo();
        }

        $materias = $query->orderBy('semestre_recomendado')->orderBy('nombre')->paginate($perPage);

        return response()->json($materias);
    }

    public function show(int $id): JsonResponse
    {
        $materia = Materia::with(['carrera', 'prerrequisitos', 'horarios'])->find($id);

        if (!$materia) {
            return response()->json(['message' => 'Materia no encontrada'], 404);
        }

        return response()->json($materia);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'carrera_id' => 'required|exists:carreras,id',
            'codigo' => 'required|string|max:20|unique:materias,codigo',
            'nombre' => 'required|string|max:200',
            'creditos' => 'required|integer|min:1|max:10',
            'semestre_recomendado' => 'required|integer|min:1|max:12',
            'tipo' => 'required|in:obligatoria,electiva,especialidad',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $materia = Materia::create($request->all());
        $materia->load('carrera');

        return response()->json([
            'message' => 'Materia creada exitosamente',
            'data' => $materia
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $materia = Materia::find($id);

        if (!$materia) {
            return response()->json(['message' => 'Materia no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'codigo' => 'sometimes|string|max:20|unique:materias,codigo,' . $id,
            'nombre' => 'sometimes|string|max:200',
            'creditos' => 'sometimes|integer|min:1|max:10',
            'semestre_recomendado' => 'sometimes|integer|min:1|max:12',
            'tipo' => 'sometimes|in:obligatoria,electiva,especialidad',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $materia->update($request->all());
        $materia->load('carrera');

        return response()->json([
            'message' => 'Materia actualizada exitosamente',
            'data' => $materia
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $materia = Materia::find($id);

        if (!$materia) {
            return response()->json(['message' => 'Materia no encontrada'], 404);
        }

        $materia->delete();

        return response()->json(['message' => 'Materia eliminada exitosamente']);
    }
}
