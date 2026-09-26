@php
    $thaiMonthsList = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
    $currentThaiFormattedDate = date('j') . ' ' . $thaiMonthsList[(int)date('n')] . ' ' . (date('Y') + 543);
@endphp
<div id="tram" class="page">
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-6 bg-white p-5 md:p-6 rounded-2xl shadow-xs border border-gray-100">
                <div>
                    <h2 class="text-xl md:text-2xl font-black text-slate-800 tracking-tight">จัดการรถไฟฟ้าในระบบ</h2>
                    <p class="text-xs md:text-sm text-slate-500 mt-1 font-medium">เพิ่ม ลบ หรือแก้ไขสถานะรถไฟฟ้าบริการภายในมหาวิทยาลัย</p>
                    <p class="text-xs md:text-sm text-pink-600 font-bold mt-1.5 flex items-center gap-1.5"><i class="fas fa-calendar-alt text-xs"></i> <span>ณ วันที่ {{ $currentThaiFormattedDate }}</span></p>
                </div>

                <button onclick="openTramModal()" class="bg-pink-600 hover:bg-pink-700 text-white px-4 py-2 rounded-xl transition shadow-xs text-xs font-bold flex items-center gap-1.5 active:scale-95">
                    <i class="fas fa-plus"></i> เพิ่ม
                </button>
            </div>

            <div class="bg-white p-4 rounded-xl shadow-sm mb-6 flex flex-wrap gap-4 items-center justify-between">
                <div class="flex flex-wrap gap-3 items-center">
                    <span class="text-sm text-gray-600 font-medium"><i class="fas fa-filter mr-1"></i> ตัวกรองข้อมูล:</span>
                    <select id="tramFilterStatus" onchange="filterTrams()" class="border border-gray-300 p-1.5 rounded-lg text-sm bg-white focus:ring-2 focus:ring-pink-400 outline-none">
                        <option value="ทั้งหมด">แสดงสถานะทั้งหมด</option>
                        <option value="พร้อมใช้งาน">พร้อมใช้งาน</option>
                        <option value="รถขัดข้อง">รถขัดข้อง</option>
                        <option value="ระงับการใช้งาน">ระงับการใช้งาน</option>
                    </select>
                </div>
                <div class="relative w-full sm:w-80">
                    <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                        <i class="fas fa-search"></i>
                    </span>
                    <input type="text" id="tramSearchInput" onkeyup="filterTrams()" 
                        class="w-full pl-9 pr-4 py-1.5 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none text-sm" 
                        placeholder="ค้นหา รหัสรถ, ทะเบียน, หรือชื่อคนขับ...">
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden overflow-x-auto">
                <table class="w-full text-left min-w-[900px]">
                    <thead class="bg-gray-50 text-gray-600 text-xs uppercase tracking-wider">
                        <tr>
                            <th class="p-4 w-20">รูปภาพ</th>
                            <th class="p-4">รหัส / ชื่อเรียก</th>
                            <th class="p-4 min-w-[150px]">ทะเบียนรถ</th>
                            <th class="p-4">ความจุ</th>
                            <th class="p-4">คนขับประจำ</th>
                            <th class="p-4 text-center">สถานะการใช้งาน</th>
                            <th class="p-4 text-center w-44">การจัดการข้อมูล</th>
                        </tr>
                    </thead>
                    <tbody id="tramTable" class="text-sm divide-y divide-gray-100"></tbody>
                </table>
            </div>
        </div>