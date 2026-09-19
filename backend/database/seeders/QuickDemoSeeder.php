<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

/**
 * Seeder dividido en partes pequeñas para Render
 * Cada parte se ejecuta en < 10 segundos
 */
class QuickDemoSeeder extends Seeder
{
    public function run(): void
    {
        $part = request('part', 'all');
        
        echo "🚀 QuickDemoSeeder - Parte: {$part}\n\n";
        
        switch ($part) {
            case '1-users':
                $this->part1Users();
                break;
            case '2-facultades':
                $this->part2Facultades();
                break;
            case '3-carreras':
                $this->part3Carreras();
                break;
            case '4-materias':
                $this->part4Materias();
                break;
            case '5-profesores':
                $this->part5Profesores();
                break;
            case '6-aulas':
                $this->part6Aulas();
                break;
            case '7-estudiantes':
                $this->part7Estudiantes();
                break;
            case '8-horarios':
                $this->part8Horarios();
                break;
            case '9-inscripciones':
                $this->part9Inscripciones();
                break;
            case 'all':
            default:
                echo "⚠️  Use ?part=1-users, ?part=2-facultades, etc.\n";
                echo "   Partes disponibles:\n";
                echo "   - 1-users\n";
                echo "   - 2-facultades\n";
                echo "   - 3-carreras\n";
                echo "   - 4-materias\n";
                echo "   - 5-profesores\n";
                echo "   - 6-aulas\n";
                echo "   - 7-estudiantes\n";
                echo "   - 8-horarios\n";
                echo "   - 9-inscripciones\n";
                break;
        }
    }
    
    private function part1Users(): void
    {
        echo "👥 Creando 6 usuarios...\n";
        $password = Hash::make('Admin2026!');
        
        $users = [
            ['name' => 'Administrador SGE', 'email' => 'admin@universidad.edu.ve', 'role' => 'administrativo'],
            ['name' => 'Profesor Demo', 'email' => 'profesor@universidad.edu.ve', 'role' => 'profesor'],
            ['name' => 'Estudiante Demo', 'email' => 'estudiante@universidad.edu.ve', 'role' => 'estudiante'],
            ['name' => 'Solicitante Demo', 'email' => 'solicitante@universidad.edu.ve', 'role' => 'estudiante'],
            ['name' => 'Desarrollador Sistema', 'email' => 'developer@universidad.edu.ve', 'role' => 'desarrollador'],
            ['name' => 'Soporte Técnico', 'email' => 'soporte@universidad.edu.ve', 'role' => 'soporte'],
        ];
        
        foreach ($users as $userData) {
            User::updateOrCreate(
                ['email' => $userData['email']],
                array_merge($userData, ['password' => $password, 'email_verified_at' => now()])
            );
        }
        
        echo "✅ 6 usuarios creados\n";
    }
    
