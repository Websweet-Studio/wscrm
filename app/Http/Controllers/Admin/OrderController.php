<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\DomainPrice;
use App\Models\HostingPlan;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\ServicePlan;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(): Response
    {
        $view = request('view', 'orders'); // orders or services

        $query = Order::with(['customer', 'orderItems', 'hostingPlan']);

        if ($view === 'services') {
            // Show service-like records (active, suspended, expired, terminated)
            $query->services();
        } else {
            // Show order-like records (pending, processing, cancelled)
            $query->orders();
        }

        $orders = $query->when(request('search'), function ($query, $search) {
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

                // Validate sort field
                $allowedSorts = ['id', 'total_amount', 'status', 'billing_cycle', 'created_at', 'expires_at', 'customer_name'];
                if (!in_array($sort, $allowedSorts)) {
                    $sort = 'created_at';
                }

                // Validate direction
                if (!in_array($direction, ['asc', 'desc'])) {
                    $direction = 'desc';
                }

                if ($sort === 'customer_name') {
                    $query->join('customers', 'orders.customer_id', '=', 'customers.id')
                        ->orderBy('customers.name', $direction)
                        ->select('orders.*');
                } else {
                    $query->orderBy('orders.' . $sort, $direction);
                }
            }, function ($query) {
                // Default sorting
                $query->orderBy('orders.created_at', 'desc');
            })
            ->paginate(20)
            ->withQueryString();

        $customers = Customer::select('id', 'name', 'email')->get();
        $hostingPlans = HostingPlan::active()->get();
        $domainPrices = DomainPrice::where('is_active', true)->get();
        $servicePlans = ServicePlan::where('is_active', true)->get();

        return Inertia::render('Admin/Orders/Index', [
            'orders' => $orders,
            'view' => $view,
            'filters' => request()->only(['search', 'status', 'service_type', 'customer_id', 'view']),
            'sort' => request('sort'),
            'direction' => request('direction', 'asc'),
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
            'hostingPlan',
            'invoices',
        ]);

        return Inertia::render('Admin/Orders/Show', [
            'order' => $order,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'domain_name' => 'nullable|string|max:255',
            'billing_cycle' => 'required|in:onetime,monthly,quarterly,semi_annually,annually',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:hosting,domain,service,app,web,maintenance',
            'items.*.item_id' => 'required|integer',
        ]);

        DB::transaction(function () use ($request) {
            $totalAmount = 0;
            $items = collect($request->items);

            // Calculate total amount
            foreach ($items as $item) {
                switch ($item['item_type']) {
                    case 'hosting':
                        $plan = HostingPlan::findOrFail($item['item_id']);
                        $totalAmount += $plan->selling_price;
                        break;
                    case 'domain':
                        $domain = DomainPrice::findOrFail($item['item_id']);
                        $totalAmount += $domain->selling_price;
                        break;
                    case 'service':
                        $service = ServicePlan::findOrFail($item['item_id']);
                        $totalAmount += $service->price;
                        break;
                    case 'app':
                    case 'web':
                    case 'maintenance':
                        // Default pricing for new services
                        $totalAmount += 500000; // IDR
                        break;
                }
            }

            $order = Order::create([
                'customer_id' => $request->customer_id,
                'order_type' => 'hosting',
                'domain_name' => $request->domain_name,
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
                    'domain_name' => null,
                    'quantity' => 1,
                    'price' => $price,
                ]);
            }
        });

        return redirect()->back()->with('success', 'Pesanan berhasil dibuat!');
    }

    public function update(Request $request, Order $order)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'domain_name' => 'nullable|string|max:255',
            'billing_cycle' => 'required|in:onetime,monthly,quarterly,semi_annually,annually',
            'status' => 'required|in:pending,processing,active,suspended,expired,cancelled,terminated',
            'expires_at' => 'nullable|date',
            'auto_renew' => 'boolean',
            'discount_amount' => 'nullable|numeric|min:0',
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:hosting,domain,service,app,web,maintenance',
            'items.*.item_id' => 'required|integer',
            'items.*.price' => 'nullable|numeric|min:0',
        ]);

        DB::transaction(function () use ($request, $order) {
            // Update order basic info
            $order->update([
                'customer_id' => $request->customer_id,
                'domain_name' => $request->domain_name,
                'billing_cycle' => $request->billing_cycle,
                'status' => $request->status,
                'expires_at' => $request->expires_at,
                'auto_renew' => $request->auto_renew ?? false,
                'discount_amount' => $request->discount_amount ?? 0,
            ]);

            // Delete existing order items
            $order->orderItems()->delete();

            // Create new order items and recalculate total
            $totalAmount = 0;
            $items = collect($request->items);

            foreach ($items as $item) {
                $price = $item['price'] ?? $this->getDefaultPrice($item['item_type'], $item['item_id']);
                $totalAmount += (float) $price;

                OrderItem::create([
                    'order_id' => $order->id,
                    'item_type' => $item['item_type'],
                    'item_id' => $item['item_id'],
                    'domain_name' => null, // Domain name is at order level
                    'quantity' => 1,
                    'price' => $price,
                ]);
            }

            // Update total amount
            $order->update(['total_amount' => $totalAmount]);
        });

        return redirect()->back()->with('success', 'Pesanan berhasil diperbarui!');
    }

    private function getDefaultPrice(string $itemType, int $itemId): float
    {
        switch ($itemType) {
            case 'hosting':
                $plan = HostingPlan::find($itemId);

                return $plan ? $plan->selling_price : 500000;
            case 'domain':
                $domain = DomainPrice::find($itemId);

                return $domain ? $domain->selling_price : 150000;
            case 'service':
                $service = ServicePlan::find($itemId);

                return $service ? $service->price : 500000;
            case 'app':
                return 2500000; // Default app development price
            case 'web':
                return 1500000; // Default web development price
            case 'maintenance':
                return 300000; // Default maintenance price
            default:
                return 100000; // Fallback price
        }
    }

    public function destroy(Order $order)
    {
        if ($order->isService() && $order->status === 'active') {
            return redirect()->back()->with('error', 'Tidak dapat menghapus layanan aktif. Mohon tangguhkan atau hentikan terlebih dahulu.');
        }

        if ($order->isOrder() && $order->status === 'completed') {
            return redirect()->back()->with('error', 'Tidak dapat menghapus pesanan yang sudah selesai.');
        }

        DB::transaction(function () use ($order) {
            $order->orderItems()->delete();
            $order->invoices()->delete();
            $order->delete();
        });

        $message = $order->isOrder() ? 'Pesanan berhasil dihapus!' : 'Layanan berhasil dihapus!';

        return redirect()->back()->with('success', $message);
    }
}
