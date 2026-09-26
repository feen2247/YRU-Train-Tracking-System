<?php
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
curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$res1 = curl_exec($ch);
curl_close($ch);

// Extract cookies
preg_match_all('/^set-cookie:\s*([^;]+)/mi', $res1, $matches);
$cookies = implode('; ', $matches[1]);
echo "Extracted Cookies: $cookies\n\n";

// 2. GET /vehicle-head with the cookies explicitly passed
$ch2 = curl_init('https://406665014.student.yru.ac.th/vehicle-head');
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_HTTPHEADER, [
    'Cookie: ' . $cookies
]);
curl_setopt($ch2, CURLINFO_HEADER_OUT, true);
curl_setopt($ch2, CURLOPT_HEADER, true);
curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
$res2 = curl_exec($ch2);
$httpCode = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
$hdr = curl_getinfo($ch2, CURLINFO_HEADER_OUT);
curl_close($ch2);

echo "--- STEP 2 REQUEST HEADERS ---\n$hdr\n";
echo "--- STEP 2 HTTP STATUS: $httpCode ---\n";
if ($httpCode === 200) {
    echo "SUCCESS! /vehicle-head returned HTTP 200!\n";
    if (strpos($res2, 'หัวหน้ายานพาหนะ') !== false) {
        echo "Found 'หัวหน้ายานพาหนะ' in response HTML!\n";
    }
} else {
    echo "FAILED: HTTP $httpCode\n$res2\n";
}
