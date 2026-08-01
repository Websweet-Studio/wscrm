<?php

namespace App\Services;

use App\Models\Order;
use App\Models\WebsiteClient;
use App\Services\AiAgents\ArticleAgent;
use App\Services\AiAgents\OrderAgent;
use App\Services\AiAgents\WebsiteAgent;
use Illuminate\Support\Facades\Log;

/**
 * Orchestrator AI Agent: pahami intent user, bangun konteks, panggil sub-agent per domain.
 */
class AiAgentService
{
    public function __construct(
        private AiClient $aiClient,
        private WebsiteAgent $websiteAgent,
        private ArticleAgent $articleAgent,
        private OrderAgent $orderAgent,
    ) {}

    /**
     * Process a user command and return the AI response + actions taken.
     */
    public function process(string $userMessage): array
    {
        // 1. Gather context: all websites & orders with their current state
        $context = $this->buildContext();

        // 2. Send to AI to determine intent and required actions
        $aiResponse = $this->callAI($userMessage, $context);

        // 3. Parse & execute actions
        $results = $this->executeActions($aiResponse);

        // 4. Lampirkan hasil eksekusi nyata ke pesan agar AI tidak mengklaim sukses sebelum aksi dijalankan
        $message = $aiResponse['message'] ?? '';
        if ($results) {
            $lines = [];
            foreach ($results as $r) {
                $res = $r['result'];
                if (isset($res['error'])) {
                    $lines[] = '[GAGAL] ' . $r['action'] . ': ' . $res['error'];
                } else {
                    $lines[] = '[OK] ' . ($res['message'] ?? 'Aksi ' . $r['action'] . ' selesai');
                }
            }
            $message = trim($message) . "\n\n" . implode("\n", $lines);
        }

        return [
            'ai_response' => $message,
            'actions' => $results,
            'success' => true,
        ];
    }

    private function buildContext(): array
    {
        $websites = WebsiteClient::with('customer')->get();

        $websiteData = [];
        foreach ($websites as $w) {
            $websiteData[] = [
                'id' => $w->id,
                'name' => $w->name,
                'url' => $w->url,
                'wp_version' => $w->wp_version,
                'theme_name' => $w->theme_name,
                'theme_version' => $w->theme_version,
                'plugins' => $w->plugins,
                'has_wp_credentials' => !empty($w->wp_username) && !empty($w->wp_app_password),
            ];
        }

        $orders = Order::with('customer')
            ->whereIn('status', ['active', 'suspended'])
            ->orderBy('expires_at')
            ->get();

        $orderData = $orders->map(fn(Order $o) => [
            'id' => $o->id,
            'customer' => $o->customer?->name ?? 'Tanpa customer',
            'service_type' => $o->service_type,
            'domain' => $o->domain_name,
            'expires_at' => $o->expires_at?->format('Y-m-d'),
            'auto_renew' => (bool) $o->auto_renew,
            'status' => $o->status,
        ])->values()->all();

        return [
            'websites' => $websiteData,
            'orders' => $orderData,
        ];
    }

    private function callAI(string $userMessage, array $context): array
    {
        if (!$this->aiClient->hasApiKey()) {
            return ['message' => "AI tidak dikonfigurasi. Tambahkan AI_API_KEY di .env"];
        }

        try {
            $content = $this->aiClient->chat([
                ['role' => 'system', 'content' => $this->getSystemPrompt($context)],
                ['role' => 'user', 'content' => $userMessage],
            ], 0.3, 2000);

            // Parse JSON from response (AI should return JSON with actions)
            return $this->parseAiResponse($content);
        } catch (\Exception $e) {
            Log::error('AI call failed: ' . $e->getMessage());
            return ['message' => "Gagal menghubungi AI: " . $e->getMessage()];
        }
    }

