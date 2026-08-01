<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\WebsiteClientRequest;
use App\Models\Customer;
use App\Models\WebsiteClient;
use Inertia\Inertia;
use Inertia\Response;

class WebsiteClientController extends Controller
{
    private function checkAdmin(): void
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }
    }

    public function index(): Response
    {
        $this->checkAdmin();

        $websites = WebsiteClient::with('customer')
            ->when(request('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('url', 'like', "%{$search}%");
                });
            })
            ->when(request('customer_id'), fn($q, $v) => $q->where('customer_id', $v))
            ->when(request('is_active') !== null, fn($q, $v) => $q->where('is_active', filter_var($v, FILTER_VALIDATE_BOOLEAN)))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        $customers = Customer::orderBy('name')->get(['id', 'name']);

        $editingWebsite = null;
        if (request()->filled('edit')) {
            $editingWebsite = WebsiteClient::find(request('edit'));
        }

        return Inertia::render('Admin/WebsiteClients/Index', [
            'websites' => $websites,
            'customers' => $customers,
            'filters' => request()->only(['search', 'customer_id', 'is_active']),
            'editingWebsite' => $editingWebsite,
        ]);
    }

    public function store(WebsiteClientRequest $request)
    {
        $this->checkAdmin();
        WebsiteClient::create($request->validated());

        return redirect()->back()->with('success', 'Website berhasil ditambahkan.');
    }

    public function update(WebsiteClientRequest $request, WebsiteClient $website)
    {
        $this->checkAdmin();
        $website->update($request->validated());

        return redirect()->back()->with('success', 'Website berhasil diperbarui.');
    }

    public function destroy(WebsiteClient $website)
    {
        $this->checkAdmin();
        $website->delete();

        return redirect()->route('admin.websites.index')->with('success', 'Website berhasil dihapus.');
    }

    public function bulkDelete()
    {
        $this->checkAdmin();
        $ids = request('ids', []);

        if (empty($ids)) {
            return redirect()->back()->with('error', 'Tidak ada website yang dipilih.');
        }

        WebsiteClient::whereIn('id', $ids)->delete();

        return redirect()->back()->with('success', count($ids) . ' website berhasil dihapus.');
    }

    public function show(WebsiteClient $website): Response
    {
        $this->checkAdmin();

        $website->load('customer');

        $journals = $website->journals()
            ->with('user')
            ->orderBy('entry_date', 'desc')
            ->paginate(10);

        return Inertia::render('Admin/WebsiteClients/Show', [
            'website' => $website,
            'journals' => $journals,
        ]);
    }
}
