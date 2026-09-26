# Quick sync script for changed files
$ftpHost = "host.site.yru.ac.th"
$ftpUser = "s406665014"
$ftpPass = "Tasnee_047"
$serverAppRoot = "htdocs/406665014.site.yru.ac.th"
$localRoot = "C:\Users\ASUS\Downloads\YRU-Train-Tracking-System"

$filesToUpload = @(
    "app\Models\MaintenanceRequest.php",
    "app\Http\Controllers\MaintenanceController.php",
    "app\Http\Controllers\AuthController.php",
    "app\Http\Controllers\UserController.php",
    "resources\views\passenger\welcome\index.blade.php",
    "resources\views\passenger\home\index.blade.php",
    "resources\views\passenger\tracking\index.blade.php",
    "resources\views\passenger\executive\index.blade.php",
    "resources\views\passenger\maintenance\index.blade.php",
    "resources\views\passenger\maintenance\partials\printable-form-modal.blade.php",
    "resources\views\passenger\admin\index.blade.php",
    "resources\views\passenger\admin\pages\dashboard.blade.php",
    "resources\views\passenger\admin\pages\role.blade.php",
    "resources\views\passenger\admin\partials\header.blade.php",
    "resources\views\passenger\admin\partials\sidebar.blade.php",
    "resources\views\passenger\vehicle-head\index.blade.php",
    "resources\views\passenger\vehicle-head\partials\header.blade.php",
    "resources\views\passenger\vehicle-head\partials\sidebar.blade.php",
    "database\migrations\2026_08_22_000001_create_maintenance_requests_table.php",
    "routes\web.php"
)

function Ensure-FtpDirectory($ftpDirPath) {
    $parts = $ftpDirPath.Trim('/').Split('/')
    $current = ""
    foreach ($part in $parts) {
        $current = if ($current) { "$current/$part" } else { $part }
        $uri = "ftp://$ftpHost/$current"
        try {
            $req = [System.Net.FtpWebRequest]::Create($uri)
            $req.Credentials = [System.Net.NetworkCredential]::new($ftpUser, $ftpPass)
            $req.Method = [System.Net.WebRequestMethods+Ftp]::MakeDirectory
            $req.UsePassive = $true
            $req.KeepAlive = $false
            $resp = $req.GetResponse()
            $resp.Close()
        } catch {
            # Ignore if directory already exists
        }
    }
}

function Upload-FtpFile($localFile, $ftpPath) {
    $parentDir = [System.IO.Path]::GetDirectoryName($ftpPath).Replace('\', '/')
    Ensure-FtpDirectory $parentDir

    $uri = "ftp://$ftpHost/$ftpPath"
    try {
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
        Write-Host "[SUCCESS] Uploaded $localFile -> $ftpPath" -ForegroundColor Green
    } catch {
        Write-Host "[ERROR] Failed to upload $localFile : $_" -ForegroundColor Red
    }
}

foreach ($f in $filesToUpload) {
    $localPath = Join-Path $localRoot $f
    $serverPath = "$serverAppRoot/" + ($f.Replace('\', '/'))
    if (Test-Path $localPath) {
        Upload-FtpFile $localPath $serverPath
    } else {
        Write-Host "[SKIP] Local file not found: $localPath" -ForegroundColor Yellow
    }
}
