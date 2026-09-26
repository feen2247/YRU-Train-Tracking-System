<?php
$context = stream_context_create([
    'ssl' => [
        'verify_peer' => false,
        'verify_peer_name' => false,
    ]
]);
$content = file_get_contents('https://406665014.student.yru.ac.th/admin-view', false, $context);
file_put_contents(__DIR__ . '/live_admin_view.html', $content);
echo "Downloaded " . strlen($content) . " bytes\n";
