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
        Schema::table('hosting_plans', function (Blueprint $table) {
            $table->decimal('base_price_per_gb', 12, 2)->nullable()->after('selling_price');
            $table->decimal('plan_multiplier', 4, 2)->default(1.00)->after('base_price_per_gb');
            $table->decimal('cost_per_gb', 12, 2)->nullable()->after('plan_multiplier');
            $table->boolean('use_bulk_pricing')->default(false)->after('cost_per_gb');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('hosting_plans', function (Blueprint $table) {
            $table->dropColumn([
                'base_price_per_gb',
                'plan_multiplier',
                'cost_per_gb',
                'use_bulk_pricing',
            ]);
        });
    }
};
