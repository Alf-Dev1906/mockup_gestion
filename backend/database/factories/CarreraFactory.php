<?php

namespace Database\Factories;

use App\Models\Carrera;
use App\Models\Facultad;
use Illuminate\Database\Eloquent\Factories\Factory;

class CarreraFactory extends Factory
{
    protected $model = Carrera::class;

    public function definition(): array
    {
        $carreras = [
            ['nombre' => 'Ingeniería de Sistemas', 'codigo' => 'ISI', 'titulo' => 'Ingeniero de Sistemas', 'semestres' => 10, 'creditos' => 200],
            ['nombre' => 'Ingeniería Civil', 'codigo' => 'ICI', 'titulo' => 'Ingeniero Civil', 'semestres' => 10, 'creditos' => 210],
            ['nombre' => 'Ingeniería Eléctrica', 'codigo' => 'IEL', 'titulo' => 'Ingeniero Electricista', 'semestres' => 10, 'creditos' => 205],
            ['nombre' => 'Ingeniería Mecánica', 'codigo' => 'IME', 'titulo' => 'Ingeniero Mecánico', 'semestres' => 10, 'creditos' => 205],
            ['nombre' => 'Ingeniería Química', 'codigo' => 'IQU', 'titulo' => 'Ingeniero Químico', 'semestres' => 10, 'creditos' => 200],
            ['nombre' => 'Medicina', 'codigo' => 'MED', 'titulo' => 'Médico Cirujano', 'semestres' => 12, 'creditos' => 280],
            ['nombre' => 'Derecho', 'codigo' => 'DER', 'titulo' => 'Abogado', 'semestres' => 10, 'creditos' => 180],
            ['nombre' => 'Administración', 'codigo' => 'ADM', 'titulo' => 'Licenciado en Administración', 'semestres' => 8, 'creditos' => 160],
            ['nombre' => 'Contaduría Pública', 'codigo' => 'CPA', 'titulo' => 'Licenciado en Contaduría Pública', 'semestres' => 8, 'creditos' => 165],
            ['nombre' => 'Economía', 'codigo' => 'ECO', 'titulo' => 'Licenciado en Economía', 'semestres' => 8, 'creditos' => 165],
            ['nombre' => 'Arquitectura', 'codigo' => 'ARQ', 'titulo' => 'Arquitecto', 'semestres' => 10, 'creditos' => 220],
            ['nombre' => 'Educación', 'codigo' => 'EDU', 'titulo' => 'Licenciado en Educación', 'semestres' => 8, 'creditos' => 160],
            ['nombre' => 'Comunicación Social', 'codigo' => 'COM', 'titulo' => 'Licenciado en Comunicación Social', 'semestres' => 8, 'creditos' => 155],
            ['nombre' => 'Psicología', 'codigo' => 'PSI', 'titulo' => 'Licenciado en Psicología', 'semestres' => 10, 'creditos' => 180],
            ['nombre' => 'Biología', 'codigo' => 'BIO', 'titulo' => 'Licenciado en Biología', 'semestres' => 10, 'creditos' => 175],
            ['nombre' => 'Química', 'codigo' => 'QUI', 'titulo' => 'Licenciado en Química', 'semestres' => 10, 'creditos' => 175],
            ['nombre' => 'Matemáticas', 'codigo' => 'MAT', 'titulo' => 'Licenciado en Matemáticas', 'semestres' => 10, 'creditos' => 170],
            ['nombre' => 'Física', 'codigo' => 'FIS', 'titulo' => 'Licenciado en Física', 'semestres' => 10, 'creditos' => 170],
            ['nombre' => 'Odontología', 'codigo' => 'ODO', 'titulo' => 'Odontólogo', 'semestres' => 10, 'creditos' => 240],
            ['nombre' => 'Farmacia', 'codigo' => 'FAR', 'titulo' => 'Licenciado en Farmacia', 'semestres' => 10, 'creditos' => 190],
        ];

        $carrera = fake()->randomElement($carreras);

        return [
            'facultad_id' => Facultad::factory(),
            'codigo' => $carrera['codigo'] . '-' . fake()->unique()->numberBetween(1, 999),
            'nombre' => $carrera['nombre'],
            'titulo_otorgado' => $carrera['titulo'],
            'duracion_semestres' => $carrera['semestres'],
            'creditos_totales' => $carrera['creditos'],
            'coordinador' => fake()->name(),
            'descripcion' => fake()->paragraph(4),
            'modalidad' => fake()->randomElement(['presencial', 'presencial', 'presencial', 'semipresencial', 'distancia']),
            'activo' => fake()->boolean(90),
        ];
    }

    public function activo(): static
    {
        return $this->state(fn (array $attributes) => [
            'activo' => true,
        ]);
    }

    public function presencial(): static
    {
        return $this->state(fn (array $attributes) => [
            'modalidad' => 'presencial',
        ]);
    }
}
