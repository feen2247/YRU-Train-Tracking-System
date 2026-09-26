<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;

// Run Seeder
$seeder = new Database\Seeders\UserSeeder();
$seeder->run();

// Cleanly update suthin in local database
$suthin = User::where('username', 'suthin')->orWhere('email', 'suthin.m@yru.ac.th')->first();
if ($suthin) {
    $suthin->employee_id = '69014';
    $suthin->password = Hash::make('69014');
    $suthin->user_role = 'vehicle_head';
    $suthin->status = 'ใช้งาน';
    $suthin->usage_rights = 'Active';
    $suthin->save();
}

$prasan = User::where('username', 'prasan')->orWhere('email', 'prasan.g@yru.ac.th')->first();
if ($prasan) {
    $prasan->employee_id = '69013';
    $prasan->password = Hash::make('69013');
    $prasan->user_role = 'Mechanic';
    $prasan->status = 'ใช้งาน';
    $prasan->usage_rights = 'Active';
    $prasan->save();
}

echo "Cleanly configured suthin and prasan.\n";

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
