<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class BrandingSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'key' => 'app_name',
                'value' => 'WSCRM',
                'type' => 'text',
                'description' => 'Nama aplikasi yang ditampilkan di seluruh sistem',
                'is_active' => true,
            ],
            [
                'key' => 'app_logo',
                'value' => null,
                'type' => 'image',
                'description' => 'Logo utama aplikasi (format: PNG, JPG, SVG)',
                'is_active' => true,
            ],
            [
                'key' => 'app_logo_dark',
                'value' => null,
                'type' => 'image',
                'description' => 'Logo untuk mode gelap (format: PNG, JPG, SVG)',
                'is_active' => true,
            ],
            [
                'key' => 'app_favicon',
                'value' => null,
                'type' => 'image',
                'description' => 'Favicon aplikasi (format: ICO, PNG)',
                'is_active' => true,
            ],
            [
                'key' => 'app_slogan',
                'value' => 'Solusi Web Hosting Terpercaya',
                'type' => 'text',
                'description' => 'Slogan atau tagline perusahaan',
                'is_active' => true,
            ],
            [
                'key' => 'primary_color',
                'value' => '#3b82f6',
                'type' => 'color',
                'description' => 'Warna utama aplikasi',
                'is_active' => true,
            ],
            [
                'key' => 'secondary_color',
                'value' => '#64748b',
                'type' => 'color',
                'description' => 'Warna sekunder aplikasi',
                'is_active' => true,
            ],
            [
                'key' => 'accent_color',
                'value' => '#10b981',
                'type' => 'color',
                'description' => 'Warna aksen untuk tombol dan elemen penting',
                'is_active' => true,
            ],
            [
                'key' => 'footer_text',
                'value' => '© 2024 WSCRM. Semua hak dilindungi.',
                'type' => 'text',
                'description' => 'Teks copyright di footer',
                'is_active' => true,
            ],
            [
                'key' => 'company_address',
                'value' => null,
                'type' => 'textarea',
                'description' => 'Alamat lengkap perusahaan',
                'is_active' => true,
            ],
            [
                'key' => 'company_phone',
                'value' => null,
                'type' => 'text',
                'description' => 'Nomor telepon perusahaan',
                'is_active' => true,
            ],
            [
                'key' => 'company_email',
                'value' => null,
                'type' => 'text',
                'description' => 'Email perusahaan untuk kontak',
                'is_active' => true,
            ],
            [
                'key' => 'company_whatsapp',
                'value' => null,
                'type' => 'text',
                'description' => 'Nomor WhatsApp perusahaan (contoh: 6281234567890)',
                'is_active' => true,
            ],
        ];

        foreach ($settings as $setting) {
            DB::table('branding_settings')->updateOrInsert(
                ['key' => $setting['key']],
                array_merge($setting, [
                    'created_at' => now(),
                    'updated_at' => now(),
                ])
            );
        }
    }
}
