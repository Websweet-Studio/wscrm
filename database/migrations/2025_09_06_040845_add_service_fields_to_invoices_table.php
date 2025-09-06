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
        Schema::table('invoices', function (Blueprint $table) {
            $table->enum('invoice_type', ['setup', 'renewal'])->default('setup')->after('invoice_number');
            $table->foreignId('service_id')->nullable()->constrained()->onDelete('cascade')->after('order_id');
            $table->date('issue_date')->default(now())->after('amount');
            $table->string('billing_cycle')->default('monthly')->after('due_date');
            
            // Update status enum to include 'pending'
            $table->enum('status', ['draft', 'pending', 'sent', 'paid', 'overdue', 'cancelled'])->default('pending')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('invoices', function (Blueprint $table) {
            $table->dropColumn(['invoice_type', 'service_id', 'issue_date', 'billing_cycle']);
            
            // Revert status enum
            $table->enum('status', ['draft', 'sent', 'paid', 'overdue', 'cancelled'])->default('draft')->change();
        });
    }
};
