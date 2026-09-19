<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use App\Models\{User, Facultad, Carrera, Profesor, Estudiante, Aula, Materia, Horario, Inscripcion};
use Illuminate\Support\Facades\Hash;

class DemoSeeder extends Seeder
{
    /**
     * Seeder ligero para demo en producción
     * Mantiene TODOS los usuarios pero reduce datos masivos
     */
    public function run(): void
    {
        $startTime = microtime(true);
        
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║         DEMO SEEDER - Versión Ligera para Producción        ║\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n";
        echo "\n";

        // Desactivar FK checks
        $this->disableForeignKeyChecks();

        try {
            // 1. TODOS LOS USUARIOS DE PRUEBA (6 roles)
            echo "👥 PASO 1/9: Usuarios de prueba (6 usuarios)\n";
            $this->seedUsers();

            // 2. Facultades (solo 3)
            echo "📁 PASO 2/9: Facultades (3)\n";
            $this->seedFacultades();

            // 3. Carreras (solo 6: 2 por facultad)
            echo "📚 PASO 3/9: Carreras (6)\n";
            $this->seedCarreras();

            // 4. Profesores (20 en lugar de 50)
            echo "👨‍🏫 PASO 4/9: Profesores (20)\n";
            $this->seedProfesores(20);

            // 5. Aulas (15 en lugar de 30)
            echo "🏫 PASO 5/9: Aulas (15)\n";
            $this->seedAulas(15);

            // 6. Materias (30: 5 por carrera)
            echo "📖 PASO 6/9: Materias (30)\n";
            $this->seedMaterias();

            // 7. Estudiantes (30 en lugar de 60)
            echo "👨‍🎓 PASO 7/9: Estudiantes (30)\n";
            $this->seedEstudiantes(30);

            // 8. Horarios (20 en lugar de 50)
            echo "🕐 PASO 8/9: Horarios (20)\n";
            $this->seedHorarios(20);

            // 9. Inscripciones (~100 en lugar de 300)
            echo "📝 PASO 9/9: Inscripciones (100)\n";
            $this->seedInscripciones();

        } finally {
            $this->enableForeignKeyChecks();
        }

        $totalTime = round(microtime(true) - $startTime, 2);

        echo "\n";
        echo str_repeat("═", 64) . "\n";
        echo "✅ DEMO SEEDER COMPLETADO EN {$totalTime}s\n";
        echo str_repeat("═", 64) . "\n";
        echo "\n";
        echo "📊 Resumen de registros creados:\n";
        echo "    ✓ Usuarios:       " . DB::table('users')->count() . "\n";
        echo "    ✓ Facultades:     " . DB::table('facultades')->count() . "\n";
        echo "    ✓ Carreras:       " . DB::table('carreras')->count() . "\n";
        echo "    ✓ Profesores:     " . DB::table('profesores')->count() . "\n";
        echo "    ✓ Aulas:          " . DB::table('aulas')->count() . "\n";
        echo "    ✓ Materias:       " . number_format(DB::table('materias')->count()) . "\n";
        echo "    ✓ Estudiantes:    " . DB::table('estudiantes')->count() . "\n";
        echo "    ✓ Horarios:       " . DB::table('horarios')->count() . "\n";
        echo "    ✓ Inscripciones:  " . number_format(DB::table('inscripciones')->count()) . "\n";
        echo "\n";
        echo "🎯 Sistema listo para demo con todos los roles funcionando!\n";
        echo "\n";
    }

    protected function disableForeignKeyChecks(): void
    {
        $driver = DB::getDriverName();
        switch ($driver) {
            case 'mysql': DB::statement('SET FOREIGN_KEY_CHECKS=0'); break;
            case 'sqlite': DB::statement('PRAGMA foreign_keys = OFF'); break;
        }
    }

    protected function enableForeignKeyChecks(): void
    {
        $driver = DB::getDriverName();
        switch ($driver) {
            case 'mysql': DB::statement('SET FOREIGN_KEY_CHECKS=1'); break;
            case 'sqlite': DB::statement('PRAGMA foreign_keys = ON'); break;
        }
    }

