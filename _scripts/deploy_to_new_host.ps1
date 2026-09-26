# =========================================================================
# YRU Train Tracking - Complete Deployment to host.site.yru.ac.th
# =========================================================================

$ftpHost = "host.site.yru.ac.th"
$ftpUser = "s406665014"
$ftpPass = "Tasnee_047"
$remoteWebRoot = "htdocs/406665014.site.yru.ac.th"
$liveUrl = "https://406665014.site.yru.ac.th"

$localRoot = "C:\Users\ASUS\Downloads\YRU-Train-Tracking-System"
$stagingDir = "$env:TEMP\yru_deploy_stage"
$zipPath = "$env:TEMP\yru_deploy_package.zip"

Write-Host ""
Write-Host "===================================================" -ForegroundColor Cyan
Write-Host " Deploying YRU Train Tracking System to New Host" -ForegroundColor Cyan
Write-Host " Target Host: $ftpHost" -ForegroundColor White
Write-Host " Target URL : $liveUrl" -ForegroundColor White
Write-Host "===================================================" -ForegroundColor Cyan
Write-Host ""

# Step 1: Clean & Prepare Staging
Write-Host "[1/6] Preparing staging directory..." -ForegroundColor Yellow
if (Test-Path $stagingDir) {
    Remove-Item -Recurse -Force $stagingDir
}
if (Test-Path $zipPath) {
    Remove-Item -Force $zipPath
}
New-Item -ItemType Directory -Path $stagingDir | Out-Null

$copyFolders = @("app", "bootstrap", "config", "database", "public", "resources", "routes", "vendor")
foreach ($folder in $copyFolders) {
    $src = "$localRoot\$folder"
    if (Test-Path $src) {
        Write-Host "  -> Copying $folder..." -ForegroundColor DarkGray
        Copy-Item -Path $src -Destination "$stagingDir\$folder" -Recurse -Force
    }
}

# Create storage hierarchy
Write-Host "  -> Preparing storage structure..." -ForegroundColor DarkGray
$storageDirs = @(
    "storage\app\public",
    "storage\framework\cache\data",
    "storage\framework\sessions",
    "storage\framework\views",
    "storage\logs"
)
foreach ($sd in $storageDirs) {
    New-Item -ItemType Directory -Path "$stagingDir\$sd" -Force | Out-Null
}

# Copy files
$copyFiles = @("artisan", "composer.json", "composer.lock", "package.json", ".htaccess", "index.php")
foreach ($file in $copyFiles) {
    $src = "$localRoot\$file"
    if (Test-Path $src) {
        Copy-Item -Path $src -Destination "$stagingDir\$file" -Force
    }
}

# Copy .env.production as .env
Copy-Item -Path "$localRoot\.env.production" -Destination "$stagingDir\.env" -Force
Write-Host "  -> .env configured from .env.production" -ForegroundColor Green

# Step 2: Compress to Zip using POSIX forward-slash entries
Write-Host ""
Write-Host "[2/6] Compressing staging directory to zip package (POSIX paths)..." -ForegroundColor Yellow
Add-Type -AssemblyName System.IO.Compression
Add-Type -AssemblyName System.IO.Compression.FileSystem

$zip = [System.IO.Compression.ZipFile]::Open($zipPath, [System.IO.Compression.ZipArchiveMode]::Create)
$allFiles = Get-ChildItem -Path $stagingDir -Recurse -File

