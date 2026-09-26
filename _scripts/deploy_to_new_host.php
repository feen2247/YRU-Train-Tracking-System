<?php
// =========================================================================
// Automated Deployment Script to host.site.yru.ac.th
// =========================================================================

$ftpHost = 'host.site.yru.ac.th';
$ftpUser = 's406665014';
$ftpPass = 'Tasnee_047';
$remoteWebRoot = 'htdocs/406665014.site.yru.ac.th';
$liveUrl = 'https://406665014.site.yru.ac.th';

$localRoot = realpath(__DIR__ . '/..');
$zipPath = $localRoot . '/deploy_package.zip';

echo "===================================================\n";
echo " Deploying YRU Train Tracking to host.site.yru.ac.th\n";
echo " Target URL: $liveUrl\n";
echo "===================================================\n\n";

// Step 1: Create Deployment Zip
echo "[1/6] Packaging project into $zipPath ...\n";
if (file_exists($zipPath)) {
    unlink($zipPath);
}

$zip = new ZipArchive();
if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
    die("ERROR: Cannot create zip archive\n");
}

$foldersToInclude = [
    'app',
    'bootstrap',
    'config',
    'database',
    'public',
    'resources',
    'routes',
    'storage',
    'vendor'
];

$filesToInclude = [
    'artisan',
    'composer.json',
    'composer.lock',
    'package.json',
    '.htaccess',
    'index.php'
];

// Add single files
foreach ($filesToInclude as $file) {
    $fullPath = $localRoot . '/' . $file;
    if (file_exists($fullPath)) {
        $zip->addFile($fullPath, $file);
    }
}

// Add .env from .env.production
$envProdPath = $localRoot . '/.env.production';
if (file_exists($envProdPath)) {
    $zip->addFile($envProdPath, '.env');
}

// Add folders recursively
foreach ($foldersToInclude as $folder) {
    $dir = $localRoot . '/' . $folder;
    if (!is_dir($dir)) continue;

    $iterator = new RecursiveIteratorIterator(
        new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
        RecursiveIteratorIterator::SELF_FIRST
    );

    foreach ($iterator as $item) {
        $relativePath = substr($item->getPathname(), strlen($localRoot) + 1);
        $relativePath = str_replace('\\', '/', $relativePath);

        // Skip unwanted caches or temp files
        if (strpos($relativePath, 'storage/logs/') !== false && !$item->isDir()) continue;
        if (strpos($relativePath, 'storage/framework/cache/data/') !== false && !$item->isDir()) continue;
        if (strpos($relativePath, 'storage/framework/sessions/') !== false && !$item->isDir()) continue;
        if (strpos($relativePath, 'storage/framework/views/') !== false && !$item->isDir()) continue;

        if ($item->isDir()) {
            $zip->addEmptyDir($relativePath);
        } else {
            $zip->addFile($item->getPathname(), $relativePath);
        }
    }
}

$zip->close();
$zipSizeMb = round(filesize($zipPath) / 1024 / 1024, 2);
echo "Package created successfully: {$zipSizeMb} MB\n\n";

// Step 2: Connect FTP
echo "[2/6] Connecting to FTP ($ftpHost) ...\n";
$conn = ftp_connect($ftpHost, 21, 60);
if (!$conn || !ftp_login($conn, $ftpUser, $ftpPass)) {
    die("ERROR: FTP connection/login failed\n");
}
ftp_pasv($conn, true);
echo "FTP connected successfully!\n\n";

// Step 3: Upload Extractor Script
echo "[3/6] Uploading zip package and remote extractor ...\n";

$extractorCode = <<<'CODE'
<?php
set_time_limit(300);
ini_set('memory_limit', '512M');
header('Content-Type: text/plain; charset=utf-8');

echo "=== UNPACKING DEPLOYMENT PACKAGE ===\n";
$zipFile = __DIR__ . '/deploy_package.zip';
if (!file_exists($zipFile)) {
    die("ERROR: deploy_package.zip not found\n");
}

