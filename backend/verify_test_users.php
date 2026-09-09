<?php
/**
 * Script para verificar los usuarios de prueba
 * Ejecutar con: php backend/verify_test_users.php
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

$testUsers = [
    ['email' => 'developer@universidad.edu.ve', 'password' => 'Dev2026!', 'role' => 'desarrollador'],
    ['email' => 'soporte@universidad.edu.ve', 'password' => 'Soporte2026!', 'role' => 'soporte_it'],
    ['email' => 'admin@universidad.edu.ve', 'password' => 'Admin2026!', 'role' => 'administrativo'],
    ['email' => 'profesor@universidad.edu.ve', 'password' => 'Profesor2026!', 'role' => 'profesor'],
    ['email' => 'estudiante@universidad.edu.ve', 'password' => 'Estudiante2026!', 'role' => 'estudiante'],
    ['email' => 'solicitante@universidad.edu.ve', 'password' => 'Solicitante2026!', 'role' => 'estudiante'],
];

echo "═══════════════════════════════════════════════════════════\n";
echo "  VERIFICACIÓN DE USUARIOS DE PRUEBA\n";
echo "═══════════════════════════════════════════════════════════\n\n";

foreach ($testUsers as $testUser) {
    echo "Verificando: {$testUser['email']}\n";
    echo str_repeat("─", 60) . "\n";
    
    $user = User::where('email', $testUser['email'])->first();
    
    if (!$user) {
        echo "❌ Usuario NO EXISTE en la base de datos\n";
        echo "   Necesitas crear este usuario con el seeder\n\n";
        continue;
    }
    
    echo "✅ Usuario existe\n";
    echo "   ID: {$user->id}\n";
    echo "   Nombre: {$user->name}\n";
    echo "   Role: {$user->role}\n";
    echo "   Email verificado: " . ($user->email_verified_at ? 'Sí' : 'No') . "\n";
    
    // Verificar password
    if (Hash::check($testUser['password'], $user->password)) {
        echo "✅ Password CORRECTO: {$testUser['password']}\n";
    } else {
        echo "❌ Password INCORRECTO\n";
        echo "   Se esperaba: {$testUser['password']}\n";
        echo "   Hash en BD: " . substr($user->password, 0, 30) . "...\n";
    }
    
    echo "\n";
}

echo "═══════════════════════════════════════════════════════════\n";
echo "  FIN DE LA VERIFICACIÓN\n";
echo "═══════════════════════════════════════════════════════════\n";
