<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use App\Services\SecurityLogService;

class AdministrativoController extends Controller
{
    /**
     * Dashboard administrativo
     */
    public function dashboard(): JsonResponse
    {
        // El middleware ya verificÃ³ el rol, no necesitamos Gate aquÃ­
        // Gate::authorize('ver-reportes-academicos');

        $stats = [
            'solicitudes_pendientes' => DB::table('solicitudes_admision')
                ->where('estado', 'pendiente')
                ->count(),
            
            'inscripciones_periodo_actual' => DB::table('inscripciones')
                ->where('periodo_academico', now()->year . '-' . (now()->month <= 6 ? '1' : '2'))
                ->count(),
            
            'pagos_pendientes' => DB::table('pagos')
                ->where('estatus', 'pendiente')
                ->count(),
            
            'estudiantes_por_estatus' => DB::table('estudiantes')
                ->select('estatus', DB::raw('count(*) as count'))
                ->groupBy('estatus')
                ->get(),
            
            'inscripciones_recientes' => DB::table('inscripciones')
                ->join('estudiantes', 'inscripciones.estudiante_id', '=', 'estudiantes.id')
                ->join('horarios',   'inscripciones.horario_id', '=', 'horarios.id')
                ->join('materias',   'horarios.materia_id', '=', 'materias.id')
                ->select(
                    'inscripciones.id',
                    'inscripciones.created_at',
                    'estudiantes.nombre as estudiante_nombre',
                    'estudiantes.apellido as estudiante_apellido',
                    'materias.nombre as materia_nombre'
                )
                ->orderBy('inscripciones.created_at', 'desc')
                ->limit(10)
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Listar solicitudes de admisiÃ³n
     */
    public function solicitudes(Request $request): JsonResponse
    {
        // El middleware ya verificÃ³ el rol
        // Gate::authorize('aprobar-solicitud-admision');

        $perPage = $request->query('per_page', 20);
        $estado = $request->query('estatus'); // Frontend usa 'estatus'

        $query = DB::table('solicitudes_admision')
            ->leftJoin('estudiantes', 'solicitudes_admision.estudiante_id', '=', 'estudiantes.id')
            ->leftJoin('users', 'estudiantes.user_id', '=', 'users.id')
            ->leftJoin('users as revisor', 'solicitudes_admision.revisado_por', '=', 'revisor.id')
            
            ->select(
                'solicitudes_admision.*',
                DB::raw("CONCAT(estudiantes.nombre, ' ', estudiantes.apellido) as estudiante_nombre"),
                'estudiantes.cedula as estudiante_cedula',
                'users.email as estudiante_email',
                
                'revisor.name as revisado_por_nombre'
            )
             // Solo las enviadas
            ->orderBy('solicitudes_admision.created_at', 'desc');

        if ($estado) {
            $query->where('solicitudes_admision.estado', $estado);
        }

        $solicitudes = $query->paginate($perPage);

        return response()->json($solicitudes);
    }

    /**
     * Ver detalle de solicitud
     */
    public function showSolicitud(int $id): JsonResponse
    {
        Gate::authorize('aprobar-solicitud-admision');

        $solicitud = DB::table('solicitudes_admision')
            ->leftJoin('estudiantes', 'solicitudes_admision.estudiante_id', '=', 'estudiantes.id')
            ->leftJoin('users', 'estudiantes.user_id', '=', 'users.id')
            ->leftJoin('carreras as c1', 'solicitudes_admision.carrera_id', '=', 'c1.id')
            ->leftJoin('carreras as c2', 'solicitudes_admision.carrera_alternativa_id', '=', 'c2.id')
            ->leftJoin('users as revisor', 'solicitudes_admision.revisado_por', '=', 'revisor.id')
            ->select(
                'solicitudes_admision.*',
                'estudiantes.nombre as est_nombre',
                'estudiantes.apellido as est_apellido',
                'estudiantes.cedula as est_cedula',
                'users.email as est_email',
                'c1.nombre as carrera_nombre',
                'c2.nombre as carrera_alternativa_nombre',
                'revisor.name as revisado_por_nombre'
            )
            ->where('solicitudes_admision.id', $id)
            ->first();

        if (!$solicitud) {
            return response()->json(['error' => 'Solicitud no encontrada'], 404);
        }

        // Restructurar para el frontend
        $response = [
            'id' => $solicitud->id,
            'numero_referencia' => $solicitud->numero_referencia,
            'estado' => $solicitud->estado,
            'fecha_envio' => $solicitud->fecha_envio,
            'fecha_revision' => $solicitud->fecha_revision,
            'created_at' => $solicitud->created_at,
            
            // Paso 1: Personal
            'fecha_nacimiento' => $solicitud->fecha_nacimiento,
            'genero' => $solicitud->genero,
            'telefono' => $solicitud->telefono,
            'telefono_alternativo' => $solicitud->telefono_alternativo,
            'direccion' => $solicitud->direccion,
            'ciudad' => $solicitud->ciudad,
            'estado_residencia' => $solicitud->estado,
            'codigo_postal' => $solicitud->codigo_postal,
            'contacto_emergencia_nombre' => $solicitud->contacto_emergencia_nombre,
            'contacto_emergencia_telefono' => $solicitud->contacto_emergencia_telefono,
            'contacto_emergencia_relacion' => $solicitud->contacto_emergencia_relacion,
            
            // Paso 2: Carrera
            'modalidad' => $solicitud->modalidad,
            'turno_preferido' => $solicitud->turno_preferido,
            'motivacion' => $solicitud->motivacion,
            
            // Paso 3: AcadÃ©mico
            'nivel_educativo' => $solicitud->nivel_educativo,
            'institucion_egreso' => $solicitud->institucion_egreso,
            'aÃ±o_graduacion' => $solicitud->aÃ±o_graduacion,
            'promedio_notas' => $solicitud->promedio_notas,
            'tipo_bachillerato' => $solicitud->tipo_bachillerato,
            'mencion' => $solicitud->mencion,
            'universidad_procedencia' => $solicitud->universidad_procedencia,
            'carrera_previa' => $solicitud->carrera_previa,
            'semestres_completados' => $solicitud->semestres_completados,
            'desea_convalidar' => $solicitud->desea_convalidar,
            
            // Paso 4: Documentos
            'doc_cedula' => $solicitud->doc_cedula,
            'doc_partida_nacimiento' => $solicitud->doc_partida_nacimiento,
            'doc_titulo_bachiller' => $solicitud->doc_titulo_bachiller,
            'doc_foto_carnet' => $solicitud->doc_foto_carnet,
            'doc_comprobante_domicilio' => $solicitud->doc_comprobante_domicilio,
            'doc_certificado_medico' => $solicitud->doc_certificado_medico,
            'doc_carta_conducta' => $solicitud->doc_carta_conducta,
            
            // RevisiÃ³n
            'comentarios_admin' => $solicitud->comentarios_admin,
            'razon_rechazo' => $solicitud->razon_rechazo,
            'revisado_por_nombre' => $solicitud->revisado_por_nombre,
            
            // Relaciones
            'estudiante' => [
                'nombre' => $solicitud->est_nombre,
                'apellido' => $solicitud->est_apellido,
                'cedula' => $solicitud->est_cedula,
                'user' => [
                    'email' => $solicitud->est_email
                ]
            ],
            'carrera' => [
                'nombre' => $solicitud->carrera_nombre
            ],
            'carrera_alternativa' => $solicitud->carrera_alternativa_nombre ? [
                'nombre' => $solicitud->carrera_alternativa_nombre
            ] : null,
        ];

        return response()->json($response);
    }

    /**
     * Aprobar solicitud de admisiÃ³n
     */
    public function aprobarSolicitud(Request $request, int $id): JsonResponse
    {
        Gate::authorize('aprobar-solicitud-admision');

        $request->validate([
            'comentarios' => 'nullable|string|max:500',
        ]);

        $solicitud = DB::table('solicitudes_admision')->where('id', $id)->first();

        if (!$solicitud) {
            return response()->json(['error' => 'Solicitud no encontrada'], 404);
        }

        if (!in_array($solicitud->estado, ['pendiente', 'en_revision'])) {
            return response()->json(['error' => 'La solicitud ya fue procesada'], 400);
        }

        DB::beginTransaction();
        try {
            // Actualizar solicitud
            DB::table('solicitudes_admision')
                ->where('id', $id)
                ->update([
                    'estado' => 'aprobada',
                    'revisado_por' => $request->user()->id,
                    'fecha_revision' => now(),
                    'comentarios_admin' => $request->comentarios,
                    'updated_at' => now(),
                ]);

            // Cambiar estatus del estudiante a 'activo' y asignar carrera
            if ($solicitud->estudiante_id) {
                $updates = [
                    'estatus' => 'activo',
                    'updated_at' => now(),
                ];
                
                // Asignar carrera si fue seleccionada
                if ($solicitud->carrera_id) {
                    $updates['carrera_id'] = $solicitud->carrera_id;
                }
                
                DB::table('estudiantes')
                    ->where('id', $solicitud->estudiante_id)
                    ->update($updates);
                    
                // Generar matrÃ­cula definitiva (reemplazar TEMP-)
                $estudiante = DB::table('estudiantes')->where('id', $solicitud->estudiante_id)->first();
                if ($estudiante && str_starts_with($estudiante->matricula, 'TEMP-')) {
                    $aÃ±o = date('Y');
                    $nuevaMatricula = $aÃ±o . '-' . str_pad($solicitud->estudiante_id, 6, '0', STR_PAD_LEFT);
                    DB::table('estudiantes')
                        ->where('id', $solicitud->estudiante_id)
                        ->update(['matricula' => $nuevaMatricula]);
                }
            }

            DB::commit();

            SecurityLogService::logAdmissionApproval($request->user(), $id, true);

            return response()->json([
                'message' => 'Solicitud aprobada exitosamente. El estudiante ahora tiene acceso completo al sistema.',
            ]);
        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al aprobar solicitud: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Rechazar solicitud de admisiÃ³n
     */
    public function rechazarSolicitud(Request $request, int $id): JsonResponse
    {
        Gate::authorize('aprobar-solicitud-admision');

        $request->validate([
            'razon_rechazo' => 'required|string|max:1000',
        ]);

        $solicitud = DB::table('solicitudes_admision')->where('id', $id)->first();

        if (!$solicitud) {
            return response()->json(['error' => 'Solicitud no encontrada'], 404);
        }

        if (!in_array($solicitud->estado, ['pendiente', 'en_revision'])) {
            return response()->json(['error' => 'La solicitud ya fue procesada'], 400);
        }

        DB::table('solicitudes_admision')
            ->where('id', $id)
            ->update([
                'estado' => 'rechazada',
                'revisado_por' => $request->user()->id,
                'fecha_revision' => now(),
                'razon_rechazo' => $request->razon_rechazo,
                'updated_at' => now(),
            ]);

        SecurityLogService::logAdmissionApproval($request->user(), $id, false);

        return response()->json([
            'message' => 'Solicitud rechazada',
        ]);
    }

    /**
     * Solicitar correcciÃ³n a la solicitud
     */
    public function solicitarCorreccion(Request $request, int $id): JsonResponse
    {
        Gate::authorize('aprobar-solicitud-admision');

        $request->validate([
            'comentarios' => 'required|string|max:1000',
        ]);

        $solicitud = DB::table('solicitudes_admision')->where('id', $id)->first();

        if (!$solicitud) {
            return response()->json(['error' => 'Solicitud no encontrada'], 404);
        }

        if (!in_array($solicitud->estado, ['pendiente', 'en_revision'])) {
            return response()->json(['error' => 'La solicitud ya fue procesada'], 400);
        }

        DB::table('solicitudes_admision')
            ->where('id', $id)
            ->update([
                'estado' => 'requiere_correccion',
                'revisado_por' => $request->user()->id,
                'fecha_revision' => now(),
                'comentarios_admin' => $request->comentarios,
                'updated_at' => now(),
            ]);

        return response()->json([
            'message' => 'Se ha solicitado correcciÃ³n. El estudiante podrÃ¡ modificar su solicitud.',
        ]);
    }

    /**
     * Listar pagos
     */
    public function pagos(Request $request): JsonResponse
    {
        // El middleware ya verificÃ³ el rol
        // Gate::authorize('gestionar-pagos');

        $perPage = $request->query('per_page', 20);
        $estatus = $request->query('estatus');
        $estudianteId = $request->query('estudiante_id');

        $query = DB::table('pagos')
            ->join('estudiantes', 'pagos.estudiante_id', '=', 'estudiantes.id')
            ->select(
                'pagos.*',
                'estudiantes.nombre as estudiante_nombre',
                'estudiantes.apellido as estudiante_apellido',
                'estudiantes.matricula'
            )
            ->orderBy('pagos.created_at', 'desc');

        if ($estatus) {
            $query->where('pagos.estatus', $estatus);
        }

        if ($estudianteId) {
            $query->where('pagos.estudiante_id', $estudianteId);
        }

        $pagos = $query->paginate($perPage);

        return response()->json($pagos);
    }

    /**
     * Registrar pago
     */
    public function registrarPago(Request $request): JsonResponse
    {
        Gate::authorize('gestionar-pagos');

        $request->validate([
            'estudiante_id' => 'required|exists:estudiantes,id',
            'concepto' => 'required|string|max:100',
            'monto' => 'required|numeric|min:0',
            'fecha_vencimiento' => 'nullable|date',
            'referencia' => 'nullable|string|max:50',
        ]);

        $pagoId = DB::table('pagos')->insertGetId([
            'estudiante_id' => $request->estudiante_id,
            'concepto' => $request->concepto,
            'monto' => $request->monto,
            'estatus' => 'pendiente',
            'fecha_vencimiento' => $request->fecha_vencimiento,
            'referencia' => $request->referencia,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        SecurityLogService::log(
            'pago_registrado',
            $request->user(),
            'Pago',
            $pagoId,
            null,
            $request->all()
        );

        return response()->json([
            'message' => 'Pago registrado exitosamente',
            'pago_id' => $pagoId,
        ], 201);
    }

    /**
     * Marcar pago como pagado
     */
    public function marcarPagado(Request $request, int $id): JsonResponse
    {
        Gate::authorize('gestionar-pagos');

        $request->validate([
            'referencia' => 'required|string|max:50',
        ]);

        DB::table('pagos')
            ->where('id', $id)
            ->update([
                'estatus' => 'pagado',
                'fecha_pago' => now(),
                'referencia' => $request->referencia,
                'updated_at' => now(),
            ]);

        SecurityLogService::log(
            'pago_confirmado',
            $request->user(),
            'Pago',
            $id,
            null,
            ['referencia' => $request->referencia]
        );

        return response()->json([
            'message' => 'Pago confirmado exitosamente',
        ]);
    }

    /**
     * Reportes acadÃ©micos
     */
    public function reportes(Request $request): JsonResponse
    {
        // El middleware ya verificÃ³ el rol
        // Gate::authorize('ver-reportes-academicos');

        $tipo = $request->query('tipo', 'general');

        $reportes = match($tipo) {
            'inscripciones' => $this->reporteInscripciones(),
            'calificaciones' => $this->reporteCalificaciones(),
            'estudiantes' => $this->reporteEstudiantes(),
            default => $this->reporteGeneral(),
        };

        return response()->json($reportes);
    }

    private function reporteGeneral(): array
    {
        return [
            'total_estudiantes' => DB::table('estudiantes')->count(),
            'estudiantes_activos' => DB::table('estudiantes')->where('estatus', 'activo')->count(),
            'total_profesores' => DB::table('profesores')->count(),
            'total_materias' => DB::table('materias')->count(),
            'inscripciones_periodo' => DB::table('inscripciones')
                ->where('periodo_academico', now()->year . '-' . (now()->month <= 6 ? '1' : '2'))
                ->count(),
        ];
    }

    private function reporteInscripciones(): array
    {
        return [
            'por_estatus' => DB::table('inscripciones')
                ->select('estatus', DB::raw('count(*) as count'))
                ->groupBy('estatus')
                ->get(),
            'por_materia' => DB::table('inscripciones')
                ->join('horarios', 'inscripciones.horario_id', '=', 'horarios.id')
                ->join('materias', 'horarios.materia_id', '=', 'materias.id')
                ->select('materias.nombre', DB::raw('count(*) as count'))
                ->groupBy('materias.id', 'materias.nombre')
                ->orderBy('count', 'desc')
                ->limit(10)
                ->get(),
        ];
    }

    private function reporteCalificaciones(): array
    {
        return [
            'promedio_general' => DB::table('calificaciones')
                ->avg('nota_final'),
            'aprobados' => DB::table('calificaciones')
                ->where('nota_final', '>=', 10)
                ->count(),
            'reprobados' => DB::table('calificaciones')
                ->where('nota_final', '<', 10)
                ->count(),
        ];
    }

    private function reporteEstudiantes(): array
    {
        return [
            'por_carrera' => DB::table('estudiantes')
                ->join('carreras', 'estudiantes.carrera_id', '=', 'carreras.id')
                ->select('carreras.nombre', DB::raw('count(*) as count'))
                ->where('estudiantes.estatus', 'activo')
                ->groupBy('carreras.id', 'carreras.nombre')
                ->get(),
            'por_semestre' => DB::table('estudiantes')
                ->select('semestre_actual', DB::raw('count(*) as count'))
                ->where('estatus', 'activo')
                ->groupBy('semestre_actual')
                ->orderBy('semestre_actual')
                ->get(),
        ];
    }
}

