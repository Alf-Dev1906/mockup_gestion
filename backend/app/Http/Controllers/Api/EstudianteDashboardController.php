<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Estudiante;
use App\Models\Horario;
use App\Models\Inscripcion;
use App\Models\Calificacion;
use Illuminate\Support\Facades\DB;

class EstudianteDashboardController extends Controller
{
    /**
     * Dashboard del estudiante
     */
    public function dashboard(Request $request)
    {
        try {
            $user = $request->user();
            $estudiante = Estudiante::with('carrera.facultad')
                ->where('email', $user->email)
                ->first();
            
            if (!$estudiante) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estudiante no encontrado'
                ], 404);
            }

            // Estadísticas básicas
            $totalInscripciones = Inscripcion::where('estudiante_id', $estudiante->id)
                ->whereIn('estatus', ['inscrito', 'cursando'])
                ->count();
            
            $materiasAprobadas = Inscripcion::whereHas('calificacion', function ($q) {
                $q->where('estatus', 'aprobado');
            })->where('estudiante_id', $estudiante->id)->count();

            $promedioGeneral = Inscripcion::where('estudiante_id', $estudiante->id)
                ->whereHas('calificacion')
                ->join('calificaciones', 'inscripciones.id', '=', 'calificaciones.inscripcion_id')
                ->avg('calificaciones.nota_final');

            return response()->json([
                'success' => true,
                'data' => [
                    'estudiante' => [
                        'id' => $estudiante->id,
                        'nombre_completo' => $estudiante->nombre . ' ' . $estudiante->apellido,
                        'matricula' => $estudiante->matricula,
                        'email' => $estudiante->email,
                        'semestre_actual' => $estudiante->semestre_actual,
                        'carrera' => $estudiante->carrera->nombre ?? 'N/A',
                        'facultad' => $estudiante->carrera->facultad->nombre ?? 'N/A',
                        'estatus' => $estudiante->estatus,
                    ],
                    'estadisticas' => [
                        'materias_inscritas' => $totalInscripciones,
                        'materias_aprobadas' => $materiasAprobadas,
                        'promedio_general' => round($promedioGeneral ?? 0, 2),
                        'creditos_aprobados' => $estudiante->creditos_aprobados,
                        'indice_academico' => $estudiante->indice_academico,
                        'porcentaje_avance' => $estudiante->carrera 
                            ? round(($estudiante->creditos_aprobados / $estudiante->carrera->creditos_totales) * 100, 2)
                            : 0,
                    ]
                ]
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
     * Obtener horario del estudiante
     */
    public function miHorario(Request $request)
    {
        try {
            $user = $request->user();
            $estudiante = Estudiante::where('email', $user->email)->first();
            
            if (!$estudiante) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estudiante no encontrado'
                ], 404);
            }

            // Obtener inscripciones activas
            $inscripciones = Inscripcion::with(['horario.materia', 'horario.profesor', 'horario.aula'])
                ->where('estudiante_id', $estudiante->id)
                ->whereIn('estatus', ['inscrito', 'cursando'])
                ->get();

            // Organizar por día de la semana
            $diasSemana = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes', 'sábado'];
            $horarioOrganizado = [];

            foreach ($diasSemana as $dia) {
                $horarioOrganizado[$dia] = $inscripciones->filter(function ($ins) use ($dia) {
                    return $ins->horario->dia_semana === $dia;
                })->map(function ($ins) {
                    $h = $ins->horario;
                    return [
                        'id' => $h->id,
                        'materia' => $h->materia->nombre,
                        'codigo_materia' => $h->materia->codigo,
                        'creditos' => $h->materia->creditos,
                        'profesor' => $h->profesor->nombre . ' ' . $h->profesor->apellido,
                        'hora_inicio' => substr($h->hora_inicio, 0, 5),
                        'hora_fin' => substr($h->hora_fin, 0, 5),
                        'aula' => $h->aula->nombre ?? 'N/A',
                        'edificio' => $h->aula->edificio ?? 'N/A',
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
     * Obtener calificaciones del estudiante
     */
    public function misCalificaciones(Request $request)
    {
        try {
            $user = $request->user();
            $estudiante = Estudiante::where('email', $user->email)->first();
            
            if (!$estudiante) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estudiante no encontrado'
                ], 404);
            }

            // Obtener todas las inscripciones con calificaciones
            $inscripciones = Inscripcion::with([
                'horario.materia',
                'horario.profesor',
                'calificacion'
            ])
                ->where('estudiante_id', $estudiante->id)
                ->get()
                ->map(function ($inscripcion) {
                    $calificacion = $inscripcion->calificacion;
                    $materia = $inscripcion->horario->materia;
                    $profesor = $inscripcion->horario->profesor;
                    
                    return [
                        'inscripcion_id' => $inscripcion->id,
                        'periodo' => $inscripcion->periodo_academico,
                        'materia' => $materia->nombre ?? 'N/A',
                        'codigo' => $materia->codigo ?? 'N/A',
                        'creditos' => $materia->creditos ?? 0,
                        'profesor' => ($profesor->nombre ?? '') . ' ' . ($profesor->apellido ?? ''),
                        'seccion' => $inscripcion->horario->seccion ?? 'N/A',
                        'estatus_inscripcion' => $inscripcion->estatus,
                        'nota_corte_1' => $calificacion->nota_corte_1 ?? null,
                        'nota_corte_2' => $calificacion->nota_corte_2 ?? null,
                        'nota_corte_3' => $calificacion->nota_corte_3 ?? null,
                        'nota_final' => $calificacion->nota_final ?? null,
                        'asistencias' => $calificacion->asistencias ?? 0,
                        'estatus' => $calificacion->estatus ?? 'cursando',
                        'observaciones' => $calificacion->observaciones ?? '',
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
     * Obtener perfil del estudiante
     */
    public function miPerfil(Request $request)
    {
        try {
            $user = $request->user();
            $estudiante = Estudiante::with('carrera.facultad')
                ->where('email', $user->email)
                ->first();
            
            if (!$estudiante) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estudiante no encontrado'
                ], 404);
            }

            // Calcular estadísticas
            $totalInscripciones = Inscripcion::where('estudiante_id', $estudiante->id)->count();
            $materiasAprobadas = Inscripcion::whereHas('calificacion', function ($q) {
                $q->where('estatus', 'aprobado');
            })->where('estudiante_id', $estudiante->id)->count();

            $data = [
                'id' => $estudiante->id,
                'cedula' => $estudiante->cedula,
                'nombre' => $estudiante->nombre,
                'apellido' => $estudiante->apellido,
                'nombre_completo' => $estudiante->nombre . ' ' . $estudiante->apellido,
                'email' => $estudiante->email,
                'telefono' => $estudiante->telefono,
                'fecha_nacimiento' => $estudiante->fecha_nacimiento,
                'genero' => $estudiante->genero,
                'direccion' => $estudiante->direccion,
                'ciudad' => $estudiante->ciudad,
                'estado' => $estudiante->estado,
                'matricula' => $estudiante->matricula,
                'fecha_ingreso' => $estudiante->fecha_ingreso,
                'semestre_actual' => $estudiante->semestre_actual,
                'indice_academico' => $estudiante->indice_academico,
                'creditos_aprobados' => $estudiante->creditos_aprobados,
                'estatus' => $estudiante->estatus,
                'carrera' => [
                    'id' => $estudiante->carrera->id ?? null,
                    'nombre' => $estudiante->carrera->nombre ?? 'N/A',
                    'codigo' => $estudiante->carrera->codigo ?? 'N/A',
                    'duracion_semestres' => $estudiante->carrera->duracion_semestres ?? 0,
                    'creditos_totales' => $estudiante->carrera->creditos_totales ?? 0,
                ],
                'facultad' => [
                    'nombre' => $estudiante->carrera->facultad->nombre ?? 'N/A',
                    'codigo' => $estudiante->carrera->facultad->codigo ?? 'N/A',
                ],
                'contacto_emergencia' => [
                    'nombre' => $estudiante->contacto_emergencia_nombre,
                    'telefono' => $estudiante->contacto_emergencia_telefono,
                    'relacion' => $estudiante->contacto_emergencia_relacion,
                ],
                'estadisticas' => [
                    'total_inscripciones' => $totalInscripciones,
                    'materias_aprobadas' => $materiasAprobadas,
                    'porcentaje_avance' => $estudiante->carrera 
                        ? round(($estudiante->creditos_aprobados / $estudiante->carrera->creditos_totales) * 100, 2)
                        : 0,
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
     * Actualizar perfil del estudiante
     */
    public function actualizarPerfil(Request $request)
    {
        try {
            $user = $request->user();
            $estudiante = Estudiante::where('email', $user->email)->first();
            
            if (!$estudiante) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estudiante no encontrado'
                ], 404);
            }

            $request->validate([
                'telefono' => 'nullable|string|max:20',
                'direccion' => 'nullable|string',
                'ciudad' => 'nullable|string|max:100',
                'estado' => 'nullable|string|max:100',
                'contacto_emergencia_nombre' => 'nullable|string|max:150',
                'contacto_emergencia_telefono' => 'nullable|string|max:20',
                'contacto_emergencia_relacion' => 'nullable|string|max:50',
            ]);

            $estudiante->update($request->only([
                'telefono',
                'direccion',
                'ciudad',
                'estado',
                'contacto_emergencia_nombre',
                'contacto_emergencia_telefono',
                'contacto_emergencia_relacion',
            ]));

            return response()->json([
                'success' => true,
                'message' => 'Perfil actualizado exitosamente',
                'data' => $estudiante
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al actualizar perfil',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener estado de solicitud de admisión (para solicitantes)
     */
    public function obtenerEstadoSolicitud(Request $request)
    {
        try {
            $user = $request->user();
            $estudiante = Estudiante::where('email', $user->email)->first();
            
            if (!$estudiante) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estudiante no encontrado'
                ], 404);
            }

            // Buscar solicitud de admisión del estudiante
            $solicitud = \App\Models\SolicitudAdmision::with(['carrera', 'carreraAlternativa'])
                ->where('estudiante_id', $estudiante->id)
                ->orderBy('created_at', 'desc')
                ->first();

            if (!$solicitud) {
                // Si no tiene solicitud, crear datos básicos a partir del estudiante
                $data = [
                    'numero_solicitud' => 'SOL-' . str_pad($estudiante->id, 6, '0', STR_PAD_LEFT),
                    'estatus' => $estudiante->estatus === 'inactivo' ? 'pendiente' : 'aprobado',
                    'created_at' => $estudiante->created_at,
                    'fecha_revision' => $estudiante->updated_at,
                    'revisado_por_nombre' => null,
                    'observaciones' => null,
                    'datos_personales' => [
                        'nombre' => $estudiante->nombre,
                        'apellido' => $estudiante->apellido,
                        'cedula' => $estudiante->cedula,
                        'fecha_nacimiento' => $estudiante->fecha_nacimiento,
                        'genero' => $estudiante->genero,
                    ],
                    'datos_contacto' => [
                        'email' => $estudiante->email,
                        'telefono' => $estudiante->telefono,
                        'direccion' => $estudiante->direccion,
                        'ciudad' => $estudiante->ciudad,
                        'estado' => $estudiante->estado,
                    ],
                    'datos_academicos' => [
                        'carrera' => $estudiante->carrera->nombre ?? 'N/A',
                    ],
                ];
            } else {
                // Datos completos de la solicitud
                $data = [
                    'numero_solicitud' => $solicitud->numero_referencia,
                    'estatus' => $solicitud->estado,
                    'paso_actual' => $solicitud->paso_actual,
                    'created_at' => $solicitud->created_at,
                    'fecha_envio' => $solicitud->fecha_envio,
                    'fecha_revision' => $solicitud->fecha_revision,
                    'revisado_por_nombre' => $solicitud->revisado_por 
                        ? \App\Models\User::find($solicitud->revisado_por)?->name 
                        : null,
                    'observaciones' => $solicitud->comentarios_admin,
                    'razon_rechazo' => $solicitud->razon_rechazo,
                    'datos_personales' => [
                        'nombre' => $estudiante->nombre,
                        'apellido' => $estudiante->apellido,
                        'cedula' => $estudiante->cedula,
                        'fecha_nacimiento' => $solicitud->fecha_nacimiento,
                        'genero' => $solicitud->genero,
                    ],
                    'datos_contacto' => [
                        'email' => $estudiante->email,
                        'telefono' => $solicitud->telefono,
                        'telefono_alternativo' => $solicitud->telefono_alternativo,
                        'direccion' => $solicitud->direccion,
                        'ciudad' => $solicitud->ciudad,
                        'estado' => $solicitud->estado_provincia,
                        'codigo_postal' => $solicitud->codigo_postal,
                    ],
                    'contacto_emergencia' => [
                        'nombre' => $solicitud->contacto_emergencia_nombre,
                        'telefono' => $solicitud->contacto_emergencia_telefono,
                        'relacion' => $solicitud->contacto_emergencia_relacion,
                    ],
                    'datos_academicos' => [
                        'carrera' => $solicitud->carrera->nombre ?? 'N/A',
                        'carrera_alternativa' => $solicitud->carreraAlternativa->nombre ?? null,
                        'modalidad' => $solicitud->modalidad,
                        'turno_preferido' => $solicitud->turno_preferido,
                        'motivacion' => $solicitud->motivacion,
                        'nivel_educativo' => $solicitud->nivel_educativo,
                        'institucion_egreso' => $solicitud->institucion_egreso,
                        'promedio_notas' => $solicitud->promedio_notas,
                        'tipo_bachillerato' => $solicitud->tipo_bachillerato,
                        'mencion' => $solicitud->mencion,
                    ],
                    'documentos' => [
                        'cedula' => $solicitud->doc_cedula ? true : false,
                        'titulo_bachiller' => $solicitud->doc_titulo_bachiller ? true : false,
                        'notas_certificadas' => $solicitud->doc_notas_certificadas ? true : false,
                        'foto_carnet' => $solicitud->doc_foto_carnet ? true : false,
                        'comprobante_domicilio' => $solicitud->doc_comprobante_domicilio ? true : false,
                    ],
                    'pasos' => [
                        'paso1_completado' => $solicitud->paso1_completado,
                        'paso2_completado' => $solicitud->paso2_completado,
                        'paso3_completado' => $solicitud->paso3_completado,
                        'paso4_completado' => $solicitud->paso4_completado,
                        'paso5_completado' => $solicitud->paso5_completado,
                    ],
                ];
            }

            return response()->json([
                'success' => true,
                'data' => $data
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener solicitud',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener inscripciones del estudiante
     */
    public function misInscripciones(Request $request)
    {
        try {
            $user = $request->user();
            $estudiante = Estudiante::where('email', $user->email)->first();
            
            if (!$estudiante) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estudiante no encontrado'
                ], 404);
            }

            $inscripciones = Inscripcion::with(['horario.materia', 'horario.profesor'])
                ->where('estudiante_id', $estudiante->id)
                ->whereIn('estatus', ['inscrito', 'cursando'])
                ->get()
                ->map(function ($ins) {
                    return [
                        'id' => $ins->id,
                        'periodo_academico' => $ins->periodo_academico,
                        'fecha_inscripcion' => $ins->fecha_inscripcion,
                        'estatus' => $ins->estatus,
                        'materia' => [
                            'id' => $ins->horario->materia->id,
                            'codigo' => $ins->horario->materia->codigo,
                            'nombre' => $ins->horario->materia->nombre,
                            'creditos' => $ins->horario->materia->creditos,
                            'semestre' => $ins->horario->materia->semestre ?? 'N/A',
                        ],
                        'horario' => [
                            'id' => $ins->horario->id,
                            'seccion' => $ins->horario->seccion,
                            'dia_semana' => $ins->horario->dia_semana,
                            'hora_inicio' => substr($ins->horario->hora_inicio, 0, 5),
                            'hora_fin' => substr($ins->horario->hora_fin, 0, 5),
                        ],
                        'profesor' => $ins->horario->profesor 
                            ? $ins->horario->profesor->nombre . ' ' . $ins->horario->profesor->apellido
                            : 'N/A',
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $inscripciones
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener inscripciones',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener horarios disponibles para inscripción
     */
    public function horariosDisponibles(Request $request)
    {
        try {
            $user = $request->user();
            $estudiante = Estudiante::where('email', $user->email)->first();
            
            if (!$estudiante) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estudiante no encontrado'
                ], 404);
            }

            // Obtener IDs de materias ya inscritas
            $materiasInscritasIds = Inscripcion::where('estudiante_id', $estudiante->id)
                ->where('estatus', 'inscrito')
                ->join('horarios', 'inscripciones.horario_id', '=', 'horarios.id')
                ->pluck('horarios.materia_id');

            // Obtener horarios disponibles de la misma carrera
            $horarios = Horario::with(['materia', 'profesor', 'aula'])
                ->whereHas('materia', function ($q) use ($estudiante) {
                    $q->where('carrera_id', $estudiante->carrera_id);
                })
                ->whereNotIn('materia_id', $materiasInscritasIds)
                ->where('estatus', 'abierto')
                ->whereRaw('cupo_actual < cupo_maximo')
                ->get()
                ->map(function ($h) {
                    return [
                        'id' => $h->id,
                        'materia' => $h->materia->nombre,
                        'codigo' => $h->materia->codigo,
                        'creditos' => $h->materia->creditos,
                        'profesor' => $h->profesor->nombre . ' ' . $h->profesor->apellido,
                        'dia_semana' => $h->dia_semana,
                        'hora_inicio' => substr($h->hora_inicio, 0, 5),
                        'hora_fin' => substr($h->hora_fin, 0, 5),
                        'aula' => $h->aula->nombre ?? 'N/A',
                        'seccion' => $h->seccion,
                        'cupos_disponibles' => $h->cupo_maximo - $h->cupo_actual,
                        'cupo_maximo' => $h->cupo_maximo,
                    ];
                });

            return response()->json([
                'success' => true,
                'data' => $horarios
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener horarios disponibles',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Inscribir estudiante en un horario
     */
    public function inscribir(Request $request)
    {
        try {
            $request->validate([
                'horario_id' => 'required|exists:horarios,id'
            ]);

            $user = $request->user();
            $estudiante = Estudiante::where('email', $user->email)->first();
            
            if (!$estudiante) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estudiante no encontrado'
                ], 404);
            }

            $horario = Horario::with('materia')->find($request->horario_id);

            // Verificar si ya está inscrito en esta materia
            $yaInscrito = Inscripcion::where('estudiante_id', $estudiante->id)
                ->whereHas('horario', function ($q) use ($horario) {
                    $q->where('materia_id', $horario->materia_id);
                })
                ->where('estatus', 'inscrito')
                ->exists();

            if ($yaInscrito) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ya estás inscrito en esta materia'
                ], 400);
            }

            // Verificar cupos disponibles
            if ($horario->cupo_actual >= $horario->cupo_maximo) {
                return response()->json([
                    'success' => false,
                    'message' => 'No hay cupos disponibles en este horario'
                ], 400);
            }

            // Crear inscripción
            DB::beginTransaction();

            $inscripcion = Inscripcion::create([
                'estudiante_id' => $estudiante->id,
                'horario_id' => $horario->id,
                'periodo_academico' => $horario->periodo_academico,
                'fecha_inscripcion' => now(),
                'estatus' => 'inscrito',
            ]);

            // Incrementar cupo actual
            $horario->increment('cupo_actual');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Inscripción realizada exitosamente',
                'data' => $inscripcion
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al inscribir',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Retirar inscripción de un horario
     */
    public function retirarInscripcion(Request $request, $inscripcionId)
    {
        try {
            $user = $request->user();
            $estudiante = Estudiante::where('email', $user->email)->first();
            
            if (!$estudiante) {
                return response()->json([
                    'success' => false,
                    'message' => 'Estudiante no encontrado'
                ], 404);
            }

            $inscripcion = Inscripcion::where('id', $inscripcionId)
                ->where('estudiante_id', $estudiante->id)
                ->first();

            if (!$inscripcion) {
                return response()->json([
                    'success' => false,
                    'message' => 'Inscripción no encontrada'
                ], 404);
            }

            DB::beginTransaction();

            // Actualizar inscripción
            $inscripcion->update([
                'estatus' => 'retirado',
                'fecha_retiro' => now(),
            ]);

            // Decrementar cupo
            $inscripcion->horario->decrement('cupo_actual');

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Inscripción retirada exitosamente'
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Error al retirar inscripción',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
