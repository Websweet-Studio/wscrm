<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Service extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'service_type',
        'plan_id',
        'domain_name',
        'status',
        'expires_at',
        'auto_renew',
        'metadata',
    ];

    protected function casts(): array
    {
        return [
            'expires_at' => 'date',
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

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function scopeActive($query)
    {
        return $query->where('status', 'active');
    }

    public function scopeExpiringSoon($query, int $days = 30)
    {
        return $query->where('expires_at', '<=', Carbon::now()->addDays($days))
            ->where('status', 'active');
    }

    public function scopeByCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    public function scopeByType($query, string $type)
    {
        return $query->where('service_type', $type);
    }

    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    public function daysUntilExpiry(): int
    {
        return $this->expires_at->diffInDays(Carbon::now());
    }
}
