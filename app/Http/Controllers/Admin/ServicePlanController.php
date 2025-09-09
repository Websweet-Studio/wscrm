<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ServicePlan;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ServicePlanController extends Controller
{
    public function index(): Response
    {
        $servicePlans = ServicePlan::when(request('search'), function ($query, $search) {
            $query->where('name', 'like', "%{$search}%")
                ->orWhere('description', 'like', "%{$search}%")
                ->orWhere('category', 'like', "%{$search}%");
        })
            ->when(request('category'), function ($query, $category) {
                $query->where('category', $category);
            })
            ->orderBy('category')
            ->orderBy('price')
            ->paginate(20)
            ->withQueryString();

        $categories = [
            'web_package' => 'Paket Website',
            'addon' => 'Layanan Add-on',
            'license' => 'Lisensi Premium',
            'custom_system' => 'Custom System',
        ];

        return Inertia::render('Admin/ServicePlans/Index', [
            'servicePlans' => $servicePlans,
            'categories' => $categories,
            'filters' => request()->only(['search', 'category']),
        ]);
    }

    public function show(ServicePlan $servicePlan): Response
    {
        return Inertia::render('Admin/ServicePlans/Show', [
            'servicePlan' => $servicePlan,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:web_package,addon,license,custom_system',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        ServicePlan::create([
            'name' => $request->name,
            'category' => $request->category,
            'description' => $request->description,
            'price' => $request->price,
            'features' => $request->features ?? [],
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->back()->with('success', 'Paket layanan berhasil dibuat!');
    }

    public function update(Request $request, ServicePlan $servicePlan)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'category' => 'required|in:web_package,addon,license,custom_system',
            'description' => 'nullable|string',
            'price' => 'required|numeric|min:0',
            'features' => 'nullable|array',
            'is_active' => 'boolean',
        ]);

        $servicePlan->update([
            'name' => $request->name,
            'category' => $request->category,
            'description' => $request->description,
            'price' => $request->price,
            'features' => $request->features ?? [],
            'is_active' => $request->is_active ?? true,
        ]);

        return redirect()->back()->with('success', 'Paket layanan berhasil diperbarui!');
    }

    public function destroy(ServicePlan $servicePlan)
    {
        $servicePlan->delete();

        return redirect()->back()->with('success', 'Paket layanan berhasil dihapus!');
    }
}
