<aside id="sidebar" class="w-64 bg-gradient-to-b from-pink-400 to-pink-500 text-white p-6 absolute inset-y-0 left-0 transform -translate-x-full transition duration-200 ease-in-out z-40 md:relative md:translate-x-0 flex flex-col justify-between">
    <div>
        <!-- Mobile close button -->
        <div class="flex justify-end items-center mb-6 md:hidden">
            <button onclick="toggleSidebar()" class="text-white p-1">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Admin View Badge -->
        <div class="bg-white/15 px-4 py-3 rounded-2xl border border-white/20 mb-6 shadow-xs">
            <div class="text-sm font-black text-white flex items-center gap-1.5">
                ผู้ดูแลระบบ
            </div>
            <p class="text-[10px] text-pink-100/90 mt-1">จัดการระบบและข้อมูลการเดินรถ</p>
        </div>

        <nav class="space-y-1.5">
            <button onclick="navigatePage('dashboard', this)" class="menu-btn flex items-center space-x-3 hover:bg-white/10 w-full px-4 py-2.5 rounded-lg text-left transition active-menu">
                <i class="fas fa-chart-bar w-5"></i><span>รายงานสถิติ</span>
            </button>
            <button onclick="navigatePage('tram', this)" class="menu-btn flex items-center space-x-3 hover:bg-white/10 w-full px-4 py-2.5 rounded-lg text-left transition">
                <i class="fas fa-bus w-5"></i><span>จัดการรถไฟฟ้า</span>
            </button>
            <button onclick="navigatePage('gps', this)" class="menu-btn flex items-center space-x-3 hover:bg-white/10 w-full px-4 py-2.5 rounded-lg text-left transition">
                <i class="fas fa-satellite-dish w-5"></i><span>อุปกรณ์ GPS</span>
            </button>
            <button onclick="navigatePage('route', this)" class="menu-btn flex items-center space-x-3 hover:bg-white/10 w-full px-4 py-2.5 rounded-lg text-left transition">
                <i class="fas fa-map-marked-alt w-5"></i><span>จุดจอดและเส้นทาง</span>
            </button>
            <button onclick="navigatePage('users', this)" class="menu-btn flex items-center space-x-3 hover:bg-white/10 w-full px-4 py-2.5 rounded-lg text-left transition">
                <i class="fas fa-users w-5"></i><span>จัดการข้อมูลผู้ใช้งาน</span>
            </button>
            <button onclick="navigatePage('role', this)" class="menu-btn flex items-center space-x-3 hover:bg-white/10 w-full px-4 py-2.5 rounded-lg text-left transition">
                <i class="fas fa-lock w-5"></i><span>รายงานสิทธิ์การใช้งาน</span>
            </button>

        </nav>
    </div>
    <div class="text-xs text-pink-200 text-center border-t border-pink-400/30 pt-4 mt-8">
        &copy; 2026 Yala Rajabhat University
    </div>
</aside>
