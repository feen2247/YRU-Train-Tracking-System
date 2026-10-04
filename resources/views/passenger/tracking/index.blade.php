@php
    $authUser = null;
    try {
        if (class_exists('Illuminate\Support\Facades\Auth') && \Illuminate\Support\Facades\Auth::check()) {
            $authUser = \Illuminate\Support\Facades\Auth::user();
        }
    } catch (\Throwable $e) {
        $authUser = null;
    }
    $authEmail = strtolower($authUser->email ?? '');
    $authUsername = strtolower($authUser->username ?? '');
    $authEmpId = strtolower($authUser->employee_id ?? '');

    // Read dynamic driver vehicle assignments from Cache first
    $cachedAssignments = \Illuminate\Support\Facades\Cache::get('driver_vehicle_assignments', []);
    $dynamicDriverCarMap = [];
    foreach ($cachedAssignments as $cCode => $info) {
        if (!empty($info['driver_id'])) {
            $dynamicDriverCarMap[strtolower($info['driver_id'])] = $cCode;
        }
        if (!empty($info['driver_name'])) {
            $dynamicDriverCarMap[strtolower($info['driver_name'])] = $cCode;
            $parts = explode('@', strtolower($info['driver_name']));
            if (!empty($parts[0])) $dynamicDriverCarMap[$parts[0]] = $cCode;
        }
    }

    $driverCarMap = array_merge([
        'asmee' => 'EV-01', 'asmee@yru.ac.th' => 'EV-01', '69003' => 'EV-01',
        'arfan' => 'EV-02', 'arfan@yru.ac.th' => 'EV-02', '69004' => 'EV-02',
        'sufiyan' => 'EV-03', 'sufiyan@yru.ac.th' => 'EV-03', '69005' => 'EV-03',
        'usman' => 'EV-04', 'usman@yru.ac.th' => 'EV-04', '69006' => 'EV-04',
        'badri' => 'EV-05', 'badri@yru.ac.th' => 'EV-05', '69007' => 'EV-05',
        'torik' => 'EV-06', 'torik@yru.ac.th' => 'EV-06', '69008' => 'EV-06',
        'somwang' => 'EV-07', 'somwang@yru.ac.th' => 'EV-07', '69009' => 'EV-07',
        'somjai' => 'EV-08', 'somjai@yru.ac.th' => 'EV-08', 'somjal@yru.ac.th' => 'EV-08', '69010' => 'EV-08',
        'kitti' => 'EV-09', 'kitti@yru.ac.th' => 'EV-09', '69011' => 'EV-09',
        'ruslan' => 'EV-10', 'ruslan@yru.ac.th' => 'EV-10', '69012' => 'EV-10',
    ], $dynamicDriverCarMap);

    $carDriverEmailMap = [
        'EV-01' => 'asmee@yru.ac.th',
        'EV-02' => 'arfan@yru.ac.th',
        'EV-03' => 'sufiyan@yru.ac.th',
        'EV-04' => 'usman@yru.ac.th',
        'EV-05' => 'badri@yru.ac.th',
        'EV-06' => 'torik@yru.ac.th',
        'EV-07' => 'somwang@yru.ac.th',
        'EV-08' => 'somjai@yru.ac.th',
        'EV-09' => 'kitti@yru.ac.th',
        'EV-10' => 'ruslan@yru.ac.th',
    ];

    $mappedCar = $driverCarMap[$authEmail] ?? ($driverCarMap[$authUsername] ?? ($driverCarMap[$authEmpId] ?? 'EV-01'));
    $reqCar = request('car') ?: request('car_id');
    if ($reqCar) {
        $formattedReqCar = str_starts_with($reqCar, 'EV-') ? $reqCar : ((int)$reqCar < 10 ? 'EV-0' . (int)$reqCar : 'EV-' . (int)$reqCar);
    } else {
        $formattedReqCar = $mappedCar;
    }
    $initialDriverEmail = $carDriverEmailMap[$formattedReqCar] ?? ($authUser->email ?? 'asmee@yru.ac.th');
@endphp
<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ระบบติดตามเส้นทางการเดินรถไฟฟ้ามหาวิทยาลัยราชภัฏยะลา - พนักงานขับรถ</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v={{ time() }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.png') }}?v={{ time() }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}?v={{ time() }}">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script>
        // ระบบความปลอดภัย: Client-side Auth Guard & Sync
        (function() {
            try {
                @if(Auth::check())
                    var authObj = {
                        user_id: "{{ Auth::user()->employee_id ?: Auth::user()->user_id }}",
                        emp_id: "{{ Auth::user()->employee_id }}",
                        name: "{{ addslashes(Auth::user()->name) }}",
                        email: "{{ Auth::user()->email }}",
                        username: "{{ Auth::user()->username }}",
                        role: "{{ Auth::user()->user_role }}"
                    };
                    localStorage.setItem('yru_user_login', JSON.stringify(authObj));
                    sessionStorage.setItem('yru_user_login', JSON.stringify(authObj));
                    return;
                @endif

                var rawUser = localStorage.getItem('yru_user_login') || sessionStorage.getItem('yru_user_login');
                if (!rawUser) {
                    window.location.replace("{{ url('/') }}");
                    return;
                }
                var u = JSON.parse(rawUser);
                var r = ((u && u.role) || (u && u.user_role) || '').toLowerCase();
                var isValidRole = r.includes('driver') || 
                                  r.includes('ขับ') || 
                                  r.includes('operator') || 
                                  r.includes('admin') || 
                                  r.includes('ผู้ดูแลระบบ');
                if (!isValidRole) {
                    window.location.replace("{{ url('/') }}");
                    return;
                }
            } catch (e) {
                @if(!Auth::check())
                    window.location.replace("{{ url('/') }}");
                @endif
            }
        })();
    </script>
    <script src="/api/storage/init?v={{ time() }}"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Inter', 'Sarabun', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Sarabun:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    <!-- Leaflet CSS & JS (Dual CDN Fallback: Cloudflare cdnjs + unpkg) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css" />
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
    <script>
        if (typeof L === 'undefined') {
            document.write('<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"><\/script>');
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { 
            box-sizing: border-box; 
            font-family: 'Inter', 'Sarabun', sans-serif;
        }
        #map, .map-bg, .leaflet-container {
            width: 100% !important;
            height: 100% !important;
            min-height: 450px !important;
            background: #fdf2f8 !important;
        }
        body, button, input, select, textarea, div, span, p, a, h1, h2, h3, h4, h5, h6, label, td, th, .leaflet-container, .leaflet-popup-content-wrapper, .leaflet-popup-content, .leaflet-tooltip, .swal2-popup, .swal2-title, .swal2-content {
            font-family: 'Inter', 'Sarabun', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        }
        .font-kanit, .font-sarabun {
            font-family: 'Inter', 'Sarabun', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        }
        /* Protect FontAwesome Icons from being overridden by fonts */
        .fa, .fas, .far, .fab, .fa-solid, .fa-regular, .fa-brands, [class*="fa-"] {
            font-family: 'Font Awesome 6 Free', 'Font Awesome 6 Brands', 'Font Awesome 5 Free', sans-serif !important;
        }
        body { overflow: hidden; height: 100vh; display: flex; flex-direction: column; }
        .map-bg { position: relative; flex: 1; overflow: hidden; min-height: 0; }
        #map {
            width: 100% !important;
            height: 100% !important;
            min-height: 400px !important;
            background-color: #f8fafc !important;
        }
        .leaflet-container {
            width: 100% !important;
            height: 100% !important;
            background-color: #f8fafc !important;
        }

        .status-item {
            background: #f8fafc;
            border-color: #e2e8f0;
            color: #64748b;
            font-size: 0.8rem;
            font-weight: 500;
        }
        .status-item.active[data-type="normal"] {
            background: rgba(16, 185, 129, 0.1);
            border-color: #10b981;
            color: #059669;
            font-weight: bold;
        }
        .status-item.active[data-type="pause"] {
            background: rgba(245, 158, 11, 0.1);
            border-color: #f59e0b;
            color: #d97706;
            font-weight: bold;
        }
        .status-item.active[data-type="broken"] {
            background: rgba(239, 68, 68, 0.1);
            border-color: #ef4444;
            color: #dc2626;
            font-weight: bold;
        }

        .custom-leaflet-icon { background: none !important; border: none !important; box-shadow: none !important; }
        .station-dot, .station-dot-red, .station-dot-green { animation: none !important; }
        .shuttle-float { animation: none !important; }
        .subtle-pulse { animation: none !important; display: none !important; }

        .notif-badge {
            background: #ef4444;
            color: white;
            border-radius: 50%;
            width: 32px;
            height: 32px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 1rem;
            font-weight: 900;
            border: 3px solid #ffffff;
            box-shadow: 0 4px 10px rgba(0,0,0,0.15);
            animation: pulse 1.5s infinite;
        }

        @keyframes pulse {
            0% { transform: scale(1); }
            50% { transform: scale(1.15); }
            100% { transform: scale(1); }
        }

        @keyframes bounceSubtle {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-4px); }
        }
        .animate-bounce-subtle {
            animation: bounceSubtle 2.2s ease-in-out infinite;
        }
    </style>
</head>
<body class="bg-pink-100 text-slate-800 overflow-x-hidden min-h-screen">

<div class="flex flex-col min-h-screen bg-pink-100 overflow-x-hidden">
    @include('passenger.tracking.partials.header')

    <!-- Main Container: Grid layout matching home/index.blade.php -->
    <div class="grid grid-cols-1 md:grid-cols-12 flex-1 p-4 md:p-6 gap-4 md:gap-6 min-h-0">
        <!-- Left Column: Driver Controls -->
        <div class="order-2 md:order-1 md:col-span-4 flex flex-col space-y-4 overflow-y-auto">
            
            <div class="space-y-4">
                <!-- Logged-in Driver & Vehicle Info Card -->
                <div class="bg-white border border-pink-200/80 rounded-2xl p-4 shadow-sm space-y-2.5">
                    <div class="flex items-center justify-between">
                        <span class="text-[10px] font-bold text-pink-600 uppercase tracking-wider flex items-center gap-1.5">
                            <i class="fas fa-id-badge text-pink-500"></i> ข้อมูลรถประจำทางและพนักงานขับ
                        </span>
                        <span class="text-xs text-pink-700 bg-pink-100/80 border border-pink-200 px-3 py-1 rounded-full font-bold flex items-center gap-1.5 shadow-sm">
                            <i class="fas fa-sync-alt text-pink-500 text-[10px]"></i>
                            <span>รอบที่ <strong id="card-round-number" class="text-pink-800 text-xs font-black">0</strong></span>
                        </span>
                    </div>
                    
                    <div class="flex items-center justify-between gap-3 pt-0.5">
                        <div class="flex items-center gap-3 min-w-0">
                            <div class="w-11 h-11 rounded-2xl flex items-center justify-center text-white text-base font-black shadow-md ring-2 ring-white shrink-0" id="card-vehicle-badge-circle" style="background: #3B82F6;">
                                1
                            </div>
                            <div class="min-w-0">
                                <div class="flex items-center gap-1.5">
                                    <h3 class="text-base font-black text-slate-800 truncate" id="card-car-code">EV-01</h3>
                                    <span class="text-xs text-slate-500 font-mono shrink-0" id="card-car-plate">(กค 1234 ยะลา)</span>
                                </div>
                                <p class="text-xs text-slate-600 font-semibold flex items-center gap-1.5 mt-0.5 truncate">
                                    <i class="fas fa-user-tie text-pink-500 shrink-0"></i>
                                    <span class="text-slate-800 font-bold truncate" id="card-driver-name">นายอัสมี มูเล็ง</span>
                                </p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Clean Minimal Driver Navigator Card (100% Match with Clean Favorite Design) -->
                <div id="next-stop-navigator-card" class="bg-white border border-pink-200/90 rounded-2xl p-4 shadow-sm space-y-3.5 transition-all duration-300 font-kanit">
                    <!-- Target Stop Title -->
                    <div class="border-b border-pink-100 pb-2 flex items-center justify-between">
                        <h2 id="next-stop-name" class="text-xl font-extrabold text-slate-800 tracking-tight">จุดจอด 1 หน้าอาคารที่พักบุคลากร</h2>
                        <span class="text-[10px] text-pink-600 font-bold bg-pink-50 border border-pink-100 px-2 py-0.5 rounded-full" id="next-stop-sequence">STOP 1/7</span>
                    </div>

                    <!-- Dynamic Call Request Alert Card (Full Detail - Shown when passenger calls this car) -->
                    <!-- Simple compact banner kept for backward-compat, hidden -->
                    <div id="on-demand-call-banner" class="hidden" aria-hidden="true">
                        <span id="call-banner-title"></span>
                        <span id="call-passenger-num">1</span>
                        <span id="on-demand-passenger-count"></span>
                    </div>

                    <!-- ===== Full Passenger Call Card (Smart Route Queue Carousel) ===== -->
                    <div id="next-stop-alert" class="hidden rounded-2xl p-4 flex flex-col gap-3 shadow-lg border border-emerald-400 bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 text-white transition-all duration-300">
                        <!-- Header Badge & Queue Navigation Controls -->
                        <div class="flex items-center justify-end gap-2">
                            <span id="call-alert-title-badge" class="hidden"></span>
                            <div class="flex items-center gap-1.5 shrink-0">
                                <!-- Queue Controller (Shown when multiple passengers call) -->
                                <div id="call-queue-controller" class="hidden items-center gap-1 bg-black/30 px-2 py-0.5 rounded-full text-xs font-bold border border-white/20">
                                    <button type="button" onclick="prevCallQueue(event)" class="w-5 h-5 rounded-full hover:bg-white/20 flex items-center justify-center transition cursor-pointer text-white" title="คิวก่อนหน้า">
                                        <i class="fas fa-chevron-left text-[9px]"></i>
                                    </button>
                                    <span id="call-queue-indicator" class="text-[10px] font-black text-amber-200 px-1 whitespace-nowrap">1/1</span>
                                    <button type="button" onclick="nextCallQueue(event)" class="w-5 h-5 rounded-full hover:bg-white/20 flex items-center justify-center transition cursor-pointer text-white" title="คิวถัดไป">
                                        <i class="fas fa-chevron-right text-[9px]"></i>
                                    </button>
                                </div>
                                <button type="button" onclick="cancelPassengerCall()" class="w-6 h-6 rounded-full bg-white/20 hover:bg-white/40 flex items-center justify-center text-white text-xs transition cursor-pointer" title="ยกเลิกเคสนี้">
                                    <i class="fas fa-times"></i>
                                </button>
                            </div>
                        </div>

                        <!-- Route Journey Info (2 ช่องชัดเจน: ผู้โดยสารเรียกที่ไหน & ให้ไปส่งที่ไหน) -->
                        <div class="space-y-2">
                            <!-- ช่องที่ 1: ผู้โดยสารเรียกที่ไหน (จุดรับ) -->
                            <div class="bg-black/25 rounded-xl p-3 border border-emerald-300/40 flex items-start gap-2.5 shadow-xs">
                                <div class="w-7 h-7 rounded-lg bg-emerald-500 text-white flex items-center justify-center shrink-0 shadow-sm text-xs mt-0.5">
                                    <i class="fas fa-map-marker-alt"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-[10px] font-black uppercase tracking-wider text-emerald-200 mb-0.5 flex items-center gap-1">
                                        <span>📍 ผู้โดยสารเรียกที่นี่ (จุดรับ):</span>
                                    </div>
                                    <div class="text-sm font-black leading-snug text-white break-words" id="call-alert-station">-</div>
                                </div>
                            </div>

                            <!-- ช่องที่ 2: ให้ไปส่งที่ไหน (จุดหมายปลายทาง) -->
                            <div class="bg-black/25 rounded-xl p-3 border border-cyan-300/40 flex items-start gap-2.5 shadow-xs">
                                <div class="w-7 h-7 rounded-lg bg-blue-500 text-white flex items-center justify-center shrink-0 shadow-sm text-xs mt-0.5">
                                    <i class="fas fa-flag-checkered"></i>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="text-[10px] font-black uppercase tracking-wider text-cyan-200 mb-0.5 flex items-center gap-1">
                                        <span>🏁 ให้ไปส่งที่นี่ (จุดหมายปลายทาง):</span>
                                    </div>
                                    <div class="text-sm font-black leading-snug text-white break-words" id="call-alert-destination">-</div>
                                </div>
                            </div>
                        </div>

                        <!-- Pax + Time -->
                        <div class="flex items-center justify-between bg-white/15 rounded-xl px-3 py-2">
                            <div class="flex items-center gap-1.5">
                                <i class="fas fa-users text-amber-300"></i>
                                <span class="text-xs font-bold">จำนวน: <span id="call-alert-pax" class="font-black text-amber-200">1</span> คน</span>
                            </div>
                            <div class="text-[10px] text-white/70 font-semibold">
                                <i class="fas fa-clock mr-1"></i><span id="call-alert-time">เมื่อสักครู่</span>
                            </div>
                        </div>

                        <!-- Queue Total Summary Bar (Removed per user request) -->
                        <div id="call-queue-summary-bar" class="hidden"></div>
                    </div>



                    <!-- Action Buttons Pair -->
                    <div id="driver-actions-grid" class="grid grid-cols-2 gap-2.5 pt-1 font-kanit">
                        <button id="btn-arrived-stop" disabled="disabled" onclick="handleArrivedAtStop()" class="bg-slate-200 text-slate-400 border border-slate-300 font-bold py-3.5 px-3 rounded-xl flex items-center justify-center gap-2 text-xs transition opacity-60 cursor-not-allowed pointer-events-none">
                            <i class="fas fa-check-circle text-sm"></i> ถึงจุดจอดแล้ว
                        </button>
                        <button id="btn-skip-stop" disabled="disabled" onclick="handleSkipStop()" class="bg-slate-100 text-slate-400 border border-slate-200 font-bold py-3.5 px-3 rounded-xl flex items-center justify-center gap-2 text-xs transition opacity-60 cursor-not-allowed pointer-events-none">
                            <i class="fas fa-forward text-sm"></i> ข้ามจุดจอด
                        </button>
                    </div>
                </div>

                <!-- Passenger Counter Card -->
                <div class="bg-white border border-pink-200/80 rounded-2xl p-4 shadow-sm space-y-3.5">
                    <div class="flex items-center justify-between border-b border-pink-100 pb-2.5">
                        <span class="text-xs font-semibold text-slate-500 tracking-wide uppercase flex items-center gap-1.5"><i class="fas fa-users text-blue-500"></i>นับจำนวนผู้โดยสารบนรถ</span>
                        <span class="text-[10px] text-blue-600 font-bold bg-blue-50 border border-blue-100 px-2.5 py-0.5 rounded-full" id="seats-percentage">0%</span>
                    </div>
                    
                    <div class="flex items-baseline justify-between">
                        <span class="text-xs text-slate-500 font-medium">ผู้โดยสารบนรถขณะนี้</span>
                        <span class="text-3xl font-extrabold text-slate-800"><span id="seats-occupied-display" class="text-blue-600">0</span> <span class="text-sm font-normal text-slate-500">/ <span id="seats-max-capacity">8</span> คน</span></span>
                    </div>

                    <!-- Progress Bar -->
                    <div class="w-full bg-slate-100 h-2 rounded-full overflow-hidden">
                        <div id="seats-progress-bar" class="bg-blue-600 h-full rounded-full transition-all duration-300" style="width: 0%;"></div>
                    </div>
                </div>

                <!-- Action Control Card: Break & Round Management -->
                <div class="bg-white border border-pink-200/80 rounded-2xl p-4 shadow-sm space-y-3.5">
                    <div class="flex items-center justify-between border-b border-pink-100 pb-2.5">
                        <span class="text-xs font-semibold text-slate-500 tracking-wide uppercase flex items-center gap-1.5"><i class="fas fa-tasks text-pink-500"></i>จัดการช่วงพัก & สิ้นสุดรอบ</span>
                        <span id="break-status-badge" class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-500 border border-slate-200">ยังไม่เริ่มงาน</span>
                    </div>
                    
                    <div class="grid grid-cols-3 gap-2.5">
                        <!-- Left Box: Start Work Button -->
                        <div id="start-work-container">
                            <button id="btn-start-work" onclick="startWorkAction()" class="w-full h-12 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold px-1.5 rounded-xl flex items-center justify-center gap-1.5 transition text-xs shadow-md shadow-emerald-600/20 font-kanit cursor-pointer ring-2 ring-emerald-400/40">
                                เริ่มงาน
                            </button>
                        </div>

                        <!-- Middle Box: Break Button -->
                        <div id="break-action-container">
                            <button id="btn-take-break" disabled="disabled" onclick="toggleBreakStatus(true)" class="w-full h-12 bg-amber-100 text-amber-500/70 border border-amber-200/60 font-bold px-1.5 rounded-xl flex items-center justify-center gap-1.5 text-xs font-kanit opacity-50 cursor-not-allowed pointer-events-none transition">
                                พัก
                            </button>
                        </div>

                        <!-- Right Box: End Work Button -->
                        <div id="round-action-container">
                            <button id="btn-end-work" disabled="disabled" onclick="showDailySummaryModal()" class="w-full h-12 bg-rose-100 text-rose-500/70 border border-rose-200/60 font-bold px-1.5 rounded-xl flex items-center justify-center gap-1.5 text-xs font-kanit opacity-50 cursor-not-allowed pointer-events-none transition">
                                เลิกงาน
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Emergency / Repair Card -->
                <div class="bg-white border border-pink-200/80 rounded-2xl p-4 shadow-sm space-y-4">
                    <div class="flex items-center border-b border-pink-100 pb-2.5">
                        <span class="text-xs font-semibold text-slate-500 tracking-wide uppercase flex items-center gap-1.5"><i class="fas fa-exclamation-triangle text-amber-500"></i>แจ้งเหตุ / แจ้งซ่อมบำรุง</span>
                    </div>
                    
                    <!-- Real-Time Maintenance Status for Driver -->
                    <div id="driverMaintenanceStatusContainer" class="space-y-2 empty:hidden"></div>

                    <div class="pt-1">
                        <button type="button" id="btn-report-issue" onclick="var m=document.getElementById('driverIssueReportModal'); if(m){ m.classList.remove('hidden'); m.style.display='flex'; } if(typeof openDriverIssueReportModal==='function'){ openDriverIssueReportModal(); }" class="w-full bg-amber-500 hover:bg-amber-600 active:scale-95 text-white font-bold py-3.5 px-4 rounded-xl flex items-center justify-center gap-2 transition text-sm shadow-md shadow-amber-500/20 cursor-pointer font-kanit"><i class="fas fa-wrench"></i> แจ้งซ่อมบำรุงรถไฟฟ้า</button>
                    </div>
                    <script>
                        (function() {
                            function bindReportBtn() {
                                var btn = document.getElementById('btn-report-issue');
                                if (btn && !btn._boundReport) {
                                    btn._boundReport = true;
                                    btn.addEventListener('click', function(e) {
                                        e.preventDefault();
                                        var m = document.getElementById('driverIssueReportModal');
                                        if (m) {
                                            m.classList.remove('hidden');
                                            m.style.display = 'flex';
                                        }
                                        if (typeof openDriverIssueReportModal === 'function') {
                                            try { openDriverIssueReportModal(); } catch(err) { console.warn(err); }
                                        }
                                    });
                                }
                            }
                            if (document.readyState === 'loading') {
                                document.addEventListener('DOMContentLoaded', bindReportBtn);
                            } else {
                                bindReportBtn();
                            }
                        })();
                    </script>
                </div>
            </div>
        </div>

        <!-- Right Column: Navigation Map (Matching Passenger Home Screen) -->
        <div class="order-1 md:order-2 md:col-span-8 relative flex flex-col min-h-[450px] md:min-h-[550px] overflow-hidden">
            <div id="map" class="map-bg flex-1 relative z-0 rounded-[2rem] border-2 border-pink-200 shadow-sm overflow-hidden w-full h-full min-h-[450px] md:min-h-[550px]" style="min-height:450px;">
                <!-- แผนที่ Legend (bottom-left) -->
                <div class="absolute bottom-5 left-5 backdrop-blur-md bg-white/85 px-4 py-3 rounded-2xl shadow-lg border border-pink-200 z-[1000]" id="mapLegendWrapper">
                    <div class="flex flex-col gap-2.5">
                        <div class="flex items-center gap-2.5">
                            <div class="w-6 h-0 border-t-2 border-dashed border-pink-500"></div>
                            <span class="text-[9.5px] text-slate-600 font-bold">เส้นทางเดินรถ</span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <div class="w-3.5 h-3.5 rounded-full border-2 border-pink-500 bg-white flex items-center justify-center">
                                <div class="w-1.5 h-1.5 bg-pink-500 rounded-full"></div>
                            </div>
                            <span class="text-[9.5px] text-slate-600 font-bold">จุดจอดรับ-ส่ง</span>
                        </div>
                    </div>
                </div>

            </div>
            <!-- Alert Badge inside Map corner -->
            <div id="notif-count" class="absolute top-8 right-8 z-20 notif-badge hidden">0</div>
        </div>
    </div>
</div>

<!-- Legacy alert box container hidden for compatibility -->
<div id="passenger-alert-box" style="display:none;"><div id="waiting-list"></div></div>

