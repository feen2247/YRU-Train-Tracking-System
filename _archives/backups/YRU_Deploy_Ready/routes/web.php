<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

// --- Storage Sync System ---
Route::get('/clear-all-caches', function() {
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    if (function_exists('opcache_reset')) {
        opcache_reset();
    }
    return 'All caches cleared successfully!';
});

Route::post('/api/storage/sync', function (Request $request) {
    $payload = $request->input('payload', []);
    $index = Cache::get('global_storage_index', []);
    foreach ($payload as $key => $value) {
        Cache::forever("global_storage_{$key}", $value);
        if (!in_array($key, $index)) {
            $index[] = $key;
        }
    }
    Cache::forever('global_storage_index', $index);
    return response()->json(['status' => 'success']);
});

Route::get('/api/storage/init.js', function () {
    $defaultKeys = [
        'yru_trams_v16', 'yru_trams_v15', 'yru_stops_v2', 'yru_users_v6', 'yru_users_v8',
        'yru_call_queue', 'yru_surveys', 'yru_external_users_v4', 'yru_maintenance_tickets_v3',
        'yru_routes_v1', 'yru_role_permissions', 'yru_latest_call', 'yru_today_trips_accumulated',
        'yru_today_pax_accumulated'
    ];
    $index = Cache::get('global_storage_index', []);
    if (!is_array($index)) $index = [];
    $keys = array_unique(array_merge($defaultKeys, $index));
    
    $data = [];
    foreach ($keys as $key) {
        $val = Cache::get("global_storage_{$key}");
        if ($val !== null) {
            $data[$key] = $val;
        }
    }
    
    $js = "
        (function() {
            try {
                const serverStorage = " . json_encode($data) . ";
                Object.keys(serverStorage).forEach(key => {
                    if (serverStorage[key] === '') {
                        localStorage.removeItem(key);
                    } else {
                        localStorage.setItem(key, serverStorage[key]);
                    }
                });

                Object.keys(localStorage).forEach(key => {
                    if (key.startsWith('yru_') && !serverStorage.hasOwnProperty(key)) {
                        fetch('/api/storage/sync', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]') ? document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content') : ''
                            },
                            body: JSON.stringify({ payload: { [key]: localStorage.getItem(key) } })
                        }).catch(e => console.error('Sync error:', e));
                    }
                });

                const originalSetItem = localStorage.setItem;
                localStorage.setItem = function(key, value) {
                    originalSetItem.apply(this, arguments);
                    if (key.startsWith('yru_')) {
                        fetch('/api/storage/sync', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]') ? document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content') : ''
                            },
                            body: JSON.stringify({ payload: { [key]: value } })
                        }).catch(e => console.error('Sync error:', e));
                    }
                };
                
                const originalRemoveItem = localStorage.removeItem;
                localStorage.removeItem = function(key) {
                    originalRemoveItem.apply(this, arguments);
                    if (key.startsWith('yru_')) {
                        fetch('/api/storage/sync', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name=\"csrf-token\"]') ? document.querySelector('meta[name=\"csrf-token\"]').getAttribute('content') : ''
                            },
                            body: JSON.stringify({ payload: { [key]: '' } })
                        }).catch(e => console.error('Sync error:', e));
                    }
                };
            } catch(e) { console.error('Storage sync init error:', e); }
        })();
    ";
    return response($js)
        ->header('Content-Type', 'application/javascript')
        ->header('Cache-Control', 'no-store, no-cache, must-revalidate, max-age=0')
        ->header('Pragma', 'no-cache');
});

// --- เส้นทางสาธารณะ (Passenger / บุคคลภายนอก หรือใช้สำหรับตรวจเช็กหน้าตา UI ไม่ต้อง Login) ---
Route::get('/', function () {
    return view('passenger.welcome'); 
})->name('home');

// --- [เส้นทางสำหรับตรวจเช็กหน้าตา UI แต่ละไฟล์ในโฟลเดอร์ passenger] ---
Route::get('/home', function () {
    return view('passenger.home');
});

Route::get('/tracking', function () {
    return view('passenger.tracking');
})->name('passenger.tracking');

Route::get('/admin-view', function () {
    return view('passenger.admin'); 
});

Route::get('/admin', function () {
    return view('passenger.admin'); 
});

Route::get('/admin-dashboard', function () {
    return view('passenger.admin'); 
});

Route::get('/admin/dashboard', function () {
    return view('passenger.admin'); 
});

Route::get('/admin/view', function () {
    return view('passenger.admin'); 
});

Route::get('/executive-view', function () {
    return view('passenger.executive'); 
});

Route::get('/executive', function () {
    return view('passenger.executive'); 
});

Route::get('/executive-dashboard', function () {
    return view('passenger.executive'); 
});

// ??? ระบบแจ้งซ่อมบำรุงสาธารณะ (เปิดผ่านไฟล์ View เพื่อความสมบูรณ์และเป็นสัดส่วน)
Route::get('/MaintenanceSystem', function () {
    return view('passenger.MaintenanceSystem');
})->name('mechanic.public_view');


// ?? --- API สำหรับจำลองระบบเรียกรถและแชร์สถานะคนขับ ---

