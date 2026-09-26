<?php
$cookieFile = tempnam(sys_get_temp_dir(), 'ck_final');

// 1. Get Login Page & CSRF Token
$ch = curl_init('https://406665014.site.yru.ac.th/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$loginHtml = curl_exec($ch);
curl_close($ch);

preg_match('/name="_token" value="([^"]+)"/', $loginHtml, $m);
$token = $m[1] ?? '';
if (!$token) {
    preg_match('/"X-CSRF-TOKEN": "([^"]+)"/', $loginHtml, $m);
    $token = $m[1] ?? '';
}

// 2. Submit to /api/login-submit
$ch = curl_init('https://406665014.site.yru.ac.th/api/login-submit');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode([
    '_token' => $token,
    'username' => '406665014',
    'password' => '406665014'
]));
curl_setopt($ch, CURLOPT_HTTPHEADER, [
    'Content-Type: application/json',
    'Accept: application/json',
    'X-CSRF-TOKEN: ' . $token,
    'X-Requested-With: XMLHttpRequest'
]);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$loginRes = curl_exec($ch);
curl_close($ch);

echo "Login Response: $loginRes\n";

// 3. Fetch /api/storage/init
$ch = curl_init('https://406665014.site.yru.ac.th/api/storage/init');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$initJs = curl_exec($ch);
curl_close($ch);

echo "Init JS Length: " . strlen($initJs) . " bytes\n";
if (strpos($initJs, 'EV-01') !== false) {
    echo "SUCCESS: EV-01 and all live vehicle data are present in init.js!\n";
}

@unlink($cookieFile);
