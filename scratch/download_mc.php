<?php
$ftp = ftp_connect('ftp.student.yru.ac.th');
if ($ftp && ftp_login($ftp, 'S406665014', '406665014')) {
    ftp_pasv($ftp, true);
    if (ftp_get($ftp, __DIR__ . '/../app/Http/Controllers/MaintenanceController.php', 'web/406665014.student.yru.ac.th/public_html/app/Http/Controllers/MaintenanceController.php', FTP_BINARY)) {
        echo "RESTORED SUCCESSFUL\n";
    } else {
        echo "GET FAILED\n";
    }
    ftp_close($ftp);
} else {
    echo "LOGIN FAILED\n";
}
