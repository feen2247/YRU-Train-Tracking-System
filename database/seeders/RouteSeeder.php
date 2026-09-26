<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Route;
use App\Models\RouteStop;
use App\Models\Station;
use App\Models\ElectricTrain;

class RouteSeeder extends Seeder
{
    public function run(): void
    {
        // Disable foreign key checks for clean seeding
        \Illuminate\Support\Facades\Schema::disableForeignKeyConstraints();
        RouteStop::truncate();
        Route::truncate();
        Station::truncate();
        \Illuminate\Support\Facades\Schema::enableForeignKeyConstraints();

        // 1. Seed the ONLY route in the system
        $route = Route::create([
            'route_code' => 'LINE-A',
            'route_name' => 'สาย 1: วิ่งรอบมหาวิทยาลัย (เนินขาม-หอพัก)',
            'route_details' => 'เส้นทางหลักวิ่งรับส่งรอบมหาวิทยาลัยราชภัฏยะลา ผ่านป้ายหยุดรถทั้ง 7 จุด',
            'route_color' => '#ec4899', // Pink theme
            'polyline_data' => [
                [6.549929, 101.291254],
                [6.549100, 101.290467],
                [6.547835, 101.289502],
                [6.547224, 101.289471],
                [6.547311, 101.288880],
                [6.548822, 101.288523],
                [6.549225, 101.289286]
            ]
        ]);

        // 2. Seed Stations (pointing to the single route)
        $defaultStops = [
            [ 'parking_spot_code' => 'จุดจอด 1 ประตูหลังมอ.', 'parking_spot_name' => 'จุดจอด 1 ประตูหลังมอ.', 'order_of_parking_spots' => 1, 'route_code' => 'LINE-A' ],
            [ 'parking_spot_code' => 'จุดจอด 2 ตึกศิลปะ', 'parking_spot_name' => 'จุดจอด 2 ตึกศิลปะ', 'order_of_parking_spots' => 2, 'route_code' => 'LINE-A' ],
            [ 'parking_spot_code' => 'จุดจอด 3 ศูนย์วิทยาศาสตร์', 'parking_spot_name' => 'จุดจอด 3 ศูนย์วิทยาศาสตร์', 'order_of_parking_spots' => 3, 'route_code' => 'LINE-A' ],
            [ 'parking_spot_code' => 'จุดจอด 4 คณะวิทยาศาสตร์', 'parking_spot_name' => 'จุดจอด 4 คณะวิทยาศาสตร์', 'order_of_parking_spots' => 4, 'route_code' => 'LINE-A' ],
            [ 'parking_spot_code' => 'จุดจอด 5 คณะสังคมศาสตร์', 'parking_spot_name' => 'จุดจอด 5 คณะสังคมศาสตร์', 'order_of_parking_spots' => 5, 'route_code' => 'LINE-A' ],
            [ 'parking_spot_code' => 'จุดจอด 6 อาคารเรียน20', 'parking_spot_name' => 'จุดจอด 6 อาคารเรียน20', 'order_of_parking_spots' => 6, 'route_code' => 'LINE-A' ],
            [ 'parking_spot_code' => 'จุดจอด 7 คณะวิทยาการจัดการ', 'parking_spot_name' => 'จุดจอด 7 คณะวิทยาการจัดการ', 'order_of_parking_spots' => 7, 'route_code' => 'LINE-A' ],
        ];

        foreach ($defaultStops as $s) {
            Station::create($s);
        }

        // 3. Seed RouteStops relationships for all 7 stops
        foreach ($defaultStops as $s) {
            RouteStop::create([
                'route_code' => 'LINE-A',
                'parking_spot_code' => $s['parking_spot_code'],
                'stop_order' => $s['order_of_parking_spots']
            ]);
        }

        // 4. Force assign all existing EV cars in the DB to this single route code
        ElectricTrain::query()->update(['route_code' => 'LINE-A']);
    }
}
