<?php

namespace App\Http\Controllers\Admin\Ai;

use App\Http\Controllers\Controller;
use App\Models\AiTransaction;
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
            'filters' => request()->only(['customer_id', 'type', 'source', 'from', 'to']),
        ]);
    }
}
