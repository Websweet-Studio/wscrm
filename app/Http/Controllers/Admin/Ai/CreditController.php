<?php

namespace App\Http\Controllers\Admin\Ai;

use App\Http\Controllers\Controller;
use App\Models\AiCredit;
use App\Models\AiTransaction;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Inertia\Response;

class CreditController extends Controller
{
    public function index(): Response
    {
        $customers = Customer::query()
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            })
            ->leftJoin('ai_credits', 'ai_credits.customer_id', '=', 'customers.id')
            ->select('customers.*', 'ai_credits.balance as ai_balance')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Ai/Credits/Index', [
            'customers' => $customers,
            'filters' => request()->only(['search']),
        ]);
    }

    /**
     * Penyesuaian saldo manual: add / subtract / set. Semua tercatat sebagai transaksi manual_adjust.
     */
    public function adjust(Request $request)
    {
        $validated = $request->validate([
            'customer_id' => 'required|exists:customers,id',
            'action' => 'required|in:add,subtract,set',
            'credits' => 'required|integer|min:0',
            'description' => 'nullable|string|max:255',
        ]);

        $delta = DB::transaction(function () use ($validated) {
            $credit = AiCredit::where('customer_id', $validated['customer_id'])
                ->lockForUpdate()
                ->first();

            if (! $credit) {
                $credit = AiCredit::create(['customer_id' => $validated['customer_id'], 'balance' => 0]);
            }

            $current = (int) $credit->balance;

            $delta = match ($validated['action']) {
                'add' => $validated['credits'],
                'subtract' => -$validated['credits'],
                'set' => $validated['credits'] - $current,
            };

            $delta = max(-$current, $delta);

            if ($delta === 0) {
                return 0;
            }

            AiTransaction::create([
                'customer_id' => $validated['customer_id'],
                'type' => $delta > 0 ? 'in' : 'out',
                'source' => 'manual_adjust',
                'credits' => $delta,
                'description' => $validated['description'] ?: 'Penyesuaian manual oleh admin',
            ]);

            $credit->update(['balance' => $current + $delta]);

            return $delta;
        });

        if ($delta === 0) {
            return redirect()->back()->with('info', 'Tidak ada perubahan saldo.');
        }

        return redirect()->back()->with('success', 'Saldo kredit berhasil disesuaikan.');
    }
}
