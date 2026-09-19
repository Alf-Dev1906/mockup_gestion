<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Gate;use App\Models\User;
use App\Services\SecurityLogService;

class SoporteController extends Controller
{
    /**
     * Dashboard de soporte IT
     */
    public function dashboard(): JsonResponse
    {
        Gate::authorize('gestionar-usuarios');

        $stats = [
            'usuarios' => [
                'total' => User::count(),
                'activos_hoy' => DB::table('personal_access_tokens')
                    ->whereDate('last_used_at', today())
                    ->distinct('tokenable_id')
                    ->count('tokenable_id'),
                'por_rol' => User::select('role', DB::raw('count(*) as count'))
                    ->groupBy('role')
                    ->get(),
            ],
            'seguridad' => [
                'logins_fallidos_hoy' => DB::table('system_logs')
                    ->where('accion', 'login_fallido')
                    ->whereDate('created_at', today())
                    ->count(),
                'accesos_denegados_hoy' => DB::table('system_logs')
                    ->where('accion', 'intento_acceso_no_autorizado')
                    ->whereDate('created_at', today())
                    ->count(),
            ],
            'sistema' => [
                'backups_count' => DB::table('backups')->count(),
                'ultimo_backup' => DB::table('backups')
                    ->orderBy('created_at', 'desc')
                    ->first(),
            ],
        ];

        return response()->json($stats);
    }

