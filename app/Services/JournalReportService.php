<?php

namespace App\Services;

use App\Enums\ActivityType;
use App\Models\JournalEntry;
use App\Models\WebsiteClient;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class JournalReportService
{
    public function generate(string $period, string $dateFrom, string $dateTo, ?string $websiteClientId = null): array
    {
        $from = Carbon::parse($dateFrom)->startOfDay();
        $to = Carbon::parse($dateTo)->endOfDay();

        $query = JournalEntry::with(['websiteClient', 'user'])
            ->whereBetween('entry_date', [$from, $to])
            ->when($websiteClientId, fn($q) => $q->where('website_client_id', $websiteClientId))
            ->orderBy('entry_date', 'desc')
            ->orderBy('created_at', 'desc');

        $entries = $query->get();

        // Group entries for aggregation
        if ($period === 'weekly') {
            $entries = $this->aggregateWeekly($entries);
        } elseif ($period === 'monthly') {
            $entries = $this->aggregateMonthly($entries);
        }

        $stats = $this->calculateStats($entries);
        $activityTypeLabels = ActivityType::options();

        return [
            'entries' => $this->formatEntries($entries),
            'stats' => $stats,
            'activity_type_labels' => $activityTypeLabels,
        ];
    }

    private function aggregateWeekly(Collection $entries): Collection
    {
        return $entries->groupBy(function ($entry) {
            $date = Carbon::parse($entry->entry_date);
            return $entry->website_client_id . '|' . $date->startOfWeek()->toDateString();
        })->map(function ($group) {
            $first = $group->first();
            $allActivities = $group->pluck('activities')->flatten(1)->values()->toArray();

            return [
                'website_client_id' => $first->website_client_id,
                'website_name' => $first->websiteClient?->name,
                'entry_date' => Carbon::parse($group->min('entry_date'))->startOfWeek()->toDateString() . ' s/d ' . Carbon::parse($group->max('entry_date'))->endOfWeek()->toDateString(),
                'activities' => $allActivities,
                'user_name' => $first->user?->name,
            ];
        })->values();
    }

    private function aggregateMonthly(Collection $entries): Collection
    {
        return $entries->groupBy(function ($entry) {
            $date = Carbon::parse($entry->entry_date);
            return $entry->website_client_id . '|' . $date->format('Y-m');
        })->map(function ($group) {
            $first = $group->first();
            $allActivities = $group->pluck('activities')->flatten(1)->values()->toArray();

            return [
                'website_client_id' => $first->website_client_id,
                'website_name' => $first->websiteClient?->name,
                'entry_date' => Carbon::parse($group->min('entry_date'))->startOfMonth()->toDateString() . ' s/d ' . Carbon::parse($group->min('entry_date'))->endOfMonth()->toDateString(),
                'activities' => $allActivities,
                'user_name' => $first->user?->name,
            ];
        })->values();
    }

    private function calculateStats(Collection $entries): array
    {
        $allActivities = $entries->pluck('activities')->flatten(1);

        return [
            'total_entries' => $entries->count(),
            'total_activities' => $allActivities->count(),
            'wp_updates' => $allActivities->where('type', ActivityType::WP_UPDATE->value)->count(),
            'plugin_updates' => $allActivities->where('type', ActivityType::PLUGIN_UPDATE->value)->count(),
            'theme_updates' => $allActivities->where('type', ActivityType::THEME_UPDATE->value)->count(),
            'articles' => $allActivities->where('type', ActivityType::ARTICLE->value)->count(),
            'page_optimizations' => $allActivities->where('type', ActivityType::PAGE_OPTIMIZATION->value)->count(),
            'others' => $allActivities->where('type', ActivityType::OTHER->value)->count(),
        ];
    }

    private function formatEntries(Collection $entries): array
    {
        return $entries->map(function ($entry) {
            // If entry is a model, format it directly
            if ($entry instanceof JournalEntry) {
                return [
                    'id' => $entry->id,
                    'website_name' => $entry->websiteClient?->name,
                    'entry_date' => $entry->entry_date instanceof \Carbon\Carbon
                        ? $entry->entry_date->toDateString()
                        : $entry->entry_date,
                    'activities' => $this->enrichActivities($entry->activities ?? []),
                    'summary' => $entry->summary,
                    'user_name' => $entry->user?->name,
                ];
            }

            // Already aggregated
            return [
                'website_name' => $entry['website_name'] ?? '',
                'entry_date' => $entry['entry_date'] ?? '',
                'activities' => $this->enrichActivities($entry['activities'] ?? []),
                'user_name' => $entry['user_name'] ?? '',
            ];
        })->toArray();
    }

    private function enrichActivities(array $activities): array
    {
        return array_map(function ($activity) {
            $type = $activity['type'] ?? '';
            $label = ActivityType::tryFrom($type)?->label() ?? $type;

            return array_merge($activity, ['type_label' => $label]);
        }, $activities);
    }

    public function formatActivityDetail(array $activity): string
    {
        $type = $activity['type'] ?? '';

        return match ($type) {
            ActivityType::WP_UPDATE->value => sprintf(
                'WP %s → %s%s',
                $activity['from_version'] ?? '-',
                $activity['to_version'] ?? '-',
                !empty($activity['note']) ? ' (' . $activity['note'] . ')' : ''
            ),
            ActivityType::PLUGIN_UPDATE->value => sprintf(
                '%s: %s → %s',
                $activity['plugin'] ?? '-',
                $activity['from_version'] ?? '-',
                $activity['to_version'] ?? '-'
            ),
            ActivityType::THEME_UPDATE->value => sprintf(
                '%s: %s → %s',
                $activity['theme'] ?? '-',
                $activity['from_version'] ?? '-',
                $activity['to_version'] ?? '-'
            ),
            ActivityType::ARTICLE->value => sprintf(
                '%s (%s kata)',
                $activity['title'] ?? '-',
                $activity['word_count'] ?? 0
            ),
            ActivityType::PAGE_OPTIMIZATION->value => sprintf(
                '%s: %s',
                $activity['page'] ?? '-',
                $activity['detail'] ?? ''
            ),
            default => $activity['description'] ?? '-',
        };
    }
}