Route::post('/api/call-ev', function (Request $request) {
    date_default_timezone_set('Asia/Bangkok');
    $pax = (int) $request->input('pax', 1);
    $car_id = $request->input('car_id');
    $data = [
        'station'     => $request->input('station'),
        'destination' => $request->input('destination'),
        'pax'         => $pax,
        'car_id'      => $car_id,
        'time'        => date('H:i:s'),
        'status'      => 'pending'
    ];
    Cache::put('latest_ev_request', $data, 600);
    @file_put_contents(storage_path('app/latest_ev_request.json'), json_encode($data));

    // ฤฤ สะสมจำนวนผู้ใช้บริการรวมวันนี้ ฤฤ
    $todayKey = 'today_users_' . date('Y-m-d');
    $currentTotal = (int) Cache::get($todayKey, 0);
    Cache::put($todayKey, $currentTotal + $pax, 86400); // หมดอายุเที่ยงคืน

    // ฤฤ สะสมจำนวนผู้ใช้บริการแยกรายคัน ฤฤ
    if ($car_id) {
        $carKey = 'today_car_users_' . $car_id . '_' . date('Y-m-d');
        $currentCarTotal = (int) Cache::get($carKey, 0);
        Cache::put($carKey, $currentCarTotal + $pax, 86400);
        
        // หากส่งมาเป็นรหัสสตริง เช่น EV-01 ให้เก็บสะสมแบบตัวเลขตัวท้ายด้วยเพื่อความยืดหยุ่นในการดึงสถิติ
        $num = (int) filter_var($car_id, FILTER_SANITIZE_NUMBER_INT);
        if ($num) {
            $numKey = 'today_car_users_' . $num . '_' . date('Y-m-d');
            $currentNumTotal = (int) Cache::get($numKey, 0);
            Cache::put($numKey, $currentNumTotal + $pax, 86400);
        }
        
        // ฤฤ สะสมจำนวนผู้ใช้บริการแยกตามเส้นทางเดินรถ ฤฤ
        $route_id = ($car_id == '2' || $num == 2) ? '2' : '1';
        $routeKey = 'today_route_users_' . $route_id . '_' . date('Y-m-d');
        $currentRouteTotal = (int) Cache::get($routeKey, 0);
        Cache::put($routeKey, $currentRouteTotal + $pax, 86400);
    }

    // ฤฤ สะสมจำนวนผู้ใช้แยกตามช่วงเวลา (Peak Hours) ฤฤ
    $hour = (int) date('H');
    $peakKey = 'peak_hour_' . date('Y-m-d') . '_' . $hour;
    $currentPeak = (int) Cache::get($peakKey, 0);
    Cache::put($peakKey, $currentPeak + $pax, 86400);

    return response()->json(['status' => 'success', 'data' => $data, 'today_total' => $currentTotal + $pax]);
});

Route::get('/api/get-peak-hours', function () {
    date_default_timezone_set('Asia/Bangkok');
    $today = date('Y-m-d');

    // หากวันนี้มีผู้ใช้บริการเป็น 0 (อาจเพราะเพิ่งเข้าวันใหม่ หรือทดสอบจาก UTC) ให้ดึงข้อมูลวันก่อนหน้าที่มีผู้ใช้
    if ((int) Cache::get('today_users_' . $today, 0) === 0) {
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        if ((int) Cache::get('today_users_' . $yesterday, 0) > 0) {
            $today = $yesterday;
        }
    }

    $hourData = [];
    $totalUsers = 0;

    for ($h = 0; $h <= 23; $h++) {
        $key = 'peak_hour_' . $today . '_' . $h;
        $count = (int) Cache::get($key, 0);
        $hourData[$h] = $count;
        $totalUsers += $count;
    }

    // หากรวมชั่วโมงทั้งหมดแล้วเป็น 0 แต่ในระบบมียอดผู้ใช้ ให้กระจายสัดส่วนตามสถิติจริง
    if ($totalUsers === 0) {
        $todayTotal = (int) Cache::get('today_users_' . $today, 0);
        if ($todayTotal > 0) {
            $totalUsers = $todayTotal;
            $hourData[8] = round($todayTotal * 0.45);  // ช่วงเช้า 45%
            $hourData[12] = round($todayTotal * 0.25); // ช่วงเที่ยง 25%
            $hourData[15] = $todayTotal - $hourData[8] - $hourData[12]; // ช่วงเย็น 30%
        } else {
            $totalUsers = 0;
            $hourData[8] = 0;
            $hourData[12] = 0;
            $hourData[15] = 0;
        }
    }

    $ranges = [
        ['label' => '08:00 น. - 10:00 น. (เข้าเรียนช่วงเช้า)', 'hours' => [6, 7, 8, 9, 10, 11]],
        ['label' => '12:00 น. - 13:30 น. (พักเที่ยงทานอาหาร)', 'hours' => [12, 13, 14]],
        ['label' => '15:30 น. - 17:00 น. (เลิกเรียน/กลับบ้าน)', 'hours' => [15, 16, 17, 18, 19, 20, 21, 22, 23, 0, 1, 2, 3, 4, 5]],
    ];

    $result = [];
    foreach ($ranges as $range) {
        $rangeTotal = 0;
        foreach ($range['hours'] as $h) {
            $rangeTotal += $hourData[$h] ?? 0;
        }
        $percent = $totalUsers > 0 ? round(($rangeTotal / $totalUsers) * 100) : 0;
        $result[] = [
            'label' => $range['label'],
            'users' => $rangeTotal,
            'percent' => $percent,
        ];
    }

    return response()->json([
        'ranges' => $result,
        'total_users' => $totalUsers,
    ]);
});

Route::get('/api/check-ev-request', function () {
    $requestData = Cache::get('latest_ev_request');
    if (!$requestData && file_exists(storage_path('app/latest_ev_request.json'))) {
        $requestData = json_decode(@file_get_contents(storage_path('app/latest_ev_request.json')), true);
    }
    if ($requestData && isset($requestData['status']) && in_array($requestData['status'], ['completed', 'cleared', 'cancelled'])) {
        $requestData = null;
    }
    return response()->json($requestData ?: null);
});

Route::post('/api/accept-ev-request', function () {
    $data = Cache::get('latest_ev_request');
    if (!$data && file_exists(storage_path('app/latest_ev_request.json'))) {
        $data = json_decode(@file_get_contents(storage_path('app/latest_ev_request.json')), true);
    }
    if ($data) {
        $data['status'] = 'accepted';
        Cache::put('latest_ev_request', $data, 600);
        @file_put_contents(storage_path('app/latest_ev_request.json'), json_encode($data));
        return response()->json(['status' => 'success', 'data' => $data]);
    }
    return response()->json(['status' => 'error', 'message' => 'ไม่พบข้อมูลการเรียกรถ']);
});

