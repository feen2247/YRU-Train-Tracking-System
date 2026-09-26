@php
    $thaiMonthsList = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
    $currentThaiFormattedDate = date('j') . ' ' . $thaiMonthsList[(int)date('n')] . ' ' . (date('Y') + 543);
@endphp
<div id="dashboard" class="page space-y-6 block font-kanit">
    <!-- ═══════════════════════════════════════════════════════════════════════════ -->
    <!-- 1. DASHBOARD HEADER BAR & CONTROL PANEL                                    -->
    <!-- ═══════════════════════════════════════════════════════════════════════════ -->
    <div class="flex flex-col xl:flex-row justify-between items-start xl:items-center gap-4 bg-white p-4 sm:p-5 md:p-6 rounded-2xl shadow-xs border border-gray-100">
        <div class="w-full xl:w-auto">
            <div class="flex items-center gap-2.5">
                <h2 class="text-xl md:text-2xl font-black text-slate-800 tracking-tight">รายงานสถิติภาพรวม</h2>
                <span class="text-[10px] font-bold text-emerald-600 bg-emerald-50 border border-emerald-200 px-2.5 py-0.5 rounded-full flex items-center gap-1.5 shadow-2xs shrink-0">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-500"></span>
                    </span>
                    Live Sync
                </span>
            </div>
            <p class="text-xs md:text-sm text-slate-500 mt-1 font-medium">ภาพรวมการให้บริการ รถไฟฟ้า สถานะการเดินรถ และการวิเคราะห์สถิติผู้โดยสารเชิงลึก</p>
            <p class="text-xs md:text-sm text-pink-600 font-bold mt-1.5 flex items-center gap-1.5"><i class="fas fa-calendar-alt text-xs"></i> <span>ณ วันที่ {{ $currentThaiFormattedDate }}</span></p>
        </div>
        
        <!-- Controls & Filters Container -->
        <div class="flex flex-wrap items-center justify-start xl:justify-end gap-2 sm:gap-2.5 w-full xl:w-auto">
            <!-- Quick Preset Buttons -->
            <div class="flex items-center gap-1 bg-pink-50/60 p-1 rounded-xl border border-pink-100/80 text-xs max-w-full overflow-x-auto scrollbar-none">
                <button type="button" id="btn-preset-today" onclick="applyQuickPreset('today')" class="px-2.5 py-1.5 rounded-lg text-slate-600 font-semibold hover:text-slate-900 transition cursor-pointer whitespace-nowrap">วันนี้</button>
                <button type="button" id="btn-preset-week" onclick="applyQuickPreset('this_week')" class="px-2.5 py-1.5 rounded-lg text-slate-600 font-semibold hover:text-slate-900 transition cursor-pointer whitespace-nowrap">สัปดาห์</button>
                
                <!-- ปุ่มเดือน พร้อมเมนูดรอปดาวน์เลือก 12 เดือนข้างใน -->
                <div class="relative inline-block" id="dash-month-dropdown-container">
                    <button type="button" id="btn-preset-month" onclick="toggleDashMonthDropdown(event)" class="px-2.5 py-1.5 rounded-lg text-slate-600 font-semibold hover:text-slate-900 transition cursor-pointer whitespace-nowrap flex items-center gap-1">
                        <span id="dash-month-btn-label">เดือน</span>
                        <i class="fas fa-chevron-down text-[9px] text-slate-400 transition-transform" id="dash-month-chevron"></i>
                    </button>

                    <!-- เมนูเลือกเดือน 1-12 ด้านใน -->
                    <div id="dash-month-dropdown-menu" class="hidden absolute left-0 sm:left-auto sm:right-0 top-full mt-1.5 w-52 max-w-[calc(100vw-2rem)] bg-white rounded-2xl shadow-xl border border-pink-100 p-2 z-50 animate-in fade-in duration-150">
                        <div class="text-[10px] font-bold text-pink-600 px-2 py-1 mb-1.5 border-b border-pink-50 flex items-center justify-between">
                            <span>📅 เลือกเดือนที่ต้องการดู</span>
                        </div>
                        <div class="grid grid-cols-2 gap-1 text-xs">
                            @for ($m = 1; $m <= 12; $m++)
                                @php
                                    $mPad = str_pad($m, 2, '0', STR_PAD_LEFT);
                                    $isCur = ($m === (int)date('n'));
                                @endphp
                                <button type="button" onclick="selectSpecificMonth('{{ $mPad }}', '{{ $thaiMonthsList[$m] }}')" class="px-2.5 py-1.5 rounded-xl text-left text-slate-700 hover:bg-pink-50 hover:text-pink-600 font-medium transition flex items-center justify-between cursor-pointer {{ $isCur ? 'bg-pink-50/70 font-bold text-pink-600' : '' }}">
                                    <span>{{ $thaiMonthsList[$m] }}</span>
                                    @if ($isCur)
                                        <span class="w-1.5 h-1.5 rounded-full bg-pink-500"></span>
                                    @endif
                                </button>
                            @endfor
                        </div>
                    </div>
                </div>

                <!-- ปุ่มปี พร้อมเมนูดรอปดาวน์เลือกปี พ.ศ. ข้างใน -->
                <div class="relative inline-block" id="dash-year-dropdown-container">
                    <button type="button" id="btn-preset-year" onclick="toggleDashYearDropdown(event)" class="px-2.5 py-1.5 rounded-lg text-slate-600 font-semibold hover:text-slate-900 transition cursor-pointer whitespace-nowrap flex items-center gap-1">
                        <span id="dash-year-btn-label">ปี</span>
                        <i class="fas fa-chevron-down text-[9px] text-slate-400 transition-transform" id="dash-year-chevron"></i>
                    </button>

                    <!-- เมนูเลือกปี พ.ศ. ด้านใน -->
                    <div id="dash-year-dropdown-menu" class="hidden absolute left-0 sm:left-auto sm:right-0 top-full mt-1.5 w-44 max-w-[calc(100vw-2rem)] bg-white rounded-2xl shadow-xl border border-pink-100 p-2 z-50 animate-in fade-in duration-150">
                        <div class="text-[10px] font-bold text-pink-600 px-2 py-1 mb-1.5 border-b border-pink-50 flex items-center justify-between">
                            <span>📅 เลือกปี พ.ศ. ที่ต้องการดู</span>
                        </div>
                        <div class="flex flex-col gap-1 text-xs">
                            @php
                                $curYearAD = (int)date('Y');
                            @endphp
                            @for ($yOffset = 0; $yOffset < 5; $yOffset++)
                                @php
                                    $adYear = $curYearAD - $yOffset;
                                    $beYear = $adYear + 543;
                                    $isCurY = ($yOffset === 0);
                                @endphp
                                <button type="button" onclick="selectSpecificYear('{{ $adYear }}', '{{ $beYear }}')" class="px-3 py-1.5 rounded-xl text-left text-slate-700 hover:bg-pink-50 hover:text-pink-600 font-medium transition flex items-center justify-between cursor-pointer {{ $isCurY ? 'bg-pink-50/70 font-bold text-pink-600' : '' }}">
                                    <span>พ.ศ. {{ $beYear }}</span>
                                    @if ($isCurY)
                                        <span class="text-[10px] bg-pink-100 text-pink-600 px-1.5 py-0.5 rounded-md font-bold">ปีปัจจุบัน</span>
                                    @endif
                                </button>
                            @endfor
                        </div>
                    </div>
                </div>
                <button type="button" id="btn-preset-all" onclick="applyQuickPreset('all')" class="px-2.5 py-1.5 rounded-lg bg-pink-600 text-white font-bold shadow-xs transition cursor-pointer whitespace-nowrap">ทั้งหมด</button>
            </div>

            <!-- Custom Date Range Picker -->
            <div class="flex flex-wrap sm:flex-nowrap items-center gap-1.5 bg-pink-50/50 p-1.5 sm:p-1 rounded-xl border border-pink-100 text-xs w-full sm:w-auto">
                <span class="text-pink-700 font-bold px-1 flex items-center gap-1 whitespace-nowrap">
                    <i class="far fa-calendar-alt text-pink-600"></i> ช่วงวันที่:
                </span>
                <div class="flex items-center gap-1.5 w-full sm:w-auto">
                    <input type="date" id="dash-filter-from" onchange="onDashDateInputChange()" class="bg-white border border-pink-200 rounded-lg px-2 py-1 text-slate-700 text-xs focus:ring-2 focus:ring-pink-400 outline-none font-medium cursor-pointer shadow-2xs w-full sm:w-auto min-w-0 max-w-[135px]">
                    <span class="text-pink-400 font-bold shrink-0">ถึง</span>
                    <input type="date" id="dash-filter-to" onchange="onDashDateInputChange()" class="bg-white border border-pink-200 rounded-lg px-2 py-1 text-slate-700 text-xs focus:ring-2 focus:ring-pink-400 outline-none font-medium cursor-pointer shadow-2xs w-full sm:w-auto min-w-0 max-w-[135px]">
                    <button onclick="resetDashDateFilter()" title="รีเซ็ตวันที่" class="bg-white hover:bg-pink-100 text-pink-600 border border-pink-200 rounded-lg px-2 py-1 transition flex items-center justify-center text-xs font-bold active:scale-95 shadow-2xs cursor-pointer shrink-0">
                        <i class="fas fa-undo"></i>
                    </button>
                </div>
            </div>

            <!-- Export Buttons -->
            <div class="flex items-center gap-1.5 w-full sm:w-auto justify-end sm:justify-start">
                <button onclick="exportAnalyticsReport('excel')" class="bg-emerald-600 hover:bg-emerald-700 text-white px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs hover:shadow active:scale-95 cursor-pointer whitespace-nowrap">
                    <i class="fas fa-file-excel text-xs"></i> Excel
                </button>
                <button onclick="exportAnalyticsReport('pdf')" class="bg-slate-800 hover:bg-slate-900 text-white px-3 py-1.5 rounded-xl text-xs font-bold transition flex items-center gap-1.5 shadow-xs hover:shadow active:scale-95 cursor-pointer whitespace-nowrap">
                    <i class="fas fa-file-pdf text-xs text-rose-400"></i> PDF Report
                </button>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════════════ -->
    <!-- 2. ENTERPRISE KPI METRIC CARDS GRID (6 CARDS)                              -->
    <!-- ═══════════════════════════════════════════════════════════════════════════ -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-6 gap-4">
        <!-- KPI 1: จำนวนรอบการเดินรถทั้งหมด -->
        <div class="bg-white p-5 rounded-2xl shadow-xs border border-gray-100 flex flex-col justify-between hover:shadow-md hover:-translate-y-0.5 transition group">
            <div class="flex items-center justify-between">
                <p class="text-xs text-slate-500 font-bold">รอบเดินรถทั้งหมด</p>
                <div class="w-8 h-8 rounded-xl bg-pink-50 text-pink-600 flex items-center justify-center group-hover:bg-pink-600 group-hover:text-white transition">
                    <i class="fas fa-route text-sm"></i>
                </div>
            </div>
            <div class="mt-3">
                <h3 id="dash-total-users" class="text-2xl font-black text-slate-800 tracking-tight">82 รอบ</h3>
                <p class="text-[11px] text-emerald-600 font-bold mt-1 flex items-center gap-1">
                    <i class="fas fa-arrow-up text-[10px]"></i> +12% จากเมื่อวาน
                </p>
            </div>
        </div>

        <!-- KPI 2: รถไฟฟ้าพร้อมใช้งาน -->
        <div onclick="showPage('tram')" class="bg-white p-5 rounded-2xl shadow-xs border border-gray-100 flex flex-col justify-between hover:shadow-md hover:-translate-y-0.5 transition cursor-pointer group">
            <div class="flex items-center justify-between">
                <p class="text-xs text-slate-500 font-bold">รถพร้อมใช้งาน</p>
                <div class="w-8 h-8 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center group-hover:bg-emerald-600 group-hover:text-white transition">
                    <i class="fas fa-bus text-sm"></i>
                </div>
            </div>
            <div class="mt-3">
                <h3 id="dash-active-trams" class="text-2xl font-black text-slate-800 tracking-tight">8 คัน</h3>
                <p id="dash-active-trams-sub" class="text-[11px] text-emerald-600 font-bold mt-1 flex items-center gap-1">
                    <i class="fas fa-check-circle text-[10px]"></i> พร้อมให้บริการ 80%
                </p>
            </div>
        </div>

        <!-- KPI 3: จุดจอดรถไฟฟ้าทั้งหมด -->
        <div onclick="showPage('route')" class="bg-white p-5 rounded-2xl shadow-xs border border-gray-100 flex flex-col justify-between hover:shadow-md hover:-translate-y-0.5 transition cursor-pointer group">
            <div class="flex items-center justify-between">
                <p class="text-xs text-slate-500 font-bold">จุดจอดทั้งหมด</p>
                <div class="w-8 h-8 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center group-hover:bg-amber-600 group-hover:text-white transition">
                    <i class="fas fa-map-marker-alt text-sm"></i>
                </div>
            </div>
            <div class="mt-3">
                <h3 id="dash-total-stations" class="text-2xl font-black text-slate-800 tracking-tight">7 จุด</h3>
                <p class="text-[11px] text-slate-400 font-semibold mt-1">ครอบคลุมทั้งมหาวิทยาลัย</p>
            </div>
        </div>

        <!-- KPI 4: อัตราการใช้งานรถไฟฟ้า (Fleet Utilization Rate %) -->
        <div class="bg-white p-5 rounded-2xl shadow-xs border border-gray-100 flex flex-col justify-between hover:shadow-md hover:-translate-y-0.5 transition group">
            <div class="flex items-center justify-between">
                <p class="text-xs text-slate-500 font-bold">อัตราการใช้งานรถ</p>
                <div class="w-8 h-8 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center group-hover:bg-blue-600 group-hover:text-white transition">
                    <i class="fas fa-tachometer-alt text-sm"></i>
                </div>
            </div>
            <div class="mt-3">
                <h3 id="dash-utilization-rate" class="text-2xl font-black text-slate-800 tracking-tight">88.5%</h3>
                <p class="text-[11px] text-blue-600 font-bold mt-1 flex items-center gap-1">
                    <i class="fas fa-chart-line text-[10px]"></i> ประสิทธิภาพสูง
                </p>
            </div>
        </div>

        <!-- KPI 5: จำนวนผู้โดยสารรวม (Total Passengers) -->
        <div class="bg-white p-5 rounded-2xl shadow-xs border border-gray-100 flex flex-col justify-between hover:shadow-md hover:-translate-y-0.5 transition group">
            <div class="flex items-center justify-between">
                <p class="text-xs text-slate-500 font-bold">ผู้โดยสารรวมสะสม</p>
                <div class="w-8 h-8 rounded-xl bg-purple-50 text-purple-600 flex items-center justify-center group-hover:bg-purple-600 group-hover:text-white transition">
                    <i class="fas fa-users text-sm"></i>
                </div>
            </div>
            <div class="mt-3">
                <h3 id="dash-total-passengers" class="text-2xl font-black text-slate-800 tracking-tight">131 คน</h3>
                <p class="text-[11px] text-purple-600 font-bold mt-1 flex items-center gap-1">
                    <i class="fas fa-user-plus text-[10px]"></i> <span id="dash-avg-passengers">เฉลี่ย 44 คน/วัน</span>
                </p>
            </div>
        </div>

        <!-- KPI 6: อัตราความตรงต่อเวลา (On-Time Performance %) -->
        <div class="bg-white p-5 rounded-2xl shadow-xs border border-gray-100 flex flex-col justify-between hover:shadow-md hover:-translate-y-0.5 transition group">
            <div class="flex items-center justify-between">
                <p class="text-xs text-slate-500 font-bold">ความตรงต่อเวลา</p>
                <div class="w-8 h-8 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center group-hover:bg-indigo-600 group-hover:text-white transition">
                    <i class="fas fa-clock text-sm"></i>
                </div>
            </div>
            <div class="mt-3">
                <h3 id="dash-ontime-rate" class="text-2xl font-black text-slate-800 tracking-tight">96.2%</h3>
                <p class="text-[11px] text-emerald-600 font-bold mt-1 flex items-center gap-1">
                    <i class="fas fa-check-double text-[10px]"></i> อยู่ในเกณฑ์ดีเยี่ยม
                </p>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════════════ -->
    <!-- 3. ANALYTICS CHARTS - GRID 2 COLUMNS                                      -->
    <!-- ═══════════════════════════════════════════════════════════════════════════ -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        <!-- Chart 1: Peak Hours Bar Chart -->
        <div class="bg-white p-6 md:p-7 rounded-2xl shadow-xs border border-gray-100 flex flex-col justify-between hover:shadow-md transition">
            <div>
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                    <div>
                        <h3 class="font-extrabold text-base text-slate-800 flex items-center gap-2">
                            <i class="far fa-clock text-amber-500"></i> ช่วงเวลาที่มีผู้โดยสารหนาแน่นที่สุด (Peak Hours)
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">จำแนกตามความหนาแน่นของผู้โดยสารในแต่ละชั่วโมง</p>
                    </div>
                    <span class="text-[10px] bg-amber-50 text-amber-700 font-bold px-2.5 py-1 rounded-full border border-amber-200 shadow-2xs">
                        ⚡ Demand Analytics
                    </span>
                </div>
                <div class="h-60 relative">
                    <canvas id="peakHoursChart"></canvas>
                </div>
            </div>
            <div class="bg-amber-50/80 border border-amber-200/80 rounded-xl p-3.5 mt-4 text-xs text-amber-900 flex items-start gap-2.5 shadow-2xs">
                <i class="fas fa-lightbulb text-amber-500 text-base mt-0.5"></i>
                <div>
                    <span class="font-bold text-amber-900">คำแนะนำระบบ AI ในการจัดคิวรถ:</span>
                    <span id="peak-hours-insight" class="block mt-0.5 text-[11px] leading-relaxed">ช่วงเวลาหนาแน่นสูงสุดคือ 08:00 - 10:00 น. และ 16:00 - 18:00 น. แนะนำให้เพิ่มความถี่วิ่งรถเป็นทุก 5 นาที</span>
                </div>
            </div>
        </div>

        <!-- Chart 2: Top Stations Horizontal Bar Chart -->
        <div class="bg-white p-6 md:p-7 rounded-2xl shadow-xs border border-gray-100 flex flex-col justify-between hover:shadow-md transition">
            <div>
                <div class="flex items-center justify-between border-b border-gray-100 pb-3 mb-4">
                    <div>
                        <h3 class="font-extrabold text-base text-slate-800 flex items-center gap-2">
                            <i class="fas fa-map-marker-alt text-pink-500"></i> จุดจอดที่มีผู้ใช้บริการสูงสุด (Top Stations)
                        </h3>
                        <p class="text-xs text-slate-500 mt-0.5">สถิติจุดรับ-ส่งที่ได้รับการเรียกรถและเข้าใช้บริการมากที่สุด</p>
                    </div>
                    <span class="text-[10px] bg-pink-50 text-pink-700 font-bold px-2.5 py-1 rounded-full border border-pink-200 shadow-2xs">
                        📍 Station Popularity
                    </span>
                </div>
                <div class="h-60 relative">
                    <canvas id="topStationsChart"></canvas>
                </div>
            </div>
            <div class="bg-pink-50/80 border border-pink-200/80 rounded-xl p-3.5 mt-4 text-xs text-pink-900 flex items-start gap-2.5 shadow-2xs">
                <i class="fas fa-info-circle text-pink-500 text-base mt-0.5"></i>
                <div>
                    <span class="font-bold text-pink-900">ข้อสรุปจุดจอดหลัก:</span>
                    <span id="top-stations-insight" class="block mt-0.5 text-[11px] leading-relaxed">"จุดจอด 2 ตึกศิลปะ" และ "จุดจอด 1 ประตูหลังมรย." มีปริมาณผู้ใช้บริการสูงสุดเป็นอันดับ 1</span>
                </div>
            </div>
        </div>
    </div>

    <!-- ═══════════════════════════════════════════════════════════════════════════ -->
    <!-- 4. RECENT PASSENGER CALL LOG TABLE                                         -->
    <!-- ═══════════════════════════════════════════════════════════════════════════ -->
    <div class="bg-white p-4 sm:p-6 md:p-8 rounded-2xl shadow-xs border border-gray-100 font-kanit hover:shadow-md transition mt-6">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-5 border-b border-gray-100 pb-4">
            <div class="flex items-center gap-2.5">
                <div class="w-9 h-9 bg-gradient-to-br from-purple-500 to-pink-600 rounded-xl flex items-center justify-center shadow-xs text-white shrink-0">
                    <i class="fas fa-history text-sm"></i>
                </div>
                <div>
                    <h3 class="font-extrabold text-sm sm:text-base text-slate-800 flex flex-wrap items-center gap-2">
                        <span>ประวัติการเรียกรถของผู้โดยสารล่าสุด</span>
                        <span class="text-[10px] sm:text-[11px] bg-purple-50 text-purple-700 font-bold px-2 py-0.5 rounded-full border border-purple-200 shadow-2xs">10 รายการล่าสุด</span>
                    </h3>
                    <p class="text-[10px] sm:text-xs text-slate-400 mt-0.5">ติดตามการเรียกใช้บริการรถไฟฟ้าของนักศึกษาและบุคลากรแบบเรียลไทม์</p>
                </div>
            </div>
            <div class="flex items-center gap-2 self-end sm:self-auto">
                <button onclick="renderRecentActivitiesTable()" class="bg-slate-50 text-slate-600 px-3 py-1.5 rounded-xl hover:bg-slate-100 transition text-xs font-bold flex items-center gap-1.5 border border-slate-200/80 active:scale-95 cursor-pointer">
                    <i class="fas fa-sync-alt text-[10px]"></i> รีเฟรช
                </button>
            </div>
        </div>

        <div class="overflow-x-auto w-full">
            <table class="w-full text-left border-collapse min-w-[650px]">
                <thead>
                    <tr class="border-b border-slate-100 text-slate-400 text-xs uppercase tracking-wider font-bold">
                        <th class="p-3 pl-0">เวลาที่เรียก</th>
                        <th class="p-3">ชื่อผู้เรียก</th>
                        <th class="p-3">จุดรับ - จุดส่ง</th>
                        <th class="p-3 text-center">รถที่รับงาน</th>
                        <th class="p-3 text-center">สถานะ</th>
                    </tr>
                </thead>
                <tbody id="recentActivitiesTable" class="text-sm text-gray-700 divide-y divide-slate-100">
                    <tr><td colspan="5" class="p-6 text-center text-gray-400 font-medium">กำลังโหลดประวัติการเรียกรถ...</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>
