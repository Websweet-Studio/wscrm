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
}
