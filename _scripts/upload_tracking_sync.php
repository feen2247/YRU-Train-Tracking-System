<?php
$host = 'ftp.student.yru.ac.th';
$user = 'S406665014';
$pass = '406665014';
$base = 'web/406665014.student.yru.ac.th/public_html';

$conn = ftp_connect($host, 21, 30);
if (!$conn) die("[ERROR] Cannot connect to FTP\n");

if (!ftp_login($conn, $user, $pass)) die("[ERROR] FTP login failed\n");
ftp_pasv($conn, true);

$files = [
    'routes/web.php',
    'resources/views/passenger/tracking/index.blade.php',
    'resources/views/passenger/home/index.blade.php',
    'resources/views/passenger/maintenance/index.blade.php',
    'resources/views/passenger/executive/index.blade.php',
    'resources/views/passenger/executive/partials/sidebar.blade.php',
];

$localRoot = dirname(__DIR__);

foreach ($files as $rel) {
    $local  = $localRoot . DIRECTORY_SEPARATOR . str_replace('/', DIRECTORY_SEPARATOR, $rel);
    $remote = $base . '/' . $rel;

    // Ensure remote directory exists
    $parts = explode('/', dirname($remote));
    $cur = '';
    foreach ($parts as $p) {
        $cur = $cur ? "$cur/$p" : $p;
        @ftp_mkdir($conn, $cur);
    }

    if (ftp_put($conn, $remote, $local, FTP_BINARY)) {
        echo "[SUCCESS] $rel\n";
    } else {
        echo "[ERROR] $rel\n";
    }
}

ftp_close($conn);
echo "Done.\n";
