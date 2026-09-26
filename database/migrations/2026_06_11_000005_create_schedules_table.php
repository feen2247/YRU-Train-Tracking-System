<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('schedules', function (Blueprint $table) {
            $table->string('timetable_code', 10)->primary(); // VARCHAR(10) PK
            $table->string('skytrain_code', 10); // FK
            $table->string('driver_id', 10); // FK references users(user_id)
            $table->string('route_code', 10); // FK
            $table->time('departure_time'); // TIME
            $table->time('arrival_time'); // TIME
            $table->date('date'); // DATE
            $table->timestamps();

            // Foreign Key Constraints
            $table->foreign('skytrain_code')
                  ->references('skytrain_code')
                  ->on('electric_trains')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('driver_id')
                  ->references('user_id')
                  ->on('users')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('route_code')
                  ->references('route_code')
                  ->on('routes')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('schedules');
    }
};
