<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::table('routes', function (Blueprint $table) {
            if (!Schema::hasColumn('routes', 'route_color')) {
                $table->string('route_color', 50)->nullable()->default('#ec4899'); // Default pink color
            }
        });
    }

    public function down(): void {
        Schema::table('routes', function (Blueprint $table) {
            if (Schema::hasColumn('routes', 'route_color')) {
                $table->dropColumn('route_color');
            }
        });
    }
};
