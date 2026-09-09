<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Estudiante;
use App\Models\Profesor;

class TestUsersSeeder extends Seeder
{
    /**
     * Crear usuarios de prueba para el sistema
     */
    public function run(): void
    {
        $this->command->info('🔄 Creando usuarios de prueba...');

        // Verificar que exista al menos una carrera y facultad
        $primeraCarrera = DB::table('carreras')->first();
        $primeraFacultad = DB::table('facultades')->first();
        
        if (!$primeraCarrera || !$primeraFacultad) {
            $this->command->error('❌ Error: Necesitas tener al menos una facultad y una carrera en la base de datos.');
            $this->command->line('   Ejecuta primero: php artisan db:seed --class=DatabaseSeeder');
            return;
        }

        // NIVEL 5 - DESARROLLADOR
        $this->createUser(
            'developer@universidad.edu.ve',
            'Desarrollador Principal',
            'Dev2026!',
            'desarrollador'
        );

        // NIVEL 4 - SOPORTE IT
        $this->createUser(
            'soporte@universidad.edu.ve',
            'Soporte IT',
            'Soporte2026!',
            'soporte_it'
        );

        // NIVEL 3 - ADMINISTRATIVO
        $this->createUser(
            'admin@universidad.edu.ve',
            'Administrador Académico',
            'Admin2026!',
            'administrativo'
        );

        // NIVEL 2 - PROFESOR
        $profesorUser = $this->createUser(
            'profesor@universidad.edu.ve',
            'Profesor de Prueba',
            'Profesor2026!',
            'profesor'
        );

        // Crear el registro de profesor si no existe
        if ($profesorUser && !Profesor::where('email', $profesorUser->email)->exists()) {
            Profesor::create([
                'facultad_id' => $primeraFacultad->id,
                'cedula' => 'V-12345678',
                'nombre' => 'Profesor',
                'apellido' => 'De Prueba',
                'email' => $profesorUser->email,
                'telefono' => '0212-1234567',
                'fecha_nacimiento' => '1980-01-01',
                'genero' => 'M',
                'especialidad' => 'Ingeniería de Sistemas',
                'fecha_contratacion' => now(),
                'estatus' => 'activo',
            ]);
            $this->command->info('   ✅ Registro de profesor creado');
        }

        // NIVEL 1A - ESTUDIANTE ACTIVO
        $estudianteUser = $this->createUser(
            'estudiante@universidad.edu.ve',
            'Estudiante Activo',
            'Estudiante2026!',
            'estudiante'
        );

        // Crear el registro de estudiante activo si no existe
        if ($estudianteUser && !Estudiante::where('email', $estudianteUser->email)->exists()) {
            Estudiante::create([
                'cedula' => 'V-20000001',
                'nombre' => 'Estudiante',
                'apellido' => 'Activo',
                'email' => $estudianteUser->email,
                'telefono' => '0424-1234567',
                'fecha_nacimiento' => '2000-01-01',
                'genero' => 'M',
                'direccion' => 'Caracas, Venezuela',
                'ciudad' => 'Caracas',
                'estado' => 'Distrito Capital',
                'matricula' => '2024-000001',
                'fecha_ingreso' => now()->subYear(),
                'carrera_id' => $primeraCarrera->id,
                'semestre_actual' => 3,
                'indice_academico' => 16.50,
                'creditos_aprobados' => 45,
                'estatus' => 'activo',
            ]);
            $this->command->info('   ✅ Registro de estudiante activo creado');
        }

        // NIVEL 1B - ESTUDIANTE SOLICITANTE
        $solicitanteUser = $this->createUser(
            'solicitante@universidad.edu.ve',
            'Estudiante Solicitante',
            'Solicitante2026!',
            'estudiante'
        );

        // Crear el registro de estudiante solicitante si no existe
        if ($solicitanteUser && !Estudiante::where('email', $solicitanteUser->email)->exists()) {
            Estudiante::create([
                'cedula' => 'V-20000002',
                'nombre' => 'Estudiante',
                'apellido' => 'Solicitante',
                'email' => $solicitanteUser->email,
                'telefono' => '0424-7654321',
                'fecha_nacimiento' => '2001-01-01',
                'genero' => 'F',
                'direccion' => 'Caracas, Venezuela',
                'ciudad' => 'Caracas',
                'estado' => 'Distrito Capital',
                'matricula' => 'TEMP-2026-000001',
                'fecha_ingreso' => now(),
                'carrera_id' => $primeraCarrera->id,
                'semestre_actual' => 1,
                'estatus' => 'inactivo', // Inactivo porque aún no ha sido aprobado
            ]);
            $this->command->info('   ✅ Registro de estudiante solicitante creado');
        }

        $this->command->info('');
        $this->command->info('✨ ¡Usuarios de prueba creados exitosamente!');
        $this->command->line('');
        $this->command->line('📧 Puedes iniciar sesión con:');
        $this->command->line('   developer@universidad.edu.ve / Dev2026!');
        $this->command->line('   admin@universidad.edu.ve / Admin2026!');
        $this->command->line('   estudiante@universidad.edu.ve / Estudiante2026!');
        $this->command->line('');
    }

    /**
     * Crear o actualizar un usuario
     */
    private function createUser(string $email, string $name, string $password, string $role): ?User
    {
        $user = User::where('email', $email)->first();

        if ($user) {
            // Actualizar password si ya existe
            $user->update([
                'name' => $name,
                'password' => Hash::make($password),
                'role' => $role,
                'email_verified_at' => now(),
            ]);
            $this->command->warn("⚠️  Usuario {$email} ya existía - password actualizado");
        } else {
            // Crear nuevo usuario
            $user = User::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'role' => $role,
                'email_verified_at' => now(),
            ]);
            $this->command->info("✅ Usuario creado: {$email}");
        }

        return $user;
    }
}
