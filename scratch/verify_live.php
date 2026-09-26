<?php
$urls = [
    'https://406665014.site.yru.ac.th/' => 200,
    'https://406665014.site.yru.ac.th/tracking' => 200,
    'https://406665014.site.yru.ac.th/schedule' => 200,
    'https://406665014.site.yru.ac.th/admin' => [200, 302],
    'https://406665014.site.yru.ac.th/vehicle-head' => [200, 302],
    'https://406665014.site.yru.ac.th/mechanic' => [200, 302],
    'https://406665014.site.yru.ac.th/executive' => [200, 302],
];

foreach ($urls as $u => $expected) {
    $ch = curl_init($u);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    curl_setopt($ch, CURLOPT_FOLLOWLOCATION, false);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    $res = curl_exec($ch);
    $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    echo "$u => HTTP $code\n";
}