foreach ($f in $allFiles) {
    $rel = $f.FullName.Substring($stagingDir.Length + 1).Replace('\', '/')
    [System.IO.Compression.ZipFileExtensions]::CreateEntryFromFile($zip, $f.FullName, $rel, [System.IO.Compression.CompressionLevel]::Fastest) | Out-Null
}
$zip.Dispose()

$zipSize = (Get-Item $zipPath).Length / 1MB
Write-Host "  -> Package size: $([math]::Round($zipSize, 2)) MB" -ForegroundColor Green

# Step 3: FTP Helper & Upload
Write-Host ""
Write-Host "[3/6] Uploading deployment package and extractor to $ftpHost..." -ForegroundColor Yellow

function Upload-FtpFile {
    param([string]$localFile, [string]$remoteFile)
    $uri = "ftp://$ftpHost/$remoteFile"
    $req = [System.Net.FtpWebRequest]::Create($uri)
    $req.Credentials = [System.Net.NetworkCredential]::new($ftpUser, $ftpPass)
    $req.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
    $req.UseBinary = $true
    $req.KeepAlive = $false
    $req.UsePassive = $true

    $bytes = [System.IO.File]::ReadAllBytes($localFile)
    $req.ContentLength = $bytes.Length
    $stream = $req.GetRequestStream()
    $stream.Write($bytes, 0, $bytes.Length)
    $stream.Close()
    $stream.Dispose()

    $resp = $req.GetResponse()
    $resp.Close()
    $resp.Dispose()
}

# Pure PHP Extractor (No disabled functions)
$extractorContent = @'
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(300);
ini_set('memory_limit', '512M');
header('Content-Type: text/plain; charset=utf-8');

echo "=== UNPACKING DEPLOYMENT PACKAGE ===\n";
$zipFile = __DIR__ . '/deploy_package.zip';
if (!file_exists($zipFile)) {
    die("ERROR: deploy_package.zip not found\n");
}

$zip = new ZipArchive();
$res = $zip->open($zipFile);
if ($res === true) {
    $count = $zip->numFiles;
    echo "Zip entries count: $count\n";
    
    // Extract file by file to ensure directories and paths are created properly
    for ($i = 0; $i < $count; $i++) {
        $entryName = $zip->getNameIndex($i);
        $entryName = str_replace('\\', '/', $entryName);
        $target = __DIR__ . '/' . $entryName;
        
        if (substr($entryName, -1) === '/') {
            if (!is_dir($target)) {
                @mkdir($target, 0777, true);
            }
            continue;
        }
        
        $dir = dirname($target);
        if (!is_dir($dir)) {
            @mkdir($dir, 0777, true);
        }
        
        $stream = $zip->getStream($zip->getNameIndex($i));
        if ($stream) {
            $fp = fopen($target, 'wb');
            if ($fp) {
                while (!feof($stream)) {
                    fwrite($fp, fread($stream, 8192));
                }
                fclose($fp);
            }
            fclose($stream);
        }
    }
    
    $zip->close();
    echo "SUCCESS: Extracted all files successfully.\n";
    @unlink($zipFile);
} else {
    die("ERROR: Failed to open zip file, code: $res\n");
}

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

echo "SUCCESS: Storage directories prepared.\n";
echo "AUTOLOAD_EXISTS: " . (file_exists(__DIR__ . '/vendor/autoload.php') ? 'YES' : 'NO') . "\n";
'@

$tempExtractor = "$env:TEMP\_extractor.php"
[System.IO.File]::WriteAllText($tempExtractor, $extractorContent)
Write-Host "  -> Uploading _extractor.php..." -ForegroundColor DarkGray
Upload-FtpFile $tempExtractor "$remoteWebRoot/_extractor.php"
Remove-Item -Force $tempExtractor

Write-Host "  -> Uploading $([math]::Round($zipSize, 2)) MB zip archive to server (please wait)..." -ForegroundColor Cyan
Upload-FtpFile $zipPath "$remoteWebRoot/deploy_package.zip"
Write-Host "  -> Upload complete!" -ForegroundColor Green

# Step 4: Run Extractor on Remote Server
Write-Host ""
Write-Host "[4/6] Extracting files on remote server..." -ForegroundColor Yellow
$extractOutput = curl.exe -k -s "$liveUrl/_extractor.php"
Write-Host $extractOutput -ForegroundColor White

# Step 5: Database Setup and Migration Runner
Write-Host ""
Write-Host "[5/6] Running Database Migrations and Seeders on MySQL (s406665014)..." -ForegroundColor Yellow

$dbSetupContent = @'
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(300);
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
    echo "Running artisan migrate:fresh --force ...\n";
    Artisan::call('migrate:fresh', ['--force' => true]);
    echo Artisan::output() . "\n";

    echo "Running artisan db:seed --force ...\n";
    Artisan::call('db:seed', ['--force' => true]);
    echo Artisan::output() . "\n";

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
'@

$tempDb = "$env:TEMP\_migrate_seed.php"
[System.IO.File]::WriteAllText($tempDb, $dbSetupContent)
Upload-FtpFile $tempDb "$remoteWebRoot/_migrate_seed.php"
Remove-Item -Force $tempDb

$dbOutput = curl.exe -k -s "$liveUrl/_migrate_seed.php"
Write-Host $dbOutput -ForegroundColor White

# Step 6: Cleanup
Write-Host ""
Write-Host "[6/6] Cleaning up temporary deployment files..." -ForegroundColor Yellow

function Delete-FtpFile {
    param([string]$remoteFile)
    try {
        $uri = "ftp://$ftpHost/$remoteFile"
        $req = [System.Net.FtpWebRequest]::Create($uri)
        $req.Credentials = [System.Net.NetworkCredential]::new($ftpUser, $ftpPass)
        $req.Method = [System.Net.WebRequestMethods+Ftp]::DeleteFile
        $resp = $req.GetResponse()
        $resp.Close()
    } catch {}
}

Delete-FtpFile "$remoteWebRoot/_extractor.php"
Delete-FtpFile "$remoteWebRoot/_migrate_seed.php"
Delete-FtpFile "$remoteWebRoot/_probe.php"
Delete-FtpFile "$remoteWebRoot/_db_test.php"
Delete-FtpFile "$remoteWebRoot/_zip_check.php"
Delete-FtpFile "$remoteWebRoot/_diag.php"
Delete-FtpFile "$remoteWebRoot/deploy_package.zip"

if (Test-Path $stagingDir) { Remove-Item -Recurse -Force $stagingDir }
if (Test-Path $zipPath) { Remove-Item -Force $zipPath }

Write-Host ""
Write-Host "===================================================" -ForegroundColor Green
Write-Host " DEPLOYMENT TO NEW HOST COMPLETED SUCCESSFULLY!" -ForegroundColor Green
Write-Host " Website URL: $liveUrl" -ForegroundColor Green
Write-Host "===================================================" -ForegroundColor Green
Write-Host ""