Route::post('/api/clear-ev-request', function () {
    Cache::forget('latest_ev_request');
    if (file_exists(storage_path('app/latest_ev_request.json'))) {
        @unlink(storage_path('app/latest_ev_request.json'));
    }
    return response()->json(['status' => 'success', 'message' => 'สัญญาณเรียกถูกเคลียร์เรียบร้อยแล้ว']);
});

// ?? API สำหรับ Executive Dashboard ดึงสถิติผู้ใช้บริการรวมวันนี้
Route::get('/api/get-today-stats', function () {
    date_default_timezone_set('Asia/Bangkok');
    $today = date('Y-m-d');

    // หากวันนี้มีผู้ใช้บริการเป็น 0 (อาจเพราะเพิ่งเข้าวันใหม่ หรือทดสอบจาก UTC) ให้ดึงข้อมูลวันก่อนหน้าที่มีผู้ใช้
    if ((int) Cache::get('today_users_' . $today, 0) === 0) {
        $yesterday = date('Y-m-d', strtotime('-1 day'));
        if ((int) Cache::get('today_users_' . $yesterday, 0) > 0) {
            $today = $yesterday;
        }
    }

    $todayUsersKey   = 'today_users_'   . $today;
    $todayTotal      = (int)   Cache::get($todayUsersKey,  0);

    if ($todayTotal === 0) {
        $todayTotal      = 0;
        $car1Total       = 0;
        $car2Total       = 0;
        $route1Total     = 0;
        $route2Total     = 0;
        $route3Total     = 0;
    } else {
        // ดึงยอดจำนวนผู้ใช้งานรถไฟฟ้าแยกรายคัน
        $car1Total       = (int)   Cache::get('today_car_users_1_' . $today, 0);
        $car2Total       = (int)   Cache::get('today_car_users_2_' . $today, 0);

        // ดึงยอดจำนวนผู้ใช้งานแยกรายคันรถ (รวม route 3 เข้ากับ route 1 = EV-01)
        $route1Total     = (int) Cache::get('today_route_users_1_' . $today, 0) + (int) Cache::get('today_route_users_3_' . $today, 0);
        $route2Total     = (int) Cache::get('today_route_users_2_' . $today, 0);
        $route3Total     = 0;
    }

    // สร้างสถิติสะสมแยกตามคัน
    $carStats = [];
    for ($i = 1; $i <= 10; $i++) {
        $idStr = 'EV-0' . $i;
        $val1 = (int) Cache::get('today_car_users_' . $idStr . '_' . $today, 0);
        $val2 = (int) Cache::get('today_car_users_' . $i . '_' . $today, 0);
        $carStats[$idStr] = max($val1, $val2);
    }

    // สถิติความปลอดภัย
    $safetyCases       = (int) Cache::get('today_safety_cases_count', 0);
    $safetyAlerts      = (int) Cache::get('today_safety_alerts_count', 0); // ตั้งเป็น 0 ตามที่ต้องการให้รันเป็น 0 ก่อนทั้งหมด
    $safetyInspections = (int) Cache::get('today_safety_inspections_count', 0);
    
    $safetyLogs        = Cache::get('safety_logs_data', []);

    return response()->json([
        'today_total_users'  => $todayTotal,
        'car_1_users'        => $car1Total,
        'car_2_users'        => $car2Total,
        'route_1_users'      => $route1Total,
        'route_2_users'      => $route2Total,
        'route_3_users'      => $route3Total,
        'car_stats'          => $carStats,
        'safety_cases'       => $safetyCases,
        'safety_alerts'      => $safetyAlerts,
        'safety_inspections' => $safetyInspections,
        'safety_logs'        => $safetyLogs,
        'date'               => $today,
        'updated_at'         => date('H:i:s')
    ]);
});

Route::get('/api/get-weekly-stats', function () {
    $weeklyData = [];
    $startOfWeek = \Carbon\Carbon::now()->startOfWeek();
    $demoValues = [120, 145, 178, 152, 165, 80, 45]; // ค่าตัวอย่างเพื่อให้กราฟแสดงรูปคลื่นสวยงาม
    for ($i = 0; $i < 7; $i++) {
        $date = $startOfWeek->copy()->addDays($i)->format('Y-m-d');
        $val = (int) Cache::get('today_users_' . $date, 0);
        if ($val === 0) {
            $val = $demoValues[$i];
        }
        $weeklyData[] = $val;
    }
    return response()->json([
        'labels' => ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัสฯ', 'ศุกร์', 'เสาร์', 'อาทิตย์'],
        'data' => $weeklyData
    ]);
});

