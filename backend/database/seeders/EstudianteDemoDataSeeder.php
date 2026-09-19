<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class EstudianteDemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $estudianteId = 96001; // estudiante@universidad.edu.ve
        
        // Obtener inscripciones del estudiante
        $inscripciones = DB::table('inscripciones')
            ->where('estudiante_id', $estudianteId)
            ->where('estatus', 'inscrito')
            ->get();

        if ($inscripciones->isEmpty()) {
            $this->command->error('No hay inscripciones para el estudiante demo.');
            return;
        }

        $this->command->info('📚 Creando datos para ' . $inscripciones->count() . ' materias inscritas...');

        // Obtener user_id de un profesor cualquiera para created_by
        $profesorUserId = DB::table('users')->where('role', 'profesor')->first()->id ?? 1;

        $totalAssignments = 0;
        $totalSubmissions = 0;
        $totalQuizzes = 0;
        $totalAttempts = 0;

        foreach ($inscripciones as $inscripcion) {
            // 1. CREAR ASSIGNMENTS (2-3 por horario)
            $numAssignments = rand(2, 3);
            for ($i = 1; $i <= $numAssignments; $i++) {
                $dueDate = Carbon::now()->addDays(rand(5, 30));
                $isPast = rand(1, 3) === 1; // 33% son pasadas
                
                if ($isPast) {
                    $dueDate = Carbon::now()->subDays(rand(1, 15));
                }

                $assignmentId = DB::table('assignments')->insertGetId([
                    'horario_id' => $inscripcion->horario_id,
                    'titulo' => 'Tarea ' . $i . ' - ' . $this->getRandomAssignmentTitle(),
                    'descripcion' => $this->getRandomDescription(),
                    'fecha_apertura' => $dueDate->copy()->subDays(15),
                    'fecha_limite' => $dueDate,
                    'permitir_entrega_tardia' => rand(0, 1) === 1,
                    'peso_calificacion' => rand(10, 30),
                    'nota_maxima' => 20,
                    'tipos_permitidos' => 'pdf,zip,docx',
                    'tamano_maximo_mb' => 10,
                    'estatus' => 'publicado',
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $totalAssignments++;

                // Crear submission para tareas pasadas (60% de probabilidad)
                if ($isPast && rand(1, 100) <= 60) {
                    $submittedAt = $dueDate->copy()->subDays(rand(0, 5));
                    $esTardia = $submittedAt->isAfter($dueDate);
                    
                    DB::table('submissions')->insert([
                        'assignment_id' => $assignmentId,
                        'estudiante_id' => $estudianteId,
                        'inscripcion_id' => $inscripcion->id,
                        'archivo_url' => '/storage/assignments/entrega_' . uniqid() . '.pdf',
                        'nombre_archivo_original' => 'Tarea_' . $i . '_Estudiante.pdf',
                        'tamano_bytes' => rand(100000, 5000000),
                        'es_tardia' => $esTardia,
                        'nota' => rand(0, 1) === 0 ? null : rand(12, 20),
                        'retroalimentacion' => rand(0, 1) === 0 ? null : 'Buen trabajo.',
                        'calificado_at' => rand(0, 1) === 0 ? null : $submittedAt->copy()->addDays(rand(1, 5)),
                        'created_at' => $submittedAt,
                        'updated_at' => $submittedAt,
                    ]);

                    $totalSubmissions++;
                }
            }

            // 2. CREAR QUIZZES (2-3 por horario)
            $numQuizzes = rand(2, 3);
            for ($i = 1; $i <= $numQuizzes; $i++) {
                $startDate = Carbon::now()->addDays(rand(3, 20));
                $isPast = rand(1, 2) === 1; // 50% son pasados
                
                if ($isPast) {
                    $startDate = Carbon::now()->subDays(rand(5, 20));
                }

                $quizId = DB::table('quizzes')->insertGetId([
                    'horario_id' => $inscripcion->horario_id,
                    'titulo' => 'Examen ' . $i . ' - ' . $this->getRandomQuizTitle(),
                    'descripcion' => 'Evaluación del módulo ' . $i,
                    'tipo' => ['quiz', 'examen'][rand(0, 1)],
                    'fecha_inicio' => $startDate,
                    'fecha_fin' => $startDate->copy()->addHours(rand(2, 48)),
                    'visible_desde' => $startDate->copy()->subDays(2),
                    'duracion_minutos' => rand(30, 120),
                    'intentos_permitidos' => rand(1, 3),
                    'advertencias_max' => 3,
                    'orden_aleatorio' => rand(0, 1) === 1,
                    'mostrar_resultado_inmediato' => rand(0, 1) === 1,
                    'estado' => 'publicado',
                    'created_by' => $profesorUserId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $totalQuizzes++;

                // Crear preguntas del quiz
                $numQuestions = rand(5, 10);
                $notaMaxima = 0;
                
                for ($q = 1; $q <= $numQuestions; $q++) {
                    $puntos = rand(1, 4);
                    $notaMaxima += $puntos;
                    
                    DB::table('quiz_questions')->insert([
                        'quiz_id' => $quizId,
                        'orden' => $q,
                        'tipo' => 'seleccion',
                        'enunciado' => 'Pregunta ' . $q . ': ' . $this->getRandomQuestion(),
                        'contenido' => json_encode([
                            'opciones' => [
                                ['id' => 'A', 'texto' => 'Opción A', 'correcta' => true],
                                ['id' => 'B', 'texto' => 'Opción B', 'correcta' => false],
                                ['id' => 'C', 'texto' => 'Opción C', 'correcta' => false],
                                ['id' => 'D', 'texto' => 'Opción D', 'correcta' => false]
                            ]
                        ]),
                        'puntos' => $puntos,
                        'obligatoria' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Crear attempt para quizzes pasados (70% de probabilidad)
                if ($isPast && rand(1, 100) <= 70) {
                    $inicioAt = $startDate->copy()->addMinutes(rand(0, 60));
                    $finAt = $inicioAt->copy()->addMinutes(rand(30, 120));
                    $notaObtenida = rand(10, 20);

                    DB::table('quiz_attempts')->insert([
                        'quiz_id' => $quizId,
                        'estudiante_id' => $estudianteId,
                        'inscripcion_id' => $inscripcion->id,
                        'estado' => 'calificado',
                        'inicio_at' => $inicioAt,
                        'fin_at' => $finAt,
                        'tiempo_restante_seg' => 0,
                        'nota_obtenida' => $notaObtenida,
                        'nota_maxima' => $notaMaxima,
                        'advertencias_count' => rand(0, 2),
                        'auto_enviado' => false,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);

                    $totalAttempts++;
                }
            }
        }

        $this->command->info('✅ Datos creados exitosamente:');
        $this->command->info("   - Tareas: $totalAssignments (con $totalSubmissions entregas)");
        $this->command->info("   - Exámenes: $totalQuizzes (con $totalAttempts intentos)");
    }

    private function getRandomAssignmentTitle(): string
    {
        $titles = [
            'Análisis de Caso',
            'Investigación Bibliográfica',
            'Ensayo Crítico',
            'Proyecto Práctico',
            'Resolución de Problemas',
            'Trabajo en Equipo',
        ];

        return $titles[array_rand($titles)];
    }

    private function getRandomQuizTitle(): string
    {
        $titles = [
            'Parcial',
            'Quiz de Conocimientos',
            'Evaluación Continua',
            'Prueba Diagnóstica',
        ];

        return $titles[array_rand($titles)];
    }

    private function getRandomDescription(): string
    {
        $descriptions = [
            'Realiza el análisis solicitado según las pautas discutidas en clase.',
            'Desarrolla el contenido teórico-práctico aplicando los conceptos vistos.',
            'Entrega en formato PDF con las referencias bibliográficas correspondientes.',
            'Trabajo individual que será evaluado según rúbrica compartida.',
        ];

        return $descriptions[array_rand($descriptions)];
    }

    private function getRandomQuestion(): string
    {
        $questions = [
            '¿Cuál es la definición correcta del concepto estudiado?',
            'Seleccione la respuesta que mejor describa el proceso.',
            '¿Qué elemento NO pertenece a la clasificación mencionada?',
            'Identifique la opción correcta según lo visto en clase.',
            '¿Cuál de las siguientes afirmaciones es verdadera?',
        ];

        return $questions[array_rand($questions)];
    }
}