$zip = new ZipArchive();
if ($zip->open($zipFile) === true) {
    $zip->extractTo(__DIR__);
    $zip->close();
    echo "SUCCESS: Extracted all files successfully.\n";
    @unlink($zipFile);
} else {
    die("ERROR: Failed to open zip file\n");
}

// Ensure storage subdirectories exist and writable
$storageDirs = [
    __DIR__ . '/storage',
    __DIR__ . '/storage/app',
    __DIR__ . '/storage/app/public',
    __DIR__ . '/storage/framework',
    __DIR__ . '/storage/framework/cache',
    __DIR__ . '/storage/framework/cache/data',
    __DIR__ . '/storage/framework/sessions',
    __DIR__ . '/storage/framework/views',
    __DIR__ . '/storage/logs',
    __DIR__ . '/bootstrap/cache',
];

foreach ($storageDirs as $sDir) {
    if (!is_dir($sDir)) {
        @mkdir($sDir, 0777, true);
    }
    @chmod($sDir, 0777);
}

// Clear any stale cached views/config
$viewFiles = glob(__DIR__ . '/storage/framework/views/*.php');
if ($viewFiles) {
    foreach ($viewFiles as $vf) {
        @unlink($vf);
    }
}
$configCache = __DIR__ . '/bootstrap/cache/config.php';
if (file_exists($configCache)) @unlink($configCache);
$routesCache = __DIR__ . '/bootstrap/cache/routes-v7.php';
if (file_exists($routesCache)) @unlink($routesCache);

echo "SUCCESS: Storage directories prepared and cache cleared.\n";
CODE;

$tempExtractor = tempnam(sys_get_temp_dir(), 'ext');
file_put_contents($tempExtractor, $extractorCode);
ftp_put($conn, $remoteWebRoot . '/_extractor.php', $tempExtractor, FTP_BINARY);
unlink($tempExtractor);

// Upload deploy_package.zip
echo "Uploading {$zipSizeMb} MB archive to {$remoteWebRoot}/deploy_package.zip (this may take 1-2 minutes)...\n";
$uploadSuccess = ftp_put($conn, $remoteWebRoot . '/deploy_package.zip', $zipPath, FTP_BINARY);
if (!$uploadSuccess) {
    die("ERROR: Failed to upload deploy_package.zip via FTP\n");
}
echo "Upload complete!\n\n";

// Step 4: Run Extractor via HTTP
echo "[4/6] Executing remote extractor via HTTP ...\n";
$ctx = stream_context_create([
    'ssl' => ['verify_peer' => false, 'verify_peer_name' => false],
    'http' => ['timeout' => 120]
]);
$extractResult = file_get_contents($liveUrl . '/_extractor.php', false, $ctx);
echo $extractResult . "\n";

// Step 5: Upload and Run Database Migrations & Seeders
echo "[5/6] Setting up Database and Master Data on MySQL (s406665014) ...\n";

$dbSetupCode = <<<'CODE'
<?php
set_time_limit(300);
ini_set('display_errors', 1);
error_reporting(E_ALL);
header('Content-Type: text/plain; charset=utf-8');

require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

echo "=== RUNNING DATABASE MIGRATIONS & SEEDS ===\n";

