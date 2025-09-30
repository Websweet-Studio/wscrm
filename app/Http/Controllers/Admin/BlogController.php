<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BlogCategory;
use App\Models\BlogPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class BlogController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $posts = BlogPost::with(['category', 'author'])
            ->when(request('search'), function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('content', 'like', "%{$search}%");
            })
            ->when(request('category'), function ($query, $category) {
                $query->where('blog_category_id', $category);
            })
            ->when(request('status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->when(request('type'), function ($query, $type) {
                $query->where('type', $type);
            })
            ->when(request('sort'), function ($query, $sort) {
                $direction = request('direction', 'desc');
                $allowedSorts = ['created_at', 'published_at', 'title', 'views_count', 'likes_count'];

                if (in_array($sort, $allowedSorts)) {
                    $query->orderBy($sort, $direction);
                }
            }, function ($query) {
                $query->orderBy('created_at', 'desc');
            })
            ->paginate(20)
            ->withQueryString();

        $categories = BlogCategory::active()->ordered()->get();

        return Inertia::render('Admin/Blog/Index', [
            'posts' => $posts,
            'categories' => $categories,
            'filters' => request()->only(['search', 'category', 'status', 'type', 'sort', 'direction']),
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): Response
    {
        $categories = BlogCategory::active()->ordered()->get();

        return Inertia::render('Admin/Blog/Create', [
            'categories' => $categories,
        ]);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_posts,slug',
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'blog_category_id' => 'required|exists:blog_categories,id',
            'type' => 'required|in:article,announcement,news',
            'status' => 'required|in:draft,published,archived',
            'is_featured' => 'boolean',
            'is_pinned' => 'boolean',
            'allow_comments' => 'boolean',
            'published_at' => 'nullable|date',
            'meta_data' => 'nullable|array',
        ]);

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = $request->file('featured_image')->store('blog/images', 'public');
        }

        // Set author
        $validated['user_id'] = Auth::id();

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = \Str::slug($validated['title']);
        }

        BlogPost::create($validated);

        return redirect()->route('admin.blog.index')->with('success', 'Artikel berhasil dibuat!');
    }

    /**
     * Display the specified resource.
     */
    public function show(BlogPost $blog): Response
    {
        $blog->load(['category', 'author']);

        return Inertia::render('Admin/Blog/Show', [
            'post' => $blog,
        ]);
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(BlogPost $blog): Response
    {
        $categories = BlogCategory::active()->ordered()->get();

        return Inertia::render('Admin/Blog/Edit', [
            'post' => $blog,
            'categories' => $categories,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, BlogPost $blog)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:blog_posts,slug,'.$blog->id,
            'excerpt' => 'nullable|string|max:500',
            'content' => 'required|string',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'blog_category_id' => 'required|exists:blog_categories,id',
            'type' => 'required|in:article,announcement,news',
            'status' => 'required|in:draft,published,archived',
            'is_featured' => 'boolean',
            'is_pinned' => 'boolean',
            'allow_comments' => 'boolean',
            'published_at' => 'nullable|date',
            'meta_data' => 'nullable|array',
        ]);

        // Handle featured image upload
        if ($request->hasFile('featured_image')) {
            // Delete old image
            if ($blog->featured_image) {
                Storage::disk('public')->delete($blog->featured_image);
            }
            $validated['featured_image'] = $request->file('featured_image')->store('blog/images', 'public');
        }

        // Auto-generate slug if not provided
        if (empty($validated['slug'])) {
            $validated['slug'] = \Str::slug($validated['title']);
        }

        $blog->update($validated);

        return redirect()->route('admin.blog.index')->with('success', 'Artikel berhasil diperbarui!');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(BlogPost $blog)
    {
        // Delete featured image
        if ($blog->featured_image) {
            Storage::disk('public')->delete($blog->featured_image);
        }

        $blog->delete();

        return redirect()->route('admin.blog.index')->with('success', 'Artikel berhasil dihapus!');
    }

    /**
     * Toggle featured status
     */
    public function toggleFeatured(BlogPost $blog)
    {
        $blog->update(['is_featured' => ! $blog->is_featured]);

        return back()->with('success', 'Status featured berhasil diubah!');
    }

    /**
     * Toggle pinned status
     */
    public function togglePinned(BlogPost $blog)
    {
        $blog->update(['is_pinned' => ! $blog->is_pinned]);

        return back()->with('success', 'Status pinned berhasil diubah!');
    }
}
