<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\JournalEntry;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class MaintenanceController extends Controller
{
    public function index(): Response
    {
        $customer = Auth::guard('customer')->user();
        $websiteIds = $customer->websiteClients()->pluck('id');

        if ($websiteIds->isEmpty()) {
            return Inertia::render('Customer/Maintenance/Index', [
                'journals' => ['data' => [], 'current_page' => 1, 'last_page' => 1, 'per_page' => 20, 'total' => 0, 'links' => []],
                'websites' => [],
                'filters' => request()->only(['website_client_id', 'date_from', 'date_to']),
            ]);
        }

        $journals = JournalEntry::with(['websiteClient', 'user'])
            ->whereIn('website_client_id', $websiteIds)
            ->when(request('website_client_id'), fn($q, $v) => $q->where('website_client_id', $v))
            ->when(request('date_from'), fn($q, $v) => $q->where('entry_date', '>=', $v))
            ->when(request('date_to'), fn($q, $v) => $q->where('entry_date', '<=', $v))
            ->orderBy('entry_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $websites = $customer->websiteClients()->orderBy('name')->get(['id', 'name']);

        return Inertia::render('Customer/Maintenance/Index', [
            'journals' => $journals,
            'websites' => $websites,
            'filters' => request()->only(['website_client_id', 'date_from', 'date_to']),
        ]);
    }
}