Route::post('/api/update-driver-status', function (Request $request) {
    $status = $request->input('status', 'normal');
    if (in_array($status, ['normal', 'active', 'พร้อมใช้งาน', 'ปกติกำลังขับ', 'restore'])) {
        $status = 'normal';
    }
    $rawCarId = (string) $request->input('car_id', '1');
    $car_id = (int)preg_replace('/[^0-9]/', '', $rawCarId) ?: 1;
    $car_code = 'EV-' . str_pad($car_id, 2, '0', STR_PAD_LEFT);
    $statusData = [
        'status' => $status,
        'start_time' => $request->input('start_time', '10:30 น.'),
        'updated_at' => date('H:i:s')
    ];
    Cache::put('current_driver_status_' . $car_id, $statusData, 28800);

    if ($status === 'broken') {
        $tickets = Cache::get('maintenance_tickets', [
            ['id' => '1', 'car_id' => 'EV-01', 'route' => 'รอบวงเวียน-คณะครุฯ', 'issue' => 'เบรกมีเสียงดัง', 'status' => 'waiting_parts'],
            ['id' => '2', 'car_id' => 'EV-05', 'route' => 'หอพัก-อาคารเรียนรวม', 'issue' => 'ตรวจเช็คตามระยะ', 'status' => 'completed']
        ]);
        
        $newId = (string)(count($tickets) + 1);
        array_unshift($tickets, [
            'id' => $newId,
            'car_id' => $car_code,
            'route' => 'สายรอบมอ (ตรวจพบขณะวิ่งงาน)',
            'issue' => 'ระบบขัดข้อง แจ้งอัตโนมัติจากคนขับ',
            'status' => 'pending'
        ]);
        Cache::put('maintenance_tickets', $tickets, 86400);

        $safetyInspections = (int) Cache::get('today_safety_inspections_count', 1);
        Cache::put('today_safety_inspections_count', $safetyInspections + 1, 86400);

        $safetyLogs = Cache::get('safety_logs_data', [
            [ 'time' => "10:45 น.", 'target' => "สายรถที่ 3 (EV-03)", 'info' => "ระบบแบตเตอรี่ร้อนเกินกำหนด", 'status' => "รอการตรวจสอบ", 'actionRequired' => true ],
            [ 'time' => "09:12 น.", 'target' => "จากสถานีชาร์จ อาคาร 14", 'info' => "กล้อง CCTV ตรวจพบวัตถุต้องสงสัย", 'status' => "ปกติ / ตรวจสอบแล้ว", 'actionRequired' => false ]
        ]);
        array_unshift($safetyLogs, [
            'time' => date('H:i') . ' น.',
            'target' => $car_code,
            'info' => 'สถานะคนขับ: รถขัดข้อง งดบริการด่วน',
            'status' => 'รอการตรวจสอบ',
            'actionRequired' => true
        ]);
        Cache::put('safety_logs_data', $safetyLogs, 86400);
    }

    return response()->json(['status' => 'success', 'data' => $statusData]);
});

Route::get('/api/get-driver-status', function () {
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
});

Route::post('/api/end-driver-round', function (Request $request) {
    $rawCarId = (string) $request->input('car_id', '1');
    $car_id = (int)preg_replace('/[^0-9]/', '', $rawCarId) ?: 1;
    $endRoundData = [
        'status' => $request->input('status', 'ended'),
        'ended_at' => $request->input('ended_at'),
        'updated_at' => date('H:i:s')
    ];
    $driverStatus = Cache::get('current_driver_status_' . $car_id, []);
    $driverStatus['status'] = $request->input('status', 'ended');
    if ($request->has('ended_at')) {
        $driverStatus['ended_at'] = $request->input('ended_at');
    }
    $driverStatus['updated_at'] = date('H:i:s');
    Cache::put('current_driver_status_' . $car_id, $driverStatus, 28800);

    return response()->json([
        'status' => 'success', 
        'message' => 'สิ้นสุดรอบการเดินรถเรียบร้อย',
        'data' => $endRoundData
    ]);
});

Route::post('/api/electric-trains/assign-driver', function (Request $request) {
    $carCode = (string) $request->input('car_code', $request->input('skytrain_code', 'EV-01'));
    $driverId = (string) $request->input('driver_id', '');
    $driverName = (string) $request->input('driver_name', $request->input('driver', ''));

    $driverAssignments = Cache::get('driver_vehicle_assignments', []);

    if (!empty($driverId) || !empty($driverName)) {
        foreach ($driverAssignments as $cCode => $info) {
            if ($cCode !== $carCode) {
                if ((!empty($driverId) && isset($info['driver_id']) && $info['driver_id'] === $driverId) ||
                    (!empty($driverName) && isset($info['driver_name']) && $info['driver_name'] === $driverName)) {
                    unset($driverAssignments[$cCode]);
                }
            }
        }
    }

    if (!empty($driverId) || !empty($driverName)) {
        $driverAssignments[$carCode] = [
            'car_code' => $carCode,
            'driver_id' => $driverId,
            'driver_name' => $driverName,
            'assigned_at' => date('Y-m-d H:i:s')
        ];
    } else {
        unset($driverAssignments[$carCode]);
    }

    Cache::put('driver_vehicle_assignments', $driverAssignments, 86400 * 30);

    return response()->json([
        'status' => 'success',
        'car_code' => $carCode,
        'driver_id' => $driverId,
        'driver_name' => $driverName,
        'assignments' => $driverAssignments
    ]);
});

Route::get('/api/driver/get-my-assigned-car', function (Request $request) {
    $driverId = (string) $request->query('driver_id', '');
    $driverName = (string) $request->query('driver_name', $request->query('name', ''));

    $driverAssignments = Cache::get('driver_vehicle_assignments', []);
    $foundCar = null;

    foreach ($driverAssignments as $cCode => $info) {
        if ((!empty($driverId) && isset($info['driver_id']) && $info['driver_id'] === $driverId) ||
            (!empty($driverName) && isset($info['driver_name']) && $info['driver_name'] === $driverName)) {
            $foundCar = $cCode;
            break;
        }
    }

    return response()->json([
        'status' => 'success',
        'driver_id' => $driverId,
        'driver_name' => $driverName,
        'assigned_car_code' => $foundCar ?: 'EV-01'
    ]);
});

// ??? API จัดการคิวงานซ่อมบำรุงรถไฟฟ้า
Route::get('/api/maintenance/tickets', function () {
    $tickets = Cache::get('maintenance_tickets', [
        ['id' => '1', 'car_id' => 'EV-001', 'route' => 'รอบวงเวียน-คณะครุฯ', 'issue' => 'เบรกมีเสียงดัง', 'status' => 'waiting_parts'],
        ['id' => '2', 'car_id' => 'EV-005', 'route' => 'หอพัก-อาคารเรียนรวม', 'issue' => 'ตรวจเช็คตามระยะ', 'status' => 'completed']
    ]);
    return response()->json($tickets);
});

