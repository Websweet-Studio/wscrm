<?php

namespace App\Http\Controllers\Admin\Ai;

use App\Http\Controllers\Controller;
use App\Mail\AiCreditGrantedMail;
use App\Models\AiCredit;
use App\Models\AiTransaction;
use App\Models\Customer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Inertia\Inertia;
use Inertia\Response;

class CreditController extends Controller
{
    public function index(): Response
    {
        $sortBy = in_array(request('sort_by'), ['name', 'email', 'username', 'ai_balance'], true)
            ? request('sort_by')
            : 'name';
        $sortDir = strtolower((string) request('sort_dir')) === 'desc' ? 'desc' : 'asc';
        $sortColumn = $sortBy === 'ai_balance' ? 'ai_balance' : "customers.{$sortBy}";

        $customers = Customer::query()
            ->when(request('search'), function ($query, $search) {
                $query->where('name', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('username', 'like', "%{$search}%");
            })
            ->leftJoin('ai_credits', 'ai_credits.customer_id', '=', 'customers.id')
            ->select('customers.*', 'ai_credits.balance as ai_balance')
            ->orderBy($sortColumn, $sortDir)
            ->orderBy('customers.id')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Ai/Credits/Index', [
            'customers' => $customers,
            'filters' => request()->only(['search', 'sort_by', 'sort_dir']),
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

            if ($delta > 0) {
                AiTransaction::create([
                    'customer_id' => $validated['customer_id'],
                    'type' => 'in',
                    'source' => 'manual_adjust',
                    'credits' => $delta,
                    'expires_at' => now()->addDays((int) config('ai.credit_ttl_days', 30)),
                    'remaining' => $delta,
                    'description' => $validated['description'] ?: 'Penyesuaian manual oleh admin',
                ]);

                $credit->increment('balance', $delta);

                return $delta;
            }

            // Pengurangan saldo (subtract/set ke nilai lebih rendah): konsumsi FIFO.
            AiTransaction::create([
                'customer_id' => $validated['customer_id'],
                'type' => 'out',
                'source' => 'manual_adjust',
                'credits' => $delta,
                'description' => $validated['description'] ?: 'Penyesuaian manual oleh admin',
            ]);

            AiTransaction::consumeFifo($validated['customer_id'], abs($delta));

            return $delta;
        });

        if ($delta === 0) {
            return redirect()->back()->with('info', 'Tidak ada perubahan saldo.');
        }

        // Kirim email notifikasi hanya saat admin MENAMBAH kredit (gratis).
        if ($delta > 0) {
            $customer = Customer::find($validated['customer_id']);
            $balanceAfter = (int) AiCredit::where('customer_id', $validated['customer_id'])->value('balance');

            if ($customer?->email) {
                try {
                    Mail::to($customer->email)->send(new AiCreditGrantedMail(
                        $customer,
                        $delta,
                        $balanceAfter,
                        $validated['description'] ?: null,
                    ));
                } catch (\Throwable $e) {
                    report($e);
                }
            }
        }

        return redirect()->back()->with('success', 'Saldo kredit berhasil disesuaikan.');
    }
}
