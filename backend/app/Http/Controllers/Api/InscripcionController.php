<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Inscripcion;
use App\Models\Horario;
use App\Models\Estudiante;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;

class InscripcionController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min($request->get('per_page', 50), 100);
        
        $query = Inscripcion::with(['estudiante', 'horario.materia', 'horario.profesor']);

        if ($request->filled('buscar')) {
            $termino = $request->buscar;
            $query->whereHas('estudiante', function ($q) use ($termino) {
                $q->buscar($termino);
            });
        }

        if ($request->filled('estudiante_id')) {
            $query->porEstudiante($request->estudiante_id);
        }

        if ($request->filled('horario_id')) {
            $query->porHorario($request->horario_id);
        }

        if ($request->filled('periodo_academico')) {
            $query->porPeriodo($request->periodo_academico);
        }

        if ($request->filled('estatus')) {
            $query->where('estatus', $request->estatus);
        }

        $inscripciones = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json($inscripciones);
    }

    public function show(int $id): JsonResponse
    {
        $inscripcion = Inscripcion::with([
            'estudiante.carrera',
            'horario.materia',
            'horario.profesor',
            'horario.aula'
        ])->find($id);

        if (!$inscripcion) {
            return response()->json(['message' => 'Inscripción no encontrada'], 404);
        }

        return response()->json($inscripcion);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'estudiante_id' => 'required|exists:estudiantes,id',
            'horario_id' => 'required|exists:horarios,id',
            'periodo_academico' => 'required|string|max:10',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Verificar cupos disponibles
        $horario = Horario::find($request->horario_id);
        if ($horario->esta_lleno) {
            return response()->json([
                'message' => 'No hay cupos disponibles',
                'error' => 'El horario seleccionado está lleno'
            ], 409);
        }

        // Verificar inscripción duplicada
        $existe = Inscripcion::where('estudiante_id', $request->estudiante_id)
            ->where('horario_id', $request->horario_id)
            ->where('periodo_academico', $request->periodo_academico)
            ->exists();

        if ($existe) {
            return response()->json([
                'message' => 'Inscripción duplicada',
                'error' => 'El estudiante ya está inscrito en este horario'
            ], 409);
        }

        $inscripcion = Inscripcion::create([
            'estudiante_id' => $request->estudiante_id,
            'horario_id' => $request->horario_id,
            'periodo_academico' => $request->periodo_academico,
            'fecha_inscripcion' => now(),
            'estatus' => 'inscrito',
        ]);

        $inscripcion->load(['estudiante', 'horario.materia']);

        return response()->json([
            'message' => 'Inscripción creada exitosamente',
            'data' => $inscripcion
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $inscripcion = Inscripcion::find($id);

        if (!$inscripcion) {
            return response()->json(['message' => 'Inscripción no encontrada'], 404);
        }

        $validator = Validator::make($request->all(), [
            'estatus' => 'sometimes|in:inscrito,retirado,aprobado,reprobado,cursando,lista_espera',
            'nota_parcial_1' => 'nullable|numeric|min:0|max:20',
            'nota_parcial_2' => 'nullable|numeric|min:0|max:20',
            'nota_parcial_3' => 'nullable|numeric|min:0|max:20',
            'nota_final' => 'nullable|numeric|min:0|max:20',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $inscripcion->update($request->all());
        $inscripcion->load(['estudiante', 'horario.materia']);

        return response()->json([
            'message' => 'Inscripción actualizada exitosamente',
            'data' => $inscripcion
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $inscripcion = Inscripcion::find($id);

        if (!$inscripcion) {
            return response()->json(['message' => 'Inscripción no encontrada'], 404);
        }

        $inscripcion->delete();

        return response()->json(['message' => 'Inscripción eliminada exitosamente']);
    }

    public function retirar(int $id): JsonResponse
    {
        $inscripcion = Inscripcion::find($id);

        if (!$inscripcion) {
            return response()->json(['message' => 'Inscripción no encontrada'], 404);
        }

        if ($inscripcion->estatus === 'retirado') {
            return response()->json([
                'message' => 'La inscripción ya está retirada'
            ], 400);
        }

        $inscripcion->retirar();

        return response()->json([
            'message' => 'Materia retirada exitosamente',
            'data' => $inscripcion
        ]);
    }
}
