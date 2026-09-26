<?php
/**
 * db_connect.php
 * Database Connection using PDO (PHP Data Objects)
 * 
 * Run the following SQL queries in your MySQL database to set up the tables:
 * 
 * -- 1. Users Table
 * CREATE TABLE IF NOT EXISTS `users` (
 *     `id` INT AUTO_INCREMENT PRIMARY KEY,
 *     `email` VARCHAR(100) NOT NULL UNIQUE,
 *     `password` VARCHAR(255) NOT NULL,
 *     `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
 * 
 * -- 2. Password Resets Table
 * CREATE TABLE IF NOT EXISTS `password_resets` (
 *     `email` VARCHAR(100) NOT NULL,
 *     `token_hash` VARCHAR(64) NOT NULL,
 *     `expires_at` DATETIME NOT NULL,
 *     `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 *     PRIMARY KEY (`email`),
 *     INDEX (`token_hash`)
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
 * 
 * -- 3. OTP Resets Table
 * CREATE TABLE IF NOT EXISTS `otp_resets` (
 *     `email` VARCHAR(100) NOT NULL,
 *     `otp_code` VARCHAR(6) NOT NULL,
 *     `expires_at` DATETIME NOT NULL,
 *     `attempts` INT DEFAULT 0,
 *     `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
 *     PRIMARY KEY (`email`)
 * ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
 */

$host = '127.0.0.1';
$db   = 'yru_train_db'; // Change to your database name
$user = 'root';         // Change to your database user
$pass = '';             // Change to your database password
$charset = 'utf8mb4';

$dsn = "mysql:host=$host;dbname=$db;charset=$charset";
$options = [
    PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    PDO::ATTR_EMULATE_PREPARES   => false,
];

try {
     $pdo = new PDO($dsn, $user, $pass, $options);
} catch (\PDOException $e) {
     throw new \PDOException($e->getMessage(), (int)$e->getCode());
}
