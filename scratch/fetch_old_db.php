<?php
$ch = curl_init('https://406665014.student.yru.ac.th/_export_full_db.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
curl_setopt($ch, CURLOPT_TIMEOUT, 60);
$jsonStr = curl_exec($ch);
$httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
curl_close($ch);

echo "HTTP Code: $httpCode\n";
if (!$jsonStr) {
    die("No response received from old server\n");
}

file_put_contents(__DIR__ . '/old_db_dump.json', $jsonStr);
$data = json_decode($jsonStr, true);

if (isset($data['status']) && $data['status'] === 'success') {
    echo "=== OLD DATABASE DUMP SUMMARY ===\n";
    foreach ($data['data'] as $tableName => $rows) {
        echo "Table: $tableName -> " . count($rows) . " rows\n";
    }
} else {
    echo "Error or unexpected response: " . substr($jsonStr, 0, 500) . "\n";
}
