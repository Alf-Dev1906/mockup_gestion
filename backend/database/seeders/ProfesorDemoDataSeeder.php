<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class ProfesorDemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $profesorId = 5001; // profesor@universidad.edu.ve
        
        // Obtener horarios del profesor con sus inscripciones
        $horarios = DB::table('horarios')
            ->where('profesor_id', $profesorId)
            ->where('estatus', '!=', 'cancelado')
            ->get();

        if ($horarios->isEmpty()) {
            $this->command->error('No hay horarios asignados al profesor demo.');
            return;
        }

        $this->command->info('📚 Creando datos para ' . $horarios->count() . ' horarios...');

        // Contadores
        $totalAssignments = 0;
        $totalSubmissions = 0;
        $totalQuizzes = 0;
        $totalAttempts = 0;
        $totalSessions = 0;
        $totalAttendances = 0;

        foreach ($horarios as $horario) {
            // Obtener inscripciones del horario
            $inscripciones = DB::table('inscripciones')
                ->where('horario_id', $horario->id)
                ->where('estatus', 'inscrito')
                ->get();

            if ($inscripciones->isEmpty()) {
                continue;
            }

            // 1. CREAR ASSIGNMENTS (2-4 por horario)
            $numAssignments = rand(2, 4);
            for ($i = 1; $i <= $numAssignments; $i++) {
                $dueDate = Carbon::now()->addDays(rand(5, 30));
                $isPast = rand(1, 3) === 1; // 33% son pasadas
                
                if ($isPast) {
                    $dueDate = Carbon::now()->subDays(rand(1, 15));
                }

                $assignmentId = DB::table('assignments')->insertGetId([
                    'horario_id' => $horario->id,
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

                // Crear submissions para algunos estudiantes
                foreach ($inscripciones as $inscripcion) {
                    // 60% de estudiantes entregan
                    if (rand(1, 100) <= 60) {
                        $submittedAt = $isPast 
                            ? $dueDate->copy()->subDays(rand(0, 5))
                            : ($dueDate->isPast() ? $dueDate->copy()->subDays(rand(1, 3)) : null);

                        if ($submittedAt) {
                            $esTardia = $submittedAt->isAfter($dueDate);
                            
                            DB::table('submissions')->insert([
                                'assignment_id' => $assignmentId,
                                'estudiante_id' => $inscripcion->estudiante_id,
                                'inscripcion_id' => $inscripcion->id,
                                'archivo_url' => '/storage/assignments/entrega_' . uniqid() . '.pdf',
                                'nombre_archivo_original' => 'Tarea_Estudiante_' . $inscripcion->estudiante_id . '.pdf',
                                'tamano_bytes' => rand(100000, 5000000),
                                'es_tardia' => $esTardia,
                                'nota' => rand(0, 1) === 0 ? null : rand(12, 20), // 50% calificadas
                                'retroalimentacion' => rand(0, 1) === 0 ? null : 'Buen trabajo, sigue así.',
                                'calificado_at' => rand(0, 1) === 0 ? null : $submittedAt->copy()->addDays(rand(1, 5)),
                                'created_at' => $submittedAt,
                                'updated_at' => $submittedAt,
                            ]);

                            $totalSubmissions++;
                        }
                    }
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

                // Obtener user_id del profesor
                $userId = DB::table('users')->where('email', 'profesor@universidad.edu.ve')->value('id');

                $quizId = DB::table('quizzes')->insertGetId([
                    'horario_id' => $horario->id,
                    'titulo' => 'Examen ' . $i . ' - ' . $this->getRandomQuizTitle(),
                    'descripcion' => 'Evaluación de conocimientos del módulo ' . $i,
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
                    'created_by' => $userId,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $totalQuizzes++;

                // Crear preguntas del quiz
                $numQuestions = rand(5, 10);
                for ($q = 1; $q <= $numQuestions; $q++) {
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
                        'puntos' => rand(5, 20),
                        'obligatoria' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                }

                // Crear attempts para quizzes pasados
                if ($isPast) {
                    foreach ($inscripciones as $inscripcion) {
                        // 70% de estudiantes presentan exámenes pasados
                        if (rand(1, 100) <= 70) {
                            $inicioAt = $startDate->copy()->addMinutes(rand(0, 60));
                            $finAt = $inicioAt->copy()->addMinutes(rand(30, 120));
                            $notaObtenida = rand(8, 20);

                            DB::table('quiz_attempts')->insert([
                                'quiz_id' => $quizId,
                                'estudiante_id' => $inscripcion->estudiante_id,
                                'inscripcion_id' => $inscripcion->id,
                                'estado' => 'calificado',
                                'inicio_at' => $inicioAt,
                                'fin_at' => $finAt,
                                'tiempo_restante_seg' => 0,
                                'nota_obtenida' => $notaObtenida,
                                'nota_maxima' => 20,
                                'advertencias_count' => rand(0, 2),
                                'auto_enviado' => false,
                                'created_at' => now(),
                                'updated_at' => now(),
                            ]);

                            $totalAttempts++;
                        }
                    }
                }
            }

            // 3. CREAR SESIONES DE ASISTENCIA (3-5 por horario)
            $numSessions = rand(3, 5);
            $fechasUsadas = [];
            
            for ($i = 1; $i <= $numSessions; $i++) {
                // Generar fecha única para este horario
                do {
                    $sessionDate = Carbon::now()->subDays(rand(1, 30))->startOfDay();
                    $fechaKey = $sessionDate->format('Y-m-d');
                } while (in_array($fechaKey, $fechasUsadas));
                
                $fechasUsadas[] = $fechaKey;
                
                $sessionId = DB::table('attendance_sessions')->insertGetId([
                    'horario_id' => $horario->id,
                    'profesor_id' => $profesorId,
                    'session_date' => $sessionDate->format('Y-m-d'),
                    'codigo_dinamico' => strtoupper(substr(md5(uniqid()), 0, 6)),
                    'codigo_expira_at' => $sessionDate->copy()->addMinutes(10),
                    'abierta' => false,
                    'cierre_automatico_at' => $sessionDate->copy()->addMinutes(15),
                    'total_presentes' => 0,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);

                $totalSessions++;

                // Crear registros de asistencia
                $presentesCount = 0;
                foreach ($inscripciones as $inscripcion) {
                    // 80% de asistencia promedio
                    if (rand(1, 100) <= 80) {
                        DB::table('attendances')->insert([
                            'session_id' => $sessionId,
                            'estudiante_id' => $inscripcion->estudiante_id,
                            'inscripcion_id' => $inscripcion->id,
                            'estatus' => 'presente',
                            'ip_address' => '192.168.1.' . rand(1, 254),
                            'user_agent' => 'Mozilla/5.0',
                            'created_at' => $sessionDate->copy()->addMinutes(rand(0, 10)),
                            'updated_at' => now(),
                        ]);

                        $presentesCount++;
                        $totalAttendances++;
                    }
                }

                // Actualizar total_presentes
                DB::table('attendance_sessions')
                    ->where('id', $sessionId)
                    ->update(['total_presentes' => $presentesCount]);
            }
        }

        $this->command->info('✅ Datos creados exitosamente:');
        $this->command->info("   - Tareas: $totalAssignments (con $totalSubmissions entregas)");
        $this->command->info("   - Exámenes: $totalQuizzes (con $totalAttempts intentos)");
        $this->command->info("   - Sesiones de asistencia: $totalSessions (con $totalAttendances registros)");
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
            'Presentación Oral',
        ];

        return $titles[array_rand($titles)];
    }

    private function getRandomQuizTitle(): string
    {
        $titles = [
            'Parcial',
            'Quiz de Conocimientos',
            'Evaluación Continua',
            'Examen Final',
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
