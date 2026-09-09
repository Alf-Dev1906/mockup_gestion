<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;

class EnsureUserRole
{
    /**
     * Jerarquía de roles (de menor a mayor nivel de acceso)
     * Cada nivel tiene acceso a sus funciones Y a las de niveles inferiores
     */
    private const ROLE_HIERARCHY = [
        'estudiante' => 1,
        'profesor' => 2,
        'administrativo' => 3,
        'soporte_it' => 4,
        'desarrollador' => 5,
    ];

    /**
     * Permisos específicos por rol
     */
    private const ROLE_PERMISSIONS = [
        'estudiante' => [
            'ver_propio_perfil',
            'ver_propias_calificaciones',
            'ver_propios_horarios',
            'inscribirse_materias',
            'ver_propias_inscripciones',
            'actualizar_propio_perfil',
        ],
        'profesor' => [
            'ver_propias_materias',
            'gestionar_calificaciones_propias',
            'ver_estudiantes_propias_materias',
            'tomar_asistencia',
            'ver_propio_horario',
        ],
        'administrativo' => [
            'gestionar_estudiantes',
            'gestionar_profesores',
            'gestionar_materias',
            'gestionar_carreras',
            'gestionar_facultades',
            'gestionar_aulas',
            'gestionar_horarios',
            'gestionar_inscripciones',
            'aprobar_solicitudes_admision',
            'rechazar_solicitudes_admision',
            'gestionar_pagos',
            'ver_reportes_academicos',
            'ver_todas_calificaciones',
        ],
        'soporte_it' => [
            'gestionar_usuarios',
            'asignar_roles',
            'resetear_passwords',
            'ver_logs_sistema',
            'gestionar_respaldos',
            'ver_estadisticas_sistema',
            'gestionar_tickets_soporte',
            'ver_sesiones_activas',
        ],
        'desarrollador' => [
            'acceso_total',
            'modificar_codigo',
            'acceso_base_datos',
            'configuraciones_criticas',
            'deploy_actualizaciones',
            'ver_logs_completos',
            'ejecutar_comandos_sistema',
            'modificar_cualquier_dato',
        ],
    ];

    /**
     * Maneja la autorización basada en roles y jerarquía
     *
     * @param Request $request
     * @param Closure $next
     * @param string $requiredRole Rol mínimo requerido
     * @param string|null $permission Permiso específico opcional
     * @return Response
     */
    public function handle(Request $request, Closure $next, string $requiredRole, ?string $permission = null): Response
    {
        $user = $request->user();

        // 1. Verificar que el usuario esté autenticado
        if (!$user) {
            Log::warning('Intento de acceso sin autenticación', [
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'method' => $request->method(),
            ]);

            return response()->json([
                'message' => 'No autenticado. Por favor inicie sesión.',
                'error' => 'UNAUTHENTICATED'
            ], 401);
        }

        // 2. Verificar que el rol del usuario sea válido
        if (!$this->isValidRole($user->role)) {
            Log::error('Usuario con rol inválido detectado', [
                'user_id' => $user->id,
                'role' => $user->role,
                'email' => $user->email,
            ]);

            return response()->json([
                'message' => 'Rol de usuario inválido. Contacte al administrador.',
                'error' => 'INVALID_ROLE'
            ], 403);
        }

        // 3. Verificar que el rol requerido sea válido
        if (!$this->isValidRole($requiredRole)) {
            Log::error('Middleware configurado con rol inválido', [
                'required_role' => $requiredRole,
                'route' => $request->route()?->getName(),
            ]);

            return response()->json([
                'message' => 'Error de configuración de seguridad.',
                'error' => 'MISCONFIGURED_MIDDLEWARE'
            ], 500);
        }

        // 4. Verificar jerarquía de roles
        if (!$this->hasRoleAccess($user->role, $requiredRole)) {
            Log::warning('Intento de acceso denegado por jerarquía de roles', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'required_role' => $requiredRole,
                'route' => $request->route()?->getName(),
                'ip' => $request->ip(),
            ]);

            return response()->json([
                'message' => 'No tiene permisos suficientes para acceder a este recurso.',
                'error' => 'INSUFFICIENT_PERMISSIONS',
                'required_role' => $requiredRole,
                'your_role' => $user->role,
            ], 403);
        }

        // 5. Verificar permiso específico si se proporciona
        if ($permission && !$this->hasPermission($user->role, $permission)) {
            Log::warning('Intento de acceso denegado por falta de permiso específico', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'required_permission' => $permission,
                'route' => $request->route()?->getName(),
            ]);

