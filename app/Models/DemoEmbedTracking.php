<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DemoEmbedTracking extends Model
{
    protected $fillable = [
        'referer_url',
        'referer_host',
        'embed_type',
        'demo_website_id',
        'hits',
        'first_seen_at',
        'last_seen_at',
    ];

    protected function casts(): array
    {
        return [
            'first_seen_at' => 'datetime',
            'last_seen_at' => 'datetime',
        ];
    }

    public function demoWebsite(): BelongsTo
    {
        return $this->belongsTo(DemoWebsite::class);
    }

    public static function recordHit(string $referer, string $type, ?int $demoId = null): void
    {
        $host = parse_url($referer, PHP_URL_HOST);
        $referer = rtrim($referer, '/');

        $tracking = self::where('referer_url', $referer)
            ->where('embed_type', $type)
            ->when($demoId, fn($q) => $q->where('demo_website_id', $demoId))
            ->when(!$demoId, fn($q) => $q->whereNull('demo_website_id'))
            ->first();

        if ($tracking) {
            $tracking->increment('hits');
            $tracking->update(['last_seen_at' => now()]);
        } else {
            self::create([
                'referer_url' => $referer,
                'referer_host' => $host,
                'embed_type' => $type,
                'demo_website_id' => $demoId,
                'hits' => 1,
                'first_seen_at' => now(),
                'last_seen_at' => now(),
            ]);
        }
    }
}
