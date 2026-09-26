<!DOCTYPE html>
<html lang="th">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>ระบบติดตามเส้นทางการเดินรถไฟฟ้ามหาวิทยาลัยราชภัฏยะลา - Executive Dashboard</title>
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
    <link href="https://fonts.googleapis.com/css2?family=Kanit:wght@300;400;500;600;700;900&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    
    <style>
        body, button, input, select, textarea, div, span, p, a, h1, h2, h3, h4, h5, h6, label, td, th { font-family: 'Kanit', sans-serif !important; }
        body { background-color: #fdf2f2; }
        .page { display: none; }
        .menu-active { 
            background: linear-gradient(90deg, #ec4899 0%, #f472b6 100%); 
            color: white !important; 
            box-shadow: 0 10px 15px -3px rgba(236, 72, 153, 0.3); 
        }
        .sidebar-bg { background-color: #111827; }
        canvas { max-width: 100% !important; }

        /* ── Chart gradient animation ── */
        @keyframes kpiFadeUp {
            from { opacity: 0; transform: translateY(10px); }
            to   { opacity: 1; transform: translateY(0); }
        }
        .kpi-card { animation: kpiFadeUp 0.4s ease both; }
    </style>
</head>
<body class="text-gray-800 border-box">



<div class="flex h-screen overflow-hidden">
    <aside class="w-72 sidebar-bg text-white flex flex-col shrink-0 z-10">
        <div class="p-8 flex-1">
            <div class="flex flex-col gap-4 mb-10">
                <div class="flex items-center gap-3">
                    <img src="{{ asset('img/logo-yru.png') }}" alt="YRU Logo" class="h-10 bg-white rounded-xl p-1 shadow-lg">
                    <span class="text-lg font-bold tracking-tight uppercase text-pink-500">YRU EV</span>
                </div>
                <div class="text-xs text-gray-300 font-medium leading-relaxed">
                    ระบบติดตามเส้นทางการเดินรถไฟฟ้ามหาวิทยาลัยราชภัฏยะลา
                </div>
            </div>
            
            <p class="text-xs text-gray-500 font-bold uppercase tracking-wider mb-3">มุมมองผู้บริหาร</p>
            <nav class="space-y-3 mb-8">
                <button onclick="showPage('dashboard', this)" class="menu-btn w-full text-left px-5 py-3 rounded-2xl transition-all font-medium flex items-center gap-3 text-gray-400 hover:bg-gray-800 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"></path></svg>
                    สรุปภาพรวม
                </button>
                <button onclick="showPage('safety', this)" class="menu-btn w-full text-left px-5 py-3 rounded-2xl transition-all font-medium text-gray-400 flex items-center gap-3 hover:bg-gray-800 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    ความปลอดภัย
                </button>
                <button onclick="showPage('stats', this)" class="menu-btn w-full text-left px-5 py-3 rounded-2xl transition-all font-medium text-gray-400 flex items-center gap-3 hover:bg-gray-800 cursor-pointer">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path></svg>
                    วิเคราะห์สถิติ
                </button>
                <button id="menu-btn-survey" onclick="showPage('survey', this)" class="menu-btn w-full text-left px-5 py-3 rounded-2xl transition-all font-medium text-gray-400 flex items-center gap-3 hover:bg-gray-800 cursor-pointer relative">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"></path></svg>
                    ผลแบบประเมิน
                    <span id="survey-badge" class="hidden ml-auto bg-pink-500 text-white text-[9px] font-bold px-1.5 py-0.5 rounded-full">ใหม่</span>
                </button>
            </nav>
        </div>
    </aside>

    <main class="flex-1 p-10 overflow-y-auto">
        
        <section id="dashboard" class="page">
            <div class="flex justify-between items-start mb-10">
                <div>
                    <h1 class="text-4xl font-bold text-slate-900 mb-2">Executive Summary</h1>
                    <p class="text-slate-500">รายงานวิเคราะห์ระบบติดตามเส้นทางการเดินรถไฟฟ้ามหาวิทยาลัยราชภัฏยะลา</p>
                </div>
                <div class="flex items-center gap-4">
                    <div class="bg-white px-6 py-3 rounded-2xl shadow-sm border border-pink-50 flex items-center gap-4">
                        <div class="text-right">
                            <p class="text-[10px] text-gray-400 font-bold uppercase tracking-widest">Administrator</p>
                            <p class="text-sm font-bold text-slate-700">ผศ.ดร.ศิริชัย นามบุรี</p>
                        </div>
                        <div class="w-12 h-12 bg-pink-500 rounded-2xl flex items-center justify-center text-white font-bold text-xl shadow-md">SM</div>
                    </div>
                </div>
            </div>

            <!-- แถบสรุปภาพรวม KPI -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                <!-- ผู้ใช้บริการรวมวันนี้ -->
                <div class="kpi-card bg-gradient-to-br from-pink-600 to-pink-400 p-6 rounded-3xl shadow-lg text-white flex items-center justify-between">
                    <div>
                        <p class="text-pink-100 text-xs font-semibold mb-1">ผู้ใช้บริการรวม (วันนี้)</p>
                        <div class="flex items-baseline gap-2">
                            <h2 id="kpi-total-users" class="kpi-total-users-val text-3xl font-bold">0</h2>
                            <span class="bg-white/20 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">+12%</span>
                        </div>
                        <p class="text-pink-100 text-[10px] mt-1">เทียบกับสัปดาห์ที่แล้ว</p>
                    </div>
                    <div class="bg-white/20 w-12 h-12 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"></path></svg>
                    </div>
                </div>

                <!-- สรุปรถคันที่มีคนใช้งานเยอะกว่า -->
                <div class="kpi-card bg-gradient-to-br from-indigo-600 to-indigo-400 p-6 rounded-3xl shadow-lg text-white flex items-center justify-between">
                    <div>
                        <p class="text-indigo-100 text-xs font-semibold mb-1">รถไฟฟ้าที่มีผู้ใช้งานสูงสุด</p>
                        <div class="flex items-baseline gap-2">
                            <h2 id="kpi-most-used-car" class="kpi-most-used-car-val text-2xl font-bold">-</h2>
                            <span id="kpi-most-used-percent" class="kpi-most-used-percent-val bg-white/20 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">0%</span>
                        </div>
                        <p id="kpi-most-used-details" class="kpi-most-used-details-val text-indigo-100 text-[10px] mt-1">ยังไม่มีผู้ใช้บริการรถไฟฟ้าในวันนี้</p>
                    </div>
                    <div class="bg-white/20 w-12 h-12 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path></svg>
                    </div>
                </div>

                <!-- ช่องสรุปข้อมูลจากการสแกนความพึงพอใจ -->
                <div class="kpi-card bg-gradient-to-br from-emerald-600 to-emerald-400 p-6 rounded-3xl shadow-lg text-white flex items-center justify-between">
                    <div>
                        <p class="text-emerald-100 text-xs font-semibold mb-1">ความพึงพอใจเฉลี่ย</p>
                        <div class="flex items-baseline gap-2">
                            <h2 id="kpi-satisf-avg" class="kpi-satisf-avg-val text-3xl font-bold">0.00</h2>
                            <span id="kpi-satisf-count" class="kpi-satisf-count-val bg-white/20 text-white text-[10px] font-bold px-2 py-0.5 rounded-full">0 ประเมิน</span>
                        </div>
                        <p id="kpi-satisf-details" class="kpi-satisf-details-val text-emerald-100 text-[10px] mt-1">ยังไม่มีผลการประเมิน</p>
                    </div>
                    <div class="bg-white/20 w-12 h-12 rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.828 14.828a4 4 0 01-5.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 bg-white p-10 rounded-[2.5rem] shadow-sm relative">
                    <div class="flex justify-between items-center mb-2">
                        <div>
                            <h3 class="text-2xl font-bold text-slate-800">แนวโน้มการเข้าใช้งาน</h3>
                            <p class="text-slate-400 text-sm mt-1">เปรียบเทียบรายวันในสัปดาห์นี้</p>
                        </div>
                        <div class="flex gap-2">
                            <span class="flex items-center gap-1.5 text-xs text-slate-500 font-semibold">
                                <span class="w-3 h-3 rounded-full bg-pink-500 inline-block"></span>ผู้ใช้บริการ
                            </span>
                        </div>
                    </div>
                    <div style="height: 300px; position: relative;">
                        <canvas id="usageChart"></canvas>
                    </div>
                </div>

                <div class="bg-white p-10 rounded-[2.5rem] shadow-sm">
                    <h3 class="text-2xl font-bold text-slate-800 mb-8">สถานะการเดินรถ</h3>
                    <div class="space-y-8">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div id="status-dot-1" class="w-3 h-3 bg-pink-500 rounded-full animate-pulse"></div>
                                <div>
                                    <p class="font-bold text-slate-700">สายที่ 1 (อาคาร 25)</p>
                                    <p id="status-text-1" class="text-xs text-slate-400 font-medium">กำลังวิ่ง - ปกติ</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div id="status-dot-2" class="w-3 h-3 bg-pink-500 rounded-full animate-pulse"></div>
                                <div>
                                    <p class="font-bold text-slate-700">สายที่ 2 (อาคาร 14)</p>
                                    <p id="status-text-2" class="text-xs text-slate-400 font-medium">กำลังวิ่ง - ปกติ</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div id="status-dot-3" class="w-3 h-3 bg-gray-300 rounded-full"></div>
                                <div>
                                    <p class="font-bold text-slate-700">สายที่ 3 (อาคาร 20)</p>
                                    <p id="status-text-3" class="text-xs text-slate-400 font-medium">จอดพัก - ชาร์จไฟ 80%</p>
                                </div>
                            </div>
                        </div>
                        <button onclick="showPage('stats', document.querySelectorAll('.menu-btn')[2])" class="w-full py-4 mt-4 bg-pink-50 text-pink-600 font-bold rounded-2xl hover:bg-pink-100 transition-colors cursor-pointer">
                            ดูรายงานฉบับเต็ม
                        </button>
                    </div>
                </div>
            </div>
        </section>

        <section id="safety" class="page">
            <div class="flex justify-between items-center mb-10">
                <h1 class="text-4xl font-bold text-slate-900">ระบบความปลอดภัย</h1>
                <button onclick="triggerEmergencyAlert()" class="bg-rose-600 hover:bg-rose-700 text-white px-8 py-3 rounded-2xl font-bold shadow-lg shadow-rose-200 hover:scale-105 transition-transform cursor-pointer">
                    ⚠️ แจ้งเหตุฉุกเฉินส่วนกลาง
                </button>
            </div>
            
            

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-10">
                <div class="bg-white p-8 rounded-3xl border-l-8 border-green-500 shadow-sm">
                    <p class="text-slate-400 text-sm font-bold mb-2">เหตุการณ์ฉุกเฉินวันนี้</p>
                    <h2 id="safety-count-cases" class="text-4xl font-bold">0 <span class="text-lg font-normal text-slate-400 ml-2">เคส</span></h2>
                </div>
                <div class="bg-white p-8 rounded-3xl border-l-8 border-amber-500 shadow-sm">
                    <p class="text-slate-400 text-sm font-bold mb-2">การแจ้งเตือนระบบ</p>
                    <h2 id="safety-count-alerts" class="text-4xl font-bold">2 <span class="text-lg font-normal text-slate-400 ml-2">รายการ</span></h2>
                </div>
                <div class="bg-white p-8 rounded-3xl border-l-8 border-rose-500 shadow-sm">
                    <p class="text-slate-400 text-sm font-bold mb-2">รถที่ต้องตรวจสอบด่วน</p>
                    <h2 id="safety-count-inspections" class="text-4xl font-bold">1 <span class="text-lg font-normal text-slate-400 ml-2">คัน</span></h2>
                </div>
            </div>

            <div class="bg-white rounded-[2.5rem] shadow-sm overflow-hidden">
                <div class="p-8 border-b flex justify-between items-center">
                    <h3 class="text-xl font-bold text-slate-800">บันทึกเหตุการณ์ล่าสุด</h3>
                    <span class="text-pink-600 text-sm font-bold italic animate-pulse">● อัปเดตแบบ Real-time</span>
                </div>
                <table class="w-full text-left">
                    <thead class="bg-gray-50 text-slate-400 text-sm uppercase tracking-wider">
                        <tr>
                            <th class="px-8 py-5">เวลา</th>
                            <th class="px-8 py-5">สถานที่ / อุปกรณ์</th>
                            <th class="px-8 py-5">สถานะความปลอดภัย</th>
                            <th class="px-8 py-5 text-center">การดำเนินการ</th>
                        </tr>
                    </thead>
                    <tbody id="safety-log-table" class="divide-y divide-gray-100">
                        <tr class="hover:bg-gray-50 transition-colors">
                            <td class="px-8 py-6 font-bold text-slate-600">10:45 น.</td>
                            <td class="px-8 py-6">
                                <p class="font-bold text-slate-700">สายรถที่ 3 (EV-003)</p>
                                <p class="text-xs text-gray-400">ระบบแบตเตอรี่ร้อนเกินกำหนด</p>
                            </td>
                            <td class="px-8 py-6">
                                <span class="bg-amber-100 text-amber-600 px-4 py-1.5 rounded-full text-xs font-bold">รอการตรวจสอบ</span>
                            </td>
                            <td class="px-8 py-6 text-center">
                                <button onclick="alert('ส่งคำสั่งตรวจสอบด่วนไปยังทีมช่างเทคนิคแล้ว')" class="bg-slate-900 text-white px-6 py-2 rounded-xl text-xs font-bold hover:bg-slate-800 cursor-pointer">สั่งการด่วน</button>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        </section>

        <section id="stats" class="page">
            <div id="pdf-content">
                <div class="flex justify-between items-start mb-10">
                    <div>
                        <h1 class="text-4xl font-bold text-slate-900 mb-2">วิเคราะห์สถิติเชิงลึก</h1>
                        <p class="text-slate-500">วิเคราะห์ข้อมูลความพึงพอใจและสถิติการใช้งานรายรถคัน</p>
                    </div>
                    <div class="flex gap-4" data-html2canvas-ignore="true">
                        <select class="bg-white border border-gray-200 px-5 py-2.5 rounded-2xl text-sm font-bold shadow-sm outline-none cursor-pointer">
                            <option>ปีงบประมาณ 2569</option>
                        </select>
                        <button onclick="downloadPDF()" class="bg-slate-900 text-white px-6 py-2.5 rounded-2xl text-sm font-bold hover:bg-slate-800 hover:shadow-lg transition-all cursor-pointer">ดาวน์โหลด PDF</button>
                    </div>
                </div>


                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-10">
                    <div class="bg-white p-10 rounded-[2.5rem] shadow-sm">
                        <h3 class="text-xl font-bold text-slate-800 mb-8">จำนวนผู้ใช้ (เปรียบเทียบปีก่อนหน้า vs ปีปัจจุบัน)</h3>
                        <div style="height: 250px; position: relative;">
                            <canvas id="comparisonChart"></canvas>
                        </div>
                    </div>

                    <div class="bg-white p-10 rounded-[2.5rem] shadow-sm">
                        <h3 class="text-xl font-bold text-slate-800 mb-8">สัดส่วนผู้ใช้งานรายเส้นทาง</h3>
                        <div class="flex flex-col md:flex-row items-center justify-between gap-8">
                            <div class="w-full max-w-[200px]" style="height: 200px; position: relative;">
                                <canvas id="routeDoughnutChart"></canvas>
                            </div>
                            <div class="flex-1 space-y-5 w-full">
                                <div class="flex items-center justify-between border-b pb-3">
                                    <span class="flex items-center gap-2 font-medium"><div class="w-3 h-3 bg-pink-500 rounded-full"></div> สาย 1</span>
                                    <span id="route-p1" class="font-bold text-slate-800">45%</span>
                                </div>
                                <div class="flex items-center justify-between border-b pb-3">
                                    <span class="flex items-center gap-2 font-medium"><div class="w-3 h-3 bg-slate-700 rounded-full"></div> สาย 2</span>
                                    <span id="route-p2" class="font-bold text-slate-800">30%</span>
                                </div>
                                <div class="flex items-center justify-between border-b pb-3">
                                    <span class="flex items-center gap-2 font-medium"><div class="w-3 h-3 bg-slate-300 rounded-full"></div> สาย 3</span>
                                    <span id="route-p3" class="font-bold text-slate-800">25%</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
                    <!-- การวิเคราะห์ความพึงพอใจจากการสแกน QR Code -->
                    <div class="bg-white p-10 rounded-[2.5rem] shadow-sm flex flex-col md:flex-row gap-6">
                        <div class="flex-1">
                            <h3 class="text-xl font-bold text-slate-800 mb-3">สรุปผลประเมินความพึงพอใจ</h3>
                            <p class="text-slate-400 text-sm mb-6">สถิติคะแนนความพึงพอใจการใช้บริการของผู้ใช้บริการ</p>
                            <div class="space-y-4">
                                <div>
                                    <div class="flex justify-between text-sm text-gray-600 mb-1">
                                        <span class="flex items-center gap-1.5">😍 ดีเยี่ยม (5 คะแนน)</span>
                                        <span id="satisf-5-percent" class="font-bold text-emerald-600">82%</span>
                                    </div>
                                    <div class="w-full bg-gray-100 h-2.5 rounded-full"><div id="satisf-5-bar" class="bg-emerald-500 h-2.5 rounded-full" style="width: 82%"></div></div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-sm text-gray-600 mb-1">
                                        <span class="flex items-center gap-1.5">😐 พอใช้ (3 คะแนน)</span>
                                        <span id="satisf-3-percent" class="font-bold text-amber-600">14%</span>
                                    </div>
                                    <div class="w-full bg-gray-100 h-2.5 rounded-full"><div id="satisf-3-bar" class="bg-amber-500 h-2.5 rounded-full" style="width: 14%"></div></div>
                                </div>
                                <div>
                                    <div class="flex justify-between text-sm text-gray-600 mb-1">
                                        <span class="flex items-center gap-1.5">😡 ต้องปรับปรุง (1 คะแนน)</span>
                                        <span id="satisf-1-percent" class="font-bold text-rose-600">4%</span>
                                    </div>
                                    <div class="w-full bg-gray-100 h-2.5 rounded-full"><div id="satisf-1-bar" class="bg-rose-500 h-2.5 rounded-full" style="width: 4%"></div></div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- สรุปสัดส่วนการใช้งานรถไฟฟ้า (EV 01 vs EV 02) -->
                    <div class="bg-white p-10 rounded-[2.5rem] shadow-sm">
                        <h3 class="text-xl font-bold text-slate-800 mb-4">สัดส่วนผู้ใช้บริการรายรถคัน (EV 01 vs EV 02)</h3>
                        <p class="text-slate-400 text-sm mb-6">รถคันที่มีผู้ใช้งานเยอะกว่าและอัตราการกระจายการใช้งาน</p>
                        <div class="flex flex-col md:flex-row items-center gap-8">
                            <div class="w-full max-w-[150px] h-[150px] relative">
                                <canvas id="carUsageDoughnutChart"></canvas>
                            </div>
                            <div class="flex-1 space-y-4 w-full">
                                <div class="p-4 bg-indigo-50/50 rounded-2xl border border-indigo-100/50">
                                    <div class="flex justify-between items-center mb-1">
                                        <span class="text-sm font-bold text-indigo-900">รถคันที่มีผู้ใช้งานเยอะกว่า</span>
                                        <span id="most-used-badge" class="bg-indigo-600 text-white text-[10px] px-2 py-0.5 rounded-full font-bold">EV 01</span>
                                    </div>
                                    <p id="most-used-comparison-text" class="text-[11px] text-indigo-700">EV 01 มีคนใช้งานรวมมากกว่า EV 02 อยู่ 320 คน</p>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="flex items-center gap-2 font-medium"><div class="w-3 h-3 bg-indigo-600 rounded-full"></div> EV 01 (คันที่ 1)</span>
                                    <span id="car-1-users-display" class="font-bold text-slate-800">780 คน (63%)</span>
                                </div>
                                <div class="flex justify-between items-center text-sm">
                                    <span class="flex items-center gap-2 font-medium"><div class="w-3 h-3 bg-purple-500 rounded-full"></div> EV 02 (คันที่ 2)</span>
                                    <span id="car-2-users-display" class="font-bold text-slate-800">460 คน (37%)</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </section>

        <!-- ========== หน้าสรุปผลแบบประเมินความพึงพอใจ ========== -->
        <section id="survey" class="page">
            <div class="flex justify-between items-start mb-8">
                <div>
                    <h1 class="text-4xl font-bold text-slate-900 mb-2">สรุปผลแบบประเมินความพึงพอใจ</h1>
                    <p class="text-slate-500">รายงานผลการประเมินจากผู้ใช้บริการรถไฟฟ้า YRU EV</p>
                </div>
                <button onclick="clearSurveyData()" class="bg-rose-50 text-rose-600 hover:bg-rose-100 border border-rose-200 px-5 py-2.5 rounded-2xl text-sm font-bold transition cursor-pointer">
                    🗑️ ล้างข้อมูลทั้งหมด
                </button>
            </div>

            <!-- KPI การประเมิน -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-5 mb-8">
                <div class="kpi-card bg-gradient-to-br from-amber-500 to-yellow-400 p-6 rounded-3xl shadow-lg text-white">
                    <p class="text-yellow-100 text-xs font-semibold mb-1">คะแนนเฉลี่ยรวม</p>
                    <h2 id="sv-avg" class="text-4xl font-bold">-</h2>
                    <p class="text-yellow-100 text-[10px] mt-1">เต็ม 5.00 คะแนน</p>
                </div>
                <div class="kpi-card bg-gradient-to-br from-pink-600 to-pink-400 p-6 rounded-3xl shadow-lg text-white">
                    <p class="text-pink-100 text-xs font-semibold mb-1">จำนวนผู้ประเมิน</p>
                    <h2 id="sv-count" class="text-4xl font-bold">0</h2>
                    <p class="text-pink-100 text-[10px] mt-1">ราย</p>
                </div>
                <div class="kpi-card bg-gradient-to-br from-emerald-600 to-emerald-400 p-6 rounded-3xl shadow-lg text-white">
                    <p class="text-emerald-100 text-xs font-semibold mb-1">ประเมินดีเยี่ยม (5★)</p>
                    <h2 id="sv-five-star" class="text-4xl font-bold">0%</h2>
                    <p class="text-emerald-100 text-[10px] mt-1">สัดส่วนผู้ให้คะแนนสูงสุด</p>
                </div>
                <div class="kpi-card bg-gradient-to-br from-violet-600 to-violet-400 p-6 rounded-3xl shadow-lg text-white">
                    <p class="text-violet-100 text-xs font-semibold mb-1">ล่าสุดเมื่อ</p>
                    <h2 id="sv-last-time" class="text-base font-bold mt-2">-</h2>
                    <p class="text-violet-100 text-[10px] mt-1">เวลาที่ประเมินล่าสุด</p>
                </div>
            </div>

            <!-- คะแนนรายข้อ -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-8 mb-8">
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm">
                    <h3 class="text-xl font-bold text-slate-800 mb-6">📊 คะแนนเฉลี่ยรายข้อ</h3>
                    <div class="space-y-5" id="sv-per-question">
                        <!-- Rendered by JS -->
                    </div>
                </div>

                <!-- ตารางรายการประเมินล่าสุด -->
                <div class="bg-white p-8 rounded-[2.5rem] shadow-sm flex flex-col">
                    <div class="flex justify-between items-center mb-6">
                        <h3 class="text-xl font-bold text-slate-800">📋 รายการประเมินล่าสุด</h3>
                        <span id="sv-total-badge" class="bg-pink-100 text-pink-700 text-xs font-bold px-3 py-1 rounded-full">0 รายการ</span>
                    </div>
                    <div class="flex-1 overflow-y-auto space-y-3 max-h-80" id="sv-entries-list">
                        <p class="text-slate-400 text-sm text-center py-8">ยังไม่มีข้อมูลการประเมิน</p>
                    </div>
                </div>
            </div>

            <!-- ความคิดเห็น -->
            <div class="bg-white p-8 rounded-[2.5rem] shadow-sm">
                <h3 class="text-xl font-bold text-slate-800 mb-6">💬 ความคิดเห็นและข้อเสนอแนะ</h3>
                <div id="sv-comments-list" class="space-y-3">
                    <p class="text-slate-400 text-sm text-center py-4">ยังไม่มีความคิดเห็น</p>
                </div>
            </div>
        </section>

        </main>
</div>

<script>
    // ==========================================
    // SYSTEM GLOBAL STATE (ข้อมูลตั้งต้นของระบบ)
    // ==========================================
    let systemData = {
        totalUsers: 0,
        avgSatisfaction: 0,
        satisfactionCount: 0,
        satisfScoreCount: {
            '5': 0,
            '3': 0,
            '1': 0
        },
        carUsers: {
            '1': 0,
            '2': 0
        },
        
        // ข้อมูลแบ่งสายรถ
        routeUsers: {
            '1': 0,
            '2': 0,
            '3': 0
        },
        
        // สถานะปัจจุบันของรถแต่ละสาย
        busStatus: {
            '1': 'กำลังวิ่ง - ปกติ',
            '2': 'กำลังวิ่ง - ปกติ',
            '3': 'จอดพัก - ชาร์จไฟ 80%'
        },

        // นับจำนวนเคสความปลอดภัย
        safetyCases: 0,
        safetyAlerts: 2,
        safetyInspections: 1,

        // ประวัติ Log ความปลอดภัย
        safetyLogs: [
            { time: "10:45 น.", target: "สายรถที่ 3 (EV-003)", info: "ระบบแบตเตอรี่ร้อนเกินกำหนด", status: "รอการตรวจสอบ", actionRequired: true },
            { time: "09:12 น.", target: "จากสถานีชาร์จ อาคาร 14", info: "กล้อง CCTV ตรวจพบวัตถุต้องสงสัย", status: "ปกติ / ตรวจสอบแล้ว", actionRequired: false }
        ]
    };

    // ตัวแปรเก็บ Object Charts
    let currentChartUsage = null;
    let comparisonChart = null;
    let routeChart = null;
    let carUsageChart = null;

    // ==========================================
    // NAVIGATION LOGIC
    // ==========================================
    function showPage(pageId, btn) {
        document.querySelectorAll(".page").forEach(p => p.style.display = "none");
        document.getElementById(pageId).style.display = "block";
        
        document.querySelectorAll(".menu-btn").forEach(m => {
            m.classList.remove("menu-active");
            m.classList.add("text-gray-400");
        });
        
        btn.classList.add("menu-active");
        btn.classList.remove("text-gray-400");

        requestAnimationFrame(() => {
            setTimeout(() => {
                updateUIElements();
                if (pageId === 'dashboard') { 
                    renderMainChart(); 
                } else if (pageId === 'stats') { 
                    renderStatsCharts(); 
                } else if (pageId === 'safety') {
                    renderSafetyLogs();
                } else if (pageId === 'survey') {
                    renderSurveyPage();
                }
            }, 50);
        });
    }

    // ==========================================
    // UI REFRESH LOGIC (อัปเดตตัวเลขตาม State จริง)
    // ==========================================
    function updateUIElements() {
        // หน้าแดชบอร์ดหลัก
        if(document.getElementById('kpi-total-users')) document.getElementById('kpi-total-users').innerText = systemData.totalUsers.toLocaleString();

        // คำนวณและแสดงผลข้อมูลการใช้รถคัน
        let car1Users = systemData.carUsers['1'];
        let car2Users = systemData.carUsers['2'];
        let totalCarUsers = car1Users + car2Users;
        let car1Percent = totalCarUsers > 0 ? Math.round((car1Users / totalCarUsers) * 100) : 50;
        let car2Percent = totalCarUsers > 0 ? Math.round((car2Users / totalCarUsers) * 100) : 50;
        
        let mostUsedCar = "EV 01";
        let mostUsedPercent = car1Percent;
        let diffUsers = Math.abs(car1Users - car2Users);
        let comparisonText = `EV 01 (คันที่ 1) และ EV 02 (คันที่ 2) มีผู้ใช้บริการเท่ากัน`;
        
        if (car1Users < car2Users) {
            mostUsedCar = "EV 02";
            mostUsedPercent = car2Percent;
            comparisonText = `EV 02 มีคนใช้งานรวมมากกว่า EV 01 อยู่ ${diffUsers.toLocaleString()} คน`;
        } else if (car1Users > car2Users) {
            mostUsedCar = "EV 01";
            mostUsedPercent = car1Percent;
            comparisonText = `EV 01 มีคนใช้งานรวมมากกว่า EV 02 อยู่ ${diffUsers.toLocaleString()} คน`;
        }

        if(document.getElementById('kpi-most-used-car')) document.getElementById('kpi-most-used-car').innerText = mostUsedCar;
        if(document.getElementById('kpi-most-used-percent')) document.getElementById('kpi-most-used-percent').innerText = `${mostUsedPercent}%`;
        if(document.getElementById('kpi-most-used-details')) document.getElementById('kpi-most-used-details').innerText = `EV 01 (${car1Users.toLocaleString()} คน) vs EV 02 (${car2Users.toLocaleString()} คน)`;
        if(document.getElementById('most-used-badge')) document.getElementById('most-used-badge').innerText = mostUsedCar;
        if(document.getElementById('most-used-comparison-text')) document.getElementById('most-used-comparison-text').innerText = comparisonText;
        if(document.getElementById('car-1-users-display')) document.getElementById('car-1-users-display').innerText = `${car1Users.toLocaleString()} คน (${car1Percent}%)`;
        if(document.getElementById('car-2-users-display')) document.getElementById('car-2-users-display').innerText = `${car2Users.toLocaleString()} คน (${car2Percent}%)`;

        // ── คำนวณและแสดงผลคะแนนความพึงพอใจจากแบบประเมินจริง (localStorage) ──
        const lsSurveys = JSON.parse(localStorage.getItem('yru_surveys') || '[]');
        const hasSurveys = lsSurveys.length > 0;

        let avgSatisf, satisfCount, p5, p3, p1, satisfDetail;

        if (hasSurveys) {
            // คำนวณจากแบบประเมินจริงที่ส่งมา
            satisfCount = lsSurveys.length;
            avgSatisf = lsSurveys.reduce((s, e) => s + e.avg, 0) / satisfCount;

            // นับสัดส่วนดาว: 5★ = avg≥4.5, 3★ = avg 2.5–4.4, 1★ = avg<2.5
            const cnt5 = lsSurveys.filter(e => e.avg >= 4.5).length;
            const cnt3 = lsSurveys.filter(e => e.avg >= 2.5 && e.avg < 4.5).length;
            const cnt1 = lsSurveys.filter(e => e.avg < 2.5).length;
            p5 = Math.round((cnt5 / satisfCount) * 100);
            p3 = Math.round((cnt3 / satisfCount) * 100);
            p1 = Math.round((cnt1 / satisfCount) * 100);

            // ข้อความสรุปในการ์ด
            const starLabel = avgSatisf >= 4.5 ? '😍 ดีเยี่ยม' : avgSatisf >= 3.5 ? '😊 ดี' : avgSatisf >= 2.5 ? '😐 พอใช้' : '😟 ต้องปรับปรุง';
            satisfDetail = `${starLabel} — ${p5}% ให้คะแนนสูงสุด`;
        } else {
            // ยังไม่มีแบบประเมิน → แสดง placeholder
            avgSatisf = null;
            satisfCount = 0;
            p5 = 0; p3 = 0; p1 = 0;
            satisfDetail = 'ยังไม่มีผลการประเมิน';
        }

        const kpiAvgEl = document.getElementById('kpi-satisf-avg');
        const kpiCountEl = document.getElementById('kpi-satisf-count');
        const kpiDetailsEl = document.getElementById('kpi-satisf-details');
        if (kpiAvgEl) kpiAvgEl.innerText = hasSurveys ? avgSatisf.toFixed(2) : '-';
        if (kpiCountEl) kpiCountEl.innerText = `${satisfCount} ประเมิน`;
        if (kpiDetailsEl) kpiDetailsEl.innerText = satisfDetail;

        if(document.getElementById('satisf-5-percent')) document.getElementById('satisf-5-percent').innerText = `${p5}%`;
        if(document.getElementById('satisf-5-bar')) document.getElementById('satisf-5-bar').style.width = `${p5}%`;
        if(document.getElementById('satisf-3-percent')) document.getElementById('satisf-3-percent').innerText = `${p3}%`;
        if(document.getElementById('satisf-3-bar')) document.getElementById('satisf-3-bar').style.width = `${p3}%`;
        if(document.getElementById('satisf-1-percent')) document.getElementById('satisf-1-percent').innerText = `${p1}%`;
        if(document.getElementById('satisf-1-bar')) document.getElementById('satisf-1-bar').style.width = `${p1}%`;



        // อัปเดตข้อความสถานะการเดินรถเรียลไทม์
        for (let i = 1; i <= 3; i++) {
            let txtElement = document.getElementById(`status-text-${i}`);
            let dotElement = document.getElementById(`status-dot-${i}`);
            if(txtElement) txtElement.innerText = systemData.busStatus[i];
            
            // เปลี่ยนสีปุ่มสถานะตามความตึงเครียดของข้อมูล
            if(dotElement) {
                if(systemData.busStatus[i].includes("ปกติ") || systemData.busStatus[i].includes("100%")) {
                    dotElement.className = "w-3 h-3 bg-pink-500 rounded-full animate-pulse";
                } else {
                    dotElement.className = "w-3 h-3 bg-amber-500 rounded-full animate-pulse";
                }
            }
        }

        // หน้าความปลอดภัย
        if(document.getElementById('safety-count-cases')) document.getElementById('safety-count-cases').innerHTML = `${systemData.safetyCases} <span class="text-lg font-normal text-slate-400 ml-2">เคส</span>`;
        if(document.getElementById('safety-count-alerts')) document.getElementById('safety-count-alerts').innerHTML = `${systemData.safetyAlerts} <span class="text-lg font-normal text-slate-400 ml-2">รายการ</span>`;
        if(document.getElementById('safety-count-inspections')) document.getElementById('safety-count-inspections').innerHTML = `${systemData.safetyInspections} <span class="text-lg font-normal text-slate-400 ml-2">คัน</span>`;

        // อัปเดตสัดส่วนเปอร์เซ็นต์ในหน้าสถิติ
        let totalRouteUsers = systemData.routeUsers['1'] + systemData.routeUsers['2'] + systemData.routeUsers['3'];
        if(totalRouteUsers > 0) {
            if(document.getElementById('route-p1')) document.getElementById('route-p1').innerText = Math.round((systemData.routeUsers['1'] / totalRouteUsers) * 100) + "%";
            if(document.getElementById('route-p2')) document.getElementById('route-p2').innerText = Math.round((systemData.routeUsers['2'] / totalRouteUsers) * 100) + "%";
            if(document.getElementById('route-p3')) document.getElementById('route-p3').innerText = Math.round((systemData.routeUsers['3'] / totalRouteUsers) * 100) + "%";
        }
    }

    // สั่งวาดตารางความปลอดภัยใหม่เมื่อมีเหตุการณ์อัปเดต
    function renderSafetyLogs() {
        const tbody = document.getElementById('safety-log-table');
        if(!tbody) return;
        tbody.innerHTML = "";
        
        systemData.safetyLogs.forEach((log, index) => {
            let statusClass = log.status.includes('ปกติ') ? 'bg-green-100 text-green-600' : 'bg-amber-100 text-amber-600';
            if(log.status.includes('ด่วน')) statusClass = 'bg-rose-100 text-rose-600';

            let actionButton = log.actionRequired 
                ? `<button onclick="resolveIncident(${index})" class="bg-slate-900 text-white px-6 py-2 rounded-xl text-xs font-bold hover:bg-slate-800 cursor-pointer">สั่งการด่วน</button>`
                : `<span class="text-slate-300 text-xs font-bold italic">เรียบร้อย</span>`;

            tbody.innerHTML += `
                <tr class="hover:bg-gray-50 transition-colors">
                    <td class="px-8 py-6 font-bold text-slate-600">${log.time}</td>
                    <td class="px-8 py-6">
                        <p class="font-bold text-slate-700">${log.target}</p>
                        <p class="text-xs text-gray-400">${log.info}</p>
                    </td>
                    <td class="px-8 py-6">
                        <span class="${statusClass} px-4 py-1.5 rounded-full text-xs font-bold">${log.status}</span>
                    </td>
                    <td class="px-8 py-6 text-center">${actionButton}</td>
                </tr>
            `;
        });
    }

    function resolveIncident(index) {
        fetch('/api/safety/resolve-incident', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': '{{ csrf_token() }}'
            },
            body: JSON.stringify({ index: index })
        })
        .then(res => res.json())
        .then(data => {
            alert('ส่งคำสั่งตรวจสอบด่วนไปยังทีมช่างเทคนิคเรียบร้อยแล้ว!');
            fetchTodayStats();
        })
        .catch(err => {
            console.error('Error resolving incident:', err);
            alert('เกิดข้อผิดพลาดในการเชื่อมต่อเซิร์ฟเวอร์');
        });
    }

    function triggerEmergencyAlert() {
        alert('🚨 ระบบได้ส่งสัญญาณเตือนภัยฉุกเฉินระดับสูง ไปยังศูนย์รักษาความปลอดภัยกลาง YRU และบันทึกข้อมูลเข้าหน้ารายงานเรียบร้อยแล้ว');
        systemData.safetyCases += 1;
        updateUIElements();
    }

// ==========================================
    // CHART.JS RENDERING LOGIC
    // ==========================================
    function renderMainChart() {
        const canvas = document.getElementById('usageChart');
        if (!canvas) return;
        const ctx = canvas.getContext('2d');
        if (currentChartUsage) currentChartUsage.destroy();

        // Gradient fill
        const gradient = ctx.createLinearGradient(0, 0, 0, 300);
        gradient.addColorStop(0, 'rgba(236,72,153,0.25)');
        gradient.addColorStop(1, 'rgba(236,72,153,0.0)');

        // คำนวณวันปัจจุบันเพื่อแทนที่ข้อมูลจริงในกราฟ
        const baseDayData = [120, 240, 190, 310, 80, 110, 95];
        const dayIndex = new Date().getDay(); // 0 = อาทิตย์, 1 = จันทร์, ..., 6 = เสาร์
        const chartIndex = dayIndex === 0 ? 6 : dayIndex - 1;
        baseDayData[chartIndex] = systemData.totalUsers;

        const dayData = baseDayData;
        const maxIdx  = dayData.indexOf(Math.max(...dayData));

        currentChartUsage = new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['จันทร์', 'อังคาร', 'พุธ', 'พฤหัส', 'ศุกร์', 'เสาร์', 'อาทิตย์'],
                datasets: [{
                    label: 'ผู้ใช้บริการรายวัน',
                    data: dayData,
                    borderColor: '#ec4899',
                    borderWidth: 3,
                    backgroundColor: gradient,
                    fill: true,
                    tension: 0.45,
                    pointRadius: dayData.map((_, i) => i === maxIdx ? 8 : 5),
                    pointBackgroundColor: dayData.map((_, i) => i === maxIdx ? '#ec4899' : '#fff'),
                    pointBorderColor: '#ec4899',
                    pointBorderWidth: 2.5,
                    pointHoverRadius: 9
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { intersect: false, mode: 'index' },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        backgroundColor: '#1e293b',
                        titleColor: '#f1f5f9',
                        bodyColor: '#fb7185',
                        padding: 12,
                        cornerRadius: 12,
                        callbacks: {
                            label: ctx => ` ${ctx.parsed.y.toLocaleString()} คน`
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: '#f1f5f9', drawBorder: false },
                        ticks: { color: '#94a3b8', font: { size: 11 } }
                    },
                    x: {
                        grid: { display: false },
                        ticks: { color: '#94a3b8', font: { size: 11 } }
                    }
                }
            }
        });
    }

    function renderStatsCharts() {
        // Line Chart เปรียบเทียบผู้ใช้
        const canvasComp = document.getElementById('comparisonChart');
        if (canvasComp) {
            const ctxComp = canvasComp.getContext('2d');
            if (comparisonChart) comparisonChart.destroy();
            comparisonChart = new Chart(ctxComp, {
                type: 'line',
                data: {
                    labels: ['ม.ค.', 'ก.พ.', 'มี.ค.', 'เม.ย.', 'พ.ค.', 'มิ.ย.'],
                    datasets: [
                        { label: 'ปีปัจจุบัน', data: [1200, 1800, 1500, 2200, 2800, systemData.totalUsers], borderColor: '#ec4899', backgroundColor: 'rgba(236,72,153,0.1)', fill: true, tension: 0.4 },
                        { label: 'ปีก่อนหน้า', data: [1000, 1200, 1100, 1400, 1500, 1300], borderColor: '#cbd5e1', borderDash: [5,5], tension: 0.4 }
                    ]
                },
                options: { responsive: true, maintainAspectRatio: false }
            });
        }

        // Doughnut Chart สัดส่วนเส้นทาง
        const canvasRoute = document.getElementById('routeDoughnutChart');
        if (canvasRoute) {
            const ctxRoute = canvasRoute.getContext('2d');
            if (routeChart) routeChart.destroy();
            routeChart = new Chart(ctxRoute, {
                type: 'doughnut',
                data: {
                    labels: ['สาย 1', 'สาย 2', 'สาย 3'],
                    datasets: [{
                        data: [systemData.routeUsers['1'], systemData.routeUsers['2'], systemData.routeUsers['3']],
                        backgroundColor: ['#ec4899', '#334155', '#e2e8f0'],
                        borderWidth: 0
                    }]
                },
                options: { cutout: '75%', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
            });
        }

        // Doughnut Chart สัดส่วนผู้ใช้บริการรายรถคัน (EV 01 vs EV 02)
        const canvasCar = document.getElementById('carUsageDoughnutChart');
        if (canvasCar) {
            const ctxCar = canvasCar.getContext('2d');
            if (carUsageChart) carUsageChart.destroy();
            carUsageChart = new Chart(ctxCar, {
                type: 'doughnut',
                data: {
                    labels: ['EV 01', 'EV 02'],
                    datasets: [{
                        data: [systemData.carUsers['1'], systemData.carUsers['2']],
                        backgroundColor: ['#4f46e5', '#a855f7'],
                        borderWidth: 0
                    }]
                },
                options: { cutout: '75%', responsive: true, maintainAspectRatio: false, plugins: { legend: { display: false } } }
            });
        }
    }

    // ฟังก์ชันดาวน์โหลดรายงานสถิติเป็น PDF
    function downloadPDF() {
        const element = document.getElementById('pdf-content');
        const opt = {
            margin:       10,
            filename:     'YRU-EV-Analytics-Report.pdf',
            image:        { type: 'jpeg', quality: 0.98 },
            html2canvas:  { scale: 2, useCORS: true },
            jsPDF:        { unit: 'mm', format: 'a4', orientation: 'landscape' }
        };
        html2pdf().set(opt).from(element).save();
    }

    // สั่งรันคำสั่งเมื่อหน้าจอเปิดใช้งานครั้งแรก
    document.addEventListener("DOMContentLoaded", () => {
        // ตรวจสอบ URL param ?tab=survey เพื่อ auto-open หน้าประเมิน
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('tab') === 'survey') {
            const surveyBtn = document.getElementById('menu-btn-survey');
            showPage('survey', surveyBtn);
            renderSurveyPage();
        } else {
            showPage('dashboard', document.querySelector(".menu-btn"));
        }
        fetchTodayStats();
        // แสดง badge ถ้ามีผลประเมินใหม่
        const surveys = JSON.parse(localStorage.getItem('yru_surveys') || '[]');
        if (surveys.length > 0) {
            document.getElementById('survey-badge').classList.remove('hidden');
        }
    });

    // ==========================================
    // SURVEY PAGE RENDERING
    // ==========================================
    const QUESTION_LABELS = [
        'ความสุภาพ กริยา มารยาทของเจ้าหน้าที่',
        'เจ้าหน้าที่แต่งกายสุภาพ เรียบร้อย เหมาะสม',
        'ความใส่ใจ กระตือรือร้น เต็มใจให้บริการ',
        'ให้บริการเท่าเทียม ไม่เลือกปฏิบัติ',
        'ตอบข้อซักถามอย่างชัดเจน'
    ];

    function renderSurveyPage() {
        const surveys = JSON.parse(localStorage.getItem('yru_surveys') || '[]');
        document.getElementById('survey-badge').classList.add('hidden');

        // ─── KPI ───
        document.getElementById('sv-count').innerText = surveys.length;
        document.getElementById('sv-total-badge').innerText = surveys.length + ' รายการ';

        if (surveys.length === 0) {
            document.getElementById('sv-avg').innerText = '-';
            document.getElementById('sv-five-star').innerText = '0%';
            document.getElementById('sv-last-time').innerText = '-';
            document.getElementById('sv-per-question').innerHTML = '<p class="text-slate-400 text-sm text-center py-4">ยังไม่มีข้อมูล</p>';
            document.getElementById('sv-entries-list').innerHTML = '<p class="text-slate-400 text-sm text-center py-8">ยังไม่มีข้อมูลการประเมิน</p>';
            document.getElementById('sv-comments-list').innerHTML = '<p class="text-slate-400 text-sm text-center py-4">ยังไม่มีความคิดเห็น</p>';
            return;
        }

        // คำนวณ KPI
        const totalAvg = surveys.reduce((s, e) => s + e.avg, 0) / surveys.length;
        document.getElementById('sv-avg').innerText = totalAvg.toFixed(2);
        document.getElementById('sv-last-time').innerText = surveys[surveys.length - 1].time;

        const fiveStarCount = surveys.filter(e => e.avg >= 4.5).length;
        document.getElementById('sv-five-star').innerText = Math.round((fiveStarCount / surveys.length) * 100) + '%';

        // คะแนนรายข้อ
        const keys = ['q1','q2','q3','q4','q5'];
        const perQ = {};
        keys.forEach(k => {
            const sum = surveys.reduce((s, e) => s + (e.ratings[k] || 0), 0);
            perQ[k] = sum / surveys.length;
        });

        const qContainer = document.getElementById('sv-per-question');
        qContainer.innerHTML = '';
        keys.forEach((k, i) => {
            const score = perQ[k];
            const pct = (score / 5) * 100;
            const stars = '★'.repeat(Math.round(score)) + '☆'.repeat(5 - Math.round(score));
            const colorBar = score >= 4 ? 'bg-emerald-500' : score >= 3 ? 'bg-amber-400' : 'bg-rose-500';
            const colorText = score >= 4 ? 'text-emerald-600' : score >= 3 ? 'text-amber-600' : 'text-rose-600';
            qContainer.innerHTML += `
                <div>
                    <div class="flex justify-between items-center mb-1">
                        <span class="text-sm font-semibold text-slate-700 flex-1 pr-4">${i+1}. ${QUESTION_LABELS[i]}</span>
                        <span class="text-sm font-bold ${colorText} whitespace-nowrap">${score.toFixed(2)} / 5</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex-1 bg-gray-100 h-3 rounded-full overflow-hidden">
                            <div class="${colorBar} h-3 rounded-full transition-all" style="width:${pct}%"></div>
                        </div>
                        <span class="text-amber-400 text-xs tracking-tight whitespace-nowrap">${stars}</span>
                    </div>
                </div>
            `;
        });

        // รายการประเมินล่าสุด (แสดง 10 รายการล่าสุด)
        const list = document.getElementById('sv-entries-list');
        list.innerHTML = '';
        [...surveys].reverse().slice(0, 10).forEach((e, i) => {
            const starsFill = '★'.repeat(Math.round(e.avg)) + '☆'.repeat(5 - Math.round(e.avg));
            const avgColor = e.avg >= 4 ? 'text-emerald-600 bg-emerald-50' : e.avg >= 3 ? 'text-amber-600 bg-amber-50' : 'text-rose-600 bg-rose-50';
            list.innerHTML += `
                <div class="flex items-center justify-between p-3 bg-slate-50 rounded-2xl border border-slate-100">
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 bg-pink-100 text-pink-600 rounded-xl flex items-center justify-center font-bold text-xs">${surveys.length - i}</div>
                        <div>
                            <p class="text-xs font-bold text-slate-700">${e.time} — <span class="text-pink-600 font-bold">${e.driverName || 'ทั่วไป'}</span></p>
                            <p class="text-[10px] text-amber-400 tracking-tight">${starsFill}</p>
                        </div>
                    </div>
                    <span class="text-sm font-black ${avgColor} px-3 py-1 rounded-xl">${e.avg.toFixed(2)}</span>
                </div>
            `;
        });

        // ความคิดเห็น
        const comments = surveys.filter(e => e.comment && e.comment.trim() !== '');
        const commentsEl = document.getElementById('sv-comments-list');
        if (comments.length === 0) {
            commentsEl.innerHTML = '<p class="text-slate-400 text-sm text-center py-4">ยังไม่มีความคิดเห็น</p>';
        } else {
            commentsEl.innerHTML = '';
            [...comments].reverse().forEach((e, i) => {
                commentsEl.innerHTML += `
                    <div class="flex gap-3 p-4 bg-slate-50 rounded-2xl border border-slate-100">
                        <div class="w-8 h-8 bg-pink-500 text-white rounded-xl flex items-center justify-center font-bold text-xs flex-shrink-0">
                            ${String.fromCharCode(65 + i)}
                        </div>
                        <div>
                            <p class="text-xs text-slate-700 leading-relaxed">${e.comment}</p>
                            <p class="text-[10px] text-slate-400 mt-1">${e.time}</p>
                        </div>
                    </div>
                `;
            });
        }
    }

    function clearSurveyData() {
        if (!confirm('ต้องการล้างข้อมูลแบบประเมินทั้งหมดใช่หรือไม่?')) return;
        localStorage.removeItem('yru_surveys');
        renderSurveyPage();
    }

    // ==========================================
    // 📡 REAL-TIME: ดึงยอดผู้ใช้บริการรวมวันนี้ และประเมินความพึงพอใจ
    // ==========================================
    let _prevTotalUsers = -1;

    function fetchTodayStats() {
        fetch('/api/get-today-stats')
            .then(res => {
                if (!res.ok) throw new Error('API error');
                return res.json();
            })
            .then(data => {
                // อัปเดตข้อมูลผู้ใช้สะสมวันนี้เมื่อมีข้อมูลจริงวิ่งเข้ามา
                systemData.totalUsers = data.today_total_users || 0;
                
                // อัปเดตข้อมูลการสแกนประเมินความพึงพอใจเมื่อมีผู้ใช้สแกนโหวตจริง
                if (data.satisfaction_count > 0) {
                    systemData.avgSatisfaction = data.avg_satisfaction;
                    systemData.satisfactionCount = data.satisfaction_count;
                    systemData.satisfScoreCount['5'] = data.satisf_5_count;
                    systemData.satisfScoreCount['3'] = data.satisf_3_count;
                    systemData.satisfScoreCount['1'] = data.satisf_1_count;
                }

                // อัปเดตข้อมูลผู้ใช้งานแยกคัน
                systemData.carUsers['1'] = data.car_1_users || 0;
                systemData.carUsers['2'] = data.car_2_users || 0;

                // อัปเดตข้อมูลผู้ใช้งานแยกตามสายวิ่ง (รวมข้อมูลฐานสถิติเดิม + ข้อมูลเรียลไทม์)
                systemData.routeUsers['1'] = (data.route_1_users || 0) + 45;
                systemData.routeUsers['2'] = (data.route_2_users || 0) + 30;
                systemData.routeUsers['3'] = (data.route_3_users || 0) + 25;

                // อัปเดตข้อมูลความปลอดภัย
                systemData.safetyCases = data.safety_cases ?? 0;
                systemData.safetyAlerts = data.safety_alerts ?? 2;
                systemData.safetyInspections = data.safety_inspections ?? 1;
                if (data.safety_logs) {
                    systemData.safetyLogs = data.safety_logs;
                }

                updateUIElements();

                // วาดกราฟหน้าแดชบอร์ดใหม่ถ้ากำลังแสดงอยู่
                const dashboardTab = document.getElementById('dashboard');
                if (dashboardTab && (dashboardTab.style.display === 'block' || dashboardTab.style.display === '')) {
                    renderMainChart();
                }

                // วาดกราฟสถิติใหม่ถ้ากำลังแสดงอยู่
                const statsTab = document.getElementById('stats');
                if (statsTab && statsTab.style.display === 'block') {
                    renderStatsCharts();
                }

                // วาดตารางความปลอดภัยใหม่ถ้ากำลังแสดงอยู่
                const safetyTab = document.getElementById('safety');
                if (safetyTab && safetyTab.style.display === 'block') {
                    renderSafetyLogs();
                }

                const newTotal = systemData.totalUsers;
                const elUsers = document.getElementById('kpi-total-users');
                if (elUsers && newTotal !== _prevTotalUsers) {
                    elUsers.style.transition = 'color 0.4s ease, transform 0.3s ease';
                    elUsers.style.color      = '#fff';
                    elUsers.style.transform  = 'scale(1.08)';
                    elUsers.innerText        = newTotal.toLocaleString();
                    setTimeout(() => { elUsers.style.transform = ''; }, 800);
                    _prevTotalUsers = newTotal;
                }
            })
            .catch(err => console.warn('fetchTodayStats error:', err));
    }

    // Polling ทุก 5 วินาที
    setInterval(fetchTodayStats, 5000);
</script>

</body>
</html>