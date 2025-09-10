<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Invoice;
use App\Services\InvoiceGeneratorService;
use Illuminate\Http\Request;
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

        $invoices = $query->latest()->paginate(20)->withQueryString();

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
            'statistics' => [
                'total' => $totalInvoices,
                'revenue' => $totalRevenue,
                'pending' => $pendingAmount,
                'overdue' => $overdueAmount,
            ],
            'customers' => Customer::orderBy('name')->get(['id', 'name', 'email']),
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

    /**
     * Helper method to get invoice number from service
     */
    protected function generateInvoiceNumber(): string
    {
        return (new InvoiceGeneratorService)->generateInvoiceNumber();
    }
}
