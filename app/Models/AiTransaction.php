<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class AiTransaction extends Model
{
    protected $fillable = [
        'customer_id',
        'type',
        'source',
        'credits',
        'expires_at',
        'remaining',
        'ai_package_id',
        'invoice_id',
        'ai_model_id',
        'tokens_input',
        'tokens_output',
        'description',
    ];

    protected function casts(): array
    {
        return [
            'credits' => 'integer',
            'remaining' => 'integer',
            'expires_at' => 'datetime',
        ];
    }

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

    /**
     * Konsumsi kredit masuk secara FIFO (yang paling cepat kedaluwarsa dipakai dulu)
     * dan tandai sumber kredit yang sudah habis dipakai. Bila tidak ada baris `in`
     * dengan sisa (mis. data lama sebelum fitur masa aktif), fallback ke pengurangan
     * sederhana dari balance supaya pemakaian tetap berjalan.
     *
     * @return int sisa yang belum tertutup (harusnya 0 bila saldo cukup)
     */
    public static function consumeFifo(int $customerId, int $amount): int
    {
        if ($amount <= 0) {
            return 0;
        }

        return DB::transaction(function () use ($customerId, $amount) {
            $credit = \App\Models\AiCredit::where('customer_id', $customerId)->lockForUpdate()->first();

            if (! $credit || $credit->balance < $amount) {
                return $amount;
            }

            $rows = static::where('customer_id', $customerId)
                ->where('type', 'in')
                ->where('remaining', '>', 0)
                ->orderBy('expires_at')
                ->orderBy('id')
                ->lockForUpdate()
                ->get();

            $left = $amount;

            foreach ($rows as $row) {
                if ($left <= 0) {
                    break;
                }

                $take = min((int) $row->remaining, $left);
                $row->decrement('remaining', $take);
                $left -= $take;
            }

            $credit->balance = max(0, (int) $credit->balance - $amount);
            $credit->save();

            return $left;
        });
    }
}
