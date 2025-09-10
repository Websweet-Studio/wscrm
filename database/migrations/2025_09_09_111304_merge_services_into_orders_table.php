<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Add fields from services table to orders table
        Schema::table('orders', function (Blueprint $table) {
            // Service-specific fields
            $table->enum('service_type', ['hosting', 'domain'])->nullable()->after('order_type');
            $table->unsignedBigInteger('plan_id')->nullable()->after('service_type');
            $table->string('domain_name', 191)->nullable()->after('plan_id');

            // Service lifecycle fields
            $table->date('expires_at')->nullable()->after('status');
            $table->boolean('auto_renew')->default(true)->after('expires_at');
            $table->date('next_billing_date')->nullable()->after('auto_renew');
            $table->json('metadata')->nullable()->after('billing_cycle');

            // Update status to include service statuses
            $table->dropColumn('status');
        });

        // Recreate status column with combined values
        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', [
                'pending',      // Order pending payment
                'processing',   // Order being processed
                'active',       // Service is active
                'suspended',    // Service suspended
                'expired',      // Service expired
                'cancelled',    // Order/Service cancelled
                'terminated',    // Service terminated
            ])->default('pending')->after('domain_name');
        });

        // Add billing_cycle option for onetime
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('billing_cycle');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('billing_cycle', ['onetime', 'monthly', 'quarterly', 'semi_annually', 'annually'])->default('annually')->after('next_billing_date');
        });

        // Copy data from services to orders
        DB::table('services')->orderBy('id')->chunk(100, function ($services) {
            foreach ($services as $service) {
                DB::table('orders')->insert([
                    'customer_id' => $service->customer_id,
                    'order_type' => 'service', // Mark as converted from service
                    'service_type' => $service->service_type,
                    'plan_id' => $service->plan_id,
                    'domain_name' => $service->domain_name,
                    'status' => $service->status,
                    'expires_at' => $service->expires_at,
                    'auto_renew' => $service->auto_renew,
                    'next_billing_date' => $service->auto_renew ? $service->expires_at : null,
                    'billing_cycle' => 'annually', // Default assumption
                    'metadata' => $service->metadata,
                    'total_amount' => 0.00, // Will need to be updated based on plan prices
                    'created_at' => $service->created_at,
                    'updated_at' => $service->updated_at,
                ]);
            }
        });

        // Add indexes for new fields
        Schema::table('orders', function (Blueprint $table) {
            $table->index(['customer_id', 'service_type']);
            $table->index(['status', 'expires_at']);
            $table->index('domain_name');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Remove service-related fields from orders
        Schema::table('orders', function (Blueprint $table) {
            $table->dropIndex(['customer_id', 'service_type']);
            $table->dropIndex(['status', 'expires_at']);
            $table->dropIndex('domain_name');

            $table->dropColumn([
                'service_type',
                'plan_id',
                'domain_name',
                'expires_at',
                'auto_renew',
                'next_billing_date',
                'metadata',
            ]);
        });

        // Restore original status enum
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('status');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('status', ['pending', 'processing', 'completed', 'cancelled'])->default('pending');
        });

        // Restore original billing_cycle enum
        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn('billing_cycle');
        });

        Schema::table('orders', function (Blueprint $table) {
            $table->enum('billing_cycle', ['monthly', 'quarterly', 'semi_annually', 'annually'])->default('annually');
        });
    }
};
