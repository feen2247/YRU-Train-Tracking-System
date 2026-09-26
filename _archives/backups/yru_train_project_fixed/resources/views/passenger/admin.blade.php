<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>ระบบติดตามเส้นทางการเดินรถไฟฟ้ามหาวิทยาลัยราชภัฏยะลา - Admin</title>
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
        <button type="button" onclick="toggleProfileDropdown()" class="flex items-center gap-2 focus:outline-none hover:bg-white/10 transition-all rounded-full px-3 py-1.5 border border-white/10">
            <div class="hidden sm:flex flex-col items-end mr-1">
                <span class="text-white text-xs font-bold drop-shadow-sm">{{ Auth::user()->email ?? 'admin@yru.ac.th' }}</span>
            </div>
            <div class="w-8 h-8 rounded-full bg-white text-pink-500 flex items-center justify-center text-sm font-black shadow-md">
                {{ strtoupper(substr(Auth::user()->email ?? 'A', 0, 1)) }}
            </div>
            <i class="fas fa-chevron-down text-white text-[10px] ml-1 transition-transform duration-200" id="profileDropdownIcon"></i>
        </button>

        <!-- Dropdown Menu -->
        <div id="profileDropdownMenu" class="absolute right-0 mt-2 w-48 rounded-xl shadow-lg bg-white ring-1 ring-black ring-opacity-5 hidden opacity-0 transform scale-95 transition-all duration-200 origin-top-right z-50">
            <div class="py-1">
                <a href="{{ url('/') }}" class="group flex items-center px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors font-medium">
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
                    <i class="fas fa-map-marker-alt w-5"></i><span>จุดจอด</span>
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
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <h2 class="text-2xl font-bold text-gray-800">รายงานสถิติภาพรวม</h2>
                <div class="flex flex-wrap gap-2">
                    <button onclick="openTramModal()" class="bg-blue-600 text-white px-4 py-2 rounded-lg hover:bg-blue-700 transition text-sm shadow-sm">+ เพิ่มรถ</button>
                    <button onclick="openStopModal()" class="bg-green-600 text-white px-4 py-2 rounded-lg hover:bg-green-700 transition text-sm shadow-sm">+ เพิ่มจุดจอด</button>
                    <button onclick="showPage('reportView')" class="bg-gray-700 text-white px-4 py-2 rounded-lg hover:bg-gray-800 transition text-sm shadow-sm">ดูรายงานฉบับเต็ม</button>
                </div>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-xl shadow-sm border-b-4 border-blue-500 hover:shadow-md transition">
                    <p class="text-gray-500 text-sm font-medium">รอบการเดินรถทั้งหมด</p>
                    <h3 id="dash-total-users" class="text-3xl font-bold mt-2 text-gray-800">0 รอบ</h3>
                    <button onclick="showPage('reportView')" class="mt-4 text-blue-500 hover:underline text-sm block">ดูรายละเอียดพฤติกรรม</button>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border-b-4 border-green-500 hover:shadow-md transition">
                    <p class="text-gray-500 text-sm font-medium">รถที่พร้อมใช้งานในระบบ</p>
                    <h3 id="dash-tram-count" class="text-3xl font-bold mt-2 text-gray-800">0 คัน</h3>
                    <button onclick="showPage('tram')" class="mt-4 text-green-500 hover:underline text-sm block">ตรวจสอบสถานะรถ</button>
                </div>
                <div class="bg-white p-6 rounded-xl shadow-sm border-b-4 border-orange-500 hover:shadow-md transition">
                    <p class="text-gray-500 text-sm font-medium">จุดจอดรถไฟฟ้าทั้งหมด</p>
                    <h3 id="dash-stop-count" class="text-3xl font-bold mt-2 text-gray-800">0 จุด</h3>
                    <button onclick="showPage('route')" class="mt-4 text-orange-500 hover:underline text-sm block">ดูแผนผังจุดจอด</button>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
                <div class="bg-white p-6 rounded-xl shadow-sm lg:col-span-3">
                    <h3 class="font-bold text-lg mb-4 text-gray-700"><i class="fas fa-chart-area mr-2 text-pink-500"></i>สถิติจำนวนรอบการเดินรถในสัปดาห์นี้</h3>
                    <div class="h-64 relative">
                        <canvas id="weeklyUserChart"></canvas>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm overflow-x-auto">
                <div class="flex justify-between items-center mb-4 min-w-[600px]">
                    <h3 class="font-bold text-lg text-gray-700"><i class="fas fa-bell text-pink-500 mr-2"></i>คิวเรียกรถไฟฟ้าล่าสุด</h3>
                    <div class="flex gap-2">
                        <button onclick="renderDashboardData()" class="bg-gray-100 text-gray-700 px-3 py-1.5 rounded-lg hover:bg-gray-200 transition text-sm font-medium flex items-center gap-1">
                            <i class="fas fa-sync-alt"></i> รีเฟรชข้อมูล
                        </button>
                        <button onclick="clearCallQueue()" class="bg-red-50 text-red-500 px-3 py-1.5 rounded-lg hover:bg-red-100 transition text-sm font-medium flex items-center gap-1">
                            <i class="fas fa-trash-alt"></i> ล้างคิว
                        </button>
                    </div>
                </div>
                <table class="w-full text-left border-collapse min-w-[600px]">
                    <thead>
                        <tr class="border-b text-gray-400 text-sm">
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
            </div>
        </div>

        <div id="reportView" class="page">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <div class="flex items-center space-x-3">
                    <button onclick="showPage('dashboard')" class="bg-gray-200 text-gray-700 px-3 py-1.5 rounded-lg hover:bg-gray-300 transition text-sm">
                        <i class="fas fa-arrow-left"></i> กลับไปหน้าแรก
                    </button>
                    <h2 class="text-2xl font-bold">รายงานสถิติระบบเดินรถภาพรวม</h2>
                </div>
                <button onclick="window.print()" class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700 transition shadow-sm text-sm">
                    <i class="fas fa-print mr-2"></i>พิมพ์ / ส่งออก PDF
                </button>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
                <div class="bg-white p-6 rounded-xl shadow-sm">
                    <h3 class="font-bold text-lg mb-4 text-pink-600"><i class="fas fa-chart-line mr-2"></i>ช่วงเวลาที่มีผู้ใช้บริการหนาแน่นที่สุด</h3>
                    <div id="report-peak-hours" class="space-y-4">
                        <p class="text-center text-gray-400 text-sm py-4">กำลังโหลดข้อมูล...</p>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-xl shadow-sm">
                    <h3 class="font-bold text-lg mb-4 text-blue-600"><i class="fas fa-star mr-2"></i>คะแนนความพึงพอใจการให้บริการรวม</h3>
                    <div class="flex items-center space-x-6 mt-4">
                        <div class="text-center">
                            <h4 id="report-satisf-score" class="text-5xl font-bold text-gray-800">-</h4>
                        </div>
                        <div class="flex-1 border-l pl-6 space-y-1 text-sm text-gray-500">
                            <div class="flex justify-between"><span>⭐⭐⭐⭐⭐ (ดีเยี่ยม)</span><span id="report-star-5" class="font-semibold text-gray-700">-</span></div>
                            <div class="flex justify-between"><span>⭐⭐⭐ (พอใช้)</span><span id="report-star-3" class="font-semibold text-gray-700">-</span></div>
                            <div class="flex justify-between"><span>⭐ (ต้องปรับปรุง)</span><span id="report-star-1" class="font-semibold text-gray-700">-</span></div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-white p-6 rounded-xl shadow-sm overflow-x-auto">
                <h3 class="font-bold text-lg mb-4 text-gray-800">ตารางสถิติจำนวนเที่ยวคันแยกตามคันรถ</h3>
                <table class="w-full text-left border-collapse min-w-[500px]">
                    <thead>
                        <tr class="border-b bg-gray-50 text-gray-600 text-sm">
                            <th class="p-3">คันรถไฟฟ้า</th>
                            <th class="p-3">จำนวนเที่ยววิ่งรวม/วัน</th>
                            <th class="p-3">จำนวนผู้โดยสาร</th>
                            <th class="p-3">สถานะความเสถียร</th>
                        </tr>
                    </thead>
                    <tbody id="report-route-tbody" class="text-sm">
                        <tr><td colspan="4" class="p-3 text-center text-gray-400">กำลังโหลดข้อมูล...</td></tr>
                    </tbody>
                </table>
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
                        <option value="กำลังปรับปรุง">กำลังปรับปรุง</option>
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



        <div id="route" class="page">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">จุดจอดรถ</h2>
                    <p class="text-gray-500 text-sm">กำหนดชื่อป้ายสถานีรถไฟฟ้าและผูกเข้ากับเส้นทางเดินรถต่าง ๆ</p>
                </div>
                <button onclick="openStopModal()" class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700 transition shadow-sm text-sm">+ เพิ่ม</button>
            </div>
            <div class="bg-white rounded-xl shadow-sm overflow-hidden overflow-x-auto">
                <table class="w-full text-left min-w-[500px]">
                    <thead class="bg-gray-50 text-gray-600 text-sm">
                        <tr>
                            <th class="p-4 w-20">ลำดับ</th>
                            <th class="p-4">ชื่อป้าย / จุดจอดรถ</th>
                            <th class="p-4">พิกัด (ละติจูด, ลองจิจูด)</th>
                            <th class="p-4 text-center">การจัดการข้อมูล</th>
                        </tr>
                    </thead>
                    <tbody id="stopTable" class="text-sm"></tbody>
                </table>
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
                <label class="block text-xs font-semibold text-gray-500 mb-1">เบอร์โทรศัพท์</label>
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
                    <option value="ใช้งาน">ใช้งาน</option>
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
                            <input type="number" id="modalTramCapSit" min="0" class="w-full border border-gray-300 p-2 pr-8 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none text-sm" placeholder="20">
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
                            <option value="กำลังปรับปรุง">กำลังปรับปรุง</option>
                        </select>
                    </div>
                    <input type="hidden" id="modalTramRoute" value="สาย 1: เนินขาม-หอพัก">
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
                        <input type="date" id="modalTramPurchaseDate" class="w-full border border-gray-300 p-2 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none text-sm bg-white">
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
                    <input type="date" id="modalRepairDate" class="w-full border border-gray-300 p-2.5 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none bg-white">
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

