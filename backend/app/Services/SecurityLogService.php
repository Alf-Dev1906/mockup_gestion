<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\User;

class SecurityLogService
{
    /**
     * Registra un evento de seguridad en system_logs
     */
    public static function log(
        string $accion,
        ?User $user = null,
        ?string $modelo = null,
        ?int $modeloId = null,
        ?array $datosAnteriores = null,
        ?array $datosNuevos = null,
        ?string $ip = null,
        ?string $userAgent = null
    ): void {
        try {
            DB::table('system_logs')->insert([
                'user_id' => $user?->id,
                'accion' => $accion,
                'modelo' => $modelo,
                'modelo_id' => $modeloId,
                'datos_anteriores' => $datosAnteriores ? json_encode($datosAnteriores) : null,
                'datos_nuevos' => $datosNuevos ? json_encode($datosNuevos) : null,
                'ip' => $ip ?? request()->ip(),
                'user_agent' => $userAgent ?? request()->userAgent(),
                'created_at' => now(),
            ]);
        } catch (\Exception $e) {
            // Si falla el log en BD, al menos loguear en archivo
            Log::error('Error al registrar log de seguridad', [
                'accion' => $accion,
                'user_id' => $user?->id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Log de intento de acceso no autorizado
     */
    public static function logUnauthorizedAccess(
        ?User $user,
        string $recurso,
        string $razon
    ): void {
        self::log(
            accion: 'intento_acceso_no_autorizado',
            user: $user,
            datosNuevos: [
                'recurso' => $recurso,
                'razon' => $razon,
                'timestamp' => now()->toDateTimeString(),
            ]
        );

        Log::warning('Intento de acceso no autorizado', [
            'user_id' => $user?->id,
            'user_role' => $user?->role,
            'recurso' => $recurso,
            'razon' => $razon,
            'ip' => request()->ip(),
        ]);
    }

    /**
     * Log de login exitoso
     */
    public static function logSuccessfulLogin(User $user): void
    {
        self::log(
            accion: 'login_exitoso',
            user: $user,
            datosNuevos: [
                'email' => $user->email,
                'role' => $user->role,
            ]
        );
    }

    /**
     * Log de login fallido
     */
    public static function logFailedLogin(string $email, string $razon = 'credenciales_invalidas'): void
    {
        self::log(
            accion: 'login_fallido',
            datosNuevos: [
                'email' => $email,
                'razon' => $razon,
            ]
        );

        Log::warning('Intento de login fallido', [
            'email' => $email,
            'razon' => $razon,
            'ip' => request()->ip(),
        ]);
    }

    /**
     * Log de cambio de rol
     */
    public static function logRoleChange(
        User $adminUser,
        User $targetUser,
        string $oldRole,
        string $newRole
    ): void {
        self::log(
            accion: 'cambio_rol',
            user: $adminUser,
            modelo: 'User',
            modeloId: $targetUser->id,
            datosAnteriores: ['role' => $oldRole],
            datosNuevos: ['role' => $newRole]
        );

        Log::info('Cambio de rol ejecutado', [
            'admin_id' => $adminUser->id,
            'target_user_id' => $targetUser->id,
            'old_role' => $oldRole,
            'new_role' => $newRole,
        ]);
    }

    /**
     * Log de aprobación de solicitud de admisión
     */
    public static function logAdmissionApproval(
        User $adminUser,
        int $solicitudId,
        bool $aprobado
    ): void {
        self::log(
            accion: $aprobado ? 'solicitud_aprobada' : 'solicitud_rechazada',
            user: $adminUser,
            modelo: 'SolicitudAdmision',
            modeloId: $solicitudId
        );
    }

    /**
     * Log de modificación de calificación
     */
    public static function logGradeChange(
        User $user,
        int $calificacionId,
        array $oldGrades,
        array $newGrades
    ): void {
        self::log(
            accion: 'modificacion_calificacion',
            user: $user,
            modelo: 'Calificacion',
            modeloId: $calificacionId,
            datosAnteriores: $oldGrades,
            datosNuevos: $newGrades
        );
    }

    /**
     * Log de creación de backup
     */
    public static function logBackupCreated(User $user, string $backupName, int $size): void
    {
        self::log(
            accion: 'backup_creado',
            user: $user,
            datosNuevos: [
                'backup_name' => $backupName,
                'size_bytes' => $size,
            ]
        );
    }

    /**
     * Log de acceso a configuración crítica
     */
    public static function logCriticalConfigAccess(User $user, string $configKey): void
    {
        self::log(
            accion: 'acceso_configuracion_critica',
            user: $user,
            datosNuevos: ['config_key' => $configKey]
        );

        Log::info('Acceso a configuración crítica', [
            'user_id' => $user->id,
            'config_key' => $configKey,
            'ip' => request()->ip(),
        ]);
    }

    /**
     * Log de comando ejecutado
     */
    public static function logCommandExecution(User $user, string $command): void
    {
        self::log(
            accion: 'comando_ejecutado',
            user: $user,
            datosNuevos: ['command' => $command]
        );

        Log::info('Comando del sistema ejecutado', [
            'user_id' => $user->id,
            'command' => $command,
        ]);
    }

    /**
     * Obtiene logs de un usuario específico
     */
    public static function getUserLogs(int $userId, int $limit = 50): array
    {
        return DB::table('system_logs')
            ->where('user_id', $userId)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }

    /**
     * Obtiene logs de seguridad recientes
     */
    public static function getRecentSecurityLogs(int $limit = 100): array
    {
        $securityActions = [
            'intento_acceso_no_autorizado',
            'login_fallido',
            'cambio_rol',
            'acceso_configuracion_critica',
            'comando_ejecutado',
        ];

        return DB::table('system_logs')
            ->whereIn('accion', $securityActions)
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
