<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Cermin domain dari API RDASH (read-only terhadap RDash, sumber kebenaran = registrar).
 */
class RdashDomain extends Model
{
    protected $fillable = [
        'rdash_id',
        'name',
        'status',
        'status_label',
        'status_reason',
        'verification_status',
        'verification_status_label',
        'is_premium',
        'is_locked',
        'is_registrar_locked',
        'nameserver_1',
        'nameserver_2',
        'nameserver_3',
        'nameserver_4',
        'nameserver_5',
        'notes',
        'expired_at',
        'rdash_created_at',
        'rdash_customer_id',
        'order_id',
        'order_item_id',
        'order_expires_at',
        'match_status',
        'drift_days',
        'order_matches',
        'last_synced_at',
        'payload',
    ];

    protected function casts(): array
    {
        return [
            'status' => 'integer',
            'verification_status' => 'integer',
            'is_premium' => 'boolean',
            'is_locked' => 'boolean',
            'is_registrar_locked' => 'boolean',
            'expired_at' => 'date',
            'rdash_created_at' => 'datetime',
            'order_expires_at' => 'date',
            'drift_days' => 'integer',
            'last_synced_at' => 'datetime',
            'payload' => 'array',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function orderItem(): BelongsTo
    {
        return $this->belongsTo(OrderItem::class, 'order_item_id');
    }

    /** Domain yang tanggalnya beda dengan order WSCRM. */
    public function scopeDrift($query)
    {
        return $query->where('match_status', 'drift');
    }

    /** Domain di RDash yang belum ada order-nya di WSCRM. */
    public function scopeUnmatched($query)
    {
        return $query->where('match_status', 'unmatched');
    }

    /** Normalisasi nama domain untuk pencocokan. */
    public static function normalize(string $domain): string
    {
        $domain = strtolower(trim($domain));
        $domain = preg_replace('#^https?://#', '', $domain) ?? $domain;
        $domain = preg_replace('#^www\.#', '', $domain) ?? $domain;
        $domain = rtrim(explode('/', $domain)[0], '.');

        return $domain;
    }
}
