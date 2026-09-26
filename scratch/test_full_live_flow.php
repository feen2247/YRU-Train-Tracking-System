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
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Step 1 - Login API Response (HTTP $httpCode):\n$response\n\n";

// 2. Access /vehicle-head with the session cookie
$ch2 = curl_init('https://406665014.student.yru.ac.th/vehicle-head');
curl_setopt($ch2, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch2, CURLOPT_COOKIEFILE, $cookieJar);
curl_setopt($ch2, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch2, CURLOPT_FOLLOWLOCATION, false); // Don't follow redirect to see if 200 or 302
$html = curl_exec($ch2);
$httpCode2 = curl_getinfo($ch2, CURLINFO_HTTP_CODE);
curl_close($ch2);

echo "Step 2 - GET /vehicle-head (HTTP $httpCode2):\n";
if ($httpCode2 === 200) {
    echo "SUCCESS! Page loaded properly (HTML length: " . strlen($html) . " bytes)\n";
    // Check if title or header is present
    if (strpos($html, 'หัวหน้ายานพาหนะ') !== false) {
        echo "Found 'หัวหน้ายานพาหนะ' in HTML output!\n";
    }
} else {
    echo "FAILED! Returned HTTP $httpCode2\n";
}
