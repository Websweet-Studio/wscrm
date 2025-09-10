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

    public function services(): HasMany
    {
        return $this->hasMany(Service::class, 'plan_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByPlan($query, string $planName)
    {
        return $query->where('plan_name', $planName);
    }
}
