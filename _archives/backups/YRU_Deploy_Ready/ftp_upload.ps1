# =========================================
# YRU Train Tracking System - FTP Upload Script
# =========================================

$ftpHost     = "ftp.student.yru.ac.th"
$ftpUser     = "S406665014"
$ftpPass     = "406665014"
$localRoot   = "C:\Users\ASUS\Downloads\YRU-Train-Tracking-System"

# --- กำหนด path บนเซิร์ฟเวอร์ ---
# Laravel: โฟลเดอร์ public/ → public_html/
# ไฟล์อื่นๆ → ไว้ใน laravel/ (อยู่เหนือ public_html)

$serverPublicHtml = "web/406665014.student.yru.ac.th/public_html/public"
$serverAppRoot    = "web/406665014.student.yru.ac.th/public_html"

# โฟลเดอร์และไฟล์ที่ไม่ต้องอัพ
$excludeFolders = @("node_modules", ".git", "vendor\filament", "storage\logs", "storage\framework\cache")
$excludeFiles   = @("*.log", "ftp_upload.ps1", "yru_train_project_fixed.zip", "*.sql", "composer.phar")

# =========================================
# Helper Functions
# =========================================

function Get-FtpCredential {
    return [System.Net.NetworkCredential]::new($ftpUser, $ftpPass)
}

function Ensure-FtpDirectory {
    param([string]$ftpPath)
    $uri = "ftp://$ftpHost/$ftpPath"
    try {
        $req = [System.Net.FtpWebRequest]::Create($uri)
        $req.Credentials = Get-FtpCredential
        $req.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
        $req.UseBinary = $true
        $req.KeepAlive = $false
        $resp = $req.GetResponse()
        $resp.Close()
        Write-Host "  [DIR]  Created: $ftpPath" -ForegroundColor Green
    } catch {
        # Directory may already exist - ignore error
    }
}

function Upload-FtpFile {
    param([string]$localFile, [string]$ftpPath)
    $uri = "ftp://$ftpHost/$ftpPath"
    try {
        $req = [System.Net.FtpWebRequest]::Create($uri)
        $req.Credentials = Get-FtpCredential
        $req.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
        $req.UseBinary = $true
        $req.KeepAlive = $false
        $req.UsePassive = $true

        $fileContent = [System.IO.File]::ReadAllBytes($localFile)
        $req.ContentLength = $fileContent.Length

        $stream = $req.GetRequestStream()
        $stream.Write($fileContent, 0, $fileContent.Length)
        $stream.Close()

        $resp = $req.GetResponse()
        $resp.Close()
        return $true
    } catch {
        Write-Host "  [ERR]  Failed: $ftpPath - $_" -ForegroundColor Red
        return $false
    }
}

function Should-Exclude {
    param([string]$relativePath)
    foreach ($ex in $excludeFolders) {
        if ($relativePath -like "$ex*" -or $relativePath -like "*\$ex\*") {
            return $true
        }
    }
    foreach ($ex in $excludeFiles) {
        if ($relativePath -like $ex) {
            return $true
        }
    }
    return $false
}

function Upload-Directory {
    param([string]$localDir, [string]$ftpDir)

    $items = Get-ChildItem -Path $localDir -ErrorAction SilentlyContinue

    foreach ($item in $items) {
        $relativeName = $item.Name
        $relPath = "$ftpDir/$relativeName"

        if ($item.PSIsContainer) {
            # Skip excluded folders
            $skipFolder = $false
            foreach ($ex in $excludeFolders) {
                if ($relativeName -eq $ex -or $item.FullName -like "*\$ex") {
                    $skipFolder = $true
                    break
                }
            }
            if ($skipFolder) {
                Write-Host "  [SKIP] $relativeName/" -ForegroundColor DarkGray
                continue
            }

            Ensure-FtpDirectory -ftpPath $relPath
            Upload-Directory -localDir $item.FullName -ftpDir $relPath
        } else {
            # Skip excluded files
            $skipFile = $false
            foreach ($ex in $excludeFiles) {
                if ($relativeName -like $ex) {
                    $skipFile = $true
                    break
                }
            }
            if ($skipFile) {
                Write-Host "  [SKIP] $relativeName" -ForegroundColor DarkGray
                continue
            }

            $fileSize = [math]::Round($item.Length / 1KB, 1)
            Write-Host "  [UP]   $relPath ($fileSize KB)" -ForegroundColor Cyan
            Upload-FtpFile -localFile $item.FullName -ftpPath $relPath
        }
    }
}

