<header class="h-[75px] bg-gradient-to-r from-pink-500 via-pink-500 to-pink-600 text-white px-4 md:px-6 flex justify-between items-center z-30 relative shrink-0 shadow-lg" style="border-bottom: 1.5px solid rgba(255,255,255,0.15);">
    <div class="flex items-center gap-4">
        <button onclick="toggleSidebar()" class="text-white p-2 focus:outline-none md:hidden hover:bg-white/10 rounded-lg">
            <i class="fas fa-bars text-xl"></i>
        </button>
        <!-- Logo with glow ring -->
        <div class="relative flex-shrink-0">
            <div class="absolute inset-0 bg-white/30 rounded-full blur-sm scale-110"></div>
            <img src="{{ asset('tracking-ev-logo.png') }}" onerror="this.onerror=null; this.src='{{ asset('img/tracking-ev-logo.png') }}';" alt="TRACKING YRU EV Logo"
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
            $adminEmail = 'somchai@yru.ac.th';
            $userName = 'ดร.สมชาย เรียนดี';
            $userRole = 'ผู้บริหาร';
            try {
                if (class_exists('Illuminate\Support\Facades\Auth') && \Illuminate\Support\Facades\Auth::check()) {
                    $u = \Illuminate\Support\Facades\Auth::user();
                    if ($u && (in_array(strtolower($u->role ?? ''), ['executive', 'ผู้บริหาร']) || str_contains(strtolower($u->email ?? ''), 'somchai'))) { 
                        $adminEmail = $u->email ?? $u->username ?? $adminEmail; 
                        $userName = $u->name ?? $userName;
                        $userRole = $u->role ?? $userRole;
                    }
                }
            } catch (\Throwable $e) {}
            $userRole = 'ผู้บริหาร';
            $adminInitial = 'S';
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
        <div id="profileDropdownMenu" class="absolute right-0 mt-2 w-52 rounded-xl shadow-lg bg-white ring-1 ring-black ring-opacity-5 hidden opacity-0 transform scale-95 transition-all duration-200 origin-top-right z-50 p-1 font-kanit">
            <div class="px-3 py-2 border-b border-gray-100">
                <p class="text-xs font-bold text-gray-800">{{ $userName }}</p>
                <p class="text-[10px] text-gray-400">สิทธิ์: {{ $userRole }}</p>
            </div>
            <a href="{{ url('/') }}" onclick="sessionStorage.clear(); localStorage.removeItem('yru_user_login');" class="group flex items-center px-3 py-2 text-xs text-red-600 hover:bg-red-50 rounded-lg transition-colors font-medium">
                <i class="fas fa-sign-out-alt w-4 text-center mr-2"></i> ออกจากระบบ
            </a>
        </div>
    </div>
</header>
