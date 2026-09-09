<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CalificacionSeeder extends Seeder
{
    /**
     * Inserta calificaciones por cursor de IDs (sin Eloquent ni whereDoesntHave)
     * para no agotar memoria con ~250k inscripciones.
     */
    public function run(): void
    {
        DB::connection()->disableQueryLog();

        echo "Generando calificaciones por chunks...\n";

        $chunkSize = 400;
        $lastId = 0;
        $created = 0;
        $now = now();

        while (true) {
            $rows = DB::table('inscripciones as i')
                ->leftJoin('calificaciones as c', function ($join) {
                    $join->on('c.inscripcion_id', '=', 'i.id')
                        ->whereNull('c.deleted_at');
                })
                ->whereNull('c.id')
                ->whereIn('i.estatus', ['inscrito', 'aprobado', 'reprobado'])
                ->whereNull('i.deleted_at')
                ->where('i.id', '>', $lastId)
                ->orderBy('i.id')
                ->limit($chunkSize)
                ->get(['i.id']);

            if ($rows->isEmpty()) {
                break;
            }

            $batch = [];

            foreach ($rows as $row) {
                $lastId = (int) $row->id;
                $rand = random_int(1, 100);

                if ($rand <= 70) {
                    $nota1 = $this->notaAleatoria(12, 20);
                    $nota2 = $this->notaAleatoria(12, 20);
                    $nota3 = $this->notaAleatoria(12, 20);
                    $estatus = 'aprobado';
                    $asistencias = random_int(80, 100);
                } elseif ($rand <= 90) {
                    $nota1 = $this->notaAleatoria(1, 9);
                    $nota2 = $this->notaAleatoria(1, 9);
                    $nota3 = $this->notaAleatoria(1, 9);
                    $estatus = 'reprobado';
                    $asistencias = random_int(30, 70);
                } else {
                    $nota1 = $nota2 = $nota3 = null;
                    $estatus = 'cursando';
                    $asistencias = random_int(50, 95);
                }

                $notaFinal = ($nota1 === null)
                    ? null
                    : round(($nota1 + $nota2 + $nota3) / 3, 2);

                $batch[] = [
                    'inscripcion_id' => $row->id,
                    'nota_corte_1' => $nota1,
                    'nota_corte_2' => $nota2,
                    'nota_corte_3' => $nota3,
                    'nota_final' => $notaFinal,
                    'estatus' => $estatus,
                    'asistencias' => $asistencias,
                    'observaciones' => null,
                    'created_at' => $now,
                    'updated_at' => $now,
                ];
            }

            DB::table('calificaciones')->insert($batch);
            $created += count($batch);

            unset($batch, $rows);

            if ($created % 8000 === 0) {
                echo "  … {$created} calificaciones\n";
                gc_collect_cycles();
            }
        }

        $total = DB::table('calificaciones')->count();
        echo "✅ Calificaciones insertadas en esta pasada: {$created} (total tabla: {$total})\n\n";
    }

    private function notaAleatoria(float $min, float $max): float
    {
        return round($min + (mt_rand() / mt_getrandmax()) * ($max - $min), 2);
    }
}
