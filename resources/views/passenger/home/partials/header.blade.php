<header class="h-[75px] bg-gradient-to-r from-pink-500 via-pink-500 to-pink-600 text-white px-4 md:px-6 flex justify-between items-center z-[110] relative shrink-0 shadow-lg" style="border-bottom: 1.5px solid rgba(255,255,255,0.15);">
    <div class="flex items-center gap-4">
        <!-- Logo with glow ring -->
        <div class="relative flex-shrink-0">
            <div class="absolute inset-0 bg-white/20 rounded-full blur-md scale-110"></div>
            <img src="{{ asset('tracking-ev-logo.png') }}" onerror="this.onerror=null; this.src='{{ asset('img/tracking-ev-logo.png') }}';" alt="TRACKING YRU EV Logo"
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
            $authUsername = $authUser ? ($authUser->name ?? $authUser->username ?? $authUser->email ?? 'ผู้ใช้งาน') : 'ผู้ใช้งานทั่วไป';
            $authInitials = mb_strtoupper(mb_substr($authUser ? ($authUser->name ?? $authUser->username ?? 'User') : 'GN', 0, 2, 'UTF-8'), 'UTF-8');
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
