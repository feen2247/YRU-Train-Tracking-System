<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$app->make('Illuminate\Contracts\Console\Kernel')->bootstrap();

echo "--- Global Storage Surveys ---\n";
var_dump(\Illuminate\Support\Facades\Cache::get('global_storage_yru_surveys'));

echo "--- Users ---\n";
$users = \App\Models\User::all(['user_id', 'name', 'user_role']);
foreach ($users as $u) {
    echo "{$u->user_id}: {$u->name} ({$u->user_role})\n";
}

echo "--- Travel Histories ---\n";
$th = \App\Models\TravelHistory::count();
echo "TravelHistories: {$th}\n";
