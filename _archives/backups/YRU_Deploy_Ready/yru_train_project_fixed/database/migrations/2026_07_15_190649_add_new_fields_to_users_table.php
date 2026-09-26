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
        Schema::table('users', function (Blueprint $table) {
            $table->string('employee_id')->nullable()->unique()->after('user_id');
            $table->string('prefix', 50)->nullable()->after('employee_id');
            $table->string('first_name')->nullable()->after('prefix');
            $table->string('last_name')->nullable()->after('first_name');
            $table->string('phone_number')->nullable()->after('password');
            $table->string('status')->default('ใช้งาน')->after('user_role');
            $table->text('remark')->nullable()->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'employee_id',
                'prefix',
                'first_name',
                'last_name',
                'phone_number',
                'status',
                'remark'
            ]);
        });
    }
};
