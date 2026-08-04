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

    public function export()
    {
        $customer = Auth::guard('customer')->user();
        $websiteIds = $customer->websiteClients()->pluck('id');

        if ($websiteIds->isEmpty()) {
            return back()->with('error', 'Tidak ada data untuk diexport.');
        }

        $journals = JournalEntry::with(['websiteClient', 'user'])
            ->whereIn('website_client_id', $websiteIds)
            ->when(request('website_client_id'), fn($q, $v) => $q->where('website_client_id', $v))
            ->when(request('date_from'), fn($q, $v) => $q->where('entry_date', '>=', $v))
            ->when(request('date_to'), fn($q, $v) => $q->where('entry_date', '<=', $v))
            ->orderBy('entry_date', 'desc')
            ->get();

        $typeLabels = [
            'wp_update' => 'Update WordPress',
            'plugin_update' => 'Update Plugin',
            'theme_update' => 'Update Tema',
            'article' => 'Penulisan Artikel',
            'page_optimization' => 'Optimasi Halaman',
            'other' => 'Aktivitas Lain',
        ];

        $rows = [];
        foreach ($journals as $j) {
            $dateFormatted = Carbon::parse($j->entry_date)->translatedFormat('d F Y');
            foreach ($j->activities ?? [] as $activity) {
                $rows[] = [
                    $j->websiteClient?->name ?? '',
                    $dateFormatted,
                    $typeLabels[$activity['type'] ?? ''] ?? $activity['type'] ?? '',
                    $this->formatActivityDetail($activity),
                    $j->summary ?? '-',
                    $j->user?->name ?? '',
                ];
            }
        }

        $headers = ['Website', 'Tanggal', 'Tipe Aktivitas', 'Detail Pengerjaan', 'Ringkasan', 'Dikerjakan Oleh'];
        $filename = 'jurnal-maintenance-' . date('Ymd-His') . '.xlsx';

        return response($this->buildXlsx($headers, $rows), 200, [
            'Content-Type' => 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ]);
    }

    private function formatActivityDetail(array $activity): string
    {
        $type = $activity['type'] ?? '';

        return match ($type) {
            'wp_update' => sprintf(
                'Update WordPress dari versi %s ke %s%s',
                $activity['from_version'] ?? '-',
                $activity['to_version'] ?? '-',
                !empty($activity['note']) ? ' (' . $activity['note'] . ')' : ''
            ),
            'plugin_update' => sprintf(
                'Plugin %s diperbarui dari versi %s ke %s',
                $activity['plugin'] ?? '-',
                $activity['from_version'] ?? '-',
                $activity['to_version'] ?? '-'
            ),
            'theme_update' => sprintf(
                'Tema %s diperbarui dari versi %s ke %s',
                $activity['theme'] ?? '-',
                $activity['from_version'] ?? '-',
                $activity['to_version'] ?? '-'
            ),
            'article' => sprintf(
                'Artikel "%s" — %s kata',
                $activity['title'] ?? 'Tanpa Judul',
                $activity['word_count'] ?? 0
            ),
            'page_optimization' => sprintf(
                'Optimasi halaman %s: %s',
                $activity['page'] ?? '-',
                $activity['detail'] ?? '-'
            ),
            default => $activity['description'] ?? '-',
        };
    }

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
