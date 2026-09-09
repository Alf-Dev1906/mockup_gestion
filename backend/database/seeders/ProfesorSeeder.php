<?php

namespace Database\Seeders;

use App\Models\Profesor;
use App\Models\Facultad;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ProfesorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('profesores')->truncate();

        $cantidadProfesores = 5000; // 5,000 profesores
        $chunkSize = 250; // REDUCIDO de 500 a 250 para evitar problemas

        echo "Creando {$cantidadProfesores} profesores...\n";

        $facultades = Facultad::pluck('id')->toArray();
        $created = 0;

        // Crear en chunks para optimizar memoria
        for ($i = 0; $i < $cantidadProfesores; $i += $chunkSize) {
            $profesores = [];
            $remaining = min($chunkSize, $cantidadProfesores - $i);

            for ($j = 0; $j < $remaining; $j++) {
                $profesor = Profesor::factory()->make([
                    'facultad_id' => fake()->randomElement($facultades),
                ]);
                
                // Solo incluir los campos de la BD, excluir accessors
                $profesores[] = $profesor->only([
                    'facultad_id', 'cedula', 'nombre', 'apellido', 'email', 'telefono',
                    'fecha_nacimiento', 'genero', 'direccion', 'foto_url', 'codigo_empleado',
                    'fecha_contratacion', 'tipo_contrato', 'categoria', 'especialidad',
                    'titulo_academico', 'experiencia', 'horas_semanales', 'estatus',
                    'created_at', 'updated_at'
                ]);
            }

            DB::table('profesores')->insert($profesores);
            $created += count($profesores);
            
            $percentage = round(($created / $cantidadProfesores) * 100);
            echo "Progreso: {$created}/{$cantidadProfesores} ({$percentage}%)\n";
        }

        echo "\n✅ Se crearon {$created} profesores.\n\n";
    }
}
