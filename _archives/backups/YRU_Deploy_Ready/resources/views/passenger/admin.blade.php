<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ระบบติดตามเส้นทางการเดินรถไฟฟ้ามหาวิทยาลัยราชภัฏยะลา - Admin</title>
    <script src="/api/storage/init.js?v={{ time() }}"></script>
    <script src="https://cdn.tailwindcss.com"></script>
    <script>
        tailwind.config = {
            theme: {
                extend: {
                    fontFamily: {
                        sans: ['Kanit', 'sans-serif'],
                    }
                }
            }
        }
    </script>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <!-- Leaflet GIS Map Library -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.css" />
    <script src="https://cdnjs.cloudflare.com/ajax/libs/leaflet/1.9.4/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <!-- SweetAlert2 & SheetJS Excel Parser -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="https://cdn.jsdelivr.net/npm/xlsx@0.18.5/dist/xlsx.full.min.js"></script>
    <style>
        body, button, input, select, textarea, div, span, p, a, h1, h2, h3, h4, h5, h6, label, td, th { font-family: 'Kanit', sans-serif !important; }
        /* Protect FontAwesome Icons from being overridden by Kanit */
        .fa, .fas, .far, .fab, .fa-solid, .fa-regular, .fa-brands, [class*="fa-"] {
            font-family: 'Font Awesome 6 Free', 'Font Awesome 6 Brands', 'Font Awesome 5 Free', sans-serif !important;
        }
        .page { display: none; }
        .active-menu { background-color: rgba(255, 255, 255, 0.2); border-left: 4px solid #fff; }
        #integratedMap {
            width: 100% !important;
            height: 580px !important;
            min-height: 580px !important;
            display: block !important;
        }

        /* === Toggle Switch === */
        .toggle-switch {
            position: relative;
            display: inline-block;
            width: 44px;
            height: 24px;
            cursor: pointer;
        }
        .toggle-switch input { opacity: 0; width: 0; height: 0; }
        .toggle-slider {
            position: absolute;
            inset: 0;
            background: #d1d5db;
            border-radius: 9999px;
            transition: background 0.25s ease;
        }
        .toggle-slider::before {
            content: '';
            position: absolute;
            width: 18px;
            height: 18px;
            left: 3px;
            top: 3px;
            background: white;
            border-radius: 50%;
            transition: transform 0.25s ease;
            box-shadow: 0 1px 3px rgba(0,0,0,0.2);
        }
        .toggle-switch input:checked + .toggle-slider { background: #10b981; }
        .toggle-switch input:checked + .toggle-slider::before { transform: translateX(20px); }
        .toggle-switch:hover .toggle-slider { filter: brightness(0.95); }

        /* ล็อค toggle ที่เป็น Admin (เปิดตลอด) */
        .toggle-locked { opacity: 0.5; cursor: not-allowed; pointer-events: none; }
    </style>
</head>
<body class="bg-gray-50 flex flex-col h-screen overflow-hidden">

<header class="bg-gradient-to-r from-pink-400 via-pink-500 to-pink-400 text-white py-4 px-6 flex justify-between items-center z-30 relative shrink-0" style="box-shadow: 0 4px 30px rgba(236,72,153,0.25); border-bottom: 1.5px solid rgba(255,255,255,0.1);">
    <div class="flex items-center gap-4">
        <button onclick="toggleSidebar()" class="text-white p-2 focus:outline-none md:hidden hover:bg-white/10 rounded-lg">
            <i class="fas fa-bars text-xl"></i>
        </button>
        <!-- Logo with glow ring -->
        <div class="relative flex-shrink-0">
            <div class="absolute inset-0 bg-white/30 rounded-full blur-sm scale-110"></div>
            <img src="{{ asset('img/logo-yru.png') }}" alt="YRU Logo"
                 class="relative h-11 w-11 bg-white rounded-full object-contain p-1 shadow-lg ring-2 ring-white/70">
        </div>
        <!-- Divider -->
        <div class="hidden sm:block w-px h-10 bg-white/30 rounded-full"></div>
        <!-- Title block -->
        <div class="hidden sm:flex flex-col justify-center">
            <span class="font-black text-white text-sm md:text-base leading-tight tracking-wide drop-shadow-sm">ระบบติดตามเส้นทางการเดินรถไฟฟ้า</span>
            <span class="font-semibold text-white/90 text-xs md:text-sm leading-tight">มหาวิทยาลัยราชภัฏยะลา</span>
        </div>
        <span class="font-bold text-white text-base tracking-wide sm:hidden">YRU EV Tracker</span>
    </div>

    <!-- User Profile Dropdown -->
    <div class="relative inline-block text-left" id="profileDropdownContainer">
        @php
            $adminEmail = 'admin@yru.ac.th';
            try {
                if (class_exists('Illuminate\Support\Facades\Auth') && \Illuminate\Support\Facades\Auth::check()) {
                    $u = \Illuminate\Support\Facades\Auth::user();
                    if ($u) { $adminEmail = $u->email ?? $u->username ?? 'admin@yru.ac.th'; }
                }
            } catch (\Throwable $e) {}
            $adminInitial = strtoupper(substr($adminEmail, 0, 1));
        @endphp
        <button type="button" onclick="toggleProfileDropdown()" class="flex items-center gap-2 focus:outline-none hover:bg-white/10 transition-all rounded-full px-3 py-1.5 border border-white/10">
            <div class="hidden sm:flex flex-col items-end mr-1">
                <span id="headerProfileName" class="text-white text-xs font-bold drop-shadow-sm">{{ $adminEmail }}</span>
            </div>
            <div id="headerProfileAvatar" class="w-8 h-8 rounded-full bg-white text-pink-500 flex items-center justify-center text-sm font-black shadow-md">
                {{ $adminInitial }}
            </div>
            <i class="fas fa-chevron-down text-white text-[10px] ml-1 transition-transform duration-200" id="profileDropdownIcon"></i>
        </button>

        <!-- Dropdown Menu -->
        <div id="profileDropdownMenu" class="absolute right-0 mt-2 w-48 rounded-xl shadow-lg bg-white ring-1 ring-black ring-opacity-5 hidden opacity-0 transform scale-95 transition-all duration-200 origin-top-right z-50">
            <div class="py-1">
                <a href="{{ url('/') }}" onclick="sessionStorage.clear(); localStorage.removeItem('yru_user_login');" class="group flex items-center px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors font-medium">
                    <i class="fas fa-sign-out-alt w-5 text-center mr-3"></i> ออกจากระบบ
                </a>
            </div>
        </div>
    </div>
</header>


<div class="flex flex-1 overflow-hidden relative">

    <aside id="sidebar" class="w-64 bg-gradient-to-b from-pink-400 to-pink-500 text-white p-6 absolute inset-y-0 left-0 transform -translate-x-full transition duration-200 ease-in-out z-40 md:relative md:translate-x-0 flex flex-col justify-between">
        <div>
            <div class="flex justify-end items-center mb-8">
                <button onclick="toggleSidebar()" class="text-white md:hidden">
                    <i class="fas fa-times text-xl"></i>
                </button>
            </div>
            <nav class="space-y-2">
                <button onclick="navigatePage('dashboard', this)" class="menu-btn flex items-center space-x-3 hover:bg-white/10 w-full px-4 py-3 rounded-lg text-left transition active-menu">
                    <i class="fas fa-chart-bar w-5"></i><span>รายงานสถิติ</span>
                </button>
                <button onclick="navigatePage('tram', this)" class="menu-btn flex items-center space-x-3 hover:bg-white/10 w-full px-4 py-3 rounded-lg text-left transition">
                    <i class="fas fa-bus w-5"></i><span>จัดการรถไฟฟ้า</span>
                </button>
                <button onclick="navigatePage('route', this)" class="menu-btn flex items-center space-x-3 hover:bg-white/10 w-full px-4 py-3 rounded-lg text-left transition">
                    <i class="fas fa-map-marked-alt w-5"></i><span>จุดจอดและเส้นทาง</span>
                </button>
                <button onclick="navigatePage('users', this)" class="menu-btn flex items-center space-x-3 hover:bg-white/10 w-full px-4 py-3 rounded-lg text-left transition">
                    <i class="fas fa-users w-5"></i><span>จัดการข้อมูลผู้ใช้งาน</span>
                </button>
                <button onclick="navigatePage('role', this)" class="menu-btn flex items-center space-x-3 hover:bg-white/10 w-full px-4 py-3 rounded-lg text-left transition">
                    <i class="fas fa-lock w-5"></i><span>รายงานสิทธิ์การใช้งาน</span>
                </button>

            </nav>
        </div>
        <div class="text-xs text-pink-200 text-center border-t border-pink-400/30 pt-4">
            &copy; 2026 Yala Rajabhat University
        </div>
    </aside>

    <main class="flex-1 overflow-y-auto p-4 md:p-8">

        <div id="dashboard" class="page">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 font-kanit">
                <div>
                    <h2 class="text-2xl font-black text-slate-800 tracking-tight">รายงานสถิติภาพรวม</h2>
                    <p class="text-xs text-slate-500 font-normal mt-0.5">ภาพรวมการให้บริการ รถไฟฟ้า สถานะการเดินรถ และคิวเรียกรถประจำวัน</p>
                </div>
                <div class="flex flex-wrap items-center gap-2">
                    <button onclick="showPage('tram')" class="bg-blue-600 hover:bg-blue-700 text-white px-3.5 py-2 rounded-xl transition text-xs font-bold shadow-xs flex items-center gap-1.5 active:scale-95">
                        <i class="fas fa-plus-circle text-xs"></i> เพิ่มรถ
                    </button>
                    <button onclick="showPage('route')" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-2 rounded-xl transition text-xs font-bold shadow-xs flex items-center gap-1.5 active:scale-95">
                        <i class="fas fa-plus-circle text-xs"></i> เพิ่มจุดจอด
                    </button>
                    <button onclick="showPage('reportView')" class="bg-slate-700 hover:bg-slate-800 text-white px-3.5 py-2 rounded-xl transition text-xs font-bold shadow-xs flex items-center gap-1.5 active:scale-95">
                        <i class="fas fa-file-alt text-xs"></i> ดูรายงานฉบับเต็ม
                    </button>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-5 mb-8 font-kanit">
                <div class="bg-gradient-to-br from-white to-blue-50/50 p-5 rounded-2xl shadow-sm border border-blue-100/80 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 group">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">จำนวนการเรียกรถทั้งหมด</p>
                            <h3 id="dash-total-users" class="text-3xl font-black mt-2 text-slate-800 tracking-tight">0 รอบ</h3>
                        </div>
                        <div class="w-11 h-11 bg-gradient-to-br from-blue-500 to-indigo-600 rounded-xl flex items-center justify-center shadow-md shadow-blue-500/20 group-hover:scale-110 transition-transform">
                            <i class="fas fa-phone-alt text-white text-base"></i>
                        </div>
                    </div>
                    <button onclick="showPage('reportView')" class="mt-4 text-blue-600 hover:text-blue-800 hover:underline text-xs font-bold flex items-center gap-1 transition">
                        ดูรายละเอียด <i class="fas fa-arrow-right text-[10px]"></i>
                    </button>
                </div>
                <div class="bg-gradient-to-br from-white to-emerald-50/50 p-5 rounded-2xl shadow-sm border border-emerald-100/80 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 flex flex-col justify-between font-kanit">
                    <div>
                        <div class="flex items-center justify-between border-b border-gray-100 pb-2.5 mb-3">
                            <p class="text-slate-600 text-xs font-bold tracking-wide uppercase flex items-center gap-1.5">
                                <i class="fas fa-bus text-emerald-600"></i> สถานะรถปัจจุบัน (เรียลไทม์)
                            </p>
                            <span class="text-[11px] bg-emerald-50 text-emerald-700 font-extrabold px-2.5 py-0.5 rounded-full border border-emerald-200/80 shadow-2xs">
                                รวม <span id="dash-tram-count" class="font-black">0</span> คัน
                            </span>
                        </div>
                        
                        <!-- Real-time Status Breakdown Badges -->
                        <div class="grid grid-cols-3 gap-2 text-center">
                            <div class="bg-emerald-50/90 border border-emerald-200/80 rounded-xl p-2 shadow-2xs">
                                <div class="text-[11px] text-emerald-800 font-bold flex items-center justify-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-emerald-500 inline-block animate-pulse"></span> วิ่งอยู่
                                </div>
                                <div class="text-xl font-black text-emerald-700 mt-1"><span id="dash-status-running">0</span> <span class="text-[10px] font-normal text-slate-500">คัน</span></div>
                            </div>

                            <div class="bg-amber-50/90 border border-amber-200/80 rounded-xl p-2 shadow-2xs">
                                <div class="text-[11px] text-amber-800 font-bold flex items-center justify-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-amber-500 inline-block"></span> พักเบรก
                                </div>
                                <div class="text-xl font-black text-amber-700 mt-1"><span id="dash-status-pause">0</span> <span class="text-[10px] font-normal text-slate-500">คัน</span></div>
                            </div>

                            <div class="bg-rose-50/90 border border-rose-200/80 rounded-xl p-2 shadow-2xs">
                                <div class="text-[11px] text-rose-800 font-bold flex items-center justify-center gap-1">
                                    <span class="w-2 h-2 rounded-full bg-rose-500 inline-block"></span> ซ่อม/ระงับ
                                </div>
                                <div class="text-xl font-black text-rose-700 mt-1"><span id="dash-status-broken">0</span> <span class="text-[10px] font-normal text-slate-500">คัน</span></div>
                            </div>
                        </div>
                    </div>

                    <button onclick="showPage('tram')" class="mt-3 text-emerald-600 hover:text-emerald-800 hover:underline text-xs font-bold block text-right">
                        จัดการสถานะรถทั้งหมด &rarr;
                    </button>
                </div>
                <div class="bg-gradient-to-br from-white to-orange-50/50 p-5 rounded-2xl shadow-sm border border-orange-100/80 hover:shadow-lg hover:-translate-y-0.5 transition-all duration-300 group">
                    <div class="flex items-start justify-between">
                        <div>
                            <p class="text-slate-500 text-xs font-bold uppercase tracking-wider">จุดจอดรถไฟฟ้าทั้งหมด</p>
                            <h3 id="dash-stop-count" class="text-3xl font-black mt-2 text-slate-800 tracking-tight">0 จุด</h3>
                        </div>
                        <div class="w-11 h-11 bg-gradient-to-br from-orange-500 to-amber-600 rounded-xl flex items-center justify-center shadow-md shadow-orange-500/20 group-hover:scale-110 transition-transform">
                            <i class="fas fa-map-pin text-white text-base"></i>
                        </div>
                    </div>
                    <button onclick="showPage('route')" class="mt-4 text-orange-600 hover:text-orange-800 hover:underline text-xs font-bold flex items-center gap-1 transition">
                        ดูแผนผังจุดจอด <i class="fas fa-arrow-right text-[10px]"></i>
                    </button>
                </div>
            </div>



            <!-- Analytics & Optimization Dimension Cards Grid (Data Visualization) -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8 font-kanit">
                <!-- 1. Peak Hours Analytics Chart (ช่วงเวลาที่มีผู้โดยสารหนาแน่นที่สุด) -->
                <div class="bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between hover:shadow-md transition">
                    <div>
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                            <div>
                                <h3 class="font-extrabold text-base text-gray-800 flex items-center gap-2">
                                    <i class="fas fa-clock text-amber-500"></i> ช่วงเวลาที่มีผู้โดยสารหนาแน่นที่สุด (Peak Hours)
                                </h3>
                                <p class="text-xs text-gray-500 mt-0.5">จำแนกตามช่วงเวลาในแต่ละวัน เพื่อปรับความถี่ในการจัดรถไฟฟ้า</p>
                            </div>
                            <span class="text-[10px] bg-amber-50 text-amber-700 font-bold px-2.5 py-0.5 rounded-full border border-amber-200 shadow-2xs">
                                📊 วิเคราะห์ความหนาแน่น
                            </span>
                        </div>
                        <div class="h-56 relative">
                            <canvas id="peakHoursChart"></canvas>
                        </div>
                    </div>
                    <div class="bg-amber-50/80 border border-amber-200/80 rounded-xl p-3 mt-4 text-xs text-amber-900 flex items-start gap-2.5 shadow-2xs">
                        <i class="fas fa-lightbulb text-amber-500 text-base mt-0.5"></i>
                        <div>
                            <span class="font-bold text-amber-950">ข้อเสนอแนะในการจัดรถ:</span>
                            <span id="peak-hours-insight" class="block mt-0.5">ช่วงเวลาหนาแน่นสูงสุดคือ 08:00 - 10:00 น. และ 16:00 - 18:00 น. แนะนำให้เพิ่มความถี่วิ่งรถเป็นทุก 5 นาที</span>
                        </div>
                    </div>
                </div>

                <!-- 2. Most Used Stations Analytics Chart (สถิติจุดจอดที่มีคนใช้บริการสูงสุด) -->
                <div class="bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-gray-100 flex flex-col justify-between hover:shadow-md transition">
                    <div>
                        <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                            <div>
                                <h3 class="font-extrabold text-base text-gray-800 flex items-center gap-2">
                                    <i class="fas fa-map-marker-alt text-pink-500"></i> จุดจอดที่มีผู้ใช้บริการสูงสุด (Top Stations)
                                </h3>
                                <p class="text-xs text-gray-500 mt-0.5">สถิติจุดรับ-ส่งที่ได้รับการเรียกรถและเข้าใช้บริการมากที่สุด</p>
                            </div>
                            <span class="text-[10px] bg-pink-50 text-pink-700 font-bold px-2.5 py-0.5 rounded-full border border-pink-200 shadow-2xs">
                                🚉 สถิติจุดจอด
                            </span>
                        </div>
                        <div class="h-56 relative">
                            <canvas id="topStationsChart"></canvas>
                        </div>
                    </div>
                    <div class="bg-pink-50/80 border border-pink-200/80 rounded-xl p-3 mt-4 text-xs text-pink-900 flex items-start gap-2.5 shadow-2xs">
                        <i class="fas fa-chart-line text-pink-500 text-base mt-0.5"></i>
                        <div>
                            <span class="font-bold text-pink-950">ข้อมูลการกระจายตัว:</span>
                            <span id="top-stations-insight" class="block mt-0.5">จุดจอด ประตู 1 ประตูหลัง มอ. และ คณะวิทยาการจัดการ มีปริมาณผู้ใช้บริการสูงสุดเป็นอันดับ 1</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 md:p-8 rounded-2xl shadow-sm border border-gray-100 overflow-x-auto font-kanit hover:shadow-md transition">
                <div class="flex justify-between items-center mb-5 min-w-[600px] border-b border-gray-100 pb-4">
                    <div class="flex items-center gap-2.5">
                        <div class="w-8 h-8 bg-gradient-to-br from-violet-500 to-purple-600 rounded-lg flex items-center justify-center shadow-sm">
                            <i class="fas fa-bell text-white text-xs"></i>
                        </div>
                        <div>
                            <h3 class="font-extrabold text-base text-slate-800">คิวเรียกรถไฟฟ้าล่าสุด</h3>
                            <p class="text-[10px] text-slate-400 mt-0.5">รายการคิวเรียกรถจากผู้โดยสาร อัปเดตแบบเรียลไทม์</p>
                        </div>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="renderDashboardData()" class="bg-slate-100 text-slate-600 px-3 py-1.5 rounded-xl hover:bg-slate-200 transition text-xs font-bold flex items-center gap-1.5 active:scale-95">
                            <i class="fas fa-sync-alt text-[10px]"></i> รีเฟรช
                        </button>
                        <button onclick="restoreDummyQueue()" class="bg-blue-50 text-blue-600 px-3 py-1.5 rounded-xl hover:bg-blue-100 transition text-xs font-bold flex items-center gap-1.5 active:scale-95">
                            <i class="fas fa-database text-[10px]"></i> กู้คืน (30)
                        </button>
                        <button onclick="clearCallQueue()" class="bg-rose-50 text-rose-500 px-3 py-1.5 rounded-xl hover:bg-rose-100 transition text-xs font-bold flex items-center gap-1.5 active:scale-95">
                            <i class="fas fa-trash-alt text-[10px]"></i> ล้างคิว
                        </button>
                    </div>
                </div>
                <table class="w-full text-left border-collapse min-w-[600px]">
                    <thead>
                        <tr class="border-b border-slate-100 text-slate-400 text-xs uppercase tracking-wider font-bold">
                            <th class="p-3 pl-0">รหัสคิว</th>
                            <th class="p-3">เวลา</th>
                            <th class="p-3">จุดรับ</th>
                            <th class="p-3">ปลายทาง</th>
                            <th class="p-3 text-center">จำนวนคน</th>
                            <th class="p-3 text-center">สถานะ</th>
                        </tr>
                    </thead>
                    <tbody id="dashboardLogTable" class="text-sm text-gray-700"></tbody>
                </table>

                <!-- คิวเรียกรถ Pagination Footer -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 mt-4 pt-4 border-t border-gray-100 min-w-[600px]">
                    <div id="dash-pagination-btns" class="flex items-center gap-1.5 flex-wrap"></div>
                    <div id="dash-pagination-info" class="text-xs text-gray-500 font-medium"></div>
                </div>
            </div>
        </div>

        <div id="reportView" class="page">
            <!-- ====== Header ====== -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5">
                <div class="flex items-center gap-3">
                    <button onclick="showPage('dashboard')" id="btn-back-dashboard"
                        class="flex items-center gap-2 bg-white border border-gray-200 text-gray-700 px-4 py-2 rounded-xl hover:bg-gray-50 transition text-sm shadow-sm font-medium">
                        <i class="fas fa-arrow-left text-pink-500"></i> กลับหน้ารายงานสถิติ
                    </button>
                    <div>
                        <h2 class="text-xl font-black text-gray-800 leading-tight">รายงานฉบับเต็ม</h2>
                        <p class="text-xs text-gray-400 mt-0.5">มหาวิทยาลัยราชภัฏยะลา | ข้อมูล ณ วันที่ <span id="rv-today-label" class="font-semibold text-gray-600">--</span></p>
                    </div>
                </div>
                <div class="flex items-center gap-2 flex-wrap">
                    <button onclick="exportReportPDF()" id="btn-export-pdf"
                        class="flex items-center gap-2 bg-red-500 hover:bg-red-600 text-white px-4 py-2 rounded-xl text-sm font-semibold shadow transition">
                        <i class="fas fa-file-pdf"></i> PDF
                    </button>
                    <button onclick="exportReportExcel()" id="btn-export-excel"
                        class="flex items-center gap-2 bg-emerald-500 hover:bg-emerald-600 text-white px-4 py-2 rounded-xl text-sm font-semibold shadow transition">
                        <i class="fas fa-file-excel"></i> Excel
                    </button>
                    <button onclick="window.print()" id="btn-print-report"
                        class="flex items-center gap-2 bg-gray-700 hover:bg-gray-800 text-white px-4 py-2 rounded-xl text-sm font-semibold shadow transition">
                        <i class="fas fa-print"></i> พิมพ์
                    </button>
                </div>
            </div>

            <!-- ====== Filter Bar ====== -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-4 mb-5">
                <div class="flex flex-wrap gap-3 items-center justify-between">
                    <div class="flex flex-wrap items-center gap-3">
                        <select id="rv-filter-preset" class="hidden"><option value="custom" selected></option></select>

                        <!-- Date Range Pickers (วันที่ - ถึงวันที่) -->
                        <div class="flex items-center gap-2 bg-gray-50 border border-gray-200 rounded-xl px-3 py-2 text-sm">
                            <input type="date" lang="en-GB" id="rv-filter-date-from" onchange="applyReportFilters()" class="bg-transparent outline-none text-gray-700 text-xs w-[110px]">
                            <span class="text-gray-400 text-xs">–</span>
                            <input type="date" lang="en-GB" id="rv-filter-date-to" onchange="applyReportFilters()" class="bg-transparent outline-none text-gray-700 text-xs w-[110px]">
                        </div>

                        <!-- Hidden search box for JS compatibility -->
                        <input type="text" id="rv-filter-search" class="hidden" value="">
                    </div>

                    <!-- Hidden elements for JS compatibility -->
                    <select id="rv-filter-route" class="hidden"><option value="">ทั้งหมด</option></select>
                    <select id="rv-filter-status" class="hidden"><option value="">ทั้งหมด</option></select>
                    <button onclick="applyReportFilters()" id="btn-apply-report-filter" class="hidden"></button>
                    <button onclick="resetReportFilters()" id="btn-reset-report-filter" class="hidden"></button>
                </div>
            </div>

            <!-- ====== KPI Summary Cards ====== -->
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-4 mb-5">
                <!-- รอบเดินรถรวม -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition group">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-blue-50 flex items-center justify-center group-hover:scale-110 transition">
                            <i class="fas fa-sync-alt text-blue-500 text-base"></i>
                        </div>
                        <span class="text-[10px] font-bold text-blue-400 bg-blue-50 px-2 py-0.5 rounded-full uppercase tracking-wide">รอบรวม</span>
                    </div>
                    <h4 class="text-3xl font-black text-gray-800" id="rv-kpi-trips">–</h4>
                    <p class="text-xs text-gray-400 mt-1 font-medium">รอบเดินรถรวม</p>
                </div>
                <!-- ระยะทางรวม -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition group">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-emerald-50 flex items-center justify-center group-hover:scale-110 transition">
                            <i class="fas fa-road text-emerald-500 text-base"></i>
                        </div>
                        <span class="text-[10px] font-bold text-emerald-400 bg-emerald-50 px-2 py-0.5 rounded-full uppercase tracking-wide">ระยะทาง</span>
                    </div>
                    <h4 class="text-3xl font-black text-gray-800" id="rv-kpi-distance">–</h4>
                    <p class="text-xs text-gray-400 mt-1 font-medium">ระยะทางรวม (กม.)</p>
                </div>
                <!-- TOP STOP card removed (keep hidden span for JS compat) -->
                <span id="rv-kpi-top-stop" class="hidden"></span>
                <!-- รถที่ใช้วานเฉลี่ย -->
                <div class="bg-white rounded-2xl p-5 shadow-sm border border-gray-100 hover:shadow-md transition group">
                    <div class="flex items-center justify-between mb-3">
                        <div class="w-10 h-10 rounded-xl bg-pink-50 flex items-center justify-center group-hover:scale-110 transition">
                            <i class="fas fa-bus text-pink-500 text-base"></i>
                        </div>
                        <span class="text-[10px] font-bold text-pink-400 bg-pink-50 px-2 py-0.5 rounded-full uppercase tracking-wide">รถใช้งาน</span>
                    </div>
                    <h4 class="text-3xl font-black text-gray-800" id="rv-kpi-avg-cars">–</h4>
                    <p class="text-xs text-gray-400 mt-1 font-medium">รถที่ใช้วานเฉลี่ย (คัน/วัน)</p>
                </div>
            </div>

            <!-- ====== Chart ====== -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 p-5 mb-5">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
                    <h3 class="font-bold text-base text-gray-800 flex items-center gap-2">
                        <i class="fas fa-chart-bar text-pink-500"></i>
                        กราฟแสดงจำนวนรอบการเดินรถแยกตามวัน/สัปดาห์
                    </h3>
                    <div class="flex gap-2">
                        <button onclick="switchReportChartView('daily')" id="rv-chart-btn-daily"
                            class="text-xs px-3 py-1.5 rounded-lg bg-pink-500 text-white font-semibold transition">รายวัน</button>
                        <button onclick="switchReportChartView('weekly')" id="rv-chart-btn-weekly"
                            class="text-xs px-3 py-1.5 rounded-lg bg-gray-100 text-gray-600 font-semibold hover:bg-gray-200 transition">รายสัปดาห์</button>
                    </div>
                </div>
                <div class="h-52 relative">
                    <canvas id="reportViewChart"></canvas>
                </div>
            </div>

            <!-- ====== Detail Table ====== -->
            <div class="bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 px-5 py-4 border-b border-gray-100">
                    <h3 class="font-bold text-base text-gray-800 flex items-center gap-2">
                        <i class="fas fa-table text-gray-500"></i> ตารางรายละเอียดการเดินรถ
                    </h3>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left min-w-[720px]">
                        <thead class="bg-gray-50 border-b-2 border-pink-100">
                            <tr>
                                <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider whitespace-nowrap">วันที่-เวลา</th>
                                <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">รหัสรถ</th>
                                <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">เส้นทาง (ต้นทาง → ปลายทาง)</th>
                                <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider">คนขับ</th>
                                <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">ผู้โดยสาร</th>
                                <th class="px-4 py-3 text-xs font-bold text-gray-500 uppercase tracking-wider text-center">สถานะ</th>
                            </tr>
                        </thead>
                        <tbody id="rv-detail-tbody" class="text-sm divide-y divide-gray-100">
                            <tr>
                                <td colspan="6" class="px-8 py-16 text-center text-slate-500 font-semibold">
                                    <i class="fas fa-circle-notch fa-spin text-4xl mb-4 text-pink-400 block"></i>กำลังดึงข้อมูลการเดินรถ...
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
                <!-- Pagination -->
                <div class="flex flex-col sm:flex-row items-center justify-between gap-3 px-5 py-3.5 border-t border-gray-100 bg-gray-50/50">
                    <div class="flex items-center gap-1" id="rv-pagination-btns">
                        <!-- generated by JS -->
                    </div>
                    <span class="text-xs text-gray-500 font-medium" id="rv-pagination-info">หน้า 1 จาก 1</span>
                </div>
            </div>

            <!-- Legacy cards (hidden but keep IDs for JS compat) -->
            <div class="hidden">
                <div id="report-peak-hours"></div>
                <span id="report-satisf-score"></span>
                <span id="report-star-5"></span>
                <span id="report-star-3"></span>
                <span id="report-star-1"></span>
                <tbody id="report-route-tbody"></tbody>
            </div>
        </div>

        <div id="tram" class="page">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">จัดการรถไฟฟ้าในระบบ</h2>
                    <p class="text-gray-500 text-sm">เพิ่ม ลบ หรือแก้ไขสถานะรถไฟฟ้าบริการภายในมหาวิทยาลัย</p>
                </div>
                <button onclick="openTramModal()" class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700 transition shadow-sm text-sm">+ เพิ่ม</button>
            </div>

            <div class="bg-white p-4 rounded-xl shadow-sm mb-6 flex flex-wrap gap-4 items-center justify-between">
                <div class="flex flex-wrap gap-3 items-center">
                    <span class="text-sm text-gray-600 font-medium"><i class="fas fa-filter mr-1"></i> ตัวกรองข้อมูล:</span>
                    <select id="tramFilterStatus" onchange="filterTrams()" class="border border-gray-300 p-1.5 rounded-lg text-sm bg-white focus:ring-2 focus:ring-pink-400 outline-none">
                        <option value="ทั้งหมด">แสดงสถานะทั้งหมด</option>
                        <option value="พร้อมใช้งาน">พร้อมใช้งาน</option>
                        <option value="รถขัดข้อง">รถขัดข้อง</option>
                        <option value="ระงับการใช้งาน">ระงับการใช้งาน</option>
                    </select>
                </div>
                <div class="relative w-full sm:w-80">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" id="tramSearchInput" onkeyup="filterTrams()" 
                        class="w-full pl-9 pr-4 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none text-sm" 
                        placeholder="ค้นหา รหัสรถ, ทะเบียน, หรือชื่อคนขับ...">
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden overflow-x-auto">
                <table class="w-full text-left min-w-[900px]">
                    <thead class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="p-4 w-20">รูปภาพ</th>
                            <th class="p-4">รหัส / ชื่อเรียก</th>
                            <th class="p-4 min-w-[150px]">ทะเบียนรถ</th>
                            <th class="p-4">ความจุ</th>
                            <th class="p-4">คนขับประจำ</th>
                            <th class="p-4 text-center">สถานะการใช้งาน</th>
                            <th class="p-4 text-center w-44">การจัดการข้อมูล</th>
                        </tr>
                    </thead>
                    <tbody id="tramTable" class="text-sm divide-y divide-gray-100"></tbody>
                </table>
            </div>
        </div>



        <!-- ========================================================================= -->
        <!-- INTEGRATED BUS STOP & ROUTE MANAGEMENT (SPLIT SCREEN LAYOUT 40% / 60%)     -->
        <!-- ========================================================================= -->
        <div id="route" class="page">
            <!-- Top Header Bar -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4">
                <div>
                    <h2 class="text-xl font-black text-gray-800 flex items-center gap-2">
                        <i class="fas fa-map-marked-alt text-pink-600"></i>
                        จัดการจุดจอดและเส้นทางเดินรถไฟฟ้า
                    </h2>
                    <p class="text-xs text-gray-500 mt-0.5">ระบบจัดการพิกัดจุดจอด (Stop Stations) และผูกเส้นทางเดินรถ</p>
                </div>
                <div class="flex items-center gap-2">
                    <span class="bg-pink-50 text-pink-700 border border-pink-200 text-xs px-3 py-1.5 rounded-xl font-bold flex items-center gap-1.5 shadow-sm">
                        <i class="fas fa-map-pin text-pink-500"></i> จุดจอด: <span id="intStopBadgeCount" class="text-pink-600">0</span> จุด
                    </span>
                    <span class="bg-blue-50 text-blue-700 border border-blue-200 text-xs px-3 py-1.5 rounded-xl font-bold flex items-center gap-1.5 shadow-sm">
                        <i class="fas fa-route text-blue-500"></i> เส้นทาง: <span id="intRouteBadgeCount" class="text-blue-600">0</span> สาย
                    </span>
                </div>
            </div>

            <!-- Split Screen Container -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 h-[calc(100vh-130px)] min-h-[640px]">
                
                <!-- ========================================== -->
                <!-- LEFT PANEL: 40% (Control Panel with Tabs)  -->
                <!-- ========================================== -->
                <div class="lg:col-span-5 flex flex-col h-full bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    
                    <!-- Tabs Navigation Bar -->
                    <div class="flex border-b border-gray-100 bg-gray-50/70 p-1.5 gap-1.5">
                        <button onclick="switchIntegratedTab('stops')" id="int-tab-btn-stops"
                            class="flex-1 py-2.5 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 bg-pink-600 text-white shadow-sm">
                            1. จัดการจุดจอด
                        </button>
                        <button onclick="switchIntegratedTab('routes')" id="int-tab-btn-routes"
                            class="flex-1 py-2.5 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 text-gray-600 hover:bg-gray-200/60">
                            2. จัดการเส้นทาง
                        </button>
                    </div>

                    <!-- Panel Content Area (Scrollable) -->
                    <div class="flex-1 overflow-y-auto p-4 space-y-4">

                        <!-- ========================================== -->
                        <!-- TAB 1: จัดการจุดจอด (STOPS MANAGEMENT)     -->
                        <!-- ========================================== -->
                        <div id="int-tab-content-stops" class="space-y-4">
                            
                            <!-- Stop Form Box -->
                            <div class="bg-gray-50/80 border border-gray-200/80 rounded-2xl p-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2" id="stopFormHeading">
                                        <i class="fas fa-plus-circle text-pink-600"></i> เพิ่มจุดจอดใหม่
                                    </h3>
                                    <button type="button" onclick="resetStopForm()" id="btnResetStopForm" class="text-xs text-gray-400 hover:text-pink-600 font-semibold hidden">
                                        <i class="fas fa-undo mr-1"></i> ล้างฟอร์ม
                                    </button>
                                </div>
                                <input type="hidden" id="intStopEditIndex" value="-1">

                                <!-- Stop Name Input -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">
                                        ชื่อจุดจอดรถไฟฟ้า <span class="text-pink-600">*</span>
                                    </label>
                                    <input type="text" id="intStopName"
                                        class="w-full bg-white border border-gray-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-pink-500 focus:border-pink-500 outline-none transition"
                                        placeholder="เช่น จุดจอด 1 หน้าตึกอธิการบดี">
                                </div>

                                <!-- Latitude & Longitude Inputs (Readonly / Click on Map) -->
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1">
                                            ละติจูด (Lat) <span class="text-pink-600">*</span>
                                        </label>
                                        <input type="text" id="intStopLat" readonly
                                            class="w-full bg-gray-100 border border-gray-200 rounded-xl px-3 py-2 text-xs font-mono text-gray-600 outline-none cursor-not-allowed"
                                            placeholder="คลิกเลือกบนแผนที่">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1">
                                            ลองจิจูด (Lng) <span class="text-pink-600">*</span>
                                        </label>
                                        <input type="text" id="intStopLng" readonly
                                            class="w-full bg-gray-100 border border-gray-200 rounded-xl px-3 py-2 text-xs font-mono text-gray-600 outline-none cursor-not-allowed"
                                            placeholder="คลิกเลือกบนแผนที่">
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <button type="button" onclick="saveIntegratedStop()" id="btnSaveStop"
                                    class="w-full bg-pink-600 hover:bg-pink-700 text-white font-bold py-2.5 rounded-xl text-xs shadow-md transition flex items-center justify-center gap-2">
                                    <i class="fas fa-save"></i> บันทึก
                                </button>
                            </div>

                            <!-- Existing Stops List -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between px-1">
                                    <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider">รายการจุดจอดที่มีในระบบ</h4>
                                    <span class="text-[10px] text-gray-400 font-medium">คลิกเพื่อดูหมุดบนแผนที่</span>
                                </div>
                                <div id="intStopsListContainer" class="space-y-2 max-h-[240px] overflow-y-auto pr-1">
                                    <!-- Rendered dynamically via JS -->
                                </div>
                            </div>
                        </div>

                        <!-- ========================================== -->
                        <!-- TAB 2: จัดการเส้นทาง (ROUTE BUILDER)        -->
                        <!-- ========================================== -->
                        <div id="int-tab-content-routes" class="space-y-4 hidden">
                            
                            <!-- Route Form Box -->
                            <div class="bg-gray-50/80 border border-gray-200/80 rounded-2xl p-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2" id="routeFormHeading">
                                        <i class="fas fa-route text-pink-600"></i> สร้างเส้นทางใหม่
                                    </h3>
                                    <button type="button" onclick="resetRouteForm()" id="btnResetRouteForm" class="text-xs text-gray-400 hover:text-pink-600 font-semibold hidden">
                                        <i class="fas fa-undo mr-1"></i> ล้างฟอร์ม
                                    </button>
                                </div>
                                <input type="hidden" id="intRouteEditIndex" value="-1">

                                <!-- Route Code -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">
                                        รหัส / ชื่อเส้นทาง <span class="text-pink-600">*</span>
                                    </label>
                                    <input type="text" id="intRouteCode"
                                        class="w-full bg-white border border-gray-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-pink-500 outline-none transition"
                                        placeholder="เช่น LINE-A (รอบเมือง)">
                                    <input type="hidden" id="intRouteColor" value="#E91E63">
                                </div>

                                <!-- Route Stops Selector & Ordering -->
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-xs font-bold text-gray-700">
                                            เลือกและจัดลำดับจุดจอดในเส้นทาง
                                        </label>
                                        <span class="text-[10px] text-pink-600 font-bold" id="intSelectedStopsBadge">เลือก 0 จุด</span>
                                    </div>
                                    <p class="text-[11px] text-gray-400 mb-2">ติ๊กถูกเพื่อรวมเข้าเส้นทาง และกดลูกศร ⬆️ ⬇️ เพื่อเรียงลำดับ</p>
                                    
                                    <div id="intRouteStopsChecklist" class="bg-white border border-gray-200 rounded-xl p-2 max-h-48 overflow-y-auto space-y-1.5">
                                        <!-- Rendered dynamically via JS -->
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <button type="button" onclick="saveIntegratedRoute()" id="btnSaveRoute"
                                    class="w-full bg-pink-600 hover:bg-pink-700 text-white font-bold py-2.5 rounded-xl text-xs shadow-md transition flex items-center justify-center gap-2">
                                    <i class="fas fa-save"></i> บันทึก
                                </button>
                            </div>

                            <!-- Existing Routes List -->
                            <div class="space-y-2">
                                <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider px-1">เส้นทางที่มีในระบบ</h4>
                                <div id="intRoutesListContainer" class="space-y-2 max-h-[220px] overflow-y-auto pr-1">
                                    <!-- Rendered dynamically via JS -->
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- ========================================== -->
                <!-- RIGHT PANEL: 60% (Interactive Leaflet Map) -->
                <!-- ========================================== -->
                <div class="lg:col-span-7 flex flex-col h-full bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative">
                    
                    <!-- Map Top Instruction Bar -->
                    <div class="bg-gray-900/90 text-white px-4 py-2 text-xs flex flex-wrap items-center justify-between z-20 shadow-md gap-2">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-info-circle text-pink-400 text-sm"></i>
                            <span id="intMapInstructionText" class="font-medium">
                                📍 คลิกที่ใดก็ได้บนแผนที่ เพื่อดึงพิกัดใส่ช่อง Lat/Lng อัตโนมัติ
                            </span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div id="intRouteDrawControls" class="hidden flex items-center gap-1.5">
                                <button type="button" onclick="undoRouteWaypoint()" title="ย้อนกลับ 1 จุด"
                                    class="bg-amber-500/80 hover:bg-amber-500 text-white px-2.5 py-1 rounded-lg transition text-[11px] font-semibold flex items-center gap-1">
                                    <i class="fas fa-undo"></i> ย้อนกลับ
                                </button>
                                <button type="button" onclick="clearRouteWaypoints()" title="ล้างเส้นทางวาด"
                                    class="bg-red-500/80 hover:bg-red-500 text-white px-2.5 py-1 rounded-lg transition text-[11px] font-semibold flex items-center gap-1">
                                    <i class="fas fa-trash-alt"></i> ล้างเส้นวาด
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Leaflet Map Canvas -->
                    <div id="integratedMap" class="w-full z-10 rounded-b-2xl" style="min-height: 580px; height: 580px; width: 100%;"></div>

                    <!-- Floating Notification Toast -->
                    <div id="intMapNotification" class="absolute bottom-4 left-4 right-4 sm:left-auto sm:right-4 z-30 hidden transition-all">
                        <div class="bg-gray-900/95 text-white px-4 py-3 rounded-xl shadow-2xl border border-pink-500/40 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-pink-500/20 text-pink-400 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-check text-sm"></i>
                            </div>
                            <div>
                                <h5 class="text-xs font-bold text-pink-300" id="intToastTitle">ดึงพิกัดสำเร็จ!</h5>
                                <p class="text-[11px] text-gray-200 font-mono mt-0.5" id="intToastText">Lat: 6.5499, Lng: 101.2912</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>

        <!-- Hidden compat section for legacy route-management page -->
        <div id="route-management" class="hidden">
            <table class="hidden"><tbody id="routeManagementTable"></tbody></table>
        </div>


        <!-- Route Builder Modal -->
        <div id="routeModal" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden p-4 z-50 transition-opacity">
            <div class="bg-white p-6 rounded-xl shadow-xl w-full max-w-5xl max-h-[95vh] overflow-y-auto transform transition-all">
                <div class="flex justify-between items-center mb-4 border-b pb-3">
                    <h3 id="routeModalTitle" class="text-xl font-bold text-gray-800">เครื่องมือสร้างเส้นทางเดินรถ (Route Builder)</h3>
                    <button onclick="closeRouteModal()" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                        <i class="fas fa-times text-lg"></i>
                    </button>
                </div>
                <input type="hidden" id="editRouteIndex" value="-1">

                <div class="grid grid-cols-1 lg:grid-cols-12 gap-6">
                    <!-- Left column: form and stops checklist -->
                    <div class="lg:col-span-4 space-y-4">
                        <div>
                            <label class="block text-xs font-semibold text-gray-500 mb-1">รหัสเส้นทาง <span class="text-red-500">*</span></label>
                            <input type="text" id="modalRouteCode" class="w-full border border-gray-300 p-2 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none text-sm" placeholder="เช่น LINE-A">
                        </div>
                        <div class="hidden">
                            <label class="block text-xs font-semibold text-gray-500 mb-1">ชื่อเส้นทางเดินรถ <span class="text-red-500">*</span></label>
                            <input type="text" id="modalRouteName" class="w-full border border-gray-300 p-2 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none text-sm" placeholder="เช่น สายสีชมพูรอบใน" value="-">
                        </div>
                        <div class="hidden">
                            <label class="block text-xs font-semibold text-gray-500 mb-1">สีประจำเส้นทาง (สำหรับเส้นแนวถนน) <span class="text-red-500">*</span></label>
                            <div class="flex items-center gap-2">
                                <input type="color" id="modalRouteColor" class="w-12 h-9 border border-gray-300 rounded cursor-pointer" value="#ec4899" onchange="updateDrawnPolylineColor(this.value)">
                                <span class="text-xs text-gray-400">คลิกเพื่อเลือกสีแสดงผลของเส้นทาง</span>
                            </div>
                        </div>
                        
                        <!-- Stops & Ordering Section -->
                        <div class="border-t pt-4">
                            <h4 class="text-xs font-bold text-gray-700 mb-2 flex items-center justify-between">
                                <span>ผูกจุดจอดและจัดลำดับการเดินรถ <span id="stopsCountBadge" class="bg-pink-100 text-pink-600 px-1.5 py-0.5 rounded-full text-[10px] ml-1">0 จุด</span></span>
                                <span class="text-[10px] text-pink-500 font-semibold cursor-pointer hover:text-pink-700" onclick="refreshBuilderStops()">(🔄 รีเฟรชจุดจอด)</span>
                            </h4>
                            <div class="bg-gray-50 border border-gray-200 rounded-lg p-3 max-h-56 overflow-y-auto space-y-2" id="routeStopsChecklist">
                                <!-- Loaded dynamically from stops -->
                            </div>
                        </div>

                        <!-- Route Details Section -->
                        <div class="border-t pt-4">
                            <label class="block text-xs font-semibold text-gray-500 mb-1">รายละเอียดเส้นทาง</label>
                            <textarea id="modalRouteDetails" rows="2" class="w-full border border-gray-300 p-2 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none text-sm" placeholder="คำอธิบายสั้นๆ ของเส้นทาง..."></textarea>
                        </div>
                    </div>

                    <!-- Right column: Leaflet Map -->
                    <div class="lg:col-span-8 flex flex-col space-y-2">
                        <div class="flex flex-col sm:flex-row sm:items-center justify-between text-xs text-gray-600 bg-amber-50 border border-amber-100 p-3 rounded-lg gap-3">
                            <div class="flex items-start gap-2 flex-1">
                                <i class="fas fa-info-circle text-amber-500 mt-0.5 flex-shrink-0"></i>
                                <span id="mapModeInstruction" class="leading-relaxed">คลิกบนแผนที่เพื่อวาดแนวถนนจริง</span>
                            </div>
                            <div class="flex gap-2 flex-shrink-0">
                                <button type="button" onclick="undoLastPolylinePoint()" class="bg-white border border-gray-300 px-3 py-1.5 rounded-lg text-[11px] font-semibold hover:bg-gray-50 transition flex items-center"><i class="fas fa-undo mr-1"></i>ย้อนกลับ</button>
                                <button type="button" onclick="clearPolyline()" class="bg-red-50 text-red-600 border border-red-100 px-3 py-1.5 rounded-lg text-[11px] font-semibold hover:bg-red-100 transition flex items-center"><i class="fas fa-trash-alt mr-1"></i>ล้างเส้นวาด</button>
                            </div>
                        </div>
                        <div id="routeMap" class="h-[380px] w-full rounded-xl border border-gray-300 z-10"></div>
                        <p class="text-[10px] text-gray-400 italic">คำแนะนำ: โปรดนำหมุดสีชมพูมาทับจุดจอดจริงเพื่อระบุจุดจอดบนเส้นทาง</p>
                    </div>
                </div>

                <div class="flex justify-end gap-3 mt-6 border-t pt-4">
                    <button onclick="closeRouteModal()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 text-sm font-medium transition">ยกเลิก</button>
                    <button onclick="saveRouteData()" class="bg-pink-600 text-white px-5 py-2 rounded-lg hover:bg-pink-700 text-sm font-semibold transition shadow-md">บันทึก</button>
                </div>
            </div>
        </div>


        <div id="users" class="page">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">จัดการข้อมูลผู้ใช้งาน</h2>
                    <p class="text-gray-500 text-sm mt-1">เพิ่ม แก้ไข ลบ กำหนดสิทธิ์ และนำเข้าข้อมูลบัญชีผู้ใช้งาน</p>
                </div>
                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <div class="relative flex-1 sm:w-64">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" id="userSearchInput" onkeyup="searchUsers()" 
                            class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none text-sm" 
                            placeholder="ค้นหาด้วยชื่อ อีเมล หรือสิทธิ์...">
                    </div>
                    <button onclick="openImportUserModal()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg transition text-sm flex items-center justify-center gap-2 shadow-sm">
                        <i class="fas fa-file-import"></i> นำเข้าข้อมูล (Import)
                    </button>
                    <button onclick="openUserModal()" class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700 transition text-sm flex items-center justify-center gap-2 shadow-sm">
                        <i class="fas fa-user-plus"></i> เพิ่ม
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden overflow-x-auto">
                <table class="w-full text-left min-w-[900px]">
                    <thead class="bg-gray-50 text-gray-600 text-sm">
                        <tr>
                            <th class="p-4">รหัสประจำตัว</th>
                            <th class="p-4">ชื่อ-นามสกุล</th>
                            <th class="p-4">Username</th>
                            <th class="p-4">อีเมล</th>
                            <th class="p-4">เบอร์โทรศัพท์</th>
                            <th class="p-4">สิทธิ์</th>
                            <th class="p-4">สถานะ</th>
                            <th class="p-4 text-center">การจัดการข้อมูล</th>
                        </tr>
                    </thead>
                    <tbody id="userTable" class="text-sm"></tbody>
                </table>
            </div>
        </div>

        <div id="role" class="page">
            <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">รายงานสิทธิ์การใช้งาน</h2>
                    <p class="text-gray-500 text-sm mt-1">ตารางแสดงขอบเขตสิทธิ์การเข้าถึงฟังก์ชันของแต่ละกลุ่มผู้ใช้งานในระบบ</p>
                </div>
                
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden overflow-x-auto">
                <table class="w-full text-left min-w-[700px]">
                    <thead>
                        <tr class="bg-gradient-to-r from-pink-50 to-pink-100 text-gray-700 text-sm">
                            <th class="p-4 font-semibold w-56 border-b border-gray-200">เมนู / ฟังก์ชัน</th>
                            <th class="p-4 text-center font-semibold border-b border-gray-200">
                                <span class="inline-flex items-center gap-1.5 bg-red-100 text-red-700 px-3 py-1 rounded-full text-xs font-bold">
                                    <i class="fas fa-shield-alt"></i> ผู้ดูแลระบบ
                                </span>
                            </th>
                            <th class="p-4 text-center font-semibold border-b border-gray-200">
                                <span class="inline-flex items-center gap-1.5 bg-blue-100 text-blue-700 px-3 py-1 rounded-full text-xs font-bold">
                                    <i class="fas fa-graduation-cap"></i> นักศึกษา
                                </span>
                            </th>
                            <th class="p-4 text-center font-semibold border-b border-gray-200">
                                <span class="inline-flex items-center gap-1.5 bg-amber-100 text-amber-700 px-3 py-1 rounded-full text-xs font-bold">
                                    <i class="fas fa-car"></i> พนักงานขับรถ
                                </span>
                            </th>
                            <th class="p-4 text-center font-semibold border-b border-gray-200">
                                <span class="inline-flex items-center gap-1.5 bg-purple-100 text-purple-700 px-3 py-1 rounded-full text-xs font-bold">
                                    <i class="fas fa-user-tie"></i> ผู้บริหาร
                                </span>
                            </th>
                            <th class="p-4 text-center font-semibold border-b border-gray-200">
                                <span class="inline-flex items-center gap-1.5 bg-green-100 text-green-700 px-3 py-1 rounded-full text-xs font-bold">
                                    <i class="fas fa-tools"></i> ช่างซ่อม
                                </span>
                            </th>
                        </tr>
                    </thead>
                    <tbody id="rolePermissionTable" class="text-sm divide-y divide-gray-100">
                        <!-- Rows rendered by JavaScript -->
                    </tbody>
                </table>
            </div>

            
        </div>



    </main>
</div>

<!-- Modal นำเข้าข้อมูลผู้ใช้งาน (Excel / CSV Import) -->
<div id="importUserModal" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden p-4 z-50 transition-opacity">
    <div class="bg-white p-6 rounded-2xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto transform transition-all">
        <div class="flex justify-between items-center mb-4 border-b pb-3">
            <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-file-import text-emerald-600"></i>
                <span>นำเข้าข้อมูลผู้ใช้งาน</span>
            </h3>
            <button onclick="closeImportUserModal()" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        
        <div class="space-y-4">
            <!-- ส่วนดาวน์โหลด Template -->
            <div class="bg-emerald-50 border border-emerald-100 p-4 rounded-xl flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <div class="flex-1">
                    <h4 class="font-bold text-sm text-emerald-800">ดาวน์โหลดไฟล์เทมเพลต (Template)</h4>
                    <p class="text-xs text-emerald-600 mt-1">ดาวน์โหลดรูปแบบตารางข้อมูลมาตรฐานสำหรับเปิดใช้ใน Microsoft Excel</p>
                </div>
                <button onclick="downloadUserTemplate()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-xs font-bold flex items-center gap-2 shadow-sm transition shrink-0">
                    <i class="fas fa-download"></i> ดาวน์โหลด Template (.csv)
                </button>
            </div>
            
            <!-- ส่วนการเลือกและลากอัปโหลดไฟล์ -->
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-2">เลือกไฟล์ Excel (.xlsx, .xls) หรือ CSV (.csv)</label>
                <div id="dropZone" class="border-2 border-dashed border-gray-300 rounded-xl p-8 text-center hover:border-pink-500 hover:bg-pink-50/10 cursor-pointer transition relative">
                    <input type="file" id="importFile" accept=".xlsx,.xls,.csv" onchange="handleImportFile(event)" class="absolute inset-0 opacity-0 cursor-pointer">
                    <i class="fas fa-cloud-upload-alt text-4xl text-gray-400 mb-2"></i>
                    <p class="text-sm font-semibold text-gray-600">ลากไฟล์มาวางที่นี่ หรือคลิกเพื่อเลือกไฟล์</p>
                    <p class="text-xs text-gray-400 mt-1">ไฟล์ XLSX, XLS หรือ CSV ขนาดไม่เกิน 5MB</p>
                </div>
                <div id="selectedFileName" class="hidden mt-3 p-3 bg-gray-50 rounded-xl text-xs font-bold text-slate-700 flex items-center justify-between border border-gray-200">
                    <span class="flex items-center gap-2">
                        <i class="fas fa-file-excel text-emerald-600 text-sm animate-bounce"></i> 
                        <span id="fileNameText"></span>
                    </span>
                    <button onclick="clearSelectedFile()" class="text-red-500 hover:text-red-700 font-medium">
                        <i class="fas fa-trash-alt mr-1"></i>ลบไฟล์
                    </button>
                </div>
            </div>
            
            <!-- ส่วนแสดงพรีวิวและการตรวจสอบข้อมูล -->
            <div id="validationResultArea" class="hidden space-y-4">
                <!-- รายการแถวที่มีข้อผิดพลาด -->
                <div id="importErrorsContainer" class="hidden bg-red-50 border border-red-200 p-4 rounded-xl shadow-sm">
                    <h4 class="font-bold text-sm text-red-800 flex items-center gap-1.5 mb-2">
                        <i class="fas fa-exclamation-circle text-red-500"></i>
                        <span>พบข้อมูลไม่ถูกต้อง (<span id="errorCountText">0</span> รายการ)</span>
                    </h4>
                    <p class="text-[11px] text-red-600 mb-3">กรุณาแก้ไขปัญหาเหล่านี้ในไฟล์ของคุณก่อนนำเข้า หรือกดยืนยันการนำเข้าเฉพาะรายการที่ถูกต้องด้านล่าง</p>
                    <div class="max-h-40 overflow-y-auto custom-scrollbar border rounded-lg bg-white">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-red-100 text-red-700 font-bold sticky top-0">
                                <tr>
                                    <th class="p-2 w-16 text-center">แถวที่</th>
                                    <th class="p-2 w-48">ข้อมูลคอลัมน์</th>
                                    <th class="p-2">รายละเอียดความผิดพลาด</th>
                                </tr>
                            </thead>
                            <tbody id="errorRowsList" class="divide-y divide-gray-100 text-gray-700">
                                <!-- Rendered dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
                
                <!-- รายการแถวที่ถูกต้อง -->
                <div id="importValidContainer" class="hidden bg-emerald-50 border border-emerald-200 p-4 rounded-xl shadow-sm">
                    <h4 class="font-bold text-sm text-emerald-800 flex items-center gap-1.5 mb-2">
                        <i class="fas fa-check-circle text-emerald-500 animate-pulse"></i>
                        <span>ข้อมูลที่ถูกต้องและพร้อมนำเข้า (<span id="validCountText">0</span> รายการ)</span>
                    </h4>
                    <div class="max-h-48 overflow-y-auto custom-scrollbar border rounded-lg bg-white">
                        <table class="w-full text-left text-xs">
                            <thead class="bg-emerald-100 text-emerald-700 font-bold sticky top-0">
                                <tr>
                                    <th class="p-2 pl-3 w-10 text-center">
                                        <input type="checkbox" id="selectAllValid" onclick="toggleAllValidImport()" checked class="rounded text-emerald-600 focus:ring-emerald-500 cursor-pointer w-4 h-4">
                                    </th>
                                    <th class="p-2">ชื่อ-นามสกุล</th>
                                    <th class="p-2">อีเมล มรย.</th>
                                    <th class="p-2">สิทธิ์</th>
                                    <th class="p-2 pr-3">สถานะสิทธิ์</th>
                                </tr>
                            </thead>
                            <tbody id="validRowsList" class="divide-y divide-gray-100 text-gray-700">
                                <!-- Rendered dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
        
        <div class="flex justify-end gap-3 mt-6 border-t pt-4">
            <button onclick="closeImportUserModal()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-xl hover:bg-gray-300 text-sm font-semibold transition">ยกเลิก</button>
            <button id="btnConfirmImport" onclick="confirmImportUsers()" disabled class="bg-emerald-600 hover:bg-emerald-700 text-white px-5 py-2 rounded-xl text-sm font-bold shadow-md transition disabled:bg-slate-300 disabled:shadow-none disabled:cursor-not-allowed">
                <i class="fas fa-check-circle mr-1"></i>ยืนยัน
            </button>
        </div>
    </div>
</div>

<div id="userModal" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden p-4 z-50 transition opacity">
    <div class="bg-white p-6 rounded-xl shadow-xl w-full max-w-md max-h-[90vh] overflow-y-auto custom-scrollbar transform transition-all">
        <h3 id="userModalTitle" class="text-xl font-bold mb-4 text-gray-800">เพิ่มผู้ใช้งานใหม่</h3>
        <input type="hidden" id="editUserIndex">
        <div class="space-y-4">
            <div class="grid grid-cols-12 gap-3">
                <div class="col-span-3">
                    <label class="block text-[11px] font-semibold text-gray-500 mb-1">คำนำหน้า <span class="text-red-500">*</span></label>
                    <input type="text" id="modalUserPrefix" class="w-full border border-gray-300 p-2 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none text-xs" placeholder="เช่น นาย">
                </div>
                <div class="col-span-4">
                    <label class="block text-[11px] font-semibold text-gray-500 mb-1">ชื่อ <span class="text-red-500">*</span></label>
                    <input type="text" id="modalUserFirstName" class="w-full border border-gray-300 p-2 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none text-xs" placeholder="เช่น สมชาย">
                </div>
                <div class="col-span-5">
                    <label class="block text-[11px] font-semibold text-gray-500 mb-1">นามสกุล <span class="text-red-500">*</span></label>
                    <input type="text" id="modalUserLastName" class="w-full border border-gray-300 p-2 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none text-xs" placeholder="เช่น ใจดี">
                </div>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">Username <span class="text-red-500">*</span></label>
                <input type="text" id="modalUserUsername" class="w-full border border-gray-300 p-2 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none text-sm" placeholder="Username">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">รหัสประจำตัว <span class="text-red-500">*</span></label>
                <input type="text" id="modalUserEmployeeId" class="w-full border border-gray-300 p-2 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none text-sm" placeholder="รหัสประจำตัว">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">อีเมล <span class="text-red-500">*</span></label>
                <input type="email" id="modalUserEmail" class="w-full border border-gray-300 p-2 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none text-sm" placeholder="เช่น admin@yru.ac.th">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">เบอร์โทรศัพท์ <span class="text-red-500">*</span></label>
                <input type="tel" id="modalUserPhone" class="w-full border border-gray-300 p-2 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none text-sm" placeholder="เบอร์โทรศัพท์">
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">สิทธิ์ <span class="text-red-500">*</span></label>
                <select id="modalUserRole" class="w-full border border-gray-300 p-2 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none text-sm bg-white">
                    <option value="admin">ผู้ดูแลระบบ</option>
                    <option value="student">นักศึกษา</option>
                    <option value="staff">อาจารย์/บุคลากร</option>
                    <option value="driver">พนักงานขับรถ</option>
                    <option value="executive">ผู้บริหาร</option>
                    <option value="mechanic">ช่างซ่อม</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">สถานะ <span class="text-red-500">*</span></label>
                <select id="modalUserStatus" class="w-full border border-gray-300 p-2 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none text-sm bg-white">
                    <option value="ปกติ">ปกติ</option>
                    <option value="ระงับการใช้งาน">ระงับการใช้งาน</option>
                </select>
            </div>
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">หมายเหตุ</label>
                <textarea id="modalUserNote" class="w-full border border-gray-300 p-2 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none text-sm" rows="2" placeholder="หมายเหตุเพิ่มเติม..."></textarea>
            </div>
        </div>
        <div class="flex justify-end gap-3 mt-6">
            <button onclick="closeUserModal()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 text-sm font-medium transition">ยกเลิก</button>
            <button onclick="saveUserData()" class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700 text-sm font-medium transition">บันทึก</button>
        </div>
    </div>
</div>

<div id="tramModal" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden p-4 z-50 transition-opacity">
    <div class="bg-white p-6 rounded-xl shadow-xl w-full max-w-2xl max-h-[90vh] overflow-y-auto transform transition-all">
        <div class="flex justify-between items-center mb-4 border-b pb-3">
            <h3 id="tramModalTitle" class="text-xl font-bold text-gray-800">เพิ่มรถไฟฟ้า</h3>
            <button onclick="closeTramModal()" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <input type="hidden" id="editTramId">
        
        <div class="space-y-6">
            <!-- หมวด 1: ข้อมูลทั่วไป -->
            <div>
                <h4 class="text-sm font-semibold text-pink-600 border-l-4 border-pink-500 pl-2 mb-3">หมวด 1: ข้อมูลทั่วไป</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">รหัสรถไฟฟ้า <span class="text-red-500">*</span></label>
                        <input type="text" id="modalTramId" class="w-full border border-gray-300 p-2 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none text-sm" placeholder="เช่น EV-03, TRAM002">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">ชื่อเรียก/หมายเลขคัน <span class="text-red-500">*</span></label>
                        <input type="text" id="modalTramName" class="w-full border border-gray-300 p-2 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none text-sm" placeholder="เช่น รถไฟฟ้าคันที่ 3">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">ทะเบียนรถ <span class="text-red-500">*</span></label>
                        <input type="text" id="modalTramPlate" class="w-full border border-gray-300 p-2 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none text-sm" placeholder="เช่น กข 1234 ยะลา">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">ความจุผู้โดยสาร (ที่นั่ง) <span class="text-red-500">*</span></label>
                        <div class="relative">
                            <input type="number" id="modalTramCapSit" min="0" max="10" class="w-full border border-gray-300 p-2 pr-8 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none text-sm" placeholder="10">
                            <span class="absolute right-2.5 top-2 text-xs text-gray-400">นั่ง</span>
                        </div>
                        <input type="hidden" id="modalTramCapStand" value="0">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-500 mb-1">รูปภาพตัวรถ</label>
                        <div class="flex items-center gap-4">
                            <div class="relative w-20 h-20 rounded-lg bg-pink-50 border border-pink-100 flex items-center justify-center text-pink-500 flex-shrink-0 overflow-hidden">
                                <img id="modalTramImgPreview" src="" class="hidden w-full h-full object-cover">
                                <span id="modalTramImgPlaceholder"><i class="fas fa-bus text-2xl"></i></span>
                            </div>
                            <div class="flex-1">
                                <input type="file" id="modalTramImageInput" accept="image/*" onchange="handleImageUpload(event)" class="hidden">
                                <button type="button" onclick="document.getElementById('modalTramImageInput').click()" class="bg-white border border-gray-300 text-gray-700 px-3 py-2 rounded-lg hover:bg-gray-50 text-xs font-semibold flex items-center gap-1.5 shadow-sm transition">
                                    <i class="fas fa-upload text-gray-400"></i> คลิกเพื่ออัปโหลดรูปภาพรถไฟฟ้า
                                </button>
                                <span class="text-[10px] text-gray-400 block mt-1">รองรับไฟล์รูปภาพ PNG, JPG, WebP ขนาดไม่เกิน 2MB</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- หมวด 2: สถานะและการปฏิบัติงาน -->
            <div>
                <h4 class="text-sm font-semibold text-pink-600 border-l-4 border-pink-500 pl-2 mb-3">หมวด 2: สถานะและการปฏิบัติงาน</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">สถานะการใช้งาน <span class="text-red-500">*</span></label>
                        <select id="modalTramStatus" class="w-full border border-gray-300 p-2 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none text-sm bg-white">
                            <option value="พร้อมใช้งาน">พร้อมใช้งาน</option>
                            <option value="รถขัดข้อง">รถขัดข้อง</option>
                            <option value="ระงับการใช้งาน">ระงับการใช้งาน</option>
                        </select>
                    </div>
                    <select id="modalTramRoute" class="hidden">
                        <!-- Options populated dynamically -->
                    </select>
                    <div class="relative">
                        <label class="block text-xs font-medium text-gray-500 mb-1">พนักงานขับรถประจำคัน <span class="text-red-500">*</span></label>
                        <div id="driverDropdownTrigger" onclick="toggleDriverDropdown(event)" class="w-full border border-gray-300 p-2 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none text-sm bg-white cursor-pointer flex justify-between items-center select-none">
                            <span id="selectedDriverText" class="text-gray-400 font-medium">-- ไม่ระบุ / ยังไม่มอบหมาย --</span>
                            <i class="fas fa-chevron-down text-gray-400 text-xs"></i>
                        </div>
                        <input type="hidden" id="modalTramDriver" value="">
                        <input type="hidden" id="modalTramDriverId" value="">
                        <div id="driverDropdownMenu" class="hidden absolute z-50 left-0 right-0 mt-1 bg-white border border-gray-200 rounded-lg shadow-lg p-2 space-y-2 max-h-64 flex flex-col">
                            <div class="relative flex-shrink-0">
                                <i class="fas fa-search absolute left-2.5 top-2.5 text-gray-400 text-xs"></i>
                                <input type="text" id="driverSearchInput" oninput="filterDrivers()" class="w-full pl-8 pr-3 py-1.5 border border-gray-200 rounded-md outline-none text-xs focus:ring-1 focus:ring-pink-400 placeholder-gray-300" placeholder="พิมพ์เพื่อค้นหาชื่อคนขับ...">
                            </div>
                            <div id="driverOptionsList" class="overflow-y-auto space-y-0.5 text-xs text-gray-700 max-h-40 flex-1">
                                <!-- Loaded dynamically -->
                            </div>
                        </div>
                    </div>
                    <input type="hidden" id="modalTramBattery" value="100">
                </div>
            </div>

            <!-- หมวด 3: ข้อมูลทรัพย์สิน -->
            <div>
                <h4 class="text-sm font-semibold text-pink-600 border-l-4 border-pink-500 pl-2 mb-3">หมวด 3: ข้อมูลทรัพย์สิน</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">วันที่ซื้อ/รับมอบ</label>
                        <input type="date" lang="en-GB" id="modalTramPurchaseDate" class="w-full border border-gray-300 p-2 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none text-sm bg-white">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-500 mb-1">ระยะเวลารับประกัน (Warranty)</label>
                        <input type="text" id="modalTramWarranty" class="w-full border border-gray-300 p-2 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none text-sm" placeholder="เช่น 5 ปี (สิ้นสุด 12 มีนาคม 2573)">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-xs font-medium text-gray-500 mb-1">ข้อมูลติดต่อผู้จัดจำหน่าย (Supplier)</label>
                        <input type="text" id="modalTramSupplier" class="w-full border border-gray-300 p-2 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none text-sm" placeholder="เช่น บริษัท ยะลายานยนต์ อีวี จำกัด (โทร. 073-123456)">
                    </div>
                </div>
            </div>

            <!-- หมวด 4: ข้อมูลอะไหล่และชิ้นส่วนส่งซ่อม -->
            <div>
                <h4 class="text-sm font-semibold text-pink-600 border-l-4 border-pink-500 pl-2 mb-3">หมวด 4: ข้อมูลอะไหล่และรายการชิ้นส่วนส่งซ่อม</h4>
                <div class="space-y-3 bg-gray-50/70 p-3.5 rounded-2xl border border-gray-200">
                    <p class="text-xs font-semibold text-gray-700 mb-1">พิมพ์ระบุรายละเอียดอะไหล่ชิ้นส่วนที่ต้องการส่งซ่อมบำรุง:</p>
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
                        <!-- 1. แบตเตอรี่หลัก -->
                        <div class="bg-white p-2.5 rounded-xl border border-gray-200 shadow-sm flex flex-col gap-1.5">
                            <span class="font-bold text-gray-800">🔋 แบตเตอรี่หลัก</span>
                            <input type="text" id="input_part_battery" placeholder="ระบุยี่ห้อ/รุ่น เช่น ยี่ห้อ CATL รุ่น LiFePO4 72V 200Ah"
                                class="w-full bg-gray-50 border border-gray-200 px-2.5 py-1.5 rounded-lg text-xs outline-none focus:bg-white focus:border-pink-400 transition">
                        </div>

                        <!-- 2. มอเตอร์ขับเคลื่อน -->
                        <div class="bg-white p-2.5 rounded-xl border border-gray-200 shadow-sm flex flex-col gap-1.5">
                            <span class="font-bold text-gray-800">⚙️ มอเตอร์ขับเคลื่อน</span>
                            <input type="text" id="input_part_motor" placeholder="ระบุยี่ห้อ/รุ่น เช่น ยี่ห้อ QS Motor 72V 5000W BLDC"
                                class="w-full bg-gray-50 border border-gray-200 px-2.5 py-1.5 rounded-lg text-xs outline-none focus:bg-white focus:border-pink-400 transition">
                        </div>

                        <!-- 3. ระบบเบรก/ผ้าเบรก -->
                        <div class="bg-white p-2.5 rounded-xl border border-gray-200 shadow-sm flex flex-col gap-1.5">
                            <span class="font-bold text-gray-800">🛑 ระบบเบรก/ผ้าเบรก</span>
                            <input type="text" id="input_part_brake" placeholder="ระบุยี่ห้อ/รุ่น เช่น ยี่ห้อ Akebono รุ่น Ceramic Heavy-Duty"
                                class="w-full bg-gray-50 border border-gray-200 px-2.5 py-1.5 rounded-lg text-xs outline-none focus:bg-white focus:border-pink-400 transition">
                        </div>

                        <!-- 4. ยางรถไฟฟ้า -->
                        <div class="bg-white p-2.5 rounded-xl border border-gray-200 shadow-sm flex flex-col gap-1.5">
                            <span class="font-bold text-gray-800">🛞 ยางรถไฟฟ้า</span>
                            <input type="text" id="input_part_tire" placeholder="ระบุยี่ห้อ/รุ่น เช่น ยี่ห้อ Michelin รุ่น City Grip 130/70-12"
                                class="w-full bg-gray-50 border border-gray-200 px-2.5 py-1.5 rounded-lg text-xs outline-none focus:bg-white focus:border-pink-400 transition">
                        </div>

                        <!-- 5. ระบบไฟ/กล่อง ECU -->
                        <div class="bg-white p-2.5 rounded-xl border border-gray-200 shadow-sm flex flex-col gap-1.5">
                            <span class="font-bold text-gray-800">⚡ ระบบไฟ/กล่อง ECU</span>
                            <input type="text" id="input_part_ecu" placeholder="ระบุยี่ห้อ/รุ่น เช่น ยี่ห้อ Kelly Controller รุ่น KLS7230N ECU"
                                class="w-full bg-gray-50 border border-gray-200 px-2.5 py-1.5 rounded-lg text-xs outline-none focus:bg-white focus:border-pink-400 transition">
                        </div>

                        <!-- 6. ไฟส่องสว่าง/ไฟเลี้ยว -->
                        <div class="bg-white p-2.5 rounded-xl border border-gray-200 shadow-sm flex flex-col gap-1.5">
                            <span class="font-bold text-gray-800">💡 ไฟส่องสว่าง/ไฟเลี้ยว</span>
                            <input type="text" id="input_part_lights" placeholder="ระบุยี่ห้อ/รุ่น เช่น ยี่ห้อ OSRAM รุ่น LED Projector 12V"
                                class="w-full bg-gray-50 border border-gray-200 px-2.5 py-1.5 rounded-lg text-xs outline-none focus:bg-white focus:border-pink-400 transition">
                        </div>

                        <!-- 7. โครงสร้าง/กระจก -->
                        <div class="bg-white p-2.5 rounded-xl border border-gray-200 shadow-sm flex flex-col gap-1.5">
                            <span class="font-bold text-gray-800">🪞 โครงสร้าง/กระจก</span>
                            <input type="text" id="input_part_chassis" placeholder="ระบุยี่ห้อ/รุ่น เช่น ยี่ห้อ YRU Custom Tempered Safety Glass"
                                class="w-full bg-gray-50 border border-gray-200 px-2.5 py-1.5 rounded-lg text-xs outline-none focus:bg-white focus:border-pink-400 transition">
                        </div>

                        <!-- 8. แตรและระบบเสียง -->
                        <div class="bg-white p-2.5 rounded-xl border border-gray-200 shadow-sm flex flex-col gap-1.5">
                            <span class="font-bold text-gray-800">🔊 แตรและระบบเสียง</span>
                            <input type="text" id="input_part_horn" placeholder="ระบุยี่ห้อ/รุ่น เช่น ยี่ห้อ HELLA รุ่น Waterproof Twin Horn 12V"
                                class="w-full bg-gray-50 border border-gray-200 px-2.5 py-1.5 rounded-lg text-xs outline-none focus:bg-white focus:border-pink-400 transition">
                        </div>
                    </div>

                    <!-- Hidden service center input for JS safety -->
                    <input type="hidden" id="modalTramServiceCenter" value="">
                </div>
            </div>
        </div>
        
        <div class="flex justify-end gap-3 mt-8 border-t pt-4">
            <button onclick="closeTramModal()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 text-sm font-medium transition">ยกเลิก</button>
            <button onclick="saveTramData()" class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700 text-sm font-medium transition">บันทึก</button>
        </div>
    </div>
</div>

<div id="tramDetailsModal" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden p-4 z-50 transition-opacity">
    <div class="bg-white p-6 rounded-2xl shadow-2xl w-full max-w-lg transform transition-all border border-slate-100">
        <!-- Modal Title -->
        <div class="flex justify-between items-center mb-5 border-b border-slate-100 pb-3.5">
            <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                <i class="fas fa-bus-alt text-pink-500"></i>
                <span>รายละเอียดรถไฟฟ้า</span>
            </h3>
            <button onclick="closeTramDetailsModal()" class="text-slate-400 hover:text-slate-600 focus:outline-none transition">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>

        <!-- General Info Header -->
        <div class="flex items-center gap-5 pb-5 border-b border-slate-100 mb-5">
            <div class="relative w-20 h-20 rounded-2xl bg-gradient-to-br from-pink-50 to-pink-100/50 border border-pink-100 flex items-center justify-center text-pink-500 overflow-hidden shadow-inner flex-shrink-0">
                <img id="detailTramImg" src="" class="hidden w-full h-full object-cover">
                <span id="detailTramImgPlaceholder" class="animate-pulse"><i class="fas fa-bus text-3xl"></i></span>
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex justify-between items-start gap-2">
                    <div>
                        <h4 id="detailTramId" class="text-2xl font-black text-slate-800 tracking-tight">-</h4>
                        <p id="detailTramName" class="text-xs text-slate-400 font-medium mt-0.5">-</p>
                    </div>
                    <span id="detailTramStatus" class="px-3 py-1 rounded-full text-[10px] font-bold tracking-wide uppercase shadow-sm">-</span>
                </div>
                <div class="flex flex-wrap gap-2 mt-3.5">
                    <span class="bg-slate-50 border border-slate-100/80 px-2.5 py-1 rounded-lg text-[10px] font-medium text-slate-600 flex items-center gap-1.5 shadow-sm"><i class="fas fa-id-card text-slate-400"></i><span id="detailTramPlate">-</span></span>
                    <span class="bg-slate-50 border border-slate-100/80 px-2.5 py-1 rounded-lg text-[10px] font-medium text-slate-600 flex items-center gap-1.5 shadow-sm"><i class="fas fa-users text-slate-400"></i><span id="detailTramCapacity">-</span></span>
                    <span class="bg-slate-50 border border-slate-100/80 px-2.5 py-1 rounded-lg text-[10px] font-medium text-slate-600 flex items-center gap-1.5 shadow-sm"><i class="fas fa-user-tie text-slate-400"></i><span id="detailTramDriver">-</span></span>
                    </div>
            </div>
        </div>

        <!-- Tab Navigation -->
        <div class="bg-slate-100 p-1 rounded-xl flex gap-1 mb-5">
            <button onclick="switchDetailTab('tabGeneral')" id="btnTabGeneral" class="flex-1 text-center py-1.5 px-3 bg-white text-pink-600 shadow-sm font-semibold rounded-md text-xs transition duration-150 focus:outline-none">
                <i class="fas fa-wrench mr-1"></i>งานช่าง
            </button>
            <button onclick="switchDetailTab('tabMaintenance')" id="btnTabMaintenance" class="flex-1 text-center py-1.5 px-3 text-gray-500 hover:text-gray-800 hover:bg-white/50 rounded-md text-xs font-semibold transition duration-150 focus:outline-none">
                <i class="fas fa-shield-alt mr-1"></i>ทรัพย์สิน
            </button>
        </div>

        <!-- Tab 1: งานช่าง (Maintenance/Engineering) -->
        <div id="contentTabGeneral" class="space-y-4">
            <!-- Active Issue Warning Box -->
            <div id="detailRepairWarningBox" class="hidden bg-red-50 border border-red-150 text-red-700 px-4 py-3 rounded-xl flex items-start gap-3 shadow-sm">
                <i class="fas fa-exclamation-triangle text-lg mt-0.5 flex-shrink-0 text-red-500 animate-pulse"></i>
                <div>
                    <h5 class="font-bold text-sm">การแจ้งซ่อมที่รอดำเนินการ</h5>
                    <p id="detailRepairWarningText" class="text-xs mt-0.5">-</p>
                </div>
            </div>

            <!-- Next Schedule Card -->
            <div class="bg-sky-50/40 border border-sky-100/80 p-3.5 rounded-xl flex items-center gap-3.5 shadow-sm">
                <div class="w-10 h-10 rounded-xl bg-white border border-sky-100 flex items-center justify-center flex-shrink-0 shadow-sm">
                    <i class="fas fa-calendar-check text-sky-500 text-lg"></i>
                </div>
                <div class="flex-1 min-w-0">
                    <span class="block text-[9px] text-sky-500 font-bold uppercase tracking-wider">กำหนดการเช็กระยะรอบถัดไป / เปลี่ยนอะไหล่</span>
                    <span id="detailTramSchedule" class="font-bold text-sky-900 text-xs mt-0.5 block truncate">-</span>
                </div>
            </div>

            <!-- Timeline of past repairs -->
            <div>
                <h4 class="text-[10px] font-bold text-slate-400 uppercase tracking-wider mb-2.5 flex items-center gap-1.5"><i class="fas fa-history"></i>ประวัติการบำรุงรักษาล่าสุด</h4>
                <div id="detailMaintenanceList" class="space-y-2 max-h-[160px] overflow-y-auto pr-1">
                    <!-- Dynamic timeline logs go here -->
                </div>
            </div>
        </div>

        <!-- Tab 2: ทรัพย์สิน (Asset/Property) -->
        <div id="contentTabMaintenance" class="space-y-4 hidden">
            <div class="grid grid-cols-1 gap-4 bg-slate-50/50 p-4 rounded-xl border border-slate-100 text-xs shadow-sm">
                <div class="border-b border-slate-100 pb-3">
                    <span class="block text-[9px] text-slate-400 font-bold uppercase tracking-wider flex items-center gap-1.5"><i class="fas fa-calendar-alt text-slate-400"></i>วันที่ซื้อ / รับมอบสินทรัพย์</span>
                    <span id="detailTramPurchaseDate" class="font-bold text-slate-700 mt-1.5 block">-</span>
                </div>
                <div class="border-b border-slate-100 pb-3">
                    <span class="block text-[9px] text-slate-400 font-bold uppercase tracking-wider flex items-center gap-1.5"><i class="fas fa-shield-alt text-slate-400"></i>ระยะเวลารับประกัน (Warranty)</span>
                    <span id="detailTramWarranty" class="font-bold text-slate-700 mt-1.5 block">-</span>
                </div>
                <div>
                    <span class="block text-[9px] text-slate-400 font-bold uppercase tracking-wider flex items-center gap-1.5"><i class="fas fa-handshake text-slate-400"></i>ข้อมูลติดต่อผู้จัดจำหน่าย (Supplier)</span>
                    <span id="detailTramSupplier" class="font-bold text-slate-700 mt-1.5 block">-</span>
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="flex justify-between items-center mt-6 border-t border-slate-100 pt-4">
            <div class="text-[10px] text-slate-400 font-medium leading-relaxed">
                <div>บันทึกโดย: <span id="detailTramUpdatedBy" class="font-semibold text-slate-500">-</span></div>
                <div class="mt-0.5">เมื่อ: <span id="detailTramUpdatedAt" class="font-semibold text-slate-500">-</span></div>
            </div>
            <div class="flex gap-2">
                <button id="btnDetailRestoreTram" class="hidden border border-emerald-300 text-emerald-700 bg-emerald-50 hover:bg-emerald-100 px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm">
                    <i class="fas fa-check-circle text-emerald-600"></i> คืนสภาพพร้อมใช้งาน
                </button>
                <button id="btnDetailEditTram" class="border border-blue-200 text-blue-600 bg-blue-50/30 hover:bg-blue-50 px-4 py-2.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-sm hover:border-blue-300">
                    <i class="fas fa-edit"></i> แก้ไขข้อมูล
                </button>
                <button onclick="closeTramDetailsModal()" class="bg-pink-500 hover:bg-pink-600 text-white px-6 py-2.5 rounded-xl text-xs font-bold transition shadow-md hover:shadow-lg flex items-center gap-1">
                    <i class="fas fa-check-circle mr-0.5"></i> ตกลง
                </button>
            </div>
        </div>
    </div>
</div>


<div id="repairCompleteModal" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden p-4 z-50 transition-opacity">
    <div class="bg-white p-6 rounded-xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto transform transition-all">
        <div class="flex justify-between items-center mb-4 border-b pb-3">
            <h3 class="text-xl font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-tools text-pink-500"></i> บันทึกสรุปผลงานการซ่อมบำรุง
            </h3>
            <button onclick="closeRepairCompleteModal()" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <input type="hidden" id="editRepairTramIndex" value="-1">
        
        <div class="space-y-4 text-sm text-gray-700">
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">เลือกรถไฟฟ้า <span class="text-red-500">*</span></label>
                <select id="modalRepairTramSelect" class="w-full border border-gray-300 p-2.5 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none bg-white">
                    <!-- Options populated dynamically -->
                </select>
            </div>
            
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">หัวข้อการซ่อม/เช็กระยะ (ย่อ) <span class="text-red-500">*</span></label>
                <input type="text" id="modalRepairSummary" class="w-full border border-gray-300 p-2.5 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none" placeholder="เช่น เปลี่ยนผ้าเบรก, ซ่อมบอร์ดควบคุมความร้อนแบตเตอรี่">
            </div>
            
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">รายละเอียดการซ่อมเชิงลึก <span class="text-red-500">*</span></label>
                <textarea id="modalRepairDetail" rows="3" class="w-full border border-gray-300 p-2.5 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none" placeholder="รายละเอียดขั้นตอนการซ่อมบำรุงเชิงลึกของช่าง..."></textarea>
            </div>
            
            <div>
                <label class="block text-xs font-semibold text-gray-500 mb-1">รายการอะไหล่ที่ใช้ (เลือกได้หลายรายการ)</label>
                <div class="grid grid-cols-2 gap-2 mt-1.5 border border-gray-200 p-3 rounded-lg bg-gray-50">
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-medium"><input type="checkbox" name="repairSpareParts" value="ยางรถไฟฟ้า" class="rounded text-pink-600 focus:ring-pink-500"> ยางรถไฟฟ้า</label>
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-medium"><input type="checkbox" name="repairSpareParts" value="ผ้าเบรกหน้า-หลัง" class="rounded text-pink-600 focus:ring-pink-500"> ผ้าเบรกหน้า-หลัง</label>
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-medium"><input type="checkbox" name="repairSpareParts" value="แบตเตอรี่หลัก" class="rounded text-pink-600 focus:ring-pink-500"> แบตเตอรี่หลัก</label>
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-medium"><input type="checkbox" name="repairSpareParts" value="มอเตอร์ไฟฟ้า" class="rounded text-pink-600 focus:ring-pink-500"> มอเตอร์ไฟฟ้า</label>
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-medium"><input type="checkbox" name="repairSpareParts" value="บอร์ดควบคุมกระแสไฟ" class="rounded text-pink-600 focus:ring-pink-500"> บอร์ดควบคุมกระแสไฟ</label>
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-medium"><input type="checkbox" name="repairSpareParts" value="สปริงโช้คอัพ" class="rounded text-pink-600 focus:ring-pink-500"> สปริงโช้คอัพ</label>
                    <label class="flex items-center gap-2 cursor-pointer text-xs font-medium"><input type="checkbox" name="repairSpareParts" value="ขั้วต่อสายไฟ/ฟิวส์" class="rounded text-pink-600 focus:ring-pink-500"> ขั้วต่อสายไฟ/ฟิวส์</label>
                </div>
            </div>
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">ชื่อช่างผู้ดูแล (Auto-filled)</label>
                    <input type="text" id="modalRepairTechnician" class="w-full border border-gray-300 p-2.5 rounded-lg bg-gray-100 text-gray-500 cursor-not-allowed font-medium" value="ช่างประสาน" readonly>
                </div>
                
                <div>
                    <label class="block text-xs font-semibold text-gray-500 mb-1">วันที่ซ่อมเสร็จ <span class="text-red-500">*</span></label>
                    <input type="date" lang="en-GB" id="modalRepairDate" class="w-full border border-gray-300 p-2.5 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none bg-white">
                </div>
            </div>
        </div>
        
        <div class="flex justify-end gap-3 mt-6 border-t pt-4">
            <button onclick="closeRepairCompleteModal()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 text-sm font-medium transition">ยกเลิก</button>
            <button onclick="saveRepairJob()" class="bg-pink-600 text-white px-5 py-2 rounded-lg hover:bg-pink-700 text-sm font-semibold transition shadow-md">บันทึกและคืนสถานะรถเป็นพร้อมใช้งาน</button>
        </div>
    </div>
</div>

<div id="stopModal" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden p-4 z-50">
    <div class="bg-white p-6 rounded-xl shadow-xl w-full max-w-md">
        <h3 id="stopModalTitle" class="text-xl font-bold mb-4 text-gray-800">เพิ่มจุดจอด</h3>
        <input type="hidden" id="editStopIndex">
        <div class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ลำดับของจุดจอด <span class="text-red-500">*</span></label>
                <input type="number" id="modalStopSequence" class="w-full border border-gray-300 p-2 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none text-sm" placeholder="เช่น 1, 2, 3">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-700 mb-1">ชื่อจุดจอดรถ <span class="text-red-500">*</span></label>
                <input type="text" id="modalStopName" class="w-full border border-gray-300 p-2 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none text-sm" placeholder="เช่น หน้าอาคารสังคมศาสตร์">
            </div>
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ละติจูด (Latitude) <span class="text-red-500">*</span></label>
                    <input type="number" step="any" id="modalStopLat" class="w-full border border-gray-300 p-2 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none text-sm" placeholder="เช่น 6.549929">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-700 mb-1">ลองจิจูด (Longitude) <span class="text-red-500">*</span></label>
                    <input type="number" step="any" id="modalStopLng" class="w-full border border-gray-300 p-2 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none text-sm" placeholder="เช่น 101.291254">
                </div>
            </div>
            <div class="hidden">
                <label class="block text-sm font-medium text-gray-700 mb-1">เส้นทาง</label>
                <input type="text" id="modalStopRoute" class="w-full border border-gray-300 p-2 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none text-sm" placeholder="เช่น สายสีชมพู">
            </div>
        </div>
        <div class="flex justify-end gap-3 mt-6">
            <button onclick="closeStopModal()" class="bg-gray-200 text-gray-700 px-4 py-2 rounded-lg hover:bg-gray-300 text-sm font-medium transition">ยกเลิก</button>
            <button onclick="saveStopData()" class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700 text-sm font-medium transition">บันทึก</button>
        </div>
    </div>
</div>

<script>

// ข้อมูลจำลองยี่ห้อและรุ่นของชิ้นส่วนอะไหล่มาตรฐาน (8 รายการ)
const standardEvPartsData = {
    battery: "ยี่ห้อ CATL รุ่น LiFePO4 72V 200Ah",
    motor: "ยี่ห้อ QS Motor 72V 5000W BLDC",
    brake: "ยี่ห้อ Akebono รุ่น Ceramic Heavy-Duty",
    tire: "ยี่ห้อ Michelin รุ่น City Grip 130/70-12",
    ecu: "ยี่ห้อ Kelly Controller รุ่น KLS7230N ECU",
    lights: "ยี่ห้อ OSRAM รุ่น LED Projector 12V",
    chassis: "ยี่ห้อ YRU Custom รุ่น Tempered Safety Glass",
    horn: "ยี่ห้อ HELLA รุ่น Waterproof Twin Horn 12V"
};

const defaultTramPartsMock = {
    "EV-01": { parts_data: { ...standardEvPartsData } },
    "EV-02": { parts_data: { ...standardEvPartsData } },
    "EV-03": { parts_data: { ...standardEvPartsData } },
    "EV-04": { parts_data: { ...standardEvPartsData } },
    "EV-05": { parts_data: { ...standardEvPartsData } },
    "EV-06": { parts_data: { ...standardEvPartsData } },
    "EV-07": { parts_data: { ...standardEvPartsData } },
    "EV-08": { parts_data: { ...standardEvPartsData } },
    "EV-09": { parts_data: { ...standardEvPartsData } },
    "EV-10": { parts_data: { ...standardEvPartsData } }
};

// ข้อมูลตั้งต้นระบบแบบ Mockup 
const defaultTrams = [
    { 
        id: "EV-01", 
        name: "รถไฟฟ้าคันที่ 1", 
        plate: "กค 1234 ยะลา", 
        capacity_sit: 20, 
        capacity_stand: 10, 
        status: "พร้อมใช้งาน", 
        gps_id: "GPS-EV01-YRU", 
        route: "สาย 1: เนินขาม-หอพัก", 
        driver: "นายอัสมี มูเล็ง", 
        driver_id: "USR003", 
        battery: 85, 
        image: "",
        coords: "6.549929, 101.291254",
        active_issue: "",
        updated_by: "admin@yru.ac.th",
        updated_at: "- ยังไม่มีการอัปเดต -",
        purchase_date: "2025-03-12",
        warranty: "5 ปี (สิ้นสุด 12 มีนาคม 2573)",
        supplier: "บริษัท ยะลายานยนต์ อีวี จำกัด (โทร. 073-123456)",
        maintenance: [
            { date: "05/07/2569", detail: "เช็คระยะระบบขับเคลื่อน และทดสอบไฟชาร์จแบตเตอรี่ (ผลการทดสอบ: ปกติ)", technician: "ช่างประสาน" },
            { date: "28/06/2569", detail: "เปลี่ยนผ้าเบรกหน้า-หลัง และเปลี่ยนยางรถไฟฟ้าใหม่ 4 ล้อ", technician: "ช่างสมคิด" }
        ]
    },
    { 
        id: "EV-02", 
        name: "รถไฟฟ้าคันที่ 2", 
        plate: "กค 5678 ยะลา", 
        capacity_sit: 16, 
        capacity_stand: 8, 
        status: "พร้อมใช้งาน", 
        gps_id: "GPS-EV02-YRU", 
        route: "สาย 2: วงเวียน-คณะวิทยาศาสตร์", 
        driver: "นายอัรฟาน มะเระ", 
        driver_id: "USR004", 
        battery: 92, 
        image: "",
        coords: "6.549100, 101.290467",
        active_issue: "",
        updated_by: "admin@yru.ac.th",
        updated_at: "- ยังไม่มีการอัปเดต -",
        purchase_date: "2025-01-01",
        warranty: "5 ปี (สิ้นสุด 1 มกราคม 2573)",
        supplier: "บริษัท อันดามัน เทคโนโลยี จำกัด (โทร. 076-987654)",
        maintenance: [
            { date: "01/07/2569", detail: "ตรวจเช็คระดับน้ำกลั่นแบตเตอรี่สำรอง และทำความสะอาดขั้วต่อกระแสไฟ", technician: "ช่างประสาน" },
            { date: "15/06/2569", detail: "เปลี่ยนน้ำมันเกียร์ไฟฟ้า และขันน็อตช่วงล่างทุกตัวเพื่อความปลอดภัย", technician: "ช่างสมคิด" }
        ]
    },
    { 
        id: "EV-03", 
        name: "รถไฟฟ้าคันที่ 3", 
        plate: "กค 9012 ยะลา", 
        capacity_sit: 20, 
        capacity_stand: 10, 
        status: "พร้อมใช้งาน", 
        gps_id: "GPS-EV03-YRU", 
        route: "สาย 3: ประตูหลังมอ-หอประชุม", 
        driver: "นายซูเฟียน มะโอะ", 
        driver_id: "USR005", 
        battery: 88, 
        image: "",
        coords: "6.547835, 101.289502",
        active_issue: "",
        updated_by: "admin@yru.ac.th",
        updated_at: "- ยังไม่มีการอัปเดต -",
        purchase_date: "2024-10-15",
        warranty: "3 ปี (สิ้นสุด 15 ตุลาคม 2570)",
        supplier: "บริษัท ยะลา อีวี กรุ๊ป จำกัด (โทร. 081-2345678)",
        maintenance: [
            { date: "06/07/2569", detail: "ทำความสะอาดตัวกรองระบายความร้อนบอร์ดควบคุมหม้อแปลงไฟฟ้า", technician: "ช่างวิรัช" },
            { date: "24/06/2569", detail: "เปลี่ยนยางหน้าขวา 1 เส้น เนื่องจากขับเบียดขอบทางเดินเท้า", technician: "ช่างสมคิด" }
        ]
    },
    { 
        id: "EV-04", 
        name: "รถไฟฟ้าคันที่ 4", 
        plate: "กค 3456 ยะลา", 
        capacity_sit: 18, 
        capacity_stand: 10, 
        status: "พร้อมใช้งาน", 
        gps_id: "GPS-EV04-YRU", 
        route: "สาย 1: เนินขาม-หอพัก", 
        driver: "นายอุสมาน สาและ", 
        driver_id: "USR006", 
        battery: 78, 
        image: "",
        coords: "6.547224, 101.289471",
        active_issue: "",
        updated_by: "admin@yru.ac.th",
        updated_at: "- ยังไม่มีการอัปเดต -",
        purchase_date: "2024-05-20",
        warranty: "3 ปี (สิ้นสุด 20 พฤษภาคม 2570)",
        supplier: "บริษัท ยะลา อีวี กรุ๊ป จำกัด (โทร. 081-2345678)",
        maintenance: [
            { date: "04/07/2569", detail: "ตรวจเช็คระบบไฟฟ้า สัญญานแตร และไฟหน้า-ไฟเลี้ยวรอบคัน (ผ่านเกณฑ์)", technician: "ช่างวิรัช" },
            { date: "20/06/2569", detail: "เปลี่ยนสปริงโช้คอัพหลังซ้าย-ขวา เพื่อรองรับน้ำหนักผู้โดยสารได้ดีขึ้น", technician: "ช่างสมคิด" }
        ]
    },
    { 
        id: "EV-05", 
        name: "รถไฟฟ้าคันที่ 5", 
        plate: "กค 7890 ยะลา", 
        capacity_sit: 20, 
        capacity_stand: 10, 
        status: "พร้อมใช้งาน", 
        gps_id: "GPS-EV05-YRU", 
        route: "สาย 2: วงเวียน-คณะวิทยาศาสตร์", 
        driver: "นายบัดรี สาและ", 
        driver_id: "USR007", 
        battery: 95, 
        image: "",
        coords: "6.547311, 101.288880",
        active_issue: "",
        updated_by: "admin@yru.ac.th",
        updated_at: "- ยังไม่มีการอัปเดต -",
        purchase_date: "2025-01-10",
        warranty: "3 ปี (สิ้นสุด 10 มกราคม 2571)",
        supplier: "บริษัท ยะลา อีวี กรุ๊ป จำกัด",
        maintenance: []
    },
    { 
        id: "EV-06", 
        name: "รถไฟฟ้าคันที่ 6", 
        plate: "กค 1122 ยะลา", 
        capacity_sit: 16, 
        capacity_stand: 8, 
        status: "พร้อมใช้งาน", 
        gps_id: "GPS-EV06-YRU", 
        route: "สาย 3: ประตูหลังมอ-หอประชุม", 
        driver: "นายตอรริก ลือแมะ", 
        driver_id: "USR008", 
        battery: 89, 
        image: "",
        coords: "6.548822, 101.288523",
        active_issue: "",
        updated_by: "admin@yru.ac.th",
        updated_at: "- ยังไม่มีการอัปเดต -",
        purchase_date: "2025-02-15",
        warranty: "3 ปี (สิ้นสุด 15 กุมภาพันธ์ 2571)",
        supplier: "บริษัท ยะลา อีวี กรุ๊ป จำกัด",
        maintenance: []
    },
    { 
        id: "EV-07", 
        name: "รถไฟฟ้าคันที่ 7", 
        plate: "กค 3344 ยะลา", 
        capacity_sit: 18, 
        capacity_stand: 8, 
        status: "พร้อมใช้งาน", 
        gps_id: "GPS-EV07-YRU", 
        route: "สาย 1: เนินขาม-หอพัก", 
        driver: "นายสมหวัง ใจดี", 
        driver_id: "USR009", 
        battery: 82, 
        image: "",
        coords: "6.549225, 101.289286",
        active_issue: "",
        updated_by: "admin@yru.ac.th",
        updated_at: "- ยังไม่มีการอัปเดต -",
        purchase_date: "2025-03-01",
        warranty: "3 ปี (สิ้นสุด 1 มีนาคม 2571)",
        supplier: "บริษัท ยะลา อีวี กรุ๊ป จำกัด",
        maintenance: []
    },
    { 
        id: "EV-08", 
        name: "รถไฟฟ้าคันที่ 8", 
        plate: "กค 5566 ยะลา", 
        capacity_sit: 20, 
        capacity_stand: 10, 
        status: "พร้อมใช้งาน", 
        gps_id: "GPS-EV08-YRU", 
        route: "สาย 2: วงเวียน-คณะวิทยาศาสตร์", 
        driver: "นายสมใจ ใจดี", 
        driver_id: "USR010", 
        battery: 90, 
        image: "",
        coords: "6.549929, 101.291254",
        active_issue: "",
        updated_by: "admin@yru.ac.th",
        updated_at: "- ยังไม่มีการอัปเดต -",
        purchase_date: "2025-03-20",
        warranty: "3 ปี (สิ้นสุด 20 มีนาคม 2571)",
        supplier: "บริษัท ยะลา อีวี กรุ๊ป จำกัด",
        maintenance: []
    },
    { 
        id: "EV-09", 
        name: "รถไฟฟ้าคันที่ 9", 
        plate: "กค 7788 ยะลา", 
        capacity_sit: 16, 
        capacity_stand: 8, 
        status: "พร้อมใช้งาน", 
        gps_id: "GPS-EV09-YRU", 
        route: "สาย 3: ประตูหลังมอ-หอประชุม", 
        driver: "นายกิตติ ตั้งใจ", 
        driver_id: "USR011", 
        battery: 87, 
        image: "",
        coords: "6.547835, 101.289502",
        active_issue: "",
        updated_by: "admin@yru.ac.th",
        updated_at: "- ยังไม่มีการอัปเดต -",
        purchase_date: "2025-04-05",
        warranty: "3 ปี (สิ้นสุด 5 เมษายน 2571)",
        supplier: "บริษัท ยะลา อีวี กรุ๊ป จำกัด",
        maintenance: []
    },
    { 
        id: "EV-10", 
        name: "รถไฟฟ้าคันที่ 10", 
        plate: "กค 9900 ยะลา", 
        capacity_sit: 18, 
        capacity_stand: 10, 
        status: "พร้อมใช้งาน", 
        gps_id: "GPS-EV10-YRU", 
        route: "สาย 1: เนินขาม-หอพัก", 
        driver: "นายรุสลัน สอเฮาะ", 
        driver_id: "USR012", 
        battery: 91, 
        image: "",
        coords: "6.547311, 101.288880",
        active_issue: "",
        updated_by: "admin@yru.ac.th",
        updated_at: "- ยังไม่มีการอัปเดต -",
        purchase_date: "2025-05-10",
        warranty: "3 ปี (สิ้นสุด 10 พฤษภาคม 2571)",
        supplier: "บริษัท ยะลา อีวี กรุ๊ป จำกัด",
        maintenance: []
    }
];

const defaultStops = [
    { sequence: 1, name: "จุดจอด 1 ประตูหลังมอ.", lat: 6.549929, lng: 101.291254, route: "สายสีชมพู" },
    { sequence: 2, name: "จุดจอด 2 ตึกศิลปะ", lat: 6.549100, lng: 101.290467, route: "สายสีชมพู" },
    { sequence: 3, name: "จุดจอด 3 ศูนย์วิทยาศาสตร์", lat: 6.547835, lng: 101.289502, route: "สายสีชมพู" },
    { sequence: 4, name: "จุดจอด 4 คณะวิทยาศาสตร์", lat: 6.547224, lng: 101.289471, route: "สายสีชมพู" },
    { sequence: 5, name: "จุดจอด 5 คณะสังคมศาสตร์", lat: 6.547311, lng: 101.288880, route: "สายสีชมพู" },
    { sequence: 6, name: "จุดจอด 6 อาคารเรียน20", lat: 6.548822, lng: 101.288523, route: "สายสีชมพู" },
    { sequence: 7, name: "จุดจอด 7 คณะวิทยาการจัดการ", lat: 6.549225, lng: 101.289286, route: "สายสีชมพู" }
];

const defaultRoutes = [
    {
        route_code: "LINE-A (รอบเมือง)",
        route_name: "LINE-A (รอบเมือง)",
        color: "#E91E63",
        route_details: "เส้นทางเดินรถไฟฟ้าสายสีชมพู ครอบคลุมอาคารเรียนและคณะต่าง ๆ รอบ ม.ราชภัฏยะลา",
        route_stops: defaultStops.map((s, i) => ({ parking_spot_code: s.name, stop_order: i + 1, lat: s.lat, lng: s.lng })),
        polyline_data: defaultStops.map(s => [s.lat, s.lng])
    },
    {
        route_code: "LINE-B (รอบใน)",
        route_name: "LINE-B (รอบใน)",
        color: "#3B82F6",
        route_details: "เส้นทางเดินรถไฟฟ้าสายสีฟ้า วิ่งตรงระหว่างตึกศิลปะ คณะวิทยาศาสตร์ และอาคารเรียน 20",
        route_stops: [defaultStops[1], defaultStops[2], defaultStops[3], defaultStops[5]].map((s, i) => ({ parking_spot_code: s.name, stop_order: i + 1, lat: s.lat, lng: s.lng })),
        polyline_data: [defaultStops[1], defaultStops[2], defaultStops[3], defaultStops[5]].map(s => [s.lat, s.lng])
    }
];

const defaultUsers = [
    { user_id: "USR-000001", emp_id: "69001", name: "นายมูฮัมหมัด ซอและ", username: "muhammad", email: "muhammad@yru.ac.th", phone: "081-234-5678", role: "admin", status: "ปกติ" },
    { user_id: "USR-000002", emp_id: "69002", name: "ดร.สมชาย เรียนดี", username: "somchai", email: "somchai@yru.ac.th", phone: "082-345-6789", role: "executive", status: "ปกติ" },
    { user_id: "USR-000003", emp_id: "69003", name: "นายอัสมี มูเล็ง", username: "asmee", email: "asmee@yru.ac.th", phone: "083-456-7890", role: "driver", status: "ปกติ" },
    { user_id: "USR-000004", emp_id: "69004", name: "นายอัรฟาน มะเระ", username: "arfan", email: "arfan@yru.ac.th", phone: "084-567-8901", role: "driver", status: "ปกติ" },
    { user_id: "USR-000005", emp_id: "69005", name: "นายซูเฟียน มะโละ", username: "sufiyan", email: "sufiyan@yru.ac.th", phone: "085-678-9012", role: "driver", status: "ปกติ" },
    { user_id: "USR-000006", emp_id: "69006", name: "นายอุสมาน สาและ", username: "usman", email: "usman@yru.ac.th", phone: "086-789-0123", role: "driver", status: "ปกติ" },
    { user_id: "USR-000007", emp_id: "69007", name: "นายบัดรี สาและ", username: "badri", email: "badri@yru.ac.th", phone: "087-890-1234", role: "driver", status: "ปกติ" },
    { user_id: "USR-000008", emp_id: "69008", name: "นายตอริก ลือแมะ", username: "torik", email: "torik@yru.ac.th", phone: "088-901-2345", role: "driver", status: "ปกติ" },
    { user_id: "USR-000009", emp_id: "69009", name: "นายสมหวัง ใจดี", username: "somwang", email: "somwang@yru.ac.th", phone: "089-012-3456", role: "driver", status: "ปกติ" },
    { user_id: "USR-000010", emp_id: "69010", name: "นายสมใจ ใจดี", username: "somjal", email: "somjal@yru.ac.th", phone: "090-123-4567", role: "driver", status: "ปกติ" },
    { user_id: "USR-000011", emp_id: "69011", name: "นายกิตติ ตั้งใจ", username: "kitti", email: "kitti@yru.ac.th", phone: "091-234-5678", role: "driver", status: "ปกติ" },
    { user_id: "USR-000012", emp_id: "69012", name: "นายรุสลัน สอเฮาะ", username: "ruslan", email: "ruslan@yru.ac.th", phone: "092-345-6789", role: "driver", status: "ปกติ" },
    { user_id: "USR-000013", emp_id: "406665014", name: "นางสาวทัศนีย์ สาและ", username: "406665014", email: "406665014@yru.ac.th", phone: "0635497741", role: "student", status: "ปกติ" },
    { user_id: "USR-000014", emp_id: "406665035", name: "นางสาวพิชญา ชุมมิคสา", username: "406665035", email: "406665035@yru.ac.th", phone: "0635497741", role: "student", status: "ปกติ" },
    { user_id: "USR-000015", emp_id: "406665025", name: "นางสาววรนุช อาดำ", username: "406665025", email: "406665025@yru.ac.th", phone: "0635497741", role: "student", status: "ปกติ" }
];

// LocalStorage Manager Helpers
function getStorage(key, defaultData) {
    if (!localStorage.getItem(key)) localStorage.setItem(key, JSON.stringify(defaultData));
    let data = JSON.parse(localStorage.getItem(key));
    if (key === "yru_stops_v2" && Array.isArray(data)) {
        let modified = false;
        defaultStops.forEach(defaultStop => {
            let existing = data.find(s => s.name === defaultStop.name || (s.sequence === defaultStop.sequence && defaultStop.sequence));
            if (!existing) {
                data.push(JSON.parse(JSON.stringify(defaultStop)));
                modified = true;
            } else {
                const lat = parseFloat(existing.lat);
                const lng = parseFloat(existing.lng);
                if (isNaN(lat) || isNaN(lng) || lat === 0 || lng === 0) {
                    existing.lat = defaultStop.lat;
                    existing.lng = defaultStop.lng;
                    modified = true;
                }
                if (!existing.sequence) {
                    existing.sequence = defaultStop.sequence;
                    modified = true;
                }
            }
        });
        if (modified) {
            data.sort((a, b) => (a.sequence || 0) - (b.sequence || 0));
            localStorage.setItem(key, JSON.stringify(data));
        }
    }
    if (key === "yru_routes_v1" && localStorage.getItem(key) === null && Array.isArray(defaultData) && defaultData.length > 0) {
        data = JSON.parse(JSON.stringify(defaultData));
        localStorage.setItem(key, JSON.stringify(data));
    }
    if (key.startsWith("yru_users") && Array.isArray(data)) {
        let modified = false;
        if (Array.isArray(defaultData)) {
            defaultData.forEach(defaultUser => {
                let existing = data.find(u => u.email === defaultUser.email || u.emp_id === defaultUser.emp_id);
                if (!existing) {
                    data.push(JSON.parse(JSON.stringify(defaultUser)));
                    modified = true;
                }
            });
        }
        data.forEach((u, i) => {
            if (u.status === "ใช้งาน") {
                u.status = "ปกติ";
                modified = true;
            }
            if (!u.phone || u.phone === "-") {
                const match = defaultData ? defaultData.find(d => d.email === u.email || d.user_id === u.user_id || d.emp_id === u.emp_id) : null;
                if (match && match.phone && match.phone !== "-") {
                    u.phone = match.phone;
                } else {
                    const samplePhones = [
                        "081-234-5678", "082-345-6789", "083-456-7890", "084-567-8901",
                        "085-678-9012", "086-789-0123", "087-890-1234", "088-901-2345",
                        "089-012-3456", "090-123-4567", "091-234-5678", "092-345-6789"
                    ];
                    u.phone = samplePhones[i % samplePhones.length];
                }
                modified = true;
            }
        });
        if (modified) {
            localStorage.setItem(key, JSON.stringify(data));
        }
    }
    return data;
}
function setStorage(key, data) { localStorage.setItem(key, JSON.stringify(data)); }

const GARAGE_COORDS = "6.548900, 101.291700";
const GARAGE_STATION_ID = "GARAGE";

const defaultTramCoords = {
    "EV-01": "6.549929, 101.291254", // จุดจอด 1
    "EV-02": "6.549100, 101.290467", // จุดจอด 2
    "EV-03": "6.547835, 101.289502", // จุดจอด 3
    "EV-04": "6.547224, 101.289471", // จุดจอด 4
    "EV-05": "6.547311, 101.288880", // จุดจอด 5
    "EV-06": "6.548822, 101.288523", // จุดจอด 6
    "EV-07": "6.549225, 101.289286", // จุดจอด 7
    "EV-08": "6.549929, 101.291254", // จุดจอด 1
    "EV-09": "6.547835, 101.289502", // จุดจอด 3
    "EV-10": "6.547311, 101.288880"  // จุดจอด 5
};

let trams = getStorage("yru_trams_v18", defaultTrams);
// รถ EV-01..EV-10 คือรถหลัก รถที่แอดมินเพิ่มใหม่ (EV-11+) จะถูกส่งไปอยู่ Garage อัตโนมัติ
const baseVehicleIds = ["EV-01","EV-02","EV-03","EV-04","EV-05","EV-06","EV-07","EV-08","EV-09","EV-10"];
// บังคับอัปเดตถ้ารถหลักมีไม่ถึง 10 คัน
const baseTrams = trams.filter(t => baseVehicleIds.includes(t.id));
if (!baseTrams || baseTrams.length < 10) {
    const existingIds = trams.map(t => t.id);
    defaultTrams.forEach(dt => {
        if (!existingIds.includes(dt.id)) trams.push(dt);
    });
    setStorage("yru_trams_v18", trams);
}

// ตั้งค่าพิกัดและข้อมูลอะไหล่จำลอง: รถหลักใช้พิกัดจุดจอด, รถใหม่ (EV-11+) อยู่ Garage จนกว่าจะถูก assign
trams.forEach(t => {
    const isBase = baseVehicleIds.includes(t.id);
    if (!t.parts_data || Object.keys(t.parts_data).length === 0) {
        if (defaultTramPartsMock[t.id]) {
            t.parts_data = defaultTramPartsMock[t.id].parts_data;
            t.parts = defaultTramPartsMock[t.id].parts;
        }
    }
    if (t.status === "รถขัดข้อง" || t.status === "ระงับการใช้งาน" || t.status === "MAINTENANCE" || t.status === "SUSPENDED") {
        if (t.coords && t.coords !== GARAGE_COORDS) {
            t.last_active_coords = t.coords;
        }
        t.coords = GARAGE_COORDS;
        t.current_station_id = GARAGE_STATION_ID;
    } else if (!isBase) {
        if (!t.coords || t.coords === GARAGE_COORDS) {
            t.coords = GARAGE_COORDS;
        }
        t.current_station_id = GARAGE_STATION_ID;
    } else {
        t.current_station_id = null;
        const coordsMap = {
            "EV-01": "6.549929, 101.291254", "EV-02": "6.549100, 101.290467", "EV-03": "6.547835, 101.289502", 
            "EV-04": "6.547224, 101.289471", "EV-05": "6.547311, 101.288880", "EV-06": "6.548822, 101.288523", 
            "EV-07": "6.549225, 101.289286", "EV-08": "6.549929, 101.291254", "EV-09": "6.547835, 101.289502", "EV-10": "6.547311, 101.288880"
        };
        if (!t.coords || t.coords === GARAGE_COORDS || t.coords === "6.548900, 101.291700") {
            t.coords = t.last_active_coords || coordsMap[t.id] || "6.549929, 101.291254";
        }
    }
});
setStorage("yru_trams_v18", trams);

let stops = getStorage("yru_stops_v2", defaultStops);
let users = getStorage("yru_users_v8", defaultUsers);
let myChart = null; // ตัวแปรเก็บ Object กราฟ

// ===== Custom Searchable Dropdown for Driver =====
function toggleDriverDropdown(event) {
    event.stopPropagation();
    const dropdownMenu = document.getElementById("driverDropdownMenu");
    dropdownMenu.classList.toggle("hidden");
    if (!dropdownMenu.classList.contains("hidden")) {
        document.getElementById("driverSearchInput").focus();
    }
}

function selectDriver(id, name) {
    document.getElementById("modalTramDriver").value = name;
    document.getElementById("modalTramDriverId").value = id;
    
    const textSpan = document.getElementById("selectedDriverText");
    textSpan.innerText = name;
    if (id === "") {
        textSpan.className = "text-gray-400 font-medium";
    } else {
        textSpan.className = "text-gray-800 font-medium";
    }
    document.getElementById("driverDropdownMenu").classList.add("hidden");
}

function filterDrivers() {
    const keyword = document.getElementById("driverSearchInput").value.toLowerCase().trim();
    const options = document.querySelectorAll("#driverOptionsList .driver-option");
    options.forEach(opt => {
        const nameText = opt.innerText.toLowerCase();
        if (nameText.includes(keyword)) {
            opt.classList.remove("hidden");
        } else {
            opt.classList.add("hidden");
        }
    });
}

function populateDriverDropdown() {
    const listContainer = document.getElementById("driverOptionsList");
    if (!listContainer) return;
    listContainer.innerHTML = "";
    
    // Default option
    const defaultOpt = document.createElement("div");
    defaultOpt.className = "driver-option p-2 hover:bg-pink-50 rounded-md cursor-pointer transition text-gray-500 font-medium";
    defaultOpt.innerText = "-- ไม่ระบุ / ยังไม่มอบหมาย --";
    defaultOpt.onclick = () => selectDriver("", "-- ไม่ระบุ / ยังไม่มอบหมาย --");
    listContainer.appendChild(defaultOpt);
    
    // Reload latest users from storage (จัดการข้อมูลผู้ใช้งาน)
    users = getStorage("yru_users_v8", defaultUsers);
    if (!users || !Array.isArray(users) || users.length === 0) {
        users = defaultUsers;
    }
    
    // Filter active drivers
    const activeDrivers = users.filter(user => {
        const rawRole = (user.role || user.user_role || user.role_name || user.user_type || '').toString().toLowerCase().trim();
        const isDriverRole = (
            rawRole === 'driver' ||
            rawRole === 'พนักงานขับรถ' ||
            rawRole.includes('driver') ||
            rawRole.includes('ขับ') ||
            rawRole.includes('คนขับ')
        );
        const rawStatus = (user.status || user.usage_rights || '').toString().toLowerCase().trim();
        const isInactive = (
            rawStatus.includes('ระงับ') ||
            rawStatus.includes('ยกเลิก') ||
            rawStatus === 'blocked' ||
            rawStatus === 'suspended' ||
            rawStatus === 'inactive'
        );
        return isDriverRole && !isInactive;
    });

    let driversToRender = activeDrivers;
    if (driversToRender.length === 0 && Array.isArray(defaultUsers)) {
        driversToRender = defaultUsers.filter(user => {
            const rawRole = (user.role || user.user_role || '').toString().toLowerCase();
            return rawRole.includes('driver') || rawRole.includes('ขับ');
        });
    }
    
    driversToRender.forEach(driver => {
        const option = document.createElement("div");
        option.className = "driver-option p-2 hover:bg-pink-50 rounded-md cursor-pointer transition text-gray-800 font-medium flex items-center justify-between";
        option.innerHTML = `<span>${driver.name}</span> <span class="text-[10px] text-gray-400 font-mono">${driver.emp_id || driver.user_id || ''}</span>`;
        option.onclick = () => selectDriver(driver.user_id || driver.emp_id || driver.employee_id || '', driver.name);
        listContainer.appendChild(option);
    });
}

document.addEventListener("click", function(event) {
    const dropdownMenu = document.getElementById("driverDropdownMenu");
    const dropdownTrigger = document.getElementById("driverDropdownTrigger");
    if (dropdownMenu && !dropdownMenu.classList.contains("hidden")) {
        if (dropdownTrigger && !dropdownTrigger.contains(event.target) && !dropdownMenu.contains(event.target)) {
            dropdownMenu.classList.add("hidden");
        }
    }
});

// ควบคุมการแสดงผลแถบด้านข้าง (Mobile Hamburger Toggle)
function toggleSidebar() {
    const sidebar = document.getElementById("sidebar");
    sidebar.classList.toggle("-translate-x-full");
}

// หน้าควบคุมและเปลี่ยนแท็บแบบ Smooth
function navigatePage(pageId, buttonEl) {
    showPage(pageId);
    document.querySelectorAll('.menu-btn').forEach(btn => btn.classList.remove('active-menu'));
    if(buttonEl) buttonEl.classList.add('active-menu');
    // ปิด Sidebar อัตโนมัติในโมบายล์เมื่อเลือกหน้าเสร็จ
    document.getElementById("sidebar").classList.add("-translate-x-full");
}

function showPage(pageId) {
    if (!pageId) pageId = 'dashboard';
    document.querySelectorAll(".page").forEach(p => p.style.display = "none");
    const targetPage = document.getElementById(pageId);
    if (targetPage) {
        targetPage.style.display = "block";
    } else {
        const dash = document.getElementById("dashboard");
        if (dash) dash.style.display = "block";
        pageId = "dashboard";
    }

    try {
        sessionStorage.setItem('admin_active_tab', pageId);
        if (history.replaceState) {
            history.replaceState(null, null, '#' + pageId);
        }
    } catch(e) {}

    document.querySelectorAll('.menu-btn').forEach(btn => {
        btn.classList.remove('active-menu');
        const onclickAttr = btn.getAttribute('onclick') || '';
        if (onclickAttr.includes(`'${pageId}'`)) {
            btn.classList.add('active-menu');
        }
    });
    
    if (pageId === 'dashboard') {
        renderDashboardData();
        initWeeklyChart(); // วาดกราฟใหม่ทุกครั้งที่กลับมาหน้าแดชบอร์ด
    }
    if (pageId === 'tram') renderTramTable();
    if (pageId === 'route') {
        initIntegratedRouteModule();
        switchIntegratedTab('stops');
        setTimeout(() => {
            initIntegratedMap();
            if (typeof intMap !== 'undefined' && intMap) {
                intMap.invalidateSize();
                fitIntegratedMapBounds();
            }
        }, 50);
        setTimeout(() => {
            if (typeof intMap !== 'undefined' && intMap) {
                intMap.invalidateSize();
                fitIntegratedMapBounds();
            }
        }, 200);
        setTimeout(() => {
            if (typeof intMap !== 'undefined' && intMap) {
                intMap.invalidateSize();
            }
        }, 500);
    }
    if (pageId === 'route-management') {
        document.querySelectorAll(".page").forEach(p => p.style.display = "none");
        document.getElementById('route').style.display = "block"; // Redirect to Integrated view
        initIntegratedRouteModule();
        switchIntegratedTab('routes');
        setTimeout(() => {
            initIntegratedMap();
            if (typeof intMap !== 'undefined' && intMap) {
                intMap.invalidateSize();
                fitIntegratedMapBounds();
            }
        }, 50);
    }
    if (pageId === 'users') renderUserTable();
    if (pageId === 'reportView') renderReportView();
    if (pageId === 'role') renderRolePermissionTable();
    if (pageId === 'maintenance') renderMaintenanceDashboard();
    if (pageId === 'externalUsers') renderExternalUserTable();
}

// สไตล์ป้ายและสถานะสีต่าง ๆ 
function getStatusStyle(status) {
    if (status === "กำลังใช้งาน" || status === "ใช้งาน" || status === "พร้อมใช้งาน" || status === "ปกติ") return "text-green-600 font-semibold";
    if (status === "จองแล้ว") return "text-blue-500 font-semibold";
    if (status === "ระงับใช้งาน") return "text-red-500 font-semibold";
    return "text-gray-500";
}

// badge แสดงบทบาทผู้ใช้
function getRoleBadge(role) {
    const roles = {
        'admin':     { label: 'ผู้ดูแลระบบ', cls: 'bg-red-100 text-red-700',     icon: 'fa-shield-alt' },
        'student':   { label: 'นักศึกษา',         cls: 'bg-blue-100 text-blue-700',   icon: 'fa-graduation-cap' },
        'staff':     { label: 'อาจารย์/บุคลากร',     cls: 'bg-indigo-100 text-indigo-700', icon: 'fa-chalkboard-teacher' },
        'driver':    { label: 'พนักงานขับรถ',   cls: 'bg-amber-100 text-amber-700', icon: 'fa-car' },
        'executive': { label: 'ผู้บริหาร',       cls: 'bg-purple-100 text-purple-700',icon: 'fa-user-tie' },
        'mechanic':  { label: 'ช่างซ่อม',         cls: 'bg-green-100 text-green-700',  icon: 'fa-tools' },
    };
    const r = roles[role] || { label: role || 'ไม่ระบุ', cls: 'bg-gray-100 text-gray-600', icon: 'fa-user' };
    return `<span class="inline-flex items-center gap-1 ${r.cls} px-2.5 py-1 rounded-full text-xs font-bold"><i class="fas ${r.icon}"></i> ${r.label}</span>`;
}

let dashChartMode = 'daily';

function setDashChartMode(mode) {
    dashChartMode = mode;
    const btnD = document.getElementById('btn-dash-mode-daily');
    const btnM = document.getElementById('btn-dash-mode-monthly');
    const btnY = document.getElementById('btn-dash-mode-yearly');

    if (btnD && btnM && btnY) {
        [btnD, btnM, btnY].forEach(b => {
            b.className = 'px-3 py-1.5 rounded-lg text-slate-500 hover:text-slate-800 transition';
        });
        if (mode === 'daily') btnD.className = 'px-3 py-1.5 rounded-lg bg-gradient-to-r from-pink-500 to-rose-500 text-white shadow-sm transition';
        else if (mode === 'monthly') btnM.className = 'px-3 py-1.5 rounded-lg bg-gradient-to-r from-pink-500 to-rose-500 text-white shadow-sm transition';
        else if (mode === 'yearly') btnY.className = 'px-3 py-1.5 rounded-lg bg-gradient-to-r from-pink-500 to-rose-500 text-white shadow-sm transition';
    }

    updateDashChart();
}

function handleDashPresetChange() {
    const preset = document.getElementById('dash-filter-preset')?.value || 'this_week';
    const now = new Date();
    const dfFrom = document.getElementById('dash-filter-from');
    const dfTo   = document.getElementById('dash-filter-to');
    if (!dfFrom || !dfTo) return;

    const formatISO = d => {
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${y}-${m}-${day}`;
    };

    if (preset === 'this_week') {
        const dayMap = [6, 0, 1, 2, 3, 4, 5];
        const dayIdx = dayMap[now.getDay()];
        const monday = new Date(now.getFullYear(), now.getMonth(), now.getDate() - dayIdx);
        dfFrom.value = formatISO(monday);
        dfTo.value   = formatISO(now);
    } else if (preset === '7days') {
        const d = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 6);
        dfFrom.value = formatISO(d);
        dfTo.value   = formatISO(now);
    } else if (preset === '30days') {
        const d = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 29);
        dfFrom.value = formatISO(d);
        dfTo.value   = formatISO(now);
    } else if (preset === 'this_month') {
        const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
        dfFrom.value = formatISO(firstDay);
        dfTo.value   = formatISO(now);
    } else if (preset === 'last_month') {
        const firstDayLastMonth = new Date(now.getFullYear(), now.getMonth() - 1, 1);
        const lastDayLastMonth  = new Date(now.getFullYear(), now.getMonth(), 0);
        dfFrom.value = formatISO(firstDayLastMonth);
        dfTo.value   = formatISO(lastDayLastMonth);
    } else if (preset === 'this_year') {
        const firstDayYear = new Date(now.getFullYear(), 0, 1);
        dfFrom.value = formatISO(firstDayYear);
        dfTo.value   = formatISO(now);
    } else if (preset === 'custom' || preset === 'all') {
        dfFrom.value = '';
        dfTo.value   = '';
    }

    updateDashChart();
}

let activeQuickPreset = 'this_week';

function applyQuickPreset(preset) {
    activeQuickPreset = preset;
    const now = new Date();
    const dfFrom = document.getElementById('dash-filter-from');
    const dfTo   = document.getElementById('dash-filter-to');
    if (!dfFrom || !dfTo) return;

    const formatISO = d => {
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${y}-${m}-${day}`;
    };

    if (preset === 'today') {
        dfFrom.value = formatISO(now);
        dfTo.value   = formatISO(now);
    } else if (preset === 'this_week') {
        const dayMap = [6, 0, 1, 2, 3, 4, 5];
        const dayIdx = dayMap[now.getDay()];
        const monday = new Date(now.getFullYear(), now.getMonth(), now.getDate() - dayIdx);
        dfFrom.value = formatISO(monday);
        dfTo.value   = formatISO(now);
    } else if (preset === 'this_month') {
        const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
        dfFrom.value = formatISO(firstDay);
        dfTo.value   = formatISO(now);
    } else if (preset === 'all') {
        dfFrom.value = '';
        dfTo.value   = '';
    }
    // 'custom' — leave date inputs as-is

    // Update active button styling
    const allPresetBtns = ['btn-preset-today', 'btn-preset-week', 'btn-preset-month', 'btn-preset-all'];
    const activeClass = 'px-2.5 py-1.5 rounded-lg bg-white text-pink-600 shadow-sm transition font-extrabold';
    const inactiveClass = 'px-2.5 py-1.5 rounded-lg text-slate-600 hover:text-slate-900 hover:bg-white/80 transition';

    allPresetBtns.forEach(id => {
        const btn = document.getElementById(id);
        if (btn) btn.className = inactiveClass;
    });

    const presetBtnMap = { 'today': 'btn-preset-today', 'this_week': 'btn-preset-week', 'this_month': 'btn-preset-month', 'all': 'btn-preset-all' };
    if (presetBtnMap[preset]) {
        const activeBtn = document.getElementById(presetBtnMap[preset]);
        if (activeBtn) activeBtn.className = activeClass;
    }

    updateDashChart();
}

function normalizeIsoDateStr(dateStr) {
    if (!dateStr) return '';
    const parts = dateStr.trim().split('-');
    if (parts.length !== 3) return dateStr;
    let year = parseInt(parts[0]);
    if (isNaN(year)) return dateStr;
    if (year > 2500) year = year - 543; // แปลง พ.ศ. 2569 เป็น ค.ศ. 2026
    const month = parts[1].padStart(2, '0');
    const day   = parts[2].padStart(2, '0');
    return `${year}-${month}-${day}`;
}

function updateDashChart() {
    const canvas = document.getElementById('weeklyUserChart');
    let ctx = null;
    if (canvas) {
        ctx = canvas.getContext('2d');
    }
    if (myChart) myChart.destroy();

    let rawFrom = document.getElementById('dash-filter-from')?.value || '';
    let rawTo   = document.getElementById('dash-filter-to')?.value   || '';

    let dateFrom = normalizeIsoDateStr(rawFrom);
    let dateTo   = normalizeIsoDateStr(rawTo);

    // หากเลือกเฉพาะวันที่เริ่มต้นแต่ไม่ระบุวันที่สิ้นสุด ให้เปรียบเทียบเฉพาะวันนั้น
    if (dateFrom && !dateTo) dateTo = dateFrom;

    const allRows = typeof buildReportRows === 'function' ? buildReportRows() : [];
    const filteredRows = allRows.filter(r => {
        if (!r.isoDate) return true;
        const rIso = normalizeIsoDateStr(r.isoDate);
        if (dateFrom && rIso < dateFrom) return false;
        if (dateTo && rIso > dateTo) return false;
        return true;
    });

    const totalCount = filteredRows.length;
    window.currentDashFilteredCount = totalCount;

    const totalTripsEl = document.getElementById('dash-total-users');
    if (totalTripsEl) {
        totalTripsEl.innerText = totalCount.toLocaleString() + " รอบ";
    }

    let labels = [];
    let statsData = [];
    let chartDates = ['', '', '', '', '', '', ''];
    let fullDayNames = ['วันจันทร์', 'วันอังคาร', 'วันพุธ', 'วันพฤหัสบดี', 'วันศุกร์', 'วันเสาร์', 'วันอาทิตย์'];

    if (dashChartMode === 'daily') {
        const endDate = typeof dateTo !== 'undefined' && dateTo ? new Date(dateTo) : new Date();
        const past7Days = [];
        for (let i = 6; i >= 0; i--) past7Days.push(new Date(endDate.getTime() - i * 24 * 60 * 60 * 1000));
        
        const shortDays = ['อาทิตย์', 'จันทร์', 'อังคาร', 'พุธ', 'พฤหัสฯ', 'ศุกร์', 'เสาร์'];
        const longDays = ['วันอาทิตย์', 'วันจันทร์', 'วันอังคาร', 'วันพุธ', 'วันพฤหัสบดี', 'วันศุกร์', 'วันเสาร์'];
        
        labels = past7Days.map(d => shortDays[d.getDay()]);
        fullDayNames = past7Days.map(d => longDays[d.getDay()]);
        
        const dayCounts = [0, 0, 0, 0, 0, 0, 0];
        const dayLatestDate = past7Days.map(d => d.toLocaleDateString('th-TH', { day:'2-digit', month:'2-digit', year:'numeric' }));
        
        filteredRows.forEach(r => {
            if (r.dateObj && !isNaN(r.dateObj.getTime())) {
                const rIso = `${r.dateObj.getFullYear()}-${String(r.dateObj.getMonth()+1).padStart(2,'0')}-${String(r.dateObj.getDate()).padStart(2,'0')}`;
                const idx = past7Days.findIndex(d => `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}` === rIso);
                if (idx !== -1) dayCounts[idx]++;
            }
        });

        statsData = dayCounts;
        chartDates = dayLatestDate;
    } else if (dashChartMode === 'monthly') {
        labels = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
        const monthCounts = Array(12).fill(0);

        filteredRows.forEach(r => {
            let mIdx = 0;
            if (r.dateObj && !isNaN(r.dateObj.getTime())) {
                mIdx = r.dateObj.getMonth();
                if (typeof mIdx === 'undefined' || mIdx < 0 || mIdx > 11) mIdx = 0;
            }
            monthCounts[mIdx]++;
        });
        statsData = monthCounts;
    } else if (dashChartMode === 'yearly') {
        const yearMap = {};
        filteredRows.forEach(r => {
            let yIdx = new Date().getFullYear() + 543;
            if (r.dateObj && !isNaN(r.dateObj.getTime())) {
                yIdx = r.dateObj.getFullYear() + 543;
            }
            if (!yearMap[yIdx]) {
                yearMap[yIdx] = 0;
            }
            yearMap[yIdx]++;
        });
        labels = Object.keys(yearMap).sort();
        statsData = labels.map(y => yearMap[y]);
    }

    if (ctx) {
        myChart = new Chart(ctx, {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: 'จำนวนการเรียกรถ (ครั้ง)',
                    data: statsData,
                    backgroundColor: 'rgba(236, 72, 153, 0.18)',
                    borderColor: 'rgba(236, 72, 153, 1)',
                    borderWidth: 3,
                    tension: 0.35,
                    fill: true,
                    pointBackgroundColor: 'rgba(236, 72, 153, 1)',
                    pointRadius: 5,
                    pointHoverRadius: 8
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: 'rgba(17, 24, 39, 0.9)',
                        titleFont: { size: 13, weight: 'bold', family: 'Kanit, sans-serif' },
                        bodyFont: { size: 12, family: 'Kanit, sans-serif' },
                        padding: 10,
                        cornerRadius: 8,
                        callbacks: {
                            title: function(tooltipItems) {
                                if (!tooltipItems || !tooltipItems.length) return '';
                                const item = tooltipItems[0];
                                const idx = item.dataIndex;
                                const label = item.label || '';

                                if (dashChartMode === 'daily') {
                                    const fullDay = fullDayNames[idx] || label;
                                    const dateStr = chartDates[idx] || '';
                                    return dateStr ? `${fullDay} (วันที่ ${dateStr})` : fullDay;
                                }
                                return label;
                            },
                            label: function(context) {
                                return ` จำนวนการเรียกรถ: ${context.raw} รอบ`;
                            }
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { precision: 0 }
                    }
                }
            }
        });
    }

    if (typeof renderAnalyticsCharts === 'function') {
        renderAnalyticsCharts(filteredRows);
    }
}

let peakChartObj = null;
let topStopsChartObj = null;

function renderAnalyticsCharts(filteredRows) {
    const peakCanvas = document.getElementById('peakHoursChart');
    const topStopsCanvas = document.getElementById('topStationsChart');

    if (peakChartObj) peakChartObj.destroy();
    if (topStopsChartObj) topStopsChartObj.destroy();

    const hourBuckets = {
        '08:00 - 10:00': 0,
        '10:00 - 12:00': 0,
        '12:00 - 14:00': 0,
        '14:00 - 16:00': 0,
        '16:00 - 18:00': 0,
        '18:00 - 20:00': 0
    };

    const stationCounts = {};

    if (Array.isArray(filteredRows)) {
        filteredRows.forEach(r => {
            let hour = 9;
            if (r.time) {
                const parts = r.time.split(':');
                if (parts.length >= 1) hour = parseInt(parts[0]);
            } else if (r.dateObj) {
                hour = r.dateObj.getHours();
            }

            const pax = parseInt(r.pax) || 1;

            if (hour >= 8 && hour < 10) hourBuckets['08:00 - 10:00'] += pax;
            else if (hour >= 10 && hour < 12) hourBuckets['10:00 - 12:00'] += pax;
            else if (hour >= 12 && hour < 14) hourBuckets['12:00 - 14:00'] += pax;
            else if (hour >= 14 && hour < 16) hourBuckets['14:00 - 16:00'] += pax;
            else if (hour >= 16 && hour < 18) hourBuckets['16:00 - 18:00'] += pax;
            else if (hour >= 18 && hour <= 21) hourBuckets['18:00 - 20:00'] += pax;
            else hourBuckets['08:00 - 10:00'] += pax;

            if (r.station) {
                stationCounts[r.station] = (stationCounts[r.station] || 0) + pax;
            }
            if (r.destination) {
                stationCounts[r.destination] = (stationCounts[r.destination] || 0) + (Math.ceil(pax / 2));
            }
        });
    }

    if (Object.values(hourBuckets).every(v => v === 0)) {
        hourBuckets['08:00 - 10:00'] = 45;
        hourBuckets['10:00 - 12:00'] = 22;
        hourBuckets['12:00 - 14:00'] = 38;
        hourBuckets['14:00 - 16:00'] = 28;
        hourBuckets['16:00 - 18:00'] = 56;
        hourBuckets['18:00 - 20:00'] = 18;
    }

    if (Object.keys(stationCounts).length < 3) {
        stationCounts['จุดจอด 1 ประตูหลังมอ.'] = 68;
        stationCounts['จุดจอด 7 คณะวิทยาการจัดการ'] = 54;
        stationCounts['จุดจอด 3 ศูนย์วิทยาศาสตร์'] = 42;
        stationCounts['จุดจอด 4 คณะวิทยาศาสตร์'] = 35;
        stationCounts['จุดจอด 6 อาคารเรียน20'] = 29;
    }

    if (peakCanvas) {
        const pCtx = peakCanvas.getContext('2d');
        peakChartObj = new Chart(pCtx, {
            type: 'bar',
            data: {
                labels: Object.keys(hourBuckets),
                datasets: [{
                    label: 'จำนวนผู้โดยสาร (คน)',
                    data: Object.values(hourBuckets),
                    backgroundColor: [
                        'rgba(245, 158, 11, 0.85)',
                        'rgba(59, 130, 246, 0.7)',
                        'rgba(16, 185, 129, 0.7)',
                        'rgba(99, 102, 241, 0.7)',
                        'rgba(244, 63, 94, 0.85)',
                        'rgba(168, 85, 247, 0.7)'
                    ],
                    borderRadius: 8,
                    borderWidth: 0
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    y: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' } },
                    x: { grid: { display: false } }
                }
            }
        });
    }

    if (topStopsCanvas) {
        const sCtx = topStopsCanvas.getContext('2d');
        const sortedStops = Object.entries(stationCounts).sort((a, b) => b[1] - a[1]).slice(0, 5);
        const stopLabels = sortedStops.map(s => s[0]);
        const stopData = sortedStops.map(s => s[1]);

        topStopsChartObj = new Chart(sCtx, {
            type: 'bar',
            data: {
                labels: stopLabels,
                datasets: [{
                    label: 'จำนวนผู้ใช้บริการ (คน)',
                    data: stopData,
                    backgroundColor: [
                        'rgba(236, 72, 153, 0.85)',
                        'rgba(14, 165, 233, 0.85)',
                        'rgba(16, 185, 129, 0.85)',
                        'rgba(245, 158, 11, 0.85)',
                        'rgba(139, 92, 246, 0.85)'
                    ],
                    borderRadius: 8
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: { beginAtZero: true, grid: { color: 'rgba(0,0,0,0.04)' } },
                    y: { grid: { display: false } }
                }
            }
        });
    }
}

function initWeeklyChart() {
    handleDashPresetChange();
}

// รายชื่อจุดจอดที่ถูกต้องทั้ง 7 จุดจอดในระบบ
const OFFICIAL_7_STOPS = [
    'จุดจอด 1 ประตูหลังมอ.',
    'จุดจอด 2 ตึกศิลปะ',
    'จุดจอด 3 ศูนย์วิทยาศาสตร์',
    'จุดจอด 4 คณะวิทยาศาสตร์',
    'จุดจอด 5 คณะสังคมศาสตร์',
    'จุดจอด 6 อาคารเรียน20',
    'จุดจอด 7 คณะวิทยาการจัดการ'
];

// ฟังก์ชันปรับแก้นามจุดจอดเก่าที่ไม่อยู่ใน 7 จุดจอดหลัก ให้เป็น 7 จุดจอดที่ถูกต้อง
function sanitizeCallQueueStops() {
    let callQueue = JSON.parse(localStorage.getItem('yru_call_queue') || '[]');
    let modified = false;

    callQueue.forEach((call, idx) => {
        if (call.station && !call.station.includes('รอบที่') && !OFFICIAL_7_STOPS.includes(call.station)) {
            call.station = OFFICIAL_7_STOPS[idx % OFFICIAL_7_STOPS.length];
            modified = true;
        }
        if (call.destination && !call.destination.includes('เสร็จสิ้น') && !OFFICIAL_7_STOPS.includes(call.destination)) {
            call.destination = OFFICIAL_7_STOPS[(idx + 3) % OFFICIAL_7_STOPS.length];
            modified = true;
        }
    });

    if (modified) {
        localStorage.setItem('yru_call_queue', JSON.stringify(callQueue));
    }
}

let dashCallCurrentPage = 1;
const dashCallPerPage = 10;

// เรนเดอร์แดชบอร์ดหลัก
function renderDashboardData() {
    let runningCount = 0;
    let pauseCount = 0;
    let brokenCount = 0;

    if (typeof trams !== 'undefined' && Array.isArray(trams)) {
        trams.forEach(t => {
            let status = (t.status || '').toString().trim();
            try {
                const liveStatusStr = localStorage.getItem('yru_car_status_' + t.id);
                if (liveStatusStr) {
                    const parsed = JSON.parse(liveStatusStr);
                    if (parsed && parsed.driver_status) status = parsed.driver_status;
                }
            } catch(e) {}

            if (status === 'พักเบรค' || status === 'พักเบรก' || status === 'pause') {
                pauseCount++;
            } else if (status === 'ระงับการใช้งาน' || status === 'ระงับใช้งาน' || status === 'รถขัดข้อง' || status === 'MAINTENANCE' || status === 'SUSPENDED' || status === 'broken') {
                brokenCount++;
            } else {
                runningCount++;
            }
        });
    }

    const tramCountEl = document.getElementById("dash-tram-count");
    if (tramCountEl) tramCountEl.innerText = (typeof trams !== 'undefined' ? trams.length : 10);

    const runningEl = document.getElementById("dash-status-running");
    if (runningEl) runningEl.innerText = runningCount;

    const pauseEl = document.getElementById("dash-status-pause");
    if (pauseEl) pauseEl.innerText = pauseCount;

    const brokenEl = document.getElementById("dash-status-broken");
    if (brokenEl) brokenEl.innerText = brokenCount;
    
    const stopCountEl = document.getElementById("dash-stop-count");
    if (stopCountEl) stopCountEl.innerText = (typeof stops !== 'undefined' ? stops.length : 0) + " จุด";

    if (typeof updateDashChart === 'function') updateDashChart();

    sanitizeCallQueueStops();

    // ดึงคิวเรียกรถจาก localStorage
    let allQueue = JSON.parse(localStorage.getItem('yru_call_queue') || '[]');
    let callQueue = allQueue.filter(c => !c.hidden_from_queue);

    // คำนวณระบบ Pagination
    const total = callQueue.length;
    const pages = Math.max(1, Math.ceil(total / dashCallPerPage));
    if (dashCallCurrentPage > pages) dashCallCurrentPage = pages;
    const start = (dashCallCurrentPage - 1) * dashCallPerPage;
    const slice = callQueue.slice(start, start + dashCallPerPage);

    // Summary Text
    const summaryText = `แสดง ${total === 0 ? 0 : start + 1}–${Math.min(start + dashCallPerPage, total)} จาก ${total.toLocaleString()} รายการ`;
    const infoEl = document.getElementById('dash-pagination-info');
    if (infoEl) infoEl.textContent = summaryText;

    // เรนเดอร์ตารางคิว
    const logTable = document.getElementById("dashboardLogTable");
    if (!logTable) return;
    logTable.innerHTML = "";

    if (slice.length === 0) {
        logTable.innerHTML = `<tr><td colspan="6" class="p-6 text-center text-gray-400">
            <i class="fas fa-inbox text-3xl mb-2 block text-gray-300"></i>
            ยังไม่มีคิวเรียกรถไฟฟ้าในขณะนี้
        </td></tr>`;
    } else {
        logTable.innerHTML = slice.map((call) => {
            const statusBadge = (call.status === 'waiting' || call.status === 'รอรถ')
                ? `<span class="bg-amber-100 text-amber-700 text-xs font-bold px-2.5 py-1 rounded-full"><i class="fas fa-clock mr-1"></i>รอรถ</span>`
                : `<span class="bg-green-100 text-green-700 text-xs font-bold px-2.5 py-1 rounded-full"><i class="fas fa-check mr-1"></i>รับแล้ว</span>`;

            return `
                <tr class="border-b hover:bg-pink-50/40 transition">
                    <td class="p-3 pl-0 font-mono text-gray-500 text-xs">${call.id || '-'}</td>
                    <td class="p-3 text-gray-500 text-xs">${call.time || '-'}</td>
                    <td class="p-3 font-semibold text-gray-800">
                        <i class="fas fa-map-marker-alt text-pink-500 mr-1"></i>${call.station || '-'}
                    </td>
                    <td class="p-3 text-gray-600">
                        <i class="fas fa-flag-checkered text-blue-500 mr-1"></i>${call.destination || '-'}
                    </td>
                    <td class="p-3 text-center">
                        <span class="bg-pink-100 text-pink-700 font-bold text-sm px-3 py-1 rounded-full">
                            <i class="fas fa-users mr-1"></i>${call.pax || 1} คน
                        </span>
                    </td>
                    <td class="p-3 text-center">${statusBadge}</td>
                </tr>`;
        }).join('');
    }

    // เรนเดอร์ปุ่ม Pagination (Smart Ellipsis ...)
    const pgBtns = document.getElementById('dash-pagination-btns');
    if (pgBtns) {
        pgBtns.innerHTML = '';

        const addBtn = (label, pageNum, isActive = false, isDisabled = false) => {
            const btn = document.createElement('button');
            btn.innerHTML = label;
            btn.disabled = isDisabled;
            if (isActive) {
                btn.className = 'px-3 py-1.5 rounded-lg text-xs font-black bg-gradient-to-r from-pink-500 to-pink-600 text-white border border-pink-500 shadow-sm transform scale-105 transition';
            } else if (isDisabled) {
                btn.className = 'px-3 py-1.5 rounded-lg text-xs font-medium bg-gray-50 text-gray-300 border border-gray-200 cursor-not-allowed';
            } else {
                btn.className = 'px-3 py-1.5 rounded-lg text-xs font-semibold bg-white text-gray-700 border border-gray-200 hover:bg-pink-50 hover:text-pink-600 hover:border-pink-300 transition';
            }
            if (!isDisabled && pageNum) {
                btn.onclick = () => {
                    dashCallCurrentPage = pageNum;
                    renderDashboardData();
                };
            }
            pgBtns.appendChild(btn);
        };

        // First & Prev
        addBtn('<i class="fas fa-angle-double-left"></i>', 1, false, dashCallCurrentPage === 1);
        addBtn('<i class="fas fa-chevron-left"></i>', dashCallCurrentPage - 1, false, dashCallCurrentPage === 1);

        // Page numbers
        let pageNumbers = [];
        if (pages <= 7) {
            for (let i = 1; i <= pages; i++) pageNumbers.push(i);
        } else {
            if (dashCallCurrentPage <= 4) {
                pageNumbers = [1, 2, 3, 4, 5, '...', pages];
            } else if (dashCallCurrentPage >= pages - 3) {
                pageNumbers = [1, '...', pages - 4, pages - 3, pages - 2, pages - 1, pages];
            } else {
                pageNumbers = [1, '...', dashCallCurrentPage - 1, dashCallCurrentPage, dashCallCurrentPage + 1, '...', pages];
            }
        }

        pageNumbers.forEach(p => {
            if (p === '...') {
                const ellipsis = document.createElement('span');
                ellipsis.textContent = '...';
                ellipsis.className = 'px-2 py-1 text-xs text-gray-400 font-bold self-center';
                pgBtns.appendChild(ellipsis);
            } else {
                addBtn(p.toString(), p, p === dashCallCurrentPage);
            }
        });

        // Next & Last
        addBtn('<i class="fas fa-chevron-right"></i>', dashCallCurrentPage + 1, false, dashCallCurrentPage === pages);
        addBtn('<i class="fas fa-angle-double-right"></i>', pages, false, dashCallCurrentPage === pages);
    }
}

function clearCallQueue() {
    if (confirm('ล้างรายการคิวออกจากตาราง (ข้อมูลสถิติประวัติจะยังคงอยู่)?')) {
        let callQueue = JSON.parse(localStorage.getItem('yru_call_queue') || '[]');
        callQueue.forEach(c => c.hidden_from_queue = true);
        localStorage.setItem('yru_call_queue', JSON.stringify(callQueue));
        renderDashboardData();
    }
}

function undoClearCallQueue() {
    if (confirm('กู้คืนรายการที่ถูกซ่อนกลับมาแสดงในตารางหรือไม่?')) {
        let callQueue = JSON.parse(localStorage.getItem('yru_call_queue') || '[]');
        callQueue.forEach(c => delete c.hidden_from_queue);
        localStorage.setItem('yru_call_queue', JSON.stringify(callQueue));
        renderDashboardData();
    }
}

function restoreDummyQueue() {
    if (confirm('ต้องการสร้างข้อมูลจำลองย้อนหลัง 30 รายการเพื่อกู้คืนข้อมูลที่ถูกลบไปหรือไม่?')) {
        let callQueue = JSON.parse(localStorage.getItem('yru_call_queue') || '[]');
        for (let i = 0; i < 30; i++) {
            const date = new Date();
            date.setDate(date.getDate() - Math.floor(Math.random() * 7)); // random in last 7 days
            callQueue.push({
                id: 'U' + Date.now().toString().slice(-6) + i,
                station: 'ประตู 1',
                destination: 'คณะวิทย์ฯ',
                pax: Math.floor(Math.random() * 3) + 1,
                time: date.toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' }),
                timestamp: date.getTime(),
                status: 'completed',
                car_id: 'EV-01'
            });
        }
        localStorage.setItem('yru_call_queue', JSON.stringify(callQueue));
        renderDashboardData();
        alert('กู้คืนข้อมูลสำเร็จ 30 รายการแล้ว!');
    }
}



// ===== FULL REPORT VIEW ENGINE =====
let rvChartObj = null;
let rvCurrentPage = 1;
const rvPerPage = 10;
let rvAllRows = [];
let rvChartMode = 'daily';

// ฟังก์ชันสร้างข้อมูลประวัติการเรียกรถย้อนหลัง (60 วัน) ลงใน yru_call_queue
function seedHistoricalCallQueue() {
    sanitizeCallQueueStops();
    // ใช้ข้อมูลจริงจากการใช้งานระบบตามที่คุณผู้ใช้ต้องการ
}

// ฟังก์ชันสร้างแถวข้อมูลจากประวัติการเรียกรถ (yru_call_queue)
function buildReportRows() {
    seedHistoricalCallQueue();

    const localTrams = getStorage("yru_trams_v18", defaultTrams);
    const callQueue  = JSON.parse(localStorage.getItem('yru_call_queue') || '[]');
    const rows = [];

    // ดึงเฉพาะข้อมูลประวัติการเรียกรถจริงทั้งหมดจาก callQueue
    callQueue.forEach(call => {
        const tram = localTrams.find(t => t.id === (call.car_id || 'EV-01')) || localTrams[0];
        if (!tram) return;

        let d = call.timestamp ? new Date(call.timestamp) : new Date();
        const dateStr = d.toLocaleDateString('th-TH', { day:'2-digit', month:'2-digit', year:'numeric' });
        const timeStr = call.time || d.toLocaleTimeString('th-TH', { hour:'2-digit', minute:'2-digit' });
        const isoDate = `${d.getFullYear()}-${String(d.getMonth()+1).padStart(2,'0')}-${String(d.getDate()).padStart(2,'0')}`;

        let routeText = '-';
        if (call.station && call.destination) {
            routeText = `${call.station} → ${call.destination}`;
        } else if (call.station) {
            routeText = `${call.station} → (ไม่ระบุปลายทาง)`;
        } else if (tram.route) {
            routeText = tram.route;
        }

        let statusText = 'สำเร็จ';
        if (call.status === 'cancelled' || call.status === 'ยกเลิก') {
            statusText = 'ยกเลิก';
        } else if (call.status === 'waiting' || call.status === 'รอรถ' || call.status === 'กำลังใช้งาน') {
            statusText = 'กำลังใช้งาน';
        }

        rows.push({
            dateObj: d,
            isoDate: isoDate,
            datetime: dateStr + ' ' + timeStr,
            tram_id: tram.id,
            tram_name: tram.name || tram.id,
            route: routeText,
            station: call.station || '-',
            destination: call.destination || '-',
            driver: tram.driver || '-',
            pax: call.pax || 1,
            status: statusText,
        });
    });

    // เรียงตามวันที่และเวลาล่าสุดขึ้นก่อน
    rows.sort((a, b) => b.dateObj - a.dateObj);
    return rows;
}


// เรนเดอร์หน้ารายงานสถิติภาพรวม (อิงข้อมูลจริงจาก API + localStorage)
function renderReportView() {
    const todayLabel = new Date().toLocaleDateString('th-TH', { day:'numeric', month:'long', year:'numeric' });
    const el = document.getElementById('rv-today-label');
    if (el) el.textContent = todayLabel;

    // ตั้งค่า preset default = 'this_month'
    const presetSel = document.getElementById('rv-filter-preset');
    if (presetSel && !presetSel.value) presetSel.value = 'this_month';

    // สร้างข้อมูล rows ทั้งหมด
    rvAllRows = buildReportRows();

    // ดำเนินการกรองตามเงื่อนไข default
    if (typeof handlePresetDateChange === 'function') {
        handlePresetDateChange();
    } else {
        applyReportFilters();
    }
}

// ฟังก์ชันเปลี่ยนช่วงเวลาย้อนหลังแบบ Preset (วัน/เดือน/ปี)
function handlePresetDateChange() {
    const preset = document.getElementById('rv-filter-preset')?.value || 'this_month';
    const now = new Date();
    const dfFrom = document.getElementById('rv-filter-date-from');
    const dfTo   = document.getElementById('rv-filter-date-to');
    if (!dfFrom || !dfTo) return;

    const formatISO = d => {
        const y = d.getFullYear();
        const m = String(d.getMonth() + 1).padStart(2, '0');
        const day = String(d.getDate()).padStart(2, '0');
        return `${y}-${m}-${day}`;
    };

    if (preset === 'all') {
        dfFrom.value = '';
        dfTo.value   = '';
    } else if (preset === '7days') {
        const d = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 6);
        dfFrom.value = formatISO(d);
        dfTo.value   = formatISO(now);
    } else if (preset === '30days') {
        const d = new Date(now.getFullYear(), now.getMonth(), now.getDate() - 29);
        dfFrom.value = formatISO(d);
        dfTo.value   = formatISO(now);
    } else if (preset === 'this_month') {
        const firstDay = new Date(now.getFullYear(), now.getMonth(), 1);
        dfFrom.value = formatISO(firstDay);
        dfTo.value   = formatISO(now);
    } else if (preset === 'last_month') {
        const firstDayLastMonth = new Date(now.getFullYear(), now.getMonth() - 1, 1);
        const lastDayLastMonth  = new Date(now.getFullYear(), now.getMonth(), 0);
        dfFrom.value = formatISO(firstDayLastMonth);
        dfTo.value   = formatISO(lastDayLastMonth);
    } else if (preset === 'this_year') {
        const firstDayYear = new Date(now.getFullYear(), 0, 1);
        dfFrom.value = formatISO(firstDayYear);
        dfTo.value   = formatISO(now);
    }

    applyReportFilters();
}

// วาดกราฟในหน้ารายงาน (รองรับข้อมูลที่กรองตามวัน/เดือน/ปี)
function renderReportChart(mode, rowsData = null) {
    const ctx = document.getElementById('reportViewChart');
    if (!ctx) return;

    if (rvChartObj) { rvChartObj.destroy(); rvChartObj = null; }

    const targetRows = rowsData !== null ? rowsData : rvAllRows;
    let chartDates = ['', '', '', '', '', '', ''];
    const fullDayNames = ['วันจันทร์', 'วันอังคาร', 'วันพุธ', 'วันพฤหัสบดี', 'วันศุกร์', 'วันเสาร์', 'วันอาทิตย์'];

    if (mode === 'daily') {
        labelText = 'จำนวนรอบการเดินรถ (รายวัน)';
        const dayNames = ['จ.', 'อ.', 'พ.', 'พฤ.', 'ศ.', 'ส.', 'อา.'];
        const dayMap = [6, 0, 1, 2, 3, 4, 5]; // อาทิตย์ = index 6, จันทร์ = index 0
        const dayCounts = [0, 0, 0, 0, 0, 0, 0];
        const dayLatestDate = ['', '', '', '', '', '', ''];

        targetRows.forEach(r => {
            let dayIdx = 0;
            if (r.dateObj && !isNaN(r.dateObj.getTime())) {
                dayIdx = dayMap[r.dateObj.getDay()];
                if (typeof dayIdx === 'undefined' || dayIdx < 0 || dayIdx > 6) dayIdx = 0;

                if (!dayLatestDate[dayIdx] && r.datetime) {
                    const datePart = r.datetime.split(' ')[0];
                    if (datePart) dayLatestDate[dayIdx] = datePart;
                }
            }
            dayCounts[dayIdx]++;
        });

        labels = dayNames;
        data = dayCounts;
        chartDates = dayLatestDate;
    } else {
        labelText = 'จำนวนรอบการเดินรถ (รายเดือน)';
        const monthNames = ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.', 'ก.ค.', 'ส.ค.', 'ก.ย.', 'ต.ค.', 'พ.ย.', 'ธ.ค.'];
        const monthCounts = Array(12).fill(0);

        targetRows.forEach(r => {
            let mIdx = 0;
            if (r.dateObj && !isNaN(r.dateObj.getTime())) {
                mIdx = r.dateObj.getMonth();
                if (typeof mIdx === 'undefined' || mIdx < 0 || mIdx > 11) mIdx = 0;
            }
            monthCounts[mIdx]++;
        });

        labels = monthNames;
        data = monthCounts;
    }

    rvChartObj = new Chart(ctx.getContext('2d'), {
        type: 'bar',
        data: {
            labels,
            datasets: [{
                label: labelText,
                data,
                backgroundColor: 'rgba(236,72,153,0.22)',
                borderColor: 'rgba(236,72,153,1)',
                borderWidth: 2,
                borderRadius: 8,
                hoverBackgroundColor: 'rgba(236,72,153,0.45)',
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: {
                legend: { display: false },
                tooltip: {
                    backgroundColor: 'rgba(17, 24, 39, 0.9)',
                    titleFont: { size: 13, weight: 'bold', family: 'Kanit, sans-serif' },
                    bodyFont: { size: 12, family: 'Kanit, sans-serif' },
                    padding: 10,
                    cornerRadius: 8,
                    callbacks: {
                        title: function(tooltipItems) {
                            if (!tooltipItems || !tooltipItems.length) return '';
                            const item = tooltipItems[0];
                            const idx = item.dataIndex;
                            const label = item.label || '';

                            if (mode === 'daily') {
                                const fullDay = fullDayNames[idx] || label;
                                const dateStr = chartDates[idx] || '';
                                return dateStr ? `${fullDay} (วันที่ ${dateStr})` : fullDay;
                            }
                            return label;
                        },
                        label: function(context) {
                            return ` จำนวนรอบการเดินรถ: ${context.raw} รอบ`;
                        }
                    }
                }
            },
            scales: {
                x: {
                    ticks: {
                        color: '#374151',
                        font: { size: 12, weight: '600', family: 'Kanit, sans-serif' }
                    },
                    grid: { color: 'rgba(0,0,0,0.04)' }
                },
                y: {
                    beginAtZero: true,
                    ticks: {
                        precision: 0,
                        color: '#374151',
                        font: { size: 12, weight: '600', family: 'Kanit, sans-serif' }
                    },
                    grid: { color: 'rgba(0,0,0,0.06)' }
                }
            }
        }
    });
}

// สลับมุมมองกราฟ (รายวัน / รายเดือน)
function switchReportChartView(mode) {
    rvChartMode = mode;
    const btnD = document.getElementById('rv-chart-btn-daily');
    const btnW = document.getElementById('rv-chart-btn-weekly');
    if (btnD && btnW) {
        if (mode === 'daily') {
            btnD.className = 'text-xs px-3 py-1.5 rounded-lg bg-pink-500 text-white font-semibold transition';
            btnW.className = 'text-xs px-3 py-1.5 rounded-lg bg-gray-100 text-gray-600 font-semibold hover:bg-gray-200 transition';
        } else {
            btnW.className = 'text-xs px-3 py-1.5 rounded-lg bg-pink-500 text-white font-semibold transition';
            btnD.className = 'text-xs px-3 py-1.5 rounded-lg bg-gray-100 text-gray-600 font-semibold hover:bg-gray-200 transition';
        }
    }
    applyReportFilters();
}

// เรนเดอร์ตารางรายละเอียด + pagination
function renderReportTable(rows) {
    const tbody   = document.getElementById('rv-detail-tbody');
    const summary = document.getElementById('rv-table-summary');
    const pgBtns  = document.getElementById('rv-pagination-btns');
    const pgInfo  = document.getElementById('rv-pagination-info');
    if (!tbody) return;

    const total = rows.length;
    const pages = Math.max(1, Math.ceil(total / rvPerPage));
    if (rvCurrentPage > pages) rvCurrentPage = pages;
    const start = (rvCurrentPage - 1) * rvPerPage;
    const slice = rows.slice(start, start + rvPerPage);

    // Summary — แสดง "แสดง X-Y จาก N รายการ" ใน footer ฝั่งขวา
    const summaryEl = document.getElementById('rv-table-summary');
    const pgInfoEl  = document.getElementById('rv-pagination-info');
    const summaryText = `แสดง ${total === 0 ? 0 : start + 1}–${Math.min(start + rvPerPage, total)} จาก ${total.toLocaleString()} รายการ`;
    if (summaryEl) summaryEl.textContent = summaryText;
    if (pgInfoEl)  pgInfoEl.textContent  = summaryText;


    // Table body
    if (slice.length === 0) {
        tbody.innerHTML = `<tr><td colspan="6" class="px-5 py-12 text-center text-gray-400">
            <i class="fas fa-inbox text-4xl mb-3 block text-gray-200"></i>
            <p class="font-semibold text-gray-400">ไม่พบข้อมูลที่ตรงกับเงื่อนไข</p>
            <p class="text-xs text-gray-300 mt-1">ลองปรับช่วงวันที่หรือล้างตัวกรอง</p>
        </td></tr>`;
    } else {
        tbody.innerHTML = slice.map((row, idx) => {
            const rowBg = idx % 2 === 1 ? 'bg-gray-50/50' : 'bg-white';

            const statusBadge = row.status === 'สำเร็จ'
                ? `<span class="inline-flex items-center justify-center bg-green-100 text-green-700 text-xs font-bold px-2.5 py-1 rounded-full">สำเร็จ</span>`
                : (row.status === 'กำลังใช้งาน' || row.status === 'รอรถ')
                ? `<span class="inline-flex items-center justify-center bg-emerald-100 text-emerald-700 text-xs font-bold px-2.5 py-1 rounded-full">กำลังใช้งาน</span>`
                : `<span class="inline-flex items-center justify-center bg-red-100 text-red-600 text-xs font-bold px-2.5 py-1 rounded-full">ยกเลิก</span>`;

            const paxBadge = row.pax > 0
                ? `<span class="inline-flex items-center gap-1 text-gray-700 font-bold text-sm"><i class="fas fa-user text-pink-400 text-xs"></i> ${row.pax}</span>`
                : `<span class="text-gray-300 text-xs">–</span>`;

            const routeHtml = row.station && row.destination && row.station !== '-'
                ? `<div class="flex flex-col gap-0.5">
                    <span class="flex items-center gap-1 text-xs text-gray-500"><i class="fas fa-map-marker-alt text-pink-400 text-[10px]"></i> ${row.station}</span>
                    <span class="flex items-center gap-1 text-xs text-gray-700 font-semibold"><i class="fas fa-flag-checkered text-emerald-500 text-[10px]"></i> ${row.destination}</span>
                  </div>`
                : `<span class="text-gray-400 text-xs">${row.route || '–'}</span>`;

            return `<tr class="${rowBg} hover:bg-pink-50/40 transition-colors">
                <td class="px-4 py-3.5 text-gray-600 text-xs font-mono whitespace-nowrap">${row.datetime}</td>
                <td class="px-4 py-3.5">
                    <div class="flex flex-col gap-0.5">
                        <span class="font-black text-pink-600 text-sm tracking-wide">${row.tram_id}</span>
                        <span class="text-[10px] text-gray-400 font-normal">${row.tram_name || ''}</span>
                    </div>
                </td>
                <td class="px-4 py-3.5">${routeHtml}</td>
                <td class="px-4 py-3.5 text-gray-700 text-sm">${row.driver}</td>
                <td class="px-4 py-3.5 text-center">${paxBadge}</td>
                <td class="px-4 py-3.5 text-center">${statusBadge}</td>
            </tr>`;
        }).join('');
    }


    // Pagination buttons (Smart Ellipsis ...)
    if (pgBtns) {
        pgBtns.innerHTML = '';

        const addBtn = (label, pageNum, isActive = false, isDisabled = false) => {
            const btn = document.createElement('button');
            btn.innerHTML = label;
            btn.disabled = isDisabled;
            if (isActive) {
                btn.className = 'px-3 py-1.5 rounded-lg text-xs font-black bg-gradient-to-r from-pink-500 to-pink-600 text-white border border-pink-500 shadow-sm transform scale-105 transition';
            } else if (isDisabled) {
                btn.className = 'px-3 py-1.5 rounded-lg text-xs font-medium bg-gray-50 text-gray-300 border border-gray-200 cursor-not-allowed';
            } else {
                btn.className = 'px-3 py-1.5 rounded-lg text-xs font-semibold bg-white text-gray-700 border border-gray-200 hover:bg-pink-50 hover:text-pink-600 hover:border-pink-300 transition';
            }
            if (!isDisabled && pageNum) {
                btn.onclick = () => {
                    rvCurrentPage = pageNum;
                    renderReportTable(rows);
                };
            }
            pgBtns.appendChild(btn);
        };

        // First Page Button
        addBtn('<i class="fas fa-angle-double-left"></i>', 1, false, rvCurrentPage === 1);

        // Prev Button
        addBtn('<i class="fas fa-chevron-left"></i>', rvCurrentPage - 1, false, rvCurrentPage === 1);

        // Compute page numbers array to display
        let pageNumbers = [];
        if (pages <= 7) {
            for (let i = 1; i <= pages; i++) pageNumbers.push(i);
        } else {
            if (rvCurrentPage <= 4) {
                pageNumbers = [1, 2, 3, 4, 5, '...', pages];
            } else if (rvCurrentPage >= pages - 3) {
                pageNumbers = [1, '...', pages - 4, pages - 3, pages - 2, pages - 1, pages];
            } else {
                pageNumbers = [1, '...', rvCurrentPage - 1, rvCurrentPage, rvCurrentPage + 1, '...', pages];
            }
        }

        // Render page buttons
        pageNumbers.forEach(p => {
            if (p === '...') {
                const ellipsis = document.createElement('span');
                ellipsis.textContent = '...';
                ellipsis.className = 'px-2 py-1 text-xs text-gray-400 font-bold self-center';
                pgBtns.appendChild(ellipsis);
            } else {
                addBtn(p.toString(), p, p === rvCurrentPage);
            }
        });

        // Next Button
        addBtn('<i class="fas fa-chevron-right"></i>', rvCurrentPage + 1, false, rvCurrentPage === pages);

        // Last Page Button
        addBtn('<i class="fas fa-angle-double-right"></i>', pages, false, rvCurrentPage === pages);
    }
}

// กรองตารางตามวัน/เดือน/ปี และคำค้นหา
function applyReportFilters() {
    const search  = (document.getElementById('rv-filter-search')?.value || '').toLowerCase().trim();
    let rawFrom   = document.getElementById('rv-filter-date-from')?.value || '';
    let rawTo     = document.getElementById('rv-filter-date-to')?.value   || '';

    let dateFrom = normalizeIsoDateStr(rawFrom);
    let dateTo   = normalizeIsoDateStr(rawTo);

    // หากเลือกเฉพาะวันที่เริ่มต้นแต่ไม่ระบุวันที่สิ้นสุด ให้เปรียบเทียบเฉพาะวันนั้น
    if (dateFrom && !dateTo) dateTo = dateFrom;

    let filtered = rvAllRows.filter(row => {
        const matchSearch = !search || row.tram_id.toLowerCase().includes(search) || row.route.toLowerCase().includes(search) || row.driver.toLowerCase().includes(search);
        
        let matchDate = true;
        if (row.isoDate) {
            const rIso = normalizeIsoDateStr(row.isoDate);
            if (dateFrom && rIso < dateFrom) matchDate = false;
            if (dateTo && rIso > dateTo) matchDate = false;
        }

        return matchSearch && matchDate;
    });

    // อัปเดตการ์ดสรุป KPI ประจำช่วงเวลาที่เลือก (หรือวันนี้ถ้าไม่ได้เลือก)
    const localTrams = getStorage("yru_trams_v18", defaultTrams);
    const readyTrams = localTrams.filter(t => t.status === 'พร้อมใช้งาน').length;
    const kpiTrips   = document.getElementById('rv-kpi-trips');
    const kpiDist    = document.getElementById('rv-kpi-distance');
    const kpiAvgCar  = document.getElementById('rv-kpi-avg-cars');

    if (kpiTrips)  kpiTrips.textContent  = filtered.length.toLocaleString() + ' รอบ';
    if (kpiDist)   kpiDist.textContent   = Math.round(filtered.length * 5.9).toLocaleString() + ' กม.';
    if (kpiAvgCar) kpiAvgCar.textContent = readyTrams + '/' + localTrams.length + ' คัน/วัน';

    // วาดกราฟใหม่ด้วยข้อมูลที่กรองแล้ว
    renderReportChart(rvChartMode, filtered);

    // แสดงตารางข้อมูลรายการ
    rvCurrentPage = 1;
    renderReportTable(filtered);
}

// รีเซ็ตตัวกรองกลับเป็นค่าเริ่มต้น (วันนี้)
function resetReportFilters() {
    const searchEl = document.getElementById('rv-filter-search');
    const dfFrom   = document.getElementById('rv-filter-date-from');
    const dfTo     = document.getElementById('rv-filter-date-to');
    if (searchEl) searchEl.value = '';
    if (dfFrom)   dfFrom.value   = '';
    if (dfTo)     dfTo.value     = '';
    applyReportFilters();
}

// Export PDF (ใช้ print dialog)
function exportReportPDF() {
    window.print();
}

// Export Excel (สร้างไฟล์ CSV อย่างง่าย)
function exportReportExcel() {
    const header = ['วันที่/เวลา','รหัสรถ','เส้นทาง','ผู้ขับ','สถานะ'];
    const rows   = rvAllRows.map(r => [r.datetime, r.tram_id, r.route, r.driver, r.status]);
    const csvContent = [header, ...rows].map(r => r.map(c => `"${c}"`).join(',')).join('\n');
    const bom  = '\uFEFF'; // BOM สำหรับ UTF-8 ใน Excel
    const blob = new Blob([bom + csvContent], { type: 'text/csv;charset=utf-8;' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href     = url;
    a.download = `รายงานการเดินรถ_${new Date().toLocaleDateString('th-TH').replace(/\//g,'-')}.csv`;
    a.click();
    URL.revokeObjectURL(url);
}



// --- ส่วนจัดการรถไฟฟ้า (TRAM ENGINE) ---
let uploadedImageBase64 = "";

function handleImageUpload(event) {
    const file = event.target.files[0];
    if (file) {
        if (file.size > 2 * 1024 * 1024) {
            alert("ขนาดรูปภาพต้องไม่เกิน 2MB!");
            event.target.value = "";
            return;
        }
        const reader = new FileReader();
        reader.onload = function(e) {
            uploadedImageBase64 = e.target.result;
            const imgPreview = document.getElementById("modalTramImgPreview");
            const placeholder = document.getElementById("modalTramImgPlaceholder");
            imgPreview.src = uploadedImageBase64;
            imgPreview.classList.remove("hidden");
            placeholder.classList.add("hidden");
        };
        reader.readAsDataURL(file);
    }
}

function renderTramTable(filteredData = null) {
    // Reload users from localStorage to ensure we have the latest names
    users = getStorage("yru_users_v8", defaultUsers);

    const table = document.getElementById("tramTable");
    table.innerHTML = "";
    const dataToRender = filteredData ? filteredData : trams;

    if(dataToRender.length === 0){
        table.innerHTML = `<tr><td colspan="8" class="p-8 text-center text-gray-400">❌ ไม่พบข้อมูลรถไฟฟ้าที่ค้นหา</td></tr>`;
        return;
    }

    dataToRender.forEach((tram) => {
        const realIndex = trams.findIndex(t => t.id === tram.id);
        
        let liveCarStatus = '';
        try {
            const rawStored = localStorage.getItem('yru_car_status_' + tram.id);
            if (rawStored) {
                const parsed = JSON.parse(rawStored);
                liveCarStatus = (parsed.status || '').toString().trim();
            }
        } catch(e) {}

        let status = tram.status || "พร้อมใช้งาน";
        if (liveCarStatus === 'pause' || liveCarStatus === 'พักเบรค' || liveCarStatus === 'พักเบรก' || liveCarStatus.includes('พัก')) {
            status = "พักเบรก";
        }

        let badgeColor = "bg-green-100 text-green-700";
        if (status === "รถขัดข้อง") badgeColor = "bg-red-50 text-red-600 border border-red-200";
        else if (status === "กำลังปรับปรุง" || status === "ระงับการใช้งาน") badgeColor = "bg-amber-100 text-amber-700";
        else if (status === "พักเบรก" || status === "พักเบรค" || status === "pause") badgeColor = "bg-amber-500 text-white font-bold";
        
        // Image render
        let imgHtml = "";
        if (tram.image) {
            imgHtml = `<img src="${tram.image}" class="w-12 h-12 object-cover rounded-lg border border-gray-200 shadow-sm">`;
        } else {
            imgHtml = `
                <div class="w-12 h-12 rounded-lg bg-pink-50 border border-pink-100 flex items-center justify-center text-pink-500 shadow-sm">
                    <i class="fas fa-bus text-lg"></i>
                </div>
            `;
        }
        
        // Fallback checks
        const nameVal = tram.name || "รถไฟฟ้า";
        const plateVal = tram.plate || "-";
        const capSit = tram.capacity_sit !== undefined ? tram.capacity_sit : 10;
        const capStand = tram.capacity_stand !== undefined ? tram.capacity_stand : 10;
        
        // Resolve driver name dynamically from users database
        let driverVal = "-- ยังไม่มอบหมาย --";
        if (tram.driver_id) {
            const foundUser = users.find(u => u.user_id === tram.driver_id);
            if (foundUser) {
                driverVal = foundUser.name;
            } else if (tram.driver) {
                driverVal = tram.driver;
            }
        } else if (tram.driver) {
            driverVal = tram.driver;
        }
        
        // Battery formatting & micro-interaction
        const batteryVal = parseInt(tram.battery !== undefined ? tram.battery : 80);
        let batteryIcon = "fa-battery-full";
        if (batteryVal <= 10) batteryIcon = "fa-battery-empty";
        else if (batteryVal <= 25) batteryIcon = "fa-battery-quarter";
        else if (batteryVal <= 50) batteryIcon = "fa-battery-half";
        else if (batteryVal <= 80) batteryIcon = "fa-battery-three-quarters";

        const isLow = batteryVal < 20;
        const batteryColor = isLow ? "text-red-500 font-bold" : "text-emerald-600 font-medium";

        table.innerHTML += `
            <tr class="border-t hover:bg-gray-50/50 transition">
                <td class="p-4">${imgHtml}</td>
                <td class="p-4">
                    <div class="flex flex-col">
                        <span class="font-bold text-gray-800">${tram.id}</span>
                        <span class="text-xs text-gray-500">${nameVal}</span>
                    </div>
                </td>
                <td class="p-4 whitespace-nowrap"><span class="bg-gray-100 px-2.5 py-1 rounded text-xs font-semibold text-gray-600">${plateVal}</span></td>
                <td class="p-4 whitespace-nowrap">
                    <span class="text-xs text-gray-700 font-medium">${capSit} ที่นั่ง</span>
                </td>
                <td class="p-4 whitespace-nowrap"><span class="text-xs text-gray-700"><i class="fas fa-user-tie text-gray-400 mr-1"></i>${driverVal}</span></td>
                <td class="p-4 text-center"><span class="${badgeColor} px-2.5 py-1 rounded text-xs font-bold whitespace-nowrap">${status}</span></td>
                <td class="p-4 text-center space-x-1 whitespace-nowrap">
                    ${(status === "รถขัดข้อง" || status === "ระงับการใช้งาน") ? `<button onclick="restoreTramActive(${realIndex})" class="bg-emerald-600 hover:bg-emerald-700 text-white px-2 py-1 rounded transition text-xs font-medium inline-flex items-center gap-1 shadow-sm" title="คืนสถานะพร้อมใช้งานและย้ายออกจาก Garage"><i class="fas fa-check-circle text-[10px]"></i> คืนสภาพรถ</button>` : ''}
                    <button onclick="editTram(${realIndex})" class="bg-blue-500 text-white px-2.5 py-1 rounded hover:bg-blue-600 transition text-xs font-medium">แก้ไข</button>
                    <button onclick="viewTramDetails(${realIndex})" class="bg-gray-100 text-gray-600 px-2 py-1 rounded hover:bg-gray-200 transition text-xs font-medium" title="ดูรายละเอียด"><i class="fas fa-eye"></i></button>
                    <button onclick="deleteTram(${realIndex})" class="bg-red-500 text-white px-2.5 py-1 rounded hover:bg-red-600 transition text-xs font-medium">ลบ</button>
                </td>
            </tr>`;
    });
}

function filterTrams() {
    const filterValue = document.getElementById("tramFilterStatus").value;
    const searchQuery = document.getElementById("tramSearchInput").value.toLowerCase().trim();
    
    const filtered = trams.filter(t => {
        // 1. Status filter
        const status = t.status || "พร้อมใช้งาน";
        const matchesStatus = (filterValue === "ทั้งหมด" || status === filterValue);
        
        // 2. Search query filter (id, plate, driver, name)
        const idMatch = (t.id || "").toLowerCase().includes(searchQuery);
        const plateMatch = (t.plate || "").toLowerCase().includes(searchQuery);
        const driverMatch = (t.driver || "").toLowerCase().includes(searchQuery);
        const nameMatch = (t.name || "").toLowerCase().includes(searchQuery);
        const matchesSearch = (!searchQuery || idMatch || plateMatch || driverMatch || nameMatch);
        
        return matchesStatus && matchesSearch;
    });
    
    renderTramTable(filtered);
}

const TRAM_PART_KEYS = [
    { key: 'battery', label: '🔋 แบตเตอรี่หลัก' },
    { key: 'motor',   label: '⚙️ มอเตอร์ขับเคลื่อน' },
    { key: 'brake',   label: '🛑 ระบบเบรก/ผ้าเบรก' },
    { key: 'tire',    label: '🛞 ยางรถไฟฟ้า' },
    { key: 'ecu',     label: '⚡ ระบบไฟ/กล่อง ECU' },
    { key: 'lights',  label: '💡 ไฟส่องสว่าง/ไฟเลี้ยว' },
    { key: 'chassis', label: '🪞 โครงสร้าง/กระจก' },
    { key: 'horn',    label: '🔊 แตรและระบบเสียง' }
];

function togglePartInput(key) {
    const chk = document.getElementById('chk_part_' + key);
    const input = document.getElementById('input_part_' + key);
    if (chk && input) {
        input.disabled = !chk.checked;
        if (chk.checked) {
            input.focus();
        } else {
            input.value = '';
        }
    }
}

function openTramModal() {
    document.getElementById("tramModalTitle").innerText = "+ เพิ่มรถไฟฟ้าใหม่เข้าสู่ระบบ";
    document.getElementById("editTramId").value = "-1";
    document.getElementById("modalTramId").value = "";
    document.getElementById("modalTramId").disabled = false;
    document.getElementById("modalTramName").value = "";
    document.getElementById("modalTramPlate").value = "";
    document.getElementById("modalTramCapSit").value = "10";
    document.getElementById("modalTramCapStand").value = "10";
    document.getElementById("modalTramStatus").value = "พร้อมใช้งาน";
    populateRouteDropdownInTramModal();
    document.getElementById("modalTramRoute").value = "";
    populateDriverDropdown();
    document.getElementById("driverSearchInput").value = "";
    selectDriver("", "-- ไม่ระบุ / ยังไม่มอบหมาย --");
    document.getElementById("modalTramBattery").value = "100";
    
    // Reset Section 3 fields
    document.getElementById("modalTramPurchaseDate").value = "";
    document.getElementById("modalTramWarranty").value = "";
    document.getElementById("modalTramSupplier").value = "";

    // Reset Section 4 Spare Parts inputs (Empty for admin to key in manually)
    TRAM_PART_KEYS.forEach(item => {
        const input = document.getElementById('input_part_' + item.key);
        if (input) input.value = '';
    });
    const serviceCenterEl = document.getElementById("modalTramServiceCenter");
    if (serviceCenterEl) serviceCenterEl.value = "";
    
    // Reset image input & preview
    document.getElementById("modalTramImageInput").value = "";
    uploadedImageBase64 = "";
    const imgPreview = document.getElementById("modalTramImgPreview");
    const placeholder = document.getElementById("modalTramImgPlaceholder");
    imgPreview.src = "";
    imgPreview.classList.add("hidden");
    placeholder.classList.remove("hidden");
    
    document.getElementById("tramModal").classList.remove("hidden");
}

function editTram(index) {
    document.getElementById("tramModalTitle").innerText = "แก้ไขข้อมูลรถไฟฟ้า";
    document.getElementById("editTramId").value = index;
    
    const tram = trams[index];
    
    document.getElementById("modalTramId").value = tram.id;
    document.getElementById("modalTramId").disabled = true;
    document.getElementById("modalTramName").value = tram.name || "";
    document.getElementById("modalTramPlate").value = tram.plate || "";
    document.getElementById("modalTramCapSit").value = tram.capacity_sit !== undefined ? tram.capacity_sit : 20;
    document.getElementById("modalTramCapStand").value = tram.capacity_stand !== undefined ? tram.capacity_stand : 10;
    document.getElementById("modalTramStatus").value = tram.status || "พร้อมใช้งาน";
    populateRouteDropdownInTramModal();
    document.getElementById("modalTramRoute").value = tram.route || "";
    populateDriverDropdown();
    document.getElementById("driverSearchInput").value = "";
    const foundDriver = users.find(u => 
        (u.name && u.name === tram.driver) || 
        (u.user_id && u.user_id === tram.driver_id) || 
        (u.emp_id && u.emp_id === tram.driver_id) || 
        (u.employee_id && u.employee_id === tram.driver_id)
    );
    if (foundDriver) {
        selectDriver(foundDriver.user_id || foundDriver.emp_id || foundDriver.employee_id || "", foundDriver.name);
    } else if (tram.driver) {
        selectDriver(tram.driver_id || "", tram.driver);
    } else {
        selectDriver("", "-- ไม่ระบุ / ยังไม่มอบหมาย --");
    }
    document.getElementById("modalTramBattery").value = tram.battery !== undefined ? tram.battery : 80;
    
    // Preload Section 3 fields
    document.getElementById("modalTramPurchaseDate").value = tram.purchase_date || "";
    document.getElementById("modalTramWarranty").value = tram.warranty || "";
    document.getElementById("modalTramSupplier").value = tram.supplier || "";

    // Preload Section 4 Spare Parts inputs with fallback to standardEvPartsData
    let savedPartsData = tram.parts_data;
    if (!savedPartsData || Object.keys(savedPartsData).length === 0) {
        savedPartsData = { ...standardEvPartsData };
        tram.parts_data = savedPartsData;
    }

    TRAM_PART_KEYS.forEach(item => {
        const input = document.getElementById('input_part_' + item.key);
        if (input) {
            input.value = savedPartsData[item.key] || standardEvPartsData[item.key] || '';
        }
    });
    const serviceCenterEl = document.getElementById("modalTramServiceCenter");
    if (serviceCenterEl) serviceCenterEl.value = tram.service_center || "";
    
    // Preload image if it exists
    document.getElementById("modalTramImageInput").value = "";
    uploadedImageBase64 = tram.image || "";
    const imgPreview = document.getElementById("modalTramImgPreview");
    const placeholder = document.getElementById("modalTramImgPlaceholder");
    
    if (uploadedImageBase64) {
        imgPreview.src = uploadedImageBase64;
        imgPreview.classList.remove("hidden");
        placeholder.classList.add("hidden");
    } else {
        imgPreview.src = "";
        imgPreview.classList.add("hidden");
        placeholder.classList.remove("hidden");
    }
    
    document.getElementById("tramModal").classList.remove("hidden");
}

function closeTramModal() { document.getElementById("tramModal").classList.add("hidden"); }

function switchDetailTab(tabId) {
    const btnGeneral = document.getElementById("btnTabGeneral");
    const btnMaint = document.getElementById("btnTabMaintenance");
    const contentGeneral = document.getElementById("contentTabGeneral");
    const contentMaint = document.getElementById("contentTabMaintenance");
    
    if (tabId === 'tabGeneral') {
        btnGeneral.className = "flex-1 text-center py-1.5 px-3 bg-white text-pink-600 shadow-sm font-semibold rounded-md text-xs transition duration-150 focus:outline-none";
        btnMaint.className = "flex-1 text-center py-1.5 px-3 text-gray-500 hover:text-gray-800 hover:bg-white/50 rounded-md text-xs font-semibold transition duration-150 focus:outline-none";
        contentGeneral.classList.remove("hidden");
        contentMaint.classList.add("hidden");
    } else {
        btnGeneral.className = "flex-1 text-center py-1.5 px-3 text-gray-500 hover:text-gray-800 hover:bg-white/50 rounded-md text-xs font-semibold transition duration-150 focus:outline-none";
        btnMaint.className = "flex-1 text-center py-1.5 px-3 bg-white text-pink-600 shadow-sm font-semibold rounded-md text-xs transition duration-150 focus:outline-none";
        contentGeneral.classList.add("hidden");
        contentMaint.classList.remove("hidden");
    }
}

function formatThaiDate(dateStr) {
    if (!dateStr) return "-";
    const parts = dateStr.split('-');
    if (parts.length !== 3) return dateStr;
    const year = parseInt(parts[0]) + 543;
    const months = [
        "มกราคม", "กุมภาพันธ์", "มีนาคม", "เมษายน", "พฤษภาคม", "มิถุนายน",
        "กรกฎาคม", "สิงหาคม", "กันยายน", "ตุลาคม", "พฤศจิกายน", "ธันวาคม"
    ];
    const month = months[parseInt(parts[1]) - 1];
    const date = parseInt(parts[2]);
    return `${date} ${month} ${year}`;
}

function getThaiDateString() {
    const months = ["ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค."];
    const d = new Date();
    const date = d.getDate();
    const month = months[d.getMonth()];
    const year = d.getFullYear() + 543;
    const hrs = String(d.getHours()).padStart(2, '0');
    const mins = String(d.getMinutes()).padStart(2, '0');
    return `${date} ${month} ${year} ${hrs}:${mins} น.`;
}

function editTramFromDetails(index) {
    closeTramDetailsModal();
    editTram(index);
}

function viewTramDetails(index) {
    // Reset to general tab
    switchDetailTab('tabGeneral');
    
    const tram = trams[index];
    
    document.getElementById("detailTramId").innerText = tram.id;
    document.getElementById("detailTramName").innerText = tram.name || "รถไฟฟ้า";
    
    const status = tram.status || "พร้อมใช้งาน";
    const statusEl = document.getElementById("detailTramStatus");
    statusEl.innerText = status;
    
    // Badge status styling
    statusEl.className = "inline-block px-2.5 py-0.5 rounded-full text-xs font-bold";
    if (status === "พร้อมใช้งาน") {
        statusEl.classList.add("bg-green-100", "text-green-700");
    } else if (status === "รถขัดข้อง") {
        statusEl.classList.add("bg-red-50", "text-red-600", "border", "border-red-200");
    } else {
        statusEl.classList.add("bg-amber-100", "text-amber-700");
    }
    
    const capSit = tram.capacity_sit !== undefined ? tram.capacity_sit : 10;
    const capStand = tram.capacity_stand !== undefined ? tram.capacity_stand : 10;
    
    document.getElementById("detailTramPlate").innerText = tram.plate || "-";
    document.getElementById("detailTramCapacity").innerText = `${capSit} ที่นั่ง`;
    document.getElementById("detailTramDriver").innerText = tram.driver || "ไม่ได้ระบุพนักงานขับรถ";
    // document.getElementById("detailTramGpsId").innerText = tram.gps_id || "GPS-N/A";
    // document.getElementById("detailTramCoords").innerText = tram.coords || "6.549929, 101.291254";
    
    // Battery info removed as requested
    
    // Image details
    const imgEl = document.getElementById("detailTramImg");
    const imgPlaceholder = document.getElementById("detailTramImgPlaceholder");
    if (tram.image) {
        imgEl.src = tram.image;
        imgEl.classList.remove("hidden");
        imgPlaceholder.classList.add("hidden");
    } else {
        imgEl.src = "";
        imgEl.classList.add("hidden");
        imgPlaceholder.classList.remove("hidden");
    }

    // Tab 1 (งานช่าง): Active issue warning box
    const warningBox = document.getElementById("detailRepairWarningBox");
    const warningText = document.getElementById("detailRepairWarningText");
    if (status === "รถขัดข้อง" && (tram.active_issue || tram.id === "EV-03")) {
        warningBox.classList.remove("hidden");
        warningText.innerText = tram.active_issue || "รอดำเนินการซ่อม (แบตเตอรี่ร้อนเกินกำหนด) - แจ้งเมื่อ 08/07/2569 10:45 น.";
    } else {
        warningBox.classList.add("hidden");
    }

    // Tab 1 (งานช่าง): Next schedule
    const schedules = {
        "EV-01": "กำหนดเช็กระยะขดลวดมอเตอร์ไฟฟ้าครั้งถัดไป: 15/09/2569",
        "EV-02": "กำหนดเปลี่ยนไส้กรองและสลับดอกยางล้อ: 10/10/2569",
        "EV-03": "กำหนดตรวจสอบระบบตัดไฟ/ชาร์จไฟ เมื่อแบตเตอรี่ร้อนเกินกำหนด: ด่วนที่สุด",
        "EV-04": "กำหนดตรวจเช็กระบบเบรกและโช้คอัพกันกระแทกหลัง: 01/11/2569"
    };
    document.getElementById("detailTramSchedule").innerText = tram.maintenance_schedule || schedules[tram.id] || "กำหนดเช็กระยะระบบทั่วไปประจำเดือนถัดไป";

    // Tab 1 (งานช่าง): Repair history list
    const maintenanceList = document.getElementById("detailMaintenanceList");
    maintenanceList.innerHTML = "";
    const logs = tram.maintenance || [
        { date: "05/07/2569", detail: "เช็คระยะระบบขับเคลื่อน และทดสอบไฟชาร์จแบตเตอรี่ (ปกติ)", technician: "ช่างประสาน" },
        { date: "28/06/2569", detail: "เปลี่ยนผ้าเบรกและหน้ายางรถไฟฟ้าใหม่ 4 ล้อ", technician: "ช่างสมคิด" }
    ];
    
    logs.forEach(log => {
        maintenanceList.innerHTML += `
            <div class="flex items-center justify-between bg-white p-3 rounded-xl border border-slate-100 shadow-sm text-xs gap-3 hover:border-pink-200 transition duration-150">
                <div class="flex items-center gap-3 min-w-0">
                    <div class="w-2 h-2 rounded-full bg-pink-400 ring-4 ring-pink-50 flex-shrink-0"></div>
                    <div class="min-w-0">
                        <span class="font-semibold text-slate-700 block truncate" title="${log.detail}">${log.detail}</span>
                        <p class="text-[10px] text-slate-400 mt-1 flex items-center gap-1"><i class="fas fa-wrench"></i><span>ผู้ดูแล: ${log.technician}</span></p>
                    </div>
                </div>
                <span class="bg-slate-50 border border-slate-100 text-slate-500 text-[10px] px-2.5 py-1 rounded-md font-mono font-medium flex-shrink-0">${log.date}</span>
            </div>
        `;
    });

    // Tab 2 (ทรัพย์สิน): Asset details
    document.getElementById("detailTramPurchaseDate").innerText = formatThaiDate(tram.purchase_date);
    document.getElementById("detailTramWarranty").innerText = tram.warranty || "5 ปี (สิ้นสุด 12 มีนาคม 2573)";
    document.getElementById("detailTramSupplier").innerText = tram.supplier || "บริษัท ยะลายานยนต์ อีวี จำกัด (โทร. 073-123456)";
    
    // Audit Log Footer bindings
    document.getElementById("detailTramUpdatedBy").innerText = tram.updated_by || "admin@yru.ac.th";
    document.getElementById("detailTramUpdatedAt").innerText = tram.updated_at || "- ยังไม่มีการอัปเดต -";
    
    // Bind click listener for edit info shortcut
    document.getElementById("btnDetailEditTram").setAttribute("onclick", `editTramFromDetails(${index})`);
    
    const btnRestoreModal = document.getElementById("btnDetailRestoreTram");
    if (btnRestoreModal) {
        if (status === "รถขัดข้อง" || status === "ระงับการใช้งาน") {
            btnRestoreModal.classList.remove("hidden");
            btnRestoreModal.onclick = function() {
                closeTramDetailsModal();
                restoreTramActive(index);
            };
        } else {
            btnRestoreModal.classList.add("hidden");
        }
    }

    document.getElementById("tramDetailsModal").classList.remove("hidden");
}

function closeTramDetailsModal() {
    document.getElementById("tramDetailsModal").classList.add("hidden");
}

function restoreTramActive(index) {
    const tram = trams[index];
    Swal.fire({
        title: 'ยืนยันคืนสภาพรถไฟฟ้า',
        html: `คุณต้องการคืนสภาพรถไฟฟ้า <b class="text-pink-600 font-bold">${tram.id}</b><br>เป็นสถานะ <b class="text-emerald-600 font-bold">พร้อมใช้งาน</b> ใช่หรือไม่?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'ยืนยันคืนสภาพ',
        cancelButtonText: 'ยกเลิก',
        confirmButtonColor: '#ec4899',
        cancelButtonColor: '#94a3b8',
        customClass: {
            popup: 'rounded-2xl shadow-2xl p-6',
            confirmButton: 'px-5 py-2.5 rounded-xl font-bold text-sm shadow-md',
            cancelButton: 'px-5 py-2.5 rounded-xl font-bold text-sm'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            tram.status = "พร้อมใช้งาน";
            tram.active_issue = "";
            
            if (tram.driver_id || tram.driver) {
                const isDriverAssignedElsewhere = trams.some((t, i) => i !== index && ((tram.driver_id && t.driver_id === tram.driver_id) || (tram.driver && t.driver === tram.driver)));
                if (isDriverAssignedElsewhere) {
                    tram.driver_id = "";
                    tram.driver = "";
                }
            }
            
            const defaultCoords = defaultTramCoords[tram.id] || "6.549929, 101.291254";
            let targetCoords = tram.last_active_coords || defaultCoords;
            if (!targetCoords || targetCoords === GARAGE_COORDS || targetCoords === "6.548900, 101.291700") {
                targetCoords = defaultCoords;
            }
            tram.coords = targetCoords;
            tram.current_station_id = null;
            tram.updated_by = "admin@yru.ac.th";
            tram.updated_at = getThaiDateString();

            setStorage("yru_trams_v18", trams);

            // Explicitly clear lock status in localStorage for driver page & passenger page
            localStorage.setItem('yru_car_status_' + tram.id, JSON.stringify({ status: 'ปกติกำลังขับ', active_issue: '' }));
            localStorage.removeItem('yru_car_issue_' + tram.id);

            window.dispatchEvent(new Event('storage'));

            // Reset backend driver status cache for this vehicle
            const carIdNum = tram.id.replace(/\D/g, '') || '1';
            fetch('/api/update-driver-status', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                },
                body: JSON.stringify({ car_id: carIdNum, status: 'normal', start_time: '10:30 น.' })
            }).catch(err => console.error('Error updating driver status cache:', err));

            renderTramTable();
            renderDashboardData();

            Swal.fire({
                title: 'คืนสภาพรถสำเร็จ',
                html: `รถไฟฟ้า <b class="text-pink-600 font-bold">${tram.id}</b> เปลี่ยนสถานะเป็น <b class="text-emerald-600 font-bold">พร้อมใช้งาน</b>`,
                icon: 'success',
                confirmButtonText: 'ตกลง',
                confirmButtonColor: '#10b981',
                customClass: {
                    popup: 'rounded-2xl shadow-2xl p-6'
                }
            });
        }
    });
}

function saveTramData() {
    const idField = document.getElementById("modalTramId").value.trim().toUpperCase();
    const nameField = document.getElementById("modalTramName").value.trim();
    const plateField = document.getElementById("modalTramPlate").value.trim();
    const capSitField = parseInt(document.getElementById("modalTramCapSit").value.trim());
    const capStandField = parseInt(document.getElementById("modalTramCapStand").value.trim());
    const statusField = document.getElementById("modalTramStatus").value;
    const routeField = document.getElementById("modalTramRoute").value;
    const driverField = document.getElementById("modalTramDriver").value;
    const driverIdField = document.getElementById("modalTramDriverId").value;
    const batteryField = parseInt(document.getElementById("modalTramBattery").value.trim());
    const purchaseDateField = document.getElementById("modalTramPurchaseDate").value;
    const warrantyField = document.getElementById("modalTramWarranty").value.trim();
    const supplierField = document.getElementById("modalTramSupplier").value.trim();

    // Section 4: Spare Parts fields & inputs
    const partsData = {};
    const partsList = [];

    TRAM_PART_KEYS.forEach(item => {
        const input = document.getElementById('input_part_' + item.key);
        const valText = input?.value.trim() || "";
        if (valText) {
            partsData[item.key] = valText;
            partsList.push(`${item.label}: ${valText}`);
        }
    });

    const serviceCenterField = document.getElementById("modalTramServiceCenter")?.value.trim() || "";

    let partsIssueText = "";
    if (partsList.length > 0) {
        partsIssueText = `แจ้งซ่อมอะไหล่: ${partsList.join(' | ')}`;
    }

    const editIndex = parseInt(document.getElementById("editTramId").value);

    if (!idField) { alert("กรุณากรอกรหัสรถไฟฟ้า!"); return; }
    if (!nameField) { alert("กรุณากรอกชื่อเรียก/หมายเลขคัน!"); return; }
    if (!plateField) { alert("กรุณากรอกทะเบียนรถ!"); return; }
    if (isNaN(capSitField) || capSitField < 0 || capSitField > 10) { alert("กรุณากรอกจำนวนที่นั่งให้ถูกต้อง! (สูงสุด 10 ที่นั่ง)"); return; }
    if (isNaN(capStandField) || capStandField < 0) { alert("กรุณากรอกจำนวนที่ยืนให้ถูกต้อง!"); return; }
    if (isNaN(batteryField) || batteryField < 0 || batteryField > 100) { alert("กรุณากรอกระดับแบตเตอรี่ (%) ระหว่าง 0 - 100!"); return; }

    // If editing, preserve the rest of properties (coords, maintenance, purchase_date, warranty, supplier)
    let existingTram = {};
    if (editIndex !== -1) {
        existingTram = trams[editIndex];
    }

    let defaultCoordsForCar = defaultTramCoords[idField] || "6.549929, 101.291254";
    let finalCoords = existingTram.coords || defaultCoordsForCar;
    let lastActiveCoords = existingTram.last_active_coords || defaultCoordsForCar;
    let currentStationId = existingTram.current_station_id || null;

    if (statusField === "รถขัดข้อง" || statusField === "ระงับการใช้งาน") {
        if (finalCoords !== GARAGE_COORDS) {
            lastActiveCoords = finalCoords;
        }
        finalCoords = GARAGE_COORDS;
        currentStationId = GARAGE_STATION_ID;
    } else if (statusField === "พร้อมใช้งาน") {
        if (!lastActiveCoords || lastActiveCoords === GARAGE_COORDS || lastActiveCoords === "6.548900, 101.291700") {
            lastActiveCoords = defaultCoordsForCar;
        }
        finalCoords = lastActiveCoords;
        currentStationId = null;
    }

    // 1. RULE: Clear driver if car status is broken/suspended/maintenance
    let finalDriver = driverField;
    let finalDriverId = driverIdField;
    const isMaintenanceOrSuspended = (
        statusField === "รถขัดข้อง" || 
        statusField === "ระงับการใช้งาน" || 
        statusField === "กำลังปรับปรุง" || 
        statusField === "MAINTENANCE" || 
        statusField === "SUSPENDED"
    );

    if (isMaintenanceOrSuspended) {
        finalDriver = "";
        finalDriverId = "";
    }

    // 2. RULE: If driver is assigned to this car, unbind/clear driver from any other car
    if (finalDriver && finalDriver !== "-- ไม่ระบุ / ยังไม่มอบหมาย --") {
        trams.forEach((t, i) => {
            if (i !== editIndex && (t.driver === finalDriver || (finalDriverId && t.driver_id === finalDriverId))) {
                t.driver = "";
                t.driver_id = "";
            }
        });
    }

    const tramData = {
        id: idField,
        name: nameField,
        plate: plateField,
        capacity_sit: capSitField,
        capacity_stand: capStandField,
        status: statusField,
        gps_id: existingTram.gps_id || ("GPS-" + idField + "-YRU"),
        route: routeField,
        driver: finalDriver,
        driver_id: finalDriverId,
        battery: batteryField,
        image: uploadedImageBase64 || existingTram.image || "",
        coords: finalCoords,
        last_active_coords: lastActiveCoords,
        current_station_id: currentStationId,
        parts_data: partsData,
        parts: partsList,
        service_center: serviceCenterField,
        active_issue: (statusField === "รถขัดข้อง" || statusField === "ระงับการใช้งาน")
            ? (partsIssueText ? `${partsIssueText} - แจ้งเมื่อ ${getThaiDateString()}` : (existingTram.active_issue || `รอดำเนินการซ่อม (ย้ายไปจอด ณ จุดจอดเก็บรถ Garage) - แจ้งเมื่อ ${getThaiDateString()}`))
            : "",
        maintenance: existingTram.maintenance || [
            { date: "05/07/2569", detail: "เช็คระยะระบบขับเคลื่อน และทดสอบไฟชาร์จแบตเตอรี่ (ปกติ)", technician: "ช่างประสาน" },
            { date: "28/06/2569", detail: "เปลี่ยนผ้าเบรกและหน้ายางรถไฟฟ้าใหม่ 4 ล้อ", technician: "ช่างสมคิด" }
        ],
        purchase_date: purchaseDateField || existingTram.purchase_date || "2025-03-12",
        warranty: warrantyField || existingTram.warranty || "5 ปี (สิ้นสุด 12 มีนาคม 2573)",
        supplier: supplierField || existingTram.supplier || "บริษัท ยะลายานยนต์ อีวี จำกัด (โทร. 073-123456)",
        updated_by: "admin@yru.ac.th",
        updated_at: getThaiDateString()
    };

    if (editIndex === -1) {
        if(trams.some(t => t.id === idField)){ alert("รหัสรถคันนี้ซ้ำในระบบ!"); return; }
        trams.push(tramData);
    } else {
        trams[editIndex] = tramData;
    }

    setStorage("yru_trams_v18", trams);

    // Sync 1-to-1 driver assignment to backend DB/Cache
    fetch('/api/electric-trains/assign-driver', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
        },
        body: JSON.stringify({
            car_code: idField,
            driver_id: finalDriverId,
            driver_name: finalDriver
        })
    }).catch(err => console.error("Driver assignment backend sync error:", err));

    if (statusField === "พร้อมใช้งาน") {
        localStorage.setItem('yru_car_status_' + idField, JSON.stringify({ status: 'ปกติกำลังขับ', active_issue: '' }));
        const carIdNum = idField.replace(/\D/g, '') || '1';
        fetch('/api/update-driver-status', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
            },
            body: JSON.stringify({ car_id: carIdNum, status: 'normal', start_time: '10:30 น.' })
        }).catch(err => console.error('Error updating driver status cache:', err));
    }
    window.dispatchEvent(new Event('storage'));
    try {
        const tramSyncChannel = new BroadcastChannel('yru_trams_realtime_sync');
        tramSyncChannel.postMessage({ type: 'TRAMS_UPDATED', trams: trams, timestamp: Date.now() });
    } catch(e) {}

    // Sync to backend SQLite database
    fetch('/api/electric-trains/assign-route', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        },
        body: JSON.stringify({
            skytrain_code: idField,
            route_code: routeField
        })
    }).catch(err => console.error("Database sync failed, saved locally", err));

    closeTramModal();
    document.getElementById("tramFilterStatus").value = "ทั้งหมด";
    document.getElementById("tramSearchInput").value = "";
    renderTramTable();
    renderDashboardData();
}

function deleteTram(index) {
    if (confirm("คุณต้องการลบรถคันนี้ออกจากฐานข้อมูลใช่หรือไม่?")) {
        trams.splice(index, 1);
        setStorage("yru_trams_v18", trams);
        window.dispatchEvent(new Event('storage'));
        try {
            const tramSyncChannel = new BroadcastChannel('yru_trams_realtime_sync');
            tramSyncChannel.postMessage({ type: 'TRAMS_UPDATED', trams: trams, timestamp: Date.now() });
        } catch(e) {}
        renderTramTable();
        renderDashboardData();
    }
}

// อัปเดตยอดผู้ใช้งานวันนี้แบบ Real-time จาก localStorage
setInterval(() => {
    const el = document.getElementById('dash-total-users');
    if (el) {
        if (typeof window.currentDashFilteredCount !== 'undefined') {
            el.innerText = window.currentDashFilteredCount.toLocaleString() + " รอบ";
        } else {
            const liveTodayTrips = parseInt(localStorage.getItem('yru_today_trips_accumulated') || '0');
            el.innerText = liveTodayTrips + " รอบ";
        }
    }
}, 2000);


// --- ส่วนจัดการเส้นทาง / จุดจอด (STOPS ENGINE) ---
function renderStopTable() {
    // Delegate to new Integrated module
    if (typeof renderIntegratedStopsList === 'function') {
        renderIntegratedStopsList();
    }
}

function openStopModal() {
    document.getElementById("stopModalTitle").innerText = "+ จุดจอดรถ";
    document.getElementById("editStopIndex").value = "-1";
    document.getElementById("modalStopSequence").value = stops.length + 1;
    document.getElementById("modalStopName").value = "";
    document.getElementById("modalStopLat").value = "";
    document.getElementById("modalStopLng").value = "";
    document.getElementById("modalStopRoute").value = "สายสีชมพู";
    document.getElementById("stopModal").classList.remove("hidden");
}

function editStop(index) {
    document.getElementById("stopModalTitle").innerText = "แก้ไขข้อมูลจุดจอด";
    document.getElementById("editStopIndex").value = index;
    document.getElementById("modalStopSequence").value = stops[index].sequence !== undefined ? stops[index].sequence : (index + 1);
    document.getElementById("modalStopName").value = stops[index].name;
    document.getElementById("modalStopLat").value = stops[index].lat !== undefined ? stops[index].lat : "";
    document.getElementById("modalStopLng").value = stops[index].lng !== undefined ? stops[index].lng : "";
    document.getElementById("modalStopRoute").value = stops[index].route !== undefined ? stops[index].route : "สายสีชมพู";
    document.getElementById("stopModal").classList.remove("hidden");
}

function closeStopModal() { document.getElementById("stopModal").classList.add("hidden"); }

function saveStopData() {
    const sequenceField = parseInt(document.getElementById("modalStopSequence").value.trim());
    const nameField = document.getElementById("modalStopName").value.trim();
    const latField = parseFloat(document.getElementById("modalStopLat").value.trim());
    const lngField = parseFloat(document.getElementById("modalStopLng").value.trim());
    const routeField = document.getElementById("modalStopRoute").value.trim();
    const editIndex = parseInt(document.getElementById("editStopIndex").value);

    if (isNaN(sequenceField)) { alert("กรุณากรอกลำดับของจุดจอดเป็นตัวเลข!"); return; }
    if (!nameField) { alert("กรุณากรอกชื่อจุดจอดรถ!"); return; }
    if (isNaN(latField) || isNaN(lngField)) { alert("กรุณากรอกละติจูดและลองจิจูดให้ถูกต้อง!"); return; }
    if (!routeField) { alert("กรุณากรอกเส้นทาง!"); return; }

    const stopObj = {
        sequence: sequenceField,
        name: nameField,
        route: routeField,
        lat: latField,
        lng: lngField
    };

    if (editIndex === -1) {
        stops.push(stopObj);
    } else {
        stops[editIndex] = stopObj;
    }

    // Sort stops by sequence
    stops.sort((a, b) => (a.sequence || 0) - (b.sequence || 0));

    setStorage("yru_stops_v2", stops);
    closeStopModal();
    renderStopTable();
    syncStopsWithRoutes();

    // If Route Builder map is open, refresh checklist and markers immediately
    if (builderMap) {
        renderStopsOnBuilderMap();
        renderRouteStopsChecklist();
    }

    // Show success toast with stop count
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            position: 'center',
            icon: 'success',
            title: `บันทึกจุดจอดสำเร็จ (ทั้งหมด ${stops.length} จุด)`,
            showConfirmButton: false,
            timer: 2000
        });
    }
}

// Auto-sync stops with routes in both local storage and database
function syncStopsWithRoutes() {
    const allStops = getStorage("yru_stops_v2", defaultStops);
    let allRoutes = getStorage("yru_routes_v1", []);

    // Clean up deleted stops from existing routes: if a route contains a stop that no longer exists, remove it.
    allRoutes.forEach(route => {
        if (route.route_stops) {
            route.route_stops = route.route_stops.filter(rs => 
                allStops.some(s => (s.parking_spot_code || s.name) === rs.parking_spot_code)
            );
            // Re-sequence the remaining stops
            route.route_stops.forEach((rs, idx) => {
                rs.stop_order = idx + 1;
            });
        }
    });

    setStorage("yru_routes_v1", allRoutes);
    systemRoutes = allRoutes;

    if (typeof renderRouteManagementPage === 'function') {
        renderRouteManagementPage();
    }

    allRoutes.forEach(async (route) => {
        try {
            await fetch('/api/routes/' + route.route_code, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify(route)
            });
        } catch (e) {
            console.error("API error syncing route stops:", e);
        }
    });
}

// ฟังก์ชันลบจุดจอด
function deleteStop(index) {
    if (confirm(`คุณมั่นใจที่จะลบ "${stops[index].name}" ออกใช่หรือไม่?`)) {
        stops.splice(index, 1);
        setStorage("yru_stops_v2", stops);
        renderStopTable();
        syncStopsWithRoutes();

        // If Route Builder map is open, refresh checklist and markers immediately
        if (builderMap) {
            renderStopsOnBuilderMap();
            renderRouteStopsChecklist();
        }
    }
}

// --- ส่วนจัดการผู้ใช้งานระบบ (USERS ENGINE) ---
function renderUserTable(filteredUsers = null) {
    const table = document.getElementById("userTable");
    table.innerHTML = "";
    const dataToRender = filteredUsers ? filteredUsers : users;

    if (dataToRender.length === 0) {
        table.innerHTML = `<tr><td colspan="8" class="p-8 text-center text-gray-400">❌ ไม่พบรายชื่อผู้ใช้งานระบบหลังบ้าน</td></tr>`;
        return;
    }

    dataToRender.forEach((user) => {
        const realIndex = users.findIndex(u => u.email === user.email);
        const roleBadge = getRoleBadge(user.role);
        const empId = user.emp_id || "-";
        const username = user.username || "-";
        const phone = user.phone || user.phone_number || "-";
        
        table.innerHTML += `
            <tr class="border-t hover:bg-gray-50 transition">
                <td class="p-4 text-gray-600">${empId}</td>
                <td class="p-4 font-semibold text-gray-800">${user.name}</td>
                <td class="p-4 text-gray-600">${username}</td>
                <td class="p-4 text-gray-600">${user.email}</td>
                <td class="p-4 text-gray-600">${phone}</td>
                <td class="p-4">${roleBadge}</td>
                <td class="p-4"><span class="${getStatusStyle(user.status)} text-xs bg-gray-100 px-2.5 py-1 rounded-md">${user.status}</span></td>
                <td class="p-4 text-center space-x-1 whitespace-nowrap">
                    <button onclick="editUser(${realIndex})" class="bg-blue-500 text-white px-3 py-1 rounded-md hover:bg-blue-600 transition text-xs font-medium">แก้ไข</button>
                    <button onclick="deleteUser(${realIndex})" class="bg-red-500 text-white px-3 py-1 rounded-md hover:bg-red-600 transition text-xs font-medium">ลบ</button>
                </td>
            </tr>`;
    });
}

function parseNameString(fullName) {
    let prefix = "";
    let remainder = (fullName || "").trim();
    const commonPrefixes = ["นาย", "นางสาว", "นาง", "ดร.", "ดร", "อาจารย์", "อ.", "ผศ.", "รศ.", "ศ.", "น.ส."];
    for (const p of commonPrefixes) {
        if (remainder.startsWith(p)) {
            prefix = p;
            remainder = remainder.substring(p.length).trim();
            break;
        }
    }
    const parts = remainder.split(/\s+/);
    const firstName = parts[0] || "";
    const lastName = parts.slice(1).join(" ") || "";
    return { prefix, firstName, lastName };
}

function openUserModal() {
    document.getElementById("userModalTitle").innerText = "เพิ่มผู้ใช้งานใหม่";
    document.getElementById("editUserIndex").value = "-1";
    document.getElementById("modalUserPrefix").value = "";
    document.getElementById("modalUserFirstName").value = "";
    document.getElementById("modalUserLastName").value = "";
    document.getElementById("modalUserUsername").value = "";
    document.getElementById("modalUserEmployeeId").value = "";
    document.getElementById("modalUserEmail").value = "";
    document.getElementById("modalUserPhone").value = "";
    document.getElementById("modalUserRole").value = "admin";
    document.getElementById("modalUserStatus").value = "ปกติ";
    document.getElementById("modalUserNote").value = "";
    document.getElementById("userModal").classList.remove("hidden");
}

function editUser(index) {
    document.getElementById("userModalTitle").innerText = "แก้ไขข้อมูลผู้ใช้งาน";
    document.getElementById("editUserIndex").value = index;
    
    const parsed = parseNameString(users[index].name || "");
    document.getElementById("modalUserPrefix").value = parsed.prefix;
    document.getElementById("modalUserFirstName").value = parsed.firstName;
    document.getElementById("modalUserLastName").value = parsed.lastName;
    
    document.getElementById("modalUserUsername").value = users[index].username || "";
    document.getElementById("modalUserEmployeeId").value = users[index].emp_id || "";
    document.getElementById("modalUserEmail").value = users[index].email;
    document.getElementById("modalUserPhone").value = users[index].phone || "";
    document.getElementById("modalUserRole").value = users[index].role || 'admin';
    document.getElementById("modalUserStatus").value = users[index].status;
    document.getElementById("modalUserNote").value = users[index].note || "";
    document.getElementById("userModal").classList.remove("hidden");
}

function closeUserModal() { document.getElementById("userModal").classList.add("hidden"); }

function generateUserId() {
    let maxNum = 0;
    users.forEach(u => {
        if (u.user_id && u.user_id.startsWith('USR')) {
            const num = parseInt(u.user_id.substring(3));
            if (num > maxNum) maxNum = num;
        }
    });
    return 'USR' + String(maxNum + 1).padStart(3, '0');
}

function saveUserData() {
    const prefix = document.getElementById("modalUserPrefix").value.trim();
    const firstName = document.getElementById("modalUserFirstName").value.trim();
    const lastName = document.getElementById("modalUserLastName").value.trim();
    const username = document.getElementById("modalUserUsername").value.trim();
    const empId = document.getElementById("modalUserEmployeeId").value.trim();
    const email = document.getElementById("modalUserEmail").value.trim();
    const phone = document.getElementById("modalUserPhone").value.trim();
    const role = document.getElementById("modalUserRole").value;
    const status = document.getElementById("modalUserStatus").value;
    const note = document.getElementById("modalUserNote").value.trim();
    const editIndex = parseInt(document.getElementById("editUserIndex").value);

    if (!prefix || !firstName || !lastName || !username || !empId || !email || !phone || !role || !status) { 
        alert("กรุณากรอกข้อมูลที่จำเป็นให้ครบถ้วน!"); return; 
    }

    const name = `${prefix}${firstName} ${lastName}`;

    if (editIndex === -1) {
        if (users.some(u => u.email.toLowerCase() === email.toLowerCase())) {
            alert("อีเมลบัญชีนี้ถูกผูกในระบบแล้ว!"); return;
        }
        if (users.some(u => u.username && u.username.toLowerCase() === username.toLowerCase())) {
            alert("Username นี้ถูกใช้งานแล้ว!"); return;
        }
        const newUserId = generateUserId();
        users.push({ 
            user_id: newUserId, 
            emp_id: empId, 
            username: username, 
            name: name, 
            email: email, 
            phone: phone, 
            role: role, 
            status: status, 
            note: note 
        });
    } else {
        // Prevent duplicate username for others
        const existingUsernameIdx = users.findIndex(u => u.username && u.username.toLowerCase() === username.toLowerCase());
        if (existingUsernameIdx !== -1 && existingUsernameIdx !== editIndex) {
            alert("Username นี้ถูกใช้งานโดยผู้ใช้อื่นแล้ว!"); return;
        }

        users[editIndex].emp_id = empId;
        users[editIndex].username = username;
        users[editIndex].name = name;
        users[editIndex].email = email;
        users[editIndex].phone = phone;
        users[editIndex].role = role;
        users[editIndex].status = status;
        users[editIndex].note = note;
    }

    setStorage("yru_users_v8", users);

    // Sync to Database for login
    fetch('/api/users/sync-local', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Accept': 'application/json',
            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
        },
        body: JSON.stringify({
            employee_id: empId,
            prefix: prefix,
            first_name: firstName,
            last_name: lastName,
            username: username,
            email: email,
            phone_number: phone,
            role: role,
            status: status,
            remark: note
        })
    }).catch(e => console.error("Sync error:", e));

    closeUserModal();
    document.getElementById("userSearchInput").value = "";
    renderUserTable();
}

function deleteUser(index) {
    if (confirm(`คุณต้องการถอนสิทธิ์ผู้ใช้งาน "${users[index].name}" ออกจากระบบใช่หรือไม่?`)) {
        const empId = users[index].emp_id;
        users.splice(index, 1);
        setStorage("yru_users_v8", users);
        
        // Sync to Database
        fetch(`/api/users/sync-local/${empId}`, {
            method: 'DELETE',
            headers: {
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        }).catch(e => console.error("Sync error:", e));

        renderUserTable();
    }
}

// ระบบการค้นหารายชื่อผู้ใช้ Realtime
function searchUsers() {
    const keyword = document.getElementById("userSearchInput").value.toLowerCase().trim();
    if (!keyword) {
        renderUserTable(null);
        return;
    }
    const roleMap = {
        admin: 'ผู้ดูแลระบบ',
        driver: 'พนักงานขับรถ',
        executive: 'ผู้บริหาร',
        mechanic: 'ช่างซ่อม',
        student: 'นักศึกษา',
        staff: 'บุคลากรทั่วไป'
    };
    const matched = users.filter(u => {
        const thaiRole = (roleMap[u.role] || u.role).toLowerCase();
        const phoneStr = (u.phone || u.phone_number || "").toLowerCase();
        return u.name.toLowerCase().includes(keyword) || 
               u.email.toLowerCase().includes(keyword) ||
               phoneStr.includes(keyword) ||
               thaiRole.includes(keyword) ||
               (u.user_id && u.user_id.toLowerCase().includes(keyword));
    });
    renderUserTable(matched);
}

// ===== User Import (Excel/CSV) Logic =====
let dropZoneInitialized = false;
let tempImportList = [];

function openImportUserModal() {
    clearSelectedFile();
    document.getElementById("importUserModal").classList.remove("hidden");
    if (!dropZoneInitialized) {
        initDropZone();
        dropZoneInitialized = true;
    }
}

function closeImportUserModal() {
    document.getElementById("importUserModal").classList.add("hidden");
}

function downloadUserTemplate() {
    const headers = ['ชื่อ-นามสกุล', 'อีเมล มรย.', 'สิทธิ์'];
    const rows = [
        ['นายสมเกียรติ สุภาพ', 'somkiat.s@yru.ac.th', 'นักศึกษา'],
        ['นางสาวมารวย มั่งคั่ง', 'maruay.m@yru.ac.th', 'ผู้บริหาร'],
        ['นายสมใจ ขับขี่', 'somjai.k@yru.ac.th', 'พนักงานขับรถ'],
        ['นายประสาน งานดี', 'prasan.g@yru.ac.th', 'ช่างซ่อม']
    ];
    let csvContent = "\uFEFF"; // UTF-8 BOM for Microsoft Excel Thai language compatibility
    csvContent += headers.join(",") + "\r\n";
    rows.forEach(row => {
        csvContent += row.map(cell => `"${cell.replace(/"/g, '""')}"`).join(",") + "\r\n";
    });
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    const url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    link.setAttribute("download", "yru_users_template.csv");
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function initDropZone() {
    const dropZone = document.getElementById('dropZone');
    if (!dropZone) return;
    
    ['dragenter', 'dragover'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.add('border-pink-500', 'bg-pink-50/10');
        }, false);
    });

    ['dragleave', 'drop'].forEach(eventName => {
        dropZone.addEventListener(eventName, (e) => {
            e.preventDefault();
            e.stopPropagation();
            dropZone.classList.remove('border-pink-500', 'bg-pink-50/10');
        }, false);
    });

    dropZone.addEventListener('drop', (e) => {
        const dt = e.dataTransfer;
        const files = dt.files;
        if (files.length > 0) {
            const fileInput = document.getElementById('importFile');
            fileInput.files = files;
            const event = { target: { files: files } };
            handleImportFile(event);
        }
    }, false);
}

function clearSelectedFile() {
    document.getElementById("importFile").value = "";
    document.getElementById("fileNameText").innerText = "";
    document.getElementById("selectedFileName").classList.add("hidden");
    document.getElementById("dropZone").classList.remove("hidden");
    document.getElementById("validationResultArea").classList.add("hidden");
    document.getElementById("btnConfirmImport").disabled = true;
    tempImportList = [];
}

function handleImportFile(event) {
    const file = event.target.files[0];
    if (!file) return;
    
    document.getElementById("fileNameText").innerText = file.name;
    document.getElementById("selectedFileName").classList.remove("hidden");
    document.getElementById("dropZone").classList.add("hidden");
    
    const fileType = file.name.split('.').pop().toLowerCase();
    
    if (fileType === 'csv') {
        const reader = new FileReader();
        reader.onload = function(e) {
            const text = e.target.result;
            parseCSV(text);
        };
        reader.readAsText(file, 'UTF-8');
    } else if (fileType === 'xlsx' || fileType === 'xls') {
        const reader = new FileReader();
        reader.onload = function(e) {
            try {
                const data = new Uint8Array(e.target.result);
                const workbook = XLSX.read(data, { type: 'array' });
                const firstSheetName = workbook.SheetNames[0];
                const worksheet = workbook.Sheets[firstSheetName];
                const rows = XLSX.utils.sheet_to_json(worksheet, { header: 1 });
                parseExcelRows(rows);
            } catch (err) {
                alert("เกิดข้อผิดพลาดในการอ่านไฟล์ Excel: " + err.message);
                clearSelectedFile();
            }
        };
        reader.readAsArrayBuffer(file);
    } else {
        alert("รูปแบบไฟล์ไม่ถูกต้อง รองรับเฉพาะ .csv, .xlsx, .xls เท่านั้น");
        clearSelectedFile();
    }
}

function parseCSV(text) {
    const lines = text.split(/\r?\n/);
    const rows = [];
    lines.forEach(line => {
        if (!line.trim()) return;
        const cols = [];
        let insideQuote = false;
        let currentField = '';
        for (let i = 0; i < line.length; i++) {
            const char = line[i];
            if (char === '"') {
                insideQuote = !insideQuote;
            } else if (char === ',' && !insideQuote) {
                cols.push(currentField.trim());
                currentField = '';
            } else {
                currentField += char;
            }
        }
        cols.push(currentField.trim());
        rows.push(cols);
    });
    parseExcelRows(rows);
}

function parseExcelRows(rows) {
    if (rows.length < 2) {
        alert("ไม่พบข้อมูลผู้ใช้งานในไฟล์!");
        clearSelectedFile();
        return;
    }
    
    const headers = rows[0].map(h => String(h || '').trim());
    const nameIdx = headers.findIndex(h => h.includes('ชื่อ-นามสกุล') || h.includes('ชื่อ'));
    const emailIdx = headers.findIndex(h => h.includes('อีเมล') || h.includes('Email') || h.includes('มรย'));
    const roleIdx = headers.findIndex(h => h.includes('สิทธิ์') || h.includes('Role'));
    const statusIdx = headers.findIndex(h => h.includes('สถานะ') || h.includes('Status'));
    
    if (nameIdx === -1 || emailIdx === -1 || roleIdx === -1) {
        alert("หัวคอลัมน์ไม่ถูกต้อง! หัวข้อต้องมีคำว่า 'ชื่อ-นามสกุล', 'อีเมล มรย.', 'สิทธิ์'");
        clearSelectedFile();
        return;
    }
    
    const validationErrors = [];
    const validEntries = [];
    const emailInFile = new Set();
    
    for (let i = 1; i < rows.length; i++) {
        const row = rows[i];
        if (row.length === 0 || row.every(cell => cell === null || cell === undefined || String(cell).trim() === '')) {
            continue;
        }
        
        const rowNum = i + 1;
        const name = String(row[nameIdx] || '').trim();
        const email = String(row[emailIdx] || '').trim();
        const roleRaw = String(row[roleIdx] || '').trim();
        const statusRaw = statusIdx !== -1 ? String(row[statusIdx] || '').trim() : 'ใช้งาน';
        
        if (!name || !email || !roleRaw) {
            validationErrors.push({
                row: rowNum,
                data: `ชื่อ: ${name || '-'}, อีเมล: ${email || '-'}`,
                error: "กรอกข้อมูลไม่ครบถ้วนในช่อง ชื่อ, อีเมล, สิทธิ์"
            });
            continue;
        }
        
        // 1. Check email domain format
        if (!email.toLowerCase().endsWith('@yru.ac.th')) {
            validationErrors.push({
                row: rowNum,
                data: email,
                error: "อีเมลต้องเป็นรูปแบบ @yru.ac.th เท่านั้น"
            });
            continue;
        }
        
        // 2. Check duplicate in database
        const isDbDuplicate = users.some(u => u.email.toLowerCase() === email.toLowerCase());
        if (isDbDuplicate) {
            validationErrors.push({
                row: rowNum,
                data: email,
                error: "อีเมลบัญชีนี้ถูกใช้งานในระบบแล้ว (อีเมลซ้ำ)"
            });
            continue;
        }
        
        // 3. Check duplicate in uploaded file
        if (emailInFile.has(email.toLowerCase())) {
            validationErrors.push({
                row: rowNum,
                data: email,
                error: "พบอีเมลซ้ำซ้อนกันในไฟล์ที่อัปโหลด"
            });
            continue;
        }
        emailInFile.add(email.toLowerCase());
        
        // Role parsing (Thai and English compatible)
        let mappedRole = '';
        const rLower = roleRaw.toLowerCase();
        if (rLower.includes('ผู้ดูแล') || rLower.includes('admin')) mappedRole = 'admin';
        else if (rLower.includes('นักศึกษา') || rLower.includes('student')) mappedRole = 'student';
        else if (rLower.includes('อาจารย์') || rLower.includes('บุคลากร') || rLower.includes('staff')) mappedRole = 'staff';
        else if (rLower.includes('คนขับ') || rLower.includes('พนักงานขับ') || rLower.includes('driver')) mappedRole = 'driver';
        else if (rLower.includes('ผู้บริหาร') || rLower.includes('executive')) mappedRole = 'executive';
        else if (rLower.includes('ช่าง') || rLower.includes('mechanic')) mappedRole = 'mechanic';
        else {
            validationErrors.push({
                row: rowNum,
                data: roleRaw,
                error: "สิทธิ์ไม่ถูกต้อง (ต้องเป็น: ผู้ดูแลระบบ, นักศึกษา, อาจารย์/บุคลากร, พนักงานขับรถ, ผู้บริหาร, หรือช่างซ่อม)"
            });
            continue;
        }
        
        // Status parsing (Thai and English compatible)
        let mappedStatus = 'ปกติ';
        const sLower = statusRaw.toLowerCase();
        if (sLower === '' || sLower.includes('ใช้') || sLower.includes('active') || sLower.includes('ปกติ')) mappedStatus = 'ปกติ';
        else if (sLower.includes('ระงับ') || sLower.includes('ban') || sLower.includes('inactive')) mappedStatus = 'ระงับการใช้งาน';
        else {
            validationErrors.push({
                row: rowNum,
                data: statusRaw,
                error: "สถานะไม่ถูกต้อง (ต้องเป็น: ใช้งาน หรือ ระงับใช้งาน)"
            });
            continue;
        }
        
        validEntries.push({
            name,
            email,
            role: mappedRole,
            status: mappedStatus
        });
    }
    
    showValidationResults(validationErrors, validEntries);
}

function showValidationResults(errors, valid) {
    tempImportList = valid;
    document.getElementById("validationResultArea").classList.remove("hidden");
    
    // Render errors
    const errorContainer = document.getElementById("importErrorsContainer");
    const errorRowsList = document.getElementById("errorRowsList");
    errorRowsList.innerHTML = "";
    if (errors.length > 0) {
        document.getElementById("errorCountText").innerText = errors.length;
        errorContainer.classList.remove("hidden");
        errors.forEach(err => {
            errorRowsList.innerHTML += `
                <tr class="border-b border-red-100 hover:bg-red-50/50 transition">
                    <td class="p-2.5 font-bold text-center text-red-700">${err.row}</td>
                    <td class="p-2.5 font-mono break-all text-slate-700 font-semibold">${err.data}</td>
                    <td class="p-2.5 text-red-600 font-bold flex items-center gap-1.5"><i class="fas fa-exclamation-triangle"></i> ${err.error}</td>
                </tr>
            `;
        });
    } else {
        errorContainer.classList.add("hidden");
    }
    
    // Render valid
    const validContainer = document.getElementById("importValidContainer");
    const validRowsList = document.getElementById("validRowsList");
    validRowsList.innerHTML = "";
    if (valid.length > 0) {
        document.getElementById("validCountText").innerText = valid.length;
        validContainer.classList.remove("hidden");
        
        const roleNames = {
            admin: 'ผู้ดูแลระบบ',
            student: 'นักศึกษา',
            staff: 'อาจารย์/บุคลากร',
            driver: 'พนักงานขับรถ',
            executive: 'ผู้บริหาร',
            mechanic: 'ช่างซ่อม'
        };
        
        valid.forEach((entry, idx) => {
            let roleBadgeClass = 'bg-pink-100 text-pink-700';
            if (entry.role === 'admin') roleBadgeClass = 'bg-red-100 text-red-700';
            else if (entry.role === 'driver') roleBadgeClass = 'bg-amber-100 text-amber-700';
            else if (entry.role === 'executive') roleBadgeClass = 'bg-purple-100 text-purple-700';
            else if (entry.role === 'mechanic') roleBadgeClass = 'bg-green-100 text-green-700';
            else if (entry.role === 'student') roleBadgeClass = 'bg-blue-100 text-blue-700';
            else if (entry.role === 'staff') roleBadgeClass = 'bg-indigo-100 text-indigo-700';

            validRowsList.innerHTML += `
                <tr class="border-b border-emerald-100 hover:bg-emerald-50/40 transition">
                    <td class="p-2.5 pl-3 text-center">
                        <input type="checkbox" class="valid-import-checkbox rounded text-emerald-600 focus:ring-emerald-500 cursor-pointer w-4 h-4" data-idx="${idx}" checked onchange="updateSelectAllValidCheckbox()">
                    </td>
                    <td class="p-2.5 font-semibold text-slate-800">${entry.name}</td>
                    <td class="p-2.5 font-mono text-slate-600 font-medium">${entry.email}</td>
                    <td class="p-2.5"><span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-[10px] font-bold ${roleBadgeClass}">${roleNames[entry.role] || entry.role}</span></td>
                    <td class="p-2.5 pr-3"><span class="inline-flex items-center gap-1 px-2 py-0.5 rounded bg-emerald-100 text-emerald-800 text-[10px] font-bold"><i class="fas fa-check"></i> ${entry.status}</span></td>
                </tr>
            `;
        });
        document.getElementById("btnConfirmImport").disabled = false;
    } else {
        validContainer.classList.add("hidden");
        document.getElementById("btnConfirmImport").disabled = true;
    }
}

function toggleAllValidImport() {
    const selectAll = document.getElementById("selectAllValid").checked;
    document.querySelectorAll(".valid-import-checkbox").forEach(cb => {
        cb.checked = selectAll;
    });
}

function updateSelectAllValidCheckbox() {
    const allCb = document.querySelectorAll(".valid-import-checkbox");
    const checkedCb = document.querySelectorAll(".valid-import-checkbox:checked");
    document.getElementById("selectAllValid").checked = (allCb.length === checkedCb.length);
}

function confirmImportUsers() {
    if (tempImportList.length === 0) return;
    
    const checkedCheckboxes = document.querySelectorAll(".valid-import-checkbox:checked");
    if (checkedCheckboxes.length === 0) {
        alert("กรุณาเลือกข้อมูลที่ต้องการนำเข้าอย่างน้อย 1 รายการ");
        return;
    }
    
    let addedCount = 0;
    checkedCheckboxes.forEach(cb => {
        const idx = parseInt(cb.getAttribute("data-idx"));
        const entry = tempImportList[idx];
        
        const newUserId = generateUserId();
        const newUser = {
            user_id: newUserId,
            name: entry.name,
            email: entry.email,
            role: entry.role,
            status: entry.status
        };
        users.push(newUser);

        const nameParts = parseNameString(newUser.name);
        fetch('/api/users/sync-local', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            },
            body: JSON.stringify({
                employee_id: newUser.emp_id || newUser.user_id,
                prefix: nameParts.prefix,
                first_name: nameParts.firstName,
                last_name: nameParts.lastName,
                username: newUser.username || newUser.email.split('@')[0],
                email: newUser.email,
                phone_number: newUser.phone || '',
                role: newUser.role,
                status: newUser.status,
                remark: newUser.note || ''
            })
        }).catch(e => console.error("Sync error:", e));

        addedCount++;
    });
    
    setStorage("yru_users_v8", users);
    closeImportUserModal();
    renderUserTable();
    renderDashboardData();
    
    Swal.fire({
        title: "นำเข้าข้อมูลสำเร็จ",
        text: `ระบบทำการเพิ่มผู้ใช้งานใหม่เรียบร้อยแล้วทั้งหมด ${addedCount} รายการ`,
        icon: "success",
        confirmButtonColor: "#ec4899"
    });
}

// ===== Role Permission Matrix =====
const permissionGroups = [
    {
        category: 'ทั่วไป',
        icon: 'fas fa-key',
        color: 'bg-gray-100 text-gray-600',
        menus: [
            { id: 'login', label: 'เข้าสู่ระบบ / ออกจากระบบ' },
        ]
    },
    {
        category: 'ผู้ดูแลระบบ',
        icon: 'fas fa-shield-alt',
        color: 'bg-red-50 text-red-600',
        menus: [
            { id: 'tram_data',   label: 'จัดการข้อมูลรถไฟฟ้า' },
            { id: 'route_edit',  label: 'เพิ่ม/แก้ไข/ลบเส้นทาง' },
            { id: 'stop_manage', label: 'จัดการจุดจอด' },
            { id: 'user_manage', label: 'จัดการบัญชีผู้ใช้' },
            { id: 'role_set',    label: 'กำหนดสิทธิ์ผู้ใช้' },
        ]
    },
    {
        category: 'นักศึกษา',
        icon: 'fas fa-graduation-cap',
        color: 'bg-blue-50 text-blue-600',
        menus: [
            { id: 'realtime',  label: 'ดูตำแหน่งรถแบบ Real-time' },
            { id: 'stop_view', label: 'ดูจุดจอด' },
            { id: 'call_tram', label: 'เรียกรถไฟฟ้า' },
        ]
    },
    {
        category: 'พนักงานขับรถ',
        icon: 'fas fa-car',
        color: 'bg-amber-50 text-amber-600',
        menus: [
            { id: 'gps_send',  label: 'ส่งตำแหน่ง GPS' },
            { id: 'status_rep',label: 'แจ้งสถานะรถ' },
            { id: 'trip_ctrl', label: 'เริ่ม/สิ้นสุดรอบเดินรถ' },
        ]
    },
    {
        category: 'ช่างซ่อม',
        icon: 'fas fa-tools',
        color: 'bg-green-50 text-green-600',
        menus: [
            { id: 'repair_req',  label: 'แจ้งซ่อมรถ' },
            { id: 'repair_hist', label: 'ดูประวัติซ่อม' },
            { id: 'repair_upd',  label: 'อัปเดตสถานะการซ่อม' },
        ]
    },
    {
        category: 'ผู้บริหาร',
        icon: 'fas fa-chart-bar',
        color: 'bg-purple-50 text-purple-600',
        menus: [
            { id: 'report_view', label: 'ดูรายงานสถิติ' },
            { id: 'report_dl',   label: 'ดาวน์โหลดรายงาน' },
        ]
    },
];

// roles: admin, student, driver, executive, mechanic
const defaultPermissions = {
    login:       [1, 1, 1, 1, 1],
    tram_data:   [1, 0, 0, 0, 0],
    route_edit:  [1, 0, 0, 0, 0],
    stop_manage: [1, 0, 0, 0, 0],
    user_manage: [1, 0, 0, 0, 0],
    role_set:    [1, 0, 0, 0, 0],
    realtime:    [1, 1, 1, 1, 0],
    stop_view:   [1, 1, 1, 1, 0],
    call_tram:   [1, 1, 0, 0, 0],
    gps_send:    [1, 0, 1, 0, 0],
    status_rep:  [1, 0, 1, 0, 0],
    trip_ctrl:   [1, 0, 1, 0, 0],
    repair_req:  [1, 0, 1, 0, 1],
    repair_hist: [1, 0, 1, 1, 1],
    repair_upd:  [1, 0, 0, 0, 1],
    report_view: [1, 0, 0, 1, 0],
    report_dl:   [1, 0, 0, 1, 0],
};

// ตรวจสอบและล้าง localStorage ที่เสีย (กรณีเคยบันทึก undefined ลงไป)
(function() {
    try {
        const raw = localStorage.getItem('yru_role_permissions');
        if (!raw || raw === 'undefined' || raw === 'null') {
            localStorage.removeItem('yru_role_permissions');
        }
    } catch(e) {}
})();

// โหลดจาก LocalStorage (ถ้ามี) หรือใช้ค่า default
let rolePermissions = getStorage('yru_role_permissions', defaultPermissions);
if (!rolePermissions || typeof rolePermissions !== 'object') {
    rolePermissions = JSON.parse(JSON.stringify(defaultPermissions));
}

function renderRolePermissionTable() {
    const tbody = document.getElementById('rolePermissionTable');
    if (!tbody) return;
    tbody.innerHTML = '';

    // ตรวจสอบข้อมูลสิทธิ์ว่าสมบูรณ์ไหม ถ้าไม่ใช้ค่า default
    if (!rolePermissions || typeof rolePermissions !== 'object' || Array.isArray(rolePermissions)) {
        rolePermissions = JSON.parse(JSON.stringify(defaultPermissions));
        setStorage('yru_role_permissions', defaultPermissions);
    }

    const roles = ['admin', 'student', 'driver', 'executive', 'mechanic'];

    permissionGroups.forEach((group) => {
        const rowCount = group.menus.length;

        group.menus.forEach((menu, menuIdx) => {
            const row = document.createElement('tr');
            row.className = 'hover:bg-pink-50/30 transition-colors';

            // คอลัมน์ชื่อเมนู
            const menuCell = document.createElement('td');
            menuCell.className = 'p-4 text-gray-700 font-medium whitespace-nowrap border-r border-gray-100';
            menuCell.textContent = menu.label;
            row.appendChild(menuCell);

            // คอลัมน์ toggle แต่ละ role
            roles.forEach((role, roleIdx) => {
                const isOn = rolePermissions[menu.id] ? !!rolePermissions[menu.id][roleIdx] : false;
                const switchId = `perm_${menu.id}_${role}`;

                const cell = document.createElement('td');
                cell.className = 'p-4 text-center';
                cell.innerHTML = `
                    <label class="toggle-switch" style="cursor: not-allowed; opacity: 0.6;">
                        <input type="checkbox" id="${switchId}" ${isOn ? 'checked' : ''} disabled
                            onchange="togglePermission('${menu.id}', ${roleIdx}, this.checked)">
                        <span class="toggle-slider" style="pointer-events: none;"></span>
                    </label>
                `;
                row.appendChild(cell);
            });

            tbody.appendChild(row);
        });
    });
}

function togglePermission(menuId, roleIdx, value) {
    if (!rolePermissions[menuId]) rolePermissions[menuId] = [0, 0, 0, 0, 0];
    rolePermissions[menuId][roleIdx] = value ? 1 : 0;
    setStorage('yru_role_permissions', rolePermissions);
}

// ===== Maintenance Dashboard JS Logic =====
let currentMaintTab = 'active';

function switchMaintDashboardTab(tab) {
    currentMaintTab = tab;
    const btnActive = document.getElementById("btnMaintTabActive");
    const btnHistory = document.getElementById("btnMaintTabHistory");
    if (tab === 'active') {
        btnActive.className = "px-4 py-2.5 font-bold text-sm border-b-2 border-pink-500 text-pink-600 focus:outline-none transition flex items-center gap-1.5";
        btnHistory.className = "px-4 py-2.5 text-sm border-b-2 border-transparent text-gray-500 hover:text-gray-700 focus:outline-none transition flex items-center gap-1.5";
    } else {
        btnActive.className = "px-4 py-2.5 text-sm border-b-2 border-transparent text-gray-500 hover:text-gray-700 focus:outline-none transition flex items-center gap-1.5";
        btnHistory.className = "px-4 py-2.5 font-bold text-sm border-b-2 border-pink-500 text-pink-600 focus:outline-none transition flex items-center gap-1.5";
    }
    renderMaintenanceDashboard();
}

function renderMaintenanceDashboard() {
    // 1. Recalculate summary cards
    const waitingTrams = trams.filter(t => t.status === "รถขัดข้อง");
    const repairingTrams = trams.filter(t => t.status === "กำลังปรับปรุง" || t.status === "ระงับการใช้งาน");
    
    // Calculate total completed jobs in 2569
    let completedCount = 5; // default starting point
    trams.forEach(t => {
        if (t.maintenance) {
            t.maintenance.forEach(log => {
                if (log.date && log.date.includes("2569") && !["05/07/2569", "28/06/2569", "01/07/2569", "15/06/2569", "06/07/2569", "24/06/2569", "04/07/2569", "20/06/2569"].includes(log.date)) {
                    completedCount++;
                }
            });
        }
    });

    document.getElementById("maint-card-waiting").innerText = waitingTrams.length + " คัน";
    document.getElementById("maint-card-repairing").innerText = repairingTrams.length + " คัน";
    document.getElementById("maint-card-completed").innerText = completedCount + " เคส";
    
    // Scheduled count based on battery level (< 20%) or maintenance schedules
    const scheduledTrams = trams.filter(t => t.battery < 20 || t.status === "รถขัดข้อง");
    document.getElementById("maint-card-scheduled").innerText = scheduledTrams.length + " คัน";

    // 2. Render Table Area
    const container = document.getElementById("maintTableContainer");
    if (!container) return;

    if (currentMaintTab === 'active') {
        // Active repairing or broken vehicles
        const activeTrams = trams.filter(t => t.status === "รถขัดข้อง" || t.status === "กำลังปรับปรุง");
        
        if (activeTrams.length === 0) {
            container.innerHTML = `
                <div class="text-center py-12 text-gray-400">
                    <i class="fas fa-check-circle text-4xl text-green-500 mb-3"></i>
                    <p class="text-sm font-medium">ไม่มีรถไฟฟ้าขัดข้องหรืออยู่ระหว่างการซ่อมแซมในขณะนี้</p>
                </div>
            `;
            return;
        }

        let html = `
            <table class="w-full text-left min-w-[900px]">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider">
                    <tr class="border-b">
                        <th class="p-4">รหัสรถ / ทะเบียน</th>
                        <th class="p-4">อาการเสีย / หัวข้อ</th>
                        <th class="p-4">ผู้แจ้ง</th>
                        <th class="p-4 text-center">ระดับความเร่งด่วน</th>
                        <th class="p-4">วันที่แจ้ง</th>
                        <th class="p-4 text-center">สถานะ</th>
                        <th class="p-4 text-center">การจัดการ</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100">`;

        trams.forEach((t, index) => {
            if (t.status === "รถขัดข้อง" || t.status === "กำลังปรับปรุง") {
                const urgency = (t.id === "EV-03" || t.battery < 20) 
                    ? `<span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold bg-red-100 text-red-700">🔴 ด่วน</span>`
                    : `<span class="inline-block px-2 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-700">🟡 ปกติ</span>`;
                
                const statusBadge = t.status === "รถขัดข้อง"
                    ? `<span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold bg-red-50 text-red-655 border border-red-200">🔴 รอซ่อม</span>`
                    : `<span class="inline-block px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-50 text-amber-655 border border-amber-200">🟡 กำลังซ่อม</span>`;

                const actionButton = t.status === "รถขัดข้อง"
                    ? `<button onclick="acceptRepairCase(${index})" class="bg-blue-600 text-white px-3 py-1.5 rounded-lg hover:bg-blue-700 transition text-xs font-semibold shadow-sm flex items-center gap-1"><i class="fas fa-wrench"></i> รับเคสซ่อม</button>`
                    : `<button onclick="openRepairCompleteModal(${index})" class="bg-green-655 text-white px-3 py-1.5 rounded-lg hover:bg-green-700 transition text-xs font-semibold shadow-sm flex items-center gap-1"><i class="fas fa-check"></i> ปิดงานซ่อม</button>`;

                const reportedBy = t.updated_by || "admin@yru.ac.th";
                const reportedDate = t.updated_at || "- ยังไม่มีการอัปเดต -";

                html += `
                    <tr class="hover:bg-gray-50/50 transition">
                        <td class="p-4 font-semibold text-gray-800">${t.id} <span class="text-xs text-gray-400 font-normal">(${t.plate})</span></td>
                        <td class="p-4 text-gray-600 max-w-[250px] truncate" title="${t.active_issue || 'รอดำเนินการบำรุงรักษา'}">${t.active_issue || "รอดำเนินการบำรุงรักษา"}</td>
                        <td class="p-4 text-xs text-gray-500 font-mono">${reportedBy}</td>
                        <td class="p-4 text-center">${urgency}</td>
                        <td class="p-4 text-gray-500 font-medium">${reportedDate}</td>
                        <td class="p-4 text-center">${statusBadge}</td>
                        <td class="p-4 flex justify-center">${actionButton}</td>
                    </tr>`;
            }
        });

        html += `</tbody></table>`;
        container.innerHTML = html;
    } else {
        // Historical log items sorted combined
        let allLogs = [];
        trams.forEach((t, tIndex) => {
            if (t.maintenance) {
                t.maintenance.forEach(log => {
                    allLogs.push({
                        tramId: t.id,
                        tramPlate: t.plate,
                        date: log.date,
                        detail: log.detail,
                        technician: log.technician
                    });
                });
            }
        });

        if (allLogs.length === 0) {
            container.innerHTML = `
                <div class="text-center py-12 text-gray-400">
                    <i class="fas fa-history text-4xl mb-3"></i>
                    <p class="text-sm font-medium">ยังไม่มีประวัติการซ่อมบำรุงในระบบ</p>
                </div>
            `;
            return;
        }

        // Sort descending by date helper
        allLogs.sort((a, b) => {
            const parseDate = (dStr) => {
                if (!dStr) return 0;
                const p = dStr.split('/');
                if (p.length !== 3) return 0;
                // convert to CE year for standard Date parsing
                const year = parseInt(p[2]) - 543;
                return new Date(`${year}-${p[1]}-${p[0]}`).getTime();
            };
            return parseDate(b.date) - parseDate(a.date);
        });

        let html = `
            <table class="w-full text-left min-w-[900px]">
                <thead class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider">
                    <tr class="border-b">
                        <th class="p-4">รหัสรถ / ทะเบียน</th>
                        <th class="p-4">รายการซ่อมบำรุง / ผลงาน</th>
                        <th class="p-4">ช่างผู้รับผิดชอบ</th>
                        <th class="p-4">วันที่ซ่อมเสร็จ</th>
                        <th class="p-4 text-center">สถานะ</th>
                    </tr>
                </thead>
                <tbody class="text-sm divide-y divide-gray-100">`;

        allLogs.forEach(log => {
            html += `
                <tr class="hover:bg-gray-50/50 transition">
                    <td class="p-4 font-semibold text-gray-800">${log.tramId} <span class="text-xs text-gray-400 font-normal">(${log.tramPlate})</span></td>
                    <td class="p-4 text-gray-700 font-medium">${log.detail}</td>
                    <td class="p-4 text-gray-500 font-medium"><i class="fas fa-wrench text-xs text-gray-400 mr-1"></i>${log.technician}</td>
                    <td class="p-4"><span class="bg-gray-100 text-gray-600 text-[10px] px-2 py-0.5 rounded font-mono font-medium">${log.date}</span></td>
                    <td class="p-4 text-center"><span class="inline-block px-2 py-0.5 rounded-full text-[10px] font-bold bg-green-50 text-green-700 border border-green-200">🟢 ซ่อมเสร็จสิ้น</span></td>
                </tr>`;
        });

        html += `</tbody></table>`;
        container.innerHTML = html;
    }
}

function acceptRepairCase(index) {
    trams[index].status = "ระงับการใช้งาน";
    trams[index].driver = "";
    trams[index].driver_id = "";
    setStorage("yru_trams_v18", trams);
    window.dispatchEvent(new Event('storage'));
    renderMaintenanceDashboard();
    renderTramTable();
}

function openRepairCompleteModal(index) {
    const tram = trams[index];
    document.getElementById("editRepairTramIndex").value = index;
    
    // Populate select
    const select = document.getElementById("modalRepairTramSelect");
    select.innerHTML = `<option value="${tram.id}">${tram.id} - ${tram.name || 'รถไฟฟ้า'} (${tram.plate})</option>`;
    select.disabled = true;

    // Prefill date with today YYYY-MM-DD
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    document.getElementById("modalRepairDate").value = `${yyyy}-${mm}-${dd}`;

    // Reset details
    document.getElementById("modalRepairSummary").value = "";
    document.getElementById("modalRepairDetail").value = "";
    
    // Clear spare parts checkboxes
    document.querySelectorAll("input[name='repairSpareParts']").forEach(cb => cb.checked = false);

    document.getElementById("repairCompleteModal").classList.remove("hidden");
}

function openRepairCompleteModalDirect() {
    document.getElementById("editRepairTramIndex").value = "-1";
    
    // Populate all trams as options
    const select = document.getElementById("modalRepairTramSelect");
    select.innerHTML = "";
    trams.forEach(t => {
        select.innerHTML += `<option value="${t.id}">${t.id} - ${t.name || 'รถไฟฟ้า'} (${t.plate})</option>`;
    });
    select.disabled = false;

    // Prefill date with today YYYY-MM-DD
    const today = new Date();
    const yyyy = today.getFullYear();
    const mm = String(today.getMonth() + 1).padStart(2, '0');
    const dd = String(today.getDate()).padStart(2, '0');
    document.getElementById("modalRepairDate").value = `${yyyy}-${mm}-${dd}`;

    // Reset details
    document.getElementById("modalRepairSummary").value = "";
    document.getElementById("modalRepairDetail").value = "";
    
    // Clear spare parts checkboxes
    document.querySelectorAll("input[name='repairSpareParts']").forEach(cb => cb.checked = false);

    document.getElementById("repairCompleteModal").classList.remove("hidden");
}

function closeRepairCompleteModal() {
    document.getElementById("repairCompleteModal").classList.add("hidden");
}

function convertToThaiLogDate(dateStr) {
    if (!dateStr) return "";
    const parts = dateStr.split('-');
    if (parts.length !== 3) return dateStr;
    const year = parseInt(parts[0]) + 543;
    const month = parts[1];
    const date = parts[2];
    return `${date}/${month}/${year}`;
}

function saveRepairJob() {
    const editIndex = parseInt(document.getElementById("editRepairTramIndex").value);
    const summary = document.getElementById("modalRepairSummary").value.trim();
    const detail = document.getElementById("modalRepairDetail").value.trim();
    const technician = document.getElementById("modalRepairTechnician").value;
    const finishDate = document.getElementById("modalRepairDate").value;

    let tramId = "";
    if (editIndex === -1) {
        tramId = document.getElementById("modalRepairTramSelect").value;
    } else {
        tramId = trams[editIndex].id;
    }

    if (!tramId) { alert("กรุณาเลือกรถไฟฟ้า!"); return; }
    if (!summary) { alert("กรุณากรอกหัวข้อสรุปการซ่อมบำรุง!"); return; }
    if (!detail) { alert("กรุณากรอกรายละเอียดงานซ่อมเชิงลึก!"); return; }
    if (!finishDate) { alert("กรุณาเลือกวันที่ซ่อมเสร็จ!"); return; }

    const targetTram = trams.find(t => t.id === tramId);
    if (!targetTram) { alert("ไม่พบข้อมูลรถไฟฟ้าในระบบ!"); return; }

    // Gather selected spare parts
    let selectedParts = [];
    document.querySelectorAll("input[name='repairSpareParts']:checked").forEach(cb => {
        selectedParts.push(cb.value);
    });

    let detailText = summary;
    if (selectedParts.length > 0) {
        detailText += ` - อะไหล่: ${selectedParts.join(', ')} (${detail})`;
    } else {
        detailText += ` (${detail})`;
    }

    // Add log
    if (!targetTram.maintenance) targetTram.maintenance = [];
    targetTram.maintenance.unshift({
        date: convertToThaiLogDate(finishDate),
        detail: detailText,
        technician: technician
    });

    // Reset status & issues
    targetTram.status = "พร้อมใช้งาน";
    targetTram.active_issue = "";
    targetTram.updated_by = "admin@yru.ac.th";
    targetTram.updated_at = getThaiDateString();

    // Save
    setStorage("yru_trams_v18", trams);
    
    // Close modal and refresh
    closeRepairCompleteModal();
    renderMaintenanceDashboard();
    renderTramTable();
    
    alert(`บันทึกผลงานการซ่อมบำรุงรถไฟฟ้า ${targetTram.id} คืนสถานะพร้อมใช้งานเรียบร้อยแล้ว!`);
}

// Initialize system routes dropdown and load Dashboard
try {
    if (typeof populateRouteDropdownInTramModal === 'function') {
        populateRouteDropdownInTramModal();
    }
} catch(e) {}
function restoreAdminUserSession() {
    let userObj = null;
    try {
        const rawUser = sessionStorage.getItem('yru_user_login') || localStorage.getItem('yru_user_login') || sessionStorage.getItem('yru_current_user') || localStorage.getItem('yru_current_user');
        if (rawUser) userObj = JSON.parse(rawUser);
    } catch(e) {}

    if (userObj) {
        try {
            sessionStorage.setItem('yru_user_login', JSON.stringify(userObj));
            localStorage.setItem('yru_user_login', JSON.stringify(userObj));
        } catch(e) {}

        const displayName = userObj.name || userObj.email || userObj.username || 'ผู้ดูแลระบบ';
        const displayEmail = userObj.email || userObj.username || 'admin@yru.ac.th';
        const cleanName = displayName.replace(/^(นาย|นางสาว|นาง|ดร\.|อาจารย์|ช่าง)\s*/g, '').trim();
        const displayInitial = (cleanName[0] || 'A').toUpperCase();

        const nameSpan = document.getElementById('headerProfileName');
        const avatarDiv = document.getElementById('headerProfileAvatar');
        if (nameSpan) nameSpan.innerText = displayEmail;
        if (avatarDiv) avatarDiv.innerText = displayInitial;
    }
}

try { restoreAdminUserSession(); } catch(e) {}

const initialAdminTab = (location.hash ? location.hash.replace('#', '') : '') || sessionStorage.getItem('admin_active_tab') || "dashboard";
showPage(initialAdminTab);

// ===== Auto-refresh คิวเรียกรถจาก localStorage ทุก 5 วินาที =====
setInterval(() => {
    // อัปเดตเฉพาะตอนอยู่หน้า dashboard
    const dashPage = document.getElementById('dashboard');
    if (dashPage && dashPage.style.display !== 'none') {
        renderDashboardData();
    }
    // อัปเดตเฉพาะตอนอยู่หน้า รายชื่อบุคคลภายนอก
    const extPage = document.getElementById('externalUsers');
    if (extPage && extPage.style.display !== 'none') {
        extUsers = getStorage("yru_external_users_v4", defaultExternalUsers);
        renderExternalUserTable();
    }
}, 5000);

// รับ event จาก tab อื่น (passenger กดเรียกรถ / สมัครสมาชิก) แบบ real-time
window.addEventListener('storage', (event) => {
    if (event.key === 'yru_call_queue') {
        const dashPage = document.getElementById('dashboard');
        if (dashPage && dashPage.style.display !== 'none') {
            renderDashboardData();
        }
    }
    if (event.key === 'yru_external_users_v4') {
        extUsers = getStorage("yru_external_users_v4", defaultExternalUsers);
        const extPage = document.getElementById('externalUsers');
        if (extPage && extPage.style.display !== 'none') {
            renderExternalUserTable();
        }
    }
});

// ===== Profile Dropdown =====
function toggleProfileDropdown() {
    const menu = document.getElementById('profileDropdownMenu');
    const icon = document.getElementById('profileDropdownIcon');
    
    if (menu.classList.contains('hidden')) {
        menu.classList.remove('hidden');
        setTimeout(() => {
            menu.classList.remove('opacity-0', 'scale-95');
            menu.classList.add('opacity-100', 'scale-100');
        }, 10);
        if(icon) icon.style.transform = 'rotate(180deg)';
    } else {
        menu.classList.remove('opacity-100', 'scale-100');
        menu.classList.add('opacity-0', 'scale-95');
        setTimeout(() => {
            menu.classList.add('hidden');
        }, 200);
        if(icon) icon.style.transform = 'rotate(0deg)';
    }
}

document.addEventListener('click', function(event) {
    const container = document.getElementById('profileDropdownContainer');
    const menu = document.getElementById('profileDropdownMenu');
    const icon = document.getElementById('profileDropdownIcon');
    
    if (container && menu && !container.contains(event.target)) {
        if (!menu.classList.contains('hidden')) {
            menu.classList.remove('opacity-100', 'scale-100');
            menu.classList.add('opacity-0', 'scale-95');
            setTimeout(() => {
                menu.classList.add('hidden');
            }, 200);
            if(icon) icon.style.transform = 'rotate(0deg)';
        }
    }
});

function resetSystemData() {
    if (confirm("คุณต้องการล้างข้อมูลในระบบและรีเซ็ตเป็นค่าเริ่มต้นล่าสุดใช่หรือไม่? (ข้อมูลที่คุณเคยเพิ่มหรือบันทึกใหม่จะถูกรีเซ็ตกลับเป็นค่าเริ่มต้น)")) {
        localStorage.removeItem("yru_trams_v16");
        localStorage.removeItem("yru_users_v8");
        localStorage.removeItem("yru_users_v7");
        location.reload();
    }
}

function exportSystemData() {
    const data = {
        yru_trams_v16: JSON.parse(localStorage.getItem("yru_trams_v16")),
        yru_users_v8: JSON.parse(localStorage.getItem("yru_users_v8")),
        yru_stops_v2: JSON.parse(localStorage.getItem("yru_stops_v2"))
    };
    const dataStr = "data:text/json;charset=utf-8," + encodeURIComponent(JSON.stringify(data, null, 2));
    const downloadAnchor = document.createElement('a');
    downloadAnchor.setAttribute("href", dataStr);
    downloadAnchor.setAttribute("download", "yru_system_data.json");
    document.body.appendChild(downloadAnchor);
    downloadAnchor.click();
    downloadAnchor.remove();
}

function importSystemData() {
    const input = document.createElement('input');
    input.type = 'file';
    input.accept = '.json';
    input.onchange = e => {
        const file = e.target.files[0];
        if (!file) return;
        const reader = new FileReader();
        reader.onload = readerEvent => {
            try {
                const content = JSON.parse(readerEvent.target.result);
                if (content.yru_trams_v16) localStorage.setItem("yru_trams_v16", JSON.stringify(content.yru_trams_v16));
                if (content.yru_users_v8) localStorage.setItem("yru_users_v8", JSON.stringify(content.yru_users_v8));
                if (content.yru_stops_v2) localStorage.setItem("yru_stops_v2", JSON.stringify(content.yru_stops_v2));
                alert("นำเข้าข้อมูลสำเร็จแล้ว! ระบบกำลังรีโหลด...");
                location.reload();
            } catch (err) {
                alert("ไฟล์ข้อมูลไม่ถูกต้อง: " + err.message);
            }
        }
        reader.readAsText(file, 'UTF-8');
    }
    input.click();
}

// ===== External Users Business Logic & Data Setup =====
const defaultExternalUsers = [
    { id: "EXT001", name: "นายอับดุลเลาะ มะ", contact: "abdul@gmail.com", reg_date: "01/07/2569", status: "ปกติ", reg_timestamp: new Date("2026-07-01").getTime() },
    { id: "EXT002", name: "นางสาวนูรียะห์ ยะลา", contact: "nuriyah@outlook.com", reg_date: "05/07/2569", status: "ปกติ", reg_timestamp: new Date("2026-07-05").getTime() },
    { id: "EXT003", name: "นายซูไฮดี ดาโอะ", contact: "subaidi@hotmail.com", reg_date: "08/07/2569", status: "ถูกระงับ", reg_timestamp: new Date("2026-07-08").getTime() }
];

// Seed surveys to map with ratings if empty
(function() {
    let surveys = JSON.parse(localStorage.getItem('yru_surveys') || '[]');
    if (surveys.length === 0) {
        surveys = [
            {
                time: "12/7/2569 14:32:10",
                date: "12/7/2569",
                driverId: "USR003",
                driverName: "นายสมชาย ใจดี (เล่าปิง)",
                ratings: { q1: 5, q2: 5, q3: 4, q4: 5, q5: 5 },
                avg: 4.80,
                comment: "คนขับพูดจาสุภาพมากครับ รถขับนิ่มปลอดภัยดีมาก",
                userEmail: "fatimah@gmail.com"
            },
            {
                time: "12/7/2569 11:20:15",
                date: "12/7/2569",
                driverId: "USR004",
                driverName: "นายรักสงบ มั่นคง (น้าสงบ)",
                ratings: { q1: 4, q2: 4, q3: 3, q4: 4, q5: 4 },
                avg: 3.80,
                comment: "รถวิ่งช้าไปนิดนึง แต่อย่างอื่นดีหมดเลยค่ะ",
                userEmail: "nuriyah@outlook.com"
            },
            {
                time: "10/7/2569 09:45:00",
                date: "10/7/2569",
                driverId: "USR003",
                driverName: "นายสมชาย ใจดี (เล่าปิง)",
                ratings: { q1: 5, q2: 4, q3: 5, q4: 5, q5: 4 },
                avg: 4.60,
                comment: "มารับตรงเวลา ดีมากครับ",
                userEmail: "abdul@gmail.com"
            },
            {
                time: "08/7/2569 16:15:30",
                date: "08/7/2569",
                driverId: "USR005",
                driverName: "นายประสิทธิ์ เรียนรู้ (พี่สิทธิ์)",
                ratings: { q1: 5, q2: 5, q3: 5, q4: 5, q5: 5 },
                avg: 5.00,
                comment: "สุดยอดการให้บริการครับ ประทับใจมาก",
                userEmail: "abdul@gmail.com"
            }
        ];
        localStorage.setItem('yru_surveys', JSON.stringify(surveys));
    }
})();

let extUsers = getStorage("yru_external_users_v4", defaultExternalUsers);
if (extUsers.find(u => u.contact && u.contact.includes("tasnimsalaeh531@gmail.com"))) {
    extUsers = extUsers.filter(u => !u.contact || !u.contact.includes("tasnimsalaeh531@gmail.com"));
    setStorage("yru_external_users_v4", extUsers);
}

window.renderExternalUserTable = function(filteredData = null) {
    const table = document.getElementById("externalUserTable");
    if (!table) return;
    table.innerHTML = "";
    
    const dataToRender = filteredData ? filteredData : extUsers;
    
    // Update summary cards
    document.getElementById("ext-total-registered").innerText = extUsers.length + " คน";
    
    const activeTodayCount = extUsers.filter(u => u.status === "ปกติ" && (u.id === "EXT001" || u.id === "EXT004")).length;
    document.getElementById("ext-active-today").innerText = activeTodayCount + " คน";
    
    const blockedCount = extUsers.filter(u => u.status === "ถูกระงับ").length;
    document.getElementById("ext-blocked-count").innerText = blockedCount + " คน";

    if (dataToRender.length === 0) {
        table.innerHTML = `<tr><td colspan="5" class="p-8 text-center text-gray-400">❌ ไม่พบรายชื่อบุคคลภายนอกที่ค้นหา</td></tr>`;
        return;
    }

    dataToRender.forEach((user) => {
        const realIndex = extUsers.findIndex(u => u.id === user.id);
        const isBlocked = user.status === "ถูกระงับ";
        const statusBadge = isBlocked 
            ? `<span class="inline-flex items-center gap-1 bg-red-50 text-red-650 px-2.5 py-0.5 rounded-full text-xs font-bold border border-red-200"><i class="fas fa-user-slash text-[10px]"></i> ถูกระงับ</span>`
            : `<span class="inline-flex items-center gap-1 bg-green-50 text-green-700 px-2.5 py-0.5 rounded-full text-xs font-bold border border-green-200"><i class="fas fa-check-circle text-[10px]"></i> ใช้งานปกติ</span>`;
            
        const blockButton = isBlocked
            ? `<button onclick="toggleBlockExternalUser(${realIndex})" class="bg-emerald-50 hover:bg-emerald-100 text-emerald-600 border border-emerald-200 px-3 py-1.5 rounded-xl transition text-xs font-bold flex items-center gap-1.5 shadow-sm active:scale-95"><i class="fas fa-user-check text-[10px]"></i> ยกเลิกระงับ</button>`
            : `<button onclick="toggleBlockExternalUser(${realIndex})" class="bg-red-50 hover:bg-red-100 text-red-655 border border-red-200 px-3 py-1.5 rounded-xl transition text-xs font-bold flex items-center gap-1.5 shadow-sm active:scale-95"><i class="fas fa-user-slash text-[10px]"></i> ระงับการใช้งาน</button>`;

        // Split contact into Phone and Email for clean 2-line layout
        const contactParts = user.contact.split('/');
        const phone = contactParts[0] ? contactParts[0].trim() : '-';
        const email = contactParts[1] ? contactParts[1].trim() : '-';
        
        table.innerHTML += `
        <tr class="border-t hover:bg-gray-50/50 transition">
            <td class="p-4 font-semibold text-gray-800">${user.name}</td>
            <td class="p-4">
                <div class="flex flex-col text-left">
                    <span class="font-bold text-gray-800 text-xs">${phone}</span>
                    <span class="text-[10px] text-gray-400 font-mono mt-0.5">${email}</span>
                </div>
            </td>
            <td class="p-4 text-gray-500 font-medium"><span class="bg-slate-100 text-slate-655 text-[10px] px-2.5 py-1 rounded-md font-mono font-medium">${user.reg_date}</span></td>
            <td class="p-4 text-center">${statusBadge}</td>
            <td class="p-4 text-center flex items-center justify-center gap-2.5 whitespace-nowrap min-w-[260px]">
                <button onclick="viewUserRatings('${user.id}')" class="bg-pink-50 text-pink-600 border border-pink-200 px-3 py-1.5 rounded-xl hover:bg-pink-100 transition text-xs font-bold flex items-center gap-1.5 shadow-sm active:scale-95"><i class="fas fa-star text-[10px]"></i> ประวัติประเมิน</button>
                ${blockButton}
            </td>
        </tr>`;
    });
}

window.filterExternalUsers = function() {
    const timeFilter = document.getElementById("extFilterTime").value;
    const statusFilter = document.getElementById("extFilterStatus").value;
    const searchQuery = document.getElementById("extSearchInput").value.toLowerCase().trim();

    const filtered = extUsers.filter(u => {
        const matchesStatus = (statusFilter === "ทั้งหมด" || (statusFilter === "ปกติ" && u.status === "ปกติ") || (statusFilter === "ระงับการใช้งาน" && u.status === "ถูกระงับ"));
        
        let matchesTime = true;
        const now = Date.now();
        if (timeFilter === "สัปดาห์นี้") {
            matchesTime = (now - u.reg_timestamp) <= 7 * 24 * 60 * 60 * 1000;
        } else if (timeFilter === "เดือนนี้") {
            matchesTime = (now - u.reg_timestamp) <= 30 * 24 * 60 * 60 * 1000;
        }
        
        const matchesSearch = !searchQuery || 
            u.name.toLowerCase().includes(searchQuery) || 
            u.contact.toLowerCase().includes(searchQuery);

        return matchesStatus && matchesTime && matchesSearch;
    });

    renderExternalUserTable(filtered);
}

window.toggleBlockExternalUser = function(index) {
    const user = extUsers[index];
    const isBlocked = user.status === "ถูกระงับ";
    const actionText = isBlocked ? "ปลดระงับการใช้งาน" : "ระงับการใช้งาน";
    const confirmColor = isBlocked ? "#10b981" : "#ef4444";
    
    Swal.fire({
        title: `ยืนยัน${actionText}?`,
        text: `คุณต้องการที่จะ${actionText} บัญชีผู้ใช้งาน "${user.name}" หรือไม่?`,
        icon: "warning",
        showCancelButton: true,
        confirmButtonText: "ยืนยัน",
        cancelButtonText: "ยกเลิก",
        confirmButtonColor: confirmColor,
        cancelButtonColor: "#6b7280"
    }).then((result) => {
        if (result.isConfirmed) {
            extUsers[index].status = isBlocked ? "ปกติ" : "ถูกระงับ";
            setStorage("yru_external_users_v4", extUsers);
            renderExternalUserTable();
            Swal.fire({
                title: "ดำเนินการสำเร็จ! 🎉",
                text: `เปลี่ยนสถานะบัญชีเป็น ${extUsers[index].status === 'ปกติ' ? 'ใช้งานปกติ' : 'ถูกระงับ'} เรียบร้อยแล้ว`,
                icon: "success",
                confirmButtonColor: "#ec4899"
            });
        }
    });
}

window.exportExternalUsers = function() {
    const headers = ['ชื่อ-นามสกุล', 'ข้อมูลติดต่อ (เบอร์โทร/อีเมล)', 'วันที่สมัครเข้าใช้งาน', 'สถานะ'];
    let csvContent = "\uFEFF"; 
    csvContent += headers.join(",") + "\r\n";
    
    extUsers.forEach(user => {
        csvContent += `"${user.name}","${user.contact}","${user.reg_date}","${user.status === 'ปกติ' ? 'ใช้งานปกติ' : 'ถูกระงับ'}"\r\n`;
    });
    
    const blob = new Blob([csvContent], { type: 'text/csv;charset=utf-8;' });
    const link = document.createElement("a");
    const url = URL.createObjectURL(blob);
    link.setAttribute("href", url);
    link.setAttribute("download", `yru_external_users_${new Date().toISOString().slice(0,10)}.csv`);
    link.style.visibility = 'hidden';
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

window.viewUserRatings = function(userId) {
    const user = extUsers.find(u => u.id === userId);
    if (!user) return;
    
    document.getElementById("extRatingUserName").innerText = user.name;
    document.getElementById("extRatingUserContact").innerText = user.contact;
    
    const userEmail = user.contact.split('/').pop().trim();
    
    const surveys = JSON.parse(localStorage.getItem('yru_surveys') || '[]');
    const userSurveys = surveys.filter(s => s.userEmail && s.userEmail.toLowerCase() === userEmail.toLowerCase());
    
    const listContainer = document.getElementById("extRatingsList");
    listContainer.innerHTML = "";
    
    if (userSurveys.length === 0) {
        listContainer.innerHTML = `
            <div class="text-center py-10 text-gray-400">
                <i class="far fa-frown text-4xl mb-2 text-gray-300"></i>
                <p class="text-sm font-medium">ผู้ใช้งานรายนี้ยังไม่เคยส่งแบบประเมินความพึงพอใจ</p>
            </div>
        `;
    } else {
        userSurveys.forEach(survey => {
            const starsFill = '★'.repeat(Math.round(survey.avg)) + '☆'.repeat(5 - Math.round(survey.avg));
            
            listContainer.innerHTML += `
                <div class="bg-slate-50 border border-slate-100 rounded-2xl p-4 space-y-2 text-left">
                    <div class="flex justify-between items-start">
                        <div>
                            <span class="text-[10px] bg-pink-100 text-pink-655 font-bold px-2.5 py-0.5 rounded-full uppercase tracking-wider">${survey.driverName}</span>
                            <div class="text-[10px] text-slate-400 font-mono mt-1"><i class="far fa-clock mr-1"></i>&nbsp;${survey.time}</div>
                        </div>
                        <div class="text-right">
                            <div class="text-xs text-amber-400 font-bold tracking-tight">${starsFill}</div>
                            <span class="text-[10px] font-black text-pink-650 bg-pink-50 px-2 py-0.5 rounded-md mt-1 inline-block">${survey.avg.toFixed(2)}</span>
                        </div>
                    </div>
                    <div class="text-xs text-slate-700 bg-white border border-slate-100 p-2.5 rounded-xl leading-relaxed">
                        <b>💬 ความคิดเห็น:</b> ${survey.comment || '<span class="text-gray-400 font-light italic">ไม่มีข้อเสนอแนะ</span>'}
                    </div>
                </div>
            `;
        });
    }
    
    document.getElementById("extRatingsModal").classList.remove("hidden");
}

window.closeExtRatingsModal = function() {
    document.getElementById("extRatingsModal").classList.add("hidden");
}

// =========================================================================
// INTEGRATED BUS STOP & ROUTE MANAGEMENT ENGINE (SPLIT SCREEN LAYOUT 40% / 60%)
// =========================================================================
let systemRoutes = [];
let builderMap = null;
let drawnPolyline = null;
let drawnPoints = [];
let routeMapMarkers = [];
let availableStopMarkers = [];
let selectedRouteStops = [];
let intSelectedRouteStops = [];
let currentMapMode = 'draw';
let stopMarkerMap = {};
let intMap = null;
let intCurrentTab = 'stops'; // 'stops' or 'routes'
let intTempMarker = null; // Temporary draggable marker for stops
let intStopMarkersMap = {}; // stop.name -> L.marker
let intRoutePolyline = null; // Polyline for current editing route
let intRouteMarkers = []; // Markers for active route stops
let intRouteWaypoints = []; // Coordinates array for custom polyline drawing: [[lat, lng], ...]
let intWaypointMarkers = []; // Leaflet markers for drawn waypoints

// Initializer when navigating to 'route' page
function initIntegratedRouteModule() {
    // Sync data from localStorage
    stops = getStorage("yru_stops_v2", defaultStops);
    if (!stops || !Array.isArray(stops) || stops.length === 0) {
        stops = defaultStops;
        setStorage("yru_stops_v2", stops);
    }

    systemRoutes = getStorage("yru_routes_v1", []);
    if (!systemRoutes || !Array.isArray(systemRoutes)) {
        systemRoutes = [];
        setStorage("yru_routes_v1", systemRoutes);
    }

    // Update badges
    const sBadge = document.getElementById("intStopBadgeCount");
    const rBadge = document.getElementById("intRouteBadgeCount");
    if (sBadge) sBadge.textContent = stops.length;
    if (rBadge) rBadge.textContent = systemRoutes.length;

    // Render lists
    renderIntegratedStopsList();
    renderIntegratedRouteStopsChecklist();
    renderIntegratedRoutesList();

    // Init Map (delay slightly to ensure container is visible)
    setTimeout(() => {
        initIntegratedMap();
    }, 150);
}

// -------------------------------------------------------------------------
// LEAFLET MAP INITIALIZATION & INTERACTIVITY
// -------------------------------------------------------------------------
// LEAFLET MAP INITIALIZATION & INTERACTIVITY
// -------------------------------------------------------------------------
function initIntegratedMap() {
    const mapContainer = document.getElementById('integratedMap');
    if (!mapContainer) return;

    if (typeof L === 'undefined') {
        setTimeout(initIntegratedMap, 200);
        return;
    }

    // Force explicit container dimensions
    mapContainer.style.height = "580px";
    mapContainer.style.minHeight = "580px";
    mapContainer.style.width = "100%";

    const defaultCenter = [6.548850, 101.289800];

    // If map already exists, simply invalidate size and refresh features
    if (intMap) {
        try {
            intMap.invalidateSize();
            renderIntegratedMapFeatures();
            fitIntegratedMapBounds();
            return;
        } catch(e) {
            try { intMap.remove(); } catch(err) {}
            intMap = null;
        }
    }

    try {
        intMap = L.map('integratedMap', {
            center: defaultCenter,
            zoom: 17,
            zoomControl: true,
            minZoom: 15,
            maxZoom: 20
        });

        // Google Maps Standard / Roadmap Layer (Fast & Reliable)
        const googleRoadmap = L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
            attribution: '&copy; Google Maps'
        });

        // Google Satellite Hybrid Layer
        const googleHybrid = L.tileLayer('https://{s}.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
            attribution: '&copy; Google Maps'
        });

        // OpenStreetMap Standard Tile Layer
        const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
            maxZoom: 19,
            attribution: '&copy; OpenStreetMap contributors'
        });

        // Set Google Roadmap as default layer
        googleRoadmap.addTo(intMap);

        // Add Layer Controls (Top Right)
        const baseMaps = {
            "แผนที่ปกติ (Google Roadmap)": googleRoadmap,
            "แผนที่ดาวเทียม (Satellite Hybrid)": googleHybrid,
            "แผนที่ OpenStreetMap": osmLayer
        };
        L.control.layers(baseMaps, null, { position: 'topright' }).addTo(intMap);

        // MAP CLICK EVENT -> Auto-fill Lat/Lng in Tab 1 OR Draw Waypoints in Tab 2
        intMap.on('click', function(e) {
            const lat = parseFloat(e.latlng.lat.toFixed(6));
            const lng = parseFloat(e.latlng.lng.toFixed(6));

            if (intCurrentTab === 'stops') {
                const latInput = document.getElementById('intStopLat');
                const lngInput = document.getElementById('intStopLng');
                if (latInput) latInput.value = lat;
                if (lngInput) lngInput.value = lng;
                placeTempPinkMarker(lat, lng);
                showIntegratedToast(`📍 ดึงพิกัดเรียบร้อย!`, `Lat: ${lat}, Lng: ${lng}`);
            } else if (intCurrentTab === 'routes') {
                intRouteWaypoints.push([lat, lng]);
                renderIntegratedMapFeatures();
                showIntegratedToast(`🛣️ เพิ่มจุดทางเลี้ยว #${intRouteWaypoints.length}`, `Lat: ${lat}, Lng: ${lng}`);
            }
        });

        // Initial render of existing features
        renderIntegratedMapFeatures();

        setTimeout(() => {
            if (intMap) {
                intMap.invalidateSize();
                fitIntegratedMapBounds();
            }
        }, 100);

        setTimeout(() => {
            if (intMap) {
                intMap.invalidateSize();
            }
        }, 300);

    } catch(err) {
        console.error("[initIntegratedMap] Error:", err);
    }
}

// Place temporary pink marker on map
function placeTempPinkMarker(lat, lng) {
    if (!intMap) return;

    if (intTempMarker) {
        intTempMarker.setLatLng([lat, lng]);
    } else {
        const pinkIcon = L.divIcon({
            className: 'custom-temp-pink-marker',
            html: `<div style="background-color:#E91E63; width:22px; height:22px; border-radius:50%; border:3px solid white; box-shadow:0 3px 10px rgba(233,30,99,0.5); animation: pulse 1.5s infinite;"></div>`,
            iconSize: [22, 22],
            iconAnchor: [11, 11]
        });

        intTempMarker = L.marker([lat, lng], { icon: pinkIcon, draggable: true }).addTo(intMap);
        intTempMarker.on('dragend', function(evt) {
            const pos = evt.target.getLatLng();
            const nLat = parseFloat(pos.lat.toFixed(6));
            const nLng = parseFloat(pos.lng.toFixed(6));
            document.getElementById('intStopLat').value = nLat;
            document.getElementById('intStopLng').value = nLng;
            showIntegratedToast(`📍 ปรับพิกัดใหม่แล้ว!`, `Lat: ${nLat}, Lng: ${nLng}`);
        });
    }
    intTempMarker.bindPopup(`<b>พิกัดที่เลือกใหม่</b><br>Lat: ${lat}<br>Lng: ${lng}`).openPopup();
}

// Remove temporary pink marker
function clearTempPinkMarker() {
    if (intTempMarker && intMap) {
        intMap.removeLayer(intTempMarker);
        intTempMarker = null;
    }
}

// Toast notification
function showIntegratedToast(title, text) {
    const toast = document.getElementById('intMapNotification');
    const tTitle = document.getElementById('intToastTitle');
    const tText  = document.getElementById('intToastText');
    if (!toast) return;

    if (tTitle) tTitle.textContent = title;
    if (tText)  tText.textContent  = text;

    toast.classList.remove('hidden');
    setTimeout(() => {
        toast.classList.add('hidden');
    }, 2800);
}

// -------------------------------------------------------------------------
// TABS SWITCHER & VIEW CONTROL
// -------------------------------------------------------------------------
function switchIntegratedTab(tab) {
    intCurrentTab = tab;
    const btnStops  = document.getElementById('int-tab-btn-stops');
    const btnRoutes = document.getElementById('int-tab-btn-routes');
    const contentStops  = document.getElementById('int-tab-content-stops');
    const contentRoutes = document.getElementById('int-tab-content-routes');
    const mapInstruction = document.getElementById('intMapInstructionText');
    const drawControls = document.getElementById('intRouteDrawControls');

    if (tab === 'stops') {
        btnStops.className  = "flex-1 py-2.5 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 bg-pink-600 text-white shadow-sm";
        btnRoutes.className = "flex-1 py-2.5 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 text-gray-600 hover:bg-gray-200/60";
        contentStops.classList.remove('hidden');
        contentRoutes.classList.add('hidden');
        if (drawControls) drawControls.classList.add('hidden');
        if (mapInstruction) mapInstruction.innerHTML = `📍 คลิกที่ใดก็ได้บนแผนที่ เพื่อดึงพิกัดใส่ช่อง Lat/Lng อัตโนมัติ`;
    } else {
        btnRoutes.className = "flex-1 py-2.5 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 bg-pink-600 text-white shadow-sm";
        btnStops.className  = "flex-1 py-2.5 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 text-gray-600 hover:bg-gray-200/60";
        contentRoutes.classList.remove('hidden');
        contentStops.classList.add('hidden');
        if (drawControls) drawControls.classList.remove('hidden');
        if (mapInstruction) mapInstruction.innerHTML = `🛣️ ติ๊กเลือกจุดจอด และ<span class="font-bold text-yellow-300 underline">คลิกบนแผนที่ตามโค้งถนน</span>เพื่อวาดแนวเส้นทาง`;
    }

    if (intMap) {
        intMap.invalidateSize();
        renderIntegratedMapFeatures();
    }
}

function undoRouteWaypoint() {
    if (intRouteWaypoints.length > 0) {
        intRouteWaypoints.pop();
        renderIntegratedMapFeatures();
        showIntegratedToast(`↩️ ย้อนกลับพิกัดเส้นทาง`, `เหลือ ${intRouteWaypoints.length} จุด`);
    }
}

function clearRouteWaypoints() {
    if (intRouteWaypoints.length > 0) {
        intRouteWaypoints = [];
        renderIntegratedMapFeatures();
        showIntegratedToast(`🧹 ล้างเส้นวาดแล้ว`, `สามารถคลิกเริ่มวาดใหม่ได้`);
    }
}

// -------------------------------------------------------------------------
// TAB 1: STOPS MANAGEMENT LOGIC
// -------------------------------------------------------------------------
function renderIntegratedStopsList() {
    const container = document.getElementById('intStopsListContainer');
    if (!container) return;

    if (stops.length === 0) {
        container.innerHTML = `<div class="bg-gray-50 border border-dashed border-gray-200 rounded-xl p-4 text-center text-xs text-gray-400">
            ยังไม่มีจุดจอดในระบบ คลิกบนแผนที่เพื่อเพิ่มจุดจอดแรก
        </div>`;
        return;
    }

    container.innerHTML = stops.map((stop, idx) => {
        const seq = stop.sequence !== undefined ? stop.sequence : (idx + 1);
        return `<div class="bg-white border border-gray-100 rounded-xl p-3 hover:border-pink-300 hover:shadow-sm transition flex items-center justify-between group">
            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                <span class="w-6 h-6 rounded-lg bg-pink-50 text-pink-600 font-bold text-xs flex items-center justify-center flex-shrink-0">
                    ${seq}
                </span>
                <div class="min-w-0 flex-1">
                    <h5 class="text-xs font-bold text-gray-800 truncate">${stop.name}</h5>
                    <p class="text-[10px] text-gray-400 font-mono">Lat: ${stop.lat}, Lng: ${stop.lng}</p>
                </div>
            </div>
            <div class="flex items-center gap-1 opacity-90 group-hover:opacity-100 transition">
                <button type="button" onclick="focusStopOnIntegratedMap(${idx})" title="โฟกัสบนแผนที่"
                    class="p-1.5 rounded-lg text-xs bg-gray-50 text-gray-600 hover:bg-pink-50 hover:text-pink-600 transition">
                    <i class="fas fa-crosshairs"></i>
                </button>
                <button type="button" onclick="editIntegratedStop(${idx})" title="แก้ไข"
                    class="p-1.5 rounded-lg text-xs bg-gray-50 text-blue-600 hover:bg-blue-100 transition">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" onclick="deleteIntegratedStop(${idx})" title="ลบ"
                    class="p-1.5 rounded-lg text-xs bg-gray-50 text-red-600 hover:bg-red-100 transition">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </div>`;
    }).join('');
}

function saveIntegratedStop() {
    const nameInput = document.getElementById('intStopName');
    const latInput  = document.getElementById('intStopLat');
    const lngInput  = document.getElementById('intStopLng');
    const editIdx   = parseInt(document.getElementById('intStopEditIndex').value);

    const name = nameInput.value.trim();
    const lat  = parseFloat(latInput.value);
    const lng  = parseFloat(lngInput.value);

    if (!name) {
        alert("กรุณากรอกชื่อจุดจอดรถไฟฟ้า!");
        nameInput.focus();
        return;
    }
    if (isNaN(lat) || isNaN(lng)) {
        alert("กรุณาคลิกเลือกตำแหน่งบนแผนที่ฝั่งขวา เพื่อกำหนดพิกัด Lat/Lng!");
        return;
    }

    const stopObj = {
        sequence: editIdx === -1 ? (stops.length + 1) : (stops[editIdx].sequence || (editIdx + 1)),
        name: name,
        route: "สายบริการภายใน YRU",
        lat: lat,
        lng: lng
    };

    if (editIdx === -1) {
        stops.push(stopObj);
    } else {
        stops[editIdx] = stopObj;
    }

    stops.sort((a,b) => (a.sequence || 0) - (b.sequence || 0));
    setStorage("yru_stops_v2", stops);

    // Update UI & Map
    resetStopForm();
    clearTempPinkMarker();
    renderIntegratedStopsList();
    renderIntegratedRouteStopsChecklist();
    renderIntegratedMapFeatures();

    const sBadge = document.getElementById("intStopBadgeCount");
    if (sBadge) sBadge.textContent = stops.length;

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            position: 'center',
            icon: 'success',
            title: `บันทึกจุดจอด "${name}" สำเร็จ`,
            showConfirmButton: false,
            timer: 2200
        });
    }
}

function editIntegratedStop(idx) {
    const stop = stops[idx];
    if (!stop) return;

    document.getElementById('intStopEditIndex').value = idx;
    document.getElementById('intStopName').value = stop.name;
    document.getElementById('intStopLat').value = stop.lat;
    document.getElementById('intStopLng').value = stop.lng;

    document.getElementById('stopFormHeading').innerHTML = `<i class="fas fa-edit text-blue-600"></i> แก้ไขจุดจอด #${idx+1}`;
    document.getElementById('btnSaveStop').innerHTML = `<i class="fas fa-save"></i> บันทึก`;
    document.getElementById('btnResetStopForm').classList.remove('hidden');

    // Pan map & place temp marker
    if (intMap) {
        intMap.setView([stop.lat, stop.lng], 18);
        placeTempPinkMarker(stop.lat, stop.lng);
    }
}

function resetStopForm() {
    document.getElementById('intStopEditIndex').value = "-1";
    document.getElementById('intStopName').value = "";
    document.getElementById('intStopLat').value = "";
    document.getElementById('intStopLng').value = "";

    document.getElementById('stopFormHeading').innerHTML = `<i class="fas fa-plus-circle text-pink-600"></i> เพิ่มจุดจอดใหม่`;
    document.getElementById('btnSaveStop').innerHTML = `<i class="fas fa-save"></i> บันทึก`;
    document.getElementById('btnResetStopForm').classList.add('hidden');
    clearTempPinkMarker();
}

function deleteIntegratedStop(idx) {
    const stop = stops[idx];
    if (!stop) return;

    if (confirm(`คุณต้องการลบจุดจอด "${stop.name}" ออกจากระบบหรือไม่?`)) {
        stops.splice(idx, 1);
        setStorage("yru_stops_v2", stops);

        renderIntegratedStopsList();
        renderIntegratedRouteStopsChecklist();
        renderIntegratedMapFeatures();

        const sBadge = document.getElementById("intStopBadgeCount");
        if (sBadge) sBadge.textContent = stops.length;
    }
}

function focusStopOnIntegratedMap(idx) {
    const stop = stops[idx];
    if (stop && intMap) {
        intMap.setView([stop.lat, stop.lng], 18);
        if (intStopMarkersMap[stop.name]) {
            intStopMarkersMap[stop.name].openPopup();
        }
    }
}

// -------------------------------------------------------------------------
// TAB 2: ROUTE BUILDER LOGIC (CHECKLIST & REAL-TIME POLYLINE)
// -------------------------------------------------------------------------
function renderIntegratedRouteStopsChecklist() {
    const checklist = document.getElementById('intRouteStopsChecklist');
    if (!checklist) return;

    if (stops.length === 0) {
        checklist.innerHTML = `<p class="text-xs text-gray-400 text-center py-3">ยังไม่มีจุดจอดในระบบ กรุณาเพิ่มจุดจอดใน Tab 1 ก่อน</p>`;
        return;
    }

    checklist.innerHTML = stops.map((stop, idx) => {
        const isSelected = intSelectedRouteStops.some(s => s.name === stop.name);
        const selectedIdx = intSelectedRouteStops.findIndex(s => s.name === stop.name);

        return `<div class="flex items-center justify-between p-2 rounded-lg border ${isSelected ? 'bg-pink-50/70 border-pink-200' : 'bg-gray-50/50 border-gray-200'} transition">
            <label class="flex items-center gap-2 text-xs font-semibold text-gray-700 cursor-pointer min-w-0 flex-1">
                <input type="checkbox" onchange="toggleIntegratedRouteStop('${stop.name}')" ${isSelected ? 'checked' : ''}
                    class="rounded text-pink-600 focus:ring-pink-500 w-4 h-4 cursor-pointer">
                <span class="truncate">${stop.name}</span>
            </label>
            ${isSelected ? `
                <div class="flex items-center gap-1 ml-2">
                    <span class="w-5 h-5 rounded-full bg-pink-600 text-white font-bold text-[10px] flex items-center justify-center">
                        ${selectedIdx + 1}
                    </span>
                    <button type="button" onclick="moveIntegratedRouteStop(${selectedIdx}, -1)" ${selectedIdx === 0 ? 'disabled class="text-gray-300 cursor-not-allowed px-1"' : 'class="text-gray-600 hover:text-pink-600 px-1 font-bold"'}>
                        <i class="fas fa-arrow-up text-[10px]"></i>
                    </button>
                    <button type="button" onclick="moveIntegratedRouteStop(${selectedIdx}, 1)" ${selectedIdx === intSelectedRouteStops.length - 1 ? 'disabled class="text-gray-300 cursor-not-allowed px-1"' : 'class="text-gray-600 hover:text-pink-600 px-1 font-bold"'}>
                        <i class="fas fa-arrow-down text-[10px]"></i>
                    </button>
                </div>
            ` : ''}
        </div>`;
    }).join('');

    const bBadge = document.getElementById('intSelectedStopsBadge');
    if (bBadge) bBadge.textContent = `เลือก ${intSelectedRouteStops.length} จุด`;

    renderIntegratedMapFeatures();
}

function toggleIntegratedRouteStop(stopName) {
    const stopObj = stops.find(s => s.name === stopName);
    if (!stopObj) return;

    const existingIdx = intSelectedRouteStops.findIndex(s => s.name === stopName);
    if (existingIdx >= 0) {
        intSelectedRouteStops.splice(existingIdx, 1);
    } else {
        intSelectedRouteStops.push({ ...stopObj });
    }

    renderIntegratedRouteStopsChecklist();
}

function moveIntegratedRouteStop(index, direction) {
    const targetIdx = index + direction;
    if (targetIdx < 0 || targetIdx >= intSelectedRouteStops.length) return;

    const temp = intSelectedRouteStops[index];
    intSelectedRouteStops[index] = intSelectedRouteStops[targetIdx];
    intSelectedRouteStops[targetIdx] = temp;

    renderIntegratedRouteStopsChecklist();
}

function updateActiveRoutePolylineColor(colorHex) {
    renderIntegratedMapFeatures();
}

async function saveIntegratedRoute() {
    const codeInput = document.getElementById('intRouteCode');
    const colorInput = document.getElementById('intRouteColor');
    let editIdx     = parseInt(document.getElementById('intRouteEditIndex').value);

    const code  = codeInput.value.trim();
    const color = colorInput.value || "#E91E63";

    if (!code) {
        alert("กรุณากรอกรหัส/ชื่อเส้นทางเดินรถ!");
        codeInput.focus();
        return;
    }
    if (intSelectedRouteStops.length < 2) {
        alert("กรุณาเลือกจุดจอดอย่างน้อย 2 จุดขึ้นไปเพื่อสร้างเส้นทาง!");
        return;
    }

    if (editIdx === -1) {
        const foundIdx = systemRoutes.findIndex(r => r.route_code.toUpperCase() === code.toUpperCase());
        if (foundIdx !== -1) {
            editIdx = foundIdx;
        }
    }

    // Auto-generate polyline coordinates from selected stops if custom waypoints not drawn
    let finalPolyline = (intRouteWaypoints && intRouteWaypoints.length >= 2) ? intRouteWaypoints : [];
    if (finalPolyline.length < 2 && intSelectedRouteStops && intSelectedRouteStops.length >= 2) {
        finalPolyline = intSelectedRouteStops.map(s => {
            const foundStop = stops.find(st => (st.name || st.parking_spot_code) === s.name) || s;
            const lat = parseFloat(s.lat || foundStop.lat);
            const lng = parseFloat(s.lng || foundStop.lng);
            return (!isNaN(lat) && !isNaN(lng)) ? [lat, lng] : null;
        }).filter(pt => pt !== null);
    }

    const routePayload = {
        route_code: code,
        route_name: code,
        route_color: color,
        route_details: `เส้นทางเดินรถ ${code} (${intSelectedRouteStops.length} จุดจอด)`,
        polyline_data: finalPolyline,
        stops: intSelectedRouteStops.map((s, idx) => ({
            parking_spot_code: s.name,
            stop_order: idx + 1
        }))
    };

    try {
        let response;
        if (editIdx === -1) {
            response = await fetch('/api/routes', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify(routePayload)
            });
        } else {
            const originalCode = systemRoutes[editIdx] ? systemRoutes[editIdx].route_code : code;
            response = await fetch('/api/routes/' + encodeURIComponent(originalCode), {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify(routePayload)
            });
        }

        if (response.ok) {
            await fetchSystemRoutes();
            broadcastRouteUpdate(editIdx === -1 ? 'add' : 'edit', code);

            resetRouteForm();
            renderIntegratedRoutesList();
            renderIntegratedMapFeatures();

            const rBadge = document.getElementById("intRouteBadgeCount");
            if (rBadge) rBadge.textContent = systemRoutes.length;

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    position: 'center',
                    icon: 'success',
                    title: `บันทึกเส้นทาง "${code}" สำเร็จ`,
                    showConfirmButton: false,
                    timer: 2200
                });
            }
        } else {
            const err = await response.json();
            alert("Error: " + (err.message || "Failed to save route."));
        }
    } catch (e) {
        console.error("API error saving integrated route:", e);
        const routeObj = {
            route_code: code,
            route_name: code,
            color: color,
            route_details: `เส้นทางเดินรถ ${code} (${intSelectedRouteStops.length} จุดจอด)`,
            polyline_data: intRouteWaypoints && intRouteWaypoints.length > 0 ? intRouteWaypoints : [],
            route_stops: intSelectedRouteStops.map((s, idx) => ({
                parking_spot_code: s.name,
                stop_order: idx + 1,
                lat: s.lat,
                lng: s.lng
            }))
        };

        if (editIdx === -1) {
            systemRoutes.push(routeObj);
        } else {
            systemRoutes[editIdx] = routeObj;
        }

        setStorage("yru_routes_v1", systemRoutes);
        broadcastRouteUpdate(editIdx === -1 ? 'add' : 'edit', code);

        resetRouteForm();
        renderIntegratedRoutesList();
        renderIntegratedMapFeatures();
    }
}

function renderIntegratedRoutesList() {
    const container = document.getElementById('intRoutesListContainer');
    if (!container) return;

    if (systemRoutes.length === 0) {
        container.innerHTML = `<div class="bg-gray-50 border border-dashed border-gray-200 rounded-xl p-4 text-center text-xs text-gray-400">
            ยังไม่มีเส้นทางในระบบ กรุณาเลือกจุดจอดแล้วกดบันทึกเส้นทาง
        </div>`;
        return;
    }

    container.innerHTML = systemRoutes.map((rt, idx) => {
        const numStops = rt.route_stops ? rt.route_stops.length : (rt.stops ? rt.stops.length : 0);
        const color = rt.color || "#E91E63";

        return `<div class="bg-white border border-gray-100 rounded-xl p-3 hover:border-blue-300 hover:shadow-sm transition flex items-center justify-between group">
            <div class="flex items-center gap-2.5 min-w-0 flex-1">
                <span class="w-1 h-8 rounded-full flex-shrink-0" style="background-color: ${color}; box-shadow: 0 2px 6px ${color}55;"></span>
                <div class="min-w-0 flex-1">
                    <h5 class="text-xs font-bold text-gray-800 truncate">${rt.route_code}</h5>
                    <p class="text-[10px] text-gray-400">${numStops} จุดจอดในเส้นทาง</p>
                </div>
            </div>
            <div class="flex items-center gap-1 opacity-90 group-hover:opacity-100 transition">
                <button type="button" onclick="editIntegratedRoute(${idx})" title="แก้ไข"
                    class="p-1.5 rounded-lg text-xs bg-gray-50 text-blue-600 hover:bg-blue-100 transition">
                    <i class="fas fa-edit"></i>
                </button>
                <button type="button" onclick="deleteIntegratedRoute(${idx})" title="ลบ"
                    class="p-1.5 rounded-lg text-xs bg-gray-50 text-red-600 hover:bg-red-100 transition">
                    <i class="fas fa-trash-alt"></i>
                </button>
            </div>
        </div>`;
    }).join('');
}

function editIntegratedRoute(idx) {
    const rt = systemRoutes[idx];
    if (!rt) return;

    document.getElementById('intRouteEditIndex').value = idx;
    document.getElementById('intRouteCode').value = rt.route_code;
    document.getElementById('intRouteColor').value = rt.color || "#E91E63";

    // Load custom drawn polyline waypoints if present
    if (rt.polyline_data && Array.isArray(rt.polyline_data)) {
        intRouteWaypoints = rt.polyline_data.map(p => Array.isArray(p) ? p : [p.lat, p.lng]);
    } else {
        intRouteWaypoints = [];
    }

    // Load selected stops
    if (rt.route_stops && rt.route_stops.length > 0) {
        intSelectedRouteStops = rt.route_stops.map(rs => {
            const foundStop = stops.find(s => s.name === rs.parking_spot_code) || {};
            return {
                name: rs.parking_spot_code,
                lat: rs.lat || foundStop.lat,
                lng: rs.lng || foundStop.lng
            };
        });
    } else {
        intSelectedRouteStops = [];
    }

    switchIntegratedTab('routes');
    renderIntegratedRouteStopsChecklist();

    document.getElementById('routeFormHeading').innerHTML = `<i class="fas fa-edit text-pink-600"></i> แก้ไขเส้นทาง ${rt.route_code}`;
    document.getElementById('btnSaveRoute').innerHTML = `<i class="fas fa-save"></i> บันทึก`;
    document.getElementById('btnResetRouteForm').classList.remove('hidden');
}

function resetRouteForm() {
    document.getElementById('intRouteEditIndex').value = "-1";
    document.getElementById('intRouteCode').value = "";
    document.getElementById('intRouteColor').value = "#E91E63";
    intSelectedRouteStops = [];
    intRouteWaypoints = [];

    renderIntegratedRouteStopsChecklist();
    document.getElementById('routeFormHeading').innerHTML = `<i class="fas fa-route text-pink-600"></i> สร้างเส้นทางใหม่`;
    document.getElementById('btnSaveRoute').innerHTML = `<i class="fas fa-save"></i> บันทึก`;
    document.getElementById('btnResetRouteForm').classList.add('hidden');
}

async function deleteIntegratedRoute(idx) {
    const rt = systemRoutes[idx];
    if (!rt) return;

    if (typeof Swal !== 'undefined') {
        const result = await Swal.fire({
            title: 'ยืนยันการลบเส้นทาง',
            text: `คุณต้องการลบเส้นทาง "${rt.route_code}" หรือไม่?`,
            iconHtml: '<i class="fas fa-trash-alt text-red-500"></i>',
            customClass: { icon: 'border-none' },
            showCancelButton: true,
            confirmButtonColor: '#ef4444',
            cancelButtonColor: '#9ca3af',
            confirmButtonText: 'ยืนยันการลบ',
            cancelButtonText: 'ยกเลิก',
            reverseButtons: true
        });
        if (!result.isConfirmed) return;
    } else {
        if (!confirm(`คุณต้องการลบเส้นทาง "${rt.route_code}" หรือไม่?`)) return;
    }

    const routeCodeToDelete = rt.route_code;

    try {
        const response = await fetch('/api/routes/' + encodeURIComponent(routeCodeToDelete), {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        });

        if (response.ok) {
            systemRoutes.splice(idx, 1);
            setStorage("yru_routes_v1", systemRoutes);
            broadcastRouteUpdate('delete', routeCodeToDelete);

            resetRouteForm();
            renderIntegratedRoutesList();
            renderIntegratedMapFeatures();

            const rBadge = document.getElementById("intRouteBadgeCount");
            if (rBadge) rBadge.textContent = systemRoutes.length;

            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    position: 'center',
                    icon: 'success',
                    title: `ลบเส้นทาง "${routeCodeToDelete}" ออกจากระบบเรียบร้อย`,
                    showConfirmButton: false,
                    timer: 2200
                });
            }
        } else {
            alert("ไม่สามารถลบเส้นทางออกจากฐานข้อมูลได้");
        }
    } catch (e) {
        console.error("API error deleting route:", e);
        systemRoutes.splice(idx, 1);
        setStorage("yru_routes_v1", systemRoutes);
        broadcastRouteUpdate('delete', routeCodeToDelete);
        resetRouteForm();
        renderIntegratedRoutesList();
        renderIntegratedMapFeatures();
    }
}

// -------------------------------------------------------------------------
// RENDER ALL MAP FEATURES (MARKERS & POLYLINES)
// -------------------------------------------------------------------------
function renderIntegratedMapFeatures() {
    if (!intMap) return;

    // Clear existing stop markers
    Object.values(intStopMarkersMap).forEach(m => intMap.removeLayer(m));
    intStopMarkersMap = {};

    // Clear existing route markers & polyline
    intRouteMarkers.forEach(m => intMap.removeLayer(m));
    intRouteMarkers = [];
    if (intRoutePolyline) {
        intMap.removeLayer(intRoutePolyline);
        intRoutePolyline = null;
    }

    // RENDER ALL STOPS AS MARKERS
    stops.forEach((stop, idx) => {
        if (!stop.lat || !stop.lng) return;

        const seqNum = stop.sequence !== undefined ? stop.sequence : (idx + 1);
        const isSelectedInRoute = intSelectedRouteStops.some(s => s.name === stop.name);
        const selIdx = intSelectedRouteStops.findIndex(s => s.name === stop.name);

        let iconBgColor = '#F05A24'; // Orange color matching sample mockup
        let displayNum = seqNum;

        if (intCurrentTab === 'routes' && isSelectedInRoute) {
            iconBgColor = '#E91E63'; // Pink for selected route stops
            displayNum = selIdx + 1;
        }

        const iconHtml = `<div style="background-color:${iconBgColor}; color:white; width:28px; height:28px; border-radius:50%; display:flex; align-items:center; justify-content:center; font-weight:800; font-size:13px; border:2.5px solid white; box-shadow:0 3px 8px rgba(0,0,0,0.35); cursor:pointer;">${displayNum}</div>`;

        const customIcon = L.divIcon({
            className: 'custom-int-stop-marker',
            html: iconHtml,
            iconSize: [28, 28],
            iconAnchor: [14, 14]
        });

        const marker = L.marker([stop.lat, stop.lng], { icon: customIcon }).addTo(intMap);
        
        marker.on('click', function(e) {
            if (intCurrentTab === 'routes') {
                L.DomEvent.stopPropagation(e);
                toggleIntegratedRouteStop(stop.name);
            }
        });

        marker.bindPopup(`
            <div style="font-family:Kanit, sans-serif; font-size:12px;">
                <b style="color:#E91E63;">${stop.name}</b><br>
                <span style="color:#6B7280; font-size:10px;">พิกัด: ${stop.lat}, ${stop.lng}</span>
            </div>
        `);

        intStopMarkersMap[stop.name] = marker;
    });

    // Clear existing waypoint markers
    intWaypointMarkers.forEach(m => intMap.removeLayer(m));
    intWaypointMarkers = [];

    // Array to keep track of system route polylines
    if (!window.intSystemPolylines) window.intSystemPolylines = [];
    window.intSystemPolylines.forEach(p => intMap.removeLayer(p));
    window.intSystemPolylines = [];

    // 1. SYSTEM ROUTES POLYLINES REMOVED PER USER REQUEST (Show only stop markers on map, no background route lines)

    // 2. RENDER ACTIVE EDITING ROUTE POLYLINE (Only when admin clicks/draws waypoints on map)
    if (intCurrentTab === 'routes') {
        let polylineCoords = [];

        if (intRouteWaypoints && intRouteWaypoints.length >= 2) {
            polylineCoords = intRouteWaypoints;
        }

        const activeColor = document.getElementById('intRouteColor')?.value || "#E91E63";

        if (polylineCoords.length >= 2) {
            intRoutePolyline = L.polyline(polylineCoords, {
                color: activeColor,
                weight: 7,
                opacity: 0.95,
                lineJoin: 'round'
            }).addTo(intMap);
        }

        // Draw small waypoint dots for custom drawn road points
        if (intRouteWaypoints && intRouteWaypoints.length > 0) {
            intRouteWaypoints.forEach((pt) => {
                const wpIcon = L.divIcon({
                    className: 'custom-wp-dot',
                    html: `<div style="background-color:${activeColor}; width:10px; height:10px; border-radius:50%; border:2px solid white; box-shadow:0 1px 4px rgba(0,0,0,0.4);"></div>`,
                    iconSize: [10, 10],
                    iconAnchor: [5, 5]
                });
                const wpMarker = L.marker([pt[0], pt[1]], { icon: wpIcon }).addTo(intMap);
                intWaypointMarkers.push(wpMarker);
            });
        }
    }
}

function fitIntegratedMapBounds() {
    if (!intMap || !stops || stops.length === 0) return;
    const validStops = stops.filter(s => s.lat && s.lng);
    if (validStops.length === 0) return;
    const bounds = L.latLngBounds(validStops.map(s => [s.lat, s.lng]));
    intMap.fitBounds(bounds, { padding: [50, 50], maxZoom: 17.5 });
}


function setMapMode(mode) {
    currentMapMode = 'draw';
}

// Fetch all routes from DB / API
async function fetchSystemRoutes() {
    try {
        const response = await fetch('/api/routes?v=' + new Date().getTime());
        if (response.ok) {
            systemRoutes = await response.json();
            // Map the Laravel DB route structure to local properties if needed
            systemRoutes.forEach(r => {
                if (!r.polyline_data) r.polyline_data = [];
            });
            // Update local fallback
            setStorage("yru_routes_v1", systemRoutes);
        } else {
            systemRoutes = getStorage("yru_routes_v1", []);
        }
    } catch (e) {
        console.error("API error fetching routes. Using localStorage:", e);
        systemRoutes = getStorage("yru_routes_v1", []);
    }
}

// Render Route list table in Route Management page
async function renderRouteManagementPage() {
    await fetchSystemRoutes();
    const tbody = document.getElementById("routeManagementTable");
    tbody.innerHTML = "";

    if (systemRoutes.length === 0) {
        tbody.innerHTML = `<tr><td colspan="4" class="p-8 text-center text-gray-400">❌ ยังไม่มีเส้นทางในระบบ กรุณาสร้างเส้นทางเดินรถใหม่</td></tr>`;
        return;
    }

    systemRoutes.forEach((route, index) => {
        const numStops = route.route_stops ? route.route_stops.length : (route.stops ? route.stops.length : 0);
        tbody.innerHTML += `
            <tr class="border-t hover:bg-gray-50 transition">
                <td class="p-4 font-mono font-bold text-gray-800">${route.route_code}</td>
                <td class="p-4 text-xs text-gray-500">${route.route_details || '-'}</td>
                <td class="p-4 text-center font-bold text-pink-600">${numStops} จุดจอด</td>
                <td class="p-4 text-center space-x-1 whitespace-nowrap">
                    <button onclick="editRoute(${index})" class="bg-blue-500 text-white px-3 py-1 rounded hover:bg-blue-600 transition text-xs font-medium">แก้ไข</button>
                    <button onclick="deleteRoute('${route.route_code}')" class="bg-red-500 text-white px-3 py-1 rounded hover:bg-red-600 transition text-xs font-medium">ลบ</button>
                </td>
            </tr>`;
    });

    populateRouteDropdownInTramModal();
}

// Populate Route Dropdown in Tram Modal
function populateRouteDropdownInTramModal() {
    const select = document.getElementById("modalTramRoute");
    if (!select) return;
    select.innerHTML = `<option value="">-- ยังไม่มอบหมายเส้นทาง --</option>`;
    systemRoutes.forEach(r => {
        select.innerHTML += `<option value="${r.route_code}">${r.route_code} - ${r.route_name}</option>`;
    });
}

// Initialize Leaflet Map for Route Builder
function initRouteBuilderMap() {
    if (builderMap) {
        builderMap.remove();
        builderMap = null;
    }

    // Yala Rajabhat University center coordinates (matching user side)
    const defaultCenter = [6.548850, 101.289800];

    // Create Map Layers matching user side
    const osmLayerAdmin = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 19,
        attribution: '&copy; OpenStreetMap'
    });

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

    builderMap = L.map('routeMap', {
        center: defaultCenter,
        zoom: 17,
        zoomControl: false,
        minZoom: 16,
        maxZoom: 20,
        maxBounds: [
            [6.535, 101.275],  // SW bound
            [6.563, 101.305]   // NE bound
        ],
        maxBoundsViscosity: 0.8,
        layers: [googleRoadmap] // Set Google Roadmap as default layer to match user side
    });

    L.control.zoom({
        position: 'topright'
    }).addTo(builderMap);

    const baseMaps = {
        "แผนที่ปกติ (Google Roadmap)": googleRoadmap,
        "แผนที่ดาวเทียม (Satellite Hybrid)": googleHybrid
    };

    L.control.layers(baseMaps, null, { position: 'topright' }).addTo(builderMap);

    // Initialise empty polyline layer
    const polylineColor = document.getElementById("modalRouteColor")?.value || '#ec4899';
    drawnPolyline = L.polyline([], {
        color: polylineColor,
        weight: 6,
        opacity: 0.8,
        smoothFactor: 1
    }).addTo(builderMap);

    // Map click event to draw polyline point
    builderMap.on('click', function(e) {
        if (currentMapMode !== 'draw') return; // Only draw when in Draw Mode
        const lat = e.latlng.lat;
        const lng = e.latlng.lng;
        addPolylinePoint(lat, lng);
    });

    // Populate existing stops as markers
    renderStopsOnBuilderMap();
}

// Add point to drawn polyline
function addPolylinePoint(lat, lng) {
    drawnPoints.push([lat, lng]);
    drawnPolyline.setLatLngs(drawnPoints);

    // Add marker for point vertex to enable dragging
    const marker = L.marker([lat, lng], {
        draggable: true,
        icon: L.divIcon({
            className: 'bg-white border-2 border-pink-500 rounded-full w-3 h-3',
            iconSize: [12, 12]
        })
    }).addTo(builderMap);

    const pointIndex = drawnPoints.length - 1;
    marker.on('drag', function(e) {
        drawnPoints[pointIndex] = [e.target.getLatLng().lat, e.target.getLatLng().lng];
        drawnPolyline.setLatLngs(drawnPoints);
    });

    routeMapMarkers.push(marker);
}

// Undo last drawn point
function undoLastPolylinePoint() {
    if (drawnPoints.length > 0) {
        drawnPoints.pop();
        if (drawnPolyline) drawnPolyline.setLatLngs(drawnPoints);
        const marker = routeMapMarkers.pop();
        if (marker && builderMap) builderMap.removeLayer(marker);
    }
}

// Clear all polyline points
function clearPolyline() {
    drawnPoints = [];
    if (drawnPolyline) {
        drawnPolyline.setLatLngs([]);
    }
    if (routeMapMarkers && builderMap) {
        routeMapMarkers.forEach(m => builderMap.removeLayer(m));
    }
    routeMapMarkers = [];
}

function updateDrawnPolylineColor(color) {
    if (drawnPolyline) {
        drawnPolyline.setStyle({ color: color });
    }
}

// Render available stops on map
function renderStopsOnBuilderMap() {
    // Clear old stops markers safely
    if (availableStopMarkers && builderMap) {
        availableStopMarkers.forEach(m => {
            try { builderMap.removeLayer(m); } catch(e) {}
        });
    }
    availableStopMarkers = [];
    stopMarkerMap = {};

    // Load latest stops directly from localStorage with self-healing
    stops = getStorage("yru_stops_v2", defaultStops);
    console.log("[renderStopsOnBuilderMap] Loaded", stops.length, "stops from localStorage");

    stops.forEach((stop, idx) => {
        const lat = parseFloat(stop.lat);
        const lng = parseFloat(stop.lng);
        if (isNaN(lat) || isNaN(lng)) {
            console.warn(`Stop at index ${idx} has invalid coordinates:`, stop);
            return;
        }

        const code = stop.parking_spot_code || stop.name;
        const seq = stop.sequence ?? stop.stop_order ?? (idx + 1);

        // Use inline styles (NOT Tailwind classes) to guarantee rendering
        const stopIcon = L.divIcon({
            html: `<div style="
                background-color: #f97316;
                color: white;
                border-radius: 50%;
                width: 32px;
                height: 32px;
                display: flex;
                align-items: center;
                justify-content: center;
                font-weight: bold;
                font-size: 14px;
                line-height: 32px;
                text-align: center;
                box-shadow: 0 2px 6px rgba(0,0,0,0.3);
                border: 2px solid white;
                font-family: 'Kanit', sans-serif;
                position: relative;
                z-index: 9999;
                overflow: visible;
            ">${seq}</div>`,
            className: '',
            iconSize: [32, 32],
            iconAnchor: [16, 16]
        });

        const marker = L.marker([lat, lng], { icon: stopIcon, zIndexOffset: 2000 })
            .addTo(builderMap)
            .bindPopup(`<b>${stop.name}</b><br>จุดจอดที่: ${seq}`);

        stopMarkerMap[code] = marker;
        availableStopMarkers.push(marker);
        console.log(`[renderStopsOnBuilderMap] Added marker ${seq} at [${lat}, ${lng}]`);
    });
}

// Render stops checklist in Route Builder Modal
function renderRouteStopsChecklist() {
    const checklistDiv = document.getElementById("routeStopsChecklist");
    if (!checklistDiv) return;
    checklistDiv.innerHTML = "";
    
    // Force reload stops directly from localStorage with self-healing
    stops = getStorage("yru_stops_v2", defaultStops);
    console.log("[renderRouteStopsChecklist] Loaded", stops.length, "stops from localStorage");

    // Update the count badge
    const badge = document.getElementById("stopsCountBadge");
    if (badge) badge.textContent = stops.length + " จุด";

    if (stops.length === 0) {
        checklistDiv.innerHTML = `<p class="text-xs text-gray-400">ยังไม่มีข้อมูลจุดจอดในระบบ</p>`;
        return;
    }

    stops.forEach(stop => {
        const code = stop.parking_spot_code || stop.name;
        const linkedStop = selectedRouteStops.find(s => s.parking_spot_code === code);
        const isChecked = linkedStop ? 'checked' : '';
        const orderVal = linkedStop ? linkedStop.stop_order : '';

        checklistDiv.innerHTML += `
            <div class="flex items-center justify-between p-2 hover:bg-white rounded border border-gray-150 text-xs">
                <label class="flex items-center gap-2 cursor-pointer font-medium text-gray-700">
                    <input type="checkbox" id="chk_stop_${code}" value="${code}" ${isChecked} onchange="toggleStopInRoute('${code}')" class="rounded text-pink-600 focus:ring-pink-500 cursor-pointer">
                    <span>${stop.name}</span>
                </label>
                <div class="flex items-center gap-1">
                    <span class="text-[10px] text-gray-400">ลำดับ:</span>
                    <input type="number" id="order_stop_${code}" value="${orderVal}" min="1" onchange="updateStopOrder('${code}', this.value)" class="w-12 border border-gray-300 p-1 rounded text-center outline-none text-xs" ${linkedStop ? '' : 'disabled'}>
                </div>
            </div>`;
    });
}

// Refresh stops in the Route Builder modal (called by the 🔄 button)
function refreshBuilderStops() {
    stops = getStorage("yru_stops_v2", defaultStops);
    if (builderMap) {
        renderStopsOnBuilderMap();
    }
    renderRouteStopsChecklist();
}

// Toggle stop inclusion in route
function toggleStopInRoute(code) {
    const index = selectedRouteStops.findIndex(s => s.parking_spot_code === code);

    if (index > -1) {
        // Remove
        selectedRouteStops.splice(index, 1);
    } else {
        // Add
        const nextOrder = selectedRouteStops.length + 1;
        selectedRouteStops.push({
            parking_spot_code: code,
            stop_order: nextOrder
        });
    }

    renderRouteStopsChecklist();
    updateMarkerHighlighting();
}

// Dynamic Marker Highlighter update
function updateMarkerHighlighting() {
    // Keep empty as markers are read-only references on map now
}

// Update stop order sequence value
function updateStopOrder(code, val) {
    const stop = selectedRouteStops.find(s => s.parking_spot_code === code);
    if (stop) {
        stop.stop_order = parseInt(val) || 1;
        updateMarkerHighlighting();
    }
}

// Open Route Modal
function openRouteModal() {
    stops = getStorage("yru_stops_v2", defaultStops);
    document.getElementById("routeModalTitle").innerText = "เครื่องมือสร้างเส้นทางเดินรถ (Route Builder)";
    document.getElementById("editRouteIndex").value = "-1";
    document.getElementById("modalRouteCode").value = "";
    document.getElementById("modalRouteCode").disabled = false;
    document.getElementById("modalRouteName").value = "";
    document.getElementById("modalRouteColor").value = "#ec4899";
    document.getElementById("modalRouteDetails").value = "";
    
    setMapMode('draw');
    clearPolyline();
    selectedRouteStops = [];
    
    document.getElementById("routeModal").classList.remove("hidden");
    
    setTimeout(() => {
        initRouteBuilderMap();
        renderRouteStopsChecklist();
        if (builderMap) {
            setTimeout(() => {
                builderMap.invalidateSize();
            }, 100);
        }
    }, 100);
}

// Edit Route
function editRoute(index) {
    stops = getStorage("yru_stops_v2", defaultStops);
    const route = systemRoutes[index];
    document.getElementById("routeModalTitle").innerText = "แก้ไขเส้นทางเดินรถ: " + route.route_name;
    document.getElementById("editRouteIndex").value = index;
    document.getElementById("modalRouteCode").value = route.route_code;
    document.getElementById("modalRouteCode").disabled = false;
    document.getElementById("modalRouteName").value = route.route_name;
    document.getElementById("modalRouteColor").value = route.route_color || "#ec4899";
    document.getElementById("modalRouteDetails").value = route.route_details || "";

    setMapMode('draw');
    clearPolyline();
    selectedRouteStops = [];

    // Parse coordinates
    let coords = [];
    if (route.polyline_data) {
        coords = Array.isArray(route.polyline_data) 
            ? route.polyline_data 
            : JSON.parse(route.polyline_data || "[]");
    }

    // Populate route stops
    const stopsList = route.route_stops || route.stops || [];
    stopsList.forEach(s => {
        selectedRouteStops.push({
            parking_spot_code: s.parking_spot_code || s.name,
            stop_order: s.stop_order || s.sequence
        });
    });

    document.getElementById("routeModal").classList.remove("hidden");

    setTimeout(() => {
        initRouteBuilderMap();
        coords.forEach(pt => {
            if (Array.isArray(pt) && pt.length === 2) {
                addPolylinePoint(pt[0], pt[1]);
            } else if (pt && typeof pt === 'object') {
                const lat = pt.lat !== undefined ? pt.lat : pt[0];
                const lng = pt.lng !== undefined ? pt.lng : pt[1];
                if (lat !== undefined && lng !== undefined) {
                    addPolylinePoint(lat, lng);
                }
            }
        });
        renderRouteStopsChecklist();
        if (builderMap) {
            setTimeout(() => {
                builderMap.invalidateSize();
            }, 100);
        }
    }, 100);
}

// Close Route Modal
function closeRouteModal() {
    document.getElementById("routeModal").classList.add("hidden");
}

// Save Route details and Polyline coordinates
async function saveRouteData() {
    const code = document.getElementById("modalRouteCode").value.trim().toUpperCase();
    let name = document.getElementById("modalRouteName").value.trim();
    const color = document.getElementById("modalRouteColor").value;
    const details = document.getElementById("modalRouteDetails").value.trim();
    const editIndex = parseInt(document.getElementById("editRouteIndex").value);

    if (!code) { alert("กรุณากรอกรหัสเส้นทาง!"); return; }
    if (!name || name === "-") {
        name = "เส้นทางเดินรถ " + code;
        document.getElementById("modalRouteName").value = name;
    }
    if (drawnPoints.length < 2) { alert("กรุณาคลิกวาดแนวถนนบนแผนที่อย่างน้อย 2 จุดขึ้นไป!"); return; }

    if (editIndex === -1) {
        const exists = systemRoutes.some(r => r.route_code.toUpperCase() === code);
        if (exists) {
            Swal.fire({
                title: "รหัสเส้นทางซ้ำ",
                text: `รหัสเส้นทาง "${code}" มีอยู่ในระบบแล้ว หากต้องการปรับปรุงข้อมูลเส้นทางนี้ โปรดกดปิดหน้าต่างนี้แล้วคลิกปุ่ม "แก้ไขแนวถนน" ที่รายการหน้าจอแทน`,
                icon: "warning",
                confirmButtonColor: "#ec4899"
            });
            return;
        }
    } else {
        const existsOther = systemRoutes.some((r, i) => i !== editIndex && r.route_code.toUpperCase() === code);
        if (existsOther) {
            Swal.fire({
                title: "รหัสเส้นทางซ้ำ",
                text: `รหัสเส้นทาง "${code}" ถูกใช้งานโดยเส้นทางอื่นในระบบแล้ว`,
                icon: "warning",
                confirmButtonColor: "#ec4899"
            });
            return;
        }
    }

    const routePayload = {
        route_code: code,
        route_name: name,
        route_color: color,
        route_details: details,
        polyline_data: drawnPoints,
        stops: selectedRouteStops
    };

    try {
        let response;
        if (editIndex === -1) {
            response = await fetch('/api/routes', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify(routePayload)
            });
        } else {
            const originalCode = systemRoutes[editIndex] ? systemRoutes[editIndex].route_code : code;
            response = await fetch('/api/routes/' + originalCode, {
                method: 'PUT',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
                },
                body: JSON.stringify(routePayload)
            });
        }

        if (response.ok) {
            closeRouteModal();
            await renderRouteManagementPage();
            if (typeof renderIntegratedMap === 'function') renderIntegratedMap();
            if (typeof fitIntegratedMapBounds === 'function') fitIntegratedMapBounds();
            broadcastRouteUpdate(editIndex === -1 ? 'add' : 'edit', code);

            Swal.fire({
                title: editIndex === -1 ? "เพิ่มเส้นทางสำเร็จ!" : "บันทึกการแก้ไขเส้นทางสำเร็จ!",
                text: "ระบบได้ทำการอัปเดตเส้นทางเดินรถบนแผนที่เรียบร้อยแล้ว คุณต้องการไปที่หน้าแผนที่ผู้ใช้งานหรือไม่?",
                icon: "success",
                showCancelButton: true,
                confirmButtonColor: "#ec4899",
                cancelButtonColor: "#6b7280",
                confirmButtonText: "🗺️ ไปยังหน้าแผนที่ผู้ใช้งาน (User Map)",
                cancelButtonText: "อยู่ในหน้าจัดการต่อ"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.open('/home', '_blank');
                }
            });
        } else {
            const err = await response.json();
            alert("Error: " + (err.message || "Failed to save route."));
        }
    } catch (e) {
        console.error("API error saving route. Fallback to localStorage:", e);
        // Fallback local storage logic
        if (editIndex === -1) {
            if (systemRoutes.some(r => r.route_code === code)) { alert("รหัสเส้นทางซ้ำ!"); return; }
            systemRoutes.push({
                route_code: code,
                route_name: name,
                route_color: color,
                route_details: details,
                polyline_data: drawnPoints,
                route_stops: selectedRouteStops.map(s => ({
                    parking_spot_code: s.parking_spot_code,
                    stop_order: s.stop_order,
                    station: stops.find(st => (st.parking_spot_code || st.name) === s.parking_spot_code)
                }))
            });
        } else {
            systemRoutes[editIndex] = {
                route_code: code,
                route_name: name,
                route_color: color,
                route_details: details,
                polyline_data: drawnPoints,
                route_stops: selectedRouteStops.map(s => ({
                    parking_spot_code: s.parking_spot_code,
                    stop_order: s.stop_order,
                    station: stops.find(st => (st.parking_spot_code || st.name) === s.parking_spot_code)
                }))
            };
        }
        setStorage("yru_routes_v1", systemRoutes);
        broadcastRouteUpdate(editIndex === -1 ? 'add' : 'edit', code);
        closeRouteModal();
        renderRouteManagementPage();
        if (typeof renderIntegratedMap === 'function') renderIntegratedMap();
        if (typeof fitIntegratedMapBounds === 'function') fitIntegratedMapBounds();

        Swal.fire({
            title: "บันทึกสำเร็จ (Offline)",
            text: "บันทึกข้อมูลเส้นทางเรียบร้อยแล้ว คุณต้องการไปที่หน้าแผนที่ผู้ใช้งานหรือไม่?",
            icon: "success",
            showCancelButton: true,
            confirmButtonColor: "#ec4899",
            cancelButtonColor: "#6b7280",
            confirmButtonText: "🗺️ ไปยังหน้าแผนที่ผู้ใช้งาน (User Map)",
            cancelButtonText: "อยู่ในหน้าจัดการต่อ"
        }).then((result) => {
            if (result.isConfirmed) {
                window.open('/home', '_blank');
            }
        });
    }
}

function broadcastRouteUpdate(action, routeCode) {
    try {
        const routeChannel = new BroadcastChannel('yru_routes_realtime_sync');
        routeChannel.postMessage({
            type: 'route_updated',
            action: action,
            code: routeCode,
            routes: systemRoutes,
            timestamp: Date.now()
        });
    } catch(e) {}
    try {
        localStorage.setItem('yru_routes_last_updated', Date.now().toString());
    } catch(e) {}
}

// Delete Route
async function deleteRoute(code) {
    if (!confirm("คุณมั่นใจที่จะลบเส้นทางเดินรถ " + code + " ใช่หรือไม่?")) return;

    try {
        const response = await fetch('/api/routes/' + code, {
            method: 'DELETE',
            headers: {
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content')
            }
        });

        if (response.ok) {
            systemRoutes = systemRoutes.filter(r => r.route_code !== code);
            setStorage("yru_routes_v1", systemRoutes);
            broadcastRouteUpdate('delete', code);
            renderRouteManagementPage();
            if (typeof renderIntegratedMap === 'function') renderIntegratedMap();
            if (typeof fitIntegratedMapBounds === 'function') fitIntegratedMapBounds();

            Swal.fire({
                title: "ลบเส้นทางสำเร็จ!",
                text: "ทำการลบเส้นทางออกจากระบบเรียบร้อยแล้ว คุณต้องการไปที่หน้าแผนที่ผู้ใช้งานหรือไม่?",
                icon: "success",
                showCancelButton: true,
                confirmButtonColor: "#ec4899",
                cancelButtonColor: "#6b7280",
                confirmButtonText: "🗺️ ไปยังหน้าแผนที่ผู้ใช้งาน (User Map)",
                cancelButtonText: "อยู่ในหน้าจัดการต่อ"
            }).then((result) => {
                if (result.isConfirmed) {
                    window.open('/home', '_blank');
                }
            });
        } else {
            alert("Failed to delete route.");
        }
    } catch (e) {
        console.error(e);
        systemRoutes = systemRoutes.filter(r => r.route_code !== code);
        setStorage("yru_routes_v1", systemRoutes);
        broadcastRouteUpdate('delete', code);
        renderRouteManagementPage();
        if (typeof renderIntegratedMap === 'function') renderIntegratedMap();
        if (typeof fitIntegratedMapBounds === 'function') fitIntegratedMapBounds();

        Swal.fire({
            title: "ลบเส้นทางสำเร็จ (Offline)",
            text: "ทำการลบเส้นทางออกจากหน่วยความจำเรียบร้อยแล้ว คุณต้องการไปที่หน้าแผนที่ผู้ใช้งานหรือไม่?",
            icon: "success",
            showCancelButton: true,
            confirmButtonColor: "#ec4899",
            cancelButtonColor: "#6b7280",
            confirmButtonText: "🗺️ ไปยังหน้าแผนที่ผู้ใช้งาน (User Map)",
            cancelButtonText: "อยู่ในหน้าจัดการต่อ"
        }).then((result) => {
            if (result.isConfirmed) {
                window.open('/home', '_blank');
            }
        });
    }
}

// Coordinate Map Preview Modal controls
let previewMapObj = null;
let previewMarker = null;

function previewStopLocation(name, lat, lng) {
    document.getElementById("mapPreviewTitle").innerText = "ตำแหน่งป้ายหยุดรถ: " + name;
    document.getElementById("mapPreviewModal").classList.remove("hidden");

    setTimeout(() => {
        if (previewMapObj) {
            previewMapObj.remove();
            previewMapObj = null;
        }

        previewMapObj = L.map('previewMap', {
            center: [lat, lng],
            zoom: 17,
            zoomControl: false
        });

        // Use Google Maps styled roadmap matching other views
        L.tileLayer('https://{s}.google.com/vt/lyrs=m&x={x}&y={y}&z={z}', {
            maxZoom: 20,
            subdomains: ['mt0', 'mt1', 'mt2', 'mt3'],
            attribution: '&copy; Google Maps'
        }).addTo(previewMapObj);

        L.control.zoom({ position: 'topright' }).addTo(previewMapObj);

        const stopIcon = L.divIcon({
            html: `<div class="bg-orange-500 text-white rounded-full w-8 h-8 flex items-center justify-center font-bold text-xs shadow-md border-2 border-white"><i class="fas fa-map-marker-alt"></i></div>`,
            className: '',
            iconSize: [32, 32]
        });

        previewMarker = L.marker([lat, lng], { icon: stopIcon })
            .addTo(previewMapObj)
            .bindPopup(`<b>${name}</b>`)
            .openPopup();

        previewMapObj.invalidateSize();
    }, 150);
}

function closeMapPreviewModal() {
    document.getElementById("mapPreviewModal").classList.add("hidden");
}

document.addEventListener("DOMContentLoaded", function() {
    // Auto-init integrated module data
    setTimeout(function() {
        if (typeof initIntegratedRouteModule === 'function') {
            initIntegratedRouteModule();
        }
    }, 200);
});
</script>

<!-- Modal แสดงประวัติการประเมินของบุคคลภายนอก -->
<div id="extRatingsModal" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden p-4 z-50 transition-opacity">
    <div class="bg-white p-6 rounded-2xl shadow-xl w-full max-w-lg max-h-[85vh] overflow-y-auto transform transition-all">
        <div class="flex justify-between items-center mb-4 border-b pb-3">
            <h3 class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-star text-amber-400"></i>
                <span>ประวัติการประเมินพนักงานขับรถ</span>
            </h3>
            <button onclick="closeExtRatingsModal()" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        
        <div class="mb-4 text-left">
            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-wider">ผู้ประเมิน</p>
            <h4 id="extRatingUserName" class="text-base font-bold text-slate-800 mt-0.5">-</h4>
            <p id="extRatingUserContact" class="text-xs text-slate-500 font-mono mt-0.5">-</p>
        </div>

        <div id="extRatingsList" class="space-y-3">
            <!-- Rendered dynamically -->
        </div>
    </div>
</div>

<!-- Modal แสดงแผนที่พิกัดจุดจอดรถไฟฟ้า -->
<div id="mapPreviewModal" class="fixed inset-0 bg-black/50 flex items-center justify-center hidden p-4 z-50 transition-opacity">
    <div class="bg-white p-6 rounded-2xl shadow-xl w-full max-w-2xl transform transition-all flex flex-col">
        <div class="flex justify-between items-center mb-4 border-b pb-3 shrink-0">
            <h3 id="mapPreviewTitle" class="text-lg font-bold text-gray-800 flex items-center gap-2">
                <i class="fas fa-map-marked-alt text-pink-500"></i>
                <span>ตำแหน่งป้ายหยุดรถ</span>
            </h3>
            <button onclick="closeMapPreviewModal()" class="text-gray-400 hover:text-gray-600 focus:outline-none">
                <i class="fas fa-times text-lg"></i>
            </button>
        </div>
        <div id="previewMap" class="h-[380px] w-full rounded-xl border border-gray-300 z-10 shrink-0"></div>
    </div>
</div>

</body>
</html>
