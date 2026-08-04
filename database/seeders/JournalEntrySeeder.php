<?php

namespace Database\Seeders;

use App\Enums\ActivityType;
use App\Models\JournalEntry;
use App\Models\WebsiteClient;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class JournalEntrySeeder extends Seeder
{
    public function run(): void
    {
        $clients = WebsiteClient::pluck('id')->toArray();

        if (empty($clients)) {
            $this->command?->warn('No website clients found. Skipping journal entry seeding.');
            return;
        }

        $activityTypes = ActivityType::cases();
        $activitiesPool = [
            ['type' => 'wp_update', 'description' => 'Update WordPress ke versi terbaru'],
            ['type' => 'plugin_update', 'description' => 'Update plugin Yoast SEO'],
            ['type' => 'plugin_update', 'description' => 'Update plugin WooCommerce'],
            ['type' => 'plugin_update', 'description' => 'Update plugin Elementor'],
            ['type' => 'theme_update', 'description' => 'Update tema ke versi terbaru'],
            ['type' => 'article', 'description' => 'Menulis artikel blog baru'],
            ['type' => 'article', 'description' => 'Optimasi artikel existing'],
            ['type' => 'page_optimization', 'description' => 'Optimasi kecepatan halaman'],
            ['type' => 'page_optimization', 'description' => 'Optimasi SEO on-page'],
            ['type' => 'other', 'description' => 'Backup website'],
            ['type' => 'other', 'description' => 'Update SSL certificate'],
            ['type' => 'other', 'description' => 'Monitoring uptime'],
        ];

        $summaries = [
            'Semua task selesai dikerjakan sesuai jadwal.',
            'Ada kendala saat update plugin, sudah resolved.',
            'Rutin maintenance mingguan.',
            'Optimasi berhasil, loading speed naik signifikan.',
            'Artikel baru sudah publish dan diindex Google.',
            null,
            null,
            null,
        ];

        $inserted = 0;
        $startDate = Carbon::now()->subDays(30);

        foreach ($clients as $clientId) {
            // Generate 2-4 entries per client across the last 30 days
            $entryCount = rand(2, 4);
            $usedDates = [];

            for ($i = 0; $i < $entryCount; $i++) {
                $entryDate = $startDate->copy()->addDays(rand(0, 30));
                $dateStr = $entryDate->toDateString();

                // Unique per client + date
                if (in_array($dateStr, $usedDates)) continue;
                $usedDates[] = $dateStr;

                // Generate 1-3 random activities
                $activityCount = rand(1, 3);
                $selectedActivities = [];
                $pickedKeys = array_rand($activitiesPool, min($activityCount, count($activitiesPool)));
                if (!is_array($pickedKeys)) $pickedKeys = [$pickedKeys];

                foreach ($pickedKeys as $key) {
                    $selectedActivities[] = $activitiesPool[$key];
                }

                try {
                    JournalEntry::create([
                        'website_client_id' => $clientId,
                        'user_id' => null,
                        'entry_date' => $dateStr,
                        'activities' => $selectedActivities,
                        'summary' => $summaries[array_rand($summaries)],
                    ]);
                    $inserted++;
                } catch (\Exception $e) {
                    // skip duplicate entry_date per client
                }
            }
        }

        $this->command?->info("Seeded {$inserted} journal entries for " . count($clients) . " website clients.");
    }
}
