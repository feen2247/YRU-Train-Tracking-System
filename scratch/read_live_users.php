<?php
$pdo = new PDO('sqlite:' . __DIR__ . '/live_database.sqlite');
$stmt = $pdo->query('PRAGMA table_info(users)');
print_r($stmt->fetchAll(PDO::FETCH_ASSOC));

$stmt2 = $pdo->query('SELECT * FROM users');
$users = $stmt2->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as &$u) {
    unset($u['password']);
}
echo json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
