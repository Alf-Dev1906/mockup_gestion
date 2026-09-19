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
     * Optimizado para completarse en ~60 segundos
     */
    public function run(): void
    {
        echo "🎓 DEMO LIGHT SEEDER - Versión para Render\n";
        
        // PASO 1: Facultades (solo 5)
        echo "\n📚 Creando 5 facultades...\n";
        $facultades = [
            ['nombre' => 'Facultad de Ingeniería', 'codigo' => 'ING', 'descripcion' => 'Facultad de Ingeniería'],
            ['nombre' => 'Facultad de Ciencias', 'codigo' => 'CIE', 'descripcion' => 'Facultad de Ciencias'],
            ['nombre' => 'Facultad de Humanidades', 'codigo' => 'HUM', 'descripcion' => 'Facultad de Humanidades y Educación'],
            ['nombre' => 'Facultad de Medicina', 'codigo' => 'MED', 'descripcion' => 'Facultad de Ciencias de la Salud'],
            ['nombre' => 'Facultad de Derecho', 'codigo' => 'DER', 'descripcion' => 'Facultad de Ciencias Jurídicas y Políticas'],
        ];
        
        foreach ($facultades as $fac) {
            Facultad::create($fac);
        }
        echo "✅ 5 facultades creadas\n";
        
        // PASO 2: Carreras (2 por facultad = 10 carreras)
        echo "\n📖 Creando 10 carreras...\n";
        $facultadesIds = Facultad::pluck('id')->toArray();
        
        foreach ($facultadesIds as $facId) {
            Carrera::create([
                'facultad_id' => $facId,
                'nombre' => "Licenciatura en Ciencias - Facultad {$facId}",
                'codigo' => "LIC{$facId}",
                'titulo_otorgado' => "Licenciado en Ciencias",
                'duracion_semestres' => 10,
                'creditos_totales' => 200,
                'modalidad' => 'presencial',
                'activo' => true
            ]);
            
            Carrera::create([
                'facultad_id' => $facId,
                'nombre' => "Ingeniería en Tecnología - Facultad {$facId}",
                'codigo' => "ING{$facId}",
                'titulo_otorgado' => "Ingeniero en Tecnología",
                'duracion_semestres' => 10,
                'creditos_totales' => 220,
                'modalidad' => 'presencial',
                'activo' => true
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
                    'horas_teoricas' => rand(2, 4),
                    'horas_practicas' => rand(0, 2),
                    'horas_laboratorio' => rand(0, 2),
                    'semestre_recomendado' => $i,
                    'tipo' => 'obligatoria',
                    'activo' => true
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
                'edificio' => "Edificio " . chr(64 + ($i % 5) + 1),
                'piso' => rand(1, 3),
                'disponible' => true
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
                'cedula' => 'V-' . str_pad($i + 10000000, 8, '0', STR_PAD_LEFT),
                'telefono' => '0414' . str_pad($i, 7, '0', STR_PAD_LEFT),
                'fecha_nacimiento' => now()->subYears(rand(30, 60))->format('Y-m-d'),
                'genero' => ['M', 'F'][rand(0, 1)],
                'direccion' => "Dirección del profesor {$i}",
                'facultad_id' => $facultadesForProf[array_rand($facultadesForProf)],
                'especialidad' => 'Especialidad ' . $i,
                'titulo_academico' => ['Licenciado', 'Magister', 'Doctor'][rand(0, 2)],
                'fecha_ingreso' => now()->subYears(rand(1, 20))->format('Y-m-d'),
                'tipo_contrato' => 'tiempo_completo',
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
                'cedula' => 'V-' . str_pad($i + 20000000, 8, '0', STR_PAD_LEFT),
                'telefono' => '0424' . str_pad($i, 7, '0', STR_PAD_LEFT),
                'fecha_nacimiento' => now()->subYears(rand(18, 30))->format('Y-m-d'),
                'genero' => ['M', 'F'][rand(0, 1)],
                'direccion' => "Dirección del estudiante {$i}",
                'carrera_id' => $carrerasForEst[array_rand($carrerasForEst)],
                'semestre_actual' => rand(1, 10),
                'indice_academico' => rand(12, 20),
                'creditos_aprobados' => rand(0, 180),
                'fecha_ingreso' => now()->subYears(rand(1, 5))->format('Y-m-d'),
                'tipo_ingreso' => 'regular',
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
            $horaInicio = rand(7, 16);
            $horaFin = $horaInicio + rand(2, 4);
            
            Horario::create([
                'materia_id' => $materia->id,
                'profesor_id' => $profesores[array_rand($profesores)],
                'aula_id' => $aulas[array_rand($aulas)],
                'dia_semana' => $dias[rand(0, 4)],
                'hora_inicio' => sprintf('%02d:00:00', $horaInicio),
                'hora_fin' => sprintf('%02d:00:00', $horaFin),
                'periodo_academico' => '2026-1',
                'seccion' => chr(65 + ($i % 5)),
                'cupo_maximo' => rand(30, 50),
                'cupos_disponibles' => rand(5, 30),
                'activo' => true
            ]);
        }
        echo "✅ 50 horarios creados\n";
        
        // PASO 8: Inscripciones (1000 inscripciones)
        echo "\n✍️ Creando 1000 inscripciones...\n";
        $estudiantes = Estudiante::pluck('id')->toArray();
        $horarios = Horario::pluck('id')->toArray();
        $estatusOpciones = ['inscrito', 'cursando'];
        
        $created = 0;
        $attempts = 0;
        $maxAttempts = 1500;
        
        while ($created < 1000 && $attempts < $maxAttempts) {
            $attempts++;
            try {
                Inscripcion::create([
                    'estudiante_id' => $estudiantes[array_rand($estudiantes)],
                    'horario_id' => $horarios[array_rand($horarios)],
                    'periodo_academico' => '2026-1',
                    'estatus' => $estatusOpciones[array_rand($estatusOpciones)],
                    'fecha_inscripcion' => now()->subDays(rand(1, 90)),
                    'nota_final' => null
                ]);
                $created++;
                
                if ($created % 200 == 0) {
                    echo "  → {$created}/1000 inscripciones creadas\n";
                }
            } catch (\Exception $e) {
                // Ignorar duplicados o errores de constraint
                continue;
            }
        }
        echo "✅ {$created} inscripciones creadas\n";
        
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
        echo "✅ Inscripciones: {$created}\n";
        echo str_repeat('=', 60) . "\n";
    }
}
