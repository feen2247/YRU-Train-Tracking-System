<?php
// Script to run directly on the live server to query/update MySQL database S406665014_db
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\User;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

header('Content-Type: application/json; charset=utf-8');

$allUsers = User::all();
$output = [
    'existing_users' => $allUsers->map(function($u) {
        return [
            'user_id' => $u->user_id,
            'employee_id' => $u->employee_id,
            'username' => $u->username,
            'email' => $u->email,
            'name' => $u->name,
            'role' => $u->user_role,
            'status' => $u->status,
            'usage_rights' => $u->usage_rights
        ];
    }),
];

// Ensure hadee / suthin (vehicle_head) and prasan (mechanic) exist in MySQL
$vehicleHead = User::where('username', 'hadee')
    ->orWhere('username', 'suthin')
    ->orWhere('employee_id', '69014')
    ->orWhere('email', 'hadee@yru.ac.th')
    ->first();

if (!$vehicleHead) {
    $vehicleHead = new User();
    $vehicleHead->user_id = 'USR-000014';
}

$vehicleHead->employee_id = '69014';
$vehicleHead->username = 'hadee';
$vehicleHead->prefix = 'นาย';
$vehicleHead->first_name = 'ฮาดิ';
$vehicleHead->last_name = 'ลือแมะ';
$vehicleHead->name = 'นายฮาดิ ลือแมะ';
$vehicleHead->email = 'hadee@yru.ac.th';
$vehicleHead->password = Hash::make('69014');
$vehicleHead->user_role = 'vehicle_head';
$vehicleHead->usage_rights = 'Active';
$vehicleHead->status = 'ใช้งาน';
$vehicleHead->email_verified_at = now();
$vehicleHead->save();

// Also create/update suthin for vehicle_head
$suthin = User::where('username', 'suthin')->orWhere('email', 'suthin.m@yru.ac.th')->first();
if (!$suthin) {
    $suthin = new User();
    $suthin->user_id = 'USR-000018';
}
$suthin->employee_id = '69014';
$suthin->username = 'suthin';
$suthin->prefix = 'นาย';
$suthin->first_name = 'สุทิน';
$suthin->last_name = 'มีสุข';
$suthin->name = 'นายสุทิน มีสุข';
$suthin->email = 'suthin.m@yru.ac.th';
$suthin->password = Hash::make('69014');
$suthin->user_role = 'vehicle_head';
$suthin->usage_rights = 'Active';
$suthin->status = 'ใช้งาน';
$suthin->email_verified_at = now();
$suthin->save();

// Also create/update prasan for mechanic
$prasan = User::where('username', 'prasan')->orWhere('email', 'prasan.g@yru.ac.th')->first();
if (!$prasan) {
    $prasan = new User();
    $prasan->user_id = 'USR-000013';
}
$prasan->employee_id = '69013';
$prasan->username = 'prasan';
$prasan->prefix = 'นาย';
$prasan->first_name = 'ประสาน';
$prasan->last_name = 'งานดี';
$prasan->name = 'นายประสาน งานดี';
$prasan->email = 'prasan.g@yru.ac.th';
$prasan->password = Hash::make('69013');
$prasan->user_role = 'Mechanic';
$prasan->usage_rights = 'Active';
$prasan->status = 'ใช้งาน';
$prasan->email_verified_at = now();
$prasan->save();

$output['status'] = 'success';
$output['updated_vehicle_head'] = $vehicleHead;
$output['updated_suthin'] = $suthin;
$output['updated_prasan'] = $prasan;

echo json_encode($output, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
