<?php

namespace Database\Seeders;

use App\Models\DemoCategory;
use App\Models\DemoPackage;
use App\Models\DemoWebsite;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class DemoWebsiteSeeder extends Seeder
{
    public function run(): void
    {
        $this->command->info('Fetching demo websites from external API...');

        $response = Http::get('https://my.websweetstudio.com/wp-json/wp/v2/portofolio?access_key=hgdfas56t89df56ds4534yh5g435hyg');

        if (!$response->successful()) {
            $this->command->error('Failed to fetch demo websites from external API.');

            return;
        }

        $portfolios = $response->json();

        if (empty($portfolios)) {
            $this->command->warn('No demo websites found from external API.');

            return;
        }

        // Collect unique categories from API data
        $categoriesFromApi = collect($portfolios)
            ->pluck('jenis')
            ->filter()
            ->unique()
            ->values()
            ->toArray();

        // Create categories
        $categoryMap = [];
        foreach ($categoriesFromApi as $jenis) {
            $category = DemoCategory::updateOrCreate(
                ['slug' => Str::slug($jenis)],
                [
                    'name' => ucfirst($jenis),
                    'is_active' => true,
                    'sort_order' => 0,
                ]
            );
            $categoryMap[$jenis] = $category->id;
        }

        // Create packages: Instant, Toko Online, UMKM
        $defaultPackages = [
            ['name' => 'Instant', 'slug' => 'instant', 'description' => 'Paket instant untuk website cepat dan ringan'],
            ['name' => 'Toko Online', 'slug' => 'toko-online', 'description' => 'Paket toko online untuk bisnis e-commerce'],
            ['name' => 'UMKM', 'slug' => 'umkm', 'description' => 'Paket UMKM untuk usaha kecil dan menengah'],
        ];

        $packageMap = [];
        foreach ($defaultPackages as $pkg) {
            $package = DemoPackage::updateOrCreate(
                ['slug' => $pkg['slug']],
                [
                    'name' => $pkg['name'],
                    'description' => $pkg['description'],
                    'is_active' => true,
                    'sort_order' => 0,
                ]
            );
            $packageMap[$pkg['slug']] = $package->id;
        }

        $count = 0;
        foreach ($portfolios as $portfolio) {
            $demoWebsite = DemoWebsite::updateOrCreate(
                ['url' => $portfolio['url_live_preview'] ?? ''],
                [
                    'title' => $portfolio['title'] ?? 'Untitled',
                    'url' => $portfolio['url_live_preview'] ?? '',
                    'demo_category_id' => $categoryMap[$portfolio['jenis']] ?? null,
                    'category' => $portfolio['jenis'] ?? '',
                    'featured_image' => $portfolio['screenshot'] ?? null,
                    'description' => isset($portfolio['content']) ? strip_tags($portfolio['content']) : null,
                    'is_active' => true,
                    'sort_order' => $count,
                ]
            );

            // Default: assign all demos to "Instant" package
            $demoWebsite->demoPackages()->syncWithoutDetaching([$packageMap['instant']]);

            $count++;
        }

        $this->command->info("Seeded {$count} demo websites, " . count($categoryMap) . " categories, " . count($packageMap) . " packages from external API.");
    }
}