    private function getSystemPrompt(array $context): string
    {
        $contextJson = json_encode($context, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);

        return <<<PROMPT
Kamu adalah AI Agent untuk mengelola aplikasi WSCRM (website WordPress, layanan hosting/domain, dan order klien). Kamu BISA menjalankan aksi nyata di sistem.

## Data Saat Ini:
```json
{$contextJson}
```

## Aksi yang Bisa Kamu Lakukan:
1. **check_updates** - Cek website mana yang perlu update WP core/plugin/tema (sertakan website_id jika user menyebut website/domain tertentu, cari id-nya di data websites)
2. **update_wp** - Update WordPress core untuk website tertentu (perlu id)
3. **update_plugins** - Update plugin spesifik di website tertentu (perlu id, plugin_slugs[])
4. **create_article** - Buat artikel SEO lengkap otomatis: generate konten, sisipkan 2 gambar, audit SEO, publish jika skor >= 80, revisi otomatis jika gagal (perlu website_id, title/topik, opsional keyword)
5. **audit_seo** - Audit SEO halaman website (perlu id, url)
6. **check_expiring_orders** - Cek berapa order/layanan aktif yang akan berakhir (kadaluarsa) bulan ini
7. **renew_order** - Perpanjang masa aktif order/layanan dan/atau tandai sudah dibayar (perlu id dari data orders, months (jumlah bulan, default 3), mark_paid (true/false))

## Aturan:
- Selalu analisis data website & order terlebih dahulu
- Jika user minta "cek update", gunakan aksi **check_updates** dan sebutkan website mana saja
- Jika user menyebut website/domain tertentu (misal "cek demo1.sweet.web.id"), cari id website itu di data websites lalu sertakan **website_id** pada aksi check_updates — jangan cek semua website
- Jika user minta "update", jalankan aksi update
- Jika user minta "buat artikel" (dengan/tanpa menyebut gambar, audit, publish), jalankan aksi **create_article** — sistem otomatis generate konten, sisipkan gambar, audit, dan publish jika skor lolos
- Jika user tanya order yang akan mati/berakhir/kadaluarsa/habis masa aktif bulan ini, gunakan aksi **check_expiring_orders**
- Jika user bilang order sudah bayar / minta perpanjang / "set expired N bulan" / tandai lunas, cari order di data orders berdasarkan nama customer/domain lalu gunakan aksi **renew_order** dengan id yang sesuai, months sesuai permintaan (default 3 jika tidak disebut), dan mark_paid true jika user menyebut sudah bayar
- Balas dalam bahasa Indonesia yang natural dan informatif
- Di akhir respons, sertakan JSON aksi yang perlu dijalankan dalam format:
```json
{"message": "respons kamu ke user", "actions": [{"action": "nama_aksi", "params": {...}}]}
```
- Jika tidak ada aksi yang perlu dijalankan, actions bisa kosong []
- Jika user hanya tanya/minta informasi, jawab saja tanpa actions

PROMPT;
    }

    private function parseAiResponse(string $content): array
    {
        // Try to extract JSON from the response
        if (preg_match('/```json\s*([\s\S]*?)\s*```/', $content, $m)) {
            $json = json_decode($m[1], true);
            if ($json && isset($json['message'])) {
                return $json;
            }
        }

        if (preg_match('/\{[\s\S]*"message"[\s\S]*\}/', $content, $m)) {
            $json = json_decode($m[0], true);
            if ($json && isset($json['message'])) {
                return $json;
            }
        }

        return ['message' => $content, 'actions' => []];
    }

    private function executeActions(array $aiResponse): array
    {
        $actions = $aiResponse['actions'] ?? [];
        $results = [];
        $seen = [];

        foreach ($actions as $action) {
            $actionName = $action['action'] ?? '';

            // Skip duplicate actions the AI may send twice
            if (in_array($actionName, $seen, true)) {
                continue;
            }
            $seen[] = $actionName;

            $params = $action['params'] ?? [];

            try {
                $result = match ($actionName) {
                    'check_updates' => $this->websiteAgent->checkUpdates($params['website_id'] ?? $params['id'] ?? null),
                    'update_wp' => $this->websiteAgent->updateWp($params['id'] ?? null),
                    'update_plugins' => $this->websiteAgent->updatePlugins($params['id'] ?? null, $params['plugin_slugs'] ?? []),
                    'audit_seo' => $this->websiteAgent->auditSeo($params['id'] ?? null, $params['url'] ?? ''),
                    'create_article' => $this->articleAgent->createArticle($params['website_id'] ?? $params['id'] ?? null, $params['title'] ?? '', $params['content'] ?? '', $params['keyword'] ?? ''),
                    'check_expiring_orders' => $this->orderAgent->checkExpiringOrders(),
                    'renew_order' => $this->orderAgent->renewOrder($params['id'] ?? null, (int) ($params['months'] ?? 3), (bool) ($params['mark_paid'] ?? false)),
                    default => ['error' => "Aksi tidak dikenal: {$actionName}"],
                };
            } catch (\Exception $e) {
                $result = ['error' => $e->getMessage()];
            }

            $results[] = [
                'action' => $actionName,
                'params' => $params,
                'result' => $result,
            ];
        }

        return $results;
    }
}
