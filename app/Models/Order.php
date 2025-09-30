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
        return $this->expires_at ? $this->expires_at->diffInDays(Carbon::now()) : 0;
    }

    public function isRecurring(): bool
    {
        return $this->billing_cycle !== 'onetime';
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
