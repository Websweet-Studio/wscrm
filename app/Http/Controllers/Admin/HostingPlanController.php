<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\HostingPlan;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class HostingPlanController extends Controller
{
    public function index(): Response
    {
        $hostingPlans = HostingPlan::query()
            ->when(request('search'), function ($query, $search) {
                $query->where('plan_name', 'like', "%{$search}%");
            })
            ->orderBy('plan_name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/HostingPlans/Index', [
            'hostingPlans' => $hostingPlans,
            'filters' => request()->only(['search']),
        ]);
    }

    public function create(): Response
    {
        return Inertia::render('Admin/HostingPlans/Create');
    }

    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'plan_name' => 'required|string|unique:hosting_plans,plan_name',
            'storage_gb' => 'required|numeric|min:0',
            'cpu_cores' => 'required|numeric|min:1',
            'ram_gb' => 'required|numeric|min:0',
            'bandwidth' => 'required|string',
            'modal_cost' => 'required|numeric|min:0',
            'maintenance_cost' => 'required|numeric|min:0',
            'discount_percent' => 'required|numeric|min:0|max:100',
            'selling_price' => 'required|numeric|min:0',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        HostingPlan::create($validated);

        return redirect()->route('admin.hosting-plans.index')
            ->with('success', 'Paket hosting berhasil dibuat.');
    }

    public function show(HostingPlan $hostingPlan): Response
    {
        return Inertia::render('Admin/HostingPlans/Show', [
            'hostingPlan' => $hostingPlan,
        ]);
    }

    public function edit(HostingPlan $hostingPlan): Response
    {
        return Inertia::render('Admin/HostingPlans/Edit', [
            'hostingPlan' => $hostingPlan,
        ]);
    }

    public function update(Request $request, HostingPlan $hostingPlan): RedirectResponse
    {
        $validated = $request->validate([
            'plan_name' => 'required|string|unique:hosting_plans,plan_name,'.$hostingPlan->id,
            'storage_gb' => 'required|numeric|min:0',
            'cpu_cores' => 'required|numeric|min:1',
            'ram_gb' => 'required|numeric|min:0',
            'bandwidth' => 'required|string',
            'modal_cost' => 'required|numeric|min:0',
            'maintenance_cost' => 'required|numeric|min:0',
            'discount_percent' => 'required|numeric|min:0|max:100',
            'selling_price' => 'required|numeric|min:0',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $hostingPlan->update($validated);

        return redirect()->route('admin.hosting-plans.index')
            ->with('success', 'Paket hosting berhasil diperbarui.');
    }

    public function destroy(HostingPlan $hostingPlan): RedirectResponse
    {
        $hostingPlan->delete();

        return redirect()->route('admin.hosting-plans.index')
            ->with('success', 'Paket hosting berhasil dihapus.');
    }

    public function bulkDestroy(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'ids' => ['required', 'array', 'min:1'],
            'ids.*' => ['integer', 'distinct'],
        ]);

        $ids = array_values(array_unique(array_map('intval', $validated['ids'])));

        $deleted = DB::transaction(function () use ($ids) {
            return HostingPlan::whereIn('id', $ids)->delete();
        });

        return redirect()->route('admin.hosting-plans.index')
            ->with('success', "{$deleted} paket hosting berhasil dihapus.");
    }
}
