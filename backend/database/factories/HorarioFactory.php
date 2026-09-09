<?php

namespace Database\Factories;

use App\Models\Horario;
use App\Models\Materia;
use App\Models\Profesor;
use App\Models\Aula;
use Illuminate\Database\Eloquent\Factories\Factory;

class HorarioFactory extends Factory
{
    protected $model = Horario::class;

    public function definition(): array
    {
        $anno = fake()->numberBetween(2024, 2026);
        $periodo = fake()->numberBetween(1, 2);
        $periodoAcademico = $anno . '-' . $periodo;
        
        $secciones = ['A', 'B', 'C', 'D', 'E', '01', '02', '03', '04'];
        $seccion = fake()->randomElement($secciones);
        
        $dias = ['lunes', 'martes', 'miércoles', 'jueves', 'viernes'];
        $dia = fake()->randomElement($dias);
        
        // Horas de inicio típicas universitarias
        $horasInicio = ['07:00', '08:00', '09:00', '10:00', '11:00', '12:00', '13:00', '14:00', '15:00', '16:00', '17:00', '18:00'];
        $horaInicio = fake()->randomElement($horasInicio);
        
        // Calcular hora fin (2-4 horas después)
        $duracion = fake()->randomElement([2, 3, 4]);
        $horaFin = date('H:i', strtotime($horaInicio . ' +' . $duracion . ' hours'));
        
        $cupoMaximo = fake()->numberBetween(25, 50);
        $cupoActual = fake()->numberBetween(0, $cupoMaximo);
        
        return [
            'materia_id' => Materia::factory(),
            'profesor_id' => Profesor::factory(),
            'aula_id' => Aula::factory(),
            'seccion' => $seccion,
            'periodo_academico' => $periodoAcademico,
            'anno' => $anno,
            'periodo' => $periodo,
            'dia_semana' => $dia,
            'hora_inicio' => $horaInicio,
            'hora_fin' => $horaFin,
            'cupo_maximo' => $cupoMaximo,
            'cupo_actual' => $cupoActual,
            'lista_espera' => fake()->numberBetween(0, 10),
            'estatus' => fake()->randomElement(['abierto', 'abierto', 'en_curso', 'en_curso', 'cerrado', 'finalizado']),
            'fecha_inicio' => fake()->dateTimeBetween('-3 months', '+1 month'),
            'fecha_fin' => fake()->dateTimeBetween('+2 months', '+5 months'),
        ];
    }

    public function periodoActual(): static
    {
        $anno = 2026;
        $periodo = 1;
        
        return $this->state(fn (array $attributes) => [
            'periodo_academico' => $anno . '-' . $periodo,
            'anno' => $anno,
            'periodo' => $periodo,
            'estatus' => 'en_curso',
            'fecha_inicio' => now()->subMonths(2),
            'fecha_fin' => now()->addMonths(3),
        ]);
    }

    public function abierto(): static
    {
        return $this->state(fn (array $attributes) => [
            'estatus' => 'abierto',
        ]);
    }

    public function enCurso(): static
    {
        return $this->state(fn (array $attributes) => [
            'estatus' => 'en_curso',
        ]);
    }

    public function conCuposDisponibles(): static
    {
        return $this->state(function (array $attributes) {
            $cupoMaximo = fake()->numberBetween(30, 50);
            $cupoActual = fake()->numberBetween(0, $cupoMaximo - 10);
            
            return [
                'cupo_maximo' => $cupoMaximo,
                'cupo_actual' => $cupoActual,
                'estatus' => 'abierto',
            ];
        });
    }

    public function lleno(): static
    {
        return $this->state(function (array $attributes) {
            $cupoMaximo = fake()->numberBetween(30, 50);
            
            return [
                'cupo_maximo' => $cupoMaximo,
                'cupo_actual' => $cupoMaximo,
                'estatus' => 'cerrado',
            ];
        });
    }
}
