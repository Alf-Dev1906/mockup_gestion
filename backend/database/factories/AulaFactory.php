<?php

namespace Database\Factories;

use App\Models\Aula;
use App\Models\Facultad;
use Illuminate\Database\Eloquent\Factories\Factory;

class AulaFactory extends Factory
{
    protected $model = Aula::class;

    public function definition(): array
    {
        static $counter = 0;
        $counter++;
        
        $edificios = ['A', 'B', 'C', 'D', 'E', 'F', 'G', 'H'];
        $edificio = fake()->randomElement($edificios);
        $piso = fake()->numberBetween(1, 5);
        
        // Usar contador para garantizar códigos únicos
        $codigo = $edificio . '-' . str_pad($counter, 3, '0', STR_PAD_LEFT);

        $tipos = ['aula_regular', 'aula_regular', 'aula_regular', 'aula_regular', 'laboratorio', 'auditorio', 'salon_conferencias', 'taller'];
        $tipo = fake()->randomElement($tipos);
        
        // Capacidad según tipo
        $capacidades = [
            'aula_regular' => fake()->numberBetween(30, 50),
            'laboratorio' => fake()->numberBetween(20, 35),
            'auditorio' => fake()->numberBetween(80, 200),
            'salon_conferencias' => fake()->numberBetween(40, 80),
            'taller' => fake()->numberBetween(15, 30),
        ];

        $capacidad = $capacidades[$tipo] ?? 30;
        
        return [
            'facultad_id' => Facultad::factory(),
            'codigo' => $codigo,
            'nombre' => 'Aula ' . $codigo,
            'edificio' => 'Edificio ' . $edificio,
            'piso' => 'Piso ' . $piso,
            'capacidad' => $capacidad,
            'tipo' => $tipo,
            'equipamiento' => $this->getEquipamiento($tipo),
            'tiene_proyector' => fake()->boolean(80),
            'tiene_aire_acondicionado' => fake()->boolean(70),
            'tiene_computadoras' => $tipo === 'laboratorio' ? true : fake()->boolean(20),
            'numero_computadoras' => $tipo === 'laboratorio' ? fake()->numberBetween(20, 35) : 0,
            'accesible_discapacitados' => $piso == 1 ? true : fake()->boolean(60),
            'estatus' => fake()->randomElement(['disponible', 'disponible', 'disponible', 'disponible', 'mantenimiento', 'fuera_servicio']),
        ];
    }

    private function getEquipamiento($tipo): string
    {
        $equipamientos = [
            'aula_regular' => 'Pizarra acrílica, pupitres individuales',
            'laboratorio' => 'Mesas de trabajo, computadoras, equipos especializados',
            'auditorio' => 'Sistema de sonido, proyector, pantalla grande, butacas',
            'salon_conferencias' => 'Mesa de conferencias, sillas ejecutivas, sistema audiovisual',
            'taller' => 'Mesas de trabajo grandes, herramientas, equipos especializados',
        ];

        return $equipamientos[$tipo] ?? 'Equipamiento estándar';
    }

    public function disponible(): static
    {
        return $this->state(fn (array $attributes) => [
            'estatus' => 'disponible',
        ]);
    }

    public function laboratorio(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => 'laboratorio',
            'tiene_computadoras' => true,
            'numero_computadoras' => fake()->numberBetween(20, 35),
            'capacidad' => fake()->numberBetween(20, 35),
        ]);
    }

    public function auditorio(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => 'auditorio',
            'capacidad' => fake()->numberBetween(80, 200),
            'tiene_proyector' => true,
            'tiene_aire_acondicionado' => true,
        ]);
    }
}
