<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Invoice;
use App\Models\PaymentAccount;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends CustomerBaseController
{
    /** Relasi item order yang dibutuhkan untuk menampilkan nama produk pada rincian tagihan. */
    private const ITEM_RELATIONS = [
        'order.orderItems.hostingPlan',
        'order.orderItems.domainPrice',
        'order.orderItems.servicePlan',
    ];

    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $customer = $this->customer();

        $invoices = $customer->invoices()
            ->with(['bank', 'paymentAccount', 'order'])
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return Inertia::render('Customer/Invoices/Index', [
            'invoices' => $invoices,
        ]);
    }

    /**
     * Display the specified resource.
     */
    public function show(Invoice $invoice): Response
    {
        $this->authorize('view', $invoice);

        $invoice->load(['bank', 'paymentAccount', 'customer', ...self::ITEM_RELATIONS]);
        $this->appendItemLabels($invoice);

        return Inertia::render('Customer/Invoices/Show', [
            'invoice' => $invoice,
        ]);
    }

    /**
     * Sertakan type_label/display_name/display_spec pada tiap item supaya halaman pelanggan
     * menampilkan "produk apa" dari satu sumber yang sama dengan PDF & email invoice.
     */
    private function appendItemLabels(Invoice $invoice): void
    {
        foreach ($invoice->order?->orderItems ?? [] as $item) {
            $item->append(['type_label', 'display_name', 'display_spec']);
        }
    }

    /**
     * Show payment form for the invoice.
     */
    public function payment(Invoice $invoice): Response
    {
        $this->authorize('view', $invoice);

        if ($invoice->isPaid()) {
            return redirect()->route('customer.invoices.show', $invoice)
                ->with('error', 'Invoice sudah dibayar.');
        }

        $invoice->load(['bank', 'paymentAccount', 'customer', ...self::ITEM_RELATIONS]);
        $this->appendItemLabels($invoice);

        $paymentAccounts = PaymentAccount::query()
            ->active()
            ->orderBy('sort')
            ->orderBy('type')
            ->orderBy('name')
            ->get();

        return Inertia::render('Customer/Invoices/Payment', [
            'invoice' => $invoice,
            'paymentAccounts' => $paymentAccounts,
        ]);
    }

    /**
     * Process payment for the invoice.
     */
    public function processPayment(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('view', $invoice);

        if ($invoice->isPaid()) {
            return redirect()->route('customer.invoices.show', $invoice)
                ->with('error', 'Invoice sudah dibayar.');
        }

        $request->validate([
            'payment_account_id' => 'required|exists:payment_accounts,id',
        ]);

        $paymentAccount = PaymentAccount::findOrFail($request->payment_account_id);

        if (! $paymentAccount->is_active) {
            return redirect()->back()
                ->with('error', 'Metode pembayaran yang dipilih tidak aktif.');
        }

        // Update invoice with selected payment account
        $invoice->update([
            'bank_id' => null,
            'payment_account_id' => $paymentAccount->id,
            'payment_method' => $paymentAccount->type,
            'status' => 'sent', // Mark as sent for payment
        ]);

        return redirect()->route('customer.invoices.show', $invoice)
            ->with('success', 'Metode pembayaran berhasil dipilih. Silakan lakukan pembayaran sesuai instruksi.');
    }

    /**
     * Konfirmasi pembayaran oleh customer: hanya mengirim bukti & menunggu verifikasi admin.
     * Kredit TIDAK ditambahkan di sini — admin yang akan menandai lunas setelah verifikasi.
     */
    public function confirmPayment(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('view', $invoice);

        if ($invoice->isPaid()) {
            return redirect()->route('customer.invoices.show', $invoice)
                ->with('error', 'Invoice sudah dibayar.');
        }

        $request->validate([
            'payment_proof' => 'required|string|max:2000',
        ]);

        $invoice->update([
            'status' => 'pending',
            'payment_proof' => $request->payment_proof,
        ]);

        return redirect()->route('customer.invoices.show', $invoice)
            ->with('success', 'Bukti pembayaran berhasil dikirim. Menunggu verifikasi admin.');
    }
}
