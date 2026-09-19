<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiModel extends Model
{
    protected $table = 'ai_models';

    protected $fillable = [
        'provider_id', 'model_key', 'display_name', 'label', 'input_rate', 'output_rate',
        'is_active', 'supports_vision', 'supports_deep_thinking', 'sort_order',
        // Detail spesifikasi model (katalog provider) — opsional.
        'description', 'upstream_slug', 'cli_command', 'intelligence_index', 'output_speed',
        'context_window', 'cache_read_rate', 'agent_loop_cost', 'released_at',
    ];

    protected function casts(): array
    {
        return [
            'input_rate' => 'decimal:4',
            'output_rate' => 'decimal:4',
            'is_active' => 'boolean',
            'supports_vision' => 'boolean',
            'supports_deep_thinking' => 'boolean',
            'intelligence_index' => 'decimal:1',
            'output_speed' => 'decimal:1',
            'cache_read_rate' => 'decimal:6',
            'agent_loop_cost' => 'decimal:6',
            'released_at' => 'date:Y-m-d',
        ];
    }

    public function provider(): BelongsTo
    {
        return $this->belongsTo(AiProvider::class, 'provider_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
