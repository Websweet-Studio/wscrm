<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class DemoWebsite extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'url',
        'demo_category_id',
        'featured_image',
        'description',
        'is_active',
        'sort_order',
        'category',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'sort_order' => 'integer',
            'demo_category_id' => 'integer',
        ];
    }

    public function demoCategory(): BelongsTo
    {
        return $this->belongsTo(DemoCategory::class);
    }

    public function demoPackages(): BelongsToMany
    {
        return $this->belongsToMany(DemoPackage::class, 'demo_package_demo_website');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('created_at', 'desc');
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    public function getFeaturedImageUrlAttribute()
    {
        if ($this->featured_image) {
            if (filter_var($this->featured_image, FILTER_VALIDATE_URL)) {
                return $this->featured_image;
            }

            return asset('storage/' . $this->featured_image);
        }

        return null;
    }
}