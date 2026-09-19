<?php

namespace App\Traits;

trait HasRoleAuthorization
{
    /**
     * Jerarquía de roles
     */
    private static $roleHierarchy = [
        'estudiante' => 1,
        'profesor' => 2,
        'administrativo' => 3,
        'soporte_it' => 4,
        'desarrollador' => 5,
    ];

    /**
     * Verifica si el usuario tiene un rol específico o superior
     */
    public function hasRole(string $role): bool
    {
        $userLevel = self::$roleHierarchy[$this->role] ?? 0;
        $requiredLevel = self::$roleHierarchy[$role] ?? 999;

        return $userLevel >= $requiredLevel;
    }

    /**
     * Alias de hasRole() para mayor claridad semántica
     * Verifica si el usuario tiene el nivel mínimo de rol requerido
     */
    public function hasMinimumRole(string $role): bool
    {
        return $this->hasRole($role);
    }

    /**
     * Verifica si el usuario tiene exactamente un rol
     */
    public function hasExactRole(string $role): bool
    {
        return $this->role === $role;
    }

    /**
     * Verifica si el usuario es estudiante
     */
    public function isEstudiante(): bool
    {
        return $this->role === 'estudiante';
    }

    /**
     * Verifica si el usuario es profesor
     */
    public function isProfesor(): bool
    {
        return $this->hasRole('profesor');
    }

    /**
     * Verifica si el usuario es administrativo
     */
    public function isAdministrativo(): bool
    {
        return $this->hasRole('administrativo');
    }

    /**
     * Verifica si el usuario es soporte IT
     */
    public function isSoporteIT(): bool
    {
        return $this->hasRole('soporte_it');
    }

    /**
     * Verifica si el usuario es desarrollador
     */
    public function isDesarrollador(): bool
    {
        return $this->hasRole('desarrollador');
    }

    /**
     * Verifica si el usuario puede gestionar otros usuarios
     */
    public function canManageUsers(): bool
    {
        return $this->hasRole('soporte_it');
    }

    /**
     * Verifica si el usuario puede aprobar solicitudes de admisión
     */
    public function canApproveAdmissions(): bool
    {
        return $this->hasRole('administrativo');
    }

    /**
     * Verifica si el usuario puede gestionar calificaciones
     */
    public function canManageGrades(): bool
    {
        return $this->hasRole('profesor');
    }

    /**
     * Verifica si el usuario puede acceder a configuraciones críticas
     */
    public function canAccessCriticalConfig(): bool
    {
        return $this->isDesarrollador();
    }

    /**
     * Verifica si el usuario puede ver logs del sistema
     */
    public function canViewSystemLogs(): bool
    {
        return $this->hasRole('soporte_it');
    }

    /**
     * Verifica si el usuario puede gestionar backups
     */
    public function canManageBackups(): bool
    {
        return $this->hasRole('soporte_it');
    }

    /**
     * Obtiene el nivel jerárquico del rol
     */
    public function getRoleLevel(): int
    {
        return self::$roleHierarchy[$this->role] ?? 0;
    }

    /**
     * Obtiene el nombre legible del rol
     */
    public function getRoleName(): string
    {
        $roleNames = [
            'estudiante' => 'Estudiante',
            'profesor' => 'Profesor',
            'administrativo' => 'Administrativo',
            'soporte_it' => 'Soporte IT',
            'desarrollador' => 'Desarrollador',
        ];

        return $roleNames[$this->role] ?? 'Desconocido';
    }

    /**
     * Verifica si el usuario puede realizar una acción sobre otro usuario
     * basado en la jerarquía de roles (solo puede gestionar roles inferiores)
     */
    public function canManageUser(User $targetUser): bool
    {
        return $this->getRoleLevel() > $targetUser->getRoleLevel();
    }

    /**
     * Verifica si el usuario puede impersonar otro rol (solo desarrollador)
     */
    public function canImpersonate(): bool
    {
        return $this->isDesarrollador();
    }

    /**
     * Verifica si el usuario es estudiante activo (solo para estudiantes)
     */
    public function isEstudianteActivo(): bool
    {
        if (!$this->isEstudiante()) {
            return false;
        }

        $estudiante = \App\Models\Estudiante::where('email', $this->email)->first();
        
        return $estudiante && $estudiante->estatus === 'activo';
    }

    /**
     * Verifica si el estudiante es solicitante (solo para estudiantes)
     */
    public function isEstudianteSolicitante(): bool
    {
        if (!$this->isEstudiante()) {
            return false;
        }

        $estudiante = \App\Models\Estudiante::where('email', $this->email)->first();
        
        return $estudiante && $estudiante->estatus === 'solicitante';
    }

    /**
     * Obtiene el estatus del estudiante
     */
    public function getEstudianteStatus(): ?string
    {
        if (!$this->isEstudiante()) {
            return null;
        }

        $estudiante = \App\Models\Estudiante::where('email', $this->email)->first();
        
        return $estudiante?->estatus;
    }

    /**
     * Scope para filtrar por rol
     */
    public function scopeWithRole($query, string $role)
    {
        return $query->where('role', $role);
    }

    /**
     * Scope para filtrar por rol mínimo
     */
    public function scopeWithMinRole($query, string $minRole)
    {
        $minLevel = self::$roleHierarchy[$minRole] ?? 0;
        
        $roles = array_keys(array_filter(
            self::$roleHierarchy,
            fn($level) => $level >= $minLevel
        ));

        return $query->whereIn('role', $roles);
    }
}
