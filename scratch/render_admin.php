<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();
$html = view('passenger.admin.index')->render();
file_put_contents(__DIR__ . '/rendered_admin.html', $html);
echo "Rendered " . strlen($html) . " bytes\n";
