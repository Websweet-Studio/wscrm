<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use Carbon\Carbon;

class BlogPost extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'slug',
        'excerpt',
        'content',
        'featured_image',
        'meta_data',
        'blog_category_id',
        'user_id',
        'type',
        'status',
        'is_featured',
        'is_pinned',
        'allow_comments',
        'views_count',
        'likes_count',
        'published_at',
    ];

    protected function casts(): array
    {
        return [
            'meta_data' => 'array',
            'is_featured' => 'boolean',
            'is_pinned' => 'boolean',
            'allow_comments' => 'boolean',
            'views_count' => 'integer',
            'likes_count' => 'integer',
            'published_at' => 'datetime',
        ];
    }

    // Relationships
    public function category(): BelongsTo
    {
        return $this->belongsTo(BlogCategory::class, 'blog_category_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    // Scopes
    public function scopePublished($query)
    {
        return $query->where('status', 'published')
            ->where('published_at', '<=', now());
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopePinned($query)
    {
        return $query->where('is_pinned', true);
    }

    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    public function scopeByCategory($query, $categoryId)
    {
        return $query->where('blog_category_id', $categoryId);
    }

    public function scopeRecent($query, $limit = 10)
    {
        return $query->orderBy('published_at', 'desc')->limit($limit);
    }

    public function scopePopular($query, $limit = 10)
    {
        return $query->orderBy('views_count', 'desc')->limit($limit);
    }

    // Mutators
    public function setTitleAttribute($value)
    {
        $this->attributes['title'] = $value;
        if (empty($this->attributes['slug'])) {
            $this->attributes['slug'] = Str::slug($value);
        }
    }

    public function setStatusAttribute($value)
    {
        $this->attributes['status'] = $value;

        // Auto-set published_at when status changes to published
        if ($value === 'published' && !$this->published_at) {
            $this->attributes['published_at'] = now();
        }
    }

    // Accessors
    public function getExcerptAttribute($value)
    {
        if ($value) {
            return $value;
        }

        // Auto-generate excerpt from content
        return Str::limit(strip_tags($this->content), 200);
    }

    public function getReadingTimeAttribute()
    {
        $words = str_word_count(strip_tags($this->content));
        $minutes = ceil($words / 200); // Average reading speed: 200 words per minute

        return $minutes . ' menit baca';
    }

    public function getFormattedDateAttribute()
    {
        return $this->published_at ? $this->published_at->format('d M Y') : null;
    }

    public function getFeaturedImageUrlAttribute()
    {
        if ($this->featured_image) {
            return asset('storage/' . $this->featured_image);
        }

        return asset('images/blog-placeholder.jpg'); // Default image
    }

    // Helper methods
    public function incrementViews()
    {
        $this->increment('views_count');
    }

    public function incrementLikes()
    {
        $this->increment('likes_count');
    }

    public function isPublished(): bool
    {
        return $this->status === 'published' &&
               $this->published_at &&
               $this->published_at <= now();
    }

    public function isDraft(): bool
    {
        return $this->status === 'draft';
    }
}
