<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\BlogPost;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class AgentBlogController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'content' => 'required|string',
            'blog_category_id' => 'required|exists:blog_categories,id',
            'type' => 'required|in:article,announcement,news',
            'status' => 'nullable|in:draft,published',
            'excerpt' => 'nullable|string|max:500',
            'meta_data' => 'nullable|array',
        ]);

        $validated['slug'] = Str::slug($validated['title']);
        $validated['status'] ??= 'draft';

        // Gunakan admin user pertama sebagai author default
        $admin = User::where('role', 'admin')->first() ?? User::first();
        $validated['user_id'] = $admin->id;

        $post = BlogPost::create($validated);

        return response()->json([
            'data' => $post->load(['category', 'author']),
        ], 201);
    }
}
