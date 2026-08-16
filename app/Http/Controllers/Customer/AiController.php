<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\AiCredit;
use App\Models\AiModel;
use App\Models\AiPackage;
use App\Models\AiTransaction;
use App\Models\Invoice;
use App\Services\InvoiceGeneratorService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Str;
use Inertia\Inertia;
use Inertia\Response;

class AiController extends CustomerBaseController
{
    /**
     * Dashboard token & usage: sisa kredit, API key, harga per model, endpoint, riwayat usage.
     */
    public function index(): Response
    {
        $customer = $this->customer();

        $credit = AiCredit::firstOrCreate(['customer_id' => $customer->id]);

        $apiKey = null;
        if ($credit->api_key) {
            try {
                $apiKey = Crypt::decryptString($credit->api_key);
            } catch (\Throwable $e) {
                $apiKey = null;
            }
        }

        $models = AiModel::with('provider:id,name')
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        // Harga referensi 1 kredit (Rp) = paket aktif termurah per kredit.
        $creditPrice = AiPackage::active()
            ->where('credits', '>', 0)
            ->get()
            ->map(fn ($p) => (float) $p->final_price / (int) $p->credits)
            ->min();

        $transactions = AiTransaction::with(['model:id,model_key', 'package:id,name'])
            ->where('customer_id', $customer->id)
            ->latest('created_at')
            ->limit(20)
            ->get();

        // Total kredit yang pernah masuk (pembelian + penyesuaian manual), patokan 100%.
        $totalCredits = (int) AiTransaction::where('customer_id', $customer->id)
            ->where('type', 'in')
            ->sum('credits');

        // Pemakaian harian 30 hari terakhir: kredit terpakai + jumlah request.
        $daily = [];
        for ($i = 29; $i >= 0; $i--) {
            $day = now()->subDays($i);
            $daily[$day->format('Y-m-d')] = [
                'date' => $day->format('Y-m-d'),
                'label' => $day->format('d M'),
                'credits' => 0,
                'runs' => 0,
            ];
        }

        AiTransaction::where('customer_id', $customer->id)
            ->where('type', 'out')
            ->where('created_at', '>=', now()->subDays(29)->startOfDay())
            ->get(['created_at', 'credits'])
            ->each(function ($t) use (&$daily) {
                $key = Carbon::parse($t->created_at)->format('Y-m-d');
                if (isset($daily[$key])) {
                    $daily[$key]['credits'] += abs((int) $t->credits);
                    $daily[$key]['runs']++;
                }
            });

        return Inertia::render('Customer/Ai/Index', [
            'balance' => (int) $credit->balance,
            'total_credits' => $totalCredits,
            'api_key' => $apiKey,
            'endpoint' => url('/api/v1'),
            'models' => $models,
            'credit_price' => $creditPrice !== null ? round($creditPrice, 2) : null,
            'transactions' => $transactions,
            'usage_daily' => array_values($daily),
        ]);
    }

    /**
     * Generate / regenerate API key customer.
     */
    public function apiKey(): JsonResponse
    {
        $customer = $this->customer();

        $credit = AiCredit::firstOrCreate(['customer_id' => $customer->id]);

        $key = 'wsk-'.Str::random(48);

        $credit->update([
            'api_key' => Crypt::encryptString($key),
            'api_key_hash' => hash('sha256', $key),
        ]);

        return response()->json(['api_key' => $key]);
    }

    public function packages(): Response
    {
        $customer = $this->customer();

        $packages = AiPackage::active()
            ->orderBy('sort_order')
            ->orderBy('id')
            ->get();

        return Inertia::render('Customer/Ai/Packages', [
            'balance' => AiCredit::currentBalance($customer->id),
            'packages' => $packages,
        ]);
    }

    /**
     * Beli paket kredit → buat invoice topup → arahkan ke halaman pembayaran existing.
     */
    public function buy(AiPackage $package, InvoiceGeneratorService $generator)
    {
        abort_unless($package->is_active, 404, 'Paket tidak tersedia.');

        $customer = $this->customer();

        // Cegah invoice topup ganda (pending/sent) utk paket yang sama.
        $existing = Invoice::where('customer_id', $customer->id)
            ->where('ai_package_id', $package->id)
            ->where('invoice_type', 'topup')
            ->whereIn('status', ['pending', 'sent'])
            ->first();

        if ($existing) {
            return redirect()->route('customer.invoices.payment', $existing)
                ->with('info', 'Anda sudah memiliki invoice aktif untuk paket ini.');
        }

        $invoice = Invoice::create([
            'customer_id' => $customer->id,
            'invoice_number' => $generator->generateInvoiceNumber(),
            'invoice_type' => 'topup',
            'amount' => $package->price,
            'discount' => $package->discount_amount ?? 0,
            'ai_package_id' => $package->id,
            'status' => 'pending',
            'issue_date' => now(),
            'due_date' => now()->addDays(7),
        ]);

        return redirect()->route('customer.invoices.payment', $invoice)
            ->with('success', 'Invoice pembelian kredit AI dibuat. Silakan selesaikan pembayaran.');
    }
}
