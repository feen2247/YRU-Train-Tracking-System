<?php
$cases = [
    ['username' => 'suthin', 'password' => '69014'],
    ['username' => 'suthin.m@yru.ac.th', 'password' => '69014'],
    ['username' => '69014', 'password' => '69014'],
    ['username' => 'suthin', 'password' => 'suthin'],
    ['username' => 'prasan', 'password' => '69013'],
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

    echo "Live server response for [{$c['username']} / {$c['password']}]: HTTP $httpCode\n$response\n\n";
}
