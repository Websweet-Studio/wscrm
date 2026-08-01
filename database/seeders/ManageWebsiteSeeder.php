<?php

namespace Database\Seeders;

use App\Enums\ActivityType;
use App\Models\Customer;
use App\Models\JournalEntry;
use App\Models\User;
use App\Models\WebsiteClient;
use Illuminate\Database\Seeder;

class ManageWebsiteSeeder extends Seeder
{
    public function run(): void
    {
        $customers = Customer::all();
        $users = User::all();

        $websiteData = [
            [
                'name' => 'Toko Bunga Online',
                'url' => 'https://tokobunga.example.com',
                'wp_username' => 'admin',
                'wp_app_password' => null,
                'wp_version' => '6.6',
                'theme_name' => 'Astra',
                'theme_version' => '4.7',
                'plugins' => [
                    ['name' => 'WooCommerce', 'version' => '9.1', 'active' => true, 'slug' => 'woocommerce'],
                    ['name' => 'Yoast SEO', 'version' => '22.8', 'active' => true, 'slug' => 'wordpress-seo'],
                    ['name' => 'Elementor', 'version' => '3.24', 'active' => true, 'slug' => 'elementor'],
                    ['name' => 'Contact Form 7', 'version' => '5.9', 'active' => true, 'slug' => 'contact-form-7'],
                    ['name' => 'Akismet', 'version' => '5.3', 'active' => false, 'slug' => 'akismet'],
                ],
                'notes' => 'Website e-commerce bunga, perlu update WooCommerce rutin.',
            ],
            [
                'name' => 'Klinik Sehat Sentosa',
                'url' => 'https://kliniksehat.example.com',
                'wp_username' => 'admin',
                'wp_app_password' => null,
                'wp_version' => '6.5.5',
                'theme_name' => 'Hello Elementor',
                'theme_version' => '3.0',
                'plugins' => [
                    ['name' => 'Elementor', 'version' => '3.22', 'active' => true, 'slug' => 'elementor'],
                    ['name' => 'Yoast SEO', 'version' => '22.5', 'active' => true, 'slug' => 'wordpress-seo'],
                    ['name' => 'WPForms', 'version' => '1.9', 'active' => true, 'slug' => 'wpforms-lite'],
                    ['name' => 'Flamingo', 'version' => '2.5', 'active' => true, 'slug' => 'flamingo'],
                ],
                'notes' => 'Perlu update WP ke 6.6 dan optimasi halaman booking.',
            ],
            [
                'name' => 'CV Maju Jaya Konstruksi',
                'url' => 'https://majujaya.example.com',
                'wp_username' => 'editor',
                'wp_app_password' => null,
                'wp_version' => '6.6',
                'theme_name' => 'GeneratePress',
                'theme_version' => '3.4',
                'plugins' => [
                    ['name' => 'Yoast SEO', 'version' => '22.8', 'active' => true, 'slug' => 'wordpress-seo'],
                    ['name' => 'WPForms', 'version' => '1.9', 'active' => true, 'slug' => 'wpforms-lite'],
                    ['name' => 'Smush', 'version' => '3.16', 'active' => true, 'slug' => 'wp-smushit'],
                    ['name' => 'GP Premium', 'version' => '2.4', 'active' => true, 'slug' => 'gp-premium'],
                ],
                'notes' => null,
            ],
            [
                'name' => 'Restoran Nusantara',
                'url' => 'https://restorannusantara.example.com',
                'wp_username' => 'admin',
                'wp_app_password' => null,
                'wp_version' => '6.6',
                'theme_name' => 'Divi',
                'theme_version' => '4.27',
                'plugins' => [
                    ['name' => 'WooCommerce', 'version' => '9.0', 'active' => true, 'slug' => 'woocommerce'],
                    ['name' => 'Rank Math SEO', 'version' => '1.0.220', 'active' => true, 'slug' => 'seo-by-rank-math'],
                    ['name' => 'W3 Total Cache', 'version' => '2.7', 'active' => true, 'slug' => 'w3-total-cache'],
                    ['name' => 'MonsterInsights', 'version' => '8.27', 'active' => true, 'slug' => 'google-analytics-for-wordpress'],
                ],
                'notes' => 'Artikel menu 3 hari sekali. Optimasi load speed.',
            ],
            [
                'name' => 'Sekolah Cerdas Bangsa',
                'url' => 'https://sekolahcerdas.example.com',
                'wp_username' => 'admin',
                'wp_app_password' => null,
                'wp_version' => '6.5.5',
                'theme_name' => 'Education Hub',
                'theme_version' => '2.8',
                'plugins' => [
                    ['name' => 'Elementor', 'version' => '3.23', 'active' => true, 'slug' => 'elementor'],
                    ['name' => 'LearnPress', 'version' => '4.2', 'active' => true, 'slug' => 'learnpress'],
                    ['name' => 'Yoast SEO', 'version' => '22.6', 'active' => true, 'slug' => 'wordpress-seo'],
                    ['name' => 'WP Super Cache', 'version' => '1.12', 'active' => true, 'slug' => 'wp-super-cache'],
                    ['name' => 'UpdraftPlus', 'version' => '1.24', 'active' => true, 'slug' => 'updraftplus'],
                ],
                'notes' => 'Perlu update WP dan plugin e-learning.',
            ],
            [
                'name' => 'Digital Agency Pro',
                'url' => 'https://digitalpro.example.com',
                'wp_username' => 'admin',
                'wp_app_password' => null,
                'wp_version' => '6.6',
                'theme_name' => 'Kadence',
                'theme_version' => '1.2',
                'plugins' => [
                    ['name' => 'Rank Math SEO', 'version' => '1.0.222', 'active' => true, 'slug' => 'seo-by-rank-math'],
                    ['name' => 'Elementor', 'version' => '3.24', 'active' => true, 'slug' => 'elementor'],
                    ['name' => 'Gravity Forms', 'version' => '2.8', 'active' => true, 'slug' => 'gravityforms'],
                    ['name' => 'Slider Revolution', 'version' => '6.7', 'active' => true, 'slug' => 'revslider'],
                    ['name' => 'WP Rocket', 'version' => '3.16', 'active' => true, 'slug' => 'wp-rocket'],
                    ['name' => 'Advanced Custom Fields', 'version' => '6.3', 'active' => true, 'slug' => 'advanced-custom-fields'],
                ],
                'notes' => 'Portfolio agency. Update rutin semua plugin premium.',
            ],
        ];

        $this->command->info('Seeding 6 website clients...');

        foreach ($websiteData as $i => $data) {
            if ($customers->isNotEmpty()) {
                $data['customer_id'] = $customers->random()->id;
            }

            WebsiteClient::create($data);
            $this->command->info("  ✓ {$data['name']}");
        }

        // Seed journal entries for the last 14 days
        $this->command->info('Seeding journal entries...');
        $websites = WebsiteClient::all();
        $createdCount = 0;

        foreach ($websites as $website) {
            for ($day = 14; $day >= 0; $day--) {
                // ~70% chance of having activity on a given day
                if (fake()->boolean(30)) {
                    continue;
                }

                $date = now()->subDays($day);
                $activities = $this->generateActivities($website, $date);
                $summary = fake()->boolean(30) ? fake()->sentence(8) : null;

                try {
                    JournalEntry::create([
                        'website_client_id' => $website->id,
                        'user_id' => $users->random()->id,
                        'entry_date' => $date,
                        'activities' => $activities,
                        'summary' => $summary,
                    ]);
                    $createdCount++;
                } catch (\Exception $e) {
                    // skip duplicate
                }
            }
        }

        $this->command->info("  ✓ {$createdCount} journal entries created");
        $this->command->info('Manage Website seeding complete!');
    }

