<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use App\Models\Estudiante;
use App\Models\Profesor;

class RolesUsersSeeder extends Seeder
{
    public function run(): void
    {
        echo "\n╔══════════════════════════════════════════════════════════════╗\n";
        echo "║   CREANDO USUARIOS DE PRUEBA PARA CADA ROL                  ║\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n\n";

        $credenciales = [];

        // ════════════════════════════════════════════════════════════
        // NIVEL 5 - DESARROLLADOR (Tú)
        // ════════════════════════════════════════════════════════════
        $dev = User::updateOrCreate(
            ['email' => 'developer@universidad.edu.ve'],
            [
                'name' => 'Daniel Desarrollador',
                'password' => Hash::make('Dev2026!'),
                'role' => 'desarrollador',
                'email_verified_at' => now(),
            ]
        );
        $credenciales[] = [
            'Nivel' => '5 - DESARROLLADOR',
            'Nombre' => 'Daniel Desarrollador',
            'Email' => 'developer@universidad.edu.ve',
            'Password' => 'Dev2026!',
            'Rol' => 'desarrollador',
            'Permisos' => 'CONTROL TOTAL: BD, código, deploy, backups, configuraciones críticas'
        ];

        // ════════════════════════════════════════════════════════════
        // NIVEL 4 - SOPORTE IT
        // ════════════════════════════════════════════════════════════
        $soporte = User::updateOrCreate(
            ['email' => 'soporte@universidad.edu.ve'],
            [
                'name' => 'Carlos Técnico',
                'password' => Hash::make('Soporte2026!'),
                'role' => 'soporte_it',
                'email_verified_at' => now(),
            ]
        );
        $credenciales[] = [
            'Nivel' => '4 - SOPORTE IT',
            'Nombre' => 'Carlos Técnico',
            'Email' => 'soporte@universidad.edu.ve',
            'Password' => 'Soporte2026!',
            'Rol' => 'soporte_it',
            'Permisos' => 'Gestión usuarios, logs, respaldos, tickets, resetear passwords'
        ];

        // ════════════════════════════════════════════════════════════
        // NIVEL 3 - ADMINISTRATIVO
        // ════════════════════════════════════════════════════════════
        $admin = User::updateOrCreate(
            ['email' => 'admin@universidad.edu.ve'],
            [
                'name' => 'María Administrativa',
                'password' => Hash::make('Admin2026!'),
                'role' => 'administrativo',
                'email_verified_at' => now(),
            ]
        );
        $credenciales[] = [
            'Nivel' => '3 - ADMINISTRATIVO',
            'Nombre' => 'María Administrativa',
            'Email' => 'admin@universidad.edu.ve',
            'Password' => 'Admin2026!',
            'Rol' => 'administrativo',
            'Permisos' => 'Aprobar solicitudes, gestión académica, pagos, reportes'
        ];

        // ════════════════════════════════════════════════════════════
        // NIVEL 2 - PROFESOR
        // ════════════════════════════════════════════════════════════
        // Buscar un profesor existente o crear uno nuevo
        $profesorData = Profesor::first();
        
        if (!$profesorData) {
            // Crear un profesor si no existe ninguno
            $profesorData = Profesor::create([
                'cedula' => 'V-12345678',
                'nombre' => 'Juan',
                'apellido' => 'Profesor',
                'email' => 'profesor@universidad.edu.ve',
                'telefono' => '0424-1234567',
                'especialidad' => 'Ingeniería de Software',
                'titulo' => 'Magister en Computación',
                'fecha_ingreso' => now()->subYears(5),
                'estatus' => 'activo',
            ]);
        }

        $profesor = User::updateOrCreate(
            ['email' => 'profesor@universidad.edu.ve'],
            [
                'name' => 'Juan Profesor',
                'password' => Hash::make('Profesor2026!'),
                'role' => 'profesor',
                'email_verified_at' => now(),
            ]
        );

        // Vincular profesor con user
        if ($profesorData && !$profesorData->user_id) {
            $profesorData->update(['user_id' => $profesor->id]);
        }

        $credenciales[] = [
            'Nivel' => '2 - PROFESOR',
            'Nombre' => 'Juan Profesor',
            'Email' => 'profesor@universidad.edu.ve',
            'Password' => 'Profesor2026!',
            'Rol' => 'profesor',
            'Permisos' => 'Ver sus materias, gestionar calificaciones, ver estudiantes'
        ];

        // ════════════════════════════════════════════════════════════
        // NIVEL 1 - ESTUDIANTE (Activo)
        // ════════════════════════════════════════════════════════════
        $carrera = DB::table('carreras')->first();
        
        if (!$carrera) {
            echo "❌ No hay carreras en la base de datos. Por favor ejecuta los seeders de carreras primero.\n";
            return;
        }

        $estudianteActivo = User::updateOrCreate(
            ['email' => 'estudiante@universidad.edu.ve'],
            [
                'name' => 'Ana Estudiante',
                'password' => Hash::make('Estudiante2026!'),
                'role' => 'estudiante',
                'email_verified_at' => now(),
            ]
        );

        // Buscar o crear estudiante
        $estudianteData = Estudiante::where('email', 'estudiante@universidad.edu.ve')->first();
        
        if (!$estudianteData) {
            $estudianteData = Estudiante::create([
                'user_id' => $estudianteActivo->id,
                'carrera_id' => $carrera->id,
                'cedula' => 'V-87654321',
                'nombre' => 'Ana',
                'apellido' => 'Estudiante',
                'email' => 'estudiante@universidad.edu.ve',
                'telefono' => '0424-7654321',
                'fecha_nacimiento' => '2005-03-15',
                'genero' => 'F',
                'direccion' => 'Av. Principal, Los Ruices',
                'ciudad' => 'Caracas',
                'estado' => 'Distrito Capital',
                'matricula' => 'E-2024-0001',
                'fecha_ingreso' => now()->subYear(),
                'semestre_actual' => 2,
                'estatus' => 'activo',
            ]);
        } else {
            $estudianteData->update(['user_id' => $estudianteActivo->id]);
        }

        $credenciales[] = [
            'Nivel' => '1A - ESTUDIANTE (ACTIVO)',
            'Nombre' => 'Ana Estudiante',
            'Email' => 'estudiante@universidad.edu.ve',
            'Password' => 'Estudiante2026!',
            'Rol' => 'estudiante',
            'Permisos' => 'Inscribirse, ver horarios, ver calificaciones, historial'
        ];

        // ════════════════════════════════════════════════════════════
        // NIVEL 1 - ESTUDIANTE (Solicitante)
        // ════════════════════════════════════════════════════════════
        $estudianteSolicitante = User::updateOrCreate(
            ['email' => 'solicitante@universidad.edu.ve'],
            [
                'name' => 'Pedro Solicitante',
                'password' => Hash::make('Solicitante2026!'),
                'role' => 'estudiante',
                'email_verified_at' => now(),
            ]
        );

        $solicitanteData = Estudiante::updateOrCreate(
            ['email' => 'solicitante@universidad.edu.ve'],
            [
                'user_id' => $estudianteSolicitante->id,
                'carrera_id' => $carrera->id,
                'cedula' => 'V-11223344',
                'nombre' => 'Pedro',
                'apellido' => 'Solicitante',
                'telefono' => '0424-1122334',
                'fecha_nacimiento' => '2006-05-20',
                'genero' => 'M',
                'direccion' => 'Calle Falsa 123',
                'ciudad' => 'Caracas',
                'estado' => 'Distrito Capital',
                'matricula' => 'S-2026-0001',
                'fecha_ingreso' => now(),
                'semestre_actual' => 1,
                'estatus' => 'solicitante', // ← Estado de solicitante
            ]
        );

        // Crear solicitud de admisión
        DB::table('solicitudes_admision')->updateOrInsert(
            ['user_id' => $estudianteSolicitante->id],
            [
                'numero_solicitud' => 'SOL-2026-0001',
                'estudiante_id' => $solicitanteData->id,
                'datos_personales' => json_encode([
                    'nombre' => 'Pedro',
                    'apellido' => 'Solicitante',
                    'cedula' => 'V-11223344',
                    'fecha_nacimiento' => '2006-05-20',
                    'genero' => 'M',
                ]),
                'datos_contacto' => json_encode([
                    'email' => 'solicitante@universidad.edu.ve',
                    'telefono' => '0424-1122334',
                    'direccion' => 'Calle Falsa 123',
                    'ciudad' => 'Caracas',
                    'estado' => 'Distrito Capital',
                ]),
                'datos_academicos' => json_encode([
                    'carrera_id' => $carrera->id,
                    'carrera' => $carrera->nombre ?? 'N/A',
                    'nivel_previo' => 'Bachiller',
                ]),
                'contacto_emergencia' => json_encode([
                    'nombre' => 'Rosa Solicitante',
                    'telefono' => '0414-5566778',
                    'relacion' => 'Madre',
                ]),
                'estatus' => 'pendiente',
                'created_at' => now(),
                'updated_at' => now(),
            ]
        );

        $credenciales[] = [
            'Nivel' => '1B - ESTUDIANTE (SOLICITANTE)',
            'Nombre' => 'Pedro Solicitante',
            'Email' => 'solicitante@universidad.edu.ve',
            'Password' => 'Solicitante2026!',
            'Rol' => 'estudiante (estatus: solicitante)',
            'Permisos' => 'SOLO ver estatus de solicitud y subir documentos'
        ];

        // ════════════════════════════════════════════════════════════
        // GENERAR ARCHIVO DE CREDENCIALES
        // ════════════════════════════════════════════════════════════
        $this->generarArchivoCredenciales($credenciales);

        echo "\n✅ Usuarios creados exitosamente!\n";
        echo "📄 Archivo de credenciales generado en: storage/app/CREDENCIALES_USUARIOS.txt\n\n";
    }

