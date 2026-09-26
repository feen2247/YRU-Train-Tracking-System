<?php
$conn = @ftp_connect('host.site.yru.ac.th', 21, 10);
if ($conn && @ftp_login($conn, 's406665014', 'Tasnee_047')) {
    ftp_pasv($conn, true);
    echo "=== Listing /htdocs/406665014.site.yru.ac.th ===\n";
    $list = ftp_nlist($conn, 'htdocs/406665014.site.yru.ac.th');
    print_r($list);
    
    // Upload a test probe to htdocs/406665014.site.yru.ac.th
    $probeCode = "<?php echo 'HELLO_NEW_HOST_PHP=' . PHP_VERSION . '<br>'; phpinfo();";
    $tempFile = tempnam(sys_get_temp_dir(), 'probe');
    file_put_contents($tempFile, $probeCode);
    if (ftp_put($conn, 'htdocs/406665014.site.yru.ac.th/_probe.php', $tempFile, FTP_BINARY)) {
        echo "Uploaded _probe.php into htdocs/406665014.site.yru.ac.th successfully\n";
    }
    unlink($tempFile);
    ftp_close($conn);
}
