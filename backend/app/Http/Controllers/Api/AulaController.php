<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Aula;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class AulaController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min($request->get('per_page', 50), 100);
        
        $query = Aula::with('facultad');

        if ($request->filled('buscar')) {
            $query->buscar($request->buscar);
        }

        if ($request->filled('facultad_id')) {
            $query->porFacultad($request->facultad_id);
        }

        if ($request->filled('edificio')) {
            $query->porEdificio($request->edificio);
        }

        if ($request->filled('tipo')) {
            $query->porTipo($request->tipo);
        }

        if ($request->filled('capacidad_minima')) {
            $query->conCapacidadMinima($request->capacidad_minima);
        }

        if (!$request->has('todas')) {
            $query->disponible();
        }

        $aulas = $query->orderBy('codigo')->paginate($perPage);

        return response()->json($aulas);
    }

    public function show(int $id): JsonResponse
    {
        $aula = Aula::with(['facultad', 'horarios'])->find($id);

        if (!$aula) {
            return response()->json(['message' => 'Aula no encontrada'], 404);
        }

        return response()->json($aula);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'facultad_id' => 'required|exists:facultades,id',
            'codigo' => 'required|string|max:20|unique:aulas,codigo',
            'nombre' => 'required|string|max:100',
            'edificio' => 'required|string|max:50',
            'capacidad' => 'required|integer|min:10|max:500',
            'tipo' => 'required|in:aula_regular,laboratorio,auditorio,salon_conferencias,taller',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $aula = Aula::create($request->all());
        $aula->load('facultad');

        return response()->json([
            'message' => 'Aula creada exitosamente',
            'data' => $aula
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $aula = Aula::find($id);

        if (!$aula) {
            return response()->json(['message' => 'Aula no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'codigo' => 'sometimes|string|max:20|unique:aulas,codigo,' . $id,
            'nombre' => 'sometimes|string|max:100',
            'capacidad' => 'sometimes|integer|min:10|max:500',
            'estatus' => 'sometimes|in:disponible,mantenimiento,fuera_servicio',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $aula->update($request->all());
        $aula->load('facultad');

        return response()->json([
            'message' => 'Aula actualizada exitosamente',
            'data' => $aula
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $aula = Aula::find($id);

        if (!$aula) {
            return response()->json(['message' => 'Aula no encontrada'], 404);
        }

        $aula->delete();

        return response()->json(['message' => 'Aula eliminada exitosamente']);
    }
}
