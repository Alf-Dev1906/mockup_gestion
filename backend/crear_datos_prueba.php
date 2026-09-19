<?php

require __DIR__.'/vendor/autoload.php';

$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

use App\Models\Horario;
use App\Models\Inscripcion;
use Illuminate\Support\Facades\DB;

echo "Creando datos de prueba para admin...\n\n";

// Buscar algunos horarios existentes
$horarios = Horario::with(['materia', 'profesor'])
    ->where('estatus', 'abierto')
    ->limit(3)
    ->get();

if ($horarios->count() === 0) {
    echo "⚠️  No hay horarios disponibles. El admin no tendrá datos que ver.\n";
    exit(0);
}

echo "✓ Encontrados {$horarios->count()} horarios\n";

// Crear inscripciones para el estudiante activo (user_id=101)
$estudianteId = DB::table('estudiantes')->where('user_id', 101)->value('id');

if (!$estudianteId) {
    echo "❌ No se encontró el estudiante activo\n";
    exit(1);
}

echo "✓ Estudiante ID: $estudianteId\n\n";

$creadas = 0;
foreach ($horarios as $horario) {
    try {
        Inscripcion::create([
            'estudiante_id' => $estudianteId,
            'horario_id' => $horario->id,
            'periodo_academico' => '2026-1',
            'estatus' => 'cursando',
            'fecha_inscripcion' => now(),
        ]);
        
        echo "✓ Inscripción creada: {$horario->materia->nombre}\n";
        $creadas++;
    } catch (\Exception $e) {
        echo "⚠️  No se pudo crear inscripción para {$horario->materia->nombre}: {$e->getMessage()}\n";
    }
}

echo "\n✅ {$creadas} inscripciones creadas exitosamente\n";
echo "Ahora el admin debería poder ver datos en Horarios, Inscripciones y Calificaciones\n";
