<?php
$conn = @ftp_connect('ftp.student.yru.ac.th', 21, 15);
if (!$conn || !@ftp_login($conn, 'S406665014', '406665014')) {
    die("OLD FTP login failed\n");
}
ftp_pasv($conn, true);

$dumperCode = <<<'CODE'
<?php
header('Content-Type: application/json; charset=utf-8');
set_time_limit(300);

try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=S406665014_db;charset=utf8mb4", "S406665014_db", "406665014", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    $dump = [];
    foreach ($tables as $table) {
        $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        $dump[$table] = $rows;
    }

    echo json_encode(['status' => 'success', 'data' => $dump], JSON_UNESCAPED_UNICODE);
} catch (Exception $e) {
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
CODE;

$temp = tempnam(sys_get_temp_dir(), 'old_dump');
file_put_contents($temp, $dumperCode);
// Upload to both root and public/
ftp_put($conn, 'web/406665014.student.yru.ac.th/public_html/_export_full_db.php', $temp, FTP_BINARY);
ftp_put($conn, 'web/406665014.student.yru.ac.th/public_html/public/_export_full_db.php', $temp, FTP_BINARY);
unlink($temp);
ftp_close($conn);
echo "Uploaded to public/\n";