const defaultUsers = [
    { user_id: "USR-000001", emp_id: "69001", name: "นายมูฮัมหมัด ซอและ", username: "muhammad", email: "muhammad@yru.ac.th", role: "admin", status: "ใช้งาน" },
    { user_id: "USR-000002", emp_id: "69002", name: "ดร.สมชาย เรียนดี", username: "somchai", email: "somchai@yru.ac.th", role: "executive", status: "ใช้งาน" },
    { user_id: "USR-000003", emp_id: "69003", name: "นายอัสมี มูเล็ง", username: "asmee", email: "asmee@yru.ac.th", role: "driver", status: "ใช้งาน" },
    { user_id: "USR-000004", emp_id: "69004", name: "นายอัรฟาน มะเระ", username: "arfan", email: "arfan@yru.ac.th", role: "driver", status: "ใช้งาน" },
    { user_id: "USR-000005", emp_id: "69005", name: "นายซูเฟียน มะโละ", username: "sufiyan", email: "sufiyan@yru.ac.th", role: "driver", status: "ใช้งาน" },
    { user_id: "USR-000006", emp_id: "69006", name: "นายอุสมาน สาและ", username: "usman", email: "usman@yru.ac.th", role: "driver", status: "ใช้งาน" },
    { user_id: "USR-000007", emp_id: "69007", name: "นายบัดรี สาและ", username: "badri", email: "badri@yru.ac.th", role: "driver", status: "ใช้งาน" },
    { user_id: "USR-000008", emp_id: "69008", name: "นายตอริก ลือแมะ", username: "torik", email: "torik@yru.ac.th", role: "driver", status: "ใช้งาน" },
    { user_id: "USR-000009", emp_id: "69009", name: "นายสมหวัง ใจดี", username: "somwang", email: "somwang@yru.ac.th", role: "driver", status: "ใช้งาน" },
    { user_id: "USR-000010", emp_id: "69010", name: "นายสมใจ ใจดี", username: "somjal", email: "somjal@yru.ac.th", role: "driver", status: "ใช้งาน" },
    { user_id: "USR-000011", emp_id: "69011", name: "นายกิตติ ตั้งใจ", username: "kitti", email: "kitti@yru.ac.th", role: "driver", status: "ใช้งาน" },
    { user_id: "USR-000012", emp_id: "69012", name: "นายรุสลัน สอเฮาะ", username: "ruslan", email: "ruslan@yru.ac.th", role: "driver", status: "ใช้งาน" }
];

