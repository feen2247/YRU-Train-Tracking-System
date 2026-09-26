<?php
$dump = json_decode(file_get_contents(__DIR__ . '/old_db_dump.json'), true);
$cacheRows = $dump['data']['cache'];

echo "=== RESTORING ALL " . count($cacheRows) . " CACHE ROWS TO NEW HOST ===\n";

$importer = <<<'CODE'
<?php
header('Content-Type: text/plain; charset=utf-8');
$dump = json_decode(file_get_contents('php://input'), true);

try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=s406665014;charset=utf8mb4", "s406665014", "Tasnee_047", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $pdo->exec("TRUNCATE TABLE `cache`");
    $stmt = $pdo->prepare("INSERT INTO `cache` (`key`, `value`, `expiration`) VALUES (?, ?, ?)");

    $count = 0;
    foreach ($dump as $row) {
        $stmt->execute([$row['key'], $row['value'], $row['expiration']]);
        $count++;
    }

    echo "SUCCESS: Restored $count cache rows to new host.\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
}
CODE;

$conn = @ftp_connect('host.site.yru.ac.th', 21, 30);
if (!$conn || !@ftp_login($conn, 's406665014', 'Tasnee_047')) {
    die("FTP login failed\n");
}
ftp_pasv($conn, true);

$temp = tempnam(sys_get_temp_dir(), 'cache_restorer');
file_put_contents($temp, $importer);
ftp_put($conn, 'htdocs/406665014.site.yru.ac.th/_restore_cache.php', $temp, FTP_BINARY);
unlink($temp);
ftp_close($conn);

$ch = curl_init('https://406665014.site.yru.ac.th/_restore_cache.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($cacheRows));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
$res = curl_exec($ch);
curl_close($ch);

echo $res . "\n";

// Cleanup
$conn = @ftp_connect('host.site.yru.ac.th', 21, 30);
if ($conn && @ftp_login($conn, 's406665014', 'Tasnee_047')) {
    ftp_pasv($conn, true);
    @ftp_delete($conn, 'htdocs/406665014.site.yru.ac.th/_restore_cache.php');
    ftp_close($conn);
}
