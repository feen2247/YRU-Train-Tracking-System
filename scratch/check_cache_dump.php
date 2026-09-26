<?php
// Check cache table from old_db_dump.json
$dump = json_decode(file_get_contents(__DIR__ . '/old_db_dump.json'), true);
echo "=== OLD CACHE ROWS (" . count($dump['data']['cache']) . ") ===\n";
foreach ($dump['data']['cache'] as $row) {
    echo "Key: " . $row['key'] . " | Exp: " . $row['expiration'] . " | Val len: " . strlen($row['value']) . "\n";
}
