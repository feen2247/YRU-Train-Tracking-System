<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

// --- Storage Sync System ---
Route::get('/clear-all-caches', function() {
    $res = [];
    try { \Illuminate\Support\Facades\Artisan::call('view:clear'); $res[] = 'view:clear'; } catch(\Throwable $e) {}
    try { \Illuminate\Support\Facades\Artisan::call('cache:clear'); $res[] = 'cache:clear'; } catch(\Throwable $e) {}
    try { \Illuminate\Support\Facades\Artisan::call('config:clear'); $res[] = 'config:clear'; } catch(\Throwable $e) {}
    try { \Illuminate\Support\Facades\Artisan::call('route:clear'); $res[] = 'route:clear'; } catch(\Throwable $e) {}
    if (function_exists('opcache_reset')) {
        try { opcache_reset(); $res[] = 'opcache_reset'; } catch(\Throwable $e) {}
    }
    for ($i = 1; $i <= 10; $i++) {
        $cCode = 'EV-' . str_pad($i, 2, '0', STR_PAD_LEFT);
        Cache::forget('current_driver_status_' . $i);
        Cache::forget('global_storage_yru_car_status_' . $cCode);
        Cache::forget('global_storage_yru_car_status_EV-0' . $i);
        Cache::forget('global_storage_yru_car_status_EV-' . $i);
        Cache::forget('global_storage_yru_car_status_' . $i);
        Cache::forget('admin_vehicle_lock_' . $cCode);
        Cache::put('current_driver_status_' . $i, [
            'status' => 'normal',
            'start_time' => '08:00 น.',
            'updated_at' => date('H:i:s')
        ], 28800);
    }
    Cache::forget('global_storage_yru_trams_v18');
    Cache::forget('global_storage_yru_trams_v16');
    Cache::forget('global_storage_yru_trams_v15');
    Cache::forget('global_storage_yru_user_login');
    Cache::forget('global_storage_yru_last_passenger_login');
    Cache::forget('global_storage_yru_last_passenger_name');
    Cache::forget('global_storage_yru_last_passenger_role');
    Cache::forget('global_storage_yru_last_passenger_id');
    Cache::forget('global_storage_index');
    Cache::forget('driver_vehicle_assignments');
    return response()->json([
        'status' => 'success',
        'message' => 'All caches cleared successfully!',
        'actions' => $res
    ]);
});

Route::get('/api/system/sync-db-users', function () {
    try {
        $usersToEnsure = [
            [
                'user_id' => 'USR-000014',
                'employee_id' => '69014',
                'username' => 'hadee',
                'prefix' => 'นาย',
                'first_name' => 'ฮาดิ',
                'last_name' => 'ลือแมะ',
                'name' => 'นายฮาดิ ลือแมะ',
                'email' => 'hadee@yru.ac.th',
                'password' => \Illuminate\Support\Facades\Hash::make('69014'),
                'user_role' => 'vehicle_head',
                'usage_rights' => 'Active',
                'status' => 'ใช้งาน',
                'email_verified_at' => now(),
            ],
            [
                'user_id' => 'USR-000018',
                'employee_id' => '69014',
                'username' => 'suthin',
                'prefix' => 'นาย',
                'first_name' => 'สุทิน',
                'last_name' => 'มีสุข',
                'name' => 'นายสุทิน มีสุข',
                'email' => 'suthin.m@yru.ac.th',
                'password' => \Illuminate\Support\Facades\Hash::make('69014'),
                'user_role' => 'vehicle_head',
                'usage_rights' => 'Active',
                'status' => 'ใช้งาน',
                'email_verified_at' => now(),
            ],
            [
                'user_id' => 'USR-000013',
                'employee_id' => '69013',
                'username' => 'prasan',
                'prefix' => 'นาย',
                'first_name' => 'ประสาน',
                'last_name' => 'งานดี',
                'name' => 'นายประสาน งานดี',
                'email' => 'prasan.g@yru.ac.th',
                'password' => \Illuminate\Support\Facades\Hash::make('69013'),
                'user_role' => 'Mechanic',
                'usage_rights' => 'Active',
                'status' => 'ใช้งาน',
                'email_verified_at' => now(),
            ]
        ];

        foreach ($usersToEnsure as $uData) {
            $user = \App\Models\User::where('username', $uData['username'])
                ->orWhere('email', $uData['email'])
                ->first();
            if ($user) {
                $user->employee_id = $uData['employee_id'];
                $user->name = $uData['name'];
                $user->user_role = $uData['user_role'];
                $user->status = $uData['status'];
                $user->usage_rights = $uData['usage_rights'];
                $user->password = $uData['password'];
                $user->email_verified_at = now();
                $user->save();
            } else {
                \App\Models\User::create($uData);
            }
        }

        $sessionTableInfo = 'Table not checked';
        try {
            if (!\Illuminate\Support\Facades\Schema::hasTable('sessions')) {
                \Illuminate\Support\Facades\Schema::create('sessions', function ($table) {
                    $table->string('id')->primary();
                    $table->string('user_id', 255)->nullable()->index();
                    $table->string('ip_address', 45)->nullable();
                    $table->text('user_agent')->nullable();
                    $table->longText('payload');
                    $table->integer('last_activity')->index();
                });
                $sessionTableInfo = 'Created sessions table with string user_id';
            } else {
                // Check user_id type and alter if needed
                try {
                    \Illuminate\Support\Facades\DB::statement("ALTER TABLE `sessions` MODIFY `user_id` VARCHAR(255) NULL");
                    $sessionTableInfo = 'Altered sessions.user_id to VARCHAR(255)';
                } catch (\Throwable $exAlter) {
                    $sessionTableInfo = 'Alter notice: ' . $exAlter->getMessage();
                }
            }
        } catch (\Throwable $exSession) {
            $sessionTableInfo = 'Session table error: ' . $exSession->getMessage();
        }

        try {
            \Illuminate\Support\Facades\Artisan::call('view:clear');
            \Illuminate\Support\Facades\Artisan::call('cache:clear');
        } catch (\Throwable $e) {}

        return response()->json([
            'status' => 'success',
            'session_table_info' => $sessionTableInfo,
            'users' => \App\Models\User::all(['user_id', 'employee_id', 'username', 'email', 'name', 'user_role'])
        ]);
    } catch (\Throwable $e) {
        return response()->json(['status' => 'error', 'message' => $e->getMessage()]);
    }
});

Route::post('/api/storage/sync', function (Request $request) {
    $payload = $request->input('payload', []);
    $privateKeys = [
        'yru_user_login', 'yru_remember_login', 'yru_last_passenger_login', 
        'yru_last_passenger_name', 'yru_last_passenger_role', 'yru_last_passenger_id',
        'remembered_username', 'remembered_password', 'yru_user_session', 'yru_auth_token'
    ];
    $index = Cache::get('global_storage_index', []);
    if (!is_array($index)) $index = [];
    foreach ($payload as $key => $value) {
        if (in_array($key, $privateKeys)) {
            continue; // NEVER store private client auth data in global server cache
        }
        Cache::forever("global_storage_{$key}", $value);
        if (!in_array($key, $index)) {
            $index[] = $key;
        }
    }
    Cache::forever('global_storage_index', $index);
    return response()->json(['status' => 'success']);
});

