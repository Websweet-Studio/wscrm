<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WebsiteClient extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'customer_id',
        'name',
        'url',
        'wp_username',
        'wp_app_password',
        'wp_version',
        'theme_name',
        'theme_version',
        'plugins',
        'notes',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'plugins' => 'array',
            'is_active' => 'boolean',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function journals(): HasMany
    {
        return $this->hasMany(JournalEntry::class);
    }
}
