<?php
$conn = ftp_connect('ftp.student.yru.ac.th');
if (!$conn) die("FTP Connection Failed\n");
if (!ftp_login($conn, 'S406665014', '406665014')) die("FTP Login Failed\n");
ftp_pasv($conn, true);

$local = __DIR__ . '/../_scripts/sync_live_mysql.php';
$remote = 'web/406665014.student.yru.ac.th/public_html/sync_live_mysql.php';
if (ftp_put($conn, $remote, $local, FTP_BINARY)) {
    echo "Uploaded sync_live_mysql.php successfully.\n";
} else {
    die("Failed to upload\n");
}
ftp_close($conn);

// Execute via HTTP
$ch = curl_init('https://406665014.student.yru.ac.th/sync_live_mysql.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$res = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Result ($httpCode):\n$res\n";

// Remove remote file after execution for security
$conn = ftp_connect('ftp.student.yru.ac.th');
if ($conn && ftp_login($conn, 'S406665014', '406665014')) {
    ftp_delete($conn, $remote);
    ftp_close($conn);
    echo "Cleaned up remote sync script.\n";
}
