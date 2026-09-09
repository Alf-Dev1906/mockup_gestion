<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use App\Models\Estudiante;
use App\Models\Inscripcion;

class EstudianteDashboardController extends Controller
{
    /** Datos del estudiante autenticado */
    private function getEstudiante(Request $request): ?Estudiante
    {
        return Estudiante::with('carrera.facultad')
            ->where('user_id', $request->user()->id)
            ->first();
    }

    /** Dashboard general */
    public function dashboard(Request $request): JsonResponse
    {
        $estudiante = $this->getEstudiante($request);
        if (!$estudiante) return response()->json(['error' => 'Perfil de estudiante no encontrado'], 404);

        $creditos = DB::table('inscripciones')
            ->join('horarios', 'inscripciones.horario_id', '=', 'horarios.id')
            ->join('materias', 'horarios.materia_id', '=', 'materias.id')
            ->where('inscripciones.estudiante_id', $estudiante->id)
            ->where('inscripciones.estatus', 'aprobado')
            ->sum('materias.creditos');

        $promedio = DB::table('inscripciones')
            ->where('inscripciones.estudiante_id', $estudiante->id)
            ->whereNotNull('inscripciones.nota_final')
            ->avg('inscripciones.nota_final');

        // Obtener solicitud de admisión si existe
        $solicitud = DB::table('solicitudes_admision')
            ->where('estudiante_id', $estudiante->id)
            ->select(
                'id',
                'numero_referencia',
                'estado',
                'paso_actual',
                'paso1_completado',
                'paso2_completado',
                'paso3_completado',
                'paso4_completado',
                'paso5_completado',
                'fecha_envio',
                'comentarios_admin',
                'razon_rechazo'
            )
            ->first();

        // Calcular porcentaje de completitud si hay solicitud
        if ($solicitud) {
            $pasosCompletados = 0;
            if ($solicitud->paso1_completado) $pasosCompletados++;
            if ($solicitud->paso2_completado) $pasosCompletados++;
            if ($solicitud->paso3_completado) $pasosCompletados++;
            if ($solicitud->paso4_completado) $pasosCompletados++;
            if ($solicitud->paso5_completado) $pasosCompletados++;
            
            $solicitud->porcentaje = round(($pasosCompletados / 5) * 100);
        }

        return response()->json([
            'estudiante'          => $estudiante,
            'materias_inscritas'  => Inscripcion::where('estudiante_id', $estudiante->id)->count(),
            'creditos_aprobados'  => (int) $creditos,
            'indice_academico'    => round($promedio ?? 0, 2),
            'semestre_actual'     => $estudiante->semestre_actual,
            'solicitud'           => $solicitud,
        ]);
    }

    /** Horarios del estudiante */
    public function horarios(Request $request): JsonResponse
    {
        $estudiante = $this->getEstudiante($request);
        if (!$estudiante) return response()->json(['error' => 'Perfil no encontrado'], 404);

        $horarios = DB::table('inscripciones')
            ->join('horarios',   'inscripciones.horario_id', '=', 'horarios.id')
            ->join('materias',   'horarios.materia_id', '=', 'materias.id')
            ->join('profesores', 'horarios.profesor_id', '=', 'profesores.id')
            ->leftJoin('aulas',  'horarios.aula_id', '=', 'aulas.id')
            ->where('inscripciones.estudiante_id', $estudiante->id)
            ->whereIn('inscripciones.estatus', ['inscrito', 'cursando'])
            ->select(
                'horarios.id',
                'horarios.dia_semana',
                'horarios.hora_inicio',
                'horarios.hora_fin',
                'materias.codigo as materia_codigo',
                'materias.nombre as materia_nombre',
                'materias.creditos',
                DB::raw("CONCAT(profesores.nombre, ' ', profesores.apellido) as profesor"),
                'aulas.codigo as aula_codigo',
                'aulas.edificio as aula_edificio'
            )
            ->orderByRaw("FIELD(horarios.dia_semana,'Lunes','Martes','Miércoles','Jueves','Viernes','Sábado','Domingo')")
            ->orderBy('horarios.hora_inicio')
            ->get();

        return response()->json($horarios);
    }

