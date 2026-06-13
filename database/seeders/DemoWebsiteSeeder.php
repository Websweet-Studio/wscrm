<?php

namespace Database\Seeders;

use App\Models\DemoWebsite;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;

class DemoWebsiteSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Fetching demo websites from external API...');

        $response = Http::get('https://my.websweetstudio.com/wp-json/wp/v2/portofolio?access_key=hgdfas56t89df56ds4534yh5g435hyg');

        if (! $response->successful()) {
            $this->command->error('Failed to fetch demo websites from external API.');

            return;
        }

        $portfolios = $response->json();

        if (empty($portfolios)) {
            $this->command->warn('No demo websites found from external API.');

            return;
        }

        $count = 0;
        foreach ($portfolios as $portfolio) {
            DemoWebsite::updateOrCreate(
                ['url' => $portfolio['url_live_preview'] ?? ''],
                [
                    'title' => $portfolio['title'] ?? 'Untitled',
                    'url' => $portfolio['url_live_preview'] ?? '',
                    'category' => $portfolio['jenis'] ?? 'uncategorized',
                    'package' => null,
                    'featured_image' => $portfolio['screenshot'] ?? null,
                    'description' => strip_tags($portfolio['content'] ?? ''),
                    'is_active' => true,
                    'sort_order' => $count,
                ]
            );
            $count++;
        }

        $this->command->info("Seeded {$count} demo websites from external API.");
    }
}