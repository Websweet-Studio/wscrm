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
        Schema::create('hosting_plans', function (Blueprint $table) {
            $table->id();
            $table->string('plan_name', 191);
            $table->decimal('storage_gb', 8, 2);
            $table->decimal('cpu_cores', 4, 2);
            $table->decimal('ram_gb', 8, 2);
            $table->string('bandwidth', 191)->default('Unlimited');
            $table->decimal('modal_cost', 12, 2);
            $table->decimal('maintenance_cost', 12, 2);
            $table->integer('discount_percent')->default(0);
            $table->decimal('selling_price', 12, 2);
            $table->json('features')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['plan_name', 'storage_gb']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('hosting_plans');
    }
};
