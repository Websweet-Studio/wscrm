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
            ->map(fn($p) => (float) $p->final_price / (int) $p->credits)
            ->min();

        $transactions = $this->filteredTransactions($customer->id, null, null)
            ->paginate(15);

        // Total kredit yang pernah masuk (pembelian + penyesuaian manual), patokan 100%.
        $totalCredits = (int) AiTransaction::where('customer_id', $customer->id)
            ->where('type', 'in')
            ->sum('credits');

        // Ringkasan pemakaian token (input+output) dari transaksi usage.
        $tokenSum = function (?string $since) use ($customer) {
            $q = AiTransaction::where('customer_id', $customer->id)->where('type', 'out');
            if ($since !== null) {
                $q->where('created_at', '>=', $since);
            }

            return (int) $q->selectRaw('COALESCE(SUM(COALESCE(tokens_input,0)+COALESCE(tokens_output,0)),0) as total')->value('total');
        };

        $tokensToday = $tokenSum(now()->startOfDay()->toDateTimeString());
        $tokens30d = $tokenSum(now()->subDays(29)->startOfDay()->toDateTimeString());
        $tokensTotal = $tokenSum(null);

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
            'tokens_today' => $tokensToday,
            'tokens_30d' => $tokens30d,
            'tokens_total' => $tokensTotal,
        ]);
    }

    /**
     * Query transaksi AI customer dengan filter opsional (type, model_id).
     */
    private function filteredTransactions(int $customerId, ?string $type, ?int $modelId)
    {
        return AiTransaction::with(['model:id,model_key', 'package:id,name'])
            ->where('customer_id', $customerId)
            ->when($type !== null && $type !== '', fn($q) => $q->where('type', $type))
            ->when($modelId !== null && $modelId > 0, fn($q) => $q->where('ai_model_id', $modelId))
            ->latest('created_at');
    }

    /**
     * API JSON riwayat transaksi (paginasi + filter) untuk tab Riwayat Usage.
     */
    public function history(Request $request): JsonResponse
    {
        $customer = $this->customer();

        $type = $request->input('type');
        $modelId = (int) $request->input('model_id', 0);

        $transactions = $this->filteredTransactions($customer->id, $type, $modelId)
            ->paginate(15)
            ->withQueryString();

        return response()->json([
            'data' => $transactions->items(),
            'current_page' => $transactions->currentPage(),
            'last_page' => $transactions->lastPage(),
            'total' => $transactions->total(),
            'per_page' => $transactions->perPage(),
        ]);
    }

    /**
     * Export riwayat transaksi ke CSV.
     */
    public function exportCsv(Request $request): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $customer = $this->customer();

        $type = $request->input('type');
        $modelId = (int) $request->input('model_id', 0);

        $transactions = $this->filteredTransactions($customer->id, $type, $modelId)->get();

        $filename = 'ai-transactions-' . now()->format('Ymd-His') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        return response()->stream(function () use ($transactions) {
            $out = fopen('php://output', 'w');
            fwrite($out, "\xEF\xBB\xBF"); // BOM utk Excel
            fputcsv($out, ['Waktu', 'Tipe', 'Kredit', 'Token In', 'Token Out', 'Model', 'Detail']);

            foreach ($transactions as $t) {
                fputcsv($out, [
                    Carbon::parse($t->created_at)->format('Y-m-d H:i'),
                    $t->type === 'in' ? ($t->source === 'purchase' ? 'Pembelian' : 'Penyesuaian') : 'Pemakaian',
                    (int) $t->credits,
                    (int) ($t->tokens_input ?? 0),
                    (int) ($t->tokens_output ?? 0),
                    $t->model?->model_key ?? '',
                    (string) $t->description,
                ]);
            }

            fclose($out);
        }, 200, $headers);
    }

    /**
     * Generate / regenerate API key customer.
     */
    public function apiKey(): JsonResponse
    {
        $customer = $this->customer();

        $credit = AiCredit::firstOrCreate(['customer_id' => $customer->id]);

        $key = 'wsk-' . Str::random(48);

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
