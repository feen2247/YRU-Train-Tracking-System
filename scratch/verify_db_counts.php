<?php
try {
    $pdo = new PDO('mysql:host=host.site.yru.ac.th;dbname=s406665014', 's406665014', 'Tasnee_047', [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
    ]);
    $tables = $pdo->query('SHOW TABLES')->fetchAll(PDO::FETCH_COLUMN);
    echo "=== LIVE DATABASE TABLE COUNTS (host.site.yru.ac.th:s406665014) ===\n";
    foreach ($tables as $t) {
        $c = $pdo->query("SELECT COUNT(*) FROM `$t`")->fetchColumn();
        echo "Table: $t -> $c rows\n";
    }
} catch (Exception $e) {
    echo "DB ERROR: " . $e->getMessage() . "\n";
}
