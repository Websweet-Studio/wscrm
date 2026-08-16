<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class AiSetting extends Model
{
    protected $table = 'ai_settings';

    private const CACHE_KEY = 'ai_settings.all';

    protected $fillable = ['key', 'value'];

    public static function getValue(string $key, mixed $default = null): mixed
    {
        $settings = static::allSettings();

        return array_key_exists($key, $settings) ? $settings[$key] : $default;
    }

    public static function setValue(string $key, mixed $value): void
    {
        static::updateOrCreate(
            ['key' => $key],
            ['value' => $value === null ? null : (string) $value]
        );

        Cache::forget(self::CACHE_KEY);
    }

    public static function allSettings(): array
    {
        return Cache::remember(self::CACHE_KEY, 300, fn () => static::query()->pluck('value', 'key')->all());
    }
}
