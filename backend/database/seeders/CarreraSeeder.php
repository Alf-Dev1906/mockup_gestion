<?php

namespace Database\Seeders;

use App\Models\Carrera;
use App\Models\Facultad;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CarreraSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('carreras')->truncate();

        echo "Creando carreras por facultad...\n";

        $facultades = Facultad::all();
        
        $carrerasPorFacultad = [
            'ING' => [
                ['codigo' => 'ISI', 'nombre' => 'Ingeniería de Sistemas', 'titulo' => 'Ingeniero de Sistemas', 'semestres' => 10, 'creditos' => 200],
                ['codigo' => 'ICI', 'nombre' => 'Ingeniería Civil', 'titulo' => 'Ingeniero Civil', 'semestres' => 10, 'creditos' => 210],
                ['codigo' => 'IEL', 'nombre' => 'Ingeniería Eléctrica', 'titulo' => 'Ingeniero Electricista', 'semestres' => 10, 'creditos' => 205],
                ['codigo' => 'IME', 'nombre' => 'Ingeniería Mecánica', 'titulo' => 'Ingeniero Mecánico', 'semestres' => 10, 'creditos' => 205],
                ['codigo' => 'IQU', 'nombre' => 'Ingeniería Química', 'titulo' => 'Ingeniero Químico', 'semestres' => 10, 'creditos' => 200],
            ],
            'CIE' => [
                ['codigo' => 'BIO', 'nombre' => 'Biología', 'titulo' => 'Licenciado en Biología', 'semestres' => 10, 'creditos' => 175],
                ['codigo' => 'QUI', 'nombre' => 'Química', 'titulo' => 'Licenciado en Química', 'semestres' => 10, 'creditos' => 175],
                ['codigo' => 'MAT', 'nombre' => 'Matemáticas', 'titulo' => 'Licenciado en Matemáticas', 'semestres' => 10, 'creditos' => 170],
                ['codigo' => 'FIS', 'nombre' => 'Física', 'titulo' => 'Licenciado en Física', 'semestres' => 10, 'creditos' => 170],
            ],
            'HYE' => [
                ['codigo' => 'EDU', 'nombre' => 'Educación', 'titulo' => 'Licenciado en Educación', 'semestres' => 8, 'creditos' => 160],
                ['codigo' => 'COM', 'nombre' => 'Comunicación Social', 'titulo' => 'Licenciado en Comunicación Social', 'semestres' => 8, 'creditos' => 155],
                ['codigo' => 'PSI', 'nombre' => 'Psicología', 'titulo' => 'Licenciado en Psicología', 'semestres' => 10, 'creditos' => 180],
                ['codigo' => 'HIS', 'nombre' => 'Historia', 'titulo' => 'Licenciado en Historia', 'semestres' => 8, 'creditos' => 150],
            ],
            'CES' => [
                ['codigo' => 'ADM', 'nombre' => 'Administración', 'titulo' => 'Licenciado en Administración', 'semestres' => 8, 'creditos' => 160],
                ['codigo' => 'CPA', 'nombre' => 'Contaduría Pública', 'titulo' => 'Licenciado en Contaduría Pública', 'semestres' => 8, 'creditos' => 165],
                ['codigo' => 'ECO', 'nombre' => 'Economía', 'titulo' => 'Licenciado en Economía', 'semestres' => 8, 'creditos' => 165],
            ],
            'CJP' => [
                ['codigo' => 'DER', 'nombre' => 'Derecho', 'titulo' => 'Abogado', 'semestres' => 10, 'creditos' => 180],
                ['codigo' => 'CIP', 'nombre' => 'Ciencias Políticas', 'titulo' => 'Licenciado en Ciencias Políticas', 'semestres' => 8, 'creditos' => 160],
            ],
            'MED' => [
                ['codigo' => 'MED', 'nombre' => 'Medicina', 'titulo' => 'Médico Cirujano', 'semestres' => 12, 'creditos' => 280],
                ['codigo' => 'ENF', 'nombre' => 'Enfermería', 'titulo' => 'Licenciado en Enfermería', 'semestres' => 8, 'creditos' => 170],
            ],
            'ODO' => [
                ['codigo' => 'ODO', 'nombre' => 'Odontología', 'titulo' => 'Odontólogo', 'semestres' => 10, 'creditos' => 240],
            ],
            'FAR' => [
                ['codigo' => 'FAR', 'nombre' => 'Farmacia', 'titulo' => 'Licenciado en Farmacia', 'semestres' => 10, 'creditos' => 190],
            ],
            'ARQ' => [
                ['codigo' => 'ARQ', 'nombre' => 'Arquitectura', 'titulo' => 'Arquitecto', 'semestres' => 10, 'creditos' => 220],
                ['codigo' => 'URB', 'nombre' => 'Urbanismo', 'titulo' => 'Licenciado en Urbanismo', 'semestres' => 8, 'creditos' => 170],
            ],
            'VET' => [
                ['codigo' => 'VET', 'nombre' => 'Medicina Veterinaria', 'titulo' => 'Médico Veterinario', 'semestres' => 10, 'creditos' => 220],
            ],
        ];

        $totalCarreras = 0;

        foreach ($facultades as $facultad) {
            if (isset($carrerasPorFacultad[$facultad->codigo])) {
                foreach ($carrerasPorFacultad[$facultad->codigo] as $carreraData) {
                    $nivel = $facultad->codigo === 'ING' ? 'ingenieria' : 'licenciatura';

                    Carrera::create([
                        'facultad_id' => $facultad->id,
                        'codigo' => $carreraData['codigo'],
                        'nombre' => $carreraData['nombre'],
                        'nivel' => $nivel,
                        'titulo_otorgado' => $carreraData['titulo'],
                        'duracion_semestres' => $carreraData['semestres'],
                        'creditos_totales' => $carreraData['creditos'],
                        'coordinador' => fake()->name(),
                        'descripcion' => "Formación profesional de excelencia en {$carreraData['nombre']}.",
                        'modalidad' => 'presencial',
                        'activo' => true,
                    ]);
                    
                    echo "✓ {$facultad->codigo}: {$carreraData['nombre']}\n";
                    $totalCarreras++;
                }
            }
        }

        echo "\n✅ Se crearon {$totalCarreras} carreras.\n\n";
    }
}
