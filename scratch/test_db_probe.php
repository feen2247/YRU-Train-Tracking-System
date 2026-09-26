<?php
$conn = @ftp_connect('host.site.yru.ac.th', 21, 10);
if ($conn && @ftp_login($conn, 's406665014', 'Tasnee_047')) {
    ftp_pasv($conn, true);
    
    $dbTestCode = <<<'CODE'
<?php
header('Content-Type: application/json');
$res = [];
$hosts = ['127.0.0.1', 'localhost'];
foreach ($hosts as $host) {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=s406665014;charset=utf8mb4", "s406665014", "Tasnee_047", [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
        $res[$host] = [
            'status' => 'connected',
            'tables' => $tables
        ];
    } catch (Exception $e) {
        $res[$host] = [
            'status' => 'error',
            'message' => $e->getMessage()
        ];
    }
}
echo json_encode($res, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
CODE;

    $tempFile = tempnam(sys_get_temp_dir(), 'dbtest');
    file_put_contents($tempFile, $dbTestCode);
    ftp_put($conn, 'htdocs/406665014.site.yru.ac.th/_db_test.php', $tempFile, FTP_BINARY);
    unlink($tempFile);
    ftp_close($conn);
    echo "Uploaded _db_test.php\n";
}