// LocalStorage Manager Helpers
function getStorage(key, defaultData) {
    if (!localStorage.getItem(key)) localStorage.setItem(key, JSON.stringify(defaultData));
    return JSON.parse(localStorage.getItem(key));
}
function setStorage(key, data) { localStorage.setItem(key, JSON.stringify(data)); }

let trams = getStorage("yru_trams_v16", defaultTrams);
// บังคับอัปเดตข้อมูลถ้ารถมีไม่ถึง 10 คัน
if (!trams || trams.length < 10) {
    trams = defaultTrams;
}
// ตั้งค่าพิกัดรถคันที่ 1-10 ให้อยู่ประจำจุดจอดตามที่ร้องขอ
trams.forEach(t => {
    if (t.id === "EV-01") t.coords = "6.549929, 101.291254"; // จุดจอด 1
    if (t.id === "EV-02") t.coords = "6.549100, 101.290467"; // จุดจอด 2
    if (t.id === "EV-03") t.coords = "6.547835, 101.289502"; // จุดจอด 3
    if (t.id === "EV-04") t.coords = "6.547224, 101.289471"; // จุดจอด 4
    if (t.id === "EV-05") t.coords = "6.547311, 101.288880"; // จุดจอด 5
    if (t.id === "EV-06") t.coords = "6.548822, 101.288523"; // จุดจอด 6
    if (t.id === "EV-07") t.coords = "6.549225, 101.289286"; // จุดจอด 7
    if (t.id === "EV-08") t.coords = "6.549929, 101.291254"; // จุดจอด 1
    if (t.id === "EV-09") t.coords = "6.547835, 101.289502"; // จุดจอด 3
    if (t.id === "EV-10") t.coords = "6.547311, 101.288880"; // จุดจอด 5
});
setStorage("yru_trams_v16", trams);

