<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DirectAdminSetting extends Model
{
    protected $table = 'directadmin_settings';

    protected $fillable = [
        'key',
        'value',
    ];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $setting = static::where('key', $key)->first();

        return $setting ? $setting->value : $default;
    }

    public static function setValue(string $key, string|int|bool|null $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => (string) $value]
        );
    }

    public static function allSettings(): array
    {
        return static::query()->pluck('value', 'key')->all();
    }

    /**
     * Simpan nilai rahasia (enkripsi at-rest) — utk password/credential.
     */
    public static function setSecret(string $key, string $value): void
    {
        static::setValue($key, \Illuminate\Support\Facades\Crypt::encryptString($value));
    }

    /**
     * Baca nilai rahasia; fallback ke plaintext bila data lama belum terenkripsi.
     */
    public static function getSecret(string $key, ?string $default = null): ?string
    {
        $value = static::getValue($key);
        if ($value === null || $value === '') {
            return $default;
        }

        try {
            return \Illuminate\Support\Facades\Crypt::decryptString($value);
        } catch (\Throwable) {
            return $value;
        }
    }
}
