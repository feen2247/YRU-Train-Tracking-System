<?php
$envPath = __DIR__ . '/../.env';
$env = parse_ini_file($envPath);
$mysqlHost = $env['DB_HOST'] ?? '127.0.0.1';
$mysqlDb   = $env['DB_DATABASE'] ?? 'S406665014_db';
$mysqlUser = $env['DB_USERNAME'] ?? 'S406665014_db';
$mysqlPass = $env['DB_PASSWORD'] ?? '406665014';
try {
    $mysql = new PDO("mysql:host=$mysqlHost;dbname=$mysqlDb;charset=utf8mb4", $mysqlUser, $mysqlPass);
    $mysql->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $sqlitePath = __DIR__ . '/../database/database.sqlite';
    if (!file_exists($sqlitePath)) die("SQLite file not found");
    $sqlite = new PDO("sqlite:$sqlitePath");
    $sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $routes = $sqlite->query("SELECT * FROM routes")->fetchAll(PDO::FETCH_ASSOC);
    if (count($routes) > 0) {
        $mysql->exec("TRUNCATE TABLE routes");
        $stmt = $mysql->prepare("INSERT INTO routes (route_code, route_name, route_details, route_color, polyline_data, created_at, updated_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
        foreach ($routes as $r) $stmt->execute([$r['route_code'], $r['route_name'], $r['route_details'], $r['route_color'], $r['polyline_data'], $r['created_at'], $r['updated_at']]);
        echo "Synced routes: " . count($routes) . "<br>";
    }
    
    $routeStops = $sqlite->query("SELECT * FROM route_stops")->fetchAll(PDO::FETCH_ASSOC);
    if (count($routeStops) > 0) {
        $mysql->exec("TRUNCATE TABLE route_stops");
        $stmt = $mysql->prepare("INSERT INTO route_stops (route_code, parking_spot_code, stop_order, created_at, updated_at) VALUES (?, ?, ?, ?, ?)");
        foreach ($routeStops as $r) $stmt->execute([$r['route_code'], $r['parking_spot_code'], $r['stop_order'], $r['created_at'], $r['updated_at']]);
        echo "Synced route_stops: " . count($routeStops) . "<br>";
    }
} catch (Exception $e) { echo "Error: " . $e->getMessage(); }
