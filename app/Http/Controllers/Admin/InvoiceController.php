<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BrandingSetting;
use App\Models\Customer;
use App\Models\Invoice;
use App\Models\Order;
use App\Models\PaymentAccount;
use App\Services\InvoiceGeneratorService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function index(Request $request): Response
    {
        $query = Invoice::with(['customer', 'order']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->get('search');
            $query->where(function ($q) use ($search) {
                $q->where('invoice_number', 'like', "%{$search}%")
                    ->orWhereHas('customer', function ($customerQuery) use ($search) {
                        $customerQuery->where('name', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        // Status filter
        if ($request->filled('status')) {
            $query->where('status', $request->get('status'));
        }

        // Type filter
        if ($request->filled('invoice_type')) {
            $query->where('invoice_type', $request->get('invoice_type'));
        }

        // Sorting
        $sort = $request->get('sort', 'created_at');
        $direction = $request->get('direction', 'desc');

        // Validate sort field
        $allowedSorts = ['invoice_number', 'customer_name', 'invoice_type', 'amount', 'billing_cycle', 'due_date', 'status', 'created_at'];
        if (! in_array($sort, $allowedSorts)) {
            $sort = 'created_at';
        }

        // Validate direction
        if (! in_array($direction, ['asc', 'desc'])) {
            $direction = 'desc';
        }

        if ($sort === 'customer_name') {
            $query->join('customers', 'invoices.customer_id', '=', 'customers.id')
                ->orderBy('customers.name', $direction)
                ->select('invoices.*');
        } else {
            $query->orderBy($sort, $direction);
        }

        $invoices = $query->paginate(20)->withQueryString();

        // Tandai invoice lunas yang layanannya BELUM diperpanjang (perpanjangan
        // manual) supaya badge peringatan bisa tampil di daftar.
        $invoices->getCollection()->each(fn (Invoice $invoice) => $invoice->append('service_renewal_pending'));

        // Layanan yang sudah dibayar tapi belum diperpanjang — pekerjaan manual
        // yang sering terlewat.
        $renewalPending = Invoice::query()
            ->paid()
            ->whereNotNull('order_id')
            ->with('order:id,status,expires_at,domain_name')
            ->get()
            ->filter(fn (Invoice $invoice) => $invoice->serviceRenewalPending())
            ->count();

        // Statistics — semua pakai NILAI BERSIH (amount - discount), bukan bruto.
        $netAmount = DB::raw('amount - COALESCE(discount, 0)');
        $totalInvoices = Invoice::count();
        $totalRevenue = (float) Invoice::where('status', 'paid')->sum($netAmount);
        $pendingAmount = (float) Invoice::query()->unpaid()->sum($netAmount);
        $overdueAmount = (float) Invoice::query()->overdue()->sum($netAmount);

        return Inertia::render('Admin/Invoices/Index', [
            'invoices' => $invoices,
            'filters' => [
                'search' => $request->get('search'),
                'status' => $request->get('status'),
                'invoice_type' => $request->get('invoice_type'),
            ],
            'sort' => $sort,
            'direction' => $direction,
            'statistics' => [
                'total' => $totalInvoices,
                'revenue' => $totalRevenue,
                'pending' => $pendingAmount,
                'overdue' => $overdueAmount,
                'renewal_pending' => $renewalPending,
            ],
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'email']),
            'services' => Order::services()
                ->with('customer:id,name,email')
                ->get(['id', 'domain_name', 'service_type', 'customer_id']),
        ]);
    }

    public function store(Request $request, InvoiceGeneratorService $generator)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'service_id' => 'nullable|exists:orders,id',
            'invoice_type' => 'required|in:setup,renewal',
            'amount' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,quarterly,semi_annually,annually',
            'due_date' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|string|max:1000',
        ]);

        $invoiceNumber = $generator->generateInvoiceNumber();

        Invoice::create([
            'invoice_number' => $invoiceNumber,
            'invoice_type' => $validated['invoice_type'],
            'customer_id' => $validated['customer_id'],
            'order_id' => $validated['service_id'] ?? null,
            'amount' => $validated['amount'],
            'discount' => $validated['discount'] ?? 0,
            'issue_date' => now(),
            'due_date' => $validated['due_date'],
            'billing_cycle' => $validated['billing_cycle'],
            'status' => 'pending',
            'notes' => $validated['notes'] ?? null,
        ]);

        return back()->with('message', 'Invoice created successfully.');
    }

    public function show(Invoice $invoice): Response
    {
        $invoice->load(['customer', 'order']);
        $invoice->append('service_renewal_pending');

        return Inertia::render('Admin/Invoices/Show', [
            'invoice' => $invoice,
        ]);
    }

    public function update(Request $request, Invoice $invoice)
    {
        $validated = $request->validate([
            'status' => 'required|in:pending,paid,overdue,cancelled',
            'discount' => 'nullable|numeric|min:0',
            'payment_method' => 'nullable|string',
            'notes' => 'nullable|string',
        ]);

        if ($validated['status'] === 'paid' && $invoice->status !== 'paid') {
            $invoice->markAsPaid($validated['payment_method'] ?? null);
        } else {
            $invoice->update($validated);
        }

        return back()->with('message', 'Invoice updated successfully.');
    }

    public function destroy(Invoice $invoice)
    {
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Invoice yang sudah dibayar tidak bisa dihapus.');
        }

        $invoice->delete();

        return back()->with('success', 'Invoice berhasil dihapus.');
    }

    public function generateRenewalInvoices(InvoiceGeneratorService $generator)
    {
        $count = $generator->generateRenewalInvoices(30);

        return back()->with('message', "Generated {$count} renewal invoice(s) successfully.");
    }

    public function downloadPdf(Invoice $invoice)
    {
        try {
            $invoice->load(['customer', 'order.orderItems', 'paymentAccount']);

            $branding = BrandingSetting::getAllActive()->pluck('value', 'key');
            $paymentAccounts = PaymentAccount::active()->orderBy('sort')->get();

            $pdf = Pdf::loadView('invoices.pdf', compact('invoice', 'branding', 'paymentAccounts'))
                ->setPaper('a4', 'portrait');

            $filename = 'invoice-' . str_replace(['/', '\\'], '-', $invoice->invoice_number) . '.pdf';

            return $pdf->download($filename);
        } catch (\Exception $e) {
            logger()->error('PDF Generation Error: ' . $e->getMessage());

            return back()->with('error', 'Gagal menggenerate PDF: ' . $e->getMessage());
        }
    }

    /**
     * Send invoice email to customer
     */
    public function sendInvoice(Invoice $invoice)
    {
        $invoice->load(['customer', 'order.orderItems' => function ($q) {
            $q->with(['hostingPlan', 'domainPrice', 'servicePlan']);
        }]);

        Mail::to($invoice->customer->email)->send(new \App\Mail\InvoiceEmail($invoice));

        return back()->with('success', 'Tagihan berhasil dikirim ke email ' . $invoice->customer->email);
    }

    /**
     * Create invoice from order and send it via email
     */
    public function createAndSendInvoice(Order $order, InvoiceGeneratorService $generator)
    {
        // Check if order already has an invoice
        if ($order->invoices()->exists()) {
            $invoice = $order->invoices()->first();
            $invoice->load(['customer', 'order.orderItems' => function ($q) {
                $q->with(['hostingPlan', 'domainPrice', 'servicePlan']);
            }]);
            Mail::to($invoice->customer->email)->send(new \App\Mail\InvoiceEmail($invoice));

            return back()->with('success', 'Tagihan sudah ada dan berhasil dikirim ke email ' . $invoice->customer->email);
        }

        // Calculate subtotal from order items
        $orderItems = $order->orderItems()->with(['hostingPlan', 'domainPrice', 'servicePlan'])->get();
        $subtotal = $orderItems->sum(fn($item) => $item->price * $item->quantity);
        $discountAmount = $order->discount_amount ?? 0;

        // Calculate due date: 7 days from now
        $dueDate = now()->addDays(7);

        // Create invoice from order data
        $invoice = Invoice::create([
            'invoice_number' => $generator->generateInvoiceNumber(),
            'invoice_type' => $order->isService() ? 'renewal' : 'setup',
            'customer_id' => $order->customer_id,
            'order_id' => $order->id,
            'amount' => $subtotal,
            'discount' => $discountAmount,
            'issue_date' => now()->toDateString(),
            'due_date' => $dueDate->toDateString(),
            // Periode tagihan = jatuh tempo layanan (penjaga anti invoice ganda).
            'period_end' => $order->expires_at?->toDateString(),
            'status' => 'pending',
            'billing_cycle' => $order->billing_cycle,
            'notes' => 'Tagihan untuk ' . ($order->domain_name ?: 'Order #' . $order->id),
        ]);

        // Pre-load all relationships for the email
        $invoice->load(['customer', 'order.orderItems' => function ($q) {
            $q->with(['hostingPlan', 'domainPrice', 'servicePlan']);
        }]);

        // Send the invoice email
        Mail::to($order->customer->email)->send(new \App\Mail\InvoiceEmail($invoice));

        return back()->with('success', 'Tagihan berhasil dibuat dan dikirim ke email ' . $order->customer->email);
    }

    /**
     * Mark invoice as paid
     */
    public function markAsPaid(Invoice $invoice)
    {
        // Only allow marking unpaid invoices as paid
        if ($invoice->status === 'paid') {
            return back()->with('error', 'Invoice sudah dalam status dibayar.');
        }

        $invoice->update([
            'status' => 'paid',
            'paid_at' => now(),
        ]);

        // Perpanjangan layanan TIDAK otomatis — ingatkan operator kalau belum dikerjakan.
        $invoice->loadMissing('order');

        if ($invoice->serviceRenewalPending()) {
            $domain = $invoice->order?->domain_name ?? ('order #'.$invoice->order_id);

            return back()->with('success', 'Invoice berhasil ditandai sebagai dibayar. Perhatian: layanan '.$domain.' belum diperpanjang — jalankan `php8.3 artisan service:renew '.$domain.'` agar masa aktifnya ikut bergeser.');
        }

        return back()->with('success', 'Invoice berhasil ditandai sebagai dibayar.');
    }

    public function bulkMarkAsPaid(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:invoices,id',
        ]);

        $ids = array_values(array_unique($validated['ids']));

        // Tandai lunas SATU PER SATU lewat model (bukan query builder update) supaya
        // observer jalan (kredit AI topup bertambah). Layanan TIDAK diperpanjang
        // otomatis — perpanjangan tetap manual lewat `service:renew`.
        $invoices = Invoice::with('order')->whereIn('id', $ids)->get();
        $alreadyPaid = $invoices->where('status', 'paid')->count();
        $updated = 0;
        $pendingRenewal = [];

        foreach ($invoices as $invoice) {
            if ($invoice->status === 'paid') {
                continue;
            }

            $invoice->update([
                'status' => 'paid',
                'paid_at' => now(),
            ]);

            $updated++;

            if ($invoice->serviceRenewalPending()) {
                $pendingRenewal[] = $invoice->order?->domain_name ?? ('order #'.$invoice->order_id);
            }
        }

        if ($updated === 0 && $alreadyPaid > 0) {
            return back()->with('message', 'Semua invoice yang dipilih sudah dalam status dibayar.');
        }

        $extra = $pendingRenewal !== []
            ? ' Belum diperpanjang (jalankan `service:renew`): '.implode(', ', $pendingRenewal).'.'
            : '';

        return back()->with('success', "Berhasil menandai {$updated} invoice sebagai dibayar. {$alreadyPaid} invoice sudah dibayar.{$extra}");
    }

    public function bulkDestroy(Request $request)
    {
        $validated = $request->validate([
            'ids' => 'required|array|min:1',
            'ids.*' => 'integer|exists:invoices,id',
        ]);

        $ids = array_values(array_unique($validated['ids']));

        return DB::transaction(function () use ($ids) {
            $paidCount = Invoice::whereIn('id', $ids)->where('status', 'paid')->count();

            $deleted = Invoice::whereIn('id', $ids)
                ->where('status', '!=', 'paid')
                ->delete();

            if ($deleted === 0 && $paidCount > 0) {
                return back()->with('error', 'Invoice yang sudah dibayar tidak bisa dihapus.');
            }

            return back()->with('success', "Berhasil menghapus {$deleted} invoice. {$paidCount} invoice sudah dibayar (tidak dihapus).");
        });
    }

    /**
     * Helper method to get invoice number from service
     */
    protected function generateInvoiceNumber(): string
    {
        return (new InvoiceGeneratorService)->generateInvoiceNumber();
    }
}
