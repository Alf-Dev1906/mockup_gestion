<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Profesor;
use App\Models\Horario;
use App\Models\Calificacion;
use App\Models\Inscripcion;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use App\Services\SecurityLogService;

class ProfesorController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = min($request->get('per_page', 50), 100);
        
        $query = Profesor::with('facultad');

        if ($request->filled('buscar')) {
            $query->buscar($request->buscar);
        }

        if ($request->filled('facultad_id')) {
            $query->porFacultad($request->facultad_id);
        }

        if ($request->filled('categoria')) {
            $query->porCategoria($request->categoria);
        }

        if ($request->filled('especialidad')) {
            $query->porEspecialidad($request->especialidad);
        }

        if (!$request->has('todos')) {
            $query->activo();
        }

        $profesores = $query->orderBy('apellido')->orderBy('nombre')->paginate($perPage);

        return response()->json($profesores);
    }

    public function show(int $id): JsonResponse
    {
        $profesor = Profesor::with(['facultad', 'horarios.materia'])->find($id);

        if (!$profesor) {
            return response()->json(['message' => 'Profesor no encontrado'], 404);
        }

        return response()->json($profesor);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'facultad_id' => 'required|exists:facultades,id',
            'cedula' => 'required|string|max:20|unique:profesores,cedula',
            'nombre' => 'required|string|max:100',
            'apellido' => 'required|string|max:100',
            'email' => 'required|email|max:100|unique:profesores,email',
            'codigo_empleado' => 'required|string|max:20|unique:profesores,codigo_empleado',
            'fecha_contratacion' => 'required|date',
            'tipo_contrato' => 'required|in:tiempo_completo,medio_tiempo,hora_clase,contratado',
            'categoria' => 'required|in:instructor,asistente,agregado,asociado,titular',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $profesor = Profesor::create($request->all());
        $profesor->load('facultad');

        return response()->json([
            'message' => 'Profesor creado exitosamente',
            'data' => $profesor
        ], 201);
    }

    public function update(Request $request, int $id): JsonResponse
    {
        $profesor = Profesor::find($id);

        if (!$profesor) {
            return response()->json(['message' => 'Profesor no encontrado'], 404);
        }

        $validator = Validator::make($request->all(), [
            'cedula' => 'sometimes|string|max:20|unique:profesores,cedula,' . $id,
            'email' => 'sometimes|email|max:100|unique:profesores,email,' . $id,
            'codigo_empleado' => 'sometimes|string|max:20|unique:profesores,codigo_empleado,' . $id,
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Errores de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $profesor->update($request->all());
        $profesor->load('facultad');

        return response()->json([
            'message' => 'Profesor actualizado exitosamente',
            'data' => $profesor
        ]);
    }

    public function destroy(int $id): JsonResponse
    {
        $profesor = Profesor::find($id);

        if (!$profesor) {
            return response()->json(['message' => 'Profesor no encontrado'], 404);
        }

        $profesor->delete();

        return response()->json(['message' => 'Profesor eliminado exitosamente']);
    }

    // ============ MÉTODOS PARA PROFESORES AUTENTICADOS ============

    /**
     * Dashboard del profesor
     */
    public function dashboard(Request $request): JsonResponse
    {
        $user = $request->user();
        $profesor = Profesor::where('email', $user->email)->first();

        if (!$profesor) {
            return response()->json(['error' => 'Profesor no encontrado'], 404);
        }

        $stats = [
            'materias_asignadas' => Horario::where('profesor_id', $profesor->id)
                ->distinct('materia_id')
                ->count('materia_id'),
            
            'estudiantes_total' => \App\Models\Inscripcion::whereHas('horario', function($q) use ($profesor) {
                $q->where('profesor_id', $profesor->id);
            })->count(),
            
            'calificaciones_pendientes' => \App\Models\Inscripcion::whereHas('horario', function($q) use ($profesor) {
                $q->where('profesor_id', $profesor->id);
            })
            ->whereNull('nota_final')
            ->whereIn('estatus', ['inscrito', 'cursando'])
            ->count(),
        ];

        return response()->json($stats);
    }

    /**
     * Listar materias del profesor
     */
    public function misMaterias(Request $request): JsonResponse
    {
        $profesor = Profesor::where('email', $request->user()->email)->first();

        if (!$profesor) {
            return response()->json(['error' => 'Profesor no encontrado'], 404);
        }

        $materias = Horario::with(['materia.carrera', 'aula'])
            ->where('profesor_id', $profesor->id)
            ->get()
            ->map(function($horario) {
                return [
                    'horario_id' => $horario->id,
                    'materia' => [
                        'id'      => $horario->materia->id,
                        'codigo'  => $horario->materia->codigo,
                        'nombre'  => $horario->materia->nombre,
                        'creditos'=> $horario->materia->creditos,
                        'carrera' => $horario->materia->carrera->nombre ?? null,
                    ],
                    'dia_semana'  => $horario->dia_semana,
                    'hora_inicio' => $horario->hora_inicio,
                    'hora_fin'    => $horario->hora_fin,
                    'aula' => $horario->aula ? [
                        'numero'   => $horario->aula->codigo ?? $horario->aula->numero ?? null,
                        'edificio' => $horario->aula->edificio,
                    ] : null,
                ];
            });

        return response()->json($materias);
    }

    /**
     * Estudiantes de una materia del profesor
     */
    public function estudiantesMateria(Request $request, int $materiaId): JsonResponse
    {
        $profesor = Profesor::where('email', $request->user()->email)->first();

        if (!$profesor) {
            return response()->json(['error' => 'Profesor no encontrado'], 404);
        }

        // Verificar que el profesor enseña esta materia
        $enseña = Horario::where('profesor_id', $profesor->id)
            ->where('materia_id', $materiaId)
            ->exists();

        if (!$enseña) {
            return response()->json(['error' => 'No autorizado para ver esta materia'], 403);
        }

        $estudiantes = Inscripcion::with('estudiante')
            ->whereHas('horario', fn($q) => $q->where('materia_id', $materiaId))
            ->get()
            ->map(function($inscripcion) {
                return [
                    'inscripcion_id' => $inscripcion->id,
                    'estudiante' => [
                        'id'        => $inscripcion->estudiante->id,
                        'matricula' => $inscripcion->estudiante->matricula,
                        'nombre'    => $inscripcion->estudiante->nombre,
                        'apellido'  => $inscripcion->estudiante->apellido,
                        'email'     => $inscripcion->estudiante->email,
                    ],
                    'calificacion' => [
                        'id'         => $inscripcion->id,
                        'parcial1'   => $inscripcion->nota_parcial_1,
                        'parcial2'   => $inscripcion->nota_parcial_2,
                        'parcial3'   => $inscripcion->nota_parcial_3,
                        'nota_final' => $inscripcion->nota_final,
                    ],
                ];
            });

        return response()->json($estudiantes);
    }

    /**
     * Crear o actualizar calificación (directamente en inscripciones)
     */
    public function gestionarCalificacion(Request $request): JsonResponse
    {
        $request->validate([
            'inscripcion_id' => 'required|exists:inscripciones,id',
            'parcial1'       => 'nullable|numeric|min:0|max:20',
            'parcial2'       => 'nullable|numeric|min:0|max:20',
            'parcial3'       => 'nullable|numeric|min:0|max:20',
        ]);

        $profesor      = Profesor::where('email', $request->user()->email)->first();
        $inscripcion   = Inscripcion::with('horario')->findOrFail($request->inscripcion_id);

        // Verificar que el profesor enseña esta materia
        $enseña = Horario::where('profesor_id', $profesor->id)
            ->where('id', $inscripcion->horario_id)
            ->exists();

        if (!$enseña) {
            return response()->json(['error' => 'No autorizado para calificar esta inscripción'], 403);
        }

        // Calcular nota final (30% + 30% + 40%)
        $p1 = $request->parcial1 ?? $inscripcion->nota_parcial_1 ?? 0;
        $p2 = $request->parcial2 ?? $inscripcion->nota_parcial_2 ?? 0;
        $p3 = $request->parcial3 ?? $inscripcion->nota_parcial_3 ?? 0;
        $notaFinal = round($p1 * 0.30 + $p2 * 0.30 + $p3 * 0.40, 2);

        $oldData = ['p1' => $inscripcion->nota_parcial_1, 'p2' => $inscripcion->nota_parcial_2, 'p3' => $inscripcion->nota_parcial_3];

        $inscripcion->update([
            'nota_parcial_1' => $request->filled('parcial1') ? $request->parcial1 : $inscripcion->nota_parcial_1,
            'nota_parcial_2' => $request->filled('parcial2') ? $request->parcial2 : $inscripcion->nota_parcial_2,
            'nota_parcial_3' => $request->filled('parcial3') ? $request->parcial3 : $inscripcion->nota_parcial_3,
            'nota_final'     => $notaFinal,
        ]);

        SecurityLogService::logGradeChange(
            $request->user(),
            $inscripcion->id,
            $oldData,
            ['p1' => $inscripcion->nota_parcial_1, 'p2' => $inscripcion->nota_parcial_2, 'p3' => $inscripcion->nota_parcial_3]
        );

        return response()->json([
            'message'      => 'Calificación guardada exitosamente',
            'calificacion' => [
                'id'         => $inscripcion->id,
                'parcial1'   => $inscripcion->nota_parcial_1,
                'parcial2'   => $inscripcion->nota_parcial_2,
                'parcial3'   => $inscripcion->nota_parcial_3,
                'nota_final' => $inscripcion->nota_final,
            ],
        ]);
    }

    /**
     * Horario del profesor
     */
    public function miHorario(Request $request): JsonResponse
    {
        $profesor = Profesor::where('email', $request->user()->email)->first();

        if (!$profesor) {
            return response()->json(['error' => 'Profesor no encontrado'], 404);
        }

        $horarios = Horario::with(['materia', 'aula'])
            ->where('profesor_id', $profesor->id)
            ->orderBy('dia_semana')
            ->orderBy('hora_inicio')
            ->get()
            ->map(function($horario) {
                return [
                    'id' => $horario->id,
                    'dia_semana' => $horario->dia_semana,
                    'hora_inicio' => $horario->hora_inicio,
                    'hora_fin' => $horario->hora_fin,
                    'materia' => [
                        'codigo' => $horario->materia->codigo,
                        'nombre' => $horario->materia->nombre,
                    ],
                    'aula' => $horario->aula ? [
                        'numero' => $horario->aula->numero,
                        'edificio' => $horario->aula->edificio,
                    ] : null,
                ];
            });

        return response()->json($horarios);
    }
}
