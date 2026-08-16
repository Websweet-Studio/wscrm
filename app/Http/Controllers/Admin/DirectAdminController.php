<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\DirectAdminSetting;
use App\Models\Order;
use App\Services\DirectAdminService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;
use Inertia\Response;

class DirectAdminController extends Controller
{
    public function __construct(private DirectAdminService $da) {}

    public function index(): Response
    {
        $settings = $this->da->settings();

        // Sembunyikan password asli dari frontend
        $maskedSettings = $settings;
        $maskedSettings['password'] = $settings['password'] !== '' ? '********' : '';

        $accounts = [];
        $error = null;
        $connection = ['ok' => false, 'message' => ''];

        if ($this->da->isConfigured()) {
            try {
                $accounts = array_map(function (array $account) {
                    $account['linked_order'] = $this->findOrderByDomain($account['domain'] ?? '');

                    return $account;
                }, $this->da->listAccounts());
                $connection = ['ok' => true, 'message' => 'Koneksi ke DirectAdmin berhasil.'];
            } catch (\Exception $e) {
                $error = $e->getMessage();
                $connection = ['ok' => false, 'message' => $error];
            }
        }

        // Order hosting yang domainnya belum cocok dengan akun DA mana pun
        $linkedDomains = collect($accounts)
            ->pluck('domain')
            ->filter()
            ->map(fn ($d) => $this->normalizeDomain($d))
            ->all();

        $unlinkedOrders = Order::with('customer')
            ->whereIn('status', ['active', 'suspended', 'expired'])
            ->whereNotNull('domain_name')
            ->get()
            ->filter(function (Order $order) use ($linkedDomains) {
                return ! in_array($this->normalizeDomain($order->domain_name), $linkedDomains, true);
            })
            ->values();

        return Inertia::render('Admin/DirectAdmin/Index', [
            'settings' => $maskedSettings,
            'accounts' => $accounts,
            'unlinkedOrders' => $unlinkedOrders,
            'connection' => $connection,
            'error' => $error,
        ]);
    }

    public function saveSettings(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'scheme' => ['required', Rule::in(['https', 'http'])],
            'host' => 'required|string|max:255',
            'port' => 'required|integer|between:1,65535',
            'username' => 'required|string|max:255',
            'password' => 'nullable|string|max:255',
            'verify_ssl' => 'boolean',
        ]);

        DirectAdminSetting::setValue('scheme', $validated['scheme']);
        DirectAdminSetting::setValue('host', $validated['host']);
        DirectAdminSetting::setValue('port', (string) $validated['port']);
        DirectAdminSetting::setValue('username', $validated['username']);
        DirectAdminSetting::setValue('verify_ssl', (int) ($request->boolean('verify_ssl')));

        // Password kosong → pertahankan yang lama
        if (! empty($validated['password']) && $validated['password'] !== '********') {
            DirectAdminSetting::setSecret('password', $validated['password']);
        }

        $test = $this->da->testConnection();

        if ($test['ok']) {
            return redirect()->back()->with('success', 'Pengaturan DirectAdmin tersimpan dan koneksi berhasil.');
        }

        return redirect()->back()->with('error', 'Pengaturan tersimpan, tapi tes koneksi gagal: '.$test['message']);
    }

    public function suspend(string $username): RedirectResponse
    {
        try {
            $result = $this->da->suspend($username);

            if (! $this->da->successful($result)) {
                return redirect()->back()->with('error', 'Gagal suspend '.$username.': '.($result['text'] ?? 'respons tidak valid'));
            }

            $this->updateOrderStatus($username, 'suspended');

            return redirect()->back()->with('success', "Akun {$username} berhasil di-suspend.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal suspend '.$username.': '.$e->getMessage());
        }
    }

    public function unsuspend(string $username): RedirectResponse
    {
        try {
            $result = $this->da->unsuspend($username);

            if (! $this->da->successful($result)) {
                return redirect()->back()->with('error', 'Gagal unsuspend '.$username.': '.($result['text'] ?? 'respons tidak valid'));
            }

            $this->updateOrderStatus($username, 'active');

            return redirect()->back()->with('success', "Akun {$username} berhasil di-unsuspend.");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Gagal unsuspend '.$username.': '.$e->getMessage());
        }
    }

    /**
     * Cari order lokal yang domainnya cocok dengan domain akun DA.
     * Prefer layanan hosting, lalu yang status active.
     */
    private function findOrderByDomain(string $domain): ?Order
    {
        if ($domain === '') {
            return null;
        }

        $normalized = $this->normalizeDomain($domain);

        $orders = Order::where('domain_name', $normalized)
            ->orWhere('domain_name', 'www.'.$normalized)
            ->get();

        return $orders
            ->sortByDesc(fn (Order $o) => $o->service_type === 'hosting')
            ->sortByDesc(fn (Order $o) => $o->status === 'active')
            ->first();
    }

    /**
     * Update status order lokal yang ter-link dengan username tsb.
     * Ambil domain dari config user tsb (1 panggilan API).
     */
    private function updateOrderStatus(string $username, string $status): void
    {
        try {
            $config = $this->da->request('CMD_API_SHOW_USER_CONFIG', ['user' => $username]);
        } catch (\Exception) {
            return;
        }

        $domain = $config['domain'] ?? '';
        if ($domain === '') {
            return;
        }

        $order = $this->findOrderByDomain($domain);

        if ($order) {
            $order->update(['status' => $status]);
        }
    }

    private function normalizeDomain(string $domain): string
    {
        return strtolower(trim(preg_replace('/^www\./i', '', $domain)));
    }
}
