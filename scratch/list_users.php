<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

$users = App\Models\User::all(['id', 'username', 'email', 'name', 'user_role', 'status', 'usage_rights']);
echo json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
