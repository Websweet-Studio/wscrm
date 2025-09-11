<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Inertia\Inertia;
use Inertia\Response;

class CustomerController extends Controller
{
    public function index(): Response
    {
        $customers = Customer::with(['orders'])
            ->withCount(['orders'])
            ->addSelect([
                'services_count' => \DB::raw('(
                    SELECT COUNT(*)
                    FROM order_items oi
                    JOIN orders o ON o.id = oi.order_id
                    WHERE o.customer_id = customers.id
                    AND oi.item_type IN ("hosting", "domain")
                )'),
            ])
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            })
            ->when(request('status'), function ($query, $status) {
                $query->where('status', $status);
            })
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Customers/Index', [
            'customers' => $customers,
            'filters' => request()->only(['search', 'status']),
        ]);
    }

    public function show(Customer $customer): Response
    {
        $customer->load([
            'orders.orderItems',
            'invoices',
        ]);

        // Get services from orders
        $services = \DB::table('order_items')
            ->join('orders', 'orders.id', '=', 'order_items.order_id')
            ->leftJoin('hosting_plans', 'hosting_plans.id', '=', 'order_items.hosting_plan_id')
            ->where('orders.customer_id', $customer->id)
            ->whereIn('order_items.item_type', ['hosting', 'domain'])
            ->select([
                'order_items.id',
                'order_items.domain_name',
                'order_items.item_type as service_type',
                'order_items.status',
                'order_items.expires_at',
                'order_items.created_at',
                'hosting_plans.plan_name',
                'hosting_plans.storage_gb',
                'hosting_plans.bandwidth_gb'
            ])
            ->get()
            ->map(function ($service) {
                return [
                    'id' => $service->id,
                    'domain_name' => $service->domain_name,
                    'service_type' => $service->service_type,
                    'status' => $service->status,
                    'expires_at' => $service->expires_at,
                    'created_at' => $service->created_at,
                    'hosting_plan' => $service->plan_name ? [
                        'plan_name' => $service->plan_name,
                        'storage_gb' => $service->storage_gb,
                        'bandwidth_gb' => $service->bandwidth_gb,
                    ] : null,
                ];
            });

        $customer->services = $services;

        return Inertia::render('Admin/Customers/Show', [
            'customer' => $customer,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:customers',
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
        ]);

        Customer::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'city' => $request->city,
            'country' => $request->country,
            'postal_code' => $request->postal_code,
            'status' => 'active',
        ]);

        return redirect()->back()->with('success', 'Pelanggan berhasil dibuat!');
    }

    public function update(Request $request, Customer $customer)
    {
        \Log::info('Update customer request', [
            'customer_id' => $customer->id,
            'request_data' => $request->all(),
            'email_validation_rule' => 'unique:customers,email,'.$customer->id,
        ]);

        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:customers,email,'.$customer->id,
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
            'status' => 'required|in:active,inactive,suspended',
        ]);

        $updateData = $request->only([
            'name', 'email', 'phone', 'address', 'city', 'country', 'postal_code', 'status',
        ]);

        if ($request->filled('password')) {
            $request->validate([
                'password' => ['confirmed', Rules\Password::defaults()],
            ]);
            $updateData['password'] = Hash::make($request->password);
        }

        $customer->update($updateData);

        return redirect()->back()->with('success', 'Pelanggan berhasil diperbarui!');
    }

    public function destroy(Customer $customer)
    {
        try {
            // Delete all related data in the correct order
            // First delete invoices
            $customer->invoices()->delete();

            // Then delete order items and orders
            foreach ($customer->orders as $order) {
                $order->orderItems()->delete();
            }
            $customer->orders()->delete();

            // Services are now handled through orders - no separate deletion needed

            // Finally delete the customer
            $customer->delete();

            return redirect()->back()->with('success', 'Pelanggan dan semua data terkait berhasil dihapus!');
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal menghapus pelanggan: '.$e->getMessage());
        }
    }
}