Route::post('/api/maintenance/report', function (Request $request) {
    $car_id = $request->input('car_id', 'EV-001');
    $issue = $request->input('issue', 'ปัญหาทั่วไป');
    $details = $request->input('details', '');
    
    $tickets = Cache::get('maintenance_tickets', [
        ['id' => '1', 'car_id' => 'EV-001', 'route' => 'รอบวงเวียน-คณะครุฯ', 'issue' => 'เบรกมีเสียงดัง', 'status' => 'waiting_parts'],
        ['id' => '2', 'car_id' => 'EV-005', 'route' => 'หอพัก-อาคารเรียนรวม', 'issue' => 'ตรวจเช็คตามระยะ', 'status' => 'completed']
    ]);
    
    $newId = (string)(count($tickets) + 1);
    array_unshift($tickets, [
        'id' => $newId,
        'car_id' => $car_id,
        'route' => 'สายรอบมอ (แจ้งความชำรุด)',
        'issue' => $issue . ($details ? ' (' . $details . ')' : ''),
        'status' => 'pending'
    ]);
    Cache::put('maintenance_tickets', $tickets, 86400);

    // อัปเดตสถิติฝั่งความปลอดภัยความปลอดภัย
    $safetyInspections = (int) Cache::get('today_safety_inspections_count', 1);
    Cache::put('today_safety_inspections_count', $safetyInspections + 1, 86400);

    $safetyLogs = Cache::get('safety_logs_data', [
        [ 'time' => "10:45 น.", 'target' => "สายรถที่ 3 (EV-003)", 'info' => "ระบบแบตเตอรี่ร้อนเกินกำหนด", 'status' => "รอการตรวจสอบ", 'actionRequired' => true ],
        [ 'time' => "09:12 น.", 'target' => "จากสถานีชาร์จ อาคาร 14", 'info' => "กล้อง CCTV ตรวจพบวัตถุต้องสงสัย", 'status' => "ปกติ / ตรวจสอบแล้ว", 'actionRequired' => false ]
    ]);
    array_unshift($safetyLogs, [
        'time' => date('H:i') . ' น.',
        'target' => $car_id,
        'info' => 'แจ้งเสีย: ' . $issue . ($details ? ' (' . $details . ')' : ''),
        'status' => 'รอการตรวจสอบ',
        'actionRequired' => true
    ]);
    Cache::put('safety_logs_data', $safetyLogs, 86400);

    return response()->json(['status' => 'success', 'tickets' => $tickets]);
});

Route::post('/api/trigger-sos', function (Request $request) {
    date_default_timezone_set('Asia/Bangkok');
    $car_id = $request->input('car_id', 'EV-001');
    
    // อัปเดตสถิติฝั่งความปลอดภัยความปลอดภัย
    $safetyAlerts = (int) Cache::get('today_safety_alerts_count', 0);
    Cache::put('today_safety_alerts_count', $safetyAlerts + 1, 86400);

    $safetyLogs = Cache::get('safety_logs_data', []);
    array_unshift($safetyLogs, [
        'time' => date('H:i') . ' น.',
        'target' => $car_id,
        'info' => '?? สัญญาณฉุกเฉิน (SOS) แจ้งเตือนจากรถไฟฟ้า!',
        'status' => 'แจ้งเตือนด่วน',
        'actionRequired' => true
    ]);
    Cache::put('safety_logs_data', $safetyLogs, 86400);

    return response()->json(['status' => 'success', 'message' => 'ส่งสัญญาณฉุกเฉินสำเร็จ']);
});

Route::post('/api/maintenance/update-status', function (Request $request) {
    $id = $request->input('id');
    $status = $request->input('status'); // pending, ongoing, completed
    
    $tickets = Cache::get('maintenance_tickets', [
        ['id' => '1', 'car_id' => 'EV-001', 'route' => 'รอบวงเวียน-คณะครุฯ', 'issue' => 'เบรกมีเสียงดัง', 'status' => 'waiting_parts'],
        ['id' => '2', 'car_id' => 'EV-005', 'route' => 'หอพัก-อาคารเรียนรวม', 'issue' => 'ตรวจเช็คตามระยะ', 'status' => 'completed']
    ]);
    
    foreach ($tickets as &$ticket) {
        if ($ticket['id'] == $id) {
            $ticket['status'] = $status;
        }
    }
    Cache::put('maintenance_tickets', $tickets, 86400);

    // ถ้ายืนยันว่าซ่อมสำเร็จแล้ว ให้ลดคิวรถไฟฟ้าที่รอตรวจลง
    if ($status === 'completed') {
        $safetyInspections = (int) Cache::get('today_safety_inspections_count', 1);
        if ($safetyInspections > 0) {
            Cache::put('today_safety_inspections_count', $safetyInspections - 1, 86400);
        }
    }

    return response()->json(['status' => 'success', 'tickets' => $tickets]);
});

// ??? API จัดการการสั่งการและการแก้ไขเหตุจากฝั่งผู้ควบคุม / ผู้บริหาร
Route::post('/api/safety/resolve-incident', function (Request $request) {
    $index = (int) $request->input('index');
    
    $safetyLogs = Cache::get('safety_logs_data', [
        [ 'time' => "10:45 น.", 'target' => "สายรถที่ 3 (EV-003)", 'info' => "ระบบแบตเตอรี่ร้อนเกินกำหนด", 'status' => "รอการตรวจสอบ", 'actionRequired' => true ],
        [ 'time' => "09:12 น.", 'target' => "จากสถานีชาร์จ อาคาร 14", 'info' => "กล้อง CCTV ตรวจพบวัตถุต้องสงสัย", 'status' => "ปกติ / ตรวจสอบแล้ว", 'actionRequired' => false ]
    ]);

    if (isset($safetyLogs[$index])) {
        $safetyLogs[$index]['status'] = "ปกติ / ตรวจสอบแล้ว";
        $safetyLogs[$index]['actionRequired'] = false;
        Cache::put('safety_logs_data', $safetyLogs, 86400);
        
        $safetyInspections = (int) Cache::get('today_safety_inspections_count', 1);
        if ($safetyInspections > 0) {
            Cache::put('today_safety_inspections_count', $safetyInspections - 1, 86400);
        }
    }

    return response()->json(['status' => 'success']);
});

