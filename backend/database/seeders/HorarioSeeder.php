<?php

namespace Database\Seeders;

use App\Models\Horario;
use App\Models\Materia;
use App\Models\Profesor;
use App\Models\Aula;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HorarioSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('horarios')->truncate();

        echo "Creando horarios para el periodo 2026-1...\n";

        $materias = Materia::pluck('id')->toArray();
        $profesores = Profesor::where('estatus', 'activo')->pluck('id')->toArray();
        $aulas = Aula::where('estatus', 'disponible')->pluck('id')->toArray();

        if (empty($profesores) || empty($aulas)) {
            echo "⚠️  No hay profesores activos o aulas disponibles. Ejecute ProfesorSeeder y AulaSeeder primero.\n";
            return;
        }

        // Crear 15,000 horarios (múltiples secciones por materia)
        $cantidadHorarios = 15000;
        $chunkSize = 500; // REDUCIDO de 1000 a 500 para evitar problemas
        $created = 0;

        // Desactivar eventos
        Horario::unsetEventDispatcher();

        for ($i = 0; $i < $cantidadHorarios; $i += $chunkSize) {
            $horarios = [];
            $remaining = min($chunkSize, $cantidadHorarios - $i);

            for ($j = 0; $j < $remaining; $j++) {
                $horario = Horario::factory()->periodoActual()->make([
                    'materia_id' => fake()->randomElement($materias),
                    'profesor_id' => fake()->randomElement($profesores),
                    'aula_id' => fake()->randomElement($aulas),
                ]);
                
                $horarios[] = $horario->only([
                    'materia_id', 'profesor_id', 'aula_id', 'seccion', 'periodo_academico',
                    'anno', 'periodo', 'dia_semana', 'hora_inicio', 'hora_fin',
                    'cupo_maximo', 'cupo_actual', 'lista_espera', 'estatus',
                    'fecha_inicio', 'fecha_fin', 'created_at', 'updated_at'
                ]);
            }

            DB::table('horarios')->insert($horarios);
            $created += count($horarios);
            
            $percentage = round(($created / $cantidadHorarios) * 100);
            echo "Progreso: {$created}/{$cantidadHorarios} ({$percentage}%)\n";
            
            unset($horarios);
        }

        echo "\n✅ Se crearon {$created} horarios para el periodo 2026-1.\n\n";
    }
}
