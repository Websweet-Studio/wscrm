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

        $journals = JournalEntry::with(['websiteClient.customer', 'user'])
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

    public function exportExcel(JournalReportService $reportService)
    {
        $this->checkAdmin();

        $journals = JournalEntry::with(['websiteClient', 'user'])
            ->when(request('website_client_id'), fn($q, $v) => $q->where('website_client_id', $v))
            ->when(request('date_from'), fn($q, $v) => $q->where('entry_date', '>=', $v))
            ->when(request('date_to'), fn($q, $v) => $q->where('entry_date', '<=', $v))
            ->orderBy('entry_date', 'desc')
            ->get();

        $typeLabels = [
            'wp_update' => 'WP Update',
            'plugin_update' => 'Update Plugin',
            'theme_update' => 'Update Tema',
            'article' => 'Artikel',
            'page_optimization' => 'Optimasi Halaman',
            'other' => 'Lainnya',
        ];

        $rows = [];
        foreach ($journals as $j) {
            foreach ($j->activities ?? [] as $activity) {
                $rows[] = [
                    $j->websiteClient?->name ?? '',
                    $j->entry_date,
                    $typeLabels[$activity['type'] ?? ''] ?? $activity['type'] ?? '',
                    $reportService->formatActivityDetail($activity),
                    $j->user?->name ?? '',
                ];
            }
        }

        $headers = ['Website', 'Tanggal', 'Tipe Aktivitas', 'Detail', 'User'];

        $filename = 'jurnal-maintenance-' . date('Ymd-His') . '.xlsx';

        return response($this->buildXlsx($headers, $rows), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    /**
     * Build a minimal valid .xlsx file (no external library required).
     */
    private function buildXlsx(array $headers, array $rows): string
    {
        $sheetData = '<sheetData>';
        $sheetData .= '<row r="1">';
        foreach ($headers as $i => $h) {
            $sheetData .= $this->xlsxCell($this->colLetter($i + 1) . '1', $h);
        }
        $sheetData .= '</row>';

        foreach ($rows as $rowNum => $row) {
            $r = $rowNum + 2;
            $sheetData .= '<row r="' . $r . '">';
            foreach ($row as $i => $cell) {
                $sheetData .= $this->xlsxCell($this->colLetter($i + 1) . $r, $cell);
            }
            $sheetData .= '</row>';
        }
        $sheetData .= '</sheetData>';

        $parts = [
            '[Content_Types].xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types"><Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/><Default Extension="xml" ContentType="application/xml"/><Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/><Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/></Types>',
            '_rels/.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/></Relationships>',
            'xl/workbook.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main" xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships"><sheets><sheet name="Jurnal" sheetId="1" r:id="rId1"/></sheets></workbook>',
            'xl/_rels/workbook.xml.rels' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships"><Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/></Relationships>',
            'xl/worksheets/sheet1.xml' => '<?xml version="1.0" encoding="UTF-8" standalone="yes"?><worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">' . $sheetData . '</worksheet>',
        ];

        $path = tempnam(sys_get_temp_dir(), 'xlsx');
        $zip = new \ZipArchive();
        $zip->open($path, \ZipArchive::CREATE | \ZipArchive::OVERWRITE);
        foreach ($parts as $name => $content) {
            $zip->addFromString($name, $content);
        }
        $zip->close();

        $content = file_get_contents($path);
        @unlink($path);

        return $content;
    }

    private function xlsxCell(string $ref, $value): string
    {
        return '<c r="' . $ref . '" t="inlineStr"><is><t xml:space="preserve">'
            . htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8')
            . '</t></is></c>';
    }

    private function colLetter(int $index): string
    {
        $letter = '';
        while ($index > 0) {
            $letter = chr(65 + (($index - 1) % 26)) . $letter;
            $index = intdiv($index - 1, 26);
        }
        return $letter;
    }
}
