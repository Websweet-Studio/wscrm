<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\DomainPrice;
use App\Models\HostingPlan;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class HostingController extends Controller
{
    public function index(): Response
    {
        $hostingPlans = HostingPlan::active()
            ->when(request('search'), function ($query, $search) {
                $query->where('plan_name', 'like', "%{$search}%");
            })
            ->orderBy('selling_price')
            ->get();

        // Get domain prices for new domain orders (limit to most popular)
        $domainPrices = DomainPrice::active()
            ->orderBy('selling_price')
            ->limit(15)
            ->get(['id', 'extension', 'selling_price']);

        // Get customer's existing domains from their orders
        $existingDomains = collect([]);
        if (Auth::guard('customer')->check()) {
            $customer = Auth::guard('customer')->user();
            $existingDomains = \DB::table('order_items')
                ->join('orders', 'orders.id', '=', 'order_items.order_id')
                ->where('orders.customer_id', $customer->id)
                ->where('order_items.item_type', 'domain')
                ->whereNotNull('order_items.domain_name')
                ->select(['order_items.id', 'order_items.domain_name', 'orders.status'])
                ->distinct()
                ->get()
                ->map(function ($domain) {
                    return [
                        'id' => $domain->id,
                        'domain_name' => $domain->domain_name,
                        'status' => $domain->status === 'completed' ? 'active' : $domain->status,
                    ];
                });
        }

        return Inertia::render('Customer/Hosting/Index', [
            'hostingPlans' => $hostingPlans,
            'domainPrices' => $domainPrices,
            'existingDomains' => $existingDomains,
            'filters' => request()->only(['search']),
        ]);
    }

    public function show(HostingPlan $hostingPlan): Response
    {
        if (! $hostingPlan->is_active) {
            abort(404);
        }

        return Inertia::render('Customer/Hosting/Show', [
            'hostingPlan' => $hostingPlan,
        ]);
    }
}
