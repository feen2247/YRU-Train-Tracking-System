<?php
$cookieFile = tempnam(sys_get_temp_dir(), 'ck_ajax2');

// 1. Get CSRF Token
$ch = curl_init('https://406665014.site.yru.ac.th/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$html = curl_exec($ch);
curl_close($ch);

preg_match('/name="_token" value="([^"]+)"/', $html, $m);
$token = $m[1] ?? '';
if (!$token) {
    preg_match('/"X-CSRF-TOKEN": "([^"]+)"/', $html, $m);
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
$res = curl_exec($ch);
curl_close($ch);

echo "Login API Response: $res\n\n";

// 3. Fetch /home with authenticated session
$ch = curl_init('https://406665014.site.yru.ac.th/home');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$homeHtml = curl_exec($ch);
curl_close($ch);

echo "Home HTML Length: " . strlen($homeHtml) . "\n";
if (strpos($homeHtml, 'จุดเก็บรถ ${count} คัน') !== false || strpos($homeHtml, 'จุดเก็บรถ') !== false) {
    echo "SUCCESS: Found 'จุดเก็บรถ \${count} คัน' on the new host!\n\n";
    preg_match('/getGarageClusterIcon[\s\S]*?iconSize/i', $homeHtml, $match);
    echo "Rendered Snippet on Live Server:\n" . ($match[0] ?? '') . "\n";
} else {
    echo "NOT FOUND snippet in HTML.\n";
}

@unlink($cookieFile);
