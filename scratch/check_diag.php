<?php
$conn = @ftp_connect('host.site.yru.ac.th', 21, 10);
if ($conn && @ftp_login($conn, 's406665014', 'Tasnee_047')) {
    ftp_pasv($conn, true);
    
    $diag = <<<'CODE'
<?php
header('Content-Type: text/plain; charset=utf-8');
echo "DISABLE_FUNCTIONS: " . ini_get('disable_functions') . "\n";
echo "MEMORY_LIMIT: " . ini_get('memory_limit') . "\n";
echo "MAX_EXECUTION_TIME: " . ini_get('max_execution_time') . "\n";

$zipFile = __DIR__ . '/deploy_package.zip';
echo "ZIP_EXISTS: " . (file_exists($zipFile) ? filesize($zipFile) . ' bytes' : 'NO') . "\n";
CODE;

    $temp = tempnam(sys_get_temp_dir(), 'diag');
    file_put_contents($temp, $diag);
    ftp_put($conn, 'htdocs/406665014.site.yru.ac.th/_diag.php', $temp, FTP_BINARY);
    unlink($temp);
    ftp_close($conn);
}