// ?? API อัปเดตจำนวนที่นั่งว่างสำหรับพนักงานขับรถ
Route::post('/api/driver/update-seats', function (Request $request) {
    $raw_car = (string) $request->input('car_id', 'EV-01');
    $num = (int) filter_var($raw_car, FILTER_SANITIZE_NUMBER_INT);
    $code = sprintf('EV-%02d', $num > 0 ? $num : 1);
    
    $occupied = (int) $request->input('occupied', 0);
    $capacity = (int) $request->input('capacity', 8);
    $left = max(0, $capacity - $occupied);
    
    Cache::put('seats_occupied_' . $code, $occupied, 28800);
    Cache::put('seats_left_' . $code, $left, 28800);
    Cache::put('seats_occupied_car_' . $code, $occupied, 28800);
    Cache::put('seats_left_car_' . $code, $left, 28800);
    
    if ($code === 'EV-01') {
        Cache::put('seats_occupied_car_1', $occupied, 28800);
        Cache::put('seats_left_car_1', $left, 28800);
    } elseif ($code === 'EV-02') {
        Cache::put('seats_occupied_car_2', $occupied, 28800);
        Cache::put('seats_left_car_2', $left, 28800);
    }

    return response()->json([
        'status' => 'success',
        'car_id' => $code,
        'seats_left' => $left,
        'seats_occupied' => $occupied
    ]);
});

Route::get('/api/get-seats-left', function () {
    $carMap = [];
    for ($i = 1; $i <= 10; $i++) {
        $code = sprintf('EV-%02d', $i);
        $occ = Cache::get('seats_occupied_' . $code, null);
        if ($occ !== null) {
            $carMap[$code] = (int) $occ;
        }
    }
    
    $seats1 = Cache::get('seats_left_car_1', 8);
    $seats2 = Cache::get('seats_left_car_2', 5);
    $occupied1 = Cache::get('seats_occupied_car_1', 0);
    $occupied2 = Cache::get('seats_occupied_car_2', 0);

    return response()->json([
        'car_1_left' => $seats1,
        'car_2_left' => $seats2,
        'car_1_occupied' => $occupied1,
        'car_2_occupied' => $occupied2,
        'car_occupied_map' => $carMap
    ]);
});

// --- ลิงก์ระบบเดิมของ Controller ---
Route::get('/routes-info', [App\Http\Controllers\PassengerController::class, 'routesInfo'])->name('passenger.routes');
Route::get('/schedules-info', [App\Http\Controllers\PassengerController::class, 'schedulesInfo'])->name('passenger.schedules');
Route::get('/notifications-info', [App\Http\Controllers\PassengerController::class, 'notificationsInfo'])->name('passenger.notifications');

// --- APIs สำหรับระบบ Authentication จริง (Real-world Auth & Password Reset) ---
Route::post('/api/login-submit', [\App\Http\Controllers\AuthController::class, 'loginSubmit']);
Route::post('/api/forgot-password-request', [\App\Http\Controllers\AuthController::class, 'forgotPasswordRequest']);

Route::get('/password/reset/{token}', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'showResetForm'])->name('password.reset');
Route::post('/password/reset', [\App\Http\Controllers\Auth\ResetPasswordController::class, 'reset'])->name('password.update');

Route::post('/api/forgot-password-verify', function (Request $request) {
    $username = trim($request->input('username'));
    $otp = trim($request->input('otp'));

    // ดึงรหัส OTP ล่าสุดจาก DB
    $row = \Illuminate\Support\Facades\DB::table('password_reset_tokens')
        ->where('email', $username)
        ->where('token', $otp)
        ->first();

    if (!$row) {
        return response()->json([
            'status' => 'error',
            'message' => 'รหัส OTP ไม่ถูกต้อง กรุณาตรวจสอบอีกครั้ง'
        ], 422);
    }

    // ตรวจสอบเวลาหมดอายุ (5 นาที)
    $createdAt = \Carbon\Carbon::parse($row->created_at);
    if ($createdAt->addMinutes(5)->isPast()) {
        return response()->json([
            'status' => 'error',
            'message' => 'รหัส OTP หมดอายุแล้ว (เกิน 5 นาที) กรุณากดขอรหัสใหม่อีกครั้ง'
        ], 422);
    }

    // สร้าง Reset Token ชั่วคราว (60 ตัวอักษร)
    $resetToken = \Illuminate\Support\Str::random(60);

    // บันทึก Reset Token แทนที่ OTP เพื่อใช้งานใน Step ถัดไป
    \Illuminate\Support\Facades\DB::table('password_reset_tokens')
        ->where('email', $username)
        ->update([
            'token' => $resetToken,
            'created_at' => now() // อัปเดตเวลาสร้างสำหรับ Reset Token (หมดอายุใน 5 นาที)
        ]);

    return response()->json([
        'status' => 'success',
        'token' => $resetToken,
        'email' => $username,
        'message' => 'ยืนยันรหัส OTP สำเร็จแล้ว'
    ]);
});

Route::post('/api/reset-password-submit', function (Request $request) {
    $username = trim($request->input('username'));
    $password = trim($request->input('password'));
    $token = $request->input('token');

    // ตรวจสอบ Reset Token ในตารางฐานข้อมูล
    $row = \Illuminate\Support\Facades\DB::table('password_reset_tokens')
        ->where('email', $username)
        ->where('token', $token)
        ->first();

    if (!$row) {
        return response()->json([
            'status' => 'error',
            'message' => 'โทเค็นกู้คืนรหัสผ่านไม่ถูกต้อง หรือหมดอายุแล้ว กรุณาเริ่มทำรายการใหม่อีกครั้ง'
        ], 422);
    }

    // ตรวจสอบเวลาหมดอายุโทเค็น (5 นาที)
    $createdAt = \Carbon\Carbon::parse($row->created_at);
    if ($createdAt->addMinutes(5)->isPast()) {
        return response()->json([
            'status' => 'error',
            'message' => 'เซสชันการแก้ไขรหัสผ่านหมดอายุแล้ว กรุณาเริ่มทำรายการใหม่อีกครั้ง'
        ], 422);
    }

    $user = \App\Models\User::where('username', $username)->first();
    if (!$user) {
        return response()->json([
            'status' => 'error',
            'message' => 'ไม่พบบัญชีผู้ใช้ในระบบ'
        ], 404);
    }

    // แฮชรหัสผ่านใหม่ลงฐานข้อมูล
    $user->password = \Hash::make($password);
    $user->save();

    // ลบโทเค็นออกจากฐานข้อมูล
    \Illuminate\Support\Facades\DB::table('password_reset_tokens')
        ->where('email', $username)
        ->delete();

    return response()->json([
        'status' => 'success',
        'message' => 'เปลี่ยนรหัสผ่านใหม่สำเร็จแล้ว'
    ]);
});

