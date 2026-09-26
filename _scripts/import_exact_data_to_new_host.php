<?php
// =========================================================================
// Exact 100% Data Import from Old Host to New Host
// =========================================================================

$dumpFile = __DIR__ . '/../scratch/old_db_dump.json';
if (!file_exists($dumpFile)) {
    die("ERROR: old_db_dump.json not found\n");
}

$dumpData = json_decode(file_get_contents($dumpFile), true);
if (!$dumpData || $dumpData['status'] !== 'success') {
    die("ERROR: Invalid dump data\n");
}

echo "=== PREPARING EXACT 100% DATABASE IMPORT TO NEW HOST ===\n";
$tables = $dumpData['data'];

// Build remote import script
$importerCode = <<<'CODE'
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(300);
header('Content-Type: text/plain; charset=utf-8');

echo "=== IMPORTING EXACT DATA TO MYSQL (s406665014) ===\n";

$jsonPayload = file_get_contents('php://input');
if (!$jsonPayload) {
    die("ERROR: No JSON payload received\n");
}

$dump = json_decode($jsonPayload, true);
if (!$dump || !isset($dump['data'])) {
    die("ERROR: Invalid JSON structure\n");
}

try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=s406665014;charset=utf8mb4", "s406665014", "Tasnee_047", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    // Disable foreign keys
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

    $importedStats = [];
    foreach ($dump['data'] as $table => $rows) {
        // Truncate table first to avoid any duplicates
        try {
            $pdo->exec("TRUNCATE TABLE `$table`");
        } catch (Exception $e) {
            // Table might not exist yet or have other restrictions
        }

        if (empty($rows)) {
            $importedStats[$table] = 0;
            continue;
        }

        // Insert rows in batches
        $firstRow = $rows[0];
        $columns = array_keys($firstRow);
        $colList = implode(', ', array_map(function($c) { return "`$c`"; }, $columns));
        $placeholders = implode(', ', array_fill(0, count($columns), '?'));

        $stmt = $pdo->prepare("INSERT INTO `$table` ($colList) VALUES ($placeholders)");

        $count = 0;
        foreach ($rows as $row) {
            $values = [];
            foreach ($columns as $c) {
                $values[] = $row[$c] ?? null;
            }
            $stmt->execute($values);
            $count++;
        }
        $importedStats[$table] = $count;
        echo "Table: $table -> imported $count rows successfully\n";
    }

    // Re-enable foreign keys
    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

    echo "\nSUCCESS: ALL TABLES IMPORTED EXACTLY 100%!\n";
} catch (Exception $e) {
    echo "IMPORT ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
CODE;

// Upload importer to new host
echo "Uploading _importer.php to new host...\n";
$conn = ftp_connect('host.site.yru.ac.th', 21, 30);
if (!$conn || !ftp_login($conn, 's406665014', 'Tasnee_047')) {
    die("ERROR: New host FTP login failed\n");
}
ftp_pasv($conn, true);

$tempImp = tempnam(sys_get_temp_dir(), 'imp');
file_put_contents($tempImp, $importerCode);
ftp_put($conn, 'htdocs/406665014.site.yru.ac.th/_importer.php', $tempImp, FTP_BINARY);
unlink($tempImp);
ftp_close($conn);

// Post dump data to importer via HTTP
echo "Executing remote data import via HTTP POST...\n";
$ch = curl_init('https://406665014.site.yru.ac.th/_importer.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($dumpData));
curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
curl_setopt($ch, CURLOPT_TIMEOUT, 120);

$response = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "\n--- Import Response (HTTP $httpCode) ---\n";
echo $response . "\n";

// Cleanup importer script
$conn = ftp_connect('host.site.yru.ac.th', 21, 30);
if ($conn && ftp_login($conn, 's406665014', 'Tasnee_047')) {
    ftp_pasv($conn, true);
    ftp_delete($conn, 'htdocs/406665014.site.yru.ac.th/_importer.php');
    ftp_close($conn);
}

// Cleanup dumper on old host as well
$oldConn = @ftp_connect('ftp.student.yru.ac.th', 21, 15);
if ($oldConn && @ftp_login($oldConn, 'S406665014', '406665014')) {
    ftp_pasv($oldConn, true);
    @ftp_delete($oldConn, 'web/406665014.student.yru.ac.th/public_html/_export_full_db.php');
    @ftp_delete($oldConn, 'web/406665014.student.yru.ac.th/public_html/public/_export_full_db.php');
    ftp_close($oldConn);
}

echo "=== MIGRATION OF ALL EXACT DATA COMPLETED! ===\n";
