<?php

namespace Database\Seeders;

use App\Models\ServicePlan;
use Illuminate\Database\Seeder;

class ServicePlanSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $servicePlans = [
            // Web Packages
            [
                'name' => 'Paket UMKM',
                'category' => 'web_package',
                'description' => 'Paket website untuk UMKM dengan fitur lengkap',
                'price' => 125000.00,
                'features' => [
                    'responsive_design' => true,
                    'seo_optimized' => true,
                    'contact_form' => true,
                    'admin_panel' => true,
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Paket Instant',
                'category' => 'web_package',
                'description' => 'Paket website instant dengan setup cepat',
                'price' => 200000.00,
                'features' => [
                    'instant_setup' => true,
                    'pre_built_templates' => true,
                    'responsive_design' => true,
                    'basic_seo' => true,
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Paket Custom Desain',
                'category' => 'web_package',
                'description' => 'Paket website dengan desain custom sesuai kebutuhan',
                'price' => 350000.00,
                'features' => [
                    'custom_design' => true,
                    'unlimited_revisions' => true,
                    'responsive_design' => true,
                    'advanced_seo' => true,
                    'admin_panel' => true,
                ],
                'is_active' => true,
            ],

            // Add-ons
            [
                'name' => 'Tambah Toko Online',
                'category' => 'addon',
                'description' => 'Menambahkan fitur toko online ke website existing',
                'price' => 50000.00,
                'features' => [
                    'shopping_cart' => true,
                    'payment_integration' => true,
                    'inventory_management' => true,
                    'order_tracking' => true,
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Paket Custom Sistem',
                'category' => 'custom_system',
                'description' => 'Pengembangan sistem custom sesuai kebutuhan khusus',
                'price' => 0.00, // Price on request
                'features' => [
                    'custom_development' => true,
                    'consultation' => true,
                    'maintenance' => true,
                    'documentation' => true,
                ],
                'is_active' => true,
            ],

            // Licenses
            [
                'name' => 'Lisensi Elementor, WP Rocket',
                'category' => 'license',
                'description' => 'Lisensi premium untuk Elementor Pro dan WP Rocket',
                'price' => 100000.00,
                'features' => [
                    'elementor_pro' => true,
                    'wp_rocket' => true,
                    '1_year_license' => true,
                    'support_included' => true,
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Lisensi Elementor + Crocoblock',
                'category' => 'license',
                'description' => 'Lisensi premium untuk Elementor Pro dan Crocoblock suite',
                'price' => 200000.00,
                'features' => [
                    'elementor_pro' => true,
                    'crocoblock_suite' => true,
                    'jetengine' => true,
                    'jetwoobuilder' => true,
                    '1_year_license' => true,
                    'support_included' => true,
                ],
                'is_active' => true,
            ],
            [
                'name' => 'Plugin GPL',
                'category' => 'license',
                'description' => 'Plugin GPL dengan lisensi resmi',
                'price' => 25000.00,
                'features' => [
                    'gpl_license' => true,
                    'regular_updates' => true,
                    'basic_support' => true,
                ],
                'is_active' => true,
            ],
        ];

        foreach ($servicePlans as $plan) {
            ServicePlan::updateOrCreate(
                ['name' => $plan['name']],
                $plan
            );
        }
    }
}
