<?php
$conn = ftp_connect('ftp.student.yru.ac.th');
if (!$conn) die("FTP Connection Failed\n");
if (!ftp_login($conn, 'S406665014', '406665014')) die("FTP Login Failed\n");
ftp_pasv($conn, true);

$local = __DIR__ . '/../routes/web.php';
$remote = 'web/406665014.student.yru.ac.th/public_html/routes/web.php';
if (ftp_put($conn, $remote, $local, FTP_BINARY)) {
    echo "Uploaded routes/web.php successfully.\n";
} else {
    die("Failed to upload\n");
}

// Purge remote compiled views
$viewDir = 'web/406665014.student.yru.ac.th/public_html/storage/framework/views';
$cachedViews = ftp_nlist($conn, $viewDir);
if (is_array($cachedViews)) {
    foreach ($cachedViews as $cv) {
        if (substr($cv, -4) === '.php') {
            @ftp_delete($conn, $cv);
        }
    }
    echo "Purged remote view cache on student server.\n";
}

ftp_close($conn);

// Call /api/system/sync-db-users
$ch = curl_init('https://406665014.student.yru.ac.th/api/system/sync-db-users');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
$res = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "Sync DB Users Result ($httpCode):\n$res\n";
