<?php
$cookieFile = tempnam(sys_get_temp_dir(), 'ck_debug');

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

// 2. Perform Login
$ch = curl_init('https://406665014.site.yru.ac.th/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query([
    '_token' => $token,
    'username' => '406665014',
    'password' => '406665014'
]));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_HEADER, true);
$loginRes = curl_exec($ch);
curl_close($ch);
echo "=== LOGIN RESPONSE ===\n$loginRes\n";

// 3. Fetch /home
$ch = curl_init('https://406665014.site.yru.ac.th/home');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
curl_setopt($ch, CURLOPT_HEADER, true);
$homeRes = curl_exec($ch);
curl_close($ch);

echo "=== HOME RESPONSE ===\n$homeRes\n";
@unlink($cookieFile);
