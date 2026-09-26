<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('routes', function (Blueprint $table) {
            $table->string('route_code', 10)->primary(); // VARCHAR(10) PK
            $table->string('route_name', 100); // VARCHAR(100)
            $table->string('route_details', 255)->nullable(); // VARCHAR(255)
            $table->timestamps();
        });
    }

    public function down(): void {
        Schema::dropIfExists('routes');
    }
};
