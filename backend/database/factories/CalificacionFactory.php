<?php

namespace Database\Factories;

use App\Models\Calificacion;
use App\Models\Inscripcion;
use Illuminate\Database\Eloquent\Factories\Factory;

class CalificacionFactory extends Factory
{
    protected $model = Calificacion::class;

    public function definition(): array
    {
        // Generar notas realistas (sistema venezolano 0-20)
        $nota1 = fake()->randomFloat(2, 8, 20);
        $nota2 = fake()->randomFloat(2, 8, 20);
        $nota3 = fake()->randomFloat(2, 8, 20);
        
        $notaFinal = round(($nota1 + $nota2 + $nota3) / 3, 2);
        
        // Determinar estatus
        $estatus = 'cursando';
        if ($nota1 && $nota2 && $nota3) {
            $estatus = $notaFinal >= 10.00 ? 'aprobado' : 'reprobado';
        }

        return [
            'inscripcion_id' => Inscripcion::factory(),
            'nota_corte_1' => $nota1,
            'nota_corte_2' => $nota2,
            'nota_corte_3' => $nota3,
            'nota_final' => $notaFinal,
            'estatus' => $estatus,
            'asistencias' => fake()->numberBetween(60, 100),
            'observaciones' => fake()->optional(0.3)->sentence(),
        ];
    }

    public function aprobado(): static
    {
        return $this->state(function (array $attributes) {
            $nota1 = fake()->randomFloat(2, 12, 20);
            $nota2 = fake()->randomFloat(2, 12, 20);
            $nota3 = fake()->randomFloat(2, 12, 20);
            $notaFinal = round(($nota1 + $nota2 + $nota3) / 3, 2);
            
            return [
                'nota_corte_1' => $nota1,
                'nota_corte_2' => $nota2,
                'nota_corte_3' => $nota3,
                'nota_final' => $notaFinal,
                'estatus' => 'aprobado',
                'asistencias' => fake()->numberBetween(80, 100),
            ];
        });
    }

    public function reprobado(): static
    {
        return $this->state(function (array $attributes) {
            $nota1 = fake()->randomFloat(2, 1, 9);
            $nota2 = fake()->randomFloat(2, 1, 9);
            $nota3 = fake()->randomFloat(2, 1, 9);
            $notaFinal = round(($nota1 + $nota2 + $nota3) / 3, 2);
            
            return [
                'nota_corte_1' => $nota1,
                'nota_corte_2' => $nota2,
                'nota_corte_3' => $nota3,
                'nota_final' => $notaFinal,
                'estatus' => 'reprobado',
                'asistencias' => fake()->numberBetween(30, 70),
            ];
        });
    }

    public function cursando(): static
    {
        return $this->state(function (array $attributes) {
            return [
                'nota_corte_1' => null,
                'nota_corte_2' => null,
                'nota_corte_3' => null,
                'nota_final' => null,
                'estatus' => 'cursando',
                'asistencias' => fake()->numberBetween(50, 95),
            ];
        });
    }
}
