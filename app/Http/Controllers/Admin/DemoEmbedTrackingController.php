<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DemoEmbedTracking;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;

class DemoEmbedTrackingController extends Controller
{
    public function index(Request $request): InertiaResponse
    {
        $trackings = DemoEmbedTracking::query()
            ->when($request->search, function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('referer_url', 'like', "%{$search}%")
                        ->orWhere('referer_host', 'like', "%{$search}%");
                });
            })
            ->when($request->type, function ($query, $type) {
                $query->where('embed_type', $type);
            })
            ->orderByDesc('last_seen_at')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/DemoEmbedTrackings/Index', [
            'trackings' => $trackings,
            'stats' => [
                'total' => DemoEmbedTracking::count(),
                'total_hits' => DemoEmbedTracking::sum('hits'),
                'blocked' => DemoEmbedTracking::blocked()->count(),
            ],
            'filters' => [
                'search' => $request->search,
                'type' => $request->type,
            ],
        ]);
    }

    public function toggleBlock(Request $request, DemoEmbedTracking $demoEmbedTracking)
    {
        $isBlocking = !$demoEmbedTracking->is_blocked;

        $demoEmbedTracking->update([
            'is_blocked' => $isBlocking,
            'blocked_at' => $isBlocking ? now() : null,
            'blocked_reason' => $isBlocking ? $request->input('reason') : null,
        ]);

        return back()->with('success', $isBlocking
            ? 'Domain berhasil di-block.'
            : 'Domain berhasil di-unblock.');
    }

    public function destroy(DemoEmbedTracking $demoEmbedTracking)
    {
        $demoEmbedTracking->delete();

        return back()->with('success', 'Record berhasil dihapus.');
    }

    public function bulkDestroy(Request $request)
    {
        $ids = $request->input('ids', []);

        if (empty($ids)) {
            return back()->with('error', 'Tidak ada record yang dipilih.');
        }

        DemoEmbedTracking::whereIn('id', $ids)->delete();

        return back()->with('success', count($ids) . ' record berhasil dihapus.');
    }
}