            return response()->json([
                'message' => 'No tiene el permiso específico requerido para esta acción.',
                'error' => 'MISSING_PERMISSION',
                'required_permission' => $permission,
            ], 403);
        }

        // 6. Verificaciones adicionales para estudiantes
        if ($user->role === 'estudiante') {
            $estudianteStatus = $this->getEstudianteStatus($user);
            
            // Los solicitantes tienen acceso muy limitado
            if ($estudianteStatus === 'solicitante') {
                // Solo permitir acceso a rutas específicas para solicitantes
                $allowedRoutes = [
                    'estudiante.solicitud.status',
                    'estudiante.documentos.upload',
                    'estudiante.perfil.show',
                    'auth.logout',
                ];

                $currentRoute = $request->route()?->getName();
                
                if (!in_array($currentRoute, $allowedRoutes)) {
                    return response()->json([
                        'message' => 'Su solicitud de admisión está pendiente. No puede acceder a esta función hasta ser aprobado.',
                        'error' => 'PENDING_ADMISSION',
                        'status' => 'solicitante',
                    ], 403);
                }
            }

            // Estudiantes suspendidos tienen restricciones
            if ($estudianteStatus === 'suspendido') {
                $restrictedRoutes = [
                    'estudiante.inscripciones.create',
                    'estudiante.materias.inscribir',
                ];

                $currentRoute = $request->route()?->getName();
                
                if (in_array($currentRoute, $restrictedRoutes)) {
                    return response()->json([
                        'message' => 'Su cuenta está suspendida. No puede realizar inscripciones. Contacte administración.',
                        'error' => 'ACCOUNT_SUSPENDED',
                        'status' => 'suspendido',
                    ], 403);
                }
            }
        }

        // 7. Log de acceso exitoso para auditoría (solo para acciones sensibles)
        if ($this->isSensitiveRoute($request)) {
            Log::info('Acceso autorizado a ruta sensible', [
                'user_id' => $user->id,
                'user_role' => $user->role,
                'route' => $request->route()?->getName(),
                'method' => $request->method(),
                'ip' => $request->ip(),
            ]);
        }

        return $next($request);
    }

    /**
     * Verifica si un rol es válido
     */
    private function isValidRole(string $role): bool
    {
        return array_key_exists($role, self::ROLE_HIERARCHY);
    }

    /**
     * Verifica si el rol del usuario tiene acceso al rol requerido
     * Un rol tiene acceso si su nivel es >= al nivel requerido
     */
    private function hasRoleAccess(string $userRole, string $requiredRole): bool
    {
        $userLevel = self::ROLE_HIERARCHY[$userRole] ?? 0;
        $requiredLevel = self::ROLE_HIERARCHY[$requiredRole] ?? 999;

        return $userLevel >= $requiredLevel;
    }

    /**
     * Verifica si un rol tiene un permiso específico
     * Incluye permisos heredados de roles inferiores
     */
    private function hasPermission(string $role, string $permission): bool
    {
        // Desarrollador tiene TODOS los permisos
        if ($role === 'desarrollador') {
            return true;
        }

        // Obtener todos los permisos del rol incluyendo heredados
        $allPermissions = $this->getAllPermissionsForRole($role);

        return in_array($permission, $allPermissions);
    }

    /**
     * Obtiene todos los permisos de un rol incluyendo los heredados
     */
    private function getAllPermissionsForRole(string $role): array
    {
        $permissions = [];
        $roleLevel = self::ROLE_HIERARCHY[$role] ?? 0;

        // Agregar permisos de todos los roles con nivel menor o igual
        foreach (self::ROLE_HIERARCHY as $r => $level) {
            if ($level <= $roleLevel) {
                $permissions = array_merge($permissions, self::ROLE_PERMISSIONS[$r] ?? []);
            }
        }

        return array_unique($permissions);
    }

    /**
     * Obtiene el estatus del estudiante desde la BD
     */
    private function getEstudianteStatus($user): ?string
    {
        if ($user->role !== 'estudiante') {
            return null;
        }

        $estudiante = \App\Models\Estudiante::where('email', $user->email)->first();
        
        return $estudiante?->estatus;
    }

    /**
     * Determina si una ruta es sensible y debe ser auditada
     */
    private function isSensitiveRoute(Request $request): bool
    {
        $sensitivePatterns = [
            'admin.*',
            'soporte.*',
            'dev.*',
            '*.delete',
            '*.destroy',
            'usuarios.update',
            'roles.assign',
            'configuracion.*',
            'backups.*',
            'logs.*',
        ];

        $routeName = $request->route()?->getName() ?? '';

        foreach ($sensitivePatterns as $pattern) {
            if (fnmatch($pattern, $routeName)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Método estático para usar en controladores
     * Verifica si el usuario tiene un rol específico o superior
     */
    public static function userHasRole($user, string $requiredRole): bool
    {
        if (!$user) {
            return false;
        }

        $userLevel = self::ROLE_HIERARCHY[$user->role] ?? 0;
        $requiredLevel = self::ROLE_HIERARCHY[$requiredRole] ?? 999;

        return $userLevel >= $requiredLevel;
    }

    /**
     * Método estático para verificar permisos específicos
     */
    public static function userHasPermission($user, string $permission): bool
    {
        if (!$user) {
            return false;
        }

        if ($user->role === 'desarrollador') {
            return true;
        }

        $instance = new self();
        return $instance->hasPermission($user->role, $permission);
    }
}
