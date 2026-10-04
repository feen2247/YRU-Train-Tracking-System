<?php
require __DIR__ . '/vendor/autoload.php';
$app = require_once __DIR__ . '/bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

$initialTrams = [
    [
        "id" => "EV-01",
        "name" => "รถไฟฟ้าคันที่ 1",
        "plate" => "กค 1234 ยะลา",
        "capacity_sit" => 20,
        "capacity_stand" => 10,
        "status" => "พร้อมใช้งาน",
        "gps_id" => "GPS-EV01-YRU",
        "route" => "สาย 1: หน้ามรย-หอพัก",
        "driver" => "นายอัสมี มูเล็ง",
        "driver_id" => "USR003",
        "battery" => 85,
        "image" => "",
        "coords" => "6.549929, 101.291254",
        "active_issue" => "",
        "updated_by" => "admin@yru.ac.th",
        "updated_at" => "12 มี.ค. 2568",
        "purchase_date" => "2025-03-12",
        "warranty" => "5 ปี (สิ้นสุด 12 มีนาคม 2573)",
        "supplier" => "บริษัท ยะลายานยนต์ อีวี จำกัด (โทร. 073-123456)",
        "maintenance" => []
    ],
    [
        "id" => "EV-02",
        "name" => "รถไฟฟ้าคันที่ 2",
        "plate" => "กค 5678 ยะลา",
        "capacity_sit" => 16,
        "capacity_stand" => 8,
        "status" => "พร้อมใช้งาน",
        "gps_id" => "GPS-EV02-YRU",
        "route" => "สาย 2: วงเวียน-คณะวิทยาศาสตร์",
        "driver" => "นายอัรฟาน มะเระ",
        "driver_id" => "USR004",
        "battery" => 92,
        "image" => "",
        "coords" => "6.549100, 101.290467",
        "active_issue" => "",
        "updated_by" => "admin@yru.ac.th",
        "updated_at" => "12 มี.ค. 2568",
        "purchase_date" => "2025-03-12",
        "warranty" => "5 ปี (สิ้นสุด 12 มีนาคม 2573)",
        "supplier" => "บริษัท ยะลายานยนต์ อีวี จำกัด (โทร. 073-123456)",
        "maintenance" => []
    ],
    [
        "id" => "EV-03",
        "name" => "รถไฟฟ้าคันที่ 3",
        "plate" => "กค 9012 ยะลา",
        "capacity_sit" => 20,
        "capacity_stand" => 10,
        "status" => "พร้อมใช้งาน",
        "gps_id" => "GPS-EV03-YRU",
        "route" => "สาย 1: หน้ามรย-หอพัก",
        "driver" => "นายซูเฟียน มะโละ",
        "driver_id" => "USR005",
        "battery" => 78,
        "image" => "",
        "coords" => "6.547835, 101.289502",
        "active_issue" => "",
        "updated_by" => "admin@yru.ac.th",
        "updated_at" => "12 มี.ค. 2568",
        "purchase_date" => "2025-03-12",
        "warranty" => "5 ปี (สิ้นสุด 12 มีนาคม 2573)",
        "supplier" => "บริษัท ยะลายานยนต์ อีวี จำกัด (โทร. 073-123456)",
        "maintenance" => []
    ],
    [
        "id" => "EV-04",
        "name" => "รถไฟฟ้าคันที่ 4",
        "plate" => "กค 3456 ยะลา",
        "capacity_sit" => 18,
        "capacity_stand" => 10,
        "status" => "พร้อมใช้งาน",
        "gps_id" => "GPS-EV04-YRU",
        "route" => "สาย 1: หน้ามรย-หอพัก",
        "driver" => "นายอุสมาน สาและ",
        "driver_id" => "USR006",
        "battery" => 78,
        "image" => "",
        "coords" => "6.547224, 101.289471",
        "active_issue" => "",
        "updated_by" => "admin@yru.ac.th",
        "updated_at" => "12 มี.ค. 2568",
        "purchase_date" => "2024-05-20",
        "warranty" => "3 ปี (สิ้นสุด 20 พฤษภาคม 2570)",
        "supplier" => "บริษัท ยะลายานยนต์ อีวี จำกัด (โทร. 081-2345678)",
        "maintenance" => []
    ],
    [
        "id" => "EV-05",
        "name" => "รถไฟฟ้าคันที่ 5",
        "plate" => "กค 7890 ยะลา",
        "capacity_sit" => 20,
        "capacity_stand" => 10,
        "status" => "พร้อมใช้งาน",
        "gps_id" => "GPS-EV05-YRU",
        "route" => "สาย 2: วงเวียน-คณะวิทยาศาสตร์",
        "driver" => "นายบัดรี สาและ",
        "driver_id" => "USR007",
        "battery" => 95,
        "image" => "",
        "coords" => "6.547311, 101.288880",
        "active_issue" => "",
        "updated_by" => "admin@yru.ac.th",
        "updated_at" => "12 มี.ค. 2568",
        "purchase_date" => "2025-01-10",
        "warranty" => "3 ปี (สิ้นสุด 10 มกราคม 2571)",
        "supplier" => "บริษัท ยะลายานยนต์ อีวี จำกัด",
        "maintenance" => []
    ],
    [
        "id" => "EV-06",
        "name" => "รถไฟฟ้าคันที่ 6",
        "plate" => "กค 1122 ยะลา",
        "capacity_sit" => 16,
        "capacity_stand" => 8,
        "status" => "พร้อมใช้งาน",
        "gps_id" => "GPS-EV06-YRU",
        "route" => "สาย 3: ประตูหลังมอ-หอประชุม",
        "driver" => "นายตอริก ลือแมะ",
        "driver_id" => "USR008",
        "battery" => 89,
        "image" => "",
        "coords" => "6.548822, 101.288523",
        "active_issue" => "",
        "updated_by" => "admin@yru.ac.th",
        "updated_at" => "12 มี.ค. 2568",
        "purchase_date" => "2025-02-15",
        "warranty" => "3 ปี (สิ้นสุด 15 กุมภาพันธ์ 2571)",
        "supplier" => "บริษัท ยะลายานยนต์ อีวี จำกัด",
        "maintenance" => []
    ],
    [
        "id" => "EV-07",
        "name" => "รถไฟฟ้าคันที่ 7",
        "plate" => "กค 3344 ยะลา",
        "capacity_sit" => 18,
        "capacity_stand" => 8,
        "status" => "พร้อมใช้งาน",
        "gps_id" => "GPS-EV07-YRU",
        "route" => "สาย 1: หน้ามรย-หอพัก",
        "driver" => "นายสมหวัง ใจดี",
        "driver_id" => "USR009",
        "battery" => 82,
        "image" => "",
        "coords" => "6.549225, 101.289286",
        "active_issue" => "",
        "updated_by" => "admin@yru.ac.th",
        "updated_at" => "12 มี.ค. 2568",
        "purchase_date" => "2025-03-01",
        "warranty" => "3 ปี (สิ้นสุด 1 มีนาคม 2571)",
        "supplier" => "บริษัท ยะลายานยนต์ อีวี จำกัด",
        "maintenance" => []
    ],
    [
        "id" => "EV-08",
        "name" => "รถไฟฟ้าคันที่ 8",
        "plate" => "กค 5566 ยะลา",
        "capacity_sit" => 20,
        "capacity_stand" => 10,
        "status" => "พร้อมใช้งาน",
        "gps_id" => "GPS-EV08-YRU",
        "route" => "สาย 2: วงเวียน-คณะวิทยาศาสตร์",
        "driver" => "นายสมใจ ใจดี",
        "driver_id" => "USR010",
        "battery" => 90,
        "image" => "",
        "coords" => "6.549929, 101.291254",
        "active_issue" => "",
        "updated_by" => "admin@yru.ac.th",
        "updated_at" => "12 มี.ค. 2568",
        "purchase_date" => "2025-03-20",
        "warranty" => "3 ปี (สิ้นสุด 20 มีนาคม 2571)",
        "supplier" => "บริษัท ยะลายานยนต์ อีวี จำกัด",
        "maintenance" => []
    ],
    [
        "id" => "EV-09",
        "name" => "รถไฟฟ้าคันที่ 9",
        "plate" => "กค 7788 ยะลา",
        "capacity_sit" => 16,
        "capacity_stand" => 8,
        "status" => "พร้อมใช้งาน",
        "gps_id" => "GPS-EV09-YRU",
        "route" => "สาย 3: ประตูหลังมอ-หอประชุม",
        "driver" => "นายกิตติ ตั้งใจ",
        "driver_id" => "USR011",
        "battery" => 87,
        "image" => "",
        "coords" => "6.547835, 101.289502",
        "active_issue" => "",
        "updated_by" => "admin@yru.ac.th",
        "updated_at" => "12 มี.ค. 2568",
        "purchase_date" => "2025-04-05",
        "warranty" => "3 ปี (สิ้นสุด 5 เมษายน 2571)",
        "supplier" => "บริษัท ยะลายานยนต์ อีวี จำกัด",
        "maintenance" => []
    ],
    [
        "id" => "EV-10",
        "name" => "รถไฟฟ้าคันที่ 10",
        "plate" => "กค 9900 ยะลา",
        "capacity_sit" => 18,
        "capacity_stand" => 10,
        "status" => "พร้อมใช้งาน",
        "gps_id" => "GPS-EV10-YRU",
        "route" => "สาย 1: หน้ามรย-หอพัก",
        "driver" => "นายรุสลัน สอเฮาะ",
        "driver_id" => "USR012",
        "battery" => 91,
        "image" => "",
        "coords" => "6.547311, 101.288880",
        "active_issue" => "",
        "updated_by" => "admin@yru.ac.th",
        "updated_at" => "12 มี.ค. 2568",
        "purchase_date" => "2025-05-10",
        "warranty" => "3 ปี (สิ้นสุด 10 พฤษภาคม 2571)",
        "supplier" => "บริษัท ยะลายานยนต์ อีวี จำกัด",
        "maintenance" => []
    ]
];

