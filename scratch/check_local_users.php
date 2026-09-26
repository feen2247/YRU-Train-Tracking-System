<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$user = App\Models\User::where('username', 'suthin')->first();
var_dump($user ? $user->toArray() : null);

$user2 = App\Models\User::where('employee_id', '69011')->first();
var_dump($user2 ? $user2->toArray() : null);

$allUsers = App\Models\User::all(['user_id', 'username', 'email', 'employee_id'])->toArray();
echo json_encode($allUsers, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
