<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\JournalEntryRequest;
use App\Models\JournalEntry;
use App\Models\WebsiteClient;
use App\Services\JournalReportService;
use Inertia\Inertia;
use Inertia\Response;

class JournalEntryController extends Controller
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

        $journals = JournalEntry::with(['websiteClient', 'user'])
            ->when(request('website_client_id'), fn($q, $v) => $q->where('website_client_id', $v))
            ->when(request('user_id'), fn($q, $v) => $q->where('user_id', $v))
            ->when(request('date_from'), fn($q, $v) => $q->where('entry_date', '>=', $v))
            ->when(request('date_to'), fn($q, $v) => $q->where('entry_date', '<=', $v))
            ->orderBy('entry_date', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        $websites = WebsiteClient::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Journals/Index', [
            'journals' => $journals,
            'websites' => $websites,
            'filters' => request()->only(['website_client_id', 'user_id', 'date_from', 'date_to']),
        ]);
    }

    public function create(): Response
    {
        $this->checkAdmin();

        $websites = WebsiteClient::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Journals/CreateEdit', [
            'websites' => $websites,
            'journal' => null,
        ]);
    }

    public function edit(JournalEntry $journal): Response
    {
        $this->checkAdmin();

        $websites = WebsiteClient::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Journals/CreateEdit', [
            'websites' => $websites,
            'journal' => $journal,
        ]);
    }

    public function store(JournalEntryRequest $request)
    {
        $this->checkAdmin();

        $data = $request->validated();
        $data['user_id'] = auth()->id();

        JournalEntry::create($data);

        return redirect()->route('admin.journals.index')->with('success', 'Jurnal berhasil dicatat.');
    }

    public function update(JournalEntryRequest $request, JournalEntry $journal)
    {
        $this->checkAdmin();
        $journal->update($request->validated());

        return redirect()->route('admin.journals.index')->with('success', 'Jurnal berhasil diperbarui.');
    }

    public function destroy(JournalEntry $journal)
    {
        $this->checkAdmin();
        $journal->delete();

        return redirect()->route('admin.journals.index')->with('success', 'Jurnal berhasil dihapus.');
    }

    public function report(JournalReportService $reportService): Response
    {
        $this->checkAdmin();

        $period = request('period', 'daily'); // daily, weekly, monthly
        $dateFrom = request('date_from', now()->startOfMonth()->toDateString());
        $dateTo = request('date_to', now()->toDateString());
        $websiteClientId = request('website_client_id');

        $reportData = $reportService->generate($period, $dateFrom, $dateTo, $websiteClientId);
        $websites = WebsiteClient::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/Journals/Report', [
            'reportData' => $reportData,
            'websites' => $websites,
            'filters' => [
                'period' => $period,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'website_client_id' => $websiteClientId,
            ],
        ]);
    }

    public function export(JournalReportService $reportService)
    {
        $this->checkAdmin();

        $period = request('period', 'daily');
        $dateFrom = request('date_from', now()->startOfMonth()->toDateString());
        $dateTo = request('date_to', now()->toDateString());
        $websiteClientId = request('website_client_id');

        $reportData = $reportService->generate($period, $dateFrom, $dateTo, $websiteClientId);

        $filename = 'laporan-jurnal-' . $period . '-' . date('Ymd') . '.csv';
        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($reportData) {
            $output = fopen('php://output', 'w');
            // BOM for UTF-8 Excel compatibility
            fprintf($output, chr(0xEF) . chr(0xBB) . chr(0xBF));

            fputcsv($output, ['Website', 'Tanggal', 'Tipe Aktivitas', 'Detail', 'User']);

            foreach ($reportData['entries'] ?? [] as $entry) {
                foreach (($entry['activities'] ?? []) as $activity) {
                    fputcsv($output, [
                        $entry['website_name'] ?? '',
                        $entry['entry_date'] ?? '',
                        $activity['type_label'] ?? $activity['type'] ?? '',
                        $reportService->formatActivityDetail($activity),
                        $entry['user_name'] ?? '',
                    ]);
                }
            }

            fclose($output);
        };

        return response()->stream($callback, 200, $headers);
    }
}
