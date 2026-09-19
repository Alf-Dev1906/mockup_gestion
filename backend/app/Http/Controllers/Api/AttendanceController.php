<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\AttendanceSession;
use App\Models\Attendance;
use App\Models\Profesor;
use App\Models\Estudiante;
use App\Models\Horario;
use App\Models\Inscripcion;
use App\Services\AttendanceCodeService;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\Auth;
use Illuminate\Database\UniqueConstraintViolationException;

class AttendanceController extends Controller
{
    protected AttendanceCodeService $codeService;

    public function __construct(AttendanceCodeService $codeService)
    {
        $this->codeService = $codeService;
    }
    /**
     * ══════════════════════════════════════════════════════════════
     * ENDPOINTS DEL PROFESOR
     * ══════════════════════════════════════════════════════════════
     */

    /**
     * Abrir una nueva sesión de asistencia
     * POST /api/profesor/asistencia/abrir-sesion
     * 
     * Body: { horario_id, duracion_minutos (opcional, default 10) }
     */
    public function abrirSesion(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'horario_id' => 'required|exists:horarios,id',
            'duracion_minutos' => 'nullable|integer|min:1|max:60',
            'tipo_codigo' => 'nullable|in:alfanumerico,numerico',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $profesor = Profesor::where('email', $user->email)->first();

        $horario = Horario::find($request->horario_id);
        if (!$horario || $horario->profesor_id !== $profesor->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a este horario'
            ], 403);
        }

        // Verificar que no haya otra sesión abierta
        $sessionAbierta = AttendanceSession::where('horario_id', $horario->id)
            ->where('abierta', true)
            ->first();

        if ($sessionAbierta) {
            return response()->json([
                'success' => false,
                'message' => 'Ya hay una sesión abierta para este horario',
                'data' => [
                    'session_id' => $sessionAbierta->id,
                    'codigo_dinamico' => $sessionAbierta->codigo_dinamico,
                    'codigo_formateado' => $this->codeService->formatearParaVisualizacion($sessionAbierta->codigo_dinamico),
                    'tiempo_restante_seg' => $sessionAbierta->tiempoRestanteSeg(),
                ]
            ], 422);
        }

        // Generar código dinámico usando el servicio
        $tipoCodigo = $request->input('tipo_codigo', 'alfanumerico'); // 'alfanumerico' o 'numerico'
        $longitud = $tipoCodigo === 'numerico' ? 4 : 6;
        
        $codigo = $tipoCodigo === 'numerico'
            ? $this->codeService->generarNumerico($horario->id, $longitud)
            : $this->codeService->generarAlfanumerico($horario->id, $longitud);
        
        $duracion = $request->duracion_minutos ?? 10;

        // Crear sesión
        $session = AttendanceSession::create([
            'horario_id' => $horario->id,
            'profesor_id' => $profesor->id,
            'session_date' => now()->format('Y-m-d'),
            'codigo_dinamico' => $codigo,
            'codigo_expira_at' => now()->addMinutes($duracion),
            'abierta' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sesión de asistencia abierta',
            'data' => [
                'session_id' => $session->id,
                'codigo_dinamico' => $session->codigo_dinamico,
                'codigo_formateado' => $this->codeService->formatearParaVisualizacion($session->codigo_dinamico),
                'duracion_minutos' => $duracion,
                'codigo_expira_at' => $session->codigo_expira_at->format('Y-m-d H:i:s'),
                'tipo_codigo' => $tipoCodigo,
            ]
        ]);
    }

    /**
     * Obtener sesión activa de un horario
     * GET /api/profesor/asistencia/sesion-activa/{horario_id}
     */
    public function sesionActiva(int $horarioId): JsonResponse
    {
        $user = Auth::user();
        $profesor = Profesor::where('email', $user->email)->first();

        if (!$profesor) {
            return response()->json([
                'success' => false,
                'message' => 'Profesor no encontrado'
            ], 404);
        }

        $horario = Horario::find($horarioId);
        if (!$horario || $horario->profesor_id !== $profesor->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a este horario'
            ], 403);
        }

        $session = AttendanceSession::where('horario_id', $horarioId)
            ->where('abierta', true)
            ->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'No hay sesión abierta'
            ], 404);
        }

        // Calcular estadísticas de forma segura
        $allAttendances = $session->attendances()->get();
        $presentes = $allAttendances->whereIn('estatus', ['presente', 'justificado'])->count();
        $ausentes = $allAttendances->where('estatus', 'ausente')->count();
        $inscritos = Inscripcion::where('horario_id', $horarioId)->count();

        // Calcular porcentaje
        $totalRegistrados = $presentes + $ausentes;
        $porcentaje = $inscritos > 0 ? round(($presentes / $inscritos) * 100, 2) : 0;

        // Obtener listado con eager loading
        $listado = $session->attendances()
            ->with('estudiante')
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(function($a) {
                return [
                    'id' => $a->id,
                    'estudiante' => $a->estudiante ? ($a->estudiante->nombre . ' ' . $a->estudiante->apellido) : 'N/A',
                    'estatus' => $a->obtenerEstatusLegible(),
                    'registrado_at' => $a->created_at->format('H:i:s'),
                ];
            });

        return response()->json([
            'success' => true,
            'data' => [
                'session' => [
                    'id' => $session->id,
                    'codigo_dinamico' => $session->codigo_dinamico,
                    'codigo_formateado' => $this->codeService->formatearParaVisualizacion($session->codigo_dinamico),
                    'tiempo_restante_seg' => $session->tiempoRestanteSeg(),
                    'es_valido' => $session->esValido(),
                    'abierta_at' => $session->created_at->format('Y-m-d H:i:s'),
                ],
                'estadisticas' => [
                    'presentes' => $presentes,
                    'ausentes' => $ausentes,
                    'no_registrados' => $inscritos - $totalRegistrados,
                    'total_inscritos' => $inscritos,
                    'porcentaje_asistencia' => $porcentaje,
                ],
                'listado' => $listado,
            ]
        ]);
    }

    /**
     * Cerrar sesión de asistencia
     * POST /api/profesor/asistencia/cerrar-sesion/{session_id}
     */
    public function cerrarSesion(int $sessionId): JsonResponse
    {
        $user = Auth::user();
        $profesor = Profesor::where('email', $user->email)->first();

        $session = AttendanceSession::with('horario')->find($sessionId);
        if (!$session || $session->horario->profesor_id !== $profesor->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a esta sesión'
            ], 403);
        }

        if (!$session->abierta) {
            return response()->json([
                'success' => false,
                'message' => 'La sesión ya está cerrada'
            ], 422);
        }

        // Marcar ausentes a todos los no registrados
        $inscritos = Inscripcion::where('horario_id', $session->horario_id)->get();
        $registrados = $session->attendances()->pluck('estudiante_id')->toArray();

        foreach ($inscritos as $inscripcion) {
            if (!in_array($inscripcion->estudiante_id, $registrados)) {
                Attendance::create([
                    'session_id' => $session->id,
                    'estudiante_id' => $inscripcion->estudiante_id,
                    'inscripcion_id' => $inscripcion->id,
                    'estatus' => 'ausente',
                ]);
            }
        }

        // Cerrar sesión
        $session->update([
            'abierta' => false,
        ]);

        // Actualizar totales
        $presentes = $session->attendances()->presentes()->count();
        $ausentes = $session->attendances()->ausentes()->count();
        $session->update([
            'total_presentes' => $presentes,
            'total_ausentes' => $ausentes,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Sesión cerrada correctamente',
            'data' => [
                'total_presentes' => $presentes,
                'total_ausentes' => $ausentes,
            ]
        ]);
    }

    /**
     * Historial de sesiones de un horario
     * GET /api/profesor/asistencia/historial/{horario_id}
     */
    public function historial(int $horarioId): JsonResponse
    {
        $user = Auth::user();
        $profesor = Profesor::where('email', $user->email)->first();

        $horario = Horario::find($horarioId);
        if (!$horario || $horario->profesor_id !== $profesor->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a este horario'
            ], 403);
        }

        $sessions = AttendanceSession::where('horario_id', $horarioId)
            ->orderBy('created_at', 'desc')
            ->get()
            ->map(fn($s) => [
                'id' => $s->id,
                'abierta_at' => $s->created_at->format('Y-m-d H:i:s'),
                'cerrada_at' => $s->abierta ? null : $s->updated_at->format('Y-m-d H:i:s'),
                'total_presentes' => $s->total_presentes,
                'total_ausentes' => $s->attendances()->where('estatus', 'ausente')->count(),
                'porcentaje_asistencia' => $s->porcentajeAsistencia(),
            ]);

        return response()->json([
            'success' => true,
            'data' => $sessions
        ]);
    }

    /**
     * Justificar una asistencia (ÚNICA modificación permitida)
     * PUT /api/profesor/asistencia/{attendance_id}/justificar
     * 
     * Body: { observacion (opcional) }
     */
    public function justificar(Request $request, int $attendanceId): JsonResponse
    {
        $user = Auth::user();
        $profesor = Profesor::where('email', $user->email)->first();

        $attendance = Attendance::with(['session.horario'])->find($attendanceId);
        if (!$attendance || $attendance->session->horario->profesor_id !== $profesor->id) {
            return response()->json([
                'success' => false,
                'message' => 'No tienes acceso a este registro'
            ], 403);
        }

        $validator = Validator::make($request->all(), [
            'observacion' => 'nullable|string|max:500',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        try {
            $attendance->justificar($request->observacion);
            return response()->json([
                'success' => true,
                'message' => 'Asistencia justificada correctamente',
                'data' => [
                    'id' => $attendance->id,
                    'estatus' => $attendance->obtenerEstatusLegible(),
                    'observacion' => $attendance->observacion,
                ]
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 422);
        }
    }

    /**
     * ══════════════════════════════════════════════════════════════
     * ENDPOINTS DEL ESTUDIANTE
     * ══════════════════════════════════════════════════════════════
     */

    /**
     * Marcar asistencia
     * POST /api/estudiante/asistencia/marcar
     * 
     * Body: { horario_id, codigo }
     */
    public function marcar(Request $request): JsonResponse
    {
        $validator = Validator::make($request->all(), [
            'horario_id' => 'required|exists:horarios,id',
            'codigo' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        $estudiante = Estudiante::where('email', $user->email)->first();

        // Limpiar código (remover guiones y otros separadores)
        $codigoLimpio = $this->codeService->limpiarFormato($request->codigo);

        // Obtener sesión activa
        $session = AttendanceSession::where('horario_id', $request->horario_id)
            ->where('abierta', true)
            ->first();

        if (!$session) {
            return response()->json([
                'success' => false,
                'message' => 'No hay sesión de asistencia abierta en este momento'
            ], 404);
        }

        // Verificar que el código es correcto
        if ($session->codigo_dinamico !== $codigoLimpio) {
            return response()->json([
                'success' => false,
                'message' => 'Código incorrecto'
            ], 422);
        }

        // Verificar que el código no haya expirado
        if (!$session->esValido()) {
            return response()->json([
                'success' => false,
                'message' => 'El código ha expirado'
            ], 410);
        }

        // Obtener inscripción
        $inscripcion = Inscripcion::where('horario_id', $request->horario_id)
            ->where('estudiante_id', $estudiante->id)
            ->first();

        if (!$inscripcion) {
            return response()->json([
                'success' => false,
                'message' => 'No estás inscrito en este horario'
            ], 403);
        }

        // Intentar registrar asistencia
        try {
            $attendance = Attendance::create([
                'session_id' => $session->id,
                'estudiante_id' => $estudiante->id,
                'inscripcion_id' => $inscripcion->id,
                'estatus' => 'presente',
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Asistencia registrada correctamente',
                'data' => [
                    'attendance_id' => $attendance->id,
                    'estatus' => $attendance->obtenerEstatusLegible(),
                    'registrado_at' => $attendance->created_at->format('Y-m-d H:i:s'),
                ]
            ], 201);

        } catch (UniqueConstraintViolationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Ya has registrado tu asistencia en esta sesión'
            ], 409);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al registrar asistencia: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Obtener resumen de asistencia del estudiante
     * GET /api/estudiante/asistencia/resumen
     */
    public function resumenEstudiante(): JsonResponse
    {
        $user = Auth::user();
        $estudiante = Estudiante::where('email', $user->email)->first();

        $attendances = Attendance::where('estudiante_id', $estudiante->id)
            ->with(['session.horario.materia', 'inscripcion'])
            ->orderBy('created_at', 'desc')
            ->get();

        $presentes = $attendances->where('estatus', 'presente')->count();
        $ausentes = $attendances->where('estatus', 'ausente')->count();
        $justificados = $attendances->where('estatus', 'justificado')->count();
        $total = $attendances->count();

        $porcentaje = $total === 0 ? 0 : round((($presentes + $justificados) / $total) * 100, 2);

        return response()->json([
            'success' => true,
            'data' => [
                'estadisticas' => [
                    'presentes' => $presentes,
                    'ausentes' => $ausentes,
                    'justificados' => $justificados,
                    'total' => $total,
                    'porcentaje_asistencia' => $porcentaje,
                ],
                'historial' => $attendances->map(fn($a) => [
                    'id' => $a->id,
                    'materia' => $a->session->horario->materia->nombre,
                    'estatus' => $a->obtenerEstatusLegible(),
                    'registrado_at' => $a->created_at->format('Y-m-d H:i'),
                    'observacion' => $a->observacion,
                ]),
            ]
        ]);
    }

    /**
     * ══════════════════════════════════════════════════════════════
     * ENDPOINT DE TESTING/DEBUG (solo desarrollo)
     * ══════════════════════════════════════════════════════════════
     */

    /**
     * Generar códigos de prueba (testing)
     * GET /api/profesor/asistencia/test/codigos
     */
    public function testCodigos(Request $request): JsonResponse
    {
        $tipo = $request->input('tipo', 'alfanumerico');
        $cantidad = (int) $request->input('cantidad', 10);
        $longitud = $tipo === 'numerico' ? 4 : 6;

        $codigos = $this->codeService->generarMultiples(
            min($cantidad, 100), // Máximo 100 códigos
            $tipo,
            $longitud
        );

        $estadisticas = $this->codeService->estadisticasCombinaciones($tipo, $longitud);

        return response()->json([
            'success' => true,
            'data' => [
                'codigos' => array_map(
                    fn($c) => [
                        'raw' => $c,
                        'formatted' => $this->codeService->formatearParaVisualizacion($c)
                    ],
                    $codigos
                ),
                'estadisticas' => $estadisticas,
            ]
        ]);
    }
}
