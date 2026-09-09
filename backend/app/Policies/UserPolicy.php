<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    /**
     * Ver listado de usuarios
     */
    public function viewAny(User $user): bool
    {
        // Solo Soporte IT y Desarrollador
        return $user->hasRole('soporte_it');
    }

    /**
     * Ver un usuario específico
     */
    public function view(User $user, User $targetUser): bool
    {
        // Usuario puede ver su propio perfil
        if ($user->id === $targetUser->id) {
            return true;
        }

        // Soporte IT y superiores pueden ver otros usuarios
        return $user->hasRole('soporte_it');
    }

    /**
     * Crear usuario
     */
    public function create(User $user): bool
    {
        // Solo Soporte IT y superiores
        return $user->hasRole('soporte_it');
    }

    /**
     * Actualizar usuario
     */
    public function update(User $user, User $targetUser): bool
    {
        // Usuario puede actualizar su propio perfil (datos limitados)
        if ($user->id === $targetUser->id) {
            return true;
        }

        // Soporte IT puede actualizar usuarios de nivel inferior
        if ($user->isSoporteIT()) {
            return $targetUser->getRoleLevel() < $user->getRoleLevel();
        }

        // Desarrollador puede actualizar cualquiera
        return $user->isDesarrollador();
    }

    /**
     * Eliminar usuario
     */
    public function delete(User $user, User $targetUser): bool
    {
        // No se puede eliminar a sí mismo
        if ($user->id === $targetUser->id) {
            return false;
        }

        // Soporte IT puede eliminar usuarios de nivel inferior
        if ($user->isSoporteIT()) {
            return $targetUser->getRoleLevel() < $user->getRoleLevel();
        }

        // Desarrollador puede eliminar cualquiera (excepto a sí mismo)
        return $user->isDesarrollador();
    }

    /**
     * Asignar roles
     */
    public function assignRole(User $user, User $targetUser, string $newRole): bool
    {
        // No se puede cambiar el propio rol
        if ($user->id === $targetUser->id) {
            return false;
        }

        $roleHierarchy = [
            'estudiante' => 1,
            'profesor' => 2,
            'administrativo' => 3,
            'soporte_it' => 4,
            'desarrollador' => 5,
        ];

        $newRoleLevel = $roleHierarchy[$newRole] ?? 0;

        // Soporte IT puede asignar roles de nivel inferior al suyo
        if ($user->isSoporteIT() && !$user->isDesarrollador()) {
            return $newRoleLevel < $user->getRoleLevel() 
                && $targetUser->getRoleLevel() < $user->getRoleLevel();
        }

        // Solo Desarrollador puede asignar rol de Desarrollador
        if ($newRole === 'desarrollador') {
            return $user->isDesarrollador();
        }

        // Desarrollador puede asignar cualquier rol
        return $user->isDesarrollador();
    }

    /**
     * Resetear contraseña de otros usuarios
     */
    public function resetPassword(User $user, User $targetUser): bool
    {
        // No se incluye resetear la propia contraseña (eso se hace por otro flujo)
        if ($user->id === $targetUser->id) {
            return false;
        }

        // Soporte IT puede resetear contraseñas de usuarios de nivel inferior
        if ($user->isSoporteIT()) {
            return $targetUser->getRoleLevel() < $user->getRoleLevel();
        }

        return false;
    }

    /**
     * Ver logs de actividad del usuario
     */
    public function viewActivityLogs(User $user, User $targetUser): bool
    {
        // Soporte IT y superiores pueden ver logs
        return $user->hasRole('soporte_it');
    }
}
