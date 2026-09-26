<?php
$conn = @ftp_connect('ftp.student.yru.ac.th', 21, 15);
if (!$conn || !@ftp_login($conn, 'S406665014', '406665014')) {
    die("OLD FTP login failed\n");
}
ftp_pasv($conn, true);

echo "=== Checking storage and public folders on old server ===\n";

function listRecursive($conn, $dir) {
    $results = [];
    $list = @ftp_nlist($conn, $dir);
    if ($list) {
        foreach ($list as $item) {
            if ($item === '.' || $item === '..') continue;
            // Check if directory
            $sub = @ftp_nlist($conn, $item);
            if ($sub !== false && $sub !== [$item]) {
                $results = array_merge($results, listRecursive($conn, $item));
            } else {
                $results[] = $item;
            }
        }
    }
    return $results;
}

$storageFiles = listRecursive($conn, 'web/406665014.student.yru.ac.th/public_html/storage/app/public');
echo "storage/app/public files (" . count($storageFiles) . "):\n";
print_r($storageFiles);

$imgFiles = listRecursive($conn, 'web/406665014.student.yru.ac.th/public_html/public/img');
echo "public/img files (" . count($imgFiles) . "):\n";
print_r($imgFiles);

$uploadsFiles = listRecursive($conn, 'web/406665014.student.yru.ac.th/public_html/public/uploads');
echo "public/uploads files (" . count($uploadsFiles) . "):\n";
print_r($uploadsFiles);

ftp_close($conn);
