<?php

namespace Database\Seeders;

use App\Models\Inscripcion;
use App\Models\Estudiante;
use App\Models\Horario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class InscripcionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('inscripciones')->truncate();

        echo "Creando inscripciones masivas...\n";
        echo "⚠️  Nota: Este proceso puede tardar varios minutos debido al volumen.\n\n";

        $estudiantes = Estudiante::where('estatus', 'activo')->pluck('id')->toArray();
        $horarios = Horario::where('estatus', 'en_curso')->pluck('id')->toArray();

        if (empty($estudiantes)) {
            echo "⚠️  No hay estudiantes activos. Ejecute EstudianteSeeder primero.\n";
            return;
        }

        if (empty($horarios)) {
            echo "⚠️  No hay horarios en curso. Ejecute HorarioSeeder primero.\n";
            return;
        }

        // Cada estudiante inscrito en 4-6 materias promedio
        $inscripcionesPorEstudiante = 5;
        $estudiantesMuestra = min(50000, count($estudiantes)); // Limitar a 50K estudiantes
        $totalInscripciones = $estudiantesMuestra * $inscripcionesPorEstudiante;
        $chunkSize = 1000; // REDUCIDO de 5000 a 1000 para evitar exceder placeholders
        $created = 0;
        $startTime = microtime(true);

        echo "Procesando {$estudiantesMuestra} estudiantes × {$inscripcionesPorEstudiante} materias = ~{$totalInscripciones} inscripciones\n\n";

        // Desactivar eventos
        Inscripcion::unsetEventDispatcher();

        // Tomar muestra de estudiantes
        $estudiantesMuestreados = array_slice($estudiantes, 0, $estudiantesMuestra);

        foreach (array_chunk($estudiantesMuestreados, $chunkSize / $inscripcionesPorEstudiante) as $chunkEstudiantes) {
            $inscripciones = [];

            foreach ($chunkEstudiantes as $estudianteId) {
                // Cada estudiante se inscribe en 4-6 materias aleatorias
                $numMaterias = rand(4, 6);
                $horariosSeleccionados = fake()->randomElements($horarios, $numMaterias);

                foreach ($horariosSeleccionados as $horarioId) {
                    $inscripcion = Inscripcion::factory()->make([
                        'estudiante_id' => $estudianteId,
                        'horario_id' => $horarioId,
                        'periodo_academico' => '2026-1',
                    ]);
                    
                    $inscripciones[] = $inscripcion->only([
                        'estudiante_id', 'horario_id', 'periodo_academico', 'fecha_inscripcion',
                        'estatus', 'nota_parcial_1', 'nota_parcial_2', 'nota_parcial_3',
                        'nota_final', 'inasistencias', 'porcentaje_asistencia',
                        'fecha_retiro', 'fecha_calificacion_final', 'observaciones',
                        'created_at', 'updated_at'
                    ]);
                }
            }

            // Inserción masiva
            DB::table('inscripciones')->insert($inscripciones);
            $created += count($inscripciones);
            
            $elapsed = round(microtime(true) - $startTime, 2);
            $rate = round($created / $elapsed);
            $percentage = round(($created / $totalInscripciones) * 100, 1);
            
            echo sprintf(
                "Progreso: %d/%d (%s%%) | Tiempo: %s seg | Velocidad: %d/seg\n",
                $created,
                $totalInscripciones,
                $percentage,
                $elapsed,
                $rate
            );
            
            unset($inscripciones);
            
            // Pausa para no saturar
            usleep(50000); // 0.05 segundos
        }

        $totalTime = round(microtime(true) - $startTime, 2);
        
        echo "\n✅ Se crearon {$created} inscripciones en {$totalTime} segundos.\n";
        echo "📊 Promedio: " . round($created / $estudiantesMuestra, 1) . " materias por estudiante.\n\n";
    }
}
