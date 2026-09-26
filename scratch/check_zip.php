<?php
$conn = @ftp_connect('host.site.yru.ac.th', 21, 10);
if ($conn && @ftp_login($conn, 's406665014', 'Tasnee_047')) {
    ftp_pasv($conn, true);
    $probe = "<?php echo 'ZIP_ENABLED=' . (class_exists('ZipArchive') ? 'YES' : 'NO') . PHP_EOL;";
    $tempFile = tempnam(sys_get_temp_dir(), 'zipcheck');
    file_put_contents($tempFile, $probe);
    ftp_put($conn, 'htdocs/406665014.site.yru.ac.th/_zip_check.php', $tempFile, FTP_BINARY);
    unlink($tempFile);
    ftp_close($conn);
}
