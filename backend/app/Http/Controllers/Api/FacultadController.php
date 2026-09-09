<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Facultad;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class FacultadController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = Facultad::query();

        if ($request->filled('buscar')) {
            $query->buscar($request->buscar);
        }

        if (!$request->has('todas')) {
            $query->activo();
        }

        $facultades = $query->orderBy('nombre')->get();

        return response()->json($facultades);
    }

    public function show(int $id): JsonResponse
    {
        $facultad = Facultad::with(['carreras', 'profesores', 'aulas'])->find($id);

        if (!$facultad) {
            return response()->json(['message' => 'Facultad no encontrada'], 404);
        }

        return response()->json($facultad);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'codigo' => 'required|string|max:10|unique:facultades,codigo',
            'nombre' => 'required|string|max:150',
            'decano' => 'nullable|string|max:150',
            'email' => 'nullable|email|max:100',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $facultad = Facultad::create($request->all());

        return response()->json([
            'message' => 'Facultad creada exitosamente',
            'data' => $facultad
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $facultad = Facultad::find($id);

        if (!$facultad) {
            return response()->json(['message' => 'Facultad no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'codigo' => 'sometimes|string|max:10|unique:facultades,codigo,' . $id,
            'nombre' => 'sometimes|string|max:150',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $facultad->update($request->all());

        return response()->json([
            'message' => 'Facultad actualizada exitosamente',
            'data' => $facultad
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $facultad = Facultad::find($id);

        if (!$facultad) {
            return response()->json(['message' => 'Facultad no encontrada'], 404);
        }

        $facultad->delete();

        return response()->json(['message' => 'Facultad eliminada exitosamente']);
    }
}
