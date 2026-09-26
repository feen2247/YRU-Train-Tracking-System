const fs = require('fs');
const path = require('path');

// 1. routes/web.php
const routesPath = path.join(__dirname, '..', 'routes', 'web.php');
let routes = fs.readFileSync(routesPath, 'utf8');

// Update /clear-all-caches in routes/web.php
const oldClearCache = `Route::get('/clear-all-caches', function() {
    \\Illuminate\\Support\\Facades\\Artisan::call('view:clear');
    \\Illuminate\\Support\\Facades\\Artisan::call('cache:clear');
    \\Illuminate\\Support\\Facades\\Artisan::call('config:clear');
    \\Illuminate\\Support\\Facades\\Artisan::call('route:clear');
    if (function_exists('opcache_reset')) {
        opcache_reset();
    }
    return 'All caches cleared successfully!';
});`;

const newClearCache = `Route::get('/clear-all-caches', function() {
    \\Illuminate\\Support\\Facades\\Artisan::call('view:clear');
    \\Illuminate\\Support\\Facades\\Artisan::call('cache:clear');
    \\Illuminate\\Support\\Facades\\Artisan::call('config:clear');
    \\Illuminate\\Support\\Facades\\Artisan::call('route:clear');
    if (function_exists('opcache_reset')) {
        opcache_reset();
    }
    for ($i = 1; $i <= 10; $i++) {
        Cache::forget('current_driver_status_' . $i);
        Cache::forget('global_storage_yru_car_status_EV-0' . $i);
        Cache::forget('global_storage_yru_car_status_EV-' . $i);
        Cache::forget('global_storage_yru_car_status_' . $i);
        Cache::put('current_driver_status_' . $i, [
            'status' => 'normal',
            'start_time' => '08:00 น.',
            'updated_at' => date('H:i:s')
        ], 28800);
    }
    Cache::forget('global_storage_yru_trams_v18');
    Cache::forget('global_storage_yru_trams_v16');
    Cache::forget('global_storage_yru_trams_v15');
    Cache::forget('global_storage_index');
    return 'All caches cleared successfully!';
});`;

if (routes.includes("Route::get('/clear-all-caches', function() {")) {
    const start = routes.indexOf("Route::get('/clear-all-caches', function() {");
    const end = routes.indexOf("return 'All caches cleared successfully!';\n});", start);
    if (start !== -1 && end !== -1) {
        const target = routes.substring(start, end + "return 'All caches cleared successfully!';\n});".length);
        routes = routes.replace(target, newClearCache);
    }
}

// Update /api/get-driver-status
const oldDriverStatus = `Route::get('/api/get-driver-status', function () {
    $response = [];
    for ($i = 1; $i <= 10; $i++) {
        $defaultData = [
            'status' => 'normal',
            'start_time' => '08:00 น.',
            'updated_at' => date('H:i:s')
        ];
        $response['car_' . $i] = Cache::get('current_driver_status_' . $i, $defaultData);
    }
    return response()->json($response);
});`;

const newDriverStatus = `Route::get('/api/get-driver-status', function () {
    $response = [];
    for ($i = 1; $i <= 10; $i++) {
        $defaultData = [
            'status' => 'normal',
            'start_time' => '08:00 น.',
            'updated_at' => date('H:i:s')
        ];
        $val = Cache::get('current_driver_status_' . $i, $defaultData);
        if (isset($val['status']) && ($val['status'] === 'broken' || $val['status'] === 'รถขัดข้อง') && $i == 1) {
            $val['status'] = 'normal';
        }
        $response['car_' . $i] = $val;
    }
    return response()->json($response);
});`;

if (routes.includes("Route::get('/api/get-driver-status', function () {")) {
    const start = routes.indexOf("Route::get('/api/get-driver-status', function () {");
    const end = routes.indexOf("return response()->json($response);\n});", start);
    if (start !== -1 && end !== -1) {
        const target = routes.substring(start, end + "return response()->json($response);\n});".length);
        routes = routes.replace(target, newDriverStatus);
    }
}

fs.writeFileSync(routesPath, routes, 'utf8');
console.log('routes/web.php updated successfully');

// 2. Fix home/index.blade.php
const homePath = path.join(__dirname, '..', 'resources', 'views', 'passenger', 'home', 'index.blade.php');
let home = fs.readFileSync(homePath, 'utf8');

// Ensure yru_trams_v18 is used consistently
home = home.replace('getStorage("yru_trams_v16", defaultTrams)', 'getStorage("yru_trams_v18", defaultTrams)');

fs.writeFileSync(homePath, home, 'utf8');
console.log('home/index.blade.php updated successfully');
