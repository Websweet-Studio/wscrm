<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Bank;
use App\Models\BrandingSetting;
use App\Models\Invoice;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): Response
    {
        $customer = Auth::guard('customer')->user();

        $invoices = $customer->invoices()
            ->with(['bank', 'order'])
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

        $invoice->load(['bank', 'order', 'customer']);

        $activeBanks = Bank::where('is_active', true)
            ->orderBy('bank_name')
            ->get();

        return Inertia::render('Customer/Invoices/Show', [
            'invoice' => $invoice,
            'banks' => $activeBanks,
        ]);
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

        $invoice->load(['bank', 'order', 'customer']);

        $activeBanks = Bank::where('is_active', true)
            ->orderBy('bank_name')
            ->get();

        $paymentMethods = BrandingSetting::getActivePaymentMethods();
        if (count($paymentMethods) === 0) {
            $paymentMethods = array_values(array_filter(BrandingSetting::getPaymentMethods(), fn (array $m) => ($m['key'] ?? null) === 'bank_transfer'));
        }
        if (count($paymentMethods) === 0) {
            $paymentMethods = BrandingSetting::getPaymentMethods();
        }

        return Inertia::render('Customer/Invoices/Payment', [
            'invoice' => $invoice,
            'banks' => $activeBanks,
            'paymentMethods' => $paymentMethods,
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

        $paymentMethods = BrandingSetting::getActivePaymentMethods();
        if (count($paymentMethods) === 0) {
            $paymentMethods = array_values(array_filter(BrandingSetting::getPaymentMethods(), fn (array $m) => ($m['key'] ?? null) === 'bank_transfer'));
        }
        if (count($paymentMethods) === 0) {
            $paymentMethods = BrandingSetting::getPaymentMethods();
        }

        $allowedPaymentMethodKeys = array_values(array_filter(array_map(fn (array $m) => $m['key'] ?? null, $paymentMethods)));

        $request->validate([
            'bank_id' => 'required|exists:banks,id',
            'payment_method' => ['required', Rule::in($allowedPaymentMethodKeys)],
        ]);

        $bank = Bank::findOrFail($request->bank_id);

        if (! $bank->is_active) {
            return redirect()->back()
                ->with('error', 'Bank yang dipilih tidak aktif.');
        }

        // Update invoice with selected bank and payment method
        $invoice->update([
            'bank_id' => $request->bank_id,
            'payment_method' => $request->payment_method,
            'status' => 'sent', // Mark as sent for payment
        ]);

        return redirect()->route('customer.invoices.show', $invoice)
            ->with('success', 'Metode pembayaran berhasil dipilih. Silakan lakukan pembayaran sesuai instruksi.');
    }

    /**
     * Confirm payment for the invoice.
     */
    public function confirmPayment(Request $request, Invoice $invoice): RedirectResponse
    {
        $this->authorize('view', $invoice);

        if ($invoice->isPaid()) {
            return redirect()->route('customer.invoices.show', $invoice)
                ->with('error', 'Invoice sudah dibayar.');
        }

        $request->validate([
            'payment_proof' => 'nullable|string|max:1000',
        ]);

        // In a real application, you might want to upload payment proof files
        // For now, we'll just mark it as paid
        $invoice->markAsPaid($invoice->payment_method);

        return redirect()->route('customer.invoices.show', $invoice)
            ->with('success', 'Pembayaran berhasil dikonfirmasi. Terima kasih!');
    }
}
