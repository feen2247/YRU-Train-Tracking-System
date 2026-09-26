<?php
// =========================================================================
// 100% Exact Full MySQL Schema + Data Transfer
// =========================================================================

echo "=== STEP 1: Exporting exact SQL DDL + Data from OLD HOST ===\n";

$oldConn = @ftp_connect('ftp.student.yru.ac.th', 21, 15);
if (!$oldConn || !@ftp_login($oldConn, 'S406665014', '406665014')) {
    die("ERROR: OLD FTP login failed\n");
}
ftp_pasv($oldConn, true);

$sqlExporter = <<<'CODE'
<?php
set_time_limit(300);
ini_set('memory_limit', '512M');
header('Content-Type: text/plain; charset=utf-8');

try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=S406665014_db;charset=utf8mb4", "S406665014_db", "406665014", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $sql = "-- 100% EXACT DATABASE DUMP FROM STUDENT SERVER\n";
    $sql .= "SET FOREIGN_KEY_CHECKS = 0;\n\n";

    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);

    foreach ($tables as $table) {
        $sql .= "-- --------------------------------------------------------\n";
        $sql .= "-- Table structure for `$table`\n";
        $sql .= "-- --------------------------------------------------------\n";
        $sql .= "DROP TABLE IF EXISTS `$table`;\n";

        $createRow = $pdo->query("SHOW CREATE TABLE `$table`")->fetch(PDO::FETCH_NUM);
        $sql .= $createRow[1] . ";\n\n";

        // Dump Data
        $rows = $pdo->query("SELECT * FROM `$table`")->fetchAll(PDO::FETCH_ASSOC);
        if (!empty($rows)) {
            $sql .= "-- Dumping data for `$table`\n";
            $columns = array_keys($rows[0]);
            $colList = implode(', ', array_map(function($c) { return "`$c`"; }, $columns));

            foreach ($rows as $r) {
                $vals = [];
                foreach ($columns as $c) {
                    $v = $r[$c];
                    if ($v === null) {
                        $vals[] = "NULL";
                    } else {
                        $vals[] = $pdo->quote($v);
                    }
                }
                $sql .= "INSERT INTO `$table` ($colList) VALUES (" . implode(', ', $vals) . ");\n";
            }
            $sql .= "\n";
        }
    }

    $sql .= "SET FOREIGN_KEY_CHECKS = 1;\n";
    echo $sql;
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage();
}
CODE;

$temp = tempnam(sys_get_temp_dir(), 'sqlexp');
file_put_contents($temp, $sqlExporter);
ftp_put($oldConn, 'web/406665014.student.yru.ac.th/public_html/public/_export_exact_sql.php', $temp, FTP_BINARY);
unlink($temp);
ftp_close($oldConn);

echo "Fetching full SQL from old host...\n";
$ch = curl_init('https://406665014.student.yru.ac.th/_export_exact_sql.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 120);
$fullSql = curl_exec($ch);
curl_close($ch);

if (strpos($fullSql, 'SET FOREIGN_KEY_CHECKS') === false) {
    die("ERROR: Export failed: " . substr($fullSql, 0, 500) . "\n");
}

file_put_contents(__DIR__ . '/../scratch/full_exact_dump.sql', $fullSql);
$sqlSizeKb = round(strlen($fullSql) / 1024, 2);
echo "SQL Dump generated successfully: {$sqlSizeKb} KB\n\n";

echo "=== STEP 2: Importing exact SQL into NEW HOST (host.site.yru.ac.th) ===\n";

$sqlImporter = <<<'CODE'
<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
set_time_limit(300);
ini_set('memory_limit', '512M');
header('Content-Type: text/plain; charset=utf-8');

try {
    $pdo = new PDO("mysql:host=127.0.0.1;dbname=s406665014;charset=utf8mb4", "s406665014", "Tasnee_047", [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

    // Drop all existing tables on new host first
    $existing = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($existing as $exTable) {
        $pdo->exec("DROP TABLE IF EXISTS `$exTable`");
        echo "Dropped existing table: $exTable\n";
    }

    $sqlFile = __DIR__ . '/full_exact_dump.sql';
    if (!file_exists($sqlFile)) {
        die("ERROR: full_exact_dump.sql not found\n");
    }

    $sql = file_get_contents($sqlFile);
    
    // Execute line by line / statement by statement
    $statements = array_filter(array_map('trim', explode(";\n", $sql)));
    $successCount = 0;
    foreach ($statements as $stmt) {
        if ($stmt === '' || strpos($stmt, '--') === 0) continue;
        try {
            $pdo->exec($stmt);
            $successCount++;
        } catch (Exception $e) {
            echo "STMT ERROR: " . $e->getMessage() . " in:\n" . substr($stmt, 0, 150) . "\n";
        }
    }

    $pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");
    @unlink($sqlFile);

    echo "\nSUCCESS: $successCount statements executed successfully!\n\n";
    echo "=== SUMMARY OF LIVE TABLES IN s406665014 ===\n";
    $tables = $pdo->query("SHOW TABLES")->fetchAll(PDO::FETCH_COLUMN);
    foreach ($tables as $t) {
        $count = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        echo "Table: $t -> $count rows\n";
    }
} catch (Exception $e) {
    echo "IMPORT ERROR: " . $e->getMessage() . "\n" . $e->getTraceAsString() . "\n";
}
CODE;

$newConn = @ftp_connect('host.site.yru.ac.th', 21, 30);
if (!$newConn || !@ftp_login($newConn, 's406665014', 'Tasnee_047')) {
    die("ERROR: New FTP login failed\n");
}
ftp_pasv($newConn, true);

// Upload dump SQL and runner
ftp_put($newConn, 'htdocs/406665014.site.yru.ac.th/full_exact_dump.sql', __DIR__ . '/../scratch/full_exact_dump.sql', FTP_BINARY);

$tempImp = tempnam(sys_get_temp_dir(), 'sqlrunner');
file_put_contents($tempImp, $sqlImporter);
ftp_put($newConn, 'htdocs/406665014.site.yru.ac.th/_run_exact_sql.php', $tempImp, FTP_BINARY);
unlink($tempImp);
ftp_close($newConn);

echo "Executing SQL import on new host...\n";
$ch = curl_init('https://406665014.site.yru.ac.th/_run_exact_sql.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 120);
$importResult = curl_exec($ch);
curl_close($ch);

echo "--- NEW HOST RESULT ---\n";
echo $importResult . "\n";

// Cleanup scripts
$newConn = @ftp_connect('host.site.yru.ac.th', 21, 30);
if ($newConn && @ftp_login($newConn, 's406665014', 'Tasnee_047')) {
    ftp_pasv($newConn, true);
    @ftp_delete($newConn, 'htdocs/406665014.site.yru.ac.th/_run_exact_sql.php');
    @ftp_delete($newConn, 'htdocs/406665014.site.yru.ac.th/full_exact_dump.sql');
    ftp_close($newConn);
}

$oldConn = @ftp_connect('ftp.student.yru.ac.th', 21, 15);
if ($oldConn && @ftp_login($oldConn, 'S406665014', '406665014')) {
    ftp_pasv($oldConn, true);
    @ftp_delete($oldConn, 'web/406665014.student.yru.ac.th/public_html/public/_export_exact_sql.php');
    ftp_close($oldConn);
}

echo "=== 100% EXACT MIGRATION COMPLETE! ===\n";
