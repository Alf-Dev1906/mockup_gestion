<?php

namespace Database\Seeders;

use App\Models\Materia;
use App\Models\Carrera;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class MateriaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('materia_prerrequisitos')->truncate();
        DB::table('materias')->truncate();

        echo "Creando materias por carrera...\n";

        $carreras = Carrera::all();
        $totalMaterias = 0;

        foreach ($carreras as $carrera) {
            // Cada carrera tendrá entre 40-60 materias
            $cantidadMaterias = rand(40, 60);
            
            $materias = [];
            for ($i = 0; $i < $cantidadMaterias; $i++) {
                $materia = Materia::factory()->make([
                    'carrera_id' => $carrera->id,
                ]);
                
                $materias[] = $materia->only([
                    'carrera_id', 'codigo', 'nombre', 'descripcion', 'creditos',
                    'horas_teoricas', 'horas_practicas', 'horas_laboratorio',
                    'semestre_recomendado', 'tipo', 'competencias', 'bibliografia',
                    'activo', 'created_at', 'updated_at'
                ]);
            }

            DB::table('materias')->insert($materias);
            $totalMaterias += count($materias);
            
            echo "✓ {$carrera->nombre}: {$cantidadMaterias} materias\n";
        }

        echo "\n✅ Se crearon {$totalMaterias} materias.\n\n";

        // Crear algunos prerrequisitos de ejemplo
        echo "Creando prerrequisitos de ejemplo...\n";
        
        $todasLasMaterias = Materia::all();
        $prereqsCreados = 0;

        foreach ($carreras as $carrera) {
            $materias = $carrera->materias()->orderBy('semestre_recomendado')->get();
            
            foreach ($materias as $index => $materia) {
                // Las materias de semestre 3+ pueden tener prerrequisitos
                if ($materia->semestre_recomendado >= 3 && $index > 0) {
                    // 30% de probabilidad de tener 1-2 prerrequisitos
                    if (rand(1, 100) <= 30) {
                        $numPrereqs = rand(1, 2);
                        $posiblesPrereqs = $materias->where('semestre_recomendado', '<', $materia->semestre_recomendado)
                                                    ->where('id', '!=', $materia->id)
                                                    ->take($numPrereqs);
                        
                        foreach ($posiblesPrereqs as $prereq) {
                            $materia->prerrequisitos()->attach($prereq->id);
                            $prereqsCreados++;
                        }
                    }
                }
            }
        }

        echo "✅ Se crearon {$prereqsCreados} relaciones de prerrequisitos.\n\n";
    }
}
