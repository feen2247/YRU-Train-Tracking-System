<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ระบบติดตามเส้นทางการเดินรถไฟฟ้า - หัวหน้ายานพาหนะ</title>
    <link rel="icon" type="image/png" href="{{ asset('favicon.png') }}?v={{ time() }}">
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('favicon.png') }}?v={{ time() }}">
    <link rel="apple-touch-icon" href="{{ asset('favicon.png') }}?v={{ time() }}">
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
                var isValidRole = r.includes('vehicle_head') || 
                                  r.includes('vehicle head') || 
                                  r.includes('vehiclehead') || 
                                  r.includes('head_of_vehicle') || 
                                  r.includes('supervisor') || 
                                  r.includes('ยานพาหนะ') || 
                                  r.includes('หัวหน้า') || 
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
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800;900&family=Sarabun:ital,wght@0,300;0,400;0,500;0,600;0,700;0,800;1,400&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        body, button, input, select, textarea, div, span, p, a, h1, h2, h3, h4, h5, h6, label, td, th {
            font-family: 'Inter', 'Sarabun', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif !important;
        }
        .tab-content { display: none; }
        .tab-content.active { display: block; }
        .active-menu { background-color: rgba(255, 255, 255, 0.2); border-left: 4px solid #fff; }
    </style>
</head>
@php
    date_default_timezone_set('Asia/Bangkok');
    $thaiMonthsList = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
    $currentThaiFormattedDate = date('j') . ' ' . $thaiMonthsList[(int)date('n')] . ' ' . (date('Y') + 543);
@endphp
<body class="bg-slate-50 flex flex-col h-screen overflow-hidden text-slate-800 font-kanit">

    <!-- Header Component -->
    @include('passenger.vehicle-head.partials.header')

    <div class="flex flex-1 overflow-hidden relative">
        <!-- Sidebar Component -->
        @include('passenger.vehicle-head.partials.sidebar')

        <!-- Main Content Area -->
        <main class="flex-1 overflow-y-auto p-4 md:p-8 space-y-6">

            <!-- Page Title Section (matching Admin/Maintenance style) -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 bg-white p-5 md:p-6 rounded-2xl shadow-xs border border-gray-100">
                <div>
                    <h2 class="text-xl md:text-2xl font-black text-slate-800 tracking-tight">หน่วยยานพาหนะ งานธุรการและสารบรรณ กองกลาง</h2>
                    <p class="text-xs md:text-sm text-slate-500 mt-1 font-medium">ตรวจสอบและพิจารณาใบขอแจ้งซ่อมรถไฟฟ้า</p>
                    <p class="text-xs md:text-sm text-pink-600 font-bold mt-1.5 flex items-center gap-1.5"><i class="fas fa-calendar-alt text-xs"></i> <span id="vh-live-date-badge">ณ วันที่ {{ $currentThaiFormattedDate }}</span></p>
                </div>
            </div>

            <!-- Summary KPI Cards -->
            <div class="grid grid-cols-2 md:grid-cols-4 gap-3 md:gap-4">
                <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs text-slate-400 font-bold block">รถพร้อมวิ่งบริการ</span>
                        <span class="text-xl md:text-2xl font-black text-emerald-600 mt-1 block" id="kpi-ready-cars">8 คัน</span>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg">
                        <i class="fas fa-bus"></i>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs text-slate-400 font-bold block">รถรอ/อยู่ระหว่างซ่อม</span>
                        <span class="text-xl md:text-2xl font-black text-rose-600 mt-1 block" id="kpi-maintenance-cars">1 คัน</span>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-rose-50 text-rose-600 flex items-center justify-center text-lg">
                        <i class="fas fa-wrench"></i>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs text-slate-400 font-bold block">คนขับประจำการวันนี้</span>
                        <span class="text-xl md:text-2xl font-black text-pink-600 mt-1 block" id="kpi-active-drivers">8 คน</span>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-pink-50 text-pink-600 flex items-center justify-center text-lg">
                        <i class="fas fa-id-card"></i>
                    </div>
                </div>
                <div class="bg-white p-4 rounded-2xl border border-slate-100 shadow-sm flex items-center justify-between">
                    <div>
                        <span class="text-xs text-slate-400 font-bold block">ใบขออนุญาตรอพิจารณา</span>
                        <span class="text-xl md:text-2xl font-black text-amber-600 mt-1 block" id="kpi-pending-tickets">0 ใบ</span>
                    </div>
                    <div class="w-10 h-10 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-lg">
                        <i class="fas fa-clipboard-list"></i>
                    </div>
                </div>
            </div>

            <!-- TAB 3: Maintenance Review -->
            <div id="tab-maintenance" class="tab-content active space-y-6">
                <div class="bg-white rounded-3xl p-5 border border-slate-100 shadow-sm space-y-4">
                    <div class="flex flex-col md:flex-row md:items-center justify-between gap-4 border-b border-slate-100 pb-3">
                        <div class="flex items-center gap-4 sm:gap-6 border-b border-slate-100 md:border-b-0 pb-2 md:pb-0">
                            <button onclick="switchVehicleHeadSubTab('active')" id="vh-subtab-active" class="font-bold text-pink-600 text-sm md:text-base flex items-center gap-2 border-b-2 border-pink-600 pb-2 transition">
                                <i class="fas fa-file-signature text-pink-500"></i> รายงานการตรวจสอบ
                            </button>
                            <button onclick="switchVehicleHeadSubTab('history')" id="vh-subtab-history" class="font-semibold text-slate-500 hover:text-slate-700 text-sm md:text-base flex items-center gap-2 border-b-2 border-transparent pb-2 transition">
                                <i class="fas fa-history text-slate-400"></i> ประวัติการตรวจสอบทั้งหมด
                            </button>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-200/80 px-2.5 py-1 rounded-full flex items-center gap-1.5 shadow-2xs">
                                <span class="relative flex h-2 w-2">
                                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                                    <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                                </span>
                                เรียลไทม์
                            </span>
                            <button onclick="fetchMaintenanceRequests()" class="px-3 py-1.5 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-xl text-xs font-bold transition flex items-center gap-1">
                                <i class="fas fa-sync-alt"></i> รีเฟรชข้อมูล
                            </button>
                        </div>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-slate-50 text-slate-500 font-bold uppercase text-[11px] border-b border-slate-100">
                                <tr>
                                    <th class="py-3 px-3">เลขที่เอกสาร</th>
                                    <th class="py-3 px-3">ขบวนรถ / ทะเบียน</th>
                                    <th class="py-3 px-3">พนักงานขับรถ</th>
                                    <th class="py-3 px-3">อาการเสีย (1-5 รายการ)</th>
                                    <th class="py-3 px-3">เลขไมล์</th>
                                    <th class="py-3 px-3">ขั้นตอน/สถานะ</th>
                                    <th class="py-3 px-3 text-right">ดำเนินการ</th>
                                </tr>
                            </thead>
                            <tbody id="maintenance-tickets-tbody" class="divide-y divide-slate-100 text-slate-700">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

        </main>
    </div>

    <!-- Include Printable Official Form Modal -->
    @include('passenger.maintenance.partials.printable-form-modal')

    <!-- Step 1: New Driver Request Modal (Launched by Driver or Vehicle Head) -->
    <div id="driverRequestModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center hidden p-4 z-50 transition-all font-kanit">
        <div class="bg-white p-6 rounded-3xl shadow-2xl w-full max-w-2xl border border-slate-100 max-h-[90vh] overflow-y-auto">
            <div class="flex justify-between items-center mb-4 border-b border-slate-100 pb-3">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-xl bg-pink-50 text-pink-600 flex items-center justify-center font-bold">1</div>
                    <div>
                        <h3 class="text-base font-bold text-slate-800">ขั้นตอนที่ 1: กรอกใบแจ้งซ่อมและนำส่งรถไฟฟ้าเพื่อซ่อมบำรุง</h3>
                        <p class="text-xs text-slate-400">หน่วยยานพาหนะ งานธุรการและสารบรรณ กองกลาง สำนักงานอธิการบดี มรย.</p>
                    </div>
                </div>
                <button onclick="closeDriverRequestModal()" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-lg"></i></button>
            </div>

            <form id="driverRequestForm" onsubmit="submitDriverRequestForm(event)" class="space-y-4 text-xs">
                <div class="grid grid-cols-3 gap-3 bg-slate-50 p-3 rounded-2xl border border-slate-200">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">วันที่</label>
                        <input type="text" id="reqDocDate" class="w-full bg-white border border-slate-200 p-2 rounded-xl text-center font-bold text-xs" value="{{ date('j') }}">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">เดือน</label>
                        <input type="text" id="reqDocMonth" class="w-full bg-white border border-slate-200 p-2 rounded-xl text-center font-bold text-xs" value="สิงหาคม">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">พ.ศ.</label>
                        <input type="text" id="reqDocYear" class="w-full bg-white border border-slate-200 p-2 rounded-xl text-center font-bold text-xs" value="{{ date('Y') + 543 }}">
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">ขบวนรถไฟฟ้า <span class="text-pink-600">*</span></label>
                        <select id="reqCarId" class="w-full bg-white border border-slate-200 p-2.5 rounded-xl font-bold text-xs text-slate-800" onchange="autoFillVehicleInfo()">
                            <option value="EV-01">EV-01 (กค 1234 ยะลา)</option>
                            <option value="EV-02">EV-02 (กค 5678 ยะลา)</option>
                            <option value="EV-03">EV-03 (กค 9012 ยะลา)</option>
                            <option value="EV-04">EV-04 (กค 3456 ยะลา)</option>
                            <option value="EV-05">EV-05 (กค 7890 ยะลา)</option>
                            <option value="EV-06">EV-06 (กค 1122 ยะลา)</option>
                            <option value="EV-07">EV-07 (กค 3344 ยะลา)</option>
                            <option value="EV-08">EV-08 (กค 5566 ยะลา)</option>
                            <option value="EV-09">EV-09 (กค 7788 ยะลา)</option>
                            <option value="EV-10">EV-10 (กค 9900 ยะลา)</option>
                        </select>
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">พนักงานขับรถผู้แจ้ง <span class="text-pink-600">*</span></label>
                        <input type="text" id="reqDriverName" class="w-full bg-white border border-slate-200 p-2.5 rounded-xl text-xs font-bold text-slate-800" placeholder="ชื่อ-สกุล พนักงานขับรถ" required>
                    </div>
                </div>

                <div class="grid grid-cols-3 gap-3">
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">ยี่ห้อ</label>
                        <input type="text" id="reqBrand" class="w-full bg-white border border-slate-200 p-2 rounded-xl text-xs" value="YRU EV">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">รุ่น</label>
                        <input type="text" id="reqModel" class="w-full bg-white border border-slate-200 p-2 rounded-xl text-xs" value="Tram Electric 2026">
                    </div>
                    <div>
                        <label class="block font-bold text-slate-700 mb-1">มาตรวัดระยะทาง (กม.)</label>
                        <input type="number" id="reqMileage" class="w-full bg-white border border-slate-200 p-2 rounded-xl font-mono text-xs font-bold text-pink-600" value="14250">
                    </div>
                </div>

                <div class="space-y-2 bg-slate-50 p-4 rounded-2xl border border-slate-200">
                    <div class="flex justify-between items-center mb-1">
                        <label class="font-bold text-slate-800">รายการแจ้งอาการเสีย/ขอตรวจซ่อมบำรุง (1-5 รายการ)</label>
                        <span class="text-[11px] text-slate-400">ตามแบบฟอร์ม มรย.</span>
                    </div>
                    <div class="space-y-2">
                        <div class="flex items-center gap-2">
                            <span class="w-5 text-center font-bold text-slate-500">1.</span>
                            <input type="text" id="reqIssue1" class="flex-1 bg-white border border-slate-200 p-2 rounded-xl text-xs" placeholder="ระบุอาการชำรุดข้อที่ 1 (เช่น ผ้าเบรกมีเสียงดังผิดปกติ)" required>
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-5 text-center font-bold text-slate-500">2.</span>
                            <input type="text" id="reqIssue2" class="flex-1 bg-white border border-slate-200 p-2 rounded-xl text-xs" placeholder="ระบุอาการชำรุดข้อที่ 2 (เช่น ระบบไฟเลี้ยวด้านซ้ายไม่ติด)">
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-5 text-center font-bold text-slate-500">3.</span>
                            <input type="text" id="reqIssue3" class="flex-1 bg-white border border-slate-200 p-2 rounded-xl text-xs" placeholder="ระบุอาการชำรุดข้อที่ 3 (เช่น แรงดันลมยางล้อหลังขวาลดลงผิดปกติ)">
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-5 text-center font-bold text-slate-500">4.</span>
                            <input type="text" id="reqIssue4" class="flex-1 bg-white border border-slate-200 p-2 rounded-xl text-xs" placeholder="ระบุอาการชำรุดข้อที่ 4 (ถ้ามี)">
                        </div>
                        <div class="flex items-center gap-2">
                            <span class="w-5 text-center font-bold text-slate-500">5.</span>
                            <input type="text" id="reqIssue5" class="flex-1 bg-white border border-slate-200 p-2 rounded-xl text-xs" placeholder="ระบุอาการชำรุดข้อที่ 5 (ถ้ามี)">
                        </div>
                    </div>
                </div>

                <div class="flex justify-end gap-2 pt-2 border-t border-slate-100">
                    <button type="button" onclick="closeDriverRequestModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">ยกเลิก</button>
                    <button type="submit" class="bg-gradient-to-r from-pink-600 to-rose-600 hover:from-pink-700 hover:to-rose-700 text-white px-5 py-2.5 rounded-xl text-xs font-extrabold shadow-md flex items-center gap-1.5 active:scale-95 transition">
                        <i class="fas fa-paper-plane"></i> บันทึกข้อมูลและส่งเรื่อง
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Step 2: Supervisor Verification Modal — ดีไซน์พรีเมียมสอดคล้องกับแบบฟอร์ม -->
    <div id="supervisorVerifyModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center hidden p-3 z-50 transition-all font-kanit">
        <div class="bg-gray-50 w-full max-w-[680px] max-h-[95vh] overflow-y-auto rounded-3xl shadow-2xl border border-gray-200" style="font-family: 'Sarabun', 'Inter', sans-serif;">
            
            <!-- HEADER BANNER (Pink Gradient) -->
            <div class="bg-gradient-to-r from-pink-500 to-pink-600 px-6 pt-5 pb-4 rounded-t-3xl text-center relative">
                <button onclick="closeSupervisorVerifyModal()" class="absolute top-3 right-4 text-white/70 hover:text-white transition p-1 z-10">
                    <i class="fas fa-times text-base"></i>
                </button>
                <div class="w-10 h-10 rounded-full bg-white/20 flex items-center justify-center mx-auto mb-2">
                    <i class="fas fa-clipboard-check text-white text-lg"></i>
                </div>
                <h2 class="text-[15px] font-extrabold text-white tracking-tight">ตรวจสอบและพิจารณาใบขอแจ้งซ่อมรถไฟฟ้า</h2>
                <!-- Document No -->
                <div class="inline-flex items-center gap-1.5 bg-white/20 backdrop-blur-sm text-white text-[11px] font-bold px-3.5 py-1 rounded-full mt-2.5">
                    <i class="fas fa-file-alt text-[10px]"></i>
                    <span>เลขที่เอกสาร: <span id="vTicketNoDisplay" class="font-mono">-</span></span>
                </div>
            </div>

            <div class="px-5 py-5 space-y-4">
                <input type="hidden" id="verifyTargetTicketId">

                <!-- ═══════ ข้อมูลรถและอาการแจ้งซ่อมเบื้องต้น ═══════ -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-xs p-3.5 space-y-2.5">
                    <div class="flex items-center justify-between text-[12px] border-b border-gray-100 pb-2">
                        <span class="font-bold text-gray-500 flex items-center gap-1"><i class="fas fa-bus text-pink-500"></i> ข้อมูลรถไฟฟ้า</span>
                        <span class="font-extrabold text-pink-600 font-mono" id="vCarInfo">EV-01 (กค 1234 ยะลา)</span>
                    </div>
                    <div class="grid grid-cols-2 gap-2 text-[12px]">
                        <div>
                            <span class="text-gray-400 block font-semibold">พนักงานขับรถผู้แจ้ง:</span>
                            <span class="font-bold text-slate-800" id="vDriverName">นายอัสมี มูเล็ง</span>
                        </div>
                        <div>
                            <span class="text-gray-400 block font-semibold">ระยะทางปัจจุบัน (เลขไมล์):</span>
                            <span class="font-bold text-pink-600 font-mono" id="vMileage">14,250 กม.</span>
                        </div>
                    </div>
                    <!-- ลายเซ็นดิจิทัลของพนักงานขับรถ -->
                    <div class="pt-2 border-t border-gray-100 flex items-center justify-between text-[12px]">
                        <span class="text-gray-400 font-semibold">ลายมือชื่อดิจิทัลพนักงานขับรถ:</span>
                        <div id="vDriverSignatureBox" class="text-right">
                            <span class="font-bold text-slate-800 border-b border-dotted border-gray-400 px-2 pb-0.5">นายอัสมี มูเล็ง</span>
                        </div>
                    </div>
                    <div class="pt-2.5 border-t border-gray-100 text-[12px] space-y-2">
                        <span class="text-slate-800 font-extrabold flex items-center gap-1 block">
                            <i class="fas fa-tasks text-pink-500 text-xs"></i>
                            รายการอาการเสียที่แจ้งซ่อม (เลือกพิจารณาอนุญาต):
                        </span>

                        <!-- Checklist Container -->
                        <div id="vIssuesList" class="space-y-2 text-slate-700 font-medium leading-relaxed">
                            <!-- JS fills checklist cards -->
                        </div>
                    </div>
                </div>

                <!-- ═══════ เอกสาร/รูปภาพประกอบที่แนบโดยพนักงานขับรถ ═══════ -->
                <div id="vAttachmentContainer" class="bg-white rounded-2xl border border-gray-200 shadow-xs p-3.5 space-y-2">
                    <div class="flex items-center justify-between">
                        <span class="text-xs font-bold text-slate-800 flex items-center gap-1.5">
                            <i class="fas fa-paperclip text-pink-500"></i> เอกสาร/รูปภาพแนบจากพนักงานขับรถ
                        </span>
                        <span id="vAttachmentStatusBadge" class="text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-700">
                            ✓ มีไฟล์แนบ
                        </span>
                    </div>
                    
                    <div id="vAttachmentPreviewArea" class="bg-slate-50 border border-slate-200 rounded-xl p-2 text-center">
                        <!-- JS fills preview thumbnail / link -->
                    </div>
                </div>

                <!-- ═══════ ส่วนตรวจสอบสภาพชำรุด (ตรงตามรูปภาพกระดาษ) ═══════ -->
                <div class="bg-white rounded-2xl border border-gray-200 shadow-xs p-4 space-y-3">
                    <div class="flex items-center justify-between">
                        <label class="text-[12px] font-extrabold text-slate-800 tracking-tight flex items-center gap-1">
                            <i class="fas fa-search text-pink-500 text-xs"></i>
                            การตรวจสอบสภาพการชำรุด เสียหาย รถไฟฟ้าราชการส่วนกลาง <span class="text-pink-600">*</span>
                        </label>
                    </div>
                    
                    <div class="relative">
                        <textarea id="vSupervisorNotes" rows="3" 
                            class="w-full bg-transparent border-0 border-b border-dotted border-gray-400 rounded-none text-xs text-slate-800 font-medium outline-none focus:border-pink-500 transition-all duration-200 resize-none leading-7"
                            style="background-image: linear-gradient(to bottom, transparent 27px, #9ca3af 27px, #9ca3af 28px, transparent 28px); background-size: 100% 28px; line-height: 28px;"
                            placeholder="พิมพ์ผลการตรวจสอบสภาพความเสียหายลงบนเส้นประ..." required></textarea>
                    </div>
                </div>

                <!-- ═══════ ลงลายมือชื่อดิจิทัลหัวหน้ายานพาหนะ (Digital Signature Pad - Standardized UI/UX) ═══════ -->
                <div class="bg-gradient-to-br from-slate-50 to-pink-50/30 rounded-2xl border border-pink-200/80 p-3.5 space-y-2.5 font-kanit shadow-2xs">
                    <div class="flex items-center justify-between gap-2 flex-wrap sm:flex-nowrap">
                        <div class="flex items-center gap-1.5 text-xs font-bold text-slate-800">
                            <span class="text-pink-500">✍️</span> ลงลายมือชื่อดิจิทัลหัวหน้าหน่วยงาน <span class="text-rose-500">*</span>
                        </div>
                        <div class="flex items-center bg-pink-100/80 text-pink-700 px-2.5 py-1 rounded-lg text-[10px] font-bold gap-1 shadow-2xs">
                            ✍️ วาดลายเซ็นสด
                        </div>
                    </div>

                    <!-- Mode 1: Signature Canvas -->
                    <div id="vSupSignaturePadBox" class="space-y-2 pt-0.5">
                        <div class="relative bg-white border-2 border-dashed border-pink-300 hover:border-pink-400 rounded-xl overflow-hidden touch-none group shadow-2xs transition">
                            <canvas id="vSupSignatureCanvas" class="w-full h-[140px] cursor-crosshair bg-white block" style="touch-action: none !important; user-select: none; -webkit-user-select: none;"></canvas>
                            <div id="vSupCanvasPlaceholder" class="absolute inset-0 flex items-center justify-center pointer-events-none text-slate-400 text-xs font-medium gap-1.5">
                                <i class="fas fa-pen-nib text-pink-400"></i> ใช้นิ้วหรือเมาส์วาดลายเซ็นของคุณในช่องนี้
                            </div>
                            <button type="button" onclick="clearVSupSignatureCanvas()" class="absolute top-2 right-2 bg-slate-100 hover:bg-rose-500 hover:text-white text-slate-600 text-[10px] font-bold px-2.5 py-1 rounded-lg transition flex items-center gap-1 cursor-pointer border border-slate-200 shadow-2xs">
                                <i class="fas fa-eraser"></i> ล้างลายเซ็น
                            </button>
                        </div>
                        <div class="flex items-center justify-between text-xs px-1">
                            <span id="vSupCanvasSigStatus" class="text-[11px] text-slate-500 font-medium flex items-center gap-1">
                                <i class="fas fa-info-circle text-slate-400"></i> ยังไม่ได้วาดลายเซ็น
                            </span>
                            <span class="text-[10px] text-pink-700 bg-pink-100/80 px-2 py-0.5 rounded-full font-bold">
                                หัวหน้าหน่วยงานยานพาหนะ
                            </span>
                        </div>
                    </div>

                    <!-- Mode 2: Auto System Signature -->
                    <div id="vSupSignatureAutoBox" class="hidden bg-white border border-pink-200 rounded-xl p-3 text-center shadow-2xs">
                        <div class="flex items-baseline justify-center gap-1.5 text-[12px] text-slate-700 pt-0.5">
                            <span class="font-medium">ลงชื่อ</span>
                            <span class="font-bold text-slate-800 border-b border-dotted border-pink-300 px-4 pb-0.5">นายฮาดี ลือแมะ</span>
                            <span class="font-medium">หัวหน้าหน่วยงานยานพาหนะ</span>
                        </div>
                        <p class="text-[10px] text-slate-400 mt-1">(ลงชื่ออัตโนมัติจากระบบอิเล็กทรอนิกส์พร้อมประทับตราเวลา)</p>
                    </div>

                    <input type="hidden" id="vSupervisorName" value="นายฮาดี ลือแมะ">
                </div>

                <!-- ═══════ ปุ่มกดการจัดการ ═══════ -->
                <div class="flex items-center justify-center gap-3 pt-2">
                    <button onclick="submitSupervisorVerification()" class="bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 active:scale-95 text-white px-6 py-3 rounded-2xl text-xs font-extrabold shadow-lg shadow-emerald-500/30 flex items-center gap-2 transition cursor-pointer">
                        <i class="fas fa-check-circle"></i> ยืนยัน
                    </button>
                    <button type="button" onclick="closeSupervisorVerifyModal()" class="bg-gray-200 hover:bg-gray-300 text-gray-600 px-5 py-3 rounded-2xl text-xs font-bold transition cursor-pointer">
                        ยกเลิก
                    </button>
                </div>

            </div>
        </div>
    </div>

    <!-- Reassignment Modal -->
    <div id="reassignModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center hidden p-4 z-50 transition-all font-kanit">
        <div class="bg-white p-5 md:p-6 rounded-3xl shadow-2xl w-full max-w-md border border-slate-100">
            <div class="flex justify-between items-center mb-4 border-b border-slate-100 pb-3">
                <h3 class="text-base font-bold text-slate-800 flex items-center gap-2">
                    <i class="fas fa-user-edit text-pink-600"></i> มอบหมายคนขับประจำรถ
                </h3>
                <button onclick="closeReassignModal()" class="text-slate-400 hover:text-slate-600"><i class="fas fa-times text-lg"></i></button>
            </div>
            
            <input type="hidden" id="modal-reassign-car-code">

            <div class="space-y-4 text-xs">
                <div class="bg-slate-50 p-3 rounded-2xl border border-slate-200/80">
                    <span class="text-slate-500 font-bold block mb-0.5">ขบวนรถที่เลือก:</span>
                    <span id="modal-reassign-car-display" class="font-extrabold text-pink-600 text-sm">EV-01</span>
                </div>

                <div>
                    <label class="block font-bold text-slate-700 mb-1">เลือกพนักงานขับรถ <span class="text-pink-600">*</span></label>
                    <select id="modal-reassign-driver-select" class="w-full bg-white border border-slate-200 p-2.5 rounded-xl text-xs font-bold text-slate-800 outline-none focus:ring-2 focus:ring-pink-400">
                        <option value="นายอัสมี มูเล็ง|69003|asmee@yru.ac.th">นายอัสมี มูเล็ง (69003)</option>
                        <option value="นายอัรฟาน มะเระ|69004|arfan@yru.ac.th">นายอัรฟาน มะเระ (69004)</option>
                        <option value="นายชูฟียาน มะโละ|69005|sufiyan@yru.ac.th">นายชูฟียาน มะโละ (69005)</option>
                        <option value="นายอุสมาน สาและ|69006|usman@yru.ac.th">นายอุสมาน สาและ (69006)</option>
                        <option value="นายบัดรี สาและ|69007|badri@yru.ac.th">นายบัดรี สาและ (69007)</option>
                        <option value="นายตอริก ลือแมะ|69008|torik@yru.ac.th">นายตอริก ลือแมะ (69008)</option>
                        <option value="นายสมหวัง ใจดี|69009|somwang@yru.ac.th">นายสมหวัง ใจดี (69009)</option>
                        <option value="นายสมใจ ใจดี|69010|somjai@yru.ac.th">นายสมใจ ใจดี (69010)</option>
                        <option value="นายกิตติ ตั้งใจ|69011|kitti@yru.ac.th">นายกิตติ ตั้งใจ (69011)</option>
                        <option value="นายรุสลัน สอเฮาะ|69012|ruslan@yru.ac.th">นายรุสลัน สอเฮาะ (69012)</option>
                    </select>
                </div>
            </div>

            <div class="flex justify-end gap-2 mt-6 pt-3 border-t border-slate-100">
                <button onclick="closeReassignModal()" class="px-4 py-2 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100">ยกเลิก</button>
                <button onclick="confirmDriverAssignment()" class="bg-pink-600 hover:bg-pink-700 text-white px-5 py-2 rounded-xl text-xs font-bold shadow-md">บันทึกการมอบหมาย</button>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script>
        const defaultTrams = [
            { id: "EV-01", name: "คันที่ 1", plate: "กค 1234 ยะลา", driver: "นายอัสมี มูเล็ง", driver_id: "69003", battery: 90, status: "พร้อมใช้งาน", coords: "6.550100, 101.291500" },
            { id: "EV-02", name: "คันที่ 2", plate: "กค 5678 ยะลา", driver: "นายอัรฟาน มะเระ", driver_id: "69004", battery: 85, status: "พร้อมใช้งาน", coords: "6.551200, 101.292800" },
            { id: "EV-03", name: "คันที่ 3", plate: "กค 9012 ยะลา", driver: "นายซูเฟียน มะโละ", driver_id: "69005", battery: 15, status: "ระงับการใช้งาน", coords: "6.548900, 101.291700" },
            { id: "EV-04", name: "คันที่ 4", plate: "กค 3456 ยะลา", driver: "นายอุสมาน สาและ", driver_id: "69006", battery: 92, status: "พร้อมใช้งาน", coords: "6.553500, 101.293400" },
            { id: "EV-05", name: "คันที่ 5", plate: "กค 7890 ยะลา", driver: "นายบัดรี สาและ", driver_id: "69007", battery: 78, status: "พร้อมใช้งาน", coords: "6.554200, 101.294100" },
            { id: "EV-06", name: "คันที่ 6", plate: "กค 1122 ยะลา", driver: "นายตอริก ลือแมะ", driver_id: "69008", battery: 88, status: "พร้อมใช้งาน", coords: "6.552800, 101.290500" },
            { id: "EV-07", name: "คันที่ 7", plate: "กค 3344 ยะลา", driver: "นายสมหวัง ใจดี", driver_id: "69009", battery: 95, status: "พร้อมใช้งาน", coords: "6.549500, 101.290200" },
            { id: "EV-08", name: "คันที่ 8", plate: "กค 5566 ยะลา", driver: "นายสมใจ ใจดี", driver_id: "69010", battery: 82, status: "พร้อมใช้งาน", coords: "6.551800, 101.295200" },
            { id: "EV-09", name: "คันที่ 9", plate: "กค 7788 ยะลา", driver: "นายกิตติ ตั้งใจ", driver_id: "69011", battery: 94, status: "พร้อมใช้งาน", coords: "6.552200, 101.293100" },
            { id: "EV-10", name: "คันที่ 10", plate: "กค 9900 ยะลา", driver: "นายรุสลัน สอเฮาะ", driver_id: "69012", battery: 89, status: "พร้อมใช้งาน", coords: "6.553900, 101.294800" }
        ];

        let fleetMap = null;
        let maintenanceTicketsList = [];

        function getStorage(key, fallback) {
            try {
                const val = localStorage.getItem(key);
                return val ? JSON.parse(val) : fallback;
            } catch(e) {
                return fallback;
            }
        }

        function setStorage(key, val) {
            localStorage.setItem(key, JSON.stringify(val));
        }

        function switchTab(tabId) {
            document.querySelectorAll('.tab-content').forEach(el => el.classList.remove('active'));
            const target = document.getElementById('tab-' + tabId);
            if (target) target.classList.add('active');

            const tabButtons = ['dashboard', 'dispatch', 'maintenance', 'inspections', 'live-map'];
            tabButtons.forEach(b => {
                const btn = document.getElementById('btn-tab-' + b);
                if (btn) {
                    if (b === tabId) {
                        btn.className = 'w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold text-pink-600 bg-pink-50 transition text-left';
                    } else {
                        btn.className = 'w-full flex items-center gap-3 px-3.5 py-2.5 rounded-xl text-xs font-bold text-slate-600 hover:bg-slate-50 transition text-left';
                    }
                }
            });

            if (tabId === 'live-map') {
                setTimeout(initFleetMap, 200);
            }
        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            if (sidebar) sidebar.classList.toggle('-translate-x-full');
        }

        window.toggleProfileDropdown = function(e) {
            if (e) { try { e.stopPropagation(); } catch(err) {} }
            const menu = document.getElementById('profileDropdownMenu');
            const icon = document.getElementById('profileDropdownIcon');
            if (!menu) return;
            if (menu.classList.contains('hidden')) {
                menu.classList.remove('hidden');
                setTimeout(() => {
                    menu.classList.remove('opacity-0', 'scale-95');
                    menu.classList.add('opacity-100', 'scale-100');
                }, 10);
                if (icon) icon.classList.add('rotate-180');
            } else {
                menu.classList.remove('opacity-100', 'scale-100');
                menu.classList.add('opacity-0', 'scale-95');
                setTimeout(() => menu.classList.add('hidden'), 200);
                if (icon) icon.classList.remove('rotate-180');
            }
        };

        document.addEventListener('click', function(e) {
            const container = document.getElementById('profileDropdownContainer');
            const menu = document.getElementById('profileDropdownMenu');
            const icon = document.getElementById('profileDropdownIcon');
            if (menu && !menu.classList.contains('hidden') && container && !container.contains(e.target)) {
                menu.classList.remove('opacity-100', 'scale-100');
                menu.classList.add('opacity-0', 'scale-95');
                setTimeout(() => menu.classList.add('hidden'), 200);
                if (icon) icon.classList.remove('rotate-180');
            }
        });

        async function fetchMaintenanceRequests() {
            let apiTickets = [];
            try {
                const res = await fetch('/api/maintenance/requests');
                const json = await res.json();
                if (json.status === 'success' && Array.isArray(json.data)) {
                    apiTickets = json.data;
                }
            } catch (e) {
                console.error("Error fetching maintenance requests:", e);
            }

            let localTickets = getStorage('yru_maintenance_tickets_v3', []);
            const idsToDelete = ['MNT-0001', 'MNT-0002', 'MNT-0003', 'MNT-2569-D1BB'];
            const filteredLocal = localTickets.filter(tk => {
                const key = String(tk.ticket_no || tk.id);
                return !idsToDelete.includes(key);
            });
            if (filteredLocal.length !== localTickets.length) {
                localTickets = filteredLocal;
                setStorage('yru_maintenance_tickets_v3', localTickets);
            }

            const mergedMap = new Map();

            localTickets.forEach(tk => {
                if (tk && (tk.ticket_no || tk.id)) mergedMap.set(String(tk.ticket_no || tk.id), tk);
            });
            apiTickets.forEach(tk => {
                if (tk && (tk.ticket_no || tk.id)) {
                    const key = String(tk.ticket_no || tk.id);
                    const existing = mergedMap.get(key);
                    if (existing) {
                        const localSig = existing.signature_image || (existing.driver_signature && String(existing.driver_signature).startsWith('data:image/') ? existing.driver_signature : null);
                        const localDoc = existing.attachment_url || existing.signed_document_url;
                        
                        const merged = { ...existing, ...tk };
                        if (localSig) {
                            merged.driver_signature = localSig;
                            merged.signature_image = localSig;
                        }
                        if (localDoc) {
                            merged.attachment_url = localDoc;
                            merged.signed_document_url = localDoc;
                        }
                        mergedMap.set(key, merged);
                    } else {
                        mergedMap.set(key, tk);
                    }
                }
            });

            maintenanceTicketsList = Array.from(mergedMap.values());
            renderHeadDashboard();
        }

        let currentVehicleHeadSubTab = 'active';

        window.switchVehicleHeadSubTab = function(tab) {
            currentVehicleHeadSubTab = tab;
            const btnActive = document.getElementById('vh-subtab-active');
            const btnHistory = document.getElementById('vh-subtab-history');

            if (tab === 'active') {
                if (btnActive) btnActive.className = "font-bold text-pink-600 text-sm md:text-base flex items-center gap-2 border-b-2 border-pink-600 pb-2 transition cursor-pointer";
                if (btnHistory) btnHistory.className = "font-semibold text-slate-500 hover:text-slate-700 text-sm md:text-base flex items-center gap-2 border-b-2 border-transparent pb-2 transition cursor-pointer";
            } else {
                if (btnActive) btnActive.className = "font-semibold text-slate-500 hover:text-slate-700 text-sm md:text-base flex items-center gap-2 border-b-2 border-transparent pb-2 transition cursor-pointer";
                if (btnHistory) btnHistory.className = "font-bold text-pink-600 text-sm md:text-base flex items-center gap-2 border-b-2 border-pink-600 pb-2 transition cursor-pointer";
            }
            renderHeadDashboard();
        };

        function renderHeadDashboard() {
            const trams = getStorage('yru_trams_v18', defaultTrams);

            // Sort tickets descending by date so latest is at the top
            maintenanceTicketsList.sort((a, b) => {
                const dateA = a.created_at ? new Date(a.created_at).getTime() : 0;
                const dateB = b.created_at ? new Date(b.created_at).getTime() : 0;
                return dateB - dateA;
            });

            // Calculate active maintenance cars
            const activeMaintCars = new Set();
            maintenanceTicketsList.forEach(tk => {
                if (tk.status && !['completed', 'approved', 'rejected'].includes(tk.status)) {
                    if (tk.car_id) activeMaintCars.add(tk.car_id);
                }
            });

            let readyCount = 0;
            let maintCount = 0;
            let activeDrivers = 0;

            trams.forEach(t => {
                const isReady = !activeMaintCars.has(t.id) && (t.status === 'พร้อมใช้งาน' || t.status === 'ปกติกำลังขับ');
                if (isReady) {
                    readyCount++;
                    if (t.driver && t.driver.trim() !== '' && t.driver !== '-' && !t.driver.includes('ไม่มีคนขับ') && !t.driver.includes('ไม่ระบุ')) {
                        activeDrivers++;
                    }
                } else {
                    maintCount++;
                }
            });

            const pendingStep2Count = maintenanceTicketsList.filter(t => t.status === 'pending_supervisor' || t.status === 'รอการอนุมัติ' || t.status === 'รอหัวหน้ายานพาหนะตรวจ').length;

            document.getElementById('kpi-ready-cars').innerText = `${readyCount} คัน`;
            document.getElementById('kpi-maintenance-cars').innerText = `${maintCount} คัน`;
            document.getElementById('kpi-active-drivers').innerText = `${activeDrivers} คน`;
            document.getElementById('kpi-pending-tickets').innerText = `${pendingStep2Count} ใบ`;

            // Render Table
            const tbody = document.getElementById('fleet-table-body');
            if (tbody) {
                tbody.innerHTML = '';
                trams.forEach(t => {
                    const isReady = !activeMaintCars.has(t.id) && (t.status === 'พร้อมใช้งาน' || t.status === 'ปกติกำลังขับ');
                    const statusBadge = isReady 
                        ? '<span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-emerald-100 text-emerald-700"><i class="fas fa-check-circle mr-1"></i> พร้อมใช้งาน</span>'
                        : '<span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-700"><i class="fas fa-tools mr-1"></i> ระงับ/ขัดข้อง</span>';

                    tbody.innerHTML += `
                        <tr class="hover:bg-slate-50 transition border-b border-slate-100">
                            <td class="py-3 px-3 font-extrabold text-pink-600">${t.id}</td>
                            <td class="py-3 px-3 font-semibold text-slate-700">${t.plate || '-'}</td>
                            <td class="py-3 px-3 font-bold text-slate-800">${t.driver || '-'}</td>
                            <td class="py-3 px-3">
                                <div class="flex items-center gap-2">
                                    <div class="w-16 bg-slate-100 rounded-full h-2 overflow-hidden">
                                        <div class="bg-emerald-500 h-2 rounded-full" style="width: ${t.battery || 85}%"></div>
                                    </div>
                                    <span class="font-bold text-[11px] text-slate-600">${t.battery || 85}%</span>
                                </div>
                            </td>
                            <td class="py-3 px-3">${statusBadge}</td>
                            <td class="py-3 px-3 text-right">
                                <button onclick="openReassignModal('${t.id}')" class="px-3 py-1 bg-pink-50 hover:bg-pink-100 text-pink-600 rounded-lg text-xs font-bold transition">
                                    <i class="fas fa-user-edit mr-1"></i> มอบหมาย
                                </button>
                            </td>
                        </tr>
                    `;
                });
            }

            // Render Dispatch Cards
            const cardsGrid = document.getElementById('dispatch-cards-grid');
            if (cardsGrid) {
                cardsGrid.innerHTML = '';
                trams.forEach(t => {
                    cardsGrid.innerHTML += `
                        <div class="bg-slate-50 border border-slate-200/80 rounded-2xl p-4 space-y-3 hover:border-pink-300 transition shadow-xs">
                            <div class="flex justify-between items-center">
                                <span class="text-sm font-black text-pink-600">${t.id}</span>
                                <span class="text-[11px] text-slate-500 font-semibold">${t.plate || '-'}</span>
                            </div>
                            <div class="space-y-1">
                                <span class="text-[10px] text-slate-400 uppercase font-bold">คนขับประจำรถ:</span>
                                <p class="text-xs font-extrabold text-slate-800 flex items-center gap-1.5">
                                    <i class="fas fa-id-badge text-pink-500"></i> ${t.driver || 'ยังไม่ระบุ'}
                                </p>
                            </div>
                            <button onclick="openReassignModal('${t.id}')" class="w-full bg-white border border-pink-200 hover:bg-pink-50 text-pink-600 font-bold text-xs py-2 rounded-xl transition shadow-xs">
                                เปลี่ยนคนขับประจำรถ
                            </button>
                        </div>
                    `;
                });
            }

            // Render 5-Step Maintenance Tickets
            const ticketsTbody = document.getElementById('maintenance-tickets-tbody');
            ticketsTbody.innerHTML = '';

            const filteredTickets = maintenanceTicketsList.filter(tk => {
                const isPendingReview = (tk.status === 'pending_supervisor' || tk.status === 'รอการอนุมัติ' || tk.status === 'รอหัวหน้ายานพาหนะตรวจ');
                if (currentVehicleHeadSubTab === 'active') {
                    return isPendingReview;
                } else {
                    return !isPendingReview;
                }
            });

            if (filteredTickets.length === 0) {
                const emptyMsg = currentVehicleHeadSubTab === 'active'
                    ? '❌ ไม่มีรายการขออนุญาตซ่อมบำรุงที่รอการตรวจสอบขณะนี้'
                    : '❌ ยังไม่มีประวัติการตรวจสอบซ่อมบำรุงที่ดำเนินการไปแล้ว';
                ticketsTbody.innerHTML = `<tr><td colspan="7" class="py-8 text-center text-slate-400 font-semibold">${emptyMsg}</td></tr>`;
            } else {
                filteredTickets.forEach(tk => {
                    const issuesArr = parseIssues(tk.issues || tk.issue || tk.symptoms || tk.details || tk.description);
                    const issuesText = issuesArr.join(' | ') || '-';
                    
                    let stepBadge = '';
                    let actionBtn = '';

                    if (tk.status === 'pending_supervisor') {
                        stepBadge = '<span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-amber-100 text-amber-800 animate-pulse"><i class="fas fa-clock mr-1"></i> ขั้นที่ 2: รอหัวหน้ายานพาหนะตรวจ</span>';
                        actionBtn = `
                            <button onclick="openSupervisorVerifyModal('${tk.ticket_no || tk.id}')" class="px-3 py-1 bg-pink-600 hover:bg-pink-700 text-white rounded-lg text-xs font-bold transition shadow-xs flex items-center gap-1 ml-auto">
                                <i class="fas fa-clipboard-check"></i> ตรวจสอบสภาพ
                            </button>
                        `;
                    } else if (tk.status === 'pending_quotation') {
                        stepBadge = '<span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-blue-100 text-blue-800"><i class="fas fa-wrench mr-1"></i> ขั้นที่ 3: รอช่างประเมินราคา</span>';
                        actionBtn = `
                            <button onclick="viewTicketOfficial('${tk.ticket_no || tk.id}')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition flex items-center gap-1 ml-auto">
                                <i class="fas fa-print"></i> พิมพ์/ดูแบบฟอร์ม
                            </button>
                        `;
                    } else if (tk.status === 'pending_director') {
                        stepBadge = '<span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-purple-100 text-purple-800"><i class="fas fa-user-tie mr-1"></i> ขั้นที่ 4: รอ ผอ. อนุมัติงบ</span>';
                        actionBtn = `
                            <button onclick="viewTicketOfficial('${tk.ticket_no || tk.id}')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition flex items-center gap-1 ml-auto">
                                <i class="fas fa-print"></i> พิมพ์/ดูแบบฟอร์ม
                            </button>
                        `;
                    } else if (tk.status === 'in_progress') {
                        stepBadge = '<span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-indigo-100 text-indigo-800"><i class="fas fa-cog fa-spin mr-1"></i> ขั้นที่ 5: กำลังดำเนินการซ่อม</span>';
                        actionBtn = `
                            <button onclick="viewTicketOfficial('${tk.ticket_no || tk.id}')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition flex items-center gap-1 ml-auto">
                                <i class="fas fa-print"></i> พิมพ์/ดูแบบฟอร์ม
                            </button>
                        `;
                    } else if (tk.status === 'completed') {
                        stepBadge = '<span class="px-2.5 py-1 rounded-full text-[10px] font-extrabold bg-emerald-100 text-emerald-800 border border-emerald-300 shadow-2xs"><i class="fas fa-receipt text-emerald-600 mr-1"></i> ซ่อมเสร็จแล้ว (มีใบเสร็จการซ่อม)</span>';
                        actionBtn = `
                            <button onclick="openViewRepairReceiptModal('${tk.ticket_no || tk.id}')" class="px-2.5 py-1 bg-emerald-600 hover:bg-emerald-700 text-white rounded-lg text-xs font-bold transition flex items-center gap-1 ml-auto shadow-2xs cursor-pointer">
                                <i class="fas fa-file-invoice-dollar"></i> ดูใบเสร็จการซ่อม
                            </button>
                            <button onclick="viewTicketOfficial('${tk.ticket_no || tk.id}')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition flex items-center gap-1 ml-auto cursor-pointer">
                                <i class="fas fa-print"></i> แบบฟอร์ม
                            </button>
                        `;
                    } else if (tk.status === 'rejected') {
                        stepBadge = '<span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-rose-100 text-rose-800"><i class="fas fa-times-circle mr-1"></i> ไม่อนุญาต / ตีกลับ</span>';
                        actionBtn = `
                            <button onclick="viewTicketOfficial('${tk.ticket_no || tk.id}')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition flex items-center gap-1 ml-auto cursor-pointer">
                                <i class="fas fa-print"></i> พิมพ์/ดูแบบฟอร์ม
                            </button>
                        `;
                    } else {
                        stepBadge = `<span class="px-2.5 py-1 rounded-full text-[10px] font-bold bg-slate-100 text-slate-700">${tk.status}</span>`;
                        actionBtn = `
                            <button onclick="viewTicketOfficial('${tk.ticket_no || tk.id}')" class="px-2.5 py-1 bg-slate-100 hover:bg-slate-200 text-slate-700 rounded-lg text-xs font-bold transition flex items-center gap-1 ml-auto cursor-pointer">
                                <i class="fas fa-print"></i> พิมพ์/ดูแบบฟอร์ม
                            </button>
                        `;
                    }

                    ticketsTbody.innerHTML += `
                        <tr class="hover:bg-slate-50 transition border-b border-slate-100">
                            <td class="py-3 px-3 font-mono font-extrabold text-slate-900">${tk.ticket_no || tk.id}</td>
                            <td class="py-3 px-3 font-bold text-pink-600">${tk.car_id}</td>
                            <td class="py-3 px-3 font-semibold text-slate-700">${tk.driver_name || tk.reporter || '-'}</td>
                            <td class="py-3 px-3 text-slate-800 font-medium max-w-[200px] truncate" title="${issuesText}">${issuesText}</td>
                            <td class="py-3 px-3 font-mono font-bold text-slate-600">${Number(tk.mileage || 0).toLocaleString()} กม.</td>
                            <td class="py-3 px-3">${stepBadge}</td>
                            <td class="py-3 px-3 text-right">${actionBtn}</td>
                        </tr>
                    `;
                });
            }

            // Render Checklist
            const checkContainer = document.getElementById('safety-checklist-container');
            if (checkContainer) {
                checkContainer.innerHTML = '';
                const checkItems = [
                    { title: "ระบบเบรกและผ้าเบรก", desc: "ตรวจสอบระยะเบรกและระดับน้ำมันเบรกทุกขบวน", status: "ผ่านเกณฑ์ 100%" },
                    { title: "แรงดันลมยางและดอกยาง", desc: "วัดแรงดันลมยางรถบริการทุกคันให้อยู่ในเกณฑ์ 36-38 PSI", status: "ผ่านเกณฑ์ 100%" },
                    { title: "ระบบไฟส่องสว่างและไฟเลี้ยว", desc: "ทดสอบสัญญาณไฟหน้า ไฟท้าย ไฟเลี้ยว และไฟฉุกเฉิน", status: "ผ่านเกณฑ์ 100%" },
                    { title: "กล้องวงจรปิดและ GPS Tracker", desc: "ตรวจสอบสัญญาณเชื่อมต่อ Real-time Telemetry ทุกคัน", status: "ออนไลน์ครบ 10 คัน" }
                ];
                checkItems.forEach(item => {
                    checkContainer.innerHTML += `
                        <div class="bg-slate-50 border border-slate-200/80 p-4 rounded-2xl space-y-2">
                            <div class="flex items-center justify-between">
                                <h4 class="text-xs font-bold text-slate-800">${item.title}</h4>
                                <span class="text-[10px] font-bold bg-emerald-100 text-emerald-700 px-2 py-0.5 rounded-full">${item.status}</span>
                            </div>
                            <p class="text-[11px] text-slate-500">${item.desc}</p>
                        </div>
                    `;
                });
            }
        }

        function openDriverRequestModalFromHead() {
            autoFillVehicleInfo();
            document.getElementById('driverRequestModal').classList.remove('hidden');
        }

        function closeDriverRequestModal() {
            document.getElementById('driverRequestModal').classList.add('hidden');
        }

        function autoFillVehicleInfo() {
            const carId = document.getElementById('reqCarId').value;
            const trams = getStorage('yru_trams_v18', defaultTrams);
            const tram = trams.find(t => t.id === carId);
            if (tram) {
                document.getElementById('reqDriverName').value = tram.driver || 'นายอัสมี มูเล็ง';
            }
        }

        async function submitDriverRequestForm(e) {
            e.preventDefault();
            const carId = document.getElementById('reqCarId').value;
            const driverName = document.getElementById('reqDriverName').value;
            const docDate = document.getElementById('reqDocDate').value;
            const docMonth = document.getElementById('reqDocMonth').value;
            const docYear = document.getElementById('reqDocYear').value;
            const brand = document.getElementById('reqBrand').value;
            const model = document.getElementById('reqModel').value;
            const mileage = document.getElementById('reqMileage').value;

            const issues = [
                document.getElementById('reqIssue1').value,
                document.getElementById('reqIssue2').value,
                document.getElementById('reqIssue3').value,
                document.getElementById('reqIssue4').value,
                document.getElementById('reqIssue5').value
            ].filter(s => s.trim().length > 0);

            try {
                const res = await fetch('/api/maintenance/requests', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({
                        car_id: carId,
                        driver_name: driverName,
                        doc_date: docDate,
                        doc_month: docMonth,
                        doc_year: docYear,
                        brand: brand,
                        model: model,
                        mileage: mileage,
                        issues: issues
                    })
                });

                const json = await res.json();
                if (json.status === 'success') {
                    closeDriverRequestModal();
                    await fetchMaintenanceRequests();
                    Swal.fire({
                        icon: 'success',
                        title: 'ออกใบขออนุญาตซ่อมสำเร็จ!',
                        text: `สร้างใบขออนุญาตซ่อม ${json.ticket_no} เรียบร้อยแล้ว (สถานะ: รอหัวหน้ายานพาหนะตรวจสอบ)`,
                        confirmButtonColor: '#EC4899',
                        customClass: { popup: 'rounded-2xl font-kanit' }
                    });
                } else {
                    alert(json.message || "เกิดข้อผิดพลาด");
                }
            } catch (err) {
                console.error(err);
                alert("เชื่อมต่อเซิร์ฟเวอร์ขัดข้อง");
            }
        }

        function parseIssues(rawIssues, defaultFallback = 'ตรวจเช็คสภาพทั่วไป') {
            let arr = [];
            if (Array.isArray(rawIssues)) {
                arr = rawIssues;
            } else if (typeof rawIssues === 'string' && rawIssues.trim() !== '') {
                try {
                    const parsed = JSON.parse(rawIssues);
                    if (Array.isArray(parsed)) arr = parsed;
                    else if (typeof parsed === 'string') arr = [parsed];
                } catch (e) {
                    if (rawIssues.includes('|')) arr = rawIssues.split('|').map(s => s.trim());
                    else if (rawIssues.includes('\n')) arr = rawIssues.split('\n').map(s => s.trim());
                    else if (rawIssues.includes(',')) arr = rawIssues.split(',').map(s => s.trim());
                    else arr = [rawIssues];
                }
            } else if (rawIssues) {
                arr = [String(rawIssues)];
            }

            arr = arr.map(s => String(s).trim()).filter(s => s.length > 0);
            if (arr.length === 0 && defaultFallback) {
                arr = [defaultFallback];
            }
            return arr;
        }

        function generateSignatureSvgDataUrl(name, strokeColor = '#1e3a8a') {
            const text = (name && name.trim()) ? name.trim() : 'พนักงานขับรถ';
            let hash = 0;
            for (let i = 0; i < text.length; i++) {
                hash = (hash << 5) - hash + text.charCodeAt(i);
                hash |= 0;
            }
            const seed = Math.abs(hash);
            const slant = -4 - (seed % 4);
            const flourishType = seed % 3;
            let flourishPath = '';
            if (flourishType === 0) {
                flourishPath = `M 15 48 C 55 58, 115 54, 160 44 C 190 38, 220 40, 225 35 C 210 50, 150 56, 75 56 C 38 56, 18 51, 10 47`;
            } else if (flourishType === 1) {
                flourishPath = `M 18 46 Q 110 58 215 42 C 230 40, 225 50, 200 52 C 145 56, 75 55, 25 48`;
            } else {
                flourishPath = `M 20 50 C 60 56, 120 52, 170 44 C 198 39, 222 36, 226 42 C 220 54, 165 58, 85 56 C 42 55, 22 50, 15 48`;
            }
            const topLoop = `M ${35 + (seed % 15)} 26 C ${30 + (seed % 10)} 12, ${55 + (seed % 15)} 10, ${60 + (seed % 15)} 20 C ${65 + (seed % 15)} 32, ${40 + (seed % 10)} 36, ${25 + (seed % 10)} 40`;

            const svg = `<svg xmlns="http://www.w3.org/2000/svg" width="220" height="62" viewBox="0 0 220 62">
                <defs>
                    <style>
                        @import url('https://fonts.googleapis.com/css2?family=Charm:wght@700&amp;family=Caveat:wght@700&amp;display=swap');
                        .sig-txt-${seed} {
                            font-family: 'Charm', 'Caveat', 'Brush Script MT', cursive;
                            font-size: 26px;
                            font-weight: 700;
                            fill: ${strokeColor};
                            letter-spacing: 0.5px;
                        }
                        .sig-stroke-${seed} {
                            stroke: ${strokeColor};
                            stroke-width: 2.2;
                            fill: none;
                            stroke-linecap: round;
                            stroke-linejoin: round;
                        }
                    </style>
                </defs>
                <g transform="rotate(${slant} 110 31)">
                    <path d="${topLoop}" class="sig-stroke-${seed}" opacity="0.6" stroke-width="1.8"/>
                    <text x="110" y="33" dominant-baseline="middle" text-anchor="middle" class="sig-txt-${seed}">${text}</text>
                    <path d="${flourishPath}" class="sig-stroke-${seed}" opacity="0.95"/>
                </g>
                <circle cx="210" cy="12" r="3" fill="#10b981" opacity="0.75"/>
            </svg>`;
            try {
                return 'data:image/svg+xml;base64,' + btoa(unescape(encodeURIComponent(svg)));
            } catch(e) {
                return 'data:image/svg+xml;utf8,' + encodeURIComponent(svg);
            }
        }

        function openSupervisorVerifyModal(ticketId) {
            const tk = maintenanceTicketsList.find(t => (t.ticket_no === ticketId || String(t.id) === String(ticketId)));
            if (!tk) return;

            document.getElementById('verifyTargetTicketId').value = tk.ticket_no || tk.id;
            document.getElementById('vTicketNoDisplay').innerText = tk.ticket_no || tk.id;
            let vCarDisplay = tk.car_id || '';
            if (tk.license_plate && tk.license_plate !== '-' && tk.license_plate !== vCarDisplay) {
                let lp = tk.license_plate.trim();
                if (lp.startsWith(vCarDisplay)) {
                    vCarDisplay = lp;
                } else {
                    vCarDisplay = `${vCarDisplay} (${lp})`;
                }
            }
            document.getElementById('vCarInfo').innerText = vCarDisplay;
            document.getElementById('vDriverName').innerText = tk.driver_name || tk.reporter || '-';
            const rawMileage = tk.mileage ?? tk.current_mileage ?? tk.odometer ?? 0;
            const numMileage = parseInt(String(rawMileage).replace(/\D/g, '')) || 0;
            document.getElementById('vMileage').innerText = `${numMileage.toLocaleString()} กม.`;

            const issuesArr = parseIssues(tk.issues || tk.issue || tk.symptoms || tk.details || tk.description || tk.repair_items);
            const issuesContainer = document.getElementById('vIssuesList');
            if (issuesContainer) {
                issuesContainer.innerHTML = '';
                if (issuesArr.length === 0) {
                    issuesContainer.innerHTML = '<div class="text-slate-400 italic text-xs py-2 text-center">ไม่มีรายการแจ้งซ่อม</div>';
                } else {
                    issuesArr.forEach((iss, i) => {
                        let isChecked = true;
                        if (Array.isArray(tk.approved_items) && tk.approved_items.length >= 0) {
                            isChecked = tk.approved_items.includes(iss);
                        } else if (Array.isArray(tk.rejected_items) && tk.rejected_items.includes(iss)) {
                            isChecked = false;
                        }

                        const savedReason = (tk.item_reject_reasons && tk.item_reject_reasons[iss]) ? tk.item_reject_reasons[iss] : '';
                        issuesContainer.innerHTML += `
                            <div class="rounded-xl border border-slate-200 bg-white shadow-2xs transition-all hover:border-pink-300 overflow-hidden">
                                <div class="flex items-center justify-between p-2.5">
                                    <label class="flex items-center gap-2.5 cursor-pointer flex-1 min-w-0 mr-2">
                                        <input type="checkbox" id="vItemCheck_${i}" class="v-item-check w-4 h-4 text-pink-600 rounded border-slate-300 focus:ring-pink-500 cursor-pointer accent-pink-600 shrink-0" ${isChecked ? 'checked' : ''} onchange="updateItemChecklistSummary()">
                                        <span class="text-xs font-semibold text-slate-800 break-words leading-snug">
                                            <span class="font-bold text-pink-600">${i+1}.</span> ${iss}
                                        </span>
                                    </label>
                                    <div class="shrink-0 flex items-center gap-1">
                                        <button type="button" id="vItemBtn_${i}" onclick="toggleSingleChecklistItem(${i})" class="v-item-btn px-2.5 py-1 rounded-lg text-[10px] font-extrabold transition cursor-pointer flex items-center gap-1">
                                        </button>
                                    </div>
                                </div>
                                <!-- ช่องระบุรายละเอียดด้านล่างของรายการที่ไม่อนุญาต เพื่อตีกลับไปยังพนักงานขับรถ -->
                                <div id="vItemRejectReasonBox_${i}" class="${isChecked ? 'hidden' : ''} px-3 py-2 bg-rose-50/80 border-t border-rose-100 space-y-1.5 transition-all">
                                    <div class="flex items-center justify-between">
                                        <span class="text-[11px] font-bold text-rose-700 flex items-center gap-1">
                                            <i class="fas fa-undo-alt text-rose-500 text-[10px]"></i>
                                            ระบุเหตุผล / รายละเอียดการตีกลับ:
                                        </span>
                                    </div>
                                    <textarea id="vItemRejectReason_${i}" rows="2" 
                                        class="w-full bg-white border border-rose-200 rounded-lg p-2 text-xs text-slate-800 placeholder-slate-400 focus:outline-none focus:ring-2 focus:ring-rose-400 font-medium resize-none shadow-2xs leading-relaxed"
                                        placeholder="พิมพ์เหตุผลหรือคำแนะนำสำหรับรายการนี้...">${savedReason}</textarea>
                                </div>
                            </div>
                        `;
                    });
                }
                updateItemChecklistSummary();
            }

            // Render Driver Digital Signature (Canvas Image or Auto-Generated SVG Signature)
            const driverSigBox = document.getElementById('vDriverSignatureBox');
            const sigImg = (tk.signature_image && String(tk.signature_image).startsWith('data:image/')) 
                ? tk.signature_image 
                : ((tk.driver_signature && String(tk.driver_signature).startsWith('data:image/')) ? tk.driver_signature : (tk.signature_image || tk.driver_signature));
            const driverName = tk.driver_name || tk.reporter || 'พนักงานขับรถ';
            const fallbackSvgUrl = generateSignatureSvgDataUrl(driverName);

            if (driverSigBox) {
                const isValidBase64Img = sigImg && typeof sigImg === 'string' && sigImg.startsWith('data:image/') && sigImg.length > 200 && !sigImg.includes('[TRUNCATED');

                if (isValidBase64Img) {
                    driverSigBox.innerHTML = `
                        <div class="inline-flex flex-col items-center">
                            <img src="${sigImg}" class="h-10 max-w-[170px] object-contain border-b border-gray-300 pb-0.5" 
                                 onerror="this.onerror=null; this.src='${fallbackSvgUrl}';" 
                                 alt="ลายมือชื่อดิจิทัล">
                            <span class="text-[9px] text-emerald-600 font-bold mt-0.5"><i class="fas fa-signature text-[8px]"></i> ลายมือชื่อดิจิทัลสด</span>
                        </div>
                    `;
                } else {
                    const sigText = (sigImg && typeof sigImg === 'string' && !sigImg.startsWith('data:')) ? sigImg : driverName;
                    const svgUrl = generateSignatureSvgDataUrl(sigText);
                    driverSigBox.innerHTML = `
                        <div class="inline-flex flex-col items-center">
                            <img src="${svgUrl}" class="h-10 max-w-[170px] object-contain border-b border-gray-300 pb-0.5" alt="ลายมือชื่อดิจิทัล">
                            <span class="text-[9px] text-pink-600 font-bold mt-0.5"><i class="fas fa-check-circle text-[8px]"></i> ลงลายมือชื่ออิเล็กทรอนิกส์</span>
                        </div>
                    `;
                }
            }

            // Render Driver Attachment (Photo/Document)
            const attachContainer = document.getElementById('vAttachmentContainer');
            const attachPreviewArea = document.getElementById('vAttachmentPreviewArea');
            const attachStatusBadge = document.getElementById('vAttachmentStatusBadge');

            const attachUrl = tk.attachment_url || tk.signed_document_url || tk.attachment;
            const attachName = tk.attachment_name || tk.signed_document_name || 'เอกสารแนบประกอบ.png';

            if (attachUrl && typeof attachUrl === 'string' && attachUrl.trim() !== '') {
                if (attachContainer) attachContainer.classList.remove('hidden');
                if (attachStatusBadge) {
                    attachStatusBadge.className = 'text-[10px] font-bold px-2.5 py-0.5 rounded-full bg-emerald-100 text-emerald-700 border border-emerald-300';
                    attachStatusBadge.innerHTML = '<i class="fas fa-check-circle mr-1"></i> มีไฟล์แนบ';
                }

                if (attachUrl.startsWith('data:image/') || attachUrl.match(/\.(jpeg|jpg|gif|png|webp|heic)/i)) {
                    attachPreviewArea.innerHTML = `
                        <div class="space-y-1.5 p-1">
                            <div class="relative group inline-block">
                                <img src="${attachUrl}" class="max-h-56 max-w-full rounded-xl mx-auto border border-slate-200 shadow-md cursor-pointer hover:opacity-95 transition" onclick="window.open('${attachUrl}', '_blank')" alt="รูปภาพแนบจากคนขับ">
                            </div>
                            <p class="text-[10px] text-slate-500 font-medium flex items-center justify-center gap-1">
                                <i class="fas fa-search-plus text-pink-500"></i> คลิกที่รูปภาพเพื่อดูขนาดใหญ่ (${attachName})
                            </p>
                        </div>
                    `;
                } else if (attachUrl.startsWith('data:application/pdf')) {
                    attachPreviewArea.innerHTML = `
                        <div class="flex items-center justify-between bg-white p-3 rounded-xl border border-slate-200 shadow-xs">
                            <div class="flex items-center gap-2 overflow-hidden text-left">
                                <i class="fas fa-file-pdf text-rose-500 text-2xl shrink-0"></i>
                                <div class="truncate">
                                    <p class="text-xs font-bold text-slate-800 truncate">${attachName}</p>
                                    <p class="text-[10px] text-slate-400 font-mono">เอกสาร PDF แนบจากพนักงานขับรถ</p>
                                </div>
                            </div>
                            <a href="${attachUrl}" download="${attachName || 'document.pdf'}" target="_blank" class="px-3 py-1.5 bg-pink-600 hover:bg-pink-700 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5 shrink-0 shadow-xs">
                                <i class="fas fa-download"></i> ดาวน์โหลด / เปิดอ่าน PDF
                            </a>
                        </div>
                    `;
                } else {
                    attachPreviewArea.innerHTML = `
                        <div class="flex items-center justify-between bg-white p-3 rounded-xl border border-slate-200 shadow-xs">
                            <div class="flex items-center gap-2 overflow-hidden text-left">
                                <i class="fas fa-paperclip text-slate-500 text-xl shrink-0"></i>
                                <span class="text-xs font-bold text-slate-800 truncate">${attachName}</span>
                            </div>
                            <a href="${attachUrl}" target="_blank" class="px-3 py-1.5 bg-pink-600 hover:bg-pink-700 text-white text-xs font-bold rounded-xl transition flex items-center gap-1.5 shrink-0 shadow-xs">
                                <i class="fas fa-external-link-alt"></i> เปิดดูไฟล์แนบ
                            </a>
                        </div>
                    `;
                }
            } else {
                if (attachContainer) attachContainer.classList.remove('hidden');
                if (attachStatusBadge) {
                    attachStatusBadge.className = 'text-[10px] font-bold px-2 py-0.5 rounded-full bg-slate-200 text-slate-600';
                    attachStatusBadge.innerText = 'ไม่มีไฟล์แนบ';
                }
                if (attachPreviewArea) {
                    attachPreviewArea.innerHTML = `<p class="text-xs text-slate-400 font-medium py-1.5">ไม่มีรูปภาพหรือไฟล์แนบเพิ่มเติมจากพนักงานขับรถ</p>`;
                }
            }

            // Fill supervisor notes dynamically
            const supervisorNotesEl = document.getElementById('vSupervisorNotes');
            if (supervisorNotesEl) {
                if (tk.supervisor_notes && tk.supervisor_notes.trim()) {
                    supervisorNotesEl.value = tk.supervisor_notes;
                } else {
                    supervisorNotesEl.value = '';
                }
            }

            // Set Supervisor Name inside signature bracket
            const supNameVal = document.getElementById('vSupervisorName') ? document.getElementById('vSupervisorName').value : 'นายฮาดี ลือแมะ';
            const sigBracket = document.getElementById('vSupervisorNameBracket');
            if (sigBracket) {
                sigBracket.innerText = supNameVal;
            }

            document.getElementById('supervisorVerifyModal').classList.remove('hidden');
            setTimeout(() => {
                initVSupSignatureCanvas();
                clearVSupSignatureCanvas();
                switchVSupSignatureMode('pad');
            }, 50);
        }

        let vSupSignatureCanvas = null;
        let vSupSignatureCtx = null;
        let isVSupDrawing = false;
        let hasVSupDrawnSignature = false;
        let vSupSignatureMode = 'pad';

        function initVSupSignatureCanvas() {
            vSupSignatureCanvas = document.getElementById('vSupSignatureCanvas');
            if (!vSupSignatureCanvas) return;

            const dpr = window.devicePixelRatio || 1;
            const rect = vSupSignatureCanvas.getBoundingClientRect();
            const w = rect.width > 0 ? rect.width : 480;
            const h = rect.height > 0 ? rect.height : 140;

            if (vSupSignatureCanvas.width !== Math.floor(w * dpr) || vSupSignatureCanvas.height !== Math.floor(h * dpr)) {
                vSupSignatureCanvas.width = Math.floor(w * dpr);
                vSupSignatureCanvas.height = Math.floor(h * dpr);
            }

            vSupSignatureCtx = vSupSignatureCanvas.getContext('2d');
            vSupSignatureCtx.scale(dpr, dpr);
            vSupSignatureCtx.lineWidth = 2.8;
            vSupSignatureCtx.lineCap = 'round';
            vSupSignatureCtx.lineJoin = 'round';
            vSupSignatureCtx.strokeStyle = '#0f172a';

            if (vSupSignatureCanvas._hasSigListeners) return;
            vSupSignatureCanvas._hasSigListeners = true;

            function getPos(e) {
                const r = vSupSignatureCanvas.getBoundingClientRect();
                const clientX = (e.touches && e.touches.length > 0) ? e.touches[0].clientX : e.clientX;
                const clientY = (e.touches && e.touches.length > 0) ? e.touches[0].clientY : e.clientY;
                return {
                    x: clientX - r.left,
                    y: clientY - r.top
                };
            }

            function startDrawing(e) {
                e.preventDefault();
                isVSupDrawing = true;
                hasVSupDrawnSignature = true;
                const placeholder = document.getElementById('vSupCanvasPlaceholder');
                if (placeholder) placeholder.style.display = 'none';

                const pos = getPos(e);
                vSupSignatureCtx.beginPath();
                vSupSignatureCtx.moveTo(pos.x, pos.y);
            }

            function draw(e) {
                if (!isVSupDrawing) return;
                e.preventDefault();
                const pos = getPos(e);

                vSupSignatureCtx.lineTo(pos.x, pos.y);
                vSupSignatureCtx.stroke();

                const statusEl = document.getElementById('vSupCanvasSigStatus');
                if (statusEl) {
                    statusEl.innerHTML = '<i class="fas fa-check-circle text-emerald-600"></i> <span class="text-emerald-700 font-bold">วาดลายเซ็นเรียบร้อยแล้ว</span>';
                }
            }

            function stopDrawing() {
                if (isVSupDrawing) {
                    isVSupDrawing = false;
                    vSupSignatureCtx.closePath();
                }
            }

            vSupSignatureCanvas.addEventListener('mousedown', startDrawing);
            vSupSignatureCanvas.addEventListener('mousemove', draw);
            vSupSignatureCanvas.addEventListener('mouseup', stopDrawing);
            vSupSignatureCanvas.addEventListener('mouseleave', stopDrawing);

            vSupSignatureCanvas.addEventListener('touchstart', startDrawing, { passive: false });
            vSupSignatureCanvas.addEventListener('touchmove', draw, { passive: false });
            vSupSignatureCanvas.addEventListener('touchend', stopDrawing);
        }

        function clearVSupSignatureCanvas() {
            if (!vSupSignatureCanvas || !vSupSignatureCtx) return;
            vSupSignatureCtx.clearRect(0, 0, vSupSignatureCanvas.width, vSupSignatureCanvas.height);
            hasVSupDrawnSignature = false;
            const placeholder = document.getElementById('vSupCanvasPlaceholder');
            if (placeholder) placeholder.style.display = 'flex';
            const statusEl = document.getElementById('vSupCanvasSigStatus');
            if (statusEl) {
                statusEl.innerHTML = '<i class="fas fa-info-circle text-slate-400"></i> ยังไม่ได้วาดลายเซ็น';
            }
        }

        function switchVSupSignatureMode(mode) {
            vSupSignatureMode = mode;
            const btnPad = document.getElementById('vSupBtnSigPad');
            const btnAuto = document.getElementById('vSupBtnSigAuto');
            const padBox = document.getElementById('vSupSignaturePadBox');
            const autoBox = document.getElementById('vSupSignatureAutoBox');

            if (mode === 'pad') {
                if (btnPad) btnPad.className = "px-2.5 py-1 rounded-lg transition-all bg-white text-pink-600 shadow-xs flex items-center gap-1 cursor-pointer font-bold";
                if (btnAuto) btnAuto.className = "px-2.5 py-1 rounded-lg transition-all text-gray-500 hover:text-slate-700 flex items-center gap-1 cursor-pointer font-bold";
                if (padBox) padBox.classList.remove('hidden');
                if (autoBox) autoBox.classList.add('hidden');
            } else {
                if (btnPad) btnPad.className = "px-2.5 py-1 rounded-lg transition-all text-gray-500 hover:text-slate-700 flex items-center gap-1 cursor-pointer font-bold";
                if (btnAuto) btnAuto.className = "px-2.5 py-1 rounded-lg transition-all bg-white text-pink-600 shadow-xs flex items-center gap-1 cursor-pointer font-bold";
                if (padBox) padBox.classList.add('hidden');
                if (autoBox) autoBox.classList.remove('hidden');
            }
        }

        function closeSupervisorVerifyModal() {
            document.getElementById('supervisorVerifyModal').classList.add('hidden');
        }

        window.toggleSingleChecklistItem = function(index) {
            const chk = document.getElementById(`vItemCheck_${index}`);
            if (chk) {
                chk.checked = !chk.checked;
                updateItemChecklistSummary();
            }
        };

        window.toggleAllChecklistItems = function(status) {
            const checkboxes = document.querySelectorAll('.v-item-check');
            checkboxes.forEach(chk => {
                chk.checked = status;
            });
            updateItemChecklistSummary();
        };

        window.updateItemChecklistSummary = function() {
            const checkboxes = document.querySelectorAll('.v-item-check');
            checkboxes.forEach((chk, i) => {
                const btn = document.getElementById(`vItemBtn_${i}`);
                const rejBox = document.getElementById(`vItemRejectReasonBox_${i}`);
                if (chk.checked) {
                    if (btn) {
                        btn.className = "v-item-btn px-2.5 py-1 rounded-lg text-[10px] font-extrabold transition cursor-pointer bg-emerald-100 text-emerald-800 border border-emerald-300 hover:bg-emerald-200 flex items-center gap-1";
                        btn.innerHTML = '<i class="fas fa-check-circle text-[10px]"></i> <span>อนุญาต</span>';
                    }
                    if (rejBox) {
                        rejBox.classList.add('hidden');
                    }
                } else {
                    if (btn) {
                        btn.className = "v-item-btn px-2.5 py-1 rounded-lg text-[10px] font-extrabold transition cursor-pointer bg-rose-100 text-rose-800 border border-rose-300 hover:bg-rose-200 flex items-center gap-1";
                        btn.innerHTML = '<i class="fas fa-times-circle text-[10px]"></i> <span>ไม่อนุญาต</span>';
                    }
                    if (rejBox) {
                        rejBox.classList.remove('hidden');
                    }
                }
            });
        };

        async function submitSupervisorVerification() {
            const ticketId = document.getElementById('verifyTargetTicketId').value;
            const notes = document.getElementById('vSupervisorNotes').value.trim();
            const supervisorName = document.getElementById('vSupervisorName').value.trim() || 'นายฮาดี ลือแมะ';

            if (!notes) {
                Swal.fire({
                    icon: 'warning',
                    title: 'กรุณากรอกข้อมูลให้ครบถ้วน',
                    text: 'กรุณาระบุผลการตรวจสอบสภาพความเสียหาย',
                    confirmButtonColor: '#ec4899',
                    fontFamily: 'Kanit'
                });
                return;
            }

            let supSigImg = null;
            if (vSupSignatureMode === 'pad') {
                if (!hasVSupDrawnSignature) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'กรุณากรอกข้อมูลให้ครบถ้วน',
                        text: 'กรุณาวาดลายเซ็นดิจิทัลเพื่อยืนยันก่อนบันทึก',
                        confirmButtonColor: '#ec4899',
                        fontFamily: 'Kanit'
                    });
                    return;
                }
                try {
                    supSigImg = vSupSignatureCanvas.toDataURL('image/png');
                } catch(e) {}
            } else {
                try {
                    const svgText = (supervisorName && supervisorName.trim()) ? supervisorName.trim() : 'หัวหน้ายานพาหนะ';
                    supSigImg = generateSignatureSvgDataUrl(svgText, '#059669');
                } catch(e) {}
            }

            // Gather checklist approval selection
            const activeTk = maintenanceTicketsList.find(t => (String(t.ticket_no) === String(ticketId) || String(t.id) === String(ticketId)));
            const issuesArr = parseIssues(activeTk?.issues || activeTk?.issue || activeTk?.symptoms || activeTk?.details || activeTk?.description || activeTk?.repair_items);

            const approvedItems = [];
            const rejectedItems = [];
            const itemRejectReasons = {};

            for (let i = 0; i < issuesArr.length; i++) {
                const iss = issuesArr[i];
                const chk = document.getElementById(`vItemCheck_${i}`);
                if (chk && !chk.checked) {
                    const reasonEl = document.getElementById(`vItemRejectReason_${i}`);
                    const reasonVal = reasonEl ? reasonEl.value.trim() : '';
                    if (!reasonVal) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'กรุณากรอกข้อมูลให้ครบถ้วน',
                            text: `กรุณาระบุเหตุผลที่ไม่อนุญาตในรายการ "${iss}" (*)`,
                            confirmButtonColor: '#ec4899',
                            customClass: { popup: 'rounded-2xl font-kanit' }
                        });
                        if (reasonEl) reasonEl.focus();
                        return;
                    }
                    itemRejectReasons[iss] = reasonVal;
                    rejectedItems.push(iss);
                } else {
                    approvedItems.push(iss);
                }
            }

            // Condition:
            // - If approvedItems.length > 0 -> pending_quotation (เด้งไปหน้าช่างซ่อมให้ประเมินราคา)
            // - If approvedItems.length === 0 -> rejected (ไปหน้าพนักงานขับรถ / ไม่อนุมัติ)
            const targetStatus = (approvedItems.length > 0) ? 'pending_quotation' : 'rejected';
            const statusSummaryText = (approvedItems.length > 0)
                ? (rejectedItems.length > 0
                    ? `อนุญาต ${approvedItems.length}/${issuesArr.length} รายการ (ส่งต่อไปยังหน้าช่าง) และ ไม่อนุญาต ${rejectedItems.length} รายการ (ส่งกลับไปยังหน้าคนขับรถ)`
                    : `อนุญาตทั้งหมด ${approvedItems.length} รายการ (ส่งต่อไปยังหน้าช่างเพื่อประเมินราคา)`)
                : `ไม่อนุญาตการแจ้งซ่อมทั้งหมด (ส่งข้อมูลตีกลับไปยังพนักงานขับรถ)`;

            // Update local tickets in localStorage
            let localTickets = getStorage('yru_maintenance_tickets_v3', []);
            const targetIndex = localTickets.findIndex(t => (String(t.ticket_no) === String(ticketId) || String(t.id) === String(ticketId)));
            if (targetIndex !== -1) {
                localTickets[targetIndex].status = targetStatus;
                localTickets[targetIndex].supervisor_notes = notes;
                localTickets[targetIndex].supervisor_name = supervisorName;
                localTickets[targetIndex].supervisor_signature = supSigImg || supervisorName;
                localTickets[targetIndex].supervisor_signature_img = supSigImg;
                localTickets[targetIndex].supervisor_signature_image = supSigImg;
                localTickets[targetIndex].supervisor_signed_at = new Date().toISOString();
                localTickets[targetIndex].approved_items = approvedItems;
                localTickets[targetIndex].rejected_items = rejectedItems;
                localTickets[targetIndex].item_reject_reasons = itemRejectReasons;
                if (approvedItems.length > 0) {
                    localTickets[targetIndex].issues = approvedItems; // Mechanic sees strictly approved items
                    localTickets[targetIndex].approved_issues = approvedItems;
                } else {
                    localTickets[targetIndex].issues = rejectedItems;
                }

                // If partial approval, create/update rejected record for driver
                if (rejectedItems.length > 0 && approvedItems.length > 0) {
                    const rejTicketId = `${ticketId}-REJ`;
                    const existingRejIdx = localTickets.findIndex(t => t.ticket_no === rejTicketId || t.id === rejTicketId);
                    
                    let compiledRejectNotes = notes;
                    const reasonEntries = Object.entries(itemRejectReasons).filter(([_, r]) => r && r.trim());
                    if (reasonEntries.length > 0) {
                        const detailsStr = reasonEntries.map(([it, r]) => `• ${it}: ${r}`).join('\n');
                        compiledRejectNotes = (notes ? notes + '\n\n' : '') + 'รายละเอียดเหตุผลการตีกลับ:\n' + detailsStr;
                    }

                    const rejTicketObj = {
                        ...localTickets[targetIndex],
                        id: rejTicketId,
                        ticket_no: rejTicketId,
                        parent_ticket_no: ticketId,
                        issues: rejectedItems,
                        rejected_items: rejectedItems,
                        item_reject_reasons: itemRejectReasons,
                        approved_items: [],
                        status: 'rejected',
                        description: `รายการที่ไม่ได้รับอนุญาตจากคำขอ ${ticketId}`,
                        supervisor_notes: compiledRejectNotes,
                        supervisor_name: supervisorName,
                        supervisor_signature: supSigImg || supervisorName,
                        supervisor_signature_img: supSigImg,
                        supervisor_signature_image: supSigImg,
                        driver_signature_img: localTickets[targetIndex].signature_image || localTickets[targetIndex].driver_signature_img,
                        signature_image: localTickets[targetIndex].signature_image || localTickets[targetIndex].driver_signature_img,
                        driver_signature: localTickets[targetIndex].driver_signature,
                        supervisor_signed_at: new Date().toISOString()
                    };
                    if (existingRejIdx !== -1) {
                        localTickets[existingRejIdx] = rejTicketObj;
                    } else {
                        localTickets.unshift(rejTicketObj);
                    }
                }
                setStorage('yru_maintenance_tickets_v3', localTickets);
            }

            if (activeTk) {
                activeTk.status = targetStatus;
                activeTk.supervisor_notes = notes;
                activeTk.supervisor_name = supervisorName;
                activeTk.supervisor_signature = supSigImg || supervisorName;
                activeTk.supervisor_signature_img = supSigImg;
                activeTk.supervisor_signed_at = new Date().toISOString();
                activeTk.approved_items = approvedItems;
                activeTk.rejected_items = rejectedItems;
                if (approvedItems.length > 0) {
                    activeTk.issues = approvedItems;
                    activeTk.approved_issues = approvedItems;
                } else {
                    activeTk.issues = rejectedItems;
                }
            }

            try {
                await fetch(`/api/maintenance/requests/${ticketId}/supervisor-verify`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'Accept': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                    },
                    body: JSON.stringify({
                        status: targetStatus,
                        supervisor_notes: notes,
                        supervisor_name: supervisorName,
                        supervisor_id: '69014',
                        supervisor_signature: supSigImg || supervisorName,
                        supervisor_signature_img: supSigImg,
                        approved_items: approvedItems,
                        rejected_items: rejectedItems
                    })
                });
            } catch (err) {
                console.error(err);
            }

            closeSupervisorVerifyModal();

            Swal.fire({
                icon: (targetStatus === 'pending_quotation') ? 'success' : 'info',
                title: (targetStatus === 'pending_quotation') ? 'บันทึกการพิจารณาเรียบร้อย' : 'ไม่อนุมัติการแจ้งซ่อม',
                html: `
                    <div class="text-xs text-slate-600 space-y-1.5 font-kanit">
                        <p class="font-bold text-slate-800">${statusSummaryText}</p>
                        <p class="text-[11px] text-pink-600 font-bold">${(targetStatus === 'pending_quotation') 
                            ? (rejectedItems.length > 0 ? '➔ รายการที่อนุญาตส่งไปยังหน้าช่างซ่อม และรายการที่ไม่อนุญาตส่งกลับไปยังคนขับรถ' : '➔ ข้อมูลส่งต่อไปยังหน้าช่างซ่อมเพื่อประเมินราคา') 
                            : '➔ รายการตีกลับไปยังหน้าพนักงานขับรถ'}</p>
                    </div>
                `,
                confirmButtonColor: '#ec4899',
                customClass: { popup: 'font-kanit rounded-2xl' }
            });

            if (typeof renderMaintenanceTable === 'function') renderMaintenanceTable();
            if (typeof updateStatsCounters === 'function') updateStatsCounters();
            if (typeof fetchMaintenanceRequests === 'function') fetchMaintenanceRequests();

            try {
                const syncBc = new BroadcastChannel('yru_trams_realtime_sync');
                syncBc.postMessage({
                    type: 'MAINTENANCE_SUPERVISOR_VERIFIED',
                    ticket_no: ticketId,
                    car_id: activeTk?.car_id || activeTk?.tram_id,
                    driver_name: activeTk?.driver_name || activeTk?.reporter,
                    approved_items: approvedItems,
                    rejected_items: rejectedItems,
                    supervisor_notes: notes,
                    status: targetStatus
                });
                syncBc.postMessage({ type: 'MAINTENANCE_UPDATED' });
            } catch(e) {}
        }

        function viewTicketOfficial(ticketId) {
            let tk = maintenanceTicketsList.find(t => (t.ticket_no === ticketId || String(t.id) === String(ticketId)));
            if (!tk) {
                const localTickets = getStorage('yru_maintenance_tickets_v3', []);
                tk = localTickets.find(t => (t.ticket_no === ticketId || String(t.id) === String(ticketId)));
            }
            if (tk) {
                // If it's a -REJ ticket or child ticket, inherit signatures from parent if missing
                if (tk.parent_ticket_no || String(tk.ticket_no || tk.id).endsWith('-REJ')) {
                    const parentId = tk.parent_ticket_no || String(tk.ticket_no || tk.id).replace('-REJ', '');
                    const parentTk = maintenanceTicketsList.find(t => (t.ticket_no === parentId || String(t.id) === String(parentId))) 
                                  || getStorage('yru_maintenance_tickets_v3', []).find(t => (t.ticket_no === parentId || String(t.id) === String(parentId)));
                    if (parentTk) {
                        if (!tk.signature_image) tk.signature_image = parentTk.signature_image || parentTk.driver_signature_img;
                        if (!tk.driver_signature_img) tk.driver_signature_img = parentTk.driver_signature_img || parentTk.signature_image;
                        if (!tk.supervisor_signature_img) tk.supervisor_signature_img = parentTk.supervisor_signature_img || parentTk.supervisor_signature_image;
                        if (!tk.supervisor_signature_image) tk.supervisor_signature_image = parentTk.supervisor_signature_image || parentTk.supervisor_signature_img;
                        if (!tk.supervisor_name) tk.supervisor_name = parentTk.supervisor_name;
                        if (!tk.supervisor_notes && parentTk.supervisor_notes) tk.supervisor_notes = parentTk.supervisor_notes;
                    }
                }
                openPrintableFormModal(tk);
            }
        }

        function openReassignModal(carId) {
            document.getElementById('modal-reassign-car-code').value = carId;
            document.getElementById('modal-reassign-car-display').innerText = carId;
            document.getElementById('reassignModal').classList.remove('hidden');
        }

        function closeReassignModal() {
            document.getElementById('reassignModal').classList.add('hidden');
        }

        function confirmDriverAssignment() {
            const carId = document.getElementById('modal-reassign-car-code').value;
            const selectVal = document.getElementById('modal-reassign-driver-select').value;
            const [driverName, driverId, driverEmail] = selectVal.split('|');

            const trams = getStorage('yru_trams_v18', defaultTrams);
            const tram = trams.find(t => t.id === carId);
            if (tram) {
                tram.driver = driverName;
                tram.driver_id = driverId;
                setStorage('yru_trams_v18', trams);
            }

            // Sync to backend API
            fetch('/api/electric-trains/assign-driver', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({
                    car_code: carId,
                    driver_id: driverId,
                    driver_name: driverName
                })
            }).catch(e => console.error(e));

            closeReassignModal();
            renderHeadDashboard();
            Swal.fire({
                icon: 'success',
                title: 'มอบหมายสำเร็จ!',
                text: `มอบหมาย [${driverName}] ประจำรถ ${carId} เรียบร้อยแล้ว`,
                confirmButtonColor: '#EC4899',
                customClass: { popup: 'rounded-2xl font-kanit' }
            });
        }

        function initFleetMap() {
            if (fleetMap) {
                fleetMap.invalidateSize();
                return;
            }
            fleetMap = L.map('head-fleet-map').setView([6.551500, 101.292500], 16);
            L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
                maxZoom: 20,
                subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
                attribution: '&copy; Google Maps'
            }).addTo(fleetMap);

            const trams = getStorage('yru_trams_v18', defaultTrams);
            trams.forEach(t => {
                if (t.coords) {
                    const [lat, lng] = t.coords.split(',').map(n => parseFloat(n.trim()));
                    if (!isNaN(lat) && !isNaN(lng)) {
                        const marker = L.circleMarker([lat, lng], {
                            radius: 8,
                            fillColor: '#EC4899',
                            color: '#ffffff',
                            weight: 2,
                            opacity: 1,
                            fillOpacity: 0.9
                        }).addTo(fleetMap);
                        marker.bindPopup(`<b>${t.id} (${t.plate || ''})</b><br>คนขับ: ${t.driver || '-'}<br>สถานะ: ${t.status}`);
                    }
                }
            });
        }

        function updateLiveThaiDate() {
            const now = new Date();
            const thaiMonths = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
            const day = now.getDate();
            const month = thaiMonths[now.getMonth() + 1];
            const year = now.getFullYear() + 543;
            const fullDateText = `ณ วันที่ ${day} ${month} ${year}`;
            
            const badge = document.getElementById('vh-live-date-badge');
            if (badge) {
                badge.innerText = fullDateText;
            }

            const docDateInp = document.getElementById('reqDocDate');
            const docMonthInp = document.getElementById('reqDocMonth');
            const docYearInp = document.getElementById('reqDocYear');
            if (docDateInp && (!docDateInp._userModified)) docDateInp.value = day;
            if (docMonthInp && (!docMonthInp._userModified)) docMonthInp.value = month;
            if (docYearInp && (!docYearInp._userModified)) docYearInp.value = year;
        }

        // Live Midnight Watcher: Immediately flips date at 00:00:00
        function initMidnightWatcher() {
            window._lastRecordedDay = new Date().getDate();
            updateLiveThaiDate();
            
            setInterval(() => {
                const now = new Date();
                if (now.getDate() !== window._lastRecordedDay) {
                    window._lastRecordedDay = now.getDate();
                    updateLiveThaiDate();
                    if (typeof fetchMaintenanceRequests === 'function') {
                        fetchMaintenanceRequests();
                    }
                }
            }, 1000);
        }

        function openViewRepairReceiptModal(ticketId) {
            let tk = maintenanceTicketsList.find(t => (t.ticket_no === ticketId || String(t.id) === String(ticketId)));
            if (!tk) {
                const localTickets = getStorage('yru_maintenance_tickets_v3', []);
                tk = localTickets.find(t => (t.ticket_no === ticketId || String(t.id) === String(ticketId)));
            }
            if (!tk) return;

            document.getElementById('vrrTicketNo').innerText = tk.ticket_no || tk.id || '-';
            document.getElementById('vrrCarInfo').innerText = tk.car_id ? `${tk.car_id} (${tk.license_plate || '-'})` : '-';
            document.getElementById('vrrCompletedAt').innerText = tk.repair_completed_at ? new Date(tk.repair_completed_at).toLocaleDateString('th-TH') : (tk.archive_date || new Date().toLocaleDateString('th-TH'));
            document.getElementById('vrrReceiptNo').innerText = tk.repair_receipt_no || tk.archive_no || `RCP-2569-${(tk.ticket_no || tk.id || '001').replace('MNT-', '')}`;
            document.getElementById('vrrReceiptCost').innerText = `${Number(tk.repair_receipt_cost || tk.total_cost || tk.approved_total_cost || 0).toLocaleString('th-TH', {minimumFractionDigits: 2})} บาท`;
            document.getElementById('vrrCompletionNotes').innerText = tk.completion_notes || 'ดำเนินการเปลี่ยนอะไหล่และซ่อมบำรุงเรียบร้อยแล้ว ผ่านการทดสอบระบบ 100%';
            document.getElementById('vrrTechnicianName').innerText = tk.technician_name || tk.receiver_name || 'นายประสาน งานดี (ช่าง มรย.)';
            document.getElementById('vrrDirectorName').innerText = tk.director_name || 'ผศ.ดร. ศิริชัย นามบุรี';
            document.getElementById('vrrApprovedBudget').innerText = `${Number(tk.approved_total_cost || tk.total_cost || 0).toLocaleString('th-TH', {minimumFractionDigits: 2})} บาท`;

            const imgWrapper = document.getElementById('vrrReceiptImgWrapper');
            const receiptImg = tk.repair_receipt_img || tk.receipt_img;
            if (imgWrapper) {
                if (receiptImg && receiptImg.startsWith('data:image/')) {
                    imgWrapper.classList.remove('hidden');
                    document.getElementById('vrrReceiptImg').src = receiptImg;
                } else {
                    imgWrapper.classList.add('hidden');
                }
            }

            document.getElementById('viewRepairReceiptModal').classList.remove('hidden');
        }

        function closeViewRepairReceiptModal() {
            document.getElementById('viewRepairReceiptModal').classList.add('hidden');
        }

        document.addEventListener('DOMContentLoaded', () => {
            initMidnightWatcher();
            fetchMaintenanceRequests();
            setInterval(() => {
                fetchMaintenanceRequests();
            }, 3000);

            try {
                const syncBc = new BroadcastChannel('yru_trams_realtime_sync');
                syncBc.onmessage = (event) => {
                    if (event.data) {
                        if (event.data.type === 'MAINTENANCE_REPAIR_COMPLETED' || event.data.type === 'NEW_MAINTENANCE_TICKET' || event.data.type === 'MAINTENANCE_UPDATED') {
                            fetchMaintenanceRequests();
                            if ((event.data.type === 'MAINTENANCE_REPAIR_COMPLETED' || event.data.status === 'completed') && event.data.ticket_no && typeof Swal !== 'undefined') {
                                Swal.fire({
                                    icon: 'success',
                                    iconColor: '#10B981',
                                    title: '<div class="text-slate-800 font-extrabold text-xl font-kanit">ช่างส่งใบเสร็จ & ผลการซ่อมบำรุงแล้ว!</div>',
                                    html: `
                                        <div class="text-xs text-slate-600 space-y-2 text-left bg-emerald-50 p-3.5 rounded-xl border border-emerald-200 mt-2 font-kanit">
                                            <div class="flex justify-between font-bold">
                                                <span>เลขที่ใบแจ้งซ่อม:</span>
                                                <span class="font-mono text-emerald-800">${event.data.ticket_no}</span>
                                            </div>
                                            <div class="flex justify-between font-bold">
                                                <span>ขบวนรถ:</span>
                                                <span class="text-pink-600">${event.data.car_id || '-'}</span>
                                            </div>
                                            ${event.data.repair_receipt_no ? `<div class="flex justify-between font-bold"><span>เลขที่ใบเสร็จ:</span><span class="font-mono text-slate-800">${event.data.repair_receipt_no}</span></div>` : ''}
                                            ${event.data.repair_receipt_cost ? `<div class="flex justify-between font-bold"><span>จำนวนเงินตามใบเสร็จ:</span><span class="text-emerald-700 font-black">${Number(event.data.repair_receipt_cost).toLocaleString()} บาท</span></div>` : ''}
                                            <p class="text-slate-500 text-center text-[11px] pt-1">งานซ่อมเสร็จสิ้นและช่างนำส่งใบเสร็จเรียบร้อยแล้ว</p>
                                        </div>
                                    `,
                                    confirmButtonText: '<i class="fas fa-file-invoice mr-1"></i> ดูใบเสร็จ & ผลการซ่อม',
                                    confirmButtonColor: '#10B981',
                                    customClass: { popup: 'rounded-2xl font-kanit' }
                                }).then((res) => {
                                    if (res.isConfirmed) {
                                        openViewRepairReceiptModal(event.data.ticket_no);
                                    }
                                });
                            }
                        }
                    }
                };
            } catch(e) {}
        });
    </script>

    <!-- Modal ดูใบเสร็จการซ่อม & สรุปผลการซ่อมบำรุง (สำหรับหัวหน้ายานพาหนะ) -->
    <div id="viewRepairReceiptModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center hidden p-4 z-50 transition-all duration-300 font-kanit">
        <div class="bg-white p-6 md:p-7 rounded-3xl shadow-2xl w-full max-w-xl max-h-[92vh] overflow-y-auto border border-slate-100">
            <div class="flex justify-between items-center mb-4 border-b border-emerald-100 pb-3">
                <div class="flex items-center gap-2.5">
                    <div class="w-10 h-10 rounded-2xl bg-emerald-600 text-white flex items-center justify-center font-black text-base shadow-md shadow-emerald-500/30">
                        <i class="fas fa-file-invoice-dollar"></i>
                    </div>
                    <div>
                        <h3 class="text-base font-black text-slate-800">ใบเสร็จการซ่อม & รายงานผลซ่อมบำรุง</h3>
                        <p class="text-xs text-slate-400 font-medium">เลขที่เอกสาร: <span id="vrrTicketNo" class="font-mono font-bold text-emerald-800">-</span></p>
                    </div>
                </div>
                <button onclick="closeViewRepairReceiptModal()" class="text-slate-400 hover:text-slate-600 p-1 focus:outline-none transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>

            <div class="space-y-4 text-xs">
                <!-- Status & Car Card -->
                <div class="bg-gradient-to-r from-emerald-50 to-teal-50 p-4 rounded-2xl border border-emerald-200/80 space-y-2">
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500 font-medium">ขบวนรถ / ทะเบียน:</span>
                        <span id="vrrCarInfo" class="text-pink-600 font-extrabold text-sm">EV-01</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-slate-500 font-medium">สถานะงานซ่อม:</span>
                        <span class="bg-emerald-100 text-emerald-800 text-[10px] font-black px-2.5 py-0.5 rounded-full border border-emerald-300">
                            <i class="fas fa-check-circle mr-1"></i> ซ่อมเสร็จสมบูรณ์
                        </span>
                    </div>
                    <div class="flex justify-between items-center pt-1.5 border-t border-emerald-200/60">
                        <span class="text-slate-600 font-bold">วันที่บันทึกเสร็จสิ้น:</span>
                        <span id="vrrCompletedAt" class="font-mono font-bold text-slate-800">-</span>
                    </div>
                </div>

                <!-- Receipt Details Card -->
                <div class="bg-white p-4 rounded-2xl border-2 border-emerald-100 shadow-xs space-y-2">
                    <h4 class="font-black text-emerald-900 text-xs flex items-center gap-1.5">
                        <i class="fas fa-receipt text-emerald-600"></i> ข้อมูลใบเสร็จรับเงินการซ่อม
                    </h4>
                    <div class="grid grid-cols-2 gap-3 pt-1">
                        <div class="bg-slate-50 p-2.5 rounded-xl border border-slate-200">
                            <span class="text-slate-400 text-[10px] block font-bold">เลขที่ใบเสร็จรับเงิน</span>
                            <span id="vrrReceiptNo" class="font-mono font-bold text-slate-900 text-xs">-</span>
                        </div>
                        <div class="bg-emerald-50 p-2.5 rounded-xl border border-emerald-200">
                            <span class="text-emerald-700 text-[10px] block font-bold">จำนวนเงินรวมทั้งสิ้น</span>
                            <span id="vrrReceiptCost" class="font-mono font-black text-emerald-700 text-sm">0.00 บาท</span>
                        </div>
                    </div>
                </div>

                <!-- Technician Notes & Tested Result -->
                <div class="bg-slate-50 p-3.5 rounded-2xl border border-slate-200 space-y-1.5">
                    <span class="font-bold text-slate-700 block">รายละเอียดการซ่อม & ผลการทดสอบจากช่าง:</span>
                    <p id="vrrCompletionNotes" class="text-slate-800 font-medium whitespace-pre-line text-[11px] leading-relaxed bg-white p-2.5 rounded-xl border border-slate-200">-</p>
                    <div class="flex justify-between items-center text-slate-500 text-[11px] pt-1">
                        <span>ช่างผู้ดำเนินการ:</span>
                        <span id="vrrTechnicianName" class="font-bold text-slate-800">-</span>
                    </div>
                </div>

                <!-- Executive Approval Details -->
                <div class="bg-purple-50/60 p-3.5 rounded-2xl border border-purple-100 space-y-1.5">
                    <div class="flex justify-between items-center">
                        <span class="font-bold text-purple-900">ผู้อนุมัติโครงการ (ผอ.):</span>
                        <span id="vrrDirectorName" class="font-bold text-purple-950">-</span>
                    </div>
                    <div class="flex justify-between items-center text-[11px]">
                        <span class="text-slate-500">งบประมาณที่ได้รับอนุมัติ:</span>
                        <span id="vrrApprovedBudget" class="font-mono font-bold text-purple-800">0.00 บาท</span>
                    </div>
                </div>

                <!-- Receipt Image Attachment -->
                <div id="vrrReceiptImgWrapper" class="hidden">
                    <span class="font-bold text-slate-700 block mb-1.5">รูปถ่าย/หลักฐานใบเสร็จรับเงิน:</span>
                    <div class="text-center p-2 bg-slate-50 rounded-2xl border border-slate-200">
                        <img id="vrrReceiptImg" src="" class="max-h-48 mx-auto rounded-xl border border-slate-300 shadow-md cursor-pointer hover:opacity-95 transition" onclick="window.open(this.src, '_blank')">
                        <span class="text-[10px] text-slate-400 block mt-1">(คลิกที่ภาพเพื่อดูขนาดใหญ่)</span>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-2.5 mt-6 pt-3.5 border-t border-slate-100">
                <button onclick="closeViewRepairReceiptModal()" class="px-4 py-2.5 rounded-xl text-xs font-semibold text-slate-600 hover:bg-slate-100 transition">ปิดหน้าต่าง</button>
                <button onclick="viewTicketOfficial(document.getElementById('vrrTicketNo').innerText)" class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2.5 rounded-xl text-xs font-bold shadow-md flex items-center gap-1.5 transition cursor-pointer">
                    <i class="fas fa-print"></i> พิมพ์แบบฟอร์มเสร็จสมบูรณ์
                </button>
            </div>
        </div>
    </div>
</body>
</html>