// 1. Write persistent JSON file
$filePath = storage_path('app/yru_trams_persistent.json');
file_put_contents($filePath, json_encode($initialTrams, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT));
echo "Saved persistent file: {$filePath}\n";

// 2. Update Laravel Cache
$jsonRaw = json_encode($initialTrams, JSON_UNESCAPED_UNICODE);
Cache::forever('global_storage_yru_trams_v18', $jsonRaw);
Cache::forever('global_storage_yru_trams_v16', $jsonRaw);
echo "Updated Cache\n";

// 3. Update Database electric_trains table
if (\Illuminate\Support\Facades\Schema::hasTable('electric_trains')) {
    foreach ($initialTrams as $t) {
        $cId = $t['id'];
        $num = (string)preg_replace('/[^0-9]/', '', $cId);
        $seats = intval($t['capacity_sit']);
        $cStatus = $t['status'];
        
        DB::table('electric_trains')->updateOrInsert(
            ['car_id' => $cId],
            [
                'skytrain_code' => $cId,
                'car_number' => $num ?: '1',
                'car_name' => $t['name'],
                'electric_train_type' => 'EV Tram',
                'number_of_seats' => $seats,
                'car_status' => $cStatus === 'พร้อมใช้งาน' ? 'Active' : $cStatus,
                'status' => $cStatus,
                'updated_at' => now(),
            ]
        );
        echo "Updated DB: {$cId} -> {$seats} seats\n";
    }
}

echo "DONE!\n";
