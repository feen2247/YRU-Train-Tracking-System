<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void {
        Schema::create('maintenances', function (Blueprint $table) {
            $table->string('maintenance_code', 10)->primary(); // VARCHAR(10) PK
            $table->string('user_id', 10); // FK references users(user_id)
            $table->string('skytrain_code', 10); // FK references electric_trains(skytrain_code)
            $table->string('repair_details', 255); // VARCHAR(255)
            $table->date('repair_notification_date'); // DATE
            $table->date('repair_start_date')->nullable(); // DATE
            $table->date('date_of_repair_completion')->nullable(); // DATE
            $table->string('repair_status', 50); // VARCHAR(50)
            $table->timestamps();

            // Foreign Key Constraints
            $table->foreign('user_id')
                  ->references('user_id')
                  ->on('users')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');

            $table->foreign('skytrain_code')
                  ->references('skytrain_code')
                  ->on('electric_trains')
                  ->onDelete('cascade')
                  ->onUpdate('cascade');
        });
    }

    public function down(): void {
        Schema::dropIfExists('maintenances');
    }
};
