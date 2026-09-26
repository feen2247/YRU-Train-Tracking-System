<?php
require __DIR__ . '/../vendor/autoload.php';
$app = require_once __DIR__ . '/../bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;

// 1. Seed into local database
$seeder = new Database\Seeders\UserSeeder();
$seeder->run();
echo "Seeded local database successfully.\n";

// 2. Insert into live_database.sqlite as well
$pdo = new PDO('sqlite:' . __DIR__ . '/live_database.sqlite');
$stmt = $pdo->prepare("
    INSERT OR REPLACE INTO users (
        user_id, employee_id, username, prefix, first_name, last_name, name, email, password, user_role, usage_rights, status, email_verified_at, created_at, updated_at
    ) VALUES (
        :user_id, :employee_id, :username, :prefix, :first_name, :last_name, :name, :email, :password, :user_role, :usage_rights, :status, datetime('now'), datetime('now'), datetime('now')
    )
");

$stmt->execute([
    ':user_id' => 'USR-000010',
    ':employee_id' => '69010',
    ':username' => 'prasan',
    ':prefix' => 'นาย',
    ':first_name' => 'ประสาน',
    ':last_name' => 'งานดี',
    ':name' => 'นายประสาน งานดี',
    ':email' => 'prasan.g@yru.ac.th',
    ':password' => Hash::make('69010'),
    ':user_role' => 'Mechanic',
    ':usage_rights' => 'Active',
    ':status' => 'ใช้งาน'
]);

$stmt->execute([
    ':user_id' => 'USR-000011',
    ':employee_id' => '69011',
    ':username' => 'suthin',
    ':prefix' => 'นาย',
    ':first_name' => 'สุทิน',
    ':last_name' => 'มีสุข',
    ':name' => 'นายสุทิน มีสุข',
    ':email' => 'suthin.m@yru.ac.th',
    ':password' => Hash::make('69011'),
    ':user_role' => 'vehicle_head',
    ':usage_rights' => 'Active',
    ':status' => 'ใช้งาน'
]);

echo "Updated live_database.sqlite successfully.\n";

// 3. Test loginSubmit for vehicle_head
$authController = new App\Http\Controllers\AuthController();

$testCases = [
    ['username' => 'suthin', 'password' => '69011'],
    ['username' => 'suthin.m@yru.ac.th', 'password' => '69011'],
    ['username' => '69011', 'password' => '69011'],
    ['username' => 'suthin', 'password' => 'suthin'],
    ['username' => 'suthin', 'password' => '123456'],
    ['username' => 'prasan', 'password' => '69010'],
];

foreach ($testCases as $tc) {
    $req = new Illuminate\Http\Request([], $tc);
    $res = $authController->loginSubmit($req);
    echo "Login test for [{$tc['username']} / {$tc['password']}]: HTTP " . $res->getStatusCode() . " -> " . $res->getContent() . "\n";
}
