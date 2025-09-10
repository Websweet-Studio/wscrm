<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PricingTier extends Model
{
    protected $fillable = [
        'storage_gb',
        'discount_percentage',
        'sort_order',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'storage_gb' => 'integer',
            'discount_percentage' => 'decimal:2',
            'sort_order' => 'integer',
            'is_active' => 'boolean',
        ];
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order');
    }

    public static function getDefaultTiers(): array
    {
        return [
            ['storage_gb' => 1, 'discount_percentage' => 0.00, 'sort_order' => 1],
            ['storage_gb' => 3, 'discount_percentage' => 3.00, 'sort_order' => 2],
            ['storage_gb' => 5, 'discount_percentage' => 7.00, 'sort_order' => 3],
            ['storage_gb' => 10, 'discount_percentage' => 12.00, 'sort_order' => 4],
            ['storage_gb' => 20, 'discount_percentage' => 20.00, 'sort_order' => 5],
            ['storage_gb' => 50, 'discount_percentage' => 30.00, 'sort_order' => 6],
            ['storage_gb' => 100, 'discount_percentage' => 40.00, 'sort_order' => 7],
            ['storage_gb' => 200, 'discount_percentage' => 45.00, 'sort_order' => 8],
        ];
    }
}
