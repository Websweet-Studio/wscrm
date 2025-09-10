<?php

namespace App\Http\Controllers;

use App\Models\Customer;
use App\Models\Order;
use Carbon\Carbon;
use Inertia\Inertia;
use Inertia\Response;

class DashboardController extends Controller
{
    public function index(): Response
    {
        $now = Carbon::now();
        $thisMonth = $now->startOfMonth();
        $lastMonth = $now->copy()->subMonth()->startOfMonth();

        // Customer Statistics
        $totalCustomers = Customer::count();
        $activeCustomers = Customer::active()->count();
        $newCustomersThisMonth = Customer::where('created_at', '>=', $thisMonth)->count();
        $newCustomersLastMonth = Customer::where('created_at', '>=', $lastMonth)
            ->where('created_at', '<', $thisMonth)->count();

        // Order Statistics
        $totalOrders = Order::count();
        $completedOrders = Order::where('status', 'completed')->count();
        $ordersThisMonth = Order::where('created_at', '>=', $thisMonth)->count();
        $ordersLastMonth = Order::where('created_at', '>=', $lastMonth)
            ->where('created_at', '<', $thisMonth)->count();

        // Revenue Statistics
        $totalRevenue = Order::where('status', 'completed')->sum('total_amount');
        $revenueThisMonth = Order::where('status', 'completed')
            ->where('created_at', '>=', $thisMonth)->sum('total_amount');
        $revenueLastMonth = Order::where('status', 'completed')
            ->where('created_at', '>=', $lastMonth)
            ->where('created_at', '<', $thisMonth)->sum('total_amount');

        // Recent Activities
        $recentOrders = Order::with(['customer', 'orderItems'])
            ->orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        $recentCustomers = Customer::orderBy('created_at', 'desc')
            ->limit(5)
            ->get();

        // Calculate growth percentages
        $customerGrowth = $newCustomersLastMonth > 0
            ? (($newCustomersThisMonth - $newCustomersLastMonth) / $newCustomersLastMonth) * 100
            : ($newCustomersThisMonth > 0 ? 100 : 0);

        $orderGrowth = $ordersLastMonth > 0
            ? (($ordersThisMonth - $ordersLastMonth) / $ordersLastMonth) * 100
            : ($ordersThisMonth > 0 ? 100 : 0);

        $revenueGrowth = $revenueLastMonth > 0
            ? (($revenueThisMonth - $revenueLastMonth) / $revenueLastMonth) * 100
            : ($revenueThisMonth > 0 ? 100 : 0);

        // Daily orders for current month chart
        $dailyOrders = [];
        $currentMonth = $now->copy()->startOfMonth();
        $daysInMonth = $currentMonth->daysInMonth;

        for ($day = 1; $day <= $daysInMonth; $day++) {
            $date = $currentMonth->copy()->day($day);
            $dayOrders = Order::whereDate('created_at', $date)->count();
            $dailyOrders[] = [
                'date' => $date->format('Y-m-d'),
                'day' => $day,
                'orders' => $dayOrders,
            ];
        }

        // Previous months statistics (last 6 months)
        $monthlyStats = [];
        for ($i = 5; $i >= 0; $i--) {
            $monthStart = $now->copy()->subMonths($i)->startOfMonth();
            $monthEnd = $monthStart->copy()->endOfMonth();

            $monthOrders = Order::whereBetween('created_at', [$monthStart, $monthEnd])->count();
            $monthRevenue = Order::where('status', 'completed')
                ->whereBetween('created_at', [$monthStart, $monthEnd])
                ->sum('total_amount');
            $monthCustomers = Customer::whereBetween('created_at', [$monthStart, $monthEnd])->count();

            $monthlyStats[] = [
                'month' => $monthStart->format('M Y'),
                'month_short' => $monthStart->format('M'),
                'orders' => $monthOrders,
                'revenue' => $monthRevenue,
                'customers' => $monthCustomers,
            ];
        }

        return Inertia::render('Dashboard', [
            'stats' => [
                'customers' => [
                    'total' => $totalCustomers,
                    'active' => $activeCustomers,
                    'newThisMonth' => $newCustomersThisMonth,
                    'growth' => round($customerGrowth, 1),
                ],
                'orders' => [
                    'total' => $totalOrders,
                    'completed' => $completedOrders,
                    'thisMonth' => $ordersThisMonth,
                    'growth' => round($orderGrowth, 1),
                ],
                'revenue' => [
                    'total' => $totalRevenue,
                    'thisMonth' => $revenueThisMonth,
                    'growth' => round($revenueGrowth, 1),
                ],
            ],
            'recentActivities' => [
                'orders' => $recentOrders,
                'customers' => $recentCustomers,
            ],
            'chartData' => [
                'dailyOrders' => $dailyOrders,
                'monthlyStats' => $monthlyStats,
            ],
        ]);
    }
}
