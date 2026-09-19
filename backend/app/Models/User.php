<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Traits\HasRoleAuthorization;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoleAuthorization;

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password'          => 'hashed',
        ];
    }

    // Relaciones
    public function estudiante()
    {
        return $this->hasOne(Estudiante::class, 'user_id');
    }

    public function profesor()
    {
        return $this->hasOne(Profesor::class, 'user_id');
    }

    // ═══════════════════════════════════════════════════════════════════════
    // MÉTODOS DE ACCESO RÁPIDO (delegados al trait)
    // ═══════════════════════════════════════════════════════════════════════

    /**
     * Verifica si el usuario puede crear quizzes
     */
    public function canCreateQuizzes(): bool
    {
        return $this->isProfesor();
    }

    /**
     * Verifica si el usuario puede ver auditoría de estudiantes
     */
    public function canViewAuditoria(): bool
    {
        return $this->isProfesor();
    }

    /**
     * Verifica si el usuario puede gestionar tareas
     */
    public function canManageAssignments(): bool
    {
        return $this->isProfesor();
    }

    /**
     * Verifica si el usuario puede tomar exámenes
     */
    public function canTakeQuizzes(): bool
    {
        return $this->isEstudiante() && $this->isEstudianteActivo();
    }

    /**
     * Verifica si el usuario puede marcar asistencia
     */
    public function canMarkAttendance(): bool
    {
        return $this->isEstudiante() && $this->isEstudianteActivo();
    }

    /**
     * Verifica si el usuario puede gestionar asistencia
     */
    public function canManageAttendance(): bool
    {
        return $this->isProfesor();
    }

    /**
     * Verifica si el usuario puede ver todas las notificaciones del sistema
     */
    public function canViewAllNotifications(): bool
    {
        return $this->hasRole('soporte_it');
    }

    /**
     * Obtiene el dashboard route del usuario según su rol
     */
    public function getDashboardRoute(): string
    {
        $routes = [
            'desarrollador' => '/dev/dashboard',
            'soporte_it' => '/soporte/dashboard',
            'administrativo' => '/admin/dashboard',
            'profesor' => '/profesor/dashboard',
            'estudiante' => '/estudiante/dashboard',
        ];

        return $routes[$this->role] ?? '/';
    }
}
