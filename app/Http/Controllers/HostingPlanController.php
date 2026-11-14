<?php

namespace App\Http\Controllers;

use App\Models\HostingPlan;
use Inertia\Inertia;
use Inertia\Response;

class HostingPlanController extends Controller
{
    public function index(): Response
    {
        $hostingPlans = HostingPlan::active()
            ->orderBy('plan_name')
            ->orderBy('storage_gb')
            ->get()
            ->groupBy('plan_name');

        return Inertia::render('Hosting/Index', [
            'hostingPlans' => $hostingPlans,
        ]);
    }

    public function show(HostingPlan $hostingPlan): Response
    {
        return Inertia::render('Hosting/Show', [
            'hostingPlan' => $hostingPlan,
        ]);
    }

    public function publicIndex(): Response
    {
        $hostingPlans = HostingPlan::active()
            ->when(request('search'), function ($query, $search) {
                $query->where('plan_name', 'like', "%{$search}%");
            })
            ->orderBy('selling_price')
            ->get();

        return Inertia::render('Public/Hosting/Index', [
            'hostingPlans' => $hostingPlans,
            'filters' => array_merge(request()->only(['search']), ['tab' => request()->route('tab', 'hosting')]),
        ]);
    }
}
