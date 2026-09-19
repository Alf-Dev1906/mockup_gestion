<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Artisan;

/**
 * RUTAS TEMPORALES PARA SETUP EN RENDER
 * ⚠️ BORRAR DESPUÉS DE EJECUTAR MIGRACIONES Y SEEDERS
 */

Route::get('/setup/migrate', function () {
    try {
        Artisan::call('config:clear');
        Artisan::call('migrate', ['--force' => true]);
        
        return response()->json([
            'success' => true,
            'message' => 'Migraciones ejecutadas exitosamente',
            'output' => Artisan::output()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});

Route::get('/setup/seed/{seeder}', function ($seeder) {
    $allowedSeeders = [
        'UserSeeder',
        'DemoSeeder',
        'LinkDemoUsersSeeder',
        'SolicitudesYPagosSeeder',
        'PagosMockupSeeder',
        'CalificacionesMockupSeeder',
        'ProfesorDemoDataSeeder',
        'EstudianteDemoDataSeeder'
    ];

    if (!in_array($seeder, $allowedSeeders)) {
        return response()->json([
            'success' => false,
            'error' => 'Seeder no permitido'
        ], 400);
    }

    try {
        set_time_limit(900); // 15 minutos para DemoSeeder
        
        Artisan::call('db:seed', [
            '--class' => $seeder,
            '--force' => true
        ]);
        
        return response()->json([
            'success' => true,
            'message' => "Seeder {$seeder} ejecutado exitosamente",
            'output' => Artisan::output()
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ], 500);
    }
});

Route::get('/setup/verify', function () {
    try {
        $stats = [
            'users' => \App\Models\User::count(),
            'estudiantes' => \App\Models\Estudiante::count(),
            'profesores' => \App\Models\Profesor::count(),
            'inscripciones' => \App\Models\Inscripcion::count(),
            'horarios' => \App\Models\Horario::count(),
        ];
        
        return response()->json([
            'success' => true,
            'stats' => $stats
        ]);
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'error' => $e->getMessage()
        ], 500);
    }
});
