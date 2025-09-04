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
        Schema::create('banks', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name');
            $table->string('bank_code', 10)->unique();
            $table->string('account_number');
            $table->string('account_name');
            $table->string('branch')->nullable();
            $table->string('swift_code')->nullable();
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('admin_fee', 10, 2)->default(0);
            $table->enum('bank_type', ['local', 'international'])->default('local');
            $table->timestamps();

            $table->index(['is_active', 'bank_type']);
            $table->index('bank_code');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('banks');
    }
};
