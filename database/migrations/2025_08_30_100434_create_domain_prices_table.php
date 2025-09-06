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
        Schema::create('domain_prices', function (Blueprint $table) {
            $table->id();
            $table->string('extension', 191);
            $table->decimal('base_cost', 10, 2);
            $table->decimal('renewal_cost', 10, 2);
            $table->decimal('selling_price', 10, 2);
            $table->decimal('renewal_price_with_tax', 10, 2);
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique('extension');
            $table->index('is_active');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('domain_prices');
    }
};