let stops = getStorage("yru_stops_v2", defaultStops);
let users = getStorage("yru_users_v7", defaultUsers);
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
    listContainer.innerHTML = "";
    
    // Default option
    const defaultOpt = document.createElement("div");
    defaultOpt.className = "driver-option p-2 hover:bg-pink-50 rounded-md cursor-pointer transition text-gray-500 font-medium";
    defaultOpt.innerText = "-- ไม่ระบุ / ยังไม่มอบหมาย --";
    defaultOpt.onclick = () => selectDriver("", "-- ไม่ระบุ / ยังไม่มอบหมาย --");
    listContainer.appendChild(defaultOpt);
    
    // Filter active drivers
    const activeDrivers = users.filter(user => user.role === 'driver' && user.status === 'ใช้งาน');
    
    activeDrivers.forEach(driver => {
        const option = document.createElement("div");
        option.className = "driver-option p-2 hover:bg-pink-50 rounded-md cursor-pointer transition text-gray-800 font-medium";
        option.innerText = driver.name;
        option.onclick = () => selectDriver(driver.user_id, driver.name);
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
    document.querySelectorAll(".page").forEach(p => p.style.display = "none");
    document.getElementById(pageId).style.display = "block";
    
    if (pageId === 'dashboard') {
        renderDashboardData();
        initWeeklyChart(); // วาดกราฟใหม่ทุกครั้งที่กลับมาหน้าแดชบอร์ด
    }
    if (pageId === 'tram') renderTramTable();
    if (pageId === 'route') renderStopTable();
    if (pageId === 'users') renderUserTable();
    if (pageId === 'reportView') renderReportView();
    if (pageId === 'role') renderRolePermissionTable();
    if (pageId === 'maintenance') renderMaintenanceDashboard();
    if (pageId === 'externalUsers') renderExternalUserTable();
}

// สไตล์ป้ายและสถานะสีต่าง ๆ 
function getStatusStyle(status) {
    if (status === "กำลังใช้งาน" || status === "ใช้งาน" || status === "พร้อมใช้งาน") return "text-green-600 font-semibold";
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

// ฟังก์ชันสร้างและวาดกราฟรอบสัปดาห์ด้วย Chart.js
function initWeeklyChart() {
    const ctx = document.getElementById('weeklyUserChart').getContext('2d');
    if (myChart) myChart.destroy(); // เคลียร์ขยะกราฟเก่าก่อนวาดใหม่ป้องกันการซ้อนทับกัน

    const labels = ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัสฯ', 'ศุกร์', 'เสาร์', 'อาทิตย์'];
    
    // ตั้งค่าข้อมูลวันอื่นๆ เป็น 0 ทั้งหมดก่อน ตามที่ผู้ใช้งานร้องขอ
    const statsData = [0, 0, 0, 0, 0, 0, 0];

    // ดึงข้อมูลจำนวนคนจริงสะสมจาก localStorage ของวันปัจจุบันมาแสดง
    const dayIndexMap = [6, 0, 1, 2, 3, 4, 5]; // 0=Sun(6), 1=Mon(0), 2=Tue(1), 3=Wed(2), 4=Thu(3), 5=Fri(4), 6=Sat(5)
    const currentDayIndex = dayIndexMap[new Date().getDay()];
    
    const liveTodayTrips = parseInt(localStorage.getItem('yru_today_trips_accumulated') || '0');
    
    // แทนที่เฉพาะวันปัจจุบันด้วยจำนวนรอบเรียกรถจริงสะสม
    statsData[currentDayIndex] = liveTodayTrips;

    myChart = new Chart(ctx, {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                label: 'จำนวนรอบการเดินรถ (รอบ/วัน)',
                data: statsData,
                backgroundColor: 'rgba(236, 72, 153, 0.2)',
                borderColor: 'rgba(236, 72, 153, 1)',
                borderWidth: 3,
                tension: 0.3,
                fill: true
            }]
        },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: false } },
            scales: { y: { beginAtZero: true } }
        }
    });
}

// เรนเดอร์แดชบอร์ดหลัก
function renderDashboardData() {
    document.getElementById("dash-tram-count").innerText = trams.length + " คัน";
    document.getElementById("dash-stop-count").innerText = stops.length + " จุด";

    // อ่านคิวเรียกรถจาก localStorage
    const callQueue = JSON.parse(localStorage.getItem('yru_call_queue') || '[]');

    // ดึงค่าผู้โดยสารสะสมประจำวันจาก localStorage (ตัวเลขจะเพิ่มขึ้นเรื่อยๆ เมื่อมีการกดยืนยันเรียก)
    const liveTodayTrips = parseInt(localStorage.getItem('yru_today_trips_accumulated') || '0');
    document.getElementById("dash-total-users").innerText = liveTodayTrips + " รอบ";

    // อัปเดตข้อมูลกราฟในวันปัจจุบันแบบ Real-time ทันทีที่มีการเรียกรถ
    if (myChart && myChart.data && myChart.data.datasets && myChart.data.datasets[0]) {
        const liveTodayTrips = parseInt(localStorage.getItem('yru_today_trips_accumulated') || '0');
        const dayIndexMap = [6, 0, 1, 2, 3, 4, 5];
        const currentDayIndex = dayIndexMap[new Date().getDay()];
        myChart.data.datasets[0].data[currentDayIndex] = liveTodayTrips;
        myChart.update();
    }

    // เรนเดอร์ตารางคิว
    const logTable = document.getElementById("dashboardLogTable");
    logTable.innerHTML = "";

    if (callQueue.length === 0) {
        logTable.innerHTML = `<tr><td colspan="6" class="p-6 text-center text-gray-400">
            <i class="fas fa-inbox text-3xl mb-2 block text-gray-300"></i>
            ยังไม่มีคิวเรียกรถไฟฟ้าในขณะนี้
        </td></tr>`;
        return;
    }

    callQueue.forEach((call) => {
        const statusBadge = call.status === 'waiting'
            ? `<span class="bg-amber-100 text-amber-700 text-xs font-bold px-2.5 py-1 rounded-full"><i class="fas fa-clock mr-1"></i>รอรถ</span>`
            : `<span class="bg-green-100 text-green-700 text-xs font-bold px-2.5 py-1 rounded-full"><i class="fas fa-check mr-1"></i>รับแล้ว</span>`;

        logTable.innerHTML += `
            <tr class="border-b hover:bg-pink-50/40 transition">
                <td class="p-3 pl-0 font-mono text-gray-500 text-xs">${call.id}</td>
                <td class="p-3 text-gray-500 text-xs">${call.time}</td>
                <td class="p-3 font-semibold text-gray-800">
                    <i class="fas fa-map-marker-alt text-pink-500 mr-1"></i>${call.station}
                </td>
                <td class="p-3 text-gray-600">
                    <i class="fas fa-flag-checkered text-blue-500 mr-1"></i>${call.destination}
                </td>
                <td class="p-3 text-center">
                    <span class="bg-pink-100 text-pink-700 font-bold text-sm px-3 py-1 rounded-full">
                        <i class="fas fa-users mr-1"></i>${call.pax} คน
                    </span>
                </td>
                <td class="p-3 text-center">${statusBadge}</td>
            </tr>`;
    });
}

