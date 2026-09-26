<?php
$conn = ftp_connect('ftp.student.yru.ac.th');
if (!$conn) die("FTP failed\n");
if (!ftp_login($conn, 'S406665014', '406665014')) die("FTP login failed\n");
ftp_pasv($conn, true);

$viewDir = 'web/406665014.student.yru.ac.th/public_html/storage/framework/views';
$files = ftp_nlist($conn, $viewDir);
echo "Files found in view cache:\n";
print_r($files);

if (is_array($files)) {
    foreach ($files as $f) {
        if (substr($f, -4) === '.php') {
            $path = (strpos($f, '/') !== false) ? $f : $viewDir . '/' . $f;
            $res = ftp_delete($conn, $path);
            echo "Deleting $path: " . ($res ? "SUCCESS" : "FAILED") . "\n";
        }
    }
}

ftp_close($conn);
