<?php
$urls = [
    'http://406665014.site.yru.ac.th/_probe.php',
    'https://406665014.site.yru.ac.th/_probe.php',
    'http://s406665014.site.yru.ac.th/_probe.php',
    'https://s406665014.site.yru.ac.th/_probe.php',
    'http://host.site.yru.ac.th/~s406665014/_probe.php',
    'http://host.site.yru.ac.th/~406665014/_probe.php',
];
foreach ($urls as $u) {
    $ctx = stream_context_create(['ssl' => ['verify_peer' => false, 'verify_peer_name' => false], 'http' => ['timeout' => 5]]);
    $res = @file_get_contents($u, false, $ctx);
    echo $u . ' => ' . ($res ? substr(strip_tags($res), 0, 100) : 'FAILED') . "\n";
}
