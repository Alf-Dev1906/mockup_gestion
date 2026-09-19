<?php
/**
 * SCRIPT TEMPORAL PARA EJECUTAR MIGRACIONES Y SEEDERS
 * ⚠️ BORRAR DESPUÉS DE TERMINAR EL SETUP
 */

// Cargar Laravel
require __DIR__.'/../vendor/autoload.php';
$app = require_once __DIR__.'/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

// Headers para JSON
header('Content-Type: application/json');

// Obtener la acción
$action = $_GET['action'] ?? 'menu';
$seeder = $_GET['seeder'] ?? '';

try {
    switch ($action) {
        case 'migrate':
            ob_start();
            Artisan::call('config:clear');
            Artisan::call('migrate', ['--force' => true]);
            $output = ob_get_clean();
            
            echo json_encode([
                'success' => true,
                'message' => 'Migraciones ejecutadas',
                'output' => Artisan::output()
            ], JSON_PRETTY_PRINT);
            break;
            
        case 'seed':
            $allowedSeeders = [
                'UserSeeder',
                'DemoSeeder',
                'DemoLightSeeder',  // Nueva versión ligera
                'LinkDemoUsersSeeder',
                'SolicitudesYPagosSeeder',
                'PagosMockupSeeder',
                'CalificacionesMockupSeeder',
                'ProfesorDemoDataSeeder',
                'EstudianteDemoDataSeeder'
            ];
            
            if (!in_array($seeder, $allowedSeeders)) {
                throw new Exception('Seeder no permitido');
            }
            
            set_time_limit(900); // 15 minutos
            ini_set('memory_limit', '512M');
            
            ob_start();
            Artisan::call('db:seed', [
                '--class' => $seeder,
                '--force' => true
            ]);
            $output = ob_get_clean();
            
            echo json_encode([
                'success' => true,
                'message' => "Seeder {$seeder} ejecutado",
                'output' => Artisan::output()
            ], JSON_PRETTY_PRINT);
            break;
            
        case 'verify':
            $stats = [
                'users' => \App\Models\User::count(),
                'estudiantes' => \App\Models\Estudiante::count(),
                'profesores' => \App\Models\Profesor::count(),
                'inscripciones' => \App\Models\Inscripcion::count(),
                'horarios' => \App\Models\Horario::count(),
                'facultades' => \App\Models\Facultad::count(),
                'carreras' => \App\Models\Carrera::count(),
            ];
            
            echo json_encode([
                'success' => true,
                'stats' => $stats
            ], JSON_PRETTY_PRINT);
            break;
            
        case 'menu':
        default:
            echo json_encode([
                'message' => 'Script de Setup para Render',
                'endpoints' => [
                    'migrate' => '?action=migrate',
                    'seed' => '?action=seed&seeder=UserSeeder',
                    'verify' => '?action=verify',
                ],
                'seeders' => [
                    'UserSeeder',
                    'DemoSeeder',
                    'LinkDemoUsersSeeder',
                    'SolicitudesYPagosSeeder',
                    'PagosMockupSeeder',
                    'CalificacionesMockupSeeder',
                    'ProfesorDemoDataSeeder',
                    'EstudianteDemoDataSeeder'
                ]
            ], JSON_PRETTY_PRINT);
            break;
    }
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode([
        'success' => false,
        'error' => $e->getMessage(),
        'trace' => $e->getTraceAsString()
    ], JSON_PRETTY_PRINT);
}
