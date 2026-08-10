<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class NotificationController extends Controller
{
    private function checkAdmin(): void
    {
        if (!auth()->user()->isAdmin()) {
            abort(403, 'Unauthorized access.');
        }
    }

    public function index(): Response
    {
        $this->checkAdmin();

        $notifications = auth()->user()
            ->notifications()
            ->orderBy('created_at', 'desc')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/Notifications/Index', [
            'notifications' => $notifications,
        ]);
    }

    public function latestJson()
    {
        $this->checkAdmin();

        $notifications = auth()->user()
            ->notifications()
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        return response()->json([
            'unread' => $notifications->whereNull('read_at')->count(),
            'notifications' => $notifications->map(fn ($n) => [
                'id' => $n->id,
                'data' => $n->data,
                'read_at' => $n->read_at,
                'created_at' => $n->created_at?->toIso8601String(),
            ]),
        ]);
    }

    public function markRead(Request $request, string $id)
    {
        $this->checkAdmin();

        $notification = auth()->user()->notifications()->findOrFail($id);
        $notification->markAsRead();

        return redirect()->back();
    }

    public function markAllRead(Request $request)
    {
        $this->checkAdmin();

        auth()->user()->unreadNotifications->markAsRead();

        return redirect()->back();
    }

    public function destroy(Request $request, string $id)
    {
        $this->checkAdmin();

        auth()->user()->notifications()->findOrFail($id)->delete();

        return redirect()->back();
    }
}
