<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class JournalEntry extends Model
{
    protected $fillable = [
        'website_client_id',
        'user_id',
        'entry_date',
        'activities',
        'summary',
    ];

    protected function casts(): array
    {
        return [
            'activities' => 'array',
            'entry_date' => 'date',
        ];
    }

    public function websiteClient(): BelongsTo
    {
        return $this->belongsTo(WebsiteClient::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
