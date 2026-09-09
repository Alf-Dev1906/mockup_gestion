<?php

namespace Database\Factories;

use App\Models\Inscripcion;
use App\Models\Estudiante;
use App\Models\Horario;
use Illuminate\Database\Eloquent\Factories\Factory;

class InscripcionFactory extends Factory
{
    protected $model = Inscripcion::class;

    public function definition(): array
    {
        $anno = fake()->numberBetween(2024, 2026);
        $periodo = fake()->numberBetween(1, 2);
        $periodoAcademico = $anno . '-' . $periodo;
        
        $fechaInscripcion = fake()->dateTimeBetween('-5 months', '-2 months');
        
        $estatus = fake()->randomElement([
            'inscrito', 'inscrito',
            'cursando', 'cursando', 'cursando',
            'aprobado', 'aprobado', 'aprobado',
            'reprobado',
            'retirado'
        ]);
        
        // Generar notas según el estatus
        $notas = $this->generarNotas($estatus);
        
        return [
            'estudiante_id' => Estudiante::factory(),
            'horario_id' => Horario::factory(),
            'periodo_academico' => $periodoAcademico,
            'fecha_inscripcion' => $fechaInscripcion,
            'estatus' => $estatus,
            'nota_parcial_1' => $notas['parcial1'],
            'nota_parcial_2' => $notas['parcial2'],
            'nota_parcial_3' => $notas['parcial3'],
            'nota_final' => $notas['final'],
            'inasistencias' => fake()->numberBetween(0, 10),
            'porcentaje_asistencia' => fake()->randomFloat(2, 70, 100),
            'fecha_retiro' => $estatus === 'retirado' ? fake()->dateTimeBetween($fechaInscripcion, 'now') : null,
            'fecha_calificacion_final' => in_array($estatus, ['aprobado', 'reprobado']) ? fake()->dateTimeBetween($fechaInscripcion, 'now') : null,
            'observaciones' => fake()->optional(0.3)->sentence(),
        ];
    }

    private function generarNotas($estatus): array
    {
        $notas = [
            'parcial1' => null,
            'parcial2' => null,
            'parcial3' => null,
            'final' => null,
        ];

        switch ($estatus) {
            case 'aprobado':
                $notas['parcial1'] = fake()->randomFloat(2, 10, 20);
                $notas['parcial2'] = fake()->randomFloat(2, 10, 20);
                $notas['parcial3'] = fake()->randomFloat(2, 10, 20);
                $notas['final'] = fake()->randomFloat(2, 10, 20);
                break;

            case 'reprobado':
                $notas['parcial1'] = fake()->randomFloat(2, 0, 15);
                $notas['parcial2'] = fake()->randomFloat(2, 0, 15);
                $notas['parcial3'] = fake()->randomFloat(2, 0, 15);
                $notas['final'] = fake()->randomFloat(2, 0, 9);
                break;

            case 'cursando':
                $notas['parcial1'] = fake()->randomFloat(2, 5, 20);
                $notas['parcial2'] = fake()->optional(0.7)->randomFloat(2, 5, 20);
                $notas['parcial3'] = fake()->optional(0.3)->randomFloat(2, 5, 20);
                break;

            case 'inscrito':
                // Sin notas aún
                break;

            case 'retirado':
                $notas['parcial1'] = fake()->optional(0.5)->randomFloat(2, 0, 20);
                break;
        }

        return $notas;
    }

    public function aprobado(): static
    {
        return $this->state(fn (array $attributes) => [
            'estatus' => 'aprobado',
            'nota_parcial_1' => fake()->randomFloat(2, 12, 20),
            'nota_parcial_2' => fake()->randomFloat(2, 12, 20),
            'nota_parcial_3' => fake()->randomFloat(2, 12, 20),
            'nota_final' => fake()->randomFloat(2, 12, 20),
            'porcentaje_asistencia' => fake()->randomFloat(2, 85, 100),
            'inasistencias' => fake()->numberBetween(0, 5),
        ]);
    }

    public function reprobado(): static
    {
        return $this->state(fn (array $attributes) => [
            'estatus' => 'reprobado',
            'nota_parcial_1' => fake()->randomFloat(2, 0, 12),
            'nota_parcial_2' => fake()->randomFloat(2, 0, 12),
            'nota_parcial_3' => fake()->randomFloat(2, 0, 12),
            'nota_final' => fake()->randomFloat(2, 0, 9),
        ]);
    }

    public function cursando(): static
    {
        return $this->state(fn (array $attributes) => [
            'estatus' => 'cursando',
            'nota_parcial_1' => fake()->randomFloat(2, 8, 20),
            'nota_parcial_2' => fake()->optional(0.8)->randomFloat(2, 8, 20),
            'nota_parcial_3' => null,
            'nota_final' => null,
        ]);
    }

    public function inscrito(): static
    {
        return $this->state(fn (array $attributes) => [
            'estatus' => 'inscrito',
            'nota_parcial_1' => null,
            'nota_parcial_2' => null,
            'nota_parcial_3' => null,
            'nota_final' => null,
            'fecha_inscripcion' => now()->subDays(fake()->numberBetween(1, 30)),
        ]);
    }
}
