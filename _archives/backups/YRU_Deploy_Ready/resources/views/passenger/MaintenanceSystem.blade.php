<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ระบบงานช่างและซ่อมบำรุง - YRU TRAM Admin</title>
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
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <style>
        body, button, input, select, textarea, div, span, p, a, h1, h2, h3, h4, h5, h6, label, td, th {
            font-family: 'Kanit', sans-serif !important;
        }
        /* Protect FontAwesome Icons from being overridden by Kanit */
        .fa, .fas, .far, .fab, .fa-solid, .fa-regular, .fa-brands, [class*="fa-"] {
            font-family: 'Font Awesome 6 Free', 'Font Awesome 6 Brands', 'Font Awesome 5 Free', sans-serif !important;
        }
        .theme-gradient {
            background: linear-gradient(135deg, #d81b60 0%, #ad1457 100%);
        }
        .theme-color {
            color: #d81b60;
        }
        .theme-bg {
            background-color: #d81b60;
        }
        .theme-border {
            border-color: #d81b60;
        }
    </style>
</head>
<body class="bg-slate-50 min-h-screen text-slate-800 flex flex-col">

    <!-- Navigation Header -->
    <nav class="theme-gradient text-white p-4 shadow-lg flex justify-between items-center z-10">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-full bg-white flex items-center justify-center text-pink-600 shadow-md">
                <i class="fas fa-tools text-xl"></i>
            </div>
            <div class="flex flex-col">
                <span class="text-xs text-pink-100 font-semibold tracking-wider">YRU TRAM</span>
                <h1 class="text-sm md:text-lg font-black leading-tight">ระบบงานช่างและซ่อมบำรุง (Maintenance Dashboard)</h1>
            </div>
        </div>
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2 bg-white/10 px-3.5 py-1.5 rounded-full border border-white/20">
                <div class="w-2.5 h-2.5 rounded-full bg-emerald-400 animate-pulse"></div>
                <span class="text-xs font-semibold">สวัสดี, นายช่างสมชาย</span>
            </div>
            <button onclick="logoutDemo()" class="bg-red-500/80 hover:bg-red-500 px-3.5 py-1.5 rounded-full transition text-xs font-bold flex items-center gap-1.5 border border-red-400/20 shadow-md">
                <i class="fas fa-sign-out-alt"></i> <span>ออกจากระบบ</span>
            </button>
        </div>
    </nav>

    <!-- Main Container -->
    <div class="max-w-6xl w-full mx-auto p-4 md:p-6 flex-1 flex flex-col gap-6">
        
        <!-- Header Actions and Statistics Intro -->
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
            <div>
                <h2 class="text-xl md:text-2xl font-black text-slate-800">แผงควบคุมระบบแจ้งซ่อมบำรุง</h2>
                <p class="text-slate-500 text-xs mt-0.5">จัดการสถานะความขัดข้องของรถไฟฟ้าบริการ ประวัติงานช่าง และการเช็กระยะประจำสัปดาห์</p>
            </div>
            <div class="flex gap-2.5 w-full sm:w-auto">
                <button onclick="openReportIssueModal()" class="flex-1 sm:flex-initial bg-red-600 text-white px-4 py-2 rounded-xl hover:bg-red-700 transition text-xs font-bold flex items-center justify-center gap-2 shadow-sm">
                    <i class="fas fa-bell"></i> แจ้งปัญหาชำรุดใหม่
                </button>
                <button onclick="openJobCompleteModalDirect()" class="flex-1 sm:flex-initial bg-pink-600 text-white px-4 py-2 rounded-xl hover:bg-pink-700 transition text-xs font-bold flex items-center justify-center gap-2 shadow-sm">
                    <i class="fas fa-plus"></i> บันทึกปิดเคสตรง
                </button>
            </div>
        </div>

        <!-- Summary Cards Grid -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 md:gap-6">
            <!-- waiting -->
            <div class="bg-white p-5 rounded-2xl shadow-sm border-b-4 border-red-500 flex items-center justify-between hover:shadow-md transition">
                <div>
                    <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">รถรอซ่อม</p>
                    <h3 id="stat-waiting" class="text-2xl font-black mt-1 text-slate-800">0 รายการ</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-red-50 text-red-500 flex items-center justify-center text-xl shadow-inner">
                    <i class="fas fa-exclamation-circle animate-pulse"></i>
                </div>
            </div>
            <!-- ongoing -->
            <div class="bg-white p-5 rounded-2xl shadow-sm border-b-4 border-amber-500 flex items-center justify-between hover:shadow-md transition">
                <div>
                    <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">กำลังซ่อม</p>
                    <h3 id="stat-ongoing" class="text-2xl font-black mt-1 text-slate-800">0 รายการ</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-amber-50 text-amber-500 flex items-center justify-center text-xl shadow-inner">
                    <i class="fas fa-tools"></i>
                </div>
            </div>
            <!-- completed -->
            <div class="bg-white p-5 rounded-2xl shadow-sm border-b-4 border-green-500 flex items-center justify-between hover:shadow-md transition">
                <div>
                    <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">ซ่อมเสร็จสัปดาห์นี้</p>
                    <h3 id="stat-completed" class="text-2xl font-black mt-1 text-slate-800">0 รายการ</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-green-50 text-green-500 flex items-center justify-center text-xl shadow-inner">
                    <i class="fas fa-check-circle"></i>
                </div>
            </div>
            <!-- scheduled checkup -->
            <div class="bg-white p-5 rounded-2xl shadow-sm border-b-4 border-blue-500 flex items-center justify-between hover:shadow-md transition">
                <div>
                    <p class="text-slate-400 text-[10px] font-bold uppercase tracking-wider">ถึงกำหนดเช็กระยะ</p>
                    <h3 id="stat-scheduled" class="text-2xl font-black mt-1 text-slate-800">3 คัน</h3>
                </div>
                <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-500 flex items-center justify-center text-xl shadow-inner">
                    <i class="fas fa-calendar-alt"></i>
                </div>
            </div>
        </div>

        <!-- Table Area Container -->
        <div class="bg-white rounded-2xl shadow-sm p-4 md:p-6 overflow-hidden flex flex-col flex-1 border border-slate-100">
            <!-- Tabs Navigation -->
            <div class="flex border-b mb-6 gap-2">
                <button onclick="switchTab('active')" id="tab-active-btn" class="px-4 py-2.5 font-bold text-sm border-b-2 border-pink-600 text-pink-600 focus:outline-none transition flex items-center gap-1.5">
                    <i class="fas fa-clipboard-list text-base"></i> รายการแจ้งซ่อมปัจจุบัน
                </button>
                <button onclick="switchTab('history')" id="tab-history-btn" class="px-4 py-2.5 text-sm border-b-2 border-transparent text-slate-500 hover:text-slate-700 focus:outline-none transition flex items-center gap-1.5">
                    <i class="fas fa-history text-base"></i> ประวัติการซ่อมทั้งหมด
                </button>
            </div>

            <!-- Table content wrapper -->
            <div id="table-container" class="overflow-x-auto flex-1">
                <!-- Dynamically Rendered Table -->
            </div>
        </div>

    </div>

    <!-- Footer -->
    <footer class="bg-white border-t p-4 text-center text-xs text-slate-400 font-medium">
        &copy; 2026 ระบบการจัดการบำรุงรักษารถไฟฟ้า YRU TRAM &bull; มหาวิทยาลัยราชภัฏยะลา
    </footer>

    <!-- Job Completion Modal -->
    <div id="jobCompletionModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center hidden p-4 z-50 transition-all duration-300">
        <div class="bg-white p-6 rounded-2xl shadow-xl w-full max-w-lg max-h-[90vh] overflow-y-auto border border-slate-100">
            <div class="flex justify-between items-center mb-4 border-b pb-3">
                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <i class="fas fa-clipboard-check text-pink-600"></i> บันทึกสรุปผลงานเมื่อซ่อมเสร็จ
                </h3>
                <button onclick="closeJobCompleteModal()" class="text-slate-400 hover:text-slate-600 focus:outline-none transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            
            <input type="hidden" id="modal-ticket-id">

            <div class="space-y-4 text-sm">
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1">เลือกรถไฟฟ้า <span class="text-red-500">*</span></label>
                    <select id="modal-car-select" class="w-full border border-slate-200 p-2.5 rounded-xl focus:ring-2 focus:ring-pink-400 outline-none bg-white">
                        <option value="EV-01">EV-01 (กค 1234 ยะลา)</option>
                        <option value="EV-02">EV-02 (กค 5678 ยะลา)</option>
                        <option value="EV-03">EV-03 (กค 9012 ยะลา)</option>
                        <option value="EV-04">EV-04 (กค 3456 ยะลา)</option>
                        <option value="EV-05">EV-05 (กค 7890 ยะลา)</option>
                        <option value="EV-06">EV-06 (กค 1122 ยะลา)</option>
                        <option value="EV-07">EV-07 (กค 3344 ยะลา)</option>
                        <option value="EV-08">EV-08 (กค 5566 ยะลา)</option>
                        <option value="EV-09">EV-09 (กค 7788 ยะลา)</option>
                        <option value="EV-10">EV-10 (กค 9900 ยะลา)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1">หัวข้อการซ่อม/เช็กระยะ (ย่อ) <span class="text-red-500">*</span></label>
                    <input type="text" id="modal-repair-summary" class="w-full border border-slate-200 p-2.5 rounded-xl focus:ring-2 focus:ring-pink-400 outline-none" placeholder="เช่น เปลี่ยนบอร์ดควบคุมความร้อนแบตเตอรี่, เปลี่ยนผ้าเบรก">
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1">รายละเอียดการซ่อมเชิงลึก <span class="text-red-500">*</span></label>
                    <textarea id="modal-repair-detail" rows="3" class="w-full border border-slate-200 p-2.5 rounded-xl focus:ring-2 focus:ring-pink-400 outline-none" placeholder="กรอกรายละเอียดขั้นตอนการซ่อมบำรุงและการตั้งค่าการแก้ไขอัจฉริยะ..."></textarea>
                </div>

                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1">รายการอะไหล่ที่ใช้ (เลือกได้หลายรายการ)</label>
                    <div class="grid grid-cols-2 gap-2.5 mt-1 border border-slate-150 p-3 rounded-xl bg-slate-50">
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-medium"><input type="checkbox" name="spareParts" value="ยางรถไฟฟ้า" class="rounded text-pink-600 focus:ring-pink-500"> ยางรถไฟฟ้า</label>
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-medium"><input type="checkbox" name="spareParts" value="ผ้าเบรกหน้า-หลัง" class="rounded text-pink-600 focus:ring-pink-500"> ผ้าเบรกหน้า-หลัง</label>
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-medium"><input type="checkbox" name="spareParts" value="แบตเตอรี่หลัก" class="rounded text-pink-600 focus:ring-pink-500"> แบตเตอรี่หลัก</label>
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-medium"><input type="checkbox" name="spareParts" value="มอเตอร์ไฟฟ้า" class="rounded text-pink-600 focus:ring-pink-500"> มอเตอร์ไฟฟ้า</label>
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-medium"><input type="checkbox" name="spareParts" value="บอร์ดควบคุมกระแสไฟ" class="rounded text-pink-600 focus:ring-pink-500"> บอร์ดควบคุมกระแสไฟ</label>
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-medium"><input type="checkbox" name="spareParts" value="สปริงโช้คอัพ" class="rounded text-pink-600 focus:ring-pink-500"> สปริงโช้คอัพ</label>
                        <label class="flex items-center gap-2 cursor-pointer text-xs font-medium"><input type="checkbox" name="spareParts" value="ขั้วต่อสายไฟ/ฟิวส์" class="rounded text-pink-600 focus:ring-pink-500"> ขั้วต่อสายไฟ/ฟิวส์</label>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">ชื่อช่างผู้ดูแล (Auto-filled)</label>
                        <input type="text" id="modal-repair-technician" class="w-full border border-slate-200 p-2.5 rounded-xl bg-slate-100 text-slate-400 cursor-not-allowed font-semibold" value="ช่างประสาน" readonly>
                    </div>
                    <div>
                        <label class="block text-xs font-bold text-slate-500 mb-1">วันที่ซ่อมเสร็จ <span class="text-red-500">*</span></label>
                        <input type="date" lang="en-GB" id="modal-repair-date" class="w-full border border-slate-200 p-2.5 rounded-xl focus:ring-2 focus:ring-pink-400 outline-none bg-white">
                    </div>
                </div>
            </div>
            
            <div class="flex justify-end gap-3 mt-6 border-t pt-4">
                <button onclick="closeJobCompleteModal()" class="bg-slate-100 text-slate-700 px-4 py-2 rounded-xl hover:bg-slate-200 text-sm font-semibold transition">ยกเลิก</button>
                <button onclick="saveJobCompletion()" class="bg-pink-600 hover:bg-pink-700 text-white px-5 py-2 rounded-xl text-sm font-bold transition shadow-md">บันทึกและคืนสถานะรถเป็นพร้อมใช้งาน</button>
            </div>
        </div>
    </div>

    <!-- Report Issue Modal -->
    <div id="reportIssueModal" class="fixed inset-0 bg-slate-900/60 backdrop-blur-sm flex items-center justify-center hidden p-4 z-50 transition-all duration-300">
        <div class="bg-white p-6 rounded-2xl shadow-xl w-full max-w-md border border-slate-100">
            <div class="flex justify-between items-center mb-4 border-b pb-3">
                <h3 class="text-lg font-bold text-slate-800 flex items-center gap-2">
                    <i class="fas fa-exclamation-triangle text-red-600"></i> แจ้งเรื่องขัดข้อง / ปัญหาชำรุดใหม่
                </h3>
                <button onclick="closeReportIssueModal()" class="text-slate-400 hover:text-slate-600 focus:outline-none transition">
                    <i class="fas fa-times text-lg"></i>
                </button>
            </div>
            
            <div class="space-y-4 text-sm">
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1">เลือกรถไฟฟ้า <span class="text-red-500">*</span></label>
                    <select id="report-car-select" class="w-full border border-slate-200 p-2.5 rounded-xl focus:ring-2 focus:ring-pink-400 outline-none bg-white">
                        <option value="EV-01">EV-01 (กค 1234 ยะลา)</option>
                        <option value="EV-02">EV-02 (กค 5678 ยะลา)</option>
                        <option value="EV-03">EV-03 (กค 9012 ยะลา)</option>
                        <option value="EV-04">EV-04 (กค 3456 ยะลา)</option>
                        <option value="EV-05">EV-05 (กค 7890 ยะลา)</option>
                        <option value="EV-06">EV-06 (กค 1122 ยะลา)</option>
                        <option value="EV-07">EV-07 (กค 3344 ยะลา)</option>
                        <option value="EV-08">EV-08 (กค 5566 ยะลา)</option>
                        <option value="EV-09">EV-09 (กค 7788 ยะลา)</option>
                        <option value="EV-10">EV-10 (กค 9900 ยะลา)</option>
                    </select>
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1">หัวข้ออาการขัดข้อง <span class="text-red-500">*</span></label>
                    <input type="text" id="report-summary" class="w-full border border-slate-200 p-2.5 rounded-xl focus:ring-2 focus:ring-pink-400 outline-none" placeholder="เช่น ระบบแบตเตอรี่ร้อนเกินกำหนด, เบรกเสียงดัง">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1">ผู้แจ้งเรื่อง <span class="text-red-500">*</span></label>
                    <input type="email" id="report-user" class="w-full border border-slate-200 p-2.5 rounded-xl focus:ring-2 focus:ring-pink-400 outline-none" value="somchai.r@yru.ac.th" placeholder="somchai.r@yru.ac.th">
                </div>
                <div>
                    <label class="block text-xs font-bold text-slate-500 mb-1">ความเร่งด่วน <span class="text-red-500">*</span></label>
                    <select id="report-urgency" class="w-full border border-slate-200 p-2.5 rounded-xl focus:ring-2 focus:ring-pink-400 outline-none bg-white">
                        <option value="🔴 ด่วน">🔴 ด่วน (ระงับวิ่งด่วน)</option>
                        <option value="🟡 ปกติ">🟡 ปกติ</option>
                    </select>
                </div>
            </div>
            
            <div class="flex justify-end gap-3 mt-6 border-t pt-4">
                <button onclick="closeReportIssueModal()" class="bg-slate-100 text-slate-700 px-4 py-2 rounded-xl hover:bg-slate-200 text-sm font-semibold transition">ยกเลิก</button>
                <button onclick="saveReportedIssue()" class="bg-red-600 hover:bg-red-700 text-white px-5 py-2 rounded-xl text-sm font-bold transition shadow-md">แจ้งส่งซ่อมบำรุง</button>
            </div>
        </div>
    </div>

    <!-- JS Scripts -->
    <script>
        // Default seed data
        const initialTickets = [
            {
                id: "TCK-001",
                car_id: "EV-03",
                plate: "กค 9012 ยะลา",
                issue: "ระบบแบตเตอรี่ร้อนเกินกำหนด",
                reporter: "somchai.r@yru.ac.th",
                urgency: "🔴 ด่วน",
                reported_at: "08/07/2569",
                status: "pending", // pending, ongoing, completed
                detail: "",
                spare_parts: [],
                technician: "",
                finish_date: ""
            },
            {
                id: "TCK-002",
                car_id: "EV-01",
                plate: "กค 1234 ยะลา",
                issue: "เบรกมีเสียงดังผิดปกติขณะชะลอรถ",
                reporter: "driver.ev01@yru.ac.th",
                urgency: "🟡 ปกติ",
                reported_at: "05/07/2569",
                status: "completed",
                detail: "เช็คระยะระบบขับเคลื่อน และทดสอบไฟชาร์จแบตเตอรี่ พร้อมเปลี่ยนผ้าเบรกหน้า-หลัง",
                spare_parts: ["ผ้าเบรกหน้า-หลัง"],
                technician: "ช่างประสาน",
                finish_date: "2026-07-05"
            },
            {
                id: "TCK-003",
                car_id: "EV-02",
                plate: "กค 5678 ยะลา",
                issue: "ตรวจระดับน้ำกลั่นแบตเตอรี่สำรอง",
                reporter: "driver.ev02@yru.ac.th",
                urgency: "🟡 ปกติ",
                reported_at: "01/07/2569",
                status: "completed",
                detail: "ตรวจเช็คระดับน้ำกลั่นแบตเตอรี่สำรอง และทำความสะอาดขั้วต่อกระแสไฟ",
                spare_parts: ["ขั้วต่อสายไฟ/ฟิวส์"],
                technician: "ช่างประสาน",
                finish_date: "2026-07-01"
            }
        ];

        // Local Storage Helpers
        function getStorage(key, defaultVal) {
            if (!localStorage.getItem(key)) {
                localStorage.setItem(key, JSON.stringify(defaultVal));
            }
            return JSON.parse(localStorage.getItem(key));
        }

        function setStorage(key, val) {
            localStorage.setItem(key, JSON.stringify(val));
        }

        let tickets = getStorage("yru_maintenance_tickets_v3", initialTickets);
        let activeTab = "active"; // active, history

        const carPlates = {
            "EV-01": "กค 1234 ยะลา",
            "EV-02": "กค 5678 ยะลา",
            "EV-03": "กค 9012 ยะลา",
            "EV-04": "กค 3456 ยะลา",
            "EV-05": "กค 7890 ยะลา",
            "EV-06": "กค 1122 ยะลา",
            "EV-07": "กค 3344 ยะลา",
            "EV-08": "กค 5566 ยะลา",
            "EV-09": "กค 7788 ยะลา",
            "EV-10": "กค 9900 ยะลา"
        };

        // Render Dashboard Stats and Tables
        function renderDashboard() {
            // Summary Cards calculation
            const waiting = tickets.filter(t => t.status === "pending").length;
            const ongoing = tickets.filter(t => t.status === "ongoing").length;
            const completed = tickets.filter(t => t.status === "completed").length;

            document.getElementById("stat-waiting").innerText = `${waiting} รายการ`;
            document.getElementById("stat-ongoing").innerText = `${ongoing} รายการ`;
            document.getElementById("stat-completed").innerText = `${completed} รายการ`;
            
            // Fixed or calculated checkups
            document.getElementById("stat-scheduled").innerText = `3 คัน`;

            // Table rendering
            const tableContainer = document.getElementById("table-container");
            
            if (activeTab === "active") {
                const activeTickets = tickets.filter(t => t.status === "pending" || t.status === "ongoing");
                
                if (activeTickets.length === 0) {
                    tableContainer.innerHTML = `
                        <div class="text-center py-12 text-slate-400">
                            <div class="w-16 h-16 rounded-full bg-emerald-50 text-emerald-500 flex items-center justify-center text-3xl mx-auto mb-3 shadow-inner">
                                <i class="fas fa-clipboard-check"></i>
                            </div>
                            <p class="text-sm font-semibold text-slate-700">ไม่มีรายการรถไฟฟ้าแจ้งซ่อมในขณะนี้</p>
                            <p class="text-xs text-slate-400 mt-1">รถไฟฟ้าทุกคันพร้อมปฏิบัติหน้าที่ออกให้บริการอย่างเป็นปกติ</p>
                        </div>
                    `;
                    return;
                }

                let html = `
                    <table class="w-full text-left border-collapse min-w-[900px]">
                        <thead>
                            <tr class="border-b bg-slate-50 text-slate-500 uppercase text-xs tracking-wider">
                                <th class="p-4 font-bold">รหัสรถ / ทะเบียน</th>
                                <th class="p-4 font-bold">อาการเสีย / หัวข้อ</th>
                                <th class="p-4 font-bold">ผู้แจ้งปัญหา</th>
                                <th class="p-4 text-center font-bold">ระดับความเร่งด่วน</th>
                                <th class="p-4 font-bold">วันที่แจ้ง</th>
                                <th class="p-4 text-center font-bold">สถานะ</th>
                                <th class="p-4 text-center font-bold">จัดการเคสซ่อม</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">`;

                activeTickets.forEach(t => {
                    const statusBadge = t.status === "pending" 
                        ? `<span class="bg-red-50 text-red-600 px-3 py-1 rounded-full text-xs font-bold border border-red-200 inline-block"><i class="fas fa-hourglass-start mr-1"></i>รอซ่อม</span>`
                        : `<span class="bg-amber-50 text-amber-600 px-3 py-1 rounded-full text-xs font-bold border border-amber-200 inline-block"><i class="fas fa-tools mr-1 animate-spin"></i>กำลังซ่อม</span>`;

                    const actionButtons = t.status === "pending"
                        ? `<button onclick="updateTicketStatus('${t.id}', 'ongoing')" class="bg-blue-600 hover:bg-blue-700 text-white text-xs px-3.5 py-2 rounded-xl font-bold transition shadow-sm flex items-center gap-1.5"><i class="fas fa-wrench"></i> รับเคสซ่อม</button>`
                        : `<button onclick="openJobCompleteModal('${t.id}')" class="bg-green-600 hover:bg-green-700 text-white text-xs px-3.5 py-2 rounded-xl font-bold transition shadow-sm flex items-center gap-1.5"><i class="fas fa-check-circle"></i> ปิดงานซ่อม</button>`;

                    const urgencyBadge = t.urgency.includes("ด่วน")
                        ? `<span class="bg-red-100 text-red-700 px-2.5 py-0.5 rounded-full text-xs font-bold inline-block">${t.urgency}</span>`
                        : `<span class="bg-amber-100 text-amber-700 px-2.5 py-0.5 rounded-full text-xs font-bold inline-block">${t.urgency}</span>`;

                    html += `
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="p-4 font-bold text-slate-800">${t.car_id} <span class="block text-xs font-normal text-slate-400 mt-0.5">${t.plate}</span></td>
                            <td class="p-4 text-slate-700 max-w-[200px] truncate" title="${t.issue}">${t.issue}</td>
                            <td class="p-4 text-xs font-mono text-slate-500">${t.reporter}</td>
                            <td class="p-4 text-center">${urgencyBadge}</td>
                            <td class="p-4 text-slate-500 font-semibold">${t.reported_at}</td>
                            <td class="p-4 text-center">${statusBadge}</td>
                            <td class="p-4 flex items-center justify-center">${actionButtons}</td>
                        </tr>
                    `;
                });

                html += `</tbody></table>`;
                tableContainer.innerHTML = html;
            } else {
                const historyTickets = tickets.filter(t => t.status === "completed");
                
                if (historyTickets.length === 0) {
                    tableContainer.innerHTML = `
                        <div class="text-center py-12 text-slate-400">
                            <i class="fas fa-history text-4xl mb-3"></i>
                            <p class="text-sm font-semibold">ยังไม่มีประวัติการแจ้งซ่อมที่ทำเสร็จในระบบ</p>
                        </div>
                    `;
                    return;
                }

                let html = `
                    <table class="w-full text-left border-collapse min-w-[900px]">
                        <thead>
                            <tr class="border-b bg-slate-50 text-slate-500 uppercase text-xs tracking-wider">
                                <th class="p-4 font-bold">รหัสรถ / ทะเบียน</th>
                                <th class="p-4 font-bold">อาการซ่อมเสร็จสมบูรณ์</th>
                                <th class="p-4 font-bold">ช่างผู้รับผิดชอบ</th>
                                <th class="p-4 font-bold">วันที่ซ่อมเสร็จ</th>
                                <th class="p-4 text-center font-bold">สถานะตู้</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-sm">`;

                historyTickets.forEach(t => {
                    const logDate = t.finish_date ? convertToThaiLogDate(t.finish_date) : t.reported_at;
                    const sparePartsStr = t.spare_parts && t.spare_parts.length > 0 
                        ? ` <span class="text-xs text-pink-600 block mt-1.5"><i class="fas fa-box-open mr-1"></i>อะไหล่: ${t.spare_parts.join(', ')}</span>`
                        : "";

                    html += `
                        <tr class="hover:bg-slate-50/50 transition">
                            <td class="p-4 font-bold text-slate-800">${t.car_id} <span class="block text-xs font-normal text-slate-400 mt-0.5">${t.plate}</span></td>
                            <td class="p-4 text-slate-700">
                                <span class="font-bold">${t.issue}</span>
                                <p class="text-xs text-slate-500 mt-1">${t.detail || '-'}</p>
                                ${sparePartsStr}
                            </td>
                            <td class="p-4 text-slate-500 font-semibold"><i class="fas fa-wrench mr-1 text-slate-400"></i>${t.technician || 'ช่างประสาน'}</td>
                            <td class="p-4"><span class="bg-slate-100 text-slate-600 text-[10px] px-2 py-0.5 rounded font-mono font-medium">${logDate}</span></td>
                            <td class="p-4 text-center">
                                <span class="bg-green-50 text-green-700 px-3 py-1 rounded-full text-xs font-bold border border-green-200 inline-block">
                                    <i class="fas fa-check-circle mr-1"></i>ซ่อมเสร็จสิ้น
                                </span>
                            </td>
                        </tr>
                    `;
                });

                html += `</tbody></table>`;
                tableContainer.innerHTML = html;
            }
        }

        function switchTab(tab) {
            activeTab = tab;
            const activeBtn = document.getElementById("tab-active-btn");
            const historyBtn = document.getElementById("tab-history-btn");

            if (tab === "active") {
                activeBtn.className = "px-4 py-2.5 font-bold text-sm border-b-2 border-pink-600 text-pink-600 focus:outline-none transition flex items-center gap-1.5";
                historyBtn.className = "px-4 py-2.5 text-sm border-b-2 border-transparent text-slate-500 hover:text-slate-700 focus:outline-none transition flex items-center gap-1.5";
            } else {
                activeBtn.className = "px-4 py-2.5 text-sm border-b-2 border-transparent text-slate-500 hover:text-slate-700 focus:outline-none transition flex items-center gap-1.5";
                historyBtn.className = "px-4 py-2.5 font-bold text-sm border-b-2 border-pink-600 text-pink-600 focus:outline-none transition flex items-center gap-1.5";
            }
            renderDashboard();
        }

        // Status update logic (e.g. from wait to ongoing)
        function updateTicketStatus(id, newStatus) {
            const ticket = tickets.find(t => t.id === id);
            if (ticket) {
                ticket.status = newStatus;
                setStorage("yru_maintenance_tickets_v3", tickets);
                renderDashboard();
            }
        }

        // Job completion modal logic
        function openJobCompleteModal(id) {
            const ticket = tickets.find(t => t.id === id);
            if (!ticket) return;

            document.getElementById("modal-ticket-id").value = id;
            
            const carSelect = document.getElementById("modal-car-select");
            carSelect.value = ticket.car_id;
            carSelect.disabled = true;

            document.getElementById("modal-repair-summary").value = ticket.issue;
            document.getElementById("modal-repair-detail").value = "";
            document.getElementById("modal-repair-technician").value = "ช่างประสาน";
            
            // Pre-fill today's date YYYY-MM-DD
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const dd = String(today.getDate()).padStart(2, '0');
            document.getElementById("modal-repair-date").value = `${yyyy}-${mm}-${dd}`;

            // Reset checkboxes
            document.querySelectorAll("input[name='spareParts']").forEach(cb => cb.checked = false);

            document.getElementById("jobCompletionModal").classList.remove("hidden");
        }

        function openJobCompleteModalDirect() {
            document.getElementById("modal-ticket-id").value = "direct";
            const carSelect = document.getElementById("modal-car-select");
            carSelect.disabled = false;
            carSelect.value = "EV-01";

            document.getElementById("modal-repair-summary").value = "";
            document.getElementById("modal-repair-detail").value = "";
            
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const dd = String(today.getDate()).padStart(2, '0');
            document.getElementById("modal-repair-date").value = `${yyyy}-${mm}-${dd}`;

            document.querySelectorAll("input[name='spareParts']").forEach(cb => cb.checked = false);
            document.getElementById("jobCompletionModal").classList.remove("hidden");
        }

        function closeJobCompleteModal() {
            document.getElementById("jobCompletionModal").classList.add("hidden");
        }

        function convertToThaiLogDate(dateStr) {
            if (!dateStr) return "";
            const parts = dateStr.split('-');
            if (parts.length !== 3) return dateStr;
            const year = parseInt(parts[0]) + 543;
            const month = parts[1];
            const date = parts[2];
            return `${date}/${month}/${year}`;
        }

        function saveJobCompletion() {
            const ticketId = document.getElementById("modal-ticket-id").value;
            const carId = document.getElementById("modal-car-select").value;
            const summary = document.getElementById("modal-repair-summary").value.trim();
            const detail = document.getElementById("modal-repair-detail").value.trim();
            const technician = document.getElementById("modal-repair-technician").value;
            const finishDate = document.getElementById("modal-repair-date").value;

            if (!summary) { alert("กรุณากรอกหัวข้อหลักการซ่อม!"); return; }
            if (!detail) { alert("กรุณากรอกรายละเอียดเชิงลึก!"); return; }
            if (!finishDate) { alert("กรุณาเลือกวันที่ซ่อมเสร็จ!"); return; }

            // Spare parts checkboxes
            let selectedParts = [];
            document.querySelectorAll("input[name='spareParts']:checked").forEach(cb => {
                selectedParts.push(cb.value);
            });

            if (ticketId === "direct") {
                // Free form addition of completed job
                const newId = "TCK-" + String(tickets.length + 1).padStart(3, '0');
                const newTicket = {
                    id: newId,
                    car_id: carId,
                    plate: carPlates[carId] || "กค 1234 ยะลา",
                    issue: summary,
                    reporter: "admin@yru.ac.th",
                    urgency: "🟡 ปกติ",
                    reported_at: convertToThaiLogDate(finishDate),
                    status: "completed",
                    detail: detail,
                    spare_parts: selectedParts,
                    technician: technician,
                    finish_date: finishDate
                };
                tickets.unshift(newTicket);
            } else {
                // Resolve existing ticket
                const ticket = tickets.find(t => t.id === ticketId);
                if (ticket) {
                    ticket.status = "completed";
                    ticket.issue = summary;
                    ticket.detail = detail;
                    ticket.spare_parts = selectedParts;
                    ticket.technician = technician;
                    ticket.finish_date = finishDate;
                }
            }

            // Save to localStorage
            setStorage("yru_maintenance_tickets_v3", tickets);
            
            // Sync with main admin DB if present in storage
            syncWithAdminDB(carId, summary, detail, selectedParts, technician, finishDate);

            closeJobCompleteModal();
            renderDashboard();
            alert("บันทึกประวัติการซ่อมบำรุงและส่งคืนสภาพพร้อมใช้งานเรียบร้อย!");
        }

        // Helper to push maintenance logs back to the main admin storage (yru_trams_v18)
        function syncWithAdminDB(carId, summary, detail, spareParts, technician, finishDate) {
            try {
                let tramsData = JSON.parse(localStorage.getItem("yru_trams_v18"));
                if (tramsData) {
                    const tram = tramsData.find(t => t.id === carId);
                    if (tram) {
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
                        tram.status = "พร้อมใช้งาน";
                        tram.active_issue = "";
                        tram.coords = tram.last_active_coords || defaultTramCoords[tram.id] || "6.549929, 101.291254";
                        tram.current_station_id = "";
                        tram.updated_by = technician || "mechanic@yru.ac.th";
                        
                        // Current Thai Date string helper
                        const months = ["ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค."];
                        const d = new Date();
                        const date = d.getDate();
                        const month = months[d.getMonth()];
                        const year = d.getFullYear() + 543;
                        tram.updated_at = `${date} ${month} ${year}`;
                        
                        let detailText = summary;
                        if (spareParts.length > 0) {
                             detailText += ` - อะไหล่: ${spareParts.join(', ')} (${detail})`;
                        } else {
                             detailText += ` (${detail})`;
                        }
                        
                        if (!tram.maintenance) tram.maintenance = [];
                        tram.maintenance.unshift({
                            date: convertToThaiLogDate(finishDate),
                            detail: detailText,
                            technician: technician
                        });
                        
                        localStorage.setItem("yru_trams_v18", JSON.stringify(tramsData));
                        localStorage.setItem('yru_car_status_' + carId, JSON.stringify({ status: 'ปกติกำลังขับ', active_issue: '' }));
                        try { window.dispatchEvent(new Event('storage')); } catch(e) {}

                        const carIdNum = carId.replace(/\D/g, '') || '1';
                        fetch('/api/update-driver-status', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')?.content || ''
                            },
                            body: JSON.stringify({ car_id: carIdNum, status: 'normal', start_time: '10:30 น.' })
                        }).catch(err => console.error(err));
                    }
                }
            } catch (e) {
                console.error("Sync with admin DB failed: ", e);
            }
        }

        // Issue reporting logic
        function openReportIssueModal() {
            document.getElementById("report-summary").value = "";
            document.getElementById("report-urgency").value = "🟡 ปกติ";
            document.getElementById("reportIssueModal").classList.remove("hidden");
        }

        // Close report modal
        function closeReportIssueModal() {
            document.getElementById("reportIssueModal").classList.add("hidden");
        }

        // Save reported issue
        function saveReportedIssue() {
            const carId = document.getElementById("report-car-select").value;
            const summary = document.getElementById("report-summary").value.trim();
            const user = document.getElementById("report-user").value.trim();
            const urgency = document.getElementById("report-urgency").value;

            if (!summary) { alert("กรุณาระบุหัวข้ออาการชำรุด!"); return; }
            if (!user) { alert("กรุณากรอกอีเมลผู้แจ้ง!"); return; }

            const newId = "TCK-" + String(tickets.length + 1).padStart(3, '0');
            const today = new Date();
            const yyyy = today.getFullYear();
            const mm = String(today.getMonth() + 1).padStart(2, '0');
            const dd = String(today.getDate()).padStart(2, '0');
            const formattedDate = `${yyyy}-${mm}-${dd}`;

            const newTicket = {
                id: newId,
                car_id: carId,
                plate: carPlates[carId] || "กค 1234 ยะลา",
                issue: summary,
                reporter: user,
                urgency: urgency,
                reported_at: convertToThaiLogDate(formattedDate),
                status: "pending",
                detail: "",
                spare_parts: [],
                technician: "",
                finish_date: ""
            };

            tickets.unshift(newTicket);
            setStorage("yru_maintenance_tickets_v3", tickets);

            // Sync status broken with main admin DB
            try {
                let tramsData = JSON.parse(localStorage.getItem("yru_trams_v18"));
                if (tramsData) {
                    const tram = tramsData.find(t => t.id === carId);
                    if (tram) {
                        tram.status = "รถขัดข้อง";
                        tram.active_issue = summary;
                        if (tram.coords && tram.coords !== "6.548900, 101.291700") {
                            tram.last_active_coords = tram.coords;
                        }
                        tram.coords = "6.548900, 101.291700";
                        tram.current_station_id = "GARAGE";
                        tram.updated_by = user;
                        
                        const months = ["ม.ค.", "ก.พ.", "มี.ค.", "เม.ย.", "พ.ค.", "มิ.ย.", "ก.ค.", "ส.ค.", "ก.ย.", "ต.ค.", "พ.ย.", "ธ.ค."];
                        const d = new Date();
                        const date = d.getDate();
                        const month = months[d.getMonth()];
                        const year = d.getFullYear() + 543;
                        tram.updated_at = `${date} ${month} ${year}`;
                        
                        localStorage.setItem("yru_trams_v18", JSON.stringify(tramsData));
                    }
                }
            } catch (e) {
                console.error(e);
            }

            closeReportIssueModal();
            renderDashboard();
            alert("เพิ่มใบสั่งแจ้งซ่อมไปยังคิวเรียบร้อย!");
        }

        function logoutDemo() {
            alert("ออกจากระบบจำลองช่างซ่อมบำรุงสำเร็จ!");
            window.location.href = "/";
        }

        // Run on load
        document.addEventListener("DOMContentLoaded", () => {
            renderDashboard();
        });
    </script>
</body>
</html>