# =========================================
# Main Upload
# =========================================

Write-Host ""
Write-Host "=======================================" -ForegroundColor Yellow
Write-Host " YRU Train Tracking - FTP Upload" -ForegroundColor Yellow
Write-Host "=======================================" -ForegroundColor Yellow
Write-Host " Host    : $ftpHost" -ForegroundColor White
Write-Host " User    : $ftpUser" -ForegroundColor White
Write-Host " Target  : public_html/ + laravel/" -ForegroundColor White
Write-Host "=======================================" -ForegroundColor Yellow
Write-Host ""

# Step 1: สร้าง root directories
Write-Host "[1/3] Creating server directories..." -ForegroundColor Magenta
Ensure-FtpDirectory -ftpPath $serverAppRoot
Ensure-FtpDirectory -ftpPath $serverPublicHtml

# Step 2: อัพโหลด public/ folder ไปที่ public_html/
Write-Host ""
Write-Host "[2/3] Uploading public/ -> public_html/ ..." -ForegroundColor Magenta
$publicLocal = "$localRoot\public"
Upload-Directory -localDir $publicLocal -ftpDir $serverPublicHtml

# Step 3: อัพโหลดไฟล์ Laravel ทั้งหมด (ยกเว้น public/) ไปที่ laravel/
Write-Host ""
Write-Host "[3/3] Uploading Laravel app -> laravel/ ..." -ForegroundColor Magenta

$appFolders = @("app", "bootstrap", "config", "database", "resources", "routes", "storage", "tests")
$appFiles   = @(".env", ".env.example", ".htaccess", "artisan", "composer.json", "composer.lock", "package.json", "phpunit.xml", "vite.config.js")

# อัพโฟลเดอร์
foreach ($folder in $appFolders) {
    $localPath = "$localRoot\$folder"
    if (Test-Path $localPath) {
        Write-Host ""
        Write-Host "  -> $folder/" -ForegroundColor Yellow
        Ensure-FtpDirectory -ftpPath "$serverAppRoot/$folder"
        Upload-Directory -localDir $localPath -ftpDir "$serverAppRoot/$folder"
    }
}

# อัพไฟล์รูทโปรเจกต์
Write-Host ""
Write-Host "  -> Root files..." -ForegroundColor Yellow
foreach ($file in $appFiles) {
    $localPath = "$localRoot\$file"
    if (Test-Path $localPath) {
        $item = Get-Item $localPath
        $fileSize = [math]::Round($item.Length / 1KB, 1)
        Write-Host "  [UP]   $serverAppRoot/$file ($fileSize KB)" -ForegroundColor Cyan
        Upload-FtpFile -localFile $localPath -ftpPath "$serverAppRoot/$file"
    }
}

Write-Host ""
Write-Host "=======================================" -ForegroundColor Green
Write-Host " Upload Complete!" -ForegroundColor Green
Write-Host "=======================================" -ForegroundColor Green
Write-Host ""
Write-Host "Next steps:" -ForegroundColor White
Write-Host "  1. SSH/cPanel: แก้ public/index.php ให้ชี้ไปที่ ../laravel/..." -ForegroundColor Gray
Write-Host "  2. ตั้งค่า .env บนเซิร์ฟเวอร์ (DB_HOST, DB_NAME, DB_USER, DB_PASS)" -ForegroundColor Gray
Write-Host "  3. รัน: php artisan migrate --seed" -ForegroundColor Gray
Write-Host ""
