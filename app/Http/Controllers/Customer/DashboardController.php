<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\AiCredit;
use App\Models\JournalEntry;
use App\Models\PaymentAccount;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends CustomerBaseController
{
    public function index(): Response
    {
        $customer = $this->customer();

        // Services are now handled through orders - get active orders instead
        $services = $customer->orders()
            ->whereIn('status', ['active', 'suspended'])
            ->with(['orderItems', 'hostingPlan'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentOrders = $customer->orders()
            ->with(['orderItems'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $unpaidInvoices = $customer->invoices()
            ->unpaid()
            ->orderBy('due_date', 'asc')
            ->limit(10)
            ->get();

        $unpaidTotal = (float) $customer->invoices()->unpaid()->sum('amount');

        // Ringkasan pengeluaran: total lunas bulan ini + total outstanding (belum dibayar).
        // Pakai final amount (amount - discount) supaya konsisten dengan yang dibayar customer.
        $expenseMonth = (float) $customer->invoices()
            ->paid()
            ->where('paid_at', '>=', now()->startOfMonth())
            ->get()
            ->sum(fn ($i) => $i->final_amount);

        $outstandingTotal = (float) $customer->invoices()
            ->unpaid()
            ->get()
            ->sum(fn ($i) => $i->final_amount);

        $paymentAccounts = PaymentAccount::query()
            ->active()
            ->orderBy('type')
            ->orderBy('name')
            ->limit(12)
            ->get();

        $aiBalance = AiCredit::currentBalance($customer->id);

        // Layanan hampir habis / sudah kadaluarsa (dalam 30 hari ke depan).
        $expiringSoon = $customer->orders()
            ->whereIn('status', ['active', 'suspended'])
            ->whereNotNull('expires_at')
            ->where('expires_at', '<=', now()->addDays(30)->endOfDay())
            ->with(['orderItems', 'hostingPlan'])
            ->orderBy('expires_at')
            ->get()
            ->map(fn ($o) => [
                'id' => $o->id,
                'name' => $o->orderItems->first()?->domain_name ?: ($o->hostingPlan?->plan_name ?? 'Layanan #'.$o->id),
                'expires_at' => $o->expires_at?->toDateString(),
                'is_expired' => $o->isExpired(),
                'days_left' => $o->isExpired() ? 0 : $o->daysUntilExpiry(),
            ])
            ->values();

        // Jurnal maintenance terbaru milik customer.
        $websiteIds = $customer->websiteClients()->pluck('id');
        $recentJournals = $websiteIds->isEmpty()
            ? collect()
            : JournalEntry::with('websiteClient:id,name')
                ->whereIn('website_client_id', $websiteIds)
                ->orderBy('entry_date', 'desc')
                ->orderBy('created_at', 'desc')
                ->limit(3)
                ->get()
                ->map(fn ($j) => [
                    'id' => $j->id,
                    'website_name' => $j->websiteClient?->name,
                    'entry_date' => $j->entry_date?->toDateString(),
                    'summary' => $j->summary,
                    'activity_count' => count($j->activities ?? []),
                ])
                ->values();

        return Inertia::render('Customer/Dashboard', [
            'customer' => $customer,
            'services' => $services,
            'recentOrders' => $recentOrders,
            'unpaidInvoices' => $unpaidInvoices,
            'unpaidTotal' => $unpaidTotal,
            'expenseMonth' => $expenseMonth,
            'outstandingTotal' => $outstandingTotal,
            'paymentAccounts' => $paymentAccounts,
            'aiBalance' => $aiBalance,
            'expiringSoon' => $expiringSoon,
            'recentJournals' => $recentJournals,
        ]);
    }
}
