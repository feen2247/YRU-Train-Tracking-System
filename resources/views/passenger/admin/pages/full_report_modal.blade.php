<!-- Full Operational Report Modal (หน้าต่างรายงานสถิติและผลการดำเนินงานฉบับเต็ม - แบบเต็มหน้าจอ Full Screen & Pink Theme) -->
<div id="fullReportModal" class="fixed inset-0 bg-slate-900/80 backdrop-blur-sm flex items-center justify-center hidden z-50 transition-all font-sans">
    <div class="bg-white w-full h-full max-w-full max-h-full flex flex-col overflow-hidden transform transition-all rounded-none">
        
        <!-- Modal Top Bar (Sticky Header matching YRU Pink Theme) -->
        <div class="px-6 md:px-10 py-3.5 bg-gradient-to-r from-pink-500 via-pink-500 to-pink-600 text-white flex flex-wrap items-center justify-between gap-4 border-b border-pink-400/40 flex-shrink-0 shadow-md">
            <div class="flex items-center gap-3.5">
                <div class="w-10 h-10 rounded-2xl bg-white/20 flex items-center justify-center text-white border border-white/30 shadow-inner flex-shrink-0">
                    <i class="fas fa-file-invoice text-lg"></i>
                </div>
                <div>
                    <div class="flex items-center gap-2.5">
                        <h3 class="text-lg md:text-xl font-black tracking-tight text-white flex items-center gap-2">
                            รายงานสถิติและผลการดำเนินงานฉบับเต็ม
                        </h3>
                        <span class="text-[10px] bg-white/20 text-white font-bold px-2.5 py-0.5 rounded-full border border-white/30 shadow-xs">
                            Full Analytics Report
                        </span>
                    </div>
                    <p class="text-xs text-white/90 font-medium">ระบบติดตามเส้นทางการเดินรถไฟฟ้ามหาวิทยาลัยราชภัฏยะลา</p>
                </div>
            </div>

            <!-- Action Buttons -->
            <div class="flex items-center gap-2.5">
                <button onclick="printFullReport()" class="bg-white/20 hover:bg-white/30 text-white px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 border border-white/30 active:scale-95 shadow-xs">
                    <i class="fas fa-print"></i> พิมพ์รายงาน
                </button>
                <button onclick="exportFullReportPDF()" class="bg-white hover:bg-pink-50 text-pink-600 px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 active:scale-95 shadow-sm">
                    <i class="fas fa-file-pdf text-rose-500"></i> Export PDF
                </button>
                <button onclick="exportFullReportExcel()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3.5 py-2 rounded-xl text-xs font-bold transition flex items-center gap-1.5 active:scale-95 shadow-sm">
                    <i class="fas fa-file-excel"></i> Export Excel
                </button>
                <button onclick="closeFullReportModal()" class="w-9 h-9 rounded-xl bg-white/20 hover:bg-red-500 text-white flex items-center justify-center transition border border-white/20 ml-1 active:scale-95 shadow-xs" title="ปิดหน้าต่าง">
                    <i class="fas fa-times text-sm"></i>
                </button>
            </div>
        </div>

        <!-- Filter & Control Toolbar -->
        <div class="bg-pink-50/60 px-6 md:px-10 py-2.5 border-b border-pink-100 flex flex-wrap items-center justify-between gap-4 text-xs flex-shrink-0">
            <div class="flex flex-wrap items-center gap-2.5">
                <span class="font-bold text-pink-900 flex items-center gap-1.5">
                    <i class="fas fa-filter text-pink-600"></i> ตัวกรองช่วงเวลา:
                </span>
                <div class="inline-flex rounded-xl bg-white p-0.5 border border-pink-200 shadow-2xs" id="fr-preset-buttons">
                    <button onclick="setFullReportPreset('all')" id="fr-btn-all" class="px-3 py-1 rounded-lg font-bold transition text-slate-600 hover:text-pink-600">ทั้งหมด</button>
                    <button onclick="setFullReportPreset('today')" id="fr-btn-today" class="px-3 py-1 rounded-lg font-bold transition text-slate-600 hover:text-pink-600">วันนี้</button>
                    <button onclick="setFullReportPreset('7days')" id="fr-btn-7days" class="px-3 py-1 rounded-lg font-bold transition text-slate-600 hover:text-pink-600">7 วันล่าสุด</button>
                    <button onclick="setFullReportPreset('30days')" id="fr-btn-30days" class="px-3 py-1 rounded-lg font-bold transition bg-pink-600 text-white shadow-xs">30 วันล่าสุด</button>
                    <button onclick="setFullReportPreset('this_month')" id="fr-btn-this_month" class="px-3 py-1 rounded-lg font-bold transition text-slate-600 hover:text-pink-600">เดือนนี้</button>
                </div>
            </div>

            <!-- Custom Date Range -->
            <div class="flex items-center gap-2">
                <span class="text-pink-900 font-semibold">ระบุวันที่:</span>
                <input type="date" id="fr-date-from" onchange="onFullReportCustomDateChange()" class="bg-white border border-pink-200 rounded-lg px-2.5 py-1 text-slate-700 text-xs focus:ring-2 focus:ring-pink-400 outline-none font-medium shadow-2xs">
                <span class="text-pink-400 font-bold">ถึง</span>
                <input type="date" id="fr-date-to" onchange="onFullReportCustomDateChange()" class="bg-white border border-pink-200 rounded-lg px-2.5 py-1 text-slate-700 text-xs focus:ring-2 focus:ring-pink-400 outline-none font-medium shadow-2xs">
                <button onclick="resetFullReportDateFilter()" class="p-1.5 text-pink-600 hover:bg-pink-100 bg-white border border-pink-200 rounded-lg transition shadow-2xs" title="รีเซ็ตวันที่">
                    <i class="fas fa-undo text-xs"></i>
                </button>
            </div>
        </div>

        <!-- Scrollable Report Body (Optimized for Fullscreen & Print) -->
        <div class="p-4 sm:p-6 md:p-8 overflow-y-auto flex-1 bg-slate-50/70 custom-scrollbar w-full" id="fullReportPrintable">
            <div class="w-full space-y-6">
                
                <!-- 1. Formal Institutional Report Header (100% Full Width & Balanced) -->
                <div class="bg-white p-5 sm:p-6 md:p-7 rounded-2xl md:rounded-3xl border border-pink-200/90 shadow-sm flex flex-col xl:flex-row items-start xl:items-center justify-between gap-6 w-full">
                    <div class="flex items-center gap-4 sm:gap-5 min-w-0">
                        <!-- Controlled Fixed Logo Size -->
                        <div class="relative flex-shrink-0 w-14 h-14" style="width: 56px; height: 56px; max-width: 56px; max-height: 56px;">
                            <img src="{{ asset('tracking-ev-logo.png') }}" onerror="this.onerror=null; this.src='{{ asset('img/tracking-ev-logo.png') }}';" alt="TRACKING YRU EV Logo" class="w-14 h-14 object-contain p-1 bg-white rounded-full border-2 border-pink-200 shadow-sm" style="width: 56px; height: 56px; max-width: 56px; max-height: 56px; min-width: 56px; min-height: 56px;">
                        </div>
                        <div class="min-w-0">
                            <span class="text-xs font-black text-pink-600 uppercase tracking-wider block">
                                มหาวิทยาลัยราชภัฏยะลา &bull; YALA RAJABHAT UNIVERSITY
                            </span>
                            <h1 class="text-lg sm:text-xl lg:text-2xl font-black text-slate-800 tracking-tight mt-0.5 leading-snug">
                                รายงานสถิติและผลการดำเนินงานระบบรถไฟฟ้ารับ-ส่ง
                            </h1>
                            <p class="text-xs text-slate-500 mt-1 flex items-center gap-1.5 font-medium">
                                <i class="fas fa-leaf text-emerald-500"></i> ศูนย์บริหารจัดการระบบขนส่งสาธารณะอัจฉริยะและพลังงานสะอาด มหาวิทยาลัยราชภัฏยะลา
                            </p>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 sm:grid-cols-4 xl:grid-cols-2 gap-3 text-xs w-full xl:w-auto shrink-0">
                        <div class="bg-pink-50/80 p-3 rounded-xl border border-pink-100 min-w-[170px] shadow-2xs">
                            <span class="text-pink-600 font-bold block text-[10px] uppercase flex items-center gap-1">
                                <i class="far fa-calendar-alt text-pink-500"></i> ช่วงเวลาของข้อมูล:
                            </span>
                            <span id="fr-meta-period" class="font-extrabold text-slate-800 text-xs block mt-0.5">ทั้งหมด</span>
                        </div>
                        <div class="bg-pink-50/80 p-3 rounded-xl border border-pink-100 min-w-[170px] shadow-2xs">
                            <span class="text-pink-600 font-bold block text-[10px] uppercase flex items-center gap-1">
                                <i class="far fa-clock text-pink-500"></i> วันที่พิมพ์เอกสาร:
                            </span>
                            <span id="fr-meta-printed-date" class="font-bold text-slate-800 text-xs block mt-0.5">-</span>
                        </div>
                        <div class="bg-pink-50/80 p-3 rounded-xl border border-pink-100 min-w-[170px] shadow-2xs">
                            <span class="text-pink-600 font-bold block text-[10px] uppercase flex items-center gap-1">
                                <i class="fas fa-barcode text-pink-500"></i> รหัสเอกสารรายงาน:
                            </span>
                            <span id="fr-meta-doc-no" class="font-mono font-bold text-pink-600 text-xs block mt-0.5">YRU-EV-REP-2026</span>
                        </div>
                        <div class="bg-pink-50/80 p-3 rounded-xl border border-pink-100 min-w-[170px] shadow-2xs">
                            <span class="text-pink-600 font-bold block text-[10px] uppercase flex items-center gap-1">
                                <i class="fas fa-user-shield text-pink-500"></i> ผู้ออกรายงาน:
                            </span>
                            <span class="font-bold text-slate-800 text-xs block mt-0.5">ผู้ดูแลระบบ (Admin)</span>
                        </div>
                    </div>
                </div>

                <!-- 2. Section 1: Executive KPI Summary Cards -->
                <div class="space-y-4">
                    <div class="flex items-center gap-2 border-l-4 border-pink-500 pl-3">
                        <h2 class="text-base font-black text-slate-800 uppercase tracking-wide">
                            1. สรุปภาพรวมและตัวชี้วัดสำคัญ (Executive Summary & Key KPIs)
                        </h2>
                    </div>

                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4">
                        <!-- KPI 1: Total Trips -->
                        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between hover:shadow-md transition">
                            <div class="flex items-center justify-between text-slate-500 mb-2">
                                <span class="text-xs font-semibold">จำนวนรอบวิ่งรวม</span>
                                <div class="w-8 h-8 rounded-xl bg-pink-50 text-pink-600 flex items-center justify-center text-sm">
                                    <i class="fas fa-route"></i>
                                </div>
                            </div>
                            <h3 id="fr-kpi-total-rounds" class="text-3xl font-black text-slate-800 tracking-tight">0 รอบ</h3>
                            <p class="text-[11px] text-pink-600 font-semibold mt-1">เดินรถต่อเนื่องทุกวัน</p>
                        </div>

                        <!-- KPI 2: Total Passengers -->
                        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between hover:shadow-md transition">
                            <div class="flex items-center justify-between text-slate-500 mb-2">
                                <span class="text-xs font-semibold">ผู้โดยสารที่ให้บริการ</span>
                                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-sm">
                                    <i class="fas fa-users"></i>
                                </div>
                            </div>
                            <h3 id="fr-kpi-total-pax" class="text-3xl font-black text-blue-600 tracking-tight">0 คน</h3>
                            <p class="text-[11px] text-blue-600 font-semibold mt-1">เฉลี่ย 8-10 คน/รอบ</p>
                        </div>

                        <!-- KPI 3: Fleet Availability -->
                        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between hover:shadow-md transition">
                            <div class="flex items-center justify-between text-slate-500 mb-2">
                                <span class="text-xs font-semibold">ความพร้อมของขบวนรถ</span>
                                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-sm">
                                    <i class="fas fa-bus"></i>
                                </div>
                            </div>
                            <h3 id="fr-kpi-fleet-rate" class="text-3xl font-black text-emerald-600 tracking-tight">100%</h3>
                            <p id="fr-kpi-fleet-ready-text" class="text-[11px] text-emerald-600 font-semibold mt-1">พร้อมวิ่ง 10/10 คัน</p>
                        </div>

                        <!-- KPI 4: Avg Wait Time -->
                        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between hover:shadow-md transition">
                            <div class="flex items-center justify-between text-slate-500 mb-2">
                                <span class="text-xs font-semibold">เวลารอรถเฉลี่ย</span>
                                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-sm">
                                    <i class="fas fa-stopwatch"></i>
                                </div>
                            </div>
                            <h3 id="fr-kpi-avg-wait" class="text-3xl font-black text-amber-600 tracking-tight">~6.5 นาที</h3>
                            <p class="text-[11px] text-amber-600 font-semibold mt-1">ตรงตามเป้าหมาย (&lt;10 นาที)</p>
                        </div>

                        <!-- KPI 5: Environmental & CO2 -->
                        <div class="bg-white p-5 rounded-2xl border border-slate-200/80 shadow-xs flex flex-col justify-between hover:shadow-md transition">
                            <div class="flex items-center justify-between text-slate-500 mb-2">
                                <span class="text-xs font-semibold">ลดคาร์บอน (CO2)</span>
                                <div class="w-8 h-8 rounded-xl bg-teal-50 text-teal-600 flex items-center justify-center text-sm">
                                    <i class="fas fa-leaf"></i>
                                </div>
                            </div>
                            <h3 id="fr-kpi-co2-saved" class="text-3xl font-black text-teal-600 tracking-tight">0 kg</h3>
                            <p id="fr-kpi-clean-km" class="text-[11px] text-teal-600 font-semibold mt-1">0 กม. พลังงานสะอาด 100%</p>
                        </div>
                    </div>
                </div>

                <!-- 3. Section 2: Fleet Performance Breakdown Table (สถิติรายคัน EV-01 ถึง EV-10) -->
                <div class="bg-white p-6 md:p-8 rounded-3xl border border-pink-200/70 shadow-xs space-y-4">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 border-b border-slate-100 pb-4">
                        <div class="flex items-center gap-2 border-l-4 border-pink-500 pl-3">
                            <div>
                                <h2 class="text-base font-black text-slate-800 uppercase tracking-wide">
                                    2. สถิติการปฏิบัติงานของขบวนรถไฟฟ้ารายคัน (Fleet Operational Performance)
                                </h2>
                                <p class="text-xs text-slate-500 mt-0.5">บันทึกสถิติการเดินรถ จำนวนรอบ ผู้โดยสาร และระดับพลังงานของรถทั้ง 10 คัน</p>
                            </div>
                        </div>
                        <span class="text-xs bg-pink-50 text-pink-700 font-bold px-3 py-1 rounded-full border border-pink-200">
                            10 ขบวนรถ
                        </span>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-pink-50/70 text-pink-900 border-y border-pink-200/80 font-bold">
                                    <th class="p-3 text-center w-12">ลำดับ</th>
                                    <th class="p-3">รหัสรถ / ทะเบียน</th>
                                    <th class="p-3">พนักงานขับรถ</th>
                                    <th class="p-3 text-center">รอบที่วิ่งสะสม</th>
                                    <th class="p-3 text-center">ผู้โดยสารที่ให้บริการ</th>
                                    <th class="p-3 text-center">ระยะทางรวม (กม.)</th>
                                    <th class="p-3 text-center">ระดับแบตเตอรี่</th>
                                    <th class="p-3 text-center">สถานะการทำงาน</th>
                                </tr>
                            </thead>
                            <tbody id="fr-table-fleet-tbody" class="divide-y divide-slate-100 text-slate-700">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 4. Section 3 & 4: Station Traffic & Hourly Peak Hours Breakdown (Side by Side) -->
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    
                    <!-- Section 3: Station Traffic Analysis (7 จุดจอด) -->
                    <div class="bg-white p-6 md:p-8 rounded-3xl border border-pink-200/70 shadow-xs space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                            <div class="flex items-center gap-2 border-l-4 border-pink-500 pl-3">
                                <div>
                                    <h2 class="text-base font-black text-slate-800 uppercase tracking-wide">
                                        3. สถิติการใช้งานแยกตาม 7 จุดจอด
                                    </h2>
                                    <p class="text-xs text-slate-500 mt-0.5">ปริมาณผู้โดยสารและคิวเรียกใช้บริการรถไฟฟ้า</p>
                                </div>
                            </div>
                            <span class="text-xs bg-pink-50 text-pink-700 font-bold px-3 py-1 rounded-full border border-pink-200">
                                7 จุดจอด
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                <tr class="bg-pink-50/70 text-pink-900 border-y border-pink-200/80 font-bold">
                                    <th class="p-2.5 text-center w-10">ลำดับ</th>
                                    <th class="p-2.5">ชื่อจุดจอด</th>
                                    <th class="p-2.5 text-center">จำนวนการเรียก</th>
                                    <th class="p-2.5 text-center">สัดส่วน (%)</th>
                                    <th class="p-2.5 text-center">ความหนาแน่น</th>
                                </tr>
                                </thead>
                                <tbody id="fr-table-stations-tbody" class="divide-y divide-slate-100 text-slate-700">
                                    <!-- Populated dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Section 4: Hourly Demand & Peak Hours -->
                    <div class="bg-white p-6 md:p-8 rounded-3xl border border-pink-200/70 shadow-xs space-y-4">
                        <div class="flex items-center justify-between border-b border-slate-100 pb-4">
                            <div class="flex items-center gap-2 border-l-4 border-amber-500 pl-3">
                                <div>
                                    <h2 class="text-base font-black text-slate-800 uppercase tracking-wide">
                                        4. การกระจายตัวตามช่วงเวลา (Peak Hours)
                                    </h2>
                                    <p class="text-xs text-slate-500 mt-0.5">สถิติความหนาแน่นในแต่ละช่วงเวลาของวัน</p>
                                </div>
                            </div>
                            <span class="text-xs bg-amber-50 text-amber-700 font-bold px-3 py-1 rounded-full border border-amber-200">
                                6 ช่วงเวลา
                            </span>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left border-collapse text-xs">
                                <thead>
                                <tr class="bg-amber-50/70 text-amber-900 border-y border-amber-200/80 font-bold">
                                    <th class="p-2.5">ช่วงเวลา</th>
                                    <th class="p-2.5 text-center">จำนวนครั้ง</th>
                                    <th class="p-2.5 text-center">สัดส่วน (%)</th>
                                    <th class="p-2.5">คำแนะนำในการจัดรถ</th>
                                </tr>
                                </thead>
                                <tbody id="fr-table-hours-tbody" class="divide-y divide-slate-100 text-slate-700">
                                    <!-- Populated dynamically -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- 5. Section 5: Passenger Call History Log (ตารางประวัติการเรียกรถ) -->
                <div class="bg-white p-6 md:p-8 rounded-3xl border border-pink-200/70 shadow-xs space-y-4">
                    <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-2 border-b border-slate-100 pb-4">
                        <div class="flex items-center gap-2 border-l-4 border-purple-500 pl-3">
                            <div>
                                <h2 class="text-base font-black text-slate-800 uppercase tracking-wide">
                                    5. บันทึกรายการเรียกรถของผู้โดยสาร (Passenger Activity Log)
                                </h2>
                                <p class="text-xs text-slate-500 mt-0.5">บันทึกประวัติการเรียกใช้บริการรถไฟฟ้าล่าสุด</p>
                            </div>
                        </div>
                        <span id="fr-calls-count-badge" class="text-xs bg-purple-50 text-purple-700 font-bold px-3 py-1 rounded-full border border-purple-200">
                            0 รายการ
                        </span>
                    </div>

                    <div class="overflow-x-auto max-h-96 custom-scrollbar">
                        <table class="w-full text-left border-collapse text-xs">
                            <thead>
                                <tr class="bg-purple-50/70 text-purple-900 border-y border-purple-200/80 font-bold sticky top-0">
                                    <th class="p-3">วัน-เวลาที่เรียก</th>
                                    <th class="p-3">ชื่อผู้เรียก</th>
                                    <th class="p-3">จุดรับ - จุดส่ง</th>
                                    <th class="p-3 text-center">รถที่รับงาน</th>
                                    <th class="p-3 text-center">สถานะ</th>
                                </tr>
                            </thead>
                            <tbody id="fr-table-calls-tbody" class="divide-y divide-slate-100 text-slate-700">
                                <!-- Populated dynamically -->
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- 6. Section 6: Formal Report Sign-off & Signatures -->
                <div class="bg-white p-6 md:p-8 rounded-3xl border border-pink-200/70 shadow-xs space-y-4">
                    <div class="border-b border-slate-100 pb-3">
                        <h2 class="text-base font-black text-slate-800 uppercase tracking-wide">
                            6. ส่วนลงนามและรับรองรายงานผลการดำเนินงาน (Official Approval & Signatures)
                        </h2>
                    </div>

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-8 pt-4 text-xs">
                        <!-- Officer Sign-off -->
                        <div class="flex flex-col items-center justify-center p-6 border border-dashed border-pink-200 rounded-2xl bg-pink-50/30">
                            <p class="text-slate-600 font-semibold mb-10">ผู้จัดทำและตรวจสอบรายงาน</p>
                            <div class="w-56 border-b border-slate-400 mb-2"></div>
                            <p class="font-bold text-slate-800">( .............................................................. )</p>
                            <p class="text-pink-600 text-xs font-semibold mt-1">เจ้าหน้าที่ควบคุมระบบเดินรถไฟฟ้า YRU</p>
                            <p class="text-slate-400 text-[11px] mt-0.5">วันที่: ...... / ...... / ..........</p>
                        </div>

                        <!-- Executive Sign-off -->
                        <div class="flex flex-col items-center justify-center p-6 border border-dashed border-pink-200 rounded-2xl bg-pink-50/30">
                            <p class="text-slate-600 font-semibold mb-10">ผู้บริหารรับทราบและอนุมัติรายงาน</p>
                            <div class="w-56 border-b border-slate-400 mb-2"></div>
                            <p class="font-bold text-slate-800">( .............................................................. )</p>
                            <p class="text-pink-600 text-xs font-semibold mt-1">ผู้อำนวยการกองอาคารสถานที่และยานพาหนะ</p>
                            <p class="text-slate-400 text-[11px] mt-0.5">วันที่: ...... / ...... / ..........</p>
                        </div>
                    </div>
                </div>

                <!-- Print Footer Notice -->
                <div class="text-center text-xs text-slate-400 pt-2 pb-6">
                    เอกสารนี้สร้างขึ้นโดยระบบสารสนเทศติดตามและบริหารการเดินรถไฟฟ้า มหาวิทยาลัยราชภัฏยะลา &bull; ข้อมูลอัปเดตแบบเรียลไทม์
                </div>
            </div>
        </div>

        <!-- Modal Footer -->
        <div class="px-6 md:px-10 py-3.5 bg-white border-t border-pink-100 flex items-center justify-between gap-4 text-xs flex-shrink-0 shadow-sm">
            <span class="text-slate-500 font-medium">
                <i class="fas fa-info-circle text-pink-500 mr-1.5"></i> รายงานฉบับเต็มประกอบด้วยสถิติครอบคลุมการบริหารขบวนรถทั้งหมด
            </span>
            <button onclick="closeFullReportModal()" class="bg-gradient-to-r from-pink-500 to-pink-600 hover:from-pink-600 hover:to-pink-700 text-white px-6 py-2.5 rounded-xl font-bold transition active:scale-95 shadow-md shadow-pink-500/20">
                ปิดหน้าต่าง
            </button>
        </div>
    </div>
</div>
