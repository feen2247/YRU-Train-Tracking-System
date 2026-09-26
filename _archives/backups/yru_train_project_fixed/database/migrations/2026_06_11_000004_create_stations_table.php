<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('stations', function (Blueprint $table) {
            $table->string('parking_spot_code', 10)->primary(); // VARCHAR(10) PK
            $table->string('route_code', 10); // FK
            $table->string('parking_spot_name', 100); // VARCHAR(100)
            $table->integer('order_of_parking_spots'); // INT
            $table->timestamps();

            // Foreign Key Constraint
            $table->foreign('route_code')
                  ->references('route_code')
                  ->on('routes')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('stations');
    }
};
