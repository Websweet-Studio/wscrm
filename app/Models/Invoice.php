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
        'service_id',
        'invoice_number',
        'invoice_type',
        'amount',
        'status',
        'issue_date',
        'due_date',
        'billing_cycle',
        'paid_at',
        'payment_method',
        'bank_id',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'issue_date' => 'date',
            'due_date' => 'date',
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

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }

    public function bank(): BelongsTo
    {
        return $this->belongsTo(Bank::class);
    }

    public function scopePaid($query)
    {
        return $query->where('status', 'paid');
    }

    public function scopeUnpaid($query)
    {
        return $query->whereIn('status', ['sent', 'overdue']);
    }

    public function scopeOverdue($query)
    {
        return $query->where('status', 'overdue')
            ->orWhere(function ($q) {
                $q->where('due_date', '<', Carbon::now())
                    ->where('status', 'sent');
            });
    }

    public function scopeByCustomer($query, int $customerId)
    {
        return $query->where('customer_id', $customerId);
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
}