<script>
    // ===== Vehicle Color Palette (10 สีประจำรถ) =====
    const vehicleColors = [
        { bg: '#3B82F6', text: '#ffffff', name: 'Blue',    light: '#DBEAFE', border: '#93C5FD' },  // EV-01
        { bg: '#EC4899', text: '#ffffff', name: 'Pink',    light: '#FCE7F3', border: '#F9A8D4' },  // EV-02
        { bg: '#EA580C', text: '#ffffff', name: 'Orange',  light: '#FFEDD5', border: '#FB923C' },  // EV-03
        { bg: '#8B5CF6', text: '#ffffff', name: 'Purple',  light: '#EDE9FE', border: '#C4B5FD' },  // EV-04
        { bg: '#14B8A6', text: '#ffffff', name: 'Teal',    light: '#CCFBF1', border: '#5EEAD4' },  // EV-05
        { bg: '#EF4444', text: '#ffffff', name: 'Red',     light: '#FEE2E2', border: '#FCA5A5' },  // EV-06
        { bg: '#10B981', text: '#ffffff', name: 'Emerald', light: '#D1FAE5', border: '#A7F3D0' },  // EV-07
        { bg: '#6366F1', text: '#ffffff', name: 'Indigo',  light: '#E0E7FF', border: '#C7D2FE' },  // EV-08
        { bg: '#06B6D4', text: '#ffffff', name: 'Cyan',    light: '#CFFAFE', border: '#67E8F9' },  // EV-09
        { bg: '#F43F5E', text: '#ffffff', name: 'Rose',    light: '#FFE4E6', border: '#FDA4AF' }   // EV-10
    ];

    function getVehicleColor(idx) {
        return vehicleColors[idx % vehicleColors.length];
    }

    var GARAGE_LAT = 6.548900;
    var GARAGE_LNG = 101.291700;
    var GARAGE_STATION_ID = "GARAGE";
    let globalCarStatus = {};

    function isVehicleInGarage(tram) {
        if (!tram) return false;
        var carData = (typeof globalCarStatus !== 'undefined' && globalCarStatus[tram.id]) ? globalCarStatus[tram.id] : {};
        var rawStatus = (tram.status || carData.status || '').toString().trim();

        try {
            var rawLive = localStorage.getItem('yru_car_status_' + tram.id);
            if (rawLive) {
                var parsed = JSON.parse(rawLive);
                if (parsed.status) rawStatus = parsed.status.toString().trim();
                else if (parsed.driver_status) rawStatus = parsed.driver_status.toString().trim();
            }
        } catch(e) {}
        
        var isMaintenanceOrSuspended = 
            rawStatus === 'รถขัดข้อง' ||
            rawStatus === 'ระงับการใช้งาน' ||
            rawStatus.includes('ขัดข้อง') || 
            rawStatus.includes('ปรับปรุง') || 
            rawStatus.includes('ระงับ') || 
            rawStatus === 'MAINTENANCE' || 
            rawStatus === 'SUSPENDED';

        if (isMaintenanceOrSuspended) return true;

        var stationId = (tram.current_station_id || '').toString();
        var isAtGarage = (stationId === 'GARAGE' || stationId === 'GARAGE_STATION');
        return isAtGarage;
    }

    const defaultUsers = [
        { user_id: "USR001", name: "Admin YRU", email: "admin@yru.ac.th", role: "admin", status: "ใช้งาน" },
        { user_id: "USR002", name: "ดร.สมชาย เรียนดี", email: "somchai.r@yru.ac.th", role: "executive", status: "ใช้งาน" },
        { user_id: "USR003", name: "นายอัสมี มูเล็ง", email: "asmee@yru.ac.th", role: "driver", status: "ใช้งาน" },
        { user_id: "USR004", name: "นายอัรฟาน มะเระ", email: "arfan@yru.ac.th", role: "driver", status: "ใช้งาน" },
        { user_id: "USR005", name: "นายซูเฟียน มะโละ", email: "sufiyan@yru.ac.th", role: "driver", status: "ใช้งาน" },
        { user_id: "USR006", name: "นายอุสมาน สาและ", email: "usman@yru.ac.th", role: "driver", status: "ใช้งาน" },
        { user_id: "USR007", name: "นายบัดรี สาและ", email: "badri@yru.ac.th", role: "driver", status: "ใช้งาน" },
        { user_id: "USR008", name: "นายตอริก ลือแมะ", email: "torik@yru.ac.th", role: "driver", status: "ใช้งาน" },
        { user_id: "USR009", name: "นายสมหวัง ใจดี", email: "somwang@yru.ac.th", role: "driver", status: "ใช้งาน" },
        { user_id: "USR010", name: "นายสมใจ ใจดี", email: "somjai@yru.ac.th", role: "driver", status: "ใช้งาน" },
        { user_id: "USR011", name: "นายกิตติ ตั้งใจ", email: "kitti@yru.ac.th", role: "driver", status: "ใช้งาน" },
        { user_id: "USR012", name: "นายรุสลัน สอเฮาะ", email: "ruslan@yru.ac.th", role: "driver", status: "ใช้งาน" }
    ];

    const defaultTrams = [
        { id: "EV-01", name: "รถรางคันที่ 1", plate: "กค 1234 ยะลา", capacity_sit: 20, capacity_stand: 10, status: "พร้อมใช้งาน", gps_id: "GPS-EV01-YRU", route: "สาย 1: หน้ามรย-หอพัก", driver: "นายอัสมี มูเล็ง", driver_id: "USR003", battery: 85, image: "", coords: "6.549929, 101.291254", active_issue: "", updated_by: "admin@yru.ac.th", updated_at: "12 มี.ค. 2568", purchase_date: "2025-03-12", warranty: "5 ปี (สิ้นสุด 12 มีนาคม 2573)", supplier: "บริษัท ยะลายานยนต์ อีวี จำกัด (โทร. 073-123456)", maintenance: [{ date: "05/07/2569", detail: "เช็กระยะระบบเบรก และทดสอบแบตเตอรี่่ (ผลการทดสอบ: ผ่าน)", technician: "ช่างประสิทธิ์" }, { date: "28/06/2569", detail: "เปลี่ยนผ้าเบรกกหน้า-หลัง และเปลี่ยนยางรถรางใหม่ 4 ล้อ", technician: "ช่างสมคิด" }] },
        { id: "EV-02", name: "รถรางคันที่ 2", plate: "กค 5678 ยะลา", capacity_sit: 16, capacity_stand: 8, status: "พร้อมใช้งาน", gps_id: "GPS-EV02-YRU", route: "สาย 2: วงเวียน-คณะวิทยาศาสตร์", driver: "นายอัรฟาน มะเระ", driver_id: "USR004", battery: 92, image: "", coords: "6.549100, 101.290467", active_issue: "", updated_by: "admin@yru.ac.th", updated_at: "12 มี.ค. 2568", purchase_date: "2025-03-12", warranty: "5 ปี (สิ้นสุด 12 มีนาคม 2573)", supplier: "บริษัท ยะลายานยนต์ อีวี จำกัด (โทร. 073-123456)", maintenance: [] },
        { id: "EV-03", name: "รถรางคันที่ 3", plate: "กค 9012 ยะลา", capacity_sit: 18, capacity_stand: 10, status: "พร้อมใช้งาน", gps_id: "GPS-EV03-YRU", route: "สาย 1: หน้ามรย-หอพัก", driver: "นายซูเฟียน มะโละ", driver_id: "USR005", battery: 78, image: "", coords: "6.547835, 101.289502", active_issue: "", updated_by: "admin@yru.ac.th", updated_at: "12 มี.ค. 2568", purchase_date: "2025-03-12", warranty: "5 ปี (สิ้นสุด 12 มีนาคม 2573)", supplier: "บริษัท ยะลายานยนต์ อีวี จำกัด (โทร. 073-123456)", maintenance: [] },
        { id: "EV-04", name: "รถรางคันที่ 4", plate: "กค 3456 ยะลา", capacity_sit: 18, capacity_stand: 10, status: "พร้อมใช้งาน", gps_id: "GPS-EV04-YRU", route: "สาย 1: หน้ามรย-หอพัก", driver: "นายอุสมาน สาและ", driver_id: "USR006", battery: 78, image: "", coords: "6.547224, 101.289471", active_issue: "", updated_by: "admin@yru.ac.th", updated_at: "12 มี.ค. 2568", purchase_date: "2024-05-20", warranty: "3 ปี (สิ้นสุด 20 พฤษภาคม 2570)", supplier: "บริษัท ยะลายานยนต์ อีวี จำกัด (โทร. 081-2345678)", maintenance: [{ date: "04/07/2569", detail: "ตรวจเช็กระบบไฟ สัญญาณแต๊ก และไฟหน้า-ไฟเลี้ยวรถคัน (ผ่านเกณฑ์)", technician: "ช่างวิรัตน์" }] },
        { id: "EV-05", name: "รถรางคันที่ 5", plate: "กค 7890 ยะลา", capacity_sit: 20, capacity_stand: 10, status: "พร้อมใช้งาน", gps_id: "GPS-EV05-YRU", route: "สาย 2: วงเวียน-คณะวิทยาศาสตร์", driver: "นายบัดรี สาและ", driver_id: "USR007", battery: 95, image: "", coords: "6.547311, 101.288880", active_issue: "", updated_by: "admin@yru.ac.th", updated_at: "12 มี.ค. 2568", purchase_date: "2025-01-10", warranty: "3 ปี (สิ้นสุด 10 มกราคม 2571)", supplier: "บริษัท ยะลายานยนต์ อีวี จำกัด", maintenance: [] },
        { id: "EV-06", name: "รถรางคันที่ 6", plate: "กค 1122 ยะลา", capacity_sit: 16, capacity_stand: 8, status: "พร้อมใช้งาน", gps_id: "GPS-EV06-YRU", route: "สาย 3: ประตููหลังมอ-หอประชุม", driver: "นายตอริก ลือแมะ", driver_id: "USR008", battery: 89, image: "", coords: "6.548822, 101.288523", active_issue: "", updated_by: "admin@yru.ac.th", updated_at: "12 มี.ค. 2568", purchase_date: "2025-02-15", warranty: "3 ปี (สิ้นสุด 15 กุมภาพันธ์ 2571)", supplier: "บริษัท ยะลายานยนต์ อีวี จำกัด", maintenance: [] },
        { id: "EV-07", name: "รถรางคันที่ 7", plate: "กค 3344 ยะลา", capacity_sit: 18, capacity_stand: 8, status: "พร้อมใช้งาน", gps_id: "GPS-EV07-YRU", route: "สาย 1: หน้ามรย-หอพัก", driver: "นายสมหวัง ใจดี", driver_id: "USR009", battery: 82, image: "", coords: "6.549225, 101.289286", active_issue: "", updated_by: "admin@yru.ac.th", updated_at: "12 มี.ค. 2568", purchase_date: "2025-03-01", warranty: "3 ปี (สิ้นสุด 1 มีนาคม 2571)", supplier: "บริษัท ยะลายานยนต์ อีวี จำกัด", maintenance: [] },
        { id: "EV-08", name: "รถรางคันที่ 8", plate: "กค 5566 ยะลา", capacity_sit: 20, capacity_stand: 10, status: "พร้อมใช้งาน", gps_id: "GPS-EV08-YRU", route: "สาย 2: วงเวียน-คณะวิทยาศาสตร์", driver: "นายสมใจ ใจดี", driver_id: "USR010", battery: 90, image: "", coords: "6.549929, 101.291254", active_issue: "", updated_by: "admin@yru.ac.th", updated_at: "12 มี.ค. 2568", purchase_date: "2025-03-20", warranty: "3 ปี (สิ้นสุด 20 มีนาคม 2571)", supplier: "บริษัท ยะลายานยนต์ อีวี จำกัด", maintenance: [] },
        { id: "EV-09", name: "รถรางคันที่ 9", plate: "กค 7788 ยะลา", capacity_sit: 16, capacity_stand: 8, status: "พร้อมใช้งาน", gps_id: "GPS-EV09-YRU", route: "สาย 3: ประตููหลังมอ-หอประชุม", driver: "นายกิตติ ตั้งใจ", driver_id: "USR011", battery: 87, image: "", coords: "6.547835, 101.289502", active_issue: "", updated_by: "admin@yru.ac.th", updated_at: "12 มี.ค. 2568", purchase_date: "2025-04-05", warranty: "3 ปี (สิ้นสุด 5 เมษายน 2571)", supplier: "บริษัท ยะลายานยนต์ อีวี จำกัด", maintenance: [] },
        { id: "EV-10", name: "รถรางคันที่ 10", plate: "กค 9900 ยะลา", capacity_sit: 18, capacity_stand: 10, status: "พร้อมใช้งาน", gps_id: "GPS-EV10-YRU", route: "สาย 1: หน้ามรย-หอพัก", driver: "นายรุสลัน สอเฮาะ", driver_id: "USR012", battery: 91, image: "", coords: "6.547311, 101.288880", active_issue: "", updated_by: "admin@yru.ac.th", updated_at: "12 มี.ค. 2568", purchase_date: "2025-05-10", warranty: "3 ปี (สิ้นสุด 10 พฤษภาคม 2571)", supplier: "บริษัท ยะลายานยนต์ อีวี จำกัด", maintenance: [] }
    ];

    function getStorage(key, defaultData) {
        if (key.startsWith("yru_trams")) {
            const v18 = localStorage.getItem("yru_trams_v18");
            if (v18 && v18) return JSON.parse(v18);
        }
        if (key.startsWith("yru_users")) {
            const v8 = localStorage.getItem("yru_users_v8");
            if (v8 && v8) return JSON.parse(v8);
        }
        if (!localStorage.getItem(key)) {
            localStorage.setItem(key, JSON.stringify(defaultData));
        }
        const res = JSON.parse(localStorage.getItem(key));
        if (!res) {
            localStorage.setItem(key, JSON.stringify(defaultData));
            return defaultData;
        }
        return res;
    }

    let trams = getStorage("yru_trams_v18", defaultTrams);
    if (!trams || trams.length < 10) {
        trams = defaultTrams;
    }

    const defaultTramCoords = {
        "EV-01": "6.549929, 101.291254", // จุดจอด 1 ประตูหลังมอ.
        "EV-02": "6.549100, 101.290467", // จุดจอด 2 ตึกศิลปะ
        "EV-03": "6.547835, 101.289502", // จุดจอด 3 ศูนย์วิทยาศาสตร์
        "EV-04": "6.547224, 101.289471", // จุดจอด 4 คณะวิทยาศาสตร์
        "EV-05": "6.547311, 101.288880", // จุดจอด 5 คณะสังคมศาสตร์
        "EV-06": "6.548822, 101.288523", // จุดจอด 6 อาคารเรียน 20
        "EV-07": "6.549225, 101.289286", // จุดจอด 7 คณะวิทยาการจัดการ
        "EV-08": "6.549929, 101.291254", // จุดจอด 1 ประตูหลังมอ.
        "EV-09": "6.547835, 101.289502", // จุดจอด 3 ศูนย์วิทยาศาสตร์
        "EV-10": "6.547311, 101.288880"  // จุดจอด 5 คณะสังคมศาสตร์
    };

    const baseVehicleIds = ["EV-01","EV-02","EV-03","EV-04","EV-05","EV-06","EV-07","EV-08","EV-09","EV-10"];
    const GARAGE_COORDS = "6.548900, 101.291700";

    trams.forEach(t => {
        const isBase = baseVehicleIds.includes(t.id);
        const isSuspended = (t.status === "รถขัดข้อง" || t.status === "ระงับการใช้งาน" || t.status === "MAINTENANCE" || t.status === "SUSPENDED");

        if (isSuspended) {
            t.coords = GARAGE_COORDS;
            t.current_station_id = "GARAGE";
        } else if (!isBase) {
            if (!t.coords || t.coords === GARAGE_COORDS) {
                t.coords = GARAGE_COORDS;
            }
            t.current_station_id = "GARAGE";
        } else {
            t.current_station_id = null;
            if (!t.coords || t.coords === GARAGE_COORDS || t.coords === "6.548900, 101.291700") {
                t.coords = defaultTramCoords[t.id] || GARAGE_COORDS;
            }
        }
    });

    // Auto-detect currently logged-in driver's assigned car from yru_trams_v18 or server auth
    let loggedInDriver = null;
    try {
        const rawUser = sessionStorage.getItem('yru_user_login') || localStorage.getItem('yru_user_login') || sessionStorage.getItem('yru_current_user') || localStorage.getItem('yru_current_user');
        if (rawUser) loggedInDriver = JSON.parse(rawUser);
    } catch(e) {}
    const serverAuthUser = {!! json_encode($authUser ?? null) !!};
    if (!loggedInDriver && serverAuthUser) {
        loggedInDriver = serverAuthUser;
    }

    let driverAssignedCar = null;
    if (loggedInDriver) {
        const foundAssigned = trams.find(t => 
            (t.driver_id && (t.driver_id === loggedInDriver.user_id || t.driver_id === loggedInDriver.emp_id || t.driver_id === loggedInDriver.employee_id || t.driver_id === loggedInDriver.email || t.driver_id === loggedInDriver.username)) ||
            (t.driver && (t.driver === loggedInDriver.name || t.driver.includes(loggedInDriver.name) || loggedInDriver.name.includes(t.driver)))
        );
        if (foundAssigned && foundAssigned.id) {
            driverAssignedCar = foundAssigned.id;
        }
    }

    const urlParams = new URLSearchParams(window.location.search);
    let paramCar = urlParams.get('car_id') || urlParams.get('car') || '';

    // Priority: 1. URL param (if specified), 2. Driver's assigned car in DB/Storage, 3. Server mapped car, 4. Default EV-01
    let resolvedCar = paramCar || driverAssignedCar || "{{ $mappedCar }}" || "EV-01";
    if (resolvedCar && !resolvedCar.startsWith('EV-')) {
        const num = parseInt(resolvedCar);
        resolvedCar = !isNaN(num) ? (num < 10 ? 'EV-0' + num : 'EV-' + num) : 'EV-01';
    }

    const currentCarCode = resolvedCar;
    const currentCarId = parseInt(currentCarCode.replace('EV-', '')) || 1;

    // Keep URL parameter synchronized with the resolved assigned car
    if (paramCar !== currentCarCode) {
        const cleanUrl = new URL(window.location.href);
        cleanUrl.searchParams.set('car', currentCarCode);
        window.history.replaceState({}, '', cleanUrl.toString());
    }

    trams.forEach((tram, idx) => {
        let maxOcc = (tram.capacity_sit !== undefined && !isNaN(parseInt(tram.capacity_sit))) ? parseInt(tram.capacity_sit) : 10;
        globalCarStatus[tram.id] = {
            status: tram.status || 'พร้อมใช้งาน',
            occupied: 0,
            max: maxOcc,
            isFull: false
        };
    });

    function getCarOccupiedInfo(tramId) {
        if (globalCarStatus[tramId]) {
            return globalCarStatus[tramId];
        }
        return { occupied: 0, max: 10, isFull: false };
    }

    trams.forEach(t => {
        const isBase = baseVehicleIds.includes(t.id);
        if (!t.status || t.status === 'เสร็จสิ้นการปฏิบัติงาน' || t.status === 'เลิกงาน') {
            t.status = 'พร้อมใช้งาน';
        }
        try {
            const rawCar = localStorage.getItem('yru_car_status_' + t.id);
            if (rawCar) {
                const parsed = JSON.parse(rawCar);
                if (parsed.status === 'เสร็จสิ้นการปฏิบัติงาน' || parsed.status === 'เลิกงาน') {
                    parsed.status = 'พร้อมใช้งาน';
                    localStorage.setItem('yru_car_status_' + t.id, JSON.stringify(parsed));
                }
            }
        } catch(e) {}

        const isSuspended = (t.status === "รถขัดข้อง" || t.status === "ระงับการใช้งาน" || t.status === "MAINTENANCE" || t.status === "SUSPENDED");

        if (isSuspended) {
            t.coords = GARAGE_COORDS;
            t.current_station_id = "GARAGE";
        } else if (!isBase) {
            if (!t.coords || t.coords === GARAGE_COORDS) {
                t.coords = GARAGE_COORDS;
            }
            t.current_station_id = "GARAGE";
        } else {
            t.current_station_id = null;
            if (!t.coords || t.coords === GARAGE_COORDS || t.coords === "6.548900, 101.291700") {
                t.coords = defaultTramCoords[t.id] || GARAGE_COORDS;
            }
        }
        if (t.id === "EV-01" && !t.driver_id) {
            t.driver_id = "USR003";
            t.driver = t.driver || "นายอัสมี มูเล็ง";
        }
        if (t.id === "EV-02" && !t.driver_id) {
            t.driver_id = "USR004";
            t.driver = t.driver || "นายอัรฟาน มะเระ";
        }
    });
    localStorage.setItem("yru_trams_v18", JSON.stringify(trams));

    // 🗺️ Leaflet Map Initialization centered at YRU campus (100% Identical to Home View)
    const map = L.map('map', {
        zoomControl: false,
        zoomSnap: 0.1,
        minZoom: 15,
        maxZoom: 20,
        maxBounds: [
            [6.535, 101.275],  // SW bound
            [6.563, 101.305]   // NE bound
        ],
        maxBoundsViscosity: 0.8
    }).setView([6.548600, 101.289950], 17.4);
    window.map = map;

    // 🗺️ 100% Original Google Maps HD Layer
    const googleRoadmap = L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
        attribution: '&copy; Google Maps'
    });

    const googleHybrid = L.tileLayer('https://{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
        attribution: '&copy; Google Maps'
    });

    // Set Google Maps Roadmap as default layer
    googleRoadmap.addTo(map);

    setTimeout(function() { if (map) map.invalidateSize(); }, 150);
    setTimeout(function() { if (map) map.invalidateSize(); }, 500);
    window.addEventListener('resize', function() { if (map) map.invalidateSize(); });

    const baseMaps = {
        "🗺️ แผนที่มาตรฐาน (Google HD)": googleRoadmap,
        "🛰️ ภาพถ่ายดาวเทียม (Google Hybrid)": googleHybrid
    };

    // Zoom controls
    L.control.zoom({
        position: 'topright'
    }).addTo(map);

    // Add layer control
    L.control.layers(baseMaps, null, { position: 'topright' }).addTo(map);

    // 🏫 University label control (top-left) - Modern glassmorphism
    const uniLabel = L.control({ position: 'topleft' });
    uniLabel.onAdd = function() {
        const div = L.DomUtil.create('div', '');
        div.innerHTML = `
            <div style="
                background: linear-gradient(135deg, rgba(244,114,182,0.9), rgba(236,72,153,0.9));
                backdrop-filter: blur(10px);
                color: white;
                padding: 7px 14px;
                border-radius: 24px;
                font-family: 'Inter', 'Sarabun', sans-serif;
                font-size: 11px;
                font-weight: 600;
                box-shadow: 0 4px 15px rgba(236,72,153,0.25);
                display: flex;
                align-items: center;
                gap: 6px;
                white-space: nowrap;
                user-select: none;
                pointer-events: none;
                margin: 10px;
                border: 1px solid rgba(255,255,255,0.1);
            ">
                <span style="font-size:13px;">🏫</span>
                <span>มหาวิทยาลัยราชภัฏยะลา</span>
            </div>
        `;
        return div;
    };
    uniLabel.addTo(map);

    // ===== Default Station Data (9 Bus Stops matching Home View) =====
    const defaultStationData = [
        { id: 1, name: "จุดจอด 1 หน้าอาคารที่พักบุคลากร", lat: 6.549929, lng: 101.291254, type: "P", status: "จอดอยู่", time: "Now", car: "EV-08", color: "#6366F1", border: "#C7D2FE" },
        { id: 2, name: "จุดจอด 2 หน้าตึกศิลปะ", lat: 6.549100, lng: 101.290467, type: "P", status: "รอถัดไป", time: "5 นาที", car: "EV-02", color: "#EC4899", border: "#F9A8D4" },
        { id: 3, name: "จุดจอด 3 หน้าอาคารศูนย์วิทยาศาสตร์", lat: 6.547835, lng: 101.289502, type: "P", status: "ถัดไป", time: "10 นาที", car: "EV-09", color: "#06B6D4", border: "#67E8F9" },
        { id: 4, name: "จุดจอด 4 หน้าอาคารคณะวิทยาศาสตร์", lat: 6.547224, lng: 101.289471, type: "P", status: "ถัดไป", time: "15 นาที", car: "EV-04", color: "#8B5CF6", border: "#C4B5FD" },
        { id: 5, name: "จุดจอด 5 หน้าอาคารคณะสังคมศาสตร์", lat: 6.547311, lng: 101.288880, type: "P", status: "จอดอยู่", time: "Now", car: "EV-10", color: "#F43F5E", border: "#FDA4AF" },
        { id: 6, name: "จุดจอด 6 หน้าร้าน Old School", lat: 6.547687, lng: 101.288335, type: "P", status: "รอถัดไป", time: "5 นาที", car: "EV-05", color: "#EF4444", border: "#FCA5A5" },
        { id: 7, name: "จุดจอด 7 หน้าอาคาร20", lat: 6.548822, lng: 101.288523, type: "P", status: "ถัดไป", time: "10 นาที", car: "EV-06", color: "#06B6D4", border: "#67E8F9" },
        { id: 8, name: "จุดจอด 8 หน้าอาคารคณะวิทยาการจัดการ", lat: 6.549225, lng: 101.289286, type: "P", status: "ถัดไป", time: "15 นาที", car: "EV-07", color: "#6366F1", border: "#C7D2FE" },
        { id: 9, name: "จุดจอด 9 หน้าโรงอาหาร", lat: 6.550323, lng: 101.290024, type: "P", status: "ถัดไป", time: "20 นาที", car: "EV-03", color: "#14B8A6", border: "#99F6E4" }
    ];

    let stationData = defaultStationData;
    let stationMarkers = {};

    function fitMapBounds() {
        const bounds = L.latLngBounds([
            [6.547150, 101.288400], // SW (station 5 & 6 area)
            [6.550050, 101.291750]  // NE (station 1 & garage area)
        ]);
        map.fitBounds(bounds, { padding: [10, 10], maxZoom: 17.5 });
    }

    // Force map to render correctly and auto-fit
    setTimeout(() => { if (map) { map.invalidateSize(); fitMapBounds(); } }, 100);
    setTimeout(() => { if (map) { map.invalidateSize(); fitMapBounds(); } }, 400);
    setTimeout(() => { if (map) { map.invalidateSize(); fitMapBounds(); } }, 1000);
    window.addEventListener('load', () => { if (map) { map.invalidateSize(); fitMapBounds(); } });
    window.addEventListener('resize', () => { if (map) { map.invalidateSize(); } });

    function loadDynamicStationData() {
        const localStops = JSON.parse(localStorage.getItem("yru_stops_v2") || "[]");
        if (localStops.length === 0 || localStops.length < 9) {
            stationData = defaultStationData;
            return;
        }

        stationData = localStops.map((stop, index) => {
            const seq = stop.sequence || (index + 1);
            let timeStr = "";
            let statusStr = "";
            if (seq === 1 || seq === 5) {
                timeStr = "Now";
                statusStr = "จอดอยู่";
            } else if (seq === 2 || seq === 6) {
                timeStr = "5 นาที";
                statusStr = "รอถัดไป";
            } else {
                timeStr = `${(seq % 3 + 1) * 5} นาที`;
                statusStr = "ถัดไป";
            }

            return {
                id: seq,
                name: stop.name,
                lat: parseFloat(stop.lat),
                lng: parseFloat(stop.lng),
                type: "P",
                status: statusStr,
                time: timeStr
            };
        });
    }

    // Station markers with distinctive colors and clean circular design (100% Matching Home View)
    const stationColorMap = {
        1: { border: '#EC4899', fill: '#F472B6', badge: '#DB2777' }, // Pink
        2: { border: '#8B5CF6', fill: '#A78BFA', badge: '#7C3AED' }, // Purple
        3: { border: '#3B82F6', fill: '#60A5FA', badge: '#2563EB' }, // Blue
        4: { border: '#10B981', fill: '#34D399', badge: '#059669' }, // Emerald
        5: { border: '#F59E0B', fill: '#FBBF24', badge: '#D97706' }, // Amber
        6: { border: '#EF4444', fill: '#F87171', badge: '#DC2626' }, // Red
        7: { border: '#06B6D4', fill: '#22D3EE', badge: '#0891B2' }, // Cyan
        8: { border: '#6366F1', fill: '#818CF8', badge: '#4F46E5' }, // Indigo
        9: { border: '#14B8A6', fill: '#2DD4BF', badge: '#0D9488' }  // Teal
    };

    function drawStationMarkers() {
        if (!map || typeof L === 'undefined') return;

        stationData.forEach(st => {
            const colors = stationColorMap[st.id] || { border: '#EC4899', fill: '#F472B6', badge: '#DB2777' };
            const iconHtml = `
                <div class="relative flex items-center justify-center" style="width: 60px; height: 60px;">
                    <!-- Concentric Target Pin Design (Clean & Consistent) -->
                    <div style="position: absolute; width: 34px; height: 34px; background: rgba(255,255,255,0.9); border-radius: 50%; box-shadow: 0 4px 10px rgba(0,0,0,0.12); display: flex; align-items: center; justify-content: center;">
                        <div style="width: 22px; height: 22px; border-radius: 50%; border: 3px solid ${colors.border}; background: #ffffff; display: flex; align-items: center; justify-content: center;">
                            <div style="width: 8px; height: 8px; border-radius: 50%; background: ${colors.border};"></div>
                        </div>
                    </div>

                    <!-- Dual Badges (P on left, จุดจอด on right) -->
                    <div style="position: absolute; top: 1px; display: flex; align-items: center; gap: 2px; z-index: 20; filter: drop-shadow(0 2px 4px rgba(0,0,0,0.15)); pointer-events: none;">
                        <span style="background: #ffffff; color: ${colors.border}; font-size: 8.5px; font-weight: 900; padding: 1px 4px; border-radius: 6px; border: 1.5px solid ${colors.border}; line-height: 1;">P</span>
                        <span style="background: ${colors.badge}; color: #ffffff; font-size: 8.5px; font-weight: 800; padding: 1.5px 5px; border-radius: 6px; line-height: 1; white-space: nowrap; font-family: 'Inter', 'Sarabun', sans-serif;">จุดจอด ${st.id}</span>
                    </div>
                </div>
            `;

            const icon = L.divIcon({
                className: 'custom-leaflet-icon',
                html: iconHtml,
                iconSize: [60, 60],
                iconAnchor: [30, 32]
            });

            const popupHtml = `
                <div class="p-2 font-kanit">
                    <h4 class="font-bold text-slate-800 text-sm mb-1">${st.name}</h4>
                    <p class="text-[11px] text-slate-500 mb-1 font-mono">พิกัด: ${st.lat}, ${st.lng}</p>
                    <div class="flex items-center gap-2 mt-1.5">
                        <span class="text-xs px-2 py-0.5 rounded-full ${st.status === 'จอดอยู่' ? 'bg-green-100 text-green-700' : st.status === 'กำลังมา' ? 'bg-blue-100 text-blue-700' : 'bg-pink-100 text-pink-700'} font-bold">
                            ${st.status}
                        </span>
                        <span class="text-xs text-slate-500 font-medium">
                            เวลารอ: ${st.time}
                        </span>
                    </div>
                </div>
            `;

            if (!stationMarkers[st.id]) {
                stationMarkers[st.id] = L.marker([st.lat, st.lng], { icon: icon, zIndexOffset: 2500 }).addTo(map)
                    .bindPopup(popupHtml);
            } else {
                stationMarkers[st.id].setIcon(icon);
                stationMarkers[st.id].getPopup().setContent(popupHtml);
            }
        });
    }

    loadDynamicStationData();
    drawStationMarkers();
    try { if (typeof initTramMarkers === 'function') initTramMarkers(); } catch(e) {}

    // Fallback static polyline route loop connecting all 9 stations along road network
    const fallbackRouteCoords = [
        [6.549929, 101.291254], // จุดจอด 1 (ประตูหลังมอ)
        [6.549880, 101.291000],
        [6.549100, 101.290467], // จุดจอด 2 (ตึกศิลปะ)
        [6.547835, 101.289502], // จุดจอด 3 (ศูนย์วิทยาศาสตร์)
        [6.547224, 101.289471], // จุดจอด 4 (คณะวิทยาศาสตร์)
        [6.547050, 101.289200],
        [6.547311, 101.288880], // จุดจอด 5 (สังคมศาสตร์)
        [6.547687, 101.288335], // จุดจอด 6 (หน้าร้าน Old School)
        [6.548822, 101.288523], // จุดจอด 7 (อาคารเรียน 20)
        [6.549225, 101.289286], // จุดจอด 8 (คณะวิทยาการจัดการ)
        [6.550100, 101.289800],
        [6.550323, 101.290024], // จุดจอด 9 (หน้าโรงอาหาร)
        [6.550350, 101.290500],
        [6.550150, 101.291200],
        [6.549929, 101.291254]  // Loop back to จุดจอด 1
    ];

    let activeRouteLines = [];

    function drawRouteLine(coords, color) {
        if (!map || typeof L === 'undefined' || !coords || coords.length < 2) return;
        const line = L.polyline(coords, {
            color: color || '#ec4899',
            weight: 5,
            opacity: 0.75,
            dashArray: '10, 10'
        }).addTo(map);
        activeRouteLines.push(line);
    }

    function clearAllRouteLines() {
        activeRouteLines.forEach(line => {
            if (map && line) map.removeLayer(line);
        });
        activeRouteLines = [];
    }

    // ⚡ Instant Route Line Renderer (Matching Home View Map Loop)
    function renderRouteInstant() {
        clearAllRouteLines();
        let drewAnyRoute = false;
        
        try {
            const rawStorage = localStorage.getItem("yru_routes_v1");
            if (rawStorage) {
                const localRoutes = JSON.parse(rawStorage || "[]");
                if (Array.isArray(localRoutes) && localRoutes.length > 0) {
                    localRoutes.forEach(route => {
                        let coords = route.polyline_data;
                        if (typeof coords === 'string') {
                            try { coords = JSON.parse(coords); } catch(e) { coords = []; }
                        }
                        if (coords && coords.length >= 2) {
                            drawRouteLine(coords, route.color || route.route_color || '#ec4899');
                            drewAnyRoute = true;
                        }
                    });
                }
            }
        } catch (e) {}

        // Always fallback to campus loop polyline if no active custom routes drawn
        if (!drewAnyRoute) {
            drawRouteLine(fallbackRouteCoords, '#ec4899');
        }
    }

    // Dynamic Route Loader (Instant 0ms render + Background API Sync + Realtime Popup)
    let lastKnownRouteSignature = null;
    let isInitialRouteLoad = true;

    function notifyRouteUpdate(actionText) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({
                toast: true,
                position: 'top-end',
                icon: 'info',
                title: '🚌 อัปเดตเส้นทางเดินรถ',
                text: actionText || 'แอดมินได้ทำการเพิ่ม/แก้ไข/ลบเส้นทางเดินรถใหม่ในระบบแล้ว',
                showConfirmButton: false,
                timer: 3500,
                timerProgressBar: true
            });
        }
    }

    async function loadDynamicRoute(forceNotifyAction) {
        renderRouteInstant();

        try {
            const response = await fetch('/api/routes?v=' + Date.now());
            if (response.ok) {
                const apiRoutes = await response.json();
                if (apiRoutes && Array.isArray(apiRoutes)) {
                    // Sync latest routes to localStorage
                    localStorage.setItem("yru_routes_v1", JSON.stringify(apiRoutes));

                    const currentSig = JSON.stringify(apiRoutes.map(r => ({ c: r.route_code, color: r.route_color || r.color, pts: (r.polyline_data || []).length })));
                    
                    if (currentSig !== lastKnownRouteSignature) {
                        clearAllRouteLines();
                        let allRouteCoords = [];
                        apiRoutes.forEach(route => {
                            let coords = route.polyline_data;
                            if (typeof coords === 'string') {
                                try { coords = JSON.parse(coords); } catch(e) { coords = []; }
                            }
                            if (coords && coords.length >= 2) {
                                drawRouteLine(coords, route.color || route.route_color || '#ec4899');
                                coords.forEach(pt => allRouteCoords.push(pt));
                            }
                        });

                        // Auto-bounce & fit bounds to updated route map if routes exist
                        if (!isInitialRouteLoad && allRouteCoords.length > 0 && typeof map !== 'undefined' && map && typeof map.flyToBounds === 'function') {
                            try {
                                const routeBounds = L.latLngBounds(allRouteCoords);
                                map.flyToBounds(routeBounds, { padding: [50, 50], maxZoom: 16.5, duration: 1.2 });
                            } catch(e) {}
                        }

                        if (!isInitialRouteLoad || forceNotifyAction) {
                            notifyRouteUpdate(forceNotifyAction);
                        }

                        lastKnownRouteSignature = currentSig;
                    }
                }
            }
        } catch (e) {
            console.error("API route sync error:", e);
        } finally {
            isInitialRouteLoad = false;
        }
    }

    loadDynamicRoute();
    setInterval(() => {
        if (!document.hidden) {
            loadDynamicRoute();
        }
    }, 15000);

    // Real-time BroadcastChannel sync across open tabs
    try {
        const routeSyncChannel = new BroadcastChannel('yru_routes_realtime_sync');
        routeSyncChannel.onmessage = function(ev) {
            if (ev.data && ev.data.type === 'route_updated') {
                const actMsg = ev.data.action === 'delete' ? 'แอดมินได้ลบเส้นทางเดินรถออกจากระบบ' : (ev.data.action === 'add' ? 'แอดมินได้เพิ่มเส้นทางเดินรถใหม่ในระบบ' : 'แอดมินได้แก้ไขเส้นทางเดินรถในระบบ');
                loadDynamicRoute(actMsg);
            }
        };
    } catch(e) {}

    // Listen to real-time storage updates from Admin tab
    window.addEventListener('storage', (event) => {
        if (!event || !event.key || event.key === 'yru_stops_v2' || event.key === 'yru_routes_v1' || event.key === 'yru_routes_last_updated') {
            if (typeof loadDynamicStations === 'function') loadDynamicStations();
            if (typeof loadDynamicStationData === 'function') loadDynamicStationData();
            if (typeof drawStationMarkers === 'function') drawStationMarkers();
            if (typeof loadDynamicRoute === 'function') loadDynamicRoute('แอดมินได้ทำการปรับเปลี่ยนข้อมูลเส้นทางเดินรถ');
        }
    });

    // ===== 🚌 Shuttle Markers — ALL vehicles shown with numbered icons =====
    // ===== Garage / Depot Clustering Helpers =====

    // Helper: Create custom HTML DivIcon for Garage Cluster Marker
    function getGarageClusterIcon(count) {
        return L.divIcon({
            className: 'custom-leaflet-icon',
            html: `
                <div class="relative group cursor-pointer flex items-center justify-center" style="transform: translate(-50%, -50%);">
                    <div style="background-color: #ffffff !important; color: #000000 !important; border: 2.5px solid #f59e0b !important; border-radius: 14px; padding: 4px 10px; box-shadow: 0 4px 14px rgba(0,0,0,0.25); display: flex; align-items: center; gap: 6px; white-space: nowrap;">
                        <span style="font-size: 14px;">🏠</span>
                        <span style="font-size: 12px; font-weight: 800; color: #000000 !important; font-family: 'Kanit', sans-serif;">จุดเก็บรถ ${count} คัน</span>
                    </div>
                </div>
            `,
            iconSize: [130, 36],
            iconAnchor: [0, 0]
        });
    }

    let garageClusterMarker = null;

    function updateGarageCluster(garageTrams) {
        if (!garageTrams || garageTrams.length === 0) {
            if (garageClusterMarker && map) {
                map.removeLayer(garageClusterMarker);
                garageClusterMarker = null;
            }
            return;
        }

        const count = garageTrams.length;
        const icon = getGarageClusterIcon(count);
        const latLng = [GARAGE_LAT, GARAGE_LNG];

        const vehicleListItems = garageTrams.map(tram => {
            const tramIndex = (typeof trams !== 'undefined' && Array.isArray(trams)) ? trams.findIndex(t => t.id === tram.id) : -1;
            const color = getVehicleColor(tramIndex !== -1 ? tramIndex : 0);
            const carData = globalCarStatus[tram.id] || {};
            const rawStatus = (carData.status || tram.status || 'ซ่อมบำรุง / หยุดพัก').toString().trim();
            const isBroken = rawStatus.includes('ขัดข้อง') || rawStatus === 'MAINTENANCE' || rawStatus.includes('ระงับ');
            const isEnded = rawStatus === 'ended' || rawStatus === 'สิ้นสุด' || rawStatus === 'เลิกงาน';
            
            let statusBadgeClass = 'bg-amber-100 text-amber-800 border-amber-300';
            let statusIcon = '🚫';
            let displayStatus = 'ระงับการใช้งาน';
            
            if (rawStatus.includes('ขัดข้อง') || rawStatus.includes('เสีย') || rawStatus === 'MAINTENANCE') {
                statusBadgeClass = 'bg-rose-100 text-rose-700 border-rose-200';
                statusIcon = '🚨';
                displayStatus = 'ขัดข้อง';
            } else if (rawStatus.includes('ระงับ') || rawStatus === 'SUSPENDED' || rawStatus === 'DISABLED' || isEnded) {
                statusBadgeClass = 'bg-amber-100 text-amber-800 border-amber-300';
                statusIcon = '🚫';
                displayStatus = 'ระงับการใช้งาน';
            } else {
                displayStatus = rawStatus || 'ระงับการใช้งาน';
            }

            return `
                <div class="p-2.5 bg-slate-50 border border-slate-200/80 rounded-xl hover:bg-slate-100/80 transition shadow-sm">
                    <div class="flex items-center justify-between gap-2 mb-1 flex-nowrap">
                        <div class="flex items-center gap-1.5 whitespace-nowrap shrink-0">
                            <span class="font-bold text-slate-800 text-xs whitespace-nowrap">${tram.id}</span>
                            <span class="text-[10px] text-slate-500 font-mono whitespace-nowrap">(${tram.plate || 'N/A'})</span>
                        </div>
                        <span class="text-[9.5px] font-bold px-2 py-0.5 rounded-full border ${statusBadgeClass} whitespace-nowrap shrink-0">
                            ${statusIcon} ${displayStatus}
                        </span>
                    </div>
                    <div class="text-[10.5px] text-slate-600">
                        <p><span class="text-slate-400">ผู้ขับ:</span> <span class="font-semibold text-slate-700">${tram.driver || 'ไม่มีผู้ปฏิบัติงาน'}</span></p>
                    </div>
                </div>
            `;
        }).join('');

        const popupHtml = `
            <div class="p-3 font-kanit min-w-[290px] max-w-[340px]">
                <div class="flex items-center justify-between border-b border-slate-200 pb-2 mb-2">
                    <div class="flex items-center gap-2">
                        <div class="w-8 h-8 rounded-xl bg-amber-100 text-amber-700 flex items-center justify-center font-bold text-base shadow-sm">
                            🛠️
                        </div>
                        <div>
                            <h4 class="font-bold text-slate-800 text-sm leading-tight">จุดจอดเก็บรถ</h4>
                        </div>
                    </div>
                    <span class="bg-gradient-to-r from-red-500 to-rose-600 text-white text-xs font-black px-2.5 py-1 rounded-full shadow-sm">
                        ${count} คัน
                    </span>
                </div>
                <p class="text-xs font-semibold text-slate-700 mb-2 flex items-center gap-1">
                    <span>📋</span> รายชื่อรถประจำจุดจอดเก็บรถ:
                </p>
                <div class="space-y-2 max-h-56 overflow-y-auto pr-1">
                    ${vehicleListItems}
                </div>
            </div>
        `;

        if (!garageClusterMarker) {
            garageClusterMarker = L.marker(latLng, {
                icon: icon,
                zIndexOffset: 300
            }).addTo(map);
        } else {
            garageClusterMarker.setLatLng(latLng);
            garageClusterMarker.setIcon(icon);
        }
        garageClusterMarker.bindPopup(popupHtml);
    }

    // ===== Shuttle Markers =====
    const tramMarkers = {};

    function getShuttleIcon(tramId, status, vehicleIdx) {
        const color = getVehicleColor(vehicleIdx);
        const number = vehicleIdx + 1;
        const isCurrentCar = (tramId === currentCarCode || tramId === 'EV-' + currentCarId || tramId === 'EV-0' + currentCarId);

        let storedStatus = '';
        try {
            const rawStored = localStorage.getItem('yru_car_status_' + tramId);
            if (rawStored) {
                const parsedStored = JSON.parse(rawStored);
                storedStatus = (parsedStored.status || '').toString().trim();
            }
        } catch(e) {}

        const tramObj = (typeof trams !== 'undefined' && Array.isArray(trams)) ? trams.find(t => t.id === tramId) : null;
        const mainStatus = (tramObj ? tramObj.status : '').toString().trim();
        const carData = (typeof globalCarStatus !== 'undefined' && globalCarStatus[tramId]) ? globalCarStatus[tramId] : {};
        const rawStatus = (carData.status || storedStatus || (tramObj ? tramObj.status : '')).toString().trim();

        const isPause = (
            status === 'pause' ||
            storedStatus === 'pause' || storedStatus === 'พักเบรก' || storedStatus.includes('พัก') ||
            rawStatus === 'pause' || rawStatus === 'พักเบรก' || rawStatus.includes('พัก') ||
            (mainStatus && (mainStatus.includes('พัก') || mainStatus === 'pause'))
        );

        const isBroken = (
            (mainStatus === 'รถขัดข้อง' || mainStatus === 'broken' || storedStatus === 'รถขัดข้อง' || storedStatus === 'broken') &&
            mainStatus !== 'พร้อมใช้งาน' && mainStatus !== 'ACTIVE'
        );

        if (isPause) {
            status = 'pause';
        } else if (isBroken) {
            status = 'broken';
        } else {
            status = 'normal';
        }

        let bgColor = color.bg;
        let textColor = color.text;
        let pingHtml = isCurrentCar ? `<div class="absolute -inset-2 rounded-full bg-pink-500/40 animate-ping"></div>` : '';
        let opacityClass = '';
        let statusIcon = '';
        let topStatusBadge = '';

        if (status === 'pause') {
            bgColor = '#EAB308';
            textColor = '#451A03';
            pingHtml = isCurrentCar ? `<div class="absolute -inset-2 rounded-full bg-amber-500/40 animate-ping"></div>` : '';
            statusIcon = '<i class="fas fa-pause text-[8px] absolute -bottom-0.5 -right-0.5 bg-amber-600 text-white w-4 h-4 flex items-center justify-center rounded-full border-2 border-white shadow-md"></i>';
            topStatusBadge = `<div class="absolute -top-6 left-1/2 -translate-x-1/2 bg-amber-500 text-white text-[9px] font-black px-2 py-0.5 rounded-full shadow-md whitespace-nowrap border border-white flex items-center gap-1"><i class="fas fa-pause text-[8px]"></i> พัก</div>`;
        } else if (status === 'broken') {
            pingHtml = '';
            bgColor = '#EF4444';
            statusIcon = '<i class="fas fa-wrench text-[8px] absolute -bottom-0.5 -right-0.5 bg-red-100 text-red-600 w-3.5 h-3.5 flex items-center justify-center rounded-full border border-white"></i>';
            topStatusBadge = `<div class="absolute -top-6 left-1/2 -translate-x-1/2 bg-red-600 text-white text-[9px] font-black px-2 py-0.5 rounded-full shadow-md whitespace-nowrap border border-white flex items-center gap-1"><i class="fas fa-wrench text-[8px]"></i> รถเสีย</div>`;
        } else if (mainStatus !== 'พร้อมใช้งาน' && mainStatus !== 'ACTIVE' && (status === 'ended' || rawStatus === 'ended' || rawStatus === 'สิ้นสุด' || rawStatus === 'เลิกงาน')) {
            pingHtml = '';
            opacityClass = 'opacity-50';
            bgColor = '#94A3B8';
            statusIcon = '<i class="fas fa-stop text-[7px] absolute -bottom-0.5 -right-0.5 bg-slate-200 text-slate-500 w-3.5 h-3.5 flex items-center justify-center rounded-full border border-white"></i>';
        }

        const labelText = tramId;
        const pillTextColor = (status === 'pause') ? '#D97706' : ((status === 'broken') ? '#DC2626' : color.bg);
        const pillBorderColor = (status === 'pause') ? '#F59E0B' : ((status === 'broken') ? '#EF4444' : color.border);

        return L.divIcon({
            className: 'custom-leaflet-icon',
            html: `
                <div class="relative shuttle-float ${opacityClass}" style="transform: translate(-20px, -20px);">
                    ${topStatusBadge}
                    ${pingHtml}
                    <div class="relative w-10 h-10 rounded-full flex items-center justify-center border-[3px] ${isCurrentCar ? 'border-pink-500 ring-4 ring-pink-400/50 scale-110 shadow-2xl z-50' : 'border-white shadow-xl'}"
                         style="background: ${bgColor};">
                        <div class="flex items-center gap-1">
                            <i class="fas fa-bus text-[10px]" style="color: ${textColor};"></i>
                            <span class="text-sm font-black" style="color: ${textColor}; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">${number}</span>
                        </div>
                        ${statusIcon}
                    </div>
                    <span class="absolute -bottom-5 left-1/2 -translate-x-1/2 ${isCurrentCar ? 'bg-gradient-to-r from-pink-600 to-rose-600 text-white font-extrabold shadow-lg border border-pink-200' : 'bg-white/95 backdrop-blur-sm font-bold text-slate-700 border border-slate-200'} text-[9.5px] px-2.5 py-0.5 rounded-full shadow whitespace-nowrap">
                        ${labelText} ${isCurrentCar ? '(รถของคุณ)' : ''}
                    </span>
                </div>
            `,
            iconSize: [40, 40],
            iconAnchor: [20, 20]
        });
    }

    function initTramMarkers() {
        if (!map || typeof L === 'undefined') return;
        const garageTrams = [];

        trams.forEach((tram, idx) => {
            if (isVehicleInGarage(tram)) {
                if (tramMarkers[tram.id]) {
                    map.removeLayer(tramMarkers[tram.id]);
                    delete tramMarkers[tram.id];
                }
                garageTrams.push(tram);
                return;
            }

            let latLng = null;
            if (tram.coords) {
                const parts = tram.coords.split(',').map(Number);
                if (parts.length === 2 && !isNaN(parts[0]) && !isNaN(parts[1])) {
                    latLng = parts;
                }
            }
            if (!latLng) {
                const defCoord = defaultTramCoords[tram.id];
                if (defCoord) {
                    latLng = defCoord.split(',').map(Number);
                }
            }
            if (!latLng) latLng = [6.548600, 101.289950];

            const color = getVehicleColor(idx);
            const tramIndex = idx;
            const carData = (typeof globalCarStatus !== 'undefined' && globalCarStatus[tram.id]) ? globalCarStatus[tram.id] : {};
            const isCurrentCar = (tram.id === currentCarCode || tram.id === 'EV-' + currentCarId || tram.id === 'EV-0' + currentCarId);
            let storedCarStatus = '';
            try {
                const sObj = JSON.parse(localStorage.getItem('yru_car_status_' + tram.id) || '{}');
                storedCarStatus = (sObj.status || sObj.driver_status || '').toString().trim();
            } catch(e) {}
            const rawStatus = (storedCarStatus || carData.status || tram.status || 'พร้อมใช้งาน').toString().trim();
            const isBroken = rawStatus.includes('ขัดข้อง') || rawStatus === 'MAINTENANCE' || rawStatus.includes('ระงับ');
            const isPause = rawStatus.includes('พัก') || rawStatus === 'pause';

            let statusIcon = '🟢';
            let statusBadge = 'bg-emerald-50 text-emerald-700 border-emerald-300';
            let displayStatusText = 'พร้อมใช้งาน';
            if (isBroken) {
                statusIcon = '🚨';
                statusBadge = 'bg-rose-50 text-rose-700 border-rose-300';
                displayStatusText = rawStatus;
            } else if (isPause) {
                statusIcon = '🟡';
                statusBadge = 'bg-amber-50 text-amber-800 border-amber-300';
                displayStatusText = 'พักเบรค';
            }

            const icon = getShuttleIcon(tram.id, tram.status, idx);

            const occInfo = getCarOccupiedInfo(tram.id);
            const statusDisplay = (occInfo.isFull && !isVehicleInGarage(tram))
                ? '<span class="font-bold text-red-600 bg-red-100 border border-red-200 px-2 py-0.5 rounded-full text-xs">🛑 รถเต็ม (Full)</span>'
                : `<span class="inline-flex items-center gap-1 font-bold text-xs px-2.5 py-0.5 rounded-full border shadow-xs ${statusBadge}">${statusIcon} ${displayStatusText}</span>`;

            const popupHtml = `
                <div class="p-3 font-kanit min-w-[260px]">
                    <div class="flex items-center justify-between border-b pb-2 mb-2">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-xl flex items-center justify-center font-bold text-white shadow-sm" style="background: ${color.bg};">
                                ${idx + 1}
                            </div>
                            <div>
                                <h4 class="font-bold text-slate-800 text-sm leading-tight">${tram.id} ${isCurrentCar ? '<span class="text-xs text-pink-600 font-extrabold">(รถของคุณ)</span>' : ''}</h4>
                                <p class="text-[11px] text-slate-500 font-mono">${tram.plate || ''}</p>
                            </div>
                        </div>
                        ${statusDisplay}
                    </div>
                    <div class="space-y-1 text-xs text-slate-600 mb-2">
                        <p><span class="text-slate-400">ผู้ขับ:</span> <span class="font-semibold text-slate-700">${tram.driver || 'ไม่มีคนขับ'}</span></p>
                        <p><span class="text-slate-400">ผู้โดยสาร:</span> <span class="font-semibold ${occInfo.isFull ? 'text-red-600' : 'text-pink-600'} font-extrabold">${occInfo.occupied}/${occInfo.max} คน</span></p>
                    </div>
                </div>
            `;

            if (!tramMarkers[tram.id]) {
                const m = L.marker(latLng, {
                    icon: icon,
                    zIndexOffset: isCurrentCar ? 3500 : (2000 + idx)
                }).addTo(map);
                m.bindPopup(popupHtml);
                m._originalLatLng = latLng;
                tramMarkers[tram.id] = m;
            } else {
                tramMarkers[tram.id]._originalLatLng = latLng;
                tramMarkers[tram.id].setIcon(icon);
                tramMarkers[tram.id].getPopup().setContent(popupHtml);
            }
        });

        updateGarageCluster(garageTrams);
        applyAntiOverlap();
    }

    // ===== Anti-Overlap: Smart offset to prevent vehicles from obscuring station markers or each other =====
    function applyAntiOverlap() {
        if (!map || typeof tramMarkers === 'undefined') return;
        const tramEntries = Object.entries(tramMarkers);
        if (tramEntries.length === 0) return;

        // 1. Get pixel positions for station markers
        const stationPoints = (typeof stationMarkers !== 'undefined' ? Object.entries(stationMarkers) : []).map(([stId, stMarker]) => {
            if (!stMarker || !stMarker.getLatLng) return null;
            const latlng = stMarker.getLatLng();
            const point = map.latLngToContainerPoint(latlng);
            return { id: stId, latlng, point };
        }).filter(Boolean);

        // 2. Get base pixel positions for vehicle markers using _originalLatLng
        const tramPositions = tramEntries.map(([id, marker]) => {
            if (!marker) return null;
            const baseLatLng = marker._originalLatLng || marker.getLatLng();
            const point = map.latLngToContainerPoint(baseLatLng);
            return { id, marker, baseLatLng, point };
        }).filter(Boolean);

        const ST_THRESHOLD_PX = 32;
        const VEH_THRESHOLD_PX = 30;

        const vehiclesAtStations = {};
        const routeVehicles = [];

        tramPositions.forEach(v => {
            let nearestStation = null;
            let minDist = Infinity;

            stationPoints.forEach(st => {
                const dx = v.point.x - st.point.x;
                const dy = v.point.y - st.point.y;
                const dist = Math.hypot(dx, dy);
                if (dist < ST_THRESHOLD_PX && dist < minDist) {
                    minDist = dist;
                    nearestStation = st;
                }
            });

            if (nearestStation) {
                if (!vehiclesAtStations[nearestStation.id]) {
                    vehiclesAtStations[nearestStation.id] = { station: nearestStation, vehicles: [] };
                }
                vehiclesAtStations[nearestStation.id].vehicles.push(v);
            } else {
                routeVehicles.push(v);
            }
        });

        function setMarkerLatLngSmooth(marker, targetLatLng) {
            const current = marker.getLatLng();
            if (!current || Math.abs(current.lat - targetLatLng.lat) > 0.000001 || Math.abs(current.lng - targetLatLng.lng) > 0.000001) {
                marker.setLatLng(targetLatLng);
            }
        }

        // Crisp, orderly positioning for vehicles at station stops
        Object.values(vehiclesAtStations).forEach(({ station, vehicles }) => {
            const count = vehicles.length;
            if (count === 1) {
                const newPoint = L.point(station.point.x + 12, station.point.y - 12);
                const newLatLng = map.containerPointToLatLng(newPoint);
                setMarkerLatLngSmooth(vehicles[0].marker, newLatLng);
            } else if (count === 2) {
                const p0 = L.point(station.point.x - 14, station.point.y - 12);
                const p1 = L.point(station.point.x + 14, station.point.y - 12);
                setMarkerLatLngSmooth(vehicles[0].marker, map.containerPointToLatLng(p0));
                setMarkerLatLngSmooth(vehicles[1].marker, map.containerPointToLatLng(p1));
            } else {
                const step = 26;
                const startX = station.point.x - ((count - 1) * step) / 2;
                vehicles.forEach((v, i) => {
                    const newPoint = L.point(startX + i * step, station.point.y - 14);
                    setMarkerLatLngSmooth(v.marker, map.containerPointToLatLng(newPoint));
                });
            }
        });

        // Vehicle-to-vehicle anti-overlap for open route vehicles
        const visited = new Set();
        const groups = [];

        for (let i = 0; i < routeVehicles.length; i++) {
            if (visited.has(i)) continue;
            const group = [i];
            visited.add(i);

            for (let j = i + 1; j < routeVehicles.length; j++) {
                if (visited.has(j)) continue;
                const dx = routeVehicles[i].point.x - routeVehicles[j].point.x;
                const dy = routeVehicles[i].point.y - routeVehicles[j].point.y;
                const dist = Math.hypot(dx, dy);
                if (dist < VEH_THRESHOLD_PX) {
                    group.push(j);
                    visited.add(j);
                }
            }

            if (group.length > 1) {
                groups.push(group);
            } else {
                setMarkerLatLngSmooth(routeVehicles[i].marker, routeVehicles[i].baseLatLng);
            }
        }

        groups.forEach(group => {
            const count = group.length;
            const angleStep = (2 * Math.PI) / count;
            const OFFSET_PX = 36;

            group.forEach((posIdx, i) => {
                const angle = angleStep * i - Math.PI / 2;
                const offsetX = Math.cos(angle) * OFFSET_PX;
                const offsetY = Math.sin(angle) * OFFSET_PX;

                const pos = routeVehicles[posIdx];
                const newPoint = L.point(pos.point.x + offsetX, pos.point.y + offsetY);
                const newLatLng = map.containerPointToLatLng(newPoint);
                setMarkerLatLngSmooth(pos.marker, newLatLng);
            });
        });
    }

    if (map) {
        map.on('zoomend moveend', () => {
            applyAntiOverlap();
        });
    }


    // Debounced wrapper: ป้องกัน initTramMarkers ถูกเรียกซ้ำถี่เกินไป (เช่น จาก storage event)
    let _initTramMarkersDebounceTimer = null;
    function debouncedInitTramMarkers() {
        clearTimeout(_initTramMarkersDebounceTimer);
        _initTramMarkersDebounceTimer = setTimeout(() => {
            if (typeof initTramMarkers === 'function') initTramMarkers();
        }, 300);
    }

    initTramMarkers();

    // Resolve driver, vehicle, and route details dynamically
    trams = getStorage("yru_trams_v18", defaultTrams);
    const users = getStorage("yru_users_v6", defaultUsers);

    let currentOccupied = 0;
    const currentCar = trams.find(t => t.id === currentCarCode || t.id === 'EV-' + currentCarId || t.id === 'EV-0' + currentCarId);
    let maxCapacity = (currentCar && currentCar.capacity_sit !== undefined) ? (parseInt(currentCar.capacity_sit) || 10) : 10;

    document.addEventListener('DOMContentLoaded', () => {
        const capacityEl = document.getElementById('seats-max-capacity');
        if (capacityEl) capacityEl.innerText = maxCapacity;
        
        const codeEl = document.getElementById('car-display-code');
        if (codeEl) codeEl.innerText = currentCarCode;

        let currentCarIdx = trams.findIndex(t => t.id === currentCarCode || t.id === 'EV-' + currentCarId || t.id === 'EV-0' + currentCarId);
        if (currentCarIdx === -1) currentCarIdx = 0;
        const currentCarObj = trams[currentCarIdx];
        if (currentCarObj && currentCarObj.capacity_sit !== undefined) {
            maxCapacity = parseInt(currentCarObj.capacity_sit) || 10;
            const capEl = document.getElementById('seats-max-capacity');
            if (capEl) capEl.innerText = maxCapacity;
        }

        const carNum = currentCarIdx + 1;
        const carCode = currentCarObj ? currentCarObj.id : currentCarCode;
        const carPlate = currentCarObj ? (currentCarObj.plate || '') : '';
        const carRoute = currentCarObj ? (currentCarObj.route || 'สาย 1: หน้ามรย-หอพัก') : 'ุ 1';
        const color = getVehicleColor(currentCarIdx);

        const emailMapByName = {
            'นายอัสมี มูเล็ง': 'asmee@yru.ac.th',
            'นายอัรฟาน มะเระ': 'arfan@yru.ac.th',
            'นายซูเฟียน มะโละ': 'sufiyan@yru.ac.th',
            'นายอุสมาน สาและ': 'usman@yru.ac.th',
            'นายบัดรี สาและ': 'badri@yru.ac.th',
            'นายตอริก ลือแมะ': 'torik@yru.ac.th',
            'นายสมหวัง ใจดี': 'somwang@yru.ac.th',
            'นายสมใจ ใจดี': 'somjai@yru.ac.th',
            'นายกิตติ ตั้งใจ': 'kitti@yru.ac.th',
            'นายรุสลัน สอเฮาะ': 'ruslan@yru.ac.th'
        };

        let driverName = currentCarObj ? (currentCarObj.driver || 'ยังไม่มอบหมาย') : 'ยังไม่มอบหมาย';
        let driverEmail = emailMapByName[driverName] || '';
        
        if (currentCarObj && (currentCarObj.driver_id || currentCarObj.driver)) {
            const allUsers = getStorage("yru_users_v8", defaultUsers);
            const foundUser = allUsers.find(u => 
                (currentCarObj.driver_id && (u.user_id === currentCarObj.driver_id || u.emp_id === currentCarObj.driver_id || u.employee_id === currentCarObj.driver_id || u.email === currentCarObj.driver_id || u.username === currentCarObj.driver_id)) ||
                (currentCarObj.driver && (u.name === currentCarObj.driver || u.email === currentCarObj.driver || u.username === currentCarObj.driver))
            );
            if (foundUser) {
                if (foundUser.name) driverName = foundUser.name;
                if (foundUser.email) driverEmail = foundUser.email;
            }
        }
        let actualLoggedInEmail = '';
        let actualLoggedInName = '';
        if (loggedInDriver) {
            actualLoggedInEmail = loggedInDriver.email || loggedInDriver.username || '';
            actualLoggedInName = loggedInDriver.name || '';
        }
        const profileDisplayEmail = actualLoggedInEmail || driverEmail || (driverName ? driverName : 'พนักงานขับรถ');
        const profileDisplayName = actualLoggedInName || driverName || 'พนักงานขับรถ';

        // 1. Update Header Badge
        const hNum = document.getElementById('header-car-num-badge');
        if (hNum) { hNum.innerText = carNum; hNum.style.background = color.bg; }
        
        const hCode = document.getElementById('header-car-code-text');
        if (hCode) hCode.innerText = carCode;
        
        const hPlate = document.getElementById('header-car-plate-text');
        if (hPlate) hPlate.innerText = carPlate ? `(${carPlate})` : '';
        
        const hDriver = document.getElementById('header-driver-name-text');
        if (hDriver) hDriver.innerText = driverName;

        // 2. Update Logged-in Profile & Dropdown
        const loggedName = document.getElementById('driver-logged-name');
        if (loggedName) loggedName.innerText = profileDisplayEmail;
        
        const avatarCircle = document.getElementById('driver-avatar-circle');
        if (avatarCircle) {
            avatarCircle.innerText = carNum;
            avatarCircle.style.background = color.bg;
            avatarCircle.style.color = '#ffffff';
        }
        
        const dropEmail = document.getElementById('dropdown-user-email');
        if (dropEmail) dropEmail.innerText = profileDisplayEmail;
        
        const dropDriver = document.getElementById('dropdown-driver-name');
        if (dropDriver) dropDriver.innerHTML = `<span>พนักงานขับรถ: <strong class="text-pink-600">${profileDisplayName}</strong></span>`;
        
        const dropCar = document.getElementById('dropdown-car-info');
        if (dropCar) dropCar.innerHTML = `<span>รถไฟฟ้าประจำตัว: <strong class="text-slate-700">${carCode} ${carPlate ? '(' + carPlate + ')' : ''}</strong></span>`;

        // 3. Update Side Control Panel Card
        const cBadge = document.getElementById('card-vehicle-badge-circle');
        if (cBadge) { cBadge.innerText = carNum; cBadge.style.background = color.bg; }
        
        const cCode = document.getElementById('card-car-code');
        if (cCode) cCode.innerText = carCode;

        const cDisplayCode = document.getElementById('card-display-car-code');
        if (cDisplayCode) cDisplayCode.innerText = carCode;
        
        const cPlate = document.getElementById('card-car-plate');
        if (cPlate) cPlate.innerText = carPlate ? `(${carPlate})` : '';
        
        const cDriver = document.getElementById('card-driver-name');
        if (cDriver) cDriver.innerText = driverName;
        
        const cRoute = document.getElementById('card-route-name');
        if (cRoute) cRoute.innerText = carRoute;

        const cRound = document.getElementById('card-round-number');
        if (cRound) {
            cRound.innerText = currentRoundNumber;
        }

        const driverDisp = document.getElementById('driver-name-display');
        if (driverDisp) driverDisp.innerText = `พนักงานขับรถ: ${driverName}`;

        const routeDisp = document.getElementById('route-name-display');
        if (routeDisp) routeDisp.innerText = carRoute;

        updateNextStopUI();
        if (typeof syncDriverVehicleData === 'function') syncDriverVehicleData();
        if (typeof syncVehicleWorkState === 'function') syncVehicleWorkState();
        if (typeof updateRoundDisplay === 'function') updateRoundDisplay();
    });

    function syncDriverVehicleData() {
        const freshTrams = getStorage("yru_trams_v18", defaultTrams);
        if (!freshTrams || !Array.isArray(freshTrams)) return;

        let loggedInUser = null;
        try {
            const rawUser = sessionStorage.getItem('yru_user_login') || localStorage.getItem('yru_user_login') || sessionStorage.getItem('yru_current_user') || localStorage.getItem('yru_current_user');
            if (rawUser) loggedInUser = JSON.parse(rawUser);
        } catch(e) {}

        if (loggedInUser && (loggedInUser.role === 'driver' || loggedInUser.user_role === 'driver' || loggedInUser.role === 'พนักงานขับรถ' || loggedInUser.user_role === 'พนักงานขับรถ')) {
            const assignedTram = freshTrams.find(t => 
                (t.driver_id && (t.driver_id === loggedInUser.user_id || t.driver_id === loggedInUser.emp_id || t.driver_id === loggedInUser.employee_id || t.driver_id === loggedInUser.email || t.driver_id === loggedInUser.username)) ||
                (t.driver && (t.driver === loggedInUser.name || t.driver.includes(loggedInUser.name) || loggedInUser.name.includes(t.driver)))
            );
            if (assignedTram && assignedTram.id && assignedTram.id !== currentCarCode) {
                const currentUrl = new URL(window.location.href);
                currentUrl.searchParams.set("car", assignedTram.id);
                currentUrl.searchParams.set("reassigned_from", currentCarCode);
                window.location.replace(currentUrl.toString());
                return;
            }
        }

        let curIdx = freshTrams.findIndex(t => t.id === currentCarCode || t.id === "EV-" + currentCarId || t.id === "EV-0" + currentCarId);
        if (curIdx === -1) curIdx = 0;
        const curObj = freshTrams[curIdx];

        if (curObj) {
            if (curObj.status === "ระงับการใช้งาน" || curObj.status === "รถขัดข้อง" || curObj.status === "MAINTENANCE" || curObj.status === "SUSPENDED") {
                Swal.fire({
                    icon: "warning",
                    title: "รถถูกระงับการใช้งาน / รถขัดข้อง",
                    html: "รถไฟฟ้าประจำของคุณ (" + currentCarCode + ") อยู่ในสถานะ <span class=\"text-rose-600 font-bold\">" + curObj.status + "</span><br>ไม่สามารถออกปฏิบัติงานได้ กรุณาติดต่อผู้ดูแลระบบ",
                    confirmButtonText: "รับทราบ",
                    confirmButtonColor: "#EC4899",
                    customClass: { popup: "rounded-2xl shadow-2xl" }
                });
            }

            const reassignedFrom = urlParams.get("reassigned_from");
            if (reassignedFrom && reassignedFrom !== currentCarCode) {
                Swal.fire({
                    icon: "info",
                    title: "แจ้งเตือนการมอบหมายงานใหม่",
                    html: "ผู้ดูแลระบบได้มอบหมายงานให้ท่านปฏิบัติหน้าที่ขับรถไฟฟ้า <b class=\"text-pink-600\">" + currentCarCode + "</b> (" + (curObj.plate || "ทะเบียนประจำรถ") + ")<br>แทนรถคันเดิม (" + reassignedFrom + ")",
                    confirmButtonText: "รับทราบและเริ่มงาน",
                    confirmButtonColor: "#EC4899",
                    customClass: { popup: "rounded-2xl shadow-2xl" }
                });
                const cleanUrl = new URL(window.location.href);
                cleanUrl.searchParams.delete("reassigned_from");
                window.history.replaceState({}, "", cleanUrl.toString());
            }

            if (curObj.capacity_sit !== undefined && !isNaN(parseInt(curObj.capacity_sit))) {
                const newMax = parseInt(curObj.capacity_sit) || 10;
                if (newMax !== maxCapacity) {
                    maxCapacity = newMax;
                    const capEl = document.getElementById("seats-max-capacity");
                    if (capEl) capEl.innerText = maxCapacity;
                }
            }

            const carNum = curIdx + 1;
            const carCode = curObj.id || currentCarCode;
            const carPlate = curObj.plate || '';
            const carRoute = curObj.route || 'สาย 1: หน้ามรย-หอพัก';
            const driverName = curObj.driver || 'พนักงานขับรถ';
            const color = getVehicleColor(curIdx);

            const cBadge = document.getElementById('card-vehicle-badge-circle');
            if (cBadge) { cBadge.innerText = carNum; cBadge.style.background = color.bg; }

            const cCode = document.getElementById('card-car-code');
            if (cCode) cCode.innerText = carCode;
            
            const cPlate = document.getElementById('card-car-plate');
            if (cPlate) cPlate.innerText = carPlate ? `(${carPlate})` : '';
            
            const cRoute = document.getElementById('card-route-name');
            if (cRoute) cRoute.innerText = carRoute;

            const cDriver = document.getElementById('card-driver-name');
            if (cDriver) cDriver.innerText = driverName;

            const hNum = document.getElementById('header-car-num-badge');
            if (hNum) { hNum.innerText = carNum; hNum.style.background = color.bg; }

            const hPlate = document.getElementById('header-car-plate-text');
            if (hPlate) hPlate.innerText = carPlate ? `(${carPlate})` : '';
            
            const hDriver = document.getElementById('header-driver-name-text');
            if (hDriver) hDriver.innerText = driverName;

            const loggedNameEl = document.getElementById('driver-logged-name');
            if (loggedNameEl && driverName && driverName !== 'พนักงานขับรถ') {
                loggedNameEl.innerText = driverName;
            }
            const dropNameEl = document.getElementById('dropdown-driver-name');
            if (dropNameEl && driverName) { dropNameEl.innerHTML = `<span>พนักงานขับรถ: <strong class="text-pink-600">${driverName}</strong></span>`; }

            const routeDisp = document.getElementById('route-name-display');
            if (routeDisp) routeDisp.innerText = carRoute;

            const driverDisp = document.getElementById('driver-name-display');
            if (driverDisp) driverDisp.innerText = `พนักงานขับรถ: ${driverName}`;

            if (typeof updateSeatsUI === 'function') updateSeatsUI();
            if (typeof syncVehicleWorkState === 'function') syncVehicleWorkState();
            if (typeof updateWorkButtonsUI === 'function') updateWorkButtonsUI();
        }
    }

    window.resetSeatsOccupied = function() {
        currentOccupied = 0;
        updateSeatsUI();
    }

    window.changeSeatsOccupied = function(change) {
        const activeCar = typeof currentCarCode !== 'undefined' ? currentCarCode : 'EV-01';

        if (change === "reset" || change === 0) {
            currentOccupied = 0;
        } else {
            currentOccupied = Math.max(0, Math.min(maxCapacity, currentOccupied + change));
        }
        try {
            localStorage.setItem(`yru_seats_${activeCar}`, currentOccupied.toString());
        } catch(e) {}
        updateSeatsUI();
    }

    let lastSentOccupied = -1;

    function updateSeatsUI() {
        const disp = document.getElementById('seats-occupied-display');
        if (disp) disp.innerText = currentOccupied;

        const pct = Math.round((currentOccupied / maxCapacity) * 100);
        const pctEl = document.getElementById('seats-percentage');
        if (pctEl) pctEl.innerText = pct + '%';

        const bar = document.getElementById('seats-progress-bar');
        if (bar) {
            bar.style.width = pct + '%';
            if (pct >= 100) {
                bar.className = 'bg-rose-600 h-full rounded-full transition-all duration-300';
            } else if (pct >= 75) {
                bar.className = 'bg-amber-500 h-full rounded-full transition-all duration-300';
            } else {
                bar.className = 'bg-blue-600 h-full rounded-full transition-all duration-300';
            }
        }

        const activeCarCode = typeof currentCarCode !== 'undefined' ? currentCarCode : (typeof currentCarId !== 'undefined' ? currentCarId : 'EV-01');

        if (typeof globalCarStatus !== 'undefined') {
            const carInfo = globalCarStatus[activeCarCode] || {};
            carInfo.occupied = currentOccupied;
            carInfo.capacity = maxCapacity;
            globalCarStatus[activeCarCode] = carInfo;
            try { 
                localStorage.setItem('yru_car_status_' + activeCarCode, JSON.stringify(carInfo)); 
                localStorage.setItem('yru_seats_' + activeCarCode, currentOccupied.toString());
            } catch(e) {}
        }

        // แจ้งเตือน BroadcastChannel ให้ทุกหน้าจอ (รวมหน้าผู้โดยสาร /home) อัปเดตทันทีแบบ Real-time
        try {
            const bc = new BroadcastChannel('yru_car_status_channel');
            bc.postMessage({ 
                type: 'seats_updated', 
                car_id: activeCarCode, 
                occupied: currentOccupied,
                capacity: maxCapacity 
            });
        } catch(e) {}

        if (lastSentOccupied !== currentOccupied) {
            lastSentOccupied = currentOccupied;
            fetch('/api/driver/update-seats', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ car_id: activeCarCode, occupied: currentOccupied, capacity: maxCapacity })
            })
            .then(res => res.json())
            .then(data => console.log('Seats updated real-time:', data))
            .catch(err => console.error('Error updating seats occupied:', err));
        }
    }

    window.openIssueReportPrompt = function() {
        if (typeof Swal === 'undefined') {
            const issue = prompt("กรุณาระบุปัญหา (เช่น ไฟชาร์จไม่เข้า, ลมยางอ่อน):");
            if (!issue) return;
            fetch('/api/maintenance/report', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ car_id: currentCarCode, issue: issue, details: '' })
            });
            return;
        }

        const activeCar = typeof currentCarCode !== 'undefined' ? currentCarCode : (typeof currentCarId !== 'undefined' ? currentCarId : 'EV-01');

        // Look up tram object from all available lists
        let foundTram = null;
        if (typeof trams !== 'undefined' && Array.isArray(trams)) {
            foundTram = trams.find(t => t.id === activeCar);
        }
        if (!foundTram && typeof allTramsList !== 'undefined' && Array.isArray(allTramsList)) {
            foundTram = allTramsList.find(t => t.id === activeCar);
        }
        if (!foundTram && typeof defaultTrams !== 'undefined' && Array.isArray(defaultTrams)) {
            foundTram = defaultTrams.find(t => t.id === activeCar);
        }

        // Driver name resolution hierarchy
        const cardDriverNameEl = document.getElementById('card-driver-name');
        const cardDriverText = cardDriverNameEl ? cardDriverNameEl.innerText.trim() : '';

        const fallbackDrivers = {
            'EV-01': 'นายอัสมี มูเล็ง',
            'EV-02': 'นายอัรฟาน มะเระ',
            'EV-03': 'นายซูเฟียน มะโละ',
            'EV-04': 'นายอุสมาน สาและ',
            'EV-05': 'นายบัดรี สาและ',
            'EV-06': 'นายตอริก ลือแมะ',
            'EV-07': 'นายสมหวัง ใจดี',
            'EV-08': 'นายสมใจ ใจดี',
            'EV-09': 'นายกิตติ ตั้งใจ',
            'EV-10': 'นายรุสลัน สอเฮาะ'
        };

        let driverName = (foundTram && foundTram.driver) ? foundTram.driver : '';
        if (!driverName && cardDriverText && cardDriverText !== 'พนักงานขับรถ') {
            driverName = cardDriverText;
        }
        if (!driverName && typeof loggedInDriver !== 'undefined' && loggedInDriver && loggedInDriver.name) {
            driverName = loggedInDriver.name;
        }
        if (!driverName && typeof currentDriverName !== 'undefined' && currentDriverName && currentDriverName !== 'พนักงานขับรถ') {
            driverName = currentDriverName;
        }
        if (!driverName) {
            driverName = fallbackDrivers[activeCar] || 'นายอัสมี มูเล็ง';
        }

        // Plate resolution
        const fallbackPlates = {
            'EV-01': 'กค 1234 ยะลา',
            'EV-02': 'กค 5678 ยะลา',
            'EV-03': 'กค 9012 ยะลา',
            'EV-04': 'กค 3456 ยะลา',
            'EV-05': 'กค 7890 ยะลา',
            'EV-06': 'กค 1122 ยะลา',
            'EV-07': 'กค 3344 ยะลา',
            'EV-08': 'กค 5566 ยะลา',
            'EV-09': 'กค 7788 ยะลา',
            'EV-10': 'กค 9900 ยะลา'
        };
        const cardPlateEl = document.getElementById('card-car-plate');
        const cardPlateText = cardPlateEl ? cardPlateEl.innerText.replace(/[()]/g, '').trim() : '';
        const plateNo = (foundTram && foundTram.plate) ? foundTram.plate : (cardPlateText || fallbackPlates[activeCar] || 'กค 9012 ยะลา');

        // 0. ตรวจสอบว่ารถคันนี้มีคำขอแจ้งซ่อมที่ "รอการอนุมัติ" ค้างอยู่ในระบบแล้วหรือไม่ (จำกัด 1 คัน ต่อ 1 เคสค้างอนุมัติ)
        let existingTicketsCheck = [];
        try {
            existingTicketsCheck = JSON.parse(localStorage.getItem('yru_maintenance_tickets_v3') || '[]');
            if (Array.isArray(existingTicketsCheck)) {
                const idsToDelete = ['MNT-0001', 'MNT-0002', 'MNT-0003', 'MNT-2569-D1BB'];
                const filtered = existingTicketsCheck.filter(t => !idsToDelete.includes(String(t.ticket_no || t.id)));
                if (filtered.length !== existingTicketsCheck.length) {
                    existingTicketsCheck = filtered;
                    localStorage.setItem('yru_maintenance_tickets_v3', JSON.stringify(existingTicketsCheck));
                }
            } else {
                existingTicketsCheck = [];
            }
        } catch(e) {}

        if (!window._bypassPendingCheck) {
            const pendingTicket = existingTicketsCheck.find(t => {
                const carId = t.tram_id || t.car_id;
                const status = (t.status || '').toLowerCase().trim();
                return carId === activeCar && (status === 'pending' || status === 'pending_supervisor' || status === 'รอการอนุมัติ');
            });

            if (pendingTicket) {
                const urgencyBg = pendingTicket.urgency === 'วิกฤต' ? 'bg-rose-100 text-rose-800 border-rose-300' : (pendingTicket.urgency === 'ด่วน' ? 'bg-amber-100 text-amber-800 border-amber-300' : 'bg-emerald-100 text-emerald-800 border-emerald-300');
                Swal.fire({
                    icon: 'warning',
                    title: '<div class="text-slate-800 font-extrabold text-lg md:text-xl pt-1">มีคำขอแจ้งซ่อมค้างอยู่</div>',
                    html: `
                        <div class="text-center font-kanit text-xs md:text-sm text-slate-600 space-y-3 pt-2">
                            <p>ขบวนรถ <b class="text-pink-600 font-bold">${activeCar}</b> มีรายการแจ้งซ่อมที่รอหัวหน้ายานพาหนะตรวจสอบอยู่</p>
                            <div class="bg-amber-50/80 border border-amber-200 p-3.5 rounded-2xl text-left space-y-1.5 shadow-xs">
                                <div class="flex justify-between items-center text-xs">
                                    <span class="font-bold text-slate-800 font-mono">รหัสคำขอ: #${pendingTicket.id || pendingTicket.ticket_no}</span>
                                    <span class="px-2 py-0.5 rounded-full text-[10px] font-bold border ${urgencyBg}">${pendingTicket.urgency || 'ด่วน'}</span>
                                </div>
                                <p class="text-xs text-slate-700 font-semibold"><i class="fas fa-tools text-amber-500 mr-1"></i> ${pendingTicket.issue || 'แจ้งซ่อม'}</p>
                                <p class="text-[11px] text-slate-500"><i class="far fa-clock mr-1"></i> แจ้ง: ${pendingTicket.date || '-'}</p>
                                <div class="pt-1 flex items-center gap-1.5 text-amber-700 font-bold text-[11px]">
                                    <i class="fas fa-hourglass-half text-[10px]"></i> สถานะ: รอหัวหน้ายานพาหนะตรวจสอบ
                                </div>
                            </div>
                            <p class="text-[11px] text-slate-400">เมื่อหัวหน้ายานพาหนะตรวจสอบและพิจารณาแล้ว ท่านจะสามารถส่งรายการใหม่ได้ทันทีครับ</p>
                        </div>
                    `,
                    showConfirmButton: true,
                    confirmButtonText: 'รับทราบ',
                    confirmButtonColor: '#f59e0b',
                    customClass: {
                        popup: 'rounded-[32px] shadow-2xl border border-amber-100 p-6 md:p-7 font-kanit max-w-md',
                        confirmButton: 'bg-amber-500 hover:bg-amber-600 text-white font-bold text-xs py-2.5 px-6 rounded-xl shadow-md transition'
                    }
                });
                return;
            }
        }
        window._bypassPendingCheck = false;

        const now = new Date();
        const thaiMonths = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', '', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
        const thaiDay = now.getDate();
        const thaiMonth = thaiMonths[now.getMonth()];
        const thaiYear = now.getFullYear() > 2500 ? now.getFullYear() : now.getFullYear() + 543;

        let attachedImagesBase64 = [];

        Swal.fire({
            title: '',
            html: `
                <div class="text-left font-kanit space-y-3.5 pt-1">
                    <!-- [ ส่วนหัวเอกสาร ] -->
                    <div class="bg-gradient-to-r from-pink-700 via-pink-600 to-rose-600 text-white p-4 rounded-2xl shadow-md text-center space-y-1">
                        <div class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full bg-white/20 text-white text-[10px] font-bold backdrop-blur-xs">
                            <i class="fas fa-file-signature"></i> แบบฟุ
                        </div>
                        <h3 class="font-extrabold text-base md:text-lg tracking-tight text-white">ใบแจ้งซ่อมและนำส่งรถไฟฟ้าเพื่อซ่อมบำรุง</h3>
                        <p class="text-[11px] text-pink-100 font-medium leading-tight">งานธุรการและยานพาหนะ กองกลาง สำนักงานอธิการบดี มหาวิทยาลัยราชภัฏยะลา</p>
                        <div class="mt-2 pt-2 border-t border-white/20 text-xs font-semibold flex flex-wrap justify-center items-center gap-2 text-pink-50">
                            <span><i class="fas fa-calendar-alt text-[11px]"></i> วันที่ <b>${thaiDay}</b></span>
                            <span>เดือน <b>${thaiMonth}</b></span>
                            <span>พ.ศ. <b>${thaiYear}</b></span>
                        </div>
                    </div>

                    <!-- [ ข้อมูลผู้แจ้ง (พนักงานขับรถ) ] -->
                    <div class="bg-slate-50 border border-slate-200/90 rounded-2xl p-3.5 space-y-2.5 text-xs text-slate-700">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-2.5">
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">
                                    ข้าพเจ้า (พนักงานขับรถ) <span class="text-pink-600">*</span>
                                </label>
                                <input type="text" id="swal-driver-name" value="${driverName}" oninput="document.getElementById('swal-sign-display').innerText=this.value;" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 focus:bg-white focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 outline-none transition" placeholder="ระบุชื่อ-ุ พนักงานขับรถ">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">
                                    ทะเบียน / ขบวนรถ <span class="text-pink-600">*</span>
                                </label>
                                <input type="text" id="swal-car-plate" value="${plateNo} (${activeCar})" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs font-bold text-pink-600 focus:bg-white focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 outline-none transition" placeholder="ุ">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">ยี่ห้อ</label>
                                <input type="text" id="swal-car-brand" value="YRU EV (ไฟฟ้าราชภัฏ)" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:bg-white focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 outline-none transition" placeholder="ยี่ห้อรถ">
                            </div>
                            <div>
                                <label class="block text-[11px] font-bold text-slate-600 mb-1">รุ่น</label>
                                <input type="text" id="swal-car-model" value="Electric Shuttle 2024" class="w-full bg-white border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 focus:bg-white focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 outline-none transition" placeholder="รุ่นรถ">
                            </div>
                        </div>

                        <div>
                            <label class="block text-[11px] font-bold text-slate-700 mb-1">มาตรวัดระยะทางปัจจุบัน</label>
                            <div class="relative">
                                <i class="fas fa-tachometer-alt text-slate-400 absolute left-3 top-1/2 -translate-y-1/2 text-xs"></i>
                                <input type="text" id="swal-odometer" value="12,450 กม." class="w-full bg-white border border-slate-200 rounded-xl pl-8 pr-3 py-2 text-xs font-semibold text-slate-800 focus:bg-white focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 outline-none transition" placeholder="เช่น 12,450 กม.">
                            </div>
                        </div>
                    </div>

                    <!-- [ รายการขอตรวจซุ่ 5 รายการ ] -->
                    <div class="space-y-2">
                        <div class="flex items-center justify-between">
                            <label class="block text-xs font-bold text-slate-800">
                                ขอแจ้ง ดังต่อไปนี้ <span class="text-pink-600 font-bold">*</span>
                            </label>
                            <span class="text-[10px] text-slate-400 font-medium">(กรุ 1 รายการ)</span>
                        </div>
                        <div class="space-y-1.5">
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-pink-100 text-pink-700 font-bold text-xs flex items-center justify-center flex-shrink-0">1</span>
                                <input type="text" id="swal-item-1" class="flex-1 bg-[#f8fafc] border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 outline-none transition" placeholder="รายการที่ 1 (จำเป็น) เช่น ผ้าเบรกกหมด / ">
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 font-bold text-xs flex items-center justify-center flex-shrink-0">2</span>
                                <input type="text" id="swal-item-2" class="flex-1 bg-[#f8fafc] border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 outline-none transition" placeholder="รายการที่ 2 (ถ้ามี) เช่น ไฟ">
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 font-bold text-xs flex items-center justify-center flex-shrink-0">3</span>
                                <input type="text" id="swal-item-3" class="flex-1 bg-[#f8fafc] border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 outline-none transition" placeholder="รายการที่ 3 (ถ้ามี) เช่น ตรวจเช็คแบตเตอรี่และระบบขับเคลื่อน">
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 font-bold text-xs flex items-center justify-center flex-shrink-0">4</span>
                                <input type="text" id="swal-item-4" class="flex-1 bg-[#f8fafc] border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 outline-none transition" placeholder="รายการที่ 4 (ถ้ามี)">
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-slate-100 text-slate-600 font-bold text-xs flex items-center justify-center flex-shrink-0">5</span>
                                <input type="text" id="swal-item-5" class="flex-1 bg-[#f8fafc] border border-slate-200 rounded-xl px-3 py-2 text-xs text-slate-800 placeholder-slate-400 focus:bg-white focus:border-pink-500 focus:ring-2 focus:ring-pink-500/20 outline-none transition" placeholder="รายการที่ 5 (ถ้ามี)">
                            </div>
                        </div>
                    </div>

                    <!-- [ ส่วนลงชื่อพนักงานขับรถ ] -->
                    <div class="p-3 bg-pink-50/60 border border-pink-100 rounded-xl text-xs flex justify-between items-center text-slate-700 mt-2">
                        <span class="text-[11px] text-slate-500"><i class="fas fa-pen-fancy text-pink-600 mr-1"></i> ผู้ยื่นขออนุญาตซ่อม:</span>
                        <span class="font-bold text-slate-800">ลงชื่อ <u id="swal-sign-display">${driverName}</u> (พนักงานขับรถ)</span>
                    </div>
                </div>
            `,
            showCancelButton: true,
            confirmButtonText: '<i class="fas fa-paper-plane mr-1.5"></i> ยืนยันส่งใบขออนุญาตซ่อม',
            cancelButtonText: 'ยก',
            confirmButtonColor: '#EC4899',
            cancelButtonColor: '#8c9ba5',
            customClass: {
                popup: 'rounded-[28px] shadow-2xl border border-slate-200 p-4 md:p-6 font-kanit max-w-lg w-full',
                actions: 'flex items-center justify-center gap-3 w-full mt-3',
                confirmButton: 'bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs py-3 px-6 rounded-2xl shadow-lg shadow-pink-500/25 active:scale-95 transition',
                cancelButton: 'bg-slate-500 hover:bg-slate-600 text-white font-bold text-xs py-3 px-6 rounded-2xl shadow-sm active:scale-95 transition'
            },
            preConfirm: () => {
                const driverInput = document.getElementById('swal-driver-name')?.value.trim() || driverName;
                const plateInput = document.getElementById('swal-car-plate')?.value.trim() || plateNo;
                const brandInput = document.getElementById('swal-car-brand')?.value.trim() || 'YRU EV';
                const modelInput = document.getElementById('swal-car-model')?.value.trim() || 'Electric Shuttle';
                const odoInput = document.getElementById('swal-odometer')?.value.trim() || '-';

                const items = [];
                for (let i = 1; i <= 5; i++) {
                    const itVal = document.getElementById(`swal-item-${i}`)?.value.trim();
                    if (itVal) items.push(itVal);
                }

                if (items.length === 0) {
                    Swal.showValidationMessage('กรุณาระบุรายการตรวจซ่อมบำรุงในข้อ 1 อย่างน้อย 1 รายการ!');
                    return false;
                }

                return {
                    driver: driverInput,
                    plate: plateInput,
                    brand: brandInput,
                    model: modelInput,
                    odometer: odoInput,
                    urgency: 'ด่วน',
                    items: items
                };
            }
        }).then((result) => {
            if (result.isConfirmed && result.value) {
                const { driver, plate, brand, model, odometer, urgency, items } = result.value;

                const primaryIssue = items.map((it, idx) => `${idx + 1}. ${it}`).join(' | ');
                const formattedDocDetails = `ใบแจ้งซ่อมและนำส่งรถไฟฟ้าเพื่อซ่อมบำรุง (งานธุรการและยานพาหนะ กองกลาง สำนักงานอธิการบดี มหาวิทยาลัยราชภัฏยะลา)\n ผู้อำนวยการสำนักงานอธิการบดี\nข้าพเจ้า ${driver} พนักงานขับรถ ${plate} ยี่ห้อ ${brand} รุ่น ${model}\nมาตรวัดระยะทางปัจจุบัน ${odometer}\nขอแจ้ง ดังต่อไปนี้:\n` + items.map((it, idx) => `${idx + 1}. ${it}`).join('\n');

                // 1. คำนวณรุ (MNT-XXXX) จากข้อมูลที่มีุ
                let existingTickets = [];
                try {
                    existingTickets = JSON.parse(localStorage.getItem('yru_maintenance_tickets_v3') || '[]');
                    if (!Array.isArray(existingTickets)) existingTickets = [];
                } catch(e) {}

                let maxIdNum = 0;
                existingTickets.forEach(t => {
                    if (t && t.id) {
                        const match = String(t.id).match(/(\d+)/);
                        if (match && match[1]) {
                            maxIdNum = Math.max(maxIdNum, parseInt(match[1], 10));
                        }
                    }
                });
                const nextIdNum = maxIdNum + 1;
                const autoGeneratedId = `MNT-${String(nextIdNum).padStart(4, '0')}`;

                // Color badge helper
                let urgencyColor = 'bg-amber-100 text-amber-800';
                if (urgency === 'วิกฤต') urgencyColor = 'bg-rose-100 text-rose-800';
                else if (urgency === 'ปกติ') urgencyColor = 'bg-emerald-100 text-emerald-800';

                const newTicket = {
                    id: autoGeneratedId,
                    ticket_id: autoGeneratedId,
                    tram_id: activeCar,
                    car_id: activeCar,
                    plate: plate,
                    brand: brand,
                    model: model,
                    odometer: odometer,
                    issue: primaryIssue,
                    repair_items: items,
                    parts: 'ตรวจซุ่',
                    details: formattedDocDetails,
                    images: attachedImagesBase64 || [],
                    reporter: `${driver} (พนักงานขับรถ)`,
                    date: `${thaiDay}/${String(now.getMonth()+1).padStart(2,'0')}/${thaiYear} ${new Date().toLocaleTimeString('th-TH', {hour:'2-digit', minute:'2-digit'})} น.`,
                    urgency: urgency,
                    urgency_color: urgencyColor,
                    status: 'pending',
                    approver: '-',
                    remarks: ''
                };

                // 2. บันทึกลง LocalStorage
                try {
                    existingTickets.unshift(newTicket);
                    localStorage.setItem('yru_maintenance_tickets_v3', JSON.stringify(existingTickets));
                } catch(e) {}

                // 3. Broadcast Live to Executive Dashboard
                try {
                    const syncBc = new BroadcastChannel('yru_trams_realtime_sync');
                    syncBc.postMessage({
                        type: 'NEW_MAINTENANCE_TICKET',
                        ticket: newTicket
                    });
                } catch(e) {}

                // 4. บันทึกลง Backend API & Database
                fetch('/api/maintenance/report', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({
                        ticket_id: autoGeneratedId,
                        car_id: activeCar,
                        plate: plate,
                        issue: primaryIssue,
                        repair_items: items,
                        odometer: odometer,
                        brand: brand,
                        model: model,
                        parts: 'ตรวจซุ่',
                        urgency: urgency,
                        details: formattedDocDetails,
                        reporter: driver,
                        images: attachedImagesBase64 || []
                    })
                })
                .then(res => res.json())
                .then(data => {
                    if (data && data.ticket_id && data.ticket_id !== autoGeneratedId) {
                        const officialId = data.ticket_id;
                        newTicket.id = officialId;
                        newTicket.ticket_id = officialId;
                        try {
                            let curTickets = JSON.parse(localStorage.getItem('yru_maintenance_tickets_v3') || '[]');
                            if (Array.isArray(curTickets)) {
                                const idx = curTickets.findIndex(x => x.id === autoGeneratedId);
                                if (idx !== -1) curTickets[idx] = newTicket;
                                localStorage.setItem('yru_maintenance_tickets_v3', JSON.stringify(curTickets));
                            }
                        } catch(e) {}

                        try {
                            const syncBc = new BroadcastChannel('yru_trams_realtime_sync');
                            syncBc.postMessage({
                                type: 'NEW_MAINTENANCE_TICKET',
                                ticket: newTicket
                            });
                        } catch(e) {}
                    }
                })
                .catch(err => console.error('Error syncing maintenance report:', err));

                // 5. แสดงกล่องข้อความแจ้งเตือนตรงกลางหน้าจอ (Center Modal)
                Swal.fire({
                    icon: 'success',
                    title: '<div class="text-slate-800 font-extrabold text-lg md:text-xl pt-1">ยื่นใบขออนุญาตซ่อมสำเร็จ</div>',
                    html: `
                        <div class="text-center font-kanit text-slate-600 text-xs md:text-sm mt-2 space-y-1.5">
                            <p class="font-bold text-pink-600 text-sm">: #${autoGeneratedId}</p>
                            <p class="text-slate-700">ู</p>
                            <p class="text-[11px] text-slate-400">ท่านสามารถปฏิบัติหน้าที่ตามปกติระหว่างรอผู้บริหารพิจารณาอนุมัติ</p>
                        </div>
                    `,
                    confirmButtonText: '<i class="fas fa-check mr-1.5"></i> รับทราบ',
                    confirmButtonColor: '#EC4899',
                    timer: 5000,
                    timerProgressBar: true,
                    customClass: {
                        popup: 'rounded-[28px] shadow-2xl p-6 font-kanit max-w-sm border border-slate-100',
                        confirmButton: 'bg-pink-600 hover:bg-pink-700 text-white font-bold text-xs py-2.5 px-6 rounded-xl shadow-md transition'
                    }
                });
            }
        });
    }

    // ส่งสัญญาณฉุกเฉิน SOS
    window.triggerSOSAlert = function() {
        if (confirm('🚨 คำเตือน: คุณต้องการส่งสัญญาณแจ้งเหตุฉุกเฉิน (SOS) ไปยังศูนย์ควบคุมใช่หรือไม่?')) {
            fetch('/api/trigger-sos', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify({ car_id: currentCarCode })
            })
            .then(res => res.json())
            .then(data => {
                if (data.status === 'success') {
                    alert('🚨 ส่งสัญญาณ SOS และแจ้งเตือนฉุกเฉินไปยังแผนกควบคุมแล้ว!');
                }
            })
            .catch(err => {
                console.error('Error sending SOS:', err);
                alert('เกิดข้อผิดพลาดในการส่งสัญญาณฉุกเฉิน');
            });
        }
    }

    window.incrementCarRoundNumber = function() {
        const activeCar = typeof currentCarCode !== 'undefined' ? currentCarCode : 'EV-01';
        currentRoundNumber += 1;
        
        try {
            localStorage.setItem('yru_round_' + activeCar, currentRoundNumber.toString());
            const todayKey = new Date().toISOString().slice(0, 10);
            let dailyRounds = parseInt(localStorage.getItem(`yru_daily_rounds_${activeCar}_${todayKey}`) || '0') + 1;
            localStorage.setItem(`yru_daily_rounds_${activeCar}_${todayKey}`, dailyRounds.toString());
        } catch(e) {}
        
        updateRoundDisplay();
    };

    window.isVehicleAdminLocked = false;

    window.checkAdminVehicleLockStatus = function() {
        window.isVehicleAdminLocked = false;

        const btnContainer = document.getElementById('driver-status-buttons');
        const startWorkContainer = document.getElementById('start-work-container');
        const breakActionContainer = document.getElementById('break-action-container');
        const roundActionContainer = document.getElementById('round-action-container');
        const lockBadge = document.getElementById('admin-lock-badge');
        const lockMsg = document.getElementById('admin-lock-msg');

        if (btnContainer) btnContainer.classList.remove('opacity-40', 'pointer-events-none', 'filter', 'grayscale-[60%]');
        if (startWorkContainer) startWorkContainer.classList.remove('opacity-40', 'pointer-events-none', 'filter', 'grayscale-[60%]');
        if (breakActionContainer) breakActionContainer.classList.remove('opacity-40', 'pointer-events-none', 'filter', 'grayscale-[60%]');
        if (roundActionContainer) roundActionContainer.classList.remove('opacity-40', 'pointer-events-none', 'filter', 'grayscale-[60%]');
        if (lockBadge) lockBadge.classList.add('hidden');
        if (lockMsg) lockMsg.classList.add('hidden');
    };

    // ๐ ฟังก์ชันจัดการุ (รวมการ UI และยิง API)
    window.syncDriverStatus = function(statusType) {
        checkAdminVehicleLockStatus();
        if (window.isVehicleAdminLocked) {
            return;
        }

        const items = document.querySelectorAll('.status-item');
        items.forEach(i => {
            if(i.getAttribute('data-type') === statusType) {
                i.classList.add('active');
            } else {
                i.classList.remove('active');
            }
        });

        // 1. Update tram object in trams array & localStorage
        const curTram = trams.find(t => t.id === currentCarCode || t.id === 'EV-' + currentCarId || t.id === 'EV-0' + currentCarId);
        if (curTram) {
            if (statusType === 'normal') curTram.status = 'พร้อมใช้งาน';
            else if (statusType === 'pause') curTram.status = 'พักเบรก';
            else if (statusType === 'broken') curTram.status = 'รถขัดข้อง';
            
            try { localStorage.setItem('yru_trams_v18', JSON.stringify(trams)); } catch(e) {}
        }

        // 2. Update globalCarStatus for this car
        if (typeof globalCarStatus !== 'undefined') {
            const carInfo = globalCarStatus[currentCarCode] || {};
            if (statusType === 'normal') carInfo.status = 'พร้อมใช้งาน';
            else if (statusType === 'pause') carInfo.status = 'พักเบรก';
            else if (statusType === 'broken') carInfo.status = 'รถขัดข้อง';
            globalCarStatus[currentCarCode] = carInfo;
            try { localStorage.setItem('yru_car_status_' + currentCarCode, JSON.stringify(carInfo)); } catch(e) {}
        }

        // 3. Re-draw vehicle markers on map
        if (typeof initTramMarkers === 'function') {
            initTramMarkers();
        }

        // Native localStorage item modification already dispatches to other tabs natively.

        const gpsStatus = document.getElementById('gps-status-text');

        // อัปเดตข้อความแถบแสดงผลฝั่งคนขับ
        if(statusType === 'normal') {
            if (gpsStatus) { gpsStatus.innerHTML = '<span class="text-emerald-500">● ออนไลน์</span>'; gpsStatus.style.color = "#10b981"; }
        } else if(statusType === 'pause') {
            if (gpsStatus) { gpsStatus.innerHTML = '<span class="text-amber-500">● หยุดพักชั่วคราว</span>'; gpsStatus.style.color = "#f59e0b"; }
        } else if(statusType === 'broken') {
            if (gpsStatus) { gpsStatus.innerHTML = '<span class="text-red-500">● ออฟไลน์ (รถเสีย)</span>'; gpsStatus.style.color = "#ef4444"; }
        }

        const badgeContainer = document.getElementById('status-badge-container');
        if (badgeContainer) {
            if (statusType === 'normal') {
                badgeContainer.innerHTML = `
                    <span id="status-badge" class="bg-green-500/10 border border-green-500/20 text-green-400 px-3 py-1.5 rounded-xl flex items-center gap-1.5">
                        <i class="fas fa-circle text-green-500 text-[8px]"></i> กำลังเดินรถ
                    </span>
                `;
            } else if (statusType === 'pause') {
                badgeContainer.innerHTML = `
                    <span id="status-badge" class="bg-amber-500/10 border border-amber-500/20 text-amber-400 px-3 py-1.5 rounded-xl flex items-center gap-1.5">
                        <i class="fas fa-circle text-amber-500 text-[8px]"></i> หยุดพัก
                    </span>
                `;
            } else if (statusType === 'broken') {
                badgeContainer.innerHTML = `
                    <span id="status-badge" class="bg-red-500/10 border border-red-500/20 text-red-400 px-3 py-1.5 rounded-xl flex items-center gap-1.5">
                        <i class="fas fa-circle text-red-500 text-[8px]"></i> รถหยุด
                    </span>
                `;
            }
        }

        // อัปเดตการ์ดจัดการช่วงพัก (Break Status Card UI)
        const breakBadge = document.getElementById('break-status-badge');
        const breakActionContainer = document.getElementById('break-action-container');
        if (breakBadge && breakActionContainer) {
            if (statusType === 'pause') {
                breakBadge.className = 'text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-amber-50 text-amber-600 border border-amber-200';
                breakBadge.innerText = 'กำลังพัก';
                breakActionContainer.innerHTML = `
                    <button id="btn-resume-work" onclick="toggleBreakStatus(false)" class="w-full h-12 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold px-1 rounded-xl flex items-center justify-center text-center leading-tight transition text-xs shadow-md shadow-emerald-600/20 font-kanit">
                        กลับมาให้บริการ
                    </button>
                `;
            } else {
                breakBadge.className = 'text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-100';
                breakBadge.innerText = 'ให้บริการปกติ';
                breakActionContainer.innerHTML = `
                    <button id="btn-take-break" onclick="toggleBreakStatus(true)" class="w-full h-12 bg-amber-500 hover:bg-amber-600 active:scale-95 text-white font-bold px-1.5 rounded-xl flex items-center justify-center gap-1.5 transition text-xs shadow-md shadow-amber-500/20 font-kanit">
                        พัก
                    </button>
                `;
            }
            if (typeof updateWorkButtonsUI === 'function') updateWorkButtonsUI();
            if (typeof checkCurrentVehicleSuspensionLock === 'function') checkCurrentVehicleSuspensionLock();
        }

        fetch('/api/update-driver-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({
                status: statusType,
                car_id: currentCarCode,
                start_time: '-'
            })
        })
        .then(response => response.json())
        .then(data => console.log("Driver status synchronized:", data))
        .catch(error => console.error('Error synchronizing driver status:', error));
    } // End of window.syncDriverStatus

    window.isDriverWorking = false;
    window.isDriverOnBreak = false;

    window.isDriverWorkActive = function() {
        const activeCar = typeof currentCarCode !== 'undefined' && currentCarCode ? currentCarCode : 'EV-01';
        const todayKey = new Date().toISOString().slice(0, 10);
        return window.isDriverWorking === true || localStorage.getItem(`yru_work_started_${activeCar}_${todayKey}`) === 'true';
    };

    window.disableDriverActionButtons = function() {
        const btnArrived = document.getElementById('btn-arrived-stop');
        const btnSkip = document.getElementById('btn-skip-stop');
        if (btnArrived) {
            btnArrived.setAttribute('disabled', 'disabled');
            btnArrived.classList.remove('bg-emerald-500', 'hover:bg-emerald-600', 'active:scale-95', 'cursor-pointer');
            btnArrived.className = 'bg-slate-200 text-slate-400 border border-slate-300 font-bold py-3.5 px-3 rounded-xl flex items-center justify-center gap-2 text-xs transition opacity-60 cursor-not-allowed pointer-events-none';
        }
        if (btnSkip) {
            btnSkip.setAttribute('disabled', 'disabled');
            btnSkip.classList.remove('bg-pink-100', 'hover:bg-pink-200', 'text-pink-700', 'active:scale-95', 'cursor-pointer');
            btnSkip.className = 'bg-slate-100 text-slate-400 border border-slate-200 font-bold py-3.5 px-3 rounded-xl flex items-center justify-center gap-2 text-xs transition opacity-60 cursor-not-allowed pointer-events-none';
        }
    };

    window.enableDriverActionButtons = function() {
        // ต้องเริ่มงานแล้ว และไม่ได้อยู่ในช่วงพักเบรก ถึงจะกดปุ่มได้
        if (!window.isDriverWorkActive() || window.isDriverOnBreak) {
            window.disableDriverActionButtons();
            return;
        }
        const btnArrived = document.getElementById('btn-arrived-stop');
        const btnSkip = document.getElementById('btn-skip-stop');
        if (btnArrived) {
            btnArrived.removeAttribute('disabled');
            btnArrived.classList.remove('opacity-60', 'cursor-not-allowed', 'pointer-events-none', 'bg-slate-200', 'text-slate-400', 'border-slate-300');
            btnArrived.className = 'bg-emerald-500 hover:bg-emerald-600 active:scale-95 text-white font-bold py-3.5 px-3 rounded-xl flex items-center justify-center gap-2 text-xs shadow-md shadow-emerald-500/20 transition cursor-pointer';
        }
        if (btnSkip) {
            btnSkip.removeAttribute('disabled');
            btnSkip.classList.remove('opacity-60', 'cursor-not-allowed', 'pointer-events-none', 'bg-slate-100', 'text-slate-400', 'border-slate-200');
            btnSkip.className = 'bg-pink-100 hover:bg-pink-200 text-pink-700 border border-pink-200 font-bold py-3.5 px-3 rounded-xl flex items-center justify-center gap-2 text-xs transition active:scale-95 cursor-pointer';
        }
    };

    window.updateWorkButtonsUI = function() {
        const todayKey = new Date().toISOString().slice(0, 10);
        const carIdStr = typeof currentCarCode !== 'undefined' && currentCarCode ? currentCarCode : 'EV-01';
        const isWorkStarted = window.isDriverWorkActive();

        const btnStart = document.getElementById('btn-start-work');
        const btnBreak = document.getElementById('btn-take-break');
        const btnEnd = document.getElementById('btn-end-work');
        const breakBadge = document.getElementById('break-status-badge');

        if (!isWorkStarted) {
            // 1. ยังไม่ได้เริ่มงาน: ปลดล็อกปุ่มเริ่มงาน / ล็อกปุ่มพักเบรกและปิดงาน / ล็อกปุ่มจุดจอด (สีเทา กดไม่ได้)
            if (btnStart) {
                btnStart.removeAttribute('disabled');
                btnStart.className = 'w-full h-12 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold px-1.5 rounded-xl flex items-center justify-center gap-1.5 transition text-xs shadow-md shadow-emerald-600/20 font-kanit cursor-pointer ring-2 ring-emerald-400/40';
                btnStart.innerHTML = 'เริ่มงาน';
            }
            if (btnBreak) {
                btnBreak.setAttribute('disabled', 'disabled');
                btnBreak.className = 'w-full h-12 bg-amber-100 text-amber-500/70 border border-amber-200/60 font-bold px-1.5 rounded-xl flex items-center justify-center gap-1.5 text-xs font-kanit opacity-50 cursor-not-allowed pointer-events-none transition';
                btnBreak.innerHTML = 'พัก';
                btnBreak.setAttribute('onclick', 'toggleBreakStatus(true)');
            }
            if (btnEnd) {
                btnEnd.setAttribute('disabled', 'disabled');
                btnEnd.className = 'w-full h-12 bg-rose-100 text-rose-500/70 border border-rose-200/60 font-bold px-1.5 rounded-xl flex items-center justify-center gap-1.5 text-xs font-kanit opacity-50 cursor-not-allowed pointer-events-none transition';
                btnEnd.innerHTML = 'เลิกงาน';
            }
            window.disableDriverActionButtons();
            if (breakBadge) {
                breakBadge.className = 'text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-slate-100 text-slate-500 border border-slate-200';
                breakBadge.innerText = 'ยังไม่เริ่มงาน';
            }
        } else {
            // 2. กดเริ่มงานเรียบร้อยแล้ว: ปลดล็อกปุ่มพักเบรกและปิดงาน / ปลดล็อกปุ่มจุดจอด (สีเขียว/ชมพู กดได้)
            if (btnStart) {
                btnStart.setAttribute('disabled', 'disabled');
                btnStart.className = 'w-full h-12 bg-emerald-50 text-emerald-700 border border-emerald-300 font-bold px-1.5 rounded-xl flex items-center justify-center gap-1.5 text-xs font-kanit opacity-80 cursor-not-allowed pointer-events-none shadow-xs';
                btnStart.innerHTML = '<i class="fas fa-check-circle text-emerald-600 text-xs"></i> กำลังวิ่งงาน';
            }
            if (btnBreak) {
                btnBreak.removeAttribute('disabled');
                btnBreak.classList.remove('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
                if (window.isDriverOnBreak) {
                    btnBreak.className = 'w-full h-12 bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-bold px-1.5 rounded-xl flex items-center justify-center gap-1.5 transition text-xs font-kanit cursor-pointer shadow-xs';
                    btnBreak.innerHTML = '▶️ กลับมาทำงาน';
                    btnBreak.setAttribute('onclick', 'toggleBreakStatus(false)');
                } else {
                    btnBreak.className = 'w-full h-12 bg-amber-200 hover:bg-amber-300 active:scale-95 text-amber-900 font-bold px-1.5 rounded-xl flex items-center justify-center gap-1.5 transition text-xs font-kanit cursor-pointer shadow-xs';
                    btnBreak.innerHTML = 'พัก';
                    btnBreak.setAttribute('onclick', 'toggleBreakStatus(true)');
                }
            }
            if (btnEnd) {
                btnEnd.removeAttribute('disabled');
                btnEnd.classList.remove('opacity-50', 'cursor-not-allowed', 'pointer-events-none');
                btnEnd.className = 'w-full h-12 bg-rose-200 hover:bg-rose-300 active:scale-95 text-rose-900 font-bold px-1.5 rounded-xl flex items-center justify-center gap-1.5 transition text-xs font-kanit cursor-pointer shadow-xs';
                btnEnd.innerHTML = 'เลิกงาน';
            }
            if (window.isDriverOnBreak) {
                window.disableDriverActionButtons();
                if (breakBadge) {
                    breakBadge.className = 'text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-700 border border-amber-300';
                    breakBadge.innerText = '🟡 ช่วงพักงาน';
                }
            } else {
                window.enableDriverActionButtons();
                if (breakBadge) {
                    breakBadge.className = 'text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-50 text-emerald-600 border border-emerald-100';
                    breakBadge.innerText = 'ให้บริการปกติ';
                }
            }
        }
    };

    window.syncDriverStatus = function(targetStatus) {
        const activeCar = typeof currentCarCode !== 'undefined' && currentCarCode ? currentCarCode : 'EV-01';
        let statusText = 'พร้อมใช้งาน';
        let driverStatusText = 'เริ่มงาน';

        if (targetStatus === 'pause' || targetStatus === 'พัก' || targetStatus === 'พักเบรค' || targetStatus === 'พักเบรก') {
            statusText = 'พักเบรค';
            driverStatusText = 'พักเบรค';
        } else {
            statusText = 'พร้อมใช้งาน';
            driverStatusText = targetStatus === 'ended' ? 'เลิกงาน' : 'เริ่มงาน';
        }

        // 1. Update yru_car_status_{activeCar} in localStorage
        try {
            let carStatusObj = {};
            const raw = localStorage.getItem('yru_car_status_' + activeCar);
            if (raw) {
                try { carStatusObj = JSON.parse(raw); } catch(e) {}
            }
            carStatusObj.status = statusText;
            carStatusObj.driver_status = driverStatusText;
            carStatusObj.work_state = targetStatus;
            carStatusObj.updated_at = new Date().toISOString();
            localStorage.setItem('yru_car_status_' + activeCar, JSON.stringify(carStatusObj));
        } catch(e) {}

        // 2. Update yru_trams_v18 in localStorage
        try {
            const tramsRaw = localStorage.getItem('yru_trams_v18');
            if (tramsRaw) {
                let tramsArr = JSON.parse(tramsRaw);
                if (Array.isArray(tramsArr)) {
                    let found = false;
                    tramsArr.forEach(t => {
                        if (t.id === activeCar) {
                            t.status = statusText;
                            found = true;
                        }
                    });
                    if (found) {
                        localStorage.setItem('yru_trams_v18', JSON.stringify(tramsArr));
                    }
                }
            }
        } catch(e) {}

        // 3. Post to backend update-driver-status API
        try {
            fetch('/api/update-driver-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
                },
                body: JSON.stringify({
                    car_id: activeCar,
                    status: targetStatus,
                    status_text: statusText
                })
            }).catch(e => {});
        } catch(e) {}

        // 4. Notify all tabs via BroadcastChannel & storage event
        try {
            const bc = new BroadcastChannel('yru_car_status_channel');
            bc.postMessage({ type: 'car_status_updated', car_id: activeCar, status: statusText });
        } catch(e) {}
        window.dispatchEvent(new Event('storage'));

        // 5. Update local map marker if present
        if (typeof tramMarkers !== 'undefined' && tramMarkers[activeCar]) {
            if (typeof initTramMarkers === 'function') initTramMarkers();
        }
    };

    window.startWorkAction = function() {
        checkAdminVehicleLockStatus();
        if (window.isVehicleAdminLocked) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'ไม่สามารถทำรายการได้',
                    text: 'รถถูกระงับการใช้งาน',
                    icon: 'error',
                    confirmButtonText: 'ตกลง',
                    customClass: { popup: 'font-kanit rounded-2xl' }
                });
            }
            return;
        }

        window.isDriverWorking = true;
        window.isDriverOnBreak = false;

        const activeCar = typeof currentCarCode !== 'undefined' && currentCarCode ? currentCarCode : 'EV-01';
        const todayKey = new Date().toISOString().slice(0, 10);
        const now = new Date();
        const startTimeStr = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')} น.`;

        // เริ่มต้นรอบการทำงานใหม่เสมอ: รีเซ็ตเวลาเริ่มงาน, จำนวนรอบเป็น 1 และผู้โดยสารเป็น 0 ใหม่หมด ไม่ดึงข้อมูลเดิม
        currentRoundNumber = 1;
        roundTotalPaxServed = 0;
        currentOccupied = 0;

        try {
            localStorage.setItem(`yru_work_started_${activeCar}_${todayKey}`, 'true');
            localStorage.setItem(`yru_daily_start_time_${activeCar}_${todayKey}`, startTimeStr);
            localStorage.setItem(`yru_shift_start_time_${activeCar}`, startTimeStr);
            localStorage.setItem(`yru_shift_pax_${activeCar}`, '0');
            localStorage.setItem(`yru_shift_rounds_${activeCar}`, '1');
            localStorage.setItem(`yru_round_${activeCar}`, '1');
            localStorage.setItem(`yru_daily_pax_${activeCar}_${todayKey}`, '0');
            localStorage.setItem(`yru_daily_rounds_${activeCar}_${todayKey}`, '1');
            localStorage.setItem(`yru_seats_${activeCar}`, '0');
        } catch(e) {}

        updateRoundDisplay();
        updateSeatsUI();

        if (typeof syncDriverStatus === 'function') {
            syncDriverStatus('normal');
        }

        // ปลดล็อกปุ่มกด "ถึงจุดจอดนี้แล้ว" และ "ข้ามจุดจอดนี้" ทันที
        window.enableDriverActionButtons();

        // ปรับแต่ง UI ของปุ่มเริ่มงาน/พัก/เลิกงาน
        window.updateWorkButtonsUI();

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                icon: 'success',
                title: '🟢 เริ่มปฏิบัติงานเดินรถเรียบร้อย',
                text: 'ระบบพร้อมรับ-ส่งผู้โดยสารและเปิดใช้งานปุ่มบันทึกจุดจอดแล้ว',
                timer: 2000,
                showConfirmButton: false,
                customClass: { popup: 'font-kanit rounded-2xl' }
            });
        }
    };

    window.toggleBreakStatus = function(isBreak) {
        checkAdminVehicleLockStatus();
        if (window.isVehicleAdminLocked) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'ไม่สามารถทำรายการได้',
                    text: 'รถถูกระงับการใช้งาน',
                    icon: 'error',
                    confirmButtonText: 'ตกลง',
                    customClass: { popup: 'font-kanit rounded-2xl' }
                });
            }
            return;
        }

        if (!window.isDriverWorkActive()) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'ยังไม่ได้เริ่มงาน',
                    text: 'กรุณากด "เริ่มงาน" ก่อนเข้าสู่ช่วงพักเบรกครับ',
                    icon: 'warning',
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#10B981',
                    customClass: { popup: 'font-kanit rounded-2xl' }
                });
            }
            return;
        }

        window.isDriverOnBreak = isBreak;
        const targetStatus = isBreak ? 'pause' : 'normal';
        if (typeof syncDriverStatus === 'function') {
            syncDriverStatus(targetStatus);
        }

        const alertEl = document.getElementById('next-stop-alert');
        if (isBreak) {
            if (alertEl) alertEl.classList.add('hidden');
            window.disableDriverActionButtons();
        } else {
            window.enableDriverActionButtons();
            if (window.activePassengerCalls && window.activePassengerCalls.length > 0) {
                renderCurrentQueueItem();
            }
        }

        window.updateWorkButtonsUI();

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: isBreak ? '🟡 เข้าสู่ช่วงพักเบรก' : '🟢 กลับมาให้บริการ',
                text: isBreak ? 'ปรับสถานะเป็นหยุดพักชั่วคราวและระงับปุ่มบันทึกจุดจอด' : 'พร้อมให้บริการเดินรถต่อเนื่องเรียบร้อยแล้ว',
                icon: isBreak ? 'warning' : 'success',
                timer: 1800,
                showConfirmButton: false,
                customClass: { popup: 'font-kanit rounded-2xl' }
            });
        }
    };

    // 🏁 --- ส่วนงานจัดการรอบการเดินรถ (Round Management) ---
    let currentRoundNumber = 0;
    let roundTotalPaxServed = 0;

    function syncVehicleWorkState() {
        const activeCar = typeof currentCarCode !== 'undefined' && currentCarCode ? currentCarCode : 'EV-01';
        const todayKey = new Date().toISOString().slice(0, 10);
        const isWorkStarted = (typeof window.isDriverWorkActive === 'function') ? window.isDriverWorkActive() : (localStorage.getItem(`yru_work_started_${activeCar}_${todayKey}`) === 'true');

        if (!isWorkStarted) {
            currentRoundNumber = 0;
            roundTotalPaxServed = 0;
            currentOccupied = 0;
            try {
                localStorage.setItem(`yru_round_${activeCar}`, '0');
                localStorage.setItem(`yru_seats_${activeCar}`, '0');
                localStorage.setItem(`yru_shift_pax_${activeCar}`, '0');
                localStorage.setItem(`yru_shift_rounds_${activeCar}`, '0');
                localStorage.setItem(`yru_daily_pax_${activeCar}_${todayKey}`, '0');
                localStorage.setItem(`yru_car_status_${activeCar}`, JSON.stringify({ occupied: 0, capacity: 10, total_pax: 0, total_rounds: 0 }));
            } catch(e) {}
        } else {
            currentRoundNumber = parseInt(localStorage.getItem(`yru_shift_rounds_${activeCar}`) || localStorage.getItem(`yru_round_${activeCar}`) || '1', 10);
            if (currentRoundNumber <= 0) currentRoundNumber = 1;
            roundTotalPaxServed = parseInt(localStorage.getItem(`yru_shift_pax_${activeCar}`) || '0', 10);
            if (isNaN(roundTotalPaxServed) || roundTotalPaxServed < 0) roundTotalPaxServed = 0;
            currentOccupied = parseInt(localStorage.getItem(`yru_seats_${activeCar}`) || '0', 10);
            if (isNaN(currentOccupied) || currentOccupied < 0) currentOccupied = 0;
        }
        updateRoundDisplay();
        updateSeatsUI();
        if (typeof updateWorkButtonsUI === 'function') updateWorkButtonsUI();
    }
    window.syncVehicleWorkState = syncVehicleWorkState;

    function updateRoundDisplay() {
        const roundEl = document.getElementById('card-round-number');
        if (!roundEl) return;
        const activeCar = typeof currentCarCode !== 'undefined' && currentCarCode ? currentCarCode : 'EV-01';
        const todayKey = new Date().toISOString().slice(0, 10);
        const isWorkStarted = (typeof window.isDriverWorkActive === 'function') ? window.isDriverWorkActive() : (localStorage.getItem(`yru_work_started_${activeCar}_${todayKey}`) === 'true');

        let displayRound = currentRoundNumber;
        if (isWorkStarted) {
            if (!displayRound || displayRound <= 0) {
                displayRound = parseInt(localStorage.getItem(`yru_round_${activeCar}`) || localStorage.getItem(`yru_daily_rounds_${activeCar}_${todayKey}`) || '1', 10);
                if (displayRound <= 0) displayRound = 1;
                currentRoundNumber = displayRound;
            }
        }
        roundEl.innerText = Math.max(0, displayRound);
    }
    window.updateRoundDisplay = updateRoundDisplay;

    function renderRoundButtonState(state) {
        const container = document.getElementById('round-action-container');
        if (!container) return;

        container.innerHTML = `
            <button id="btn-end-work" onclick="showDailySummaryModal()" class="w-full h-12 bg-red-600 hover:bg-red-700 active:scale-95 text-white font-bold px-1.5 rounded-xl flex items-center justify-center gap-1.5 transition text-xs shadow-md shadow-red-600/20 font-kanit">
                เลิกงาน
            </button>
        `;
    }

    // 📊 สรุปผลการปฏิบัติงานประจำวัน (Daily Summary)
    window.showDailySummaryModal = function() {
        const todayKey = new Date().toISOString().slice(0, 10);
        const carIdStr = typeof currentCarCode !== 'undefined' ? currentCarCode : 'EV-01';
        const isWorkStarted = (typeof window.isDriverWorkActive === 'function') ? window.isDriverWorkActive() : (localStorage.getItem(`yru_work_started_${carIdStr}_${todayKey}`) === 'true');

        if (!isWorkStarted) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'ยังไม่ได้เริ่มงาน',
                    text: 'กรุณากด "เริ่มงาน" ก่อนลงเวลาการทำงานครับ',
                    icon: 'warning',
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#10B981',
                    customClass: { popup: 'font-kanit rounded-2xl' }
                });
            }
            return;
        }

        // 1. ดึงเวลาเริ่มงานของรอบงานใหม่นี้
        let dailyStartTime = '-';
        try {
            dailyStartTime = localStorage.getItem(`yru_shift_start_time_${carIdStr}`) || localStorage.getItem(`yru_daily_start_time_${carIdStr}_${todayKey}`) || '-';
        } catch(e) {}

        const thaiMonths = ['มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
        const now = new Date();
        const finishTimeStr = `${now.getDate()} ${thaiMonths[now.getMonth()]} ${now.getFullYear() + 543} | ${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')} น.`;
        const endTimeStr = `${String(now.getHours()).padStart(2, '0')}:${String(now.getMinutes()).padStart(2, '0')} น.`;

        let durationText = '-';
        if (dailyStartTime !== '-') {
            try {
                const [startH, startM] = dailyStartTime.split(' ')[0].split(':').map(Number);
                const [endH, endM] = endTimeStr.split(' ')[0].split(':').map(Number);
                let diffMin = (endH * 60 + endM) - (startH * 60 + startM);
                if (diffMin < 0) diffMin += 1440; // over midnight
                const h = Math.floor(diffMin / 60);
                const m = diffMin % 60;
                durationText = `${h} ชั่วโมง ${m} นาที`;
            } catch(e) {}
        }

        // 2. จำนวนรอบของรอบการทำงานใหม่นี้ (เริ่มต้นรอบที่ 1 เสมอ)
        let dailyRounds = currentRoundNumber;
        if (!dailyRounds || dailyRounds <= 0) {
            dailyRounds = parseInt(localStorage.getItem(`yru_shift_rounds_${carIdStr}`) || localStorage.getItem(`yru_round_${carIdStr}`) || '1', 10);
            if (dailyRounds <= 0) dailyRounds = 1;
        }

        // 3. จำนวนผู้โดยสารที่นับได้เฉพาะในรอบการทำงานใหม่นี้ (ความเป็นจริงตามคำขอรับ-ส่งจริง ไม่ดึงข้อมูลเก่า)
        let dailyPax = (typeof roundTotalPaxServed === 'number') ? roundTotalPaxServed : 0;
        if (dailyPax <= 0) {
            const shiftPax = parseInt(localStorage.getItem(`yru_shift_pax_${carIdStr}`), 10);
            if (!isNaN(shiftPax) && shiftPax > 0) {
                dailyPax = shiftPax;
            }
        }
        if (dailyPax < 0 || isNaN(dailyPax)) dailyPax = 0;

        Swal.fire({
            title: `สรุปผลการปฏิบัติงานประจำวัน<div class="text-xs font-normal text-slate-500 mt-1.5 flex items-center justify-center gap-1"><i class="fas fa-calendar-check text-emerald-600"></i> ${finishTimeStr}</div>`,
            html: `
                <div class="p-4 bg-gradient-to-b from-slate-50 to-emerald-50/50 rounded-2xl border border-emerald-200 text-left space-y-3 font-kanit shadow-sm">
                    <div class="flex items-center justify-between border-b border-emerald-200 pb-2.5">
                        <span class="text-sm font-bold text-slate-800 flex items-center gap-2">
                            <i class="fas fa-bus text-emerald-600"></i> รถไฟฟ้า: <span class="text-emerald-700 font-extrabold">${carIdStr}</span>
                        </span>
                        <span class="text-xs bg-emerald-600 text-white font-extrabold px-3 py-0.5 rounded-full shadow-xs">สิ้นสุดการปฏิบัติงาน</span>
                    </div>

                    <div class="bg-white p-3 rounded-xl border border-emerald-100 shadow-2xs mb-2.5">
                        <div class="flex items-center justify-between text-xs text-slate-600 mb-1.5">
                            <span class="font-bold flex items-center gap-1.5"><i class="fas fa-clock text-blue-500"></i> เวลาเริ่ม - สิ้นสุด:</span>
                            <span class="font-bold text-slate-800">${dailyStartTime} - ${endTimeStr}</span>
                        </div>
                        <div class="flex items-center justify-between text-xs text-slate-600 border-t border-slate-100 pt-1.5">
                            <span class="font-bold flex items-center gap-1.5"><i class="fas fa-hourglass-half text-amber-500"></i> ระยะเวลาทำงานรวม:</span>
                            <span class="font-bold text-emerald-600 text-sm">${durationText}</span>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-2.5 text-center pt-1">
                        <div class="bg-white p-3 rounded-xl border border-slate-200/80 shadow-2xs">
                            <div class="text-xs text-slate-500 font-semibold mb-1"><i class="fas fa-route text-blue-500 mr-1"></i>จำนวนรอบทั้งหมด</div>
                            <div class="text-2xl font-black text-blue-600">${dailyRounds} <span class="text-xs font-normal text-slate-500">รอบ</span></div>
                        </div>
                        <div class="bg-white p-3 rounded-xl border border-slate-200/80 shadow-2xs">
                            <div class="text-xs text-slate-500 font-semibold mb-1"><i class="fas fa-users text-emerald-500 mr-1"></i>ผู้โดยสารรวมวันนี้</div>
                            <div class="text-2xl font-black text-emerald-600">${dailyPax} <span class="text-xs font-normal text-slate-500">คน</span></div>
                        </div>
                    </div>

                    <div class="border-t border-emerald-200/80 pt-2.5 text-[11px] text-slate-600 text-center flex items-center justify-center gap-1.5 font-medium">
                        <i class="fas fa-heart text-pink-500"></i> ขอบคุณสำหรับการปฏิบัติหน้าที่ในวันนี้อย่างเต็มที่!
                    </div>
                </div>
            `,
            icon: 'success',
            confirmButtonText: 'สิ้นสุดวัน',
            confirmButtonColor: '#059669',
            customClass: { popup: 'font-kanit rounded-2xl' }
        }).then((result) => {
            if (result.isConfirmed) {
                const finalRounds = dailyRounds || 1;
                const finalPax = dailyPax || 0;
                currentRoundNumber = 0;
                roundTotalPaxServed = 0;
                currentOccupied = 0;
                const driverName = document.getElementById('card-driver-name')?.innerText || 'พนักงานขับรถ';
                const nowEnd = new Date();
                const nowEndTimeStr = `${String(nowEnd.getHours()).padStart(2, '0')}:${String(nowEnd.getMinutes()).padStart(2, '0')} น.`;

                try {
                    localStorage.setItem(`yru_work_started_${carIdStr}_${todayKey}`, 'false');
                    localStorage.removeItem(`yru_shift_start_time_${carIdStr}`);
                    localStorage.removeItem(`yru_shift_pax_${carIdStr}`);
                    localStorage.removeItem(`yru_shift_rounds_${carIdStr}`);
                    localStorage.removeItem(`yru_daily_start_time_${carIdStr}_${todayKey}`);
                    localStorage.removeItem(`yru_daily_pax_${carIdStr}_${todayKey}`);
                    localStorage.removeItem(`yru_daily_rounds_${carIdStr}_${todayKey}`);
                    localStorage.setItem('yru_round_' + carIdStr, '0');
                    localStorage.setItem(`yru_seats_${carIdStr}`, '0');
                    localStorage.setItem(`yru_car_status_${carIdStr}`, JSON.stringify({ 
                        status: 'พร้อมใช้งาน', 
                        driver_status: 'เลิกงาน', 
                        driver_name: driverName, 
                        end_time: nowEndTimeStr, 
                        total_rounds: finalRounds, 
                        total_pax: finalPax, 
                        occupied: 0, 
                        capacity: 10 
                    }));
                    if (typeof window.syncDriverStatus === 'function') {
                        window.syncDriverStatus('ended');
                    }
                } catch (e) {}
                window.isDriverWorking = false;
                window.isDriverOnBreak = false;
                window.disableDriverActionButtons();
                updateRoundDisplay();
                const seatsOccupiedEl = document.getElementById('seats-occupied-display');
                if (seatsOccupiedEl) seatsOccupiedEl.innerText = '0';
                const seatsProgressBar = document.getElementById('seats-progress-bar');
                if (seatsProgressBar) seatsProgressBar.style.width = '0%';
                const seatsPercentage = document.getElementById('seats-percentage');
                if (seatsPercentage) seatsPercentage.innerText = '0%';
                if (typeof updateWorkButtonsUI === 'function') updateWorkButtonsUI();
                
                Swal.fire({
                    title: 'สิ้นสุดการปฏิบัติงาน',
                    text: 'ระบบได้สรุปผลงานและตั้งค่าพร้อมปฏิบัติงานในรอบใหม่แล้ว',
                    icon: 'info',
                    timer: 2000,
                    showConfirmButton: false,
                    customClass: { popup: 'font-kanit rounded-2xl' }
                });
            }
        });
    };

    window.acceptPassengerPickup = function() {
        if (typeof handleCardActionDropoff === 'function') handleCardActionDropoff();
    };
    window.completePassengerTrip = function() {
        if (typeof handleCardActionDropoff === 'function') handleCardActionDropoff();
    };

    // 🏁 ฟังก์ชันปุ่ม "เริ่มรอบใหม่" สำหรับเตรียมพร้อมวิ่งรอบถัดไป
    window.startNewRound = function() {
        const startTimeStr = new Date().toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' }) + ' น.';
        
        // ส่ง API อัปเดตสถานะเป็นปกติ ฝั่ง Server
        const nowTimeNormal = new Date().toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' }) + ' น.';
        
        // รีเซ็ตดัชนีจุดจอดกลับไปจุดแรก (STOP 1/7)
        currentStopIndex = 0;
        updateNextStopUI();

        Swal.fire({
            icon: 'success',
            title: `เริ่มเดินรถรอบที่ ${currentRoundNumber}`,
            text: 'ระบบพร้อมรับผู้โดยสารสำหรับรอบใหม่เรียบร้อยแล้ว',
            confirmButtonColor: '#ec4899',
            customClass: { popup: 'font-kanit rounded-2xl' }
        });
    };

    // 📡 --- ส่วนงาน Logic จุดจอดและ Polling สายเรียกเข้าแบบ Real-time ---
    currentStopIndex = 0;
    window.lastNotifiedStopIndex = -1;
    window.latestPassengerCallData = null;

    window.completedCallIds = window.completedCallIds || new Set();
    try {
        const savedDone = JSON.parse(localStorage.getItem('yru_completed_calls') || '[]');
        savedDone.forEach(id => window.completedCallIds.add(String(id)));
    } catch(e) {}

    window.activePassengerCalls = [];
    window.currentCallQueueIndex = 0;
    window.knownCallIds = window.knownCallIds || new Set();

    function normalizeStationName(str) {
        if (!str) return '';
        return String(str)
            .toLowerCase()
            .replace(/\s+/g, '')
            .replace(/[()\-_\/.]/g, '')
            .replace(/จุดจอด(?:ที่)?/g, '')
            .replace(/stop/g, '')
            .trim();
    }

    // Helper ตรวจสอบความตรงกันของสถานี (ตรวจหมายเลขจุดจอดและชื่ออย่างแม่นยำ ป้องกันการเทียบผิดจุดจอด)
    function isStationMatch(targetStation, currentStation) {
        if (!targetStation || !currentStation) return false;
        const s1 = String(targetStation).trim().toLowerCase();
        const s2 = String(currentStation.name || '').trim().toLowerCase();
        
        if (s1 === s2) return true;

        // ดึงหมายเลขจุดจอด
        const m1 = s1.match(/จุดจอด(?:ที่)?\s*(\d+)/i) || s1.match(/stop\s*(\d+)/i) || s1.match(/^(\d+)$/);
        const m2 = s2.match(/จุดจอด(?:ที่)?\s*(\d+)/i) || s2.match(/stop\s*(\d+)/i) || s2.match(/^(\d+)$/);
        const curSeq = currentStation.id || currentStation.sequence;

        // ถ้าเป้าหมายระบุหมายเลขจุดจอด เช่น "จุดจอด 6" หรือ "6"
        if (m1) {
            const targetNum = parseInt(m1[1], 10);
            const curNum = (m2 ? parseInt(m2[1], 10) : null) || (curSeq ? parseInt(curSeq, 10) : null);
            if (curNum !== null && !isNaN(curNum)) {
                // หมายเลขจุดจอดต้องตรงกันเท่านั้น ห้ามเทียบจุดจอดอื่นเป็น true เด็ดขาด!
                return targetNum === curNum;
            }
        }

        // กรณีไม่ได้ระบุเลขจุดจอด ให้เทียบชื่อที่ตัดช่องว่าง
        const n1 = normalizeStationName(s1);
        const n2 = normalizeStationName(s2);
        if (n1 && n2 && (n1 === n2 || n1.includes(n2) || n2.includes(n1))) return true;

        const keywords = ['ที่พักบุคลากร', 'ศิลปะ', 'วิทยาศาสตร์', 'ศูนย์วิทย์', 'สังคมศาสตร์', 'อาคาร20', 'อาคาร 20', 'วิทยาการจัดการ', 'ประตูหลังมอ'];
        for (let i = 0; i < keywords.length; i++) {
            const kwNorm = keywords[i].replace(/\s+/g, '');
            if (n1.includes(kwNorm) && n2.includes(kwNorm)) return true;
        }
        
        return false;
    }

    // Helper ค้นหา Index ของจุดจอดใน stationData ที่ตรงกับคำขอเรียกรถ (จุดรับ หรือ จุดปลายทาง)
    window.findStationIndexByCallString = function(strOrId) {
        if (!strOrId || typeof stationData === 'undefined' || !Array.isArray(stationData) || stationData.length === 0) return -1;
        const str = String(strOrId).trim();

        // 1. ตรวจสอบกรณีเป็นตัวเลขจุดจอดโดยตรง เช่น 2, "2"
        const num = parseInt(str, 10);
        if (!isNaN(num) && String(num) === str) {
            for (let i = 0; i < stationData.length; i++) {
                const st = stationData[i];
                const sid = parseInt(st.id || st.sequence || (i + 1), 10);
                if (sid === num || (i + 1) === num) return i;
            }
        }

        // 2. ดึงหมายเลขจุดจอดจากข้อความ เช่น "จุดจอด 2 หน้าตึกศิลปะ" หรือ "จุดจอด 6 อาคารเรียน20"
        const m = str.match(/จุดจอด(?:ที่)?\s*(\d+)/i) || str.match(/stop\s*(\d+)/i) || str.match(/^(\d+)$/);
        if (m) {
            const stopNum = parseInt(m[1], 10);
            for (let i = 0; i < stationData.length; i++) {
                const st = stationData[i];
                const sid = parseInt(st.id || st.sequence || (i + 1), 10);
                if (sid === stopNum || (i + 1) === stopNum) return i;
            }
        }

        // 3. ตรวจสอบความตรงกันผ่าน isStationMatch หรือ keyword
        for (let i = 0; i < stationData.length; i++) {
            if (isStationMatch(str, stationData[i])) return i;
        }

        return -1;
    };

    // ซิงค์เป้าหมายจุดจอดให้ตรงกับจุดรับ (สเต็ป 1) หรือจุดส่ง (สเต็ป 2) ของผู้ใช้งาน โดยไม่ล็อคไว้ที่จุดจอด 1
    window.syncPassengerCallTargetStop = function(forceMapCenter = false) {
        if (typeof stationData === 'undefined' || !Array.isArray(stationData) || stationData.length === 0) return false;

        // กู้คืนคำขอจาก localStorage หากตัวแปรใน memory ยังไม่ได้โหลด
        if (!Array.isArray(window.activePassengerCalls) || window.activePassengerCalls.length === 0) {
            try {
                const activeCar = typeof currentCarCode !== 'undefined' ? currentCarCode : 'EV-01';
                const raw = localStorage.getItem('yru_latest_call_' + activeCar) || localStorage.getItem('yru_latest_call');
                if (raw) {
                    const parsed = JSON.parse(raw);
                    if (parsed && parsed.station && (typeof window.isCallForActiveCar !== 'function' || window.isCallForActiveCar(parsed)) && (Date.now() - (parsed.timestamp || 0) < 3600000)) {
                        window.activePassengerCalls = [parsed];
                        window.latestPassengerCallData = parsed;
                    }
                }
            } catch(e) {}
        }

        if (!Array.isArray(window.activePassengerCalls) || window.activePassengerCalls.length === 0) return false;

        const callIdx = (typeof window.currentCallQueueIndex === 'number' && window.currentCallQueueIndex < window.activePassengerCalls.length) 
            ? window.currentCallQueueIndex 
            : 0;
        const callData = window.activePassengerCalls[callIdx];
        if (!callData || !callData.station) return false;

        const statusStr = (callData.status || '').toLowerCase();
        const isStep2 = (statusStr === 'on_board' || statusStr === 'heading to destination' || statusStr === 'accepted' || statusStr === 'step2_dropoff');

        if (!isStep2) {
            // สเต็ปที่ 1: กำลังไปรับผู้โดยสาร -> เริ่มต้นจากจุดรับที่ผู้ใช้งานเรียก
            const pickupIdx = window.findStationIndexByCallString(callData.station);
            if (pickupIdx >= 0) {
                currentStopIndex = pickupIdx;
                const targetStop = stationData[pickupIdx];

                const nextStopNameEl = document.getElementById('next-stop-name');
                if (nextStopNameEl) {
                    nextStopNameEl.innerText = targetStop.name || callData.station;
                }

                const seqEl = document.getElementById('next-stop-sequence');
                if (seqEl) {
                    seqEl.innerText = 'STOP ' + (pickupIdx + 1) + '/' + stationData.length + ' (จุดรับ)';
                    seqEl.className = 'text-[10px] text-emerald-700 font-black bg-emerald-100 border border-emerald-300 px-2.5 py-0.5 rounded-full shadow-xs animate-pulse';
                }

                if (forceMapCenter && typeof map !== 'undefined' && map && targetStop.lat && targetStop.lng) {
                    map.setView([targetStop.lat, targetStop.lng], 17.5);
                }
                return true;
            }
        } else {
            // สเต็ปที่ 2: กำลังไปส่งผู้โดยสาร (ขึ้นรถแล้ว) -> ต้องผ่านจุดจอดก่อนหน้าตามลำดับเส้นทาง ไม่เด้งข้ามไปปลายทางทันที!
            const destIdx = window.findStationIndexByCallString(callData.destination);
            const curStop = stationData[currentStopIndex];
            if (curStop) {
                const nextStopNameEl = document.getElementById('next-stop-name');
                if (nextStopNameEl) {
                    nextStopNameEl.innerText = curStop.name;
                }

                const seqEl = document.getElementById('next-stop-sequence');
                if (seqEl) {
                    if (destIdx >= 0 && currentStopIndex === destIdx) {
                        // เมื่อเดินทางมาถึงจุดหมายปลายทางที่ผู้โดยสารจะลงแล้ว
                        seqEl.innerText = 'STOP ' + (currentStopIndex + 1) + '/' + stationData.length + ' (จุดส่ง)';
                        seqEl.className = 'text-[10px] text-blue-700 font-black bg-blue-100 border border-blue-300 px-2.5 py-0.5 rounded-full shadow-xs animate-pulse';
                    } else {
                        // จุดจอดระหว่างทางที่ต้องผ่านก่อนถึงปลายทาง
                        seqEl.innerText = 'STOP ' + (currentStopIndex + 1) + '/' + stationData.length;
                        seqEl.className = 'text-[10px] text-pink-600 font-bold bg-pink-50 border border-pink-100 px-2 py-0.5 rounded-full';
                    }
                }

                if (forceMapCenter && typeof map !== 'undefined' && map && curStop.lat && curStop.lng) {
                    map.setView([curStop.lat, curStop.lng], 17.5);
                }
                return true;
            }
        }
        return false;
    };

    window.updateNextStopUI = function() {
        if (typeof stationData === 'undefined' || stationData.length === 0) return;

        // ถ้ามีสายเรียกรถของผู้ใช้งาน ให้กำหนดจุดจอดเป้าหมายตามจุดรับ (สเต็ป 1) หรือจุดส่งปลายทาง (สเต็ป 2) ของผู้ใช้งานทันที
        const hasCallTarget = window.syncPassengerCallTargetStop(true);

        if (!hasCallTarget) {
            // โหมดเดินรถตามรอบปกติ (ไม่มีสายเรียกรถค้างอยู่)
            const nextStop = stationData[currentStopIndex];
            const nextStopNameEl = document.getElementById('next-stop-name');
            if (nextStopNameEl && nextStop) nextStopNameEl.innerText = nextStop.name;
            
            const seqEl = document.getElementById('next-stop-sequence');
            if (seqEl) {
                seqEl.innerText = 'STOP ' + (currentStopIndex + 1) + '/' + stationData.length;
                seqEl.className = 'text-[10px] text-pink-600 font-bold bg-pink-50 border border-pink-100 px-2 py-0.5 rounded-full';
            }
            
            // เลื่อนแผนที่ไปโฟกัสจุดจอดถัดไป
            if (typeof map !== 'undefined' && map && nextStop && nextStop.lat && nextStop.lng) {
                map.setView([nextStop.lat, nextStop.lng], 17.5);
            }
        }
        
        updateCallAlertUI();
    };

    // Helper หาระยะห่างจำนวนสถานีจากจุดปัจจุบันไปยังสถานีเป้าหมาย (ใน Loop เดินรถ)
    function getStationLoopDistance(targetStationStr, curStopIndex) {
        if (!targetStationStr || typeof stationData === 'undefined' || stationData.length === 0) return 999;
        const total = stationData.length;
        let targetIdx = -1;
        for (let i = 0; i < total; i++) {
            if (isStationMatch(targetStationStr, stationData[i])) {
                targetIdx = i;
                break;
            }
        }
        if (targetIdx === -1) return 999;
        return (targetIdx - curStopIndex + total) % total;
    }

    // ควบคุมการปลดล็อกปุ่ม "ถึงจุดจอดแล้ว" และ "ข้ามจุดจอด" (ต้องกดเริ่มงานก่อนเท่านั้น)
    window.enableDriverActionButtons = function() {
        if (!window.isDriverWorkActive() || window.isDriverOnBreak) {
            window.disableDriverActionButtons();
            return;
        }
        const btnArrived = document.getElementById('btn-arrived-stop');
        const btnSkip = document.getElementById('btn-skip-stop');
        if (btnArrived) {
            btnArrived.removeAttribute('disabled');
            btnArrived.classList.remove('opacity-60', 'cursor-not-allowed', 'pointer-events-none', 'bg-slate-200', 'text-slate-400', 'border-slate-300');
            btnArrived.className = 'bg-emerald-500 hover:bg-emerald-600 active:scale-95 text-white font-bold py-3.5 px-3 rounded-xl flex items-center justify-center gap-2 text-xs shadow-md shadow-emerald-500/20 transition cursor-pointer';
        }
        if (btnSkip) {
            btnSkip.removeAttribute('disabled');
            btnSkip.classList.remove('opacity-60', 'cursor-not-allowed', 'pointer-events-none', 'bg-slate-100', 'text-slate-400', 'border-slate-200');
            btnSkip.className = 'bg-pink-100 hover:bg-pink-200 text-pink-700 border border-pink-200 font-bold py-3.5 px-3 rounded-xl flex items-center justify-center gap-2 text-xs transition active:scale-95 cursor-pointer';
        }
    };

    // เรียงลำดับคำขอตามเส้นทางเดินรถ (Smart Route Queue)
    function sortCallsByRoute(calls, curStopIndex) {
        if (!Array.isArray(calls) || calls.length <= 1) return calls || [];
        return calls.slice().sort((a, b) => {
            const isStep2A = (a.status === 'STEP2_DROPOFF' || a.status === 'ON_BOARD' || a.status === 'Heading to Destination' || a.status === 'accepted');
            const isStep2B = (b.status === 'STEP2_DROPOFF' || b.status === 'ON_BOARD' || b.status === 'Heading to Destination' || b.status === 'accepted');
            
            const targetA = isStep2A ? a.destination : a.station;
            const targetB = isStep2B ? b.destination : b.station;
            
            const distA = getStationLoopDistance(targetA, curStopIndex);
            const distB = getStationLoopDistance(targetB, curStopIndex);
            
            return distA - distB;
        });
    }

    // ฟังก์ชันเลื่อนคิว Carousel
    window.prevCallQueue = function(e) {
        if (e) e.stopPropagation();
        if (window.activePassengerCalls.length <= 1) return;
        window.currentCallQueueIndex = (window.currentCallQueueIndex - 1 + window.activePassengerCalls.length) % window.activePassengerCalls.length;
        renderCurrentQueueItem();
        if (typeof window.syncPassengerCallTargetStop === 'function') {
            window.syncPassengerCallTargetStop(true);
        }
    };

    window.nextCallQueue = function(e) {
        if (e) e.stopPropagation();
        if (window.activePassengerCalls.length <= 1) return;
        window.currentCallQueueIndex = (window.currentCallQueueIndex + 1) % window.activePassengerCalls.length;
        renderCurrentQueueItem();
        if (typeof window.syncPassengerCallTargetStop === 'function') {
            window.syncPassengerCallTargetStop(true);
        }
    };

    function renderCurrentQueueItem() {
        const alertEl = document.getElementById('next-stop-alert');
        if (!alertEl) return;

        // ถ้า activePassengerCalls ว่าง แต่มี latestPassengerCallData ค้างอยู่ ให้กู้คืน
        if ((!window.activePassengerCalls || window.activePassengerCalls.length === 0) && window.latestPassengerCallData && window.latestPassengerCallData.station) {
            window.activePassengerCalls = [window.latestPassengerCallData];
        }

        if (typeof window.isCallForActiveCar === 'function' && Array.isArray(window.activePassengerCalls)) {
            window.activePassengerCalls = window.activePassengerCalls.filter(c => window.isCallForActiveCar(c));
        }

        const totalCalls = window.activePassengerCalls ? window.activePassengerCalls.length : 0;
        if (totalCalls === 0) {
            alertEl.classList.add('hidden');
            // รีเซ็ต sequence badge กลับเป็นสีชมพูปกติ
            const seqEl = document.getElementById('next-stop-sequence');
            if (seqEl && typeof stationData !== 'undefined' && stationData[currentStopIndex]) {
                seqEl.innerText = 'STOP ' + (currentStopIndex + 1) + '/' + stationData.length;
                seqEl.className = 'text-[10px] text-pink-600 font-bold bg-pink-50 border border-pink-100 px-2 py-0.5 rounded-full';
            }
            return;
        }

        if (window.currentCallQueueIndex >= totalCalls) {
            window.currentCallQueueIndex = 0;
        }
        if (window.currentCallQueueIndex < 0) {
            window.currentCallQueueIndex = 0;
        }

        const data = window.activePassengerCalls[window.currentCallQueueIndex];
        if (!data || !data.station) {
            alertEl.classList.add('hidden');
            return;
        }

        const statusStr = (data.status || '').toLowerCase();
        const isStep2Dropoff = (statusStr === 'on_board' || statusStr === 'heading to destination' || statusStr === 'accepted' || statusStr === 'step2_dropoff');

        if (isStep2Dropoff) {
            alertEl.className = "rounded-2xl p-4 flex flex-col gap-3 shadow-lg border border-blue-400 bg-gradient-to-r from-blue-600 via-indigo-600 to-blue-700 text-white transition-all duration-300";
        } else {
            alertEl.className = "rounded-2xl p-4 flex flex-col gap-3 shadow-lg border border-emerald-400 bg-gradient-to-r from-emerald-600 via-teal-600 to-emerald-700 text-white animate-pulse transition-all duration-300";
        }

        const titleBadgeEl = document.getElementById('call-alert-title-badge');
        if (titleBadgeEl) {
            titleBadgeEl.classList.add('hidden');
        }

        // ปุ่มเลื่อนคิว (แสดงเมื่อมีคำขอตั้งแต่ 2 กลุ่มขึ้นไป)
        const queueCtrl = document.getElementById('call-queue-controller');
        const queueIndicator = document.getElementById('call-queue-indicator');
        if (queueCtrl && queueIndicator) {
            if (totalCalls > 1) {
                queueCtrl.classList.remove('hidden');
                queueCtrl.classList.add('flex');
                queueIndicator.innerText = `${window.currentCallQueueIndex + 1}/${totalCalls}`;
            } else {
                queueCtrl.classList.add('hidden');
                queueCtrl.classList.remove('flex');
            }
        }

        // แถบสรุปยอดรวมด้านล่าง (ลบออกตามคำขอผู้ใช้)
        const summaryBar = document.getElementById('call-queue-summary-bar');
        if (summaryBar) {
            summaryBar.classList.add('hidden');
            summaryBar.classList.remove('flex');
        }

        const stationEl = document.getElementById('call-alert-station');
        if (stationEl) stationEl.innerText = data.station || '-';
        const destEl = document.getElementById('call-alert-destination');
        if (destEl) destEl.innerText = data.destination || 'ไม่ได้ระบุ';
        const paxEl = document.getElementById('call-alert-pax');
        if (paxEl) paxEl.innerText = data.pax || 1;
        const timeEl = document.getElementById('call-alert-time');
        if (timeEl) {
            let tStr = data.time || '';
            if (!tStr) tStr = 'เมื่อสักครู่';
            else if (!tStr.includes('น.')) tStr = tStr.slice(0, 5) + ' น.';
            timeEl.innerText = tStr;
        }

        alertEl.classList.remove('hidden');

        // ซิงค์เป้าหมายจุดจอดด้านบนและการเลื่อนแผนที่ให้ตรงกับจุดรับ/จุดส่งของผู้โดยสารกลุ่มนี้ทันที (ไม่ล็อคไว้ที่จุดจอด 1)
        if (typeof window.syncPassengerCallTargetStop === 'function') {
            window.syncPassengerCallTargetStop(false);
        }
    }

    window.updateCallAlertUI = function() {
        if (typeof stationData !== 'undefined' && stationData && stationData.length > 0 && typeof currentStopIndex !== 'undefined' && Array.isArray(window.activePassengerCalls) && window.activePassengerCalls.length > 1) {
            window.activePassengerCalls = sortCallsByRoute(window.activePassengerCalls, currentStopIndex);
        }
        renderCurrentQueueItem();
        if (Array.isArray(window.activePassengerCalls) && window.activePassengerCalls.length > 0) {
            if (typeof window.enableDriverActionButtons === 'function') {
                window.enableDriverActionButtons();
            }
        }
    };

    // ปุ่มกดทำงานโดยตรงบนการ์ด (รับขึ้นรถ หรือ ส่งถึงที่หมาย)
    window.handleCardActionDropoff = function(e) {
        if (e) e.stopPropagation();
        if (!Array.isArray(window.activePassengerCalls) || window.activePassengerCalls.length === 0) return;
        const callData = window.activePassengerCalls[window.currentCallQueueIndex] || window.activePassengerCalls[0];
        if (!callData) return;

        const statusStr = (callData.status || '').toLowerCase();
        const isStep2 = (statusStr === 'on_board' || statusStr === 'heading to destination' || statusStr === 'accepted' || statusStr === 'step2_dropoff');
        const paxCount = parseInt(callData.pax) || 1;
        const callId = String(callData.call_id || callData.id || callData.timestamp || (callData.station + '_' + (callData.destination || '')));
        const activeCar = typeof currentCarCode !== 'undefined' ? currentCarCode : 'EV-01';

        if (!isStep2) {
            // สเต็ป 1 -> รับขึ้นรถ
            callData.status = 'STEP2_DROPOFF';
            if (typeof changeSeatsOccupied === 'function') {
                changeSeatsOccupied(paxCount);
            }
            try {
                roundTotalPaxServed = (roundTotalPaxServed || 0) + paxCount;
                localStorage.setItem(`yru_shift_pax_${activeCar}`, roundTotalPaxServed.toString());
                const todayKey = new Date().toISOString().slice(0, 10);
                localStorage.setItem(`yru_daily_pax_${activeCar}_${todayKey}`, roundTotalPaxServed.toString());
            } catch(e) {}
            fetch('/api/accept-ev-request', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ status: 'STEP2_DROPOFF', car_id: activeCar, call_id: callData.call_id || callData.id })
            }).catch(e => {});

            // ผู้โดยสารขึ้นรถแล้ว เคลื่อนตัวไปยังจุดจอดถัดไปตามเส้นทางเดินรถปกติ (ไม่เด้งข้ามไปปลายทางทันที)
            if (typeof stationData !== 'undefined' && stationData.length > 0) {
                currentStopIndex = (currentStopIndex + 1) % stationData.length;
            }

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: '🟢 รับผู้โดยสารขึ้นรถแล้ว',
                    text: `รับผู้โดยสาร ${paxCount} คน กำลังเดินทางไปยัง ${callData.destination || 'จุดหมายปลายทาง'}`,
                    timer: 1800,
                    showConfirmButton: false,
                    customClass: { popup: 'font-kanit rounded-2xl' }
                });
            }
            
            if (typeof updateNextStopUI === 'function') {
                updateNextStopUI();
            } else {
                updateCallAlertUI();
            }
        } else {
            // สเต็ป 2 -> ส่งลงปลายทางเสร็จสิ้น
            if (typeof changeSeatsOccupied === 'function') {
                changeSeatsOccupied(-paxCount);
            }

            if (!window.completedCallIds) window.completedCallIds = new Set();
            window.completedCallIds.add(callId);
            if (callData.call_id) window.completedCallIds.add(String(callData.call_id));
            if (callData.id) window.completedCallIds.add(String(callData.id));

            try {
                let savedDone = JSON.parse(localStorage.getItem('yru_completed_calls') || '[]');
                savedDone.push(callId);
                localStorage.setItem('yru_completed_calls', JSON.stringify(savedDone.slice(-100)));

                const todayKey = new Date().toISOString().slice(0, 10);
                let savedRounds = parseInt(localStorage.getItem(`yru_daily_rounds_${activeCar}_${todayKey}`) || localStorage.getItem(`yru_round_${activeCar}`) || '0', 10);
                if (savedRounds < 1) savedRounds = 1;
                localStorage.setItem(`yru_daily_rounds_${activeCar}_${todayKey}`, savedRounds.toString());
                if (currentRoundNumber < 1) currentRoundNumber = savedRounds;
                updateRoundDisplay();
            } catch(e) {}

            fetch('/api/clear-ev-request', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                body: JSON.stringify({ car_id: activeCar, call_id: callData.call_id || callData.id })
            }).catch(e => {});

            window.activePassengerCalls = window.activePassengerCalls.filter(c => {
                const cid = String(c.call_id || c.id || c.timestamp || (c.station + '_' + (c.destination || '')));
                return cid !== callId && (!c.call_id || c.call_id !== callData.call_id);
            });

            window.latestPassengerCallData = window.activePassengerCalls.length > 0 ? window.activePassengerCalls[0] : null;
            try {
                if (window.latestPassengerCallData) {
                    localStorage.setItem('yru_latest_call', JSON.stringify(window.latestPassengerCallData));
                } else {
                    localStorage.removeItem('yru_latest_call');
                    localStorage.removeItem('yru_latest_call_' + activeCar);
                }
            } catch(e) {}

            if (window.activePassengerCalls.length === 0) {
                const alertEl = document.getElementById('next-stop-alert');
                if (alertEl) alertEl.classList.add('hidden');
                if (typeof stationData !== 'undefined' && stationData.length > 0) {
                    currentStopIndex = (currentStopIndex + 1) % stationData.length;
                }
            }

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'success',
                    title: '🔵 ส่งผู้โดยสารถึงที่หมายเรียบร้อย',
                    text: `ส่งผู้โดยสาร ${paxCount} คน ลงจากรถเรียบร้อยแล้ว`,
                    timer: 1800,
                    showConfirmButton: false,
                    customClass: { popup: 'font-kanit rounded-2xl' }
                });
            }

            if (typeof updateNextStopUI === 'function') {
                updateNextStopUI();
            } else {
                updateCallAlertUI();
            }
        }
    };

    // ปุ่มยกเลิกเคส (ยกเลิกเฉพาะกลุ่มที่กำลังเปิดดูอยู่)
    window.cancelPassengerCall = function() {
        if (!Array.isArray(window.activePassengerCalls) || window.activePassengerCalls.length === 0) return;
        const data = window.activePassengerCalls[window.currentCallQueueIndex] || window.activePassengerCalls[0];
        if (!data) return;

        const statusStr = (data.status || '').toLowerCase();
        const isBoarded = (statusStr === 'on_board' || statusStr === 'heading to destination' || statusStr === 'accepted' || statusStr === 'step2_dropoff');
        const paxCount = parseInt(data.pax) || 1;
        const callId = String(data.call_id || data.id || data.timestamp || (data.station + '_' + (data.destination || '')));

        if (typeof Swal !== 'undefined') {
            Swal.fire({
                title: 'ยืนยันยกเลิกเคสนี้?',
                text: isBoarded ? `ผู้โดยสารกลุ่มนี้ขึ้นรถมาแล้ว ${paxCount} คน การยกเลิกจะทำการหักลบจำนวนคนออกจากรถ` : 'คุณต้องการยกเลิกคำขอเรียกรถของกลุ่มนี้ใช่หรือไม่?',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonText: 'ใช่, ยกเลิกเคส',
                cancelButtonText: 'ถอยกลับ',
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#6B7280',
                customClass: { popup: 'font-kanit rounded-2xl' }
            }).then((result) => {
                if (result.isConfirmed) {
                    if (isBoarded && typeof changeSeatsOccupied === 'function') {
                        changeSeatsOccupied(-paxCount);
                    }

                    if (!window.completedCallIds) window.completedCallIds = new Set();
                    window.completedCallIds.add(callId);
                    if (data.call_id) window.completedCallIds.add(String(data.call_id));
                    if (data.id) window.completedCallIds.add(String(data.id));

                    try {
                        let savedDone = JSON.parse(localStorage.getItem('yru_completed_calls') || '[]');
                        savedDone.push(callId);
                        localStorage.setItem('yru_completed_calls', JSON.stringify(savedDone.slice(-100)));
                    } catch(e) {}

                    const activeCar = typeof currentCarCode !== 'undefined' ? currentCarCode : 'EV-01';
                    fetch('/api/clear-ev-request', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ car_id: activeCar, call_id: data.call_id || data.id })
                    }).catch(e => {});

                    // นำออกจากคิว
                    window.activePassengerCalls = window.activePassengerCalls.filter(c => {
                        const cid = String(c.call_id || c.id || c.timestamp || (c.station + '_' + (c.destination || '')));
                        return cid !== callId && (!c.call_id || c.call_id !== data.call_id);
                    });

                    window.latestPassengerCallData = window.activePassengerCalls.length > 0 ? window.activePassengerCalls[0] : null;
                    try {
                        if (window.latestPassengerCallData) {
                            localStorage.setItem('yru_latest_call', JSON.stringify(window.latestPassengerCallData));
                        } else {
                            localStorage.removeItem('yru_latest_call');
                            localStorage.removeItem('yru_latest_call_' + activeCar);
                        }
                    } catch(e) {}

                    if (window.activePassengerCalls.length === 0) {
                        const alertEl = document.getElementById('next-stop-alert');
                        if (alertEl) alertEl.classList.add('hidden');
                    }

                    Swal.fire({
                        title: 'ยกเลิกเคสเรียบร้อย',
                        text: 'เคลียร์รายการเรียกรถของกลุ่มนี้ออกจากระบบเรียบร้อยแล้ว',
                        icon: 'info',
                        timer: 1800,
                        showConfirmButton: false,
                        customClass: { popup: 'font-kanit rounded-2xl' }
                    });

                    updateCallAlertUI();
                }
            });
        }
    };

    window.handleArrivedAtStop = function() {
        if (!window.isDriverWorkActive()) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'ยังไม่ได้เริ่มงาน',
                    text: 'กรุณากดปุ่ม "เริ่มงาน" ก่อนทำการบันทึกจุดจอดครับ',
                    icon: 'warning',
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#10B981',
                    customClass: { popup: 'font-kanit rounded-2xl' }
                });
            }
            return;
        }
        if (window.isDriverOnBreak) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'อยู่ในช่วงพักงาน',
                    text: 'กรุณากดกลับมาทำงานก่อนทำการบันทึกจุดจอดครับ',
                    icon: 'info',
                    confirmButtonText: 'ตกลง',
                    customClass: { popup: 'font-kanit rounded-2xl' }
                });
            }
            return;
        }
        if (typeof stationData === 'undefined' || stationData.length === 0) return;
        const nextStop = stationData[currentStopIndex];
        let totalBoarded = 0;
        let totalAlighted = 0;
        const activeCar = typeof currentCarCode !== 'undefined' ? currentCarCode : 'EV-01';
        const isLastStop = (currentStopIndex === stationData.length - 1);
        let nextDestinationIndex = -1;

        if (Array.isArray(window.activePassengerCalls) && window.activePassengerCalls.length > 0) {
            let remainingCalls = [];

            window.activePassengerCalls.forEach(callData => {
                const paxCount = parseInt(callData.pax) || 1;
                const callId = String(callData.call_id || callData.id || callData.timestamp || (callData.station + '_' + (callData.destination || '')));
                const statusStr = (callData.status || '').toLowerCase();
                const isStep2 = (statusStr === 'on_board' || statusStr === 'heading to destination' || statusStr === 'accepted' || statusStr === 'step2_dropoff');

                const pickupMatch = !isStep2 && (isStationMatch(callData.station, nextStop) || (typeof window.findStationIndexByCallString === 'function' && window.findStationIndexByCallString(callData.station) === currentStopIndex));
                const dropoffMatch = isStep2 && (isStationMatch(callData.destination, nextStop) || (typeof window.findStationIndexByCallString === 'function' && window.findStationIndexByCallString(callData.destination) === currentStopIndex) || (isLastStop && (!callData.destination || callData.destination === 'ไม่ได้ระบุ')));

                if (pickupMatch) {
                    // ผู้โดยสารขึ้นรถที่จุดรับนี้ (+)
                    totalBoarded += paxCount;
                    callData.status = 'STEP2_DROPOFF';
                    try {
                        roundTotalPaxServed = (roundTotalPaxServed || 0) + paxCount;
                        localStorage.setItem(`yru_shift_pax_${activeCar}`, roundTotalPaxServed.toString());
                        const todayKey = new Date().toISOString().slice(0, 10);
                        localStorage.setItem(`yru_daily_pax_${activeCar}_${todayKey}`, roundTotalPaxServed.toString());
                    } catch(e) {}
                    fetch('/api/accept-ev-request', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ status: 'STEP2_DROPOFF', car_id: activeCar, call_id: callData.call_id || callData.id })
                    }).catch(e => {});

                    remainingCalls.push(callData);

                    // ผู้โดยสารขึ้นรถแล้ว ระบบจะขับผ่านจุดจอดตามลำดับเส้นทางปกติ ไม่เด้งข้ามไปปลายทางทันที

                } else if (dropoffMatch) {
                    // ผู้โดยสารลงรถที่จุดหมายปลายทางนี้ (-) ลดจำนวนคนบนรถทันที
                    totalAlighted += paxCount;
                    if (!window.completedCallIds) window.completedCallIds = new Set();
                    window.completedCallIds.add(callId);
                    if (callData.call_id) window.completedCallIds.add(String(callData.call_id));
                    if (callData.id) window.completedCallIds.add(String(callData.id));

                    try {
                        let savedDone = JSON.parse(localStorage.getItem('yru_completed_calls') || '[]');
                        savedDone.push(callId);
                        localStorage.setItem('yru_completed_calls', JSON.stringify(savedDone.slice(-100)));

                        const todayKey = new Date().toISOString().slice(0, 10);
                        let savedRounds = parseInt(localStorage.getItem(`yru_daily_rounds_${activeCar}_${todayKey}`) || localStorage.getItem(`yru_round_${activeCar}`) || '0', 10);
                        if (savedRounds < 1) savedRounds = 1;
                        localStorage.setItem(`yru_daily_rounds_${activeCar}_${todayKey}`, savedRounds.toString());
                        if (currentRoundNumber < 1) currentRoundNumber = savedRounds;
                        updateRoundDisplay();
                    } catch(e) {}

                    fetch('/api/clear-ev-request', {
                        method: 'POST',
                        headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                        body: JSON.stringify({ car_id: activeCar, call_id: callData.call_id || callData.id })
                    }).catch(e => {});
                } else {
                    remainingCalls.push(callData);
                }
            });

            // ปรับเปลี่ยนจำนวนผู้โดยสารบนรถสุทธิ (+คนขึ้น / -คนลง)
            const netChange = totalBoarded - totalAlighted;
            if (netChange !== 0 && typeof changeSeatsOccupied === 'function') {
                changeSeatsOccupied(netChange);
            }

            window.activePassengerCalls = remainingCalls;
            window.latestPassengerCallData = remainingCalls.length > 0 ? remainingCalls[0] : null;
            try {
                if (window.latestPassengerCallData) {
                    localStorage.setItem('yru_latest_call', JSON.stringify(window.latestPassengerCallData));
                } else {
                    localStorage.removeItem('yru_latest_call');
                    localStorage.removeItem('yru_latest_call_' + activeCar);
                }
            } catch(e) {}

            if (window.activePassengerCalls.length === 0) {
                const alertEl = document.getElementById('next-stop-alert');
                if (alertEl) alertEl.classList.add('hidden');
            }

            updateCallAlertUI();
        }

        if (totalBoarded === 0 && totalAlighted === 0) {
            // ไม่สุ่มผู้โดยสารปลอม: นับเฉพาะผู้โดยสารที่มีการเรียกใช้งานจริงในระบบ
            if (isLastStop && currentOccupied > 0) {
                // ถึงจุดจอดสุดท้าย ให้ผู้โดยสารที่ยังค้างบนรถลงทั้งหมด
                if (typeof changeSeatsOccupied === 'function') {
                    changeSeatsOccupied(-currentOccupied);
                }
            }
        }

        // แสดงการแจ้งเตือนเฉพาะสถานีที่มีผู้โดยสารขึ้นหรือลงเท่านั้น (จุดที่ไม่มีคนขึ้น-ลง ไม่ต้องขึ้นแจ้งเตือน)
        const hasPassengerEvent = (totalBoarded > 0 || totalAlighted > 0);
        if (hasPassengerEvent && typeof Swal !== 'undefined') {
            const msgs = [];
            if (totalBoarded > 0) {
                msgs.push(`<span class="text-sm font-semibold text-emerald-600">🟢 รับผู้โดยสารขึ้น <b class="font-black text-emerald-700">${totalBoarded}</b> คน</span>`);
            }
            if (totalAlighted > 0) {
                msgs.push(`<span class="text-sm font-semibold text-blue-600">🔵 ส่งผู้โดยสารลง <b class="font-black text-blue-700">${totalAlighted}</b> คน</span>`);
            }

            Swal.fire({
                title: `📍 ถึงจุดจอด: ${nextStop.name}`,
                html: msgs.length > 0 ? `<div class="mt-2 space-y-1">${msgs.join('<br>')}</div>` : undefined,
                icon: 'success',
                timer: 2200,
                showConfirmButton: false,
                customClass: { popup: 'font-kanit rounded-2xl' }
            });
        }
        
        // กำหนดจุดจอดถัดไป: วิ่งผ่านจุดจอดตามลำดับเส้นทางเดินรถเสมอ (ต้องผ่านจุดจอดก่อนหน้าตามจริง ไม่เด้งข้ามไปปลายทางทันที)
        currentStopIndex = (currentStopIndex + 1) % stationData.length;
        
        if (isLastStop) {
            onCompleteRoundLoop();
        }
        
        updateNextStopUI();
    };

    window.handleSkipStop = function() {
        if (!window.isDriverWorkActive()) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'ยังไม่ได้เริ่มงาน',
                    text: 'กรุณากดปุ่ม "เริ่มงาน" ก่อนทำการข้ามจุดจอดครับ',
                    icon: 'warning',
                    confirmButtonText: 'ตกลง',
                    confirmButtonColor: '#10B981',
                    customClass: { popup: 'font-kanit rounded-2xl' }
                });
            }
            return;
        }
        if (window.isDriverOnBreak) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: 'อยู่ในช่วงพักงาน',
                    text: 'กรุณากดกลับมาทำงานก่อนดำเนินการครับ',
                    icon: 'info',
                    confirmButtonText: 'ตกลง',
                    customClass: { popup: 'font-kanit rounded-2xl' }
                });
            }
            return;
        }
        if (typeof stationData === 'undefined' || stationData.length === 0) return;

        // ถ้ามีสายเรียกรถของผู้ใช้งานค้างอยู่ ให้ข้ามไปยังขั้นตอนถัดไป (สเต็ป 1 ไปสเต็ป 2 หรือจบเคสส่งผู้โดยสาร)
        if (Array.isArray(window.activePassengerCalls) && window.activePassengerCalls.length > 0) {
            const currentCall = window.activePassengerCalls[window.currentCallQueueIndex || 0];
            if (currentCall) {
                const statusStr = (currentCall.status || '').toLowerCase();
                const isStep2 = (statusStr === 'on_board' || statusStr === 'heading to destination' || statusStr === 'accepted' || statusStr === 'step2_dropoff');
                if (!isStep2 && currentCall.destination) {
                    currentCall.status = 'STEP2_DROPOFF';
                    currentStopIndex = (currentStopIndex + 1) % stationData.length;
                    updateNextStopUI();
                    return;
                } else {
                    if (typeof handleCardActionDropoff === 'function') {
                        handleCardActionDropoff();
                        return;
                    }
                }
            }
        }

        const isLastStop = (currentStopIndex === stationData.length - 1);
        currentStopIndex = (currentStopIndex + 1) % stationData.length;
        
        if (isLastStop) {
            onCompleteRoundLoop();
        }
        
        updateNextStopUI();
    };

    function onCompleteRoundLoop() {
        const carIdStr = typeof currentCarCode !== 'undefined' && currentCarCode ? currentCarCode : 'EV-01';
        const todayKey = new Date().toISOString().slice(0, 10);
        const driverName = document.getElementById('card-driver-name')?.innerText || 'พนักงานขับรถ';

        currentRoundNumber = (parseInt(currentRoundNumber) || 0) + 1;
        updateRoundDisplay();

        try {
            localStorage.setItem(`yru_round_${carIdStr}`, currentRoundNumber.toString());
            localStorage.setItem(`yru_daily_rounds_${carIdStr}_${todayKey}`, currentRoundNumber.toString());

            let shifts = JSON.parse(localStorage.getItem('yru_driver_shifts') || '{}');
            const currentPaxCount = (typeof roundTotalPaxServed === 'number') ? roundTotalPaxServed : parseInt(localStorage.getItem(`yru_shift_pax_${carIdStr}`) || '0', 10);
            if (!shifts[carIdStr]) {
                shifts[carIdStr] = {
                    car_id: carIdStr,
                    driver_name: driverName,
                    date: todayKey,
                    start_time: localStorage.getItem(`yru_shift_start_time_${carIdStr}`) || localStorage.getItem(`yru_daily_start_time_${carIdStr}_${todayKey}`) || '08:00 น.',
                    end_time: '-',
                    rounds: currentRoundNumber,
                    pax: currentPaxCount,
                    status: 'กำลังปฏิบัติงาน',
                    updated_at: Date.now()
                };
            } else {
                shifts[carIdStr].rounds = currentRoundNumber;
                shifts[carIdStr].pax = currentPaxCount;
                shifts[carIdStr].updated_at = Date.now();
            }
            localStorage.setItem('yru_driver_shifts', JSON.stringify(shifts));

            try {
                const tramSyncChannel = new BroadcastChannel('yru_trams_realtime_sync');
                tramSyncChannel.postMessage({ 
                    type: 'DRIVER_SHIFT_UPDATED', 
                    car_id: carIdStr, 
                    rounds: currentRoundNumber, 
                    timestamp: Date.now() 
                });
            } catch(e) {}
            window.dispatchEvent(new Event('storage'));
        } catch(e) {
            console.error('Error saving completed round:', e);
        }
    }

    let lastRequestTime = null; 

    // ฟังก์ชันตรวจสอบอย่างเข้มงวดว่าคำขอเรียกรถนี้จัดสรรให้รถคันนี้จริงหรือไม่ (ห้ามเด้งขึ้นรถคันอื่นเด็ดขาด)
    window.isCallForActiveCar = function(callObj) {
        if (!callObj) return false;
        const targetCar = String(callObj.car_id || '').trim().toUpperCase();
        if (!targetCar) return false; // ต้องระบุรถคันเป้าหมาย ห้ามเด้งขึ้นทุกคัน

        const activeCar = String(typeof currentCarCode !== 'undefined' && currentCarCode ? currentCarCode : 'EV-01').trim().toUpperCase();
        const curNum = (typeof currentCarId !== 'undefined' ? currentCarId : parseInt(activeCar.replace(/\D/g, ''), 10)) || 1;

        if (targetCar === activeCar) return true;
        if (targetCar === ('EV-' + curNum)) return true;
        if (targetCar === ('EV-0' + curNum)) return true;
        if (targetCar === String(curNum)) return true;

        const mTarget = targetCar.match(/(\d+)/);
        const mActive = activeCar.match(/(\d+)/);
        if (mTarget && mActive && parseInt(mTarget[1], 10) === parseInt(mActive[1], 10)) {
            return true;
        }
        return false;
    };

    // ฟังก์ชันเพิ่มหรืออัปเดตคำขอเรียกรถเข้าสู่คิวแสดงผลทันที
    window.addOrUpdatePassengerCall = function(callObj) {
        if (!callObj || !callObj.station) return;
        
        // ตรวจสอบความถูกต้องของรถเป้าหมาย: ถ้าไม่ใช่รถคันนี้ ไม่ต้องประมวลผลหรือเด้งเตือน
        if (!window.isCallForActiveCar(callObj)) {
            return;
        }

        const callId = String(callObj.call_id || callObj.id || callObj.timestamp || (callObj.station + '_' + (callObj.destination || '')));
        
        // ตรวจสอบว่าเคยเคลียร์หรือเสร็จสิ้นไปแล้วหรือไม่
        if (window.completedCallIds) {
            if (window.completedCallIds.has(callId)) return;
            if (callObj.call_id && window.completedCallIds.has(String(callObj.call_id))) return;
            if (callObj.id && window.completedCallIds.has(String(callObj.id))) return;
        }

        const statusStr = (callObj.status || '').toLowerCase();
        if (statusStr === 'completed' || statusStr === 'cleared' || statusStr === 'cancelled' || statusStr === 'dropped_off') {
            return;
        }

        if (!Array.isArray(window.activePassengerCalls)) {
            window.activePassengerCalls = [];
        }

        // กรองเอาเฉพาะรายการที่ตรงกับรถคันนี้เสมอ
        window.activePassengerCalls = window.activePassengerCalls.filter(c => window.isCallForActiveCar(c));

        const existsIdx = window.activePassengerCalls.findIndex(c => {
            const cid = String(c.call_id || c.id || c.timestamp || (c.station + '_' + (c.destination || '')));
            return cid === callId || (c.call_id && callObj.call_id && c.call_id === callObj.call_id) || (c.id && callObj.id && c.id === callObj.id);
        });

        if (existsIdx >= 0) {
            window.activePassengerCalls[existsIdx] = callObj;
        } else {
            window.activePassengerCalls.unshift(callObj);
            playAlertSound();
            showPassengerCallAlertModal(callObj);
        }

        window.latestPassengerCallData = window.activePassengerCalls.length > 0 ? window.activePassengerCalls[0] : null;

        // กำหนดจุดจอดเป้าหมายทันที: จุดรับที่ไหนก็เริ่มจากจุดจอดนั้น! หรือหากขึ้นรถแล้วให้ไปส่งปลายทาง
        if (typeof window.syncPassengerCallTargetStop === 'function') {
            window.syncPassengerCallTargetStop(true);
        }
        if (typeof window.enableDriverActionButtons === 'function') {
            window.enableDriverActionButtons();
        }

        updateCallAlertUI();
    };

    // ฟังก์ชันแสดงป๊อปอัปแจ้งเตือนคนขับตรงกลางหน้าจออย่างชัดเจนและสวยงาม
    function showPassengerCallAlertModal(callData) {
        if (!callData || typeof Swal === 'undefined') return;
        const station = callData.station || '-';
        const destination = callData.destination || 'ไม่ได้ระบุ';
        const pax = callData.pax || 1;
        const callerName = callData.user_name || callData.passenger_name || 'ผู้โดยสาร';

        Swal.fire({
            title: '🔔 มีผู้โดยสารเรียกรถ!',
            html: `
                <div class="text-left text-sm font-kanit space-y-3 my-2">
                    <div class="flex items-center gap-3 bg-emerald-50 border border-emerald-200/90 rounded-2xl p-3.5 shadow-2xs">
                        <div class="w-10 h-10 rounded-2xl bg-emerald-500 text-white flex items-center justify-center text-lg shrink-0 shadow-sm">
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="text-slate-500 text-[11px] font-bold block">จุดรับผู้โดยสาร:</span>
                            <b class="text-slate-800 text-base font-black truncate block">${station}</b>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 bg-blue-50 border border-blue-200/90 rounded-2xl p-3.5 shadow-2xs">
                        <div class="w-10 h-10 rounded-2xl bg-blue-500 text-white flex items-center justify-center text-lg shrink-0 shadow-sm">
                            <i class="fas fa-flag-checkered"></i>
                        </div>
                        <div class="min-w-0 flex-1">
                            <span class="text-slate-500 text-[11px] font-bold block">จุดหมายปลายทาง:</span>
                            <b class="text-slate-800 text-base font-black truncate block">${destination}</b>
                        </div>
                    </div>
                    <div class="flex items-center justify-between bg-amber-50 border border-amber-200/90 rounded-2xl p-3.5 shadow-2xs">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-2xl bg-amber-500 text-white flex items-center justify-center text-lg shrink-0 shadow-sm">
                                <i class="fas fa-users"></i>
                            </div>
                            <div>
                                <span class="text-slate-500 text-[11px] font-bold block">จำนวนผู้โดยสาร:</span>
                                <b class="text-slate-800 text-xs font-bold">รอขึ้นรถ</b>
                            </div>
                        </div>
                        <span class="text-amber-700 text-2xl font-black">${pax} <span class="text-xs font-bold text-slate-600">คน</span></span>
                    </div>
                </div>
            `,
            icon: 'info',
            position: 'center',
            confirmButtonText: '📍 รับทราบ',
            confirmButtonColor: '#10b981',
            showConfirmButton: true,
            timer: 8000,
            timerProgressBar: true,
            allowOutsideClick: true,
            customClass: {
                popup: 'font-kanit rounded-[2rem] p-6 shadow-2xl border border-pink-100',
                confirmButton: 'px-8 py-3 rounded-2xl font-bold font-kanit shadow-lg shadow-emerald-500/25 active:scale-95 transition cursor-pointer text-sm'
            }
        });
    }

    window.checkPassengerCalls = function() {
        const carIdParam = typeof currentCarCode !== 'undefined' ? currentCarCode : 'EV-01';
        fetch('/api/check-ev-request?car_id=' + encodeURIComponent(carIdParam) + '&t=' + new Date().getTime())
        .then(response => {
            if (!response.ok) throw new Error('API server unavailable');
            return response.json();
        })
        .then(data => {
            if (!data) {
                // ถ้าไม่มีข้อมูลจาก server ให้ล้างรายการที่อาจตกค้างออก
                if (Array.isArray(window.activePassengerCalls)) {
                    window.activePassengerCalls = window.activePassengerCalls.filter(c => window.isCallForActiveCar(c));
                }
                if (!window.activePassengerCalls || window.activePassengerCalls.length === 0) {
                    window.latestPassengerCallData = null;
                    updateCallAlertUI();
                }
                return;
            }

            const rawList = Array.isArray(data.requests) ? data.requests : (data.station ? [data] : []);

            // โหลดรายการ completed calls จาก localStorage
            try {
                const savedDone = JSON.parse(localStorage.getItem('yru_completed_calls') || '[]');
                if (!window.completedCallIds) window.completedCallIds = new Set();
                savedDone.forEach(id => window.completedCallIds.add(String(id)));
            } catch(e) {}

            // กรองคำขอที่เสร็จสิ้นหรือยกเลิกแล้วออก และต้องเป็นคำขอของรถคันนี้เท่านั้น!
            let filtered = rawList.filter(item => {
                if (!item || !item.station) return false;
                if (!window.isCallForActiveCar(item)) return false;

                const callId = String(item.call_id || item.id || item.timestamp || (item.station + '_' + (item.destination || '')));
                const statusStr = (item.status || '').toLowerCase();
                if (window.completedCallIds) {
                    if (window.completedCallIds.has(callId)) return false;
                    if (item.call_id && window.completedCallIds.has(String(item.call_id))) return false;
                    if (item.id && window.completedCallIds.has(String(item.id))) return false;
                }
                if (statusStr === 'completed' || statusStr === 'cleared' || statusStr === 'cancelled' || statusStr === 'dropped_off') return false;

                return true;
            });

            // ตรวจจับคำขอใหม่เพื่อแจ้งเตือนคนขับ
            window.knownCallIds = window.knownCallIds || new Set();
            filtered.forEach(item => {
                const callId = String(item.call_id || item.id || item.timestamp || (item.station + '_' + (item.destination || '')));
                if (!window.knownCallIds.has(callId)) {
                    window.knownCallIds.add(callId);
                    playAlertSound();
                    showPassengerCallAlertModal(item);
                }
            });

            if (filtered.length > 0) {
                // เก็บรักษา local status เช่น STEP2_DROPOFF หากคนขับกดรับขึ้นรถแล้ว
                if (Array.isArray(window.activePassengerCalls) && window.activePassengerCalls.length > 0) {
                    filtered.forEach(item => {
                        const cid = String(item.call_id || item.id || item.timestamp || (item.station + '_' + (item.destination || '')));
                        const localItem = window.activePassengerCalls.find(c => {
                            const lcid = String(c.call_id || c.id || c.timestamp || (c.station + '_' + (c.destination || '')));
                            return lcid === cid;
                        });
                        if (localItem && (localItem.status === 'STEP2_DROPOFF' || (localItem.status || '').toLowerCase() === 'on_board')) {
                            item.status = 'STEP2_DROPOFF';
                        }
                    });
                }
                window.activePassengerCalls = filtered;
                window.latestPassengerCallData = filtered[0];

                if (typeof window.syncPassengerCallTargetStop === 'function') {
                    window.syncPassengerCallTargetStop(false);
                }
            } else {
                // หากมีผู้โดยสารที่กำลังอยู่บนรถ (STEP2_DROPOFF) อย่าเพิ่งลบออกจนกว่าจะถึงปลายทางจริง
                const hasOnBoard = Array.isArray(window.activePassengerCalls) && window.activePassengerCalls.some(c => {
                    const st = (c.status || '').toLowerCase();
                    return st === 'step2_dropoff' || st === 'on_board' || st === 'heading to destination';
                });
                if (!hasOnBoard) {
                    window.activePassengerCalls = [];
                    window.latestPassengerCallData = null;
                }
            }

            if (typeof window.enableDriverActionButtons === 'function') {
                window.enableDriverActionButtons();
            }

            updateCallAlertUI();
        })
        .catch(error => console.error('Error fetching driver tracking data:', error));
    };

    // 📡 Real-time BroadcastChannel Listener สำหรับการเรียกรถ
    try {
        const passengerBc = new BroadcastChannel('yru_trams_realtime_sync');
        passengerBc.addEventListener('message', function(ev) {
            if (ev.data && ev.data.type === 'PASSENGER_CALL') {
                const targetCar = ev.data.car_id || (ev.data.call ? ev.data.call.car_id : '');
                const dummyCheck = { car_id: targetCar };
                if (!window.isCallForActiveCar(dummyCheck)) {
                    return; // ไม่ใช่รถคันนี้ ห้ามเด้งขึ้นเด็ดขาด
                }
                const activeCar = typeof currentCarCode !== 'undefined' ? currentCarCode : 'EV-01';
                const callObj = ev.data.call || {
                    id: 'U' + Date.now().toString().slice(-6),
                    station: ev.data.station,
                    destination: ev.data.destination,
                    pax: ev.data.pax || 1,
                    car_id: targetCar || activeCar,
                    time: new Date().toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' }),
                    timestamp: Date.now(),
                    status: 'waiting'
                };
                addOrUpdatePassengerCall(callObj);
            }
        });
    } catch(e) {}

    // 📡 Real-time Storage Event Listener สำหรับการเรียกรถ
    window.addEventListener('storage', function(e) {
        if (!e || !e.key) return;
        const activeCar = typeof currentCarCode !== 'undefined' ? currentCarCode : 'EV-01';
        if (e.key === ('yru_latest_call_' + activeCar) || e.key === 'yru_latest_call') {
            try {
                if (e.newValue) {
                    const callObj = JSON.parse(e.newValue);
                    if (callObj && callObj.station && window.isCallForActiveCar(callObj)) {
                        addOrUpdatePassengerCall(callObj);
                    }
                }
            } catch(err) {}
        } else if (e.key === 'yru_call_queue') {
            checkPassengerCalls();
        }
    });

    // โหลดคำขอล่าสุดจาก localStorage ทันทีที่เปิดหน้า (เฉพาะของรถคันนี้เท่านั้น)
    try {
        const activeCar = typeof currentCarCode !== 'undefined' ? currentCarCode : 'EV-01';
        const rawInitial = localStorage.getItem('yru_latest_call_' + activeCar);
        if (rawInitial) {
            const parsed = JSON.parse(rawInitial);
            if (parsed && parsed.station && window.isCallForActiveCar(parsed) && (Date.now() - (parsed.timestamp || 0) < 3600000)) {
                addOrUpdatePassengerCall(parsed);
            }
        } else {
            const rawGlobal = localStorage.getItem('yru_latest_call');
            if (rawGlobal) {
                const parsedGlobal = JSON.parse(rawGlobal);
                if (parsedGlobal && parsedGlobal.station && window.isCallForActiveCar(parsedGlobal) && (Date.now() - (parsedGlobal.timestamp || 0) < 3600000)) {
                    addOrUpdatePassengerCall(parsedGlobal);
                }
            }
        }
    } catch(e) {}


    window.clearPassengerCall = function() {
        if (window.pendingPax) {
            changeSeatsOccupied(window.pendingPax);
            window.pendingPax = 0;
        }

        fetch('/api/clear-ev-request', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            }
        })
        .then(response => {
            if (!response.ok) throw new Error('API response error');
            return response.json();
        })
        .then(data => {
            if (data.status === 'success') {
                window.latestPassengerCallData = null;
                checkPassengerCalls();
            }
        })
        .catch(error => console.error('Error clearing data:', error));
    };

    function playAlertSound() {
        try {
            const audio = new Audio('https://assets.mixkit.co/active_storage/sfx/2869/2869-preview.mp3');
            audio.play();
        } catch(e) { 
            console.log("Audio playback was blocked by browser autoplay rules"); 
        }
    }

    // ตั้ง Polling ตรวจสอบสายเรียกเข้าจากผู้ใช้และความพร้อมการจำกัดสิทธิ์จากแอดมิน (หยุดทำงานชั่วคราวเมื่อสลับแท็บไปที่อื่น)
    setInterval(checkPassengerCalls, 4000);
    checkPassengerCalls();

    syncDriverStatus('normal');

    let signatureMode = 'pad';
    let signatureCanvas = null;
    let signatureCtx = null;
    let isDrawing = false;
    let hasDrawnSignature = false;
    let currentSignedDocBase64 = null;
    let currentSignedDocName = null;

    window.initSignatureCanvas = function() {
        signatureCanvas = document.getElementById('signatureCanvas');
        if (!signatureCanvas) return;
        
        const dpr = window.devicePixelRatio || 1;
        const rect = signatureCanvas.getBoundingClientRect();
        const w = (rect.width && rect.width > 20) ? Math.floor(rect.width) : (signatureCanvas.offsetWidth || 480);
        const h = 140;

        if (signatureCanvas.width !== Math.floor(w * dpr) || signatureCanvas.height !== Math.floor(h * dpr)) {
            signatureCanvas.width = Math.floor(w * dpr);
            signatureCanvas.height = Math.floor(h * dpr);
        }

        signatureCtx = signatureCanvas.getContext('2d');
        if (!signatureCtx) return;
        signatureCtx.scale(dpr, dpr);
        signatureCtx.strokeStyle = '#0f172a';
        signatureCtx.fillStyle = '#0f172a';
        signatureCtx.lineWidth = 2.8;
        signatureCtx.lineCap = 'round';
        signatureCtx.lineJoin = 'round';

        function getPos(e) {
            const r = signatureCanvas.getBoundingClientRect();
            const clientX = (e.touches && e.touches.length > 0) ? e.touches[0].clientX : ((e.changedTouches && e.changedTouches.length > 0) ? e.changedTouches[0].clientX : e.clientX);
            const clientY = (e.touches && e.touches.length > 0) ? e.touches[0].clientY : ((e.changedTouches && e.changedTouches.length > 0) ? e.changedTouches[0].clientY : e.clientY);
            return {
                x: clientX - r.left,
                y: clientY - r.top
            };
        }

        function startDrawing(e) {
            isDrawing = true;
            hasDrawnSignature = true;
            const pos = getPos(e);
            
            signatureCtx.beginPath();
            signatureCtx.moveTo(pos.x, pos.y);

            const placeholder = document.getElementById('canvasPlaceholder');
            if (placeholder) {
                placeholder.classList.add('hidden');
                placeholder.style.display = 'none';
            }
            if (e.touches && e.cancelable) e.preventDefault();
        }

        function draw(e) {
            if (!isDrawing) return;
            const pos = getPos(e);
            
            signatureCtx.lineTo(pos.x, pos.y);
            signatureCtx.stroke();

            const statusEl = document.getElementById('trackingCanvasSigStatus');
            if (statusEl) {
                statusEl.innerHTML = '<i class="fas fa-check-circle text-emerald-600"></i> <span class="text-emerald-700 font-bold">วาดลายเซ็นเรียบร้อยแล้ว</span>';
            }
            if (e.touches && e.cancelable) e.preventDefault();
        }

        function stopDrawing() {
            if (isDrawing) {
                isDrawing = false;
                signatureCtx.closePath();
            }
        }

        if (!signatureCanvas._hasSigEventsAttached) {
            signatureCanvas._hasSigEventsAttached = true;

            signatureCanvas.addEventListener('mousedown', startDrawing);
            signatureCanvas.addEventListener('mousemove', draw);
            signatureCanvas.addEventListener('mouseup', stopDrawing);
            signatureCanvas.addEventListener('mouseleave', stopDrawing);

            signatureCanvas.addEventListener('touchstart', startDrawing, { passive: false });
            signatureCanvas.addEventListener('touchmove', draw, { passive: false });
            signatureCanvas.addEventListener('touchend', stopDrawing);
            signatureCanvas.addEventListener('touchcancel', stopDrawing);
        }
    };

    window.clearSignatureCanvas = function() {
        if (signatureCanvas && signatureCtx) {
            signatureCtx.clearRect(0, 0, signatureCanvas.width, signatureCanvas.height);
            hasDrawnSignature = false;
            
            const placeholder = document.getElementById('canvasPlaceholder');
            if (placeholder) {
                placeholder.style.display = 'flex';
                placeholder.classList.remove('hidden');
            }
            const statusEl = document.getElementById('trackingCanvasSigStatus');
            if (statusEl) {
                statusEl.innerHTML = '<i class="fas fa-info-circle text-slate-400"></i> ยังไม่ได้วาดลายเซ็น';
            }
        }
    };

    window.switchSignatureMode = function(mode) {
        signatureMode = mode;
        const tabPad = document.getElementById('tabSigPad');
        const tabAuto = document.getElementById('tabSigAuto');
        const padCont = document.getElementById('sigPadContainer');
        const autoCont = document.getElementById('sigAutoContainer');

        if (mode === 'pad') {
            tabPad.className = 'px-2.5 py-1 text-[10px] font-bold rounded-md bg-white text-slate-800 shadow-xs transition cursor-pointer';
            tabAuto.className = 'px-2.5 py-1 text-[10px] font-bold rounded-md text-slate-500 hover:text-slate-800 transition cursor-pointer';
            if (padCont) padCont.classList.remove('hidden');
            if (autoCont) autoCont.classList.add('hidden');
        } else {
            tabAuto.className = 'px-2.5 py-1 text-[10px] font-bold rounded-md bg-white text-slate-800 shadow-xs transition cursor-pointer';
            tabPad.className = 'px-2.5 py-1 text-[10px] font-bold rounded-md text-slate-500 hover:text-slate-800 transition cursor-pointer';
            if (padCont) padCont.classList.add('hidden');
            if (autoCont) autoCont.classList.remove('hidden');
        }
    };

    window.handleSignedDocChange = function(event) {
        const file = event.target.files[0];
        if (!file) return;

        currentSignedDocName = file.name;
        
        const nameEl = document.getElementById('signedDocFileName');
        if (nameEl) nameEl.innerText = file.name;

        const sizeEl = document.getElementById('signedDocFileSize');
        if (sizeEl) {
            const kb = (file.size / 1024).toFixed(1);
            sizeEl.innerText = `${kb} KB`;
        }

        const iconEl = document.getElementById('signedDocFileIcon');
        if (iconEl) {
            if (file.type === 'application/pdf') {
                iconEl.className = 'fas fa-file-pdf text-rose-500 text-base shrink-0';
            } else {
                iconEl.className = 'fas fa-file-image text-pink-500 text-base shrink-0';
            }
        }

        const emptyView = document.getElementById('signedDocEmptyView');
        const fileView = document.getElementById('signedDocFileView');
        if (emptyView) emptyView.classList.add('hidden');
        if (fileView) fileView.classList.remove('hidden');

        const badge = document.getElementById('signedDocStatusBadge');
        if (badge) {
            badge.className = 'text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-700 border border-emerald-200 flex items-center gap-1 shrink-0';
            badge.innerHTML = '<i class="fas fa-check-circle text-[9px]"></i> แนบไฟล์เรียบร้อย';
        }

        const reader = new FileReader();
        reader.onload = function(e) {
            currentSignedDocBase64 = e.target.result;
        };
        reader.readAsDataURL(file);
    };

    window.removeSignedDocFile = function() {
        currentSignedDocBase64 = null;
        currentSignedDocName = null;
        const input = document.getElementById('driverSignedDocInput');
        if (input) input.value = '';

        const emptyView = document.getElementById('signedDocEmptyView');
        const fileView = document.getElementById('signedDocFileView');
        if (emptyView) emptyView.classList.remove('hidden');
        if (fileView) fileView.classList.add('hidden');

        const badge = document.getElementById('signedDocStatusBadge');
        if (badge) {
            badge.className = 'text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-amber-100 text-amber-700 border border-amber-200 flex items-center gap-1 shrink-0';
            badge.innerHTML = '<i class="fas fa-clock text-[9px]"></i> ยังไม่ได้แนบไฟล์';
        }
    };

    window.openDriverIssueReportModal = function() {
        try {
            const activeCar = typeof currentCarCode !== 'undefined' && currentCarCode ? currentCarCode : 'EV-01';
            const carSelect = document.getElementById('driverModalCarSelect');
            if (carSelect) carSelect.value = activeCar;

            try { updateDriverVehicleDetails(); } catch(e) { console.warn('updateDriverVehicleDetails error:', e); }

            const driverNameEl = document.getElementById('card-driver-name');
            const driverName = driverNameEl ? driverNameEl.innerText.trim() : 'นายอัสมี มูเล็ง';
            const modalDriverName = document.getElementById('driverModalDriverName');
            if (modalDriverName) modalDriverName.value = driverName;

            const sigDisplay = document.getElementById('driverSignatureDisplay');
            if (sigDisplay) sigDisplay.innerText = driverName;
            const sigAutoName = document.getElementById('sigAutoDriverName');
            if (sigAutoName) sigAutoName.innerText = driverName;

            try { switchSignatureMode('pad'); } catch(e) { console.warn('switchSignatureMode error:', e); }
            try { clearSignatureCanvas(); } catch(e) { console.warn('clearSignatureCanvas error:', e); }
            try { removeSignedDocFile(); } catch(e) { console.warn('removeSignedDocFile error:', e); }

            const dateDisplayEl = document.getElementById('driverModalDateDisplay');
            if (dateDisplayEl) {
                const thaiMonths = ['', 'มกราคม','กุมภาพันธ์','มีนาคม','เมษายน','พฤษภาคม','มิถุนายน','กรกฎาคม','สิงหาคม','กันยายน','ตุลาคม','พฤศจิกายน','ธันวาคม'];
                const now = new Date();
                dateDisplayEl.innerText = `วันที่ ${now.getDate()} เดือน ${thaiMonths[now.getMonth() + 1]} พ.ศ. ${now.getFullYear() + 543}`;
            }

            // Reset all 5 category dropdowns and detail boxes (All start unselected, matching initial state)
            for (let i = 1; i <= 5; i++) {
                const select = document.getElementById(`driverCatSelect_${i}`);
                if (select) select.value = '';
                const detailBox = document.getElementById(`catDetailBox_${i}`);
                if (detailBox) {
                    detailBox.classList.remove('hidden');
                    detailBox.style.display = 'block';
                }
                const textarea = document.getElementById(`catTextarea_${i}`);
                if (textarea) {
                    textarea.value = '';
                    textarea.placeholder = (i === 1) ? 'ระบุอาการหรือจุดที่ชำรุดเพิ่มเติม (เช่น ผ้าเบรกหมด, แป้นเบรกลึก, น้ำมันเบรกพร่อง...)' : 'ระบุอาการหรือจุดที่ชำรุดเพิ่มเติม...';
                }
                const card = document.getElementById(`repairItemCard_${i}`);
                if (card) {
                    card.classList.remove('border-pink-300', 'bg-pink-50/30');
                    card.classList.add('border-slate-200', 'bg-slate-50/80');
                    if (i === 1) {
                        card.classList.remove('hidden');
                        card.style.display = 'block';
                    } else {
                        card.classList.add('hidden');
                        card.style.display = 'none';
                    }
                }
            }
            if (typeof updateRepairItemsCountBadge === 'function') {
                updateRepairItemsCountBadge();
            }

            // Guarantee modal opens regardless of above errors
            const modal = document.getElementById('driverIssueReportModal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.style.display = 'flex';
                setTimeout(function() { try { initSignatureCanvas(); } catch(e) {} }, 150);
            } else {
                console.error('driverIssueReportModal element not found in DOM');
            }
        } catch(err) {
            console.error('openDriverIssueReportModal fatal error:', err);
            // Last resort: just show the modal
            const modal = document.getElementById('driverIssueReportModal');
            if (modal) {
                modal.classList.remove('hidden');
                modal.style.display = 'flex';
            }
        }
    };

    window.closeDriverIssueReportModal = function() {
        const modal = document.getElementById('driverIssueReportModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.style.display = 'none';
        }
    };

    const repairCategoryDetails = {
        'ระบบเบรก': {
            placeholder: 'ระบุอาการหรือจุดที่ชำรุดเพิ่มเติม (เช่น ผ้าเบรกหมด, แป้นเบรกลึก, น้ำมันเบรกพร่อง...)'
        },
        'ระบบยางและล้อ': {
            placeholder: 'ระบุอาการหรือจุดที่ชำรุดเพิ่มเติม (เช่น ลมยางซึม, ยางสึก, น็อตล้อหลวม...)'
        },
        'ระบบไฟฟ้า / แบตเตอรี่': {
            placeholder: 'ระบุอาการหรือจุดที่ชำรุดเพิ่มเติม (เช่น ไฟเลี้ยวไม่ติด, แบตเตอรี่ลดเร็ว, ชาร์จไม่เข้า...)'
        },
        'ตัวถัง / โครงสร้าง / กระจก': {
            placeholder: 'ระบุอาการหรือจุดที่ชำรุดเพิ่มเติม (เช่น กระจกมองข้างหลวม, สีขูดขีด, น็อตยึดหลุด...)'
        },
        'ระบบช่วงล่าง / มอเตอร์': {
            placeholder: 'ระบุอาการหรือจุดที่ชำรุดเพิ่มเติม (เช่น มีเสียงดังใต้ท้องรถ, มอเตอร์หอน, โช้คอัพรั่ว...)'
        },
        'อื่นๆ (ระบุ)': {
            placeholder: 'ระบุอาการหรือจุดที่ชำรุดเพิ่มเติม...'
        }
    };

        window.addNewRepairItem = function(e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        for (let i = 2; i <= 5; i++) {
            const card = document.getElementById(`repairItemCard_${i}`);
            if (card) {
                const isHidden = card.classList.contains('hidden') || card.style.display === 'none' || window.getComputedStyle(card).display === 'none';
                if (isHidden) {
                    card.classList.remove('hidden');
                    card.style.removeProperty('display');
                    card.style.display = 'block';
                    card.style.opacity = '1';
                    
                    const select = document.getElementById(`driverCatSelect_${i}`);
                    if (select) {
                        setTimeout(() => {
                            try { select.focus(); } catch(err) {}
                        }, 80);
                    }
                    
                    setTimeout(() => {
                        try { card.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); } catch(err) {}
                    }, 50);
                    
                    updateRepairItemsCountBadge();
                    return;
                }
            }
        }
    };

    window.removeRepairItem = function(index, e) {
        if (e) {
            e.preventDefault();
            e.stopPropagation();
        }
        const card = document.getElementById(`repairItemCard_${index}`);
        const select = document.getElementById(`driverCatSelect_${index}`);
        const textarea = document.getElementById(`catTextarea_${index}`);
        const detailBox = document.getElementById(`catDetailBox_${index}`);

        if (select) select.value = '';
        if (textarea) textarea.value = '';
        if (detailBox) {
            detailBox.classList.add('hidden');
            detailBox.style.display = 'none';
        }

        if (typeof onRepairCategoryChange === 'function') {
            onRepairCategoryChange(index);
        }

        if (card) {
            card.classList.add('hidden');
            card.style.display = 'none';
            card.classList.remove('border-pink-300', 'bg-pink-50/30');
            card.classList.add('border-slate-200');
        }
        updateRepairItemsCountBadge();
    };

    function updateRepairItemsCountBadge() {
        let count = 1;
        for (let i = 2; i <= 5; i++) {
            const card = document.getElementById(`repairItemCard_${i}`);
            if (card) {
                const isHidden = card.classList.contains('hidden') || card.style.display === 'none';
                if (!isHidden) count++;
            }
        }
        const badge = document.getElementById('repairItemCountBadge');
        if (badge) badge.innerText = `${count} / 5 รายการ`;

        const btnAdd = document.getElementById('btnAddRepairItem');
        if (btnAdd) {
            if (count >= 5) {
                btnAdd.classList.add('opacity-50', 'pointer-events-none', 'bg-slate-50', 'border-slate-200', 'text-slate-400');
                btnAdd.classList.remove('bg-pink-50', 'hover:bg-pink-100', 'text-pink-600', 'hover:text-pink-700', 'border-pink-300');
                btnAdd.innerHTML = '<i class="fas fa-check-circle text-emerald-500 text-sm"></i> <span>เพิ่มครบ 5 รายการแล้ว</span>';
            } else {
                btnAdd.classList.remove('opacity-50', 'pointer-events-none', 'bg-slate-50', 'border-slate-200', 'text-slate-400');
                btnAdd.classList.add('bg-pink-50', 'hover:bg-pink-100', 'text-pink-600', 'hover:text-pink-700', 'border-pink-300');
                btnAdd.innerHTML = '<i class="fas fa-plus-circle text-sm"></i> <span>+ เพิ่มรายการแจ้งซ่อม</span>';
            }
        }
    }

    // Attach event listeners on DOM ready for 100% guarantee
    document.addEventListener('DOMContentLoaded', () => {
        const btnAdd = document.getElementById('btnAddRepairItem');
        if (btnAdd) {
            btnAdd.addEventListener('click', (e) => {
                window.addNewRepairItem(e);
            });
        }
    });

            window.onRepairCategoryChange = function(index) {
        try {
            const select = document.getElementById(`driverCatSelect_${index}`);
            const detailBox = document.getElementById(`catDetailBox_${index}`);
            const textarea = document.getElementById(`catTextarea_${index}`);
            const card = document.getElementById(`repairItemCard_${index}`);

            if (!select) return;

            const val = select.value ? select.value.trim() : '';
            let data = repairCategoryDetails[val];
            if (!data && val) {
                for (let k in repairCategoryDetails) {
                    if (val.indexOf(k) !== -1 || k.indexOf(val) !== -1) {
                        data = repairCategoryDetails[k];
                        break;
                    }
                }
            }

            if (detailBox) {
                detailBox.style.display = 'block';
                detailBox.classList.remove('hidden');
            }

            if (textarea) {
                if (val && data && data.placeholder) {
                    textarea.placeholder = data.placeholder;
                } else {
                    textarea.placeholder = 'ระบุอาการหรือจุดที่ชำรุดเพิ่มเติม...';
                }
                setTimeout(() => {
                    try { textarea.focus(); } catch(err) {}
                }, 50);
            }

            if (card) {
                if (val) {
                    card.classList.add('border-pink-300', 'bg-pink-50/30', 'shadow-xs');
                    card.classList.remove('border-slate-200', 'bg-slate-50/80');
                } else {
                    card.classList.remove('border-pink-300', 'bg-pink-50/30', 'shadow-xs');
                    card.classList.add('border-slate-200', 'bg-slate-50/80');
                }
            }
        } catch(err) {
            console.error('onRepairCategoryChange error:', err);
        }
    };

    window.insertQuickSymptom = function(index, text) {
        const textarea = document.getElementById(`catTextarea_${index}`);
        if (!textarea) return;
        if (textarea.value.trim()) {
            textarea.value += ', ' + text;
        } else {
            textarea.value = text;
        }
        textarea.focus();
    };

    const vehiclePlatesMap = {
        'EV-01': 'กค 1234 ยะลา',
        'EV-02': 'กค 5678 ยะลา',
        'EV-03': 'กค 9101 ยะลา',
        'EV-04': 'กค 1121 ยะลา',
        'EV-05': 'กค 3141 ยะลา',
        'EV-06': 'กค 5161 ยะลา',
        'EV-07': 'กค 7181 ยะลา',
        'EV-08': 'กค 9202 ยะลา',
        'EV-09': 'กค 2224 ยะลา',
        'EV-10': 'กค 4246 ยะลา',
    };

    window.updateDriverVehicleDetails = function() {
        const carSelect = document.getElementById('driverModalCarSelect');
        const carCode = carSelect ? carSelect.value : 'EV-01';
        const plateEl = document.getElementById('driverModalPlate');
        if (plateEl) {
            plateEl.value = vehiclePlatesMap[carCode] || 'กค 1234 ยะลา';
        }
        const badgeEl = document.getElementById('driverModalCarBadge');
        if (badgeEl) {
            badgeEl.innerText = carCode;
        }
    };

    window.submitDriverIssueReport = async function(event) {
        if (event) event.preventDefault();

        const carCode = document.getElementById('driverModalCarSelect').value;
        const plate = document.getElementById('driverModalPlate').value;
        const driverName = document.getElementById('driverModalDriverName').value;
        const brand = document.getElementById('driverModalBrand').value || 'YRU EV';
        const model = document.getElementById('driverModalModel').value || 'Tram Electric 2026';
        const mileage = parseInt(document.getElementById('driverModalMileage').value) || 0;
        
        const issuesList = [];
        const categoriesList = [];
        const detailsList = [];

        for (let i = 1; i <= 5; i++) {
            const card = document.getElementById(`repairItemCard_${i}`);
            const isVisible = card && !card.classList.contains('hidden') && card.style.display !== 'none';

            const select = document.getElementById(`driverCatSelect_${i}`);
            const catVal = select ? select.value.trim() : '';
            const textarea = document.getElementById(`catTextarea_${i}`);
            const detVal = textarea ? textarea.value.trim() : '';

            if (isVisible) {
                if (i === 1 && !catVal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'กรุณาเลือกหมวดหมู่อาการ',
                        text: 'โปรดเลือกหมวดหมู่อาการที่ต้องการแจ้งซ่อมในรายการข้อที่ 1',
                        confirmButtonColor: '#ec4899',
                        customClass: { popup: 'font-kanit rounded-2xl' }
                    });
                    if (select) select.focus();
                    return;
                }

                if (catVal && !detVal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'กรุณาระบุรายละเอียดอาการเฉพาะเจาะจง',
                        text: `กรุณากรอกรายละเอียดอาการหรือจุดที่ชำรุดเพิ่มเติมในรายการแจ้งซ่อมข้อที่ ${i}`,
                        confirmButtonColor: '#ec4899',
                        customClass: { popup: 'font-kanit rounded-2xl' }
                    });
                    if (textarea) textarea.focus();
                    return;
                }

                if (!catVal && detVal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'กรุณาเลือกหมวดหมู่อาการ',
                        text: `กรุณาเลือกหมวดหมู่อาการที่ต้องการแจ้งซ่อมในรายการแจ้งซ่อมข้อที่ ${i}`,
                        confirmButtonColor: '#ec4899',
                        customClass: { popup: 'font-kanit rounded-2xl' }
                    });
                    if (select) select.focus();
                    return;
                }

                if (catVal && detVal) {
                    issuesList.push(`[${catVal}] ${detVal}`);
                    categoriesList.push(catVal);
                    detailsList.push(detVal);
                }
            }
        }

        if (issuesList.length === 0) {
            Swal.fire({
                icon: 'warning',
                title: 'กรุณากรอกข้อมูลการแจ้งซ่อม',
                text: 'โปรดเลือกหมวดหมู่อาการและระบุรายละเอียดอาการเฉพาะเจาะจงในรายการข้อที่ 1',
                confirmButtonColor: '#ec4899',
                customClass: { popup: 'font-kanit rounded-2xl' }
            });
            return;
        }

        const primaryCategory = categoriesList[0] || 'หมวดทั่วไป / อื่นๆ';
        const primaryDescription = detailsList.join(' | ') || issuesList[0] || '';

        let signatureDataUrl = null;
        if (signatureMode === 'pad') {
            if (!hasDrawnSignature || !signatureCanvas) {
                Swal.fire({
                    icon: 'warning',
                    title: 'กรุณาลงลายมือชื่อดิจิทัล',
                    text: 'กรุณาวาดลายเซ็นในช่องก่อนบันทึก',
                    confirmButtonColor: '#ec4899',
                    customClass: { popup: 'font-kanit rounded-2xl' }
                });
                return;
            }
            signatureDataUrl = signatureCanvas.toDataURL('image/png');
        }

        const payload = {
            car_id: carCode,
            plate_number: plate,
            license_plate: plate,
            driver_name: driverName,
            driver_signature: signatureDataUrl || driverName,
            signature_image: signatureDataUrl,
            brand: brand,
            model: model,
            current_mileage: mileage,
            mileage: mileage,
            category: primaryCategory,
            description: primaryDescription,
            issue_category: primaryCategory,
            issue_description: primaryDescription,
            categories: categoriesList,
            symptoms: issuesList,
            issues: issuesList,
            urgency: 'ด่วน',
            signed_document_name: currentSignedDocName,
            signed_document_url: currentSignedDocBase64,
            attachment_name: currentSignedDocName,
            attachment_url: currentSignedDocBase64
        };

        try {
            Swal.fire({
                title: 'กำลังส่งคำขอแจ้งซ่อม...',
                allowOutsideClick: false,
                didOpen: () => Swal.showLoading(),
                customClass: { popup: 'font-kanit rounded-2xl' }
            });

            const res = await fetch('/api/maintenance/requests', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': '{{ csrf_token() }}'
                },
                body: JSON.stringify(payload)
            });

            const result = await res.json();
            if (result.status === 'success' || result.data) {
                closeDriverIssueReportModal();

                // Save to local yru_maintenance_tickets_v3 so all local pages reflect immediately
                let localTk = null;
                try {
                    const createdTk = result.data || {};
                    const tkNo = createdTk.ticket_no || result.ticket_no || `MNT-${Date.now()}`;
                    localTk = {
                        id: tkNo,
                        ticket_no: tkNo,
                        car_id: carCode,
                        license_plate: plate,
                        brand: brand,
                        model: model,
                        mileage: mileage,
                        category: primaryCategory,
                        description: primaryDescription,
                        issues: issuesList,
                        status: 'รอการอนุมัติ',
                        urgency: 'ด่วน',
                        driver_name: driverName,
                        driver_signature: signatureDataUrl || driverName,
                        signature_image: signatureDataUrl,
                        driver_signature_img: signatureDataUrl,
                        doc_date: '{{ date("d") }}',
                        doc_month: '{{ ["", "มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม"][date("n")] }}',
                        doc_year: '{{ date("Y") + 543 }}',
                        created_at: new Date().toISOString()
                    };

                    const localTickets = JSON.parse(localStorage.getItem('yru_maintenance_tickets_v3') || '[]');
                    localTickets.unshift(localTk);
                    localStorage.setItem('yru_maintenance_tickets_v3', JSON.stringify(localTickets));
                } catch(e) {
                    console.error("Local storage sync error:", e);
                }

                Swal.fire({
                    icon: 'success',
                    title: '<div class="text-slate-800 font-extrabold text-lg md:text-xl pt-1">บันทึกข้อมูลและส่งเรื่องเรียบร้อยแล้ว</div>',
                    html: `
                        <div class="space-y-2 mt-1 font-kanit">
                            <div class="bg-emerald-50 border border-emerald-200 rounded-xl p-3.5 inline-block w-full">
                                <p class="font-bold text-pink-600 text-base">เลขที่เอกสาร: ${result.data?.ticket_no || localTk?.ticket_no || 'MNT-NEW'}</p>
                                <p class="text-slate-700 font-medium text-xs mt-1">ส่งต่อเรื่องให้ <b class="text-slate-900">หัวหน้าหน่วยงานยานพาหนะ</b> ตรวจสอบเรียบร้อยแล้ว</p>
                            </div>
                            <p class="text-[11px] text-slate-500 text-center leading-relaxed">ท่านสามารถพิมพ์เอกสารใบขออนุญาตซ่อมขนาด A4 พร้อมลายมือชื่อดิจิทัลเพื่อแนบแฟ้มประวัติตามระเบียบราชการได้ทันที</p>
                        </div>
                    `,
                    showCancelButton: true,
                    confirmButtonColor: '#ec4899',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: '🖨️ พิมพ์เอกสารใบซ่อมเป็น PDF',
                    cancelButtonText: 'ปิดหน้าต่าง',
                    customClass: {
                        popup: 'font-kanit rounded-3xl p-5 max-w-[420px]',
                        confirmButton: 'px-5 py-2.5 rounded-xl font-bold text-xs',
                        cancelButton: 'px-5 py-2.5 rounded-xl font-bold text-xs'
                    }
                }).then((subResult) => {
                    if (subResult.isConfirmed) {
                        printDriverReportFormFromModal(localTk || result.data);
                    }
                });

                // Submitting maintenance request does NOT change vehicle status to broken
                // The driver remains operating and can log in at all times
            } else {
                throw new Error(result.message || 'ไม่สามารถส่งข้อมูลได้');
            }
        } catch (e) {
            Swal.fire({
                icon: 'error',
                title: 'เกิดข้อผิดพลาด',
                text: e.message || 'ไม่สามารถเชื่อมต่อเซิร์ฟเวอร์ได้',
                confirmButtonColor: '#ec4899',
                customClass: { popup: 'font-kanit rounded-2xl' }
            });
        }
    };

    
