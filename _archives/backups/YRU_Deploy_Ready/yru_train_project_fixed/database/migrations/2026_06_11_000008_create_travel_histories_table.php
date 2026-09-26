<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('travel_histories', function (Blueprint $table) {
            $table->id();
            $table->string('skytrain_code', 10);
            $table->string('driver_id', 10)->nullable();
            $table->string('route_code', 10);
            $table->datetime('start_time');
            $table->datetime('end_time')->nullable();
            $table->decimal('distance_km', 8, 2)->default(0.00);
            $table->string('travel_status', 50); // e.g. Running, Completed, Interrupted
            $table->timestamps();

            $table->foreign('skytrain_code')
                  ->references('skytrain_code')
                  ->on('electric_trains')
                  ->onDelete('cascade');

            $table->foreign('driver_id')
                  ->references('user_id')
                  ->on('users')
                  ->onDelete('set null');

            $table->foreign('route_code')
                  ->references('route_code')
                  ->on('routes')
                  ->onDelete('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('travel_histories');
    }
};
