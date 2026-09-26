<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

$authController = new App\Http\Controllers\AuthController();

$testCases = [
    ['username' => 'suthin', 'password' => '69014'],
    ['username' => 'suthin.m@yru.ac.th', 'password' => '69014'],
    ['username' => '69014', 'password' => '69014'],
    ['username' => 'suthin', 'password' => 'suthin'],
    ['username' => 'suthin', 'password' => '123456'],
    ['username' => 'prasan', 'password' => '69013'],
    ['username' => 'prasan.g@yru.ac.th', 'password' => '69013'],
    ['username' => 'kitti', 'password' => '69011'],
];

foreach ($testCases as $tc) {
    $throttleKey = Str::lower($tc['username']) . '|127.0.0.1';
    RateLimiter::clear($throttleKey);
    $req = Illuminate\Http\Request::create('/api/login-submit', 'POST', $tc);
    $res = $authController->loginSubmit($req);
    echo "Login test for [{$tc['username']} / {$tc['password']}]: HTTP " . $res->getStatusCode() . " -> " . $res->getContent() . "\n";
}
