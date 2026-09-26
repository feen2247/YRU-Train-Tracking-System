<?php
// Simple .env parser that handles = in values
$envFile = file_get_contents(__DIR__ . '/../.env');
$lines = explode("\n", $envFile);
$env = [];
foreach ($lines as $line) {
    $line = trim($line);
    if (!empty($line) && strpos($line, '=') !== false && substr($line, 0, 1) !== '#') {
        list($key, $val) = explode('=', $line, 2);
        $env[trim($key)] = trim($val, ' "');
    }
}

$mysqlHost = $env['DB_HOST'] ?? '127.0.0.1';
$mysqlDb   = $env['DB_DATABASE'] ?? 'S406665014_db';
$mysqlUser = $env['DB_USERNAME'] ?? 'S406665014_db';
$mysqlPass = $env['DB_PASSWORD'] ?? '406665014';

try {
    $mysql = new PDO("mysql:host=$mysqlHost;dbname=$mysqlDb;charset=utf8mb4", $mysqlUser, $mysqlPass);
    $mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $sql = file_get_contents(__DIR__ . '/sync_routes.sql');
    if ($sql) {
        $mysql->exec($sql);
        echo "Database imported successfully!";
    } else {
        echo "SQL file is empty or not found.";
    }
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
