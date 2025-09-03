<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Customer;
use App\Models\HostingPlan;
use App\Models\DomainPrice;
use App\Models\ServicePlan;
use App\Models\OrderItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(): Response
    {
        $orders = Order::with(['customer', 'orderItems'])
            ->when(request('search'), function ($query, $search) {
                $query->whereHas('customer', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('id', 'like', "%{$search}%");
            })
            ->when(request('status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $customers = Customer::select('id', 'name', 'email')->get();
        $hostingPlans = HostingPlan::active()->get();
        $domainPrices = DomainPrice::where('is_active', true)->get();
        $servicePlans = ServicePlan::where('is_active', true)->get();

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'filters' => request()->only(['search', 'status']),
            'customers' => $customers,
            'hostingPlans' => $hostingPlans,
            'domainPrices' => $domainPrices,
            'servicePlans' => $servicePlans,
        ]);
    }

    public function show(Order $order): Response
    {
        $order->load([
            'customer',
            'orderItems',
            'invoice'
        ]);

        return Inertia::render('Admin/Orders/Show', [
            'order' => $order,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'order_type' => 'required|in:domain,hosting,domain_hosting,app,web,domain_hosting_app_web,maintenance',
            'billing_cycle' => 'required|in:monthly,quarterly,semi_annually,annually',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:hosting,domain,service,app,web,maintenance',
            'items.*.item_id' => 'required|integer',
            'items.*.domain_name' => 'nullable|string|max:255',
            'items.*.quantity' => 'required|integer|min:1',
        ]);

        DB::transaction(function () use ($request) {
            $totalAmount = 0;
            $items = collect($request->items);

            // Calculate total amount
            foreach ($items as $item) {
                switch ($item['item_type']) {
                    case 'hosting':
                        $plan = HostingPlan::findOrFail($item['item_id']);
                        $totalAmount += $plan->selling_price * $item['quantity'];
                        break;
                    case 'domain':
                        $domain = DomainPrice::findOrFail($item['item_id']);
                        $totalAmount += $domain->selling_price * $item['quantity'];
                        break;
                    case 'service':
                        $service = ServicePlan::findOrFail($item['item_id']);
                        $totalAmount += $service->price * $item['quantity'];
                        break;
                    case 'app':
                    case 'web':
                    case 'maintenance':
                        // Default pricing for new services
                        $totalAmount += 500000 * $item['quantity']; // IDR
                        break;
                }
            }

            $order = Order::create([
                'customer_id' => $request->customer_id,
                'order_type' => $request->order_type,
                'total_amount' => $totalAmount,
                'status' => 'pending',
                'billing_cycle' => $request->billing_cycle,
            ]);

            // Create order items
            foreach ($items as $item) {
                switch ($item['item_type']) {
                    case 'hosting':
                        $plan = HostingPlan::findOrFail($item['item_id']);
                        $price = $plan->selling_price;
                        break;
                    case 'domain':
                        $domain = DomainPrice::findOrFail($item['item_id']);
                        $price = $domain->selling_price;
                        break;
                    case 'service':
                        $service = ServicePlan::findOrFail($item['item_id']);
                        $price = $service->price;
                        break;
                    case 'app':
                    case 'web':
                    case 'maintenance':
                        $price = 500000; // Default price in IDR
                        break;
                    default:
                        $price = 0;
                        break;
                }

                OrderItem::create([
                    'order_id' => $order->id,
                    'item_type' => $item['item_type'],
                    'item_id' => $item['item_id'],
                    'domain_name' => $item['domain_name'],
                    'quantity' => $item['quantity'],
                    'price' => $price,
                ]);
            }
        });

        return redirect()->back()->with('success', 'Order created successfully!');
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'status' => 'required|in:pending,processing,completed,cancelled',
        ]);

        $order->update([
            'status' => $request->status,
        ]);

        return redirect()->back()->with('success', 'Order status updated successfully!');
    }

    public function destroy(Order $order)
    {
        if ($order->status === 'completed') {
            return redirect()->back()->with('error', 'Cannot delete completed orders.');
        }

        DB::transaction(function () use ($order) {
            $order->orderItems()->delete();
            $order->delete();
        });

        return redirect()->back()->with('success', 'Order deleted successfully!');
    }
}
