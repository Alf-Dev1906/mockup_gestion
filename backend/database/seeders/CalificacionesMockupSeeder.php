<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CalificacionesMockupSeeder extends Seeder
{
    /**
     * Seed calificaciones de prueba
     */
    public function run(): void
    {
        echo "📊 Creando calificaciones de prueba...\n\n";

        // Obtener inscripciones existentes (limitamos para no saturar)
        $inscripciones = DB::table('inscripciones')
            ->limit(500)  // Crear calificaciones solo para las primeras 500 inscripciones
            ->pluck('id')
            ->toArray();

        if (empty($inscripciones)) {
            echo "   ⚠️  No hay inscripciones, omitiendo calificaciones\n";
            return;
        }

        $count = 0;
        $faker = \Faker\Factory::create('es_VE');

        foreach ($inscripciones as $inscripcionId) {
            // 80% tiene calificaciones, 20% sin calificar aún
            if ($faker->boolean(80)) {
                // Generar notas por cortes (escala 0-20)
                $nota1 = $faker->randomFloat(2, 8, 20);
                $nota2 = $faker->randomFloat(2, 8, 20);
                $nota3 = $faker->randomFloat(2, 8, 20);
                
                // Calcular promedio
                $notaFinal = ($nota1 + $nota2 + $nota3) / 3;
                
                // Determinar estatus
                $estatus = match(true) {
                    $notaFinal >= 10 => 'aprobado',
                    $notaFinal >= 8 => 'aplazado',
                    default => 'reprobado'
                };
                
                DB::table('calificaciones')->insert([
                    'inscripcion_id' => $inscripcionId,
                    'nota_corte_1' => $nota1,
                    'nota_corte_2' => $nota2,
                    'nota_corte_3' => $nota3,
                    'nota_final' => round($notaFinal, 2),
                    'estatus' => $estatus,
                    'asistencias' => $faker->numberBetween(70, 100),
                    'observaciones' => $estatus === 'reprobado' 
                        ? 'Debe presentar reparación' 
                        : ($estatus === 'aplazado' ? 'Presenta examen diferido' : null),
                    'created_at' => now()->subDays(rand(1, 90)),
                    'updated_at' => now(),
                ]);
                
                $count++;
            }
        }

        echo "   ✓ {$count} calificaciones creadas\n";
        echo "   ℹ️  " . (count($inscripciones) - $count) . " inscripciones sin calificar aún\n";
        echo "\n✅ Calificaciones de prueba creadas!\n\n";
    }
}
