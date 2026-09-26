$ftpHost     = "ftp.student.yru.ac.th"
$ftpUser     = "S406665014"
$ftpPass     = "406665014"
$targetDir   = "web/406665014.student.yru.ac.th/public_html/storage/framework/views"

function Get-FtpCredential {
    return [System.Net.NetworkCredential]::new($ftpUser, $ftpPass)
}

try {
    # List files in the directory
    $uri = "ftp://$ftpHost/$targetDir/"
    $req = [System.Net.FtpWebRequest]::Create($uri)
    $req.Credentials = Get-FtpCredential
    $req.Method = [System.Net.WebRequestMethods+Ftp]::ListDirectory
    $resp = $req.GetResponse()
    $reader = [System.IO.StreamReader]::new($resp.GetResponseStream())
    $files = $reader.ReadToEnd() -split "`n" | Where-Object { $_.Trim() -ne "" }
    $reader.Close()
    $resp.Close()

    foreach ($file in $files) {
        $fileName = $file.Trim()
        if ($fileName -match '\.php$') {
            $delUri = "ftp://$ftpHost/$targetDir/$fileName"
            Write-Host "Deleting $delUri"
            $delReq = [System.Net.FtpWebRequest]::Create($delUri)
            $delReq.Credentials = Get-FtpCredential
            $delReq.Method = [System.Net.WebRequestMethods+Ftp]::DeleteFile
            try {
                $delResp = $delReq.GetResponse()
                $delResp.Close()
                Write-Host "  -> Deleted successfully." -ForegroundColor Green
            } catch {
                Write-Host "  -> Failed to delete: $_" -ForegroundColor Red
            }
        }
    }
    Write-Host "Cache clearing completed." -ForegroundColor Cyan
} catch {
    Write-Host "Error listing directory: $_" -ForegroundColor Red
}
