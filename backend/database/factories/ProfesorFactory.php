<?php

namespace Database\Factories;

use App\Models\Profesor;
use App\Models\Facultad;
use Illuminate\Database\Eloquent\Factories\Factory;

class ProfesorFactory extends Factory
{
    protected $model = Profesor::class;

    public function definition(): array
    {
        static $counter = 0;
        $counter++;
        
        $nombre = fake()->firstName();
        $apellido = fake()->lastName();
        $tipoDocumento = fake()->randomElement(['V', 'E']);
        $cedula = $tipoDocumento . '-' . fake()->unique()->numberBetween(5000000, 25000000);
        
        $especialidades = [
            'Matemáticas', 'Física', 'Química', 'Programación', 'Bases de Datos',
            'Redes', 'Inteligencia Artificial', 'Estructuras', 'Mecánica', 'Electrónica',
            'Derecho Civil', 'Derecho Penal', 'Anatomía', 'Fisiología', 'Farmacología',
            'Economía', 'Contabilidad', 'Administración', 'Marketing', 'Finanzas',
            'Historia', 'Literatura', 'Filosofía', 'Sociología', 'Psicología'
        ];

        $titulos = [
            'Dr.', 'Dra.', 'Msc.', 'Ing.', 'Lic.', 'Prof.', 'Abg.', 'Arq.'
        ];

        return [
            'facultad_id' => Facultad::factory(),
            'cedula' => $cedula,
            'nombre' => $nombre,
            'apellido' => $apellido,
            'email' => strtolower($nombre . '.' . $apellido . $counter) . '@profesor.edu.ve',
            'telefono' => '0' . fake()->randomElement(['212', '412', '414', '424']) . '-' . fake()->numerify('#######'),
            'fecha_nacimiento' => fake()->dateTimeBetween('-65 years', '-25 years'),
            'genero' => fake()->randomElement(['M', 'F']),
            'direccion' => fake()->streetAddress(),
            'foto_url' => null,
            'codigo_empleado' => 'P-' . fake()->unique()->numerify('######'),
            'fecha_contratacion' => fake()->dateTimeBetween('-20 years', '-1 year'),
            'tipo_contrato' => fake()->randomElement(['tiempo_completo', 'tiempo_completo', 'tiempo_completo', 'medio_tiempo', 'hora_clase', 'contratado']),
            'categoria' => fake()->randomElement(['instructor', 'asistente', 'asistente', 'agregado', 'agregado', 'asociado', 'titular']),
            'especialidad' => fake()->randomElement($especialidades),
            'titulo_academico' => fake()->randomElement($titulos),
            'experiencia' => fake()->paragraph(2),
            'horas_semanales' => fake()->randomElement([40, 40, 40, 30, 20, 15, 10]),
            'estatus' => fake()->randomElement(['activo', 'activo', 'activo', 'activo', 'activo', 'licencia', 'inactivo']),
        ];
    }

    public function activo(): static
    {
        return $this->state(fn (array $attributes) => [
            'estatus' => 'activo',
        ]);
    }

    public function tiempoCompleto(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo_contrato' => 'tiempo_completo',
            'horas_semanales' => 40,
        ]);
    }

    public function titular(): static
    {
        return $this->state(fn (array $attributes) => [
            'categoria' => 'titular',
            'titulo_academico' => fake()->randomElement(['Dr.', 'Dra.']),
        ]);
    }
}