    protected function seedUsers(): void
    {
        $password = Hash::make('Admin2026!');

        $users = [
            [
                'name' => 'Administrador SGE',
                'email' => 'admin@universidad.edu.ve',
                'password' => $password,
                'role' => 'administrativo',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Profesor Demo',
                'email' => 'profesor@universidad.edu.ve',
                'password' => $password,
                'role' => 'profesor',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Estudiante Demo',
                'email' => 'estudiante@universidad.edu.ve',
                'password' => $password,
                'role' => 'estudiante',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Solicitante Demo',
                'email' => 'solicitante@universidad.edu.ve',
                'password' => $password,
                'role' => 'estudiante',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Desarrollador Sistema',
                'email' => 'developer@universidad.edu.ve',
                'password' => $password,
                'role' => 'desarrollador',
                'email_verified_at' => now(),
            ],
            [
                'name' => 'Soporte Técnico',
                'email' => 'soporte@universidad.edu.ve',
                'password' => $password,
                'role' => 'soporte',
                'email_verified_at' => now(),
            ],
        ];

        foreach ($users as $userData) {
            // Usar updateOrCreate para evitar duplicados
            User::updateOrCreate(
                ['email' => $userData['email']],
                $userData
            );
        }

        echo "   ✓ 6 usuarios creados/actualizados (admin, profesor, estudiante, solicitante, developer, soporte)\n";
    }

