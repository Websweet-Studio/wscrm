<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_conversations', function (Blueprint $table) {
            $table->id();
            // Tanpa FK ke users: users.id di DB lama (production) bertipe non-bigint → errno 150.
            // Nullable: percakapan admin (user_id) atau customer (customer_id).
            $table->foreignId('user_id')->nullable();
            $table->string('title')->default('Percakapan baru');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_conversations');
    }
};
