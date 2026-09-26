<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

use Illuminate\Support\Facades\DB;

echo "=== Local SQLite Stations ===\n";
$stations = DB::table('stations')->get();
echo "Total local stations: " . count($stations) . "\n";
foreach ($stations as $s) {
    echo "ID: {$s->id} | code: {$s->parking_spot_code} | name: {$s->name} | lat: {$s->latitude} | lng: {$s->longitude}\n";
}