</script>

<!-- ใบคำขออนุญาตซ่อมบำรุงรถไฟฟ้า (Driver Form) - ดีไซน์ตามแบบฟอร์ม Word -->
<div id="driverIssueReportModal" class="fixed inset-0 z-50 flex items-center justify-center p-3 md:p-4 bg-slate-900/60 backdrop-blur-xs hidden animate-fade-in font-kanit">
    <div class="bg-white rounded-3xl shadow-2xl w-full max-w-xl overflow-hidden flex flex-col max-h-[92vh] border border-pink-100">
        
        <!-- 📌 STICKY HEADER: ส่วนหัว Modal และ แถบข้อมูลรถ ตรึงอยู่ด้านบนสุดตลอดเวลา -->
        <div class="sticky top-0 z-30 bg-white flex-shrink-0 shadow-xs border-b border-slate-100">
            <!-- HEADER BANNER (Pink Gradient) with integrated close button -->
            <div class="bg-gradient-to-r from-pink-600 via-pink-500 to-rose-500 text-white px-4 py-3 text-center relative">
                <button type="button" onclick="closeDriverIssueReportModal()" class="absolute top-2.5 right-3 text-white/80 hover:text-white bg-white/10 hover:bg-white/20 w-7 h-7 rounded-full flex items-center justify-center transition cursor-pointer">
                    <i class="fas fa-times text-xs"></i>
                </button>
                <div class="inline-flex items-center gap-2">
                    <div class="w-6 h-6 rounded-full bg-white/20 flex items-center justify-center text-xs text-white">
                        <i class="fas fa-wrench"></i>
                    </div>
                    <h2 class="text-sm md:text-base font-extrabold text-white tracking-tight">ใบแจ้งซ่อมและนำส่งรถไฟฟ้าเพื่อซ่อมบำรุง</h2>
                </div>
            </div>

            <!-- ข้อมูลผู้ขับและยานพาหนะ (แบบกระชับ เรียบง่าย ตรึงอยู่บนสุด ไม่ล้นกรอบ) -->
            <div class="bg-slate-50/95 backdrop-blur-md px-4 py-2 border-t border-white/20 font-kanit">
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2 text-xs">
                    <!-- ผู้แจ้ง -->
                    <div class="min-w-0 bg-white px-2.5 py-1 rounded-xl border border-slate-200/70 shadow-2xs overflow-hidden">
                        <span class="text-[9px] font-bold text-slate-400 block leading-none mb-0.5 truncate">ผู้แจ้งซ่อม</span>
                        <input type="text" id="driverModalDriverName" value="นายอัสมี มูเล็ง" class="w-full bg-transparent font-bold text-slate-800 text-xs outline-none truncate" required>
                    </div>
                    <!-- ทะเบียนรถ -->
                    <div class="min-w-0 bg-white px-2.5 py-1 rounded-xl border border-slate-200/70 shadow-2xs overflow-hidden">
                        <span class="text-[9px] font-bold text-slate-400 block leading-none mb-0.5 truncate">ทะเบียน / ขบวน</span>
                        <div class="flex items-center gap-1 min-w-0">
                            <span id="driverModalCarBadge" class="hidden">EV-01</span>
                            <input type="text" id="driverModalPlate" value="กค 1234 ยะลา" class="w-full bg-transparent font-bold text-slate-700 text-xs outline-none truncate" readonly>
                        </div>
                    </div>
                    <!-- ยี่ห้อ/รุ่น -->
                    <div class="min-w-0 bg-white px-2.5 py-1 rounded-xl border border-slate-200/70 shadow-2xs overflow-hidden">
                        <span class="text-[9px] font-bold text-slate-400 block leading-none mb-0.5 truncate">ยี่ห้อ / รุ่น</span>
                        <div class="flex items-center gap-1 min-w-0">
                            <input type="text" id="driverModalBrand" value="YRU EV" class="w-[46px] shrink-0 bg-transparent font-medium text-slate-600 text-xs outline-none truncate">
                            <span class="text-slate-300 shrink-0">/</span>
                            <input type="text" id="driverModalModel" value="Tram 2026" class="min-w-0 flex-1 bg-transparent font-medium text-slate-600 text-xs outline-none truncate">
                        </div>
                    </div>
                    <!-- เลขไมล์ -->
                    <div class="min-w-0 bg-white px-2.5 py-1 rounded-xl border border-slate-200/70 shadow-2xs overflow-hidden">
                        <span class="text-[9px] font-bold text-slate-400 block leading-none mb-0.5 truncate">มาตรวัดระยะทาง</span>
                        <div class="flex items-center gap-1 min-w-0">
                            <input type="number" id="driverModalMileage" value="15420" class="min-w-0 flex-1 bg-transparent font-bold font-mono text-slate-700 text-xs outline-none truncate" required>
                            <span class="text-[9px] text-slate-400 font-bold shrink-0">กม.</span>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- hidden: car select -->
        <select id="driverModalCarSelect" onchange="updateDriverVehicleDetails()" class="hidden">
            <option value="EV-01">EV-01</option>
            <option value="EV-02">EV-02</option>
            <option value="EV-03">EV-03</option>
            <option value="EV-04">EV-04</option>
            <option value="EV-05">EV-05</option>
            <option value="EV-06">EV-06</option>
            <option value="EV-07">EV-07</option>
            <option value="EV-08">EV-08</option>
            <option value="EV-09">EV-09</option>
            <option value="EV-10">EV-10</option>
        </select>

        <!-- 📜 SCROLLABLE FORM BODY -->
        <form onsubmit="submitDriverIssueReport(event)" class="flex flex-col flex-1 overflow-hidden">
            <div class="p-4 md:p-5 space-y-4 overflow-y-auto flex-1">

                <!-- รายการขอตรวจซ่อมบำรุง (แบบ Dynamic Add Item + Dropdown) -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-xs p-4 space-y-3.5 font-kanit">
                    <div class="flex items-center justify-between border-b border-gray-100 pb-2.5">
                        <div class="flex items-center gap-2">
                            <div class="w-7 h-7 rounded-xl bg-pink-100 text-pink-600 flex items-center justify-center text-xs font-bold shadow-xs">
                                <i class="fas fa-list-check"></i>
                            </div>
                            <div>
                                <span class="text-xs font-bold text-slate-800">รายการขอตรวจซ่อมบำรุง <span class="text-pink-500">*</span></span>
                                <p class="text-[10px] text-slate-400">เลือกหมวดหมู่และระบุรายละเอียดอาการที่ชำรุด</p>
                            </div>
                        </div>
                        <span id="repairItemCountBadge" class="text-[10px] font-bold px-2.5 py-0.5 bg-slate-100 text-slate-600 rounded-full">1 / 5 รายการ</span>
                    </div>

                    <!-- List of Repair Item Cards -->
                    <div class="space-y-3">
                        
                        <!-- รายการที่ 1 (จำเป็น) -->
                        <div id="repairItemCard_1" class="border border-slate-200 rounded-2xl bg-slate-50/80 p-3.5 transition-all duration-300 space-y-2.5">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-md bg-pink-600 text-white text-[10px] font-black flex items-center justify-center shadow-xs">1</span>
                                    <span class="text-xs font-bold text-slate-800">รายการแจ้งซ่อมข้อที่ 1 <span class="text-pink-600">*</span></span>
                                </div>
                                <span class="text-[9px] font-bold px-2 py-0.5 bg-pink-100 text-pink-700 rounded-full">จำเป็น</span>
                            </div>
                            <div>
                                <select id="driverCatSelect_1" onchange="window.onRepairCategoryChange(1)" oninput="window.onRepairCategoryChange(1)" class="w-full bg-white border border-slate-200 focus:border-pink-500 focus:ring-2 focus:ring-pink-100 rounded-xl px-3 py-2 text-xs font-bold text-slate-800 outline-none transition cursor-pointer shadow-2xs">
                                    <option value="" selected>-- เลือกหมวดหมู่อาการที่ต้องการแจ้งซ่อม (ข้อ 1) --</option>
                                    <option value="ระบบเบรก">ระบบเบรก</option>
                                    <option value="ระบบยางและล้อ">ระบบยางและล้อ</option>
                                    <option value="ระบบไฟฟ้า / แบตเตอรี่">ระบบไฟฟ้า / แบตเตอรี่</option>
                                    <option value="ตัวถัง / โครงสร้าง / กระจก">ตัวถัง / โครงสร้าง / กระจก</option>
                                    <option value="ระบบช่วงล่าง / มอเตอร์">ระบบช่วงล่าง / มอเตอร์</option>
                                    <option value="อื่นๆ (ระบุ)">อื่นๆ (ระบุ)</option>
                                </select>
                            </div>
                            <div id="catDetailBox_1" class="space-y-1 pt-1.5 border-t border-pink-200/60 transition-all duration-300">
                                <div class="flex items-center justify-between">
                                    <label class="text-[10px] font-bold text-slate-700 block">รายละเอียดอาการเฉพาะเจาะจง <span class="text-pink-600 font-bold">*</span></label>
                                    <span class="text-[9px] font-bold text-pink-600">(จำเป็น)</span>
                                </div>
                                <textarea id="catTextarea_1" rows="2" class="w-full h-16 min-h-[58px] bg-white border border-pink-200 focus:border-pink-500 focus:ring-2 focus:ring-pink-100 rounded-xl p-2.5 text-xs text-slate-800 placeholder:text-slate-400 outline-none transition resize-y shadow-2xs leading-relaxed" placeholder="ระบุอาการหรือจุดที่ชำรุดเพิ่มเติม (เช่น ผ้าเบรกหมด, แป้นเบรกลึก, น้ำมันเบรกพร่อง...)" required></textarea>
                            </div>
                        </div>

                        <!-- รายการที่ 2 (ถ้ามี) -->
                        <div id="repairItemCard_2" class="border border-slate-200 rounded-2xl bg-slate-50/80 p-3.5 transition-all duration-300 space-y-2.5 hidden" style="display: none;">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-md bg-slate-400 text-white text-[10px] font-black flex items-center justify-center">2</span>
                                    <span class="text-xs font-bold text-slate-700">รายการแจ้งซ่อมข้อที่ 2</span>
                                </div>
                                <button type="button" onclick="window.removeRepairItem(2, event)" class="text-slate-400 hover:text-rose-500 hover:bg-rose-50 px-2 py-0.5 rounded-lg text-[11px] font-bold transition flex items-center gap-1 cursor-pointer">
                                    <i class="fas fa-trash-alt text-[10px]"></i>
                                    <span>ลบรายการนี้</span>
                                </button>
                            </div>
                            <div>
                                <select id="driverCatSelect_2" onchange="window.onRepairCategoryChange(2)" oninput="window.onRepairCategoryChange(2)" class="w-full bg-white border border-slate-200 focus:border-pink-500 focus:ring-2 focus:ring-pink-100 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 outline-none transition cursor-pointer shadow-2xs">
                                    <option value="" selected>-- เลือกหมวดหมู่อาการที่ต้องการแจ้งซ่อม (ข้อ 2) --</option>
                                    <option value="ระบบเบรก">ระบบเบรก</option>
                                    <option value="ระบบยางและล้อ">ระบบยางและล้อ</option>
                                    <option value="ระบบไฟฟ้า / แบตเตอรี่">ระบบไฟฟ้า / แบตเตอรี่</option>
                                    <option value="ตัวถัง / โครงสร้าง / กระจก">ตัวถัง / โครงสร้าง / กระจก</option>
                                    <option value="ระบบช่วงล่าง / มอเตอร์">ระบบช่วงล่าง / มอเตอร์</option>
                                    <option value="อื่นๆ (ระบุ)">อื่นๆ (ระบุ)</option>
                                </select>
                            </div>
                            <div id="catDetailBox_2" class="space-y-1 pt-1.5 border-t border-slate-200/60 transition-all duration-300">
                                <div class="flex items-center justify-between">
                                    <label class="text-[10px] font-bold text-slate-700 block">รายละเอียดอาการเฉพาะเจาะจง <span class="text-pink-600 font-bold">*</span></label>
                                    <span class="text-[9px] text-slate-400">เขียนอธิบายอาการที่พบ</span>
                                </div>
                                <textarea id="catTextarea_2" rows="2" class="w-full h-16 min-h-[58px] bg-white border border-slate-200 focus:border-pink-500 focus:ring-2 focus:ring-pink-100 rounded-xl p-2.5 text-xs text-slate-800 placeholder:text-slate-400 outline-none transition resize-y shadow-2xs leading-relaxed" placeholder="ระบุอาการหรือจุดที่ชำรุดเพิ่มเติม..."></textarea>
                            </div>
                        </div>

                        <!-- รายการที่ 3 (ถ้ามี) -->
                        <div id="repairItemCard_3" class="border border-slate-200 rounded-2xl bg-slate-50/80 p-3.5 transition-all duration-300 space-y-2.5 hidden" style="display: none;">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-md bg-slate-400 text-white text-[10px] font-black flex items-center justify-center">3</span>
                                    <span class="text-xs font-bold text-slate-700">รายการแจ้งซ่อมข้อที่ 3</span>
                                </div>
                                <button type="button" onclick="window.removeRepairItem(3, event)" class="text-slate-400 hover:text-rose-500 hover:bg-rose-50 px-2 py-0.5 rounded-lg text-[11px] font-bold transition flex items-center gap-1 cursor-pointer">
                                    <i class="fas fa-trash-alt text-[10px]"></i>
                                    <span>ลบรายการนี้</span>
                                </button>
                            </div>
                            <div>
                                <select id="driverCatSelect_3" onchange="window.onRepairCategoryChange(3)" oninput="window.onRepairCategoryChange(3)" class="w-full bg-white border border-slate-200 focus:border-pink-500 focus:ring-2 focus:ring-pink-100 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 outline-none transition cursor-pointer shadow-2xs">
                                    <option value="" selected>-- เลือกหมวดหมู่อาการที่ต้องการแจ้งซ่อม (ข้อ 3) --</option>
                                    <option value="ระบบเบรก">ระบบเบรก</option>
                                    <option value="ระบบยางและล้อ">ระบบยางและล้อ</option>
                                    <option value="ระบบไฟฟ้า / แบตเตอรี่">ระบบไฟฟ้า / แบตเตอรี่</option>
                                    <option value="ตัวถัง / โครงสร้าง / กระจก">ตัวถัง / โครงสร้าง / กระจก</option>
                                    <option value="ระบบช่วงล่าง / มอเตอร์">ระบบช่วงล่าง / มอเตอร์</option>
                                    <option value="อื่นๆ (ระบุ)">อื่นๆ (ระบุ)</option>
                                </select>
                            </div>
                            <div id="catDetailBox_3" class="space-y-1 pt-1.5 border-t border-slate-200/60 transition-all duration-300">
                                <div class="flex items-center justify-between">
                                    <label class="text-[10px] font-bold text-slate-700 block">รายละเอียดอาการเฉพาะเจาะจง <span class="text-pink-600 font-bold">*</span></label>
                                    <span class="text-[9px] text-slate-400">เขียนอธิบายอาการที่พบ</span>
                                </div>
                                <textarea id="catTextarea_3" rows="2" class="w-full h-16 min-h-[58px] bg-white border border-slate-200 focus:border-pink-500 focus:ring-2 focus:ring-pink-100 rounded-xl p-2.5 text-xs text-slate-800 placeholder:text-slate-400 outline-none transition resize-y shadow-2xs leading-relaxed" placeholder="ระบุอาการหรือจุดที่ชำรุดเพิ่มเติม..."></textarea>
                            </div>
                        </div>

                        <!-- รายการที่ 4 (ถ้ามี) -->
                        <div id="repairItemCard_4" class="border border-slate-200 rounded-2xl bg-slate-50/80 p-3.5 transition-all duration-300 space-y-2.5 hidden" style="display: none;">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-md bg-slate-400 text-white text-[10px] font-black flex items-center justify-center">4</span>
                                    <span class="text-xs font-bold text-slate-700">รายการแจ้งซ่อมข้อที่ 4</span>
                                </div>
                                <button type="button" onclick="window.removeRepairItem(4, event)" class="text-slate-400 hover:text-rose-500 hover:bg-rose-50 px-2 py-0.5 rounded-lg text-[11px] font-bold transition flex items-center gap-1 cursor-pointer">
                                    <i class="fas fa-trash-alt text-[10px]"></i>
                                    <span>ลบรายการนี้</span>
                                </button>
                            </div>
                            <div>
                                <select id="driverCatSelect_4" onchange="window.onRepairCategoryChange(4)" oninput="window.onRepairCategoryChange(4)" class="w-full bg-white border border-slate-200 focus:border-pink-500 focus:ring-2 focus:ring-pink-100 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 outline-none transition cursor-pointer shadow-2xs">
                                    <option value="" selected>-- เลือกหมวดหมู่อาการที่ต้องการแจ้งซ่อม (ข้อ 4) --</option>
                                    <option value="ระบบเบรก">ระบบเบรก</option>
                                    <option value="ระบบยางและล้อ">ระบบยางและล้อ</option>
                                    <option value="ระบบไฟฟ้า / แบตเตอรี่">ระบบไฟฟ้า / แบตเตอรี่</option>
                                    <option value="ตัวถัง / โครงสร้าง / กระจก">ตัวถัง / โครงสร้าง / กระจก</option>
                                    <option value="ระบบช่วงล่าง / มอเตอร์">ระบบช่วงล่าง / มอเตอร์</option>
                                    <option value="อื่นๆ (ระบุ)">อื่นๆ (ระบุ)</option>
                                </select>
                            </div>
                            <div id="catDetailBox_4" class="space-y-1 pt-1.5 border-t border-slate-200/60 transition-all duration-300">
                                <div class="flex items-center justify-between">
                                    <label class="text-[10px] font-bold text-slate-700 block">รายละเอียดอาการเฉพาะเจาะจง <span class="text-pink-600 font-bold">*</span></label>
                                    <span class="text-[9px] text-slate-400">เขียนอธิบายอาการที่พบ</span>
                                </div>
                                <textarea id="catTextarea_4" rows="2" class="w-full h-16 min-h-[58px] bg-white border border-slate-200 focus:border-pink-500 focus:ring-2 focus:ring-pink-100 rounded-xl p-2.5 text-xs text-slate-800 placeholder:text-slate-400 outline-none transition resize-y shadow-2xs leading-relaxed" placeholder="ระบุอาการหรือจุดที่ชำรุดเพิ่มเติม..."></textarea>
                            </div>
                        </div>

                        <!-- รายการที่ 5 (ถ้ามี) -->
                        <div id="repairItemCard_5" class="border border-slate-200 rounded-2xl bg-slate-50/80 p-3.5 transition-all duration-300 space-y-2.5 hidden" style="display: none;">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-2">
                                    <span class="w-5 h-5 rounded-md bg-slate-400 text-white text-[10px] font-black flex items-center justify-center">5</span>
                                    <span class="text-xs font-bold text-slate-700">รายการแจ้งซ่อมข้อที่ 5</span>
                                </div>
                                <button type="button" onclick="window.removeRepairItem(5, event)" class="text-slate-400 hover:text-rose-500 hover:bg-rose-50 px-2 py-0.5 rounded-lg text-[11px] font-bold transition flex items-center gap-1 cursor-pointer">
                                    <i class="fas fa-trash-alt text-[10px]"></i>
                                    <span>ลบรายการนี้</span>
                                </button>
                            </div>
                            <div>
                                <select id="driverCatSelect_5" onchange="window.onRepairCategoryChange(5)" oninput="window.onRepairCategoryChange(5)" class="w-full bg-white border border-slate-200 focus:border-pink-500 focus:ring-2 focus:ring-pink-100 rounded-xl px-3 py-2 text-xs font-bold text-slate-700 outline-none transition cursor-pointer shadow-2xs">
                                    <option value="" selected>-- เลือกหมวดหมู่อาการที่ต้องการแจ้งซ่อม (ข้อ 5) --</option>
                                    <option value="ระบบเบรก">ระบบเบรก</option>
                                    <option value="ระบบยางและล้อ">ระบบยางและล้อ</option>
                                    <option value="ระบบไฟฟ้า / แบตเตอรี่">ระบบไฟฟ้า / แบตเตอรี่</option>
                                    <option value="ตัวถัง / โครงสร้าง / กระจก">ตัวถัง / โครงสร้าง / กระจก</option>
                                    <option value="ระบบช่วงล่าง / มอเตอร์">ระบบช่วงล่าง / มอเตอร์</option>
                                    <option value="อื่นๆ (ระบุ)">อื่นๆ (ระบุ)</option>
                                </select>
                            </div>
                            <div id="catDetailBox_5" class="space-y-1 pt-1.5 border-t border-slate-200/60 transition-all duration-300">
                                <div class="flex items-center justify-between">
                                    <label class="text-[10px] font-bold text-slate-700 block">รายละเอียดอาการเฉพาะเจาะจง <span class="text-pink-600 font-bold">*</span></label>
                                    <span class="text-[9px] text-slate-400">เขียนอธิบายอาการที่พบ</span>
                                </div>
                                <textarea id="catTextarea_5" rows="2" class="w-full h-16 min-h-[58px] bg-white border border-slate-200 focus:border-pink-500 focus:ring-2 focus:ring-pink-100 rounded-xl p-2.5 text-xs text-slate-800 placeholder:text-slate-400 outline-none transition resize-y shadow-2xs leading-relaxed" placeholder="ระบุอาการหรือจุดที่ชำรุดเพิ่มเติม..."></textarea>
                            </div>
                        </div>
                    </div>

                    <!-- ➕ BUTTON: เพิ่มรายการแจ้งซ่อม -->
                    <div id="addMoreRepairItemWrapper" class="pt-2 border-t border-slate-100">
                        <button type="button" id="btnAddRepairItem" onclick="window.addNewRepairItem && window.addNewRepairItem(event); return false;" class="w-full py-3 px-4 rounded-xl bg-pink-50 hover:bg-pink-100 active:bg-pink-200 text-pink-600 hover:text-pink-700 font-bold text-xs border-2 border-dashed border-pink-300 hover:border-pink-400 transition-all duration-200 flex items-center justify-center gap-2 shadow-xs cursor-pointer select-none">
                            <i class="fas fa-plus-circle text-base text-pink-600"></i>
                            <span>+ เพิ่มรายการแจ้งซ่อม (ข้อ 2 - 5)</span>
                        </button>
                    </div>
                </div>

                <!-- ลายมือชื่อดิจิทัล -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-xs p-4 space-y-3 font-kanit">
                <!-- ═══════ ลงลายมือชื่อดิจิทัล (Standardized UI/UX) ═══════ -->
                <div class="bg-gradient-to-br from-slate-50 to-pink-50/30 rounded-2xl border border-pink-200/80 p-3.5 space-y-2.5 font-kanit shadow-2xs">
                    <div class="flex items-center justify-between gap-2 flex-wrap sm:flex-nowrap">
                        <div class="flex items-center gap-1.5 text-xs font-bold text-slate-800">
                            <span class="text-pink-500">✍️</span> ลงลายมือชื่อดิจิทัล <span class="text-rose-500">*</span>
                        </div>
                        <div class="flex items-center bg-pink-100/80 text-pink-700 px-2.5 py-1 rounded-lg text-[10px] font-bold gap-1 shadow-2xs">
                            ✍️ วาดลายเซ็นสด
                        </div>
                    </div>

                    <!-- Mode 1: Signature Canvas -->
                    <div id="sigPadContainer" class="space-y-2 pt-0.5">
                        <div class="relative bg-white border-2 border-dashed border-pink-300 hover:border-pink-400 rounded-xl overflow-hidden touch-none group shadow-2xs transition">
                            <canvas id="signatureCanvas" class="w-full h-[140px] cursor-crosshair bg-white block" style="touch-action: none !important; user-select: none; -webkit-user-select: none;"></canvas>
                            <div id="canvasPlaceholder" class="absolute inset-0 flex items-center justify-center text-slate-400 text-xs font-medium gap-1.5 pointer-events-none" style="pointer-events: none !important; user-select: none;">
                                <i class="fas fa-pen-nib text-pink-400"></i> ใช้นิ้วหรือเมาส์วาดลายเซ็นของคุณในช่องนี้
                            </div>
                            <button type="button" onclick="clearSignatureCanvas()" class="absolute top-2 right-2 bg-slate-100 hover:bg-rose-500 hover:text-white text-slate-600 text-[10px] font-bold px-2.5 py-1 rounded-lg transition flex items-center gap-1 cursor-pointer border border-slate-200 shadow-2xs">
                                <i class="fas fa-eraser"></i> ล้างลายเซ็น
                            </button>
                        </div>
                        <div class="flex items-center justify-between text-xs px-1">
                            <span id="trackingCanvasSigStatus" class="text-[11px] text-slate-500 font-medium flex items-center gap-1">
                                <i class="fas fa-info-circle text-slate-400"></i> ยังไม่ได้วาดลายเซ็น
                            </span>
                            <span class="text-[10px] text-pink-700 bg-pink-100/80 px-2 py-0.5 rounded-full font-bold">
                                พนักงานขับรถ
                            </span>
                        </div>
                    </div>

                    <!-- Mode 2: Auto System Signature -->
                    <div id="sigAutoContainer" class="hidden bg-white border border-pink-200 rounded-xl p-3 text-center shadow-2xs">
                        <div class="flex items-baseline justify-center gap-1.5 text-[12px] text-slate-700 pt-0.5">
                            <span class="font-medium">ลงชื่อ</span>
                            <span id="driverSignatureDisplay" class="font-bold text-slate-800 border-b border-dotted border-pink-300 px-4 pb-0.5"><!-- JS fills --></span>
                            <span class="font-medium">พนักงานขับรถ</span>
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1">(ลงชื่ออัตโนมัติจากระบบอิเล็กทรอนิกส์พร้อมประทับตราเวลา)</p>
                    </div>
                </div>

                <!-- แนบเอกสารเพิ่มเติม -->
                <div class="bg-slate-50 rounded-2xl border border-gray-200 p-3 space-y-2">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-1.5 text-xs font-bold text-slate-700">
                            <i class="fas fa-paperclip text-slate-400"></i> แนบเอกสารเพิ่มเติม (ถ้ามี)
                        </div>
                        <span id="signedDocStatusBadge" class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-slate-200 text-slate-600 flex items-center gap-1">
                            ไม่บังคับ
                        </span>
                    </div>

                    <input type="file" id="driverSignedDocInput" accept="image/*,application/pdf" class="hidden" onchange="handleSignedDocChange(event)">
                    
                    <div id="signedDocContainer" class="bg-white border border-dashed border-gray-300 hover:border-pink-400 rounded-xl p-2.5 text-center transition cursor-pointer" onclick="document.getElementById('driverSignedDocInput').click()">
                        <div id="signedDocEmptyView" class="flex items-center justify-center gap-2 text-slate-500 text-xs font-medium">
                            <i class="fas fa-cloud-upload-alt text-pink-400"></i> คลิกแนบไฟล์ประกอบเพิ่มเติม (PDF/รูปภาพ)
                        </div>
                        <div id="signedDocFileView" class="hidden flex items-center justify-between bg-emerald-50 border border-emerald-200 rounded-lg p-2">
                            <div class="flex items-center gap-2 overflow-hidden text-left">
                                <i id="signedDocFileIcon" class="fas fa-file-pdf text-rose-500 text-base shrink-0"></i>
                                <div class="truncate">
                                    <p id="signedDocFileName" class="text-xs font-bold text-slate-800 truncate">document.pdf</p>
                                    <p id="signedDocFileSize" class="text-[10px] font-mono text-emerald-700">250 KB</p>
                                </div>
                            </div>
                            <button type="button" onclick="event.stopPropagation(); removeSignedDocFile()" class="text-slate-400 hover:text-rose-600 p-1 transition cursor-pointer">
                                <i class="fas fa-times"></i>
                            </button>
                        </div>
                    </div>
                </div>

            </div>

            <!-- ปุ่มกด (Sticky Footer) -->
            <div class="bg-gray-50 border-t border-gray-200/80 px-5 py-3.5 flex items-center justify-center gap-3 shrink-0 z-20">
                <button type="submit" class="bg-gradient-to-r from-pink-600 to-rose-600 hover:from-pink-700 hover:to-rose-700 active:scale-95 text-white px-7 py-3 rounded-2xl text-xs font-extrabold shadow-lg shadow-pink-500/25 flex items-center justify-center gap-2 transition cursor-pointer font-kanit">
                    <i class="fas fa-check-circle text-sm"></i> ยืนยัน
                </button>
                <button type="button" onclick="var m=document.getElementById('driverIssueReportModal'); if(m){ m.classList.add('hidden'); m.style.display='none'; } if(typeof closeDriverIssueReportModal==='function'){ closeDriverIssueReportModal(); }" class="bg-gray-200 hover:bg-gray-300 active:scale-95 text-gray-700 px-5 py-3 rounded-2xl text-xs font-bold transition cursor-pointer font-kanit">
                    ยกเลิก
                </button>
            </div>

        </form>
    </div>
