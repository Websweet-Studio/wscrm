<?php

namespace App\Http\Middleware;

use App\Models\BrandingSetting;
use App\Models\Employee;
use App\Models\Task;
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

        $adminBadges = [];
        if ($user = $request->user()) {
            $department = Employee::where('user_id', $user->id)->value('department');
            $pendingTasksCount = Task::query()
                ->where('status', '!=', 'done')
                ->where(function ($q) use ($user, $department) {
                    $q->where('assigned_user_id', $user->id);
                    if ($department) {
                        $q->orWhere('assigned_department', $department);
                    }
                })
                ->count();
            $unassignedTodoCount = Task::query()
                ->where('status', 'todo')
                ->whereNull('assigned_user_id')
                ->where('created_by_user_id', $user->id)
                ->count();
            $inProgressCount = Task::query()
                ->where('status', 'in_progress')
                ->where(function ($q) use ($user) {
                    $q->where('assigned_user_id', $user->id)
                        ->orWhere('created_by_user_id', $user->id);
                })
                ->count();
            $todoAssignedToMeCount = Task::query()
                ->where('status', 'todo')
                ->where('assigned_user_id', $user->id)
                ->count();

            $adminBadges = [
                'pending_tasks' => $pendingTasksCount,
                'tasks_unassigned_todo' => $unassignedTodoCount,
                'tasks_in_progress' => $inProgressCount,
                'tasks_todo_assigned' => $todoAssignedToMeCount,
            ];
        }

        return [
            ...parent::share($request),
            'name' => config('app.name'),
            'brandingSettings' => BrandingSetting::getAllActive()->pluck('value', 'key'),
            'quote' => ['message' => trim($message), 'author' => trim($author)],
            'auth' => [
                'user' => $request->user(), // Admin user (web guard)
                'customer' => $request->user('customer'), // Customer user (customer guard)
            ],
            'sidebarOpen' => ! $request->hasCookie('sidebar_state') || $request->cookie('sidebar_state') === 'true',
            'customerBadges' => $customerBadges,
            'adminBadges' => $adminBadges,
            'flash' => [
                'toast' => $request->session()->get('toast'),
                'error' => $request->session()->get('error'),
                'success' => $request->session()->get('success'),
            ],
        ];
    }
}
