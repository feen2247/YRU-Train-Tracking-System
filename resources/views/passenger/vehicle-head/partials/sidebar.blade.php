<aside id="sidebar" class="w-64 bg-gradient-to-b from-pink-400 to-pink-500 text-white p-6 absolute inset-y-0 left-0 transform -translate-x-full transition duration-200 ease-in-out z-40 md:relative md:translate-x-0 flex flex-col justify-between shrink-0 shadow-lg font-kanit">
    <div>
        <!-- Mobile close button -->
        <div class="flex justify-end items-center mb-6 md:hidden">
            <button onclick="toggleSidebar()" class="text-white p-1">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <!-- Vehicle Head View Badge -->
        <div class="bg-white/15 px-4 py-3 rounded-2xl border border-white/20 mb-6 shadow-xs">
            <div class="text-sm font-black text-white flex items-center gap-2">
                <i class="fas fa-car text-pink-100"></i>
                <span>หน่วยยานพาหนะ</span>
            </div>
            <p class="text-[10px] text-pink-100/90 mt-1 leading-snug">งานธุรการและสารบรรณ กองกลาง สำนักงานอธิการบดี</p>
        </div>

        <nav class="space-y-1.5">
            <button onclick="switchTab('maintenance')" id="btn-tab-maintenance" class="menu-btn flex items-center space-x-3 hover:bg-white/10 w-full px-4 py-2.5 rounded-lg text-left transition active-menu">
                <i class="fas fa-file-invoice-dollar w-5"></i>
                <span>ตรวจสอบและพิจารณาใบขอแจ้งซ่อมรถไฟฟ้า</span>
            </button>
        </nav>
    </div>

    <div class="text-xs text-pink-200 text-center border-t border-pink-400/30 pt-4 mt-8">
        &copy; 2026 Yala Rajabhat University
    </div>
</aside>
