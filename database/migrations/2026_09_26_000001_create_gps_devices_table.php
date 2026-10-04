<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        if (!Schema::hasTable('gps_devices')) {
            Schema::create('gps_devices', function (Blueprint $table) {
                $table->id();
                $table->string('device_id', 50)->unique(); // รหัสอุปกรณ์ที่ ESP32 ส่งมา เช่น ESP32-A1B2C3
                $table->string('name', 100)->nullable(); // ชื่อเรียกที่แอดมินตั้ง
                $table->string('vehicle_id', 20)->nullable()->unique(); // รหัสรถ เช่น EV-01 (1 GPS = 1 คัน)
                $table->decimal('latitude', 10, 7)->nullable();
                $table->decimal('longitude', 10, 7)->nullable();
                $table->decimal('speed_kmh', 6, 2)->nullable();
                $table->unsignedSmallInteger('satellites')->nullable();
                $table->decimal('hdop', 5, 2)->nullable();
                $table->timestamp('last_seen_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void {
        Schema::dropIfExists('gps_devices');
    }
};
