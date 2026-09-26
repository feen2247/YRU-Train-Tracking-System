<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Add new quotation fields to maintenance_requests table.
     */
    public function up(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('maintenance_requests', 'garage_to')) {
                $table->string('garage_to', 255)->nullable()->after('quotation_doc_url');
            }
            if (!Schema::hasColumn('maintenance_requests', 'garage_project')) {
                $table->string('garage_project', 500)->nullable()->after('garage_to');
            }
            if (!Schema::hasColumn('maintenance_requests', 'quotation_no')) {
                $table->string('quotation_no', 50)->nullable()->after('garage_project');
            }
            if (!Schema::hasColumn('maintenance_requests', 'quotation_date')) {
                $table->string('quotation_date', 30)->nullable()->after('quotation_no');
            }
            if (!Schema::hasColumn('maintenance_requests', 'subtotal')) {
                $table->decimal('subtotal', 12, 2)->default(0)->after('quotation_date');
            }
            if (!Schema::hasColumn('maintenance_requests', 'vat')) {
                $table->decimal('vat', 12, 2)->default(0)->after('subtotal');
            }
            if (!Schema::hasColumn('maintenance_requests', 'thai_baht_text')) {
                $table->string('thai_baht_text', 500)->nullable()->after('total_cost');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('maintenance_requests', function (Blueprint $table) {
            $table->dropColumn([
                'garage_to', 'garage_project', 'quotation_no', 'quotation_date',
                'subtotal', 'vat', 'thai_baht_text'
            ]);
        });
    }
};
