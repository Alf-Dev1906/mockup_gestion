<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Estudiante;

class EstudiantePolicy
{
    /**
     * Ver listado de estudiantes
     */
    public function viewAny(User $user): bool
    {
        // Administrativos, Soporte IT y Desarrolladores pueden ver todos
        return $user->hasRole('administrativo');
    }

    /**
     * Ver un estudiante específico
     */
    public function view(User $user, Estudiante $estudiante): bool
    {
        // Estudiante puede ver solo su propio perfil
        if ($user->isEstudiante()) {
            return $estudiante->user_id === $user->id;
        }

        // Profesor puede ver estudiantes de sus materias
        if ($user->isProfesor()) {
            return $this->profesorTeachesEstudiante($user, $estudiante);
        }

        // Administrativo y superiores pueden ver todos
        return $user->hasRole('administrativo');
    }

    /**
     * Crear estudiante
     */
    public function create(User $user): bool
    {
        // Solo administrativos y superiores
        return $user->hasRole('administrativo');
    }

    /**
     * Actualizar estudiante
     */
    public function update(User $user, Estudiante $estudiante): bool
    {
        // Estudiante puede actualizar solo su propio perfil (datos limitados)
        if ($user->isEstudiante() && $estudiante->user_id === $user->id) {
            return true;
        }

        // Administrativos y superiores pueden actualizar cualquiera
        return $user->hasRole('administrativo');
    }

    /**
     * Eliminar estudiante
     */
    public function delete(User $user, Estudiante $estudiante): bool
    {
        // Solo administrativos y superiores
        return $user->hasRole('administrativo');
    }

    /**
     * Cambiar estatus de estudiante (solicitante -> activo, etc)
     */
    public function changeStatus(User $user, Estudiante $estudiante): bool
    {
        // Solo administrativos y superiores
        return $user->hasRole('administrativo');
    }

    /**
     * Verifica si un profesor enseña a un estudiante
     */
    private function profesorTeachesEstudiante(User $user, Estudiante $estudiante): bool
    {
        $profesor = \App\Models\Profesor::where('email', $user->email)->first();
        
        if (!$profesor) {
            return false;
        }

        // Obtener inscripciones del estudiante en materias del profesor
        return \App\Models\Inscripcion::where('estudiante_id', $estudiante->id)
            ->whereHas('materia.horarios', function ($query) use ($profesor) {
                $query->where('profesor_id', $profesor->id);
            })
            ->exists();
    }
}
