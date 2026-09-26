<?php
$cookieJar = tempnam(sys_get_temp_dir(), 'curl_cookie_');

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
$res1 = curl_exec($ch);
curl_close($ch);

echo "--- STEP 1: LOGIN ---\n$res1\n\n";

// 2. GET /vehicle-head with the cookies
$ch2 = curl_init('https://406665014.student.yru.ac.th/vehicle-head');
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_COOKIEFILE, $cookieJar);
curl_setopt($ch2, CURLOPT_HEADER, true);
curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
$res2 = curl_exec($ch2);
curl_close($ch2);

echo "--- STEP 2: GET /vehicle-head ---\n$res2\n";
