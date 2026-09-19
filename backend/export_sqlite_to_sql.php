<?php
/**
 * Script para exportar datos de SQLite a formato SQL compatible con PostgreSQL
 */

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

echo "╔══════════════════════════════════════════════════════════════╗\n";
echo "║   EXPORTANDO DATOS DE SQLite A SQL (POSTGRESQL COMPATIBLE)   ║\n";
echo "╚══════════════════════════════════════════════════════════════╝\n\n";

$outputFile = __DIR__.'/database/sqlite_export.sql';
$handle = fopen($outputFile, 'w');

// Orden correcto respetando foreign keys
$tables = [
    'users',
    'facultades',
    'carreras',
    'materias',
    'materia_prerrequisitos',
    'profesores',
    'estudiantes',
    'aulas',
    'horarios',
    'inscripciones',
    'calificaciones',
    'assignments',
    'submissions',
    'quizzes',
    'quiz_questions',
    'quiz_answers',
    'quiz_attempts',
    'attendance_sessions',
    'attendances',
    'exam_incidents',
    'notifications',
    'solicitudes_admision',
    'pagos',
    'backups',
    'system_logs',
];

fwrite($handle, "-- Exportación de datos desde SQLite\n");
fwrite($handle, "-- Fecha: " . date('Y-m-d H:i:s') . "\n\n");
fwrite($handle, "SET client_encoding = 'UTF8';\n");
fwrite($handle, "SET standard_conforming_strings = on;\n\n");

$totalRecords = 0;

foreach ($tables as $table) {
    if (!Schema::hasTable($table)) {
        echo "⏭️  Tabla '{$table}' no existe, omitiendo...\n";
        continue;
    }

    $count = DB::table($table)->count();
    
    if ($count === 0) {
        echo "⏭️  Tabla '{$table}' vacía, omitiendo...\n";
        continue;
    }

    echo "📦 Exportando '{$table}' ({$count} registros)...\n";
    
    fwrite($handle, "\n-- Tabla: {$table}\n");
    fwrite($handle, "-- Registros: {$count}\n\n");

    // Obtener datos en chunks para no saturar memoria
    DB::table($table)->orderBy('id')->chunk(1000, function ($records) use ($handle, $table) {
        foreach ($records as $record) {
            $record = (array) $record;
            
            // Escapar valores para PostgreSQL
            $columns = array_keys($record);
            $values = array_map(function($value) {
                if (is_null($value)) {
                    return 'NULL';
                }
                if (is_bool($value)) {
                    return $value ? 'TRUE' : 'FALSE';
                }
                if (is_numeric($value)) {
                    return $value;
                }
                // Escapar comillas simples
                $escaped = str_replace("'", "''", $value);
                return "'" . $escaped . "'";
            }, $record);

            $sql = sprintf(
                "INSERT INTO \"%s\" (\"%s\") VALUES (%s);\n",
                $table,
                implode('", "', $columns),
                implode(', ', $values)
            );
            
            fwrite($handle, $sql);
        }
    });

    fwrite($handle, "\n");
    $totalRecords += $count;
}

// Reset sequences
fwrite($handle, "\n-- Resetear secuencias de IDs\n\n");
foreach ($tables as $table) {
    if (Schema::hasTable($table) && Schema::hasColumn($table, 'id')) {
        $maxId = DB::table($table)->max('id') ?? 0;
        if ($maxId > 0) {
            fwrite($handle, "SELECT setval(pg_get_serial_sequence('{$table}', 'id'), {$maxId}, true);\n");
        }
    }
}

fclose($handle);

echo "\n";
echo str_repeat("═", 64) . "\n";
echo "✅ EXPORTACIÓN COMPLETADA\n";
echo str_repeat("═", 64) . "\n";
echo "📁 Archivo: {$outputFile}\n";
echo "📊 Total registros: " . number_format($totalRecords) . "\n";
echo "💾 Tamaño: " . round(filesize($outputFile) / 1024 / 1024, 2) . " MB\n";
echo "\n";
