<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DemoWebsite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class DemoWebsiteController extends Controller
{
    public function index(): Response
    {
        $demos = DemoWebsite::when(request('search'), function ($query, $search) {
            $query->where('title', 'like', "%{$search}%")
                ->orWhere('category', 'like', "%{$search}%");
        })
            ->when(request('category'), function ($query, $category) {
                $query->where('category', $category);
            })
            ->when(request('status'), function ($query, $status) {
                $query->where('is_active', $status === 'active');
            })
            ->ordered()
            ->paginate(20)
            ->withQueryString();

        $categories = DemoWebsite::select('category')->distinct()->pluck('category');

        return Inertia::render('Admin/DemoWebsites/Index', [
            'demos' => $demos,
            'categories' => $categories,
            'filters' => request()->only(['search', 'category', 'status']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'category' => 'required|string|max:255',
            'package' => 'nullable|string|max:255',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = $request->file('featured_image')->store('demo-websites', 'public');
        }

        DemoWebsite::create($validated);

        return redirect()->route('admin.demo-websites.index')->with('success', 'Demo website berhasil ditambahkan!');
    }

    public function update(Request $request, DemoWebsite $demoWebsite)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'category' => 'required|string|max:255',
            'package' => 'nullable|string|max:255',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if ($request->hasFile('featured_image')) {
            if ($demoWebsite->featured_image && ! filter_var($demoWebsite->featured_image, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($demoWebsite->featured_image);
            }
            $validated['featured_image'] = $request->file('featured_image')->store('demo-websites', 'public');
        }

        $demoWebsite->update($validated);

        return redirect()->route('admin.demo-websites.index')->with('success', 'Demo website berhasil diperbarui!');
    }

    public function destroy(DemoWebsite $demoWebsite)
    {
        if ($demoWebsite->featured_image && ! filter_var($demoWebsite->featured_image, FILTER_VALIDATE_URL)) {
            Storage::disk('public')->delete($demoWebsite->featured_image);
        }

        $demoWebsite->delete();

        return redirect()->route('admin.demo-websites.index')->with('success', 'Demo website berhasil dihapus!');
    }

    public function toggleStatus(DemoWebsite $demoWebsite)
    {
        $demoWebsite->update(['is_active' => ! $demoWebsite->is_active]);

        return back()->with('success', 'Status demo website berhasil diubah!');
    }
}