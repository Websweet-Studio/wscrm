<?php

namespace App\Http\Controllers;

use App\Models\DomainPrice;
use App\Models\HostingPlan;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class OrderController extends Controller
{
    public function index(): Response
    {
        $orders = Auth::guard('customer')->user()->orders()
            ->with(['orderItems'])
            ->orderBy('created_at', 'desc')
            ->get();

        return Inertia::render('Orders/Index', [
            'orders' => $orders,
        ]);
    }

    public function show(Order $order): Response
    {
        $this->authorize('view', $order);

        $order->load(['orderItems']);

        return Inertia::render('Orders/Show', [
            'order' => $order,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'items' => 'required|array|min:1',
            'items.*.item_type' => 'required|in:hosting,domain,app,web,maintenance',
            'items.*.item_id' => 'required|integer',
            'items.*.domain_name' => 'nullable|string',
            'items.*.quantity' => 'integer|min:1',
            'billing_cycle' => 'required|in:monthly,quarterly,semi_annually,annually',
        ]);

        DB::transaction(function () use ($request) {
            $totalAmount = 0;
            $orderItems = [];
            $hasHosting = false;
            $hasDomain = false;
            $hostingPlan = null;

            // First pass: identify if this is a hosting + domain bundle
            foreach ($request->items as $item) {
                if ($item['item_type'] === 'hosting') {
                    $hasHosting = true;
                    $hostingPlan = HostingPlan::findOrFail($item['item_id']);
                } elseif ($item['item_type'] === 'domain') {
                    $hasDomain = true;
                }
            }

            // Check if eligible for bundle discount (hosting 2GB+ with new domain)
            $isBundleEligible = $hasHosting && $hasDomain && $hostingPlan && $hostingPlan->storage_gb >= 2;

            // Calculate total amount
            foreach ($request->items as $item) {
                switch ($item['item_type']) {
                    case 'hosting':
                        $hostingPlan = HostingPlan::findOrFail($item['item_id']);
                        $price = $hostingPlan->selling_price;

                        // Apply existing hosting discount first
                        if ($hostingPlan->discount_percent > 0) {
                            $price = $price * (1 - $hostingPlan->discount_percent / 100);
                        }

                        // Apply bundle discount if eligible (10% off hosting)
                        if ($isBundleEligible) {
                            $price = $price * 0.9; // 10% bundle discount
                        }
                        break;
                    case 'domain':
                        $domainPrice = DomainPrice::findOrFail($item['item_id']);
                        $price = $domainPrice->selling_price;
                        break;
                    case 'app':
                    case 'web':
                    case 'maintenance':
                        // For now, use a default price or look up from a services table
                        // You might want to create separate models/tables for these
                        $price = 500000; // Default price in IDR
                        break;
                    default:
                        $price = 0;
                        break;
                }

                $quantity = $item['quantity'] ?? 1;
                $itemTotal = $price * $quantity;
                $totalAmount += $itemTotal;

                $orderItems[] = [
                    'item_type' => $item['item_type'],
                    'item_id' => $item['item_id'],
                    'domain_name' => $item['domain_name'] ?? null,
                    'quantity' => $quantity,
                    'price' => $itemTotal,
                ];
            }

            // Create order
            $order = Order::create([
                'customer_id' => Auth::guard('customer')->id(),
                'total_amount' => $totalAmount,
                'billing_cycle' => $request->billing_cycle,
                'status' => 'pending',
            ]);

            // Create order items
            foreach ($orderItems as $orderItem) {
                $order->orderItems()->create($orderItem);
            }

            return $order;
        });

        return redirect()->route('orders.index')->with('success', 'Order created successfully!');
    }
}
