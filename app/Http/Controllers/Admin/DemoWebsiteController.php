<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DemoCategory;
use App\Models\DemoPackage;
use App\Models\DemoWebsite;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Inertia\Inertia;
use Inertia\Response;

class DemoWebsiteController extends Controller
{
    public function index(): Response
    {
        $demos = DemoWebsite::with(['demoCategory', 'demoPackages'])
            ->when(request('search'), function ($query, $search) {
                $query->where('title', 'like', "%{$search}%")
                    ->orWhere('url', 'like', "%{$search}%");
            })
            ->when(request('category'), function ($query, $category) {
                $query->where('demo_category_id', $category);
            })
            ->when(request('package'), function ($query, $package) {
                $query->whereHas('demoPackages', function ($q) use ($package) {
                    $q->where('demo_packages.id', $package);
                });
            })
            ->when(request('status'), function ($query, $status) {
                $query->where('is_active', $status === 'active');
            })
            ->ordered()
            ->paginate(20)
            ->withQueryString();

        $categories = DemoCategory::active()->ordered()->get();
        $packages = DemoPackage::active()->ordered()->get();

        return Inertia::render('Admin/DemoWebsites/Index', [
            'demos' => $demos,
            'categories' => $categories,
            'packages' => $packages,
            'filters' => request()->only(['search', 'category', 'package', 'status']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'demo_category_id' => 'required|exists:demo_categories,id',
            'demo_packages' => 'nullable|array',
            'demo_packages.*' => 'exists:demo_packages,id',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $packages = $validated['demo_packages'] ?? [];
        unset($validated['demo_packages']);

        if ($request->hasFile('featured_image')) {
            $validated['featured_image'] = $request->file('featured_image')->store('demo-websites', 'public');
        }

        // Sync the string field
        $category = DemoCategory::find($validated['demo_category_id']);
        $validated['category'] = $category ? $category->slug : '';

        $demoWebsite = DemoWebsite::create($validated);
        $demoWebsite->demoPackages()->sync($packages);

        return redirect()->route('admin.demo-websites.index')->with('success', 'Demo website berhasil ditambahkan!');
    }

    public function update(Request $request, DemoWebsite $demoWebsite)
    {
        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'url' => 'required|url|max:255',
            'demo_category_id' => 'required|exists:demo_categories,id',
            'demo_packages' => 'nullable|array',
            'demo_packages.*' => 'exists:demo_packages,id',
            'featured_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,webp|max:2048',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        $packages = $validated['demo_packages'] ?? [];
        unset($validated['demo_packages']);

        if ($request->hasFile('featured_image')) {
            if ($demoWebsite->featured_image && !filter_var($demoWebsite->featured_image, FILTER_VALIDATE_URL)) {
                Storage::disk('public')->delete($demoWebsite->featured_image);
            }
            $validated['featured_image'] = $request->file('featured_image')->store('demo-websites', 'public');
        }

        // Sync the string field
        $category = DemoCategory::find($validated['demo_category_id']);
        $validated['category'] = $category ? $category->slug : '';

        $demoWebsite->update($validated);
        $demoWebsite->demoPackages()->sync($packages);

        return redirect()->route('admin.demo-websites.index')->with('success', 'Demo website berhasil diperbarui!');
    }

    public function destroy(DemoWebsite $demoWebsite)
    {
        if ($demoWebsite->featured_image && !filter_var($demoWebsite->featured_image, FILTER_VALIDATE_URL)) {
            Storage::disk('public')->delete($demoWebsite->featured_image);
        }

        $demoWebsite->demoPackages()->detach();
        $demoWebsite->delete();

        return redirect()->route('admin.demo-websites.index')->with('success', 'Demo website berhasil dihapus!');
    }

    public function toggleStatus(DemoWebsite $demoWebsite)
    {
        $demoWebsite->update(['is_active' => !$demoWebsite->is_active]);

        return back()->with('success', 'Status demo website berhasil diubah!');
    }
}