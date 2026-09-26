<?php
$pdo = new PDO('sqlite:' . __DIR__ . '/live_database.sqlite');
$stmt = $pdo->query('SELECT user_id, employee_id, username, email, name, user_role FROM users');
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
echo json_encode($users, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
