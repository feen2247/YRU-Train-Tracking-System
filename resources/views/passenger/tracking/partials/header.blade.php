@php
    $carDriverNameMap = [
        'EV-01' => 'นายอัสมี มูเล็ง',
        'EV-02' => 'นายอัรฟาน มะเระ',
        'EV-03' => 'นายชูฟียาน มะโละ',
        'EV-04' => 'นายฮัมดี เจ๊ะซู',
        'EV-05' => 'นายบัดรี สาและ',
        'EV-06' => 'นายตอริก ลือแมะ',
        'EV-07' => 'นายสมหวัง ใจดี',
        'EV-08' => 'นายสมใจ ใจดี',
        'EV-09' => 'นายกิตติ ตั้งใจ',
        'EV-10' => 'นายรุสลัน สอเฮาะ',
    ];
    $resolvedDriverName = $carDriverNameMap[$formattedReqCar] ?? 'นายอัสมี มูเล็ง';
@endphp
<!-- Top Bar Header (Pink Theme matching Admin Header) -->
<header class="h-[75px] bg-gradient-to-r from-pink-500 via-pink-500 to-pink-600 text-white shadow-lg px-4 md:px-6 flex items-center justify-between z-50 flex-shrink-0" style="border-bottom: 1.5px solid rgba(255,255,255,0.15);">
    <div class="flex items-center gap-3">
        <!-- Logo with glow ring -->
        <div class="relative flex-shrink-0">
            <div class="absolute inset-0 bg-white/30 rounded-full blur-sm scale-110"></div>
            <img src="{{ asset('tracking-ev-logo.png') }}" onerror="this.onerror=null; this.src='{{ asset('img/tracking-ev-logo.png') }}';" alt="TRACKING YRU EV Logo" class="relative h-11 w-11 bg-white rounded-full object-contain p-1 shadow-lg ring-2 ring-white/70">
        </div>
        <!-- Divider -->
        <div class="hidden sm:block w-px h-10 bg-white/30 rounded-full"></div>
        <!-- Title block -->
        <div class="hidden sm:flex flex-col justify-center">
            <span class="font-black text-white text-sm md:text-base leading-tight tracking-wide drop-shadow-sm">ระบบติดตามเส้นทางการเดินรถไฟฟ้า</span>
            <span class="font-semibold text-white/90 text-xs md:text-sm leading-tight">มหาวิทยาลัยราชภัฏยะลา</span>
        </div>
        <span class="font-bold text-white text-base tracking-wide sm:hidden">YRU EV Tracker</span>

        <span id="car-display-code" class="hidden">EV-01</span>
        <span id="route-name-display" class="hidden">สาย 1</span>
    </div>

    <div class="flex items-center gap-3">
        <span id="gps-status-text" class="hidden">&#9679; ออนไลน์</span>

        <!-- Notification / Message Box Bell for Driver -->
        <div class="relative inline-block text-left z-50">
            <button type="button" id="header-bell-btn" onclick="openDriverInboxModal(); return false;" title="กล่องข้อความแจ้งเตือน" class="relative w-10 h-10 bg-white/20 hover:bg-white/30 active:scale-95 rounded-full text-white transition flex items-center justify-center cursor-pointer shadow-md pointer-events-auto select-none ring-1 ring-white/30">
                <i class="fas fa-bell text-base pointer-events-none"></i>
                <span id="header-return-msg-badge" class="hidden absolute -top-1 -right-1 bg-rose-500 text-white font-black text-[9px] min-w-5 h-5 px-1 rounded-full flex items-center justify-center ring-2 ring-white shadow-md animate-pulse pointer-events-none select-none">
                    0
                </span>
            </button>
        </div>

        <!-- Logged-in User Profile Dropdown -->
        <div class="relative inline-block text-left z-50" id="profileDropdownContainer">
            <button type="button" id="btnProfileDropdownToggle" onclick="toggleProfileDropdown(event)" class="flex items-center gap-2 focus:outline-none hover:bg-white/20 active:bg-white/30 transition-all rounded-full px-3 py-1.5 border border-white/25 shadow-sm cursor-pointer">
                <div class="hidden sm:flex flex-col items-end mr-1">
                    <span class="text-white text-xs font-extrabold drop-shadow-xs" id="driver-logged-name">
                        {{ $initialDriverEmail }}
                    </span>
                </div>
                <div class="w-8 h-8 rounded-full bg-white text-pink-600 flex items-center justify-center text-xs font-black shadow-md ring-2 ring-white/50" id="driver-avatar-circle">
                    {{ substr($formattedReqCar, 3) ?: '1' }}
                </div>
                <i class="fas fa-chevron-down text-white text-[10px] ml-1 transition-transform duration-200" id="profileDropdownIcon"></i>
            </button>

            <!-- Profile Dropdown Menu -->
            <div id="profileDropdownMenu" class="absolute right-0 top-full mt-2 w-64 rounded-2xl shadow-2xl bg-white border border-pink-100 hidden opacity-0 transform scale-95 transition-all duration-200 origin-top-right z-[9999999] p-2 font-kanit">
                <div class="px-3.5 py-3 border-b border-pink-100/80 mb-1 bg-gradient-to-r from-pink-50/80 to-white rounded-xl">
                    <p class="text-xs font-extrabold text-slate-800 truncate flex items-center gap-1.5" id="dropdown-driver-name">
                        <i class="fas fa-user-circle text-pink-500"></i>
                        <span>พนักงานขับรถ: <strong class="text-pink-600 font-black">{{ $resolvedDriverName }}</strong></span>
                    </p>
                    <p class="text-[11.5px] text-slate-600 font-bold mt-1 truncate flex items-center gap-1.5" id="dropdown-car-info">
                        <i class="fas fa-bus text-blue-500"></i>
                        <span>รถไฟฟ้าประจำตัว: <strong class="text-slate-800 font-extrabold">{{ $formattedReqCar }}</strong></span>
                    </p>
                </div>

                <div class="pt-1">
                    <a href="{{ url('/') }}" onclick="sessionStorage.clear(); localStorage.removeItem('yru_user_login');" class="group flex items-center px-3 py-2 text-xs text-red-600 hover:bg-red-50 rounded-xl transition-colors font-bold">
                        <i class="fas fa-sign-out-alt w-5 text-center mr-2 text-red-500"></i> ออกจากระบบ
                    </a>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Modal: กล่องข้อความแจ้งเตือนคนขับ (Driver Messages / Maintenance Return Inbox) -->
