<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Profesor;
use App\Models\Horario;
use App\Models\Inscripcion;
use App\Models\Calificacion;
use Illuminate\Support\Facades\DB;

class ProfesorDashboardController extends Controller
{
    /**
     * Dashboard del profesor con estadísticas generales
     */
    public function dashboard(Request $request)
    {
        try {
            $user = $request->user();
            $profesor = Profesor::where('email', $user->email)->first();
            
            if (!$profesor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profesor no encontrado'
                ], 404);
            }

            // Obtener IDs de horarios del profesor
            $horariosIds = Horario::where('profesor_id', $profesor->id)
                ->where('estatus', '!=', 'cancelado')
                ->pluck('id');

            // Calcular estadísticas
            $stats = [
                'nombre_completo' => $profesor->nombre . ' ' . $profesor->apellido,
                'materias_asignadas' => Horario::where('profesor_id', $profesor->id)
                    ->where('estatus', '!=', 'cancelado')
                    ->distinct('materia_id')
                    ->count('materia_id'),
                
                'estudiantes_total' => Inscripcion::whereIn('horario_id', $horariosIds)
                    ->where('estatus', 'inscrito')
                    ->distinct('estudiante_id')
                    ->count('estudiante_id'),
                
                'calificaciones_pendientes' => Inscripcion::whereIn('horario_id', $horariosIds)
                    ->where('estatus', 'inscrito')
                    ->whereDoesntHave('calificacion', function($q) {
                        $q->whereNotNull('nota_final');
                    })
                    ->count(),
                
                'horarios_esta_semana' => Horario::where('profesor_id', $profesor->id)
                    ->where('estatus', '!=', 'cancelado')
                    ->count(),
            ];

