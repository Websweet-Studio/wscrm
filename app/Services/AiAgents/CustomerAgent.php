<?php

namespace App\Services\AiAgents;

use App\Models\Customer;
use App\Models\Invoice;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * Sub-agent: manajemen customer & invoice — daftar, buat, ubah status, invoice.
 */
class CustomerAgent
{
    public function listCustomers(?string $search = null, ?string $status = null, ?callable $onEvent = null): array
    {
        if ($onEvent) {
            $onEvent('Menarik daftar customer...', 'loading', 'Customer Agent');
        }

        $query = Customer::query()
            ->when($search, fn ($q) => $q->where(function ($qq) use ($search) {
                $qq->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%");
            }))
            ->when($status, fn ($q) => $q->where('status', $status))
            ->orderBy('created_at', 'desc')
            ->limit(50);

        $statusLabels = [
            'active' => 'Aktif',
            'inactive' => 'Tidak aktif',
            'suspended' => 'Ditangguhkan',
        ];

        $list = $query->get()->map(fn (Customer $c) => [
            'id' => $c->id,
            'name' => $c->name,
            'email' => $c->email,
            'username' => $c->username,
            'phone' => $c->phone,
            'city' => $c->city,
            'status' => $c->status,
            'status_label' => $statusLabels[$c->status] ?? $c->status,
            'created_at' => $c->created_at?->format('d M Y'),
        ])->values()->all();

        if ($onEvent) {
            $onEvent("Ditemukan " . count($list) . " customer", 'done', 'Customer Agent');
        }

        return [
            'customers' => $list,
            'total' => count($list),
            'summary' => count($list) . ' customer ditemukan.',
        ];
    }

    public function createCustomer(array $data, ?callable $onEvent = null): array
    {
        if ($onEvent) {
            $onEvent('Membuat customer baru...', 'loading', 'Customer Agent');
        }

        $password = $data['password'] ?? Str::random(10);

        $validated = validator($data, [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:customers',
            'username' => 'nullable|string|min:5|max:255|regex:/^[a-zA-Z0-9_]+$/|unique:customers',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string|max:500',
            'city' => 'nullable|string|max:100',
            'country' => 'nullable|string|max:100',
            'postal_code' => 'nullable|string|max:20',
        ])->validate();

        $customer = Customer::create(array_merge($validated, [
            'password' => Hash::make($password),
            'status' => 'active',
        ]));

        if ($onEvent) {
            $onEvent("Customer '{$customer->name}' berhasil dibuat", 'done', 'Customer Agent');
        }

        return [
            'success' => true,
            'customer_id' => $customer->id,
            'name' => $customer->name,
            'email' => $customer->email,
            'username' => $customer->username,
            'temporary_password' => $customer->username ? $password : null,
            'message' => "Customer '{$customer->name}' ({$customer->email}) berhasil dibuat." .
                ($customer->username ? " Username: {$customer->username}. Password sementara: {$password}" : ''),
        ];
    }

    public function updateCustomerStatus(int $customerId, string $status, ?callable $onEvent = null): array
    {
        if (!in_array($status, ['active', 'inactive', 'suspended'], true)) {
            return ['error' => 'Status tidak valid. Gunakan: active, inactive, suspended.'];
        }

        $customer = Customer::find($customerId);
        if (!$customer) {
            return ['error' => 'Customer tidak ditemukan.'];
        }

        if ($onEvent) {
            $onEvent("Mengubah status customer '{$customer->name}' → {$status}...", 'loading', 'Customer Agent');
        }

        $customer->update(['status' => $status]);

        $statusLabels = ['active' => 'Aktif', 'inactive' => 'Tidak aktif', 'suspended' => 'Ditangguhkan'];

        if ($onEvent) {
            $onEvent("Status customer '{$customer->name}' berubah ke {$statusLabels[$status]}", 'done', 'Customer Agent');
        }

        return [
            'success' => true,
            'customer_id' => $customer->id,
            'name' => $customer->name,
            'status' => $status,
            'message' => "Status customer '{$customer->name}' diubah ke {$statusLabels[$status]}.",
        ];
    }

    public function listUnpaidInvoices(?callable $onEvent = null): array
    {
        if ($onEvent) {
            $onEvent('Menarik daftar invoice belum dibayar...', 'loading', 'Customer Agent');
        }

        $invoices = Invoice::with('customer')
            ->whereIn('status', ['sent', 'overdue'])
            ->orderBy('due_date')
            ->limit(50)
            ->get();

        $list = $invoices->map(fn (Invoice $i) => [
            'id' => $i->id,
            'invoice_number' => $i->invoice_number,
            'customer' => $i->customer?->name ?? 'Tanpa customer',
            'amount' => (float) $i->getFinalAmountAttribute(),
            'due_date' => $i->due_date?->format('d M Y'),
            'status' => $i->status,
            'status_label' => $i->status === 'overdue' ? 'Terlambat' : 'Belum dibayar',
            'days_late' => $i->isOverdue() ? max(0, (int) $i->due_date->diffInDays(now())) : 0,
        ])->values()->all();

        if ($onEvent) {
            $onEvent(count($list) . " invoice belum dibayar ditemukan", 'done', 'Customer Agent');
        }

        return [
            'invoices' => $list,
            'total' => count($list),
            'total_amount' => round(collect($list)->sum('amount'), 2),
            'overdue_count' => collect($list)->where('status', 'overdue')->count(),
            'summary' => count($list) . ' invoice belum dibayar (total ' .
                number_format(collect($list)->sum('amount'), 0, ',', '.') . ', ' .
                collect($list)->where('status', 'overdue')->count() . ' terlambat).',
        ];
    }

    public function markInvoicePaid(int $invoiceId, ?string $paymentMethod = null, ?callable $onEvent = null): array
    {
        $invoice = Invoice::find($invoiceId);
        if (!$invoice) {
            return ['error' => 'Invoice tidak ditemukan.'];
        }

        if ($invoice->isPaid()) {
            return ['error' => "Invoice {$invoice->invoice_number} sudah lunas."];
        }

        if ($onEvent) {
            $onEvent("Menandai invoice {$invoice->invoice_number} lunas...", 'loading', 'Customer Agent');
        }

        $invoice->markAsPaid($paymentMethod);

        if ($onEvent) {
            $onEvent("Invoice {$invoice->invoice_number} ditandai lunas", 'done', 'Customer Agent');
        }

        return [
            'success' => true,
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'customer' => $invoice->customer?->name ?? 'Tanpa customer',
            'message' => "Invoice {$invoice->invoice_number} (" . ($invoice->customer?->name ?? 'Tanpa customer') . ") ditandai lunas.",
        ];
    }
}