</div>
</div>

@include('passenger.maintenance.partials.printable-form-modal')

<script>
window.addNewRepairItem = function(e) {
    if (e) {
        try { e.preventDefault(); e.stopPropagation(); } catch(err) {}
    }
    for (let i = 2; i <= 5; i++) {
        const card = document.getElementById('repairItemCard_' + i);
        if (card) {
            const isHidden = card.classList.contains('hidden') || card.style.display === 'none' || window.getComputedStyle(card).display === 'none';
            if (isHidden) {
                card.classList.remove('hidden');
                card.style.removeProperty('display');
                card.style.display = 'block';
                card.style.opacity = '1';
                
                const sel = document.getElementById('driverCatSelect_' + i);
                if (sel) {
                    setTimeout(() => { try { sel.focus(); } catch(err) {} }, 80);
                }
                
                setTimeout(() => {
                    try { card.scrollIntoView({ behavior: 'smooth', block: 'nearest' }); } catch(err) {}
                }, 50);
                
                if (typeof updateRepairItemsCountBadge === 'function') {
                    updateRepairItemsCountBadge();
                }
                return false;
            }
        }
    }
    return false;
};

window.removeRepairItem = function(index, e) {
    if (e) {
        try { e.preventDefault(); e.stopPropagation(); } catch(err) {}
    }
    const card = document.getElementById('repairItemCard_' + index);
    const select = document.getElementById('driverCatSelect_' + index);
    const textarea = document.getElementById('catTextarea_' + index);
    const detailBox = document.getElementById('catDetailBox_' + index);

    if (select) select.value = '';
    if (textarea) textarea.value = '';
    if (detailBox) {
        detailBox.classList.add('hidden');
        detailBox.style.display = 'none';
    }

    if (typeof onRepairCategoryChange === 'function') {
        onRepairCategoryChange(index);
    }

    if (card) {
        card.classList.add('hidden');
        card.style.display = 'none';
        card.classList.remove('border-pink-300', 'bg-pink-50/30');
        card.classList.add('border-slate-200');
    }
    if (typeof updateRepairItemsCountBadge === 'function') {
        updateRepairItemsCountBadge();
    }
    return false;
};

