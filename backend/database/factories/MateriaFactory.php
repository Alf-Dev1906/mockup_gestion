<?php

namespace Database\Factories;

use App\Models\Materia;
use App\Models\Carrera;
use Illuminate\Database\Eloquent\Factories\Factory;

class MateriaFactory extends Factory
{
    protected $model = Materia::class;

    public function definition(): array
    {
        static $counter = 0;
        $counter++;
        
        $materias = [
            // Ingeniería y Ciencias
            'Cálculo I', 'Cálculo II', 'Cálculo III', 'Álgebra Lineal', 'Geometría Analítica',
            'Física I', 'Física II', 'Física III', 'Química General', 'Química Orgánica',
            'Programación I', 'Programación II', 'Estructuras de Datos', 'Algoritmos',
            'Bases de Datos I', 'Bases de Datos II', 'Redes de Computadoras',
            'Sistemas Operativos', 'Ingeniería de Software', 'Inteligencia Artificial',
            'Mecánica de Fluidos', 'Termodinámica', 'Resistencia de Materiales',
            'Circuitos Eléctricos', 'Electrónica Digital', 'Electrónica Analógica',
            
            // Ciencias Sociales y Humanidades
            'Introducción al Derecho', 'Derecho Civil', 'Derecho Penal', 'Derecho Mercantil',
            'Contabilidad I', 'Contabilidad II', 'Finanzas', 'Microeconomía', 'Macroeconomía',
            'Administración I', 'Administración II', 'Marketing', 'Recursos Humanos',
            'Metodología de la Investigación', 'Estadística', 'Estadística Aplicada',
            'Filosofía', 'Ética Profesional', 'Sociología', 'Psicología General',
            
            // Medicina y Salud
            'Anatomía Humana', 'Fisiología', 'Bioquímica', 'Microbiología', 'Parasitología',
            'Farmacología', 'Patología', 'Medicina Interna', 'Cirugía General',
        ];

        $nombreMateria = fake()->randomElement($materias);
        $semestre = fake()->numberBetween(1, 10);
        $creditos = fake()->randomElement([2, 3, 4, 5]);
        
        // Código único usando contador
        $codigoBase = fake()->regexify('[A-Z]{3}');
        $codigo = $codigoBase . str_pad($counter, 4, '0', STR_PAD_LEFT);
        
        return [
            'carrera_id' => Carrera::factory(),
            'codigo' => $codigo,
            'nombre' => $nombreMateria,
            'descripcion' => fake()->paragraph(3),
            'creditos' => $creditos,
            'horas_teoricas' => fake()->numberBetween(2, 4),
            'horas_practicas' => fake()->numberBetween(0, 3),
            'horas_laboratorio' => fake()->numberBetween(0, 2),
            'semestre_recomendado' => $semestre,
            'tipo' => fake()->randomElement(['obligatoria', 'obligatoria', 'obligatoria', 'electiva', 'especialidad']),
            'competencias' => fake()->paragraph(2),
            'bibliografia' => fake()->paragraph(1),
            'activo' => fake()->boolean(95),
        ];
    }

    public function obligatoria(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => 'obligatoria',
        ]);
    }

    public function electiva(): static
    {
        return $this->state(fn (array $attributes) => [
            'tipo' => 'electiva',
        ]);
    }

    public function primerSemestre(): static
    {
        return $this->state(fn (array $attributes) => [
            'semestre_recomendado' => 1,
            'tipo' => 'obligatoria',
        ]);
    }
}
