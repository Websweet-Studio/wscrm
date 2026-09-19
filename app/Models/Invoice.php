<?php

namespace App\Models;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Invoice extends Model
{
    use HasFactory;

    protected $fillable = [
        'customer_id',
        'order_id',
        'invoice_number',
        'invoice_type',
        'amount',
        'discount',
        'status',
        'issue_date',
        'due_date',
        'billing_cycle',
        'paid_at',
        'payment_method',
        'bank_id',
        'payment_account_id',
        'ai_package_id',
        'notes',
        'payment_proof',
        'period_end',
    ];

    /**
     * `amount` = subtotal BRUTO, `discount` = total potongan.
     * Nilai yang benar-benar ditagih/dibayar = amount - discount (final_amount).
     * Ikut diserialisasi supaya frontend tidak perlu menebak-nebak.
     */
    protected $appends = ['final_amount'];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'discount' => 'decimal:2',
            'issue_date' => 'date',
            'due_date' => 'date',
            'period_end' => 'date',
            'paid_at' => 'datetime',
        ];
    }

    public function customer(): BelongsTo
    {
        return $this->belongsTo(Customer::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    public function paymentAccount(): BelongsTo
    {
        return $this->belongsTo(PaymentAccount::class);
    }

    public function aiPackage(): BelongsTo
    {
        return $this->belongsTo(AiPackage::class, 'ai_package_id');
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    /**
     * Semua invoice yang belum lunas. WAJIB memuat 'pending' — semua invoice
     * diterbitkan dengan status 'pending', jadi tanpa ini daftar tagihan kosong.
     */
    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['pending', 'sent', 'overdue']);
    }

    public function scopeByCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
    }

    /**
     * Invoice lewat jatuh tempo (status 'overdue' ATAU tanggalnya sudah lewat).
     * Dikelompokkan agar aman dirantai dengan filter lain (dulu `orWhere` lepas
     * bisa membocorkan invoice customer lain).
     */
    public function scopeOverdue($query)
    {
        return $query->where(function ($q) {
            $q->where('status', 'overdue')
                ->orWhere(function ($q2) {
                    $q2->where('due_date', '<', Carbon::now()->startOfDay())
                        ->whereIn('status', ['pending', 'sent']);
                });
        });
    }

    public function isPaid(): bool
    {
        return $this->status === 'paid' && $this->paid_at !== null;
    }

    public function isOverdue(): bool
    {
        return $this->due_date->isPast() && ! $this->isPaid();
    }

    public function markAsPaid(?string $paymentMethod = null): void
    {
        $this->update([
            'status' => 'paid',
            'paid_at' => Carbon::now(),
            'payment_method' => $paymentMethod,
        ]);
    }

    public function getFinalAmountAttribute(): float
    {
        return max(0, $this->amount - $this->discount);
    }

    /**
     * Invoice LUNAS tapi layanan yang ditagih belum diperpanjang.
     *
     * Perpanjangan di WSCRM SELALU manual (`service:renew`) karena pembayarannya
     * juga manual (transfer → verifikasi admin). Jadi ini bukan proses otomatis,
     * hanya peringatan supaya langkah manual itu tidak terlewat.
     *
     * Aturan:
     * - `period_end` terisi → order.expires_at harus >= period_end
     * - `period_end` kosong → order yang masih `expired` dianggap belum diperpanjang
     */
    public function serviceRenewalPending(): bool
    {
        if (! $this->isPaid() || ! $this->order_id) {
            return false;
        }

        $order = $this->relationLoaded('order') ? $this->order : $this->order()->first();

        if (! $order) {
            return false;
        }

        // Order yang sudah berhenti memang tidak perlu diperpanjang.
        if (in_array($order->status, ['cancelled', 'terminated'], true)) {
            return false;
        }

        if ($this->period_end) {
            return ! $order->expires_at
                || Carbon::parse($order->expires_at)->startOfDay()->lt($this->period_end->copy()->startOfDay());
        }

        return $order->status === 'expired';
    }

    public function getServiceRenewalPendingAttribute(): bool
    {
        return $this->serviceRenewalPending();
    }

    public function getDiscountedAttribute(): bool
    {
        return $this->discount > 0;
    }
}