function updateRepairItemsCountBadge() {
    let count = 1;
    for (let i = 2; i <= 5; i++) {
        const card = document.getElementById('repairItemCard_' + i);
        if (card) {
            const isHidden = card.classList.contains('hidden') || card.style.display === 'none';
            if (!isHidden) count++;
        }
    }
    const badge = document.getElementById('repairItemCountBadge');
    if (badge) badge.innerText = `${count} / 5 รายการ`;

    const btnAdd = document.getElementById('btnAddRepairItem');
    if (btnAdd) {
        if (count >= 5) {
            btnAdd.classList.add('opacity-50', 'pointer-events-none', 'bg-slate-50', 'border-slate-200', 'text-slate-400');
            btnAdd.classList.remove('bg-pink-50', 'hover:bg-pink-100', 'active:bg-pink-200', 'text-pink-600', 'hover:text-pink-700', 'border-pink-300', 'hover:border-pink-400');
            btnAdd.innerHTML = '<i class="fas fa-check-circle text-emerald-500 text-sm"></i> <span>เพิ่มครบ 5 รายการแล้ว</span>';
        } else {
            btnAdd.classList.remove('opacity-50', 'pointer-events-none', 'bg-slate-50', 'border-slate-200', 'text-slate-400');
            btnAdd.classList.add('bg-pink-50', 'hover:bg-pink-100', 'active:bg-pink-200', 'text-pink-600', 'hover:text-pink-700', 'border-pink-300', 'hover:border-pink-400');
            btnAdd.innerHTML = '<i class="fas fa-plus-circle text-base text-pink-600"></i> <span>+ เพิ่มรายการแจ้งซ่อม (ข้อ 2 - 5)</span>';
        }
    }
}
</script>
<script>
window.printDriverReportFormFromModal = function(customTicket) {
    if (customTicket && (customTicket.signature_image || customTicket.driver_signature || customTicket.issues)) {
        if (typeof printDirectly === 'function') {
            printDirectly(customTicket);
            return;
        } else if (typeof openPrintableFormModal === 'function') {
            openPrintableFormModal(customTicket, true);
            printOfficialForm();
            return;
        }
    }

    const driver = document.getElementById('driverModalDriverName')?.value || 'นายอัสมี มูเล็ง';
    const plate = document.getElementById('driverModalPlate')?.value || 'กค 1234 ยะลา';
    const brand = document.getElementById('driverModalBrand')?.value || 'YRU EV';
    const model = document.getElementById('driverModalModel')?.value || 'Tram Electric 2026';
    const mileage = document.getElementById('driverModalMileage')?.value || '0';
    
    let sigImg = null;
    if (signatureMode === 'pad' && hasDrawnSignature && signatureCanvas) {
        try {
            sigImg = signatureCanvas.toDataURL('image/png');
        } catch(e) {}
    }

    const issues = [];
    for (let i = 1; i <= 5; i++) {
        const select = document.getElementById(`driverCatSelect_${i}`);
        const catVal = select ? select.value.trim() : '';
        const textarea = document.getElementById(`catTextarea_${i}`);
        const detVal = textarea ? textarea.value.trim() : '';
        if (catVal) {
            const finalDet = detVal || 'ขอตรวจเช็คสภาพ';
            issues.push(`[${catVal}] ${finalDet}`);
        }
    }

    const ticket = {
        id: 'MNT-DRAFT',
        driver_name: driver,
        driver_signature: sigImg || driver,
        signature_image: sigImg,
        driver_signature_img: sigImg,
        license_plate: plate,
        brand: brand,
        model: model,
        mileage: mileage,
        issues: issues.length > 0 ? issues : ['ตรวจเช็คสภาพทั่วไป'],
        doc_date: '{{ date("d") }}',
        doc_month: '{{ ["", "มกราคม","กุมภาพันธ์","มีนาคม","เมษายน","พฤษภาคม","มิถุนายน","กรกฎาคม","สิงหาคม","กันยายน","ตุลาคม","พฤศจิกายน","ธันวาคม"][date("n")] }}',
        doc_year: '{{ date("Y") + 543 }}'
    };

    if (typeof printDirectly === 'function') {
        printDirectly(ticket);
    } else if (typeof openPrintableFormModal === 'function') {
        openPrintableFormModal(ticket, true);
        printOfficialForm();
    } else {
        window.print();
    }
};

