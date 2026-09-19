<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Gate;
use App\Models\User;
use App\Models\Estudiante;
use App\Models\Calificacion;
use App\Policies\UserPolicy;
use App\Policies\EstudiantePolicy;
use App\Policies\CalificacionPolicy;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Mapeo de modelos a policies
     */
    protected $policies = [
        User::class => UserPolicy::class,
        Estudiante::class => EstudiantePolicy::class,
        Calificacion::class => CalificacionPolicy::class,
    ];

    public function register(): void
    {
        //
    }

    public function boot(): void
    {
        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?: $request->ip());
        });

        // Registrar policies
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }

        // Gates personalizados adicionales
        $this->registerCustomGates();
    }

    /**
     * Registra Gates personalizados para permisos específicos
     */
    protected function registerCustomGates(): void
    {
        // Gate para aprobar solicitudes de admisión
        Gate::define('aprobar-solicitud-admision', function (User $user) {
            return $user->hasRole('administrativo');
        });

        // Gate para gestionar backups
        Gate::define('gestionar-backups', function (User $user) {
            return $user->hasRole('soporte_it') || $user->isDesarrollador();
        });

        // Gate para acceder a configuraciones críticas
        Gate::define('acceso-configuracion-critica', function (User $user) {
            return $user->isDesarrollador();
        });

        // Gate para ver logs del sistema
        Gate::define('ver-logs-sistema', function (User $user) {
            return $user->hasRole('soporte_it') || $user->isDesarrollador();
        });

        // Gate para gestionar usuarios
        Gate::define('gestionar-usuarios', function (User $user) {
            return $user->hasRole('soporte_it') || $user->isDesarrollador();
        });

        // Gate para ejecutar comandos del sistema
        Gate::define('ejecutar-comandos-sistema', function (User $user) {
            return $user->isDesarrollador();
        });

        // Gate para acceder a la base de datos directamente
        Gate::define('acceso-base-datos', function (User $user) {
            return $user->isDesarrollador();
        });

        // Gate para deploy y actualizaciones
        Gate::define('deploy-actualizaciones', function (User $user) {
            return $user->isDesarrollador();
        });

        // Gate para gestionar pagos
        Gate::define('gestionar-pagos', function (User $user) {
            return $user->hasRole('administrativo');
        });

        // Gate para ver reportes académicos
        Gate::define('ver-reportes-academicos', function (User $user) {
            return $user->hasRole('administrativo');
        });
    }
}

