<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        // 1. Update routes table to add polyline_data
        Schema::table('routes', function (Blueprint $table) {
            if (!Schema::hasColumn('routes', 'polyline_data')) {
                $table->longText('polyline_data')->nullable(); // JSON coordinates [[lat, lng], ...]
            }
        });

        // 2. Create route_stops junction table
        Schema::create('route_stops', function (Blueprint $table) {
            $table->id();
            $table->string('route_code', 10);
            $table->string('parking_spot_code', 255);
            $table->integer('stop_order'); // order of stop in route sequence
            $table->timestamps();

            $table->foreign('route_code')
                  ->references('route_code')
                  ->on('routes')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });

        // 3. Add route_code to electric_trains table
        Schema::table('electric_trains', function (Blueprint $table) {
            if (!Schema::hasColumn('electric_trains', 'route_code')) {
                $table->string('route_code', 10)->nullable();
                $table->foreign('route_code')
                      ->references('route_code')
                      ->on('routes')
                      ->onDelete('set null')
                      ->onUpdate('cascade');
            }
        });
    }

    public function down(): void {
        Schema::table('electric_trains', function (Blueprint $table) {
            if (Schema::hasColumn('electric_trains', 'route_code')) {
                $table->dropForeign(['route_code']);
                $table->dropColumn('route_code');
            }
        });

        Schema::dropIfExists('route_stops');

        Schema::table('routes', function (Blueprint $table) {
            if (Schema::hasColumn('routes', 'polyline_data')) {
                $table->dropColumn('polyline_data');
            }
        });
    }
};
