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
        Schema::table('orders', function (Blueprint $table) {
            // Skip adding pending_plan_id as it already exists
            if (! Schema::hasColumn('orders', 'change_status')) {
                $table->enum('change_status', ['none', 'pending', 'completed'])->default('none')->after('status');
            }
            if (! Schema::hasColumn('orders', 'change_requested_at')) {
                $table->timestamp('change_requested_at')->nullable()->after('change_status');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'change_status')) {
                $table->dropColumn('change_status');
            }
            if (Schema::hasColumn('orders', 'change_requested_at')) {
                $table->dropColumn('change_requested_at');
            }
        });
    }
};
