<?php

namespace App\Services\AiAgents;

use App\Models\JournalEntry;
use App\Models\WebsiteClient;
use Illuminate\Support\Facades\Log;

/**
 * Sub-agent: jurnal maintenance — catat aktivitas harian per website, daftar, update.
 */
class JournalAgent
{
    private const VALID_TYPES = ['wp_update', 'plugin_update', 'theme_update', 'article', 'page_optimization', 'other'];

    public function listJournals(?int $websiteId = null, ?string $dateFrom = null, ?string $dateTo = null, ?callable $onEvent = null): array
    {
        if ($onEvent) {
            $onEvent('Menarik daftar jurnal maintenance...', 'loading', 'Journal Agent');
        }

        $query = JournalEntry::with('websiteClient.customer', 'user')
            ->when($websiteId, fn ($q) => $q->where('website_client_id', $websiteId))
            ->when($dateFrom, fn ($q) => $q->where('entry_date', '>=', $dateFrom))
            ->when($dateTo, fn ($q) => $q->where('entry_date', '<=', $dateTo))
            ->orderBy('entry_date', 'desc')
            ->limit(30)
            ->get();

        $list = $query->map(fn (JournalEntry $j) => [
            'id' => $j->id,
            'website' => $j->websiteClient?->name ?? 'Tanpa website',
            'customer' => $j->websiteClient?->customer?->name,
            'entry_date' => $j->entry_date?->format('d M Y'),
            'activity_count' => count($j->activities ?? []),
            'summary' => $j->summary,
        ])->values()->all();

        if ($onEvent) {
            $onEvent(count($list) . ' jurnal ditemukan', 'done', 'Journal Agent');
        }

        return [
            'journals' => $list,
            'total' => count($list),
            'summary' => count($list) . ' jurnal maintenance ditemukan.',
        ];
    }

    public function createJournal(array $data, ?callable $onEvent = null): array
    {
        if ($onEvent) {
            $onEvent('Mencatat jurnal maintenance...', 'loading', 'Journal Agent');
        }

        $validated = validator($data, [
            'website_client_id' => 'required|exists:website_clients,id',
            'entry_date' => 'required|date',
            'activities' => 'required|array|min:1',
            'activities.*.type' => 'required|string|in:' . implode(',', self::VALID_TYPES),
            'summary' => 'nullable|string|max:5000',
        ])->validate();

        // Satu entry per website per tanggal
        $exists = JournalEntry::where('website_client_id', $validated['website_client_id'])
            ->where('entry_date', $validated['entry_date'])
            ->exists();

        if ($exists) {
            $website = WebsiteClient::find($validated['website_client_id']);
            return ['error' => "Jurnal untuk " . ($website?->name ?? 'website ini') . " tanggal {$validated['entry_date']} sudah ada. Gunakan update_journal untuk mengubahnya."];
        }

        $entry = JournalEntry::create(array_merge($validated, [
            'user_id' => auth()->id(),
        ]));

        $website = $entry->websiteClient?->name ?? 'website';

        if ($onEvent) {
            $onEvent("Jurnal untuk {$website} ({$entry->entry_date->format('d M Y')}) dicatat", 'done', 'Journal Agent');
        }

        return [
            'success' => true,
            'journal_id' => $entry->id,
            'website' => $website,
            'entry_date' => $entry->entry_date->format('d M Y'),
            'activity_count' => count($entry->activities),
            'message' => "Jurnal maintenance untuk {$website} tanggal {$entry->entry_date->format('d M Y')} berhasil dicatat (" . count($entry->activities) . " aktivitas).",
        ];
    }

    public function updateJournal(int $journalId, array $data, ?callable $onEvent = null): array
    {
        $entry = JournalEntry::find($journalId);
        if (!$entry) {
            return ['error' => 'Jurnal tidak ditemukan.'];
        }

        if ($onEvent) {
            $onEvent("Mengupdate jurnal tanggal {$entry->entry_date?->format('d M Y')}...", 'loading', 'Journal Agent');
        }

        $validated = validator($data, [
            'website_client_id' => 'nullable|exists:website_clients,id',
            'entry_date' => 'nullable|date',
            'activities' => 'required_without_all:website_client_id,entry_date,summary|array|min:1',
            'activities.*.type' => 'required|string|in:' . implode(',', self::VALID_TYPES),
            'summary' => 'nullable|string|max:5000',
        ])->validate();

        // Cek duplikat bila tanggal/website berubah
        $websiteId = $validated['website_client_id'] ?? $entry->website_client_id;
        $date = $validated['entry_date'] ?? $entry->entry_date?->toDateString();
        $dup = JournalEntry::where('website_client_id', $websiteId)
            ->where('entry_date', $date)
            ->where('id', '!=', $entry->id)
            ->exists();

        if ($dup) {
            return ['error' => "Sudah ada jurnal untuk website dan tanggal tersebut. Ubah tanggal/website atau edit entry yang benar."];
        }

        // Update jurnal: aktivitas baru DITAMBAHKAN ke yang sudah ada (bukan mengganti),
        // lalu di-dedupe berdasarkan tipe+judul agar tidak duplikat saat AI kirim ulang.
        $existing = $entry->activities ?? [];
        $newActivities = $validated['activities'] ?? [];
        $validated['activities'] = collect(array_merge($existing, $newActivities))
            ->unique(fn ($a) => ($a['type'] ?? '') . '|' . ($a['title'] ?? $a['plugin'] ?? $a['description'] ?? ''))
            ->values()
            ->all();

        $entry->update(array_merge($validated, [
            'website_client_id' => $websiteId,
            'entry_date' => $date,
        ]));

        if ($onEvent) {
            $onEvent("Jurnal #{$entry->id} berhasil diupdate", 'done', 'Journal Agent');
        }

        return [
            'success' => true,
            'journal_id' => $entry->id,
            'message' => "Jurnal #{$entry->id} berhasil diperbarui.",
        ];
    }

    public function deleteJournal(int $journalId, ?callable $onEvent = null): array
    {
        $entry = JournalEntry::find($journalId);
        if (!$entry) {
            return ['error' => 'Jurnal tidak ditemukan.'];
        }

        if ($onEvent) {
            $onEvent("Menghapus jurnal #{$journalId}...", 'loading', 'Journal Agent');
        }

        $entry->delete();

        if ($onEvent) {
            $onEvent("Jurnal #{$journalId} dihapus", 'done', 'Journal Agent');
        }

        return [
            'success' => true,
            'journal_id' => $journalId,
            'message' => "Jurnal #{$journalId} berhasil dihapus.",
        ];
    }
}
