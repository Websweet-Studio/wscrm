<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HostingPlan extends Model
{
    use HasFactory;

    protected $fillable = [
        'plan_name',
        'service_type',
        'billing_period',
        'storage_gb',
        'cpu_cores',
        'ram_gb',
        'bandwidth',
        'modal_cost',
        'maintenance_cost',
        'discount_percent',
        'selling_price',
        'features',
        'is_active',
        'base_price_per_gb',
        'plan_multiplier',
        'cost_per_gb',
        'use_bulk_pricing',
    ];

    protected function casts(): array
    {
        return [
            'storage_gb' => 'decimal:2',
            'cpu_cores' => 'decimal:2',
            'ram_gb' => 'decimal:2',
            'modal_cost' => 'decimal:2',
            'maintenance_cost' => 'decimal:2',
            'selling_price' => 'decimal:2',
            'features' => 'array',
            'is_active' => 'boolean',
            'base_price_per_gb' => 'decimal:2',
            'plan_multiplier' => 'decimal:2',
            'cost_per_gb' => 'decimal:2',
            'use_bulk_pricing' => 'boolean',
        ];
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class, 'item_id')->where('item_type', 'hosting');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeHosting($query)
    {
        return $query->where('service_type', 'hosting');
    }

    public function scopeVps($query)
    {
        return $query->where('service_type', 'vps');
    }

    public function scopeByPlan($query, string $planName)
    {
        return $query->where('plan_name', $planName);
    }

    public function finalPrice(): float
    {
        $base = (float) $this->selling_price;

        if ((bool) $this->use_bulk_pricing) {
            return $base;
        }

        $discount = (float) $this->discount_percent;
        if ($discount <= 0) {
            return $base;
        }

        return $base * (1 - ($discount / 100));
    }

    /** Jumlah bulan untuk setiap periode tagihan. */
    public const PERIOD_MONTHS = [
        'monthly' => 1,
        'quarterly' => 3,
        'semi_annually' => 6,
        'annually' => 12,
    ];

    /** Lama periode tagihan paket ini dalam bulan (default: tahunan). */
    public function billingPeriodMonths(): int
    {
        return self::PERIOD_MONTHS[$this->billing_period ?? ''] ?? 12;
    }

    /**
     * Harga jual paket untuk satu siklus tagihan tertentu.
     *
     * Paket bisa dijual per BULAN (VPS: `billing_period = monthly`) atau per TAHUN
     * (shared hosting), jadi basis perhitungan diambil dari `billing_period` paket —
     * bukan diasumsikan tahunan. Contoh: VPS CE 3 (Rp290.000/bulan) siklus 6 bulan
     * = 290.000 × 6 = Rp1.740.000, siklus 1 tahun = Rp3.480.000.
     */
    public function priceForCycle(?string $cycle): float
    {
        $baseMonths = $this->billingPeriodMonths();
        $cycleMonths = self::PERIOD_MONTHS[$cycle ?? ''] ?? $baseMonths;

        return round($this->finalPrice() * ($cycleMonths / $baseMonths), 2);
    }
}
