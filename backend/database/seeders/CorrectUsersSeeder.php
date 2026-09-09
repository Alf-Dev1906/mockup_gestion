<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Estudiante;
use App\Models\Profesor;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class CorrectUsersSeeder extends Seeder
{
    /**
     * Crea usuarios con las credenciales CORRECTAS especificadas
     */
    public function run(): void
    {
        echo "\n";
        echo "╔═══════════════════════════════════════════════════════════╗\n";
        echo "║   CREANDO USUARIOS CON CREDENCIALES CORRECTAS            ║\n";
        echo "╚═══════════════════════════════════════════════════════════╝\n";
        echo "\n";

        // NIVEL 5 - DESARROLLADOR
        User::updateOrCreate(
            ['email' => 'developer@universidad.edu.ve'],
            [
                'name' => 'Carlos Desarrollador',
                'password' => Hash::make('Dev2026!'),
                'email_verified_at' => now(),
                'role' => 'desarrollador',
            ]
        );
        echo "✅ Desarrollador creado: developer@universidad.edu.ve / Dev2026!\n";

        // NIVEL 4 - SOPORTE IT
        User::updateOrCreate(
            ['email' => 'soporte@universidad.edu.ve'],
            [
                'name' => 'Ana Soporte',
                'password' => Hash::make('Soporte2026!'),
                'email_verified_at' => now(),
                'role' => 'soporte_it',
            ]
        );
        echo "✅ Soporte IT creado: soporte@universidad.edu.ve / Soporte2026!\n";

        // NIVEL 3 - ADMINISTRATIVO
        User::updateOrCreate(
            ['email' => 'admin@universidad.edu.ve'],
            [
                'name' => 'María Administrativa',
                'password' => Hash::make('Admin2026!'),
                'email_verified_at' => now(),
                'role' => 'administrativo',
            ]
        );
        echo "✅ Administrativo creado: admin@universidad.edu.ve / Admin2026!\n";

        // NIVEL 2 - PROFESOR
        $profesor = User::updateOrCreate(
            ['email' => 'profesor@universidad.edu.ve'],
            [
                'name' => 'Dr. Juan Profesor',
                'password' => Hash::make('Profesor2026!'),
                'email_verified_at' => now(),
                'role' => 'profesor',
            ]
        );
        echo "✅ Profesor creado: profesor@universidad.edu.ve / Profesor2026!\n";

        // NIVEL 1A - ESTUDIANTE ACTIVO
        $estudiante = User::updateOrCreate(
            ['email' => 'estudiante@universidad.edu.ve'],
            [
                'name' => 'Pedro Estudiante',
                'password' => Hash::make('Estudiante2026!'),
                'email_verified_at' => now(),
                'role' => 'estudiante',
            ]
        );
        echo "✅ Estudiante ACTIVO creado: estudiante@universidad.edu.ve / Estudiante2026!\n";

        // NIVEL 1B - ESTUDIANTE SOLICITANTE
        $solicitante = User::updateOrCreate(
            ['email' => 'solicitante@universidad.edu.ve'],
            [
                'name' => 'Laura Solicitante',
                'password' => Hash::make('Solicitante2026!'),
                'email_verified_at' => now(),
                'role' => 'estudiante',
            ]
        );
        echo "✅ Estudiante SOLICITANTE creado: solicitante@universidad.edu.ve / Solicitante2026!\n";

        echo "\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "  CREDENCIALES DE ACCESO\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "  [NIVEL 5] developer@universidad.edu.ve    / Dev2026!\n";
        echo "  [NIVEL 4] soporte@universidad.edu.ve      / Soporte2026!\n";
        echo "  [NIVEL 3] admin@universidad.edu.ve        / Admin2026!\n";
        echo "  [NIVEL 2] profesor@universidad.edu.ve     / Profesor2026!\n";
        echo "  [NIVEL 1] estudiante@universidad.edu.ve   / Estudiante2026!\n";
        echo "  [NIVEL 1] solicitante@universidad.edu.ve  / Solicitante2026!\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "\n";
        echo "✅ Todos los usuarios creados exitosamente!\n";
        echo "\n";
    }
}
