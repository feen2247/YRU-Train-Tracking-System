<aside id="sidebar" class="w-64 bg-gradient-to-b from-pink-400 to-pink-500 text-white p-6 absolute inset-y-0 left-0 transform -translate-x-full transition duration-200 ease-in-out z-40 md:relative md:translate-x-0 flex flex-col justify-between shrink-0 font-kanit">
    <div>
        <!-- Mobile close button -->
        <div class="flex justify-end items-center mb-6 md:hidden">
            <button type="button" onclick="toggleSidebar()" class="text-white p-1 cursor-pointer">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Executive Portal Badge -->
        <div class="bg-white/15 px-4 py-3 rounded-2xl border border-white/20 mb-6 shadow-xs">
            <div class="text-sm font-black text-white flex items-center gap-1.5">
                สำหรับผู้บริหาร
            </div>
            <p class="text-[10px] text-pink-100/90 mt-1">สรุปข้อมูลเชิงสถิติและอนุมัติงานซ่อม</p>
        </div>

        <!-- Sidebar Navigation Items -->
        <nav class="space-y-2">
            <!-- 1. Executive Dashboard (รายงานสถิติ) -->
            <button type="button" id="menu-btn-exec-dash" onclick="switchExecutiveTab('exec-dash')" class="menu-btn flex items-center space-x-3 hover:bg-white/10 w-full px-4 py-3 rounded-xl text-left transition active-menu font-medium text-sm cursor-pointer">
                <i class="fas fa-chart-line w-5 text-center"></i>
                <span>รายงานสถิติ</span>
            </button>

            <!-- 2. Pending Maintenance Approvals (รายการแจ้งซ่อมที่รอการอนุมัติ) -->
            <button type="button" id="menu-btn-pending" onclick="switchExecutiveTab('pending')" class="menu-btn flex items-center space-x-3 hover:bg-white/10 w-full px-4 py-3 rounded-xl text-left transition font-medium text-sm cursor-pointer">
                <i class="fas fa-clipboard-check w-5 text-center"></i>
                <span>รายการแจ้งซ่อมที่รอการอนุมัติ</span>
                <span id="sidebar-pending-badge" class="ml-auto bg-purple-900 text-purple-100 border border-purple-400/30 text-[11px] font-black px-2 py-0.5 rounded-full shadow-sm animate-pulse">3</span>
            </button>

            <!-- 3. Driver Ratings (คะแนนประเมินพนักงานขับรถ) -->
            <button type="button" id="menu-btn-ratings" onclick="switchExecutiveTab('ratings')" class="menu-btn flex items-center space-x-3 hover:bg-white/10 w-full px-4 py-3 rounded-xl text-left transition font-medium text-sm cursor-pointer">
                <i class="fas fa-star w-5 text-center text-amber-300"></i>
                <span>คะแนนประเมินคนขับ</span>
                <span id="sidebar-ratings-badge" class="ml-auto bg-amber-400 text-slate-900 text-[10px] font-black px-2 py-0.5 rounded-full shadow-2xs">4.8★</span>
            </button>
        </nav>
    </div>

    <!-- Sidebar Footer -->
    <div class="text-xs text-pink-200 text-center border-t border-pink-400/30 pt-4 mt-8">
        &copy; 2026 Yala Rajabhat University
    </div>
</aside>
