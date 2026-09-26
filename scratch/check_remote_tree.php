<?php
$conn = @ftp_connect('host.site.yru.ac.th', 21, 10);
if ($conn && @ftp_login($conn, 's406665014', 'Tasnee_047')) {
    ftp_pasv($conn, true);
    echo "=== Listing htdocs/406665014.site.yru.ac.th ===\n";
    $list = ftp_nlist($conn, 'htdocs/406665014.site.yru.ac.th');
    print_r($list);
    ftp_close($conn);
}
