<?php
$cookieJar = __DIR__ . '/cookie.txt';
if (file_exists($cookieJar)) unlink($cookieJar);

// 1. Login with hadee@yru.ac.th
$ch = curl_init('https://406665014.student.yru.ac.th/api/login-submit');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    'username' => 'hadee@yru.ac.th',
    'password' => '69014'
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'X-Requested-With: XMLHttpRequest'
]);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieJar);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$res = curl_exec($ch);
curl_close($ch);

echo "Login response: $res\n";

// 2. Add debug route call
$ch2 = curl_init('https://406665014.student.yru.ac.th/api/debug-auth');
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_COOKIEFILE, $cookieJar);
curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
$debugRes = curl_exec($ch2);
echo "Debug Auth response: $debugRes\n";
