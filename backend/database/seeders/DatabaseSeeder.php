<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DatabaseSeeder extends Seeder
{
    /**
     * Desactivar foreign key checks según el motor de BD
     */
    protected function disableForeignKeyChecks(): void
    {
        $driver = DB::getDriverName();
        
        switch ($driver) {
            case 'mysql':
                DB::statement('SET FOREIGN_KEY_CHECKS=0');
                break;
            case 'sqlite':
                DB::statement('PRAGMA foreign_keys = OFF');
                break;
            case 'pgsql':
                // PostgreSQL no necesita desactivar FK checks para inserciones masivas
                break;
        }
    }

    /**
     * Reactivar foreign key checks según el motor de BD
     */
    protected function enableForeignKeyChecks(): void
    {
        $driver = DB::getDriverName();
        
        switch ($driver) {
            case 'mysql':
                DB::statement('SET FOREIGN_KEY_CHECKS=1');
                break;
            case 'sqlite':
                DB::statement('PRAGMA foreign_keys = ON');
                break;
            case 'pgsql':
                // PostgreSQL no necesita reactivar FK checks
                break;
        }
    }

    /**
     * Seed the application's database.
     * 
     * Este seeder poblará la base de datos con datos masivos para simular
     * un entorno de alta concurrencia con 100K+ estudiantes.
     * 
     * ADVERTENCIA: Este proceso puede tardar 10-20 minutos dependiendo
     * del hardware. Se recomienda ejecutar en un ambiente de desarrollo.
     * 
     * Orden de ejecución:
     * 1. Facultades (10)
     * 2. Carreras (~30) + TSU / carreras extra
     * 3. Profesores (5,000)
     * 4. Aulas (800)
     * 5. Materias (~1,500)
     * 6. Estudiantes (100,000) ⏱️ Este es el más lento
     * 7. Horarios (15,000)
     * 8. Inscripciones (~250,000)
     * 9. Calificaciones (una por inscripción, en chunks)
     */
    public function run(): void
    {
        $startTime = microtime(true);
        
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════╗\n";
        echo "║   SISTEMA DE GESTIÓN ESTUDIANTIL - DATABASE SEEDER          ║\n";
        echo "║   Generación Masiva de Datos para Alta Concurrencia         ║\n";
        echo "╚══════════════════════════════════════════════════════════════╝\n";
        echo "\n";
        echo "⚠️  ADVERTENCIA: Este proceso generará más de 370,000 registros\n";
        echo "    y puede tardar 10-20 minutos.\n\n";
        echo "📊 Datos a generar:\n";
        echo "    • 10 Facultades\n";
        echo "    • ~30 Carreras\n";
        echo "    • 5,000 Profesores\n";
        echo "    • 800 Aulas\n";
        echo "    • ~1,500 Materias\n";
        echo "    • 100,000 Estudiantes ⏱️\n";
        echo "    • 15,000 Horarios\n";
        echo "    • ~250,000 Inscripciones ⏱️\n";
        echo "\n";
        echo "🚀 Iniciando proceso...\n";
        echo str_repeat("═", 64) . "\n\n";

        // Desactivar foreign key checks temporalmente para mejor performance
        $this->disableForeignKeyChecks();

        try {
            // 0. Usuario Administrador
            echo "👤 PASO 0/9: Usuario Administrador\n";
            $this->call(UserSeeder::class);
            
            // 1. Facultades
            echo "📁 PASO 1/9: Facultades\n";
            $this->call(FacultadSeeder::class);

            // 2. Carreras
            echo "📚 PASO 2/9: Carreras\n";
            $this->call(CarreraSeeder::class);
            echo "📚 PASO 2b/9: Carreras TSU y complementarias\n";
            $this->call(NuevasCarrerasSeeder::class);

            // 3. Profesores
            echo "👨‍🏫 PASO 3/9: Profesores\n";
            $this->call(ProfesorSeeder::class);

            // 4. Aulas
            echo "🏫 PASO 4/9: Aulas\n";
            $this->call(AulaSeeder::class);

            // 5. Materias
            echo "📖 PASO 5/9: Materias\n";
            $this->call(MateriaSeeder::class);

            // 6. Estudiantes (el más lento)
            echo "👨‍🎓 PASO 6/9: Estudiantes (proceso largo)\n";
            $this->call(EstudianteSeeder::class);

            // 7. Horarios
            echo "🕐 PASO 7/9: Horarios\n";
            $this->call(HorarioSeeder::class);

            // 8. Inscripciones (segundo más lento)
            echo "📝 PASO 8/9: Inscripciones (proceso largo)\n";
            $this->call(InscripcionSeeder::class);

            // 9. Calificaciones (chunks, sin Eloquent masivo)
            echo "📊 PASO 9/9: Calificaciones\n";
            $this->call(CalificacionSeeder::class);

        } finally {
            // Reactivar foreign key checks
            $this->enableForeignKeyChecks();
        }

        $totalTime = round(microtime(true) - $startTime, 2);
        $minutes = floor($totalTime / 60);
        $seconds = round($totalTime % 60);

        echo "\n";
        echo str_repeat("═", 64) . "\n";
        echo "✅ PROCESO COMPLETADO EXITOSAMENTE\n";
        echo str_repeat("═", 64) . "\n";
        echo "\n";
        echo "⏱️  Tiempo total: {$minutes} min {$seconds} seg\n";
        echo "\n";
        echo "📊 Resumen de registros creados:\n";
        echo "    ✓ Facultades:     " . DB::table('facultades')->count() . "\n";
        echo "    ✓ Carreras:       " . DB::table('carreras')->count() . "\n";
        echo "    ✓ Profesores:     " . number_format(DB::table('profesores')->count()) . "\n";
        echo "    ✓ Aulas:          " . number_format(DB::table('aulas')->count()) . "\n";
        echo "    ✓ Materias:       " . number_format(DB::table('materias')->count()) . "\n";
        echo "    ✓ Estudiantes:    " . number_format(DB::table('estudiantes')->count()) . "\n";
        echo "    ✓ Horarios:       " . number_format(DB::table('horarios')->count()) . "\n";
        echo "    ✓ Inscripciones:  " . number_format(DB::table('inscripciones')->count()) . "\n";
        echo "    ✓ Calificaciones: " . number_format(DB::table('calificaciones')->count()) . "\n";
        echo "\n";
        echo "🎉 La base de datos está lista para pruebas de alta concurrencia!\n";
        echo "\n";
    }
}
