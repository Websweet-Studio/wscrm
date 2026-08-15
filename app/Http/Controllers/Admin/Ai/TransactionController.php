<?php

namespace App\Http\Controllers\Admin\Ai;

use App\Http\Controllers\Controller;
use App\Models\AiTransaction;
use Illuminate\Support\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class TransactionController extends Controller
{
    public function index(): Response
    {
        $transactions = AiTransaction::query()
            ->with(['customer:id,name,email', 'package:id,name', 'invoice:id,invoice_number', 'model:id,model_key'])
            ->when(request('customer_id'), function ($query, $customerId) {
                $query->where('customer_id', $customerId);
            })
            ->when(request('type'), function ($query, $type) {
                $query->where('type', $type);
            })
            ->when(request('source'), function ($query, $source) {
                $query->where('source', $source);
            })
            ->when(request('from'), function ($query, $from) {
                $query->whereDate('created_at', '>=', $from);
            })
            ->when(request('to'), function ($query, $to) {
                $query->whereDate('created_at', '<=', $to);
            })
            ->latest('created_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Ai/Transactions/Index', [
            'transactions' => $transactions,
            'analytics' => $this->analytics(),
            'filters' => request()->only(['customer_id', 'type', 'source', 'from', 'to']),
        ]);
    }

    /**
     * Statistik pemakaian AI (hanya transaksi usage) untuk dashboard grafik.
     */
    private function analytics(): array
    {
        $usage = fn() => AiTransaction::query()->where('source', 'usage');

        $inputTokens = (int) $usage()->sum('tokens_input');
        $outputTokens = (int) $usage()->sum('tokens_output');

        // Pemakaian bulanan 6 bulan terakhir (label konsisten walau kosong).
        $monthly = [];
        for ($i = 5; $i >= 0; $i--) {
            $key = now()->subMonths($i)->format('Y-m');
            $monthly[$key] = [
                'month' => now()->subMonths($i)->translatedFormat('M Y'),
                'tokens' => 0,
                'runs' => 0,
            ];
        }

        AiTransaction::where('source', 'usage')
            ->where('created_at', '>=', now()->subMonths(6)->startOfMonth())
            ->get(['created_at', 'tokens_input', 'tokens_output'])
            ->each(function ($t) use (&$monthly) {
                $key = Carbon::parse($t->created_at)->format('Y-m');
                if (isset($monthly[$key])) {
                    $monthly[$key]['tokens'] += (int) $t->tokens_input + (int) $t->tokens_output;
                    $monthly[$key]['runs']++;
                }
            });

        // Rincian per model.
        $byModel = AiTransaction::where('source', 'usage')
            ->whereNotNull('ai_transactions.ai_model_id')
            ->join('ai_models', 'ai_models.id', '=', 'ai_transactions.ai_model_id')
            ->selectRaw('ai_models.model_key, COUNT(*) as runs, SUM(ai_transactions.tokens_input) as tokens_in, SUM(ai_transactions.tokens_output) as tokens_out, SUM(ai_transactions.credits) as credits')
            ->groupBy('ai_models.model_key')
            ->orderByDesc('runs')
            ->get()
            ->map(fn($m) => [
                'model_key' => $m->model_key,
                'runs' => (int) $m->runs,
                'tokens_in' => (int) $m->tokens_in,
                'tokens_out' => (int) $m->tokens_out,
                'credits' => abs((int) $m->credits),
            ])
            ->values();

        return [
            'total_tokens' => $inputTokens + $outputTokens,
            'input_tokens' => $inputTokens,
            'output_tokens' => $outputTokens,
            'total_runs' => $usage()->count(),
            'credits_spent' => abs((int) $usage()->sum('credits')),
            'monthly' => array_values($monthly),
            'by_model' => $byModel,
        ];
    }
}
