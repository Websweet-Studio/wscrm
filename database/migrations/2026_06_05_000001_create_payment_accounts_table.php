<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_accounts', function (Blueprint $table) {
            $table->id();
            $table->enum('type', ['bank', 'ewallet', 'qris']);
            $table->string('name', 191);
            $table->string('account_number', 191)->nullable();
            $table->string('account_name', 191)->nullable();
            $table->string('qris_provider', 191)->nullable();
            $table->text('qris_image_path')->nullable();
            $table->boolean('is_active')->default(true);
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['type', 'is_active']);
            $table->index(['is_active', 'sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_accounts');
    }
};

