<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Estudiante;
use App\Models\Profesor;
use App\Models\Facultad;
use App\Models\Carrera;
use App\Models\SolicitudAdmision;

class UsuariosPruebaSeeder extends Seeder
{
    public function run(): void
    {
        echo "🚀 Creando usuarios de prueba...\n\n";

        // 1. Crear datos base necesarios
        $facultad = Facultad::firstOrCreate(
            ['codigo' => 'ING'],
            ['nombre' => 'Facultad de Ingeniería']
        );

        $carrera = Carrera::firstOrCreate(
            ['codigo' => 'ISIST'],
            [
                'facultad_id' => $facultad->id,
                'nombre' => 'Ingeniería de Sistemas',
                'duracion_semestres' => 10,
                'nivel' => 'licenciatura',
                'titulo_otorgado' => 'Ingeniero de Sistemas',
            ]
        );

        echo "✓ Facultad y carrera verificadas\n";

        // 2. Admin (ya existe del user seeder base, solo actualizamos password)
        $admin = User::find(1);
        if ($admin) {
            $admin->update([
                'password' => Hash::make('Admin2026!'),
                'role' => 'administrativo',
            ]);
            echo "✓ Admin actualizado: admin@universidad.edu.ve / Admin2026!\n";
        }

        // 3. Desarrollador
        $dev = User::updateOrCreate(
            ['email' => 'developer@universidad.edu.ve'],
            [
                'name' => 'Desarrollador Sistema',
                'email_verified_at' => now(),
                'password' => Hash::make('Dev2026!'),
                'role' => 'desarrollador',
            ]
        );
        echo "✓ Desarrollador creado: developer@universidad.edu.ve / Dev2026!\n";

        // 4. Soporte IT
        $soporte = User::updateOrCreate(
            ['email' => 'soporte@universidad.edu.ve'],
            [
                'name' => 'Soporte TI',
                'email_verified_at' => now(),
                'password' => Hash::make('Soporte2026!'),
                'role' => 'soporte_it',
            ]
        );
        echo "✓ Soporte IT creado: soporte@universidad.edu.ve / Soporte2026!\n";

        // 5. Profesor
        $userProfesor = User::updateOrCreate(
            ['email' => 'profesor@universidad.edu.ve'],
            [
                'name' => 'Prof. Juan Pérez',
                'email_verified_at' => now(),
                'password' => Hash::make('Profesor2026!'),
                'role' => 'profesor',
            ]
        );

        Profesor::updateOrCreate(
            ['user_id' => $userProfesor->id],
            [
                'cedula' => 'V-12345678',
                'nombre' => 'Juan',
                'apellido' => 'Pérez',
                'email' => 'profesor@universidad.edu.ve',
                'facultad_id' => $facultad->id,
                'especialidad' => 'Ingeniería de Sistemas',
                'estatus' => 'activo',
            ]
        );
        echo "✓ Profesor creado: profesor@universidad.edu.ve / Profesor2026!\n";

        // 6. Estudiante activo
        $userEstudiante = User::updateOrCreate(
            ['email' => 'estudiante@universidad.edu.ve'],
            [
                'name' => 'María González',
                'email_verified_at' => now(),
                'password' => Hash::make('Estudiante2026!'),
                'role' => 'estudiante',
            ]
        );

        Estudiante::updateOrCreate(
            ['user_id' => $userEstudiante->id],
            [
                'cedula' => 'V-23456789',
                'nombre' => 'María',
                'apellido' => 'González',
                'carrera_id' => $carrera->id,
                'semestre_actual' => 3,
                'estatus' => 'activo',
            ]
        );
        echo "✓ Estudiante creado: estudiante@universidad.edu.ve / Estudiante2026!\n";

        // 7. Solicitante (estudiante inactivo con solicitud)
        $userSolicitante = User::updateOrCreate(
            ['email' => 'solicitante@universidad.edu.ve'],
            [
                'name' => 'Carlos Ramírez',
                'email_verified_at' => now(),
                'password' => Hash::make('Solicitante2026!'),
                'role' => 'estudiante',
            ]
        );

        $estudianteSolicitante = Estudiante::updateOrCreate(
            ['user_id' => $userSolicitante->id],
            [
                'cedula' => 'V-34567890',
                'nombre' => 'Carlos',
                'apellido' => 'Ramírez',
                'carrera_id' => null,
                'semestre_actual' => 1,
                'estatus' => 'inactivo',
            ]
        );

        SolicitudAdmision::updateOrCreate(
            ['estudiante_id' => $estudianteSolicitante->id],
            [
                'numero_referencia' => 'SOL-2026-000103',
                'estado' => 'pendiente',
                'paso1_completado' => true,
                'paso2_completado' => true,
                'paso3_completado' => true,
                'paso4_completado' => true,
                'paso5_completado' => true,
                'nombres' => 'Carlos',
                'apellidos' => 'Ramírez',
                'cedula' => 'V-34567890',
                'fecha_nacimiento' => '2000-01-15',
                'genero' => 'M',
                'nacionalidad' => 'V',
                'email_personal' => 'solicitante@universidad.edu.ve',
                'telefono_movil' => '04241234567',
            ]
        );
        echo "✓ Solicitante creado: solicitante@universidad.edu.ve / Solicitante2026!\n";

        echo "\n✅ Usuarios de prueba creados exitosamente\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "Puedes iniciar sesión con:\n";
        echo "  - admin@universidad.edu.ve / Admin2026!\n";
        echo "  - developer@universidad.edu.ve / Dev2026!\n";
        echo "  - soporte@universidad.edu.ve / Soporte2026!\n";
        echo "  - profesor@universidad.edu.ve / Profesor2026!\n";
        echo "  - estudiante@universidad.edu.ve / Estudiante2026!\n";
        echo "  - solicitante@universidad.edu.ve / Solicitante2026!\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
    }
}
