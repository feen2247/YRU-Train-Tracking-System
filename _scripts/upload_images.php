<?php
$conn = ftp_connect('host.site.yru.ac.th', 21, 30);
if (!$conn || !ftp_login($conn, 's406665014', 'Tasnee_047')) {
    die("FTP Login Failed\n");
}
ftp_pasv($conn, true);

@ftp_mkdir($conn, 'htdocs/406665014.site.yru.ac.th/img');
@ftp_mkdir($conn, 'htdocs/406665014.site.yru.ac.th/public');
@ftp_mkdir($conn, 'htdocs/406665014.site.yru.ac.th/public/img');

$files = [
    'public/tracking-ev-logo.png' => [
        'htdocs/406665014.site.yru.ac.th/tracking-ev-logo.png', 
        'htdocs/406665014.site.yru.ac.th/public/tracking-ev-logo.png',
        'htdocs/406665014.site.yru.ac.th/img/tracking-ev-logo.png',
        'htdocs/406665014.site.yru.ac.th/public/img/tracking-ev-logo.png',
        'htdocs/406665014.site.yru.ac.th/logo.png',
        'htdocs/406665014.site.yru.ac.th/public/logo.png',
        'htdocs/406665014.site.yru.ac.th/img/logo.png',
        'htdocs/406665014.site.yru.ac.th/public/img/logo.png'
    ],
    'public/favicon.png' => [
        'htdocs/406665014.site.yru.ac.th/favicon.png', 
        'htdocs/406665014.site.yru.ac.th/public/favicon.png'
    ]
];

foreach ($files as $local => $remotes) {
    if (file_exists($local)) {
        foreach ($remotes as $remote) {
            if (ftp_put($conn, $remote, $local, FTP_BINARY)) {
                echo "SUCCESS: $local -> $remote\n";
            } else {
                echo "FAILED: $local -> $remote\n";
            }
        }
    }
}
ftp_close($conn);
