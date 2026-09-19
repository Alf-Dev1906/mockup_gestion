<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Calificacion;

class CalificacionPolicy
{
    /**
     * Ver listado de calificaciones
     */
    public function viewAny(User $user): bool
    {
        // Todos pueden ver calificaciones (pero filtradas por su scope)
        return true;
    }

    /**
     * Ver una calificación específica
     */
    public function view(User $user, Calificacion $calificacion): bool
    {
        // Estudiante puede ver solo sus propias calificaciones
        if ($user->isEstudiante()) {
            $estudiante = \App\Models\Estudiante::where('email', $user->email)->first();
            return $estudiante && $calificacion->inscripcion->estudiante_id === $estudiante->id;
        }

        // Profesor puede ver calificaciones de sus materias
        if ($user->isProfesor()) {
            $profesor = \App\Models\Profesor::where('email', $user->email)->first();
            return $profesor && $this->profesorTeachesMateria($profesor, $calificacion->inscripcion->materia_id);
        }

        // Administrativo y superiores pueden ver todas
        return $user->hasRole('administrativo');
    }

    /**
     * Crear calificación
     */
    public function create(User $user): bool
    {
        // Solo profesores y superiores
        return $user->hasRole('profesor');
    }

    /**
     * Actualizar calificación
     */
    public function update(User $user, Calificacion $calificacion): bool
    {
        // Profesor solo puede actualizar calificaciones de sus materias
        if ($user->isProfesor() && !$user->hasRole('administrativo')) {
            $profesor = \App\Models\Profesor::where('email', $user->email)->first();
            return $profesor && $this->profesorTeachesMateria($profesor, $calificacion->inscripcion->materia_id);
        }

        // Administrativos y superiores pueden actualizar cualquiera
        return $user->hasRole('administrativo');
    }

    /**
     * Eliminar calificación
     */
    public function delete(User $user, Calificacion $calificacion): bool
    {
        // Solo administrativos y superiores
        return $user->hasRole('administrativo');
    }

    /**
     * Verifica si un profesor enseña una materia
     */
    private function profesorTeachesMateria($profesor, int $materiaId): bool
    {
        return \App\Models\Horario::where('profesor_id', $profesor->id)
            ->where('materia_id', $materiaId)
            ->exists();
    }
}
