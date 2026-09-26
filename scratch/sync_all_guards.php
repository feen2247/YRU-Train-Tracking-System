<?php
$conn = ftp_connect('ftp.student.yru.ac.th');
if (!$conn) die("FTP Connection Failed\n");
if (!ftp_login($conn, 'S406665014', '406665014')) die("FTP Login Failed\n");
ftp_pasv($conn, true);

$files = [
    'resources/views/passenger/vehicle-head/index.blade.php',
    'resources/views/passenger/maintenance/index.blade.php',
    'resources/views/passenger/admin/index.blade.php',
    'resources/views/passenger/executive/index.blade.php',
    'resources/views/passenger/tracking/index.blade.php',
    'resources/views/passenger/welcome/index.blade.php',
    'app/Http/Controllers/AuthController.php',
    'routes/web.php',
    'database/seeders/UserSeeder.php',
    'database/database.sqlite',
];

foreach ($files as $file) {
    $remote = 'web/406665014.student.yru.ac.th/public_html/' . str_replace('\\', '/', $file);
    if (ftp_put($conn, $remote, __DIR__ . '/../' . $file, FTP_BINARY)) {
        echo "Uploaded: $file -> $remote\n";
    } else {
        echo "FAILED: $file\n";
    }
}

// Purge remote compiled views
$viewDir = 'web/406665014.student.yru.ac.th/public_html/storage/framework/views';
$cachedViews = ftp_nlist($conn, $viewDir);
if (is_array($cachedViews)) {
    foreach ($cachedViews as $cv) {
        if (substr($cv, -4) === '.php') {
            @ftp_delete($conn, $cv);
        }
    }
    echo "Purged remote view cache on student server.\n";
}

ftp_close($conn);