function clearCallQueue() {
    if (confirm('ล้างคิวเรียกรถและรีเซ็ตจำนวนผู้บริการและรอบสะสมวันนี้กลับเป็น 0 ใช่หรือไม่?')) {
        localStorage.removeItem('yru_call_queue');
        localStorage.removeItem('yru_today_pax_accumulated');
        localStorage.removeItem('yru_today_trips_accumulated');
        renderDashboardData();
    }
}


// เรนเดอร์หน้ารายงานสถิติภาพรวม (อิงข้อมูลจริงจาก API + localStorage)
function renderReportView() {
    const liveTodayPax = parseInt(localStorage.getItem('yru_today_pax_accumulated') || '0');
    const callQueue = JSON.parse(localStorage.getItem('yru_call_queue') || '[]');
    const container = document.getElementById('report-peak-hours');
    
    const colors = [
        { text: 'text-pink-600', bg: 'bg-pink-500' },
        { text: 'text-purple-600', bg: 'bg-purple-500' },
        { text: 'text-pink-400', bg: 'bg-pink-400' }
    ];

    // === ส่วนช่วงเวลาหนาแน่น ===
    if (liveTodayPax > 0 && callQueue.length > 0) {
        let ranges = [
            { label: '08:00 น. - 10:00 น. (เข้าเรียนช่วงเช้า)', hours: [6, 7, 8, 9, 10, 11], count: 0, percent: 0 },
            { label: '12:00 น. - 13:30 น. (พักเที่ยงทานอาหาร)', hours: [12, 13, 14], count: 0, percent: 0 },
            { label: '15:30 น. - 17:00 น. (เลิกเรียน/กลับบ้าน)', hours: [15, 16, 17, 18, 19, 20, 21, 22, 23, 0, 1, 2, 3, 4, 5], count: 0, percent: 0 },
        ];
        
        let totalQueuePax = 0;
        callQueue.forEach(call => {
            if (call.time) {
                const parts = call.time.split(':');
                if (parts.length >= 2) {
                    const hour = parseInt(parts[0]);
                    const minute = parseInt(parts[1]);
                    
                    totalQueuePax += call.pax;
                    
                    if (hour >= 6 && hour < 12) {
                        ranges[0].count += call.pax;
                    } else if (hour >= 12 && hour < 15) {
                        ranges[1].count += call.pax;
                    } else {
                        ranges[2].count += call.pax;
                    }
                }
            }
        });
        
        if (totalQueuePax > 0) {
            ranges.forEach(r => {
                r.percent = Math.round((r.count / totalQueuePax) * 100);
            });
        }
        
        container.innerHTML = ranges.map((r, i) => {
            const c = colors[i] || colors[0];
            return `
                <div>
                    <div class="flex justify-between text-sm text-gray-600 mb-1">
                        <span>${r.label}</span>
                        <span class="font-bold ${c.text}">${r.percent}%</span>
                    </div>
                    <div class="w-full bg-gray-100 h-3 rounded-full">
                        <div class="${c.bg} h-3 rounded-full transition-all" style="width: ${Math.max(r.percent, 3)}%"></div>
                    </div>
                </div>
            `;
        }).join('');
    } else {
        // ดึงข้อมูลสถิติประวัติจาก API จริง
        fetch('/api/get-peak-hours?t=' + Date.now())
            .then(res => res.json())
            .then(data => {
                if (!data.ranges || data.total_users === 0) {
                    container.innerHTML = '<p class="text-center text-gray-400 text-sm py-4">ยังไม่มีข้อมูลการใช้บริการในวันนี้</p>';
                    return;
                }
                container.innerHTML = data.ranges.map((r, i) => {
                    const c = colors[i] || colors[0];
                    return `
                        <div>
                            <div class="flex justify-between text-sm text-gray-600 mb-1">
                                <span>${r.label}</span>
                                <span class="font-bold ${c.text}">${r.percent}%</span>
                            </div>
                            <div class="w-full bg-gray-100 h-3 rounded-full">
                                <div class="${c.bg} h-3 rounded-full transition-all" style="width: ${Math.max(r.percent, 3)}%"></div>
                            </div>
                        </div>
                    `;
                }).join('');
            })
            .catch(err => console.error('Error fetching peak hours:', err));
    }

    // === ส่วนคะแนนความพึงพอใจ (จากแบบประเมินจริงใน localStorage) ===
    const surveys = JSON.parse(localStorage.getItem('yru_surveys') || '[]');
    const scoreEl = document.getElementById('report-satisf-score');
    const star5El = document.getElementById('report-star-5');
    const star3El = document.getElementById('report-star-3');
    const star1El = document.getElementById('report-star-1');

    if (surveys.length > 0) {
        const avgScore = surveys.reduce((sum, s) => sum + s.avg, 0) / surveys.length;
        scoreEl.innerText = avgScore.toFixed(2); // แสดง 2 ตำแหน่งตามหน้าผู้บริหาร

        const cnt5 = surveys.filter(s => s.avg >= 4.5).length;
        const cnt3 = surveys.filter(s => s.avg >= 2.5 && s.avg < 4.5).length;
        const cnt1 = surveys.filter(s => s.avg < 2.5).length;
        
        star5El.innerText = Math.round((cnt5 / surveys.length) * 100) + '%';
        star3El.innerText = Math.round((cnt3 / surveys.length) * 100) + '%';
        star1El.innerText = Math.round((cnt1 / surveys.length) * 100) + '%';
    } else {
        scoreEl.innerText = '-';
        star5El.innerText = '-';
        star3El.innerText = '-';
        star1El.innerText = '-';
    }

    // === ส่วนตารางสถิติจำนวนเที่ยวคันแยกตามคันรถจริง ===
    const trams = getStorage("yru_trams_v16", defaultTrams);
    
    // คำนวณผู้โดยสารและจำนวนเที่ยวแยกตามคันรถจริงจากคิวเรียกรถ
    const carPaxMap = {};
    const carTripsMap = {};
    
    trams.forEach(t => {
        carPaxMap[t.id] = 0;
        carTripsMap[t.id] = 0;
    });
    
    callQueue.forEach(call => {
        const cid = call.car_id || "EV-01";
        if (carPaxMap[cid] !== undefined) {
            carPaxMap[cid] += call.pax;
            carTripsMap[cid] += 1;
        }
    });

    fetch('/api/get-today-stats?t=' + Date.now())
        .then(res => res.json())
        .then(data => {
            const tbody = document.getElementById('report-route-tbody');
            tbody.innerHTML = "";
            
            const totalUsers = liveTodayPax > 0 ? liveTodayPax : (data.today_total_users || 0);

            trams.forEach((tram, index) => {
                let pCount = 0;
                let trips = 0;

                if (liveTodayPax > 0) {
                    pCount = carPaxMap[tram.id] || 0;
                    trips = carTripsMap[tram.id] || 0;
                } else {
                    // ดึงข้อมูลสะสมจาก Cache ของระบบ
                    if (data.car_stats && data.car_stats[tram.id] !== undefined) {
                        pCount = data.car_stats[tram.id];
                    } else if (data.car_stats && data.car_stats[index + 1] !== undefined) {
                        pCount = data.car_stats[index + 1];
                    }
                    trips = pCount > 0 ? Math.ceil(pCount / 8) : 0;
                }

                let statusBadge = '<span class="bg-gray-100 text-gray-500 px-2 py-0.5 rounded text-xs">ยังไม่มีข้อมูล</span>';
                if (pCount > 0 || tram.status !== 'พร้อมใช้งาน') {
                    statusBadge = tram.status === 'รถขัดข้อง'
                        ? '<span class="bg-red-100 text-red-700 px-2 py-0.5 rounded text-xs">ขัดข้อง</span>'
                        : '<span class="bg-green-100 text-green-700 px-2 py-0.5 rounded text-xs">ปกติ</span>';
                }

                const tramColor = index === 0 ? 'text-yellow-500' : index === 1 ? 'text-pink-500' : 'text-gray-500';
                const nameDesc = tram.name || `รถไฟฟ้าคันที่ ${index + 1}`;

                tbody.innerHTML += `
                    <tr class="border-b hover:bg-gray-50/50 transition">
                        <td class="p-3 font-semibold ${tramColor}"><i class="fas fa-shuttle-van mr-1"></i>${tram.id} (${nameDesc})</td>
                        <td class="p-3">${trips} เที่ยว</td>
                        <td class="p-3">${pCount.toLocaleString()} คน</td>
                        <td class="p-3">${statusBadge}</td>
                    </tr>
                `;
            });
        })
        .catch(err => console.error('Error fetching report data:', err));
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
    users = getStorage("yru_users_v7", defaultUsers);

    const table = document.getElementById("tramTable");
    table.innerHTML = "";
    const dataToRender = filteredData ? filteredData : trams;

    if(dataToRender.length === 0){
        table.innerHTML = `<tr><td colspan="8" class="p-8 text-center text-gray-400">❌ ไม่พบข้อมูลรถไฟฟ้าที่ค้นหา</td></tr>`;
        return;
    }

    dataToRender.forEach((tram) => {
        const realIndex = trams.findIndex(t => t.id === tram.id);
        const status = tram.status || "พร้อมใช้งาน";
        let badgeColor = "bg-green-100 text-green-700";
        if (status === "รถขัดข้อง") badgeColor = "bg-red-50 text-red-600 border border-red-200";
        else if (status === "กำลังปรับปรุง") badgeColor = "bg-amber-100 text-amber-700";
        
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
        const capSit = tram.capacity_sit !== undefined ? tram.capacity_sit : 20;
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

function openTramModal() {
    document.getElementById("tramModalTitle").innerText = "+ เพิ่มรถไฟฟ้าใหม่เข้าสู่ระบบ";
    document.getElementById("editTramId").value = "-1";
    document.getElementById("modalTramId").value = "";
    document.getElementById("modalTramId").disabled = false;
    document.getElementById("modalTramName").value = "";
    document.getElementById("modalTramPlate").value = "";
    document.getElementById("modalTramCapSit").value = "20";
    document.getElementById("modalTramCapStand").value = "10";
    document.getElementById("modalTramStatus").value = "พร้อมใช้งาน";
    document.getElementById("modalTramRoute").value = "สาย 1: เนินขาม-หอพัก";
    populateDriverDropdown();
    document.getElementById("driverSearchInput").value = "";
    selectDriver("", "-- ไม่ระบุ / ยังไม่มอบหมาย --");
    document.getElementById("modalTramBattery").value = "100";
    
    // Reset Section 3 fields
    document.getElementById("modalTramPurchaseDate").value = "";
    document.getElementById("modalTramWarranty").value = "";
    document.getElementById("modalTramSupplier").value = "";
    
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
    document.getElementById("modalTramRoute").value = tram.route || "สาย 1: เนินขาม-หอพัก";
    populateDriverDropdown();
    document.getElementById("driverSearchInput").value = "";
    const foundDriver = users.find(u => u.name === tram.driver || (u.user_id && u.user_id === tram.driver_id));
    if (foundDriver) {
        selectDriver(foundDriver.user_id, foundDriver.name);
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
    
    const capSit = tram.capacity_sit !== undefined ? tram.capacity_sit : 20;
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
    
    document.getElementById("tramDetailsModal").classList.remove("hidden");
}

function closeTramDetailsModal() {
    document.getElementById("tramDetailsModal").classList.add("hidden");
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
    const editIndex = parseInt(document.getElementById("editTramId").value);

    if (!idField) { alert("กรุณากรอกรหัสรถไฟฟ้า!"); return; }
    if (!nameField) { alert("กรุณากรอกชื่อเรียก/หมายเลขคัน!"); return; }
    if (!plateField) { alert("กรุณากรอกทะเบียนรถ!"); return; }
    if (isNaN(capSitField) || capSitField < 0) { alert("กรุณากรอกจำนวนที่นั่งให้ถูกต้อง!"); return; }
    if (isNaN(capStandField) || capStandField < 0) { alert("กรุณากรอกจำนวนที่ยืนให้ถูกต้อง!"); return; }
    if (isNaN(batteryField) || batteryField < 0 || batteryField > 100) { alert("กรุณากรอกระดับแบตเตอรี่ (%) ระหว่าง 0 - 100!"); return; }

    // If editing, preserve the rest of properties (coords, maintenance, purchase_date, warranty, supplier)
    let existingTram = {};
    if (editIndex !== -1) {
        existingTram = trams[editIndex];
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
        driver: driverField,
        driver_id: driverIdField,
        battery: batteryField,
        image: uploadedImageBase64 || existingTram.image || "",
        coords: existingTram.coords || "6.549710, 101.291365",
        active_issue: statusField === "รถขัดข้อง" ? (existingTram.active_issue || "รอดำเนินการซ่อม (แบตเตอรี่ร้อนเกินกำหนด) - แจ้งเมื่อ 08/07/2569 10:45 น.") : "",
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

    setStorage("yru_trams_v16", trams);
    closeTramModal();
    document.getElementById("tramFilterStatus").value = "ทั้งหมด";
    document.getElementById("tramSearchInput").value = "";
    renderTramTable();
    renderDashboardData();
}

function deleteTram(index) {
    if (confirm("คุณต้องการลบรถคันนี้ออกจากฐานข้อมูลใช่หรือไม่?")) {
        trams.splice(index, 1);
        setStorage("yru_trams_v16", trams);
        renderTramTable();
        renderDashboardData();
    }
}

// อัปเดตยอดผู้ใช้งานวันนี้แบบ Real-time จาก localStorage
setInterval(() => {
    const el = document.getElementById('dash-total-users');
    if (el) {
        const liveTodayTrips = parseInt(localStorage.getItem('yru_today_trips_accumulated') || '0');
        el.innerText = liveTodayTrips + " รอบ";
    }
}, 2000);


// --- ส่วนจัดการเส้นทาง / จุดจอด (STOPS ENGINE) ---
function renderStopTable() {
    const table = document.getElementById("stopTable");
    table.innerHTML = "";
    stops.forEach((stop, index) => {
        const seq = stop.sequence !== undefined ? stop.sequence : (index + 1);
        const lat = stop.lat !== undefined ? stop.lat : "-";
        const lng = stop.lng !== undefined ? stop.lng : "-";
        const rName = stop.route !== undefined ? stop.route : "สายสีชมพู";
        table.innerHTML += `
            <tr class="border-t hover:bg-gray-50 transition">
                <td class="p-4 font-semibold text-gray-700">${seq}</td>
                <td class="p-4 font-semibold text-gray-800"><i class="fas fa-map-pin text-orange-500 mr-2"></i>${stop.name}</td>
                <td class="p-4 text-xs font-mono text-gray-500">${lat}, ${lng}</td>
                <td class="p-4 text-center space-x-1">
                    <button onclick="editStop(${index})" class="bg-blue-500 text-white px-3 py-1 rounded-md hover:bg-blue-600 transition text-xs font-medium">แก้ไข</button>
                    <button onclick="deleteStop(${index})" class="bg-red-500 text-white px-3 py-1 rounded-md hover:bg-red-600 transition text-xs font-medium">ลบ</button>
                </td>
            </tr>`;
    });
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
}

// ฟังก์ชันลบจุดจอด
function deleteStop(index) {
    if (confirm(`คุณมั่นใจที่จะลบ "${stops[index].name}" ออกใช่หรือไม่?`)) {
        stops.splice(index, 1);
        setStorage("yru_stops_v2", stops);
        renderStopTable();
    }
}

// --- ส่วนจัดการผู้ใช้งานระบบ (USERS ENGINE) ---
function renderUserTable(filteredUsers = null) {
    const table = document.getElementById("userTable");
    table.innerHTML = "";
    const dataToRender = filteredUsers ? filteredUsers : users;

    if (dataToRender.length === 0) {
        table.innerHTML = `<tr><td colspan="7" class="p-8 text-center text-gray-400">❌ ไม่พบรายชื่อผู้ใช้งานระบบหลังบ้าน</td></tr>`;
        return;
    }

    dataToRender.forEach((user) => {
        const realIndex = users.findIndex(u => u.email === user.email);
        const roleBadge = getRoleBadge(user.role);
        const empId = user.emp_id || "-";
        const username = user.username || "-";
        
        table.innerHTML += `
            <tr class="border-t hover:bg-gray-50 transition">
                <td class="p-4 text-gray-600">${empId}</td>
                <td class="p-4 font-semibold text-gray-800">${user.name}</td>
                <td class="p-4 text-gray-600">${username}</td>
                <td class="p-4 text-gray-600">${user.email}</td>
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
    document.getElementById("modalUserStatus").value = "ใช้งาน";
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

    if (!prefix || !firstName || !lastName || !username || !empId || !email || !role || !status) { 
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

    setStorage("yru_users_v7", users);

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
        setStorage("yru_users_v7", users);
        
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
        return u.name.toLowerCase().includes(keyword) || 
               u.email.toLowerCase().includes(keyword) ||
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
        let mappedStatus = 'ใช้งาน';
        const sLower = statusRaw.toLowerCase();
        if (sLower === '' || sLower.includes('ใช้') || sLower.includes('active') || sLower.includes('ปกติ')) mappedStatus = 'ใช้งาน';
        else if (sLower.includes('ระงับ') || sLower.includes('ban') || sLower.includes('inactive')) mappedStatus = 'ระงับใช้งาน';
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
    
    setStorage("yru_users_v7", users);
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
    const repairingTrams = trams.filter(t => t.status === "กำลังปรับปรุง");
    
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
    trams[index].status = "กำลังปรับปรุง";
    setStorage("yru_trams_v16", trams);
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
    setStorage("yru_trams_v16", trams);
    
    // Close modal and refresh
    closeRepairCompleteModal();
    renderMaintenanceDashboard();
    renderTramTable();
    
    alert(`บันทึกผลงานการซ่อมบำรุงรถไฟฟ้า ${targetTram.id} คืนสถานะพร้อมใช้งานเรียบร้อยแล้ว!`);
}

// บังคับให้โหลดหน้า Dashboard ขึ้นมาเป็นหน้าแรกสุดเสมอตอนเปิดเว็บ
showPage("dashboard");

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
        localStorage.removeItem("yru_users_v7");
        location.reload();
    }
}

function exportSystemData() {
    const data = {
        yru_trams_v16: JSON.parse(localStorage.getItem("yru_trams_v16")),
        yru_users_v7: JSON.parse(localStorage.getItem("yru_users_v7")),
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
                if (content.yru_users_v7) localStorage.setItem("yru_users_v7", JSON.stringify(content.yru_users_v7));
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

</body>
</html>
