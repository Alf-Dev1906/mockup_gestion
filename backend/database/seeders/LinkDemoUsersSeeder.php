<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\{User, Estudiante, Profesor};

class LinkDemoUsersSeeder extends Seeder
{
    /**
     * Vincula los usuarios de prueba con sus respectivas tablas
     */
    public function run(): void
    {
        echo "🔗 Vinculando usuarios demo con sus tablas correspondientes...\n\n";

        // 1. Usuario ESTUDIANTE - crear registro en tabla estudiantes
        $estudianteUser = User::where('email', 'estudiante@universidad.edu.ve')->first();
        if ($estudianteUser) {
            $carrera = DB::table('carreras')->first();
            
            $estudiante = Estudiante::updateOrCreate(
                ['email' => 'estudiante@universidad.edu.ve'],
                [
                    'user_id' => $estudianteUser->id,
                    'nombre' => 'Estudiante',
                    'apellido' => 'Demo',
                    'cedula' => 'V-99999991',
                    'telefono' => '0412-9999991',
                    'fecha_nacimiento' => '2000-01-01',
                    'genero' => 'M',
                    'carrera_id' => $carrera->id ?? 1,
                    'matricula' => 'EST-DEMO-001',
                    'fecha_ingreso' => now()->subYears(2),
                    'semestre_actual' => 4,
                    'indice_academico' => 16.50,
                    'creditos_aprobados' => 60,
                    'estatus' => 'activo',
                ]
            );
            echo "✓ Usuario ESTUDIANTE vinculado (user_id: {$estudianteUser->id}, estudiante_id: {$estudiante->id})\n";
        }

        // 2. Usuario SOLICITANTE - también es estudiante PERO con estatus inactivo
        $solicitanteUser = User::where('email', 'solicitante@universidad.edu.ve')->first();
        if ($solicitanteUser) {
            $carrera = DB::table('carreras')->skip(1)->first();
            
            $solicitante = Estudiante::updateOrCreate(
                ['email' => 'solicitante@universidad.edu.ve'],
                [
                    'user_id' => $solicitanteUser->id,
                    'nombre' => 'Solicitante',
                    'apellido' => 'Demo',
                    'cedula' => 'V-99999992',
                    'telefono' => '0412-9999992',
                    'fecha_nacimiento' => '2001-01-01',
                    'genero' => 'F',
                    'carrera_id' => $carrera->id ?? 2,
                    'matricula' => 'EST-DEMO-002',
                    'fecha_ingreso' => now()->subMonths(1),
                    'semestre_actual' => 1,
                    'indice_academico' => 0.00,
                    'creditos_aprobados' => 0,
                    'estatus' => 'inactivo', // IMPORTANTE: inactivo para que sea considerado solicitante
                ]
            );
            
            // Crear solicitud de admisión si no existe
            DB::table('solicitudes_admision')->updateOrInsert(
                ['estudiante_id' => $solicitante->id],
                [
                    'numero_referencia' => 'SOL-2026-DEMO-002',
                    'estado' => 'en_revision',
                    'fecha_envio' => now()->subDays(5),
                    'nivel_educativo' => 'bachiller',
                    'institucion_egreso' => 'Liceo Nacional Andrés Bello',
                    'año_graduacion' => 2025,
                    'promedio_notas' => 18.50,
                    'paso1_completado' => true,
                    'paso2_completado' => true,
                    'paso3_completado' => true,
                    'paso4_completado' => true,
                    'paso5_completado' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]
            );
            
            echo "✓ Usuario SOLICITANTE vinculado (user_id: {$solicitanteUser->id}, estudiante_id: {$solicitante->id}, estatus: inactivo)\n";
        }

        // 3. Usuario PROFESOR - crear registro en tabla profesores
        $profesorUser = User::where('email', 'profesor@universidad.edu.ve')->first();
        if ($profesorUser) {
            $facultad = DB::table('facultades')->first();
            
            $profesor = Profesor::updateOrCreate(
                ['email' => 'profesor@universidad.edu.ve'],
                [
                    'user_id' => $profesorUser->id,
                    'facultad_id' => $facultad->id ?? 1,
                    'nombre' => 'Profesor',
                    'apellido' => 'Demo',
                    'cedula' => 'V-99999993',
                    'telefono' => '0412-9999993',
                    'fecha_nacimiento' => '1980-01-01',
                    'genero' => 'M',
                    'codigo_empleado' => 'PROF-DEMO-001',
                    'fecha_contratacion' => now()->subYears(5),
                    'tipo_contrato' => 'tiempo_completo',
                    'categoria' => 'asociado',
                    'especialidad' => 'Sistemas de Información',
                    'titulo_academico' => 'Doctor',
                    'horas_semanales' => 40,
                    'estatus' => 'activo',
                ]
            );
            echo "✓ Usuario PROFESOR vinculado (user_id: {$profesorUser->id}, profesor_id: {$profesor->id})\n";
        }

        // Los roles ADMIN, SOPORTE y DESARROLLADOR no necesitan registros adicionales
        echo "✓ Usuarios ADMIN, SOPORTE y DESARROLLADOR no requieren vinculación\n";

        echo "\n✅ Vinculación completada!\n\n";
    }
}
