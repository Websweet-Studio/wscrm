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

    /**
     * Sub-field aktivitas wajib didaftarkan agar validasi (excludeUnvalidatedArrayKeys)
     * tidak membuang field lain selain "type" dari activities.
     */
    private const ACTIVITY_FIELD_RULES = [
        'activities.*.title' => 'nullable|string',
        'activities.*.url' => 'nullable|string',
        'activities.*.word_count' => 'nullable|integer',
        'activities.*.plugin' => 'nullable|string',
        'activities.*.theme' => 'nullable|string',
        'activities.*.page' => 'nullable|string',
        'activities.*.detail' => 'nullable|string',
        'activities.*.description' => 'nullable|string',
        'activities.*.from_version' => 'nullable|string',
        'activities.*.to_version' => 'nullable|string',
        'activities.*.note' => 'nullable|string',
    ];

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
        ] + self::ACTIVITY_FIELD_RULES)->validate();

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
        ] + self::ACTIVITY_FIELD_RULES)->validate();

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

        // Update jurnal: aktivitas baru di-MERGE dengan yang sudah ada.
        // Cocokkan berdasar identitas (tipe + judul/plugin/halaman). Jika identitas sama,
        // field yang dikirim AI menimpa yang lama (misal perbaikan description),
        // bukan membuat duplikat. Aktivitas dengan identitas baru di-append.
        $existing = $entry->activities ?? [];
        $newActivities = $validated['activities'] ?? [];
        $validated['activities'] = $this->mergeActivities($existing, $newActivities);

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

    /**
     * Merge aktivitas lama + baru. Aktivitas dengan identitas sama (tipe + title/plugin/page)
     * di-overwrite field-nya; yang baru di-append.
     */
    private function mergeActivities(array $existing, array $incoming): array
    {
        foreach ($incoming as $new) {
            $key = $this->activityIdentity($new);
            $matched = false;

            foreach ($existing as $i => $old) {
                if ($this->activityIdentity($old) === $key) {
                    // Field baru menimpa, field lama yang tidak dikirim tetap dipertahankan
                    $existing[$i] = array_merge($old, $new);
                    $matched = true;
                    break;
                }
            }

            if (!$matched) {
                $existing[] = $new;
            }
        }

        return array_values($existing);
    }

    /**
     * Identitas aktivitas: tipe + (title|plugin|page). Untuk "other" tanpa identitas
     * lanjutan, description dijadikan pembeda agar beberapa aktivitas "other" tidak saling timpa.
     */
    private function activityIdentity(array $a): string
    {
        $type = $a['type'] ?? '';
        if ($type === 'other' && empty($a['title']) && empty($a['plugin']) && empty($a['page'])) {
            return 'other|' . ($a['description'] ?? '');
        }

        return $type . '|' . ($a['title'] ?? $a['plugin'] ?? $a['page'] ?? '');
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
