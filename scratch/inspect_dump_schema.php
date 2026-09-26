<?php
$dump = json_decode(file_get_contents(__DIR__ . '/old_db_dump.json'), true);
foreach ($dump['data'] as $table => $rows) {
    if (!empty($rows)) {
        echo "Table: $table (" . count($rows) . " rows)\n";
        echo "Columns: " . implode(', ', array_keys($rows[0])) . "\n\n";
    }
}
