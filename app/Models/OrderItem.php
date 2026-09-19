<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'item_type',
        'item_id',
        'domain_name',
        'quantity',
        'price',
        'billing_cycle',
        'expires_at',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'expires_at' => 'date',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function item(): MorphTo
    {
        return $this->morphTo('item', 'item_type', 'item_id');
    }

    public function hostingPlan(): BelongsTo
    {
        return $this->belongsTo(HostingPlan::class, 'item_id');
    }

    public function domainPrice(): BelongsTo
    {
        return $this->belongsTo(DomainPrice::class, 'item_id');
    }

    public function servicePlan(): BelongsTo
    {
        return $this->belongsTo(ServicePlan::class, 'item_id');
    }

    /**
     * Label jenis item yang bisa dibaca klien.
     */
    public function getTypeLabelAttribute(): string
    {
        return match ($this->item_type) {
            'hosting' => 'Hosting',
            'domain' => 'Domain',
            'service' => 'Layanan',
            'app' => 'Aplikasi',
            'web' => 'Website',
            'maintenance' => 'Maintenance',
            default => ucfirst((string) $this->item_type),
        };
    }

    /**
     * Nama produk yang ditagih ("produk apa") — SATU sumber untuk PDF invoice, email invoice,
     * halaman admin, dan halaman pelanggan. Jangan tulis ulang logika ini di template/Vue.
     */
    public function getDisplayNameAttribute(): string
    {
        if ($this->item_type === 'hosting') {
            return $this->hostingPlan?->plan_name ?: ($this->servicePlan?->name ?: 'Layanan Hosting');
        }

        if ($this->item_type === 'domain') {
            if ($this->domain_name) {
                return $this->domain_name;
            }

            $extension = $this->domainPrice?->extension;
            $orderDomain = $this->order?->domain_name;

            // Pakai domain utama order hanya kalau ekstensinya cocok (mis. kmdesain.my.id utk item .my.id).
            if ($orderDomain && $extension && str_ends_with(strtolower($orderDomain), strtolower($extension))) {
                return $orderDomain;
            }

            return $extension ? 'Domain '.$extension : ($orderDomain ?: 'Registrasi Domain');
        }

        if ($name = $this->servicePlan?->name) {
            return $name;
        }

        return $this->domain_name ?: $this->type_label;
    }

    /**
     * Spesifikasi singkat produk (baris kecil di bawah nama produk).
     */
    public function getDisplaySpecAttribute(): ?string
    {
        if ($this->item_type === 'hosting' && $this->hostingPlan) {
            $plan = $this->hostingPlan;

            $parts = array_filter([
                $plan->storage_gb ? $this->trimNumber($plan->storage_gb).' GB SSD' : null,
                $plan->cpu_cores ? $this->trimNumber($plan->cpu_cores).' Core' : null,
                $plan->ram_gb ? $this->trimNumber($plan->ram_gb).' GB RAM' : null,
            ]);

            return $parts ? implode(' · ', $parts) : null;
        }

        // Domain: kalau namanya sudah berupa domain penuh (mis. kmdesain.my.id) tidak perlu catatan ekstensi lagi.
        if ($this->item_type === 'domain' && ! str_contains($this->display_name, '.') && $this->domainPrice?->extension) {
            return 'Ekstensi '.$this->domainPrice->extension;
        }

        return null;
    }

    /** Tampilkan angka tanpa nol desimal yang tidak perlu: 8,00 -> 8; 1,30 -> 1,3 */
    private function trimNumber($value): string
    {
        return rtrim(rtrim(number_format((float) $value, 2, ',', '.'), '0'), ',');
    }
}
