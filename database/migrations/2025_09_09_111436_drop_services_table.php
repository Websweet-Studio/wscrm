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
        // Skip this migration - it was handled in a previous merge operation
        // The services table and service_id column should already be handled
        // This migration is kept for compatibility but does nothing

        // Optional: Only drop services table if it exists and has no data
        if (Schema::hasTable('services')) {
            try {
                // Check if table has any data before dropping
                $count = \DB::table('services')->count();
                if ($count == 0) {
                    Schema::dropIfExists('services');
                }
            } catch (\Exception $e) {
                // Ignore errors when checking/dropping services table
            }
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Recreate services table structure
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->onDelete('cascade');
            $table->enum('service_type', ['hosting', 'domain']);
            $table->unsignedBigInteger('plan_id')->nullable();
            $table->string('domain_name', 191);
            $table->enum('status', ['active', 'suspended', 'terminated', 'pending'])->default('pending');
            $table->date('expires_at');
            $table->boolean('auto_renew')->default(true);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['customer_id', 'service_type']);
            $table->index(['status', 'expires_at']);
            $table->index('domain_name');
        });
    }
};
