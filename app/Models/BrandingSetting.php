<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BrandingSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->where('is_active', true)->first();

        return $setting ? $setting->value : $default;
    }

    public static function setValue(string $key, mixed $value): bool
    {
        return static::updateOrCreate(
            ['key' => $key],
            ['value' => $value]
        )->wasRecentlyCreated || true;
    }

    public static function getByType(string $type): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('type', $type)->where('is_active', true)->get();
    }

    public static function getAllActive(): \Illuminate\Database\Eloquent\Collection
    {
        return static::where('is_active', true)->orderBy('key')->get();
    }

    public static function ensureDefaultsIfEmpty(): void
    {
        if (! \Schema::hasTable('branding_settings')) {
            return;
        }

        if (static::query()->exists()) {
            return;
        }

        $now = now();
        $rows = [
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

        $rows = array_map(function (array $row) use ($now) {
            $row['created_at'] = $now;
            $row['updated_at'] = $now;

            return $row;
        }, $rows);

        static::query()->insert($rows);
    }
}