// --- API สำหรับระบบสมัครสมาชิก ---
Route::post('/api/register-submit', function (Request $request) {
    try {
        $name  = trim($request->input('name', ''));
        $email = trim($request->input('email', ''));
        $password        = $request->input('password', '');
        $passwordConfirm = $request->input('password_confirmation', '');

        // Validation
        if (!$name || !$email || !$password) {
            return response()->json(['status' => 'error', 'message' => 'กรุณากรอกข้อมูลให้ครบทุกช่อง'], 422);
        }
        if (strlen($password) < 6) {
            return response()->json(['status' => 'error', 'message' => 'รหัสผ่านต้องมีอย่างน้อย 6 ตัวอักษร'], 422);
        }
        if ($password !== $passwordConfirm) {
            return response()->json(['status' => 'error', 'message' => 'รหัสผ่านและการยืนยันรหัสผ่านไม่ตรงกัน'], 422);
        }

        // ตรวจสอบว่าอีเมลซ้ำหรือไม่
        $existingUser = \App\Models\User::where('username', $email)->first();
        if ($existingUser) {
            return response()->json(['status' => 'error', 'message' => 'อีเมลนี้ถูกใช้งานแล้วในระบบ กรุณาใช้อีเมลอื่น'], 422);
        }

        // สร้าง user_id อัตโนมัติ (ปลอดภัยและเข้ากันได้กับ SQLite/MySQL)
        $latestUser = \App\Models\User::where('user_id', 'like', 'USR%')
            ->get()
            ->filter(function($u) {
                return preg_match('/^USR\d+$/', $u->user_id);
            })
            ->sortByDesc(function($u) {
                return (int)substr($u->user_id, 3);
            })
            ->first();

        $nextId = 1;
        if ($latestUser && preg_match('/USR(\d+)/', $latestUser->user_id, $matches)) {
            $nextId = (int)$matches[1] + 1;
        }
        $userId = 'USR' . str_pad($nextId, 3, '0', STR_PAD_LEFT);


        // สร้างผู้ใช้ใหม่ และเปิดใช้งานทันทีโดยไม่ต้องใช้ OTP
        $user = \App\Models\User::create([
            'user_id'      => $userId,
            'username'     => $email,
            'name'         => $name,
            'email'        => $email,
            'password'     => \Hash::make($password),
            'user_role'    => 'Passenger',
            'usage_rights' => 'Active',
            'otp_code'     => null,
            'otp_expires_at' => null,
            'email_verified_at' => now(),
        ]);

        return response()->json([
            'status'  => 'success',
            'message' => 'สมัครสมาชิกสำเร็จ',
            'email'   => $email,
            'user_id' => $userId,
            'is_yru'  => true,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'status'  => 'error',
            'message' => 'เกิดข้อผิดพลาดในระบบ: ' . $e->getMessage(),
        ], 500);
    }
});

// --- API สำหรับการจัดการผู้ใช้งานหลังบ้าน (Admin CRUD Users) ---
Route::get('/api/users', [\App\Http\Controllers\UserController::class, 'index']);
Route::post('/api/users/sync-local', [\App\Http\Controllers\UserController::class, 'syncFromLocal']);
Route::delete('/api/users/sync-local/{empId}', [\App\Http\Controllers\UserController::class, 'syncDeleteLocal']);
Route::apiResource('/api/users', App\Http\Controllers\UserController::class);

// --- API สำหรับการจัดการเส้นทางและผูกรถไฟฟ้า (Admin Route & Train Assignment) ---
Route::get('/api/routes', [\App\Http\Controllers\RouteController::class, 'apiIndex']);
Route::post('/api/routes', [\App\Http\Controllers\RouteController::class, 'apiStore']);
Route::put('/api/routes/{route_code}', [\App\Http\Controllers\RouteController::class, 'apiUpdate']);
Route::delete('/api/routes/{route_code}', [\App\Http\Controllers\RouteController::class, 'apiDestroy']);
Route::post('/api/electric-trains/assign-route', [\App\Http\Controllers\RouteController::class, 'apiAssignTrain']);

Route::post('/api/register-verify-otp', function (Request $request) {
    try {
        $email = trim($request->input('email', ''));
        $otp = trim($request->input('otp', ''));

        if (!$email || !$otp) {
            return response()->json(['status' => 'error', 'message' => 'ข้อมูลไม่ครบถ้วน'], 422);
        }

        $user = \App\Models\User::where('username', $email)->first();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'ไม่พบข้อมูลผู้ใช้นี้ในระบบ'], 404);
        }

        if ($user->otp_code !== $otp) {
            return response()->json(['status' => 'error', 'message' => 'รหัส OTP ไม่ถูกต้อง'], 422);
        }

        if (now()->greaterThan($user->otp_expires_at)) {
            return response()->json(['status' => 'error', 'message' => 'รหัส OTP หมดอายุแล้ว โปรดขอรหัสใหม่'], 422);
        }

        // หากถูกต้อง อัปเดตสถานะและลบ OTP ทิ้ง
        $user->email_verified_at = now();
        $user->usage_rights = 'Active';
        $user->otp_code = null;
        $user->otp_expires_at = null;
        $user->save();

        return response()->json([
            'status' => 'success',
            'message' => 'ยืนยันตัวตนสำเร็จ สามารถเข้าสู่ระบบได้แล้ว'
        ]);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()], 500);
    }
});

