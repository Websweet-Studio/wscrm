<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DomainPrice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class DomainPriceController extends Controller
{
    public function index(Request $request): Response
    {
        // Sorting
        $sort = $request->get('sort', 'extension');
        $direction = $request->get('direction', 'asc');

        // Validate sort field
        $allowedSorts = ['extension', 'base_cost', 'renewal_cost', 'selling_price', 'renewal_price_with_tax', 'is_active'];
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'extension';
        }

        // Validate direction
        if (!in_array($direction, ['asc', 'desc'])) {
            $direction = 'asc';
        }

        $domainPrices = DomainPrice::query()
            ->when(request('search'), function ($query, $search) {
                $query->where('extension', 'like', "%{$search}%");
            })
            ->orderBy($sort, $direction)
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/DomainPrices/Index', [
            'domainPrices' => $domainPrices,
            'filters' => request()->only(['search']),
            'sort' => $sort,
            'direction' => $direction,
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/DomainPrices/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'extension' => 'required|string|unique:domain_prices,extension',
            'base_cost' => 'required|numeric|min:0',
            'renewal_cost' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'renewal_price_with_tax' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        DomainPrice::create($validated);

        return redirect()->route('admin.domain-prices.index')
            ->with('success', 'Harga domain berhasil dibuat.');
    }

    public function show(DomainPrice $domainPrice): Response
    {
        return Inertia::render('Admin/DomainPrices/Show', [
            'domainPrice' => $domainPrice,
        ]);
    }

    public function edit(DomainPrice $domainPrice): Response
    {
        return Inertia::render('Admin/DomainPrices/Edit', [
            'domainPrice' => $domainPrice,
        ]);
    }

    public function update(Request $request, DomainPrice $domainPrice): RedirectResponse
    {
        $validated = $request->validate([
            'extension' => 'required|string|unique:domain_prices,extension,'.$domainPrice->id,
            'base_cost' => 'required|numeric|min:0',
            'renewal_cost' => 'required|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'renewal_price_with_tax' => 'required|numeric|min:0',
            'is_active' => 'boolean',
        ]);

        $domainPrice->update($validated);

        return redirect()->route('admin.domain-prices.index')
            ->with('success', 'Harga domain berhasil diperbarui.');
    }

    public function destroy(DomainPrice $domainPrice): RedirectResponse
    {
        $domainPrice->delete();

        return redirect()->route('admin.domain-prices.index')
            ->with('success', 'Harga domain berhasil dihapus.');
    }
}
