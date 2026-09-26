<?php
$conn = ftp_connect('ftp.student.yru.ac.th');
if (!$conn) die("FTP Connection Failed\n");
if (!ftp_login($conn, 'S406665014', '406665014')) die("FTP Login Failed\n");
ftp_pasv($conn, true);

$list = ftp_nlist($conn, 'web/406665014.student.yru.ac.th/public_html/database');
print_r($list);

// Download database.sqlite from live server to scratch to inspect live users
if (in_array('web/406665014.student.yru.ac.th/public_html/database/database.sqlite', (array)$list)) {
    ftp_get($conn, __DIR__ . '/live_database.sqlite', 'web/406665014.student.yru.ac.th/public_html/database/database.sqlite', FTP_BINARY);
    echo "Downloaded live database.sqlite\n";
}

ftp_close($conn);
