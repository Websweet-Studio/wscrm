<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class WebsiteClient extends Model
{
    use SoftDeletes;

    /**
     * wp_app_password di-hide dari serialization karena ter-encrypt pakai
     * APP_KEY produksi lama (103) yang beda. Data tetap utuh di DB; akses
     * via attribute tetap jalan (controller sync). Fix sementara sampai
     * APP_KEY asli didapat. Ref: session deploy app.websweetstudio.com.
     */
    protected $hidden = ['wp_app_password'];

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
        'auto_update_enabled',
        'last_auto_update_at',
        'last_auto_update_status',
    ];

    protected function casts(): array
    {
        return [
            'plugins' => 'array',
            'wp_app_password' => 'encrypted',
            'is_active' => 'boolean',
            'auto_update_enabled' => 'boolean',
            'last_auto_update_at' => 'datetime',
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
