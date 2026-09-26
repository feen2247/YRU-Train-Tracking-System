<?php
$conn = @ftp_connect('host.site.yru.ac.th', 21, 30);
if (!$conn || !@ftp_login($conn, 's406665014', 'Tasnee_047')) {
    die("FTP login failed\n");
}
ftp_pasv($conn, true);
ftp_put($conn, 'htdocs/406665014.site.yru.ac.th/.env', __DIR__ . '/../.env.production', FTP_BINARY);
ftp_close($conn);
echo "Uploaded .env to new host\n";
