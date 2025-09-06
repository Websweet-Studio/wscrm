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

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
