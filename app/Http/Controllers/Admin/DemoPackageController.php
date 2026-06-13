<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DemoPackage;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DemoPackageController extends Controller
{
    public function index(): Response
    {
        $packages = DemoPackage::when(request('search'), function ($query, $search) {
            $query->where('name', 'like', "%{$search}%");
        })
            ->ordered()
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/DemoPackages/Index', [
            'packages' => $packages,
            'filters' => request()->only(['search']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:demo_packages,slug',
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = \Str::slug($validated['name']);
        }

        DemoPackage::create($validated);

        return redirect()->route('admin.demo-packages.index')->with('success', 'Paket demo berhasil ditambahkan!');
    }

    public function update(Request $request, DemoPackage $demoPackage)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'slug' => 'nullable|string|max:255|unique:demo_packages,slug,' . $demoPackage->id,
            'description' => 'nullable|string',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer',
        ]);

        if (empty($validated['slug'])) {
            $validated['slug'] = \Str::slug($validated['name']);
        }

        $demoPackage->update($validated);

        return redirect()->route('admin.demo-packages.index')->with('success', 'Paket demo berhasil diperbarui!');
    }

    public function destroy(DemoPackage $demoPackage)
    {
        $demoPackage->delete();

        return redirect()->route('admin.demo-packages.index')->with('success', 'Paket demo berhasil dihapus!');
    }

    public function toggleStatus(DemoPackage $demoPackage)
    {
        $demoPackage->update(['is_active' => ! $demoPackage->is_active]);

        return back()->with('success', 'Status paket berhasil diubah!');
    }
}