// ===== Real-time Map and Vehicle Status Synchronization matching Passenger view =====
function fetchDriverStatusRealtime() {
    fetch('/api/get-driver-status?t=' + new Date().getTime())
    .then(response => {
        if (!response.ok) throw new Error('API server status invalid');
        return response.json();
    })
    .then(data => {
        trams.forEach((tram, index) => {
            const carKey = 'car_' + (index + 1);
            if (data[carKey]) {
                updateCarStatusUI(tram.id, data[carKey]);
            }
        });
        if (typeof debouncedInitTramMarkers === 'function') debouncedInitTramMarkers();
    })
    .catch(error => console.error('Error fetching driver status:', error));
}

function updateCarStatusUI(tramId, data) {
    if (!data || !globalCarStatus[tramId]) return;

    let localBreak = false;
    try {
        const rawStored = localStorage.getItem('yru_car_status_' + tramId);
        if (rawStored) {
            const parsed = JSON.parse(rawStored);
            if (parsed.status === 'pause' || parsed.status === 'พักเบรค' || parsed.status === 'พักเบรก' || (parsed.status && parsed.status.includes('พัก'))) {
                localBreak = true;
            }
        }
    } catch(e) {}

    let status = data.status || 'normal';
    if (localBreak) status = 'pause';

    let statusLabel = 'ปกติกำลังขับ';
    if (status === 'normal' || status === 'พร้อมใช้งาน' || status === 'ปกติกำลังขับ') statusLabel = 'ปกติกำลังขับ';
    else if (status === 'pause' || status === 'พักเบรค' || status === 'พักเบรก') statusLabel = 'พักเบรค';
    else if (status === 'broken' || status === 'รถขัดข้อง') statusLabel = 'รถขัดข้อง';
    else if (status === 'suspended' || status === 'ระงับการใช้งาน' || status === 'ระงับใช้งาน') statusLabel = 'ระงับการใช้งาน';
    else if (status === 'ended' || status === 'สิ้นสุดรอบวิ่ง' || status === 'เลิกงาน') statusLabel = 'สิ้นสุดรอบวิ่ง';
    else statusLabel = status;

    globalCarStatus[tramId].status = statusLabel;
}

