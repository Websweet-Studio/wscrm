<?php

namespace App\Enums;

enum ActivityType: string
{
    case WP_UPDATE = 'wp_update';
    case PLUGIN_UPDATE = 'plugin_update';
    case THEME_UPDATE = 'theme_update';
    case ARTICLE = 'article';
    case PAGE_OPTIMIZATION = 'page_optimization';
    case PLUGIN_REMOVE = 'plugin_remove';
    case OTHER = 'other';

    public function label(): string
    {
        return match ($this) {
            self::WP_UPDATE => 'WP Update',
            self::PLUGIN_UPDATE => 'Update Plugin',
            self::THEME_UPDATE => 'Update Tema',
            self::ARTICLE => 'Artikel',
            self::PAGE_OPTIMIZATION => 'Optimasi Halaman',
            self::PLUGIN_REMOVE => 'Hapus Plugin',
            self::OTHER => 'Lainnya',
        };
    }

    public static function options(): array
    {
        return array_map(fn ($case) => [
            'value' => $case->value,
            'label' => $case->label(),
        ], self::cases());
    }
}
