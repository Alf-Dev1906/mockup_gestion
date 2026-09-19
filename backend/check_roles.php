<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

foreach(DB::table('users')->get() as $u) {
    echo "{$u->email} => {$u->role}\n";
}