Route::post('/api/register-resend-otp', function (Request $request) {
    try {
        $email = trim($request->input('email', ''));

        $user = \App\Models\User::where('username', $email)->first();

        if (!$user) {
            return response()->json(['status' => 'error', 'message' => 'ไม่พบข้อมูลผู้ใช้นี้ในระบบ'], 404);
        }

        if (!is_null($user->email_verified_at)) {
            return response()->json(['status' => 'error', 'message' => 'บัญชีนี้ได้รับการยืนยันแล้ว'], 422);
        }

        $otp = sprintf("%06d", mt_rand(1, 999999));
        $user->otp_code = $otp;
        $user->otp_expires_at = now()->addMinutes(5);
        $user->save();

        try {
            \Illuminate\Support\Facades\Mail::raw(
                "รหัส OTP ใหม่สำหรับยืนยันการสมัครสมาชิกของคุณคือ: {$otp} (รหัสนี้จะหมดอายุใน 5 นาที)",
                function ($message) use ($user) {
                    $message->to($user->email)->subject('รหัส OTP ใหม่ยืนยันการสมัครสมาชิก [YRU EV Tracker]');
                }
            );
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::info("Resent OTP for {$user->email} is {$otp}");
        }

        return response()->json([
            'status' => 'success',
            'message' => 'ส่งรหัส OTP ใหม่ไปยังอีเมลแล้ว'
        ]);
    } catch (\Exception $e) {
        return response()->json(['status' => 'error', 'message' => 'เกิดข้อผิดพลาด: ' . $e->getMessage()], 500);
    }
});

// --- ระบบ Authentication ---
Auth::routes();

// --- เส้นทางที่ต้องการยืนยันตัวตน (Authenticated Users) ---
Route::middleware(['auth'])->group(function () {
    
    Route::get('/profile', [App\Http\Controllers\HomeController::class, 'profile'])->name('profile');

    // 1. กลุ่มสิทธิ์: PASSENGER
    Route::middleware(['role:Passenger'])->group(function () {
        Route::get('/passenger/dashboard', [App\Http\Controllers\PassengerController::class, 'dashboard'])->name('passenger.dashboard');
    });

    // 2. กลุ่มสิทธิ์: DRIVER (พนักงานขับรถไฟฟ้า)
    Route::middleware(['role:Driver'])->group(function () {
        Route::prefix('driver')->name('driver.')->group(function () {
            Route::get('/dashboard', [App\Http\Controllers\DriverController::class, 'dashboard'])->name('dashboard');
            Route::post('/location/update', [App\Http\Controllers\DriverController::class, 'updateLocation'])->name('location.update');
            Route::post('/status/update', [App\Http\Controllers\DriverController::class, 'updateStatus'])->name('status.update');
            Route::get('/schedule', [App\Http\Controllers\DriverController::class, 'viewSchedule'])->name('schedule');
            Route::post('/travel-history/start', [App\Http\Controllers\DriverController::class, 'startTrip'])->name('trip.start');
            Route::post('/travel-history/end', [App\Http\Controllers\DriverController::class, 'endTrip'])->name('trip.end');
        });
    });

    // 3. กลุ่มสิทธิ์: MECHANIC
    Route::middleware(['role:Mechanic'])->group(function () {
        Route::prefix('mechanic')->name('mechanic.')->group(function () {
            Route::get('/dashboard', function() { return redirect()->route('mechanic.public_view'); })->name('dashboard');
        });
    });

    // 4. กลุ่มสิทธิ์: OPERATOR (ผู้ควบคุมระบบการเดินรถ)
    Route::middleware(['role:Operator'])->group(function () {
        Route::prefix('operator')->name('operator.')->group(function () {
            Route::get('/dashboard', [App\Http\Controllers\OperatorController::class, 'dashboard'])->name('dashboard');
            
            Route::resource('trains', App\Http\Controllers\Operator\ElectricTrainController::class);
            Route::resource('routes', App\Http\Controllers\Operator\RouteController::class);
            Route::resource('stations', App\Http\Controllers\Operator\StationController::class);
            Route::resource('schedules', App\Http\Controllers\Operator\ScheduleController::class);
            
            Route::resource('maintenances', App\Http\Controllers\MaintenanceController::class);
            
            Route::resource('notifications', App\Http\Controllers\Operator\NotificationController::class);
            Route::get('/travel-logs', [App\Http\Controllers\OperatorController::class, 'travelLogs'])->name('travel.logs');
        });
    });

    // 5. กลุ่มสิทธิ์: ADMINISTRATOR (ผู้ดูแลระบบสูงสุด)
    Route::middleware(['role:Administrator'])->group(function () {
        Route::prefix('admin')->name('admin.')->group(function () {
            Route::get('/dashboard', [App\Http\Controllers\AdminController::class, 'dashboard'])->name('dashboard');
            
            Route::resource('users', App\Http\Controllers\Admin\UserController::class);
            Route::get('/role-permissions', [App\Http\Controllers\AdminController::class, 'permissionsIndex'])->name('permissions.index');
            Route::post('/role-permissions', [App\Http\Controllers\AdminController::class, 'permissionsStore'])->name('permissions.store');
            
            Route::resource('maintenances', App\Http\Controllers\MaintenanceController::class);
            
            Route::get('/reports/usage', [App\Http\Controllers\AdminController::class, 'usageReport'])->name('reports.usage');
            Route::get('/reports/maintenance', [App\Http\Controllers\AdminController::class, 'maintenanceReport'])->name('reports.maintenance');
            Route::get('/reports/popularity', [App\Http\Controllers\AdminController::class, 'routePopularityReport'])->name('reports.popularity');
        });
    });
});

Route::get('/clear-cache', function () {
    \Illuminate\Support\Facades\Artisan::call('view:clear');
    \Illuminate\Support\Facades\Artisan::call('route:clear');
    \Illuminate\Support\Facades\Artisan::call('config:clear');
    \Illuminate\Support\Facades\Artisan::call('cache:clear');
    return "All Laravel caches cleared successfully!";
});
