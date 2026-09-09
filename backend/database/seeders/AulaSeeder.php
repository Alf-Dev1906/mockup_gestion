<?php

namespace Database\Seeders;

use App\Models\Aula;
use App\Models\Facultad;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AulaSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('aulas')->truncate();

        $cantidadAulas = 800; // 800 aulas
        $chunkSize = 100; // REDUCIDO de 200 a 100 para evitar problemas

        echo "Creando {$cantidadAulas} aulas...\n";

        $facultades = Facultad::pluck('id')->toArray();
        $created = 0;

        for ($i = 0; $i < $cantidadAulas; $i += $chunkSize) {
            $aulas = [];
            $remaining = min($chunkSize, $cantidadAulas - $i);

            for ($j = 0; $j < $remaining; $j++) {
                $aula = Aula::factory()->make([
                    'facultad_id' => fake()->randomElement($facultades),
                ]);
                
                $aulas[] = $aula->only([
                    'facultad_id', 'codigo', 'nombre', 'edificio', 'piso', 'capacidad',
                    'tipo', 'equipamiento', 'tiene_proyector', 'tiene_aire_acondicionado',
                    'tiene_computadoras', 'numero_computadoras', 'accesible_discapacitados',
                    'estatus', 'created_at', 'updated_at'
                ]);
            }

            DB::table('aulas')->insert($aulas);
            $created += count($aulas);
            
            $percentage = round(($created / $cantidadAulas) * 100);
            echo "Progreso: {$created}/{$cantidadAulas} ({$percentage}%)\n";
        }

        echo "\n✅ Se crearon {$created} aulas.\n\n";
    }
}
