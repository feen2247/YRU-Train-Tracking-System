<?php
$conn = ftp_connect('ftp.student.yru.ac.th');
if (!$conn) die("FTP Connection Failed\n");
if (!ftp_login($conn, 'S406665014', '406665014')) die("FTP Login Failed\n");
ftp_pasv($conn, true);

// Ensure directories exist
@ftp_mkdir($conn, 'web/406665014.student.yru.ac.th/public_html/img');
@ftp_mkdir($conn, 'web/406665014.student.yru.ac.th/public_html/public');
@ftp_mkdir($conn, 'web/406665014.student.yru.ac.th/public_html/public/img');

$file = 'public/img/south-pk-seal.png';
$remotes = [
    'web/406665014.student.yru.ac.th/public_html/img/south-pk-seal.png',
    'web/406665014.student.yru.ac.th/public_html/public/img/south-pk-seal.png'
];

foreach ($remotes as $remote) {
    if (ftp_put($conn, $remote, $file, FTP_BINARY)) {
        echo "Uploaded: $file -> $remote\n";
    } else {
        echo "FAILED: $remote\n";
    }
}
ftp_close($conn);
