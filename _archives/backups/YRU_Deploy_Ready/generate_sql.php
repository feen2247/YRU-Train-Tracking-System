<?php
try {
    $sqlite = new PDO('sqlite:' . __DIR__ . '/database/database.sqlite');
    $sqlite->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    
    $routes = $sqlite->query("SELECT * FROM routes")->fetchAll(PDO::FETCH_ASSOC);
    $stops = $sqlite->query("SELECT * FROM route_stops")->fetchAll(PDO::FETCH_ASSOC);
    $trains = $sqlite->query("SELECT skytrain_code, route_code FROM electric_trains")->fetchAll(PDO::FETCH_ASSOC);
    
    $sql = "TRUNCATE TABLE routes;\n";
    foreach ($routes as $r) {
        $rc = addslashes($r['route_code']); $rn = addslashes($r['route_name']);
        $rd = addslashes($r['route_details'] ?? ''); $rcol = addslashes($r['route_color'] ?? '');
        $rp = addslashes($r['polyline_data'] ?? ''); $ca = addslashes($r['created_at'] ?? ''); $ua = addslashes($r['updated_at'] ?? '');
        $sql .= "INSERT INTO routes (route_code, route_name, route_details, route_color, polyline_data, created_at, updated_at) VALUES ('$rc', '$rn', '$rd', '$rcol', '$rp', '$ca', '$ua');\n";
    }
    
    $sql .= "TRUNCATE TABLE route_stops;\n";
    foreach ($stops as $s) {
        $rc = addslashes($s['route_code']); $psc = addslashes($s['parking_spot_code']);
        $so = $s['stop_order']; $ca = addslashes($s['created_at'] ?? ''); $ua = addslashes($s['updated_at'] ?? '');
        $sql .= "INSERT INTO route_stops (route_code, parking_spot_code, stop_order, created_at, updated_at) VALUES ('$rc', '$psc', $so, '$ca', '$ua');\n";
    }
    
    foreach ($trains as $t) {
        if ($t['route_code']) {
            $rc = addslashes($t['route_code']); $sc = addslashes($t['skytrain_code']);
            $sql .= "UPDATE electric_trains SET route_code = '$rc' WHERE skytrain_code = '$sc';\n";
        }
    }
    file_put_contents('sync_routes.sql', $sql);
    echo "SQL generated successfully.";
} catch (Exception $e) {
    echo "Error: " . $e->getMessage();
}