    private function part2Facultades(): void
    {
        echo "📚 Creando 3 facultades...\n";
        
        $facultades = [
            ['nombre' => 'Facultad de Ingeniería', 'codigo' => 'ING', 'descripcion' => 'Ingeniería y Tecnología'],
            ['nombre' => 'Facultad de Ciencias', 'codigo' => 'CIE', 'descripcion' => 'Ciencias Básicas'],
            ['nombre' => 'Facultad de Humanidades', 'codigo' => 'HUM', 'descripcion' => 'Humanidades'],
        ];
        
        foreach ($facultades as $fac) {
            DB::table('facultades')->insertOrIgnore(array_merge($fac, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
        
        echo "✅ 3 facultades creadas\n";
    }
    
    private function part3Carreras(): void
    {
        echo "📖 Creando 6 carreras...\n";
        
        $facultades = DB::table('facultades')->pluck('id', 'codigo')->toArray();
        
        $carreras = [
            ['facultad_id' => $facultades['ING'], 'nombre' => 'Ingeniería en Sistemas', 'codigo' => 'ING-SIS', 'titulo_otorgado' => 'Ingeniero en Sistemas'],
            ['facultad_id' => $facultades['ING'], 'nombre' => 'Ingeniería Civil', 'codigo' => 'ING-CIV', 'titulo_otorgado' => 'Ingeniero Civil'],
            ['facultad_id' => $facultades['CIE'], 'nombre' => 'Licenciatura en Matemáticas', 'codigo' => 'LIC-MAT', 'titulo_otorgado' => 'Licenciado en Matemáticas'],
            ['facultad_id' => $facultades['CIE'], 'nombre' => 'Licenciatura en Física', 'codigo' => 'LIC-FIS', 'titulo_otorgado' => 'Licenciado en Física'],
            ['facultad_id' => $facultades['HUM'], 'nombre' => 'Licenciatura en Educación', 'codigo' => 'LIC-EDU', 'titulo_otorgado' => 'Licenciado en Educación'],
            ['facultad_id' => $facultades['HUM'], 'nombre' => 'Licenciatura en Historia', 'codigo' => 'LIC-HIS', 'titulo_otorgado' => 'Licenciado en Historia'],
        ];
        
        foreach ($carreras as $carrera) {
            DB::table('carreras')->insertOrIgnore(array_merge($carrera, [
                'duracion_semestres' => 10,
                'creditos_totales' => 200,
                'modalidad' => 'presencial',
                'activo' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }
        
        echo "✅ 6 carreras creadas\n";
    }
    
    private function part4Materias(): void
    {
        echo "📝 Creando 30 materias...\n";
        
        $carreras = DB::table('carreras')->pluck('id')->toArray();
        $count = 0;
        
        foreach ($carreras as $carreraId) {
            for ($i = 1; $i <= 5; $i++) {
                DB::table('materias')->insert([
                    'carrera_id' => $carreraId,
                    'nombre' => "Materia {$i} - Carrera {$carreraId}",
                    'codigo' => "MAT-{$carreraId}-" . str_pad($i, 2, '0', STR_PAD_LEFT),
                    'creditos' => rand(3, 5),
                    'horas_teoricas' => rand(2, 4),
                    'horas_practicas' => rand(0, 2),
                    'horas_laboratorio' => 0,
                    'semestre_recomendado' => $i,
                    'tipo' => 'obligatoria',
                    'activo' => true,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $count++;
            }
        }
        
        echo "✅ {$count} materias creadas\n";
    }
    
    private function part5Profesores(): void
    {
        echo "👨‍🏫 Creando 20 profesores...\n";
        
        $faker = \Faker\Factory::create('es_VE');
        $facultades = DB::table('facultades')->pluck('id')->toArray();
        
        for ($i = 1; $i <= 20; $i++) {
            DB::table('profesores')->insert([
                'facultad_id' => $faker->randomElement($facultades),
                'nombre' => $faker->firstName(),
                'apellido' => $faker->lastName(),
                'cedula' => 'V-' . (10000000 + $i),
                'email' => "profesor{$i}@universidad.edu.ve",
                'telefono' => '0414' . str_pad($i, 7, '0', STR_PAD_LEFT),
                'fecha_nacimiento' => now()->subYears(rand(30, 60))->format('Y-m-d'),
                'genero' => $faker->randomElement(['M', 'F']),
                'codigo_empleado' => 'PROF-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'fecha_contratacion' => now()->subYears(rand(1, 10))->format('Y-m-d'),
                'tipo_contrato' => 'tiempo_completo',
                'categoria' => 'asistente',
                'especialidad' => 'Especialidad ' . $i,
                'titulo_academico' => 'Magister',
                'horas_semanales' => 40,
                'estatus' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        echo "✅ 20 profesores creados\n";
    }
    
    private function part6Aulas(): void
    {
        echo "🏫 Creando 15 aulas...\n";
        
        $faker = \Faker\Factory::create();
        $facultades = DB::table('facultades')->pluck('id')->toArray();
        $edificios = ['A', 'B', 'C'];
        
        for ($i = 1; $i <= 15; $i++) {
            DB::table('aulas')->insert([
                'facultad_id' => $faker->randomElement($facultades),
                'codigo' => $edificios[$i % 3] . '-' . str_pad($i, 3, '0', STR_PAD_LEFT),
                'nombre' => 'Aula ' . $i,
                'edificio' => $edificios[$i % 3],
                'piso' => (string)rand(1, 3),
                'capacidad' => rand(30, 50),
                'tipo' => 'aula_regular',
                'tiene_proyector' => true,
                'tiene_aire_acondicionado' => true,
                'estatus' => 'disponible',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        echo "✅ 15 aulas creadas\n";
    }
    
    private function part7Estudiantes(): void
    {
        echo "👥 Creando 30 estudiantes...\n";
        
        $faker = \Faker\Factory::create('es_VE');
        $carreras = DB::table('carreras')->pluck('id')->toArray();
        
        for ($i = 1; $i <= 30; $i++) {
            DB::table('estudiantes')->insert([
                'nombre' => $faker->firstName(),
                'apellido' => $faker->lastName(),
                'cedula' => 'V-' . (20000000 + $i),
                'email' => "estudiante{$i}@universidad.edu.ve",
                'telefono' => '0424' . str_pad($i, 7, '0', STR_PAD_LEFT),
                'fecha_nacimiento' => now()->subYears(rand(18, 25))->format('Y-m-d'),
                'genero' => $faker->randomElement(['M', 'F']),
                'carrera_id' => $faker->randomElement($carreras),
                'matricula' => 'EST-' . str_pad($i, 6, '0', STR_PAD_LEFT),
                'fecha_ingreso' => now()->subYears(rand(1, 4))->format('Y-m-d'),
                'semestre_actual' => rand(1, 8),
                'indice_academico' => rand(14, 19),
                'creditos_aprobados' => rand(20, 100),
                'estatus' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        echo "✅ 30 estudiantes creados\n";
    }
    
    private function part8Horarios(): void
    {
        echo "📅 Creando 20 horarios...\n";
        
        $faker = \Faker\Factory::create();
        $materias = DB::table('materias')->pluck('id')->toArray();
        $profesores = DB::table('profesores')->pluck('id')->toArray();
        $aulas = DB::table('aulas')->pluck('id')->toArray();
        $dias = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes'];
        
        for ($i = 1; $i <= 20; $i++) {
            DB::table('horarios')->insert([
                'materia_id' => $faker->randomElement($materias),
                'profesor_id' => $faker->randomElement($profesores),
                'aula_id' => $faker->randomElement($aulas),
                'periodo_academico' => '2026-1',
                'seccion' => chr(65 + ($i % 3)),
                'dia_semana' => $faker->randomElement($dias),
                'hora_inicio' => sprintf('%02d:00:00', rand(7, 14)),
                'hora_fin' => sprintf('%02d:00:00', rand(9, 17)),
                'cupos_totales' => 40,
                'cupos_disponibles' => rand(5, 20),
                'estatus' => 'abierto',
                'fecha_inicio' => '2026-02-01',
                'fecha_fin' => '2026-06-30',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
        
        echo "✅ 20 horarios creados\n";
    }
    
    private function part9Inscripciones(): void
    {
        echo "✍️ Creando inscripciones...\n";
        
        $estudiantes = DB::table('estudiantes')->pluck('id')->toArray();
        $horarios = DB::table('horarios')->pluck('id')->toArray();
        $count = 0;
        
        foreach ($estudiantes as $estudianteId) {
            $horariosAleatorios = array_rand(array_flip($horarios), min(3, count($horarios)));
            
            foreach ((array)$horariosAleatorios as $horarioId) {
                DB::table('inscripciones')->insert([
                    'estudiante_id' => $estudianteId,
                    'horario_id' => $horarioId,
                    'fecha_inscripcion' => now()->subDays(rand(1, 30)),
                    'estatus' => 'confirmada',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $count++;
            }
        }
        
        echo "✅ {$count} inscripciones creadas\n";
    }
}
