<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\JournalEntry;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Inertia\Inertia;
use Inertia\Response;

class MaintenanceController extends Controller
{
    public function index(): Response
    {
        $customer = Auth::guard('customer')->user();
        $websiteIds = $customer->websiteClients()->pluck('id');

        $filters = request()->only(['website_client_id', 'date_from', 'date_to']);

        if ($websiteIds->isEmpty()) {
            return Inertia::render('Customer/Maintenance/Index', [
                'journals' => ['data' => [], 'current_page' => 1, 'last_page' => 1, 'per_page' => 20, 'total' => 0, 'links' => []],
                'websites' => [],
                'filters' => $filters,
                'chartData' => [],
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

        // Chart data: last 30 days activity count
        $chartData = $this->buildChartData($websiteIds);

        return Inertia::render('Customer/Maintenance/Index', [
            'journals' => $journals,
            'websites' => $websites,
            'filters' => $filters,
            'chartData' => $chartData,
        ]);
    }

    private function buildChartData($websiteIds): array
    {
        $start = Carbon::now()->subDays(29)->startOfDay();
        $end = Carbon::now()->endOfDay();

        $entries = JournalEntry::whereIn('website_client_id', $websiteIds)
            ->whereBetween('entry_date', [$start->toDateString(), $end->toDateString()])
            ->get(['entry_date', 'activities']);

        $dailyCounts = [];
        $dailyByType = [];
        $max = 0;

        foreach ($entries as $entry) {
            $date = $entry->entry_date->format('Y-m-d');
            $count = count($entry->activities ?? []);
            $dailyCounts[$date] = ($dailyCounts[$date] ?? 0) + $count;

            foreach ($entry->activities ?? [] as $a) {
                $type = $a['type'] ?? 'other';
                $dailyByType[$date][$type] = ($dailyByType[$date][$type] ?? 0) + 1;
            }

            if ($dailyCounts[$date] > $max) {
                $max = $dailyCounts[$date];
            }
        }

        $result = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = Carbon::now()->subDays($i)->format('Y-m-d');
            $label = Carbon::now()->subDays($i)->translatedFormat('d M');
            $count = $dailyCounts[$date] ?? 0;
            $result[] = [
                'date' => $date,
                'label' => $label,
                'total' => $count,
                'byType' => $dailyByType[$date] ?? [],
                'height_pct' => $max > 0 ? round(($count / $max) * 100) : 0,
            ];
        }

        return $result;
    }
}
