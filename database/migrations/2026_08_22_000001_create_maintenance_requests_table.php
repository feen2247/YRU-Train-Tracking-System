<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (!Schema::hasTable('maintenance_requests')) {
            Schema::create('maintenance_requests', function (Blueprint $table) {
                $table->id();
                $table->string('ticket_no', 50)->unique();
                
                // Step 1: Driver Request
                $table->string('doc_date', 10)->nullable();
                $table->string('doc_month', 30)->nullable();
                $table->string('doc_year', 10)->nullable();
                $table->string('driver_id', 50)->nullable();
                $table->string('driver_name', 255)->nullable();
                $table->string('car_id', 50)->nullable();
                $table->string('license_plate', 50)->nullable();
                $table->string('brand', 100)->default('YRU EV');
                $table->string('model', 100)->default('Tram Electric');
                $table->integer('mileage')->default(0);
                $table->text('issues')->nullable(); // JSON array
                $table->string('driver_signature', 255)->nullable();
                $table->string('urgency', 50)->default('normal');
                
                // Step 2: Supervisor Verification
                $table->string('supervisor_id', 50)->nullable();
                $table->string('supervisor_name', 255)->nullable();
                $table->text('supervisor_notes')->nullable();
                $table->timestamp('supervisor_verified_at')->nullable();
                
                // Step 3: Quotation Submission (Mechanic/Garage)
                $table->string('garage_name', 255)->nullable();
                $table->string('garage_manager', 255)->nullable();
                $table->string('mechanic_name', 255)->nullable();
                $table->text('quotation_items')->nullable(); // JSON array
                $table->decimal('parts_cost', 10, 2)->default(0);
                $table->decimal('labor_cost', 10, 2)->default(0);
                $table->decimal('total_cost', 10, 2)->default(0);
                $table->integer('estimated_days')->default(1);
                $table->string('quotation_doc_url', 500)->nullable();
                
                // Step 4: Director Approval
                $table->string('director_opinion', 50)->nullable(); // approved / rejected
                $table->string('budget_type', 50)->nullable(); // government_budget / revenue_budget
                $table->string('revenue_budget_source', 255)->nullable();
                $table->string('director_name', 255)->nullable();
                $table->timestamp('director_signed_at')->nullable();
                $table->text('director_remarks')->nullable();
                
                // Step 5: Completion & Billing
                $table->string('receiver_name', 255)->nullable();
                $table->timestamp('completed_at')->nullable();
                $table->string('archive_no', 50)->nullable();
                $table->string('archive_date', 20)->nullable();
                $table->string('receipt_doc_url', 500)->nullable();
                $table->text('completion_notes')->nullable();
                $table->text('photos')->nullable(); // JSON array
                
                $table->string('status', 50)->default('pending_supervisor');
                $table->timestamps();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_requests');
    }
};
