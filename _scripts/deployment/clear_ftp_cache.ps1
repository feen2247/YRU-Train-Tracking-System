# =========================================
# YRU Train Tracking - Clear Remote Blade Cache
# =========================================

$ftpHost = "host.site.yru.ac.th"
$ftpUser = "s406665014"
$ftpPass = "Tasnee_047"
$viewDir = "htdocs/406665014.site.yru.ac.th/storage/framework/views"

Write-Host "Connecting to FTP to purge compiled views..." -ForegroundColor Cyan

$req = [System.Net.FtpWebRequest]::Create("ftp://$ftpHost/$viewDir")
$req.Credentials = [System.Net.NetworkCredential]::new($ftpUser, $ftpPass)
$req.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
$req.UsePassive = $true

try {
    $resp = $req.GetResponse()
    $reader = [System.IO.StreamReader]::new($resp.GetResponseStream())
    $files = $reader.ReadToEnd().Split("`n") | Where-Object { $_ -match '\.php$' }
    $reader.Close()
    $resp.Close()

    $count = 0
    foreach ($f in $files) {
        $fName = $f.Trim()
        if ($fName) {
            $delReq = [System.Net.FtpWebRequest]::Create("ftp://$ftpHost/$viewDir/$fName")
            $delReq.Credentials = [System.Net.NetworkCredential]::new($ftpUser, $ftpPass)
            $delReq.Method = [System.Net.WebRequestMethods+Ftp]::DeleteFile
            $delResp = $delReq.GetResponse()
            $delResp.Close()
            $count++
        }
    }
    Write-Host "[SUCCESS] Deleted $count compiled view files on remote server." -ForegroundColor Green
} catch {
    Write-Host "[INFO] No cache views to delete or folder empty." -ForegroundColor Yellow
}