Route::get('/api/debug-auth', function () {
    $dbUsersCols = [];
    $dbSessionsCols = [];
    $recentSessions = [];
    try {
        $dbUsersCols = \Illuminate\Support\Facades\DB::select("DESCRIBE users");
        $dbSessionsCols = \Illuminate\Support\Facades\DB::select("DESCRIBE sessions");
        $recentSessions = \Illuminate\Support\Facades\DB::table('sessions')->orderBy('last_activity', 'desc')->take(5)->get();
    } catch (\Throwable $e) {
        $dbUsersCols = $e->getMessage();
    }
    return response()->json([
        'auth_check' => \Illuminate\Support\Facades\Auth::check(),
        'auth_user' => \Illuminate\Support\Facades\Auth::user(),
        'session_id' => session()->getId(),
        'users_table' => $dbUsersCols,
        'sessions_table' => $dbSessionsCols,
        'recent_sessions' => $recentSessions,
    ]);
});

$storageInitHandler = function () {
    $defaultKeys = [
        'yru_trams_v18', 'yru_stops_v2', 'yru_users_v6', 'yru_users_v8',
        'yru_call_queue', 'yru_surveys', 'yru_external_users_v4', 'yru_maintenance_tickets_v3',
        'yru_routes_v1', 'yru_role_permissions', 'yru_latest_call', 'yru_today_trips_accumulated',
        'yru_today_pax_accumulated', 'yru_driver_shifts', 'yru_driver_shift_history', 'yru_today_rounds_accumulated'
    ];
    $privateKeys = [
        'yru_user_login', 'yru_remember_login', 'yru_last_passenger_login', 
        'yru_last_passenger_name', 'yru_last_passenger_role', 'yru_last_passenger_id',
        'remembered_username', 'remembered_password', 'yru_user_session', 'yru_auth_token'
    ];
    $index = Cache::get('global_storage_index', []);
    if (!is_array($index)) $index = [];
    $keys = array_unique(array_merge($defaultKeys, $index));
    
    $data = [];
    foreach ($keys as $key) {
        if (in_array($key, $privateKeys)) {
            continue; // NEVER send private user credentials to client
        }
        $val = Cache::get("global_storage_{$key}");
        if ($val !== null) {
            $data[$key] = $val;
        }
    }
    
    $privateKeysJson = json_encode($privateKeys);

    $js = "
        (function() {
            try {
                const privateKeys = " . $privateKeysJson . ";
                const isPrivate = function(k) {
                    if (!k) return false;
                    return privateKeys.indexOf(k) !== -1 || k.indexOf('user_login') !== -1 || k.indexOf('remember') !== -1 || k.indexOf('passenger_login') !== -1;
                };
                const serverStorage = " . json_encode($data) . ";
                Object.keys(serverStorage).forEach(key => {
                    if (isPrivate(key)) return;
                    if (serverStorage[key] === '') {
                        localStorage.removeItem(key);
                    } else {
                        localStorage.setItem(key, serverStorage[key]);
                    }
                });

                Object.keys(localStorage).forEach(key => {
                    if (key.startsWith('yru_') && !isPrivate(key) && !serverStorage.hasOwnProperty(key)) {
                        fetch('/api/storage/sync', {
                            method: 'POST',
                            keepalive: true,
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
                    if (key.startsWith('yru_') && !isPrivate(key)) {
                        fetch('/api/storage/sync', {
                            method: 'POST',
                            keepalive: true,
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
                    if (key.startsWith('yru_') && !isPrivate(key)) {
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
};

Route::get('/api/storage/init.js', $storageInitHandler);
Route::get('/api/storage/init', $storageInitHandler);
Route::get('/api/storage/sync-init', $storageInitHandler);

// --- หน้าหลัก / เข้าสู่ระบบ (Public Login Page) ---
Route::get('/', function () {
    try {
        \Illuminate\Support\Facades\Artisan::call('view:clear');
    } catch (\Throwable $e) {}
    return view('passenger.welcome.index'); 
})->name('login');

// --- ออกจากระบบ (Logout) ---
Route::get('/logout', function() {
    \Illuminate\Support\Facades\Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect('/');
})->name('logout');
Route::post('/logout', function() {
    \Illuminate\Support\Facades\Auth::logout();
    session()->invalidate();
    session()->regenerateToken();
    return redirect('/');
});

// --- หน้าผู้โดยสาร / นักศึกษา (Student / Passenger Tracking View) ---
Route::get('/home', function () {
    if (!\Illuminate\Support\Facades\Auth::check()) {
        return redirect('/');
    }
    return view('passenger.home.index');
})->name('home');

// --- หน้าพนักงานขับรถไฟฟ้า (Driver View) ---
Route::get('/tracking', function () {
    if (!\Illuminate\Support\Facades\Auth::check()) {
        return redirect('/');
    }
    $role = strtolower(trim(\Illuminate\Support\Facades\Auth::user()->user_role ?? ''));
    if (!in_array($role, ['driver', 'พนักงานขับรถ', 'พนักงานขับรถไฟฟ้า', 'admin', 'administrator', 'ผู้ดูแลระบบ'])) {
        return redirect('/');
    }
    return view('passenger.tracking.index');
})->name('passenger.tracking.index');

// --- หน้าผู้ดูแลระบบ (Admin View) ---
Route::get('/admin-view', function () {
    if (!\Illuminate\Support\Facades\Auth::check()) {
        return redirect('/');
    }
    $role = strtolower(trim(\Illuminate\Support\Facades\Auth::user()->user_role ?? ''));
    if (!in_array($role, ['admin', 'administrator', 'ผู้ดูแลระบบ', 'staff', 'เจ้าหน้าที่'])) {
        return redirect('/');
    }
    $recentActivities = [];
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('travel_histories')) {
            $recentActivities = \App\Models\TravelHistory::with(['electricTrain', 'driver', 'route'])
                ->latest()
                ->take(10)
                ->get();
        }
    } catch (\Exception $e) {
        $recentActivities = [];
    }
    return view('passenger.admin.index', compact('recentActivities')); 
})->name('admin.view');

Route::get('/admin', function () {
    return redirect('/admin-view'); 
});

Route::get('/admin-dashboard', function () {
    return redirect('/admin-view'); 
});

Route::get('/admin/dashboard', function () {
    return redirect('/admin-view'); 
});

Route::get('/admin/view', function () {
    return redirect('/admin-view'); 
});

// --- ระบบผู้บริหาร (Executive View) ---
Route::get('/executive-view', [App\Http\Controllers\ExecutiveController::class, 'index'])->name('executive.view');
Route::get('/executive_view', function() {
    return redirect('/executive-view');
});
Route::get('/executive', function() {
    return redirect('/executive-view');
})->name('executive.index');
Route::get('/executive-dashboard', function() {
    return redirect('/executive-view');
})->name('executive.dashboard');
Route::get('/executive_dashboard', function() {
    return redirect('/executive-view');
});
Route::post('/api/executive/approve-maintenance', [App\Http\Controllers\ExecutiveController::class, 'approveMaintenance']);
Route::post('/api/executive/reject-maintenance', [App\Http\Controllers\ExecutiveController::class, 'rejectMaintenance']);

// --- ระบบช่างซ่อมบำรุง (Maintenance System View) ---
Route::get('/maintenance-system', function () {
    if (!\Illuminate\Support\Facades\Auth::check()) {
        return redirect('/');
    }
    $role = strtolower(trim(\Illuminate\Support\Facades\Auth::user()->user_role ?? ''));
    $isAllowed = in_array($role, [
        'mechanic', 'technician', 'maintenance', 'ช่างซ่อมบำรุง', 'ช่างซ่อม', 'ช่าง',
        'admin', 'administrator', 'ผู้ดูแลระบบ',
        'vehicle_head', 'vehicle head', 'vehiclehead', 'head_of_vehicle', 'supervisor', 'หัวหน้ายานพาหนะ'
    ]) || str_contains($role, 'ช่าง') || str_contains($role, 'mechanic') || str_contains($role, 'หัวหน้า') || str_contains($role, 'vehicle');
    if (!$isAllowed) {
        return redirect('/');
    }
    return response()->view('passenger.maintenance.index')
        ->header('Cache-Control', 'no-cache, no-store, must-revalidate, max-age=0')
        ->header('Pragma', 'no-cache')
        ->header('Expires', '0');
})->name('mechanic.public_view');

Route::get('/maintenance', function () {
    return redirect('/maintenance-system');
});

// --- ระบบหัวหน้ายานพาหนะ (Vehicle Head / Fleet Supervisor View) ---
Route::get('/vehicle-head', function () {
    if (!\Illuminate\Support\Facades\Auth::check()) {
        return redirect('/');
    }
    $role = strtolower(trim(\Illuminate\Support\Facades\Auth::user()->user_role ?? ''));
    $isVehicleHead = in_array($role, [
        'vehicle_head', 'vehicle head', 'vehiclehead', 'head_of_vehicle', 'supervisor', 'หัวหน้ายานพาหนะ',
        'admin', 'administrator', 'ผู้ดูแลระบบ'
    ]) || str_contains($role, 'หัวหน้า') || str_contains($role, 'vehicle') || str_contains($role, 'supervisor');
    if (!$isVehicleHead) {
        return redirect('/');
    }
    return view('passenger.vehicle-head.index');
})->name('vehicle_head.index');

Route::get('/vehicle-head-view', function () {
    return redirect('/vehicle-head');
});


// ?? --- API สำหรับจำลองระบบเรียกรถและแชร์สถานะคนขับ ---

Route::post('/api/call-ev', function (Request $request) {
    date_default_timezone_set('Asia/Bangkok');
    $pax = (int) $request->input('pax', 1);
    $car_id = $request->input('car_id');
    $userName = $request->input('user_name') ?: $request->input('passenger_name');
    $userRole = $request->input('user_role', 'นักศึกษา');
    $userId = $request->input('user_id');
    if (!$userName && Auth::check()) {
        $userName = Auth::user()->name ?: Auth::user()->username;
        $userRole = Auth::user()->user_role ?: 'นักศึกษา';
        $userId = Auth::user()->employee_id ?: Auth::user()->id;
    }
    if (!$userName) {
        $userName = 'นางสาวทัศนีย์ สาและ';
        $userRole = 'นักศึกษา';
        $userId = '406665014';
    }
    $data = [
        'call_id'     => $request->input('call_id', 'C' . time()),
        'station'     => $request->input('station'),
        'destination' => $request->input('destination'),
        'pax'         => $pax,
        'car_id'      => $car_id,
        'time'        => date('H:i:s'),
        'timestamp'   => time(),
        'status'      => 'pending',
        'user_id'     => $userId,
        'user_name'   => $userName,
        'passenger_name' => $userName,
        'user_role'   => $userRole
    ];
    Cache::put('latest_ev_request', $data, 600);
    @file_put_contents(storage_path('app/latest_ev_request.json'), json_encode($data));

    // เพิ่มเข้าคิว Active Requests รวม (Multi-call Queue)
    $activeRequests = Cache::get('active_ev_requests');
    if (!$activeRequests && file_exists(storage_path('app/active_ev_requests.json'))) {
        $activeRequests = json_decode(@file_get_contents(storage_path('app/active_ev_requests.json')), true);
    }
    if (!is_array($activeRequests)) $activeRequests = [];
    $activeRequests[$data['call_id']] = $data;
    Cache::put('active_ev_requests', $activeRequests, 3600);
    @file_put_contents(storage_path('app/active_ev_requests.json'), json_encode($activeRequests));

    if ($car_id) {
        Cache::put('latest_ev_request_' . $car_id, $data, 600);
        @file_put_contents(storage_path('app/latest_ev_request_' . $car_id . '.json'), json_encode($data));
        
        Cache::put('global_storage_yru_car_status_' . $car_id, [
            'occupied' => $pax,
            'capacity' => 10,
            'status'   => 'มีผู้โดยสารเรียกรถ',
            'station'  => $request->input('station')
        ], 600);
    }

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
        $route_id = ($car_id == '2' || (isset($num) && $num == 2)) ? '2' : '1';
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

Route::get('/api/check-ev-request', function (Request $request) {
    $car_id = $request->input('car_id');
    
    $activeRequests = Cache::get('active_ev_requests');
    if (!$activeRequests && file_exists(storage_path('app/active_ev_requests.json'))) {
        $activeRequests = json_decode(@file_get_contents(storage_path('app/active_ev_requests.json')), true);
    }
    if (!is_array($activeRequests)) $activeRequests = [];

    $clearedIds = Cache::get('cleared_ev_call_ids', []);
    if (!is_array($clearedIds)) $clearedIds = [];

    // Filter out expired (> 1 hour) or finished requests
    $activeRequests = array_filter($activeRequests, function($req) use ($clearedIds) {
        if (empty($req['timestamp'])) return false;
        if (time() - $req['timestamp'] > 3600) return false;
        $status = strtolower($req['status'] ?? '');
        if (in_array($status, ['completed', 'cleared', 'cancelled', 'dropped_off'])) return false;
        $cid = $req['call_id'] ?? ($req['id'] ?? '');
        if ($cid && in_array((string)$cid, $clearedIds)) return false;
        return true;
    });

    // Filter for requested car if given (Strict matching: do not leak calls across cars)
    $carRequests = array_values(array_filter($activeRequests, function($req) use ($car_id) {
        if (!$car_id) return true;
        $targetCar = trim((string)($req['car_id'] ?? ''));
        if (empty($targetCar)) return false;
        
        $cleanTarget = strtoupper($targetCar);
        $cleanCar = strtoupper(trim((string)$car_id));
        if ($cleanTarget === $cleanCar) return true;
        
        $numTarget = (int) filter_var($cleanTarget, FILTER_SANITIZE_NUMBER_INT);
        $numCar = (int) filter_var($cleanCar, FILTER_SANITIZE_NUMBER_INT);
        if ($numTarget > 0 && $numCar > 0 && $numTarget === $numCar) {
            return true;
        }
        return false;
    }));

    if (empty($carRequests)) {
        // Check legacy fallback only for this specific car if car_id is given
        $legacy = null;
        if ($car_id) {
            $legacy = Cache::get('latest_ev_request_' . $car_id);
            if (!$legacy && file_exists(storage_path('app/latest_ev_request_' . $car_id . '.json'))) {
                $legacy = json_decode(@file_get_contents(storage_path('app/latest_ev_request_' . $car_id . '.json')), true);
            }
        } else {
            $legacy = Cache::get('latest_ev_request');
            if (!$legacy && file_exists(storage_path('app/latest_ev_request.json'))) {
                $legacy = json_decode(@file_get_contents(storage_path('app/latest_ev_request.json')), true);
            }
        }
        if ($legacy) {
            $legStatus = strtolower($legacy['status'] ?? '');
            $legCid = (string)($legacy['call_id'] ?? ($legacy['id'] ?? ''));
            $legTime = (int)($legacy['timestamp'] ?? 0);
            if (in_array($legStatus, ['completed', 'cleared', 'cancelled', 'dropped_off']) || 
                ($legCid && in_array($legCid, $clearedIds)) ||
                (time() - $legTime > 1800)) {
                $legacy = null;
            }
            if ($legacy && $car_id) {
                $legCar = strtoupper(trim((string)($legacy['car_id'] ?? '')));
                $cleanCar = strtoupper(trim((string)$car_id));
                $numLeg = (int) filter_var($legCar, FILTER_SANITIZE_NUMBER_INT);
                $numCar = (int) filter_var($cleanCar, FILTER_SANITIZE_NUMBER_INT);
                if ($legCar !== $cleanCar && ($numLeg <= 0 || $numLeg !== $numCar)) {
                    $legacy = null;
                }
            }
        }
        if ($legacy) {
            $carRequests = [$legacy];
        } else {
            date_default_timezone_set('Asia/Bangkok');
            $todayDate = date('Y-m-d');
            $carKey = 'today_car_users_' . $car_id . '_' . $todayDate;
            $num = (int) filter_var($car_id, FILTER_SANITIZE_NUMBER_INT);
            $numKey = 'today_car_users_' . $num . '_' . $todayDate;
            $todayCarUsers = max((int) Cache::get($carKey, 0), (int) Cache::get($numKey, 0));
            return response()->json([
                'requests' => [],
                'total_requests' => 0,
                'today_car_users' => $todayCarUsers
            ]);
        }
    }

    $first = $carRequests[0];
    $response = $first;
    $response['requests'] = $carRequests;
    $response['total_requests'] = count($carRequests);

    date_default_timezone_set('Asia/Bangkok');
    $todayDate = date('Y-m-d');
    $carKey = 'today_car_users_' . $car_id . '_' . $todayDate;
    $num = (int) filter_var($car_id, FILTER_SANITIZE_NUMBER_INT);
    $numKey = 'today_car_users_' . $num . '_' . $todayDate;
    $todayCarUsers = max((int) Cache::get($carKey, 0), (int) Cache::get($numKey, 0));
    $response['today_car_users'] = $todayCarUsers;

    return response()->json($response);
});

Route::post('/api/accept-ev-request', function (Request $request) {
    $car_id = $request->input('car_id');
    $call_id = $request->input('call_id');
    $newStatus = $request->input('status', 'STEP2_DROPOFF');

    $activeRequests = Cache::get('active_ev_requests');
    if (!$activeRequests && file_exists(storage_path('app/active_ev_requests.json'))) {
        $activeRequests = json_decode(@file_get_contents(storage_path('app/active_ev_requests.json')), true);
    }
    if (!is_array($activeRequests)) $activeRequests = [];

    $updated = false;
    if ($call_id && isset($activeRequests[$call_id])) {
        $activeRequests[$call_id]['status'] = $newStatus;
        $updated = true;
    } else {
        foreach ($activeRequests as $cid => &$req) {
            if (!$car_id || empty($req['car_id']) || $req['car_id'] === $car_id) {
                $req['status'] = $newStatus;
                $updated = true;
                break;
            }
        }
    }

    Cache::put('active_ev_requests', $activeRequests, 3600);
    @file_put_contents(storage_path('app/active_ev_requests.json'), json_encode($activeRequests));

    // Also sync status with legacy single caches to prevent reverting to pending
    $leg = Cache::get('latest_ev_request');
    if ($leg && (empty($call_id) || ($leg['call_id'] ?? '') === $call_id || ($leg['id'] ?? '') === $call_id)) {
        $leg['status'] = $newStatus;
        Cache::put('latest_ev_request', $leg, 600);
        @file_put_contents(storage_path('app/latest_ev_request.json'), json_encode($leg));
    }
    if ($car_id) {
        $legCar = Cache::get('latest_ev_request_' . $car_id);
        if ($legCar && (empty($call_id) || ($legCar['call_id'] ?? '') === $call_id || ($legCar['id'] ?? '') === $call_id)) {
            $legCar['status'] = $newStatus;
            Cache::put('latest_ev_request_' . $car_id, $legCar, 600);
            @file_put_contents(storage_path('app/latest_ev_request_' . $car_id . '.json'), json_encode($legCar));
        }
    }

    return response()->json(['status' => 'success', 'updated' => $updated]);
});

Route::post('/api/clear-ev-request', function (Request $request) {
    $car_id = $request->input('car_id');
    $call_id = $request->input('call_id');

    // Register cleared call ID to prevent zombie reappearance
    $clearedIds = Cache::get('cleared_ev_call_ids', []);
    if (!is_array($clearedIds)) $clearedIds = [];
    if ($call_id) {
        $clearedIds[] = (string)$call_id;
        $clearedIds = array_values(array_unique($clearedIds));
        Cache::put('cleared_ev_call_ids', $clearedIds, 7200);
    }

    $activeRequests = Cache::get('active_ev_requests');
    if (!$activeRequests && file_exists(storage_path('app/active_ev_requests.json'))) {
        $activeRequests = json_decode(@file_get_contents(storage_path('app/active_ev_requests.json')), true);
    }
    if (!is_array($activeRequests)) $activeRequests = [];

    if ($call_id && isset($activeRequests[$call_id])) {
        unset($activeRequests[$call_id]);
    }
    if ($car_id) {
        $activeRequests = array_filter($activeRequests, function($req) use ($car_id, $call_id) {
            $rCid = $req['call_id'] ?? ($req['id'] ?? '');
            if ($call_id && $rCid === $call_id) return false;
            if (!$call_id && !empty($req['car_id']) && $req['car_id'] === $car_id) return false;
            return true;
        });
    }

    Cache::put('active_ev_requests', $activeRequests, 3600);
    @file_put_contents(storage_path('app/active_ev_requests.json'), json_encode($activeRequests));

    // Always clear matching single caches
    if ($car_id) {
        Cache::forget('latest_ev_request_' . $car_id);
        if (file_exists(storage_path('app/latest_ev_request_' . $car_id . '.json'))) {
            @unlink(storage_path('app/latest_ev_request_' . $car_id . '.json'));
        }
    }
    if ($call_id) {
        $leg = Cache::get('latest_ev_request');
        if (!$leg && file_exists(storage_path('app/latest_ev_request.json'))) {
            $leg = json_decode(@file_get_contents(storage_path('app/latest_ev_request.json')), true);
        }
        if ($leg && (($leg['call_id'] ?? '') === $call_id || ($leg['id'] ?? '') === $call_id)) {
            Cache::forget('latest_ev_request');
            if (file_exists(storage_path('app/latest_ev_request.json'))) {
                @unlink(storage_path('app/latest_ev_request.json'));
            }
        }
    }
    if (empty($activeRequests)) {
        Cache::forget('latest_ev_request');
        if (file_exists(storage_path('app/latest_ev_request.json'))) {
            @unlink(storage_path('app/latest_ev_request.json'));
        }
        for ($i = 1; $i <= 10; $i++) {
            $cid = sprintf('EV-%02d', $i);
            Cache::forget('latest_ev_request_' . $cid);
            if (file_exists(storage_path('app/latest_ev_request_' . $cid . '.json'))) {
                @unlink(storage_path('app/latest_ev_request_' . $cid . '.json'));
            }
        }
    }

    return response()->json(['status' => 'success', 'message' => 'เคลียร์สัญญาณเรียกรถเรียบร้อยแล้ว']);
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

    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('electric_trains')) {
            \App\Models\ElectricTrain::where('skytrain_code', $car_code)
                ->update(['status' => ($status === 'broken' ? 'รถขัดข้อง' : 'พร้อมใช้งาน')]);
        }
    } catch (\Exception $e) {}

    return response()->json(['status' => 'success', 'data' => $statusData]);
});

// Admin explicit vehicle status management (ระงับการใช้งาน / รถขัดข้อง โดยแอดมิน)
Route::post('/api/admin/set-vehicle-status', function (Request $request) {
    $carId = (string)$request->input('car_id', 'EV-01');
    $status = (string)$request->input('status', 'พร้อมใช้งาน');
    $locked = (bool)$request->input('locked', false);
    $carNum = (int)preg_replace('/[^0-9]/', '', $carId) ?: 1;

    if ($locked && ($status === 'ระงับการใช้งาน' || $status === 'รถขัดข้อง')) {
        Cache::forever('admin_vehicle_lock_' . $carId, [
            'locked' => true,
            'status' => $status,
            'reason' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]);
        Cache::put('current_driver_status_' . $carNum, [
            'status' => ($status === 'ระงับการใช้งาน' ? 'suspended' : 'broken'),
            'start_time' => '-',
            'updated_at' => date('H:i:s')
        ], 28800);
        Cache::forever('global_storage_yru_car_status_' . $carId, json_encode([
            'status' => $status,
            'active_issue' => $status,
            'updated_at' => date('Y-m-d H:i:s')
        ]));
    } else {
        Cache::forget('admin_vehicle_lock_' . $carId);
        Cache::put('current_driver_status_' . $carNum, [
            'status' => 'normal',
            'start_time' => '08:00 น.',
            'updated_at' => date('H:i:s')
        ], 28800);
        Cache::forever('global_storage_yru_car_status_' . $carId, json_encode([
            'status' => 'พร้อมใช้งาน',
            'active_issue' => ''
        ]));
    }

    // Direct update of yru_trams_v18 in backend Cache to ensure immediate refresh persistence
    $v18Raw = Cache::get('global_storage_yru_trams_v18');
    if ($v18Raw) {
        $tramsList = json_decode($v18Raw, true);
        if (is_array($tramsList)) {
            $found = false;
            foreach ($tramsList as &$t) {
                if (($t['id'] ?? '') === $carId) {
                    $t['status'] = $status;
                    $t['admin_suspended'] = $locked;
                    $t['admin_locked'] = $locked;
                    if ($locked) {
                        $t['driver'] = '';
                        $t['driver_id'] = '';
                    }
                    $found = true;
                    break;
                }
            }
            Cache::forever('global_storage_yru_trams_v18', json_encode($tramsList));
            Cache::forever('global_storage_yru_trams_v16', json_encode($tramsList));
        }
    }

    // Ensure index has these keys for init.js syncing
    $index = Cache::get('global_storage_index', []);
    if (!is_array($index)) $index = [];
    $keysToAdd = ['yru_trams_v18', 'yru_car_status_' . $carId];
    foreach ($keysToAdd as $k) {
        if (!in_array($k, $index)) $index[] = $k;
    }
    Cache::forever('global_storage_index', $index);

    return response()->json(['status' => 'success']);
});

Route::get('/api/get-driver-status', function () {
    $response = [];
    for ($i = 1; $i <= 10; $i++) {
        $defaultData = [
            'status' => 'normal',
            'start_time' => '08:00 น.',
            'updated_at' => date('H:i:s')
        ];
        $val = Cache::get('current_driver_status_' . $i, $defaultData);
        $response['car_' . $i] = $val;
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

Route::post('/api/update-driver-status', function (Request $request) {
    $rawCarId = (string) $request->input('car_id', 'EV-01');
    $carNum = (int) preg_replace('/[^0-9]/', '', $rawCarId) ?: 1;
    $carCode = 'EV-' . str_pad($carNum, 2, '0', STR_PAD_LEFT);
    $status = (string) $request->input('status', 'normal');
    $statusText = ($status === 'pause' || $status === 'พัก' || $status === 'พักเบรค' || $status === 'พักเบรก') ? 'พักเบรค' : 'พร้อมใช้งาน';

    $driverStatus = Cache::get('current_driver_status_' . $carNum, []);
    $driverStatus['status'] = $status;
    $driverStatus['status_text'] = $statusText;
    $driverStatus['updated_at'] = date('H:i:s');
    Cache::put('current_driver_status_' . $carNum, $driverStatus, 28800);

    $storageKey = 'global_storage_yru_car_status_' . $carCode;
    $existingStatus = json_decode(Cache::get($storageKey, '{}'), true) ?: [];
    $existingStatus['status'] = $statusText;
    $existingStatus['driver_status'] = $statusText;
    $existingStatus['updated_at'] = date('Y-m-d H:i:s');
    Cache::forever($storageKey, json_encode($existingStatus));

    $v18Raw = Cache::get('global_storage_yru_trams_v18');
    if ($v18Raw) {
        $tramsList = json_decode($v18Raw, true);
        if (is_array($tramsList)) {
            foreach ($tramsList as &$t) {
                if (($t['id'] ?? '') === $carCode) {
                    $t['status'] = $statusText;
                    break;
                }
            }
            Cache::forever('global_storage_yru_trams_v18', json_encode($tramsList));
        }
    }

    $index = Cache::get('global_storage_index', []);
    if (!is_array($index)) $index = [];
    $keysToAdd = ['yru_trams_v18', 'yru_car_status_' . $carCode];
    foreach ($keysToAdd as $k) {
        if (!in_array($k, $index)) $index[] = $k;
    }
    Cache::forever('global_storage_index', $index);

    return response()->json([
        'status' => 'success',
        'car_id' => $carCode,
        'driver_status' => $status,
        'status_text' => $statusText
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

// 🛠️ API จัดการคิวงานซ่อมบำรุงรถไฟฟ้า
Route::get('/api/maintenance/tickets', function () {
    $raw = Cache::get('global_storage_yru_maintenance_tickets_v3', null);
    if ($raw) {
        $tickets = is_string($raw) ? json_decode($raw, true) : $raw;
        if (is_array($tickets)) return response()->json($tickets);
    }
    $tickets = Cache::get('maintenance_tickets', []);
    return response()->json($tickets);
});

Route::get('/api/maintenance/requests', function () {
    $raw = Cache::get('global_storage_yru_maintenance_tickets_v3', null);
    $tickets = [];
    if ($raw) {
        $tickets = is_string($raw) ? json_decode($raw, true) : $raw;
    }

    $defaultTickets = [
        [
            'id' => 'MNT-2569-ED4D',
            'ticket_no' => 'MNT-2569-ED4D',
            'car_id' => 'EV-01',
            'license_plate' => 'EV-01 (กค 1234 ยะลา)',
            'driver_name' => 'นายอิสลีย์ มูเก็ง',
            'reporter' => 'นายอิสลีย์ มูเก็ง',
            'issues' => ['ระบบเบรกมีเสียงดังผิดปกติ', 'แบตเตอรี่เสื่อมสภาพเร็ว'],
            'issue' => 'ระบบเบรกมีเสียงดังผิดปกติ | แบตเตอรี่เสื่อมสภาพเร็ว',
            'approved_items' => ['ระบบเบรกมีเสียงดังผิดปกติ', 'แบตเตอรี่เสื่อมสภาพเร็ว'],
            'total_cost' => 18500,
            'status' => 'pending_director',
            'urgency' => 'ด่วนที่สุด',
            'date' => '03/09/2569 09:15 น.',
            'doc_date' => '03',
            'doc_month' => 'กันยายน',
            'doc_year' => '2569',
            'garage_name' => 'อู่ยะลาการช่าง (ศูนย์บริการมาตรฐาน)',
            'mechanic_name' => 'นายอิบรอเฮม อูมา'
        ],
        [
            'id' => 'MNT-2026-005',
            'ticket_no' => 'MNT-2026-005',
            'car_id' => 'EV-03',
            'license_plate' => 'กค 9014 ยะลา',
            'driver_name' => 'นายมะกอซี ตาเยะ',
            'reporter' => 'นายมะกอซี ตาเยะ',
            'issues' => ['ระบบเครื่องปรับอากาศไม่เย็น', 'สายพานหน้าเครื่องหย่อน'],
            'issue' => 'ระบบเครื่องปรับอากาศไม่เย็น | สายพานหน้าเครื่องหย่อน',
            'approved_items' => ['ระบบเครื่องปรับอากาศไม่เย็น', 'สายพานหน้าเครื่องหย่อน'],
            'total_cost' => 6400,
            'status' => 'pending_director',
            'urgency' => 'ด่วน',
            'date' => '03/09/2569 08:30 น.',
            'doc_date' => '03',
            'doc_month' => 'กันยายน',
            'doc_year' => '2569',
            'garage_name' => 'อู่ยะลาการช่าง (ศูนย์บริการมาตรฐาน)',
            'mechanic_name' => 'นายประสาน ช่างเครื่อง'
        ],
        [
            'id' => 'MNT-2026-004',
            'ticket_no' => 'MNT-2026-004',
            'car_id' => 'EV-05',
            'license_plate' => 'กค 9016 ยะลา',
            'driver_name' => 'นายสมศักดิ์ ขยันยิ่ง',
            'reporter' => 'นายสมศักดิ์ ขยันยิ่ง',
            'issues' => ['ตรวจเช็คระยะระบบช่วงล่าง', 'เปลี่ยนน้ำมันเกียร์ไฟฟ้า'],
            'issue' => 'ตรวจเช็คระยะระบบช่วงล่าง | เปลี่ยนน้ำมันเกียร์ไฟฟ้า',
            'approved_items' => ['ตรวจเช็คระยะระบบช่วงล่าง', 'เปลี่ยนน้ำมันเกียร์ไฟฟ้า'],
            'total_cost' => 12500,
            'status' => 'pending_director',
            'urgency' => 'ปกติ',
            'date' => '02/09/2569 16:45 น.',
            'doc_date' => '02',
            'doc_month' => 'กันยายน',
            'doc_year' => '2569',
            'garage_name' => 'ศูนย์บริการยางการช่าง ยะลา',
            'mechanic_name' => 'นายสมเกียรติ'
        ],
        [
            'id' => 'MNT-2026-003',
            'ticket_no' => 'MNT-2026-003',
            'car_id' => 'EV-02',
            'license_plate' => 'กค 5678 ยะลา',
            'driver_name' => 'นายอัรฟาน มะเระ',
            'reporter' => 'นายอัรฟาน มะเระ',
            'issues' => ['เปลี่ยนยางรถยนต์ 4 เส้น', 'ถ่วงล้อและตั้งศูนย์ใหม่'],
            'issue' => 'เปลี่ยนยางรถยนต์ 4 เส้น | ถ่วงล้อและตั้งศูนย์ใหม่',
            'approved_items' => ['เปลี่ยนยางรถยนต์ 4 เส้น', 'ถ่วงล้อและตั้งศูนย์ใหม่'],
            'total_cost' => 14200,
            'status' => 'pending_director',
            'urgency' => 'ด่วน',
            'date' => '01/09/2569 14:20 น.',
            'doc_date' => '01',
            'doc_month' => 'กันยายน',
            'doc_year' => '2569',
            'garage_name' => 'ศูนย์บริการยางการช่าง ยะลา',
            'mechanic_name' => 'นายสมเกียรติ'
        ],
        [
            'id' => 'MNT-2026-002',
            'ticket_no' => 'MNT-2026-002',
            'car_id' => 'EV-08',
            'license_plate' => 'กค 5566 ยะลา',
            'driver_name' => 'นายสมใจ ใจดี',
            'reporter' => 'นายสมใจ ใจดี',
            'issues' => ['ซ่อมระบบไฟส่องสว่างสัญญาณเตือน', 'เปลี่ยนหลอดไฟ LED'],
            'issue' => 'ซ่อมระบบไฟส่องสว่างสัญญาณเตือน | เปลี่ยนหลอดไฟ LED',
            'approved_items' => ['ซ่อมระบบไฟส่องสว่างสัญญาณเตือน', 'เปลี่ยนหลอดไฟ LED'],
            'total_cost' => 8900,
            'status' => 'pending_director',
            'urgency' => 'ด่วนที่สุด',
            'date' => '31/08/2569 11:10 น.',
            'doc_date' => '31',
            'doc_month' => 'สิงหาคม',
            'doc_year' => '2569',
            'garage_name' => 'อู่ยะลาการช่าง (ศูนย์บริการมาตรฐาน)',
            'mechanic_name' => 'นายดำรงค์'
        ],
        [
            'id' => 'MNT-2026-001',
            'ticket_no' => 'MNT-2026-001',
            'car_id' => 'EV-10',
            'license_plate' => 'กค 9900 ยะลา',
            'driver_name' => 'นายรุสลัน สอเฮาะ',
            'reporter' => 'นายรุสลัน สอเฮาะ',
            'issues' => ['ซ่อมมอเตอร์ขับเคลื่อน', 'เช็คระบบควบคุมอิเล็กทรอนิกส์'],
            'issue' => 'ซ่อมมอเตอร์ขับเคลื่อน | เช็คระบบควบคุมอิเล็กทรอนิกส์',
            'approved_items' => ['ซ่อมมอเตอร์ขับเคลื่อน', 'เช็คระบบควบคุมอิเล็กทรอนิกส์'],
            'total_cost' => 22500,
            'status' => 'pending_director',
            'urgency' => 'ด่วน',
            'date' => '30/08/2569 09:00 น.',
            'doc_date' => '30',
            'doc_month' => 'สิงหาคม',
            'doc_year' => '2569',
            'garage_name' => 'ศูนย์บริการมอเตอร์ไฟฟ้า ยะลา',
            'mechanic_name' => 'นายวิชาญ'
        ]
    ];

    $mergedMap = [];
    foreach ($defaultTickets as $dt) {
        $mergedMap[$dt['id']] = $dt;
    }
    if (is_array($tickets)) {
        foreach ($tickets as $t) {
            if (isset($t['id'])) {
                $mergedMap[$t['id']] = array_merge($mergedMap[$t['id']] ?? [], $t);
            }
        }
    }
    $tickets = array_values($mergedMap);
    Cache::forever('global_storage_yru_maintenance_tickets_v3', json_encode($tickets));

    return response()->json(['status' => 'success', 'data' => $tickets]);
});

Route::post('/api/maintenance/report', function (Request $request) {
    date_default_timezone_set('Asia/Bangkok');
    $car_id = $request->input('car_id', 'EV-01');
    $issue = $request->input('issue', 'ปัญหาทั่วไป');
    $parts = $request->input('parts', 'ระบบทั่วไป');
    $urgency = $request->input('urgency', 'ด่วน');
    $details = $request->input('details', '');
    $reporter = $request->input('reporter', 'พนักงานขับรถ');
    $plate = $request->input('plate', 'กค 9012 ยะลา');
    $images = $request->input('images', []);

    // 1. อ่านรายการแจ้งซ่อมทั้งหมดที่มีอยู่ในระบบ Cache / Storage
    $rawStorage = Cache::get('global_storage_yru_maintenance_tickets_v3', null);
    $tickets = [];
    if ($rawStorage) {
        $tickets = is_string($rawStorage) ? json_decode($rawStorage, true) : $rawStorage;
    }
    if (!is_array($tickets) || empty($tickets)) {
        $tickets = Cache::get('maintenance_tickets', []);
    }
    if (!is_array($tickets)) $tickets = [];

    // กรองรายการจำลอง (Mock tickets) ออก
    $mockIds = ['MNT-101', 'MNT-102', 'MNT-103', 'MNT-104', 'MNT-005', '101', '102', '103', '104', '005'];
    $tickets = array_values(array_filter($tickets, function($t) use ($mockIds) {
        return !in_array($t['id'] ?? '', $mockIds);
    }));

    // ตรวจสอบว่ารถคันนี้มีคำขอแจ้งซ่อมที่รอการอนุมัติอยู่แล้วหรือไม่ (1 คัน ต่อ 1 เคสค้างอนุมัติ)
    foreach ($tickets as $t) {
        $c = $t['car_id'] ?? $t['tram_id'] ?? '';
        $st = strtolower(trim($t['status'] ?? ''));
        if ($c === $car_id && ($st === 'pending' || $st === 'รออนุมัติ')) {
            return response()->json([
                'status' => 'error',
                'message' => "ขบวนรถ {$car_id} มีคำขอแจ้งซ่อมที่รอการอนุมัติอยู่แล้ว (#{$t['id']})"
            ], 422);
        }
    }

    // 2. ค้นหารหัสตัวเลขที่สูงสุดจากทุกรายการ (รวมใน Database และ Cache) เพื่อรันลำดับต่อเนื่อง
    $maxNum = 0;
    foreach ($tickets as $t) {
        if (isset($t['id'])) {
            preg_match('/(\d+)/', (string)$t['id'], $m);
            if (!empty($m[1])) {
                $maxNum = max($maxNum, (int)$m[1]);
            }
        }
    }

    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('maintenances')) {
            $dbCodes = \App\Models\Maintenance::pluck('maintenance_code');
            foreach ($dbCodes as $code) {
                preg_match('/(\d+)/', (string)$code, $m);
                if (!empty($m[1])) {
                    $maxNum = max($maxNum, (int)$m[1]);
                }
            }
        }
    } catch (\Exception $e) {}

    // 3. กำหนดรหัสอัตโนมัติ เช่น MNT-0001, MNT-0002 ต่อเนื่องเสมอ
    $nextNum = $maxNum + 1;
    $ticketCode = sprintf('MNT-%04d', $nextNum);

    $urgencyColor = 'bg-amber-100 text-amber-800';
    if ($urgency === 'วิกฤต') $urgencyColor = 'bg-rose-100 text-rose-800';
    else if ($urgency === 'ปกติ') $urgencyColor = 'bg-emerald-100 text-emerald-800';

    $newTicket = [
        'id' => $ticketCode,
        'ticket_id' => $ticketCode,
        'tram_id' => $car_id,
        'car_id' => $car_id,
        'plate' => $plate ?: 'กค 9012 ยะลา',
        'issue' => $issue,
        'parts' => $parts,
        'details' => $details,
        'images' => $images,
        'reporter' => str_contains($reporter, 'คนขับ') ? $reporter : ($reporter . ' (คนขับรถ)'),
        'date' => date('d/m/Y H:i') . ' น.',
        'urgency' => $urgency,
        'urgency_color' => $urgencyColor,
        'status' => 'pending',
        'approver' => '-',
        'remarks' => ''
    ];

    array_unshift($tickets, $newTicket);

    // 4. บันทึกลง Cache & Sync System เพื่อส่งข้อมูลสดไปยัง Admin / Executive
    Cache::forever('global_storage_yru_maintenance_tickets_v3', json_encode($tickets));
    Cache::put('maintenance_tickets', $tickets, 86400 * 30);

    // 5. บันทึกลงฐานข้อมูลตาราง `maintenances` (Database Insertion)
    try {
        if (\Illuminate\Support\Facades\Schema::hasTable('maintenances')) {
            $user = \App\Models\User::first();
            $userId = $user ? $user->user_id : 'USR-000001';

            if (\Illuminate\Support\Facades\Schema::hasTable('electric_trains')) {
                \App\Models\ElectricTrain::firstOrCreate(
                    ['skytrain_code' => $car_id],
                    [
                        'skytrain_name' => 'ขบวน ' . $car_id,
                        'passenger_capacity' => 8,
                        'status' => 'พร้อมใช้งาน'
                    ]
                );
            }

            \App\Models\Maintenance::updateOrCreate(
                ['maintenance_code' => $ticketCode],
                [
                    'user_id' => $userId,
                    'skytrain_code' => $car_id,
                    'repair_details' => $issue . ($details ? ' (' . $details . ')' : '') . ($parts ? " [หมวด: {$parts}]" : ''),
                    'repair_notification_date' => date('Y-m-d'),
                    'repair_status' => 'pending'
                ]
            );
        }
    } catch (\Exception $e) {
        \Illuminate\Support\Facades\Log::warning('DB Maintenance insert: ' . $e->getMessage());
    }

    // 6. อัปเดตสถิติความปลอดภัย
    $safetyInspections = (int) Cache::get('today_safety_inspections_count', 0);
    Cache::put('today_safety_inspections_count', $safetyInspections + 1, 86400);

    $safetyLogs = Cache::get('safety_logs_data', []);
    array_unshift($safetyLogs, [
        'time' => date('H:i') . ' น.',
        'target' => $car_id,
        'info' => "แจ้งเสีย (#{$ticketCode}): {$issue}" . ($details ? " ({$details})" : ''),
        'status' => 'รอการตรวจสอบ',
        'actionRequired' => true
    ]);
    Cache::put('safety_logs_data', $safetyLogs, 86400);

    return response()->json([
        'status' => 'success',
        'ticket_id' => $ticketCode,
        'ticket' => $newTicket,
        'tickets' => $tickets
    ]);
});

Route::post('/api/maintenance/requests/{id}/submit-quotation', function (Request $request, $id) {
    date_default_timezone_set('Asia/Bangkok');
    $rawStorage = Cache::get('global_storage_yru_maintenance_tickets_v3', null);
    $tickets = [];
    if ($rawStorage) {
        $tickets = is_string($rawStorage) ? json_decode($rawStorage, true) : $rawStorage;
    }
    if (!is_array($tickets)) $tickets = [];

    $targetIdx = -1;
    foreach ($tickets as $idx => $t) {
        $tId = (string)($t['ticket_no'] ?? $t['id'] ?? '');
        if ($tId === (string)$id) {
            $targetIdx = $idx;
            break;
        }
    }

    if ($targetIdx !== -1) {
        $tickets[$targetIdx]['status'] = 'pending_director';
        $tickets[$targetIdx]['garage_to'] = $request->input('garage_to');
        $tickets[$targetIdx]['garage_project'] = $request->input('garage_project');
        $tickets[$targetIdx]['quotation_no'] = $request->input('quotation_no');
        $tickets[$targetIdx]['quotation_date'] = $request->input('quotation_date');
        $tickets[$targetIdx]['garage_name'] = $request->input('garage_name');
        $tickets[$targetIdx]['garage_manager'] = $request->input('garage_manager');
        $tickets[$targetIdx]['mechanic_name'] = $request->input('mechanic_name');
        $tickets[$targetIdx]['quotation_items'] = $request->input('items', []);
        $tickets[$targetIdx]['subtotal'] = $request->input('subtotal');
        $tickets[$targetIdx]['vat'] = $request->input('vat');
        $tickets[$targetIdx]['total_cost'] = $request->input('total_cost');
        $tickets[$targetIdx]['thai_baht_text'] = $request->input('thai_baht_text');
        $tickets[$targetIdx]['estimated_days'] = $request->input('estimated_days');
        if ($request->filled('signature_image')) {
            $tickets[$targetIdx]['signature_image'] = $request->input('signature_image');
            $tickets[$targetIdx]['mechanic_signature'] = $request->input('signature_image');
        }
        Cache::forever('global_storage_yru_maintenance_tickets_v3', json_encode($tickets));
        Cache::put('maintenance_tickets', $tickets, 86400 * 30);
    }

    return response()->json(['status' => 'success', 'message' => 'บันทึกใบเสนอราคาสำเร็จ']);
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
    
    $targetCar = null;
    foreach ($tickets as &$ticket) {
        if ($ticket['id'] == $id) {
            $ticket['status'] = $status;
            $targetCar = $ticket['car_id'] ?? $ticket['tram_id'] ?? null;
        }
    }
    Cache::put('maintenance_tickets', $tickets, 86400);

    // ถ้ายืนยันว่าซ่อมสำเร็จแล้ว ให้ลดคิวรถไฟฟ้าที่รอตรวจลง และคืนสถานะรถ
    if ($status === 'completed') {
        $safetyInspections = (int) Cache::get('today_safety_inspections_count', 1);
        if ($safetyInspections > 0) {
            Cache::put('today_safety_inspections_count', $safetyInspections - 1, 86400);
        }
        try {
            if ($targetCar && \Illuminate\Support\Facades\Schema::hasTable('electric_trains')) {
                \App\Models\ElectricTrain::where('skytrain_code', $targetCar)
                    ->update(['status' => 'พร้อมใช้งาน']);
            }
        } catch (\Exception $e) {}
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

// --- 5-Step Maintenance & Budget Approval Workflow APIs ---
Route::get('/api/maintenance/requests', [\App\Http\Controllers\MaintenanceController::class, 'index']);
Route::get('/api/maintenance/requests/statistics', [\App\Http\Controllers\MaintenanceController::class, 'getStatistics']);
Route::get('/api/maintenance/requests/{id}', [\App\Http\Controllers\MaintenanceController::class, 'show']);
Route::post('/api/maintenance/requests', [\App\Http\Controllers\MaintenanceController::class, 'storeDriverRequest']);
Route::post('/api/maintenance/requests/{id}/supervisor-verify', [\App\Http\Controllers\MaintenanceController::class, 'verifySupervisor']);
Route::post('/api/maintenance/requests/{id}/submit-quotation', [\App\Http\Controllers\MaintenanceController::class, 'submitQuotation']);
Route::post('/api/maintenance/requests/{id}/director-approve', [\App\Http\Controllers\MaintenanceController::class, 'directorApprove']);
Route::post('/api/maintenance/requests/{id}/complete', [\App\Http\Controllers\MaintenanceController::class, 'completeRepair']);
Route::delete('/api/maintenance/requests/{id}', [\App\Http\Controllers\MaintenanceController::class, 'destroy']);

