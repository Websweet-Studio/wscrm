<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'service_type',
        'plan_id',
        'pending_plan_id',
        'domain_name',
        'total_amount',
        'discount_amount',
        'status',
        'change_status',
        'change_requested_at',
        'billing_cycle',
        'expires_at',
        'auto_renew',
        'next_billing_date',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'total_amount' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'expires_at' => 'date',
            'next_billing_date' => 'date',
            'change_requested_at' => 'datetime',
            'auto_renew' => 'boolean',
            'metadata' => 'array',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function hostingPlan(): BelongsTo
    {
        return $this->belongsTo(HostingPlan::class, 'plan_id');
    }

    public function pendingPlan(): BelongsTo
    {
        return $this->belongsTo(HostingPlan::class, 'pending_plan_id');
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function tasks(): HasMany
    {
        return $this->hasMany(Task::class);
    }

    // Scopes for different use cases
    public function scopeOrders($query)
    {
        return $query->whereIn('orders.status', ['pending', 'processing', 'cancelled']);
    }

    public function scopeServices($query)
    {
        return $query->whereIn('orders.status', ['active', 'suspended', 'expired', 'terminated']);
    }

    public function scopeActive($query)
    {
        return $query->where('orders.status', 'active');
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->where('orders.expires_at', '<=', Carbon::now()->addDays($days))
            ->where('orders.status', 'active');
    }

    public function scopeByStatus($query, string $status)
    {
        return $query->where('orders.status', $status);
    }

    public function scopeByCustomer($query, int $customerId)
    {
        return $query->where('orders.customer_id', $customerId);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('orders.service_type', $type);
    }

    // Helper methods
    public function isOrder(): bool
    {
        return in_array($this->status, ['pending', 'processing', 'cancelled']);
    }

    public function isService(): bool
    {
        return in_array($this->status, ['active', 'suspended', 'expired', 'terminated']);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function daysUntilExpiry(): int
    {
        if (! $this->expires_at) {
            return 0;
        }

        // Carbon 3: diffInDays() mengembalikan float BERTANDA (bukan absolut seperti Carbon 2),
        // jadi selisih ke depan dihitung eksplisit agar tidak menghasilkan angka negatif.
        $expiry = $this->expires_at->copy()->startOfDay();
        $today = Carbon::now()->startOfDay();

        return $expiry->greaterThanOrEqualTo($today)
            ? (int) $today->diffInDays($expiry)
            : -(int) $expiry->diffInDays($today);
    }

    public function isRecurring(): bool
    {
        return $this->billing_cycle !== 'onetime';
    }

    /**
     * Jumlah bulan per siklus tagihan. Dipakai untuk perpanjangan:
     * masa aktif bertambah sesuai siklus yang ditagih (bukan selalu 12 bulan).
     */
    public const BILLING_CYCLE_MONTHS = [
        'monthly' => 1,
        'quarterly' => 3,
        'semi_annually' => 6,
        'semi_annual' => 6,
        'annual' => 12,
        'annually' => 12,
        'yearly' => 12,
    ];

    /**
     * Diskon loyalitas untuk layanan yang sudah aktif >= 12 bulan.
     *
     * Dibiarkan 0.0 (nonaktif) sampai keputusan harga dari pemilik: mengaktifkannya
     * menurunkan tagihan perpanjangan semua klien lama. Ubah ke 0.05 untuk menyalakan.
     */
    public const LOYALTY_DISCOUNT_RATE = 0.0;

    public const LOYALTY_DISCOUNT_MIN_MONTHS = 12;

    public function billingCycleMonths(?string $cycle = null): int
    {
        $cycle = $cycle ?? $this->billing_cycle;

        return self::BILLING_CYCLE_MONTHS[$cycle ?? ''] ?? 0;
    }

    /**
     * Umur layanan dalam bulan (selalu positif — Carbon 3 diffInMonths() bertanda).
     */
    public function serviceAgeMonths(): int
    {
        if (! $this->created_at) {
            return 0;
        }

        return (int) abs(Carbon::parse($this->created_at)->diffInMonths(Carbon::now()));
    }

    /**
     * Nilai diskon loyalitas dari sebuah basis net (0 bila fitur nonaktif).
     */
    public function loyaltyDiscount(float $netBase): float
    {
        $rate = (float) self::LOYALTY_DISCOUNT_RATE;

        if ($rate <= 0 || $this->serviceAgeMonths() < self::LOYALTY_DISCOUNT_MIN_MONTHS) {
            return 0.0;
        }

        return round($netBase * $rate, 2);
    }

    /**
     * Titik awal perpanjangan: jatuh tempo berjalan bila masih di depan,
     * kalau sudah lewat pakai hari ini (masa aktif tidak boleh menyusut).
     */
    public function renewalBaseDate(): Carbon
    {
        $now = Carbon::now()->startOfDay();

        if ($this->expires_at && $this->expires_at->copy()->startOfDay()->greaterThan($now)) {
            return $this->expires_at->copy()->startOfDay();
        }

        return $now;
    }

    /**
     * Tanggal jatuh tempo berikutnya menurut siklus tagihan.
     */
    public function nextExpiryFor(?int $months = null): ?Carbon
    {
        $months = $months ?? $this->billingCycleMonths();

        if ($months <= 0) {
            return null;
        }

        return $this->renewalBaseDate()->copy()->addMonths($months);
    }

    // Upgrade/Downgrade helpers
    public function hasPendingChange(): bool
    {
        return $this->change_status === 'pending' && $this->pending_plan_id !== null;
    }

    public function getRemainingDays(): int
    {
        if (! $this->expires_at) {
            return 0;
        }

        return max(0, Carbon::now()->diffInDays($this->expires_at, false));
    }

    public function getTotalBillingDays(): int
    {
        return match ($this->billing_cycle) {
            'monthly' => 30,
            'quarterly' => 90,
            'semi_annually' => 180,
            'annually' => 365,
            default => 30
        };
    }

    public function calculateProRatedAmount(float $planPrice): float
    {
        $remainingDays = $this->getRemainingDays();
        $totalDays = $this->getTotalBillingDays();

        if ($totalDays <= 0) {
            return 0;
        }

        return ($remainingDays / $totalDays) * $planPrice;
    }
}
