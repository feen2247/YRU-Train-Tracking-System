<?php
$testLogins = [
    ['username' => 'hadee@yru.ac.th', 'password' => '69014', 'target' => '/vehicle-head'],
    ['username' => 'suthin.m@yru.ac.th', 'password' => '69014', 'target' => '/vehicle-head'],
    ['username' => '69014', 'password' => '69014', 'target' => '/vehicle-head'],
    ['username' => 'prasan.g@yru.ac.th', 'password' => '69013', 'target' => '/maintenance-system'],
];

foreach ($testLogins as $t) {
    // 1. Login
    $ch = curl_init('https://406665014.student.yru.ac.th/api/login-submit');
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
        'username' => $t['username'],
        'password' => $t['password']
    ]));
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Content-Type: application/json',
        'Accept: application/json',
        'X-Requested-With: XMLHttpRequest'
    ]);
    curl_setopt($ch, CURLOPT_HEADER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $res1 = curl_exec($ch);
    curl_close($ch);

    preg_match_all('/^set-cookie:\s*([^;]+)/mi', $res1, $matches);
    $cookies = implode('; ', $matches[1]);

    // 2. Access target
    $ch2 = curl_init('https://406665014.student.yru.ac.th' . $t['target']);
    curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch2, CURLOPT_HTTPHEADER, ['Cookie: ' . $cookies]);
    curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
    $html = curl_exec($ch2);
    $httpCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
    curl_close($ch2);

    echo "User [{$t['username']} / {$t['password']}] -> Target {$t['target']}: HTTP $httpCode " . ($httpCode === 200 ? 'SUCCESS' : 'FAILED') . "\n";
}
