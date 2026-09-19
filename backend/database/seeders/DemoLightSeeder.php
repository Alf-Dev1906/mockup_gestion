<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Facultad;
use App\Models\Carrera;
use App\Models\Materia;
use App\Models\Aula;
use App\Models\Profesor;
use App\Models\Estudiante;
use App\Models\Horario;
use App\Models\Inscripcion;

class DemoLightSeeder extends Seeder
{
    /**
     * Versión LIGERA del DemoSeeder para Render
     * Genera datos mínimos pero suficientes para demostración
     */
    public function run(): void
    {
        echo "🎓 DEMO LIGHT SEEDER - Versión para Render\n";
        
        // PASO 1: Facultades (solo 5)
        echo "\n📚 Creando 5 facultades...\n";
        $facultades = [
            ['nombre' => 'Facultad de Ingeniería', 'codigo' => 'ING'],
            ['nombre' => 'Facultad de Ciencias', 'codigo' => 'CIE'],
            ['nombre' => 'Facultad de Humanidades', 'codigo' => 'HUM'],
            ['nombre' => 'Facultad de Medicina', 'codigo' => 'MED'],
            ['nombre' => 'Facultad de Derecho', 'codigo' => 'DER'],
        ];
        
        foreach ($facultades as $fac) {
            Facultad::create($fac);
        }
        echo "✅ 5 facultades creadas\n";
        
        // PASO 2: Carreras (2 por facultad = 10 carreras)
        echo "\n📖 Creando 10 carreras...\n";
        $facultadesIds = Facultad::pluck('id')->toArray();
        $carreras = [];
        
        foreach ($facultadesIds as $facId) {
            Carrera::create([
                'facultad_id' => $facId,
                'nombre' => "Carrera A - Facultad {$facId}",
                'codigo' => "CA{$facId}",
                'duracion_semestres' => 10,
                'creditos_totales' => 200
            ]);
            
            Carrera::create([
                'facultad_id' => $facId,
                'nombre' => "Carrera B - Facultad {$facId}",
                'codigo' => "CB{$facId}",
                'duracion_semestres' => 8,
                'creditos_totales' => 180
            ]);
        }
        echo "✅ 10 carreras creadas\n";
        
        // PASO 3: Materias (5 por carrera = 50 materias)
        echo "\n📝 Creando 50 materias...\n";
        $carrerasIds = Carrera::pluck('id')->toArray();
        
        foreach ($carrerasIds as $carreraId) {
            for ($i = 1; $i <= 5; $i++) {
                Materia::create([
                    'carrera_id' => $carreraId,
                    'nombre' => "Materia {$i} - Carrera {$carreraId}",
                    'codigo' => "MAT{$carreraId}-{$i}",
                    'creditos' => rand(3, 5),
                    'semestre' => $i,
                    'horas_semanales' => rand(3, 6)
                ]);
            }
        }
        echo "✅ 50 materias creadas\n";
        
        // PASO 4: Aulas (20 aulas)
        echo "\n🏫 Creando 20 aulas...\n";
        for ($i = 1; $i <= 20; $i++) {
            Aula::create([
                'nombre' => "Aula {$i}",
                'codigo' => "A{$i}",
                'capacidad' => rand(30, 60),
                'tipo' => ['teoria', 'laboratorio', 'auditorio'][rand(0, 2)],
                'edificio' => "Edificio " . chr(64 + ($i % 5) + 1)
            ]);
        }
        echo "✅ 20 aulas creadas\n";
        
        // PASO 5: Profesores (100 profesores)
        echo "\n👨‍🏫 Creando 100 profesores...\n";
        $facultadesForProf = Facultad::pluck('id')->toArray();
        
        for ($i = 1; $i <= 100; $i++) {
            Profesor::create([
                'nombres' => "Profesor{$i}",
                'apellidos' => "Apellido{$i}",
                'email' => "profesor{$i}@universidad.edu.ve",
                'cedula' => str_pad($i + 10000000, 8, '0', STR_PAD_LEFT),
                'telefono' => '0414' . str_pad($i, 7, '0', STR_PAD_LEFT),
                'fecha_nacimiento' => now()->subYears(rand(30, 60)),
                'genero' => ['M', 'F'][rand(0, 1)],
                'facultad_id' => $facultadesForProf[array_rand($facultadesForProf)],
                'especialidad' => 'Especialidad ' . $i,
                'titulo_academico' => ['Licenciado', 'Magister', 'Doctor'][rand(0, 2)],
                'fecha_ingreso' => now()->subYears(rand(1, 20)),
                'estatus' => 'activo'
            ]);
        }
        echo "✅ 100 profesores creados\n";
        
        // PASO 6: Estudiantes (500 estudiantes)
        echo "\n👥 Creando 500 estudiantes...\n";
        $carrerasForEst = Carrera::pluck('id')->toArray();
        
        for ($i = 1; $i <= 500; $i++) {
            Estudiante::create([
                'nombres' => "Estudiante{$i}",
                'apellidos' => "Apellido{$i}",
                'email' => "estudiante{$i}@universidad.edu.ve",
                'cedula' => str_pad($i + 20000000, 8, '0', STR_PAD_LEFT),
                'telefono' => '0424' . str_pad($i, 7, '0', STR_PAD_LEFT),
                'fecha_nacimiento' => now()->subYears(rand(18, 30)),
                'genero' => ['M', 'F'][rand(0, 1)],
                'carrera_id' => $carrerasForEst[array_rand($carrerasForEst)],
                'semestre_actual' => rand(1, 10),
                'promedio' => rand(12, 20),
                'fecha_ingreso' => now()->subYears(rand(1, 5)),
                'estatus' => 'activo'
            ]);
            
            if ($i % 100 == 0) {
                echo "  → {$i}/500 estudiantes creados\n";
            }
        }
        echo "✅ 500 estudiantes creados\n";
        
        // PASO 7: Horarios (50 horarios)
        echo "\n📅 Creando 50 horarios...\n";
        $materias = Materia::all();
        $profesores = Profesor::pluck('id')->toArray();
        $aulas = Aula::pluck('id')->toArray();
        $dias = ['Lunes', 'Martes', 'Miércoles', 'Jueves', 'Viernes'];
        
        for ($i = 0; $i < 50; $i++) {
            $materia = $materias->random();
            Horario::create([
                'materia_id' => $materia->id,
                'profesor_id' => $profesores[array_rand($profesores)],
                'aula_id' => $aulas[array_rand($aulas)],
                'dia_semana' => $dias[rand(0, 4)],
                'hora_inicio' => sprintf('%02d:00:00', rand(7, 16)),
                'hora_fin' => sprintf('%02d:00:00', rand(9, 18)),
                'periodo' => '2026-1',
                'cupo_maximo' => rand(30, 50)
            ]);
        }
        echo "✅ 50 horarios creados\n";
        
        // PASO 8: Inscripciones (1000 inscripciones)
        echo "\n✍️ Creando 1000 inscripciones...\n";
        $estudiantes = Estudiante::pluck('id')->toArray();
        $horarios = Horario::pluck('id')->toArray();
        $estatusOpciones = ['inscrito', 'cursando'];
        
        for ($i = 0; $i < 1000; $i++) {
            try {
                Inscripcion::create([
                    'estudiante_id' => $estudiantes[array_rand($estudiantes)],
                    'horario_id' => $horarios[array_rand($horarios)],
                    'periodo' => '2026-1',
                    'estatus' => $estatusOpciones[array_rand($estatusOpciones)],
                    'fecha_inscripcion' => now()->subDays(rand(1, 90))
                ]);
            } catch (\Exception $e) {
                // Ignorar duplicados
                continue;
            }
            
            if ($i % 200 == 0 && $i > 0) {
                echo "  → {$i}/1000 inscripciones creadas\n";
            }
        }
        echo "✅ 1000 inscripciones creadas\n";
        
        // RESUMEN
        echo "\n" . str_repeat('=', 60) . "\n";
        echo "🎉 DEMO LIGHT SEEDER COMPLETADO\n";
        echo str_repeat('=', 60) . "\n";
        echo "✅ Facultades: 5\n";
        echo "✅ Carreras: 10\n";
        echo "✅ Materias: 50\n";
        echo "✅ Aulas: 20\n";
        echo "✅ Profesores: 100\n";
        echo "✅ Estudiantes: 500\n";
        echo "✅ Horarios: 50\n";
        echo "✅ Inscripciones: ~1000\n";
        echo str_repeat('=', 60) . "\n";
    }
}
