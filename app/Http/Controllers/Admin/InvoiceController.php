<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\InvoiceGeneratorService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class InvoiceController extends Controller
{
    public function index(Request $request, InvoiceGeneratorService $generator): Response
    {
        // Auto-generate renewal invoices for services expiring within 30 days
        $generatedCount = $generator->generateRenewalInvoices(30);

        // Add message to session if invoices were generated
        $generationMessage = null;
        if ($generatedCount > 0) {
            $generationMessage = "Auto-generated {$generatedCount} renewal invoice(s) for expiring services.";
        }

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
        if (!in_array($sort, $allowedSorts)) {
            $sort = 'created_at';
        }

        // Validate direction
        if (!in_array($direction, ['asc', 'desc'])) {
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

        // Statistics
        $totalInvoices = Invoice::count();
        $totalRevenue = Invoice::where('status', 'paid')->sum('amount');
        $pendingAmount = Invoice::where('status', 'pending')->sum('amount');
        $overdueAmount = Invoice::where('status', 'overdue')->sum('amount');

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
            ],
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'email']),
            'generationMessage' => $generationMessage,
        ]);
    }

    public function store(Request $request, InvoiceGeneratorService $generator)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'invoice_type' => 'required|in:setup,renewal',
            'amount' => 'required|numeric|min:0',
            'discount' => 'nullable|numeric|min:0',
            'billing_cycle' => 'required|in:monthly,quarterly,semi_annually,annually',
            'due_date' => 'required|date|after_or_equal:today',
            'notes' => 'nullable|string|max:1000',
        ]);

        $invoiceNumber = $generator->generateInvoiceNumber();

        $invoice = Invoice::create([
            'invoice_number' => $invoiceNumber,
            'invoice_type' => $validated['invoice_type'],
            'customer_id' => $validated['customer_id'],
            'amount' => $validated['amount'],
            'discount' => $validated['discount'] ?? 0,
            'issue_date' => now(),
            'due_date' => $validated['due_date'],
            'billing_cycle' => $validated['billing_cycle'],
            'status' => 'pending',
            'notes' => $validated['notes'],
        ]);

        return back()->with('message', 'Invoice created successfully.');
    }

    public function show(Invoice $invoice): Response
    {
        $invoice->load(['customer', 'order']);

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

    public function generateRenewalInvoices(InvoiceGeneratorService $generator)
    {
        $count = $generator->generateRenewalInvoices(30);

        return back()->with('message', "Generated {$count} renewal invoice(s) successfully.");
    }

    public function downloadPdf(Invoice $invoice)
    {
        try {
            $invoice->load(['customer', 'order.orderItems']);

            $pdf = Pdf::loadView('invoices.pdf', compact('invoice'))
                ->setPaper('a4', 'portrait');

            $filename = 'invoice-'.str_replace(['/', '\\'], '-', $invoice->invoice_number).'.pdf';

            return $pdf->download($filename);

        } catch (\Exception $e) {
            \Log::error('PDF Generation Error: '.$e->getMessage());

            return back()->with('error', 'Gagal menggenerate PDF: '.$e->getMessage());
        }
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
            'paid_at' => now()
        ]);

        return back()->with('success', 'Invoice berhasil ditandai sebagai dibayar.');
    }

    /**
     * Helper method to get invoice number from service
     */
    protected function generateInvoiceNumber(): string
    {
        return (new InvoiceGeneratorService)->generateInvoiceNumber();
    }
}
