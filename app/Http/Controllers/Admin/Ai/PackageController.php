<?php

namespace App\Http\Controllers\Admin\Ai;

use App\Http\Controllers\Controller;
use App\Models\AiPackage;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class PackageController extends Controller
{
    public function index(): Response
    {
        $packages = AiPackage::query()
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%");
            })
            ->orderBy('sort_order')
            ->orderBy('id')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Ai/Packages/Index', [
            'packages' => $packages,
            'filters' => request()->only(['search']),
        ]);
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'credits' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        AiPackage::create([
            'name' => $validated['name'],
            'credits' => $validated['credits'],
            'price' => $validated['price'],
            'discount_amount' => $validated['discount_amount'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->back()->with('success', 'Paket kredit AI berhasil ditambahkan.');
    }

    public function update(Request $request, AiPackage $package)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'credits' => 'required|integer|min:1',
            'price' => 'required|numeric|min:0',
            'discount_amount' => 'nullable|numeric|min:0',
            'is_active' => 'boolean',
            'sort_order' => 'nullable|integer|min:0',
        ]);

        $package->update([
            'name' => $validated['name'],
            'credits' => $validated['credits'],
            'price' => $validated['price'],
            'discount_amount' => $validated['discount_amount'] ?? null,
            'is_active' => $validated['is_active'] ?? true,
            'sort_order' => $validated['sort_order'] ?? 0,
        ]);

        return redirect()->back()->with('success', 'Paket kredit AI berhasil diperbarui.');
    }

    public function destroy(AiPackage $package)
    {
        $package->delete();

        return redirect()->back()->with('success', 'Paket kredit AI berhasil dihapus.');
    }
}
