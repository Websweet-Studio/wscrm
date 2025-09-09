<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\HostingPlan;
use App\Models\Service;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ServiceController extends Controller
{
    public function index(): Response
    {
        $services = Service::with(['customer', 'hostingPlan'])
            ->when(request('search'), function ($query, $search) {
                $query->where('domain_name', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($q) use ($search) {
                        $q->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
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

        return Inertia::render('Admin/Services/Index', [
            'services' => $services,
            'filters' => request()->only(['search', 'status', 'service_type']),
            'customers' => $customers,
            'hostingPlans' => $hostingPlans,
        ]);
    }

    public function show(Service $service): Response
    {
        $service->load([
            'customer',
            'hostingPlan',
        ]);

        return Inertia::render('Admin/Services/Show', [
            'service' => $service,
        ]);
    }

    public function store(Request $request)
    {
        $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'service_type' => 'required|in:hosting,domain',
            'plan_id' => 'required_if:service_type,hosting|exists:hosting_plans,id',
            'domain_name' => 'required|string|max:255',
            'expires_at' => 'required|date|after:today',
            'auto_renew' => 'boolean',
        ]);

        Service::create([
            'customer_id' => $request->customer_id,
            'service_type' => $request->service_type,
            'plan_id' => $request->plan_id,
            'domain_name' => $request->domain_name,
            'status' => 'pending',
            'expires_at' => $request->expires_at,
            'auto_renew' => $request->auto_renew ?? false,
        ]);

        return redirect()->back()->with('success', 'Layanan berhasil dibuat!');
    }

    public function update(Request $request, Service $service)
    {
        $request->validate([
            'status' => 'required|in:active,suspended,terminated,pending',
            'expires_at' => 'required|date',
            'auto_renew' => 'boolean',
            'domain_name' => 'required|string|max:255',
        ]);

        $service->update($request->only(['status', 'expires_at', 'auto_renew', 'domain_name']));

        return redirect()->back()->with('success', 'Layanan berhasil diperbarui!');
    }

    public function destroy(Service $service)
    {
        if ($service->status === 'active') {
            return redirect()->back()->with('error', 'Tidak dapat menghapus layanan aktif. Mohon tangguhkan atau hentikan terlebih dahulu.');
        }

        $service->delete();

        return redirect()->back()->with('success', 'Layanan berhasil dihapus!');
    }
}
