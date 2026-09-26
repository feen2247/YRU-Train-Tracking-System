<?php
$conn = @ftp_connect('ftp.student.yru.ac.th', 21, 15);
if ($conn && @ftp_login($conn, 'S406665014', '406665014')) {
    ftp_pasv($conn, true);
    ftp_put($conn, 'web/406665014.student.yru.ac.th/public_html/resources/views/passenger/home/index.blade.php', __DIR__ . '/../resources/views/passenger/home/index.blade.php', FTP_BINARY);
    ftp_put($conn, 'web/406665014.student.yru.ac.th/public_html/resources/views/passenger/tracking/index.blade.php', __DIR__ . '/../resources/views/passenger/tracking/index.blade.php', FTP_BINARY);
    
    // Purge student server view cache
    $viewDir = 'web/406665014.student.yru.ac.th/public_html/storage/framework/views';
    $cachedViews = @ftp_nlist($conn, $viewDir);
    if (is_array($cachedViews)) {
        foreach ($cachedViews as $cv) {
            if (substr($cv, -4) === '.php') {
                @ftp_delete($conn, $cv);
            }
        }
    }
    ftp_close($conn);
    echo "Synced to old student server as well.\n";
}
