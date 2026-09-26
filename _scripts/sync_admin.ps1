$ftpHost = "ftp.student.yru.ac.th"
$ftpUser = "S406665014"
$ftpPass = "406665014"

$filesToUpload = @(
    @{
        Local = "C:\Users\ASUS\Downloads\YRU-Train-Tracking-System\resources\views\passenger\tracking\index.blade.php"
        Remote = "web/406665014.student.yru.ac.th/public_html/resources/views/passenger/tracking/index.blade.php"
    },
    @{
        Local = "C:\Users\ASUS\Downloads\YRU-Train-Tracking-System\resources\views\passenger\tracking\partials\header.blade.php"
        Remote = "web/406665014.student.yru.ac.th/public_html/resources/views/passenger/tracking/partials/header.blade.php"
    },
    @{
        Local = "C:\Users\ASUS\Downloads\YRU-Train-Tracking-System\resources\views\passenger\admin\index.blade.php"
        Remote = "web/406665014.student.yru.ac.th/public_html/resources/views/passenger/admin/index.blade.php"
    },
    @{
        Local = "C:\Users\ASUS\Downloads\YRU-Train-Tracking-System\resources\views\passenger\admin\partials\header.blade.php"
        Remote = "web/406665014.student.yru.ac.th/public_html/resources/views/passenger/admin/partials/header.blade.php"
    },
    @{
        Local = "C:\Users\ASUS\Downloads\YRU-Train-Tracking-System\resources\views\passenger\admin\partials\sidebar.blade.php"
        Remote = "web/406665014.student.yru.ac.th/public_html/resources/views/passenger/admin/partials/sidebar.blade.php"
    },
    @{
        Local = "C:\Users\ASUS\Downloads\YRU-Train-Tracking-System\resources\views\passenger\admin\pages\dashboard.blade.php"
        Remote = "web/406665014.student.yru.ac.th/public_html/resources/views/passenger/admin/pages/dashboard.blade.php"
    },
    @{
        Local = "C:\Users\ASUS\Downloads\YRU-Train-Tracking-System\resources\views\passenger\admin\pages\users.blade.php"
        Remote = "web/406665014.student.yru.ac.th/public_html/resources/views/passenger/admin/pages/users.blade.php"
    },
    @{
        Local = "C:\Users\ASUS\Downloads\YRU-Train-Tracking-System\resources\views\passenger\admin\pages\route.blade.php"
        Remote = "web/406665014.student.yru.ac.th/public_html/resources/views/passenger/admin/pages/route.blade.php"
    },
    @{
        Local = "C:\Users\ASUS\Downloads\YRU-Train-Tracking-System\resources\views\passenger\executive\index.blade.php"
        Remote = "web/406665014.student.yru.ac.th/public_html/resources/views/passenger/executive/index.blade.php"
    },
    @{
        Local = "C:\Users\ASUS\Downloads\YRU-Train-Tracking-System\resources\views\passenger\executive\partials\header.blade.php"
        Remote = "web/406665014.student.yru.ac.th/public_html/resources/views/passenger/executive/partials/header.blade.php"
    },
    @{
        Local = "C:\Users\ASUS\Downloads\YRU-Train-Tracking-System\resources\views\passenger\executive\partials\sidebar.blade.php"
        Remote = "web/406665014.student.yru.ac.th/public_html/resources/views/passenger/executive/partials/sidebar.blade.php"
    },
    @{
        Local = "C:\Users\ASUS\Downloads\YRU-Train-Tracking-System\routes\web.php"
        Remote = "web/406665014.student.yru.ac.th/public_html/routes/web.php"
    },
    @{
        Local = "C:\Users\ASUS\Downloads\YRU-Train-Tracking-System\app\Http\Controllers\AdminController.php"
        Remote = "web/406665014.student.yru.ac.th/public_html/app/Http/Controllers/AdminController.php"
    },
    @{
        Local = "C:\Users\ASUS\Downloads\YRU-Train-Tracking-System\app\Http\Controllers\ExecutiveController.php"
        Remote = "web/406665014.student.yru.ac.th/public_html/app/Http/Controllers/ExecutiveController.php"
    },
    @{
        Local = "C:\Users\ASUS\Downloads\YRU-Train-Tracking-System\resources\views\passenger\maintenance\index.blade.php"
        Remote = "web/406665014.student.yru.ac.th/public_html/resources/views/passenger/maintenance/index.blade.php"
    },
    @{
        Local = "C:\Users\ASUS\Downloads\YRU-Train-Tracking-System\resources\views\passenger\home\index.blade.php"
        Remote = "web/406665014.student.yru.ac.th/public_html/resources/views/passenger/home/index.blade.php"
    },
    @{
        Local = "C:\Users\ASUS\Downloads\YRU-Train-Tracking-System\resources\views\passenger\welcome\index.blade.php"
        Remote = "web/406665014.student.yru.ac.th/public_html/resources/views/passenger/welcome/index.blade.php"
    },
    @{
        Local = "C:\Users\ASUS\Downloads\YRU-Train-Tracking-System\resources\views\passenger\admin\pages\tram.blade.php"
        Remote = "web/406665014.student.yru.ac.th/public_html/resources/views/passenger/admin/pages/tram.blade.php"
    },
    @{
        Local = "C:\Users\ASUS\Downloads\YRU-Train-Tracking-System\resources\views\passenger\admin\pages\role.blade.php"
        Remote = "web/406665014.student.yru.ac.th/public_html/resources/views/passenger/admin/pages/role.blade.php"
    },
    @{
        Local = "C:\Users\ASUS\Downloads\YRU-Train-Tracking-System\resources\views\passenger\vehicle-head\index.blade.php"
        Remote = "web/406665014.student.yru.ac.th/public_html/resources/views/passenger/vehicle-head/index.blade.php"
    },
    @{
        Local = "C:\Users\ASUS\Downloads\YRU-Train-Tracking-System\app\Http\Controllers\AuthController.php"
        Remote = "web/406665014.student.yru.ac.th/public_html/app/Http/Controllers/AuthController.php"
    }
)

foreach ($item in $filesToUpload) {
    $uri = "ftp://$ftpHost/$($item.Remote)"
    Write-Host "Uploading $($item.Local) to $uri ..."
    $req = [System.Net.FtpWebRequest]::Create($uri)
    $req.Credentials = [System.Net.NetworkCredential]::new($ftpUser, $ftpPass)
    $req.Method = [System.Net.WebRequestMethods+Ftp]::UploadFile
    $req.UseBinary = $true
    $req.KeepAlive = $false
    $req.UsePassive = $true

    $fileContent = [System.IO.File]::ReadAllBytes($item.Local)
    $req.ContentLength = $fileContent.Length
    $stream = $req.GetRequestStream()
    $stream.Write($fileContent, 0, $fileContent.Length)
    $stream.Close()
    $resp = $req.GetResponse()
    Write-Host "FTP Upload Status: $($resp.StatusDescription)"
    $resp.Close()
}
