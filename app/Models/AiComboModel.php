<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiComboModel extends Model
{
    protected $table = 'ai_combo_models';

    protected $fillable = ['ai_combo_id', 'ai_model_id', 'priority'];

    public function combo(): BelongsTo
    {
        return $this->belongsTo(AiCombo::class, 'ai_combo_id');
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(AiModel::class, 'ai_model_id');
    }
}