            return response()->json([
                'success' => true,
                'data' => $stats
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener dashboard',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener materias del profesor autenticado
     */
    public function misMaterias(Request $request)
    {
        try {
            $user = $request->user();
            
            // Buscar profesor por email
            $profesor = Profesor::where('email', $user->email)->first();
            
            if (!$profesor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profesor no encontrado'
                ], 404);
            }

            // Obtener horarios con sus materias
            $horarios = Horario::with(['materia.carrera', 'aula'])
                ->where('profesor_id', $profesor->id)
                ->where('estatus', '!=', 'cancelado')
                ->get();

            // Agrupar por materia
            $materias = $horarios->groupBy('materia_id')->map(function ($horariosPorMateria) {
                $primerHorario = $horariosPorMateria->first();
                $materia = $primerHorario->materia;
                
                return [
                    'id' => $materia->id,
                    'codigo' => $materia->codigo,
                    'nombre' => $materia->nombre,
                    'creditos' => $materia->creditos,
                    'carrera' => $materia->carrera->nombre ?? 'N/A',
                    'horarios' => $horariosPorMateria->map(function ($h) {
                        return [
                            'id' => $h->id,
                            'dia_semana' => $h->dia_semana,
                            'hora_inicio' => $h->hora_inicio,
                            'hora_fin' => $h->hora_fin,
                            'aula' => $h->aula->nombre ?? 'N/A',
                            'seccion' => $h->seccion,
                            'cupo_actual' => $h->cupo_actual,
                            'cupo_maximo' => $h->cupo_maximo,
                        ];
                    }),
                    'total_estudiantes' => $horariosPorMateria->sum('cupo_actual'),
                ];
            })->values();

            return response()->json([
                'success' => true,
                'data' => $materias
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener materias',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener horario del profesor
     */
    public function miHorario(Request $request)
    {
        try {
            $user = $request->user();
            $profesor = Profesor::where('email', $user->email)->first();
            
            if (!$profesor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profesor no encontrado'
                ], 404);
            }

            // Obtener todos los horarios del profesor
            $horarios = Horario::with(['materia', 'aula'])
                ->where('profesor_id', $profesor->id)
                ->where('estatus', '!=', 'cancelado')
                ->orderBy('dia_semana')
                ->orderBy('hora_inicio')
                ->get();

            // Organizar por día de la semana
            $diasSemana = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
            $horarioOrganizado = [];

            foreach ($diasSemana as $dia) {
                $horarioOrganizado[$dia] = $horarios->filter(function ($h) use ($dia) {
                    return $h->dia_semana === $dia;
                })->map(function ($h) {
                    return [
                        'id' => $h->id,
                        'materia' => $h->materia->nombre,
                        'codigo_materia' => $h->materia->codigo,
                        'hora_inicio' => substr($h->hora_inicio, 0, 5),
                        'hora_fin' => substr($h->hora_fin, 0, 5),
                        'aula' => $h->aula->nombre ?? 'N/A',
                        'seccion' => $h->seccion,
                    ];
                })->values();
            }

            return response()->json([
                'success' => true,
                'data' => $horarioOrganizado
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener horario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estudiantes del profesor
     */
    public function misEstudiantes(Request $request)
    {
        try {
            $user = $request->user();
            $profesor = Profesor::where('email', $user->email)->first();
            
            if (!$profesor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profesor no encontrado'
                ], 404);
            }

            // Obtener horarios del profesor
            $horariosIds = Horario::where('profesor_id', $profesor->id)
                ->where('estatus', '!=', 'cancelado')
                ->pluck('id');

            // Obtener inscripciones de esos horarios
            $estudiantes = Inscripcion::with(['estudiante', 'horario.materia'])
                ->whereIn('horario_id', $horariosIds)
                ->where('estatus', 'inscrito')
                ->get()
                ->groupBy('estudiante_id')
                ->map(function ($inscripciones) {
                    $estudiante = $inscripciones->first()->estudiante;
                    return [
                        'id' => $estudiante->id,
                        'cedula' => $estudiante->cedula,
                        'nombre_completo' => $estudiante->nombre . ' ' . $estudiante->apellido,
                        'email' => $estudiante->email,
                        'matricula' => $estudiante->matricula,
                        'semestre_actual' => $estudiante->semestre_actual,
                        'indice_academico' => $estudiante->indice_academico,
                        'materias_inscritas' => $inscripciones->count(),
                        'materias' => $inscripciones->map(function ($ins) {
                            return [
                                'nombre' => $ins->horario->materia->nombre ?? 'N/A',
                                'seccion' => $ins->horario->seccion ?? 'N/A',
                            ];
                        })
                    ];
                })->values();

            return response()->json([
                'success' => true,
                'data' => $estudiantes
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estudiantes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estudiantes de una materia del profesor
     */
    public function estudiantesMateria(Request $request, $materiaId)
    {
        try {
            $user = $request->user();
            $profesor = Profesor::where('email', $user->email)->first();
            
            if (!$profesor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profesor no encontrado'
                ], 404);
            }

            // Verificar que el profesor enseña esta materia
            $enseña = Horario::where('profesor_id', $profesor->id)
                ->where('materia_id', $materiaId)
                ->exists();

            if (!$enseña) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tienes permiso para ver estudiantes de esta materia'
                ], 403);
            }

            // Obtener horarios del profesor para esta materia
            $horariosIds = Horario::where('profesor_id', $profesor->id)
                ->where('materia_id', $materiaId)
                ->pluck('id');

            // Obtener inscripciones de esos horarios
            $estudiantes = Inscripcion::with(['estudiante', 'calificacion'])
                ->whereIn('horario_id', $horariosIds)
                ->where('estatus', 'inscrito')
                ->get()
                ->map(function ($inscripcion) {
                    $calificacion = $inscripcion->calificacion;
                    
                    return [
                        'inscripcion_id' => $inscripcion->id,
                        'estudiante' => [
                            'id' => $inscripcion->estudiante->id,
                            'nombre' => $inscripcion->estudiante->nombre,
                            'apellido' => $inscripcion->estudiante->apellido,
                            'email' => $inscripcion->estudiante->email,
                            'matricula' => $inscripcion->estudiante->matricula,
                        ],
                        'calificacion' => [
                            'id' => $calificacion->id ?? null,
                            'nota_final' => $calificacion->nota_final ?? null,
                        ]
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $estudiantes
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener estudiantes',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener calificaciones para gestionar
     */
    public function calificaciones(Request $request)
    {
        try {
            $user = $request->user();
            $profesor = Profesor::where('email', $user->email)->first();
            
            if (!$profesor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profesor no encontrado'
                ], 404);
            }

            $materiaId = $request->query('materia_id');
            $horarioId = $request->query('horario_id');

            // Obtener horarios del profesor
            $horariosQuery = Horario::where('profesor_id', $profesor->id)
                ->where('estatus', '!=', 'cancelado');

            if ($materiaId) {
                $horariosQuery->where('materia_id', $materiaId);
            }

            if ($horarioId) {
                $horariosQuery->where('id', $horarioId);
            }

            $horariosIds = $horariosQuery->pluck('id');

            // Obtener inscripciones con calificaciones
            $inscripciones = Inscripcion::with([
                'estudiante',
                'horario.materia',
                'calificacion'
            ])
                ->whereIn('horario_id', $horariosIds)
                ->where('estatus', 'inscrito')
                ->get()
                ->map(function ($inscripcion) {
                    $calificacion = $inscripcion->calificacion;
                    
                    return [
                        'inscripcion_id' => $inscripcion->id,
                        'estudiante_id' => $inscripcion->estudiante->id,
                        'estudiante' => $inscripcion->estudiante->nombre . ' ' . $inscripcion->estudiante->apellido,
                        'cedula' => $inscripcion->estudiante->cedula,
                        'matricula' => $inscripcion->estudiante->matricula,
                        'materia' => $inscripcion->horario->materia->nombre ?? 'N/A',
                        'seccion' => $inscripcion->horario->seccion ?? 'N/A',
                        'calificacion_id' => $calificacion->id ?? null,
                        'nota_corte_1' => $calificacion->nota_corte_1 ?? null,
                        'nota_corte_2' => $calificacion->nota_corte_2 ?? null,
                        'nota_corte_3' => $calificacion->nota_corte_3 ?? null,
                        'nota_final' => $calificacion->nota_final ?? null,
                        'asistencias' => $calificacion->asistencias ?? 0,
                        'estatus' => $calificacion->estatus ?? 'cursando',
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $inscripciones
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener calificaciones',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener perfil del profesor
     */
    public function miPerfil(Request $request)
    {
        try {
            $user = $request->user();
            $profesor = Profesor::with('facultad')->where('email', $user->email)->first();
            
            if (!$profesor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profesor no encontrado'
                ], 404);
            }

            // Contar materias y estudiantes
            $materiasIds = Horario::where('profesor_id', $profesor->id)
                ->where('estatus', '!=', 'cancelado')
                ->distinct('materia_id')
                ->pluck('materia_id');

            $totalMaterias = $materiasIds->count();
            
            $horariosIds = Horario::where('profesor_id', $profesor->id)
                ->where('estatus', '!=', 'cancelado')
                ->pluck('id');
            
            $totalEstudiantes = Inscripcion::whereIn('horario_id', $horariosIds)
                ->where('estatus', 'inscrito')
                ->distinct('estudiante_id')
                ->count('estudiante_id');

            $data = [
                'id' => $profesor->id,
                'cedula' => $profesor->cedula,
                'nombre' => $profesor->nombre,
                'apellido' => $profesor->apellido,
                'nombre_completo' => $profesor->nombre . ' ' . $profesor->apellido,
                'email' => $profesor->email,
                'telefono' => $profesor->telefono,
                'direccion' => $profesor->direccion,
                'fecha_nacimiento' => $profesor->fecha_nacimiento,
                'genero' => $profesor->genero,
                'codigo_empleado' => $profesor->codigo_empleado,
                'fecha_contratacion' => $profesor->fecha_contratacion,
                'tipo_contrato' => $profesor->tipo_contrato,
                'categoria' => $profesor->categoria,
                'estatus' => $profesor->estatus,
                'facultad' => [
                    'id' => $profesor->facultad->id ?? null,
                    'nombre' => $profesor->facultad->nombre ?? 'N/A',
                    'codigo' => $profesor->facultad->codigo ?? 'N/A',
                ],
                'estadisticas' => [
                    'materias_asignadas' => $totalMaterias,
                    'estudiantes_total' => $totalEstudiantes,
                ],
            ];

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener perfil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Registrar o actualizar calificación
     */
    public function guardarCalificacion(Request $request)
    {
        try {
            $request->validate([
                'inscripcion_id' => 'required|exists:inscripciones,id',
                'nota_corte_1' => 'nullable|numeric|min:0|max:20',
                'nota_corte_2' => 'nullable|numeric|min:0|max:20',
                'nota_corte_3' => 'nullable|numeric|min:0|max:20',
                'asistencias' => 'nullable|integer|min:0|max:100',
                'observaciones' => 'nullable|string',
            ]);

            // Verificar que el profesor puede calificar esta inscripción
            $user = $request->user();
            $profesor = Profesor::where('email', $user->email)->first();
            
            if (!$profesor) {
                return response()->json([
                    'success' => false,
                    'message' => 'Profesor no encontrado'
                ], 404);
            }

            $inscripcion = Inscripcion::with('horario')->find($request->inscripcion_id);
            
            if ($inscripcion->horario->profesor_id !== $profesor->id) {
                return response()->json([
                    'success' => false,
                    'message' => 'No tiene permiso para calificar este estudiante'
                ], 403);
            }

            // Calcular nota final
            $notas = array_filter([
                $request->nota_corte_1,
                $request->nota_corte_2,
                $request->nota_corte_3
            ]);
            
            $notaFinal = count($notas) > 0 ? array_sum($notas) / count($notas) : null;
            
            // Determinar estatus
            $estatus = 'cursando';
            if ($notaFinal !== null) {
                $estatus = $notaFinal >= 10 ? 'aprobado' : 'reprobado';
            }

            // Crear o actualizar calificación
            $calificacion = Calificacion::updateOrCreate(
                ['inscripcion_id' => $request->inscripcion_id],
                [
                    'nota_corte_1' => $request->nota_corte_1,
                    'nota_corte_2' => $request->nota_corte_2,
                    'nota_corte_3' => $request->nota_corte_3,
                    'nota_final' => $notaFinal,
                    'asistencias' => $request->asistencias ?? 0,
                    'estatus' => $estatus,
                    'observaciones' => $request->observaciones,
                ]
            );

            return response()->json([
                'success' => true,
                'message' => 'Calificación guardada exitosamente',
                'data' => $calificacion
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al guardar calificación',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
