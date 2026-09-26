@php
    $thaiMonthsList = ['', 'มกราคม', 'กุมภาพันธ์', 'มีนาคม', 'เมษายน', 'พฤษภาคม', 'มิถุนายน', 'กรกฎาคม', 'สิงหาคม', 'กันยายน', 'ตุลาคม', 'พฤศจิกายน', 'ธันวาคม'];
    $currentThaiFormattedDate = date('j') . ' ' . $thaiMonthsList[(int)date('n')] . ' ' . (date('Y') + 543);
@endphp
<div id="users" class="page">
            <div class="flex flex-col md:flex-row md:items-center md:justify-between gap-4 mb-6 bg-white p-5 md:p-6 rounded-2xl shadow-xs border border-gray-100">
                <div>
                    <h2 class="text-xl md:text-2xl font-black text-slate-800 tracking-tight">จัดการข้อมูลผู้ใช้งาน</h2>
                    <p class="text-xs md:text-sm text-slate-500 mt-1 font-medium">เพิ่ม แก้ไข ลบ กำหนดสิทธิ์ และนำเข้าข้อมูลบัญชีผู้ใช้งาน</p>
                    <p class="text-xs md:text-sm text-pink-600 font-bold mt-1.5 flex items-center gap-1.5"><i class="fas fa-calendar-alt text-xs"></i> <span>ณ วันที่ {{ $currentThaiFormattedDate }}</span></p>
                </div>

                <div class="flex flex-col sm:flex-row items-stretch sm:items-center gap-3">
                    <div class="relative flex-1 sm:w-64">
                        <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-gray-400">
                            <i class="fas fa-search"></i>
                        </span>
                        <input type="text" id="userSearchInput" onkeyup="searchUsers()" 
                            class="w-full pl-9 pr-4 py-2 border border-gray-300 rounded-lg focus:ring-2 focus:ring-pink-400 outline-none text-sm" 
                            placeholder="ค้นหาด้วยชื่อ อีเมล หรือสิทธิ์...">
                    </div>
                    <button onclick="openImportUserModal()" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-lg transition text-sm flex items-center justify-center gap-2 shadow-sm">
                        <i class="fas fa-file-import"></i> นำเข้าข้อมูล (Import)
                    </button>
                    <button onclick="openUserModal()" class="bg-pink-600 text-white px-4 py-2 rounded-lg hover:bg-pink-700 transition text-sm flex items-center justify-center gap-2 shadow-sm">
                        <i class="fas fa-user-plus"></i> เพิ่ม
                    </button>
                </div>
            </div>

            <div class="bg-white rounded-xl shadow-sm overflow-hidden overflow-x-auto">
                <table class="w-full text-left min-w-[900px]">
                    <thead class="bg-gray-50 text-gray-600 text-sm">
                        <tr>
                            <th class="p-4">รหัสประจำตัว</th>
                            <th class="p-4">ชื่อ-นามสกุล</th>
                            <th class="p-4">Username</th>
                            <th class="p-4">อีเมล</th>
                            <th class="p-4">เบอร์โทรศัพท์</th>
                            <th class="p-4">สิทธิ์</th>
                            <th class="p-4">สถานะ</th>
                            <th class="p-4 text-center">การจัดการข้อมูล</th>
                        </tr>
                    </thead>
                    <tbody id="userTable" class="text-sm"></tbody>
                </table>
            </div>
        </div>