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
            $query->where('domain_name', 'like', "%{$search}%")
                ->orWhereHas('customer', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('email', 'like', "%{$search}%");
                })->orWhere('id', 'like', "%{$search}%");
        })
            ->when(request('status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->when(request('service_type'), function ($query, $type) {
                $query->where('service_type', $type);
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
            'view' => $view,
            'filters' => request()->only(['search', 'status', 'service_type', 'view']),
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
        if ($order->isOrder()) {
            // Order update
            $request->validate([
                'status' => 'required|in:pending,processing,cancelled',
            ]);
        } else {
            // Service update
            $request->validate([
                'status' => 'required|in:active,suspended,expired,terminated',
                'expires_at' => 'nullable|date',
                'auto_renew' => 'boolean',
                'domain_name' => 'nullable|string|max:255',
            ]);
        }

        $order->update($request->only(['status', 'expires_at', 'auto_renew', 'domain_name']));

        $message = $order->isOrder() ? 'Status pesanan berhasil diperbarui!' : 'Layanan berhasil diperbarui!';

        return redirect()->back()->with('success', $message);
    }

    public function createService(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'service_type' => 'required|in:hosting,domain',
            'plan_id' => 'required_if:service_type,hosting|exists:hosting_plans,id',
            'domain_name' => 'required|string|max:255',
            'expires_at' => 'required|date|after:today',
            'auto_renew' => 'boolean',
        ]);

        Order::create([
            'customer_id' => $request->customer_id,
            'service_type' => $request->service_type,
            'plan_id' => $request->plan_id,
            'domain_name' => $request->domain_name,
            'status' => 'active',
            'expires_at' => $request->expires_at,
            'auto_renew' => $request->auto_renew ?? false,
            'billing_cycle' => 'annually',
            'total_amount' => 0, // Will be updated with proper pricing
        ]);

        return redirect()->back()->with('success', 'Layanan berhasil dibuat!');
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
