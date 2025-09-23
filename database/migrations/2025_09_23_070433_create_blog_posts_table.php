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
        Schema::create('blog_posts', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->unique();
            $table->text('excerpt')->nullable();
            $table->longText('content');
            $table->string('featured_image')->nullable();
            $table->json('meta_data')->nullable(); // For SEO meta tags, etc.

            $table->foreignId('blog_category_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Author

            $table->enum('type', ['article', 'announcement', 'news'])->default('article');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');

            $table->boolean('is_featured')->default(false);
            $table->boolean('is_pinned')->default(false);
            $table->boolean('allow_comments')->default(true);

            $table->integer('views_count')->default(0);
            $table->integer('likes_count')->default(0);

            $table->timestamp('published_at')->nullable();
            $table->timestamps();

            // Indexes for performance
            $table->index(['status', 'published_at']);
            $table->index(['type', 'status']);
            $table->index(['is_featured', 'published_at']);
            $table->index(['blog_category_id', 'status']);
            $table->index('slug');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('blog_posts');
    }
};
