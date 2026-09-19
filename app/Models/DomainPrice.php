<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Carbon;

class DomainPrice extends Model
{
    use HasFactory;

    protected $fillable = [
        'extension',
        'base_cost',
        'renewal_cost',
        'selling_price',
        'renewal_price_with_tax',
        'promo_price',
        'promo_base_cost',
        'promo_selling_price',
        'promo_starts_at',
        'promo_ends_at',
        'promo_note',
        'is_active',
    ];

    protected $appends = [
        'promo_active',
        'effective_selling_price',
        'promo_days_left',
        'promo_savings',
    ];

    protected function casts(): array
    {
        return [
            'base_cost' => 'decimal:2',
            'renewal_cost' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'renewal_price_with_tax' => 'decimal:2',
            'promo_price' => 'decimal:2',
            'promo_base_cost' => 'decimal:2',
            'promo_selling_price' => 'decimal:2',
            'promo_starts_at' => 'datetime',
            'promo_ends_at' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'item_id')->where('item_type', 'domain');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByExtension($query, string $extension)
    {
        return $query->where('extension', $extension);
    }

    /**
     * Punya data promo registrasi dari RDash (belum tentu sedang berlaku).
     */
    public function hasPromo(): bool
    {
        return $this->promo_selling_price !== null;
    }

    /**
     * Promo registrasi sedang berlaku pada tanggal tertentu (default: sekarang).
     */
    public function promoIsActive(?\DateTimeInterface $at = null): bool
    {
        if ($this->promo_selling_price === null) {
            return false;
        }

        $at = $at !== null ? Carbon::instance($at)->setTimezone('UTC') : now('UTC');

        $starts = $this->promo_starts_at !== null ? $this->promo_starts_at->copy()->setTimezone('UTC') : null;
        $ends = $this->promo_ends_at !== null ? $this->promo_ends_at->copy()->setTimezone('UTC') : null;

        if ($starts !== null && $at->lt($starts)) {
            return false;
        }

        if ($ends !== null && $at->gt($ends)) {
            return false;
        }

        return true;
    }

    /**
     * Harga jual yang berlaku sekarang: harga promo registrasi bila sedang aktif,
     * selain itu harga jual normal.
     */
    public function priceNow(?\DateTimeInterface $at = null): float
    {
        if ($this->promoIsActive($at)) {
            return (float) $this->promo_selling_price;
        }

        return (float) $this->selling_price;
    }

    /**
     * Modal yang berlaku sekarang (dipakai untuk menghitung margin berjalan).
     */
    public function costNow(?\DateTimeInterface $at = null): float
    {
        if ($this->promoIsActive($at)) {
            return (float) ($this->promo_base_cost ?? $this->base_cost);
        }

        return (float) $this->base_cost;
    }

    /**
     * Sisa hari promo (null bila tidak ada promo aktif / tanpa tanggal akhir).
     */
    public function promoDaysRemaining(): ?int
    {
        if (! $this->promoIsActive() || $this->promo_ends_at === null) {
            return null;
        }

        $todayEnd = now('UTC')->startOfDay();

        return max(0, (int) $todayEnd->diffInDays($this->promo_ends_at->copy()->setTimezone('UTC')->startOfDay(), false));
    }

    /**
     * Potongan untuk klien dibanding harga jual normal (null bila tidak ada promo aktif).
     */
    public function promoCutAmount(): ?float
    {
        if (! $this->promoIsActive()) {
            return null;
        }

        return max(0, (float) $this->selling_price - (float) $this->promo_selling_price);
    }

    public function scopeWithActivePromo($query)
    {
        $now = now('UTC')->toDateTimeString();

        return $query->whereNotNull('promo_selling_price')
            ->where(function ($q) use ($now) {
                $q->whereNull('promo_starts_at')->orWhere('promo_starts_at', '<=', $now);
            })
            ->where(function ($q) use ($now) {
                $q->whereNull('promo_ends_at')->orWhere('promo_ends_at', '>=', $now);
            });
    }

    protected function promoActive(): Attribute
    {
        return Attribute::make(get: fn () => $this->promoIsActive());
    }

    protected function effectiveSellingPrice(): Attribute
    {
        return Attribute::make(get: fn () => $this->priceNow());
    }

    protected function promoDaysLeft(): Attribute
    {
        return Attribute::make(get: fn () => $this->promoDaysRemaining());
    }

    protected function promoSavings(): Attribute
    {
        return Attribute::make(get: fn () => $this->promoCutAmount());
    }
}
