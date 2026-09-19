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
            // 1. TODOS LOS USUARIOS DE PRUEBA
            echo "👥 PASO 1/9: Usuarios de prueba (4 usuarios)\n";
            $this->seedUsers();

            // 2. Facultades (10)
            echo "📁 PASO 2/9: Facultades\n";
            $this->call(FacultadSeeder::class);

            // 3. Carreras (40)
            echo "📚 PASO 3/9: Carreras\n";
            $this->call(CarreraSeeder::class);
            $this->call(NuevasCarrerasSeeder::class);

            // 4. Profesores (50 en lugar de 5000)
            echo "👨‍🏫 PASO 4/9: Profesores (50)\n";
            $this->seedProfesores(50);

            // 5. Aulas (30 en lugar de 800)
            echo "🏫 PASO 5/9: Aulas (30)\n";
            $this->seedAulas(30);

            // 6. Materias (completas ~2000)
            echo "📖 PASO 6/9: Materias\n";
            $this->call(MateriaSeeder::class);

            // 7. Estudiantes (60 en lugar de 96000)
            echo "👨‍🎓 PASO 7/9: Estudiantes (60)\n";
            $this->seedEstudiantes(60);

            // 8. Horarios (50 en lugar de 15000)
            echo "🕐 PASO 8/9: Horarios (50)\n";
            $this->seedHorarios(50);

            // 9. Inscripciones (~300 en lugar de 138000)
            echo "📝 PASO 9/9: Inscripciones\n";
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
        ];

        foreach ($users as $userData) {
            User::create($userData);
        }

        echo "   ✓ 4 usuarios creados (admin, profesor, estudiante, solicitante)\n";
    }

    protected function seedProfesores(int $count): void
    {
        $faker = \Faker\Factory::create('es_VE');
        $carreras = DB::table('carreras')->pluck('id')->toArray();

        for ($i = 0; $i < $count; $i++) {
            DB::table('profesores')->insert([
                'nombres' => $faker->firstName(),
                'apellidos' => $faker->lastName() . ' ' . $faker->lastName(),
                'cedula' => 'V-' . $faker->unique()->numberBetween(10000000, 30000000),
                'email' => $faker->unique()->safeEmail(),
                'telefono' => $faker->phoneNumber(),
                'fecha_nacimiento' => $faker->dateTimeBetween('-60 years', '-25 years'),
                'genero' => $faker->randomElement(['M', 'F']),
                'codigo_empleado' => 'PROF-' . str_pad($i + 5001, 6, '0', STR_PAD_LEFT),
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

        echo "   ✓ {$count} profesores creados\n";
    }

    protected function seedAulas(int $count): void
    {
        $faker = \Faker\Factory::create('es_VE');
        $edificios = ['A', 'B', 'C', 'D'];
        $tipos = ['aula_regular', 'laboratorio', 'auditorio'];

        for ($i = 0; $i < $count; $i++) {
            DB::table('aulas')->insert([
                'codigo' => $faker->randomElement($edificios) . '-' . str_pad($i + 1, 3, '0', STR_PAD_LEFT),
                'nombre' => 'Aula ' . ($i + 1),
                'edificio' => $faker->randomElement($edificios),
                'piso' => (string)$faker->numberBetween(1, 4),
                'capacidad' => $faker->randomElement([25, 30, 40, 50]),
                'tipo' => $faker->randomElement($tipos),
                'tiene_proyector' => $faker->boolean(70),
                'tiene_aire_acondicionado' => $faker->boolean(80),
                'tiene_pizarra_digital' => $faker->boolean(30),
                'estatus' => 'disponible',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        echo "   ✓ {$count} aulas creadas\n";
    }

    protected function seedEstudiantes(int $count): void
    {
        $faker = \Faker\Factory::create('es_VE');
        $carreras = DB::table('carreras')->pluck('id')->toArray();

        for ($i = 0; $i < $count; $i++) {
            DB::table('estudiantes')->insert([
                'nombres' => $faker->firstName(),
                'apellidos' => $faker->lastName() . ' ' . $faker->lastName(),
                'cedula' => 'V-' . $faker->unique()->numberBetween(20000000, 30000000),
                'email' => $faker->unique()->safeEmail(),
                'telefono' => $faker->phoneNumber(),
                'fecha_nacimiento' => $faker->dateTimeBetween('-25 years', '-17 years'),
                'genero' => $faker->randomElement(['M', 'F']),
                'carrera_id' => $faker->randomElement($carreras),
                'cohorte' => $faker->randomElement(['2024-1', '2024-2', '2025-1']),
                'semestre_actual' => $faker->numberBetween(1, 8),
                'indice_academico' => $faker->randomFloat(2, 12, 20),
                'creditos_aprobados' => $faker->numberBetween(0, 120),
                'estatus' => 'activo',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        echo "   ✓ {$count} estudiantes creados\n";
    }

    protected function seedHorarios(int $count): void
    {
        $faker = \Faker\Factory::create('es_VE');
        $materias = DB::table('materias')->pluck('id')->toArray();
        $profesores = DB::table('profesores')->pluck('id')->toArray();
        $aulas = DB::table('aulas')->pluck('id')->toArray();
        $dias = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes'];

        for ($i = 0; $i < $count; $i++) {
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

        echo "   ✓ {$count} horarios creados\n";
    }

    protected function seedInscripciones(): void
    {
        $estudiantes = DB::table('estudiantes')->pluck('id')->toArray();
        $horarios = DB::table('horarios')->pluck('id')->toArray();
        $count = 0;

        foreach ($estudiantes as $estudianteId) {
            // Cada estudiante se inscribe en 5 materias
            $horariosAleatorios = array_rand(array_flip($horarios), min(5, count($horarios)));
            
            foreach ($horariosAleatorios as $horarioId) {
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
