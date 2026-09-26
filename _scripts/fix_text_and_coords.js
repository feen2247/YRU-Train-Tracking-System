const fs = require('fs');
const path = require('path');

// 1. Fix home/index.blade.php
const homePath = path.join(__dirname, '..', 'resources', 'views', 'passenger', 'home', 'index.blade.php');
let homeContent = fs.readFileSync(homePath, 'utf8');

// Replace lines around 570-610
const homeBadSectionRegex = /<div id="mapLegendWrapper">[\s\S]*?<\/button>\s*<\/div>\s*<\/div>\s*<\/div>\s*<\/main>/;
const homeGoodSection = `<div id="mapLegendWrapper">
                    <div class="flex flex-col gap-2.5">
                        <div class="flex items-center gap-2.5">
                            <div class="w-6 h-0 border-t-2 border-dashed border-pink-500"></div>
                            <span class="text-[9.5px] text-slate-600 font-bold">เส้นทางเดินรถ</span>
                        </div>
                        <div class="flex items-center gap-2.5">
                            <div class="w-3.5 h-3.5 rounded-full border-2 border-pink-500 bg-white flex items-center justify-center">
                                <div class="w-1.5 h-1.5 bg-pink-500 rounded-full"></div>
                            </div>
                            <span class="text-[9.5px] text-slate-600 font-bold">จุดจอดรับ-ส่ง</span>
                        </div>
                    </div>
                </div>

            </div>

            <!-- Bottom bar -->
            <div class="px-6 py-4.5 bg-white rounded-[2rem] border-2 border-pink-200 relative z-50 shadow-[0_8px_30px_rgb(0,0,0,0.02)] flex-shrink-0">
                <div class="flex flex-col sm:flex-row justify-between items-center gap-4">
                    <div class="flex items-center gap-3">
                        <div class="bg-pink-50 text-pink-500 p-3.5 rounded-2xl border border-pink-100 pulse-container"><i class="fas fa-route text-lg"></i></div>
                        <div id="statusPassengerDisplay">
                            <p class="text-[10px] text-slate-400 font-semibold">สถานะการให้บริการ</p>
                            <p class="text-sm font-bold text-slate-700 flex items-center gap-2">
                                <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 animate-pulse inline-block"></span>
                                ยังไม่มีรายการเรียกรถในขณะนี้ <span class="text-xs text-slate-500 font-normal hidden md:inline">(กดปุ่ม "เรียกรถที่นี่" เพื่อเริ่มเรียกรถไฟฟ้า)</span>
                            </p>
                        </div>
                    </div>

                    <div class="flex items-center gap-3 w-full sm:w-auto justify-end">
                        <button onclick="openCallModal()" class="bg-emerald-500 hover:bg-emerald-600 text-white px-8 py-3.5 rounded-2xl font-bold flex items-center gap-2.5 shadow-lg shadow-emerald-500/20 transition-all active:scale-95">
                            <i class="fas fa-hand-paper text-sm"></i>
                            <span>เรียกรถที่นี่</span>
                        </button>
                    </div>
                </div>
            </div>
        </main>`;

if (homeBadSectionRegex.test(homeContent)) {
    homeContent = homeContent.replace(homeBadSectionRegex, homeGoodSection);
}

// Ensure EV-01 coords = Stop 1 ("6.549929, 101.291254")
homeContent = homeContent.replace(
    'coords: "6.548900, 101.291700"',
    'coords: "6.549929, 101.291254"'
);

fs.writeFileSync(homePath, homeContent, 'utf8');
console.log('home/index.blade.php updated successfully');

// 2. Fix tracking/index.blade.php
const trackingPath = path.join(__dirname, '..', 'resources', 'views', 'passenger', 'tracking', 'index.blade.php');
let trackingContent = fs.readFileSync(trackingPath, 'utf8');

// Ensure EV-01 default coords = Stop 1
trackingContent = trackingContent.replace(
    '{ id: "EV-01", name: "รถไฟฟ้าคันที่ 1", plate: "กค 1234 ยะลา", capacity_sit: 20, capacity_stand: 10, status: "พร้อมใช้งาน", gps_id: "GPS-EV01-YRU", route: "สาย 1: เนินขาม-หอพัก", driver: "นายอัสมี มูเล็ง", driver_id: "USR003", battery: 85, image: "", coords: "6.548900, 101.291700"',
    '{ id: "EV-01", name: "รถไฟฟ้าคันที่ 1", plate: "กค 1234 ยะลา", capacity_sit: 20, capacity_stand: 10, status: "พร้อมใช้งาน", gps_id: "GPS-EV01-YRU", route: "สาย 1: เนินขาม-หอพัก", driver: "นายอัสมี มูเล็ง", driver_id: "USR003", battery: 85, image: "", coords: "6.549929, 101.291254"'
);

// In defaultTramCoords
trackingContent = trackingContent.replace(
    '"EV-01": "6.548900, 101.291700"',
    '"EV-01": "6.549929, 101.291254"'
);

fs.writeFileSync(trackingPath, trackingContent, 'utf8');
console.log('tracking/index.blade.php updated successfully');
