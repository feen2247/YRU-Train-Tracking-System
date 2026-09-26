<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ระบบติดตามเส้นทางการเดินรถไฟฟ้ามหาวิทยาลัยราชภัฏยะลา</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
    <!-- Google Fonts Kanit -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <!-- Leaflet CSS & JS -->
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <style>
        * { 
            box-sizing: border-box; 
            font-family: 'Kanit', sans-serif;
        }
        body, button, input, select, textarea, div, span, p, a, h1, h2, h3, h4, h5, h6, label, td, th, .leaflet-container, .leaflet-popup-content-wrapper, .leaflet-popup-content, .leaflet-tooltip, .swal2-popup, .swal2-title, .swal2-content {
            font-family: 'Kanit', sans-serif !important;
        }
        /* Protect FontAwesome Icons from being overridden by Kanit */
        .fa, .fas, .far, .fab, .fa-solid, .fa-regular, .fa-brands, [class*="fa-"] {
            font-family: 'Font Awesome 6 Free', 'Font Awesome 6 Brands', 'Font Awesome 5 Free', sans-serif !important;
        }
        body { overflow: hidden; height: 100vh; display: flex; flex-direction: column; }
        .map-bg { position: relative; flex: 1; overflow: hidden; min-height: 0; }

        /* Custom scrollbar */
        .custom-scrollbar::-webkit-scrollbar { width: 5px; }
        .custom-scrollbar::-webkit-scrollbar-track { background: transparent; }
        .custom-scrollbar::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }
        .custom-scrollbar::-webkit-scrollbar-thumb:hover { background: #94a3b8; }

        /* Leaflet icon reset */ 
        .custom-leaflet-icon {
            background: none !important;
            border: none !important;
            box-shadow: none !important;
        }

        /* ===== Animations ===== */
        @keyframes fadeInUp {
            from { opacity: 0; transform: translateY(16px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in-up { animation: fadeInUp 0.3s ease-out forwards; }

        @keyframes slideInRight {
            from { opacity: 0; transform: translateX(20px); }
            to { opacity: 1; transform: translateX(0); }
        }
        .animate-slide-in { animation: slideInRight 0.35s ease-out forwards; }

        /* เส้นทางวิ่ง */
        .route-path {
            stroke-dasharray: 2500;
            stroke-dashoffset: 2500;
            animation: drawRoute 2.5s ease forwards;
        }
        @keyframes drawRoute {
            to { stroke-dashoffset: 0; }
        }

        /* จุดจอด และไอคอนรถ อยู่นิ่ง ไม่กระพริบทุกคัน */
        .station-dot { animation: none !important; }
        .shuttle-float { animation: none !important; }
        .subtle-pulse { animation: none !important; display: none !important; }
        .eta-now { animation: none !important; }
        .pulse-container { animation: none !important; }

        /* Vehicle pill strip — compact premium tags */
        .vehicle-strip {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        .vehicle-pill {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 7px 14px 7px 10px;
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(226, 232, 240, 0.8);
            background: white;
            font-size: 11px;
            font-weight: 700;
            white-space: nowrap;
            box-shadow: 0 4px 10px rgba(15, 23, 42, 0.03);
        }
        .vehicle-pill:hover {
            transform: translateY(-2px) scale(1.03);
            box-shadow: 0 8px 20px rgba(15, 23, 42, 0.08);
            border-color: rgba(203, 213, 225, 0.8);
        }
        .vehicle-pill .v-num {
            width: 22px; height: 22px;
            border-radius: 8px;
            display: flex; align-items: center; justify-content: center;
            color: white;
            font-size: 10px;
            font-weight: 900;
            flex-shrink: 0;
            box-shadow: 0 2px 5px rgba(0,0,0,0.08);
        }
        .vehicle-pill .v-occ {
            font-size: 10px;
            font-weight: 600;
            color: #64748b;
            margin-left: -1px;
        }
        .vehicle-pill .v-status {
            width: 8px; height: 8px;
            border-radius: 50%;
            flex-shrink: 0;
            box-shadow: 0 0 0 2.5px rgba(255,255,255,1), 0 0 6px rgba(0,0,0,0.1);
        }

        /* Station row — vertical layout with clean flex-wrap ETA below */
        .station-row {
            display: flex;
            flex-direction: column;
            padding: 12px 14px 10px;
            margin-bottom: 8px;
            background: white;
            border-radius: 16px;
            cursor: pointer;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
            border: 1px solid rgba(241, 245, 249, 0.8);
            box-shadow: 0 4px 12px rgba(15, 23, 42, 0.02);
            border-left: 4px solid transparent;
        }
        .station-row:hover {
            border-color: rgba(252, 231, 243, 0.8);
            border-left-color: #ec4899;
            background: #fff;
            transform: translateX(3px);
            box-shadow: 0 6px 18px rgba(236, 72, 153, 0.05);
        }
        .station-row .st-top {
            display: flex;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 8px;
        }
        .station-row .st-left {
            display: flex;
            align-items: center;
            min-width: 0;
            flex: 1;
            margin-right: 12px;
        }
        .station-row .st-num {
            width: 24px; height: 24px;
            border-radius: 6px;
            display: flex; align-items: center; justify-content: center;
            font-weight: 900;
            font-size: 10px;
            flex-shrink: 0;
            background: #f1f5f9;
            color: #64748b;
            transition: all 0.2s;
            box-shadow: inset 0 1px 2px rgba(0,0,0,0.02);
        }
        .station-row:hover .st-num {
            background: #ec4899;
            color: white;
        }
        .station-row .st-name {
            font-size: 13px;
            font-weight: 700;
            color: #334155;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }
        .station-row .st-eta-container {
            display: flex;
            align-items: center;
            gap: 6px;
            flex-wrap: wrap;
        }
        .eta-chip {
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-size: 10px;
            font-weight: 800;
            padding: 4px 8px;
            border-radius: 8px;
            white-space: nowrap;
            border: 1px solid rgba(0,0,0,0.02);
            box-shadow: 0 1px 3px rgba(15, 23, 42, 0.03);
            transition: all 0.2s ease;
        }
        .eta-chip:hover {
            transform: scale(1.05);
        }
        .eta-chip .eta-vnum {
            width: 16px;
            height: 16px;
            border-radius: 4px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 8.5px;
            font-weight: 900;
            color: white;
            flex-shrink: 0;
            box-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .eta-chip .eta-time {
            font-size: 10px;
            font-weight: 700;
            white-space: nowrap;
        }
        .eta-chip .eta-icon {
            font-size: 8px;
        }

        /* Status badge */
        .status-badge {
            font-size: 9px;
            padding: 2px 6px;
            border-radius: 999px;
            font-weight: 600;
            white-space: nowrap;
            letter-spacing: 0.02em;
        }

        /* ===== แบบประเมินความพึงพอใจ ===== */
        .survey-modal-overlay {
            position: fixed; inset: 0;
            background: rgba(15, 23, 42, 0.4);
            backdrop-filter: blur(8px);
            z-index: 200;
            display: none;
            align-items: center;
            justify-content: center;
            padding: 1rem;
        }
        .survey-modal-overlay.active { display: flex; }
        .survey-card {
            background: #fff;
            border-radius: 1.5rem;
            width: 100%;
            max-width: 540px;
            max-height: 90vh;
            overflow-y: auto;
            box-shadow: 0 25px 60px rgba(15, 23, 42, 0.15);
            animation: fadeInUp 0.35s ease-out forwards;
        }
        .survey-header {
            background: linear-gradient(135deg, #f472b6, #ec4899);
            padding: 1.2rem 1.5rem;
            border-radius: 1.5rem 1.5rem 0 0;
            color: #fff;
        }
        .star-group { display: flex; gap: 6px; flex-direction: row-reverse; justify-content: flex-end; }
        .star-group input { display: none; }
        .star-group label {
            font-size: 1.8rem;
            color: #d1d5db;
            cursor: pointer;
            transition: color 0.15s, transform 0.15s;
        }
        .star-group input:checked ~ label,
        .star-group label:hover,
        .star-group label:hover ~ label {
            color: #f59e0b;
            transform: scale(1.15);
        }
        .survey-submit-btn {
            background: linear-gradient(135deg, #f472b6, #ec4899);
            color: #fff;
            border: none;
            border-radius: 1rem;
            padding: 0.85rem 1.5rem;
            font-family: 'Kanit', sans-serif;
            font-size: 1rem;
            font-weight: 600;
            width: 100%;
            cursor: pointer;
            transition: filter 0.2s, transform 0.1s;
            box-shadow: 0 6px 20px rgba(236,72,153,0.3);
        }
        .survey-submit-btn:hover { filter: brightness(1.08); }
        .survey-submit-btn:active { transform: scale(0.97); }
        .survey-success {
            text-align: center;
            padding: 2.5rem 1.5rem;
            display: none;
        }
        .survey-success .check-circle {
            width: 72px; height: 72px;
            background: linear-gradient(135deg, #10b981, #059669);
            border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            margin: 0 auto 1rem;
            font-size: 2rem; color: #fff;
            animation: popIn 0.4s cubic-bezier(0.175, 0.885, 0.32, 1.275) forwards;
        }
        @keyframes popIn {
            from { transform: scale(0); opacity: 0; }
            to   { transform: scale(1); opacity: 1; }
        }

        /* Sidebar tab switching */
        .sidebar-tab {
            cursor: pointer;
            padding: 8px 12px;
            font-size: 12px;
            font-weight: 600;
            border-radius: 8px;
            transition: all 0.2s;
            white-space: nowrap;
        }
        .sidebar-tab.active {
            background: #ec4899;
            color: white;
            box-shadow: 0 2px 8px rgba(236,72,153,0.3);
        }
        .sidebar-tab:not(.active) {
            background: #f1f5f9;
            color: #64748b;
        }
        .sidebar-tab:not(.active):hover {
            background: #fce7f3;
            color: #ec4899;
        }
    </style>
</head>
<body class="bg-white flex flex-col h-screen overflow-hidden">

    <!-- Top Navbar -->
    <header class="bg-gradient-to-r from-pink-400 via-pink-500 to-pink-400 text-white py-4 px-6 flex justify-between items-center z-[110] relative shrink-0" style="box-shadow: 0 4px 30px rgba(236,72,153,0.25); border-bottom: 1.5px solid rgba(255,255,255,0.1);">
        <div class="flex items-center gap-4">
            <!-- Logo with glow ring -->
            <div class="relative flex-shrink-0">
                <div class="absolute inset-0 bg-white/20 rounded-full blur-md scale-110"></div>
                <img src="{{ asset('img/logo-yru.png') }}" alt="YRU Logo"
                     class="relative h-11 w-11 bg-white rounded-full object-contain p-1 shadow-lg ring-2 ring-white/60">
            </div>
            <!-- Divider -->
            <div class="hidden sm:block w-px h-8 bg-white/20 rounded-full"></div>
            <!-- Title block -->
            <div class="hidden sm:flex flex-col justify-center">
                <span class="font-extrabold text-white text-sm md:text-base leading-tight tracking-wide drop-shadow-sm">ระบบติดตามเส้นทางการเดินรถไฟฟ้า</span>
                <span class="font-medium text-white/80 text-xs md:text-sm leading-tight">มหาวิทยาลัยราชภัฏยะลา</span>
            </div>
            <span class="font-bold text-white text-base tracking-wide sm:hidden">YRU EV Tracker</span>
        </div>
        <!-- User Profile Dropdown -->
        <div class="relative inline-block text-left" id="profileDropdownContainer">
            @php
                $authUser = null;
                try {
                    if (class_exists('Illuminate\Support\Facades\Auth') && \Illuminate\Support\Facades\Auth::check()) {
                        $authUser = \Illuminate\Support\Facades\Auth::user();
                    }
                } catch (\Throwable $e) {
                    $authUser = null;
                }
                $authUsername = $authUser ? ($authUser->username ?? $authUser->email ?? 'ผู้ใช้งาน') : 'ผู้ใช้งานทั่วไป';
                $authInitials = mb_strtoupper(mb_substr($authUser ? ($authUser->username ?? $authUser->email ?? 'User') : 'GN', 0, 2, 'UTF-8'), 'UTF-8');
            @endphp
            <button type="button" onclick="toggleProfileDropdown()" class="flex items-center gap-2 focus:outline-none hover:bg-white/10 transition-all rounded-full px-3 py-1.5 border border-white/10">
                <div class="hidden sm:flex flex-col items-end mr-1">
                    <span class="text-white text-xs font-bold drop-shadow-sm">{{ $authUsername }}</span>
                </div>
                <!-- Avatar Circle -->
                <div class="w-8 h-8 rounded-full bg-white text-pink-500 flex items-center justify-center text-sm font-black shadow-md">
                    {{ $authInitials }}
                </div>
                <i class="fas fa-chevron-down text-white text-[10px] ml-1 transition-transform duration-200" id="profileDropdownIcon"></i>
            </button>

            <!-- Dropdown Menu -->
            <div id="profileDropdownMenu" class="absolute right-0 mt-2 w-56 rounded-xl shadow-lg bg-white ring-1 ring-black ring-opacity-5 divide-y divide-gray-100 hidden opacity-0 transform scale-95 transition-all duration-200 origin-top-right z-50">
                <div class="py-1">
                    <button onclick="openSurveyModal(); toggleProfileDropdown();" class="w-full group flex items-center px-4 py-2.5 text-sm text-gray-700 hover:bg-pink-50 hover:text-pink-600 transition-colors text-left">
                        <i class="fas fa-star w-5 text-center mr-3 text-gray-400 group-hover:text-yellow-400"></i> ประเมินความพึงพอใจ
                    </button>
                </div>
                <div class="py-1">
                    <a href="{{ url('/') }}" class="group flex items-center px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 transition-colors font-medium">
                        <i class="fas fa-sign-out-alt w-5 text-center mr-3"></i> ออกจากระบบ
                    </a>
                </div>
            </div>
        </div>
    </header>


    <!-- Modal เรียกรถ -->
    <div id="callModal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-[100] p-4 backdrop-blur-sm">
        <div class="bg-white w-full max-w-md rounded-3xl shadow-2xl overflow-hidden animate-fade-in-up">
            <div class="bg-pink-600 p-4 text-white flex justify-between items-center">
                <h3 class="font-bold"><i class="fas fa-paper-plane mr-2"></i>ส่งสัญญาณเรียก EV</h3>
                <button onclick="closeCallModal()" class="text-white/80 hover:text-white"><i class="fas fa-times"></i></button>
            </div>
            
            <div class="p-6 space-y-4">
                <div id="no-ev-warning" class="hidden bg-red-50 border border-red-200 text-red-700 p-3 rounded-xl text-xs font-semibold items-center gap-2">
                    <i class="fas fa-exclamation-circle text-red-500"></i>
                    <span>ขณะนี้ไม่มีรถไฟฟ้าให้บริการชั่วคราว (หยุดพัก/ขัดข้อง/สิ้นสุดรอบวิ่ง)</span>
                </div>
                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-2">ตำแหน่งของคุณ (จุดเริ่มต้น)</label>
                    <div class="relative">
                        <i class="fas fa-map-marker-alt absolute left-3 top-3 text-pink-500"></i>
                        <select id="callLocation" class="w-full pl-10 pr-4 py-2 bg-slate-100 border-none rounded-xl text-sm focus:ring-2 focus:ring-pink-500 outline-none disabled:opacity-50 disabled:cursor-not-allowed">
                            <option value="">-- เลือกจุดที่คุณอยู่ --</option>
                            <option value="1">ประตูหลังมอ.</option>
                            <option value="2">ตึกศิลปะ</option>
                            <option value="3">ศูนย์วิทยาศาสตร์</option>
                            <option value="4">คณะวิทยาศาสตร์</option>
                            <option value="5">คณะสังคมศาสตร์</option>
                            <option value="6">อาคารเรียน20</option>
                            <option value="7">คณะวิทยาการจัดการ</option>
                        </select>   
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-2">ตำแหน่งปลายทาง</label>
                    <div class="relative">
                        <i class="fas fa-location-arrow absolute left-3 top-3 text-pink-500"></i>
                        <select id="callDestination" class="w-full pl-10 pr-4 py-2 bg-slate-100 border-none rounded-xl text-sm focus:ring-2 focus:ring-pink-500 outline-none disabled:opacity-50 disabled:cursor-not-allowed">
                            <option value="">-- เลือกจุดปลายทาง --</option>
                            <option value="1">ประตูหลังมอ.</option>
                            <option value="2">ตึกศิลปะ</option>
                            <option value="3">ศูนย์วิทยาศาสตร์</option>
                            <option value="4">คณะวิทยาศาสตร์</option>
                            <option value="5">คณะสังคมศาสตร์</option>
                            <option value="6">อาคารเรียน20</option>
                            <option value="7">คณะวิทยาการจัดการ</option>
                        </select>
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-slate-600 mb-2">จำนวนผู้โดยสาร</label>
                    <div class="flex items-center gap-4 bg-slate-100 p-2 rounded-xl justify-between">
                        <button onclick="updatePax(-1)" class="w-10 h-10 bg-white rounded-lg shadow-sm text-pink-600 font-bold">-</button>
                        <div class="text-center">
                            <span id="paxCount" class="text-xl font-bold text-slate-700">1</span>
                            <span class="text-xs text-slate-400 block">คน</span>
                        </div>
                        <button onclick="updatePax(1)" class="w-10 h-10 bg-white rounded-lg shadow-sm text-pink-600 font-bold">+</button>
                    </div>
                </div>

                <button id="btnSubmitCall" onclick="submitCall()" class="w-full bg-pink-600 hover:bg-pink-700 text-white font-bold py-3 rounded-2xl shadow-lg shadow-pink-200 transition-all active:scale-95 disabled:bg-slate-300 disabled:shadow-none disabled:cursor-not-allowed">
                    ยืนยันการเรียก
                </button>
            </div>
        </div>
    </div>

    <!-- แบบประเมินความพึงพอใจ Modal -->
    <div id="surveyModal" class="survey-modal-overlay">
        <div class="survey-card">
            <!-- Header -->
            <div class="survey-header">
                <div class="flex justify-between items-center">
                    <div>
                        <h3 class="font-bold text-lg leading-tight">📋 แบบประเมินความพึงพอใจ</h3>
                        <p class="text-pink-200 text-xs mt-0.5">การให้บริการรถไฟฟ้า YRU EV</p>
                    </div>
                    <button onclick="closeSurveyModal()" class="text-white/70 hover:text-white text-xl leading-none">&times;</button>
                </div>
            </div>

            <!-- แบบฟอร์ม -->
            <div id="surveyFormSection" class="p-5 space-y-5">
                
                <!-- ส่วนแสดงผลและเลือกข้อมูลคนขับเพื่อยืนยันตัวตน -->
                <div class="bg-gradient-to-r from-pink-50 to-rose-50 border border-pink-100 rounded-2xl p-4 flex flex-col gap-3">
                    <div class="flex items-center gap-4">
                        <img id="surveyDriverAvatar" src="https://ui-avatars.com/api/?name=Driver&background=ec4899&color=fff&size=128" class="w-16 h-16 rounded-full border-2 border-white object-cover shadow-md" alt="Driver Avatar">
                        <div class="flex-1 text-left">
                            <span class="text-[10px] bg-pink-100 text-pink-600 font-bold px-2.5 py-0.5 rounded-full uppercase tracking-wider">พนักงานขับรถ</span>
                            <h4 id="surveyDriverName" class="text-base font-bold text-slate-800 mt-1">กรุณาเลือกคนขับ</h4>
                            <p id="surveyDriverRole" class="text-xs text-slate-500 font-light mt-0.5">ไม่ได้เลือกพนักงานขับรถ</p>
                        </div>
                    </div>
                    
                    <div class="border-t border-pink-100 pt-2">
                        <label for="surveyDriverSelect" class="block text-[11px] font-semibold text-slate-500 mb-1">กรุณาเลือกหรือยืนยันพนักงานขับรถ:</label>
                        <select id="surveyDriverSelect" onchange="updateSurveyDriverCard(this.value)" class="w-full px-3 py-2 bg-white border border-pink-200 rounded-xl text-xs outline-none focus:ring-2 focus:ring-pink-400 text-slate-700">
                            <option value="">-- กรุณาเลือกคนขับ --</option>
                        </select>
                    </div>
                </div>

                <!-- ข้อ 1 -->
                <div class="bg-pink-50 rounded-xl p-4">
                    <p class="text-sm font-semibold text-slate-700 mb-2">1. ความสุภาพ กริยา มารยาทของเจ้าหน้าที่</p>
                    <div class="star-group" id="q1">
                        <input type="radio" name="q1" id="q1s5" value="5"><label for="q1s5" title="ดีมาก">★</label>
                        <input type="radio" name="q1" id="q1s4" value="4"><label for="q1s4" title="ดี">★</label>
                        <input type="radio" name="q1" id="q1s3" value="3"><label for="q1s3" title="ปานกลาง">★</label>
                        <input type="radio" name="q1" id="q1s2" value="2"><label for="q1s2" title="พอใช้">★</label>
                        <input type="radio" name="q1" id="q1s1" value="1"><label for="q1s1" title="ควรปรับปรุง">★</label>
                    </div>
                </div>

                <!-- ข้อ 2 -->
                <div class="bg-pink-50 rounded-xl p-4">
                    <p class="text-sm font-semibold text-slate-700 mb-2">2. เจ้าหน้าที่แต่งกายสุภาพ เรียบร้อย เหมาะสมตามลักษณะหน้าที่</p>
                    <div class="star-group" id="q2">
                        <input type="radio" name="q2" id="q2s5" value="5"><label for="q2s5">★</label>
                        <input type="radio" name="q2" id="q2s4" value="4"><label for="q2s4">★</label>
                        <input type="radio" name="q2" id="q2s3" value="3"><label for="q2s3">★</label>
                        <input type="radio" name="q2" id="q2s2" value="2"><label for="q2s2">★</label>
                        <input type="radio" name="q2" id="q2s1" value="1"><label for="q2s1">★</label>
                    </div>
                </div>

                <!-- ข้อ 3 -->
                <div class="bg-pink-50 rounded-xl p-4">
                    <p class="text-sm font-semibold text-slate-700 mb-2">3. ความใส่ใจ กระตือรือร้น มีความเต็มใจ และความพร้อมในการบริการของเจ้าหน้าที่</p>
                    <div class="star-group" id="q3">
                        <input type="radio" name="q3" id="q3s5" value="5"><label for="q3s5">★</label>
                        <input type="radio" name="q3" id="q3s4" value="4"><label for="q3s4">★</label>
                        <input type="radio" name="q3" id="q3s3" value="3"><label for="q3s3">★</label>
                        <input type="radio" name="q3" id="q3s2" value="2"><label for="q3s2">★</label>
                        <input type="radio" name="q3" id="q3s1" value="1"><label for="q3s1">★</label>
                    </div>
                </div>

                <!-- ข้อ 4 -->
                <div class="bg-pink-50 rounded-xl p-4">
                    <p class="text-sm font-semibold text-slate-700 mb-2">4. เจ้าหน้าที่ให้บริการต่อผู้รับบริการเหมือนกันทุกราย โดยไม่เลือกปฏิบัติ</p>
                    <div class="star-group" id="q4">
                        <input type="radio" name="q4" id="q4s5" value="5"><label for="q4s5">★</label>
                        <input type="radio" name="q4" id="q4s4" value="4"><label for="q4s4">★</label>
                        <input type="radio" name="q4" id="q4s3" value="3"><label for="q4s3">★</label>
                        <input type="radio" name="q4" id="q4s2" value="2"><label for="q4s2">★</label>
                        <input type="radio" name="q4" id="q4s1" value="1"><label for="q4s1">★</label>
                    </div>
                </div>

                <!-- ข้อ 5 -->
                <div class="bg-pink-50 rounded-xl p-4">
                    <p class="text-sm font-semibold text-slate-700 mb-2">5. เจ้าหน้าที่ตอบข้อซักถามอย่างชัดเจน เกี่ยวกับเรื่องการให้บริการ</p>
                    <div class="star-group" id="q5">
                        <input type="radio" name="q5" id="q5s5" value="5"><label for="q5s5">★</label>
                        <input type="radio" name="q5" id="q5s4" value="4"><label for="q5s4">★</label>
                        <input type="radio" name="q5" id="q5s3" value="3"><label for="q5s3">★</label>
                        <input type="radio" name="q5" id="q5s2" value="2"><label for="q5s2">★</label>
                        <input type="radio" name="q5" id="q5s1" value="1"><label for="q5s1">★</label>
                    </div>
                </div>

                <!-- ความคิดเห็นและข้อเสนอแนะ -->
                <div>
                    <label class="block text-sm font-semibold text-slate-700 mb-2">
                        <i class="fas fa-comment-dots text-pink-500 mr-1"></i>
                        ความคิดเห็นและข้อเสนอแนะเพื่อปรับปรุงการให้บริการ
                    </label>
                    <textarea id="surveyComment" rows="4"
                        class="w-full px-4 py-3 bg-slate-50 border border-pink-200 rounded-xl text-sm outline-none focus:ring-2 focus:ring-pink-400 resize-none transition-all"
                        placeholder="กรุณาแสดงความคิดเห็น..."></textarea>
                </div>

                <button class="survey-submit-btn" onclick="submitSurvey()">
                    <i class="fas fa-paper-plane mr-2"></i>ส่งแบบประเมิน
                </button>
            </div>

            <!-- หน้าขอบคุณ -->
            <div id="surveySuccess" class="survey-success">
                <div class="check-circle"><i class="fas fa-check"></i></div>
                <h3 class="text-xl font-bold text-slate-700 mb-1">ขอบคุณครับ</h3>
                <p class="text-slate-500 text-sm">ความคิดเห็นของท่านจะช่วยพัฒนาการบริการของเราให้ดียิ่งขึ้น</p>
            </div>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="flex-1 grid grid-cols-1 md:grid-cols-12 overflow-hidden min-h-0 p-4 bg-pink-50/30 gap-4">
        <!-- Sidebar ซ้าย: Compact Vehicle Strip + Station Rows -->
        <aside class="md:col-span-4 bg-white rounded-[2rem] shadow-[0_8px_30px_rgb(0,0,0,0.02)] flex flex-col h-[40vh] md:h-auto overflow-hidden relative z-50">
            <!-- Header section -->
            <div class="flex-shrink-0 z-10">

                <!-- Station list header -->
                <div class="px-5 py-2.5 bg-slate-100/40 border-b border-slate-100 flex items-center justify-between">
                    <span class="text-[11px] font-bold text-slate-500 flex items-center gap-1.5">
                        <i class="fas fa-map-pin text-pink-500 text-[10px]"></i>
                        จุดจอด
                    </span>
                    <span class="text-[9.5px] text-slate-400">แสดง 3 คันแรก (กดปุ่ม + เพื่อดูทั้งหมด)</span>
                </div>
            </div>

            <!-- Station Rows (compact) -->
            <div class="flex-1 overflow-y-auto custom-scrollbar px-4 py-3.5 relative bg-slate-50/10" id="stationListContainer">
                <!-- Rendered by JS -->
            </div>
        </aside>

        <!-- แผนที่หลัก -->
        <main class="md:col-span-8 relative flex flex-col gap-4 overflow-hidden" style="min-height:0;">
            <div id="map" class="map-bg flex-1 relative z-0 rounded-[2rem] border-2 border-pink-200 shadow-[0_8px_30px_rgb(0,0,0,0.02)] overflow-hidden" style="min-height:0;">

                <!-- แผนที่ Legend (bottom-left) - Clean Glassmorphic -->
                <div class="absolute bottom-5 left-5 backdrop-blur-lg bg-white/90 px-4 py-3.5 rounded-[1.25rem] shadow-xl border border-pink-100 z-[1000]" id="mapLegendWrapper">
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

            <!-- Bottom bar -->
            <div class="px-6 py-4.5 bg-white rounded-[2rem] border-2 border-pink-200 relative z-50 shadow-[0_8px_30px_rgb(0,0,0,0.02)] flex-shrink-0">
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="flex items-center gap-3">
                        <div class="bg-pink-50 text-pink-500 p-3.5 rounded-2xl border border-pink-100 pulse-container"><i class="fas fa-route text-lg"></i></div>
                        <div id="statusPassengerDisplay">
                            <p class="text-[10px] text-slate-400 font-semibold">สถานะการให้บริการ</p>
                            <p class="text-sm font-bold text-slate-700 flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse inline-block"></span>
                                ยังไม่มีรายการเรียกรถในขณะนี้ <span class="text-xs text-slate-500 font-normal hidden md:inline">(กดปุ่ม "เรียกรถที่นี่" เพื่อเริ่มเรียกรถไฟฟ้า)</span>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                        <button onclick="openCallModal()" class="bg-emerald-500 hover:bg-emerald-600 text-white px-8 py-3.5 rounded-2xl font-bold flex items-center gap-2.5 shadow-lg shadow-emerald-500/20 transition-all active:scale-95">
                            <i class="fas fa-hand-paper text-sm"></i>
                            <span>เรียกรถที่นี่</span>
                        </button>
                    </div>
                </div>
            </div>
        </main>
    </div>


<script>
    // ===== 📍 Garage / Depot & Station Constants (Global Scope) =====
    var GARAGE_LAT = 6.548900;
    var GARAGE_LNG = 101.291700;
    var GARAGE_STATION_ID = "GARAGE";
    var stationData = [];

    function isVehicleInGarage(tram) {
        if (!tram) return false;
        var carData = (typeof globalCarStatus !== 'undefined' && globalCarStatus[tram.id]) ? globalCarStatus[tram.id] : {};
        var rawStatus = (tram.status || carData.status || '').toString();
        
        var isMaintenanceOrSuspended = 
            rawStatus.includes('ขัดข้อง') || 
            rawStatus.includes('ปรับปรุง') || 
            rawStatus.includes('ระงับ') || 
            rawStatus === 'MAINTENANCE' || 
            rawStatus === 'SUSPENDED';

        if (isMaintenanceOrSuspended) return true;

        var baseIds = ["EV-01","EV-02","EV-03","EV-04","EV-05","EV-06","EV-07","EV-08","EV-09","EV-10"];
        var isBase = baseIds.includes(tram.id);

        if (isBase && (rawStatus === 'พร้อมใช้งาน' || rawStatus === 'ปกติกำลังขับ' || rawStatus === 'ACTIVE' || rawStatus === 'normal' || rawStatus === 'pause' || rawStatus === 'พักเบรค' || rawStatus === 'พักเบรก' || rawStatus.includes('พัก'))) {
            return false;
        }

        var stationId = (tram.current_station_id || '').toString();
        var isAtGarage = (stationId === 'GARAGE' || stationId === 'GARAGE_STATION');
        return isAtGarage;
    }

    // ===== 🎨 Vehicle Color Palette — 10 สีสำหรับรถแต่ละคัน =====
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

    // ===== Load Trams from LocalStorage =====
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
            updated_at: "12 มี.ค. 2568",
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
            updated_at: "12 มี.ค. 2568",
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
            updated_at: "12 มี.ค. 2568",
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
            updated_at: "12 มี.ค. 2568",
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
            updated_at: "12 มี.ค. 2568",
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
            updated_at: "12 มี.ค. 2568",
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
            updated_at: "12 มี.ค. 2568",
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
            updated_at: "12 มี.ค. 2568",
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
            updated_at: "12 มี.ค. 2568",
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
            updated_at: "12 มี.ค. 2568",
            purchase_date: "2025-05-10",
            warranty: "3 ปี (สิ้นสุด 10 พฤษภาคม 2571)",
            supplier: "บริษัท ยะลา อีวี กรุ๊ป จำกัด",
            maintenance: []
        }
    ];

    function getStorage(key, defaultData) {
        if (key === "yru_trams_v15" || key === "yru_trams_v16") {
            const v16 = localStorage.getItem("yru_trams_v16");
            if (v16) return JSON.parse(v16);
            const v15 = localStorage.getItem("yru_trams_v15");
            if (v15) return JSON.parse(v15);
        }
        if (!localStorage.getItem(key)) localStorage.setItem(key, JSON.stringify(defaultData));
        return JSON.parse(localStorage.getItem(key));
    }


    let trams = getStorage("yru_trams_v16", defaultTrams);
    // รถ EV-01..EV-10 คือรถหลัก รถที่แอดมินเพิ่มใหม่ (EV-11+) จะถูกส่งไปอยู่ Garage อัตโนมัติ
    const baseVehicleIds = ["EV-01","EV-02","EV-03","EV-04","EV-05","EV-06","EV-07","EV-08","EV-09","EV-10"];
    // บังคับอัปเดตข้อมูลถ้ารถหลักมีไม่ถึง 10 คัน
    const baseTrams = trams.filter(t => baseVehicleIds.includes(t.id));
    if (!baseTrams || baseTrams.length < 10) {
        // เติมรถหลักที่หายไปจาก defaultTrams
        const existingIds = trams.map(t => t.id);
        defaultTrams.forEach(dt => {
            if (!existingIds.includes(dt.id)) trams.push(dt);
        });
    }
    const defaultTramCoords = {
        "EV-01": "6.549929, 101.291254",
        "EV-02": "6.549100, 101.290467",
        "EV-03": "6.547835, 101.289502",
        "EV-04": "6.547224, 101.289471",
        "EV-05": "6.547311, 101.288880",
        "EV-06": "6.548822, 101.288523",
        "EV-07": "6.549225, 101.289286",
        "EV-08": "6.549929, 101.291254",
        "EV-09": "6.547835, 101.289502",
        "EV-10": "6.547311, 101.288880"
    };
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
            // รถหลัก EV-01..EV-10: assign พิกัดประจำจุดจอดเพื่อให้วิ่งบนแผนที่
            t.current_station_id = null;
            if (!t.coords || t.coords === GARAGE_COORDS || t.coords === "6.548900, 101.291700") {
                t.coords = defaultTramCoords[t.id] || GARAGE_COORDS;
            }
        }
    });
    localStorage.setItem("yru_trams_v18", JSON.stringify(trams));

    let lastHandledStatus = null;
    
    let globalCarStatus = {};
    trams.forEach((tram, idx) => {
        let maxOcc = (tram.capacity_sit !== undefined && !isNaN(parseInt(tram.capacity_sit))) ? parseInt(tram.capacity_sit) : 10;
        globalCarStatus[tram.id] = {
            status: tram.status || 'พร้อมใช้งาน',
            occupied: 0,
            max: maxOcc
        };
    });

    function getCarOccupiedInfo(tramId) {
        let baseOccupied = 0;
        let maxCap = 10;

        if (typeof trams !== 'undefined' && Array.isArray(trams)) {
            const tramIndex = trams.findIndex(t => t.id === tramId);
            if (tramIndex >= 0) {
                const tramObj = trams[tramIndex];
                if (tramObj && tramObj.capacity_sit !== undefined && !isNaN(parseInt(tramObj.capacity_sit))) {
                    maxCap = parseInt(tramObj.capacity_sit) || 10;
                }
            }
        }

        try {
            const rawStatus = localStorage.getItem('yru_car_status_' + tramId);
            if (rawStatus) {
                const parsed = JSON.parse(rawStatus);
                if (parsed && parsed.occupied !== undefined && !isNaN(parseInt(parsed.occupied))) {
                    baseOccupied = parseInt(parsed.occupied);
                }
                if (parsed && parsed.capacity !== undefined && !isNaN(parseInt(parsed.capacity))) {
                    maxCap = parseInt(parsed.capacity);
                }
            } else {
                const rawSeats = localStorage.getItem('yru_seats_' + tramId);
                if (rawSeats && !isNaN(parseInt(rawSeats))) {
                    baseOccupied = parseInt(rawSeats);
                } else if (typeof globalCarStatus !== 'undefined' && globalCarStatus[tramId]) {
                    baseOccupied = parseInt(globalCarStatus[tramId].occupied) || 0;
                }
            }
        } catch(e) {}

        baseOccupied = Math.max(0, baseOccupied);
        maxCap = Math.max(1, maxCap);

        const isFull = baseOccupied >= maxCap;

        return { 
            occupied: Math.min(maxCap, baseOccupied), 
            rawOccupied: baseOccupied,
            max: maxCap,
            isFull: isFull
        };
    }

    function isVehicleFull(tram) {
        if (!tram || !tram.id) return false;
        const occInfo = getCarOccupiedInfo(tram.id);
        return occInfo.isFull;
    }

    let garageClusterMarker = null;

    // ===== Default fallback Station data with ETA per vehicle =====
    const defaultStationsList = [
        { id: 1, name: "ประตูหลังมอ.", type: "P",
          etas: [
            { vehicleIdx: 0, time: "Now" },
            { vehicleIdx: 7, time: "Now" },
            { vehicleIdx: 6, time: "5 นาที" },
            { vehicleIdx: 5, time: "10 นาที" },
            { vehicleIdx: 4, time: "15 นาที" },
            { vehicleIdx: 9, time: "15 นาที" },
            { vehicleIdx: 3, time: "20 นาที" },
            { vehicleIdx: 2, time: "25 นาที" },
            { vehicleIdx: 8, time: "25 นาที" },
            { vehicleIdx: 1, time: "30 นาที" }
          ]
        },
        { id: 2, name: "ตึกศิลปะ", type: "P",
          etas: [
            { vehicleIdx: 1, time: "Now" },
            { vehicleIdx: 0, time: "5 นาที" },
            { vehicleIdx: 7, time: "5 นาที" },
            { vehicleIdx: 6, time: "10 นาที" },
            { vehicleIdx: 5, time: "15 นาที" },
            { vehicleIdx: 4, time: "20 นาที" },
            { vehicleIdx: 9, time: "20 นาที" },
            { vehicleIdx: 3, time: "25 นาที" },
            { vehicleIdx: 2, time: "30 นาที" },
            { vehicleIdx: 8, time: "30 นาที" }
          ]
        },
        { id: 3, name: "ศูนย์วิทยาศาสตร์", type: "P",
          etas: [
            { vehicleIdx: 2, time: "Now" },
            { vehicleIdx: 8, time: "Now" },
            { vehicleIdx: 1, time: "5 นาที" },
            { vehicleIdx: 0, time: "10 นาที" },
            { vehicleIdx: 7, time: "10 นาที" },
            { vehicleIdx: 6, time: "15 นาที" },
            { vehicleIdx: 5, time: "20 นาที" },
            { vehicleIdx: 4, time: "25 นาที" },
            { vehicleIdx: 9, time: "25 นาที" },
            { vehicleIdx: 3, time: "30 นาที" }
          ]
        },
        { id: 4, name: "คณะวิทยาศาสตร์", type: "P",
          etas: [
            { vehicleIdx: 3, time: "Now" },
            { vehicleIdx: 2, time: "5 นาที" },
            { vehicleIdx: 8, time: "5 นาที" },
            { vehicleIdx: 1, time: "10 นาที" },
            { vehicleIdx: 0, time: "15 นาที" },
            { vehicleIdx: 7, time: "15 นาที" },
            { vehicleIdx: 6, time: "20 นาที" },
            { vehicleIdx: 5, time: "25 นาที" },
            { vehicleIdx: 4, time: "30 นาที" },
            { vehicleIdx: 9, time: "30 นาที" }
          ]
        },
        { id: 5, name: "คณะสังคมศาสตร์", type: "P",
          etas: [
            { vehicleIdx: 4, time: "Now" },
            { vehicleIdx: 9, time: "Now" },
            { vehicleIdx: 3, time: "5 นาที" },
            { vehicleIdx: 2, time: "10 นาที" },
            { vehicleIdx: 8, time: "10 นาที" },
            { vehicleIdx: 1, time: "15 นาที" },
            { vehicleIdx: 0, time: "20 นาที" },
            { vehicleIdx: 7, time: "20 นาที" },
            { vehicleIdx: 6, time: "25 นาที" },
            { vehicleIdx: 5, time: "30 นาที" }
          ]
        },
        { id: 6, name: "อาคารเรียน20", type: "P",
          etas: [
            { vehicleIdx: 5, time: "Now" },
            { vehicleIdx: 4, time: "5 นาที" },
            { vehicleIdx: 9, time: "5 นาที" },
            { vehicleIdx: 3, time: "10 นาที" },
            { vehicleIdx: 2, time: "15 นาที" },
            { vehicleIdx: 8, time: "15 นาที" },
            { vehicleIdx: 1, time: "20 นาที" },
            { vehicleIdx: 0, time: "25 นาที" },
            { vehicleIdx: 7, time: "25 นาที" },
            { vehicleIdx: 6, time: "30 นาที" }
          ]
        },
        { id: 7, name: "คณะวิทยาการจัดการ", type: "P",
          etas: [
            { vehicleIdx: 6, time: "Now" },
            { vehicleIdx: 5, time: "5 นาที" },
            { vehicleIdx: 4, time: "10 นาที" },
            { vehicleIdx: 9, time: "10 นาที" },
            { vehicleIdx: 3, time: "15 นาที" },
            { vehicleIdx: 2, time: "20 นาที" },
            { vehicleIdx: 8, time: "20 นาที" },
            { vehicleIdx: 1, time: "25 นาที" },
            { vehicleIdx: 0, time: "30 นาที" },
            { vehicleIdx: 7, time: "30 นาที" }
          ]
        }
    ];

    let stations = [];

    function loadDynamicStations() {
        const localStops = JSON.parse(localStorage.getItem("yru_stops_v2") || "[]");
        if (localStops.length === 0) {
            stations = defaultStationsList;
            return;
        }

        stations = localStops.map((stop, index) => {
            const seq = stop.sequence || (index + 1);
            const etas = [];
            for (let vIdx = 0; vIdx < 10; vIdx++) {
                let timeStr = "";
                if (vIdx === (seq - 1) % 10) {
                    timeStr = "Now";
                } else {
                    const diff = Math.abs(vIdx - ((seq - 1) % 10));
                    timeStr = `${diff * 5} นาที`;
                }
                etas.push({ vehicleIdx: vIdx, time: timeStr });
            }
            return {
                id: seq,
                name: stop.name,
                type: "P",
                etas: etas
            };
        });
    }

    loadDynamicStations();

    function getEtaTextForStation(stationName, preferredCarId = null) {
        const station = stations.find(s => s.name === stationName || s.name.includes(stationName) || stationName.includes(s.name));
        if (!station) return `⏱️ กำลังตรวจสอบเวลา...`;

        let nearestEta = null;
        if (preferredCarId) {
            const tramIdx = trams.findIndex(t => t.id === preferredCarId);
            if (tramIdx !== -1) {
                const tram = trams[tramIdx];
                if (!isVehicleInactive(tram)) {
                    nearestEta = station.etas.find(e => e.vehicleIdx === tramIdx);
                }
            }
        }
        
        if (!nearestEta) {
            const activeEtas = getNearestETAs(station, 1);
            if (activeEtas.length > 0) {
                nearestEta = activeEtas[0];
            } else {
                // Fallback to closest available ETA for non-inactive cars
                const validEtas = station.etas.filter(e => {
                    if (e.time === '-') return false;
                    const tram = trams[e.vehicleIdx];
                    return tram && !isVehicleInactive(tram);
                });
                validEtas.sort((a, b) => parseEtaMinutes(a.time) - parseEtaMinutes(b.time));
                if (validEtas.length > 0) nearestEta = validEtas[0];
            }
        }

        if (nearestEta) {
            const tram = trams[nearestEta.vehicleIdx];
            const etaTime = nearestEta.time;
            if (etaTime === 'Now') {
                return `⚡ รถไฟฟ้า <span class="text-green-600 font-bold">${tram.id} ถึงจุดจอดของคุณแล้วขณะนี้!</span>`;
            } else {
                return `⏱️ คาดว่ารถไฟฟ้า <span class="text-pink-600 font-bold">${tram.id} จะมาถึงในอีก ${etaTime}</span>`;
            }
        }
        return `⏱️ กำลังตรวจสอบเวลา...`;
    }

    // ===== Call Shuttle form validation and submission =====
    function callShuttle() {
        // ... (callShuttle implementation using getEtaTextForStation) ...
    }

    // ===== Helper: parse ETA to minutes for sorting =====
    function parseEtaMinutes(etaStr) {
        if (etaStr === 'Now') return 0;
        if (etaStr === '-') return 9999;
        const m = etaStr.match(/(\d+)/);
        return m ? parseInt(m[1]) : 9999;
    }

    // ===== Helper: Check if a vehicle is inactive/suspended =====
    function isVehicleInactive(tram) {
        if (!tram) return true;
        const carData = globalCarStatus[tram.id] || {};
        const driverStatus = (carData.status || '').toString().trim();
        const mainStatus = (tram.status || '').toString().trim();
        const checkStatus = (driverStatus && driverStatus !== 'ปกติกำลังขับ') ? driverStatus : mainStatus;

        // Inactive terms DO NOT include break/pause so break vehicles stay visible on route
        const inactiveTerms = [
            'ขัดข้อง', 'ปรับปรุง', 'ระงับ', 'สิ้นสุด', 'ไม่พร้อม', 'ยกเลิก', 'ซ่อม',
            'MAINTENANCE', 'SUSPENDED', 'INACTIVE', 'ENDED', 'DISABLED', 'REPAIR'
        ];

        const matchInactive = inactiveTerms.some(term => 
            checkStatus.toLowerCase().includes(term.toLowerCase())
        );

        if (matchInactive) return true;
        if (typeof isVehicleInGarage === 'function' && isVehicleInGarage(tram)) return true;

        return false;
    }

    function isVehicleOnBreak(tram) {
        if (!tram) return false;
        const carData = globalCarStatus[tram.id] || {};
        const driverStatus = (carData.status || '').toString().trim();
        const mainStatus = (tram.status || '').toString().trim();
        let storedStatus = '';
        try {
            const rawStored = localStorage.getItem('yru_car_status_' + tram.id);
            if (rawStored) {
                const parsed = JSON.parse(rawStored);
                storedStatus = (parsed.status || '').toString().trim();
            }
        } catch(e) {}

        return (
            driverStatus === 'pause' || driverStatus === 'พักเบรค' || driverStatus === 'พักเบรก' || driverStatus.includes('พัก') ||
            mainStatus === 'pause' || mainStatus === 'พักเบรค' || mainStatus === 'พักเบรก' || mainStatus.includes('พัก') ||
            storedStatus === 'pause' || storedStatus === 'พักเบรค' || storedStatus === 'พักเบรก' || storedStatus.includes('พัก')
        );
    }

    // ===== Get the nearest active vehicles for a station (filters out suspended/inactive cars) =====
    // Vehicles physically AT the station are marked time='Now' and sorted first.
    function getNearestETAs(station, maxCount = 10) {
        // Find the station's GPS coordinates from stationData
        const stObj = stationData.find(st => String(st.id) === String(station.id) || st.name === station.name || st.name.includes(station.name));

        const activeEtas = station.etas
            .filter(e => {
                if (e.time === '-') return false;
                const tram = trams[e.vehicleIdx];
                if (!tram) return false;
                if (isVehicleInactive(tram)) return false;
                return true;
            })
            .map(e => {
                const tram = trams[e.vehicleIdx];
                const onBreak = isVehicleOnBreak(tram);
                let isPhysicallyAtStation = (e.time === 'Now');

                // Check GPS proximity: if tram coords are within ~44m of station, mark as 'Now'
                if (!isPhysicallyAtStation && stObj && tram && tram.coords) {
                    const parts = tram.coords.split(',').map(Number);
                    if (parts.length === 2 && !isNaN(parts[0]) && !isNaN(parts[1])) {
                        const dist = Math.hypot(parts[0] - stObj.lat, parts[1] - stObj.lng);
                        if (dist < 0.0004) {
                            isPhysicallyAtStation = true;
                        }
                    }
                }

                return { 
                    ...e, 
                    time: onBreak ? 'พักเบรก' : (isPhysicallyAtStation ? 'Now' : e.time),
                    isOnBreak: onBreak
                };
            });

        // Sort: Active running 'Now' first, approaching ETAs, then vehicles on break
        activeEtas.sort((a, b) => {
            if (a.isOnBreak && !b.isOnBreak) return 1;
            if (!a.isOnBreak && b.isOnBreak) return -1;
            const aMin = parseEtaMinutes(a.time);
            const bMin = parseEtaMinutes(b.time);
            return aMin - bMin;
        });

        return activeEtas.slice(0, maxCount);
    }

    // ===== Helper: Find best available active (non-broken, non-garage) vehicle nearest to pickup station =====
    function getBestAvailableVehicle(pickupStationId) {
        trams = getStorage("yru_trams_v18", defaultTrams);

        // Find station object from stationData, defaultStationData, or stations
        const stList = (stationData && stationData.length > 0) ? stationData : defaultStationData;
        const pickupStObj = stList.find(st => 
            String(st.id) === String(pickupStationId) || 
            st.name === pickupStationId || 
            st.name.includes(pickupStationId) || 
            (pickupStationId && pickupStationId.includes(st.name))
        ) || defaultStationData.find(st => 
            String(st.id) === String(pickupStationId) || 
            st.name === pickupStationId || 
            st.name.includes(pickupStationId) || 
            (pickupStationId && pickupStationId.includes(st.name))
        );

        // Filter out all broken, maintenance, suspended, or garage vehicles
        const activeTrams = trams.filter(t => !isVehicleInGarage(t));
        
        if (activeTrams.length === 0) {
            return null; // All trams are broken, maintenance, or in garage
        }

        // Filter out vehicles that are FULL (occupied >= max)
        const availableTrams = activeTrams.filter(t => !isVehicleFull(t));
        const tramsToConsider = availableTrams.length > 0 ? availableTrams : activeTrams;

        let bestTram = null;
        let minDistance = Infinity;

        // 1. Check candidate vehicles for distance or station match
        tramsToConsider.forEach(tram => {
            // Check if tram is assigned to this station ID/name
            if (tram.current_station_id && pickupStObj && 
               (String(tram.current_station_id) === String(pickupStObj.id) || tram.current_station_id === pickupStObj.name)) {
                minDistance = 0;
                bestTram = tram;
            }

            // Check coordinate distance to pickup station if coordinates available
            if (pickupStObj && tram.coords) {
                const parts = tram.coords.split(',').map(Number);
                if (parts.length === 2 && !isNaN(parts[0]) && !isNaN(parts[1])) {
                    const dist = Math.hypot(parts[0] - pickupStObj.lat, parts[1] - pickupStObj.lng);
                    if (dist < minDistance) {
                        minDistance = dist;
                        bestTram = tram;
                    }
                }
            }
        });

        // 2. Check ETA list for the station among active ETAs
        if (!bestTram && pickupStObj) {
            const stInList = stations.find(s => 
                String(s.id) === String(pickupStObj.id) || 
                s.name === pickupStObj.name || 
                s.name.includes(pickupStObj.name) || 
                pickupStObj.name.includes(s.name)
            );
            if (stInList) {
                const nearestArr = getNearestETAs(stInList, 10);
                if (nearestArr && nearestArr.length > 0) {
                    const tram = trams[nearestArr[0].vehicleIdx];
                    if (tram && !isVehicleInactive(tram)) {
                        bestTram = tram;
                    }
                }
            }
        }

        // 3. Fallback to first available active vehicle if distance matching yielded nothing
        if (!bestTram) {
            bestTram = activeTrams[0];
        }

        return bestTram;
    }

    // ===== Render Vehicle Pills (compact strip) =====
    function renderVehicleCards() {
        const container = document.getElementById('vehicleCardsContainer');
        if (!container) return;

        let activeCount = 0;

        container.innerHTML = trams.map((tram, idx) => {
            const color = getVehicleColor(idx);
            const carData = globalCarStatus[tram.id] || {};
            const rawStatus = (carData.status || tram.status || 'พร้อมใช้งาน').toString().trim();
            const occInfo = getCarOccupiedInfo(tram.id);
            const inactive = isVehicleInactive(tram);

            let statusColor = '#22c55e'; // green = running
            let statusText = 'พร้อมใช้งาน';

            if (rawStatus.includes('พัก') || rawStatus === 'pause') {
                statusColor = '#f59e0b';
                statusText = 'พักเบรค';
            } else if (rawStatus.includes('ขัดข้อง') || rawStatus.includes('ปรับปรุง') || rawStatus.includes('ระงับ') || rawStatus === 'SUSPENDED' || rawStatus === 'MAINTENANCE') {
                statusColor = '#ef4444';
                statusText = rawStatus;
            } else if (rawStatus === 'ended' || rawStatus === 'สิ้นสุด' || rawStatus === 'เลิกงาน') {
                statusColor = '#94a3b8';
                statusText = 'เลิกงาน/สิ้นสุดรอบ';
            } else {
                statusColor = '#22c55e';
                activeCount++;
                statusText = 'พร้อมใช้งาน';
            }

            return `
                <div class="vehicle-pill"
                     style="border-color: ${color.border}; ${(inactive && tram.status !== 'พร้อมใช้งาน' && tram.status !== 'ACTIVE') ? 'opacity: 0.55; filter: grayscale(30%);' : ''}"
                     onclick="focusVehicle('${tram.id}')"
                     title="${tram.id} — ${tram.plate || ''} — ${statusText} — ${occInfo.occupied}/${occInfo.max} คน"
                     id="vcard-${tram.id}">
                    <div class="v-num" style="background: ${color.bg};">${idx + 1}</div>
                    <div class="v-status" style="background: ${statusColor};"></div>
                    <span style="color: ${color.bg};">${tram.id}</span>
                    <span class="v-occ">${occInfo.occupied}/${occInfo.max}</span>
                </div>
            `;
        }).join('');

        const countEl = document.getElementById('activeVehicleCount');
        if (countEl) countEl.textContent = `${activeCount}/${trams.length}`;
    }

    // ===== Render Station Rows with clear ETA display (Top 3 with view all toggle) =====
    function renderStationList() {
        const container = document.getElementById('stationListContainer');
        if (!container) return;

        container.innerHTML = stations.map((s, sIdx) => {
            const nearestETAs = getNearestETAs(s, 10);

            let etaHtml = '';
            if (nearestETAs.length === 0) {
                etaHtml = '<span class="text-[9px] text-slate-300 italic">ไม่มีรถ</span>';
            } else {
                const renderChip = (eta) => {
                    const color = getVehicleColor(eta.vehicleIdx);
                    const vNum = eta.vehicleIdx + 1;
                    const tram = trams[eta.vehicleIdx];

                    // getNearestETAs already normalised time to 'Now' for physically-at-station vehicles
                    const isAtStation = (eta.time === 'Now');
                    const mins = parseEtaMinutes(eta.time);

                    if (eta.isOnBreak) {
                        return `
                            <div class="eta-chip animate-fade-in" style="background: #fffbeb; color: #b45309; border-color: #fde68a;" title="รถไฟฟ้า ${tram ? tram.id : vNum} อยู่ในช่วงพักเบรกชั่วคราว">
                                <div class="eta-badge eta-vnum" style="background: #EAB308; color: #451a03;">${vNum}</div>
                                <span class="eta-time font-bold flex items-center gap-1">
                                    <i class="fas fa-pause text-amber-600 text-[10px]"></i>
                                    พักเบรก
                                </span>
                            </div>
                        `;
                    }

                    if (isAtStation || mins === 0) {
                        return `
                            <div class="eta-chip animate-fade-in" style="background: #ecfdf5; color: #065f46; border-color: #a7f3d0;" title="รถไฟฟ้า ${tram ? tram.id : vNum} อยู่ ณ จุดจอดนี้แล้ว">
                                <div class="eta-badge eta-vnum" style="background: ${color.bg}; color: white;">${vNum}</div>
                                <span class="eta-time font-bold flex items-center gap-1">
                                    <i class="fas fa-check-circle text-emerald-600 text-[10px]"></i>
                                    ถึงแล้ว
                                </span>
                            </div>
                        `;
                    } else {
                        let chipBg = '#eff6ff'; // blue-50
                        let chipText = '#1e40af'; // blue-800
                        let borderColor = '#bfdbfe';

                        if (mins <= 10) {
                            chipBg = '#fff7ed'; // orange-50
                            chipText = '#c2410c'; // orange-800
                            borderColor = '#fed7aa';
                        }

                        return `
                            <div class="eta-chip animate-fade-in" style="background: ${chipBg}; color: ${chipText}; border-color: ${borderColor};" title="คาดว่ารถไฟฟ้า ${tram ? tram.id : vNum} จะมาถึงในอีก ${eta.time}">
                                <div class="eta-badge eta-vnum" style="background: ${color.bg}; color: white;">${vNum}</div>
                                <span class="eta-time font-bold flex items-center gap-1">
                                    <i class="fas fa-clock text-amber-500 text-[9px]"></i>
                                    ${eta.time}
                                </span>
                            </div>
                        `;
                    }
                };

                const visibleChips = nearestETAs.slice(0, 3).map(renderChip).join('');
                const hiddenChips = nearestETAs.slice(3).map(renderChip).join('');

                if (nearestETAs.length > 3) {
                    etaHtml = `
                        <div class="flex items-center gap-[6px] flex-wrap">
                            ${visibleChips}
                            <div id="more-etas-${s.id}" class="hidden items-center gap-[6px] flex-wrap">
                                ${hiddenChips}
                            </div>
                            <button id="btn-more-${s.id}" data-count="${nearestETAs.length - 3}" onclick="toggleMoreEtas(event, ${s.id})" class="eta-chip hover:scale-105 border border-pink-100 bg-pink-50 text-pink-700 font-bold transition flex items-center gap-1">
                                +${nearestETAs.length - 3} คัน <i class="fas fa-chevron-down text-[8px]"></i>
                            </button>
                        </div>
                    `;
                } else {
                    etaHtml = `<div class="flex items-center gap-[6px] flex-wrap">${visibleChips}</div>`;
                }
            }

            return `
                <div onclick="focusStation(${s.id})" class="station-row">
                    <div class="st-top">
                        <div class="st-left">
                            <div class="st-num mr-2">${s.id}</div>
                            <div class="st-name">${s.name}</div>
                        </div>
                        <span class="text-[9px] text-slate-400 flex-shrink-0">${nearestETAs.length} คัน</span>
                    </div>
                    <div class="st-eta-container">
                        ${etaHtml}
                    </div>
                </div>
            `;
        }).join('');
    }

    window.toggleMoreEtas = function(event, stationId) {
        if (event) event.stopPropagation();
        const target = document.getElementById(`more-etas-${stationId}`);
        const btn = document.getElementById(`btn-more-${stationId}`);
        if (target && btn) {
            if (target.classList.contains('hidden')) {
                target.classList.remove('hidden');
                target.classList.add('flex');
                btn.innerHTML = `ซ่อน <i class="fas fa-chevron-up text-[8px]"></i>`;
            } else {
                target.classList.remove('flex');
                target.classList.add('hidden');
                const count = btn.getAttribute('data-count');
                btn.innerHTML = `+${count} คัน <i class="fas fa-chevron-down text-[8px]"></i>`;
            }
        }
    };

    // ===== Default Station Data (needed before initial render for GPS proximity checks) =====
    const defaultStationData = [
        { id: 1, name: "จุดจอด 1 ประตูหลังมอ.", lat: 6.549929, lng: 101.291254, type: "P", status: "จอดอยู่", time: "Now" },
        { id: 2, name: "จุดจอด 2 ตึกศิลปะ ", lat: 6.549100, lng: 101.290467, type: "P", status: "รอถัดไป", time: "5 นาที" },
        { id: 3, name: "จุดจอด 3 ศูนย์วิทยาศาสตร์ ", lat: 6.547835, lng: 101.289502, type: "P", status: "ถัดไป", time: "10 นาที" },
        { id: 4, name: "จุดจอด 4 คณะวิทยาศาสตร์", lat: 6.547224, lng: 101.289471, type: "P", status: "ถัดไป", time: "15 นาที" },
        { id: 5, name: "จุดจอด 5 สังคมศาสตร์", lat: 6.547311, lng: 101.288880, type: "P", status: "จอดอยู่", time: "Now" },
        { id: 6, name: "จุดจอด 6 อาคารเรียน20 ", lat: 6.548822, lng: 101.288523, type: "P", status: "รอถัดไป", time: "5 นาที" },
        { id: 7, name: "จุดจอด 7 คณะวิทยาการจัดการ ", lat: 6.549225, lng: 101.289286, type: "P", status: "ถัดไป", time: "10 นาที" }
    ];
    // Pre-populate stationData from localStorage or fallback before initial render
    (function() {
        const localStops = JSON.parse(localStorage.getItem("yru_stops_v2") || "[]");
        stationData = localStops.length > 0
            ? localStops.map((stop, index) => ({
                id: stop.sequence || (index + 1),
                name: stop.name || `จุดจอด ${stop.sequence || (index + 1)}`,
                lat: parseFloat(stop.lat || 0),
                lng: parseFloat(stop.lng || 0),
                type: "P", status: "ถัดไป", time: `${(index + 1) * 5} นาที`
              }))
            : defaultStationData;
    })();

    // Initial render
    renderVehicleCards();
    renderStationList();


    // 🗺️ Leaflet Map Initialization centered at YRU campus
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

    // Define base layers
    const googleRoadmap = L.tileLayer('https://mt1.google.com/vt/lyrs=r&apistyle=s.t:0|s.e:l|p.v:off,s.t:21|p.v:off,s.t:20|p.v:off&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        attribution: '&copy; Google Maps'
    });

    const googleSatellite = L.tileLayer('https://mt1.google.com/vt/lyrs=s&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        attribution: '&copy; Google Maps'
    });

    const googleHybrid = L.tileLayer('https://mt1.google.com/vt/lyrs=y&x={x}&y={y}&z={z}', {
        maxZoom: 20,
        attribution: '&copy; Google Maps'
    });

    const osmLayer = L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
        maxZoom: 20,
        attribution: '&copy; OpenStreetMap'
    });

    // Add default layer to map
    googleRoadmap.addTo(map);

    const baseMaps = {
        "แผนที่ปกติ (Google)": googleRoadmap,
        "ดาวเทียม": googleSatellite,
        "ดาวเทียม (มีชื่อถนน)": googleHybrid,
        "แผนที่ OSM": osmLayer
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
                font-family: 'Kanit', sans-serif;
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

    // Auto-fit map bounds so all 7 stations & garage are fully visible & close up
    function fitMapBounds() {
        const bounds = L.latLngBounds([
            [6.547150, 101.288400], // SW (station 5 & 6 area)
            [6.550050, 101.291750]  // NE (station 1 & garage area)
        ]);
        map.fitBounds(bounds, { padding: [10, 10], maxZoom: 17.5 });
    }

    // Force map to render correctly
    setTimeout(() => {
        map.invalidateSize();
        fitMapBounds();
    }, 400);


    let stationMarkers = {};

    function loadDynamicStationData() {
        const localStops = JSON.parse(localStorage.getItem("yru_stops_v2") || "[]");
        if (localStops.length === 0) {
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

    // Add Station markers — Concentric dot pin with จุดจอด label
    function drawStationMarkers() {
        stationData.forEach(st => {
            const markerHtml = `
                <div class="flex flex-col items-center" style="transform: translate(-20px, -20px);">
                    <div class="station-dot bg-white border-2 border-pink-600 w-5 h-5 rounded-full flex items-center justify-center shadow-md">
                        <div class="w-2 h-2 bg-pink-600 rounded-full"></div>
                    </div>
                    <div class="bg-white/95 backdrop-blur-sm text-[9px] font-bold px-2 py-0.5 rounded-full shadow border border-pink-100 text-pink-700 mt-0.5 whitespace-nowrap">
                        <i class="fas fa-parking text-pink-600 mr-0.5"></i>จุดจอด ${st.id}
                    </div>
                </div>
            `;

            const icon = L.divIcon({
                className: 'custom-leaflet-icon',
                html: markerHtml,
                iconSize: [40, 40],
                iconAnchor: [20, 20]
            });

            const popupHtml = `
                <div class="p-2 font-kanit">
                    <h4 class="font-bold text-slate-800 text-sm mb-1">จุดจอดรถไฟฟ้าที่ ${st.id}</h4>
                    <p class="text-xs text-slate-700 font-semibold mb-1">${st.name}</p>
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
                stationMarkers[st.id].getPopup().setContent(popupHtml);
            }
        });
    }

    loadDynamicStationData();
    drawStationMarkers();

    // Fallback static polyline route loop connecting stations
    const fallbackRouteCoords = [
        [6.549929, 101.291254], // จุดจอด 1 (ขวาบน)
        [6.549100, 101.290467], // จุดจอด 2
        [6.547835, 101.289502], // จุดจอด 3
        [6.547568, 101.289698], // (โค้งขวาล่าง เลี้ยวไปจุด 4)
        [6.547224, 101.289471], // จุดจอด 4
        [6.547311, 101.288880], // จุดจอด 5
        [6.547781, 101.288230], // (ทางแยกเข้าจุด 6)
        [6.548313, 101.288650],
        [6.548542, 101.288859], // ตรงไปจุด6 
        [6.548822, 101.288523], // จุดจอด 6 (ปลายติ่งซ้าย)
        [6.548575, 101.288877], // (กลับออกมาจากจุด 6)
        [6.549225, 101.289286], // จุดจอด 7
        [6.550430, 101.290106], // (โค้งซ้ายบนใกล้สนามกีฬา เลี้ยวขวา)
        [6.549929, 101.291254]  // Loop back to จุดจอด 1
    ];

    let activeRouteLines = [];

    function drawRouteLine(coords, color) {
        if (!map || !coords || coords.length < 2) return;
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

    // ⚡ Instant Route Line Renderer (0ms delay)
    function renderRouteInstant() {
        clearAllRouteLines();
        const rawStorage = localStorage.getItem("yru_routes_v1");
        if (rawStorage === null) {
            // Only draw fallback route if system has NEVER initialized routes before
            drawRouteLine(fallbackRouteCoords, '#ec4899');
            return;
        }

        try {
            const localRoutes = JSON.parse(rawStorage || "[]");
            if (Array.isArray(localRoutes) && localRoutes.length > 0) {
                localRoutes.forEach(route => {
                    let coords = route.polyline_data;
                    if (typeof coords === 'string') {
                        try { coords = JSON.parse(coords); } catch(e) { coords = []; }
                    }
                    if (coords && coords.length >= 2) {
                        drawRouteLine(coords, route.color || route.route_color || '#ec4899');
                    }
                });
            }
        } catch (e) {}
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

    function populateCallModalDropdowns() {
        const callLoc = document.getElementById('callLocation');
        const callDest = document.getElementById('callDestination');
        if (!callLoc || !callDest) return;

        const localStops = JSON.parse(localStorage.getItem("yru_stops_v2") || "[]");
        const stopsList = localStops.length > 0 ? localStops : defaultStationData;

        let locHtml = '<option value="">-- เลือกจุดที่คุณอยู่ --</option>';
        let destHtml = '<option value="">-- เลือกจุดปลายทาง --</option>';

        stopsList.forEach((stop, index) => {
            const seq = stop.sequence || (index + 1);
            const name = stop.name || `จุดจอด ${seq}`;
            locHtml += `<option value="${name}">${name}</option>`;
            destHtml += `<option value="${name}">${name}</option>`;
        });

        callLoc.innerHTML = locHtml;
        callDest.innerHTML = destHtml;
    }

    populateCallModalDropdowns();

    // Listen to real-time storage updates from Admin tab
    window.addEventListener('storage', (event) => {
        if (!event || !event.key || event.key === 'yru_stops_v2' || event.key === 'yru_routes_v1' || event.key === 'yru_routes_last_updated') {
            if (typeof loadDynamicStations === 'function') loadDynamicStations();
            if (typeof loadDynamicStationData === 'function') loadDynamicStationData();
            if (typeof drawStationMarkers === 'function') drawStationMarkers();
            if (typeof renderStationList === 'function') renderStationList();
            if (typeof loadDynamicRoute === 'function') loadDynamicRoute('แอดมินได้ทำการปรับเปลี่ยนข้อมูลเส้นทางเดินรถ');
            populateCallModalDropdowns();
        }
    });


    // ===== 🚌 Shuttle Markers — ALL vehicles shown with numbered icons =====
    // ===== Garage / Depot Clustering Helpers =====

    // Helper: Create custom HTML DivIcon for Garage Cluster Marker
    function getGarageClusterIcon(count) {
        return L.divIcon({
            className: 'custom-leaflet-icon',
            html: `
                <div class="relative group cursor-pointer" style="transform: translate(-24px, -24px);">
                    <!-- Badge Card -->
                    <div class="relative flex items-center gap-2 bg-slate-900/95 hover:bg-slate-900 backdrop-blur-md text-white border-2 border-amber-400/90 px-2.5 py-1.5 rounded-2xl shadow-2xl transition-all duration-300 transform group-hover:scale-105">
                        <div class="w-7 h-7 rounded-xl bg-gradient-to-br from-amber-400 to-orange-500 flex items-center justify-center text-slate-900 font-bold text-sm shadow-md flex-shrink-0">
                            🏠
                        </div>
                        <div class="flex flex-col pr-1 whitespace-nowrap">
                            <span class="text-xs font-bold text-black leading-tight" style="color: black;">จุดจอดเก็บรถ</span>
                            <div class="flex items-center gap-1 mt-0.5">
                                <span class="bg-gradient-to-r from-red-500 to-rose-600 text-white text-[10px] font-extrabold px-2 py-0.5 rounded-full shadow-sm">
                                    ${count} คัน
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            `,
            iconSize: [48, 48],
            iconAnchor: [24, 24]
        });
    }

    // Helper: Render or update single Garage Cluster Marker & its Popup
    function updateGarageCluster(garageTrams) {
        if (!garageTrams || garageTrams.length === 0) {
            if (garageClusterMarker) {
                map.removeLayer(garageClusterMarker);
                garageClusterMarker = null;
            }
            return;
        }

        const count = garageTrams.length;
        const icon = getGarageClusterIcon(count);
        const latLng = [GARAGE_LAT, GARAGE_LNG];

        const vehicleListItems = garageTrams.map(tram => {
            const tramIndex = trams.findIndex(t => t.id === tram.id);
            const color = getVehicleColor(tramIndex !== -1 ? tramIndex : 0);
            const carData = globalCarStatus[tram.id] || {};
            const rawStatus = (carData.status || tram.status || 'ซ่อมบำรุง / หยุดพัก').toString().trim();
            const isBroken = rawStatus.includes('ขัดข้อง') || rawStatus === 'MAINTENANCE' || rawStatus.includes('ระงับ');
            const isEnded = rawStatus === 'ended' || rawStatus === 'สิ้นสุด' || rawStatus === 'เลิกงาน';
            
            let statusBadgeClass = 'bg-amber-100 text-amber-800 border-amber-200';
            let statusIcon = '⏸️';
            let displayStatus = 'พักเบรก / จุดจอด';
            
            if (isBroken) {
                statusBadgeClass = 'bg-red-100 text-red-700 border-red-200';
                statusIcon = '🛑';
                displayStatus = rawStatus;
            } else if (isEnded) {
                statusBadgeClass = 'bg-gray-100 text-gray-700 border-gray-200';
                statusIcon = '🏁';
                displayStatus = 'เลิกงาน / สิ้นสุดรอบ';
            }

            const issueNote = tram.active_issue || tram.note || 'ประจำจุดจอด / พักรอรอบถัดไป';

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
                            🏠
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

    // ===== 🚌 Shuttle Markers — Active Route vehicles shown, Garage vehicles clustered =====
    const tramMarkers = {};

    // Custom icon: numbered circle with vehicle color + vehicle pill tag underneath
    function getShuttleIcon(tramId, status, vehicleIdx) {
        const color = getVehicleColor(vehicleIdx);
        const number = vehicleIdx + 1;

        let storedStatus = '';
        try {
            const rawStored = localStorage.getItem('yru_car_status_' + tramId);
            if (rawStored) {
                const parsedStored = JSON.parse(rawStored);
                storedStatus = (parsedStored.status || '').toString().trim();
            }
        } catch(e) {}

        const carData = (typeof globalCarStatus !== 'undefined' && globalCarStatus[tramId]) ? globalCarStatus[tramId] : {};
        let rawStatus = (carData.status || storedStatus || 'ปกติกำลังขับ').toString().trim();
        const tramObj = (typeof trams !== 'undefined' && Array.isArray(trams)) ? trams.find(t => t.id === tramId) : null;
        const mainStatus = (tramObj ? tramObj.status : '').toString().trim();

        const isPause = (
            status === 'pause' ||
            storedStatus === 'pause' || storedStatus === 'พักเบรค' || storedStatus === 'พักเบรก' || storedStatus.includes('พัก') ||
            rawStatus === 'pause' || rawStatus === 'พักเบรค' || rawStatus === 'พักเบรก' || rawStatus.includes('พัก') ||
            (mainStatus && (mainStatus.includes('พัก') || mainStatus === 'pause'))
        );

        const isBroken = (
            status === 'broken' ||
            storedStatus === 'broken' || storedStatus.includes('ขัดข้อง') || storedStatus.includes('ระงับ') ||
            rawStatus === 'broken' || rawStatus.includes('ขัดข้อง') || rawStatus.includes('ระงับ') ||
            (mainStatus && (mainStatus.includes('ขัดข้อง') || mainStatus.includes('ระงับ')))
        );

        if (isPause) {
            status = 'pause';
        } else if (isBroken) {
            status = 'broken';
        } else if (mainStatus === 'พร้อมใช้งาน' || mainStatus === 'ACTIVE') {
            status = 'normal';
        }

        let bgColor = color.bg;
        let textColor = color.text;
        let pingHtml = '';
        let opacityClass = '';
        let statusIcon = '';
        let topStatusBadge = '';

        if (status === 'pause') {
            bgColor = '#EAB308';
            textColor = '#451A03';
            pingHtml = '';
            statusIcon = '<i class="fas fa-pause text-[8px] absolute -bottom-0.5 -right-0.5 bg-amber-600 text-white w-4 h-4 flex items-center justify-center rounded-full border-2 border-white shadow-md"></i>';
            topStatusBadge = `<div class="absolute -top-6 left-1/2 -translate-x-1/2 bg-amber-500 text-white text-[9px] font-black px-2 py-0.5 rounded-full shadow-md whitespace-nowrap border border-white flex items-center gap-1"><i class="fas fa-pause text-[8px]"></i> พักเบรค</div>`;
        } else if (status === 'broken' || (mainStatus !== 'พร้อมใช้งาน' && (rawStatus.includes('ขัดข้อง') || rawStatus.includes('ระงับ')))) {
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
                    <div class="relative w-10 h-10 rounded-full flex items-center justify-center border-[3px] border-white shadow-xl"
                         style="background: ${bgColor};">
                        <span class="text-sm font-black" style="color: ${textColor}; text-shadow: 0 1px 2px rgba(0,0,0,0.2);">${number}</span>
                        ${statusIcon}
                    </div>
                    <span class="absolute -bottom-5 left-1/2 -translate-x-1/2 bg-white/95 backdrop-blur-sm text-[9px] font-bold px-2 py-0.5 rounded-full shadow whitespace-nowrap"
                          style="color: ${pillTextColor}; border: 1.5px solid ${pillBorderColor};">
                        ${labelText}
                    </span>
                </div>
            `,
            iconSize: [40, 40],
            iconAnchor: [20, 20]
        });
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
            if (!marker || !marker.getLatLng) return null;
            const baseLatLng = marker._originalLatLng || marker.getLatLng();
            const point = map.latLngToContainerPoint(baseLatLng);
            return { id, marker, baseLatLng, point };
        }).filter(Boolean);

        const ST_THRESHOLD_PX = 50;  // distance threshold to a station
        const VEH_THRESHOLD_PX = 42; // distance threshold between vehicles

        const vehiclesAtStations = {};
        const routeVehicles = [];

        // Classify vehicles: at a station vs on open route
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

        // Helper to update marker position ONLY if it actually changed (prevents flicker!)
        function setMarkerLatLngSmooth(marker, targetLatLng) {
            const current = marker.getLatLng();
            if (!current || Math.abs(current.lat - targetLatLng.lat) > 0.000001 || Math.abs(current.lng - targetLatLng.lng) > 0.000001) {
                marker.setLatLng(targetLatLng);
            }
        }

        // Offset vehicles at stations
        Object.values(vehiclesAtStations).forEach(({ station, vehicles }) => {
            const count = vehicles.length;
            if (count === 1) {
                const newPoint = L.point(station.point.x, station.point.y - 44);
                const newLatLng = map.containerPointToLatLng(newPoint);
                setMarkerLatLngSmooth(vehicles[0].marker, newLatLng);
            } else {
                const spreadAngle = Math.PI / 3;
                const startAngle = -Math.PI / 2 - (spreadAngle * (count - 1)) / 2;
                const radius = 46;

                vehicles.forEach((v, i) => {
                    const angle = startAngle + i * spreadAngle;
                    const offsetX = Math.cos(angle) * radius;
                    const offsetY = Math.sin(angle) * radius;
                    const newPoint = L.point(station.point.x + offsetX, station.point.y + offsetY);
                    const newLatLng = map.containerPointToLatLng(newPoint);
                    setMarkerLatLngSmooth(v.marker, newLatLng);
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

    map.on('zoomend moveend layeradd', () => {
        applyAntiOverlap();
    });

    // Initialize shuttle markers — Active route vehicles shown, Garage vehicles clustered
    function initTramMarkers() {
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
                if (idx === 0) {
                    latLng = [stationData[0].lat, stationData[0].lng]; // EV-01: จุดจอด 1
                } else if (idx === 1) {
                    latLng = [stationData[4].lat, stationData[4].lng]; // EV-02: จุดจอด 5
                } else if (idx === 2) {
                    latLng = [stationData[2].lat, stationData[2].lng]; // EV-03: จุดจอด 3
                } else if (idx === 3) {
                    latLng = [stationData[6].lat, stationData[6].lng]; // EV-04: จุดจอด 7
                } else {
                    latLng = [GARAGE_LAT, GARAGE_LNG]; // Garage default
                }
            }

            const carData = globalCarStatus[tram.id] || {};
            const rawStatusStr = (carData.status || tram.status || '').toString().trim();

            let status = 'normal';
            if (rawStatusStr.includes('ขัดข้อง') || rawStatusStr === 'SUSPENDED' || rawStatusStr === 'MAINTENANCE') {
                status = 'broken';
            } else if (rawStatusStr.includes('พัก') || rawStatusStr === 'pause' || rawStatusStr === 'PAUSE' || rawStatusStr === 'กำลังปรับปรุง') {
                status = 'pause';
            } else if (rawStatusStr === 'ended' || rawStatusStr === 'สิ้นสุด' || rawStatusStr === 'เลิกงาน') {
                status = 'ended';
            }

            if (!tramMarkers[tram.id]) {
                const marker = L.marker(latLng, {
                    icon: getShuttleIcon(tram.id, status, idx),
                    zIndexOffset: 500
                }).addTo(map);
                marker._originalLatLng = L.latLng(latLng[0], latLng[1]);
                tramMarkers[tram.id] = marker;
            } else {
                tramMarkers[tram.id]._originalLatLng = L.latLng(latLng[0], latLng[1]);
                tramMarkers[tram.id].setIcon(getShuttleIcon(tram.id, status, idx));
            }

            // Bind popup for active vehicle
            const occInfo = getCarOccupiedInfo(tram.id);
            
            let statusTextHtml = '<span class="font-bold text-emerald-600">พร้อมใช้งาน</span>';
            if (rawStatusStr.includes('ขัดข้อง') || rawStatusStr === 'SUSPENDED' || rawStatusStr === 'MAINTENANCE' || rawStatusStr.includes('ระงับ')) {
                statusTextHtml = `<span class="font-bold text-red-600">${rawStatusStr}</span>`;
            } else if (rawStatusStr.includes('พัก') || rawStatusStr === 'pause') {
                statusTextHtml = `<span class="font-bold text-amber-500">พักเบรค</span>`;
            } else if (rawStatusStr === 'ended' || rawStatusStr === 'สิ้นสุด' || rawStatusStr === 'เลิกงาน') {
                statusTextHtml = `<span class="font-bold text-gray-500">เลิกงาน/สิ้นสุดรอบ</span>`;
            }

            const statusDisplay = (occInfo.isFull && !isVehicleInGarage(tram))
                ? '<span class="font-bold text-red-600 bg-red-100 border border-red-200 px-2 py-0.5 rounded-full text-xs">🛑 รถเต็ม (Full)</span>'
                : statusTextHtml;

            tramMarkers[tram.id].bindPopup(`
                <div class="p-2 font-kanit">
                    <div class="flex items-center gap-2 mb-2">
                        <h4 class="font-bold text-slate-800 text-sm">${tram.id}</h4>
                    </div>
                    <p class="text-xs text-slate-500">ทะเบียน: <span class="font-bold text-slate-700">${tram.plate}</span></p>
                    <p class="text-xs text-slate-500 mt-1">สถานะ: ${statusDisplay}</p>
                    <p class="text-xs text-slate-500 mt-1">ผู้โดยสาร: <span class="font-bold ${occInfo.isFull ? 'text-red-600' : 'text-pink-600'} font-extrabold">${occInfo.occupied}/${occInfo.max} คน</span></p>
                </div>
            `);
        });

        updateGarageCluster(garageTrams);
    }

    initTramMarkers();
    setTimeout(applyAntiOverlap, 600);

    // ===== Focus helpers =====
    window.focusStation = function(stationId) {
        const station = stationData.find(st => st.id === stationId);
        if (station) {
            map.setView([station.lat, station.lng], 19, { animate: true });
            stationMarkers[stationId].openPopup();
        }
    };

    window.focusVehicle = function(tramId) {
        const tram = trams.find(t => t.id === tramId);
        if (tram && isVehicleInGarage(tram)) {
            map.setView([GARAGE_LAT, GARAGE_LNG], 18.5, { animate: true });
            if (garageClusterMarker) {
                garageClusterMarker.openPopup();
            }
            return;
        }

        const marker = tramMarkers[tramId];
        if (marker) {
            const latlng = marker._originalLatLng || marker.getLatLng();
            map.setView(latlng, 19, { animate: true });
            marker.openPopup();
        }
    };

    function updateShuttleLabels() {
        // Handled dynamically via setIcon
    }

    function openCallModal() {
        trams = getStorage("yru_trams_v18", defaultTrams);
        const activeTrams = trams.filter(t => !isVehicleInactive(t));
        const warnEl = document.getElementById('no-ev-warning');
        const submitBtn = document.getElementById('btnSubmitCall');
        const locSelect = document.getElementById('callLocation');
        const destSelect = document.getElementById('callDestination');

        if (activeTrams.length === 0) {
            if (warnEl) warnEl.classList.remove('hidden');
            if (submitBtn) submitBtn.disabled = true;
            if (locSelect) locSelect.disabled = true;
            if (destSelect) destSelect.disabled = true;
        } else {
            if (warnEl) warnEl.classList.add('hidden');
            if (submitBtn) submitBtn.disabled = false;
            if (locSelect) locSelect.disabled = false;
            if (destSelect) destSelect.disabled = false;
        }

        document.getElementById('callModal').classList.remove('hidden');
        document.getElementById('callModal').classList.add('flex');
    }

    function closeCallModal() {
        document.getElementById('callModal').classList.add('hidden');
        document.getElementById('callModal').classList.remove('flex');
    }

    function updatePax(change) {
        const paxElement = document.getElementById('paxCount');
        let currentPax = parseInt(paxElement.innerText);
        currentPax = Math.max(1, Math.min(10, currentPax + change));
        paxElement.innerText = currentPax;
    }

    // Dynamic refreshMarkerIcon - works for any tram ID
    function refreshMarkerIcon(tramId) {
        const marker = tramMarkers[tramId];
        if (!marker) return;

        const tramIndex = trams.findIndex(t => t.id === tramId);
        if (tramIndex < 0) return;
        const tram = trams[tramIndex];

        let storedStatus = '';
        try {
            const rawStored = localStorage.getItem('yru_car_status_' + tramId);
            if (rawStored) {
                const parsedStored = JSON.parse(rawStored);
                storedStatus = (parsedStored.status || '').toString().trim();
            }
        } catch(e) {}

        const carData = globalCarStatus[tramId] || {};
        const rawStatus = (carData.status || storedStatus || tram.status || '').toString().trim();
        let status = 'normal';
        if (rawStatus === 'pause' || rawStatus.includes('พัก') || rawStatus === 'กำลังปรับปรุง' || storedStatus === 'pause' || storedStatus.includes('พัก')) status = 'pause';
        else if (rawStatus === 'broken' || rawStatus.includes('ขัดข้อง') || rawStatus.includes('ระงับ') || storedStatus === 'broken' || storedStatus.includes('ขัดข้อง')) status = 'broken';
        else if (rawStatus === 'ended' || rawStatus.includes('สิ้นสุด') || rawStatus.includes('เลิกงาน') || storedStatus === 'ended') status = 'ended';
        
        marker.setIcon(getShuttleIcon(tramId, status, tramIndex));

        const color = getVehicleColor(tramIndex);
        const occInfo = getCarOccupiedInfo(tramId);
        
        let statusTextHtml = '<span class="font-bold text-emerald-600">พร้อมใช้งาน</span>';
        const rawStatusStr = (rawStatus || '').toString().trim();
        if (rawStatusStr === 'ระงับการใช้งาน' || rawStatusStr === 'SUSPENDED' || rawStatusStr === 'MAINTENANCE' || rawStatusStr === 'ระงับใช้งาน' || rawStatusStr.includes('ขัดข้อง')) {
            statusTextHtml = `<span class="font-bold text-red-600">${rawStatusStr}</span>`;
        } else if (rawStatusStr === 'พักเบรก' || rawStatusStr === 'พัก' || rawStatusStr === 'พักเบรค') {
            statusTextHtml = `<span class="font-bold text-amber-500">พักเบรก</span>`;
        } else if (rawStatusStr === 'ended' || rawStatusStr === 'สิ้นสุด' || rawStatusStr === 'เลิกงาน') {
            statusTextHtml = `<span class="font-bold text-gray-500">เลิกงาน/สิ้นสุดรอบ</span>`;
        }

        const statusDisplay = (occInfo.isFull && !isVehicleInGarage(tram))
            ? '<span class="font-bold text-red-600 bg-red-100 border border-red-200 px-2 py-0.5 rounded-full text-xs">🛑 รถเต็ม (Full)</span>'
            : statusTextHtml;

        const popupHtml = `
            <div class="p-2 font-kanit">
                <div class="flex items-center gap-2 mb-2">
                    <h4 class="font-bold text-slate-800 text-sm">${tramId}</h4>
                </div>
                <p class="text-xs text-slate-600 mb-1">คนขับ: <span class="font-bold text-slate-800">${tram.driver || 'ไม่มีคนขับ'}</span></p>
                <p class="text-xs text-slate-500">สถานะ: ${statusDisplay}</p>
                <p class="text-xs text-slate-500 mt-1">ผู้โดยสาร: <span class="font-bold ${occInfo.isFull ? 'text-red-600' : 'text-pink-600'} font-extrabold">${occInfo.occupied}/${occInfo.max} คน</span></p>
                <button onclick="openSurveyModal('${tram.driver_id}')" class="mt-2.5 w-full bg-pink-600 hover:bg-pink-700 text-white text-[11px] font-bold py-1.5 px-3 rounded-xl transition-all shadow-sm active:scale-95">
                    ⭐ ประเมินความพึงพอใจ
                </button>
            </div>
        `;
        marker.bindPopup(popupHtml);
    }

    function fetchSeatsLeft() {
        trams = getStorage("yru_trams_v18", defaultTrams);
        const garageTrams = [];

        // Sync globalCarStatus with latest tram statuses
        trams.forEach(t => {
            if (globalCarStatus[t.id]) {
                globalCarStatus[t.id].status = t.status || 'พร้อมใช้งาน';
            }
        });

        // Remove markers that are no longer in trams
        Object.keys(tramMarkers).forEach(tramId => {
            if (!trams.find(t => t.id === tramId)) {
                map.removeLayer(tramMarkers[tramId]);
                delete tramMarkers[tramId];
            }
        });

        trams.forEach((tram, index) => {
            if (isVehicleInGarage(tram)) {
                if (tramMarkers[tram.id]) {
                    map.removeLayer(tramMarkers[tram.id]);
                    delete tramMarkers[tram.id];
                }
                garageTrams.push(tram);
                return;
            }

            let marker = tramMarkers[tram.id];
            let latLng = null;
            if (tram.coords) {
                const parts = tram.coords.split(',').map(Number);
                if (parts.length === 2 && !isNaN(parts[0]) && !isNaN(parts[1])) {
                    latLng = L.latLng(parts[0], parts[1]);
                }
            }
            
            if (!marker && latLng) {
                marker = L.marker(latLng, { icon: getShuttleIcon(tram.id, 'active', index), zIndexOffset: 1000 - index }).addTo(map);
                tramMarkers[tram.id] = marker;
            }

            if (marker && latLng) {
                marker._originalLatLng = latLng;
                refreshMarkerIcon(tram.id);
            }
        });

        updateGarageCluster(garageTrams);
        applyAntiOverlap();

        if (typeof trams !== 'undefined' && Array.isArray(trams)) {
            trams.forEach(tram => {
                try {
                    const rawStatus = localStorage.getItem('yru_car_status_' + tram.id);
                    if (rawStatus) {
                        const parsed = JSON.parse(rawStatus);
                        if (parsed && parsed.occupied !== undefined && globalCarStatus[tram.id]) {
                            globalCarStatus[tram.id].occupied = parseInt(parsed.occupied) || 0;
                        }
                    }
                } catch(e) {}
            });
        }

        fetch('/api/get-seats-left?t=' + new Date().getTime())
        .then(res => res.json())
        .then(data => {
            if (data && data.car_occupied_map && typeof data.car_occupied_map === 'object') {
                Object.keys(data.car_occupied_map).forEach(cCode => {
                    if (globalCarStatus[cCode]) {
                        globalCarStatus[cCode].occupied = parseInt(data.car_occupied_map[cCode]) || 0;
                    }
                });
            }
            if (trams[0] && data.car_1_occupied !== undefined) {
                const id0 = trams[0].id;
                if (globalCarStatus[id0]) globalCarStatus[id0].occupied = data.car_1_occupied;
            }
            if (trams[1] && data.car_2_occupied !== undefined) {
                const id1 = trams[1].id;
                if (globalCarStatus[id1]) globalCarStatus[id1].occupied = data.car_2_occupied;
            }
            trams.forEach(tram => {
                if (!isVehicleInGarage(tram)) {
                    refreshMarkerIcon(tram.id);
                }
            });
            updateShuttleLabels();
            renderVehicleCards();
            renderStationList();
            applyAntiOverlap();
        }).catch(e => {
            trams.forEach(tram => {
                if (!isVehicleInGarage(tram)) {
                    refreshMarkerIcon(tram.id);
                }
            });
            updateShuttleLabels();
            renderVehicleCards();
            renderStationList();
        });
    }

    window.addEventListener('storage', function(e) {
        if (typeof renderVehicleCards === 'function') {
            renderVehicleCards();
        }
        if (typeof trams !== 'undefined' && Array.isArray(trams)) {
            trams.forEach(t => {
                if (typeof refreshMarkerIcon === 'function') {
                    refreshMarkerIcon(t.id);
                }
            });
        }
    });

    function submitCall() {
        const locSelect = document.getElementById('callLocation');
        const locValue = locSelect.value;
        const locName = locSelect.options[locSelect.selectedIndex].text;
        
        const destSelect = document.getElementById('callDestination');
        const destValue = destSelect.value;
        const destName = destSelect.options[destSelect.selectedIndex].text;

        const pax = document.getElementById('paxCount').innerText;

        if (!locValue) {
            alert("กรุณาเลือกตำแหน่งของคุณก่อนครับ");
            return;
        }
        if (!destValue) {
            alert("กรุณาเลือกตำแหน่งปลายทางของคุณครับ");
            return;
        }
        if (locValue === destValue) {
            alert("ตำแหน่งเริ่มต้นและปลายทางต้องไม่ซ้ำกันครับ");
            return;
        }
        if (lastHandledStatus === 'ended') {
            alert("ไม่สามารถเรียกรถได้ เนื่องจากขณะนี้รถไฟฟ้าสิ้นสุดรอบเดินรถแล้วครับ");
            return;
        }

        // หาหมายเลขรถที่ใกล้จุดจอดที่สุด และพร้อมใช้งาน (ไม่ขัดข้อง / ไม่ระงับการใช้งาน / ไม่อยู่ในอู่)
        const assignedTram = getBestAvailableVehicle(locValue);
        
        if (!assignedTram) {
            Swal.fire({
                title: '⚠️ ไม่สามารถเรียกรถได้',
                text: 'ขณะนี้ไม่มีรถไฟฟ้าที่พร้อมให้บริการอยู่ในเส้นทาง (รถทั้งหมดขัดข้อง ระงับการใช้งาน หรืออยู่จุดจอดเก็บรถ)',
                icon: 'warning',
                confirmButtonText: 'ตกลง',
                confirmButtonColor: '#ec4899'
            });
            return;
        }

        const assignedCarId = assignedTram.id;
        const occInfo = getCarOccupiedInfo(assignedCarId);
        const requestedPax = parseInt(pax) || 1;
        const remainingSeats = Math.max(0, occInfo.max - occInfo.occupied);

        // 1. ตรวจสอบสถานะรถเต็ม (Full Status Check)
        if (remainingSeats <= 0 || occInfo.isFull) {
            Swal.fire({
                title: 'ที่นั่งไม่พอ',
                text: 'รถคันนี้ที่นั่งไม่พอ กรุณารอเที่ยวถัดไป',
                icon: 'warning',
                confirmButtonText: 'ตกลง',
                confirmButtonColor: '#ec4899'
            });
            return;
        }

        // 2. ตรวจสอบจำนวนผู้โดยสารเรียกรถไม่ให้เกินที่นั่งว่างคงเหลือ (Capacity Check)
        if (requestedPax > remainingSeats) {
            Swal.fire({
                title: 'ที่นั่งไม่พอ',
                text: 'รถคันนี้ที่นั่งไม่พอ กรุณารอเที่ยวถัดไป',
                icon: 'warning',
                confirmButtonText: 'ตกลง',
                confirmButtonColor: '#ec4899'
            });
            return;
        }

        // คำนวณเวลาที่รถจะมาถึง
        let arrivalText = getEtaTextForStation(locName, assignedCarId);

        // บันทึกคิวเรียกรถลง localStorage เพื่อให้ admin เห็น
        // บันทึกคิวเรียกรถลง localStorage เพื่อให้ admin เห็น
        const callQueue = JSON.parse(localStorage.getItem('yru_call_queue') || '[]');
        const newCall = {
            id: 'U' + Date.now().toString().slice(-6),
            station: locName,
            destination: destName,
            pax: parseInt(pax),
            time: new Date().toLocaleTimeString('th-TH', { hour: '2-digit', minute: '2-digit' }),
            timestamp: Date.now(),
            status: 'waiting',
            car_id: assignedCarId
        };
        callQueue.unshift(newCall); // เพิ่มที่ต้นคิว (ล่าสุดก่อน)
        if (callQueue.length > 1000) callQueue.pop(); // เก็บสูงสุด 1000 รายการประวัติเรียกรถจริง
        localStorage.setItem('yru_call_queue', JSON.stringify(callQueue));

        // บันทึกว่าผู้ใช้คนนี้มีรายการเรียกรถของตัวเองใน session นี้
        sessionStorage.setItem('yru_my_active_call_id', newCall.id);
        sessionStorage.setItem('yru_my_active_call_data', JSON.stringify(newCall));

        // ส่งสถิติไปยัง Backend API เพื่อสะสมข้อมูลจริงใน Cache
        fetch('/api/call-ev', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || ''
            },
            body: JSON.stringify({
                call_id: newCall.id,
                station: locName,
                destination: destName,
                pax: parseInt(pax),
                car_id: assignedCarId
            })
        })
        .then(res => res.json())
        .then(data => console.log('Synced call to backend:', data))
        .catch(err => console.error('Error syncing call to backend:', err));

        // บวกจำนวนผู้โดยสารสะสมประจำวัน (บวกเพิ่มเรื่อยๆ ไม่ลดลง)
        let accumulatedPax = parseInt(localStorage.getItem('yru_today_pax_accumulated') || '0');
        accumulatedPax += parseInt(pax);
        localStorage.setItem('yru_today_pax_accumulated', accumulatedPax.toString());

        // บวกรอบการเดินรถ
        let accumulatedTrips = parseInt(localStorage.getItem('yru_today_trips_accumulated') || '0');
        accumulatedTrips += 1;
        localStorage.setItem('yru_today_trips_accumulated', accumulatedTrips.toString());

        // แสดงผลสำเร็จทันที (frontend-only mode)
        Swal.fire({
            title: '📢 ส่งสัญญาณเรียกสำเร็จ',
            html: `<b>รับที่:</b> ${locName}<br><b>ปลายทาง:</b> ${destName}<br><b>จำนวน:</b> ${pax} คน<br><b>รถไฟฟ้าที่จัดสรร:</b> <span class="text-pink-600 font-bold">${assignedCarId}</span>`,
            icon: 'success',
            confirmButtonText: 'ตกลง',
            confirmButtonColor: '#ec4899'
        });
        closeCallModal();

        // อัปเดตจำนวนผู้โดยสารบนรถ (Pills & Popups) ทันทีหลังเรียกรถ
        renderVehicleCards();
        if (typeof trams !== 'undefined' && Array.isArray(trams)) {
            trams.forEach(t => refreshMarkerIcon(t.id));
        }
        try { window.dispatchEvent(new Event('storage')); } catch(e) {}

        const statusDisplay = document.getElementById('statusPassengerDisplay');
        if (statusDisplay) {
            statusDisplay.innerHTML = `
                <div class="flex flex-col">
                    <span class="text-pink-600 font-black text-base animate-pulse">
                        <i class="fas fa-check-circle"></i> รับทราบ! ระบบกำลังส่งรถไปรับคุณ
                    </span>
                    <span class="text-slate-800 text-sm font-semibold mt-1">
                        ${arrivalText}
                    </span>
                    <span class="text-slate-500 text-xs mt-1">
                        📍 จุดรับ: <span class="font-bold text-blue-600">${locName}</span> ➡️ ปลายทาง: <span class="font-bold text-blue-600">${destName}</span>
                    </span>
                </div>
            `;
        }

        // เริ่มตรวจสอบสถานะตอบรับจากคนขับทันที
        startCallStatusPolling();
    }

    // 📡 Real-time status polling
    function fetchDriverStatusRealtime() {
        fetch('/api/get-driver-status?t=' + new Date().getTime())
        .then(response => {
            if (!response.ok) throw new Error('API server status invalid');
            return response.json();
        })
        .then(data => {
            // Map all 10 cars status from backend
            trams.forEach((tram, index) => {
                const carKey = 'car_' + (index + 1);
                if (data[carKey]) {
                    updateCarStatusUI(tram.id, data[carKey]);
                }
            });
            // Re-render sidebar
            renderVehicleCards();
            renderStationList();
        })
        .catch(error => console.error('Error fetching driver status:', error));
    }

    let callStatusInterval = null;
    
    function startCallStatusPolling() {
        if (callStatusInterval) clearInterval(callStatusInterval);
        callStatusInterval = setInterval(checkMyCallStatus, 4000);
        checkMyCallStatus();
    }
    
    function checkMyCallStatus() {
        const statusDisplay = document.getElementById('statusPassengerDisplay');
        if (!statusDisplay) return;

        const myActiveCallId = sessionStorage.getItem('yru_my_active_call_id');
        const myActiveCallData = JSON.parse(sessionStorage.getItem('yru_my_active_call_data') || 'null');

        // หากผู้ใช้เพิ่งเข้าสู่ระบบ หรือยังไม่ได้กดเรียกรถด้วยตัวเองในรอบนี้ ให้แสดงสถานะเริ่มต้นพร้อมใช้งาน
        if (!myActiveCallId && !myActiveCallData) {
            statusDisplay.innerHTML = `
                <p class="text-[10px] text-slate-400 font-semibold">สถานะการให้บริการ</p>
                <p class="text-sm font-bold text-slate-700 flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse inline-block"></span>
                    ยังไม่มีรายการเรียกรถในขณะนี้ <span class="text-xs text-slate-500 font-normal hidden md:inline">(กดปุ่ม "เรียกรถที่นี่" เพื่อเริ่มเรียกรถไฟฟ้า)</span>
                </p>
            `;
            return;
        }

        // ตรวจสอบคิวเรียกรถใน localStorage
        const callQueue = JSON.parse(localStorage.getItem('yru_call_queue') || '[]');
        const foundMyCall = callQueue.find(c => c.id === myActiveCallId);

        fetch('/api/check-ev-request?t=' + Date.now())
        .then(res => res.json())
        .then(data => {
            const isCallActive = (foundMyCall && foundMyCall.status !== 'completed' && foundMyCall.status !== 'cleared' && foundMyCall.status !== 'cancelled') ||
                                 (data && data.status !== 'completed' && data.status !== 'cleared' && data.status !== 'cancelled' && data.car_id && (!data.call_id || data.call_id === myActiveCallId));

            if (isCallActive && (foundMyCall || myActiveCallData)) {
                const callData = foundMyCall || myActiveCallData;
                const statusStr = (foundMyCall ? foundMyCall.status : (data ? data.status : 'waiting'));

                const targetCarId = callData.car_id || 'EV-01';
                const targetCarObj = (typeof trams !== 'undefined' && Array.isArray(trams)) ? trams.find(t => t.id === targetCarId) : null;
                const isTargetOnBreak = isVehicleOnBreak(targetCarObj);

                if (isTargetOnBreak) {
                    statusDisplay.innerHTML = `
                        <div class="flex flex-col">
                            <span class="text-amber-600 font-black text-base animate-pulse flex items-center gap-2">
                                <i class="fas fa-pause-circle text-amber-500"></i> รถไฟฟ้า ${targetCarId} กำลังพักเบรกชั่วคราว
                            </span>
                            <span class="text-amber-800 text-xs font-semibold mt-1">
                                ☕ พนักงานขับรถประจำคันกำลังพักเบรกสั้นๆ ระบบจะดำเนินการรับผู้โดยสารต่อเมื่อสิ้นสุดช่วงพัก
                            </span>
                            <span class="text-slate-500 text-xs mt-1">
                                📍 จุดรับ: <span class="font-bold text-blue-600">${callData.station}</span> ➡️ ปลายทาง: <span class="font-bold text-blue-600">${callData.destination || 'ไม่ได้ระบุ'}</span> (${callData.pax || 1} คน)
                            </span>
                        </div>
                    `;
                } else if (statusStr === 'onboard') {
                    statusDisplay.innerHTML = `
                        <div class="flex flex-col">
                            <span class="text-blue-600 font-black text-base animate-pulse">
                                <i class="fas fa-bus text-blue-500"></i> คุณอยู่บนรถแล้ว! กำลังมุ่งหน้าไป: <span class="text-pink-600 font-extrabold">${callData.destination || 'จุดหมายปลายทาง'}</span>
                            </span>
                            <span class="text-slate-500 text-xs mt-1">
                                📍 จุดรับ: <span class="font-bold text-blue-600">${callData.station}</span> ➡️ ปลายทาง: <span class="font-bold text-blue-600">${callData.destination || 'ไม่ได้ระบุ'}</span> (${callData.pax || 1} คน)
                            </span>
                        </div>
                    `;
                } else if (statusStr === 'accepted') {
                    const etaText = getEtaTextForStation(callData.station, callData.car_id || 'EV-01');
                    statusDisplay.innerHTML = `
                        <div class="flex flex-col animate-pulse">
                            <span class="text-emerald-600 font-black text-base">
                                <i class="fas fa-check-circle text-emerald-500"></i> คนขับรถตอบรับแล้ว! กำลังเดินทางไปรับคุณ
                            </span>
                            <span class="text-slate-800 text-sm font-semibold mt-1">
                                ${etaText}
                            </span>
                            <span class="text-slate-500 text-xs mt-1">
                                📍 จุดรับ: <span class="font-bold text-blue-600">${callData.station}</span> ➡️ ปลายทาง: <span class="font-bold text-blue-600">${callData.destination || 'ไม่ได้ระบุ'}</span> (${callData.pax || 1} คน)
                            </span>
                        </div>
                    `;
                } else {
                    const etaText = getEtaTextForStation(callData.station, callData.car_id);
                    statusDisplay.innerHTML = `
                        <div class="flex flex-col">
                            <span class="text-pink-600 font-black text-base animate-pulse">
                                <i class="fas fa-spinner fa-spin"></i> ระบบกำลังส่งรถไฟฟ้าไปรับคุณ...
                            </span>
                            <span class="text-slate-800 text-sm font-semibold mt-1">
                                ${etaText}
                            </span>
                            <span class="text-slate-500 text-xs mt-1">
                                📍 จุดรับ: <span class="font-bold text-blue-600">${callData.station}</span> ➡️ ปลายทาง: <span class="font-bold text-blue-600">${callData.destination || 'ไม่ได้ระบุ'}</span> (${callData.pax || 1} คน)
                            </span>
                        </div>
                    `;
                }
            } else {
                // หากรายการสิ้นสุดแล้ว ลบออกจาก sessionStorage และแสดงสถานะเริ่มต้น
                sessionStorage.removeItem('yru_my_active_call_id');
                sessionStorage.removeItem('yru_my_active_call_data');
                statusDisplay.innerHTML = `
                    <p class="text-[10px] text-slate-400 font-semibold">สถานะการให้บริการ</p>
                    <p class="text-sm font-bold text-slate-700 flex items-center gap-2">
                        <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse inline-block"></span>
                        ยังไม่มีรายการเรียกรถในขณะนี้ <span class="text-xs text-slate-500 font-normal hidden md:inline">(กดปุ่ม "เรียกรถที่นี่" เพื่อเริ่มเรียกรถไฟฟ้า)</span>
                    </p>
                `;
            }
        })
        .catch(err => console.error('Error checking call status:', err));
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
        if (status === 'normal') statusLabel = 'ปกติกำลังขับ';
        else if (status === 'pause') statusLabel = 'พักเบรค';
        else if (status === 'broken') statusLabel = 'รถขัดข้อง';
        else if (status === 'ended') statusLabel = 'สิ้นสุดรอบวิ่ง';

        globalCarStatus[tramId].status = statusLabel;
        refreshMarkerIcon(tramId);
        updateShuttleLabels();
    }

    setInterval(() => {
        fetchDriverStatusRealtime();
        fetchSeatsLeft();
    }, 4000);
    fetchDriverStatusRealtime();
    fetchSeatsLeft();
    startCallStatusPolling();

    // ===== แบบประเมินความพึงพอใจ =====
    const defaultUsers = [
    { user_id: "USR001", name: "Admin YRU", email: "admin@yru.ac.th", role: "admin", status: "ใช้งาน" },
    { user_id: "USR002", name: "ดร.สมชาย เรียนดี", email: "somchai.r@yru.ac.th", role: "executive", status: "ใช้งาน" },
    { user_id: "USR003", name: "นายอัสมี มูเล็ง", email: "asmee@yru.ac.th", role: "driver", status: "ใช้งาน" },
    { user_id: "USR004", name: "นายอัรฟาน มะเระ", email: "arfan@yru.ac.th", role: "driver", status: "ใช้งาน" },
    { user_id: "USR005", name: "นายซูเฟียน มะโอะ", email: "sufiyan@yru.ac.th", role: "driver", status: "ใช้งาน" },
    { user_id: "USR006", name: "นายอุสมาน สาและ", email: "usman@yru.ac.th", role: "driver", status: "ใช้งาน" },
    { user_id: "USR007", name: "นายบัดรี สาและ", email: "badri@yru.ac.th", role: "driver", status: "ใช้งาน" },
    { user_id: "USR008", name: "นายตอรริก ลือแมะ", email: "torik@yru.ac.th", role: "driver", status: "ใช้งาน" },
    { user_id: "USR009", name: "นายสมหวัง ใจดี", email: "somwang@yru.ac.th", role: "driver", status: "ใช้งาน" },
    { user_id: "USR010", name: "นายสมใจ ใจดี", email: "somjal@yru.ac.th", role: "driver", status: "ใช้งาน" },
    { user_id: "USR011", name: "นายกิตติ ตั้งใจ", email: "kitti@yru.ac.th", role: "driver", status: "ใช้งาน" },
    { user_id: "USR012", name: "นายรุสลัน สอเฮาะ", email: "ruslan@yru.ac.th", role: "driver", status: "ใช้งาน" }
];
    let users = getStorage("yru_users_v6", defaultUsers);
    let driverDetails = {};

    window.buildSurveyDriverOptions = function() {
        trams = getStorage("yru_trams_v18", defaultTrams);
        users = getStorage("yru_users_v9", defaultUsers);
        // เลือกเฉพาะคนขับที่ยังถูกมอบหมายให้ขับรถในปัจจุบัน
        let activeDrivers = users.filter(u => u.role === "driver" && u.status === "ใช้งาน" && trams.some(t => t.driver_id === u.user_id));

        driverDetails = {};
        const selectEl = document.getElementById('surveyDriverSelect');
        if (selectEl) {
            selectEl.innerHTML = '<option value="">-- กรุณาเลือกคนขับ --</option>';
        }

        activeDrivers.forEach(driver => {
            const assignedTram = trams.find(t => t.driver_id === driver.user_id);
            const tramCode = assignedTram ? assignedTram.id : "";
            const roleName = tramCode ? `พนักงานขับรถ ${tramCode}` : "พนักงานขับรถ";
            
            const cleanName = driver.name.replace(/\s*\(.*?\)\s*/g, '').trim();
            const avatarUrl = `https://ui-avatars.com/api/?name=${encodeURIComponent(cleanName)}&background=ec4899&color=fff&size=128`;
            
            driverDetails[driver.user_id] = {
                name: driver.name,
                role: roleName,
                avatar: avatarUrl
            };
            
            if (selectEl) {
                const optText = tramCode ? `${driver.name} — คนขับ ${tramCode}` : driver.name;
                const option = new Option(optText, driver.user_id);
                selectEl.add(option);
            }
        });
    }

    // สร้างข้อมูลเริ่มต้น
    buildSurveyDriverOptions();

    window.updateSurveyDriverCard = function(driverId) {
        const avatarImg = document.getElementById('surveyDriverAvatar');
        const nameEl = document.getElementById('surveyDriverName');
        const roleEl = document.getElementById('surveyDriverRole');
        const selectEl = document.getElementById('surveyDriverSelect');
        
        if (driverId && driverDetails[driverId]) {
            avatarImg.src = driverDetails[driverId].avatar;
            nameEl.innerText = driverDetails[driverId].name;
            roleEl.innerText = driverDetails[driverId].role;
            selectEl.value = driverId;
        } else {
            avatarImg.src = 'https://ui-avatars.com/api/?name=Driver&background=cbd5e1&color=fff&size=128';
            nameEl.innerText = 'กรุณาเลือกคนขับ';
            roleEl.innerText = 'ไม่ได้เลือกพนักงานขับรถ';
            selectEl.value = '';
        }
    }

    window.openSurveyModal = function(driverId = null) {
        // อัพเดทรายชื่อคนขับใหม่ทุกครั้งที่เปิด modal เพื่อให้ตรงกับรถในปัจจุบัน
        buildSurveyDriverOptions();

        document.getElementById('surveyModal').classList.add('active');
        document.getElementById('surveyFormSection').style.display = 'block';
        document.getElementById('surveySuccess').style.display = 'none';
        
        // reset form
        ['q1','q2','q3','q4','q5'].forEach(name => {
            document.querySelectorAll(`input[name="${name}"]`).forEach(r => r.checked = false);
        });
        document.getElementById('surveyComment').value = '';
        
        // Pre-select driver if passed
        updateSurveyDriverCard(driverId);
    }

    function closeSurveyModal() {
        document.getElementById('surveyModal').classList.remove('active');
    }

    window.submitSurvey = function() {
        const driverId = document.getElementById('surveyDriverSelect').value;
        if (!driverId) {
            alert('⚠️ กรุณาเลือกพนักงานขับรถก่อนส่งแบบประเมินครับ');
            return;
        }

        const ratings = {};
        let allAnswered = true;
        ['q1','q2','q3','q4','q5'].forEach(name => {
            const checked = document.querySelector(`input[name="${name}"]:checked`);
            if (!checked) { allAnswered = false; }
            else { ratings[name] = parseInt(checked.value); }
        });
        if (!allAnswered) {
            alert('⚠️ กรุณาประเมินทุกหัวข้อก่อนส่งแบบประเมินครับ');
            return;
        }
        const comment = document.getElementById('surveyComment').value.trim();
        const avg = (ratings.q1 + ratings.q2 + ratings.q3 + ratings.q4 + ratings.q5) / 5;

        // ── บันทึกผลประเมินลง localStorage เพื่อส่งต่อหน้าผู้บริหาร ──
        const surveyEntry = {
            time: new Date().toLocaleString('th-TH'),
            date: new Date().toLocaleDateString('th-TH'), // บันทึกวันที่ประเมินแยกเฉพาะ
            driverId: driverId,
            driverName: driverDetails[driverId].name,
            ratings,
            avg: parseFloat(avg.toFixed(2)),
            comment,
            userEmail: "@php
    $surveyUserEmail = 'guest';
    try {
        if (class_exists('Illuminate\Support\Facades\Auth') && \Illuminate\Support\Facades\Auth::check()) {
            $u = \Illuminate\Support\Facades\Auth::user();
            if ($u) { $surveyUserEmail = $u->email ?? $u->username ?? 'guest'; }
        }
    } catch (\Throwable $e) {}
    echo $surveyUserEmail;
@endphp"
        };
        let allSurveys = JSON.parse(localStorage.getItem('yru_surveys') || '[]');
        allSurveys.push(surveyEntry);
        localStorage.setItem('yru_surveys', JSON.stringify(allSurveys));

        // แสดงหน้าขอบคุณ แล้วปิดหน้าต่างลง
        document.getElementById('surveyFormSection').style.display = 'none';
        document.getElementById('surveySuccess').style.display = 'block';
        setTimeout(() => {
            closeSurveyModal();
        }, 2000);
    }

    // ปิด Modal เมื่อคลิกพื้นหลัง
    document.getElementById('surveyModal').addEventListener('click', function(e) {
        if (e.target === this) closeSurveyModal();
    });

    // ===== Profile Dropdown =====
    function toggleProfileDropdown() {
        const menu = document.getElementById('profileDropdownMenu');
        const icon = document.getElementById('profileDropdownIcon');
        
        if (menu.classList.contains('hidden')) {
            // เปิด dropdown
            menu.classList.remove('hidden');
            setTimeout(() => {
                menu.classList.remove('opacity-0', 'scale-95');
                menu.classList.add('opacity-100', 'scale-100');
            }, 10);
            if(icon) icon.style.transform = 'rotate(180deg)';
        } else {
            // ปิด dropdown
            menu.classList.remove('opacity-100', 'scale-100');
            menu.classList.add('opacity-0', 'scale-95');
            setTimeout(() => {
                menu.classList.add('hidden');
            }, 200);
            if(icon) icon.style.transform = 'rotate(0deg)';
        }
    }

    // ปิด Dropdown เมื่อคลิกพื้นที่อื่น
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

    // ===== Auto sync when Admin/Operator updates vehicle status in another tab =====
    function syncRealtimeData() {
        trams = getStorage("yru_trams_v18", defaultTrams);
        trams.forEach(t => {
            if (globalCarStatus[t.id]) {
                globalCarStatus[t.id].status = t.status || 'พร้อมใช้งาน';
            }
        });
        renderVehicleCards();
        renderStationList();
        if (typeof drawStationMarkers === 'function') drawStationMarkers();
        setTimeout(applyAntiOverlap, 300);
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
        };
    } catch (e) {
        // Fallback for older browsers
    }

    // ===== Tab switching (reserved for future tabs) =====
    function switchTab(tabName) {
        // Currently only 'stations' tab
    }
</script>
</body>
</html>