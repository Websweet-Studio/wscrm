<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AiTransaction extends Model
{
    protected $fillable = [
        'customer_id',
        'type',
        'source',
        'credits',
        'ai_package_id',
        'invoice_id',
        'ai_model_id',
        'tokens_input',
        'tokens_output',
        'description',
    ];

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class, 'customer_id');
    }

    public function package(): BelongsTo
    {
        return $this->belongsTo(AiPackage::class, 'ai_package_id');
    }

    public function invoice(): BelongsTo
    {
        return $this->belongsTo(Invoice::class, 'invoice_id');
    }

    public function model(): BelongsTo
    {
        return $this->belongsTo(AiModel::class, 'ai_model_id');
    }
}