    private function generarArchivoCredenciales(array $credenciales): void
    {
        $contenido = "╔══════════════════════════════════════════════════════════════════════════════╗\n";
        $contenido .= "║              CREDENCIALES DE USUARIOS - SISTEMA DE GESTIÓN UNIVERSITARIA     ║\n";
        $contenido .= "║                          Generado: " . now()->format('d/m/Y H:i:s') . "                        ║\n";
        $contenido .= "╚══════════════════════════════════════════════════════════════════════════════╝\n\n";
        $contenido .= "⚠️  IMPORTANTE: Estas credenciales son SOLO para desarrollo y testing.\n";
        $contenido .= "    En producción, cambiar TODAS las contraseñas inmediatamente.\n\n";
        $contenido .= str_repeat("═", 80) . "\n\n";

        foreach ($credenciales as $index => $cred) {
            $contenido .= "┌" . str_repeat("─", 78) . "┐\n";
            $contenido .= "│ " . str_pad($cred['Nivel'], 76) . " │\n";
            $contenido .= "├" . str_repeat("─", 78) . "┤\n";
            $contenido .= "│ Nombre:     " . str_pad($cred['Nombre'], 64) . " │\n";
            $contenido .= "│ Email:      " . str_pad($cred['Email'], 64) . " │\n";
            $contenido .= "│ Password:   " . str_pad($cred['Password'], 64) . " │\n";
            $contenido .= "│ Rol:        " . str_pad($cred['Rol'], 64) . " │\n";
            $contenido .= "├" . str_repeat("─", 78) . "┤\n";
            $contenido .= "│ Permisos:                                                                  │\n";
            
            // Dividir permisos en líneas de máximo 74 caracteres
            $permisos = wordwrap($cred['Permisos'], 74);
            $lineasPermisos = explode("\n", $permisos);
            foreach ($lineasPermisos as $linea) {
                $contenido .= "│   " . str_pad($linea, 74) . " │\n";
            }
            
            $contenido .= "└" . str_repeat("─", 78) . "┘\n\n";
        }

        $contenido .= "\n" . str_repeat("═", 80) . "\n";
        $contenido .= "JERARQUÍA DE ROLES:\n";
        $contenido .= str_repeat("═", 80) . "\n\n";
        $contenido .= "5. DESARROLLADOR   → Control total del sistema\n";
        $contenido .= "4. SOPORTE IT      → Mantenimiento técnico, usuarios, logs\n";
        $contenido .= "3. ADMINISTRATIVO  → Gestión académica, aprobar admisiones\n";
        $contenido .= "2. PROFESOR        → Gestionar calificaciones de sus materias\n";
        $contenido .= "1. ESTUDIANTE      → Ver datos, inscribirse (si está activo)\n\n";
        $contenido .= "ESTADOS DE ESTUDIANTE:\n";
        $contenido .= "  - solicitante: Aplicó pero no aprobado (acceso limitado)\n";
        $contenido .= "  - activo:      Aprobado, puede inscribirse\n";
        $contenido .= "  - suspendido:  Problemas académicos o pagos\n";
        $contenido .= "  - egresado:    Terminó la carrera\n";
        $contenido .= "  - retirado:    Abandonó\n\n";

        // Guardar archivo
        $ruta = storage_path('app/CREDENCIALES_USUARIOS.txt');
        file_put_contents($ruta, $contenido);

        // También crear versión JSON para fácil lectura programática
        $rutaJson = storage_path('app/CREDENCIALES_USUARIOS.json');
        file_put_contents($rutaJson, json_encode($credenciales, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }
}
