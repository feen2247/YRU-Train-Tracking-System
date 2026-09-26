<?php
$conn = ftp_connect('ftp.student.yru.ac.th');
if (!$conn) die("FTP Connection Failed\n");
if (!ftp_login($conn, 'S406665014', '406665014')) die("FTP Login Failed\n");
ftp_pasv($conn, true);

$tempFile = __DIR__ . '/live_env.txt';
if (ftp_get($conn, $tempFile, 'web/406665014.student.yru.ac.th/public_html/.env', FTP_BINARY)) {
    echo "Live .env content:\n" . file_get_contents($tempFile) . "\n";
} else {
    echo "Failed to get live .env\n";
}
ftp_close($conn);