function fetchSeatsLeft() {
    if (typeof getStorage === 'function') {
        trams = getStorage("yru_trams_v18", defaultTrams);
    }
    trams.forEach(t => {
        if (globalCarStatus[t.id]) {
            globalCarStatus[t.id].status = t.status || 'พร้อมใช้งาน';
        }
    });

    fetch('/api/get-seats-left?t=' + new Date().getTime())
    .then(response => {
        if (!response.ok) throw new Error('API response invalid');
        return response.json();
    })
    .then(data => {
        trams.forEach((tram) => {
            let occupied = 0;
            if (data.car_occupied_map && data.car_occupied_map[tram.id] !== undefined) {
                occupied = data.car_occupied_map[tram.id];
            } else if (tram.id === 'EV-01' && data.car_1_occupied !== undefined) {
                occupied = data.car_1_occupied;
            } else if (tram.id === 'EV-02' && data.car_2_occupied !== undefined) {
                occupied = data.car_2_occupied;
            }
            const max = (tram.capacity_sit !== undefined) ? parseInt(tram.capacity_sit) : 10;
            if (globalCarStatus[tram.id]) {
                globalCarStatus[tram.id].occupied = occupied;
                globalCarStatus[tram.id].isFull = occupied >= max;
            }
        });
        if (typeof debouncedInitTramMarkers === 'function') debouncedInitTramMarkers();
    })
    .catch(err => console.error('Error fetching seats left:', err));
}

