<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_packages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->integer('credits')->default(0);
            $table->decimal('price', 12, 2)->default(0);
            $table->decimal('discount_amount', 12, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_packages');
    }
};
