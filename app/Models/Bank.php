<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Bank extends Model
{
    use HasFactory;

    protected $fillable = [
        'bank_name',
        'bank_code',
        'account_number',
        'account_name',
        'branch',
        'swift_code',
        'description',
        'is_active',
        'admin_fee',
        'bank_type',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'admin_fee' => 'decimal:2',
        ];
    }

    public function invoices(): HasMany
    {
        return $this->hasMany(Invoice::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLocal($query)
    {
        return $query->where('bank_type', 'local');
    }

    public function scopeInternational($query)
    {
        return $query->where('bank_type', 'international');
    }

    public function isActive(): bool
    {
        return $this->is_active;
    }

    public function getFullAccountInfo(): string
    {
        return "{$this->bank_name} - {$this->account_number} ({$this->account_name})";
    }
}