function syncRealtimeData() {
    if (typeof getStorage === 'function') {
        trams = getStorage("yru_trams_v18", defaultTrams);
    }
    if (typeof debouncedInitTramMarkers === 'function') {
        debouncedInitTramMarkers();
    } else if (typeof initTramMarkers === 'function') {
        initTramMarkers();
    }
}

window.addEventListener('storage', function(e) {
    if (!e.key || e.key.startsWith('yru_trams') || e.key.startsWith('yru_stops') || e.key.includes('status')) {
        syncRealtimeData();
    }
});

try {
    const syncChannel = new BroadcastChannel('yru_trams_realtime_sync');
    syncChannel.onmessage = function(ev) {
        syncRealtimeData();
        if (ev.data && (ev.data.type === 'MAINTENANCE_UPDATED' || ev.data.type === 'MAINTENANCE_SUPERVISOR_VERIFIED' || ev.data.type === 'MAINTENANCE_REJECTED')) {
            if (typeof handleDriverMaintenanceBroadcast === 'function') {
                handleDriverMaintenanceBroadcast(ev.data);
            }
            if (typeof renderDriverMaintenanceStatus === 'function') {
                renderDriverMaintenanceStatus();
            }
        }
    };
} catch (e) {}

// Driver Maintenance Status Card & Header Notifications Renderer
window.renderDriverMaintenanceStatus = function() {
    const container = document.getElementById('driverMaintenanceStatusContainer');
    const headerBadge = document.getElementById('header-return-msg-badge');
    const bellBtn = document.getElementById('header-bell-btn');
    const inboxContent = document.getElementById('driverInboxContent');
    const banner = document.getElementById('maintenance-return-msg-banner');
    const urlCar = new URLSearchParams(window.location.search).get('car');
    const activeCar = urlCar ? urlCar : (typeof currentCarCode !== 'undefined' && currentCarCode ? currentCarCode : 'EV-01');

    let tickets = [];
    try {
        tickets = JSON.parse(localStorage.getItem('yru_maintenance_tickets_v3') || '[]');
    } catch(e) {}

    function isTicketForCar(t, targetCar) {
        if (!t || !targetCar) return false;
        const strTarget = String(targetCar).trim().toUpperCase();
        const tCarId = String(t.car_id || t.tram_id || t.car_code || t.car || '').trim().toUpperCase();
        if (!tCarId || tCarId === 'EV' || tCarId === 'CAR') return false;
        
        if (tCarId === strTarget) return true;
        
        const mTarget = strTarget.match(/EV-?0*(\d+)/i) || strTarget.match(/(\d+)/);
        const mTicket = tCarId.match(/EV-?0*(\d+)/i) || tCarId.match(/(\d+)/);
        if (mTarget && mTicket) {
            return (mTarget[1] === mTicket[1]);
        }
        
        return false;
    }

    // Strictly filter rejected tickets for the ACTIVE CAR ONLY!
    const rejectedTickets = tickets.filter(t => {
        const isRej = (
            t.status === 'rejected' ||
            t.status === 'ไม่อนุมัติ' ||
            t.status === 'ตีกลับ' ||
            t.director_opinion === 'rejected' ||
            t.supervisor_opinion === 'rejected' ||
            (Array.isArray(t.rejected_items) && t.rejected_items.length > 0)
        );
        if (!isRej) return false;
        return isTicketForCar(t, activeCar);
    });

    const carTickets = tickets.filter(t => isTicketForCar(t, activeCar));
    // Find all completed tickets for this car that are unacknowledged by driver
    const completedTickets = carTickets.filter(t => (t.status === 'completed' || t.status === 'ซ่อมเสร็จสิ้น') && !t.driver_ack);
    const pendingSupTicket = carTickets.find(t => t.status === 'pending' || t.status === 'pending_supervisor' || t.status === 'รอการอนุมัติ');

    const totalNotifs = rejectedTickets.length + completedTickets.length;

    // Update Header Badge (Top Right Bell Icon)
    if (headerBadge) {
        if (totalNotifs > 0) {
            headerBadge.innerText = totalNotifs;
            headerBadge.classList.remove('hidden');
            if (rejectedTickets.length > 0) {
                headerBadge.className = "absolute -top-1 -right-1 bg-rose-500 text-white font-black text-[9px] min-w-5 h-5 px-1 rounded-full flex items-center justify-center ring-2 ring-white shadow-sm animate-pulse pointer-events-none select-none";
            } else {
                headerBadge.className = "absolute -top-1 -right-1 bg-emerald-500 text-white font-black text-[9px] min-w-5 h-5 px-1 rounded-full flex items-center justify-center ring-2 ring-white shadow-sm animate-pulse pointer-events-none select-none";
            }
        } else {
            headerBadge.classList.add('hidden');
        }
    }

    if (bellBtn) {
        if (totalNotifs > 0) {
            bellBtn.className = "relative w-10 h-10 bg-rose-500/30 hover:bg-rose-500/40 active:scale-95 rounded-full text-white transition flex items-center justify-center cursor-pointer shadow-md ring-2 ring-rose-400 pointer-events-auto";
        } else {
            bellBtn.className = "relative w-10 h-10 bg-white/20 hover:bg-white/30 active:scale-95 rounded-full text-white transition flex items-center justify-center cursor-pointer shadow-sm pointer-events-auto";
        }
    }

    // Update Bouncing Message Box Banner on Dashboard for Rejected Tickets
    if (banner) {
        if (rejectedTickets.length > 0 && !window._returnBannerDismissed) {
            const latestRej = rejectedTickets[0];
            const rejItems = (Array.isArray(latestRej.rejected_items) && latestRej.rejected_items.length > 0)
                ? latestRej.rejected_items
                : (Array.isArray(latestRej.issues) ? latestRej.issues : [latestRej.issue || 'อาการที่แจ้ง']);

            const titleEl = document.getElementById('return-msg-title');
            const badgeEl = document.getElementById('return-msg-ticket-badge');
            const itemsListEl = document.getElementById('return-msg-items-list');
            const notesEl = document.getElementById('return-msg-notes');
            const timeEl = document.getElementById('return-msg-time');

            if (titleEl) titleEl.innerText = `มี ${rejItems.length} รายการถูกตีกลับ (ไม่อนุญาต)`;
            if (badgeEl) badgeEl.innerText = latestRej.ticket_no || latestRej.id || 'MNT-REJ';
            if (timeEl) timeEl.innerText = latestRej.date || 'ล่าสุด';
            if (notesEl) notesEl.innerText = latestRej.supervisor_notes || 'ไม่มีบันทึกเพิ่มเติมจากหัวหน้ายานพาหนะ';
            
            if (itemsListEl) {
                itemsListEl.innerHTML = rejItems.map(it => {
                    const r = (latestRej.item_reject_reasons && latestRej.item_reject_reasons[it]) ? latestRej.item_reject_reasons[it] : '';
                    return `<li class="py-0.5"><b>${it}</b>${r ? `<span class="text-rose-700 block text-[11px] font-normal pl-2.5">↳ เหตุผล: ${r}</span>` : ''}</li>`;
                }).join('');
            }

            window._latestRejectedItems = rejItems;
            banner.classList.remove('hidden');
        } else if (rejectedTickets.length === 0) {
            banner.classList.add('hidden');
            window._returnBannerDismissed = false;
        }
    }

    // Update Inbox Modal Content (Top Right Bell Dropdown/Modal)
    if (inboxContent) {
        if (totalNotifs === 0) {
            inboxContent.innerHTML = `
                <div class="text-center py-10 px-4 font-kanit space-y-3">
                    <div class="w-16 h-16 rounded-3xl bg-emerald-50 border border-emerald-100 flex items-center justify-center mx-auto text-emerald-500 text-2xl shadow-inner animate-pulse">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div>
                        <h4 class="text-sm font-extrabold text-slate-800">ไม่มีข้อความแจ้งเตือนใหม่</h4>
                        <p class="text-xs text-slate-500 mt-1 max-w-xs mx-auto">คำขอแจ้งซ่อมทั้งหมดได้รับการดำเนินการตามปกติ ไม่มีรายการที่โดนตีกลับหรือรอการรับทราบ</p>
                    </div>
                    <div class="pt-1">
                        <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100/80 text-emerald-700 text-[11px] font-bold">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 animate-ping"></span> สถานะระบบปกติ
                        </span>
                    </div>
                </div>
            `;
        } else {
            let inboxHtml = '';

            // Render Rejected Tickets Notifications
            rejectedTickets.forEach(tk => {
                const rejItems = (Array.isArray(tk.rejected_items) && tk.rejected_items.length > 0)
                    ? tk.rejected_items
                    : (Array.isArray(tk.issues) ? tk.issues : [tk.issue || 'อาการที่แจ้ง']);
                const rejJson = JSON.stringify(rejItems).replace(/"/g, '&quot;');

                inboxHtml += `
                    <div class="bg-rose-50/90 border border-rose-200/90 rounded-2xl p-4 space-y-3 shadow-sm hover:shadow-md transition">
                        <div class="flex items-center justify-between border-b border-rose-200/60 pb-2.5">
                            <span class="text-xs font-black text-rose-700 flex items-center gap-1.5">
                                <span class="w-2 h-2 rounded-full bg-rose-500 animate-ping"></span>
                                <i class="fas fa-exclamation-triangle text-rose-600"></i> ใบแจ้งซ่อมถูกตีกลับ (ไม่อนุญาต)
                            </span>
                            <span class="text-[10px] font-mono font-bold bg-white text-rose-700 px-2.5 py-0.5 rounded-full border border-rose-200 shadow-2xs">
                                ${tk.ticket_no || tk.id}
                            </span>
                        </div>
                        <div class="bg-white/90 rounded-xl p-3 border border-rose-100 space-y-2 text-xs">
                            <span class="text-[11px] text-rose-600 font-bold block">รายการที่ไม่ผ่านการอนุมัติ:</span>
                            <ul class="list-disc pl-4 space-y-1 font-bold text-slate-800">
                                ${rejItems.map(it => {
                                    const r = (tk.item_reject_reasons && tk.item_reject_reasons[it]) ? tk.item_reject_reasons[it] : '';
                                    return `<li class="py-0.5"><span>${it}</span>${r ? `<div class="text-[11px] font-normal text-rose-700 bg-rose-50 rounded-lg p-2 border border-rose-200 mt-1"><i class="fas fa-comment-dots text-[9px] mr-1 text-rose-500"></i><b>เหตุผล:</b> ${r}</div>` : ''}</li>`;
                                }).join('')}
                            </ul>
                            ${tk.supervisor_notes ? `
                            <div class="text-[11px] text-slate-700 bg-amber-50/80 p-2.5 rounded-xl border border-amber-200 mt-2">
                                <span class="font-bold text-amber-900">เหตุผลเพิ่มเติมจากหัวหน้ายาน:</span>
                                <p class="mt-0.5 italic text-slate-700">${tk.supervisor_notes}</p>
                            </div>` : ''}
                        </div>
                        <button type="button" onclick="closeDriverInboxModal(); openDriverIssueReportModalWithItems(${rejJson});" class="w-full bg-gradient-to-r from-rose-600 to-pink-600 hover:from-rose-700 hover:to-pink-700 active:scale-95 text-white font-extrabold py-2.5 px-4 rounded-xl text-xs flex items-center justify-center gap-2 transition cursor-pointer shadow-md shadow-rose-600/20 font-kanit">
                            <i class="fas fa-edit"></i> เปิดฟอร์มแจ้งซ่อมใหม่ (แก้ไขรายการนี้)
                        </button>
                    </div>
                `;
            });

            // Render Completed Tickets Notifications (ซ่อมเสร็จแล้ว)
            completedTickets.forEach(tk => {
                const compItems = (Array.isArray(tk.approved_items) && tk.approved_items.length > 0)
                    ? tk.approved_items
                    : (Array.isArray(tk.issues) ? tk.issues : [tk.issue || 'ซ่อมแซมและตรวจเช็คเรียบร้อยแล้ว']);
                const compId = tk.ticket_no || tk.id;

                inboxHtml += `
                    <div class="bg-emerald-50/90 border border-emerald-200/90 rounded-2xl p-4 space-y-3 shadow-sm hover:shadow-md transition">
                        <div class="flex items-center justify-between border-b border-emerald-200/60 pb-2.5">
                            <span class="text-xs font-black text-emerald-800 flex items-center gap-1.5">
                                <i class="fas fa-check-circle text-emerald-600 text-sm"></i> ซ่อมรถไฟฟ้าเสร็จสมบูรณ์แล้ว
                            </span>
                            <span class="text-[10px] font-mono font-bold bg-white text-emerald-700 px-2.5 py-0.5 rounded-full border border-emerald-200 shadow-2xs">
                                ${compId}
                            </span>
                        </div>
                        <div class="bg-white/90 rounded-xl p-3 border border-emerald-100 space-y-2 text-xs">
                            <span class="text-[11px] text-emerald-700 font-bold block">รายการที่ดำเนินการซ่อมเสร็จสิ้น:</span>
                            <ul class="list-disc pl-4 space-y-1 font-bold text-emerald-950">
                                ${compItems.map(it => `<li>${it}</li>`).join('')}
                            </ul>
                            <div class="text-[11px] text-slate-600 mt-2 pt-2 border-t border-emerald-100 flex items-center justify-between">
                                <span><b>สถานะรถไฟฟ้า:</b> <span class="text-emerald-600 font-extrabold">พร้อมใช้งาน 100%</span></span>
                                <span class="text-slate-400 text-[10px]">${tk.date || ''}</span>
                            </div>
                        </div>
                        <button type="button" onclick="acknowledgeCompletedTicket('${compId}')" class="w-full bg-emerald-600 hover:bg-emerald-700 active:scale-95 text-white font-extrabold py-2.5 px-4 rounded-xl text-xs flex items-center justify-center gap-2 transition cursor-pointer shadow-md shadow-emerald-600/20 font-kanit">
                            <i class="fas fa-check-double"></i> รับทราบ / ปิดการแจ้งเตือน
                        </button>
                    </div>
                `;
            });

            inboxContent.innerHTML = inboxHtml;
        }
    }

    if (!container) return;
    if (carTickets.length === 0) {
        container.innerHTML = '';
        return;
    }

    let html = '';

    if (pendingSupTicket) {
        html += `
            <div class="bg-amber-50/90 border border-amber-200 rounded-2xl p-2.5 space-y-1 font-kanit shadow-xs">
                <div class="flex items-center justify-between text-xs">
                    <span class="font-bold text-amber-800 flex items-center gap-1.5">
                        <i class="fas fa-hourglass-half text-amber-600"></i> รอหัวหน้ายานพาหนะตรวจสอบ
                    </span>
                    <span class="text-[9.5px] font-mono font-bold bg-white text-amber-700 px-2 py-0.5 rounded-full border border-amber-200">
                        ${pendingSupTicket.ticket_no || pendingSupTicket.id}
                    </span>
                </div>
            </div>
        `;
    }

    container.innerHTML = html;
};

window.acknowledgeCompletedTicket = function(ticketId) {
    try {
        let tickets = JSON.parse(localStorage.getItem('yru_maintenance_tickets_v3') || '[]');
        const idx = tickets.findIndex(t => String(t.ticket_no || t.id) === String(ticketId));
        if (idx !== -1) {
            tickets[idx].driver_ack = true;
            localStorage.setItem('yru_maintenance_tickets_v3', JSON.stringify(tickets));
        }
    } catch(e) {}

    renderDriverMaintenanceStatus();
};

window.handleDriverMaintenanceBroadcast = function(data) {
    renderDriverMaintenanceStatus();
};

window.dismissReturnMsgBanner = function() {
    window._returnBannerDismissed = true;
    const banner = document.getElementById('maintenance-return-msg-banner');
    if (banner) banner.classList.add('hidden');
};

window.handleReturnMsgAction = function() {
    window.dismissReturnMsgBanner();
    if (window._latestRejectedItems) {
        openDriverIssueReportModalWithItems(window._latestRejectedItems);
    } else {
        openDriverIssueReportModal();
    }
};

window.openDriverInboxModal = async function() {
    const modal = document.getElementById('driverInboxModal');
    if (modal) {
        modal.classList.remove('hidden');
        modal.style.display = 'flex';
        modal.style.visibility = 'visible';
        modal.style.opacity = '1';
        modal.style.zIndex = '9999999';
        modal.style.pointerEvents = 'auto';
    }
    if (typeof renderDriverMaintenanceStatus === 'function') {
        try { renderDriverMaintenanceStatus(); } catch(e) {}
    }
    try {
        const res = await fetch('/api/maintenance/requests?t=' + new Date().getTime());
        if (res.ok) {
            const json = await res.json();
            const serverList = Array.isArray(json) ? json : (json.data || []);
            if (Array.isArray(serverList) && serverList.length > 0) {
                let localTickets = [];
                try {
                    localTickets = JSON.parse(localStorage.getItem('yru_maintenance_tickets_v3') || '[]');
                } catch(e) {}
                const mergedMap = new Map();
                serverList.forEach(t => mergedMap.set(String(t.ticket_no || t.id), t));
                localTickets.forEach(t => {
                    const k = String(t.ticket_no || t.id);
                    if (mergedMap.has(k)) {
                        mergedMap.set(k, { ...mergedMap.get(k), ...t });
                    } else {
                        mergedMap.set(k, t);
                    }
                });
                localStorage.setItem('yru_maintenance_tickets_v3', JSON.stringify(Array.from(mergedMap.values())));
                if (typeof renderDriverMaintenanceStatus === 'function') {
                    renderDriverMaintenanceStatus();
                }
            }
        }
    } catch(e) {}
};

window.closeDriverInboxModal = function() {
    const modal = document.getElementById('driverInboxModal');
    if (modal) {
        modal.classList.add('hidden');
        modal.style.display = 'none';
    }
};

// Open Report Modal pre-filled with rejected items
window.openDriverIssueReportModalWithItems = function(items) {
    window._bypassPendingCheck = true;
    if (typeof openDriverIssueReportModal === 'function') {
        openDriverIssueReportModal();
    }

    if (Array.isArray(items) && items.length > 0) {
        setTimeout(() => {
            items.forEach((iss, idx) => {
                const itemNum = idx + 1;
                if (itemNum > 5) return;

                if (itemNum >= 2) {
                    const card = document.getElementById(`repairItemCard_${itemNum}`);
                    if (card) {
                        card.classList.remove('hidden');
                        card.style.display = 'block';
                        card.style.opacity = '1';
                    }
                }

                let cat = '';
                let desc = iss;
                const match = String(iss).match(/^\[(.*?)\]\s*(.*)$/);
                if (match) {
                    cat = match[1].trim();
                    desc = match[2].trim();
                }

                const sel = document.getElementById(`driverCatSelect_${itemNum}`);
                const inp = document.getElementById(`catTextarea_${itemNum}`);

                if (sel) {
                    if (cat) {
                        for (let opt of sel.options) {
                            if (opt.value.includes(cat) || cat.includes(opt.value)) {
                                sel.value = opt.value;
                                break;
                            }
                        }
                        if (!sel.value && cat) {
                            sel.value = 'อื่นๆ (ระบุ)';
                        }
                    }
                    if (typeof onRepairCategoryChange === 'function') {
                        onRepairCategoryChange(itemNum);
                    }
                }

                if (inp) {
                    inp.value = desc;
                }
            });

            if (typeof updateRepairItemsCountBadge === 'function') {
                updateRepairItemsCountBadge();
            }
        }, 120);
    }
};

// Handle Incoming Maintenance Supervisor Verification Broadcast
window.handleDriverMaintenanceBroadcast = function(data) {
    const activeCar = typeof currentCarCode !== 'undefined' && currentCarCode ? currentCarCode : 'EV-01';

    // Update local storage ticket record if provided
    if (data && data.ticket_no) {
        try {
            let localTickets = JSON.parse(localStorage.getItem('yru_maintenance_tickets_v3') || '[]');
            const idx = localTickets.findIndex(t => String(t.ticket_no) === String(data.ticket_no) || String(t.id) === String(data.ticket_no));
            if (idx !== -1) {
                localTickets[idx].status = data.status || localTickets[idx].status;
                localTickets[idx].approved_items = data.approved_items || [];
                localTickets[idx].rejected_items = data.rejected_items || [];
                localTickets[idx].supervisor_notes = data.supervisor_notes || localTickets[idx].supervisor_notes;
                localStorage.setItem('yru_maintenance_tickets_v3', JSON.stringify(localTickets));
            }
        } catch(e) {}
    }

    if (typeof renderDriverMaintenanceStatus === 'function') {
        renderDriverMaintenanceStatus();
    }
    // Notification update for driver screen - Update bell icon badge only (no center modal)
    if (typeof syncDriverMaintenanceNotifications === 'function') {
        syncDriverMaintenanceNotifications();
    } else if (typeof renderDriverMaintenanceStatus === 'function') {
        renderDriverMaintenanceStatus();
    }
};

window.syncDriverMaintenanceNotifications = async function() {
    try {
        const res = await fetch('/api/maintenance/requests');
        const json = await res.json();
        if (json && json.status === 'success' && Array.isArray(json.data)) {
            const serverList = json.data;
            let localTickets = [];
            try {
                localTickets = JSON.parse(localStorage.getItem('yru_maintenance_tickets_v3') || '[]');
            } catch(e) {}

            const mergedMap = new Map();
            serverList.forEach(t => mergedMap.set(String(t.ticket_no || t.id), t));
            localTickets.forEach(t => {
                const key = String(t.ticket_no || t.id);
                if (mergedMap.has(key)) {
                    mergedMap.set(key, { ...mergedMap.get(key), ...t });
                } else {
                    mergedMap.set(key, t);
                }
            });
            localStorage.setItem('yru_maintenance_tickets_v3', JSON.stringify(Array.from(mergedMap.values())));
        }
    } catch(e) {}

    if (typeof renderDriverMaintenanceStatus === 'function') {
        renderDriverMaintenanceStatus();
    }
};

// Initial status render on load and polling
document.addEventListener('DOMContentLoaded', () => {
    syncDriverMaintenanceNotifications();
    if (typeof updateWorkButtonsUI === 'function') updateWorkButtonsUI();
});

// Start Polling Intervals
setInterval(() => {
    fetchDriverStatusRealtime();
    fetchSeatsLeft();
    syncDriverMaintenanceNotifications();
}, 5000);
fetchDriverStatusRealtime();
fetchSeatsLeft();
</script>



</body>
</html>