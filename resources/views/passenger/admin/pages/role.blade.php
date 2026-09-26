@php
    $thaiMonthsList = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
    $currentThaiFormattedDate = date('j') . ' ' . $thaiMonthsList[(int)date('n')] . ' ' . (date('Y') + 543);
@endphp
<div id="role" class="page">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6 bg-white p-5 md:p-6 rounded-2xl shadow-xs border border-gray-100">
        <div>
            <h2 class="text-xl md:text-2xl font-black text-slate-800 tracking-tight">รายงานสิทธิ์และการเข้าสู่ระบบแต่ละบทบาท</h2>
            <p class="text-xs md:text-sm text-slate-500 mt-1 font-medium">ตารางแสดงขอบเขตสิทธิ์การเข้าถึงฟังก์ชัน และทางลัดเข้าสู่หน้าแดชบอร์ดของแต่ละกลุ่มผู้ใช้งาน</p>
            <p class="text-xs md:text-sm text-pink-600 font-bold mt-1.5 flex items-center gap-1.5"><i class="fas fa-calendar-alt text-xs"></i> <span>ณ วันที่ {{ $currentThaiFormattedDate }}</span></p>
        </div>
    </div>



    <div class="bg-white rounded-xl shadow-sm overflow-hidden overflow-x-auto">
        <table class="w-full text-left min-w-[850px]">
            <thead>
                <tr class="bg-gradient-to-r from-pink-50 to-pink-100 text-gray-700 text-sm">
                    <th class="p-4 font-semibold w-56 border-b border-gray-200">เมนู / ฟังก์ชัน</th>
                    <th class="p-3 text-center font-semibold border-b border-gray-200">
                        <a href="{{ url('/admin-view') }}" class="inline-flex items-center gap-1.5 bg-red-100 hover:bg-red-200 text-red-700 px-3 py-1.5 rounded-full text-xs font-bold transition shadow-2xs" title="คลิกเพื่อเข้าหน้าผู้ดูแลระบบ">
                            <i class="fas fa-shield-alt"></i> ผู้ดูแลระบบ
                        </a>
                    </th>
                    <th class="p-3 text-center font-semibold border-b border-gray-200">
                        <a href="{{ url('/home') }}" class="inline-flex items-center gap-1.5 bg-blue-100 hover:bg-blue-200 text-blue-700 px-3 py-1.5 rounded-full text-xs font-bold transition shadow-2xs" title="คลิกเพื่อเข้าหน้านักศึกษา">
                            <i class="fas fa-graduation-cap"></i> นักศึกษา
                        </a>
                    </th>
                    <th class="p-3 text-center font-semibold border-b border-gray-200">
                        <a href="{{ url('/tracking') }}" class="inline-flex items-center gap-1.5 bg-pink-100 hover:bg-pink-200 text-pink-700 px-3 py-1.5 rounded-full text-xs font-bold transition shadow-2xs" title="คลิกเพื่อเข้าหน้ารถไฟฟ้า (คนขับ)">
                            <i class="fas fa-car"></i> พนักงานขับรถ
                        </a>
                    </th>
                    <th class="p-3 text-center font-semibold border-b border-gray-200">
                        <a href="{{ url('/executive-view') }}" class="inline-flex items-center gap-1.5 bg-purple-100 hover:bg-purple-200 text-purple-700 px-3 py-1.5 rounded-full text-xs font-bold transition shadow-2xs" title="คลิกเพื่อเข้าหน้าผู้บริหาร">
                            <i class="fas fa-user-tie"></i> ผู้บริหาร
                        </a>
                    </th>
                    <th class="p-3 text-center font-semibold border-b border-gray-200">
                        <a href="{{ url('/maintenance-system') }}" class="inline-flex items-center gap-1.5 bg-green-100 hover:bg-green-200 text-green-700 px-3 py-1.5 rounded-full text-xs font-bold transition shadow-2xs" title="คลิกเพื่อเข้าหน้าช่างซ่อม">
                            <i class="fas fa-tools"></i> ช่างซ่อม
                        </a>
                    </th>
                    <th class="p-3 text-center font-semibold border-b border-gray-200">
                        <a href="{{ url('/vehicle-head') }}" class="inline-flex items-center gap-1.5 bg-amber-100 hover:bg-amber-200 text-amber-800 px-3 py-1.5 rounded-full text-xs font-bold transition shadow-2xs" title="คลิกเพื่อเข้าหน้าหัวหน้ายานพาหนะ">
                            <i class="fas fa-user-shield"></i> หัวหน้ายานพาหนะ
                        </a>
                    </th>
                </tr>
            </thead>
            <tbody id="rolePermissionTable" class="text-sm divide-y divide-gray-100">
                <!-- Rows rendered by JavaScript -->
            </tbody>
        </table>
    </div>
</div>