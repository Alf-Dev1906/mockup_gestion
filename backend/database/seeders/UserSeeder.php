<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        echo "Creando 6 usuarios del sistema...\n";

        $users = [
            [
                'name' => 'Desarrollador Sistema',
                'email' => 'developer@universidad.edu.ve',
                'password' => Hash::make('Dev2026!'),
                'role' => 'desarrollador',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Soporte Técnico',
                'email' => 'soporte@universidad.edu.ve',
                'password' => Hash::make('Soporte2026!'),
                'role' => 'soporte_it',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Administrador SGE',
                'email' => 'admin@universidad.edu.ve',
                'password' => Hash::make('Admin2026!'),
                'role' => 'administrativo',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Profesor Demo',
                'email' => 'profesor@universidad.edu.ve',
                'password' => Hash::make('Admin2026!'),
                'role' => 'profesor',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Estudiante Demo',
                'email' => 'estudiante@universidad.edu.ve',
                'password' => Hash::make('Admin2026!'),
                'role' => 'estudiante',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Solicitante Demo',
                'email' => 'solicitante@universidad.edu.ve',
                'password' => Hash::make('Admin2026!'),
                'role' => 'estudiante',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        echo "✅ 6 usuarios creados exitosamente.\n";
        echo "\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "  CREDENCIALES DE ACCESO\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "  1. DESARROLLADOR (NIVEL 5)\n";
        echo "     Email:    developer@universidad.edu.ve\n";
        echo "     Password: Dev2026!\n";
        echo "\n";
        echo "  2. SOPORTE IT (NIVEL 4)\n";
        echo "     Email:    soporte@universidad.edu.ve\n";
        echo "     Password: Soporte2026!\n";
        echo "\n";
        echo "  3. ADMINISTRATIVO (NIVEL 3)\n";
        echo "     Email:    admin@universidad.edu.ve\n";
        echo "     Password: Admin2026!\n";
        echo "\n";
        echo "  4. PROFESOR (NIVEL 2)\n";
        echo "     Email:    profesor@universidad.edu.ve\n";
        echo "     Password: Admin2026!\n";
        echo "\n";
        echo "  5. ESTUDIANTE ACTIVO (NIVEL 1A)\n";
        echo "     Email:    estudiante@universidad.edu.ve\n";
        echo "     Password: Admin2026!\n";
        echo "\n";
        echo "  6. SOLICITANTE (NIVEL 1B)\n";
        echo "     Email:    solicitante@universidad.edu.ve\n";
        echo "     Password: Admin2026!\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "\n";
    }
}
