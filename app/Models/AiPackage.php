<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AiPackage extends Model
{
    protected $fillable = ['name', 'credits', 'price', 'discount_amount', 'is_active', 'sort_order'];

    // Sertakan aksesor `final_price` di output JSON (Inertia) — tanpa ini undefined di frontend → RpNaN.
    protected $appends = ['final_price'];

    protected function casts(): array
    {
        return [
            'credits' => 'integer',
            'price' => 'decimal:2',
            'discount_amount' => 'decimal:2',
            'is_active' => 'boolean',
        ];
    }

    public function getFinalPriceAttribute(): float
    {
        return max(0, (float) $this->price - (float) ($this->discount_amount ?? 0));
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
