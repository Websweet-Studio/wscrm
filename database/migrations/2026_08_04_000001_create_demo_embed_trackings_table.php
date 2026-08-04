<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('demo_embed_trackings', function (Blueprint $table) {
            $table->id();
            $table->string('referer_url')->nullable();
            $table->string('referer_host')->nullable();
            $table->enum('embed_type', ['listing', 'single', 'oembed', 'api'])->default('listing');
            $table->foreignId('demo_website_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedBigInteger('hits')->default(1);
            $table->boolean('is_blocked')->default(false);
            $table->timestamp('blocked_at')->nullable();
            $table->timestamp('first_seen_at')->nullable();
            $table->timestamp('last_seen_at')->nullable();
            $table->timestamps();

            $table->index('referer_host');
            $table->index('embed_type');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('demo_embed_trackings');
    }
};
