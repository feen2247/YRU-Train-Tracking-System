<?php
$ftp = ftp_connect('ftp.student.yru.ac.th');
if ($ftp && ftp_login($ftp, 'S406665014', '406665014')) {
    ftp_pasv($ftp, true);
    if (ftp_get($ftp, __DIR__ . '/../resources/views/passenger/executive/index.blade.php', 'web/406665014.student.yru.ac.th/public_html/resources/views/passenger/executive/index.blade.php', FTP_BINARY)) {
        echo "INDEX RESTORED SUCCESSFUL\n";
    } else {
        echo "GET FAILED\n";
    }
    ftp_close($ftp);
} else {
    echo "LOGIN FAILED\n";
}