<div id="driverInboxModal" class="hidden fixed inset-0 z-[9999999] flex items-center justify-center p-3 md:p-4 bg-slate-900/60 backdrop-blur-sm font-kanit" onclick="if(event.target===this){ closeDriverInboxModal(); }">
    <div class="bg-white rounded-[2rem] shadow-2xl w-full max-w-md overflow-hidden flex flex-col max-h-[85vh] border border-pink-100/80 relative z-10" onclick="event.stopPropagation();">
        <!-- Header -->
        <div class="bg-gradient-to-r from-pink-600 via-rose-600 to-pink-500 text-white px-6 py-4 flex items-center justify-between shadow-md relative overflow-hidden">
            <div class="absolute -right-6 -bottom-6 w-24 h-24 rounded-full bg-white/10 blur-xl pointer-events-none"></div>
            <div class="flex items-center gap-3 relative z-10">
                <div class="w-10 h-10 rounded-2xl bg-white/20 border border-white/30 flex items-center justify-center text-white text-base shadow-inner shrink-0 backdrop-blur-md">
                    <i class="fas fa-bell"></i>
                </div>
                <div>
                    <h3 class="text-base font-black leading-tight drop-shadow-xs">กล่องข้อความแจ้งเตือน</h3>
                    <p class="text-[11px] text-pink-100 font-medium">แจ้งเตือนการตีกลับและสถานะการซ่อมบำรุง</p>
                </div>
            </div>
            <button type="button" onclick="closeDriverInboxModal()" class="relative z-20 text-white/80 hover:text-white bg-white/10 hover:bg-white/20 w-8 h-8 rounded-full flex items-center justify-center transition cursor-pointer active:scale-95" title="ปิดหน้าต่าง">
                <i class="fas fa-times text-sm pointer-events-none"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="p-5 overflow-y-auto space-y-4 flex-1 bg-slate-50/50" id="driverInboxContent">
            <!-- Filled dynamically by renderDriverMaintenanceStatus -->
        </div>

        <!-- Footer -->
        <div class="bg-white px-5 py-3 border-t border-slate-100 flex items-center justify-between shrink-0">
            <span class="text-[11px] text-slate-400 font-semibold flex items-center gap-1.5">
                <i class="fas fa-info-circle text-pink-500"></i> อัปเดตข้อมูลแบบเรียลไทม์
            </span>
            <button type="button" onclick="closeDriverInboxModal()" class="px-5 py-2 bg-slate-100 hover:bg-slate-200 text-slate-700 font-bold text-xs rounded-xl transition cursor-pointer active:scale-95">
                ปิด
            </button>
        </div>
    </div>
</div>

<script>
    window.openDriverInboxModal = function() {
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
    };

    window.closeDriverInboxModal = function() {
        const modal = document.getElementById('driverInboxModal');
        if (modal) {
            modal.classList.add('hidden');
            modal.style.display = 'none';
        }
    };

    window.toggleProfileDropdown = function(e) {
        if (e) {
            try { e.preventDefault(); e.stopPropagation(); } catch(err) {}
        }
        const menu = document.getElementById('profileDropdownMenu');
        const icon = document.getElementById('profileDropdownIcon');
        if (!menu) return;

        const isHidden = menu.classList.contains('hidden') || menu.style.display === 'none' || window.getComputedStyle(menu).display === 'none';
        if (isHidden) {
            menu.classList.remove('hidden', 'opacity-0', 'scale-95');
            menu.classList.add('opacity-100', 'scale-100');
            menu.style.display = 'block';
            menu.style.visibility = 'visible';
            menu.style.opacity = '1';
            menu.style.transform = 'scale(1)';
            menu.style.zIndex = '9999999';
            menu.style.pointerEvents = 'auto';
            if (icon) icon.style.transform = 'rotate(180deg)';
        } else {
            window.closeProfileDropdown();
        }
    };

    window.closeProfileDropdown = function() {
        const menu = document.getElementById('profileDropdownMenu');
        const icon = document.getElementById('profileDropdownIcon');
        if (!menu) return;

        menu.classList.remove('opacity-100', 'scale-100');
        menu.classList.add('opacity-0', 'scale-95', 'hidden');
        menu.style.display = 'none';
        if (icon) icon.style.transform = 'rotate(0deg)';
    };

    document.addEventListener('click', function(e) {
        const container = document.getElementById('profileDropdownContainer');
        if (container && !container.contains(e.target)) {
            if (typeof window.closeProfileDropdown === 'function') {
                window.closeProfileDropdown();
            }
        }
    });

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') {
            if (typeof window.closeProfileDropdown === 'function') {
                window.closeProfileDropdown();
            }
            if (typeof window.closeDriverInboxModal === 'function') {
                window.closeDriverInboxModal();
            }
        }
    });
</script>

