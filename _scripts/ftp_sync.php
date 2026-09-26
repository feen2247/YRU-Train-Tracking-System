<?php
$conn = ftp_connect('host.site.yru.ac.th', 21, 30);
if (!$conn) {
    die("FTP Connection Failed\n");
}

if (!ftp_login($conn, 's406665014', 'Tasnee_047')) {
    die("FTP Login Failed\n");
}

ftp_pasv($conn, true);

$files = array_slice($argv, 1);
if (empty($files)) {
    $files = ['resources/views/passenger/tracking/index.blade.php'];
}

foreach ($files as $file) {
    $remote = 'htdocs/406665014.site.yru.ac.th/' . str_replace('\\', '/', $file);
    
    // Ensure parent directory exists
    $parts = explode('/', dirname($remote));
    $current = '';
    foreach ($parts as $p) {
        $current = $current === '' ? $p : "$current/$p";
        @ftp_mkdir($conn, $current);
    }

    if (ftp_put($conn, $remote, $file, FTP_BINARY)) {
        echo "Uploaded: $file -> $remote\n";
    } else {
        echo "FAILED: $file\n";
    }
}

// Purge remote compiled views if any blade file was uploaded
$hasBlade = false;
foreach ($files as $file) {
    if (strpos($file, '.blade.php') !== false) {
        $hasBlade = true;
        break;
    }
}

if ($hasBlade) {
    $viewDir = 'htdocs/406665014.site.yru.ac.th/storage/framework/views';
    $cachedViews = ftp_nlist($conn, $viewDir);
    if (is_array($cachedViews)) {
        foreach ($cachedViews as $cv) {
            if (substr($cv, -4) === '.php') {
                @ftp_delete($conn, $cv);
            }
        }
        echo "Purged remote view cache on new server.\n";
    }
}

ftp_close($conn);
