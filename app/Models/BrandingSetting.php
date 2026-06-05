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

    public static function ensurePaymentMethodsSettingExists(): void
    {
        if (! \Schema::hasTable('branding_settings')) {
            return;
        }

        $default = json_encode(static::defaultPaymentMethods(), JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        $setting = static::firstOrNew(['key' => 'payment_methods']);
        $setting->type = 'payment';
        $setting->description = $setting->description ?: 'Daftar metode pembayaran yang tersedia untuk customer saat membayar invoice';
        $setting->is_active = true;

        if ($setting->exists === false || $setting->value === null || trim((string) $setting->value) === '') {
            $setting->value = $default;
        }

        $setting->save();
    }

    public static function getPaymentMethods(): array
    {
        $default = static::defaultPaymentMethods();
        $raw = static::getValue('payment_methods', json_encode($default, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES));

        return static::normalizePaymentMethods($raw, $default);
    }

    public static function getActivePaymentMethods(): array
    {
        return array_values(array_filter(static::getPaymentMethods(), fn (array $method) => (bool) ($method['enabled'] ?? false)));
    }

    public static function normalizePaymentMethods(mixed $raw, ?array $fallback = null): array
    {
        $fallback = $fallback ?? static::defaultPaymentMethods();

        if (! is_string($raw) || trim($raw) === '') {
            return $fallback;
        }

        try {
            $decoded = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
        } catch (\Throwable) {
            return $fallback;
        }

        if (! is_array($decoded)) {
            return $fallback;
        }

        $normalized = [];

        foreach ($decoded as $row) {
            if (! is_array($row)) {
                continue;
            }

            $key = isset($row['key']) ? (string) $row['key'] : '';
            $key = trim($key);
            if ($key === '') {
                continue;
            }

            $label = isset($row['label']) ? (string) $row['label'] : '';
            $label = trim($label);
            if ($label === '') {
                $label = $key;
            }

            $description = isset($row['description']) ? (string) $row['description'] : '';
            $description = trim($description);

            $enabled = (bool) ($row['enabled'] ?? false);
            $sort = isset($row['sort']) ? (int) $row['sort'] : 0;

            $normalized[] = [
                'key' => $key,
                'label' => $label,
                'description' => $description,
                'enabled' => $enabled,
                'sort' => $sort,
            ];
        }

        if (count($normalized) === 0) {
            return $fallback;
        }

        usort($normalized, function (array $a, array $b) {
            $as = (int) ($a['sort'] ?? 0);
            $bs = (int) ($b['sort'] ?? 0);

            if ($as === $bs) {
                return strcmp((string) ($a['key'] ?? ''), (string) ($b['key'] ?? ''));
            }

            return $as <=> $bs;
        });

        return $normalized;
    }

    private static function defaultPaymentMethods(): array
    {
        return [
            [
                'key' => 'bank_transfer',
                'label' => 'Transfer Bank',
                'description' => 'ATM/Internet Banking',
                'enabled' => true,
                'sort' => 10,
            ],
            [
                'key' => 'credit_card',
                'label' => 'Kartu Kredit',
                'description' => 'Visa/Mastercard',
                'enabled' => false,
                'sort' => 20,
            ],
            [
                'key' => 'e_wallet',
                'label' => 'E-Wallet',
                'description' => 'GoPay/OVO/DANA',
                'enabled' => false,
                'sort' => 30,
            ],
        ];
    }
}
