<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class PagosMockupSeeder extends Seeder
{
    public function run(): void
    {
        // Obtener estudiantes aleatorios
        $estudiantes = DB::table('estudiantes')
            ->inRandomOrder()
            ->limit(150)
            ->pluck('id')
            ->toArray();

        if (empty($estudiantes)) {
            $this->command->error('No hay estudiantes para asignar pagos.');
            return;
        }

        $conceptos = [
            ['concepto' => 'Inscripción Semestre 2026-1', 'monto' => 500.00],
            ['concepto' => 'Mensualidad Enero 2026', 'monto' => 200.00],
            ['concepto' => 'Mensualidad Febrero 2026', 'monto' => 200.00],
            ['concepto' => 'Mensualidad Marzo 2026', 'monto' => 200.00],
            ['concepto' => 'Derecho a Examen', 'monto' => 50.00],
            ['concepto' => 'Solicitud de Certificado', 'monto' => 30.00],
            ['concepto' => 'Carnet Estudiantil', 'monto' => 20.00],
        ];

        $metodosPago = ['transferencia', 'deposito', 'tarjeta', 'efectivo', 'punto_venta'];
        $estatuses = ['pendiente', 'pagado', 'verificado'];

        $pagos = [];
        $referenceCounter = 1000;

        foreach ($estudiantes as $estudianteId) {
            // Cada estudiante tiene entre 2 y 4 pagos
            $numPagos = rand(2, 4);
            
            for ($i = 0; $i < $numPagos; $i++) {
                $concepto = $conceptos[array_rand($conceptos)];
                $estatus = $estatuses[array_rand($estatuses)];
                
                $fechaVencimiento = Carbon::now()->subDays(rand(0, 60));
                $fechaPago = null;
                $metodo = null;
                $referencia = null;

                if ($estatus === 'pagado' || $estatus === 'verificado') {
                    $fechaPago = $fechaVencimiento->copy()->addDays(rand(-5, 10));
                    $metodo = $metodosPago[array_rand($metodosPago)];
                    $referencia = 'REF-' . str_pad($referenceCounter++, 8, '0', STR_PAD_LEFT);
                }

                $pagos[] = [
                    'estudiante_id' => $estudianteId,
                    'concepto' => $concepto['concepto'],
                    'monto' => $concepto['monto'],
                    'estatus' => $estatus,
                    'fecha_vencimiento' => $fechaVencimiento->format('Y-m-d'),
                    'fecha_pago' => $fechaPago ? $fechaPago->format('Y-m-d H:i:s') : null,
                    'referencia' => $referencia,
                    'created_at' => now(),
                    'updated_at' => now(),
                ];
            }
        }

        // Insertar en bloques de 500
        $chunks = array_chunk($pagos, 500);
        
        foreach ($chunks as $chunk) {
            DB::table('pagos')->insert($chunk);
        }

        $this->command->info('✅ ' . count($pagos) . ' pagos creados exitosamente.');
        $this->command->info('   - Pendientes: ' . DB::table('pagos')->where('estatus', 'pendiente')->count());
        $this->command->info('   - Pagados: ' . DB::table('pagos')->where('estatus', 'pagado')->count());
        $this->command->info('   - Verificados: ' . DB::table('pagos')->where('estatus', 'verificado')->count());
    }
}
