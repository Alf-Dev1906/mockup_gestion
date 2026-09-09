<?php

namespace Database\Factories;

use App\Models\Facultad;
use Illuminate\Database\Eloquent\Factories\Factory;

class FacultadFactory extends Factory
{
    protected $model = Facultad::class;

    public function definition(): array
    {
        $facultades = [
            ['nombre' => 'Facultad de Ingeniería', 'codigo' => 'ING'],
            ['nombre' => 'Facultad de Ciencias', 'codigo' => 'CIE'],
            ['nombre' => 'Facultad de Humanidades y Educación', 'codigo' => 'HYE'],
            ['nombre' => 'Facultad de Ciencias Económicas y Sociales', 'codigo' => 'CES'],
            ['nombre' => 'Facultad de Ciencias Jurídicas y Políticas', 'codigo' => 'CJP'],
            ['nombre' => 'Facultad de Medicina', 'codigo' => 'MED'],
            ['nombre' => 'Facultad de Odontología', 'codigo' => 'ODO'],
            ['nombre' => 'Facultad de Farmacia', 'codigo' => 'FAR'],
            ['nombre' => 'Facultad de Arquitectura y Urbanismo', 'codigo' => 'ARQ'],
            ['nombre' => 'Facultad de Ciencias Veterinarias', 'codigo' => 'VET'],
        ];

        $facultad = fake()->randomElement($facultades);

        return [
            'codigo' => $facultad['codigo'],
            'nombre' => $facultad['nombre'],
            'decano' => fake()->name(),
            'email' => strtolower(str_replace(' ', '', $facultad['codigo'])) . '@universidad.edu.ve',
            'telefono' => '0212-' . fake()->numerify('#######'),
            'descripcion' => fake()->paragraph(3),
            'activo' => fake()->boolean(95), // 95% activas
        ];
    }

    public function activo(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => true,
        ]);
    }
}
