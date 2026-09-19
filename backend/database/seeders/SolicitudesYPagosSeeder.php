<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SolicitudesYPagosSeeder extends Seeder
{
    /**
     * Seed solicitudes de admisión y pagos de prueba
     */
    public function run(): void
    {
        echo "📝 Creando solicitudes de admisión y pagos de prueba...\n\n";

        // 1. Solicitudes de Admisión
        $this->createSolicitudes();

        // 2. Pagos (DESHABILITADO - tabla no existe en migraciones)
        // $this->createPagos();

        echo "✅ Solicitudes y pagos creados exitosamente!\n\n";
    }

    private function createSolicitudes(): void
    {
        $carreras = DB::table('carreras')->pluck('id')->toArray();
        $estudiantes = DB::table('estudiantes')->limit(40)->pluck('id')->toArray();
        $faker = \Faker\Factory::create('es_VE');

        if (empty($estudiantes)) {
            echo "   ⚠️  No hay estudiantes, omitiendo solicitudes\n";
            return;
        }

        $estados = [
            'pendiente' => 8,      // 8 solicitudes pendientes
            'en_revision' => 6,    // 6 en revisión
            'aprobada' => 4,       // 4 aprobadas
            'rechazada' => 2,      // 2 rechazadas
        ];

        $count = 0;

        foreach ($estados as $estado => $cantidad) {
            for ($i = 0; $i < min($cantidad, count($estudiantes) - $count); $i++) {
                $count++;
                
                // Fecha según el estado
                $fechaSolicitud = match($estado) {
                    'pendiente' => Carbon::now()->subDays(rand(1, 5)),
                    'en_revision' => Carbon::now()->subDays(rand(5, 15)),
                    'aprobada' => Carbon::now()->subDays(rand(20, 60)),
                    'rechazada' => Carbon::now()->subDays(rand(30, 90)),
                };

                DB::table('solicitudes_admision')->insert([
                    'estudiante_id' => $estudiantes[$count - 1],
                    'numero_referencia' => 'SOL-2026-' . str_pad($count, 5, '0', STR_PAD_LEFT),
                    
                    // Datos personales
                    'fecha_nacimiento' => $faker->dateTimeBetween('-25 years', '-17 years'),
                    'genero' => $faker->randomElement(['M', 'F']),
                    'telefono' => $faker->phoneNumber(),
                    'direccion' => $faker->address(),
                    'ciudad' => $faker->city(),
                    'estado_provincia' => $faker->randomElement(['Distrito Capital', 'Miranda', 'Carabobo', 'Zulia']),
                    'codigo_postal' => $faker->postcode(),
                    
                    // Contacto de emergencia
                    'contacto_emergencia_nombre' => $faker->name(),
                    'contacto_emergencia_telefono' => $faker->phoneNumber(),
                    'contacto_emergencia_relacion' => $faker->randomElement(['Padre', 'Madre', 'Hermano']),
                    
                    // Carrera
                    'carrera_id' => $faker->randomElement($carreras),
                    'modalidad' => $faker->randomElement(['presencial', 'semipresencial']),
                    'turno_preferido' => $faker->randomElement(['mañana', 'tarde', 'noche']),
                    'motivacion' => $faker->paragraph(2),
                    
                    // Datos académicos
                    'nivel_educativo' => 'bachiller',
                    'institucion_egreso' => $faker->company() . ' High School',
                    'año_graduacion' => $faker->numberBetween(2020, 2026),
                    'promedio_notas' => $faker->randomFloat(2, 14, 20),
                    'tipo_bachillerato' => $faker->randomElement(['ciencias', 'humanidades']),
                    
                    // Progreso del wizard
                    'paso1_completado' => true,
                    'paso2_completado' => true,
                    'paso3_completado' => true,
                    'paso4_completado' => $estado !== 'pendiente',
                    'paso5_completado' => $estado !== 'pendiente',
                    'paso_actual' => $estado === 'pendiente' ? 4 : 5,
                    
                    // Estado de solicitud
                    'estado' => $estado,
                    'fecha_envio' => $estado !== 'borrador' ? $fechaSolicitud : null,
                    'fecha_revision' => in_array($estado, ['aprobada', 'rechazada']) 
                        ? $fechaSolicitud->copy()->addDays(rand(3, 10)) 
                        : null,
                    'comentarios_admin' => $estado === 'rechazada' 
                        ? 'Promedio académico insuficiente para la carrera seleccionada' 
                        : null,
                    
                    'created_at' => $fechaSolicitud,
                    'updated_at' => now(),
                ]);
            }
        }

        echo "   ✓ {$count} solicitudes de admisión creadas\n";
    }

    private function createPagos(): void
    {
        // Obtener algunos estudiantes para asociar pagos
        $estudiantes = DB::table('estudiantes')->limit(30)->pluck('id')->toArray();
        $faker = \Faker\Factory::create('es_VE');

        $tiposPago = [
            'inscripcion' => 2500.00,
            'mensualidad' => 800.00,
            'examen' => 150.00,
            'certificado' => 50.00,
            'titulo' => 500.00,
        ];

        $estados = [
            'pendiente' => 12,
            'procesando' => 5,
            'completado' => 20,
            'rechazado' => 3,
        ];

        $count = 0;

        foreach ($estados as $estado => $cantidad) {
            for ($i = 0; $i < $cantidad; $i++) {
                $count++;
                
                $tipoPago = $faker->randomElement(array_keys($tiposPago));
                $monto = $tiposPago[$tipoPago];
                
                $fechaPago = match($estado) {
                    'pendiente' => Carbon::now()->subDays(rand(1, 3)),
                    'procesando' => Carbon::now()->subHours(rand(2, 24)),
                    'completado' => Carbon::now()->subDays(rand(5, 60)),
                    'rechazado' => Carbon::now()->subDays(rand(7, 30)),
                };

                DB::table('pagos')->insert([
                    'estudiante_id' => $faker->randomElement($estudiantes),
                    'concepto' => ucfirst(str_replace('_', ' ', $tipoPago)),
                    'monto' => $monto,
                    'metodo_pago' => $faker->randomElement(['transferencia', 'tarjeta', 'efectivo', 'pago_movil']),
                    'referencia' => strtoupper($faker->bothify('REF-####-????')),
                    'estado' => $estado,
                    'fecha_pago' => $fechaPago,
                    'fecha_procesado' => in_array($estado, ['completado', 'rechazado']) 
                        ? $fechaPago->copy()->addHours(rand(1, 48)) 
                        : null,
                    'observaciones' => $estado === 'rechazado' 
                        ? 'Datos bancarios incorrectos' 
                        : null,
                    'created_at' => $fechaPago,
                    'updated_at' => now(),
                ]);
            }
        }

        echo "   ✓ {$count} pagos creados\n";
    }
}
