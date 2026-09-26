<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$all = App\Models\User::all();
foreach ($all as $u) {
    echo "user_id: {$u->user_id}, username: {$u->username}, email: {$u->email}, emp_id: {$u->employee_id}, role: {$u->user_role}\n";
}
