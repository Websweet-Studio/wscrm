<?php

namespace Database\Seeders;

use App\Models\BlogCategory;
use Illuminate\Database\Seeder;

class BlogCategorySeeder extends Seeder
{
    public function run(): void
    {
        $categories = [
            [
                'name' => 'Teknologi',
                'slug' => 'teknologi',
                'description' => 'Artikel tentang teknologi terbaru dan perkembangan digital',
                'color' => '#3B82F6',
                'icon' => 'monitor',
                'is_active' => true,
                'sort_order' => 1,
            ],
            [
                'name' => 'Hosting & Domain',
                'slug' => 'hosting-domain',
                'description' => 'Panduan hosting, domain, dan layanan web',
                'color' => '#10B981',
                'icon' => 'server',
                'is_active' => true,
                'sort_order' => 2,
            ],
            [
                'name' => 'Tutorial',
                'slug' => 'tutorial',
                'description' => 'Tutorial langkah demi langkah untuk berbagai topik',
                'color' => '#8B5CF6',
                'icon' => 'book-open',
                'is_active' => true,
                'sort_order' => 3,
            ],
            [
                'name' => 'Pengumuman',
                'slug' => 'pengumuman',
                'description' => 'Pengumuman resmi dan informasi penting',
                'color' => '#F59E0B',
                'icon' => 'megaphone',
                'is_active' => true,
                'sort_order' => 4,
            ],
            [
                'name' => 'Tips & Trik',
                'slug' => 'tips-trik',
                'description' => 'Tips dan trik berguna seputar teknologi dan web',
                'color' => '#EF4444',
                'icon' => 'lightbulb',
                'is_active' => true,
                'sort_order' => 5,
            ],
            [
                'name' => 'Berita',
                'slug' => 'berita',
                'description' => 'Berita terkini seputar industri teknologi',
                'color' => '#06B6D4',
                'icon' => 'newspaper',
                'is_active' => true,
                'sort_order' => 6,
            ],
        ];

        foreach ($categories as $category) {
            BlogCategory::firstOrCreate(
                ['slug' => $category['slug']],
                $category
            );
        }
    }
}