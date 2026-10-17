<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu baris = satu pengajuan whitelist sementara oleh pelanggan dari /customer/ip-block.
 *
 * @property string $ip
 * @property string $status  diterima | gagal
 */
class ShieldWhitelistRequest extends Model
{
    protected $fillable = [
        'customer_id',
        'ip',
        'domain',
        'reason',
        'hours',
        'status',
        'note',
        'server',
        'expires_at',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'datetime',
            'hours' => 'integer',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }
}
