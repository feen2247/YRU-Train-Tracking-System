<?php
$cookieFile = tempnam(sys_get_temp_dir(), 'ck_verify');

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
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_exec($ch);
curl_close($ch);

// 3. Fetch /home
$ch = curl_init('https://406665014.site.yru.ac.th/home');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$homeHtml = curl_exec($ch);
curl_close($ch);

@unlink($cookieFile);

echo "=== RENDERED /home ON NEW HOST (host.site.yru.ac.th) ===\n";
if (strpos($homeHtml, 'จุดเก็บรถ ${count} คัน') !== false || strpos($homeHtml, 'จุดเก็บรถ') !== false) {
    echo "SUCCESS: Found 'จุดเก็บรถ \${count} คัน' on the new host!\n";
    preg_match('/getGarageClusterIcon[\s\S]*?iconSize/i', $homeHtml, $match);
    echo "Rendered Icon Snippet:\n" . ($match[0] ?? '') . "\n";
} else {
    echo "FAILED to find snippet in /home HTML. Length: " . strlen($homeHtml) . "\n";
}
