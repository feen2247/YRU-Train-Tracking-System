<?php
require __DIR__.'/vendor/autoload.php';
$app = require_once __DIR__.'/bootstrap/app.php';
$app->make(Illuminate\Contracts\Console\Kernel::class)->bootstrap();

$emails = ['user@yru.ac.th', 'tracking1@yru.ac.th', 'tracking2@yru.ac.th', 'executive@yru.ac.th', 'admin@yru.ac.th'];
foreach($emails as $email) {
    $user = App\Models\User::where('email', $email)->orWhere('username', $email)->first();
    if($user) {
        $user->password = Hash::make('12345678');
        $user->save();
        echo $email . " password updated to '12345678'\n";
    } else {
        echo $email . " does not exist\n";
    }
}
