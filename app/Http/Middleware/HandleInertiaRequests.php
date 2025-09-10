<?php

namespace App\Http\Middleware;

use Illuminate\Foundation\Inspiring;
use Illuminate\Http\Request;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that's loaded on the first page visit.
     *
     * @see https://inertiajs.com/server-side-setup#root-template
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determines the current asset version.
     *
     * @see https://inertiajs.com/asset-versioning
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @see https://inertiajs.com/shared-data
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        [$message, $author] = str(Inspiring::quotes()->random())->explode('-');

        $customerBadges = [];
        if ($customer = $request->user('customer')) {
            // Count unpaid invoices (pending, sent, overdue)
            $unpaidInvoicesCount = $customer->invoices()
                ->whereIn('status', ['pending', 'sent', 'overdue'])
                ->count();

            // Count orders needing followup (pending, processing)
            $pendingOrdersCount = $customer->orders()
                ->whereIn('status', ['pending', 'processing'])
                ->count();

            $customerBadges = [
                'unpaid_invoices' => $unpaidInvoicesCount,
                'pending_orders' => $pendingOrdersCount,
            ];
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $request->user(), // Admin user (web guard)
                'customer' => $request->user('customer'), // Customer user (customer guard)
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'customerBadges' => $customerBadges,
        ];
    }
}
