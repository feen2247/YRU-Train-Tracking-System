<?php
$ftp = ftp_connect('ftp.student.yru.ac.th');
if (!$ftp) {
    die("FTP connection failed\n");
}
if (!ftp_login($ftp, 'S406665014', '406665014')) {
    die("FTP login failed\n");
}
ftp_pasv($ftp, true);
$remotePath = 'web/406665014.student.yru.ac.th/public_html/resources/views/passenger/executive/index.blade.php';
$localPath = 'resources/views/passenger/executive/index.blade.php';

if (ftp_get($ftp, $localPath, $remotePath, FTP_BINARY)) {
    echo "Successfully downloaded executive/index.blade.php (" . filesize($localPath) . " bytes)\n";
} else {
    echo "Failed to download from FTP\n";
}
ftp_close($ftp);
