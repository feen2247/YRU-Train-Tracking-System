<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = App\Models\User::where('username', 'suthin')->orWhere('email', 'suthin.m@yru.ac.th')->get();
echo "Found users for suthin:\n";
foreach ($users as $u) {
    echo "user_id: {$u->user_id}, username: {$u->username}, email: {$u->email}, emp_id: {$u->employee_id}, role: {$u->user_role}, pwd: {$u->password}\n";
    echo "Check 69014: " . (Illuminate\Support\Facades\Hash::check('69014', $u->password) ? 'MATCH' : 'NO MATCH') . "\n";
    echo "Check 69011: " . (Illuminate\Support\Facades\Hash::check('69011', $u->password) ? 'MATCH' : 'NO MATCH') . "\n";
}
