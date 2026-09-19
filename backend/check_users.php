<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

echo "Usuarios en SQLite:\n";
echo str_repeat("-", 80) . "\n";

foreach(DB::table('users')->get() as $u) {
    echo "Email: {$u->email}\n";
    echo "Nombre: {$u->name}\n";
    echo "Rol: {$u->role}\n";
    echo str_repeat("-", 80) . "\n";
}

echo "\nTotal: " . DB::table('users')->count() . " usuarios\n";
