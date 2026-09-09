<?php

namespace Database\Factories;

use App\Models\Estudiante;
use App\Models\Carrera;
use Illuminate\Database\Eloquent\Factories\Factory;

class EstudianteFactory extends Factory
{
    protected $model = Estudiante::class;

    public function definition(): array
    {
        static $counter = 0;
        $counter++;
        
        $nombre = fake()->firstName();
        $apellido = fake()->lastName();
        $tipoDocumento = fake()->randomElement(['V', 'E']);
        $cedula = $tipoDocumento . '-' . fake()->unique()->numberBetween(10000000, 30000000);
        
        // Fecha de ingreso entre 1 y 5 años atrás
        $fechaIngreso = fake()->dateTimeBetween('-5 years', '-1 year');
        $semestreActual = min(10, max(1, fake()->numberBetween(1, 10)));
        
        $estados = [
            'Distrito Capital', 'Miranda', 'Vargas', 'Aragua', 'Carabobo',
            'Zulia', 'Lara', 'Táchira', 'Mérida', 'Bolívar', 'Anzoátegui',
            'Monagas', 'Sucre', 'Nueva Esparta', 'Falcón'
        ];

        return [
            'carrera_id' => Carrera::factory(),
            'cedula' => $cedula,
            'nombre' => $nombre,
            'apellido' => $apellido,
            'email' => strtolower($nombre . '.' . $apellido . $counter) . '@estudiantes.edu.ve',
            'telefono' => '0' . fake()->randomElement(['412', '414', '424', '416', '426']) . '-' . fake()->numerify('#######'),
            'fecha_nacimiento' => fake()->dateTimeBetween('-30 years', '-18 years'),
            'genero' => fake()->randomElement(['M', 'F']),
            'direccion' => fake()->streetAddress(),
            'ciudad' => fake()->city(),
            'estado' => fake()->randomElement($estados),
            'codigo_postal' => fake()->numerify('####'),
            'foto_url' => null,
            'matricula' => fake()->unique()->numerify('##########'),
            'fecha_ingreso' => $fechaIngreso,
            'semestre_actual' => $semestreActual,
            'indice_academico' => fake()->randomFloat(2, 10, 20), // Escala 0-20
            'creditos_aprobados' => fake()->numberBetween(0, 180),
            'estatus' => fake()->randomElement(['activo', 'activo', 'activo', 'activo', 'activo', 'inactivo', 'retirado']),
            'contacto_emergencia_nombre' => fake()->name(),
            'contacto_emergencia_telefono' => '0' . fake()->randomElement(['412', '414', '424']) . '-' . fake()->numerify('#######'),
            'contacto_emergencia_relacion' => fake()->randomElement(['Padre', 'Madre', 'Hermano/a', 'Tío/a', 'Cónyuge', 'Otro']),
        ];
    }

    public function activo(): static
    {
        return $this->state(fn (array $attributes) => [
            'estatus' => 'activo',
        ]);
    }

    public function egresado(): static
    {
        return $this->state(fn (array $attributes) => [
            'estatus' => 'egresado',
            'semestre_actual' => 0,
            'creditos_aprobados' => 200,
            'indice_academico' => fake()->randomFloat(2, 14, 20),
        ]);
    }

    public function primerSemestre(): static
    {
        return $this->state(fn (array $attributes) => [
            'semestre_actual' => 1,
            'creditos_aprobados' => 0,
            'indice_academico' => 0,
            'fecha_ingreso' => now()->subMonths(3),
        ]);
    }
}
