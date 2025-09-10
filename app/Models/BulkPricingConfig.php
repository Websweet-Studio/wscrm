<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BulkPricingConfig extends Model
{
    protected $fillable = [
        'name',
        'description',
        'base_price_per_gb',
        'cost_per_gb',
        'plan_multipliers',
        'tier_discounts',
        'is_active',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'base_price_per_gb' => 'decimal:2',
            'cost_per_gb' => 'decimal:2',
            'plan_multipliers' => 'array',
            'tier_discounts' => 'array',
            'is_active' => 'boolean',
            'is_default' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeDefault($query)
    {
        return $query->where('is_default', true);
    }

    public static function getDefaultConfig(): array
    {
        $defaultConfig = self::default()->first();
        
        if ($defaultConfig) {
            return [
                'base_price_per_gb' => $defaultConfig->base_price_per_gb,
                'cost_per_gb' => $defaultConfig->cost_per_gb,
                'plan_multipliers' => $defaultConfig->plan_multipliers,
                'tier_discounts' => $defaultConfig->tier_discounts,
            ];
        }

        // Fallback to hardcoded default
        return [
            'base_price_per_gb' => 150000,
            'cost_per_gb' => 112500,
            'plan_multipliers' => [
                'basic' => 1.0,
                'lite' => 0.77,
                'premium' => 1.3,
            ],
            'tier_discounts' => [
                ['storage_gb' => 1, 'discount_percentage' => 0.00],
                ['storage_gb' => 3, 'discount_percentage' => 3.00],
                ['storage_gb' => 5, 'discount_percentage' => 7.00],
                ['storage_gb' => 10, 'discount_percentage' => 12.00],
                ['storage_gb' => 20, 'discount_percentage' => 20.00],
                ['storage_gb' => 50, 'discount_percentage' => 30.00],
                ['storage_gb' => 100, 'discount_percentage' => 40.00],
                ['storage_gb' => 200, 'discount_percentage' => 45.00],
            ],
        ];
    }
}
