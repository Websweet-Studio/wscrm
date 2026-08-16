<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\WebsiteClientRequest;
use App\Models\Customer;
use App\Models\JournalEntry;
use App\Models\WebsiteClient;
use App\Services\WordPressService;
use Inertia\Inertia;
use Inertia\Response;
use Illuminate\Http\JsonResponse;

class WebsiteClientController extends Controller
{
    public function index(): Response
    {
        $websites = WebsiteClient::with('customer')
            ->when(request('search'), function ($query, $search) {
                $query->where(function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                        ->orWhere('url', 'like', "%{$search}%");
                });
            })
            ->when(request('customer_id'), fn($q, $v) => $q->where('customer_id', $v))
            ->when(request('is_active') !== null, fn($q, $v) => $q->where('is_active', filter_var($v, FILTER_VALIDATE_BOOLEAN)))
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return Inertia::render('Admin/WebsiteClients/Index', [
            'websites' => $websites,
            'filters' => request()->only(['search', 'customer_id', 'is_active']),
        ]);
    }

    public function create(): Response
    {
        $customers = Customer::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/WebsiteClients/CreateEdit', [
            'customers' => $customers,
            'website' => null,
        ]);
    }

    public function edit(WebsiteClient $website): Response
    {
        $customers = Customer::orderBy('name')->get(['id', 'name']);

        return Inertia::render('Admin/WebsiteClients/CreateEdit', [
            'customers' => $customers,
            'website' => $website,
        ]);
    }

    public function store(WebsiteClientRequest $request)
    {
        WebsiteClient::create($request->validated());

        return redirect()->route('admin.websites.index')->with('success', 'Website berhasil ditambahkan.');
    }

    public function update(WebsiteClientRequest $request, WebsiteClient $website)
    {
        $website->update($request->validated());

        return redirect()->route('admin.websites.index')->with('success', 'Website berhasil diperbarui.');
    }

    public function destroy(WebsiteClient $website)
    {
        $website->delete();

        return redirect()->route('admin.websites.index')->with('success', 'Website berhasil dihapus.');
    }

    public function bulkDelete()
    {
        $ids = request('ids', []);

        if (empty($ids)) {
            return redirect()->back()->with('error', 'Tidak ada website yang dipilih.');
        }

        WebsiteClient::whereIn('id', $ids)->delete();

        return redirect()->back()->with('success', count($ids) . ' website berhasil dihapus.');
    }

    public function show(WebsiteClient $website): Response
    {
        $website->load('customer');

        $journals = $website->journals()
            ->with('user')
            ->orderBy('entry_date', 'desc')
            ->paginate(10);

        return Inertia::render('Admin/WebsiteClients/Show', [
            'website' => $website,
            'journals' => $journals,
        ]);
    }

    public function sync(WebsiteClient $website, WordPressService $wpService): JsonResponse
    {
        $result = $wpService->syncSiteInfo($website);

        if ($result === null) {
            return response()->json([
                'success' => false,
                'message' => 'Gagal sync. Pastikan URL, username, dan Application Password sudah benar.',
            ], 422);
        }

        $updates = $wpService->checkAllUpdates($website);

        return response()->json([
            'success' => true,
            'message' => 'Berhasil sync data WordPress.',
            'data' => $result,
            'updates' => $updates,
        ]);
    }

    public function plugins(WebsiteClient $website, WordPressService $wpService): JsonResponse
    {
        if (!$website->wp_username || !$website->wp_app_password) {
            return response()->json([
                'success' => false,
                'message' => 'Kredensial WordPress belum dikonfigurasi.',
            ], 422);
        }

        return response()->json([
            'success' => true,
            'plugins' => $wpService->getLivePlugins($website),
        ]);
    }

    public function destroyPlugin(WebsiteClient $website, WordPressService $wpService): JsonResponse
    {
        $pluginFile = (string) request('plugin');
        if (!$pluginFile) {
            return response()->json([
                'success' => false,
                'message' => 'Parameter plugin tidak valid.',
            ], 422);
        }

        if (!$website->wp_username || !$website->wp_app_password) {
            return response()->json([
                'success' => false,
                'message' => 'Kredensial WordPress belum dikonfigurasi.',
            ], 422);
        }

        $result = $wpService->deletePlugin($website, $pluginFile);

        if (!$result['success']) {
            return response()->json($result, 422);
        }

        // Refresh stored plugin data so the synced list stays accurate.
        $wpService->syncSiteInfo($website);

        $pluginName = (string) request('name') ?: $pluginFile;

        JournalEntry::create([
            'website_client_id' => $website->id,
            'user_id' => auth()->id(),
            'entry_date' => now()->toDateString(),
            'activities' => [[
                'type' => 'plugin_remove',
                'title' => 'Hapus Plugin',
                'plugin' => $pluginName,
                'detail' => 'Plugin dihapus dari website WordPress',
                'note' => $result['message'],
            ]],
            'summary' => 'Hapus plugin: ' . $pluginName,
        ]);

        return response()->json([
            'success' => true,
            'message' => $result['message'],
        ]);
    }
}
