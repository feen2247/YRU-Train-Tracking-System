<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('electric_trains', function (Blueprint $table) {
            $table->string('skytrain_code', 10)->primary(); // VARCHAR(10) PK
            $table->string('car_number', 10); // VARCHAR(10)
            $table->string('electric_train_type', 50); // VARCHAR(50)
            $table->string('car_status', 20); // VARCHAR(20) - Active, Maintenance, Inactive
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('electric_trains');
    }
};