    private function generateActivities(WebsiteClient $website, $date): array
    {
        $activities = [];
        $dayOfWeek = $date->dayOfWeek;

        // Weekend: fewer activities
        $maxActivities = in_array($dayOfWeek, [0, 6]) ? 1 : 3;
        $count = rand(1, $maxActivities);

        for ($i = 0; $i < $count; $i++) {
            $type = fake()->randomElement(ActivityType::cases());
            $activity = ['type' => $type->value];

            switch ($type) {
                case ActivityType::WP_UPDATE:
                    $activity['from_version'] = '6.5.5';
                    $activity['to_version'] = '6.6';
                    $activity['note'] = fake()->boolean(50) ? 'Update keamanan rutin' : null;
                    break;

                case ActivityType::PLUGIN_UPDATE:
                    $activity['plugin'] = fake()->randomElement([
                        'Yoast SEO', 'Elementor', 'WooCommerce', 'Contact Form 7',
                        'Rank Math SEO', 'WPForms', 'Smush', 'Akismet',
                    ]);
                    $activity['from_version'] = fake()->numerify('#.#');
                    $activity['to_version'] = fake()->numerify('#.#');
                    break;

                case ActivityType::THEME_UPDATE:
                    $activity['theme'] = $website->theme_name ?? fake()->randomElement(['Astra', 'Divi', 'GeneratePress', 'Kadence']);
                    $activity['from_version'] = fake()->numerify('#.#');
                    $activity['to_version'] = fake()->numerify('#.#');
                    break;

                case ActivityType::ARTICLE:
                    $activity['title'] = fake()->sentence(6);
                    $activity['url'] = rtrim($website->url, '/') . '/' . fake()->slug(3);
                    $activity['word_count'] = rand(400, 2000);
                    break;

                case ActivityType::PAGE_OPTIMIZATION:
                    $activity['page'] = fake()->randomElement([
                        'Landing Page', 'Homepage', 'Tentang Kami', 'Kontak',
                        'Produk', 'Blog', 'Layanan',
                    ]);
                    $activity['detail'] = fake()->randomElement([
                        'Optimasi SEO on-page',
                        'Perbaikan load speed & image compression',
                        'Update meta description dan title tag',
                        'Perbaikan broken links',
                        'Optimasi mobile responsiveness',
                    ]);
                    break;

                case ActivityType::OTHER:
                    $activity['description'] = fake()->randomElement([
                        'Backup database dan file',
                        'Pengecekan keamanan malware',
                        'Update SSL certificate',
                        'Perbaikan tampilan responsive',
                        'Membersihkan spam comments',
                        'Update informasi kontak & jam operasional',
                    ]);
                    break;
            }

            $activities[] = $activity;
        }

        return $activities;
    }
}
