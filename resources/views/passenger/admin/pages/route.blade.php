@php
    $thaiMonthsList = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
    $currentThaiFormattedDate = date('j') . ' ' . $thaiMonthsList[(int)date('n')] . ' ' . (date('Y') + 543);
@endphp
<div id="route" class="page">
            <!-- Top Header Bar -->
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-3 mb-4 bg-white p-5 rounded-2xl shadow-xs border border-gray-100">
                <div>
                    <h2 class="text-xl md:text-2xl font-black text-slate-800 tracking-tight">จัดการจุดจอดและเส้นทางเดินรถไฟฟ้า</h2>
                    <p class="text-xs md:text-sm text-slate-500 mt-1 font-medium">ระบบจัดการพิกัดจุดจอดและผูกเส้นทางเดินรถ</p>
                    <p class="text-xs md:text-sm text-pink-600 font-bold mt-1.5 flex items-center gap-1.5"><i class="fas fa-calendar-alt text-xs"></i> <span>ณ วันที่ {{ $currentThaiFormattedDate }}</span></p>
                </div>

                <div class="flex items-center gap-2">
                    <span class="bg-pink-50 text-pink-700 border border-pink-200 text-xs px-3 py-1.5 rounded-xl font-bold flex items-center gap-1.5 shadow-2xs">
                        <i class="fas fa-map-pin text-pink-500"></i> จุดจอด: <span id="intStopBadgeCount" class="text-pink-600">0</span> จุด
                    </span>
                    <span class="bg-blue-50 text-blue-700 border border-blue-200 text-xs px-3 py-1.5 rounded-xl font-bold flex items-center gap-1.5 shadow-2xs">
                        <i class="fas fa-route text-blue-500"></i> เส้นทาง: <span id="intRouteBadgeCount" class="text-blue-600">0</span> สาย
                    </span>
                </div>
            </div>

            <!-- Split Screen Container -->
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-5 h-auto lg:h-[calc(100vh-130px)] min-h-0 lg:min-h-[640px]">
                
                <!-- ========================================== -->
                <!-- LEFT PANEL: 40% (Control Panel with Tabs)  -->
                <!-- ========================================== -->
                <div class="lg:col-span-5 flex flex-col h-auto lg:h-full bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden">
                    
                    <!-- Tabs Navigation Bar -->
                    <div class="flex border-b border-gray-100 bg-gray-50/70 p-1.5 gap-1.5">
                        <button onclick="switchIntegratedTab('stops')" id="int-tab-btn-stops"
                            class="flex-1 py-2.5 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 bg-pink-600 text-white shadow-sm">
                            1. จัดการจุดจอด
                        </button>
                        <button onclick="switchIntegratedTab('routes')" id="int-tab-btn-routes"
                            class="flex-1 py-2.5 px-3 rounded-xl text-xs font-bold transition flex items-center justify-center gap-2 text-gray-600 hover:bg-gray-200/60">
                            2. จัดการเส้นทาง
                        </button>
                    </div>

                    <!-- Panel Content Area (Scrollable) -->
                    <div class="flex-1 overflow-y-auto p-4 space-y-4">

                        <!-- ========================================== -->
                        <!-- TAB 1: จัดการจุดจอด (STOPS MANAGEMENT)     -->
                        <!-- ========================================== -->
                        <div id="int-tab-content-stops" class="space-y-4">
                            
                            <!-- Stop Form Box -->
                            <div class="bg-gray-50/80 border border-gray-200/80 rounded-2xl p-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2" id="stopFormHeading">
                                        <i class="fas fa-plus-circle text-pink-600"></i> เพิ่มจุดจอดใหม่
                                    </h3>
                                    <button type="button" onclick="resetStopForm()" id="btnResetStopForm" class="text-xs text-gray-400 hover:text-pink-600 font-semibold hidden">
                                        <i class="fas fa-undo mr-1"></i> ล้างฟอร์ม
                                    </button>
                                </div>
                                <input type="hidden" id="intStopEditIndex" value="-1">

                                <!-- Stop Name Input -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">
                                        ชื่อจุดจอดรถไฟฟ้า <span class="text-pink-600">*</span>
                                    </label>
                                    <input type="text" id="intStopName"
                                        class="w-full bg-white border border-gray-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-pink-500 focus:border-pink-500 outline-none transition"
                                        placeholder="เช่น จุดจอด 1 หน้าตึกอธิการบดี">
                                </div>

                                <!-- Latitude & Longitude Inputs (Readonly / Click on Map) -->
                                <div class="grid grid-cols-2 gap-2">
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1">
                                            ละติจูด (Lat) <span class="text-pink-600">*</span>
                                        </label>
                                        <input type="text" id="intStopLat" readonly
                                            class="w-full bg-gray-100 border border-gray-200 rounded-xl px-3 py-2 text-xs font-mono text-gray-600 outline-none cursor-not-allowed"
                                            placeholder="คลิกเลือกบนแผนที่">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-bold text-gray-700 mb-1">
                                            ลองจิจูด (Lng) <span class="text-pink-600">*</span>
                                        </label>
                                        <input type="text" id="intStopLng" readonly
                                            class="w-full bg-gray-100 border border-gray-200 rounded-xl px-3 py-2 text-xs font-mono text-gray-600 outline-none cursor-not-allowed"
                                            placeholder="คลิกเลือกบนแผนที่">
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <button type="button" onclick="saveIntegratedStop()" id="btnSaveStop"
                                    class="w-full bg-pink-600 hover:bg-pink-700 text-white font-bold py-2.5 rounded-xl text-xs shadow-md transition flex items-center justify-center gap-2">
                                    <i class="fas fa-save"></i> บันทึก
                                </button>
                            </div>

                            <!-- Existing Stops List -->
                            <div class="space-y-2">
                                <div class="flex items-center justify-between px-1">
                                    <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider">รายการจุดจอดที่มีในระบบ</h4>
                                    <span class="text-[10px] text-gray-400 font-medium">คลิกเพื่อดูหมุดบนแผนที่</span>
                                </div>
                                <div id="intStopsListContainer" class="space-y-2 max-h-[240px] overflow-y-auto pr-1">
                                    <!-- Rendered dynamically via JS -->
                                </div>
                            </div>
                        </div>

                        <!-- ========================================== -->
                        <!-- TAB 2: จัดการเส้นทาง (ROUTE BUILDER)        -->
                        <!-- ========================================== -->
                        <div id="int-tab-content-routes" class="space-y-4 hidden">
                            
                            <!-- Route Form Box -->
                            <div class="bg-gray-50/80 border border-gray-200/80 rounded-2xl p-4 space-y-3">
                                <div class="flex items-center justify-between">
                                    <h3 class="text-sm font-bold text-gray-800 flex items-center gap-2" id="routeFormHeading">
                                        <i class="fas fa-route text-pink-600"></i> สร้างเส้นทางใหม่
                                    </h3>
                                    <button type="button" onclick="resetRouteForm()" id="btnResetRouteForm" class="text-xs text-gray-400 hover:text-pink-600 font-semibold hidden">
                                        <i class="fas fa-undo mr-1"></i> ล้างฟอร์ม
                                    </button>
                                </div>
                                <input type="hidden" id="intRouteEditIndex" value="-1">

                                <!-- Route Code -->
                                <div>
                                    <label class="block text-xs font-bold text-gray-700 mb-1">
                                        รหัส / ชื่อเส้นทาง <span class="text-pink-600">*</span>
                                    </label>
                                    <input type="text" id="intRouteCode"
                                        class="w-full bg-white border border-gray-300 rounded-xl px-3 py-2 text-xs focus:ring-2 focus:ring-pink-500 outline-none transition"
                                        placeholder="เช่น LINE-A (รอบเมือง)">
                                    <input type="hidden" id="intRouteColor" value="#E91E63">
                                </div>

                                <!-- Route Stops Selector & Ordering -->
                                <div>
                                    <div class="flex items-center justify-between mb-1">
                                        <label class="block text-xs font-bold text-gray-700">
                                            เลือกและจัดลำดับจุดจอดในเส้นทาง
                                        </label>
                                        <span class="text-[10px] text-pink-600 font-bold" id="intSelectedStopsBadge">เลือก 0 จุด</span>
                                    </div>
                                    <p class="text-[11px] text-gray-400 mb-2">ติ๊กถูกเพื่อรวมเข้าเส้นทาง และกดลูกศร ⬆️ ⬇️ เพื่อเรียงลำดับ</p>
                                    
                                    <div id="intRouteStopsChecklist" class="bg-white border border-gray-200 rounded-xl p-2 max-h-48 overflow-y-auto space-y-1.5">
                                        <!-- Rendered dynamically via JS -->
                                    </div>
                                </div>

                                <!-- Submit Button -->
                                <button type="button" onclick="saveIntegratedRoute()" id="btnSaveRoute"
                                    class="w-full bg-pink-600 hover:bg-pink-700 text-white font-bold py-2.5 rounded-xl text-xs shadow-md transition flex items-center justify-center gap-2">
                                    <i class="fas fa-save"></i> บันทึก
                                </button>
                            </div>

                            <!-- Existing Routes List -->
                            <div class="space-y-2">
                                <h4 class="text-xs font-bold text-gray-700 uppercase tracking-wider px-1">เส้นทางที่มีในระบบ</h4>
                                <div id="intRoutesListContainer" class="space-y-2 max-h-[220px] overflow-y-auto pr-1">
                                    <!-- Rendered dynamically via JS -->
                                </div>
                            </div>
                        </div>

                    </div>
                </div>

                <!-- ========================================== -->
                <!-- RIGHT PANEL: 60% (Interactive Leaflet Map) -->
                <!-- ========================================== -->
                <div class="lg:col-span-7 flex flex-col h-auto lg:h-full bg-white rounded-2xl shadow-sm border border-gray-100 overflow-hidden relative">
                    
                    <!-- Map Top Instruction Bar -->
                    <div class="bg-gray-900/90 text-white px-4 py-2 text-xs flex flex-wrap items-center justify-between z-20 shadow-md gap-2">
                        <div class="flex items-center gap-2">
                            <i class="fas fa-info-circle text-pink-400 text-sm"></i>
                            <span id="intMapInstructionText" class="font-medium">
                                <i class="fas fa-map-pin text-pink-400 mr-1"></i> คลิกที่ใดก็ได้บนแผนที่ เพื่อดึงพิกัดใส่ช่อง Lat/Lng อัตโนมัติ
                            </span>
                        </div>
                        <div class="flex items-center gap-1.5">
                            <div id="intRouteDrawControls" class="hidden flex items-center gap-1.5">
                                <button type="button" onclick="undoRouteWaypoint()" title="ย้อนกลับ 1 จุด"
                                    class="bg-amber-500/80 hover:bg-amber-500 text-white px-2.5 py-1 rounded-lg transition text-[11px] font-semibold flex items-center gap-1">
                                    <i class="fas fa-undo"></i> ย้อนกลับ
                                </button>
                                <button type="button" onclick="clearRouteWaypoints()" title="ล้างเส้นทางวาด"
                                    class="bg-red-500/80 hover:bg-red-500 text-white px-2.5 py-1 rounded-lg transition text-[11px] font-semibold flex items-center gap-1">
                                    <i class="fas fa-trash-alt"></i> ล้างเส้นวาด
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Leaflet Map Canvas -->
                    <div id="integratedMap" class="w-full z-10 rounded-b-2xl"></div>

                    <!-- Floating Notification Toast -->
                    <div id="intMapNotification" class="absolute bottom-4 left-4 right-4 sm:left-auto sm:right-4 z-30 hidden transition-all">
                        <div class="bg-gray-900/95 text-white px-4 py-3 rounded-xl shadow-2xl border border-pink-500/40 flex items-center gap-3">
                            <div class="w-8 h-8 rounded-full bg-pink-500/20 text-pink-400 flex items-center justify-center flex-shrink-0">
                                <i class="fas fa-check text-sm"></i>
                            </div>
                            <div>
                                <h5 class="text-xs font-bold text-pink-300" id="intToastTitle">ดึงพิกัดสำเร็จ!</h5>
                                <p class="text-[11px] text-gray-200 font-mono mt-0.5" id="intToastText">Lat: 6.5499, Lng: 101.2912</p>
                            </div>
                        </div>
                    </div>
                </div>

            </div>
        </div>