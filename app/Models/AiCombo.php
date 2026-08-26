<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class AiCombo extends Model
{
    protected $table = 'ai_combos';

    protected $fillable = ['name', 'slug', 'description', 'label', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (AiCombo $combo) {
            if (empty($combo->slug)) {
                $combo->slug = Str::slug($combo->name) . '-' . Str::random(4);
            }
        });
    }

    public function models(): BelongsToMany
    {
        return $this->belongsToMany(AiModel::class, 'ai_combo_models', 'ai_combo_id', 'ai_model_id')
            ->withPivot('priority')
            ->orderByPivot('priority');
    }

    public function comboModels(): HasMany
    {
        return $this->hasMany(AiComboModel::class, 'ai_combo_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
