<?php
$sql = file_get_contents(__DIR__ . '/full_exact_dump.sql');
preg_match_all('/CREATE TABLE [`a-zA-Z0-9_]+ \([\s\S]*?\)(?: ENGINE=[^;]+)?;/i', $sql, $matches);
foreach ($matches[0] as $m) {
    if (strpos($m, 'electric_trains') !== false || strpos($m, 'maintenances') !== false) {
        echo $m . "\n\n";
    }
}
