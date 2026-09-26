<?php
$cases = [
    ['username' => 'hadee', 'password' => '69014'],
    ['username' => 'hadee@yru.ac.th', 'password' => '69014'],
    ['username' => 'head.vehicle@yru.ac.th', 'password' => '69014'],
    ['username' => 'vehiclehead', 'password' => '69014'],
    ['username' => 'supervisor', 'password' => '69014'],
];

foreach ($cases as $c) {
    $ch = curl_init('https://406665014.student.yru.ac.th/api/login-submit');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($c));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'X-Requested-With: XMLHttpRequest'
    ]);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $response = curl_exec($ch);
    $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);

    echo "Response for [{$c['username']} / {$c['password']}]: HTTP $httpCode -> $response\n";
}