    /** Calificaciones del estudiante */
    public function calificaciones(Request $request): JsonResponse
    {
        $estudiante = $this->getEstudiante($request);
        if (!$estudiante) return response()->json(['error' => 'Perfil no encontrado'], 404);

        $calificaciones = DB::table('inscripciones')
            ->join('horarios', 'inscripciones.horario_id', '=', 'horarios.id')
            ->join('materias', 'horarios.materia_id', '=', 'materias.id')
            ->where('inscripciones.estudiante_id', $estudiante->id)
            ->select(
                'inscripciones.id as inscripcion_id',
                'inscripciones.estatus as inscripcion_estatus',
                'materias.codigo',
                'materias.nombre',
                'materias.creditos',
                'materias.semestre_recomendado as semestre',
                'inscripciones.nota_parcial_1 as parcial1',
                'inscripciones.nota_parcial_2 as parcial2',
                'inscripciones.nota_parcial_3 as parcial3',
                'inscripciones.nota_final'
            )
            ->orderBy('materias.semestre_recomendado')
            ->orderBy('materias.nombre')
            ->get();

        $resumen = [
            'promedio'   => round($calificaciones->whereNotNull('nota_final')->avg('nota_final') ?? 0, 2),
            'aprobadas'  => $calificaciones->where('estatus', 'aprobado')->count(),
            'reprobadas' => $calificaciones->where('estatus', 'reprobado')->count(),
            'pendientes' => $calificaciones->whereIn('estatus', ['inscrito', 'cursando'])->whereNull('nota_final')->count(),
        ];

        return response()->json([
            'calificaciones' => $calificaciones,
            'resumen'        => $resumen,
        ]);
    }

    /** Estado de solicitud (solo para solicitantes) */
    public function solicitud(Request $request): JsonResponse
    {
        $user = $request->user();
        $solicitud = DB::table('solicitudes_admision')
            ->leftJoin('users as revisor', 'solicitudes_admision.revisado_por', '=', 'revisor.id')
            ->select(
                'solicitudes_admision.*',
                'revisor.name as revisado_por_nombre'
            )
            ->where('solicitudes_admision.user_id', $user->id)
            ->first();

        if (!$solicitud) {
            return response()->json(['message' => 'No se encontró solicitud de admisión'], 404);
        }

        // Decodificar campos JSON
        $solicitud->datos_personales  = json_decode($solicitud->datos_personales ?? 'null');
        $solicitud->datos_contacto    = json_decode($solicitud->datos_contacto ?? 'null');
        $solicitud->datos_academicos  = json_decode($solicitud->datos_academicos ?? 'null');
        $solicitud->contacto_emergencia = json_decode($solicitud->contacto_emergencia ?? 'null');

        return response()->json($solicitud);
    }

    /** Perfil del estudiante */
    public function perfil(Request $request): JsonResponse
    {
        $user = $request->user();
        $estudiante = $this->getEstudiante($request);

        return response()->json([
            'user' => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
            'estudiante' => $estudiante,
        ]);
    }

    /** Inscripciones actuales */
    public function inscripciones(Request $request): JsonResponse
    {
        $estudiante = $this->getEstudiante($request);
        if (!$estudiante) return response()->json(['error' => 'Perfil no encontrado'], 404);

        // Verificar que el estudiante esté activo
        if ($estudiante->estatus !== 'activo') {
            return response()->json(['error' => 'Tu cuenta no permite inscripciones'], 403);
        }

        $inscripciones = Inscripcion::with(['materia.carrera'])
            ->where('estudiante_id', $estudiante->id)
            ->orderBy('created_at', 'desc')
            ->get();

        return response()->json($inscripciones);
    }
}
