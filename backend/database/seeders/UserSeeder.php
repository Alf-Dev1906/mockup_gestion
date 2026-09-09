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
        echo "Creando usuario administrador...\n";

        // Usuario Admin con privilegios completos
        User::updateOrCreate(
            ['email' => 'admin@universidad.edu.ve'],
            [
                'name' => 'Administrador SGE',
                'password' => Hash::make('Admin123!'),
                'email_verified_at' => now(),
                'role' => 'administrativo',
            ]
        );

        echo "✅ Usuario administrador creado exitosamente.\n";
        echo "\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "  CREDENCIALES DE ACCESO\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "  Email:    admin@universidad.edu.ve\n";
        echo "  Password: Admin123!\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "\n";
    }
}
