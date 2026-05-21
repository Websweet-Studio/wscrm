<?php

use App\Models\BlogCategory;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Support\Carbon;

test('public pages can be opened', function (string $url, string $component) {
    $response = $this->get($url);
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component($component));
})->with(function () {
    return [
        ['/', 'CustomerWelcome'],
        ['/hosting', 'Public/Hosting/Index'],
        ['/domains', 'Public/Domains/Index'],
        ['/domains/search?domain=test.com', 'Public/Domains/Search'],
        ['/blog', 'Blog/Index'],
    ];
});

test('blog detail page can be opened', function () {
    $author = User::factory()->create([
        'role' => 'super_admin',
        'email_verified_at' => Carbon::now(),
    ]);

    $category = BlogCategory::create([
        'name' => 'News',
        'slug' => 'news',
        'description' => null,
        'color' => '#3B82F6',
        'icon' => null,
        'is_active' => true,
        'sort_order' => 0,
    ]);

    $post = BlogPost::create([
        'title' => 'Hello World',
        'excerpt' => 'Excerpt',
        'content' => '<p>Content</p>',
        'blog_category_id' => $category->id,
        'user_id' => $author->id,
        'type' => 'article',
        'status' => 'published',
        'is_featured' => false,
        'is_pinned' => false,
        'allow_comments' => true,
        'views_count' => 0,
        'likes_count' => 0,
        'published_at' => Carbon::now()->subMinute(),
    ]);

    $response = $this->get("/blog/{$post->slug}");
    $response->assertStatus(200);
    $response->assertInertia(fn ($page) => $page->component('Blog/Show'));
});
