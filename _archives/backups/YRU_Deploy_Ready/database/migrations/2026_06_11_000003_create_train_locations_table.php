<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('train_locations', function (Blueprint $table) {
            $table->increments('position_code'); // INT PK Auto-Increment
            $table->string('skytrain_code', 10); // FK
            $table->decimal('latitude', 10, 6); // DECIMAL(10,6)
            $table->decimal('longitude', 10, 6); // DECIMAL(10,6)
            $table->datetime('recorded_time'); // DATETIME
            $table->timestamps();

            // Foreign Key Constraint
            $table->foreign('skytrain_code')
                  ->references('skytrain_code')
                  ->on('electric_trains')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('train_locations');
    }
};
