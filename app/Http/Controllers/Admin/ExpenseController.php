<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class ExpenseController extends Controller
{
    /**
     * Display all expenses with filtering.
     */
    public function index(): Response
    {
        return Inertia::render('Admin/Expenses/Index', [
            'monthlyExpenses' => [
                [
                    'id' => 1,
                    'name' => 'Lisensi Claude Pro',
                    'amount' => 20,
                    'currency' => 'USD',
                    'provider' => 'Anthropic',
                    'category' => 'Software',
                    'next_billing' => '2025-01-19',
                    'status' => 'active',
                    'type' => 'monthly'
                ],
                [
                    'id' => 2,
                    'name' => 'cPanel Guard',
                    'amount' => 15,
                    'currency' => 'USD',
                    'provider' => 'cPanel',
                    'category' => 'Security',
                    'next_billing' => '2025-01-15',
                    'status' => 'active',
                    'type' => 'monthly'
                ]
            ],
            'yearlyExpenses' => [
                [
                    'id' => 3,
                    'name' => 'Domain License',
                    'amount' => 150,
                    'currency' => 'USD',
                    'provider' => 'Registrar',
                    'category' => 'Domain',
                    'next_billing' => '2025-12-01',
                    'status' => 'active',
                    'type' => 'yearly'
                ]
            ],
            'oneTimeExpenses' => [
                [
                    'id' => 4,
                    'name' => 'Deposit Domain .id',
                    'amount' => 500000,
                    'currency' => 'IDR',
                    'provider' => 'PANDI',
                    'category' => 'Domain Deposit',
                    'paid_date' => '2024-12-01',
                    'status' => 'paid',
                    'type' => 'one-time'
                ],
                [
                    'id' => 5,
                    'name' => 'Setup Server',
                    'amount' => 100,
                    'currency' => 'USD',
                    'provider' => 'Digital Ocean',
                    'category' => 'Infrastructure',
                    'paid_date' => '2024-11-15',
                    'status' => 'paid',
                    'type' => 'one-time'
                ]
            ]
        ]);
    }
}
