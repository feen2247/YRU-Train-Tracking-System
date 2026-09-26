<?php
// Test login endpoint on new live server
$loginUrl = 'https://406665014.site.yru.ac.th/login';
$cookieFile = tempnam(sys_get_temp_dir(), 'ck_test');

// Step 1: GET login page to obtain CSRF token and session cookie
$ch = curl_init('https://406665014.site.yru.ac.th/');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
$html = curl_exec($ch);
curl_close($ch);

preg_match('/name="_token" value="([^"]+)"/', $html, $matches);
$csrfToken = $matches[1] ?? '';
if (!$csrfToken) {
    preg_match('/"X-CSRF-TOKEN": "([^"]+)"/', $html, $matches);
    $csrfToken = $matches[1] ?? '';
}

echo "CSRF Token obtained: " . ($csrfToken ? substr($csrfToken, 0, 10) . '...' : 'NONE') . "\n";

// Step 2: POST login
$postData = [
    '_token' => $csrfToken,
    'username' => '406665014',
    'password' => '406665014',
];

$ch = curl_init('https://406665014.site.yru.ac.th/login');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
curl_setopt($ch, CURLOPT_COOKIEJAR, $cookieFile);
curl_setopt($ch, CURLOPT_COOKIEFILE, $cookieFile);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
$res = curl_exec($ch);
$finalUrl = curl_getinfo($ch, CURLINFO_EFFECTIVE_URL);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Login Response URL: $finalUrl (HTTP $httpCode)\n";
if (strpos($res, 'ทัศนีย์') !== false || strpos($res, 'ยินดีต้อนรับ') !== false || strpos($res, '406665014') !== false) {
    echo "SUCCESS: Logged in and authenticated successfully!\n";
} else {
    echo "Login check result: " . substr(strip_tags($res), 0, 200) . "\n";
}

@unlink($cookieFile);