    /**
     * Listar todos los usuarios
     */
    public function usuarios(Request $request): JsonResponse
    {
        Gate::authorize('viewAny', User::class);

        $perPage = $request->query('per_page', 20);
        $role = $request->query('role');
        $search = $request->query('search');

        $query = User::select('id', 'name', 'email', 'role', 'created_at', 'email_verified_at');

        if ($role) {
            $query->where('role', $role);
        }

        if ($search) {
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%");
            });
        }

        $usuarios = $query->paginate($perPage);

        return response()->json($usuarios);
    }

    /**
     * Ver usuario específico
     */
    public function showUsuario(User $usuario): JsonResponse
    {
        Gate::authorize('view', $usuario);

        $data = [
            'id' => $usuario->id,
            'name' => $usuario->name,
            'email' => $usuario->email,
            'role' => $usuario->role,
            'role_name' => $usuario->getRoleName(),
            'role_level' => $usuario->getRoleLevel(),
            'email_verified_at' => $usuario->email_verified_at,
            'created_at' => $usuario->created_at,
        ];

        // Agregar info adicional según rol
        if ($usuario->isEstudiante()) {
            $estudiante = \App\Models\Estudiante::where('email', $usuario->email)->first();
            if ($estudiante) {
                $data['estudiante'] = [
                    'id' => $estudiante->id,
                    'matricula' => $estudiante->matricula,
                    'estatus' => $estudiante->estatus,
                    'carrera' => $estudiante->carrera->nombre ?? null,
                ];
            }
        }

        if ($usuario->isProfesor()) {
            $profesor = \App\Models\Profesor::where('email', $usuario->email)->first();
            if ($profesor) {
                $data['profesor'] = [
                    'id' => $profesor->id,
                    'cedula' => $profesor->cedula,
                    'especialidad' => $profesor->especialidad,
                ];
            }
        }

        return response()->json($data);
    }

    /**
     * Crear usuario
     */
    public function createUsuario(Request $request): JsonResponse
    {
        Gate::authorize('create', User::class);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8',
            'role' => 'required|in:estudiante,profesor,administrativo,soporte_it,desarrollador',
        ]);

        // Verificar que puede asignar ese rol
        Gate::authorize('assignRole', [User::class, new User(), $request->role]);

        $usuario = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => $request->role,
            'email_verified_at' => now(),
        ]);

        SecurityLogService::log(
            'usuario_creado',
            $request->user(),
            'User',
            $usuario->id,
            null,
            ['name' => $usuario->name, 'email' => $usuario->email, 'role' => $usuario->role]
        );

        return response()->json($usuario, 201);
    }

    /**
     * Actualizar usuario
     */
    public function updateUsuario(Request $request, User $usuario): JsonResponse
    {
        Gate::authorize('update', $usuario);

        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|string|email|max:255|unique:users,email,' . $usuario->id,
        ]);

        $oldData = $usuario->toArray();

        $usuario->update($request->only(['name', 'email']));

        SecurityLogService::log(
            'usuario_actualizado',
            $request->user(),
            'User',
            $usuario->id,
            $oldData,
            $usuario->toArray()
        );

        return response()->json($usuario);
    }

    /**
     * Cambiar rol de usuario
     */
    public function cambiarRol(Request $request, User $usuario): JsonResponse
    {
        $request->validate([
            'role' => 'required|in:estudiante,profesor,administrativo,soporte_it,desarrollador',
        ]);

        // Verificar autorización para asignar ese rol
        Gate::authorize('assignRole', [$usuario, $request->role]);

        $oldRole = $usuario->role;
        $usuario->update(['role' => $request->role]);

        SecurityLogService::logRoleChange(
            $request->user(),
            $usuario,
            $oldRole,
            $request->role
        );

        return response()->json([
            'message' => 'Rol actualizado correctamente',
            'usuario' => $usuario,
        ]);
    }

    /**
     * Resetear contraseña de usuario
     */
    public function resetPassword(Request $request, User $usuario): JsonResponse
    {
        Gate::authorize('resetPassword', $usuario);

        $request->validate([
            'password' => 'required|string|min:8',
        ]);

        $usuario->update([
            'password' => Hash::make($request->password),
        ]);

        SecurityLogService::log(
            'password_reseteado',
            $request->user(),
            'User',
            $usuario->id
        );

        return response()->json([
            'message' => 'Contraseña actualizada correctamente',
        ]);
    }

    /**
     * Eliminar usuario
     */
    public function deleteUsuario(Request $request, User $usuario): JsonResponse
    {
        Gate::authorize('delete', $usuario);

        $usuarioData = $usuario->toArray();

        $usuario->delete();

        SecurityLogService::log(
            'usuario_eliminado',
            $request->user(),
            'User',
            $usuario->id,
            $usuarioData,
            null
        );

        return response()->json([
            'message' => 'Usuario eliminado correctamente',
        ]);
    }

    /**
     * Ver logs de actividad
     */
    public function logs(Request $request): JsonResponse
    {
        Gate::authorize('ver-logs-sistema');

        $limit = $request->query('limit', 100);
        $action = $request->query('action');
        $userId = $request->query('user_id');

        $query = DB::table('system_logs')
            ->leftJoin('users', 'system_logs.user_id', '=', 'users.id')
            ->select(
                'system_logs.*',
                'users.name as user_name',
                'users.email as user_email'
            )
            ->orderBy('system_logs.created_at', 'desc')
            ->limit($limit);

        if ($action) {
            $query->where('system_logs.accion', $action);
        }

        if ($userId) {
            $query->where('system_logs.user_id', $userId);
        }

        $logs = $query->get();

        return response()->json($logs);
    }

    /**
     * Ver sesiones activas
     */
    public function sesionesActivas(): JsonResponse
    {
        Gate::authorize('ver-logs-sistema');

        $sesiones = DB::table('personal_access_tokens')
            ->join('users', 'personal_access_tokens.tokenable_id', '=', 'users.id')
            ->select(
                'personal_access_tokens.id',
                'users.id as user_id',
                'users.name',
                'users.email',
                'users.role',
                'personal_access_tokens.last_used_at',
                'personal_access_tokens.created_at'
            )
            ->whereNotNull('personal_access_tokens.last_used_at')
            ->orderBy('personal_access_tokens.last_used_at', 'desc')
            ->get();

        return response()->json($sesiones);
    }

    /**
     * Revocar sesión
     */
    public function revocarSesion(Request $request, int $tokenId): JsonResponse
    {
        Gate::authorize('gestionar-usuarios');

        DB::table('personal_access_tokens')
            ->where('id', $tokenId)
            ->delete();

        SecurityLogService::log(
            'sesion_revocada',
            $request->user(),
            'PersonalAccessToken',
            $tokenId
        );

        return response()->json([
            'message' => 'Sesión revocada correctamente',
        ]);
    }

    /**
     * Estadísticas del sistema
     */
    public function estadisticas(): JsonResponse
    {
        Gate::authorize('ver-logs-sistema');

        $stats = [
            'actividad_por_dia' => DB::table('system_logs')
                ->select(
                    DB::raw('DATE(created_at) as fecha'),
                    DB::raw('count(*) as total')
                )
                ->where('created_at', '>=', now()->subDays(30))
                ->groupBy('fecha')
                ->orderBy('fecha', 'desc')
                ->get(),
            
            'acciones_mas_frecuentes' => DB::table('system_logs')
                ->select('accion', DB::raw('count(*) as total'))
                ->where('created_at', '>=', now()->subDays(7))
                ->groupBy('accion')
                ->orderBy('total', 'desc')
                ->limit(10)
                ->get(),
            
            'usuarios_mas_activos' => DB::table('system_logs')
                ->join('users', 'system_logs.user_id', '=', 'users.id')
                ->select(
                    'users.id',
                    'users.name',
                    'users.email',
                    DB::raw('count(*) as actividades')
                )
                ->where('system_logs.created_at', '>=', now()->subDays(7))
                ->groupBy('users.id', 'users.name', 'users.email')
                ->orderBy('actividades', 'desc')
                ->limit(10)
                ->get(),
        ];

        return response()->json($stats);
    }

    /**
     * Listar backups
     */
    public function backups(): JsonResponse
    {
        Gate::authorize('gestionar-backups');

        $backups = DB::table('backups')
            ->leftJoin('users', 'backups.creado_por', '=', 'users.id')
            ->select(
                'backups.*',
                'users.name as creado_por_nombre'
            )
            ->orderBy('backups.created_at', 'desc')
            ->get();

        return response()->json($backups);
    }

    /**
     * Crear backup (delegado al DesarrolladorController pero accesible para soporte)
     */
    public function createBackup(Request $request): JsonResponse
    {
        Gate::authorize('gestionar-backups');

        // Reutilizar la lógica del desarrollador
        $controller = new DesarrolladorController();
        return $controller->createBackup($request);
    }
}