try {
    // Run migrations
    echo "Running artisan migrate ...\n";
    Artisan::call('migrate', ['--force' => true]);
    echo Artisan::output() . "\n";

    // Run database seeder if needed
    echo "Running database seeder ...\n";
    Artisan::call('db:seed', ['--force' => true]);
    echo Artisan::output() . "\n";

    // Ensure all critical users exist
    $usersData = [
        [
            'user_id' => 'USR-000001',
            'employee_id' => '69001',
            'username' => 'admin',
            'prefix' => 'นาย',
            'first_name' => 'ผู้ดูแลระบบ',
            'last_name' => 'สถานี',
            'name' => 'นายผู้ดูแลระบบ สถานี',
            'email' => 'admin@yru.ac.th',
            'password' => Hash::make('69001'),
            'user_role' => 'Admin',
            'usage_rights' => 'Active',
            'status' => 'ใช้งาน'
        ],
        [
            'user_id' => 'USR-000014',
            'employee_id' => '69014',
            'username' => 'hadee',
            'prefix' => 'นาย',
            'first_name' => 'ฮาดิ',
            'last_name' => 'ลือแมะ',
            'name' => 'นายฮาดิ ลือแมะ',
            'email' => 'hadee@yru.ac.th',
            'password' => Hash::make('69014'),
            'user_role' => 'vehicle_head',
            'usage_rights' => 'Active',
            'status' => 'ใช้งาน'
        ],
        [
            'user_id' => 'USR-000018',
            'employee_id' => '69014',
            'username' => 'suthin',
            'prefix' => 'นาย',
            'first_name' => 'สุทิน',
            'last_name' => 'มีสุข',
            'name' => 'นายสุทิน มีสุข',
            'email' => 'suthin.m@yru.ac.th',
            'password' => Hash::make('69014'),
            'user_role' => 'vehicle_head',
            'usage_rights' => 'Active',
            'status' => 'ใช้งาน'
        ],
        [
            'user_id' => 'USR-000013',
            'employee_id' => '69013',
            'username' => 'prasan',
            'prefix' => 'นาย',
            'first_name' => 'ประสาน',
            'last_name' => 'งานดี',
            'name' => 'นายประสาน งานดี',
            'email' => 'prasan.g@yru.ac.th',
            'password' => Hash::make('69013'),
            'user_role' => 'Mechanic',
            'usage_rights' => 'Active',
            'status' => 'ใช้งาน'
        ],
        [
            'user_id' => 'USR-000002',
            'employee_id' => '69002',
            'username' => 'driver1',
            'prefix' => 'นาย',
            'first_name' => 'สมศักดิ์',
            'last_name' => 'ขับขี่ดี',
            'name' => 'นายสมศักดิ์ ขับขี่ดี',
            'email' => 'driver1@yru.ac.th',
            'password' => Hash::make('69002'),
            'user_role' => 'Driver',
            'usage_rights' => 'Active',
            'status' => 'ใช้งาน'
        ]
    ];

    foreach ($usersData as $u) {
        $user = User::where('username', $u['username'])
            ->orWhere('email', $u['email'])
            ->orWhere('user_id', $u['user_id'])
            ->first();
        if (!$user) {
            $user = new User();
        }
        foreach ($u as $k => $v) {
            $user->$k = $v;
        }
        $user->email_verified_at = now();
        $user->save();
        echo "Ensured user: {$user->username} ({$user->user_role})\n";
    }

    // List all tables and counts
    echo "\n=== DATABASE TABLES SUMMARY ===\n";
    $tables = DB::select("SHOW TABLES");
    $dbNameKey = 'Tables_in_' . config('database.connections.mysql.database');
    foreach ($tables as $t) {
        $tableName = $t->$dbNameKey;
        $cnt = DB::table($tableName)->count();
        echo "Table: $tableName -> $cnt rows\n";
    }

} catch (Exception $e) {
    echo "DB SETUP EXCEPTION: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
CODE;

$tempDb = tempnam(sys_get_temp_dir(), 'db_mig');
file_put_contents($tempDb, $dbSetupCode);
ftp_put($conn, $remoteWebRoot . '/_migrate_seed.php', $tempDb, FTP_BINARY);
unlink($tempDb);

$dbResult = file_get_contents($liveUrl . '/_migrate_seed.php', false, $ctx);
echo $dbResult . "\n";

// Step 6: Cleanup temporary deployment files
echo "[6/6] Cleaning up temporary migration & probe files ...\n";
$cleanupFiles = [
    '_extractor.php',
    '_migrate_seed.php',
    '_probe.php',
    '_db_test.php',
    '_zip_check.php',
    'deploy_package.zip'
];
foreach ($cleanupFiles as $cf) {
    @ftp_delete($conn, $remoteWebRoot . '/' . $cf);
}
ftp_close($conn);

if (file_exists($zipPath)) {
    unlink($zipPath);
}

echo "\n===================================================\n";
echo " DEPLOYMENT SUCCESSFUL!\n";
echo " Live URL: $liveUrl\n";
echo "===================================================\n";
