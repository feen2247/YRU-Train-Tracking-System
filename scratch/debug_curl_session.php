<?php
$cookieJar = __DIR__ . '/cookie.txt';
if (file_exists($cookieJar)) unlink($cookieJar);

// 1. Post to login
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
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
curl_close($ch);

echo "--- LOGIN RESPONSE ---\n$response\n";
echo "--- COOKIE JAR CONTENT ---\n" . file_get_contents($cookieJar) . "\n";

// 2. Access /vehicle-head
$ch2 = curl_init('https://406665014.student.yru.ac.th/vehicle-head');
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_COOKIEFILE, $cookieJar);
curl_setopt($ch2, CURLOPT_HEADER, true);
curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
$html = curl_exec($ch2);
curl_close($ch2);

echo "--- GET /vehicle-head RESPONSE ---\n$html\n";
