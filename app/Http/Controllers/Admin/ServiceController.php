<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\DomainPrice;
use App\Models\HostingPlan;
use App\Models\Order;
use App\Models\ServicePlan;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function index(): Response
    {
        $query = Order::with(['customer', 'orderItems', 'hostingPlan'])->services();

        $orders = $query
            ->when(request('search'), function ($query, $search) {
                $query->where('orders.domain_name', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    })->orWhere('orders.id', 'like', "%{$search}%");
            })
            ->when(request('status'), function ($query, $status) {
                $query->where('orders.status', $status);
            })
            ->when(request('service_type'), function ($query, $type) {
                $query->where('orders.service_type', $type);
            })
            ->when(request('customer_id'), function ($query, $customerId) {
                $query->where('orders.customer_id', $customerId);
            })
            ->when(request('sort'), function ($query, $sort) {
                $direction = request('direction', 'asc');

                $allowedSorts = ['id', 'total_amount', 'status', 'billing_cycle', 'created_at', 'expires_at', 'customer_name'];
                if (! in_array($sort, $allowedSorts)) {
                    $sort = 'created_at';
                }

                if (! in_array($direction, ['asc', 'desc'])) {
                    $direction = 'desc';
                }

                if ($sort === 'customer_name') {
                    $query->join('customers', 'orders.customer_id', '=', 'customers.id')
                        ->orderBy('customers.name', $direction)
                        ->select('orders.*');
                } else {
                    $query->orderBy('orders.'.$sort, $direction);
                }
            }, function ($query) {
                $query->orderBy('orders.created_at', 'desc');
            })
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'view' => 'services',
            'filters' => request()->only(['search', 'status', 'service_type', 'customer_id']),
            'sort' => request('sort'),
            'direction' => request('direction', 'asc'),
            'customers' => Customer::select('id', 'name', 'email')->get(),
            'hostingPlans' => HostingPlan::active()->get(),
            'domainPrices' => DomainPrice::where('is_active', true)->get(),
            'servicePlans' => ServicePlan::where('is_active', true)->get(),
            'indexUrl' => '/admin/services',
            'includeViewParam' => false,
        ]);
    }

    public function show(int $id)
    {
        $order = Order::findOrFail($id);

        return redirect()->route('admin.orders.show', $order);
    }
}
