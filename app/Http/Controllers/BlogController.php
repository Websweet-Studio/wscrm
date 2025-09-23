<?php

namespace App\Http\Controllers;

use App\Models\BlogPost;
use App\Models\BlogCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    public function index(Request $request): Response
    {
        $query = BlogPost::with(['category', 'author'])
            ->published()
            ->when($request->search, function ($q, $search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('excerpt', 'like', "%{$search}%")
                  ->orWhere('content', 'like', "%{$search}%");
            })
            ->when($request->category, function ($q, $category) {
                $q->where('blog_category_id', $category);
            })
            ->when($request->type, function ($q, $type) {
                $q->where('type', $type);
            });

        // Get featured posts for carousel (maximum 5)
        $featuredPosts = BlogPost::with(['category', 'author'])
            ->published()
            ->featured()
            ->latest('published_at')
            ->limit(5)
            ->get();

        // Get pinned posts
        $pinnedPosts = BlogPost::with(['category', 'author'])
            ->published()
            ->pinned()
            ->latest('published_at')
            ->limit(3)
            ->get();

        // Get regular posts (excluding featured and pinned)
        $posts = $query
            ->where('is_featured', false)
            ->where('is_pinned', false)
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        // Get recent posts for sidebar
        $recentPosts = BlogPost::with(['category'])
            ->published()
            ->latest('published_at')
            ->limit(5)
            ->get();

        // Get popular posts for sidebar
        $popularPosts = BlogPost::with(['category'])
            ->published()
            ->popular(5)
            ->get();

        $categories = BlogCategory::active()
            ->ordered()
            ->withCount(['publishedPosts'])
            ->get();

        return Inertia::render('Blog/Index', [
            'featuredPosts' => $featuredPosts,
            'pinnedPosts' => $pinnedPosts,
            'posts' => $posts,
            'recentPosts' => $recentPosts,
            'popularPosts' => $popularPosts,
            'categories' => $categories,
            'filters' => $request->only(['search', 'category', 'type']),
        ]);
    }

    public function show(BlogPost $blogPost): Response
    {
        // Increment view count
        $blogPost->incrementViews();

        // Load relationships
        $blogPost->load(['category', 'author']);

        // Get related posts (same category, excluding current post)
        $relatedPosts = BlogPost::with(['category', 'author'])
            ->published()
            ->where('blog_category_id', $blogPost->blog_category_id)
            ->where('id', '!=', $blogPost->id)
            ->latest('published_at')
            ->limit(4)
            ->get();

        // Get recent posts for sidebar
        $recentPosts = BlogPost::with(['category'])
            ->published()
            ->latest('published_at')
            ->limit(5)
            ->get();

        return Inertia::render('Blog/Show', [
            'post' => $blogPost,
            'relatedPosts' => $relatedPosts,
            'recentPosts' => $recentPosts,
        ]);
    }

    public function category(BlogCategory $category): Response
    {
        $posts = BlogPost::with(['category', 'author'])
            ->published()
            ->where('blog_category_id', $category->id)
            ->latest('published_at')
            ->paginate(12)
            ->withQueryString();

        // Get recent posts for sidebar
        $recentPosts = BlogPost::with(['category'])
            ->published()
            ->latest('published_at')
            ->limit(5)
            ->get();

        $categories = BlogCategory::active()
            ->ordered()
            ->withCount(['publishedPosts'])
            ->get();

        return Inertia::render('Blog/Category', [
            'category' => $category,
            'posts' => $posts,
            'recentPosts' => $recentPosts,
            'categories' => $categories,
        ]);
    }
}
