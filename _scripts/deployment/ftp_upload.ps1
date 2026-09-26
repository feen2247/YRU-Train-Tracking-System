# =========================================
# YRU Train Tracking System - FTP Upload Script
# =========================================

$ftpHost     = "host.site.yru.ac.th"
$ftpUser     = "s406665014"
$ftpPass     = "Tasnee_047"
$localRoot   = "C:\Users\ASUS\Downloads\YRU-Train-Tracking-System"

$serverAppRoot = "htdocs/406665014.site.yru.ac.th"

# โฟลเดอร์และไฟล์ที่ไม่ต้องอัพ
$excludeFolders = @("node_modules", ".git", "storage\logs", "storage\framework\cache", "scratch", "_archives")
$excludeFiles   = @("*.log", "ftp_upload.ps1", "*.zip", "*.sql")

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

function Upload-Directory {
    param([string]$localDir, [string]$ftpDir)

    $items = Get-ChildItem -Path $localDir -ErrorAction SilentlyContinue

    foreach ($item in $items) {
        $relativeName = $item.Name
        $relPath = "$ftpDir/$relativeName"

        if ($item.PSIsContainer) {
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
Write-Host " Target  : $serverAppRoot" -ForegroundColor White
Write-Host "=======================================" -ForegroundColor Yellow
Write-Host ""

Ensure-FtpDirectory -ftpPath $serverAppRoot

$appFolders = @("app", "bootstrap", "config", "database", "public", "resources", "routes", "storage")
$appFiles   = @(".htaccess", "index.php", "artisan", "composer.json", "composer.lock", "package.json")

foreach ($folder in $appFolders) {
    $localPath = "$localRoot\$folder"
    if (Test-Path $localPath) {
        Write-Host ""
        Write-Host "  -> $folder/" -ForegroundColor Yellow
        Ensure-FtpDirectory -ftpPath "$serverAppRoot/$folder"
        Upload-Directory -localDir $localPath -ftpDir "$serverAppRoot/$folder"
    }
}

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

# Upload .env.production as .env
if (Test-Path "$localRoot\.env.production") {
    Write-Host "  [UP]   $serverAppRoot/.env (.env.production)" -ForegroundColor Cyan
    Upload-FtpFile -localFile "$localRoot\.env.production" -ftpPath "$serverAppRoot/.env"
}

Write-Host ""
Write-Host "=======================================" -ForegroundColor Green
Write-Host " Upload Complete!" -ForegroundColor Green
Write-Host "=======================================" -ForegroundColor Green
Write-Host ""