    protected function seedProfesores(int $count): void
    {
        // Si ya existen profesores, no crear más
        $existingCount = DB::table('profesores')->count();
        if ($existingCount >= $count) {
            echo "   ℹ️  Ya existen {$existingCount} profesores, omitiendo creación\n";
            return;
        }

        $faker = \Faker\Factory::create('es_VE');
        $facultades = DB::table('facultades')->pluck('id')->toArray();
        
        if (empty($facultades)) {
            echo "   ⚠️  No hay facultades, omitiendo profesores\n";
            return;
        }

        $toCreate = $count - $existingCount;

        for ($i = 0; $i < $toCreate; $i++) {
            DB::table('profesores')->insert([
                'facultad_id' => $faker->randomElement($facultades),
                'nombre' => $faker->firstName(),
                'apellido' => $faker->lastName() . ' ' . $faker->lastName(),
                'cedula' => 'V-' . $faker->unique()->numberBetween(10000000, 30000000),
                'email' => $faker->unique()->safeEmail(),
                'telefono' => $faker->phoneNumber(),
                'fecha_nacimiento' => $faker->dateTimeBetween('-60 years', '-25 years'),
                'genero' => $faker->randomElement(['M', 'F']),
                'codigo_empleado' => 'PROF-' . str_pad($existingCount + $i + 5001, 6, '0', STR_PAD_LEFT),
                'fecha_contratacion' => $faker->dateTimeBetween('-10 years', '-1 year'),
                'tipo_contrato' => $faker->randomElement(['tiempo_completo', 'medio_tiempo']),
                'categoria' => $faker->randomElement(['instructor', 'asistente', 'agregado', 'asociado']),
                'especialidad' => $faker->words(3, true),
                'titulo_academico' => $faker->randomElement(['Licenciado', 'Magister', 'Doctor']),
                'horas_semanales' => $faker->randomElement([20, 40]),
                'estatus' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $finalCount = DB::table('profesores')->count();
        echo "   ✓ {$finalCount} profesores en total ({$toCreate} nuevos)\n";
    }

    protected function seedAulas(int $count): void
    {
        // Si ya existen aulas, no crear más
        $existingCount = DB::table('aulas')->count();
        if ($existingCount >= $count) {
            echo "   ℹ️  Ya existen {$existingCount} aulas, omitiendo creación\n";
            return;
        }

        $faker = \Faker\Factory::create('es_VE');
        $facultades = DB::table('facultades')->pluck('id')->toArray();
        $edificios = ['A', 'B', 'C', 'D'];
        $tipos = ['aula_regular', 'laboratorio', 'auditorio'];
        $toCreate = $count - $existingCount;

        if (empty($facultades)) {
            echo "   ⚠️  No hay facultades, omitiendo aulas\n";
            return;
        }

        for ($i = $existingCount; $i < $count; $i++) {
            DB::table('aulas')->insert([
                'facultad_id' => $faker->randomElement($facultades),
                'codigo' => $faker->randomElement($edificios) . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'nombre' => 'Aula ' . ($i + 1),
                'edificio' => $faker->randomElement($edificios),
                'piso' => (string)$faker->numberBetween(1, 4),
                'capacidad' => $faker->randomElement([25, 30, 40, 50]),
                'tipo' => $faker->randomElement($tipos),
                'tiene_proyector' => $faker->boolean(70),
                'tiene_aire_acondicionado' => $faker->boolean(80),
                'estatus' => 'disponible',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $finalCount = DB::table('aulas')->count();
        echo "   ✓ {$finalCount} aulas en total ({$toCreate} nuevas)\n";
    }

    protected function seedEstudiantes(int $count): void
    {
        // Si ya existen estudiantes, no crear más
        $existingCount = DB::table('estudiantes')->count();
        if ($existingCount >= $count) {
            echo "   ℹ️  Ya existen {$existingCount} estudiantes, omitiendo creación\n";
            return;
        }

        $faker = \Faker\Factory::create('es_VE');
        $carreras = DB::table('carreras')->pluck('id')->toArray();
        
        if (empty($carreras)) {
            echo "   ⚠️  No hay carreras, omitiendo estudiantes\n";
            return;
        }

        $toCreate = $count - $existingCount;

        for ($i = 0; $i < $toCreate; $i++) {
            DB::table('estudiantes')->insert([
                'nombre' => $faker->firstName(),
                'apellido' => $faker->lastName() . ' ' . $faker->lastName(),
                'cedula' => 'V-' . $faker->unique()->numberBetween(20000000, 30000000),
                'email' => $faker->unique()->safeEmail(),
                'telefono' => $faker->phoneNumber(),
                'fecha_nacimiento' => $faker->dateTimeBetween('-25 years', '-17 years'),
                'genero' => $faker->randomElement(['M', 'F']),
                'carrera_id' => $faker->randomElement($carreras),
                'matricula' => 'EST-' . str_pad($existingCount + $i + 20000, 6, '0', STR_PAD_LEFT),
                'fecha_ingreso' => $faker->dateTimeBetween('-4 years', '-1 year'),
                'semestre_actual' => $faker->numberBetween(1, 8),
                'indice_academico' => $faker->randomFloat(2, 12, 20),
                'creditos_aprobados' => $faker->numberBetween(0, 120),
                'estatus' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $finalCount = DB::table('estudiantes')->count();
        echo "   ✓ {$finalCount} estudiantes en total ({$toCreate} nuevos)\n";
    }

    protected function seedHorarios(int $count): void
    {
        // Si ya existen horarios, no crear más
        $existingCount = DB::table('horarios')->count();
        if ($existingCount >= $count) {
            echo "   ℹ️  Ya existen {$existingCount} horarios, omitiendo creación\n";
            return;
        }

        $faker = \Faker\Factory::create('es_VE');
        $materias = DB::table('materias')->pluck('id')->toArray();
        $profesores = DB::table('profesores')->pluck('id')->toArray();
        $aulas = DB::table('aulas')->pluck('id')->toArray();
        $dias = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes'];
        $toCreate = $count - $existingCount;

        if (empty($materias) || empty($profesores) || empty($aulas)) {
            echo "   ⚠️  Faltan datos previos (materias/profesores/aulas), omitiendo horarios\n";
            return;
        }

        for ($i = 0; $i < $toCreate; $i++) {
            DB::table('horarios')->insert([
                'materia_id' => $faker->randomElement($materias),
                'profesor_id' => $faker->randomElement($profesores),
                'aula_id' => $faker->randomElement($aulas),
                'periodo_academico' => '2026-1',
                'seccion' => $faker->randomElement(['A', 'B', 'C']),
                'dia_semana' => $faker->randomElement($dias),
                'hora_inicio' => $faker->time('H:i:s', '16:00:00'),
                'hora_fin' => $faker->time('H:i:s', '20:00:00'),
                'cupos_totales' => $faker->numberBetween(25, 40),
                'cupos_disponibles' => $faker->numberBetween(5, 15),
                'estatus' => 'abierto',
                'fecha_inicio' => '2026-02-01',
                'fecha_fin' => '2026-06-30',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $finalCount = DB::table('horarios')->count();
        echo "   ✓ {$finalCount} horarios en total ({$toCreate} nuevos)\n";
    }

    protected function seedFacultades(): void
    {
        $existingCount = DB::table('facultades')->count();
        if ($existingCount >= 3) {
            echo "   ℹ️  Ya existen {$existingCount} facultades, omitiendo creación\n";
            return;
        }

        $facultades = [
            ['nombre' => 'Facultad de Ingeniería', 'codigo' => 'ING', 'descripcion' => 'Ingeniería y Tecnología'],
            ['nombre' => 'Facultad de Ciencias', 'codigo' => 'CIE', 'descripcion' => 'Ciencias Básicas y Aplicadas'],
            ['nombre' => 'Facultad de Humanidades', 'codigo' => 'HUM', 'descripcion' => 'Humanidades y Educación'],
        ];

        foreach ($facultades as $fac) {
            DB::table('facultades')->insert(array_merge($fac, [
                'created_at' => now(),
                'updated_at' => now(),
            ]));
        }

        echo "   ✓ 3 facultades creadas\n";
    }

    protected function seedCarreras(): void
    {
        $existingCount = DB::table('carreras')->count();
        if ($existingCount >= 6) {
            echo "   ℹ️  Ya existen {$existingCount} carreras, omitiendo creación\n";
            return;
        }

        $facultades = DB::table('facultades')->pluck('id', 'codigo')->toArray();

        if (empty($facultades)) {
            echo "   ⚠️  No hay facultades, omitiendo carreras\n";
            return;
        }

        $carreras = [
            [
                'facultad_id' => $facultades['ING'] ?? null,
                'nombre' => 'Ingeniería en Sistemas',
                'codigo' => 'ING-SIS',
                'titulo_otorgado' => 'Ingeniero en Sistemas',
                'duracion_semestres' => 10,
                'creditos_totales' => 220,
                'modalidad' => 'presencial',
                'activo' => true,
            ],
            [
                'facultad_id' => $facultades['ING'] ?? null,
                'nombre' => 'Ingeniería Civil',
                'codigo' => 'ING-CIV',
                'titulo_otorgado' => 'Ingeniero Civil',
                'duracion_semestres' => 10,
                'creditos_totales' => 230,
                'modalidad' => 'presencial',
                'activo' => true,
            ],
            [
                'facultad_id' => $facultades['CIE'] ?? null,
                'nombre' => 'Licenciatura en Matemáticas',
                'codigo' => 'LIC-MAT',
                'titulo_otorgado' => 'Licenciado en Matemáticas',
                'duracion_semestres' => 8,
                'creditos_totales' => 180,
                'modalidad' => 'presencial',
                'activo' => true,
            ],
            [
                'facultad_id' => $facultades['CIE'] ?? null,
                'nombre' => 'Licenciatura en Física',
                'codigo' => 'LIC-FIS',
                'titulo_otorgado' => 'Licenciado en Física',
                'duracion_semestres' => 8,
                'creditos_totales' => 180,
                'modalidad' => 'presencial',
                'activo' => true,
            ],
            [
                'facultad_id' => $facultades['HUM'] ?? null,
                'nombre' => 'Licenciatura en Educación',
                'codigo' => 'LIC-EDU',
                'titulo_otorgado' => 'Licenciado en Educación',
                'duracion_semestres' => 8,
                'creditos_totales' => 160,
                'modalidad' => 'presencial',
                'activo' => true,
            ],
            [
                'facultad_id' => $facultades['HUM'] ?? null,
                'nombre' => 'Licenciatura en Historia',
                'codigo' => 'LIC-HIS',
                'titulo_otorgado' => 'Licenciado en Historia',
                'duracion_semestres' => 8,
                'creditos_totales' => 160,
                'modalidad' => 'presencial',
                'activo' => true,
            ],
        ];

        foreach ($carreras as $carrera) {
            if ($carrera['facultad_id']) {
                DB::table('carreras')->insert(array_merge($carrera, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ]));
            }
        }

        echo "   ✓ 6 carreras creadas\n";
    }

    protected function seedMaterias(): void
    {
        $existingCount = DB::table('materias')->count();
        if ($existingCount >= 30) {
            echo "   ℹ️  Ya existen {$existingCount} materias, omitiendo creación\n";
            return;
        }

        $carreras = DB::table('carreras')->pluck('id')->toArray();

        if (empty($carreras)) {
            echo "   ⚠️  No hay carreras, omitiendo materias\n";
            return;
        }

        $count = 0;
        foreach ($carreras as $carreraId) {
            // 5 materias por carrera
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

        echo "   ✓ {$count} materias creadas\n";
    }

    protected function seedInscripciones(): void
    {
        $existingCount = DB::table('inscripciones')->count();
        if ($existingCount > 0) {
            echo "   ℹ️  Ya existen {$existingCount} inscripciones, omitiendo creación\n";
            return;
        }

        $estudiantes = DB::table('estudiantes')->pluck('id')->toArray();
        $horarios = DB::table('horarios')->pluck('id')->toArray();
        
        if (empty($estudiantes) || empty($horarios)) {
            echo "   ⚠️  No hay estudiantes u horarios, omitiendo inscripciones\n";
            return;
        }

        $count = 0;

        foreach ($estudiantes as $estudianteId) {
            // Cada estudiante se inscribe en 5 materias
            $horariosAleatorios = array_rand(array_flip($horarios), min(5, count($horarios)));
            
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

        echo "   ✓ {$count} inscripciones creadas\n";
    }
}
