<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DemoCategory;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DemoCategoryController extends Controller
{
    public function index(): Response
    {
        $categories = DemoCategory::when(request('search'), function ($query, $search) {
            $query->where('name', 'like', "%{$search}%");
        })
            ->ordered()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/DemoCategories/Index', [
            'categories' => $categories,
            'filters' => request()->only(['search']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:demo_categories,slug',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = \Str::slug($validated['name']);
        }

        DemoCategory::create($validated);

        return redirect()->route('admin.demo-categories.index')->with('success', 'Kategori demo berhasil ditambahkan!');
    }

    public function update(Request $request, DemoCategory $demoCategory)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:demo_categories,slug,' . $demoCategory->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = \Str::slug($validated['name']);
        }

        $demoCategory->update($validated);

        return redirect()->route('admin.demo-categories.index')->with('success', 'Kategori demo berhasil diperbarui!');
    }

    public function destroy(DemoCategory $demoCategory)
    {
        $demoCategory->delete();

        return redirect()->route('admin.demo-categories.index')->with('success', 'Kategori demo berhasil dihapus!');
    }

    public function toggleStatus(DemoCategory $demoCategory)
    {
        $demoCategory->update(['is_active' => ! $demoCategory->is_active]);

        return back()->with('success', 'Status kategori berhasil diubah!');
    }
}