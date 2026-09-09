<?php

namespace Database\Seeders;

use App\Models\Estudiante;
use App\Models\Carrera;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EstudianteSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('estudiantes')->truncate();

        // 100,000 estudiantes para simular alta concurrencia
        $cantidadEstudiantes = 100000;
        $chunkSize = 1000; // REDUCIDO de 2000 a 1000 para evitar problemas de placeholders

        echo "Creando {$cantidadEstudiantes} estudiantes (esto puede tardar varios minutos)...\n";
        echo "Utilizando chunks de {$chunkSize} registros para optimizar memoria.\n\n";

        $carreras = Carrera::pluck('id')->toArray();
        $created = 0;
        $startTime = microtime(true);

        // Desactivar eventos temporalmente para mejor performance
        Estudiante::unsetEventDispatcher();

        for ($i = 0; $i < $cantidadEstudiantes; $i += $chunkSize) {
            $chunkStartTime = microtime(true);
            $estudiantes = [];
            $remaining = min($chunkSize, $cantidadEstudiantes - $i);

            for ($j = 0; $j < $remaining; $j++) {
                $estudiante = Estudiante::factory()->make([
                    'carrera_id' => fake()->randomElement($carreras),
                ]);
                
                // Solo campos de BD, excluir accessors como nombre_completo
                $estudiantes[] = $estudiante->only([
                    'carrera_id', 'cedula', 'nombre', 'apellido', 'email', 'telefono',
                    'fecha_nacimiento', 'genero', 'direccion', 'ciudad', 'estado',
                    'codigo_postal', 'foto_url', 'matricula', 'fecha_ingreso',
                    'semestre_actual', 'indice_academico', 'creditos_aprobados', 'estatus',
                    'contacto_emergencia_nombre', 'contacto_emergencia_telefono',
                    'contacto_emergencia_relacion', 'created_at', 'updated_at'
                ]);
            }

            // Inserción masiva
            DB::table('estudiantes')->insert($estudiantes);
            $created += count($estudiantes);
            
            $chunkTime = round(microtime(true) - $chunkStartTime, 2);
            $percentage = round(($created / $cantidadEstudiantes) * 100, 1);
            $elapsed = round(microtime(true) - $startTime, 2);
            $rate = round($created / $elapsed);
            $estimated = round((($cantidadEstudiantes - $created) / $rate) / 60, 1);
            
            echo sprintf(
                "Progreso: %d/%d (%s%%) | Chunk: %s seg | Total: %s seg | Velocidad: %d/seg | ETA: %s min\n",
                $created,
                $cantidadEstudiantes,
                $percentage,
                $chunkTime,
                $elapsed,
                $rate,
                $estimated
            );
            
            // Limpiar memoria
            unset($estudiantes);
            
            // Pequeña pausa cada 10 chunks para no saturar la BD
            if (($i / $chunkSize) % 10 == 0) {
                usleep(100000); // 0.1 segundos
            }
        }

        $totalTime = round(microtime(true) - $startTime, 2);
        $avgRate = round($cantidadEstudiantes / $totalTime);

        echo "\n✅ Se crearon {$created} estudiantes en {$totalTime} segundos.\n";
        echo "📊 Velocidad promedio: {$avgRate} estudiantes/segundo.\n\n";
    }
}
